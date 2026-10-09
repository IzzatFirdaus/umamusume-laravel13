<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The run's own identity on the Cockpit (A6.1 to A6.3 renders): the trainee's rarity and potential, the
 * card form it started on, the growth-rate row it read off that card, and the fan ladder the latest
 * reading sits in.
 *
 * The rendered copy is asserted in the browser spec; this file proves the wire the renderer reads. Each
 * figure crosses as its stored value when a writer recorded it, and as null when none did, so a block
 * with nothing to say renders the absence rather than a default (D-220). The fan ladder is read through
 * `FanLadder`, so the class and the gap are one band's answer.
 *
 * Class-based with private helpers, matching `RunGrowthRateTest`: a top-level function in
 * `tests/Feature` shares one namespace, and a duplicate name fatals the whole suite at load time.
 */
final class CockpitRunIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_reads_the_trainee_card_growth_and_fan_ladder_when_recorded(): void
    {
        $trainee = Umamusume::factory()->create();
        $card = CharacterCard::factory()->create([
            'umamusume_id' => $trainee->id,
            'title' => '[Rosy Dreams]',
        ]);

        $run = TrainingRun::factory()->create([
            'umamusume_id' => $trainee->id,
            'character_card_id' => $card->id,
            'status' => RunStatus::Active,
            'trainee_rarity' => 3,
            'potential_level' => 2,
            'growth_rate' => ['Speed' => 0, 'Stamina' => 10, 'Power' => 0, 'Guts' => 20, 'Wit' => 0],
        ]);

        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => 1,
            'fans' => 209245,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('identity.trainee_rarity', 3)
                ->where('identity.potential_level', 2)
                ->where('identity.card_title', '[Rosy Dreams]')
                ->where('identity.growth_rate', fn (Collection $growth): bool => $growth->all() === [
                    'Speed' => 0, 'Stamina' => 10, 'Power' => 0, 'Guts' => 20, 'Wit' => 0,
                ])
                ->where('identity.fans', 209245)
                // Read through `FanLadder`: 209,245 sits in the Star band, 30,755 fans from 240,000.
                ->where('identity.fan_ladder.class', 'Star')
                ->where('identity.fan_ladder.nextThreshold', 240000)
                ->where('identity.fan_ladder.gap', 30755));
    }

    public function test_reads_the_identity_as_absent_for_a_run_that_has_recorded_nothing(): void
    {
        $run = TrainingRun::factory()->create([
            'status' => RunStatus::Active,
            'umamusume_id' => Umamusume::factory()->create()->id,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('identity.trainee_rarity', null)
                ->where('identity.potential_level', null)
                ->where('identity.card_title', null)
                ->where('identity.growth_rate', null)
                ->where('identity.fans', null)
                ->where('identity.fan_ladder', null));
    }

    public function test_reads_a_fan_count_above_every_band_as_a_bare_number(): void
    {
        // Above the highest documented threshold the ladder names no class, so the class and the gap
        // are null while the count itself still crosses the wire: the row prints the number alone
        // rather than a class nobody sourced (`FanLadder`'s own contract).
        $run = TrainingRun::factory()->create([
            'status' => RunStatus::Active,
            'umamusume_id' => Umamusume::factory()->create()->id,
        ]);

        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => 1,
            'fans' => 500000,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('identity.fans', 500000)
                ->where('identity.fan_ladder.class', null)
                ->where('identity.fan_ladder.gap', null));
    }
}
