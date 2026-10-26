<script setup lang="ts">
/*
 * SCREEN-003, Trainee Selection (`ADR-0020` §1; `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` §6). Step 2 of the
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
    /** `route('career.target')`, the step this screen advances to. */
    targetUrl: string;
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

/**
 * The table's columns (plan 3.4).
 *
 * Each key names a slot the template fills, except `trainee` which renders the portrait and the name
 * together. `manual` is deliberately not a column: the table treats a missing cell as "Not recorded",
 * and a control is neither present nor absent, so the select lives inside the last column's cell
 * instead of claiming a column of its own.
 */
/*
 * The card's three aptitude rows (`career-trainee.html:127-129`).
 *
 * The wireframe draws Surface, Distance and Style as one row each, with the strongest letter of the row in
 * bold. The ten axes arrive from the controller already carrying the family they belong to, so grouping by
 * `axis` reproduces those three rows without this file naming an axis or a family: a tenth axis, or a
 * re-lettering, changes the card without an edit here.
 *
 * The row's own name is the family the controller sent, capitalised by CSS (`career-trainee.html:127-129`
 * prints "Surface", "Distance" and "Style"). The filter bar's labels are its own vocabulary and are not
 * reused here: the facet calls the style family "Running style", which is the right name for a filter and
 * the wrong one for a card row three words wide.
 */
const axisRows = computed(() =>
    props.aptitudeAxes.reduce<Record<string, typeof props.aptitudeAxes>>((rows, axis) => {
        (rows[axis.axis] ??= []).push(axis);

        return rows;
    }, {}),
);


/** S is the best letter and G the worst, so the row's strongest is its lowest rank, not its first. */
const LETTER_RANK: Record<string, number> = { S: 0, A: 1, B: 2, C: 3, D: 4, E: 5, F: 6, G: 7 };

function strongest(aptitudes: Record<string, string> | null, axes: { value: string }[]): string | null {
    if (aptitudes === null) {
        return null;
    }

    return axes.reduce<string | null>((best, axis) => {
        const letter = aptitudes[axis.value] ?? null;

        if (letter === null) {
            return best;
        }

        if (best === null || (LETTER_RANK[letter] ?? 99) < (LETTER_RANK[best] ?? 99)) {
            return letter;
        }

        return best;
    }, null);
}


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


/** The wireframe's pill reads "1 form" / "2 forms", which is the count alone rather than the noun. */
const formPillLabel = (count: number): string => (count === 1 ? '1 form' : `${count} forms`);

/** The form the card's rarity pill states: the one the draft names, else the first the catalog lists. */
function headlineForm(row: TraineeRow): FormRow | null {
    const picked = pickedForm.value[row.id] ?? null;

    return row.forms.find((form) => form.id === picked) ?? row.forms[0] ?? null;
}

/**
 * The stored pick, as the "Selected" card at the foot of the roster states it (`career-trainee.html:191-200`).
 *
 * Read from `selectedName` rather than from `trainees.data`: the roster is filtered and paginated, so the
 * chosen trainee is often not on the page, and a lookup there used to answer `N/A` beside a flash saying she
 * was set (D5). The aptitude sentence and the form title are refinements: they print only when the row is
 * on the page shown, and their absence leaves the name and the link intact.
 */
const selectedRow = computed<TraineeRow | null>(
    () => props.trainees.data.find((trainee) => trainee.id === props.selected) ?? null,
);

const selectedSummary = computed<string | null>(() => {
    const row = selectedRow.value;

    if (row === null || row.aptitudes === null) {
        return null;
    }

    // The strongest letter of each family, named the way the card's own rows name them, in the order the
    // wireframe prints them: distance, then track, then the running style.
    const strongestOf = (axis: string): string | null => {
        const axes = axisRows.value[axis] ?? [];
        const letter = strongest(row.aptitudes, axes);
        const named = axes.find((candidate) => row.aptitudes?.[candidate.value] === letter);

        return named === undefined || letter === null ? null : `${named.label} ${letter}`;
    };

    return [strongestOf('distance'), strongestOf('surface'), strongestOf('style')]
        .filter((part): part is string => part !== null)
        .join(' · ');
});
</script>

