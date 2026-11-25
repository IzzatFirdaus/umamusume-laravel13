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
use App\Services\Advisor\TrainerAdvisor;
use Illuminate\Support\Facades\Http;

/*
 * The optional AI Advisor layer (Advisor AI slice).
 *
 * What this file pins, and why each case exists:
 *
 * - **The deterministic rail is untouched.** `TrainerAdvisorTest` (12) and `NextDecisionTest` (19)
 *   stand unchanged; these cases add behaviour rather than replace it.
 * - **A missing fact stays missing.** An unknown Energy reading must not arrive at a provider as
 *   zero, and an unrecorded stat must not arrive as a number. This is the failure the whole slice
 *   is built to avoid (`AGENTS.md` §5).
 * - **A provider cannot widen the choice set.** Every rejection path is exercised with a fake, so the
 *   guarantee does not depend on Logfare being reachable or the account being Premium.
 * - **No inference writes a career row.** Asserted explicitly rather than assumed.
 *
 * No test here reaches the network. `Http::fake` covers every provider call, and the index is built
 * into a per-test scratch file rather than the shared dev database.
 */

/**
 * A run with one recorded turn, for the cases that need a real model.
 */
function aiRun(array $attributes = [], array $turn = []): TrainingRun
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

it('reads a snapshot with a stable revision that changes when a turn is logged', function (): void {
    $run = aiRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    $first = AdvisorSnapshot::forRun($run, $latest);
    $second = AdvisorSnapshot::forRun($run, $latest);

    expect($first->revision)->toBe($second->revision)
        ->and($first->energy)->toBe(60)
        ->and($first->energyState)->toBe('exact')
        ->and($first->stats['Speed'])->toBe(500);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 2,
        'speed' => 520,
        'energy' => 55,
        'energy_state' => EnergyState::Exact,
        'mood' => 'NORMAL',
    ]);

    $moved = AdvisorSnapshot::forRun($run->fresh(['turnEntries']), $run->fresh(['turnEntries'])->turnEntries->sortByDesc('turn')->first());

    expect($moved->revision)->not->toBe($first->revision);
});

it('keeps an unrecorded Energy reading unknown rather than zero', function (): void {
    $run = aiRun(turn: ['energy' => null, 'energy_state' => EnergyState::Unknown]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    $snapshot = AdvisorSnapshot::forRun($run, $latest);

    expect($snapshot->energy)->toBeNull()
        ->and($snapshot->energyState)->toBe('unknown')
        ->and($snapshot->unknowns)->toContain('Energy')
        // The provider payload is the thing that actually leaves the machine, so the absence is
        // asserted there too and not only on the object.
        ->and($snapshot->forProvider()['energy'])->toBeNull();
});

it('keeps a band reading as a band rather than converting it to a figure', function (): void {
    $run = aiRun(turn: ['energy' => null, 'energy_state' => EnergyState::Band, 'energy_band' => 'low']);
    $snapshot = AdvisorSnapshot::forRun($run, $run->turnEntries->sortByDesc('turn')->first());

    expect($snapshot->energy)->toBeNull()
        ->and($snapshot->energyState)->toBe('band')
        ->and($snapshot->energyBand)->toBe('low');
});

it('offers only the options the engine ranked, and names the ones it withheld', function (): void {
    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();
    $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget(), $run);

    $candidates = CandidateActions::from($advice, $run, $latest, []);

    // Below the advisory line the engine recommends Rest and names Wit as the alternative when Wit
    // has a deficit to close. Those two are the decision; the four training actions it also derived
    // are withheld, because offering them would let a provider pick what the engine ruled against.
    expect($advice->recommendation?->action)->toBe('Rest')
        ->and($candidates->eligible)->toHaveCount($advice->alternative === null ? 1 : 2)
        ->and($candidates->allows('rest'))->toBeTrue()
        ->and($candidates->allows(CandidateActions::ABSTAIN))->toBeTrue()
        ->and($candidates->allows('speed'))->toBeFalse()
        ->and($candidates->allows('power'))->toBeFalse();

    // Nothing is silently dropped: every engine option is either selectable or explained as blocked.
    expect(count($candidates->eligible) + count($candidates->blocked))->toBe(count($advice->options));
});

