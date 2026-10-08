<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The deck slot's owned-or-rented state (`ADR-0023`, D3).
 *
 * `ADR-0014` recorded a deck as card *identity* with no ownership column, so the setup wizard carried
 * the flag in its session draft while the run-scoped builder printed the one value it could read. The
 * audit found the consequence: a card borrowed from another account became indistinguishable from an
 * owned one the moment the run existed, because nothing wrote the flag down.
 *
 * The fact is per run and per slot, so it lives on the slot. Nullable, and null means "not recorded"
 * rather than "owned": a run that predates this column, or a slot whose Trainer never said, holds no
 * flag, and defaulting it would state something nobody asserted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deck_slots', function (Blueprint $table): void {
            $table->string('ownership')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('deck_slots', function (Blueprint $table): void {
            $table->dropColumn('ownership');
        });
    }
};
