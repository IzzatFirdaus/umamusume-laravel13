<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

/*
 * The snapshot form. One pass, not a wizard: a Trainer holding a client partway through a career
 * reads it top to bottom once, and the run that comes out of it stands where the client says.
 *
 * What this form does not collect is deliberate. A run's Legacy and its support deck already have one
 * write path each - the Legacy Lab and the Support deck screens - and re-entering them here would
 * give the same fact two writers, which is the shape the remediation's D9 spent a slice removing. So
 * those two sections link to the surfaces that own the write instead of repeating it.
 *
 * Every number is optional: a snapshot is read off a client the Trainer is looking at, and a figure
 * they cannot see is recorded as absent rather than guessed at.
 */
const props = defineProps<{
    trainees: { id: number; name: string }[];
    scenarios: Record<string, string>;
    years: { value: number; label: string }[];
    phases: { value: string; label: string }[];
    action: string;
}>();

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

const submit = (): void => {
    form.post(props.action, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head title="Record an existing career" />
        <template #title>Record an existing career</template>

        <p class="max-w-2xl text-sm text-ink-muted">
            Enter what your client shows right now. The position becomes the run's own position, and a
            field you leave empty is recorded as not provided rather than guessed at.
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
                    The numbers the client shows now. Leave a field empty when you have not read it;
                    the run records the absence and says so rather than a zero.
                </p>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <label v-for="stat in ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']" :key="stat" class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">{{ stat }}</span>
                        <input
                            v-model="form[stat.toLowerCase() as 'speed' | 'stamina' | 'power' | 'guts' | 'wit']"
                            type="number" :name="stat.toLowerCase()" min="0"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                    </label>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Energy</span>
                        <input v-model="form.energy" type="number" name="energy" min="0" max="100" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Fans</span>
                        <input v-model="form.fans" type="number" name="fans" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-ink">Skill Points</span>
                        <input v-model="form.skill_points" type="number" name="skill_points" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
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
                    {{ form.processing ? 'Recording…' : 'Record snapshot' }}
                </button>
                <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Recording the snapshot…</p>
                <a href="/career/snapshot" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">Back</a>
            </div>
        </form>
    </AppLayout>
</template>
