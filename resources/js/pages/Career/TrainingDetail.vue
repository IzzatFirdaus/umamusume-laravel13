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
 * (`ADR-0001` §2 and §7, `ADR-0020` §2). A yield or a failure rate renders `N/A` through
 * `AbsenceValue.vue`, whose disclosure carries the exclusion; `TrainingCard.vue` and
 * `CareerTrainingDetailTest` hold that line together.
 *
 * **The write is a direct record.** `Train` on a card holds that card's choice for the form below —
 * carried, never re-asked (WCAG 3.3.7) — and the form posts to `runs.turns.store` with the
 * `StoreTurnEntryRequest` that already validates it, no preview step in between. The previous flow
 * carried a `stage=preview` intermediate that redirected to the run record screen, whose rail then
 * gated the confirm (F2, plan §9.6; owner ruling on group R-2). That record screen is retired and the
 * two-stage rail has no 2.0 reproduction, so this screen now writes the row itself and redirects to
 * `runs.cockpit`, where the recorded turn appears in the correction selector.
 *
 * The five stat totals are what the client shows after the turn resolves, so the inputs start empty
 * and the previous turn's readings arrive as placeholders only (D-220). A press before they are
 * entered is answered by the field messages the existing request returns. The same holds for the two
 * turn choices that are not trainings: Rest and Recreation are offered beside the five cards, on the
 * one record form, and never as cards of their own (`SCREEN_SPEC.md` SCR-CAR-012).
 *
 * The props contract is declared locally: `defineProps<Imported>()` cannot resolve an imported type,
 * because TypeScript 7 ships no `lib/typescript.js` for `@vue/compiler-sfc` to load (plan §11).
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import TrainingCard from '../../components/career/TrainingCard.vue';
import { BaseInput, BaseSelect, BaseRadio } from '../../components/Form';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';
import { TURN_ENTRY_ENERGY_BANDS, TURN_ENTRY_ENERGY_STATES, TURN_ENTRY_FACILITY_FIELDS, TURN_ENTRY_PREVIEW_FIELDS, TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';

interface Cost {
    min: number;
    max: number;
    source: string;
    verified_at: string;
    confidence: string;
}

interface PublishedRow {
    level: number;
    text: string;
    server: string;
    source: string;
    notes: string[];
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
    published: {
        gains: PublishedRow | null;
        gains_absence: string | null;
        cost: PublishedRow | null;
        risk_reason: string | null;
    };
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
        deck_url: string;
    };
    options: Option[];
    deck: { recorded: boolean; slots: number };
    advisor: { action: string | null; band: string | null; absence: string | null };
    careerPosition: { year: number; month: number; phase: string; turn_index: number; scenario_countdown: number | null } | null;
    currentStats: Record<string, number | null>;
    energy: { state: string | null; band: string | null; value: number | null; label: string };
    write: {
        action: string;
        turn: number;
        moods: string[];
        extra_choices: { value: string; label: string; note: string; recommended: boolean }[];
        outcomes: Record<string, Record<string, boolean>>;
        previous: Record<string, number | null> | null;
        performance: { enabled: boolean; types: string[] };
    };
}>();

const { visiting, visitFailed } = useVisitState();

/*
 * The run's own position, in the client's words, when it has one. The next turn the record form
 * writes is `write.turn` (the run's `nextTurnNumber()`, which already reads the imported position),
 * and this line says where the run stands so that number has a calendar behind it.
 */
const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const YEAR_LABELS: Record<number, string> = { 1: 'Junior Year', 2: 'Classic Year', 3: 'Senior Year' };

const positionLabel = computed<string | null>(() => {
    const position = props.careerPosition;

    if (position === null) {
        return null;
    }

    const year = YEAR_LABELS[position.year] ?? `Year ${position.year}`;

    return `${year} · ${position.phase} ${MONTHS[position.month - 1]} · Turn ${position.turn_index}`;
});

