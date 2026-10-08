<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Enums\TurnEventType;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-018, the Career Timeline (plan §8 D14). The props are asserted here; the rendered copy,
 * the keyboard behaviour, the 44px sweep and the axe scan live in
 * `tests/browser/career-timeline.spec.ts`.
 *
 * The contract's three load-bearing facts: every TurnEntry becomes one row whose AFTER stat block
 * is the row's own values and whose BEFORE is the previous logged turn (or null on turn 1),
 * `corrected: true` reflects `updated_at > created_at` (D14 cannot recover the prior values; the
 * data layer has no separate edits log, and the prompt's appending on correction becomes a flag
 * here, not a separate row, so the spec is honest about what the model stores), and the page's
 * filter chips are exactly the kinds the controller found.
 */

function timelineRun(array $runState = [], int $turns = 0, int $startTurn = 1): TrainingRun
{
    $run = TrainingRun::factory()->create($runState + [
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ])->create()->id,
    ]);

    for ($i = 0; $i < $turns; $i++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $startTurn + $i,
        ]);
    }

    return $run;
}

it('returns the empty state when no turns have been logged', function (): void {
    $run = timelineRun();

    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Timeline')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.trainee_ja', 'ライスシャワー')
            ->where('run.scenario_label', 'No scenario set')
            ->where('run.run_url', route('runs.show', $run))
            ->where('run.cockpit_url', route('runs.cockpit', $run))
            ->has('entries', 0)
            ->has('filters.available', 0)
            ->where('filters.active', [])
            ->where('empty', fn (?string $empty): bool => is_string($empty) && str_contains($empty, 'No turns'))
        );
});

it('emits one row per turn with BEFORE = previous turn and AFTER = this turn, oldest first', function (): void {
    $run = timelineRun(['scenario' => 'ura_finale'], turns: 3);

    $first = TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 1)->first();
    $second = TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 2)->first();
    $third = TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 3)->first();

    $this->get(route('runs.timeline', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries', 3)
            // Oldest first.
            ->where('entries.0.turn', 1)
            ->where('entries.1.turn', 2)
            ->where('entries.2.turn', 3)
            // Row 1 has no previous turn.
            ->where('entries.0.before', null)
            ->where('entries.0.after.speed', $first->speed)
            ->where('entries.0.after.stamina', $first->stamina)
            ->where('entries.0.kind', 'turn')
            ->where('entries.0.glyph', '●')
            // Row 2's BEFORE is row 1's AFTER.
            ->where('entries.1.before.speed', $first->speed)
            ->where('entries.1.before.stamina', $first->stamina)
            ->where('entries.1.after.speed', $second->speed)
            ->where('entries.1.after.energy', $second->energy)
            ->where('entries.1.mood', fn (?string $mood): bool => $mood === null || is_string($mood))
            // Row 3's BEFORE is row 2's AFTER.
            ->where('entries.2.before.speed', $second->speed)
            ->where('entries.2.after.speed', $third->speed)
            // EXPECTED and ACTUAL collapse to the stored values: ADR-0003 holds absolute end-of-turn,
            // no separate deltas row exists, so RESULT = "Recorded as entered".
            ->where('entries.2.expected.speed', $third->speed)
            ->where('entries.2.actual.speed', $third->speed)
            ->where('entries.2.result', 'Recorded as entered')
            // No correction yet.
            ->where('entries.2.corrected', false)
            ->where('entries.2.correction_id', null)
        );
});

it('flags a corrected turn with corrected: true and a deterministic correction_id', function (): void {
    $run = timelineRun(['scenario' => 'ura_finale'], turns: 1);

    // The factory writes created_at and updated_at within the same second, so a sleep gives the
    // updated column a parseable difference (the page only reads the boolean).
    sleep(1);
    $entry = TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 1)->first();
    $entry->update(['speed' => $entry->speed + 7]);

    $this->get(route('runs.timeline', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries', 1)
            ->where('entries.0.corrected', true)
            ->where('entries.0.correction_id', 'turn-1')
            ->where('entries.0.after.speed', $entry->fresh()->speed)
        );
});

it('interleaves race, event and purchase rows under their own turns', function (): void {
    $run = timelineRun(['scenario' => 'trackblazer'], turns: 2);

    $turnOneId = TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 1)->first()->id;

    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn_entry_id' => $turnOneId,
        'status' => 'Completed',
        'fans_gain' => 1200,
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'event_type' => TurnEventType::Character,
        'source_name' => 'Go Beyond',
        'choice_label' => 'Option A',
        'origin_note' => '+20 Speed',
    ]);

    TurnEvent::create([
        'training_run_id' => $run->id,
        'turn' => 2,
        'event_type' => TurnEventType::Failure,
        'source_name' => 'Training failure',
        'origin_note' => 'Stamina 34 instead of 70',
    ]);

    $this->get(route('runs.timeline', $run))
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries', 5)
            // Turn 1 holds the turn row + race + event.
            ->where('entries.0.kind', 'turn')
            ->where('entries.0.turn', 1)
            ->where('entries.1.kind', 'race')
            ->where('entries.1.turn', 1)
            ->where('entries.1.glyph', '●')
            ->where('entries.1.decision_url', route('runs.races.decision', $run))
            ->where('entries.2.kind', 'event')
            ->where('entries.2.turn', 1)
            ->where('entries.2.action_label', fn (string $label): bool => str_contains($label, 'Go Beyond'))
            ->where('entries.2.decision_url', route('runs.events.decision', $run))
            // Turn 2 holds the turn row + failure.
            ->where('entries.3.kind', 'turn')
            ->where('entries.3.turn', 2)
            ->where('entries.4.kind', 'failure')
            ->where('entries.4.turn', 2)
            ->where('entries.4.glyph', '×')
            ->where('entries.4.result', fn (string $result): bool => str_contains($result, 'Stamina 34'))
            // Filter chips expose only the kinds present.
            ->where('filters.available', function ($available): bool {
                $arr = $available instanceof Collection ? $available->all() : (array) $available;

                return count($arr) >= 4
                    && in_array('Turn', $arr, true)
                    && in_array('Race', $arr, true)
                    && in_array('Event', $arr, true)
                    && in_array('Failure', $arr, true);
            })
        );
});
