<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * D14a, the run-scoped race strip (plan §8.3, an interim slice before D14). The props are asserted
 * here; the rendered copy, the keyboard path and the axe scan live in
 * `tests/browser/career-race-strip.spec.ts` (plan §2, convention 2).
 *
 * The load-bearing cases are the three the catalogue forces on the prompt's proposed contract:
 *
 * 1. `this_turn` cannot be one object. The seed corpus puts eleven catalogue rows at Senior turn 19,
 *    so a single "the race at this turn" would either drop ten or pick one, and picking one is the
 *    Race Decision screen's job (`SCR-CAR-013`), not this region's. It carries the position, how many
 *    races the calendar offers there, and what this run has already recorded against them.
 * 2. There is no goal data. `trainee_goals` has never been migrated (KI-34), `race_catalog_slots` has
 *    no trainee-bearing column, and `scenario_slots` holds `goal_race` rows for `ura_finale` only. A
 *    per-row `goal` boolean would print a claim the tool cannot source, so the region carries one
 *    named absence instead and the rows carry no `goal` key at all.
 * 3. One race, one region. Ahead is strictly future *and* unrecorded, so a finished race never reads
 *    as something still to come and never appears twice.
 */

/**
 * A run with `$turns` logged turns, so `nextTurnToPlay()` lands on the turn after the last one.
 *
 * Named `raceStripRun` rather than `raceRun`: Pest files share the global function namespace, and
 * `CareerRaceDecisionTest` owns `raceRun()` while `ResourceStripTest` owns `stripRun()`.
 */
function raceStripRun(int $turns = 0, ?string $scenario = 'unity_cup'): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run;
}

/** One row of the shared career catalogue. Null `scenario_key` means every scenario. */
function raceStripSlot(array $state = []): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create($state + [
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'month' => 7,
        'half' => 'Early',
        'turn' => 13,
        'title' => 'Chukyo Junior Stakes',
        'tier' => 'OP',
        'distance' => 1200,
        'distance_band' => 'Sprint',
        'surface' => 'Turf',
        'sort_order' => 1,
    ]);
}

/** The logged turn row for turn `$turn` of `$run`, for an entry's `turn_entry_id`. */
function raceStripTurn(TrainingRun $run, int $turn): TurnEntry
{
    return $run->turnEntries()->where('turn', $turn)->firstOrFail();
}

/** A Trainer-typed race: a scenario slot with no catalogue row behind it. */
function raceStripFreeSlot(TrainingRun $run, string $title): ScenarioSlot
{
    return ScenarioSlot::factory()->create([
        'scenario_key' => $run->scenarioKey(),
        'kind' => 'free_race',
        'title' => $title,
        'tier' => null,
        'is_mandatory' => false,
    ]);
}

it('lists the races this run recorded, oldest turn first, unlinked last', function (): void {
    $run = raceStripRun(turns: 12);
    $early = raceStripSlot(['title' => 'Kyoto Daishoten', 'year' => 1, 'turn' => 5, 'month' => 3, 'half' => 'Early', 'tier' => 'G2', 'sort_order' => 1]);
    $late = raceStripSlot(['title' => 'Arima Kinen', 'year' => 1, 'turn' => 12, 'month' => 6, 'half' => 'Late', 'tier' => 'G1', 'sort_order' => 2]);
    $typed = raceStripFreeSlot($run, 'A race the Trainer typed in');

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $late->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'turn_entry_id' => raceStripTurn($run, 12)->id,
    ]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $early->id,
        'status' => RaceEntryStatus::Entered,
        'turn_entry_id' => raceStripTurn($run, 5)->id,
    ]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $typed->id,
        'status' => RaceEntryStatus::Skipped,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->has('raceStrip.run', 3)
            // Ordered by the turn each race was run on, so the newest reads last.
            ->where('raceStrip.run.0.title', 'Kyoto Daishoten')
            ->where('raceStrip.run.0.turn', 5)
            ->where('raceStrip.run.0.tier', 'G2')
            ->where('raceStrip.run.0.status', 'Entered')
            ->where('raceStrip.run.0.status_label', 'Entered')
            ->where('raceStrip.run.0.placement', null)
            ->where('raceStrip.run.1.title', 'Arima Kinen')
            ->where('raceStrip.run.1.turn', 12)
            ->where('raceStrip.run.1.status_label', 'Completed')
            ->where('raceStrip.run.1.placement', '1st')
            // A typed race has no catalogue row, so the title is the slot's and the tier is the
            // model's own fallback. The row with no turn link sorts last and says so.
            ->where('raceStrip.run.2.title', 'A race the Trainer typed in')
            ->where('raceStrip.run.2.turn', null)
            ->where('raceStrip.run.2.status_label', 'Skipped')
            // The absence carries its reason, never a dash and never a zero.
            ->where('raceStrip.absences.turn_link', fn (string $line): bool => str_contains($line, 'turn'))
            ->where('raceStrip.links.run_url', route('runs.cockpit', $run))
            ->where('raceStrip.links.decision_url', route('runs.races.decision', $run)));
});

