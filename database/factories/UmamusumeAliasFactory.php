<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AliasLanguage;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmamusumeAlias>
 */
class UmamusumeAliasFactory extends Factory
{
    protected $model = UmamusumeAlias::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umamusume_id' => Umamusume::factory(),
            'alias' => fake()->unique()->words(2, true),
            'language' => AliasLanguage::Japanese,
        ];
    }
}
