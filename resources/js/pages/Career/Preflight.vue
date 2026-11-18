<script setup lang="ts">
/*
 * HFW-OVERRIDE (computed/rect: the wizard-wide class) — `preflight-diff-{1280,390}.json` measures the
 * app's DESIGN.md tokens against the wireframe's mock CSS and the shell wrapper's offset. Tokens
 * outrank the mock palette (`AGENTS.md` §2) and the wrapper moves every box, so both buckets carry
 * entries by construction; the page is not restyled to chase the mock's values.
 */
/*
 * The career setup wizard's last step, `SCR-CAR-010` (SCREEN-008, PRD FR-A-5, `ADR-0020` §1 and §3).
 * Step 6 of six, and the only step that writes.
 *
 * **Nothing is re-asked.** Every fact on this page is read from the draft the five earlier steps filled and
 * arrives as one `contract` prop, so Back and Edit round trips are lossless by construction: a step opens
 * on the values it stored, not on what the client still held.
 *
 * **The page is a checklist, not a report.** One row per entered step, each carrying its own state glyph
 * (entered or missing), the step's name, a one-line summary of what the draft holds or `Not entered`, and
 * the one Edit door that returns to that step. The earlier revision printed the whole contract again as
 * four detail blocks and repeated every door in a second row under them; the summary is what a Trainer
 * reads at the commitment screen, and the detail lives one click away on the step that owns it.
 *
 * **`Start career` is the page's only primary, and it is honest about what it needs.** A career needs a
 * scenario and a trainee, which is exactly what `StartCareerRequest` refuses without, so the button is
 * natively `disabled` until both are entered, and the reason is printed beside it whether or not it is
 * pressed.
 *
 * *Dated correction 2026-10-22 (UX audit F-05): the paragraph above used to say the button carries
 * `aria-disabled` and a click guard "rather than the `disabled` attribute", so that the plan's "focus
 * the first missing row on an attempted click" stayed reachable. `DESIGN.md` §5 (b)'s 2026-10-17
 * amendment names that shape as the defect — an enabled control whose label promises what the product
 * cannot perform — and rules native `disabled` or a named absence. The focus-on-refusal path is
 * removed with it: a disabled control cannot be pressed, so there is no attempted click to answer, and
 * the reason and the missing rows are already on screen before the press. The button is truly disabled
 * while the write is in flight as well, as before.*
 *
 * **The primary action creates the run.** The write re-runs every step's own Form Request server-side
 * before it creates anything, so this page's rendering is never the trust boundary. While the write is in
 * flight the button says so and is truly disabled, because it is a user-initiated action and its own
 * progress is the only loading state this screen has (ADR-0007).
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

interface Trainee {
    id: number;
    name: string;
    name_ja: string | null;
    /** The chosen costume form's own label, resolved server-side; null when the draft names none. */
    form_label: string | null;
}

interface Scenario {
    key: string;
    label: string;
    /**
     * Ruling F1's Trackblazer variant classification and the track it selects, derived by
     * `TrackblazerVariant` from the draft trainee's aptitudes. Null for every scenario that composes no
     * Result Point objectives, and for a Trackblazer draft with no trainee chosen yet.
     */
    variant: {
        classification: 'dirt_leaning' | 'limited_turf_range' | 'ambiguous' | 'standard' | null;
        track: 'dirt_leaning' | 'limited_turf_range' | 'standard';
        reason: string;
        evidence: { label: string; letter: string }[];
    } | null;
    focus: string | null;
    /** Why no focus sentence exists, when the scenario's own record says why (URA Finale's). */
    focus_reason: string | null;
    /**
     * The scenario's own rules, read from `config/scenarios.php` by
     * `PreflightController::scenarioSection()`. These are what the wireframe's "What this scenario
     * asks" table prints (`career-preflight.html:152-155`) — rules the configuration states, not
     * facts about the draft. Empty arrays and nulls mean the scenario's record states none of them,
     * and each row prints its absence with a reason.
     */
    asks: {
        facility_level_source: string | null;
        fan_gates: {
            label: string;
            year_label: string | null;
            timing: string | null;
            turf: number | null;
            dirt: number | null;
            effect: string | null;
        }[];
        stamina_by_distance: Record<string, { min: number; max: number } | null>;
        /** The source's own hedge for the speed target, e.g. "around 800 Speed, or an A rank". */
        speed_phrasing: string | null;
        /**
         * The duel's reward rule, forwarded from `config('scenarios.scenarios.<key>.duel')`. Null for
         * every scenario but URA. `cap_points` is the corrected magnitude (2026-11-12: +4, not "one
         * tier"); `gains` is the config's own phrase for the rest.
         */
        duel: {
            win_sp: number | null;
            loss_sp: number | null;
            cap_points: number | null;
            gains: string | null;
            escalation_wins: number | null;
        } | null;
    };
}

interface Target {
    purpose: string;
    purpose_label: string;
    /** R7-14: all three are nullable on the server (`SetupDraft` reads them through `is_string`, and a
     *  skipped target records none of them), so the type states that rather than promising a string the
     *  payload does not always carry. */
    distance: string | null;
    surface: string | null;
    style: string | null;
    targets: Record<string, number>;
}

interface Spark {
    kind: string;
    kind_label: string | null;
    target: string | null;
    stars: number | null;
}

interface Member {
    slot: string;
    label: string;
    name: string | null;
    rank: number | null;
    rank_letter: string | null;
    rank_label: string;
    is_guest: boolean;
    ancestors: { slot: string; name: string | null; rank: number | null; rank_letter: string | null; rank_label: string; sparks: Spark[]; sparks_label: string }[];
    sparks: Spark[];
    probability: { value: null; title: string };
}

