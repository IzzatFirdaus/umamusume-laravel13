<script setup lang="ts">
/*
 * The career state panel (SCREEN-009 §13): five stats and four meta values.
 *
 * Each stat shows the entered current value over the denominator its caller names: the Trainer's own
 * target, or the run's ceiling (the figure entered for that run where one exists, the scenario's
 * published cap otherwise), with a bar for the distance between them. The word `target` or `cap` is a
 * required prop because printing one screen's denominator under the other's name is the defect R2-09
 * closed. The HFW's cockpit draws the ceiling, so the bar answers "how far to the roof" there; the
 * advisor's ranking against the target is a separate reading and stays stated in the rail.
 * The bar is `aria-hidden` and the numbers are printed beside it, so nothing
 * here is a value carried by a fill alone (SCREEN-009 §34, WCAG 1.4.1).
 *
 * An unrecorded value is `AbsenceValue`'s: the marker plus its reason, which a keyboard and a screen
 * reader can both reach. It used to be `N/A` with a `title`, and a `title` answers a mouse only
 * (`DESIGN.md` §5). A zero is still printed as `0`, never as an absence, and a caller whose denominator
 * is a target that no one set draws no track and states the absence in words rather than an empty
 * track that reads as "at zero".
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
    /**
     * A-0.11: the client's Mood Effect headline (the `GREAT` tier's +20% training / +4% running),
     * sourced from `config('uma.mood_effect')` (`UMAMUSUME_REFERENCE.md` §1.1.6). Rendered beside the
     * mood readout; null when a caller does not carry the figure, so no percentage is invented.
     */
    moodEffect?: { training: number; running: number } | null;
}>();

const group = (n: number): string => n.toLocaleString('en-US');

const growthFor = (stat: Stat): number | null => {
    const value = props.growthRate?.[stat.key];

    return typeof value === 'number' ? value : null;
};

// `Star · 30,755 to 240,000`: the ladder's own three facts. The gap and the threshold are shipped
// together by `FanLadder::fromValue()` so they cannot come from different bands; the row names the
// threshold so a reader does not need to know 240,000 from outside (A-0.10, walk-1 Screen 8). Null when
// the ladder does not resolve, so the row prints the bare count rather than a class nobody sourced (D-220).
const fanSuffix = computed((): string | null => {
    const ladder = props.fanLadder;

    if (ladder === null || ladder === undefined || ladder.class === null || ladder.gap === null || ladder.nextThreshold === null) {
        return null;
    }

    return `${ladder.class} · ${group(ladder.gap)} to ${group(ladder.nextThreshold)}`;
});

const currentHint = (stat: Stat): string => (stat.current === null ? 'No turn has recorded this stat yet.' : `Entered: ${group(stat.current)}.`);

/**
 * D-259 / DM-5: the client's Mood Effect panel, training column (`UMAMUSUME_REFERENCE.md` §1.1.6,
 * client capture 2026-09-27). The five values are the client's own and are not interpolated: the ramp
 * is not a formula, and the report of 2026-10-24 keeps the config to one sourced entry rather than a
 * table. One owner would be `MoodTier` beside `arrow()`; the table lives here until that enum grows
 * the method, because this slice owns no enum file.
 */
const MOOD_TRAINING_PCT: Record<string, number> = {
    GREAT: 20,
    GOOD: 10,
    NORMAL: 0,
    BAD: -10,
    AWFUL: -20,
};

/** The tier's training percentage, signed, or null for a tier nobody recorded. */
const moodPercent = (tier: string | null): string | null => {
    if (tier === null || MOOD_TRAINING_PCT[tier] === undefined) {
        return null;
    }

    const value = MOOD_TRAINING_PCT[tier];

    return `${value >= 0 ? '+' : ''}${value}%`;
};

/**
 * The bars' own caption. The Cockpit draws them against the run's ceiling (HFW annotation 3): the
 * figure the Trainer entered where one exists, the scenario's published cap otherwise. The second
 * sentence fires only where the build target is absent, which is the state the HFW names: the advisor
 * ranks against a target that does not exist, so the bars are the only reading the screen has.
 */
const barCaption = computed((): string | null => {
    if (props.denominatorLabel !== 'cap') {
        return null;
    }

    const caption = 'Bars are drawn against the run\'s ceiling: the figure entered for this run where one exists, the scenario\'s published cap otherwise.';
    const noTarget = 'No build target was set for this run.';

    return props.stats.some((stat: Stat): boolean => stat.target === null)
        ? `${caption} ${noTarget}`
        : caption;
});

