<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Enums\SpiritBurstState;
use App\Enums\TurnEventType;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use App\Models\TurnEvents\RaceFatiguePayload;
use App\Models\TurnEvents\SpiritBurstPayload;
use App\Models\TurnEvents\TeamRankPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Slice 8 T2-T5 on the surface: the Unity Cup and Trackblazer panels, the race writer,
 * and the KI-15 disclosure line.
 *
 * Two rules decide most assertions here. A panel that has nothing to say renders the
 * reason and not a number (D-220), and a derived figure is labelled as derived with its
 * cause named beside it (D-256, D-222). Neither state may collapse into a zero, because a
 * zero is a claim.
 */
it('records a team rank as a payload and rejects a letter the league does not have', function (): void {
    $run = uiRun('unity_cup');
    recorded($run, 4, 'Scenario', TeamRankPayload::make('A')->toArray());

    expect($run->fresh()->latestTeamRank()?->rank)->toBe('A')
        ->and($run->facilityLevel('A'))->toBe(4);

    $this->expectException(InvalidArgumentException::class);
    recorded($run, 5, 'Scenario', ['rank' => 'SS']);
});

it('renders the rank gauge as unrecorded before anything is entered', function (): void {
    $run = uiRun('unity_cup');

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('teamRank.enabled', true)
            ->whereNull('teamRank.current'));
});

it('names the rank the facility level was derived from', function (): void {
    $run = uiRun('unity_cup');
    recorded($run, 6, 'Scenario', TeamRankPayload::make('S')->toArray());

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('teamRank.enabled', true)
            ->where('teamRank.current.rank', 'S')
            ->where('teamRank.current.level', 5));
});

it('keeps S+ off the ladder without inventing a level for it', function (): void {
    $run = uiRun('unity_cup');
    recorded($run, 6, 'Scenario', TeamRankPayload::make('S+')->toArray());

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('teamRank.enabled', true)
            ->where('teamRank.current.rank', 'S+')
            ->whereNull('teamRank.current.level'));

    expect($run->facilityLevel('S+'))->toBeNull();
});

it('shows every burst state with a word, so no teammate is read by colour alone', function (): void {
    $run = uiRun('unity_cup');

    foreach (SpiritBurstState::cases() as $index => $state) {
        recorded($run, $index + 1, 'Scenario', SpiritBurstPayload::make('mate_'.$index, $state)->toArray());
    }

    // Each roster row carries the backing `state` value and the human `stateLabel`. The three
    // states whose value differs from their label are the leak detector: a regression that printed
    // `->value` as the label would fail because the prop would carry the backing string as stateLabel.
    $ranks = SpiritBurstState::cases();

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($ranks): void {
            $page->component('Runs/Show')
                ->where('spiritBursts.enabled', true)
                ->has('spiritBursts.roster', count($ranks));

            foreach ($ranks as $state) {
                $page->where('spiritBursts.roster', function ($roster) use ($state): bool {
                    $rows = is_array($roster) ? $roster : $roster->toArray();

                    foreach ($rows as $row) {
                        if ($row['stateLabel'] === $state->label() && $row['state'] === $state->value) {
                            return true;
                        }
                    }

                    return false;
                });
            }
        });
});

it('renders the fatigue chip as a word and the source pointer', function (): void {
    $run = uiRun('trackblazer');
    recorded($run, 8, 'Scenario', RaceFatiguePayload::make(3)->toArray());

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('fatigue.enabled', true)
            ->where('fatigue.consecutiveRaces', 3)
            ->where('fatigue.riskWord', 'likely'));
});

it('refuses a fatigue reading below one race', function (): void {
    $run = uiRun('trackblazer');

    $this->expectException(InvalidArgumentException::class);
    RaceFatiguePayload::make(0);
});

it('says when no consecutive-race reading exists instead of showing zero', function (): void {
    $run = uiRun('trackblazer');

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('fatigue.enabled', true)
            ->whereNull('fatigue.consecutiveRaces'));
});

it('writes a race entry against a slot and rejects one that is not on this calendar', function (): void {
    $run = uiRun('unity_cup');
    $slot = uiSlot('unity_cup', 'goal_race', 'April Cup', 1);
    $other = uiSlot('ura_finale', 'goal_race', 'Oka Sho', 2);

    $this->post('/training-runs/'.$run->id.'/races', [
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
    ])->assertSessionHasNoErrors();

    expect($run->raceEntries()->count())->toBe(1);

    $this->post('/training-runs/'.$run->id.'/races', [
        'scenario_slot_id' => $other->id,
        'status' => RaceEntryStatus::Completed->value,
    ])->assertSessionHasErrors('scenario_slot_id');
});

