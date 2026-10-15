<script setup lang="ts">
/*
 * The skill search list, shared by the skill search (`/skills`) and the Database hub's Skills area
 * (`/database/skills`, SCREEN-023, plan §8 D17).
 *
 * One list body, two URLs. The page that mounts it owns the shell and the heading; `filterPath` is
 * the address the filter form and the pager write back to, so a Trainer filtering from the Database
 * URL stays on it.
 */
import { Link, router, usePage } from '@inertiajs/vue3';
import Pagination from '../Pagination.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import SparkChip from '../SparkChip.vue';
import BaseSelect from '../Form/BaseSelect.vue';
import BaseInput from '../Form/BaseInput.vue';
import BaseButton from '../Form/BaseButton.vue';
import BaseCheckbox from '../Form/BaseCheckbox.vue';
import { computed, ref } from 'vue';

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    is_unique: boolean;
    type: string | null;
    sp_cost: number | null;
    /** Provenance state for the skill's data. */
    provenance?: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
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
    /** Where the filter form and the pager write back to. Required: a caller that omits it would
     *  silently filter from the Database URL back to `/skills`, so each page states its own address. */
    filterPath: string;
}>();

const page = usePage();
const typeError = computed(() => (page.props.errors as Record<string, string | undefined> | undefined)?.type ?? null);

const search = ref(props.search ?? '');
const type = ref(props.type ?? '');
const unique = ref(props.unique);
const loading = ref(false);

function applyFilters(): void {
    router.get(
        props.filterPath,
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
    <h2 class="text-lg font-semibold text-ink-strong">Skill search</h2>

    <!-- Control sizes are measured, not assumed (DESIGN.md §6.14 fixes a form input at 44px,
         and `h-11` is this repository's idiom for it). The checkbox is the WCAG 2.2 AA floor
         of 24px, with the label carrying the rest of the click area. -->
    <div class="mt-4 rounded-md border border-rule bg-raised p-4">
        <form class="flex flex-wrap items-end gap-3 text-sm" @submit.prevent="applyFilters">
            <label class="flex flex-col gap-1">
                <span class="text-ink">Search</span>
                <BaseInput
                    v-model="search"
                    type="text"
                    id="skill-search"
                    name="search"
                    placeholder="Part of a skill name"
                />
            </label>

            <label class="flex flex-col gap-1">
                <span class="text-ink">Type</span>
                <BaseSelect
                    v-model="type"
                    :options="[{ value: '', label: 'All' }, ...types.map((option) => ({ value: option, label: option })), { value: unspecifiedType, label: unspecifiedType }]"
                    placeholder="All"
                />
            </label>

            <BaseCheckbox
              v-model="unique"
              name="unique"
              value="1"
              label="Unique only"
            />

            <BaseButton type="submit" variant="primary" size="md">
                Filter
            </BaseButton>
        </form>

        <p v-if="typeError" class="mt-2 text-sm text-risk">Type: {{ typeError }}</p>
    </div>

    <!-- D-65: the invitation belongs to the state where nobody has asked yet. -->
    <p v-if="search === null" class="mt-2 text-sm text-ink-muted">
        Type part of a skill name to narrow the catalog. The search reads the same normalized key the
        import writes, so capital letters and spacing are not part of the question.
    </p>

    <p v-if="loading" role="status" class="mt-4 text-sm text-ink-muted">Loading results…</p>

    <template v-if="skills.data.length === 0">
        <p
            v-if="totalCount === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            The skill catalog holds no rows yet. Run <code>php artisan uma:fetch gametora-skills</code> to fill it,
            or <code>php artisan uma:reparse gametora-skills</code> if a fetch says the document is unchanged.
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
                <Link :href="`/skills/${skill.id}`" class="font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out">{{ skill.name }}</Link>

                <!-- 18 of the 623 [Global] rows store the same string in both name columns, so the
                     second slot only appears when it carries something the first does not. -->
                <span v-if="skill.name_ja !== null && skill.name_ja !== skill.name" lang="ja" class="text-ink-muted">
                    {{ skill.name_ja }}
                </span>

                <SparkChip
                    v-if="skill.is_unique"
                    label="Unique"
                    variant="skill"
                    size="sm"
                    title="Marked from the source's skill class code, not from a card's own skill list."
                />

                <SparkChip
                    v-if="skill.type"
                    :label="skill.type"
                    variant="skill"
                    size="sm"
                />

                <ProvenanceBadge
                    v-if="skill.provenance"
                    :state="skill.provenance"
                />

                <span class="ml-auto text-xs text-ink-muted">
                    <template v-if="skill.sp_cost !== null"> {{ skill.sp_cost }} SP</template>
                    <template v-else>
                        · <span title="The source states no SP cost for this class of skill.">N/A</span>
                    </template>
                </span>
            </li>
        </ul>

        <Pagination :links="skills.links" />
    </template>

    <p class="mt-6 max-w-3xl text-xs text-ink-muted">
        The type word is this tool's reading of the source's effect codes, not a client label. The reading
        refuses a negative value and refuses a skill carrying two kinds of effect, so some rows show no word
        at all; the Type filter's “{{ unspecifiedType }}” choice finds those rows.
    </p>
</template>
