<script setup lang="ts">
import AppLayout from '../layouts/AppLayout.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { LinkButton } from '../components/Form';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useVisitState } from '../composables/useVisitState';
import { useLayout } from '../composables/useLayout';
import ArtworkSlot from '../components/ArtworkSlot.vue';
import AbsenceValue from '../components/AbsenceValue.vue';
import Pill from '../components/ui/Pill.vue';
import Tile from '../components/ui/Tile.vue';
import Note from '../components/ui/Note.vue';
import NavGlyph from '../components/NavGlyph.vue';

/*
 * The landing screen, rebuilt as a structural clone of HFW `index.html`.
 *
 * Region order is the wireframe's reading order and it is load-bearing:
 *
 *   1. page head: eyebrow, h1, lede, two actions
 *   2. "Pick up where you left off" + "Recent veterans"   (row one, 2.1fr / 1fr)
 *   3. "Needs you" + "Quick actions"                      (row two, 2.1fr / 1fr)
 *
 * The older three-tile KPI strip, the Roster card and the separate Data Status card are gone:
 * `index.html` has none of them. The catalogue counts now sit in the "Needs you" column as its
 * catalogue door's count line, which is where the wireframe carries them.
 *
 * Absence is a word plus a reason, never a blank cell and never a bare tooltip: every `N/A` below
 * is paired with a sentence on the same screen (`AbsenceValue`'s disclosure, or the note).
 */

interface ActiveCareer {
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    position_label: string | null;
    turn: number;
    position_turn: number | null;
    /** Where the cockpit says the run stands, which can disagree with `turn`. */
    cockpit_turn: number | null;
    cockpit_label: string | null;
    total_turns: number;
    energy: number | null;
    team_rank: { value: string | null; value_hint: string };
    resource: { label: string; value: number | null; value_hint: string } | null;
    resume_url: string;
    artworkURL: string | null;
}

interface VeteranRow {
    id: number;
    label: string;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    run_id: number;
    run_url: string;
    artworkURL: string | null;
}

interface DataStatus {
    label: string;
    state: string;
    stale: boolean;
    verified_at: string | null;
    source: string;
}

const props = defineProps<{
    activeCareer: ActiveCareer | null;
    activeCareerCount: number;
    finishedRunCount: number;
    globalScenarioCount: number;
    careersUrl: string;
    recentVeterans: { rows: VeteranRow[]; total: number; limit: number; library_url: string };
    needsYou: {
        dataStatus: DataStatus;
        veteranCount: number;
        reviewPending: number;
    };
    quickActions: { label: string; to: string; icon: string }[];
    counts: {
        careersRunning: number;
        trainees: number;
        catalogueTrainees: number;
        forms: number;
        skills: number;
        supportCards: number;
    };
    dataStatus: DataStatus;
}>();

const page = usePage();
const { visiting, visitFailed } = useVisitState();
const ruleset = computed<string | null>(() => page.props.app?.ruleset ?? null);

const { mode: dashboardLayout } = useLayout();
const savedSettings = computed(() => (page.props.preferences?.settings ?? {}) as Record<string, unknown>);

/*
 * The Settings toggles still hide the regions they named. `roster` and `data_status` keep their ids
 * although the sections they used to switch are gone or moved, so a saved row keeps meaning: both
 * now switch the "Needs you" column, which carries the data status line and the catalogue figures.
 */
const hiddenCards = computed<string[]>(() =>
    Array.isArray(savedSettings.value.dashboard_hidden_cards)
        ? (savedSettings.value.dashboard_hidden_cards as string[])
        : [],
);
const isCardHidden = (cardId: string): boolean => hiddenCards.value.includes(cardId);

const DASHBOARD_CARD_IDS: ReadonlyArray<string> = [
    'active_career',
    'quick_actions',
    'recent_veterans',
    'roster',
    'data_status',
];
const allCardsHidden = computed<boolean>(() => DASHBOARD_CARD_IDS.every((id) => hiddenCards.value.includes(id)));

const group = (n: number): string => n.toLocaleString('en-US');

// Both rows are the wireframe's `.g-side`: a wide column and a narrow one, one column below 820px.
// Compact keeps one column at every width, because a narrower measure is the point of that mode.
/*
 * `` because the `.card + .card` primitive stacks a card under its sibling with 14px of
 * margin, which is right in a column and wrong in a grid: it pushed the second card of each row down
 * by a gap the wireframe does not draw (`.card{margin:0}` inline on every HFW card).
 */
