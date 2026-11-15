<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Team Rank letter as stored current state (R6-48; owner ruling 2026-11-15, recorded in
 * `docs/audits/browser-observation-2026-11-15-followup/08-r6-48-review-prep.md`).
 *
 * §1.8 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` calls the Team Info panel a "status
 * readout, not a performance record": the rank is the run's *current* league standing, so it is
 * one stored value rather than a per-turn history. The turn-event path (`TeamRankPayload`) stays
 * for the `UnityCupEditor` write route; `TrainingRun::latestTeamRank()` reads this column first
 * and falls back to the latest reported turn event, so the header strip, the Team panel and the
 * Dashboard tile cannot disagree about one rank.
 *
 * Vocabulary: `G,F,E,D,C,B,A,S,S+` (`TeamRankPayload::LETTERS`), validated at the model boundary
 * by the `saving` guard, not by a DB enum. `S+` is accepted here while `team_rank_ladder` still
 * maps it no facility level (it sits above S and grants a second hint). Nullable, because a run
 * that has not stated a rank renders the absence rather than a default (D-220).
 *
 * Cited requirement: PRD FR-C-1 (a run records its own team state), matching the authorisation
 * note `team_stat_grades` landed under.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->string('team_rank')->nullable()->after('current_spirit_proxy');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('team_rank');
        });
    }
};
