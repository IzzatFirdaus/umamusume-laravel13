<?php

declare(strict_types=1);

/*
 * A-98's follow-on: a call site passes `BaseSelect` its label as a prop, and the component owns the
 * `<label for>` that names the control. A call site that instead wraps the control in its own
 * `<label>` gives the select no accessible name at all (the wrapper owns the text and the inner
 * control is unnamed), which is the defect the 2026-10-19 pass fixed at ten sites.
 *
 * A source scan rather than a rendered-page scan, for the reason `base-select.spec.ts` gives about
 * itself: the app bundle cannot be rebuilt while a peer holds files dirty (KI-70), so the per-site
 * guard runs against the source and the component-level browser case runs against the fixture. The
 * two together are the contract: the component turns `label` into the name, and each site passes it.
 *
 * Failability: re-adding any `<label>` wrapper listed as removed below reds this file.
 */

it('passes the label in as a prop at every fixed site, with no outer label left behind', function (): void {
    $read = static fn (string $relative): string => (string) file_get_contents(base_path($relative));

    $raceCard = $read('resources/js/components/career/RaceCard.vue');
    $snapshot = $read('resources/js/pages/Career/Snapshot/Setup.vue');
    $runSettings = $read('resources/js/pages/Career/RunSettings.vue');
    $training = $read('resources/js/pages/Career/TrainingDetail.vue');

    // RaceCard: the turn picker carried an implicit wrapper label.
    expect($raceCard)->toContain('label="Turn this race was run on (optional)"')
        ->and($raceCard)->not->toContain('<label v-if="props.entry.turns.length > 0"');

    // Snapshot: Scenario, Trainee and Year lost their wrappers in the 2.5 batch, and this pass takes
    // Month, Half and the Notes textarea. The file now holds NO `<label>` element at all, which is the
    // strongest form of the guard: every control in it owns its name through its component.
    expect($snapshot)->toContain('label="Scenario *"')
        ->and($snapshot)->toContain('label="Trainee *"')
        ->and($snapshot)->toContain('label="Year *"')
        ->and($snapshot)->toContain('label="Month *"')
        ->and($snapshot)->toContain('label="Half *"')
        ->and($snapshot)->toContain('label="Anything worth remembering about this career"')
        ->and(substr_count($snapshot, '<label'))->toBe(0);

    // RunSettings: the run-identity door (`C6b.1`) is where the rarity control lives now. The
    // Cockpit carried it until the `Career tools` disclosure retired; the control moved, the rule did
    // not, so this reads the door that owns it. `trainee_rarity` is guarded for the same reason the
    // Cockpit's was: a `<label for>` beside the component would take the name back off the select.
    expect($runSettings)->toContain('label="Rarity"')
        ->and($runSettings)->not->toContain('<label for="trainee_rarity"');

    // TrainingDetail: the Performance pair. Both controls name themselves through `label`; the
    // wrapping `<label>` each used to carry was the last of its kind in the tree.
    expect($training)->toContain('label="Performance type"')
        ->and($training)->toContain('label="Performance delta"')
        ->and($training)->not->toContain('>Performance type</span>');
});

it('declares maxlength on BaseTextarea so the label branch keeps enforcing it', function (): void {
    // Measured, not assumed: with a `label` the root element is the `<label>`, so an UNDECLARED
    // `maxlength` falls through onto the wrapper and the textarea stops enforcing it. Declaring it and
    // binding it on both branches is what keeps the Notes field's 5000-character limit honest.
    $source = (string) file_get_contents(base_path('resources/js/components/Form/BaseTextarea.vue'));

    expect($source)->toContain('maxlength?: number | string;')
        // One declaration plus one binding per template branch.
        ->and(substr_count($source, ':maxlength="maxlength"'))->toBe(2);
});

/*
 * `Career/LegacySelect.vue` used to be held dirty by a peer, so the 2026-10-19 pass skipped its two
 * "Kind" pickers and left a tripwire pinning the deferred defect — a case that reds the day the file
 * is fixed, forcing this guard to be updated rather than left stale.
 *
 * It fired in `9c6bbcaa`: both Kind pickers now pass `label="Kind"` and the `<label>` wrapper is gone,
 * so the file joins the sites above instead of being excused from them. The tripwire is deleted
 * because its subject no longer exists; asserting `label="Kind"` in its place means a regression at
 * this file reds the same way a regression anywhere else does.
 */
it('names the two Legacy Kind pickers through the component, with no outer label left behind', function (): void {
    $legacySelect = (string) file_get_contents(base_path('resources/js/pages/Career/LegacySelect.vue'));

    expect(substr_count($legacySelect, 'label="Kind"'))->toBe(1)
        ->and($legacySelect)->not->toContain('<label class="flex flex-col gap-1 text-xs text-ink">');
});
