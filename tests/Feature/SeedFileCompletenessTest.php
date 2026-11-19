<?php

declare(strict_types=1);

/*
 * S1.4: three sources declared a `seed_file` whose committed body was absent, so a fresh
 * `migrate --seed` skipped them, left their tables empty, and still exited 0. `ReadsCommittedSource`
 * warns and returns null on a missing body, which is the right runtime behaviour and the wrong
 * thing to discover after the fact.
 *
 * This test is the boundary: every source that declares a body must ship one, and the shipped body
 * must be real data rather than a zero-byte placeholder that satisfies an existence check.
 */

it('ships a committed body for every source that declares one', function (): void {
    foreach (config('uma.sources') as $key => $source) {
        if (! isset($source['seed_file'])) {
            continue;
        }

        expect(is_file(database_path('seeders/data/'.$source['seed_file'])))
            ->toBeTrue("Source {$key} declares seed_file {$source['seed_file']}, which is not committed.");
    }
});

it('ships a non-empty, decodable JSON body for every source that declares one', function (): void {
    foreach (config('uma.sources') as $key => $source) {
        if (! isset($source['seed_file'])) {
            continue;
        }

        $path = database_path('seeders/data/'.$source['seed_file']);

        if (! is_file($path)) {
            // The first case owns the missing-file message; this one must not double-report it as
            // "empty".
            continue;
        }

        $body = (string) file_get_contents($path);

        expect($body)->not->toBe('', "Source {$key} seed_file {$source['seed_file']} is empty.")
            ->and(json_decode($body, true))
            ->not->toBeNull("Source {$key} seed_file {$source['seed_file']} does not decode as JSON.");
    }
});
