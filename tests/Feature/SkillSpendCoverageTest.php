<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\HintDiscount;
use App\Services\SkillSpendCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The Skills Planner's Skill Point coverage, computed from the stored costs (plan §8 D13, A5).
 *
 * The audit found the headline stated as "The skills still to learn cost N/A SP", a panel that
 * could never resolve. `SkillSpendCoverage` now sums the required skills' stored prices against the
 * run's recorded Skill Points, and the screen prints that figure, the remainder, or the names of the
 * skills it cannot price. The hint-discount ladder is a client fact (UMAMUSUME_REFERENCE.md §1.1.4),
 * so this file also proves the ladder resolves and that a row with no recorded hint level stays at its
 * base cost.
 *
 * Class-based with private helpers, matching `CareerSkillsPlannerTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class SkillSpendCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_computes_the_required_total_and_the_remainder_from_the_stored_costs(): void
    {
        $run = $this->plannerRun();
        $this->skill('Priced High', 200);
        $this->skill('Priced Higher', 160);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Priced High', 'Priced Higher'],
        ])]);
        TurnEntry::factory()->for($run)->create(['turn' => 12, 'sp' => 500]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('coverage.total', 360)
                ->where('coverage.sp', 500)
                ->where('coverage.remaining', 140)
                ->where('coverage.warn', false)
                ->where('coverage.absent', null));
    }

    public function test_names_the_skills_it_cannot_price_instead_of_stating_a_total(): void
    {
        $run = $this->plannerRun();
        $this->skill('Unpriced Skill', null);
        $this->skill('Priced High', 200);

        $run->update(['build_target' => $this->target([
            'skill_priorities' => ['Unpriced Skill', 'Priced High'],
        ])]);
        TurnEntry::factory()->for($run)->create(['turn' => 12, 'sp' => 500]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // A missing price is not a zero: the total and the remainder are refused, and the
                // skills the catalogue cannot price are named rather than the sum shortened.
                ->where('coverage.total', null)
                ->where('coverage.remaining', null)
                ->where('coverage.absent', fn (string $absent): bool => str_contains($absent, 'Cost not recorded for:')
                    && str_contains($absent, 'Unpriced Skill')));
    }

    public function test_leaves_the_remainder_unstated_while_no_turn_is_logged(): void
    {
        $run = $this->plannerRun();
        $this->skill('Priced High', 200);

        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priced High']])]);

        $this->get(route('runs.skills.planner', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // The total resolves from the catalogue alone; the remainder needs a recorded SP
                // total, which no logged turn means the run has never stated.
                ->where('coverage.total', 200)
                ->where('coverage.sp', null)
                ->where('coverage.remaining', null)
                ->where('coverage.absent', null));
    }

    public function test_resolves_the_hint_discount_ladder_from_the_reference(): void
    {
        $run = $this->plannerRun();
        $this->skill('Priced Skill', 200);

        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priced Skill']])]);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        // UMAMUSUME_REFERENCE.md §1.1.4: Lv1 10%, Lv2 20%, Lv3 30%, Lv4 35%, Lv Max 40%.
        expect(config('uma.skills.hint_discount'))->toBe([1 => 10, 2 => 20, 3 => 30, 4 => 35, 'max' => 40]);

        /** @var array<string, mixed> $props */
        $props = $response->viewData('page')['props'];
        $encoded = json_encode($props, JSON_THROW_ON_ERROR);

        expect($props)->toHaveKey('hint_discount')
            ->and($encoded)->toContain('Hint Lvl 1')
            ->and($encoded)->toContain('Hint Lvl Max')
            ->and($props['hint_discount'])->toHaveCount(5)
            // A row with no recorded hint level stays at its base cost: the ladder exists, but no
            // column holds a hint level, so nothing re-prices the row (G-SK-3).
            ->and($props['groups'][0]['rows'][0]['sp_cost'])->toBe(200);
    }

    public function test_prices_a_skill_at_each_hint_level_with_the_floored_discount(): void
    {
        // UMAMUSUME_REFERENCE.md §1.1.4: displayed cost is floor(base × (1 − discount)). The one
        // observed rounding case is Pace Chaser Corners ○ at 84 against a base of 130 (130 × 0.65).
        expect(HintDiscount::price(130, 1))->toBe(117)
            ->and(HintDiscount::price(130, 2))->toBe(104)
            ->and(HintDiscount::price(130, 3))->toBe(91)
            ->and(HintDiscount::price(130, 4))->toBe(84)
            ->and(HintDiscount::price(130, 'max'))->toBe(78)
            // A base the reference samples: 160 at Lv Max is 96 (160 × 0.6).
            ->and(HintDiscount::price(160, 'max'))->toBe(96)
            // Integer arithmetic: the float form evaluates 180 × 0.7 as 125.999… and floors to 125,
            // where the client's rule prints 126.
            ->and(HintDiscount::price(180, 3))->toBe(126)
            ->and(HintDiscount::price(200, 3))->toBe(140)
            // An unknown hint level leaves the base cost untouched rather than inventing a discount.
            ->and(HintDiscount::price(130, 9))->toBe(130);
    }

    public function test_prices_the_full_ladder_from_one_base_including_the_none_row(): void
    {
        // The detail page's six rows: the base itself, then each client tier priced through price().
        expect(HintDiscount::priceLadder(130))->toBe([
            ['key' => 'none', 'label' => 'None', 'discount' => 0, 'price' => 130],
            ['key' => 'lv1', 'label' => 'Lv1', 'discount' => 10, 'price' => 117],
            ['key' => 'lv2', 'label' => 'Lv2', 'discount' => 20, 'price' => 104],
            ['key' => 'lv3', 'label' => 'Lv3', 'discount' => 30, 'price' => 91],
            ['key' => 'lv4', 'label' => 'Lv4', 'discount' => 35, 'price' => 84],
            ['key' => 'max', 'label' => 'Lv Max', 'discount' => 40, 'price' => 78],
        ])
            // A class that states no cost has no ladder: every price is null, never zero.
            ->and(HintDiscount::priceLadder(null))->toHaveCount(6)
            ->and(array_column(HintDiscount::priceLadder(null), 'price'))->each->toBeNull();
    }

    public function test_the_service_refuses_the_sum_when_any_price_is_absent(): void
    {
        expect(SkillSpendCoverage::sum(['A' => 200, 'B' => 160], 500))->toBe(['total_cost' => 360, 'remaining' => 140])
            ->and(SkillSpendCoverage::sum(['A' => 200, 'B' => null], 500))->toBeNull()
            ->and(SkillSpendCoverage::sum(['A' => 200], null))->toBe(['total_cost' => 200, 'remaining' => null])
            ->and(SkillSpendCoverage::sum([], 500))->toBe(['total_cost' => 0, 'remaining' => 500]);
    }

    private function plannerRun(): TrainingRun
    {
        return TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    }

    private function skill(string $name, ?int $sp): Skill
    {
        return Skill::factory()->create([
            'name' => $name,
            'sp_cost' => $sp,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);
    }

    /**
     * A complete build target, the shape `StoreBuildTargetRequest::payload()` writes.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function target(array $overrides = []): array
    {
        return [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
            'skill_priorities' => [],
            ...$overrides,
        ];
    }
}
