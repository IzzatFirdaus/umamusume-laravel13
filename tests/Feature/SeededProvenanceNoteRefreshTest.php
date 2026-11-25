<?php

declare(strict_types=1);

use App\Enums\RaceEntryStatus;
use App\Models\CharacterCard;
use App\Models\DeckSlot;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\RunSkill;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use App\Models\Veteran;
use Database\Seeders\RosyDreamsRiceShowerSeeder;
use Database\Seeders\TrackerVeteransSeeder;
use Database\Seeders\UraFinaleRunsSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Re-seed behaviour of the three imported-career seeders' provenance notes.
 *
 * Every seeder here binds to concrete catalog rows, so the fixture materialises exactly those
 * ids before the first seed: the six support cards both the Rosy deck and the tracker's run-15
 * fixture resolve by support_id, the umamusume, character cards, skills and race slots the merged
 * URA table names, and Rosy's own trainee, card, parents, skills and race slots. Nothing here is
 * an assertion; the assertions are below.
 */
beforeEach(function (): void {
    if (Umamusume::find(1001) === null) {
        // `support_cards.char_id` points at this id (the support-card factory's default).
        Umamusume::factory()->create(['id' => 1001]);
    }

    $sharedDeck = [
        ['support_id' => 30003, 'card_id' => 250],
        ['support_id' => 30018, 'card_id' => 265],
        ['support_id' => 30020, 'card_id' => 267],
        ['support_id' => 30016, 'card_id' => 263],
        ['support_id' => 30030, 'card_id' => 277],
        ['support_id' => 30019, 'card_id' => 266],
    ];

    foreach ($sharedDeck as $card) {
        if (SupportCard::find($card['card_id']) !== null) {
            continue;
        }

        $created = SupportCard::factory()->create(['support_id' => $card['support_id']]);

        // `support_cards.id` is not in the model's fillable set, so the row is re-keyed
        // through the query builder the way the catalog import resolves the client's own card id.
        DB::table('support_cards')->where('id', $created->id)->update(['id' => $card['card_id']]);
    }

    // Ensure CharacterCard with card_id 101501 exists for test repair scenarios
    if (CharacterCard::where('card_id', 101501)->count() === 0) {
        CharacterCard::factory()->create(['id' => 101501, 'card_id' => 101501]);
    }

    $slots = [
        1 => ['title' => 'Junior Make Debut', 'year' => 1],
        180 => ['title' => 'Kikuka Sho', 'year' => 2],
        215 => ['title' => 'Arima Kinen', 'year' => 2, 'month' => 12, 'half' => 'Late'],
        375 => ['title' => 'JBC Sprint', 'year' => 3],
        400 => ['title' => 'Arima Kinen', 'year' => 3, 'month' => 12, 'half' => 'Late'],
        405 => ['title' => 'URA Finals Qualifier', 'year' => 4],
        406 => ['title' => 'URA Finals Semifinal', 'year' => 4],
        410 => ['title' => 'URA Finals Final (URA)', 'year' => 4],
        264 => ['title' => 'Nikkei Sho', 'year' => 2],
        280 => ['title' => 'Tenno Sho (Spring)', 'year' => 2],
        310 => ['title' => 'Takarazuka Kinen', 'year' => 2],
        355 => ['title' => 'Kyoto Daishoten', 'year' => 2],
    ];

    foreach (UraFinaleRunsSeeder::RUNS as $label => $run) {
        if (Umamusume::find($run['umamusume_id']) === null) {
            Umamusume::factory()->create(['id' => $run['umamusume_id']]);
        }

        if (CharacterCard::find($run['character_card_id']) === null) {
            // `card_id` comes from the factory's unique generator. It previously read
            // `UraFinaleRunsSeeder::CAREER_CARDS`, a constant the seeder never declared, so the
            // fixture error stopped all eleven cases before a single assertion ran. Nothing here
            // resolves a card by its client id — the seeder binds `character_card_id` — so any
            // unique value serves.
            CharacterCard::factory()->create([
                'id' => $run['character_card_id'],
                'umamusume_id' => $run['umamusume_id'],
            ]);
        }

        foreach ($run['skills'] as [$skillId]) {
            if (Skill::find($skillId) === null) {
                Skill::factory()->create(['id' => $skillId]);
            }
        }

        foreach ($run['races'] as $race) {
            if (RaceCatalogSlot::find($race['slot']) !== null) {
                continue;
            }

            RaceCatalogSlot::factory()->create($slots[$race['slot']] + ['id' => $race['slot']]);
        }
    }

    // Rosy's own prerequisites: the trainee, her [Rosy Dreams] card, the two parents the Legacy
    // panel names, her eleven skills and her five race slots.
    foreach ([30, 4, 8] as $umamusumeId) {
        if (Umamusume::find($umamusumeId) === null) {
            Umamusume::factory()->create(['id' => $umamusumeId]);
        }
    }

    if (CharacterCard::find(31) === null) {
        CharacterCard::factory()->create(['id' => 31, 'card_id' => 103001, 'umamusume_id' => 30]);
    }

    foreach ([47, 31, 142, 147, 174, 217, 219, 223, 311, 315, 148] as $skillId) {
        if (Skill::find($skillId) === null) {
            Skill::factory()->create(['id' => $skillId]);
        }
    }

    foreach ($slots as $slot => $definition) {
        if (RaceCatalogSlot::find($slot) === null) {
            RaceCatalogSlot::factory()->create($definition + ['id' => $slot]);
        }
    }
});

