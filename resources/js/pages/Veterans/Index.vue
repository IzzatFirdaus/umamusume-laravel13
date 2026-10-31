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
 * HFW-OVERRIDE (filters: factor grades) - CORRECTED at R3. The previous text claimed "the
 * wireframe's own filter row has no grade facet" and cited `veterans.html:124-138`, which is the
 * factor-grade reference CARD. Both halves were wrong about the wireframe: `veterans.html:73`, inside
 * the `.filters` row at `:70-75`, draws exactly this control -
 * `<select>` with `Any / ★3 / ★2 / ★1` under the label "Factor grade".
 *
 * The facet is still not built, and now for a reason that is true and binding rather than a
 * misreading. `ADR-0020` §3 ("A Veteran library is authorized as recording ... Explicitly unchanged,
 * per ADR-0010: no inheritance engine. Nothing computes a Spark firing, an affinity payout, an
 * offspring, or an 'expected inheritance.' ... The library stores and searches what the trainer
 * entered; it does not derive an outcome"), with `PRD FR-G-4` behind it, bars computing a grade:
 * a factor grade is derived from stored sparks, which is the derivation that clause forbids.
 * `App\Services\Legacy\VeteranRow` states the same rule at its own docblock ("It reads; it derives
 * nothing") and exposes only the blue/pink/unique COUNTS, "entries in the payload, never a derived
 * price".
 *
 * And there is no column to read: `veterans` holds 7 columns (id, training_run_id, tags, notes,
 * created_at, updated_at, tags_normalized) - no grade. So the facet waits on a ruling that would have
 * to lift ADR-0020 §3 or add a stored grade, not on this page.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Pagination from '../../components/ui/Pagination.vue';
import Card from '../../components/ui/Card.vue';
import Tile from '../../components/ui/Tile.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import { BaseInput, BaseSelect, BaseButton, LinkButton } from '../../components/Form';
import { carriesAncestryNote, veteranResultClause, VETERAN_ANCESTRY_NOTE } from '../../domain/veteranNote';
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
    /** The three page facts the in-card pager prints (`LengthAwarePaginator` emits all of them). */
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
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

/** The hoisted sentence is only stated when a row on this page actually carried it. */
const hasSharedAncestryNote = computed(() =>
    props.veterans.data.some((veteran) => carriesAncestryNote(veteran.notes)),
);

/**
 * The wireframe's own team arithmetic, stated once as a rule rather than left to the reader:
 * seven registered against three needed is "met twice over" on the HFW; a library of one is not met
 * at all. Both sentences are the same two numbers, so the rule is printed only when it is not met
 * (`GP-3`: a summation is labelled as a summation, and the label carries the rule).
 */
const teamSentence = computed<string>(() => {
    const registered = props.totalCount;

    if (registered < NEEDED_FOR_TEAM) {
        return `Entering a Champions Meeting needs three Veteran Umamusume registered as a team. This library holds ${registered}, so the requirement is not met yet.`;
    }

    return `Entering a Champions Meeting needs three Veteran Umamusume registered as a team. This library holds ${registered}, so the requirement is met.`;
});

/** A page move is a visit, not a client-side slice: the filters and the order ride along (`withQueryString`). */
function goToPage(page: number): void {
    router.get(props.searchAction, { ...filteredQuery(props.order), page }, { preserveScroll: true });
}

/** The four factor families the reference card names, with the published grade arithmetic (UMAMUSUME_REFERENCE.md §1.5.2). */
const factorGrades: { family: string; note: string }[] = [
    { family: 'Blue · stat', note: 'One stat +5 / +12 / +21 at career start, for ★1 / ★2 / ★3.' },
    { family: 'Pink · aptitude', note: '+1 / +2 / +3 / +4 grades at ★1 / 4 / 7 / 10-18.' },
    { family: 'Green · unique', note: "The trainee's own skill, as 1-3 hint levels." },
    { family: 'White · skill or competition', note: 'Mid-run hint +1…+5, or 3 / 6 / 9 stat from a G1 win. Never fires at career start.' },
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
            >
                <!-- The head-level Compare door. `compare_url` has always been passed
                     (`VeteranController@index`); until now it was rendered per row only, so the
                     library had no way to start a comparison without first finding two rows. The
                     HFW puts the action in the head band, where a screen-level verb belongs. -->
                <template #actions>
                    <!-- The page's one accented control (UI-7, UX-6, DESIGN.md §2.3). The HFW's head
                         action is `btn-primary`, and the filter commit is a quiet secondary so the
                         accent has one owner. -->
                    <LinkButton :href="props.compare_url" variant="primary">
                        Compare veterans
                    </LinkButton>
                </template>
            </PageHeader>
        </template>

        <!--
            The wireframe's filter row (`veterans.html:70-75`): a name search that takes the space, then
            two narrow facets, then the quiet end of the row.

            HFW-OVERRIDE (filters: tag, order and submit) — the wireframe draws four items because it is a
            static mock: `Search`, `Scenario`, `Factor grade`, `Clear`. Three of this row's controls are
            real capabilities the mock does not draw and the product will not remove:
              - `Tag` occupies the third slot. That slot is the Factor grade facet, which is unbuilt by
                ruling (`ADR-0020` §3 / FR-G-4: a grade is derived from stored sparks), and a dead select
                is not an option (DESIGN.md §5).
              - the order pair is a shipped capability with its own stored-order caveat (`SCREEN-021`).
              - `Filter` exists because a real form needs a commit control; the mock has nothing to submit.
        -->
        <form data-region="filters" class="mt-4 flex flex-wrap items-end gap-2.5 text-sm" @submit.prevent="applyFilters">
            <label class="flex min-w-0 flex-[1_1_240px] flex-col gap-1 text-[10px] font-extrabold uppercase tracking-[0.07em] text-ink-muted" data-region="field">
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
            <label class="flex min-w-0 flex-[0_1_170px] flex-col gap-1 text-[10px] font-extrabold uppercase tracking-[0.07em] text-ink-muted" data-region="field">
                Scenario
                <BaseSelect
                    v-model="scenario"
                    id="veteran-filter-scenario"
                    name="scenario"
                    aria-label="Scenario"
                    :options="[{ value: '', label: 'Any' }, ...Object.entries(scenarios).map(([key, label]) => ({ value: key, label }))]"
                />
            </label>
            <label class="flex min-w-0 flex-[0_1_160px] flex-col gap-1 text-[10px] font-extrabold uppercase tracking-[0.07em] text-ink-muted" data-region="field">
                Tag
                <BaseInput
                    v-model="tag"
                    id="veteran-filter-tag"
                    name="tag"
                    type="text"
                    placeholder="One tag"
                    :error="errors.tag"
                />
            </label>

            <div class="ms-auto flex flex-wrap items-center gap-2">
                <div class="flex gap-1" role="group" aria-label="Sort order">
                    <BaseButton
                        type="button"
                        variant="ghost"
                        :class="props.order === 'newest' ? 'bg-raised font-bold text-ink-strong' : ''"
                        :aria-current="props.order === 'newest' ? 'true' : undefined"
                        @click="visitOrder('newest')"
                    >
                        Newest first
                    </BaseButton>
                    <BaseButton
                        type="button"
                        variant="ghost"
                        :class="props.order === 'oldest' ? 'bg-raised font-bold text-ink-strong' : ''"
                        title="Order of the library's own records. No column holds the date a career finished, so this is not a completion date."
                        :aria-current="props.order === 'oldest' ? 'true' : undefined"
                        @click="visitOrder('oldest')"
                    >
                        Oldest first
                    </BaseButton>
                </div>
                <BaseButton type="submit" variant="secondary">Filter</BaseButton>
                <a
                    href="/veterans"
                    class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
                >Clear</a>
            </div>
        </form>

        <p v-if="errors.search" role="alert" class="mt-2 text-sm text-risk">Search: {{ errors.search }}</p>
        <p v-if="errors.scenario" role="alert" class="mt-2 text-sm text-risk">Scenario: {{ errors.scenario }}</p>
        <p v-if="errors.tag" role="alert" class="mt-2 text-sm text-risk">Tag: {{ errors.tag }}</p>

        <!-- The screen's small print in one place: the parent search and its rule, the in-flight status,
             and the ancestry sentence the imported tracker forced. The count that used to sit here now
             lives in the card's own pager, where the wireframe puts it (`veterans.html:115-119`). -->
        <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-ink-muted">
            <a
                :href="props.find_parents_url"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >Find parents for this build</a>
            <span>Searches the Legacy Lab with the tags you are filtering by.</span>
            <span v-if="loading" role="status">Loading results…</span>
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
            <p>The library holds no Veterans yet.</p>
            <p class="mt-1">{{ props.filingNotice }}</p>
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

        <Card v-else class="mt-3 min-w-0">
            <!-- `relative` is load-bearing, not decoration: the table's `sr-only` spans are absolutely
                 positioned, and without a positioned scroll container they resolve against the initial
                 containing block and add their static offset to the document's scrollable overflow. -->
            <div class="relative min-w-0 overflow-x-auto">
                <table class="t">
                    <thead>
                        <tr>
                            <th scope="col">Trainee</th>
                            <th scope="col">Scenario</th>
                            <th scope="col">Saved</th>
                            <th scope="col" class="n">Blue sparks</th>
                            <th scope="col" class="n">Pink sparks</th>
                            <th scope="col" class="n">Unique sparks</th>
                            <th scope="col" class="n"><span class="sr-only">Open the Veteran</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="veteran in veterans.data" :key="veteran.id" :data-veteran-row="veteran.id">
                            <td>
                                <span class="flex items-start gap-2.5">
                                    <ArtworkSlot :url="veteran.portrait_url" alt="" size="size-12" reserve data-region="slot" />
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

                                        <!-- HFW-OVERRIDE (row: second line) - the wireframe draws one line per
                                             row; SCREEN-021 requires the run's state, the Trainer's tags and
                                             notes, and the two doors. Compressed to one muted line so the row
                                             still reads as one record. -->
                                        <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-muted">
                                            <span>{{ veteran.status_label }}</span>
                                            <span v-if="veteran.tags.length === 0">&middot; No tags recorded</span>
                                            <span v-else class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="tagName in veteran.tags"
                                                    :key="tagName"
                                                    class="rounded-pill bg-sunken px-2 py-0.5 text-ink"
                                                >{{ tagName }}</span>
                                            </span>
                                            <span v-if="veteranResultClause(veteran.notes)" class="w-full">{{ veteranResultClause(veteran.notes) }}</span>
                                            <span class="flex flex-wrap items-center gap-x-3">
                                                <a v-if="veteran.builder_url" :href="veteran.builder_url" class="underline">Legacy builder</a>
                                                <span v-else>No Legacy selection yet</span>
                                                <a :href="veteran.compare_url" class="underline">Compare</a>
                                            </span>
                                        </span>
                                    </span>
                                </span>
                            </td>
                            <td>{{ veteran.scenario_label }}</td>
                            <td class="font-mono tabular-nums">
                                <template v-if="veteran.saved_at">{{ veteran.saved_at }}</template>
                                <span v-else>N/A<span class="sr-only">: this record states no filing date.</span></span>
                            </td>
                            <!--
                                One bare integer per cell: the stored spark tally for that family, never a
                                grade. HFW-OVERRIDE (columns: spark counts, not grades) - the wireframe asks
                                for three grade columns (annotation 1) and draws stars; ADR-0020 §3 with
                                FR-G-4 bars deriving a grade from stored sparks. The unit is stated once in
                                the thead so a count cannot be read as a star rating.
                            -->
                            <td class="n font-mono tabular-nums" data-spark="blue">{{ veteran.blue }}</td>
                            <td class="n font-mono tabular-nums" data-spark="pink">{{ veteran.pink }}</td>
                            <!-- HFW-OVERRIDE (columns: unique is green). The wireframe heads this
                                 column "Unique"; the reference's family is Green (§1.5.2), and the row key
                                 `unique` is `sparkCount($payload, 'green')`. One join, stated once. -->
                            <td class="n font-mono tabular-nums" data-spark="unique">{{ veteran.unique }}</td>
                            <td class="n">
                                <LinkButton :href="veteran.veteran_url" variant="secondary" size="sm">Open</LinkButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- HFW-OVERRIDE (copy: pager noun) - the mock prints "7 veterans - page 1 of 2"; the
                 app prints the paginator's own noun, singular "veteran" for a library of one, because the
                 count is a live figure rather than the mock's fixed seven. -->
            <Pagination
                variant="compact"
                noun="veteran"
                :page="veterans.current_page"
                :pages="veterans.last_page"
                :from="veterans.from ?? 0"
                :to="veterans.to ?? 0"
                :total="veterans.total"
                @navigate="goToPage"
            />
        </Card>

        <div class="mt-3.5 grid grid-cols-1 gap-3.5 min-[821px]:grid-cols-2">
            <Card as="section" aria-labelledby="veteran-factor-grades" class="min-w-0">
                <h2 id="veteran-factor-grades">Factor grades</h2>
                <div class="min-w-0 overflow-x-auto">
                <table class="t">
                    <thead>
                        <tr><th scope="col">Family</th><th scope="col">Carries</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="grade in factorGrades" :key="grade.family">
                            <th scope="row" class="font-medium text-ink">{{ grade.family }}</th>
                            <td>{{ grade.note }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <p class="note mt-2.5">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        Three stars is the ceiling on Global. The ★4 cap is the JP-only factor-research item,
                        which cannot push past ★4 and does not exist here.
                    </span>
                </p>
            </Card>

            <!-- Team eligibility is arithmetic this page does rather than a figure a Trainer keeps by hand:
                 a Champions Meeting team registers three Veterans, so the library's own total answers both
                 how many are registered and how many whole teams that makes. -->
            <Card as="section" aria-labelledby="veteran-team-eligibility">
                <h2 id="veteran-team-eligibility">Team eligibility</h2>
                <p class="mt-1.5 text-[12.5px] leading-[1.55] text-ink-muted">{{ teamSentence }}</p>
                <div class="tiles mt-2.5 grid-cols-1 sm:grid-cols-3" data-field="team-eligibility">
                    <Tile label="Registered" :value="String(totalCount)" />
                    <Tile label="Needed for a team" :value="String(NEEDED_FOR_TEAM)" />
                    <Tile label="Teams available" :value="String(teamsAvailable)" />
                </div>
                <p v-if="totalCount > 0 && teamsAvailable === 0" class="mt-2 text-sm text-ink-muted">
                    No whole team yet: {{ NEEDED_FOR_TEAM - totalCount }} more
                    {{ NEEDED_FOR_TEAM - totalCount === 1 ? 'Veteran' : 'Veterans' }} would register one. This
                    is arithmetic on the library, not a claim that any three can win.
                </p>
            </Card>
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
