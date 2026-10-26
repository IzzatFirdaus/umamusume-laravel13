<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Card from '../../components/ui/Card.vue';
import TraineeCombobox from '../../components/TraineeCombobox.vue';
import FormField from '../../components/ui/FormField.vue';
import TextInput from '../../components/ui/TextInput.vue';
import Textarea from '../../components/ui/Textarea.vue';
import Select from '../../components/ui/Select.vue';
import { BaseButton, LinkButton } from '../../components/Form';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface CardRow {
    selectionId: number;
    sourceCardId: number;
    title: string;
    titleKey: string;
    releaseDate: string;
    debut: boolean;
    cardless?: boolean;
}

interface TraineeRow {
    umamusumeId: number;
    trainee: string;
    traineeJa: string | null;
    cards: CardRow[];
}

const props = defineProps<{
    roster: TraineeRow[];
    scenarios: Record<string, string>;
    statuses: Record<string, string>;
    maxObjectiveIndex: number;
    old: Record<string, unknown>;
}>();

// A failed write comes back through a redirect, which reloads the page, so the form opens on the
// flashed input rather than on empty fields. The ids are compared as text because flashed form input
// comes back as text.
function previous(key: string, fallback = ''): string {
    const value = props.old[key];

    return value === undefined || value === null ? fallback : String(value);
}

const form = useForm({
    umamusume_id: previous('umamusume_id'),
    character_card_id: previous('character_card_id'),
    scenario: previous('scenario'),
    status: previous('status', 'Active'),
    inheritance_parent_a_id: previous('inheritance_parent_a_id'),
    inheritance_parent_b_id: previous('inheritance_parent_b_id'),
    current_objective_index: previous('current_objective_index'),
    shop_resets_in: previous('shop_resets_in'),
    notes: previous('notes'),
});

// One list for both questions. The combobox is the page's only trainee source, so the inheritance
// parents read the same rows rather than a second collection that can drift from it.
const trainees = computed(() =>
    props.roster.map((row) => ({ id: String(row.umamusumeId), name: row.trainee })),
);

// The shared select takes `options` as an array; the record props read the same here as the raw
// `v-for (label, key)` did, with the empty-value row that carries this screen's honesty note kept
// first for scenario and both parents.
const scenarioOptions = computed(() => [
    { value: '', label: 'Not set (baseline strip)' },
    ...Object.entries(props.scenarios).map(([key, label]) => ({ value: key, label })),
]);

const statusOptions = computed(() =>
    Object.entries(props.statuses).map(([value, label]) => ({ value, label })),
);

const parentOptions = computed(() => [
    { value: '', label: 'Not set' },
    ...trainees.value.map((trainee) => ({ value: trainee.id, label: trainee.name })),
]);

function onCommit(hit: { trainee: TraineeRow; card: CardRow }): void {
    form.umamusume_id = String(hit.trainee.umamusumeId);
    // A cardless row writes nothing: `character_card_id` is nullable and the request accepts a run
    // naming the trainee alone, which is the only claim she can honestly make with nothing confirmed.
    form.character_card_id = hit.card.cardless === true ? '' : String(hit.card.selectionId);
}

function onClear(): void {
    form.umamusume_id = '';
    form.character_card_id = '';
}

