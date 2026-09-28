<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\TrainingRun;

/*
 * R61/R68: a `free_race` cell takes open-cell geometry and the Trainer-entered marker, and never
 * a Goal pennant.
 *
 * Slice 12 §7.3 reported this rule as lost. Slice 13 found that report was wrong about the cause
 * and the calendar session reached the same conclusion independently in `5dcc06c`, which pins the
 * model: a persisted free_race row reads back as `['state' => 'open', 'manual' => true]`.
 *
 * What nothing pinned was the rendered cell, and the one test that appeared to was
 * `RaceCalendarTest.php:276`, which hand-writes `['state' => 'past', 'label' => 'Local Stakes
 * (Trainer-entered)']` into the cells array and then asserts the string `Trainer-entered` is in
 * the output. That passes on the label text alone; it would still pass if `calendarCell()` never
 * emitted `manual`, because it never calls `calendarCell()`. So the check could not fail for the
 * reason it was running, which is the same failure KI-21 was filed against.
 *
 * This file goes through the read path. A Trainer-entered race is created as a row, the run is
 * fetched over HTTP, and the marker is required to sit inside a calendar cell that carries the
 * open treatment, names its state to assistive tech, and has no Goal pennant anywhere on the page.
 */

it('renders a persisted free_race row as an open manual cell on the run screen', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'source_key' => null,
        'slot_label' => 'Autumn Practice Stakes',
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'tier' => null,
        'is_manual' => true,
        'sort_order' => 1,
    ]);

    $html = $this->get(route('runs.show', $run))->content();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Scoped to a calendar cell, which is the element carrying role="img": the race panel prints
    // the same words on its own entry rows, and a page-wide count would pass without the calendar
    // rendering anything at all.
    $markers = $xpath->query('//*[text()="Trainer-entered"][ancestor::*[@role="img"]]');

    expect($markers->length)->toBe(1);

    $cell = $xpath->query('ancestor::*[@role="img"]', $markers->item(0))->item(0);
    $cellClass = $cell->getAttribute('class');

    // Open geometry: the dashed edge. Not the goal outline, and not the past treatment.
    expect($cellClass)->toContain('border-dashed')
        ->and($cellClass)->toContain('border-green-line')
        ->and($cellClass)->not->toContain('border-goal-line')
        ->and($cellClass)->not->toContain('bg-transparent');

    // D-12: the treatment is not the only signal. The cell says its state out loud.
    expect($cell->getAttribute('aria-label'))->toContain('Entry open')
        ->and($cell->getAttribute('aria-label'))->toContain('Autumn Practice Stakes');

    // R61: never a Goal pennant. D-181 draws it as a corner triangle with border-l-goal, and it
    // is emitted only for state `goal`, so the whole page holding zero of them is the assertion.
    expect($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " border-l-goal ")]')->length)
        ->toBe(0);
});

/*
 * The marker survives the finish. `calendarCell()` returns `['state' => 'past', ..., 'manual' =>
 * true]` for a free race with a recorded entry (`app/Models/TrainingRun.php:437`), because
 * `free_race` is a tool concept rather than a client one: the game never offers a race that is not
 * in the calendar, so "this row came from the Trainer" is provenance about where the record came
 * from, and Slice 13 measured that it reads better looking back over a finished career. Slice 12
 * reported the marker as lost; the read path's owner made that call in the same window this slice
 * ran, and the test below is what pins it.
 */
it('keeps the Trainer-entered marker on a free_race cell after its finish is recorded', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $slot = ScenarioSlot::factory()->create([
        'scenario_key' => 'ura_finale',
        'kind' => 'free_race',
        'source_key' => null,
        'slot_label' => 'Autumn Practice Stakes',
        'title' => 'Autumn Practice Stakes',
        'month' => 9,
        'half' => 'Late',
        'tier' => null,
        'is_manual' => true,
        'sort_order' => 1,
    ]);
    RaceEntry::create([
        'training_run_id' => $run->id,
        'scenario_slot_id' => $slot->id,
        'status' => RaceEntryStatus::Completed,
        'placement' => 1,
    ]);

    $html = $this->get(route('runs.show', $run))->content();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $markers = $xpath->query('//*[text()="Trainer-entered"][ancestor::*[@role="img"]]');

    expect($markers->length)->toBe(1);

    $cell = $xpath->query('ancestor::*[@role="img"]', $markers->item(0))->item(0);

    // Past geometry: the run happened here. Provenance still named, and still no pennant.
    expect($cell->getAttribute('class'))->toContain('bg-transparent')
        ->and($cell->getAttribute('aria-label'))->toContain('Run')
        ->and($cell->getAttribute('aria-label'))->toContain('Autumn Practice Stakes')
        ->and($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " border-l-goal ")]')->length)
        ->toBe(0);
});
