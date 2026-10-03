<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * The two regions of the run screen (D-40, D-41, D-170).
 *
 * What scrolls and what stays is a claim about rendered geometry, and a test cannot
 * establish geometry by reading a class name - `lg:sticky` in the markup would pass with
 * the sticky offset broken, the background missing, or the region so tall it leaves no
 * room for the log. So this file pins only the structure the geometry is built on, and
 * the persistence itself is measured in the browser and recorded in
 * `docs/design-research/verification/slice-5-2026-09-28.md`.
 *
 * The split is asserted with DOM containment rather than by comparing string offsets: two
 * regions in document order are not two regions, and a `<section>` that merely opens
 * earlier than the table proves nothing about where the table lives.
 */
function frameHtml(int $turns = 1): string
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

    return test()->get('/training-runs/'.$run->id)->assertOk()->getContent();
}

/**
 * @return array{state: ?DOMNode, log: ?DOMNode, xpath: DOMXPath, doc: DOMDocument}
 */
function frameRegions(string $html): array
{
    $doc = new DOMDocument;
    // The page carries HTML5 elements and an inline dev script libxml does not know; the
    // warnings are noise, the tree it builds is what the assertions read.
    @$doc->loadHTML($html, LIBXML_NOERROR);

    $xpath = new DOMXPath($doc);

    return [
        'state' => $xpath->query('//section[@aria-label="Run state"]')->item(0),
        'log' => $xpath->query('//section[@aria-label="Turn log"]')->item(0),
        'xpath' => $xpath,
        'doc' => $doc,
    ];
}

it('splits the run screen into a state region and a log region', function (): void {
    $regions = frameRegions(frameHtml());

    expect($regions['state'])->not->toBeNull()
        ->and($regions['log'])->not->toBeNull();
});

it('keeps the strip and the stat band in the region that is pinned', function (): void {
    $regions = frameRegions(frameHtml());
    $xpath = $regions['xpath'];
    $state = $regions['state'];

    $badges = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " bg-grade-")]', $state);
    $turnWidget = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " bg-anchor")]', $state);

    // The band is the persistent half of D-41, and the turn chip is named by D-170. If
    // either drifts into the log region the frame silently stops doing its job.
    expect($badges->length)->toBe(5)
        ->and($turnWidget->length)->toBeGreaterThanOrEqual(1);
});

it('keeps the timeline, the rail and the escape hatch in the scrolling region', function (): void {
    $regions = frameRegions(frameHtml());
    $xpath = $regions['xpath'];
    $log = $regions['log'];

    expect($xpath->query('.//table', $log)->length)->toBe(1)
        ->and($xpath->query('.//*[@role="radiogroup"]', $log)->length)->toBe(1)
        ->and($xpath->query('.//details', $log)->length)->toBe(1)
        ->and($xpath->query('.//details//input[@name="condition"]', $log)->length)->toBe(1);
});

it('holds nothing in the pinned region that would need its own scrollbar', function (): void {
    $regions = frameRegions(frameHtml(8));
    $xpath = $regions['xpath'];
    $state = $regions['state'];

    // Eight logged turns must not grow the pinned box: the timeline is the part that
    // scales with use, and a pinned region that grows with the log eventually pins the
    // whole screen. This is the structural half of the 613px measurement that sent the
    // identity block out of the sticky region.
    expect($xpath->query('.//table', $state)->length)->toBe(0)
        ->and($xpath->query('.//*[contains(@class, " bg-grade-")]', $state)->length)->toBe(5);
});

it('pins the resources region only from the desktop width, so a narrow screen stacks', function (): void {
    $html = frameHtml();

    // D-40: below 1024px the regions stack. Asserting the breakpoint here is the one
    // markup check that earns its place, because the alternative is a mobile viewport
    // that pins a 600px header on a 640px screen - and the browser pass runs at desktop.
    // Counted rather than pattern-negated: a regex written to exclude `lg:sticky` also
    // matches the `g:sticky` inside it, which is how the first version of this assertion
    // failed on markup that was correct.
    //
    // Re-pointed 2026-10-03 (O-2): the sticky class moved off `section[aria-label="Run
    // state"]` and onto the Resources strip wrapper, because pinning the whole state block
    // took two thirds of the viewport. The claim is unchanged - exactly one element carries
    // a `sticky`-containing class at the `lg` breakpoint, and it is the Resources region -
    // only the subject of the assertion moved.
    preg_match('/aria-label="Resources"[^>]*class="([^"]*)"/', $html, $matched);

    $classes = explode(' ', $matched[1] ?? '');

    expect($classes)->toContain('lg:sticky')
        ->and(array_values(array_filter($classes, fn (string $c): bool => str_contains($c, 'sticky'))))->toBe(['lg:sticky']);
});
