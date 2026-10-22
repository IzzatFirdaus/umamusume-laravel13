<?php

/*
 * One absence channel, reachable without a mouse (`DESIGN.md` §5 (a), 2026-10-17).
 *
 * The rule: `N/A` carries its reason either through `AbsenceValue`'s disclosure or as visible prose
 * beside the marker. A `title` is the superseded idiom — reachable by a pointer and by nothing else —
 * and whenever it rides the same element that prints `N/A`, the component has two channels for one
 * absence and one of them is mouse-only.
 *
 * This scans the Vue tree because the defect is a shape, not a screen: the 2026-10-17 sweep found 17
 * single-line sites and could not see the multi-line ones (`RaceCard.vue:130`/`:132`, and five of the
 * six sites this gate was written for). A source scan is the only gate that covers a pattern across
 * files without a fixture per component.
 *
 * **What it deliberately does not flag.** A `title` on a *present* value (a provenance qualifier, a
 * `ProvenanceBadge` note) is a different question and is not an absence channel. So the scan keys on an
 * element that renders `N/A` itself — the literal, or a fallback expression producing it — and carries
 * an attribute literally named `title` or `:title`.
 *
 * The tag reader is a linear character scan rather than a regex: the obvious
 * `(?:[^>"']|"[^"]*"|'[^']*')*` form backtracks catastrophically on a 40 KB SFC full of apostrophes in
 * prose comments, which is how the first version of this file hung the suite instead of failing it.
 */

/**
 * Every start tag in a source file: `[offset, end, attributes, name]`, where `end` is the offset of
 * the tag's closing `>`.
 *
 * @return array<int, array{offset: int, end: int, attributes: string, name: string}>
 */
function vueStartTags(string $source): array
{
    $tags = [];
    $length = strlen($source);

    for ($i = 0; $i < $length; $i++) {
        if ($source[$i] !== '<') {
            continue;
        }

        $next = $source[$i + 1] ?? '';

        if (! ctype_alpha($next)) {
            continue;
        }

        $name = '';
        $j = $i + 1;

        while ($j < $length && (ctype_alnum($source[$j]) || in_array($source[$j], ['-', '_'], true))) {
            $name .= $source[$j];
            $j++;
        }

        // Attributes run to the closing `>`, skipping over quoted values so a `>` inside a string
        // does not end the tag early.
        $attributes = '';
        $quote = null;

        while ($j < $length) {
            $char = $source[$j];

            if ($quote !== null) {
                if ($char === $quote) {
                    $quote = null;
                }
            } elseif ($char === '"' || $char === "'") {
                $quote = $char;
            } elseif ($char === '>') {
                break;
            }

            $attributes .= $char;
            $j++;
        }

        $tags[] = ['offset' => $i, 'end' => $j, 'attributes' => $attributes, 'name' => $name];

        // Continue after the tag, so the closing `>` is not reconsidered as a new tag's start.
        $i = $j;
    }

    return $tags;
}

/** @return array<int, array{file: string, line: int, text: string}> */
function absenceTitleSites(): array
{
    $root = base_path('resources/js');
    $sites = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isDir() || ! in_array($file->getExtension(), ['vue', 'ts'], true)) {
            continue;
        }

        $path = $file->getPathname();
        $source = (string) file_get_contents($path);

        foreach (vueStartTags($source) as $tag) {
            // The element's OWN text: everything between this start tag's `>` and the next `<`.
            // Anything wider picks up a later sibling's `N/A` and reports innocent elements.
            $textStart = $tag['end'] + 1;
            $nextTag = strpos($source, '<', $textStart);
            $ownText = substr($source, $textStart, ($nextTag === false ? strlen($source) : $nextTag) - $textStart);

            if (! str_contains($ownText, 'N/A')) {
                continue;
            }

            if (preg_match('/(?:^|\s):?title\s*=/', $tag['attributes']) !== 1) {
                continue;
            }

            $sites[] = [
                'file' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $path),
                'line' => substr_count(substr($source, 0, $tag['offset']), "\n") + 1,
                'text' => trim(substr($tag['attributes'], 0, 100)),
            ];
        }
    }

    return $sites;
}

it('never adds a new N/A whose reason is only on a title', function (): void {
    $sites = absenceTitleSites();

    // A floor on the scan itself: a reader that found no tags would pass on a tree full of the defect.
    expect(count(vueStartTags((string) file_get_contents(base_path('resources/js/components/AbsenceValue.vue')))))
        ->toBeGreaterThan(0);

    /*
     * The six sites the UX audit's F-11 named are migrated and must stay migrated. This half is the
     * assertion that fails if any of them is reverted to the hover-only shape.
     */
    $migrated = [
        'resources/js/components/scenario/TrackblazerFatiguePanel.vue',
        'resources/js/components/scenario/TrackblazerRoutePlanner.vue',
        // FQ-12 (2026-10-23): UraMilestoneTracker.vue was deleted; its entry left this list
        // with it. The disclosure channel it was migrated to is unchanged on every other site.
        'resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue',
        'resources/js/pages/Career/Cockpit/Panels/TeamRankLadderPanel.vue',
        'resources/js/pages/Career/Cockpit/Panels/UraMandatoryLadder.vue',
        // Already migrated before this pass; listed so the same regression is caught here.
        'resources/js/components/ui/DataTable.vue',
        'resources/js/components/SubCell.vue',
        'resources/js/components/scenario/ScenarioPanel.vue',
        'resources/js/components/ProvenanceMark.vue',
    ];

    $regressed = array_values(array_filter(
        $sites,
        static fn (array $site): bool => in_array($site['file'], $migrated, true),
    ));

    expect($regressed)->toBe([], "A migrated site went back to a `title`-only reason:\n"
        .implode("\n", array_map(static fn (array $s): string => $s['file'].':'.$s['line'], $regressed)));

    /*
     * And the remaining inventory may not grow. It is measured, not guessed: **74 sites** when this
     * gate landed on 2026-10-22. The audit report `F-12` estimated 20 across 10 files; the measured
     * figure is 74, because a single-line sweep cannot see the multi-line shape and this reader can.
     *
     * This is a ratchet, not an endorsement. It exists so the migration that is not done today cannot
     * get quietly larger while it waits, and the failure message is the work list for the next pass.
     * Lowering the number is the point of that pass; raising it needs the migration, not an edit here.
     */
    $budget = 74;

    $inventory = implode("\n", array_map(
        static fn (array $site): string => sprintf('%s:%d', $site['file'], $site['line']),
        $sites,
    ));

    expect(count($sites))->toBeLessThanOrEqual(
        $budget,
        "The title-only `N/A` inventory grew past its measured ceiling of {$budget}.\n"
        ."A new site may not be added while the migration is outstanding; fix the new one, or migrate and lower the ceiling.\n"
        ."Current inventory:\n".$inventory,
    );
});
