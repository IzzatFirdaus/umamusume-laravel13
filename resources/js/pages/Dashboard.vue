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

// Layout preference (PLAN 6B.15): reads `dashboard_layout` and `dashboard_hidden_cards` from the
// shared preferences prop. Default is `bento` with all cards visible. `wide_bento` falls back to
// `bento` until the DESIGN.md ruling lands (it is disabled in the Settings UI).
const savedSettings = computed(() => (page.props.preferences?.settings ?? {}) as Record<string, unknown>);
const dashboardLayout = computed<'bento' | 'wide_bento' | 'focused'>(() =>
    ['bento', 'wide_bento', 'focused'].includes(savedSettings.value.dashboard_layout as string)
        ? (savedSettings.value.dashboard_layout as 'bento' | 'wide_bento' | 'focused')
        : 'bento',
);
const hiddenCards = computed<string[]>(() =>
    Array.isArray(savedSettings.value.dashboard_hidden_cards)
        ? (savedSettings.value.dashboard_hidden_cards as string[])
        : [],
);

function isCardHidden(cardId: string): boolean {
    return hiddenCards.value.includes(cardId);
}

// The five cards this screen composes. All hidden is a state of its own (DL.5): the grid renders
// one empty-state card rather than a blank page.
const DASHBOARD_CARD_IDS: ReadonlyArray<string> = [
    'active_career',
    'next_best_action',
    'quick_actions',
    'recent_veterans',
    'data_status',
];
const allCardsHidden = computed<boolean>(() => DASHBOARD_CARD_IDS.every((id) => hiddenCards.value.includes(id)));

// Grid container class varies by layout preference (PLAN 6B.15).
const gridClass = computed(() => {
    if (dashboardLayout.value === 'focused') {
        return 'grid w-full max-w-3xl grid-cols-1 gap-4 mx-auto';
    }
    // wide_bento uses the wider shell (DESIGN.md §2.3 ruling).
    if (dashboardLayout.value === 'wide_bento') {
        return 'grid w-full max-w-7xl grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12 mx-auto';
    }
    // bento (default)
    return 'grid w-full grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12';
});

// Card col-span per layout. Focused forces single-column at all breakpoints; bento uses the
// responsive grid described in PLAN 6B.13.
function cardSpan(cardId: string): string {
    if (dashboardLayout.value === 'focused') {
        return '';
    }
    const spans: Record<string, string> = {
        active_career: 'col-span-1 xl:col-span-8',
        next_best_action: 'col-span-1 xl:col-span-4',
        quick_actions: 'col-span-1 xl:col-span-4',
        recent_veterans: 'col-span-1 xl:col-span-4',
        data_status: 'col-span-1 md:col-span-2 xl:col-span-4',
    };
    return spans[cardId] ?? 'col-span-1';
}

const { visiting, visitFailed } = useVisitState();

const group = (n: number): string => n.toLocaleString('en-US');

const cardBase = 'rounded-md border border-rule bg-panel p-4';
const emptyCardBase = 'rounded-md border border-dashed border-rule bg-raised p-4';
const subCellStrong = 'font-mono text-xl tabular-nums text-ink-strong';
const subCellDefault = 'font-mono text-xl tabular-nums';
const kpiValueClass = 'font-mono text-3xl tabular-nums text-ink-strong';
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
        <div :class="gridClass" :data-layout="dashboardLayout">
            <!-- ALL CARDS HIDDEN (DL.5): one empty-state card rather than a blank page. It sits
                 inside the same shell measure as the grid it replaces. -->
            <section
                v-if="allCardsHidden"
                role="region"
                aria-labelledby="dashboard-empty-heading"
                :class="[emptyCardBase, dashboardLayout === 'focused' ? '' : 'col-span-1 md:col-span-2 xl:col-span-12']"
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
                :class="[cardBase, cardSpan('active_career')]"
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

                <!-- Five stat cells compressed into one 4-column strip (Slice 1.1). The duplicate
                     Team Rank cell is gone: Team Rank now has its own row, read through the model's
                     one owner (`latestTeamRank()`). -->
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4" role="list" aria-label="Career progress">
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
                    <!-- Progress indicator: Turn N / 24 -->
                    <SubCell
                        label="Progress"
                        :value="`Turn ${props.activeCareer.turn} / 24`"
                        :value-class="subCellStrong"
                    />
                </div>

                <!-- Active Career's Resume button is secondary (neutral), not lime.
                     The single accent is Next Best Action's button below. -->
                <LinkButton
                    :href="props.activeCareer.resume_url"
                    variant="secondary"
                    class="mt-4"
                >
                    Resume Career
                </LinkButton>

