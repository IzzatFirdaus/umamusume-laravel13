<script setup lang="ts">
/*
 * Where every dataset this tool prints comes from (slice 2.2).
 *
 * Every reference view used to end with its own sentence naming a file path and a section number.
 * The Trainer could neither open the file nor find the section, so the sentence told them less than
 * the one fact it had: this table was transcribed rather than fetched, on a date. This page answers
 * that question once for every dataset.
 *
 * **Two lists, because the two age differently.** A fetched dataset's freshness is a recorded
 * timestamp and goes stale the moment the publisher republishes. A transcribed one's is the day the
 * transcribing pass read the document, and the document may have moved since; the column is headed
 * "Read" rather than "Updated" for that reason.
 *
 * **A dataset with no recorded fetch says so in words.** It does not print a dash and does not print
 * a zero: "No fetch recorded" and "fetched in March" are different facts, and only one is a problem
 * to act on.
 *
 * **The guide is named, not cited.** `the reference guide` is what the Trainer can be told; its file
 * path is a repository detail that belongs in the commit, not on the screen.
 *
 * N6.5 / TD-08: the ledger narrows in place. Five facets read facts the payload already carries —
 * kind, tier, whether a read date was recorded, whether an open conflict names the row, and what the
 * record shows across open *and* resolved rows. The result count is announced rather than implied,
 * because a filter whose label lies about how much it narrowed is the failure this screen removes.
 *
 * TD-08's five historical states exist because a two-state boolean cannot make the distinction that
 * matters: a source named only by settled rows is not in a live dispute but is also not untouched,
 * and a source the records could not place is not a clean one. `unknown` therefore outranks `none`,
 * and the state always prints a word — never an ink — with its own explanation on `title`.
 *
 * N2.11: a dated row names the provenance arm that dated it. The server unions this tool's fetch
 * ledger with the reference rows' own inline stamp (ADR-0003 R3) and says which won; the screen has
 * to surface that, because a ledger date and an inline date are different facts. A row dated by
 * neither names no arm at all.
 *
 * Laid out as `database-sources.html`: the `div.info` verification band, then the Tiers card, then the
 * Sources ledger, then the two closing cards in a `g-2` grid. Every table is the ported `table.t` and
 * every card is the ported `.card`, so the card is the scroll region a wide ledger needs.
 * HFW-OVERRIDE (data: ledger columns) and (style: no current row) are recorded on the ledger itself;
 * (data: conflicts) is recorded beside the list below.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

/**
 * The five historical involvement states (TD-08). `unknown` is missing evidence, never a clean bill.
 */
type HistoryInvolvement = 'open' | 'resolved' | 'mixed' | 'none' | 'unknown';

/** Which recorded arm supplied a row's date (N2.11). `null` means neither arm recorded one. */
type Channel = 'inline' | 'data_sources' | null;

interface FetchedDataset {
    name: string;
    url: string;
    fetched_at: string | null;
    channel: Channel;
    tier: string | null;
    note: string;
    conflicts: string[];
    open_conflict_count: number;
    in_open_conflict: boolean;
    history_involvement: HistoryInvolvement;
    resolved_conflicts: string[];
    history_involvement_note: string;
}

interface TranscribedDataset {
    name: string;
    url: string;
    anchored_at: string;
    tier: string | null;
    note: string;
    conflicts: string[];
    open_conflict_count: number;
    in_open_conflict: boolean;
    history_involvement: HistoryInvolvement;
    resolved_conflicts: string[];
    history_involvement_note: string;
}

interface ConflictRecord {
    id: string;
    question: string;
    claims: string[];
    state: string;
    affected_sources: string[];
    evidence_available?: boolean;
}

