<?php

declare(strict_types=1);

/*
 * A control treatment retired in `resources/css/app.css` leaves behind every spec that asserted it.
 * That is how `enamel` went: the class was purged from the Vue app on 2026-10-15, three specs kept
 * asserting `locator('.enamel')`, and every browser run since reported a product regression on a
 * screen that was fine. The red landed on whichever slice happened to run the suite, not on the
 * class that had actually gone away, and for two days it was filed in three separate hand-offs as
 * "pre-existing" without anyone owning it.
 *
 * This guard makes that a one-line failure naming the spec, the line and the class, at the moment
 * the class is retired rather than at the next unrelated browser run.
 */

/**
 * Control classes that no longer exist in the shipped surface.
 *
 * Add to this list the day a treatment is retired. It is a list rather than a derivation because
 * "retired" is a decision and not a fact about the file system: a class with zero consumers is dead,
 * and a class that was deliberately replaced is retired, and only the second one means a spec is
 * wrong.
 */
const RETIRED_BROWSER_SPEC_CLASSES = ['enamel', 'lattice', 'torn-chip'];

it('asserts no retired control class in any browser spec', function (): void {
    $specs = [];

    // RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`, so a glob-based
    // walk would scan one directory and pass without having looked.
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('tests/browser'), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! str_ends_with($file->getFilename(), '.spec.ts')) {
            continue;
        }

        $specs[str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname())] =
            (string) file_get_contents($file->getPathname());
    }

    // The floor is what separates "no spec asserts a retired class" from "the walk found nothing".
    // Measured 2026-10-18: 56 spec files in `tests/browser`.
    expect(count($specs))
        ->toBeGreaterThanOrEqual(40)
        ->and($specs)->not->toBeEmpty();

    $offenders = [];

    foreach ($specs as $path => $source) {
        foreach (RETIRED_BROWSER_SPEC_CLASSES as $class) {
            $quoted = preg_quote($class, '/');

            foreach (preg_split('/\R/', $source) ?: [] as $index => $line) {
                // Class-shaped use only: `.name` in a selector, `/name/` in `toHaveClass`, or
                // `'name'`/`"name"` as an argument. The bare word in a comment is this repository
                // describing the retirement, and a guard that fails on prose gets loosened rather
                // than obeyed. The trailing lookahead keeps `.lattice` from matching `.lattice-bleed`.
                if (preg_match('/(?:\.|\/|["\'])'.$quoted.'(?![\w-])/', $line) === 1) {
                    $offenders[] = $path.':'.($index + 1).': '.trim($line);
                }
            }
        }
    }

    expect($offenders)->toBe([]);
});
