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
     * `calc` and `symbol` are the export's own vocabularies, not this repository's. Across all 35
     * records in `support_effects.json` `calc` is only ever `mult`, `add` or absent, and `symbol` is
     * `percent`, `none`, `level` or absent. The defaults are effect id 1 read off the export.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'effect_id' => fake()->unique()->numberBetween(1000, 9999),
            'name_en' => 'Friendship Bonus',
            'name_ja' => '友情ボーナス',
            'calc' => 'mult',
            'symbol' => 'percent',
            'description_en' => 'Increases the effectiveness of Friendship Training',
            'source_url' => 'https://gametora.com/data/umamusume/support_effects.json',
            'fetched_at' => now(),
        ];
    }

    /**
     * The 31 of 35 records that declare no `calc` at all. This is the common case, and it is the one
     * UMAMUSUME_REFERENCE.md §1.4.8 turns on: only the effects that do declare it combine
     * multiplicatively, so a test for ordinary flat effects has to leave the column empty rather than
     * write a word into it that the client never sends.
     */
    public function undeclared(): static
    {
        return $this->state(fn (array $attributes) => ['calc' => null, 'symbol' => 'none']);
    }

    public function additive(): static
    {
        return $this->state(fn (array $attributes) => ['calc' => 'add']);
    }
}