interface Card {
    id: number;
    name: string;
    type: string;
    type_label: string;
    rarity_word: string;
    artworkURL: string | null;
    effects: { effect_id: number; name: string | null; display: string }[];
}

interface Slot {
    position: number;
    label: string;
    is_friend: boolean;
    ownership: 'OWNED' | 'RENTED' | null;
    card: Card | null;
}

interface Warning {
    key: string;
    title: string;
    detail: string;
    href: string;
}

const props = defineProps<{
    step: number;
    /** Plan v2 slice 3.6: three career years of the client's own 24-turn grid, from the server. */
    careerTurns: number;
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    contract: {
        complete: boolean;
        build: {
            trainee: Trainee | null;
            scenario: Scenario | null;
            target: Target | null;
            legacy: { affinity: string | null; members: Member[] } | null;
        };
        deck: {
            slots: Slot[];
            equipped: number;
            analysis: {
                covered: number;
                categories: Record<string, unknown>;
                uncategorised: unknown[];
                strengths: string[];
                weaknesses: string[];
            };
        } | null;
        target: {
            stats: { key: string; label: string; value: number | null }[];
            races: { distance: string | null; surface: string | null; style: string | null };
            skills: { names: string[] };
            /**
             * Why `build.target` is absent, when it is: null when no target and no ceilings exist
             * (the "not entered" sentence is the whole truth), a named reason when ceilings exist
             * without target metadata, or when a stored target can no longer be read. Plan v2 slice
             * 2.4 (reshaped): walk-8 F-06 was one sentence covering three causes (PA-03).
             */
            reason: string | null;
        };
        /**
         * The derivable gaps in the entered data. The controller still composes them and
         * `CareerPreflightTest` still pins each trigger; the checklist states the section-level half
         * (entered or not) and this page no longer renders the list.
         */
        warnings: Warning[];
        ruleset: { label: string; title: string };
    };
    edit: Record<'scenario' | 'trainee' | 'target' | 'legacy' | 'deck', string>;
    startAction: string;
    /**
     * The Independent Training inputs (§1.1.7): the enum's own policy cases and labels, the ceiling the
     * request enforces, and the Global-client skill catalogue a priority can name. Server-resolved so the
     * page never restates the vocabulary, and so a label change cannot drift between the two sides.
     */
    independent: {
        policies: { value: string; label: string }[];
        maxPrioritySkills: number;
        defaultPolicy: string;
        skills: { id: number; name: string }[];
    };
}>();

/** One row of the review checklist: a step, its state, its summary, and its one door back. */
interface ChecklistRow {
    key: 'scenario' | 'trainee' | 'target' | 'legacy' | 'deck';
    label: string;
    entered: boolean;
    summary: string;
    /** The trainee's Japanese name, printed beside her English one where the record carries it. */
    ja?: string | null;
    edit: string;
}

/*
 * The write carries nothing of its own. `StartCareerRequest::prepareForValidation()` composes every run
 * fact out of the session draft, so the request body is empty by design — a hand-made POST cannot
 * smuggle in a trainee, a deck or an ancestry the steps never accepted. Independent Training's three
 * inputs (`independent`, `policy`, `rotation`) are gone with their controls (Phase E2): the HFW draws
 * neither the mode switch nor the policy radios, and the request's rules for them stay nullable.
 */
// The page's one write. The draft supplies every other fact server-side, so the form carries only the
// idle-mode inputs (§1.1.7); typing them here is what lets the refusal print under its own control
// instead of behind `errors: Record<string, string>`. `transform()` in `start()` adds them to the PUT.
const form = useForm<{
    independent: boolean;
    policy: string | null;
    priority_skills: number[];
}>({
    independent: false,
    policy: null,
    priority_skills: [],
});
const alert = ref<HTMLElement | null>(null);

// A refused write comes back with the section's message. The alert is focused rather than left for a
// Trainer to find, because the refusal is the whole answer to the press (WCAG 2.2, SC 3.3.1).
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

/*
 * The scenario's own rules, as the configuration states them (`contract.build.scenario.asks`).
 *
 * Read once here rather than reached into from the template, because the table has to tell "no
 * scenario chosen" (no object at all) apart from "the scenario states none of this" (an object with
 * empty values) — the first prints "no scenario", the second prints each row's own absence.
 */
const asks = computed(() => props.contract.build.scenario?.asks ?? null);

/**
 * The selected Result Point track's display name. The stored keys stay `dirt_leaning`-shaped (ruling E
 * keeps the `grade_*` identifiers and the underscore keys); only this string is display copy.
 */
const preflightTrackLabel = computed<string>((): string => {
    switch (props.contract.build.scenario?.variant?.track) {
        case 'dirt_leaning':
            return 'dirt-leaning';
        case 'limited_turf_range':
            return 'limited-turf-range';
        default:
            return 'standard';
    }
});

/*
 * The finale-target line, assembled from the configuration's own figures: the speed target's own
 * phrasing plus the stamina floor for the distance this run targets. Both come from
 * `config/scenarios.php` (`finale_targets.speed_to_win.phrasing`, `finale_targets.stamina_by_distance`),
 * so nothing here is computed — the sentence is the record's own words, joined.
 *
 * Null when the scenario states neither, so the row prints its absence rather than a bare sentence.
 */
