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
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';
import { BaseButton, BaseInput, BaseSelect } from '../../components/Form';

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
    surface: string | null;
    distance_label: string | null;
    distance_band: string | null;
    aptitude: { surface: string | null; distance: string | null; label: string };
    blocked_reason: string | null;
    payout: { tier: string; stat: number; sp: number; source: string; verified_at: string } | null;
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

// A refused manual write answers in its own alert, and focus moves there rather than being left on
// the press target below the fold (WCAG 2.2 SC 3.3.1, the idiom TrainingDetail's guided form owns).
const alertRef = ref<HTMLElement | null>(null);

watch(
    () => manualForm.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }

        await nextTick();
        alertRef.value?.focus();
    },
);

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

/*
 * The HFW's race decision: one table of the races this turn offers, one selected race's detail, and
 * one commit. Entering is the same calendar write RaceCard's drawer posts; recording a result stays
 * available in a disclosure because it is how a completed race clears an objective.
 */
const selectedId = ref<number | null>(
    (props.races.find((race) => race.blocked_reason === null) ?? props.races[0])?.id ?? null,
);

const selected = computed(() => props.races.find((race) => race.id === selectedId.value) ?? null);

const selectRace = (id: number): void => {
    selectedId.value = id;
};

const trainingUrl = computed(() => props.run.run_url.replace(/\/cockpit$/, '/training'));

const undoableTurn = computed(() => Math.max(1, (props.nextTurn?.turn ?? 1) - 1));

const enterForm = useForm({ turn_entry_id: '' });

const enterRace = (): void => {
    if (selected.value === null) {
        return;
    }

    enterForm
        .transform(() => ({
            entry_mode: 'calendar',
            race_catalog_slot_id: String(selected.value?.id),
            status: 'Entered',
            turn_entry_id: null,
        }))
        .post(props.entry.action, { preserveScroll: true });
};

const enterFormFor = (race: Race): void => {
    enterForm
        .transform(() => ({
            entry_mode: 'calendar',
            race_catalog_slot_id: String(race.id),
            status: 'Entered',
            turn_entry_id: null,
        }))
        .post(props.entry.action, { preserveScroll: true });
};

const resultForm = useForm({ placement: '' });

const recordResult = (): void => {
    if (selected.value === null) {
        return;
    }

    resultForm
        .transform(() => ({
            entry_mode: 'calendar',
            race_catalog_slot_id: String(selected.value?.id),
            status: 'Completed',
            placement: resultForm.placement,
        }))
        .post(props.entry.action, { preserveScroll: true });
};

const aptitudePill = (race: Race): string => {
    const letter = race.aptitude.distance ?? race.aptitude.surface ?? 'N/A';

    return letter;
};

