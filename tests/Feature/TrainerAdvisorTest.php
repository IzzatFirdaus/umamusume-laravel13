<?php

declare(strict_types=1);

use App\Models\Advisor\BuildTargetPayload;
use App\Models\TurnEntry;
use App\Services\Advisor\TrainerAdvisor;

/*
 * C2 — the Trainer Advisor (FR-F, `ADR-0020` §2, `ADR-0001` §3–4, PROCESS-PLANS trainer-advisor §4–5).
 *
 * This file pins the ranking rules in the order the spec states them, because the order is the
 * contract: the first rule that fires produces the recommendation and the rest do not run. It also
 * pins the two things the slice is authorised on and could most easily drift from — that no figure
 * is invented (only entered turns and the declared constants are read), and that every option that
 * ships carries a reason a Trainer can check.
 *
 * The engine reads `config('advisor')`, so this file lives in Feature where the application
 * container exists.
 */

function advisorTurn(?int $energy, array $stats = []): TurnEntry
{
    return new TurnEntry(array_merge([
        'turn' => 1,
        'speed' => 500, 'stamina' => 500, 'power' => 500, 'guts' => 500, 'wit' => 500,
        'energy' => $energy,
        'mood' => 'NORMAL',
    ], $stats));
}

function advisorTarget(array $targets = []): BuildTargetPayload
{
    return BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => array_merge(['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500], $targets),
        'skill_priorities' => [],
    ]);
}

it('returns no band and no ranking when the run has recorded no Energy', function (): void {
    // Rule 1. A historical run has turns but no Energy, and a band off a null would be a number
    // invented from nothing. The absence is named rather than left as an empty panel.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(null), advisorTarget());

    expect($advice->band)->toBeNull()
        ->and($advice->recommendation)->toBeNull()
        ->and($advice->alternative)->toBeNull()
        ->and($advice->absence)->toContain('Energy');
});

it('recommends Rest below the advisory line and names the line and the recovery', function (): void {
    // Rule 2. 50 is the only sourced threshold (ADR-0001 §3), and +30 is the sourced recovery.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(42), advisorTarget());

    expect($advice->band)->toBe('BelowAdvisory')
        ->and($advice->recommendation?->action)->toBe('Rest')
        ->and($advice->recommendation?->reason)->toContain('42')
        ->and($advice->recommendation?->reason)->toContain('50')
        ->and($advice->recommendation?->reason)->toContain('30');
});

it('offers Wit as the alternative below the line when Wit is short of its target', function (): void {
    // Wit costs no Energy, so it is the one action that still makes progress on a low bar. It is
    // offered only when there is a deficit to close, because otherwise it is progress toward nothing.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(20), advisorTarget(['Wit' => 600]));

    expect($advice->recommendation?->action)->toBe('Rest')
        ->and($advice->alternative?->action)->toBe('Wit')
        ->and($advice->alternative?->reason)->toContain('0 Energy');
});

it('offers no alternative below the line when Wit has nothing left to close', function (): void {
    $advice = (new TrainerAdvisor)->advise(advisorTurn(20), advisorTarget(['Wit' => 500]));

    expect($advice->recommendation?->action)->toBe('Rest')
        ->and($advice->alternative)->toBeNull();
});

it('gives Energy guidance only, and no stat ranking, when no target was entered', function (): void {
    // Rule 3. This is ADR-0001's original scope: there is no target to rank against, so ranking
    // would be the tool inventing one. The absence is named instead.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(70), null);

    expect($advice->band)->toBe('AtOrAboveAdvisory')
        ->and($advice->recommendation)->toBeNull()
        ->and($advice->absence)->toContain('target');
});

it('recommends the training with the largest deficit when a target is set', function (): void {
    // Rule 4. Deficits: Speed 900-500=400, Stamina 800-500=300, Power 700-500=200, Guts 600-500=100,
    // Wit 500-500=0. Speed is largest.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(70), advisorTarget());

    expect($advice->band)->toBe('AtOrAboveAdvisory')
        ->and($advice->recommendation?->action)->toBe('Speed')
        ->and($advice->recommendation?->reason)->toContain('400');
});

it('breaks a tie by the stat matrix order rather than by the order options were built', function (): void {
    // All five stats equally short. config('scenarios.stat_order') starts at Speed, so the winner is
    // Speed and the choice is a property of the matrix rather than of this loop's iteration order.
    $advice = (new TrainerAdvisor)->advise(
        advisorTurn(70, ['speed' => 400, 'stamina' => 400, 'power' => 400, 'guts' => 400, 'wit' => 400]),
        advisorTarget(['Speed' => 500, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 500]),
    );

    expect($advice->recommendation?->action)->toBe('Speed');
});

it('reports Wit as RiskNotMeasured rather than giving it a band', function (): void {
    // ADR-0001 §3 correction: Wit always enters at full Energy, so a band would assert an exemption
    // the sources leave unsettled. Two band states exist and Wit is not in either.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(70), advisorTarget());
    $wit = collect($advice->options)->firstWhere('action', 'Wit');

    expect($wit?->band)->toBe('RiskNotMeasured');
});

it('gives every option that ships a reason a Trainer could check', function (): void {
    // ADR-0001 §4: a suggestion with no derivable reason does not ship. Asserted over the whole list
    // so a new option cannot be added without one.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(70), advisorTarget());

    expect($advice->options)->not->toBeEmpty();

    foreach ($advice->options as $option) {
        expect(trim($option->reason))->not->toBe('');
    }
});

it('never ranks above the two states the sources support', function (): void {
    // A third band (the "highly dangerous below 30" line) is in no source and is not rendered as
    // game fact (ADR-0001 §3). Asserted as a closed set so a later edit cannot quietly widen it.
    $bands = collect((new TrainerAdvisor)->advise(advisorTurn(70), advisorTarget())->options)
        ->map(fn ($option): ?string => $option->band)
        ->unique()
        ->values()
        ->all();

    expect($bands)->each->toBeIn(['AtOrAboveAdvisory', 'BelowAdvisory', 'RiskNotMeasured']);
});

it('reads Energy-after from the declared constants rather than a number of its own', function (): void {
    // Rest recovers +30; a training session costs the declared range. Both are asserted against
    // config so the engine cannot drift from the constant set it is required to read.
    $advice = (new TrainerAdvisor)->advise(advisorTurn(70), advisorTarget());
    $options = collect($advice->options)->keyBy('action');

    expect($options['Rest']->energyAfter)->toBe(['min' => 100, 'max' => 100])
        ->and($options['Speed']->energyAfter)->toBe([
            'min' => 70 - (int) config('advisor.constants.session_cost.value.1'),
            'max' => 70 - (int) config('advisor.constants.session_cost.value.0'),
        ]);
});

it('carries the source and date of every constant it read', function (): void {
    // ADR-0001 §7: a number is shown with the data behind it. The engine exposes the provenance of
    // the constants it used so the surface can print the date without a second lookup.
    $sources = (new TrainerAdvisor)->constants();

    foreach (['rest_recovery', 'session_cost', 'wit_cost', 'advisory_threshold'] as $key) {
        expect($sources[$key]['source'] ?? null)->toBeString()->not->toBe('')
            ->and($sources[$key]['verified_at'] ?? null)->toBeString()->not->toBe('');
    }
});
