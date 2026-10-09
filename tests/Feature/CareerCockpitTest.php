<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RunStatus;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * SCREEN-009, the Career Cockpit (plan §8 D8). The props are asserted here; the rendered copy, the
 * three breakpoints, the keyboard path through the action grid and the reduced-motion check live in
 * `tests/browser/career-cockpit.spec.ts` (plan §2 convention 2).
 *
 * The advisor cases are the acceptance the slice names: the contract with Energy present and with
 * Energy absent. C2's four ranking rules are its own tests; what this file proves is that the page
 * carries C2's answer across the wire without adding a field C2 does not have — no score, no numeric
 * confidence — and that a run with no Energy prints the refusal rather than a band.
 */

/**
 * A run with `$turns` logged turns, the last carrying the given Energy and Mood.
 *
 * `stripValues()` reads Energy and Fans off the latest logged turn, so a run's header figures and its
 * turn count are two readings of the same rows and the fixture writes them together.
 */
function cockpitRun(array $runState, int $turns = 0, ?int $energy = null, ?MoodTier $mood = null): TrainingRun
{
    $run = TrainingRun::factory()->create($runState);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 600,
            'stamina' => 600,
            'power' => 600,
            'guts' => 600,
            'wit' => 600,
            'sp' => 120,
            'energy' => $turn === $turns ? $energy : 60,
            'mood' => $turn === $turns ? $mood : MoodTier::Normal,
            'fans' => 1000 * $turn,
        ]);
    }

    return $run;
}

it('renders the cockpit for a run with no logged turn, and names what is missing', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state([
            'name' => 'Rice Shower',
            'name_ja' => 'ライスシャワー',
        ]),
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('run.id', $run->id)
            ->where('run.trainee', 'Rice Shower')
            ->where('run.trainee_ja', 'ライスシャワー')
            ->where('run.scenario_label', 'Unity Cup')
            ->where('run.status', 'Active')
            ->where('run.status_label', 'Active')
            ->where('run.run_url', route('runs.cockpit', $run))
            // F1: the Career Result had no door on a 2.0 screen, only in the 0.1.0 record page's
            // header. The left column carries it now, so retiring that page cannot strand the screen.
            ->where('run.result_url', route('runs.result', $run))
            ->where('header.turn', 0)
            // A run with nothing logged has no position to claim: null, never Early January (D-220).
            ->where('header.year_label', null)
            ->where('header.month_label', null)
            ->where('header.energy', null)
            ->where('header.mood', null)
            ->where('header.fans', null)
            ->where('header.skill_points', null)
            // No turn has recorded anything, so no stat has a current value.
            ->where('state.stats.0.key', 'Speed')
            ->where('state.stats.0.current', null)
            ->where('state.stats.4.key', 'Wit')
            ->has('state.stats', 5)
            // Energy, Mood, Fans and Skill Points: four meta values beside the five stats (§13).
            ->has('state.meta', 4)
            ->where('state.meta.0.key', 'energy')
            ->where('state.meta.0.value', null)
            ->where('state.meta.1.key', 'mood')
            ->where('state.meta.2.key', 'fans')
            ->where('state.meta.3.key', 'skill_points')
            // No turn to correct, so the correction form has nothing to open on.
            ->where('correction', null));
});

it('carries the advisor contract with Energy present and no score or numeric confidence', function (): void {
    $run = cockpitRun([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ], turns: 12, energy: 80);

    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        // Speed carries the largest deficit and Wit the second, so the advisor names a recommendation
        // and an alternative rather than a recommendation alone.
        'targets' => ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
        'skill_priorities' => [],
    ])->toArray()]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.band', 'AtOrAboveAdvisory')
            // Speed is 300 below its target, the largest deficit, so it is the recommendation.
            ->where('advisor.action', 'Speed')
            ->where('advisor.alternative', 'Wit')
            // The reason lines restate the deficit and the Energy the action leaves behind.
            ->has('advisor.reasons', 2)
            ->where('advisor.reasons.0', 'Speed is 300 below target (the largest deficit).')
            ->has('advisor.risk')
            // The five contract fields plus one status line, and still no score and no numeric
            // confidence: those would be unsourced figures (plan §8 "Recommendation contract (D8)",
            // ADR-0001 §3). `finale` is a position read off the career calendar, not a judgement, and
            // the engine gives no counsel on the concert itself (ADR-0020 §3, SCREEN_SPEC.md §7-22).
            ->has('advisor', fn (Assert $advisor) => $advisor
                ->hasAll(['action', 'band', 'reasons', 'alternative', 'risk', 'finale'])));
});

