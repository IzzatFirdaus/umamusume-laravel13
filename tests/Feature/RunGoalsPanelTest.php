<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;

/*
 * O-12: the run page has no goals surface, and the client's header is the first thing a
 * Trainer reads each turn. The Grade Point meter is a Trackblazer-only points ladder and the
 * Race calendar is a year grid, so neither is the line "Place 1st in Arima Kinen, entry
 * criteria met, 5 turns, three cleared behind it" that the audit names.
 *
 * `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` O-12 fixes this with a list of mandatory or special
 * race entries on the run, each with a state word. The report's data shape is three cleared
 * and one active for the run-7 fixture; the tests below reproduce that shape in factories.
 */

function goalsRun(): TrainingRun
{
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $slots = [
        RaceCatalogSlot::factory()->debut()->create(['sort_order' => 1]),
        RaceCatalogSlot::factory()->create([
            'title' => 'Japanese Derby',
            'year' => RaceCatalogSlot::YEAR_CLASSIC,
            'turn' => 16,
            'is_mandatory' => true,
            'is_special_race' => true,
        ]),
        RaceCatalogSlot::factory()->create([
            'title' => 'Arima Kinen',
            'year' => RaceCatalogSlot::YEAR_SENIOR,
            'turn' => 24,
            'is_mandatory' => true,
            'is_special_race' => true,
        ]),
        RaceCatalogSlot::factory()->create([
            'title' => 'Tenno Sho (Autumn)',
            'year' => RaceCatalogSlot::YEAR_SENIOR,
            'turn' => 20,
            'is_mandatory' => true,
            'is_special_race' => true,
        ]),
    ];

    // Three cleared, one active. The active slot has the earliest Senior turn so it sorts
    // above Arima Kinen, which makes the order assertion below non-trivial.
    foreach ([$slots[0], $slots[1], $slots[2]] as $slot) {
        RaceEntry::factory()->create([
            'training_run_id' => $run->id,
            'race_catalog_slot_id' => $slot->id,
            'status' => RaceEntryStatus::Completed,
            'placement' => 1,
        ]);
    }
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $slots[3]->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    return $run;
}

/**
 * How many elements inside the Goals landmark print exactly this status word.
 *
 * Scoped rather than page-wide, because the count stopped being about the panel: the run page now
 * carries a status select whose options are literally Active, Completed and Retired
 * (`runs/show.blade.php`, §7-4), and a logged failure prints a "Failed" chip. A badge and an
 * option that happen to say the same word are different things.
 */
function goalsStatusLabel(DOMXPath $xpath, string $label): int
{
    return $xpath->query('//*[@aria-label="Goals"]//*[normalize-space(text())="'.$label.'"]')->length;
}

it('renders the three cleared and one active goals from the run 7 shape', function (): void {
    $run = goalsRun();

    $html = test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $cleared = goalsStatusLabel($xpath, 'Cleared');
    $active = goalsStatusLabel($xpath, 'Active');
    $failed = goalsStatusLabel($xpath, 'Failed');

    expect($cleared)->toBe(3)
        ->and($active)->toBe(1)
        ->and($failed)->toBe(0);

    // The Goals section exists as an `aria-label="Goals"` landmark.
    $sections = $xpath->query('//*[@aria-label="Goals"]')->length;
    expect($sections)->toBe(1);
});

it('lists every mandatory and special race entry but not an optional one', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $mandatory = RaceCatalogSlot::factory()->create([
        'title' => 'Oka Sho',
        'is_mandatory' => true,
        'is_special_race' => false,
    ]);
    $special = RaceCatalogSlot::factory()->create([
        'title' => 'Arima Kinen',
        'is_mandatory' => false,
        'is_special_race' => true,
    ]);
    $optional = RaceCatalogSlot::factory()->create([
        'title' => 'Some Handicap',
        'is_mandatory' => false,
        'is_special_race' => false,
    ]);

    foreach ([$mandatory, $special, $optional] as $slot) {
        RaceEntry::factory()->create([
            'training_run_id' => $run->id,
            'race_catalog_slot_id' => $slot->id,
            'status' => RaceEntryStatus::Completed,
        ]);
    }

    test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->assertSee('Oka Sho')
        ->assertSee('Arima Kinen')
        ->assertDontSee('Some Handicap');
});

it('names a skipped mandatory goal as Failed and an unentered one as Active', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $skippedSlot = RaceCatalogSlot::factory()->create([
        'title' => 'Skipped Classic G1',
        'year' => RaceCatalogSlot::YEAR_CLASSIC,
        'is_mandatory' => true,
        'is_special_race' => true,
    ]);
    $enteredSlot = RaceCatalogSlot::factory()->create([
        'title' => 'Entered Senior G1',
        'year' => RaceCatalogSlot::YEAR_SENIOR,
        'is_mandatory' => true,
        'is_special_race' => true,
    ]);

    RaceEntry::factory()->skipped()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $skippedSlot->id,
    ]);
    RaceEntry::factory()->create([
        'training_run_id' => $run->id,
        'race_catalog_slot_id' => $enteredSlot->id,
        'status' => RaceEntryStatus::Entered,
    ]);

    $html = test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    expect(goalsStatusLabel($xpath, 'Failed'))->toBe(1)
        ->and(goalsStatusLabel($xpath, 'Active'))->toBe(1)
        ->and(goalsStatusLabel($xpath, 'Cleared'))->toBe(0);
});

it('omits the Goals section when the run has no mandatory or special race entries', function (): void {
    // A fresh run has no logged race entries. The Goals section is absent, not
    // "No goals yet", because a Trainer watching a run they have just started
    // does not need a panel asserting what is already true. The section appears
    // when the first mandatory or special entry is logged.
    $run = TrainingRun::factory()->create(['scenario' => null]);

    $html = test()->get("/training-runs/{$run->id}")
        ->assertOk()
        ->getContent();

    expect(str_contains($html, 'aria-label="Goals"'))->toBeFalse();
});
