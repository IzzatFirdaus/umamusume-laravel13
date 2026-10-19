<?php

declare(strict_types=1);

/*
 * Plan 2.5 (batch item "one helper sentence per group on non-wizard screens"): every helper
 * sentence renders once per field group rather than once per field, and standing caveats render
 * once per surface rather than once per card.
 *
 * These are source scans, the same shape `RenderedCopyHygieneTest` uses: the sentences under test
 * live in Vue templates, so the source is the artifact a duplicated sentence would show up in.
 * `substr_count` over the source, not over a rendered page, because a sentence inside a `v-for`
 * appears once in source and many times in markup, and the rule is about what ships in the file.
 */

it('keeps the Legacy builder helpers once per group, not once per field', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/pages/Legacy/Builder.vue'));

    // The per-field hints are gone: two rank fields per parent and per ancestor used to each carry
    // the same two sentences, so one group read its own rule six times. The group's sentence now
    // sits once under its heading.
    expect(substr_count($source, 'hint="'))->toBe(0)
        ->and(substr_count($source, 'Own rank is unbounded and blank stays not recorded'))->toBe(1)
        ->and(substr_count($source, 'Stored by name; leave blank if you have not recorded one'))->toBe(1);
});

it('shows the distance-band note once, above the race list', function (): void {
    $card = (string) file_get_contents(base_path('resources/js/components/career/RaceCard.vue'));
    $list = (string) file_get_contents(base_path('resources/js/components/career/RacePlanList.vue'));

    // The card stops printing the band caveat under every race; the list prints it once.
    expect($card)->toContain("fact.key !== 'distance_band'")
        ->and(substr_count($list, "Band names are the export's own"))->toBe(1);
});

it('carries the no-suggestion explanation once above the review list, not on each card', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/pages/Review/Index.vue'));

    expect(substr_count($source, 'fuzzy-match any trainee'))->toBe(1);
});

it('carries the record-only banner only on the Legacy builder', function (): void {
    // The banner renders `{{ notice }}` (or `{{ props.notice }}`); the controllers still pass the
    // prop, so the rule is about which templates draw it. `Career/LegacySelect.vue` is exempted
    // with a dated note: a peer holds that file dirty in the shared tree (2026-10-19), so its
    // banner deletion is the peer's slice to land, and scanning it here would pin a file this
    // slice must not touch.
    $builder = (string) file_get_contents(base_path('resources/js/pages/Legacy/Builder.vue'));
    expect($builder)->toContain('{{ notice }}');

    // `Career/LegacySelect.vue` is exempted with a dated note: a peer holds that file dirty in the
    // shared tree (2026-10-19), so its banner deletion is the peer's slice to land, and scanning it
    // here would pin a file this slice must not touch.
    $skipped = ['resources/js/pages/Career/LegacySelect.vue'];

    // RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`.
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/js'), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! str_ends_with($file->getFilename(), '.vue')) {
            continue;
        }

        $relative = str_replace('\\', '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()));
        if (in_array($relative, $skipped, true) || $relative === 'resources/js/pages/Legacy/Builder.vue') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());
        expect($source)->not->toContain('{{ notice }}')->and($source)->not->toContain('{{ props.notice }}');
    }
});
