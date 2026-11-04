<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Inertia\Testing\AssertableInertia as Assert;

uses()->group('career-deck');

beforeEach(function (): void {
    // Seed a trainee for the draft
    // @phpstan-ignore-next-line property.notFound - Pest injects $this->trainee via beforeEach closure binding
    $this->trainee = Umamusume::factory()->create([
        'external_ref' => 'gametora:char:1001',
        'name' => 'Test Trainee',
    ]);

    // Start a session with the trainee selected
    Session::start();
    SetupDraft::write([
        'scenario' => 'ura_finale',
        'umamusume_id' => $this->trainee->id,
    ]);
});

afterEach(function (): void {
    Session::flush();
});

/**
 * @test
 * Picker payload carries ownership group from saved runs (OWNED / RENTED / NOT_RECORDED)
 */
it('picker payload includes ownership group derived from saved runs', function (): void {
    // Create support cards
    $ownedCard = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Owned Card', 'release_global' => '2025-06-26']);
    $rentedCard = SupportCard::factory()->create(['support_id' => 1002, 'char_name' => 'Rented Card', 'release_global' => '2025-06-26']);
    $notRecordedCard = SupportCard::factory()->create(['support_id' => 1003, 'char_name' => 'Not Recorded Card', 'release_global' => '2025-06-26']);

    // Create a saved run with OWNED deck slot
    // @phpstan-ignore-next-line property.notFound - Pest injects $this->trainee via beforeEach closure binding
    $run = TrainingRun::factory()->create(['umamusume_id' => $this->trainee->id]);
    DeckSlot::factory()->create([
        'training_run_id' => $run->id,
        'support_card_id' => $ownedCard->id,
        'slot_position' => 1,
        'ownership' => 'OWNED',
    ]);

    // Create another saved run with RENTED deck slot
    // @phpstan-ignore-next-line property.notFound - Pest injects $this->trainee via beforeEach closure binding
    $run2 = TrainingRun::factory()->create(['umamusume_id' => $this->trainee->id]);
    DeckSlot::factory()->create([
        'training_run_id' => $run2->id,
        'support_card_id' => $rentedCard->id,
        'slot_position' => 2,
        'ownership' => 'RENTED',
    ]);

    // No saved run for notRecordedCard

    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $ownedCard->id)
                    ->first()['ownership_group'] === 'OWNED'
                )
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $rentedCard->id)
                    ->first()['ownership_group'] === 'RENTED'
                )
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $notRecordedCard->id)
                    ->first()['ownership_group'] === 'NOT_RECORDED'
                )
        );
});

/**
 * @test
 * Picker payload includes in-deck position from the current draft deck
 */
it('picker payload includes in-deck position from draft deck', function (): void {
    $card1 = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Card One', 'release_global' => '2025-06-26']);
    $card2 = SupportCard::factory()->create(['support_id' => 1002, 'char_name' => 'Card Two', 'release_global' => '2025-06-26']);
    $card3 = SupportCard::factory()->create(['support_id' => 1003, 'char_name' => 'Card Three', 'release_global' => '2025-06-26']);

    // Set up draft deck with card1 at position 1, card2 at position 3
    SetupDraft::write([
        'deck' => [
            ['position' => 1, 'support_card_id' => $card1->id, 'ownership' => 'OWNED'],
            ['position' => 2, 'support_card_id' => null, 'ownership' => null],
            ['position' => 3, 'support_card_id' => $card2->id, 'ownership' => 'RENTED'],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null],
            ['position' => 6, 'support_card_id' => null, 'ownership' => null],
        ],
    ]);

    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $card1->id)
                    ->first()['in_deck_position'] === 1
                )
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $card2->id)
                    ->first()['in_deck_position'] === 3
                )
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $card3->id)
                    ->first()['in_deck_position'] === null
                )
        );
});

/**
 * @test
 * Ownership group and in-deck position are computed from different sources:
 * a card in the draft deck but not in any saved run must be in-deck AND in NOT_RECORDED group
 */
it('card in draft deck but not in saved runs shows in-deck position and NOT_RECORDED ownership', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Draft Only Card', 'release_global' => '2025-06-26']);

    // Put card in draft deck (position 2)
    SetupDraft::write([
        'deck' => [
            ['position' => 1, 'support_card_id' => null, 'ownership' => null],
            ['position' => 2, 'support_card_id' => $card->id, 'ownership' => 'OWNED'],
            ['position' => 3, 'support_card_id' => null, 'ownership' => null],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null],
            ['position' => 6, 'support_card_id' => null, 'ownership' => null],
        ],
    ]);

    // No saved runs for this card

    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('picker.options', function ($options) use ($card) {
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                    $opt = collect($options)->where('id', $card->id)->first();
                    return $opt['in_deck_position'] === 2
                        && $opt['ownership_group'] === 'NOT_RECORDED';
                })
        );
});

/**
 * @test
 * Ownership group precedence: OWNED > RENTED > NOT_RECORDED
 * If a card has both OWNED and RENTED in different saved runs, it shows as OWNED
 */
