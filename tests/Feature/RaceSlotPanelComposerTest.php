<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * S2: the run-detail goal panels read from `scenario_slots` and this run's own
 * race entries, not from values written in the template.
 *
 * The shaping lives on the model beside `stripValues()` because it is the same
 * kind of thing: run state composed for a component. Keeping it in the view
 * would put a query and a config walk inside a template.
 *
 * Since the Inertia port (ADR-0020 §1) this file asserts the shaped state on the
 * model and the client treatment on the component's own source: `stateClass`,
 * `stateWord`, and the pennant and marker spans are the whole treatment, and the
 * page is what feeds the component now. The cases that used to hand a hand-built
 * cell array to a Blade tag are the ones the component's source answers instead;
 * each says why the model cannot produce that cell.
 */

function runWithFans(int $fans): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'fans' => $fans]);

    return $run->fresh();
}

/**
 * The calendar component's source, which owns the cell treatment.
 */
function composerSource(): string
{
    return (string) file_get_contents(base_path('resources/js/components/RaceCalendar.vue'));
}

/**
 * One entry out of the `stateClass` map, so a treatment is read rather than retyped.
 */
function composerStateClass(string $state): string
{
    preg_match('/'.preg_quote($state, '/').": '([^']*)',/", composerSource(), $match);

    return $match[1] ?? '';
}

it('renders a mandatory career race as an open cell, not as a Goal pennant', function (): void {
    $run = runWithFans(20000);
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => 1,
        'title' => 'Tenno Sho (Spring)',
        'month' => 4,
        'half' => 'Late',
        'turn' => 8,
        'is_mandatory' => true,
        'fans_needed' => 12000,
    ]);

    $cells = $run->calendarCells();

    // The cell indexes from zero (Jan = 0); the table stores 1-12.
    //
    // This is the audit's conflation, closed. `is_mandatory` is a scenario-scoped
    // career obligation — the debut and the final rounds — while the client's red
    // banner marks a per-character objective. Every Goal banner in the four
    // [Global] panels recorded in 09 sits on a race like NHK Mile Cup or Tokyo
    // Yushun, never on the debut or the finals. So the flag stays true on the row
    // and stops being a rendering input until trainee_goals can drive it.
    expect($cells[3]['halves']['Late']['slots'][0]['state'])->toBe('open')
        ->and($cells[3]['halves']['Late']['slots'][0]['label'])->toBe('Tenno Sho (Spring)')
        ->and($cells[0]['halves']['Early']['slots'])->toBe([])
        ->and(count($cells))->toBe(12);
});

it('keeps the debut pennant-free while it is mandatory but unGoal-ed', function (): void {
    // State one of the two the fix needs: mandatory, no Goal seeded, no pennant.
    $run = runWithFans(20000);
    RaceCatalogSlot::factory()->debut()->create();

    // The model decides the cell state, and a mandatory career race is an open cell.
    expect($run->fresh()->calendarCells(1)[5]['halves']['Late']['slots'][0])
        ->toMatchArray(['state' => 'open', 'label' => 'Junior Make Debut']);

    // The pennant is then gated on that state alone, so no cell the model can emit today can draw one.
    expect(composerSource())->toContain('v-if="cell.state === \'goal\'"')
        ->toContain("goal: 'border-2 border-goal-line");
});

it('locks a fan-gated race until this run has the fans it asks for', function (): void {
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => 1,
        'title' => 'Oka Sho',
        'month' => 4,
        'half' => 'Early',
        'turn' => 7,
        'is_mandatory' => false,
        'fans_needed' => 15000,
    ]);

    $short = runWithFans(3000);
    $long = runWithFans(15000);

    // The figure travels with the lock: it is what the Trainer works toward (D-173).
    expect($short->calendarCells()[3]['halves']['Early']['slots'][0]['state'])->toBe('fan_locked')
        ->and($short->calendarCells()[3]['halves']['Early']['slots'][0]['fans_needed'])->toBe(15000)
        ->and($long->calendarCells()[3]['halves']['Early']['slots'][0]['state'])->toBe('open');
});

