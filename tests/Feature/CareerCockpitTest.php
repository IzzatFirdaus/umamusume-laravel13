<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RunStatus;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
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
            ->where('run.run_url', route('runs.show', $run))
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
            // The five contract fields and nothing else: a score or a numeric confidence would be a
            // sixth key, and neither is sourced (plan §8 "Recommendation contract (D8)", ADR-0001 §3).
            ->has('advisor', fn (Assert $advisor) => $advisor
                ->hasAll(['action', 'band', 'reasons', 'alternative', 'risk'])));
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

it('reports the cockpit as an Inertia page for a retired run too, with no write routes invented', function (): void {
    // The cockpit is a read surface over any run, whatever its status: nothing here gates on Active.
    $run = cockpitRun(['scenario' => 'ura_finale', 'status' => RunStatus::Completed], turns: 2, energy: 70);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Cockpit')
            ->where('run.status', 'Completed'));
});
