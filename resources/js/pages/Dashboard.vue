<script setup lang="ts">
import AppLayout from '../layouts/AppLayout.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { LinkButton } from '../components/Form';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useVisitState } from '../composables/useVisitState';
import { useLayout } from '../composables/useLayout';
import SubCell from '../components/SubCell.vue';
import ArtworkSlot from '../components/ArtworkSlot.vue';

/*
 * The Dashboard, rebuilt 2026-10-26 as a structural clone of the HFW dashboard.html. Region order:
 * page head, Active Career + Quick Actions, the three-tile KPI strip, Roster + Data Status, then
 * Recent Veterans full width. Counts and the turn figures are live props; nothing here types a
 * wireframe capture value, and the wireframe's own meta blocks (the annotation key, the proto chip)
 * stay in the wireframe rather than shipping into the product.
 */

interface ActiveCareer {
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    position_label: string | null;
    turn: number;
    position_turn: number | null;
    total_turns: number;
    energy: number | null;
    team_rank: { value: string | null; value_hint: string };
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

const props = defineProps<{
    activeCareer: ActiveCareer | null;
    activeCareerCount: number;
    finishedRunCount: number;
    globalScenarioCount: number;
    careersUrl: string;
    recentVeterans: { rows: VeteranRow[]; total: number; limit: number; library_url: string };
    quickActions: { label: string; to: string }[];
    counts: {
        careersRunning: number;
        /** The catalogue count (67), not the app counter (71 incl. test fixtures). */
        catalogueTrainees: number;
        skills: number;
        supportCards: number;
        forms: number;
    };
    dataStatus: { label: string; state: string; verified_at: string | null; source: string };
}>();

const page = usePage();
const ruleset = computed(() => page.props.app?.ruleset ?? null);

const { mode: dashboardLayout } = useLayout();
const savedSettings = computed(() => (page.props.preferences?.settings ?? {}) as Record<string, unknown>);
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

const totalTurns = computed<number>(() => props.activeCareer?.total_turns ?? 0);
const turnMarks = computed<number[]>(() => Array.from({ length: totalTurns.value }, (_, index) => index + 1));
const remainingTurns = computed<number>(() => Math.max(0, totalTurns.value - (props.activeCareer?.turn ?? 0)));
const turnLabels = computed<number[]>(() => {
    const total = totalTurns.value;
    if (total === 0) return [];
    const labels = [1];
    for (let mark = 12; mark < total; mark += 12) labels.push(mark);
    labels.push(total);
    return labels;
});

const gridClass = computed(() => {
    if (dashboardLayout.value === 'compact') {
        return 'grid w-full max-w-3xl grid-cols-1 gap-4 mx-auto';
    }
    return 'grid w-full mx-auto grid-cols-1 gap-6 items-start md:grid-cols-2 xl:grid-cols-12';
});

function cardSpan(cardId: string): string {
    if (dashboardLayout.value === 'compact') return '';
    // Four rows, in the wireframe's own order: Active Career + Quick Actions, the KPI strip,
    // Roster + Data Status, then Recent Veterans across the whole width. The starts are explicit so
    // the rows cannot drift when a card is hidden from Settings.
    const spans: Record<string, string> = {
        active_career: 'col-span-1 xl:col-span-8 @7xl:col-start-1 @7xl:row-start-1',
        quick_actions: 'col-span-1 xl:col-span-4 @7xl:col-start-9 @7xl:row-start-1',
        kpi: 'col-span-1 md:col-span-2 xl:col-span-12 @7xl:row-start-2',
        roster: 'col-span-1 xl:col-span-8 @7xl:col-start-1 @7xl:row-start-3',
        data_status: 'col-span-1 xl:col-span-4 @7xl:col-start-9 @7xl:row-start-3',
        recent_veterans: 'col-span-1 md:col-span-2 xl:col-span-12 @7xl:row-start-4',
    };
    return spans[cardId] ?? 'col-span-1';
}

const { visiting, visitFailed } = useVisitState();
const group = (n: number): string => n.toLocaleString('en-US');
const cardBase = 'rounded-md border border-rule bg-panel p-4 self-start';
const emptyCardBase = 'rounded-md border border-dashed border-rule bg-raised p-4 self-start';
const subCellStrong = 'font-mono text-xl tabular-nums text-ink-strong';
const subCellDefault = 'font-mono text-xl tabular-nums';
const kpiTile = 'rounded-md border border-rule bg-panel px-4 py-3 self-start';
const kpiKey = 'text-xs font-semibold uppercase tracking-wider text-ink-muted';
const kpiValue = 'mt-1 font-mono text-xl tabular-nums text-ink-strong';

// The page lede, composed from live figures; the verification line comes from the data status.
const lede = computed<string>(() => {
    const verified = props.dataStatus.verified_at;
    const verifiedLine =
        verified !== null
            ? `Verified ${verified} · ${props.dataStatus.source}`
            : 'Verification date not recorded';
    return (
        `${props.activeCareerCount} active career${props.activeCareerCount === 1 ? '' : 's'}, ` +
        `${props.finishedRunCount} finished run${props.finishedRunCount === 1 ? '' : 's'} and ` +
        `${props.globalScenarioCount} Global scenarios. ${verifiedLine}.`
    );
});

// The absence note under the turn rail (dashboard.html annotation 3): the rail fact is constant;
// the second sentence names exactly which tiles print N/A and why.
const absenceNote = computed<string>(() => {
    if (props.activeCareer === null) return '';
    const missing: string[] = [];
    if (props.activeCareer.energy === null) {
        missing.push('Energy');
    }
    if (props.activeCareer.team_rank.value === null) {
        missing.push('Team Rank');
    }
    const rail = 'The rail is one mark per career turn: seventy-two marks, the client\'s twenty-four turns a year over three years.';
    if (missing.length === 0) return rail;
    return `${rail} ${missing.join(' and ')} print N/A because no logged turn has recorded them yet.`;
});
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Dashboard' }]">
        <Head title="Dashboard" />
        <template #head>
            <PageHeader eyebrow="Overview" title="Dashboard" :description="lede">
                <template #actions>
                    <LinkButton href="/career/setup/scenario" variant="secondary">New career</LinkButton>
                    <Link
                        :href="props.careersUrl"
                        class="inline-flex min-h-11 items-center px-2 text-sm font-medium text-ink underline"
                    >All careers</Link>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="text-sm text-ink-muted">Loading…</p>

