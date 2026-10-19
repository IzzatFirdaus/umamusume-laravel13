<?php

declare(strict_types=1);

/*
 * Plan slice 4.1: the career section nav's two structural contracts, plus its adoption.
 *
 * These are source scans, and they are the FIRST line for a specific reason: `career-section-nav.spec.ts`
 * renders app routes, so it needs `public/build` to carry the component under test. The manifest is
 * 2026-10-19 19:04:47 while `resources/js` is 22:00:33 and the tree holds peer-dirty Vue, so a rebuild
 * is withheld (KI-70) and the browser case is reported blocked-on-stale-bundle. A source guard is what
 * can be proven true today.
 *
 * What the guard is actually worth: the unreachable-tab rule (DESIGN.md Section 5's 2026-10-17
 * amendment) is a claim about structure - an `aria-disabled` element must not be an anchor and must
 * carry no click handler, because a `<Link>` navigates regardless of the attribute. That is exactly a
 * property of the source, and it is the property the browser case cannot run to prove today.
 */

it('renders an unreachable section as a span that cannot navigate', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/career/CareerSectionNav.vue'));

    // The disabled branch names the state and nothing else: role, aria-disabled, tabindex and title.
    expect($source)->toContain('role="link"')
        ->and($source)->toContain('aria-disabled="true"')
        ->and($source)->toContain('tabindex="0"')
        ->and($source)->toContain(':title="UNAVAILABLE_TITLE"');

    // It is a span, it carries NO href, and it has NO click handler. The branch is located by the
    // `<span` that owns the `v-else` (the tag opener precedes the directive, so a window starting at
    // `v-else` would miss the tag itself), and a later edit that turns it into a Link reds here.
    $elseAt = strpos($source, 'v-else');
    expect($elseAt)->not->toBeFalse();

    $spanAt = strrpos(substr($source, 0, (int) $elseAt), '<span');
    expect($spanAt)->not->toBeFalse();

    $branch = substr($source, (int) $spanAt, 500);

    expect($branch)->toContain('<span')
        ->and($branch)->toContain('role="link"')
        ->and($branch)->not->toContain(':href')
        ->and($branch)->not->toContain('@click');
});

it('marks exactly one live section with aria-current', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/career/CareerSectionNav.vue'));

    // Nine sections in the career's own order, and the live one is chosen by the `current` key rather
    // than by position.
    foreach (['Cockpit', 'Training', 'Races', 'Skills', 'Events', 'Legacy', 'Deck', 'Timeline', 'Result'] as $label) {
        expect($source)->toContain("label: '{$label}'");
    }

    expect(substr_count($source, "'page' : undefined"))->toBe(1);
});

it('mounts the nav in the layout and drops the per-screen door pair', function (): void {
    $layout = (string) file_get_contents(base_path('resources/js/layouts/CareerLayout.vue'));

    expect($layout)->toContain('CareerSectionNav')
        ->and($layout)->toContain(':current="props.current"')
        // The old career bar with its two doors is gone, and so is the duplicate Swings nav marker.
        ->and($layout)->not->toContain('doorClass')
        ->and($layout)->not->toContain('>Dashboard<');

    // The two pilots mark their own tab and pass the status the reachability rule reads.
    $cockpit = (string) file_get_contents(base_path('resources/js/pages/Career/Cockpit.vue'));
    expect($cockpit)->toContain('current="cockpit"')
        ->and($cockpit)->toContain(':status="props.run.status"');

    $training = (string) file_get_contents(base_path('resources/js/pages/Career/TrainingDetail.vue'));
    expect($training)->toContain('current="training"')
        ->and($training)->toContain(':status="props.run.status"')
        // The inline door that duplicated the Cockpit tab is removed.
        ->and($training)->not->toContain('Career Cockpit');
});

it('leaves the existing Cockpit identity card alone', function (): void {
    // 4.1 named `CareerHeader.vue`, but that path is already the Cockpit's identity and position card
    // and is imported by Cockpit.vue. This pass built the nav under its own name rather than clobbering
    // it, and this case is what keeps the two from being conflated later.
    $header = (string) file_get_contents(base_path('resources/js/components/career/CareerHeader.vue'));

    expect($header)->toContain('career-header-heading')
        ->and($header)->not->toContain('Career sections')
        ->and((string) file_get_contents(base_path('resources/js/pages/Career/Cockpit.vue')))->toContain('CareerHeader');
});
