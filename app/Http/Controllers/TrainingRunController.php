<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ImportHistoricalRun;
use App\Enums\CardRarity;
use App\Enums\MoodTier;
use App\Enums\PerformanceType;
use App\Enums\ReleaseStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Enums\TurnEventType;
use App\Http\Requests\DeckPickerSearchRequest;
use App\Http\Requests\ImportHistoricalRunRequest;
use App\Http\Requests\StoreBuildTargetRequest;
use App\Http\Requests\StoreDeckRequest;
use App\Http\Requests\StoreRaceEntryRequest;
use App\Http\Requests\StoreRunSkillRequest;
use App\Http\Requests\StoreShopPurchaseRequest;
use App\Http\Requests\StoreTrainingRunRequest;
use App\Http\Requests\StoreTurnEntryRequest;
use App\Http\Resources\TrainingRunResource;
use App\Models\CharacterCard;
use App\Models\DeckSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvents\PerformancePayload;
use App\Models\TurnEvents\ShopPurchasePayload;
use App\Models\Umamusume;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\DeckAnalysis;
use App\Services\PageSize;
use App\Services\SupportCardEffects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trainer-owned run CRUD: runs, their turns, their skill states, and export
 * (PRD US-3/US-4/US-6, FR-C-4/C-5). Validation lives in the Form Requests;
 * deletion cascades to turns and skill rows. No engine data is touched here.
 */