it('appends the tracker evidence limit to every imported career note', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $limit = 'This imported career preserves one tracker snapshot plus explicitly recorded results: '
        .'unrecorded turns, deck selection, support-card events, full Legacy lineage, Spark '
        .'activations, skill acquisition timing, and choices remain unknown.';

    foreach (UraFinaleRunsSeeder::RUNS as $label => $run) {
        $stored = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run '.$label.')')->sole();

        expect($stored->notes)->toBe($run['notes'].' '.$limit);
    }
});

it('refreshes a stale tracker note for an existing career on re-seed', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $source = 'docs/Umamusume_Progress_Tracker_Runs.md (Run 15)';
    $run = TrainingRun::where('import_source', $source)->sole();
    $raw = UraFinaleRunsSeeder::RUNS[15]['notes'];
    $limit = 'This imported career preserves one tracker snapshot plus explicitly recorded results: '
        .'unrecorded turns, deck selection, support-card events, full Legacy lineage, Spark '
        .'activations, skill acquisition timing, and choices remain unknown.';
    $before = TrainingRun::where('import_source', $source)->count();

    // The previous build stored the tracker snapshot's own note alone.
    $run->update(['notes' => $raw]);

    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::where('import_source', $source)->count())->toBe($before)
        ->and(TrainingRun::where('import_source', $source)->sole()->notes)->toBe($raw.' '.$limit);
});

it('leaves a Trainer-edited tracker note untouched on re-seed', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $source = 'docs/Umamusume_Progress_Tracker_Runs.md (Run 15)';
    $trainerNote = 'Trainer note: hold her out of the semifinal and go again in April.';

    TrainingRun::where('import_source', $source)->sole()->update(['notes' => $trainerNote]);

    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::where('import_source', $source)->sole()->notes)->toBe($trainerNote);
});

it('repairs the seeded trainee and card pair in place using the published card identity', function (): void {
    $card = CharacterCard::query()->where('card_id', 101501)->sole();
    $owner = Umamusume::factory()->create();
    $card->update(['umamusume_id' => $owner->id]);
    $this->seed(UraFinaleRunsSeeder::class);

    $run = TrainingRun::query()->where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 5)')->sole();
    expect($run->umamusume_id)->toBe($owner->id);
    $run->update(['umamusume_id' => UraFinaleRunsSeeder::RUNS[5]['umamusume_id']]);
    $this->seed(UraFinaleRunsSeeder::class);

    expect($run->refresh()->umamusume_id)->toBe($owner->id)
        ->and($run->character_card_id)->toBe($card->id)
        ->and($run->turnEntries()->count())->toBe(1);
});

it('fills a missing tracker note on re-seed', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);

    $source = 'docs/Umamusume_Progress_Tracker_Runs.md (Run 15)';
    $run = TrainingRun::where('import_source', $source)->sole();
    $raw = UraFinaleRunsSeeder::RUNS[15]['notes'];
    $limit = 'This imported career preserves one tracker snapshot plus explicitly recorded results: '
        .'unrecorded turns, deck selection, support-card events, full Legacy lineage, Spark '
        .'activations, skill acquisition timing, and choices remain unknown.';

    $run->update(['notes' => null]);

    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::where('import_source', $source)->sole()->notes)->toBe($raw.' '.$limit);
});

