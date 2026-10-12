<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The four stat ceilings a Trainer enters for one run (`ADR-0025`, PRD FR-C-1 and FR-C-2).
 *
 * `ScenarioCaps` owns the *scenario's* ceiling: `base_cap` plus that scenario's bonus, clamped to the
 * engine `hard_cap` (`ADR-0015`). The Rice Shower / Unity Cup run report measured ceilings above that
 * arithmetic — Speed 1325, Stamina 1322, Power 1368 and Guts 1308 on a scenario floor of 1300 — and the
 * composition behind the four extras does not reconcile with the run's six blue sparks, so no formula
 * in this repository can produce them. The owner's ruling is to record what the client shows rather than
 * derive it: each column holds the ceiling as read, and null holds the scenario's own answer.
 *
 * Four columns, not five: Wit's scenario bound is 1800 and no ruled surface writes it, so a fifth column
 * would be schema for a fact nobody may enter.
 *
 * This is the second attempt at storing a run's ceiling. `2026_10_10_140000` added a `stat_ceilings` json
 * bag with the same "entered, never derived" reasoning and `2026_10_10_170000` dropped it, because the
 * render half never landed and no reader of the column remained. This one ships with its reader: the
 * ceiling owner reads it, both entry forms write it, and `ScenarioCapsTest`/`EnteredStatCapsTest` pin both.
 *
 * No backfill and no `data_sources` row. A run that stated no ceiling keeps stating none, and the fetch
 * engine never touches these columns (`AGENTS.md` §8): they are Trainer data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->unsignedSmallInteger('cap_speed')->nullable();
            $table->unsignedSmallInteger('cap_stamina')->nullable();
            $table->unsignedSmallInteger('cap_power')->nullable();
            $table->unsignedSmallInteger('cap_guts')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn(['cap_speed', 'cap_stamina', 'cap_power', 'cap_guts']);
        });
    }
};
