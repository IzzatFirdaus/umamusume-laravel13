<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\SkillAcquisition;
use App\Http\Controllers\Controller;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Services\SkillSpendCoverage;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Skills Planner (plan §8 D13, design-2.0 only). What the run's skills look like against the
 * build target: the four states, what the skills still to learn cost, and whether each skill's own
 * recorded conditions agree with the target.
 *
 * **Nothing here recommends a skill.** OQ-5 stays open (`PRD.md`, `ADR-0020` §2), so the race-fit
 * cells say "matches", "does not match" or "not recorded" and never rank, score or prioritise; the
 * only ordering on the screen is the Trainer's own priority list. Every number is a catalogue fact,
 * a recorded value, or the run's own stored total; a figure this tree cannot source renders as the
 * absence it is, with the reason, never a default (D-220).
 */
final class SkillsPlannerController extends Controller
{
    /** The fit dimensions, in the order the prompt lists them. */
    private const FIT_KEYS = ['distance', 'surface', 'style', 'course', 'weather', 'ground', 'phase'];

    public function show(TrainingRun $run): Response
    {
        $run->load(['umamusume', 'turnEntries', 'skills']);

        $target = $run->buildTarget();
        $catalog = $this->catalogFor($run);
        $required = $this->requiredRows($run, $catalog, $target);

        return Inertia::render('Career/SkillsPlanner', [
            'run' => $this->runSection($run),
            'target' => $this->targetSection($run, $target),
            'coverage' => $this->coverageSection($run, $required),
            // A-0.8: the priced skills the run has not acquired, sorted by cost, with the sum and the
            // shortfall against the run's recorded SP (§2.2/§2.3).
            'purchasable' => $this->purchasableSection($run),
            'groups' => $this->groupRows($run, $required, $target),
            // F2, plan §9.6 ruling 5: the acquired/skipped status write now lives on the Skills
            // Planner rather than the 2.0.0 run-detail screen. The route and Form Request are the
            // same ones the record screen used, and the endpoint returns to this planner, so a POST
            // from here lands the write and keeps the Trainer where the run's skills are read.
            'skills_sync_url' => route('runs.skills.sync', $run),
            'acquisition_options' => $this->acquisitionOptions(),
            // Slice 10, the add affordance. The editor below can only edit the status of rows already
            // in a group, so a run with no build target and no rows had no way to gain its first
            // skill; this is the catalogue it may draw from, filtered server-side so the page never
            // ships a skill the run already holds or the Global scope cannot offer.
            'picker_skills' => $this->pickerSkills($run),
        ]);
    }

    /**
     * @return array{id: int, trainee: string, trainee_ja: string|null, scenario_label: string, status_label: string, run_url: string, skills_sync_url: string}
     */
    private function runSection(TrainingRun $run): array
    {
        return [
            'id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            'status_label' => $run->status->label(),
            'run_url' => route('runs.cockpit', $run),
            'skills_sync_url' => route('runs.skills.sync', $run),
        ];
    }

