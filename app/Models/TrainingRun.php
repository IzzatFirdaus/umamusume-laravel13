<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PerformanceType;
use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Enums\SpiritBurstState;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\TurnEvents\RaceFatiguePayload;
use App\Models\TurnEvents\ShopPurchasePayload;
use App\Models\TurnEvents\TeamRankPayload;
use Database\Factories\TrainingRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * One training run for one Umamusume; owns its turns and skill states, both
 * cascade-deleted with it (PRD FR-C-1, C-4).
 *
 * @property int $id
 * @property int $umamusume_id
 * @property int|null $character_card_id the costume-card form this run started on
 *                                       (FR-C-1 as amended by ADR-0008); null when
 *                                       the Trainer named only the trainee, which is
 *                                       a complete run rather than a missing field
 * @property string|null $scenario
 * @property RunStatus $status
 * @property int|null $inheritance_parent_a_id
 * @property int|null $inheritance_parent_b_id
 * @property array<array-key, mixed>|null $legacy_selection the Legacy Select read-back as the
 *                                                          Trainer recorded it (D-260, D-268,
 *                                                          ADR-0010); null when they never opened
 *                                                          that screen. `legacySelection()` is the
 *                                                          typed view of this bag
 * @property string|null $notes
 * @property int|null $current_objective_index the Grade Point period the Trainer
 *                                             reports as live (1..4, US-10); null
 *                                             until they say, which the meter shows
 *                                             as no period rather than as zero
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $shop_resets_in turns until the shop rotation, as the Trainer
 *                                    reports it; null renders the N/A disclosure and is
 *                                    never computed from the turn number (D-232)
 * @property array<array-key, mixed>|null $build_target the build target as the Trainer entered
 *                                                      it (FR-F-1, ADR-0020 §2); null when they
 *                                                      entered none, which the advisor reads as
 *                                                      no target rather than as an empty one.
 *                                                      `buildTarget()` is the typed view of this bag
 * @property-read Collection<int, TurnEntry> $turnEntries
 * @property-read Collection<int, TurnEvent> $turnEvents
 * @property-read Collection<int, RaceEntry> $raceEntries
 * @property-read Collection<int, Skill> $skills
 * @property-read Umamusume $umamusume
 * @property-read CharacterCard|null $characterCard
 * @property-read Veteran|null $veteran the library row built from this run, or null when none was
 *                                       saved (`LegacyCompareRequest::runsInOrder()` reads it so a
 *                                       run with a Legacy selection stays comparable before it is
 *                                       filed)
 */
#[Fillable(['umamusume_id', 'character_card_id', 'scenario', 'status', 'inheritance_parent_a_id', 'inheritance_parent_b_id', 'legacy_selection', 'build_target', 'notes', 'current_objective_index', 'shop_resets_in', 'imported_at', 'import_source'])]
class TrainingRun extends Model
{
    /** @use HasFactory<TrainingRunFactory> */
    use HasFactory;

    /**
     * Turns in one career year: the client's own grid, twelve months times Early and
     * Late. A three-year career is therefore 72 turns, which is what `nextTurnToPlay()`
     * reads as the end of the career.
     */
    public const TURNS_PER_YEAR = 24;

    /**
     * The period the Trainer reports as live is entered, not inferred (D-270), and it
     * is only meaningful on a run whose scenario composes grade objectives. The HTTP
     * boundary validates the same range; this guard is what keeps a writer that is
     * not a Form Request honest.
     */
    protected static function booted(): void
    {
        static::saving(function (self $run): void {
            if ($run->current_objective_index !== null) {
                self::assertGradePeriod($run->current_objective_index, $run, 'current_objective_index');
            }

            $resets = $run->shop_resets_in;

            if ($resets === null) {
                return;
            }

            // Bound by the scenario's own rotation period, which is config and sourced
            // (`shop.rotation_turns`), rather than by the column's numeric width. A
            // countdown longer than the rotation is a misread, not a future fact.
            $rotation = $run->hasScenario()
                ? (int) config('scenarios.scenarios.'.$run->scenarioKey().'.shop.rotation_turns', 0)
                : 0;

            if ($rotation === 0) {
                throw new \InvalidArgumentException(
                    "Scenario [{$run->scenarioKey()}] has no shop, so it has no rotation countdown.",
                );
            }

            if ($resets < 0 || $resets > $rotation) {
                throw new \InvalidArgumentException(
                    "shop_resets_in [{$resets}] is outside this scenario's rotation of {$rotation} turns.",
                );
            }
        });
    }

