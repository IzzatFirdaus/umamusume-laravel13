<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umamusume', function (Blueprint $table): void {
            /*
             * GametoraCharacterParser has emitted `gametora:char:{id}` since it was
             * written and no catalog row stored it — `match_candidates.external_ref`
             * keeps it, but only for a record still sitting in the review queue
             * (app/Services/DataPipeline/PipelineRunner.php:92-94) — so a promoted row
             * lost its only durable link to the source (ADR-0008). Cards attach
             * through this rather than by re-matching on name: the name is what the
             * match engine refuses to guess about, the char id is what the source
             * asserts.
             */
            $table->string('external_ref')->nullable()->index();
        });
    }

    public function down(): void
    {
        // SQLite will not drop a column its index still names — it fails with
        // "error in index umamusume_external_ref_index after drop column". The
        // index goes first, in its own Schema::table call, the way
        // 2026_09_28_180544_add_source_key_to_scenario_slots.php:down() does it.
        Schema::table('umamusume', function (Blueprint $table): void {
            $table->dropIndex(['external_ref']);
        });

        Schema::table('umamusume', function (Blueprint $table): void {
            $table->dropColumn('external_ref');
        });
    }
};
