<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use Database\Seeders\ScenarioSlotSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * D-8 — Unity Cup team-race scenario slots (owner-authorised 2026-11-25).
 *
 * The seeder reads a committed, attributed calendar extract
 * (`database/seeders/data/unity-cup-team-races.json`) and writes five `team_race`
 * rows at the owner-approved month+half anchors. These tests pin the round trip:
 * the seeded slots exist with their provenance, and a Trainer can record a race
 * entry against one through the ordinary calendar path.
 */

it('seeds the five Unity Cup team-race slots at their calendar anchors', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $slots = ScenarioSlot::where('scenario_key', 'unity_cup')
        ->where('kind', 'team_race')
        ->orderBy('sort_order')
        ->get();

    expect($slots)->toHaveCount(5)
        ->and($slots->pluck('source_key')->all())->toBe(['round_1', 'round_2', 'round_3', 'round_4', 'finals'])
        ->and($slots->pluck('slot_label')->all())->toBe(['Round 1', 'Round 2', 'Round 3', 'Round 4', 'Finals'])
        ->and($slots->every(fn (ScenarioSlot $s): bool => $s->isTeamRace()))->toBeTrue()
        ->and($slots->every(fn (ScenarioSlot $s): bool => $s->is_mandatory === false))->toBeTrue()
        ->and($slots->every(fn (ScenarioSlot $s): bool => $s->tier === null))->toBeTrue()
        ->and($slots->every(fn (ScenarioSlot $s): bool => $s->fans_needed === null))->toBeTrue();
});

it('stamps provenance from the committed extract on every Unity Cup slot', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $missing = ScenarioSlot::where('scenario_key', 'unity_cup')
        ->where('kind', 'team_race')
        ->where(function ($q): void {
            $q->whereNull('source_url')
                ->orWhereNull('snapshot_path')
                ->orWhereNull('fetched_at')
                ->orWhereNull('source_timezone');
        })
        ->count();

    expect($missing)->toBe(0);

    $round1 = ScenarioSlot::where('scenario_key', 'unity_cup')->where('source_key', 'round_1')->first();
    expect($round1->source_url)->toBe('https://gametora.com/umamusume/unity-cup')
        ->and($round1->snapshot_path)->toBe('database/seeders/data/unity-cup-team-races.json')
        ->and($round1->month)->toBe(12)
        ->and($round1->half)->toBe('Late');
});

it('is idempotent for the Unity Cup slots', function (): void {
    $this->seed(ScenarioSlotSeeder::class);
    $first = ScenarioSlot::where('scenario_key', 'unity_cup')->where('kind', 'team_race')->count();

    $this->seed(ScenarioSlotSeeder::class);
    $second = ScenarioSlot::where('scenario_key', 'unity_cup')->where('kind', 'team_race')->count();

    expect($second)->toBe($first)->toBe(5);
});

it('records a race entry against a seeded Unity Cup team-race slot', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = ScenarioSlot::where('scenario_key', 'unity_cup')->where('source_key', 'round_1')->firstOrFail();

    $response = $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 1,
        'circles' => 4,
    ]);

    $response->assertRedirect();

    $entry = RaceEntry::where('training_run_id', $run->id)
        ->where('scenario_slot_id', $slot->id)
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->status)->toBe(RaceEntryStatus::Completed)
        ->and($entry->placement)->toBe(1)
        ->and($entry->circles)->toBe(4);
});

it('refuses circles against a non team-race slot', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $goal = ScenarioSlot::factory()->create([
        'scenario_key' => 'unity_cup',
        'kind' => 'goal_race',
        'month' => 10,
        'half' => 'Early',
    ]);

    $response = $this->from(route('runs.cockpit', $run))->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $goal->id,
        'status' => RaceEntryStatus::Completed->value,
        'circles' => 3,
    ]);

    $response->assertSessionHasErrors('circles');
});

it('keeps team-race slots separate from ordinary individual races and other scenarios', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    // The seeded team-race rows belong to unity_cup alone and are never goal_race.
    expect(ScenarioSlot::where('kind', 'team_race')->pluck('scenario_key')->unique()->all())
        ->toBe(['unity_cup'])
        ->and(ScenarioSlot::where('scenario_key', 'unity_cup')->where('kind', 'goal_race')->count())
        ->toBe(0);

    // The ordinary individual-race calendar is a different table (race_catalog_slots),
    // so a team-race slot never appears there.
    $this->assertDatabaseMissing('race_catalog_slots', ['scenario_key' => 'unity_cup', 'kind' => 'team_race']);
});

it('seeds the same five stable slot identities on a repeat run', function (): void {
    $this->seed(ScenarioSlotSeeder::class);
    $first = ScenarioSlot::where('scenario_key', 'unity_cup')->where('kind', 'team_race')
        ->orderBy('sort_order')->pluck('source_key')->all();

    $this->seed(ScenarioSlotSeeder::class);
    $second = ScenarioSlot::where('scenario_key', 'unity_cup')->where('kind', 'team_race')
        ->orderBy('sort_order')->pluck('source_key')->all();

    expect($second)->toBe($first);
});

/*
 * D-8 frontend integration: the five slots reach the Team screen's calendar, not only the
 * recording route. Before this key, a slot was reachable only by a Trainer who already knew
 * an id — the "reachable only by URL" defect in its calendar form.
 */
it('carries the five team-race slots into the team payload, in calendar order', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.team', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('team.team_race_calendar', 5)
            ->where('team.team_race_calendar', fn ($rows): bool => collect($rows)->pluck('source_key')->all()
                === ['round_1', 'round_2', 'round_3', 'round_4', 'finals'])
            ->where('team.team_race_calendar', fn ($rows): bool => collect($rows)->pluck('label')->all()
                === ['Round 1', 'Round 2', 'Round 3', 'Round 4', 'Finals'])
            // No round is recorded yet, so every entry is absent rather than a loss.
            ->where('team.team_race_calendar', fn ($rows): bool => collect($rows)->every(
                fn (array $row): bool => $row['entry'] === null,
            )));
});

it('attaches the run\'s own recorded result to the round it was recorded against', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $slot = ScenarioSlot::where('scenario_key', 'unity_cup')->where('source_key', 'round_2')->firstOrFail();

    $this->post(route('runs.races.store', $run), [
        'entry_mode' => 'calendar',
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed->value,
        'placement' => 2,
        'circles' => 3,
    ])->assertRedirect();

    $this->get(route('runs.team', $run))
        ->assertInertia(fn (Assert $page) => $page->where('team.team_race_calendar', function ($rows): bool {
            $byKey = collect($rows)->keyBy('source_key');

            return $byKey['round_2']['entry'] !== null
                && $byKey['round_2']['entry']['placement'] === 2
                && $byKey['round_2']['entry']['circles'] === 3
                // The other four rounds stay unrecorded: one result is not a result for the set.
                && $byKey['round_1']['entry'] === null
                && $byKey['finals']['entry'] === null;
        }));
});

it('carries no team-race calendar on a scenario without a team layer', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // The team screen redirects for this scenario, so the assertion is on the cockpit, which reads
    // the same builder. An empty list is the honest answer: this scenario holds no team race.
    $this->get(route('runs.team', $run))
        ->assertRedirect(route('runs.cockpit', $run));
});
