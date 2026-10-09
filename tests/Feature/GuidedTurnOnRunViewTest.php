<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The guided turn flow and the stat band, mounted on a real run.
 *
 * The review's reframe was that `x-stat-band` and `x-guided-step` are built, tested and
 * measured clean, and the defect was that only a preview surface rendered them. These
 * tests are the other half of that fix - a component nobody can reach has no behaviour to
 * guard, so the guards live on the route a Trainer actually uses. That preview surface
 * (`design-preview`) has since been deleted, so this file exercises the run screen only.
 *
 * The screen is Inertia now (ADR-0020), so these assert the `rail` prop the Vue rail
 * renders from. The rendered half - the radiogroup, the choice cards, the delta colours,
 * the energy chip words, the first-turn note - lives in tests/browser/run-detail.spec.ts.
 *
 * Two rules decide most of what is asserted here:
 *   D-51 - committing is a separate action from selecting, and "preview before commit" is
 *          enforced by the server, not by a script, because T1c ships zero new JS.
 *   D-220 - a value the run has not recorded is absent, not a zero. A band built from no
 *           turns would print five zeroes about a trainee who has never trained.
 *
 * The numbers are the R17 fixture's: turn 2 is 480 -> 550 on Speed, which is +70, and
 * 88 -> 74 on Energy, which is -14. Both are read out of entered rows, so the preview is
 * explainable from the turns (Planner Rule 5) rather than projected.
 */
function guidedRun(?string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => $scenario]);

    return $run;
}

function guidedTurn(TrainingRun $run, int $turn, array $overrides = []): TurnEntry
{
    // create(), not the factory: a factory runs unguarded, so it would write keys the
    // model's #[Fillable] rejects and this test would prove nothing about writability.
    return TurnEntry::create(array_merge([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'speed' => 480,
        'stamina' => 300,
        'power' => 355,
        'guts' => 210,
        'wit' => 95,
        'sp' => 240,
        'energy' => 88,
        'mood' => 'NORMAL',
        'fans' => 9000,
    ], $overrides));
}

function previewPayload(TrainingRun $run, array $overrides = []): array
{
    return array_merge([
        'turn' => 2,
        'speed' => 550,
        'stamina' => 525,
        'power' => 601,
        'guts' => 75,
        'wit' => 0,
        'sp' => 190,
        'energy' => 74,
        'mood' => 'GREAT',
        'fans' => 12400,
        'choice' => 'training-Speed',
        'outcome' => 'Success',
        'stage' => 'preview',
    ], $overrides);
}

it('mounts the stat band from the latest entered turn of the run', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);
    guidedTurn($run, 2, ['speed' => 550, 'stamina' => 525, 'power' => 601, 'guts' => 75, 'wit' => 0]);

    // Turn 1 holds 480 on Speed and turn 2 holds 550, so a band built from turn 1 fails
    // here: the band takes the latest, not the first. The grade letters it derives from
    // these values (550/50 = index 11 of the seventeen labels, so Speed reads B+) live in
    // StatBand.vue and are pinned in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('band.values', [
                'Speed' => 550, 'Stamina' => 525, 'Power' => 601, 'Guts' => 75, 'Wit' => 0,
            ]));
});

it('mounts no stat band for a run that has logged no turns', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);
    TurnEntry::query()->where('training_run_id', $run->id)->delete();

    // Five zeroes would be a claim about a trainee nobody entered (D-220), so the band
    // payload is null. The empty log is also what renders the page's "No turns logged yet"
    // copy (R-27), in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('band', null)
            ->has('turns', 0));
});

it('puts the guided rail ahead of the raw form and hides the raw form behind a disclosure', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    // D-53: the escape hatch is reachable, it is not the default. The rail is what the page
    // offers first, assembled server-side. Its rendered position, the <details> wrapper and
    // the 'Correct a turn by hand' summary are in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('rail.choices', 7)
            ->where('rail.current', 'training')
            ->where('rail.turn', 2)
            ->where('rail.action', route('runs.turns.store', $run)));
});

it('previews a turn without writing it', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $payload = previewPayload($run);

    test()->post('/training-runs/'.$run->id.'/turns', $payload)
        ->assertRedirect(route('runs.show', $run));

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);

    // PRG: the previewed screen lives on the redirect target, and the carry below is the
    // flashed input plus the `previewed` flag the controller adds, which is exactly what
    // the browser arrives with. Each delta is entered minus stored with its own direction:
    // orange up, blue down, never colour alone (D-12). The panel heading, the 'Confirm
    // turn' control and the colours are in tests/browser/run-detail.spec.ts.
    test()->withSession(['_old_input' => $payload + ['previewed' => '1']])
        ->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.previewed', true)
            ->where('rail.current', 'outcome')
            ->where('rail.preview', [
                ['direction' => 'up', 'text' => '+70 Speed'],
                ['direction' => 'up', 'text' => '+225 Stamina'],
                ['direction' => 'up', 'text' => '+246 Power'],
                ['direction' => 'down', 'text' => '-135 Guts'],
                ['direction' => 'down', 'text' => '-95 Wit'],
                ['direction' => 'down', 'text' => '-50 Skill Points'],
                ['direction' => 'down', 'text' => '-14 Energy'],
                ['direction' => 'up', 'text' => '+3400 Fans'],
                ['direction' => 'up', 'text' => '+2 Mood'],
            ]));
});