it('refuses to rank when Energy has not been entered', function (): void {
    $run = cockpitRun([
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active,
    ], turns: 3, energy: null);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // No band, no ranking: C2's own refusal state, carried across rather than papered over.
            ->where('advisor.band', null)
            ->where('advisor.action', null)
            ->where('advisor.alternative', null)
            // The absence is the one reason line, and it names Energy as the missing input.
            ->has('advisor.reasons', 1)
            ->where('advisor.reasons.0', 'This run has recorded no Energy, so there is no band to read and nothing to rank against.'));
});

it('marks exactly one action as recommended, and none when the advisor declines', function (): void {
    $run = cockpitRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 12, energy: 80);
    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        // Speed carries the largest deficit and Wit the second, so the advisor names a recommendation
        // and an alternative rather than a recommendation alone.
        'targets' => ['Speed' => 900, 'Stamina' => 500, 'Power' => 500, 'Guts' => 500, 'Wit' => 800],
        'skill_priorities' => [],
    ])->toArray()]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Seven entries, in the spec's own order (SCREEN-009 §12).
            ->has('actions', 7)
            ->where('actions.0.key', 'training')
            ->where('actions.1.key', 'race')
            ->where('actions.2.key', 'rest')
            ->where('actions.3.key', 'recreation')
            ->where('actions.4.key', 'scenario')
            ->where('actions.5.key', 'event')
            ->where('actions.6.key', 'inheritance')
            // Von Restorff (plan §13): one RECOMMENDED marker, not an accent on every card. Speed is
            // a training action, so the marker sits on the training entry and nowhere else.
            ->where('actions.0.recommended', true)
            ->where('actions.1.recommended', false)
            ->where('actions.2.recommended', false)
            ->where('actions.3.recommended', false)
            ->where('actions.4.recommended', false)
            ->where('actions.5.recommended', false)
            ->where('actions.6.recommended', false));

    // Below the advisory line the advisor recommends Rest, and the marker moves with it.
    $run->turnEntries()->reorder('turn', 'desc')->first()->update(['energy' => 20]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.band', 'BelowAdvisory')
            ->where('advisor.action', 'Rest')
            ->where('actions.2.recommended', true)
            ->where('actions.0.recommended', false));

    // No Energy at all: the advisor declines, so nothing is marked. Every entry, not a sample of two.
    $run->turnEntries()->reorder('turn', 'desc')->first()->update(['energy' => null]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('advisor.action', null)
            ->where('actions', fn (Collection $actions): bool => $actions->every(
                static fn (array $action): bool => $action['recommended'] === false,
            )));
});

it('reads the scenario widgets from config rather than from a scenario name', function (): void {
    // Two scenarios, two widget lists, one code path (G-33, D-240). Unity Cup composes `team_rank`
    // and `spirit_bursts` beyond the baseline three; URA Finale composes none.
    $unity = cockpitRun(['scenario' => 'unity_cup', 'status' => RunStatus::Active], turns: 1, energy: 70);

    $this->get(route('runs.cockpit', $unity))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('header.widgets', ['team_rank', 'spirit_bursts'])
            ->where('header.scenario_declared', true));

    $unity->update(['status' => RunStatus::Retired]);

    $ura = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 70);

    $this->get(route('runs.cockpit', $ura))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('header.widgets', []));

    // A run that names no scenario composes the baseline's three widgets and no scenario resource.
    $ura->update(['status' => RunStatus::Retired]);

    $bare = cockpitRun(['scenario' => null, 'status' => RunStatus::Active], turns: 1, energy: 70);

    $this->get(route('runs.cockpit', $bare))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.scenario_label', 'No scenario set')
            ->where('header.scenario_declared', false)
            ->where('header.widgets', []));
});

