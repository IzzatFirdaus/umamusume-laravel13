<?php

declare(strict_types=1);

use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Models\TrainingRun;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The deck builder, `/training-runs/{run}/deck` (SCREEN-007, the `Support/Builder` page).
 *
 * These assert the props the page receives. The rendered copy, the keyboard replacement path, the
 * 44px sweep and the analysis labels are asserted against the rendered DOM in
 * `tests/browser/support-deck.spec.ts`; the write itself was cited here to `RunDeckTest`, a file `tests/`
 * has never held, and one case now pins it for real (`it writes the flag this screen posts onto the slot
 * row and reads it back`), with the no-flag sibling caller in `CareerLegacyDeckStepsTest`.
 *
 * The three claims the brief makes that no earlier test covers are the six slots, the ownership flag
 * and the seven types, so those carry their own cases. Everything else is about not lying: the
 * Scenario Link badge is derived or it is `N/A`, the analysis totals only what the export lets it
 * total, and the recommended replacement stays unbuilt.
 */

function builderRun(array $attributes = []): TrainingRun
{
    return TrainingRun::factory()->create(['scenario' => 'ura_finale', ...$attributes]);
}

// ── the six slots ──────────────────────────────────────────────────────────

it('renders six slots in order, with the sixth named Friends whatever card sits in it', function (): void {
    $run = builderRun();
    $speed = SupportCard::factory()->speed()->create();
    DeckSlot::factory()->friendSlot()->create(['training_run_id' => $run->id, 'support_card_id' => $speed->id]);

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->has('slots', 6)
            ->where('slots', fn (Collection $slots): bool => $slots->pluck('position')->all() === [1, 2, 3, 4, 5, 6])
            ->where('slots.0.label', 'Slot 1')
            ->where('slots.4.label', 'Slot 5')
            ->where('slots.5.label', 'Slot 6 · Friends')
            // The role belongs to the position, not to the card parked in it (ADR-0014 correction 1).
            ->where('slots.5.is_friend', true)
            ->where('slots.0.is_friend', false)
            ->where('slots.5.card.type_label', 'Speed'));
});

// ── the ownership flag ─────────────────────────────────────────────────────

it('gives every slot an ownership flag a Trainer can read and change', function (): void {
    $run = builderRun();
    $friend = SupportCard::factory()->create();
    DeckSlot::factory()->atPosition(6)->create([
        'training_run_id' => $run->id,
        'support_card_id' => $friend->id,
        'ownership' => 'RENTED',
    ]);

    // The flag is the run's own record now (`ADR-0023`, D3), so a slot nobody has said anything about
    // reads as an absence rather than as a defaulted `Owned`, and the control is per slot: no slot's
    // flag is computed from its position.
    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('slots.5.ownership', 'RENTED')
            ->where('slots.0.ownership', null));
});

it('states on every deck surface that the flag is held by a column and carried onto the run', function (): void {
    // R2-06. `ADR-0023` (D3) gave `deck_slots` its nullable `ownership` column and both writers began
    // recording it, but three shipped sentences still said the opposite: the wizard's step 5 and the
    // Preflight contract each printed "The deck table has no column for it yet", and the slot
    // component's own docblock still called the value client-side. A Trainer reading step 5 is told the
    // flag will be lost, then lands on a career whose slots hold it. Copy is the defect, so copy is
    // what this reads, in every source that can print it (`AGENTS.md` §2: code wins, the rule is stale).
    $surfaces = [
        'wizard step 5' => resource_path('js/pages/Career/DeckSelect.vue'),
        'Preflight step 6' => resource_path('js/pages/Career/Preflight.vue'),
        'run-scoped builder' => resource_path('js/pages/Support/Builder.vue'),
        'the slot component' => resource_path('js/components/support/SupportSlot.vue'),
    ];

    foreach ($surfaces as $name => $path) {
        $source = (string) file_get_contents($path);

        // The retired claim, in any of the shapes it was written. The sweep alone would pass on a
        // pattern matching nothing, so the three shipped sentences below are the positive half.
        expect($source, $name)->not->toMatch('/no column for it|has no ownership column|stores no field|client-side only/');
    }

    // And each screen says where the flag actually goes, in words the write path honours: the draft keeps
    // it through setup, `PreflightController::store()` writes it onto the slot row, and the run screen
    // reads it back from the same column.
    expect((string) file_get_contents($surfaces['wizard step 5']))
        ->toMatch('/owned or rented flag is kept in this setup draft, and Preflight writes it onto the run/s')
        ->and((string) file_get_contents($surfaces['Preflight step 6']))
        ->toMatch('/owned or rented flag is in this setup draft, and starting the career writes it/s')
        ->and((string) file_get_contents($surfaces['run-scoped builder']))
        ->toMatch('/owned or rented flag is saved with the deck/');
});

