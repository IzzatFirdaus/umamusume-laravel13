<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The per-placement fan payout the client pays for a race, from the UX walk's screen 13.
 *
 * The walk recorded the source's per-race fan rewards (`6,700` for the win) with nowhere to put
 * them: the catalogue stored the payout *curve* id and no table resolving those curves, so the Fan
 * gain row could only say the figure could not be read off. These three columns hold the resolved
 * payout for first, second and third, where a source states it.
 *
 * **Not backfilled.** A row with no sourced payout stays null, and the screen prints its absence
 * rather than a zero: a zero would read as "this race pays nothing".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->unsignedInteger('fans_first')->nullable();
            $table->unsignedInteger('fans_second')->nullable();
            $table->unsignedInteger('fans_third')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('race_catalog_slots', function (Blueprint $table): void {
            $table->dropColumn(['fans_first', 'fans_second', 'fans_third']);
        });
    }
};
