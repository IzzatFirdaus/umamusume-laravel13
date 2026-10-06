<script setup lang="ts">
/*
 * The career header (SCREEN-009 §12): where the run stands and the readings it holds.
 *
 * Every figure is stored or entered, and an unrecorded one renders `N/A` with a `title` naming which
 * half of the fact is missing, never a default and never a dash (AGENTS.md §5, D-220). A run that has
 * logged no turn claims no year, no month and no Energy, because Early January and a zero would both
 * be statements about a career nobody has started.
 *
 * The scenario's own widgets are read from `config/scenarios.php` by subtraction against the baseline
 * entry, server-side, and arrive as a list: nothing here branches on a scenario name (D-240, G-33),
 * and a fifth scenario names its resource in one config line.
 */
import EnergyGauge from '../../components/EnergyGauge.vue';
import MoodPill from '../../components/MoodPill.vue';
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
                <dd class="text-ink-strong" :title="props.header.year_label === null ? absent('A career year') : undefined">
                    {{ props.header.year_label ?? 'N/A' }}
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Month</dt>
                <dd class="text-ink-strong" :title="props.header.month_label === null ? absent('A career month') : undefined">
                    {{ props.header.month_label ?? 'N/A' }}
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Turn</dt>
                <dd class="font-mono tabular-nums text-ink-strong">{{ group(props.header.turn) }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Fans</dt>
                <dd
                    class="font-mono tabular-nums text-ink-strong"
                    :title="props.header.fans === null ? absent('A Fan count') : undefined"
                >
                    {{ props.header.fans === null ? 'N/A' : group(props.header.fans) }}
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Skill Points</dt>
                <dd
                    class="font-mono tabular-nums text-ink-strong"
                    :title="props.header.skill_points === null ? absent('A Skill Point total') : undefined"
                >
                    {{ props.header.skill_points === null ? 'N/A' : group(props.header.skill_points) }}
                </dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Mood</dt>
                <dd><MoodPill :tier="props.header.mood" unrecorded="N/A, not recorded" /></dd>
            </div>
        </dl>

        <div class="mt-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Energy</p>
            <EnergyGauge class="mt-1" :energy="props.header.energy" />
        </div>

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
            This scenario composes no resource beyond Turn, Energy and Fans.
        </p>
    </section>
</template>
