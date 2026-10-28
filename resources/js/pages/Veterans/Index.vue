<script setup lang="ts">
/*
 * The Veteran library, `SCREEN-021` (PRD FR-G-2, ADR-0020 §3), rebuilt to `veterans.html`.
 *
 * One table row per filed career: the trainee and her frame, the scenario she ran, when the record was
 * filed, and the stored spark tallies. The row's own second line keeps what the wireframe does not
 * draw — the Trainer's tags, her notes, the run's state — and the two doors the other surfaces need:
 * the Legacy builder and the compare picker. Two cards under the table carry the published factor
 * grades and the team arithmetic; the "not in this build" disclosure stays as the last section.
 *
 * HFW-OVERRIDE (filters: factor grades) - `veterans.html:124-138` prints the factor-grade reference
 * card; the wireframe's own filter row has no grade facet, and none is added: no column stores a
 * grade, they are derived from sparks this library only counts.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import { BaseInput, BaseSelect, BaseButton, LinkButton } from '../../components/Form';
import { carriesAncestryNote, VETERAN_ANCESTRY_NOTE } from '../../domain/veteranNote';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Veteran {
    id: number;
    run_id: number;
    label: string;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
    veteran_url: string;
    portrait_url: string | null;
    saved_at: string | null;
    blue: number;
    pink: number;
    unique: number;
    compare_url: string;
}

interface Absence {
    label: string;
    reason: string;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    veterans: Paginator<Veteran>;
    order: 'newest' | 'oldest';
    filters: { search: string | null; scenario: string | null; tag: string | null };
    scenarios: Record<string, string>;
    totalCount: number;
    notice: string;
    filingNotice: string;
    file_veteran_url: string;
    find_parents_url: string;
    compare_url: string;
    absences: Absence[];
    searchAction: string;
}>();

const page = usePage();
const errors = computed(() => (page.props.errors ?? {}) as Record<string, string>);

/** A Champions Meeting team registers three Veterans; whole teams are all the library can field. */
const NEEDED_FOR_TEAM = 3;

const teamsAvailable = computed(() => Math.floor(props.totalCount / NEEDED_FOR_TEAM));

/**
 * The head's one sentence, in the wireframe's own shape: what the library is for, and the figure
 * that decides whether a Champions Meeting team can be fielded. Bound, never typed.
 */
const libraryLede = computed<string>(() => {
    const base = 'Finished careers, banked as inheritance candidates.';

    if (props.totalCount === 0) {
        return `${base} Nothing is registered yet, so no team can be fielded.`;
    }

    return `${base} ${props.totalCount} ${props.totalCount === 1 ? 'is' : 'are'} registered; a Champions Meeting team needs ${NEEDED_FOR_TEAM}.`;
});

const search = ref(props.filters.search ?? '');
const scenario = ref(props.filters.scenario ?? '');
const tag = ref(props.filters.tag ?? '');
const loading = ref(false);

function filteredQuery(order: 'newest' | 'oldest'): Record<string, string> {
    const query: Record<string, string> = { order };

    if (search.value !== '') {
        query.search = search.value;
    }

    if (scenario.value !== '') {
        query.scenario = scenario.value;
    }

    if (tag.value !== '') {
        query.tag = tag.value;
    }

    return query;
}

