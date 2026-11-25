<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * D03: the recurring team-race countdown, recorded by the Trainer.
 *
 * `shop_resets_in` is the precedent this column follows exactly (Slice 8 T1, `2026_09_28_143228`):
 * a countdown the Trainer reports off the client, nullable, and bounded by the scenario's own
 * published period rather than by a number this tool invented.
 *
 * What is different, and why this column is the team-race countdown and not a second shop counter:
 *
 *  - **The shop's period is published.** `config('scenarios.scenarios.*.shop.rotation_turns')` is a
 *    real figure a source states, so a recorded countdown can be checked against it and a misread
 *    refused. The team race's period is not: `team_race.occurs_every_months` is six and
 *    `month_anchors` are Late June and Late December, but no source publishes the career turn a round
 *    falls on (`SCENARIO-PUBLISHER-REFERENCES.md:1186` records that as UNVERIFIED, and
 *    `config/scenarios.php` says the same on the `team_race` block's own comment). So there is no
 *    rotation figure to bound this column against, and none is invented here.
 *
 *  - **The countdown is still recorded.** The owner approved a recurring team-race countdown contract,
 *    and an approved requirement does not evaporate because the schedule is unpublished. The
 *    Trainer's observed value is the record; null is the honest "not observed". The two are not the
 *    same fact and this column never claims to be a prediction.
 *
 *  - **Zero is a real reading.** A countdown of 0 means the round is this turn, which is distinct
 *    from null (D-220: absence is not zero). The column is nullable precisely so 0 and unknown stay
 *    separate.
 *
 *  - **It is not the finals countdown.** The finals live in `FinaleReader::forRun()` and
 *    `TrainerAdvisor::finaleContext()`, which derive the distance from the 72-turn grid's end — a
 *    derivation the team race cannot have, because its anchor is a calendar month with no published
 *    turn. Keeping the two in separate columns and separate builders is what stops the Cockpit from
 *    printing one number for two different events.
 *
 *  - **It is not the Spirit Burst countdown.** That is `TeamPanelBuilder::burstCountdown()`, which
 *    reads the burst tallies and the band table. This column records a team race, not a burst.
 *
 *  - **It is not the generic scenario milestone.** `CareerPosition::scenarioCountdown` is the
 *    turns-until-a-scenario-milestone figure a snapshot may carry, and it stays exactly that. A
 *    historical `scenario_countdown` value is not reinterpreted as a team-race countdown here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->unsignedTinyInteger('team_race_countdown')->nullable()->after('shop_resets_in');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('team_race_countdown');
        });
    }
};
