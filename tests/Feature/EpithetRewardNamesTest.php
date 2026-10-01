<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Models\Skill;

/*
 * An epithet reward that names a skill is a promise the catalogue has to be able to keep.
 *
 * `config/scenarios.php` carries the Trackblazer route rewards, and `epithet-checklist.blade.php` prints
 * the `reward` value verbatim beside the route name. One of those strings, `Mile Straightaways hint +1`,
 * names a skill the catalogue does not hold: `[Global]` has `Mile Straightaways ◎` (export 201031) and
 * `Mile Straightaways ○` (201032), and a bare stem matches neither. The scenario source that supplied
 * the reward, `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Epithets (Race Route Bonuses)", names no tier, so the fix cannot pick one
 * without inventing a provenance, which is the move `ADR-0011` §5 and the `Traightaways` withdrawal at
 * `CONSTRAINTS.md` D-210 both refuse. The reward therefore names the family and says the tier is not
 * published.
 *
 * This test is the guard that would have caught it. It is deliberately narrow: it only classifies
 * rewards that name a skill, and it never asserts anything about rewards that state a stat bonus.
 */

/**
 * The reward of every epithet route, each tagged with the scenario it belongs to.
 *
 * @return list<array{scenario: string, reward: string}>
 */
function epithetRewards(): array
{
    $out = [];

    foreach ((array) config('scenarios.scenarios') as $key => $scenario) {
        foreach ($scenario['epithet_routes'] ?? [] as $route) {
            if (isset($route['reward'])) {
                $out[] = ['scenario' => (string) $key, 'reward' => (string) $route['reward']];
            }
        }
    }

    return $out;
}

/**
 * What a reward string claims about the catalogue, or null when it claims nothing checkable.
 *
 * A reward that names no skill is not this rule's business. One that names a skill must resolve to
 * exactly one `[Global]` client row. A stem that matches a family of tiers resolves to none by itself,
 * so it is only honest when the string says the tier is not stated.
 *
 * @return string|null the problem, or null when the reward is sound
 */
function rewardSkillClaim(string $reward): ?string
{
    // Anchored and permissive: the tier glyphs the client uses in skill names, `◎` and `○`, are not
    // in any ASCII class, and a stem that stops at one of them reads as a name of its own.
    if (preg_match('/^(?<name>.*?)\s+hint\b/iu', $reward, $m) !== 1) {
        return null;
    }

    $stem = trim($m['name']);

    $exact = Skill::query()->availableOnGlobal()->where('name', $stem)->count();

    if ($exact === 1) {
        return null;
    }

    $family = Skill::query()->availableOnGlobal()->where('name', 'like', $stem.' %')->count();

    if ($family > 0 && str_contains($reward, 'tier not stated')) {
        return null;
    }

    if ($family > 0) {
        return "\"{$reward}\" names the family \"{$stem}\", which is {$family} client rows, and does not say which tier it means.";
    }

    return "\"{$reward}\" names \"{$stem}\", which no [Global] client row carries.";
}

function seedMileStraightawaysFamily(): void
{
    // The two rows are the client's own strings and the export's own ids, read from the shipped
    // document. `Homestretch Haste` is the other reward that names a skill, and `SkillSeeder` already
    // holds it as export 200512, so the fixture carries both families a reward can name.
    foreach ([
        ['export_id' => 201031, 'name' => 'Mile Straightaways ◎', 'match_key' => 'milestraightaways'],
        ['export_id' => 201032, 'name' => 'Mile Straightaways ○', 'match_key' => 'milestraightaways'],
        ['export_id' => 200512, 'name' => 'Homestretch Haste', 'match_key' => 'homestretchhaste'],
    ] as $row) {
        Skill::query()->create($row + [
            'release_status' => ReleaseStatus::GlobalReleased->value,
            'name_is_client' => true,
            'rarity' => 1,
            'sp_cost' => 110,
        ]);
    }
}

it('never promises an epithet reward that names a skill the catalogue cannot resolve', function (): void {
    seedMileStraightawaysFamily();

    $problems = [];

    foreach (epithetRewards() as $entry) {
        $problem = rewardSkillClaim($entry['reward']);

        if ($problem !== null) {
            $problems[] = $entry['scenario'].': '.$problem;
        }
    }

    expect($problems)->toBe([], "an epithet reward names a skill the tool cannot point at:\n".implode("\n", $problems));
});

it('catches the bare stem that shipped, which is the point of the check', function (): void {
    // A guard that passes every string proves nothing. This is the exact value
    // `config/scenarios.php:270` held before the fix, run through the same classifier.
    seedMileStraightawaysFamily();

    expect(rewardSkillClaim('Mile Straightaways hint +1'))
        ->not->toBeNull('the classifier let a tierless family name through, so it cannot catch this bug');

    // And the same stem with the tier published is a clean claim, so the rule is not just
    // rewarding vagueness: naming one real row satisfies it.
    expect(rewardSkillClaim('Mile Straightaways ◎ hint +1'))->toBeNull()
        ->and(rewardSkillClaim('+15 to 2 random stats'))->toBeNull();
});

it('states the family and the unpublished tier, rather than guessing a variant', function (): void {
    $rewards = array_column(epithetRewards(), 'reward');

    // Exact membership, not a substring: the fixed string differs from the broken one by the
    // parenthetical alone, so any looser match would pass on both.
    expect($rewards)->toContain('Mile Straightaways hint +1 (tier not stated by the source)')
        ->and($rewards)->not->toContain('Mile Straightaways hint +1');
});