        <p
            v-if="visitFailed"
            role="alert"
            class="rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
        >
            That page could not be loaded. Try again, or open it from the navigation.
        </p>

        <div :class="gridClass" data-dashboard-root :data-layout="dashboardLayout">
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
                <LinkButton href="/preferences" variant="secondary" class="mt-4">Open Settings</LinkButton>
            </section>

            <!-- ACTIVE CAREER (col-span-8) -->
            <section
                v-if="props.activeCareer && !isCardHidden('active_career')"
                role="region"
                aria-labelledby="active-career-heading"
                data-card="active_career"
                :class="[cardBase, cardSpan('active_career')]"
            >
                <h2 id="active-career-heading" class="text-lg font-semibold text-ink-strong">Active Career</h2>

                <div class="mt-2 flex flex-wrap items-center gap-4">
                    <div class="min-w-0 flex-[1_1_250px]">
                        <p class="text-xl font-extrabold text-ink-strong truncate">
                            {{ props.activeCareer.trainee }}
                            <span class="ml-2 inline-block align-middle rounded-full bg-brand-accent-soft px-2 py-0.5 text-xs font-semibold text-brand-accent-ink">
                                {{ props.activeCareer.scenario_label }}
                            </span>
                        </p>
                        <p class="mt-1 text-xs text-ink-muted">
                            <template v-if="props.activeCareer.trainee_ja">{{ props.activeCareer.trainee_ja }} · </template>
                            <template v-if="props.activeCareer.position_label">{{ props.activeCareer.position_label }}</template>
                            <span v-else title="No turn has been logged, so this career is not standing anywhere on the calendar yet.">
                                N/A<span class="sr-only">: no turn has been logged yet.</span>
                            </span>
                        </p>
                    </div>
                    <LinkButton :href="props.activeCareer.resume_url" variant="primary" class="shrink-0">
                        Resume career
                    </LinkButton>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3" aria-label="Career progress">
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

