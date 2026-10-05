<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Ported from x-race-calendar. The page, not this component, decides whether the panel
// exists: it renders here only when the scenario's config opens `panels.race_calendar`
// (D-221, gate G-34), so no scenario name is ever spoken inside the component.
interface CalendarSlotItem {
    state: string;
    label?: string | null;
    // Pre-formatted by the page ("1,234"); a fan figure is a formatted value, not a
    // quantity to re-do here. Present only on `fan_locked` items.
    fans?: string | null;
    manual?: boolean;
}

interface CalendarMonth {
    halves: {
        Early: { slots: CalendarSlotItem[] };
        Late: { slots: CalendarSlotItem[] };
    };
}

const props = withDefaults(
    defineProps<{
        // TrainingRun::calendarCells() shape, twelve months January-first.
        cells: CalendarMonth[];
        // Clamped career year; null keeps the panels pre-year shape (no tabs, no word).
        year: number | null;
        // Built server-side with fullUrlWithQuery so the year stays part of the address:
        // a Trainer can hand over "her Classic spring" as a URL and back keeps the tab.
        yearTabs: { year: number; label: string; url: string }[];
        // The YEARS word for the active tab ('Junior'|'Classic'|'Senior'), for the region name.
        yearWord: string | null;
        // The turn to decide, as its position within `year`, 1-24; null shows no outline
        // rather than falling back to the last turn played.
        nextTurn: number | null;
        monthLabels?: string[];
    }>(),
    {
        monthLabels: () => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
);

// Full literal class strings so Tailwind's scanner sees them. The three locks must never be
// confused (D-173): goal is the warm outline plus pennant, fan_locked solid, maiden_locked
// dashed (D-181). `goal` and `maiden_locked` stay wired though no data reaches them today:
// 79ffad5 withdrew `is_mandatory` as a rendering input, and the parser writes every
// `is_maiden_gated` false. Kept on purpose, not deleted on the way past.
const stateClass: Record<string, string> = {
    // The client's empty half-month: measured disabled fill (DESIGN.md §6.20); the words
    // moved into the accessible name, where a screen reader still hears "No race".
    empty: 'border border-rule bg-disabled text-ink-muted py-1.5',
    open: 'border border-dashed border-green-line bg-raised text-ink py-1.5',
    goal: 'border-2 border-goal-line bg-raised text-ink-strong py-3',
    fan_locked: 'border-2 border-solid border-rule bg-sunken text-ink-muted py-1.5',
    maiden_locked: 'border-2 border-dashed border-ink-muted bg-raised text-ink-muted py-1.5',
    past: 'border border-rule bg-transparent text-ink-muted py-1.5',
    // The pick pair the selected year tab and a spirit burst already use.
    current: 'border-2 border-pick-line bg-pick text-on-pick py-1.5',
};

// The model state stays `past` (the fact about the entry); the spoken word is the client's
// `Scheduled`, matching the pink pill under the name.
const stateWord: Record<string, string> = {
    empty: 'No race',
    open: 'Entry open',
    goal: 'Mandatory goal',
    fan_locked: 'Fan gate',
    maiden_locked: 'Maiden rule',
    past: 'Scheduled',
    current: 'Next',
};

const priority: Record<string, number> = {
    goal: 6,
    current: 5,
    fan_locked: 4,
    maiden_locked: 3,
    open: 2,
    past: 1,
};

const slotCount = computed(() => props.monthLabels.length * 2);

const regionLabel = computed(
    () =>
        `Race calendar, ${props.yearWord !== null ? `${props.yearWord} year, ` : ''}${slotCount.value} turn slots`,
);

interface RenderedCell {
    half: string;
    month: string;
    state: string;
    slots: CalendarSlotItem[];
    multi: boolean;
    entered: boolean;
    fans: string | null;
    manual: boolean;
    ariaText: string;
}

const cellsRendered = computed<RenderedCell[]>(() => {
    // Reading down a column walks the year: the DOM order is the turn order, and the row
    // order above is the turn order, so the slot index is the turn with one added.
    return Array.from({ length: 24 }, (_, slotIndex) => {
        const monthIndex = Math.floor(slotIndex / 2);
        const half = slotIndex % 2 === 0 ? 'Early' : 'Late';
        const month = props.monthLabels[monthIndex];
        const slots = props.cells[monthIndex]?.halves[half]?.slots ?? [];
        const isNext = props.nextTurn !== null && props.nextTurn === slotIndex + 1;

        // One border per cell, derived from its slots: the strongest candidate wins. The
        // turn-being-decided is a property of the cell, not of any race in it, so it enters
        // as one more candidate for the same loop rather than as a slot.
        const candidates = [...slots.map((slotItem) => slotItem.state), ...(isNext ? ['current'] : [])];
        let state = 'empty';
        for (const candidate of candidates) {
            if ((priority[candidate] ?? 0) > (priority[state] ?? 0)) {
                state = candidate;
            }
        }
        if (!(state in stateClass)) {
            state = 'empty';
        }

        const firstLabel = slots[0]?.label ?? null;
        const fans = state === 'fan_locked' ? (slots[0]?.fans ?? null) : null;
        // Not the same question as the border state: a cell holding one entered race and one
        // open one draws the open border while the entered race stays entered.
        const entered = slots.some((slotItem) => slotItem.state === 'past');

        let ariaText =
            slots.length > 1
                ? `${slots.length} races: ${slots.map((slotItem) => slotItem.label ?? '').join(', ')}`
                : (firstLabel ?? stateWord[state]);
        if (entered && state !== 'past') {
            ariaText += '; one entered';
        }
        if (isNext) {
            ariaText += '; next turn to play';
        }

        return {
            half,
            month,
            state,
            slots,
            multi: slots.length > 1,
            entered,
            fans,
            manual: slots.some((slotItem) => slotItem.manual === true),
            ariaText,
        };
    });
});
</script>

<template>
    <div class="rounded-md border border-rule bg-panel p-3">
        <!-- The capsule header's anatomy inlined (x-capsule-header is still Blade; its Vue
             extraction belongs with the next Blade file that has no Vue twin). Chrome fill,
             never the client's bright lime: white on lime measures 1.99:1 (DESIGN.md §2.3, D-3). -->
        <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pr-5 pl-20">
            <span class="text-base font-bold text-on-chrome">Race calendar</span>
        </div>

        <div v-if="year !== null" class="mb-3 flex gap-1" role="tablist" aria-label="Career year">
            <Link
                v-for="tab in yearTabs"
                :key="tab.year"
                :href="tab.url"
                role="tab"
                :aria-selected="tab.year === year ? 'true' : 'false'"
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="
                    tab.year === year
                        ? 'bg-pick text-on-pick'
                        : 'border border-rule bg-raised text-ink hover:bg-sunken'
                "
            >
                {{ tab.label }}
            </Link>
        </div>

        <!-- A bare "no data" line tells the Trainer nothing: the scenario owns the calendar
             either way, so the structure stays and the message says what is missing (D-220). -->
        <p
            v-if="cells.length === 0"
            class="mb-3 rounded-md border border-dashed border-rule bg-raised px-3 py-2 text-sm text-ink"
        >
            No races entered for this run yet. The grid is the {{ slotCount }} turn slots this scenario runs
            on, so it renders even when empty; add a race on a turn to fill it.
        </p>

        <!-- Four cells across, six rows: the client's shape. The old 12-column band scrolled
             horizontally and so carried the tabindex (KI-25); this grid does not clip, so the
             region name stays and the focus stop went with the scroll. -->
        <div class="grid grid-cols-4 items-start gap-x-3 gap-y-4" role="region" :aria-label="regionLabel">
            <div v-for="cell in cellsRendered" :key="`${cell.half}-${cell.month}`" class="flex flex-col items-center gap-1">
                <!-- `role="img"` is what makes the label a name at all: a label on a bare div
                     is a property most technologies do not expose. The caption below leads with
                     the same two words in the same order. -->
                <div
                    class="relative w-full rounded-md border px-1 text-center text-xs leading-tight"
                    :class="stateClass[cell.state]"
                    role="img"
                    :aria-label="`${cell.half} ${cell.month}: ${stateWord[cell.state]}, ${cell.ariaText}`"
                >
                    <!-- D-181: the Goal pennant, the client's red flag top-right. Only the filled
                         edge carries colour; border-<color> alone sets all four sides. -->
                    <span
                        v-if="cell.state === 'goal'"
                        class="absolute top-0 right-0 h-0 w-0 border-t-3 border-b-3 border-l-5 border-t-transparent border-b-transparent border-l-goal"
                        aria-hidden="true"
                    ></span>
                    <!-- An empty half-month carries the client's muted plus and no word; the state
                         is still spoken in the accessible name. -->
                    <span v-if="cell.slots.length === 0" class="block text-base leading-none text-ink-faint" aria-hidden="true">+</span>
                    <ul v-else-if="cell.multi" class="space-y-0.5">
                        <li
                            v-for="(slotItem, slotIndex) in cell.slots"
                            :key="slotIndex"
                            class="truncate font-semibold"
                            :title="slotItem.label ?? ''"
                        >
                            {{ slotItem.label ?? stateWord[cell.state] }}
                        </li>
                    </ul>
                    <span v-else class="block truncate font-semibold" :title="cell.slots[0]?.label ?? stateWord[cell.state]">
                        {{ cell.slots[0]?.label ?? stateWord[cell.state] }}
                    </span>
                    <!-- The client's pink `Scheduled` pill, never dimmed (UX §2.11); keyed to the
                         slot, not the border state, because a shared half-month keeps the open border. -->
                    <span
                        v-if="cell.entered"
                        class="mt-0.5 inline-block rounded-full bg-mood-great px-1.5 py-0.5 font-mono text-[10px] font-bold text-on-mood"
                    >Scheduled</span>
                    <span v-if="cell.fans !== null" class="block font-mono text-xs tabular-nums text-ink-muted">
                        {{ cell.fans }} fans
                    </span>
                    <span v-if="cell.manual" class="block text-[10px] text-ink-muted">Trainer-entered</span>
                </div>
                <span class="text-xs font-semibold text-ink-muted">{{ cell.half }} {{ cell.month }}</span>
            </div>
        </div>

        <p class="mt-2 text-xs text-ink-muted">
            {{ slotCount }} turn slots, Early and Late for each month. A fan gate shows the number it needs; a
            maiden rule is a different lock and a dashed outline, because one is a quantity to work toward and
            the other is an event to reach.
        </p>
    </div>
</template>
