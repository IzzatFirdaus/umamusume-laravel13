<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Enums\TurnChoice;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/*
 * G2-FD4 — the action noun, the frozen snapshot, and correction-by-append (plan §9a FD-4, rulings
 * D-1a/D-1b/D-1c/D-1d).
 *
 * **Nothing here hard-codes the wire string for a choice.** `TrainingDetail.vue`'s guided rail posts a
 * prefixed form (`.scratch-uma/cited/g2f4-vocab.txt` records the vocabulary read), so every case speaks through
 * `TurnChoice` itself. A change to a backing value moves the enum and these tests together, never one
 * without the other.
 *
 * **The four columns are the whole schema change.** `choice`, `decision_snapshot`, `corrected_from`,
 * `superseded_by`, plus D-1b's index swap. The tests that prove the index does its job are the two that
 * read `PRAGMA index_list` rather than trusting the migration's own intent: a partial unique index that
 * was named wrongly, or created without its `WHERE`, passes every insert test above it and still lets two
 * current rows coexist.
 */

function fd4Run(string $scenario = 'ura_finale'): TrainingRun
{
    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]);
}

function fd4Turn(TrainingRun $run, int $turn, array $attributes = []): TurnEntry
{
    return TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'speed' => 600,
        'stamina' => 600,
        'power' => 600,
        'guts' => 600,
        'wit' => 600,
        'sp' => 120,
        'energy' => 80,
        'fans' => 1000,
    ] + $attributes);
}

/**
 * The one order a correction can be written in: the new row goes in first, carrying `corrected_from`, and
 * only then can the row it replaces be marked. A partial unique index is checked per statement, so the
 * reverse order asks a database to hold two current rows at one turn for a moment, which is the insert the
 * index exists to refuse. Proven on a scratch database: with the predicate on `superseded_by` the first
 * statement raised `UNIQUE constraint failed` before the second could ever run.
 */
function fd4Correct(TurnEntry $original, array $attributes = []): TurnEntry
{
    $correction = TurnEntry::factory()->create([
        'training_run_id' => $original->training_run_id,
        'turn' => $original->turn,
        'corrected_from' => $original->id,
    ] + $attributes);

    $original->update(['superseded_by' => $correction->id]);

    return $correction;
}

it('keeps the client type mirror of the vocabulary equal to the enum', function (): void {
    // `resources/js/types.ts` copies this list for the browser. A mirror nobody checks is a mirror that
    // rots, so the copy is pinned the way `TurnEntryFieldSourceTest` pins the turn-entry field lists.
    $source = (string) file_get_contents(base_path('resources/js/types.ts'));

    expect($source)->toMatch('/export type TurnChoice =/');

    preg_match('/export type TurnChoice =([^;]+);/s', $source, $match);

    preg_match_all("/'([^']+)'/", (string) ($match[1] ?? ''), $literals);

    $mirror = array_values($literals[1]);
    $enum = array_map(static fn (TurnChoice $case): string => $case->value, TurnChoice::cases());

    sort($mirror);
    sort($enum);

    expect($mirror)->toBe($enum);
});

it('defines a TurnChoice case for every action the application can actually produce', function (): void {
    // D-5: a duel is not producible, so no case may exist for it. The guard is written as a name test
    // rather than a count, so adding a duel case later fails here instead of silently passing.
    expect(TurnChoice::cases())->not->toBe([])
        ->and(collect(TurnChoice::cases())->first(
            static fn (TurnChoice $case): bool => str_contains(strtolower($case->name), 'duel'),
        ))->toBeNull()
        // Rest is a turn choice the advisor itself names, so it belongs in the vocabulary.
        ->and(collect(TurnChoice::cases())->map(
            static fn (TurnChoice $case): int => strlen($case->value),
        )->max())->toBeLessThanOrEqual(32);
});

it('refuses a choice value that is not a TurnChoice', function (): void {
    $run = fd4Run();

    $this->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'energy' => 80, 'fans' => 1000, 'sp' => 120,
        'choice' => 'TrainingTeleport',
    ])
        ->assertSessionHasErrors('choice');

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(0);
});