// The choice the Trainer holds for the turn, set by a card's Train button. Null until one is pressed,
// which is the honest state: nothing here presumes which training this turn was.
const choice = ref<string | null>(null);

const chosen = computed(() => props.options.find((option) => option.choice === choice.value) ?? null);

/*
 * Rest and Recreation are turn choices, not training options (SCR-CAR-012), so they get no card and
 * no figures of a card: no deficit, no cost, no supports. They are held here, beside the five, so the
 * one record form writes every turn the career takes.
 */
const chosenExtra = computed(
    () => props.write.extra_choices.find((extra) => extra.value === choice.value) ?? null,
);

const chosenLabel = computed(() => chosen.value?.key ?? chosenExtra.value?.label ?? null);

/*
 * The one field the chosen action actually moves, marked the way a chosen card is (`border-pick-line`):
 * Energy for a rest, Mood for an outing, and neither for a training, whose movement is the five totals
 * above. It is a second signal beside the field's own label, never the only one, and it claims nothing
 * about magnitude — no source publishes a recovery or an outing figure (`ADR-0001` §3).
 */
const emphasizedField = computed<'energy' | 'mood' | null>(() => {
    if (choice.value === 'rest') {
        return 'energy';
    }

    return choice.value === 'mood' ? 'mood' : null;
});

/*
 * The outcome words on offer, read off the choice the Trainer is holding. `write.outcomes` is composed
 * from `TurnChoice::outcomes()`, the one owner, so the select cannot offer a word the request refuses;
 * the fallback is the pair every other choice offers. Each word carries its own failure flag because the
 * penalty field belongs to the *failure branch*, not to one magic word: D-205 rules that Rest carries the
 * same branch treatment as any other choice, so `Poor Rest` opens it exactly as `Failure` does.
 */
const outcomeWords = computed<Record<string, boolean>>(
    () => props.write.outcomes[choice.value ?? ''] ?? { Success: false, Failure: true },
);

const outcomeOptions = computed(() => [
    { value: '', label: 'Choose' },
    ...Object.keys(outcomeWords.value).map((word) => ({ value: word, label: word })),
]);

const outcomeIsFailure = computed(() => outcomeWords.value[form.outcome] === true);

// A word the new choice does not offer cannot stay selected. A select left holding a value outside its own
// list shows its placeholder while the form still posts the stale word, so the choice moving clears it.
watch(choice, () => {
    if (!(form.outcome in outcomeWords.value)) {
        form.outcome = '';
    }
});

// The chosen card's key figures restated at the form (corpus §35's collapsed view): a Trainer working
// down here should not need the card above still in view. Only figures the tool holds: the support
// count is the deck's, and the energy aftermath is the card's own "calculated, not a reading" figure.
// The client's previewed gain and failure rate are deliberately absent - no source publishes either
// (TrainingCard's gainsTitle and failureTitle), so the sentence prints no number for them.
const chosenSummary = computed(() => {
    if (chosen.value === null) {
        return null;
    }

    const supports = `${chosen.value.supports.length} support${chosen.value.supports.length === 1 ? '' : 's'} entered for this training`;
    const after = chosen.value.energy_after;

    if (after === null) {
        return `${supports}.`;
    }

    const energy = after.min === after.max ? `${after.min}` : `${after.min} to ${after.max}`;

    return `${supports}; energy after ${energy} (calculated, not a reading).`;
});

// The advisor's answer, read back out of the five cards. `Rest` is one of the advisor's six options
// and not one of these five, so it resolves to null and the page names the absence.
const recommended = computed(() => props.options.find((option) => option.key === props.advisor.action) ?? null);

const optionalFields = [
    { name: 'sp', label: 'Skill Points', max: null },
    { name: 'fans', label: 'Fans', max: null },
] as const;

const energyStates = TURN_ENTRY_ENERGY_STATES;
const energyBands = TURN_ENTRY_ENERGY_BANDS;

// The five facility inputs, from the shared source. The names are listed on the form below rather than
// spread in, so TypeScript checks each `form[field.name]` binding against a key the form actually has.
const facilityFields = TURN_ENTRY_FACILITY_FIELDS;