it('ownership group precedence: OWNED overrides RENTED', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Both Card', 'release_global' => '2025-06-26']);

    // Run 1: RENTED
    // @phpstan-ignore-next-line property.notFound - Pest injects $this->trainee via beforeEach closure binding
    $run1 = TrainingRun::factory()->create(['umamusume_id' => $this->trainee->id]);
    DeckSlot::factory()->create([
        'training_run_id' => $run1->id,
        'support_card_id' => $card->id,
        'slot_position' => 1,
        'ownership' => 'RENTED',
    ]);

    // Run 2: OWNED (same card, different run)
    // @phpstan-ignore-next-line property.notFound - Pest injects $this->trainee via beforeEach closure binding
    $run2 = TrainingRun::factory()->create(['umamusume_id' => $this->trainee->id]);
    DeckSlot::factory()->create([
        'training_run_id' => $run2->id,
        'support_card_id' => $card->id,
        'slot_position' => 2,
        'ownership' => 'OWNED',
    ]);

    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $card->id)
                    ->first()['ownership_group'] === 'OWNED'
                )
        );
});

/**
 * @test
 * Picker payload includes type_match boolean and slot_type string
 */
it('picker payload includes type match marker for current slot', function (): void {
    $speedCard = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Speed Card', 'type' => 'speed']);
    $staminaCard = SupportCard::factory()->create(['support_id' => 1002, 'char_name' => 'Stamina Card', 'type' => 'stamina']);

    // Fill slot 1 (Speed slot) - the picker should show type_match for speed cards
    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck', ['slot' => 1]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('picker.options', function ($options) use ($speedCard, $staminaCard) {
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                    $speed = collect($options)->where('id', $speedCard->id)->first();
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                    $stamina = collect($options)->where('id', $staminaCard->id)->first();
                    return $speed['type_match'] === true
                        && $speed['slot_type'] === 'speed'
                        && $stamina['type_match'] === false
                        && $stamina['slot_type'] === 'speed';
                })
        );
});

/**
 * @test
 * Store-level duplicate handling unchanged (D6 safety net)
 * The picker payload keeps an in-deck card visible so the Replace flow can name it
 */
it('picker payload keeps an in-deck card visible so the Replace flow can name it', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Shared Card', 'release_global' => '2025-06-26']);

    // Put card in draft deck at position 1
    SetupDraft::write([
        'deck' => [
            ['position' => 1, 'support_card_id' => $card->id, 'ownership' => 'OWNED'],
            ['position' => 2, 'support_card_id' => null, 'ownership' => null],
            ['position' => 3, 'support_card_id' => null, 'ownership' => null],
            ['position' => 4, 'support_card_id' => null, 'ownership' => null],
            ['position' => 5, 'support_card_id' => null, 'ownership' => null],
            ['position' => 6, 'support_card_id' => null, 'ownership' => null],
        ],
    ]);

    // Request picker for slot 2 - the card should still appear as an option
    // (with in_deck_position = 1, but still selectable for Replace)
    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck', ['slot' => 2]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                ->where('picker.options', fn ($options) => collect($options)
                    ->where('id', $card->id)
                    ->first() !== null
                )
        );
});

/**
 * @test
 * Not-recorded card lands in NOT_RECORDED group and is never treated as a value
 * (i.e., ownership_group is NOT_RECORDED, not null or empty)
 */
it('not-recorded card has explicit NOT_RECORDED ownership group', function (): void {
    $card = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'No History Card', 'release_global' => '2025-06-26']);

    // No saved runs, no draft deck entry

    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('picker.options', function ($options) use ($card) {
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                    $opt = collect($options)->where('id', $card->id)->first();
                    return $opt['ownership_group'] === 'NOT_RECORDED'
                        && $opt['in_deck_position'] === null;
                })
        );
});

/**
 * @test
 * Picker filters by slot type when requested
 */
it('picker filters options by slot type', function (): void {
    $speedCard = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Speed Card', 'type' => 'speed', 'release_global' => '2025-06-26']);
    $staminaCard = SupportCard::factory()->create(['support_id' => 1002, 'char_name' => 'Stamina Card', 'type' => 'stamina', 'release_global' => '2025-06-26']);
    $powerCard = SupportCard::factory()->create(['support_id' => 1003, 'char_name' => 'Power Card', 'type' => 'power', 'release_global' => '2025-06-26']);

    // Request picker for slot 2 (Stamina) with type filter
    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $this->get(route('career.deck', ['slot' => 2, 'type' => 'stamina']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Career/DeckSelect')
                ->where('picker.type', 'stamina')
                ->where('picker.options', function ($options) use ($speedCard, $staminaCard, $powerCard) {
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                    $ids = collect($options)->pluck('id')->all();
                    return in_array($staminaCard->id, $ids)
                        && !in_array($speedCard->id, $ids)
                        && !in_array($powerCard->id, $ids);
                })
        );
});

/**
 * @test
 * Picker search filters by name/epithet
 */
it('picker filters options by search query', function (): void {
    $card1 = SupportCard::factory()->create(['support_id' => 1001, 'char_name' => 'Tokai', 'title_en' => 'Dream', 'release_global' => '2025-06-26']);
    $card2 = SupportCard::factory()->create(['support_id' => 1002, 'char_name' => 'Agnes', 'title_en' => 'Tachyon', 'release_global' => '2025-06-26']);

    // @phpstan-ignore-next-line method.notFound - Pest's TestCall has magic get() via __call
    $response = $this->get(route('career.deck', ['q' => 'Tokai']));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Career/DeckSelect')
            ->where('picker.query', 'Tokai')
            ->where('picker.options', function ($options) {
    // @phpstan-ignore-next-line argument.templateType - collect() template types not resolvable in closure
                $names = collect($options)->pluck('name')->all();
                return in_array('Tokai Dream', $names)
                    && !in_array('Agnes Tachyon', $names);
            })
    );
});