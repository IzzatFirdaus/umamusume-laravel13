<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * A-0.1: the cockpit ships the cap arithmetic's recorded inputs (§1.2) — the caps the run states
 * beside the blue sparks its Legacy tree carries — and states that they do not reconcile.
 *
 * The rendered copy is the browser spec's; this file proves the wire the renderer reads. The caps are
 * resolved the same way the stat strip resolves them, so this test also pins that they agree.
 */
final class CockpitCapCompositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ships_the_caps_and_the_blue_sparks_side_by_side(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
            'scenario' => 'unity_cup',
            'cap_speed' => 1325,
            'cap_stamina' => 1322,
            'cap_power' => 1368,
            'cap_guts' => 1308,
            'legacy_selection' => [
                'affinity' => null,
                'legacies' => [
                    [
                        'rank' => null,
                        'is_guest' => false,
                        'ancestors' => [null, null],
                        'sparks' => [
                            ['kind' => 'blue', 'target' => 'Power', 'stars' => 1],
                        ],
                    ],
                    [
                        'rank' => null,
                        'is_guest' => false,
                        'ancestors' => [null, null],
                        'sparks' => [
                            ['kind' => 'blue', 'target' => 'Speed', 'stars' => 2],
                        ],
                    ],
                ],
            ],
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('capComposition.caps.Speed', 1325)
                ->where('capComposition.caps.Stamina', 1322)
                ->where('capComposition.caps.Power', 1368)
                ->where('capComposition.caps.Guts', 1308)
                ->where('capComposition.sparks.0.stat', 'Power')
                ->where('capComposition.sparks.0.stars', 1)
                ->where('capComposition.sparks.0.source', 'Parent A')
                ->where('capComposition.sparks.1.stat', 'Speed')
                ->where('capComposition.sparks.1.stars', 2)
                ->where('capComposition.sparks.1.source', 'Parent B')
                ->where('capComposition.note', fn (string $note): bool => str_contains($note, 'do not reconcile')));
    }

    public function test_ships_no_sparks_for_a_run_that_records_no_legacy(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
            'scenario' => 'unity_cup',
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('capComposition.sparks', [])
                ->has('capComposition.caps.Speed'));
    }
}
