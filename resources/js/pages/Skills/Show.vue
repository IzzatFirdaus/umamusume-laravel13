<script setup lang="ts">
/*
 * One skill, rebuilt to `skill-detail.html` (plan Slice 8).
 *
 * Engine tokens first, prose second. The condition table is the primary content: the export's own
 * predicate and effect vector are the one representation that can be diffed against the source, so a
 * paraphrase would lose exactly the boundary the skill fires on.
 *
 * The description block is an empty state by ruling, not a gap. No client-vocabulary description for a
 * skill exists in this repository, so the block prints "No client string." instead of the export's
 * English (which is the publisher's wording, not the client's) or a paraphrase of the tokens above.
 *
 * Two of the wireframe's "Related" rows and its "In your runs" region are absences this schema
 * forces, and each says so in place rather than being faked. "Same condition" is a fact the stored
 * `condition_groups` can answer and is counted. "Same class code" and "Same class code and type" are
 * keyed on a class code, and no column in this database carries one. "In your runs" needs a per-run
 * hint level, and no table stores one. The class code row of the engine table is withheld for the
 * same reason: there is nothing to print.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import { LinkButton } from '../../components/Form';
import { Head, Link } from '@inertiajs/vue3';
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

/** The client's published hint-discount ladder (`UMAMUSUME_REFERENCE.md` §1.1.4), a reference only. */
const HINT_LADDER = [
    { level: 'None', discount: 0 },
    { level: 'Lv1', discount: 10 },
    { level: 'Lv2', discount: 20 },
    { level: 'Lv3', discount: 30 },
    { level: 'Lv4', discount: 35 },
    { level: 'Lv Max', discount: 40 },
];

const props = defineProps<{
    skill: Skill;
    uniqueHolders: Holder[];
    innateHolders: Holder[];
    awakeningHolders: Holder[];
    eventHolders: Holder[];
    /** How many skills the Global catalogue holds, for the provenance card's scope line. */
    catalogueCount: number;
    /**
     * How many other Global skills carry this skill's exact stored condition groups, or null when
     * this row stores none (nothing to compare, so it is stated rather than counted as zero).
     */
    relatedSameCondition: number | null;
}>();

const holderGroups = computed(() => [
    { heading: 'As her unique skill', holders: props.uniqueHolders },
    { heading: 'Among her innate skills', holders: props.innateHolders },
    { heading: 'Among her awakening skills', holders: props.awakeningHolders },
    { heading: 'Among her event skills', holders: props.eventHolders },
]);

const hasAnyHolder = computed(() => holderGroups.value.some((group) => group.holders.length > 0));

const conditions = computed<ConditionGroup[]>(() => props.skill.condition_groups ?? []);

const lede = computed<string>(() =>
    [
        props.skill.type ?? 'No type word',
        props.skill.sp_cost === null ? 'no stated SP cost' : `${props.skill.sp_cost} Skill Points at base`,
    ].join(' · '),
);

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const tdLabel = 'w-1/3 px-2 py-2 align-top text-sm text-ink-muted';
const tdValue = 'min-w-0 px-2 py-2 align-top text-sm text-ink';
const tdMono = 'min-w-0 break-words px-2 py-2 align-top font-mono text-xs text-ink';

const effectLine = (effect: Effect): string => `type ${effect.type}: ${effect.value > 0 ? '+' : ''}${effect.value}`;
</script>

