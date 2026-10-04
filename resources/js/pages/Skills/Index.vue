<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    is_unique: boolean;
    type: string | null;
    sp_cost: number | null;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    skills: Paginator<Skill>;
    search: string | null;
    searchKey: string | null;
    type: string | null;
    types: string[];
    unspecifiedType: string;
    unique: boolean;
    totalCount: number;
    askedFor: string;
}>();

const page = usePage();
const typeError = computed(() => (page.props.errors as Record<string, string | undefined> | undefined)?.type ?? null);

const search = ref(props.search ?? '');
const type = ref(props.type ?? '');
const unique = ref(props.unique);
const loading = ref(false);

function applyFilters(): void {
    router.get(
        '/skills',
        {
            search: search.value || undefined,
            type: type.value || undefined,
            unique: unique.value ? 1 : undefined,
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
        <Head title="Skill search" />
        <template #title>Skill search</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Skill search</h2>

        <!-- Control sizes are measured, not assumed (DESIGN.md §6.14 fixes a form input at 44px,
             and `h-11` is this repository's idiom for it). The checkbox is the WCAG 2.2 AA floor
             of 24px, with the label carrying the rest of the click area. -->
        <form class="mt-4 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex flex-col gap-1">
                <span class="text-ink">Search</span>
                <input
                    v-model="search"
                    type="text"
                    name="search"
                    placeholder="Part of a skill name"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                >
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink">Type</span>
                <select
                    v-model="type"
                    name="type"
                    class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                >
                    <option value="">All</option>
                    <option v-for="option in types" :key="option" :value="option">{{ option }}</option>
                    <option :value="unspecifiedType">{{ unspecifiedType }}</option>
                </select>
            </label>

            <label class="flex items-center gap-2 text-ink">
                <input
                    v-model="unique"
                    type="checkbox"
                    name="unique"
                    value="1"
                    class="size-6 rounded border-rule bg-raised focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green"
                >
                <span>Unique only</span>
            </label>

            <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">
                Filter
            </button>
        </form>

        <!-- D-65: the invitation belongs to the state where nobody has asked yet. -->
        <p v-if="search === null" class="mt-2 text-sm text-ink-muted">
            Type part of a skill name to narrow the catalog. The search reads the same normalized key the
            import writes, so capital letters and spacing are not part of the question.
        </p>

        <p v-if="typeError" class="mt-2 text-sm text-risk">Type: {{ typeError }}</p>

        <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

        <template v-if="skills.data.length === 0">
            <p
                v-if="totalCount === 0"
                class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
            >
                The skill catalog holds no rows yet. Run `php artisan uma:fetch gametora-skills` to fill it,
                or `php artisan uma:reparse gametora-skills` if a fetch says the document is unchanged.
            </p>
            <p
                v-else
                class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
            >
                Nothing matches {{ askedFor }}.
                <template v-if="searchKey !== null">
                    Searched as the key “{{ searchKey }}”.
                </template>
                If a skill you expect is not in the skill catalog, that is a gap in the fetched data rather
                than a mistake in the query.
            </p>
        </template>

        <template v-else>
            <p class="mt-6 text-sm text-ink-muted">{{ skills.total }} of {{ totalCount }} skills available on [Global]</p>

            <ul id="skill-results" class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
                <li
                    v-for="skill in skills.data"
                    :key="skill.id"
                    class="flex flex-wrap items-baseline gap-x-2 gap-y-1 px-4 py-3 text-sm"
                >
                    <a :href="`/skills/${skill.id}`" class="font-semibold text-ink-strong hover:underline">{{ skill.name }}</a>

                    <!-- 18 of the 623 [Global] rows store the same string in both name columns, so the
                         second slot only appears when it carries something the first does not. -->
                    <span v-if="skill.name_ja !== null && skill.name_ja !== skill.name" lang="ja" class="text-ink-muted">
                        {{ skill.name_ja }}
                    </span>

                    <span
                        v-if="skill.is_unique"
                        class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                        title="Marked from the source's skill class code, not from a card's own skill list."
                    >✦ Unique</span>

                    <span class="ml-auto text-xs text-ink-muted">
                        <template v-if="skill.type"> · {{ skill.type }}</template>

                        <template v-if="skill.sp_cost !== null"> · {{ skill.sp_cost }} SP</template>
                        <template v-else>
                            · <span title="The source states no SP cost for this class of skill.">N/A</span>
                        </template>
                    </span>
                </li>
            </ul>

            <nav v-if="skills.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-2 text-sm">
                <template v-for="(link, index) in skills.links" :key="index">
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

        <p class="mt-6 max-w-3xl text-xs text-ink-muted">
            The type word is this tool's reading of the source's effect codes, not a client label. The reading
            refuses a negative value and refuses a skill carrying two kinds of effect, so some rows show no word
            at all; the Type filter's “{{ unspecifiedType }}” choice finds those rows.
        </p>
    </AppLayout>
</template>
