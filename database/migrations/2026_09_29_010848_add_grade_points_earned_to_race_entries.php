<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KI-10's missing figure: what one race paid in Grade Points, stored on the race.
 *
 * `TrainingRun::gradeEarnedFor()` has priced Grade Points on every read from a slot's tier and the
 * finish's placement, so nothing on the row said what the race was worth, and a period holding one
 * unpriced finish withheld its total with no way to see which finish did it. This column is that
 * figure per row (PRD US-10, "I track race goals and predictions per run"; ADR-0003 `race_entries`).
 *
 * Null means "not priced", and it is doing real work rather than standing in for zero.
 * `docs/scenarios/05-trackblazer-gametora.md` §"Grade Points and Shop Coins — Exact Values" prices
 * a 1st place only (G1 100, G2 80, G3 60, OP 40, Pre-OP 20) and says the rest "scale down
 * proportionally" without naming a ratio: that is KI-10's open half. The same page's 100/60/30/0
 * placement table is Shop Coins, which that document says explicitly "do not depend on race grade at
 * all", so borrowing its ratios would put a wrong number under a real citation.
 *
 * No default, so a row written before this column existed reads as unpriced rather than as a zero
 * the client never paid — the same reason `energy`, `mood` and `fans` arrived nullable (ADR-0003).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->unsignedInteger('grade_points_earned')
                ->nullable()
                ->after('circles');
        });
    }

    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->dropColumn('grade_points_earned');
        });
    }
};
