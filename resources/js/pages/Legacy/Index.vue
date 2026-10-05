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
 * The page pattern is `Catalog/Index.vue`'s and no other: `AppLayout`, `<Head title>`, the `#title`
 * slot, `router.get` with `preserveState` / `preserveScroll`, and a `useForm` for writes. There is no
 * write on this page, so there is no `useForm` here.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface VeteranRow {
    id: number;
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
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
    <AppLayout>
        <Head title="Legacy Lab" />
        <template #title>Legacy Lab</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Veteran library</h2>

        <!--
            The record-only banner, on every Legacy screen, as glyph plus text (the prompt's
            requirement). The glyph is `aria-hidden` and the sentence carries the whole meaning, so the
            banner is one statement to a screen reader rather than two. `role="note"` is the right
            role for a standing caveat that is not an alert: it interrupts nothing and never takes
            focus, and nothing about it is conditional on a write having failed.
        -->
        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ notice }}</span>
        </p>

        <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex flex-col gap-1">
                <span class="text-ink">Trainee</span>
                <select
                    v-model="trainee"
                    name="trainee"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">All</option>
                    <option v-for="(name, id) in trainees" :key="id" :value="id">{{ name }}</option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink">Scenario</span>
                <select
                    v-model="scenario"
                    name="scenario"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <option value="">All</option>
                    <option v-for="(label, key) in scenarios" :key="key" :value="key">{{ label }}</option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink">Tag</span>
                <input
                    v-model="tag"
                    type="text"
                    name="tag"
                    placeholder="Distance, surface, style, Spark type"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
            </label>

            <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 font-semibold text-on-chrome">
                Filter
            </button>
        </form>

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
                            {{ veteran.trainee }}
                            <span
                                v-if="veteran.trainee_ja"
                                lang="ja"
                                class="ml-2 text-sm font-normal text-ink-muted"
                            >{{ veteran.trainee_ja }}</span>
                        </span>
                        <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                            <span>{{ veteran.scenario_label }}</span>
                            <span>{{ veteran.status_label }}</span>
                        </span>
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
                    <p v-else class="mt-2 text-xs text-ink-muted" title="You saved this Veteran with no tags.">
                        No tags.
                    </p>

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
                            class="inline-flex min-h-11 items-center rounded-full bg-chrome px-3 font-semibold text-on-chrome"
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

                        <a
                            :href="`/legacy/compare?runs[]=${veteran.run_id}`"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                        >
                            Compare
                        </a>
                    </p>
                </li>
            </ul>

            <nav v-if="veterans.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in veterans.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        :aria-current="link.active ? 'page' : undefined"
                        class="rounded-md border border-rule px-3 py-1 text-ink hover:bg-raised"
                        :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 text-ink-muted" v-html="link.label" />
                </template>
            </nav>
        </template>
    </AppLayout>
</template>
