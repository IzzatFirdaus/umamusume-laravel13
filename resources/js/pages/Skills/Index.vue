<script setup lang="ts">
/*
 * Skill search, rebuilt to `skills.html` (plan Slice 8).
 *
 * The engine condition sits in monospace beside the readable name, because the source's own tokens are
 * the one representation that can be diffed against the export, and a Trainer who does not read them
 * still has the name. The cost column is the base cost only: a hint discount is a per-run fact the
 * catalogue cannot know, so the discount belongs to the run planner and not here.
 *
 * The type is a word in a neutral pill, never a colour: skills carry no saturated identity hue in this
 * system. Rows the source leaves untyped print N/A with the reason, and the type filter's own
 * "Unspecified" choice is how they are found again.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Pagination from '../../components/ui/Pagination.vue';
import BaseInput from '../../components/Form/BaseInput.vue';
import BaseSelect from '../../components/Form/BaseSelect.vue';
import BaseCheckbox from '../../components/Form/BaseCheckbox.vue';
import BaseButton from '../../components/Form/BaseButton.vue';
import { LinkButton } from '../../components/Form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    is_unique: boolean;
    type: string | null;
    sp_cost: number | null;
    /** The source's activation predicate, one entry per condition group. Nulls are groups with none. */
    conditions: (string | null)[];
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
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
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const search = ref(props.search ?? '');
const type = ref(props.type ?? '');
const unique = ref(props.unique);
const loading = ref(false);

function params(): Record<string, string | number | undefined> {
    return {
        search: search.value || undefined,
        type: type.value || undefined,
        unique: unique.value ? 1 : undefined,
    };
}

