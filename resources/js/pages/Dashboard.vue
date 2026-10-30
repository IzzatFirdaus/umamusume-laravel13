<script setup lang="ts">
import AppLayout from '../layouts/AppLayout.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { LinkButton } from '../components/Form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useVisitState } from '../composables/useVisitState';
import { useLayout } from '../composables/useLayout';
import ArtworkSlot from '../components/ArtworkSlot.vue';
import AbsenceValue from '../components/AbsenceValue.vue';
import Pill from '../components/ui/Pill.vue';
import Tile from '../components/ui/Tile.vue';
import Note from '../components/ui/Note.vue';

/*
 * The landing screen, rebuilt as a structural clone of HFW `index.html` (owner steering: `index.html`
 * is the dashboard reference, not `dashboard.html` -- the two draw different boards).
 *
 * Region order is the wireframe's reading order and it is load-bearing (index.html:69-186):
 *
 *   1. page head: eyebrow, h1, lede, two actions, hairline
 *   2. "Pick up where you left off" + "Needs you"          (.g-side, 2.1fr / 1fr)
 *   3. four KPI tiles: Careers running / Trainees / Skills / Support cards   (.g-4)
 *   4. "Recent veterans" + "Quick actions"                 (.g-side, 2.1fr / 1fr)
 *
 * The Roster card and the separate Data Status card are gone from `index.html`, and its own
 * annotation 5 says so: the flat board became two, and the counts a Trainer read there are the
 * KPI tiles' own figures. The catalogue figure reachable from a card stays, as the "Trainees
 * catalogued" tile's door.
 *
 * The 72-mark turn rail is NOT drawn here: `index.html` has no rail, and the rail belongs to
 * `dashboard.html`'s board (annotation 2 there). Its fact survives as the Turn tile's "of 72",
 * which is exactly where `index.html:90` prints it.
 *
 * The wireframe's Table of contents and Annotation key are document artifacts -- one is a
 * navigation aid for reading the set, the other is a checklist for the person reviewing it -- and
 * neither is a screen. They are absent, and `tests/browser/dashboard.spec.ts` pins their absence.
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
    /** Independent Training overlay (§1.1.7), present when this run is an idle session. */
    indep: { in_progress: boolean; completes_at: string | null; policy_label: string | null; abandon_url: string } | null;
}