it('refuses to confirm a turn that was never previewed', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run, ['stage' => 'confirm']))
        ->assertSessionHasErrors('previewed');

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
});

it('reads a mood drop as a drop, whatever the enum order says', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['mood' => 'GREAT']);

    $payload = previewPayload($run, ['mood' => 'BAD']);

    test()->post('/training-runs/'.$run->id.'/turns', $payload)
        ->assertRedirect(route('runs.show', $run));

    // MoodTier::cases() is ordered best to worst, so subtracting in index order reports
    // GREAT -> BAD as +3 and paints a mood collapse in the colour of a gain. The browser
    // pass caught this on the rendered preview; the deltas above are the same arithmetic.
    test()->withSession(['_old_input' => $payload + ['previewed' => '1']])
        ->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.preview', fn (Collection $deltas): bool => $deltas->contains(
                fn (array $delta): bool => $delta['direction'] === 'down' && $delta['text'] === '-3 Mood'
            ) && ! $deltas->contains(fn (array $delta): bool => $delta['text'] === '+3 Mood')));
});

it('confirms a previewed turn and stores it', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run, ['stage' => 'confirm', 'previewed' => '1']))
        ->assertRedirect();

    $this->assertDatabaseHas('turn_entries', [
        'training_run_id' => $run->id,
        'turn' => 2,
        'speed' => 550,
        'energy' => 74,
        'mood' => 'GREAT',
    ]);
});

it('records a failed turn as an event and names the penalty kind on the timeline', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run, [
        'stage' => 'confirm',
        'previewed' => '1',
        'outcome' => 'Failure',
        'penalty_kind' => 'stat',
    ]))->assertRedirect();

    $this->assertDatabaseHas('turn_events', [
        'training_run_id' => $run->id,
        'turn' => 2,
        'event_type' => 'Failure',
    ]);

    // A failure is a first-class state, not a zero (D-200, D-153): it is folded onto the
    // turn row (ADR-0003) as `failure`, which is what the 'Failed' chip renders from. The
    // chip word and the penalty wording are in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('turns', function (Collection $rows): bool {
                $turn = $rows->firstWhere('turn', 2);

                return is_array($turn) && $turn['failure'] === [
                    'penalty_kind' => 'stat',
                    'source_name' => 'Speed',
                ];
            }));
});

it('offers the five client mood strings with their arrows', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    // D-259 makes the glyph part of the component: the three derived mood colours are
    // not separable by hue, so the arrow is the only ordinal signal. The rail's select
    // binds to exactly this list, and the option text it renders is in
    // tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('moodOptions', [
                ['value' => 'GREAT', 'arrow' => '↑'],
                ['value' => 'GOOD', 'arrow' => '↑'],
                ['value' => 'NORMAL', 'arrow' => '→'],
                ['value' => 'BAD', 'arrow' => '↓'],
                ['value' => 'AWFUL', 'arrow' => '↓'],
            ]));
});

it('defaults the mood choice to the mood of the previous turn', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['mood' => 'GOOD']);

    // The select's selected option binds to `rail.mood`, which is the latest stored tier
    // when nothing is staged.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('rail.mood', 'GOOD'));
});

it('renders the energy band word that the value puts it in', function (int $energy): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => $energy]);

    // The Safe/Caution/Danger words and their 50/30 boundaries are derived in
    // GuidedStep.vue from this reading, so the band chip itself is in
    // tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('rail.energy', $energy));
})->with([
    74, // Safe
    42, // Caution
    18, // Danger
]);

it('attributes the Danger boundary to this tool, not to the game', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => 18]);

    // D-204: 50 is the only sourced Energy threshold. A Danger band below 30 is an owner
    // preference and has to say so wherever it renders; the rail's own note renders in
    // GuidedStep.vue from the reading below, and its wording is in
    // tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('rail.energy', 18));
});

it('advises under 50 and names Wit, and stays quiet above it', function (int $energy): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => $energy]);

    // The hint renders in GuidedStep.vue from this reading being below 50; its 'Wit costs
    // 0 Energy' wording is in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('rail.energy', $energy));
})->with([
    74, // no hint: above the sourced 50 line
    42, // hint
    18, // hint
]);

it('gives green-tint its committed consumer in the Safe state', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => 74]);

    // R23 promised this token a real consumer instead of retirement. KI-11's open half
    // closes only if the Safe band actually renders on a Trainer-facing surface: the
    // rail's Safe state (above 50) is the one that carries bg-green-tint, in
    // GuidedStep.vue, pinned in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('rail.energy', 74));
});

