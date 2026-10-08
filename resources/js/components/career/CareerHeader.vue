<script setup lang="ts">
/*
 * The career header (SCREEN-009 §12): who the run belongs to, and where in it the Trainer stands.
 *
 * Energy, Mood, Fans and Skill Points are deliberately not printed here. They are the state panel's, in
 * the column beside the advisor: a reading is only useful next to the numbers it conditions, and one
 * screen printing the same figure twice is one more place for the two copies to disagree. What is here
 * stays identity and position — trainee, scenario, year, month, turn, and the scenario's own resources.
 *
 * Every figure is stored or entered, and an unrecorded one is `AbsenceValue`'s: the marker with its
 * reason, reachable by keyboard and screen reader rather than by a `title` that answers a mouse only
 * (`DESIGN.md` §5). A run that has logged no turn claims no year and no month, because Early January
 * would be a statement about a career nobody has started.
 *
 * The scenario's own widgets are read from `config/scenarios.php` by subtraction against the baseline
 * entry, server-side, and arrive as a list: nothing here branches on a scenario name (D-240, G-33),
 * and a fifth scenario names its resource in one config line.
 */
import AbsenceValue from '../AbsenceValue.vue';
import ResourceStrip from '../../components/ResourceStrip.vue';

interface Header {
    scenario_label: string;
    scenario_declared: boolean;
    year_label: string | null;
    month_label: string | null;
    turn: number;
    energy: number | null;
    mood: string | null;
    fans: number | null;
    skill_points: number | null;
    widgets: string[];
    values: Record<string, number | string | null>;
}

const props = defineProps<{
    trainee: string;
    traineeJa: string | null;
    header: Header;
}>();

const group = (n: number): string => n.toLocaleString('en-US');

const absent = (what: string): string => `${what} has not been recorded for this run.`;
</script>

<template>
    <section aria-labelledby="career-header-heading" class="rounded-md border border-rule bg-panel p-4">
        <h2 id="career-header-heading" class="text-base font-semibold text-ink-strong">
            {{ props.trainee }}
            <span v-if="props.traineeJa" lang="ja" class="ml-2 text-sm font-normal text-ink-muted">
                {{ props.traineeJa }}
            </span>
        </h2>

        <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm">
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Scenario</dt>
                <dd class="text-ink-strong">
                    <span v-if="props.header.scenario_declared">{{ props.header.scenario_label }}</span>
                    <span v-else title="No scenario was chosen for this run.">No scenario set</span>
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Year</dt>
                <dd class="text-ink-strong">
                    <AbsenceValue v-if="props.header.year_label === null" :reason="absent('A career year')" compact />
                    <span v-else>{{ props.header.year_label }}</span>
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Month</dt>
                <dd class="text-ink-strong">
                    <AbsenceValue v-if="props.header.month_label === null" :reason="absent('A career month')" compact />
                    <span v-else>{{ props.header.month_label }}</span>
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Turn</dt>
                <dd class="font-mono tabular-nums text-ink-strong">{{ group(props.header.turn) }}</dd>
            </div>
        </dl>

        <div v-if="props.header.widgets.length > 0" class="mt-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Scenario resources</p>
            <ResourceStrip
                class="mt-1"
                :widgets="props.header.widgets as never"
                :scenario-label="props.header.scenario_label"
                :declared="props.header.scenario_declared"
                :values="props.header.values"
            />
        </div>
        <p v-else class="mt-3 text-xs text-ink-muted">
            This scenario composes no resource of its own. Turn, Energy and Fans are what every scenario
            carries; the readings are on Stats and state, beside the advisor.
        </p>
    </section>
</template>
