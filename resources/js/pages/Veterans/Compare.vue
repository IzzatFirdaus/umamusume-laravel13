<script setup lang="ts">
/*
 * SCREEN-022, the Veteran comparison (`SCR-VET-004`, plan §8 D16). Up to four filed careers, one property
 * per row, `design-2.0` §46's aligned shape.
 *
 * **Why this screen exists beside the Legacy Lab's compare.** That one compares the *configurations* two
 * runs recorded and refuses a run with no Legacy read-back, because a column of absences looks compared and
 * is not. This one compares *careers*, and the career a Trainer most wants beside another is usually the one
 * they just filed and have built no Legacy on, so refusing it here would delete the subject of the screen.
 * The eligibility rule differs, so the selector does too; the row itself is still `VeteranRow`, the same shape
 * the library list and the detail screen print.
 *
 * **Recorded values only, and the rows the brief asks for that are not recorded are named, not scored.**
 * `SCREEN-022`'s correction row wants inheritance usefulness, a compatibility calculation and a factor
 * comparison. `ADR-0020` §3 keeps those out of the record screens and no table in this repository prices
 * them, so they appear under NOT IN THIS BUILD with their reason. A number in one of those rows would be an
 * invention, and the comparison's whole value is that the Trainer can read four real careers side by side.
 *
 * The selection lives in the query string, so the view is shareable and survives a reload with no client
 * state to rehydrate. The cap is enforced in the picker as a message and on the server as the rule.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Aptitude {
    key: string;
    label: string;
    letter: string | null;
}

interface Column {
    id: number;
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
    veteran_url: string;
    career: { turns: number; energy: number | null; fans: number | null };
    stats: Record<string, number | null>;
    counts: { skills: number; races: number };
    aptitudes: Aptitude[];
}

const props = defineProps<{
    columns: Column[];
    selected: number[];
    max: number;
    comparable: { id: number; label: string }[];
    empty: { message: string; library_url: string } | null;
    aptitude_axes: { key: string; label: string }[];
    notice: string;
    absences: { label: string; reason: string }[];
}>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const picked = ref<string[]>(props.selected.map((id) => String(id)));
const loading = ref(false);

function isChecked(id: number): boolean {
    return picked.value.includes(String(id));
}

function toggle(id: number): void {
    const value = String(id);

    picked.value = isChecked(id)
        ? picked.value.filter((selected) => selected !== value)
        : // The cap is enforced here as a message, not as authority: a fifth tick does nothing rather than
          // posting a selection that comes back as a field error. `VeteranCompareRequest` still decides.
          picked.value.length >= props.max
            ? picked.value
            : [...picked.value, value];
}

function compare(): void {
    router.get(
        '/veterans/compare',
        { veterans: picked.value },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

/** The rows of the table, in the order a Trainer reads a career: who, where, how far, then the numbers. */
const statKeys = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'];

/**
 * One career's letter on one axis, read by key rather than by position. An index here would be a claim that
 * every career publishes the same axes in the same order, and a career whose source published nothing has an
 * empty list, so the position would silently shift every letter one row up.
 */
function letterFor(column: Column, key: string): string | null {
    return column.aptitudes.find((aptitude) => aptitude.key === key)?.letter ?? null;
}
</script>