it('summarises the turn being decided rather than re-listing every race offered there', function (): void {
    $run = raceStripRun(turns: 12);

    // Eleven rows is what the seed corpus actually holds at Senior turn 19; three here is enough to
    // prove the region counts instead of listing, and one of them is already recorded.
    $picked = raceStripSlot(['title' => 'Kyoto Daishoten', 'tier' => 'G2', 'sort_order' => 1]);
    raceStripSlot(['title' => 'M. C. Nambu Hai', 'tier' => 'G1', 'sort_order' => 2]);
    raceStripSlot(['title' => 'Mainichi Okan', 'tier' => 'G2', 'sort_order' => 3]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $picked->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.this_turn.turn', 13)
            ->where('raceStrip.this_turn.year_label', 'Junior')
            ->where('raceStrip.this_turn.offered', 3)
            ->has('raceStrip.this_turn.recorded', 1)
            ->where('raceStrip.this_turn.recorded.0.title', 'Kyoto Daishoten')
            ->where('raceStrip.this_turn.recorded.0.status', 'Entered')
            // The option list stays on the screen that owns the decision.
            ->where('raceStrip.links.decision_url', route('runs.races.decision', $run)));
});

it('says so when the calendar carries no race at the turn being decided', function (): void {
    $run = raceStripRun(turns: 12);
    raceStripSlot(['title' => 'A race at some other turn', 'turn' => 20, 'month' => 10, 'half' => 'Early']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.this_turn', null)
            ->where('raceStrip.empty.this_turn', fn (string $line): bool => str_contains($line, 'No race')
                && str_contains($line, 'turn 13')
                // It names a door as well as the gap, and it is not a bare "no data".
                && str_contains($line, 'Race Decision')));
});

it('lists ahead only the obligations still to come and not yet recorded', function (): void {
    $run = raceStripRun(turns: 12);
    // Turn 13 of Junior is the turn being decided, so anything at or before it is not ahead.
    raceStripSlot(['title' => 'Junior Make Debut', 'turn' => 5, 'month' => 3, 'half' => 'Early', 'is_mandatory' => true, 'sort_order' => 0]);
    $entered = raceStripSlot(['title' => 'URA Finals Qualifier', 'year' => RaceCatalogSlot::YEAR_FINALE, 'turn' => null, 'month' => null, 'half' => null, 'is_mandatory' => true, 'tier' => 'G1', 'sort_order' => 0]);
    raceStripSlot(['title' => 'A race nobody has to run', 'year' => 2, 'turn' => 1, 'month' => 1, 'half' => 'Early', 'is_mandatory' => false]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $entered->id,
        'status' => RaceEntryStatus::Entered,
    ]);
    raceStripSlot(['title' => 'URA Finals Final', 'year' => RaceCatalogSlot::YEAR_FINALE, 'turn' => null, 'month' => null, 'half' => null, 'is_mandatory' => true, 'tier' => 'G1', 'sort_order' => 1]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('raceStrip.ahead', 1)
            ->where('raceStrip.ahead.0.title', 'URA Finals Final')
            ->where('raceStrip.ahead.0.year_label', 'Finale')
            ->where('raceStrip.ahead.0.turn', null)
            // No goal flag: the tool holds no per-trainee objective data to set one from.
            ->missing('raceStrip.ahead.0.goal')
            // No deadline state either: the obligation the Trainer already entered is in Run, and a
            // region that shows only what is still to come has no state to report on it.
            ->missing('raceStrip.ahead.0.state')
            ->where('raceStrip.absences.goal', fn (string $line): bool => str_contains($line, 'goal')
                && str_contains($line, 'KI-34')));
});