const props = defineProps<{
    docName: string;
    fetched: FetchedDataset[];
    transcribed: TranscribedDataset[];
    /** The disagreements the build still leaves open. Open-only by construction. */
    conflicts: ConflictRecord[];
    /**
     * The settled rows, shipped as their own history. "We checked and it is settled" and "we never
     * looked" are different answers, and deleting the resolved row would make them look alike.
     */
    conflict_history: ConflictRecord[];
    /**
     * At least one conflict record could not establish which datasets it involves. Disclosed on its
     * own, independently of any known involvement, and never treated as an exhaustive answer.
     */
    conflict_evidence_unavailable: boolean;
    /** The recorded date the scenario matrix was checked against its sources. */
    matrixVerifiedAt: string;
}>();

/** One ledger row, whichever arm it came from. The screen reads one shape for both. */
interface LedgerRow {
    key: string;
    name: string;
    kind: 'fetched' | 'transcribed';
    url: string;
    tier: string | null;
    note: string;
    /** ISO date the row was read, or `null` when no arm recorded one. */
    date_iso: string | null;
    channel: Channel;
    conflicts: string[];
    history_involvement: HistoryInvolvement;
    resolved_conflicts: string[];
    history_involvement_note: string;
}

const rows = computed((): LedgerRow[] => {
    const fetched = props.fetched.map(
        (dataset): LedgerRow => ({
            key: `fetched-${dataset.name}`,
            name: dataset.name,
            kind: 'fetched',
            url: dataset.url,
            tier: dataset.tier,
            note: dataset.note,
            date_iso: dataset.fetched_at,
            channel: dataset.channel,
            conflicts: dataset.conflicts ?? [],
            history_involvement: dataset.history_involvement,
            resolved_conflicts: dataset.resolved_conflicts ?? [],
            history_involvement_note: dataset.history_involvement_note,
        }),
    );

    const transcribed = props.transcribed.map(
        (dataset): LedgerRow => ({
            key: `transcribed-${dataset.name}`,
            name: dataset.name,
            kind: 'transcribed',
            url: dataset.url,
            tier: dataset.tier,
            note: dataset.note,
            // A transcribed row's date is its anchor, which the transcribing pass wrote itself. It is
            // dated, and it names no cross-store arm: there were never two arms to choose between.
            date_iso: dataset.anchored_at,
            channel: null,
            conflicts: dataset.conflicts ?? [],
            history_involvement: dataset.history_involvement,
            resolved_conflicts: dataset.resolved_conflicts ?? [],
            history_involvement_note: dataset.history_involvement_note,
        }),
    );

    return [...fetched, ...transcribed];
});

/*
 * Every facet keeps `'all'` as a real option rather than relying on the control's empty value. An
 * empty `<option>` would be indistinguishable from "nothing chosen", and this page's whole point is
 * that an unstated state and a stated one are different facts.
 */
const ALL = 'all';

const kindFilter = ref<string>(ALL);
const tierFilter = ref<string>(ALL);
const readFilter = ref<string>(ALL);
const conflictFilter = ref<string>(ALL);
const historyFilter = ref<string>(ALL);

/** The tier classes the corpus itself records, in weight order. No tier is invented here. */
const TIERS = ['S', 'A', 'B', 'C'] as const;

const tierOptions = computed((): { value: string; label: string }[] => {
    const present = new Set<string>();

    for (const row of rows.value) {
        if (row.tier !== null && row.tier !== '') {
            present.add(row.tier);
        }
    }

    // Only the classes a row actually carries, plus "any". A tier option matching nothing would be a
    // filter that promises a narrowing it cannot deliver.
    return [
        { value: ALL, label: 'Any tier' },
        ...TIERS.filter((tier) => present.has(tier)).map((tier) => ({ value: tier, label: `Tier ${tier}` })),
    ];
});

const historyOptions = [
    { value: ALL, label: 'Any history' },
    { value: 'open', label: 'Open' },
    { value: 'resolved', label: 'Resolved' },
    { value: 'mixed', label: 'Open and resolved' },
    { value: 'none', label: 'No record' },
    { value: 'unknown', label: 'Not established' },
];

