<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const errors = computed(() => form.errors);
const statNames = ['speed', 'stamina', 'power', 'guts', 'wit'];

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
    const lines = statNames
        .map((stat) => ({ stat, message: findRowError(stat) }))
        .filter((entry): entry is { stat: string; message: string } => entry.message !== null)
        .map((entry) => `A ${entry.stat} value is outside what this scenario allows: ${entry.message}`);

    const turn = findRowError('turn');

    if (turn !== null) {
        lines.unshift(turn);
    }

    for (const field of ['mood', 'energy']) {
        const message = findRowError(field);

        if (message !== null) {
            lines.push(message);
        }
    }

    return lines;
});

const traineeName = computed(() => {
    const id = Number(props.preview?.run.umamusume_id);

    return props.trainees.find((trainee) => trainee.id === id)?.name ?? 'Unknown trainee';
});

const scenarioName = computed(() => {
    const key = props.preview?.run.scenario;

    if (key === null || key === undefined || key === '') {
        return 'No scenario (baseline strip)';
    }

    return props.scenarios[String(key)] ?? String(key);
});

function confirmImport(): void {
    router.post('/training-runs/import', props.preview?.run ?? {});
}
</script>

<template>
    <AppLayout>
        <Head title="Import a historical run" />
        <template #title>Import a historical run</template>

        <p class="mt-2 max-w-2xl text-sm text-ink-muted">
            For a run you already finished. Paste or choose the CSV this app exports, and the turns come in
            as the record of a past career. The preview shows every row before anything is written, and the
            file has to be correct before the run exists: nothing is created until you confirm.
        </p>

        <!-- Preview and commit are two POSTs to two routes, and the commit runs the same Form Request the
             preview did, so the preview is not a trust boundary: the raw text is carried in a field rather
             than a signed token standing in for it, and editing it into something invalid fails on the
             second POST rather than reaching the action. -->
        <form v-if="preview !== null" class="mt-6" @submit.prevent="confirmImport">
            <h2 class="text-lg font-semibold text-ink-strong">Confirm the import</h2>

            <p class="mt-2 text-sm text-ink-muted">
                <span class="font-semibold text-ink">{{ traineeName }}</span>
                · {{ scenarioName }}
                · {{ preview.run.status }}
                · {{ preview.turns.length }} turn{{ preview.turns.length === 1 ? '' : 's' }}
            </p>

            <p
                role="note"
                class="mt-3 max-w-2xl rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted"
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
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-3 font-semibold text-on-chrome"
                >
                    Import {{ preview.turns.length }} turns
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
            class="mt-6 max-w-2xl space-y-4 rounded-md border border-rule bg-raised p-4 text-sm"
            :aria-busy="form.processing"
            @submit.prevent="form.post('/training-runs/import/preview', { forceFormData: true })"
        >
            <label class="block">
                <span class="font-medium text-ink">Trainee *</span>
                <!-- A plain select rather than the create page's combobox: an import names a trainee you
                     already ran, and the costume-card gate the combobox exists to enforce has no field here. -->
                <select
                    v-model="form.umamusume_id"
                    name="umamusume_id"
                    required
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    :class="errors.umamusume_id ? 'border-risk' : ''"
                >
                    <option value="">Choose a trainee</option>
                    <option v-for="trainee in trainees" :key="trainee.id" :value="String(trainee.id)">
                        {{ trainee.name }}<template v-if="trainee.name_ja"> · {{ trainee.name_ja }} </template>
                    </option>
                </select>
                <p v-if="errors.umamusume_id" class="mt-1 text-risk">{{ errors.umamusume_id }}</p>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Scenario (optional)</span>
                <select
                    v-model="form.scenario"
                    name="scenario"
                    class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                    :class="errors.scenario ? 'border-risk' : ''"
                >
                    <option value="">Not set (baseline strip)</option>
                    <option v-for="(label, key) in scenarios" :key="key" :value="key">{{ label }}</option>
                </select>
                <p v-if="errors.scenario" class="mt-1 text-risk">{{ errors.scenario }}</p>
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
                <p v-if="errors.status" class="mt-1 text-risk">{{ errors.status }}</p>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Notes (optional)</span>
                <textarea v-model="form.notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink" />
            </label>

            <div class="flex flex-col gap-1">
                <label for="import-file" class="font-medium text-ink">CSV file</label>
                <!-- When both are present the file wins: the request copies the upload into `csv` before
                     `csv` is validated, so choosing a file cannot silently mix with a stale paste. -->
                <input
                    id="import-file"
                    type="file"
                    name="file"
                    accept=".csv,text/csv"
                    class="min-h-11 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                    @change="onFile"
                >
                <p v-if="errors.file" class="mt-1 text-risk">{{ errors.file }}</p>
            </div>

            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">or paste the CSV</span>
                <span class="font-mono text-xs text-ink-muted">{{ headers.join(', ') }}</span>
                <!-- No `required` here on purpose. A native constraint is evaluated against this field alone,
                     so a Trainer who chooses the file and leaves the box empty gets "Please fill out this
                     field" and the form never submits: the upload path the field above exists for, closed.
                     The server has the rule with the pair in view, and its message says so in the words this
                     form needs. -->
                <textarea
                    v-model="form.csv"
                    name="csv"
                    rows="8"
                    class="min-h-11 rounded-md border border-rule bg-raised px-2 py-1 font-mono text-xs text-ink"
                    :class="errors.csv || errors.turns || rowErrors.length ? 'border-risk' : ''"
                    :placeholder="headers.join(',')"
                />
                <p v-if="errors.csv" class="mt-1 text-risk">{{ errors.csv }}</p>
                <p v-if="errors.turns" class="mt-1 text-risk">{{ errors.turns }}</p>
                <p v-for="(message, index) in rowErrors" :key="index" class="mt-1 text-risk">{{ message }}</p>
            </label>

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
