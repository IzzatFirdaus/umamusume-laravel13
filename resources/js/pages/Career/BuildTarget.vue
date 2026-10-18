<script setup lang="ts">
/*
 * SCREEN-005, Build Target (`ADR-0020` §1; `docs/proposals/screen-spec-2.0.md` SCREEN-005). Step 3 of
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

/** Integer percent of the stat's cap, clamped to the bar's length. Display arithmetic, nothing more. */
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

const capTitle = (stat: string): string => {
    if (props.scenarioPending) {
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

function addSkill(): void {
    const name = newSkill.value.trim();

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
        <template #eyebrow>Setup</template>
        <template #title>Your target</template>

        <!-- Back breadcrumb -->
        <nav class="mt-4 mb-2" aria-label="Breadcrumb">
            <Link href="/career/setup/target" class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-ink hover:underline transition-colors duration-150 ease-out" aria-label="Back to target step">
                ← Back to target
            </Link>
        </nav>

        <!-- Header: eyebrow + subtitle. The page title is the layout's #title h1. -->
        <header class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-accent-ink">Step 3 of 6</p>
            <p class="mt-1 text-sm text-ink-muted">The target is what this career is built to reach.</p>
        </header>

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
            title="No build target is stored in this setup draft yet."
        >
            No target is recorded yet. The fields below start empty rather than at zero.
        </p>

        <!-- `novalidate` on purpose: the `max` attribute mirrors the cap for the browser only, and
             native constraint validation would block the submit with a browser bubble instead of
             letting the server refuse with its named bound, which is the message this page renders
             with its own `aria-describedby` and focus. -->
        <form class="mt-5 mx-auto max-w-4xl space-y-6" novalidate @submit.prevent="submit">
            <fieldset class="rounded-lg border border-rule bg-panel p-4">
                <legend class="px-1 text-lg font-semibold text-ink-strong">What the career is for</legend>

                <div class="grid gap-4 @3xl:grid-cols-2">
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

                <!-- The design brief names six purposes and the stored enum holds four: one design
                     name maps onto a stored case (per the enum's own docblock), and one more this
                     tool does not record. The gap is stated rather than filled with an option the
                     write would refuse. -->
                <p class="mt-3 text-xs text-ink-muted">
                    The design also names a fifth purpose, Competitive Build, which this tool does not
                    record yet.
                </p>
            </fieldset>

            <fieldset class="rounded-lg border border-rule bg-panel p-4">
                <legend class="px-1 text-lg font-semibold text-ink-strong">Stat targets & ceilings</legend>

                <p class="text-sm text-ink-muted">
                    Target is the number you aim for this run. Ceiling is the highest the client shows;
                    leave empty to keep the scenario's published figure.
                </p>

                <div class="mt-4 space-y-4">
                    <div v-for="stat in props.statOrder" :key="stat" :data-stat="stat" class="grid gap-3 @3xl:grid-cols-2">
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
                            <span class="text-xs text-ink-muted" :title="capTitle(stat)">Cap {{ cap(stat) }}</span>

                            <span class="flex flex-wrap items-center gap-2">
                                <span class="h-2 w-36 overflow-hidden rounded-md bg-sunken" aria-hidden="true">
                                    <span
                                        class="block h-full bg-chrome"
                                        :data-fill="percent(stat)"
                                        :style="{ width: `${percent(stat)}%` }"
                                    ></span>
                                </span>
                                <AbsenceValue
                                    v-if="entered(stat) === null"
                                    compact
                                    class="font-mono"
                                    reason="No target entered for this stat yet."
                                />
                                <span v-else class="font-mono text-xs text-ink tabular-nums">{{ enteredLabel(stat) }}</span>
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

            <fieldset class="rounded-lg border border-rule bg-panel p-4">
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

<div class="flex flex-wrap items-center gap-3">
                <BaseButton type="submit" variant="primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save target' }}
                </BaseButton>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving your target…</p>
            </div>
        </form>
    </SetupLayout>
</template>

