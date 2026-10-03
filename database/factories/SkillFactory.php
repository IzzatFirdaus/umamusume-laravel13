<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /**
     * `match_key` is absent here on purpose. `Skill::newFactory()` fills it from whatever name the row
     * finally carries, which is the only shape that cannot drift: this file used to derive the key with
     * `Str::slug`, a second algorithm that transliterates punctuation away, and 119 of 200 sampled Global
     * skill names got a key the pipeline never writes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'name_ja' => null,
            'sp_cost' => fake()->numberBetween(10, 400),
            'type' => null,
            'is_unique' => false,
        ];
    }
}
