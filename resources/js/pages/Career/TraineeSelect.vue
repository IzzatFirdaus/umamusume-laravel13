<script setup lang="ts">
/*
 * SCREEN-003, Trainee Selection (`ADR-0020` §1; `docs/proposals/screen-spec-2.0.md` §6). Step 2 of the
 * setup wizard.
 *
 * **The page prints props and decides nothing.** Every filter vocabulary, every column name and the letter
 * domain arrive from `TraineeSelectController`, which reads them off `TraineeSearchRequest`, which reads
 * them off the schema. That chain is why there is no facet list, no letter list and no scenario name in
 * this file: `CareerTraineeSelectTest` greps for exactly that.
 *
 * **Two of the brief's filters are absent, by data rather than by choice.** Growth rate has no column
 * anywhere (`docs/research-scratch/GOVERNANCE.md:600` defers it, "No column added now") and scenario
 * suitability has no table: `config/scenarios.php`'s `scenario_links` is a cast list, not a ranking. Both
 * reasons are on `TraineeSelectController`'s docblock, and the card prints no "Speed +20%" line because
 * there is no such datum to print. Rarity is stated **per costume form**, since `character_cards.rarity`
 * is the only rarity column and a trainee-level badge would be an aggregate presented as a fact.
 *
 * Selection is the same card-button pattern as step 1: a native `<button>` with `aria-pressed`, in the tab
 * order with no arrow-key handler, stating which row holds the stored choice after the PUT round trip. That
 * one action is a toggle, and its own label says which way the next click goes; the state word lives inside
 * the button rather than beside it, so the row never prints it twice. The row's own profile link is a
 * separate control, because SCREEN-004 exists precisely so a Trainer can read more before committing.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import AptitudeBadge from '../../components/AptitudeBadge.vue';
import DataTable from '../../components/ui/DataTable.vue';
import type { DataTableColumn } from '../../components/ui/data-table-types';
import { BaseInput, BaseSelect, BaseButton } from '../../components/Form';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

interface FormRow {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    is_debut_form: boolean;
}

interface TraineeRow {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    form_count: number;
    artworkURL: string | null;
    /** The trainee's own ten letters, or null when the source published none. */
    aptitudes: Record<string, string> | null;
    forms: FormRow[];
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

interface Facet {
    param: string;
    label: string;
    letters: string[];
}

interface Option {
    value: string;
    label: string;
}

interface SkillOption {
    export_id: number;
    name: string;
}

const props = defineProps<{
    trainees: Paginator<TraineeRow>;
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    choices: { label: string; value: string | null; url: string | null }[];
    selected: number | null;
    /** The costume form the draft already names, so a return to this step re-seeds the picker (R3-06). */
    selectedCard: number | null;
    /**
     * The stored trainee's name, sent with the selection. The readout must not derive it from
     * `trainees.data`: the roster is paginated and filtered, so the chosen trainee is often not on the
     * page shown, and a lookup there answered `N/A` beside a flash saying she was set (D5).
     */
    selectedName: string | null;
    scenarioLabel: string | null;
    scenarioPending: boolean;
    filters: {
        search: string | null;
        surface: string | null;
        distance: string | null;
        style: string | null;
        skill: number | null;
    };
    sortBy: string;
    direction: string;
    facets: Facet[];
    sortKeys: Option[];
    /** The ten aptitude axes with the family each belongs to; the controller is their one owner. */
    aptitudeAxes: { value: string; label: string; axis: string }[];
    uniqueSkills: SkillOption[];
    /** `route('career.trainee.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
    /** `route('career.trainee')`, supplied by the server so the filter never hard-codes the URL. */
    rosterUrl: string;
}>()

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

/**
 * A refusal is announced in the words the control it belongs to already prints. `aptitude_speed: That
 * rating is not one of S to G` hands the Trainer a schema to read instead of a form to fix, and the
 * message from `TraineeSearchRequest` is already plain; only the key was not.
 */
const filterLabels = computed(() =>
    Object.fromEntries(props.facets.map((facet) => [facet.param, facet.label])),
);

const STATIC_FILTER_LABELS: Record<string, string> = {
    search: 'Search',
    skill: 'Unique skill',
    sortBy: 'Sort by',
    direction: 'Order',
};

