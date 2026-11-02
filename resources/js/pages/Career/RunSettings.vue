<script setup lang="ts">
/**
 * The run-identity door (`C6b.1`, KI-144).
 *
 * The Cockpit used to host these four forms inside its `Career tools` disclosure. That disclosure
 * was the interim home for writes the retired record screen owned, and its retirement needs each
 * write to have a door of its own. This is the identity one.
 *
 * Every form PUTs to `run.update_url` (`runs.update`) — the route and validator that already own
 * these columns — so the door adds a page and no write path. Validation feedback therefore lands
 * where it always did: `form.errors` from that request.
 */
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import CareerLayout from '../../layouts/CareerLayout.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import LinkButton from '../../components/Form/LinkButton.vue';
import PageHeader from '../../components/ui/PageHeader.vue';

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario: string | null;
        scenario_label: string;
        status_labels: Record<string, string>;
        scenarios: Record<string, string>;
        umamusume_id: number;
        scenario: string | null;
        current_objective_index: number | null;
        run_url: string;
        update_url: string;
        deck_url: string;
    };
    identity: {
        trainee_rarity: number | null;
        potential_level: number | null;
        growth_rate: Record<string, number | null> | null;
        stat_order: string[];
        caps: Record<string, { entered: number | null; scenario: number }>;
    };
    gradeObjectives: { index: number; name: string }[];
}>();

/** The request's own column name for an entered ceiling, so this page spells none of them. */
const capColumn = (stat: string): string => `cap_${stat.toLowerCase()}`;

/** What this page is for, in one line, under the one `h1`. */
const lede = computed(() => `The identity fields the Cockpit used to hold. Everything here writes through ${props.run.update_url}.`);

/*
 * Every form carries the whole key set `runs.update` demands, because `umamusume_id` and `status`
 * are required and a partial payload is refused. The refusal arrives as `form.errors`, which this
 * page does not print, so an incomplete form reads as "nothing happened" — the shape the cockpit
 * forms had, and the reason they carried the siblings they were not editing.
 */
const base = {
    umamusume_id: String(props.run.umamusume_id),
    status: props.run.status,
    scenario: props.run.scenario ?? '',
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
};

const statusForm = useForm({ ...base });
const scenarioForm = useForm({ ...base });
const periodForm = useForm({ ...base });

const saveStatus = (): void => {
    statusForm.put(props.run.update_url, { preserveScroll: true });
};

const saveScenario = (): void => {
    scenarioForm.put(props.run.update_url, { preserveScroll: true });
};

const savePeriod = (): void => {
    periodForm.put(props.run.update_url, { preserveScroll: true });
};

const identityForm = useForm({
    umamusume_id: String(props.run.umamusume_id),
    status: props.run.status,
    trainee_rarity: props.identity.trainee_rarity === null ? '' : String(props.identity.trainee_rarity),
    potential_level: props.identity.potential_level === null ? '' : String(props.identity.potential_level),
    // The entered ceilings, prefilled with what the run holds. An empty field is a clear rather than
    // a no-op, which is why they ride this form's key set at all (`ADR-0025`). The stats come from
    // the server's map, so this page names none of them.
    ...Object.fromEntries(
        Object.entries(props.identity.caps).map(([stat, ceiling]) => [
            capColumn(stat),
            ceiling.entered === null ? '' : String(ceiling.entered),
        ]),
    ) as Record<string, string>,
    growth_rate: Object.fromEntries(
        props.identity.stat_order.map((stat) => {
            const value = props.identity.growth_rate?.[stat];

            return [stat, value === null || value === undefined ? '' : String(value)];
        }),
    ) as Record<string, string>,
});

