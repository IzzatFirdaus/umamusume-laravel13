<script setup lang="ts">
/*
 * SCREEN-001, the 2.0 landing screen. The page pattern is `Catalog/Index.vue`'s: `AppLayout`,
 * `<Head title>`, the `#title` slot, and Inertia `Link` for the routes `spa.ts` resolves. There is
 * no write here, so there is no `useForm`.
 *
 * Three panels the brief's mockup names are deliberately not data panels, and the page says so in
 * words rather than leaving them blank:
 *
 *  - **Recent builds** has no source. A build is the Career Plan a run holds (`build_target`, C1) and
 *    the wizard that enters one is D2–D7, so listing runs under the word "Builds" would label a run
 *    as something it is not.
 *  - **Legacy-goal gaps** is omitted outright. A gap is the run's targets set against inherited
 *    stats, which is the inheritance computation `ADR-0020` §3 bans; C1 and C3 give no honest
 *    comparison, and the omission is reported on the slice rather than rendered as a guess.
 *  - **A scenario resource value** renders `N/A` with its reason, because no column records one. The
 *    run page's resource strip renders it the same way.
 *
 * Loading and error are page-level because this page's only user-initiated async action is following
 * one of its links (ADR-0007): Inertia keeps the current page on screen during a visit, so the state
 * is announced rather than drawn as a skeleton. `invalid` and `exception` are both cancelable, and
 * returning `false` takes the failure out of Inertia's default modal so this page's own `role="alert"`
 * is the single surface. Both listeners are removed on unmount, so the suppression is scoped to
 * visits started from here.
 *
 * The Dashboard renders as a bento grid (PLAN 6B.13): four regions pack above the fold at 1920x1080
 * with no scroll. The grid collapses to two columns at 768 and to one at 390. The layout preference
 * (PLAN 6B.15) reads `dashboard_layout` and `dashboard_hidden_cards` from the shared preferences
 * prop and overrides the grid class and card visibility on mount.
 */
import AppLayout from '../layouts/AppLayout.vue';
import { LinkButton } from '../components/Form';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useVisitState } from '../composables/useVisitState';
import { useLayout } from '../composables/useLayout';
import SubCell from '../components/SubCell.vue';
import KpiCard from '../components/KpiCard.vue';
import ArtworkSlot from '../components/ArtworkSlot.vue';

/*
 * The props contract, declared here rather than imported from `resources/js/types.ts`. It cannot be
 * imported: `defineProps<Imported>()` needs `@vue/compiler-sfc` to read the type's source file, which
 * it does through `ts.sys`, and TypeScript 7 ships no `lib/typescript.js` to load (the same TS 7 gap
 * plan §11 records for `vue-tsc`). The build fails outright with "No fs option provided to
 * compileScript in non-Node environment" if it is tried. Every other page declares its props locally
 * for the same reason, so this is the page pattern and not a second one.
 *
 * Every nullable field is a named absence the page renders as `N/A` with a `title`, never a default
 * (AGENTS.md §5): `position_label` is null for a run with no logged turn, `energy` is null when no
 * turn recorded it, and `resource.value` is null because no column holds a scenario resource yet.
 */
interface ActiveCareer {
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    /** The matrix label from `config/scenarios.php`, or "No scenario set". Never a storage key. */
    scenario_label: string;
    /** e.g. "Senior Year · Early July", on the client's 24-turn grid. */
    position_label: string | null;
    /** Logged turns, the same reading the run page's resource strip takes. */
    turn: number;
    energy: number | null;
    resource: { label: string; value: number | null; value_hint: string } | null;
    team_rank: { value: string | null; value_hint: string };
    resume_url: string;
    /** Loopback URL for the trainee's portrait, keyed on `character_card_id`. Null when unmirrored. */
    artworkURL: string | null;
}

interface VeteranRow {
    id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    run_id: number;
    run_url: string;
    /** Loopback URL for the trainee's portrait, keyed on `character_card_id`. Null when unmirrored. */
    artworkURL: string | null;
}

const props = defineProps<{
    activeCareer: ActiveCareer | null;
    /** Live Active runs. More than one is reachable; the card above features only the newest. */
    activeCareerCount: number;
    careersUrl: string;
    recentVeterans: { rows: VeteranRow[]; total: number; limit: number; library_url: string };
    quickActions: { label: string; to: string }[];
    counts: { trainees: number; skills: number; supportCards: number };
    dataStatus: { label: string; state: string; verified_at: string | null; source: string };
}>();

