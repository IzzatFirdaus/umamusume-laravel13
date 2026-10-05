<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The two regions of the run screen (D-40, D-41, D-170), after the Inertia port (ADR-0020 §1).
 *
 * What scrolls and what stays is a claim about rendered geometry, and a Pest assertion cannot
 * establish geometry - `lg:sticky` in the component source would pass with the sticky offset
 * broken, the background missing, or the region so tall it leaves no room for the log. So this
 * file pins the payload each region is built from, and the persistence itself is measured in
 * the browser pass (docs/design-research/verification/slice-5-2026-09-28.md,
 * tests/browser/run-detail.spec.ts).
 *
 * The split is asserted through the payload rather than DOM containment: the state half
 * (`strip`, `band`) and the log half (`turns`, `rail`) travel on one response, and how they
 * nest is template structure in `resources/js/pages/Runs/Show.vue`.
 */

function frameRun(int $turns = 1): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    for ($turn = 1; $turn <= $turns; $turn++) {
        TurnEntry::create([
            'training_run_id' => $run->id,
            'turn' => $turn,
            'speed' => 300 + $turn * 50,
            'stamina' => 280,
            'power' => 240,
            'guts' => 210,
            'wit' => 150,
            'sp' => 120,
            'energy' => 74,
            'mood' => 'GOOD',
            'fans' => 4000 * $turn,
        ]);
    }

    return $run;
}

it('splits the run screen into a state region and a log region', function (): void {
    $run = frameRun();

    // One response feeds both halves of the frame: the pinned state region renders `strip` and
    // `band`, the scrolling log region renders `turns` and the rail. A response missing either
    // half leaves a section of the frame unbuilt.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->has('strip')
            ->has('band')
            ->has('turns', 1)
            ->has('rail'));
});

it('keeps the strip and the stat band in the region that is pinned', function (): void {
    $run = frameRun();

    // The band is the persistent half of D-41 and the turn chip (D-170) is the `turn` widget
    // the strip draws with the anchor treatment. Both come from the pinned region's payload,
    // and the band carries exactly the five disciplines its five badges render.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('strip.widgets', ['turn', 'energy', 'fans'])
            ->where('strip.values.turn', 1)
            ->where('strip.values.energy', 74)
            ->where('band.values', [
                'Speed' => 350,
                'Stamina' => 280,
                'Power' => 240,
                'Guts' => 210,
                'Wit' => 150,
            ]));
});

it('keeps the timeline, the rail and the escape hatch in the scrolling region', function (): void {
    $run = frameRun();

    // The timeline is the `turns` rows, the rail's radiogroup is built from `rail.choices`,
    // and the escape hatch shares the rail's endpoint: it opens on `run.turn_count` + 1 and
    // posts to `rail.action`.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('turns', 1)
            ->where('turns.0.speed', 350)
            ->has('rail.choices', 7)
            ->where('rail.action', route('runs.turns.store', $run))
            ->where('run.turn_count', 1));
});

it('holds nothing in the pinned region that would need its own scrollbar', function (): void {
    $run = frameRun(8);

    // Eight logged turns must not grow the pinned box: the timeline is the part that scales
    // with use, and a pinned region that grows with the log eventually pins the whole screen.
    // The payload keeps that split - `turns` carries all eight rows while the pinned half
    // stays the five stat cells and the strip's own widget list.
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('turns', 8)
            ->where('band.values', fn (Collection $values): bool => $values->keys()->all() === ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
            ->where('strip.widgets', ['turn', 'energy', 'fans']));
});

it('pins the resources region only from the desktop width, so a narrow screen stacks', function (): void {
    // D-40: below 1024px the regions stack. The page is component-rendered now, so the class
    // is read from the component that owns it; the rendered geometry stays the browser pass's.
    $source = (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));

    // Counted rather than pattern-negated: a regex written to exclude `lg:sticky` also
    // matches the `g:sticky` inside it, which is how the first version of this assertion
    // failed on markup that was correct.
    //
    // Re-pointed 2026-10-03 (O-2): the sticky class moved off the Run state section and onto
    // the Resources strip wrapper, because pinning the whole state block took two thirds of
    // the viewport, and re-pointed at the component source by the Inertia port. The claim is
    // unchanged - exactly one element carries a sticky-containing class at the `lg` breakpoint,
    // and it is the Resources region - only the subject of the assertion moved.
    preg_match('/aria-label="Resources"[^>]*class="([^"]*)"/', $source, $matched);

    $classes = explode(' ', $matched[1] ?? '');

    expect($classes)->toContain('lg:sticky')
        ->and(array_values(array_filter($classes, fn (string $c): bool => str_contains($c, 'sticky'))))->toBe(['lg:sticky']);
});
