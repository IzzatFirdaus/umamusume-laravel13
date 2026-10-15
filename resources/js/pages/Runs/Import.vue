<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { BaseInput, BaseSelect, BaseTextarea } from '../../components/Form';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Trainee {
    id: number;
    name: string;
    name_ja: string | null;
}

interface PreviewRow {
    turn: string | null;
    speed: string | null;
    stamina: string | null;
    power: string | null;
    guts: string | null;
    wit: string | null;
    sp: string | null;
    condition: string | null;
    energy: string | null;
    mood: string | null;
    fans: string | null;
}

interface Preview {
    csv: string;
    turns: PreviewRow[];
    run: Record<string, unknown>;
    source: string;
}

const props = defineProps<{
    trainees: Trainee[];
    scenarios: Record<string, string>;
    statuses: Record<string, string>;
    headers: string[];
    preview: Preview | null;
    old: Record<string, unknown>;
}>();

const form = useForm({
    umamusume_id: String(props.old.umamusume_id ?? ''),
    scenario: String(props.old.scenario ?? ''),
    // Completed rather than Active: this form is for a career that is already finished.
    status: String(props.old.status ?? 'Completed'),
    notes: String(props.old.notes ?? ''),
    csv: String(props.old.csv ?? ''),
    file: null as File | null,
});

// The form shows the merged bag: its own errors while the Trainer retries, and the shared page
// errors after a refused write redirects back here — the commit posts with `router`, so its refusal
// lands in the page props rather than in `form.errors`, and without them a refused import rendered
// no message at all (KI-64).
const errors = computed(() => ({
    ...((usePage().props.errors ?? {}) as Record<string, string | undefined>),
    ...form.errors,
}));
const statNames = ['speed', 'stamina', 'power', 'guts', 'wit'];

// The shared selects take `options` as an array; the trainee rows compose the same `Name · ja`
// label the raw option carried, and scenario/status keep their leading empty-value row.
const importTraineeOptions = computed(() => [
    { value: '', label: 'Choose a trainee' },
    ...props.trainees.map((trainee) => ({
        value: String(trainee.id),
        label: trainee.name_ja === null || trainee.name_ja === '' ? trainee.name : `${trainee.name} · ${trainee.name_ja}`,
    })),
]);

const importScenarioOptions = computed(() => [
    { value: '', label: 'Not set (baseline strip)' },
    ...Object.entries(props.scenarios).map(([key, label]) => ({ value: key, label })),
]);

const importStatusOptions = computed(() =>
    Object.entries(props.statuses).map(([value, label]) => ({ value, label })),
);