<template>
    <AppLayout>
        <Head title="Compare Veterans" />
        <template #title>Compare Veterans</template>

        <p v-if="loading" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>

        <p class="text-sm text-ink-muted">{{ props.notice }}</p>

        <fieldset class="mt-4 rounded-md border border-rule bg-panel p-4">
            <legend class="text-sm font-semibold text-ink-strong">Choose up to {{ props.max }}</legend>
            <p id="picker-help" class="text-xs text-ink-muted">
                Only careers already filed in the library can be compared. A run that has not been saved has
                no row here to name.
            </p>

            <p v-if="props.comparable.length === 0" class="mt-2 text-sm text-ink" role="status">
                The library is empty, so there is nothing to choose. File a career from its Result screen
                first.
            </p>

            <ul v-else class="mt-2 grid gap-1 sm:grid-cols-2">
                <li v-for="option in props.comparable" :key="option.id">
                    <label :for="`pick-${option.id}`" class="inline-flex min-h-11 items-center gap-2 text-sm text-ink">
                        <input
                            :id="`pick-${option.id}`"
                            type="checkbox"
                            class="h-6 w-6 rounded border border-rule bg-raised"
                            :checked="isChecked(option.id)"
                            :disabled="!isChecked(option.id) && picked.length >= props.max"
                            @change="toggle(option.id)"
                        >
                        {{ option.label }}
                    </label>
                </li>
            </ul>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    class="enamel inline-flex h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:opacity-50"
                    :disabled="picked.length === 0"
                    @click="compare"
                >
                    Compare
                </button>
                <p v-if="picked.length >= props.max" class="text-xs text-ink-muted">
                    {{ props.max }} is the most this view aligns in one screen. Deselect one to swap another in.
                </p>
            </div>

            <ul v-if="Object.keys(errors).length > 0" class="mt-2 space-y-1">
                <li
                    v-for="(message, field) in errors"
                    :key="field"
                    role="alert"
                    class="rounded border border-risk bg-raised px-3 py-1 text-sm text-risk"
                >
                    {{ field }}: {{ message }}
                </li>
            </ul>
        </fieldset>

        <section
            v-if="props.empty !== null"
            aria-labelledby="compare-empty"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="compare-empty" class="text-base font-semibold text-ink-strong">Nothing selected</h2>
            <p class="mt-1 text-sm text-ink">{{ props.empty.message }}</p>
            <a
                :href="props.empty.library_url"
                class="mt-3 inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm text-ink hover:bg-sunken"
            >
                Open the library
            </a>
        </section>

        <section v-else aria-labelledby="compare-table" class="mt-4">
            <h2 id="compare-table" class="text-base font-semibold text-ink-strong">
                {{ props.columns.length }} career{{ props.columns.length === 1 ? '' : 's' }} side by side
            </h2>

            <!-- §46: identical properties aligned vertically, in one scrollable region, never two cards the
                 Trainer has to hold in memory beside each other. -->
            <div role="region" tabindex="0" aria-label="Career comparison, scrollable" class="mt-2 overflow-x-auto">
                <table class="w-full min-w-max border-collapse text-sm">
                    <caption class="pb-2 text-left text-xs text-ink-muted">
                        Recorded values only. A blank cell means the career never recorded it, and its tooltip says which.
                    </caption>
                    <thead>
                        <tr class="border-b border-rule">
                            <th scope="col" class="p-2 text-left text-xs font-semibold uppercase text-ink-muted">Property</th>
                            <th v-for="column in props.columns" :key="`head-${column.id}`" scope="col" class="p-2 text-left">
                                <a :href="column.veteran_url" class="font-semibold text-ink underline">{{ column.trainee }}</a>
                                <span v-if="column.trainee_ja" lang="ja" class="block text-xs text-ink-muted">{{ column.trainee_ja }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Scenario</th>
                            <td v-for="column in props.columns" :key="`scenario-${column.id}`" class="p-2 text-ink">{{ column.scenario_label }}</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Status</th>
                            <td v-for="column in props.columns" :key="`status-${column.id}`" class="p-2 text-ink">{{ column.status_label }}</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Turns logged</th>
                            <td v-for="column in props.columns" :key="`turns-${column.id}`" class="p-2 text-ink">{{ column.career.turns }}</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Energy</th>
                            <td v-for="column in props.columns" :key="`energy-${column.id}`" class="p-2 text-ink">
                                <template v-if="column.career.energy !== null">{{ column.career.energy }}</template>
                                <span v-else title="No logged turn on this career carries an end-of-turn Energy figure.">N/A</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Fans</th>
                            <td v-for="column in props.columns" :key="`fans-${column.id}`" class="p-2 text-ink">
                                <template v-if="column.career.fans !== null">{{ column.career.fans }}</template>
                                <span v-else title="No logged turn on this career carries an end-of-turn Fans figure.">N/A</span>
                            </td>
                        </tr>

                        <tr v-for="key in statKeys" :key="`stat-${key}`" class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">{{ key }}</th>
                            <td v-for="column in props.columns" :key="`stat-${key}-${column.id}`" class="p-2 text-ink">
                                <template v-if="column.stats[key] !== null">{{ column.stats[key] }}</template>
                                <span v-else title="This career logged no turn, so it recorded no value for this stat.">N/A</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <!-- Counted, not filtered: the pivot holds Suggested and Skipped rows and
                                 `race_entries` holds NotOffered and Skipped rows, so these are the records a
                                 career carries rather than the skills it earned or the races it ran. See
                                 `SCR-CAR-018`, which rules a skipped race is not a race the career ran. -->
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Skills recorded</th>
                            <td v-for="column in props.columns" :key="`skills-${column.id}`" class="p-2 text-ink">{{ column.counts.skills }}</td>
                        </tr>
                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Races entered</th>
                            <td v-for="column in props.columns" :key="`races-${column.id}`" class="p-2 text-ink">{{ column.counts.races }}</td>
                        </tr>

                        <tr v-for="axis in props.aptitude_axes" :key="`apt-${axis.key}`" class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">{{ axis.label }}</th>
                            <td v-for="column in props.columns" :key="`apt-${axis.key}-${column.id}`" class="p-2 font-mono text-ink">
                                <template v-if="letterFor(column, axis.key) !== null">{{ letterFor(column, axis.key) }}</template>
                                <span v-else title="No source published this trainee's aptitude letter on this axis.">N/A</span>
                            </td>
                        </tr>

                        <tr class="border-b border-rule">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Tags</th>
                            <td v-for="column in props.columns" :key="`tags-${column.id}`" class="p-2">
                                <ul v-if="column.tags.length > 0" class="flex flex-wrap gap-1">
                                    <li v-for="tag in column.tags" :key="`${column.id}-${tag}`" class="rounded-full border border-rule bg-sunken px-2 text-xs text-ink">{{ tag }}</li>
                                </ul>
                                <span v-else class="text-xs text-ink-muted" title="The Trainer filed this career without tagging it.">No tags recorded</span>
                            </td>
                        </tr>
                        <tr class="border-b border-rule align-top">
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Note</th>
                            <td v-for="column in props.columns" :key="`notes-${column.id}`" class="p-2 text-ink">
                                <template v-if="column.notes !== null">{{ column.notes }}</template>
                                <span v-else title="No note was recorded on this career.">N/A</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="p-2 text-left font-medium text-ink-muted">Legacy read-back</th>
                            <td v-for="column in props.columns" :key="`legacy-${column.id}`" class="p-2 text-ink">
                                <a v-if="column.builder_url" :href="column.builder_url" class="underline">Open its graph</a>
                                <span v-else title="This career records no Legacy configuration, so it has no graph to open.">None recorded</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="compare-absences" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="compare-absences" class="text-base font-semibold text-ink-strong">NOT IN THIS BUILD</h2>
            <ul class="mt-2 space-y-2 text-sm">
                <li v-for="absence in props.absences" :key="absence.label">
                    <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                </li>
            </ul>
        </section>
    </AppLayout>
</template>
