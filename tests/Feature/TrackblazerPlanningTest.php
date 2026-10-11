<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\TurnEvents\RaceFatiguePayload;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * G5, the Trackblazer planning and race surfaces (plan §6, §10 and §17; `SCR-CAR-022`'s panel).
 *
 * Three nested sections travel inside `scenario.grade`, which is why `scenarioSectionKeys()` does
 * not move: the top-level shape is the E4 contract ScenarioPanelTest pins, and a planning view of
 * the grade ladder belongs to the ladder's own section (gate G-33 reads the matrix, not a name).
 *
 * The provenance each row earns is the point of these tests, not their by-product:
 *
 *  - `needed` is a published figure, so `confirmed`, and the standard track only (KI-15);
 *  - `plan` and the floor are arithmetic on entered rows plus config tables, so `calculated`;
 *  - a race the published grade table does not price is counted out and said, never guessed at;
 *  - the fatigue chain is the Trainer's entered `RaceFatiguePayload`, because
 *    `TrainingRun::consecutiveRaceCount()` refuses to derive it (D-270);
 *  - an absent figure carries a visible reason and no badge, which is the owner's ruling of 2026-10-09.
 *
 * Expected numbers are hard-coded from the published tables rather than recomputed here: a test that
 * re-runs the implementation's own arithmetic proves nothing about it. G1 100 / G2 80 and the
 * 60% / 40% modifiers are the figures the config comments cite.
 */

function g5Run(array $state = []): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => 'trackblazer',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
        ...$state,
    ]);
}

/**
 * A catalogue row on a real career turn. `turn` is the turn inside the career year, the same
 * reading `RaceCatalogSlot::isAtOrBeforeTurn()` orders against.
 */
function g5Slot(TrainingRun $run, int $year, int $turn, string $title, ?string $tier): RaceCatalogSlot
{
    return RaceCatalogSlot::factory()->create([
        'scenario_key' => 'trackblazer',
        'year' => $year,
        'turn' => $turn,
        'title' => $title,
        'tier' => $tier,
        'distance' => 1600,
        'distance_band' => 'Mile',
        'surface' => 'Turf',
        'slot_label' => 'Early April',
        'sort_order' => $turn,
    ]);
}

function g5Entry(TrainingRun $run, RaceCatalogSlot $slot, RaceEntryStatus $status, ?int $objective = null): RaceEntry
{
    return RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slot->id,
        'status' => $status,
        'objective_index' => $objective,
    ]);
}

/** Classic year, standing at turn 7: six Junior turns plus the six logged Classic turns. */
function g5LoggedIn(TrainingRun $run, int $throughTurn = 30): void
{
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => $throughTurn,
        'fans' => 41200,
    ]);
}

it('prints the three checkpoint periods against the standard denominator only', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('scenario.grade.planning.periods', 3)
            ->where('scenario.grade.planning.periods.0.name', 'End of Junior Year')
            // 60 / 300 / 300 from config's `standard` track. The dirt-leaning and limited-turf-range
            // tracks the matrix also carries are not printed, and each row says why.
            ->where('scenario.grade.planning.periods.0.needed', '60')
            ->where('scenario.grade.planning.periods.1.needed', '300')
            ->where('scenario.grade.planning.periods.2.needed', '300')
            ->where('scenario.grade.planning.periods.0.needed_badge', 'confirmed')
            ->where('scenario.grade.planning.periods.0.needed_title', fn (string $title): bool => str_contains($title, 'standard')));
});

it('marks the period the Trainer reports as live and no other', function (): void {
    $run = g5Run(['current_objective_index' => 3]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.planning.periods.0.is_current', false)
            ->where('scenario.grade.planning.periods.1.is_current', true)
            ->where('scenario.grade.planning.periods.2.is_current', false));
});

it('sums the plan from entered races only, not from races already run', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $g1 = g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, 20, 'Junior Mile Cup', 'G1');
    $g2 = g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, 22, 'Junior Turf Trial', 'G2');
    $done = g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, 18, 'Junior Sprint Trial', 'G3');

    g5Entry($run, $g1, RaceEntryStatus::Entered, 2);
    g5Entry($run, $g2, RaceEntryStatus::Entered, 2);
    g5Entry($run, $done, RaceEntryStatus::Completed, 2);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // A G1 win pays 100 and a G2 win 80; the completed G3 is history, not plan.
            ->where('scenario.grade.planning.periods.0.plan', '180')
            ->where('scenario.grade.planning.periods.0.plan_badge', 'calculated'));
});

it('counts out an entered race the published grade table does not price and says so', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $priced = g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, 20, 'Junior Mile Cup', 'G1');
    $unpriced = g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, 21, 'Junior Exhibition', 'EX');

    g5Entry($run, $priced, RaceEntryStatus::Entered, 2);
    g5Entry($run, $unpriced, RaceEntryStatus::Entered, 2);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // `grade_point_by_grade` holds G1..Pre-OP. An EX row is not priced by it, and
            // guessing a value for it would print a figure no source pays.
            ->where('scenario.grade.planning.periods.0.plan', '100')
            ->where('scenario.grade.planning.periods.0.uncounted', 1)
            ->where('scenario.grade.planning.periods.0.uncounted_title', fn (string $title): bool => str_contains($title, 'no published Grade Point value')));
});