it('shows each stat as current over target, with the cap beside it', function (): void {
    $run = cockpitRun(['scenario' => 'trackblazer', 'status' => RunStatus::Active], turns: 4, energy: 65);
    $run->update(['build_target' => BuildTargetPayload::fromArray([
        'purpose' => 'StoryClear',
        'distance' => 'Long',
        'surface' => 'Turf',
        'style' => 'Late Surger',
        'targets' => ['Speed' => 700, 'Stamina' => 900, 'Power' => 400, 'Guts' => 400, 'Wit' => 400],
        'skill_priorities' => [],
    ])->toArray()]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('state.stats.0.key', 'Speed')
            ->where('state.stats.0.current', 600)
            ->where('state.stats.0.target', 700)
            // The ceiling is `ScenarioCaps::forRun()`'s, the same call the turn validator makes
            // (KI-47, ADR-0015): Trackblazer adds 700 to Stamina's 1200 base.
            ->where('state.stats.1.key', 'Stamina')
            ->where('state.stats.1.cap', 1900)
            ->where('state.stats.1.target', 900));

    // A run with no target records none: the stat keeps its current value and its cap, and the
    // target is null rather than a zero the Trainer never entered.
    $run->update(['build_target' => null]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('state.stats.0.current', 600)
            ->where('state.stats.0.target', null));
});

it('opens the manual correction on the latest turn and posts to the existing route', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 3, energy: 55, mood: MoodTier::Great);

    // `reorder`, not `orderByDesc`: the relation already carries an ascending `turn` order, and
    // appending a second one yields `turn asc, turn desc`, which returns the *first* turn
    // (`TrainingRun::stripValues()` records the same trap).
    $latest = $run->turnEntries()->reorder('turn', 'desc')->first();

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('correction.action', route('runs.turns.update', [$run, $latest]))
            ->where('correction.turn', 3)
            ->where('correction.values.speed', 600)
            ->where('correction.values.energy', 55)
            ->where('correction.values.mood', 'GREAT'));
});

it('rehydrates the correction form on the turn named by edit_turn', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 3, energy: 55, mood: MoodTier::Great);

    // An earlier turn carrying its own reading, so a form that stayed on the latest turn would
    // fail these assertions rather than pass them by coincidence.
    $first = $run->turnEntries()->reorder('turn', 'asc')->first();
    $first->update(['speed' => 410, 'stamina' => 320, 'energy' => 71, 'mood' => MoodTier::Good]);

    // F2, plan §9.6 ruling 2: `?edit_turn=<id>` names any logged turn, and the form opens on that
    // turn's stored row rather than the latest one (the `selected_turn_id` the selector binds to).
    $this->get(route('runs.cockpit', $run).'?edit_turn='.$first->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('correction.action', route('runs.turns.update', [$run, $first]))
            ->where('correction.selected_turn_id', $first->id)
            ->where('correction.turn', 1)
            ->where('correction.values.speed', 410)
            ->where('correction.values.stamina', 320)
            ->where('correction.values.energy', 71)
            ->where('correction.values.mood', 'GOOD'));
});

it('refuses a correction that drops a required stat', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);
    $turn = $run->turnEntries()->reorder('turn', 'desc')->first();

    // The correction form marks all five stats required, and `StoreTurnEntryRequest` enforces it on
    // the same `runs.turns.update` the legacy row form posted to.
    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), [
            'turn' => $turn->turn,
            'stamina' => 300, 'power' => 300, 'guts' => 300, 'wit' => 300,
            // `speed` omitted.
        ])
        ->assertSessionHasErrors('speed');
});

it('refuses a correction whose stat is above the scenario ceiling', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);
    $turn = $run->turnEntries()->reorder('turn', 'desc')->first();

    // URA Finale's Speed ceiling is 1400 (base 1200 plus its 200 bonus, `ScenarioCaps::forRun`), so
    // 1500 is out by a hundred and the same request refuses it (KI-47, ADR-0015).
    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $turn]), [
            'turn' => $turn->turn,
            'speed' => 1500, 'stamina' => 300, 'power' => 300, 'guts' => 300, 'wit' => 300,
        ])
        ->assertSessionHasErrors('speed');
});

it('404s a correction to a turn id that does not exist', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);

    // The route binds `{turn}` by id, so a missing id is a 404 before the write is reached.
    $this->put(route('runs.turns.update', [$run, 999999]), [
        'turn' => 1, 'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
    ])->assertNotFound();
});

it('404s a correction to a turn belonging to another run', function (): void {
    // The correction run has turn 1 only; the other run owns turn 2. Route binding resolves the
    // foreign turn by id alone, so the controller's run scoping is what has to refuse it.
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 60);
    $other = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);

    $foreign = $other->turnEntries()->reorder('turn', 'desc')->first();

    $this->put(route('runs.turns.update', [$run, $foreign]), [
        'turn' => 2, 'speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600,
    ])->assertNotFound();
});

