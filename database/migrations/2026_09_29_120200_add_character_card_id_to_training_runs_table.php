<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            /*
             * Nullable and additive on purpose (FR-C-1 as amended by ADR-0008). A run
             * created before the card layer, or one where the Trainer named only the
             * trainee, is not a run missing data it should have had.
             *
             * Which key it targets is settled by precedent rather than preference:
             * `2026_09_28_191829` points `race_entries.race_catalog_slot_id` at `race_catalog_slots.id`
             * with `foreignId()->constrained()` even though that table also carries a source-side
             * unique grain. A foreign key names the row, the source id stays the thing a fetch
             * matches on, and the local id is durable because Task 7's store upserts by `card_id`
             * instead of recreating rows -- so `Rule::exists('character_cards','id')` in the run
             * request and `selectionId` in Task 12's payload all read the same key.
             */
            $table->foreignId('character_card_id')->nullable()->constrained('character_cards');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('character_card_id');
        });
    }
};
