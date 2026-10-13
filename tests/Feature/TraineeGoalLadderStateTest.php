<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Enums\TraineeGoalType;
use App\Models\CharacterCard;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Which absence an empty ladder prints (ADR-0026, plan §5.5).
 *
 * Both ladder surfaces used to print one sentence off one test: "no rows for this trainee". That reads a
 * fact about the publisher out of a fact about this database, because an unseeded `trainee_goals` table
 * makes the sentence true for all 67 roster trainees at once, and the source states a ladder for every one
 * of them. Measured on 2026-10-14: `database/database.sqlite` held 0 rows while the committed body held
 * 135 characters, so the app asserted a source-level absence it had never asked about.
 *
 * `TraineeGoal::ladderAbsenceReason()` is the single owner of the wording now, so these cases assert it
 * directly and then assert that the two screens print what it says.
 */

/**
 * A trainee on the roster, with the one card the catalog detail page requires to render.
 */
function ladderStateTrainee(string $name, string $slug): Umamusume
{
    $umamusume = Umamusume::factory()->create([
        'name' => $name,
        'slug' => $slug,
        'release_status' => ReleaseStatus::GlobalReleased,
    ]);

    CharacterCard::factory()->create([
        'umamusume_id' => $umamusume->id,
        'card_id' => 910000 + $umamusume->id,
        'title' => '[Global] Form 1',
        'rarity' => CardRarity::ThreeStar,
        'is_debut_form' => true,
        'global_release_date' => '2026-01-01',
    ]);

    return $umamusume->fresh();
}

/**
 * The trainee's own ladder, read the way both surfaces read it (plan §2 excludes `FinalRound`).
 */
function ownLadder(Umamusume $umamusume): Collection
{
    return TraineeGoal::query()
        ->where('umamusume_id', $umamusume->id)
        ->where('goal_type', '!=', TraineeGoalType::FinalRound->value)
        ->get();
}

it('names this database rather than the source when no ladder has been loaded at all', function (): void {
    $rice = ladderStateTrainee('Rice Shower', 'rice-shower');

    expect(TraineeGoal::all())->toHaveCount(0)
        ->and(ownLadder($rice))->toBeEmpty()
        ->and(TraineeGoal::ladderAbsenceReason(ownLadder($rice)))->toBe(
            'No objective ladder has been loaded into this database, so nothing is listed here.'
        );
});

it('names the source once a ladder is loaded and this trainee is not in it', function (): void {
    $withLadder = ladderStateTrainee('Special Week', 'special-week');
    $without = ladderStateTrainee('Agnes Digital', 'agnes-digital');

    TraineeGoal::factory()->create(['umamusume_id' => $withLadder->id]);

    expect(TraineeGoal::ladderAbsenceReason(ownLadder($without)))->toBe(
        'The loaded ladder states no objective for this trainee, so nothing is listed here.'
    );
});

it('says nothing when the trainee has a ladder to print', function (): void {
    $rice = ladderStateTrainee('Rice Shower', 'rice-shower');
    TraineeGoal::factory()->create(['umamusume_id' => $rice->id]);

    expect(ownLadder($rice))->toHaveCount(1)
        ->and(TraineeGoal::ladderAbsenceReason(ownLadder($rice)))->toBeNull();
});

it('counts a ladder of final rounds only as no objective of her own to list', function (): void {
    // Both surfaces drop FinalRound rows (plan §2: they are the scenario's obligation), so the sentence has
    // to mean the same ladder the page prints. A row set of final rounds alone is loaded, and hers is empty.
    $rice = ladderStateTrainee('Rice Shower', 'rice-shower');
    $other = ladderStateTrainee('Special Week', 'special-week');
    TraineeGoal::factory()->create(['umamusume_id' => $other->id]);
    TraineeGoal::factory()->create([
        'umamusume_id' => $rice->id,
        'goal_type' => TraineeGoalType::FinalRound,
    ]);

    expect(ownLadder($rice))->toBeEmpty()
        ->and(TraineeGoal::ladderAbsenceReason(ownLadder($rice)))->toBe(
            'The loaded ladder states no objective for this trainee, so nothing is listed here.'
        );
});

it('prints the database sentence in the Cockpit, and not the source sentence', function (): void {
    $rice = ladderStateTrainee('Rice Shower', 'rice-shower');
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'umamusume_id' => $rice->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            $goals = collect($section->all()['objectives'])->keyBy('group')->get('Trainee goals');

            expect($goals['rows'])->toBe([])
                ->and($goals['empty_note'])->toContain('has been loaded into this database')
                ->and($goals['empty_note'])->not->toContain('states no objective for this trainee')
                // The screen's own clause still travels with it: this is the one surface that has the
                // scenario-wide group below the ladder.
                ->and($goals['empty_note'])->toContain('The mandatory races below are the scenario-wide record.');

            return true;
        }));
});

it('prints the database sentence on the catalog detail page, and not the source sentence', function (): void {
    $rice = ladderStateTrainee('Rice Shower', 'rice-shower');

    $ladder = $this->get(route('catalog.show', $rice->slug))
        ->assertOk()
        ->inertiaProps('traineeGoals');

    expect($ladder['rows'])->toBe([])
        ->and($ladder['empty_note'])->toBe(
            'No objective ladder has been loaded into this database, so nothing is listed here.'
        );
});
