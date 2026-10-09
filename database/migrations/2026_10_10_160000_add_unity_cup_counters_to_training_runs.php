<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The run's Unity Cup progression counters: Unity Trainings run, Spirit Bursts and Extreme Spirit
 * Bursts earned.
 *
 * All three are entered, never derived: the cockpit's Scenario block prints Team Rank and Spirit
 * Bursts from the run's own record, and nothing in this repository counts a training or a burst. A
 * plain tally, not a modelled mechanic, so the columns hold the number the Trainer reports.
 * Nullable, because a run that has not stated one renders the N/A disclosure rather than a default
 * (D-220).
 *
 * Cited requirement: the UX walk records the Team Info panel's "55 Unity Trainings, 6 Spirit Bursts,
 * 5 Extreme Spirit Bursts" as source data with no field (docs/audits/rice-shower-unity-cup-ux-walk.md
 * line 932, citing §1.8). Persistence only; the cockpit identity render is a later slice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->unsignedTinyInteger('unity_trainings_count')->nullable();
            $table->unsignedTinyInteger('spirit_bursts_count')->nullable();
            $table->unsignedTinyInteger('extreme_bursts_count')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn(['unity_trainings_count', 'spirit_bursts_count', 'extreme_bursts_count']);
        });
    }
};