const sideGrid = computed<string>(() =>
    dashboardLayout.value === 'compact'
        ? 'grid grid-cols-1 gap-3.5'
        : 'grid grid-cols-1 gap-3.5 @[820px]:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]',
);

const totalTurns = computed<number>(() => props.activeCareer?.total_turns ?? 0);
const turnMarks = computed<number[]>(() => Array.from({ length: totalTurns.value }, (_, index) => index + 1));
const remainingTurns = computed<number>(() => Math.max(0, totalTurns.value - (props.activeCareer?.turn ?? 0)));
// The rail's own column template, computed once so the marks and their labels share it.
const railStyle = computed<Record<string, string>>(() => ({
    gridTemplateColumns: `repeat(${totalTurns.value}, minmax(0, 1fr))`,
}));

const turnLabels = computed<number[]>(() => {
    const total = totalTurns.value;
    if (total === 0) return [];
    const labels = [1];
    for (let mark = 12; mark < total; mark += 12) labels.push(mark);
    labels.push(total);
    return labels;
});

// The lede is composed from live figures; the verification line comes from data status.
const lede = computed<string>(() => {
    const verified = props.dataStatus.verified_at;
    const verifiedLine =
        verified !== null
            ? `Verified ${verified} · ${props.dataStatus.source}`
            : 'Verification date not recorded';
    return (
        `${props.activeCareerCount} active career${props.activeCareerCount === 1 ? '' : 's'}, ` +
        `${props.finishedRunCount} finished run${props.finishedRunCount === 1 ? '' : 's'}, and ` +
        `${props.globalScenarioCount} permanent Global scenarios. ${verifiedLine}.`
    );
});

/*
 * The strip counts what a Trainer has LOGGED; the cockpit stands at the imported position. When the
 * two disagree the screen prints both and names the disagreement (index.html annotation 3), because
 * printing one number while two screens contradict each other silently is the defect, not the fix.
 */
const turnDisagrees = computed<boolean>(
    () =>
        props.activeCareer !== null &&
        props.activeCareer.cockpit_turn !== null &&
        props.activeCareer.cockpit_turn !== props.activeCareer.turn,
);