it('offers every derivable option when the engine declined to rank', function (): void {
    // No build target, Energy recorded: the engine derives a session-cost reason for each training
    // action but has no target to rank them against. That is exactly the question it declines, so
    // the provider is offered all of them and `insufficient_evidence` remains available.
    $run = aiRun(['build_target' => null], ['energy' => 70, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();
    $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget(), $run);

    $candidates = CandidateActions::from($advice, $run, $latest, []);

    expect($advice->recommendation)->toBeNull()
        ->and($candidates->eligible)->not->toBe([])
        ->and(array_column($candidates->eligible, 'id'))->toBe(array_map(
            static fn ($o): string => CandidateActions::id($o->action),
            $advice->options,
        ))
        ->and($candidates->allows(CandidateActions::ABSTAIN))->toBeTrue();
});

it('refuses an id the engine never offered', function (): void {
    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();
    $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget(), $run);

    $candidates = CandidateActions::from($advice, $run, $latest, []);

    // A well-formed id for an action outside the eligible set must still be refused: the check is
    // membership, not shape.
    expect($candidates->allows('race'))->toBeFalse()
        ->and($candidates->allows('teleport'))->toBeFalse();
});

it('withholds a training action the engine ruled against when Energy is below the line', function (): void {
    // The exact case live testing exposed: Energy 42, Rest recommended, and every stat still had a
    // deficit reason the engine emitted. Offering those to a provider let it choose Speed over Rest.
    $run = aiRun(turn: ['energy' => 42, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();
    $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget(), $run);

    $candidates = CandidateActions::from($advice, $run, $latest, []);

    expect($advice->band)->toBe(TrainerAdvisor::BELOW)
        ->and($advice->recommendation?->action)->toBe('Rest')
        // Speed is derivable and is NOT offered. This is the assertion that would have failed before
        // the live run corrected the rule.
        ->and($candidates->allows('speed'))->toBeFalse()
        ->and($candidates->allows('rest'))->toBeTrue();

    $blockedIds = array_column($candidates->blocked, 'id');

    expect($blockedIds)->toContain('speed')
        ->and($blockedIds)->toContain('stamina');
});

it('names a due obligation without inventing one', function (): void {
    $run = aiRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();
    $advice = app(TrainerAdvisor::class)->advise($latest, $run->buildTarget(), $run);

    $withObligation = CandidateActions::from($advice, $run, $latest, [
        ['label' => 'Junior Make Debut', 'state' => 'current'],
    ]);

    $without = CandidateActions::from($advice, $run, $latest, [
        ['label' => 'Junior Make Debut', 'state' => 'upcoming'],
    ]);

    expect($withObligation->obligation)->toBe('Junior Make Debut is due and not recorded.')
        ->and($without->obligation)->toBeNull();
});

it('retrieves evidence with real citations and never a fabricated one', function (): void {
    $path = storage_path('app/private/advisor-index/test-'.getmypid().'.sqlite');

    if (is_file($path)) {
        unlink($path);
    }

    config(['advisor_ai.retrieval.index_path' => 'app/private/advisor-index/test-'.getmypid().'.sqlite']);
    config(['advisor_ai.embeddings.enabled' => false]);

    $index = new AdvisorIndex($path, 1200);
    $source = 'docs/scenarios/02-unity-cup.md';
    $body = (string) file_get_contents(base_path($source));

    expect($index->indexDocument($source, $body))->toBeTrue();

    $retriever = new AdvisorRetriever($index, app(EmbeddingProvider::class));
    $result = $retriever->retrieve('Spirit Burst', 3);

    expect($result['evidence'])->not->toBeEmpty()
        // Every citation names the file it was read from, and that file exists on disk.
        ->and($result['evidence'][0]->path)->toBe($source)
        ->and(base_path($result['evidence'][0]->path))->toBeFile()
        ->and($result['evidence'][0]->heading)->not->toBe('')
        // Semantic retrieval was disabled, so the report says so rather than implying full coverage.
        ->and($result['degraded'])->toBeTrue();

    // The index holds an open PDO handle, so Windows refuses the unlink until it is closed. The
    // scratch file is per-pid, so a leaked handle cannot collide with a later run; unsetting the
    // reference is enough to let the handle go.
    unset($index, $retriever);
    gc_collect_cycles();
});

it('reports an unchanged document as unchanged rather than reindexing it', function (): void {
    $path = storage_path('app/private/advisor-index/incremental-'.getmypid().'.sqlite');

    if (is_file($path)) {
        unlink($path);
    }

    $index = new AdvisorIndex($path, 1200);
    $source = 'docs/scenarios/01-ura-finale.md';
    $body = (string) file_get_contents(base_path($source));

    expect($index->indexDocument($source, $body))->toBeTrue()
        ->and($index->indexDocument($source, $body))->toBeFalse()
        // A changed body is a new hash and therefore a rebuild.
        ->and($index->indexDocument($source, $body."\n\nappended"))->toBeTrue();

    unset($index);
    gc_collect_cycles();
});

it('returns the deterministic recommendation when the AI layer is switched off', function (): void {
    config(['advisor_ai.enabled' => false]);

    $run = aiRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        ->and($analysis['contract_version'])->toBe(AdvisorAnalysis::CONTRACT_VERSION)
        // The engine's own choice is still named, so a surface has something to show.
        ->and($analysis['deterministic_action'])->not->toBeNull();

    Http::assertNothingSent();
});

it('skips Clef when exactly one ranked action exists', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
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

    // Clef must not be asked when the engine has already established the only permissible choice.
    $recorded = Http::recorded();
    $decisionCalls = count($recorded->filter(fn ($r) => str_contains($r[0]->url(), '/decisions')));
    expect($decisionCalls)->toBe(0);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['decision']['status'])->toBe('fallback')
        ->and($analysis['recommended_action']['id'])->toBe('rest')
        // Explanation is a separate call and may still run.
        ->and($analysis['explanation_source'])->toBe('logfare');
});

