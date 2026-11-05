<?php

declare(strict_types=1);

use App\Enums\MoodTier;
use App\Enums\RunStatus;
use App\Models\Preference;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Pins the frontend-audit findings that survive the F2 cutover, one test per finding.
 *
 * The two F-3 cases ("stops naming a scenario the run never chose") and the F-7 case ("reaches a
 * preview by redirect") were retired with the 2.0.0 record screen: `GET /training-runs/{run}` now
 * redirects to the Career Cockpit (F2, plan §9.6), whose scenario absence is the Cockpit's own
 * `run.scenario_label` = 'No scenario set' (asserted in `CareerCockpitTest`), and whose guided
 * preview flow has no 2.0 reproduction (owner ruling on group R-2). The three findings below are
 * surface-independent: the export route, the in-product 404, and the front-door theme.
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
    // The record route still binds `{run}`, so a missing id 404s before the redirect runs.
    //
    // `fe2fa850` (S11.3, "rebuild the three error documents on the HFW shapes") replaced this page's
    // copy: the `Nothing to show here` heading became `That page does not exist`, and the destinations
    // are now the three named routes rather than a `Training runs` line. The claims this case exists
    // for are unchanged and are what it asserts below: the response is the product's own shell (a
    // skip link, the design tokens, real navigation) rather than framework output, and it lands the
    // Trainer somewhere they can act. Asserting the retired heading pinned the wording, not the fix.
    test()->get('/training-runs/424242')
        ->assertStatus(404)
        ->assertSee('That page does not exist')
        ->assertSee('Skip to content')
        ->assertSee('Where would you like to go?')
        // The three ways back into the product, by their labels.
        ->assertSee('Careers')
        ->assertSee('Database');

    test()->get('/umamusume/there-is-no-such-trainee')
        ->assertStatus(404)
        ->assertSee('That page does not exist');
});

it('lands inside the product and honors the stored theme (F-1)', function (): void {
    Preference::put('theme', 'dark');

    // Re-pointed on merge rather than deleted. F-1 shipped against `welcome.blade.php`, and
    // master later retired that view: R57 made `/` a redirect into the runs index. As of
    // 2026-10-04 the front door is the Trainer Desk 2.0 Inertia Dashboard (ADR-0020 §1). The
    // durable half of the finding is unchanged: the front door opens inside the product shell
    // and honors the stored theme.
    $home = test()->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'))
        ->getContent();

    expect($home)->toContain('data-theme="dark"');

    $html = test()->get(route('runs.index'))->assertOk()->getContent();

    expect($html)->toContain('data-theme="dark"')
        // The skeleton's deploy path, which PRD §6.10 cuts outright.
        ->and($html)->not->toContain('cloud.laravel.com')
        ->and($html)->not->toContain('Deploy now')
        ->and($html)->not->toMatch('/#[0-9a-fA-F]{6}/')
        ->and($html)->not->toContain('fonts.bunny.net');
});
