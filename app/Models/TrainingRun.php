<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
use App\Enums\SpiritBurstState;
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
use Illuminate\Support\Carbon;

/**
 * One training run for one Umamusume; owns its turns and skill states, both
 * cascade-deleted with it (PRD FR-C-1, C-4).
 *
 * @property int $id
 * @property int $umamusume_id
 * @property string|null $scenario
 * @property RunStatus $status
 * @property int|null $inheritance_parent_a_id
 * @property int|null $inheritance_parent_b_id
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
 * @property-read Collection<int, TurnEntry> $turnEntries
 * @property-read Collection<int, TurnEvent> $turnEvents
 * @property-read Collection<int, RaceEntry> $raceEntries
 * @property-read Collection<int, Skill> $skills
 * @property-read Umamusume $umamusume
 */
#[Fillable(['umamusume_id', 'scenario', 'status', 'inheritance_parent_a_id', 'inheritance_parent_b_id', 'notes', 'current_objective_index', 'shop_resets_in'])]
class TrainingRun extends Model
{
    /** @use HasFactory<TrainingRunFactory> */
    use HasFactory;

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
     * @return BelongsToMany<Skill, $this, RunSkill, 'pivot'>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'run_skills')
            ->withPivot(['status', 'turn_acquired'])
            ->using(RunSkill::class);
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
        return $this->scenario ?? (string) config('scenarios.baseline');
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
        return $this->scenario !== null;
    }

    /**
     * The race calendar's cells, composed from this scenario's slots and this
     * run's own race log (D-221, D-240).
     *
     * Twelve entries indexed from zero — January first — each holding its Early and
     * Late half, which is the shape `x-race-calendar` reads. The slots say what a
     * race asks for; the run says how far it has got against that.
     *
     * Only `goal_race` slots become cells. Team races, Grade Point deadlines and
     * scripted events are timeline slots too, but the calendar carries no treatment
     * for them, and drawing a deadline in a race cell would show a race the
     * scenario does not have.
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
     * `turn_entries.turn` is a single monotonic counter — `nextTurn()` is
     * max(turn) + 1 with no per-year reset — so the year is derived rather than
     * stored. The divisor is the client's own grid: 24 turns per year, Early and
     * Late for each of twelve months, corroborated against [Global] captures in
     * docs/scenarios/09-global-race-calendar.md. Clamped because a Trainer can log
     * a turn beyond the career and the grid has no fourth year to show it in.
     */
    public static function careerYearForTurn(int $turn): int
    {
        return min(RaceCatalogSlot::YEAR_SENIOR, max(RaceCatalogSlot::YEAR_JUNIOR, intdiv($turn - 1, 24) + 1));
    }

    public function currentYear(): int
    {
        $latest = (int) $this->turnEntries()->max('turn');

        return $latest < 1 ? RaceCatalogSlot::YEAR_JUNIOR : self::careerYearForTurn($latest);
    }

    /**
     * Where in the year the next logged turn lands, on the 1-24 grid the client
     * labels, or null when nothing has been logged yet.
     *
     * A run with no turns has no current turn to highlight; rendering turn 1 would
     * claim the Trainer is standing on Early January when they have not taken a
     * single turn (D-220).
     */
    public function currentTurnNumber(): ?int
    {
        $latest = (int) $this->turnEntries()->max('turn');

        return $latest < 1 ? null : ((($latest - 1) % 24) + 1);
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
     * where a sprint-only trainee lands: `docs/scenarios/04` puts "Sprint Umas with
     * poor aptitude in other distances" on the **Dirt** track, while
     * `docs/scenarios/05` gives the same character class a third track with only the
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
     * docs/scenarios/05-trackblazer-gametora.md §"Grade Points and Shop Coins". That
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
        $table = $this->gradePointTable();

        if ($table === null) {
            return null;
        }

        $entries = $this->periodEntries($objectiveIndex);

        if ($entries->isEmpty()) {
            return null;
        }

        $total = 0;

        foreach ($entries as $entry) {
            $tier = $entry->scenarioSlot?->tier;

            if ($entry->placement !== 1 || $tier === null || ! array_key_exists($tier, $table)) {
                return null;
            }

            $total += (int) $table[$tier];
        }

        return $total;
    }

    /**
     * How many completed races **inside one period** cannot be turned into a figure.
     *
     * R18 gives the meter a third state, and this is what distinguishes "nothing was
     * logged in this period" from "logged, but not convertible". A result is
     * unpriceable when it finished below first, because `grade_point_by_grade` prices
     * a 1st place only, or when it has no slot, because the grade lives on the slot
     * and a free-form race records no grade.
     */
    public function gradeUnpricedFor(int $objectiveIndex): int
    {
        $table = $this->gradePointTable();

        if ($table === null) {
            return 0;
        }

        return $this->periodEntries($objectiveIndex)
            ->filter(fn (RaceEntry $entry): bool => $entry->placement !== 1
                || $entry->scenarioSlot?->tier === null
                || ! array_key_exists($entry->scenarioSlot->tier, $table))
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
        $this->raceEntries->loadMissing('scenarioSlot');

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