function applyFilters(): void {
    router.get(props.searchAction, filteredQuery(props.order), {
        preserveState: true,
        preserveScroll: true,
        onStart: () => {
            loading.value = true;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

// The order toggle is a link pair rather than a fourth form control, so it keeps the filter the Trainer
// is already looking at instead of asking them to press Filter again.
function visitOrder(order: 'newest' | 'oldest'): void {
    router.get(props.searchAction, filteredQuery(order), { preserveScroll: true });
}

const filtered = computed(
    () => props.filters.search !== null || props.filters.scenario !== null || props.filters.tag !== null,
);

/** The hoisted sentence is only stated when a row on this page actually carried it. */
const hasSharedAncestryNote = computed(() =>
    props.veterans.data.some((veteran) => carriesAncestryNote(veteran.notes)),
);

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-3 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-3 text-right align-top font-mono tabular-nums text-sm text-ink';

/** The five factor families the reference card names, with the published grade arithmetic. */
const factorGrades: { family: string; note: string }[] = [
    { family: 'Blue · stat', note: 'One stat +5 / +12 / +21 at career start, for ★1 / ★2 / ★3.' },
    { family: 'Pink · aptitude', note: '+1 / +2 / +3 / +4 grades at ★1 / 4 / 7 / 10–18.' },
];
</script>

<template>
    <AppLayout>
        <Head title="Veteran library" />
        <template #head>
            <PageHeader
                eyebrow="Veterans"
                title="Veteran library"
                :description="libraryLede"
            />
        </template>

        <!-- The wireframe's filter row: the name search first, then the two facets and Clear
             (`veterans.html:71-75`). -->
        <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex min-w-0 flex-1 basis-64 flex-col gap-1 text-xs font-semibold text-ink-muted">
                Search
                <BaseInput
                    id="veteran-filter-search"
                    v-model="search"
                    name="search"
                    type="search"
                    placeholder="Trainee name"
                    :error="errors.search"
                />
            </label>
            <BaseSelect
                v-model="scenario"
                id="veteran-filter-scenario"
                name="scenario"
                label="Scenario"
                :options="[{ value: '', label: 'Any' }, ...Object.entries(scenarios).map(([key, label]) => ({ value: key, label }))]"
            />
            <BaseInput
                v-model="tag"
                id="veteran-filter-tag"
                name="tag"
                type="text"
                placeholder="One tag, as you typed it"
                label="Tag"
            />
            <div class="flex flex-col gap-1">
                <span class="text-ink-muted">Order</span>
                <div class="flex gap-1" role="group" aria-label="Sort order">
                    <BaseButton
                        type="button"
                        variant="secondary"
                        :class="props.order === 'newest' ? 'bg-raised font-bold text-ink-strong' : ''"
                        :aria-current="props.order === 'newest' ? 'true' : undefined"
                        @click="visitOrder('newest')"
                    >
                        Newest first
                    </BaseButton>
                    <BaseButton
                        type="button"
                        variant="secondary"
                        :class="props.order === 'oldest' ? 'bg-raised font-bold text-ink-strong' : ''"
                        title="Order of the library's own records. No column holds the date a career finished, so this is not a completion date."
                        :aria-current="props.order === 'oldest' ? 'true' : undefined"
                        @click="visitOrder('oldest')"
                    >
                        Oldest first
                    </BaseButton>
                </div>
            </div>
            <BaseButton type="submit" variant="primary">Filter</BaseButton>
            <a
                href="/veterans"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <p v-if="errors.search" role="alert" class="mt-2 text-sm text-risk">Search: {{ errors.search }}</p>
        <p v-if="errors.scenario" role="alert" class="mt-2 text-sm text-risk">Scenario: {{ errors.scenario }}</p>
        <p v-if="errors.tag" role="alert" class="mt-2 text-sm text-risk">Tag: {{ errors.tag }}</p>

        <!-- `SCREEN-021`'s "Find Parents for This Build". A search, not an optimizer: it hands the tags this
             list is filtered by to the Legacy Lab's compare surface, which answers on stored columns only. -->
        <p class="mt-3 flex flex-wrap items-center gap-3 text-sm">
            <a
                :href="props.find_parents_url"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >Find parents for this build</a>
            <span class="text-ink-muted">Searches the Legacy Lab with the tags you are filtering by.</span>
        </p>

        <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

        <p class="mt-4 text-sm text-ink-muted">
            <template v-if="totalCount === 0">The library holds no Veterans yet.</template>
            <template v-else-if="filtered">{{ veterans.total }} of {{ totalCount }} Veterans match the filters.</template>
            <template v-else>{{ veterans.total }} Veterans in the library.</template>
        </p>

        <!-- Stated once for the whole list, above it, rather than once per row: the ancestry sentence is
             about what the tracker did not record, which is the same for every row here. -->
        <p v-if="hasSharedAncestryNote" class="mt-2 text-sm text-ink-muted">
            {{ VETERAN_ANCESTRY_NOTE }}
        </p>

        <!-- The two lines are different states and neither is an error: an empty library means nothing has
             been filed, and a filtered empty list means the filters asked a question no row answers. -->
        <div
            v-if="totalCount === 0"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            <p>{{ props.filingNotice }}</p>
            <a
                :href="props.file_veteran_url"
                class="mt-3 inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 font-semibold text-on-primary-action"
            >Open the run list</a>
        </div>

        <p
            v-else-if="veterans.data.length === 0"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
            data-testid="no-match"
        >
            Nothing matches those filters. The facets are the only ones the library can answer, and they
            narrow rather than widen: a tag you typed that no Veteran carries returns no rows.
        </p>

        <div v-else class="mt-3 overflow-x-auto rounded-md border border-rule bg-raised">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th :class="th">Trainee</th>
                        <th :class="th">Scenario</th>
                        <th :class="th">Saved</th>
                        <th :class="thNum">Blue</th>
                        <th :class="thNum">Pink</th>
                        <th :class="thNum">Unique</th>
                        <th :class="thNum"><span class="sr-only">Open the Veteran</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="veteran in veterans.data" :key="veteran.id" :data-veteran-row="veteran.id">
                        <td :class="td">
                            <span class="flex items-start gap-2.5">
                                <ArtworkSlot :url="veteran.portrait_url" alt="" size="size-12" reserve />
                                <span class="min-w-0">
                                    <Link
                                        :href="veteran.veteran_url"
                                        class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out"
                                    >{{ veteran.trainee }}</Link>
                                    <span
                                        v-if="veteran.trainee_ja"
                                        lang="ja"
                                        class="block text-xs text-ink-muted"
                                    >{{ veteran.trainee_ja }}</span>

                                    <!-- The secondary line: the state, the tags and the notes the HFW does
                                         not draw, plus the two doors the other surfaces own. -->
                                    <span class="mt-1 block text-xs text-ink-muted">{{ veteran.status_label }}</span>
                                    <span v-if="veteran.tags.length === 0" class="mt-1 block text-xs text-ink-muted">
                                        No tags recorded
                                    </span>
                                    <span v-if="veteran.tags.length > 0" class="mt-1 flex flex-wrap gap-1.5">
                                        <span
                                            v-for="tagName in veteran.tags"
                                            :key="tagName"
                                            class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink"
                                        >{{ tagName }}</span>
                                    </span>
                                    <span v-if="veteran.notes" class="mt-1 block text-xs text-ink-muted">{{ veteran.notes }}</span>
                                    <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                        <a v-if="veteran.builder_url" :href="veteran.builder_url" class="underline">Legacy builder</a>
                                        <span v-else class="text-ink-muted">No Legacy selection yet</span>
                                        <a :href="veteran.compare_url" class="underline">Compare</a>
                                    </span>
                                </span>
                            </span>
                        </td>
                        <td :class="td">{{ veteran.scenario_label }}</td>
                        <td :class="td">
                            <template v-if="veteran.saved_at">{{ veteran.saved_at }}</template>
                            <span v-else>N/A<span class="sr-only">: this record states no filing date.</span></span>
                        </td>
                        <td :class="tdNum">{{ veteran.blue }}</td>
                        <td :class="tdNum">{{ veteran.pink }}</td>
                        <td :class="tdNum">{{ veteran.unique }}</td>
                        <td :class="[td, 'text-right']">
                            <LinkButton :href="veteran.veteran_url" variant="secondary" size="sm">Open</LinkButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="veterans.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
            <template v-for="(link, index) in veterans.links" :key="index">
                <a
                    v-if="link.url"
                    :href="link.url"
                    :aria-current="link.active ? 'page' : undefined"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-raised transition-colors duration-150 ease-out"
                    :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                    v-html="link.label"
                />
                <span v-else class="inline-flex min-h-11 items-center px-3 text-ink-muted" v-html="link.label" />
            </template>
        </nav>

        <div class="mt-6 grid gap-4 g-2">
            <section class="card" aria-labelledby="veteran-factor-grades">
                <h2 id="veteran-factor-grades">Factor grades</h2>
                <table class="mt-2 w-full border-collapse text-sm">
                    <tbody>
                        <tr v-for="grade in factorGrades" :key="grade.family" class="border-b border-rule last:border-b-0">
                            <th scope="row" class="w-40 py-2 pr-3 text-left font-medium text-ink">{{ grade.family }}</th>
                            <td class="py-2 text-ink">{{ grade.note }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        Three stars is the ceiling on Global. The ★4 cap is the JP-only factor-research item,
                        which cannot push past ★4 and does not exist here. Grading a library row is a derived
                        value this build does not store, so the library has no grade column.
                    </span>
                </p>
            </section>

            <!-- Team eligibility is arithmetic this page does rather than a figure a Trainer keeps by hand:
                 a Champions Meeting team registers three Veterans, so the library's own total answers both
                 how many are registered and how many whole teams that makes. -->
            <section class="card" aria-labelledby="veteran-team-eligibility">
                <h2 id="veteran-team-eligibility">Team eligibility</h2>
                <dl class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3" data-field="team-eligibility">
                    <div>
                        <dt class="text-xs text-ink-muted">Registered</dt>
                        <dd class="font-mono tabular-nums text-ink-strong">{{ totalCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Needed for a team</dt>
                        <dd class="font-mono tabular-nums text-ink-strong">{{ NEEDED_FOR_TEAM }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Teams available</dt>
                        <dd class="font-mono tabular-nums text-ink-strong">{{ teamsAvailable }}</dd>
                    </div>
                </dl>
                <p v-if="totalCount > 0 && teamsAvailable === 0" class="mt-2 text-sm text-ink-muted">
                    No whole team yet: {{ NEEDED_FOR_TEAM - totalCount }} more
                    {{ NEEDED_FOR_TEAM - totalCount === 1 ? 'Veteran' : 'Veterans' }} would register one. This
                    is arithmetic on the library, not a claim that any three can win.
                </p>
            </section>
        </div>

        <details class="mt-8">
            <summary id="library-absences" class="text-lg font-semibold text-ink-strong cursor-pointer list-none">
                Not in this build
                <span class="text-sm font-normal text-ink-muted ml-2">({{ props.absences.length }} notes)</span>
            </summary>
            <ul class="mt-2 space-y-2 text-sm text-ink-muted">
                <li v-for="absence in absences" :key="absence.label">
                    <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                </li>
            </ul>
        </details>
    </AppLayout>
</template>
