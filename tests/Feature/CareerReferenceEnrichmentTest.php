<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Models\CharacterCard;
use App\Models\RaceEntry;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\Umamusume;
use App\Models\Veteran;
use App\Services\Career\CareerReference;
use Database\Seeders\UraFinaleRunsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Published reference is useful to every imported career, but it must never become observed
 * history. These cases pin the shared reader, its source-backed config coverage and its exposure on
 * both historical surfaces.
 */

it('has a published objective schedule for every unique tracker card and for URA Finale', function (): void {
    $cardIds = array_values(array_unique(UraFinaleRunsSeeder::CAREER_CARDS));

    // Ten card forms cover the 17 tracker runs; the eleventh configured card is the separate
    // Rice Shower Unity Cup seeder.
    expect($cardIds)->toHaveCount(10)
        ->and(config('imported_career_reference.cards.103001'))->toBeArray();

    foreach ($cardIds as $cardId) {
        $reference = config('imported_career_reference.cards.'.$cardId);

        expect($reference)->toBeArray()
            ->and($reference['source'] ?? null)->toBeString()
            ->and($reference['milestones'] ?? [])->not->toBeEmpty();
    }

    expect(config('imported_career_reference.scenarios.ura_finale.milestones'))->not->toBeEmpty()
        ->and(config('imported_career_reference.mechanics.scenarios.ura_finale.conflicts'))->not->toBeEmpty();
});

it('builds card, skill, objective and mechanics reference without writing observed history', function (): void {
    $trainee = Umamusume::factory()->create([
        'name' => 'Reference Trainee',
        'aptitude_turf' => 'A',
        'aptitude_dirt' => 'B',
    ]);

    $card = CharacterCard::factory()->create([
        'card_id' => 103001,
        'umamusume_id' => $trainee->id,
        'title' => '[Reference Form]',
        'skills_unique' => [100011, 999999],
        'skills_innate' => [200472],
        'skills_awakening' => [],
        'skills_event' => [],
        'skills_evo' => [['new' => 100101111, 'old' => 201351]],
        'base_stats' => ['Speed' => 70, 'Stamina' => 100, 'Power' => 80, 'Guts' => 90, 'Wit' => 60],
        'growth_rates' => ['Speed' => 0, 'Stamina' => 20, 'Power' => 0, 'Guts' => 10, 'Wit' => 10],
    ]);

    Skill::factory()->create([
        'name' => 'Reference Unique',
        'export_id' => 100011,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
        'is_unique' => true,
    ]);

    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'character_card_id' => $card->id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Active,
        'import_source' => 'tracker:reference-test',
    ]);

    $before = [
        'turns' => TurnEntry::query()->count(),
        'events' => TurnEvent::query()->count(),
        'races' => RaceEntry::query()->count(),
    ];

    $reference = CareerReference::forRun($run);

    expect($reference['available'])->toBeTrue()
        ->and($reference['imported'])->toBeTrue()
        ->and($reference['card']['card_id'])->toBe(103001)
        ->and($reference['profile']['aptitudes'][0]['letter'])->toBe('A')
        ->and($reference['profile']['base_stats']['Stamina'])->toBe(100)
        ->and($reference['schedule']['trainee']['milestones'][0][1])->toContain('Flower that Blooms in Yodo')
        ->and($reference['schedule']['scenario']['source'])->toBe('https://gametora.com/umamusume/ura-finals')
        ->and($reference['skills'][0]['skills'][0]['name'])->toBe('Reference Unique')
        ->and($reference['skills'][0]['unresolved'])->toBe(1)
        ->and($reference['coverage']['support_card_events'])->toBeFalse()
        ->and($reference['coverage']['spark_activations'])->toBeFalse()
        ->and($reference['events']['fixed_events'])->toBeTrue();

    $scenarioMechanics = collect($reference['mechanics'])->firstWhere('key', 'scenario');
    expect($scenarioMechanics)->not->toBeNull()
        ->and($scenarioMechanics['ruleset']['date'])->toBe('2026-07-01')
        ->and($scenarioMechanics['conflicts'])->not->toBeEmpty();

    expect(TurnEntry::query()->count())->toBe($before['turns'])
        ->and(TurnEvent::query()->count())->toBe($before['events'])
        ->and(RaceEntry::query()->count())->toBe($before['races']);
});

it('names the absence when a run has no matching card or chosen scenario', function (): void {
    $run = TrainingRun::factory()->create([
        'scenario' => null,
        'import_source' => null,
        'character_card_id' => null,
    ]);

    $reference = CareerReference::forRun($run);

    expect($reference['available'])->toBeFalse()
        ->and($reference['card'])->toBeNull()
        ->and($reference['profile'])->toBeNull()
        ->and($reference['skills'])->toBe([])
        ->and($reference['schedule'])->toBe(['trainee' => null, 'scenario' => null]);
});

it('exposes the same reference on the Timeline and the Veteran detail screen', function (): void {
    $trainee = Umamusume::factory()->create(['name' => 'Shared Reference']);
    $card = CharacterCard::factory()->create([
        'card_id' => 101401,
        'umamusume_id' => $trainee->id,
        'title' => '[Shared Form]',
    ]);
    $run = TrainingRun::factory()->create([
        'umamusume_id' => $trainee->id,
        'character_card_id' => $card->id,
        'scenario' => 'ura_finale',
        'status' => RunStatus::Completed,
        'import_source' => 'tracker:shared-test',
    ]);

    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Timeline')
            ->where('reference.card.card_id', 101401)
            ->where('reference.schedule.trainee.source', 'https://gametora.com/umamusume/characters/101401-el-condor-pasa')
            ->where('standard_schedule.trainee.source', 'https://gametora.com/umamusume/characters/101401-el-condor-pasa'));

    $veteran = Veteran::factory()->create(['training_run_id' => $run->id]);

    $this->get(route('veterans.show', $veteran))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Veterans/Show')
            ->where('reference.card.card_id', 101401)
            ->where('reference.schedule.scenario.source', 'https://gametora.com/umamusume/ura-finals'));
});