it('writes the flag this screen posts onto the slot row and reads it back', function (): void {
    // The write half of the sentence the run screen prints: "saved with the deck, one value per slot,
    // and stays on the run after a reload". Nothing posted a flag through `runs.deck.sync` before this
    // case, which is how three comments in the write path still claim the run panel sends none and the
    // table has nowhere to put it. The payload decides what a surface does, so the payload is pinned.
    $run = builderRun();
    $first = SupportCard::factory()->create();
    $second = SupportCard::factory()->create();
    DeckSlot::factory()->atPosition(1)->create([
        'training_run_id' => $run->id,
        'support_card_id' => $first->id,
        'ownership' => 'OWNED',
    ]);

    test()->post(route('runs.deck.sync', $run), [
        'deck' => [
            1 => ['support_card_id' => (string) $first->id, 'ownership' => 'RENTED'],
            2 => ['support_card_id' => (string) $second->id, 'ownership' => 'OWNED'],
            3 => ['support_card_id' => '', 'ownership' => ''],
        ],
    ])->assertSessionHasNoErrors();

    expect(DeckSlot::query()->where('training_run_id', $run->id)->orderBy('slot_position')->pluck('ownership')->all())
        ->toBe(['RENTED', 'OWNED']);

    // Read back through the screen's own reader rather than the table.
    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots.0.ownership', 'RENTED')
            ->where('slots.1.ownership', 'OWNED')
            ->where('slots.2.ownership', null));
});

// ── the seven types ────────────────────────────────────────────────────────

it('offers the seven support types, each under the client\'s own word', function (): void {
    $run = builderRun();

    // `intelligence` and `friend` are the export's keys; the Global client prints Wit and Pal.
    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->has('types', 7)
            ->where('types.0.key', 'speed')
            ->where('types.0.label', 'Speed')
            ->where('types.4.key', 'intelligence')
            ->where('types.4.label', 'Wit')
            ->where('types.5.key', 'friend')
            ->where('types.5.label', 'Pal')
            ->where('types.6.key', 'group')
            ->where('types.6.label', 'Group'));
});

// ── the Scenario Link badge ────────────────────────────────────────────────

it('badges a linked character on the scenario, derived on read and never stored', function (): void {
    $run = builderRun();
    // URA Finale's only linked character is Aoi Kiryuin.
    $linked = SupportCard::factory()->create(['char_name' => 'Aoi Kiryuin', 'char_id' => 9004]);
    $other = SupportCard::factory()->create(['char_name' => 'Special Week']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $linked->id]);
    DeckSlot::factory()->atPosition(2)->create(['training_run_id' => $run->id, 'support_card_id' => $other->id]);

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('slots.0.card.scenario_link', 'linked')
            ->where('slots.0.card.scenario_link_note', null)
            ->where('slots.1.card.scenario_link', 'not_linked'));
});

it('names the absence rather than badging a card on a run that names no scenario', function (): void {
    $card = SupportCard::factory()->create(['char_name' => 'Aoi Kiryuin']);
    $run = builderRun(['scenario' => null]);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    // A `false` from `isScenarioLink()` here means "no list to compare against", not "not linked", and
    // the two would read identically on the badge.
    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('run.has_scenario', false)
            ->where('run.scenario_label', null)
            ->where('slots.0.card.scenario_link', 'unknown')
            ->where('slots.0.card.scenario_link_note', 'This run names no scenario, so there is no linked list to check this card against.'));
});

