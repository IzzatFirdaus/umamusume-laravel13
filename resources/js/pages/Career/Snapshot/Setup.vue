<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { BaseButton, BaseCheckbox, BaseInput, BaseSelect, BaseTextarea } from '../../../components/Form';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

/*
 * The snapshot form. One pass, not a wizard: a Trainer holding a client partway through a career
 * reads it top to bottom once, and the run that comes out of it stands where the client says.
 *
 * What this form does not collect is deliberate. A run's Legacy and its support deck already have one
 * write path each - the Legacy Lab and the Support deck screens - and re-entering them here would
 * give the same fact two writers, which is the shape the remediation's D9 spent a slice removing. So
 * those two sections link to the surfaces that own the write instead of repeating it.
 *
 * Every state field carries a confidence, and the three are distinct on purpose: a number the Trainer
 * typed is Known, ticking "I don't know this value" is Unknown, and leaving it blank without ticking
 * is Not provided. The review screen reads those three back as three different sentences.
 *
 * The submit opens the review rather than the commit: this form's data is read back and confirmed
 * before anything is written.
 */
const props = defineProps<{
    trainees: { id: number; name: string }[];
    scenarios: Record<string, string>;
    years: { value: number; label: string }[];
    phases: { value: string; label: string }[];
    review: string;
    action: string;
    /** The payload the review screen confirmed, so Back re-hydrates this form. Null on a first visit. */
    draft?: Record<string, unknown> | null;
}>();

type StateField = 'speed' | 'stamina' | 'power' | 'guts' | 'wit' | 'energy' | 'fans' | 'skill_points';

const STAT_FIELDS: { key: StateField; label: string }[] = [
    { key: 'speed', label: 'Speed' },
    { key: 'stamina', label: 'Stamina' },
    { key: 'power', label: 'Power' },
    { key: 'guts', label: 'Guts' },
    { key: 'wit', label: 'Wit' },
];

const META_FIELDS: { key: StateField; label: string }[] = [
    { key: 'energy', label: 'Energy' },
    { key: 'fans', label: 'Fans' },
    { key: 'skill_points', label: 'Skill Points' },
];

// The draft the review screen parked, read as a string per field: the inputs are text controls and a
// number that arrives as `5` must still render as `5`. An absent draft is a first visit and every
// field opens empty.
const seeded = (key: string): string => {
    const value = (props.draft ?? {})[key];

    return value === null || value === undefined ? '' : String(value);
};

const form = useForm({
    umamusume_id: seeded('umamusume_id'),
    scenario: seeded('scenario'),
    career_year: seeded('career_year'),
    career_month: seeded('career_month'),
    career_phase: seeded('career_phase'),
    speed: seeded('speed'),
    stamina: seeded('stamina'),
    power: seeded('power'),
    guts: seeded('guts'),
    wit: seeded('wit'),
    energy: seeded('energy'),
    fans: seeded('fans'),
    skill_points: seeded('skill_points'),
    notes: seeded('notes'),
});

const unknown = reactive<Record<StateField, boolean>>({
    speed: false,
    stamina: false,
    power: false,
    guts: false,
    wit: false,
    energy: false,
    fans: false,
    skill_points: false,
});

// The parked payload carries the per-field states as well as the values, so Back restores the
// "Unknown" toggles rather than only the text the Trainer typed.
for (const key of Object.keys(unknown) as StateField[]) {
    unknown[key] = (props.draft?.field_states as Record<string, string> | undefined)?.[key] === 'unknown';
}

const toggleUnknown = (field: StateField, checked: boolean): void => {
    unknown[field] = checked;

    if (checked) {
        form[field] = '';
    }
};

const fieldStates = computed<Record<string, string>>(() => {
    const states: Record<string, string> = {};

    for (const { key } of [...STAT_FIELDS, ...META_FIELDS]) {
        if (unknown[key]) {
            states[key] = 'unknown';
        } else {
            states[key] = form[key] === '' || form[key] === null ? 'not_provided' : 'known';
        }
    }

    return states;
});

