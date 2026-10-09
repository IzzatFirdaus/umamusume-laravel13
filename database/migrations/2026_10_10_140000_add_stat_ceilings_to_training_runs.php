<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The run's own stat ceilings, as the Trainer computed them off the card and support layer.
 *
 * A JSON bag keyed by the stat matrix (`config('scenarios.stat_order')`), each entry a nullable
 * ceiling, sparse when a stat carries none. Entered, never derived: the tool's `ScenarioCaps` owns the
 * *scenario's* ceiling, but the run's real caps sit above it once the card's sparks and any
 * support-card Max-Stat layer are counted, and this repository holds neither input. So a stored bag
 * is the only place the composed ceiling can come from. Nullable, because a run that has not stated
 * it renders the N/A disclosure rather than a default (D-220).
 *
 * Cited requirement: the UX walk records that the wizard asks for a target, never a cap composition,
 * so the cap arithmetic's inputs have no field anywhere (docs/audits/rice-shower-unity-cup-ux-walk.md
 * line 177, citing §1.2 lines 39-56). Persistence only; the cockpit identity render is a later slice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('stat_ceilings')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('stat_ceilings');
        });
    }
};
