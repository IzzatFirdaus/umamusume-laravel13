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