    /**
     * The one rule both period columns answer to: a number from 1 to 4, on a run
     * whose scenario composes the Grade Point panel.
     *
     * Kept here rather than twice, because the finish's period and the run's live
     * period are the same fact seen from two ends, and a rule that exists in two
     * copies is a rule that drifts.
     */
    public static function assertGradePeriod(?int $index, ?self $run, string $column): void
    {
        if ($index === null) {
            return;
        }

        if ($index < 1 || $index > RaceEntry::MAX_OBJECTIVE_INDEX) {
            throw new \InvalidArgumentException(
                "{$column} [{$index}] is not one of the four Grade Point periods (1..4).",
            );
        }

        if ($run === null) {
            throw new \InvalidArgumentException("{$column} needs a run to belong to.");
        }

        if (! $run->composesGradeObjectives()) {
            throw new \InvalidArgumentException(
                "Scenario [{$run->scenarioKey()}] composes no Grade Point objectives, so it has no period to record.",
            );
        }
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    /**
     * The costume-card form this run started on, or null when the Trainer named
     * only the trainee (FR-C-1 as amended by ADR-0008).
     *
     * @return BelongsTo<CharacterCard, $this>
     */
    public function characterCard(): BelongsTo
    {
        return $this->belongsTo(CharacterCard::class);
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function inheritanceParentA(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class, 'inheritance_parent_a_id');
    }

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function inheritanceParentB(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class, 'inheritance_parent_b_id');
    }

    /**
     * The Legacy Select read-back as the Trainer recorded it, or null when they never
     * opened that screen (D-260, D-268, ADR-0010).
     *
     * Null and an empty payload are different statements, and the render path has to tell them
     * apart: "no Legacy Select on this run" is a disclosure, and "a Legacy Select with no Legacies
     * in it" would be a claim about a game screen that cannot be reached with zero ancestors.
     *
     * A malformed stored payload throws rather than reading as nothing. The shape is validated on
     * the way in by `LegacySelectionPayload`, so a row that fails here means a write got past it,
     * which is the fact worth an exception and not a blank panel.
     */
    public function legacySelection(): ?LegacySelectionPayload
    {
        if ($this->legacy_selection === null) {
            return null;
        }

        return LegacySelectionPayload::fromArray($this->legacy_selection);
    }

    /**
     * The build target as the Trainer entered it, or null when they entered none.
     *
     * Null is the whole point of the distinction: the advisor's contract has a case for "no target
     * set" that names the absence and falls back to Energy-only guidance, and returning an empty
     * payload here would make that case unreachable and let the advisor rank against zeroes the
     * Trainer never typed (`ADR-0020` §2, `SCREEN-005`).
     *
     * As with the Legacy payload, a malformed stored value throws rather than reading as nothing:
     * the shape is validated on the way in by `BuildTargetPayload`, so a row that fails here means
     * a write got past it, which is the fact worth an exception.
     */
    public function buildTarget(): ?BuildTargetPayload
    {
        if ($this->build_target === null) {
            return null;
        }

        return BuildTargetPayload::fromArray($this->build_target);
    }

    /**
     * @return HasMany<TurnEntry, $this>
     */
    public function turnEntries(): HasMany
    {
        return $this->hasMany(TurnEntry::class)->orderBy('turn');
    }

    /**
     * @return HasMany<TurnEvent, $this>
     */
    public function turnEvents(): HasMany
    {
        return $this->hasMany(TurnEvent::class)->orderBy('turn');
    }

