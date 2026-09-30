<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeckSlot>
 */
class DeckSlotFactory extends Factory
{
    protected $model = DeckSlot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'support_card_id' => SupportCard::factory(),
            'slot_position' => fake()->numberBetween(1, 6),
        ];
    }

    public function friendSlot(): static
    {
        return $this->state(fn (array $attributes) => ['slot_position' => 6]);
    }
}
