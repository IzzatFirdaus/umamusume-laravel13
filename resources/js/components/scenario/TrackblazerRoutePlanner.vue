<script setup lang="ts">
/*
 * The race plan (G5, plan §6) — one row per end-of-year checkpoint, each with the figure it asks
 * for, what this run's entered races would pay if they were all won, and what they still pay at 2nd
 * and 3rd.
 *
 * **No number lives in this file.** Every figure is composed in
 * `App\Http\Controllers\Career\CockpitController::planningSection()` from the scenario's own config
 * entry and the run's entered `race_entries` rows (correction 6). The modifiers that turn a win into
 * a floor are uma.guide's table at
 * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md:104-112`, and the same master's GameTora
 * section names no ratio (line 355) — which is why these floors point forward at races that have not
 * been run and no *earned* total in this tool reads them.
 *
 * **This is not a projection.** A row states what a placement is worth, never what this trainee will
 * place: `ADR-0016` holds the readiness-scoring shape and `PRD.md` §6.11 keeps prediction out. The
 * Pest file greps the composed payload for that vocabulary.
 *
 * **The epithet routes are not re-listed here.** They already render in the Epithet routes arm from
 * `TrainingRun::epithetProgress()`, and one race must not have two surfaces on one screen. What this
 * list owes is the pointer, which `planning.routes_pointer` supplies.
 *
 * **Standard track only.** The denominator is the config's standard row because no column says which
 * trainee belongs on the dirt-leaning or limited-turf-range one; that is KI-15, and the row's badge
 * carries the sentence.
 *
 * Each period is a two-column `dt`/`dd` list. The pairs are the `dl`'s own children, unwrapped:
 * axe's `definition-list` rule reads that child list, and a wrapping `div` fails it while also
 * dropping the term role Chromium would need. Narrow screens stack the value under its label.
 */
import { computed } from 'vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';

type Badge = 'confirmed' | 'calculated' | 'estimated' | 'unknown' | null;

interface PlanningPeriod {
    index: number;
    name: string;
    needed: string;
    needed_badge: Badge;
    needed_title: string;
    is_current: boolean;
    plan: string | null;
    plan_badge: Badge;
    plan_title: string;
    uncounted: number;
    uncounted_reason: string | null;
    uncounted_title: string;
    floor_second: string | null;
    floor_third: string | null;
    floor_label: string;
    floor_badge: Badge;
    floor_title: string;
}

interface PlanningFlag {
    kind: string;
    label: string;
    badge: Badge;
    title: string;
}

interface YearEndGate {
    gate_label: string;
    timing: string;
    fans_needed: string;
    fans_needed_badge: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    fans_needed_title: string;
    fans_recorded: string | null;
    fans_recorded_badge: Badge;
    fans_recorded_title: string;
    fans_shortfall: string | null;
    fans_shortfall_badge: Badge;
    fans_shortfall_title: string;
    bond_figures: string;
    bond_recorded: string | null;
    bond_title: string;
}

interface PlanningSection {
    periods: PlanningPeriod[];
    collapses_after_period: number;
    flags: PlanningFlag[];
    year_end: YearEndGate | null;
    unattributed: number;
    unattributed_title: string;
    routes_pointer: string;
}

const props = defineProps<{
    planning: PlanningSection;
}>();

const headingId = 'trackblazer-route-plan';

/**
 * Prominent while the first checkpoint is still ahead of the run: all three periods, because the
 * plan is the screen's job. Once the Trainer reports a later period, the periods behind it have no
 * plan left to make and the list folds to the ones that do. The threshold is the config's, and the
 * reported period is entered rather than inferred (D-270).
 */
const shown = computed((): PlanningPeriod[] => {
    const periods = props.planning.periods;
    const current = periods.find((period) => period.is_current);

    if (current === undefined || current.index <= props.planning.collapses_after_period) {
        return periods;
    }

    return periods.filter((period) => period.index >= current.index);
});

