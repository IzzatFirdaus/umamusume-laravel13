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
 * HFW-OVERRIDE (filters: spark grades) - CORRECTED at R3. The previous text claimed "the
 * wireframe's own filter row has no grade facet" and cited `veterans.html:124-138`, which is the
 * spark-grade reference CARD. Both halves were wrong about the wireframe: `veterans.html:73`, inside
 * the `.filters` row at `:70-75`, draws exactly this control -
 * `<select>` with `Any / ★3 / ★2 / ★1` under the label "Spark grade".
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
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseInput, BaseSelect, BaseButton, BaseCheckbox, LinkButton } from '../../components/Form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Veteran {
    id: number;
    run_id: number;
    label: string;
    trainee: string;
    scenario_label: string;
    status_label: string;
    tags: string[];
    veteran_url: string;
    portrait_url: string | null;
    saved_at: string | null;
    blue: number;
    pink: number;
    unique: number;
    used_as_parent_count: number;
    transfer_url: string | null;
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
    /** The server's own cap, so the picker never states a limit the request does not enforce. */
    compare_max: number;
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

/**
 * The careers ticked for comparison. Selection lives here, on the library, because the library is
 * where a Trainer sees the rows: `veterans.html` puts the checkboxes in the table and the compare
 * page draws only the comparison. The compare surface used to carry its own picker, which meant
 * choosing careers and reading them were the same screen and the same screen could not be linked to.
 */
const selected = ref<number[]>([]);

const isChosen = (id: number): boolean => selected.value.includes(id);

const selectedCount = computed(() => selected.value.length);
const canCompare = computed(() => selectedCount.value >= 2 && selectedCount.value <= props.compare_max);

function toggleChosen(id: number): void {
    if (isChosen(id)) {
        selected.value = selected.value.filter(chosen => chosen !== id);
    } else if (selectedCount.value < props.compare_max) {
        selected.value = [...selected.value, id];
    }
}

function compareSelected(): void {
    if (canCompare.value) {
        router.get(props.compare_url, { veterans: selected.value });
    }
}

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

/**
 * The wireframe's own team arithmetic, stated once as a rule rather than left to the reader:
 * seven registered against three needed is "met twice over" on the HFW; a library of one is not met
 * at all. Both sentences are the same two numbers, so the rule is printed only when it is not met
 * (`GP-3`: a summation is labelled as a summation, and the label carries the rule).
 */
const teamSentence = computed<string>(() => {
    const registered = props.totalCount;

    // `requires 3 (game rule)`, not `needs 3`: the sentence was read as this tool demanding a
    // third Veteran before it would do something. The rule is the game's, and naming whose rule it
    // is the difference between a requirement and a fact (UX-24). Source:
    // `docs/UMAMUSUME_REFERENCE.md`'s Champions Meeting entry.
    if (registered < NEEDED_FOR_TEAM) {
        return `A Champions Meeting team requires 3 Veteran Umamusume (game rule). This library holds ${registered}, so the requirement is not met yet.`;
    }

    return `A Champions Meeting team requires 3 Veteran Umamusume (game rule). This library holds ${registered}, so the requirement is met.`;
});

/** A page move is a visit, not a client-side slice: the filters and the order ride along (`withQueryString`). */
function goToPage(page: number): void {
    router.get(props.searchAction, { ...filteredQuery(props.order), page }, {
        preserveState: true,
        preserveScroll: true,
    });
}

