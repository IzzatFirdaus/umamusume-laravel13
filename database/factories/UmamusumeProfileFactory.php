<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmamusumeProfile>
 */
class UmamusumeProfileFactory extends Factory
{
    protected $model = UmamusumeProfile::class;

    /**
     * Every string here is a real `[Global]` string, read out of the `characters` document on
     * 2026-09-29 for `char_id` 1001 — G-16 forbids inventing fixture copy, and a Japanese name
     * is the one string in this repo where an invented one would be obvious only to a reader
     * who reads Japanese. The numbers are that row's own, not faked ranges, so a test that
     * asserts on a rendered profile is asserting on a plausible trainee.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umamusume_id' => Umamusume::factory(),
            'name_ja' => 'スペシャルウィーク',
            'va_ja' => '和氣あず未',
            'va_en' => 'Azumi Waki',
            'birth_year' => 1995,
            'birth_month' => 5,
            'birth_day' => 2,
            'height' => 158,
            'three_sizes_b' => 81,
            'three_sizes_h' => 81,
            'three_sizes_w' => 56,
            // The migration makes source_url NOT NULL, so a factory that omits it cannot insert.
            'source_url' => 'https://gametora.test/characters.json',
            'snapshot_path' => null,
            'fetched_at' => now(),
            'source_timezone' => 'Asia/Tokyo',
            'is_manual' => false,
        ];
    }

    /**
     * FR-B-4's stop sign: a Trainer who corrected this row by hand.
     */
    public function manual(): static
    {
        return $this->state(fn (): array => ['is_manual' => true]);
    }

    /**
     * A trainee the document covers but describes only in part.
     *
     * This is a measured shape rather than a hypothetical one: `va_en` and `three_sizes` are each
     * absent on 10 of the 135 roster rows, so a view test that only ever renders a complete
     * profile would never exercise the D-220 path the real data takes.
     */
    public function partial(): static
    {
        return $this->state(fn (): array => [
            'va_en' => null,
            'three_sizes_b' => null,
            'three_sizes_h' => null,
            'three_sizes_w' => null,
        ]);
    }

    /**
     * The other measured gap: `birth_year` is the only birthday part the document ever omits
     * (17 of its 163 rows).
     */
    public function withoutBirthYear(): static
    {
        return $this->state(fn (): array => ['birth_year' => null]);
    }
}
