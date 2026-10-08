<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Veteran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Veteran>
 */
class VeteranFactory extends Factory
{
    protected $model = Veteran::class;

    /**
     * The run defaults to Completed because only a completed run is recordable (FR-G-1), so a
     * factory-built Veteran is one the library would actually accept.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_run_id' => TrainingRun::factory()->state(['status' => RunStatus::Completed]),
            'tags' => [],
            // The twin `RecordVeteran` derives beside the tags (KI-72), so a factory row carries the
            // shape a row written through the app carries: factories bypass the action exactly as they
            // bypass the request.
            'tags_normalized' => fn (array $attributes): array => array_map(
                static fn (string $tag): string => mb_strtolower($tag),
                array_values((array) ($attributes['tags'] ?? [])),
            ),
            'notes' => null,
        ];
    }
}