/** Confirm and submit an Offer for Transfer action. Mirrors Show.vue and VeteranRow.vue behavior. */
function confirmTransfer(url: string, label: string): void {
    if (confirm(`Offer ${label} for transfer? This creates a transfer request that another trainer can complete.`)) {
        router.post(url, {}, { preserveScroll: true });
    }
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
                <template v-if="totalCount > 0" #actions>
                    <div class="flex flex-wrap items-center gap-2">
                        <p role="status" aria-atomic="true" class="text-sm text-ink-muted">
                            {{ selectedCount }} selected · Choose 2 to {{ props.compare_max }}
                        </p>
                        <BaseButton variant="primary" :disabled="!canCompare" @click="compareSelected">
                            Compare
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="selectedCount === 0" @click="selected = []">
                            Clear selection
                        </BaseButton>
                    </div>
                </template>
            </PageHeader>
        </template>

        <!--
            The wireframe's filter row (`veterans.html:70-75`): a name search that takes the space, then
            two narrow facets, then the quiet end of the row.

            HFW-OVERRIDE (filters: tag, order and submit) — the wireframe draws four items because it is a
            static mock: `Search`, `Scenario`, `Spark grade`, `Clear`. Three of this row's controls are
            real capabilities the mock does not draw and the product will not remove:
              - `Tag` occupies the third slot. That slot is the Spark grade facet, which is unbuilt by
                ruling (`ADR-0020` §3 / FR-G-4: a grade is derived from stored sparks), and a dead select
                is not an option (DESIGN.md §5).
              - the order pair is a shipped capability with its own stored-order caveat (`SCREEN-021`).
              - `Filter` exists because a real form needs a commit control; the mock has nothing to submit.
        -->
        <form data-region="filters" class="mt-4 flex flex-wrap items-end gap-2.5 rounded-md border border-rule-soft bg-panel p-3 text-sm" :aria-busy="loading" @submit.prevent="applyFilters">
            <label class="flex min-w-0 flex-[1_1_240px] flex-col gap-1 text-[10px] font-extrabold uppercase tracking-[0.07em] text-ink-muted" data-region="field">
                Search
                <BaseInput
                    id="veteran-filter-search"
                    v-model="search"
                    name="search"
                    type="search"
                    autocorrect="off"
                    autocapitalize="none"
                    :spellcheck="false"
                    enterkeyhint="search"
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
                    autocorrect="off"
                    autocapitalize="none"
                    :spellcheck="false"
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
                        title="Order of the library's own records. This build does not record the date a career finished, so this is not a completion date."
                        :aria-current="props.order === 'oldest' ? 'true' : undefined"
                        @click="visitOrder('oldest')"
                    >
                        Oldest first
                    </BaseButton>
                </div>
                <BaseButton type="submit" variant="secondary">Filter</BaseButton>
                <a
                    :href="props.searchAction"
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
            <span role="status" aria-atomic="true">
                <template v-if="loading">Loading results…</template>
                <template v-else>{{ veterans.total }} matching of {{ totalCount }} registered</template>
            </span>
        </p>

        <!-- The two lines are different states and neither is an error: an empty library means nothing has
             been filed, and a filtered empty list means the filters asked a question no row answers. -->
        <div
            v-if="totalCount === 0"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            <p>The library holds no Veterans yet.</p>
            <p class="mt-1">{{ props.filingNotice }}</p>
            <p class="mt-1">
                A filed career shows its trainee, scenario, the sparks the pair carried and the run it
                came from.
            </p>
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
            <div class="relative min-w-0 overflow-x-auto scroll-p-2" role="region" tabindex="0" aria-label="Veterans, scrollable">
                <table class="t min-w-2xl">
                    <caption class="sr-only">Filed careers and their recorded Sparks</caption>
                    <thead>
                        <tr>
                            <th scope="col"><span class="sr-only">Select for comparison</span></th>
                            <th scope="col">Trainee</th>
                            <th scope="col">Scenario</th>
                            <th scope="col">Completion</th>
                            <th scope="col">Recorded Sparks</th>
                            <!-- HFW-OVERRIDE (columns: used-as-parent count). The wireframe draws no
                                 such column; the owner's 2026-11-14 ruling restores the C3 index count
                                 the detail screen's trail already prints, and it sits with the other
                                 bare-integer tallies before the Open column. -->
                            <th scope="col" class="n">Used as parent</th>
                            <th scope="col" class="n"><span class="sr-only">Open the Veteran</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="veteran in veterans.data" :key="veteran.id" :data-veteran-row="veteran.id" :class="{ 'bg-pick/10': isChosen(veteran.id) }">
                            <td>
                                <!-- The row's own selection. `srOnlyLabel` keeps the sentence as the
                                     control's accessible name and prints nothing, because the trainee's
                                     name is already the cell beside it and a printed copy would read the
                                     same name twice per row. Past the cap the box disables rather than
                                     silently dropping the tick, which is what the compare page's picker
                                     did before it moved here. -->
                                <BaseCheckbox
                                    :model-value="isChosen(veteran.id)"
                                    :id="`compare-${veteran.id}`"
                                    :name="`compare-${veteran.id}`"
                                    :label="`Select ${veteran.label} for comparison`"
                                    sr-only-label
                                    :disabled="!isChosen(veteran.id) && selectedCount >= props.compare_max"
                                    @update:model-value="toggleChosen(veteran.id)"
                                />
                            </td>
                            <th scope="row" class="font-normal">
                                <span class="flex items-start gap-2.5">
                                    <ArtworkSlot :url="veteran.portrait_url" alt="" size="size-12" reserve data-region="slot" />
                                    <span class="min-w-0">
                                        <Link
                                            :href="veteran.veteran_url"
                                            class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out"
                                        >{{ veteran.trainee }}</Link>
                                        <span v-if="veteran.tags.length" class="flex flex-wrap gap-1 text-xs">
                                            <span v-for="tagName in veteran.tags" :key="tagName" class="rounded-pill bg-sunken px-2 py-0.5 text-ink">
                                                {{ tagName }}
                                            </span>
                                        </span>
                                    </span>
                                </span>
                            </th>
                            <td>{{ veteran.scenario_label }}</td>
                            <td>
                                <span class="block font-medium text-ink-strong">{{ veteran.status_label }}</span>
                                <span v-if="veteran.saved_at" class="text-xs text-ink-muted">Filed {{ veteran.saved_at }}</span>
                                <AbsenceValue v-else compact reason="No filing date is recorded for this Veteran." />
                            </td>
                            <td>
                                <ul v-if="veteran.blue || veteran.pink || veteran.unique" class="list-none space-y-1 text-xs">
                                    <li v-if="veteran.blue" data-spark="blue">Blue <span class="font-mono">{{ veteran.blue }}</span></li>
                                    <li v-if="veteran.pink" data-spark="pink">Pink <span class="font-mono">{{ veteran.pink }}</span></li>
                                    <li v-if="veteran.unique" data-spark="unique">Unique <span class="font-mono">{{ veteran.unique }}</span></li>
                                </ul>
                                <AbsenceValue v-else compact reason="No blue, pink or unique Sparks are recorded for this career." />
                            </td>
                            <!-- C3 remainder: how many careers named this trainee as an inheritance
                                 parent, one bare integer like the spark tallies beside it. The count is
                                 character-keyed (the parent columns hold Umamusume ids), counts runs not
                                 column instances, and two filings of one trainee carry the same figure -
                                 the same read the detail screen's trail makes. A zero is a recorded fact
                                 (no career used the trainee), never an absence. -->
                            <td class="n font-mono tabular-nums" data-used-as-parent>{{ veteran.used_as_parent_count }}</td>
                            <td class="n">
                                <div class="flex flex-col items-end">
                                    <LinkButton :href="veteran.veteran_url" variant="secondary" :aria-label="`Open ${veteran.label}`">Open</LinkButton>
                                    <form v-if="veteran.transfer_url" @submit.prevent="confirmTransfer(veteran.transfer_url, veteran.label)">
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 items-center px-3 text-xs font-medium text-ink-muted underline hover:text-ink-strong"
                                        >
                                            Offer for Transfer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- HFW-OVERRIDE (copy: pager noun) - the mock prints "7 veterans - page 1 of 2"; the
                 app prints the paginator's own noun, singular "veteran" for a library of one, because the
                 count is a live figure rather than the mock's fixed seven.

                 The plan asks for the pager to disappear when a single page holds everything, and
                 `veterans-transfer-requests.spec.ts:56` states the same rule for the lifecycle tabs.
                 It is `v-show`, not `v-if`: `veterans-parity.spec.ts:67` compares the app's `pager`
                 marker against the wireframe's and the capture walks DOM children, so removing the
                 node would report the marker missing. Hidden-but-present satisfies both. -->
            <Pagination
                v-show="veterans.last_page > 1"
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
            <Card as="section" aria-labelledby="veteran-spark-grades" class="min-w-0">
                <h2 id="veteran-spark-grades">Spark grades</h2>
                <!-- The plan folds the grade ladder behind a named disclosure: it is reference the
                     Trainer consults occasionally, and it was spending the same vertical band as the
                     team-eligibility arithmetic beside it. The `h2` stays outside the `details` so the
                     card keeps the heading the parity capture looks for (`.grid` filtered by the
                     "Spark grades" heading at 390px), and the summary carries the plan's own words. -->
                <details class="mt-2">
                    <summary class="min-h-11 cursor-pointer py-3 text-sm font-medium text-ink hover:text-ink-strong">
                        How Sparks work
                    </summary>
                    <div class="min-w-0 overflow-x-auto" role="region" tabindex="0" aria-label="Spark grades, scrollable">
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
                            Three stars is the ceiling on Global. The ★4 cap is the JP-only spark-research item,
                            which cannot push past ★4 and does not exist here.
                        </span>
                    </p>
                </details>
            </Card>

            <!-- Team eligibility is arithmetic this page does rather than a figure a Trainer keeps by hand:
                 a Champions Meeting team registers three Veterans, so the library's own total answers both
                 how many are registered and how many whole teams that makes. -->
            <Card as="section" aria-labelledby="veteran-team-eligibility">
                <h2 id="veteran-team-eligibility">Team eligibility</h2>
                <p
                    class="mt-1.5 text-[12.5px] leading-[1.55] text-ink-muted"
                >{{ teamSentence }}</p>
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
            <summary id="library-absences" class="flex min-h-11 cursor-pointer list-none items-center text-lg font-semibold text-ink-strong">
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
