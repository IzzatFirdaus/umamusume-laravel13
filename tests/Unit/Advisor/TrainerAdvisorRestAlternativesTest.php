<?php

declare(strict_types=1);

namespace Tests\Unit\Advisor;

use App\Services\Advisor\TrainerAdvisor;
use Tests\TestCase;

class TrainerAdvisorRestAlternativesTest extends TestCase
{
    private TrainerAdvisor $advisor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->advisor = app(TrainerAdvisor::class);
    }

    public function test_recommends_wit_when_energy_is_low_and_wit_is_available(): void
    {
        $state = [
            'energy' => 30,
            'available_trainings' => ['speed', 'stamina', 'wit'],
            'has_extreme_burst' => false,
            'has_zero_energy_race' => false,
        ];

        $decision = $this->advisor->evaluateAlternatives($state);

        $this->assertEquals('wit', $decision->action);
        $this->assertStringContainsString('Wit training', $decision->reason);
    }

    public function test_recommends_extreme_burst_when_energy_is_low_and_burst_is_available(): void
    {
        $state = [
            'energy' => 30,
            'available_trainings' => ['speed', 'stamina', 'guts'],
            'has_extreme_burst' => true,
            'has_zero_energy_race' => false,
        ];

        $decision = $this->advisor->evaluateAlternatives($state);

        $this->assertEquals('train', $decision->action);
        $this->assertStringContainsString('Extreme Spirit Burst', $decision->reason);
    }

    public function test_recommends_race_when_energy_is_low_and_zero_energy_race_available(): void
    {
        $state = [
            'energy' => 30,
            'available_trainings' => ['speed'],
            'has_extreme_burst' => false,
            'has_zero_energy_race' => true,
        ];

        $decision = $this->advisor->evaluateAlternatives($state);

        $this->assertEquals('race', $decision->action);
        $this->assertStringContainsString('scheduled race', $decision->reason);
    }

    public function test_falls_back_to_rest_when_no_alternatives_are_available(): void
    {
        $state = [
            'energy' => 30,
            'available_trainings' => ['speed', 'stamina'],
            'has_extreme_burst' => false,
            'has_zero_energy_race' => false,
        ];

        $decision = $this->advisor->evaluateAlternatives($state);

        $this->assertEquals('rest', $decision->action);
    }
}
