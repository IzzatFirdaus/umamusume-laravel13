<?php

declare(strict_types=1);

use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Fixtures\TestSourceParser;

/*
 * S1.1: a race is not a trainee, and the review queue must not pretend otherwise.
 *
 * `PipelineRunner` had no `RaceEnrichmentSourceParser` branch, so the four enrichment sources fell
 * through the generic character loop. Two of them carry no name key and threw; the other two carry
 * `name_en`, so each record became a Pending candidate indistinguishable from a trainee candidate.
 * Confirming one called `PromoteMatchedRecord`, which builds an `App\Models\Umamusume` from
 * `$record['name']` - a key race payloads do not have. So the queue offered a button that 500'd, and
 * a payload carrying both keys would have written a RACE INTO THE TRAINEE TABLE.
 *
 * The entity now travels in the payload, written from the source's own config, and the queue refuses a
 * non-Reject verdict for anything that is not a trainee.
 */

const ENTITY_FETCH_URL = 'https://source.test/races';

function declareRaceSource(): void
{
    config([
        'uma.sources.test-races' => [
            'url' => ENTITY_FETCH_URL,
            'parser' => TestSourceParser::class,
            'entity' => 'race',
            'delay_ms' => 0,
            'timeout_s' => 5,
            'timezone' => 'Asia/Tokyo',
        ],
    ]);
}

beforeEach(function (): void {
    Storage::fake('local');
    declareRaceSource();
});

it('writes the entity and a display name into the payload at queue time', function (): void {
    Http::preventStrayRequests();
    Http::fake([ENTITY_FETCH_URL => Http::response(json_encode([['name' => 'February Stakes']]))]);

    $this->artisan('uma:fetch', ['source' => 'test-races'])->assertExitCode(0);

    $candidate = MatchCandidate::query()->sole();

    // Both facts are resolved once, at write time, so no reader has to rediscover which key the
    // record used for its name. `payload['name']` is exactly what a race record does NOT carry, which
    // is why the resolver used to throw.
    expect($candidate->payload['entity'])->toBe('race')
        ->and($candidate->payload['display_name'])->toBe('February Stakes')
        ->and($candidate->proposed_name)->toBe('February Stakes');
});

it('refuses to confirm or alias a race, and says which command does promote one', function (): void {
    $candidate = MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'proposed_name' => 'February Stakes',
        'payload' => ['name_en' => 'February Stakes', 'entity' => 'race'],
        'suggested_umamusume_id' => null,
    ]);

    $trainessBefore = Umamusume::query()->count();

    foreach ([CandidateStatus::Confirmed, CandidateStatus::Aliased] as $status) {
        test()->withHeader('referer', url('/review'))
            ->followingRedirects()
            ->post("/review/{$candidate->id}", ['status' => $status->value, 'alias_language' => 'English'])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('errors.status'));
    }

    // Nothing was promoted and nothing was written into the trainee table.
    expect(Umamusume::query()->count())->toBe($trainessBefore)
        ->and($candidate->fresh()->status)->toBe(CandidateStatus::Pending);
});

it('rejects a race, which is the one verdict it can carry', function (): void {
    $candidate = MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'proposed_name' => 'February Stakes',
        'payload' => ['name_en' => 'February Stakes', 'entity' => 'race'],
    ]);

    test()->post("/review/{$candidate->id}", ['status' => CandidateStatus::Rejected->value])
        ->assertRedirect();

    expect($candidate->fresh()->status)->toBe(CandidateStatus::Rejected);
});

it('still confirms a trainee candidate through the trainee path', function (): void {
    // The refusal above must be about the entity and nothing else: a normal candidate still promotes.
    $umamusume = Umamusume::factory()->create(['name' => 'Rice Shower']);
    $candidate = MatchCandidate::factory()->create([
        'status' => CandidateStatus::Pending,
        'proposed_name' => 'Rice Shower',
        'payload' => ['name' => 'Rice Shower', 'entity' => 'umamusume', 'url' => 'https://example.test/rice'],
        'suggested_umamusume_id' => $umamusume->id,
    ]);

    test()->post("/review/{$candidate->id}", [
        'status' => CandidateStatus::Confirmed->value,
        'umamusume_id' => $umamusume->id,
    ])->assertRedirect();

    expect($candidate->fresh()->status)->toBe(CandidateStatus::Confirmed);
});

it('declares an entity for every source that has a parser', function (): void {
    /** @var array<string, array<string, mixed>> $sources */
    $sources = config('uma.sources');

    // Non-vacuity for the config side: the four enrichment entries must name `race`, and the roster
    // entries must not. A missing key defaults to `umamusume` at the read site, so an unstated race
    // source would silently become promotable as a trainee.
    foreach (['gametora-racetracks', 'gametora-race-fans', 'gametora-races', 'gametora-race-rewards'] as $key) {
        expect($sources[$key]['entity'] ?? null)->toBe('race', "{$key} must declare entity=race");
    }

    foreach (['gametora-characters', 'gametora-character-cards', 'gametora-skills'] as $key) {
        expect($sources[$key]['entity'] ?? 'umamusume')->toBe('umamusume', "{$key} must not be a race");
    }
});