// The absence sentence under the rail: always the rail's own fact, plus exactly the tiles that print
// `N/A` and the reason the strip has nothing to report for them.
const absenceNote = computed<string>(() => {
    if (props.activeCareer === null) return '';
    const missing: string[] = [];
    if (props.activeCareer.energy === null) missing.push('Energy');
    if (props.activeCareer.team_rank.value === null) missing.push('team rank');
    const rail = "The rail is one mark per career turn: seventy-two marks, the client's twenty-four turns a year over three years.";
    if (missing.length === 0) return rail;
    const list = missing.length === 1 ? missing[0] : `${missing[0]} or ${missing[1]}`;
    return `${rail} No logged turn has reported ${list} yet.`;
});
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Dashboard' }]">
        <Head title="Dashboard" />
        <template #head>
            <PageHeader eyebrow="Overview" title="Trainer Desk" :description="lede">
                <template #actions>
                    <!-- The wireframe's own pair (index.html:61-64): "New career" is an outline and
                         "All careers" is the quiet link, so the page's ONE filled action is "Open
                         cockpit" in the resume card (annotations 1 and 6). A second filled button in
                         the head would make the accent mean "a button" rather than "the button". -->
                    <LinkButton href="/career/setup/scenario" variant="secondary">New career</LinkButton>
                    <Link
                        :href="props.careersUrl"
                        class="inline-flex min-h-11 items-center px-2 text-sm font-semibold text-ink underline underline-offset-2 hover:decoration-2"
                    >All careers</Link>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loadingâ€¦</p>

        <p
            v-if="visitFailed"
            role="alert"
            class="mb-3 rounded-sm border border-risk bg-raised px-3 py-2 text-sm text-ink"
        >
            That page could not be loaded. Try again, or open it from the navigation.
        </p>

        <div v-if="allCardsHidden" role="region" aria-labelledby="dashboard-empty-heading" class="card">
            <h2 id="dashboard-empty-heading">No cards are shown on the Trainer Desk.</h2>
            <p class="text-sm text-ink-muted">
                <span class="font-semibold text-ink">Settings &gt; General &gt; Show on Dashboard</span>
                names the toggles.
            </p>
            <LinkButton href="/preferences" variant="secondary" class="mt-3">Open Settings</LinkButton>
        </div>

        <div v-else class="flex flex-col gap-3.5" data-dashboard-root :data-layout="dashboardLayout">
            <!-- ROW ONE: Pick up where you left off | Recent veterans -->
            <div :class="sideGrid" data-dashboard-row="pickup">
                <section
                    v-if="props.activeCareer && !isCardHidden('active_career')"
                    role="region"
                    aria-labelledby="pickup-heading"
                    class="card"
                    data-card="active_career"
                >
                    <h2 id="pickup-heading">Pick up where you left off</h2>
                    <div class="flex flex-wrap items-start gap-3.5">
                        <ArtworkSlot
                            :url="props.activeCareer.artworkURL"
                            alt=""
                            size="size-16"
                            class="shrink-0"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="text-[19px] font-extrabold leading-tight text-ink-strong">{{ props.activeCareer.trainee }}</span>
                                <Pill tone="neutral">{{ props.activeCareer.scenario_label }}</Pill>
                            </p>
                            <p v-if="props.activeCareer.trainee_ja" class="text-xs text-ink-muted">{{ props.activeCareer.trainee_ja }}</p>
                            <p class="mt-1 text-[12.5px] text-ink">
                                <template v-if="props.activeCareer.position_label">{{ props.activeCareer.position_label }}</template>
                                <span v-else title="No turn has been logged, so this career is not standing anywhere on the calendar yet.">
                                    N/A<span class="sr-only">: no turn has been logged yet.</span>
                                </span>
                            </p>
                            <div class="mt-2.5">
                                <LinkButton :href="props.activeCareer.resume_url" variant="primary">Open cockpit</LinkButton>
                            </div>
                        </div>
                    </div>

                    <!-- The three tiles under the identity row. Tile two is the scenario's own third
                         resource where it has one; where the scenario's resource IS team rank (Unity
                         Cup) the resource is already tile three and tile two falls back to Energy. -->
                    <div class="mt-3.5 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        <Tile label="Turn" :value="group(props.activeCareer.turn)" data-tile="turn" />
                        <Tile
                            v-if="props.activeCareer.resource"
                            :label="props.activeCareer.resource.label"
                            :value="props.activeCareer.resource.value !== null ? group(props.activeCareer.resource.value) : 'N/A'"
                            :absent="props.activeCareer.resource.value === null"
                            :hint="props.activeCareer.resource.value === null ? props.activeCareer.resource.value_hint : undefined"
                            data-tile="resource"
                        />
                        <Tile
                            v-else
                            label="Energy"
                            :value="props.activeCareer.energy !== null ? group(props.activeCareer.energy) : 'N/A'"
                            :absent="props.activeCareer.energy === null"
                            :hint="props.activeCareer.energy === null ? 'No logged turn has recorded Energy for this career.' : undefined"
                            data-tile="energy"
                        />
                        <Tile
                            label="Team rank"
                            :value="props.activeCareer.team_rank.value ?? 'N/A'"
                            :absent="props.activeCareer.team_rank.value === null"
                            :hint="props.activeCareer.team_rank.value === null ? props.activeCareer.team_rank.value_hint : undefined"
                            data-tile="team_rank"
                        />
                    </div>

                    <!-- The 72-mark turn rail. -->
                    <div class="mt-3.5">
                        <div class="flex flex-wrap items-baseline justify-between gap-2 text-xs">
                            <span class="font-extrabold text-ink-strong">Turn {{ props.activeCareer.turn }} of {{ totalTurns }}</span>
                            <span class="text-ink-muted">{{ remainingTurns }} turns to go</span>
                        </div>
                        <ol
                            role="list"
                            aria-label="Career progress"
                            class="mt-2 grid gap-px"
                            :style="railStyle"
                        >
                            <li
                                v-for="mark in turnMarks"
                                :key="mark"
                                :class="mark <= props.activeCareer.turn
                                    ? 'h-2 rounded-[1px] bg-primary-action'
                                    : 'h-2 rounded-[1px] border border-rule'"
                                :aria-current="mark === props.activeCareer.turn ? 'step' : undefined"
                            />
                        </ol>
                        <div
                            aria-hidden="true"
                            class="mt-1 grid text-[10.5px] text-ink-muted"
                            :style="railStyle"
                        >
                            <span
                                v-for="mark in turnLabels"
                                :key="mark"
                                class="whitespace-nowrap"
                                :style="{ gridColumnStart: mark, justifySelf: mark === totalTurns ? 'end' : 'start' }"
                            >{{ mark }}</span>
                        </div>
                    </div>

                    <Note class="mt-3">{{ absenceNote }}</Note>

                    <Note v-if="turnDisagrees" class="mt-2">
                        <b class="font-extrabold text-ink-strong">A turn is counted from turns recorded, not
                        from the position the run was imported at.</b>
                        This run's cockpit reports {{ props.activeCareer.cockpit_label }}; the strip here
                        counts what has been recorded. Both are labelled, and the disagreement is a known
                        defect in the turn count, not a rendering choice made here.
                    </Note>
                </section>

                <!-- The same card, saying there is nothing to resume rather than showing an empty rail. -->
                <section
                    v-else-if="!isCardHidden('active_career')"
                    role="region"
                    aria-labelledby="pickup-empty-heading"
                    class="card"
                    data-card="active_career"
                >
                    <h2 id="pickup-empty-heading">Pick up where you left off</h2>
                    <p class="text-sm font-semibold text-ink-strong">Not recorded: no career is running.</p>
                    <p class="mt-1 text-[12.5px] text-ink-muted">
                        Start a career to track turns, Energy and race record in one place, or import one
                        you have already started in the client.
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <!-- Nothing to resume is the one state where the head's outline is not enough:
                             there is no resume card to carry the accent, so "Start a career" takes it. -->
                        <LinkButton href="/career/setup/scenario" variant="primary">Start a career</LinkButton>
                        <Link
                            href="/career/snapshot"
                            class="inline-flex min-h-11 items-center px-1 text-sm font-semibold text-ink underline underline-offset-2 hover:decoration-2"
                        >Import an existing career</Link>
                    </div>
                </section>

                <section
                    v-show="!isCardHidden('recent_veterans')"
                    role="region"
                    aria-labelledby="recent-veterans-heading"
                    class="card"
                    data-card="recent_veterans"
                >
                    <h2 id="recent-veterans-heading">Recent veterans</h2>

                    <template v-if="props.recentVeterans.total === 0">
                        <p class="text-sm font-semibold text-ink-strong">Not recorded: the library holds no veterans yet.</p>
                        <p class="mt-1 text-[12.5px] text-ink-muted">
                            Finish a career and choose Save Veteran on its Result screen.
                        </p>
                        <LinkButton :href="props.recentVeterans.library_url" variant="secondary" size="sm" class="mt-3">
                            Open the Veteran library
                        </LinkButton>
                    </template>

                    <template v-else>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-[12.5px]">
                                <thead>
                                    <tr>
                                        <th class="border-b border-rule-soft px-2 pb-1.5 text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Trainee</th>
                                        <th class="border-b border-rule-soft px-2 pb-1.5 text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Scenario</th>
                                        <th class="border-b border-rule-soft px-2 pb-1.5 text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Saved</th>
                                        <th class="border-b border-rule-soft px-2 pb-1.5 text-right text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted"><span class="sr-only">Open the veteran</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="veteran in props.recentVeterans.rows" :key="veteran.run_id" class="hover:bg-raised">
                                        <td class="border-b border-rule-soft px-2 py-1.5 align-top">
                                            <span class="flex items-center gap-2">
                                                <!-- A veteran saved without a mirror file reserves its cell
                                                     and paints nothing: absence is a normal state (DESIGN.md §4.7). -->
                                                <ArtworkSlot :url="veteran.artworkURL" alt="" size="size-10" reserve />
                                                <span class="min-w-0">
                                                    <b class="font-extrabold text-ink-strong">{{ veteran.trainee }}</b>
                                                    <span v-if="veteran.trainee_ja" class="ml-1 text-ink-muted">{{ veteran.trainee_ja }}</span>
                                                </span>
                                            </span>
                                        </td>
                                        <td class="border-b border-rule-soft px-2 py-1.5 align-top">{{ veteran.scenario_label }}</td>
                                        <td class="border-b border-rule-soft px-2 py-1.5 align-top font-mono">
                                            <AbsenceValue v-if="!veteran.label" compact reason="No save date was recorded for this veteran." />
                                            <template v-else>{{ veteran.label }}</template>
                                        </td>
                                        <td class="border-b border-rule-soft px-2 py-1.5 text-right align-top">
                                            <LinkButton :href="veteran.run_url" variant="secondary" size="sm">Open</LinkButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2.5">
                            <Link
                                :href="props.recentVeterans.library_url"
                                class="text-[12.5px] font-semibold text-ink underline underline-offset-2 hover:decoration-2"
                            >All veterans</Link>
                        </p>
                    </template>
                </section>
            </div>

            <!-- ROW TWO: Needs you | Quick actions -->
            <div :class="sideGrid" data-dashboard-row="needs-you">
                <section
                    v-show="!isCardHidden('roster') && !isCardHidden('data_status')"
                    role="region"
                    aria-labelledby="needs-you-heading"
                    class="card"
                    data-card="needs_you"
                >
                    <h2 id="needs-you-heading">Needs you</h2>
                    <ul class="flex flex-col gap-2.5">
                        <li
                            class="flex flex-wrap items-baseline gap-x-2.5 gap-y-1 border-l-[3px] pl-3 text-[12.5px]"
                            :class="props.needsYou.dataStatus.stale ? 'border-l-risk' : 'border-l-transparent'"
                            data-needs-you="data_status"
                        >
                            <b class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">{{ props.needsYou.dataStatus.label }}</b>
                            <span class="font-semibold text-ink-strong">{{ props.needsYou.dataStatus.state }}</span>
                            <span v-if="props.needsYou.dataStatus.verified_at" class="text-ink-muted">
                                Verified {{ props.needsYou.dataStatus.verified_at }} · {{ props.needsYou.dataStatus.source }}
                            </span>
                            <AbsenceValue v-else compact class="text-ink-muted" reason="No verification date is recorded for the scenario matrix." />
                            <span class="text-ink-muted">
                                Ruleset version:
                                <span class="font-mono text-ink">{{ ruleset ?? 'N/A' }}</span>
                                <span v-if="!ruleset" class="sr-only">: no source defines a Global ruleset version, so this tool states none.</span>
                            </span>
                            <Link
                                href="/database/sources"
                                class="font-semibold text-ink underline underline-offset-2 hover:decoration-2"
                            >Source ledger</Link>
                        </li>

                        <!-- The catalogue doors and their counts: this is where the wireframe's catalogue
                             figures live now that the KPI strip is gone. -->
                        <li
                            class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 border-l-[3px] border-l-transparent pl-3 text-[12.5px]"
                            data-needs-you="catalogue"
                        >
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-strong">Catalogue ready</span>
                                <span class="text-ink-muted">
                                    {{ group(props.counts.catalogueTrainees) }} trainees ·
                                    {{ group(props.counts.forms) }} forms ·
                                    {{ group(props.counts.skills) }} skills ·
                                    {{ group(props.counts.supportCards) }} support cards
                                </span>
                            </span>
                            <Link href="/catalog" class="font-semibold text-ink underline underline-offset-2 hover:decoration-2">Browse catalog</Link>
                        </li>

                        <li
                            class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 border-l-[3px] border-l-transparent pl-3 text-[12.5px]"
                            data-needs-you="veterans"
                        >
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-strong">Veteran library</span>
                                <span class="text-ink-muted">{{ group(props.needsYou.veteranCount) }} veterans saved</span>
                            </span>
                            <Link
                                :href="props.recentVeterans.library_url"
                                class="font-semibold text-ink underline underline-offset-2 hover:decoration-2"
                            >Open library</Link>
                        </li>

                        <li
                            class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 border-l-[3px] border-l-transparent pl-3 text-[12.5px]"
                            data-needs-you="review"
                        >
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-strong">Match review</span>
                                <span class="text-ink-muted">
                                    {{ group(props.needsYou.reviewPending) }} pending
                                    {{ props.needsYou.reviewPending === 1 ? 'candidate' : 'candidates' }}
                                </span>
                            </span>
                            <Link href="/review" class="font-semibold text-ink underline underline-offset-2 hover:decoration-2">Open queue</Link>
                        </li>
                    </ul>
                </section>

                <section
                    v-show="!isCardHidden('quick_actions')"
                    role="region"
                    aria-labelledby="quick-actions-heading"
                    class="card"
                    data-card="quick_actions"
                >
                    <h2 id="quick-actions-heading">Quick actions</h2>
                    <nav aria-label="Quick actions">
                        <ul class="flex flex-col gap-2">
                            <li v-for="action in props.quickActions" :key="action.to">
                                <LinkButton :href="action.to" variant="secondary" class="w-full justify-start gap-2">
                                    <NavGlyph :name="action.icon" />
                                    {{ action.label }}
                                </LinkButton>
                            </li>
                        </ul>
                    </nav>
                    <p class="mt-2.5 text-xs text-ink-muted">
                        Destinations the navigation does not carry. Partway through a career?
                        <Link href="/career/snapshot" class="font-semibold text-ink underline underline-offset-2 hover:decoration-2">Import an existing career</Link>
                        instead.
                    </p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>