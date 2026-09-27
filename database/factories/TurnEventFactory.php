<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TurnEventType;
use App\Models\TrainingRun;
use App\Models\TurnEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TurnEvent>
 */
class TurnEventFactory extends Factory
{
    protected $model = TurnEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'turn' => fake()->numberBetween(1, 72),
            'event_type' => TurnEventType::Character,
            'source_name' => fake()->sentence(3),
            'choice_index' => null,
            'choice_label' => null,
            'deltas' => null,
            'support_card_name' => null,
            'bond_delta' => null,
            'origin_note' => null,
        ];
    }

    /**
     * @return $this
     */
    public function supportCard(): static
    {
        return $this->state(fn (): array => [
            'event_type' => TurnEventType::SupportCard,
            'support_card_name' => 'AGNES TACHIBANA',
            'bond_delta' => fake()->numberBetween(1, 10),
            'deltas' => ['energy' => -5, 'speed' => 12],
        ]);
    }
}
