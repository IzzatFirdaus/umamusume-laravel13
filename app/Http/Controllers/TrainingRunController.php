<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ImportHistoricalRun;
use App\Enums\MoodTier;
use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Enums\TurnEventType;
use App\Http\Requests\ImportHistoricalRunRequest;
use App\Http\Requests\StoreDeckRequest;
use App\Http\Requests\StoreRaceEntryRequest;
use App\Http\Requests\StoreRunSkillRequest;
use App\Http\Requests\StoreShopPurchaseRequest;
use App\Http\Requests\StoreTrainingRunRequest;
use App\Http\Requests\StoreTurnEntryRequest;
use App\Http\Resources\TrainingRunResource;
use App\Models\CharacterCard;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvents\ShopPurchasePayload;
use App\Models\Umamusume;
use App\Services\ScenarioCaps;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trainer-owned run CRUD: runs, their turns, their skill states, and export
 * (PRD US-3/US-4/US-6, FR-C-4/C-5). Validation lives in the Form Requests;
 * deletion cascades to turns and skill rows. No engine data is touched here.
 */
class TrainingRunController extends Controller
{
    /**
     * The inputs the guided rail stages, and therefore the only ones a failed submit
     * should hand back to it. `condition` is absent because the rail has no field for it
     * and rehydrating a key nothing reads would be a claim that the rail collects it.
     *
     * @var list<string>
     */
    private const STAGED_TURN_FIELDS = [
        'turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp',
        'energy', 'fans', 'mood', 'choice', 'outcome', 'penalty_kind',
    ];

    public function index(): View
    {
        return view('runs.index', [
            'runs' => TrainingRun::with('umamusume')->latest()->paginate(25),
        ]);
    }

    public function create(): View
    {
        /*
         * One query, two lists, one truth. The catalog page and this selector read the same
         * rows, so a card that exists in the dropdown cannot be absent from the catalog. Cards
         * come with the trainees rather than in a second pass: at 68 and ~107 rows, an eager
         * load is one query and a lazy one is sixty-nine.
         *
         * The two lists hold the same trainees, and that equality is load-bearing. The combobox
         * disables the select and commits only trainees the payload carries, so a trainee the
         * payload drops is unreachable *and* unpostable for as long as she is dropped: an
         * unconfirmed form is not a pre-fetch transient, it is how the row sits until a fetch
         * confirms it. A cardless row therefore ships with `cards: []`, and the module paints her
         * one selectable row that says no costume card is confirmed yet, committing her with an
         * empty `character_card_id` - which is nullable, and which the request accepts.
         *
         * The card gate stayed where it belongs, inside the card list (FR-A-6, FR-B-4): an
         * unconfirmed form is out of the payload the same way the catalog hides it, because it is
         * her form that is not confirmed yet, not her place on the roster.
         */
        $trainees = Umamusume::query()
            ->where('release_status', ReleaseStatus::GlobalReleased->value)
            ->with(['cards' => fn ($query) => $query
                ->where('unconfirmed', false)
                ->orderBy('global_release_date')
                ->orderBy('card_id')])
            ->orderBy('name')
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

        return view('runs.create', [
            'umamusumes' => $trainees,
            'rosterJson' => $roster,
            'selectedLabel' => $this->selectedCardLabel(),
            'scenarios' => $this->scenarioLabels(),
        ]);
    }

