<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Four corrections to the support-card tables, each forced by checking the schema against the export
 * it is meant to hold rather than against the intent behind it.
 *
 * 1. THE CHECK CONSTRAINTS DID NOT EXIST. `2026_09_30_142618` writes `->check(...)` on four column
 *    definitions. In Laravel 13's SQLite grammar that call is a silent no-op: it is a column modifier
 *    the SQLite dialect never renders, and `Blueprint` has no table-level `check()` method at all
 *    (`BadMethodCallException`). The DDL that migration actually produced contains no `CHECK` token in
 *    any of the three tables. That is why a `slot_position` of 7 inserted without complaint, and it is
 *    why the constraint has to be written in raw SQL, where SQLite does emit and does enforce it.
 *    A constraint that cannot fail is worse than no constraint, because it reads as a guarantee.
 *
 * 2. `char_id` WAS DECLARED AS A FOREIGN KEY TO `umamusume.id` AND IT IS NOT ONE. The value spaces are
 *    different kinds of number: `umamusume.id` is an autoincrement surrogate over 268 rows, so it holds
 *    1..268 while the source's character id runs 1001..1149 and lives in `umamusume.external_ref`. A card's
 *    `char_id` of 1001 is tested against a key that can only ever hold 1, and the constraint rejects
 *    trainable cards as readily as staff cards.
 *    Separately, 23 of the 559 export records carry a `char_id` in the 9000 block (Tazuna Hayakawa 9001,
 *    Aoi Kiryuin 9004, Darley Arabian 9040, Speed Symboli 9047) while the document that feeds `umamusume`
 *    is `gametora-characters.e9e9ee6d.json` — 268 records, `char_id` 1001 to 1149, none in the 9000 block —
 *    so those characters never enter the trainable catalogue.
 *    *[Corrected 2026-10-01: this note first cited `characters.json`, which is not the trainee feed and does
 *    hold 17 ids above 9000. The range was taken against an `id` key that file does not have, so every row
 *    read back as zero and the gap looked total.]*
 *    Those 23 records are also precisely the records the Scenario Link derivation needs: 10 of the 13
 *    scenarios in `scenarios.json` list a 9000-block id in `scenario_linked_characters`, so nulling those
 *    `char_id`s would delete the only evidence the badge is derived from. The shape this table should have copied is `character_cards`,
 *    which keeps the export's own `card_id` as a plain column and adds a separate `umamusume_id` only
 *    where a local join is genuinely wanted. None is added here: nothing in this slice reads a support
 *    card's trainee through the local catalogue, and a column with no consumer is a second source of
 *    truth for a fact the row already stores.
 *
 * 3. `name` IS DROPPED AND `char_name` TAKES ITS PLACE. `support-cards.json` has no `name_en` field. It
 *    has `char_name` ("Special Week") and `title_en` ("[Tracen Academy]") and nothing between them, so
 *    populating a stored `name` would mean inventing a composed string the source does not publish,
 *    which fails the floor that no fact is stored without provenance. The label a Trainer reads is
 *    composed at the view boundary by `SupportCard::displayName()`.
 *
 * 4. `support_effects.calc` PERMITTED VALUES THE SOURCE NEVER EMITS. The CHECK allowed
 *    `flat`, `mult`, `add`, `level`. Across all 35 records in `support_effects.json` the actual domain is
 *    `mult` (3 records), `add` (1) and absent (31). `flat` and `level` are this repository's own prose
 *    for the non-declaring effects (UMAMUSUME_REFERENCE.md §1.4.8), not export values, and the distinction
 *    is load-bearing: §1.4.8's finding is that exactly the effects declaring `calc` combine
 *    multiplicatively, so a stored `flat` would claim a fourth combining mode the client does not have.
 *    The rebuilt CHECK admits `mult` and `add` or nothing.
 *
 * Each table is rebuilt by create-copy-swap rather than `drop` and `create` so that a populated table
 * survives the migration. `PRAGMA foreign_keys` is turned off for the swap because dropping a parent
 * table would otherwise fire the children's `ON DELETE RESTRICT`; it is restored afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        $this->rebuildSupportEffects();
        $this->rebuildSupportCards();
        $this->rebuildDeckSlots();

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        // Recreate the shape `2026_09_30_142618` actually produced: `support_cards` with `name` and the
        // `umamusume` foreign key, and no CHECK on any of the three tables, because that migration's
        // `->check(...)` calls were no-ops. A rollback returns the database to the state that migration
        // left rather than to a stricter one.

        DB::statement('DROP TABLE IF EXISTS deck_slots_rb');

        DB::statement(<<<'SQL'
            CREATE TABLE deck_slots_rb (
                "id" integer primary key autoincrement not null,
                "training_run_id" integer not null,
                "support_card_id" integer not null,
                "slot_position" integer not null,
                "created_at" datetime,
                "updated_at" datetime,
                foreign key("training_run_id") references "training_runs"("id") on delete cascade,
                foreign key("support_card_id") references "support_cards"("id") on delete restrict,
                constraint "deck_slots_rb_run_position_unique"
                    unique ("training_run_id", "slot_position")
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO deck_slots_rb
                (id, training_run_id, support_card_id, slot_position, created_at, updated_at)
            SELECT id, training_run_id, support_card_id, slot_position, created_at, updated_at
            FROM deck_slots
        SQL);

        DB::statement('DROP TABLE deck_slots');
        DB::statement('ALTER TABLE deck_slots_rb RENAME TO deck_slots');

        DB::statement('DROP TABLE IF EXISTS support_effects_rb');

        DB::statement(<<<'SQL'
            CREATE TABLE support_effects_rb (
                "id" integer primary key autoincrement not null,
                "effect_id" integer not null,
                "name_en" varchar not null,
                "name_ja" varchar,
                "calc" varchar,
                "symbol" varchar,
                "description_en" text,
                "source_url" varchar not null,
                "fetched_at" datetime not null,
                constraint "support_effects_rb_effect_id_unique" unique ("effect_id")
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO support_effects_rb
                (id, effect_id, name_en, name_ja, calc, symbol, description_en, source_url, fetched_at)
            SELECT id, effect_id, name_en, name_ja, calc, symbol, description_en, source_url, fetched_at
            FROM support_effects
        SQL);

        DB::statement('DROP TABLE support_effects');
        DB::statement('ALTER TABLE support_effects_rb RENAME TO support_effects');

        DB::statement('DROP TABLE IF EXISTS support_cards_rb');

        DB::statement(<<<'SQL'
            CREATE TABLE support_cards_rb (
                "id" integer primary key autoincrement not null,
                "support_id" integer not null,
                "char_id" integer,
                "name" varchar not null,
                "name_ja" varchar,
                "title_en" varchar,
                "title_ja" varchar,
                "rarity" integer not null,
                "type" varchar not null,
                "release_jp" date,
                "release_global" date,
                "release_status" varchar as (CASE
                    WHEN release_global IS NOT NULL THEN 'Global'
                    WHEN release_jp IS NOT NULL THEN 'JP-only'
                    ELSE 'Unreleased'
                END) stored,
                "effects" text not null default '[]',
                "source_url" varchar not null,
                "fetched_at" datetime not null,
                "is_manual" tinyint(1) not null default '0',
                "created_at" datetime,
                "updated_at" datetime,
                constraint "support_cards_rb_support_id_unique" unique ("support_id"),
                foreign key("char_id") references "umamusume"("id") on delete set null
            )
        SQL);

        // The column `up()` removed is rebuilt by composing the two the export does publish. That is the
        // join `up()` calls an invention, so `down()` makes it only to fit the rows into the older
        // shape; the value is display text, not source data, and rolling back forward again re-nulls it.
        DB::statement(<<<'SQL'
            INSERT INTO support_cards_rb
                (id, support_id, char_id, name, name_ja, title_en, title_ja, rarity, type,
                 release_jp, release_global, effects, source_url, fetched_at, is_manual,
                 created_at, updated_at)
            SELECT id, support_id,
                   CASE WHEN char_id IN (SELECT id FROM umamusume) THEN char_id ELSE NULL END,
                   COALESCE(char_name, name_ja, 'Card ' || support_id) || COALESCE(' ' || title_en, ''),
                   name_ja, title_en, title_ja, rarity, type,
                   release_jp, release_global, effects, source_url, fetched_at, is_manual,
                   created_at, updated_at
            FROM support_cards
        SQL);

        DB::statement('DROP TABLE support_cards');
        DB::statement('ALTER TABLE support_cards_rb RENAME TO support_cards');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function rebuildSupportEffects(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE support_effects_new (
                "id" integer primary key autoincrement not null,
                "effect_id" integer not null,
                "name_en" varchar not null,
                "name_ja" varchar,
                "calc" varchar check ("calc" in ('mult', 'add')),
                "symbol" varchar,
                "description_en" text,
                "source_url" varchar not null,
                "fetched_at" datetime not null,
                constraint "support_effects_new_effect_id_unique" unique ("effect_id")
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO support_effects_new
                (id, effect_id, name_en, name_ja, calc, symbol, description_en, source_url, fetched_at)
            SELECT id, effect_id, name_en, name_ja, calc, symbol, description_en, source_url, fetched_at
            FROM support_effects
        SQL);

        DB::statement('DROP TABLE support_effects');
        DB::statement('ALTER TABLE support_effects_new RENAME TO support_effects');
    }

    private function rebuildSupportCards(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE support_cards_new (
                "id" integer primary key autoincrement not null,
                "support_id" integer not null,
                "char_id" integer,
                "char_name" varchar,
                "name_ja" varchar,
                "title_en" varchar,
                "title_ja" varchar,
                "rarity" integer not null check ("rarity" in (1, 2, 3)),
                "type" varchar not null
                    check ("type" in ('speed','stamina','power','guts','intelligence','friend','group')),
                "release_jp" date,
                "release_global" date,
                "release_status" varchar as (CASE
                    WHEN release_global IS NOT NULL THEN 'Global'
                    WHEN release_jp IS NOT NULL THEN 'JP-only'
                    ELSE 'Unreleased'
                END) stored,
                "effects" text not null default '[]',
                "source_url" varchar not null,
                "fetched_at" datetime not null,
                "is_manual" tinyint(1) not null default '0',
                "created_at" datetime,
                "updated_at" datetime,
                constraint "support_cards_new_support_id_unique" unique ("support_id")
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO support_cards_new
                (id, support_id, char_id, char_name, name_ja, title_en, title_ja, rarity, type,
                 release_jp, release_global, effects, source_url, fetched_at, is_manual,
                 created_at, updated_at)
            SELECT id, support_id, char_id, NULL, name_ja, title_en, title_ja, rarity, type,
                   release_jp, release_global, effects, source_url, fetched_at, is_manual,
                   created_at, updated_at
            FROM support_cards
        SQL);

        DB::statement('DROP TABLE support_cards');
        DB::statement('ALTER TABLE support_cards_new RENAME TO support_cards');
    }

    private function rebuildDeckSlots(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE deck_slots_new (
                "id" integer primary key autoincrement not null,
                "training_run_id" integer not null,
                "support_card_id" integer not null,
                "slot_position" integer not null check ("slot_position" between 1 and 6),
                "created_at" datetime,
                "updated_at" datetime,
                foreign key("training_run_id") references "training_runs"("id") on delete cascade,
                foreign key("support_card_id") references "support_cards"("id") on delete restrict,
                constraint "deck_slots_new_run_position_unique"
                    unique ("training_run_id", "slot_position")
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO deck_slots_new
                (id, training_run_id, support_card_id, slot_position, created_at, updated_at)
            SELECT id, training_run_id, support_card_id, slot_position, created_at, updated_at
            FROM deck_slots
        SQL);

        DB::statement('DROP TABLE deck_slots');
        DB::statement('ALTER TABLE deck_slots_new RENAME TO deck_slots');
    }
};