// The ruleset is a shared prop, not a second dashboard prop: `HandleInertiaRequests` already ships it
// to every page, and it is null, so the page prints `N/A` with the reason rather than a copy of the
// absence (design-2.0 §48).
const page = usePage();
const ruleset = computed(() => page.props.app?.ruleset ?? null);

// Layout width (PLAN 6B.15): the grid shape follows the shell's width choice, read through the same
// composable `AppLayout` uses so the legacy stored values resolve in exactly one place.
const { mode: dashboardLayout } = useLayout();
const savedSettings = computed(() => (page.props.preferences?.settings ?? {}) as Record<string, unknown>);
const hiddenCards = computed<string[]>(() =>
    Array.isArray(savedSettings.value.dashboard_hidden_cards)
        ? (savedSettings.value.dashboard_hidden_cards as string[])
        : [],
);

function isCardHidden(cardId: string): boolean {
    return hiddenCards.value.includes(cardId);
}

// The four regions this screen composes. `next_best_action` is gone: its sentence and its button
// now live inside Active Career, so one career has one door (Hick + Von Restorff, one filled
// primary per page). All hidden is a state of its own (DL.5): the grid renders one empty-state card
// rather than a blank page.
const DASHBOARD_CARD_IDS: ReadonlyArray<string> = [
    'active_career',
    'quick_actions',
    'recent_veterans',
    'data_status',
];
const allCardsHidden = computed<boolean>(() => DASHBOARD_CARD_IDS.every((id) => hiddenCards.value.includes(id)));

// Grid container class varies by layout preference (PLAN 6B.15).
const gridClass = computed(() => {
    if (dashboardLayout.value === 'compact') {
        return 'grid w-full max-w-3xl grid-cols-1 gap-4 mx-auto';
    }
    // Standard and wide share this grid; the only difference is the shell measure AppLayout puts
    // around it. A page-level max-w here would re-cap the wide shell back to its own value.
    // `items-start` keeps every region at its own content height. Without it a row stretches each
    // card to the tallest sibling, so the shorter cards become borders around empty space (Next best
    // action carried 107px of void that way); with it the leftover stays page margin, which reads as
    // a finished bento rather than as a panel that failed to fill.
    // The grid self-caps at the standard measure even inside a wide shell: four cards do not use
    // 1536px of horizontal room, and two columns spread that wide leave a taller band of empty page
    // under themselves. The shell still widens for tables and catalogues; this card set does not.
    return 'grid w-full max-w-6xl mx-auto grid-cols-1 gap-6 items-start md:grid-cols-2 xl:grid-cols-12';
});

// Card col-span per layout. Focused forces single-column at all breakpoints; bento uses the
// responsive grid described in PLAN 6B.13.
function cardSpan(cardId: string): string {
    if (dashboardLayout.value === 'compact') {
        return '';
    }
    // Two columns in the brief's 2fr + 1fr ratio, expressed on the existing 12-track grid so no new
    // token is needed. The DOM stays in reading order (Active Career, Quick Actions, Recent
    // Veterans, Data Status) and the explicit starts place them, so the tab order matches the
    // visual order at every width and no row is left with an untiled cell.
    const spans: Record<string, string> = {
        active_career: 'col-span-1 xl:col-span-8 xl:col-start-1 xl:row-start-1',
        quick_actions: 'col-span-1 xl:col-span-4 xl:col-start-9 xl:row-start-1',
        recent_veterans: 'col-span-1 xl:col-span-8 xl:col-start-1 xl:row-start-2',
        data_status: 'col-span-1 md:col-span-2 xl:col-span-4 xl:col-start-9 xl:row-start-2',
    };
    return spans[cardId] ?? 'col-span-1';
}

const { visiting, visitFailed } = useVisitState();

const group = (n: number): string => n.toLocaleString('en-US');

