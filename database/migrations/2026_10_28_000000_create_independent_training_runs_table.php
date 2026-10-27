<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UMAMUSUME_REFERENCE.md §1.1.7: 自主トレ育成 (Independent Training) is an idle mode — commit a
 * lineup, close the app if you want, and the run plays itself out over a fixed 50 minutes. It is
 * entered from its own tab on the pre-training confirmation screen, where the Trainer fixes three
 * things before walking away: a training policy (バランス重視 / スタミナ重視 / スプリント重視), an
 * optional custom race rotation that defaults to the scenario's target races, and up to ten priority
 * skill hints that steer which event skills the run takes.
 *
 * The row records those three inputs, the 50-minute window (`started_at` -> `completes_at`), and the
 * two ways the run ends (`completed_at` or `abandoned_at`). §1.1.7's limits are structural, not stored:
 * a race win inside the mode awards no trophy and unlocks no Winning Live / Gallery content, Career
 * events and concerts are not viewable, and skills that evolve off rest counts do not advance — so no
 * trophy, gallery, concert or rest-evolution column belongs here.
 *
 * `policy` is a string and validated at the model layer, matching `scenario_slots.kind`: SQLite does
 * not support `CHECK` via Blueprint, so an enum column would be a varchar with no constraint and a
 * model cast does the real work.
 *
 * Cited requirement: the mode is the reference's own (§1.1.7, §1.6.8), not a PRD story; the closest
 * PRD anchor is US-10 (race calendar and fan gating). Flagged to the owner in the report.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('independent_training_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained()->cascadeOnDelete();
            $table->string('policy'); // balanced | stamina | sprint, model-validated
            $table->json('priority_skills')->nullable(); // max 10 skill ids
            $table->json('rotation')->nullable();        // null = goal races only
            $table->timestamp('started_at');
            $table->timestamp('completes_at');           // started_at + 50 minutes
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();

            $table->index(['training_run_id', 'completes_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('independent_training_runs');
    }
};