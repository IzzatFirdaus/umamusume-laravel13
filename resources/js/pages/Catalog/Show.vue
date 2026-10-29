<script setup lang="ts">
/*
 * One trainee, rebuilt to `catalog-detail.html` (plan Slice 8).
 *
 * The page is two columns: identity, base values and aptitudes on the left, and the costume forms,
 * the breakthrough rule, her unique skill and her provenance on the right. The regions the app adds
 * beyond the HFW (her goals, her runs, the form's skill lists) follow the same two-column block as
 * full-width cards, so nothing the previous screen answered was dropped to make the new order fit.
 *
 * Two figures the HFW prints are not stored in this catalogue, and the page states that instead of
 * inventing them: the five base stat values (and with them the ★4 and ★5 totals) and the growth row.
 * `character_cards` holds a title, a rarity and a release date; a growth row lives on the run that
 * reads it off the card. A zero is a recorded figure, so no `+0%` prints where nothing was fetched.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import AptitudeGrid from '../../components/AptitudeGrid.vue';
import SkillRow from '../../components/SkillRow.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import SparkChip from '../../components/SparkChip.vue';
import { LinkButton } from '../../components/Form';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Alias {
    alias: string;
    language_label: string;
}

interface Card {
    id: number;
    title: string;
    rarity_label: string;
    rarity_stars: string;
    is_debut_form: boolean;
    unconfirmed: boolean;
    global_release_date: string;
    global_release_date_display: string;
    snapshot_path: string | null;
    source_url: string;
    fetched_at_display: string | null;
}

interface Skill {
    id: number;
    name: string;
    sp_cost: number | null;
    is_unique: boolean;
    url: string | null;
}

interface SkillList {
    label: string;
    absent: string;
    has_ids: boolean;
    skills: Skill[];
}

interface Run {
    id: number;
    status_label: string;
    scenario_label: string;
    turn_count: number;
}

interface Source {
    url: string;
    source_key: string;
    fetched_at_display: string;
}

interface Profile {
    va_ja: string | null;
    va_en: string | null;
    birthday: { iso: string | null; display: string } | null;
    height: number | null;
    three_sizes: { b: number | null; h: number | null; w: number | null } | null;
}

/**
 * One rung of the trainee's own ladder (`ADR-0026`). The keys are the Cockpit's goals rows verbatim,
 * composed by the same model readers, so the two surfaces cannot drift into two vocabularies for one
 * objective. `state`, `race_url` and the group's `summary` are the run-scoped half of that contract and
 * are null on this page because no run is in progress to read them from.
 */
interface GoalRow {
    label: string;
    state: string | null;
    year_label: string;
    turn: number;
    race_url: string | null;
    absence: string | null;
}

interface TraineeGoals {
    rows: GoalRow[];
    note: string;
    summary: string | null;
    empty_note: string | null;
}

interface Trainee {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    release_status_label: string;
    is_japan_only: boolean;
    japanese_name: string | null;
    jp_debut_date: string | null;
    global_debut_date: string | null;
    release_date: { iso: string | null; display: string; is_global: boolean } | null;
    is_manual: boolean;
    aptitudes: Record<string, string> | null;
    profile: Profile | null;
    aliases: Alias[];
    /** The Identity slot's loopback URL, or null when the mirror holds no file for the active form. */
    artworkURL: string | null;
}

const props = defineProps<{
    trainee: Trainee;
    cards: Card[];
    activeCardId: number | null;
    hiddenFormCount: number;
    runs: Run[];
    skillLists: SkillList[];
    traineeGoals: TraineeGoals;
    provenance: Source[];
    showUnconfirmed: boolean;
    /** The carry endpoint: writes this trainee into the setup draft and opens the wizard. */
    carry_url: string;
}>();

const activeCard = computed<Card | null>(
    () => props.cards.find((card) => card.id === props.activeCardId) ?? props.cards[0] ?? null,
);

const uniqueSkills = computed<SkillList>(() => props.skillLists[0]);

const formCountLabel = (count: number): string => (count === 1 ? 'form' : 'forms');

const turnLabel = (count: number): string => (count === 1 ? 'turn' : 'turns');

function formHref(id: number): string {
    const params = new URLSearchParams({ form: String(id) });

    if (props.showUnconfirmed) {
        params.set('show_unconfirmed', '1');
    }

    return `/umamusume/${props.trainee.slug}?${params.toString()}`;
}

