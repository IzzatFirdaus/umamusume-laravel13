<?php

declare(strict_types=1);

/*
 * E-02 / R8 — the snapshot door is linked from both career-entry surfaces.
 *
 * The rendered assertion lives in `tests/browser/runs.spec.ts`, which is where a client-rendered link
 * can actually be seen and clicked; the PHP layer renders an empty `#app` shell, so an HTML assertion
 * here would prove nothing. This file pins the source-level fact the browser spec depends on, so a
 * layout edit that drops one of the two doors fails at the narrowest layer with a readable message.
 */

it('links the snapshot door from both career-entry surfaces', function (string $page): void {
    $source = (string) file_get_contents(resource_path('js/pages/'.$page));

    expect($source)->toContain('href="/career/snapshot"');
})->with([
    'careers list' => 'Runs/Index.vue',
    'dashboard' => 'Dashboard.vue',
]);

it('links it from a Dashboard card that renders whatever the resume state is', function (): void {
    // The empty-resume branch linked the door, so a Trainer who already had a career running — the
    // common case — read about a door the page did not offer. The Quick actions card always renders,
    // and that is where the unconditional link lives (E-02 / R8).
    $source = (string) file_get_contents(resource_path('js/pages/Dashboard.vue'));

    $quickActions = strpos($source, 'data-card="quick_actions"');
    $link = strpos($source, 'href="/career/snapshot"', (int) $quickActions);

    expect($quickActions)->toBeGreaterThan(0)
        ->and($link)->toBeGreaterThan((int) $quickActions);
});
