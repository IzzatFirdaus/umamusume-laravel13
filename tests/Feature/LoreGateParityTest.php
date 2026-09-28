<?php

declare(strict_types=1);
use Symfony\Component\Process\Process;

/*
 * The lore gates exist twice — once in the Makefile, once in tools/lore.php — and
 * T7 kept both deliberately: `make` is the interface named in CONSTRAINTS.md, and the
 * composer script is the one that runs on a host without GNU make (KI-4).
 *
 * Two copies of a banned-pattern list is exactly the arrangement D-286 warns about, so
 * this pins them against each other. It compares the pattern strings themselves rather
 * than re-parsing flags and pathspecs from both files: the list of banned words is the
 * part that must agree, and a test that reimplements two parsers to compare them is a
 * test that fails for its own reasons.
 */

/**
 * The `git grep` command lines of a Makefile target's recipe, one per swept pattern.
 *
 * @return list<string>
 */
function makefileRecipes(string $target): array
{
    $make = (string) file_get_contents(base_path('Makefile'));

    if (preg_match('/^'.preg_quote($target, '/').':\n((?:[\t ].*\n?)*)/m', $make, $m) !== 1) {
        return [];
    }

    return array_values(array_filter(
        array_map('trim', explode("\n", trim($m[1]))),
        static fn (string $line): bool => str_starts_with($line, 'git grep'),
    ));
}

/**
 * Every "…" quoted search pattern in a Makefile target body, in order.
 *
 * @return list<string>
 */
function makefilePatterns(string $target): array
{
    $make = (string) file_get_contents(base_path('Makefile'));

    if (preg_match('/^'.preg_quote($target, '/').':\n((?:[\t ].*\n?)*)/m', $make, $m) !== 1) {
        return [];
    }

    $patterns = [];

    foreach (explode("\n", trim($m[1])) as $line) {
        if (preg_match('/git grep[^\n]*?"([^"]*)"/', trim($line), $p) === 1) {
            $patterns[] = $p[1];
        }
    }

    return $patterns;
}

/**
 * Every single-quoted "-in…" flag entry in the given mode branch of tools/lore.php.
 *
 * @return list<string>
 */
function scriptPatterns(string $mode): array
{
    $php = (string) file_get_contents(base_path('tools/lore.php'));

    // The runner holds one ternary, so 'code' appears in the source and 'docs' does not:
    // the two branches are the whole block, split on the line that opens the false arm.
    if (preg_match('/\$runs = \$mode === \'code\'([\s\S]*?\n    \];)/', $php, $m) !== 1) {
        return [];
    }

    $branches = preg_split('/^[ \t]*:[ \t]*\[/m', $m[1], 2);
    $branch = $mode === 'code' ? ($branches[0] ?? '') : ($branches[1] ?? '');

    $patterns = [];

    if (preg_match_all("/\['-in[weE]+', '((?:[^']|\\\\')*)',/", $branch, $hits) > 0) {
        $patterns = array_map(
            static fn (string $p): string => str_replace("\\'", "'", $p),
            $hits[1],
        );
    }

    return $patterns;
}

it('keeps both Makefile lore targets present, as T7 required', function (): void {
    $make = (string) file_get_contents(base_path('Makefile'));

    // If a later slice deletes the Makefile, the gate name CONSTRAINTS.md tells every
    // role to run stops resolving. That has to be a decision, not drift.
    expect($make)->toMatch('/^lore:/m')
        ->and($make)->toMatch('/^lore-code:/m');
});

it('declares three greps in each mode in both implementations', function (): void {
    expect(makefilePatterns('lore'))->toHaveCount(3)
        ->and(makefilePatterns('lore-code'))->toHaveCount(3)
        ->and(scriptPatterns('docs'))->toHaveCount(3)
        ->and(scriptPatterns('code'))->toHaveCount(3);
});

it('bans the same words in the Makefile and in the runner', function (string $target, string $mode): void {
    $fromMake = makefilePatterns($target);
    $fromScript = scriptPatterns($mode);

    // Compared as sets: order between the two files is cosmetic, a pattern present in
    // one and missing from the other is the defect.
    sort($fromMake);
    sort($fromScript);

    expect($fromScript)->toBe($fromMake);
})->with([
    ['lore', 'docs'],
    ['lore-code', 'code'],
]);

