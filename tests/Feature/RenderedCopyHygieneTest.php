<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Shipped-copy hygiene, measured on the view sources.
 *
 * KI-7 exists because `tools/gate.py` checks the D-79 em dash only over prototype
 * HTML, so a whole slice of Blade work landed with em dashes in copy that renders
 * (docs/GATE-REGISTRY.md lists that gap). This is the catch the gate did not have:
 * it reads every view file, strips the comment forms a view can hide prose in, and
 * fails on any dash that would reach a Trainer.
 *
 * Comments are stripped rather than skipped because they are where the dash is
 * legitimate — this repository's own prose about the rule uses one.
 *
 * Since the Inertia port (ADR-0020 §1, B1) almost every surface is a Vue component or
 * page, so the sweep covers both trees. A Blade-only walk would have gone quiet on the
 * day the last component moved and proved nothing from then on, which is the same shape
 * of gate DesignTokensTest already had to be widened for.
 */

/**
 * Every source file that can put shipped copy in front of a Trainer, keyed by path.
 *
 * RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`, so a
 * glob-based sweep would scan one directory and pass without looking.
 *
 * @return array<string, string>
 */
function shippedCopySources(): array
{
    $sources = [];

    foreach (['resources/views', 'resources/js'] as $root) {
        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path($root), FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            $name = $file->getFilename();

            // getExtension() answers "php" for foo.blade.php, so the suffix is matched
            // on the filename. Filtering on the extension silently scans nothing.
            if (! str_ends_with($name, '.blade.php') && ! str_ends_with($name, '.vue')) {
                continue;
            }

            $sources[str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname())] =
                (string) file_get_contents($file->getPathname());
        }
    }

    return $sources;
}

/**
 * The comment forms a view or a single-file component can hide prose in, removed.
 *
 * A `//` line is only stripped when it starts the line, so URLs in markup survive.
 */
function shippedCopyCode(string $source): string
{
    return (string) preg_replace(
        ['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s', '#/\*.*?\*/#s', '/^\s*\/\/.*$/m'],
        '',
        $source,
    );
}

/**
 * @return array<string, list<int>> file => line numbers holding a rendered dash
 */
function renderedDashLines(): array
{
    $hits = [];

    foreach (shippedCopySources() as $path => $source) {
        $lines = [];

        foreach (preg_split('/\R/', shippedCopyCode($source)) ?: [] as $index => $line) {
            if (preg_match('/[\x{2013}\x{2014}]/u', $line) === 1) {
                $lines[] = $index + 1;
            }
        }

        if ($lines !== []) {
            $hits[$path] = $lines;
        }
    }

    return $hits;
}

it('scans every view and component, not just the top directory', function (): void {
    $sources = shippedCopySources();
    // `$paths`, not `$vue`/`$blade`: the filtered values are the paths themselves, and taking
    // `array_keys()` of that list afterwards would yield list indices (0, 1, 2 …) rather than
    // paths. `array_values()` keeps them as paths with contiguous keys.
    $paths = array_values(array_keys($sources));
    $blade = array_filter($paths, static fn (string $path): bool => str_ends_with($path, '.blade.php'));
    $vue = array_filter($paths, static fn (string $path): bool => str_ends_with($path, '.vue'));

    // The guards below are worthless if the sweep walks nothing, so it has a floor on each tree
    // rather than one total: the Vue tree is where the copy lives now, and a total would be
    // satisfied by either tree alone.
    //
    // The Vue floor was an exact count of 40 until 2026-10-05, and it was the wrong shape. Every
    // Phase D slice adds components and pages, so the number is a constant that each slice has to
    // bump and none of them can own: two slices landing at once (D5's Legacy Lab and D6's Support
    // deck builder) each failed the other's count, and the "fix" would have been to bake in whichever
    // session happened to commit last. The floor keeps what the assertion is actually for — proving
    // the recursive walk reaches the whole Vue tree rather than one directory, which a Blade-only
    // sweep would satisfy vacuously now that almost every surface is a component — and stops it being
    // a collision oracle. 40 is the count the exact assertion was written against, so anything that
    // silently stops scanning that many files still fails.
    // Normalise the host's path separator before matching, so the assertion holds on Windows (`\`)
    // and on the `/` hosts this suite also runs on. `getPathname()` is what builds the key, so the
    // separator is the host's rather than the repository's.
    $vuePaths = array_map(static fn (string $path): string => str_replace('\\', '/', $path), $vue);

    expect($blade)->not->toBeEmpty()
        ->and(count($vue))->toBeGreaterThanOrEqual(40)
        // A named surface from each tree, so a walk that found files by accident while skipping
        // nested directories would still fail.
        ->and(array_filter($vuePaths, static fn (string $path): bool => str_ends_with($path, 'pages/Legacy/Builder.vue')))->not->toBeEmpty()
        ->and(array_filter($vuePaths, static fn (string $path): bool => str_ends_with($path, 'components/legacy/AncestryNode.vue')))->not->toBeEmpty();
});

