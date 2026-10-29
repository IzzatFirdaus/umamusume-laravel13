<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Card from '../../components/ui/Card.vue';
import UiSelect from '../../components/ui/Select.vue';
import UiTextarea from '../../components/ui/Textarea.vue';
import { BaseInput, BaseSelect, BaseTextarea, BaseButton, LinkButton } from '../../components/Form';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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
    mapping: string[] | null;
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
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.file = file;

    // Only the first line is read, and only to draw the mapping selectors: the request still reads
    // the uploaded file itself, so this is a display aid and never the value that gets imported.
    if (file === null) {
        fileHeaders.value = [];
        return;
    }

    void file.text().then((text) => {
        const first = text.split(/\r?\n/).find((line) => line.trim() !== '') ?? '';
        fileHeaders.value = first.trim() === '' ? [] : first.split(',').map((cell) => cell.trim().toLowerCase());
    });
}

/*
 * Paste or upload: one source, two doors. The HFW draws a segmented control over the source card and
 * this build keeps the same pair, because the textarea and the filename picker were both always shown
 * and the Trainer had to work out which one the request would read.
 */
const mode = ref<'paste' | 'upload'>('paste');

/*
 * Column mapping (import.html "Map the columns"). The file's own first line is read in the browser
 * only to draw the selectors; the server re-validates the mapping as a bijection onto the importer's
 * columns before it parses anything, so a tampered page cannot import a column twice.
 *
 * When the first line is the app's own header order the selectors stay hidden and nothing is posted:
 * the export this app writes must go back in with no extra step.
 */
const CANONICAL = ['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans'];

const fileHeaders = ref<string[]>([]);

const sourceHeaders = computed<string[]>(() => {
    if (mode.value === 'upload') {
        return fileHeaders.value;
    }

    const first = form.csv.split(/\r?\n/).find((line) => line.trim() !== '') ?? '';

    return first.trim() === '' ? [] : first.split(',').map((cell) => cell.trim().toLowerCase());
});

const mapping = ref<string[]>([]);

watch(
    sourceHeaders,
    (headers) => {
        mapping.value = headers.map((header) => (CANONICAL.includes(header) ? header : ''));
    },
    { immediate: true },
);

const needsMapping = computed(
    () => sourceHeaders.value.length > 0 && sourceHeaders.value.join(',') !== CANONICAL.join(','),
);

const mappingProblem = computed<string | null>(() => {
    if (!needsMapping.value) {
        return null;
    }

    const chosen = mapping.value.filter((target) => target !== '');

    if (new Set(chosen).size !== chosen.length) {
        return 'Two file columns map to the same import column. Each column is supplied once.';
    }

    const missing = CANONICAL.filter((target) => !chosen.includes(target));

    return missing.length === 0 ? null : `Still unmapped: ${missing.join(', ')}.`;
});

form.transform((data) => (needsMapping.value ? { ...data, mapping: mapping.value } : data));

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

/*
 * The preview's edit boxes. The run-level fields are the Trainer's own four, carried through the
 * preview payload; a local copy is what the edit boxes bind, and the commit posts that copy, so a
 * correction made here is the value the second Form Request validates. The watch re-seeds whenever a
 * new preview arrives, because the component instance survives the Inertia visit that carries it.
 */
const runEdits = ref({ umamusume_id: '', scenario: '', status: '', notes: '' });

watch(
    () => props.preview,
    (preview) => {
        if (preview === null) {
            return;
        }

        runEdits.value = {
            umamusume_id: String(preview.run.umamusume_id ?? ''),
            scenario: String(preview.run.scenario ?? ''),
            status: String(preview.run.status ?? ''),
            notes: String(preview.run.notes ?? ''),
        };
    },
    { immediate: true },
);

// "Active, Completed or Retired" — the valid set named in words, built from the enum's own labels
// rather than a second copy that can drift (import-preview annotation 2: a missing field says what
// to do, not just that it is empty).
const statusSet = computed(() => {
    const labels = Object.values(props.statuses);

    return labels.length < 2
        ? labels.join('')
        : `${labels.slice(0, -1).join(', ')} or ${labels[labels.length - 1]}`;
});