const kindOptions = [
    { value: ALL, label: 'Any kind' },
    { value: 'fetched', label: 'Fetched' },
    { value: 'transcribed', label: 'Transcribed' },
];

const readOptions = [
    { value: ALL, label: 'Any read date' },
    { value: 'dated', label: 'Recorded' },
    { value: 'undated', label: 'No fetch recorded' },
];

const conflictOptions = [
    { value: ALL, label: 'Any dispute' },
    { value: 'involved', label: 'Named by an open conflict' },
    { value: 'clear', label: 'Not named by an open conflict' },
];

/** One predicate per facet, conjoined. Each answers one question about one row. */
const filtered = computed((): LedgerRow[] =>
    rows.value.filter((row) => {
        if (kindFilter.value !== ALL && row.kind !== kindFilter.value) {
            return false;
        }

        if (tierFilter.value !== ALL && row.tier !== tierFilter.value) {
            return false;
        }

        if (readFilter.value === 'dated' && row.date_iso === null) {
            return false;
        }

        if (readFilter.value === 'undated' && row.date_iso !== null) {
            return false;
        }

        if (conflictFilter.value === 'involved' && row.conflicts.length === 0) {
            return false;
        }

        if (conflictFilter.value === 'clear' && row.conflicts.length > 0) {
            return false;
        }

        if (historyFilter.value !== ALL && row.history_involvement !== historyFilter.value) {
            return false;
        }

        return true;
    }),
);

const isFiltered = computed(
    (): boolean =>
        kindFilter.value !== ALL
        || tierFilter.value !== ALL
        || readFilter.value !== ALL
        || conflictFilter.value !== ALL
        || historyFilter.value !== ALL,
);

/**
 * The page's own statement of what it is showing. A filtered ledger says how much it narrowed and out
 * of what; an unfiltered one states its total. The count is never left for the reader to derive.
 */
const statusText = computed((): string =>
    isFiltered.value
        ? `${filtered.value.length} of ${rows.value.length} sources match.`
        : `${rows.value.length} sources.`,
);

function clearFilters(): void {
    kindFilter.value = ALL;
    tierFilter.value = ALL;
    readFilter.value = ALL;
    conflictFilter.value = ALL;
    historyFilter.value = ALL;
}

/** The Trainer-facing name for the arm that dated a row. Never the raw table name. */
function channelLabel(channel: Channel): string {
    if (channel === 'data_sources') {
        return 'the fetch ledger';
    }

    if (channel === 'inline') {
        return "the row's own stamp";
    }

    return '';
}

/** The five states as words. `unknown` must never read like `none`. */
const historyLabel: Record<HistoryInvolvement, string> = {
    open: 'Open',
    resolved: 'Resolved',
    mixed: 'Open and resolved',
    none: 'No record',
    unknown: 'Not established',
};

