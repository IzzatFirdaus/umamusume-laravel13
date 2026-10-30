<script setup lang="ts">
/*
 * One skill, rebuilt to `skill-detail.html` (plan Slice 8).
 *
 * Engine tokens first, prose second. The condition table is the primary content: the export's own predicate
 * and effect vector are the one representation that can be diffed against the source, so a paraphrase would
 * lose exactly the boundary the skill fires on. The description block is an empty state by ruling: no
 * client-vocabulary description for a skill exists in this repository (G-SK-20), so it says so instead of
 * printing the export's English or a paraphrase of the tokens.
 *
 * The hint ladder is the client's measured discount rule priced from this skill's own base. No row marks a
 * "current" level: a hint belongs to a run, no table stores one, and the "In your runs" card keeps the run
 * facts (acquisition status and the recorded unique-skill level) apart from the catalogue.
 *
 * The class code is the export's own integer, printed without a rarity word (ADR-0011 §3 as amended). The
 * type word and the ✦ Unique mark are this tool's readings, and the note beside them says whose they are.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { LinkButton } from '../../components/Form';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Effect {
    type: number;
    value: number;
}

interface ConditionGroup {
    base_time: number | null;
    condition: string | null;
    precondition: string | null;
    effects: Effect[];
}

interface Skill {
    id: number;
    name: string;
    name_ja: string | null;
    type: string | null;
    sp_cost: number | null;
    /** The export's class code (1-6), printed as the integer only. */
    rarity: number | null;
    is_unique: boolean;
    condition_groups: ConditionGroup[] | null;
    source_url: string | null;
    fetched_at_display: string | null;
}

interface Holder {
    name: string;
    slug: string;
    url: string;
}

interface HintRow {
    key: string;
    label: string;
    discount: number;
    price: number | null;
}

interface RunRow {
    id: number;
    label: string;
    status: string;
    level: number | null;
    url: string;
}

const props = defineProps<{
    skill: Skill;
    uniqueHolders: Holder[];
    innateHolders: Holder[];
    awakeningHolders: Holder[];
    eventHolders: Holder[];
    /** How many skills the Global catalogue holds, for the provenance card's scope line. */
    catalogueCount: number;
    hintLadder: { measuredAt: string; rows: HintRow[] };
    related: {
        sameCondition: number | null;
        sameClassCode: number | null;
        sameClassCodeAndType: number | null;
        typeFilter: { url: string; label: string };
    };
    runs: { rows: RunRow[]; total: number };
}>();

const page = usePage();

/**
 * The list this detail was opened from. `from` is allowlisted to the two catalogue URLs, so a crafted
 * link cannot turn the back button into an open redirect; anything else falls back to the Library list.
 * `page.url` is Inertia's current address and is reactive, unlike `window.location.search`.
 */
const allowedFrom = computed<string | null>(() => {
    const from = new URL(page.url, 'http://localhost').searchParams.get('from');

    return from === '/skills' || from === '/database/skills' ? from : null;
});

/** The way back keeps the list's query (search, type, unique, page) minus the round-trip marker. */
const backHref = computed<string>(() => {
    const params = new URL(page.url, 'http://localhost').searchParams;
    params.delete('from');

    const target = allowedFrom.value ?? '/skills';
    const query = params.toString();

    return query === '' ? target : `${target}?${query}`;
});

const holderGroups = computed(() => [
    { heading: 'As her unique skill', holders: props.uniqueHolders },
    { heading: 'Among her innate skills', holders: props.innateHolders },
    { heading: 'Among her awakening skills', holders: props.awakeningHolders },
    { heading: 'Among her event skills', holders: props.eventHolders },
]);

const hasAnyHolder = computed(() => holderGroups.value.some((group) => group.holders.length > 0));

const conditions = computed<ConditionGroup[]>(() => props.skill.condition_groups ?? []);