// The client's preview row, from the shared source. Read as one object on the form and posted as one,
// because `preview_gains` is stored as a single payload rather than six columns.
const previewFields = TURN_ENTRY_PREVIEW_FIELDS;

const form = useForm({
    turn: String(props.write.turn),
    speed: '',
    stamina: '',
    power: '',
    guts: '',
    wit: '',
    sp: '',
    energy: '',
    energy_state: 'exact',
    energy_band: 'mid',
    fans: '',
    mood: '',
    outcome: '',
    penalty_kind: '',
    choice: '',
    // The ladder opens on the level this run last read, so the Trainer changes only what moved; a run
    // that never recorded one opens blank, and a blank stays "not recorded" rather than Level 1.
    facility_speed: String(props.write.previous?.facility_speed ?? ''),
    facility_stamina: String(props.write.previous?.facility_stamina ?? ''),
    facility_power: String(props.write.previous?.facility_power ?? ''),
    facility_guts: String(props.write.previous?.facility_guts ?? ''),
    facility_wit: String(props.write.previous?.facility_wit ?? ''),
    failure_rate: '',
    preview_gains: {
        speed: '',
        stamina: '',
        power: '',
        guts: '',
        wit: '',
        sp: '',
    },
});

// A state that is not `exact` carries no figure, so the number is cleared rather than left behind a
// hidden control and posted as though it described the band.
watch(() => form.energy_state, (state) => {
    if (state !== 'exact') {
        form.energy = '';
    }
});

const placeholder = (name: string): string | undefined =>
    props.write.previous?.[name] === null || props.write.previous?.[name] === undefined
        ? 'no logged turn'
        : String(props.write.previous[name]);

// The press that says what this turn was. The choice is held for the record form rather than re-asked
// of it (WCAG 3.3.7), and focus moves to the form that now cannot complete without the client's numbers.
const choose = (value: string): void => {
    choice.value = value;
    form.choice = value;

    recordHeading.value?.focus();
};

/*
 * The Cockpit's Rest and Recreation entries arrive holding the choice they stand for, so the Trainer
 * does not pick it again. Rest's public value is the one the row is written under; Recreation's public
 * word is `recreation` while the row is written under `mood` (`TurnChoice`'s docblock records why), so
 * the one mapping the address needs lives here rather than on the enum, which carries no URL concern.
 * Only a value this screen offers is taken from the address; anything else is ignored rather than
 * posted, because the request would refuse it and the page would read as broken.
 */
const choiceFromQuery = (query: string | null): string | null =>
    query === 'recreation' ? 'mood' : query;

const arrivingChoice = props.write.extra_choices.find(
    (extra) => extra.value === choiceFromQuery(new URLSearchParams(window.location.search).get('choice')),
);

if (arrivingChoice !== undefined) {
    choice.value = arrivingChoice.value;
    form.choice = arrivingChoice.value;
}

/*
 * The turn's Energy band, in this page's own words. Corrected 2026-10-11 (G7): this comment used to say
 * the pair matched "the Cockpit's recommendation card" and that `components/career/RecommendationCard.vue`
 * held a second copy of it. Both claims were stale. That component was imported by nothing and is deleted,
 * and the Cockpit's advisor rail deliberately prints no band at all (`NextDecision.vue`: "Nothing here is
 * a score, a band or a percentage"), so there is no second vocabulary for SC 3.2.4 to keep in step and
 * nothing to extract on a third reader.
 */
const BANDS: Record<'AtOrAboveAdvisory' | 'BelowAdvisory', string> = {
    AtOrAboveAdvisory: 'At or above the advisory line',
    BelowAdvisory: 'Below the advisory line',
};

const bandWord = computed(() =>
    props.advisor.band === null ? null : BANDS[props.advisor.band],
);

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