/** Conflicts whose id the page actually prints, mapped to the question a door should promise. */
const conflictsById = computed((): Map<string, string> => {
    const map = new Map<string, string>();

    for (const conflict of [...props.conflicts, ...props.conflict_history]) {
        map.set(conflict.id, conflict.question);
    }

    return map;
});
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Data sources' }]">
        <Head title="Data sources" />

        <template #head>
            <PageHeader
                eyebrow="Source ledger"
                title="Where every figure came from"
                description="One row per source: what it supplies, when it was read, and how much weight it carries. This is the page the Source links point at."
            />
        </template>

        <DatabaseNav />

        <!--
          The wireframe's verification band (`database-sources.html:72-76`) is the ported `div.info`:
          the mark is decoration, the words carry the fact. It records when the matrix was checked
          against these sources, not when anything was fetched, and says so.
        -->
        <div class="info mt-4">
            <span aria-hidden="true">i</span>
            <span>
                <b>Matrix verified {{ props.matrixVerifiedAt }}.</b>
                A verification date records when the matrix was checked against its sources.
                Nothing on this page was fetched today.
            </span>
        </div>

        <!-- The wireframe's tier vocabulary (`database-sources.html:78-89`). A tier is a property of the
             source, not of a dataset, so it is stated once here and the lists below keep their dates. -->
        <div class="card mt-4" role="region" aria-labelledby="source-tiers">
            <h2 id="source-tiers">Tiers</h2>
            <table class="t" aria-label="Source tiers and what each one means">
                <thead>
                    <tr>
                        <th scope="col">Tier</th>
                        <th scope="col">Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row"><span class="pill pill-positive">S</span></th>
                        <td>Official publisher: the Cygames site or an in-game notice.</td>
                    </tr>
                    <tr>
                        <th scope="row"><span class="pill pill-positive">A</span></th>
                        <td>Maintained strategy wiki.</td>
                    </tr>
                    <tr>
                        <th scope="row"><span class="pill pill-caution">B</span></th>
                        <td>Data export or a single guide read.</td>
                    </tr>
                    <tr>
                        <th scope="row"><span class="pill pill-neutral">C</span></th>
                        <td>Community observation, one frame.</td>
                    </tr>
                </tbody>
            </table>
            <p class="note mt-3">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    A tier says how much weight a source carries; the date beside a dataset says when it was
                    read. Neither is merged into the other, and no row is recommended because of its tier.
                </span>
            </p>
        </div>

        <!--
          The wireframe's ledger (`database-sources.html:91-109`): one row per source and one link to
          the publisher's page, because keybox 1 is that the screen replaces file paths in the
          interface.
          HFW-OVERRIDE (data: ledger columns): the mock's State column wants the row's own confirmation
          sentence, which this build does not hold; it is filed as a prop delta rather than invented.
          The Kind column states the one distinction the payload does carry: fetched (the pipeline
          read it) or transcribed (a hand pass read it). Tier and dispute history are the payload's
          own recorded values, printed as words.
          HFW-OVERRIDE (style: no current row): the mock marks its first ledger row `tr.is-current`;
          a ledger has no current row, so the tint is not drawn.
          HFW-OVERRIDE (a11y: WCAG 2.1.1, applied where a card scrolls): the ledger card scrolls at a
          320px measure - six columns against one phone reading. Its last column holds the page's
          links, so focusing one already pans the region; the region's left edge - the source names,
          which is what a reader pans back to check - carries no focusable node at all. The tab stop is
          kept for that reason, and one behaviour for every scrolling card is easier to reason about
          than four. DESIGN.md:447-455 names the mechanism for the race calendar and the turn log; the
          authority for using it here is WCAG 2.1.1.
        -->
        <div class="card mt-4" role="region" aria-labelledby="sources-ledger" tabindex="0">
            <h2 id="sources-ledger">Sources</h2>

            <!--
              The five facets. Read-only: nothing here writes, and every one of them narrows the set
              already on screen. They sit above the status sentence so a reader hears the count after
              changing a control, which is the order the announcement is useful in.
            -->
            <div class="mt-3 flex flex-wrap items-end gap-3">
                <BaseSelect
                    id="sources-kind-filter"
                    name="sources-kind-filter"
                    v-model="kindFilter"
                    :options="kindOptions"
                    label="Kind"
                />

                <BaseSelect
                    id="sources-tier-filter"
                    name="sources-tier-filter"
                    v-model="tierFilter"
                    :options="tierOptions"
                    label="Tier"
                />

                <BaseSelect
                    id="sources-read-filter"
                    name="sources-read-filter"
                    v-model="readFilter"
                    :options="readOptions"
                    label="Read date"
                />

                <BaseSelect
                    id="sources-conflict-filter"
                    name="sources-conflict-filter"
                    v-model="conflictFilter"
                    :options="conflictOptions"
                    label="Open conflict"
                />

                <BaseSelect
                    id="sources-history-filter"
                    name="sources-history-filter"
                    v-model="historyFilter"
                    :options="historyOptions"
                    label="Dispute history"
                />

                <button
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm underline"
                    @click="clearFilters"
                >Clear filters</button>
            </div>

            <p role="status" aria-atomic="true" class="mt-3 text-sm text-ink-muted">{{ statusText }}</p>

            <table
                class="t mt-2"
                aria-label="Every source this tool reads, fetched or transcribed, with the date it was read"
            >
                <thead>
                    <tr>
                        <th scope="col">Source</th>
                        <th scope="col">Kind</th>
                        <th scope="col">Tier</th>
                        <th scope="col">Supplies</th>
                        <th scope="col">Read</th>
                        <th scope="col">Link</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in filtered" :key="row.key" :data-source="row.name">
                        <th scope="row">{{ row.name }}</th>
                        <td>
                            <span class="pill pill-outline">{{ row.kind === 'fetched' ? 'Fetched' : 'Transcribed' }}</span>
                        </td>
                        <td>{{ row.tier ?? 'No tier recorded' }}</td>
                        <td>
                            <span>{{ row.note }}</span>

                            <!--
                              TD-08: the dispute history as a word with its own explanation. The
                              `title` is the server's own sentence, so `unknown` cannot be mistaken for
                              a clean bill of health by colour alone or by a shorter synonym.
                            -->
                            <span
                                class="ml-1 inline-flex items-baseline gap-1 text-xs text-ink-muted"
                                :data-history-state="row.history_involvement"
                                :title="row.history_involvement_note"
                            >
                                <span aria-hidden="true">·</span>
                                <span>{{ historyLabel[row.history_involvement] }}</span>
                            </span>

                            <!--
                              TD-08's open facet, printed as doors rather than as a count: the row is
                              named by these records, and each door opens the card that prints both
                              sides of the disagreement. `data-named-in` is the facet's own predicate in
                              the DOM, so the control and the row cannot disagree.
                            -->
                            <span
                                v-if="row.conflicts.length > 0"
                                class="ml-1 inline-flex flex-wrap items-baseline gap-1 text-xs"
                                :data-named-in="row.conflicts.join(' ')"
                            >
                                <span class="text-ink-muted">Named in</span>
                                <a
                                    v-for="id in row.conflicts"
                                    :key="id"
                                    :href="`/database/sources#${id}`"
                                    class="inline-flex min-h-6 items-center underline"
                                    :title="conflictsById.get(id) ?? id"
                                >{{ id }}</a>
                            </span>
                        </td>
                        <td class="font-mono">
                            <template v-if="row.date_iso !== null">
                                <time :datetime="row.date_iso">{{ row.date_iso }}</time>
                                <!--
                                  N2.11: which arm dated this row. A transcribed row carries no
                                  channel: its date is its own anchor, so there were never two arms to
                                  choose between and naming one would be an invented provenance.
                                -->
                                <span
                                    v-if="row.channel !== null"
                                    class="ml-1 text-xs text-ink-muted"
                                    :data-channel="row.channel"
                                >({{ channelLabel(row.channel) }})</span>
                            </template>
                            <template v-else>No fetch recorded</template>
                        </td>
                        <td>
                            <a
                                v-if="row.url !== ''"
                                :href="row.url"
                                class="inline-flex min-h-11 items-center underline"
                                rel="noreferrer noopener"
                                target="_blank"
                            >Open source</a>
                            <template v-else>N/A<span class="sr-only">, this source publishes no page a Trainer can open.</span></template>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!--
              Two empty states, deliberately distinct. "No source datasets are configured in this
              build" is a statement about the build; "no sources match" is a statement about the
              filters. Collapsing them would tell a Trainer their build is empty when they have
              simply narrowed past every row.
            -->
            <p v-if="rows.length === 0" class="note mt-3">
                No source datasets are configured in this build. The ledger fills as the source and
                reference configs name their documents.
            </p>

            <div v-else-if="filtered.length === 0" class="note mt-3">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    No sources match the current filters. The ledger lists the sources this build
                    actually reads, so an empty result is a narrowing and not an empty corpus.
                    <button
                        type="button"
                        class="ml-1 inline-flex min-h-11 items-center underline"
                        @click="clearFilters"
                    >Clear filters</button>
                </span>
            </div>

            <p class="note mt-3">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    A fetched row's date is that dataset's own newest recorded fetch, and the words
                    beside it name which of the two recorded arms supplied that date; a transcribed
                    row's date is the day the transcribing pass read the document. Neither is an
                    update date, and one dataset's fetch is never lent to another.
                </span>
            </p>
        </div>

        <div class="grid gap-3.5 g-2 mt-4">
            <!--
              HFW-OVERRIDE (data: conflicts): the mock lists four open disagreements, one of them the
              mood race effect. The guide resolved that one on 2026-09-27 in GameWith's favour (4% and
              2%, not 10% and 5%), so a resolved row does not stay on a list headed "Open conflicts".
              It prints beneath them instead: "we checked and it is settled" and "we never looked" are
              different answers a Trainer cannot tell apart if the resolved row is deleted.
            -->
            <div class="card" role="region" aria-labelledby="conflicts-heading">
                <h2 id="conflicts-heading">Open conflicts</h2>
                <table class="t" aria-label="Disagreements between sources that this build leaves open">
                    <thead>
                        <tr>
                            <th scope="col">Question</th>
                            <th scope="col">What each side says</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="conflict in props.conflicts"
                            :key="conflict.id"
                            :id="conflict.id"
                            :data-conflict-id="conflict.id"
                        >
                            <th scope="row">{{ conflict.question }}</th>
                            <td>{{ conflict.claims.join(' ') }}</td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="props.conflicts.length === 0" class="note mt-3">
                    No disagreement is recorded as open. That is a statement about the conflict log,
                    not a claim that every figure above is settled.
                </p>

                <template v-if="props.conflict_history.length > 0">
                    <h3 id="conflict-history-heading" class="mt-4 text-sm font-semibold text-ink-strong">
                        Resolved conflicts
                    </h3>
                    <table
                        class="t mt-1"
                        aria-labelledby="conflict-history-heading"
                        aria-label="Disagreements the owner has since resolved"
                    >
                        <thead>
                            <tr>
                                <th scope="col">Question</th>
                                <th scope="col">What each side said</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="conflict in props.conflict_history"
                                :key="conflict.id"
                                :id="conflict.id"
                                :data-conflict-id="conflict.id"
                            >
                                <th scope="row">{{ conflict.question }}</th>
                                <td>{{ conflict.claims.join(' ') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>

                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        A conflict is printed as a conflict. The app does not pick a side on the Trainer's
                        behalf, because the side that is wrong will cost a run. The
                        {{ props.conflicts.length }} rows above are the ones still open.
                    </span>
                </p>

                <!--
                  The evidence gap, named rather than absorbed into the count. This is a `.warn` rather
                  than a `.note` on purpose: missing evidence is a risk the reader has to act on, and
                  the two blocks also stay distinguishable to a selector.
                -->
                <p
                    v-if="props.conflict_evidence_unavailable"
                    class="warn mt-2"
                    data-evidence="unplaced"
                >
                    <span class="g" aria-hidden="true">!</span>
                    <span>
                        At least one record does not establish which datasets it involves. A source
                        the records cannot place is reported as <em>Not established</em> in the
                        ledger, never as though the record had cleared it.
                    </span>
                </p>
            </div>

            <div class="card" role="region" aria-labelledby="purpose-heading">
                <h2 id="purpose-heading">What this page is for</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    Every Source link in the app lands here instead of printing a file path and a line
                    number in the interface. A citation belongs on the record; the screen only needs to
                    say which source it is.
                </p>
                <div class="grid g-2 mt-3 gap-3.5">
                    <div class="tile">
                        <div class="k">Sources tracked</div>
                        <div class="v font-mono">{{ rows.length }}</div>
                    </div>
                    <div class="tile">
                        <div class="k">Open conflicts</div>
                        <div class="v font-mono">{{ props.conflicts.length }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
