<script setup lang="ts">
/*
 * The Event Decision, `SCR-CAR-014` (SCREEN-012, plan §8 D11). Record-only: an event choice and
 * its observed outcome go to TurnEvent, and the run's own recorded choices are the only
 * "known outcomes" the page shows. No event catalog exists, so nothing is derived from the
 * corpus, and no score, confidence or probability is computed anywhere on this screen.
 *
 * **The advisor refuses** (design-2.0 §36): TrainerAdvisor ranks training actions only and holds
 * no event advice, so the rail prints "No recommendation available" with that reason. The
 * Trainer's choice is the default path, not an override of a recommendation, and no option is
 * ever preselected.
 *
 * Page pattern is RaceDecision's: `CareerLayout`, `<Head title>`, the `#title` slot, page-level
 * `invalid`/`exception` handling with a single `role="alert"` surface, and `useForm` for the one
 * write. Unrecorded figures render as `N/A` with a `title`.
 *
 * Props contract is declared locally: TypeScript 7 ships no `lib/typescript.js` for
 * `@vue/compiler-sfc` to load (plan §11).
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';
import { BaseInput, BaseRadio, BaseSelect, BaseTextarea } from '../../components/Form';

interface RunSection {
    id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    run_url: string;
    cockpit_url: string;
}

interface StateSection {
    stats: { key: string; label: string; current: number | null; target: number | null; cap: number }[];
    meta: { key: string; label: string; value: number | string | null; band_text?: string | null }[];
}

interface EventRow {
    id: number;
    turn: number;
    event_type: string;
    source_label: string;
    source_name: string;
    choice_index: number | null;
    choice_label: string | null;
    support_card_name: string | null;
    bond_delta: number | null;
    origin_note: string | null;
    outcome_recorded: boolean;
}

interface KnownGroup {
    event_type: string;
    source_label: string;
    source_name: string;
    choices: { label: string; outcome: string | null; outcome_recorded: boolean }[];
}

interface AdvisorSection {
    recommendation: string | null;
    band: string | null;
    reasons: string[];
    alternative: string | null;
    risk: string | null;
}

interface WriteSection {
    action: string;
    turns: { id: number; turn: number }[];
    sources: { value: string; label: string }[];
}

const props = defineProps<{
    run: RunSection;
    state: StateSection;
    events: EventRow[];
    known: KnownGroup[];
    advisor: AdvisorSection;
    write: WriteSection;
    empty: string | null;
}>();

const { visiting, visitFailed } = useVisitState();

const form = useForm({
    turn: '',
    event_type: '',
    source_name: '',
    choice_index: null as number | null,
    choice_label: '',
    support_card_name: '',
    bond_delta: '',
    origin_note: '',
});

// The turn the entry is recorded on: one past the last logged turn, the same rule the other
// decision screens use; the Undo control names the turn before it.
const initialTurn = (props.write.turns.at(-1)?.turn ?? 0) + 1;
const undoableTurn = Math.max(1, initialTurn - 1);
form.turn = String(initialTurn);

const alert = ref<HTMLElement | null>(null);

watch(
    () => form.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }
        await nextTick();
        alert.value?.focus();
    },
);

// The flashed confirmation lands through `AppLayout`'s own `flash.status` banner (one surface
// for the whole app), so this page renders none of its own.

const submitChoice = (): void => {
    form.post(props.write.action, {
        preserveScroll: true,
        onSuccess: () => {
            // Only the per-choice fields clear. The source and event name stay put so the
            // just-recorded choice reappears in the picker as a known outcome, and nothing
            // is left selected.
            form.clearErrors();
            form.choice_index = null;
            form.choice_label = '';
            form.support_card_name = '';
            form.origin_note = '';
            nextTick(() => document.getElementById('event-submit')?.focus());
        },
    });
};

// The known choices for the event name being recorded. The radio group is the picker: nothing is
// preselected, arrow keys move between options, and choosing one fills the choice fields. The
// manual text field stays available for a choice the run has not seen before.
const knownForName = computed<KnownGroup | null>(() => {
    if (form.source_name === '') {
        return null;
    }
    return (
        props.known.find(
            (group) => group.source_name === form.source_name && group.event_type === form.event_type,
        ) ?? null
    );
});

const selectChoice = (index: number, label: string): void => {
    form.choice_index = index;
    form.choice_label = label;
};

const statValue = (stat: { current: number | null; target: number | null; cap: number }): string => {
    if (stat.current === null) {
        return 'N/A';
    }
    return String(stat.current);
};

const statTitle = (stat: { current: number | null; target: number | null; cap: number }): string => {    if (stat.current === null) {
        return 'No turn has been recorded on this run yet.';
    }
    if (stat.target === null) {
        return `Target not recorded for this trainee. Scenario cap ${stat.cap}.`;
    }
    return `Target ${stat.target}. Scenario cap ${stat.cap}.`;
};

// The reason a meta value is not printed: a band is a recorded reading with no number, and only the
// absence of both is an absence (R3-01).
const metaTitle = (item: { value: number | string | null; band_text?: string | null }): string | null => {
    if (item.value !== null) {
        return null;
    }

    return item.band_text ? 'Recorded as the level the client showed, not as a number.' : 'No turn has been recorded on this run yet.';
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
        current="events"
    >
        <Head :title="'Event decision: ' + props.run.trainee" />
        <template #head>
            <PageHeader
                eyebrow="Event decision"
                title="A training event is asking"
                description="Pick one option. The event resolves immediately and cannot be re-run; an Alarm Clock retries a lost goal race, not an event."
            >
                <template #actions>
                    <a :href="props.run.run_url" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">Back to cockpit</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <form @submit.prevent="submitChoice">
            <div class="grid gap-4 g-side">
                <section class="card" aria-labelledby="event-what-heading">
                    <h2 id="event-what-heading">What the event shows</h2>

                    <div class="mt-2 rounded-md border border-rule-soft bg-raised p-4">
                        <div class="flex flex-wrap items-baseline gap-2.5">
                            <span class="text-[15px] font-extrabold text-ink-strong">
                                {{ form.source_name === '' ? 'Name the event as the client shows it' : form.source_name }}
                            </span>
                            <span class="pill pill-neutral">{{ form.event_type === '' ? 'Source not set' : form.event_type }}</span>
                            <span v-if="form.support_card_name !== ''" class="pill pill-neutral">{{ form.support_card_name }}</span>
                        </div>
                        <p class="mt-2.5 text-[13px] leading-[1.6] text-ink">
                            Event text is the client's own copy. This tool does not transcribe or paraphrase it; the
                            fields below record the words the client shows, so the screen never invents event copy.
                        </p>
                        <Note class="mt-2.5">
                            A real event screen prints what the client prints. What is stored here is the trainer's
                            own transcription of it.
                        </Note>
                    </div>

                    <p class="mt-3 text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Options</p>

                    <div class="mt-1 flex flex-col gap-2.5">
                        <label
                            v-for="(choice, index) in knownForName?.choices ?? []"
                            :key="choice.label"
                            class="flex cursor-pointer items-start gap-2.5 rounded-md px-3 py-[11px]"
                            :class="form.choice_label === choice.label ? 'border-2 border-pick-line bg-green-tint' : 'border border-rule-soft bg-raised'"
                        >
                            <input
                                type="radio"
                                name="known_choice"
                                class="mt-0.5"
                                :checked="form.choice_label === choice.label"
                                @change="selectChoice(index, choice.label)"
                            >
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13.5px] font-bold text-ink-strong">{{ choice.label }}</span>
                                <span class="mt-0.5 block text-[11.5px] text-ink-muted">
                                    Selecting shown; outcome not predicted.
                                    <span v-if="choice.outcome_recorded && choice.outcome !== null"> Recorded outcome: {{ choice.outcome }}.</span>
                                </span>
                            </span>
                        </label>

                        <label class="flex flex-col gap-1 rounded-md border border-rule-soft bg-raised px-3 py-[11px]">
                            <span class="text-[13.5px] font-bold text-ink-strong">The option's own words</span>
                            <BaseInput v-model="form.choice_label" name="choice_label" maxlength="200" placeholder="Type the option the client showed" :error="form.errors.choice_label" />
                            <span class="text-[11.5px] text-ink-muted">Selecting shown; outcome not predicted.</span>
                        </label>
                    </div>

                    <p v-if="knownForName === null" class="mt-2 text-xs text-ink-muted">
                        No recorded choices for this event yet. The first entry becomes the first known outcome.
                    </p>

                    <Note class="mt-2.5">
                        <b>No option predicts its outcome.</b> The app is forbidden to invent a score, a percentage
                        or a "best pick" for an event whose results no source publishes. It records the choice and
                        the effect you report afterwards.
                    </Note>

                    <!-- HFW OVERRIDE (write fields): the wireframe's event row is client copy. The rows the
                         database needs to key the entry (turn, source, event name, support card) stay below the
                         copy block, compact, because the request requires them. -->
                    <details class="mt-3">
                        <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Where this event came from (required to record)</summary>
                        <div class="mt-2 grid max-w-3xl grid-cols-2 gap-3 text-sm @4xl:grid-cols-4">
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Turn *</span>
                                <BaseInput v-model="form.turn" type="number" name="turn" min="1" max="72" required :error="form.errors.turn" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Source *</span>
                                <BaseSelect v-model="form.event_type" name="event_type" :options="[{ value: '', label: 'Choose' }, ...props.write.sources]" :error="form.errors.event_type" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Event name *</span>
                                <BaseInput v-model="form.source_name" name="source_name" maxlength="100" required :error="form.errors.source_name" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">Support card</span>
                                <BaseInput v-model="form.support_card_name" name="support_card_name" maxlength="100" :error="form.errors.support_card_name" />
                            </label>
                        </div>
                    </details>
                </section>

                <div class="flex flex-col gap-4">
                    <section class="card" aria-labelledby="event-outcome-heading">
                        <h2 id="event-outcome-heading">Record the outcome</h2>
                        <p class="sub">After the client resolves the event, record what it did.</p>
                        <div class="grid gap-2.5 g-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Energy</span>
                                <span data-outcome="not-recorded" class="rounded-md border border-dashed border-rule bg-raised px-2 py-2 text-sm text-ink-muted">Not recorded</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Mood</span>
                                <span data-outcome="not-recorded" class="rounded-md border border-dashed border-rule bg-raised px-2 py-2 text-sm text-ink-muted">Not recorded</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Skill points</span>
                                <span data-outcome="not-recorded" class="rounded-md border border-dashed border-rule bg-raised px-2 py-2 text-sm text-ink-muted">Not recorded</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Stat changed</span>
                                <span data-outcome="not-recorded" class="rounded-md border border-dashed border-rule bg-raised px-2 py-2 text-sm text-ink-muted">Not recorded</span>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-ink-muted">
                            No column holds an event's Energy, Mood, Skill-Point or stat delta, so those four state
                            "not recorded" rather than a zero. The note and the bond change are the two outcome
                            fields this tool can store.
                        </p>
                        <div class="mt-3 grid gap-2.5 g-2">
                            <label class="flex flex-col gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Bond change</span>
                                <BaseInput v-model="form.bond_delta" type="number" name="bond_delta" placeholder="Optional" :error="form.errors.bond_delta" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Note</span>
                                <BaseInput v-model="form.origin_note" name="origin_note" maxlength="1000" placeholder="Optional" :error="form.errors.origin_note" />
                            </label>
                        </div>
                        <Note class="mt-2.5">
                            An empty field records "not read off the screen", which is different from a zero.
                        </Note>
                    </section>

                    <section class="card" aria-labelledby="event-provenance-heading">
                        <h2 id="event-provenance-heading">This event's provenance</h2>
                        <table class="t mt-1 w-full border-collapse text-sm">
                            <tbody>
                                <tr class="border-b border-rule-soft"><th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Source</th><td class="py-2">Client screen</td></tr>
                                <tr class="border-b border-rule-soft"><th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Effect table</th><td class="py-2"><span class="pill pill-caution">Not published</span></td></tr>
                                <tr><th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Retryable</th><td class="py-2">No: Alarm Clock covers goal races only</td></tr>
                            </tbody>
                        </table>
                        <Note class="mt-2.5">
                            An Alarm Clock retries a goal race whose placing was missed; it does not re-open an event
                            choice.
                        </Note>
                    </section>

                    <div v-if="form.hasErrors" ref="alert" role="alert" tabindex="-1" class="rounded-md border border-risk bg-raised p-3 text-sm text-risk">
                        <p class="font-semibold">The event was not recorded.</p>
                        <ul class="mt-1 list-disc pl-5">
                            <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                        </ul>
                    </div>

                    <div class="savebar">
                        <span class="dirty" role="status">
                            {{ form.choice_label === '' ? 'No option selected' : form.choice_label + ' selected' }}
                        </span>
                        <button type="button" class="btn btn-quiet min-h-11" title="The build has no undo; this screen proposes one.">Undo turn {{ undoableTurn }}</button>
                        <a :href="props.run.run_url" class="btn min-h-11 inline-flex items-center">Skip</a>
                        <button type="submit" class="btn btn-primary min-h-11" :disabled="form.processing">
                            {{ form.processing ? 'Recording…' : 'Record event' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <details class="card mt-4">
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Recorded events on this run</summary>
            <p v-if="props.events.length === 0" class="mt-1 text-sm text-ink">{{ props.empty }}</p>
            <ul v-else class="mt-2 flex flex-col gap-2 text-sm">
                <li v-for="event in props.events" :key="event.id" class="rounded-md border border-rule-soft bg-raised px-3 py-2">
                    Turn {{ event.turn }} · {{ event.source_label }} · {{ event.source_name }} · {{ event.choice_label ?? 'choice not recorded' }}
                </li>
            </ul>
        </details>
    </CareerLayout>
</template>