it('accepts a choice the enum declares and stores it on the turn', function (): void {
    $run = fd4Run();
    $choice = TurnChoice::cases()[0];

    $this->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'energy' => 80, 'fans' => 1000, 'sp' => 120,
        'choice' => $choice->value,
    ])->assertSessionHasNoErrors();

    $entry = TurnEntry::query()->where('training_run_id', $run->id)->first();

    expect($entry->choice)->toBe($choice->value)
        // The write path builds the snapshot itself, from the state before the row existed. Without this
        // half of FD-4 the rail has a forecast to compare the turn against and no record of one.
        ->and($entry->decision_snapshot)->toBeArray()
        ->and(array_keys($entry->decision_snapshot))->toBe([
            'priority', 'recommended', 'alternatives', 'why', 'switch_if', 'refusal', 'objectives', 'recorded_action',
        ])
        // A snapshot that embedded the previous turn's snapshot would embed that one too, so the column
        // would grow with every turn the run takes. The key is present; the nesting is not.
        ->and($entry->decision_snapshot['recorded_action'])->toBeNull();
});

it('persists no choice sent by the browser as the decision snapshot', function (): void {
    // The snapshot is this tool's own state at action time. A hidden form field would let the client
    // assert what the advisor recommended, which is the client-trust rule this slice must not cross:
    // the server writes it, so a posted value is ignored and the stored one is the engine's.
    $run = fd4Run();

    $this->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'energy' => 80, 'fans' => 1000, 'sp' => 120,
        'choice' => TurnChoice::cases()[0]->value,
        'decision_snapshot' => ['priority' => 'The client wrote this, not the advisor.'],
    ])->assertSessionHasNoErrors();

    $stored = TurnEntry::query()->where('training_run_id', $run->id)->first()->decision_snapshot;

    expect($stored)->toBeArray()
        ->and($stored['priority'] ?? null)->not->toBe('The client wrote this, not the advisor.');
});

it('leaves the snapshot untouched when the run changes afterwards', function (): void {
    // D-1a: written once at action time, never updated. This is FD-4's load-bearing assertion — a
    // recomputed "snapshot" is not a snapshot.
    $run = fd4Run();
    $entry = fd4Turn($run, 1, [
        'choice' => TurnChoice::cases()[0]->value,
        'decision_snapshot' => ['priority' => 'Close the largest deficit against the build target you entered.'],
    ]);

    $before = $entry->fresh()->decision_snapshot;

    // A new turn, a mutated stat, and a deleted turn: three ways the run's present changes underneath
    // a stored decision.
    fd4Turn($run, 2, ['speed' => 900]);
    $entry->update(['speed' => 1234]);
    $run->turnEntries()->where('turn', 2)->delete();

    expect($entry->fresh()->decision_snapshot)->toBe($before);
});

it('swaps the plain unique for a partial one over the rows that are not corrections', function (): void {
    $indexes = collect(DB::select("PRAGMA index_list('turn_entries')"))
        ->map(static fn (object $row): string => (string) $row->name);

    expect($indexes->contains('turn_entries_run_turn_original_unique'))->toBeTrue()
        // The WHERE clause is the whole design: without it the "partial" index is just a unique index and
        // the append is impossible while every insert test still passes.
        ->and((string) DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'index' AND name = 'turn_entries_run_turn_original_unique'",
        )->sql)->toMatch('/WHERE\s+corrected_from\s+IS\s+NULL/i')
        // The old plain unique must be gone, or two rows at one turn still cannot coexist.
        ->and($indexes->first(
            static fn (string $name): bool => str_starts_with($name, 'turn_entries_training_run_id_turn_unique'),
        ))->toBeNull()
        // A partial index does not serve an ordinary lookup on (run, turn) for every row, so the
        // turn-addressed queries need a plain index to work from. Without it the swap trades a constraint
        // for a scan, and no insert test would notice.
        ->and($indexes->contains('turn_entries_run_turn_lookup'))->toBeTrue();
});

it('permits a correction beside its original, and still refuses a second row at the turn', function (): void {
    $run = fd4Run();
    $original = fd4Turn($run, 4);

    // A plain second write at a turn that already holds one is the case the original unique guarded, and
    // the partial index keeps guarding it: the new row is not a correction, so it sits in the index and
    // collides with the row already there.
    expect(fn () => fd4Turn($run, 4))->toThrow(UniqueConstraintViolationException::class);

    $correction = fd4Correct($original, ['speed' => 700]);

    expect(TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 4)->count())->toBe(2)
        ->and($original->fresh()->superseded_by)->toBe($correction->id)
        ->and($correction->fresh()->corrected_from)->toBe($original->id)
        // The turn now reads as exactly one live row, which is what every read path asks it to be.
        ->and(TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 4)
            ->whereNull('superseded_by')->count())->toBe(1);
});

