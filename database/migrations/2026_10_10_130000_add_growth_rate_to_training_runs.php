<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The trainee's growth-rate row, as the Trainer reads it off the card (the `+0/+10/+0/+20/+0` line).
 *
 * A JSON bag keyed by the stat matrix (`config('scenarios.stat_order')`), each entry a nullable
 * percentage, sparse when a stat carries no growth. Entered, never derived: the catalogue holds no
 * growth figure (`TraineeSelectController` omits the filter for exactly that reason, and
 * `docs/research-scratch/DESIGN-CORPUS.md:727-728` forbids rendering one), so a stored row is the
 * only place this number can come from. Nullable, because a run that has not stated it renders the
 * N/A disclosure rather than a default (D-220).
 *
 * Cited requirement: the UX walk's trainee-select and build-target screens list the growth row as
 * source data with no UI home (docs/audits/rice-shower-unity-cup-ux-walk.md lines 181 and 912).
 * Persistence only; the cockpit identity render is a later slice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->json('growth_rate')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('growth_rate');
        });
    }
};