it('loads no remote stylesheet or font on first paint', function (): void {
    // KI-3 / PRD NFR-1: this is a local-only tool, so a `<link>` to a font CDN is a
    // network dependency in front of the first render. `welcome.blade.php` carried two
    // to fonts.bunny.net, and DESIGN.md §7 had called it "a one-line fix nobody has
    // applied" — a guard is what stops it being a two-line fix nobody applies next time.
    //
    // Anchor `href`s are allowed: a link someone can choose to click is not a
    // dependency the page cannot render without.
    $offenders = [];

    foreach (shippedCopySources() as $path => $source) {
        preg_match_all('#<(?:link[^>]*rel=["\'](?:stylesheet|preconnect|preload|dns-prefetch)["\'][^>]*>|script[^>]*src=["\']https?://)#i', $source, $hits);

        foreach ($hits[0] as $tag) {
            // A same-origin or relative asset is not a network dependency.
            if (preg_match('#(?:href|src)=["\'](https?://|//)#i', $tag) === 1) {
                $offenders[] = $path.': '.substr($tag, 0, 90);
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('ships no comment form the template engine does not understand', function (): void {
    $offenders = [];

    // `{# ... #}` is a Vue/Angular comment, not a Blade one, and Blade renders it as page
    // text. `stat-band.blade.php` carried seven lines of design rationale this way and it
    // only became visible when the band reached a Trainer's screen: on the review surface
    // nobody reads the prose. The dash guard strips `{{-- --}}` and the two PHP forms; this
    // catches the form that is not a comment at all.
    foreach (shippedCopySources() as $path => $source) {
        foreach (preg_split('/\R/', $source) ?: [] as $index => $line) {
            if (preg_match('/^\s*(\{#|#})\s*$/', $line) === 1) {
                $offenders[] = $path.':'.($index + 1);
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('ships no em dash or en dash in rendered copy', function (): void {
    $hits = renderedDashLines();

    // R-02 and D-79 ban the em dash in shipped copy, and the owner's 2026-09-28
    // ruling settled the disclosure glyph on `N/A` plus an optional title tooltip.
    // The file list is reported so a failure says where to look.
    expect($hits)->toBe([]);
});

it('renders N/A rather than a glyph when a run has recorded no value', function (): void {
    // A run with no logged turn has no Energy, no Fans and no team figures at all, so every widget
    // Unity Cup owns has to say so in words a screen reader can read, not in a dash. The glyph and
    // its tooltip are the component's, and the empty values are the payload's: a scenario resource
    // with no column yet arrives as an explicit null rather than an absent key, so the widget
    // cannot fall back to a default.
    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.cockpit', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Career/Cockpit')
        ->where('header.values', fn (Collection $values): bool => $values->get('energy') === null
            && $values->get('fans') === null
            && $values->has('team_rank')
            && $values->get('team_rank') === null
            && $values->get('bursts') === null));

    $source = (string) file_get_contents(base_path('resources/js/components/ResourceStrip.vue'));

    expect($source)
        ->toContain("'N/A'")
        ->toContain('No value recorded for this run')
        ->not->toContain('—')
        // Four widgets Unity Cup owns, and each unrecorded one prints the glyph: Turn is the only
        // widget with a figure of its own here, and the count the strip shows is the glyph's.
        ->toContain('const recorded = raw !== null && raw !== \'\';');
});
