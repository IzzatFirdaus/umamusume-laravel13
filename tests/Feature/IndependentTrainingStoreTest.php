<?php

declare(strict_types=1);

use App\Enums\IndependentTrainingPolicy;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;

/*
 * UMAMUSUME_REFERENCE.md §1.1.7: the preflight write is where the mode is entered. The flag and the
 * three inputs ride the page's own POST — everything else is composed from the session draft, which
 * this test fills with the minimum (scenario + trainee) a run needs.
 */

function indepTrainee(): Umamusume
{
    return Umamusume::factory()->create();
}

it('starts an Independent Training session when the flag is posted', function (): void {
    session([SetupDraft::SESSION_KEY => ['scenario' => 'ura_finale', 'umamusume_id' => indepTrainee()->id]]);

    $skill = Skill::factory()->create();

    $this->putJson(route('career.preflight.store'), [
        'independent' => true,
        'policy' => 'stamina',
        'priority_skills' => [$skill->id],
    ], ['X-Inertia' => 'true'])->assertRedirect();

    $run = TrainingRun::firstOrFail();

    expect($run->isIndependent())->toBeTrue()
        ->and($run->independentTrainingRun->policy)->toBe(IndependentTrainingPolicy::Stamina)
        ->and($run->independentTrainingRun->priority_skills)->toBe([$skill->id]);
});

it('creates a normal career when the flag is absent', function (): void {
    session([SetupDraft::SESSION_KEY => ['scenario' => 'ura_finale', 'umamusume_id' => indepTrainee()->id]]);

    $this->putJson(route('career.preflight.store'), [], ['X-Inertia' => 'true'])->assertRedirect();

    expect(TrainingRun::firstOrFail()->isIndependent())->toBeFalse();
});

it('refuses an unknown policy when the flag is on', function (): void {
    session([SetupDraft::SESSION_KEY => ['scenario' => 'ura_finale', 'umamusume_id' => indepTrainee()->id]]);

    $this->putJson(route('career.preflight.store'), [
        'independent' => true,
        'policy' => 'nonsense',
    ], ['X-Inertia' => 'true'])->assertUnprocessable();
});