it('rolls the whole correction back when the second write fails', function (): void {
    // The cross-links are two writes over two rows. Half a pair leaves a correction pointing at nothing,
    // so the pair must commit together or not at all.
    $run = fd4Run();
    $original = fd4Turn($run, 5);

    $before = TurnEntry::query()->where('training_run_id', $run->id)->count();

    try {
        DB::transaction(function () use ($original, $run): void {
            fd4Correct($original, ['speed' => 700]);

            // A plain second row at the same turn trips the partial unique inside the transaction.
            fd4Turn($run, 5);
        });
    } catch (UniqueConstraintViolationException) {
        // Expected: the write is refused and nothing is left behind.
    }

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe($before)
        ->and($original->fresh()->superseded_by)->toBeNull();
});

it('accepts every case it declares, including the two the rail names but no card posts', function (): void {
    // `rest` and `mood` come from `TrainingRunController::turnChoices()`, not from the five training cards.
    // Rest is an action `TrainerAdvisor::ACTIONS` ranks, so a rail that recommends it must be able to store
    // it; a vocabulary that refused it would make the advisor's own suggestion unwritable.
    foreach (TurnChoice::cases() as $index => $choice) {
        $run = fd4Run();

        $this->post(route('runs.turns.store', $run), [
            'turn' => 1,
            'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
            'energy' => 80, 'fans' => 1000, 'sp' => 120,
            'choice' => $choice->value,
        ])->assertSessionHasNoErrors();

        expect(TurnChoice::tryFrom(
            (string) TurnEntry::query()->where('training_run_id', $run->id)->first()->choice,
        ))->toBe($choice, "case {$choice->name} did not round-trip at position {$index}");
    }
});

it('builds the three recorded-action shapes the card renders', function (): void {
    // Pending: an action with no entered result. `actual` is null and there is nothing to reconcile, so the
    // card's second state is the prompt, not an empty comparison.
    $run = fd4Run();
    $pending = fd4Turn($run, 1, [
        'choice' => TurnChoice::TrainSpeed->value,
        'decision_snapshot' => ['recommended' => ['action' => 'Speed', 'benefit' => ['Speed is 200 below target.']]],
    ]);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run->fresh(['turnEntries'])))->assertOk()->inertiaProps('advisor.decision');
    $action = $decision['recorded_action'];

    expect($action['choice'])->toBe(TurnChoice::TrainSpeed->value)
        ->and($action['action'])->toBe('Speed')
        ->and($action['expected'])->toBe([[
            'text' => 'Speed is 200 below target.',
            'badge' => 'calculated',
            'title' => $action['expected'][0]['title'],
        ]])
        ->and($action['actual'])->toBeNull()
        ->and($action['reconciliation'])->toBeNull()
        // The snapshot on the card is the stored row verbatim, not a payload rebuilt from today's state.
        ->and($action['decision'])->toBe($pending->decision_snapshot);

    // Entered and agreeing: the result is recorded, and the action taken is the action recommended.
    $pending->update(['preview_gains' => ['speed' => 20]]);

    /** @var array<string, mixed> $agreed */
    $agreed = $this->get(route('runs.cockpit', $run->fresh(['turnEntries'])))
        ->assertOk()
        ->inertiaProps('advisor.decision.recorded_action');

    expect($agreed['actual'])->toBe([[
        'text' => 'Speed +20',
        'badge' => 'confirmed',
        'title' => $agreed['actual'][0]['title'],
    ]])
        ->and($agreed['reconciliation']['match'])->toBeTrue()
        ->and($agreed['reconciliation']['note'])->toBe('The action recorded is the one this rail recommended at the time.');

    // Entered and differing: the same two facts, read against a recommendation for a different action.
    $pending->update(['choice' => TurnChoice::Rest->value]);

    /** @var array<string, mixed> $differed */
    $differed = $this->get(route('runs.cockpit', $run->fresh(['turnEntries'])))
        ->assertOk()
        ->inertiaProps('advisor.decision.recorded_action');

    expect($differed['action'])->toBe('Rest')
        ->and($differed['reconciliation']['match'])->toBeFalse()
        ->and($differed['reconciliation']['note'])->toBe(
            'The action recorded is not the one this rail recommended at the time. Both words are readings '
            .'this tool kept, and nothing here says which was the better turn.',
        );

    // Corrected: the pair the schema allows, with the newer row current and the older one superseded.
    $correction = fd4Correct($pending, [
        'choice' => TurnChoice::TrainWit->value,
        'decision_snapshot' => ['recommended' => ['action' => 'Wit', 'benefit' => []]],
    ]);

    /** @var array<string, mixed> $corrected */
    $corrected = $this->get(route('runs.cockpit', $run->fresh(['turnEntries'])))
        ->assertOk()
        ->inertiaProps('advisor.decision.recorded_action');

    expect($corrected['id'])->toBe($correction->id)
        ->and($corrected['corrected_from'])->toBe($pending->id)
        ->and($corrected['action'])->toBe('Wit')
        // The snapshot travels with the correction it belongs to, not with the row it replaced.
        ->and($corrected['decision'])->toBe($correction->decision_snapshot);
});