it('keeps one race in exactly one region', function (): void {
    $run = raceStripRun(turns: 12);
    $later = raceStripSlot(['title' => 'URA Finals Qualifier', 'year' => 2, 'turn' => 1, 'month' => 1, 'half' => 'Early', 'is_mandatory' => true]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $later->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 3,
        'turn_entry_id' => raceStripTurn($run, 12)->id,
    ]);

    // The one mandatory race ahead of this run is also the one it has already run, so it belongs to
    // Run alone. It is not a turn being decided either, which is why This Turn is absent.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('raceStrip.run', 1)
            ->where('raceStrip.run.0.id', $later->id)
            ->has('raceStrip.ahead', 0)
            ->where('raceStrip.this_turn', null));
});

it('carries no prediction, no readiness and no derived distance band', function (): void {
    $run = raceStripRun(turns: 12);
    $slot = raceStripSlot();
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 2,
        'turn_entry_id' => raceStripTurn($run, 12)->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The strip is orientation and history. The race facts, the condition applicability and
            // the fan-gap arithmetic are D10's, and none of them travels here.
            ->has('raceStrip.run', 1)
            ->missing('raceStrip.run.0.distance')
            ->missing('raceStrip.run.0.distance_band')
            ->missing('raceStrip.run.0.surface')
            ->missing('raceStrip.run.0.fans_needed')
            ->missing('raceStrip.readiness')
            ->missing('raceStrip.band')
            ->missing('raceStrip.win_probability')
            ->missing('raceStrip.score')
            ->missing('raceStrip.confidence')
            ->missing('raceStrip.risk')
            // And no string that reaches the page prints a percentage sign.
            ->where('raceStrip', fn ($strip): bool => ! str_contains(json_encode($strip, JSON_THROW_ON_ERROR), '%')));
});

it('shows no strip for a scenario the matrix closes the calendar on, and prints no catalogue', function (): void {
    $run = raceStripRun(turns: 12, scenario: 'trackblazer');
    raceStripSlot();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.shown', false)
            ->has('raceStrip.run', 0)
            ->has('raceStrip.ahead', 0)
            ->where('raceStrip.this_turn', null)
            // One sentence for the whole region, naming the matrix rather than a scenario.
            ->where('raceStrip.notice', fn (string $line): bool => str_contains($line, 'composes no race calendar'))
            // The 0.1.0 catalogue bundle is gone from the page: the strip replaces it rather than
            // sitting beside it, so no dead payload travels to the browser.
            ->missing('calendar'));
});

it('places nothing ahead of a run that has no turn being decided', function (): void {
    $run = raceStripRun();
    raceStripSlot(['title' => 'URA Finals Final', 'year' => RaceCatalogSlot::YEAR_FINALE, 'turn' => null, 'month' => null, 'half' => null, 'is_mandatory' => true]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.shown', true)
            ->where('raceStrip.this_turn', null)
            ->has('raceStrip.ahead', 0)
            ->where('raceStrip.empty.run', fn (string $line): bool => str_contains($line, 'No race is recorded'))
            ->where('raceStrip.empty.this_turn', fn (string $line): bool => str_contains($line, 'no turn being decided'))
            ->where('raceStrip.empty.ahead', fn (string $line): bool => str_contains($line, 'no turn being decided')));
});

it('never renders a race the calendar does not place at this run\'s scenario', function (): void {
    $run = raceStripRun(turns: 12, scenario: 'unity_cup');
    raceStripSlot(['title' => 'Twinkle Star Climax', 'scenario_key' => 'trackblazer', 'is_mandatory' => true, 'sort_order' => 0]);
    raceStripSlot(['title' => 'URA Finals Final', 'scenario_key' => 'unity_cup', 'year' => RaceCatalogSlot::YEAR_FINALE, 'turn' => null, 'month' => null, 'half' => null, 'is_mandatory' => true, 'sort_order' => 0]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('raceStrip.ahead', 1)
            ->where('raceStrip.ahead.0.title', 'URA Finals Final'));
});

