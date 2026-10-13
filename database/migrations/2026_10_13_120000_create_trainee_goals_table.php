<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per career objective a trainee's own ladder states (PRD US-10, ADR-0026).
 *
 * Engine-owned reference data: the ladder is a source statement about the game, not a
 * Trainer record, so this table carries `is_manual` and the four inline provenance
 * columns the way `character_cards` and `race_catalog_slots` do (`ADR-0003` Amendment R3)
 * rather than a `data_sources` row, which stays the character-level table behind FR-A-4.
 *
 * Grain is the **trainee**, not her card and not her run. `race_catalog_slots:70-72` already
 * refuses to call a scenario-wide obligation a per-character goal, and this is the other half:
 * the same race carries a different placing requirement for different trainees, so the pair
 * (race, placing) cannot live on the calendar row. A run's progress against a goal is derived
 * from the `race_entries` the Trainer already records and is never stored here, which is why
 * there is no `training_run_id` column: `RACE-AND-SLICE-RESEARCH.md` §6 sketched one, and the
 * sourced ladder does not need it.
 *
 * The `source_*` columns are the publisher's own condition codes, kept raw for two reasons.
 * They are the only stable identity a re-fetch can re-derive a row from, and the six rows whose
 * requirement this tool has not decoded stay answerable without inventing client copy (D-20).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('umamusume_id')->constrained('umamusume')->cascadeOnDelete();
            // Null for a fan objective, for an objective whose requirement is not decoded, and for
            // the three objectives whose race the source itself marks not released on Global. The
            // race name is never invented to fill this: `source_race_name` carries what the
            // document printed instead.
            $table->foreignId('race_catalog_slot_id')->nullable()->constrained('race_catalog_slots')->nullOnDelete();
            $table->string('goal_type', 20);
            // The source's own placing figure, raw. 1 and 3 and 5 are confirmed against [Global]
            // client captures; 2 and 4 are stored and left undecoded rather than guessed at.
            $table->unsignedTinyInteger('required_placement')->nullable();
            $table->unsignedInteger('fan_threshold')->nullable();
            // 1 Junior, 2 Classic, 3 Senior, 4 the post-December final block. An integer, matching
            // `race_catalog_slots.year`: the CareerYear enum carries three cases only, so a fourth
            // would make a shared `label()` match throw for the final rounds.
            $table->unsignedTinyInteger('career_year');
            $table->unsignedTinyInteger('turn');
            // Ladder position and the branch index at that position. Alternates are real: one order
            // can hold two objectives, and merging them would state one obligation where the client
            // offers a choice.
            $table->unsignedTinyInteger('source_order');
            $table->unsignedTinyInteger('source_race_choice')->default(0);
            $table->unsignedInteger('source_cond_id');
            $table->unsignedTinyInteger('source_cond_type');
            $table->unsignedInteger('source_cond_value');
            $table->unsignedInteger('source_cond_value_2')->default(0);
            $table->unsignedTinyInteger('source_target_type');
            $table->unsignedTinyInteger('source_race_type')->default(0);
            $table->string('source_race_name')->nullable();
            // The source's own `unreleased_servers` marked "en". A goal on a race Global has not
            // shipped is stored, because it is what the document states, and it is never rendered
            // as a Global obligation.
            $table->boolean('global_unreleased')->default(false);
            $table->string('source_url');
            $table->string('snapshot_path')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            // The publisher's condition id plus the deadline turn is the row's identity: it survives
            // a re-fetch when a ladder position shifts, which `source_order` alone does not
            // (measured 2026-10-13: `(char_id, order)` collides 19 times on alternates, this key
            // 0 times across the 829 objectives that belong to the Global roster).
            $table->unique(['umamusume_id', 'source_cond_id', 'turn', 'source_race_choice'],
                'trainee_goals_identity_unique');
            $table->index(['umamusume_id', 'career_year', 'turn', 'source_order'],
                'trainee_goals_ladder_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_goals');
    }
};
