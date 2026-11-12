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
 * **The write is a direct record.** `Train` on a card holds that card's choice for the form below -
 * carried, never re-asked (WCAG 3.3.7) - and the form posts to `runs.turns.store` with the
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
import { BaseInput, BaseSelect } from '../../components/Form';
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';
import DisciplineComparison from '../../components/career/DisciplineComparison.vue';
import { undoTurnLabel } from '../../lib/career/turnDisplay';
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

/**
 * The published Energy row. Same frame as `PublishedRow` plus the signed figure the server also sends
 * (`TrainingDecisionController::publishedFor()`), which the option energy cell reads. It was declared
 * as a plain `PublishedRow`, so `cost.value` was invisible to the checker while the server shipped it
 * on every option that has an Energy cost.
 */
interface PublishedCostRow extends PublishedRow {
    /** Signed Energy: negative spends, positive refunds (Wit returns Energy). */
    value: number;
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
        cost: PublishedCostRow | null;
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
    /*
     * `band` is the two-value advisory pair, not free text: `BANDS` below is keyed by it, and
     * `Advice::$band` only ever holds `AtOrAboveAdvisory` or `BelowAdvisory`. `reason` is the
     * recommendation's own source line, sent by `TrainingDecisionController::advisorSection()` and
     * rendered in the "Why rest" list; it was missing from this type while the server was already
     * sending it, so the `v-if` that guards it could never be true.
     */
    advisor: {
        action: string | null;
        band: 'AtOrAboveAdvisory' | 'BelowAdvisory' | null;
        absence: string | null;
        reason: string | null;
    };
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
    /** C2: the recorded facts per discipline for the comparison disclosure. */
    discipline_history: Record<string, {
        attendance_count: number;
        recorded_facility_levels: number[];
        historical_gain: { mean: number; sample_size: number } | null;
    }>;
    /** The five disciplines in the client's own order, the same order the option cards print. */
    stat_order: string[];
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
 * about magnitude - no source publishes a recovery or an outing figure (`ADR-0001` §3).
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
/*
 * The two field lists are `as const` tuples, so `field.name` is a literal union rather than `string`.
 * That matters at the `v-model` below: `form` is a typed object, and indexing it with a bare `string`
 * is an implicit `any`. The casts keep the literal keys the list already proves.
 */
type FacilityFieldName = `facility_${(typeof statFields)[number]['name']}`;
type PreviewFieldName = (typeof TURN_ENTRY_PREVIEW_FIELDS)[number]['name'];

const facilityFields = TURN_ENTRY_FACILITY_FIELDS as ReadonlyArray<{ name: FacilityFieldName; label: string }>;

// The client's preview row, from the shared source. Read as one object on the form and posted as one,
// because `preview_gains` is stored as a single payload rather than six columns.
type PreviewGainsKey = 'speed' | 'stamina' | 'power' | 'guts' | 'wit' | 'sp';

const previewFields = TURN_ENTRY_PREVIEW_FIELDS as ReadonlyArray<{ name: PreviewGainsKey; label: string }>;

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

/*
 * F-08's guard. The `Race` action is a link rendered as a radio, and leaving for the race desk is a
 * hard `window.location.href` — which destroys every value above with the JS context. Choosing an
 * action and abandoning the turn must not be the same gesture, so a departure is only allowed once
 * the Trainer has been told what it costs.
 *
 * The snapshot is taken once, at mount, so "dirty" means "differs from how the form opened" rather
 * than "differs from empty": `turn` and the facility ladder open pre-filled from the last logged
 * turn, and prompting on those untouched defaults would be worse than the defect.
 */
const initialSnapshot = JSON.stringify(form.data());

const isDirty = (): boolean => JSON.stringify(form.data()) !== initialSnapshot;

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
        // F-08: `preserveState: true` means a successful post does NOT remount, so the draft has to be
        // cleared here rather than by the mount path. A 422 writes nothing to the server and must not
        // clear it — the Trainer fixes the field and re-submits with everything still present — which
        // is why this is `onSuccess` and not `onFinish`.
        .post(props.write.action, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => draftStore.clear(),
        });
};