it('names the absence for a card the source stores no character name for', function (): void {
    $card = SupportCard::factory()->create(['char_name' => null, 'name_ja' => 'スペシャルウィーク']);
    $run = builderRun();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('slots.0.card.scenario_link', 'unknown')
            ->where('slots.0.card.scenario_link_note', 'The source stores no character name for this card, so there is nothing to match against the linked list.'));
});

it('says a card is not linked on a scenario whose linked list is empty, which is a stated fact', function (): void {
    $card = SupportCard::factory()->create(['char_name' => 'Special Week']);
    $run = builderRun(['scenario' => 'trackblazer']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    // Trackblazer declares `'scenario_links' => []`, so "no card can be a link here" is an answer the
    // config gives, not a gap.
    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('slots.0.card.scenario_link', 'not_linked'));
});

// ── the six selections survive navigation ──────────────────────────────────

it('carries the six picks through a filter change, which is why they ride in the query string', function (): void {
    $run = builderRun();
    $first = SupportCard::factory()->speed()->create();
    $second = SupportCard::factory()->stamina()->create();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $first->id]);
    DeckSlot::factory()->atPosition(3)->create(['training_run_id' => $run->id, 'support_card_id' => $second->id]);

    // The Trainer has replaced slot two in the URL and narrowed the picker. The other five picks have
    // to be exactly where they were, or a filter click silently empties a half-built deck.
    test()->get("/training-runs/{$run->id}/deck?type=power&deck[2]={$first->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('slots.0.selected', (string) $first->id)
            ->where('slots.1.selected', (string) $first->id)
            ->where('slots.2.selected', (string) $second->id)
            ->where('picker.type', 'power'));
});

it('keeps the six picks a Trainer made when the server refuses the deck', function (): void {
    $run = builderRun();
    $good = SupportCard::factory()->create(['char_name' => 'Keep Me']);

    // The same one-round-trip shape `RunDeckTest` uses: `old()` is only read on the redirect, which is
    // why the request carries a Referer for `back()` to find.
    test()->followingRedirects()
        ->post(
            "/training-runs/{$run->id}/deck",
            ['deck' => [1 => ['support_card_id' => $good->id], 2 => ['support_card_id' => 999999]]],
            ['HTTP_REFERER' => url("/training-runs/{$run->id}/deck")]
        )
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('slots.0.selected', (string) $good->id)
            ->where('slots.0.card.name', 'Keep Me [Tracen Academy]')
            ->where('errors', fn (Collection $errors): bool => str_contains($errors['deck.2.support_card_id'] ?? '', 'That card is not in the catalogue')));
});

it('posts to the write the run screen already uses', function (): void {
    $run = builderRun();

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('action', route('runs.deck.sync', $run)));
});

// ── the picker ─────────────────────────────────────────────────────────────

it('offers the Global releases plus any card this run already uses', function (): void {
    $run = builderRun();
    SupportCard::factory()->create(['char_name' => 'Global Card']);
    $jpOnly = SupportCard::factory()->jpOnly()->create(['char_name' => 'Japan Only Card']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $jpOnly->id]);

    $labels = collect(test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Support/Builder')->has('picker.page.data', 2))
        ->inertiaProps('picker')['page']['data'])->pluck('name');

    // Otherwise a Trainer logging an older deck sees their own card below the list and cannot re-select
    // it, so re-saving drops it with no way to put it back.
    expect($labels->contains('Global Card [Tracen Academy]'))->toBeTrue()
        ->and($labels->contains('Japan Only Card [Tracen Academy]'))->toBeTrue();
});

it('paginates the picker at twenty-five cards, as the catalog does', function (): void {
    $run = builderRun();

    foreach (range(1, 26) as $index) {
        SupportCard::factory()->create(['char_name' => sprintf('Trainee %02d', $index)]);
    }

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->has('picker.page.data', 25)
            ->where('picker.page.total', 26)
            ->where('picker.offered', 26));
});

