<script setup lang="ts">
/*
 * The career state panel (SCREEN-009 §13): five stats and four meta values.
 *
 * Each stat shows the entered current value over the Trainer's own target, with a bar for the
 * distance between them. The bar measures progress toward the target, not toward the ceiling: the
 * ceiling is the scenario's and the target is the Trainer's, and conflating them would answer a
 * question nobody asked. The bar is `aria-hidden` and the numbers are printed beside it, so nothing
 * here is a value carried by a fill alone (SCREEN-009 §34, WCAG 1.4.1).
 *
 * An unrecorded value renders `N/A` with a `title` naming what is missing, never a zero and never a
 * dash (AGENTS.md §5, D-220). A run with no target set has no target bar, and says so in words rather
 * than drawing an empty track that reads as "at zero".
 *
 * StatBand was deliberately not reused here. Its bar is the scenario ceiling with the 1,200
 * halved-gains marker, which is the run record screen's question; giving it a second, target-shaped
 * meaning would change a landed component's contract for one caller.
 */
import EnergyGauge from '../../components/EnergyGauge.vue';
import MoodPill from '../../components/MoodPill.vue';

interface Stat {
    key: string;
    label: string;
    current: number | null;
    target: number | null;
    cap: number;
}

interface Meta {
    key: string;
    label: string;
    value: number | string | null;
}

const props = defineProps<{ stats: Stat[]; meta: Meta[] }>();

const group = (n: number): string => n.toLocaleString('en-US');

const targetHint = (stat: Stat): string =>
    stat.target === null ? 'No target was entered for this stat.' : `Your target: ${group(stat.target)} of a possible ${group(stat.cap)}.`;

const currentHint = (stat: Stat): string => (stat.current === null ? 'No turn has recorded this stat yet.' : `Entered: ${group(stat.current)}.`);

function percent(stat: Stat): number {
    if (stat.current === null || stat.target === null || stat.target <= 0) {
        return 0;
    }

    return Math.min(100, (stat.current / stat.target) * 100);
}
</script>

<template>
    <section aria-labelledby="career-state-heading" class="rounded-md border border-rule bg-panel p-4">
        <h2 id="career-state-heading" class="text-base font-semibold text-ink-strong">Stats and state</h2>

        <ul class="mt-3 space-y-2">
            <li v-for="stat in props.stats" :key="stat.key" class="rounded-md border border-rule bg-raised p-2">
                <div class="flex items-baseline justify-between gap-3">
                    <span class="text-sm font-semibold text-ink-strong">{{ stat.label }}</span>
                    <span class="font-mono text-sm tabular-nums text-ink-strong">
                        <span :title="currentHint(stat)">{{ stat.current === null ? 'N/A' : group(stat.current) }}</span>
                        <span class="text-ink-muted"> / </span>
                        <span :title="targetHint(stat)">{{ stat.target === null ? 'N/A' : group(stat.target) }}</span>
                    </span>
                </div>

                <div v-if="stat.current !== null && stat.target !== null" class="mt-1.5 h-1.5 overflow-hidden rounded bg-sunken" aria-hidden="true">
                    <div class="h-full bg-green-deep" :style="{ width: `${percent(stat)}%` }"></div>
                </div>
                <p v-else class="mt-1 text-xs text-ink-muted">
                    {{ stat.current === null ? 'No reading recorded yet' : 'No target set' }}
                </p>
            </li>
        </ul>

        <dl class="mt-4 grid grid-cols-2 gap-2">
            <div v-for="row in props.meta" :key="row.key" class="rounded-md border border-rule bg-raised p-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ row.label }}</dt>
                <dd class="mt-1 text-sm text-ink-strong">
                    <EnergyGauge v-if="row.key === 'energy'" :energy="row.value as number | null" />
                    <MoodPill v-else-if="row.key === 'mood'" :tier="row.value as string | null" unrecorded="N/A, not recorded" />
                    <span
                        v-else
                        class="font-mono tabular-nums"
                        :title="row.value === null ? `${row.label} has not been recorded for this run.` : undefined"
                    >
                        {{ row.value === null ? 'N/A' : group(row.value as number) }}
                    </span>
                </dd>
            </div>
        </dl>
    </section>
</template>