// The Performance observation pair, held outside the form so the posted payload carries the key
// only when a reading was entered: a turn that observed nothing must not post an empty pair, whose
// halves the request refuses to split.
const performanceType = ref('');
const performanceDelta = ref('');

// The five stat totals are what the client shows after the turn resolves, so the form starts empty and
// a press before they are entered is answered by the field messages the existing request returns. The
// post writes the row directly and redirects to the Cockpit: the previous preview-and-confirm rail has
// no 2.0 reproduction (F2, plan §9.6; owner ruling on group R-2), so there is no stage to advance.
const recordTurn = (): void => {
    form
        .transform((data) => {
            const pair = performanceType.value === '' && performanceDelta.value === ''
                ? {}
                : { performance: { type: performanceType.value, delta: performanceDelta.value } };

            return { ...data, ...pair };
        })
        .post(props.write.action, { preserveScroll: true, preserveState: true });
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
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="props.positionLabel"
        :run-url="props.run.run_url"
        current="training"
    >
        <Head :title="`Training decision: ${props.run.trainee}`" />
        <template #title>Training decision</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That turn did not go through. Nothing was saved; try again.
        </p>

        <p class="mb-4 flex flex-wrap items-baseline gap-x-2 text-sm text-ink-muted">
            <span>The five training actions, as this run can state them.</span>
            <span v-if="props.run.trainee_ja !== null" lang="ja">{{ props.run.trainee_ja }}</span>
        </p>

        <p v-if="positionLabel !== null" class="mb-4 text-sm text-ink-muted">
            This run stands at {{ positionLabel }}. The deficits below are against the stats recorded
            there, and the next turn the form writes is {{ props.write.turn }}.
        </p>

        <p class="mb-4 text-sm text-ink">
            {{ props.energy.label }}
        </p>

        <section
            v-if="props.write.previous === null"
            aria-labelledby="training-empty-heading"
            class="mb-4 rounded-md border border-dashed border-rule bg-raised p-4"
        >
            <h2 id="training-empty-heading" class="text-lg font-semibold text-ink-strong">No turn recorded yet</h2>
            <p class="mt-1 text-sm text-ink">
                This run has logged no turn, so the cards have no entered stat to read a deficit from and
                no Energy to subtract a cost from. The advisor ranks nothing and says why, rather than
                assuming a starting state. Record the first turn and this screen fills in beside it.
            </p>
            <a :href="props.run.run_url" class="mt-2 inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                Record the first turn on the Cockpit
            </a>
        </section>

        <section aria-labelledby="training-advisor-heading" class="mb-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="training-advisor-heading" class="text-lg font-semibold text-ink-strong">Advisor</h2>

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
                marker; the record form holds it as its own choice.
            </p>

            <p v-else class="mt-1 text-sm text-ink">
                The advisor recommends
                <span class="font-semibold text-ink-strong">{{ recommended.key }}</span>:
                <span v-if="recommended.reason !== null">{{ recommended.reason }}</span>
                The marker sits on that card alone.
            </p>

            <!-- The band is a word and a figure, never a colour, and 50 is the only Energy line any
                 source states (`ADR-0001` §3). -->
            <p v-if="bandWord !== null" class="mt-2 text-sm text-ink-muted">
                This turn is {{ bandWord }}; the line it is measured against is the sourced 50.
            </p>
        </section>

        <section aria-labelledby="training-options-heading">
            <h2 id="training-options-heading" class="text-lg font-semibold text-ink-strong">Training options</h2>

            <div class="mt-3 grid grid-cols-1 gap-4 @4xl:grid-cols-2 @7xl:grid-cols-3">
                <TrainingCard
                    v-for="option in props.options"
                    :key="option.choice"
                    :option="option"
                    :scenario-label="props.run.scenario_label"
                    :deck-recorded="props.deck.recorded"
                    :chosen="choice === option.choice"
                    :turn="props.write.turn"
                    :has-previous-turn="props.write.previous !== null"
                    @train="choose(option.choice)"
                />
            </div>
        </section>

        <section aria-labelledby="training-record-heading" class="mt-6 rounded-md border border-rule bg-panel p-4">
            <h2 id="training-record-heading" ref="recordHeading" tabindex="-1" class="text-lg font-semibold text-ink-strong">Record the turn</h2>

            <p class="mt-1 text-sm text-ink">
                <template v-if="chosenLabel !== null">
                    Chosen: <span class="font-semibold text-ink-strong">{{ chosenLabel }}</span>.
                    <template v-if="chosen !== null">{{ chosenSummary }}</template>
                    <template v-else-if="chosenExtra !== null">{{ chosenExtra.note }}</template>
                </template>
                <template v-else>
                    Press <span class="font-semibold text-ink-strong">Train</span> on one card, or pick
                    one of the two turn choices below, to say what this turn was. Nothing is chosen yet.
                </template>
                Every number below is what the client shows after the turn resolves. This tool does not
                project them, so an empty field is the honest state and the write refuses a turn that has
                not been read.
            </p>

            <!-- Rest and Recreation, the two turn choices that are not trainings. They are not cards:
                 SCR-CAR-012 rules them turn choices, and the five cards are the five. Both keep Energy
                 and Mood editable, because no source in the corpus publishes a recovery or an outing
                 figure for the client to be checked against (`ADR-0001` §3). -->
            <div class="mt-3 flex flex-wrap gap-2">
                <button
                    v-for="extra in props.write.extra_choices"
                    :key="extra.value"
                    type="button"
                    :aria-pressed="choice === extra.value"
                    :class="[
                        'inline-flex min-h-11 items-center gap-x-2 rounded-md border-2 px-4 text-sm font-bold transition-colors duration-150 ease-out',
                        choice === extra.value ? 'border-pick-line bg-raised text-ink-strong' : 'border-rule text-ink hover:border-green-line',
                    ]"
                    @click="choose(extra.value)"
                >
                    {{ extra.label }}
                    <span v-if="extra.recommended" class="font-bold">
                        <span aria-hidden="true">★</span> RECOMMENDED
                    </span>
                </button>
            </div>

            <div
                v-if="form.hasErrors"
                ref="alert"
                role="alert"
                tabindex="-1"
                class="mt-3 max-w-3xl rounded-md border border-risk bg-raised p-3 text-sm text-risk"
            >
                <p class="font-semibold">The turn was not recorded.</p>
                <ul class="mt-1 list-disc pl-5">
                    <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <form class="mt-3 grid max-w-3xl grid-cols-2 gap-3 text-sm @4xl:grid-cols-3" @submit.prevent="recordTurn">
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Turn *</span>
                    <BaseInput
                        id="turn-turn"
                        v-model="form.turn"
                        type="number"
                        name="turn"
                        min="1"
                        required
                        :aria-describedby="describedBy('turn')"
                        :error="errorOf('turn')"
                    />
                </label>

                <label v-for="field in statFields" :key="field.name" class="flex flex-col gap-1">
                    <span class="text-ink-muted">{{ field.label }} *</span>
                    <BaseInput
                        :id="`turn-${field.name}`"
                        v-model="form[field.name]"
                        type="number"
                        :name="field.name"
                        min="0"
                        :max="capFor(field.label)"
                        :placeholder="placeholder(field.name)"
                        required
                        :aria-describedby="describedBy(field.name)"
                        :error="errorOf(field.name)"
                    />
                </label>

                <label v-for="field in optionalFields" :key="field.name" class="flex flex-col gap-1">
                    <span class="text-ink-muted">{{ field.label }}</span>
                    <BaseInput
                        :id="`turn-${field.name}`"
                        v-model="form[field.name]"
                        type="number"
                        :name="field.name"
                        min="0"
                        :max="field.max"
                        :placeholder="placeholder(field.name)"
                        :aria-describedby="describedBy(field.name)"
                        :error="errorOf(field.name)"
                    />
                </label>

                <!-- Energy as a state, not only a number: a figure when the Trainer read one, a band when
                     the client showed a level, and the named absence when neither. The three stay apart on
                     the screen the way they are kept apart in the store. -->
                <fieldset
                    id="turn-energy-field"
                    class="flex flex-col gap-1 rounded-md"
                    :class="emphasizedField === 'energy' ? 'border-2 border-pick-line p-2' : ''"
                >
                    <legend class="text-ink-muted">Energy</legend>
                    <div class="flex flex-wrap gap-3">
                        <BaseRadio
                            v-for="state in energyStates"
                            :key="state.value"
                            v-model="form.energy_state"
                            :value="state.value"
                            name="energy_state"
                            :label="state.label"
                            inline
                        />
                    </div>
                    <BaseInput
                        v-if="form.energy_state === 'exact'"
                        id="turn-energy"
                        v-model="form.energy"
                        type="number"
                        name="energy"
                        min="0"
                        max="100"
                        :placeholder="placeholder('energy')"
                        :aria-describedby="describedBy('energy')"
                        :error="errorOf('energy')"
                    />
                    <BaseSelect
                        v-else-if="form.energy_state === 'band'"
                        id="turn-energy-band"
                        v-model="form.energy_band"
                        name="energy_band"
                        :options="energyBands.map(band => ({ value: band.value, label: band.label }))"
                        aria-label="Energy band"
                        :aria-describedby="describedBy('energy_band')"
                        :error="errorOf('energy_band')"
                    />
                    <p v-else class="text-xs text-ink-muted" title="Energy is recorded as not recorded for this turn.">
                        Not recorded
                    </p>
                </fieldset>

                <label
                    id="turn-mood-field"
                    class="flex flex-col gap-1 rounded-md"
                    :class="emphasizedField === 'mood' ? 'border-2 border-pick-line p-2' : ''"
                >
                    <span class="text-ink-muted">Mood</span>
                    <BaseSelect
                        id="turn-mood"
                        v-model="form.mood"
                        name="mood"
                        :options="[{ value: '', label: 'not recorded' }, ...props.write.moods.map(tier => ({ value: tier, label: tier }))]"
                        :aria-describedby="describedBy('mood')"
                        :error="errorOf('mood')"
                    />
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Outcome *</span>
                    <BaseSelect
                        id="turn-outcome"
                        v-model="form.outcome"
                        name="outcome"
                        :options="outcomeOptions"
                        required
                        :aria-describedby="describedBy('outcome')"
                        :error="errorOf('outcome')"
                        />
                    </label>

                    <label v-if="outcomeIsFailure" class="flex flex-col gap-1">
                    <span class="text-ink-muted">Penalty kind *</span>
                    <BaseSelect
                        id="turn-penalty_kind"
                        v-model="form.penalty_kind"
                        name="penalty_kind"
                        :options="[
                            { value: '', label: 'Choose' },
                            { value: 'energy', label: 'Energy' },
                            { value: 'mood', label: 'Mood' },
                            { value: 'stat', label: 'A stat' }
                        ]"
                        required
                        :aria-describedby="describedBy('penalty_kind')"
                        :error="errorOf('penalty_kind')"
                                            />
                    </label>

                <!-- The Performance observation pair (Our Grand Concert only, the dated rail row in
                     SCREEN_SPEC.md). A change, never a level: the corpus publishes no starting value,
                     so the delta is a signed integer with no min, no max and no placeholder, and the
                     empty type records nothing for the turn. -->
                <template v-if="props.write.performance.enabled">
                    <BaseSelect
                        id="turn-performance-type"
                        v-model="performanceType"
                        name="performance[type]"
                        label="Performance type"
                        :options="[{ value: '', label: 'none, nothing to record' }, ...props.write.performance.types.map(word => ({ value: word, label: word }))]"
                        :aria-describedby="errorOf('performance.type') === undefined ? undefined : 'turn-performance-type-error'"
                        :error="errorOf('performance.type')"
                    />
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Performance change</span>
                        <BaseInput
                            id="turn-performance-delta"
                            v-model="performanceDelta"
                            type="number"
                            name="performance[delta]"
                            step="1"
                            :aria-describedby="errorOf('performance.delta') === undefined ? undefined : 'turn-performance-delta-error'"
                            :error="errorOf('performance.delta')"
                        />
                    </label>
                </template>

                <!-- The five facility levels the client's ladder shows beside each training. Optional by
                     design: a turn logged without reading the ladder records no level, and a zero would
                     claim a facility at its floor. Behind a disclosure because it is five inputs most
                     turns do not need, and a collapsed `details` keeps them out of the tab order. -->
