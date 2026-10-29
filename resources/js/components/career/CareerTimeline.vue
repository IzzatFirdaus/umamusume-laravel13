<script setup lang="ts">
/*
 * The vertical rail for SCREEN-018 (plan §8 D14), grouped the way the domain counts.
 *
 * **DM-1 override in force.** The HFW draws one flat table; this renders the same record as a
 * collapsible group per career year (`DESIGN-CORPUS.md` §5: "grouping by phase, collapsible - exactly
 * the shape a 24-turn run needs"). A career is `TrainingRun::TURNS_PER_YEAR` (24) turns a year for
 * three years; the groups are those years, and a row past the grid is the finale phase rather than
 * turn 73. `<!-- DOMAIN OVERRIDE (DM-1): three 24-turn years; the HFW draws one flat list -->`
 *
 * **The turn number is the spine**, first in every row, left-aligned and monospaced, so the column
 * scans vertically. Each row is a keyboard-reachable disclosure whose expanded pane is the decision
 * audit (BEFORE / ACTION / FACILITIES / PREVIEW / END OF TURN / RESULT). Glyph and text both render,
 * so state never rides on colour alone.
 *
 * **A blank is not a zero.** A stat the turn did not record renders as an empty cell with one named
 * absence for the block, never `0`; the block's own note says so. This replaces the previous `N/A`
 * per cell plus five `title=` tooltips: a reason carried only in a `title` is invisible to a touch
 * user and to a screen reader (AGENTS.md §13).
 *
 * Props are flattened from `TimelineController::buildEntries()`; `valueOf` exposes them read-only
 * because the same row is also reached from the parent filter.
 */
import { computed } from 'vue';
import StatusBadge from '../ui/StatusBadge.vue';

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
    actual: Stats | null;
    mood: string | null;
    /** The facility levels a turn row recorded, or null when it recorded none. */
    facilities?: { label: string; level: number }[] | null;
    /** The failure percentage the client showed before this turn, or null when it was not read. */
    failure_rate?: number | null;
    /** The preview row the client printed for this turn, or null when none of it was read. */
    preview_gains?: { label: string; value: number }[] | null;
    result: string;
    corrected: boolean;
    correction_id: string | null;
    decision_url: string;
}

const props = defineProps<{
    entries: Entry[];
    activeKinds: string[];
    /** `CareerCalendar::TURNS_PER_CAREER`; the finale block sits outside it. */
    totalTurns: number;
}>();

/** A career year is 24 turns (`TrainingRun::TURNS_PER_YEAR`). */
const TURNS_PER_YEAR = 24;

const YEAR_LABELS = ['Junior Year', 'Classic Year', 'Senior Year'] as const;

const STAT_FIELDS: { key: keyof Stats; label: string }[] = [
    { key: 'speed', label: 'Speed' },
    { key: 'stamina', label: 'Stamina' },
    { key: 'power', label: 'Power' },
    { key: 'guts', label: 'Guts' },
    { key: 'wit', label: 'Wit' },
];

const valueOf = (stats: Stats | null, field: keyof Stats): string => {
    const raw = stats === null ? null : stats[field];

    // Blank, not `0` and not `N/A` five times over: the block prints one absence instead.
    return raw === null ? '' : String(raw);
};

const hasReading = (stats: Stats | null): boolean =>
    stats !== null && STAT_FIELDS.some((field) => stats[field.key] !== null);

const kindVisible = (entry: Entry): boolean =>
    props.activeKinds.length === 0 || props.activeKinds.includes(entry.kind_label);

/** The phase a turn belongs to. Anything past the grid is the finale phase, never turn 73+. */
const phaseOf = (turn: number): number =>
    turn > props.totalTurns ? YEAR_LABELS.length : Math.floor((turn - 1) / TURNS_PER_YEAR);

interface Group {
    key: string;
    label: string;
    /** The turns this phase covers, as the client's own span. */
    span: string;
    /** "18 of 24 turns recorded · 20 entries", or the entry count alone outside the grid. */
    caption: string;
    entries: Entry[];
}

