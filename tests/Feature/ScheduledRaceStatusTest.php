<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * `Scheduled` is a read-side provenance state: an imported calendar slot with no observed entry
 * decision. It must render with its own label, but a Trainer must not be able to post it — a
 * hand-entered row claiming imported provenance would defeat the distinction (KI-171).
 *
 * Readers that ask "has the Trainer decided on this race?" must go through `recordsEntry()`. A
 * `Scheduled` row that still counted there cleared the obligation, emptied the still-to-come strip
 * and greyed a planner row (KI-173).
 */
function scheduledRun(): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'umamusume_id' => Umamusume::factory(),
    ]);
}

/**
 * A run that has actually logged turns, so every surface that reads `decisionTurn()` has a position.
 */
function scheduledTurnRun(string $scenario = 'unity_cup', int $loggedTurns = 12): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory(),
    ]);

    for ($turn = 1; $turn <= $loggedTurns; $turn++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 600,
            'stamina' => 600,
            'power' => 600,
            'guts' => 600,
            'wit' => 600,
            'sp' => 120,
            'energy' => 80,
            'fans' => 1000 * $turn,
        ]);
    }

    return $run->fresh();
}

function scheduledFixtureSlot(string $title, int $year, int $turn): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'year' => $year,
        'month' => 4,
        'half' => 'Early',
        'turn' => $turn,
        'slot_label' => $title,
        'title' => $title,
        'tier' => 'G1',
        'is_mandatory' => true,
        'fans_needed' => null,
    ]);
}

it('labels Scheduled distinctly and keeps it out of the writable status set', function (): void {
    expect(RaceEntryStatus::Scheduled->label())->toBe('Scheduled (entry unrecorded)')
        ->and(RaceEntryStatus::writable())->toBe([
            RaceEntryStatus::NotOffered,
            RaceEntryStatus::Skipped,
            RaceEntryStatus::Entered,
            RaceEntryStatus::Completed,
        ])
        ->and(RaceEntryStatus::writable())->not->toContain(RaceEntryStatus::Scheduled);
});

it('does not offer Scheduled as a manual race outcome on the Race Decision screen', function (): void {
    $run = scheduledRun();

    $this->get(route('runs.races.decision', ['run' => $run, 'entry_mode' => 'manual']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('race_statuses', fn ($statuses): bool => ! in_array('Scheduled', $statuses->all(), true))
            ->where('manual_defaults.status', 'Entered'));
});

it('refuses a posted Scheduled status instead of writing imported provenance by hand', function (): void {
    $run = scheduledRun();

    $this->from(route('runs.races.decision', $run))
        ->post(route('runs.races.store', $run), [
            'entry_mode' => 'manual',
            'title' => 'A race the Trainer ran',
            'month' => 10,
            'half' => 'Early',
            'status' => RaceEntryStatus::Scheduled->value,
        ])
        ->assertSessionHasErrors('status');

    expect($run->raceEntries()->count())->toBe(0);
});

it('reads a Scheduled row back through the calendar strip with its own label', function (): void {
    $run = scheduledRun();
    $turn = TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 6]);
    $slot = RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'month' => 3,
        'half' => 'Early',
        'turn' => 6,
        'title' => 'Scheduled fixture race',
    ]);

    $run->raceEntries()->create([
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Scheduled->value,
        'turn_entry_id' => $turn->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.run.0.status', 'Scheduled')
            ->where('raceStrip.run.0.status_label', 'Scheduled (entry unrecorded)'));
});

it('records an entry decision for every status except Scheduled', function (): void {
    foreach (RaceEntryStatus::cases() as $status) {
        expect($status->recordsEntry())->toBe($status !== RaceEntryStatus::Scheduled);
    }
});

it('leaves a mandatory obligation open when its only row is Scheduled', function (): void {
    $run = scheduledTurnRun();
    $turn = $run->turnEntries()->where('turn', 12)->firstOrFail();
    $slot = scheduledFixtureSlot('Saihai Sho', RaceCatalogSlot::YEAR_JUNIOR, 12);

    $run->raceEntries()->create([
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Scheduled->value,
        'turn_entry_id' => $turn->id,
    ]);

    /** @var array{objectives: list<array<string, mixed>>} $decision */
    $decision = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('advisor.decision');
    $objective = collect($decision['objectives'])->firstWhere('label', 'Saihai Sho');

    /** @var array{objectives: list<array<string, mixed>>} $scenario */
    $scenario = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('scenario');
    $group = collect($scenario['objectives'])->firstWhere('group', 'Mandatory races');
    $goal = collect($group['rows'])->firstWhere('label', 'Saihai Sho');

    // Before KI-173 the `Scheduled` row counted as an entry: both surfaces would have printed
    // `completed`, and the goals ladder would have called it `missed`.
    expect($objective)->not->toBeNull()
        ->and($goal)->not->toBeNull()
        ->and($objective['state'])->not->toBeIn(['completed', 'missed'])
        ->and($goal['state'])->not->toBeIn(['completed', 'missed']);
});

it('keeps a race whose only row is Scheduled in the still-to-come strip', function (): void {
    $run = scheduledTurnRun();
    $turn = $run->turnEntries()->where('turn', 12)->firstOrFail();
    $slot = scheduledFixtureSlot('A Race Still Ahead', RaceCatalogSlot::YEAR_CLASSIC, 20);

    $run->raceEntries()->create([
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Scheduled->value,
        'turn_entry_id' => $turn->id,
    ]);

    /** @var array{ahead: list<array<string, mixed>>, run: list<array<string, mixed>>} $strip */
    $strip = test()->get(route('runs.cockpit', $run))->assertOk()->inertiaProps('raceStrip');

    // The same slot stays ahead; the "Recorded against this turn" line keeps its own provenance label.
    expect(collect($strip['ahead'])->pluck('id')->all())->toContain($slot->id)
        ->and(collect($strip['run'])->pluck('status')->all())
        ->toContain(RaceEntryStatus::Scheduled->value);
});

it('prints no planner status for a race whose only row is Scheduled', function (): void {
    $run = scheduledTurnRun();
    $turn = $run->turnEntries()->where('turn', 12)->firstOrFail();
    $slot = scheduledFixtureSlot('A Planned Race', RaceCatalogSlot::YEAR_CLASSIC, 20);

    $run->raceEntries()->create([
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Scheduled->value,
        'turn_entry_id' => $turn->id,
    ]);

    /** @var array<int, array{races: list<array<string, mixed>>}> $groups */
    $groups = test()->get(route('runs.races.planner', $run))->assertOk()->inertiaProps('groups');
    $row = collect($groups)
        ->flatMap(fn (array $group): array => $group['races'] ?? [])
        ->firstWhere('id', $slot->id);

    expect($row)->not->toBeNull()
        ->and($row['status'])->toBeNull()
        ->and($row['placement'])->toBeNull();
});