// The second number's own denominator: the scenario ceiling when the label says `cap`, the Trainer's own
// target otherwise. `cap` is always a number; `target` is null until one is entered, and the row says so
// in words rather than printing the cap under the target's name (R2-09).
const denominator = (stat: Stat): number | null =>
    props.denominatorLabel === 'cap' ? stat.cap : stat.target;

function percent(stat: Stat): number {
    const total = denominator(stat);

    if (stat.current === null || total === null || total <= 0) {
        return 0;
    }

    return Math.min(100, (stat.current / total) * 100);
}
</script>

<template>
    <section aria-labelledby="career-state-heading" class="card rounded-lg p-4">
        <h2 id="career-state-heading" class="text-lg font-semibold text-ink-strong">Stats and state</h2>

        <p v-if="barCaption !== null" class="mt-3 text-sm text-ink-muted">{{ barCaption }}</p>

        <!-- The HFW's row shape (`.srow`): label and growth rate on the left, current over the
             denominator on the right, and the distance between them as a track beneath. The track is
             `aria-hidden` and both numbers print beside it, so nothing here is a value carried by a
             fill alone (SCREEN-009 §34, WCAG 1.4.1). -->
        <ul class="mt-2">
            <li v-for="stat in props.stats" :key="stat.key" class="srow">
                <span class="k">
                    {{ stat.label }}
                    <!-- The card's growth rate for this stat, beside its label, where the Trainer
                         read it. Rendered only when recorded, never as a default +0%. -->
                    <em v-if="growthFor(stat) !== null">+{{ growthFor(stat) }}%</em>
                </span>
                <span class="v flex items-baseline gap-1 font-mono tabular-nums">
                    <!-- The marker carries the same reason the visible sentence below the bar
                         prints, from its one owner (`currentHint`), so the cell explains itself on
                         hover and for a screen reader as well as for a reader of the row. The
                         sentence stays: a reason reachable only by hover is the shape DESIGN.md §5
                         rules out, and this is the pair rather than a swap. -->
                    <span v-if="stat.current === null" :title="currentHint(stat)">N/A</span>
                    <span v-else>{{ group(stat.current) }}</span>
                    <template v-if="denominator(stat) !== null">
                        <span class="font-normal text-ink-muted">/</span>
                        <!-- The second number's own word (R2-09): `target` on this screen, `cap` where the
                             ceiling is the question. An absent denominator prints nothing here, because
                             the caption above the grid has already said it for all five rows. -->
                        <span class="font-normal text-ink-muted">
                            {{ group(denominator(stat) as number) }}
                            <span class="text-xs">{{ props.denominatorLabel }}</span>
                        </span>
                    </template>
                </span>

                <div v-if="stat.current !== null && denominator(stat) !== null" class="col-span-2 mt-1.5 h-1.5 overflow-hidden rounded-md bg-sunken" aria-hidden="true">
                    <div class="h-full bg-green-deep" :style="{ width: `${percent(stat)}%` }"></div>
                </div>
                <p v-else-if="stat.current === null" class="col-span-2 mt-1 text-xs text-ink-muted">{{ currentHint(stat) }}</p>
            </li>
        </ul>

        <dl class="mt-4 grid grid-cols-2 gap-2">
            <div v-for="row in props.meta" :key="row.key" class="rounded-md border border-rule bg-raised p-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ row.label }}</dt>
                <dd class="mt-1 text-sm text-ink-strong">
                    <EnergyGauge v-if="row.key === 'energy'" :energy="row.value as number | null" :band-text="row.band_text ?? null" />
                    <template v-else-if="row.key === 'mood'">
                        <MoodPill :tier="row.value as string | null" unrecorded="N/A, not recorded" />
                        <!-- D-259 / DM-5: the tier's own training percentage, signed, beside the word
                             and the arrow. `Normal` prints `+0%` rather than a blank, so a real zero is
                             never read as a missing reading. -->
                        <span v-if="moodPercent(row.value as string | null) !== null" class="ml-1 font-mono text-xs tabular-nums text-ink-muted">{{ moodPercent(row.value as string | null) }}</span>
                        <!-- A-0.11: the client's Mood Effect headline beside the tier (great tier). -->
                        <span v-if="props.moodEffect" class="ml-1 text-xs text-ink-muted">
                            (training +{{ props.moodEffect.training }}%, running +{{ props.moodEffect.running }}%)
                        </span>
                    </template>
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