it('shows a maiden-gated race as a maiden lock until this run has won', function (): void {
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => 1,
        'title' => 'Naruta Kinpa Cup',
        'month' => 5,
        'half' => 'Early',
        'turn' => 9,
        'is_mandatory' => false,
        'is_maiden_gated' => true,
        'fans_needed' => 0,
    ]);

    $winless = runWithFans(3000);

    expect($winless->calendarCells()[4]['halves']['Early']['slots'][0]['state'])->toBe('maiden_locked')
        // No fan figure: a maiden gate is decided by an event, not a quantity.
        ->and($winless->calendarCells()[4]['halves']['Early']['slots'][0])->not->toHaveKey('fans_needed');

    RaceEntry::create([
        'training_run_id' => $winless->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    expect($winless->fresh()->calendarCells()[4]['halves']['Early']['slots'][0]['state'])->toBe('open');
});

it('marks a slot this run has already raced as past', function (): void {
    $run = runWithFans(20000);
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => 1,
        'title' => 'Asahi Hai Futurity Stakes',
        'month' => 11,
        'half' => 'Late',
        'turn' => 21,
        'is_mandatory' => true,
    ]);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 3,
    ]);

    expect($run->calendarCells()[10]['halves']['Late']['slots'][0]['state'])->toBe('past');
});

it('carries a Trainer-typed free race through the model as an open manual cell', function (): void {
    // The existing "labels manual rows distinctly" check in RaceCalendarTest builds
    // its cells array by hand and hands them to the component, so it cannot see the
    // model drop the marker. This is the path that actually regresses: a real
    // free_race row, read through calendarCells(). KI-22 suspected the rule had
    // been lost during the read-path retarget; this is what proves it either way.
    $run = runWithFans(20000);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Some Cup',
        'slot_label' => 'Some Cup',
        'month' => 8,
        'half' => 'Late',
        'tier' => null,
        'is_mandatory' => false,
        'is_manual' => true,
    ]);

    $cell = $run->fresh()->calendarCells()[7]['halves']['Late']['slots'][0];

    expect($cell)->toMatchArray(['state' => 'open', 'label' => 'Some Cup', 'manual' => true])
        // R61: a free race is never a goal, whatever the flags say.
        ->not->toBe('goal');
});

it('keeps a free race out of the goal pennant even when it is marked mandatory', function (): void {
    // The retarget passes an explicit `false` for mandatory on the free-race path
    // rather than reading the column, so a mis-set flag cannot draw a pennant on a
    // race the Trainer invented.
    $run = runWithFans(20000);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Mislabeled Cup',
        'slot_label' => 'Mislabeled Cup',
        'month' => 9,
        'half' => 'Early',
        'is_mandatory' => true,
        'is_manual' => true,
    ]);

    $cell = $run->fresh()->calendarCells()[8]['halves']['Early']['slots'][0];

    expect($cell['state'])->toBe('open')->and($cell['manual'])->toBeTrue();
});

it('shows a catalogue race and a free race together in the same half-month', function (): void {
    // The retarget reads two tables and merges them; the merge is the part a
    // single-source test would never exercise.
    $run = runWithFans(20000);
    RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 8, 'half' => 'Early', 'turn' => 15, 'title' => 'Phoenix Sho',
    ]);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Trainer Pick',
        'slot_label' => 'Trainer Pick',
        'month' => 8,
        'half' => 'Early',
        'is_manual' => true,
    ]);

    $labels = array_column($run->fresh()->calendarCells()[7]['halves']['Early']['slots'], 'label');

    expect($labels)->toBe(['Phoenix Sho', 'Trainer Pick']);
});

it('keeps the goal treatment ready on the component while the model withholds it', function (): void {
    // State two. There is no trainee_goals table yet, so the model cannot know a
    // character's objective — but the treatment must not be deleted on the way
    // past that, or the fix becomes a rewrite. A cell carrying the goal state
    // still draws the pennant, which is exactly what a goals source will emit.
    //
    // The cell is read from the component rather than handed to it: no run can
    // produce a goal cell today, so the assertion is on the treatment the
    // component still holds and on the one gate that decides it.
    expect(composerStateClass('goal'))->toContain('border-goal-line')
        ->and(composerStateClass('goal'))->not->toContain('border-dashed')
        ->and(composerSource())
        ->toContain('goal: \'Mandatory goal\',')
        ->toContain('v-if="cell.state === \'goal\'"')
        ->toContain('border-l-goal');
});

