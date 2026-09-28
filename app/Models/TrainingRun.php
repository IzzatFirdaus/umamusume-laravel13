<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RaceEntryStatus;
use App\Enums\RunStatus;
use App\Enums\SkillAcquisition;
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
 * @property-read Collection<int, TurnEntry> $turnEntries
 * @property-read Collection<int, TurnEvent> $turnEvents
 * @property-read Collection<int, RaceEntry> $raceEntries
 * @property-read Collection<int, Skill> $skills
 * @property-read Umamusume $umamusume
 */
#[Fillable(['umamusume_id', 'scenario', 'status', 'inheritance_parent_a_id', 'inheritance_parent_b_id', 'notes', 'current_objective_index'])]
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
    public function calendarCells(): array
    {
        if (! $this->hasScenario()) {
            return [];
        }

        $cells = [];

        for ($month = 0; $month < 12; $month++) {
            $cells[$month] = [
                'halves' => [
                    'Early' => ['state' => 'empty'],
                    'Late' => ['state' => 'empty'],
                ],
            ];
        }

        $slots = ScenarioSlot::query()
            ->where('scenario_key', $this->scenarioKey())
            ->where('kind', 'goal_race')
            ->orderBy('sort_order')
            ->get();

        if ($slots->isEmpty()) {
            // Nothing is catalogued for this scenario, so there is no grid to fill
            // and the panel's own message — which says what is missing and what to
            // do — is the honest content. Twenty-four cells reading "No race" would
            // be structure claiming to be data (D-220).
            return [];
        }

        $raced = $this->raceEntries()
            ->whereNotNull('scenario_slot_id')
            ->get()
            ->keyBy('scenario_slot_id');

        $hasWon = $this->raceEntries()
            ->where('status', RaceEntryStatus::Completed)
            ->where('placement', 1)
            ->exists();

        $fans = $this->stripValues()['fans'];

        foreach ($slots as $slot) {
            // The table stores 1-12 and the component indexes from zero.
            $index = $slot->month - 1;
            $half = $slot->half === 'Late' ? 'Late' : 'Early';

            if ($index < 0 || $index > 11) {
                continue;
            }

            $cells[$index]['halves'][$half] = $this->calendarCell(
                $slot,
                $raced->get($slot->id),
                $fans,
                $hasWon,
            );
        }

        return $cells;
    }

    /**
     * One cell: what the run did here if it did anything, and otherwise which of
     * the slot's gates this run has not cleared yet.
     *
     * @return array<string, mixed>
     */
    private function calendarCell(ScenarioSlot $slot, ?RaceEntry $entry, ?int $fans, bool $hasWon): array
    {
        if ($entry !== null) {
            return ['state' => 'past', 'label' => $slot->title];
        }

        if ($slot->hasMaidenGate() && ! $hasWon) {
            // No fan figure travels with this lock: the maiden gate clears on an
            // event, so a number here would send the Trainer off to grind toward
            // a target that decides nothing.
            return ['state' => 'maiden_locked', 'label' => $slot->title];
        }

        if ($slot->hasFanGate() && ($fans === null || $fans < $slot->fans_needed)) {
            return ['state' => 'fan_locked', 'label' => $slot->title, 'fans_needed' => $slot->fans_needed];
        }

        return [
            'state' => $slot->isMandatoryGoal() ? 'goal' : 'open',
            'label' => $slot->title,
        ];
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
     * Completed races entered against one period, with the slot that carries the grade.
     *
     * @return Collection<int, RaceEntry>
     */
    private function periodEntries(int $objectiveIndex): Collection
    {
        if ($objectiveIndex < 1 || $objectiveIndex > RaceEntry::MAX_OBJECTIVE_INDEX) {
            throw new \InvalidArgumentException("objective_index [{$objectiveIndex}] is not one of the four periods.");
        }

        return $this->raceEntries()
            ->where('status', RaceEntryStatus::Completed)
            ->where('objective_index', $objectiveIndex)
            ->with('scenarioSlot')
            ->get();
    }

    protected function casts(): array
    {
        return [
            'status' => RunStatus::class,
            'current_objective_index' => 'integer',
        ];
    }
}
