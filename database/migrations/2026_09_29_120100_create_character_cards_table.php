<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Costume cards that reached `[Global]`, one row per card (PRD FR-A-6, ADR-0008).
 *
 * The trainee is modelled once, on `umamusume`; this table holds only what
 * differs between her forms, and `GametoraCharacterParser` collapsed the cards
 * away until now. Almost every column here is a source statement or a provenance
 * field about that statement, with two exceptions a fetch must not paper over:
 * `is_debut_form` is **derived by rule** (see its comment below — the export has
 * no debut field to copy, so a parser computes it and never stores a source
 * flag), and `is_manual` is neither kind of fact: it is the Trainer's own flag,
 * not engine data (PRD FR-B-4).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_cards', function (Blueprint $table): void {
            $table->id();
            // The source's own card id, not this table's primary key: it is what a
            // re-fetch matches on, so a fetch is idempotent by identity rather than
            // by name (PRD FR-B-5).
            $table->unsignedInteger('card_id')->unique();
            // ponytail: no index on umamusume_id beyond the FK. The table holds 105
            // cards and the catalog reads them in one whereIn; add the index when the
            // roster is a few thousand rows, not before.
            $table->foreignId('umamusume_id')->constrained('umamusume')->cascadeOnDelete();
            // Verbatim [Global] client string, brackets included. CONSTRAINTS.md:38
            // keeps such names as source data and puts the lore guard on the display
            // path, so this column is never a place to normalize copy.
            $table->string('title');
            $table->unsignedTinyInteger('rarity');
            // Required: a card with no Global date is not a row in this table at all.
            $table->date('global_release_date');
            // Derived from the earliest JP release among the trainee's cards, by the
            // same rule GametoraCharacterParser already applies to the debut form.
            $table->boolean('is_debut_form')->default(false);
            // Tier B alone is not enough: ADR-0008's Provenance section ("Tier B
            // data, Tier A witness") records the rule. A card whose
            // Global status did not reach two sources is stored flagged and hidden
            // by default, rather than dropped or quietly trusted.
            $table->boolean('unconfirmed')->default(false);
            /*
             * Inline provenance, the way the reference tables in this repo do it.
             * ADR-0003 Amendment R3 requires `source_url`, `snapshot_path`,
             * `fetched_at` and `source_timezone` on the reference row itself. Three of
             * the four existing reference tables carry all four (`scenario_races`,
             * `scenario_slots`, `race_catalog_slots`); `scenarios` predates the full set
             * and carries `source_url`, `fetched_at` and `is_manual` only, which is
             * `ADR-0004:50`'s own choice rather than a gap to copy. `data_sources` stays
             * what it always was: the character-level provenance table behind FR-A-4 and
             * the detail page's Provenance section. A card is not a character, and
             * borrowing the parent's provenance row would make "where did this release
             * date come from" a question with no row that answers it.
             */
            $table->string('source_url');
            $table->string('snapshot_path')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            // FR-B-4's stop sign at card grain. Borrowing the trainee's flag was the
            // first draft and it is wrong twice over: a Trainer may correct one card's
            // title without claiming her whole character, and every fetched reference
            // table here — `scenarios`, `scenario_races`, `scenario_slots`,
            // `race_catalog_slots` — carries its own.
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_cards');
    }
};
