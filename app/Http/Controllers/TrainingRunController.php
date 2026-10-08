<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ImportHistoricalRun;
use App\Enums\CardRarity;
use App\Enums\MoodTier;
use App\Enums\RaceEntryStatus;
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
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\ScenarioSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use App\Models\TurnEvents\ShopPurchasePayload;
use App\Models\Umamusume;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\DeckAnalysis;
use App\Services\PageSize;
use App\Services\ScenarioCaps;
use App\Services\SupportCardEffects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
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

    public function show(TrainingRun $run): InertiaResponse
    {
        $run->load(['umamusume', 'turnEntries', 'skills', 'turnEvents', 'deckSlots.supportCard', 'raceEntries.scenarioSlot', 'raceEntries.raceCatalogSlot', 'raceEntries.turnEntry']);

        return Inertia::render('Runs/Show', $this->showData($run));
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
     * Everything `Runs/Show` renders, as plain arrays and scalars: the page payload. No model,
     * collection, enum or payload object rides in it (the Vue components are the contract).
     *
     * `band` is null rather than a zeroed band when the run has logged no turns: five zeroes
     * would be a claim about a trainee nobody entered (D-220).
     *
     * `$previewed` is a parameter rather than something read back off `$preview`, and that is
     * the first-turn fix (D-1). An empty delta list meant two things at once: "this response is
     * the plain GET" and "this is a preview of a first turn, which has nothing to subtract".
     * The rail advanced its stage on the first, so a run's first turn could never be committed
     * through the rail, and D-53 keeps the raw escape hatch reachable rather than default. The
     * two cases are now named.
     *
     * D-3. A failed validation redirects back to this page, so the rail rehydrates from the
     * flashed input: without it every number the Trainer typed was replaced by its placeholder
     * and the whole turn had to be entered a second time. The raw escape hatch behind the rail
     * rehydrates per field already, so the two paths to one endpoint would otherwise disagree
     * about input the server had just refused. Gated on the rail's own `stage` marker so a
     * failed *raw form* submit does not also repopulate the rail from the eight fields it
     * posted: the hatch already rehydrates itself, and two forms filling each other in is a
     * second way to be wrong.
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

        // R67: which half of the race form is open is server state. The flash wins: a failed write
        // comes back to the branch the Trainer was filling in, then the query, then 'calendar'.
        $requestedMode = request()->query('entry_mode');
        $entryMode = in_array($requestedMode, ['calendar', 'manual'], true) ? (string) $requestedMode : 'calendar';
        $entryMode = request()->old('entry_mode', $entryMode) === 'manual' ? 'manual' : 'calendar';

        // The year is address state, clamped here (careerYearForTab refuses anything outside the
        // three career years), so the tabs and the grid cannot disagree about which year it is.
        $calendarYear = $run->careerYearForTab(request('year'));

        return [
            'run' => $this->runHeader($run),
            'turns' => $this->turnRows($run),
            'goals' => $this->goalRows($run),
            'skillGroups' => $this->skillGroupsFor($run),
            'skillCatalog' => $this->skillCatalog(),
            'acquisitionOptions' => $this->acquisitionOptions(),
            'moodOptions' => $this->moodOptions(),
            'scenarios' => $this->scenarioLabels(),
            'statuses' => $this->statusLabels(),
            // KI-47/ADR-0015: the ceilings come from the one owner, the same call the turn
            // validator makes, so the band cannot rate a trainee against a bonus the form rejects.
            'caps' => ScenarioCaps::forRun($run),
            'statOrder' => (array) config('scenarios.stat_order'),
            'baseCap' => (int) config('scenarios.base_cap'),
            'hardCap' => (int) config('scenarios.hard_cap'),
            'gradeBanding' => (array) config('scenarios.grade_banding'),
            'maxObjectiveIndex' => RaceEntry::MAX_OBJECTIVE_INDEX,
            // Five zeroes would be a claim about a trainee nobody entered (D-220).
            'band' => $latest === null ? null : $this->bandFor($run, $latest),
            'strip' => $this->stripFor($run),
            // Where the latest logged turn's mood ended, or null when no turn exists: no turns,
            // no claim about a trainee (D-220).
            'currentMood' => $latest?->mood?->value,
            'gradeMeter' => $this->gradeMeterPayload($run),
            'teamRank' => $this->teamRankPayload($run),
            'spiritBursts' => $this->spiritBurstPayload($run),
            'teamRace' => $this->teamRacePayload($run),
            'epithets' => $this->epithetPayload($run),
            'fatigue' => $this->fatiguePayload($run),
            'calendar' => $this->calendarPayload($run, $calendarYear),
            'deck' => $this->deckPayload($run),
            'racePanel' => $this->racePanelPayload($run, $entryMode, $calendarYear),
            'shop' => $this->shopPayload($run),
            'rail' => $this->railPayload($run, $staged, $preview, $previewed, $latest),
        ];
    }

    /**
     * The run's own facts, flattened for the page header and its four forms. `scenario_label` is
     * null when the run names no scenario: the page then says "No scenario set" rather than
     * borrowing the baseline's name, which is a composition device and not a fact (D-220).
     *
     * @return array{id: int, umamusume_id: int, umamusume_name: string, status: string, status_label: string, scenario: string|null, scenario_label: string|null, has_scenario: bool, notes: string|null, imported_display: string|null, import_source: string|null, turn_count: int, export_csv_url: string, export_json_url: string, cockpit_url: string, update_url: string, destroy_url: string}
     */
    private function runHeader(TrainingRun $run): array
    {
        return [
            'id' => $run->id,
            'umamusume_id' => $run->umamusume_id,
            'umamusume_name' => $run->umamusume->name,
            'status' => $run->status->value,
            'status_label' => $run->status->label(),
            'scenario' => $run->scenario,
            'scenario_label' => $run->hasScenario()
                ? ($this->scenarioLabels()[$run->scenarioKey()] ?? $run->scenarioKey())
                : null,
            'has_scenario' => $run->hasScenario(),
            'notes' => $run->notes,
            // Provenance stated rather than implied (ADR-0017): a run that arrived by file is not
            // the Trainer's own typing. A typed run says nothing.
            'imported_display' => Carbon::make($run->imported_at)?->timezone(config('uma.display_timezone'))->format('M j, Y'),
            'import_source' => $run->import_source,
            // The hatch form opens on the number after the last logged turn.
            'turn_count' => $run->turnEntries->count(),
            'export_csv_url' => route('runs.export', ['run' => $run, 'format' => 'csv']),
            'export_json_url' => route('runs.export', ['run' => $run, 'format' => 'json']),
            // The Career Cockpit (SCREEN-009) descends from this run's URL; this is the record
            // screen's own door to it, so the cockpit is reachable rather than URL-only.
            'cockpit_url' => route('runs.cockpit', $run),
            // The Skills Planner (plan §8 D13) for the same reason: the run's skills live here,
            // so this is the door to the screen that reads them against the build target.
            'skills_planner_url' => route('runs.skills.planner', $run),
            // The Career Result (plan §8 D15): a finished career's summary. The screen itself
            // answers the unfinished case, so the door does not gate on status here — a Trainer
            // who opens it too early is told what is missing rather than finding no door.
            'result_url' => route('runs.result', $run),
            'update_url' => route('runs.update', $run),
            'destroy_url' => route('runs.destroy', $run),
        ];
    }

    /**
     * The turn log, each row with its own form routes. A recorded failure is an event, not a
     * column, so it is folded onto its turn here (ADR-0003): the page's Failed chip is built
     * from exactly this key.
     *
     * @return list<array{id: int, turn: int, speed: int, stamina: int, power: int, guts: int, wit: int, sp: int|null, condition: string|null, energy: int|null, fans: int|null, mood: string|null, update_url: string, destroy_url: string, failure: array{penalty_kind: string, source_name: string}|null}>
     */
    private function turnRows(TrainingRun $run): array
    {
        $failures = $run->turnEvents
            ->filter(fn (TurnEvent $event): bool => $event->event_type === TurnEventType::Failure)
            ->keyBy('turn');

        return $run->turnEntries
            ->map(function (TurnEntry $entry) use ($run, $failures): array {
                $failure = $failures->get($entry->turn);

                return [
                    'id' => $entry->id,
                    'turn' => $entry->turn,
                    'speed' => $entry->speed,
                    'stamina' => $entry->stamina,
                    'power' => $entry->power,
                    'guts' => $entry->guts,
                    'wit' => $entry->wit,
                    'sp' => $entry->sp,
                    'condition' => $entry->condition,
                    'energy' => $entry->energy,
                    'fans' => $entry->fans,
                    'mood' => $entry->mood?->value,
                    'update_url' => route('runs.turns.update', [$run, $entry]),
                    'destroy_url' => route('runs.turns.destroy', [$run, $entry]),
                    'failure' => $failure === null ? null : [
                        'penalty_kind' => (string) ($failure->deltas['penalty_kind'] ?? 'not recorded'),
                        'source_name' => $failure->source_name,
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The client's goal line (O-12): mandatory and special entries in turn order, with the state
     * word the screen renders. A status with no word stays out rather than showing as an empty
     * state the client also does not show.
     *
     * @return list<array{title: string, state: string, year_label: string, turn: int|null}>
     */
    private function goalRows(TrainingRun $run): array
    {
        $entries = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->raceCatalogSlot !== null
                && ($entry->raceCatalogSlot->is_mandatory || $entry->raceCatalogSlot->is_special_race))
            ->sortBy(fn (RaceEntry $entry): array => [
                (int) ($entry->raceCatalogSlot->year ?? 99),
                (int) ($entry->raceCatalogSlot->turn ?? 99),
            ])
            ->values();

        $rows = [];

        foreach ($entries as $entry) {
            $slot = $entry->raceCatalogSlot;

            if ($slot === null || (! $slot->is_mandatory && ! $slot->is_special_race)) {
                continue;
            }

            $state = match ($entry->status) {
                RaceEntryStatus::Completed => 'Cleared',
                RaceEntryStatus::Entered => 'Active',
                RaceEntryStatus::Skipped => 'Failed',
                default => null,
            };

            if ($state === null) {
                continue;
            }

            $rows[] = [
                'title' => $slot->title,
                'state' => $state,
                // The career catalogue's own year word; `year` is not nullable on the row.
                'year_label' => RaceCatalogSlot::YEARS[$slot->year] ?? (string) $slot->year,
                'turn' => $slot->turn,
            ];
        }

        return $rows;
    }

    /**
     * The three acquisition groups, from `SkillAcquisition::cases()` so a fourth state appears
     * without a controller edit. The database value stays the enum's; the heading is its label
     * (`Suggested` reads `Starting` for the seeded skills, KI-33).
     *
     * @return list<array{key: string, label: string, absent: string, skills: list<array{id: int, name: string, sp_cost: int|null, is_unique: bool, turn_acquired: int|null}>}>
     */
    private function skillGroupsFor(TrainingRun $run): array
    {
        $groups = [];

        foreach (SkillAcquisition::cases() as $acquisition) {
            $groups[] = [
                'key' => $acquisition->value,
                'label' => $acquisition->label(),
                'absent' => 'None.',
                'skills' => $run->skills
                    ->filter(fn (Skill $skill): bool => $skill->pivot->status === $acquisition->value)
                    ->map(static fn (Skill $skill): array => [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'sp_cost' => $skill->sp_cost,
                        'is_unique' => $skill->is_unique,
                        'turn_acquired' => $skill->pivot->turn_acquired,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return $groups;
    }

    /**
     * The skill picker's options: only rows the source says are on `[Global]` **and** named by
     * the client (ADR-0011 §2), the same scope the skill search serves. `sp_cost` rides along
     * because the option label states it (FR-D-1).
     *
     * @return list<array{id: int, name: string, sp_cost: int|null}>
     */
    private function skillCatalog(): array
    {
        return Skill::query()
            ->availableOnGlobal()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'sp_cost'])
            ->map(static fn (Skill $skill): array => [
                'id' => $skill->id,
                'name' => $skill->name,
                'sp_cost' => $skill->sp_cost,
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function acquisitionOptions(): array
    {
        return array_map(
            static fn (SkillAcquisition $status): array => ['value' => $status->value, 'label' => $status->label()],
            SkillAcquisition::cases(),
        );
    }

    /**
     * The five client tier words with the directional arrow D-259 makes mandatory: three of the
     * derived fills sit at equal luminance, so the arrow is the only ordinal signal.
     *
     * @return list<array{value: string, arrow: string}>
     */
    private function moodOptions(): array
    {
        return array_map(
            static fn (MoodTier $tier): array => ['value' => $tier->value, 'arrow' => $tier->arrow()],
            MoodTier::cases(),
        );
    }

    /**
     * The stat band's inputs. `capBonus` is the scenario's own breakdown, or null when the run
     * names no scenario: it is a display breakdown only, and the ceilings themselves are the
     * page's `caps` from `ScenarioCaps::forRun()` (KI-47, ADR-0015).
     *
     * @return array{scenario: string|null, capBonus: array<string, int>|null, values: array<string, int>, skillPoints: int|null}
     */
    private function bandFor(TrainingRun $run, TurnEntry $latest): array
    {
        $capBonus = null;

        if ($run->hasScenario()) {
            $capBonus = array_map(
                static fn (mixed $bonus): int => (int) $bonus,
                (array) config('scenarios.scenarios.'.$run->scenarioKey().'.cap_bonus'),
            );
        }

        return [
            // The run's own scenario or null: the label and the footer breakdown read it, and it
            // may not supply a ceiling (KI-47).
            'scenario' => $run->hasScenario() ? $run->scenario : null,
            'capBonus' => $capBonus,
            'values' => [
                'Speed' => $latest->speed,
                'Stamina' => $latest->stamina,
                'Power' => $latest->power,
                'Guts' => $latest->guts,
                'Wit' => $latest->wit,
            ],
            'skillPoints' => $latest->sp,
        ];
    }

    /**
     * The Resources strip: the widget list, label and panel flag from the matrix (D-240), the
     * run's own numbers from `stripValues()`, and one null per scenario resource that has no
     * column yet, so the strip prints its own "N/A" rather than a default (D-220).
     *
     * @return array{widgets: list<string>, scenarioLabel: string, hasGradeObjectives: bool, declared: bool, values: array<string, int|string|null>}
     */
    private function stripFor(TrainingRun $run): array
    {
        $scenarioKey = $run->scenarioKey();

        return [
            'widgets' => array_values((array) config('scenarios.scenarios.'.$scenarioKey.'.widgets')),
            'scenarioLabel' => (string) config('scenarios.scenarios.'.$scenarioKey.'.label'),
            'hasGradeObjectives' => config('scenarios.scenarios.'.$scenarioKey.'.panels.grade_objectives') === true,
            // The baseline key is what the strip composes from; whether the run declared a
            // scenario is a separate fact and the only one that lets the caption name it (D-220).
            'declared' => $run->hasScenario(),
            'values' => [
                ...$run->stripValues(),
                'team_rank' => null,
                'bursts' => null,
                'grade_points' => null,
                'shop_coins' => null,
            ],
        ];
    }

    /**
     * The race calendar's own inputs. `yearTabs` is built here because the tabs are navigations:
     * `fullUrlWithQuery` keeps the other query parameters, so back does not drop the tab. The
     * Finale year has no tab (it sits outside the 24-turn grid).
     *
     * @return array{show: bool, cells: list<array<string, mixed>>, year: int, yearTabs: list<array{year: int, label: string, url: string}>, yearWord: string|null, nextTurn: int|null}
     */
    private function calendarPayload(TrainingRun $run, int $calendarYear): array
    {
        // Self-gating on the matrix, not on a scenario name: an empty grid would still claim the
        // scenario has a calendar (D-221, D-241, gate G-34).
        $show = $run->composesPanel('race_calendar');

        $yearTabs = [];

        foreach (RaceCatalogSlot::YEARS as $value => $label) {
            if ($value === RaceCatalogSlot::YEAR_FINALE) {
                continue;
            }

            $yearTabs[] = [
                'year' => $value,
                'label' => $label.' Year',
                'url' => request()->fullUrlWithQuery(['year' => $value]),
            ];
        }

        // The turn being decided carries its own year, so the outline belongs to whichever tab
        // holds it rather than to the year of the last logged turn.
        $nextTurn = $run->hasScenario() ? $run->nextTurnToPlay() : null;

        return [
            'show' => $show,
            'cells' => $show ? $run->calendarCells($calendarYear) : [],
            'year' => $calendarYear,
            'yearTabs' => $yearTabs,
            'yearWord' => RaceCatalogSlot::YEARS[$calendarYear] ?? null,
            'nextTurn' => $nextTurn !== null && $calendarYear === $nextTurn['year'] ? $nextTurn['turn'] : null,
        ];
    }

    /**
     * The Grade Point meter. An empty `objectives` list is its off state, so no scenario name is
     * passed (D-221, D-241, gate G-34). `earned` stays null when no honest figure exists (KI-10)
     * and is never 0, which would claim the trainee stands on nothing.
     *
     * @return array{objectives: list<array{index: int, name: string, required: int}>, current: int|null, earned: int|null, unpricedCount: int, periods: list<array{index: int, earned: int|null, unpriced: int}>, unassignedCount: int, rotationTurns: int|null}
     */
    private function gradeMeterPayload(TrainingRun $run): array
    {
        $rotation = config('scenarios.scenarios.'.$run->scenarioKey().'.shop.rotation_turns');

        return [
            'objectives' => $run->gradeObjectives(),
            'current' => $run->currentPeriodPosition(),
            'earned' => $run->gradeEarned(),
            'unpricedCount' => $run->gradeUnpricedCount(),
            // Each deadline carries its own sum only; an absent period is null, not 0 (D-232, D-220).
            'periods' => array_map(
                static fn (array $row): array => [
                    'index' => $row['index'],
                    'earned' => $row['earned'],
                    'unpriced' => $row['unpriced'],
                ],
                $run->gradePeriods(),
            ),
            'unassignedCount' => $run->gradeUnassignedCount(),
            // The rotation is config's or null; the meter only prints it when it exists (D-232, D-240).
            'rotationTurns' => $rotation === null ? null : (int) $rotation,
        ];
    }

    /**
     * The Team Rank gauge. The ladder arrives pre-flattened from config so the view holds no
     * config lookup (D-240), and `level` is derived through the config mapping, never stored: a
     * rank above the top rung has no level and the panel must not hand it one.
     *
     * @return array{enabled: bool, current: array{rank: string, level: int|null}|null, ladder: list<array{rank: string, level: int}>}
     */
    private function teamRankPayload(TrainingRun $run): array
    {
        $enabled = $run->composesPanel('team_rank_ladder');

        $ladder = [];

        foreach ((array) config('scenarios.scenarios.'.$run->scenarioKey().'.team_rank_ladder') as $rung) {
            foreach ((array) ($rung['ranks'] ?? []) as $letter) {
                $ladder[] = ['rank' => (string) $letter, 'level' => (int) $rung['level']];
            }
        }

        $reported = $enabled ? $run->latestTeamRank() : null;

        return [
            'enabled' => $enabled,
            'current' => $reported === null ? null : [
                'rank' => $reported->rank,
                'level' => $run->facilityLevel($reported->rank),
            ],
            'ladder' => $ladder,
        ];
    }

    /**
     * The Spirit Burst roster. `enabled` is the scenario's widget membership, never a slug
     * (D-221, gate G-34): `state` is the enum's opaque backing value and `stateLabel` is its
     * label, because no Global capture names these states (D-20).
     *
     * @return array{enabled: bool, roster: list<array{teammate: string, state: string, stateLabel: string}>}
     */
    private function spiritBurstPayload(TrainingRun $run): array
    {
        $enabled = in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true);

        $roster = [];

        if ($enabled) {
            foreach ($run->spiritBurstRoster() as $row) {
                $roster[] = [
                    'teammate' => $row['teammate'],
                    'state' => $row['state']->value,
                    'stateLabel' => $row['state']->label(),
                ];
            }
        }

        return ['enabled' => $enabled, 'roster' => $roster];
    }

    /**
     * The Team Race panel. Title and tier nulls travel as nulls: the component renders its own
     * "not recorded" copy for each (G-33).
     *
     * @return array{enabled: bool, guidance: int, entries: list<array{title: string|null, tier: string|null, circles: int|null, placement: int|null}>}
     */
    private function teamRacePayload(TrainingRun $run): array
    {
        $enabled = $run->composesPanel('team_race');

        $entries = [];

        if ($enabled) {
            foreach ($run->raceEntries as $entry) {
                $slot = $entry->scenarioSlot;

                if ($slot === null || $slot->kind !== 'team_race') {
                    continue;
                }

                $entries[] = [
                    'title' => $slot->title,
                    'tier' => $slot->tier,
                    'circles' => $entry->circles,
                    'placement' => $entry->placement,
                ];
            }
        }

        return [
            'enabled' => $enabled,
            // The source's own margin, transcribed into config: three circles is a safety margin,
            // not a win condition.
            'guidance' => (int) config('scenarios.scenarios.'.$run->scenarioKey().'.team_race.circles_guidance', 0),
            'entries' => $entries,
        ];
    }

    /**
     * The epithet checklist. `rows` is `epithetProgress()` verbatim: its three states are derived
     * against the config route table there, and `unverifiable` must not be folded into `open` by
     * a reshape (D-220, D-256).
     *
     * @return array{enabled: bool, seenRaceTitles: list<string>, rows: list<array{route: string, epithet: string, reward: string, state: string, missing: list<string>, note: string|null}>}
     */
    private function epithetPayload(TrainingRun $run): array
    {
        return [
            'enabled' => $run->composesPanel('epithet_routes'),
            'seenRaceTitles' => $run->completedRaceTitles(),
            'rows' => $run->epithetProgress(),
        ];
    }

    /**
     * The Race Fatigue chip. It gates on the epithet flag, the same flag as the checklist and
     * never a race panel (the screen's own rule, kept, G-33). The word is the payload's band
     * derivation, and `hide_after` is mapped out of its storage name: an unmapped key still
     * renders as itself (KI-18).
     *
     * @return array{enabled: bool, consecutiveRaces: int|null, riskWord: string|null, hideAfterLabel: string|null}
     */
    private function fatiguePayload(TrainingRun $run): array
    {
        $enabled = $run->composesPanel('epithet_routes');
        $fatigue = $enabled ? $run->latestFatigue() : null;

        /** @var array<string, string> $labels */
        $labels = ['late_december' => 'late December'];
        $hideAfter = config('scenarios.scenarios.'.$run->scenarioKey().'.race_fatigue.hide_after');

        return [
            'enabled' => $enabled,
            'consecutiveRaces' => $fatigue?->consecutiveRaces,
            'riskWord' => $fatigue?->riskWord(),
            'hideAfterLabel' => $hideAfter === null ? null : ($labels[(string) $hideAfter] ?? (string) $hideAfter),
        ];
    }

    /**
     * The deck panel. `options` is the Global releases plus any card this run already uses, so a
     * Trainer logging an older deck is never shown a slot they cannot re-select their own card
     * in; the effect facts sit on the equipped list, not in the picker.
     *
     * @return array{equipped: list<array{position: int, card_url: string, card_name: string, slot_word: string, rarity_type: string, scenario_link: bool, effects: list<array{effect_id: int, name: string|null, display: string}>}>, slots: list<array{position: int, label: string, selected: string, selected_name: string|null, open_url: string}>, options: list<array{id: int, label: string}>, openSlot: int, action: string}
     */
    private function deckPayload(TrainingRun $run): array
    {
        $slots = $run->deckSlots->keyBy('slot_position');
        $scenarioKey = $run->scenarioKey();
        // The dictionary is read once for the panel, not once per slot: six equipped cards ask
        // the same 35 rows the same question.
        $effectNames = SupportCardEffects::dictionary();

        $offered = SupportCard::query()
            ->whereNotNull('release_global')
            ->orderBy('char_name')
            ->get();

        foreach ($run->deckSlots as $slot) {
            $card = $slot->supportCard;

            if (! $offered->contains('id', $card->id)) {
                $offered->push($card);
            }
        }

        /** @var Collection<int, SupportCard> $offered */
        $offered = $offered->unique('id')->sortBy(['type', 'char_name', 'title_en'])->values();

        $equipped = [];

        foreach (DeckSlot::POSITIONS as $position) {
            $slot = $slots->get($position);

            if ($slot === null) {
                continue;
            }

            $card = $slot->supportCard;

            $equipped[] = [
                'position' => $position,
                'card_url' => route('support-cards.show', $card),
                'card_name' => $card->displayName(),
                // Position is the slot's own identity, and six is the friend slot whatever card
                // sits in it (ADR-0014 correction 1).
                'slot_word' => $position === DeckSlot::MAX_POSITION ? 'Friends' : 'Slot '.$position,
                'rarity_type' => $card->rarityWord().' '.$card->typeLabel(),
                // Derived, never stored (ADR-0014 correction 3).
                'scenario_link' => $card->isScenarioLink($scenarioKey),
                'effects' => SupportCardEffects::atCap($card, $effectNames),
            ];
        }

        $openSlot = $this->openDeckSlot();
        $formSlots = [];

        foreach (DeckSlot::POSITIONS as $position) {
            // Rehydrated from `old()` so a rejected submission does not wipe the six picks the
            // Trainer just made (D-3).
            $selected = request()->old("deck.{$position}.support_card_id", (string) $slots->get($position)?->support_card_id);
            $chosen = (string) $selected === '' ? null : $offered->firstWhere('id', (int) $selected);

            $formSlots[] = [
                'position' => $position,
                'label' => $position === DeckSlot::MAX_POSITION ? 'Slot 6 · Friends' : 'Slot '.$position,
                'selected' => (string) $selected,
                'selected_name' => $chosen?->displayName(),
                // Built server-side so the other query parameters survive the slot switch.
                'open_url' => request()->fullUrlWithQuery(['deck_slot' => $position]),
            ];
        }

        return [
            'equipped' => $equipped,
            'slots' => $formSlots,
            'options' => $offered
                ->map(static fn (SupportCard $card): array => [
                    'id' => $card->id,
                    'label' => $card->displayName().' · '.$card->rarityWord().' '.$card->typeLabel(),
                ])
                ->all(),
            'openSlot' => $openSlot,
            'action' => route('runs.deck.sync', $run),
        ];
    }

    /**
     * The deck slot the picker opens: the requested one, else the one a rejected save landed an
     * error on, else the first. Resolved server-side because a client ref cannot reopen a slot
     * after the redirect back from a rejected submit, which is exactly when it has to reopen.
     */
    private function openDeckSlot(): int
    {
        $requested = (int) request()->query('deck_slot');

        if (in_array($requested, DeckSlot::POSITIONS, true)) {
            return $requested;
        }

        $errors = request()->session()->get('errors');

        if ($errors instanceof ViewErrorBag) {
            foreach (DeckSlot::POSITIONS as $position) {
                if ($errors->has("deck.{$position}.support_card_id")) {
                    return $position;
                }
            }
        }

        return DeckSlot::POSITIONS[0];
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
                'run_url' => route('runs.show', $run),
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
     * The race panel. Every display string in `entries` composes here, including the fallbacks
     * the screen reads ('no grade', 'turn not named', 'circles not read', 'no period'): the
     * component only renders them.
     *
     * @return array{showUrl: string, racesUrl: string, composesRaceCalendar: bool, composesTeamRace: bool, composesGradeObjectives: bool, entryMode: string, year: int, yearLabel: string, calendarSlots: list<array{id: int, label: string, tier: string}>, manualSlots: list<array{id: int, title: string}>, turns: list<array{id: int, turn: int}>, raceStatuses: list<string>, maxCircles: int, objectives: list<array{index: int, name: string}>, entries: list<array{id: int, title: string, status: string, trainer_entered: bool, tier: string, placement: string, turn: string, circles: string, period: string}>, old: array<string, mixed>}
     */
    private function racePanelPayload(TrainingRun $run, string $entryMode, int $calendarYear): array
    {
        $composesTeamRace = $run->composesPanel('team_race');
        $composesGradeObjectives = $run->composesGradeObjectives();

        // The calendar list is the career catalogue for the year this screen shows: a list built
        // from `scenario_slots` would offer a Senior G1 to a Trainer sitting in Junior.
        $calendarSlots = $run->calendarRaceSlots($calendarYear)
            ->map(static fn (RaceCatalogSlot $slot): array => [
                'id' => $slot->id,
                // The picker names the half-month and grade, so a race cannot be chosen on its
                // name alone; 'no grade' is the Blade's own fallback, kept as one string.
                'label' => $slot->title.' · '.$slot->slot_label.' · '.($slot->tier ?? 'no grade'),
                'tier' => $slot->tier ?? '',
            ])
            ->all();

        $manualSlots = $this->raceSlotsFor($run)
            ->where('kind', 'free_race')
            ->map(static fn (ScenarioSlot $slot): array => ['id' => $slot->id, 'title' => $slot->title])
            ->values()
            ->all();

        $entries = $run->raceEntries
            ->sortBy(fn (RaceEntry $entry): int => $entry->scenarioSlot->sort_order ?? $entry->raceCatalogSlot->sort_order ?? PHP_INT_MAX)
            ->values()
            ->map(function (RaceEntry $entry): array {
                $turn = $entry->turnEntry;

                return [
                    'id' => $entry->id,
                    'title' => $entry->raceCatalogSlot->title ?? $entry->scenarioSlot->title ?? 'a race with no calendar row',
                    'status' => $entry->status->value,
                    'trainer_entered' => $entry->scenarioSlot?->isFreeRace() === true,
                    'tier' => $entry->tierKey() ?? 'no grade',
                    // R69: the ordinal, teens included.
                    'placement' => $entry->placementOrdinal(),
                    // KI-17: the Trainer names the turn; nothing guesses it from a race date.
                    'turn' => $turn === null ? 'turn not named' : 'turn '.$turn->turn,
                    'circles' => $entry->circles === null ? 'circles not read' : $entry->circles.' circles',
                    'period' => $entry->objective_index === null ? 'no period' : 'period '.$entry->objective_index,
                ];
            })
            ->all();

        return [
            'showUrl' => route('runs.show', $run),
            'racesUrl' => route('runs.races.store', $run),
            'composesRaceCalendar' => $run->composesPanel('race_calendar'),
            'composesTeamRace' => $composesTeamRace,
            'composesGradeObjectives' => $composesGradeObjectives,
            'entryMode' => $entryMode,
            'year' => $calendarYear,
            'yearLabel' => RaceCatalogSlot::YEARS[$calendarYear] ?? (string) $calendarYear,
            // Both lists of slots the calendar branch can name, and the turns it can tie a race to.
            'calendarSlots' => $calendarSlots,
            'manualSlots' => $manualSlots,
            'turns' => $run->turnEntries
                ->map(static fn (TurnEntry $turn): array => ['id' => $turn->id, 'turn' => $turn->turn])
                ->values()
                ->all(),
            'raceStatuses' => array_map(static fn (RaceEntryStatus $status): string => $status->value, RaceEntryStatus::cases()),
            'maxCircles' => RaceEntry::MAX_CIRCLES,
            'objectives' => array_map(static fn (array $row): array => [
                'index' => $row['index'],
                'name' => $row['name'],
            ], $run->gradeObjectives()),
            'entries' => $entries,
            // The form opens on the flashed input after a rejected write.
            'old' => (array) request()->old(),
        ];
    }

    /**
     * The shop panel. The catalogue and purchases are read only when the scenario composes a
     * shop: `purchasePayload()` validates a stored row against the run's own catalogue and
     * throws for a scenario with no shop, so the gate is load-bearing (D-226, ADR-0003).
     *
     * @return array{panelsShop: bool, purchaseUrl: string, updateUrl: string, rotationTurns: int, maxCopies: int, resetsInLabel: string|null, catalogue: list<array{name: string, label: string}>, purchases: list<array{item: string, effect: string, cost: string}>, spendTotal: string, turnDefault: int, umamusumeId: int, statusValue: string, scenario: string, currentObjectiveIndex: int|null, shopResetsIn: int|null}
     */
    private function shopPayload(TrainingRun $run): array
    {
        $shop = $run->composesShop();
        $scenarioKey = $run->scenarioKey();

        $catalogue = [];
        $purchases = [];

        if ($shop) {
            // The form's option list is the catalogue the payload validates against, so the
            // choice offered and the choice accepted cannot drift apart.
            foreach (ShopPurchasePayload::catalogueFor($run) as $name => $row) {
                $catalogue[] = [
                    'name' => $name,
                    'label' => $name.' · '.number_format($row['cost']).' coins',
                ];
            }

            foreach ($run->turnEvents as $event) {
                $purchase = $event->purchasePayload();

                if ($purchase === null) {
                    continue;
                }

                $purchases[] = [
                    'item' => $purchase->item,
                    'effect' => $purchase->effect,
                    'cost' => number_format($purchase->cost),
                ];
            }
        }

        $resets = $run->shop_resets_in;
        $rotation = config('scenarios.scenarios.'.$scenarioKey.'.shop.rotation_turns');

        return [
            'panelsShop' => $shop,
            'purchaseUrl' => route('runs.purchases.store', $run),
            'updateUrl' => route('runs.update', $run),
            // Config is the only owner of the rotation bounds (D-240); the panel only prints them.
            'rotationTurns' => $rotation === null ? 0 : (int) $rotation,
            'maxCopies' => (int) config('scenarios.scenarios.'.$scenarioKey.'.shop.max_copies_per_item', 0),
            // Entered, never computed: 0 would say the lineup changes next turn (D-232, D-220).
            'resetsInLabel' => $resets === null ? null : 'resets in '.$resets.' '.($resets === 1 ? 'turn' : 'turns'),
            'catalogue' => $catalogue,
            'purchases' => $purchases,
            // A sum of entered costs, never a balance: the earning side is not stored (D-232).
            'spendTotal' => number_format($run->shopSpendTotal()),
            'turnDefault' => (int) $run->turnEntries->max('turn') ?: 1,
            // The rotation form is the shared `runs.update` write, so every field it is not
            // editing is carried through or the request blanks it.
            'umamusumeId' => $run->umamusume_id,
            'statusValue' => $run->status->value,
            'scenario' => $run->scenario ?? '',
            'currentObjectiveIndex' => $run->current_objective_index,
            'shopResetsIn' => $resets,
        ];
    }

    /**
     * The guided rail. `$previewed` is the response's own marker, never a read of `$preview`: a
     * first turn previews to an empty list and still has to offer its confirm step (D-1, D-51).
     * `previous` is the row the typed numbers are compared against, as placeholders only: an
     * input arriving pre-filled would assert the stat did not change (D-220).
     *
     * @param  array<string, mixed>  $staged
     * @param  list<array{direction: string, text: string}>  $preview
     * @return array{def: array<string, mixed>, declared: bool, current: string, previewed: bool, choices: list<array<string, mixed>>, selected: string|null, values: array<string, mixed>, preview: list<array{direction: string, text: string}>, energy: int|null, mood: string|null, turn: int, has_previous: bool, previous: array<string, int|null>|null, action: string}
     */
    private function railPayload(TrainingRun $run, array $staged, array $preview, bool $previewed, ?TurnEntry $latest): array
    {
        return [
            // The scenario's resolved config, so the rail orders its steps from data and branches
            // on flags rather than on a slug (D-240, G-33).
            'def' => (array) config('scenarios.scenarios.'.$run->scenarioKey()),
            // The baseline key orders the steps either way; this decides only whether the rail may
            // name the scenario out loud (D-220).
            'declared' => $run->hasScenario(),
            // Stage one asks what the turn did; stage two records how it ended.
            'current' => $previewed ? 'outcome' : 'training',
            'previewed' => $previewed,
            'choices' => $this->turnChoices(),
            'selected' => isset($staged['choice']) ? (string) $staged['choice'] : null,
            'values' => $staged,
            'preview' => $preview,
            'energy' => isset($staged['energy']) ? (int) $staged['energy'] : $latest?->energy,
            'mood' => isset($staged['mood']) ? (string) $staged['mood'] : $latest?->mood?->value,
            'turn' => (int) ($staged['turn'] ?? $run->nextTurnNumber()),
            'has_previous' => $latest !== null,
            'previous' => $latest === null ? null : [
                'speed' => $latest->speed,
                'stamina' => $latest->stamina,
                'power' => $latest->power,
                'guts' => $latest->guts,
                'wit' => $latest->wit,
                'sp' => $latest->sp,
                'energy' => $latest->energy,
                'fans' => $latest->fans,
            ],
            'action' => route('runs.turns.store', $run),
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

        return redirect()->route('runs.show', $run)->with('status', 'Run updated.');
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
            ->back(fallback: route('runs.show', $run))
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
            ->back(fallback: route('runs.show', $run))
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
        });

        return redirect()->route('runs.show', $run)
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

        return redirect()->back(fallback: route('runs.show', $run))->with('status', 'Turn '.$turn->turn.' updated.');
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

        return redirect()->route('runs.show', $run)->with('status', 'Turn '.$turn->turn.' removed.');
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

        return redirect()->route('runs.show', $run)->with('status', 'Skill status saved.');
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
            // Read the flags before the delete. The run screen's deck panel posts six cards and no
            // flag, and the flag belongs to the slot, so a re-save from a surface that offers no
            // ownership control keeps what the slot already recorded rather than erasing it (ADR-0023).
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
