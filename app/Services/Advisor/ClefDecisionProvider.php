<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The Clef decision adapter: ranks the candidates the deterministic engine already validated
 * (Advisor AI slice, Phase E).
 *
 * **The boundary this class enforces.** Clef answers with an option id and per-option scores. It
 * never returns prose, never returns a career value, and is never asked what is *possible* — that
 * question is `TrainerAdvisor`'s and it has already answered before this runs. A response naming an
 * id that was not in the supplied set is discarded rather than repaired, because a model choosing
 * an option it was not offered is the one failure mode that can produce a recommendation the game
 * would refuse.
 *
 * **Scores are preferences, not probabilities.** `probabilities` in the provider's envelope describe
 * how strongly the model prefers one supplied option over another. They are not a chance of winning
 * a race, of a training failure, or of anything else in the game, and this class never relabels them
 * as one (`AGENTS.md` §5, `ADR-0001` §3, `ADR-0020` §2).
 *
 * **Every failure returns null.** A closed switch, a missing key, a 403 from an account without the
 * training opt-in, a 429, a 5xx, a timeout and a malformed body are all the same outcome from the
 * caller's side: no AI decision, deterministic recommendation stands. Retries are bounded and only
 * attempted for the transient class.
 */
final class ClefDecisionProvider
{
    /**
     * Asks Clef to rank the candidates, or returns null.
     *
     * `$revision` travels with the request and is echoed in the result so a caller can prove the
     * answer belongs to the state it asked about. The provider is not trusted to preserve it; the
     * caller compares against the snapshot it still holds.
     *
     * @param  list<Evidence>  $evidence
     */
    public function decide(
        AdvisorSnapshot $snapshot,
        CandidateActions $candidates,
        array $evidence,
        ?float $deadline = null,
    ): ?ClefDecision {
        if ($candidates->eligible === []) {
            return null;
        }

        $apiKey = config('advisor_ai.logfare.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            Log::info('Advisor: no Logfare key configured; using the deterministic recommendation.');

            return null;
        }

        $endpoint = (string) config('advisor_ai.decision.endpoint');
        $payload = $this->payload($snapshot, $candidates, $evidence);
        $configuredTimeout = (float) config('advisor_ai.decision.timeout');
        $attempts = max(0, (int) config('advisor_ai.decision.retries')) + 1;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            // The total request budget caps each attempt. When the deadline has passed, stop.
            if ($deadline !== null && microtime(true) >= $deadline) {
                return null;
            }
            $timeout = $deadline !== null
                ? min($configuredTimeout, max(0.0, $deadline - microtime(true)))
                : $configuredTimeout;

            try {
                $response = Http::timeout($timeout)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->post($endpoint, $payload);

                if ($response->successful()) {
                    return $this->parse($response->json(), $candidates, $snapshot->revision);
                }

                if (in_array($response->status(), [401, 403], true)) {
                    Log::warning('Advisor: Logfare refused the decision request.', [
                        'status' => $response->status(),
                        'body' => mb_substr($response->body(), 0, 300),
                    ]);

                    return null;
                }

                if ($response->status() === 429) {
                    Log::info('Advisor: Logfare rate-limited the decision request.', [
                        'retry_after' => $response->header('Retry-After'),
                    ]);

                    return null;
                }

                if ($response->serverError() && $attempt < $attempts) {
                    continue;
                }

                return null;
            } catch (ConnectionException $e) {
                if ($attempt < $attempts && ($deadline === null || microtime(true) < $deadline)) {
                    continue;
                }

                Log::info('Advisor: Logfare decision request timed out: '.$e->getMessage());

                return null;
            } catch (Throwable $e) {
                Log::warning('Advisor: Logfare decision request failed: '.$e->getMessage());

                return null;
            }
        }

        return null;
    }

    /**
     * The request body, built only from validated state and retrieved evidence.
     *
     * No note, team motto, trainee name or file path from outside `docs/` appears here. The
     * `instructions` string is fixed rather than composed from user input, so nothing a Trainer
     * typed can arrive at the provider as an instruction (`config/advisor_ai.php`).
     *
     * @param  list<Evidence>  $evidence
     * @return array<string, mixed>
     */
    private function payload(AdvisorSnapshot $snapshot, CandidateActions $candidates, array $evidence): array
    {
        $maxChars = (int) config('advisor_ai.retrieval.max_chunk_chars_sent');

        return [
            'model' => (string) config('advisor_ai.decision.model'),
            'state' => [
                'career' => $snapshot->forProvider(),
                'candidates' => $candidates->forProvider(),
                'evidence' => array_map(
                    static fn (Evidence $e): array => $e->forProvider($maxChars),
                    $evidence,
                ),
                'source_conflicts' => [],
                'missing_information' => $snapshot->unknowns,
            ],
            'questions' => [
                'recommendation' => [
                    'type' => 'choice',
                    'instructions' => 'Choose the best defensible action using only the supplied facts. '
                        .'Do not treat an unknown value as zero. Do not introduce a fact that is not in '
                        .'the supplied evidence. Select insufficient_evidence when the recorded state '
                        .'does not support a reliable preference.',
                    'criteria' => $candidates->criteria(),
                ],
            ],
        ];
    }

    /**
     * Reads the documented envelope and rejects anything that does not fit it.
     *
     * Shape per `https://logfare.ai/docs`: `{"result": {"model", "answers": {"<id>": {type, choice,
     * probabilities, confidence}}}, "success": bool}`. Each check below corresponds to a test case;
     * the strictness is the point, because a half-parsed answer is a recommendation nobody validated.
     *
     * @param  array<string, mixed>  $body
     */
    private function parse(array $body, CandidateActions $candidates, string $revision): ?ClefDecision
    {
        if (($body['success'] ?? null) !== true) {
            return null;
        }

        $result = $body['result'] ?? null;

        if (! is_array($result)) {
            return null;
        }

        $answers = $result['answers'] ?? null;

        if (! is_array($answers)) {
            return null;
        }

        $answer = $answers['recommendation'] ?? null;

        if (! is_array($answer) || ($answer['type'] ?? null) !== 'choice') {
            return null;
        }

        $choice = $answer['choice'] ?? null;

        if (! is_string($choice) || ! $candidates->allows($choice)) {
            Log::info('Advisor: Clef chose an option that was not offered.', ['choice' => $choice]);

            return null;
        }

        $probabilities = $answer['probabilities'] ?? [];

        if (! is_array($probabilities)) {
            return null;
        }

        foreach ($probabilities as $id => $value) {
            // An option the model was not asked about appearing in the distribution is a malformed
            // answer, not a bonus datapoint.
            if (! is_string($id) || ! $candidates->allows($id) || ! is_numeric($value)) {
                return null;
            }
        }

        $confidence = $answer['confidence'] ?? null;

        return new ClefDecision(
            choice: $choice,
            model: is_string($result['model'] ?? null) ? $result['model'] : 'unknown',
            probabilities: array_map(static fn (mixed $v): float => (float) $v, $probabilities),
            confidence: is_numeric($confidence) ? (float) $confidence : null,
            revision: $revision,
        );
    }
}
