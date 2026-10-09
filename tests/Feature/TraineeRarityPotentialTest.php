<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The trainee's rarity and potential level, stored on the run (A6.1 persistence).
 *
 * Both are entered, never derived: a run may name only a trainee and no card, and the potential
 * level belongs to the trainee rather than the card, so neither is computable from the card layer.
 * Null is the honest "not stated" and the range is enforced at the boundary, not by a schema check
 * that would make a bad value a 500 instead of a validation error.
 *
 * Class-based with private helpers, matching `CareerSkillsPlannerTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class TraineeRarityPotentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_the_rarity_and_potential_the_form_submits(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'trainee_rarity' => 3,
            'potential_level' => 2,
        ])->assertSessionHasNoErrors();

        $run = $this->latestRun();

        expect($run->trainee_rarity)->toBe(3)
            ->and($run->potential_level)->toBe(2);
    }

    public function test_leaves_both_unstated_when_the_form_carries_neither(): void
    {
        $trainee = Umamusume::factory()->create();

        $this->post(route('runs.store'), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
        ])->assertSessionHasNoErrors();

        $run = $this->latestRun();

        // Absent is not zero: a run that never stated them stores null, which the cockpit render
        // shows as the N/A disclosure rather than a default.
        expect($run->trainee_rarity)->toBeNull()
            ->and($run->potential_level)->toBeNull();
    }

    public function test_refuses_a_rarity_outside_the_one_to_three_the_source_uses(): void
    {
        $trainee = Umamusume::factory()->create();

        foreach ([0, 4] as $rarity) {
            $this->post(route('runs.store'), [
                'umamusume_id' => $trainee->id,
                'status' => RunStatus::Active->value,
                'trainee_rarity' => $rarity,
            ])->assertSessionHasErrors('trainee_rarity');
        }

        expect(TrainingRun::query()->count())->toBe(0);
    }

    public function test_refuses_a_potential_level_outside_the_one_to_five_the_source_uses(): void
    {
        $trainee = Umamusume::factory()->create();

        foreach ([0, 6] as $level) {
            $this->post(route('runs.store'), [
                'umamusume_id' => $trainee->id,
                'status' => RunStatus::Active->value,
                'potential_level' => $level,
            ])->assertSessionHasErrors('potential_level');
        }

        expect(TrainingRun::query()->count())->toBe(0);
    }

    public function test_persists_a_later_edit_through_the_run_update_write(): void
    {
        $trainee = Umamusume::factory()->create();
        $run = TrainingRun::factory()->create([
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active,
        ]);

        $this->put(route('runs.update', $run), [
            'umamusume_id' => $trainee->id,
            'status' => RunStatus::Active->value,
            'trainee_rarity' => 1,
            'potential_level' => 5,
        ])->assertSessionHasNoErrors();

        expect($run->refresh()->trainee_rarity)->toBe(1)
            ->and($run->potential_level)->toBe(5);
    }

    private function latestRun(): TrainingRun
    {
        return TrainingRun::query()->latest('id')->firstOrFail();
    }
}
