<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Enums\CardRarity;
use App\Models\SupportCard;
use App\Services\DataPipeline\Contracts\SupportCardSourceParser;
use JsonException;

/**
 * Reads the GameTora support-card dataset into `support_cards` reference rows (ADR-0014; PRD FR-B).
 *
 * Measured on the document as published at manifest hash `88dea522` (559 records, committed as
 * `database/seeders/data/support-cards.88dea522.json`): type guts 111 / speed 125 / power 99 /
 * stamina 97 / intelligence 99 / friend 23 / group 5, rarity 1 → 146, 2 → 101, 3 → 312, and 251
 * records carrying a `release_en`. Every key named below is the document's own spelling.
 *
 * **All 559 records import, including the 308 the `[Global]` server has not received.** The
 * character-card parser at the same publisher drops what Global lacks; this one does not, because
 * `support_cards.release_status` is a stored generated column over `release_jp` and `release_global`
 * and its whole purpose is to state availability per row rather than to pre-filter it away. Dropping
 * rows here would put a second, silent copy of that decision in the parser, where a later reader
 * could not tell a card Global has not shipped from a card nobody imported.
 *
 * **`char_name` and `name_ja` are two fields, not one, and `name_ja` comes from `name_jp`.** The
 * export publishes no composed card name: it carries `char_name` ("Special Week") and `title_en`
 * ("[Tracen Academy]") and nothing between them, so `SupportCard::displayName()` composes the label at
 * the view boundary and no stored column invents one (migration `2026_09_30_151945` point 3). The
 * Japanese name sits under `name_jp` here, while the skill document spells the same thing `jpname`;
 * reading a key that is not there yields null on every row and looks like an empty source, which is
 * KI-23.
 *
 * **`char_id` is kept verbatim, including the 9000 block.** 23 of the 559 records carry a `char_id` in
 * the 9000 block, across 12 distinct staff ids (Tazuna Hayakawa 9001, Aoi Kiryuin 9004, the group
 * cards at 9040 and 9047, and eight more), and `characters.json` holds no id in that range at all, so
 * these characters are not in the trainable catalogue. The value is GameTora's own character space, not a
 * local surrogate key, which is why no foreign key exists on the column and none is resolved here
 * (migration `2026_09_30_151945` point 2). Those same rows are the ones the Scenario Link badge is
 * derived from, so dropping them would delete the evidence rather than an anomaly.
 *
 * **The anchors are stored as they arrive.** Each `effects` row is exactly twelve wide, the effect id
 * followed by the values at card levels 1, 5, 10, 15, 20, 25, 30, 35, 40, 45 and 50, and `-1` means
 * the client holds no entry at that level rather than that the effect is zero
 * (UMAMUSUME_REFERENCE.md §1.4.7). Interpolating here would materialise the ladder ADR-0014
 * correction 4 refuses, and rewriting `-1` to null or 0 would destroy the only marker the floor rule
 * reads. A row of any other width is dropped, because the vector is positional: a three-wide row would be
 * read as levels 1, 5 and 10 and print a wrong number with confidence. Measured: 5,114 anchor rows in
 * the document, all twelve wide, none dropped.
 *
 * **`type` and `rarity` are checked against the domains the columns can hold, and a record outside one
 * is not emitted.** The CHECK written by `2026_09_30_151945` refuses both, and it refuses them inside
 * the store's transaction, so one record outside the domain would cost the Trainer all 559.
 * `SupportCard::TYPES` is the list the migration, this parser and the picker are required to agree on,
 * and it is read from the model rather than copied here so the agreement is structural. An eighth type
 * is a source change the schema has to be told about, not a row to guess a label for.
 *
 * **Dates are calendar dates and are not converted.** `release` and `release_en` are `Y-m-d` strings in
 * this document, not the epochs the scenario document publishes, so there is no zone to resolve and no
 * arithmetic to do; a `Y-m-d` that fails its pattern is stored as null rather than coerced, per
 * CLAUDE.md Planner rule 3 (date-only stays date-only).
 */
