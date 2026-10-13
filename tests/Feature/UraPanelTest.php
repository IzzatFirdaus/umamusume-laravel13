<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TraineeGoal;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-014, the URA panel (plan §9 E2, `SCR-CAR-020`).
 *
 * The panel is E1's first registered renderer, so these cases assert the data half of the contract
 * `ScenarioPanel.vue` hands over: `objectives` as grouped rows and `alerts` as levelled sentences.
 * The registry seam itself, the rendered states and the a11y path are in
 * `tests/browser/ura-panel.spec.ts`.
 *
 * **Three of SCREEN-014's five components have no data in this repository**, ruled by the owner on
 * 2026-10-07: no table holds a trainee's per-character goals (`TrainingRun.php:599-616` says the Goal
 * pennant returns when `trainee_goals` exists), and nothing stores a Happy Meek level. Those modules
 * render as named absences here rather than as invented rows. What *is* recorded is the mandatory race
 * set, so that is what the panel actually draws.
 *
 * The alert has no window length anywhere in the corpus, so it is the derivable form the owner ruled:
 * the calendar's own turn has arrived and the run records nothing for it. Copy says "due and not
 * recorded", never "inside the deadline window".
 */

/**
 * @param  int<0, max>  $turns  how many turns the run has already recorded
 */
function uraRun(string $scenario = 'ura_finale', int $turns = 0): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    return $run;
}

/**
 * The two mandatory rows the calendar really holds: the debut on a numbered turn, and the
 * scenario's own finale, which the catalogue records with no turn at all.
 *
 * @return array{debut: RaceCatalogSlot, final: RaceCatalogSlot}
 */
function uraSlots(string $scenario = 'ura_finale'): array
{
    return [
        'debut' => RaceCatalogSlot::factory()->debut()->create(),
        'final' => RaceCatalogSlot::factory()->scenarioFinal($scenario)->create(),
    ];
}

it('composes the career goals flag from the matrix, on where the scenario keeps race goals and off where it does not', function (): void {
    $this->get(route('runs.cockpit', uraRun()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.panels.career_goals.label', 'Career goals')
            ->where('scenario.panels.career_goals.on', true));

    // Unity Cup and Our Grand Concert keep the trainee's own objectives on their normal schedule
    // (docs/scenarios/02-unity-cup.md:155, docs/scenarios/07-grand-concert.md:92-95). Trackblazer
    // replaces race goals with Grade Point deadlines and stays off. Declared in the matrix, never by a
    // scenario name in a component.
    foreach (['unity_cup' => true, 'our_grand_concert' => true, 'trackblazer' => false] as $key => $on) {
        $this->get(route('runs.cockpit', uraRun($key)))
            ->assertInertia(fn (Assert $page) => $page->where('scenario.panels.career_goals.on', $on));
    }
});

it('draws the recorded mandatory races as objective rows, each with a state and its calendar position', function (): void {
    $run = uraRun();
    uraSlots();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $races = collect($all['objectives'])->keyBy('group')->get('Mandatory races')['rows'];
            $debut = collect($races)->firstWhere('label', 'Junior Make Debut');
            $final = collect($races)->firstWhere('label', 'URA Finals Final');

            // The debut carries the turn the catalogue records for it; the finale records none, so
            // its turn is null and the panel renders N/A rather than inventing a number.
            expect($debut)->not->toBeNull()
                ->and($debut['state'])->toBe('upcoming')
                ->and($debut['year_label'])->toBe('Junior')
                ->and($debut['turn'])->toBe(12)
                ->and($debut['race_url'])->not->toBeNull()
                ->and($final['turn'])->toBeNull()
                ->and($final['state'])->toBe('upcoming')
                ->and($final['absence'])->not->toBeNull();

            return true;
        }));
});

it('marks a mandatory race completed once the run records it, and gives it no alert', function (): void {
    $run = uraRun(turns: 12);
    $slots = uraSlots();

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 12)->first()->id,
        'race_catalog_slot_id' => $slots['debut']->id,
        'status' => 'Completed',
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $debut = collect(collect($all['objectives'])->keyBy('group')->get('Mandatory races')['rows'])
                ->firstWhere('label', 'Junior Make Debut');

            expect($debut['state'])->toBe('completed')
                // A recorded race needs no door back to the picker, and the turn has arrived but was
                // recorded, so there is nothing to warn about.
                ->and($debut['race_url'])->toBeNull()
                ->and(collect($all['alerts'])->first(
                    fn (array $alert): bool => str_contains((string) $alert['text'], 'Junior Make Debut')
                ))->toBeNull();

            return true;
        }));
});