it('refreshes the veteran note on re-seed and keeps the ancestry sentence last', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 15)')->sole();
    $veteran = Veteran::where('training_run_id', $run->id)->sole();

    if ($veteran->notes === null) {
        throw new RuntimeException('The seeded veteran note is missing.');
    }

    $fresh = $veteran->notes;
    $disclosure = 'The complete in-career history is not recoverable from the source snapshot.';
    $stale = str_replace(' '.$disclosure.' ', ' ', $fresh);

    $veteran->update(['notes' => $stale, 'tags' => ['cup-winner']]);
    $before = Veteran::count();

    $this->seed(TrackerVeteransSeeder::class);

    $veteran->refresh();

    $ts = file_get_contents(base_path('resources/js/domain/veteranNote.ts'));

    if ($ts === false) {
        throw new RuntimeException('veteranNote.ts is unreadable.');
    }

    if (preg_match('/export const VETERAN_ANCESTRY_NOTE =\s*\'([^\']+)\';/u', $ts, $matches) !== 1) {
        throw new RuntimeException('VETERAN_ANCESTRY_NOTE was not found.');
    }

    expect(Veteran::count())->toBe($before)
        ->and($veteran->notes)->toBe($fresh)
        ->and($veteran->notes)->toEndWith($matches[1])
        ->and($veteran->tags)->toBe(['cup-winner']);
});