<template>
    <SetupLayout :step="2" :visited="props.visited" :saved="props.saved" :choices="props.choices">
        <Head title="Choose a trainee" />
        <template #eyebrow>Setup</template>
        <template #title>Choose a trainee</template>

        <!-- No breadcrumb: "Back to trainee" pointed at this same URL. The step bar above lists all
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

        <p v-if="loading" role="status" class="mt-3 text-sm text-ink-muted">Loading the roster...</p>

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

        <p v-if="selectForm.processing" role="status" class="mt-3 text-sm text-ink-muted">Saving your choice...</p>

        <p
            v-if="props.trainees.data.length === 0 && !loading"
            class="mt-8 rounded-lg border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted"
        >
            No trainee matches these filters. The roster is filled by seed data or `php artisan uma:fetch`;
            clearing the filters shows every row the catalog holds.
        </p>

        <!--
          One card per trainee, three to a row (`career-trainee.html:111-181`).

          The card states the facts the wireframe states, in its order: the pick control, the portrait, the
          name with her Japanese name and the form beside it, the rarity / form-count / release pills, and the
          three aptitude rows with the strongest letter of each in bold. A card rather than a table because
          the row is chosen, not read down a column, and the aptitude comparison the table once offered is
          still there: the three rows sit at the same height in every card of a row.

          The whole card is clickable, which is the wireframe's `<label>` affordance, but the control inside
          is a real `<button>` with `aria-pressed`: the choice is a toggle, because "no trainee" is a state
          the draft can hold and a state you can enter has to be exitable. The button keeps a 44px hit area
          and draws the small disc the wireframe draws, so the target is large and the affordance is the one
          the design shows. Its words live in an `sr-only` span: the card's own text names the trainee, so a
          visible "Select" on every card would be 25 copies of one word.
        -->
        <div
            v-else
            class="mt-6 grid grid-cols-1 gap-3 min-[821px]:grid-cols-2 min-[1081px]:grid-cols-3"
        >
            <div
                v-for="row in props.trainees.data"
                :key="row.id"
                :data-trainee-card="row.id"
                class="cursor-pointer rounded-lg border-2 bg-panel p-3.5 transition-colors duration-150 ease-out"
                :class="props.selected === row.id ? 'border-pick-line bg-sunken' : 'border-rule hover:bg-raised'"
                @click="choose(row)"
            >
                <div class="flex items-start gap-2.5">
                    <button
                        :id="`trainee-${row.id}`"
                        type="button"
                        class="flex size-11 shrink-0 items-center justify-center rounded-md disabled:cursor-wait focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        :aria-pressed="props.selected === row.id ? 'true' : 'false'"
                        :disabled="selectForm.processing"
                        @click.stop="choose(row)"
                    >
                        <span
                            aria-hidden="true"
                            class="size-4 rounded-full border-2"
                            :class="props.selected === row.id ? 'border-pick-line bg-pick-line' : 'border-rule bg-raised'"
                        />
                        <span class="sr-only">{{ props.selected === row.id ? 'Chosen · clear' : 'Select' }}</span>
                    </button>

                    <!-- The name beside the frame is the accessible name, so the image is decorative and
                         takes `alt=""` (DESIGN.md §4.7). -->
                    <ArtworkSlot :url="row.artworkURL" alt="" size="size-10" reserve />

                    <div class="min-w-0 flex-1">
                        <p class="text-[15px] font-extrabold text-ink-strong">{{ row.name }}</p>
                        <p class="text-xs text-ink-muted">
                            <span v-if="row.name_ja" lang="ja">{{ row.name_ja }}</span>
                            <template v-if="headlineForm(row)">
                                <span v-if="row.name_ja" aria-hidden="true"> · </span>{{ headlineForm(row)?.title }}
                            </template>
                        </p>

                        <div class="mt-1.5 flex flex-wrap gap-1.5 text-xs">
                            <span
                                v-if="headlineForm(row)"
                                class="inline-flex items-center rounded-full border border-rule bg-raised px-2 py-0.5 font-semibold text-ink"
                            >
                                {{ headlineForm(row)?.rarity_stars }} {{ headlineForm(row)?.rarity_word }}
                            </span>
                            <span class="inline-flex items-center rounded-full border border-rule bg-raised px-2 py-0.5 text-ink-muted">
                                {{ formPillLabel(row.form_count) }}
                            </span>
                            <span class="inline-flex items-center rounded-full border border-pick-line bg-raised px-2 py-0.5 text-ink">
                                {{ row.release_status_label }}
                            </span>
                        </div>

                        <dl
                            v-if="row.aptitudes"
                            class="mt-2 grid grid-cols-[auto_minmax(0,1fr)] gap-x-2 gap-y-0.5 text-[11.5px]"
                        >
                            <template v-for="(axes, axis) in axisRows" :key="axis">
                                <dt class="text-ink-muted capitalize">{{ axis }}</dt>
                                <dd class="text-ink">
                                    <template v-for="(cell, index) in axes" :key="cell.value">
                                        <span v-if="index > 0" aria-hidden="true" class="text-ink-muted"> · </span>
                                        <!-- The strongest letter of the row carries the weight change, so the
                                             comparison reads without colour (design-2.0 §17). -->
                                        <span
                                            :class="row.aptitudes[cell.value] === strongest(row.aptitudes, axes)
                                                ? 'font-bold text-ink-strong'
                                                : ''"
                                        >{{ cell.label }} {{ row.aptitudes[cell.value] ?? 'N/A' }}</span>
                                    </template>
                                </dd>
                            </template>
                        </dl>
                        <p v-else class="mt-2 text-xs text-ink-muted">
                            <AbsenceValue compact reason="The character document published no aptitude letters for this trainee." />
                        </p>

                        <!-- R3-06: the costume form is asked only where there is a choice to make. "Not stated"
                             stays a real answer rather than a default that names a form nobody picked. The
                             click is stopped so opening the picker does not also pick the card. -->
                        <div v-if="row.forms.length > 1" @click.stop>
                            <BaseSelect
                                class="mt-2"
                                :id="`trainee-form-${row.id}`"
                                name="character_card_id"
                                label="Costume form"
                                v-model="pickedForm[row.id]"
                                :options="[{ value: null, label: 'Not stated' }, ...row.forms.map((card: FormRow) => ({ value: card.id, label: `${card.title}${card.is_debut_form ? ' (debut form)' : ''}` }))]"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-2 flex justify-end">
                    <a
                        :href="`/career/setup/trainee/${row.id}`"
                        class="inline-flex min-h-11 items-center text-sm font-medium text-ink underline"
                        @click.stop
                    >
                        Profile
                    </a>
                </div>
            </div>
        </div>

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

        <!-- The chosen trainee, restated once at the foot of the roster (`career-trainee.html:191-200`). Read
             from `selectedName` rather than from the page's own rows: the roster is filtered and paginated,
             so the pick is usually not on the page shown, and a lookup there once answered `N/A` beside a
             flash saying she had been set (D5). The card renders only when a trainee is actually chosen -
             the wireframe has no empty state, and "nothing chosen" is already said by the strip above. -->
        <div v-if="props.selectedName" data-field="selected-trainee" class="mt-3.5 rounded-lg border border-rule bg-panel p-4">
            <h2 class="text-sm font-bold text-ink-strong">Selected</h2>
            <div class="mt-2 flex flex-wrap items-center gap-3.5">
                <div class="min-w-0 flex-1 basis-64">
                    <p class="text-[15px] font-extrabold text-ink-strong">
                        {{ props.selectedName }}
                        <span v-if="selectedRow && headlineForm(selectedRow)" class="font-semibold text-ink-muted">
                            {{ headlineForm(selectedRow)?.title }}
                        </span>
                    </p>
                    <p class="text-xs text-ink-muted">
                        <template v-if="selectedSummary">{{ selectedSummary }}.</template>
                        The form you pick sets this run's rarity and potential.
                    </p>
                </div>
                <a
                    v-if="props.selected"
                    :href="`/career/setup/trainee/${props.selected}`"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-sm font-bold text-ink hover:bg-sunken transition-colors duration-150 ease-out"
                >
                    Preview profile
                </a>
            </div>
        </div>

        <!-- The picker's only forward control used to be the wizard rail at the top, so a Trainer who
             scrolled to the end of the roster had to scroll back up to advance (R4-22). The same
             destination, at the point the choice is made. -->
        <div class="mt-4 flex justify-end">
            <a
                :href="props.targetUrl"
                class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 text-sm font-bold text-on-primary-action"
            >
                Continue to Your target
            </a>
        </div>
    </SetupLayout>
</template>
