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
 * "Read on" rather than "Updated" for that reason.
 *
 * **A dataset with no recorded fetch says so in words.** It does not print a dash and does not print
 * a zero: "No fetch recorded" and "fetched in March" are different facts, and only one is a problem
 * to act on.
 *
 * **The guide is named, not cited.** `the reference guide` is what the Trainer can be told; its file
 * path is a repository detail that belongs in the commit, not on the screen.
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
import { Head } from '@inertiajs/vue3';

interface FetchedDataset {
    name: string;
    url: string;
    fetched_at: string | null;
    note: string;
}

interface TranscribedDataset {
    name: string;
    url: string;
    anchored_at: string;
    note: string;
}

const props = defineProps<{
    docName: string;
    fetched: FetchedDataset[];
    transcribed: TranscribedDataset[];
    /** The recorded date the scenario matrix was checked against its sources. */
    matrixVerifiedAt: string;
}>();

/*
 * The disagreements the reference guide's Source Conflict Log still carries open
 * (`docs/UMAMUSUME_REFERENCE.md` §7, rows 2, 7 and 10). They are printed as conflicts because the
 * app must not pick a side on the Trainer's behalf: the side that is wrong costs a run. Kept here
 * rather than in a table because they are a reading of two published sources, not row data this
 * build owns; a resolved row leaves this list when the log resolves it.
 *
 * The mood race effect used to sit in this list. It is settled: section 1.3's dated resolution
 * (`:1385`, 2026-09-27) reads the client's own Mood Effect panel against the two guide camps and
 * resolves the race-side figure in GameWith's favour, 4% and 2% rather than 10% and 5%. The mock
 * still lists it as open (`database-sources.html:118`), so the ledger prints three conflicts and the
 * wireframe's tile prints four; the reference wins and the difference is recorded on the card.
 */
const CONFLICTS: ReadonlyArray<{ question: string; state: string }> = [
    { question: 'Is 1400 m Sprint or Mile?', state: 'Game8 says Sprint; the data export files every 1400 m course under Mile.' },
    { question: 'Can Wit training fail?', state: 'Unsettled: one guide lists a Wit failure branch, one argues the risk driver is absent.' },
    { question: 'Open League rank ceiling', state: 'Global notices exclude S and above; the JP notice excludes UC1 and above.' },
];
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
          HFW-OVERRIDE (data: ledger columns): the mock's Tier and State columns need a per-dataset tier
          and the row's own confirmation sentence, which this build does not hold; they are filed as a
          prop delta rather than invented. The Kind column states the one distinction the payload does
          carry: fetched (the pipeline read it) or transcribed (a hand pass read it).
          HFW-OVERRIDE (style: no current row): the mock marks its first ledger row `tr.is-current`;
          a ledger has no current row, so the tint is not drawn.
          HFW-OVERRIDE (style: no scroll wrapper): the card is the scroll region, as in the mock, so the
          explicit tab stop this page used to put around the table is gone. DESIGN.md scopes the
          focusable-region rule to the race calendar and the turn log, and the twelve links inside this
          ledger carry the keyboard across the clipped columns as focus reaches each one.
        -->
        <div class="card mt-4" role="region" aria-labelledby="sources-ledger">
            <h2 id="sources-ledger">Sources</h2>
            <table
                class="t"
                aria-label="Every source this tool reads, fetched or transcribed, with the date it was read"
            >
                <thead>
                    <tr>
                        <th scope="col">Source</th>
                        <th scope="col">Kind</th>
                        <th scope="col">Supplies</th>
                        <th scope="col">Read</th>
                        <th scope="col">Link</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="dataset in props.fetched" :key="`fetched-${dataset.name}`">
                        <th scope="row">{{ dataset.name }}</th>
                        <td><span class="pill pill-outline">Fetched</span></td>
                        <td>{{ dataset.note }}</td>
                        <td class="font-mono">
                            <template v-if="dataset.fetched_at !== null">
                                <time :datetime="dataset.fetched_at">{{ dataset.fetched_at }}</time>
                            </template>
                            <template v-else>No fetch recorded</template>
                        </td>
                        <td>
                            <a
                                v-if="dataset.url !== ''"
                                :href="dataset.url"
                                class="inline-flex min-h-11 items-center underline"
                                rel="noreferrer noopener"
                                target="_blank"
                            >Open source</a>
                            <template v-else>N/A<span class="sr-only">: this source publishes no page a Trainer can open.</span></template>
                        </td>
                    </tr>
                    <tr v-for="dataset in props.transcribed" :key="`transcribed-${dataset.name}`">
                        <th scope="row">{{ dataset.name }}</th>
                        <td><span class="pill pill-outline">Transcribed</span></td>
                        <td>{{ dataset.note }}</td>
                        <td class="font-mono">
                            <time :datetime="dataset.anchored_at">{{ dataset.anchored_at }}</time>
                        </td>
                        <td>
                            <a
                                v-if="dataset.url !== ''"
                                :href="dataset.url"
                                class="inline-flex min-h-11 items-center underline"
                                rel="noreferrer noopener"
                                target="_blank"
                            >Open source</a>
                            <template v-else>N/A<span class="sr-only">: this source publishes no page a Trainer can open.</span></template>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="props.fetched.length === 0 && props.transcribed.length === 0" class="note mt-3">
                No source datasets are configured in this build. The ledger fills as the source and
                reference configs name their documents.
            </p>
            <p class="note mt-3">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    A fetched row's date is the newest recorded fetch across every dataset; a transcribed
                    row's date is the day the transcribing pass read the document. Neither is an update
                    date, and neither is merged into the other.
                </span>
            </p>
        </div>

        <div class="grid gap-3.5 g-2 mt-4">
            <!--
              HFW-OVERRIDE (data: conflicts): the mock lists four open disagreements, one of them the
              mood race effect. The guide resolved that one on 2026-09-27 in GameWith's favour (4% and
              2%, not 10% and 5%), so a resolved row does not stay on a list headed "Open conflicts".
            -->
            <div class="card" role="region" aria-labelledby="conflicts-heading">
                <h2 id="conflicts-heading">Open conflicts</h2>
                <table class="t" aria-label="Disagreements between sources that this build leaves open">
                    <thead>
                        <tr>
                            <th scope="col">Question</th>
                            <th scope="col">State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="conflict in CONFLICTS" :key="conflict.question">
                            <th scope="row">{{ conflict.question }}</th>
                            <td>{{ conflict.state }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        A conflict is printed as a conflict. The app does not pick a side on the Trainer's
                        behalf, because the side that is wrong will cost a run. A disagreement the guide has
                        since resolved leaves this list: the mood race effect settled on 2026-09-27 in the
                        client panel's favour, so these three are the three still open.
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
                        <div class="v font-mono">{{ props.fetched.length + props.transcribed.length }}</div>
                    </div>
                    <div class="tile">
                        <div class="k">Open conflicts</div>
                        <div class="v font-mono">{{ CONFLICTS.length }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