function submit(): void {
    form.post('/training-runs');
}
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Careers', href: '/training-runs' }, { label: 'New career' }]">
        <Head title="New career" />
        <template #head>
            <PageHeader
                eyebrow="New career"
                title="Create a career"
                description="Trainee and status are required. Scenario, inheritance parents, the Grade Point period, the shop countdown and notes are optional and can be recorded later on the run page."
            >
                <template #actions>
                    <LinkButton href="/training-runs" variant="secondary">Back to careers</LinkButton>
                </template>
            </PageHeader>
        </template>

        <form
            class="max-w-3xl"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <Card padding="lg">
                <h2>Career</h2>

                <div>
                    <label for="trainee-combobox" class="block text-sm font-medium text-ink">
                        <!-- "Trainee" rather than "Umamusume" because it is the string this field's own copy
                             already uses ("No trainee or card found", "Trainees and costume cards"), and the
                             accessible name begins with it, so a speech-input user can say the words on
                             screen and land here (WCAG 2.5.3). The `*` is this repository's required marker:
                             a native `required` shows a sighted Trainer nothing until the submit fails. -->
                        <span>Trainee *</span>
                    </label>

                    <div class="mt-1">
                        <TraineeCombobox
                            :roster="roster"
                            :initial-trainee-id="previous('umamusume_id')"
                            :initial-card-id="previous('character_card_id')"
                            :invalid="form.errors.umamusume_id !== undefined"
                            @commit="onCommit"
                            @clear="onClear"
                        />
                    </div>

                    <p class="mt-1 text-xs text-ink-muted">
                        One combobox for both the trainee and her costume. A trainee with nothing
                        confirmed records the trainee alone.
                    </p>

                    <p v-if="form.errors.umamusume_id" id="create-umamusume_id-error" role="alert" class="mt-1 text-xs font-semibold text-risk">{{ form.errors.umamusume_id }}</p>
                    <p v-if="form.errors.character_card_id" id="create-character_card_id-error" role="alert" class="mt-1 text-xs font-semibold text-risk">{{ form.errors.character_card_id }}</p>
                </div>

                <!-- The six request fields in the HFW's two-column grid. A select over the composition
                     matrix for `scenario`, because the request validates it against its keys: free text
                     here could only ever produce a rejected submission. Leaving it empty is a real option
                     and renders the baseline strip. -->
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <Select
                        :model-value="form.scenario"
                        id="create-scenario"
                        name="scenario"
                        label="Scenario (optional)"
                        :options="scenarioOptions"
                        :error="form.errors.scenario"
                        @update:model-value="(v: string | number | null) => (form.scenario = String(v ?? ''))"
                    />

                    <Select
                        :model-value="form.status"
                        id="create-status"
                        name="status"
                        label="Status"
                        :options="statusOptions"
                        required
                        :error="form.errors.status"
                        @update:model-value="(v: string | number | null) => (form.status = String(v ?? ''))"
                    />

                    <!-- Four fields the request validates and the model persists, surfaced here because the
                         run page already reads them back (the inheritance row, the Grade Point period in the
                         meter, the shop panel's countdown). All four are nullable, so leaving one empty
                         keeps the shape a run had before this block existed. -->
                    <Select
                        :model-value="form.inheritance_parent_a_id"
                        id="create-inheritance_parent_a_id"
                        name="inheritance_parent_a_id"
                        label="Inheritance parent A (optional)"
                        :options="parentOptions"
                        :error="form.errors.inheritance_parent_a_id"
                        @update:model-value="(v: string | number | null) => (form.inheritance_parent_a_id = String(v ?? ''))"
                    />

                    <Select
                        :model-value="form.inheritance_parent_b_id"
                        id="create-inheritance_parent_b_id"
                        name="inheritance_parent_b_id"
                        label="Inheritance parent B (optional)"
                        :options="parentOptions"
                        :error="form.errors.inheritance_parent_b_id"
                        @update:model-value="(v: string | number | null) => (form.inheritance_parent_b_id = String(v ?? ''))"
                    />

                    <!-- The Grade Point period is entered, never derived (D-270). A scenario that does not
                         compose the grade_objectives panel is rejected by the request's own closure, so a
                         Trainer who picks period 3 on URA Finals sees the message there rather than the
                         value silently dropped. -->
                    <FormField
                        label="Current Grade Point period"
                        :required="false"
                        :error="form.errors.current_objective_index"
                        :hint="`1 to ${maxObjectiveIndex}, tracked in the Grade Point meter on the run page. Only scenarios that compose the Grade Point panel accept a value here.`"
                        id="create-current_objective_index"
                    >
                        <template #default="{ id, describedBy, invalid, required }">
                            <TextInput
                                :id="id"
                                type="number"
                                name="current_objective_index"
                                min="1"
                                :max="maxObjectiveIndex"
                                :model-value="form.current_objective_index"
                                :described-by="describedBy"
                                :invalid="invalid"
                                :required="required"
                                @update:model-value="(v: string) => (form.current_objective_index = String(v))"
                            />
                        </template>
                    </FormField>

                    <FormField
                        label="Shop resets in"
                        :required="false"
                        :error="form.errors.shop_resets_in"
                        hint="Turns until the shop rotation as you read it. The scenario's own upper bound applies at the model layer."
                        id="create-shop_resets_in"
                    >
                        <template #default="{ id, describedBy, invalid, required }">
                            <TextInput
                                :id="id"
                                type="number"
                                name="shop_resets_in"
                                min="0"
                                :model-value="form.shop_resets_in"
                                :described-by="describedBy"
                                :invalid="invalid"
                                :required="required"
                                @update:model-value="(v: string) => (form.shop_resets_in = String(v))"
                            />
                        </template>
                    </FormField>
                </div>

                <FormField
                    label="Notes"
                    :required="false"
                    :error="form.errors.notes"
                    id="create-notes"
                    class="mt-4"
                >
                    <template #default="{ id, describedBy, invalid, required }">
                        <Textarea
                            :id="id"
                            name="notes"
                            :rows="3"
                            :model-value="form.notes"
                            :described-by="describedBy"
                            :invalid="invalid"
                            :required="required"
                            @update:model-value="(v: string | number) => (form.notes = String(v))"
                        />
                    </template>
                </FormField>

                <div class="mt-5 flex flex-wrap gap-3">
                    <BaseButton type="submit" variant="primary" :loading="form.processing">
                        {{ form.processing ? 'Creating…' : 'Create run' }}
                    </BaseButton>
                    <LinkButton href="/training-runs" variant="secondary">Cancel</LinkButton>
                </div>
            </Card>
        </form>
    </AppLayout>
</template>
