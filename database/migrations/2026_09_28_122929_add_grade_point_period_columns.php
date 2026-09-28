<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * KI-10's schema half (US-10, ADR-0003).
     *
     * Grade Points are judged per period and surplus never carries over (D-232), so
     * two facts had nowhere to live: which period a finish counts toward, and which
     * period the Trainer says is live. Both are entered and neither is derived
     * (D-270): the sources name the four objectives and the points, and name no
     * formula that places a race or a career in one, so a tool that inferred the
     * period would be printing a guess as a progress bar.
     *
     * Ranges and the scenario-conditional rule are enforced in the models, not with a
     * CHECK here. `objective_index` is only meaningful when the run's scenario
     * composes grade objectives, and a column constraint cannot see the run's
     * scenario; `ScenarioSlot` sets the precedent for a saving guard carrying a rule
     * the schema cannot express. Both columns stay nullable, which is what makes
     * "the Trainer has not said yet" representable at all (D-220).
     */
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->unsignedTinyInteger('objective_index')->nullable();
        });

        Schema::table('training_runs', function (Blueprint $table): void {
            $table->unsignedTinyInteger('current_objective_index')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->dropColumn('objective_index');
        });

        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('current_objective_index');
        });
    }
};
