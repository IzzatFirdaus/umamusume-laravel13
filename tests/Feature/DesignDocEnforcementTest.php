<?php

/*
 * `DESIGN.md` §10 is the enforcement index, and its own history is the reason this file exists: the
 * token count drifted from 60 to 59 to 81 while the table kept naming the older number, and §8 claimed
 * the tree carried zero `transition-*` utilities years after MOTION 2 added them.
 *
 * A rewritten sentence is not enforcement. This pins the two mechanical claims to the artifacts they
 * describe, so the next drift fails a test instead of waiting for an audit:
 *
 *  1. The count §10 states is the count `DesignTokensTest` asserts.
 *  2. §8's "zero transition-*" claim is not restored while the utilities ship.
 *
 * It is deliberately narrow. A general "docs match code" gate is a second documentation system, and
 * the rest of the table is prose that needs a reader, not a regex.
 */

/** The `toHaveCount(N)` the token tripwire asserts, read from the test rather than remembered. */
function documentedTokenCount(): int
{
    $test = (string) file_get_contents(base_path('tests/Feature/DesignTokensTest.php'));

    expect(preg_match('/expect\(\$tokens\)->toHaveCount\((\d+)\)/', $test, $match))->toBe(1);

    return (int) $match[1];
}

it('states in DESIGN.md the same token count the tripwire asserts', function (): void {
    $design = (string) file_get_contents(base_path('DESIGN.md'));
    $count = documentedTokenCount();

    // The pinned number must be the real one: a tripwire that pins a stale figure would agree with a
    // stale document and the pair would pass together, which is the failure mode being closed.
    $declared = count(themeStaticColorTokens());

    expect($declared === $count)->toBeTrue(
        "The tripwire asserts {$count} but the stylesheet declares {$declared} unique --color-* names."
    );

    // And the note this pass appended must name the same number. Anchored on the note's own sentence,
    // because a bare `81` anywhere in the file would pass on an unrelated figure.
    expect(str_contains($design, "`toHaveCount({$count})`"))->toBeTrue(
        "DESIGN.md's §10 note does not name `toHaveCount({$count})`; the doc and the tripwire disagree."
    );
});

it('does not claim a motionless tree while MOTION 2 utilities ship', function (): void {
    $design = (string) file_get_contents(base_path('DESIGN.md'));

    $utilities = 0;

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('resources/js'))) as $file) {
        if ($file->isDir() || ! in_array($file->getExtension(), ['vue', 'ts'], true)) {
            continue;
        }

        preg_match_all('/transition-|duration-\d|animate-/', (string) file_get_contents($file->getPathname()), $matches);
        $utilities += count($matches[0]);
    }

    // A floor, not an equality: the number moves with every slice, and this file owns the claim that
    // the tree is motionless, not the count itself.
    expect($utilities > 0)->toBeTrue('No motion utilities found; the fixture assumption is broken.');

    // The exact superseded sentence, which §8's 2026-10-22 note now answers. If someone restores it
    // verbatim, the note contradicts the body again. Whitespace-normalised first, because the sentence
    // wraps across two lines in the file and a raw `str_contains` would miss it on line breaks.
    $flat = (string) preg_replace('/\s+/', ' ', $design);

    expect(str_contains($flat, 'zero `transition-*`, `duration-*` and `animate-*` utilities, so hover'))
        ->toBeTrue('§8 no longer carries the sentence the 2026-10-22 note corrects; re-point or retire the note.');

    expect(str_contains($design, 'Re-read 2026-10-22 (UX audit F-07, F-08)'))
        ->toBeTrue('§10 lost its 2026-10-22 re-read note, so its rows describe a tree that no longer exists.');
});
