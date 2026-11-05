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
 * Every `view: source` pair printed by a Blade template or a Vue page, for the sweep to check.
 *
 * Both roots are walked: a screen that has ported to Vue (ADR-0020 §1) prints its empty-state
 * command from `resources/js`, and a Blade-only sweep would silently stop counting it the day
 * the view was deleted.
 *
 * @return list<string>
 */
function commandStringsInViews(): array
{
    $found = [];

    foreach ([base_path('resources/views'), base_path('resources/js')] as $root) {
        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            $filename = $file->getFilename();

            if (! str_ends_with($filename, '.blade.php') && ! str_ends_with($filename, '.vue')) {
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
    $readme = str_replace("\r\n", "\n", (string) file_get_contents(base_path('README.md')));

    expect(preg_match_all('/\b(gametora-[a-z0-9-]+)\b/', $readme, $hits))
        ->toBeGreaterThan(0, 'the README names at least one source, so this guard has something to read');

    $offenders = array_values(array_unique(array_filter(
        $hits[1],
        fn (string $named): bool => ! in_array($named, declaredSources(), true),
    )));

    expect($offenders)->toBe([], 'the README names a source config/uma.php does not declare');
});

it('backs any source count the README prints with the config that declares them', function (): void {
    $readme = str_replace("\r\n", "\n", (string) file_get_contents(base_path('README.md')));

    // §7-11's defect was a count in prose that config had outgrown. The fix that lasts is not
    // deleting the number but refusing a wrong one: a file that only points at `config/uma.php`
    // passes here, and a file that states a figure has to mean it.
    //
    // Words as well as digits, because the sentence in the README is "Seven sources are declared".
    // A digit-only pattern would pass this guard without ever reading the number it claims to check.
    $matched = preg_match(
        '/\b(\d{1,3}|one|two|three|four|five|six|seven|eight|nine|ten|eleven|twelve) (?:[\w-]+ )*?sources? (?:are|is) declared/i',
        $readme,
        $stated,
    );

    if ($matched !== 1) {
        expect(true)->toBeTrue();

        return;
    }

    $words = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6,
        'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10, 'eleven' => 11, 'twelve' => 12];
    $statedCount = is_numeric($stated[1])
        ? (int) $stated[1]
        : $words[strtolower($stated[1])];
    $declaredCount = count(declaredSources());

    expect($statedCount)
        ->toBe($declaredCount, "the README states {$stated[1]} declared sources; config/uma.php declares {$declaredCount}");
});

it('lists no route in the README Web surface section that the app does not have', function (): void {
    // Normalised because this file is checked out with CRLF on a Windows host and the
    // heading's casing is the prose's choice, not a contract.
    $readme = str_replace("\r\n", "\n", (string) file_get_contents(base_path('README.md')));

    expect(preg_match("/\n## web surface\b(.*?)\n## /is", $readme, $section))
        ->toBe(1, 'the Web surface section must exist for this guard to read');

    /*
     * The section is prose with grouped bullet lists, not a one-route-per-row table. `fb48c7ac`
     * consolidated the overlapping command tables and replaced the route table with "Main areas,
     * grouped by feature" — an editorial decision about the README, not a route change — and this
     * guard was not updated with it, so it read zero paths and asserted a table had gone missing.
     *
     * What it checks is unchanged: every `/path` the section names must correspond to a real route.
     * The reader now takes each backticked path inside the section, strips the `{param}` and `*`
     * placeholders the prose uses, and compares against `route:list`. Bullets and tables are both
     * just text naming paths; the check is about the paths.
     */
    preg_match_all('/`(\/[^`]*)`/', $section[1], $cells);

    $listed = [];

    foreach ($cells[1] as $cell) {
        // A cell like `/training-runs/{run}/export/{format}` names a real route pattern; the
        // comparison below matches on the literal segments before the first placeholder, which is
        // what a stale entry (like the retired `/design-preview`) would fail.
        $path = '/'.ltrim(preg_split('/\{/', $cell)[0], '/');

        if ($path !== '/' && $path !== '') {
            $listed[] = rtrim($path, '/');
        }
    }

    $listed = array_values(array_unique($listed));

    expect($listed)->not->toBe([], 'the Web surface section must name at least one path')
        ->and(count($listed))->toBeGreaterThanOrEqual(6, 'the paths this guard reads have gone missing');

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
