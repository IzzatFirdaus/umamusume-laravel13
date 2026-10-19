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
    $cockpit = $read('resources/js/pages/Career/Cockpit.vue');
    $training = $read('resources/js/pages/Career/TrainingDetail.vue');

    // RaceCard: the turn picker carried an implicit wrapper label.
    expect($raceCard)->toContain('label="Turn this race was run on (optional)"')
        ->and($raceCard)->not->toContain('<label v-if="props.entry.turns.length > 0"');

    // Snapshot: Scenario and Trainee each carried a wrapper label. The marker is the label text
    // rather than the wrapper class, because the Notes field (a `BaseTextarea`, a different
    // component with its own contract) still carries that class and is not part of this pass.
    expect($snapshot)->toContain('label="Scenario *"')
        ->and($snapshot)->toContain('label="Trainee *"')
        ->and($snapshot)->toContain('label="Year *"')
        ->and($snapshot)->not->toContain('>Scenario *</span>')
        ->and($snapshot)->not->toContain('>Trainee *</span>')
        // Year's wrapper is gone while its siblings (Month, Half) still carry theirs.
        ->and($snapshot)->not->toContain('>Year *</span>');

    // Cockpit: Rarity and the correction-turn picker each carried an explicit `for`.
    expect($cockpit)->toContain('label="Rarity"')
        ->and($cockpit)->toContain('label="Correct turn"')
        ->and($cockpit)->not->toContain('<label for="trainee_rarity"')
        ->and($cockpit)->not->toContain('<label class="flex items-center gap-2 text-sm" for="correction-turn-select"');

    // TrainingDetail: the Performance type picker.
    expect($training)->toContain('label="Performance type"')
        ->and($training)->not->toContain('>Performance type</span>');
});

it('records the peer-held sites as still carrying the wrapper, so a later slice flips this', function (): void {
    // `Career/LegacySelect.vue` is held dirty by a peer, so the pass skipped its sites (the two
    // "Kind" pickers the site list named). This case is a tripwire, not an endorsement: it pins the
    // deferred defect so the sweep's incompleteness is visible, and it reds the day the file is
    // fixed, which forces that slice to update this guard rather than leave a stale claim behind.
    $legacySelect = (string) file_get_contents(base_path('resources/js/pages/Career/LegacySelect.vue'));

    expect(substr_count($legacySelect, '<label class="flex flex-col gap-1 text-xs text-ink">'))->toBeGreaterThan(0)
        ->and($legacySelect)->not->toContain('label="Kind"');
});
