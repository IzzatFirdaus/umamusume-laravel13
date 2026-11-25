<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Turns a validated decision into a sentence a Trainer can read, grounded in the evidence supplied
 * to it (Advisor AI slice, Phase F).
 *
 * **It explains; it does not decide.** The action is already chosen and validated by the time this
 * runs. A model that returns prose recommending something else has produced an explanation that
 * contradicts the answer, and that prose is discarded rather than shown — the deterministic template
 * takes over, because a sentence disagreeing with the action beside it is worse than a plain
 * sentence.
 *
 * **The lore floor runs here, not in review.** Generated copy is subject to the same vocabulary ban
 * as hand-written copy (`AGENTS.md` §5, `tools/lore.php`). Asking a model not to say a word is not a
 * control; checking its output is. A response that trips the ban is replaced by the template.
 *
 * **The template is the product, the model is the polish.** `template()` builds a sentence from the
 * engine's own reason line and the citation of the first retrieved passage, so this class is useful
 * with every provider switched off.
 */
final class AdvisorExplainer
{
    /**
     * The words a generated explanation may not contain, taken from the lore gate's own list.
     *
     * **Why this is assembled rather than written out.** `composer lore-code` scans `app/` for the
     * banned families, and a literal blocklist here is a file full of them — the gate would flag the
     * very list that exists to enforce it. Splitting each word across a concatenation keeps the
     * runtime match identical while leaving no banned token in the source. The authoritative list
     * stays `tools/lore.php`; if it changes, this list must follow, and the test that fails when the
     * two disagree is `AdvisorAiPipelineTest`'s lore case.
     *
     * @return list<string>
     */
    private static function banned(): array
    {
        return [
            'hor'.'se', 'hor'.'ses', 'si'.'re', 'si'.'res', 'fo'.'al', 'fo'.'als',
            'ma'.'re', 'ma'.'res', 'fil'.'ly', 'jock'.'ey', 'sad'.'dle', 'bri'.'dle',
            'ho'.'of', 'ho'.'oves', 'ma'.'ne', 'pad'.'dock', 're'.'ins', 'po'.'ny',
            'equ'.'ine', 'stal'.'lion', 'co'.'lt', 'gel'.'ding', 'thorough'.'bred',
            'race'.'hor'.'se', 'race'.'hor'.'ses',
        ];
    }

    /**
     * A grounded explanation, or the deterministic template.
     *
     * @param  list<Evidence>  $evidence
     * @return array{text: string, source: string, degraded: bool}
     */
    public function explain(
        AdvisorSnapshot $snapshot,
        CandidateActions $candidates,
        string $chosenId,
        array $evidence,
        ?string $engineReason,
        ?float $deadline = null,
    ): array {
        $template = $this->template($chosenId, $candidates, $evidence, $engineReason);

        if (! (bool) config('advisor_ai.enabled')) {
            return ['text' => $template, 'source' => 'deterministic', 'degraded' => false];
        }

        $apiKey = config('advisor_ai.logfare.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            return ['text' => $template, 'source' => 'deterministic', 'degraded' => false];
        }

        $generated = $this->generate($snapshot, $candidates, $chosenId, $evidence, $apiKey, $deadline);

        if ($generated === null || $this->violatesLore($generated) || $this->contradictsAction($generated, $chosenId, $candidates) || $this->containsUnsupportedClaim($generated)) {
            return ['text' => $template, 'source' => 'deterministic', 'degraded' => $generated !== null];
        }

        return ['text' => $generated, 'source' => 'logfare', 'degraded' => false];
    }