                <!-- The 72-mark turn rail (dashboard.html annotation 2). -->
                <div class="mt-4">
                    <div class="flex items-baseline justify-between text-xs">
                        <span class="font-extrabold text-ink-strong">Turn {{ props.activeCareer.turn }} of {{ totalTurns }}</span>
                        <span class="text-ink-muted">{{ remainingTurns }} turns to go</span>
                    </div>
                    <ol
                        role="list"
                        aria-label="Career progress"
                        class="mt-2 grid gap-px"
                        :style="{ gridTemplateColumns: `repeat(${totalTurns}, minmax(0, 1fr))` }"
                    >
                        <li
                            v-for="mark in turnMarks"
                            :key="mark"
                            :class="mark <= props.activeCareer.turn
                                ? 'h-2.5 rounded-sm bg-primary-action'
                                : 'h-2.5 rounded-sm border border-rule'"
                            :aria-current="mark === props.activeCareer.turn ? 'step' : undefined"
                        />
                    </ol>
                    <div
                        aria-hidden="true"
                        class="mt-1 grid text-xs text-ink-muted"
                        :style="{ gridTemplateColumns: `repeat(${totalTurns}, minmax(0, 1fr))` }"
                    >
                        <span
                            v-for="mark in turnLabels"
                            :key="mark"
                            class="whitespace-nowrap"
                            :style="{ gridColumnStart: mark, justifySelf: mark === totalTurns ? 'end' : 'start' }"
                        >{{ mark }}</span>
                    </div>
                </div>

                <p class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted">
                    <span aria-hidden="true" class="text-status-positive">○</span>
                    <span>{{ absenceNote }}</span>
                </p>
            </section>

            <!-- ACTIVE CAREER EMPTY STATE -->
            <section
                v-else-if="!isCardHidden('active_career')"
                role="region"
                aria-labelledby="active-career-empty-heading"
                data-card="active_career"
                :class="[emptyCardBase, cardSpan('active_career')]"
            >
                <h2 id="active-career-empty-heading" class="text-lg font-semibold text-ink-strong">Active Career</h2>
                <p class="mt-2 text-sm text-ink">No active career. Start a new career.</p>
                <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                    Start a run to track turns, Energy and race record in one place.
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <LinkButton href="/career/setup/scenario" variant="primary">Start a new career</LinkButton>
                    <LinkButton href="/career/snapshot" variant="ghost">Import an existing career</LinkButton>
                </div>
            </section>

            <!-- QUICK ACTIONS -->
            <section
                v-show="!isCardHidden('quick_actions')"
                role="region"
                aria-labelledby="quick-actions-heading"
                data-card="quick_actions"
                :class="[cardBase, cardSpan('quick_actions')]"
            >
                <h2 id="quick-actions-heading" class="text-lg font-semibold text-ink-strong">Quick Actions</h2>
                <nav aria-label="Quick actions" class="mt-2">
                    <ul class="flex flex-col gap-2">
                        <li v-for="action in props.quickActions" :key="action.to">
                            <LinkButton :href="action.to" variant="secondary" class="w-full justify-start">
                                {{ action.label }}
                            </LinkButton>
                        </li>
                    </ul>
                </nav>
                <p class="mt-3 text-xs text-ink-muted">
                    Jump to a task the sidebar does not carry. Partway through a career?
                    <Link href="/career/snapshot" class="underline font-medium text-ink">Import an existing career</Link>
                    instead.
                </p>
            </section>

            <!-- KPI STRIP: three tiles (dashboard.html). -->
            <div :class="cardSpan('kpi')" data-dashboard-kpi>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-start">
                    <div :class="kpiTile">
                        <div :class="kpiKey">Trainees</div>
                        <div :class="kpiValue">{{ group(props.counts.catalogueTrainees) }}</div>
                    </div>
                    <div :class="kpiTile">
                        <div :class="kpiKey">Skills</div>
                        <div :class="kpiValue">{{ group(props.counts.skills) }}</div>
                    </div>
                    <div :class="kpiTile">
                        <div :class="kpiKey">Support cards</div>
                        <div :class="kpiValue">{{ group(props.counts.supportCards) }}</div>
                    </div>
                </div>
            </div>

            <!-- ROSTER -->
            <section
                role="region"
                aria-labelledby="roster-heading"
                data-card="roster"
                :class="[cardBase, cardSpan('roster')]"
            >
                <h2 id="roster-heading" class="text-lg font-semibold text-ink-strong">Roster</h2>
                <p class="mt-1 text-sm text-ink-muted">Every trainee the catalogue holds, with the forms joined on.</p>
                <div class="mt-3 grid grid-cols-3 gap-3">
                    <div class="rounded-md border border-rule px-3 py-2">
                        <div :class="kpiKey">Trainees</div>
                        <div class="mt-1 font-mono text-lg tabular-nums text-ink-strong">{{ group(props.counts.catalogueTrainees) }}</div>
                    </div>
                    <div class="rounded-md border border-rule px-3 py-2">
                        <div :class="kpiKey">Forms</div>
                        <div class="mt-1 font-mono text-lg tabular-nums text-ink-strong">{{ group(props.counts.forms) }}</div>
                    </div>
                    <div class="rounded-md border border-rule px-3 py-2">
                        <div :class="kpiKey">Release</div>
                        <div class="mt-1 text-lg text-ink-strong">Global</div>
                    </div>
                </div>
                <LinkButton href="/catalog" variant="secondary" size="sm" class="mt-3">Browse catalog</LinkButton>
            </section>

