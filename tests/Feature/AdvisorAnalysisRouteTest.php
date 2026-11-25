<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\AdvisorAnalysis;
use Illuminate\Support\Facades\Http;

/*
 * The Advisor analysis route (Advisor AI slice).
 *
 * The cockpit's own `advisor` prop is pinned to exactly `decision` and `finale` by
 * `CareerCockpitTest:166`, so the AI layer lives on its own read route. These cases pin the three
 * things that matter about it: it is off by default, it never widens the frozen cockpit payload, and
 * a closed switch answers 404 rather than a half-shaped payload.
 */

it('returns 404 when the AI layer is switched off, which is the default', function (): void {
    config(['advisor_ai.enabled' => false]);

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);

    $this->get(route('runs.advisor-analysis', $run))->assertNotFound();
});

it('answers the analysis contract as JSON when the layer is on', function (): void {
    config(['advisor_ai.enabled' => true, 'advisor_ai.embeddings.enabled' => false]);

    Http::fake();

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    $response = $this->get(route('runs.advisor-analysis', $run));

    $response->assertOk()
        ->assertJsonStructure([
            'contract_version',
            'career_id',
            'career_revision',
            'turn',
            'recommended_action',
            'decision' => ['provider', 'status', 'fallback_used'],
            'explanation',
            'evidence',
            'unknowns',
            'alternatives',
            'generated_at',
        ]);

    expect($response->json('decision.provider'))->toBe('deterministic')
        ->and($response->json('contract_version'))->toBeString()
        // This run has no build target, so the engine declines to rank. The AI layer must not fill
        // that gap with a guess: `recommended_action` stays null and the absence is stated.
        ->and($response->json('recommended_action'))->toBeNull();
});

it('answers 503 with a stable code when the analysis throws, without leaking the failure', function (): void {
    config(['advisor_ai.enabled' => true, 'advisor_ai.embeddings.enabled' => false]);

    Http::fake();

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    // The pipeline is final, so the failure is forced on a partial mock of the real,
    // container-built instance: the endpoint resolves this one object and nothing else
    // about the request changes.
    $pipeline = Mockery::mock(app(AdvisorAnalysis::class));
    $pipeline->shouldReceive('analyze')
        ->andThrow(new RuntimeException('advisor pipeline boom: token abc-123'));

    $this->instance(AdvisorAnalysis::class, $pipeline);

    $before = $run->fresh(['turnEntries']);

    $response = $this->get(route('runs.advisor-analysis', $run));

    $response->assertStatus(503)
        ->assertHeader('Content-Type', 'application/json')
        ->assertJson([
            'error' => [
                'code' => 'advisor_analysis_unavailable',
                'message' => 'The analysis could not be produced. The recommendation on the cockpit is unaffected.',
            ],
        ]);

    // The response names the failure category, not the failure: the exception's own words
    // (with a token in this test, on purpose) must not reach the client.
    $body = (string) $response->getContent();
    expect($body)->not->toContain('advisor pipeline boom')->not->toContain('abc-123');

    // A failed analysis touches nothing: the career it analysed is value-identical.
    $after = $run->fresh(['turnEntries']);
    expect($after->toArray())->toEqual($before->toArray())
        ->and($after->turnEntries->toArray())->toEqual($before->turnEntries->toArray());
});

it('leaves the cockpit advisor prop exactly as it was', function (): void {
    config(['advisor_ai.enabled' => true, 'advisor_ai.embeddings.enabled' => false]);

    Http::fake();

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('advisor')
            ->has('advisor.decision')
            ->has('advisor.finale')
            // The AI layer must not appear here: this prop is a recorded contract.
            ->missing('advisor.analysis'));
});

it('exposes the career revision as a sibling prop, matching the one the analysis carries', function (): void {
    config(['advisor_ai.enabled' => true, 'advisor_ai.embeddings.enabled' => false]);

    Http::fake();

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    $analysis = $this->get(route('runs.advisor-analysis', $run))->assertOk()->json();

    // The page's number and the response's number are the same number: both come from
    // `AdvisorSnapshot::revision()`, which is the point of exposing it at all.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('advisor')
            ->missing('advisor.analysis')
            ->where('advisor_revision', $analysis['career_revision'])
            // Still exactly the two keys this prop has always had.
            ->has('advisor', fn ($advisor) => $advisor->hasAll(['decision', 'finale'])->count(2)));
});

it('moves the cockpit revision when a decision-relevant same-turn edit lands', function (): void {
    config(['advisor_ai.enabled' => true, 'advisor_ai.embeddings.enabled' => false]);

    Http::fake();

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    $turn = TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    $readRevision = function () use ($run): string {
        $value = null;
        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(function ($page) use (&$value): void {
                $value = $page->toArray()['props']['advisor_revision'];
            });

        return (string) $value;
    };

    $before = $readRevision();

    // Same turn, edited reading: the case a turn-number comparison cannot catch.
    $turn->update(['energy' => 20]);
    $after = $readRevision();

    expect($after)->not->toBe($before)
        ->and($run->fresh()->nextTurnNumber())->toBe(2);
});

it('carries the cockpit revision whether or not the AI layer is on', function (): void {
    // The revision describes the career; the switch describes the provider. Gating the prop on the
    // switch would give the page a second rule for when the number means anything.
    config(['advisor_ai.enabled' => false]);

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    $off = null;
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function ($page) use (&$off): void {
            $off = $page->toArray()['props']['advisor_revision'];
        });

    config(['advisor_ai.enabled' => true]);

    $on = null;
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(function ($page) use (&$on): void {
            $on = $page->toArray()['props']['advisor_revision'];
        });

    expect($off)->toBeString()->not->toBe('')
        ->and($on)->toBe($off);
});
