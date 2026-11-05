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
 * A-0.11 / D11: the cockpit ships the client's Mood Effect figures — the `GREAT` headline (+20%
 * training, +4% running) and the full five-tier table — from `UMAMUSUME_REFERENCE.md` §1.1.6 into
 * `config('uma.mood_effect')`.
 *
 * The 2026-10-24 ruling that kept the payload to one sourced entry is superseded: the per-tier table
 * ships, and the headline is read off its `GREAT` row rather than stored a second time.
 */
final class CockpitMoodEffectTest extends TestCase
{
    use RefreshDatabase;

    public function test_ships_the_sourced_mood_effect_headline_and_table(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('moodEffect.training', 20)
                ->where('moodEffect.running', 4)
                ->where('moodEffectTable.GREAT.training', 20)
                ->where('moodEffectTable.GREAT.running', 4)
                ->where('moodEffectTable.GOOD.training', 10)
                ->where('moodEffectTable.GOOD.running', 2)
                ->where('moodEffectTable.NORMAL.training', 0)
                ->where('moodEffectTable.NORMAL.running', 0)
                ->where('moodEffectTable.BAD.training', -10)
                ->where('moodEffectTable.BAD.running', -2)
                ->where('moodEffectTable.AWFUL.training', -20)
                ->where('moodEffectTable.AWFUL.running', -4));
    }
}
