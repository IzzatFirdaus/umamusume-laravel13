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
 * order with no arrow-key handler, stating which row holds the stored choice after the PUT round trip. The
 * row's own profile link is a separate control, because SCREEN-004 exists precisely so a Trainer can read
 * more before committing.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import RarityChip from '../../components/RarityChip.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import AptitudeBadge from '../../components/AptitudeBadge.vue';
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
}>();

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
const selectForm = useForm<{ umamusume_id: number; character_card_id: number | null }>({
    umamusume_id: 0,
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
const styleAxes = computed(() => props.aptitudeAxes.filter((axis) => axis.axis === 'style'));


function applyFilters(): void {
    router.get(
        '/career/setup/trainee',
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
    chosen.value = trainee.id;
    selectForm.umamusume_id = trainee.id;
    selectForm.character_card_id = pickedForm.value[trainee.id] ?? null;

    // Literal URL, as every page here states it: there is no Ziggy in this app, so `router.route()` would
    // throw at click time rather than at build time. Focus is restored in `onSuccess` after the new props
    // land, because the component instance survives a client-side visit and `onMounted` would not re-run
    // (WCAG 2.4.3).
    selectForm.put('/career/setup/trainee', {
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
    <SetupLayout :step="2" :visited="props.visited">
        <Head title="Choose a trainee" />
        <template #title>Choose a trainee</template>

        <p class="max-w-2xl text-sm text-ink-muted">
            The roster is the catalog this tool has fetched. Pick the trainee the career is built on; the
            profile screen behind each row states what is known about her before committing.
        </p>

        <p class="mt-3 text-sm text-ink">
            Scenario:
            <strong v-if="props.scenarioLabel" class="text-ink-strong">{{ props.scenarioLabel }}</strong>
            <span v-else class="text-ink-muted" title="No scenario has been chosen in this setup draft yet. Set it on step 1.">
                N/A
            </span>
            <Link
                href="/career/setup/scenario"
                class="ml-3 inline-flex min-h-11 items-center rounded-md px-2 text-sm text-ink underline"
            >
                Change scenario
            </Link>
        </p>

        <p class="mt-1 text-sm text-ink">
            Stored choice:
            <strong v-if="props.selectedName" class="text-ink-strong">{{ props.selectedName }}</strong>
            <span v-else class="text-ink-muted" title="No trainee has been chosen in this setup draft yet.">N/A</span>
        </p>

        <form class="mt-5 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Search</span>
                <input
                    v-model="filterValues.search"
                    type="text"
                    name="search"
                    placeholder="Trainee name"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
            </label>

            <label v-for="facet in props.facets" :key="facet.param" class="flex flex-col gap-1">
                <span class="text-ink-muted">{{ facet.label }}</span>
                <select
                    :id="`filter-${facet.param}`"
                    v-model="filterValues[facet.param]"
                    :name="facet.param"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">Any</option>
                    <option v-for="letter in facet.letters" :key="letter" :value="letter">{{ letter }}</option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Unique skill</span>
                <select
                    v-model="filterValues.skill"
                    name="skill"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">Any</option>
                    <option v-for="option in props.uniqueSkills" :key="option.export_id" :value="String(option.export_id)">
                        {{ option.name }}
                    </option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Sort by</span>
                <select v-model="sortBy" name="sortBy" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    <option v-for="option in props.sortKeys" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Order</span>
                <!-- Neutral words, because the control means two different things depending on the sort
                     key: name order, or best-letter-first on an aptitude column. The title says which. -->
                <select
                    v-model="direction"
                    name="direction"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                    title="Ascending orders names A to Z and aptitudes from the best letter, S, down to G."
                >
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
            </label>

            <button
                type="submit"
                class="enamel inline-flex h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
            >
                Filter
            </button>
            <button
                type="button"
                class="inline-flex h-11 items-center rounded-full border border-rule bg-raised px-4 text-sm font-medium text-ink hover:bg-sunken"
                @click="clearFilters"
            >
                Clear filters
            </button>
        </form>

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
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            No trainee matches these filters. The roster is filled by seed data or `php artisan uma:fetch`;
            clearing the filters shows every row the catalog holds.
        </p>

        <ul v-else class="mt-6 max-w-3xl space-y-4">
            <li
                v-for="trainee in props.trainees.data"
                :key="trainee.id"
                class="rounded-md border border-rule bg-panel p-4"
                :class="props.selected === trainee.id ? 'border-2 border-chrome' : ''"
            >
                <h2 class="flex flex-wrap items-center gap-3">
                    <!-- The header already prints her name beside the frame, so the image is decorative and
                         takes `alt=""` (DESIGN.md §4.7), and `reserve` holds the cell so every name starts
                         at one x whether or not the mirror happens to hold the file. `size-10` is the
                         §45a row geometry for a list row that already carries the name. -->
                    <ArtworkSlot :url="trainee.artworkURL" alt="" size="size-10" reserve />
                    <span class="text-lg font-semibold text-ink-strong">{{ trainee.name }}</span>
                    <span v-if="trainee.name_ja" lang="ja" class="text-sm font-normal text-ink-muted">
                        {{ trainee.name_ja }}
                    </span>
                    <span class="ml-auto text-xs text-ink-muted">{{ trainee.release_status_label }}</span>
                </h2>

                <!-- Aptitude is the only trainee-side signal the schema holds, so the badge prints the
                     letter and its band as two words and never a colour alone (`design-2.0` §17). Where no
                     letter has been published the whole grid is replaced by one sentence, which is the
                     same read `CatalogController::aptitudes()` makes of the all-or-nothing write. -->
                <p v-if="trainee.aptitudes === null" class="mt-3 text-sm text-ink-muted" title="The character document published no aptitude letters for this trainee.">
                    Aptitude: N/A
                </p>
                <div v-else class="mt-3 flex flex-wrap gap-1.5">
                    <AptitudeBadge
                        v-for="cell in primaryAxes"
                        :key="cell.key"
                        :label="cell.label"
                        :letter="trainee.aptitudes[cell.key] ?? null"
                    />
                    <!-- The style axis is the density a selection screen does not owe at full weight;
                         the summary carries the count, and every badge keeps its letter and its word
                         in the DOM whether the disclosure is open or closed. -->
                    <details v-if="styleAxes.length > 0">
                        <summary class="inline-flex min-h-11 cursor-pointer items-center rounded-md border border-rule px-2 text-xs text-ink-muted hover:bg-raised">
                            Show all aptitudes ({{ styleAxes.length }})
                        </summary>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <AptitudeBadge
                                v-for="cell in styleAxes"
                                :key="cell.key"
                                :label="cell.label"
                                :letter="trainee.aptitudes[cell.key] ?? null"
                            />
                        </div>
                    </details>
                </div>

                <p class="mt-3 text-sm text-ink-muted">
                    {{ trainee.form_count }} {{ formCountLabel(trainee.form_count) }}
                    <template v-if="trainee.forms.length > 0">
                        <span v-for="card in trainee.forms" :key="card.id" class="ml-3 whitespace-nowrap">
                            <!-- Rarity is this form's own fact. There is no trainee-level rarity column, so
                                 nothing here aggregates it. -->
                            <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                            <span class="ml-1 font-mono text-xs">{{ card.rarity_word }}</span>
                            <span v-if="card.is_debut_form" class="ml-1">debut form</span>
                        </span>
                    </template>
                </p>

                <!-- R3-06: a trainee who holds more than one costume form is asked which one the career
                     starts on. A single-form trainee is not asked: one row states a fact rather than
                     offering a choice, and "Not stated" stays a real answer rather than a default. -->
                <label
                    v-if="trainee.forms.length > 1"
                    class="mt-3 flex flex-col gap-1 text-sm"
                    :for="`trainee-form-${trainee.id}`"
                >
                    <span class="text-ink-muted">Costume form for this career</span>
                    <select
                        :id="`trainee-form-${trainee.id}`"
                        v-model="pickedForm[trainee.id]"
                        class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <option :value="null">Not stated</option>
                        <option v-for="card in trainee.forms" :key="card.id" :value="card.id">
                            {{ card.title }}{{ card.is_debut_form ? ' (debut form)' : '' }}
                        </option>
                    </select>
                </label>

                <p class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        :id="`trainee-${trainee.id}`"
                        type="button"
                        class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                        :aria-pressed="props.selected === trainee.id ? 'true' : 'false'"
                        :disabled="selectForm.processing"
                        @click="choose(trainee)"
                    >
                        {{ chosen === trainee.id ? 'Saving…' : 'Select Trainee' }}
                    </button>
                    <a
                        :href="`/career/setup/trainee/${trainee.id}`"
                        class="inline-flex min-h-11 items-center rounded-full border border-rule px-4 text-sm font-medium text-ink hover:bg-raised"
                    >
                        View Profile
                    </a>
                    <!-- The stored state is a word beside the action, never inside the row's `<h2>`: in the
                         heading it would join the accessible name, so her name would stop matching itself. -->
                    <span v-if="props.selected === trainee.id" class="text-xs font-bold text-ink-muted">Chosen</span>
                </p>
            </li>
        </ul>

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
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-raised"
                    :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                    v-html="link.label"
                />
                <span v-else class="px-3 py-1 text-ink-muted" v-html="link.label" />
            </template>
        </nav>
    </SetupLayout>
</template>
