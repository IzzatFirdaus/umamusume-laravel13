<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN_SPEC.md §7-2: `runs.turns.update` and `runs.turns.destroy` are implemented and
 * covered endpoint-to-endpoint by `TrainingRunTest:160-235`, but no form on any screen posts
 * to them, so the behavior is unreachable in the product. Those existing tests build payloads
 * by hand; the ones here drive the page, because the defect is the missing control rather
 * than a missing route.
 *
 * The disclosure was a link plus a query parameter the page read; it is the page component's
 * own open row now, and the `edit_turn` query string is still accepted by the GET. Either way
 * the destructive step is two-stepped by construction: the row's delete control only exists
 * once a Trainer is looking at the row they mean to remove, and the destroy route answers
 * DELETE only, so no navigation can fire it.
 */

function turnFor(TrainingRun $run, int $turn, array $overrides = []): TurnEntry
{
    return TurnEntry::factory()->create(array_merge([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'speed' => 100,
        'stamina' => 90,
        'power' => 110,
        'guts' => 80,
        'wit' => 95,
        'sp' => 18,
        'condition' => 'GOOD',
        'energy' => 60,
        'mood' => 'GOOD',
        'fans' => 1200,
    ], $overrides));
}

function turnRowPayload(array $overrides = []): array
{
    return array_merge([
        'turn' => 2,
        'speed' => 140,
        'stamina' => 95,
        'power' => 115,
        'guts' => 85,
        'wit' => 99,
        'sp' => 20,
        'condition' => 'NORMAL',
        'energy' => 55,
        'mood' => 'NORMAL',
        'fans' => 1500,
    ], $overrides);
}

it('offers an edit path on every turn row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    turnFor($run, 1);
    turnFor($run, 2, ['speed' => 222]);

    // The row control (`Edit turn N` opening the row's own form) is the component's; what the
    // server owes is that every turn carries its own routes, so the control a row opens posts
    // back to that row rather than to whichever row happens to be first.
    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->has('turns', 2)
            ->where('turns.0.turn', 1)
            ->where('turns.1.turn', 2)
            ->where('turns', fn (Collection $turns): bool => $turns->every(
                fn (array $turn): bool => $turn['update_url'] === route('runs.turns.update', [$run, $turn['id']])
                    && $turn['destroy_url'] === route('runs.turns.destroy', [$run, $turn['id']])
            )));
});

it('expands one row into a form that posts the correction back to that row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    // The row editor is the component's; the payload gives it the row it edits: the stored
    // readings, so a Trainer edits a number rather than retyping the turn, that row's own
    // routes, and nothing of the rail the two share the screen with.
    test()->get(route('runs.show', ['run' => $run, 'edit_turn' => $entry->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('turns.0.id', $entry->id)
            ->where('turns.0.update_url', route('runs.turns.update', [$run, $entry]))
            ->where('turns.0.destroy_url', route('runs.turns.destroy', [$run, $entry]))
            ->where('turns.0.speed', 100)
            ->missing('turns.0.stage')
            ->missing('turns.0.previewed'));
});

it('saves a corrected turn through the row form', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    // The redirect returns to the page the form was posted from, so the run screen is seeded as the
    // referer: the same URL the row form lives on, and the same target as before the Cockpit arrived.
    $this->from(route('runs.show', $run))
        ->put(route('runs.turns.update', [$run, $entry]), turnRowPayload())
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Turn 2 updated.');

    expect($entry->refresh()->speed)->toBe(140)
        ->and($entry->condition)->toBe('NORMAL')
        ->and(TurnEntry::where('training_run_id', $run->id)->count())->toBe(1);
});

it('refuses a corrected turn the same way it refuses a new one', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    $this->put(route('runs.turns.update', [$run, $entry]), turnRowPayload(['speed' => 5000]))
        ->assertSessionHasErrors('speed');

    expect($entry->refresh()->speed)->toBe(100);
});

it('deletes a turn through the expanded row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $doomed = turnFor($run, 2);
    turnFor($run, 3, ['speed' => 300]);

    $this->delete(route('runs.turns.destroy', [$run, $doomed]))
        ->assertRedirect(route('runs.show', $run))
        ->assertSessionHas('status', 'Turn 2 removed.');

    expect(TurnEntry::where('training_run_id', $run->id)->pluck('turn')->all())->toBe([3]);
});

it('keeps the row delete off the page until the row is open', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    // Two-stepping the destructive turn action. Opening a row is a navigation, and a navigation
    // cannot fire the delete: the destroy route answers DELETE only, so the GET a Trainer follows
    // to reach their row comes back 405 rather than removing anything. The row's DELETE form only
    // exists on the opened row, which is the browser spec's count.
    test()->get(route('runs.show', ['run' => $run, 'edit_turn' => $entry->id]))->assertOk();

    test()->get(route('runs.turns.destroy', [$run, $entry]))
        ->assertStatus(405);

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
});

