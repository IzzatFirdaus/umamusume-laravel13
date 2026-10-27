<script setup lang="ts">
/*
 * SCREEN-005, Build Target (`ADR-0020` §1; `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` SCREEN-005). Step 3 of
 * the setup wizard, headed "Your target".
 *
 * **The page prints props and assembles nothing the server did not send.** The purpose options arrive
 * labelled through `uma.build_purpose`, the option vocabularies arrive as `BuildTargetPayload`'s own
 * constants, the stat matrix arrives as `statOrder`, and the caps arrive pre-computed by
 * `ScenarioCaps::forRun()` — the same call the write validates against (`ADR-0015`). That is why this
 * file names no stat, no band, no surface, no style and no scenario: `CareerBuildTargetTest` greps for
 * exactly that.
 *
 * **The server is the authority on the clamp.** The `max` attribute mirrors the cap as a convenience
 * for the browser; the write is refused server-side with the bound named when a number is above it,
 * and the refusal renders beside the input that caused it with focus moved to it.
 *
 * **The bar is not a readiness verdict.** Whether a target is reachable in the turns left is held
 * computation in this repository, so the bar is the entered number against the config cap and nothing
 * more, and it never renders without its number printed beside it.
 *
 * **The summary is the only derived text on the page** and it is built from the entered values alone,
 * which is what the badge beside it states. An empty field is named as an absence in the sentence
 * rather than skipped, so a reader is never told less than the form knows.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import SubCell from '../../components/SubCell.vue';
import { BaseSelect, BaseInput, BaseButton } from '../../components/Form';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

interface SelectOption {
    value: string;
    label: string;
}

interface StoredTarget {
    purpose: string;
    distance: string;
    surface: string;
    style: string;
    targets: Record<string, number>;
    skill_priorities: string[];
}

const props = defineProps<{
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    choices: { label: string; value: string | null; url: string | null }[];
    /** The stored payload as entered, or null before one exists. */
    target: StoredTarget | null;
    /** The Trainer's stored stat-target defaults (SCREEN-024), applied only where no target is stored. */
    statDefaults: Record<string, number> | null;
    /**
     * One ceiling per stat, from `ScenarioCaps::forRun` — the entered ceiling where the draft carries
     * one, the base cap alone when no scenario is chosen yet. This is the bound the write enforces.
     */
    caps: Record<string, number>;
    /** The same stats' published ceiling from the matrix, entered figures aside (`ADR-0025`). */
    scenarioCaps: Record<string, number>;
    /** The ceilings already entered, keyed by the columns the run holds them in; absent where none was. */
    enteredCaps: Record<string, number | null>;
    /** The stats that may carry an entered ceiling. Four, and the server's list decides which. */
    capStats: string[];
    /** The engine ceiling no entered figure may pass; the request enforces it, not this attribute. */
    hardCap: number;
    statOrder: string[];
    purposeOptions: SelectOption[];
    distanceBands: string[];
    surfaces: string[];
    styles: string[];
    scenarioLabel: string | null;
    scenarioPending: boolean;
    /** `route('career.target.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
    /**
     * The scenario stanza's own stamina floors, from `config/scenarios.php` via the controller. Read
     * rather than restated, so a published figure has one home.
     */
    finaleTargets: { staminaByDistance: Record<string, number> };
}>();

/**
 * One stored ceiling as a field value. Nothing stated is an empty field rather than a zero: the request
 * reads an empty field as "no ceiling stated, keep the scenario's own" (`ADR-0025`, D-220).
 */
const ceilingField = (column: string): string => {
    const value = props.enteredCaps[column];

    return value === null || value === undefined ? '' : String(value);
};

/**
 * The four `training_runs` columns that hold an entered ceiling. These are the request's own keys, not
 * displayed vocabulary: every stat *label* on this page still arrives through `statOrder` or `capStats`,
 * which is the claim the header above makes and `CareerBuildTargetTest` greps for.
 */
const capColumns = ['cap_speed', 'cap_stamina', 'cap_power', 'cap_guts'] as const;
type CapColumn = (typeof capColumns)[number];

/** The column that holds one stat's entered ceiling. `capStats` carries exactly these four stats. */
const capColumn = (stat: string): CapColumn => `cap_${stat.toLowerCase()}` as CapColumn;