/** Whether the stored predicate names a running style, read from the conditions rather than assumed. */
const styleClause = computed<string>(() => {
    const gated = conditions.value.some((group) =>
        /running_style/.test(`${group.condition ?? ''} ${group.precondition ?? ''}`),
    );

    return gated ? 'Its condition names a running style.' : 'No running-style precondition of its own.';
});

const lede = computed<string>(() => {
    const base = [
        props.skill.type ?? 'No type word',
        props.skill.sp_cost === null ? 'no stated SP cost' : `${props.skill.sp_cost} Skill Points at base`,
    ].join(' · ');

    return `${base}. ${styleClause.value}`;
});

const countLabel = (count: number): string => `${count} ${count === 1 ? 'skill' : 'skills'}`;

const statusClass = (status: string): string => {
    if (status === 'Acquired') {
        return 'pill pill-positive';
    }

    if (status === 'Skipped') {
        return 'pill pill-neutral';
    }

    return 'pill pill-outline';
};

/** The export's own effect vector, verbatim: no added sign, because the card promises "shown as-is". */
const effectLine = (effect: Effect): string => `type ${effect.type}: ${effect.value}`;
</script>

<template>
    <AppLayout
        :breadcrumb="[
            { label: 'Skills', href: backHref },
            { label: props.skill.name },
        ]"
    >
        <Head :title="props.skill.name" />
        <template #head>
            <PageHeader
                eyebrow="Skill"
                :title="props.skill.name"
                :title-ja="props.skill.name_ja !== null && props.skill.name_ja !== props.skill.name
                    ? props.skill.name_ja
                    : undefined"
                :description="lede"
            >
                <template #actions>
                    <LinkButton :href="backHref" variant="secondary">Back to search</LinkButton>
                </template>
            </PageHeader>
        </template>

        <DatabaseNav />

        <div class="grid gap-4 g-side">
            <div>
                <section aria-labelledby="mechanics" class="card">
                    <h2 id="mechanics">The source's own engine data</h2>
                    <p class="sub">Shown as-is. These are the export's tokens, not client labels.</p>

                    <div
                        v-for="(group, index) in conditions"
                        :key="index"
                        class="min-w-0"
                        :class="index > 0 ? 'mt-4' : ''"
                    >
                        <h3 v-if="conditions.length > 1" class="text-sm font-semibold text-ink-strong">
                            Activation {{ index + 1 }}
                        </h3>
                        <table class="t table-fixed">
                            <caption class="sr-only">
                                Activation {{ index + 1 }}: condition, precondition, effects and base time
                            </caption>
                            <tbody>
                                <tr>
                                    <th scope="row" class="w-1/3 font-normal text-ink-muted">Condition</th>
                                    <td class="break-all font-mono">{{ group.condition ?? 'none stated' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 font-normal text-ink-muted">Precondition</th>
                                    <td class="break-all font-mono">{{ group.precondition ?? 'none stated' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="w-1/3 font-normal text-ink-muted">Effects</th>
                                    <td class="break-all font-mono">
                                        {{ group.effects.length > 0 ? group.effects.map(effectLine).join(' · ') : 'none stated' }}
                                    </td>
                                </tr>
                                <tr v-if="group.base_time !== null">
                                    <th scope="row" class="w-1/3 font-normal text-ink-muted">Base time</th>
                                    <td>{{ group.base_time }} ms</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p v-if="conditions.length === 0" class="text-sm text-ink-muted">
                        No activation condition, effect value or timing is recorded for this skill. The base
                        cost and the class code below are.
                    </p>

                    <!-- The catalogue's own fields, rendered in both states: only the condition rows are
                         conditional, so the no-mechanics state never claims values it does not print. -->
                    <table class="t mt-3 table-fixed">
                        <caption class="sr-only">Base cost, class code and unique mark</caption>
                        <tbody>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Base cost</th>
                                <td class="font-mono">
                                    <span v-if="props.skill.sp_cost !== null">{{ props.skill.sp_cost }} SP</span>
                                    <AbsenceValue v-else compact reason="The source states no SP cost for this class of skill." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Class code</th>
                                <td class="font-mono">
                                    <span v-if="props.skill.rarity !== null">{{ props.skill.rarity }}</span>
                                    <AbsenceValue v-else compact reason="The source states no class code for this skill." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Unique</th>
                                <td>
                                    <span
                                        v-if="props.skill.is_unique"
                                        class="pill pill-neutral"
                                        title="Marked from the source's skill class code, not from a card's own skill list."
                                    >✦ Unique</span>
                                    <template v-else>No</template>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            The expressions and effect codes above are the source's own engine data, not
                            client labels. A tracker that cleaned them into prose would lose the one
                            representation that can be diffed against the export. The type word is this
                            tool's reading of those effect codes and the ✦ mark its reading of the class
                            code; the class code itself is the export's integer, and no rarity word is
                            claimed for it.
                        </span>
                    </p>
                </section>

                <section aria-labelledby="hint-ladder" class="card mt-4">
                    <h2 id="hint-ladder">Displayed cost at each hint level</h2>
                    <p class="sub">The client's published ladder, priced from this skill's own base.</p>

                    <table class="t table-fixed">
                        <caption class="sr-only">Hint level, discount and displayed cost</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-1/2">Hint</th>
                                <th scope="col" class="n">Discount</th>
                                <th v-if="props.skill.sp_cost !== null" scope="col" class="n">Displayed cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in props.hintLadder.rows" :key="row.key">
                                <th scope="row" class="font-medium">{{ row.label }}</th>
                                <td class="n font-mono tabular-nums">{{ row.discount }}%</td>
                                <td v-if="props.skill.sp_cost !== null" class="n font-mono tabular-nums">
                                    {{ row.price }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            <template v-if="props.skill.sp_cost !== null">
                                The displayed cost is the base times one minus the discount, floored. The
                                rule is measured on a 130-point skill, where 130 × 0.65 = 84.5 is shown as
                                84; these rows apply the same rule to this skill's own base.
                            </template>
                            <template v-else>
                                This skill's class states no SP cost, so no hint-level price can be computed.
                            </template>
                            No run stores a hint level, so no row is marked as a level a run holds.
                        </span>
                    </p>
                </section>
            </div>

            <div>
                <section aria-labelledby="description" class="card">
                    <h2 id="description">Readable description</h2>
                    <p class="text-sm text-ink">No client string.</p>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            The plain description is a convenience reading of the tokens beside it, never a
                            replacement for them. Where a client string exists it prints verbatim; for a
                            skill none does, because no client-vocabulary source for descriptions exists in
                            this repository. The block is empty by ruling rather than by omission.
                        </span>
                    </p>
                </section>

                <section aria-labelledby="related" class="card">
                    <h2 id="related">Related</h2>
                    <table class="t table-fixed">
                        <caption class="sr-only">
                            Skills related by condition, by class code, and by class code and type
                        </caption>
                        <tbody>
                            <tr>
                                <th scope="row" class="w-2/3 font-normal text-ink-muted">Same condition</th>
                                <td class="n font-mono tabular-nums">
                                    <span v-if="props.related.sameCondition !== null">{{ countLabel(props.related.sameCondition) }}</span>
                                    <AbsenceValue v-else compact reason="This skill stores no condition string to compare." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-2/3 font-normal text-ink-muted">Same class code</th>
                                <td class="n font-mono tabular-nums">
                                    <span v-if="props.related.sameClassCode !== null">{{ countLabel(props.related.sameClassCode) }}</span>
                                    <AbsenceValue v-else compact reason="This skill stores no class code to compare." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-2/3 font-normal text-ink-muted">Same class code and type</th>
                                <td class="n font-mono tabular-nums">
                                    <span v-if="props.related.sameClassCodeAndType !== null">{{ countLabel(props.related.sameClassCodeAndType) }}</span>
                                    <AbsenceValue v-else compact reason="This skill stores no class code and type word to compare." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <LinkButton :href="props.related.typeFilter.url" variant="secondary" size="sm">
                            {{ props.related.typeFilter.label }}
                        </LinkButton>
                    </div>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            The three counts read the catalogue: the first counts skills sharing a
                            condition string, the second skills with the same stored class code, and the
                            third those with the same class code and type, so the third is always part of
                            the second. All are the Global scope and exclude this skill.
                        </span>
                    </p>
                </section>

                <section aria-labelledby="runs" class="card">
                    <h2 id="runs">In your runs</h2>

                    <table v-if="props.runs.rows.length > 0" class="t table-fixed">
                        <caption class="sr-only">
                            Runs that record this skill, with the stored acquisition status and the recorded level
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-1/2">Run</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="n">Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="run in props.runs.rows" :key="run.id">
                                <th scope="row" class="font-normal">
                                    <Link
                                        :href="run.url"
                                        class="inline-flex min-h-11 items-center break-words text-ink-strong underline hover:text-brand-chrome transition-colors duration-150 ease-out"
                                    >{{ run.label }}</Link>
                                </th>
                                <td><span :class="statusClass(run.status)">{{ run.status }}</span></td>
                                <td class="n font-mono tabular-nums">
                                    <span v-if="run.level !== null">{{ run.level }}</span>
                                    <AbsenceValue v-else compact reason="This run records no level for this skill." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-ink-muted">No run records this skill.</p>

                    <p v-if="props.runs.total > props.runs.rows.length" class="mt-2 text-xs text-ink-muted">
                        Showing the {{ props.runs.rows.length }} most recent of {{ props.runs.total }} runs
                        that record this skill.
                    </p>

                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span class="flex-1">
                            A hint belongs to the run that earned it, so it is shown per run and not as a
                            catalogue property. No table stores a hint level; the level column is the
                            unique-skill level the run recorded, where one exists.
                        </span>
                    </p>
                </section>

                <section aria-labelledby="holders" class="card">
                    <h2 id="holders">Held by trainees</h2>
                    <p v-if="!hasAnyHolder" class="text-sm text-ink-muted">No trainees recorded with this skill.</p>
                    <template v-else>
                        <template v-for="group in holderGroups" :key="group.heading">
                            <template v-if="group.holders.length > 0">
                                <h3 class="mt-3 text-sm font-semibold text-ink-strong">{{ group.heading }}</h3>
                                <ul class="mt-1">
                                    <li v-for="holder in group.holders" :key="holder.slug">
                                        <Link
                                            :href="holder.url"
                                            class="inline-flex min-h-11 items-center text-ink-strong underline hover:text-brand-chrome transition-colors duration-150 ease-out"
                                        >{{ holder.name }}</Link>
                                    </li>
                                </ul>
                            </template>
                        </template>
                    </template>
                    <p class="mt-2 text-xs text-ink-muted">
                        Read from the four skill lists on each trainee's costume forms, in reverse. A form
                        hidden as unconfirmed holds nothing here.
                    </p>
                </section>

                <section aria-labelledby="provenance" class="card">
                    <h2 id="provenance">Provenance</h2>
                    <table class="t table-fixed">
                        <caption class="sr-only">Data family, Global entries, last read and the ladder's measurement date</caption>
                        <tbody>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Data</th>
                                <td>
                                    <template v-if="props.skill.source_url">GameTora skill export</template>
                                    <span v-else>No fetched source; this record was seeded or entered by hand.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Entries on Global</th>
                                <td class="font-mono">{{ props.catalogueCount }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Last read</th>
                                <td>{{ props.skill.fetched_at_display ?? 'Not recorded' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-1/3 font-normal text-ink-muted">Hint ladder</th>
                                <td>Client-confirmed {{ props.hintLadder.measuredAt }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <LinkButton href="/database/sources" variant="secondary" size="sm">Source ledger</LinkButton>
                    </div>
                    <p v-if="props.skill.source_url" class="mt-2 text-xs text-ink-muted">
                        <a
                            :href="props.skill.source_url"
                            rel="noreferrer noopener"
                            target="_blank"
                            class="inline-flex min-h-11 items-center break-all text-ink-strong underline"
                        >Open the source</a>
                    </p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
