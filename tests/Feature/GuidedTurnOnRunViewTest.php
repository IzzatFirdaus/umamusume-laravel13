<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * The guided turn flow and the stat band, mounted on a real run.
 *
 * The review's reframe was that `x-stat-band` and `x-guided-step` are built, tested and
 * measured clean, and the defect is one thing: `design-preview` is the only surface that
 * renders them. These
 * tests are the other half of that fix - a component nobody can reach has no behaviour to
 * guard, so the guards live on the route a Trainer actually uses.
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

/**
 * The grade letters the mounted band printed, in DOM order.
 *
 * Matched on the badge span rather than as a substring: "B" is inside "B+", and a plain
 * contains() would pass a band that printed nothing.
 *
 * @return list<string>
 */
function mountedBandLetters(string $html): array
{
    preg_match_all(
        '/class="[^"]*bg-grade-[a-z]+[^"]*"[^>]*>\s*([A-Z][+]?)\s*</',
        $html,
        $matches,
    );

    return $matches[1];
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

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // 550/50 = index 11 of the seventeen labels, so Speed must read B+; the same row
    // printed from turn 1 would say C+. The band takes the latest, not the first.
    expect(mountedBandLetters($html))->toBe(['B+', 'B', 'A', 'G+', 'G'])
        ->and($html)->toContain('550');
});

it('mounts no stat band for a run that has logged no turns', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);
    TurnEntry::query()->where('training_run_id', $run->id)->delete();

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // Five zeroes would be a claim about a trainee nobody entered (D-220). The section
    // is absent, and the page says what to do instead (R-27).
    expect($html)->not->toContain('bg-grade-')
        ->and($html)->toContain('No turns logged yet');
});

it('puts the guided rail ahead of the raw form and hides the raw form behind a disclosure', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // D-53: the escape hatch is reachable, it is not the default. Position is the proof:
    // the rail renders first and `condition`, which only the raw form carries, sits
    // inside the <details>.
    $rail = strpos($html, 'role="radiogroup"');
    $details = strpos($html, '<details');
    $rawField = strpos($html, 'name="condition"');
    $detailsEnd = strpos($html, '</details>');

    expect($rail)->not->toBeFalse()
        ->and($details)->not->toBeFalse()
        ->and($rail)->toBeLessThan($details)
        ->and($rawField)->toBeGreaterThan($details)
        ->and($rawField)->toBeLessThan($detailsEnd)
        ->and($html)->toContain('Correct a turn by hand');
});

it('previews a turn without writing it', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $response = $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run));

    $response->assertOk()->assertSee('Preview', false);

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);

    // The deltas are the entered value minus the stored one, in the two prose colours
    // with the words present: orange up, blue down, never colour alone (D-12).
    $html = $response->getContent();

    expect($html)->toMatch('/text-up[^>]*>\s*\+70 Speed\s*</')
        ->and($html)->toMatch('/text-down[^>]*>\s*-14 Energy\s*</')
        ->and($html)->toContain('Confirm turn');
});

it('refuses to confirm a turn that was never previewed', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run, ['stage' => 'confirm']))
        ->assertSessionHasErrors('previewed');

    expect(TurnEntry::query()->where('training_run_id', $run->id)->count())->toBe(1);
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

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // A failure is a first-class state, not a zero (D-200, D-153). The word carries the
    // colour's meaning, so the chip survives colour-blindness and a screen reader.
    expect($html)->toContain('Failed')
        ->and($html)->toMatch('/penalt[a-z]*[^<]*stat/i');
});

it('offers the five client mood strings with their arrows', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // D-259 makes the glyph part of the component: the three derived mood colours are
    // not separable by hue, so the arrow is the only ordinal signal.
    foreach (['GREAT ↑', 'GOOD ↑', 'NORMAL →', 'BAD ↓', 'AWFUL ↓'] as $option) {
        expect($html)->toContain($option);
    }

    expect($html)->not->toContain('Practice Poor')
        ->and($html)->not->toContain('Peak');
});

it('defaults the mood choice to the mood of the previous turn', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['mood' => 'GOOD']);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    expect($html)->toMatch('/<option value="GOOD"[^>]*selected/');
});

it('renders the energy band word that the value puts it in', function (int $energy, string $word): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => $energy]);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    expect($html)->toContain($word);
})->with([
    [74, 'Safe'],
    [42, 'Caution'],
    [18, 'Danger'],
]);

it('attributes the Danger boundary to this tool, not to the game', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => 18]);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // D-204: 50 is the only sourced Energy threshold. A Danger band below 30 is an owner
    // preference and has to say so wherever it renders.
    expect($html)->toMatch('/30[^<]{0,80}(this tool|not a game|no source|own ruling)/i');
});

it('advises under 50 and names Wit, and stays quiet above it', function (int $energy, bool $advises): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => $energy]);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    expect(str_contains($html, 'Wit costs 0 Energy'))->toBe($advises);
})->with([
    [74, false],
    [42, true],
    [18, true],
]);

it('gives green-tint its committed consumer in the Safe state', function (): void {
    $run = guidedRun();
    guidedTurn($run, 1, ['energy' => 74]);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // R23 promised this token a real consumer instead of retirement. KI-11's open half
    // closes only if the Safe band actually renders on a Trainer-facing surface.
    expect($html)->toContain('bg-green-tint');
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

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // The deferred accessibility finding was that the rail declared `role="radiogroup"`
    // over children that were `role="radio"` buttons: a claim the element did not support,
    // and one that carried no value on submit either. Every choice is now a radio input
    // inside the banner the design mandates, so the group is telling the truth.
    $expected = count(config('scenarios.stat_order')) + 2;

    expect($html)->toContain('role="radiogroup"')
        ->and($html)->not->toContain('role="radio"')
        ->and(substr_count($html, '<input type="radio" name="choice"'))->toBe($expected);
});

it('names no scenario in the run view or the rail it passes down', function (): void {
    $view = (string) file_get_contents(base_path('resources/views/runs/show.blade.php'));
    $rail = (string) file_get_contents(base_path('resources/views/components/guided-step.blade.php'));

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

    $html = $this->post('/training-runs/'.$run->id.'/turns', previewPayload($run))->getContent();

    // Zero new JS means every value the second stage needs is in the first response:
    // re-posting the rail must not depend on anything the browser computed.
    foreach (['speed', 'stamina', 'power', 'guts', 'wit', 'energy', 'fans', 'choice', 'turn'] as $field) {
        expect($html)->toContain('name="'.$field.'"');
    }

    expect($html)->toContain('name="previewed"');
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

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // The first turn has no previous row, so there are no deltas to preview and no mood
    // to default from. The rail must still offer the door (D-50) and say what it cannot
    // yet show, and the turn number must be 1 rather than a borrowed default.
    expect($html)->toContain('role="radiogroup"')
        ->and($html)->toMatch('/name="turn"[^>]*value="1"/')
        ->and($html)->toContain('no turn to compare against');
});
