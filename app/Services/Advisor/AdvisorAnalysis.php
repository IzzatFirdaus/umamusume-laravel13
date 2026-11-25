<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The Advisor AI pipeline: snapshot, candidates, evidence, optional Clef ranking, explanation, and
 * a validated response (Advisor AI slice, Phase H).
 *
 * **The order is the guarantee.** Every stage can fail and the answer degrades to what the previous
 * stage produced. With the switch off, with no key, with Ollama down and with Logfare unreachable,
 * `analyze()` still returns the engine's own recommendation — which is why the deterministic rail
 * was not modified to make room for this class (`NextDecision` is untouched).
 *
 * **A recommendation is never written anywhere.** This service reads models and caches a payload; it
 * has no save, no update and no dispatch. That is not a convention to remember but the reason the
 * class takes only a run and a turn: there is nothing else it could write through.
 *
 * **Cache key carries the revision.** The fingerprint hashes the turn, the readings and the target,
 * so logging a turn invalidates the entry by construction rather than by expiry. `cache.ttl` is the
 * outer bound for a state nobody touches, not the mechanism.
 */
final class AdvisorAnalysis
{
    /** Bumped when the response shape changes, so an old cached payload is never served under it. */
    public const CONTRACT_VERSION = '1';

    public function __construct(
        private readonly TrainerAdvisor $advisor,
        private readonly AdvisorRetriever $retriever,
        private readonly ClefDecisionProvider $clef,
        private readonly AdvisorExplainer $explainer,
    ) {}

    /**
     * The full analysis for one turn, or the deterministic fallback when anything is unavailable.
     *
     * `$objectives` is the caller's already-ordered calendar payload, so a mandatory obligation is
     * named from the read the screen already made rather than a second `race_catalog_slots` query.
     *
     * @param  list<array<string, mixed>>  $objectives
     * @return array<string, mixed>
     */
    public function analyze(TrainingRun $run, ?TurnEntry $latest, array $objectives = []): array
    {
        $startedAt = microtime(true);
        $snapshot = AdvisorSnapshot::forRun($run, $latest);
        $advice = $this->advisor->advise($latest, $run->buildTarget(), $run);
        $candidates = CandidateActions::from($advice, $run, $latest, $objectives);

        $key = $this->cacheKey($snapshot);

        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get($key);

        if (is_array($cached) && ($cached['career_revision'] ?? null) === $snapshot->revision) {
            return $cached;
        }

        // The engine's own answer is the floor. Everything below either improves its explanation or
        // changes nothing at all.
        $deterministicId = $advice->recommendation === null
            ? null
            : CandidateActions::id($advice->recommendation->action);

        // With the switch off there is nothing to retrieve for: no ranking call follows, and no
        // explanation provider is consulted. Retrieving anyway would mean a disabled feature still
        // calling the local embedding model on every refresh, which is both wasted work and a
        // violation of what "off" means to the Trainer who turned it off.

        $totalTimeout = (float) config('advisor_ai.total_timeout');
        $deadline = $startedAt + $totalTimeout;

        $retrieval = (bool) config('advisor_ai.enabled')
            ? $this->retrieve($snapshot, $advice, $candidates, $deadline)
            : ['evidence' => [], 'degraded' => false, 'lexical_count' => 0, 'semantic_count' => 0];
        $evidence = $retrieval['evidence'];

        $decision = null;
        $fallbackUsed = true;

        // Clef is only asked to choose when the engine actually ranked more than one action.
        // With zero candidates there is nothing to choose; with exactly one the engine has
        // already established the only permissible choice, and calling the provider adds
        // latency, cost and a failure point for no selection benefit (Phase 1.2).
        if ((bool) config('advisor_ai.enabled')
            && $deterministicId !== null
            && count($candidates->eligible) > 1) {
            $remainingMs = (int) max(0, ($deadline - microtime(true)) * 1000);
            if ($remainingMs > 0) {
                $decision = $this->clef->decide($snapshot, $candidates, $evidence, $deadline);
            }

            if ($decision !== null && $decision->revision === $snapshot->revision) {
                $fallbackUsed = $decision->abstained();
            } else {
                $decision = null;
            }
        }

        // Clef missing, refusing or abstaining all land on the engine's own choice. An abstention is
        // not an instruction to hide the recommendation: the Trainer still needs the rail's answer,
        // and "the model would not choose" is a statement about the model, not about the turn.
        $chosenId = $decision === null || $decision->abstained() ? $deterministicId : $decision->choice;

        $remainingMs = (int) max(0, ($deadline - microtime(true)) * 1000);

        if ($chosenId === null) {
            $explanation = [
                'text' => $advice->absence ?? 'No recommendation can be stated for this turn.',
                'source' => 'deterministic',
                'degraded' => false,
            ];
        } elseif ($remainingMs <= 0) {
            // Total budget exhausted: ship the deterministic template rather than starting an
            // optional inference call that cannot finish inside the interactive budget.
            $label = $this->labelFor($chosenId, $candidates);
            $reason = $this->reasonFor($chosenId, $candidates);
            $sentence = $reason === null
                ? "{$label} is recommended."
                : $reason;
            if ($evidence !== []) {
                $sentence .= ' Source: '.$evidence[0]->citation().'.';
            }
            $explanation = [
                'text' => $sentence,
                'source' => 'deterministic',
                'degraded' => true,
            ];
        } else {
            $explanation = $this->explainer->explain(
                $snapshot,
                $candidates,
                $chosenId,
                $evidence,
                $this->reasonFor($chosenId, $candidates),
                $deadline,
            );
        }

        $payload = [
            'contract_version' => self::CONTRACT_VERSION,
            'career_id' => $snapshot->careerId,
            'career_revision' => $snapshot->revision,
            'turn' => $snapshot->turn,
            'scenario' => $snapshot->scenarioLabel,
            'recommended_action' => $chosenId === null ? null : [
                'id' => $chosenId,
                'label' => $this->labelFor($chosenId, $candidates),
                'kind' => $this->kindFor($chosenId, $candidates),
                'eligibility' => 'eligible',
            ],
            'deterministic_action' => $deterministicId === null ? null : [
                'id' => $deterministicId,
                'label' => $this->labelFor($deterministicId, $candidates),
            ],
            'decision' => [
                'provider' => $decision === null ? 'deterministic' : 'clef',
                'model' => $decision === null ? null : $decision->model,
                'status' => $decision === null ? 'fallback' : ($decision->abstained() ? 'abstained' : 'validated'),
                'fallback_used' => $decision === null || $fallbackUsed,
                // Preference figures, carried for a diagnostic view and never rendered as odds.
                'preference' => $decision === null ? [] : $decision->probabilities,
                'confidence' => $decision === null ? null : $decision->confidence,
            ],
            'explanation' => $explanation['text'],
            'explanation_source' => $explanation['source'],
            'evidence' => array_map(
                static fn (Evidence $e): array => [
                    'id' => $e->id,
                    'path' => $e->path,
                    'heading' => $e->heading,
                    'line' => $e->line,
                    'servers' => $e->servers,
                    'citation' => $e->citation(),
                ],
                $evidence,
            ),
            'retrieval' => [
                'degraded' => $retrieval['degraded'],
                'lexical_count' => $retrieval['lexical_count'],
                'semantic_count' => $retrieval['semantic_count'],
            ],
            'unknowns' => $snapshot->unknowns,
            'conflicts' => [],
            'obligation' => $candidates->obligation,
            'alternatives' => $this->alternatives($chosenId, $deterministicId, $candidates),
            'blocked' => $candidates->blocked,
            'generated_at' => gmdate('c'),
        ];

        Cache::put($key, $payload, (int) config('advisor_ai.cache.ttl'));

        return $payload;
    }