    /**
     * The selection a failed submit has to hand back. Read from `old()` so the visible
     * label names the same card the hidden fields still carry, not one the Trainer has to
     * pick again. Joined with a middle dot: R-02, D-79 and RenderedCopyHygieneTest keep an
     * em or en dash out of copy that reaches a Trainer.
     */
    private function selectedCardLabel(): ?string
    {
        $cardId = old('character_card_id');

        if (! is_numeric($cardId)) {
            return null;
        }

        $card = CharacterCard::with('umamusume')->find((int) $cardId);

        return $card === null ? null : $card->umamusume->name.' · '.$card->title;
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

        return redirect()->route('runs.show', $run)->with('status', 'Run created.');
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

    public function show(TrainingRun $run): View
    {
        $run->load(['umamusume', 'turnEntries', 'skills', 'turnEvents', 'deckSlots.supportCard', 'raceEntries.scenarioSlot', 'raceEntries.raceCatalogSlot', 'raceEntries.turnEntry']);

        return view('runs.show', $this->showData($run));
    }

    /**
     * The calendar rows a Trainer may enter a race against: this scenario's slots, in
     * timeline order. Empty by design until the fetch engine lands (KI-11), which is why
     * the form keeps the list out of the panel body and the panel body handles the
     * absence (C-7, D-220).
     *
     * @return Collection<int, ScenarioSlot>
     */
    private function raceSlotsFor(TrainingRun $run): Collection
    {
        if (! $run->hasScenario()) {
            return ScenarioSlot::query()->whereRaw('1 = 0')->get();
        }

        return ScenarioSlot::query()
            ->where('scenario_key', $run->scenarioKey())
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Everything `runs.show` renders, in one place, so the GET and the staged preview
     * cannot drift into two different versions of the same screen.
     *
     * `band` is null rather than an empty array when the run has logged no turns: five
     * zeroed stats would be a claim about a trainee nobody entered (D-220). `guided`
     * always exists, because the door to logging the first turn has to be there before
     * the first turn is.
     *
     * `$previewed` is a parameter rather than something read back off `$preview`, and that
     * is the whole of the first-turn fix. An empty delta list meant two different things:
     * "this response is the plain GET" and "this response is a preview of a first turn,
     * which has nothing to subtract". The rail advanced its stage and revealed its confirm
     * button on the first, so a run's first turn could never be committed through the
     * guided rail at all - the Trainer had to drop to the raw escape hatch to start the
     * run, and the escape hatch is by definition the path D-53 says must stay reachable
     * rather than default. The two cases are now named.
     *
     * D-3. A failed validation redirects back to this page, and the rail used to come
     * back empty: `show()` passed the default `$staged = []`, so every number the
     * Trainer had typed was replaced by its placeholder and the whole turn had to be
     * entered a second time. The raw escape hatch beside it rehydrates from `old()`
     * per field, so the two paths to the same endpoint disagreed about what to do with
     * input the server had just rejected - and the rail, which asks for more fields than
     * the hatch, lost the most.
     *
     * The rehydration happens here rather than in the view so the GET, the staged
     * preview and the redirect-back share one assembly. It is gated on the rail's own
     * `stage` marker so a failed *raw form* submit does not also repopulate the rail
     * from the eight fields it posted - the hatch already rehydrates itself, and two
     * forms filling each other in would be a second way to be wrong.
     *
     * @param  array<string, mixed>  $staged
     * @param  list<array{direction: string, text: string}>  $preview
     * @return array<string, mixed>
     */
    private function showData(TrainingRun $run, array $staged = [], array $preview = [], bool $previewed = false): array
    {
        if ($staged === []) {
            $old = request()->old();

            if (is_array($old) && array_key_exists('stage', $old)) {
                $staged = array_intersect_key($old, array_flip(self::STAGED_TURN_FIELDS));

                // The flag came back with the input, so a confirm that failed validation
                // comes back to the outcome step rather than to the choice cards.
                $previewed = ($old['previewed'] ?? null) === '1';

                // The bubbles are computed, not stored, so the GET recomputes them from
                // the very input the preview posted. Guarded on the five required stats:
                // a rejected submission rehydrates partial input and previewDeltas()
                // subtracts against each of them. A first turn still yields an empty list,
                // which is exactly the case `$previewed` exists to distinguish from "not a
                // preview at all" (D-1), so the rail can still offer its confirm step.
                if ($previewed && isset($staged['speed'], $staged['stamina'], $staged['power'], $staged['guts'], $staged['wit'])) {
                    $preview = $this->previewDeltas($staged, $this->previousTurn($run, (int) ($staged['turn'] ?? 0)));
                }
            }
        }

        $latest = $run->turnEntries->sortByDesc('turn')->first();

        // R67: which half of the race form is open is server state, read the same way the
        // disclosure control supplies it. `old()` wins inside showData's caller below, so a
        // failed write comes back to the branch the Trainer was filling in.
        $entryMode = request()->query('entry_mode');

        return [
            'run' => $run,
            'entryMode' => in_array($entryMode, ['calendar', 'manual'], true) ? $entryMode : 'calendar',
            // Availability is recorded at write time and applied at read time (ADR-0011 §2), so the
            // picker offers only skills the source says are on `[Global]` **and** named by the client.
            // Without the second half this select would offer `Gluttonous Ruler` — a real English string
            // for a JP-only evolved skill — as if a Global Trainer could learn it. `sp_cost` comes along
            // because the option label states it (FR-D-1).
            'skills' => Skill::query()->availableOnGlobal()->orderBy('name')->get(['id', 'name', 'sp_cost']),
            // The deck picker offers the `[Global]` releases, which is the audience every other picker
            // here already serves (the 2026-09-27 Global-only ruling). The other ~300 records in the
            // catalogue are JP-only and a Global Trainer cannot own them. A card this run already uses
            // is added back by the panel itself, so logging an older deck never shows a slot the
            // Trainer cannot re-select their own card in.
            'deckCards' => SupportCard::query()->whereNotNull('release_global')->orderBy('char_name')->get(),
            'scenarios' => $this->scenarioLabels(),
            'raceSlots' => $this->raceSlotsFor($run),
            'band' => $latest === null ? null : [
                /*
                 * The run's own scenario or null, and the ceilings, handed over together.
                 *
                 * This used to pass `$run->scenarioKey()`, which resolves null to the baseline
                 * scenario, and the band then derived its ceilings from that key — so a run that
                 * named no scenario was rated against URA Finale's +200 on screen while its own
                 * turn form enforced 1200. `ScenarioCaps::forRun()` refuses that bonus for exactly
                 * that reason, and the display path was the one reader that never went through it.
                 *
                 * The scenario is still passed, because it is the truth of the label and the footer
                 * breaks a bonus down from it; what it may no longer do is supply a ceiling. Ceilings
                 * arrive from `forRun()`, the same call the validator makes, so the page cannot show
                 * a number the form would reject. Recorded as KI-47, and deferred here by name in
                 * `docs/design-research/verification/slice-1-stat-ceilings-2026-09-30.md` §3.
                 */
                'scenario' => $run->hasScenario() ? $run->scenario : null,
                'caps' => ScenarioCaps::forRun($run),
                'values' => [
                    'Speed' => $latest->speed,
                    'Stamina' => $latest->stamina,
                    'Power' => $latest->power,
                    'Guts' => $latest->guts,
                    'Wit' => $latest->wit,
                ],
                'skillPoints' => $latest->sp,
            ],
            // The run's mood is where the latest logged turn ended, handed over as the enum so
            // the view renders a tier or says it is unrecorded and never has to map a string
            // back to a case. Null when no turn exists: no turns, no claim about a trainee.
            'currentMood' => $latest?->mood,
            'guided' => [
                'scenario' => $run->scenarioKey(),
                // Stage one asks what the turn did; stage two records how it ended.
                'current' => $previewed ? 'outcome' : 'training',
                'previewed' => $previewed,
                'choices' => $this->turnChoices(),
                'values' => $staged,
                'preview' => $preview,
                'energy' => $staged['energy'] ?? $latest?->energy,
                'mood' => $staged['mood'] ?? $latest?->mood?->value,
                'turn' => (int) ($staged['turn'] ?? $run->nextTurnNumber()),
                'has_previous' => $latest !== null,
                'previous' => $latest,
            ],
        ];
    }

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
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function turnAttributes(array $validated): array
    {
        return array_intersect_key(
            $validated,
            array_flip(['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans']),
        );
    }

    public function update(StoreTrainingRunRequest $request, TrainingRun $run): RedirectResponse
    {
        $run->update($request->validated());

        return redirect()->route('runs.show', $run);
    }

    /**
     * Records what the Trainer did with one calendar slot (US-10, ADR-0003), or
     * creates a manual slot for a race not on the calendar (R56, R61).
     *
     * The write goes through `RaceEntry::create`, not the factory, so the model's saving
     * guards are the ones that decide whether circles or a period index are admissible
     * here; a guard that only runs on the form path would be a guard that bulk writes
     * walk straight past.
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
            ->route('runs.show', $run)
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
            ->route('runs.show', $run)
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
            // The input travels in the session and `showData()` rebuilds the identical
            // screen on the GET, so preview and plain show still share one assembly and
            // cannot drift into two versions of the same page.
            return redirect()->route('runs.show', $run)
                ->withInput($validated + ['previewed' => '1']);
        }

        // The turn row and its failure event are one write (NFR-4): a turn that says `Failure` with no
        // event behind it is the half state, and nothing later reconciles it. A success writes the one
        // row it needs, inside the same transaction.
        DB::transaction(function () use ($run, $validated): void {
            $entry = $run->turnEntries()->create($this->turnAttributes($validated));

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
        });

        return redirect()->route('runs.show', $run);
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
     */
    public function updateTurn(StoreTurnEntryRequest $request, TrainingRun $run, TurnEntry $turn): RedirectResponse
    {
        abort_unless($turn->training_run_id === $run->id, 404);

        $turn->update($request->validated());

        return redirect()->route('runs.show', $run);
    }

    public function destroyTurn(TrainingRun $run, TurnEntry $turn): RedirectResponse
    {
        abort_unless($turn->training_run_id === $run->id, 404);

        $turn->delete();

        return redirect()->route('runs.show', $run);
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

        return redirect()->route('runs.show', $run);
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
            $run->deckSlots()->delete();

            foreach ($request->slotsByCardId() as $position => $cardId) {
                $run->deckSlots()->create([
                    'support_card_id' => $cardId,
                    'slot_position' => (int) $position,
                ]);
            }
        });

        return redirect()->route('runs.show', $run);
    }

