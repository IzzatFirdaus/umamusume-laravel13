<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/*
 * ADR-0008 / PRD FR-A-6: costume cards are their own rows, keyed by the source's
 * own card id, and they carry their provenance inline the way every other
 * reference table here does. These tests pin the grain (one row per card), the
 * identity rule (a re-fetch cannot duplicate), the delete rule (a card never
 * outlives its trainee) and the casts the read path in Tasks 10-12 depends on.
 */

it('creates a card, links it to its trainee and casts its rarity', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create([
        'umamusume_id' => $umamusume->id,
        'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar,
    ]);

    expect($card->rarity)->toBe(CardRarity::ThreeStar)
        ->and($card->umamusume->id)->toBe($umamusume->id)
        ->and($umamusume->cards)->toHaveCount(1);
});

it('keeps card ids unique so a re-fetch cannot duplicate a card', function (): void {
    $first = CharacterCard::factory()->create(['card_id' => 900701]);

    expect(fn () => CharacterCard::factory()->create([
        'card_id' => $first->card_id,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]))->toThrow(QueryException::class);
});

it('deletes a trainee\'s cards with her', function (): void {
    $umamusume = Umamusume::factory()->create();
    CharacterCard::factory()->count(3)->create(['umamusume_id' => $umamusume->id]);

    $umamusume->delete();

    expect(CharacterCard::query()->count())->toBe(0);
});

it('stores the source character link the parser has always emitted', function (): void {
    expect(Schema::hasColumn('umamusume', 'external_ref'))->toBeTrue();
});

it('ships exactly the columns the card-layer docs list', function (): void {
    // ADR-0008's Decision table, ARCHITECTURE.md §3's character_cards fence, the
    // ESSENTIALS digest line and D-30 all name the same twelve fillable columns
    // plus `id` and timestamps. This is the schema side of that agreement, and it
    // is an exact pin rather than a `toContain` sweep on purpose: a column added
    // to the migration without moving those four docs fails here, and a column
    // they name that no longer ships fails too. Schema change and digest change
    // then travel together, which is the AGENTS.md Architect rule made checkable.
    //
    // KI-33 adds `skills_innate` and `skills_unique`, and the 2026-10-02 slice adds
    // `skills_awakening`, `skills_event` and `skills_evo`, so the list below is seventeen
    // fillable columns. **The four docs named above have not moved** — the briefs governing
    // these changes bar edits to any ADR, to `CONSTRAINTS.md` and to the design corpus, and
    // `DocSchemaDriftTest` only pins `training_runs` columns, so no gate catches the gap. The
    // `ARCHITECTURE-ESSENTIALS.md` digest line was moved for the card lists, because AGENTS.md
    // requires the digest to travel with a schema change; `ADR-0008`, `ARCHITECTURE.md` §3 and
    // D-30 still name the pre-KI-33 set, and that residual is recorded in the pass report rather
    // than closed by an edit those briefs do not allow.
    expect(Schema::getColumnListing('character_cards'))->toBe([
        'id',
        'card_id',
        'umamusume_id',
        'title',
        'rarity',
        'global_release_date',
        'is_debut_form',
        'unconfirmed',
        'source_url',
        'snapshot_path',
        'fetched_at',
        'source_timezone',
        'is_manual',
        // SQLite's `ALTER TABLE ADD COLUMN` appends, so a column added by a later migration sits
        // after the timestamps rather than before them. This list is physical order, which is what
        // `getColumnListing` returns — not declaration order in the migration files.
        'created_at',
        'updated_at',
        'skills_innate',
        'skills_unique',
        'skills_awakening',
        'skills_event',
        'skills_evo',
    ]);
});

it('stores the two skill lists as nullable json and reads them back as arrays', function (): void {
    $card = CharacterCard::create([
        'umamusume_id' => Umamusume::factory()->create()->id,
        'card_id' => 900801,
        'title' => '[Red Strife]',
        'rarity' => CardRarity::TwoStar,
        'global_release_date' => '2025-06-26',
        'source_url' => 'https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json',
        'skills_innate' => [201591, 201212, 201472],
        // Two uniques on one card: Gold Ship is the case a scalar column would drop.
        'skills_unique' => [10071, 100071],
    ])->fresh();

    expect($card->skills_innate)->toBe([201591, 201212, 201472])
        ->and($card->skills_unique)->toBe([10071, 100071]);

    // Nullable, because a card the document gives no lists for is a real card. And the
    // `array` cast does **not** coerce a stored null into `[]` — Eloquent returns null
    // for a null attribute whatever the cast says. So the read side has to treat null
    // as "no lists" explicitly; a `foreach` over the raw attribute would be iterating
    // null, and the pre-populate test below is where that is pinned instead of here.
    $bare = CharacterCard::factory()->create(['skills_innate' => null, 'skills_unique' => null])->fresh();

    expect($bare->skills_innate)->toBeNull();
});

