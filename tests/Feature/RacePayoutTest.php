<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * A4 — the per-race fan payout columns (`fans_first`, `fans_second`, `fans_third`) on
 * `race_catalog_slots`, and the Fan gain row they fill on the Race Planner's detail panel.
 *
 * The walk recorded the source's per-race fan rewards (`6,700` for the win) with no column to hold
 * them; the Fan gain row could only say the payout curve id was stored and no table resolved it. These
 * cases pin the two halves: the model formats all three or names the absence, and the screen prints
 * the formatted payout on the Fan gain row rather than the old refusal.
 *
 * **Not backfilled**, so the ordinary row carries none of the three and the absence is the common
 * state, not the edge.
 */

it('formats the per-placement payout only when the row carries all three figures', function (): void {
    $full = RaceCatalogSlot::factory()->make(['fans_first' => 6700, 'fans_second' => 2680, 'fans_third' => 1340]);

    expect($full->fanPayout())->toBe('1st: 6,700 / 2nd: 2,680 / 3rd: 1,340');

    // A partial set is an absence, never a half-answer and never a zero.
    expect(RaceCatalogSlot::factory()->make(['fans_first' => 6700])->fanPayout())->toBeNull()
        ->and(RaceCatalogSlot::factory()->make(['fans_second' => 2680])->fanPayout())->toBeNull()
        ->and(RaceCatalogSlot::factory()->make()->fanPayout())->toBeNull();
});

it('persists the three payout columns on the catalogue row', function (): void {
    $slot = RaceCatalogSlot::factory()->create([
        'fans_first' => 6700,
        'fans_second' => 2680,
        'fans_third' => 1340,
    ]);

    $slot->refresh();

    expect($slot->fans_first)->toBe(6700)
        ->and($slot->fans_second)->toBe(2680)
        ->and($slot->fans_third)->toBe(1340);
});

it('prints the payout on the Fan gain row when the row carries one, and names the absence when not', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->state(['name' => 'Rice Shower', 'name_ja' => 'ライスシャワー']),
    ]);

    for ($turn = 1; $turn <= 12; $turn++) {
        TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => $turn]);
    }

    // Reached and unrecorded: the turn has arrived, so this row lands in the optional group.
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'month' => 7,
        'half' => 'Early',
        'turn' => 13,
        'title' => 'Kyoto Daishoten',
        'tier' => 'G2',
        'distance' => 2400,
        'distance_band' => 'Long',
        'surface' => 'Turf',
        'fans_gain_curve' => 21,
        'sort_order' => 1,
        'fans_first' => 6700,
        'fans_second' => 2680,
        'fans_third' => 1340,
    ]);

    // Ahead of the run, and carrying no payout: the absence is the ordinary state.
    RaceCatalogSlot::factory()->create([
        'scenario_key' => null,
        'year' => RaceCatalogSlot::YEAR_JUNIOR,
        'month' => 8,
        'half' => 'Early',
        'turn' => 15,
        'title' => 'Hopeful Stakes',
        'tier' => 'G1',
        'distance' => 2000,
        'distance_band' => 'Medium',
        'surface' => 'Turf',
        'fans_gain_curve' => 12,
        'sort_order' => 2,
    ]);

    $this->get(route('runs.races.planner', $run->refresh()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/RacePlanner')
            // Fan gain is comparison row 5 and the facts list's own sixth entry.
            ->where('groups.optional.races.0.comparison.5.value', '1st: 6,700 / 2nd: 2,680 / 3rd: 1,340')
            ->where('groups.optional.races.0.comparison.5.title', null)
            ->where('groups.optional.races.0.facts.5.key', 'fan_gain')
            ->where('groups.optional.races.0.facts.5.value', '1st: 6,700 / 2nd: 2,680 / 3rd: 1,340')
            // Absent, with the reason, rather than a zero that would read as "pays nothing".
            ->where('groups.upcoming.races.0.comparison.5.value', null)
            ->where('groups.upcoming.races.0.comparison.5.title', fn (string $title): bool => str_contains($title, 'Payout not recorded'))
            ->where('groups.upcoming.races.0.facts.5.value', null));
});
