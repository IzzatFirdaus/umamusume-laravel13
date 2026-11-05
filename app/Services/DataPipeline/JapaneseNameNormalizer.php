<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use Normalizer;

/**
 * The normalized search key for a Japanese name (Phase 7, `match_key_ja`).
 *
 * Sibling of `NameNormalizer`, not a replacement, and the difference between them is a rule rather
 * than a preference. `NameNormalizer` folds with NFKD and then **strips every combining mark**
 * (`preg_replace('/\p{Mn}+/u', '', ...)`), which is right for Latin accents — `Café` and `Cafe` must
 * share a key — and wrong for Japanese. Dakuten and handakuten are combining marks, so NFKD turns
 * `が` into `か` + `U+3099` and the strip then leaves `か`. Measured on this tree:
 *
 *   NameNormalizer: 'ブレイズ' -> 'フレイス'   (b/d/z all lost; three different names collide)
 *   this class:     'ブレイズ' -> 'ブレイズ'   (correct)
 *   NameNormalizer: '掲げよ'   -> '掲けよ'     (a different word)
 *
 * So `NameNormalizer` must not be used for `name_ja`, and this class must not be used for the Latin
 * `match_key`. Two normalizers, each stating its own loss model.
 *
 * **NFKC, not NFKD.** Compatibility decomposition then composition does the whole documented fold in
 * one pass, and it is the standard's own table rather than a hand-copied one:
 *
 *   - full-width ASCII -> half-width (`ＢＬＡＺＩＮＧ` -> `BLAZING`)
 *   - full-width space `U+3000` -> `U+0020` (`Ｄｉｎｇ　Ｄｏｎｇ` -> `Ding Dong`)
 *   - half-width katakana -> full-width (`ﾌﾞﾚｲｽﾞ` -> `ブレイズ`), including the voiced sound marks
 *
 * **Out of scope, deliberately.** Katakana and hiragana stay distinct (`ブレイズ` and `ぶれいず` do
 * not share a key), and no romaji transliteration happens. Both are features, not gap-closes; adding
 * either changes which rows match, which is an amendment rather than a fix.
 *
 * Whitespace is stripped last and completely, matching `match_key`: the catalogue searches by
 * substring over a spaceless key, so a query with different spacing is the same query.
 */
final class JapaneseNameNormalizer
{
    /**
     * Null in, null out.
     *
     * The column is nullable and the distinction is load-bearing: null means "the source records no
     * Japanese name", while an empty string means "recorded, and it normalizes to nothing". Collapsing
     * the two would make an unrecorded name indistinguishable from a blank one in the catalogue.
     */
    public static function normalize(?string $nameJa): ?string
    {
        if ($nameJa === null) {
            return null;
        }

        $folded = Normalizer::normalize($nameJa, Normalizer::FORM_KC);

        // A string the intl extension cannot normalize is folded with the generic whitespace rule
        // rather than dropped: the key then still matches on its literal characters.
        if ($folded === false) {
            $folded = $nameJa;
        }

        $lower = mb_strtolower(trim($folded));

        // ASCII space (also catches the folded U+3000), tab, newline and carriage return. `\s` alone
        // would not cover `U+3000` under every PCRE build, so the character class names it too.
        return preg_replace('/[\s\x{3000}]+/u', '', $lower) ?? $lower;
    }
}
