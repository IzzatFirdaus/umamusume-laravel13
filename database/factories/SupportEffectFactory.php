<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportEffect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportEffect>
 */
class SupportEffectFactory extends Factory
{
    protected $model = SupportEffect::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'effect_id' => fake()->unique()->numberBetween(1, 99),
            'name_en' => fake()->words(2, true),
            'name_ja' => null,
            'calc' => fake()->randomElement(['flat', 'mult', 'add', 'level']),
            'symbol' => '%',
            'description_en' => fake()->sentence(),
            'source_url' => 'https://gametora.com/data/umamusume/support_effects.test.json',
            'fetched_at' => now(),
        ];
    }
}
