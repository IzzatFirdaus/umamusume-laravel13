<?php

declare(strict_types=1);

use App\Enums\BuildPurpose;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;

/*
 * C1 (FR-F-1, `ADR-0020` §2, `SCREEN-005`).
 *
 * The payload is the read/write boundary for one json column, so what it has to get right is the
 * shape: a key set it can name when it is wrong, and a value vocabulary it refuses to widen. Every
 * case below is a way a stored row could read as something the Trainer did not enter, which is the
 * failure this class exists to make loud.
 *
 * This file lives in `tests/Feature`, not `tests/Unit`, for one reason: `BuildTargetPayload` reads
 * `config('scenarios.stat_order')` so the stat matrix keeps a single owner (`ADR-0015`), and
 * `tests/Unit` binds plain PHPUnit with no application container, so `config()` is not there. The
 * other config-reading domain class, `ScenarioCaps`, is tested in Feature for the same reason.
 */

function buildTarget(array $overrides = []): array
{
    return array_merge([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
        'skill_priorities' => ['Corner Adept', 'Swinging Maestro'],
    ], $overrides);
}

it('round-trips a complete target without changing it', function (): void {
    $payload = BuildTargetPayload::fromArray(buildTarget());

    expect($payload->toArray())->toBe(buildTarget())
        ->and($payload->purpose)->toBe(BuildPurpose::StoryClear)
        ->and($payload->targets['Speed'])->toBe(900);
});

it('refuses a key it does not know, and names it', function (): void {
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['targts' => []])))
        ->toThrow(InvalidArgumentException::class, 'targts');
});

it('refuses a payload missing a key, and names it', function (): void {
    $raw = buildTarget();
    unset($raw['surface']);

    expect(fn () => BuildTargetPayload::fromArray($raw))
        ->toThrow(InvalidArgumentException::class, 'surface');
});

it('refuses a purpose that is not one of the four', function (): void {
    // The cases are this tool's contract values, not client strings, so a near-miss such as the
    // label the brief uses for one of them must not be accepted as one of them.
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['purpose' => 'General Training'])))
        ->toThrow(InvalidArgumentException::class, 'General Training');
});

it('refuses a distance band outside the four the catalogue publishes', function (): void {
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['distance' => 'Marathon'])))
        ->toThrow(InvalidArgumentException::class, 'Marathon');
});

it('refuses a surface outside Turf and Dirt', function (): void {
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['surface' => 'Ice'])))
        ->toThrow(InvalidArgumentException::class, 'Ice');
});

it('refuses a running style outside the four client words', function (): void {
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['style' => 'Front'])))
        ->toThrow(InvalidArgumentException::class, 'Front');
});

it('refuses a target set whose keys are not the five stats', function (): void {
    // Four of five, or five of six, are both wrong: a target list that does not line up with the
    // stat matrix would leave the advisor ranking against a stat the run does not carry.
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['targets' => ['Speed' => 900]])))
        ->toThrow(InvalidArgumentException::class, 'Stamina');
});

it('refuses a skill priority list that is not a list of names', function (): void {
    expect(fn () => BuildTargetPayload::fromArray(buildTarget(['skill_priorities' => [1, 2]])))
        ->toThrow(InvalidArgumentException::class, 'skill_priorities');
});

it('reads an absent target as absent rather than as an empty one', function (): void {
    // The distinction the screen depends on: no target at all is a disclosure the Trainer can act
    // on, while a target with no fields in it would be a claim that they entered nothing.
    $run = new TrainingRun;
    $run->build_target = null;

    expect($run->buildTarget())->toBeNull();

    $run->build_target = buildTarget();

    expect($run->buildTarget())->toBeInstanceOf(BuildTargetPayload::class);
});

it('carries a stored target through the model and back out unchanged', function (): void {
    // The round trip through the column's cast, which is where a json payload can silently lose
    // int-ness and come back as strings the advisor would then compare against ints.
    $run = TrainingRun::factory()->create(['build_target' => buildTarget()]);

    expect($run->fresh()->buildTarget()?->toArray())->toBe(buildTarget());
});
