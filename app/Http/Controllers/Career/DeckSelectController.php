<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\CardRarity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StoreDraftDeckRequest;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Services\Career\SetupDraft;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\SupportCardEffects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
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
 * **What the flag is and is not.** The run records it: `deck_slots.ownership` holds one nullable value
 * per equipped slot (`ADR-0023`, D3), and Preflight carries the draft's flag onto that row when it
 * creates the run. This step keeps it in the session bag because the career has no row yet, so it
 * carries OWNED and RENTED per slot, refuses a save that
 * equips a card without saying which it is, and leaves the flag absent on a slot with no card: "owned or
 * rented" describes nothing when nothing sits in the position.
 *
 * **No analysis prop (Phase F).** `DeckAnalysis` used to ride this step as an `analysis` prop the page
 * rendered as a coverage panel; Phase E moved that panel to the run-scoped builder, so the prop, the
 * `DeckAnalysis` import and the at-cap input mapping are gone and nothing is computed twice. The step
 * still answers the two checks the draft can carry (the filled-slot count and the duplicate rule),
 * because those read the slot list rather than the effect catalogue. Nothing here ranks a deck, scores
 * it, or proposes a replacement (`ADR-0020` §3, PRD FR-G-4).
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
        SetupDraft::markVisited(5);
        $dictionary = SupportCardEffects::dictionary();
        $scenarioKey = $this->scenarioKey();

        /** @var list<array{position: int, support_card_id: int|null, ownership: string|null, card_level: int|null}> $draft */
        $draft = SetupDraft::deck() ?? $this->emptySlots();
        $selected = array_values(array_filter(array_column($draft, 'support_card_id')));
        $cards = SupportCard::query()
            ->whereIn('id', $selected)
            ->get()
            ->keyBy('id');

        return Inertia::render('Career/DeckSelect', [
            'visited' => SetupDraft::visited(),
            'saved' => SetupDraft::saved(),
            'slots' => array_map(
                fn (array $slot): array => [
                    'position' => $slot['position'],
                    'label' => $this->slotLabel($slot['position']),
                    // The role belongs to the position, not to the card parked in it (`ADR-0014`
                    // correction 1): position six is the friend slot whatever card sits there.
                    'is_friend' => $slot['position'] === DeckSlot::MAX_POSITION,
                    'selected' => (string) ($slot['support_card_id'] ?? ''),
                    'ownership' => $slot['ownership'],
                    'card_level' => $slot['card_level'] ?? null,
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
            'scenarioKey' => $scenarioKey,
            'scenarioHints' => $this->scenarioHints($scenarioKey),
            'scenarioPending' => SetupDraft::read()['scenario'] === null,
            // The one write this step makes, stated as a URL the page never has to resolve.
            'action' => route('career.deck.store'),
            // The two doors this step offers, supplied server-side so the page never spells a
            // setup URL. (AUD-20261102-005 F-06)
            'preflightUrl' => route('career.preflight'),
            'legacyUrl' => route('career.legacy'),
            // The page's own address, for the GET filter that retargets the picker.
            'selfUrl' => route('career.deck'),
        ]);
    }

    /**
     * Returns scenario-aware strategic hints for the deck builder.
     *
     * Per UMAMUSUME_REFERENCE.md §2.1, §2.2:
     * - Unity Cup: Wit cap is 1800, highlight Wit-heavy deck
     * - Trackblazer: No Race Goals, focus on Grade Point objectives
     * - Our Grand Concert: Speed cap is 1600, Lessons/Songs mechanic active
     * - URA Finale: Standard scenario, no extra systems
     *
     * @return list<string>
     */
    private function scenarioHints(?string $scenarioKey): array
    {
        if ($scenarioKey === null) {
            return [];
        }

        return match ($scenarioKey) {
            'unity_cup' => ['Wit cap is 1800. Consider a Wit-heavy deck.'],
            'trackblazer' => ['No Race Goals. Focus on Grade Point objectives.'],
            'our_grand_concert' => ['Speed cap is 1600. Lessons/Songs mechanic active.'],
            'ura_finale' => ['Standard scenario. No extra systems beyond training, racing, and objectives.'],
            default => [],
        };
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
     * Compute ownership group for a card from saved-run DeckSlot rows.
     *
     * Precedence: Owned if any saved-run slot records OWNED, else Rented if any records RENTED,
     * else Not recorded. Session draft (SetupDraft) is NOT considered — ownership groups
     * are derived from saved runs only (C6 scoping).
     *
     * @param  list<int>  $cardIds
     * @return array<int, 'OWNED'|'RENTED'|'NOT_RECORDED'>
     */
    private function ownershipGroups(array $cardIds): array
    {
        if ($cardIds === []) {
            return [];
        }

        $groups = [];

        // Check for OWNED ownership in saved runs
        $ownedIds = DB::table('deck_slots')
            ->join('training_runs', 'deck_slots.training_run_id', '=', 'training_runs.id')
            ->whereIn('deck_slots.support_card_id', $cardIds)
            ->where('deck_slots.ownership', 'OWNED')
            ->whereNotNull('training_runs.id')
            ->pluck('deck_slots.support_card_id')
            ->unique()
            ->all();

        // Check for RENTED ownership in saved runs
        $rentedIds = DB::table('deck_slots')
            ->join('training_runs', 'deck_slots.training_run_id', '=', 'training_runs.id')
            ->whereIn('deck_slots.support_card_id', $cardIds)
            ->where('deck_slots.ownership', 'RENTED')
            ->whereNotNull('training_runs.id')
            ->pluck('deck_slots.support_card_id')
            ->unique()
            ->all();

        foreach ($cardIds as $cardId) {
            if (in_array($cardId, $ownedIds, true)) {
                $groups[$cardId] = 'OWNED';
            } elseif (in_array($cardId, $rentedIds, true)) {
                $groups[$cardId] = 'RENTED';
            } else {
                $groups[$cardId] = 'NOT_RECORDED';
            }
        }

        return $groups;
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
            ->when($search !== null, static function ($inner) use ($search): void {
                // The row prints `Name [Epithet]`, which is `char_name` beside `title_en`, so a Trainer
                // who types the printed label must match. Split on whitespace and require every term to
                // hit one of the two fields (R4-21): `Tokai` lands in `char_name`, `[Dream` in `title_en`.
                foreach (preg_split('/\s+/', trim($search)) ?: [] as $term) {
                    if ($term === '') {
                        continue;
                    }

                    $inner->where(static fn ($sub) => $sub->where('char_name', 'like', '%'.addcslashes($term, '%_\\').'%')
                        ->orWhere('title_en', 'like', '%'.addcslashes($term, '%_\\').'%'));
                }
            })
            // The same deterministic order the catalogue and the run builder use, so a filter change cannot
            // hand back a different half of the list between two loads (KI-41's shape).
            ->orderBy('type')
            ->orderBy('char_name')
            ->orderBy('title_en');

        // Get ownership groups for all cards in the picker
        $allCardIds = $query->pluck('id')->all();
        $ownershipGroups = $this->ownershipGroups($allCardIds);

        // Get in-deck positions from the current draft
        $draft = SetupDraft::deck() ?? $this->emptySlots();
        $inDeckPositions = [];
        foreach ($draft as $slot) {
            if ($slot['support_card_id'] !== null) {
                $inDeckPositions[$slot['support_card_id']] = $slot['position'];
            }
        }

        // Get the slot's training type for the type-match marker
        // Slot 1 = Speed, 2 = Stamina, 3 = Power, 4 = Guts, 5 = Wit, 6 = Friend
        $slotTypeMap = [
            1 => 'speed',
            2 => 'stamina',
            3 => 'power',
            4 => 'guts',
            5 => 'intelligence',
            6 => 'friend',
        ];
        $fillingSlot = $this->fillingSlot($request);
        $slotType = $slotTypeMap[$fillingSlot] ?? null;

        return [
            'options' => $query->get()
                ->map(static fn (SupportCard $card): array => [
                    'id' => $card->id,
                    'name' => $card->displayName(),
                    // The row prints a thumbnail beside the name. `ArtworkMirror::url()` answers null
                    // when the mirror holds no file for this id, which is its normal partial answer
                    // (`ADR-0021` Decision 5) and leaves `ArtworkSlot` rendering the text-only row.
                    'artworkURL' => app(ArtworkMirror::class)->url('support_thumb', (int) $card->support_id),
                    'label' => $card->displayName().' · '.$card->rarityWord().' '.$card->typeLabel(),
                    'type' => $card->type,
                    'type_label' => $card->typeLabel(),
                    'rarity_word' => $card->rarityWord(),
                    // Extended payload for Deck Picker (Slice 4)
                    'ownership_group' => $ownershipGroups[$card->id] ?? 'NOT_RECORDED',
                    'in_deck_position' => $inDeckPositions[$card->id] ?? null,
                    'type_match' => $slotType !== null && $card->type === $slotType,
                    'slot_type' => $slotType,
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
            'ladder' => $this->levelLadder($card, $dictionary),
        ];
    }

    /**
     * What a copy of this card is worth at each limit-break tier, 0 unbroken to four breaks.
     *
     * The tier's level is `CardRarity::levelAtBreak()`, and every one of those five levels is an anchor
     * the export states (the ladder runs 1, 5, 10 … 50), so a cell is a published figure and not an
     * interpolation. Where a card states nothing above its own last anchor, `SupportCardEffects` carries
     * that anchor up, which is the same rule `atCap()` already applies.
     *
     * **This prints no break count for the Trainer's copy.** The draft stores a level and nothing else
     * (`ADR-0029` D1: a numerator), and `ADR-0014` declines the card collection, so the step shows the
     * ladder a card of this rarity has, not a claim about copies this tool has never seen.
     * (`UMAMUSUME_REFERENCE.md` §1.4.2, §1.4.7.)
     *
     * §1.4.7: the Unique Perk unlock level is NOT the card level. The perk unlocks at a set level:
     * Lv30 across the pool, Lv40 for Special Week at two limit breaks. The export does not carry this
     * value (ADR-0014), so we note the default and the known exception rather than inventing per-card data.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{breaks: int, is_maximum: bool, level: int, perk_unlock_level: int|null, effects: list<array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>, perk_note: string|null}>
     */
    private function levelLadder(SupportCard $card, array $dictionary): array
    {
        $tiers = [];

        // §1.4.7: default perk unlock level is 30; Special Week is the known exception at 40 (at 2LB).
        // The export does not carry this value (ADR-0014), so we apply the default and note the exception.
        $perkUnlockLevel = $card->char_name === 'Special Week' ? 40 : 30;

        for ($breaks = 0; $breaks <= CardRarity::MAX_BREAKS; $breaks++) {
            $level = $card->rarity->levelAtBreak($breaks);

            $tiers[] = [
                'breaks' => $breaks,
                'is_maximum' => $breaks === CardRarity::MAX_BREAKS,
                'level' => $level,
                'perk_unlock_level' => $level >= $perkUnlockLevel ? $perkUnlockLevel : null,
                'effects' => SupportCardEffects::atLevel($card, $level, $dictionary),
                'perk_note' => $level >= $perkUnlockLevel
                    ? 'Unique Perk unlocks at Lv'.$perkUnlockLevel.'.'
                    : 'Unique Perk not yet unlocked at this level (unlocks at Lv'.$perkUnlockLevel.').',
            ];
        }

        return $tiers;
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
                'card_level' => null,
            ],
            DeckSlot::POSITIONS,
        );
    }

    /**
     * The label a slot carries: `Slot 3`, or `Slot 6 · Friends` for the position the client reserves for a
     * borrowed card (`ADR-0014` correction 1). The words themselves are `DeckSlot::positionLabel()`'s, which
     * Preflight's summary reads too.
     */
    private function slotLabel(int $position): string
    {
        return DeckSlot::positionLabel($position);
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