it('narrows the picker by type, by rarity and by name', function (): void {
    $run = builderRun();
    SupportCard::factory()->ssr()->speed()->create(['char_name' => 'Silky Syder']);
    SupportCard::factory()->sr()->stamina()->create(['char_name' => 'Zenno Rob Roy']);

    foreach ([
        'type=speed' => 'Silky Syder',
        'rarity=2' => 'Zenno Rob Roy',
        'query=Zenno' => 'Zenno Rob Roy',
    ] as $query => $expected) {
        test()->get("/training-runs/{$run->id}/deck?{$query}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Support/Builder')
                ->has('picker.page.data', 1)
                ->where('picker.page.data.0.name', $expected.' [Tracen Academy]'));
    }
});

it('keeps a card the run already uses reachable through the availability facet', function (): void {
    $run = builderRun();
    $jpOnly = SupportCard::factory()->jpOnly()->create(['char_name' => 'Forever Young', 'title_en' => '[New Order]']);
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $jpOnly->id]);

    // The picker offers the Global releases plus what the run already holds, so a JP-only card is in it
    // only in the second case, and the availability facet is what lets a Trainer find it there.
    test()->get("/training-runs/{$run->id}/deck?status=JP-only")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->has('picker.page.data', 1)
            ->where('picker.page.data.0.name', 'Forever Young [New Order]'));
});

it('names the ask rather than showing an empty frame when a facet reaches no card', function (): void {
    $run = builderRun();
    SupportCard::factory()->jpOnly()->create(['char_name' => 'Only JP Card']);

    // The card is not offered at all: a JP-only card is reachable only once a run already holds it, so
    // the facet is real data and the honest answer here is a named absence rather than a row.
    test()->get("/training-runs/{$run->id}/deck?status=JP-only")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->has('picker.page.data', 0)
            ->where('picker.offered', 0)
            ->where('picker.askedFor', 'availability JP-only'));
});

it('refuses a facet value the columns cannot hold rather than answering with the whole catalogue', function (): void {
    $run = builderRun();
    SupportCard::factory()->create();

    test()->get("/training-runs/{$run->id}/deck?type=turbo")
        ->assertStatus(302)
        ->assertRedirect(route('runs.deck', $run))
        ->assertSessionHasErrors('type');
});

it('names the ask when a filter matches nothing', function (): void {
    $run = builderRun();
    SupportCard::factory()->speed()->create(['char_name' => 'Only Speed']);

    test()->get("/training-runs/{$run->id}/deck?type=stamina")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->has('picker.page.data', 0)
            ->where('picker.offered', 1)
            ->where('picker.askedFor', 'type Stamina'));
});

it('names the slot a replacement is filling so the picker writes to it', function (): void {
    $run = builderRun();

    test()->get("/training-runs/{$run->id}/deck?slot=4")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Support/Builder')->where('picker.fillingSlot', 4));

    // An out-of-range slot falls back to the first rather than writing to a position the deck refuses.
    test()->get("/training-runs/{$run->id}/deck?slot=9")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Support/Builder')->where('picker.fillingSlot', 1));
});

// ── the deck analysis ──────────────────────────────────────────────────────

it('computes the six categories from the anchors the source states and labels the arithmetic', function (): void {
    // Speed Bonus is one of the thirty-one effects that declare no combining mode at all, which is what
    // makes it summable across cards. The factory default is `mult`, so the state is named here.
    SupportEffect::factory()->undeclared()->create(['effect_id' => 3, 'name_en' => 'Speed Bonus']);
    $card = SupportCard::factory()->create([
        'char_name' => 'Quiet Star',
        'effects' => [[3, -1, -1, -1, -1, -1, -1, 100, -1, -1, 146, -1]],
    ]);
    $run = builderRun();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('analysis.covered', 1)
            ->where('analysis.categories', fn (Collection $categories): bool => $categories->keys()->all() === [
                'training_power', 'early_run', 'race_bonus', 'safety', 'events', 'skills',
            ])
            ->where('analysis.categories.training_power.label', 'Training power')
            // The number is the card's own highest stated anchor, and the total is the integer this
            // screen added to it, which is the one figure that is Calculated rather than stated.
            ->where('analysis.categories.training_power.effects.0.name', 'Speed Bonus')
            ->where('analysis.categories.training_power.effects.0.cards.0.display', '146')
            ->where('analysis.categories.training_power.effects.0.total.value', 146)
            ->where('analysis.categories.training_power.effects.0.total.basis', 'summed')
            // A category the deck carries nothing in is blank, not a wall of zeros.
            ->where('analysis.categories.events.blank', true)
            ->where('analysis.categories.events.effects', []));
});

