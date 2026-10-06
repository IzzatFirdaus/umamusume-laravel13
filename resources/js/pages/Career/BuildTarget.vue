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
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
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
    /** The stored payload as entered, or null before one exists. */
    target: StoredTarget | null;
    /** One ceiling per stat, from `ScenarioCaps::forRun` — base cap only when no scenario yet. */
    caps: Record<string, number>;
    statOrder: string[];
    purposeOptions: SelectOption[];
    distanceBands: string[];
    surfaces: string[];
    styles: string[];
    scenarioLabel: string | null;
    scenarioPending: boolean;
}>();

const form = useForm<{
    purpose: string;
    distance: string;
    surface: string;
    style: string;
    targets: Record<string, number | string>;
    skill_priorities: string[];
}>({
    purpose: props.target?.purpose ?? '',
    distance: props.target?.distance ?? '',
    surface: props.target?.surface ?? '',
    style: props.target?.style ?? '',
    // No stored value is an empty field, never a pre-filled zero: a zero is a number the Trainer
    // did not enter, and the payload's required rule asks for every stat by name.
    targets: Object.fromEntries(props.statOrder.map((stat) => [stat, props.target?.targets?.[stat] ?? ''])),
    skill_priorities: props.target?.skill_priorities ? [...props.target.skill_priorities] : [],
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
    'skill_priorities',
]);