const cardBase = 'rounded-md border border-rule bg-panel p-4';
const emptyCardBase = 'rounded-md border border-dashed border-rule bg-raised p-4';
const subCellStrong = 'font-mono text-xl tabular-nums text-ink-strong';
const subCellDefault = 'font-mono text-xl tabular-nums';
const kpiValueClass = 'font-mono text-kpi tabular-nums text-ink-strong';
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <template #eyebrow>Overview</template>
        <template #title>Dashboard</template>

        <p v-if="visiting" role="status" class="text-sm text-ink-muted">Loading…</p>

        <p
            v-if="visitFailed"
            role="alert"
            class="rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
        >
            That page could not be loaded. Try again, or open it from the navigation.
        </p>

        <!-- BENTO GRID: four regions packed above the fold at 1920x1080 (PLAN 6B.13).
             Each card carries role="region" with a heading so `getByRole('region')` resolves. -->
        <div :class="gridClass" data-dashboard-root :data-layout="dashboardLayout">
            <!-- ALL CARDS HIDDEN (DL.5): one empty-state card rather than a blank page. It sits
                 inside the same shell measure as the grid it replaces. -->
            <section
                v-if="allCardsHidden"
                role="region"
                aria-labelledby="dashboard-empty-heading"
                :class="[emptyCardBase, dashboardLayout === 'compact' ? '' : 'col-span-1 md:col-span-2 xl:col-span-12']"
            >
                <h2 id="dashboard-empty-heading" class="text-lg font-semibold text-ink-strong">
                    No cards are shown on the Dashboard.
                </h2>
                <p class="mt-2 text-sm text-ink-muted">
                    <span class="font-medium text-ink">Settings &gt; General &gt; Show on Dashboard</span>
                    names the toggles.
                </p>
                <LinkButton href="/preferences" variant="secondary" class="mt-4">
                    Open Settings
                </LinkButton>
            </section>

            <!-- ACTIVE CAREER (col-span-8 on xl) or EMPTY STATE -->
            <section
                v-if="props.activeCareer && !isCardHidden('active_career')"
                role="region"
                aria-labelledby="active-career-heading"
                data-card="active_career" :class="[cardBase, cardSpan('active_career')]"
            >
                <h2 id="active-career-heading" class="text-lg font-semibold text-ink-strong">
                    Active Career
                </h2>

                <div class="mt-2 flex items-start gap-3">
                    <ArtworkSlot
                        v-if="props.activeCareer.artworkURL"
                        :url="props.activeCareer.artworkURL"
                        :alt="props.activeCareer.trainee"
                        size="size-16"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="text-lg font-semibold text-ink-strong truncate">
                            {{ props.activeCareer.trainee }}
                            <span
                                v-if="props.activeCareer.trainee_ja"
                                lang="ja"
                                class="ml-2 text-sm font-normal text-ink-muted"
                            >{{ props.activeCareer.trainee_ja }}</span>
                        </p>

                        <p class="mt-1 text-sm text-ink-muted">
                            <span>{{ props.activeCareer.scenario_label }}</span>
                            <span aria-hidden="true"> · </span>
                            <span
                                v-if="props.activeCareer.position_label"
                            >{{ props.activeCareer.position_label }}</span>
                            <!-- A run with nothing logged has no position to claim, so it says so rather
                                 than pointing at Early January (D-220). -->
                            <span
                                v-else
                                title="No turn has been logged, so this career is not standing anywhere on the calendar yet."
                            >N/A</span>
                        </p>
                    </div>
                </div>

                <!-- Three stat tiles, then progress as its own full-width row: a fourth tile made
                     the strip three-across at rail widths and the number wrapped. `grid-cols-tiles`
                     folds the count instead of shrinking a value past its own width. -->
                <div class="mt-4 grid grid-cols-tiles gap-3" role="list" aria-label="Career progress">
                    <SubCell
                        label="Turn"
                        :value="group(props.activeCareer.turn)"
                        :value-class="subCellStrong"
                    />
                    <SubCell
                        label="Energy"
                        :value="props.activeCareer.energy !== null ? group(props.activeCareer.energy) : 'N/A'"
                        :value-class="subCellDefault"
                        :title="props.activeCareer.energy === null ? 'No logged turn has recorded Energy for this career.' : undefined"
                    />
                    <SubCell
                        label="Team Rank"
                        :value="props.activeCareer.team_rank.value ?? 'N/A'"
                        :value-class="subCellDefault"
                        :title="props.activeCareer.team_rank.value === null ? props.activeCareer.team_rank.value_hint : undefined"
                    />
                </div>

                <!-- Goal-Gradient: the 24-turn run reads as a bar, not only as a fraction. The
                     element carries the role and the values; the fill is decoration. -->
                <div class="mt-4">
                    <p class="text-xs font-medium text-ink-muted">Turn {{ props.activeCareer.turn }} / 24</p>
                    <div
                        role="progressbar"
                        aria-label="Turn progress"
                        :aria-valuenow="props.activeCareer.turn"
                        aria-valuemin="0"
                        aria-valuemax="24"
                        class="mt-1 h-2 overflow-hidden rounded-pill bg-sunken"
                    >
                        <div
                            class="h-full rounded-pill bg-brand-chrome transition-[width] duration-150 ease-out motion-reduce:transition-none"
                            :style="{ width: `${Math.min(100, (props.activeCareer.turn / 24) * 100)}%` }"
                        />
                    </div>
                </div>

                <!-- The next-step line, merged here from the deleted Next best action card. It names
                     no trainee and no scenario: the card already prints both above, and repeating
                     them made one glance carry the same sentence twice. -->
                <p class="mt-4 text-sm text-ink">
                    Resume this career to log its next turn.
                </p>

                <!-- The page's one filled primary. The outline duplicate is gone. -->
                <LinkButton
                    :href="props.activeCareer.resume_url"
                    variant="primary"
                    class="mt-4"
                >
                    Resume career
                </LinkButton>

                <!-- The multi-career notice is muted text with a link, not a pill: a bordered chip
                     competes with the action above it for the same glance. One career renders
                     nothing here. -->
                <p v-if="props.activeCareerCount > 0" class="mt-3 text-sm text-ink-muted">
                    {{ props.activeCareerCount }} career{{ props.activeCareerCount !== 1 ? 's' : '' }} are active{{ props.activeCareerCount > 1 ? '; this screen features the newest' : '' }}.
                    <Link :href="props.careersUrl" class="underline font-medium text-ink-strong hover:text-ink transition-colors duration-150 ease-out">View all careers ({{ props.activeCareerCount }})</Link>
                </p>
            </section>

            <!-- EMPTY STATE (col-span-8 on xl) -->
            <section
                v-else-if="!isCardHidden('active_career')"
                role="region"
                aria-labelledby="active-career-empty-heading"
                data-card="active_career" :class="[emptyCardBase, cardSpan('active_career')]"
            >
                <h2 id="active-career-empty-heading" class="text-lg font-semibold text-ink-strong">
                    Active Career
                </h2>
                <p class="mt-2 text-sm text-ink">No active career. Start a new training run.</p>
                <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                    Start a run to track turns, Energy and race record in one place.
                </p>
                <!-- The longer explanation is available but not load-bearing: three lines of prose
                     above the primary action pushed the action itself down the card. -->
                <details class="mt-2">
                    <summary class="cursor-pointer text-sm font-medium text-ink hover:text-ink-strong">
                        How careers work
                    </summary>
                    <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                        This screen resumes a career from wherever you left off. Creating one takes a
                        trainee and a scenario, and nothing here changes until you log your first turn.
                    </p>
                </details>
                <!-- One filled primary on the screen. The import door is a ghost beside it, so the
                     two offers are visibly ranked. -->
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <LinkButton href="/career/setup/scenario" variant="primary">
                        Start a new training run
                    </LinkButton>
                    <LinkButton href="/career/snapshot" variant="ghost">
                        Import an existing career
                    </LinkButton>
                </div>
            </section>

            <!-- QUICK ACTIONS (rail, top) -->
            <section
                v-show="!isCardHidden('quick_actions')"
                role="region"
                aria-labelledby="quick-actions-heading"
                data-card="quick_actions" :class="[cardBase, cardSpan('quick_actions')]"
            >
                <h2 id="quick-actions-heading" class="text-lg font-semibold text-ink-strong">
                    Quick Actions
                </h2>

                <!-- Every destination is a live route; a destination whose slice had not
                     landed would be a named absence here rather than a dead link. -->
                <nav aria-label="Quick actions" class="mt-2">
                    <!-- One full-width row per destination: a wrapped row of ghost buttons reads as a
                         toolbar, and a row is a larger target than a chip (Fitts). -->
                    <ul class="flex flex-col gap-2">
                        <li v-for="action in props.quickActions" :key="action.to">
                            <LinkButton :href="action.to" variant="secondary" class="w-full justify-start">
                                {{ action.label }}
                            </LinkButton>
                        </li>
                    </ul>
                </nav>

                <p class="mt-3 text-xs text-ink-muted">
                    Jump to a task the sidebar does not carry.
                </p>
                <!-- The snapshot door stays here so the Trainer who already has a career to resume
                     still sees it (R2-04); it moved out of Active Career, which now owns the
                     empty-state import instead. Two short lines, not one three-line paragraph. -->
                <p class="mt-2 text-xs text-ink-muted">
                    Partway through a career?
                    <Link href="/career/snapshot" class="underline font-medium text-ink">Import an existing career</Link>
                    instead.
                </p>
            </section>

            <!-- RECENT VETERANS (col-span-4 on xl) -->
            <section
                v-show="!isCardHidden('recent_veterans')"
                role="region"
                aria-labelledby="recent-veterans-heading"
                data-card="recent_veterans" :class="[cardBase, cardSpan('recent_veterans')]"
            >
                <h2 id="recent-veterans-heading" class="text-lg font-semibold text-ink-strong">
                    Recent Veterans
                </h2>

                <template v-if="props.recentVeterans.total === 0">
                    <p class="mt-2 text-sm text-ink">No veterans yet.</p>
                    <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                        Finish a career and choose Save Veteran on its Result screen.
                    </p>
                    <Link
                        :href="props.recentVeterans.library_url"
                        class="mt-4 inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-semibold text-ink hover:text-ink-strong transition-colors duration-150 ease-out"
                    >
                        Open the Veteran library
                    </Link>
                </template>