it('refuses to total an effect the export marks multiplicative', function (): void {
    SupportEffect::factory()->create(['effect_id' => 1, 'name_en' => 'Friendship Bonus', 'symbol' => 'percent', 'calc' => 'mult']);
    $first = SupportCard::factory()->create(['char_name' => 'Aoi Kiryuin', 'effects' => [[1, -1, -1, -1, -1, -1, 18, -1, -1, -1, -1, -1]]]);
    $second = SupportCard::factory()->create(['char_name' => 'Special Week', 'effects' => [[1, -1, -1, -1, -1, -1, 20, -1, -1, -1, -1, -1]]]);
    $run = builderRun();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $first->id]);
    DeckSlot::factory()->atPosition(2)->create(['training_run_id' => $run->id, 'support_card_id' => $second->id]);

    // 38% is not a figure the client states for two cards held together (§1.4.8), so the two values
    // are listed and the mode says why there is no total.
    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('analysis.categories.training_power.effects.0.mode', 'multiplicative')
            ->where('analysis.categories.training_power.effects.0.total', null)
            ->where('analysis.categories.training_power.effects.0.cards', fn (Collection $cards): bool => $cards->count() === 2));
});

it('states the empty deck as six blank categories rather than six zeros', function (): void {
    $run = builderRun();

    test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Support/Builder')
            ->where('analysis.covered', 0)
            ->where('analysis.weaknesses', fn (Collection $lines): bool => $lines->count() === 6)
            ->where('analysis.strengths', []));
});

it('carries no score and no bar anywhere in the analysis', function (): void {
    SupportEffect::factory()->undeclared()->create(['effect_id' => 15, 'name_en' => 'Race Bonus', 'symbol' => 'percent']);
    $card = SupportCard::factory()->create(['effects' => [[15, -1, -1, -1, -1, -1, 8, -1, -1, -1, -1, -1]]]);
    $run = builderRun();
    DeckSlot::factory()->atPosition(1)->create(['training_run_id' => $run->id, 'support_card_id' => $card->id]);

    $analysis = test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->inertiaProps('analysis');

    // The brief sketches a five-row bar chart. A bar without a number beside it is a score wearing a
    // chart costume, so the shape that ships is numbers and their labels, and the two keys that would
    // carry a score or a normalised percentage are absent rather than null. The card carries a Race
    // Bonus and nothing else, so one category has a strength and the other five are weaknesses.
    expect($analysis)->not->toHaveKey('score')
        ->and($analysis)->not->toHaveKey('rating')
        ->and($analysis)->not->toHaveKey('percent')
        ->and($analysis)->not->toHaveKey('bar')
        ->and($analysis['strengths'])->toBe(['Race bonus: 1 of the 1 cards carry an effect here.']);
});

// ── absences ───────────────────────────────────────────────────────────────

it('leaves the recommended replacement unbuilt and names it', function (): void {
    $run = builderRun();

    // The brief asks for it; the corpus has no replacement to recommend from, and a "recommended
    // replacement" computed here would be the score this screen is forbidden to print.
    $props = test()->get("/training-runs/{$run->id}/deck")
        ->assertOk()
        ->inertiaProps();

    expect($props)->not->toHaveKey('recommendation')
        ->and($props)->not->toHaveKey('recommended_replacement');
});

it('refuses a run that does not exist', function (): void {
    test()->get('/training-runs/999999/deck')->assertNotFound();
});
