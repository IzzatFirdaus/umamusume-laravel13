<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A race entry can point at one of two things, and they are different things.
 *
 * `race_catalog_slot_id` is a slot in the shared career calendar — a race the game
 * offers at a given year and turn. `scenario_slot_id` stays for what only the
 * scenario or the Trainer authors: a Unity Cup team race round, and the `free_race`
 * rows a Trainer types in by hand, which have no catalogue row to point at.
 *
 * Both are nullable and both stay nullable. An entry with neither is a logged race
 * with no slot behind it, which R2 already decided is legitimate for Trackblazer's
 * self-paced racing, so neither column may become required here.
 *
 * This adds a column and does not rename anything. `ScenarioSlotMigrationTest:262`
 * asserts that the FK to `scenario_slots` exists; renaming `scenario_slot_id` away
 * would have broken a check on a table this pass does not own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->foreignId('race_catalog_slot_id')
                ->nullable()
                ->after('scenario_slot_id')
                ->constrained('race_catalog_slots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('race_catalog_slot_id');
        });
    }
};
