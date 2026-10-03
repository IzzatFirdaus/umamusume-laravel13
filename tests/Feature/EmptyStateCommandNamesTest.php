<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
 * SCREEN_SPEC.md §7-11: four screens print an artisan command in their empty state, and a
 * Trainer who types one that does not exist learns nothing about their catalog. The copy
 * matched config when this file was written; nothing in the suite would have noticed the
 * day it stopped matching, which is the whole defect. The same class produced §7-1: the
 * README's route table still listed `/design-preview` after the route was deleted.
 *
 * So the guard is a sweep, not a pin on four strings: a fifth empty state naming a source
 * nobody declared fails here, and a source renamed in `config/uma.php` fails in every view
 * and in the README at once.
 */

/**
 * The source keys the fetch engine declares.
 *
 * @return list<string>
 */
function declaredSources(): array
{
    $sources = config('uma.sources');

    return is_array($sources) ? array_keys($sources) : [];
}

/**
 * Every `view: source` pair printed by a Blade template, for the sweep to check.
 *
 * @return list<string>
 */
function commandStringsInViews(): array
{
    $found = [];

    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views'), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $name = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());

        if (preg_match_all('/uma:fetch\s+([a-z0-9]+(?:-[a-z0-9]+)*)/', $source, $hits) > 0) {
            foreach ($hits[1] as $token) {
                $found[] = $name.': '.$token;
            }
        }
    }

    return $found;
}

it('registers the command its empty states tell the Trainer to run', function (): void {
    expect(array_key_exists('uma:fetch', Artisan::all()))->toBeTrue();
});

it('names only a declared source in every command string the views print', function (): void {
    $printed = commandStringsInViews();
    $declared = declaredSources();

    // The sweep is worth nothing if it walked an empty set, so it has a floor: the four
    // empty states §7-11 counts.
    expect($printed)->not->toBe([])
        ->and(count($printed))->toBeGreaterThanOrEqual(4);

    $offenders = array_values(array_filter(
        $printed,
        fn (string $entry): bool => ! in_array(explode(': ', $entry)[1], $declared, true),
    ));

    expect($offenders)->toBe([], 'views print uma:fetch sources config/uma.php does not declare');
});

it('names only a declared source in the README', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));

    expect(preg_match_all('/\b(gametora-[a-z0-9-]+)\b/', $readme, $hits))
        ->toBeGreaterThan(0, 'the README names at least one source, so this guard has something to read');

    $offenders = array_values(array_unique(array_filter(
        $hits[1],
        fn (string $named): bool => ! in_array($named, declaredSources(), true),
    )));

    expect($offenders)->toBe([], 'the README names a source config/uma.php does not declare');
});

it('lists no route in the README Web surface table that the app does not have', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));

    expect(preg_match("/\n## Web surface\n(.*?)\n## /s", $readme, $section))
        ->toBe(1, 'the Web surface section must exist for this guard to read');

    preg_match_all('/^\|\s*`?(\/[^|\s`]*)`?\s*\|/m', $section[1], $rows);
    $listed = array_values(array_unique($rows[1]));

    expect($listed)->not->toBe([], 'the Web surface table must list at least one path');

    $routes = array_map(
        fn ($route): string => '/'.ltrim($route->uri(), '/'),
        Route::getRoutes()->getRoutes(),
    );

    $stale = array_values(array_filter(
        $listed,
        // `{run}` placeholders and the `csv|.json` alternation are the README's shorthand for one
        // route, so a listed path is live when some registered route begins with its literal head.
        fn (string $path): bool => ! array_any(
            $routes,
            fn (string $route): bool => str_starts_with($route, rtrim(explode('{', explode('|', $path)[0])[0], '/')),
        ),
    ));

    expect($stale)->toBe([], 'the README lists a route the app does not have');
});
