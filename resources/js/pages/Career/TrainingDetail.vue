<script setup lang="ts">
/*
 * SCREEN-010, the Training Decision detail (plan §8 D9). Five cards to compare, then the turn record.
 *
 * **The page pattern is `Catalog/Index.vue`'s and the shell is D8's `CareerLayout`**, with the
 * loading/error handling copied from `Career/Cockpit.vue`: one `role="status"` line while a visit is in
 * flight and one `role="alert"` line for a failed one, both cancelable so this page's own message is
 * the only surface (`ADR-0007`).
 *
 * **Nothing here projects a number.** The five cards show entered values, declared constants with their
 * source and date, the advisor's own arithmetic, the deck's stated anchors and the scenario matrix
 * (`ADR-0001` §2 and §7, `ADR-0020` §2). A yield or a failure rate renders `N/A` with the exclusion in
 * its `title`; `TrainingCard.vue` and `CareerTrainingDetailTest` hold that line together.
 *
 * **The write is the guided turn's.** `Train` on a card holds that card's choice for the form below —
 * carried, never re-asked (WCAG 3.3.7) — and the form posts `stage=preview` to `runs.turns.store` with
 * the `StoreTurnEntryRequest` that already validates it. No field the request refuses is offered and no
 * number is typed on the Trainer's behalf: the five stat totals are what the client shows after the
 * turn resolves, so the inputs start empty and the previous turn's readings arrive as placeholders
 * only (D-220). The preview is a screen, and the run record screen is the one that renders it and
 * gates the confirm.
 *
 * The props contract is declared locally: `defineProps<Imported>()` cannot resolve an imported type,
 * because TypeScript 7 ships no `lib/typescript.js` for `@vue/compiler-sfc` to load (plan §11).
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import TrainingCard from '../../components/career/TrainingCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

interface Cost {
    min: number;
    max: number;
    source: string;
    verified_at: string;
    confidence: string;
}

interface Option {
    key: string;
    choice: string;
    recommended: boolean;
    current: number | null;
    target: number | null;
    cap: number;
    cap_bonus: number | null;
    deficit: number | null;
    band: string | null;
    reason: string | null;
    energy_after: { min: number; max: number } | null;
    cost: Cost;
    supports: { name: string; effects: { effect_id: number; name: string | null; display: string }[] }[];
    scenario_effects: { label: string; value: string }[] | null;
}

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario_label: string;
        run_url: string;
        cockpit_url: string;
    };
    options: Option[];
    deck: { recorded: boolean; slots: number };
    advisor: { action: string | null; band: string | null; absence: string | null };
    write: {
        action: string;
        turn: number;
        moods: string[];
        previous: Record<string, number | null> | null;
    };
}>();

const visiting = ref(false);
const visitFailed = ref(false);

const stopStart = router.on('start', () => {
    visiting.value = true;
    visitFailed.value = false;
});
const stopFinish = router.on('finish', () => {
    visiting.value = false;
});
const stopException = router.on('exception', () => {
    visiting.value = false;
    visitFailed.value = true;

    return false;
});
const stopInvalid = router.on('invalid', () => {
    visiting.value = false;
    visitFailed.value = true;

    return false;
});

onUnmounted(() => {
    stopStart();
    stopFinish();
    stopException();
    stopInvalid();
});

// The choice the Trainer holds for the turn, set by a card's Train button. Null until one is pressed,
// which is the honest state: nothing here presumes which training this turn was.
const choice = ref<string | null>(null);

const chosen = computed(() => props.options.find((option) => option.choice === choice.value) ?? null);

// The advisor's answer, read back out of the five cards. `Rest` is one of the advisor's six options
// and not one of these five, so it resolves to null and the page names the absence.
const recommended = computed(() => props.options.find((option) => option.key === props.advisor.action) ?? null);

const statFields = [
    { name: 'speed', label: 'Speed' },
    { name: 'stamina', label: 'Stamina' },
    { name: 'power', label: 'Power' },
    { name: 'guts', label: 'Guts' },
    { name: 'wit', label: 'Wit' },
] as const;

const optionalFields = [
    { name: 'sp', label: 'Skill Points', max: null },
    { name: 'energy', label: 'Energy', max: 100 },
    { name: 'fans', label: 'Fans', max: null },
] as const;

const form = useForm({
    turn: String(props.write.turn),
    speed: '',
    stamina: '',
    power: '',
    guts: '',
    wit: '',
    sp: '',
    energy: '',
    fans: '',
    mood: '',
    outcome: '',
    penalty_kind: '',
    choice: '',
    stage: 'preview',
});

const placeholder = (name: string): string | undefined =>
    props.write.previous?.[name] === null || props.write.previous?.[name] === undefined
        ? undefined
        : String(props.write.previous[name]);

const alert = ref<HTMLElement | null>(null);

// A refused write comes back with the field's message, and the refusal is the whole answer to the
// press, so focus moves to it rather than being left for the Trainer to find (WCAG 2.2 SC 3.3.1).
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

// The record panel's heading. A press that cannot complete puts focus on the panel that holds what is
// missing rather than firing a write the request is certain to refuse (WCAG 2.4.3).
const recordHeading = ref<HTMLElement | null>(null);

const train = (option: Option): void => {
    choice.value = option.choice;
    form.choice = option.choice;

    recordHeading.value?.focus();
};

// The five stat totals are what the client shows after the turn resolves, so the form starts empty and
// a press before they are entered is answered by the field messages the existing request returns.
const previewTurn = (): void => {
    form.post(props.write.action, { preserveScroll: true, preserveState: true });
};

const errorOf = (field: string): string | undefined =>
    (form.errors as Record<string, string | undefined>)[field];

const describedBy = (field: string): string | undefined =>
    errorOf(field) === undefined ? undefined : `turn-${field}-error`;

const capFor = (label: string): number =>
    props.options.find((option) => option.key === label)?.cap ?? 0;
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Training decision: ${props.run.trainee}`" />
        <template #title>Training decision</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That turn did not go through. Nothing was saved; try again.
        </p>

        <p class="mb-4 flex flex-wrap items-baseline gap-x-2 text-sm text-ink-muted">
            <span>The five training actions, as this run can state them.</span>
            <span v-if="props.run.trainee_ja !== null" lang="ja">{{ props.run.trainee_ja }}</span>
            <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                Career Cockpit
            </a>
        </p>

        <section
            v-if="props.write.previous === null"
            aria-labelledby="training-empty-heading"
            class="mb-4 rounded-md border border-dashed border-rule bg-raised p-4"
        >
            <h2 id="training-empty-heading" class="text-base font-semibold text-ink-strong">No turn recorded yet</h2>
            <p class="mt-1 text-sm text-ink">
                This run has logged no turn, so the cards have no entered stat to read a deficit from and
                no Energy to subtract a cost from. The advisor ranks nothing and says why, rather than
                assuming a starting state. Record the first turn and this screen fills in beside it.
            </p>
            <a :href="props.run.run_url" class="mt-2 inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                Record the first turn on the run screen
            </a>
        </section>

        <section aria-labelledby="training-advisor-heading" class="mb-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="training-advisor-heading" class="text-base font-semibold text-ink-strong">Advisor</h2>

            <p v-if="props.advisor.absence !== null" class="mt-1 text-sm text-ink">
                {{ props.advisor.absence }}
            </p>

            <p v-else-if="props.advisor.action === null" class="mt-1 text-sm text-ink">
                The advisor names no recommendation for this turn.
            </p>

            <p v-else-if="recommended === null" class="mt-1 text-sm text-ink">
                The advisor recommends
                <span class="font-semibold text-ink-strong">{{ props.advisor.action }}</span>
                for this turn. It is not one of the five training actions, so no card below carries the
                marker; record it on the run screen.
                <a :href="props.run.run_url" class="font-medium text-ink-strong underline">Run record</a>
            </p>

            <p v-else class="mt-1 text-sm text-ink">
                The advisor recommends
                <span class="font-semibold text-ink-strong">{{ recommended.key }}</span>:
                <span v-if="recommended.reason !== null">{{ recommended.reason }}</span>
                The marker sits on that card alone.
            </p>
        </section>

        <section aria-labelledby="training-options-heading">
            <h2 id="training-options-heading" class="text-base font-semibold text-ink-strong">Training options</h2>

            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <TrainingCard
                    v-for="option in props.options"
                    :key="option.choice"
                    :option="option"
                    :scenario-label="props.run.scenario_label"
                    :deck-recorded="props.deck.recorded"
                    :chosen="choice === option.choice"
                    :turn="props.write.turn"
                    @train="train(option)"
                />
            </div>
        </section>

        <section aria-labelledby="training-record-heading" class="mt-6 rounded-md border border-rule bg-panel p-4">
            <h2 id="training-record-heading" ref="recordHeading" tabindex="-1" class="text-base font-semibold text-ink-strong">Record the turn</h2>

            <p class="mt-1 text-sm text-ink">
                <template v-if="chosen !== null">
                    Chosen: <span class="font-semibold text-ink-strong">{{ chosen.key }}</span>.
                </template>
                <template v-else>
                    Press <span class="font-semibold text-ink-strong">Train</span> on one card to say which
                    training this turn was. Nothing is chosen yet.
                </template>
                Every number below is what the client shows after the turn resolves. This tool does not
                project them, so an empty field is the honest state and the write refuses a turn that has
                not been read.
            </p>

            <div
                v-if="form.hasErrors"
                ref="alert"
                role="alert"
                tabindex="-1"
                class="mt-3 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
            >
                <p class="font-semibold">The turn was not previewed.</p>
                <ul class="mt-1 list-disc pl-5">
                    <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <form class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-3" @submit.prevent="previewTurn">
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Turn *</span>
                    <input
                        id="turn-turn"
                        v-model="form.turn"
                        type="number"
                        name="turn"
                        min="1"
                        required
                        :aria-describedby="describedBy('turn')"
                        class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                    <span v-if="errorOf('turn')" :id="`turn-turn-error`" class="text-xs text-risk">{{ errorOf('turn') }}</span>
                </label>

                <label v-for="field in statFields" :key="field.name" class="flex flex-col gap-1">
                    <span class="text-ink-muted">{{ field.label }} total *</span>
                    <input
                        :id="`turn-${field.name}`"
                        v-model="form[field.name]"
                        type="number"
                        :name="field.name"
                        min="0"
                        :max="capFor(field.label)"
                        :placeholder="placeholder(field.name)"
                        required
                        :aria-describedby="describedBy(field.name)"
                        class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                    <span v-if="errorOf(field.name)" :id="`turn-${field.name}-error`" class="text-xs text-risk">{{ errorOf(field.name) }}</span>
                </label>

                <label v-for="field in optionalFields" :key="field.name" class="flex flex-col gap-1">
                    <span class="text-ink-muted">{{ field.label }}</span>
                    <input
                        :id="`turn-${field.name}`"
                        v-model="form[field.name]"
                        type="number"
                        :name="field.name"
                        min="0"
                        :max="field.max"
                        :placeholder="placeholder(field.name)"
                        :aria-describedby="describedBy(field.name)"
                        class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                    <span v-if="errorOf(field.name)" :id="`turn-${field.name}-error`" class="text-xs text-risk">{{ errorOf(field.name) }}</span>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Mood</span>
                    <select
                        id="turn-mood"
                        v-model="form.mood"
                        name="mood"
                        :aria-describedby="describedBy('mood')"
                        class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <option value="">not recorded</option>
                        <option v-for="tier in props.write.moods" :key="tier" :value="tier">{{ tier }}</option>
                    </select>
                    <span v-if="errorOf('mood')" id="turn-mood-error" class="text-xs text-risk">{{ errorOf('mood') }}</span>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Outcome *</span>
                    <select
                        id="turn-outcome"
                        v-model="form.outcome"
                        name="outcome"
                        required
                        :aria-describedby="describedBy('outcome')"
                        class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <option value="">Choose</option>
                        <option value="Success">Success</option>
                        <option value="Failure">Failure</option>
                    </select>
                    <span v-if="errorOf('outcome')" id="turn-outcome-error" class="text-xs text-risk">{{ errorOf('outcome') }}</span>
                </label>

                <label v-if="form.outcome === 'Failure'" class="flex flex-col gap-1">
                    <span class="text-ink-muted">Penalty kind *</span>
                    <select
                        id="turn-penalty_kind"
                        v-model="form.penalty_kind"
                        name="penalty_kind"
                        required
                        :aria-describedby="describedBy('penalty_kind')"
                        class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <option value="">Choose</option>
                        <option value="energy">Energy</option>
                        <option value="mood">Mood</option>
                        <option value="stat">A stat</option>
                    </select>
                    <span v-if="errorOf('penalty_kind')" id="turn-penalty_kind-error" class="text-xs text-risk">{{ errorOf('penalty_kind') }}</span>
                </label>

                <div class="flex flex-wrap items-end gap-3 md:col-span-3">
                    <button
                        type="submit"
                        class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Sending…' : 'Preview this turn' }}
                    </button>
                    <p class="max-w-prose text-xs text-ink-muted">
                        The preview renders on the run record screen, which is where the turn is confirmed
                        ({{ props.run.trainee }}, turn {{ props.write.turn }}). Nothing is written until you
                        confirm it there.
                    </p>
                </div>
            </form>
        </section>

        <p class="mt-4 text-sm text-ink">
            <template v-if="props.deck.recorded">
                {{ props.deck.slots }} of six Support Card slots are recorded for this run, and each card
                above counts only the ones whose type reads its training.
            </template>
            <template v-else>
                No Support Cards are recorded for this run, so no card above can count a support.
                <a href="/career/setup/deck" class="inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                    Deck step
                </a>
            </template>
        </p>
    </CareerLayout>
</template>