it('derives the floor from the published placement modifiers, at both placements it names', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    foreach ([['Junior Mile Cup', 'G1', 20], ['Junior Turf Trial', 'G2', 22]] as [$title, $tier, $turn]) {
        g5Entry($run, g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, $turn, $title, $tier), RaceEntryStatus::Entered, 2);
    }

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // A plan of 180 pays 108 at 2nd (60%) and 72 at 3rd (40%).
            ->where('scenario.grade.planning.periods.0.floor_second', '108')
            ->where('scenario.grade.planning.periods.0.floor_third', '72')
            ->where('scenario.grade.planning.periods.0.floor_badge', 'calculated')
            // The floor is a lower bound at a stated placement. It is never worded as an outcome.
            ->where('scenario.grade.planning.periods.0.floor_title', fn (string $title): bool => ! str_contains(strtolower($title), 'projected')
                && str_contains($title, 'uma.guide')));
});

it('flags a run of races with no rest turn between them', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    foreach ([12, 13, 14] as $turn) {
        g5Entry($run, g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, $turn, "Junior Race {$turn}", 'G3'), RaceEntryStatus::Entered, 2);
    }

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.planning.flags', fn (Collection $flags): bool => $flags
                ->first(fn (array $row): bool => $row['kind'] === 'race_density') !== null
                && str_contains(json_encode($flags->first(fn (array $row): bool => $row['kind'] === 'race_density')), '3 consecutive')));
});

it('keeps the density flag off a plan that rests between races', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    foreach ([12, 14] as $turn) {
        g5Entry($run, g5Slot($run, RaceCatalogSlot::YEAR_JUNIOR, $turn, "Junior Race {$turn}", 'G3'), RaceEntryStatus::Entered, 2);
    }

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.planning.flags', fn (Collection $flags): bool => $flags
                ->first(fn (array $row): bool => $row['kind'] === 'race_density') === null));
});

it('states a year-end gate as published figures and names the reading it cannot take', function (): void {
    $run = g5Run(['current_objective_index' => 3]);
    g5LoggedIn($run);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.planning.flags', fn (Collection $flags): bool => $flags
                ->first(fn (array $row): bool => $row['kind'] === 'year_end_gate') !== null)
            // The gate's figures are published, the run's reading is recorded, and only the gap
            // between them is arithmetic. The Director's bond has no column at all, so that half of
            // the gate is an absence with a visible reason and no badge.
            ->where('scenario.grade.planning.year_end.gate_label', 'Classic')
            ->where('scenario.grade.planning.year_end.fans_needed', '60,000')
            ->where('scenario.grade.planning.year_end.fans_needed_badge', 'confirmed')
            ->where('scenario.grade.planning.year_end.fans_recorded', '41,200')
            ->where('scenario.grade.planning.year_end.fans_recorded_badge', 'confirmed')
            ->where('scenario.grade.planning.year_end.fans_shortfall', '18,800')
            ->where('scenario.grade.planning.year_end.fans_shortfall_badge', 'calculated')
            ->where('scenario.grade.planning.year_end.bond_recorded', null)
            ->where('scenario.grade.planning.year_end.bond_title', fn (string $title): bool => str_contains(strtolower($title), 'no column')));
});

it('prints the two publishers bond figures without settling between them', function (): void {
    $run = g5Run(['current_objective_index' => 3]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.planning.year_end.bond_figures', '19 / 31 / 51 (uma.guide) · 15 / 30 / 50 (GameWith)'));
});

it('says rival races cannot be scheduled rather than listing them as plan rows', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.planning.flags', fn (Collection $flags): bool => $flags
                ->first(fn (array $row): bool => $row['kind'] === 'rival_races') !== null
                && str_contains(json_encode($flags->first(fn (array $row): bool => $row['kind'] === 'rival_races')), 'cannot be scheduled')));
});

it('windows the calendar to the three slots ahead of the turn this run is deciding', function (): void {
    $run = g5Run(['current_objective_index' => 3]);
    g5LoggedIn($run);

    $at = g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 7, 'Classic Turn Seven', 'G2');
    $next = g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 8, 'Classic Mile Cup', 'G2');
    $entered = g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 9, 'Classic Turf Trial', 'G3');
    $later = g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 10, 'Classic Sprint Cup', 'G1');
    $beyond = g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 14, 'Classic Late Trial', 'OP');

    g5Entry($run, $entered, RaceEntryStatus::Entered, 3);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Turn 7 is the turn being decided, and the Classic Turf Trial is already entered:
            // neither is still to come, so the window is the three that are.
            ->where('scenario.grade.calendar.rows', fn (Collection $rows): bool => $rows->pluck('title')->all()
                === ['Classic Mile Cup', 'Classic Sprint Cup', 'Classic Late Trial']));
});

