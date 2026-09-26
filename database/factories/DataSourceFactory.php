<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DataSource;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataSource>
 */
class DataSourceFactory extends Factory
{
    protected $model = DataSource::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umamusume_id' => Umamusume::factory(),
            'url' => 'https://example.test/'.fake()->unique()->slug(3),
            'source_key' => 'test',
            'fetched_at' => now(),
            'snapshot_path' => null,
            'confidence' => 1.0,
            'source_timezone' => 'Asia/Tokyo',
        ];
    }
}