// The five stats the client trains, in the client's own order (`UMAMUSUME_REFERENCE.md` §1.3.1). A
// row stays on screen for each even though this catalogue stores no value, because a missing row
// reads as a missing column while "Not recorded" reads as the state it is.
const STATS = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'];

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-2 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-2 text-right align-top font-mono text-sm text-ink';
const rowLabel = 'w-1/3 px-2 py-2 align-top text-sm text-ink-muted';
const rowValue = 'px-2 py-2 align-top text-sm text-ink';
// A heading that sits inside a flex row rather than as the card's first child: `card > h2` cannot
// reach it, so the same token pair is spelled here instead of leaving the heading unstyled.
const cardH2 = 'text-base font-bold text-ink-strong';
</script>

<template>
    <AppLayout
        :breadcrumb="[
            { label: 'Database', href: '/database' },
            { label: 'Trainees', href: '/umamusume' },
            { label: props.trainee.name },
        ]"
    >
        <Head :title="props.trainee.name" />
        <template #head>
            <!-- The wireframe's h1 carries the Japanese name beside the English one; the index's
                 dedupe rule applies, so the pair prints only when the two strings differ. -->
            <PageHeader
                eyebrow="Trainee"
                :title="props.trainee.name_ja !== null && props.trainee.name_ja !== props.trainee.name
                    ? `${props.trainee.name} ${props.trainee.name_ja}`
                    : props.trainee.name"
            >
                <template #actions>
                    <LinkButton href="/umamusume" variant="secondary">Back to catalog</LinkButton>
                </template>
            </PageHeader>
        </template>

        <DatabaseNav />

        <p v-if="props.trainee.is_japan_only" class="mb-4 rounded-md border border-ink-faint bg-raised px-3 py-2 text-sm text-ink">
            Not yet released on Global. The data below is JP-sourced.
        </p>

        <div class="grid gap-4 g-side">
            <div>
                <!-- Base values and growth: the region stays, the values state their absence. -->
                <div class="card">
                    <h2>Base stats and growth</h2>
                    <p class="sub">A card's five base values, and what each stat's growth row adds to it.</p>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th :class="th">Stat</th>
                                <th :class="thNum">Base</th>
                                <th :class="thNum">Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stat in STATS" :key="stat">
                                <td :class="td">{{ stat }}</td>
                                <td :class="tdNum">Not recorded</td>
                                <td :class="tdNum">Not recorded</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            This catalogue stores no base value and no growth row for a form, so the cells
                            above state the absence rather than a zero. The growth row is entered on a run,
                            where a Trainer reads it off the card; the ★4 and ★5 totals are functions of
                            these same five values and cannot print without them.
                        </span>
                    </p>
                </div>

                <!-- Identity and profile. Every row stays on screen when its value is absent, because a
                     dropped row reads as a missing field while "Not recorded" states the source's own
                     silence (`catalog-detail.html`, the profile annotation). -->
                <section aria-labelledby="profile">
                    <div class="card mt-4">
                        <div class="flex items-center gap-3">
                            <ArtworkSlot :url="props.trainee.artworkURL" alt="" size="size-16" />
                            <h2 id="profile" :class="cardH2">Profile</h2>
                        </div>
                        <table class="mt-3 w-full border-collapse">
                            <tbody>
                                <tr>
                                    <th scope="row" :class="rowLabel">Japanese name</th>
                                    <td :class="rowValue">
                                        <span v-if="props.trainee.japanese_name" lang="ja">{{ props.trainee.japanese_name }}</span>
                                        <span v-else>Not recorded</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Voice actor</th>
                                    <td :class="rowValue">
                                        <template v-if="props.trainee.profile?.va_ja">
                                            <span lang="ja">{{ props.trainee.profile.va_ja }}</span>
                                            <span v-if="props.trainee.profile.va_en" class="text-ink-muted"> ({{ props.trainee.profile.va_en }})</span>
                                            <span v-else class="block text-xs text-ink-muted">No English voice actor is listed for her.</span>
                                        </template>
                                        <span v-else>Not recorded</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Birthday</th>
                                    <td :class="rowValue">
                                        <template v-if="props.trainee.profile?.birthday">
                                            <time v-if="props.trainee.profile.birthday.iso" :datetime="props.trainee.profile.birthday.iso">
                                                {{ props.trainee.profile.birthday.display }}
                                            </time>
                                            <span v-else>{{ props.trainee.profile.birthday.display }}</span>
                                        </template>
                                        <span v-else>Not recorded</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Height</th>
                                    <td :class="rowValue">
                                        <template v-if="typeof props.trainee.profile?.height === 'number'">
                                            {{ props.trainee.profile.height }} cm
                                        </template>
                                        <span v-else>Not recorded</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Three sizes</th>
                                    <td :class="rowValue">
                                        <template v-if="props.trainee.profile?.three_sizes">
                                            {{ props.trainee.profile.three_sizes.b }} · {{ props.trainee.profile.three_sizes.h }} ·
                                            {{ props.trainee.profile.three_sizes.w }} cm
                                            <span class="block text-xs text-ink-muted">bust · height · waist</span>
                                        </template>
                                        <span v-else>Not recorded</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Japanese debut</th>
                                    <td :class="rowValue">{{ props.trainee.jp_debut_date ?? 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Global debut</th>
                                    <td :class="rowValue">{{ props.trainee.global_debut_date ?? 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Release status</th>
                                    <td :class="rowValue">{{ props.trainee.release_status_label }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" :class="rowLabel">Corrected by hand</th>
                                    <td :class="rowValue">
                                        {{ props.trainee.is_manual ? 'Yes, and the fetch engine leaves this row alone' : 'No' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="!props.trainee.profile" class="note mt-3">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                No profile document has been fetched for her yet, so the rows above state the
                                absence. The card, its aptitudes and its provenance are read from the card
                                document and are unaffected.
                            </span>
                        </p>
                    </div>
                </section>

                <section aria-labelledby="aptitudes">
                    <div class="card mt-4">
                        <h2 id="aptitudes">Aptitudes, full set</h2>
                        <p class="sub">Three families: the ground she handles, the distances, and the styles.</p>
                        <AptitudeGrid v-if="props.trainee.aptitudes" :aptitudes="props.trainee.aptitudes" />
                        <p v-else class="text-sm text-ink-muted">Aptitude not published for this trainee.</p>
                        <p class="note mt-3">
                            <span class="g" aria-hidden="true">○</span>
                            <span>The letter is the fact. The grouping is the client's own, not a layout convenience.</span>
                        </p>
                    </div>
                </section>
            </div>

            <div>
                <div class="card">
                    <h2>Costumes</h2>
                    <!-- One form is not a choice, so a single-form trainee gets no strip: no chrome,
                         not less page. A strip of one link would be an affordance that leads nowhere. -->
                    <nav
                        v-if="props.cards.length > 1"
                        aria-label="Costume forms"
                        class="mb-2 flex flex-wrap gap-1.5"
                    >
                        <Link
                            v-for="card in props.cards"
                            :key="card.id"
                            :href="formHref(card.id)"
                            preserve-scroll
                            :aria-current="card.id === props.activeCardId ? 'page' : undefined"
                            class="inline-flex min-h-11 items-center rounded-full border px-3 text-sm"
                            :class="
                                card.id === props.activeCardId
                                    ? 'border-pick-line bg-sunken font-semibold text-ink-strong'
                                    : 'border-rule bg-panel font-normal text-ink hover:border-green-line'
                            "
                        >
                            {{ card.title }}
                        </Link>
                    </nav>

                    <p v-if="props.cards.length === 0" class="text-sm text-ink-muted">
                        <template v-if="props.hiddenFormCount > 0">
                            Every costume form recorded for this trainee is hidden as unconfirmed.
                        </template>
                        <template v-else>
                            No Global costume card is recorded for this trainee yet.
                        </template>
                    </p>
                    <template v-else>
                        <table class="w-full border-collapse">
                            <thead>
                                <tr>
                                    <th :class="th">Title</th>
                                    <th :class="th">Rarity</th>
                                    <th :class="thNum">Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="card in props.cards"
                                    :key="card.id"
                                    :class="card.id === props.activeCardId ? 'bg-status-positive/10' : ''"
                                >
                                    <td :class="td">
                                        <b class="text-ink-strong">{{ card.title }}</b>
                                        <span class="block text-xs text-ink-muted">
                                            <template v-if="card.is_debut_form">Debut form · </template>
                                            Released (Global) {{ card.global_release_date_display }}
                                        </span>
                                        <span v-if="card.unconfirmed" class="block text-xs font-semibold text-risk">
                                            Not confirmed by two sources
                                        </span>
                                    </td>
                                    <td :class="td">{{ card.rarity_stars }} {{ card.rarity_label }}</td>
                                    <td :class="tdNum">Not recorded</td>
                                </tr>
                            </tbody>
                        </table>

                        <template v-if="activeCard">
                            <h3 class="mt-3 text-base font-semibold text-ink-strong">{{ activeCard.title }}</h3>
                            <p class="mt-1 text-xs text-ink-muted">
                                <template v-if="activeCard.is_debut_form">Debut form. </template>
                                Released on Global {{ activeCard.global_release_date_display }}.
                                Its rarity is this form's own, not the trainee's best.
                            </p>
                        </template>

                        <p class="note mt-3">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                The growth row varies by costume, so a character-wide value cannot exist.
                                This catalogue stores neither the row nor the per-form split, so each cell
                                above states the absence.
                            </span>
                        </p>
                    </template>

                    <p v-if="props.hiddenFormCount > 0" class="mt-2 text-xs text-ink-muted">
                        {{ props.hiddenFormCount }} {{ formCountLabel(props.hiddenFormCount) }} hidden as unconfirmed ·
                        <Link
                            :href="`/umamusume/${props.trainee.slug}?show_unconfirmed=1`"
                            class="text-ink-strong underline"
                        >Show unconfirmed forms</Link>
                    </p>
                </div>

                <div class="card">
                    <h2>Star breakthrough</h2>
                    <p class="text-sm text-ink">
                        Every rank past a card's native rarity adds exactly 50 points across the five base
                        values, so a ★3 card's row totals 500 at ★4 and 550 at ★5. The per-stat
                        distribution is what changes, and it is profile-driven.
                    </p>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            The rule is the export's, and it holds for every card. The five values it
                            distributes are not stored in this catalogue, so the split cannot print here.
                        </span>
                    </p>
                </div>

                <div class="card">
                    <h2>Unique skill</h2>
                    <p class="sub">A trainee-specific skill, independent of any support deck.</p>
                    <template v-if="uniqueSkills.skills.length > 0">
                        <ul class="space-y-1">
                            <li v-for="skill in uniqueSkills.skills" :key="skill.id" class="text-sm">
                                <Link v-if="skill.url" :href="skill.url" class="text-ink-strong underline">{{ skill.name }}</Link>
                                <span v-else class="text-ink">{{ skill.name }}</span>
                                <span v-if="skill.sp_cost !== null" class="ml-2 font-mono text-xs text-ink-muted">{{ skill.sp_cost }} SP</span>
                            </li>
                        </ul>
                        <p class="mt-2 text-xs text-ink-muted">
                            A name with no link is a skill the Global client has not received, so the
                            library cannot open it.
                        </p>
                    </template>
                    <p v-else class="text-sm text-ink-muted">{{ uniqueSkills.absent }}</p>
                </div>

                <div class="card">
                    <h2>Provenance</h2>
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr>
                                <th scope="row" :class="rowLabel">Data</th>
                                <td :class="rowValue">
                                    <code v-if="props.provenance.length > 0">{{ props.provenance[0].source_key }}</code>
                                    <span v-else>No fetched source; this record was seeded or entered by hand.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" :class="rowLabel">Last read</th>
                                <td :class="rowValue">{{ props.provenance[0]?.fetched_at_display ?? 'Not recorded' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" :class="rowLabel">Corrected by hand</th>
                                <td :class="rowValue">{{ props.trainee.is_manual ? 'Yes' : 'No' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <LinkButton href="/database/sources" variant="secondary" size="sm">Source ledger</LinkButton>
                    </div>

                    <!-- ONE disclosure for the trainee's own fetch history. The heading stays on screen,
                         and the summary states how many sources there are before it is opened. -->
                    <details id="provenance" class="mt-3">
                        <summary class="min-h-11 cursor-pointer list-item text-sm font-semibold text-ink-strong">
                            {{ props.provenance.length }} fetched source{{ props.provenance.length === 1 ? '' : 's' }}
                        </summary>
                        <p v-if="props.provenance.length === 0" class="mt-2 text-sm text-ink-muted">
                            No fetched sources. This record was seeded or entered by hand.
                        </p>
                        <ul v-else class="mt-2 space-y-1 text-sm">
                            <li v-for="source in props.provenance" :key="source.url" class="text-ink">
                                <a :href="source.url" class="break-all text-ink-strong underline">{{ source.url }}</a>
                                <span class="block text-xs text-ink-muted">{{ source.source_key }} · fetched {{ source.fetched_at_display }}</span>
                            </li>
                        </ul>
                    </details>
                </div>
            </div>
        </div>

        <section aria-labelledby="skills">
            <div class="card mt-4">
                <h2 id="skills">Skills on this form</h2>
                <p v-if="props.skillLists.every((list) => !list.has_ids)" class="text-sm text-ink-muted">
                    No skill lists are recorded for this form.
                </p>
                <template v-else>
                    <template v-for="list in props.skillLists" :key="list.label">
                        <h3 class="mt-4 flex items-baseline gap-2 text-base font-semibold text-ink-strong">
                            {{ list.label }}
                            <SparkChip :label="list.label" variant="skill" size="sm" />
                        </h3>
                        <ul class="mt-1">
                            <SkillRow v-for="skill in list.skills" :key="skill.id" :skill="skill" />
                            <li v-if="list.skills.length === 0 && !list.has_ids" class="py-1.5 text-sm text-ink-muted">
                                {{ list.absent }}
                            </li>
                            <li v-if="list.skills.length === 0 && list.has_ids" class="py-1.5 text-sm text-ink-muted">
                                Recorded on this form but not in the skill catalog yet.
                            </li>
                        </ul>
                    </template>
                </template>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        Each list is this form's own, in the order the source publishes it. A skill the
                        Global client has not received keeps its name and gets no link.
                    </span>
                </p>
            </div>
        </section>

        <section aria-labelledby="trainee-goals">
            <div class="card mt-4">
                <h2 id="trainee-goals">Trainee goals</h2>
                <p v-if="props.traineeGoals.rows.length === 0" class="text-sm text-ink-muted">
                    {{ props.traineeGoals.empty_note }}
                </p>
                <ul v-else class="divide-y divide-rule">
                    <li
                        v-for="row in props.traineeGoals.rows"
                        :key="row.label"
                        class="flex flex-wrap items-baseline gap-x-3 py-2 text-sm"
                    >
                        <span class="font-medium text-ink">{{ row.label }}</span>
                        <span class="text-xs text-ink-muted">{{ row.year_label }} year, turn {{ row.turn }}</span>
                        <!-- The reason is rendered, not parked in a `title`: an unmarked gap reads as a
                             value the Trainer could have entered. -->
                        <AbsenceValue v-if="row.absence !== null" :reason="row.absence" compact />
                    </li>
                </ul>
                <p class="mt-2 text-xs text-ink-muted">{{ props.traineeGoals.note }}</p>
            </div>
        </section>

        <section aria-labelledby="her-runs">
            <div class="card mt-4">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
                    <h2 id="her-runs" :class="cardH2">Her runs</h2>
                    <!-- A write, so a button that posts the carry route rather than a link: the
                         wizard's own hand-off, no run before Preflight. A double click rewrites the
                         same two draft keys, so the write is idempotent and needs no pending guard. -->
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 text-sm font-bold text-on-primary-action"
                        @click="router.post(props.carry_url)"
                    >
                        Start career with {{ props.trainee.name }}
                    </button>
                </div>
                <p class="mt-1 text-xs text-ink-muted">Starts the setup wizard with her already chosen.</p>

                <p v-if="props.runs.length === 0" class="mt-2 text-sm text-ink-muted">No runs recorded for her yet.</p>
                <ul v-else class="mt-2 divide-y divide-rule">
                    <li
                        v-for="run in props.runs"
                        :key="run.id"
                        class="flex flex-wrap items-baseline gap-x-3 py-2 text-sm"
                    >
                        <span class="font-semibold text-ink-strong">{{ run.status_label }}</span>
                        <span class="text-ink">{{ run.scenario_label }}</span>
                        <span class="ml-auto font-mono text-xs tabular-nums text-ink-muted">
                            {{ run.turn_count }} {{ turnLabel(run.turn_count) }}
                        </span>
                    </li>
                </ul>
            </div>
        </section>

        <section aria-labelledby="aliases">
            <div class="card mt-4">
                <h2 id="aliases">Aliases</h2>
                <p v-if="props.trainee.aliases.length === 0" class="text-sm text-ink-muted">No aliases yet.</p>
                <ul v-else class="flex flex-wrap gap-2 text-sm">
                    <li
                        v-for="(alias, index) in props.trainee.aliases"
                        :key="index"
                        class="rounded-md bg-sunken px-2 py-1 text-ink"
                    >
                        {{ alias.alias }} <span class="text-ink-muted">({{ alias.language_label }})</span>
                    </li>
                </ul>
            </div>
        </section>
    </AppLayout>
</template>
