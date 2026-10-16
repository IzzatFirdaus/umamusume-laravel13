<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { BaseInput, BaseSelect, BaseButton } from '../../components/Form';
import { carriesAncestryNote, VETERAN_ANCESTRY_NOTE, veteranResultClause } from '../../domain/veteranNote';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VeteranRow from '../../components/VeteranRow.vue';

// Typed locally, the rule every page in this rewrite follows: TypeScript 7 does not resolve the
// generated `PageProps` union, so an imported type would fail the typecheck rather than describe the
// screen. `resources/js/types.ts` stays the contract the server side cites.
interface Veteran {
    id: number;
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
    veteran_url: string;
    portrait_url: string | null;
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

/** The hoisted sentence is only stated when a card on this page actually carried it. */
const hasSharedAncestryNote = computed(() =>
    props.veterans.data.some((veteran) => carriesAncestryNote(veteran.notes)),
);
</script>

<template>
    <AppLayout>
        <Head title="Veteran library" />
        <template #eyebrow>Library</template>
        <template #title>Veteran library</template>

        <p
            role="note"
            class="mt-3 flex items-start gap-2 rounded-md border border-rule bg-sunken px-3 py-2 text-sm text-ink"
        >
            <span aria-hidden="true" class="font-mono font-bold text-ink-strong">i</span>
            <span>{{ props.notice }}</span>
        </p>

        <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <BaseSelect
                v-model="trainee"
                id="veteran-filter-trainee"
                name="trainee"
                label="Trainee"
                :options="[{ value: '', label: 'All' }, ...Object.entries(traineeOptions).map(([id, name]) => ({ value: id, label: name }))]"
            />
            <BaseSelect
                v-model="scenario"
                id="veteran-filter-scenario"
                name="scenario"
                label="Scenario"
                :options="[{ value: '', label: 'All' }, ...Object.entries(scenarios).map(([key, label]) => ({ value: key, label }))]"
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
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
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

        <!-- Stated once for the whole list, above it, rather than once per card: the ancestry sentence is
             about what the tracker did not record, which is the same for every row here, and repeating it
             seven times buries the one clause per card that is actually about that career. -->
        <p v-if="hasSharedAncestryNote" class="mt-2 text-sm text-ink-muted">
            {{ VETERAN_ANCESTRY_NOTE }}
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
                class="mt-3 inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 font-semibold text-on-primary-action"
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
            <li v-for="veteran in veterans.data" :key="veteran.id">
                <VeteranRow :veteran="veteran" />
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

        <details class="mt-8">
            <summary id="library-absences" class="text-lg font-semibold text-ink-strong cursor-pointer list-none">
                Not in this build
                <span class="text-sm font-normal text-ink-muted ml-2">(3 notes)</span>
            </summary>
            <ul class="mt-2 space-y-2 text-sm text-ink-muted">
                <li v-for="absence in absences" :key="absence.label">
                    <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                </li>
            </ul>
        </details>
    </AppLayout>
</template>
