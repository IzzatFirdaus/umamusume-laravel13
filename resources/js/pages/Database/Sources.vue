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
}>();

/*
 * The four disagreements the reference guide's Source Conflict Log still carries open
 * (`docs/UMAMUSUME_REFERENCE.md` §7, rows 2, 7 and 10). They are printed as conflicts because the
 * app must not pick a side on the Trainer's behalf: the side that is wrong costs a run. Kept here
 * rather than in a table because they are a reading of two published sources, not row data this
 * build owns; a resolved row leaves this list when the log resolves it.
 */
const CONFLICTS: ReadonlyArray<{ question: string; state: string }> = [
    { question: 'Is 1400 m Sprint or Mile?', state: 'Game8 says Sprint; the data export files every 1400 m course under Mile.' },
    { question: 'Mood race effect: ±4% or ±10%?', state: 'The client panel prints ±4%; two guides print ±10%.' },
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

        <!-- The wireframe's tier vocabulary (`database-sources.html:76-89`). A tier is a property of the
             source, not of a dataset, so it is stated once here and the lists below keep their dates. -->
        <section class="card mt-4" aria-labelledby="source-tiers">
            <h2 id="source-tiers">Tiers</h2>
            <table class="mt-2 w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Tier</th>
                        <th class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left"><span class="pill pill-positive">S</span></th>
                        <td class="py-2 text-ink">Official publisher: the Cygames site or an in-game notice.</td>
                    </tr>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left"><span class="pill pill-positive">A</span></th>
                        <td class="py-2 text-ink">Maintained strategy wiki.</td>
                    </tr>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left"><span class="pill pill-caution">B</span></th>
                        <td class="py-2 text-ink">Data export or a single guide read.</td>
                    </tr>
                    <tr>
                        <th scope="row" class="py-2 pr-3 text-left"><span class="pill pill-neutral">C</span></th>
                        <td class="py-2 text-ink">Community observation, one frame.</td>
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
        </section>

        <!--
          The wireframe's ledger (`database-sources.html:90-136`): one row per source and one link to
          the publisher's page, because keybox 1 is that the screen replaces file paths in the
          interface. The mock's Tier and State columns need a per-dataset tier and the row's own
          confirmation sentence, which this build does not hold; they are filed as a prop delta rather
          than invented. The Kind column states the one distinction the payload does carry: fetched
          (the pipeline read it) or transcribed (a hand pass read it).
        -->
        <section class="card mt-4" aria-labelledby="sources-ledger">
            <h2 id="sources-ledger">Sources</h2>
            <div class="mt-2 overflow-x-auto" role="region" tabindex="0" aria-label="Sources, scrollable">
                <table class="w-full border-collapse text-sm">
                    <caption class="sr-only">Every source this tool reads, fetched or transcribed, with the date it was read</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Source</th>
                            <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Kind</th>
                            <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Supplies</th>
                            <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Read</th>
                            <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="dataset in props.fetched" :key="`fetched-${dataset.name}`" class="border-b border-rule align-top">
                            <th scope="row" class="px-2 py-2 text-left font-medium text-ink-strong">{{ dataset.name }}</th>
                            <td class="px-2 py-2"><span class="pill pill-outline">Fetched</span></td>
                            <td class="px-2 py-2 text-ink-muted">{{ dataset.note }}</td>
                            <td class="px-2 py-2 font-mono text-ink">
                                <template v-if="dataset.fetched_at !== null">
                                    <time :datetime="dataset.fetched_at">{{ dataset.fetched_at }}</time>
                                </template>
                                <template v-else>No fetch recorded</template>
                            </td>
                            <td class="px-2 py-2">
                                <a
                                    v-if="dataset.url !== ''"
                                    :href="dataset.url"
                                    class="inline-flex min-h-11 items-center underline"
                                    rel="noreferrer noopener"
                                    target="_blank"
                                >Open source</a>
                                <template v-else>N/A</template>
                            </td>
                        </tr>
                        <tr v-for="dataset in props.transcribed" :key="`transcribed-${dataset.name}`" class="border-b border-rule align-top">
                            <th scope="row" class="px-2 py-2 text-left font-medium text-ink-strong">{{ dataset.name }}</th>
                            <td class="px-2 py-2"><span class="pill pill-outline">Transcribed</span></td>
                            <td class="px-2 py-2 text-ink-muted">{{ dataset.note }}</td>
                            <td class="px-2 py-2 font-mono text-ink">
                                <time :datetime="dataset.anchored_at">{{ dataset.anchored_at }}</time>
                            </td>
                            <td class="px-2 py-2">
                                <a
                                    v-if="dataset.url !== ''"
                                    :href="dataset.url"
                                    class="inline-flex min-h-11 items-center underline"
                                    rel="noreferrer noopener"
                                    target="_blank"
                                >Open source</a>
                                <template v-else>N/A</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
        </section>

        <div class="mt-4 grid gap-4 g-2">
            <section class="card" aria-labelledby="conflicts-heading">
                <h2 id="conflicts-heading">Open conflicts</h2>
                <div class="mt-2 overflow-x-auto" role="region" tabindex="0" aria-label="Open conflicts, scrollable">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">Question</th>
                                <th scope="col" class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong">State</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="conflict in CONFLICTS" :key="conflict.question" class="border-b border-rule">
                                <th scope="row" class="px-2 py-2 text-left font-semibold text-ink">{{ conflict.question }}</th>
                                <td class="px-2 py-2 text-ink">{{ conflict.state }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        A conflict is printed as a conflict. The app does not pick a side on the Trainer's
                        behalf, because the side that is wrong will cost a run.
                    </span>
                </p>
            </section>

            <section class="card" aria-labelledby="purpose-heading">
                <h2 id="purpose-heading">What this page is for</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    Every Source link in the app lands here instead of printing a file path and a line
                    number in the interface. A citation belongs on the record; the screen only needs to
                    say which source it is.
                </p>
                <div class="mt-3 grid grid-cols-2 gap-2.5">
                    <div class="tile">
                        <p class="k">Sources tracked</p>
                        <p class="v">{{ props.fetched.length + props.transcribed.length }}</p>
                    </div>
                    <div class="tile">
                        <p class="k">Open conflicts</p>
                        <p class="v">{{ CONFLICTS.length }}</p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
