<?php

declare(strict_types=1);

use App\Services\DataPipeline\JapaneseNameNormalizer;
use App\Services\DataPipeline\NameNormalizer;

/*
 * `match_key_ja`'s fold (Phase 7). Six cases, one per rule the class states.
 *
 * The pair that matters most is not the full-width or half-width fold but the dakuten case at the
 * bottom: `JapaneseNameNormalizer` exists because `NameNormalizer`'s NFKD-then-strip-marks pass
 * silently destroys voiced kana. That test pins the contrast against the other normalizer rather than
 * only asserting this one's output, so a future "one normalizer to rule them all" simplification
 * fails here instead of in the catalogue.
 */

it('normalizes lowercase and strips whitespace', function (): void {
    expect(JapaneseNameNormalizer::normalize('Blazing Wolf'))->toBe('blazingwolf');
    expect(JapaneseNameNormalizer::normalize('  You copy?  '))->toBe('youcopy?');
});

it('folds full-width ascii to half-width', function (): void {
    expect(JapaneseNameNormalizer::normalize('ＢＬＡＺＩＮＧ'))->toBe('blazing');

    // The ideographic space is part of the same rule, and NFKC folds it to U+0020 before the strip.
    expect(JapaneseNameNormalizer::normalize('Ｄｉｎｇ　Ｄｏｎｇ'))->toBe('dingdong');
});

it('folds half-width katakana to full-width, one fold only', function (): void {
    expect(JapaneseNameNormalizer::normalize('ﾌﾞﾚｲｽﾞ'))->toBe('ブレイズ');

    // The voiced sound marks are half-width too; NFKC composes them rather than leaving them loose.
    expect(JapaneseNameNormalizer::normalize('ｶﾞ'))->toBe('ガ');
});

it('does not fold katakana to hiragana, which is out of scope', function (): void {
    expect(JapaneseNameNormalizer::normalize('ぶれいず'))->toBe('ぶれいず');
    expect(JapaneseNameNormalizer::normalize('ブレイズ'))->toBe('ブレイズ');
    expect(JapaneseNameNormalizer::normalize('ぶれいず'))
        ->not->toBe(JapaneseNameNormalizer::normalize('ブレイズ'));
});

it('keeps null and the empty string distinct', function (): void {
    // Null is "the source records no Japanese name"; '' is "recorded, and it folds to nothing".
    // The catalogue renders those differently, so the key must not collapse them.
    expect(JapaneseNameNormalizer::normalize(null))->toBeNull();
    expect(JapaneseNameNormalizer::normalize(''))->toBe('');
    expect(JapaneseNameNormalizer::normalize('   '))->toBe('');
});

/*
 * The reason this class exists, pinned against the sibling it must not be replaced by.
 *
 * `NameNormalizer` strips combining marks (`\p{Mn}`), and dakuten is a combining mark: NFKD splits
 * `ブ` into `フ` + `U+3099` and the strip leaves `フ`. So the Latin normalizer turns three distinct
 * katakana names into one key. Measured, not reasoned:
 *
 *   NameNormalizer('ブレイズ')       -> 'フレイス'
 *   JapaneseNameNormalizer('ブレイズ') -> 'ブレイズ'
 */
it('preserves voiced kana where the latin normalizer does not, which is why it exists', function (): void {
    $latin = app(NameNormalizer::class);

    expect($latin->normalize('ブレイズ'))->toBe('フレイス');
    expect(JapaneseNameNormalizer::normalize('ブレイズ'))->toBe('ブレイズ');

    // Voicing is semantic in Japanese: が and か are different characters. A fold that merges them
    // makes two different skill names the same search hit.
    expect(JapaneseNameNormalizer::normalize('が'))->not->toBe(JapaneseNameNormalizer::normalize('か'));
    expect($latin->normalize('が'))->toBe($latin->normalize('か'));
});
