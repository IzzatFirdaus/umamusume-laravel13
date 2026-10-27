<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UMAMUSUME_REFERENCE.md §1.2.6: the maiden lock is a run-level state, not a per-race column.
 *
 * The export states the rule once, on the maiden row — "You can't participate in any races listed
 * here until you win either Debut or any of the Maiden Races" — and never per race. A schema that
 * models it as a per-row `is_maiden_gated` column has invented a field the source does not carry per
 * row, and a row-level flag will not fire against a winless run the way the client's own gate does.
 *
 * All three columns were already nullified by their writers (the parser seeds false, the seeder seeds
 * false), and the live read path now uses `TrainingRun::maidenGates()` — `hasClearedMaidenLock()` (won
 * Debut grade 900 or Maiden grade 800, placement 1) applied to standard grades 100–700 — so the columns
 * are dead weight and dropped.
 *
 * Cited requirement: US-10 (race calendar and fan gating). Decision: the reference wins over the prior
 * per-race model; no ADR carries the opposite rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scenario_slots', function (Blueprint $table): void {
            $table->dropColumn('is_maiden_gated');
        });

        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->dropColumn('is_maiden_gated');
        });

        Schema::table('scenario_races', function (Blueprint $table): void {
            $table->dropColumn('maiden_gated');
        });
    }

    public function down(): void
    {
        Schema::table('scenario_slots', function (Blueprint $table): void {
            $table->boolean('is_maiden_gated')->default(false);
        });

        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->boolean('is_maiden_gated')->default(false);
        });

        Schema::table('scenario_races', function (Blueprint $table): void {
            $table->boolean('maiden_gated')->default(false);
        });
    }
};