it('reports the cockpit as an Inertia page for a retired run too, with no write routes invented', function (): void {
    // The cockpit is a read surface over any run, whatever its status: nothing here gates on Active.
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Completed], turns: 2, energy: 70);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('run.status', 'Completed'));
});

it('names no scenario the run never chose, while the picker still lists every scenario', function (): void {
    // F-3, moved from the retired record screen (F2, plan §9.6). The run names no scenario, so the
    // Cockpit states the absence rather than composing a label; the picker is a control, not a
    // claim, so it still lists every scenario including the one the run does not use.
    $run = cockpitRun(['scenario' => null, 'status' => RunStatus::Active], turns: 1, energy: 60);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('run.scenario_label', 'No scenario set')
            ->where('run.scenarios.ura_finale', 'URA Finale'));
});

/*
 * The Cockpit header edit forms (F2, plan §9.6 ruling 1) and the manual correction (ruling 2) now own
 * the writes the retired 0.1.0 record screen carried. These cases were ported from the four source
 * suites that tested that screen; only the assertions whose behavior still exists on the Cockpit were
 * carried, and each posts to the same `runs.update` / `runs.turns.*` routes the legacy page used.
 */

it('offers the three statuses and marks the run\'s current one', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Completed]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The options the status select renders, value => label, built from `RunStatus::cases()`.
            ->where('run.status_labels', ['Active' => 'Active', 'Completed' => 'Completed', 'Retired' => 'Retired'])
            // The current value is what the select binds to, so pressing Save without touching the
            // select is a no-op rather than a silent reset to Active.
            ->where('run.status', 'Completed'));
});

it('moves a run through each status transition the header select offers', function (RunStatus $from, RunStatus $to): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => $from]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => $to->value,
    ])->assertRedirect(route('runs.cockpit', $run));

    expect($run->refresh()->status)->toBe($to);
})->with([
    'Active to Completed' => [RunStatus::Active, RunStatus::Completed],
    'Completed to Retired' => [RunStatus::Completed, RunStatus::Retired],
    'Retired back to Active' => [RunStatus::Retired, RunStatus::Active],
]);

it('refuses a status outside the enum and leaves the run where it was', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => 'OnHold',
    ])->assertSessionHasErrors('status');

    expect($run->refresh()->status)->toBe(RunStatus::Active);
});

it('changes the status without blanking the fields the select is not editing', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);
    $run->update(['notes' => 'Kept across a status change']);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => RunStatus::Retired->value,
    ])->assertRedirect(route('runs.cockpit', $run));

    $fresh = $run->refresh();

    expect($fresh->status)->toBe(RunStatus::Retired)
        ->and($fresh->notes)->toBe('Kept across a status change')
        ->and($fresh->scenario)->toBe('ura_finale')
        ->and($fresh->umamusume_id)->toBe($run->umamusume_id);
});

it('submits the status form with the run\'s own carried fields', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The shared request needs the fields this form is not editing, which is why the header
            // forms carry them. A status form that dropped them would blank the run, so the payload
            // ships the run's own values and the action the form posts to.
            ->where('run.update_url', route('runs.update', $run))
            ->where('run.umamusume_id', $run->umamusume_id)
            ->where('run.scenario', 'ura_finale'));
});

it('switches a run to another scenario in the matrix through the header scenario form', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'unity_cup',
        'status' => RunStatus::Active->value,
    ])->assertRedirect(route('runs.cockpit', $run));

    expect($run->refresh()->scenario)->toBe('unity_cup');
});

it('refuses a scenario the matrix does not have', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => 'grand_masters',
        'status' => RunStatus::Active->value,
    ])->assertSessionHasErrors('scenario');

    // The stored scenario is untouched: a refused switch must not blank the run.
    expect($run->refresh()->scenario)->toBe('ura_finale');
});

it('normalises an empty scenario to none through the header scenario form', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => '',
        'status' => RunStatus::Active->value,
    ])->assertSessionHasNoErrors();

    // Not '' and not null-by-accident: `whereNull` has to be able to find this run.
    expect($run->refresh()->scenario)->toBeNull()
        ->and($run->hasScenario())->toBeFalse();
});

