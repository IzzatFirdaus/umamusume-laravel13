<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use App\Models\DeckSlot;
use App\Models\RaceCatalogSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\VeteranPortfolio;
use Database\Seeders\UraFinaleRunsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * KI-172's consumption half: a deck is either an explicitly synthetic test fixture or
 * Trainer-entered data, never a by-product of importing historical runs. The tracker records no
 * deck for any of the seventeen runs, so every read path must show an absence there, while the
 * same read path must still render a deck that a test builds on purpose.
 */
beforeEach(function (): void {
    foreach (UraFinaleRunsSeeder::RUNS as $run) {
        if (Umamusume::find($run['umamusume_id']) === null) {
            Umamusume::factory()->create(['id' => $run['umamusume_id']]);
        }
        foreach ($run['skills'] as [$skillId]) {
            if (Skill::find($skillId) === null) {
                Skill::factory()->create(['id' => $skillId]);
            }
        }
        foreach ($run['races'] as $race) {
            if (RaceCatalogSlot::find($race['slot']) === null) {
                RaceCatalogSlot::factory()->create([
                    'id' => $race['slot'],
                    'title' => 'Fixture slot '.$race['slot'],
                    'year' => $race['slot'] % 4 + 1,
                    'month' => $race['slot'] % 12 + 1,
                    'half' => 'Early',
                ]);
            }
        }
    }

    foreach (UraFinaleRunsSeeder::CAREER_CARDS as $cardId) {
        $card = CharacterCard::query()->where('card_id', $cardId)->first();
        if ($card === null) {
            CharacterCard::factory()->create([
                'card_id' => $cardId,
                'umamusume_id' => UraFinaleRunsSeeder::RUNS[array_search($cardId, UraFinaleRunsSeeder::CAREER_CARDS, true)]['umamusume_id'],
            ]);
        }
    }
});

it('shows a named deck absence on an imported run instead of a borrowed fixture', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 3)')->sole();

    // The imported tracker records no deck, so the cockpit's roster readout names the absence
    // rather than borrowing a fixture. The roster payload is the recorded roster (`members` /
    // `empty` / `unrecorded`, A-0.6 D01); an imported run with no recorded members reports
    // `unrecorded: true` rather than a fabricated roster.
    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('roster.members', [])
            ->where('roster.empty', fn (string $empty): bool => str_contains($empty, 'No team roster'))
            ->where('roster.unrecorded', true));

    $this->getJson(route('api.v1.training-runs.show', $run))
        ->assertOk()
        ->assertJsonPath('data.deck', []);
});

it('renders an explicitly synthetic six-card deck through the same read paths', function (): void {
    // The deck screens' coverage lives here, in a test-owned run, rather than in a historical
    // seeder. Nothing in this test carries an import_source, so no reader can mistake it for
    // evidence about a real career.
    $run = TrainingRun::factory()->create(['import_source' => null]);

    foreach ([1, 2, 3, 4, 5, 6] as $position) {
        DeckSlot::factory()->atPosition($position)->create([
            'training_run_id' => $run->id,
            'support_card_id' => SupportCard::factory()->create()->id,
        ]);
    }

    $this->getJson(route('api.v1.training-runs.show', $run))
        ->assertOk()
        ->assertJsonCount(6, 'data.deck')
        ->assertJsonPath('data.deck.0.slotPosition', 1);

    expect(VeteranPortfolio::forRun($run, null)['deck_slots'])->toBe(6);
});