const errorOf = (field: string): string | undefined =>
    (form.errors as Record<string, string | undefined>)[field];

const describedBy = (field: string): string | undefined =>
    errorOf(field) === undefined ? undefined : `turn-${field}-error`;

const capFor = (label: string): number =>
    props.options.find((option) => option.key === label)?.cap ?? 0;

/*
 * The accelerator, and the contract printed for it (HFW `training-decision.html`: 1-5 pick, Enter
 * records, Escape goes back; H7 - an accelerator a Trainer has to already know is not discoverable).
 *
 * The listener stands down on typing surfaces: a number typed into "Turn" must pick nothing, and Enter
 * inside a field keeps the browser's own submit path instead of recording twice. A focused button keeps
 * its own Enter activation for the same reason.
 */
const typing = (target: EventTarget | null): boolean => {
    const el = target as HTMLElement | null;
    const tag = el?.tagName;

    return tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA' || el?.isContentEditable === true;
};

function onKeydown(event: KeyboardEvent): void {
    if (event.metaKey || event.ctrlKey || event.altKey || typing(event.target)) {
        return;
    }

    if (/^[1-5]$/.test(event.key)) {
        const option = props.options[Number(event.key) - 1];

        if (option !== undefined) {
            event.preventDefault();
            choose(option.choice);
        }

        return;
    }

    if (event.key === 'Enter') {
        if (choice.value === null || (event.target as HTMLElement | null)?.tagName === 'BUTTON') {
            return;
        }

        event.preventDefault();
        recordTurn();

        return;
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        window.history.back();
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

/*
 * F-08 — the turn draft (plan §9.1).
 *
 * `useForm` lives in memory only, so a hard navigation (`window.location.href` for the race desk, a
 * reload, or the browser's own back/forward) destroys every entered value. The confirm guard beside
 * this is defence in depth and does not preserve anything — it stops an accident, it does not undo one.
 *
 * The record is keyed on the RUN, not the URL or the tab: `td.turn-draft.<run_id>`. `sessionStorage`
 * is already partitioned per tab by the platform, so the draft is per-tab by construction and closing
 * the tab ends it; there is nothing to synchronise and no cross-tab conflict policy to invent.
 *
 * `v` gates the schema: a record whose version is missing or unknown is discarded rather than
 * best-effort migrated, because a transient draft is never the only copy of anything. `fingerprint`
 * is the server payload the form seeds from — if the career has moved on since the draft was written,
 * restoring it would show a Trainer numbers from a turn that is no longer the one being recorded, so
 * the draft is dropped silently and the server's own seed stands.
 *
 * Restoration seeds the form only. The server re-validates everything on submit, so a stale draft can
 * never overwrite persisted career data.
 */
interface TurnDraft {
    v: 1;
    run_id: number;
    fingerprint: string;
    data: Record<string, unknown>;
    performanceType: string;
    performanceDelta: string;
}

const DRAFT_VERSION = 1;

const draftKey = (): string => `td.turn-draft.${props.run.id}`;

/**
 * What the form was seeded from. Any change here means the page being shown is not the page the draft
 * was written on, and the draft must not be applied.
 */
const draftFingerprint = (): string =>
    JSON.stringify({
        turn: props.write.turn,
        previous: props.write.previous,
        turn_index: props.careerPosition?.turn_index ?? null,
    });

/*
 * Storage can be unavailable (private mode, a locked-down policy, a full quota). Every access is
 * wrapped and a failure is silent: persistence is an enhancement, and a Trainer must never see an
 * error about a feature they did not ask for. It must also never block navigation or input.
 */
const draftStore = {
    read(): TurnDraft | null {
        try {
            const raw = window.sessionStorage.getItem(draftKey());

            if (raw === null) {
                return null;
            }

            const parsed = JSON.parse(raw) as Partial<TurnDraft>;

            // Unknown or missing version: a transient draft is discarded, never migrated.
            if (parsed.v !== DRAFT_VERSION) {
                return null;
            }

            // The career moved on since this was written.
            if (parsed.fingerprint !== draftFingerprint()) {
                return null;
            }

            return parsed as TurnDraft;
        } catch {
            return null;
        }
    },
    write(record: TurnDraft): void {
        try {
            window.sessionStorage.setItem(draftKey(), JSON.stringify(record));
        } catch {
            // Silent by design — see above. No console error, no in-app claim of persistence.
        }
    },
    clear(): void {
        try {
            window.sessionStorage.removeItem(draftKey());
        } catch {
            // Silent by design.
        }
    },
};

/** Snapshot the whole form plus the two refs that live outside it. */
function captureDraft(): void {
    draftStore.write({
        v: DRAFT_VERSION,
        run_id: props.run.id,
        fingerprint: draftFingerprint(),
        data: form.data() as unknown as Record<string, unknown>,
        performanceType: performanceType.value,
        performanceDelta: performanceDelta.value,
    });
}

/*
 * Restore on mount, before anything else can read the form. `props.write.turn` and the facility ladder
 * open pre-filled from the last logged turn, so this must overwrite rather than merge: a restored
 * draft is the Trainer's own in-progress answer for THIS turn, and it is only applied when the
 * fingerprint proved it belongs here.
 */
const restoredDraft = draftStore.read();

if (restoredDraft !== null) {
    for (const [key, value] of Object.entries(restoredDraft.data)) {
        if (key in form) {
            (form as unknown as Record<string, unknown>)[key] = value;
        }
    }

    performanceType.value = restoredDraft.performanceType;
    performanceDelta.value = restoredDraft.performanceDelta;
}

/*
 * Write on change, not on a timer: every edit to the form or to either ref replaces the record, so a
 * hard navigation a moment later still finds the latest value. `watch` is deep because
 * `preview_gains` is an object whose keys mutate in place.
 */
watch(() => form.data(), captureDraft, { deep: true });
watch([performanceType, performanceDelta], captureDraft);


/*
 * The HFW's five-discipline list, built from the options the controller already sends. The feeder
 * routing is the game's own ([Both], UMAMUSUME_REFERENCE.md §1.1.1); the gains and the Energy figure
 * are the published per-level row the run's facility level selects, and a run that never recorded a
 * level prints the absence rather than a Level-1 stand-in.
 */
const FEEDER: Record<string, string> = {
    Speed: 'Feeds Power',
    Stamina: 'Feeds Guts',
    Power: 'Feeds Stamina',
    Guts: 'Feeds Speed and Power',
    Wit: 'Feeds Speed',
};

const signedEnergy = (value: number | null): string => {
    if (value === null) {
        return 'Energy not recorded';
    }

    return value < 0 ? `−${Math.abs(value)} Energy` : `+${value} Energy`;
};

const disciplines = computed(() =>
    props.options.map((option, index) => ({
        choice: option.choice,
        key: option.key,
        shortcut: index + 1,
        feeder: FEEDER[option.key] ?? '',
        recommended: option.key === props.advisor.action,
        gains: option.published.gains?.text ?? null,
        gainsAbsence: option.published.gains_absence,
        level: option.published.gains?.level ?? null,
        energy: option.published.cost?.value ?? null,
    })),
);

const otherActions = computed(() => [
    ...props.write.extra_choices.map((extra) => ({
        value: extra.value,
        label: extra.label,
        note: extra.note,
        recommended: extra.recommended,
        href: null as string | null,
    })),
    {
        value: 'race',
        label: 'Race',
        note: 'Optional race · see the race desk',
        recommended: false,
        href: `/training-runs/${props.run.id}/races`,
    },
]);

const chooseAction = (value: string, href: string | null): void => {
    if (href !== null) {
        // F-08: the departure is the destructive act, so it is the thing that needs consent. A clean
        // form goes straight through; a dirty one is asked first, and a refused prompt leaves every
        // entered value where it was.
        if (isDirty() && !window.confirm('Leave for the race desk? The values entered on this form will be lost.')) {
            return;
        }

        window.location.href = href;

        return;
    }

    choose(value);
};

/*
 * F-08: Discard is the explicit "throw this away" gesture, so it clears the draft as well as leaving.
 * Without this the draft would survive the discard and be re-applied on the next visit, which would
 * make the button a lie.
 */
const discard = (): void => {
    draftStore.clear();
    window.history.back();
};

const undoableTurn = computed(() => undoTurnLabel(props.write.turn));

const energyBandNote = computed(() =>
    props.energy.state === 'exact' && props.energy.value !== null
        ? `Energy ${props.energy.value}.`
        : `${props.energy.label}. A band is the reading; the exact figure is not recorded.`,
);

</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="positionLabel"
        :run-url="props.run.run_url"
        current="training"
    >
        <Head :title="'Training decision: ' + props.run.trainee" />

        <template #head>
            <PageHeader
                eyebrow="Training decision"
                title="Pick this turn's action"
                description="Five disciplines, rest, recreation and an optional race. The advisor's recommendation is marked; nothing is committed until you record the turn."
            >
                <template #actions>
                    <a
                        :href="props.run.cockpit_url"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong"
                    >Back to cockpit</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That turn did not go through. Nothing was saved; try again.
        </p>

        <form id="training-record-form" @submit.prevent="recordTurn">
            <div class="grid gap-4 g-side">
                <div class="flex flex-col gap-4">
                    <section class="card" aria-labelledby="disciplines-heading">
                        <h2 id="disciplines-heading">Disciplines</h2>
                        <p class="sub">
                            {{ energyBandNote }} Wit costs no Energy. Figures are the published Lv base the
                            run recorded for each facility, before multipliers.
                        </p>

                        <div class="flex flex-col gap-2.5">
                            <label
                                v-for="discipline in disciplines"
                                :key="discipline.choice"
                                class="flex flex-wrap items-center gap-3 rounded-md px-3 py-[11px]"
                                :class="
                                    choice === discipline.choice
                                        ? 'border-2 border-pick-line bg-green-tint'
                                        : 'border border-rule-soft bg-raised'
                                "
                            >
                                <input
                                    type="radio"
                                    name="pick"
                                    :checked="choice === discipline.choice"
                                    :aria-label="discipline.key + ' training'"
                                    @change="choose(discipline.choice)"
                                >
                                <span class="min-w-0 flex-1 basis-[190px]">
                                    <span class="font-extrabold text-ink-strong">
                                        <span class="mr-1.5 inline-flex min-w-[18px] justify-center rounded border border-rule px-1 font-mono text-xs">{{ discipline.shortcut }}</span>
                                        {{ discipline.key }}
                                        <span v-if="discipline.key === 'Wit'" class="ml-1 rounded bg-brand-accent-soft px-1.5 py-0.5 text-xs font-bold text-brand-accent-ink">no Energy</span>
                                        <span v-if="discipline.recommended" class="ml-1 rounded bg-status-positive px-1.5 py-0.5 text-xs font-bold text-status-positive-ink">Recommended</span>
                                    </span>
                                    <span class="block text-xs text-ink-muted">
                                        {{ discipline.feeder }} · Lv {{ discipline.level ?? 'not recorded' }} base
                                    </span>
                                </span>
                                <span class="text-right font-mono text-sm text-ink-strong">
                                    <template v-if="discipline.gains !== null">{{ discipline.gains }}</template>
                                    <span v-else class="font-semibold text-ink-muted">{{ discipline.gainsAbsence }}</span>
                                </span>
                                <span
                                    class="text-right font-mono text-sm font-extrabold"
                                    :class="discipline.energy !== null && discipline.energy > 0 ? 'text-[var(--up)]' : 'text-[var(--down)]'"
                                >{{ signedEnergy(discipline.energy) }}</span>
                            </label>
                        </div>

                        <Note class="mt-3">
                            <b>Published per-level figures, before multipliers.</b> A discipline with no
                            recorded facility level states that absence rather than a Level-1 number.
                        </Note>
                    </section>

                    <section class="card" aria-labelledby="other-actions-heading">
                        <h2 id="other-actions-heading">Other actions</h2>
                        <div class="grid gap-2.5 g-3">
                            <label
                                v-for="action in otherActions"
                                :key="action.value"
                                class="flex cursor-pointer items-center gap-2.5 rounded-md px-3 py-2.5"
                                :class="
                                    choice === action.value
                                        ? 'border-2 border-pick-line bg-green-tint'
                                        : 'border border-rule-soft bg-raised'
                                "
                            >
                                <input
                                    type="radio"
                                    name="pick"
                                    :checked="choice === action.value"
                                    :aria-label="action.label"
                                    @change="chooseAction(action.value, action.href)"
                                >
                                <span>
                                    <span class="block font-extrabold text-ink-strong">
                                        {{ action.label }}
                                        <span v-if="action.recommended" class="ml-1 rounded bg-status-positive px-1.5 py-0.5 text-xs font-bold text-status-positive-ink">Recommended</span>
                                    </span>
                                    <span class="block text-xs text-ink-muted">{{ action.note }}</span>
                                </span>
                            </label>
                        </div>
                    </section>
                </div>

                <div class="flex flex-col gap-4">
                    <section class="card" aria-labelledby="why-rest-heading">
                        <h2 id="why-rest-heading">Why {{ props.advisor.action ?? 'this action' }}</h2>
                        <ul class="list-disc pl-[18px] text-sm leading-[1.6] text-ink">
                            <li v-if="props.advisor.reason !== null">{{ props.advisor.reason }}</li>
                            <li v-if="bandWord !== null">This turn is {{ bandWord }}; the line it is measured against is the sourced 50.</li>
                            <li>
                                Rest pays <b>+50 on 58.1%</b> of results, +70 on 25.4%, +30 on 12.8%, and
                                +30 with <b>Late-Night Feeling</b> on 3.7% (n=3,833).
                            </li>
                            <li v-if="chosen !== null && chosen.energy_after !== null">
                                Energy after this training is calculated at
                                <span class="font-mono">{{ chosen.energy_after.min }}-{{ chosen.energy_after.max }}</span>,
                                not read from the client.
                            </li>
                        </ul>
                        <Note class="mt-2.5">
                            Below 50 Energy the failure curve changes character rather than degrading
                            linearly, which is why the advisory line sits where it does.
                        </Note>
                    </section>

                    <section class="card" aria-labelledby="training-record-heading">
                        <h2 id="training-record-heading" ref="recordHeading" tabindex="-1">Recorded outcome</h2>
                        <p class="sub">Written only after the turn is committed.</p>
                        <div class="grid gap-2.5 g-2">
                            <label class="flex flex-col gap-1">
                                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Energy after</span>
                                <BaseInput id="turn-energy" v-model="form.energy" type="number" name="energy" min="0" max="100" placeholder="-" :error="errorOf('energy')" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Mood after</span>
                                <BaseSelect
                                    id="turn-mood"
                                    v-model="form.mood"
                                    name="mood"
                                    :options="[{ value: '', label: 'Unchanged' }, ...props.write.moods.map((mood) => ({ value: mood, label: mood }))]"
                                    :error="errorOf('mood')"
                                />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Fans</span>
                                <BaseInput id="turn-fans" v-model="form.fans" type="number" name="fans" min="0" :placeholder="placeholder('fans')" :error="errorOf('fans')" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Skill points</span>
                                <BaseInput id="turn-sp" v-model="form.sp" type="number" name="sp" min="0" :placeholder="placeholder('sp')" :error="errorOf('sp')" />
                            </label>
                        </div>
                        <Note class="mt-2.5">
                            These fields exist because the client shows them, not because the tool
                            computes them. An empty field records "not read off the screen", which is
                            different from a zero.
                        </Note>
                    </section>

                    <div v-if="form.hasErrors" ref="alert" role="alert" tabindex="-1" class="rounded-md border border-risk bg-raised p-3 text-sm text-risk">
                        <p class="font-semibold">The turn was not recorded.</p>
                        <ul class="mt-1 list-disc pl-5">
                            <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                        </ul>
                    </div>

                    <div class="savebar">
                        <span class="text-xs text-ink-muted">
                            {{ chosenLabel ?? 'No action' }} selected ·
                            <span class="kbd">1</span>-<span class="kbd">5</span> pick ·
                            <span class="kbd">Enter</span> records ·
                            <span class="kbd">Esc</span> back
                        </span>
                        <!-- R7-21: disabled, matching what the title says and matching
                             `RaceDecision.vue`'s own undo, which had the binding from the start. The
                             label still names a real turn — the last logged one — because an undo that
                             arrives later will remove exactly that turn, and the number is not a
                             placeholder. -->
                        <button
                            type="button"
                            class="btn btn-quiet min-h-11"
                            disabled
                            title="The build has no undo; this screen proposes one."
                        >
                            Undo turn {{ undoableTurn }}
                        </button>
                        <button type="button" class="btn min-h-11" @click="discard()">Discard</button>
                        <button type="submit" class="btn btn-primary min-h-11" :disabled="form.processing">
                            {{ form.processing ? 'Recording…' : 'Record turn ' + form.turn }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- C2, the discipline comparison: the recorded facts side by side, collapsed by default
                 because the five option cards above already answer the primary question and this is
                 the comparison a Trainer opens when the choice is genuinely close. -->
            <details class="card mt-4">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Compare disciplines</summary>
                <p class="mt-1 text-xs text-ink-muted">
                    This view shows only what this run has recorded. It does not project future gains.
                </p>
                <div class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="Discipline comparison, scrollable">
                    <DisciplineComparison :history="discipline_history" :order="stat_order" />
                </div>
            </details>

            <!-- HFW OVERRIDE (write fields): the wireframe draws only the four outcome fields, but the
                 request this form posts requires the turn number and the five client-read totals. They
                 stay available, collapsed, so the visible page is the wireframe's and the write still
                 validates against the boundary that already owns it. -->
            <details class="card mt-4" open>
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Client totals this turn (required to record)</summary>
                <p class="mt-1 text-sm text-ink-muted">
                    What the client shows after the turn resolves. This tool does not project these, so
                    they start empty and the request refuses a turn that was not read.
                </p>
                <div class="mt-3 grid max-w-3xl grid-cols-2 gap-3 text-sm @4xl:grid-cols-3">
                    <label class="flex flex-col gap-1">
                        <!-- R7-20, owner ruling (a): the field's value is already the run's next turn
                             (`TrainingDecisionController.php:574` sends `nextTurnNumber()`), so the defect
                             was the label. `Turn *` read as "the run's position" while the value is the
                             turn about to be recorded, which is how walk 6 committed a Junior position on
                             a mid-career run without noticing. The label names what it holds; the helper
                             below says what the number means and how the client prints it. -->
                        <span class="text-ink-muted">Turn to play (the run's next turn) *</span>
                        <BaseInput id="turn-turn" v-model="form.turn" type="number" name="turn" min="1" required :aria-describedby="describedBy('turn')" :error="errorOf('turn')" />
                        <span class="text-xs text-ink-muted">
                            The turn this entry records. As the client shows this turn, the calendar name
                            and the index are the same fact: Senior Year Early October is turn 67. A run
                            imported at a later position records that turn here.
                        </span>
                    </label>
                    <label v-for="field in statFields" :key="field.name" class="flex flex-col gap-1">
                        <span class="text-ink-muted">{{ field.label }} *</span>
                        <BaseInput
                            :id="'turn-' + field.name"
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
                    <label v-for="field in facilityFields" :key="'facility-' + field.name" class="flex flex-col gap-1">
                        <span class="text-ink-muted">{{ field.label }} level</span>
                        <BaseInput
                            :id="'facility-' + field.name"
                            v-model="form[field.name]"
                            type="number"
                            :name="field.name"
                            min="1"
                            max="5"
                            :error="errorOf(field.name)"
                        />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Outcome</span>
                        <BaseSelect id="turn-outcome" v-model="form.outcome" name="outcome" :options="outcomeOptions" :error="errorOf('outcome')" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Energy state</span>
                        <BaseSelect id="turn-energy-state" v-model="form.energy_state" name="energy_state" :options="energyStates" :error="errorOf('energy_state')" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Energy band</span>
                        <BaseSelect id="turn-energy-band" v-model="form.energy_band" name="energy_band" :options="energyBands" :error="errorOf('energy_band')" />
                    </label>
                    <label v-if="outcomeIsFailure" class="flex flex-col gap-1">
                        <span class="text-ink-muted">Penalty</span>
                        <BaseSelect id="turn-penalty" v-model="form.penalty_kind" name="penalty_kind" :options="[{ value: '', label: 'None recorded' }, { value: 'energy', label: 'Energy' }, { value: 'mood', label: 'Mood' }, { value: 'both', label: 'Both' }]" :error="errorOf('penalty_kind')" />
                    </label>
                    <template v-if="props.write.performance.enabled">
                        <!--
                          Both controls name themselves through `label`, not through a wrapping
                          `<label>`: a wrapper owns the text and leaves the inner select/input with no
                          accessible name, which is the defect `BaseSelectAccessibleNameTest` exists
                          for. These two sites were the last ones still carrying the wrapper.
                        -->
                        <BaseSelect
                            id="turn-performance-type"
                            v-model="performanceType"
                            name="performance_type"
                            label="Performance type"
                            :options="[{ value: '', label: 'Not recorded' }, ...props.write.performance.types.map((type) => ({ value: type, label: type }))]"
                        />
                        <BaseInput
                            id="turn-performance-delta"
                            v-model="performanceDelta"
                            name="performance_delta"
                            type="number"
                            label="Performance delta"
                        />
                    </template>
                </div>
                <p class="mt-2 text-xs text-ink-muted">
                    Keyboard: <span class="kbd">1</span>-<span class="kbd">5</span> pick a discipline,
                    <span class="kbd">Enter</span> records the held choice, <span class="kbd">Esc</span> steps
                    back. Keys stand down while a field has focus.
                </p>
            </details>

            <!-- The two readings the client prints before a turn resolves: the failure rate and the
                 preview gains. Transcribed, not computed: the tool stores what the Trainer read
                 and never projects an outcome (`ADR-0016`, `PRD.md` §6.11). Optional by design, so
                 a turn logged without reading them stores neither. -->
            <details open class="rounded-md border border-rule bg-raised p-3">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                    Preview readings
                </summary>
                <div class="mt-2 grid grid-cols-2 gap-3 md:grid-cols-6">
                    <label class="col-span-2 flex flex-col gap-1 md:col-span-1">
                        <span class="text-ink-muted">Failure rate</span>
                        <input
                            id="turn-failure_rate"
                            v-model="form.failure_rate"
                            type="number"
                            name="failure_rate"
                            min="0"
                            max="100"
                            :aria-describedby="describedBy('failure_rate')"
                            class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                        <span v-if="errorOf('failure_rate')" id="turn-failure_rate-error" class="text-xs text-risk">{{ errorOf('failure_rate') }}</span>
                    </label>
                    <label v-for="field in previewFields" :key="field.name" class="flex flex-col gap-1">
                        <span class="text-ink-muted">{{ field.label }}</span>
                        <input
                            :id="`turn-preview_gains-${field.name}`"
                            v-model="form.preview_gains[field.name]"
                            type="number"
                            :name="`preview_gains[${field.name}]`"
                            :aria-describedby="errorOf(`preview_gains.${field.name}`) === undefined ? undefined : `turn-preview_gains-${field.name}-error`"
                            class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                        <span v-if="errorOf(`preview_gains.${field.name}`)" :id="`turn-preview_gains-${field.name}-error`" class="text-xs text-risk">{{ errorOf(`preview_gains.${field.name}`) }}</span>
                    </label>
                </div>
                <p class="mt-2 text-xs text-ink-muted">
                    What the client showed: the failure percentage and the gains it previewed. Enter
                    a negative number when the preview showed a drop. Leave them blank when this turn
                    did not read them.
                </p>
            </details>
        </form>
    </CareerLayout>
</template>
