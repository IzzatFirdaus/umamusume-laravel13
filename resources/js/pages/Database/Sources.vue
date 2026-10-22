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
</script>

<template>
    <AppLayout>
        <Head title="Data sources" />

        <template #title>Data sources</template>

        <p class="mt-2 max-w-3xl text-sm text-ink-muted">
            Every table in this tool comes from one of two places: a document this tool fetches, or
            {{ docName }} transcribed by hand. This page names both, and says when each was last read.
        </p>

        <section class="mt-8" aria-labelledby="fetched-heading">
            <h2 id="fetched-heading" class="text-lg font-semibold text-ink-strong">Fetched</h2>

            <p class="mt-1 max-w-3xl text-sm text-ink-muted">
                Read from the publisher's own document by the ingest pipeline. These move when the
                publisher publishes again.
            </p>

            <p
                v-if="props.fetched.length === 0"
                class="mt-3 rounded-md border border-dashed border-rule bg-panel p-4 text-sm text-ink-muted"
            >
                No source datasets are configured in this build. The pipeline's datasets appear here as
                the source config names them, and the date beside each row is the last recorded fetch.
            </p>

            <ul v-if="props.fetched.length > 0" class="mt-3 flex flex-col gap-3" role="list">
                <li
                    v-for="dataset in props.fetched"
                    :key="dataset.name"
                    class="rounded-md border border-rule bg-panel p-4"
                >
                    <p class="font-mono text-sm font-semibold text-ink-strong">{{ dataset.name }}</p>

                    <p class="mt-1 break-all text-sm text-ink">
                        <!-- The URL is the only content of its paragraph, so the link's own hit area is
                             the target. `min-h-11` grows it to the house 44px bar (DESIGN.md §12, KI-29);
                             `inline-flex` keeps it on the text's own line, and the paragraph grows with
                             it rather than the text being displaced. -->
                        <a
                            :href="dataset.url"
                            class="inline-flex min-h-11 items-center underline"
                            rel="noreferrer noopener"
                            target="_blank"
                        >{{ dataset.url }}</a>
                    </p>

                    <p class="mt-1 text-xs text-ink-muted">
                        <template v-if="dataset.fetched_at !== null">
                            <span class="sr-only">This dataset was</span>
                            Fetched <time :datetime="dataset.fetched_at">{{ dataset.fetched_at }}</time>.
                        </template>
                        <template v-else>No fetch is recorded for this dataset yet.</template>
                    </p>

                    <p class="mt-1 max-w-3xl text-xs text-ink-muted">{{ dataset.note }}</p>
                </li>
            </ul>
        </section>

        <section class="mt-10" aria-labelledby="transcribed-heading">
            <h2 id="transcribed-heading" class="text-lg font-semibold text-ink-strong">Transcribed</h2>

            <p class="mt-1 max-w-3xl text-sm text-ink-muted">
                Copied by hand from {{ docName }} on the date shown. A transcription does not update
                itself, so the date is when it was read rather than a promise that the guide has not
                moved.
            </p>

            <p
                v-if="props.transcribed.length === 0"
                class="mt-3 rounded-md border border-dashed border-rule bg-panel p-4 text-sm text-ink-muted"
            >
                Nothing has been transcribed in this build. The reference guide's datasets appear here
                once they are anchored in the reference config, with the date each one was read.
            </p>

            <ul v-if="props.transcribed.length > 0" class="mt-3 flex flex-col gap-3" role="list">
                <li
                    v-for="dataset in props.transcribed"
                    :key="dataset.name"
                    class="rounded-md border border-rule bg-panel p-4"
                >
                    <p class="text-sm font-semibold text-ink-strong">{{ dataset.name }}</p>

                    <p class="mt-1 text-xs text-ink-muted">
                        <span class="sr-only">This dataset was</span>
                        Read on <time :datetime="dataset.anchored_at">{{ dataset.anchored_at }}</time>.
                    </p>

                    <p class="mt-1 max-w-3xl text-xs text-ink-muted">{{ dataset.note }}</p>

                    <p v-if="dataset.url !== ''" class="mt-2 text-sm">
                        <a
                            :href="dataset.url"
                            class="inline-flex min-h-11 items-center underline"
                            rel="noreferrer noopener"
                            target="_blank"
                        >Open the publisher's page</a>
                    </p>
                </li>
            </ul>
        </section>
    </AppLayout>
</template>
