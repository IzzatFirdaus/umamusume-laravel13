<script setup lang="ts">
import CapsuleHeader from './CapsuleHeader.vue';
import { computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

interface CalendarSlotOption {
    id: number;
    // "Title · half · tier" is composed by the controller; the tier fallback ('no grade')
    // is part of the option label, so it never lives here.
    label: string;
    tier: string;
}

interface SlotOption {
    id: number;
    title: string;
}

interface TurnOption {
    id: number;
    turn: number;
}

interface ObjectiveOption {
    index: number;
    name: string;
}

interface RaceEntryRow {
    id: number;
    // Every string here is the finished display value the Blade composed from the models
    // (fallbacks included: 'a race with no calendar row', 'no grade', 'no placement',
    // 'turn not named', 'circles not read' / 'N circles', 'no period' / 'period N').
    title: string;
    status: string;
    trainer_entered: boolean;
    tier: string;
    placement: string;
    turn: string;
    circles: string;
    period: string;
}

const props = defineProps<{
    showUrl: string;
    racesUrl: string;
    // Composition flags, never a scenario slug (G-33). The Blade self-gated on
    // composesPanel('race_calendar') || composesGradeObjectives().
    composesRaceCalendar: boolean;
    composesTeamRace: boolean;
    composesGradeObjectives: boolean;
    // Server-resolved disclosure (R67): old('entry_mode') wins over the query, which wins
    // over 'calendar'. No local mode state, so a mode switch (a navigation) cannot leave a
    // surviving component instance submitting the branch it no longer shows.
    entryMode: 'calendar' | 'manual';
    year: number;
    yearLabel: string;
    calendarSlots: CalendarSlotOption[];
    manualSlots: SlotOption[];
    turns: TurnOption[];
    raceStatuses: string[];
    maxCircles: number;
    objectives: ObjectiveOption[];
    entries: RaceEntryRow[];
    old: Record<string, unknown>;
}>();

// Failed writes come back through a redirect, so the form opens on the flashed input.
function previous(key: string, fallback = ''): string {
    const value = props.old[key];

    return value === undefined || value === null ? fallback : String(value);
}

const visible = computed(() => props.composesRaceCalendar || props.composesGradeObjectives);

// The select always renders one selected option (the first), so the posted default is the
// first enum value even when nothing was flashed; an empty status would be a refusal the
// Blade could never produce.
const form = useForm({
    race_catalog_slot_id: previous('race_catalog_slot_id'),
    scenario_slot_id: previous('scenario_slot_id'),
    title: previous('title'),
    month: previous('month'),
    half: previous('half'),
    tier: previous('tier'),
    status: previous('status', props.raceStatuses[0] ?? ''),
    placement: previous('placement'),
    turn_entry_id: previous('turn_entry_id'),
    circles: previous('circles'),
    objective_index: previous('objective_index'),
});

// Post only the fields the visible branch rendered, the way only rendered Blade inputs
// submitted: the manual path prohibits `scenario_slot_id`, and the calendar branch has no
// `title`/`month`/`half`/`tier` to send.
form.transform((data) => {
    const shared = {
        entry_mode: props.entryMode,
        status: data.status,
        placement: data.placement,
        turn_entry_id: data.turn_entry_id,
        circles: data.circles,
        objective_index: data.objective_index,
    };

    return props.entryMode === 'manual'
        ? { ...shared, title: data.title, month: data.month, half: data.half, tier: data.tier }
        : { ...shared, race_catalog_slot_id: data.race_catalog_slot_id, scenario_slot_id: data.scenario_slot_id };
});

const modes = [
    { key: 'calendar', label: 'Calendar race' },
    { key: 'manual', label: 'Race not on the calendar' },
];

// Switching branch is a navigation, not a mutation (R67): the GET carries `year` too,
// because a GET form replaces the query string whole and a Classic tab would answer with
// the year the run has actually reached.
function switchMode(key: string): void {
    router.get(props.showUrl, { entry_mode: key, year: props.year });
}

const monthOptions = Array.from({ length: 12 }, (_, i) => String(i + 1));
const circleOptions = computed(() => Array.from({ length: props.maxCircles + 1 }, (_, i) => String(i)));
</script>

<template>
    <div v-if="visible" class="rounded-md border border-rule bg-panel p-3">
        <!-- The capsule anatomy is DESIGN.md §2.3's, owned by CapsuleHeader.vue. -->
        <CapsuleHeader title="Races" class="mb-3" />

        <div class="mb-3 flex flex-wrap gap-2" aria-label="Race entry mode">
            <button
                v-for="mode in modes"
                :key="mode.key"
                type="button"
                :aria-pressed="entryMode === mode.key ? 'true' : 'false'"
                class="rounded-md border px-3 py-1.5 text-sm font-medium"
                :class="entryMode === mode.key ? 'border-pick-line bg-pick/10 text-ink-strong' : 'border-rule text-ink-muted'"
                @click="switchMode(mode.key)"
            >
                {{ mode.label }}
            </button>
        </div>

        <form
            class="flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm"
            :aria-busy="form.processing"
            @submit.prevent="form.post(racesUrl)"
        >
            <template v-if="entryMode === 'calendar'">
                <label class="flex min-w-0 flex-col gap-1">
                    <span class="font-medium text-ink">Calendar race · {{ yearLabel }} year</span>
                    <template v-if="calendarSlots.length === 0">
                        <input
                            type="text"
                            class="rounded-md border border-rule bg-sunken px-2 py-1 text-ink-muted"
                            value="no calendar races in this year"
                            disabled
                        >
                        <span class="text-xs text-ink-muted">
                            Races are fetched data, and this career year has none of them.
                        </span>
                    </template>
                    <select
                        v-else
                        v-model="form.race_catalog_slot_id"
                        name="race_catalog_slot_id"
                        class="w-full min-w-0 max-w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                    >
                        <option
                            v-for="slot in calendarSlots"
                            :key="slot.id"
                            :value="String(slot.id)"
                            :data-tier="slot.tier"
                        >
                            {{ slot.label }}
                        </option>
                    </select>
                </label>

                <label v-if="manualSlots.length > 0" class="flex min-w-0 flex-col gap-1">
                    <span class="font-medium text-ink">Trainer-entered race</span>
                    <select
                        v-model="form.scenario_slot_id"
                        name="scenario_slot_id"
                        class="w-full min-w-0 max-w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                    >
                        <option value="">not a hand-entered race</option>
                        <option v-for="slot in manualSlots" :key="slot.id" :value="String(slot.id)">
                            {{ slot.title }}
                        </option>
                    </select>
                </label>
            </template>

            <template v-else>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Race title *</span>
                    <input
                        v-model="form.title"
                        type="text"
                        name="title"
                        required
                        maxlength="255"
                        placeholder="e.g. Practice Race"
                        class="min-w-48 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                    >
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Month *</span>
                    <select v-model="form.month" name="month" required class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                        <option value="">select</option>
                        <option v-for="month in monthOptions" :key="month" :value="month">{{ month }}</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Half *</span>
                    <select v-model="form.half" name="half" required class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                        <option value="">select</option>
                        <option value="Early">Early</option>
                        <option value="Late">Late</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Tier (optional)</span>
                    <input
                        v-model="form.tier"
                        type="text"
                        name="tier"
                        maxlength="10"
                        class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                    >
                    <span class="text-xs text-ink-muted">No prefill for manual races</span>
                </label>
            </template>

            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Outcome</span>
                <select v-model="form.status" name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option v-for="status in raceStatuses" :key="status" :value="status">{{ status }}</option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Placement</span>
                <input
                    v-model="form.placement"
                    type="number"
                    name="placement"
                    min="1"
                    class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                >
            </label>

            <!-- KI-17: the Trainer names the turn; nothing guesses it from a race date (D-270). -->
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Logged turn</span>
                <template v-if="turns.length === 0">
                    <input
                        type="text"
                        class="rounded-md border border-rule bg-sunken px-2 py-1 text-ink-muted"
                        value="no turns logged to name"
                        disabled
                    >
                    <span class="text-xs text-ink-muted">Log the turn first, then name it on its race.</span>
                </template>
                <select v-else v-model="form.turn_entry_id" name="turn_entry_id" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not named</option>
                    <option v-for="turn in turns" :key="turn.id" :value="String(turn.id)">Turn {{ turn.turn }}</option>
                </select>
            </label>

            <label v-if="composesTeamRace" class="flex flex-col gap-1">
                <span class="font-medium text-ink">Circles read</span>
                <select v-model="form.circles" name="circles" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">not read</option>
                    <option v-for="circles in circleOptions" :key="circles" :value="circles">{{ circles }}</option>
                </select>
            </label>

            <label v-if="composesGradeObjectives" class="flex flex-col gap-1">
                <span class="font-medium text-ink">Counts toward</span>
                <select v-model="form.objective_index" name="objective_index" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">no period</option>
                    <option v-for="objective in objectives" :key="objective.index" :value="String(objective.index)">
                        {{ objective.index }}. {{ objective.name }}
                    </option>
                </select>
            </label>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong"
            >
                Record race
            </button>

            <!-- The three trailing sentences are `errors->first(field, format)` literals the
                 Blade never substituted (no `:message` token), so it printed them verbatim
                 whenever any error existed. Kept as written. -->
            <p v-if="form.hasErrors" class="w-full text-sm text-risk" role="alert">
                {{ form.errors.race_catalog_slot_id ?? '' }}
                {{ form.errors.scenario_slot_id ?? '' }}
                {{ form.errors.title ?? '' }}
                {{ form.errors.month ?? '' }}
                {{ form.errors.half ?? '' }}
                {{ form.errors.circles ?? '' }}
                A period index is one of the four objectives. Placement is a finish number, 1 or above.
                That turn was not logged on this run.
            </p>
        </form>

        <p v-if="entries.length === 0" class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="status">
            No races recorded for this run.
        </p>
        <ul v-else class="mt-3 flex flex-col gap-1.5" aria-label="Recorded races">
            <li
                v-for="entry in entries"
                :key="entry.id"
                class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
            >
                <span class="min-w-0 flex-1">
                    <span class="font-semibold text-ink-strong">{{ entry.title }}</span>
                    <span class="ml-1.5 text-xs text-ink-muted">{{ entry.status }}</span>
                    <span v-if="entry.trainer_entered" class="ml-1 text-[10px] text-ink-muted">Trainer-entered</span>
                </span>
                <span class="flex flex-wrap gap-x-3 font-mono text-xs tabular-nums text-ink-muted">
                    <span>{{ entry.tier }}</span>
                    <span>{{ entry.placement }}</span>
                    <span>{{ entry.turn }}</span>
                    <span v-if="composesTeamRace">{{ entry.circles }}</span>
                    <span v-if="composesGradeObjectives">{{ entry.period }}</span>
                </span>
            </li>
        </ul>
    </div>
</template>
