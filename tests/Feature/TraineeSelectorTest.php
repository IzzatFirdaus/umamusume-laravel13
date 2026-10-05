<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Http\Requests\StoreTrainingRunRequest;
use App\Models\CharacterCard;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The searchable trainee and costume-card selector on "New training run" (FR-A-6, US-3), ported to
 * Inertia (ADR-0020 §1).
 *
 * What this file proves, and what moved.
 *
 * The Blade page shipped two pickers: a native <select> for a browser with scripting off, and a
 * combobox the script switched on. A client-rendered page has no no-script path, so the fallback and
 * the payload guard that protected it were retired by owner ruling on 2026-10-05, along with eleven
 * shape pins that read `trainee-combobox.ts` source text because no JS runner existed and C-8 forbade
 * adding one. C-8 still forbids a JS unit-test dependency; Playwright is not one and is already in the
 * tree, so those behaviours are now proven by running them in tests/browser/runs.spec.ts.
 *
 * Held here: the payload the component is built from, and the server round trip. Read off props and
 * off the stored row, never off markup text.
 *
 * One trap survives the port: every trainee name and card title sits in the response whether or not
 * the control offers it, so a substring match says nothing about the picker. Both are read through
 * the prop path instead.
 */

/**
 * The fixture this file measures against. Re-countable from itself: 2 selectable trainees,
 * 3 confirmed Global cards, 5 rows in total.
 */
function selectorRoster(): void
{
    $goldShip = Umamusume::factory()->create([
        'name' => 'Gold Ship', 'slug' => 'gold-ship',
        'name_ja' => 'ゴールドシップ', 'release_status' => ReleaseStatus::GlobalReleased,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100701, 'title' => '[Red Strife]',
        'rarity' => CardRarity::TwoStar, 'global_release_date' => '2025-06-26', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2026-07-02',
    ]);

    $fuji = Umamusume::factory()->create([
        'name' => 'Fuji Kiseki', 'slug' => 'fuji-kiseki',
        'name_ja' => 'フジキセキ', 'release_status' => ReleaseStatus::GlobalReleased,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $fuji->id, 'card_id' => 101401, 'title' => '[Four Seasons]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2025-12-01', 'is_debut_form' => true,
    ]);
}

/**
 * The roster prop, read out of the resolved page rather than out of the response string, so the ids
 * asserted below are the ones the component is actually handed.
 *
 * @return list<array<string, mixed>>
 */
function rosterProp(): array
{
    /** @var list<array<string, mixed>> $roster */
    $roster = test()->get('/training-runs/create')->assertOk()->inertiaProps('roster');

    return $roster;
}

it('sends every Global trainee, and only confirmed cards in her card list', function (): void {
    selectorRoster();

    $noCards = Umamusume::factory()->create(['name' => 'No Cards', 'slug' => 'no-cards']);
    $jpOnly = Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);
    CharacterCard::factory()->create(['umamusume_id' => $jpOnly->id, 'card_id' => 109001]);
    $shadowed = Umamusume::factory()->create(['name' => 'Unconfirmed Only', 'slug' => 'unconfirmed-only']);
    CharacterCard::factory()->unconfirmed()->create(['umamusume_id' => $shadowed->id, 'card_id' => 109002]);

    // The card gate lives inside the card list, not on the trainee (FR-A-6, FR-B-4): it is her form
    // that is unconfirmed, not her place on the roster, and `character_card_id` is nullable, so a
    // trainee with nothing confirmed is runnable. She arrives with `cards: []` rather than vanishing.
    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Create')
            ->has('roster', 4)
            ->where('roster', fn (Collection $rows): bool => $rows->pluck('trainee')->all() === ['Fuji Kiseki', 'Gold Ship', 'No Cards', 'Unconfirmed Only']
                && $rows->map(fn (array $r): int => count($r['cards']))->sum() === 3
                && $rows->firstWhere('umamusumeId', $noCards->id)['cards'] === []
                && $rows->firstWhere('umamusumeId', $shadowed->id)['cards'] === []
                // Release status is a trainee-level gate this does not touch.
                && $rows->firstWhere('umamusumeId', $jpOnly->id) === null));
});