function applyFilters(): void {
    router.get('/skills', params(), {
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

function goToPage(page: number): void {
    router.get('/skills', { ...params(), page }, { preserveState: true, preserveScroll: true });
}

const engineCondition = (skill: Skill): string =>
    skill.conditions.filter((condition): condition is string => condition !== null && condition !== '').join(' · ');

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-3 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-3 text-right align-top font-mono text-sm text-ink';
const tdMono = 'border-b border-rule px-2 py-3 align-top font-mono text-xs text-ink';
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Library' }, { label: 'Skills' }]">
        <Head title="Skill search" />
        <template #head>
            <PageHeader
                eyebrow="Skills"
                title="Skill search"
                :description="`${props.totalCount} skills available on Global. The condition column is the source's own engine data, not a client paraphrase.`"
            />
        </template>

        <form
            class="flex flex-wrap items-end gap-3"
            :aria-busy="loading ? 'true' : null"
            @submit.prevent="applyFilters"
        >
            <label class="flex min-w-0 flex-1 basis-64 flex-col gap-1 text-xs font-semibold text-ink-muted">
                Search
                <BaseInput
                    id="skill-search"
                    v-model="search"
                    type="text"
                    name="search"
                    placeholder="Part of a skill name"
                    :error="errors.search"
                />
            </label>
            <label class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Type
                <BaseSelect
                    id="skill-type"
                    v-model="type"
                    name="type"
                    :options="[{ value: '', label: 'All' }, ...props.types.map((option) => ({ value: option, label: option })), { value: props.unspecifiedType, label: props.unspecifiedType }]"
                    placeholder="All"
                    :error="errors.type"
                />
            </label>
            <BaseCheckbox
                id="skill-unique"
                v-model="unique"
                name="unique"
                value="1"
                label="Unique only"
            />
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
            <a
                href="/skills"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <p v-if="props.search === null" class="mt-2 text-sm text-ink-muted">
            Type part of a skill name to narrow the catalog. The search reads the same normalized key the
            import writes, so capital letters and spacing are not part of the question.
        </p>

        <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

        <div v-if="props.skills.data.length === 0" class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p class="text-sm font-semibold text-ink-strong">
                <template v-if="props.totalCount === 0">The skill catalog holds no rows yet</template>
                <template v-else>Nothing matches {{ props.askedFor }}</template>
            </p>
            <p class="mt-1 text-sm text-ink-muted">
                <template v-if="props.totalCount === 0">
                    Fetch the skill document to fill it. Until that fetch runs, this screen has nothing
                    to search.
                </template>
                <template v-else>
                    <template v-if="props.searchKey !== null">Searched as the key “{{ props.searchKey }}”. </template>
                    If a skill you expect is not in the skill catalog, that is a gap in the fetched data
                    rather than a mistake in the query.
                </template>
            </p>
        </div>

        <template v-else>
            <div class="mt-4 overflow-x-auto rounded-md border border-rule bg-panel">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th :class="th">Skill</th>
                            <th :class="th">Type</th>
                            <th :class="th">Condition (engine)</th>
                            <th :class="thNum">Base cost</th>
                            <th :class="thNum"><span class="sr-only">Open the skill</span></th>
                        </tr>
                    </thead>
                    <tbody id="skill-results">
                        <tr v-for="skill in props.skills.data" :key="skill.id">
                            <td :class="td">
                                <span class="flex flex-wrap items-baseline gap-x-2">
                                    <Link
                                        :href="`/skills/${skill.id}`"
                                        class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out"
                                    >{{ skill.name }}</Link>
                                    <!-- The badge is a claim about the source's class code, and the title
                                         says whose derivation it is (D-256). -->
                                    <span
                                        v-if="skill.is_unique"
                                        class="pill pill-neutral"
                                        title="Marked from the source's skill class code, not from a card's own skill list."
                                    >✦ Unique</span>
                                </span>
                                <!-- 18 of the 623 Global rows store the same string in both name columns,
                                     so the second line only appears when it carries something new. -->
                                <span
                                    v-if="skill.name_ja !== null && skill.name_ja !== skill.name"
                                    lang="ja"
                                    class="block text-xs text-ink-muted"
                                >{{ skill.name_ja }}</span>
                            </td>
                            <td :class="td">
                                <span v-if="skill.type" class="pill pill-outline">{{ skill.type }}</span>
                                <span title="The source's effect codes give this skill no type word.">N/A</span><span class="sr-only">: the source's effect codes give this skill no type word.</span>
                            </td>
                            <td :class="tdMono">
                                <span v-if="engineCondition(skill)">{{ engineCondition(skill) }}</span>
                                <span title="The source states no activation condition for this skill.">N/A</span><span class="sr-only">: the source states no activation condition for this skill.</span>
                            </td>
                            <td :class="tdNum">
                                <template v-if="skill.sp_cost !== null">{{ skill.sp_cost }} SP</template>
                                <span title="The source states no SP cost for this class of skill.">N/A</span><span class="sr-only">: the source states no SP cost for this class of skill.</span>
                            </td>
                            <td :class="[td, 'text-right']">
                                <LinkButton :href="`/skills/${skill.id}`" variant="secondary" size="sm">Open</LinkButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="border-t border-rule px-2 py-2 text-xs text-ink-muted">
                    {{ props.skills.total }} of {{ props.totalCount }} skills available on Global
                </p>
            </div>

            <Pagination
                :page="props.skills.current_page"
                :pages="props.skills.last_page"
                :from="props.skills.from ?? 0"
                :to="props.skills.to ?? 0"
                :total="props.skills.total"
                noun="skills"
                @navigate="goToPage"
            />
        </template>

        <p class="mt-6 max-w-3xl text-xs text-ink-muted">
            The type word is this tool's reading of the source's effect codes, not a client label. The
            reading refuses a negative value and refuses a skill carrying two kinds of effect, so some
            rows show no word at all; the Type filter's “{{ props.unspecifiedType }}” choice finds those
            rows. Cost is the base cost only: a hint discount is per run, so the run planner owns it.
            Search matches the skill's name; no readable effect text is stored, so effect text cannot be
            searched.
        </p>
    </AppLayout>
</template>
