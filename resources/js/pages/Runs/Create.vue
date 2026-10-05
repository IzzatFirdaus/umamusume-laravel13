<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import TraineeCombobox from '../../components/TraineeCombobox.vue';
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
    <AppLayout>
        <Head title="New training run" />
        <template #title>New training run</template>

        <form
            class="mt-6 max-w-lg space-y-4 rounded-md border border-rule bg-raised p-4 text-sm"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <div>
                <label for="trainee-combobox" class="block">
                    <!-- "Trainee" rather than "Umamusume" because it is the string this field's own copy
                         already uses ("No trainee or card found", "Trainees and costume cards"), and the
                         accessible name begins with it, so a speech-input user can say the words on
                         screen and land here (WCAG 2.5.3). The `*` is this repository's required marker:
                         a native `required` shows a sighted Trainer nothing until the submit fails. -->
                    <span class="font-medium text-ink">Trainee *</span>
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

                <p v-if="form.errors.umamusume_id" class="mt-1 text-risk">{{ form.errors.umamusume_id }}</p>
                <p v-if="form.errors.character_card_id" class="mt-1 text-risk">{{ form.errors.character_card_id }}</p>
            </div>

            <!-- A select over the composition matrix, because the request validates `scenario` against
                 its keys: free text here could only ever produce a rejected submission. Leaving it empty
                 is a real option and renders the baseline strip. -->
            <label class="block">
                <span class="font-medium text-ink">Scenario (optional)</span>
                <select
                    v-model="form.scenario"
                    name="scenario"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">Not set (baseline strip)</option>
                    <option v-for="(label, key) in scenarios" :key="key" :value="key">{{ label }}</option>
                </select>
                <p v-if="form.errors.scenario" class="mt-1 text-risk">{{ form.errors.scenario }}</p>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Status *</span>
                <select
                    v-model="form.status"
                    name="status"
                    required
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-risk">{{ form.errors.status }}</p>
            </label>

            <!-- Four fields the request validates and the model persists, surfaced here because the run
                 page already reads them back (the inheritance row, the Grade Point period in the meter,
                 the shop panel's countdown). All four are nullable, so leaving one empty keeps the shape
                 a run had before this block existed. -->
            <label class="block">
                <span class="font-medium text-ink">Inheritance parent A (optional)</span>
                <select
                    v-model="form.inheritance_parent_a_id"
                    name="inheritance_parent_a_id"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">Not set</option>
                    <option v-for="trainee in trainees" :key="trainee.id" :value="trainee.id">{{ trainee.name }}</option>
                </select>
                <p v-if="form.errors.inheritance_parent_a_id" class="mt-1 text-risk">
                    {{ form.errors.inheritance_parent_a_id }}
                </p>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Inheritance parent B (optional)</span>
                <select
                    v-model="form.inheritance_parent_b_id"
                    name="inheritance_parent_b_id"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">Not set</option>
                    <option v-for="trainee in trainees" :key="trainee.id" :value="trainee.id">{{ trainee.name }}</option>
                </select>
                <p v-if="form.errors.inheritance_parent_b_id" class="mt-1 text-risk">
                    {{ form.errors.inheritance_parent_b_id }}
                </p>
            </label>

            <!-- The Grade Point period is entered, never derived (D-270). A scenario that does not compose
                 the grade_objectives panel is rejected by the request's own closure, so a Trainer who
                 picks period 3 on URA Finals sees the message there rather than the value silently
                 dropped. -->
            <label class="block">
                <span class="font-medium text-ink">Current Grade Point period (optional)</span>
                <input
                    v-model="form.current_objective_index"
                    type="number"
                    name="current_objective_index"
                    min="1"
                    :max="maxObjectiveIndex"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    :class="form.errors.current_objective_index ? 'border-risk' : ''"
                >
                <span class="mt-1 block text-xs text-ink-muted">
                    1 to {{ maxObjectiveIndex }}, tracked in the Grade Point meter on the run page. Only
                    scenarios that compose the Grade Point panel accept a value here.
                </span>
                <p v-if="form.errors.current_objective_index" class="mt-1 text-risk">
                    {{ form.errors.current_objective_index }}
                </p>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Shop resets in (optional)</span>
                <input
                    v-model="form.shop_resets_in"
                    type="number"
                    name="shop_resets_in"
                    min="0"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    :class="form.errors.shop_resets_in ? 'border-risk' : ''"
                >
                <span class="mt-1 block text-xs text-ink-muted">
                    Turns until the shop rotation as you read it. The scenario's own upper bound applies
                    at the model layer.
                </span>
                <p v-if="form.errors.shop_resets_in" class="mt-1 text-risk">{{ form.errors.shop_resets_in }}</p>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Notes (optional)</span>
                <textarea
                    v-model="form.notes"
                    name="notes"
                    rows="3"
                    class="mt-1 block w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                />
            </label>

            <button
                type="submit"
                :disabled="form.processing"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-3 font-semibold text-on-chrome disabled:opacity-60"
            >
                {{ form.processing ? 'Creating…' : 'Create run' }}
            </button>
        </form>
    </AppLayout>
</template>