// A stored id that answers no catalog row is a broken reference, not an unrecorded value, so the
// sentence names the id it read rather than printing a value marker over a name nobody has.
const traineeResolves = computed(() => {
    const id = runEdits.value.umamusume_id;

    return id !== '' && props.trainees.some((trainee) => String(trainee.id) === id);
});

const traineeName = computed(() => {
    const id = runEdits.value.umamusume_id;
    const row = props.trainees.find((trainee) => String(trainee.id) === id);

    return row !== undefined
        ? row.name
        : `Trainee id ${id} does not resolve`;
});

const scenarioName = computed(() => {
    const key = runEdits.value.scenario;

    if (key === '') {
        return 'No scenario (baseline strip)';
    }

    return props.scenarios[key] ?? key;
});

// The four fields, each with the found/missing verdict the preview prints beside it. Found means the
// import carries a value; missing names what to do about it. Absence is a normal result, so a missing
// optional field is muted and only a field the commit needs is a refusal-in-waiting.
const previewFields = computed(() => [
    {
        key: 'umamusume_id',
        label: 'Trainee',
        found: traineeResolves.value,
        required: true,
        missing: `The import names no trainee the catalog answers. Choose the trainee this run belongs to.`,
    },
    {
        key: 'scenario',
        label: 'Scenario',
        found: runEdits.value.scenario !== '',
        required: false,
        missing: 'Not in the file. Choose a scenario, or leave it Not set and the run renders the baseline strip.',
    },
    {
        key: 'status',
        label: 'Status',
        found: runEdits.value.status !== '',
        required: true,
        missing: `Not in the file. Choose ${statusSet.value}.`,
    },
    {
        key: 'notes',
        label: 'Notes',
        found: runEdits.value.notes.trim() !== '',
        required: false,
        missing: 'Not in the file. Notes are optional; an imported run records what the file said and nothing more.',
    },
]);

const foundCount = computed(() => previewFields.value.filter((field) => field.found).length);
const missingCount = computed(() => previewFields.value.length - foundCount.value);

/*
 * The two missing-field controls the HFW pairs under the counts. Fill moves focus to the first
 * *required* field the file did not settle, because that is the one the commit will refuse on; drop
 * clears only the two optional fields, so an import cannot lose the trainee or the status by pressing
 * the wrong button, and the note beside them says so.
 */
function fillMissing(): void {
    const first = previewFields.value.find((field) => field.required && !field.found);

    if (first !== undefined) {
        document.getElementById(`preview-${first.key}`)?.focus();
    }
}

function dropMissing(): void {
    runEdits.value.scenario = '';
    runEdits.value.notes = '';
}

/*
 * How many of the parsed rows the table draws. The import writes every row either way: this is a
 * reading aid for a two-hundred-turn sheet, not a filter, and the line above the table says so.
 */
const rowLimit = ref<number | 'all'>(25);
const rowLimitOptions = [
    { value: '10', label: '10 rows' },
    { value: '25', label: '25 rows' },
    { value: '50', label: '50 rows' },
    { value: 'all', label: 'All rows' },
];
const rowLimitValue = computed({
    get: () => String(rowLimit.value),
    set: (value: string) => {
        rowLimit.value = value === 'all' ? 'all' : Number(value);
    },
});
const visibleTurns = computed(() =>
    rowLimit.value === 'all' ? props.preview?.turns ?? [] : (props.preview?.turns ?? []).slice(0, rowLimit.value),
);

function fieldState(key: string): { found: boolean; required: boolean; missing: string } {
    const field = previewFields.value.find((candidate) => candidate.key === key);

    return field ?? { found: true, required: false, missing: '' };
}

const submitting = ref(false);

function confirmImport(): void {
    if (props.preview === null || submitting.value) {
        return;
    }

    // Both halves of the file travel with the run's columns: the commit step re-runs the same Form
    // Request the preview did, and `csv` and `turns` are what its rules require (KI-64). The commit is
    // not the form's own visit (the payload is the preview's, not the fields'), so its pending state is
    // this ref: the button disables and names the work, and a second press while the first is in flight
    // is ignored instead of writing the run twice. The run half is the edited copy, so a correction made
    // on the preview is the value the second validation reads.
    submitting.value = true;

    router.post('/training-runs/import', {
        ...runEdits.value,
        csv: props.preview.csv,
        turns: props.preview.turns,
        // The preview's own payload carries the mapping it was read with, so a remapped file commits
        // under the same reading the preview showed instead of failing the strict header check.
        mapping: props.preview.mapping,
    }, {
        onFinish: () => {
            submitting.value = false;
        },
    });
}