function errorLabel(field: string): string {
    return filterLabels.value[field] ?? STATIC_FILTER_LABELS[field] ?? 'This filter';
}

// Mirrored from the resolved props rather than from the URL, so a filter the server refused (and therefore
// ignored) never stays highlighted on the form as though it had been applied. The three aptitude facets
// share one keyed record because the facet list arrives from the server, so the form must bind by key
// rather than by a name written into this file.
const filterValues = ref<Record<string, string>>({
    search: props.filters.search ?? '',
    surface: props.filters.surface ?? '',
    distance: props.filters.distance ?? '',
    style: props.filters.style ?? '',
    skill: props.filters.skill === null ? '' : String(props.filters.skill),
});
const sortBy = ref(props.sortBy);
const direction = ref(props.direction);
const loading = ref(false);

const chosen = ref<number | null>(null);
const selectForm = useForm<{ umamusume_id: number | null; character_card_id: number | null }>({
    umamusume_id: null,
    character_card_id: null,
});
// Which form the Trainer means for each trainee row, kept per row so browsing the roster does not carry
// one trainee's pick onto another. Null is "no form stated", which is what a single-form trainee is.
const pickedForm = ref<Record<number, number | null>>(
    props.selected === null || props.selectedCard === null ? {} : { [props.selected]: props.selectedCard },
);

// Track and distance read at full weight; the style axis sits behind the disclosure (owner ruling
// 2026-10-13, audit §3.4 option 1). Both halves come from the controller's one list, so a tenth
// axis arrives here without an edit.
const primaryAxes = computed(() => props.aptitudeAxes.filter((axis) => axis.axis !== 'style'));

/**
 * The table's columns (plan 3.4).
 *
 * Each key names a slot the template fills, except `trainee` which renders the portrait and the name
 * together. `manual` is deliberately not a column: the table treats a missing cell as "Not recorded",
 * and a control is neither present nor absent, so the select lives inside the last column's cell
 * instead of claiming a column of its own.
 */
const traineeColumns: DataTableColumn[] = [
    { key: 'trainee', label: 'Trainee' },
    { key: 'aptitudes', label: 'Aptitudes' },
    { key: 'forms', label: 'Forms', secondary: true },
    { key: 'select', label: 'Choose' },
];


function applyFilters(): void {
    router.get(
        props.rosterUrl,
        {
            search: filterValues.value.search || undefined,
            surface: filterValues.value.surface || undefined,
            distance: filterValues.value.distance || undefined,
            style: filterValues.value.style || undefined,
            skill: filterValues.value.skill || undefined,
            sortBy: sortBy.value || undefined,
            direction: direction.value || undefined,
        },
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

function clearFilters(): void {
    filterValues.value = { search: '', surface: '', distance: '', style: '', skill: '' };
    sortBy.value = 'name';
    direction.value = 'asc';

    applyFilters();
}

function choose(trainee: TraineeRow): void {
    // The row is a toggle, not a radio: this screen already renders `Stored choice: N/A`, so "no trainee"
    // is a state a Trainer can be in, and a state you can enter has to be exitable. Clicking the chosen
    // row again sends the explicit null the draft reads as a clear.
    const deselect = props.selected === trainee.id;
    chosen.value = trainee.id;
    selectForm.umamusume_id = deselect ? null : trainee.id;
    selectForm.character_card_id = deselect ? null : (pickedForm.value[trainee.id] ?? null);

    // The write URL is supplied by the server (`career.trainee.store`) so the page never states it as
    // a literal. Focus is restored in `onSuccess` after the new props land, because the component
    // instance survives a client-side visit and `onMounted` would not re-run (WCAG 2.4.3).
    selectForm.put(props.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => {
                document.getElementById(`trainee-${trainee.id}`)?.focus();
            });
        },
        onFinish: () => {
            chosen.value = null;
        },
    });
}

const formCountLabel = (count: number): string => (count === 1 ? 'costume form' : 'costume forms');
</script>