it('sends the local primary key as the submittable id and the source id only for matching', function (): void {
    selectorRoster();

    $debutCard = CharacterCard::where('card_id', 100701)->firstOrFail();
    $runCard = CharacterCard::where('card_id', 100702)->firstOrFail();

    // Cards in release order, oldest first, so the list a Trainer reads matches the ordering the
    // catalog page already uses rather than inventing a second one. The verbatim title keeps its
    // brackets; `titleKey` drops them so `RUN` prefix-matches `[RUN! RUIN! LAUNCHER!]`, whose stored
    // string starts with `[`.
    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('roster.1.trainee', 'Gold Ship')
            ->where('roster.1.traineeJa', 'ゴールドシップ')
            ->where('roster.1.cards', [
                [
                    'selectionId' => $debutCard->id,
                    'sourceCardId' => 100701,
                    'title' => '[Red Strife]',
                    'titleKey' => 'Red Strife',
                    'releaseDate' => '2025-06-26',
                    'debut' => true,
                ],
                [
                    'selectionId' => $runCard->id,
                    'sourceCardId' => 100702,
                    'title' => '[RUN! RUIN! LAUNCHER!]',
                    'titleKey' => 'RUN! RUIN! LAUNCHER!',
                    'releaseDate' => '2026-07-02',
                    'debut' => false,
                ],
            ]));

    // Asserted across the whole fixture: this file's local ids are 1, 2, 3 while the source ids are
    // 1007xx, so a payload carrying the source id in `selectionId` fails here on every row.
    expect(collect(rosterProp())->flatMap(
        fn (array $trainee): Collection => collect($trainee['cards'])
            ->map(fn (array $card): bool => $card['selectionId'] !== $card['sourceCardId'])
    ))->not->toContain(false);
});

it('sends a trainee carrying an empty card list when the database holds no costume card at all', function (): void {
    /*
     * The shape of a freshly seeded dev database: `DatabaseSeeder` calls the Umamusume, Skill and
     * ScenarioSlot seeders, and no seeder writes `character_cards`, which a fetch fills. A trainee
     * list gated on those rows would leave the form uncompletable until the fetch landed.
     */
    $first = Umamusume::factory()->create(['name' => 'Cardless One', 'slug' => 'cardless-one']);
    Umamusume::factory()->create(['name' => 'Cardless Two', 'slug' => 'cardless-two']);
    $jpOnly = Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only Cardless', 'slug' => 'japan-only-cardless']);

    expect(CharacterCard::count())->toBe(0);

    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('roster', 2)
            ->where('roster', fn (Collection $rows): bool => $rows->pluck('trainee')->all() === ['Cardless One', 'Cardless Two']
                && $rows->map(fn (array $r): int => count($r['cards']))->sum() === 0
                && $rows->firstWhere('umamusumeId', $jpOnly->id) === null));

    // Then the rail completes: a trainee with no card row is postable, and the run it writes names
    // her with a null costume card rather than an invented one.
    test()->post('/training-runs', [
        'umamusume_id' => $first->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    $run = TrainingRun::firstOrFail();

    expect($run->umamusume_id)->toBe($first->id)
        ->and($run->character_card_id)->toBeNull();
});

it('offers exactly the scenario slugs the request will accept, labelled from config', function (): void {
    selectorRoster();

    // The picker and the validator must read one matrix. A key here that the request refused would be
    // a choice the Trainer cannot make; a slug the request accepts but the picker hides would be a
    // scenario nobody can reach (`config/scenarios.php` is the single source, D-240).
    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenarios', fn (Collection $map): bool => array_keys($map->all()) === StoreTrainingRunRequest::scenarios()
                && $map->every(fn (string $label, string $key): bool => $label === (string) config("scenarios.scenarios.{$key}.label"))));
});