const controlId = (key: string): string => `field-${key.replace(/\./g, '_')}`;
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
    // The URL is literal, as every page in this app states it: there is no Ziggy dependency here,
    // so `router.route()` would throw at click time rather than at build time.
    form.put('/career/setup/target', {
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

const capTitle = computed((): string =>
    props.scenarioPending
        ? 'No scenario is chosen yet, so this is the base cap with no scenario bonus.'
        : 'The chosen scenario’s ceiling for this stat, the same number the write enforces.',
);

const optionLabel = (options: SelectOption[], value: string): string | null =>
    options.find((option) => option.value === value)?.label ?? null;

/** One sentence from the entered values alone; an empty field is named, never omitted. */
const summary = computed((): string => {
    const clauses: string[] = [];

    const purpose = optionLabel(props.purposeOptions, form.purpose);
    clauses.push(purpose === null ? 'Purpose not chosen yet' : `Purpose ${purpose}`);

    clauses.push(form.distance === '' ? 'distance not chosen yet' : `distance ${form.distance}`);
    clauses.push(form.surface === '' ? 'surface not chosen yet' : `surface ${form.surface}`);
    clauses.push(form.style === '' ? 'style not chosen yet' : `style ${form.style}`);

    clauses.push(
        props.statOrder
            .map((stat) => {
                const value = entered(stat);

                return `${stat} ${value === null ? 'not entered' : value}`;
            })
            .join(', '),
    );

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
    <SetupLayout :step="3">
        <Head title="Your target" />
        <template #title>Your target</template>

        <p class="max-w-2xl text-sm text-ink-muted">
            The target is what this career is built to reach. It is entered as one object and replaced
            as one object, and it lives in this setup draft until Preflight creates the run.
        </p>

        <p class="mt-3 text-sm text-ink">
            Scenario:
            <strong v-if="props.scenarioLabel" class="text-ink-strong">{{ props.scenarioLabel }}</strong>
            <span
                v-else
                class="text-ink-muted"
                title="No scenario has been chosen in this setup draft yet, so every cap below is the base cap with no scenario bonus."
            >N/A</span>
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
        <form class="mt-5 max-w-3xl space-y-6" novalidate @submit.prevent="submit">
            <fieldset class="rounded-md border border-rule bg-panel p-4">
                <legend class="px-1 text-sm font-semibold text-ink-strong">What the career is for</legend>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-1">
                        <span class="text-sm text-ink-muted">Purpose</span>
                        <select
                            :id="controlId('purpose')"
                            v-model="form.purpose"
                            name="purpose"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            :aria-describedby="describedBy('purpose')"
                        >
                            <option value="">Not chosen yet</option>
                            <option v-for="option in props.purposeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-sm text-ink-muted">Distance</span>
                        <select
                            :id="controlId('distance')"
                            v-model="form.distance"
                            name="distance"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            :aria-describedby="describedBy('distance')"
                        >
                            <option value="">Not chosen yet</option>
                            <option v-for="band in props.distanceBands" :key="band" :value="band">{{ band }}</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-sm text-ink-muted">Surface</span>
                        <select
                            :id="controlId('surface')"
                            v-model="form.surface"
                            name="surface"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            :aria-describedby="describedBy('surface')"
                        >
                            <option value="">Not chosen yet</option>
                            <option v-for="surface in props.surfaces" :key="surface" :value="surface">{{ surface }}</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-sm text-ink-muted">Style</span>
                        <select
                            :id="controlId('style')"
                            v-model="form.style"
                            name="style"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            :aria-describedby="describedBy('style')"
                        >
                            <option value="">Not chosen yet</option>
                            <option v-for="style in props.styles" :key="style" :value="style">{{ style }}</option>
                        </select>
                    </label>
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

            <fieldset class="rounded-md border border-rule bg-panel p-4">
                <legend class="px-1 text-sm font-semibold text-ink-strong">Stat targets</legend>

                <p class="text-sm text-ink-muted">
                    The bar is the entered number against the cap, and the cap is the server's own
                    ceiling for the chosen scenario. Whether a target is reachable in the turns left is
                    not computed here.
                </p>

                <div class="mt-4 space-y-4">
                    <div v-for="stat in props.statOrder" :key="stat" :data-stat="stat" class="space-y-1">
                        <label class="flex flex-col gap-1">
                            <span class="text-sm text-ink-muted">{{ stat }}</span>
                            <span class="flex flex-wrap items-center gap-3">
                                <input
                                    :id="controlId(`targets.${stat}`)"
                                    v-model.number="form.targets[stat]"
                                    type="number"
                                    :name="`targets[${stat}]`"
                                    :aria-label="stat"
                                    min="0"
                                    :max="cap(stat)"
                                    inputmode="numeric"
                                    class="h-11 w-32 rounded-md border border-rule bg-raised px-2 font-mono text-ink tabular-nums"
                                    :aria-describedby="describedBy(`targets.${stat}`)"
                                >
                                <span class="text-xs text-ink-muted" :title="capTitle">Cap {{ cap(stat) }}</span>
                            </span>
                        </label>

                        <!-- Bar and number together, always: the number is the readout and the bar is
                             its illustration, so the bar alone never stands in for a value. -->
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="h-2 w-36 overflow-hidden rounded bg-sunken" aria-hidden="true">
                                <span
                                    class="block h-full bg-chrome"
                                    :data-fill="percent(stat)"
                                    :style="{ width: `${percent(stat)}%` }"
                                ></span>
                            </span>
                            <span
                                class="font-mono text-xs text-ink tabular-nums"
                                :title="entered(stat) === null ? 'No target entered for this stat yet.' : undefined"
                            >{{ enteredLabel(stat) }}</span>
                        </span>

                        <p
                            v-if="errorOf(`targets.${stat}`)"
                            :id="errorId(`targets.${stat}`)"
                            role="alert"
                            class="rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                        >{{ errorOf(`targets.${stat}`) }}</p>
                    </div>
                </div>

                <p
                    v-if="errors.targets"
                    :id="errorId('targets')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errors.targets }}</p>
            </fieldset>

            <fieldset class="rounded-md border border-rule bg-panel p-4">
                <legend class="px-1 text-sm font-semibold text-ink-strong">Skill priorities</legend>

                <p class="text-sm text-ink-muted">
                    An ordered list of skill names, most important first. Reorder with the buttons; no
                    drag is required.
                </p>

                <div class="mt-3 flex flex-wrap items-end gap-2">
                    <label class="flex flex-col gap-1">
                        <span class="text-sm text-ink-muted">Skill name</span>
                        <input
                            :id="controlId('skill_priorities')"
                            v-model="newSkill"
                            type="text"
                            name="skill_name"
                            maxlength="255"
                            class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            :aria-describedby="describedBy('skill_priorities')"
                        >
                    </label>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center rounded-full border border-rule bg-raised px-4 text-sm font-medium text-ink hover:bg-sunken disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="newSkill.trim() === ''"
                        @click="addSkill"
                    >
                        Add
                    </button>
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
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-raised disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="index === 0"
                            :aria-label="`Move ${name} up`"
                            @click="move(index, -1)"
                        >
                            Move up
                        </button>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-raised disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="index === form.skill_priorities.length - 1"
                            :aria-label="`Move ${name} down`"
                            @click="move(index, 1)"
                        >
                            Move down
                        </button>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-raised"
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

            <section aria-labelledby="target-summary-heading" class="rounded-md border border-rule bg-panel p-4">
                <h2 id="target-summary-heading" class="text-sm font-semibold text-ink-strong">Summary</h2>
                <p class="mt-2 text-sm text-ink">
                    {{ summary }}
                    <ProvenanceBadge
                        state="calculated"
                        title="assembled by this tool from the values entered on this form, not a stored fact"
                    />
                </p>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving…' : 'Save target' }}
                </button>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving your target…</p>
            </div>
        </form>
    </SetupLayout>
</template>
