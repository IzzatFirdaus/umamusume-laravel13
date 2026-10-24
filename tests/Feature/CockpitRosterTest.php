<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RunStatus;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * A-0.6: the cockpit ships a derived team roster — the six deck cards' characters — as a read-out,
 * with no stored roster and no column.
 *
 * The rendered copy is the browser spec's; this file proves the wire, including the friend slot and
 * the ownership flag the deck already records, and the named absence for an empty deck.
 */
final class CockpitRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_derives_the_roster_from_the_six_deck_cards(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $first = SupportCard::factory()->create(['char_name' => 'Tokai Teio', 'title_en' => '[Dream Big!]']);
        $friend = SupportCard::factory()->create(['char_name' => 'Super Creek', 'title_en' => '[Piece of Mind]']);

        DeckSlot::factory()->create([
            'training_run_id' => $run->id,
            'support_card_id' => $first->id,
            'slot_position' => 1,
            'ownership' => 'OWNED',
        ]);
        DeckSlot::factory()->create([
            'training_run_id' => $run->id,
            'support_card_id' => $friend->id,
            'slot_position' => 6,
            'ownership' => 'RENTED',
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roster.rows.0.slot', 1)
                ->where('roster.rows.0.name', 'Tokai Teio [Dream Big!]')
                ->where('roster.rows.0.friend', false)
                ->where('roster.rows.0.ownership', 'OWNED')
                ->where('roster.rows.1.slot', 6)
                ->where('roster.rows.1.name', 'Super Creek [Piece of Mind]')
                ->where('roster.rows.1.friend', true)
                ->where('roster.rows.1.ownership', 'RENTED')
                ->where('roster.empty', null));
    }

    public function test_names_the_absence_when_no_deck_is_equipped(): void
    {
        $run = TrainingRun::factory()->create([
            'umamusume_id' => Umamusume::factory()->create()->id,
            'status' => RunStatus::Active,
        ]);

        $this->get(route('runs.cockpit', $run))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roster.rows', [])
                ->where('roster.empty', fn (string $empty): bool => str_contains($empty, 'No deck card')));
    }
}