final class GametoraSupportCardParser implements SupportCardSourceParser
{
    /**
     * One effect row: the effect id plus one anchor at each of the eleven card levels the client
     * publishes (1, 5, 10 … 50). UMAMUSUME_REFERENCE.md §1.4.7.
     */
    private const ANCHOR_ROW_WIDTH = 12;

    /**
     * @return list<array{support_id: int, char_id: int, char_name: string|null, name_ja: string|null,
     *                   title_en: string|null, title_ja: string|null, rarity: int, type: string,
     *                   release_jp: string|null, release_global: string|null, effects: list<list<int>>}>
     */
    public function parse(string $body): array
    {
        try {
            $cards = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($cards)) {
            return [];
        }

        $records = [];

        foreach ($cards as $card) {
            $row = $this->row($card);

            if ($row !== null) {
                $records[] = $row;
            }
        }

        return $records;
    }

    /**
     * @return array{support_id: int, char_id: int, char_name: string|null, name_ja: string|null,
     *               title_en: string|null, title_ja: string|null, rarity: int, type: string,
     *               release_jp: string|null, release_global: string|null, effects: list<list<int>>}|null
     */
    private function row(mixed $card): ?array
    {
        if (! is_array($card)) {
            return null;
        }

        $supportId = $card['support_id'] ?? null;
        $charId = $card['char_id'] ?? null;
        $rarity = $card['rarity'] ?? null;

        // `support_id` is the row's identity and `char_id` the Scenario Link's only evidence, so a
        // record missing either is not a card this table can hold. Both are plain ints here: `char_id`
        // addresses GameTora's character space and carries no local meaning to resolve.
        if (! is_int($supportId) || ! is_int($charId)) {
            return null;
        }

        // Validated against the enum the model casts to, so a rarity the cast would refuse on a
        // `CardRarity`-typed write is refused here instead of mid-transaction.
        if (! is_int($rarity) || CardRarity::tryFrom($rarity) === null) {
            return null;
        }

        $type = $this->text($card['type'] ?? null);

        if ($type === null || ! in_array($type, SupportCard::TYPES, true)) {
            return null;
        }

        return [
            'support_id' => $supportId,
            'char_id' => $charId,
            'char_name' => $this->text($card['char_name'] ?? null),
            'name_ja' => $this->text($card['name_jp'] ?? null),
            'title_en' => $this->text($card['title_en'] ?? null),
            'title_ja' => $this->text($card['title_ja'] ?? null),
            'rarity' => $rarity,
            'type' => $type,
            'release_jp' => $this->date($card['release'] ?? null),
            // Absent on the 308 records Global has not received, and `?? null` is the whole of that
            // reading: a missing key is "not released there", not a value to look for elsewhere.
            'release_global' => $this->date($card['release_en'] ?? null),
            'effects' => $this->anchors($card['effects'] ?? null),
        ];
    }

    /**
     * The twelve-wide anchor rows, kept verbatim; anything else is dropped.
     *
     * @param  mixed  $effects  mixed because the caller is reading a document, not a schema
     * @return list<list<int>>
     */
    private function anchors(mixed $effects): array
    {
        if (! is_array($effects)) {
            return [];
        }

        $kept = [];

        foreach ($effects as $row) {
            if (! is_array($row) || count($row) !== self::ANCHOR_ROW_WIDTH) {
                continue;
            }

            foreach ($row as $value) {
                if (! is_int($value)) {
                    continue 2;
                }
            }

            /** @var list<int> $row */
            $kept[] = $row;
        }

        return $kept;
    }

    /**
     * A calendar date the source states, passed through untouched. No zone and no `Carbon` round
     * trip: this document publishes `Y-m-d`, and reformatting a date-only value is how a zone ends up
     * inside one.
     */
    private function date(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }

    private function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
