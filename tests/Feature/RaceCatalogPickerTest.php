<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The picker's two defects, measured together.
 *
 * It read `scenario_slots`, which has no year column, so it offered every seeded race on
 * whatever tab was open — a February Stakes that the career catalogue dates to Senior Late
 * February, G1, 12,000 fans, presented to a Trainer sitting in Junior turn 3. The fix is the
 * career catalogue as the source and the viewed career year as the scope, which is the same
 * pair of facts the grid above the form is already drawn from.
 *
 * The second half is the write: a race picked from the calendar has to land on the catalogue
 * row it was picked from, or the cell it fills cannot be found again.
 *
 * Since the Inertia port (ADR-0020 §1) the picker's option list is `racePanel.calendarSlots` and
 * `racePanel.manualSlots`, composed by `TrainingRunController::racePanelPayload()` including the
 * `title · half-month · grade` label; which `<option>` each row becomes is `RacePanel.vue`, and
 * the rendered option text is carried by tests/browser/run-detail.spec.ts.
 */

function pickerRun(): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 3, 'fans' => 3100]);

    return $run->fresh();
}

it('offers only the career year the screen is showing', function (): void {
    $run = pickerRun();

    RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 7, 'half' => 'Late', 'turn' => 14,
        'title' => 'Chukyo Junior Stakes', 'tier' => 'G3',
    ]);
    RaceCatalogSlot::factory()->create([
        'year' => 3, 'month' => 2, 'half' => 'Late', 'turn' => 4,
        'title' => 'February Stakes', 'tier' => 'G1', 'fans_needed' => 12000,
    ]);

    $junior = $this->get(route('runs.show', $run))->content();

    expect($junior)->toContain('Chukyo Junior Stakes')
        // The defect, stated as an absence: a Senior race is not on a Junior screen.
        ->and($junior)->not->toContain('February Stakes');

    $senior = $this->get(route('runs.show', ['run' => $run, 'year' => 3]))->content();

    expect($senior)->toContain('February Stakes')
        ->and($senior)->not->toContain('Chukyo Junior Stakes');
});

it('names the half-month and grade of every calendar option, so the label is not the only clue', function (): void {
    $run = pickerRun();
    $slot = RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7,
        'title' => 'Oka Sho', 'slot_label' => 'Early April', 'tier' => 'G1',
    ]);

    // The label is composed server-side and arrives finished, so the picker cannot be narrowed to
    // the race name alone: the half-month and the grade travel in the same string the Trainer reads.
    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->has('racePanel.calendarSlots', 1)
        ->where('racePanel.calendarSlots.0.id', $slot->id)
        ->where('racePanel.calendarSlots.0.label', 'Oka Sho · Early April · G1')
        ->where('racePanel.calendarSlots.0.tier', 'G1'));

    // The component prints that label verbatim as the option's own text, keyed on the catalogue row
    // it names, so `value="\d+"` and its caption survive as one assertion on `RacePanel.vue`.
    $panel = (string) file_get_contents(base_path('resources/js/components/RacePanel.vue'));

    expect($panel)->toMatch('/<option\s+v-for="slot in calendarSlots"\s+:key="slot\.id"\s+:value="String\(slot\.id\)"[^>]*>\s*\{\{ slot\.label \}\}\s*<\/option>/');
});

it('records the race against the catalogue row the picker named', function (): void {
    $run = pickerRun();
    $slot = RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Oka Sho',
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ])->assertRedirect(route('runs.show', $run));

    $entry = RaceEntry::where('training_run_id', $run->id)->sole();

    expect($entry->race_catalog_slot_id)->toBe($slot->id)
        ->and($entry->scenario_slot_id)->toBeNull()
        // The point of the link: the cell the race was entered on reads as run.
        ->and($run->fresh()->calendarCells(1)[3]['halves']['Early']['slots'][0]['state'])->toBe('past');
});

it('refuses a catalogue row tagged to a scenario this run is not', function (): void {
    $run = pickerRun();
    $foreign = RaceCatalogSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'year' => 1, 'month' => 5, 'half' => 'Early', 'turn' => 9, 'title' => 'A Trackblazer-only Race',
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'race_catalog_slot_id' => $foreign->id,
        'status' => RaceEntryStatus::Completed->value,
    ])->assertSessionHasErrors('race_catalog_slot_id');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('refuses an entry that names both a calendar row and a hand-entered one', function (): void {
    $run = pickerRun();
    $calendar = RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Oka Sho',
    ]);
    $free = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Midsummer Practice Race',
        'slot_label' => 'Midsummer Practice Race',
        'month' => 7,
        'half' => 'Late',
        'is_manual' => true,
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'race_catalog_slot_id' => $calendar->id,
        'scenario_slot_id' => $free->id,
        'status' => RaceEntryStatus::Completed->value,
    ])->assertSessionHasErrors('race_catalog_slot_id');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('keeps a hand-entered race on its own control, apart from the calendar list', function (): void {
    $run = pickerRun();
    $calendar = RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 4, 'half' => 'Early', 'turn' => 7, 'title' => 'Oka Sho',
    ]);
    $free = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'title' => 'Autumn Practice Stakes',
        'slot_label' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'is_manual' => true,
    ]);

    // Two controls, two names: one select cannot carry both links. After the port the two lists are
    // two props with two id spaces, and the field each control posts is pinned on the `<select>`
    // that binds it, so a panel that collapsed both onto one control fails here.
    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->has('racePanel.calendarSlots', 1)
        ->where('racePanel.calendarSlots.0.id', $calendar->id)
        ->has('racePanel.manualSlots', 1)
        ->where('racePanel.manualSlots.0.id', $free->id)
        ->where('racePanel.manualSlots.0.title', 'Autumn Practice Stakes'));

    $panel = (string) file_get_contents(base_path('resources/js/components/RacePanel.vue'));

    expect($panel)->toMatch('/<select[^>]*v-model="form\.race_catalog_slot_id"[^>]*name="race_catalog_slot_id"/')
        ->and($panel)->toMatch('/<select[^>]*v-model="form\.scenario_slot_id"[^>]*name="scenario_slot_id"/');

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $free->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 3,
    ])->assertRedirect(route('runs.show', $run));

    $entry = RaceEntry::where('training_run_id', $run->id)->sole();

    expect($entry->scenario_slot_id)->toBe($free->id)
        ->and($entry->race_catalog_slot_id)->toBeNull();
});

it('shows a calendar race by its catalogue name and grade once recorded', function (): void {
    $run = pickerRun();
    $slot = RaceCatalogSlot::factory()->create([
        'year' => 1, 'month' => 10, 'half' => 'Early', 'turn' => 19,
        'title' => 'Artemis Stakes', 'tier' => 'G3',
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ]);

    $html = $this->get(route('runs.show', $run))->content();

    expect($html)->toContain('Artemis Stakes')
        ->and($html)->not->toContain('a race with no calendar row');
});
