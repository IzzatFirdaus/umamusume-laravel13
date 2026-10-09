<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The facility levels a turn was trained at, from the UX walk's Phase 12.
 *
 * The audit's screen 9 recorded Speed Lv4, Stamina Lv4, Power Lv4, Guts Lv5 and Wit Lv4 with nowhere to
 * put them. Five nullable columns rather than one JSON blob because each is a small bounded integer
 * with the same 1..5 shape, and a per-column bound is the schema saying so rather than a reader
 * validating a payload.
 *
 * Null means the Trainer did not read that facility this turn, which is the ordinary case for a turn
 * logged from the rail rather than from a full screen reading.
 *
 * **The write path is not wired yet.** `TrainingRunController::turnAttributes()` whitelists the columns
 * a turn write accepts, and that file carries a concurrent session's staged hunks, so the whitelist
 * could not be extended in the same pass. The columns and the request rules land here; the one line
 * that lets a turn write them, and the form that would collect them, follow when the file is free.
 * The form is deliberately not shipped before the whitelist: it would post five fields the write
 * silently discards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->unsignedTinyInteger('facility_speed')->nullable();
            $table->unsignedTinyInteger('facility_stamina')->nullable();
            $table->unsignedTinyInteger('facility_power')->nullable();
            $table->unsignedTinyInteger('facility_guts')->nullable();
            $table->unsignedTinyInteger('facility_wit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('turn_entries', function (Blueprint $table): void {
            $table->dropColumn([
                'facility_speed',
                'facility_stamina',
                'facility_power',
                'facility_guts',
                'facility_wit',
            ]);
        });
    }
};
