<script setup lang="ts">
/*
 * The skill catalogue's table body. One body, two URLs: the Library's skill search (`/skills`) and the
 * Database hub's Skills area (`/database/skills`, SCREEN-023, plan §8 D17). The host owns the shell and
 * the head band; `filterPath` is the address the filter form, the Clear link and the pager write back to,
 * so a Trainer filtering from the Database URL stays on it.
 *
 * The table is the wireframe's own grammar (`skills.html`): the engine condition in monospace beside the
 * readable name, the base cost right-aligned, and an Open column. The cost is the base cost only: a hint
 * discount is a per-run fact, so the run planner owns it (UMAMUSUME_REFERENCE.md §1.1.4).
 *
 * HFW-OVERRIDE (filters: Distance) - `skills.html:76-77` draws a Distance facet. This catalogue stores
 * no distance column: distance exists only inside the condition tokens (`distance_type==2` and friends),
 * so a picker over it would be a control the server cannot answer. The footer states the absence.
 *
 * HFW-OVERRIDE (search: effect text) - `skills.html:73` promises "Skill name or effect text". No readable
 * effect text is stored (G-SK-20), so the field matches the normalized name key only and its placeholder
 * says "Part of a skill name"; the footer states that too.
 */
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AbsenceValue from '../AbsenceValue.vue';
import BaseButton from '../Form/BaseButton.vue';
import BaseInput from '../Form/BaseInput.vue';
import BaseSelect from '../Form/BaseSelect.vue';
import { LinkButton } from '../Form';
import Pagination from '../ui/Pagination.vue';

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
    /**
     * The h2 the Database host passes ("Skill catalog"). The `/skills` host omits it: its own h1 already
     * names the search, and a second heading would say the same thing twice.
     */
    heading?: string | null;
    /** Where the filter form, the Clear link and the pager write back to. Required: a caller that omits
     *  it would silently filter from the Database URL back to `/skills`, so each page states its address. */
    filterPath: string;
}>();

const page = usePage();
/*
 * The filter's own validation errors, rendered on the field that failed. `SkillSearchRequest` rejects a
 * search longer than 120 characters and a type outside the parser's categories, and both messages belong
 * to the control a Trainer changes.
 */
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const search = ref(props.search ?? '');
const type = ref(props.type ?? '');
const unique = ref(props.unique ? '1' : '');
const loading = ref(false);

/**
 * `BaseSelect` answers the empty choice with `null`, which a native select paints as no selection at all.
 * The facets are strings, where '' is the "Any" option's own value, so each update lands back on it.
 */
const facet = (value: string | number | null): string => (value === null ? '' : String(value));

function params(): Record<string, string | number | undefined> {
    return {
        search: search.value || undefined,
        type: type.value || undefined,
        unique: unique.value === '1' ? 1 : undefined,
    };
}