it('calls Clef when multiple ranked actions exist', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    // Energy below the advisory line with a Wit deficit: Rest is recommended and Wit is the
    // alternative, so Clef is offered a real choice between the two.
    $run = aiRun(
        turn: ['energy' => 30, 'energy_state' => EnergyState::Exact],
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
    );
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'wit',
                        'probabilities' => ['rest' => 0.4, 'wit' => 0.6],
                        'confidence' => 0.55,
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Wit is safe at low energy.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    $recorded = Http::recorded();
    $decisionCalls = count($recorded->filter(fn ($r) => str_contains($r[0]->url(), '/decisions')));
    expect($decisionCalls)->toBe(1);

    expect($analysis['decision']['provider'])->toBe('clef')
        ->and($analysis['decision']['status'])->toBe('validated')
        ->and($analysis['recommended_action']['id'])->toBe('wit')
        ->and($analysis['explanation_source'])->toBe('logfare');
});

it('returns null from Clef with no HTTP call when there are no eligible candidates', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
    ]);

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

it('discards a Clef answer naming an action the engine did not offer', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'race',
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
        ->and($analysis['decision']['fallback_used'])->toBeTrue()
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('discards a Clef answer whose probability map names options that were not offered', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => [
                    'recommendation' => [
                        'type' => 'choice',
                        'choice' => 'rest',
                        'probabilities' => ['rest' => 0.7, 'race' => 0.3],
                    ],
                ],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'x']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // A distribution over an option we never supplied is a malformed answer, not a bonus datapoint.
    expect($analysis['decision']['provider'])->toBe('deterministic');
});