interface VeteranRow {
    id: number;
    /** The disambiguating sentence: "Trainee · Scenario · date". */
    label: string;
    /** The Saved column's own value, `2026-10-15`. Empty when the run carries no start date. */
    saved_date: string;
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

/*
 * The wireframe's grids (shell.css:399-406) are the shared `.g-side` / `.g-4` / `.g-3` primitives.
 * Their canvas crossings (844/584 for the wireframe's 1080/820 viewports) and the Compact
 * one-column rule live in `resources/css/layout-grid.css`, so every page reads one owner.
 */

const totalTurns = computed<number>(() => props.activeCareer?.total_turns ?? 0);

// The 50-minute countdown for an Independent Training session (§1.1.7), computed from the stored
// completion instant so it is correct on load without a poll. `mm:ss`, clamped at zero.
const indepRemaining = computed<string | null>(() => {
    const completes = props.activeCareer?.indep?.completes_at;

    if (completes === null || completes === undefined) {
        return null;
    }

    const seconds = Math.max(0, Math.floor((new Date(completes).getTime() - Date.now()) / 1000));
    const mm = Math.floor(seconds / 60);
    const ss = seconds % 60;

    return `${mm}:${String(ss).padStart(2, '0')}`;
});

/*
 * The lede is the wireframe's own sentence with its three figures bound to live props
 * (index.html:59). The verification line that used to trail it now lives where the wireframe puts
 * it -- inside the "Needs you" column's data-status tile (index.html:116) -- because a fact printed
 * twice on one screen reads as two facts.
 */
const lede = computed<string>(
    () =>
        `${props.activeCareerCount} active career${props.activeCareerCount === 1 ? '' : 's'}, ` +
        `${props.finishedRunCount} finished run${props.finishedRunCount === 1 ? '' : 's'}, and ` +
        `${props.globalScenarioCount} permanent Global scenario${props.globalScenarioCount === 1 ? '' : 's'}.`,
);

// The Japanese name and the calendar position, on the one line the wireframe gives them
// (index.html:80-82: `ライスシャワー · Junior Year · Early January`).
const careerMeta = computed<string[]>(() => {
    if (props.activeCareer === null) return [];
    return [props.activeCareer.trainee_ja, props.activeCareer.position_label].filter(
        (part): part is string => part !== null && part !== '',
    );
});

/*
 * The note under the tiles (index.html:102-107). One paragraph, in the wireframe's order:
 *
 *   1. which figures no logged turn has reported, when any are missing;
 *   2. the bold sentence naming the turn-count contradiction -- `turns recorded` (the count this
 *      strip prints, `stripValues()['turn']`) against `the position the run was imported at` (the
 *      cockpit's own standing, `nextTurnToPlay()`);
 *   3. the two live numbers, so the disagreement is checkable rather than asserted.
 *
 * The bold sentence and the numbers print whenever a career is running: the contradiction exists
 * whether or not Energy happens to be absent, and a note that only appeared for the absent figures
 * would drop the labelled-defect half as soon as a Trainer logged Energy. With nothing missing the
 * paragraph opens on the bold sentence instead of inventing an absence to lead with.
 */
const careerNote = computed<string | null>(() => {
    const career = props.activeCareer;

    if (career === null) return null;

    const missing: string[] = [];
    if (career.energy === null) missing.push('Energy');
    if (career.team_rank.value === null) missing.push('a team rank');

    const absence =
        missing.length === 0
            ? ''
            : `No logged turn has reported ${missing.length === 1 ? missing[0] : `${missing[0]} or ${missing[1]}`} yet. `;

    const recorded = career.turn;
    const cockpit = career.cockpit_turn;

    const numbers =
        cockpit === null
            ? `The strip here counts ${recorded} recorded turn${recorded === 1 ? '' : 's'}, and this run's cockpit has no imported position to report.`
            : `This run's cockpit reports turn ${cockpit} from its imported position; the strip here counts ${recorded} recorded turn${recorded === 1 ? '' : 's'}.`;

    return `${absence}A turn is counted from the turns recorded, not from the position the run was imported at. ${numbers} Both are labelled, and the disagreement is a known defect, not a rendering choice.`;
});

// How many Veterans the table is actually showing, for the footer's "7 veterans · 3 shown"
// (index.html:171). The limit travels from the controller, so the two halves of the sentence are
// both readings of the payload rather than one of them being typed here.
const veteransShown = computed<number>(() => props.recentVeterans.rows.length);
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Dashboard' }]">
        <Head title="Dashboard" />
        <template #head>
            <PageHeader eyebrow="Dashboard" title="Trainer Desk" :description="lede">
                <template #actions>
                    <!-- The wireframe's own pair (index.html:61-64): "New career" is an outline and
                         "All careers" is the quiet one, so the page's ONE filled action is "Open
                         cockpit" in the resume card (annotations 1 and 6). A second filled button in
                         the head would make the accent mean "a button" rather than "the button". -->
                    <LinkButton href="/career/setup/scenario" variant="secondary">New career</LinkButton>
                    <LinkButton :href="props.careersUrl" variant="ghost">All careers</LinkButton>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading...</p>

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
            <!-- ROW ONE: Pick up where you left off | Needs you (index.html:71-131) -->
            <div class="grid gap-3.5 g-side" data-dashboard-row="pickup">
                <section
                    v-if="props.activeCareer && !isCardHidden('active_career')"
                    role="region"
                    aria-labelledby="pickup-heading"
                    class="card"
                    data-card="active_career"
                >
                    <h2 id="pickup-heading">Pick up where you left off</h2>