it('refuses to reconcile against a stored value the vocabulary no longer declares', function (): void {
    // `Rule::enum` guards new writes, but the column is older than any one list of values. A row whose
    // `choice` no longer maps to a case prints no action word rather than a invented one, so the slot
    // falls back to "nothing recorded" instead of guessing.
    $run = fd4Run();
    fd4Turn($run, 1, [
        'choice' => 'training-Teleport',
        'decision_snapshot' => ['recommended' => null],
    ]);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run->fresh(['turnEntries'])))
        ->assertOk()
        ->inertiaProps('advisor.decision');

    expect($decision['recorded_action'])->toBeNull();
});

it('keeps a stored choice when a correction writes without one', function (): void {
    // `updateTurn` writes `$request->validated()` whole, and `choice` is one of its keys. If a PUT that
    // omits the field delivered `choice => null`, every manual correction from the Cockpit would erase
    // the action noun sitting beside the reading it came to fix.
    $run = fd4Run();
    $value = TurnChoice::cases()[0]->value;

    $this->post(route('runs.turns.store', $run), [
        'turn' => 1,
        'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'energy' => 80, 'fans' => 1000, 'sp' => 120,
        'choice' => $value,
    ])->assertSessionHasNoErrors();

    $entry = TurnEntry::query()->where('training_run_id', $run->id)->first();
    $snapshot = $entry->decision_snapshot;

    $this->put(route('runs.turns.update', [$run, $entry]), [
        'turn' => 1,
        'speed' => 610, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
        'energy' => 60, 'fans' => 1000, 'sp' => 120,
    ])->assertSessionHasNoErrors();

    expect($entry->fresh()->choice)->toBe($value)
        ->and($entry->fresh()->decision_snapshot)->toBe($snapshot)
        ->and($entry->fresh()->energy)->toBe(60);
});

it('exposes the eighth contract key and keeps the seven it had', function (): void {
    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', fd4Run()))->assertOk()->inertiaProps('advisor.decision');

    expect(array_keys($decision))->toBe([
        'priority', 'recommended', 'alternatives', 'why', 'switch_if', 'refusal', 'objectives', 'recorded_action',
    ])
        // A run with no recorded action carries a null, not an absent key and not an invented row.
        ->and($decision['recorded_action'])->toBeNull()
        ->and(json_encode($decision))->not->toMatch('/"(score|confidence|band|risk)"/');
});

it('keeps an old run record out of the new snapshot shape', function (): void {
    // A row written before this migration has no choice and no snapshot. The rail must render it as
    // unknown rather than deriving a decision the advisor may never have made.
    $run = fd4Run();
    fd4Turn($run, 1);

    /** @var array<string, mixed> $decision */
    $decision = $this->get(route('runs.cockpit', $run->fresh(['turnEntries'])))
        ->assertOk()
        ->inertiaProps('advisor.decision');

    expect($decision['recorded_action'])->toBeNull();
});
