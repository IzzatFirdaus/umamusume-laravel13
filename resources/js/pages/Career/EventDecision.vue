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
import EventCard from '../../components/career/EventCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

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
    meta: { key: string; label: string; value: number | string | null }[];
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
    origin_note: '',
});

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

const statTitle = (stat: { current: number | null; target: number | null; cap: number }): string => {
    if (stat.current === null) {
        return 'No turn has been recorded on this run yet.';
    }
    if (stat.target === null) {
        return `Target not recorded for this trainee. Scenario cap ${stat.cap}.`;
    }
    return `Target ${stat.target}. Scenario cap ${stat.cap}.`;
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Event decision: ${props.run.trainee}`" />
        <template #title>Event decision</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <p class="max-w-3xl text-sm text-ink-muted">
            Record the event that fired, the choice you made, and what it gave. Known outcomes come
            only from this run's own recorded choices; this tool does not look up events it has not
            seen, and it computes no score or probability for a choice.
        </p>

        <!-- The advisor's refusal (design-2.0 §36): the engine holds no event advice, so the rail
             says so and the Trainer chooses. Nothing is recommended and nothing is preselected. -->
        <section
            aria-labelledby="event-advisor-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="event-advisor-heading" class="text-base font-semibold text-ink-strong">Coach recommendation</h2>
            <p class="mt-1 text-sm text-ink-strong">
                <span v-if="props.advisor.recommendation !== null">{{ props.advisor.recommendation }}</span>
                <span v-else title="TrainerAdvisor holds advice for training actions only.">
                    No recommendation available
                </span>
            </p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-sm text-ink-muted">
                <li v-for="(reason, index) in props.advisor.reasons" :key="index">{{ reason }}</li>
            </ul>
        </section>

        <!-- Current career state, the same shape the Cockpit prints: five stats against targets
             and the ScenarioCaps ceiling, plus Energy, Mood, Fans and Skill Points. -->
        <section aria-labelledby="event-state-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="event-state-heading" class="text-base font-semibold text-ink-strong">Current career state</h2>
            <dl class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div
                    v-for="stat in props.state.stats"
                    :key="stat.key"
                    class="rounded-md border border-rule bg-raised p-2"
                >
                    <dt class="text-xs text-ink-muted">{{ stat.label }}</dt>
                    <dd class="mt-0.5 font-mono text-sm tabular-nums text-ink-strong">
                        <span :title="statTitle(stat)">{{ statValue(stat) }}</span>
                        <span class="text-xs font-normal text-ink-muted">/{{ stat.cap }}</span>
                    </dd>
                </div>
            </dl>
            <dl class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div
                    v-for="item in props.state.meta"
                    :key="item.key"
                    class="rounded-md border border-rule bg-raised p-2"
                >
                    <dt class="text-xs text-ink-muted">{{ item.label }}</dt>
                    <dd class="mt-0.5 font-mono text-sm tabular-nums text-ink-strong">
                        <span
                            :title="item.value === null ? 'No turn has been recorded on this run yet.' : null"
                        >{{ item.value === null ? 'N/A' : item.value }}</span>
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Known outcomes: derived from this run's recorded choices, grouped by event name. -->
        <section aria-labelledby="event-known-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="event-known-heading" class="text-base font-semibold text-ink-strong">Known outcomes on this run</h2>
            <p class="mt-1 text-sm text-ink-muted">
                Choices you recorded before, grouped by event. An outcome marked incomplete means
                no result was entered when the choice fired.
            </p>

            <p
                v-if="props.known.length === 0"
                class="mt-3 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
            >
                No recorded choices yet. The first entry below becomes the first known outcome.
            </p>
            <ul v-else class="mt-3 flex flex-col gap-2">
                <li
                    v-for="group in props.known"
                    :key="`${group.event_type}|${group.source_name}`"
                    class="rounded-md border border-rule bg-raised p-3"
                >
                    <p class="flex flex-wrap items-baseline gap-2 text-sm">
                        <span class="rounded-md bg-pick px-1.5 py-0.5 text-xs font-semibold text-on-pick">{{ group.source_label }}</span>
                        <span class="font-semibold text-ink-strong">{{ group.source_name }}</span>
                    </p>
                    <ul class="mt-2 flex flex-col gap-1">
                        <li
                            v-for="choice in group.choices"
                            :key="choice.label"
                            class="flex flex-wrap items-baseline gap-2 text-sm"
                        >
                            <span class="text-ink">{{ choice.label }}</span>
                            <span
                                v-if="choice.outcome_recorded && choice.outcome !== null"
                                class="text-ink-muted"
                            >{{ choice.outcome }}</span>
                            <span
                                v-else
                                class="rounded-full bg-warning px-2 py-0.5 text-xs font-semibold text-on-warning"
                                title="No recorded outcome was entered for this choice. Choose manually."
                            >⚠ Outcome incomplete</span>
                        </li>
                    </ul>
                </li>
            </ul>
        </section>

        <!-- Recorded history, newest first. -->
        <section aria-labelledby="event-history-heading" class="mt-4">
            <h2 id="event-history-heading" class="text-base font-semibold text-ink-strong">Recorded events</h2>

            <p
                v-if="props.empty !== null"
                class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
            >
                {{ props.empty }}
            </p>
            <ul v-else class="mt-3 flex flex-col gap-3">
                <li v-for="event in props.events" :key="event.id">
                    <EventCard v-bind="event" />
                </li>
            </ul>
        </section>

        <!-- The record form. Disabled only when the run has no turns to record against, which is
             a named absence, not a default. -->
        <form
            v-if="props.write.turns.length > 0"
            aria-labelledby="event-form-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
            @submit.prevent="submitChoice"
        >
            <h2 id="event-form-heading" class="text-base font-semibold text-ink-strong">Record an event choice</h2>
            <p class="mt-1 text-sm text-ink-muted">
                Store what fired and what you chose. This creates a TurnEvent on the selected turn.
            </p>

            <div
                v-if="form.hasErrors"
                ref="alert"
                role="alert"
                tabindex="-1"
                class="mt-3 rounded-md border border-risk bg-raised p-2 text-sm text-risk"
            >
                <p class="font-semibold">The choice was not recorded.</p>
                <ul class="mt-1 list-disc pl-5">
                    <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <label class="flex flex-col gap-1">
                    <span class="text-xs text-ink-muted">Turn *</span>
                    <select
                        v-model="form.turn"
                        name="turn"
                        required
                        class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                    >
                        <option value="">Select turn</option>
                        <option
                            v-for="turn in props.write.turns"
                            :key="turn.id"
                            :value="turn.turn"
                        >
                            Turn {{ turn.turn }}
                        </option>
                    </select>
                    <p v-if="form.errors.turn" class="text-xs text-risk">{{ form.errors.turn }}</p>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs text-ink-muted">Source *</span>
                    <select
                        v-model="form.event_type"
                        name="event_type"
                        required
                        class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                    >
                        <option value="">Select source</option>
                        <option
                            v-for="source in props.write.sources"
                            :key="source.value"
                            :value="source.value"
                        >
                            {{ source.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.event_type" class="text-xs text-risk">{{ form.errors.event_type }}</p>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs text-ink-muted">Event name *</span>
                    <input
                        v-model="form.source_name"
                        type="text"
                        name="source_name"
                        required
                        placeholder="e.g., Go Beyond"
                        class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                    />
                    <p v-if="form.errors.source_name" class="text-xs text-risk">{{ form.errors.source_name }}</p>
                </label>
            </div>

            <!-- Known choices for this event, as a keyboard radio group. Nothing is preselected;
                 the manual field below stays the fallback for an unseen choice. -->
            <fieldset
                v-if="knownForName !== null"
                class="mt-3 rounded-md border border-rule bg-raised p-3"
            >
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                    Choices seen on this run
                </legend>
                <label
                    v-for="(choice, index) in knownForName.choices"
                    :key="choice.label"
                    class="flex min-h-11 cursor-pointer items-center gap-2 px-1 text-sm"
                >
                    <input
                        type="radio"
                        name="known_choice"
                        class="size-4 accent-chrome"
                        :value="choice.label"
                        :checked="form.choice_label === choice.label"
                        @change="selectChoice(index, choice.label)"
                    />
                    <span class="text-ink">{{ choice.label }}</span>
                    <span v-if="choice.outcome_recorded && choice.outcome !== null" class="text-ink-muted">
                        {{ choice.outcome }}
                    </span>
                    <span
                        v-else
                        class="rounded-full bg-warning px-2 py-0.5 text-xs font-semibold text-on-warning"
                        title="No recorded outcome was entered for this choice. Choose manually."
                    >⚠ Outcome incomplete</span>
                </label>
            </fieldset>

            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-1">
                    <span class="text-xs text-ink-muted">Choice you made *</span>
                    <input
                        v-model="form.choice_label"
                        type="text"
                        name="choice_label"
                        required
                        placeholder="Option A, Encourage, …"
                        class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                    />
                    <p v-if="form.errors.choice_label" class="text-xs text-risk">{{ form.errors.choice_label }}</p>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs text-ink-muted">Support card (if the event came from one)</span>
                    <input
                        v-model="form.support_card_name"
                        type="text"
                        name="support_card_name"
                        placeholder="e.g., Kitasan Black"
                        class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                    />
                </label>
            </div>

            <label class="mt-3 flex flex-col gap-1">
                <span class="text-xs text-ink-muted">What the choice gave</span>
                <textarea
                    v-model="form.origin_note"
                    name="origin_note"
                    rows="2"
                    placeholder="e.g., +30 Energy, +10 Mood"
                    class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink resize-none"
                />
            </label>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button
                    id="event-submit"
                    type="submit"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:opacity-60 disabled:cursor-wait"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Recording…' : 'Record choice' }}
                </button>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">
                    Recording the choice…
                </p>
            </div>
        </form>
        <p v-else class="mt-4 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink">
            No turns logged yet. Record at least one turn on the run screen before recording an
            event choice.
        </p>

        <div class="mt-4 max-w-sm">
            <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">
                Back to Cockpit
            </a>
        </div>
    </CareerLayout>
</template>
