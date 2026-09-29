<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KI-17's first sanctioned fix: a link from a race entry to the turn it happened on. Cited to
 * PRD US-10 ("As a Trainer, I track race goals and predictions per run", `race_entries` per
 * ADR-0003), which authorises the race record this column belongs to.
 *
 * D-230 surfaces Trackblazer's Race Fatigue on the premise that the consecutive-race count is
 * "already recoverable from `turn_entries`". It was not: `race_entries` pointed at a calendar slot
 * (month, half, tier) and never at a turn, so no logged turn could be identified as a race turn.
 * This column is the link that was missing.
 *
 * It is entered, not derived (D-270): the Trainer names the turn in the race panel, the same way
 * they name the Grade Point period and the circles read. Nothing here infers a turn from a race
 * date, which is the guess KI-17 refuses to make.
 *
 * Nullable, and `nullOnDelete`: a race the Trainer has not tied to a turn is a complete row, and
 * deleting the turn it pointed at must not reach through and delete the race record with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->foreignId('turn_entry_id')
                ->nullable()
                ->after('race_catalog_slot_id')
                ->constrained('turn_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('turn_entry_id');
        });
    }
};