class TrainingRunController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Runs/Index', [
            'runs' => TrainingRun::with('umamusume')->latest()->paginate(25)
                ->through(static fn (TrainingRun $run): array => [
                    'id' => $run->id,
                    'name' => $run->umamusume->name,
                    // The scenario key is a storage value; the label is config's, so the payload
                    // never carries the slug (hasScenario() is the model's one ruling: a blank
                    // scenario is no scenario, not a name to print).
                    'scenario_label' => $run->hasScenario()
                        ? (config('scenarios.scenarios.'.$run->scenario.'.label') ?? $run->scenario)
                        : null,
                    'status_label' => $run->status->label(),
                    'created_date' => $run->created_at->toDateString(),
                    // The Cockpit is the canonical career destination (F1, plan §9's 2.0 line):
                    // selecting a career from the list must not route through the 0.1.0 run-detail
                    // page. `runs.show` stays a compatibility doorway until the redirect ruling.
                    'url' => route('runs.cockpit', $run),
                ]),
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        /*
         * One query, one list, one truth. The combobox is the page's only picker now (ADR-0020 §1),
         * so the roster is also what the inheritance-parent selects read: a second trainee collection
         * would be a second list to drift from the first. Cards come with the trainees rather than in
         * a second pass: at 68 and ~107 rows, an eager load is one query and a lazy one is sixty-nine.
         *
         * The card gate stays inside the card list (FR-A-6, FR-B-4): an unconfirmed form is out of the
         * payload the same way the catalog hides it, because it is her form that is not confirmed yet,
         * not her place on the roster. A cardless trainee therefore ships with `cards: []` and the
         * component paints her one selectable row that commits an empty `character_card_id` - which
         * is nullable, and which the request accepts.
         */
        $trainees = Umamusume::query()
            ->where('release_status', ReleaseStatus::GlobalReleased->value)
            ->with(['cards' => fn ($query) => $query
                ->where('unconfirmed', false)
                ->orderBy('global_release_date')
                ->orderBy('card_id')])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        $roster = $trainees
            ->map(fn (Umamusume $u): array => [
                'umamusumeId' => $u->id,
                'trainee' => $u->name,
                'traineeJa' => $u->name_ja,
                'cards' => $u->cards->map(fn (CharacterCard $c): array => [
                    // The local primary key, which is what `character_card_id` is a
                    // foreign key to and what StoreTrainingRunRequest's `exists` rule
                    // reads. The source's own `card_id` rides along for matching and
                    // display only; submitting it would name a row that does not exist.
                    'selectionId' => $c->id,
                    'sourceCardId' => $c->card_id,
                    'title' => $c->title,
                    // Brackets stripped so `RUN` can prefix-match `[RUN! RUIN! LAUNCHER!]`,
                    // whose verbatim string starts with `[`. The label renders `title`.
                    'titleKey' => preg_replace('/^\[|\]$/', '', $c->title) ?? $c->title,
                    'releaseDate' => $c->global_release_date->toDateString(),
                    'debut' => $c->is_debut_form,
                ])->all(),
            ])->all();

        return Inertia::render('Runs/Create', [
            'roster' => $roster,
            'scenarios' => $this->scenarioLabels(),
            'statuses' => $this->statusLabels(),
            'maxObjectiveIndex' => RaceEntry::MAX_OBJECTIVE_INDEX,
            // A failed write comes back through a redirect, which reloads the page and takes the
            // component's own state with it, so the flashed input is what keeps a Trainer from
            // choosing their trainee twice.
            'old' => (array) $request->old(),
        ]);
    }

    public function store(StoreTrainingRunRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // The run and the skills seeded onto it land together or not at all (NFR-4): a half-created
        // run would show an empty Skills section that the Trainer then has to reason about alone.
        $run = DB::transaction(function () use ($validated): TrainingRun {
            $run = TrainingRun::create($validated);
            $this->prePopulateSkills($run);

            return $run;
        });

        return redirect()->route('runs.cockpit', $run)->with('status', 'Run created.');
    }

    /**
     * Seed a new run's `Starting` skills (the `Suggested` status) from the card it was started on (KI-33).
     *
     * A run that names no card has nothing to seed from, and a card whose lists are null was
     * published without them — `array` does not coerce a null column to `[]`, so both cases return
     * here rather than meeting a `foreach` over null.
     *
     * **The card's list is not a Global statement.** KI-33 names card `112701` as holding skill
     * data while its `release_en` is null, so every id resolves through
     * `Skill::scopeAvailableOnGlobal()` — the same read-path filter the picker and Screen D use —
     * and a skill the source has not released on `[Global]` is dropped rather than offered.
     *
     * Creation only, never a backfill: a run already in progress holds Trainer-entered rows, and
     * deriving `Starting` into it overwrites memory with plan, which D-270 and Planner Rule 4
     * both forbid. `TrainingRunTest` pins that against a second creation from the same card.
     *
     * `setSkillStatus` upserts through `syncWithoutDetaching`, so seeding is idempotent and a skill
     * the Trainer already recorded on this run keeps the state they gave it.
     */
    private function prePopulateSkills(TrainingRun $run): void
    {
        $card = $run->characterCard;

        if ($card === null) {
            return;
        }

        // The union, deduped: an id should not appear on both lists, and if the source ever says so
        // the run gets one row rather than two fighting over the same pivot key.
        $exportIds = array_values(array_unique([
            ...($card->skills_innate ?? []),
            ...($card->skills_unique ?? []),
        ]));

        if ($exportIds === []) {
            return;
        }

        foreach (Skill::query()->availableOnGlobal()->whereIn('export_id', $exportIds)->get() as $skill) {
            $run->setSkillStatus($skill, SkillAcquisition::Suggested);
        }
    }

    /**
     * The retired record screen's doorway (F2, plan §9.6). `GET /training-runs/{run}` no longer
     * renders a page: every write the 0.1.0 screen owned now has a 2.0 owner reachable from the
     * Cockpit, so the route redirects there. `{run}` still binds, so a missing id 404s before this
     * method runs. `Runs/Show.vue` and the `show()` method were deleted in the same pass.
     */
    public function redirect(TrainingRun $run): RedirectResponse
    {
        return redirect()->route('runs.cockpit', $run);
    }

    /**
     * The deck builder (SCREEN-007), over the same `runs.deck.sync` write the run screen uses.
     *
     * **The six selections live in the query string, not in component state.** Every picker action is
     * a `router.get` onto this same route, so a filter change or a page change would otherwise wipe
     * the five slots the Trainer had not submitted yet. Carrying them in the URL is the same move
     * `deckPayload()`'s `open_url` already makes for one slot at a time, lifted to all six, and it is
     * what lets the picker be a genuinely paginated query instead of five hundred option nodes.
     *
     * The read-back order is `old()` first, then the query string, then the stored row: a rejected
     * submission returns the six picks the Trainer just made (`D-3`), an unsubmitted pick survives
     * navigation, and a page reached from a link shows what the run holds.
     */
    public function deck(DeckPickerSearchRequest $request, TrainingRun $run): InertiaResponse
    {
        $run->loadMissing(['umamusume', 'deckSlots.supportCard']);
        $dictionary = SupportCardEffects::dictionary();

        return Inertia::render('Support/Builder', [
            'run' => [
                'id' => $run->id,
                'name' => $run->umamusume->name,
                'status_label' => $run->status->label(),
                'scenario' => $run->scenario,
                'has_scenario' => $run->hasScenario(),
                // The label is config's, never the stored slug, and null when the Trainer has not
                // chosen one, which is also when the Scenario Link derivation has nothing to run on.
                'scenario_label' => $run->hasScenario() ? config('scenarios.scenarios.'.$run->scenarioKey().'.label') : null,
                'run_url' => route('runs.cockpit', $run),
            ],
            'slots' => $this->builderSlots($request, $run, $dictionary),
            'picker' => $this->builderPicker($request, $run, $dictionary),
            'types' => $this->builderTypes(),
            'rarities' => $this->rarityWords(),
            'availabilities' => SupportCard::AVAILABILITIES,
            'analysis' => DeckAnalysis::build($this->builderAnalysisInput($request, $run, $dictionary)),
            'action' => route('runs.deck.sync', $run),
        ]);
    }

    /**
     * The six slot rows, each with the card it holds and the owned-or-rented flag the run records.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array<string, mixed>>
     */
    private function builderSlots(Request $request, TrainingRun $run, array $dictionary): array
    {
        $stored = $run->deckSlots->keyBy('slot_position');
        $rows = [];

        foreach (DeckSlot::POSITIONS as $position) {
            $selected = $this->builderSelection($request, $run, $position);
            $card = $selected === null ? null : SupportCard::query()->find($selected);

            $rows[] = [
                'position' => $position,
                'label' => $position === DeckSlot::MAX_POSITION ? 'Slot 6 · Friends' : 'Slot '.$position,
                // The role belongs to the position, not to the card parked in it (ADR-0014 correction 1).
                'is_friend' => $position === DeckSlot::MAX_POSITION,
                'selected' => (string) $selected,
                'card' => $card === null ? null : $this->builderCard($card, $run, $dictionary),
                // The flag the slot records, or null when nobody has said (ADR-0023). It is the run's
                // own fact, read from `deck_slots`, never derived from the player's current inventory.
                'ownership' => $stored->get($position)?->ownership,
            ];
        }

        return $rows;
    }

    /**
     * What a slot holds right now: the rejected submit's pick, else the pick in the URL, else the
     * stored row, else nothing.
     */
    private function builderSelection(Request $request, TrainingRun $run, int $position): ?int
    {
        // `old()` hands back whatever the POST carried, and a hand-made request or a test can carry an
        // int where a form sends text, so the check is on the cast value rather than on the type.
        $old = $request->old("deck.{$position}.support_card_id");

        if ($old !== null && (string) $old !== '') {
            return (int) $old;
        }

        $picked = $request->query('deck');

        if (is_array($picked) && (string) ($picked[$position] ?? '') !== '') {
            return (int) $picked[$position];
        }

        $id = $run->deckSlots->firstWhere('slot_position', $position)?->support_card_id;

        return $id === null ? null : (int) $id;
    }

    /**
     * The card picker. The offered set is the Global releases plus any card this run already uses, the
     * same rule `deckPayload()` applies, and the query is the same shape `SupportCardController` uses:
     * facet columns first, a deterministic order, then a clamped page size.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return array<string, mixed>
     */
    private function builderPicker(DeckPickerSearchRequest $request, TrainingRun $run, array $dictionary): array
    {
        $equipped = $run->deckSlots->pluck('support_card_id')->all();
        $rarity = $request->validated('rarity');
        $type = $request->validated('type');
        $status = $request->validated('status');
        $search = $request->validated('query');

        $query = SupportCard::query()
            ->where(function ($q) use ($equipped): void {
                $q->whereNotNull('release_global');

                if ($equipped !== []) {
                    $q->orWhereIn('id', $equipped);
                }
            })
            ->when($rarity !== null, fn ($q) => $q->where('rarity', (int) $rarity))
            ->when($type !== null, fn ($q) => $q->where('type', $type))
            ->when($status !== null, fn ($q) => $q->where('release_status', $status))
            ->when($search !== null, fn ($q) => $q->where(
                fn ($inner) => $inner->where('char_name', 'like', '%'.$search.'%')->orWhere('title_en', 'like', '%'.$search.'%')
            ))
            // The tiebreaker is load-bearing for the same reason it is on the catalog: an order without
            // one lets pagination hand back a different half of the catalogue on each pass.
            ->orderBy('type')
            ->orderBy('char_name')
            ->orderBy('title_en');

        $offered = SupportCard::query()
            ->whereNotNull('release_global')
            ->when($equipped !== [], fn ($q) => $q->orWhereIn('id', $equipped))
            ->count();

        return [
            'page' => $query->paginate(PageSize::clamp($request->query('pageSize')))
                ->withQueryString()
                ->through(fn (SupportCard $card): array => $this->builderCard($card, $run, $dictionary)),
            'offered' => $offered,
            'type' => $type,
            'rarity' => $rarity,
            'status' => $status,
            'query' => $search,
            // Which slot a card row's Equip button writes to. A replacement is one keystroke away from
            // the row, and the focus comes back to the slot afterwards. An out-of-range slot is a
            // pasted URL, not a Trainer's mistake, so it falls back to the first rather than refusing
            // the page over a picker target (the judgement `PageSize` already states for a page size).
            'fillingSlot' => in_array((int) $request->validated('slot'), DeckSlot::POSITIONS, true)
                ? (int) $request->validated('slot')
                : DeckSlot::POSITIONS[0],
            'askedFor' => $this->builderAsk($rarity, $type, $status, $search),
        ];
    }

    /**
     * What the Trainer narrowed the picker by, for the no-results state to name the ask rather than
     * repeating a query back at them.
     */
    private function builderAsk(?string $rarity, ?string $type, ?string $status, ?string $search): string
    {
        $parts = array_filter([
            $rarity === null ? null : 'rarity '.CardRarity::from((int) $rarity)->word(),
            $type === null ? null : 'type '.SupportCard::typeWord($type),
            $status === null ? null : 'availability '.$status,
            $search === null ? null : 'a name containing "'.$search.'"',
        ]);

        return $parts === [] ? 'those conditions' : implode(' + ', $parts);
    }

    /**
     * One card, in the shape both the slot rows and the picker rows read.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return array<string, mixed>
     */
    private function builderCard(SupportCard $card, TrainingRun $run, array $dictionary): array
    {
        $scenarioLink = $this->scenarioLinkState($card, $run);

        return [
            'id' => $card->id,
            'name' => $card->displayName(),
            'name_ja' => $card->name_ja,
            'title_ja' => $card->title_ja,
            'url' => route('support-cards.show', $card),
            // Keyed on the publisher's `support_id`, the only key the mirror's path answers to
            // (ADR-0021 read half, `design-2.0` §45a "Support-card index, card row").
            'artworkURL' => app(ArtworkMirror::class)->url('support_thumb', (int) $card->support_id),
            'rarity_word' => $card->rarityWord(),
            'type' => $card->type,
            'type_label' => $card->typeLabel(),
            'scenario_link' => $scenarioLink['state'],
            'scenario_link_note' => $scenarioLink['note'],
            'effects' => SupportCardEffects::atCap($card, $dictionary),
        ];
    }

    /**
     * The Scenario Link badge, derived on read and never stored (ADR-0014 correction 3; §1.4.7).
     *
     * Three states rather than one, because `isScenarioLink()` answers `false` for two different
     * things: a character that is genuinely not on the list, and a card with nothing to compare
     * against. Printing the badge for the second would be a claim the repository cannot make, so it
     * becomes `N/A` with the reason as its title, which is the rule every underived value on this
     * screen follows.
     *
     * @return array{state: string, note: string|null}
     */
    private function scenarioLinkState(SupportCard $card, TrainingRun $run): array
    {
        // The derivation itself moved to `SupportCard::scenarioLinkState()`, the one owner both this
        // screen and the setup wizard's deck step read through. The three states, their wording and this
        // screen's own no-scenario sentence are unchanged.
        return $card->scenarioLinkState(
            $run->hasScenario() ? $run->scenarioKey() : null,
            'This run names no scenario, so there is no linked list to check this card against.',
        );
    }

    /**
     * The six cards the analysis reads, in slot order, with their at-cap effects.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{card_name: string, effects: list<array<string, mixed>>}>
     */
    private function builderAnalysisInput(Request $request, TrainingRun $run, array $dictionary): array
    {
        $ids = [];

        foreach (DeckSlot::POSITIONS as $position) {
            $id = $this->builderSelection($request, $run, $position);

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        $cards = [];

        foreach (SupportCard::query()->whereIn('id', $ids)->get() as $card) {
            $cards[] = [
                'card_name' => $card->displayName(),
                'effects' => SupportCardEffects::atCap($card, $dictionary),
            ];
        }

        return $cards;
    }

    /**
     * The seven support types as the picker and the slot rows read them.
     *
     * @return list<array{key: string, label: string}>
     */
    private function builderTypes(): array
    {
        return array_map(
            static fn (string $type): array => ['key' => $type, 'label' => SupportCard::typeWord($type)],
            SupportCard::TYPES
        );
    }

    /**
     * The rarity words the picker offers, keyed by the value the query string carries.
     *
     * `array<int, string>`, not `array<string, string>`: the keys are `CardRarity`'s backing ints, and
     * PHP stores the numeric strings this loop writes as ints. JSON emits them as object keys either
     * way, so the picker reads the same payload; the narrower annotation is the true one.
     *
     * @return array<int, string>
     */
    private function rarityWords(): array
    {
        $words = [];

        foreach (CardRarity::cases() as $case) {
            $words[(string) $case->value] = $case->word();
        }

        return $words;
    }

    /**
    /**
     * The five disciplines plus the two actions that train no stat.
     *
     * Labels are the stat words the matrix already uses, not composed client copy: the corpus
     * evidences Wit's zero Energy cost (UMAMUSUME_REFERENCE.md §1.1.1), and no client string for
     * the five facility buttons has been read, so a `Speed Work` label would be an invented name
     * promoted into a Trainer-facing select (D-20).
     *
     * `Rest` and `Recreation` are the two that are not inventions. Both are printed on the
     * six-button action row in three July 2026 client captures under
     * `docs/research-scratch/screenshot-notes/`, and `docs/scenarios/01-ura-finale.md:54` already
     * spells the second one `Recreation`. The flag that used to sit on that row is off.
     *
     * Neither detail line carries a number. The published rest and outing values are probabilities,
     * and §2.2 keeps those out of application code; the rest *tier* is worse, because this
     * repository's own two sources disagree on it. §1.1.5 of `docs/UMAMUSUME_REFERENCE.md` has
     * "+30 Energy per standard rest" against GameWith 2026-09-25, while §2.1 of
     * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` measures the modal rest at +50 with
     * +30 as the under-slept failure tier, and a post-rework Global table agrees that 50 is the
     * common case. A Trainer-facing number picked out of that would be a guess wearing the tool's
     * voice, so the line says what all four sources agree on instead.
     *
     * @return list<array<string, mixed>>
     */
    private function turnChoices(): array
    {
        $choices = [];

        foreach (config('scenarios.stat_order') as $stat) {
            $choices[] = [
                'key' => 'training-'.$stat,
                'label' => $stat,
                'detail' => $stat === 'Wit'
                    ? 'Costs no Energy, so it stays available when the bar is low.'
                    : 'Energy spent and gains made are what you record for this turn.',
            ];
        }

        $choices[] = [
            'key' => 'rest',
            'label' => 'Rest',
            'detail' => 'Refills Energy. A poor rest refills less and can leave a stayed-up-late penalty.',
        ];
        $choices[] = [
            'key' => 'mood',
            'label' => 'Recreation',
            'detail' => 'Lifts Mood, and the outing may return a little Energy as well.',
        ];

        return $choices;
    }

    /**
     * The row this submission replaces the comparison against: the highest stored turn
     * below the one being entered, not simply the last row. Inserting turn 4 into a
     * three-turn run compares against turn 3; re-editing turn 2 compares against turn 1.
     */
    private function previousTurn(TrainingRun $run, int $turn): ?TurnEntry
    {
        return $run->turnEntries
            ->filter(fn (TurnEntry $entry): bool => $entry->turn < $turn)
            ->sortBy('turn')
            ->last();
    }

    /**
     * The preview, built from what was entered minus what is stored.
     *
     * This is arithmetic on two rows the Trainer typed, not a projection: no gain is
     * forecast and no odds are shown, because no source publishes a failure curve
     * (Planner Rule 5, ADR-0001 §3). Zeros are left out rather than printed, and the
     * rail says so in words when the difference cannot be computed at all.
     *
     * @param  array<string, mixed>  $entered
     * @return list<array{direction: string, text: string}>
     */
    private function previewDeltas(array $entered, ?TurnEntry $previous): array
    {
        if ($previous === null) {
            return [];
        }

        $rows = [
            ['Speed', (int) $entered['speed'] - $previous->speed],
            ['Stamina', (int) $entered['stamina'] - $previous->stamina],
            ['Power', (int) $entered['power'] - $previous->power],
            ['Guts', (int) $entered['guts'] - $previous->guts],
            ['Wit', (int) $entered['wit'] - $previous->wit],
            ['Skill Points', (int) ($entered['sp'] ?? 0) - (int) $previous->sp],
            ['Energy', (int) ($entered['energy'] ?? 0) - (int) $previous->energy],
            ['Fans', (int) ($entered['fans'] ?? 0) - (int) $previous->fans],
        ];

        $moodDelta = $this->moodDelta($entered['mood'] ?? null, $previous->mood);

        if ($moodDelta !== null) {
            $rows[] = ['Mood', $moodDelta];
        }

        $deltas = [];

        foreach ($rows as [$label, $delta]) {
            if ($delta === 0) {
                continue;
            }

            $deltas[] = [
                'direction' => $delta > 0 ? 'up' : 'down',
                'text' => ($delta > 0 ? '+' : '-').abs($delta).' '.$label,
            ];
        }

        return $deltas;
    }

    /**
     * Mood moves in tier steps, which is how the client's own panel counts it
     * (`DESIGN.md` §6.17: +20% at GREAT down to -20% at AWFUL), so the difference of two
     * recorded tiers is a number the Trainer can check rather than a scale invented here.
     *
     * The sign runs the good way up, which means subtracting the new tier from the old:
     * `MoodTier::cases()` is ordered best to worst, so a raw index difference reads
     * GREAT to BAD as +3 and paints a mood collapse in the colour of a gain. The browser
     * pass found it; the first version of this method did not.
     */
    private function moodDelta(mixed $entered, ?MoodTier $stored): ?int
    {
        if ($entered === null || $entered === '' || $stored === null) {
            return null;
        }

        $tier = MoodTier::tryFrom((string) $entered);

        if ($tier === null) {
            return null;
        }

        return array_search($stored, MoodTier::cases(), true)
            - array_search($tier, MoodTier::cases(), true);
    }

    /**
     * Only the columns `turn_entries` owns. The rail's five extra keys are validated but
     * must not ride into the create call: Laravel 13 discards non-fillable keys silently,
     * so a typo here would lose a value without a word of complaint.
     *
     * `updateTurn` writes `$request->validated()` whole, so the two turn writes must agree on the key
     * set or a level entered on the decision screen would survive an edit and not a create. The five
     * facility keys are here for that reason; they are the request's own, not a second list.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function turnAttributes(array $validated): array
    {
        return array_intersect_key(
            $validated,
            array_flip([
                'turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans',
                'facility_speed', 'facility_stamina', 'facility_power', 'facility_guts', 'facility_wit',
                'failure_rate', 'preview_gains',
            ]),
        );
    }

    public function update(StoreTrainingRunRequest $request, TrainingRun $run): RedirectResponse
    {
        $run->update($request->validated());

        return redirect()->route('runs.cockpit', $run)->with('status', 'Run updated.');
    }

    /**
     * Stores the build target the Trainer entered for this run (FR-F-1, `ADR-0020` §2).
     *
     * The target is replaced whole rather than merged: it is entered and read as one object, and a
     * merge would leave a field the Trainer cleared sitting in the column while nothing on screen
     * still claimed it. Validation is the request's; this method writes what it was handed.
     *
     * The redirect returns to the page the form was submitted from, for the reason `storeRace()`
     * records: a target write now has two surfaces, the Skills Planner's reorder save (plan §8
     * D13) and this screen's own target editing, and pinning `runs.show` sent a Trainer who saved
     * from the planner off it. For the record screen the two are the same URL.
     */
    public function updateBuildTarget(StoreBuildTargetRequest $request, TrainingRun $run): RedirectResponse
    {
        $run->update(['build_target' => $request->payload()]);

        return redirect()
            ->back(fallback: route('runs.cockpit', $run))
            ->with('status', 'Build target saved.');
    }

    /**
     * Records what the Trainer did with one calendar slot (US-10, ADR-0003), or
     * creates a manual slot for a race not on the calendar (R56, R61).
     *
     * The write goes through `RaceEntry::create`, not the factory, so the model's saving
     * guards are the ones that decide whether circles or a period index are admissible
     * here; a guard that only runs on the form path would be a guard that bulk writes
     * walk straight past.
     *
     * The redirect returns to the page the form was submitted from, for the reason `updateTurn()`
     * records: a race write now has two surfaces, this run screen's panel and the Race Decision
     * screen's drawer (`SCREEN-011`, `SCR-CAR-013`), and pinning `runs.show` sent a Trainer who
     * entered a race from the decision screen off it. For the panel the two are the same URL.
     */
    public function storeRace(StoreRaceEntryRequest $request, TrainingRun $run): RedirectResponse
    {
        $validated = $request->validated();

        // The slot and the entry that names it land together or not at all (NFR-4), the way `store`
        // above treats the run and its seeded skills. A slot written on its own is a calendar row
        // pointing at a race nobody recorded, and it survives as one because nothing revisits it.
        DB::transaction(function () use ($request, $run, $validated): void {
            if ($request->isManualPath()) {
                $maxSort = ScenarioSlot::where('scenario_key', $run->scenarioKey())
                    ->max('sort_order') ?? 0;

                $slot = ScenarioSlot::create([
                    'scenario_key' => $run->scenarioKey(),
                    'kind' => 'free_race',
                    'source_key' => null,
                    'slot_label' => $validated['title'],
                    'title' => $validated['title'],
                    'month' => (int) $validated['month'],
                    'half' => $validated['half'],
                    'tier' => $validated['tier'] ?? null,
                    'is_manual' => true,
                    'sort_order' => $maxSort + 1,
                ]);

                $entryData = array_intersect_key($validated, array_flip([
                    'status', 'placement', 'fans_gain', 'circles', 'objective_index', 'turn_entry_id',
                ]));
                $entryData['scenario_slot_id'] = $slot->id;

                $run->raceEntries()->create($entryData);
            } else {
                $entryData = array_intersect_key($validated, array_flip([
                    'scenario_slot_id', 'race_catalog_slot_id', 'status', 'placement', 'fans_gain', 'circles', 'objective_index', 'turn_entry_id',
                ]));

                $run->raceEntries()->create($entryData);
            }
        });

        return redirect()
            ->back(fallback: route('runs.cockpit', $run))
            ->with('status', 'Race recorded.');
    }

    /**
     * Records one shop purchase as a `turn_events` row (D-226, ADR-0003).
     *
     * The payload is built through `ShopPurchasePayload::make` so the catalogue and price
     * rules are the same ones the saving guard enforces; the request has already turned a
     * bad submission into field errors, so anything reaching here that the payload rejects
     * is a bug rather than a user mistake.
     */
    public function storePurchase(StoreShopPurchaseRequest $request, TrainingRun $run): RedirectResponse
    {
        $validated = $request->validated();
        $payload = ShopPurchasePayload::make(
            (string) $validated['item'],
            (int) $validated['cost'],
            (string) $validated['effect'],
        );

        $run->turnEvents()->create([
            'turn' => (int) $validated['turn'],
            'event_type' => TurnEventType::Scenario,
            'source_name' => 'Shop',
            'deltas' => $payload->toArray(),
        ]);

        return redirect()
            ->route('runs.cockpit', $run)
            ->with('status', 'Purchase recorded.');
    }

    public function destroy(TrainingRun $run): RedirectResponse
    {
        $run->delete();

        return redirect()->route('runs.index')->with('status', 'Run deleted.');
    }

    /**
     * Two stages on one endpoint. A staged preview re-renders the screen with the deltas
     * and writes nothing (D-51: "Selecting must never write"), and the confirm stage is
     * the only path that reaches `create`. The FormRequest already refused a confirm that
     * never carried a preview.
     *
     * A recorded failure writes a `turn_events` row. It has no home in `turn_entries`,
     * which holds the absolute values the Trainer read off the client and would rather
     * stay a single kind of thing, and `turn_events` was built for exactly this: an
     * observed outcome with its deltas and a note (ADR-0003). A success writes no event,
     * because the turn row already says everything a success adds.
     */
    public function storeTurn(StoreTurnEntryRequest $request, TrainingRun $run): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['stage'] ?? null) === 'preview') {
            // PRG, like every other write in this controller. The preview is a screen,
            // not a response body: rendering it from the POST left the address bar on
            // the endpoint, so refreshing re-issued the POST and the browser got a 405
            // (audit F-7) -- the only write flow in the app that was not refresh-safe.
            // The input travels in the session so a hand-made POST with stage=preview
            // still gets the redirect rather than a write. No 2.0 surface posts the
            // stage marker anymore (F2, plan §9.6; owner ruling on group R-2), so this
            // branch is unreachable from the UI but stays for a hand-made request.
            return redirect()->route('runs.cockpit', $run)
                ->withInput($validated + ['previewed' => '1']);
        }

        // The turn row and its failure event are one write (NFR-4): a turn that says `Failure` with no
        // event behind it is the half state, and nothing later reconciles it. A success writes the one
        // row it needs, inside the same transaction.
        DB::transaction(function () use ($run, $validated): void {
            $entry = $run->turnEntries()->create($this->turnAttributes($validated));

            // An event is keyed on `(training_run_id, turn)` with no `turn_entry_id`, so a row at a
            // number a deleted turn used inherits that turn's event, and the page's Failed chip is
            // built from exactly that key. Cleared before any new event is written, and cleared
            // whether or not this turn failed: a success at a reused number has to shed the orphan
            // just as a failure has to replace it.
            $run->turnEvents()
                ->where('turn', $entry->turn)
                ->where('event_type', TurnEventType::Failure)
                ->delete();

            if (($validated['outcome'] ?? null) === 'Failure') {
                $label = $this->choiceLabel((string) ($validated['choice'] ?? ''));
                $kind = (string) $validated['penalty_kind'];
                $previous = $this->previousTurn($run, (int) $entry->turn);

                $penalties = [];

                foreach ($this->previewDeltas($validated, $previous) as $delta) {
                    if ($delta['direction'] === 'down') {
                        $penalties[$delta['text']] = true;
                    }
                }

                $run->turnEvents()->create([
                    'turn' => $entry->turn,
                    'event_type' => TurnEventType::Failure,
                    'source_name' => $label,
                    'choice_label' => $label,
                    'deltas' => [
                        'penalty_kind' => $kind,
                        'recorded' => array_keys($penalties),
                    ],
                    'origin_note' => 'The Trainer recorded this turn as a failure of '.$label.', '
                        .'with a '.$kind.' penalty. Logged from the client, not modelled: '
                        .'no source in this repository publishes a failure chance.',
                ]);
            }

            // A Performance observation is the Trainer's own reading of the turn (Our Grand
            // Concert, `docs/scenarios/07-grand-concert.md`). Nothing here derives what the turn
            // should have paid: the amount is the one that was entered, stored as a signed delta,
            // and no running total is kept -- `turn_entries` stays the record of absolute values
            // and `PerformancePayload` carries only the change. A negative delta is Performance
            // spent, which is the same kind of fact as a positive one and not a second shape.
            //
            // Stored as a `Scenario` event, so `destroyTurn` leaves it alone when a turn is removed:
            // that method already rules on this key for `storePurchase`, and deleting a turn is not a
            // ruling on a record the turn made. The scenario gate lives on the reader, the way
            // `latestTeamRank()` gates on its panel, because this table is an observation log and no
            // payload has ever been gated on the way in.
            if (isset($validated['performance'])) {
                $run->turnEvents()->create([
                    'turn' => $entry->turn,
                    'event_type' => TurnEventType::Scenario,
                    'source_name' => $this->choiceLabel((string) ($validated['choice'] ?? '')),
                    'deltas' => PerformancePayload::make(
                        PerformanceType::from((string) $validated['performance']['type']),
                        (int) $validated['performance']['delta'],
                    )->toArray(),
                    'origin_note' => 'The Trainer recorded this turn\'s Performance change. Observed, '
                        .'not modelled: no source in this repository publishes what a training turn '
                        .'pays or what a Lesson costs.',
                ]);
            }
        });

        return redirect()->route('runs.cockpit', $run)
            ->with('status', 'Turn '.$validated['turn'].' logged.');
    }

    /**
     * The choice's own label, read back out of the list the rail was built from, so the
     * event never stores a word the rail did not offer.
     */
    private function choiceLabel(string $key): string
    {
        foreach ($this->turnChoices() as $choice) {
            if ($choice['key'] === $key) {
                return (string) $choice['label'];
            }
        }

        return $key === '' ? 'unrecorded choice' : $key;
    }

    /**
     * Update a nested turn; the turn must belong to the routed run (404
     * otherwise), since route binding alone does not scope it.
     *
     * The redirect returns to the page the form was submitted from rather than to a fixed route,
     * because a turn write now has two surfaces: this run screen's row form, and the Career Cockpit's
     * manual correction (SCREEN-009, `design-2.0` §38). Pinning `runs.show` here sent a Trainer who
     * corrected a reading on the Cockpit off the screen they were reading. For the row form the two
     * are the same URL, so the run screen is unchanged.
     */
    public function updateTurn(StoreTurnEntryRequest $request, TrainingRun $run, TurnEntry $turn): RedirectResponse
    {
        abort_unless($turn->training_run_id === $run->id, 404);

        $turn->update($request->validated());

        return redirect()->back(fallback: route('runs.cockpit', $run))->with('status', 'Turn '.$turn->turn.' updated.');
    }

    public function destroyTurn(TrainingRun $run, TurnEntry $turn): RedirectResponse
    {
        abort_unless($turn->training_run_id === $run->id, 404);

        // The row and its failure event go together. The event is keyed on `(training_run_id, turn)`
        // rather than on the row, so leaving it behind hands its Failed chip to whichever turn later
        // takes the number (SCREEN_SPEC.md §7-2). Scoped to the failure type because a shop purchase
        // is stored on this same key by `storePurchase` as a `Scenario` event, and deleting a turn
        // is not a ruling on that record. One transaction, so a half delete cannot strand either row.
        DB::transaction(function () use ($run, $turn): void {
            $run->turnEvents()
                ->where('turn', $turn->turn)
                ->where('event_type', TurnEventType::Failure)
                ->delete();

            $turn->delete();
        });

        return redirect()->route('runs.cockpit', $run)->with('status', 'Turn '.$turn->turn.' removed.');
    }

    /**
     * Upsert the given skill statuses on the run; skills not listed keep their
     * current status (syncWithoutDetaching, TrainingRun::setSkillStatus).
     */
    public function syncSkills(StoreRunSkillRequest $request, TrainingRun $run): RedirectResponse
    {
        foreach ($request->validated()['skills'] as $entry) {
            // The same scope the request rule applies, so the two cannot disagree about which rows
            // are writable. The request is what refuses an ineligible skill; this is the second
            // gate behind it, and the null check stays because `find()` is nullable.
            $skill = Skill::availableOnGlobal()->find((int) $entry['skill_id']);

            if ($skill !== null) {
                $run->setSkillStatus($skill, $request->acquisitionFor($entry), $entry['turn_acquired'] ?? null);
            }
        }

        return redirect()->route('runs.cockpit', $run)->with('status', 'Skill status saved.');
    }

    /**
     * Replace the run's equipped deck with the slots submitted (ADR-0014).
     *
     * Delete-then-insert rather than an upsert, because a deck slot is a *position*: when a card moves
     * from slot two to slot three it has to leave slot two, and a write keyed on the card alone would
     * leave it standing in both. The rows all land together or not at all (NFR-4), so a run is never
     * left half-cleared by a failure halfway through.
     *
     * A deck with no rows is a true statement about a run whose Trainer does not remember what they
     * equipped, which is why every slot may be left blank rather than the form refusing to submit.
     */
    public function syncDeck(StoreDeckRequest $request, TrainingRun $run): RedirectResponse
    {
        DB::transaction(function () use ($request, $run): void {
            // Read the flags before the delete. The flag belongs to the slot, not to the card, so a
            // re-save whose payload carries no flag for a position keeps what that slot already recorded
            // rather than erasing it, and the run screen's own panel posts one value per slot
            // (`Support/Builder.vue`, `ADR-0023`; pinned by `SupportDeckBuilderTest`).
            /** @var array<int, string|null> $existing */
            $existing = $run->deckSlots()->pluck('ownership', 'slot_position')->all();
            $posted = $request->ownershipByPosition();

            $run->deckSlots()->delete();

            foreach ($request->slotsByCardId() as $position => $cardId) {
                $run->deckSlots()->create([
                    'support_card_id' => $cardId,
                    'slot_position' => (int) $position,
                    'ownership' => $posted[(int) $position] ?? $existing[(int) $position] ?? null,
                ]);
            }
        });

        return redirect()->route('runs.show', $run)->with('status', 'Deck saved.');
    }

    /**
     * The import form: pick the trainee and the scenario, paste or choose the run's CSV (ADR-0017).
     *
     * No costume card here, though `create()` asks for one. `character_card_id` is nullable, and a paper
     * sheet records the trainee rather than which of her forms was equipped; collecting it would offer a
     * field the source cannot fill and invite a guess that then reads as the Trainer's own record.
     */
    public function importForm(Request $request): InertiaResponse
    {
        return Inertia::render('Runs/Import', $this->importProps($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function importProps(Request $request): array
    {
        return [
            ...$this->importChoices(),
            'statuses' => $this->statusLabels(),
            // The column list is the export's own, so the hint a Trainer reads before pasting is the
            // same array the request parses against rather than a second copy that can drift.
            'headers' => ImportHistoricalRunRequest::HEADERS,
            'preview' => null,
            'old' => (array) $request->old(),
        ];
    }

    /**
     * Show what the file contains before any of it is written.
     *
     * The same request validates this step and the commit step, so the preview is not a trust boundary:
     * nothing here is carried forward as already-checked, and editing the flashed CSV to something invalid
     * fails on the second POST rather than reaching the action. The rows are handed over as raw strings
     * straight from the parse, which is also what makes a rejected cell point at the row that held it.
     */
    public function importPreview(ImportHistoricalRunRequest $request): InertiaResponse
    {
        $validated = $request->validated();

        return Inertia::render('Runs/Import', [
            ...$this->importProps($request),
            'preview' => [
                'csv' => (string) $validated['csv'],
                'turns' => $validated['turns'],
                'run' => $request->only(['umamusume_id', 'scenario', 'status', 'notes']),
            ],
        ]);
    }

    /**
     * Write the imported run, then return to it.
     *
     * Re-validates rather than accepting a token from the preview, which keeps the flow two POSTs and no
     * session state. A run and its turns land in one transaction (`ImportHistoricalRun`), so a file that
     * fails halfway leaves no partial history behind.
     */
    public function importStore(ImportHistoricalRunRequest $request, ImportHistoricalRun $import): RedirectResponse
    {
        $validated = $request->validated();

        // The request keeps the browser's filename when there was one and folds a pasted body to a short
        // hash of its own content, so the column always names something the Trainer can point at.
        $source = $request->sourceLabel((string) $validated['csv']);

        $run = $import->handle($validated, $validated['turns'], $source);

        return redirect()->route('runs.show', $run)
            ->with('status', 'Imported '.count($validated['turns']).' turns.');
    }

    /**
     * Scenario slug => the matrix's own display label, for the two forms that offer
     * a choice. Composed from `config('scenarios.php')` rather than from a list
     * here, so a fifth scenario appears without a controller edit (D-240).
     *
     * @return array<string, string>
     */
    private function scenarioLabels(): array
    {
        return array_map(
            static fn (array $def): string => $def['label'],
            config('scenarios.scenarios'),
        );
    }

    /**
     * Status value => the word the enum labels it with, for the two forms that offer the choice.
     * Built from `RunStatus::cases()` so a fourth case appears without a controller edit.
     *
     * @return array<string, string>
     */
    private function statusLabels(): array
    {
        return collect(RunStatus::cases())
            ->mapWithKeys(fn (RunStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * The two lists the import page renders, shared by its form and its preview step.
     *
     * Narrower than `create()`'s trainee query on purpose: the import collects no costume card, so it
     * selects no cards and carries no `unconfirmed` gate. A global roster would list a trainee the run
     * cannot be created for.
     *
     * @return array{trainees: list<array{id: int, name: string, name_ja: string|null}>, scenarios: array<string, string>}
     */
    private function importChoices(): array
    {
        return [
            'trainees' => Umamusume::query()
                ->where('release_status', ReleaseStatus::GlobalReleased->value)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'name_ja'])
                ->map(static fn (Umamusume $u): array => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'name_ja' => $u->name_ja,
                ])
                ->all(),
            'scenarios' => $this->scenarioLabels(),
        ];
    }

    /**
     * Download one run as csv (turn rows) or json (TrainingRunResource). Only
     * these two formats exist (PRD FR-C-5; Excel was cut in the Pre-Mortem);
     * anything else 404s. Sent as an attachment so the browser downloads it.
     *
     * @throws NotFoundHttpException on an unknown format
     */
    public function export(TrainingRun $run, string $format): Response
    {
        abort_unless(in_array($format, ['csv', 'json'], true), 404);

        $run->load(['umamusume', 'turnEntries', 'skills']);

        if ($format === 'json') {
            $content = json_encode(['data' => new TrainingRunResource($run)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } else {
            // Appended after the original eight rather than interleaved: the leading
            // positions are unchanged, so a consumer keying on column order still reads
            // the same fields. Energy, mood and fans are captured by the guided form and
            // shown on the run screen, so leaving them out meant the exported run was not
            // the run the Trainer had just looked at (audit F-9). `condition` stays --
            // D-53 keeps the raw escape hatch reachable, and its column is real.
            $rows = [['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans']];

            foreach ($run->turnEntries as $entry) {
                $rows[] = [$entry->turn, $entry->speed, $entry->stamina, $entry->power, $entry->guts, $entry->wit, $entry->sp, $entry->condition, $entry->energy, $entry->mood?->value, $entry->fans];
            }

            $content = implode("\n", array_map(fn (array $row): string => implode(',', array_map(fn ($cell): string => (string) ($cell ?? ''), $row)), $rows));
        }

        return response($content, 200, [
            'Content-Type' => $format === 'json' ? 'application/json' : 'text/csv',
            'Content-Disposition' => "attachment; filename=\"run-{$run->id}.{$format}\"",
        ]);
    }
}
