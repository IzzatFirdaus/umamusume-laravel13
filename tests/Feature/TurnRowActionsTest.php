<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;

/*
 * SCREEN_SPEC.md §7-2: `runs.turns.update` and `runs.turns.destroy` are implemented and
 * covered endpoint-to-endpoint by `TrainingRunTest:160-235`, but no form on any screen posts
 * to them, so the behavior is unreachable in the product. Those existing tests build payloads
 * by hand; the ones here drive the page, because the defect is the missing control rather
 * than a missing route.
 *
 * The disclosure is a link plus a query parameter, the same shape the deck picker
 * (`components/deck-panel.blade.php:93-106`) and the skill rows already use: this page carries
 * no script (ADR-0007, `RunViewNoScriptTest`), so "expand row 2" has to be a navigation. That
 * also makes the destructive step two-stepped by construction, which is the confirmation the
 * dispatch asks for, and keeps the second DELETE form off the page until a Trainer is looking
 * at the row they mean to remove.
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

it('offers an Edit control on every turn row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    turnFor($run, 1);
    turnFor($run, 2, ['speed' => 222]);

    $response = $this->get(route('runs.show', $run));

    // Rendered copy, not markup shape: the control has to say what it does, and a test that
    // matched only the anchor would pass on a link that goes nowhere useful.
    $response->assertOk()
        ->assertSeeText('Edit turn 1')
        ->assertSeeText('Edit turn 2')
        ->assertSee("edit_turn={$run->turnEntries()->orderBy('id')->value('id')}", false);
});

it('expands one row into a form that posts the correction back to that row', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    $html = $this->get(route('runs.show', ['run' => $run, 'edit_turn' => $entry->id]))
        ->assertOk()
        ->assertSeeText('Save turn')
        ->assertSeeText('Delete turn 2')
        ->content();

    // Scoped to this one form: the page carries `stage` and `previewed` for the guided rail, and
    // a page-wide absence check would be asserting about the rail rather than the row editor.
    $start = strpos($html, 'action="'.route('runs.turns.update', [$run, $entry]).'"');
    $form = substr($html, (int) $start, (int) strpos(substr($html, (int) $start), '</form>') + 7);

    expect($form)
        ->toContain('name="_method" value="PUT"')
        ->toContain('name="_token"')
        // The row's own values come pre-filled, so a Trainer edits a number rather than
        // retyping the turn, and a refused submit keeps what they typed.
        ->toContain('value="100"')
        ->toContain('name="speed"')
        ->not->toContain('name="stage"')
        ->not->toContain('name="previewed"');
});

it('saves a corrected turn through the row form', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = turnFor($run, 2);

    $this->put(route('runs.turns.update', [$run, $entry]), turnRowPayload())
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

    $plain = $this->get(route('runs.show', $run))->content();
    $open = $this->get(route('runs.show', ['run' => $run, 'edit_turn' => $entry->id]))->content();

    // The run itself has one delete form, and `RunViewNoScriptTest` pins that count page-wide.
    // Two-stepping the destructive turn action is what keeps that pin honest rather than pinned
    // around by accident: the turn form only exists once a Trainer has asked for that row.
    expect(substr_count($plain, 'name="_method" value="DELETE"'))
        ->toBeLessThan(substr_count($open, 'name="_method" value="DELETE"'));
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

    $html = $this->get(route('runs.show', ['run' => $run, 'edit_turn' => $entry->id]))->content();

    // The row is still open and still holds what was typed: a refused submit that collapsed the
    // row back to the stored readings would throw the Trainer's correction away.
    expect($html)->toContain('name="speed"')
        ->and($html)->toContain('value="140"')
        ->and($entry->refresh()->speed)->toBe(100);
});

/*
 * The two writes above reach the turn row; these three cover what the row's delete does and does
 * not take with it.
 *
 * `turn_events` is keyed on `(training_run_id, turn)` and has no `turn_entry_id` foreign key, so a
 * failure event outlives the turn row that produced it, and the run page reads its chips by keying
 * the events on `turn` (show.blade.php:321-323). Deleting a failed turn therefore left the Failed
 * chip behind to be inherited by whichever turn later took that number, and §7-2 recorded the same
 * shape for the log line the chip prints.
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

    $html = $this->get(route('runs.show', $run))->assertOk()->content();

    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0)
        ->and($html)->not->toContain('Penalty kind:')
        ->and($html)->not->toContain('>Failed<');
});
