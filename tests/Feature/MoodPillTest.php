<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

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

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    expect($html)->toContain('bg-mood-great')
        ->and($html)->toContain('text-on-mood');
});

it('renders the directional arrow beside the tier word, never colour alone', function (): void {
    $run = moodRun();
    moodTurn($run, 1, MoodTier::Awful->value);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // D-259: the glyph is the only ordinal signal in the component. A pill that prints
    // `AWFUL` in a rose fill without the down arrow has thrown away the ordering.
    expect(pillText($html, 'awful'))->toBe('AWFUL↓');
});

it('gives every tier the arrow the client\'s panel gives it', function (): void {
    $arrows = [
        'great' => 'GREAT↑',
        'good' => 'GOOD↑',
        'normal' => 'NORMAL→',
        'bad' => 'BAD↓',
        'awful' => 'AWFUL↓',
    ];

    foreach ($arrows as $token => $expected) {
        $run = moodRun();
        moodTurn($run, 1, strtoupper($token));

        $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

        expect(pillText($html, $token))->toBe($expected);
    }
});

/**
 * The pill's own visible text, read out of the rendered document rather than matched
 * across markup: the arrow lives in a nested span so a regex over the HTML would be
 * asserting a shape instead of what a Trainer reads.
 *
 * @return string the text of the first `bg-mood-{$token}` element in the timeline
 */
function pillText(string $html, string $token): string
{
    $doc = new DOMDocument;
    @$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    $xpath = new DOMXPath($doc);

    $pill = $xpath->query('//*[@class][contains(concat(" ", normalize-space(@class), " "), " bg-mood-'.$token.' ")]')->item(0);

    expect($pill)->not->toBeNull();

    return (string) $pill->textContent;
}

it('says the mood was not recorded instead of picking a tier', function (): void {
    $run = moodRun();
    moodTurn($run, 1, null);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    // No `bg-mood-*` fill may appear for a turn that stored nothing: a default tier would
    // be a claim about the trainee, and D-220 says absent beats empty.
    expect($html)->not->toMatch('/bg-mood-(great|good|normal|bad|awful)/')
        ->and(strip_tags($html))->toContain('not recorded');
});

it('shows the run\'s current mood in the pinned state region, from the latest logged turn', function (): void {
    $run = moodRun();
    moodTurn($run, 1, MoodTier::Great->value);
    moodTurn($run, 2, MoodTier::Bad->value);

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();
    $doc = new DOMDocument;
    @$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    $xpath = new DOMXPath($doc);

    $state = $xpath->query('//section[@aria-label="Run state"]')->item(0);
    expect($state)->not->toBeNull();

    $pills = $xpath->query('.//span[contains(@class, "bg-mood-")]', $state);
    // The run's mood is where the last turn ended, not an average and not turn 1 (D-220).
    expect($pills->length)->toBe(1)
        ->and($pills->item(0)->getAttribute('class'))->toContain('bg-mood-bad');
});

it('keeps the tier a select, because a pill is a readout and not an input', function (): void {
    $run = moodRun();

    $html = $this->get('/training-runs/'.$run->id)->assertOk()->getContent();

    expect($html)->toContain('<select name="mood"');
});
