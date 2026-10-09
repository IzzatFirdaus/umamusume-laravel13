<?php

declare(strict_types=1);

/*
 * One field list for the turn-entry surfaces (D9's structural half).
 *
 * The five stat inputs were declared separately on the run record, the training decision and the cockpit
 * correction, and drifted apart (`Speed *`, `Speed total *`, `Speed`). `turnEntryFields.ts` is now the
 * one owner and all three surfaces read it.
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

    expect($page)
        ->toContain("import { TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';")
        ->not->toContain('const statFields = [');
});

it('labels a stat field with the bare word and keeps the required marker separate', function (): void {
    $page = (string) file_get_contents(base_path('resources/js/pages/Career/TrainingDetail.vue'));

    expect($page)
        ->not->toContain('{{ field.label }} total')
        ->toContain('<span class="text-ink-muted">{{ field.label }} *</span>')
        // One spelling for the optional fields, with `sp` kept as the field name.
        ->toContain("{ name: 'sp', label: 'Skill Points', max: null }")
        ->toContain("{ name: 'energy', label: 'Energy', max: 100 }");
});

it('has the run record and the cockpit read the shared list instead of declaring their own', function (): void {
    $runRecord = (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));
    $cockpit = (string) file_get_contents(base_path('resources/js/pages/Career/Cockpit.vue'));

    expect($runRecord)
        ->toContain("import { TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';")
        ->not->toContain('const statWords = [')
        ->and($cockpit)
        ->toContain("import { TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';")
        ->not->toContain('const statFields = [');
});