it('renders the scenario error on the Cockpit form that refused it', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active]);

    // `assertSessionHasErrors` is deliberately absent from this chain: reading the flashed bag
    // consumes it, and this test is about the message reaching the *next* request. The referer is the
    // Cockpit, which is where a failed submit from the header form lands.
    $this->withHeader('referer', route('runs.cockpit', $run))
        ->put(route('runs.update', $run), [
            'umamusume_id' => $run->umamusume_id,
            'scenario' => 'grand_masters',
            'status' => RunStatus::Active->value,
        ])->assertRedirect(route('runs.cockpit', $run));

    // One GET, not two: the first request after the failed PUT is the one that receives the flashed
    // bag. Inertia carries it as the shared `errors` prop, which is what the scenario form reads.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Cockpit')
                ->where('errors.scenario', fn ($message): bool => is_string($message) && $message !== '')
                // The stored scenario survives the refusal, so the select still shows it.
                ->where('run.scenario', 'ura_finale');
        });
});

it('reports the period the Trainer names through the header period form', function (): void {
    $run = cockpitRun(['scenario' => 'trackblazer', 'status' => RunStatus::Active], turns: 1, energy: 60);

    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => $run->status->value,
        'current_objective_index' => 3,
    ])->assertRedirect(route('runs.cockpit', $run));

    expect($run->refresh()->current_objective_index)->toBe(3);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The column is 1-based; the grade panel carries the same column, and the objectives list
            // is where that index meets a name.
            ->where('run.current_objective_index', 3)
            ->where('scenario.grade.current', 3)
            ->where('gradeObjectives', fn (Collection $objectives): bool => $objectives->contains(
                fn (array $objective): bool => $objective['index'] === 3
                    && $objective['name'] === 'End of Classic Year'
            )));
});

it('reports no live period until the Trainer names one through the header form', function (): void {
    $run = cockpitRun(['scenario' => 'trackblazer', 'status' => RunStatus::Active], turns: 1, energy: 60);

    // An unreported period renders as "no period reported", never "Working toward"; the props' null
    // is what selects it, and `gradeEarned()` is null rather than the sum of some period (D-220).
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.current_objective_index', null)
            ->where('scenario.grade.current', null)
            ->where('scenario.grade.earned', null)
            ->where('gradeObjectives', fn (Collection $objectives) => $objectives->isNotEmpty()));
});

it('refuses a period on a scenario that composes no grade objectives', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 60);

    // The header period form posts `current_objective_index` through `runs.update`, whose request
    // reads the composition matrix: a scenario without the panel has no period to report.
    $this->put(route('runs.update', $run), [
        'umamusume_id' => $run->umamusume_id,
        'scenario' => $run->scenario,
        'status' => $run->status->value,
        'current_objective_index' => 2,
    ])->assertSessionHasErrors('current_objective_index');

    expect($run->refresh()->current_objective_index)->toBeNull();
});

it('lists every logged turn so the correction can name any of them', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 3, energy: 60);

    // The correction's turn selector reads this list: every logged turn carries its own id and turn,
    // so `?edit_turn=<id>` can open any row rather than only the latest.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('correction.turns', 3)
            ->where('correction.turns.0.turn', 1)
            ->where('correction.turns.1.turn', 2)
            ->where('correction.turns.2.turn', 3));
});

it('saves a corrected turn through the correction form', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 60);
    $entry = $run->turnEntries()->sole();

    // The redirect returns to the page the form was posted from, so the Cockpit is seeded as the
    // referer: the same URL the correction form lives on.
    $this->from(route('runs.cockpit', $run))
        ->put(route('runs.turns.update', [$run, $entry]), [
            'turn' => 1, 'speed' => 140, 'stamina' => 95, 'power' => 115, 'guts' => 85, 'wit' => 99,
            'sp' => 20, 'condition' => 'NORMAL', 'energy' => 55, 'mood' => 'NORMAL', 'fans' => 1500,
        ])
        ->assertRedirect(route('runs.cockpit', $run))
        ->assertSessionHas('status', 'Turn 1 updated.');

    expect($entry->refresh()->speed)->toBe(140)
        ->and($entry->condition)->toBe('NORMAL')
        ->and(TurnEntry::where('training_run_id', $run->id)->count())->toBe(1);
});