<template>
    <SetupLayout :step="2" :visited="props.visited" :saved="props.saved" :choices="props.choices">
        <Head title="Choose a trainee" />
        <template #eyebrow>Setup</template>
        <template #title>Choose a trainee</template>

        <!-- No breadcrumb: "← Back to trainee" pointed at this same URL. The step bar above lists all
             six steps, and the carried-choices strip states the scenario, so nothing is lost. -->

        <!-- Header: eyebrow + subtitle. The page title is the layout's #title h1. -->
        <header class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-accent-ink">Step 2 of 6</p>
            <p class="mt-1 text-sm text-ink-muted">Pick the trainee the career is built on.</p>
        </header>

        <p class="max-w-3xl text-sm text-ink-muted">
            The roster is the catalog this tool has fetched. Pick the trainee the career is built on; the
            profile screen behind each row states what is known about her before committing.
        </p>

        <!-- The scenario is stated once, by the carried-choices strip above (slice 3.2). This page
             used to repeat it here with its own "Change scenario" link; the strip's entry is that link. -->

        <!-- Filter bar in bordered container -->
        <div class="mt-5 rounded-md border border-rule bg-raised p-4">
            <form class="mx-auto w-full max-w-4xl flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
                <BaseInput
                    v-model="filterValues.search"
                    type="text"
                    id="filter-search"
                    name="search"
                    placeholder="Trainee name"
                    label="Search"
                />
                <BaseSelect
                    v-for="facet in props.facets"
                    :key="facet.param"
                    v-model="filterValues[facet.param]"
                    :id="`filter-${facet.param}`"
                    :name="facet.param"
                    :label="facet.label"
                    :options="facet.letters.map(l => ({ value: l, label: l }))"
                    placeholder="Any"
                />
                <BaseSelect
                    v-model="filterValues.skill"
                    id="filter-skill"
                    name="skill"
                    label="Unique skill"
                    :options="props.uniqueSkills.map(o => ({ value: String(o.export_id), label: o.name }))"
                    placeholder="Any"
                />
                <BaseSelect
                    v-model="sortBy"
                    id="filter-sortBy"
                    name="sortBy"
                    label="Sort by"
                    :options="props.sortKeys.map(o => ({ value: o.value, label: o.label }))"
                />
                <BaseSelect
                    v-model="direction"
                    id="filter-direction"
                    name="direction"
                    label="Order"
                    :options="[{ value: 'asc', label: 'Ascending' }, { value: 'desc', label: 'Descending' }]"
                    :aria-describedby="filter-direction"
                />
                <BaseButton type="submit" variant="secondary">
                    Filter
                </BaseButton>
                <BaseButton type="button" variant="secondary" @click="clearFilters">
                    Clear filters
                </BaseButton>
            </form>

            <!-- Row-count line -->
            <p v-if="props.trainees.total > 0" class="mt-2 text-xs text-ink-muted">
                {{ props.trainees.total }} trainee{{ props.trainees.total !== 1 ? 's' : '' }} match{{ props.trainees.total !== 1 ? 's' : '' }}.
            </p>
        </div>

        <p v-if="loading" role="status" class="mt-3 text-sm text-ink-muted">Loading the roster…</p>

        <!-- A refused filter is announced where it was typed. The three aptitude facets share one message
             because the domain is one thing (S to G), and the skill filter names its own field. -->
        <ul v-if="Object.keys(errors).length > 0" class="mt-3 space-y-1">
            <li
                v-for="(message, field) in errors"
                :key="field"
                role="alert"
                class="rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
            >
                {{ errorLabel(field) }}: {{ message }}
            </li>
        </ul>

        <p v-if="selectForm.processing" role="status" class="mt-3 text-sm text-ink-muted">Saving your choice…</p>

        <p
            v-if="props.trainees.data.length === 0 && !loading"
            class="mt-8 rounded-lg border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted"
        >
            No trainee matches these filters. The roster is filled by seed data or `php artisan uma:fetch`;
            clearing the filters shows every row the catalog holds.
        </p>

        <!--
          A table, not 25 stacked cards (plan 3.4). The cards repeated the aptitude grid and the form
          list on every row, so comparing two trainees meant scrolling past twenty-four blocks of
          chrome. A row per trainee puts the comparable facts in columns: the portrait, her name, the
          three primary aptitudes, how many forms she has, and the control that picks her.

          The aptitude and form columns render through the table's cell slots, because chips, an
          artwork frame and a select are not text. The costume-form select stays in this page rather
          than the profile: a career starts on one form, and that is a choice about THIS career, not a
          fact about the trainee.
        -->
        <DataTable
            v-else
            class="mt-6"
            caption="Trainees in the catalog"
            :columns="traineeColumns"
            :rows="props.trainees.data"
        >
            <template #cell-trainee="{ row }">
                <div class="flex items-center gap-3">
                    <!-- The name is in the next cell, so the image is decorative and takes `alt=""`
                         (DESIGN.md §4.7), and `reserve` holds the frame so every name starts at one x
                         whether or not the mirror holds the file. -->
                    <ArtworkSlot :url="row.artworkURL" alt="" size="size-10" reserve />
                    <div>
                        <span class="text-sm font-semibold text-ink-strong">{{ row.name }}</span>
                        <span v-if="row.name_ja" lang="ja" class="ml-2 text-sm text-ink-muted">{{ row.name_ja }}</span>
                    </div>
                </div>
            </template>

            <template #cell-aptitudes="{ row }">
                <div v-if="row.aptitudes === null" class="flex items-baseline gap-1 text-sm text-ink-muted">
                    <AbsenceValue compact reason="The character document published no aptitude letters for this trainee." />
                </div>
                <div v-else class="flex flex-wrap gap-1.5">
                    <!-- `design-2.0` §17: the badge prints the letter and its band as two words, never
                         a colour alone. -->
                    <AptitudeBadge
                        v-for="cell in primaryAxes"
                        :key="cell.value"
                        :label="cell.label"
                        :letter="row.aptitudes[cell.value] ?? null"
                    />
                </div>
            </template>

            <template #cell-forms="{ row }">
                <span class="whitespace-nowrap">{{ row.form_count }} {{ formCountLabel(row.form_count) }}</span>
                <!-- R3-06: the costume form is asked only where there is a choice to make. One row
                     states a fact, and "Not stated" stays a real answer rather than a default. -->
                <label
                    v-if="row.forms.length > 1"
                    class="mt-1 flex flex-col gap-1 text-xs"
                    :for="`trainee-form-${row.id}`"
                >
                    <span class="text-ink-muted">Costume form</span>
                    <BaseSelect
                        :id="`trainee-form-${row.id}`"
                        v-model="pickedForm[row.id]"
                        :options="[{ value: null, label: 'Not stated' }, ...row.forms.map((card: { id: number; title: string; is_debut_form: boolean }) => ({ value: card.id, label: `${card.title}${card.is_debut_form ? ' (debut form)' : ''}` }))]"
                    />
                </label>
            </template>

            <template #cell-select="{ row }">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        :id="`trainee-${row.id}`"
                        type="button"
                        :class="[
                            'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-bold disabled:cursor-wait disabled:bg-disabled',
                            props.selected === row.id
                                ? 'bg-chrome text-on-chrome'
                                : 'border border-rule bg-raised text-ink hover:bg-sunken transition-colors duration-150 ease-out'
                        ]"
                        :aria-pressed="props.selected === row.id ? 'true' : 'false'"
                        :disabled="selectForm.processing"
                        @click="choose(row)"
                    >
                        {{ props.selected === row.id ? 'Chosen · clear' : 'Select' }}
                    </button>
                    <a
                        :href="`/career/setup/trainee/${row.id}`"
                        class="inline-flex min-h-11 items-center text-sm font-medium text-ink underline"
                    >
                        Profile
                    </a>
                </div>
            </template>
        </DataTable>

        <nav
            v-if="props.trainees.links.length > 3"
            aria-label="Pagination"
            class="mt-4 flex flex-wrap items-center gap-2 text-sm"
        >
            <span class="mr-2 text-ink-muted">{{ props.trainees.total }} trainees match.</span>
            <template v-for="(link, index) in props.trainees.links" :key="index">
                <a
                    v-if="link.url"
                    :href="link.url"
                    :aria-current="link.active ? 'page' : undefined"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-raised transition-colors duration-150 ease-out"
                    :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                    v-html="link.label"
                />
                <span v-else class="px-3 py-1 text-ink-muted" v-html="link.label" />
            </template>
        </nav>
    </SetupLayout>
</template>
