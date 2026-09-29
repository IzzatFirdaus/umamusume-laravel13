<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;

/*
 * The resource strip mounted on a real run (owner ruling 2026-09-27: validate the
 * scenario at the Form Request, no schema migration to an FK).
 *
 * Two contracts meet here. D-220 says a widget a scenario has no mechanic for is
 * absent, and the ruling says a run with no scenario renders the baseline strip.
 * Neither is provable while the value on the run is free text, so the first block
 * pins the validation boundary that makes the composition safe to render.
 */

function runDetailHtml(TrainingRun $run): string
{
    return test()->get("/training-runs/{$run->id}")->assertOk()->getContent();
}

it('renders the baseline strip for a run with no scenario', function (): void {
    $html = runDetailHtml(TrainingRun::factory()->create(['scenario' => null]));

    expect($html)
        ->toContain('Turn')->toContain('Energy')->toContain('Fans')
        ->not->toContain('Team Rank')
        ->not->toContain('Spirit Bursts')
        ->not->toContain('Grade Points')
        ->not->toContain('Shop Coins');
});

it('composes the strip from the run\'s scenario rather than from its free text', function (string $scenario, array $expected, array $absent): void {
    $html = runDetailHtml(TrainingRun::factory()->create(['scenario' => $scenario]));

    foreach ($expected as $label) {
        expect($html)->toContain($label);
    }

    foreach ($absent as $label) {
        expect($html)->not->toContain($label);
    }
})->with([
    'trackblazer' => ['trackblazer', ['Grade Points', 'Shop Coins'], ['Team Rank', 'Spirit Bursts']],
    'unity_cup' => ['unity_cup', ['Team Rank', 'Spirit Bursts'], ['Grade Points', 'Shop Coins']],
]);

it('changes composition when the scenario is switched on the run', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    expect(runDetailHtml($run))->not->toContain('Shop Coins');

    test()->put("/training-runs/{$run->id}", [
        'umamusume_id' => $run->umamusume_id,
        'status' => 'Active',
        'scenario' => 'trackblazer',
    ])->assertRedirect();

    expect($run->fresh()->scenario)->toBe('trackblazer');

    $html = runDetailHtml($run);

    expect($html)->toContain('Shop Coins')->toContain('Grade Points')->not->toContain('Team Rank');
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

    $html = runDetailHtml($run);

    // 41/100 is the second turn's end-of-turn total. If the strip read the first
    // entry, or summed the pair, it would print 119/100 — ADR-0003 records these as
    // absolute totals, never deltas.
    expect($html)->toContain('41/100')->toContain('60,000')->not->toContain('78/100');
});

it('asserts no Energy or Fans number for a run that has logged no turn', function (): void {
    $html = runDetailHtml(TrainingRun::factory()->create(['scenario' => 'ura_finale']));

    // The scenario owns Energy and Fans, so the widgets are present. The run owns no
    // value for them yet, and printing 0/100 would state a fact about the trainee
    // that nobody entered (D-220, in run-state form).
    expect($html)
        ->toContain('Energy')
        ->toContain('not yet recorded')
        ->not->toContain('0/100');
});

it('never invents a team rank for a run that has none', function (): void {
    $html = runDetailHtml(TrainingRun::factory()->create(['scenario' => 'unity_cup']));

    // The component used to fall back to 'G', which asserts a team rank the Trainer
    // never entered on a run with no team data at all.
    expect($html)->toContain('Team Rank')->toContain('not yet recorded');
});
