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
     * `match_key` is derived by `Umamusume::newFactory()` from the name the row finally carries, so the
     * key and the display name cannot disagree and cannot use an algorithm the import does not.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'name_ja' => null,
            'release_status' => ReleaseStatus::GlobalReleased,
            'jp_debut_date' => null,
            'global_debut_date' => null,
            'is_manual' => false,
            // Aptitudes are recorded per trainee by the roster import. Null means the
            // source did not publish that aptitude, not a default grade.
            'aptitude_turf' => null,
            'aptitude_dirt' => null,
            'aptitude_sprint' => null,
            'aptitude_mile' => null,
            'aptitude_medium' => null,
            'aptitude_long' => null,
            'aptitude_front_runner' => null,
            'aptitude_pace_chaser' => null,
            'aptitude_late_surger' => null,
            'aptitude_end_closer' => null,
            'external_ref' => null,
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