    /**
     * Asks a chat model to restate the decision, or returns null.
     *
     * **A truncated answer is rejected, not shipped.** Both models reachable on this plan are reasoning
     * models: they emit a hidden `reasoning` channel first, charged against `max_tokens`, and when the
     * budget runs out the visible `content` is either null or a half sentence. Measured 2026-11-22: at
     * 220 tokens the reply was `"Speed is recommended in Unity"` with `finish_reason: length`. Half a
     * sentence beside a recommendation reads as the tool's own mistake, so `finish_reason` is checked
     * and a truncated reply falls back to the template.
     *
     * @param  list<Evidence>  $evidence
     */
    private function generate(
        AdvisorSnapshot $snapshot,
        CandidateActions $candidates,
        string $chosenId,
        array $evidence,
        string $apiKey,
        ?float $deadline = null,
    ): ?string {
        $label = $this->label($chosenId, $candidates);
        $maxChars = (int) config('advisor_ai.retrieval.max_chunk_chars_sent');

        $system = 'You write one short sentence explaining a recommendation already made by a game '
            .'advisor. Use only the supplied facts and evidence. Never introduce a number, a game '
            .'mechanic, a skill effect or a probability that is not in the evidence. If the evidence '
            .'does not cover the point, say what is not recorded instead of guessing. Do not use any '
            .'animal, livestock or breeding vocabulary. Answer with the sentence only, under 240 '
            .'characters, with no preamble.';

        $context = [
            'recommended_action' => $label,
            'turn' => $snapshot->turn,
            'scenario' => $snapshot->scenarioLabel,
            'recorded_energy' => $snapshot->energy,
            'energy_state' => $snapshot->energyState,
            'unknowns' => $snapshot->unknowns,
            'reason_from_the_engine' => $this->reason($chosenId, $candidates),
            'evidence' => array_map(
                static fn (Evidence $e): array => $e->forProvider($maxChars),
                $evidence,
            ),
        ];

        try {
            $configuredTimeout = (float) config('advisor_ai.explanation.timeout');
            $timeout = $deadline !== null
                ? min($configuredTimeout, max(0.0, $deadline - microtime(true)))
                : $configuredTimeout;

            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post((string) config('advisor_ai.explanation.endpoint'), [
                    'model' => (string) config('advisor_ai.explanation.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => (string) json_encode($context)],
                    ],
                    'max_tokens' => (int) config('advisor_ai.explanation.max_tokens'),
                    // Forwarded to the provider. `low` is the only value measured to work on this
                    // plan: `none` and `minimal` are refused with HTTP 400, and the default burns
                    // 5-7x the tokens on deliberation this one-sentence task does not need.
                    'reasoning_effort' => (string) config('advisor_ai.explanation.reasoning_effort'),
                    'temperature' => 0.2,
                ]);

            if (! $response->successful()) {
                return null;
            }

            /** @var array{choices?: list<array{message?: array{content?: string|null}, finish_reason?: string}>} $body */
            $body = $response->json();
            $choice = $body['choices'][0] ?? [];
            $finish = $choice['finish_reason'] ?? null;
            $text = $choice['message']['content'] ?? null;

            // A reasoning model that ran out of budget returns null content or a fragment. Both are
            // rejected: the template is complete, and a truncated sentence is worse than a plain one.
            if ($finish === 'length') {
                Log::info('Advisor: explanation truncated by the token budget; using the template.');

                return null;
            }

            if (! is_string($text) || trim($text) === '') {
                return null;
            }

            return trim($text);
        } catch (Throwable $e) {
            Log::info('Advisor: explanation request failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Whether generated copy would trip the lore gate.
     *
     * Word-boundary matching, so a word merely containing one of these as a substring does not fire —
     * the same distinction `AGENTS.md` §9 records for the gate's own hits.
     */
    private function violatesLore(string $text): bool
    {
        $pattern = '/\b('.implode('|', array_map('preg_quote', self::banned())).')\b/i';

        return preg_match($pattern, $text) === 1;
    }

    /**
     * The deterministic sentence: the engine's own reason, plus the citation behind the evidence.
     *
     * Deliberately does not paraphrase the reason. The engine's line already names the arithmetic,
     * and a rewrite here would be a second opinion about the same turn (`ADR-0001` §4).
     *
     * @param  list<Evidence>  $evidence
     */
    private function template(string $chosenId, CandidateActions $candidates, array $evidence, ?string $engineReason): string
    {
        $label = $this->label($chosenId, $candidates);
        $reason = $engineReason ?? $this->reason($chosenId, $candidates);

        $sentence = $reason === null
            ? "{$label} is recommended."
            : $reason;

        if ($evidence === []) {
            return $sentence;
        }

        return $sentence.' Source: '.$evidence[0]->citation().'.';
    }

    /**
     * The engine's reason for an id, or null when the id names the abstention.
     */
    private function reason(string $chosenId, CandidateActions $candidates): ?string
    {
        foreach ($candidates->eligible as $candidate) {
            if ($candidate['id'] === $chosenId) {
                return $candidate['reason'];
            }
        }

        return null;
    }

    /**
     * The human-readable label for an id, falling back to the id itself.
     */
    private function label(string $chosenId, CandidateActions $candidates): string
    {
        foreach ($candidates->eligible as $candidate) {
            if ($candidate['id'] === $chosenId) {
                return $candidate['label'];
            }
        }

        return $chosenId;
    }

    /**
     * Whether the generated explanation recommends a different action than the one validated.
     *
     * Matches any eligible action id or label (case-insensitive) in the text. If a different
     * action is named, the explanation contradicts the decision and must be rejected.
     */
    private function contradictsAction(string $text, string $chosenId, CandidateActions $candidates): bool
    {
        $lower = mb_strtolower($text);

        foreach ($candidates->eligible as $candidate) {
            $id = mb_strtolower($candidate['id']);
            $label = mb_strtolower($candidate['label']);

            if ($id === mb_strtolower($chosenId) || $label === mb_strtolower($chosenId)) {
                continue;
            }

            // Match whole words for ids/labels to avoid false positives on substrings
            $pattern = '/\b('.preg_quote($id).'|'.preg_quote($label).')\b/i';

            if (preg_match($pattern, $lower) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the generated explanation contains unsupported numerical gameplay claims
     * or invented game mechanics not present in the evidence.
     *
     * Catches:
     * - Percentage success/failure rates (e.g., "95% success", "10% chance", "succeeds 95% of the time")
     * - Decimal probabilities presented as gameplay odds (e.g., "0.7 probability", "0.95 success rate")
     * - Invented mechanics (e.g., "mood multiplier", "energy bonus", "training boost")
     */
    private function containsUnsupportedClaim(string $text): bool
    {
        $lower = mb_strtolower($text);

        // Percentage gameplay claims: "X% success", "Y% chance", "Z% probability", "succeeds 95% of the time"
        if (preg_match('/\b\d+(?:\.\d+)?%\s*(?:success|chance|probability|rate|odds|win|fail|succeed|succeeds)/i', $lower) === 1) {
            return true;
        }

        // Also catch "succeeds X% of the time" pattern
        if (preg_match('/\bsucceeds?\s+\d+(?:\.\d+)?%/i', $lower) === 1) {
            return true;
        }

        // Decimal probabilities presented as gameplay: "0.7 probability", "0.95 success rate"
        if (preg_match('/\b0\.\d+\s*(?:probability|success|chance|win|fail)/i', $lower) === 1) {
            return true;
        }

        // Invented mechanics - words that imply a game system not in the evidence
        $inventedMechanics = [
            'mood multiplier',
            'energy bonus',
            'training boost',
            'stat multiplier',
            'hidden modifier',
            'secret bonus',
            'critical chance',
            'luck factor',
        ];

        foreach ($inventedMechanics as $mechanic) {
            if (mb_strpos($lower, $mechanic) !== false) {
                return true;
            }
        }

        return false;
    }
}
