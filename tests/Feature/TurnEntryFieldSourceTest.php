<?php

declare(strict_types=1);

/*
 * One field list for the turn-entry surfaces (D9's structural half).
 *
 * The five stat inputs were declared separately on the run record, the training decision and the cockpit
 * correction, and drifted apart (`Speed *`, `Speed total *`, `Speed`). `turnEntryFields.ts` became the
 * one owner. Two of the three surfaces read it now: the run record was retired with owner ruling R-2
 * (`docs/proposals/frontend-development-plan.md` §9.6 close-out, 2026-10-09), leaving the training
 * decision and the cockpit correction.
 *
 * Asserted against the source because there is no component-test harness and no rendered HTML to read:
 * Inertia answers a page request with JSON props, so the inputs only exist after the browser hydrates.
 * `GuidedStepScenarioVariationTest` is the precedent for reading a Vue file from a PHP test.
 */

/**
 * @return array<int, array{name: string, label: string}>
 */
function sharedStatFields(): array
{
    $source = (string) file_get_contents(base_path('resources/js/domain/turnEntryFields.ts'));

    preg_match_all('/\{\s*name:\s*\'(\w+)\',\s*label:\s*\'(\w+)\'\s*\}/', $source, $matches, PREG_SET_ORDER);

    return array_map(
        static fn (array $pair): array => ['name' => $pair[1], 'label' => $pair[2]],
        $matches,
    );
}

it('declares exactly the five stat fields in the shared source, each label its own capitalisation', function (): void {
    expect(sharedStatFields())->toBe([
        ['name' => 'speed', 'label' => 'Speed'],
        ['name' => 'stamina', 'label' => 'Stamina'],
        ['name' => 'power', 'label' => 'Power'],
        ['name' => 'guts', 'label' => 'Guts'],
        ['name' => 'wit', 'label' => 'Wit'],
    ]);
});

it('has the training decision read the shared list instead of declaring its own', function (): void {
    $page = (string) file_get_contents(base_path('resources/js/pages/Career/TrainingDetail.vue'));

    // The import also names the energy constants since `1ec92ab feat(turn): energy state as
    // exact/band/unknown`, so this asserts the two things that matter rather than the whole
    // statement: the shared stat list is imported under `statFields`, from the domain file,
    // and no local list shadows it.
    expect($page)
        ->toContain('TURN_ENTRY_STAT_FIELDS as statFields')
        ->toContain("from '../../domain/turnEntryFields'")
        ->not->toContain('const statFields = [');
});

it('labels a stat field with the bare word and keeps the required marker separate', function (): void {
    $page = (string) file_get_contents(base_path('resources/js/pages/Career/TrainingDetail.vue'));

    expect($page)
        ->not->toContain('{{ field.label }} total')
        ->toContain('<span class="text-ink-muted">{{ field.label }} *</span>')
        // One spelling for the optional field, with `sp` kept as the field name.
        ->toContain("{ name: 'sp', label: 'Skill Points', max: null }")
        // Energy is a three-state control since `1ec92ab` (exact / band / unknown), reading the
        // shared constants rather than an inline optional-field row.
        ->toContain('TURN_ENTRY_ENERGY_STATES')
        ->toContain('TURN_ENTRY_ENERGY_BANDS');
});

it('has the cockpit read the shared list instead of declaring its own', function (): void {
    $cockpit = (string) file_get_contents(base_path('resources/js/pages/Career/Cockpit.vue'));

    expect($cockpit)
        ->toContain("import { TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';")
        ->not->toContain('const statFields = [');
});

/*
 * The run record's half of the previous case here retired with owner ruling R-2
 * (`docs/proposals/frontend-development-plan.md` §9.6 close-out, 2026-10-09): `Runs/Show.vue`
 * was deleted, and the Cockpit's correction form is the 2.0 owner of the turn-entry write. Its
 * posting of the same `StoreTurnEntryRequest` - the half of this file's concern that is about the
 * request's contract rather than the shared field list - is asserted in `CareerCockpitTest`, in the
 * two cases that name `runs.turns.update` and the one that refuses a dropped stat.
 */
