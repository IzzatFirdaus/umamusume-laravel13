<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The trainee's growth-rate row is copied from the selected character card
 * when a career is started through the wizard (SCREEN-008, A6.3).
 *
 * The catalogue stores the growth percentages keyed by stat name on the
 * character card (CharacterCard::$growth_rates). When the Trainer starts
 * a career, this row should be transferred to the run so the Cockpit can
 * display it without the Trainer re-entering it.
 */
final class CareerStartGrowthRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_copies_growth_rate_from_character_card_when_starting_career(): void
    {
        $umamusume = Umamusume::factory()->create();

        // Create a character card with growth rates (e.g., Rice Shower: +0/+10/+0/+20/+0)
        $card = CharacterCard::factory()->create([
            'umamusume_id' => $umamusume->id,
            'is_debut_form' => true,
            'growth_rates' => ['Speed' => 0, 'Stamina' => 10, 'Power' => 0, 'Guts' => 20, 'Wit' => 0],
        ]);

        // Compose a complete draft with this trainee and card
        SetupDraft::write([
            'scenario' => 'ura_finale',
            'umamusume_id' => $umamusume->id,
            'character_card_id' => $card->id,
        ]);

        // Start the career
        $this->put(route('career.preflight.store'))
            ->assertRedirect();

        // The created run should have the growth_rate from the card
        $run = TrainingRun::query()->where('umamusume_id', $umamusume->id)->sole();

        expect($run->growth_rate)->toBe([
            'Speed' => 0,
            'Stamina' => 10,
            'Power' => 0,
            'Guts' => 20,
            'Wit' => 0,
        ]);
    }

    public function test_omits_growth_rate_when_card_has_none(): void
    {
        $umamusume = Umamusume::factory()->create();

        // Card without growth_rates
        $card = CharacterCard::factory()->create([
            'umamusume_id' => $umamusume->id,
            'is_debut_form' => true,
            'growth_rates' => null,
        ]);

        SetupDraft::write([
            'scenario' => 'ura_finale',
            'umamusume_id' => $umamusume->id,
            'character_card_id' => $card->id,
        ]);

        $this->put(route('career.preflight.store'))
            ->assertRedirect();

        $run = TrainingRun::query()->where('umamusume_id', $umamusume->id)->sole();

        expect($run->growth_rate)->toBeNull();
    }

    public function test_omits_growth_rate_when_no_card_selected(): void
    {
        $umamusume = Umamusume::factory()->create();

        SetupDraft::write([
            'scenario' => 'ura_finale',
            'umamusume_id' => $umamusume->id,
            'character_card_id' => null,
        ]);

        $this->put(route('career.preflight.store'))
            ->assertRedirect();

        $run = TrainingRun::query()->where('umamusume_id', $umamusume->id)->sole();

        expect($run->growth_rate)->toBeNull();
    }
}
