<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReleaseStatus;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Umamusume>
 */
class UmamusumeFactory extends Factory
{
    protected $model = Umamusume::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'name_ja' => null,
            'match_key' => mb_strtolower(str_replace('-', '', Str::slug($name))),
            'release_status' => ReleaseStatus::GlobalReleased,
            'jp_debut_date' => null,
            'global_debut_date' => null,
            'is_manual' => false,
        ];
    }

    public function japanOnly(): static
    {
        return $this->state(fn (): array => ['release_status' => ReleaseStatus::JapanOnly]);
    }

    public function manual(): static
    {
        return $this->state(fn (): array => ['is_manual' => true]);
    }
}
