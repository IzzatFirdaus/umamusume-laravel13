<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Typed locally, the rule every page in this rewrite follows: TypeScript 7 does not resolve the
// generated `PageProps` union, so an imported type would fail the typecheck rather than describe the
// screen. `resources/js/types.ts` stays the contract the server side cites.
interface Veteran {
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
    veteran_url: string;
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
    filters: { trainee: number | null; scenario: string | null; tag: string | null };
    trainees: Record<string, number>;
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

const traineeOptions = computed(() =>
    Object.entries(props.trainees).map(([name, id]) => ({ name, id })),
);

const trainee = ref(props.filters.trainee === null ? '' : String(props.filters.trainee));
const scenario = ref(props.filters.scenario ?? '');
const tag = ref(props.filters.tag ?? '');
const loading = ref(false);

function filteredQuery(order: 'newest' | 'oldest'): Record<string, string> {
    const query: Record<string, string> = { order };

    if (trainee.value !== '') {
        query.trainee = trainee.value;
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
    () =>
        props.filters.trainee !== null ||
        props.filters.scenario !== null ||
        props.filters.tag !== null,
);
</script>

<template>
    <AppLayout>
        <Head title="Veteran library" />
        <template #title>Veteran library</template>

        <p class="text-sm text-ink-muted" role="note">{{ props.notice }}</p>

        <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex flex-col gap-1">
                <span class="text-ink">Trainee</span>
                <select
                    v-model="trainee"
                    name="trainee"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                >
                    <option value="">All</option>
                    <option v-for="option in traineeOptions" :key="option.id" :value="String(option.id)">
                        {{ option.name }}
                    </option>
                </select>
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink">Scenario</span>
                <select
                    v-model="scenario"
                    name="scenario"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
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
                    placeholder="One tag, as you typed it"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                >
            </label>

            <button
                type="submit"
                class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome"
            >
                Filter
            </button>
        </form>

        <p v-if="errors.trainee" role="alert" class="mt-2 text-sm text-risk">Trainee: {{ errors.trainee }}</p>
        <p v-if="errors.scenario" role="alert" class="mt-2 text-sm text-risk">Scenario: {{ errors.scenario }}</p>
        <p v-if="errors.tag" role="alert" class="mt-2 text-sm text-risk">Tag: {{ errors.tag }}</p>

        <!-- `SCREEN-021`'s "Find Parents for This Build". A search, not an optimizer: it hands the tags this
             list is filtered by to the Legacy Lab's browse list, which answers on stored columns only. No
             candidate is scored here, because `ADR-0020` §3 keeps a recommendation out of the record screens. -->
        <p class="mt-3 flex flex-wrap items-center gap-3 text-sm">
            <a
                :href="props.find_parents_url"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
            >Find parents for this build</a>
            <span class="text-ink-muted">Searches the Legacy Lab with the tags you are filtering by.</span>
        </p>

        <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

        <p class="mt-6 text-sm text-ink-muted">
            <template v-if="totalCount === 0">
                The library holds no Veterans yet.
            </template>
            <template v-else-if="filtered">
                {{ veterans.total }} of {{ totalCount }} Veterans match the filters.
            </template>
            <template v-else>
                {{ veterans.total }} Veterans in the library.
            </template>
        </p>

        <!-- The two lines are different states and neither is an error: an empty library means nothing has
             been filed, and a filtered empty list means the filters asked a question no row answers. The
             empty one carries a door now that D16's write half exists: a career is filed from its own
             Result screen, so the way in is the run list rather than an unexplained box. -->
        <div
            v-if="totalCount === 0"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            <p>{{ props.filingNotice }}</p>
            <a
                :href="props.file_veteran_url"
                class="enamel mt-3 inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome"
            >Open the run list</a>
        </div>

        <p
            v-else-if="veterans.data.length === 0"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
            data-testid="no-match"
        >
            Nothing matches those filters. The three facets are the only ones the library can answer, and
            they narrow rather than widen: a tag you typed that no Veteran carries returns no rows.
        </p>

        <ul v-else class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
            <li v-for="veteran in veterans.data" :key="veteran.id" class="px-4 py-3 text-sm">
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    <a :href="veteran.veteran_url" class="font-semibold text-ink-strong hover:underline">
                        {{ veteran.trainee }}
                    </a>

                    <span
                        v-if="veteran.trainee_ja !== null && veteran.trainee_ja !== veteran.trainee"
                        lang="ja"
                        class="text-ink-muted"
                    >
                        {{ veteran.trainee_ja }}
                    </span>

                    <span class="ml-auto text-xs text-ink-muted">
                        {{ veteran.scenario_label }} · {{ veteran.status_label }}
                    </span>
                </div>

                <p v-if="veteran.tags.length > 0" class="mt-1 flex flex-wrap gap-1 text-xs">
                    <span
                        v-for="rowTag in veteran.tags"
                        :key="rowTag"
                        class="rounded-full bg-sunken px-2 py-0.5 text-ink-strong"
                        title="A tag you typed on this record. Tags are the Trainer's own words, not client vocabulary."
                    >
                        {{ rowTag }}
                    </span>
                </p>

                <p v-else class="mt-1 text-xs">
                    <span title="No tag was recorded on this Veteran.">N/A</span>
                    <span class="text-ink-muted"> tagged</span>
                </p>

                <p v-if="veteran.notes !== null" class="mt-1 text-xs text-ink-muted">{{ veteran.notes }}</p>

                <div class="mt-2 flex flex-wrap gap-2">
                    <a
                        v-if="veteran.builder_url"
                        :href="veteran.builder_url"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                    >
                        Open in Legacy Lab
                    </a>
                    <!-- The library's own comparison, which lines careers up. It takes the Veteran id rather
                         than the run id, and it does not require a Legacy read-back: the career a Trainer
                         most wants beside another is often the one they just filed. The Legacy Lab's compare
                         stays where it belongs, comparing the configurations behind `builder_url` above. -->
                    <a
                        :href="`/veterans/compare?veterans[]=${veteran.id}`"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                    >
                        Compare this career
                    </a>
                </div>
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

        <p class="mt-6 flex flex-wrap items-center gap-2 text-sm text-ink-muted">
            <span>Order:</span>
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md px-3 font-medium"
                :class="props.order === 'newest' ? 'bg-raised font-bold text-ink-strong' : 'text-ink hover:bg-raised'"
                :aria-current="props.order === 'newest' ? 'true' : undefined"
                @click="visitOrder('newest')"
            >
                Newest first
            </button>
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md px-3 font-medium"
                :class="props.order === 'oldest' ? 'bg-raised font-bold text-ink-strong' : 'text-ink hover:bg-raised'"
                title="Order of the library's own records. No column holds the date a career finished, so this is not a completion date."
                :aria-current="props.order === 'oldest' ? 'true' : undefined"
                @click="visitOrder('oldest')"
            >
                Oldest first
            </button>
        </p>

        <section aria-labelledby="library-absences" class="mt-8">
            <h2 id="library-absences" class="text-xs font-bold tracking-wide text-ink-muted">
                NOT IN THIS BUILD
            </h2>
            <ul class="mt-2 space-y-2 text-sm text-ink-muted">
                <li v-for="absence in absences" :key="absence.label">
                    <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                </li>
            </ul>
        </section>
    </AppLayout>
</template>