const form = useForm<{
    purpose: string;
    distance: string;
    surface: string;
    style: string;
    targets: Record<string, number | string>;
    skill_priorities: string[];
    cap_speed: string;
    cap_stamina: string;
    cap_power: string;
    cap_guts: string;
}>({
    purpose: props.target?.purpose ?? '',
    distance: props.target?.distance ?? '',
    surface: props.target?.surface ?? '',
    style: props.target?.style ?? '',
    // No stored value is an empty field, never a pre-filled zero: a zero is a number the Trainer
    // did not enter, and the payload's required rule asks for every stat by name. The Trainer's
    // stored stat-target defaults (SCREEN-024) fill the gap only where no target is stored, so a
    // career's own target is never overwritten by a preference.
    targets: Object.fromEntries(
        props.statOrder.map((stat) => [
            stat,
            props.target?.targets?.[stat] ?? props.statDefaults?.[stat] ?? '',
        ]),
    ),
    skill_priorities: props.target?.skill_priorities ? [...props.target.skill_priorities] : [],
    // An unset ceiling is an empty field rather than a number: the request reads an absent value as "no
    // ceiling stated, keep the scenario's", and a zero here would be a reading nobody took (D-220).
    cap_speed: ceilingField('cap_speed'),
    cap_stamina: ceilingField('cap_stamina'),
    cap_power: ceilingField('cap_power'),
    cap_guts: ceilingField('cap_guts'),
});

const newSkill = ref('');

const errors = computed<Record<string, string | undefined>>(
    () => form.errors as Record<string, string | undefined>,
);

// The DOM order of the focusable fields, so a failed submit focuses the first control the server
// refused rather than the first error key the response happens to carry.
const fieldOrder = computed<string[]>(() => [
    'purpose',
    'distance',
    'surface',
    'style',
    ...props.statOrder.map((stat) => `targets.${stat}`),
    ...capColumns,
    'skill_priorities',
]);

const controlId = (key: string): string => key.replace(/\./g, '_');
const errorId = (key: string): string => `error-${key.replace(/\./g, '_')}`;

// A key belongs to a field when it is that field or sits under it: each stat's error key lives under
// the `targets` map, and each skill-priority row key under `skill_priorities`.
const errorOf = (key: string): string | undefined => {
    const exact = errors.value[key];

    if (exact !== undefined) {
        return exact;
    }

    const nested = Object.keys(errors.value).find((candidate) => candidate.startsWith(`${key}.`));

    return nested === undefined ? undefined : errors.value[nested];
};

const describedBy = (key: string): string | undefined => (errorOf(key) === undefined ? undefined : errorId(key));

function submit(): void {
    // The write URL is supplied by the server (`career.target.store`) so the page never states it as
    // a literal. There is no Ziggy dependency here.
    form.put(props.action, {
        preserveScroll: true,
        onError: () => {
            const first = fieldOrder.value.find(
                (key) => errors.value[key] !== undefined || errorOf(key) !== undefined,
            );

            // A refusal against the whole `targets` map (a key outside the matrix) has no control of
            // its own, so it lands on the first stat input.
            const target = first
                ?? (errors.value.targets === undefined || props.statOrder.length === 0
                    ? undefined
                    : `targets.${props.statOrder[0]}`);

            if (target !== undefined) {
                nextTick(() => {
                    document.getElementById(controlId(target))?.focus();
                });
            }
        },
    });
}

const cap = (stat: string): number => props.caps[stat] ?? 0;

const entered = (stat: string): number | null => {
    const value = form.targets[stat];

    return typeof value === 'number' && Number.isFinite(value) ? value : null;
};

const percent = (stat: string): number => {
    const value = entered(stat);
    const ceiling = cap(stat);

    if (value === null || ceiling <= 0) {
        return 0;
    }

    return Math.min(100, Math.max(0, Math.round((value / ceiling) * 100)));
};

const enteredLabel = (stat: string): string => {
    const value = entered(stat);

    return value === null ? 'N/A' : `${value} of ${cap(stat)}`;
};