function applyFilters(): void {
    router.get(props.filterPath, params(), {
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
    router.get(props.filterPath, { ...params(), page }, {
        preserveState: true,
        preserveScroll: true,
    });
}

const engineCondition = (skill: Skill): string =>
    skill.conditions.filter((condition): condition is string => condition !== null && condition !== '').join(' · ');
</script>

<template>
    <div>
        <!-- The heading names the catalog this component lists, and it must differ from the page's own h1:
             the hosts are titled "Skill search" and "Skills", so only the Database host passes one. -->
        <h2 v-if="props.heading" class="text-lg font-semibold text-ink-strong">{{ props.heading }}</h2>

        <form
            class="flex flex-wrap items-end gap-3"
            :class="props.heading ? 'mt-3' : ''"
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
                Unique
                <BaseSelect
                    id="skill-unique"
                    :model-value="unique"
                    name="unique"
                    :options="[{ value: '', label: 'Any' }, { value: '1', label: 'Unique only' }]"
                    :error="errors.unique"
                    @update:model-value="unique = facet($event)"
                />
            </label>
            <label class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Type
                <BaseSelect
                    id="skill-type"
                    :model-value="type"
                    name="type"
                    :options="[{ value: '', label: 'Any' }, ...props.types.map((option) => ({ value: option, label: option })), { value: props.unspecifiedType, label: props.unspecifiedType }]"
                    :error="errors.type"
                    @update:model-value="type = facet($event)"
                />
            </label>
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
            <a
                :href="props.filterPath"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <!-- D-65: the invitation belongs to the state where nobody has asked yet. -->
        <p v-if="props.search === null" class="mt-2 text-sm text-ink-muted">
            Type part of a skill name to narrow the catalog. The search reads the same normalized key the
            import writes, so capital letters and spacing are not part of the question.
        </p>

        <p v-if="loading" role="status" class="mt-3 text-sm text-ink-muted">Loading results…</p>

        <!-- Two absences, named apart: an empty catalogue is a fetch nobody has run, and an empty result
             is a question the data cannot answer. The no-match headline is the live region, because it is
             the result of an action; the empty catalogue is a static state and must not announce on load. -->
        <div v-if="props.skills.data.length === 0" class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p v-if="props.totalCount === 0" class="text-sm font-semibold text-ink-strong">
                The skill catalog holds no rows yet
            </p>
            <p v-else role="status" class="text-sm font-semibold text-ink-strong">
                Nothing matches {{ props.askedFor }}. Clear returns the full catalog.
            </p>
            <p class="mt-1 text-sm text-ink-muted">
                <template v-if="props.totalCount === 0">
                    Fetch the skill document to fill it: run
                    <code class="font-mono text-xs">php artisan uma:fetch gametora-skills</code>, or
                    <code class="font-mono text-xs">php artisan uma:reparse gametora-skills</code> if a fetch
                    says the document is unchanged. Until that fetch runs, this screen has nothing to search.
                </template>
                <template v-else>
                    <template v-if="props.searchKey !== null">Searched as the key “{{ props.searchKey }}”. </template>
                    If a skill you expect is not in the skill catalog, that is a gap in the fetched data
                    rather than a mistake in the query.
                </template>
            </p>
        </div>

        <template v-else>
            <div class="card mt-4">
                <div
                    class="relative overflow-x-auto"
                    role="region"
                    tabindex="0"
                    aria-label="Skill catalog, scrollable"
                >
                    <!-- `.t` is width:100%, so the min-width is what keeps the region scrolling at 320px;
                         do not simplify it to w-full. `relative` is what contains AbsenceValue's sr-only. -->
                    <table class="t min-w-2xl">
                        <caption class="sr-only">
                            Skill catalog: name, type, engine condition, base SP cost and a link to the detail
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-1/3">Skill</th>
                                <th scope="col">Type</th>
                                <th scope="col">Condition (engine)</th>
                                <th scope="col" class="n whitespace-nowrap">Base cost</th>
                                <th scope="col" class="n"><span class="sr-only">Open the skill</span></th>
                            </tr>
                        </thead>
                        <tbody id="skill-results">
                            <tr v-for="skill in props.skills.data" :key="skill.id">
                                <th scope="row" class="font-semibold text-ink-strong">
                                    <span class="flex flex-wrap items-baseline gap-x-2">
                                        <!-- The hit area is the link's own: the row is not a target, so the
                                             name link is the only way into the detail. `min-h-11` grows it to
                                             the house 44px bar without touching the type. -->
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
                                        class="block text-xs font-normal text-ink-muted"
                                    >{{ skill.name_ja }}</span>
                                </th>
                                <td>
                                    <span v-if="skill.type" class="pill pill-outline">{{ skill.type }}</span>
                                    <AbsenceValue v-else compact reason="The source's effect codes give this skill no type word." />
                                </td>
                                <!-- Engine tokens have no break opportunities, so a long predicate would
                                     otherwise set the whole table's min-content width and scroll it at
                                     desktop. Breaking anywhere is honest for a machine string. -->
                                <td class="font-mono break-all">
                                    <span v-if="engineCondition(skill) !== ''">{{ engineCondition(skill) }}</span>
                                    <AbsenceValue v-else compact reason="The source states no activation condition for this skill." />
                                </td>
                                <td class="n font-mono tabular-nums whitespace-nowrap">
                                    <span v-if="skill.sp_cost !== null">{{ skill.sp_cost }} SP</span>
                                    <AbsenceValue v-else compact reason="The source states no SP cost for this class of skill." />
                                </td>
                                <td class="n">
                                    <LinkButton :href="`/skills/${skill.id}`" variant="secondary" size="sm">
                                        Open
                                    </LinkButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span class="flex-1">
                        The condition column prints the export's own tokens. A race is simulated in phases and
                        the skill layer keys on phase flags rather than stat comparisons: the export carries
                        <code class="font-mono">phase&gt;=2</code>, <code class="font-mono">phase_random</code>,
                        <code class="font-mono">is_lastspurt==1</code> and
                        <code class="font-mono">is_last_straight==1</code> across its entries. A cell reading
                        N/A opens to the reason the source records nothing for it; it is not a zero.
                    </span>
                </p>
                <Pagination
                    :page="props.skills.current_page"
                    :pages="props.skills.last_page"
                    :from="props.skills.from ?? 0"
                    :to="props.skills.to ?? 0"
                    :total="props.skills.total"
                    noun="skills"
                    @navigate="goToPage"
                />
            </div>
        </template>

        <p class="mt-6 max-w-3xl text-xs text-ink-muted">
            The type word is this tool's reading of the source's effect codes, not a client label. The
            reading refuses a negative value and refuses a skill carrying two kinds of effect, so some
            rows show no word at all; the Type filter's “{{ props.unspecifiedType }}” choice finds those
            rows. The ✦ mark beside a name is the pipeline's derivation from the source's class code, not
            a card's own list. Cost is the base cost only: a hint discount is per run, so the run planner
            owns it. Search matches the skill's name; no readable effect text is stored, so effect text
            cannot be searched. Distance cannot be filtered separately: the catalogue stores it only
            inside the condition tokens, not in a distance column.
        </p>
    </div>
</template>
