<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Slice 8 T1 (US-10, ADR-0003). Two entered columns, no new table.
     *
     * `race_entries.circles` is the circle estimate the Trainer read off the client
     * before committing a Team Race. The tool records what was seen and never computes
     * it: `circles_per_category` is deliberately absent from config for the same reason
     * (D-225, Planner Rule 1). The 0..5 ceiling is this slice's owner ruling, not a
     * published game fact — the corpus names 3 circles as a safety margin and never a
     * maximum — so it is enforced as a validation bound and rendered as a number, not
     * as a meter of five.
     *
     * `training_runs.shop_resets_in` is the shop rotation countdown as the Trainer
     * reports it. Nullable because the rotation is not modelled by this build: a value
     * computed from a turn number would be a prediction (D-232), and null renders the
     * N/A disclosure rather than `0 turns`.
     *
     * Both ranges and the scenario-conditional rules are guards in the models, the same
     * way Slice 7 put the period rule in `TrainingRun::assertGradePeriod()`: a CHECK
     * cannot see the slot kind or the run's scenario.
     */
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->unsignedTinyInteger('circles')->nullable();
        });

        Schema::table('training_runs', function (Blueprint $table): void {
            $table->unsignedTinyInteger('shop_resets_in')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->dropColumn('circles');
        });

        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('shop_resets_in');
        });
    }
};
