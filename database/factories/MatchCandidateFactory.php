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
            'proposed_match_key' => mb_strtolower(str_replace(' ', '', $name)),
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
