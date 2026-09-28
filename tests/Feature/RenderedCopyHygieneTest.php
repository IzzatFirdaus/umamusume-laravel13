<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Blade;

/*
 * Shipped-copy hygiene, measured on the view sources.
 *
 * KI-7 exists because `tools/gate.py` checks the D-79 em dash only over prototype
 * HTML, so a whole slice of Blade work landed with em dashes in copy that renders
 * (docs/GATE-REGISTRY.md lists that gap). This is the catch the gate did not have:
 * it reads every Blade file, strips the comment forms a view can hide prose in, and
 * fails on any dash that would reach a Trainer.
 *
 * Comments are stripped rather than skipped because they are where the dash is
 * legitimate — this repository's own prose about the rule uses one.
 */

/**
 * @return array<string, list<int>> file => line numbers holding a rendered dash
 */
function renderedDashLines(): array
{
    $views = base_path('resources/views');
    $hits = [];

    // RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`,
    // so a glob-based sweep would scan one directory and pass without looking.
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($views, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        // getExtension() answers "php" for foo.blade.php, so the suffix is matched
        // on the filename. Filtering on the extension silently scans nothing.
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $hits += checkViewSource($file->getPathname());
    }

    return $hits;
}

/**
 * @return array<string, list<int>>
 */
function checkViewSource(string $path): array
{
    $source = (string) file_get_contents($path);

    // Blade comments, then the two PHP comment forms a @php block uses. A `//` line
    // is only stripped when it starts the line, so URLs in markup survive.
    $code = preg_replace(
        ['/\{\{--.*?--\}\}/s', '/\/\*.*?\*\//s', '/^\s*\/\/.*$/m'],
        '',
        $source,
    );

    $lines = [];

    foreach (preg_split('/\R/', (string) $code) ?: [] as $index => $line) {
        if (preg_match('/[\x{2013}\x{2014}]/u', $line) === 1) {
            $lines[str_replace(base_path().DIRECTORY_SEPARATOR, '', $path)][] = $index + 1;
        }
    }

    return $lines;
}

it('scans every Blade view, not just the top directory', function (): void {
    $views = base_path('resources/views');
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($views, FilesystemIterator::SKIP_DOTS),
    );

    $count = 0;

    foreach ($walk as $file) {
        if (str_ends_with($file->getFilename(), '.blade.php')) {
            $count++;
        }
    }

    // The guard test above is worthless if it walks nothing, so the sweep has a
    // floor: components/, runs/, catalog/, review/ and the vendor pagination override.
    expect($count)->toBeGreaterThanOrEqual(12);
});

it('loads no remote stylesheet or font on first paint', function (): void {
    // KI-3 / PRD NFR-1: this is a local-only tool, so a `<link>` to a font CDN is a
    // network dependency in front of the first render. `welcome.blade.php` carried two
    // to fonts.bunny.net, and DESIGN.md §7 had called it "a one-line fix nobody has
    // applied" — a guard is what stops it being a two-line fix nobody applies next time.
    //
    // Anchor `href`s are allowed: a link someone can choose to click is not a
    // dependency the page cannot render without.
    $views = base_path('resources/views');
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($views, FilesystemIterator::SKIP_DOTS),
    );

    $offenders = [];

    foreach ($walk as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        if (preg_match_all('#<(?:link[^>]*rel=["\'](?:stylesheet|preconnect|preload|dns-prefetch)["\'][^>]*>|script[^>]*src=["\']https?://)#i', $source, $hits) > 0) {
            foreach ($hits[0] as $tag) {
                // A same-origin or relative asset is not a network dependency.
                if (preg_match('#(?:href|src)=["\'](https?://|//)#i', $tag) === 1) {
                    $offenders[] = str_replace($views.DIRECTORY_SEPARATOR, '', $file->getPathname()).': '.substr($tag, 0, 90);
                }
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('ships no comment form that Blade does not understand', function (): void {
    $views = base_path('resources/views');
    $offenders = [];

    // `{# ... #}` is a Vue/Angular comment, not a Blade one, and Blade renders it as page
    // text. `stat-band.blade.php` carried seven lines of design rationale this way and it
    // only became visible when the band reached a Trainer's screen: on the review surface
    // nobody reads the prose. The dash guard below strips `{{-- --}}` and the two PHP
    // forms; this catches the form that is not a comment at all.
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($views, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        foreach (preg_split('/\R/', (string) file_get_contents($file->getPathname())) ?: [] as $index => $line) {
            if (preg_match('/^\s*(\{#|#})\s*$/', $line) === 1) {
                $offenders[] = str_replace($views.DIRECTORY_SEPARATOR, '', $file->getPathname()).':'.($index + 1);
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('ships no em dash or en dash in rendered Blade copy', function (): void {
    $hits = renderedDashLines();

    // R-02 and D-79 ban the em dash in shipped copy, and the owner's 2026-09-28
    // ruling settled the disclosure glyph on `N/A` plus an optional title tooltip.
    // The file list is reported so a failure says where to look.
    expect($hits)->toBe([]);
});

it('renders N/A rather than a glyph when a run has recorded no value', function (): void {
    $html = Blade::render(
        '<x-resource-strip :scenario="$scenario" :run="$run" />',
        ['scenario' => 'unity_cup', 'run' => ['turn' => 3]],
    );

    // Every widget the scenario owns but the run has not filled must say so in
    // words a screen reader can read, not in a dash.
    expect($html)
        ->toContain('N/A')
        ->not->toContain('—')
        ->toContain('title="No value recorded for this run"');

    expect(substr_count($html, 'N/A'))->toBeGreaterThanOrEqual(4);
});
