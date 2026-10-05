<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
 * Slice 2: support card reference data and run linkage (ADR-0014, which supersedes ADR-0005).
 *
 * Two groups of tests here exist because a constraint was reported as enforced when it was not.
 * `2026_09_30_142618` wrote `->check(...)` on four column definitions; in Laravel 13's SQLite grammar
 * that call renders no SQL, so a `slot_position` of 7 inserted cleanly and the first version of this
 * file answered by deleting the test on the grounds that SQLite "doesn't enforce" it. It does, once the
 * constraint is written in raw SQL, which is what `2026_09_30_151945` now does. Both layers are pinned:
 * the CHECK on the column and the `saving` hook on the model, because the hook is the one that also
 * catches factories, which reach the table without passing through a form request.
 *
 * The `char_id` tests are the other half. It was declared as a foreign key to `umamusume.id`, which is a
 * different number space (a local surrogate, with the GameTora id held in `external_ref`), and it would
 * have rejected the 23 Pal and group cards whose character is staff in the 9000 block. Those cards are
 * exactly the ones the Scenario Link is derived from, so the constraint was not merely redundant, it was
 * the constraint that deleted the evidence.
 */

// ── SupportCard: the columns are the export's columns ──────────────────────

it('creates a support card carrying the export fields, not a composed name', function (): void {
    $card = SupportCard::factory()->create();

    expect($card)->toBeInstanceOf(SupportCard::class)
        ->and($card->support_id)->toBeInt()
        ->and($card->char_name)->toBe('Special Week')
        ->and($card->title_en)->toBe('[Tracen Academy]')
        ->and($card->rarity)->toBe(CardRarity::OneStar)
        ->and($card->type)->toBe('guts')
        ->and(array_key_exists('name', $card->getAttributes()))->toBeFalse();
});

it('has no name column, because the source publishes no composed card name', function (): void {
    $columns = array_map(
        fn (object $c): string => (string) $c->name,
        DB::select('PRAGMA table_info(support_cards)')
    );

    expect($columns)->not->toContain('name')
        ->and($columns)->toContain('char_name');
});

it('composes the label a Trainer reads from the two parts the export gives', function (): void {
    $card = SupportCard::factory()->create(['char_name' => 'Tokai Teio', 'title_en' => '[Dream Big!]']);

    expect($card->displayName())->toBe('Tokai Teio [Dream Big!]');
});

it('falls back to the Japanese name when a card has no English character name', function (): void {
    $card = SupportCard::factory()->create([
        'char_name' => null,
        'name_ja' => 'スペシャルウィーク',
        'title_en' => null,
    ]);

    expect($card->displayName())->toBe('スペシャルウィーク');
});

it('names rarity the way the client does, not the way the enum label does', function (): void {
    // CardRarity::label() reads "Three stars", which is the order signal RarityChip puts in its
    // aria-label. The word the client prints on the card is SSR, and showing the wrong one here would
    // be the tool inventing vocabulary the game does not use.
    expect(SupportCard::factory()->create(['rarity' => 1])->rarityWord())->toBe('R')
        ->and(SupportCard::factory()->sr()->create()->rarityWord())->toBe('SR')
        ->and(SupportCard::factory()->ssr()->create()->rarityWord())->toBe('SSR');
});

it('casts rarity to the shared CardRarity enum so RarityChip works unchanged', function (): void {
    $card = SupportCard::factory()->ssr()->create();

    expect($card->rarity)->toBeInstanceOf(CardRarity::class)
        ->and($card->rarity->stars())->toBe('★★★');
});

it('maps export type keys to the Global client words at the model boundary', function (): void {
    // `intelligence` is the stat name the export keeps; `Wit` is what the client prints. `friend`
    // renders as Pal on the wikis' word alone, and ADR-0014 records that Pal is not a captured
    // client string.
    expect(SupportCard::factory()->wit()->create()->typeLabel())->toBe('Wit')
        ->and(SupportCard::factory()->friend()->create()->typeLabel())->toBe('Pal')
        ->and(SupportCard::factory()->speed()->create()->typeLabel())->toBe('Speed')
        ->and(SupportCard::factory()->group()->create()->typeLabel())->toBe('Group');
});

it('separates friend and group type from one another', function (): void {
    $friend = SupportCard::factory()->friend()->create();
    $group = SupportCard::factory()->group()->create();

    expect($friend->isFriendType())->toBeTrue()
        ->and($friend->isGroupType())->toBeFalse()
        ->and($group->isGroupType())->toBeTrue();
});

it('stores the eleven level anchors as an array, unexpanded', function (): void {
    // ADR-0014 correction 4: store the anchors, interpolate on read. A materialised 50-row ladder per
    // effect would be 28,000 rows that can silently disagree with the floor rule that produced them.
    $card = SupportCard::factory()->create([
        'effects' => [[1, 5, -1, -1, 10, 10, -1, -1, 15, -1, -1, -1]],
    ]);

    expect($card->effects)->toBeArray()
        ->and($card->effects[0])->toHaveCount(12)
        ->and($card->effects[0][1])->toBe(5);
});

