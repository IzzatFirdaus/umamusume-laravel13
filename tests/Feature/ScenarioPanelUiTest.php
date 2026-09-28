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

/*
 * Slice 8 T2-T5 on the surface: the Unity Cup and Trackblazer panels, the race writer,
 * and the KI-15 disclosure line.
 *
 * Two rules decide most assertions here. A panel that has nothing to say renders the
 * reason and not a number (D-220), and a derived figure is labelled as derived with its
 * cause named beside it (D-256, D-222). Neither state may collapse into a zero, because a
 * zero is a claim.
 */
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
        'event_type' => constant(TurnEventType::class.'::'.$type),
        'source_name' => $source,
        'deltas' => $deltas,
    ]);
}

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

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toContain('Rank not recorded')
        ->and($html)->not->toMatch('/facility level 0/');
});

it('names the rank the facility level was derived from', function (): void {
    $run = uiRun('unity_cup');
    recorded($run, 6, 'Scenario', TeamRankPayload::make('S')->toArray());

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toMatch('/facility level 5\s*derived from the rank letter/i');
});

it('keeps S+ off the ladder without inventing a level for it', function (): void {
    $run = uiRun('unity_cup');
    recorded($run, 6, 'Scenario', TeamRankPayload::make('S+')->toArray());

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toContain('grants a second hint, no higher facility')
        ->and($run->facilityLevel('S+'))->toBeNull();
});

it('shows every burst state with a word, so no teammate is read by colour alone', function (): void {
    $run = uiRun('unity_cup');

    foreach (SpiritBurstState::cases() as $index => $state) {
        recorded($run, $index + 1, 'Scenario', SpiritBurstPayload::make('mate_'.$index, $state)->toArray());
    }

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    foreach (SpiritBurstState::cases() as $state) {
        expect(strip_tags($html))->toContain($state->value);
    }

    // The state name is in the markup, not only in a class: D-12.
    expect(substr_count($html, 'ExtremeSpent'))->toBeGreaterThanOrEqual(1);
});

it('renders the fatigue chip as a word and the source pointer', function (): void {
    $run = uiRun('trackblazer');
    recorded($run, 8, 'Scenario', RaceFatiguePayload::make(3)->toArray());

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toMatch('/3\s+races\s+in\s+a\s+row,\s+a\s+mood\s+downgrade\s+is\s+likely/i');

    // D-230: the bands are published as percentages by one source only, so the tool that
    // shows a word must not also show the number it refuses to corroborate.
    expect($html)->not->toMatch('/60-90|0-33|0-15/');
});

it('refuses a fatigue reading below one race', function (): void {
    $run = uiRun('trackblazer');

    $this->expectException(InvalidArgumentException::class);
    RaceFatiguePayload::make(0);
});

it('says when no consecutive-race reading exists instead of showing zero', function (): void {
    $run = uiRun('trackblazer');

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toContain('no consecutive-race reading recorded');
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
    $ura = strip_tags($this->get('/training-runs/'.uiRun('ura_finale')->id)->getContent());
    $cup = strip_tags($this->get('/training-runs/'.uiRun('unity_cup')->id)->getContent());

    expect($ura)->not->toMatch('/Circles read/i')
        ->and($cup)->toMatch('/Circles read/i');
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

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toMatch('/Aim for at least 3 circles\s*as a margin, not a win condition/i')
        ->and($html)->toMatch('/below the margin/i');
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

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());
    expect($html)->toMatch('/Derived\s+from\s+1\s+entered\s+race\s+name/i');
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

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toMatch('/Buying at a higher rank overwrites the lower one/i');
});

it('discloses which grade point track is being shown', function (): void {
    $run = uiRun('trackblazer');

    $html = strip_tags($this->get('/training-runs/'.$run->id)->assertOk()->getContent());

    expect($html)->toMatch('/Targets shown are the standard track/i')
        ->and($html)->toMatch('/KI-15 carries the disagreement/i');
});
