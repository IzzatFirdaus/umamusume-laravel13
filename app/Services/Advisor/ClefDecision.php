<?php

declare(strict_types=1);

namespace App\Services\Advisor;

/**
 * A validated Clef answer (Advisor AI slice, Phase E).
 *
 * `probabilities` and `confidence` are the provider's own preference figures and are carried
 * unchanged so a diagnostic view can show them. **They are not game odds.** Nothing in this
 * application may render them as a chance of winning a race, of a training failure, or of a skill
 * activating — the only sourced statement about training failure is that it scales inversely with
 * Energy, and no source publishes a curve (`ADR-0001` §3).
 *
 * `revision` is the snapshot fingerprint the request carried. The caller compares it against the
 * snapshot it still holds before using the answer; a provider that echoed a different revision is
 * answering about a different turn.
 */
final readonly class ClefDecision
{
    /**
     * @param  array<string, float>  $probabilities
     */
    public function __construct(
        public string $choice,
        public string $model,
        public array $probabilities,
        public ?float $confidence,
        public string $revision,
    ) {}

    /**
     * Whether the answer is an abstention rather than a ranked action.
     */
    public function abstained(): bool
    {
        return $this->choice === CandidateActions::ABSTAIN;
    }
}
