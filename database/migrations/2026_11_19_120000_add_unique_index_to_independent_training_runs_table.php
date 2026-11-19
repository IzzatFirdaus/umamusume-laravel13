<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One overlay per run, stated by the database.
 *
 * `TrainingRun::independentTrainingRun()` is a `hasOne`, so the model already assumed at most one row;
 * nothing enforced it. `Lifecycle::canStart()` encoded the §1.1.7 one-at-a-time rule and had no
 * production caller, and two writes could therefore leave a run carrying two overlays with `hasOne`
 * resolving whichever row the query happened to return first.
 *
 * The index is the half that cannot be bypassed: a seeder, a console command or a test helper that
 * never reads `canStart()` still hits it. Scoped to every row rather than to the open ones, because a
 * run's session history belongs on one row — a completed session and a reopened one are two states of
 * the same career, and `complete()`/`abandon()` already refuse to re-stamp an ended row.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('independent_training_runs', 'independent_training_runs_training_run_id_unique')) {
            return;
        }

        Schema::table('independent_training_runs', function (Blueprint $table): void {
            $table->unique('training_run_id');
        });
    }

    public function down(): void
    {
        Schema::table('independent_training_runs', function (Blueprint $table): void {
            $table->dropUnique(['training_run_id']);
        });
    }
};
