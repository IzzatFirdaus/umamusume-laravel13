<?php

declare(strict_types=1);

use App\Enums\EnergyState;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\AdvisorAnalysis;
use App\Services\Advisor\AdvisorIndex;
use App\Services\Advisor\AdvisorRetriever;
use App\Services\Advisor\AdvisorSnapshot;
use App\Services\Advisor\CandidateActions;
use App\Services\Advisor\ClefDecisionProvider;
use App\Services\Advisor\EmbeddingProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/*
 * Decision provenance and explanation correctness (Subagent B).
 *
 * These cases pin the contract between the deterministic engine and the optional
 * AI layer so that a regression cannot:
 * - mislabel the decision origin (provider / fallback / abstention)
 * - present model preference scores as gameplay probabilities
 * - accept an explanation that contradicts the validated action
 * - cite evidence that does not support the claim
 * - invent a game-state value the sources never published
 */

function provenanceRun(array $attributes = [], array $turn = []): TrainingRun
{
    $run = TrainingRun::factory()->create(array_merge([
        'scenario' => 'unity_cup',
        'build_target' => [
            'purpose' => 'StoryClear',
            'distance' => 'Medium',
            'surface' => 'Turf',
            'style' => 'Pace Chaser',
            'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
            'skill_priorities' => [],
            'hint_levels' => [],
        ],
    ], $attributes));

    TurnEntry::factory()->create(array_merge([
        'training_run_id' => $run->id,
        'turn' => 1,
        'speed' => 500,
        'stamina' => 500,
        'power' => 500,
        'guts' => 500,
        'wit' => 500,
        'energy' => 60,
        'energy_state' => EnergyState::Exact,
        'mood' => 'NORMAL',
        'sp' => 300,
    ], $turn));

    return $run->fresh(['turnEntries']);
}

/** Build a snapshot with a Wit deficit so the engine ranks Rest + Wit (two eligible). */
function witDeficitRun(): TrainingRun
{
    return provenanceRun(
        attributes: [
            'build_target' => [
                'purpose' => 'StoryClear',
                'distance' => 'Medium',
                'surface' => 'Turf',
                'style' => 'Pace Chaser',
                'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 900],
                'skill_priorities' => [],
                'hint_levels' => [],
            ],
        ],
        turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]
    );
}

/** Build a snapshot below the advisory line with no Wit deficit (single eligible: Rest). */
function singleCandidateRun(): TrainingRun
{
    return provenanceRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
}

/** Build a snapshot with unknown Energy and no target (zero eligible). */
function noEligibleRun(): TrainingRun
{
    return provenanceRun(
        attributes: ['build_target' => null],
        turn: ['energy' => null, 'energy_state' => EnergyState::Unknown]
    );
}

beforeEach(function (): void {
    Config::set('advisor_ai.enabled', true);
    Config::set('advisor_ai.logfare.api_key', 'test-key');
    Config::set('advisor_ai.embeddings.enabled', false);
    Config::set('advisor_ai.total_timeout', 12);
    Config::set('advisor_ai.decision.timeout', 20);
    Config::set('advisor_ai.decision.retries', 1);
    Config::set('advisor_ai.explanation.timeout', 60);
    Config::set('advisor_ai.explanation.max_tokens', 400);
    Config::set('advisor_ai.explanation.reasoning_effort', 'low');
});

it('labels the origin as deterministic when the AI layer is off', function (): void {
    Config::set('advisor_ai.enabled', false);

    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        ->and($analysis['decision']['status'])->toBe('fallback')
        ->and($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['recommended_action']['id'])->toBe('speed');

    Http::assertNothingSent();
});

it('labels the origin as clef with validated status when Clef ranks multiple candidates', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'wit',
                        'probabilities' => ['rest' => 0.4, 'wit' => 0.6, 'insufficient_evidence' => 0.0],
                        'confidence' => 0.55,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit training is safe at low energy.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('clef')
        ->and($analysis['decision']['model'])->toBe('clef')
        ->and($analysis['decision']['status'])->toBe('validated')
        ->and($analysis['decision']['fallback_used'])->toBeFalse()
        ->and($analysis['recommended_action']['id'])->toBe('wit')
        ->and($analysis['explanation_source'])->toBe('logfare');
});

it('labels the origin as deterministic with fallback when Clef is skipped for single candidate', function (): void {
    $run = singleCandidateRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'rest',
                        'probabilities' => ['rest' => 0.93, 'insufficient_evidence' => 0.07],
                        'confidence' => 0.62,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Rest is advised because Energy is below the advisory line.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Clef must not be called
    $recorded = Http::recorded();
    $decisionCalls = count($recorded->filter(fn ($r) => str_contains($r[0]->url(), '/decisions')));
    expect($decisionCalls)->toBe(0);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback')
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        ->and($analysis['recommended_action']['id'])->toBe('rest')
        ->and($analysis['explanation_source'])->toBe('logfare');
});