it('raises a Critical alert when a mandatory race turn has arrived and nothing is recorded for it', function (): void {
    // Eleven turns recorded, so the next turn to play is 12: the debut's own turn has arrived.
    uraSlots();

    $this->get(route('runs.cockpit', uraRun(turns: 11)))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $alert = collect($all['alerts'])->first(
                fn (array $row): bool => str_contains((string) $row['text'], 'Junior Make Debut')
            );
            $debut = collect(collect($all['objectives'])->keyBy('group')->get('Mandatory races')['rows'])
                ->firstWhere('label', 'Junior Make Debut');

            expect($alert)->not->toBeNull()
                ->and($alert['level'])->toBe('Critical')
                // The ruled wording: due and not recorded. No window length is claimed anywhere.
                ->and($alert['text'])->toContain('due and not recorded')
                ->and($alert['detail'])->not->toBeNull()
                // The turn is now, not past, so the row reads current rather than missed.
                ->and($debut['state'])->toBe('current');

            return true;
        }));

    // Thirteen turns: turn 12 has gone by with nothing logged, which is the other state. The same
    // two calendar rows serve, because the shared-grain index refuses a second debut.
    $this->get(route('runs.cockpit', uraRun(turns: 13)))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $rows = collect(collect($all['objectives'])->keyBy('group')->get('Mandatory races')['rows']);

            expect($rows->firstWhere('label', 'Junior Make Debut')['state'])->toBe('missed')
                // The finale block has no turn of its own, so it can never be missed and raises no
                // alert: the alert this screen may make is about a recorded turn that has arrived.
                ->and($rows->firstWhere('label', 'URA Finals Final')['state'])->toBe('upcoming')
                ->and(collect($all['alerts'])->pluck('text'))
                ->not->toContain('URA Finals Final is due and not recorded');

            return true;
        }));
});

it('renders the trainee goals group from the sourced ladder instead of naming an absent module', function (): void {
    $run = uraRun();
    $slot = RaceCatalogSlot::factory()->create(['year' => 2, 'month' => 5, 'half' => 'Late', 'title' => 'Tokyo Yushun (Japanese Derby)']);
    TraineeGoal::factory()->create([
        'umamusume_id' => $run->umamusume_id,
        'race_catalog_slot_id' => $slot->id,
        'career_year' => 2,
        'turn' => 34,
        'required_placement' => 5,
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $groups = collect($all['objectives'])->pluck('group')->all();
            $goals = collect($all['objectives'])->keyBy('group')->get('Trainee goals');

            // The three-part absence this slot used to print ("what is missing / why it matters / what
            // unblocks it") is retired by ADR-0026: the module exists. The group leads the list, because
            // the client puts the trainee's own ladder above the scenario's.
            expect($groups[0])->toBe('Trainee goals')
                ->and($goals['rows'])->toHaveCount(1)
                ->and($goals['rows'][0]['label'])->toBe('Place top 5 in Tokyo Yushun (Japanese Derby)')
                ->and($goals['rows'][0]['state'])->toBe('upcoming')
                ->and($goals['summary'])->toBe('Goals Achieved 0 of 1')
                // Nothing here may claim the old absence any more.
                ->and(json_encode($all, JSON_THROW_ON_ERROR))->not->toContain('trainee_goals does not exist');

            return true;
        }));
});

it('says this database holds no ladder, rather than leaving the group silently empty', function (): void {
    // No `trainee_goals` row exists in this case, so the honest sentence is about the database. The source
    // is not on trial here: `TraineeGoalLadderStateTest` covers the loaded-table-trainee-absent branch,
    // which is the only state that may say what the ladder states.
    $this->get(route('runs.cockpit', uraRun()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $goals = collect($all['objectives'])->keyBy('group')->get('Trainee goals');

            expect($goals['rows'])->toBe([])
                ->and($goals['empty_note'])->toContain('has been loaded into this database')
                ->and($goals['empty_note'])->not->toContain('states no objective for this trainee')
                // The generic empty line speaks of mandatory races, so a goals group that holds nothing
                // carries its own sentence rather than borrowing that claim.
                ->and($goals['empty_note'])->not->toBeNull();

            return true;
        }));
});

it('renders each Happy Meek field as an unrecorded reading with a reason, and prints no number', function (): void {
    $this->get(route('runs.cockpit', uraRun()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('scenario', function (Collection $section): bool {
            /** @var array<string, mixed> $all */
            $all = $section->all();
            $rows = collect(collect($all['objectives'])->keyBy('group')->get('Happy Meek')['rows']);

            expect($rows->pluck('label')->all())->toBe([
                'Current level',
                'Duel availability',
                'Potential reward',
                'Final-race contribution',
            ])
                // Nothing is stored, so every row is an absence and no row carries a reading a
                // Trainer could mistake for a figure the tool measured.
                ->and($rows->every(fn (array $row): bool => $row['absence'] !== null
                    && $row['state'] === null
                    && $row['turn'] === null))->toBeTrue();

            return true;
        }));
});

it('draws no objective groups at all for a scenario that leaves the flag off', function (): void {
    // E2's content is gated by the flag, never by a scenario name: a cockpit whose matrix says off
    // must not gain URA rows. Trackblazer is that scenario now - it replaces race goals with Grade
    // Point deadlines (docs/scenarios/03-trackblazer.md:14-20).
    $this->get(route('runs.cockpit', uraRun('trackblazer')))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.objectives', [])
            ->where('scenario.alerts', []));
});
