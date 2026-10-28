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

it('renders an unreachable section as non-focusable text, not a link wearing aria-disabled', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/career/CareerSectionNav.vue'));

    /*
     * DESIGN.md §5 (a)'s 2026-10-17 amendment names the shape this case used to pin as the defect:
     * "`aria-disabled` alone is not that shape and never may be used as one, because it announces a
     * state without cancelling activation". The unreachable tab is now plain text, and its reason is
     * visible prose under the tab row rather than a `title` — which the same amendment supersedes as
     * the hover-only channel.
     *
     * Corrected 2026-10-22 (UX audit F-04). The assertions are the inverse of the originals on
     * purpose: the old three attributes are exactly what must not come back.
     */
    // The assertions read the TEMPLATE, not the whole file: the component's dated correction has to
    // name the attributes it retired in order to explain them, so a whole-file check would fail on its
    // own documentation.
    $templateAt = strpos($source, '<template>');
    expect($templateAt)->not->toBeFalse();

    // Comments are stripped as well as the script block: the retired attributes are named in the
    // correction prose precisely so a reader sees what not to reintroduce, and prose is not markup.
    $template = preg_replace('/<!--.*?-->/s', '', substr($source, (int) $templateAt)) ?? '';

    expect($template)->not->toContain('role="link"')
        ->and($template)->not->toContain('aria-disabled')
        ->and($template)->not->toContain('tabindex="0"')
        ->and($template)->not->toContain(':title=')
        // The helpers survive, because the reason they word is still printed as visible prose.
        ->and($template)->toContain('unavailableReason(step.key)')
        ->and($template)->toContain('unreachableSteps');

    // The unreachable branch is still a span, and still carries NO href and NO click handler, so a
    // later edit that turns it into a Link reds here.
    $elseAt = strpos($source, 'v-else');
    expect($elseAt)->not->toBeFalse();

    $spanAt = strrpos(substr($source, 0, (int) $elseAt), '<span');
    expect($spanAt)->not->toBeFalse();

    $branch = substr($source, (int) $spanAt, 300);

    expect($branch)->toContain('<span')
        ->and($branch)->not->toContain(':href')
        ->and($branch)->not->toContain('@click')
        ->and($branch)->not->toContain('role=')
        ->and($branch)->not->toContain('tabindex');
});

it('marks exactly one live section with aria-current', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/career/CareerSectionNav.vue'));

    // Nine sections in the career's own order, and the live one is chosen by the `current` key rather
    // than by position. `Inheritance` is the HFW's own label for this step
    // (`cockpit-unity-cup.html:77`); the run-scoped Legacy Lab is a different screen.
    foreach (['Cockpit', 'Training', 'Races', 'Skills', 'Events', 'Inheritance', 'Deck', 'Timeline', 'Result'] as $label) {
        expect($source)->toContain("label: '{$label}'");
    }

    // `aria-current="step"`, the value the HFW and the wizard stepper both use, not `page`.
    expect(substr_count($source, "'step' : undefined"))->toBe(2);
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
    $header = (string) file_get_contents(base_path('resources/js/components/career/CockpitIdentityCard.vue'));

    expect($header)->toContain('career-header-heading')
        ->and($header)->not->toContain('Career sections')
        ->and((string) file_get_contents(base_path('resources/js/pages/Career/Cockpit.vue')))->toContain('CockpitIdentityCard');
});