const groups = computed<Group[]>(() => {
    const visible = props.entries.filter(kindVisible);

    return [...YEAR_LABELS.map((label, index) => ({ label, index })), { label: 'After the grid', index: YEAR_LABELS.length }]
        .map(({ label, index }) => {
            const rows = visible.filter((entry) => phaseOf(entry.turn) === index);
            const first = index * TURNS_PER_YEAR + 1;
            const last = Math.min((index + 1) * TURNS_PER_YEAR, props.totalTurns);
            const turns = rows.filter((entry) => entry.kind === 'turn').length;
            const outside = index === YEAR_LABELS.length;

            return {
                key: `phase-${index}`,
                label: outside ? 'After the grid · finale block' : label,
                span: outside
                    ? `Outside the ${props.totalTurns}-turn grid; never numbered ${props.totalTurns + 1}`
                    : `Turns ${first}-${last}`,
                caption: outside
                    ? `${rows.length} entr${rows.length === 1 ? 'y' : 'ies'} outside the grid`
                    : `${turns} of ${TURNS_PER_YEAR} turns recorded · ${rows.length} entries`,
                entries: rows,
            };
        })
        .filter((group) => group.entries.length > 0);
});

/** The last group with rows is the phase being read, so it opens like the HFW's open phase. */
const openKey = computed<string | null>(() => groups.value.at(-1)?.key ?? null);
</script>

