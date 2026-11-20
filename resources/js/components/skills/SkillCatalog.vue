<script setup lang="ts">
/* Shared by the Library and Database skill catalogs. The host supplies its filter URL. */
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AbsenceValue from '../AbsenceValue.vue';
import BaseButton from '../Form/BaseButton.vue';
import BaseInput from '../Form/BaseInput.vue';
import BaseSelect from '../Form/BaseSelect.vue';
import { LinkButton } from '../Form';
import Pagination from '../ui/Pagination.vue';
import { displayDescription, langOf, NO_CLIENT_STRING } from '../../domain/skillDisplay';

export interface Skill {
    id: number;
    /** Null on a JapanOnly row: `name` there is the export's `enname`, not client copy (`R1`). */
    name: string | null;
    name_ja: string | null;
    is_unique: boolean;
    type: string | null;
    type_withheld: boolean;
    sp_cost: number | null;
    description: string | null;
    /** The row's availability, so the description gate is the shared predicate rather than a second rule. */
    release_status: string | null;
    /** The source's activation predicate, one entry per condition group. Nulls are groups with none. */
    conditions: (string | null)[];
}

export interface Paginator<T> {
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
    /**
     * The parsed `distance_type` the request narrowed to (1..4), `0` for Unspecified, or null for no
     * distance filter. Declared here because the select paints it: without the declaration Vue drops
     * the server's value into `$attrs` and `props.distance` is undefined, so `?distance=2` filtered the
     * rows while the control said "Any".
     */
    distance: number | null;
    /** The scope the catalogue answers with: `global` (default), `all`, or `japan_only`. */
    scope: string;
    /** The scope select's options, keyed by URL token. */
    scopes: Record<string, string>;
    unique: boolean;
    totalCount: number;
    askedFor: string;
    filterPath: string;
    heading?: string | null;
    /** Count of skills with range operators on distance_type (<= or >=) that cannot be filtered. */
    rangeOpCount: number;
    /**
     * Full-table counts by release status, for the header. Deliberately not scoped: this is the
     * figure that lets a Trainer tell "Japan-only is empty" from "Japan-only is filtered out",
     * while the paginator states the current scope's own row count.
     */
    release_status_counts: { global: number; japan_only: number; other: number };
}>();

const page = usePage();

/**
 * The list's current query, carried onto the detail link so the way back can resume the search.
 *
 * Read off `page.url` rather than `window.location.search`: a `preserveState` visit keeps this component
 * mounted, so the window value has no reactive dependency to update on and would serve a stale suffix.
 */
const querySuffix = computed<string>(() => {
    const at = page.url.indexOf('?');

    return at === -1 ? '' : page.url.slice(at);
});

/** The detail address, carrying the list's query and which list it came from (the back target). */
const detailHref = (id: number): string => {
    const params = new URLSearchParams(querySuffix.value);
    params.set('from', props.filterPath);

    return `/skills/${id}?${params.toString()}`;
};
/*
 * The filter's own validation errors, rendered on the field that failed. `SkillSearchRequest` rejects a
 * search longer than 120 characters and a type outside the parser's categories, and both messages belong
 * to the control a Trainer changes.
 */
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const search = ref(props.search ?? '');
const type = ref(props.type ?? '');
const unique = ref(props.unique ? '1' : '');
const distance = ref(props.distance === null ? '' : String(props.distance));
const scope = ref(props.scope);
const loading = ref(false);

const distanceOptions = [
    { value: '', label: 'Any' },
    { value: '1', label: 'Sprint' },
    { value: '2', label: 'Mile' },
    { value: '3', label: 'Medium' },
    { value: '4', label: 'Long' },
    { value: '0', label: 'Unspecified' },
];

