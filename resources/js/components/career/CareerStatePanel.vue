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
 * An unrecorded value is `AbsenceValue`'s: the marker plus its reason, which a keyboard and a screen
 * reader can both reach. It used to be `N/A` with a `title`, and a `title` answers a mouse only
 * (`DESIGN.md` §5). A zero is still printed as `0`, never as an absence, and a run with no target set has
 * no target bar and says so in words rather than drawing an empty track that reads as "at zero".
 *
 * StatBand was deliberately not reused here. Its bar is the scenario ceiling with the 1,200
 * halved-gains marker, which is the run record screen's question; giving it a second, target-shaped
 * meaning would change a landed component's contract for one caller.
 */
import { computed } from 'vue';
import AbsenceValue from '../AbsenceValue.vue';
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

const props = defineProps<{
    stats: Stat[];
    meta: Meta[];
    /**
     * The trainee's growth-rate row, keyed by the stat matrix (the same keys `stats` carries). Null
     * until the Trainer reads it off the card, so a stat with no entry shows no percentage rather than
     * a `+0%` nobody entered.
     */
    growthRate?: Record<string, number | null> | null;
    /**
     * The fan ladder the latest reading sits in, read server-side through `FanLadder`. Null when no
     * fan count is recorded; its `class` is null when no documented band contains the count, in which
     * case the row prints the bare number.
     */
    fanLadder?: { class: string | null; nextThreshold: number | null; gap: number | null } | null;
}>();

const group = (n: number): string => n.toLocaleString('en-US');

const growthFor = (stat: Stat): number | null => {
    const value = props.growthRate?.[stat.key];

    return typeof value === 'number' ? value : null;
};

// `Star · 30,755 to next`, the ladder's own two facts in one suffix; null when the ladder does not
// resolve, so the row prints the bare count rather than a class nobody sourced (D-220).
const fanSuffix = computed((): string | null => {
    const ladder = props.fanLadder;

    if (ladder === null || ladder === undefined || ladder.class === null || ladder.gap === null) {
        return null;
    }

    return `${ladder.class} · ${group(ladder.gap)} to next`;
});

const targetHint = (stat: Stat): string =>
    stat.target === null ? 'No target was entered for this stat.' : `Your target: ${group(stat.target)} of a possible ${group(stat.cap)}.`;

const currentHint = (stat: Stat): string => (stat.current === null ? 'No turn has recorded this stat yet.' : `Entered: ${group(stat.current)}.`);

// Ruled 2026-10-09 (owner): this row prints its reason as prose and carries no disclosure, so the sentence
// is the whole statement. Both halves are named when both are missing, because a single ternary let the
// target gap go unstated in the row's most common state: a run with no turns logged at all.
const statAbsence = (stat: Stat): string => {
    if (stat.current === null && stat.target === null) {
        return 'No turn has recorded this stat yet, and no target was entered for it.';
    }

    return stat.current === null ? currentHint(stat) : targetHint(stat);
};

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
                    <span class="text-sm font-semibold text-ink-strong">
                        {{ stat.label }}
                        <!-- The card's growth rate for this stat, beside its label, where the Trainer
                             read it. Rendered only when recorded, never as a default +0%. -->
                        <span v-if="growthFor(stat) !== null" class="ml-1 text-xs font-normal text-ink-muted">+{{ growthFor(stat) }}%</span>
                    </span>
                    <div class="flex items-baseline gap-1 font-mono text-sm tabular-nums text-ink-strong">
                        <span v-if="stat.current === null">N/A</span>
                        <span v-else>{{ group(stat.current) }}</span>
                        <span class="text-ink-muted">/</span>
                        <span v-if="stat.target === null">N/A</span>
                        <span v-else>{{ group(stat.target) }}</span>
                    </div>
                </div>

                <div v-if="stat.current !== null && stat.target !== null" class="mt-1.5 h-1.5 overflow-hidden rounded bg-sunken" aria-hidden="true">
                    <div class="h-full bg-green-deep" :style="{ width: `${percent(stat)}%` }"></div>
                </div>
                <p v-else class="mt-1 text-xs text-ink-muted">{{ statAbsence(stat) }}</p>
            </li>
        </ul>

        <dl class="mt-4 grid grid-cols-2 gap-2">
            <div v-for="row in props.meta" :key="row.key" class="rounded-md border border-rule bg-raised p-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ row.label }}</dt>
                <dd class="mt-1 text-sm text-ink-strong">
                    <EnergyGauge v-if="row.key === 'energy'" :energy="row.value as number | null" />
                    <MoodPill v-else-if="row.key === 'mood'" :tier="row.value as string | null" unrecorded="N/A, not recorded" />
                    <AbsenceValue
                        v-else-if="row.value === null"
                        class="font-mono tabular-nums"
                        :reason="`${row.label} has not been recorded for this run.`"
                        compact
                    />
                    <!-- The fan count with its ladder class and gap when the ladder resolves, and the
                         bare number otherwise (D-220: a count above every documented band has no class
                         this tool can claim). -->
                    <template v-else-if="row.key === 'fans'">
                        <span class="font-mono tabular-nums">{{ group(row.value as number) }}</span>
                        <span v-if="fanSuffix !== null" class="text-ink-muted"> · {{ fanSuffix }}</span>
                    </template>
                    <span v-else class="font-mono tabular-nums">{{ group(row.value as number) }}</span>
                </dd>
            </div>
        </dl>
    </section>
</template>
