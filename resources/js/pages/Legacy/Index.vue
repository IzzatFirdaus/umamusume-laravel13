<script setup lang="ts">
/*
 * The Legacy Lab browse surface, `SCREEN-006`'s left column (PRD FR-G-2, ADR-0020 §3).
 *
 * The filter form offers exactly the three facets `ListVeterans` answers: trainee, scenario and tag.
 * The screen spec names eleven candidates and C3 implements three; the other eight are absent here
 * rather than rendered as a control that cannot filter, because a facet that answers every row reads
 * as filtered when nothing was filtered (`ADR-0018`'s reasoning). Distance, surface, style and Spark
 * type are searchable through the one tag field, because those are the Trainer's own tag vocabulary
 * (SCREEN-020), not columns this schema has.
 *
 * The page pattern is the landed shell one: `AppLayout`, `<Head title>`, a `PageHeader` in `#head` (one
 * `h1`, in the banner), `router.get` with `preserveState` / `preserveScroll`. There is no write on this
 * page, so there is no `useForm` here.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseInput, BaseSelect, BaseButton, LinkButton } from '../../components/Form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface VeteranRow {
    id: number;
    run_id: number;
    label: string;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
    veteran_url: string;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    veterans: Paginator<VeteranRow>;
    filters: { trainee: number | null; scenario: string | null; tag: string | null };
    trainees: Record<string, string>;
    scenarios: Record<string, string>;
    totalCount: number;
    notice: string;
}>();

const trainee = ref(props.filters.trainee === null ? '' : String(props.filters.trainee));
const scenario = ref(props.filters.scenario ?? '');
const tag = ref(props.filters.tag ?? '');
const loading = ref(false);

// A refused facet lands back here with its message, and this screen had no place to print it. The shape
// is `Veterans/Index.vue`'s, which answers the same three facets through the same `ADR-0018` contract.
const page = usePage();
const errors = computed(() => (page.props.errors ?? {}) as Record<string, string>);

function applyFilters(): void {
    router.get(
        '/legacy',
        {
            trainee: trainee.value || undefined,
            scenario: scenario.value || undefined,
            tag: tag.value || undefined,
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
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'My data' }, { label: 'Legacy Lab' }]">
        <Head title="Legacy Lab" />
        <template #head>
            <PageHeader
                eyebrow="Legacy Lab"
                title="Build and compare inheritance pairs"
                description="A legacy is two parents and the sparks they pass on. Compare candidate pairs before committing one to a run."
            >
                <template #actions>
                    <!-- Slice 7 ruling: Compare is the filled action. Building a legacy is exploration;
                         comparing two is the decision, so the one primary sits on the decision and the
                         Filter below is a secondary control. -->
                    <LinkButton href="/legacy/compare" variant="primary">Compare two pairs</LinkButton>
                </template>
            </PageHeader>
        </template>

        <div class="grid gap-6 @3xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <section aria-labelledby="legacy-library">
                <h2 id="legacy-library" class="text-lg font-semibold text-ink-strong">Veteran library</h2>

                <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
                    <BaseSelect
                        v-model="trainee"
                        id="legacy-filter-trainee"
                        name="trainee"
                        label="Trainee"
                        :options="[{ value: '', label: 'All' }, ...Object.entries(trainees).map(([id, name]) => ({ value: id, label: name }))]"
                    />
                    <BaseSelect
                        v-model="scenario"
                        id="legacy-filter-scenario"
                        name="scenario"
                        label="Scenario"
                        :options="[{ value: '', label: 'All' }, ...Object.entries(scenarios).map(([key, label]) => ({ value: key, label }))]"
                    />
                    <BaseInput
                        v-model="tag"
                        id="legacy-filter-tag"
                        name="tag"
                        type="text"
                        placeholder="Distance, surface, style, Spark type"
                        label="Tag"
                    />
                    <BaseButton type="submit" variant="secondary">Filter</BaseButton>
                </form>

                <p v-if="errors.trainee" role="alert" class="mt-2 text-sm text-risk">Trainee: {{ errors.trainee }}</p>
                <p v-if="errors.scenario" role="alert" class="mt-2 text-sm text-risk">Scenario: {{ errors.scenario }}</p>
                <p v-if="errors.tag" role="alert" class="mt-2 text-sm text-risk">Tag: {{ errors.tag }}</p>

                <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

                <template v-if="veterans.data.length === 0">
                    <!-- Two different absences, and they need different words. design-2.0 §29 requires an
                         empty state to say what is missing, why it matters and what to do; the first case has
                         no records at all and the second has records this filter excludes, and telling a
                         Trainer to save a Veteran when the library is full is the wrong instruction. -->
                    <p
                        v-if="totalCount === 0"
                        class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
                    >
                        No Veterans recorded yet. A Veteran is a completed run you have finished logging, and
                        the library is where a finished career becomes something the next one can inherit from.
                        Finish a run and set its status to Completed, then it can be saved here.
                    </p>
                    <p
                        v-else
                        class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
                    >
                        Nothing matches these filters, and the library holds {{ totalCount }} record(s). Clear
                        the filters to see them all.
                    </p>
                </template>

                <template v-else>
                    <p class="mt-6 text-sm text-ink-muted">
                        {{ veterans.total }} of {{ totalCount }} Veterans
                    </p>

                    <ul class="mt-2 space-y-3">
                        <li
                            v-for="veteran in veterans.data"
                            :key="veteran.id"
                            class="rounded-md border border-rule bg-raised p-4 text-sm"
                        >
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <span class="font-semibold text-ink-strong">
                                    <Link :href="veteran.veteran_url" class="hover:underline transition-colors duration-150 ease-out">
                                        {{ veteran.label }}
                                    </Link>
                                    <span
                                        v-if="veteran.trainee_ja"
                                        lang="ja"
                                        class="ml-2 text-sm font-normal text-ink-muted"
                                    >{{ veteran.trainee_ja }}</span>
                                </span>
                                <div class="flex items-baseline gap-3 text-xs text-ink-muted">
                                    <span>{{ veteran.scenario_label }}</span>
                                    <a
                                        :href="`/legacy/compare?runs[]=${veteran.run_id}`"
                                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                                    >
                                        Compare
                                    </a>
                                </div>
                            </div>

                            <!-- Tags are the Trainer's own facets, printed as they were entered. No tag is
                                 expanded into "therefore this is a Speed parent": that is the derivation the
                                 library refuses (FR-G-4). -->
                            <p v-if="veteran.tags.length > 0" class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="tagName in veteran.tags"
                                    :key="tagName"
                                    class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink"
                                >{{ tagName }}</span>
                            </p>
                            <AbsenceValue v-else compact reason="You saved this Veteran with no tags." />

                            <p v-if="veteran.notes" class="mt-2 text-ink-muted">{{ veteran.notes }}</p>

                            <p class="mt-3 flex flex-wrap items-center gap-3">
                                <!--
                                    A run with a read-back opens its builder. One without says so instead of
                                    linking, because the builder is scoped to a run and there is nothing to edit
                                    until a Trainer has recorded something (D-220: a named absence, not a dead
                                    link).
                                -->
                                <a
                                    v-if="veteran.builder_url"
                                    :href="veteran.builder_url"
                                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                                >
                                    Open Legacy builder
                                </a>
                                <span
                                    v-else
                                    class="text-xs text-ink-muted"
                                    title="Open this run and record its Legacy selection, then it opens here."
                                >
                                    No Legacy selection recorded for this run yet.
                                </span>
                            </p>
                        </li>
                    </ul>

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
                </template>
            </section>

            <aside class="space-y-4" aria-label="Legacy reference">
                <!--
                    DOMAIN OVERRIDE: the HFW "What a legacy is worth" table is published reference data
                    (UMAMUSUME_REFERENCE §1.5.2), not a computation. The screen still prices no specific
                    pair: ADR-0020 §3 and FR-G-4 hold the inherited-stat arithmetic out of the library, and
                    the note below says so in the Trainer's own words. Slice 7 ruling: three rows, the blue
                    grades and the pink range being the whole starting-bonus economy.
                -->
                <div class="rounded-md border border-rule bg-raised p-4">
                    <h2 class="text-base font-semibold text-ink-strong">What a legacy is worth</h2>
                    <table class="mt-3 w-full text-sm">
                        <caption class="sr-only">Published spark reference values, by family</caption>
                        <tbody>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Blue spark, stat</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">+5 / +12 / +21 at ★1 / ★2 / ★3</td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Pink spark, aptitude</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">+1 / +2 / +3 / +4 grade</td>
                            </tr>
                            <tr>
                                <!-- DOMAIN OVERRIDE (DM-3): Green and White are the hint/skill sparks, never a
                                     flat stat boost at career start. The HFW drew them beside the stat families;
                                     they are labelled here as what they are, so the four colours stay correct. -->
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Green &amp; White sparks, hint / skill</th>
                                <td class="py-2 text-right text-ink">1–3 hint levels; never a flat stat at start</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            <b>Blue is the stat spark and Pink is the aptitude spark</b>
                            (<code>UMAMUSUME_REFERENCE.md</code> §1.5.2). Blue pays a flat stat at career start;
                            Pink lifts an aptitude grade by the star count. Green and White are hint and skill
                            sparks, which trigger at inspiration events rather than at the start. The
                            +16-per-★3 cap raise is the breakthrough, a different mechanic that moves the
                            ceiling, not the stat. This screen stores and compares what you enter; it prices
                            no pair of its own.
                        </span>
                    </p>
                </div>

                <div class="rounded-md border border-rule bg-raised p-4">
                    <h2 class="text-base font-semibold text-ink-strong">Parents available</h2>
                    <p class="mt-2 text-sm text-ink-muted">
                        <!-- Live count, never the HFW's captured figure: the library's own Veteran total. -->
                        {{ totalCount }} {{ totalCount === 1 ? 'veteran' : 'veterans' }} can be used as parents.
                        A veteran cannot be its own parent, and the two slots cannot hold the same record.
                    </p>
                    <LinkButton href="/veterans" variant="secondary" size="sm" class="mt-3">
                        Open the veteran library
                    </LinkButton>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