it('derives each recorded row\'s state from the entry\'s own status, one row at a time', function (RaceEntryStatus $status): void {
    $run = raceStripRun(turns: 12);
    $slot = raceStripSlot(['title' => 'Oka Sho', 'turn' => 6, 'month' => 3, 'half' => 'Early']);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => $status,
        'turn_entry_id' => raceStripTurn($run, 6)->id,
    ]);

    // The calendar collapses every recorded entry onto one `past` cell (`RaceCalendarTest`); the strip
    // keeps the store's own token and its Trainer-facing word per row, so entered, completed, skipped
    // and not-offered stay apart rather than sharing a state.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('raceStrip.run', 1)
            ->where('raceStrip.run.0.status', $status->value)
            ->where('raceStrip.run.0.status_label', $status->label()));
})->with(RaceEntryStatus::cases());

it('marks a race the Trainer typed in by its missing catalogue row, as the calendar kept it manual', function (): void {
    $run = raceStripRun(turns: 12);
    $seeded = raceStripSlot(['title' => 'A catalogue race', 'turn' => 5, 'month' => 3, 'half' => 'Early']);
    $typed = raceStripFreeSlot($run, 'A race the Trainer typed in');
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $seeded->id,
        'status' => RaceEntryStatus::Completed,
        'turn_entry_id' => raceStripTurn($run, 5)->id,
    ]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $typed->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    // The catalogue row carries its own id; a typed race has no catalogue row by definition, so the
    // strip reads the same provenance the calendar carried as its `manual` marker.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('raceStrip.run', 2)
            ->where('raceStrip.run.0.id', $seeded->id)
            ->where('raceStrip.run.1.id', null)
            ->where('raceStrip.run.1.title', 'A race the Trainer typed in'));
});

it('counts only the catalogue row in the run\'s own career year at the turn being decided', function (): void {
    $run = raceStripRun(turns: 7);
    // Turn 8 is Junior. A row at the same turn number in Classic is a different race, and the strip
    // scopes the turn by its career year rather than by the number alone (`RaceCalendarYearTabsTest`).
    raceStripSlot(['title' => 'Junior Early February', 'year' => RaceCatalogSlot::YEAR_JUNIOR, 'turn' => 8, 'month' => 2, 'half' => 'Early']);
    raceStripSlot(['title' => 'Classic Early February', 'year' => RaceCatalogSlot::YEAR_CLASSIC, 'turn' => 8, 'month' => 2, 'half' => 'Early']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.this_turn.turn', 8)
            ->where('raceStrip.this_turn.year_label', 'Junior')
            ->where('raceStrip.this_turn.offered', 1));
});

it('names the turn being decided in the year it falls in, which is not always the year just logged', function (): void {
    // Junior Late December is turn 24, so the turn being decided is Classic Early January. The year
    // travels with the position, so the strip cannot label the turn with the year the run just left.
    $run = raceStripRun(turns: 24);
    raceStripSlot(['title' => 'Classic Opener', 'year' => RaceCatalogSlot::YEAR_CLASSIC, 'turn' => 1, 'month' => 1, 'half' => 'Early']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('raceStrip.this_turn.turn', 1)
            ->where('raceStrip.this_turn.year_label', 'Classic'));
});

it('introduces no motion, no percentage and no distance band in the component itself', function (): void {
    // The browser cannot prove the absence of a transition on an element that never has one, so the
    // check is on the source: a future edit that adds motion or a percentage trips this (the same
    // reading `CareerRaceDecisionTest` takes of `RaceCard.vue`).
    $source = file_get_contents(resource_path('js/components/career/RunRaceStrip.vue'));

    expect($source)
        ->not->toContain('transition')
        ->not->toContain('animate')
        ->not->toContain('motion-')
        ->not->toContain('%');
});