</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :position="props.nextTurn?.position_label ?? null"
        :run-url="props.run.run_url"
        current="races"
    >
        <Head :title="'Race decision: ' + props.run.trainee" />

        <template #head>
            <PageHeader
                eyebrow="Race decision"
                title="Enter a race, or skip the turn"
                description="Races pay fans, Skill Points and stat gains; a training turn usually pays more stats. Pick the race or close this screen."
            >
                <template #actions>
                    <a
                        :href="props.run.run_url"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong"
                    >Back to cockpit</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <div class="grid gap-4 g-side">
            <section class="card" aria-labelledby="available-heading">
                <h2 id="available-heading">Available this turn</h2>
                <p class="sub">
                    The calendar's races at this turn. Every row states the grade, the surface and the
                    distance band, and the trainee's own letter for that axis.
                </p>

                <p v-if="props.races.length === 0" class="mt-2 text-sm text-ink">
                    {{ props.empty ?? 'No race is scheduled on this turn.' }}
                </p>

                <table v-else class="t mt-2 w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th scope="col" class="text-left">Race</th>
                            <th scope="col" class="text-left">Grade</th>
                            <th scope="col" class="text-left">Surface · distance</th>
                            <th scope="col" class="text-right">Fans needed</th>
                            <th scope="col" class="text-right">Skill Pt</th>
                            <th scope="col" class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="race in props.races"
                            :key="race.id"
                            :class="race.id === selectedId ? 'is-current bg-green-tint' : ''"
                            class="cursor-pointer border-b border-rule-soft"
                            @click="selectRace(race.id)"
                        >
                            <td class="py-2 pr-3">
                                <b>{{ race.title }}</b>
                                <span
                                    v-if="race.distance_label"
                                    class="block text-xs text-ink-muted"
                                >{{ race.distance_label }}</span>
                                <span v-if="race.is_mandatory" class="ml-1 rounded bg-brand-accent-soft px-1.5 py-0.5 text-[10px] font-bold text-brand-accent-ink">Mandatory</span>
                            </td>
                            <td class="py-2 pr-3">{{ race.tier ?? 'N/A' }}</td>
                            <td class="py-2 pr-3" :title="race.aptitude.label">
                                {{ race.surface ?? 'Surface not recorded' }} · {{ race.distance_band ?? 'band not recorded' }}
                                <span class="pill pill-neutral ml-1 align-[1px]">{{ aptitudePill(race) }}</span>
                            </td>
                            <td class="py-2 pr-3 text-right font-mono">{{ race.fans_needed ?? '-' }}</td>
                            <td class="py-2 pr-3 text-right font-mono">{{ race.payout ? '+' + race.payout.sp : '-' }}</td>
                            <td class="py-2 text-right">
                                <button
                                    v-if="race.blocked_reason === null"
                                    type="button"
                                    class="btn btn-primary btn-sm min-h-11"
                                    @click.stop="enterFormFor(race)"
                                >{{ enterForm.processing ? 'Entering…' : 'Enter' }}</button>
                                <button
                                    v-else
                                    type="button"
                                    class="btn btn-sm min-h-11"
                                    disabled
                                    :title="race.blocked_reason"
                                >{{ race.maiden_gated ? 'Maiden lock not cleared' : 'Blocked' }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <div class="flex flex-col gap-4">
                <section v-if="selected !== null" class="card" aria-labelledby="race-detail-heading">
                    <h2 id="race-detail-heading">{{ selected.title }}</h2>

                    <div class="mt-2 grid gap-2.5 g-2">
                        <div class="tile"><div class="k">Grade</div><div class="v">{{ selected.tier ?? 'N/A' }}</div></div>
                        <div class="tile"><div class="k">Distance</div><div class="v font-mono">{{ selected.distance_label ?? 'N/A' }}</div></div>
                        <div class="tile"><div class="k">Aptitude</div><div class="v text-sm">{{ selected.aptitude.label }}</div></div>
                        <div class="tile"><div class="k">Skill Points</div><div class="v font-mono">{{ selected.payout ? '+' + selected.payout.sp : 'N/A' }}</div></div>
                    </div>

                    <p class="mt-3 text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Placement payouts</p>
                    <table class="t mt-1 w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th scope="col" class="text-left">Placement</th>
                                <th scope="col" class="text-right">Skill Pt</th>
                                <th scope="col" class="text-right">Stat</th>
                                <th scope="col" class="text-right">Fans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="is-current">
                                <td><b>1st</b></td>
                                <td class="text-right font-mono"><b>{{ selected.payout ? selected.payout.sp : '-' }}</b></td>
                                <td class="text-right font-mono">{{ selected.payout ? '+' + selected.payout.stat : '-' }}</td>
                                <td class="text-right font-mono">scales</td>
                            </tr>
                            <tr><td>2nd</td><td class="text-right font-mono">-</td><td class="text-right font-mono">-</td><td class="text-right font-mono">scales</td></tr>
                            <tr><td>3rd</td><td class="text-right font-mono">-</td><td class="text-right font-mono">-</td><td class="text-right font-mono">scales</td></tr>
                        </tbody>
                    </table>

                    <Note class="mt-2.5">
                        <b>Only the 1st-place figure is sourced.</b> A first-place finish at 0 Race Bonus pays
                        G1 +10 stat / +45 SP, G2-G3 +8 / +35 and OP-Pre-OP +5 / +30; Trackblazer pays the same
                        stat at a lower SP. Second and third are <b>not published</b>, so those cells stay blank
                        rather than extrapolated.
                    </Note>
                    <Note class="mt-2">
                        The 34-percent Race Bonus breakpoint is the one the guide names for URA Finale and
                        Unity Cup; the 50-percent row is Trackblazer's. A planner must print the payout the
                        client shows rather than recompute it.
                    </Note>

                    <details class="mt-3">
                        <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Record the result of this race</summary>
                        <form class="mt-2 flex flex-wrap items-end gap-3" @submit.prevent="recordResult">
                            <label class="flex flex-col gap-1">
                                <span class="text-xs text-ink-muted">Placement</span>
                                <BaseInput v-model="resultForm.placement" type="number" name="placement" min="1" max="99" />
                            </label>
                            <BaseButton type="submit" variant="primary" :disabled="resultForm.processing">
                                {{ resultForm.processing ? 'Recording…' : 'Record result' }}
                            </BaseButton>
                        </form>
                    </details>
                </section>

                <section class="card" aria-labelledby="consecutive-heading">
                    <h2 id="consecutive-heading">Consecutive races</h2>
                    <p class="text-[12.5px] text-ink-muted">
                        Races in a row raise the failure risk. Trackblazer publishes the odds; URA Finale and
                        Unity Cup do not, so this screen states the count and not a probability.
                    </p>
                    <div class="srow mt-2">
                        <span class="k">Races in a row so far</span>
                        <span class="v font-mono">not recorded</span>
                    </div>
                    <Note class="mt-2">
                        No probability is printed for this scenario. Inventing one would be the "score" the app
                        is forbidden to add.
                    </Note>
                </section>

                <p v-if="enterForm.hasErrors" role="alert" class="rounded-md border border-risk bg-raised p-2 text-sm text-risk">
                    <span v-for="(message, key) in enterForm.errors" :key="key" class="block">{{ message }}</span>
                </p>

                <div class="savebar">
                    <span class="dirty" role="status">
                        {{ selected !== null ? selected.title + ' selected' : 'No race chosen' }}
                    </span>
                    <button type="button" class="btn btn-quiet min-h-11" title="The build has no undo; this screen proposes one.">Undo turn {{ undoableTurn }}</button>
                    <a :href="trainingUrl" class="btn min-h-11 inline-flex items-center">Train instead</a>
                    <button
                        type="button"
                        class="btn btn-primary min-h-11"
                        :disabled="selected === null || selected.blocked_reason !== null || enterForm.processing"
                        @click="enterRace()"
                    >{{ enterForm.processing ? 'Entering…' : 'Enter a race' }}</button>
                </div>
            </div>
        </div>

        <!-- HFW OVERRIDE (manual race): the wireframe draws only calendar races; the app can record a
             free race that no calendar row holds. It stays available, collapsed. -->
        <details class="card mt-4">
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Race not on the calendar</summary>
            <form class="mt-3 grid max-w-3xl grid-cols-2 gap-3 text-sm @4xl:grid-cols-3" :aria-busy="manualForm.processing" @submit.prevent="submitManual">
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Race title *</span>
                    <BaseInput v-model="manualForm.title" type="text" name="title" required maxlength="255" placeholder="e.g. Practice Race" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Month *</span>
                    <BaseSelect v-model="manualForm.month" name="month" required :options="monthOptions.map(month => ({ value: month, label: month }))" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Half *</span>
                    <BaseSelect v-model="manualForm.half" name="half" required :options="[{ value: '', label: 'select' }, { value: 'Early', label: 'Early' }, { value: 'Late', label: 'Late' }]" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Tier (optional)</span>
                    <BaseInput v-model="manualForm.tier" type="text" name="tier" maxlength="10" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Outcome</span>
                    <BaseSelect v-model="manualForm.status" name="status" :options="props.race_statuses.map(status => ({ value: status, label: status }))" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Placement</span>
                    <BaseInput v-model="manualForm.placement" type="number" name="placement" min="1" max="99" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Fans gained</span>
                    <BaseInput v-model="manualForm.fans_gain" type="number" name="fans_gain" min="0" />
                </label>
                <div class="flex items-end">
                    <BaseButton type="submit" variant="primary" :disabled="manualForm.processing">
                        {{ manualForm.processing ? 'Saving…' : 'Record race' }}
                    </BaseButton>
                </div>
                <p v-if="manualForm.hasErrors" ref="alertRef" role="alert" tabindex="-1" class="rounded-md border border-risk bg-raised p-2 text-sm text-risk @4xl:col-span-3">
                    <span v-for="(message, key) in manualForm.errors" :key="key" class="block">{{ message }}</span>
                </p>
            </form>
        </details>

        <p v-if="manualForm.hasErrors || enterForm.hasErrors" class="sr-only" role="alert">The race write was refused.</p>
    </CareerLayout>
</template>
