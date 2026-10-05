<?php

declare(strict_types=1);

/*
 * An ink token borrowed across roles passes the contrast gate and fails the design system:
 * `text-on-pick` on `bg-green` measured fine, and it meant "the dark ink that happens to stay
 * dark in both themes" rather than "the ink for this fill". Slice 5 recorded the borrow as an
 * open design-system call; Slice 6 settles it by naming the pair.
 *
 * This test pins the naming, not the ratio. The ratios are measured from the rendered element
 * in `docs/design-research/verification/slice-6-2026-09-28.md` §3, per D-288: a hex in a
 * stylesheet proves nothing about what a browser paints.
 *
 * The sweep covers both source trees. The components it guards are Vue now (ADR-0020 §1, B1),
 * so a Blade-only walk would go quiet the moment the last component moved.
 */
it('declares an ink for the bright lime fill instead of borrowing the gold one', function (): void {
    $css = (string) file_get_contents(base_path('resources/css/app.css'));

    expect($css)->toContain('--color-on-green');
});

/**
 * Every source that can put a class on an element a Trainer sees, keyed by its path.
 *
 * RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`, so a
 * glob-based sweep reads one directory and passes without having looked at the rest.
 *
 * @return array<string, string>
 */
function markupSources(): array
{
    $sources = [];

    foreach (['resources/views', 'resources/js'] as $root) {
        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path($root), FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            // getExtension() answers "php" for foo.blade.php, so the suffix is matched on the
            // whole filename. Filtering on the extension silently scans nothing.
            if (preg_match('/(\.blade\.php|\.vue|\.ts)$/', $file->getFilename()) === 1) {
                $sources[str_replace(
                    [DIRECTORY_SEPARATOR, base_path().DIRECTORY_SEPARATOR],
                    ['/', ''],
                    $file->getPathname(),
                )] = (string) file_get_contents($file->getPathname());
            }
        }
    }

    return $sources;
}

/**
 * Comments stripped from a source, so prose naming the rejected pair does not trip the gate.
 *
 * @param  array<string, string>  $sources
 * @return array<string, string>
 */
function withoutMarkupComments(array $sources): array
{
    $patterns = [
        '/\{\{--.*?--\}\}/s',   // Blade
        '/<!--.*?-->/s',        // Vue template
        '/\/\*.*?\*\//s',       // PHP and TypeScript block comments
        '/(?<!:)\/\/[^\n]*/',   // line comments, leaving a `:` prefix alone (URLs in strings)
    ];

    return array_map(
        static function (string $source) use ($patterns): string {
            foreach ($patterns as $pattern) {
                $source = (string) preg_replace($pattern, '', $source);
            }

            return $source;
        },
        $sources,
    );
}

it('leaves no component wearing the gold ink on the green fill', function (): void {
    $offenders = [];

    foreach (withoutMarkupComments(markupSources()) as $path => $source) {
        foreach (explode("\n", $source) as $number => $line) {
            // `\b` does not hold at the hyphen, so `bg-green-tint` and `bg-green-line` match
            // `\bbg-green\b`. The negative lookahead keeps the gate on the bare fill, which is
            // the pair this rule was written for.
            if (preg_match('/\bbg-green(?![-\w])/', $line) === 1
                && preg_match('/\btext-on-pick\b/', $line) === 1) {
                $offenders[] = $path.':'.($number + 1);
            }
        }
    }

    expect($offenders)->toBe([], 'green fill must take --color-on-green, not --color-on-pick');
});

it('keeps the gold ink on the gold fill, where it belongs', function (): void {
    // The port moved this pair from `x-guided-step` to `GuidedStep.vue`. The Caution band
    // still uses `bg-pick text-on-pick`; if that pair had been renamed away rather than the
    // borrow removed, this test is how it would show up.
    $step = (string) file_get_contents(base_path('resources/js/components/GuidedStep.vue'));

    expect($step)->toContain('bg-pick text-on-pick')
        ->and($step)->toContain('bg-green px-1.5 font-bold text-on-green');
});
