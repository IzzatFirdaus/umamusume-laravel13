<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportCard>
 */
class SupportCardFactory extends Factory
{
    protected $model = SupportCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['speed', 'stamina', 'power', 'guts', 'intelligence', 'friend', 'group'];
        $rarity = fake()->randomElement([1, 2, 3]);
        $type = fake()->randomElement($types);

        return [
            'support_id' => fake()->unique()->numberBetween(10001, 99999),
            'char_id' => null,
            'name' => fake()->words(3, true).' [Test Card]',
            'name_ja' => null,
            'title_en' => 'Test Card',
            'title_ja' => null,
            'rarity' => $rarity,
            'type' => $type,
            'release_jp' => fake()->optional()->date(),
            'release_global' => fake()->optional()->date(),
            'effects' => [],
            'source_url' => 'https://gametora.com/data/umamusume/support-cards.test.json',
            'fetched_at' => now(),
            'is_manual' => false,
        ];
    }

    public function ssr(): static
    {
        return $this->state(fn (array $attributes) => ['rarity' => 3]);
    }

    public function sr(): static
    {
        return $this->state(fn (array $attributes) => ['rarity' => 2]);
    }

    public function r(): static
    {
        return $this->state(fn (array $attributes) => ['rarity' => 1]);
    }

    public function speed(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'speed']);
    }

    public function stamina(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'stamina']);
    }

    public function power(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'power']);
    }

    public function guts(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'guts']);
    }

    public function wit(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'intelligence']);
    }

    public function friend(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'friend']);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'group']);
    }
}
