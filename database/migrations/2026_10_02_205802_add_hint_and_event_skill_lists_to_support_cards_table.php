<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The two skill lists a support card publishes (PRD FR-B; ADR-0014 at card grain).
 *
 * `2026_09_30_151945` settled the card's own columns and left the document's skill keys on the floor:
 * `GametoraSupportCardParser` kept eleven of the record's twenty-two keys and dropped `hints.hint_skills`
 * and `event_skills`. Those two are what a Trainer meets in the game — the skills a card's hints level up,
 * and the skills its story events grant — so their absence is a gap in the read, not a scope line.
 *
 * **Measured on the committed body (`support-cards.88dea522.json`, 559 records).** Both keys are present
 * on all 559. `hint_skills` is non-empty on 527 and an empty list on 32, carrying 3,971 ids over 379
 * distinct, at most 24 on one card. `event_skills` is non-empty on 555 and empty on 4, carrying 1,528 ids
 * over 495 distinct, at most 15 on one card. Every element of both is an integer, and every id resolves
 * against `skills.export_id` in `skills.609afe88.json` (unresolved 0 and 0). Of those, 3,200 hint ids and
 * 1,178 event ids name a skill `Skill::availableOnGlobal()` accepts, so the read side may not assume the
 * rest are linkable and counts what it cannot link instead of dropping it.
 *
 * **Nullable, and the null is not the empty list.** `[]` is the source stating the card hints no skills;
 * `null` is no list stored for the card at all. Collapsing the two would print "this card hints nothing"
 * over a row nobody read, which is the default-masquerading-as-fact D-220 refuses. The distinction is
 * reachable today only through a hand-seeded row, because the document carries both keys on every record,
 * and it is the reason the column is nullable rather than `json NOT NULL DEFAULT '[]'`.
 *
 * **`hints.hint_others` is deliberately not stored.** It is 1,171 well-formed `{hint_type, hint_value}`
 * pairs plus two malformed rows (one a bare list, one `{level, stats}`), and it names a hint economy this
 * tool has no surface for: no column reads `hint_type`, and rendering a numeric code as a stat word would
 * be the tool inventing vocabulary (D-20). Adding it is a widening ask, not a parser change.
 *
 * No index: both lists are read for one card at a time and never filtered on, which is also what keeps
 * `down()` cheap on SQLite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_cards', function (Blueprint $table): void {
            $table->json('hint_skills')->nullable();
            $table->json('event_skills')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('support_cards', function (Blueprint $table): void {
            $table->dropColumn(['hint_skills', 'event_skills']);
        });
    }
};