    /**
     * A-0.8: the priced skills the run has not acquired, sorted by cost, with the sum and the shortfall
     * against the run's recorded SP (§2.2's "purchasable at or under N SP", §2.3's recommended spend).
     *
     * Read-only and catalogue-only: the `skills` table carries `sp_cost`, so no schema is involved. A
     * skill already acquired is excluded — its SP is spent, not still to buy — and an unpriced row is
     * excluded rather than summed as zero (D-220). The sum prices every skill at its base; the client
     * pays less with hints, which no column holds (G-SK-3), so the figure is the honest one. With no
     * recorded SP (`sp` null) the pool still lists and sums, and the shortfall is null rather than a
     * claim the run cannot afford.
     *
     * @return array{rows: list<array{id: int, name: string, sp_cost: int}>, total: int, count: int, sp: int|null, shortfall: int|null}
     */
    private function purchasableSection(TrainingRun $run): array
    {
        $acquired = $run->skills
            ->filter(static fn (Skill $skill): bool => $skill->pivot->status === SkillAcquisition::Acquired->value)
            ->pluck('id')
            ->all();

        /** @var list<array{id: int, name: string, sp_cost: int}> $rows */
        // ponytail: the payload is capped at the 100 cheapest rows; the seeded Global catalogue prices
        // over a thousand skills and shipping all of them is a slow page for a reference list. The full
        // pool size travels as `count` so the panel can say "cheapest 100 of N". Upgrade path: paginate
        // the list if the panel ever needs the whole pool at once.
        $base = Skill::query()
            ->availableOnGlobal()
            ->whereNotIn('id', $acquired)
            ->whereNotNull('sp_cost');

        $count = (clone $base)->count();

        $rows = $base
            ->orderBy('sp_cost')
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'sp_cost'])
            ->map(static fn (Skill $skill): array => [
                'id' => (int) $skill->id,
                'name' => (string) $skill->name,
                'sp_cost' => (int) $skill->sp_cost,
            ])
            ->values()
            ->all();

        $total = array_sum(array_column($rows, 'sp_cost'));
        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $sp = $latest?->sp;

        return [
            'rows' => $rows,
            'total' => $total,
            'count' => $count,
            'sp' => $sp,
            'shortfall' => $sp === null ? null : max(0, $total - $sp),
        ];
    }

    /**
     * The build target the priorities are read from, or null when the Trainer set none.
     *
     * A target is required for the Required state to exist at all: the priority list is the
     * Trainer's own, and no screen invents one for them.
     *
     * @return array<string, mixed>|null
     */
    private function targetSection(TrainingRun $run, ?BuildTargetPayload $target): ?array
    {
        if ($target === null) {
            return null;
        }

        return [
            'purpose' => $target->purpose->value,
            'purpose_label' => $target->purpose->label(),
            'distance' => $target->distance,
            'surface' => $target->surface,
            'style' => $target->style,
            'targets' => $target->targets,
            'save_url' => route('runs.build-target.update', $run),
        ];
    }

    /**
     * The skills still to learn, priced at their base, against the run's recorded Skill Points.
     *
     * Four states, and the difference between the two empty ones is the whole point. An **empty
     * priority list** is not a calculation: nothing was ever asked for, so no figure is printed and
     * `state` is `no_priorities` (R2-10; the screen used to read "The skills still to learn cost 0 SP,
     * and the run holds 173, leaving 173" over a sum of nothing, which a Trainer could only read as a
     * completed plan). **Everything prioritized and already learned** is the opposite case: the list
     * was filled, the outcomes are recorded, and what remains to buy is genuinely zero, so `state` is
     * `covered` and the zero prints with the reason beside it. `cost` is an outstanding sum, and
     * `unpriced` refuses the sum when a catalogue row carries no price, because a missing price is not
     * a zero (D-220). Learned skills leave the Required list in `requiredRows()`, which is what makes
     * the second state reachable at all.
     *
     * No turn logged still means no Skill Point total the run ever recorded: `sp` stays null and says
     * so, in every state.
     *
     * @param  list<array<string, mixed>>  $required
     * @return array<string, mixed>
     */
    private function coverageSection(TrainingRun $run, array $required): array
    {
        $costs = [];
        $unpriced = [];

        foreach ($required as $row) {
            $name = (string) $row['name'];
            $cost = $row['sp_cost'] === null ? null : (int) $row['sp_cost'];

            $costs[$name] = $cost;

            if ($cost === null) {
                $unpriced[] = $name;
            }
        }

        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $sp = $latest?->sp;

        $state = match (true) {
            $this->priorityNames($run) === [] => 'no_priorities',
            $required === [] => 'covered',
            $unpriced !== [] => 'unpriced',
            default => 'cost',
        };

        // Nothing is added up when nothing was asked for, so the empty sum is not computed and then
        // discarded; the state decides that before the arithmetic runs.
        $coverage = $state === 'no_priorities' ? null : SkillSpendCoverage::sum($costs, $sp);

        $totalTitle = null;
        $absent = null;
        $text = '';

        if ($state === 'no_priorities') {
            // The two reasons nothing is Required are already said apart in the Required group, so this
            // sentence states only the arithmetic consequence.
            $absent = $run->buildTarget() === null
                ? 'This run has no build target, so no skill is Required and there is nothing to sum.'
                : 'The build target names no skill priorities, so there is nothing to sum.';
        } elseif ($state === 'covered') {
            // The reason rides in the provenance line the page prints beside a resolved figure, so the
            // zero and its explanation are read together rather than as two sentences.
            $totalTitle = 'Every prioritized skill is already learned, so nothing is left to buy.';
        } elseif ($state === 'unpriced') {
            $names = implode(', ', $unpriced);
            $count = count($unpriced);
            $totalTitle = "{$count} of the skills still to learn carry no SP price in the catalogue"
                ." ({$names}), so the total cannot be stated.";
            $absent = "Cost not recorded for: {$names}.";
        } elseif ($coverage !== null && $sp !== null) {
            $text = "The skills still to learn cost {$coverage['total_cost']} SP and the run holds {$sp}.";
        }

        return [
            'sp' => $sp,
            'sp_title' => $sp === null ? 'No turn has been logged, so the run has never recorded a Skill Point total.' : null,
            // Which of the four states the figures are in, so the page never has to infer it from a
            // number that means the opposite in two of them.
            'state' => $state,
            'total' => $coverage['total_cost'] ?? null,
            'total_title' => $totalTitle,
            'remaining' => $coverage['remaining'] ?? null,
            // The rendered sentence when the total is refused: the named skills, or the reason
            // there is nothing to price. Null while the figures resolve, so the page prints them.
            'absent' => $absent,
            'warn' => $state === 'cost' && $coverage !== null && $sp !== null && $coverage['total_cost'] > $sp,
            // The total prices every skill at its base; the client pays less when a hint level is
            // on the skill, and no column holds hint levels (G-SK-3), so the sum is the honest one.
            'text' => $text,
        ];
    }

    /**
     * The four states, fixed in the order the screen explains them.
     *
     * Precedence, highest first: **Learned** (pivot `Acquired`) beats the plan, because the outcome
     * is decisive and the SP is spent; **Required** is the priority list naming the skill;
     * **Available** is a `Suggested` row no priority names; **Inherited** has no storage to read —
     * `legacy_selection` carries parents and Sparks, never skills — so its list is the named
     * absence. A `Skipped` row no priority names belongs to the run screen's own list, and one
     * sentence says so.
     *
     * @param  list<array<string, mixed>>  $required
     * @return list<array<string, mixed>>
     */
    private function groupRows(TrainingRun $run, array $required, ?BuildTargetPayload $target): array
    {
        $prioritised = array_fill_keys($this->priorityNames($run), true);
        /** @var array<int, int> $levels */
        $levels = $run->skillLevels->pluck('current_level', 'skill_id')->all();

        $available = [];
        $learned = [];

        foreach ($run->skills as $skill) {
            if ($skill->pivot->status === SkillAcquisition::Acquired->value) {
                $learned[] = $this->skillRow($skill, $target, [
                    'status' => SkillAcquisition::Acquired->value,
                    'turn' => $skill->pivot->turn_acquired,
                    // ADR-0029 D4: the level the skill reached, or null when it was not recorded.
                    'current_level' => $levels[$skill->id] ?? null,
                ]);
            }

            if ($skill->pivot->status === SkillAcquisition::Suggested->value
                && ! array_key_exists($skill->name, $prioritised)) {
                $available[] = $this->skillRow($skill, $target);
            }
        }

        return [
            [
                'key' => 'required',
                'label' => 'Required',
                'glyph' => '!',
                'rows' => $required,
                'absent' => $target === null
                    ? 'No build target is set on this run, so nothing is Required. This screen reorders the priorities a target names; it does not add any.'
                    : 'The build target names no skill priorities, so nothing is Required yet.',
            ],
            [
                'key' => 'available',
                'label' => 'Available',
                'glyph' => '+',
                'rows' => $available,
                'absent' => 'No skill is marked for this run yet. This planner is where a skill state is marked.',
            ],
            [
                'key' => 'learned',
                'label' => 'Learned',
                'glyph' => '●',
                'rows' => $learned,
                'absent' => 'No skill is learned yet.',
            ],
            [
                'key' => 'inherited',
                'label' => 'Inherited',
                'glyph' => '◇',
                'rows' => [],
                'absent' => 'No column holds the skills a Legacy configuration passes down, so there is nothing to list; the client\'s own inherited list is where a Trainer reads them.',
            ],
        ];
    }

    /**
     * The Required rows, in the priority list's own order.
     *
     * Names are matched against the Global-available catalogue — the same scope every read path
     * starts from (`ADR-0011` §2) — and a name the catalogue has no row for is kept as entered
     * with every figure it cannot state rendered as the absence it is. A duplicate name is
     * deduplicated, first occurrence winning; the list is the Trainer's, and the planner's only
     * ordering is theirs.
     *
     * @param  Collection<string, Skill>  $catalog
     * @return list<array<string, mixed>>
     */
    private function requiredRows(TrainingRun $run, Collection $catalog, ?BuildTargetPayload $target): array
    {
        $rows = [];
        $seen = [];

        foreach ($this->priorityNames($run) as $name) {
            if (array_key_exists($name, $seen)) {
                continue;
            }

            $seen[$name] = true;

            $pivot = $run->skills->first(fn (Skill $skill): bool => $skill->name === $name);

            // Learned wins over Required: the outcome is decisive and the SP is spent, so the
            // priority disappears from this list and shows in the Learned group instead.
            if ($pivot !== null && $pivot->pivot->status === SkillAcquisition::Acquired->value) {
                continue;
            }

            $recorded = null;

            if ($pivot !== null) {
                $recorded = ['status' => $pivot->pivot->status, 'turn' => $pivot->pivot->turn_acquired];
            }

            $rows[] = $this->skillRow($catalog->get($name), $target, $recorded, $name);
        }

        return $rows;
    }

    /**
     * The catalogue rows the screen can name, keyed by the client string the priorities use.
     *
     * Only the rows the run names are fetched: the planner renders no catalogue at all, and the
     * full-table sweep the run screen's picker once paid for (6,270 option nodes) is the reason
     * this query is narrow.
     *
     * @return Collection<string, Skill>
     */
    private function catalogFor(TrainingRun $run): Collection
    {
        $names = array_values(array_unique(array_merge(
            $this->priorityNames($run),
            $run->skills->map(static fn (Skill $skill): string => $skill->name)->all(),
        )));

        return Skill::query()
            ->availableOnGlobal()
            ->whereIn('name', $names)
            ->get()
            ->keyBy('name');
    }

    /**
     * @return list<string>
     */
    private function priorityNames(TrainingRun $run): array
    {
        $target = $run->buildTarget();

        return $target === null ? [] : $target->skillPriorities;
    }

    /**
     * One row, as the props test asserts it: the state's facts, the base price, the fit cells, and
     * the raw conditions the source states.
     *
     * @param  array{status: string, turn: int|null}|null  $recorded
     * @return array<string, mixed>
     */
    private function skillRow(
        ?Skill $skill,
        ?BuildTargetPayload $target,
        ?array $recorded = null,
        ?string $priorityName = null,
    ): array {
        $name = $skill === null ? ($priorityName ?? '') : $skill->name;

        return [
            'id' => $skill?->id,
            'name' => $name,
            'name_ja' => $skill?->name_ja,
            'is_unique' => $skill !== null && $skill->is_unique,
            'sp_cost' => $skill?->sp_cost,
            'recorded' => $recorded,
            'conditions' => $this->conditionsLine($skill),
            'fit' => $this->fitCells($skill, $target),
            'note' => $this->prerequisiteNote($name),
        ];
    }

    /**
     * The activation conditions exactly as the source states them, or null when it states none.
     */
    private function conditionsLine(?Skill $skill): ?string
    {
        $groups = $skill?->condition_groups;

        if ($groups === null || $groups === []) {
            return null;
        }

        return implode(' | ', array_map(static fn (array $group): string => $group['condition']
            .(isset($group['precondition']) && $group['precondition'] !== ''
                ? " (precondition: {$group['precondition']})"
                : ''), $groups));
    }

    /**
     * The race-fit cells, one per dimension the prompt names, in its order.
     *
     * A cell says **matches** or **does not match** only where a source publishes the number map:
     * distance (`distance_type` 1-4) and surface (`ground_type` 1-2), both decoded in
     * `UMAMUSUME_REFERENCE.md` §1.2, so every evaluated cell cites that section. Style is where a
     * recommendation would sneak in through the back door (OQ-5): the condition records a number
     * and no source maps the numbers to the client's labels, so the cell refuses to compare. The
     * other dimensions are unconstrained by the target, which records none of them, and with no
     * target at all nothing compares against anything.
     *
     * @return list<array{key: string, label: string, state: string, title: string|null}>
     */
    private function fitCells(?Skill $skill, ?BuildTargetPayload $target): array
    {
        $distanceValues = [];
        $surfaceValues = [];
        $stylesRecorded = false;

        foreach ($this->conditionAtoms($skill) as $atoms) {
            foreach ($atoms['distance_type'] ?? [] as $value) {
                $distanceValues[] = (int) $value;
            }

            foreach ($atoms['ground_type'] ?? [] as $value) {
                $surfaceValues[] = (int) $value;
            }

            if (array_key_exists('running_style', $atoms)) {
                $stylesRecorded = true;
            }
        }

        /** @var array<int, string> $distanceMap */
        $distanceMap = config('uma.skills.fit_distance_type', []);
        /** @var array<int, string> $surfaceMap */
        $surfaceMap = config('uma.skills.fit_surface_type', []);

        $cells = [];

        foreach (self::FIT_KEYS as $key) {
            $label = ucfirst($key);
            $state = 'unrecorded';
            $title = null;

            if ($key === 'distance' || $key === 'surface') {
                $values = $key === 'distance' ? $distanceValues : $surfaceValues;
                $map = $key === 'distance' ? $distanceMap : $surfaceMap;

                if ($values === []) {
                    $title = "This skill's conditions record no {$key} constraint, so there is nothing to compare.";
                } elseif ($target === null) {
                    $title = 'No build target is set on this run, so there is nothing to compare against.';
                } else {
                    $bands = [];

                    foreach ($values as $value) {
                        if (isset($map[$value])) {
                            $bands[] = $map[$value];
                        }
                    }

                    $wanted = $key === 'distance' ? $target->distance : $target->surface;

                    if ($bands === []) {
                        $title = "This skill's {$key} numbers are outside the published map, so no comparison is made.";
                    } elseif (in_array($wanted, $bands, true)) {
                        $state = 'matches';
                        $title = "Decoded by the published {$key} map.";
                    } else {
                        $state = 'mismatch';
                        $title = "Decoded by the published {$key} map.";
                    }
                }
            }

            if ($key === 'style') {
                $title = $stylesRecorded
                    ? 'The condition records a style number, but no source maps the numbers to the client\'s style labels, so no comparison is made.'
                    : 'No condition on this skill names a running style.';
            }

            if (in_array($key, ['course', 'weather', 'ground', 'phase'], true)) {
                $title = "The build target records no {$label}, so there is nothing to compare against.";
            }

            $cells[] = ['key' => $key, 'label' => $label, 'state' => $state, 'title' => $title];
        }

        return $cells;
    }

    /**
     * The equality atoms of each condition group; `&` conjoins and `@` is the source's own or.
     * Every value a key carries is kept: `distance_type==3@distance_type==4` fires at 3 or 4, and
     * folding that to one value invented a "does not match" on the other. Inequalities and
     * unmodelled keys are skipped rather than parsed half-way, because a parse that guesses is a
     * parse that invents.
     *
     * @return list<array<string, list<string>>>
     */
    private function conditionAtoms(?Skill $skill): array
    {
        $groups = $skill?->condition_groups;

        if ($groups === null) {
            return [];
        }

        return array_map(static function (array $group): array {
            $atoms = [];

            foreach ([$group['condition'] ?? null, $group['precondition'] ?? null] as $predicate) {
                if (! is_string($predicate)) {
                    continue;
                }

                foreach (preg_split('/[@&]/', $predicate) ?: [] as $atom) {
                    if (preg_match('/^([a-z_]+)==(-?\d+)$/', trim($atom), $m) === 1) {
                        $atoms[$m[1]][] = $m[2];
                    }
                }
            }

            return $atoms;
        }, $groups);
    }

    /**
     * The one prerequisite pair a source publishes, when the skill is in it.
     */
    private function prerequisiteNote(string $name): ?string
    {
        foreach (config('uma.skills.gold_prerequisites', []) as $goldPrefix => $whitePrefix) {
            if (str_starts_with($name, (string) $goldPrefix)) {
                return "The matching {$whitePrefix} skill is its published prerequisite. Whether learning the gold suppresses or replaces the white in a race is not established, so this planner states the prerequisite and stops.";
            }
        }

        return null;
    }

    /**
     * The catalogue skills a Trainer can still add to this run: the Global scope every read path
     * starts from (`ADR-0011` §2), less the rows the run already holds. The filter is one query here
     * rather than a page-side subtraction, so the roster the picker offers and the rows the sync
     * endpoint will accept cannot disagree about what is addable.
     *
     * `{id, name}` is the whole shape. The picker posts an id and prints a name; no surface reads a
     * cost, a condition or a fit cell for a skill that is not on the run yet.
     *
     * @return list<array{id: int, name: string}>
     */
    private function pickerSkills(TrainingRun $run): array
    {
        return array_values(Skill::query()
            ->availableOnGlobal()
            ->whereNotIn('id', $run->skills->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (Skill $skill): array => ['id' => (int) $skill->id, 'name' => (string) $skill->name])
            ->values()
            ->all());
    }

    /**
     * The acquisition-status options F2 (plan §9.6 ruling 5) renders alongside the run's existing
     * skill states, so the picker mirrors the record screen's vocabulary rather than inventing one.
     *
     * @return list<array{value: string, label: string}>
     */
    private function acquisitionOptions(): array
    {
        return array_map(
            static fn (SkillAcquisition $acquisition): array => ['value' => $acquisition->value, 'label' => $acquisition->label()],
            SkillAcquisition::cases(),
        );
    }
}