                    <!-- One row: the frame, the name block, and the one filled action on the page
                         (index.html:74-85, `display:flex; align-items:center; gap:16px`). The button
                         is a sibling of the text rather than a child of it, which is what puts it on
                         the right edge at desktop and lets it wrap under the name on a phone. -->
                    <div class="flex flex-wrap items-center gap-4 pb-1.5 pt-0.5">
                        <ArtworkSlot
                            :url="props.activeCareer.artworkURL"
                            alt=""
                            size="size-16"
                            class="shrink-0"
                        />
                        <div class="min-w-0 flex-[1_1_250px]">
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="text-[19px] font-extrabold leading-tight text-ink-strong">{{ props.activeCareer.trainee }}</span>
                                <Pill tone="accent">{{ props.activeCareer.scenario_label }}</Pill>
                            </p>
                            <p class="mt-[3px] text-[12.5px] text-ink-muted">
                                <template v-if="careerMeta.length">
                                    {{ careerMeta.join(' · ') }}
                                </template>
                                <AbsenceValue
                                    v-else
                                    compact
                                    reason="No turn has been logged, so this career is not standing anywhere on the calendar yet."
                                />
                            </p>
                            <!-- Independent Training (§1.1.7): while the 50-minute window is open the
                                 Resume action yields the timer and the one-at-a-time rule. -->
                            <template v-if="props.activeCareer.indep?.in_progress">
                                <p class="mt-1 text-sm font-semibold text-ink-strong">
                                    Independent Training in progress
                                    <template v-if="props.activeCareer.indep.policy_label"> · {{ props.activeCareer.indep.policy_label }}</template>
                                    <template v-if="indepRemaining !== null"> · {{ indepRemaining }} remaining</template>
                                </p>
                                <p class="mt-0.5 text-xs text-ink-muted">
                                    You cannot start another training until this one completes.
                                </p>
                                <button
                                    type="button"
                                    class="mt-1.5 inline-flex min-h-9 items-center rounded-md border border-rule bg-raised px-3 text-xs font-medium text-ink hover:bg-sunken transition-colors duration-150 ease-out"
                                    @click="router.post(props.activeCareer.indep.abandon_url)"
                                >
                                    Give up
                                </button>
                            </template>
                        </div>
                        <LinkButton :href="props.activeCareer.resume_url" variant="primary">Open cockpit</LinkButton>
                    </div>

                    <!-- index.html:87-100: `.grid g-3`, three tiles. Tile two is the scenario's own
                         third resource where it has one; where the scenario's resource IS team rank
                         (Unity Cup) the resource is already tile three and tile two falls back to
                         Energy. The Turn tile carries "of 72" as the wireframe's quiet suffix, which
                         is the whole of the turn-length fact on this board. -->
                    <div class="mt-1.5 grid gap-2.5 g-3">
                        <Tile
                            label="Turn"
                            :value="group(props.activeCareer.turn)"
                            :suffix="'of ' + totalTurns"
                            data-tile="turn"
                        />
                        <Tile
                            v-if="props.activeCareer.resource"
                            :label="props.activeCareer.resource.label"
                            :value="props.activeCareer.resource.value !== null ? group(props.activeCareer.resource.value) : 'not recorded'"
                            :absent="props.activeCareer.resource.value === null"
                            :hint="props.activeCareer.resource.value === null ? props.activeCareer.resource.value_hint : undefined"
                            data-tile="resource"
                        />
                        <Tile
                            v-else
                            label="Energy"
                            :value="props.activeCareer.energy !== null ? group(props.activeCareer.energy) : 'not recorded'"
                            :absent="props.activeCareer.energy === null"
                            :hint="props.activeCareer.energy === null ? 'No logged turn has recorded Energy for this career.' : undefined"
                            data-tile="energy"
                        />
                        <Tile
                            label="Team rank"
                            :value="props.activeCareer.team_rank.value ?? 'not recorded'"
                            :absent="props.activeCareer.team_rank.value === null"
                            :hint="props.activeCareer.team_rank.value === null ? props.activeCareer.team_rank.value_hint : undefined"
                            data-tile="team_rank"
                        />
                    </div>

                    <Note v-if="careerNote" class="mt-2.5">{{ careerNote }}</Note>
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

                <!-- The wireframe's three stacked tiles (index.html:110-130): `.k` key, a 14px value,
                     a muted subline, and one underlined door each. -->
                <section
                    v-show="!isCardHidden('roster') && !isCardHidden('data_status')"
                    role="region"
                    aria-labelledby="needs-you-heading"
                    class="card"
                    data-card="needs_you"
                >
                    <h2 id="needs-you-heading">Needs you</h2>
                    <div class="flex flex-col gap-[9px]">
                        <!-- The risk edge reads the `stale` flag, not the word (index.html:113). -->
                        <div
                            class="flex items-center justify-between gap-3 rounded-sm border border-rule bg-raised px-2.5 py-2"
                            :class="props.needsYou.dataStatus.stale ? 'border-l-[3px] border-l-risk' : ''"
                            data-needs-you="data_status"
                        >
                            <div class="min-w-0">
                                <div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Data status</div>
                                <div class="text-[14px] font-extrabold leading-[1.3] text-ink-strong">
                                    {{ props.needsYou.dataStatus.label }} &middot; {{ props.needsYou.dataStatus.state }}
                                </div>
                                <p class="text-[11.5px] text-ink-muted">
                                    <template v-if="props.needsYou.dataStatus.verified_at">
                                        Verified {{ props.needsYou.dataStatus.verified_at }} &middot; {{ props.needsYou.dataStatus.source }}
                                    </template>
                                    <AbsenceValue
                                        v-else
                                        compact
                                        reason="No verification date is recorded for the scenario matrix."
                                    />
                                </p>
                            </div>
                            <Link
                                href="/database/sources"
                                class="inline-flex min-h-11 shrink-0 items-center text-[12px] text-ink-strong underline underline-offset-2 hover:decoration-2"
                            >Source ledger</Link>
                        </div>