it('falls back deterministically on provider refusals and transport failures', function (int $status, bool $expectNoRetry): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
        'advisor_ai.decision.retries' => 1,
    ]);

    $run = aiRun(
        turn: ['energy' => 30, 'energy_state' => EnergyState::Exact],
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
    );
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/*' => Http::response(['error' => 'nope'], $status),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['recommended_action']['id'])->toBe('rest');

    if ($expectNoRetry) {
        // 401/403/429 are not retried: repeating an unauthorised or rate-limited call is how one
        // refusal becomes an outage. Counted against the decision endpoint alone, because the
        // explanation call is a separate request and is expected.
        $decisionCalls = 0;
        $recorded = Http::recorded();
        foreach ($recorded as $r) {
            if (str_contains($r[0]->url(), '/decisions')) {
                $decisionCalls++;
            }
        }
        expect($decisionCalls)->toBe(1);
    }
})->with([
    'unauthorised' => [401, true],
    'premium gate' => [403, true],
    'rate limited' => [429, true],
]);

it('falls back when the provider returns a malformed envelope', function (mixed $body): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake(['logfare.ai/*' => Http::response($body, 200)]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['recommended_action']['id'])->toBe('rest');
})->with([
    'success false' => [['success' => false]],
    'no result' => [['success' => true]],
    'no answers' => [['success' => true, 'result' => []]],
    'wrong question type' => [['success' => true, 'result' => ['answers' => ['recommendation' => ['type' => 'noul', 'noul' => 0.9]]]]],
    'missing choice' => [['success' => true, 'result' => ['answers' => ['recommendation' => ['type' => 'choice']]]]],
]);

it('rejects an explanation that would trip the lore gate and keeps the deterministic sentence', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'rest', 'probabilities' => ['rest' => 0.9]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response([
            // The banned term is exactly what a model produced when probed on this machine; the
            // explanation must not reach a Trainer. Assembled rather than written out for the same
            // reason the blocklist is: this file is scanned by the lore gate too.
            'choices' => [['message' => ['content' => 'Rest your hor'.'se before the next session.']]],
        ]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toContain('hor'.'se')
        // The ranked action still stands: only the prose was rejected.
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('falls back to the deterministic template when the explanation is truncated by the token budget', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'rest', 'probabilities' => ['rest' => 0.9]]],
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
        // The ranked action still stands: only the prose was rejected.
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('falls back to the deterministic template when the explanation provider errors', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'rest', 'probabilities' => ['rest' => 0.9]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['error' => 'upstream'], 500),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['explanation'])->not->toBe('')
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('falls back deterministically when Clef returns a server error', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
        'advisor_ai.decision.retries' => 1,
    ]);

    $run = aiRun(
        turn: ['energy' => 30, 'energy_state' => EnergyState::Exact],
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
    );
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response(['error' => 'upstream'], 500),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'Rest is advised.']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});

it('never writes to the career during an analysis', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'rest', 'probabilities' => ['rest' => 0.9]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'Rest is advised.']]]]),
    ]);

    $turnsBefore = TurnEntry::where('training_run_id', $run->id)->count();
    $runBefore = $run->only(['id', 'scenario', 'status', 'build_target', 'notes']);

    app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect(TurnEntry::where('training_run_id', $run->id)->count())->toBe($turnsBefore)
        ->and($run->fresh()->only(['id', 'scenario', 'status', 'build_target', 'notes']))->toBe($runBefore);
});

it('caches against the revision so a logged turn is never served a stale answer', function (): void {
    config([
        'advisor_ai.enabled' => false,
        'advisor_ai.embeddings.enabled' => false,
    ]);

    $run = aiRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $first = app(AdvisorAnalysis::class)->analyze($run, $latest);

    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 2,
        'speed' => 530,
        'energy' => 40,
        'energy_state' => EnergyState::Exact,
        'mood' => 'NORMAL',
    ]);

    $fresh = $run->fresh(['turnEntries']);
    $second = app(AdvisorAnalysis::class)->analyze($fresh, $fresh->turnEntries->sortByDesc('turn')->first());

    expect($second['career_revision'])->not->toBe($first['career_revision'])
        ->and($second['turn'])->toBe(3);
});

it('refuses to cache a payload whose revision does not match the snapshot', function (): void {
    config(['advisor_ai.enabled' => false, 'advisor_ai.embeddings.enabled' => false]);

    $run = aiRun();
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake();

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);
    $snapshot = AdvisorSnapshot::forRun($run, $latest);

    expect($analysis['career_revision'])->toBe($snapshot->revision)
        ->and($analysis['career_id'])->toBe($run->id);
});

it('skips Clef when the total budget is already exhausted before the decision call', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
        'advisor_ai.total_timeout' => 0,
    ]);

    // Multiple ranked candidates: Rest + Wit (Wit deficit below the advisory line).
    $run = aiRun(
        turn: ['energy' => 30, 'energy_state' => EnergyState::Exact],
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
    );
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/decisions' => Http::response([
            'result' => [
                'model' => 'clef',
                'answers' => ['recommendation' => ['type' => 'choice', 'choice' => 'wit', 'probabilities' => ['wit' => 0.6]]],
            ],
            'success' => true,
        ]),
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'Wit is safe.']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    // Deadline already passed: Clef must not be called at all.
    $recorded = Http::recorded();
    $decisionCalls = count($recorded->filter(fn ($r) => str_contains($r[0]->url(), '/decisions')));
    expect($decisionCalls)->toBe(0)
        ->and($analysis['decision']['provider'])->toBe('deterministic')
        ->and($analysis['recommended_action']['id'])->toBe('rest')
        // Explanation also skipped: budget exhausted.
        ->and($analysis['explanation_source'])->toBe('deterministic');
});

it('uses deterministic explanation when the budget is exhausted before the explanation call', function (): void {
    config([
        'advisor_ai.enabled' => true,
        'advisor_ai.logfare.api_key' => 'test-key',
        'advisor_ai.embeddings.enabled' => false,
        'advisor_ai.total_timeout' => 0,
    ]);

    $run = aiRun(turn: ['energy' => 30, 'energy_state' => EnergyState::Exact]);
    $latest = $run->turnEntries->sortByDesc('turn')->first();

    Http::fake([
        'logfare.ai/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'Rest is advised.']]]]),
    ]);

    $analysis = app(AdvisorAnalysis::class)->analyze($run, $latest);

    expect($analysis['explanation_source'])->toBe('deterministic')
        ->and($analysis['recommended_action']['id'])->toBe('rest');
});