    /**
     * Retrieves evidence, degrading to an empty list rather than throwing.
     *
     * The query is composed from recorded state only: the scenario's label, the actions on the table,
     * and the names of what is unrecorded. No note text is included, because a Trainer's free prose
     * is not a search term and would pull passages about whatever they happened to write.
     *
     * @param  ?float  $deadline  Absolute microtime deadline; passed to retriever for budget enforcement.
     * @return array{evidence: list<Evidence>, degraded: bool, lexical_count: int, semantic_count: int}
     */
    private function retrieve(AdvisorSnapshot $snapshot, Advice $advice, CandidateActions $candidates, ?float $deadline = null): array
    {
        $parts = [$snapshot->scenarioLabel];
        $parts[] = 'training decision';

        foreach ($advice->options as $option) {
            $parts[] = $option->action;
        }

        if ($snapshot->energy === null) {
            $parts[] = 'energy not recorded';
        }

        if ($candidates->obligation !== null) {
            $parts[] = 'mandatory race deadline';
        }

        try {
            return $this->retriever->retrieve(
                implode(' ', $parts),
                (int) config('advisor_ai.retrieval.max_evidence'),
                $deadline,
            );
        } catch (Throwable $e) {
            // An index that was never built is not a failure of the career: the answer still ships,
            // with no citations and `degraded` true so a surface can say so.
            return ['evidence' => [], 'degraded' => true, 'lexical_count' => 0, 'semantic_count' => 0];
        }
    }

    /**
     * @return list<array{id: string, label: string, reason: string}>
     */
    private function alternatives(?string $chosenId, ?string $deterministicId, CandidateActions $candidates): array
    {
        $rows = [];

        foreach ($candidates->eligible as $candidate) {
            if ($candidate['id'] === $chosenId) {
                continue;
            }

            $rows[] = [
                'id' => $candidate['id'],
                'label' => $candidate['label'],
                'reason' => $candidate['reason'],
                'engine_choice' => $candidate['id'] === $deterministicId,
            ];
        }

        return $rows;
    }

    private function reasonFor(string $id, CandidateActions $candidates): ?string
    {
        foreach ($candidates->eligible as $candidate) {
            if ($candidate['id'] === $id) {
                return $candidate['reason'];
            }
        }

        return null;
    }

    private function labelFor(string $id, CandidateActions $candidates): string
    {
        foreach ($candidates->eligible as $candidate) {
            if ($candidate['id'] === $id) {
                return $candidate['label'];
            }
        }

        return $id;
    }

    private function kindFor(string $id, CandidateActions $candidates): string
    {
        foreach ($candidates->eligible as $candidate) {
            if ($candidate['id'] === $id) {
                return $candidate['kind'];
            }
        }

        return 'training';
    }

    /**
     * The cache key: the contract version, the career, and the revision fingerprint.
     *
     * The contract version is in the key so a shape change cannot serve an old payload, and the
     * revision is in the key so a logged turn is a different entry rather than an invalidated one.
     */
    private function cacheKey(AdvisorSnapshot $snapshot): string
    {
        return 'advisor-ai:'.self::CONTRACT_VERSION.":{$snapshot->careerId}:{$snapshot->revision}";
    }
}
