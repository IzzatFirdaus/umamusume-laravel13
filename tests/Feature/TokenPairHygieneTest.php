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
 */
it('declares an ink for the bright lime fill instead of borrowing the gold one', function (): void {
    $css = (string) file_get_contents(base_path('resources/css/app.css'));

    expect($css)->toContain('--color-on-green');
});

it('leaves no component wearing the gold ink on the green fill', function (): void {
    $views = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views')),
    );

    $offenders = [];

    foreach ($views as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        // Prose is allowed to name the rejected pair to say it was rejected, which is exactly
        // what layout.blade.php does. Lines inside a `{{-- --}}` block and `//` lines are
        // comment prose; everything else is markup and is scanned with its real line number.
        $inComment = false;

        foreach (explode("\n", $source) as $number => $line) {
            if ($inComment) {
                $inComment = ! str_contains($line, '--}}');

                continue;
            }

            if (preg_match('/\{\{--(?![\s\S]*?--\}\})/', $line)) {
                $inComment = true;

                continue;
            }

            if (preg_match('/^\s*(\/\/|\*)/', $line)) {
                continue;
            }

            if (preg_match('/\bbg-green\b/', $line) && preg_match('/\btext-on-pick\b/', $line)) {
                $offenders[] = $file->getRelativePathname().':'.($number + 1);
            }
        }
    }

    expect($offenders)->toBe([], 'green fill must take --color-on-green, not --color-on-pick');
});

it('keeps the gold ink on the gold fill, where it belongs', function (): void {
    $hint = (string) file_get_contents(base_path('resources/views/components/guided-step.blade.php'));

    // The Caution band still uses `bg-pick text-on-pick`. If that pair had been renamed away
    // rather than the borrow removed, this test is how it would show up.
    expect($hint)->toContain('bg-pick text-on-pick')
        ->and($hint)->toContain('bg-green px-1.5 font-bold text-on-green');
});
