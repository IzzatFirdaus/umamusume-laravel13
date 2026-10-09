<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
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

const form = useForm({
    umamusume_id: '',
    scenario: '',
    career_year: '',
    career_month: '',
    career_phase: '',
    speed: '',
    stamina: '',
    power: '',
    guts: '',
    wit: '',
    energy: '',
    fans: '',
    skill_points: '',
    notes: '',
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

        <p class="max-w-2xl text-sm text-ink-muted">
            Enter what your client shows right now. The position becomes the run's own position, and a
            field you leave empty is recorded as not provided rather than guessed at. You will read the
            snapshot back before it is written.
        </p>

        <form class="mt-6 max-w-3xl space-y-6" @submit.prevent="submit">
            <section aria-labelledby="snapshot-scenario-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-scenario-heading" class="text-base font-semibold text-ink-strong">Scenario</h2>
                <label class="mt-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-ink">Scenario *</span>
                    <select v-model="form.scenario" name="scenario" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                        <option value="">Choose a scenario</option>
                        <option v-for="(label, key) in props.scenarios" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <span v-if="form.errors.scenario" class="text-xs text-risk">{{ form.errors.scenario }}</span>
                </label>
            </section>

            <section aria-labelledby="snapshot-trainee-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-trainee-heading" class="text-base font-semibold text-ink-strong">Trainee</h2>
                <label class="mt-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-ink">Trainee *</span>
                    <select v-model="form.umamusume_id" name="umamusume_id" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                        <option value="">Choose a trainee</option>
                        <option v-for="trainee in props.trainees" :key="trainee.id" :value="String(trainee.id)">
                            {{ trainee.name }}
                        </option>
                    </select>
                    <span v-if="form.errors.umamusume_id" class="text-xs text-risk">{{ form.errors.umamusume_id }}</span>
                </label>
            </section>

            <section aria-labelledby="snapshot-position-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-position-heading" class="text-base font-semibold text-ink-strong">Career position</h2>
                <p class="mt-1 text-xs text-ink-muted">
                    Where the career stands, as the client spells it. This run starts from here; it does
                    not start from turn 1.
                </p>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Year *</span>
                        <select v-model="form.career_year" name="career_year" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">Year</option>
                            <option v-for="year in props.years" :key="year.value" :value="String(year.value)">{{ year.label }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Month *</span>
                        <select v-model="form.career_month" name="career_month" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">Month</option>
                            <option v-for="month in 12" :key="month" :value="String(month)">{{ month }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Half *</span>
                        <select v-model="form.career_phase" name="career_phase" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">Half</option>
                            <option v-for="phase in props.phases" :key="phase.value" :value="phase.value">{{ phase.label }}</option>
                        </select>
                    </label>
                </div>
                <span v-if="form.errors.career_year || form.errors.career_month || form.errors.career_phase" class="mt-2 block text-xs text-risk">
                    {{ form.errors.career_year || form.errors.career_month || form.errors.career_phase }}
                </span>
            </section>

            <section aria-labelledby="snapshot-state-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-state-heading" class="text-base font-semibold text-ink-strong">Current state</h2>
                <p class="mt-1 text-xs text-ink-muted">
                    The numbers the client shows now. Type a number when you have read it, tick "I don't
                    know this value" when the client does not show it, or leave it blank when you have not
                    filled it in. The review says which of the three each field is.
                </p>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <label v-for="field in STAT_FIELDS" :key="field.key" class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">{{ field.label }}</span>
                        <input
                            v-model="form[field.key]"
                            type="number" :name="field.key" min="0" :disabled="unknown[field.key]"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink disabled:opacity-60"
                        >
                        <span class="flex items-center gap-1.5 text-[0.7rem] text-ink-muted">
                            <input
                                type="checkbox" :checked="unknown[field.key]"
                                @change="toggleUnknown(field.key, ($event.target as HTMLInputElement).checked)"
                            >
                            I don't know this value
                        </span>
                    </label>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label v-for="field in META_FIELDS" :key="field.key" class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">{{ field.label }}</span>
                        <input
                            v-model="form[field.key]"
                            type="number" :name="field.key" min="0" :disabled="unknown[field.key]"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink disabled:opacity-60"
                        >
                        <span class="flex items-center gap-1.5 text-[0.7rem] text-ink-muted">
                            <input
                                type="checkbox" :checked="unknown[field.key]"
                                @change="toggleUnknown(field.key, ($event.target as HTMLInputElement).checked)"
                            >
                            I don't know this value
                        </span>
                    </label>
                </div>
            </section>

            <section aria-labelledby="snapshot-notes-heading" class="rounded-md border border-rule bg-raised p-4">
                <h2 id="snapshot-notes-heading" class="text-base font-semibold text-ink-strong">Notes</h2>
                <label class="mt-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-ink">Anything worth remembering about this career</span>
                    <textarea v-model="form.notes" name="notes" rows="2" maxlength="5000" class="rounded-md border border-rule bg-raised px-2 py-1.5 text-ink" />
                </label>
            </section>

            <section aria-labelledby="snapshot-later-heading" class="rounded-md border border-dashed border-rule bg-raised p-4">
                <h2 id="snapshot-later-heading" class="text-base font-semibold text-ink-strong">Recorded on their own screens</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    A run's Legacy and its support deck have one write path each, and this form is not
                    it: re-entering them here would give the same fact two writers.
                </p>
                <div class="mt-3 flex flex-wrap gap-3">
                    <a href="/legacy" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">Legacy Lab</a>
                    <a href="/support-cards" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">Support Cards</a>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:opacity-60"
                >
                    {{ form.processing ? 'Reading back…' : 'Review snapshot' }}
                </button>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Reading the snapshot back…</p>
                <a href="/career/snapshot" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">Back</a>
            </div>
        </form>
    </AppLayout>
</template>
