<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * R61/R68: a `free_race` cell takes open-cell geometry and the Trainer-entered marker, and never
 * a Goal pennant.
 *
 * Slice 12 §7.3 reported this rule as lost. Slice 13 found that report was wrong about the cause
 * and the calendar session reached the same conclusion independently in `5dcc06c`, which pins the
 * model: a persisted free_race row reads back as `['state' => 'open', 'manual' => true]`.
 *
 * What nothing pinned was the rendered cell, and the one test that appeared to was
 * `RaceCalendarTest.php`, in the case that labels manual rows distinctly, which hand-writes `['state' => 'past', 'label' => 'Local Stakes
 * (Trainer-entered)']` into the cells array and then asserts the string `Trainer-entered` is in
 * the output. That passes on the label text alone; it would still pass if `calendarCell()` never
 * emitted `manual`, because it never calls `calendarCell()`. So the check could not fail for the
 * reason it was running, which is the same failure KI-21 was filed against.
 *
 * This file goes through the read path. A Trainer-entered race is created as a row, the run is
 * fetched, and the page payload is required to carry the manual slot inside a calendar cell in the
 * open (or past) state, with no `goal` state anywhere the pennant could draw from; the geometry
 * classes and the spoken state words are the components'.
 */

/**
 * The slot the calendar payload holds for one half-month, straight out of the props.
 *
 * @return array<string, mixed>|null
 */
function calendarSlotIn(Collection $cells, int $monthIndex, string $half): ?array
{
    return $cells[$monthIndex]['halves'][$half]['slots'][0] ?? null;
}

/**
 * Every slot the twelve month-cells hold, Early and Late.
 *
 * @return Collection<int, array<string, mixed>>
 */
function allCalendarSlots(Collection $cells): Collection
{
    return $cells->flatMap(static fn (array $month): array => [
        ...$month['halves']['Early']['slots'],
        ...$month['halves']['Late']['slots'],
    ]);
}

it('renders a persisted free_race row as an open manual cell on the run screen', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'source_key' => null,
        'slot_label' => 'Autumn Practice Stakes',
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'tier' => null,
        'is_manual' => true,
        'sort_order' => 1,
    ]);

    // Scoped to the calendar payload's own cells: the race panel prints the same words on its
    // entry rows, so a page-wide check would pass without the calendar carrying anything at all.
    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('calendar.show', true)
        // Open geometry and the manual marker: the state the read path is required to report (R61).
        ->where('calendar.cells', function (Collection $cells): bool {
            $cell = calendarSlotIn($cells, 8, 'Late');

            return $cell !== null
                && $cell['state'] === 'open'
                && $cell['label'] === 'Autumn Practice Stakes'
                && $cell['manual'] === true;
        })
        // D-181: the pennant is drawn only from state `goal`, so the whole payload holding zero
        // of them is the assertion.
        ->where('calendar.cells', fn (Collection $cells) => allCalendarSlots($cells)->doesntContain(
            fn (array $slot): bool => $slot['state'] === 'goal'
        )));
});

/*
 * The marker survives the finish. `TrainingRun::calendarCell()` returns `['state' => 'past', ...,
 * 'manual' => true]` for a free race with a recorded entry, because `free_race` is a tool concept
 * rather than a client one: the game never offers a race that is not in the calendar, so "this row
 * came from the Trainer" is provenance about where the record came from, and Slice 13 measured that
 * it reads better looking back over a finished career. Slice 12 reported the marker as lost; the
 * read path's owner made that call in the same window this slice ran, and the test below is what
 * pins it.
 */
it('keeps the Trainer-entered marker on a free_race cell after its finish is recorded', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'source_key' => null,
        'slot_label' => 'Autumn Practice Stakes',
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'tier' => null,
        'is_manual' => true,
        'sort_order' => 1,
    ]);
    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // Past cell: the run happened here, and the provenance is still named. The cell's own
        // state word is `Scheduled` (the component's), never `goal`, so still no pennant.
        ->where('calendar.cells', function (Collection $cells): bool {
            $cell = calendarSlotIn($cells, 8, 'Late');

            return $cell !== null
                && $cell['state'] === 'past'
                && $cell['label'] === 'Autumn Practice Stakes'
                && $cell['manual'] === true;
        })
        ->where('calendar.cells', fn (Collection $cells) => allCalendarSlots($cells)->doesntContain(
            fn (array $slotItem): bool => $slotItem['state'] === 'goal'
        )));
});
