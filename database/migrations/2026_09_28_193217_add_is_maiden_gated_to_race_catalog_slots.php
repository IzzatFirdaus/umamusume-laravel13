<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carries the maiden lock across with the read path.
 *
 * `RaceSlotPanelComposerTest` covers a `maiden_locked` cell, and the grid rendered
 * it from `scenario_slots.is_maiden_gated`. Retargeting the calendar to
 * `race_catalog_slots` without an equivalent column would have deleted that state
 * and the test's coverage of it with it.
 *
 * The parser leaves every row false, which is what `f0ae288` seeds today, so this
 * column changes no rendering on its own. It exists because the export states the
 * rule globally rather than per row — "You can't participate in any races listed
 * here until you win either Debut or any of the Maiden Races" is attached to the
 * maiden row and describes every standard race — and deciding which reading the
 * grid should honour is a product call, not a migration's. Filed as a gap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->boolean('is_maiden_gated')->default(false)->after('is_mandatory');
        });
    }

    public function down(): void
    {
        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->dropColumn('is_maiden_gated');
        });
    }
};