    /**
     * The import form: pick the trainee and the scenario, paste or choose the run's CSV (ADR-0017).
     *
     * No costume card here, though `create()` asks for one. `character_card_id` is nullable, and a paper
     * sheet records the trainee rather than which of her forms was equipped; collecting it would offer a
     * field the source cannot fill and invite a guess that then reads as the Trainer's own record.
     */
    public function importForm(): View
    {
        return view('runs.import', $this->importChoices());
    }

    /**
     * Show what the file contains before any of it is written.
     *
     * The same request validates this step and the commit step, so the preview is not a trust boundary:
     * nothing here is carried forward as already-checked, and editing the flashed CSV to something invalid
     * fails on the second POST rather than reaching the action. The rows are handed over as raw strings
     * straight from the parse, which is also what makes a rejected cell point at the row that held it.
     */
    public function importPreview(ImportHistoricalRunRequest $request): View
    {
        $validated = $request->validated();

        return view('runs.import', array_merge($this->importChoices(), [
            'preview' => [
                'csv' => (string) $validated['csv'],
                'turns' => $validated['turns'],
                'run' => $request->only(['umamusume_id', 'scenario', 'status', 'notes']),
            ],
        ]));
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
     * The two lists the import page renders, shared by its form and its preview step.
     *
     * Narrower than `create()`'s trainee query on purpose: the import collects no costume card, so it
     * selects no cards and carries no `unconfirmed` gate. A global roster would list a trainee the run
     * cannot be created for.
     *
     * @return array{umamusumes: Collection<int, Umamusume>, scenarios: array<string, string>}
     */
    private function importChoices(): array
    {
        return [
            'umamusumes' => Umamusume::query()
                ->where('release_status', ReleaseStatus::GlobalReleased->value)
                ->orderBy('name')
                ->get(['id', 'name', 'name_ja']),
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