it('will not edit or delete another run\'s turn from this run\'s page', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $other = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $foreign = turnFor($other, 1);

    // This app has no accounts (ARCHITECTURE.md §8, NFR-1), so "the user owns it" is the nested
    // scope: a turn id from another run is not a row of this run and answers 404 either way.
    $this->put(route('runs.turns.update', [$run, $foreign]), turnRowPayload(['turn' => 7]))
        ->assertNotFound();
    $this->delete(route('runs.turns.destroy', [$run, $foreign]))
        ->assertNotFound();

    expect($foreign->refresh()->turn)->toBe(1)
        ->and(TurnEntry::where('training_run_id', $run->id)->count())->toBe(0);
});

it('keeps the row open after a refused submit so the typed edits survive', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    $this->put(route('runs.turns.update', [$run, $entry]), turnRowPayload(['wit' => 99999]))
        ->assertSessionHasErrors('wit');

    // The row the page re-reads after the refusal is still the row the Trainer was editing, with
    // the stored readings intact. Keeping it open, and keeping the typed numbers in it, is the
    // component's own state across the error visit, carried by the browser spec.
    test()->get(route('runs.show', ['run' => $run, 'edit_turn' => $entry->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('turns.0.id', $entry->id)
            ->where('turns.0.speed', 100));

    expect($entry->refresh()->speed)->toBe(100);
});

/*
 * The two writes above reach the turn row; these three cover what the row's delete does and does
 * not take with it.
 *
 * `turn_events` is keyed on `(training_run_id, turn)` and has no `turn_entry_id` foreign key, so a
 * failure event outlives the turn row that produced it, and the run page reads its chips from the
 * `failure` key the controller folds onto each turn row (ADR-0003). Deleting a failed turn
 * therefore left the Failed chip behind to be inherited by whichever turn later took that number,
 * and §7-2 recorded the same shape for the log line the chip prints.
 *
 * The scoped delete is the narrow one on purpose. `storePurchase` writes `event_type = Scenario`
 * rows on the very same `(run, turn)` key (TrainingRunController:593-598), so an unfiltered delete
 * would let "remove this turn" also destroy a shop purchase logged at that number. That is a
 * different claim about different data, and the tests below pin the failure half only.
 */

function failedTurn(TrainingRun $run, int $turn): TurnEntry
{
    test()->post("/training-runs/{$run->id}/turns", [
        'turn' => $turn,
        'speed' => 120, 'stamina' => 110, 'power' => 130, 'guts' => 100, 'wit' => 95,
        'sp' => 20, 'energy' => 70, 'mood' => 'GREAT', 'fans' => 1200,
        'choice' => 'training-Speed', 'outcome' => 'Failure', 'penalty_kind' => 'energy',
        'stage' => 'confirm', 'previewed' => '1',
    ])->assertRedirect(route('runs.show', $run));

    return TurnEntry::query()->where('training_run_id', $run->id)->where('turn', $turn)->sole();
}

it('deletes the failure event together with the turn that recorded it', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = failedTurn($run, 5);

    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(1);

    $this->delete(route('runs.turns.destroy', [$run, $entry]))->assertRedirect(route('runs.show', $run));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0);
});

it('leaves another turn\'s event alone when one turn is deleted', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $doomed = failedTurn($run, 5);
    $kept = failedTurn($run, 6);

    $this->delete(route('runs.turns.destroy', [$run, $doomed]))->assertRedirect(route('runs.show', $run));

    // The delete is by `(run, turn)`, so the row beside it has to survive untouched. Without this
    // the fix above could pass on a delete that cleared the whole run's events.
    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 6)->count())->toBe(1)
        ->and($kept->refresh()->turn)->toBe(6);
});

it('does not hand a re-logged turn the failed chip of the turn that used the number before', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $doomed = failedTurn($run, 5);

    $this->delete(route('runs.turns.destroy', [$run, $doomed]))->assertRedirect(route('runs.show', $run));

    // The same number, logged again as a success this time. The new turn writes no event, so the
    // only way this page can come out clean is the store path clearing what the old turn left.
    $this->post("/training-runs/{$run->id}/turns", [
        'turn' => 5,
        'speed' => 120, 'stamina' => 110, 'power' => 130, 'guts' => 100, 'wit' => 95,
        'sp' => 20, 'energy' => 70, 'mood' => 'GREAT', 'fans' => 1200,
        'choice' => 'training-Speed', 'outcome' => 'Success',
        'stage' => 'confirm', 'previewed' => '1',
    ])->assertRedirect(route('runs.show', $run));

    // The chip and its `Penalty kind:` line are drawn from the row's own `failure` key (ADR-0003),
    // so a null here is the clean row.
    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('turns.0.failure', null));

    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0);
});
