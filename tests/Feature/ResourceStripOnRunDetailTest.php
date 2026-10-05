<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The resource strip mounted on a real run (owner ruling 2026-09-27: validate the
 * scenario at the Form Request, no schema migration to an FK).
 *
 * Two contracts meet here. D-220 says a widget a scenario has no mechanic for is
 * absent, and the ruling says a run with no scenario renders the baseline strip.
 * Neither is provable while the value on the run is free text, so the first block
 * pins the validation boundary that makes the composition safe to render.
 *
 * The run page is now an Inertia response, so the strip arrives as `strip.widgets` (one
 * widget key per box the component composes) and `strip.values` (the run's numbers keyed
 * by widget, null when the run owns none). The keys ResourceStrip prints as labels are:
 * turn => Turn, energy => Energy, fans => Fans, team_rank => Team Rank,
 * spirit_bursts => Spirit Bursts, grade_points => Grade Points, shop_coins => Shop Coins.
 * Whether a key renders, and which widgets a scenario offers, is a prop claim and is
 * asserted here; the label a key prints, the `41/100` / `60,000` formatting and the
 * `not yet recorded` caption are the component's and live in the browser spec.
 */

function resourceStripRunPage(TrainingRun $run): TestResponse
{
    return test()->get("/training-runs/{$run->id}")->assertOk();
}

it('renders the baseline strip for a run with no scenario', function (): void {
    // A run naming no scenario resolves to the baseline key, whose widget list is turn /
    // energy / fans and nothing else. The four scenario-only keys are absent from the
    // composition rather than present-and-blank (D-220).
    resourceStripRunPage(TrainingRun::factory()->create(['scenario' => null]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('strip.widgets', fn (Collection $widgets): bool => $widgets->contains('turn')
                && $widgets->contains('energy')
                && $widgets->contains('fans')
                && ! $widgets->contains('team_rank')
                && ! $widgets->contains('spirit_bursts')
                && ! $widgets->contains('grade_points')
                && ! $widgets->contains('shop_coins')));
});

it('composes the strip from the run\'s scenario rather than from its free text', function (string $scenario, array $expected, array $absent): void {
    resourceStripRunPage(TrainingRun::factory()->create(['scenario' => $scenario]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('strip.widgets', fn (Collection $widgets): bool => collect($expected)
                ->every(fn (string $key): bool => $widgets->contains($key))
                && collect($absent)
                    ->every(fn (string $key): bool => ! $widgets->contains($key))));
})->with([
    'trackblazer' => ['trackblazer', ['grade_points', 'shop_coins'], ['team_rank', 'spirit_bursts']],
    'unity_cup' => ['unity_cup', ['team_rank', 'spirit_bursts'], ['grade_points', 'shop_coins']],
]);

it('changes composition when the scenario is switched on the run', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    resourceStripRunPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('strip.widgets', fn (Collection $widgets): bool => ! $widgets->contains('shop_coins')));

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => 'Active',
        'scenario' => 'trackblazer',
    ])->assertRedirect();

    expect($run->fresh()->scenario)->toBe('trackblazer');

    // The page reloads through the route binding, so it reads the switched scenario: Trackblazer's
    // Grade Points and Shop Coins now compose, and Team Rank stays absent.
    resourceStripRunPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('strip.widgets', fn (Collection $widgets): bool => $widgets->contains('shop_coins')
            && $widgets->contains('grade_points')
            && ! $widgets->contains('team_rank')));
});

it('rejects a scenario that no descriptor exists for', function (string $scenario): void {
    $response = test()->post('/training-runs', [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'status' => 'Active',
        'scenario' => $scenario,
    ]);

    $response->assertSessionHasErrors('scenario');

    expect(TrainingRun::count())->toBe(0);
})->with([
    'unknown slug' => ['jp_only_event'],
    'a display name' => ['URA Finale'],
    'a widget key' => ['team_rank'],
    'a prototype shorthand' => ['track'],
]);

it('treats an empty scenario as unset rather than as a value to reject', function (): void {
    test()->post('/training-runs', [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'status' => 'Active',
        'scenario' => '',
    ])->assertSessionHasNoErrors();

    expect(TrainingRun::sole()->scenario)->toBeNull();
});

it('reads Energy and Fans from the latest logged turn, not from a default', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 1, 'energy' => 78, 'fans' => 41250]);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 2, 'energy' => 41, 'fans' => 60000]);

    // 41 and 60,000 are the second turn's end-of-turn totals. If the strip read the first
    // entry, or summed the pair, it would carry 78 or 119 — ADR-0003 records these as
    // absolute totals, never deltas. The `/100` suffix and the thousands separator the
    // component adds to these two numbers are the browser's claim.
    resourceStripRunPage($run)->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('strip.values.energy', 41)
        ->where('strip.values.fans', 60000));
});

it('asserts no Energy or Fans number for a run that has logged no turn', function (): void {
    // The scenario owns Energy and Fans, so the widgets are present. The run owns no value for
    // them yet, so each travels as null rather than 0 — printing 0/100 would state a fact about
    // the trainee nobody entered (D-220, in run-state form). The "not yet recorded" caption that
    // null becomes is the component's.
    resourceStripRunPage(TrainingRun::factory()->create(['scenario' => 'ura_finale']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('strip.widgets', fn (Collection $widgets): bool => $widgets->contains('energy')
                && $widgets->contains('fans'))
            ->whereNull('strip.values.energy')
            ->whereNull('strip.values.fans'));
});

it('never invents a team rank for a run that has none', function (): void {
    // The component used to fall back to 'G', which asserts a team rank the Trainer never
    // entered on a run with no team data at all. Unity Cup offers the widget, and the value
    // stays null rather than defaulting to a letter.
    resourceStripRunPage(TrainingRun::factory()->create(['scenario' => 'unity_cup']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('strip.widgets', fn (Collection $widgets): bool => $widgets->contains('team_rank'))
            ->whereNull('strip.values.team_rank'));
});