const finaleTargetLine = computed((): string | null => {
    const scenario = props.contract.build.scenario;

    if (scenario === undefined || scenario === null) {
        return null;
    }

    const speed = scenario.asks.speed_phrasing ?? null;
    const distance = props.contract.build.target?.distance ?? null;
    const stamina = distance === null ? null : (scenario.asks.stamina_by_distance[distance] ?? null);
    if (speed === null && stamina === null) {
        return null;
    }

    // The speed half is the scenario's own figure and prints even before the target step, so the
    // row never reads N/A while the scenario states a target. The stamina half joins when the
    // target's distance band names one.
    if (stamina === null) {
        return `Speed to win: ${speed}.`;
    }

    return `Speed to win: ${speed}. Stamina by distance: ${distance} ${stamina.min}-${stamina.max}.`;
});

const legacySummary = computed((): string => {
    const legacy = props.contract.build.legacy;

    if (legacy === null) {
        return 'Not entered';
    }

    const affinity = legacy.affinity === null ? 'No Affinity grade read' : `Affinity ${legacy.affinity}`;

    return `${affinity} · ${legacyNamedCount.value} of two parents recorded`;
});

/** How many parents the stored selection actually names, which is what the row's state must follow. */
const legacyNamedCount = computed((): number => {
    const legacy = props.contract.build.legacy;

    return legacy === null ? 0 : legacy.members.filter((member) => member.name !== null).length;
});

/**
 * The Legacy row's state word, derived from the same count its summary prints (R6-10).
 *
 * `Missing` for none, `Warning` for one, `Pass` only for two. Before this, every entered non-deck
 * row was `Pass`, so `Pass` appeared beside `0 of two parents recorded` -- a green word
 * contradicting its own sentence.
 */
const legacyState = computed((): 'Pass' | 'Warning' | 'Missing' => {
    if (legacyNamedCount.value >= 2) {
        return 'Pass';
    }

    return legacyNamedCount.value === 0 ? 'Missing' : 'Warning';
});

const targetSummary = computed((): string => {
    const target = props.contract.build.target;

    if (target === null) {
        return 'Not entered';
    }

    // R7-14: a skipped target records no surface, distance or style, and the template string printed
    // them as the literal word `Missing` in the same STATE column that carries `Pass` — so a
    // supported-by-design choice read as a defect. The summary states what was recorded and names the
    // absence once, in the row's own voice, rather than leaking a state word into a value.
    // The existing separator is preserved: `surface distance` reads as one phrase and a middle dot
    // before the style separates the axes. Only the nulls change behaviour, so a completely entered
    // target renders byte-identically to before.
    const lead = [target.surface, target.distance].filter((part): part is string => part !== null && part !== '').join(' ');
    const style = target.style !== null && target.style !== '' ? target.style : null;

    if (lead === '' && style === null) {
        return `${target.purpose_label} · no distance, surface or style stated`;
    }

    return `${target.purpose_label} · ${[lead, style].filter((part): part is string => part !== null && part !== '').join(' · ')}`;
});

const rows = computed<ChecklistRow[]>(() => [
    {
        key: 'scenario',
        label: 'Scenario',
        entered: props.contract.build.scenario !== null,
        summary: props.contract.build.scenario?.label ?? 'Not entered',
        edit: props.edit.scenario,
    },
    {
        key: 'trainee',
        label: 'Trainee',
        entered: props.contract.build.trainee !== null,
        summary: props.contract.build.trainee?.name ?? 'Not entered',
        ja: props.contract.build.trainee?.name_ja ?? null,
        edit: props.edit.trainee,
    },
    {
        key: 'target',
        label: 'Your target',
        entered: props.contract.build.target !== null,
        summary: targetSummary.value,
        edit: props.edit.target,
    },
    {
        key: 'legacy',
        label: 'Legacy',
        entered: props.contract.build.legacy !== null,
        summary: legacySummary.value,
        edit: props.edit.legacy,
    },
    {
        key: 'deck',
        label: 'Deck',
        entered: props.contract.deck !== null,
        summary: props.contract.deck === null
            ? 'Not entered'
            : `${props.contract.deck.equipped} of six positions carry a card`,
        edit: props.edit.deck,
    },
]);

/**
 * The heading's count. `role="status"` on it (in the template) is the announcement: the number is the
 * page's answer to "can I start", and it changes only when the draft does.
 *
 * BO-006: the count is the table's own rows, not the server's missing-section list. A filled draft can
 * carry a `Warning` on a section that was entered (the deck with empty slots), and the old
 * `missing_section.*` filter skipped it, so the sentence said `1 step needs attention` over a table
 * reading `Legacy Missing` and `Deck Warning`. `Skipped` is the supported optional-target state and is
 * not attention.
 */
const attention = computed((): { sentence: string; tone: 'clear' | 'attention' } => {
    const counted = rows.value.filter((row) => {
        const state = checkState(row);

        return state !== 'Pass' && state !== 'Skipped';
    });

    if (counted.length === 0) {
        const entered = rows.value.filter((row) => checkState(row) === 'Pass').length;

        return { sentence: `All ${entered} entered steps are recorded.`, tone: 'clear' };
    }

    const noun = counted.length === 1 ? 'step needs' : 'steps need';

    return { sentence: `${counted.length} ${noun} attention`, tone: 'attention' };
});

/** What `StartCareerRequest` refuses without, and nothing else: a scenario and a trainee. */
const canStart = computed((): boolean => props.contract.build.scenario !== null && props.contract.build.trainee !== null);

const acknowledged = ref(false);

const startBlocker = computed((): string | null => {
    const absent: string[] = [];

    if (props.contract.build.scenario === null) {
        absent.push('A scenario');
    }

    if (props.contract.build.trainee === null) {
        absent.push('a trainee');
    }

    if (absent.length === 0) {
        return null;
    }

    const list = absent.join(' and ');

    return absent.length === 1
        ? `${list} is not entered, and a career cannot start without one.`
        : `${list} are not entered, and a career cannot start without both.`;
});

