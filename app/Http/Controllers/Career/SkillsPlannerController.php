<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\RunStatus;
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
            'groups' => $this->groupRows($run, $required, $target),
            // F2, plan §9.6 ruling 5: the acquired/skipped status write now lives on the Skills
            // Planner rather than the 0.1.0 run-detail screen. The route and Form Request are the
            // same ones the record screen used; the controller repoints them to the cockpit after the
            // flip, so a POST from here lands the write and returns the Trainer to where the run's
            // skills are read.
            'skills_sync_url' => route('runs.skills.sync', $run),
            'acquisition_options' => $this->acquisitionOptions(),
        ]);
    }

    /**
     * @return array{id: int, trainee: string, trainee_ja: string|null, scenario_label: string, status_label: string, run_url: string, skills_sync_url: string, status_labels: array<string,string>}
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
            'status_labels' => collect(RunStatus::cases())->mapWithKeys(fn (RunStatus $s): array => [$s->value => $s->label()])->toArray(),
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
     * Both figures can be unstated: no turn logged means no SP total the run ever recorded, a
     * priority skill whose catalogue row carries no price means the sum would be a guess, and no
     * target at all means nothing is Required. A missing price is not a zero, so the sum is
     * refused and the skills it could not price are named, rather than the total quietly shortened
     * (D-220). Learned skills are excluded because their SP is spent.
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

        $coverage = SkillSpendCoverage::sum($costs, $sp);

        $totalTitle = null;
        $absent = null;

        if ($required === []) {
            $totalTitle = 'No build target is set, so no skill is Required and there is nothing to sum.';
            $absent = 'No skill is Required yet, so there is nothing to price.';
        } elseif ($unpriced !== []) {
            $names = implode(', ', $unpriced);
            $count = count($unpriced);
            $totalTitle = "{$count} of the skills still to learn carry no SP price in the catalogue"
                ." ({$names}), so the total cannot be stated.";
            $absent = "Cost not recorded for: {$names}.";
        }

        return [
            'sp' => $sp,
            'sp_title' => $sp === null ? 'No turn has been logged, so the run has never recorded a Skill Point total.' : null,
            'total' => $coverage['total_cost'] ?? null,
            'total_title' => $totalTitle,
            'remaining' => $coverage['remaining'] ?? null,
            // The rendered sentence when the total is refused: the named skills, or the reason
            // there is nothing to price. Null while the figures resolve, so the page prints them.
            'absent' => $absent,
            'warn' => $coverage !== null && $sp !== null && $coverage['total_cost'] > $sp,
            // The total prices every skill at its base; the client pays less when a hint level is
            // on the skill, and no column holds hint levels (G-SK-3), so the sum is the honest one.
            'text' => $coverage !== null && $sp !== null
                ? "The skills still to learn cost {$coverage['total_cost']} SP and the run holds {$sp}."
                : '',
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

        $available = [];
        $learned = [];

        foreach ($run->skills as $skill) {
            if ($skill->pivot->status === SkillAcquisition::Acquired->value) {
                $learned[] = $this->skillRow($skill, $target, [
                    'status' => SkillAcquisition::Acquired->value,
                    'turn' => $skill->pivot->turn_acquired,
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
                'absent' => 'No skill is marked for this run yet. The run screen\'s skill panel marks one.',
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
                        $title = "This skill's {$key} numbers are outside the map `UMAMUSUME_REFERENCE.md` §1.2 publishes, so no comparison is made.";
                    } elseif (in_array($wanted, $bands, true)) {
                        $state = 'matches';
                        $title = "Decoded by the {$key} map in `UMAMUSUME_REFERENCE.md` §1.2.";
                    } else {
                        $state = 'mismatch';
                        $title = "Decoded by the {$key} map in `UMAMUSUME_REFERENCE.md` §1.2.";
                    }
                }
            }

            if ($key === 'style') {
                $title = $stylesRecorded
                    ? 'The condition records a style number, but no source maps the numbers to the client\'s style labels (PRD OQ-5), so no comparison is made.'
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
                return "The matching {$whitePrefix} skill is its published prerequisite (run report §8.1). Whether learning the gold suppresses or replaces the white in a race is not established, so this planner states the prerequisite and stops.";
            }
        }

        return null;
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