<template v-else>
                     <ul class="mt-2 divide-y divide-rule">
                         <li
                             v-for="veteran in props.recentVeterans.rows.slice(0, 3)"
                             :key="veteran.run_id"
                             class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-2"
                         >
                            <div class="flex items-center gap-3">
                                <ArtworkSlot
                                    v-if="veteran.artworkURL"
                                    :url="veteran.artworkURL"
                                    :alt="veteran.trainee"
                                    size="size-10"
                                />
                                <Link :href="veteran.run_url" class="text-sm font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out">
                                    {{ veteran.trainee }}
                                    <span
                                        v-if="veteran.trainee_ja"
                                        lang="ja"
                                        class="ml-2 text-sm font-normal text-ink-muted"
                                    >{{ veteran.trainee_ja }}</span>
                                </Link>
                            </div>
                            <span class="text-xs text-ink-muted">{{ veteran.scenario_label }}</span>
                        </li>
                    </ul>

                    <p class="mt-3 text-xs text-ink-muted">
                        Showing the {{ props.recentVeterans.rows.length }} most recent of
                        {{ group(props.recentVeterans.total) }}.
                        <Link :href="props.recentVeterans.library_url" class="underline hover:text-ink-strong transition-colors duration-150 ease-out">
                            Open the Veteran library
                        </Link>
                    </p>
                </template>
            </section>

            <!-- DATA STATUS (col-span-4 on xl) -->
            <section
                v-show="!isCardHidden('data_status')"
                role="region"
                aria-labelledby="data-status-heading"
                data-card="data_status" :class="[cardBase, cardSpan('data_status')]"
            >
                <h2 id="data-status-heading" class="text-lg font-semibold text-ink-strong">
                    Data Status
                </h2>

                <p class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm">
                    <span class="font-semibold text-ink-strong">{{ props.dataStatus.label }}</span>
                    <span aria-hidden="true" class="text-green-deep">●</span>
                    <span class="text-ink">{{ props.dataStatus.state }}</span>
                    <span v-if="props.dataStatus.verified_at" class="text-xs text-ink-muted">
                        Verified {{ props.dataStatus.verified_at }} · {{ props.dataStatus.source }}
                    </span>
                    <span
                        v-else
                        class="text-xs text-ink-muted"
                        title="No verification date is recorded for the scenario matrix."
                    >Verified N/A</span>
                </p>

                <p class="mt-2 text-xs text-ink-muted">
                    Ruleset version:
                    <span
                        v-if="ruleset"
                        class="font-mono text-ink"
                    >{{ ruleset }}</span>
                    <!-- app.ruleset is null: no source defines a Global ruleset version, so the
                         screen names the absence instead of inventing one (design-2.0 §48). -->
                    <span
                        v-else
                        class="font-mono text-ink"
                        title="No source defines a Global ruleset version, so this tool states none."
                    >N/A</span>
                </p>

                <!-- KPI stat cells -->
                <div class="mt-4 grid grid-cols-tiles gap-3">
                    <KpiCard label="Trainees" :value="group(props.counts.trainees)" :value-class="kpiValueClass" />
                    <KpiCard label="Skills" :value="group(props.counts.skills)" :value-class="kpiValueClass" />
                    <KpiCard label="Support cards" :value="group(props.counts.supportCards)" :value-class="kpiValueClass" />
                </div>

                <!-- Recent Builds footnote (PLAN 6B.13, frontend-fidelity §5 T1-5): the standalone
                     empty-state card is collapsed into this card's footnote. There is no build record
                     until the Career Plan wizard (D2–D7) lands. -->
                <p class="mt-3 text-xs text-ink-muted">
                    Recent builds: none yet.
                </p>
            </section>
        </div>
    </AppLayout>
</template>
