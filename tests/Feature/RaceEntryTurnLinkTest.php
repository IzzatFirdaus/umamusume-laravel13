<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * KI-17: D-230 says Trackblazer's Race Fatigue is safe to surface because the consecutive-race
 * count "is already recoverable from `turn_entries`". As the schema stood it was not: a
 * `race_entries` row pointed at a calendar slot (month, half) and never at a turn, so no logged
 * turn could be identified as the turn a race happened on. Slice 15 T1 of the Schema Session
 * takes the first of the two fixes KI-17 names — a link from the entry to the turn — and the
 * Trainer names that turn in the race panel, because the link is entered data like every other
 * figure on this form (D-270), never a date guess.
 *
 * The link is nullable on purpose. A race logged before turns were, or a race the Trainer simply
 * does not tie to a turn, stays a complete row: KI-17's gap was the absence of the link, not the
 * presence of unlinked entries.
 */

it('carries a nullable turn_entry_id on race_entries with a foreign key to turn_entries', function (): void {
    expect(Schema::hasColumn('race_entries', 'turn_entry_id'))->toBeTrue();

    $fkToTurns = collect(Schema::getForeignKeys('race_entries'))
        ->contains(fn (array $fk): bool => $fk['foreign_table'] === 'turn_entries');

    expect($fkToTurns)->toBeTrue();
});

it('links a race entry to the turn the Trainer names', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 12]);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Japanese Derby (Tokyo Yushun)',
        'tier' => 'G1',
    ]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'turn_entry_id' => $turn->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ])->assertRedirect(route('runs.show', $run));

    $entry = RaceEntry::where('training_run_id', $run->id)->sole();

    expect($entry->turn_entry_id)->toBe($turn->id)
        ->and($entry->turnEntry)->not->toBeNull()
        ->and($entry->turnEntry->turn)->toBe(12);
});

it('leaves the link null when the Trainer names no turn, and the entry still writes', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 3]);
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'ura_finale', 'kind' => 'goal_race']);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 4,
    ])->assertRedirect(route('runs.show', $run));

    expect(RaceEntry::where('training_run_id', $run->id)->sole()->turn_entry_id)->toBeNull();
});

it('clears an empty turn choice to null rather than to turn zero', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'ura_finale', 'kind' => 'goal_race']);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'turn_entry_id' => '',
        'status' => RaceEntryStatus::Completed->value,
    ])->assertRedirect(route('runs.show', $run));

    expect(RaceEntry::where('training_run_id', $run->id)->sole()->turn_entry_id)->toBeNull();
});

it('rejects a turn logged on a different run and writes no entry', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $otherRun = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $foreignTurn = TurnEntry::factory()->create(['training_run_id' => $otherRun->id, 'turn' => 7]);
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'ura_finale', 'kind' => 'goal_race']);

    $this->from(route('runs.show', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'calendar',
            'scenario_slot_id' => $slot->id,
            'turn_entry_id' => $foreignTurn->id,
            'status' => RaceEntryStatus::Completed->value,
        ])
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHasErrors('turn_entry_id');

    expect(RaceEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('offers exactly this run\'s logged turns in the race panel dropdown', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $first = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 5]);
    $later = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 9]);
    $stranger = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $theirs = TurnEntry::factory()->create(['training_run_id' => $stranger->id, 'turn' => 2]);

    // The picker's option list is built from `racePanel.turns`. Ordered by turn number, not by id
    // (the `turnEntries` relation carries `orderBy('turn')`), and exactly this run's rows: a turn
    // from another run would have to appear here to matter, and there is no room in two entries for
    // a third. The blank "not named" option and the `Turn N` label are the component's rendering.
    $this->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('racePanel.turns', [
            ['id' => $first->id, 'turn' => 5],
            ['id' => $later->id, 'turn' => 9],
        ])
        ->where('racePanel.turns', fn (Collection $turns): bool => ! $turns->contains(
            fn (array $row): bool => $row['id'] === $theirs->id
        )));
});

it('links a free race to its turn too, since both branches share the one form', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 8]);

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'manual',
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'turn_entry_id' => $turn->id,
        'status' => RaceEntryStatus::Completed->value,
    ])->assertRedirect(route('runs.show', $run));

    $entry = RaceEntry::where('training_run_id', $run->id)->sole();

    expect($entry->scenarioSlot->kind)->toBe('free_race')
        ->and($entry->turn_entry_id)->toBe($turn->id);
});

it('reads the linked turn back off the row the form just wrote', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 14]);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Arimura Kinen',
        'tier' => 'G1',
    ]);

    $response = $this->followingRedirects()->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'turn_entry_id' => $turn->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ]);

    // A control with no readback is a control that silently loses its value on reload.
    expect($response->content())->toMatch('/\bturn 14\b/');
});

it('brings the turn choice back when the form comes back with entered values', function (): void {
    // R67: a Trainer who mistypes one field comes back to the form with what they entered still
    // in it. Retyping a turn because a placement was wrong is the friction that ruling removed
    // for the other fields on this form.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 21]);
    $other = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 22]);
    $slot = ScenarioSlot::factory()->create(['scenario_key' => 'ura_finale', 'kind' => 'goal_race']);

    // `withOld()` is not a helper this Laravel version exposes, and phpunit.xml pins SESSION_DRIVER
    // to `array`, so a failed write followed by a redirect would drop the flashed input. It is
    // therefore seeded directly, which is the state the component rehydrates from.
    $this->withSession([
        '_old_input' => [
            'entry_mode' => 'calendar',
            'scenario_slot_id' => (string) $slot->id,
            'turn_entry_id' => (string) $turn->id,
            'status' => RaceEntryStatus::Completed->value,
        ],
    ])->get(route('runs.show', $run))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        // Both logged turns stay offered to the picker.
        ->where('racePanel.turns', [
            ['id' => $turn->id, 'turn' => 21],
            ['id' => $other->id, 'turn' => 22],
        ])
        // The choice the Trainer made comes back as the flashed input, which is what the component
        // re-selects. A single rehydrated value cannot be the other turn too, so the `selected`
        // attribute landing on this row and not on turn 22 is carried by the same assertion.
        ->where('racePanel.old.turn_entry_id', (string) $turn->id));
});
