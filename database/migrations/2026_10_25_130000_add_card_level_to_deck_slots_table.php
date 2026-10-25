<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-run snapshot per ADR-0029 D1: the level the equipped card was at
 * in this run. The cap and the limit-break count are collection state
 * declined by ADR-0014 and are not stored here. Unity Cup teammate
 * stats are not card-level dependent; card level matters for support
 * effects only (UMAMUSUME_REFERENCE.md §1.4.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deck_slots', function (Blueprint $table): void {
            $table->unsignedSmallInteger('card_level')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('deck_slots', function (Blueprint $table): void {
            $table->dropColumn('card_level');
        });
    }
};