it('exposes both gates as composer scripts pointing at the runner', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true);

    expect($composer['scripts']['lore'] ?? null)->toBe('@php tools/lore.php')
        ->and($composer['scripts']['lore-code'] ?? null)->toBe('@php tools/lore.php code');
});

it('prints a hit count, so silence cannot read as a pass', function (string $mode): void {
    $output = (string) shell_exec('php '.escapeshellarg(base_path('tools/lore.php')).' '.$mode.' 2>&1');

    // The bug this guards: cmd.exe leaves the single quotes on a pathspec, git errors,
    // `|| true` swallows it, and the gate prints nothing and exits 0.
    expect($output)->toMatch('/lore-'.$mode.': \d+ hit\(s\)/')
        ->and($output)->not->toContain('outside repository');
})->with(['docs', 'code']);

/**
 * Every `<!-- lore-ignore-line ... -->` directive the tracked tree carries.
 *
 * The comment opener is part of the needle, so a heading or a sentence that only names
 * the mechanism is not mistaken for a directive - and the three files that implement or
 * enforce it must spell its name to do so, so they are excluded on the same grounds as
 * class 4 of `docs/GATE-REGISTRY.md`: a gate's own source is a permanent self-hit.
 * Nothing else is exempt, and `docs/` files are not in that list: a `docs/` line opening
 * the comment is required to be a well-formed marker.
 *
 * @return list<array{path: string, line: int, text: string}>
 */
function loreMarkers(): array
{
    $process = new Process([
        'git', 'grep', '-nF', '<!-- lore-ignore-line', '--',
        ':(exclude)tools/lore.php',
        ':(exclude)Makefile',
        ':(exclude)tests/Feature/LoreGateParityTest.php',
    ], base_path());
    $process->run();

    // git grep exits 1 when nothing matched; that is empty, not an error.
    if (! in_array($process->getExitCode(), [0, 1], true)) {
        throw new RuntimeException("loreMarkers: git grep failed: {$process->getErrorOutput()}");
    }

    $output = trim($process->getOutput());

    if ($output === '') {
        return [];
    }

    $markers = [];

    foreach (explode("\n", $output) as $hit) {
        [$path, $line, $text] = explode(':', $hit, 3);
        $markers[] = ['path' => $path, 'line' => (int) $line, 'text' => $text];
    }

    return $markers;
}

it('filters marked lines in the docs gate and refuses to in the code gate', function (): void {
    $runner = (string) file_get_contents(base_path('tools/lore.php'));

    // R51 puts markers in `docs/` and nowhere else, so only the docs gate honours one.
    foreach (makefileRecipes('lore') as $recipe) {
        expect($recipe)->toContain('lore-ignore-line');
    }

    // An illegal marker outside `docs/` must buy nothing: the line it tried to hide
    // still prints. A slice that adds the pipe to `lore-code` has opened the exemption.
    foreach (makefileRecipes('lore-code') as $recipe) {
        expect($recipe)->not->toContain('lore-ignore-line');
    }

    // And the runner's filter is mode-guarded, so the two copies agree on which gate skips.
    expect($runner)->toMatch("/\\\$mode === 'docs' && str_contains\\(\\\$hit, '<!-- lore-ignore-line'\\)/");
});

it('keeps every line marker inside docs/', function (): void {
    $offenders = array_map(
        static fn (array $m): string => $m['path'].':'.$m['line'],
        array_values(array_filter(
            loreMarkers(),
            static fn (array $m): bool => ! str_starts_with($m['path'], 'docs/'),
        )),
    );

    // The escape hatch stays narrow on purpose: a marker in app code, a view, or a root
    // planning file would let the surface that ships copy silence its own grep.
    expect($offenders)->toBeEmpty();
});

it('requires a class and a citation on every line marker', function (): void {
    $malformed = array_values(array_filter(
        loreMarkers(),
        static fn (array $m): bool => preg_match('/\bclass=[1-4]\b/', $m['text']) !== 1
            || preg_match('/\bcite=\S+/', $m['text']) !== 1,
    ));

    expect(array_map(
        static fn (array $m): string => $m['path'].':'.$m['line'],
        $malformed,
    ))->toBeEmpty();
});

it('has the marker actually in use, so the guards above are not reading an empty list', function (): void {
    expect(loreMarkers())->not->toBeEmpty();
});