it('keeps the Trainer-entered marker on a free race after it has been run', function (): void {
    // Slice 13 measured this and deliberately did not assert it, routing the
    // question to the read path (FreeRaceCalendarCellTest, R68). The answer is that
    // the marker survives: free_race is a tool concept, so "this row came from the
    // Trainer" is provenance about the record, not a state of the decision to enter.
    $run = runWithFans(20000);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Autumn Practice Stakes',
        'slot_label' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'is_manual' => true,
    ]);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 2,
    ]);

    $cell = $run->fresh()->calendarCells()[8]['halves']['Late']['slots'][0];

    expect($cell)->toMatchArray(['state' => 'past', 'label' => 'Autumn Practice Stakes', 'manual' => true]);

    // The marker is drawn from the cell's own `manual` flag and from nothing else, so a past free race
    // keeps it while a past catalogue race does not — see the case below.
    expect(composerSource())
        ->toContain('manual: slots.some((slotItem) => slotItem.manual === true)')
        ->toContain('v-if="cell.manual"')
        ->toContain('Trainer-entered');
});

it('does not give a calendar race the Trainer-entered marker once run', function (): void {
    // The other half: surviving must not mean spreading. A catalogue race that has
    // been run stays a plain past cell.
    $run = runWithFans(20000);
    $slot = RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 10, 'half' => 'Early', 'turn' => 19, 'title' => 'Artemis Stakes',
    ]);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    expect($run->fresh()->calendarCells()[9]['halves']['Early']['slots'][0])
        ->toMatchArray(['state' => 'past', 'label' => 'Artemis Stakes'])
        ->not->toHaveKey('manual');
});

it('composes the calendar only from the run own scenario', function (): void {
    $run = runWithFans(20000);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'kind' => 'goal_race',
        'month' => 4,
        'half' => 'Late',
    ]);

    // Another scenario's calendar is not this run's, and the panel says so in its
    // own words rather than drawing a grid of blanks (D-220).
    expect($run->calendarCells())->toBe([]);
});

it('names no scenario race for a run that has not chosen one', function (): void {
    // Found in the browser pass, not by the suite. `scenarioKey()` falls back to the
    // baseline so the resource strip always has something to compose — an owner
    // ruling about generic widgets. The goal panels are not generic: they name
    // Oka Sho and Tenno Sho. Feeding them the fallback made a run whose header says
    // "No scenario set" list URA's actual race schedule below it.
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Oka Sho',
        'month' => 4,
        'half' => 'Early',
    ]);

    $run = TrainingRun::factory()->create(['scenario' => null]);

    expect($run->calendarCells())->toBe([])
        ->and($run->gradeObjectives())->toBe([])
        ->and($run->gradeEarned())->toBeNull()
        ->and(test()->get("/training-runs/{$run->id}")->getContent())
        ->not->toContain('Race calendar')
        ->not->toContain('Grade Point')
        ->not->toContain('Oka Sho');
});

it('lists the Grade Point objectives in order, named from the matrix', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'trackblazer']);

    // Each row now carries the 1-based period a finish is entered against, so the
    // number a Trainer says and the row it lands on cannot drift (US-10, ADR-0003).
    expect($run->gradeObjectives())->toBe([
        ['index' => 1, 'name' => 'Debut race', 'required' => 0],
        ['index' => 2, 'name' => 'End of Junior Year', 'required' => 60],
        ['index' => 3, 'name' => 'End of Classic Year', 'required' => 300],
        ['index' => 4, 'name' => 'End of Senior Year', 'required' => 300],
    ]);
});

it('sums Grade Points only when every completed race can be priced', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'trackblazer',
        'current_objective_index' => 2,
    ]);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'kind' => 'goal_race',
        'tier' => 'G1',
    ]);

    expect($run->gradeEarned())->toBeNull();

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'objective_index' => 2,
    ]);

    // 100 for a G1 win, from config's `grade_point_by_grade` (docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Grade
    // Points). The race names the period and the run names the live one: `gradeEarned()`
    // is period-aware now, so a finish entered against Junior is only a Classic figure
    // when the Trainer says Classic is what they are working to (D-270, D-232).
    expect($run->fresh()->gradeEarned())->toBe(100);

    // Below first the points scale down, and no ratio for that is in our corpus, so
    // the total is withheld rather than understated.
    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 2,
        'objective_index' => 2,
    ]);

    expect($run->fresh()->gradeEarned())->toBeNull();
});
