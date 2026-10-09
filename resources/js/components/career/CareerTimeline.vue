<script setup lang="ts">
/*
 * The vertical rail for SCREEN-018 (plan §8 D14). Each row is a keyboard-reachable disclosure
 * (aria-expanded on the summary); the expanded pane shows the decision audit (BEFORE / ACTION /
 * EXPECTED / ACTUAL / RESULT). Glyph + text are both rendered so the state never relies on
 * colour alone. Absent fields render `N/A` with a title, never defaults.
 *
 * Props are flattened from `TimelineController::buildEntries()`; `valueOf` exposes them read-only
 * because the same row is also reached from the parent filter.
 */

interface Stats {
    speed: number | null;
    stamina: number | null;
    power: number | null;
    guts: number | null;
    wit: number | null;
    sp: number | null;
    energy: number | null;
    fans: number | null;
}

interface Entry {
    key: string;
    kind: 'turn' | 'race' | 'event' | 'inheritance' | 'failure';
    kind_label: string;
    glyph: '●' | '◉' | '○' | '×';
    turn: number;
    action_label: string;
    before: Stats | null;
    after: Stats | null;
    expected: Stats | null;
    actual: Stats | null;
    mood: string | null;
    /** The facility levels a turn row recorded, or null when it recorded none. */
    facilities?: { label: string; level: number }[] | null;
    result: string;
    corrected: boolean;
    correction_id: string | null;
    decision_url: string;
}

const props = defineProps<{
    entries: Entry[];
    activeKinds: string[];
}>();

const STAT_FIELDS: { key: keyof Stats; label: string }[] = [
    { key: 'speed', label: 'Speed' },
    { key: 'stamina', label: 'Stamina' },
    { key: 'power', label: 'Power' },
    { key: 'guts', label: 'Guts' },
    { key: 'wit', label: 'Wit' },
];

const valueOf = (stats: Stats | null, field: keyof Stats): string => {
    if (stats === null) {
        return 'N/A';
    }
    const raw = stats[field];
    if (raw === null) {
        return 'N/A';
    }
    return String(raw);
};

const titleFor = (stats: Stats | null, kind: 'before' | 'after' | 'expected' | 'actual'): string => {
    if (stats === null) {
        return `No ${kind} state on file: this row did not record it.`;
    }
    return `End-of-turn value: this is the absolute end-of-turn reading (ADR-0003).`;
};

const moodTitle = (mood: string | null): string =>
    mood === null ? 'No mood recorded on this turn.' : 'Mood the run recorded at the end of this turn.';

const resultTitle = (entry: Entry): string =>
    entry.corrected
        ? `Updated after the original record. Correction id ${entry.correction_id}.`
        : 'Result is read from the stored row. No derived or projected value.';

const kindVisible = (entry: Entry): boolean =>
    props.activeKinds.length === 0 || props.activeKinds.includes(entry.kind_label);
</script>

<template>
    <ol v-if="entries.some(kindVisible)" class="flex flex-col gap-3" aria-label="Career timeline">
        <li v-for="entry in entries" :key="entry.key" v-show="kindVisible(entry)">
            <details class="rounded-md border border-rule bg-panel">
                <summary
                    class="flex min-h-11 cursor-pointer flex-wrap items-baseline gap-3 px-3 py-2 text-sm [&::-webkit-details-marker]:hidden"
                    :aria-label="`Turn ${entry.turn}: ${entry.action_label}`"
                >
                    <span class="font-mono text-base text-ink-strong" aria-hidden="true">{{ entry.glyph }}</span>
                    <span class="rounded-md bg-pick px-1.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-on-pick">{{ entry.kind_label }}</span>
                    <span class="font-mono tabular-nums text-xs text-ink-muted">Turn {{ entry.turn }}</span>
                    <span class="font-medium text-ink-strong">{{ entry.action_label }}</span>
                    <span
                        v-if="entry.corrected"
                        class="rounded-full bg-warning px-2 py-0.5 text-xs font-semibold text-on-warning"
                        :title="`Correction ${entry.correction_id}: row was updated.`"
                    >Updated</span>
                </summary>

                <div class="border-t border-rule p-3 text-sm">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">
                        <div v-if="entry.kind === 'turn'">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">BEFORE</dt>
                            <dd class="mt-1 font-mono tabular-nums">
                                <table>
                                    <tbody>
                                        <tr v-for="field in STAT_FIELDS" :key="field.key">
                                            <td class="pr-3 text-xs text-ink-muted">{{ field.label }}</td>
                                            <td>
                                                <span :title="titleFor(entry.before, 'before')">{{ valueOf(entry.before, field.key) }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">ACTION</dt>
                            <dd class="mt-1 text-ink-strong">{{ entry.action_label }}</dd>
                            <p v-if="entry.mood !== null" class="mt-1 text-xs text-ink-muted">
                                Mood: <span :title="moodTitle(entry.mood)">{{ entry.mood }}</span>
                            </p>
                        </div>

                        <!-- The facility levels the ladder showed this turn, when the Trainer read them.
                             One named absence for the whole block rather than five rows of `N/A`: a turn
                             logged without the ladder is the ordinary case, not five missing readings. -->
                        <div v-if="entry.kind === 'turn'">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">FACILITIES</dt>
                            <dd class="mt-1 font-mono tabular-nums">
                                <ul v-if="entry.facilities !== null && entry.facilities !== undefined" class="space-y-0.5">
                                    <li v-for="facility in entry.facilities" :key="facility.label">
                                        <span class="pr-3 text-xs text-ink-muted">{{ facility.label }}</span>
                                        <span>{{ facility.level }}</span>
                                    </li>
                                </ul>
                                <span v-else class="text-xs text-ink-muted">Facilities not recorded</span>
                            </dd>
                        </div>

                        <div v-if="entry.kind === 'turn'">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">EXPECTED</dt>
                            <dd class="mt-1 font-mono tabular-nums">
                                <table>
                                    <tbody>
                                        <tr v-for="field in STAT_FIELDS" :key="field.key">
                                            <td class="pr-3 text-xs text-ink-muted">{{ field.label }}</td>
                                            <td>
                                                <span :title="titleFor(entry.expected, 'expected')">{{ valueOf(entry.expected, field.key) }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </dd>
                        </div>

                        <div v-if="entry.kind === 'turn'">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">ACTUAL</dt>
                            <dd class="mt-1 font-mono tabular-nums">
                                <table>
                                    <tbody>
                                        <tr v-for="field in STAT_FIELDS" :key="field.key">
                                            <td class="pr-3 text-xs text-ink-muted">{{ field.label }}</td>
                                            <td>
                                                <span :title="titleFor(entry.actual, 'actual')">{{ valueOf(entry.actual, field.key) }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </dd>
                        </div>

                        <div v-else>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">ACTUAL</dt>
                            <dd class="mt-1 text-ink">{{ entry.result }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">RESULT</dt>
                            <dd class="mt-1 text-ink" :title="resultTitle(entry)">{{ entry.result }}</dd>
                        </div>
                    </dl>

                    <a
                        v-if="entry.decision_url"
                        :href="entry.decision_url"
                        class="mt-3 inline-flex min-h-11 items-center font-medium text-ink-strong underline"
                    >Open the matching decision screen</a>
                </div>
            </details>
        </li>
    </ol>
</template>
