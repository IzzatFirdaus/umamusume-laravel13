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
 * A-0.11: the cockpit ships the client's Mood Effect headline — the great tier's +20% training and
 * +4% running — sourced from `UMAMUSUME_REFERENCE.md` §1.1.6 into `config('uma.mood_effect')`.
 *
 * The rendered copy is the browser spec's; this file proves the wire. The figure is one sourced entry,
 * not a per-tier table (owner ruling 2026-10-24).
 */
final class CockpitMoodEffectTest extends TestCase
{
    use RefreshDatabase;

    public function test_ships_the_sourced_mood_effect_headline(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('moodEffect.training', 20)
                ->where('moodEffect.running', 4));
    }
}
