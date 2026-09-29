<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Services\DataPipeline\Contracts\ProfileSourceParser;
use JsonException;

/**
 * Reads the GameTora `characters` document into trainee profile rows.
 *
 * This is the source behind the "basic information" block on the character page: Japanese name,
 * voice actor, birthday, height and three sizes. The owner confirmed on 2026-09-30 that this block
 * is a *different* dataset from the card document's stat arrays, and `ADR-0012` names the source
 * as unaddressed by any of its three decisions.
 *
 * **Every number here was re-measured against the body on 2026-09-30 rather than taken from the
 * earlier probe, and three of the probe's statements did not survive:**
 *
 * 1. **There is no `birth` field.** The document carries `birth_year`, `birth_month` and
 *    `birth_day` separately. Only `birth_year` is ever null (17 of 163 rows); month and day are
 *    present on all 163. An earlier reading of a single `birth` would have had to invent a year
 *    for those 17 rows.
 * 2. **The voice actor is one person written in two scripts, not a dub cast.** `va_ja` carries the
 *    Japanese credit (`和氣あず未`) and `va_en` its romanisation (`Azumi Waki`); they name the same
 *    performer, and `va_ko` on the same rows is a third script of that one name. Where a stage name
 *    is already romanised (`Machico`, `Lynn`) all three fields hold the identical string, which no
 *    separate English cast could produce. So the 26 `va_en` nulls across the 163-row document (3 of
 *    the 105 `race === 'uma'` rows) are missing romanisations, not missing dub credits, and a
 *    profile block that showed only `va_ja` would render Japanese script to a Global reader when a
 *    romanisation already sits in the same row. `va_ko` and `va_zh_tw` are refused: two casts are
 *    what `ADR-0012` Decision 4 stores, not four.
 *
 *    *[Dated erratum, 2026-09-30]* An earlier revision of this docblock recorded this backwards:
 *    "the one with 26 nulls is the English one ... `va_en` is the English dub cast and `va_ja` is
 *    the romanised Japanese cast." The body refutes that reading, so it is quoted here and
 *    superseded rather than silently rewritten.
 * 3. **`three_sizes` is an object, not a string.** It arrives as `{"b":81,"h":81,"w":56}`. A
 *    space-separated rendering is what a spreadsheet-style viewer shows, and parsing that instead
 *    of the body would have been reading a presentation.
 *
 * **`three_sizes` is null on 36 of 163**, and that is the only field besides `va_en` and
 * `birth_year` with real gaps. There is no release date in this document: the release dates the
 * page shows come from `umamusume.global_debut_date` and `character_cards.global_release_date`,
 * which are already stored (ADR-0008), so nothing here duplicates them.
 *
 * **`sex` and `race` are parsed by nobody.** The document carries both. Neither is one of the six
 * fields the block shows, and CONSTRAINTS.md C-4 governs how this tool names these characters, so
 * neither becomes a column (see the migration for the same reasoning).
 *
 * **A row is kept even when it describes almost nothing**, because dropping it would be a second
 * way of inventing absence: the trainee is known, the block says the document says little about
 * her, and the view renders that in words. What a row must have to be stored at all is a
 * `char_id`, because that is the ref the store action resolves through.
 */
final class GametoraCharacterProfileParser implements ProfileSourceParser
{
    /**
     * The ref prefix `GametoraCharacterParser` writes into `umamusume.external_ref`, reused here
     * so the join is one format rather than two formats that happen to agree today.
     */
    private const REF_PREFIX = 'gametora:char:';

    /**
     * @return list<array{char_external_ref: string, name_ja: string|null, va_ja: string|null,
     *                    va_en: string|null, birth_year: int|null, birth_month: int|null,
     *                    birth_day: int|null, height: int|null, three_sizes_b: int|null,
     *                    three_sizes_h: int|null, three_sizes_w: int|null}>
     */
    public function parse(string $body): array
    {
        try {
            $characters = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($characters)) {
            return [];
        }

        $rows = [];

        foreach ($characters as $character) {
            $row = $this->row($character);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  mixed  $character  mixed, not array: the caller is reading a document, not a schema
     * @return array{char_external_ref: string, name_ja: string|null, va_ja: string|null,
     *               va_en: string|null, birth_year: int|null, birth_month: int|null,
     *               birth_day: int|null, height: int|null, three_sizes_b: int|null,
     *               three_sizes_h: int|null, three_sizes_w: int|null}|null
     */
    private function row(mixed $character): ?array
    {
        if (! is_array($character) || ! isset($character['char_id']) || ! is_numeric($character['char_id'])) {
            return null;
        }

        $charId = (int) $character['char_id'];

        if ($charId <= 0) {
            return null;
        }

        // The document is Japanese-first and every name here is non-ASCII, so this is a trim and
        // an emptiness check, never a transliteration. Nothing in this class rewrites a string.
        $name = $this->textOrNull($character['jp_name'] ?? null);
        $vaJa = $this->textOrNull($character['va_ja'] ?? null);
        $vaEn = $this->textOrNull($character['va_en'] ?? null);

        $sizes = $this->threeSizes($character['three_sizes'] ?? null);

        return [
            'char_external_ref' => self::REF_PREFIX.$charId,
            'name_ja' => $name,
            'va_ja' => $vaJa,
            'va_en' => $vaEn,
            'birth_year' => $this->intOrNull($character['birth_year'] ?? null),
            'birth_month' => $this->intOrNull($character['birth_month'] ?? null),
            'birth_day' => $this->intOrNull($character['birth_day'] ?? null),
            'height' => $this->intOrNull($character['height'] ?? null),
            'three_sizes_b' => $sizes['b'],
            'three_sizes_h' => $sizes['h'],
            'three_sizes_w' => $sizes['w'],
        ];
    }

    /**
     * `{"b":81,"h":81,"w":56}` into three integers, or three nulls.
     *
     * All-or-nothing on purpose. The three parts are one measurement, and a row carrying two of
     * them would render as a two-thirds measurement the source never stated. A non-array here is
     * the null case rather than an error: the field is absent on 36 of 163 rows, and a document
     * that omits a field is stating something, not failing.
     *
     * @param  mixed  $sizes  mixed: absent, null, or the object's own shape
     * @return array{b: int|null, h: int|null, w: int|null}
     */
    private function threeSizes(mixed $sizes): array
    {
        if (! is_array($sizes)) {
            return ['b' => null, 'h' => null, 'w' => null];
        }

        $bust = $this->intOrNull($sizes['b'] ?? null);
        $height = $this->intOrNull($sizes['h'] ?? null);
        $waist = $this->intOrNull($sizes['w'] ?? null);

        if ($bust === null || $height === null || $waist === null) {
            return ['b' => null, 'h' => null, 'w' => null];
        }

        return ['b' => $bust, 'h' => $height, 'w' => $waist];
    }

    private function intOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        // The document writes these as JSON numbers, so a string here means the publisher changed
        // shape rather than that the value is a measurement. Accepting a clean numeric string is
        // a tolerance for that; anything else is a null, because a cast would turn a word into 0
        // and 0 is a number this page would then print.
        if (is_string($value) && preg_match('/^\d{1,4}$/', trim($value)) === 1) {
            return (int) trim($value);
        }

        return null;
    }

    private function textOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
