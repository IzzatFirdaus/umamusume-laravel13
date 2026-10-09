<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops `training_runs.stat_ceilings`, the persistence half of the UX walk's 2A.4.
 *
 * The render half it existed for was never landed: the cockpit cutover at `b30906a` deleted
 * `StatBand.vue` and `Runs/Show.vue`, and `CareerStatePanel.vue` states the visualization was
 * deliberately not reused. No reader of the column remains in `app/`, `resources/` or any test
 * outside its own persistence test, so the column is dropped rather than left as unconsumed
 * schema. The scenario's own ceiling is untouched — `ScenarioCaps` and its `stat_ceiling` band
 * arithmetic are a different owner (ADR-0015).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('stat_ceilings');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('stat_ceilings')->nullable();
        });
    }
};
