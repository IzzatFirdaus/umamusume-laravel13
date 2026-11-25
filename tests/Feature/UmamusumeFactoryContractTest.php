<?php

namespace Tests\Feature;

use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Check #6: Umamusume factory contract — fabricated rows are fixtures by default,
 * `catalogue()` opts a row back into catalogue visibility, `manual()` marks
 * Trainer-added rows.
 */
final class UmamusumeFactoryContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_created_umamusume_is_a_fixture_by_default(): void
    {
        $uma = Umamusume::factory()->create();

        expect($uma->is_fixture)->toBeTrue();
        expect($uma->is_manual)->toBeFalse();
    }

    public function test_catalogue_state_opts_a_row_back_into_catalogue_visibility(): void
    {
        $uma = Umamusume::factory()->catalogue()->create();

        expect($uma->is_fixture)->toBeFalse();
        expect($uma->is_manual)->toBeFalse();
    }

    public function test_manual_state_marks_trainer_added_row(): void
    {
        $uma = Umamusume::factory()->manual()->create();

        expect($uma->is_manual)->toBeTrue();
        expect($uma->is_fixture)->toBeTrue();
    }

    public function test_catalogue_and_manual_can_combine(): void
    {
        $uma = Umamusume::factory()->catalogue()->manual()->create();

        expect($uma->is_fixture)->toBeFalse();
        expect($uma->is_manual)->toBeTrue();
    }
}
