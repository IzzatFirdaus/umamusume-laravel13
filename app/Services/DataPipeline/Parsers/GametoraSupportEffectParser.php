<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\SupportEffectSourceParser;
use JsonException;

/**
 * Reads the GameTora support-effect dictionary into `support_effects` rows (ADR-0014; PRD FR-B).
 *
 * Measured on the document as published at manifest hash `ca447e53` (35 records, ids 1-33 plus 41 and
 * 9991, committed as `database/seeders/data/support_effects.ca447e53.json`).
 *
 * **A missing `calc` is the finding, so it is left missing.** Only four of the 35 records declare it:
 * `mult` on ids 1, 27 and 28, `add` on id 19. UMAMUSUME_REFERENCE.md §1.4.8 turns on exactly that
 * split, that the effects declaring `calc` are the ones which combine multiplicatively, and this
 * repository's own word for the rest is prose, not a mode the client has. Writing `flat` into the 31
 * would state a fourth combining rule that no source supports, and the column's CHECK now refuses it
 * (migration `2026_09_30_151945` point 4). The value is therefore passed through verbatim when the key
 * is there and left null when it is not, with no fallback between the two.
 *
 * **`name_en` falls back to `name_en_eon`, and only those two.** Two records (id 32, Initial Skill
 * Points Up, and id 41, All Stats Bonus) carry no `name_en` at all, and both carry the publisher's
 * longer `name_en_eon` spelling; `name_en` sits on 33 records and `name_en_eon` on the other 33, so the
 * pair covers all 35 and `support_effects.name_en` is NOT NULL. The same fallback shape is what
 * `GametoraScenarioParser` does with `name_en` / `name_en_old`. Not used as a fallback: `name_ja`,
 * which every record has and which is not an English label, and the `_ko` / `_tw` localisations.
 *
 * **The other published fields are read but not stored.** `desc_en` is the source of
 * `description_en` and is kept verbatim as reference text with one fallback to `desc_en_eon` for the
 * records that carry only that (id 32 and 41 again, plus id 33 which has neither and stores null).
 * `symbol` is the export's unit word (`percent` 11, `none` 21, `level` 1, absent 2) and is stored
 * verbatim, including the two absences. `inactive`, `no_value`, `_note`, `name_ko`, `name_tw` and
 * `desc_ko` / `desc_tw` are published and not persisted: no column in `support_effects` holds them, so
 * they are dropped rather than guessed into a field that means something else.
 *
 * **The keys are the document's.** The effect's own id is `id` here, not `effect_id` as on the card
 * anchors, and the English name is `name_en`, not `name` (KI-23 is a parser reading a key the document
 * does not publish, and a fixture written to match the parser would agree with the mistake).
 */
final class GametoraSupportEffectParser implements SupportEffectSourceParser
{
    /**
     * @return list<array{effect_id: int, name_en: string, name_ja: string|null, calc: string|null,
     *                   symbol: string|null, description_en: string|null}>
     */
    public function parse(string $body): array
    {
        try {
            $effects = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($effects)) {
            return [];
        }

        $records = [];

        foreach ($effects as $effect) {
            $row = $this->row($effect);

            if ($row !== null) {
                $records[] = $row;
            }
        }

        return $records;
    }

    /**
     * @return array{effect_id: int, name_en: string, name_ja: string|null, calc: string|null,
     *               symbol: string|null, description_en: string|null}|null
     */
    private function row(mixed $effect): ?array
    {
        if (! is_array($effect)) {
            return null;
        }

        $effectId = $effect['id'] ?? null;
        $name = $this->text($effect['name_en'] ?? null) ?? $this->text($effect['name_en_eon'] ?? null);

        if (! is_int($effectId) || $name === null) {
            return null;
        }

        return [
            'effect_id' => $effectId,
            'name_en' => $name,
            'name_ja' => $this->text($effect['name_ja'] ?? null),
            // Verbatim or null. `SupportEffect::CALC_MODES` is not consulted as a filter here: an
            // unknown mode is a new claim about how an effect combines, and the CHECK on the column is
            // the layer that stops it, loudly, rather than a parser quietly emptying it.
            'calc' => $this->text($effect['calc'] ?? null),
            'symbol' => $this->text($effect['symbol'] ?? null),
            'description_en' => $this->text($effect['desc_en'] ?? null)
                ?? $this->text($effect['desc_en_eon'] ?? null),
        ];
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