const folded = computed((): boolean => shown.value.length !== props.planning.periods.length);

// The one mark this component prints, kept local the way every scenario component keeps its own
// glyph map. The word travels beside it, so a state is never carried by an icon alone.
const currentMark = '◉';
</script>

<template>
    <section :aria-labelledby="headingId" class="flex flex-col gap-3 rounded-md border border-rule bg-panel p-4">
        <h4 :id="headingId" class="text-base font-semibold text-ink-strong">
            Race plan
        </h4>

        <p v-if="props.planning.periods.length === 0" class="text-sm text-ink-muted" role="status">
            No checkpoint period is declared for this run, so there is no runway to plan against.
        </p>

        <template v-else>
            <p v-if="folded" class="text-xs text-ink-muted">
                This run reports a later checkpoint, so the periods behind it are folded: they have no
                plan left to make. The grade ladder above prints each period's own total.
            </p>

            <ol class="flex flex-col gap-3" aria-label="Checkpoint periods">
                <li
                    v-for="period in shown"
                    :key="period.index"
                    :class="[
                        'rounded-md border bg-raised p-3',
                        period.is_current ? 'border-2 border-ink-muted' : 'border-rule',
                    ]"
                >
                    <p class="flex flex-wrap items-baseline gap-x-2 text-sm font-semibold text-ink-strong">
                        <span v-if="period.is_current" aria-hidden="true" class="font-mono">{{ currentMark }}</span>
                        {{ period.name }}
                        <span v-if="period.is_current" class="text-xs font-normal text-ink-muted">the period this run reports</span>
                    </p>

                    <dl class="mt-2 grid grid-cols-1 gap-x-3 gap-y-2 sm:grid-cols-[minmax(0,8rem)_minmax(0,1fr)]">
                        <dt class="text-xs text-ink-muted">Needed</dt>
                        <dd class="flex flex-wrap items-baseline gap-x-2">
                            <ProvenanceBadge v-if="period.needed_badge !== null" :state="period.needed_badge" :title="period.needed_title" />
                            <span class="font-mono text-sm tabular-nums text-ink">{{ period.needed }}</span>
                        </dd>

                        <dt class="text-xs text-ink-muted">Entered races</dt>
                        <dd class="flex flex-col gap-1">
                            <span class="flex flex-wrap items-baseline gap-x-2">
                                <ProvenanceBadge v-if="period.plan_badge !== null" :state="period.plan_badge" :title="period.plan_title" />
                                <span v-if="period.plan !== null" class="font-mono text-sm tabular-nums text-ink">{{ period.plan }}</span>
                                <span v-else class="text-sm text-ink-muted">N/A</span>
                            </span>
                            <span v-if="period.plan === null" class="text-xs text-ink-muted">{{ period.plan_title }}</span>
                            <span
                                v-else-if="period.uncounted > 0 && period.uncounted_reason !== null"
                                class="text-xs text-ink-muted"
                                :title="period.uncounted_title"
                            >
                                {{ period.uncounted }} entered
                                {{ period.uncounted === 1 ? 'race has' : 'races have' }} no published grade.
                                {{ period.uncounted_reason }}
                            </span>
                        </dd>

                        <dt class="text-xs text-ink-muted">Floor at {{ period.floor_label }}</dt>
                        <dd class="flex flex-wrap items-baseline gap-x-2">
                            <ProvenanceBadge v-if="period.floor_badge !== null" :state="period.floor_badge" :title="period.floor_title" />
                            <span v-if="period.floor_second !== null" class="font-mono text-sm tabular-nums text-ink">
                                {{ period.floor_second }} / {{ period.floor_third }}
                            </span>
                            <span v-else class="text-sm text-ink-muted">N/A</span>
                        </dd>
                    </dl>
                </li>
            </ol>
        </template>

        <p v-if="props.planning.unattributed > 0" class="text-xs text-ink-muted" :title="props.planning.unattributed_title">
            {{ props.planning.unattributed }}
            {{ props.planning.unattributed === 1 ? 'entered race is' : 'entered races are' }}
            not attributed to a period, so
            {{ props.planning.unattributed === 1 ? 'it counts' : 'they count' }} toward no runway above.
        </p>

        <p class="text-xs text-ink-muted">{{ props.planning.routes_pointer }}</p>

        <ul v-if="props.planning.flags.length > 0" class="flex flex-col gap-2" aria-label="Plan flags">
            <li
                v-for="flag in props.planning.flags"
                :key="flag.kind"
                class="flex flex-wrap items-baseline gap-x-2 rounded-md border border-rule bg-sunken px-3 py-2"
            >
                <span class="text-sm font-medium text-ink">{{ flag.label }}</span>
                <ProvenanceBadge v-if="flag.badge !== null" :state="flag.badge" :title="flag.title" />
            </li>
        </ul>

        <!-- The gate is its own labelled group, not a bare list: the flag row above names the same
             event, so an assertion inside this region has to say which one it means. -->
        <div v-if="props.planning.year_end !== null" role="group" aria-label="Year-end gate">
            <dl class="grid grid-cols-1 gap-x-3 gap-y-2 rounded-md border border-rule bg-raised p-3 sm:grid-cols-[minmax(0,8rem)_minmax(0,1fr)]">
                <dt class="text-xs text-ink-muted">Assessment</dt>
                <dd class="text-sm text-ink" :title="props.planning.year_end.timing">
                    {{ props.planning.year_end.gate_label }} · {{ props.planning.year_end.timing }}
                </dd>

                <dt class="text-xs text-ink-muted">Fans needed</dt>
                <dd class="flex flex-wrap items-baseline gap-x-2">
                    <ProvenanceBadge :state="props.planning.year_end.fans_needed_badge" :title="props.planning.year_end.fans_needed_title" />
                    <span class="font-mono text-sm tabular-nums text-ink">{{ props.planning.year_end.fans_needed }}</span>
                </dd>

                <dt class="text-xs text-ink-muted">Fans recorded</dt>
                <dd class="flex flex-col gap-1">
                    <span class="flex flex-wrap items-baseline gap-x-2">
                        <ProvenanceBadge v-if="props.planning.year_end.fans_recorded_badge !== null" :state="props.planning.year_end.fans_recorded_badge" :title="props.planning.year_end.fans_recorded_title" />
                        <span v-if="props.planning.year_end.fans_recorded !== null" class="font-mono text-sm tabular-nums text-ink">{{ props.planning.year_end.fans_recorded }}</span>
                        <span v-else class="text-sm text-ink-muted">N/A</span>
                    </span>
                    <span v-if="props.planning.year_end.fans_recorded === null" class="text-xs text-ink-muted">
                        {{ props.planning.year_end.fans_recorded_title }}
                    </span>
                </dd>

                <dt class="text-xs text-ink-muted">Gap to the figure</dt>
                <dd class="flex flex-wrap items-baseline gap-x-2">
                    <ProvenanceBadge v-if="props.planning.year_end.fans_shortfall_badge !== null" :state="props.planning.year_end.fans_shortfall_badge" :title="props.planning.year_end.fans_shortfall_title" />
                    <span v-if="props.planning.year_end.fans_shortfall !== null" class="font-mono text-sm tabular-nums text-ink">{{ props.planning.year_end.fans_shortfall }}</span>
                    <span v-else class="text-sm text-ink-muted">N/A</span>
                </dd>

                <dt class="text-xs text-ink-muted">Bond figures published</dt>
                <dd class="flex flex-col gap-1">
                    <span class="font-mono text-sm text-ink">{{ props.planning.year_end.bond_figures }}</span>
                    <span class="text-xs text-ink-muted">{{ props.planning.year_end.bond_title }}</span>
                </dd>
            </dl>
        </div>
    </section>
</template>
