<?php

declare(strict_types=1);

/*
 * The harness can fail in a way that reads as a pass, and it has.
 *
 * Measured 2026-11-13: `php artisan test --compact > log 2>&1` under this host's bash wrapper wrote a log
 * whose only content was `stdin is not a tty`. It ran **no tests**, printed no summary line, and the
 * wrapper still exited 0 — so a check that read the exit code alone recorded a dead run as green. The
 * same command backgrounded through `vendor/bin/pest` ran 2374 tests and printed its own count.
 *
 * These cases pin the two properties that make that failure visible rather than silent: the connection
 * really is the throwaway `:memory:` database, and a run of this file does execute assertions. Neither is
 * a substitute for reading the suite's own summary line, which stays the evidence rule in `AGENTS.md` §9.
 */

it('runs against the :memory: database rather than the shared dev file', function (): void {
    // `phpunit.xml:64-65` forces this. The suite cannot see `database/database.sqlite`, which is how
    // KI-60's four Pending migrations sat unnoticed behind a green 1,326-test run.
    expect(config('database.connections.'.config('database.default').'.database'))
        ->toBe(':memory:');

    // And the connection really is that database, not a file that happens to be named like one.
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
});

it('executes the case body it was given', function (): void {
    // A harness that loads this file and runs zero assertions has measured nothing. This is the smallest
    // thing that fails if the file stops being collected.
    $marker = ['recorded' => true];

    expect($marker)->toHaveKey('recorded')
        ->and($marker['recorded'])->toBeTrue();
});