it('keeps dates as dates so a date-only value stays date-only', function (): void {
    $card = SupportCard::factory()->create();

    expect($card->release_jp)->toBeInstanceOf(Carbon::class)
        ->and($card->release_global->toDateString())->toBe('2025-06-26');
});

it('derives release status from the two server dates', function (): void {
    $global = SupportCard::factory()->create();
    $jpOnly = SupportCard::factory()->jpOnly()->create();

    expect($global->refresh()->release_status)->toBe('Global')
        ->and($jpOnly->refresh()->release_status)->toBe('JP-only');
});

it('refuses a rarity outside the three the export has, at both layers', function (): void {
    // Through Eloquent the CardRarity cast fails before anything reaches the table, which is the
    // earlier and clearer refusal. The CHECK is the one that still holds for a raw write, so both are
    // pinned rather than one standing in for the other.
    expect(fn () => SupportCard::factory()->create(['rarity' => 5]))->toThrow(ValueError::class);

    expect(fn () => DB::table('support_cards')->insert([
        'support_id' => 91001,
        'rarity' => 5,
        'type' => 'speed',
        'source_url' => 'https://gametora.com/data/umamusume/support-cards.json',
        'fetched_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('refuses a type outside the seven', function (): void {
    expect(fn () => SupportCard::factory()->create(['type' => 'turbo']))->toThrow(QueryException::class);
});

// ── char_id is a source id, not a foreign key ──────────────────────────────

it('accepts a staff char_id that has no row in the trainable catalogue', function (): void {
    // The whole reason the foreign key came off. 23 of the 559 export records carry a 9000-block
    // char_id, and the document feeding `umamusume` (`gametora-characters.e9e9ee6d.json`) tops out at
    // char_id 1149, so with the constraint in place the import would have rejected every Pal card.
    $card = SupportCard::factory()->friend()->create();

    expect($card->char_id)->toBe(9001)
        ->and(SupportCard::find($card->id))->not->toBeNull();
});

it('accepts a char_id that is not a local umamusume primary key either', function (): void {
    // The second half of the same defect: umamusume.id is a surrogate and the GameTora character id
    // lives in external_ref, so char_id 1001 was being tested against a key that never holds it.
    SupportCard::factory()->create(['char_id' => 1001]);

    expect(Umamusume::count())->toBe(0)
        ->and(SupportCard::where('char_id', 1001)->exists())->toBeTrue();
});

it('carries no foreign key out of support_cards at all', function (): void {
    expect(DB::select('PRAGMA foreign_key_list(support_cards)'))->toHaveCount(0);
});

// ── Scenario Link is derived, never stored ─────────────────────────────────

it('reads a scenario link from the scenario list rather than a column on the card', function (): void {
    $linked = SupportCard::factory()->unityCupLink()->create();
    $unlinked = SupportCard::factory()->create(['char_name' => 'Special Week']);

    expect($linked->isScenarioLink('unity_cup'))->toBeTrue()
        ->and($unlinked->isScenarioLink('unity_cup'))->toBeFalse();
});

it('gives the same card a different answer under a different scenario', function (): void {
    // The proof that a stored flag per card would be wrong: the badge follows the scenario, not the
    // card. Aoi Kiryuin is URA Finale's only linked character and is not on Unity Cup's list.
    $card = SupportCard::factory()->create(['char_name' => 'Aoi Kiryuin', 'char_id' => 9004]);

    expect($card->isScenarioLink('ura_finale'))->toBeTrue()
        ->and($card->isScenarioLink('unity_cup'))->toBeFalse();
});

it('reports no scenario link for a scenario that publishes none', function (): void {
    $card = SupportCard::factory()->create(['char_name' => 'Rice Shower']);

    expect($card->isScenarioLink('trackblazer'))->toBeFalse();
});

// ── DeckSlot: both enforcement layers ──────────────────────────────────────

it('rejects a slot position outside one to six at the model layer', function (): void {
    // The test that was deleted on a wrong premise. The model hook is the layer that also catches a
    // factory, which reaches the table without passing through #[Fillable].
    $run = TrainingRun::factory()->create();

    expect(fn () => DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => SupportCard::factory()->create()->id,
        'slot_position' => 7,
    ]))->toThrow(InvalidArgumentException::class);
});

it('rejects a slot position outside one to six at the column layer', function (): void {
    // Written around the model so it pins the DDL rather than the hook: a later change that drops the
    // CHECK is still caught here.
    $run = TrainingRun::factory()->create();

    expect(fn () => DB::table('deck_slots')->insert([
        'training_run_id' => $run->id,
        'support_card_id' => SupportCard::factory()->create()->id,
        'slot_position' => 0,
    ]))->toThrow(QueryException::class);
});

it('accepts each of the six positions', function (): void {
    $run = TrainingRun::factory()->create();

    foreach (DeckSlot::POSITIONS as $position) {
        DeckSlot::factory()->atPosition($position)->create([
            'training_run_id' => $run->id,
            'support_card_id' => SupportCard::factory()->create()->id,
        ]);
    }

    expect(DeckSlot::where('training_run_id', $run->id)->count())->toBe(6);
});

it('refuses to build a slot at an impossible position through the factory', function (): void {
    expect(fn () => DeckSlot::factory()->atPosition(9)->create())
        ->toThrow(InvalidArgumentException::class);
});

it('enforces one card per position per run', function (): void {
    $run = TrainingRun::factory()->create();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id]);

    expect(fn () => DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id]))
        ->toThrow(QueryException::class);
});

