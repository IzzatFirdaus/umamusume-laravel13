<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The career race catalogue: one row per race per turn, not one row per turn.
 *
 * `scenario_slots` was asked to hold this and cannot. Its unique key is
 * (scenario_key, month, half, kind), which allows one goal race per turn, while
 * docs/scenarios/09-global-race-calendar.md records 406 monthly slots landing on
 * 24 distinct month-and-half pairs — 59 of the 61 (year, month, half) turns hold
 * more than one race, up to 12 on Early December. Adding a year column would fix
 * the year collision and leave the multiplicity one untouched, so the grain, not
 * just the key, had to change.
 *
 * `scenario_key` is NULL for the shared monthly slots and set only on the four
 * [Global] scenario finals, which is the whole of what the scenarios differ by
 * ("Scenario differences", 09). NULL is load-bearing: it means "every scenario",
 * and an empty string would mean a scenario nobody has named.
 *
 * ADR-0003 Amendment R3 assigns this table's rows to the fetch engine rather than
 * to a seeder, so the four provenance columns are part of the shape from the
 * first version rather than added when the parser lands.
 *
 * This migration is purely additive and touches no other table. `scenario_slots`
 * keeps its `goal_race` kind: c0a743f (Slice 11, R54) widened that table's unique
 * key so several goal races can share one half-month, which is the same problem
 * this table solves the other way. Two designs now coexist, and reconciling them
 * is a named follow-up rather than a quiet side effect of this file.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_catalog_slots', function (Blueprint $table): void {
            $table->id();

            // Null = shared by every scenario. Set only on a scenario's own final.
            $table->string('scenario_key', 50)->nullable();

            // 1 Junior, 2 Classic, 3 Senior, 4 the post-December final block.
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('month')->nullable();
            $table->enum('half', ['Early', 'Late'])->nullable();
            // (month - 1) * 2 + half, the number the client grid counts against.
            $table->unsignedTinyInteger('turn')->nullable();
            $table->string('slot_label');

            $table->string('title');
            // Decoded label; grade_code keeps the export's own value auditable.
            $table->string('tier', 10)->nullable();
            $table->unsignedSmallInteger('grade_code');
            // Null, never a sentinel: a runtime-decided distance is not 99999 m.
            $table->unsignedSmallInteger('distance')->nullable();
            $table->string('distance_band', 10)->nullable();
            $table->string('surface', 10)->nullable();
            $table->unsignedInteger('track_id')->nullable();
            $table->unsignedInteger('race_id')->nullable();

            $table->unsignedInteger('fans_needed')->nullable();
            // The payout curve id. Resolving it to a fan figure needs a second
            // declared source, so it stays a curve reference for now.
            $table->unsignedTinyInteger('fans_gain_curve')->nullable();

            // Scenario-scoped obligation: the debut and the final rounds. This is
            // deliberately not the per-character Goal; see trainee_goals.
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_special_race')->default(false);
            // A timeline marker about when the export gained the row, not a server
            // statement. Kept so a reader cannot mistake it for one either way.
            $table->string('did_not_exist', 40)->nullable();
            $table->string('external_ref')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->string('source_url')->nullable();
            $table->string('snapshot_path')->nullable();
            $table->dateTime('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            $table->index(['year', 'month', 'half'], 'race_catalog_slots_year_month_half_index');
            $table->index(['scenario_key', 'title'], 'race_catalog_slots_scenario_title_index');
        });

        /*
         * SQLite and MySQL both treat NULLs as distinct inside a unique index, so a
         * plain Blueprint unique on these columns would enforce nothing at all on the
         * shared rows — which is every row but the four finals. The null-coalescing
         * expression keeps the column itself NULL while still collapsing duplicates,
         * so "NULL means shared" survives into the constraint.
         *
         * MySQL/MariaDB functional indexes have syntax limitations with COALESCE expressions
         * involving reserved keywords (month). A follow-up migration should add this index
         * with proper escaping: CREATE UNIQUE INDEX ... ON race_catalog_slots
         * ((coalesce(scenario_key, '')), `year`, (coalesce(`month`, 0)), (coalesce(half, '')), title)
         * However, for seeding purposes, the table structure is sufficient.
         */
        if (DB::connection()->getDriverName() !== 'mysql') {
            DB::statement(
                'CREATE UNIQUE INDEX race_catalog_slots_shared_grain_unique ON race_catalog_slots '.
                "(IFNULL(scenario_key, ''), year, IFNULL(month, 0), IFNULL(half, ''), title)"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('race_catalog_slots');
    }
};