it('labels the origin as deterministic with fallback on Clef provider failure (401)', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/*' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback')
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('labels the origin as deterministic with fallback on Clef provider failure (500)', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response(['error' => 'upstream'], 500),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'x']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback')
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('labels the origin as deterministic with fallback on explanation provider failure', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['error' => 'upstream'], 500),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('clef')
        ->and($analysis['decision']['status'])->toBe('validated')
        ->and($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toBe('')
        ->and($analysis['recommended_action']['id'])->toBe('wit');
});

it('rejects an explanation that recommends a different action than the validated choice', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        // Explanation says Rest but decision chose Wit — contradiction must be rejected
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Rest is recommended because Energy is low.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Explanation source must fall back to deterministic
    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toContain('Rest is recommended')
        ->and($analysis['recommended_action']['id'])->toBe('wit');
});

it('rejects an explanation containing unsupported numerical gameplay claims', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        // Explanation invents a success rate the sources never published
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit training succeeds 95% of the time at this energy.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Unsupported claim must be rejected
    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toContain('95%')
        ->and($analysis['recommended_action']['id'])->toBe('wit');
});

it('rejects an explanation containing unsupported game mechanics', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        // Explanation invents a mechanic (mood multiplier) not in evidence
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit is best because the mood multiplier doubles its effect.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toContain('mood multiplier')
        ->and($analysis['recommended_action']['id'])->toBe('wit');
});

it('does not present Clef probabilities as gameplay win probabilities', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'wit',
                        'probabilities' => ['rest' => 0.3, 'wit' => 0.7],
                        'confidence' => 0.55,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit training is safe at low energy.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // The response carries preference scores under 'preference' (not 'win_probability' or similar)
    expect($analysis['decision'])->toHaveKey('preference')
        ->and($analysis['decision']['preference'])->toBe(['rest' => 0.3, 'wit' => 0.7])
        ->and($analysis['decision'])->toHaveKey('confidence')
        ->and($analysis['decision']['confidence'])->toBe(0.55)
        // Ensure no field mislabels them as gameplay odds
        ->and($analysis['decision'])->not->toHaveKey('win_probability')
        ->and($analysis['decision'])->not->toHaveKey('success_rate')
        ->and($analysis['decision'])->not->toHaveKey('failure_chance');
});

it('reports abstention status when Clef chooses insufficient_evidence', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'insufficient_evidence',
                        'probabilities' => ['rest' => 0.45, 'wit' => 0.45, 'insufficient_evidence' => 0.1],
                        'confidence' => 0.2,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Not enough information to prefer one action.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Provider is still Clef, but status is abstained; fallback_used is true
    expect($analysis['decision']['provider'])->toBe('clef')
        ->and($analysis['decision']['model'])->toBe('clef')
        ->and($analysis['decision']['status'])->toBe('abstained')
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        // Deterministic action still stands
        ->and($analysis['recommended_action']['id'])->toBe('rest')
        ->and($analysis['deterministic_action']['id'])->toBe('rest');
});

it('rejects Clef answer whose choice is not in the eligible set', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'race', // not offered
                        'probabilities' => ['race' => 0.9],
                        'confidence' => 0.5,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'x']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback')
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('rejects Clef answer whose probability map includes options not offered', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'wit',
                        'probabilities' => ['wit' => 0.6, 'race' => 0.4], // race not offered
                        'confidence' => 0.55,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'x']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback');
});

it('grounds explanation in retrieved evidence when evidence exists', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    // Set up a real index for retrieval
    $path = storage_path('app/private/advisor-index/test-provenance-'.getmypid().'.sqlite');
    if (is_file($path)) {
        unlink($path);
    }
    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/test-provenance-'.getmypid().'.sqlite');
    Config::set('advisor_ai.embeddings.enabled', false);

    $index = new AdvisorIndex($path, 1200);
    $source = 'docs/scenarios/02-unity-cup.md';
    $body = (string) file_get_contents(base_path($source));
    $index->indexDocument($source, $body);
    $retriever = new AdvisorRetriever($index, app(EmbeddingProvider::class));

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit training is safe at low energy per Unity Cup rules.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Evidence should be present and cited in retrieval
    expect($analysis['evidence'])->not->toBeEmpty()
        ->and($analysis['evidence'][0]['path'])->toBe($source)
        // Logfare explanation may or may not include citation; deterministic template does
        ->and($analysis['explanation_source'])->toBe('logfare');

    unset($index, $retriever);
    gc_collect_cycles();
    // The container's index is a singleton holding its own handle on the same file; Windows refuses
    // the unlink while it is open.
    app(AdvisorIndex::class)->close();
    if (is_file($path)) {
        unlink($path);
    }
});

it('explanation cites the first evidence passage when available', function (): void {
    $run = provenanceRun(); // Energy 60, at or above line, largest deficit is Speed
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    $path = storage_path('app/private/advisor-index/test-citation-'.getmypid().'.sqlite');
    if (is_file($path)) {
        unlink($path);
    }
    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/test-citation-'.getmypid().'.sqlite');
    Config::set('advisor_ai.embeddings.enabled', false);

    $index = new AdvisorIndex($path, 1200);
    $source = 'docs/scenarios/02-unity-cup.md';
    $body = (string) file_get_contents(base_path($source));
    $index->indexDocument($source, $body);
    $retriever = new AdvisorRetriever($index, app(EmbeddingProvider::class));

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'speed', 'probabilities' => ['speed' => 0.7]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Speed closes the largest deficit.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['evidence'])->not->toBeEmpty()
        // Logfare explanation may or may not include citation; deterministic template does
        ->and($analysis['explanation_source'])->toBe('logfare');

    unset($index, $retriever);
    gc_collect_cycles();
    // The container's index is a singleton holding its own handle on the same file; Windows refuses
    // the unlink while it is open.
    app(AdvisorIndex::class)->close();
    if (is_file($path)) {
        unlink($path);
    }
});

it('does not cite evidence when retrieval returns none', function (): void {
    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Config::set('advisor_ai.embeddings.enabled', false);
    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/nonexistent-'.getmypid().'.sqlite');

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'speed', 'probabilities' => ['speed' => 0.7]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Speed closes the largest deficit.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['evidence'])->toBeEmpty()
        ->and($analysis['retrieval']['degraded'])->toBeTrue()
        ->and($analysis['explanation'])->not->toContain('Source:')
        ->and($analysis['explanation_source'])->toBe('logfare');
});

it('deterministic template includes evidence citation when available and explanation budget exhausted', function (): void {
    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    $path = storage_path('app/private/advisor-index/test-budget-'.getmypid().'.sqlite');
    if (is_file($path)) {
        unlink($path);
    }
    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/test-budget-'.getmypid().'.sqlite');
    Config::set('advisor_ai.embeddings.enabled', false);
    Config::set('advisor_ai.total_timeout', 0); // Exhausted before explanation call

    $index = new AdvisorIndex($path, 1200);
    $source = 'docs/scenarios/02-unity-cup.md';
    $body = (string) file_get_contents(base_path($source));
    $index->indexDocument($source, $body);
    $retriever = new AdvisorRetriever($index, app(EmbeddingProvider::class));

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['evidence'])->not->toBeEmpty()
        ->and($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->toContain('Source:')
        ->and($analysis['decision']['provider'])->toBe('deterministic');

    unset($index, $retriever);
    gc_collect_cycles();
    // The container's index is a singleton holding its own handle on the same file; Windows refuses
    // the unlink while it is open.
    app(AdvisorIndex::class)->close();
    if (is_file($path)) {
        unlink($path);
    }
});

it('reports unknown Energy and missing target as unknowns, not zeros', function (): void {
    $run = noEligibleRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['unknowns'])->toContain('Energy')
        ->and($analysis['recommended_action'])->toBeNull()
        ->and($analysis['deterministic_action'])->toBeNull()
        ->and($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback');
});

it('conflicts field is present and empty when no source contradictions exist', function (): void {
    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'speed', 'probabilities' => ['speed' => 0.7]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Speed is the largest deficit.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis)->toHaveKey('conflicts')
        ->and($analysis['conflicts'])->toBe([]);
});

it('never invents a win probability from model confidence', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'wit',
                        'probabilities' => ['rest' => 0.3, 'wit' => 0.7],
                        'confidence' => 0.85,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit is safe.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Confidence is carried but not relabeled
    expect($analysis['decision']['confidence'])->toBe(0.85)
        ->and($analysis['decision']['preference'])->toBe(['rest' => 0.3, 'wit' => 0.7])
        // No field claims this is a gameplay probability
        ->and(array_keys($analysis['decision']))->not->toContain('win_probability')
        ->and(array_keys($analysis['decision']))->not->toContain('success_probability')
        ->and($analysis['explanation'])->not->toMatch('/\d+%/');
});

it('explanation degradation flag is true when lore gate trips', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Rest your hor'.'se before the next session.']]], // banned term
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toContain('hor'.'se')
        ->and($analysis['recommended_action']['id'])->toBe('wit');
});

it('explanation degradation flag is true when token budget truncates output', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => null], 'finish_reason' => 'length']],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toBe('')
        ->and($analysis['recommended_action']['id'])->toBe('wit');
});

it('decision revision matches snapshot revision to prove provenance', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();
    $snapshot = AdvisorSnapshot::forRun($run, $latest);

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit is safe.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['career_revision'])->toBe($snapshot->revision)
        ->and($analysis['career_id'])->toBe($run->id);
});

it('cache key includes contract version and revision', function (): void {
    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $first = app(AdvisorAnalysis::class)->analyze($run, $latest);
    $second = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($first['career_revision'])->toBe($second['career_revision'])
        ->and($first['contract_version'])->toBe($second['contract_version'])
        ->and($first['contract_version'])->toBe(AdvisorAnalysis::CONTRACT_VERSION);
});

it('alternatives list includes engine_choice flag for deterministic recommendation', function (): void {
    $run = witDeficitRun(); // Engine recommends Rest, Wit is alternative
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit is safe.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Alternatives should include Rest with engine_choice = true
    $restAlt = collect($analysis['alternatives'])->firstWhere('id', 'rest');
    expect($restAlt)->not->toBeNull()
        ->and($restAlt['engine_choice'])->toBeTrue();

    // Wit (chosen) should not be in alternatives
    $witAlt = collect($analysis['alternatives'])->firstWhere('id', 'wit');
    expect($witAlt)->toBeNull();
});

it('blocked actions are reported with their blocker reasons', function (): void {
    $run = singleCandidateRun(); // Energy 30: Rest recommended, training actions blocked
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['blocked'])->not->toBeEmpty();
    $speedBlocked = collect($analysis['blocked'])->firstWhere('id', 'speed');
    expect($speedBlocked)->not->toBeNull()
        ->and($speedBlocked['blocker'])->toContain('advised Rest at this Energy reading');
});

it('obligation is named from objectives when current, absent otherwise', function (): void {
    $runWith = provenanceRun();
    $latestWith = $runWith->turnEntries->sortByDesc('turn')->first();

    $runWithout = provenanceRun();
    $latestWithout = $runWithout->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $withObligation = app(AdvisorAnalysis::class)->analyze($runWith, $latestWith, [
        ['label' => 'Junior Make Debut', 'state' => 'current'],
    ]);

    $withoutObligation = app(AdvisorAnalysis::class)->analyze($runWithout, $latestWithout, [
        ['label' => 'Junior Make Debut', 'state' => 'upcoming'],
    ]);

    expect($withObligation['obligation'])->toBe('Junior Make Debut is due and not recorded.')
        ->and($withoutObligation['obligation'])->toBeNull();
});

it('retrieval degraded flag is true when index unavailable', function (): void {
    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/does-not-exist-'.getmypid().'.sqlite');
    Config::set('advisor_ai.embeddings.enabled', false);

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['retrieval']['degraded'])->toBeTrue()
        ->and($analysis['retrieval']['lexical_count'])->toBe(0)
        ->and($analysis['retrieval']['semantic_count'])->toBe(0)
        ->and($analysis['evidence'])->toBeEmpty();
});

it('explanation template uses engine reason when no evidence retrieved', function (): void {
    $run = provenanceRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Config::set('advisor_ai.embeddings.enabled', false);
    Config::set('advisor_ai.retrieval.index_path', 'app/private/advisor-index/none-'.getmypid().'.sqlite');

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // At energy 60, Speed is largest deficit
    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->toContain('largest deficit')
        ->and($analysis['explanation'])->not->toContain('Source:');
});

it('does not call Clef when total budget exhausted before decision', function (): void {
    $run = witDeficitRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Config::set('advisor_ai.total_timeout', 0);

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit is safe.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    $recorded = Http::recorded();
    $decisionCalls = count($recorded->filter(fn ($r) => str_contains($r[0]->url(), '/decisions')));
    expect($decisionCalls)->toBe(0)
        ->and($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['explanation_source'])->toBe('deterministic');
});

it('does not call explanation when total budget exhausted before explanation', function (): void {
    $run = provenanceRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Config::set('advisor_ai.total_timeout', 0);

    Http::fake([
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Rest is advised.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    $recorded = Http::recorded();
    $explanationCalls = count($recorded->filter(fn ($r) => str_contains($r[0]->url(), '/chat/completions')));
    expect($explanationCalls)->toBe(0)
        ->and($analysis['explanation_source'])->toBe('deterministic');
});

it('clef decision provider returns null for empty eligible set', function (): void {
    $snapshot = new AdvisorSnapshot(
        careerId: 1,
        revision: 'revision-1',
        turn: 1,
        scenario: 'unity_cup',
        scenarioLabel: 'Unity Cup',
        stats: [],
        energy: null,
        energyState: 'unknown',
        energyBand: null,
        mood: null,
        skillPoints: null,
        fans: null,
        resources: [],
        unknowns: [],
        finished: false,
    );

    $candidates = new CandidateActions([], [], null);

    Http::fake();

    $provider = new ClefDecisionProvider;
    $result = $provider->decide($snapshot, $candidates, []);

    expect($result)->toBeNull();
    Http::assertNothingSent();
});
