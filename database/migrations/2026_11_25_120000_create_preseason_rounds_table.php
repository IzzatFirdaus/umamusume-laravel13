<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * D-10 — per-round Unity Cup preseason outcome recording (owner-authorised 2026-11-25,
 * persistence design delegated to Backend).
 *
 * The client's Career Profile → Unity Cup panel prints one row per round (Round 1..4)
 * with whether it was held, the date it falls on and its result (`WIN` / loss / not
 * yet held). Until now the only stored fact was `team_preseason_wins`, a single 0..4
 * tally the Trainer reported — the per-round outcome was not modelled.
 *
 * This table records each round as its own row so a single round's outcome can be
 * entered, corrected and deleted without touching its siblings, and so an
 * explicitly-unrecorded round is a null `outcome` rather than a fabricated loss.
 *
 * `team_preseason_wins` is left in place as the legacy aggregate: a run imported
 * before this migration keeps its tally, and nothing here recomputes or migrates it.
 * The aggregate and the per-round rows are independent records of the same client
 * panel, not a derived pair.
 *
 * Additive and non-destructive: no existing column is dropped or rewritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preseason_rounds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_run_id')->constrained()->cascadeOnDelete();
            // 1..4. The four preseason rounds, in the order the client lists them.
            $table->unsignedTinyInteger('round');
            // 'WIN' or 'LOSS'; null = explicitly not recorded (D-220), never a silent loss.
            $table->string('outcome', 4)->nullable();
            $table->timestamps();

            // One row per round per run, so a correction updates in place instead of
            // stacking a second row for the same round.
            $table->unique(['training_run_id', 'round'], 'preseason_rounds_run_round_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preseason_rounds');
    }
};