it('prints each upcoming race its published win value and what skipping forgoes', function (): void {
    $run = g5Run(['current_objective_index' => 3]);
    g5LoggedIn($run);

    g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 8, 'Classic Mile Cup', 'G2');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.calendar.rows.0.grade_points_win', '80')
            ->where('scenario.grade.calendar.rows.0.coins_win', '100')
            // Coins do not scale with grade, so a Pre-OP win pays the same 100.
            ->where('scenario.grade.calendar.rows.0.skip_consequence', fn (string $text): bool => str_contains($text, 'up to 80 Grade Points')
                && str_contains($text, '100 Shop Coins'))
            ->where('scenario.grade.calendar.rows.0.skip_badge', 'calculated')
            ->where('scenario.grade.calendar.rows.0.surface', 'Turf')
            ->where('scenario.grade.calendar.rows.0.distance', '1,600 m'));
});

it('names the route a skipped race leaves unclaimed', function (): void {
    $run = g5Run(['current_objective_index' => 3]);
    g5LoggedIn($run);

    g5Slot($run, RaceCatalogSlot::YEAR_CLASSIC, 8, 'Oka Sho', 'G1');

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.calendar.rows.0.skip_consequence', fn (string $text): bool => str_contains($text, 'Lady')));
});

it('shows an empty calendar window as the reason it is empty', function (): void {
    $run = g5Run(['current_objective_index' => 3]);
    g5LoggedIn($run);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.calendar.rows', fn (Collection $rows): bool => $rows->isEmpty())
            ->where('scenario.grade.calendar.absence', fn (?string $absence): bool => $absence !== null
                && str_contains(strtolower($absence), 'no race')));
});

it('reads the fatigue chain from the entered payload and never derives it', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 20,
        'event_type' => TurnEventType::Scenario,
        'source_name' => 'Race',
        'deltas' => RaceFatiguePayload::make(2)->toArray(),
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.fatigue.chain', 2)
            ->where('scenario.grade.fatigue.chain_label', '2 races')
            // The row the panel reads, printed as the publisher labels it.
            ->where('scenario.grade.fatigue.row', fn (Collection $row): bool => $row->all() === [
                'Mood Down (1+ Energy)' => '0%',
                'Mood Down (0 Energy)' => '33%',
                'Skin Outbreak (1+ Energy)' => '0%',
                'Skin Outbreak (0 Energy)' => '8%',
                '3 random stats −10' => '0%',
            ]));
});

it('leaves the chain unnumbered while the Trainer has reported none', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // `consecutiveRaceCount()` returns null by design: a race-less turn is not a rest the
            // tool was told about, so no chain is printed and no row is read.
            ->where('scenario.grade.fatigue.chain', null)
            ->where('scenario.grade.fatigue.chain_title', fn (string $title): bool => str_contains(strtolower($title), 'not entered'))
            ->where('scenario.grade.fatigue.row', null));
});

it('prints both fatigue caveats as visible copy, not as a tooltip', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.fatigue.caveats', fn (Collection $caveats): bool => $caveats->count() === 2
                && str_contains($caveats[0], '3 races')
                && str_contains($caveats[1], 'Late December')));
});

it('lists the guaranteed recovery events the publisher prints', function (): void {
    $run = g5Run(['current_objective_index' => 2]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.fatigue.recovery', fn (Collection $rows): bool => $rows->count() === 6
                && $rows->first()['when'] === 'Classic, Early February'
                && $rows->first()['effect'] === 'Mood +1')
            ->where('scenario.grade.fatigue.recovery_badge', 'confirmed'));
});

it('keeps the three surfaces nested so the pinned section shape holds', function (): void {
    $run = g5Run();

    $grade = [];
    $sectionKeys = [];

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$grade, &$sectionKeys): void {
            $section = $page->toArray()['props']['scenario'];
            $sectionKeys = array_keys($section);
            $grade = array_keys((array) $section['grade']);
        });

    expect($sectionKeys)->not->toContain('planning')->not->toContain('calendar')->not->toContain('fatigue');
    expect($grade)->toContain('planning', 'calendar', 'fatigue');
});

it('prints no projected outcome and no wiki-side gloss in the planning copy', function (): void {
    $run = g5Run(['current_objective_index' => 3]);
    g5LoggedIn($run);

    // The ban is checked against the payload the screen displays, not against comments: a
    // prediction may not reach the client's own vocabulary, and a wiki-side gloss may not
    // either. `scenario.grade` is the subtree these surfaces own.
    $displayed = '';

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$displayed): void {
            $displayed = strtolower(json_encode($page->toArray()['props']['scenario']['grade']));
        });

    foreach (['win probability', 'expected outcome', 'predict', 'projection', 'chance of win', 'strength', 'gacha', 'jewel'] as $word) {
        expect($displayed)->not->toContain($word);
    }

    foreach (['TrackblazerRoutePlanner.vue', 'TrackblazerRaceCalendar.vue', 'TrackblazerFatiguePanel.vue'] as $file) {
        $source = strtolower((string) file_get_contents(base_path('resources/js/components/scenario/'.$file)));

        foreach (['win probability', 'expected outcome', 'predicted'] as $word) {
            expect($source)->not->toContain($word, "{$word} appears in {$file}");
        }
    }
});

it('answers another scenario with no planning rows at all', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade', null));
});