<details class="rounded-md border border-rule bg-raised p-3 @4xl:col-span-3">
                    <summary class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong">
                        Facility levels this turn
                    </summary>
                    <div class="mt-2 grid grid-cols-2 gap-3 @4xl:grid-cols-5">
                        <label v-for="field in facilityFields" :key="field.name" class="flex flex-col gap-1">
                            <span class="text-ink-muted">{{ field.label }}</span>
                            <BaseInput
                                :id="`turn-${field.name}`"
                                v-model="form[field.name]"
                                type="number"
                                :name="field.name"
                                min="1"
                                max="5"
                                :aria-describedby="describedBy(field.name)"
                                :error="errorOf(field.name)"
                            />
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-ink-muted">
                        One to five, the ladder's own range. Leave a facility blank when this turn did
                        not read it.
                    </p>
                </details>

                <!-- The two readings the client prints before a turn resolves: the failure rate and the
                     preview gains. Transcribed, not computed: the tool stores what the Trainer read
                     and never projects an outcome (`ADR-0016`, `PRD.md` §6.11). Optional by design, so
                     a turn logged without reading them stores neither. -->
                <details class="rounded-md border border-rule bg-raised p-3 @4xl:col-span-3">
                    <summary class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong">
                        Preview readings
                    </summary>
                    <div class="mt-2 grid grid-cols-2 gap-3 @4xl:grid-cols-6">
                        <label class="col-span-2 flex flex-col gap-1 @4xl:col-span-1">
                            <span class="text-ink-muted">Failure rate</span>
                            <BaseInput
                                id="turn-failure_rate"
                                v-model="form.failure_rate"
                                type="number"
                                name="failure_rate"
                                min="0"
                                max="100"
                                :aria-describedby="describedBy('failure_rate')"
                                :error="errorOf('failure_rate')"
                            />
                        </label>
                        <template v-for="field in previewFields" :key="field.name">
                            <label class="flex flex-col gap-1">
                                <span class="text-ink-muted">{{ field.label }}</span>
                                <BaseInput
                                    :id="`turn-preview_gains-${field.name}`"
                                    v-model="form.preview_gains[field.name]"
                                    type="number"
                                    :name="`preview_gains[${field.name}]`"
                                    :aria-describedby="errorOf(`preview_gains.${field.name}`) === undefined ? undefined : `turn-preview_gains-${field.name}-error`"
                                    :error="errorOf(`preview_gains.${field.name}`)"
                                />
                            </label>
                        </template>
                        <p class="mt-2 text-xs text-ink-muted">
                        What the client showed: the failure percentage and the gains it previewed. Enter
                        a negative number when the preview showed a drop. Leave them blank when this turn
                        did not read them.
                    </p>
                </div>
            </details>

            <div class="flex flex-wrap items-end gap-3 @4xl:col-span-3">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 text-sm font-bold text-on-primary-action disabled:cursor-wait disabled:bg-disabled"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Sending…' : chosenExtra !== null ? 'Record this turn' : 'Record this training' }}
                    </button>
                    <p class="max-w-3xl text-xs text-ink-muted">
                        The write lands on the Cockpit, where the recorded turn appears in the correction
                        selector ({{ props.run.trainee }}, turn {{ props.write.turn }}).
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
                <a :href="props.run.deck_url" class="inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                    Record this run&apos;s deck
                </a>
            </template>
        </p>
    </CareerLayout>
</template>
