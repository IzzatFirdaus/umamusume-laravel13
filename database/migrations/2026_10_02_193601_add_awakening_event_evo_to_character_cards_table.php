<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The three remaining skill lists a trainee's card publishes (KI-33; PRD FR-A-6, ADR-0008 at card grain).
 *
 * The 2026-09-30 migration added `skills_innate` and `skills_unique` and left the card document's other
 * three `skills_*` keys on the floor: `GametoraCharacterCardParser` keeps those two and drops
 * `skills_awakening`, `skills_event` and `skills_evo`. Those three are the associations a Trainer meets
 * in the game — what awakening levels grant, what events give, what a skill evolves into — so their
 * absence is a real gap rather than a scope line.
 *
 * **Measured on the committed card body (`gametora-characters.e9e9ee6d.json`, 268 records).**
 * `skills_awakening` is present on 268 records, `skills_event` on 267, `skills_evo` on 268.
 *
 * **`skills_evo` is a list of pairs, not a list of ids.** Each entry is `{new, old}` — the evolved
 * skill id and the base skill id it replaces, e.g. `{"new": 100101111, "old": 201351}`. A flat int
 * list would lose the direction, which is the whole content of the field, so it is stored as json.
 *
 * **`skills_awakening_en` is deliberately not stored.** The document carries it on 5 of 268 records
 * only, so it is a partial translation rather than the field's Global form, and nothing here may treat
 * a sparse third-party string as the client's list.
 *
 * Resolution measured against the same skills body: every awakening, event and evo id resolves to a
 * `skills.export_id` (unresolved 0, 1 and 0), and 760 of 1,082 awakening ids, 1,110 of 1,273 event ids
 * and 421 of 1,250 evo ids name a `[Global]`-released skill. The read side therefore drops unresolved
 * ids and does not assume the rest are Global.
 *
 * Nullable, with the same reasoning as the sibling migration: a card the document gives no list for is
 * a real card, and null means "nothing to read" rather than an empty list. No index, because the lists
 * are read for one card at a time and never filtered on — which is also what keeps `down()` cheap on
 * SQLite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_cards', function (Blueprint $table): void {
            $table->json('skills_awakening')->nullable();
            $table->json('skills_event')->nullable();
            $table->json('skills_evo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('character_cards', function (Blueprint $table): void {
            $table->dropColumn(['skills_awakening', 'skills_event', 'skills_evo']);
        });
    }
};
