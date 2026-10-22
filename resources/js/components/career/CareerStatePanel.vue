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
    /**
     * The words of a band reading, present only on the Energy row and only when the turn recorded a band
     * rather than a number. A band is not an absence, so the gauge prints it instead of `N/A` (R3-01).
     */
    band_text?: string | null;
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
    /**
     * The word for the second number's own denominator. The Cockpit's second number is the Trainer's
     * target and the event screen's is the run's ceiling, so the word is a required prop rather than a
     * guess: one screen's denominator printed under the other's name is the defect this closes (R2-09).
     */
    denominatorLabel: 'target' | 'cap';
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

const currentHint = (stat: Stat): string => (stat.current === null ? 'No turn has recorded this stat yet.' : `Entered: ${group(stat.current)}.`);

// The missing build target is one fact about the run, not five facts about five rows. It reads once,
// above the grid, in the sentence `Result.vue` already uses for the same state; a row keeps only what
// is true of itself. (Owner ruling 2026-10-09 asked that the target gap never go unstated; naming it
// once above the five rows states it for all of them.)
const targetCaption = computed((): string | null =>
    props.denominatorLabel === 'target' && props.stats.some((stat: Stat): boolean => stat.target === null)
        ? 'No build target was set for this run.'
        : null,
);

function percent(stat: Stat): number {
    if (stat.current === null || stat.target === null || stat.target <= 0) {
        return 0;
    }

    return Math.min(100, (stat.current / stat.target) * 100);
}

// The second number's own denominator: the scenario ceiling when the label says `cap`, the Trainer's own
// target otherwise. `cap` is always a number; `target` is null until one is entered, and the row says so
// in words rather than printing the cap under the target's name (R2-09).
const denominator = (stat: Stat): number | null =>
    props.denominatorLabel === 'cap' ? stat.cap : stat.target;
</script>

<template>
    <section aria-labelledby="career-state-heading" class="card rounded-lg p-4">
        <h2 id="career-state-heading" class="text-lg font-semibold text-ink-strong">Stats and state</h2>

        <p v-if="targetCaption !== null" class="mt-3 text-sm text-ink-muted">{{ targetCaption }}</p>

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
                        <!-- The marker carries the same reason the visible sentence below the bar
                             prints, from its one owner (`currentHint`), so the cell explains itself on
                             hover and for a screen reader as well as for a reader of the row. The
                             sentence stays: a reason reachable only by hover is the shape DESIGN.md §5
                             rules out, and this is the pair rather than a swap. -->
                        <span v-if="stat.current === null" :title="currentHint(stat)">N/A</span>
                        <span v-else>{{ group(stat.current) }}</span>
                        <template v-if="denominator(stat) !== null">
                            <span class="text-ink-muted">/</span>
                            <!-- The second number's own word (R2-09): `target` on this screen, `cap` where the
                                 ceiling is the question. An absent denominator prints nothing here, because
                                 the caption above the grid has already said it for all five rows. -->
                            <span>
                                {{ group(denominator(stat) as number) }}
                                <span class="text-xs font-normal text-ink-muted">{{ props.denominatorLabel }}</span>
                            </span>
                        </template>
                    </div>
                </div>

                <div v-if="stat.current !== null && stat.target !== null" class="mt-1.5 h-1.5 overflow-hidden rounded-md bg-sunken" aria-hidden="true">
                    <div class="h-full bg-green-deep" :style="{ width: `${percent(stat)}%` }"></div>
                </div>
                <p v-else-if="stat.current === null" class="mt-1 text-xs text-ink-muted">{{ currentHint(stat) }}</p>
            </li>
        </ul>

        <dl class="mt-4 grid grid-cols-2 gap-2">
            <div v-for="row in props.meta" :key="row.key" class="rounded-md border border-rule bg-raised p-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ row.label }}</dt>
                <dd class="mt-1 text-sm text-ink-strong">
                    <EnergyGauge v-if="row.key === 'energy'" :energy="row.value as number | null" :band-text="row.band_text ?? null" />
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
