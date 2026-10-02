<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Enums\CardRarity;
use App\Services\DataPipeline\Contracts\CharacterCardSourceParser;
use JsonException;

/**
 * Reads the GameTora character-card dataset and emits one record per card that
 * exists on [Global] (PRD FR-A-6, ADR-0008).
 *
 * The same document GametoraCharacterParser reads, at card grain instead of
 * character grain: that parser keeps one card per trainee and drops the rest,
 * which is the behaviour ADR-0008 exists to stop being the only one. A card with
 * no release_en is [JP-Only] and is not app data at either grain.
 */
final class GametoraCharacterCardParser implements CharacterCardSourceParser
{
    /**
     * @return list<array{card_id: int, char_external_ref: string, title: string,
     *                          rarity: int, global_release_date: string, is_debut_form: bool,
     *                          skills_innate: list<int>, skills_unique: list<int>,
     *                          skills_awakening: list<int>, skills_event: list<int>,
     *                          skills_evo: list<array{new: int, old: int}>}>
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

        // One definition of "debut", shared with the character catalog.
        $debutCardIds = array_map(
            'intval',
            array_column(GametoraCharacterParser::debutForms($cards), 'card_id'),
        );

        $records = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $globalRelease = GametoraCharacterParser::dateOrNull($card['release_en'] ?? null);
            $cardId = $card['card_id'] ?? null;
            $charId = $card['char_id'] ?? null;
            // The Global client string is `title_en_gl`; `title` is the Japanese-side
            // string and they genuinely differ ("Run! Fun! Watergun!" ships as
            // "[RUN! RUIN! LAUNCHER!]"). No fallback chain between them: reading the
            // wrong one is a silent data error, and a chain hides that it happened.
            $title = $card['title_en_gl'] ?? null;

            if ($globalRelease === null || $globalRelease === GametoraCharacterParser::UNKNOWN_DATE) {
                continue;
            }

            if (! is_numeric($cardId) || ! is_numeric($charId)) {
                continue;
            }

            if (! is_string($title) || trim($title) === '') {
                continue;
            }

            $rarity = CardRarity::tryFrom((int) ($card['rarity'] ?? 0));

            if ($rarity === null) {
                continue;
            }

            $records[] = [
                'card_id' => (int) $cardId,
                'char_external_ref' => 'gametora:char:'.(int) $charId,
                'title' => trim($title),
                'rarity' => $rarity->value,
                'global_release_date' => $globalRelease,
                // Strict on purpose: the mapped ids above went through intval and this
                // cast is an int, so the flag never depends on whether the source wrote
                // 100101 or '100101'. A loose compare would accept either, silently.
                'is_debut_form' => in_array((int) $cardId, $debutCardIds, true),
                // KI-33. Both are lists, both are `[]` when the document gives nothing, and
                // `skills_unique` really does hold two ids on 22 of the 268 records — which is
                // the only reason the column is json and not a nullable foreign id.
                'skills_innate' => $this->intList($card['skills_innate'] ?? null),
                'skills_unique' => $this->intList($card['skills_unique'] ?? null),
                // The three keys this class dropped until 2026-10-02. `skills_evo` is a list of
                // `{new, old}` pairs, not ids, so it takes its own reader rather than `intList`.
                'skills_awakening' => $this->intList($card['skills_awakening'] ?? null),
                'skills_event' => $this->intList($card['skills_event'] ?? null),
                'skills_evo' => $this->evoPairs($card['skills_evo'] ?? null),
            ];
        }

        return $records;
    }

    /**
     * The `{new, old}` id pairs the source publishes, or `[]`.
     *
     * Both halves are required and each must be an export id: a pair with one side missing names no
     * evolution, and storing half of it would render a direction the source never stated. Measured on
     * the committed body, every id in either half resolves against `skills.export_id`.
     *
     * @return list<array{new: int, old: int}>
     */
    private function evoPairs(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $pairs = [];

        foreach ($value as $pair) {
            if (! is_array($pair)) {
                continue;
            }

            $new = $this->positiveIntOrNull($pair['new'] ?? null);
            $old = $this->positiveIntOrNull($pair['old'] ?? null);

            if ($new !== null && $old !== null) {
                $pairs[] = ['new' => $new, 'old' => $old];
            }
        }

        return $pairs;
    }

    /**
     * One export id, or null when the value is not one.
     *
     * The single reader both list shapes go through, so a numeric string is accepted here exactly as it
     * is in `intList`: the source has written ids that way in this same document, and two readers that
     * disagreed about it would be a difference nobody could see until a fetch stopped filling a column.
     */
    private function positiveIntOrNull(mixed $value): ?int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        return null;
    }

    /**
     * A clean list of positive integer export ids, or `[]`.
     *
     * The contract is that a malformed value cannot crash the parse and cannot land a partial row, so
     * the filter is on the element's type rather than on a coercion of it: `intval([[10071], [100071]])`
     * returns `1` for each nested array, and a helper that mapped `intval` over the list would store the
     * skill id `1` twice and look like it worked. A numeric string is accepted because the source has
     * written ids that way elsewhere in this same document (the card id case above), while `0`, a
     * negative and a non-numeric string are not ids at all.
     *
     * @return list<int>
     */
    private function intList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $ids = [];

        foreach ($value as $item) {
            $id = $this->positiveIntOrNull($item);

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
