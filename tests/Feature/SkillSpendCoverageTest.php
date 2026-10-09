<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReleaseStatus;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
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
 * skills it cannot price. The hint-discount ladder is gone on the owner's ruling, so this file also
 * proves no ladder or percentage survives on the resolved props.
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

    public function test_resolves_no_hint_ladder_and_no_percentage_anywhere_on_the_screen(): void
    {
        $run = $this->plannerRun();
        $this->skill('Priced Skill', 200);

        $run->update(['build_target' => $this->target(['skill_priorities' => ['Priced Skill']])]);

        $response = $this->get(route('runs.skills.planner', $run));
        $response->assertOk();

        // The ladder's source is gone from config, so nothing can re-price a row against it.
        expect(config('uma.skills.hint_discount'))->toBeNull();

        /** @var array<string, mixed> $props */
        $props = $response->viewData('page')['props'];
        $encoded = json_encode($props, JSON_THROW_ON_ERROR);

        expect($props)->not->toHaveKey('ladder')
            ->and($encoded)->not->toContain('Hint Lvl')
            ->and($encoded)->not->toContain('% off')
            // The per-row ladder block was the second render site; its `costs` array is gone too.
            ->and($props['groups'][0]['rows'][0])->not->toHaveKey('costs')
            ->and($props['groups'][0]['rows'][0]['sp_cost'])->toBe(200);
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
