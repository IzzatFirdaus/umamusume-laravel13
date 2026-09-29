<?php

declare(strict_types=1);

use App\Models\ScenarioSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * KI-25: the turn log forces a horizontal scroll at 390px that nobody can reach.
 *
 * The measurement is in the register and in `slice-15-2026-09-29.md` §8.4 — the table's
 * `scrollWidth` is 476px inside a 390px viewport, so content is clipped. What was missed is the
 * second half: the overflow happens, and a keyboard Trainer cannot traverse it. A scroll container
 * that is not focusable has no focus to scroll with, so those 86px are reachable by trackpad only.
 *
 * The race calendar solved this the same way when it was a twelve-column band: `role="region"` +
 * `tabindex="0"` + an `aria-label`. The calendar has since taken the client's four-column shape
 * (docs/game-screenshots/Screenshot 2026-07-17 230755.png) and stopped overflowing at any width a
 * desktop tool is used at, so it keeps the named region and gave the tab stop back. What is left of
 * the convention is asserted per region: the one that clips is the one that must be traversable.
 *
 * What this file can prove is structure — the attributes exist on the element that wraps the
 * clipping table. That arrow keys actually scroll it is a runtime fact, verified in the T3 browser
 * pass; a PHP assertion cannot press a key.
 */

function scrollRegionHtml(): string
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    TurnEntry::create([
        'training_run_id' => $run->id, 'turn' => 1, 'speed' => 300, 'stamina' => 280,
        'power' => 240, 'guts' => 210, 'wit' => 150, 'sp' => 120, 'energy' => 74,
        'mood' => 'GOOD', 'fans' => 4000,
    ]);

    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'goal_race',
        'title' => 'Japanese Derby (Tokyo Yushun)',
        'tier' => 'G1',
    ]);

    return test()->get('/training-runs/'.$run->id)->assertOk()->getContent();
}

/**
 * The element that clips: the nearest ancestor div carrying a horizontal overflow rule.
 *
 * @return array{node: DOMElement, label: string}
 */
function regionFor(string $headerText, string $html): array
{
    $doc = new DOMDocument;
    @$doc->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($doc);

    foreach ($xpath->query('//table') as $table) {
        $heads = $xpath->query('.//th', $table);

        /** @var DOMElement $th */
        foreach ($heads as $th) {
            if (trim($th->nodeValue ?? '') !== $headerText) {
                continue;
            }

            $wrapper = $table->parentNode;

            return [
                'node' => $wrapper instanceof DOMElement ? $wrapper : new DOMElement('absent'),
                'label' => $table->getAttribute('class'),
            ];
        }
    }

    return ['node' => new DOMElement('absent'), 'label' => 'no table with that header'];
}

it('wraps the turn log in a focusable scroll region', function (): void {
    $turnLog = regionFor('Turn', scrollRegionHtml());

    expect($turnLog['node']->nodeName)->toBe('div', 'the turn log table has no wrapping div — '.$turnLog['label'])
        ->and($turnLog['node']->getAttribute('role'))->toBe('region')
        ->and($turnLog['node']->getAttribute('tabindex'))->toBe('0')
        ->and($turnLog['node']->getAttribute('aria-label'))->toContain('Turn log')
        ->and($turnLog['node']->getAttribute('class'))->toContain('overflow-x-auto');
});

it('keeps the tab stop on the region that still clips and the name on the one that does not', function (): void {
    $doc = new DOMDocument;
    @$doc->loadHTML(scrollRegionHtml(), LIBXML_NOERROR);
    $xpath = new DOMXPath($doc);

    $regions = [];

    /** @var DOMElement $div */
    foreach ($xpath->query('//div[@role="region"]') as $div) {
        $regions[] = $div;
    }

    // A region that does not clip and still holds a tab stop spends a keypress on nothing, so the
    // calendar's stop went with its overflow band. Its named region stays: that is what tells a
    // screen reader which twenty-four slots it is standing in.
    expect($regions)->toHaveCount(2)
        ->and($regions[0]->getAttribute('aria-label'))->toStartWith('Race calendar')
        ->and($regions[0]->getAttribute('class'))->not->toContain('overflow-x-auto')
        ->and($regions[0]->hasAttribute('tabindex'))->toBeFalse()
        // The turn log is the table that still overflows at the 768px floor, so both halves of
        // KI-25 stand there: the region is named, and it is traversable.
        ->and($regions[1]->getAttribute('aria-label'))->toStartWith('Turn log')
        ->and($regions[1]->getAttribute('class'))->toContain('overflow-x-auto')
        ->and($regions[1]->getAttribute('tabindex'))->toBe('0');
});
