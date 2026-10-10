<script setup lang="ts">
/*
 * The Race Decision, `SCR-CAR-013` (SCREEN-011, plan §8 D10). Whether and where to race, at the turn
 * being decided.
 *
 * The page pattern is `Catalog/Index.vue`'s: `CareerLayout`, `<Head title>`, the `#title` slot, and
 * Inertia `Link` for the routes `spa.ts` resolves. The only write is inside `RaceCard`'s drawer, and
 * it posts to the existing `runs.races.store` through `useForm`, so this page holds no form of its
 * own.
 *
 * Loading and error are page-level (ADR-0007): the page's only user-initiated async action is the
 * drawer's Enter Race, which carries its own in-flight label, so Inertia's visit state is announced
 * rather than drawn as a skeleton. `invalid` and `exception` are both cancelable, and returning
 * `false` takes the failure out of Inertia's default modal so this page's own `role="alert"` is the
 * single surface.
 *
 * Every figure is a catalogue fact, an entered value, or a named absence. The brief's readiness band
 * and win figure are held on `ADR-0016` and print `N/A` with the reason.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import RaceCard from '../../components/career/RaceCard.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface Fact {
    key: string;
    label: string;
    value: string | null;
    title: string | null;
}

interface Race {
    id: number;
    title: string;
    tier: string | null;
    year_label: string;
    turn: number | null;
    fans_needed: number | null;
    maiden_gated: boolean;
    is_mandatory: boolean;
    is_special: boolean;
    facts: Fact[];
    detail_recorded: boolean;
    status: string | null;
    placement: string | null;
    fans_gain: number | null;
    grade_points: number | null;
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string };
    nextTurn: { year_label: string; turn: number; position_label: string } | null;
    races: Race[];
    deadlines: { id: number; title: string; year_label: string; turn: number | null; state: string; is_next: boolean; reached: boolean }[];
    readiness: { label: string; title: string };
    entry: { action: string; turns: { id: number; turn: number }[] };
    planner_url: string;
    empty: string | null;
    entry_mode: 'calendar' | 'manual';
    manual_slots: { id: number; title: string }[];
    manual_defaults: { month: string; half: string; tier: string };
    race_statuses: string[];
}>();

const { visiting, visitFailed } = useVisitState();

// The next obligation is the Level 1 item; the rest are context, so only one row is emphasised.
const nextDeadline = computed(() => props.deadlines.find((row) => row.is_next) ?? null);

// F2, plan §9.6 ruling 3: manual free-race entry. The branch is server-resolved (a query-string
// read, the same shape `RacePanel` uses), so switching is a navigation and this form can never
// submit the branch the page no longer shows. The form posts `entry_mode=manual` to
// `runs.races.store`, the route the calendar cards already use.
const modes = [
    { key: 'calendar', label: 'Calendar race' },
    { key: 'manual', label: 'Race not on the calendar' },
];

function switchMode(key: string): void {
    router.get(window.location.pathname, { entry_mode: key }, { preserveState: true, preserveScroll: true });
}

const manualForm = useForm({
    title: '',
    month: props.manual_defaults.month,
    half: props.manual_defaults.half,
    tier: props.manual_defaults.tier,
    status: props.race_statuses[0] ?? '',
    placement: '',
    fans_gain: '',
    turn_entry_id: '',
    objective_index: '',
});

// Post only what the manual branch renders, the way only rendered inputs submitted: the manual path
// prohibits `scenario_slot_id` and the Form Request refuses circles without a team-race slot.
manualForm.transform((data) => ({
    entry_mode: 'manual',
    title: data.title,
    month: data.month,
    half: data.half,
    tier: data.tier,
    status: data.status,
    placement: data.placement,
    fans_gain: data.fans_gain,
    turn_entry_id: data.turn_entry_id,
    objective_index: data.objective_index,
}));

const monthOptions = Array.from({ length: 12 }, (_, i) => String(i + 1));

function submitManual(): void {
    manualForm.post(props.entry.action, { preserveScroll: true });
}
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Race decision: ${props.run.trainee}`" />
        <template #title>Race decision</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <section aria-labelledby="race-turn-heading" class="rounded-md border border-rule bg-panel p-4">
            <h2 id="race-turn-heading" class="text-base font-semibold text-ink-strong">The turn being decided</h2>
            <p v-if="props.nextTurn !== null" class="mt-1 text-sm text-ink">
                {{ props.nextTurn.year_label }} ·
                {{ props.nextTurn.position_label }} ·
                <span class="font-mono tabular-nums">Turn {{ props.nextTurn.turn }}</span>
            </p>
            <p v-else class="mt-1 text-sm text-ink" title="No turn is being decided on this run.">N/A</p>
        </section>

        <!-- Level 1, Critical (design-2.0 §4): a career obligation gets strong emphasis, a glyph and a
             clear word. The glyph is aria-hidden and the word carries the meaning, so the state never
             rests on colour alone. -->
        <section
            v-if="props.deadlines.length > 0"
            aria-labelledby="race-deadlines-heading"
            class="mt-4 rounded-md border-2 border-goal-line bg-raised p-4"
        >
            <h2 id="race-deadlines-heading" class="text-base font-semibold text-ink-strong">
                <span aria-hidden="true" class="font-mono font-bold text-goal-line">!</span>
                Mandatory races
            </h2>
            <p class="mt-1 text-sm text-ink">
                The races this scenario obliges whichever trainee is running. A mandatory race is a
                career rule; it is not the same claim as a Goal, which is per-trainee and has no source here.
            </p>

            <ol class="mt-3 flex flex-col gap-1.5">
                <li
                    v-for="deadline in props.deadlines"
                    :key="deadline.id"
                    class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-panel px-3 py-2 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ deadline.title }}</span>
                        <span class="ml-1.5 text-xs text-ink-muted">
                            {{ deadline.year_label }}
                            <template v-if="deadline.turn !== null"> · Turn {{ deadline.turn }}</template>
                        </span>
                        <span v-if="deadline.is_next" class="ml-1.5 text-xs font-bold text-ink-strong">
                            <span aria-hidden="true">!</span> NEXT
                        </span>
                    </span>
                    <span class="shrink-0 text-xs font-bold text-ink-strong">{{ deadline.state }}</span>
                </li>
            </ol>
        </section>

        <p v-if="nextDeadline !== null" class="mt-2 text-sm text-ink">
            <span aria-hidden="true" class="font-mono font-bold text-goal-line">!</span>
            Next obligation: <span class="font-semibold text-ink-strong">{{ nextDeadline.title }}</span>,
            {{ nextDeadline.year_label }} turn {{ nextDeadline.turn }}. It reads "{{ nextDeadline.state }}".
        </p>

        <section aria-labelledby="race-readiness-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="race-readiness-heading" class="text-base font-semibold text-ink-strong">Readiness</h2>
            <p class="mt-1 text-sm text-ink">
                <span class="font-semibold text-ink-strong" :title="props.readiness.title">{{ props.readiness.label }}</span>
                <span class="ml-2 text-ink-muted">No readiness band is computed for this race.</span>
            </p>
        </section>

        <section aria-labelledby="race-list-heading" class="mt-4">
            <h2 id="race-list-heading" class="text-base font-semibold text-ink-strong">Races to decide between</h2>

            <!-- The branch switch. Two buttons that navigate the query string, the pattern
                 `RacePanel` uses; the branch is server-resolved so this form never submits the
                 branch the page no longer shows. -->
            <div class="mt-2 flex flex-wrap gap-2" aria-label="Race entry mode">
                <button
                    v-for="mode in modes"
                    :key="mode.key"
                    type="button"
                    :aria-pressed="props.entry_mode === mode.key ? 'true' : 'false'"
                    class="inline-flex min-h-11 items-center rounded-md border px-3 text-sm font-medium"
                    :class="props.entry_mode === mode.key ? 'border-pick-line bg-pick/10 text-ink-strong' : 'border-rule text-ink-muted'"
                    @click="switchMode(mode.key)"
                >
                    {{ mode.label }}
                </button>
            </div>

            <template v-if="props.entry_mode === 'calendar'">
                <!-- The door to the Scenario Race Planner, which draws the whole calendar rather than
                     this turn. A screen reachable only by URL is a defect (D14). -->
                <p class="mt-2 text-sm">
                    <Link :href="props.planner_url" class="inline-flex min-h-11 items-center rounded-md px-3 font-semibold text-ink underline">
                        Plan the whole calendar
                    </Link>
                </p>

                <p
                    v-if="props.empty !== null"
                    class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
                >
                    {{ props.empty }}
                </p>
                <ul v-else class="mt-3 flex flex-col gap-3">
                    <RaceCard
                        v-for="race in props.races"
                        :key="race.id"
                        :race="race"
                        :readiness="props.readiness"
                        :entry="props.entry"
                        :skip-url="props.run.run_url"
                    />
                </ul>
            </template>

            <!-- The manual free-race arm (F2, plan §9.6 ruling 3). Posts `entry_mode=manual` to
                 `runs.races.store`, which also creates the `scenario_slots` row of kind
                 `free_race`. -->
            <template v-else>
                <p class="mt-2 text-sm text-ink">
                    Some races are not on the career calendar: a rival's event, a scenario-only race.
                    Record one here and it appears in the run's race log the same way a calendar race does.
                </p>

                <form
                    class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-3"
                    :aria-busy="manualForm.processing"
                    @submit.prevent="submitManual"
                >
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Race title *</span>
                        <input v-model="manualForm.title" type="text" name="title" required maxlength="255" placeholder="e.g. Practice Race" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Month *</span>
                        <select v-model="manualForm.month" name="month" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">select</option>
                            <option v-for="month in monthOptions" :key="month" :value="month">{{ month }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Half *</span>
                        <select v-model="manualForm.half" name="half" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">select</option>
                            <option value="Early">Early</option>
                            <option value="Late">Late</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Tier (optional)</span>
                        <input v-model="manualForm.tier" type="text" name="tier" maxlength="10" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Outcome</span>
                        <select v-model="manualForm.status" name="status" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option v-for="status in props.race_statuses" :key="status" :value="status">{{ status }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Placement</span>
                        <input v-model="manualForm.placement" type="number" name="placement" min="1" max="99" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Fans gained</span>
                        <input v-model="manualForm.fans_gain" type="number" name="fans_gain" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Logged turn</span>
                        <select v-model="manualForm.turn_entry_id" name="turn_entry_id" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">not named</option>
                            <option v-for="turn in props.entry.turns" :key="turn.id" :value="String(turn.id)">Turn {{ turn.turn }}</option>
                        </select>
                    </label>
                    <div class="flex items-end">
                        <button
                            type="submit"
                            :disabled="manualForm.processing"
                            class="enamel inline-flex min-h-11 items-center justify-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:opacity-60"
                        >
                            {{ manualForm.processing ? 'Saving…' : 'Record race' }}
                        </button>
                    </div>
                    <p v-if="manualForm.hasErrors" role="alert" class="md:col-span-3 text-sm text-risk">
                        <span v-for="(message, key) in manualForm.errors" :key="key" class="block">{{ message }}</span>
                    </p>
                </form>

                <div v-if="props.manual_slots.length > 0" class="mt-4">
                    <h3 class="text-sm font-semibold text-ink-strong">Races entered by hand</h3>
                    <ul class="mt-2 flex flex-col gap-1.5" aria-label="Hand-entered races">
                        <li
                            v-for="slot in props.manual_slots"
                            :key="slot.id"
                            class="rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink"
                        >
                            {{ slot.title }}
                        </li>
                    </ul>
                </div>
            </template>
        </section>
    </CareerLayout>
</template>