<!-- One career is the ordinary case and renders nothing here: the count speaks only
                      when a second is live, so the single-run layout is unchanged. The whole notice
                      wears the pill (Slice 1.3): the count and its handover link are one statement,
                      and the smallest element carrying "careers are active" must also carry the
                      Open Careers door the keyboard test reads (`dashboard.spec.ts`'s scoping). -->
                 <p
                     v-if="props.activeCareerCount > 0"
                     class="mt-3 inline-flex flex-wrap items-center gap-x-2 rounded-pill border border-rule bg-sunken px-3 py-1.5 text-sm text-ink"
                 >
                     {{ props.activeCareerCount }} career{{ props.activeCareerCount !== 1 ? 's' : '' }} are active{{ props.activeCareerCount > 1 ? '; this screen features the newest' : '' }}.
                     <Link :href="props.careersUrl" class="underline font-medium text-ink-strong hover:text-ink transition-colors duration-150 ease-out">Open Careers</Link>
                 </p>
            </section>

            <!-- EMPTY STATE (col-span-8 on xl) -->
            <section
                v-else-if="!isCardHidden('active_career')"
                role="region"
                aria-labelledby="active-career-empty-heading"
                :class="[emptyCardBase, cardSpan('active_career')]"
            >
                <h2 id="active-career-empty-heading" class="text-lg font-semibold text-ink-strong">
                    Active Career
                </h2>
                <p class="mt-2 text-sm text-ink">No active career. Start a new training run.</p>
                <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                    A career keeps your turns, Energy and race record in one place, and this screen
                    resumes it from wherever you left off. Creating one takes a trainee and a scenario,
                    and nothing here changes until you log your first turn.
                </p>
                <LinkButton
                    :href="'/career/setup/scenario'"
                    variant="secondary"
                    class="mt-4"
                >
                    Start a new training run
                </LinkButton>
            </section>

            <!-- NEXT BEST ACTION (col-span-4 on xl) — the only lime-primary on the screen -->
            <section
                v-if="props.activeCareer && !isCardHidden('next_best_action')"
                role="region"
                aria-labelledby="next-action-heading"
                :class="[cardBase, cardSpan('next_best_action')]"
            >
                <h3 id="next-action-heading" class="text-lg font-semibold text-ink-strong">Next best action</h3>
<p class="mt-1 text-sm text-ink">
                     Resume <strong>{{ props.activeCareer.trainee }}</strong> at <strong>{{ props.activeCareer.scenario_label }}</strong>.
                     <span v-if="props.activeCareer.position_label"> Currently at {{ props.activeCareer.position_label }}.</span>
                     <span v-else title="No turn has been logged, so this career is not standing anywhere on the calendar yet.">No turn logged yet.</span>
                 </p>
                <Link
                    :href="props.activeCareer.resume_url"
                    class="enamel mt-3 inline-flex min-h-11 items-center rounded-full bg-action px-4 text-sm font-bold text-on-action"
                >
                    Resume Career
                </Link>
            </section>

            <!-- QUICK ACTIONS (col-span-4 on xl) -->
            <section
                v-show="!isCardHidden('quick_actions')"
                role="region"
                aria-labelledby="quick-actions-heading"
                :class="[cardBase, cardSpan('quick_actions')]"
            >
                <h2 id="quick-actions-heading" class="text-lg font-semibold text-ink-strong">
                    Quick Actions
                </h2>

                <!-- Every destination is a live route; a destination whose slice had not
                     landed would be a named absence here rather than a dead link. -->
                <nav aria-label="Quick actions" class="mt-2">
                    <ul class="flex flex-wrap gap-3">
                        <li v-for="action in props.quickActions" :key="action.to">
                            <LinkButton :href="action.to" variant="secondary">
                                {{ action.label }}
                            </LinkButton>
                        </li>
                    </ul>
                </nav>

                <!-- The snapshot door lives in Quick Actions so the Trainer who already has a
                     career to resume sees it (R2-04). -->
                <p class="mt-3 text-xs text-ink-muted">
                    Already partway through a career?
                    <Link href="/career/snapshot" class="underline font-medium text-ink">Import an existing career</Link>
                    instead, and this tool starts from where your client says it stands.
                </p>
            </section>

            <!-- RECENT VETERANS (col-span-4 on xl) -->
            <section
                v-show="!isCardHidden('recent_veterans')"
                role="region"
                aria-labelledby="recent-veterans-heading"
                :class="[cardBase, cardSpan('recent_veterans')]"
            >
                <h2 id="recent-veterans-heading" class="text-lg font-semibold text-ink-strong">
                    Recent Veterans
                </h2>

                <template v-if="props.recentVeterans.total === 0">
                    <p class="mt-2 text-sm text-ink">No Veterans recorded yet.</p>
                    <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                        A Veteran is a completed career filed into the library. Nothing is filed there:
                        a career is saved from its own Result screen, so open one that finished and
                        choose Save Veteran.
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
                :class="[cardBase, cardSpan('data_status')]"
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
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
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
