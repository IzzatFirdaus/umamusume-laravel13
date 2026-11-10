<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Spirit gauge's labelled manual proxy (KI-133, ruled 2026-11-10 per the S-RS-COMPLETE-1
 * dispatch: the run-level `current_spirit` integer ADR-0029 B-0.6 authorised is not the game's
 * shape, and the honest per-member `spirit_gauges` table stays blocked on the roster R8 declined).
 *
 * The column is a MANUAL PROXY, NOT A GAME VALUE. The gauge is per team member, has no published
 * numeric maximum, and the burst is a once-per-member availability (UMAMUSUME_REFERENCE.md §2.2.2,
 * 2026-10-25 note). One Trainer-entered aggregate for the whole team is what this stores, and the
 * display labels it "Spirit (manual proxy)" with the reason, per the no-invented-figures rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->integer('current_spirit_proxy')->nullable()->after('extreme_bursts_count');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropColumn('current_spirit_proxy');
        });
    }
};
