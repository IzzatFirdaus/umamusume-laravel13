<?php

declare(strict_types=1);

use App\Models\TurnEntry;

/*
 * `TurnEntry::statValue()` is the single owner of the matrix-name → stat-column lookup the four career
 * controllers each kept a private copy of (F2). These pin its two contract points: a matrix name reads
 * its own column, and a name the matrix does not carry is a null, never a default (AGENTS.md §5).
 */

it('reads each matrix name back to its own stat column', function (string $key, int $value): void {
    $entry = new TurnEntry([
        'speed' => 101,
        'stamina' => 202,
        'power' => 303,
        'guts' => 404,
        'wit' => 505,
    ]);

    expect($entry->statValue($key))->toBe($value);
})->with([
    ['Speed', 101],
    ['Stamina', 202],
    ['Power', 303],
    ['Guts', 404],
    ['Wit', 505],
]);

it('returns null for a matrix name it does not carry, never a default', function (string $key): void {
    $entry = new TurnEntry(['speed' => 101]);

    expect($entry->statValue($key))->toBeNull();
})->with([
    ['Intelligence'],
    ['speed'],
    [''],
    ['Friendship'],
]);
