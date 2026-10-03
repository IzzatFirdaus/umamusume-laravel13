<?php

declare(strict_types=1);

use App\Services\DataPipeline\NameNormalizer;

beforeEach(function (): void {
    $this->normalizer = new NameNormalizer;
});

it('folds full-width and half-width katakana to the same key', function (): void {
    expect($this->normalizer->normalize('フ'))
        ->toBe($this->normalizer->normalize('ﾌ'));
});

it('lowercases and strips spaces hyphens and middle dots', function (): void {
    expect($this->normalizer->normalize('Special Week'))
        ->toBe('specialweek')
        ->and($this->normalizer->normalize('Special-Week'))
        ->toBe('specialweek')
        ->and($this->normalizer->normalize('ウオカ・インター'))
        ->toBe('ウオカインター');
});

it('folds accented latin to its base letters', function (): void {
    expect($this->normalizer->normalize('Café'))
        ->toBe($this->normalizer->normalize('cafe'));
});

it('keeps the prolonged sound mark as part of the name', function (): void {
    expect($this->normalizer->normalize('タイクロー'))
        ->toBe('タイクロー');
});

/**
 * KI-40, pinned rather than fixed. NFKD leaves the Latin ligatures and stroked letters alone and none of
 * them is in `FOLDED_CHARACTERS`, so they survive into the key: a source that publishes `Stræight` will
 * not match a Trainer typing `Straights`, and the failure reads as "no such trainee". Measured severity
 * on today's data: the ae ligature appears only in columns this tool does not store, and O-stroke,
 * D-stroke, thorn and the oe ligature appear zero times, so no Global name folds wrong yet.
 *
 * This test is the loud half of the ceiling. It asserts the pairs do NOT match, so the day a transliteration
 * step is added (KI-40 option a, the real fix) or a source starts publishing one of these letters, this
 * fails and points at the decision instead of at a support question.
 */
it('leaves the letters NFKD does not decompose inside the key', function (): void {
    expect($this->normalizer->normalize('Stræight'))
        ->not->toBe($this->normalizer->normalize('Straights'))
        ->and($this->normalizer->normalize('Ødawara'))
        ->not->toBe($this->normalizer->normalize('Odawara'))
        ->and($this->normalizer->normalize('Þor'))
        ->not->toBe($this->normalizer->normalize('Thor'));
});
