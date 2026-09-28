<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RunStatus;
use App\Models\Preference;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/*
 * Pins the four behaviors the 2026-09-28 frontend audit reported, one test per
 * finding. Each of these fails on master at 7d4b8cf and passes on the fix
 * commit; they are not general UI snapshots.
 */

function auditRun(?string $scenario = 'ura_finale'): TrainingRun
{
    $run = TrainingRun::factory()->create([
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => $scenario,
        'status' => RunStatus::Active,
    ]);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 480,
        'stamina' => 300,
        'power' => 355,
        'guts' => 210,
        'wit' => 95,
        'sp' => 240,
        'condition' => null,
        'energy' => 74,
        'fans' => 41250,
        'mood' => MoodTier::Good,
    ]);

    return $run;
}

/*
 * The scenario picker is a control, not a claim: it must keep listing every
 * scenario, including the one the run does not use. So "URA Finale" is allowed
 * inside an <option> and nowhere else, and the assertions strip those first.
 */
function auditCopyOf(string $html): string
{
    return preg_replace('#<option\b[^>]*>.*?</option>#s', '', $html);
}

it('stops naming a scenario the run never chose, on the strip and the rail (F-3)', function (): void {
    $html = auditCopyOf(test()->get('/training-runs/'.auditRun(null)->id)->assertOk()->getContent());

    expect($html)->not->toContain('URA Finale')
        ->and($html)->toContain('no scenario set');
});

it('still names the scenario the Trainer did choose (F-3, no over-correction)', function (): void {
    $html = auditCopyOf(test()->get('/training-runs/'.auditRun('ura_finale')->id)->assertOk()->getContent());

    expect($html)->toContain('URA Finale')
        ->and($html)->not->toContain('no scenario set');
});

it('exports the energy, mood and fans it logs and displays (F-9)', function (): void {
    $run = auditRun();

    $csv = test()->get("/training-runs/{$run->id}/export/csv")->assertOk()->getContent();
    $json = test()->get("/training-runs/{$run->id}/export/json")->assertOk()->getContent();

    $header = 'turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans';
    expect($csv)->toStartWith($header)
        // The original eight columns keep their positions, so anything keying on
        // them is unaffected; the three new ones are appended (audit note).
        ->and(str($csv)->explode("\n")->get(1))->toBe('1,480,300,355,210,95,240,,74,GOOD,41250');

    $turn = json_decode($json, true)['data']['turns'][0];
    expect($turn)->toMatchArray(['energy' => 74, 'mood' => 'GOOD', 'fans' => 41250]);
});

it('renders a missing page inside the product rather than as framework output (F-10)', function (): void {
    test()->get('/training-runs/424242')
        ->assertStatus(404)
        ->assertSee('Nothing to show here')
        ->assertSee('Skip to content')
        ->assertSee('Training runs');

    test()->get('/umamusume/there-is-no-such-trainee')
        ->assertStatus(404)
        ->assertSee('Nothing to show here');
});

it('lands inside the product and honors the stored theme (F-1)', function (): void {
    Preference::put('theme', 'dark');

    $html = test()->get('/')->assertOk()->getContent();

    expect($html)->toContain('data-theme="dark"')
        // The skeleton's deploy path, which PRD §6.10 cuts outright.
        ->and($html)->not->toContain('cloud.laravel.com')
        ->and($html)->not->toContain('Deploy now')
        ->and($html)->not->toMatch('/#[0-9a-fA-F]{6}/')
        ->and($html)->not->toContain('fonts.bunny.net');
});

it('reaches a preview by redirect, so refreshing it is not a 405 (F-7)', function (): void {
    $run = auditRun();

    test()->post("/training-runs/{$run->id}/turns", [
        'stage' => 'preview',
        'turn' => 2,
        'speed' => 520,
        'stamina' => 300,
        'power' => 355,
        'guts' => 210,
        'wit' => 95,
        'sp' => 240,
        'energy' => 60,
        'fans' => 43000,
        'mood' => 'NORMAL',
        'choice' => 'training-speed',
        'outcome' => 'Success',
    ])->assertRedirect(route('runs.show', $run));

    // A preview never wrote a row.
    expect($run->turnEntries()->count())->toBe(1);

    // And the screen it redirects to is still the preview, recomputed on the GET.
    $html = test()->withSession([
        '_old_input' => [
            'stage' => 'preview',
            'turn' => 2,
            'speed' => 520,
            'stamina' => 300,
            'power' => 355,
            'guts' => 210,
            'wit' => 95,
            'sp' => 240,
            'energy' => 60,
            'fans' => 43000,
            'mood' => 'NORMAL',
            'choice' => 'training-speed',
            'outcome' => 'Success',
            'previewed' => '1',
        ],
    ])->get("/training-runs/{$run->id}")->assertOk()->getContent();

    // The bubbles survive the redirect: they are computed on the GET from the
    // flashed input, so PRG did not cost the preview anything. 520 - 480 = +40.
    expect($html)->toContain('+40 Speed');
});