it('reads fetched_at as a datetime so the card can be shown in the display zone', function (): void {
    // Task 11's card row prints $card->fetched_at->timezone(...)->format(...).
    // A 'timestamp' column without the cast hands back a string and that chain
    // dies on the string; the JST day proves the value moved through a real zone.
    $card = CharacterCard::factory()
        ->manual()
        ->create([
            'fetched_at' => '2026-06-30 20:00:00',
            'global_release_date' => '2026-06-01',
            'unconfirmed' => true,
        ])
        ->fresh();

    expect($card->fetched_at)->toBeInstanceOf(Carbon::class)
        ->and($card->fetched_at->timezone('Asia/Tokyo')->format('Y-m-d'))->toBe('2026-07-01');
});

it('reads the three card flags as booleans, not as whatever the driver stored', function (): void {
    $card = CharacterCard::factory()
        ->manual()
        ->unconfirmed()
        ->debut()
        ->create()
        ->fresh();

    expect($card->is_manual)->toBeTrue()
        ->and($card->unconfirmed)->toBeTrue()
        ->and($card->is_debut_form)->toBeTrue();

    $fetched = CharacterCard::factory()->create()->fresh();

    expect($fetched->is_manual)->toBeFalse()
        ->and($fetched->unconfirmed)->toBeFalse()
        ->and($fetched->is_debut_form)->toBeFalse();
});

it('refuses a card with no source and no Global date, which are the two rules that make a row a card', function (): void {
    foreach (['source_url' => null, 'global_release_date' => null] as $column => $value) {
        expect(fn () => CharacterCard::factory()->create([$column => $value]))
            ->toThrow(QueryException::class, $column);
    }
});

it('orders rarity by the glyph run, since hue cannot rank three near-identical fills', function (): void {
    expect(CardRarity::cases())->toHaveCount(3)
        ->and(CardRarity::OneStar->stars())->toBe('★')
        ->and(CardRarity::TwoStar->stars())->toBe('★★')
        ->and(CardRarity::ThreeStar->stars())->toBe('★★★');
});

/*
 * ADR-0008 / PRD FR-C-1 as amended: a run may name the costume-card form it
 * started on. The reference is optional and additive — a run that names only its
 * trainee is a complete run, not one missing data — and the card must belong to
 * that same trainee, so the exists rule carries the join at the boundary.
 */

it('records the form a run was started on', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create(['umamusume_id' => $umamusume->id]);

    // Model::create() rather than the factory: #[Fillable] does not constrain a
    // factory, so only a create() proves this column is actually assignable.
    $run = TrainingRun::create([
        'umamusume_id' => $umamusume->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ]);

    expect($run->characterCard->id)->toBe($card->id)
        ->and($run->umamusume->id)->toBe($umamusume->id);
});

it('refuses a card that belongs to another trainee', function (): void {
    $mine = Umamusume::factory()->create();
    $theirs = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create(['umamusume_id' => $theirs->id]);

    test()->post('/training-runs', [
        'umamusume_id' => $mine->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::query()->count())->toBe(0);
});

it('still creates a run with only a trainee', function (): void {
    $umamusume = Umamusume::factory()->create();

    test()->post('/training-runs', [
        'umamusume_id' => $umamusume->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    expect(TrainingRun::first()->character_card_id)->toBeNull();
});

it('drops the form attribution, not the run, when the card it named is deleted', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create(['umamusume_id' => $umamusume->id]);

    $run = TrainingRun::create([
        'umamusume_id' => $umamusume->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ]);

    // `character_cards` is engine-owned reference data, so this delete is a fetch or
    // a correction dropping a card the Trainer is pointing at. The FK must null the
    // optional attribution rather than refuse the delete or take the run with it --
    // without `nullOnDelete()` the line below throws a foreign key violation here.
    $card->delete();

    $run->refresh();

    expect($run->character_card_id)->toBeNull()
        ->and($run->umamusume_id)->toBe($umamusume->id)
        ->and(TrainingRun::query()->whereKey($run->id)->exists())->toBeTrue();
});

/*
 * The join in `StoreTrainingRunRequest` has a second branch the three cases above
 * never post: a request that names a card and names no trainee. The rule reads
 * `umamusume_id` with `input()`, so an absent or null trainee leaves the card with
 * nothing to match against, and a card that cannot be shown to belong to the
 * submitted trainee is not a valid choice. This is the behaviour the request
 * comment promises, asserted as required and not as a framework incidental: the
 * pair is refused on both fields and no run row appears.
 */
it('refuses a named card when the request supplies no trainee', function (array $traineeKeys): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create(['umamusume_id' => $umamusume->id]);

    test()->post('/training-runs', $traineeKeys + [
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasErrors(['umamusume_id', 'character_card_id']);

    // The card row is real, so the refusal on `character_card_id` is the
    // same-trainee join failing rather than a card that never existed.
    expect(CharacterCard::query()->whereKey($card->id)->exists())->toBeTrue()
        ->and(TrainingRun::query()->count())->toBe(0);
})->with([
    'umamusume_id absent' => [[]],
    'umamusume_id null' => [['umamusume_id' => null]],
]);
