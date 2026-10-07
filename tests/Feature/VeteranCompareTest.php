<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\Veteran;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-022, the Veteran comparison (plan §8 D16). Props and the limit are asserted here; the rendered
 * table, the keyboard path and the axe scan live in `tests/browser/veteran-compare.spec.ts`.
 *
 * **This is not the Legacy Lab's compare surface.** `LegacyCompareRequest` refuses a run with no Legacy
 * read-back, because a column of absences is a screen that looks compared and is not. A career comparison
 * has the opposite requirement: the career the Trainer just filed usually has no read-back at all, and
 * dropping it would delete the column they came to look at. Case (3) is the difference, tested.
 *
 * Design-2.0 §46 owns the shape: identical properties aligned in rows, never two cards the Trainer has to
 * hold in memory side by side.
 */

function compareVeteran(string $name, array $tags, ?array $legacySelection = null): Veteran
{
    $run = TrainingRun::factory()->create([
        'status' => RunStatus::Completed,
        'umamusume_id' => Umamusume::factory()->state(['name' => $name])->create()->id,
        'legacy_selection' => $legacySelection,
    ]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 400,
        'stamina' => 300,
        'power' => 250,
        'guts' => 200,
        'wit' => 180,
    ]);

    return Veteran::factory()->create(['training_run_id' => $run->id, 'tags' => $tags]);
}

it('lines up the filed careers the Trainer selected, in the order they named them', function (): void {
    $first = compareVeteran('Special Week', ['Speed']);
    $second = compareVeteran('Silence Suzuka', ['Stamina']);

    $this->get(route('veterans.compare', ['veterans' => [$second->id, $first->id]]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Veterans/Compare')
            ->has('columns', 2)
            // Posted order, not the order the database finds cheapest: a comparison of the wrong two
            // things reads as a correct answer.
            ->where('columns.0.trainee', 'Silence Suzuka')
            ->where('columns.1.trainee', 'Special Week')
            ->where('columns.0.stats.Speed', 400)
            ->where('columns.0.tags', ['Stamina'])
            ->where('columns.0.career.turns', 1)
            // The ten axes arrive once, not derived from whichever column came first.
            ->has('aptitude_axes', 10)
            ->where('max', 4));
});

it('refuses a fifth career rather than wrapping the table past its region', function (): void {
    $ids = [];

    foreach (range(1, 5) as $i) {
        $ids[] = compareVeteran("Comparison Candidate $i", ['Speed'])->id;
    }

    $this->get(route('veterans.compare', ['veterans' => $ids]))
        ->assertSessionHasErrors('veterans');
});

it('compares a filed career that has no Legacy read-back, which the Legacy Lab surface refuses', function (): void {
    $veteran = compareVeteran('Rice Shower', ['Long']);

    expect($veteran->trainingRun->legacySelection())->toBeNull();

    $this->get(route('veterans.compare', ['veterans' => [$veteran->id]]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('columns', 1)
            ->where('columns.0.trainee', 'Rice Shower'));
});

it('states the held comparison rows as absences with their reason instead of scoring the careers', function (): void {
    $first = compareVeteran('Miyasaka Park', ['Speed']);
    $second = compareVeteran('Narita Brian', ['Speed']);

    $this->get(route('veterans.compare', ['veterans' => [$first->id, $second->id]]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('absences', fn ($absences): bool => count($absences) >= 1
                && collect($absences)->every(fn (array $absence): bool => $absence['reason'] !== ''))
            ->where('notice', fn (string $notice): bool => str_contains($notice, 'Record only')));
});

it('says so when nothing is selected, rather than rendering an empty table', function (): void {
    $this->get(route('veterans.compare'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', [])
            ->where('selected', [])
            ->has('empty'));
});

it('refuses a career id that is not in the library', function (): void {
    compareVeteran('Mayano Top Gun', ['Sprint']);

    $this->get(route('veterans.compare', ['veterans' => [9999]]))
        ->assertSessionHasErrors('veterans.0');
});

it('offers only the filed careers to the picker, ordered by name', function (): void {
    $listed = compareVeteran('Gold Ship', ['Scenario']);
    compareVeteran('Icy Pearl', ['Speed']);
    $unfiled = TrainingRun::factory()->create(['status' => RunStatus::Completed]);

    $this->get(route('veterans.compare', ['veterans' => [$listed->id]]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Two filed careers, not three: the unfiled run is not a Veteran and cannot be compared.
            ->has('comparable', 2)
            // `collect()` rather than `array_column()`: Inertia hands a nested value as an array or a
            // Collection depending on its depth, and the assertion should not depend on which.
            ->where('comparable', fn ($comparable): bool => collect($comparable)->pluck('label')->all() === ['Gold Ship', 'Icy Pearl'])
            ->where('columns.0.veteran_url', route('veterans.show', $listed)));

    expect($unfiled->veteran)->toBeNull();
});