/** The sum of the five entered targets, or null while any one of them is still empty. */
const totalTarget = computed((): number | null => {
    const values = props.statOrder.map((stat) => entered(stat));

    return values.some((value) => value === null) ? null : (values as number[]).reduce((sum, value) => sum + value, 0);
});

/** The sum of the five caps this run is bounded by: the denominator the total is read against. */
const totalCap = computed((): number => props.statOrder.reduce((sum, stat) => sum + cap(stat), 0));


const capTitle = (stat: string): string => {    if (props.scenarioPending) {
        return 'No scenario is chosen yet, so this is the base cap with no scenario bonus.';
    }

    // An entered ceiling outranks the matrix, and the two figures are different claims: one is what the
    // Trainer read off the client, the other what the scenario publishes (`ADR-0025`).
    return props.enteredCaps[capColumn(stat)] === undefined || props.enteredCaps[capColumn(stat)] === null
        ? 'The chosen scenario’s ceiling for this stat, the same number the write enforces.'
        : `The ceiling entered for this run, the same number the write enforces. The scenario itself publishes ${props.scenarioCaps[stat]}.`;
};

const optionLabel = (options: SelectOption[], value: string): string | null =>
    options.find((option) => option.value === value)?.label ?? null;

/**
 * The published figure under each ceiling field. A ceiling the Trainer enters replaces it for this run
 * and nothing here recomputes it (`ADR-0025`).
 */
const scenarioTitle = computed((): string =>
    props.scenarioPending
        ? 'The base cap with no scenario bonus, because no scenario is chosen yet. `config/scenarios.php` is the source.'
        : 'What this scenario publishes in `config/scenarios.php`. An entered ceiling replaces it for this run.',
);

/** One sentence from the entered values alone; an empty field is named, never omitted. */
const summary = computed((): string => {
    const clauses: string[] = [];

    const purpose = optionLabel(props.purposeOptions, form.purpose);
    clauses.push(purpose === null ? 'Purpose not chosen yet' : `Purpose ${purpose}`);

    clauses.push(form.distance === '' ? 'distance not chosen yet' : `distance ${form.distance}`);
    clauses.push(form.surface === '' ? 'surface not chosen yet' : `surface ${form.surface}`);
    clauses.push(form.style === '' ? 'style not chosen yet' : `style ${form.style}`);

    const stats = props.statOrder
        .map((stat) => {
            const value = entered(stat);
            return `${stat} ${value === null ? 'not entered' : value}`;
        })
        .join(', ');
    clauses.push(stats);

    clauses.push(
        form.skill_priorities.length === 0
            ? 'skills in order: none listed yet'
            : `skills in order: ${form.skill_priorities.join(', ')}`,
    );

    return `${clauses.join('; ')}.`;
});

/**
 * Fills the five stat fields from the Trainer's stored defaults. Only ever called from the explicit
 * button, so a preference never overwrites a career's own target on load.
 */
function applyDefaults(): void {
    const defaults = props.statDefaults;

    if (defaults === null) {
        return;
    }

    props.statOrder.forEach((stat) => {
        const value = defaults[stat];

        if (typeof value === 'number') {
            form.targets[stat] = value;
        }
    });
}

function addSkill(): void {    const name = newSkill.value.trim();

    if (name === '') {
        return;
    }

    form.skill_priorities.push(name);
    newSkill.value = '';
}

function move(index: number, delta: number): void {
    const target = index + delta;

    if (target < 0 || target >= form.skill_priorities.length) {
        return;
    }

    const [name] = form.skill_priorities.splice(index, 1);
    form.skill_priorities.splice(target, 0, name);
}

function removeAt(index: number): void {
    form.skill_priorities.splice(index, 1);
}
</script>