const submit = (): void => {
    form.transform((data) => ({ ...data, field_states: fieldStates.value }))
        .get(props.review, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head title="Record an existing career" />
        <template #title>Record an existing career</template>

        <p class="max-w-3xl text-sm text-ink-muted">
            Enter what your client shows right now. The position becomes the run's own position, and a
            field you leave empty is recorded as not provided rather than guessed at. You will read the
            snapshot back before it is written.
        </p>

        <form class="mt-6 max-w-3xl space-y-6" @submit.prevent="submit">
            <section aria-labelledby="snapshot-scenario-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-scenario-heading" class="text-lg font-semibold text-ink-strong">Scenario</h2>
                <label class="mt-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-ink">Scenario *</span>
                    <BaseSelect
                      v-model="form.scenario"
                      name="scenario"
                      :options="[{ value: '', label: 'Choose a scenario' }, ...Object.keys(props.scenarios).map(key => ({ value: key, label: props.scenarios[key] }))]"
                      required
                      :error="form.errors.scenario"
                    />
                    <span v-if="form.errors.scenario" class="text-xs text-risk">{{ form.errors.scenario }}</span>
                  </label>
            </section>

            <section aria-labelledby="snapshot-trainee-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-trainee-heading" class="text-lg font-semibold text-ink-strong">Trainee</h2>
                <label class="mt-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-ink">Trainee *</span>
                    <BaseSelect
                      v-model="form.umamusume_id"
                      name="umamusume_id"
                      :options="[{ value: '', label: 'Choose a trainee' }, ...props.trainees.map(trainee => ({ value: String(trainee.id), label: trainee.name }))]"
                      required
                      :error="form.errors.umamusume_id"
                    />
                    <span v-if="form.errors.umamusume_id" class="text-xs text-risk">{{ form.errors.umamusume_id }}</span>
                </label>
            </section>

            <section aria-labelledby="snapshot-position-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-position-heading" class="text-lg font-semibold text-ink-strong">Career position</h2>
                <p class="mt-1 text-xs text-ink-muted">
                    Where the career stands, as the client spells it. This run starts from here; it does
                    not start from turn 1.
                </p>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Year *</span>
                        <BaseSelect
                          v-model="form.career_year"
                          name="career_year"
                          :options="[{ value: '', label: 'Year' }, ...props.years.map(year => ({ value: String(year.value), label: year.label }))]"
                          required
                          :error="form.errors.career_year"
                        />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Month *</span>
                        <BaseSelect
                          v-model="form.career_month"
                          name="career_month"
                          :options="[...Array(12).keys()].map(month => ({ value: String(month + 1), label: String(month + 1) }))"
                          required
                          :error="form.errors.career_month"
                        />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Half *</span>
                        <BaseSelect
                          v-model="form.career_phase"
                          name="career_phase"
                          :options="[{ value: '', label: 'Half' }, ...props.phases.map(phase => ({ value: phase.value, label: phase.label }))]"
                          required
                          :error="form.errors.career_phase"
                        />
                    </label>
                </div>
                <span v-if="form.errors.career_year || form.errors.career_month || form.errors.career_phase" class="mt-2 block text-xs text-risk">
                    {{ form.errors.career_year || form.errors.career_month || form.errors.career_phase }}
                </span>
            </section>

            <section aria-labelledby="snapshot-state-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-state-heading" class="text-lg font-semibold text-ink-strong">Current state</h2>
                <p class="mt-1 text-xs text-ink-muted">
                    The numbers the client shows now. Type a number when you have read it, tick "I don't
                    know this value" when the client does not show it, or leave it blank when you have not
                    filled it in. The review says which of the three each field is.
                </p>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <!-- One label per control: a wrapper label here made the number input's accessible
                         name the whole block ("Speed I don't know this value") and left the checkbox
                         unnamed. Each control now owns its label text. -->
                    <div v-for="field in STAT_FIELDS" :key="field.key" class="flex flex-col gap-1">
                        <BaseInput
                            :id="`snapshot-${field.key}`"
                            v-model.number="form[field.key]"
                            type="number"
                            :name="field.key"
                            min="0"
                            :disabled="unknown[field.key]"
                            :error="form.errors[field.key]"
                            :label="field.label"
                        />
                        <BaseCheckbox
                            :id="`snapshot-unknown-${field.key}`"
                            :model-value="unknown[field.key]"
                            :aria-describedby="`snapshot-${field.key}`"
                            @update:model-value="(v) => toggleUnknown(field.key, v)"
                            label="I don't know this value"
                            :disabled="form.errors[field.key]"
                        />
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div v-for="field in META_FIELDS" :key="field.key" class="flex flex-col gap-1">
                        <BaseInput
                            :id="`snapshot-${field.key}`"
                            v-model.number="form[field.key]"
                            type="number"
                            :name="field.key"
                            min="0"
                            :disabled="unknown[field.key]"
                            :error="form.errors[field.key]"
                            :label="field.label"
                        />
                        <BaseCheckbox
                            :id="`snapshot-unknown-${field.key}`"
                            :model-value="unknown[field.key]"
                            :aria-describedby="`snapshot-${field.key}`"
                            @update:model-value="(v) => toggleUnknown(field.key, v)"
                            label="I don't know this value"
                            :disabled="form.errors[field.key]"
                        />
                    </div>
                    </div>
                </section>

            <section aria-labelledby="snapshot-notes-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-notes-heading" class="text-lg font-semibold text-ink-strong">Notes</h2>
                <label class="mt-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-ink">Anything worth remembering about this career</span>
                    <BaseTextarea
                      v-model="form.notes"
                      name="notes"
                      rows="2"
                      maxlength="5000"
                      :error="form.errors.notes"
                    />
                    <span v-if="form.errors.notes" class="text-xs text-risk">{{ form.errors.notes }}</span>
                </label>
            </section>

            <section aria-labelledby="snapshot-later-heading" class="rounded-md border border-dashed border-rule bg-raised p-4">
                <h2 id="snapshot-later-heading" class="text-lg font-semibold text-ink-strong">Recorded on their own screens</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    A run's Legacy and its support deck have one write path each, and this form is not
                    it: re-entering them here would give the same fact two writers.
                </p>
                <div class="mt-3 flex flex-wrap gap-3">
                    <a href="/legacy" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out">Legacy Lab</a>
                    <a href="/support-cards" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out">Support Cards</a>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <BaseButton
                    type="submit"
                    variant="primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Reading back…' : 'Review snapshot' }}
                </BaseButton>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Reading the snapshot back…</p>
                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="() => router.visit('/career/snapshot')"
                >
                    Back
                </BaseButton>
            </div>
        </form>
    </AppLayout>
</template>