    /**
     * @return HasMany<RaceEntry, $this>
     */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }

    /**
     * The Veteran-library row built from this run, or null when the Trainer never saved one.
     *
     * A `HasOne` rather than the reverse of `Veteran::trainingRun()` because the read side is what
     * the Legacy Lab's compare surface needs: a run can hold a Legacy selection and never have been
     * filed in the library, and that run still has to be comparable. The foreign key is unique
     * (`ARCHITECTURE-ESSENTIALS.md`), so this can never return more than one row.
     *
     * @return HasOne<Veteran, $this>
     */
    public function veteran(): HasOne
    {
        return $this->hasOne(Veteran::class);
    }

    /**
     * @return BelongsToMany<Skill, $this, RunSkill, 'pivot'>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'run_skills')
            ->withPivot(['status', 'turn_acquired'])
            ->using(RunSkill::class);
    }

    /**
     * @return HasMany<DeckSlot, $this>
     */
    public function deckSlots(): HasMany
    {
        return $this->hasMany(DeckSlot::class)->orderBy('slot_position');
    }

    public function setSkillStatus(Skill $skill, SkillAcquisition $status, ?int $turnAcquired = null): void
    {
        $this->skills()->syncWithoutDetaching([
            $skill->id => [
                'status' => $status->value,
                'turn_acquired' => $turnAcquired,
            ],
        ]);
    }

    /**
     * The composition-matrix key this run's scenario-aware components read.
     *
     * A run with no scenario resolves to the baseline rather than to null, because
     * every one of those components needs a descriptor to render from and a strip
     * with no scenario would have nothing to compose. The baseline is named in
     * `config('scenarios.php')`, not here, so it stays a fact about the matrix
     * (owner ruling 2026-09-27).
     */
    public function scenarioKey(): string
    {
        // `?:` rather than `??`, for the same reason as hasScenario(): '' is a blank, and a blank must
        // resolve to the baseline instead of being handed to `config('scenarios.scenarios.')` as a key.
        return $this->scenario ?: (string) config('scenarios.baseline');
    }

    /**
     * The numbers the resource strip shows, keyed by widget.
     *
     * Energy and Fans are the latest logged turn's end-of-turn totals, never sums
     * of deltas (ADR-0003), and null when the run has logged no turn that carries
     * them. A scenario-only resource with no column yet is simply absent, so the
     * strip shows it as unrecorded rather than inventing a starting value.
     *
     * @return array<string, int|null>
     */
    public function stripValues(): array
    {
        // The relation is read rather than re-queried when the caller already loaded it, which both hot
        // screens do (`DashboardController::activeCareer()` and `TrainingRunController::show()` both list
        // `turnEntries` in their eager load). The two queries this used to issue unconditionally — one for
        // the count, one for the last turn — are the N+1 shape `ARCHITECTURE-ESSENTIALS.md` §6 tells the
        // codebase to load against.
        if ($this->relationLoaded('turnEntries')) {
            // `sortByDesc`, not `last()`: the loaded collection's order is the relation's own, and an
            // assumption about it is exactly the kind of thing that silently reports a stale Energy.
            $latest = $this->turnEntries->sortByDesc('turn')->first();

            return [
                'turn' => $this->turnEntries->count(),
                'energy' => $latest?->energy,
                'fans' => $latest?->fans,
            ];
        }

        // `reorder`, not `latest`: the relation already carries an ascending `turn`
        // order, and appending `latest` yields `turn asc, turn desc`, which puts the
        // *first* turn first and silently reports a stale Energy and Fans.
        $latest = $this->turnEntries()->reorder('turn', 'desc')->first();

        return [
            'turn' => $this->turnEntries()->count(),
            'energy' => $latest?->energy,
            'fans' => $latest?->fans,
        ];
    }

    /**
     * Whether the Trainer has actually chosen a scenario.
     *
     * Distinct from `scenarioKey()`, which falls back to the baseline so the
     * resource strip always has generic widgets to compose. The goal panels are not
     * generic — they name particular races and particular deadlines — so they read
     * this instead, and a run with no scenario shows neither rather than borrowing
     * the baseline's schedule (D-220, D-221).
     */
    public function hasScenario(): bool
    {
        // `filled`, not `!== null`. A blank string reads as a declared scenario to the null test, and
        // every self-gating component then looks its numbers up under `scenarios.scenarios.` and gets
        // nothing back: `stat-band` fatalled on `$def['cap_bonus']` with a run whose scenario column was
        // ''. `runs/show.blade.php` already guards this way (`! $run->scenario`), so the model and the
        // view disagreed about the same value, and the view's reading is the one that renders.
        return filled($this->scenario);
    }

    /**
     * The career year a year tab selects.
     *
     * A request cannot widen the three years a run trains through, and a run that has
     * logged nothing is in Junior, so both out-of-range inputs land on a year the grid
     * can draw. `runs.show` clamps the same way for the tabs it renders.
     */
    public function careerYearForTab(int|string|null $tab): int
    {
        $year = is_numeric($tab) ? (int) $tab : $this->currentYear();

        return min(RaceCatalogSlot::YEAR_SENIOR, max(RaceCatalogSlot::YEAR_JUNIOR, $year));
    }

    /**
     * The calendar races a Trainer may record against in one career year.
     *
     * Read from the shared career catalogue rather than `scenario_slots`, because the
     * scenario slot table holds no year: it could not answer "what may be entered in
     * Junior", and the picker built on it offered every seeded race on whatever tab was
     * open. Rows the catalogue holds for every scenario (`scenario_key` null) belong to
     * this run as much as the rows tagged with its scenario key.
     *
     * @return Collection<int, RaceCatalogSlot>
     */
    public function calendarRaceSlots(int|string|null $tab): Collection
    {
        if (! $this->hasScenario()) {
            return RaceCatalogSlot::query()->whereRaw('1 = 0')->get();
        }

        return RaceCatalogSlot::query()
            ->forScenario($this->scenarioKey())
            ->inYear($this->careerYearForTab($tab))
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * The race calendar's cells, composed from the career catalogue and this run's
     * own race log (D-221, D-240).
     *
     * Twelve entries indexed from zero — January first — each holding its Early and
     * Late half, which is the shape `x-race-calendar` reads. The catalogue says what
     * a race asks for; the run says how far it has got against that.
     *
     * Two sources, and the split is the retarget. Calendar races come from
     * `race_catalog_slots` for the requested career year, because that is the table
     * carrying a year; `scenario_slots` is read only for `free_race` rows, which are
     * the Trainer's own and have no catalogue row by definition. Team races, Grade
     * Point deadlines and scripted events are timeline slots too and stay out of the
     * grid: the calendar carries no treatment for them, and drawing a deadline in a
     * race cell would show a race the scenario does not have.
     *
     * @return list<array{halves: array<string, array<string, mixed>>}>
     */
    public function calendarCells(?int $year = null): array
    {
        if (! $this->hasScenario()) {
            return [];
        }

        $year ??= $this->currentYear();

        $cells = [];

        for ($month = 0; $month < 12; $month++) {
            $cells[$month] = [
                'halves' => [
                    'Early' => ['slots' => []],
                    'Late' => ['slots' => []],
                ],
            ];
        }

        // The career calendar is the shared 410-row catalogue; the scenario slot
        // table is read only for what it alone can hold, which is the Trainer's own
        // free races. See docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md.
        $catalog = RaceCatalogSlot::query()
            ->forScenario($this->scenarioKey())
            ->inYear($year)
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get();

        $free = ScenarioSlot::query()
            ->where('scenario_key', $this->scenarioKey())
            ->where('kind', 'free_race')
            ->orderBy('sort_order')
            ->get();

        if ($catalog->isEmpty() && $free->isEmpty()) {
            // Nothing is catalogued for this scenario, so there is no grid to fill
            // and the panel's own message — which says what is missing and what to
            // do — is the honest content. Twenty-four cells reading "No race" would
            // be structure claiming to be data (D-220).
            return [];
        }

        $racedCatalog = $this->raceEntries()
            ->whereNotNull('race_catalog_slot_id')
            ->get()
            ->keyBy('race_catalog_slot_id');

        $racedFree = $this->raceEntries()
            ->whereNotNull('scenario_slot_id')
            ->get()
            ->keyBy('scenario_slot_id');

        $fans = $this->stripValues()['fans'];

        $hasWon = $this->raceEntries()
            ->where('status', RaceEntryStatus::Completed)
            ->where('placement', 1)
            ->exists();

        foreach ($catalog as $slot) {
            $index = ($slot->month ?? 0) - 1;

            if ($index < 0 || $index > 11) {
                // The finale block sits outside the 24-turn grid by design.
                continue;
            }

            $cells[$index]['halves'][$slot->half === 'Late' ? 'Late' : 'Early']['slots'][] = $this->calendarCell(
                $slot->title,
                $slot->fans_needed,
                $slot->hasMaidenGate(),
                $racedCatalog->get($slot->id),
                $fans,
                false,
                $hasWon,
            );
        }

        foreach ($free as $slot) {
            $index = ((int) $slot->month) - 1;

            if ($index < 0 || $index > 11) {
                continue;
            }

            $cells[$index]['halves'][$slot->half === 'Late' ? 'Late' : 'Early']['slots'][] = $this->calendarCell(
                $slot->title,
                null,
                false,
                $racedFree->get($slot->id),
                $fans,
                true,
                $hasWon,
            );
        }

        return $cells;
    }

    /**
     * Which of the three career years a career turn falls in.
     *
     * `turn_entries.turn` is a single monotonic counter — `nextTurnNumber()` is
     * max(turn) + 1 with no per-year reset — so the year is derived rather than
     * stored. The divisor is the client's own grid: 24 turns per year, Early and
     * Late for each of twelve months, corroborated against [Global] captures in
     * docs/scenarios/09-global-race-calendar.md. Clamped because a Trainer can log
     * a turn beyond the career and the grid has no fourth year to show it in.
     */
    public static function careerYearForTurn(int $turn): int
    {
        return min(RaceCatalogSlot::YEAR_SENIOR, max(RaceCatalogSlot::YEAR_JUNIOR, intdiv($turn - 1, self::TURNS_PER_YEAR) + 1));
    }

    public function currentYear(): int
    {
        $latest = (int) $this->turnEntries()->max('turn');

        return $latest < 1 ? RaceCatalogSlot::YEAR_JUNIOR : self::careerYearForTurn($latest);
    }

    /**
     * The career turn number the next log lands on: one past the highest logged.
     */
    public function nextTurnNumber(): int
    {
        return (int) $this->turnEntries()->max('turn') + 1;
    }

    /**
     * The turn the Trainer is deciding about, as the year it falls in plus its
     * position on that year's 1-24 grid — or null when there is no turn to decide.
     *
     * The client's calendar marks the turn being played, not the one just logged: a
     * run through turn 3 is being asked about turn 4. Two answers are null. A run
     * with nothing logged has no position to claim, and pointing at Early January
     * would tell a Trainer who has not started that they are standing in it (D-220).
     * A career with all 72 turns logged has no next turn inside the grid, and the
     * finished run highlights nothing rather than falling back to the last turn
     * played.
     *
     * The year travels with the position because they can disagree: turn 24 is Junior
     * Late December, and the turn after it is Classic Early January, which sits on the
     * tab the Trainer is not looking at.
     *
     * @return array{year: int, turn: int}|null
     */
    public function nextTurnToPlay(): ?array
    {
        if ($this->turnEntries()->doesntExist()) {
            return null;
        }

        $next = $this->nextTurnNumber();

        if (intdiv($next - 1, self::TURNS_PER_YEAR) + 1 > RaceCatalogSlot::YEAR_SENIOR) {
            return null;
        }

        return [
            'year' => self::careerYearForTurn($next),
            'turn' => ((($next - 1) % self::TURNS_PER_YEAR) + 1),
        ];
    }

    /**
     * One cell: what the run did here if it did anything, and otherwise whether
     * this turn's entry is still behind a fan gate.
     *
     * No Goal pennant is emitted, and that is a decision rather than an omission.
     * The pennant used to be drawn from `ScenarioSlot::isMandatoryGoal()`, which
     * reads the scenario-scoped `is_mandatory` — a career obligation, true for the
     * debut and the final rounds. The client's red banner marks something else: a
     * per-character objective. Four [Global] panels in
     * docs/scenarios/09-global-race-calendar.md show four different Goal sets, and
     * every banner in them sits on a race like NHK Mile Cup, Tokyo Yushun or
     * Tenno Sho (Autumn) — not on the debut or the finals.
     *
     * So `is_mandatory` stays on the catalogue as the honest fact it is, and stops
     * being a rendering input. The banner returns when `trainee_goals` exists to
     * drive it; until then an unearned pennant is a worse claim than an absent one.
     * The component still renders a `goal` state, so the treatment is not being
     * deleted — only the model's authority to assert it.
     *
     * @return array<string, mixed>
     */
    private function calendarCell(
        string $title,
        ?int $fansNeeded,
        bool $maidenGated,
        ?RaceEntry $entry,
        ?int $fans,
        bool $manual,
        bool $hasWon,
    ): array {
        if ($entry !== null) {
            // The marker survives the finish. `free_race` is a tool concept, not a
            // client one: the game never offers a race that is not in the calendar,
            // so "this row came from the Trainer" is provenance about where the
            // record came from, and provenance does not expire when the race is run.
            // If anything it matters more looking back over a finished career.
            // Slice 13 measured the loss and left the call to the read path (R68).
            return $manual
                ? ['state' => 'past', 'label' => $title, 'manual' => true]
                : ['state' => 'past', 'label' => $title];
        }

        if ($manual) {
            // R61: a free race is a Trainer's own entry, so it gets the open-cell
            // geometry and the manual marker, and never a gate it does not have.
            return ['state' => 'open', 'label' => $title, 'manual' => true];
        }

        if ($maidenGated && ! $hasWon) {
            // No fan figure travels with this lock: the maiden gate clears on an
            // event, so a number here would send the Trainer off to grind toward
            // a target that decides nothing.
            return ['state' => 'maiden_locked', 'label' => $title];
        }

        if ($fansNeeded !== null && $fansNeeded > 0 && ($fans === null || $fans < $fansNeeded)) {
            return ['state' => 'fan_locked', 'label' => $title, 'fans_needed' => $fansNeeded];
        }

        return ['state' => 'open', 'label' => $title];
    }

    /**
     * Whether this run's scenario composes the Grade Point panel at all.
     *
     * Read from the composition matrix rather than a scenario name, so a fifth
     * scenario needs a config entry and no code change (D-240, gate G-34).
     */
    public function composesGradeObjectives(): bool
    {
        if (! $this->hasScenario()) {
            return false;
        }

        return config('scenarios.scenarios.'.$this->scenarioKey().'.panels.grade_objectives') === true;
    }

    /**
     * The Grade Point ladder, in order: the debut race, which asks for no points,
     * then one deadline per year. Each row carries the 1-based `index` a Trainer
     * enters a finish against, so the period a race counts toward and the period it
     * is displayed under can never drift apart.
     *
     * This was derived in the template. It belongs here for the same reason
     * `stripValues()` does: a view should not walk config.
     *
     * The standard track is the only one selected. The matrix also carries
     * `dirt_leaning` and `limited_turf_range`, and the two sources disagree about
     * where a sprint-only trainee lands: `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Grade Points (Replacing Career Goals)" puts "Sprint Umas with
     * poor aptitude in other distances" on the **Dirt** track, while
     * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Basic Information" gives the same character class a third track with only the
     * Classic objective reduced. Neither names the aptitude letter that places a
     * trainee in one, so picking a track here would be a guess printed as a target
     * (D-256, KI-15).
     *
     * @return list<array{index: int, name: string, required: int}>
     */
    public function gradeObjectives(): array
    {
        if (! $this->composesGradeObjectives()) {
            return [];
        }

        $def = config('scenarios.scenarios.'.$this->scenarioKey());
        $labels = $def['grade_objective_labels'] ?? [];
        $standard = $def['grade_objectives']['standard'] ?? [];

        if ($standard === []) {
            return [];
        }

        $rows = array_map(
            static fn (string $year, int $points): array => [
                'name' => $labels[$year] ?? $year,
                'required' => $points,
            ],
            array_keys($standard),
            array_values($standard),
        );

        array_unshift($rows, ['name' => $labels['debut'] ?? 'Debut race', 'required' => 0]);

        return array_map(
            static fn (int $i, array $row): array => ['index' => $i + 1] + $row,
            array_keys($rows),
            $rows,
        );
    }

    /**
     * Which period the Trainer reports as live, as a 0-based position in
     * `gradeObjectives()`, or null while they have said nothing.
     *
     * The column is 1-based because that is how the objectives are numbered in
     * ADR-0003 and spoken about by a Trainer ("objective 3"); the array is 0-based
     * because it is a list. This is the only place the two meet.
     */
    public function currentPeriodPosition(): ?int
    {
        $index = $this->current_objective_index;

        if ($index === null || $index < 1) {
            return null;
        }

        return min($index, RaceEntry::MAX_OBJECTIVE_INDEX) - 1;
    }

    /**
     * Grade Points earned inside one period, or null when no honest figure exists.
     *
     * Points scale with race grade — 100 for a G1 down to 20 for a Pre-OP, from the
     * matrix's `grade_point_by_grade`, transcribed from
     * docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Grade Points and Shop Coins - Exact Values". That
     * table prices a first place only; below first the corpus says points "scale down
     * proportionally" and names no ratio, so a period holding any finish below first
     * reports nothing rather than a total that understates itself (D-256, KI-10's open
     * half).
     *
     * The withholding is per period. D-232 says surplus never carries, so a race
     * entered against Junior has nothing to do with Classic's total.
     */
    public function gradeEarnedFor(int $objectiveIndex): ?int
    {
        if ($this->gradePointTable() === null) {
            return null;
        }

        $entries = $this->periodEntries($objectiveIndex);

        if ($entries->isEmpty()) {
            return null;
        }

        $total = 0;

        foreach ($entries as $entry) {
            $points = $this->pointsOf($entry);

            if ($points === null) {
                return null;
            }

            $total += $points;
        }

        return $total;
    }

    /**
     * What one finish is worth, read from the column and priced only as a fallback.
     *
     * `race_entries.grade_points_earned` is written by the same rule, so the two normally agree and
     * the column wins. The price is recomputed for a row written before the column existed: a local
     * database carrying runs from Slice 7 has a G1 win that priced fine and no figure stored, and
     * reading it as unpriced would take points away from a Trainer who had already earned them.
     */
    private function pointsOf(RaceEntry $entry): ?int
    {
        return $entry->grade_points_earned
            ?? RaceEntry::gradePointsFor($this, $entry->tierKey(), $entry->placement);
    }

    /**
     * How many completed races **inside one period** cannot be turned into a figure.
     *
     * R18 gives the meter a third state, and this is what distinguishes "nothing was
     * logged in this period" from "logged, but not convertible". A result is
     * unpriceable when it finished below first, because `grade_point_by_grade` prices
     * a 1st place only, or when it carries no tier, which since R75 is most races: the
     * grade lives on the slot, and a slot no publisher settled has none.
     */
    public function gradeUnpricedFor(int $objectiveIndex): int
    {
        if ($this->gradePointTable() === null) {
            return 0;
        }

        return $this->periodEntries($objectiveIndex)
            ->filter(fn (RaceEntry $entry): bool => $this->pointsOf($entry) === null)
            ->count();
    }

    /**
     * The whole ladder with each period's own sum beside it.
     *
     * No row is a running total and the set is never summed: D-232 says each deadline
     * is judged against zero, so the only honest number per period is that period's
     * own. A period with nothing entered reports null, not zero (D-220).
     *
     * @return list<array{index: int, name: string, required: int, earned: int|null, unpriced: int}>
     */
    public function gradePeriods(): array
    {
        return array_map(
            fn (array $row): array => [
                ...$row,
                'earned' => $this->gradeEarnedFor($row['index']),
                'unpriced' => $this->gradeUnpricedFor($row['index']),
            ],
            $this->gradeObjectives(),
        );
    }

    /**
     * The headline figure: the reported period's total, and nothing else.
     *
     * Null until the Trainer names a live period, even when finishes are logged.
     * Summing every period instead would print 300 where the game judges 100, and
     * guessing the period from a race date is the derivation D-270 forbids.
     */
    public function gradeEarned(): ?int
    {
        $index = $this->current_objective_index;

        return $index === null ? null : $this->gradeEarnedFor($index);
    }

    /**
     * Unpriceable finishes inside the period the Trainer reports as live.
     */
    public function gradeUnpricedCount(): int
    {
        $index = $this->current_objective_index;

        return $index === null ? 0 : $this->gradeUnpricedFor($index);
    }

    /**
     * Completed races entered before any period was chosen for them.
     *
     * They are not counted in any period's total, and saying "not yet recorded" while
     * they exist would be false: the run did race, the Trainer just has not said what
     * it was for.
     */
    public function gradeUnassignedCount(): int
    {
        if (! $this->composesGradeObjectives()) {
            return 0;
        }

        return $this->raceEntries()
            ->where('status', RaceEntryStatus::Completed)
            ->whereNull('objective_index')
            ->count();
    }

    /**
     * Completed races, read from the loaded collection.
     *
     * `show()` eager-loads `raceEntries.scenarioSlot`, so this is free on the run screen;
     * `loadMissing` keeps it correct for a caller that did not load, and one query there
     * instead of two per period. Before this, `gradePeriods()` asked the database four
     * periods' worth of questions it could answer from rows already in memory.
     *
     * @return Collection<int, RaceEntry>
     */
    private function completedRaces(): Collection
    {
        $this->raceEntries->loadMissing(['scenarioSlot', 'raceCatalogSlot']);

        return $this->raceEntries->where('status', RaceEntryStatus::Completed);
    }

    /**
     * @return array<string, int>|null the per-grade price table, or null when the
     *                                 run composes no grade objectives
     */
    private function gradePointTable(): ?array
    {
        if (! $this->composesGradeObjectives()) {
            return null;
        }

        return (array) config('scenarios.scenarios.'.$this->scenarioKey().'.grade_point_by_grade');
    }

    /**
     * What one grade pays a 1st place, or null when this scenario keeps no such table
     * or the grade is not in it.
     *
     * `RaceEntry::gradePointsFor()` is the only caller that needs it per tier, so the table itself
     * stays private: handing out the array would let a caller price a placement from it, which is
     * the half of KI-10 nobody has a source for.
     */
    public function gradePointsForTier(string $tier): ?int
    {
        $table = $this->gradePointTable();

        return $table === null ? null : ($table[$tier] ?? null);
    }

    /**
     * Completed races entered against one period, filtered from the loaded rows.
     *
     * @return Collection<int, RaceEntry>
     */
    private function periodEntries(int $objectiveIndex): Collection
    {
        if ($objectiveIndex < 1 || $objectiveIndex > RaceEntry::MAX_OBJECTIVE_INDEX) {
            throw new \InvalidArgumentException("objective_index [{$objectiveIndex}] is not one of the four periods.");
        }

        return $this->completedRaces()->where('objective_index', $objectiveIndex);
    }

    protected function casts(): array
    {
        return [
            'status' => RunStatus::class,
            'current_objective_index' => 'integer',
            'shop_resets_in' => 'integer',
            'legacy_selection' => 'array',
            'build_target' => 'array',
            'imported_at' => 'datetime',
        ];
    }

    /**
     * Whether this run's scenario opens the Trackblazer shop at all.
     */
    public function composesShop(): bool
    {
        if (! $this->hasScenario()) {
            return false;
        }

        return config('scenarios.scenarios.'.$this->scenarioKey().'.panels.shop') === true;
    }

    /**
     * Coins spent on recorded purchases. A sum of entered costs, never a balance: the
     * earning side of that arithmetic is not stored per turn, so "coins remaining" is a
     * subtraction this tool cannot do honestly (D-232, Planner Rule 5).
     */
    public function shopSpendTotal(): int
    {
        if (! $this->composesShop()) {
            return 0;
        }

        return $this->turnEvents
            ->map(fn (TurnEvent $event): ?ShopPurchasePayload => $event->purchasePayload())
            ->filter()
            ->sum(fn (ShopPurchasePayload $payload): int => $payload->cost);
    }

    /**
     * The consecutive-race count Race Fatigue keys on (D-230) — still not derived in this build.
     *
     * D-230's premise is that the count "is already recoverable from `turn_entries`". Slice 15 T2
     * (KI-17) added the link that premise needs: a `race_entries` row can now point at the turn it
     * was run on. What the link does not buy is the count itself. The link is nullable, so an entry
     * with none is "the Trainer has not named the turn", which is not the same statement as "this
     * turn had no race", and deriving a run of consecutive races from turns that may or may not have
     * been raced would be guessing from absence (D-270). So the count stays entered on the turn it
     * applies to, as `RaceFatiguePayload {consecutive_races}`, and the chip renders the reason
     * (D-220) rather than a number this tool has not been told.
     */
    public function consecutiveRaceCount(): ?int
    {
        return null;
    }

    /**
     * Whether this run's scenario offers the guided rail a Performance observation field.
     *
     * A capability flag read from the matrix, the way `composesShop()` reads its own, so a fifth
     * scenario that opens the same field is one config entry and no component edit (D-240, gate
     * G-33). It is not a `panels` key on purpose: `panels` is the list the ScenarioPanel shell
     * renders and Our Grand Concert composes none, which is what the baseline strip and gate G-41
     * assert — switching a panel on to reveal a rail input would hand that shell an ON panel with no
     * renderer. A turn-rail input is not a panel.
     *
     * The flag opens a place to record what the Trainer saw. It authorises no number: what a run
     * starts with, what a turn pays and what a Lesson costs stay unpublished in this corpus.
     */
    public function acceptsPerformanceObservations(): bool
    {
        if (! $this->hasScenario()) {
            return false;
        }

        return config('scenarios.scenarios.'.$this->scenarioKey().'.performance_input') === true;
    }

    /**
     * Whether this run's scenario opens a given panel, read from the composition
     * matrix so a fifth scenario needs a config entry and no code (D-240).
     */
    public function composesPanel(string $panel): bool
    {
        if (! $this->hasScenario()) {
            return false;
        }

        return config('scenarios.scenarios.'.$this->scenarioKey().'.panels.'.$panel) === true;
    }

    /**
     * The rank letter the Trainer most recently reported, or null while they have not.
     */
    public function latestTeamRank(): ?TeamRankPayload
    {
        if (! $this->composesPanel('team_rank_ladder')) {
            return null;
        }

        return $this->turnEvents
            ->sortByDesc('turn')
            ->map(fn (TurnEvent $event): ?TeamRankPayload => $event->teamRankPayload())
            ->filter()
            ->first();
    }

    /**
     * The facility level the reported rank grants, derived through the config mapping
     * and never stored. `S+` sits above S and grants no higher facility (the config
     * notes say so), so it has no level and the panel must not hand it one.
     */
    public function facilityLevel(?string $rank): ?int
    {
        if ($rank === null) {
            return null;
        }

        foreach ((array) config('scenarios.scenarios.'.$this->scenarioKey().'.team_rank_ladder') as $rung) {
            if (in_array($rank, (array) ($rung['ranks'] ?? []), true)) {
                return (int) $rung['level'];
            }
        }

        return null;
    }

    /**
     * The burst state each teammate was last recorded in, one row per teammate.
     *
     * The latest payload per key wins, because the states replace one another; an
     * earlier row for the same teammate is history, not a second teammate.
     *
     * @return list<array{teammate: string, state: SpiritBurstState}>
     */
    public function spiritBurstRoster(): array
    {
        $roster = [];

        foreach ($this->turnEvents->sortBy('turn') as $event) {
            $payload = $event->burstPayload();

            if ($payload !== null) {
                $roster[$payload->teammate] = ['teammate' => $payload->teammate, 'state' => $payload->state];
            }
        }

        return array_values($roster);
    }

    /**
     * The consecutive-race reading the Trainer most recently reported.
     */
    public function latestFatigue(): ?RaceFatiguePayload
    {
        return $this->turnEvents
            ->sortByDesc('turn')
            ->map(fn (TurnEvent $event): ?RaceFatiguePayload => $event->fatiguePayload())
            ->filter()
            ->first();
    }

    /**
     * Every Performance observation this run has recorded, oldest turn first (Our Grand Concert).
     *
     * **Observations, never a balance.** Each row is one turn's entered change; there is no row
     * that says what the resource stands at. Summing the deltas would print a "current Dance
     * Performance" whose first term is missing, because no source in this corpus publishes what a
     * run starts with — the same refusal `consecutiveRaceCount()` makes by returning null, and the
     * opposite of `shopSpendTotal()`, which may add costs precisely because both sides of that
     * subtraction are entered facts. A total becomes honest only once a starting value is verified,
     * and that is a later slice's decision, not this method's.
     *
     * Two observations at different turns stay two rows rather than collapsing into one per type,
     * the way `spiritBurstRoster()` collapses per teammate: burst states *replace* one another and
     * Performance changes *accumulate history*, so the list keeps the turn and the event id that
     * each one belongs to and a consumer decides what to show.
     *
     * Not gated on a panel flag, deliberately: `panels` is the UI-composition map and Performance
     * opens no panel, so a gate here would have to invent one to read. The scenario decision belongs
     * to the surface that renders it — `CockpitController` chooses which purchases to show the same
     * way — while this method simply reports what was recorded.
     *
     * @return list<array{turn: int, event_id: int, type: PerformanceType, delta: int}>
     */
    public function performanceObservations(): array
    {
        $observations = [];

        foreach ($this->turnEvents->sortBy([['turn', 'asc'], ['id', 'asc']]) as $event) {
            $payload = $event->performancePayload();

            if ($payload !== null) {
                $observations[] = [
                    'turn' => (int) $event->turn,
                    'event_id' => (int) $event->id,
                    'type' => $payload->type,
                    'delta' => $payload->delta,
                ];
            }
        }

        return $observations;
    }

    /**
     * Race names this run has actually completed, as entered against a slot.
     *
     * @return list<string>
     */
    public function completedRaceTitles(): array
    {
        return $this->raceEntries
            ->where('status', RaceEntryStatus::Completed)
            ->map(fn (RaceEntry $entry): ?string => $entry->scenarioSlot?->title)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The epithet checklist, derived from entered race names against the config route
     * table and labelled as derived wherever it renders.
     *
     * Three states per route, and the third one is the load-bearing one: a route the
     * surface cannot see is `unverifiable`, not `unmet`. Showing an aggregate condition
     * as unfinished would tell a Trainer they had not earned it on the evidence of a
     * field this tool does not have (D-220, D-256).
     *
     * @return list<array{route: string, epithet: string, reward: string, state: string, missing: list<string>, note: string|null}>
     */
    public function epithetProgress(): array
    {
        if (! $this->composesPanel('epithet_routes')) {
            return [];
        }

        $seen = array_map('strtolower', $this->completedRaceTitles());
        $earned = [];
        $progress = [];

        // Prerequisites resolve in list order, which is how the guide prints them: every
        // composite row names an epithet from an earlier line.
        foreach ((array) config('scenarios.scenarios.'.$this->scenarioKey().'.epithet_routes') as $row) {
            $races = (array) ($row['races'] ?? []);
            $aggregate = $row['aggregate'] ?? null;

            $missing = array_values(array_filter(
                $races,
                fn (string $race): bool => ! in_array(strtolower($race), $seen, true),
            ));

            $racesSeen = count($races) - count($missing);

            $racesMet = $aggregate === null && $races !== [] && (
                ($row['mode'] ?? 'all') === 'any' ? $racesSeen >= 1 : $missing === []
            );

            $prerequisites = (array) ($row['epithets'] ?? []);
            $prerequisitesMet = $prerequisites === []
                ? true
                : count(array_intersect($prerequisites, $earned)) === count($prerequisites);

            $state = match (true) {
                $aggregate !== null => 'unverifiable',
                $racesMet && $prerequisitesMet => 'earned',
                default => 'open',
            };

            if ($state === 'earned') {
                $earned[] = (string) $row['epithet'];
            }

            $progress[] = [
                'route' => (string) $row['route'],
                'epithet' => (string) $row['epithet'],
                'reward' => (string) $row['reward'],
                'state' => $state,
                'missing' => $missing,
                'note' => $aggregate,
            ];
        }

        return $progress;
    }
}