it('sends every run status word the enum defines', function (): void {
    selectorRoster();

    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('statuses', ['Active' => 'Active', 'Completed' => 'Completed', 'Retired' => 'Retired']));
});

it('sends the Grade Point ceiling the field states rather than the form inventing', function (): void {
    selectorRoster();

    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('maxObjectiveIndex', RaceEntry::MAX_OBJECTIVE_INDEX));
});

it('hands the Trainer their own input back when another field fails', function (): void {
    selectorRoster();

    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    // POST, fail validation on `status`, redirect back with the input flashed, render. A form that
    // forgets the trainee on a 422 is a form the Trainer submits twice. A valid scenario posted with
    // a status the enum rejects is the shape of the mistake: everything chosen but the one bad field
    // comes back chosen.
    test()->withHeader('referer', url('/training-runs/create'))
        ->followingRedirects()
        ->post('/training-runs', [
            'umamusume_id' => $goldShip->id,
            'character_card_id' => $card->id,
            'status' => 'Not A Status',
            'scenario' => 'ura_finale',
        ])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Flashed form input comes back as text, so the ids are compared as text rather than
            // pinned to whatever type a hand-built POST happens to send.
            ->where('old', fn (Collection $old): bool => (string) $old->get('umamusume_id') === (string) $goldShip->id
                && (string) $old->get('character_card_id') === (string) $card->id
                && $old->get('scenario') === 'ura_finale')
            ->has('errors'));

    expect(TrainingRun::count())->toBe(0);
});

it('submits the local card id the payload carries, and refuses the source id', function (): void {
    selectorRoster();

    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    // The value the page's own roster offers, read out of the payload rather than the fixture, so
    // this posts exactly what a Trainer's browser would post.
    $fromPayload = collect(collect(rosterProp())
        ->firstWhere('trainee', 'Gold Ship')['cards'])
        ->firstWhere('sourceCardId', 100702);

    expect($fromPayload['selectionId'])->toBe($card->id);

    test()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'character_card_id' => $fromPayload['selectionId'],
        'status' => 'Active',
    ])->assertSessionHasNoErrors()->assertRedirect();

    expect(TrainingRun::firstOrFail()->character_card_id)->toBe($card->id);

    // And the id a client that mixed the two up would send. 100702 is not a local primary key in
    // this fixture, so the `exists` rule refuses it rather than writing a run that points at nothing.
    test()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'character_card_id' => 100702,
        'status' => 'Active',
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::count())->toBe(1);
});

it('rejects a card that belongs to a different trainee', function (): void {
    selectorRoster();

    $fuji = Umamusume::where('slug', 'fuji-kiseki')->firstOrFail();
    $goldShipCard = CharacterCard::where('card_id', 100702)->firstOrFail();

    /*
     * A fetch can re-parent a card when the source's char-ref mapping corrects itself. A roster left
     * open in a stale tab would then post a card that now belongs to someone else, and
     * StoreTrainingRunRequest's `where('umamusume_id', ...)` is what turns that into a rejected pair
     * instead of a run that contradicts itself.
     */
    test()->post('/training-runs', [
        'umamusume_id' => $fuji->id,
        'character_card_id' => $goldShipCard->id,
        'status' => 'Active',
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::count())->toBe(0);
});

it('carries a client title through the payload byte for byte rather than trusting it', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Bad Title One', 'slug' => 'bad-title-one']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id, 'card_id' => 199999,
        'title' => '[</script><script>alert(1)</script>]', 'is_debut_form' => true,
    ]);

    // Titles arrive from a Tier B export. The payload carries the stored string unchanged because the
    // component renders it as text; the half that proves it never reaches a Trainer as markup is
    // measured in the browser, where the element actually exists.
    test()->get('/training-runs/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('roster.0.trainee', 'Bad Title One')
            ->where('roster.0.cards.0.title', '[</script><script>alert(1)</script>]'));
});
