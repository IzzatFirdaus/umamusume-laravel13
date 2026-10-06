<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StoreDraftDeckRequest;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Services\Career\SetupDraft;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\DeckAnalysis;
use App\Services\SupportCardEffects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\ViewErrorBag;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Support Deck Select, `SCR-CAR-009` (PRD FR-A-4, `ADR-0020` §1, under `ADR-0014`). Step 5 of the setup
 * wizard.
 *
 * **The deck is enterable before a run exists**, which is the reason this step exists: the deck builder
 * that does ship is run-scoped (`/training-runs/{run}/deck` writes `deck_slots`), and with no run there
 * was nowhere to put the six positions. This step reads and writes the same session draft as steps 1 to 4
 * (`SetupDraft`'s class docblock carries the reasoning) and Preflight (D7) creates the rows once the run
 * exists.
 *
 * **Every choice saves, and that is deliberate.** The run-scoped builder keeps its six picks in the query
 * string because a run screen has a draft in flight; a wizard step has a session bag that is the draft, so
 * equipping, renting and clearing are each one write and one read-back. Nothing is held in a component that
 * a reload could lose, which is also why the step survives a visit to step 4 and back with its values
 * intact.
 *
 * **What the flag is and is not.** `deck_slots` has no ownership column and inventing one is the owner's
 * call (`ADR-0014`), so the run-scoped screen prints the one value it can read and says so in words. A
 * session key needs no migration, so this step carries OWNED and RENTED per slot, refuses a save that
 * equips a card without saying which it is, and leaves the flag absent on a slot with no card: "owned or
 * rented" describes nothing when nothing sits in the position.
 *
 * **Analysis is the counts `DeckAnalysis` already makes, and no more.** The six cards' effects are the
 * anchors `SupportCardEffects::atCap()` resolves at each card's highest stated level, and the category
 * totals are those anchors added or listed as the export's own `calc` says. Nothing here ranks a deck,
 * scores it, or proposes a replacement (`ADR-0020` §3, PRD FR-G-4): the sentences the component prints are
 * restatements of the counts beside them.
 */
class DeckSelectController extends Controller
{
    /**
     * `GET /career/setup/deck`.
     *
     * The picker's offered set is the Global releases plus any card the draft already holds, the same rule
     * `TrainingRunController::deckPayload()` applies to a run, so a Trainer who entered a card this release
     * window does not cover is never shown a slot they cannot re-select.
     */
    public function show(Request $request): Response
    {
        $dictionary = SupportCardEffects::dictionary();
        $scenarioKey = $this->scenarioKey();

        /** @var list<array{position: int, support_card_id: int|null, ownership: string|null}> $draft */
        $draft = SetupDraft::deck() ?? $this->emptySlots();
        $selected = array_values(array_filter(array_column($draft, 'support_card_id')));
        $cards = SupportCard::query()
            ->whereIn('id', $selected)
            ->get()
            ->keyBy('id');

        return Inertia::render('Career/DeckSelect', [
            'slots' => array_map(
                fn (array $slot): array => [
                    'position' => $slot['position'],
                    'label' => $this->slotLabel($slot['position']),
                    // The role belongs to the position, not to the card parked in it (`ADR-0014`
                    // correction 1): position six is the friend slot whatever card sits there.
                    'is_friend' => $slot['position'] === DeckSlot::MAX_POSITION,
                    'selected' => (string) ($slot['support_card_id'] ?? ''),
                    'ownership' => $slot['ownership'],
                    'card' => $this->cardRow($cards->get((int) $slot['support_card_id']), $scenarioKey, $dictionary),
                ],
                $draft,
            ),
            'types' => array_map(
                static fn (string $type): array => ['key' => $type, 'label' => SupportCard::typeWord($type)],
                SupportCard::TYPES,
            ),
            'picker' => $this->picker($request, $selected),
            'scenarioLabel' => SetupDraft::scenarioLabel(),
            'scenarioPending' => SetupDraft::read()['scenario'] === null,
            'analysis' => DeckAnalysis::build($this->analysisInput($cards, $dictionary)),
            // The one write this step makes, stated as a URL the page never has to resolve.
            'action' => route('career.deck.store'),
        ]);
    }

    /**
     * `PUT /career/setup/deck`.
     *
     * The whole six-slot deck is replaced, because a deck is a set of positions and a merge would leave a
     * card the Trainer just cleared sitting in the draft while nothing on screen still claimed it — the same
     * reason `LegacyController::update()` and `syncDeck()` replace rather than patch.
     */
    public function store(StoreDraftDeckRequest $request): RedirectResponse
    {
        SetupDraft::write(['deck' => $request->payload()]);

        // Back to this step, and to the slot the Trainer was working on, so a sixth equip does not send
        // them back to the top of the list. An out-of-range slot is a pasted URL rather than a mistake, so
        // it falls back to the step's own default instead of refusing the page.
        $slot = (int) $request->input('deck_slot');

        return redirect()
            ->route('career.deck', in_array($slot, DeckSlot::POSITIONS, true) ? ['deck_slot' => $slot] : [])
            ->with('status', 'Deck saved.');
    }

    /**
     * The picker: the offered set, narrowed by the two facets this step offers, and the slot it writes to.
     *
     * The labels carry the rarity word and the type word so one line per card says what it is, the way
     * `deckPayload()`'s options do, and the type filter narrows server-side so a Trainer with 559 catalogue
     * rows is not handed 559 option nodes to arrow through.
     *
     * @param  list<int>  $selected
     * @return array<string, mixed>
     */
    private function picker(Request $request, array $selected): array
    {
        $type = is_string($request->query('type')) && $request->query('type') !== ''
            ? $request->query('type')
            : null;
        $search = is_string($request->query('q')) && $request->query('q') !== ''
            ? $request->query('q')
            : null;

        $query = SupportCard::query()
            ->where(static function ($inner) use ($selected): void {
                $inner->whereNotNull('release_global');

                if ($selected !== []) {
                    $inner->orWhereIn('id', $selected);
                }
            })
            ->when($type !== null, static fn ($inner) => $inner->where('type', $type))
            ->when($search !== null, static fn ($inner) => $inner->where(
                static fn ($sub) => $sub->where('char_name', 'like', '%'.addcslashes($search, '%_\\').'%')
                    ->orWhere('title_en', 'like', '%'.addcslashes($search, '%_\\').'%')
            ))
            // The same deterministic order the catalogue and the run builder use, so a filter change cannot
            // hand back a different half of the list between two loads (KI-41's shape).
            ->orderBy('type')
            ->orderBy('char_name')
            ->orderBy('title_en');

        return [
            'options' => $query->get()
                ->map(static fn (SupportCard $card): array => [
                    'id' => $card->id,
                    'name' => $card->displayName(),
                    'label' => $card->displayName().' · '.$card->rarityWord().' '.$card->typeLabel(),
                    'type' => $card->type,
                    'type_label' => $card->typeLabel(),
                    'rarity_word' => $card->rarityWord(),
                ])
                ->values()
                ->all(),
            'type' => $type,
            'query' => $search,
            // Which slot the equip buttons write to. Resolved server-side so a refused save reopens the slot
            // the fault is on, the way `openDeckSlot()` does for the run screen: a client ref could not
            // survive the redirect back, which is exactly when it has to reopen.
            'fillingSlot' => $this->fillingSlot($request),
        ];
    }

    /**
     * One card as the slot row reads it, or null when the slot holds nothing.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return array<string, mixed>|null
     */
    private function cardRow(?SupportCard $card, ?string $scenarioKey, array $dictionary): ?array
    {
        if ($card === null) {
            return null;
        }

        $link = $card->scenarioLinkState(
            $scenarioKey,
            'This setup has no scenario chosen yet, so there is no linked list to check this card against.',
        );

        return [
            'id' => $card->id,
            'name' => $card->displayName(),
            'name_ja' => $card->name_ja,
            'title_ja' => $card->title_ja,
            'url' => route('support-cards.show', $card),
            'artworkURL' => app(ArtworkMirror::class)->url('support_thumb', (int) $card->support_id),
            'rarity_word' => $card->rarityWord(),
            'type' => $card->type,
            'type_label' => $card->typeLabel(),
            'scenario_link' => $link['state'],
            'scenario_link_note' => $link['note'],
            'effects' => SupportCardEffects::atCap($card, $dictionary),
        ];
    }

    /**
     * The six cards the analysis reads, in the draft's order, with their at-cap effects.
     *
     * @param  Collection<int, SupportCard>  $cards
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{card_name: string, effects: list<array<string, mixed>>}>
     */
    private function analysisInput($cards, array $dictionary): array
    {
        return $cards
            ->map(static fn (SupportCard $card): array => [
                'card_name' => $card->displayName(),
                'effects' => SupportCardEffects::atCap($card, $dictionary),
            ])
            ->values()
            ->all();
    }

    /**
     * The six positions with nothing in them, the state before this step is first saved.
     *
     * @return list<array{position: int, support_card_id: int|null, ownership: string|null}>
     */
    private function emptySlots(): array
    {
        return array_map(
            static fn (int $position): array => [
                'position' => $position,
                'support_card_id' => null,
                'ownership' => null,
            ],
            DeckSlot::POSITIONS,
        );
    }

    /**
     * The label a slot carries: `Slot 3`, or `Slot 6 · Friends` for the position the client reserves for a
     * borrowed card (`ADR-0014` correction 1), worded the same way the run builder words it.
     */
    private function slotLabel(int $position): string
    {
        return $position === DeckSlot::MAX_POSITION ? 'Slot 6 · Friends' : 'Slot '.$position;
    }

    /**
     * The slot the picker writes to: the one the Trainer last moved, else the first a refused save landed an
     * error on, else the first one still empty, else the first.
     */
    private function fillingSlot(Request $request): int
    {
        $asked = (int) $request->query('deck_slot');

        if (in_array($asked, DeckSlot::POSITIONS, true)) {
            return $asked;
        }

        /** @var mixed $bag */
        $bag = session('errors');

        if ($bag instanceof ViewErrorBag) {
            foreach (DeckSlot::POSITIONS as $position) {
                if ($bag->has("deck.{$position}.support_card_id") || $bag->has("deck.{$position}.ownership")) {
                    return $position;
                }
            }
        }

        /** @var list<array{position: int, support_card_id: int|null, ownership: string|null}> $draft */
        $draft = SetupDraft::deck() ?? $this->emptySlots();

        foreach ($draft as $slot) {
            if ($slot['support_card_id'] === null) {
                return $slot['position'];
            }
        }

        return DeckSlot::POSITIONS[0];
    }

    /**
     * The draft scenario's key, or null when the step has not chosen one.
     *
     * `TrainingRun::scenarioKey()`'s fallback to the baseline is a composition device, not a fact
     * (`SetupDraft::scenarioLabel()` says the same of its own label), so the Scenario Link derivation is
     * asked only of a scenario the Trainer actually picked. Null is the state that renders `N/A`.
     */
    private function scenarioKey(): ?string
    {
        $run = SetupDraft::planningRun();

        return $run === null || ! $run->hasScenario() ? null : $run->scenarioKey();
    }
}