                        <div
                            class="flex items-center justify-between gap-3 rounded-sm border border-rule bg-raised px-2.5 py-2"
                            data-needs-you="veterans"
                        >
                            <div class="min-w-0">
                                <div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Veterans</div>
                                <div class="text-[14px] font-extrabold leading-[1.3] text-ink-strong">
                                    {{ group(props.needsYou.veteranCount) }} banked
                                </div>
                            </div>
                            <Link
                                :href="props.recentVeterans.library_url"
                                class="inline-flex min-h-11 shrink-0 items-center text-[12px] text-ink-strong underline underline-offset-2 hover:decoration-2"
                            >Open the library</Link>
                        </div>

                        <div
                            class="flex items-center justify-between gap-3 rounded-sm border border-rule bg-raised px-2.5 py-2"
                            data-needs-you="review"
                        >
                            <div class="min-w-0">
                                <div class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Review queue</div>
                                <div class="text-[14px] font-extrabold leading-[1.3] text-ink-strong">
                                    <template v-if="props.needsYou.reviewPending > 0">
                                        {{ group(props.needsYou.reviewPending) }} pending {{ props.needsYou.reviewPending === 1 ? 'match' : 'matches' }}
                                    </template>
                                    <!-- index.html:126 prints the bare instruction. With nothing waiting the
                                         instruction is still the door's own words, and the count beside it
                                         would read "0 pending matches" -- a figure that says nothing. -->
                                    <template v-else>Check pending matches</template>
                                </div>
                            </div>
                            <Link
                                href="/review"
                                class="inline-flex min-h-11 shrink-0 items-center text-[12px] text-ink-strong underline underline-offset-2 hover:decoration-2"
                            >Open review</Link>
                        </div>
                    </div>
                </section>
            </div>

            <!-- KPI strip: the wireframe's `.g-4` (index.html:133-154). Four figures, each a door to
                 the screen that owns its list where the wireframe draws one. "Careers running" leads
                 because it is the figure the resume card above features. -->
            <div class="grid gap-3.5 g-4" data-dashboard-row="kpi">
                <section class="card" aria-labelledby="kpi-careers-heading" data-card="kpi_careers">
                    <h2 id="kpi-careers-heading" class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Careers running</h2>
                    <p class="text-[26px] font-extrabold leading-[1.2] text-ink-strong">{{ group(props.counts.careersRunning) }}</p>
                    <p class="text-[11.5px] text-ink-muted">
                        <span v-if="props.activeCareer">{{ props.activeCareer.trainee }} &middot; {{ props.activeCareer.scenario_label }}</span>
                        <span v-else>No career is running.</span>
                    </p>
                </section>

                <section class="card" aria-labelledby="kpi-trainees-heading" data-card="kpi_trainees">
                    <h2 id="kpi-trainees-heading" class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Trainees catalogued</h2>
                    <!-- The catalogue figure, not the app counter: the wireframe's 71 is what the
                         sourced dataset publishes, and the plan's §7.2 ruling prints it with the
                         catalogue's own door beside it. The door sits on the number's empty right
                         side instead of under it, so the card reads figure-first. -->
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-mono text-[26px] font-extrabold leading-[1.2] text-ink-strong">{{ group(props.counts.catalogueTrainees) }}</p>
                        <Link href="/catalog" class="inline-flex min-h-11 shrink-0 items-center underline underline-offset-2 hover:decoration-2">Browse catalog</Link>
                    </div>
                </section>

                <section class="card" aria-labelledby="kpi-skills-heading" data-card="kpi_skills">
                    <h2 id="kpi-skills-heading" class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Skills</h2>
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-mono text-[26px] font-extrabold leading-[1.2] text-ink-strong">{{ group(props.counts.skills) }}</p>
                        <Link href="/skills" class="inline-flex min-h-11 shrink-0 items-center underline underline-offset-2 hover:decoration-2">Skill search</Link>
                    </div>
                </section>