<template>
    <div v-if="groups.length > 0" class="flex flex-col gap-3">
        <section v-for="group in groups" :key="group.key" :aria-labelledby="`${group.key}-heading`">
            <details class="card" :open="group.key === openKey">
                <summary
                    class="flex min-h-11 cursor-pointer flex-wrap items-baseline gap-x-3 gap-y-1 px-3 py-2 text-sm [&::-webkit-details-marker]:hidden"
                >
                    <h3 :id="`${group.key}-heading`" class="text-base font-semibold text-ink-strong">{{ group.label }}</h3>
                    <span class="font-mono tabular-nums text-xs text-ink-muted">{{ group.span }}</span>
                    <span class="text-xs text-ink-muted">{{ group.caption }}</span>
                </summary>

                <ol class="flex flex-col gap-2 px-3 pb-3" :aria-label="`${group.label} entries, oldest first`">
                    <li v-for="entry in group.entries" :key="entry.key">
                        <details class="rounded-md border border-rule bg-raised">
                            <summary
                                class="flex min-h-11 cursor-pointer flex-wrap items-baseline gap-x-2 px-3 py-2 text-sm [&::-webkit-details-marker]:hidden"
                                :aria-label="`Turn ${entry.turn}: ${entry.action_label}`"
                            >
                                <!-- The spine: turn number first, left-aligned, mono, tabular. A fixed
                                     width is what keeps the column straight down the rail. -->
                                <span class="w-10 shrink-0 font-mono tabular-nums text-xs text-ink-muted">T{{ entry.turn }}</span>
                                <span class="w-5 shrink-0 font-mono text-base text-ink-strong" aria-hidden="true">{{ entry.glyph }}</span>
                                <span class="flex flex-wrap items-baseline gap-2">
                                    <span class="rounded-md bg-pick px-1.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-on-pick">{{ entry.kind_label }}</span>
                                    <span class="font-medium text-ink-strong">{{ entry.action_label }}</span>
                                    <StatusBadge v-if="entry.corrected" state="unconfirmed" label="Updated" />
                                </span>
                            </summary>

                            <div class="border-t border-rule p-3 text-sm">
                                <dl class="grid grid-cols-1 gap-x-6 gap-y-2 min-[1081px]:grid-cols-2">
                                    <div v-if="entry.kind === 'turn'">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">BEFORE</dt>
                                        <dd class="mt-1 font-mono tabular-nums">
                                            <table v-if="hasReading(entry.before)">
                                                <tbody>
                                                    <tr v-for="field in STAT_FIELDS" :key="field.key">
                                                        <td class="pr-3 text-xs font-mono text-ink-muted">{{ field.label }}</td>
                                                        <td>{{ valueOf(entry.before, field.key) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <span v-else class="text-xs text-ink-muted">
                                                No earlier turn on file: this is the first logged turn.
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">ACTION</dt>
                                        <dd class="mt-1 text-ink-strong">{{ entry.action_label }}</dd>
                                        <p v-if="entry.mood !== null" class="mt-1 text-xs text-ink-muted">
                                            Mood at the end of this turn: <span class="text-ink">{{ entry.mood }}</span>
                                        </p>
                                    </div>

                                    <!-- The facility levels the ladder showed this turn, when the Trainer read them.
                                         One named absence for the whole block rather than five rows of blanks: a turn
                                         logged without the ladder is the ordinary case, not five missing readings. -->
                                    <div v-if="entry.kind === 'turn'">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">FACILITIES</dt>
                                        <dd class="mt-1 font-mono tabular-nums">
                                            <ul v-if="entry.facilities !== null && entry.facilities !== undefined" class="space-y-0.5">
                                                <li v-for="facility in entry.facilities" :key="facility.label">
                                                    <span class="pr-3 text-xs font-mono text-ink-muted">{{ facility.label }}</span>
                                                    <span>{{ facility.level }}</span>
                                                </li>
                                            </ul>
                                            <span v-else class="text-xs text-ink-muted">Facilities not recorded</span>
                                        </dd>
                                    </div>

                                    <!-- The two readings the client printed before the turn resolved. One named
                                         absence for the block, like FACILITIES above. -->
                                    <div v-if="entry.kind === 'turn'">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">PREVIEW</dt>
                                        <dd class="mt-1 font-mono tabular-nums">
                                            <p v-if="entry.failure_rate !== null && entry.failure_rate !== undefined" class="text-ink-strong">
                                                <span class="pr-3 text-xs font-mono text-ink-muted">Failure rate</span>{{ entry.failure_rate }}%
                                            </p>
                                            <ul v-if="entry.preview_gains !== null && entry.preview_gains !== undefined" class="space-y-0.5">
                                                <li v-for="gain in entry.preview_gains" :key="gain.label">
                                                    <span class="pr-3 text-xs font-mono text-ink-muted">{{ gain.label }}</span>
                                                    <span>{{ gain.value > 0 ? `+${gain.value}` : gain.value }}</span>
                                                </li>
                                            </ul>
                                            <span
                                                v-if="(entry.failure_rate === null || entry.failure_rate === undefined)
                                                    && (entry.preview_gains === null || entry.preview_gains === undefined)"
                                                class="text-xs text-ink-muted"
                                            >Preview not recorded</span>
                                        </dd>
                                    </div>

                                    <div v-if="entry.kind === 'turn'">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">END OF TURN</dt>
                                        <dd class="mt-1 font-mono tabular-nums">
                                            <table v-if="hasReading(entry.actual)">
                                                <tbody>
                                                    <tr v-for="field in STAT_FIELDS" :key="field.key">
                                                        <td class="pr-3 text-xs font-mono text-ink-muted">{{ field.label }}</td>
                                                        <td>{{ valueOf(entry.actual, field.key) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <span v-else class="text-xs text-ink-muted">End-of-turn stats not recorded</span>
                                            <p class="mt-1 font-sans text-xs text-ink-muted">
                                                Blank means not recorded, never zero. These are the end-of-turn
                                                readings this turn stored, not a delta.
                                            </p>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">RESULT</dt>
                                        <dd class="mt-1 text-ink">{{ entry.result }}</dd>
                                        <p v-if="entry.corrected" class="mt-1 text-xs text-ink-muted">
                                            Updated after the original save. Correction {{ entry.correction_id }}.
                                        </p>
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
            </details>
        </section>
    </div>
</template>