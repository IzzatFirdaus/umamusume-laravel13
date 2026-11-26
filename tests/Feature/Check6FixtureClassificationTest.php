<?php

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Check #6: Fixture classification and provenance acceptance.
 *
 * Covers:
 * - Factory-generated fixtures default to is_fixture=true.
 *   catalogue() explicitly sets is_fixture=false.
 * - Independent is_manual classification.
 * - Fixture exclusion from applicable catalogue queries and user-facing collections.
 * - Genuine catalogue source provenance distinguishable from synthetic fixtures (isFixture in resource).
 * - Global eligibility based on actual source records (release_status filtering).
 * - Character identities vs published card variants (Umamusume vs CharacterCard).
 */
final class Check6FixtureClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_created_umamusume_is_fixture_by_default(): void
    {
        $uma = Umamusume::factory()->create();

        expect($uma->is_fixture)->toBeTrue();
        expect($uma->is_manual)->toBeFalse();
    }

    public function test_catalogue_state_sets_is_fixture_false(): void
    {
        $uma = Umamusume::factory()->catalogue()->create();

        expect($uma->is_fixture)->toBeFalse();
        expect($uma->is_manual)->toBeFalse();
    }

    public function test_manual_is_independent_of_fixture(): void
    {
        $manualFixture = Umamusume::factory()->manual()->create();
        expect($manualFixture->is_manual)->toBeTrue();
        expect($manualFixture->is_fixture)->toBeTrue();

        $catalogueManual = Umamusume::factory()->catalogue()->manual()->create();
        expect($catalogueManual->is_manual)->toBeTrue();
        expect($catalogueManual->is_fixture)->toBeFalse();
    }

    public function test_fixture_rows_excluded_from_catalog_index(): void
    {
        // A catalogue-eligible row should appear in the API index.
        Umamusume::factory()->catalogue()->create([
            'name' => 'Catalog Trainee',
            'slug' => 'catalog-trainee',
        ]);

        // A factory fixture row should NOT appear.
        Umamusume::factory()->create([
            'name' => 'Fixture Trainee',
            'slug' => 'fixture-trainee',
        ]);

        $response = $this->getJson(route('api.v1.umamusume.index'));

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        expect($slugs)->toContain('catalog-trainee');
        expect($slugs)->not->toContain('fixture-trainee');
    }

    public function test_fixture_rows_excluded_from_api_v1_index(): void
    {
        Umamusume::factory()->catalogue()->create([
            'name' => 'Catalog Trainee',
            'slug' => 'catalog-trainee',
        ]);
        Umamusume::factory()->create([
            'name' => 'Fixture Trainee',
            'slug' => 'fixture-trainee',
        ]);

        $response = $this->getJson(route('api.v1.umamusume.index'));

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        expect($slugs)->toContain('catalog-trainee');
        expect($slugs)->not->toContain('fixture-trainee');
    }

    public function test_api_resource_includes_is_fixture(): void
    {
        // A catalogue-eligible row should carry isFixture=false in the API response.
        Umamusume::factory()->catalogue()->create([
            'name' => 'Resource Trainee',
            'slug' => 'resource-trainee',
            'is_manual' => false,
            'is_fixture' => false,
        ]);

        $response = $this->getJson(route('api.v1.umamusume.index'));

        $response->assertOk();
        $rows = collect($response->json('data'));
        $resourceRow = $rows->firstWhere('slug', 'resource-trainee');
        expect($resourceRow)->not->toBeNull();
        expect($resourceRow['isFixture'])->toBeFalse();

        // A fixture row should carry isFixture=true.
        Umamusume::factory()->create([
            'name' => 'Fixture Trainee',
            'slug' => 'fixture-trainee',
        ]);

        $response = $this->getJson(route('api.v1.umamusume.index'));

        $rows = collect($response->json('data'));
        $fixtureRow = $rows->firstWhere('slug', 'fixture-trainee');
        expect($fixtureRow)->toBeNull(); // fixtures excluded from API index

        // But when fetched directly via show endpoint, isFixture should be true.
        $fixture = Umamusume::factory()->create([
            'name' => 'Direct Fixture',
            'slug' => 'direct-fixture',
        ]);

        $response = $this->getJson(route('api.v1.umamusume.show', ['slug' => 'direct-fixture']));
        $response->assertOk();
        expect($response->json('data.isFixture'))->toBeTrue();
    }

    public function test_global_eligibility_filters_by_release_status(): void
    {
        Umamusume::factory()->catalogue()->create([
            'name' => 'Global Trainee',
            'slug' => 'global-trainee',
            'release_status' => ReleaseStatus::GlobalReleased,
        ]);
        Umamusume::factory()->catalogue()->create([
            'name' => 'Japan Trainee',
            'slug' => 'japan-trainee',
            'release_status' => ReleaseStatus::JapanOnly,
        ]);

        // Default API index should only show GlobalReleased.
        $response = $this->getJson(route('api.v1.umamusume.index'));
        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        expect($slugs)->toContain('global-trainee');
        expect($slugs)->not->toContain('japan-trainee');

        // status=JapanOnly should show JapanOnly rows.
        $response = $this->getJson(route('api.v1.umamusume.index', ['status' => 'JapanOnly']));
        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        expect($slugs)->not->toContain('global-trainee');
        expect($slugs)->toContain('japan-trainee');
    }

    public function test_character_identity_and_card_variant_are_distinct(): void
    {
        $uma = Umamusume::factory()->catalogue()->create([
            'name' => 'Rice Shower',
            'slug' => 'rice-shower',
        ]);

        $card = CharacterCard::factory()->create([
            'umamusume_id' => $uma->id,
            'card_id' => 1001,
            'title' => '[Sample Form]',
            'is_debut_form' => true,
        ]);

        // The character row and card row are separate entities.
        expect($uma->isNot($card))->toBeTrue();
        expect($uma->cards->contains($card))->toBeTrue();

        // The card belongs to exactly one character.
        expect($card->umamusume->is($uma))->toBeTrue();
    }
}