const breadcrumb = computed(() =>
    props.preview === null
        ? [{ label: 'Careers', href: '/training-runs' }, { label: 'Import' }]
        : [{ label: 'Careers', href: '/training-runs' }, { label: 'Import', href: '/training-runs/import' }, { label: 'Preview' }],
);
</script>

<template>
    <AppLayout :breadcrumb="breadcrumb">
        <!-- Preview and commit are two POSTs to two routes, and the commit runs the same Form Request the
             preview did, so the preview is not a trust boundary: the raw text is carried in a field rather
             than a signed token standing in for it, and editing it into something invalid fails on the
             second POST rather than reaching the action. -->
        <!-- A named slot must be a direct child of the component: Vue cannot attach a slot template
             whose parent is a conditional `<template v-if>`, and the build fails with "Codegen node is
             missing". Both page heads render from this one hoisted slot. -->
        <template #head>
            <PageHeader
                v-if="preview !== null"
                eyebrow="Import preview"
                title="Here is what the text says"
                description="Every field the importer read, with an edit box on each. Fields it did not read are marked, not defaulted. Nothing is written until you confirm."
            />
            <PageHeader
                v-else
                eyebrow="Import"
                title="Bring in a historical career"
                description="Paste the CSV this app exports or upload the file. The preview shows every row it read, and nothing is created until you confirm."
            />
        </template>

        <template v-if="preview !== null">
            <Head title="Import preview" />

            <!-- Three counts at the top (import-preview annotation 4): found, missing and source, so a
                 Trainer knows at a glance how much review remains. -->
            <div class="grid gap-3 g-3">
                <Card>
                    <div class="border-l-4 border-status-positive pl-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Found</p>
                        <p class="font-mono text-2xl font-extrabold text-ink-strong">{{ foundCount }} of {{ previewFields.length }}</p>
                    </div>
                </Card>
                <Card>
                    <div class="border-l-4 border-status-caution-ink pl-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Missing</p>
                        <p class="font-mono text-2xl font-extrabold text-ink-strong">{{ missingCount }}</p>
                    </div>
                </Card>
                <Card>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Source</p>
                    <p class="text-sm font-extrabold text-ink-strong">{{ preview.source }}</p>
                    <p class="mt-1 text-xs text-ink-muted">{{ preview.turns.length }} turn row{{ preview.turns.length === 1 ? '' : 's' }}</p>
                </Card>
            </div>

            <form
                class="mt-4 grid items-start gap-4 xl:grid-cols-3"
                :aria-busy="submitting"
                @submit.prevent="confirmImport"
            >
                <div class="min-w-0 flex flex-col gap-4 xl:col-span-2">
                    <Card>
                        <h2>Identity and position</h2>

                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                                @click="fillMissing"
                            >Fill missing</button>
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                                @click="dropMissing"
                            >Drop missing</button>
                            <span class="text-xs text-ink-muted">Fill moves to the first required field; drop clears Scenario and Notes only. Trainee and Status are required.</span>
                        </div>

                        <!-- DOMAIN OVERRIDE: the HFW gives every parsed turn cell an edit box. This app's
                             commit step re-parses `turns` from `csv` server-side (ImportHistoricalRunRequest
                             prepareForValidation), so a cell edited here would be silently replaced by the
                             file's own value. The run-level fields are the edit boxes; the rows are the
                             read-only review table below, and changing a row means correcting the file, which
                             is what "Start over" is for. -->
                        <div class="grid gap-4 g-2">
                            <div>
                                <label for="preview-umamusume_id" id="preview-umamusume_id-label" class="flex items-center gap-2 text-sm font-medium text-ink">
                                    Trainee
                                    <span class="tag" :class="fieldState('umamusume_id').found ? '' : 'you'">{{ fieldState('umamusume_id').found ? 'found' : 'missing' }}</span>
                                </label>
                                <UiSelect
                                    id="preview-umamusume_id"
                                    name="umamusume_id"
                                    :model-value="runEdits.umamusume_id"
                                    :options="importTraineeOptions"
                                    :described-by="fieldState('umamusume_id').found ? undefined : 'preview-umamusume_id-missing'"
                                    @update:model-value="(v: string | number | null) => (runEdits.umamusume_id = String(v ?? ''))"
                                />
                                <p v-if="!fieldState('umamusume_id').found" id="preview-umamusume_id-missing" class="mt-1 text-xs text-risk">
                                    {{ fieldState('umamusume_id').missing }}
                                </p>
                            </div>

                            <div>
                                <label for="preview-scenario" id="preview-scenario-label" class="flex items-center gap-2 text-sm font-medium text-ink">
                                    Scenario
                                    <span class="tag" :class="fieldState('scenario').found ? '' : 'you'">{{ fieldState('scenario').found ? 'found' : 'missing' }}</span>
                                </label>
                                <UiSelect
                                    id="preview-scenario"
                                    name="scenario"
                                    :model-value="runEdits.scenario"
                                    :options="importScenarioOptions"
                                    :described-by="fieldState('scenario').found ? undefined : 'preview-scenario-missing'"
                                    @update:model-value="(v: string | number | null) => (runEdits.scenario = String(v ?? ''))"
                                />
                                <p v-if="!fieldState('scenario').found" id="preview-scenario-missing" class="mt-1 text-xs text-ink-muted">
                                    {{ fieldState('scenario').missing }}
                                </p>
                            </div>

                            <div>
                                <label for="preview-status" id="preview-status-label" class="flex items-center gap-2 text-sm font-medium text-ink">
                                    Status
                                    <span class="tag" :class="fieldState('status').found ? '' : 'you'">{{ fieldState('status').found ? 'found' : 'missing' }}</span>
                                </label>
                                <UiSelect
                                    id="preview-status"
                                    name="status"
                                    :model-value="runEdits.status"
                                    :options="importStatusOptions"
                                    :described-by="fieldState('status').found ? undefined : 'preview-status-missing'"
                                    @update:model-value="(v: string | number | null) => (runEdits.status = String(v ?? ''))"
                                />
                                <p v-if="!fieldState('status').found" id="preview-status-missing" class="mt-1 text-xs text-risk">
                                    {{ fieldState('status').missing }}
                                </p>
                            </div>

                            <div>
                                <label for="preview-notes" id="preview-notes-label" class="flex items-center gap-2 text-sm font-medium text-ink">
                                    Notes
                                    <span class="tag" :class="fieldState('notes').found ? '' : 'you'">{{ fieldState('notes').found ? 'found' : 'missing' }}</span>
                                </label>
                                <UiTextarea
                                    id="preview-notes"
                                    name="notes"
                                    :rows="2"
                                    :model-value="runEdits.notes"
                                    :described-by="fieldState('notes').found ? undefined : 'preview-notes-missing'"
                                    @update:model-value="(v: string) => (runEdits.notes = v)"
                                />
                                <p v-if="!fieldState('notes').found" id="preview-notes-missing" class="mt-1 text-xs text-ink-muted">
                                    {{ fieldState('notes').missing }}
                                </p>
                            </div>
                        </div>

                        <p class="mt-3 text-sm text-ink-muted">
                            <span class="font-semibold text-ink">{{ traineeName }}</span>
                            · {{ scenarioName }}
                            · {{ runEdits.status }}
                            · from {{ preview.source }}
                            · {{ preview.turns.length }} turn{{ preview.turns.length === 1 ? '' : 's' }}
                        </p>
                    </Card>

                    <Card>
                        <h2>Rows to import</h2>

                        <p
                            role="note"
                            class="mb-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted"
                        >
                            This imports turns only. Skills, the support deck and race entries are not part of the file
                            format, so they stay empty and you add them per-turn on the run page afterwards. A sheet that
                            recorded them is not lost, it just has no column here yet.
                        </p>

                        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                            <p class="text-xs text-ink-muted">
                                Showing {{ visibleTurns.length }} of {{ preview.turns.length }} turn
                                row{{ preview.turns.length === 1 ? '' : 's' }}; the import writes all of them.
                            </p>
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                                Rows shown
                                <BaseSelect
                                    id="import-row-limit"
                                    v-model="rowLimitValue"
                                    name="row_limit"
                                    :options="rowLimitOptions"
                                />
                            </label>
                        </div>

                        <div class="overflow-x-auto" role="region" tabindex="0" aria-label="Rows to import">
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
                                        v-for="(row, index) in visibleTurns"
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
                    </Card>
                </div>

                <div class="flex flex-col gap-4">
                    <Card>
                        <h2>What will be created</h2>
                        <table class="w-full text-sm">
                            <tbody>
                                <tr class="border-b border-rule">
                                    <td class="py-1 pr-3 text-ink-muted">Run</td>
                                    <td class="text-right font-mono">1</td>
                                </tr>
                                <tr class="border-b border-rule">
                                    <td class="py-1 pr-3 text-ink-muted">Turn entries</td>
                                    <td class="text-right font-mono">{{ preview.turns.length }}</td>
                                </tr>
                                <tr class="border-b border-rule">
                                    <td class="py-1 pr-3 text-ink-muted">Race entries</td>
                                    <td class="text-right font-mono">0</td>
                                </tr>
                                <tr>
                                    <td class="py-1 pr-3 text-ink-muted">Skills and deck</td>
                                    <td class="text-right text-xs text-ink-muted">Not in the file format</td>
                                </tr>
                            </tbody>
                        </table>
                    </Card>

                    <Card>
                        <h2>Confirm the import</h2>
                        <div class="flex flex-wrap gap-3">
                            <BaseButton type="submit" variant="primary" :disabled="submitting" :loading="submitting">
                                {{ submitting ? 'Importing…' : `Import ${preview.turns.length} turns` }}
                            </BaseButton>
                            <LinkButton href="/training-runs/import" variant="secondary">Start over</LinkButton>
                        </div>
                    </Card>
                </div>
            </form>
        </template>

        <template v-else>
            <Head title="Import a run" />

            <!-- DOMAIN OVERRIDE: the HFW's source card takes free-text run write-ups and offers Markdown
                 and JSON uploads. This importer parses the app's own CSV shape only (ADR-0017,
                 ImportHistoricalRunRequest::HEADERS; `file` accepts mimes csv,txt), so the card states the
                 CSV contract rather than promising a heuristic read it cannot do. -->
            <form
                enctype="multipart/form-data"
                class="grid items-start gap-4 xl:grid-cols-3"
                :aria-busy="form.processing"
                @submit.prevent="form.post('/training-runs/import/preview', { forceFormData: true })"
            >
                <div class="min-w-0 xl:col-span-2">
                    <Card padding="lg">
                        <h2>Source</h2>

                        <!-- The HFW's segmented control (import.html): one source, two doors. -->
                        <div
                            class="mb-4 inline-flex rounded-md border border-rule p-1"
                            role="group"
                            aria-label="Where the text comes from"
                        >
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium transition-colors duration-150 ease-out"
                                :class="mode === 'paste' ? 'bg-primary-action text-on-primary-action' : 'text-ink'"
                                :aria-pressed="mode === 'paste'"
                                @click="mode = 'paste'"
                            >Paste</button>
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center rounded px-3 text-sm font-medium transition-colors duration-150 ease-out"
                                :class="mode === 'upload' ? 'bg-primary-action text-on-primary-action' : 'text-ink'"
                                :aria-pressed="mode === 'upload'"
                                @click="mode = 'upload'"
                            >Upload</button>
                        </div>

                        <!-- The textarea is the primary control, set large (import annotation 3): pasting
                             the sheet is the common case and gets more space than the filename picker. -->
                        <BaseTextarea
                            v-if="mode === 'paste'"
                            :model-value="form.csv"
                            id="import-csv"
                            name="csv"
                            label="Paste the CSV"
                            :rows="10"
                            :placeholder="headers.join(',')"
                            :error="errors.csv || errors.turns || rowErrors.length ? (errors.csv ?? errors.turns ?? rowErrors[0] ?? 'Validation error') : undefined"
                            :aria-describedby="errors.csv || errors.turns || rowErrors.length ? 'import-csv-errors' : undefined"
                            class="font-mono text-xs"
                            @update:model-value="(v: string | number) => (form.csv = String(v))"
                        />

                        <!-- The request's own HEADERS joined for reading, while the paste field's placeholder carries
                             the same list joined for pasting. Restored: `63d1cdd2` dropped this line as a stale change
                             while `run-import.spec.ts:53` kept asserting it, which left the tree red. -->
                        <p v-if="mode === 'paste'" class="mt-1 font-mono text-xs text-ink-muted">{{ headers.join(', ') }}</p>

                        <div v-if="mode === 'upload'" class="mt-4">
                            <BaseInput
                                type="file"
                                id="import-file"
                                name="file"
                                label="Or upload the file"
                                accept=".csv,text/csv"
                                :error="errors.file"
                                @change="onFile"
                            />
                        </div>

                        <!-- Column mapping: drawn only when the file's own first line is not the app's
                             header order. The selectors are a reading aid; the request refuses a mapping
                             that is not a bijection onto its columns (ImportHistoricalRunRequest). -->
                        <div v-if="needsMapping" class="mt-4 rounded-md border border-rule bg-panel p-4">
                            <h3 class="text-sm font-semibold text-ink-strong">Map the columns</h3>
                            <p class="mt-1 text-xs text-ink-muted">
                                The first line is not this app's header order. Say which import column each
                                file column supplies; an ignored column is not imported.
                            </p>
                            <div class="mt-3 grid gap-3 g-2">
                                <label
                                    v-for="(header, index) in sourceHeaders"
                                    :key="index"
                                    class="flex flex-col gap-1 text-xs font-semibold text-ink-muted"
                                >
                                    {{ header === '' ? `Column ${index + 1}` : header }}
                                    <BaseSelect
                                        :id="`import-mapping-${index}`"
                                        :model-value="mapping[index] ?? ''"
                                        :name="`mapping[${index}]`"
                                        :options="[{ value: '', label: 'Ignore' }, ...headers.map((h) => ({ value: h, label: h }))]"
                                        @update:model-value="(v: string | number | null) => (mapping[index] = String(v ?? ''))"
                                    />
                                </label>
                            </div>
                            <p v-if="mappingProblem !== null" class="mt-2 text-xs text-risk" role="status">
                                {{ mappingProblem }}
                            </p>
                        </div>

                        <div class="mt-4 grid gap-4 g-2">
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
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <BaseButton type="submit" variant="primary" :disabled="form.processing" :loading="form.processing">
                                {{ form.processing ? 'Reading the file…' : 'Preview import' }}
                            </BaseButton>
                            <LinkButton href="/training-runs" variant="secondary">Cancel</LinkButton>
                        </div>

                        <p class="note mt-4">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                Nothing is written on this step. The preview shows every row the file carries, and
                                each run field can be corrected there before the run exists.
                            </span>
                        </p>
                    </Card>
                </div>

                <div class="flex flex-col gap-4">
                    <Card>
                        <h2>What the importer looks for</h2>
                        <table class="w-full text-sm">
                            <tbody>
                                <tr class="border-b border-rule align-top">
                                    <td class="py-1 pr-3 text-ink-muted">Header row</td>
                                    <td>The first line is the header. This app's own order goes straight in; any other order is mapped in the Source card before the preview.</td>
                                </tr>
                                <tr class="border-b border-rule align-top">
                                    <td class="py-1 pr-3 text-ink-muted">Turn numbers</td>
                                    <td>One row per turn, each number unique within the run.</td>
                                </tr>
                                <tr class="border-b border-rule align-top">
                                    <td class="py-1 pr-3 text-ink-muted">The five stats</td>
                                    <td>Speed, Stamina, Power, Guts and Wit on every row, within the chosen scenario's ceilings.</td>
                                </tr>
                                <tr class="align-top">
                                    <td class="py-1 pr-3 text-ink-muted">Optional cells</td>
                                    <td>sp, condition, energy, mood and fans may stay empty. An empty cell is recorded as empty, never as a default.</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="note mt-3">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                The importer states what it found rather than blocking on what it did not: a sheet
                                with the optional cells empty is a normal outcome, not an error.
                            </span>
                        </p>
                    </Card>

                    <Card>
                        <h2>Files you can upload</h2>
                        <p class="text-sm text-ink-muted">
                            A CSV file, or a plain-text file holding the same rows: the sheet this app exports
                            from a run page. Every row is shown for review before anything is written.
                        </p>
                    </Card>
                </div>
            </form>
        </template>
    </AppLayout>
</template>