it('leaves a Trainer-edited veteran note untouched on re-seed', function (): void {
    $this->seed(TrackerVeteransSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/Umamusume_Progress_Tracker_Runs.md (Run 15)')->sole();
    $veteran = Veteran::where('training_run_id', $run->id)->sole();
    $trainerNote = 'Trainer note: keep this one on the shortlist.';

    $veteran->update(['notes' => $trainerNote]);

    $this->seed(TrackerVeteransSeeder::class);

    expect(Veteran::where('training_run_id', $run->id)->sole()->notes)->toBe($trainerNote);
});

it('appends the Turn 67 endpoint limit to the Rosy Dreams note', function (): void {
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $source = 'docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower)';
    $observed = 'Unity Cup, [Rosy Dreams] Rice Shower, read off 68 client frames on 2026-10-03. Senior '
        .'Year Early October, turn 67 of 72, five turns until the finals. Fans 209,245 against a Top '
        .'Star ladder of 240,000. Energy reads low on the bar and the client is printing its injury '
        .'warning, but no number is shown, so none is stored. Team Blue Bloom sits at Rank S with 55 '
        .'Unity Trainings and 11 combined bursts.';
    $limit = 'This is a Turn 67 endpoint record: earlier turns, exact event history, and actual '
        .'inheritance outcomes are not fully documented.';

    $run = TrainingRun::where('import_source', $source)->sole();

    expect($run->notes)->toStartWith($observed.' '.$limit)
        ->toContain('At least one Kyoto win is documented', 'Turn actions before Turn 67 are unknown')
        ->and($run->deckSlots()->count())->toBe(6)
        ->and($run->raceEntries()->count())->toBe(5)
        ->and($run->skills()->count())->toBe(11)
        ->and($run->turnEntries()->count())->toBe(1)
        ->and($run->turnEvents()->count())->toBe(2);

    $freshNote = $run->notes;

    $this->get(route('runs.timeline', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('standard_schedule.trainee.milestones', 14)
            ->where('standard_schedule.trainee.milestones.0.0', 3)
            ->where('standard_schedule.trainee.milestones.0.1', 'The Flower that Blooms in Yodo (fixed event, Stamina +5)')
            ->where('standard_schedule.trainee.milestones.13.0', 72));

    // The previous build stored the endpoint observation alone.
    $run->update(['notes' => $observed]);

    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $reseeded = TrainingRun::where('import_source', $source)->sole();

    expect($reseeded->notes)->toBe($freshNote)
        ->and($reseeded->turnEntries()->sole()->energyReading()['text'])->toBe('Low band')
        ->and($reseeded->turnEntries()->sole()->energy)->toBeNull()
        ->and($reseeded->deckSlots()->count())->toBe(6)
        ->and($reseeded->raceEntries()->count())->toBe(5)
        ->and($reseeded->skills()->count())->toBe(11)
        ->and($reseeded->turnEntries()->count())->toBe(1)
        ->and($reseeded->turnEvents()->count())->toBe(2);
});

it('adds no rows when the three seeders run twice', function (): void {
    $this->seed(UraFinaleRunsSeeder::class);
    $this->seed(TrackerVeteransSeeder::class);
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $runs = TrainingRun::count();
    $turns = TurnEntry::count();
    $races = RaceEntry::count();
    $skills = RunSkill::count();
    $decks = DeckSlot::count();

    $this->seed(UraFinaleRunsSeeder::class);
    $this->seed(TrackerVeteransSeeder::class);
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    expect(TrainingRun::count())->toBe($runs)
        ->and(TurnEntry::count())->toBe($turns)
        ->and(RaceEntry::count())->toBe($races)
        ->and(RunSkill::count())->toBe($skills)
        ->and(DeckSlot::count())->toBe($decks)
        ->and(Veteran::count())->toBe(8);

    // Seventeen tracker careers plus Rosy's single endpoint snapshot.
    expect($runs)->toBe(18);
});

it('removes the short-lived fixture disclosure from a Run 3 note but keeps Trainer text', function (): void {
    // KI-172: the borrowed deck no longer exists, so its disclosure must not linger. A Trainer-
    // edited note still wins over a re-seed, as on every other imported run.
    $this->seed(UraFinaleRunsSeeder::class);

    $source = 'docs/Umamusume_Progress_Tracker_Runs.md (Run 3)';
    $limit = 'This imported career preserves one tracker snapshot plus '
        .'explicitly recorded results: unrecorded turns, deck selection, support-card events, full '
        .'Legacy lineage, Spark activations, skill acquisition timing, and choices remain unknown.';
    $disclosure = ' The six cards shown in this run\'s deck are a UI fixture copied from the '
        .'[Rosy Dreams] Rice Shower reference build, not a recorded deck for Maruzensky: '
        .'the tracker recorded none. No support-card event, hint or bond inference may '
        .'be drawn from these slots.';

    $run = TrainingRun::where('import_source', $source)->sole();

    expect($run->notes)->toBe(UraFinaleRunsSeeder::RUNS[3]['notes'].' '.$limit)
        ->and($run->deckSlots()->count())->toBe(0);

    // A row from the short-lived disclosure build is seed-owned and gets cleaned up.
    $run->update(['notes' => UraFinaleRunsSeeder::RUNS[3]['notes'].' '.$limit.$disclosure]);
    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::where('import_source', $source)->sole()->notes)
        ->toBe(UraFinaleRunsSeeder::RUNS[3]['notes'].' '.$limit);

    $trainerNote = 'Trainer note: this one was a practice run.';
    TrainingRun::where('import_source', $source)->sole()->update(['notes' => $trainerNote]);
    $this->seed(UraFinaleRunsSeeder::class);

    expect(TrainingRun::where('import_source', $source)->sole()->notes)->toBe($trainerNote);
});

it('records Rosy\'s scheduled races as Scheduled, not as entries the frames never showed', function (): void {
    // KI-171: the client shows Kyoto Daishoten on the current turn's calendar and Arima Kinen
    // as a live goal with "Entry criteria met!". Neither proves an entry. `Scheduled` keeps the
    // calendar fact without claiming the action, and the three cleared goals stay Completed.
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $run = TrainingRun::where('import_source', 'docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower)')->sole();

    $scheduled = $run->raceEntries()->where('status', RaceEntryStatus::Scheduled->value)->get();

    expect($scheduled)->toHaveCount(2)
        ->and($scheduled->pluck('race_catalog_slot_id')->all())->toEqualCanonicalizing([355, 400])
        ->and($scheduled->pluck('placement')->filter()->all())->toBe([])
        ->and($run->raceEntries()->where('status', RaceEntryStatus::Completed->value)->count())->toBe(3)
        ->and($run->raceEntries()->where('status', RaceEntryStatus::Entered->value)->count())->toBe(0)
        ->and(RaceEntryStatus::Scheduled->label())->toBe('Scheduled (entry unrecorded)');

    // The re-seed deletes and re-creates Rosy's run and child rows, so the original model no
    // longer exists: re-query by the stable import_source label, not by the old primary key.
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $reseeded = TrainingRun::where('import_source', 'docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower)')->sole();

    expect($reseeded->raceEntries()->where('status', RaceEntryStatus::Scheduled->value)->count())->toBe(2)
        ->and($reseeded->raceEntries()->where('status', RaceEntryStatus::Entered->value)->count())->toBe(0);
});
