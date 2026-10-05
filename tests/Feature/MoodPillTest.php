<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Mood is recorded, validated and stored, and until this slice it was never shown: the
 * timeline had no Mood column and the run's state region said nothing about it. A Trainer
 * who logs `AWFUL` and then cannot see it has a field, not a readout.
 *
 * Two rules shape what these tests demand:
 *   D-259 - the arrow is not decoration. Colour plus word without the directional glyph is
 *           a review failure, because the three derived tier colours sit 1.0-3.3 degrees
 *           apart at equal luminance, so hue carries no ordinal information at all.
 *   D-220 - an unrecorded tier renders as unrecorded, never as a default tier and never as
 *           `NORMAL`, which would be a claim about a trainee whose mood nobody entered.
 *
 * The pair measurements these tokens have to survive live in
 * `docs/design-research/verification/slice-6-2026-09-28.md`, taken from the rendered
 * element per D-288, not from the hexes in this file.
 *
 * On the Inertia port the recorded tier travels as `turns[].mood` and `currentMood`, and the five
 * tier words with their arrows as `moodOptions` (MoodTier::arrow()); the pill's own fills, its
 * glyphs, and which element prints them are the component's.
 */
function moodRun(): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale']);
}

function moodTurn(TrainingRun $run, int $turn, ?string $mood): TurnEntry
{
    // create(), not the factory: a factory runs unguarded past #[Fillable].
    return TurnEntry::create(array_merge([
        'training_run_id' => $run->id,
        'turn' => $turn,
        'speed' => 480,
        'stamina' => 300,
        'power' => 355,
        'guts' => 210,
        'wit' => 95,
        'sp' => 12,
        'energy' => 74,
        'fans' => 1943,
        'mood' => $mood,
    ], []));
}

it('declares the five mood tier tokens and one ink for them', function (): void {
    $css = (string) file_get_contents(base_path('resources/css/app.css'));

    // The tier colours are the client's own pill colours (research §3.1): two measured,
    // three derived at the anchors' luminance and still provisional. They exist as tokens
    // so no component ever reaches for a raw hex, and `--color-on-mood` exists because the
    // theme's own dark ink fails on the lowest of them.
    expect($css)->toContain('--color-mood-great')
        ->and($css)->toContain('--color-mood-good')
        ->and($css)->toContain('--color-mood-normal')
        ->and($css)->toContain('--color-mood-bad')
        ->and($css)->toContain('--color-mood-awful')
        ->and($css)->toContain('--color-on-mood');
});

it('renders a recorded mood tier in the timeline as a pill wearing its own token', function (): void {
    $run = moodRun();
    moodTurn($run, 1, MoodTier::Great->value);

    // The tier reaches the timeline as the row's own `mood`; that it draws `bg-mood-great` and
    // `text-on-mood` is the pill's, carried in the browser spec.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->has('turns', 1)
        ->where('turns.0.mood', MoodTier::Great->value));
});

it('renders the directional arrow beside the tier word, never colour alone', function (): void {
    $run = moodRun();
    moodTurn($run, 1, MoodTier::Awful->value);

    // D-259: the glyph is the only ordinal signal in the component. A pill that prints
    // `AWFUL` without the down arrow has thrown away the ordering.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('turns.0.mood', 'AWFUL')
        ->where('moodOptions', fn (Collection $options) => $options->contains(
            fn (array $option): bool => $option['value'] === 'AWFUL' && $option['arrow'] === '↓'
        )));
});

it('gives every tier the arrow the client\'s panel gives it', function (): void {
    $arrows = [
        'GREAT' => '↑',
        'GOOD' => '↑',
        'NORMAL' => '→',
        'BAD' => '↓',
        'AWFUL' => '↓',
    ];

    foreach ($arrows as $tier => $arrow) {
        $run = moodRun();
        moodTurn($run, 1, $tier);

        $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('turns.0.mood', $tier)
            ->where('moodOptions', fn (Collection $options) => $options->contains(
                fn (array $option): bool => $option['value'] === $tier && $option['arrow'] === $arrow
            )));
    }
});

it('says the mood was not recorded instead of picking a tier', function (): void {
    $run = moodRun();
    moodTurn($run, 1, null);

    // No tier may be read for a turn that stored nothing, and the state region may not fall back
    // either: a default would be a claim about the trainee, and D-220 says absent beats empty.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->whereNull('turns.0.mood')
        ->whereNull('currentMood'));
});

it('shows the run\'s current mood in the pinned state region, from the latest logged turn', function (): void {
    $run = moodRun();
    moodTurn($run, 1, MoodTier::Great->value);
    moodTurn($run, 2, MoodTier::Bad->value);

    // The run's mood is where the last turn ended, not an average and not turn 1 (D-220).
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('currentMood', MoodTier::Bad->value));
});

it('keeps the tier a select, because a pill is a readout and not an input', function (): void {
    $run = moodRun();

    // The five client words travel as the option list a select renders; MoodPill is the readout.
    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('moodOptions', fn (Collection $options) => $options->pluck('value')->all() === [
            'GREAT', 'GOOD', 'NORMAL', 'BAD', 'AWFUL',
        ]));
});