                <section class="card" aria-labelledby="kpi-support-heading" data-card="kpi_support">
                    <h2 id="kpi-support-heading" class="text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Support cards</h2>
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-mono text-[26px] font-extrabold leading-[1.2] text-ink-strong">{{ group(props.counts.supportCards) }}</p>
                        <Link href="/support-cards" class="inline-flex min-h-11 shrink-0 items-center underline underline-offset-2 hover:decoration-2">Browse cards</Link>
                    </div>
                </section>
            </div>

            <!-- ROW TWO: Recent veterans | Quick actions (index.html:156-186) -->
            <div class="grid gap-3.5 g-side" data-dashboard-row="veterans">
                <section
                    v-show="!isCardHidden('recent_veterans')"
                    role="region"
                    aria-labelledby="recent-veterans-heading"
                    class="card"
                    data-card="recent_veterans"
                >
                    <h2 id="recent-veterans-heading">Recent veterans</h2>

                    <div v-if="props.recentVeterans.rows.length" class="overflow-x-auto">
                        <table class="w-full border-collapse text-[12.5px]">
                            <thead>
                                <tr>
                                    <th class="border-b border-rule-soft px-[9px] py-[7px] text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Trainee</th>
                                    <th class="border-b border-rule-soft px-[9px] py-[7px] text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Scenario</th>
                                    <th class="border-b border-rule-soft px-[9px] py-[7px] text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Saved</th>
                                    <th class="border-b border-rule-soft px-[9px] py-[7px] text-right text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted"><span class="sr-only">Open the veteran</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="veteran in props.recentVeterans.rows" :key="veteran.run_id" class="hover:bg-raised">
                                    <td class="border-b border-rule-soft px-[9px] py-[7px] align-top">
                                        <span class="flex items-center gap-[9px]">
                                            <!-- A veteran saved without a mirror file reserves its cell and paints nothing: absence is the normal state (DESIGN.md §4.7). -->
                                            <ArtworkSlot :url="veteran.artworkURL" alt="" size="size-10" reserve />
                                            <span class="min-w-0">
                                                <b class="font-extrabold text-ink-strong">{{ veteran.trainee }}</b>
                                                <span v-if="veteran.trainee_ja" class="ml-1 text-ink-muted">{{ veteran.trainee_ja }}</span>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="border-b border-rule-soft px-[9px] py-[7px] align-top">{{ veteran.scenario_label }}</td>
                                    <td class="border-b border-rule-soft px-[9px] py-[7px] align-top font-mono">
                                        <AbsenceValue v-if="!veteran.saved_date" compact reason="No save date was recorded for this veteran." />
                                        <template v-else>{{ veteran.saved_date }}</template>
                                    </td>
                                    <td class="border-b border-rule-soft px-[9px] py-[7px] text-right align-top">
                                        <LinkButton :href="veteran.run_url" variant="secondary" size="sm">Open</LinkButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- The footer is one line in the wireframe (index.html:170-173): how many the
                         library holds, how many of them this card is showing, and the door to the
                         rest. The count on the left is the payload's own total, so it cannot drift
                         from the library it links to. -->
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[12px] text-ink-muted">
                            {{ group(props.recentVeterans.total) }} veteran{{ props.recentVeterans.total === 1 ? '' : 's' }}
                            &middot; {{ veteransShown }} shown
                        </span>
                        <LinkButton :href="props.recentVeterans.library_url" variant="secondary" size="sm">All veterans</LinkButton>
                    </div>
                </section>

                <section
                    v-show="!isCardHidden('quick_actions')"
                    role="region"
                    aria-labelledby="quick-actions-heading"
                    class="card"
                    data-card="quick_actions"
                >
                    <h2 id="quick-actions-heading">Quick actions</h2>
                    <!-- Four outline buttons, full width, label centred (index.html:178-183). The
                         wireframe draws no marks here; the rail already carries one per destination,
                         and a second icon set on one card would be a second vocabulary for the same
                         destinations. -->
                    <nav aria-label="Quick actions">
                        <ul class="flex flex-col gap-2">
                            <li v-for="action in props.quickActions" :key="action.to">
                                <LinkButton :href="action.to" variant="secondary" class="w-full">{{ action.label }}</LinkButton>
                            </li>
                        </ul>
                    </nav>
                    <details class="mt-3">
                        <summary class="cursor-pointer list-none text-[12.5px] font-semibold text-ink-strong">
                            Why four destinations
                        </summary>
                        <p class="mt-1.5 text-[12.5px] text-ink-muted">
                            The running app's sidebar carries ten flat items; this dashboard is the same
                            set, grouped, with one primary action.
                        </p>
                    </details>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
