<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The run's team identity under Unity Cup: the team's name, motto, league placement and preseason
 * rounds won.
 *
 * All four are entered, never derived: the scenario panel reads them off the run's own screen, and no
 * table in this repository holds a team. Nullable, because a run that has not stated one renders the
 * N/A disclosure rather than a default (D-220). `team_preseason_wins` is the count of the four
 * preseason rounds won; the round itself is not modelled here, only the tally the Trainer reports.
 *
 * Cited requirement: the UX walk records the cockpit's Scenario block carrying Team Rank and Spirit
 * Bursts but no team name, motto, league placement or preseason tally (docs/audits/rice-shower-unity-cup-ux-walk.md
 * lines 394-396, citing §1.6 lines 169-172). Persistence only; the cockpit identity render is a later
 * slice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->string('team_name')->nullable();
            $table->string('team_motto')->nullable();
            $table->unsignedTinyInteger('team_league_placement')->nullable();
            $table->unsignedTinyInteger('team_preseason_wins')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn(['team_name', 'team_motto', 'team_league_placement', 'team_preseason_wins']);
        });
    }
};
