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
    /**
     * The card's own heading. An active run reads "Stats and state"; a completed one reads
     * "Final stats", the word the HFW gives it once nothing is left to change.
     */
    title?: string;
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

const currentHint = (stat: Stat): string => (stat.current === null ? 'No turn has recorded this stat yet.' : `Entered: ${group(stat.current)}.`);

/**
 * The tier's headline percentage (`UMAMUSUME_REFERENCE.md` §1.1.6's training column: +20% at GREAT
 * down to −20% at AWFUL). DM-5 makes the mood reading word + arrow + percentage, and every cockpit
 * HFW draws `GREAT ↑ +20%`, so this is the one column the pill carries. The reference's second
 * column (pre-race attributes) is not drawn on this screen.
 */
const MOOD_TRAINING_PCT: Record<string, number> = {
    GREAT: 20,
    GOOD: 10,
    NORMAL: 0,
    BAD: -10,
    AWFUL: -20,
};

/** The tier's percentage, signed, or null for a tier nobody recorded. */
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

    // The HFW's own sentence, verbatim. It was two sentences that said the same thing twice: the
    // first restated the mechanic and the second restated the absence, so the card carried a
    // paragraph where the wireframe carries a line.
    const caption = 'Bars are drawn against the scenario ceiling.';

    return props.stats.some((stat: Stat): boolean => stat.target === null)
        ? 'No build target was set for this run, so bars are drawn against the scenario ceiling.'
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
        <!-- HFW-OVERRIDE (text: final-stats-heading): a completed run's card reads "Final stats" in
             `cockpit-ura-finale.html`; the active-run pages read "Stats and state". One component
             answers both through this prop, so the word follows the run's state and not the file. -->
        <h2 id="career-state-heading" class="text-lg font-semibold text-ink-strong">{{ props.title ?? 'Stats and state' }}</h2>

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
                        <!-- HFW's own shape: the denominator sits inside `<s>`, which `.srow .v s`
                             renders small and muted. HFW-OVERRIDE (text: denominator-word): R2-09
                             requires the word `cap` or `target` to travel with the second number so
                             one screen's denominator is never read as the other's, and
                             `cockpit-unity-cup.html` prints the number alone. The word stays. -->
                        <s class="flex items-baseline gap-1">
                            <span>/</span>
                            <span>{{ group(denominator(stat) as number) }}</span>
                            <span class="text-xs">{{ props.denominatorLabel }}</span>
                        </s>
                    </template>
                </span>

                <div v-if="stat.current !== null && denominator(stat) !== null" class="track" aria-hidden="true">
                    <i :style="{ width: `${percent(stat)}%` }"></i>
                </div>
                <p v-else-if="stat.current === null" class="col-span-2 mt-1 text-xs text-ink-muted">{{ currentHint(stat) }}</p>
            </li>
        </ul>

        <!-- The HFW's four tiles in a 2x2 grid: `.tiles` + `.tile`, key above value. It was a `dl`
             of bordered `div`s, which read as a different component beside the same cards elsewhere. -->
        <ul class="tiles mt-4 grid-cols-2">
            <li v-for="row in props.meta" :key="row.key" class="tile">
                <p class="k">{{ row.label }}</p>
                <div class="v">
                    <EnergyGauge v-if="row.key === 'energy'" :energy="row.value as number | null" :band-text="row.band_text ?? null" />
                    <template v-else-if="row.key === 'mood'">
                        <!-- HFW-OVERRIDE (text: mood-effect-columns): the reference carries a training
                             and a running percentage beside the tier, and this card printed both.
                             Every cockpit HFW draws the word, the arrow and the tier's own percentage
                             and nothing else, so the two extra columns are excess on this screen and
                             are cut. The figures stay available on the reference a Trainer reads. -->
                        <MoodPill
                            :tier="row.value as string | null"
                            :pct="moodPercent(row.value as string | null)"
                            unrecorded="N/A, not recorded"
                        />
                    </template>
                    <AbsenceValue
                        v-else-if="row.value === null"
                        class="font-mono tabular-nums"
                        :reason="`${row.label} has not been recorded for this run.`"
                        compact
                    />
                    <!-- HFW-OVERRIDE (text: fan-ladder): this tile printed the fan count with its
                         ladder class and the gap to the next band. Every cockpit HFW prints the bare
                         count in this tile, and the ladder is a Dashboard reading, so the suffix is
                         excess here. `fanLadder` stays a prop so a caller that wants it can add it
                         back rather than re-deriving the band. -->
                    <span v-else class="font-mono tabular-nums">{{ group(row.value as number) }}</span>
                </div>
            </li>
        </ul>
    </section>
</template>