const appliedFilters = computed(() => {
    const distanceLabel = distanceOptions.find(option => option.value === String(props.distance))?.label;

    return [
        props.search === null ? null : `Search “${props.search}”`,
        props.unique ? 'Unique only' : null,
        props.type === null ? null : `Type ${props.type}`,
        distanceLabel === undefined ? null : `Distance ${distanceLabel}`,
    ].filter((label): label is string => label !== null);
});

const clearHref = computed(() => props.scope === 'global'
    ? props.filterPath
    : `${props.filterPath}?${new URLSearchParams({ scope: props.scope })}`);

const effectText = (skill: Skill): string =>
    displayDescription({ description: skill.description, releaseStatus: skill.release_status });

/**
 * `BaseSelect` answers the empty choice with `null`, which a native select paints as no selection at all.
 * The facets are strings, where '' is the "Any" option's own value, so each update lands back on it.
 */
const facet = (value: string | number | null): string => (value === null ? '' : String(value));

function params(): Record<string, string | number | undefined> {
    return {
        search: search.value || undefined,
        pageSize: new URLSearchParams(querySuffix.value).get('pageSize') ?? undefined,
        type: type.value || undefined,
        unique: unique.value === '1' ? 1 : undefined,
        distance: distance.value || undefined,
        // `global` is the server default, so it is omitted rather than carried: a URL saying the same
        // thing as no URL keeps a Trainer's address bar honest about what they chose.
        scope: scope.value === 'global' ? undefined : scope.value || undefined,
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
    const applied = new URLSearchParams(querySuffix.value);
    applied.set('page', String(page));
    router.get(props.filterPath, Object.fromEntries(applied), {
        preserveState: true,
        preserveScroll: true,
    });
}

const engineConditions = (skill: Skill): string[] =>
    skill.conditions.filter((condition): condition is string => condition !== null && condition !== '');

/**
 * The name a row prints, and the language it prints it in.
 *
 * At `japan_only` the server sends `name: null` (its `name` column is the export's `enname`, which the
 * Air Messiah ruling bars from UI copy), so the source's own `name_ja` is the name. At the other scopes
 * `name` is client copy and `name_ja` is the second line, printed only when it says something new.
 */
const displayName = (skill: Skill): string => skill.name ?? skill.name_ja ?? '';

/** The second line: the Japanese name, when the row already printed a different one. */
const secondaryName = (skill: Skill): string | null =>
    skill.name !== null && skill.name_ja !== null && skill.name_ja !== skill.name ? skill.name_ja : null;
</script>

<template>
    <div>
        <!-- The heading names the catalog this component lists, and it must differ from the page's own h1:
             the hosts are titled "Skill search" and "Skills", so only the Database host passes one. -->
        <h2 v-if="props.heading" class="text-lg font-semibold text-ink-strong">{{ props.heading }}</h2>

        <form
            data-region="filters"
            class="flex flex-wrap items-end gap-3 rounded-md border border-rule-soft bg-panel p-3"
            :class="props.heading ? 'mt-3' : ''"
            :aria-busy="loading"
            @submit.prevent="applyFilters"
        >
            <label data-region="field" class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Scope
                <BaseSelect
                    id="skill-scope"
                    :model-value="scope"
                    name="scope"
                    :options="Object.entries(props.scopes).map(([value, label]) => ({ value, label }))"
                    :error="errors.scope"
                    @update:model-value="scope = facet($event)"
                />
            </label>
            <label data-region="field" class="flex min-w-0 flex-1 basis-64 flex-col gap-1 text-xs font-semibold text-ink-muted">
                Search
                <BaseInput
                    id="skill-search"
                    v-model="search"
                    type="text"
                    name="search"
                    autocorrect="off"
                    autocapitalize="none"
                    :spellcheck="false"
                    enterkeyhint="search"
                    placeholder="Part of a skill name"
                    :error="errors.search"
                />
            </label>
            <label data-region="field" class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
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
            <label data-region="field" class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
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
            <label data-region="field" class="flex flex-col gap-1 text-xs font-semibold text-ink-muted">
                Distance
                <BaseSelect
                    id="skill-distance"
                    :model-value="distance"
                    name="distance"
                    :options="distanceOptions"
                    :error="errors.distance"
                    @update:model-value="distance = facet($event)"
                />
            </label>
            <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                Filter
            </BaseButton>
            <a
                :href="clearHref"
                class="inline-flex min-h-11 items-center px-2 text-sm text-ink-muted hover:underline transition-colors duration-150 ease-out"
            >Clear</a>
        </form>

        <p v-if="appliedFilters.length === 0" class="mt-2 text-sm text-ink-muted">
            Type part of a skill name or effect to narrow the catalog.
        </p>
        <p v-else class="mt-2 text-sm text-ink-muted">
            <span class="font-semibold text-ink-strong">Active filters</span>
            {{ appliedFilters.join(' · ') }}
        </p>
        <p class="mt-2 text-sm text-ink-muted" role="status" aria-atomic="true" data-testid="result-summary">
            <template v-if="loading">Loading results…</template>
            <template v-else>
                <span class="font-mono tabular-nums text-ink-strong">{{ props.skills.total }}</span>
                matching of
                <span class="font-mono tabular-nums text-ink-strong">{{ props.totalCount }}</span>
                in {{ props.scopes[props.scope] ?? props.scope }}
            </template>
        </p>

        <!-- Two absences, named apart: an empty catalogue is a fetch nobody has run, and an empty result
             is a question the data cannot answer. The no-match headline is the live region, because it is
             the result of an action; the empty catalogue is a static state and must not announce on load. -->
        <div v-if="props.skills.data.length === 0" class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p v-if="props.totalCount === 0" class="text-sm font-semibold text-ink-strong">
                The skill catalog holds no rows yet
            </p>
            <p v-else class="text-sm font-semibold text-ink-strong">
                Nothing matches {{ props.askedFor }}. Clear returns the full catalog for this scope.
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
                <details class="border-b border-rule-soft">
                    <summary class="min-h-11 cursor-pointer py-3 text-sm font-semibold text-ink-strong">
                        Release counts and sources
                    </summary>
                    <div
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-rule px-3 py-2 text-sm"
                        data-testid="provenance-counts"
                    >
                        <!-- `Calculated` follows DESIGN.md §provenance. The description states the arithmetic. -->
                        <span
                            class="pill pill-neutral text-xs"
                            data-footnote-id="provenance-source"
                            aria-describedby="provenance-source"
                        >Calculated</span>
                        <span class="text-ink-muted">
                            Global <span data-count-kind="global" class="font-mono tabular-nums text-ink-strong">{{ props.release_status_counts.global }}</span>
                        </span>
                        <span class="text-ink-muted">
                            Japan only <span data-count-kind="japan_only" class="font-mono tabular-nums text-ink-strong">{{ props.release_status_counts.japan_only }}</span>
                        </span>
                        <span class="text-ink-muted">
                            Other <span data-count-kind="other" class="font-mono tabular-nums text-ink-strong">{{ props.release_status_counts.other }}</span>
                        </span>
                    </div>
                    <p id="provenance-source" class="note m-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            Source: the <code class="font-mono">release_status</code> column from the client
                            export, counted over the whole table. These figures do not change with the scope
                            filter: the scope narrows the rows below, and the paginator states how many of
                            them it is showing. A row the client has not shipped is counted under Japan only
                            and is never reachable at the default scope.
                        </span>
                    </p>
                </details>
                <div
                    class="relative overflow-x-auto scroll-p-2"
                    role="region"
                    tabindex="0"
                    aria-label="Skill catalog, scrollable"
                >
                    <!-- `.t` is width:100%, so the min-width is what keeps the region scrolling at 320px;
                         do not simplify it to w-full. `relative` is what contains AbsenceValue's sr-only. -->
                    <table class="t min-w-2xl">
                        <caption class="sr-only">
                            Skills, effects, base SP costs and technical details
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-1/3">Skill</th>
                                <th scope="col">Type</th>
                                <th scope="col">Effect</th>
                                <th scope="col" class="n whitespace-nowrap">Base cost</th>
                                <th scope="col"><span class="sr-only">Technical details</span></th>
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
                                            :href="detailHref(skill.id)"
                                            :lang="langOf(displayName(skill)) ?? undefined"
                                            class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out"
                                        >{{ displayName(skill) }}</Link>
                                        <!-- The badge is a claim about the source's class code, and the title
                                             says whose derivation it is (D-256). -->
                                        <span
                                            v-if="skill.is_unique"
                                            class="pill pill-neutral"
                                            title="Marked from the source's skill class code, not from a card's own skill list."
                                        >✦ Unique<span class="sr-only">, from the source's skill class code, not a rarity word</span></span>
                                    </span>
                                    <!-- 18 of the 623 Global rows store the same string in both name columns,
                                         so the second line only appears when it carries something new. At
                                         `japan_only` the name column already printed `name_ja`, so this
                                         line is suppressed there by `secondaryName` returning null. -->
                                    <span
                                        v-if="secondaryName(skill) !== null"
                                        :lang="langOf(secondaryName(skill)) ?? undefined"
                                        class="block text-xs font-normal text-ink-muted"
                                    >{{ secondaryName(skill) }}</span>
                                </th>
                                <td>
                                    <span v-if="skill.type" class="pill pill-outline">{{ skill.type }}</span>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        :reason="skill.type_withheld
                                            ? 'Untyped by design. The effects are non-positive or span more than one supported type.'
                                            : 'Type unknown. The recorded effects do not establish a supported classification.'"
                                    />
                                </td>
                                <td class="max-w-prose text-sm">
                                    <AbsenceValue
                                        v-if="effectText(skill) === NO_CLIENT_STRING"
                                        compact
                                        reason="No client string. A verified readable effect is unavailable for this skill."
                                    />
                                    <span v-else>{{ effectText(skill) }}</span>
                                </td>
                                <td class="n font-mono tabular-nums whitespace-nowrap">
                                    <span v-if="skill.sp_cost !== null">{{ skill.sp_cost }} SP</span>
                                    <AbsenceValue v-else compact reason="The source states no SP cost for this class of skill." />
                                </td>
                                <td>
                                    <details>
                                        <summary class="min-h-11 cursor-pointer py-3 text-sm font-medium text-ink-strong">
                                            Technical details<span class="sr-only"> for {{ displayName(skill) }}</span>
                                        </summary>
                                        <ul v-if="engineConditions(skill).length" class="mt-1 list-none space-y-1">
                                            <li v-for="(condition, index) in engineConditions(skill)" :key="index">
                                                <code class="select-text break-all font-mono text-xs">{{ condition }}</code>
                                            </li>
                                        </ul>
                                        <AbsenceValue v-else compact reason="The source states no activation predicate for this skill." />
                                        <LinkButton :href="detailHref(skill.id)" variant="secondary" size="sm" class="mt-2">
                                            View mechanics and sources
                                        </LinkButton>
                                    </details>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
            </div>
        </template>

        <p class="mt-6 max-w-3xl text-xs text-ink-muted" data-testid="catalogue-footer">
            <span v-if="props.scope === 'japan_only'">The Global client has not received these skills. Their source names are shown.</span>
            Type is derived from the source effect codes. Unspecified includes both untyped and unknown skills.
            Cost is the base SP before hint discounts. Distance matches the recorded distance filter.
            <span data-testid="range-op-sentence">
                <template v-if="rangeOpCount > 0">{{ rangeOpCount }} skills have distance ranges this filter cannot match.</template>
                <template v-else>No skill in this scope has a recorded distance range.</template>
            </span>
        </p>
    </div>
</template>
