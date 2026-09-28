<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * D-221 / ADR-0003: generalise scenario_races → scenario_slots
 *
 * The original `scenario_races` table was URA-shaped: every row was a race
 * with fan gates and maiden gates. This worked for URA Finale but fails for
 * Trackblazer (no race goals, finale is a points league) and Unity Cup
 * (Team Races every 6 months). Our Grand Concert has no guide at all.
 *
 * The new `scenario_slots` table carries a `kind` discriminator so every
 * scenario's timeline is composed from data, not from branches in the code
 * (D-240). The four kinds cover all timeline slot types across all scenarios:
 *
 *   goal_race       Mandatory or optional races with fan/maiden gates (URA, Unity Cup)
 *   team_race       Unity Cup Team Races (every 6 months, opponent selection)
 *   grade_deadline  Trackblazer Grade Point deadlines (end of each year)
 *   scripted_event  Scenario-specific events (URA April Unique Skill check)
 *
 * Cited requirements: US-10 (race calendar and fan gating, promoted to P1),
 * ADR-0003 ruling (absolute end-of-turn values, not deltas).
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scenario_slots', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_key')->index();
            $table->string('kind')->index(); // goal_race, team_race, grade_deadline, scripted_event
            $table->string('slot_label');   // e.g., 'G1', 'Round 1', 'Junior', 'Event'
            $table->string('title');        // e.g., 'Tenno Sho (Spring)', 'End of Junior Year'
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('month'); // 1-12
            $table->enum('half', ['Early', 'Late']);
            $table->string('tier')->nullable(); // G1, G2, OP, etc. (races only)
            $table->unsignedInteger('fans_needed')->nullable(); // goal_race only
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_maiden_gated')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('source_url')->nullable();
            $table->string('snapshot_path')->nullable();
            $table->dateTime('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            // Unique composite: one slot per scenario per month/half per kind
            $table->unique(['scenario_key', 'month', 'half', 'kind'], 'scenario_slots_scenario_key_month_half_kind_unique');

            // CHECK constraint for kind enum (application layer also validates)
            // SQLite doesn't support CHECK via Blueprint; validated at model layer
        });

        // Add scenario_slot_id to race_entries (replacing scenario_race_id)
        Schema::table('race_entries', function (Blueprint $table) {
            $table->foreignId('scenario_slot_id')
                ->nullable()
                ->after('scenario_race_id')
                ->constrained('scenario_slots')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table) {
            $table->dropForeign(['scenario_slot_id']);
            $table->dropColumn('scenario_slot_id');
        });

        Schema::dropIfExists('scenario_slots');
    }
};