<template>
    <SetupLayout :step="3" :visited="props.visited" :saved="props.saved" :choices="props.choices">
        <Head title="Your target" />
        <template #eyebrow>New career</template>
        <template #title>Your target</template>

        <!-- No breadcrumb: "← Back to target" pointed at this same URL, a control that navigates to the
             page it is on. The step bar above lists all six steps and the carried-choices strip states
             the scenario and the trainee, so nothing is lost. Steps 1 and 2 carry the same note. -->

        <!-- The wireframe's labelled-defect pattern, kept in its shape. It states the failure the step
             exists to prevent rather than describing the implementation: a career whose target was never
             entered reads as a career with no goal at the moment it is committed. -->
        <div class="warn" data-field="target-defect">
            <b>This is the step the current build skips.</b>
            A run created with no target entered commits with five empty goals, and the cockpit then has
            nothing to measure a turn against. Filling these in is what makes the run's progress answerable.
        </div>

        <p class="max-w-3xl text-sm text-ink-muted">
            The target is what this career is built to reach. It is entered as one object and replaced
            as one object, and it lives in this setup draft until Preflight creates the run.
        </p>

        <section aria-labelledby="target-summary-heading" class="mt-3 rounded-lg border border-rule bg-panel p-4">
            <h2 id="target-summary-heading" class="text-lg font-semibold text-ink-strong">Summary</h2>
            <p class="mt-2 text-sm text-ink">
                {{ summary }}
                <ProvenanceBadge
                    state="calculated"
                    title="assembled by this tool from the values entered on this form, not a stored fact"
                />
            </p>
        </section>

        <p class="mt-3 text-sm text-ink">
            Scenario:
            <strong v-if="props.scenarioLabel" class="text-ink-strong">{{ props.scenarioLabel }}</strong>
            <AbsenceValue
                v-else
                compact
                reason="No scenario has been chosen in this setup draft yet, so every cap below is the base cap with no scenario bonus."
            />
            <Link
                href="/career/setup/trainee"
                class="ml-3 inline-flex min-h-11 items-center rounded-md px-2 text-sm text-ink underline"
            >
                Back: Trainee
            </Link>
            <Link
                href="/career/setup/scenario"
                class="ml-3 inline-flex min-h-11 items-center rounded-md px-2 text-sm text-ink underline"
            >
                Change scenario
            </Link>
        </p>

        <p
            v-if="props.target === null"
            class="mt-3 text-sm text-ink-muted"
        >
            No target is recorded yet. The fields below start empty rather than at zero.
        </p>

        <!-- `novalidate` on purpose: the `max` attribute mirrors the cap for the browser only, and
             native constraint validation would block the submit with a browser bubble instead of
             letting the server refuse with its named bound, which is the message this page renders
             with its own `aria-describedby` and focus. -->
        <form class="mt-5 mx-auto max-w-4xl space-y-6" novalidate @submit.prevent="submit">
            <!--
              The wireframe's two-column canvas. `.g-side` is `minmax(0,2.1fr) minmax(0,1fr)` and it
              collapses below 821px (frontend-v2-wireframes/assets/shell.css:404,406), so both the
              breakpoint and the proportions are the wireframe's own rather than a separate measure:
              the same reason the card grid keeps `min-[821px]:`. The blocks sit at their existing
              indentation so the move reads as two wrapper lines rather than a re-indent of the form.
            -->
            <div class="grid gap-6 min-[821px]:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]">
            <div class="space-y-6">
            <fieldset data-region="card" class="rounded-lg border border-rule bg-panel p-4">
                <legend class="px-1 text-lg font-semibold text-ink-strong">What the career is for</legend>

                <div class="grid gap-4 min-[821px]:grid-cols-2">
                    <BaseSelect
                        v-model="form.purpose"
                        :id="controlId('purpose')"
                        name="purpose"
                        label="Purpose"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.purposeOptions]"
                        :aria-describedby="describedBy('purpose')"
                    />
                    <BaseSelect
                        v-model="form.distance"
                        :id="controlId('distance')"
                        name="distance"
                        label="Distance"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.distanceBands.map(b => ({ value: b, label: b }))]"
                        :aria-describedby="describedBy('distance')"
                    />
                    <BaseSelect
                        v-model="form.surface"
                        :id="controlId('surface')"
                        name="surface"
                        label="Surface"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.surfaces.map(s => ({ value: s, label: s }))]"
                        :aria-describedby="describedBy('surface')"
                    />
                    <BaseSelect
                        v-model="form.style"
                        :id="controlId('style')"
                        name="style"
                        label="Style"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.styles.map(s => ({ value: s, label: s }))]"
                        :aria-describedby="describedBy('style')"
                    />
                </div>

                <p
                    v-if="errorOf('purpose')"
                    :id="errorId('purpose')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errorOf('purpose') }}</p>
                <p
                    v-if="errorOf('distance')"
                    :id="errorId('distance')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errorOf('distance') }}</p>
                <p
                    v-if="errorOf('surface')"
                    :id="errorId('surface')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errorOf('surface') }}</p>
                <p
                    v-if="errorOf('style')"
                    :id="errorId('style')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errorOf('style') }}</p>

                <!-- The design brief names six purposes and the stored enum holds four. The gap
                     is stated rather than filled with an option the write would refuse. -->
            </fieldset>

            <fieldset data-region="card" class="rounded-lg border border-rule bg-panel p-4">
                <legend class="px-1 text-lg font-semibold text-ink-strong">Stat targets & ceilings</legend>

                <p class="text-sm text-ink-muted">
                    Target is the number you aim for this run. Ceiling is the highest the client shows;
                    leave empty to keep the scenario's published figure.
                </p>

                <div class="mt-4 space-y-4">
                    <!-- Each stat is one `.field`: the wireframe puts the value, its denominator and the share of
                             the cap in a single labelled group, so the cap is read with the number rather
                             than matched up from a column header elsewhere. `data-region` rather than the
                             corpus' `.field` class because `.field` is unlayered in app.css and would win
                             over the utilities this row already carries. -->
                        <div v-for="stat in props.statOrder" :key="stat" :data-stat="stat" data-region="field" class="grid gap-3 min-[821px]:grid-cols-2">
                        <!-- Target column -->
                        <div class="space-y-1">
                            <BaseInput
                                v-model.number="form.targets[stat]"
                                :id="controlId(`targets.${stat}`)"
                                type="number"
                                :name="`targets[${stat}]`"
                                :aria-label="stat"
                                :min="0"
                                :max="cap(stat)"
                                inputmode="numeric"
                                :aria-describedby="describedBy(`targets.${stat}`)"
                                :label="stat"
                                value-class="font-mono text-ink tabular-nums"
                                :error="errorOf(`targets.${stat}`)"
                            />
                            <!-- The denominator sits inside the field's own group: the value and the cap it
                                 is read against are one claim, so they print together rather than in a
                                 column header the Trainer has to match up. -->
                            <span class="font-mono text-xs text-ink-muted tabular-nums" data-field="denominator">
                                {{ entered(stat) ?? '—' }} / {{ cap(stat) }} cap
                            </span>
                            <span class="text-xs text-ink-muted" data-field="percent-of-cap">
                                <template v-if="entered(stat) !== null">{{ percent(stat) }}% of cap</template>
                                <template v-else>not entered, so no share of the cap is claimed</template>
                            </span>
                            <span class="text-xs text-ink-muted" :title="capTitle(stat)">Cap {{ cap(stat) }}</span>

                            <span class="flex flex-wrap items-center gap-2">
                                <span v-if="entered(stat) !== null" class="flex flex-wrap items-center gap-2">
                                    <span class="h-2 w-36 overflow-hidden rounded-md bg-sunken" aria-hidden="true">
                                        <span
                                            class="block h-full bg-chrome"
                                            :data-fill="percent(stat)"
                                            :style="{ width: `${percent(stat)}%` }"
                                        ></span>
                                    </span>
                                    <span class="font-mono text-xs text-ink tabular-nums">{{ enteredLabel(stat) }}</span>
                                </span>
                                <AbsenceValue
                                    v-else
                                    compact
                                    class="font-mono"
                                    reason="No target entered for this stat yet."
                                />
                            </span>
                        </div>

                        <!-- Ceiling column (only for stats that have an enterable ceiling) -->
                        <div v-if="props.capStats.includes(stat)" class="space-y-1">
                            <BaseInput
                                v-model="form[capColumn(stat)]"
                                :id="controlId(capColumn(stat))"
                                type="number"
                                :name="capColumn(stat)"
                                :aria-label="`${stat} ceiling`"
                                :min="1"
                                :max="props.hardCap"
                                inputmode="numeric"
                                :aria-describedby="describedBy(capColumn(stat))"
                                :label="`${stat} ceiling`"
                                value-class="font-mono text-ink tabular-nums"
                                class="w-full"
                                :error="errorOf(capColumn(stat))"
                            />
                            <span class="text-xs text-ink-muted" :title="scenarioTitle">
                                Scenario publishes {{ props.scenarioCaps[stat] }}
                            </span>
                        </div>
                        <!-- Placeholder for stats without ceiling (the fifth stat) to keep grid alignment -->
                        <div v-else class="space-y-1">
                            <span class="text-xs text-ink-muted">No enterable ceiling for this stat</span>
                        </div>
                    </div>
                </div>

                <p
                    v-if="errors.targets"
                    :id="errorId('targets')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errors.targets }}</p>
            </fieldset>

            <!-- The totals block: what the entered figures add up to, beside the one published build the
                 page names and the ceiling the five caps sum to. Three different claims, three rows, and
                 the note says which one is a requirement (none of them is). -->
            <section data-region="card" class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="target-totals">
                <h2 id="target-totals" class="text-lg font-semibold text-ink-strong">Totals</h2>

                <div class="mt-2" data-field="target-totals">
                    <div class="srow">
                        <span class="k">Your total</span>
                        <span class="v font-mono tabular-nums">{{ totalTarget === null ? '—' : totalTarget }}</span>
                    </div>
                    <div class="srow">
                        <span class="k">Published Global sample build <em>Special Week</em></span>
                        <span class="v font-mono tabular-nums">—</span>
                    </div>
                    <div class="srow">
                        <span class="k">Sum of the five scenario caps</span>
                        <span class="v font-mono tabular-nums">{{ totalCap }}</span>
                    </div>
                </div>

                <p class="note mt-2">
                    The published Global sample is one publisher's build, not a requirement and not this
                    tool's recommendation: it is a figure to compare against, and a career below it is not
                    a worse career. This repository stores no sample-build column, so the row prints a dash
                    rather than a number it cannot source.
                </p>
            </section>

            </div>

            <!-- The right column of the wireframe's `.g-side`: Distance, then Stamina floor. The 1400 m
                 row is the wireframe's `.note` inside the Distance card (career-target.html:159), not a
                 warning band: the page states a source disagreement rather than refusing a submit. -->
            <div class="space-y-6">
            <section data-region="card" class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="distance-bands">
                <h2 id="distance-bands" class="text-lg font-semibold text-ink-strong">Distance bands</h2>

                <ul class="mt-2 flex flex-wrap gap-1.5 text-xs">
                    <li v-for="band in props.distanceBands" :key="band" class="pill pill-outline">{{ band }}</li>
                </ul>

                <p class="note mt-2" data-field="distance-conflict">
                    <b>1400 m is disputed.</b>
                    Game8 files it as {{ props.distanceBands[0] }}, the client's export code 2 reads as
                    {{ props.distanceBands[1] }}, and the client's own skills gate on
                    <code>distance_type == 2</code>. This tool states the disagreement rather than picking a
                    side, so a 1400 m race can be typed either way as long as the skill gate it is planned
                    against uses the same reading.
                </p>
            </section>

            <!-- The stamina floor per band, read from the scenario's own stanza rather than restated. -->
            <section data-region="card" class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="distance-floor">
                <h2 id="distance-floor" class="text-lg font-semibold text-ink-strong">Floor per distance band</h2>

                <table v-if="Object.keys(props.finaleTargets.staminaByDistance).length > 0" class="mt-2 w-full text-sm">
                    <thead>
                        <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                            <th class="py-2 pr-3">Band</th>
                            <th>Floor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(floor, band) in props.finaleTargets.staminaByDistance" :key="band" class="border-b border-rule">
                            <td class="py-2 pr-3 text-ink">{{ band }}</td>
                            <td class="font-mono text-ink tabular-nums">{{ floor }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="mt-2 text-sm text-ink-muted">
                    <AbsenceValue compact reason="The chosen scenario publishes no stamina floor per distance, so none is stated." />
                </p>

                <p class="note mt-2">
                    These come from the scenario's own settings, the same entry its caps come from. They are
                    the floor the finale expects, not a target to overshoot.
                </p>
            </section>
            </div>
            </div>

            <fieldset data-region="card" class="rounded-lg border border-rule bg-panel p-4">
                <legend class="px-1 text-lg font-semibold text-ink-strong">Skill priorities</legend>

                <p class="text-sm text-ink-muted">
                    An ordered list of skill names, most important first. Reorder with the buttons; no
                    drag is required.
                </p>

                <div class="mt-3 flex flex-wrap items-end gap-2">
                    <BaseInput
                        v-model="newSkill"
                        :id="controlId('skill_priorities')"
                        type="text"
                        name="skill_name"
                        maxlength="255"
                        label="Skill name"
                        :aria-describedby="describedBy('skill_priorities')"
                        placeholder="Enter skill name"
                    />
                    <BaseButton type="button" variant="secondary" :disabled="newSkill.trim() === ''" @click="addSkill">
                        Add
                    </BaseButton>
                </div>

                <ol v-if="form.skill_priorities.length > 0" class="mt-4 space-y-2">
                    <li
                        v-for="(name, index) in form.skill_priorities"
                        :key="`${index}-${name}`"
                        class="flex flex-wrap items-center gap-2"
                        data-skill-row
                    >
                        <span class="font-mono text-sm text-ink-muted tabular-nums">{{ index + 1 }}.</span>
                        <span class="mr-auto text-sm text-ink-strong">{{ name }}</span>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-raised disabled:cursor-not-allowed disabled:opacity-60 transition-colors duration-150 ease-out"
                            :disabled="index === 0"
                            :aria-label="`Move ${name} up`"
                            @click="move(index, -1)"
                        >
                            Move up
                        </button>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-raised disabled:cursor-not-allowed disabled:opacity-60 transition-colors duration-150 ease-out"
                            :disabled="index === form.skill_priorities.length - 1"
                            :aria-label="`Move ${name} down`"
                            @click="move(index, 1)"
                        >
                            Move down
                        </button>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-raised transition-colors duration-150 ease-out"
                            :aria-label="`Remove ${name}`"
                            @click="removeAt(index)"
                        >
                            Remove
                        </button>
                    </li>
                </ol>
                <p v-else class="mt-3 text-sm text-ink-muted">No skill priorities listed yet.</p>

                <p
                    v-if="errorOf('skill_priorities')"
                    :id="errorId('skill_priorities')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errorOf('skill_priorities') }}</p>

                <!-- The design also asks for a risk tolerance and per-skill marks. Both would be
                     stored-shape changes (`BuildTargetPayload::KEYS` is exactly six keys), so they
                     are named here rather than offered as fields the write would refuse. -->
                <p class="mt-3 text-xs text-ink-muted">
                    A risk tolerance and per-skill marks (Required, High, Optional, Ignore) are not
                    recorded yet.
                </p>
            </fieldset>

            <!-- The wireframe's fourth card: the saved default is a preference the Trainer applies, not this
                 run's target. Its three preset buttons are deliberately not built: `statDefaults` holds one
                 stored spread, and naming presets the tool does not hold would be an invented figure
                 (AGENTS.md §5), so the card carries the one control the payload supports and where to set
                 it. The submit stays outside the card because it is the step's write, not a default. -->
            <section data-region="card" class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="target-saved-default">
                <h2 id="target-saved-default" class="text-lg font-semibold text-ink-strong">Target from a saved default</h2>
                <p class="mt-1 text-sm text-ink-muted">Settings can hold a default spread so a new run does not start blank.</p>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <BaseButton type="button" variant="secondary" :disabled="props.statDefaults === null" @click="applyDefaults">
                        Use saved defaults
                    </BaseButton>
                    <Link href="/settings" class="inline-flex min-h-11 items-center text-sm text-ink underline">
                        Set defaults in Settings
                    </Link>
                </div>

                <p class="mt-3 text-xs text-ink-muted">
                    <template v-if="props.statDefaults === null">
                        No saved stat defaults exist, so there is nothing to apply. Settings is where they are set.
                    </template>
                    <template v-else>
                        Saved defaults are your own preference. Applying them replaces the five figures above;
                        they are never applied to a run whose target is already stored.
                    </template>
                </p>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <BaseButton type="submit" variant="primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save target' }}
                </BaseButton>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving your target…</p>
            </div>
        </form>
    </SetupLayout>
</template>