it('deletes a turn through the correction form', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);
    $doomed = $run->turnEntries()->reorder('turn', 'desc')->first();

    $this->delete(route('runs.turns.destroy', [$run, $doomed]))
        ->assertRedirect(route('runs.cockpit', $run))
        ->assertSessionHas('status', 'Turn 2 removed.');

    expect(TurnEntry::where('training_run_id', $run->id)->pluck('turn')->all())->toBe([1]);
});

it('keeps the turn delete off the page until the row is open', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 60);
    $entry = $run->turnEntries()->sole();

    // Two-stepping the destructive turn action: opening a row is a navigation, and a navigation
    // cannot fire the delete because the destroy route answers DELETE only.
    $this->get(route('runs.cockpit', $run).'?edit_turn='.$entry->id)->assertOk();

    $this->get(route('runs.turns.destroy', [$run, $entry]))->assertStatus(405);

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
});

it('will not delete another run\'s turn from this run\'s Cockpit', function (): void {
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 1, energy: 60);
    $other = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Active], turns: 2, energy: 60);
    $foreign = $other->turnEntries()->reorder('turn', 'desc')->first();

    // This app has no accounts, so "the user owns it" is the nested scope: a turn id from another
    // run is not a row of this run and answers 404.
    $this->delete(route('runs.turns.destroy', [$run, $foreign]))->assertNotFound();

    expect($foreign->refresh()->turn)->toBe(2)
        ->and(TurnEntry::where('training_run_id', $run->id)->count())->toBe(1);
});

/*
 * `turn_events` is keyed on `(training_run_id, turn)` and has no `turn_entry_id` foreign key, so a
 * failure event outlives the turn row that produced it. Deleting a failed turn therefore has to take
 * the failure event with it, and only that type: `storePurchase` writes `Scenario` events on the very
 * same key, so the scoped delete is the narrow one on purpose.
 */

function failedTurn(TrainingRun $run, int $turn): TurnEntry
{
    test()->post(route('runs.turns.store', $run), [
        'turn' => $turn,
        'speed' => 120, 'stamina' => 110, 'power' => 130, 'guts' => 100, 'wit' => 95,
        'sp' => 20, 'energy' => 70, 'mood' => 'GREAT', 'fans' => 1200,
        'choice' => 'training-Speed', 'outcome' => 'Failure', 'penalty_kind' => 'energy',
        'stage' => 'confirm', 'previewed' => '1',
    ])->assertRedirect(route('runs.cockpit', $run));

    return TurnEntry::query()->where('training_run_id', $run->id)->where('turn', $turn)->sole();
}

it('deletes the failure event together with the turn that recorded it', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $entry = failedTurn($run, 5);

    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(1);

    $this->delete(route('runs.turns.destroy', [$run, $entry]))->assertRedirect(route('runs.cockpit', $run));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0)
        ->and(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0);
});

it('leaves another turn\'s event alone when one turn is deleted', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $doomed = failedTurn($run, 5);
    $kept = failedTurn($run, 6);

    $this->delete(route('runs.turns.destroy', [$run, $doomed]))->assertRedirect(route('runs.cockpit', $run));

    // The delete is by `(run, turn)`, so the row beside it has to survive untouched. Without this
    // the fix above could pass on a delete that cleared the whole run's events.
    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 6)->count())->toBe(1)
        ->and($kept->refresh()->turn)->toBe(6);
});

it('does not hand a re-logged turn the failed chip of the turn that used the number before', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $doomed = failedTurn($run, 5);

    $this->delete(route('runs.turns.destroy', [$run, $doomed]))->assertRedirect(route('runs.cockpit', $run));

    // The same number, logged again as a success this time. The new turn writes no event, so the
    // only way this run can come out clean is the store path clearing what the old turn left.
    $this->post(route('runs.turns.store', $run), [
        'turn' => 5,
        'speed' => 120, 'stamina' => 110, 'power' => 130, 'guts' => 100, 'wit' => 95,
        'sp' => 20, 'energy' => 70, 'mood' => 'GREAT', 'fans' => 1200,
        'choice' => 'training-Speed', 'outcome' => 'Success',
        'stage' => 'confirm', 'previewed' => '1',
    ])->assertRedirect(route('runs.cockpit', $run));

    expect(TurnEvent::query()->where('training_run_id', $run->id)->where('turn', 5)->count())->toBe(0);
});
