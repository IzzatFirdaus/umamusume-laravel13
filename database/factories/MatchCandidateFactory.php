<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CandidateStatus;
use App\Enums\MatchTier;
use App\Models\MatchCandidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchCandidate>
 */
class MatchCandidateFactory extends Factory
{
    protected $model = MatchCandidate::class;

    /**
     * `proposed_match_key` is derived by `MatchCandidate::newFactory()` from the name this row carries.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'source_key' => 'test',
            'external_ref' => null,
            'proposed_name' => $name,
            'proposed_name_ja' => null,
            'suggested_umamusume_id' => null,
            'match_tier' => MatchTier::None,
            'status' => CandidateStatus::Pending,
            'payload' => ['name' => $name],
            'created_by_fetch_at' => now(),
        ];
    }

    public function fuzzy(): static
    {
        return $this->state(fn (): array => ['match_tier' => MatchTier::Fuzzy]);
    }
}
