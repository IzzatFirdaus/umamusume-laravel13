<?php

declare(strict_types=1);

namespace Tests\Feature\Career;

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Career\TeamPanelBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpiritBurstCountdownTest extends TestCase
{
    use RefreshDatabase;

    private TeamPanelBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = app(TeamPanelBuilder::class);
    }

    public function test_burst_countdown_warns_when_under_13_in_senior_october(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'spirit_bursts_count' => 8,
            'extreme_bursts_count' => 3,
        ]);

        // Create a turn at Senior October (turn ~65)
        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => 65,
        ]);

        $team = $this->builder->teamSection($run);
        $countdown = $this->builder->burstCountdown($run, $team);

        $this->assertNotNull($countdown);
        $this->assertEquals(11, $countdown['current_bursts']);
        $this->assertEquals(13, $countdown['target_bursts']);
        $this->assertEquals(2, $countdown['bursts_needed']);
        $this->assertStringContainsString('Need 2 more bursts', $countdown['warning_message']);
        $this->assertGreaterThan(0, $countdown['turns_left']);
    }

    public function test_burst_countdown_no_warning_when_at_or_above_13(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'spirit_bursts_count' => 10,
            'extreme_bursts_count' => 3,
        ]);

        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => 65,
        ]);

        $team = $this->builder->teamSection($run);
        $countdown = $this->builder->burstCountdown($run, $team);

        $this->assertNotNull($countdown);
        $this->assertEquals(13, $countdown['current_bursts']);
        $this->assertEquals(13, $countdown['target_bursts']);
        $this->assertEquals(0, $countdown['bursts_needed']);
        $this->assertNull($countdown['warning_message']);
    }

    public function test_burst_countdown_null_when_not_unity_cup(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'ura_finale',
            'spirit_bursts_count' => 8,
            'extreme_bursts_count' => 3,
        ]);

        TurnEntry::factory()->create([
            'training_run_id' => $run->id,
            'turn' => 65,
        ]);

        $team = $this->builder->teamSection($run);
        $countdown = $this->builder->burstCountdown($run, $team);

        $this->assertNull($countdown);
    }

    public function test_burst_countdown_null_when_no_turns_recorded(): void
    {
        $run = TrainingRun::factory()->create([
            'scenario' => 'unity_cup',
            'spirit_bursts_count' => 8,
            'extreme_bursts_count' => 3,
        ]);

        $team = $this->builder->teamSection($run);
        $countdown = $this->builder->burstCountdown($run, $team);

        $this->assertNull($countdown);
    }
}