it('still accepts a raw escape-hatch entry with none of the rail fields', function (): void {
    $run = guidedRun();

    // The five new keys are conditional on `stage`, and this is the proof they stayed
    // conditional: the escape hatch posts the eight fields it always posted, writes on
    // submit, and shows no preview (D-53). Widening the FormRequest for the rail must not
    // have quietly closed the door it exists to keep open.
    $this->post('/training-runs/'.$run->id.'/turns', [
        'turn' => 1,
        'speed' => 210,
        'stamina' => 180,
        'power' => 95,
        'guts' => 120,
        'wit' => 60,
        'sp' => 30,
        'condition' => 'Good',
    ])->assertRedirect();

    $this->assertDatabaseHas('turn_entries', [
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 210,
    ]);

    expect($run->turnEvents()->count())->toBe(0);
});

it('gives the choice group the semantics it claims', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    // The deferred accessibility finding was that the rail declared `role="radiogroup"`
    // over children that were `role="radio"` buttons: a claim the element did not support,
    // and one that carried no value on submit either. Every choice is now a radio input
    // inside the banner the design mandates, built one-per-choice from this payload; the
    // group itself is in tests/browser/run-detail.spec.ts.
    $expected = count(config('scenarios.stat_order')) + 2;

    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('rail.choices', $expected)
            ->where('rail.choices', fn (Collection $choices): bool => $choices->pluck('key')->all() === [
                'training-Speed', 'training-Stamina', 'training-Power', 'training-Guts', 'training-Wit', 'rest', 'mood',
            ]));
});

it('names no scenario in the run view or the rail it passes down', function (): void {
    // The run screen is Inertia now, so the view and the rail are these two sources; the
    // retired Blade pair is no longer rendered by anything.
    $view = (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));
    $rail = (string) file_get_contents(base_path('resources/js/components/GuidedStep.vue'));

    // D-240: the run view composes from config. A scenario literal here is the smell the
    // whole component set exists to avoid.
    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($view)->not->toContain($key)
            ->and($rail)->not->toContain($key);
    }
});

it('carries the turn being staged across both stages without a script', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $payload = previewPayload($run);

    test()->post('/training-runs/'.$run->id.'/turns', $payload)
        ->assertRedirect(route('runs.show', $run));

    // Zero new JS means every value the second stage needs is in the first response:
    // re-posting the rail must not depend on anything the browser computed. `previewed`
    // is the flag the confirm control renders from.
    test()->withSession(['_old_input' => $payload + ['previewed' => '1']])
        ->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.values', [
                'turn' => 2,
                'speed' => 550,
                'stamina' => 525,
                'power' => 601,
                'guts' => 75,
                'wit' => 0,
                'sp' => 190,
                'energy' => 74,
                'mood' => 'GREAT',
                'fans' => 12400,
                'choice' => 'training-Speed',
                'outcome' => 'Success',
            ])
            ->where('rail.selected', 'training-Speed')
            ->where('rail.previewed', true));
});

it('rejects a failure with no penalty kind and says which step caused it', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run, [
        'stage' => 'confirm',
        'previewed' => '1',
        'outcome' => 'Failure',
        'penalty_kind' => null,
    ]))->assertSessionHasErrors('penalty_kind');

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
});

it('renders the rail with no turn rows at all rather than inventing a first turn', function (): void {
    $run = guidedRun();

    // The first turn has no previous row, so there are no deltas to preview and no mood
    // to default from. The rail must still offer the door (D-50) and say what it cannot
    // yet show, and the turn number must be 1 rather than a borrowed default. The note
    // itself is in tests/browser/run-detail.spec.ts.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.turn', 1)
            ->where('rail.has_previous', false)
            ->where('rail.previous', null)
            ->where('rail.preview', [])
            ->where('rail.mood', null)
            ->where('rail.energy', null)
            ->has('rail.choices', 7));
});

it('counts the rail\'s indicator against its own two stages, not the scenario vocabulary (D7)', function (): void {
    // `def.steps` names the scenario's turn vocabulary — Unity Cup has five — and the rail only ever
    // lands on two of them, so counting the indicator against that list read "Step 2 of 5" and then
    // "Step 4 of 5" with no step 3. The flow is what the rail actually offers, and `current` is
    // always a member of it.
    $run = guidedRun('unity_cup');

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.flow', ['training', 'outcome'])
            ->where('rail.current', 'training')
            // The scenario vocabulary is still sent, and it is still longer than the flow.
            ->has('rail.def.steps', 5));

    // Stage two: the flow's second entry, so the indicator reads "Step 2 of 2".
    test()->post(route('runs.turns.store', $run), previewPayload($run))->assertRedirect();

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('rail.current', 'outcome')
            ->where('rail.flow', ['training', 'outcome']));
});