it('allows the same position in a different run', function (): void {
    DeckSlot::factory()->atPosition(3)->create();
    DeckSlot::factory()->atPosition(3)->create();

    expect(DeckSlot::count())->toBe(2);
});

it('identifies the friend slot by position alone', function (): void {
    // Any card may sit at six, including a stat card, so the role cannot be read off the card
    // (ADR-0005 correction 1, carried into ADR-0014).
    $statAtSix = DeckSlot::factory()->friendSlot()->create([
        'support_card_id' => SupportCard::factory()->speed()->create()->id,
    ]);

    expect($statAtSix->isFriendSlot())->toBeTrue()
        ->and($statAtSix->supportCard->type)->toBe('speed')
        ->and(DeckSlot::factory()->atPosition(1)->create()->isFriendSlot())->toBeFalse();
});

// ── Relationships and referential behaviour ────────────────────────────────

it('orders a run deck by slot position', function (): void {
    $run = TrainingRun::factory()->create();

    foreach ([4, 1, 6] as $position) {
        DeckSlot::factory()->atPosition($position)->create([
            'training_run_id' => $run->id,
            'support_card_id' => SupportCard::factory()->create()->id,
        ]);
    }

    expect($run->deckSlots->pluck('slot_position')->all())->toBe([1, 4, 6]);
});

it('eager loads a slot card', function (): void {
    expect(DeckSlot::factory()->create()->supportCard)->toBeInstanceOf(SupportCard::class);
});

it('deletes a run slots when the run goes', function (): void {
    $run = TrainingRun::factory()->create();
    $slot = DeckSlot::factory()->create(['training_run_id' => $run->id]);

    $run->delete();

    expect(DeckSlot::find($slot->id))->toBeNull();
});

it('refuses to delete a card a run is still using', function (): void {
    // ON DELETE RESTRICT: removing a card from under a logged deck would make that run's history
    // unreadable, so the caller has to unassign it first.
    $slot = DeckSlot::factory()->create();

    expect(fn () => $slot->supportCard->delete())->toThrow(QueryException::class);
});

it('lists the runs a card appears in', function (): void {
    $card = SupportCard::factory()->create();

    DeckSlot::factory()->create([
        'training_run_id' => TrainingRun::factory()->create()->id,
        'support_card_id' => $card->id,
    ]);
    DeckSlot::factory()->atPosition(2)->create([
        'training_run_id' => TrainingRun::factory()->create()->id,
        'support_card_id' => $card->id,
    ]);

    expect($card->deckSlots)->toHaveCount(2);
});

// ── SupportEffect: the dictionary, in the export's vocabulary ──────────────

it('creates an effect using the calc and symbol words the export actually sends', function (): void {
    $effect = SupportEffect::factory()->create();

    expect($effect->effect_id)->toBeInt()
        ->and($effect->calc)->toBe('mult')
        ->and($effect->symbol)->toBe('percent');
});

it('leaves calc null for the effects that declare no combining mode', function (): void {
    // 31 of 35 records. UMAMUSUME_REFERENCE.md §1.4.8 finds that only the four declaring calc combine
    // multiplicatively, so a stored `flat` would claim a mode the client does not have.
    $effect = SupportEffect::factory()->undeclared()->create();

    expect($effect->calc)->toBeNull()
        ->and($effect->symbol)->toBe('none');
});

it('rejects a calc value the source never emits', function (): void {
    expect(fn () => SupportEffect::factory()->create(['calc' => 'flat']))
        ->toThrow(QueryException::class);
});

it('stores no timestamps on the dictionary', function (): void {
    expect(SupportEffect::factory()->create()->created_at)->toBeNull();
});

it('uniquely keys an effect id, which is how the card anchors join to it', function (): void {
    $effect = SupportEffect::factory()->create(['effect_id' => 777]);

    expect(fn () => SupportEffect::factory()->create(['effect_id' => $effect->effect_id]))
        ->toThrow(QueryException::class);
});