            <!-- DATA STATUS -->
            <section
                v-show="!isCardHidden('data_status')"
                role="region"
                aria-labelledby="data-status-heading"
                data-card="data_status"
                :class="[cardBase, cardSpan('data_status')]"
            >
                <h2 id="data-status-heading" class="text-lg font-semibold text-ink-strong">Data Status</h2>
                <p class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm">
                    <b class="text-ink-strong">{{ props.dataStatus.label }}</b>
                    <span aria-hidden="true" class="text-status-positive">●</span>
                    <span>{{ props.dataStatus.state }}</span>
                </p>
                <p class="mt-2 text-xs text-ink-muted">
                    <template v-if="props.dataStatus.verified_at">
                        Verified {{ props.dataStatus.verified_at }} · {{ props.dataStatus.source }}
                    </template>
                    <span v-else title="No verification date is recorded for the scenario matrix.">
                        Verified N/A<span class="sr-only">: no verification date is recorded.</span>
                    </span>
                </p>
                <p class="mt-2 text-xs text-ink-muted">
                    Ruleset version:
                    <span v-if="ruleset" class="font-mono text-ink">{{ ruleset }}</span>
                    <span v-else class="font-mono text-ink" title="No source defines a Global ruleset version, so this tool states none.">N/A</span>
                </p>
                <Link href="/database/sources" class="mt-2 inline-block text-xs font-medium text-ink-strong underline">
                    Source ledger
                </Link>
            </section>

            <!-- RECENT VETERANS (full width) -->
            <section
                v-show="!isCardHidden('recent_veterans')"
                role="region"
                aria-labelledby="recent-veterans-heading"
                data-card="recent_veterans"
                :class="[cardBase, cardSpan('recent_veterans')]"
            >
                <h2 id="recent-veterans-heading" class="text-lg font-semibold text-ink-strong">Recent Veterans</h2>

                <template v-if="props.recentVeterans.total === 0">
                    <p class="mt-2 text-sm text-ink">No veterans yet.</p>
                    <p class="mt-2 text-sm text-ink-muted">
                        Finish a career and choose Save Veteran on its Result screen.
                    </p>
                    <LinkButton :href="props.recentVeterans.library_url" variant="secondary" size="sm" class="mt-3">
                        Open the Veteran library
                    </LinkButton>
                </template>

                <template v-else>
                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                                    <th class="py-2 pr-3">Trainee</th>
                                    <th class="pr-3">Scenario</th>
                                    <th class="pr-3">Saved</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="veteran in props.recentVeterans.rows.slice(0, 3)" :key="veteran.run_id" class="border-b border-rule last:border-b-0">
                                    <td class="py-2 pr-3">
                                        <span class="flex items-center gap-2">
                                            <ArtworkSlot
                                                v-if="veteran.artworkURL"
                                                :url="veteran.artworkURL"
                                                :alt="veteran.trainee"
                                                size="size-10"
                                            />
                                            <span>
                                                <b class="text-ink-strong">{{ veteran.trainee }}</b>
                                                <span v-if="veteran.trainee_ja" class="ml-1 text-ink-muted">{{ veteran.trainee_ja }}</span>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="py-2 pr-3">{{ veteran.scenario_label }}</td>
                                    <td class="py-2 pr-3">
                                        <Link :href="veteran.run_url" class="font-medium text-ink-strong underline">{{ veteran.label }}</Link>
                                    </td>
                                    <td class="py-2 text-right">
                                        <LinkButton :href="veteran.run_url" variant="secondary" size="sm">Open</LinkButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs text-ink-muted">{{ props.recentVeterans.rows.length }} most recent shown</span>
                        <LinkButton :href="props.recentVeterans.library_url" variant="secondary" size="sm">All veterans</LinkButton>
                    </div>
                </template>
            </section>
        </div>
    </AppLayout>
</template>