function onFile(event: Event): void {
    form.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function previewRow(row: string | null): string {
    return row ?? '';
}

/**
 * The server keys a nested failure by the row it found it in (`turns.7.speed`), and the message names the
 * turn inside it (KI-46). One line per column is what the form prints, because the row identity is already
 * in the sentence.
 */
function findRowError(field: string): string | null {
    const key = Object.keys(errors.value).find((k) => new RegExp(`^turns\\.\\d+\\.${field}$`).test(k));

    return key === undefined ? null : (errors.value[key] as string);
}

const rowErrors = computed(() => {
    const lines: string[] = [];

    const turn = findRowError('turn');

    if (turn !== null) {
        lines.unshift(turn);
    }

    // Every column the file declares gets a render path, driven from the request's own header list:
    // a column whose rules can fail can no longer fail invisibly. The five stats keep their prefix
    // sentence and their order; the rest print the row-keyed message as the server composed it.
    for (const field of props.headers) {
        if (field === 'turn') {
            continue;
        }

        const message = findRowError(field);

        if (message === null) {
            continue;
        }

        lines.push(
            statNames.includes(field)
                ? `A ${field} value is outside what this scenario allows: ${message}`
                : message,
        );
    }

    return lines;
});

// A stored id that answers no catalog row is a broken reference, not an unrecorded value, so the
// sentence names the id it read rather than printing a value marker over a name nobody has.
const traineeRef = computed(() => {
    const id = Number(props.preview?.run.umamusume_id);
    const row = props.trainees.find((trainee) => trainee.id === id);

    return row !== undefined
        ? { name: row.name, title: undefined }
        : {
            name: `Trainee id ${id} does not resolve`,
            title: `The imported payload carries umamusume_id ${id} and no catalog row answers it. This is a broken reference, not a name left blank.`,
        };
});

const scenarioName = computed(() => {
    const key = props.preview?.run.scenario;

    if (key === null || key === undefined || key === '') {
        return 'No scenario (baseline strip)';
    }

    return props.scenarios[String(key)] ?? String(key);
});

const submitting = ref(false);

function confirmImport(): void {
    if (props.preview === null || submitting.value) {
        return;
    }

    // Both halves of the file travel with the run's columns: the commit step re-runs the same Form
    // Request the preview did, and `csv` and `turns` are what its rules require (KI-64). The commit is
    // not the form's own visit (the payload is the preview's, not the fields'), so its pending state is
    // this ref: the button disables and names the work, and a second press while the first is in flight
    // is ignored instead of writing the run twice.
    submitting.value = true;

    router.post('/training-runs/import', {
        ...props.preview.run,
        csv: props.preview.csv,
        turns: props.preview.turns,
    }, {
        onFinish: () => {
            submitting.value = false;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Import a historical run" />
        <template #title>Import a historical run</template>

        <p class="mt-2 max-w-3xl text-sm text-ink-muted">
            For a run you already finished. Paste or choose the CSV this app exports, and the turns come in
            as the record of a past career. The preview shows every row before anything is written, and the
            file has to be correct before the run exists: nothing is created until you confirm.
        </p>

        <!-- Preview and commit are two POSTs to two routes, and the commit runs the same Form Request the
             preview did, so the preview is not a trust boundary: the raw text is carried in a field rather
             than a signed token standing in for it, and editing it into something invalid fails on the
             second POST rather than reaching the action. -->
        <form v-if="preview !== null" class="mt-6" :aria-busy="submitting" @submit.prevent="confirmImport">
            <h2 class="text-lg font-semibold text-ink-strong">Confirm the import</h2>

            <p class="mt-2 text-sm text-ink-muted">
                <span class="font-semibold text-ink" :title="traineeRef.title">{{ traineeRef.name }}</span>
                · {{ scenarioName }}
                · {{ preview.run.status }}
                · from {{ preview.source }}
                · {{ preview.turns.length }} turn{{ preview.turns.length === 1 ? '' : 's' }}
            </p>

            <p
                role="note"
                class="mt-3 max-w-3xl rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted"
            >
                This imports turns only. Skills, the support deck and race entries are not part of the file
                format, so they stay empty and you add them per-turn on the run page afterwards. A sheet that
                recorded them is not lost, it just has no column here yet.
            </p>

            <div class="mt-4 overflow-x-auto" role="region" tabindex="0" aria-label="Rows to import">
                <table class="w-full border-collapse text-sm">
                    <caption class="sr-only">Every turn row that will be written</caption>
                    <thead>
                        <tr class="border-b border-rule text-left text-ink-muted">
                            <th class="py-1 pr-3">Turn</th>
                            <th class="pr-3">Speed</th>
                            <th class="pr-3">Stamina</th>
                            <th class="pr-3">Power</th>
                            <th class="pr-3">Guts</th>
                            <th class="pr-3">Wit</th>
                            <th class="pr-3">SP</th>
                            <th class="pr-3">Condition</th>
                            <th class="pr-3">Energy</th>
                            <th class="pr-3">Mood</th>
                            <th class="pr-3">Fans</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, index) in preview.turns"
                            :key="index"
                            class="border-b border-rule font-mono text-xs tabular-nums"
                        >
                            <td class="py-1 pr-3">{{ row.turn }}</td>
                            <td class="pr-3">{{ row.speed }}</td>
                            <td class="pr-3">{{ row.stamina }}</td>
                            <td class="pr-3">{{ row.power }}</td>
                            <td class="pr-3">{{ row.guts }}</td>
                            <td class="pr-3">{{ row.wit }}</td>
                            <td class="pr-3">{{ previewRow(row.sp) }}</td>
                            <td class="pr-3 font-sans">{{ previewRow(row.condition) }}</td>
                            <td class="pr-3">{{ previewRow(row.energy) }}</td>
                            <td class="pr-3">{{ previewRow(row.mood) }}</td>
                            <td class="pr-3">{{ previewRow(row.fans) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-3 font-semibold text-on-chrome disabled:opacity-60"
                >
                    {{ submitting ? 'Importing…' : `Import ${preview.turns.length} turns` }}
                </button>
                <Link
                    href="/training-runs/import"
                    class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-3 text-sm font-semibold text-ink-strong"
                >
                    Start over
                </Link>
            </div>
        </form>

        <form
            v-else
            enctype="multipart/form-data"
            class="mt-6 max-w-3xl space-y-4 rounded-md border border-rule bg-raised p-4 text-sm"
            :aria-busy="form.processing"
            @submit.prevent="form.post('/training-runs/import/preview', { forceFormData: true })"
        >
            <BaseSelect
                :model-value="form.umamusume_id"
                id="import-umamusume_id"
                name="umamusume_id"
                label="Trainee *"
                :options="importTraineeOptions"
                required
                :error="errors.umamusume_id"
                @update:model-value="(v: string | number | null) => (form.umamusume_id = String(v ?? ''))"
            />
            <!-- A plain select rather than the create page's combobox: an import names a trainee you
                 already ran, and the costume-card gate the combobox exists to enforce has no field here. -->

            <BaseSelect
                :model-value="form.scenario"
                id="import-scenario"
                name="scenario"
                label="Scenario (optional)"
                :options="importScenarioOptions"
                :error="errors.scenario"
                @update:model-value="(v: string | number | null) => (form.scenario = String(v ?? ''))"
            />

            <BaseSelect
                :model-value="form.status"
                id="import-status"
                name="status"
                label="Status *"
                :options="importStatusOptions"
                required
                :error="errors.status"
                @update:model-value="(v: string | number | null) => (form.status = String(v ?? ''))"
            />

            <BaseTextarea
                :model-value="form.notes"
                id="import-notes"
                name="notes"
                label="Notes (optional)"
                :rows="2"
                @update:model-value="(v: string | number) => (form.notes = String(v))"
            />

            <BaseInput
                    type="file"
                    id="import-file"
                    name="file"
                    label="CSV file"
                    accept=".csv,text/csv"
                    :error="errors.file"
                    @change="onFile"
                />

            <BaseTextarea
                    :model-value="form.csv"
                    id="import-csv"
                    name="csv"
                    label="or paste the CSV"
                    :rows="8"
                    :placeholder="headers.join(',')"
                    :error="errors.csv || errors.turns || rowErrors.length ? (errors.csv ?? errors.turns ?? rowErrors[0] ?? 'Validation error') : undefined"
                    :aria-describedby="errors.csv || errors.turns || rowErrors.length ? 'import-csv-errors' : undefined"
                    class="font-mono text-xs"
                    @update:model-value="(v: string | number) => (form.csv = String(v))"
                />

            <button
                type="submit"
                :disabled="form.processing"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-3 font-semibold text-on-chrome disabled:opacity-60"
            >
                {{ form.processing ? 'Reading the file…' : 'Preview import' }}
            </button>
        </form>
    </AppLayout>
</template>

