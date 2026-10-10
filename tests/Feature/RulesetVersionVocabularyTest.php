<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

// R2-19. One fact travels the application: the Global ruleset *version*, which `HandleInertiaRequests`
// carries as `app.ruleset` = null because no source in this repository names one (design-2.0 §48). It
// reaches a Trainer on six surfaces, and until this pass four of them headed it with four different
// words while the refusal beside each of them said `version`. R2-05 was one card printing two facts under
// one heading; this is one fact printed under four headings, which reads as four different absences.
//
// The pair of cases below is the whole contract: the first names every surface that prints the fact, so a
// seventh heading cannot arrive without a reviewer noticing; the second forbids the retired wordings
// anywhere in the component tree, so the next page to print this fact cannot borrow one.

/**
 * Every shipped surface that prints the Global ruleset version, and the heading it must use.
 *
 * @var array<string, string>
 */
const RULESET_VERSION_SURFACES = [
    'pages/Career/ScenarioSelect.vue' => '/>Ruleset version</',
    'components/scenario/ScenarioPanel.vue' => '/^\s*Ruleset version:$/m',
    'pages/Dashboard.vue' => '/^\s*Ruleset version:$/m',
    'pages/Career/Result.vue' => '/^\s*Ruleset version:$/m',
    'pages/Career/Preflight.vue' => '/^\s*Ruleset version:$/m',
    'pages/Preferences/Edit.vue' => '/>Global Ruleset Version</',
];

it('heads the Global ruleset version the same way on every surface that prints it', function (): void {
    foreach (RULESET_VERSION_SURFACES as $path => $heading) {
        $source = (string) file_get_contents(resource_path('js/'.$path));

        expect($source)->toMatch($heading, $path.' does not head the version fact the way the list says it does');
    }

    // The count is the guard. A surface that starts printing this fact has to join the list, and one that
    // stops printing it cannot leave the list standing as the record of what the tool shows.
    expect(RULESET_VERSION_SURFACES)->toHaveCount(6);
});

it('never heads the Global ruleset version with a word that is not version', function (): void {
    // The wordings R2-19 retired. `Ruleset:` alone, `Ruleset snapshot:` on the wizard's last step, and a
    // bare `<dt>Ruleset</dt>` or `<dt>Global Ruleset</dt>` beside a value that is a version. Anchored to
    // the line start because these are template text lines, and a comment or a doc that quotes a retired
    // wording is prose, not a heading.
    $retired = [
        '/^\s*Ruleset:$/m',
        '/^\s*Ruleset snapshot:$/m',
        '/<dt[^>]*>\s*Ruleset\s*</',
        '/<dt[^>]*>\s*Global Ruleset\s*</',
    ];

    $scanned = 0;

    // `allFiles()` and not `files()`: every component lives under `pages/` or `components/`, and the
    // counter below proves the walk reached them rather than reporting a clean tree it never read.
    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        $scanned++;
        $source = (string) file_get_contents($file->getPathname());

        foreach ($retired as $pattern) {
            expect($source)->not->toMatch($pattern, $file->getPathname());
        }
    }

    // The sweep above is a guard, not a pass, unless it actually read the component tree.
    expect($scanned)->toBeGreaterThan(50);
});