const saveIdentity = (): void => {
    identityForm
        // An empty field is "not stated" and `0` is a real figure, so the two are kept apart here
        // rather than collapsed by a `Number('')` on the way out (D-220). A growth row with nothing
        // stated is `null` rather than five nulls: the request's own contract is that a run which
        // stated no growth stores no row.
        .transform((data) => {
            const growth = Object.fromEntries(
                Object.entries(data.growth_rate).map(([stat, value]) => [stat, value === '' ? null : Number(value)]),
            );

            // A ceiling field with nothing in it is a clear, which is the statement "use the
            // scenario's own bound" — never a zero the client never showed.
            const ceilings = Object.fromEntries(
                Object.keys(props.identity.caps).map((stat) => {
                    const column = capColumn(stat);
                    const value = data[column];

                    return [column, value === '' || value === undefined ? null : Number(value)];
                }),
            );

            return {
                ...data,
                ...ceilings,
                trainee_rarity: data.trainee_rarity === '' ? null : Number(data.trainee_rarity),
                potential_level: data.potential_level === '' ? null : Number(data.potential_level),
                growth_rate: Object.values(growth).every((value) => value === null) ? null : growth,
            };
        })
        .put(props.run.update_url, { preserveScroll: true });
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="null"
        :run-url="props.run.run_url"
        current="settings"
    >
        <Head :title="`Run settings: ${props.run.trainee}`" />

        <template #head>
            <PageHeader eyebrow="Run settings" :title="props.run.trainee" :description="lede">
                <template #actions>
                    <LinkButton :href="props.run.run_url + '/cockpit'" variant="secondary" size="sm">
                        Back to Cockpit
                    </LinkButton>
                </template>
            </PageHeader>
        </template>

        <section aria-labelledby="run-settings-heading" class="card mt-4">
            <h2 id="run-settings-heading" class="text-lg font-semibold text-ink-strong">Run identity</h2>
            <p class="sub">
                What this run was recorded as. Every field writes through the run's own update route,
                so an edit here is the same edit the Cockpit made.
            </p>

            <ul class="mt-3 flex flex-wrap items-end gap-4 text-sm">
                <li class="flex flex-col gap-1">
                    <label for="status" class="text-ink-muted">Status</label>
                    <form @submit.prevent="saveStatus">
                        <BaseSelect
                            id="status"
                            v-model="statusForm.status"
                            name="status"
                            :options="Object.keys(props.run.status_labels).map(value => ({ value: value, label: props.run.status_labels[value] }))"
                        />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="statusForm.processing"
                        >
                            {{ statusForm.processing ? 'Saving…' : 'Change status' }}
                        </button>
                        <p v-if="statusForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                        <p v-if="Object.keys(statusForm.errors).length" role="alert" class="mt-1 text-xs text-risk">{{ Object.values(statusForm.errors).join(' ') }}</p>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <label for="scenario" class="text-ink-muted">Scenario</label>
                    <form @submit.prevent="saveScenario">
                        <BaseSelect
                            id="scenario"
                            v-model="scenarioForm.scenario"
                            name="scenario"
                            :options="[{ value: '', label: 'No scenario set' }, ...Object.keys(props.run.scenarios).map(key => ({ value: key, label: props.run.scenarios[key] }))]"
                        />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="scenarioForm.processing"
                        >
                            {{ scenarioForm.processing ? 'Saving…' : 'Change scenario' }}
                        </button>
                        <p v-if="scenarioForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                        <p v-if="Object.keys(scenarioForm.errors).length" role="alert" class="mt-1 text-xs text-risk">{{ Object.values(scenarioForm.errors).join(' ') }}</p>
                    </form>
                </li>

                <li v-if="props.gradeObjectives.length > 0" class="flex flex-col gap-1">
                    <label for="current_objective_index" class="text-ink-muted">Grade Point period</label>
                    <form @submit.prevent="savePeriod">
                        <BaseSelect
                            id="current_objective_index"
                            v-model="periodForm.current_objective_index"
                            name="current_objective_index"
                            :options="[{ value: '', label: 'Not reported' }, ...props.gradeObjectives.map(objective => ({ value: String(objective.index), label: objective.index + '. ' + objective.name }))]"
                        />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="periodForm.processing"
                        >
                            {{ periodForm.processing ? 'Saving…' : 'Report period' }}
                        </button>
                        <p v-if="periodForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                        <p v-if="Object.keys(periodForm.errors).length" role="alert" class="mt-1 text-xs text-risk">{{ Object.values(periodForm.errors).join(' ') }}</p>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Trainee rarity and potential</span>
                    <form @submit.prevent="saveIdentity" class="flex flex-col gap-2">
                        <span class="flex flex-wrap items-end gap-3">
                            <BaseSelect
                                id="trainee_rarity"
                                v-model="identityForm.trainee_rarity"
                                name="trainee_rarity"
                                label="Rarity"
                                :options="[{ value: '', label: 'Not stated' }, ...[1, 2, 3].map(rarity => ({ value: String(rarity), label: '★' + String(rarity) }))]"
                            />
                            <label for="potential_level" class="flex flex-col gap-1">
                                <span class="text-xs text-ink-muted">Potential Level</span>
                                <BaseInput
                                    id="potential_level"
                                    v-model="identityForm.potential_level"
                                    type="number"
                                    name="potential_level"
                                    min="1"
                                    max="5"
                                />
                            </label>
                        </span>
                        <fieldset class="flex flex-wrap items-end gap-3">
                            <legend class="text-xs text-ink-muted">Growth rate</legend>
                            <label
                                v-for="stat in props.identity.stat_order"
                                :key="stat"
                                :for="`growth_rate_${stat}`"
                                class="flex flex-col gap-1"
                            >
                                <span class="text-xs text-ink-muted">{{ stat }}</span>
                                <span class="flex items-center gap-1">
                                    <BaseInput
                                        :id="`growth_rate_${stat}`"
                                        v-model="identityForm.growth_rate[stat]"
                                        type="number"
                                        :name="`growth_rate[${stat}]`"
                                        min="0"
                                        max="30"
                                    />
                                    <span class="text-ink-muted">%</span>
                                </span>
                            </label>
                        </fieldset>
                        <fieldset class="flex flex-wrap items-end gap-3">
                            <legend class="text-xs text-ink-muted">Stat ceilings</legend>
                            <!-- The ceiling the client shows for this run, entered rather than computed
                                 (`ADR-0025`): nothing here derives it from sparks or card effects. The
                                 published figure stays printed beside the field, so an entered ceiling is
                                 never read as the scenario's own arithmetic. -->
                            <label
                                v-for="(ceiling, stat) in props.identity.caps"
                                :key="stat"
                                class="flex flex-col gap-1"
                            >
                                <span class="text-xs text-ink-muted">{{ stat }}</span>
                                <span class="flex items-center gap-1">
                                    <BaseInput
                                        :id="`ceiling_${stat}`"
                                        v-model="identityForm[capColumn(String(stat))]"
                                        type="number"
                                        :name="capColumn(String(stat))"
                                        :aria-label="`${stat} ceiling`"
                                        min="1"
                                    />
                                    <span
                                        class="text-xs text-ink-muted"
                                        title="The figure this scenario publishes in its own cap table. An entered ceiling replaces it for this run; leaving the field empty restores it."
                                    >Scenario {{ ceiling.scenario }}</span>
                                </span>
                            </label>
                        </fieldset>
                        <p v-if="identityForm.isDirty" class="text-xs text-ink-muted">Unsaved changes.</p>
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 w-fit items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="identityForm.processing"
                        >
                            {{ identityForm.processing ? 'Saving…' : 'Save identity' }}
                        </button>
                        <p v-if="identityForm.recentlySuccessful" class="mt-1 text-xs text-ink-muted">Saved.</p>
                        <p v-if="Object.keys(identityForm.errors).length" role="alert" class="mt-1 text-xs text-risk">{{ Object.values(identityForm.errors).join(' ') }}</p>
                    </form>
                </li>
            </ul>
        </section>
    </CareerLayout>
</template>