/*
 * Independent Training's three inputs (§1.1.7), held on the page because they are the only facts the
 * draft does not already hold: the trainee, deck and scenario all come from `SetupDraft` server-side,
 * while the idle-mode policy and its priority skills are fixed at the moment the run is created
 * (`Lifecycle::start()`).
 *
 * `prioritySkills` is a list of skill ids, capped at `props.independent.maxPrioritySkills` (10). The
 * picker disables at the ceiling rather than accepting an eleventh and letting the server refuse it:
 * a control that can enter a value the write refuses is the defect `DESIGN.md` §5 names.
 */
const independent = ref(false);
const policy = ref<string>(props.independent.defaultPolicy);
const prioritySkills = ref<number[]>([]);

/** The chosen priority skills as rows, in the order they were added. */
const prioritySkillRows = computed(() =>
    prioritySkills.value
        .map((id) => props.independent.skills.find((skill) => skill.id === id))
        .filter((skill): skill is { id: number; name: string } => skill !== undefined),
);

/** Add the skill the select named, then reset the control so the same list can offer it again later. */
const addPrioritySkill = (event: Event): void => {
    const select = event.target as HTMLSelectElement;
    const id = Number(select.value);
    select.value = '';

    if (! Number.isInteger(id) || id <= 0) {
        return;
    }

    if (prioritySkills.value.includes(id)) {
        return;
    }

    if (prioritySkills.value.length >= props.independent.maxPrioritySkills) {
        return;
    }

    prioritySkills.value = [...prioritySkills.value, id];
};

const removePrioritySkill = (id: number): void => {
    prioritySkills.value = prioritySkills.value.filter((held) => held !== id);
};

const start = (): void => {
    if (form.processing || !acknowledged.value) {
        return;
    }

    // The three idle-mode facts ride on this same PUT, which is the page's only write: a second form
    // would be a second door to one act (`SHR-1`). Absent `independent` is the ordinary career, and
    // `independentConfig()` reads it as null.
    form.independent = independent.value;
    form.policy = independent.value ? policy.value : null;
    form.priority_skills = independent.value ? prioritySkills.value : [];

    form.put(props.startAction);
};

/*
 * The "This run" tiles (`career-preflight.html:90-106`): eight facts, each either a value the draft holds or
 * a stated absence. A tile is never blank and never a zero standing in for "unknown" — the sum below returns
 * null rather than 0 when there is no target, because a target total of 0 is a claim the draft has not made.
 */
const targetTotal = computed((): number | null => {
    const targets = props.contract.build.target?.targets ?? null;

    if (targets === null) {
        return null;
    }

    return Object.values(targets).reduce((sum, value) => sum + value, 0);
});

/** The two parent names the legacy step recorded, or the word the wireframe uses for an empty slot. */
const parentsSummary = computed((): string => {
    const legacy = props.contract.build.legacy;

    if (legacy === null) {
        return 'Not entered';
    }

    const named = legacy.members.map((member) => member.name).filter((name): name is string => name !== null);

    if (named.length === 0) {
        return 'empty';
    }

    // The wireframe prints "Vodka · empty" for one recorded parent, so an unfilled slot is stated in place
    // rather than silently dropped from the list.
    return [...named, ...Array.from({ length: legacy.members.length - named.length }, () => 'empty')].join(' · ');
});

/**
 * The deck's own state word. A deck with empty slots is a legitimate choice — the run commits either way —
 * so it is a warning rather than a refusal, and it is stated once here rather than on every later screen
 * (`career-preflight.html:140-143`).
 */
const deckState = computed((): 'Pass' | 'Warning' | 'Missing' => {
    const deck = props.contract.deck;

    if (deck === null) {
        return 'Missing';
    }

    return deck.equipped === deck.slots.length ? 'Pass' : 'Warning';
});

/** The one word each check row carries, read off the row's own state rather than restated per branch. */
/**
 * R7-14: the wizard step each checklist row stands for.
 *
 * The rows are built in wizard order, and `SetupLayout` numbers the same steps from 1, so this is the
 * row's own position rather than a second vocabulary. It exists because `checkState` has to ask whether
 * THIS step was opened and whether it wrote anything, and the payload carries those as step numbers.
 */
const STEP_NUMBER: Record<string, number> = { scenario: 1, trainee: 2, target: 3, legacy: 4, deck: 5 };

function checkState(row: ChecklistRow): 'Pass' | 'Warning' | 'Missing' | 'Skipped' {
    // R7-14: `Your target Missing` sat over a summary that already read `Not entered` -- the same fact
    // twice, and `Missing` is the word this page also uses for a step the schema cannot satisfy. The
    // target step is optional (`BuildTarget.vue`'s forward door works without it), so a step the Trainer
    // opened and deliberately left empty is `Skipped`, not missing. The page already carries the
    // distinction: `visited` is what was opened, `saved` what wrote something.
    if (!row.entered) {
        // Scoped to the target row, which is the one `BuildTarget.vue` makes optional: its forward door
        // works without a target, so a visited-and-empty target step is a supported state. Legacy and
        // Deck are required for a usable run (`StartCareerRequest`), so an empty one there is a genuine
        // gap and keeps the word `Missing`.
        if (row.key === 'target') {
            const step = STEP_NUMBER[row.key];

            if (step !== undefined && props.visited.includes(step) && !props.saved.includes(step)) {
                return 'Skipped';
            }
        }

        return 'Missing';
    }

    if (row.key === 'deck') {
        return deckState.value;
    }

    if (row.key === 'legacy') {
        return legacyState.value;
    }

    return 'Pass';
}