<template>
    <AppLayout
        :breadcrumb="[
            { label: 'Skills', href: '/skills' },
            { label: props.skill.name },
        ]"
    >
        <Head :title="props.skill.name" />
        <template #head>
            <PageHeader
                eyebrow="Skill"
                :title="props.skill.name_ja !== null && props.skill.name_ja !== props.skill.name
                    ? `${props.skill.name} ${props.skill.name_ja}`
                    : props.skill.name"
                :description="lede"
            >
                <template #actions>
                    <LinkButton href="/skills" variant="secondary">Back to search</LinkButton>
                </template>
            </PageHeader>
        </template>

        <DatabaseNav />

        <!-- The index's dedupe rule carried over: 18 of the 623 Global rows store the same string in
             both name columns, and printing the pair would repeat the name. The Japanese name now
             rides in the h1, where the wireframe puts it. -->

        <div class="grid gap-4 g-side">
            <section aria-labelledby="mechanics">
                <div class="card">
                    <h2 id="mechanics">The source's own engine data</h2>
                    <p class="sub">Shown as-is. These are the export's tokens, not client labels.</p>

                    <template v-if="conditions.length > 0">
                        <div v-for="(group, index) in conditions" :key="index" class="min-w-0" :class="index > 0 ? 'mt-4' : ''">
                            <h3 v-if="conditions.length > 1" class="text-sm font-semibold text-ink-strong">
                                Activation {{ index + 1 }}
                            </h3>
                            <table class="mt-1 w-full table-fixed border-collapse">
                                <tbody>
                                    <tr>
                                        <th scope="row" :class="tdLabel">Condition</th>
                                        <td :class="tdMono">{{ group.condition ?? 'none stated' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" :class="tdLabel">Precondition</th>
                                        <td :class="tdMono">{{ group.precondition ?? 'none stated' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" :class="tdLabel">Effects</th>
                                        <td :class="tdMono">
                                            <template v-if="group.effects.length > 0">
                                                {{ group.effects.map(effectLine).join(' · ') }}
                                            </template>
                                            <span v-else>none stated</span>
                                        </td>
                                    </tr>
                                    <tr v-if="group.base_time !== null">
                                        <th scope="row" :class="tdLabel">Base time</th>
                                        <td :class="tdValue">{{ group.base_time }} ms</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <table class="mt-3 w-full table-fixed border-collapse">
                            <tbody>
                                <tr>
                                    <th scope="row" :class="tdLabel">Base cost</th>
                                    <td :class="[tdValue, 'font-mono']">
                                        <template v-if="props.skill.sp_cost !== null">{{ props.skill.sp_cost }} SP</template>
                                        <template v-else>
                                            <span title="The source states no SP cost for this class of skill.">N/A</span><span class="sr-only">: the source states no SP cost for this class of skill.</span>
                                        </template>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="tdLabel">Type</th>
                                    <td :class="tdValue">
                                        <template v-if="props.skill.type">{{ props.skill.type }}</template>
                                        <span title="The source's effect codes give this skill no type word.">N/A</span><span class="sr-only">: the source's effect codes give this skill no type word.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="tdLabel">Unique</th>
                                    <td :class="tdValue">
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
                            <span>
                                The expressions and effect codes above are the source's own engine data, not
                                client labels. A tracker that cleaned them into prose would lose the one
                                representation that can be diffed against the export.
                            </span>
                        </p>
                    </template>

                    <p v-else class="text-sm text-ink-muted">
                        Activation conditions, effect values and distance or style qualifiers are not
                        recorded for this skill. The cost and the type are; the description block below
                        states why no readable sentence accompanies them.
                    </p>
                </div>

                <!-- The wireframe's hint-level reference (`skill-detail.html`, keybox 3). ADR-0011 and
                     ADR-0029 store no hint level, so the ladder is printed as the client's published
                     reference; no per-skill discounted price is derived on this page. -->
                <div class="card mt-4" aria-labelledby="hint-ladder">
                    <h2 id="hint-ladder">Displayed cost at each hint level</h2>
                    <p class="sub">
                        The client's published discount ladder. This catalogue stores no hint level, so
                        it prints the ladder itself and derives no price for this skill.
                    </p>
                    <table class="mt-2 w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-rule">
                                <th scope="col" class="py-1.5 pr-3 text-left text-xs font-semibold text-ink-strong">Hint level</th>
                                <th scope="col" class="py-1.5 text-right text-xs font-semibold text-ink-strong">Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in HINT_LADDER" :key="row.level" class="border-b border-rule last:border-b-0">
                                <th scope="row" class="py-1.5 pr-3 text-left font-medium text-ink">{{ row.level }}</th>
                                <td class="py-1.5 text-right font-mono tabular-nums text-ink">{{ row.discount }}%</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-2">
                        A run's planner applies this ladder to its own hint level; the catalogue holds no
                        run, so no discounted figure is asserted here.
                    </p>
                </div>
            </section>

            <div>
                <div class="card">
                    <h2>Readable description</h2>
                    <p class="text-sm text-ink">No client string.</p>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            The plain description is a convenience reading of the tokens beside it, never a
                            replacement for them. Where a client string exists it prints verbatim; for a
                            skill none does, because no client-vocabulary source for descriptions exists in
                            this repository. The block is empty by ruling rather than by omission.
                        </span>
                    </p>
                </div>

                <!-- The wireframe's "Related" card (`skill-detail.html:134-141`) asks for three
                     counts. One is answerable from what this schema stores and is counted; the other
                     two are keyed on a class code and no column carries one, so each states its
                     absence instead of printing a number the app cannot compute. -->
                <div class="card">
                    <h2>Related</h2>
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr>
                                <th scope="row" :class="tdLabel">Same condition</th>
                                <td :class="[tdValue, 'text-right font-mono tabular-nums']">
                                    <template v-if="props.relatedSameCondition === null">Not recorded</template>
                                    <template v-else>{{ props.relatedSameCondition }} {{ props.relatedSameCondition === 1 ? 'skill' : 'skills' }}</template>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" :class="tdLabel">Same class code</th>
                                <td :class="[tdValue, 'text-right']">Not recorded</td>
                            </tr>
                            <tr>
                                <th scope="row" :class="tdLabel">Same class code and type</th>
                                <td :class="[tdValue, 'text-right']">Not recorded</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-2">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            The first count reads the stored condition groups, so it is the export's own
                            comparison. A class code is the export's integer and no column in this
                            database carries one, so those two rows say they are not recorded rather than
                            printing zero.
                        </span>
                    </p>
                </div>

                <div class="card">
                    <h2>Held by trainees</h2>
                    <p v-if="!hasAnyHolder" class="text-sm text-ink-muted">No trainees recorded with this skill.</p>
                    <template v-else>
                        <template v-for="group in holderGroups" :key="group.heading">
                            <template v-if="group.holders.length > 0">
                                <h3 class="mt-3 text-sm font-semibold text-ink-strong">{{ group.heading }}</h3>
                                <ul class="mt-1">
                                    <li v-for="holder in group.holders" :key="holder.slug" class="py-0.5 text-sm">
                                        <Link :href="holder.url" class="text-ink-strong underline">{{ holder.name }}</Link>
                                    </li>
                                </ul>
                            </template>
                        </template>
                    </template>
                    <p class="mt-2 text-xs text-ink-muted">
                        Read from the four skill lists on each trainee's costume forms, in reverse. A form
                        hidden as unconfirmed holds nothing here.
                    </p>
                </div>

                <div class="card">
                    <h2>Provenance</h2>
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr>
                                <th scope="row" :class="tdLabel">Data</th>
                                <td :class="tdValue">
                                    <template v-if="props.skill.source_url">GameTora skill export</template>
                                    <span v-else>No fetched source; this record was seeded or entered by hand.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" :class="tdLabel">Entries on Global</th>
                                <td :class="[tdValue, 'font-mono']">{{ props.catalogueCount }}</td>
                            </tr>
                            <tr>
                                <th scope="row" :class="tdLabel">Last read</th>
                                <td :class="tdValue">{{ props.skill.fetched_at_display ?? 'Not recorded' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <LinkButton href="/database/sources" variant="secondary" size="sm">Source ledger</LinkButton>
                    </div>
                    <p v-if="props.skill.source_url" class="mt-2 text-xs text-ink-muted">
                        <a :href="props.skill.source_url" rel="noreferrer noopener" target="_blank" class="text-ink-strong underline">Open the source</a>
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