it('refuses an unknown slot id outright', function (): void {
    $run = uiRun('unity_cup');

    $this->post('/training-runs/'.$run->id.'/races', [
        'scenario_slot_id' => 987654,
        'status' => RaceEntryStatus::Entered->value,
    ])->assertSessionHasErrors('scenario_slot_id');
});

it('keeps circles off the race form where the scenario has no team race', function (): void {
    $this->get('/training-runs/'.uiRun('ura_finale')->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('racePanel.composesTeamRace', false));

    $this->get('/training-runs/'.uiRun('unity_cup')->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('racePanel.composesTeamRace', true));
});

it('states the circle margin as a margin, not as a win condition', function (): void {
    $run = uiRun('unity_cup');
    $slot = uiSlot('unity_cup', 'team_race', 'Team Race A', 3);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
        'circles' => 2,
    ]);

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('teamRace.enabled', true)
            ->where('teamRace.guidance', 3)
            ->has('teamRace.entries', 1)
            ->where('teamRace.entries.0.circles', 2)
            ->where('teamRace.entries.0.placement', 1));
});

it('derives the epithet checklist and marks what it cannot see as unverifiable', function (): void {
    $run = uiRun('trackblazer');
    $slot = uiSlot('trackblazer', 'goal_race', 'Satsuki Sho', 1);

    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $rows = collect($run->epithetProgress())->keyBy('epithet');

    expect($rows['Stunning']['state'])->toBe('open')
        ->and($rows['Stunning']['missing'])->toBe(['Japanese Derby', 'Kikuka Sho'])
        ->and($rows['Dirty Work']['state'])->toBe('unverifiable')
        ->and($rows['Lady']['state'])->toBe('open');

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('epithets.enabled', true)
            ->has('epithets.seenRaceTitles', 1));
});

it('completes a route once every named race is entered', function (): void {
    $run = uiRun('trackblazer');

    foreach (['Oka Sho', 'Japanese Oaks', 'Shuka Sho'] as $order => $title) {
        RaceEntry::create([
            'training_run_id' => $run->id,
            'scenario_slot_id' => uiSlot('trackblazer', 'goal_race', $title, $order)->id,
            'status' => RaceEntryStatus::Completed,
            'placement' => 1,
        ]);
    }

    expect(collect($run->epithetProgress())->firstWhere('epithet', 'Lady')['state'])->toBe('earned');
});

it('carries the shop overwrite warning', function (): void {
    $run = uiRun('trackblazer');

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('shop.panelsShop', true));
});

it('discloses which grade point track is being shown', function (): void {
    $run = uiRun('trackblazer');

    $this->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('gradeMeter.objectives', fn (Collection $objectives) => $objectives->isNotEmpty()));
});

it('renders the four-period ladder from the loaded rows, not from four queries', function (): void {
    $run = uiRun('trackblazer');
    $run->update(['current_objective_index' => 2]);

    foreach (['Oka Sho' => 2, 'Satsuki Sho' => 3] as $title => $period) {
        RaceEntry::create([
            'training_run_id' => $run->id,
            'scenario_slot_id' => uiSlot('trackblazer', 'goal_race', $title, $period)->id,
            'status' => RaceEntryStatus::Completed,
            'placement' => 1,
            'objective_index' => $period,
        ]);
    }

    // The shape show() uses: the rows and their slots arrive in the page load, so the
    // ladder that reads them must not ask the database anything at all.
    $run->load(['raceEntries.scenarioSlot', 'turnEvents']);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $periods = $run->gradePeriods();
    $queries = count(DB::getQueryLog());

    expect($periods)->toHaveCount(4)
        ->and($periods[1]['earned'])->toBe(100)
        ->and($periods[2]['earned'])->toBe(100)
        ->and($queries)->toBe(0, 'gradePeriods() issued '.$queries.' queries against an already-loaded run');
});

function uiRun(string $scenario): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => $scenario]);
}

function uiSlot(string $scenario, string $kind, string $title, int $order = 0): ScenarioSlot
{
    // month and half are unique per (scenario, kind), so the order walks the calendar
    // instead of stacking every test slot on the same date.
    return ScenarioSlot::factory()->create([
        'scenario_key' => $scenario,
        'kind' => $kind,
        'title' => $title,
        'tier' => 'G1',
        'month' => ($order % 12) + 1,
        'half' => $order % 2 === 0 ? 'Early' : 'Late',
        'sort_order' => $order,
    ]);
}

function recorded(TrainingRun $run, int $turn, string $type, array $deltas, string $source = 'Unity Training'): TurnEvent
{
    return TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'event_type' => TurnEventType::from($type),
        'source_name' => $source,
        'deltas' => $deltas,
    ]);
}