/** The wireframe's career length, shipped by the controller from `TrainingRun::TURNS_PER_YEAR` ×
 *  `RaceCatalogSlot::YEAR_SENIOR` (plan v2 slice 3.6). The page no longer spells the number. */
const CAREER_TURNS = props.careerTurns;
</script>

<template>
    <SetupLayout :step="props.step" :visited="props.visited" :saved="props.saved">
        <Head title="Before the first turn" />
        <template #eyebrow>New career</template>
        <template #title>Before the first turn</template>
        <template #lede>
            One screen listing what will be created, what the run will be measured against, and the
            things worth knowing before you commit.
        </template>
        <!--
          The HFW's head-band pair (`career-preflight.html:61-65`). Both controls used to sit in a
          row inside the "This run" card, below a fieldset the Trainer had to reach by scrolling, so
          the screen's one write was under everything it commits.

          "Create the run" is this step's write and it stays a method call rather than a form submit:
          `PreflightController@store` is a PUT composed from the draft, and the button's disabled
          state reads `canStart`, which is the same gate the request re-checks.
        -->
        <template #actions>
            <Link
                :href="props.edit.deck"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:bg-panel"
            >
                Back to deck
            </Link>
            <!-- A disabled primary action with no stated reason is a dead end: the acknowledgement it
                 waits on sits most of a page further down, so a Trainer who reaches the footer finds a
                 dead button and no explanation (WCAG 3.3.1). The sentence names the control that
                 enables it. It is text, not a link, on purpose: the head band's contract is one back
                 door and one forward door (`wizard-actions.spec.ts`), and a third anchor here would
                 break that invariant. Gated on `canStart` so it does not compete with
                 `startBlocker`'s own sentence when a required step is missing. -->
            <p
                v-if="canStart && !acknowledged"
                data-ack-hint
                class="basis-full text-xs text-ink-muted sm:order-first sm:basis-auto sm:mr-auto"
            >
                Tick the confirmation below to enable Create the run.
            </p>
            <button
                type="button"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-primary-action bg-primary-action px-4 text-sm font-semibold text-on-primary-action hover:bg-primary-action/90 disabled:opacity-60"
                :disabled="!canStart || form.processing || !acknowledged"
                @click="start"
            >
                {{ form.processing ? 'Starting…' : 'Create the run' }}
            </button>
        </template>

        <!-- The refusal, when the server finds a section that no longer validates. Nothing below blocks;
             this does, and it names the section so the next action is the step that owns it. -->
        <div
            v-if="form.hasErrors"
            ref="alert"
            role="alert"
            tabindex="-1"
            class="mt-4 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
        >
            <p class="font-semibold">The career was not created.</p>
            <ul class="mt-1 list-disc pl-5">
                <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
            </ul>
        </div>

        <!-- `career-preflight.html:87-189`: a two-column shell, the run and its checks on the left, what the
             scenario asks and what is missing on the right. `g-side` is 2.1fr / 1fr above 820px and one
             column below it (shell.css:404-406), so the breakpoints are media queries and not container
             queries: the columns must follow the window, not the shell's content box. -->
        <div class="grid grid-cols-1 gap-3.5 min-[821px]:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]">
            <div class="space-y-3.5">
                <section class="card" aria-labelledby="run-heading">
                    <h2 id="run-heading">This run</h2>

                    <div class="mt-2.5 grid grid-cols-2 gap-2.5 min-[1081px]:grid-cols-4">
                        <div class="tile">
                            <p class="k">Trainee</p>
                            <p class="v">
                                <template v-if="props.contract.build.trainee">{{ props.contract.build.trainee.name }}</template>
                                <AbsenceValue v-else compact reason="No trainee has been chosen in this draft." />
                            </p>
                        </div>
                        <div class="tile">
                            <p class="k">Form</p>
                            <p class="v">
                                <template v-if="props.contract.build.trainee?.form_label">{{ props.contract.build.trainee.form_label }}</template>
                                <AbsenceValue
                                    v-else
                                    compact
                                    reason="No costume form was chosen in this draft, so none is named here."
                                />
                            </p>
                        </div>
                        <div class="tile">
                            <p class="k">Scenario</p>
                            <p class="v">
                                <template v-if="props.contract.build.scenario">{{ props.contract.build.scenario.label }}</template>
                                <AbsenceValue v-else compact reason="No scenario has been chosen in this draft." />
                            </p>
                        </div>
                        <div class="tile">
                            <p class="k">Distance</p>
                            <p class="v">
                                <template v-if="props.contract.build.target">{{ props.contract.build.target.distance }}</template>
                                <AbsenceValue v-else compact :reason="props.contract.target.reason ?? 'The target step has not been entered.'" />
                            </p>
                        </div>
                        <div class="tile">
                            <p class="k">Parents</p>
                            <p class="v">{{ parentsSummary }}</p>
                        </div>
                        <div class="tile">
                            <p class="k">Deck</p>
                            <p class="v">
                                <template v-if="props.contract.deck">
                                    {{ props.contract.deck.equipped }} of {{ props.contract.deck.slots.length }}
                                </template>
                                <AbsenceValue v-else compact reason="The deck step has not been entered." />
                            </p>
                        </div>
                        <div class="tile">
                            <p class="k">Target total</p>
                            <p class="v font-mono tabular-nums">
                                <template v-if="targetTotal !== null">{{ targetTotal.toLocaleString('en-US') }}</template>
                                <AbsenceValue v-else compact :reason="props.contract.target.reason ?? 'The target step has not been entered.'" />
                            </p>
                        </div>
                        <div class="tile">
                            <p class="k">Career length</p>
                            <p class="v font-mono tabular-nums">{{ CAREER_TURNS }} turns</p>
                        </div>
                    </div>

                    <!-- The wireframe states where the 72 comes from, because it is the one number on this
                         screen that is not read off the draft. The post-December finale block sits outside
                         the 24-turn grid and is deliberately not counted. -->
                    <p class="mt-2.5 text-xs text-ink-muted">
                        {{ CAREER_TURNS }} turns is three career years of the client's own 24-turn grid. The
                        post-December finale block sits outside that grid and is not counted.
                    </p>
                </section>

                <!-- The wireframe's Checks table (`career-preflight.html:108-144`), one row per step the
                     wizard has, so the state and the door back to the step that owns it are on the same line.
                     The ids survive from the list this replaced: they are the hooks the spec and the
                     screen's own deep links use. -->
                <section class="card" aria-labelledby="checks-heading">
                    <h2 id="checks-heading">Checks</h2>
                    <p role="status" class="mt-1 text-sm text-ink">{{ attention.sentence }}</p>
                    <!-- Walk-8 E-14: the count names steps needing attention while creation is allowed
                         anyway, and nothing said which gate actually blocks. A scenario and a trainee
                         are the whole refusal list (`canStart`), so the line fires only while that is
                         true and an attention row exists above it. -->
                    <p v-if="attention.tone === 'attention' && canStart" class="mt-1 text-xs text-ink-muted">
                        Only the scenario and the trainee block creation. Every other step above
                        records as absent if you start now.
                    </p>

                    <table class="mt-2.5 w-full text-left text-sm">
                        <caption class="sr-only">The five setup steps and whether each is recorded</caption>
                        <thead>
                            <tr class="border-b border-rule text-xs uppercase tracking-wide text-ink-muted">
                                <th scope="col" class="py-1.5 pr-2 font-semibold">Check</th>
                                <th scope="col" class="py-1.5 pr-2 font-semibold">State</th>
                                <th scope="col" class="py-1.5 font-semibold">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in rows"
                                :id="`preflight-step-${row.key}`"
                                :key="row.key"
                                tabindex="-1"
                                class="border-b border-rule last:border-0 align-top"
                            >
                                <th scope="row" class="py-2 pr-2 font-semibold text-ink-strong">{{ row.label }}</th>
                                <td class="py-2 pr-2">
                                    <!-- A pill states the word as text, never a colour alone (design-2.0 §17). -->
                                    <span
                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold"
                                        :class="checkState(row) === 'Pass'
                                            ? 'border-pick-line bg-raised text-ink'
                                            : checkState(row) === 'Warning'
                                                ? 'border-status-caution bg-raised text-ink'
                                                : checkState(row) === 'Skipped'
                                                  ? 'border-rule bg-raised text-ink-muted'
                                                  : 'border-risk bg-raised text-ink'"
                                    >{{ checkState(row) }}</span>
                                </td>
                                <td class="py-2 text-ink">
                                    <span>{{ row.summary }}</span>
                                    <!-- R7-54: a margin is not a separator. `ml-2` spaced the two names visually while the
                                     accessible name and any copied text read `Rice Showerライスシャワー` as one
                                     word. The parens belong to the markup, not to the value, so a `<span>`
                                     around the pair keeps them together in the a11y tree. -->
                                <span v-if="row.ja" class="text-ink-muted">
                                    (<span lang="ja">{{ row.ja }}</span>)
                                </span>
                                    <!-- Exactly one fix link, on the row that is the one warning. A link on
                                         every row would be five ways to leave a screen whose point is the
                                         single thing left to do. -->
                                    <span v-if="checkState(row) === 'Warning'" aria-hidden="true" class="text-ink-muted"> · </span>
                                    <Link
                                        v-if="checkState(row) === 'Warning'"
                                        :href="row.edit"
                                        :aria-label="`Fix ${row.label}`"
                                        class="inline-flex min-h-11 items-center text-sm font-medium text-ink underline"
                                    >
                                        Fix this
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- The wireframe's own note inside the Checks card (`career-preflight.html:146`):
                         a warning is a stated fact, not a block, and it is printed once. -->
                    <p v-if="rows.some((row) => checkState(row) === 'Warning')" class="note mt-2.5">
                        <b>A warning is not a block.</b> The one warning here is a deck with empty slots,
                        which is a legitimate choice: the run commits either way and the warning is stated
                        once, not repeated on every screen.
                    </p>
                </section>
            </div>

            <div class="space-y-3.5">
                <!-- What the run will be measured against. Every row is a fact the draft or the ruleset
                     holds; a row this tool cannot answer is absent rather than invented. -->
                <section class="card" aria-labelledby="asks-heading">
                    <h2 id="asks-heading">What this scenario asks</h2>
                    <table class="mt-2.5 w-full text-left text-sm">
                        <caption class="sr-only">The target this run will be measured against</caption>
                        <tbody>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Scenario</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.scenario">{{ props.contract.build.scenario.label }}</template>
                                    <AbsenceValue v-else compact reason="No scenario has been chosen in this draft." />
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Focus</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.scenario?.focus">{{ props.contract.build.scenario.focus }}</template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        :reason="props.contract.build.scenario?.focus_reason ?? 'The scenario record carries no focus sentence.'"
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Purpose</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.target">{{ props.contract.build.target.purpose_label }}</template>
                                    <AbsenceValue v-else compact :reason="props.contract.target.reason ?? 'The target step has not been entered.'" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Course</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.target">
                                        {{ props.contract.build.target.surface }} {{ props.contract.build.target.distance }} ·
                                        {{ props.contract.build.target.style }}
                                    </template>
                                    <AbsenceValue v-else compact :reason="props.contract.target.reason ?? 'The target step has not been entered.'" />
                                </td>
                            </tr>
                            <!--
                                The four RULES the wireframe asks for (`career-preflight.html:152-155`).

                                These are not facts about the draft, which is why the four rows above print
                                the draft's Scenario/Focus/Purpose/Course and these print the scenario's own
                                configuration. Each is read by `PreflightController::scenarioSection()` from
                                `config/scenarios.php` and travels as `contract.build.scenario.asks`, so the
                                screen and the configuration cannot disagree.

                                A scenario that states none of them prints its absence with a reason rather
                                than an empty cell, which is the same rule every other row here follows.
                            -->
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Facility levels</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="asks?.facility_level_source === 'repetition'">
                                        Rise by repetition: four uses per level, capped at Lv5.
                                    </template>
                                    <template v-else-if="asks?.facility_level_source === 'team_rank'">
                                        Rise with the team rank, not by repeated training.
                                    </template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        reason="This scenario's record states no facility-level rule."
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Fan gates</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="asks && asks.fan_gates.length > 0">
                                        <span v-for="(gate, index) in asks.fan_gates" :key="gate.label">
                                            <template v-if="index > 0">; </template>
                                            {{ gate.label }}<template v-if="gate.turf !== null">
                                                {{ gate.turf.toLocaleString() }} turf</template><template
                                                v-if="gate.dirt !== null">, {{ gate.dirt.toLocaleString() }} dirt</template>
                                        </span>
                                    </template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        reason="This scenario's record states no fan gates."
                                    />
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Happy Meek</th>
                                <td class="py-1.5 text-ink">
                                    <!-- The duel is URA Finale's own system (`UMAMUSUME_REFERENCE.md` §2.2.1,
                                         GameTora/Game8/umamusu.wiki; the config keeps `duel` only under
                                         `ura_finale`). The row reads `asks.duel` from the payload rather
                                         than branching on a scenario key, so the figures printed here are
                                         the config's and cannot drift from it again — they did: this cell
                                         said "+1 cap tier" while the config said "one tier", and the
                                         2026-11-12 reference correction established the real figure is
                                         +4 points. A scenario declaring no duel states the absence rather
                                         than a rule that does not apply. -->
                                    <template v-if="asks?.duel">
                                        Can appear on any stat-training turn.
                                        <template v-if="asks.duel.win_sp !== null">Win pays {{ asks.duel.win_sp }} SP</template><template v-if="asks.duel.cap_points !== null"> and +{{ asks.duel.cap_points }} on the dueled stat's cap</template>;<template v-if="asks.duel.loss_sp !== null"> lose pays {{ asks.duel.loss_sp }} SP</template>.
                                    </template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        reason="This scenario's record has no duel system."
                                    />
                                </td>
                            </tr>
                            <tr
                                v-if="props.contract.build.scenario?.variant"
                                class="border-b border-rule"
                            >
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Result Point track</th>
                                <td class="py-1.5 text-ink">
                                    <!-- Ruling F1: the track is selected from the draft trainee's aptitudes, by
                                         the same `TrackblazerVariant` owner the Cockpit reads, so the two
                                         screens cannot disagree. An ambiguous reading prints that word and its
                                         two tying aptitudes rather than letting "standard" stand as a
                                         classification. -->
                                    <p class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                        <span>{{ preflightTrackLabel }}</span>
                                        <span
                                            v-if="props.contract.build.scenario.variant.classification === 'ambiguous'"
                                            class="pill pill-caution"
                                        >ambiguous</span>
                                        <span
                                            v-else-if="props.contract.build.scenario.variant.classification === null"
                                            class="pill pill-caution"
                                        >not classified</span>
                                    </p>
                                    <p class="mt-1 text-xs text-ink-muted">
                                        {{ props.contract.build.scenario.variant.reason }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Finale targets</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="finaleTargetLine !== null">{{ finaleTargetLine }}</template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        reason="This scenario's record states no finale target."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Product gaps, each naming what is missing and what the screen does instead. None says
                     "not built": that is developer language in a user interface
                     (`career-preflight.html:160-174`). -->
                <section class="card" aria-labelledby="gaps-heading">
                    <h2 id="gaps-heading">What is not built yet</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-ink-muted">
                        <li>Per-discipline facility training counts are not recorded, so no facility level can be shown.</li>
                        <li>
                            This build does not record a Global ruleset version, so the version is stated as an absence
                            beside the ruleset it would qualify.
                        </li>
                        <li>
                            A deck position's owned-or-rented flag is stored per position, and a position holding
                            no card creates no deck row.
                        </li>
                    </ul>
                    <p class="mt-2.5 text-xs text-ink-muted">
                        Ruleset version:
                        <AbsenceValue compact :reason="props.contract.ruleset.title" />
                    </p>
                </section>

                <!--
                  Independent Training (`UMAMUSUME_REFERENCE.md` §1.1.7), restored by S3.1 of the master
                  roadmap.

                  The mode switch and the policy radios were cut in Phase E2 because the wireframe draws
                  only the Commit card beside them, and that cut is what left the mode unreachable:
                  `Lifecycle::start()` has been called from `store()` the whole time and
                  `StartCareerRequest` has validated the three fields, yet no control ever set
                  `independent`, so `independent_training_runs` held 0 rows and only a seeder or a test
                  could start the mode.

                  It sits in its own card rather than inside Commit for two measured reasons: the Commit
                  card is held to a 25-descendant and 900-character ceiling by
                  `career-preflight.spec.ts` ("the commit card stays a card, not a document"), and
                  putting three controls and their help text inside it would break both. The card is a
                  closed disclosure, so the page still reads as the wireframe draws it and the Commit
                  card remains the only place the write happens.

                  Rotation is deliberately not offered: `Lifecycle::start()` reads a null rotation as the
                  scenario's own target races, the documented default in `ADR-0032`, and a second
                  race-editing surface here would duplicate the Race Planner.
                -->
                <section class="card" aria-labelledby="independent-training-heading">
                    <h2 id="independent-training-heading">Independent Training</h2>
                    <details data-independent-training>
                        <summary class="flex min-h-11 cursor-pointer items-center text-sm font-medium text-ink">
                            Start this run as an Independent Training session
                        </summary>
                        <p class="mt-1 text-xs text-ink-muted">
                            The session plays itself out over {{ 50 }} minutes with the app closed. One
                            session runs at a time, and the run's Result screen reports what it earned.
                        </p>

                        <label class="mt-2 flex items-start gap-2 text-sm">
                            <input
                                id="independent"
                                v-model="independent"
                                type="checkbox"
                                class="mt-0.5"
                            />
                            <span>Run this career as an idle session</span>
                        </label>

                        <template v-if="independent">
                            <div class="mt-2">
                                <label for="policy" class="text-xs font-medium text-ink">Training policy</label>
                                <select
                                    id="policy"
                                    v-model="policy"
                                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring"
                                >
                                    <option v-for="option in props.independent.policies" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <p class="mt-1 text-xs text-ink-muted">
                                    Balanced is the recommended policy: the other two skew stats and cost win rate.
                                </p>
                            </div>

                            <div class="mt-2">
                                <label for="priority-skills" class="text-xs font-medium text-ink">
                                    Priority skills ({{ prioritySkills.length }} of {{ props.independent.maxPrioritySkills }})
                                </label>
                                <select
                                    id="priority-skills"
                                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:ring"
                                    :disabled="prioritySkills.length >= props.independent.maxPrioritySkills"
                                    @change="addPrioritySkill"
                                >
                                    <option value="">Add a skill…</option>
                                    <option
                                        v-for="skill in props.independent.skills"
                                        :key="skill.id"
                                        :value="String(skill.id)"
                                        :disabled="prioritySkills.includes(skill.id)"
                                    >
                                        {{ skill.name }}
                                    </option>
                                </select>
                                <ul v-if="prioritySkills.length > 0" class="mt-2 space-y-1">
                                    <li
                                        v-for="skill in prioritySkillRows"
                                        :key="skill.id"
                                        class="flex items-center justify-between gap-2 text-sm"
                                    >
                                        <span>{{ skill.name }}</span>
                                        <button
                                            type="button"
                                            class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-xs font-semibold text-ink hover:bg-panel"
                                            :aria-label="`Remove ${skill.name}`"
                                            @click="removePrioritySkill(skill.id)"
                                        >
                                            Remove
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <p v-if="form.errors.independent" class="mt-1 text-xs text-risk">{{ form.errors.independent }}</p>
                            <p v-if="form.errors.policy" class="mt-1 text-xs text-risk">{{ form.errors.policy }}</p>
                            <p v-if="form.errors.priority_skills" class="mt-1 text-xs text-risk">
                                {{ form.errors.priority_skills }}
                            </p>
                        </template>
                    
                    </details>
                </section>

                <!-- The commitment itself. `Start career` is the page's only filled action, and it is honest
                     about what it needs: a scenario and a trainee, which is exactly what
                     `StartCareerRequest` refuses without. -->
                <section class="card" aria-labelledby="commit-heading">
                    <h2 id="commit-heading">Commit</h2>
                    <p class="mt-2 text-xs text-ink-muted">
                        Creating the run writes the trainee, scenario, target, parents and deck. Turn 1 is
                        recorded from the cockpit, not here, so nothing is marked played until a turn is
                        actually logged.
                    </p>
                    <p v-if="startBlocker !== null" class="mt-2 text-sm text-ink">{{ startBlocker }}</p>

                    <label class="mt-3 flex items-start gap-2 text-sm">
                        <input
                            v-model="acknowledged"
                            type="checkbox"
                            id="confirmed"
                            class="mt-0.5"
                        />
                        <span>I understand this creates an Active run that cannot be un-created; retirement is the only exit.</span>
                    </label>

                    <p v-if="!acknowledged" role="status" class="mt-2 text-sm text-ink-muted">
                        Acknowledge the above to enable "Create the run".
                    </p>

                    <!--
                      The three Independent Training inputs (`UMAMUSUME_REFERENCE.md` §1.1.7).

                      The mode switch and the policy radios were cut in Phase E2 because the wireframe
                      draws only the Commit card here, and that cut is what left the mode unreachable:
                      `Lifecycle::start()` has been called from `store()` the whole time, and
                      `StartCareerRequest` has validated the three fields, but no control ever set
                      `independent`. A Trainer could not start the mode; only a seeder or a test could.
                      Restored here as a disclosure inside the Commit card, so the wireframe's one
                      filled action stays the page's one filled action (`wizard-actions.spec.ts`) and
                      nothing new competes with it.

                      Rotation is deliberately not offered: `Lifecycle::start()` reads a null rotation as
                      the scenario's own target races, which is the documented default (`ADR-0032`), and
                      a second race-editing surface on this screen would duplicate the Race Planner.
                    -->
                    
                </section>
            </div>
        </div>
    </SetupLayout>
</template>