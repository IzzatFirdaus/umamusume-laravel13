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
     * Position 1 by default. Not a random one to six: `deck_slots` has a unique constraint on
     * (training_run_id, slot_position), so a randomised default makes any test that builds several
     * slots for one run intermittently collide, and a sequence counter does not help either because
     * Laravel keys it on the factory instance and each `DeckSlot::factory()` call is a fresh one.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory(),
            'support_card_id' => SupportCard::factory(),
            'slot_position' => DeckSlot::MIN_POSITION,
        ];
    }

    /**
     * The friend slot, position six. The role belongs to the position and not to the card placed in
     * it (ADR-0014 correction 1), so this state says nothing about the card's type.
     */
    public function friendSlot(): static
    {
        return $this->state(fn (array $attributes) => ['slot_position' => DeckSlot::MAX_POSITION]);
    }

    /**
     * Place the slot at one of the six positions, failing loudly for anything else. Silently writing an
     * out-of-range position would let a test assert against a deck the app refuses to store.
     */
    public function atPosition(int $position): static
    {
        if (! in_array($position, DeckSlot::POSITIONS, true)) {
            throw new \InvalidArgumentException("Position {$position} is not one of the six a deck has.");
        }

        return $this->state(fn (array $attributes) => ['slot_position' => $position]);
    }
}
