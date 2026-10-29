<script setup lang="ts">
/*
 * The Career Cockpit, `SCR-CAR-011` (SCREEN-009, plan §8 D8). The screen a Trainer reads on every turn.
 *
 * **Structure.** The four scenario variants share one region order, ported from the HFW set's
 * `cockpit-*` pages at shell density: the page head carries the trainee, the position and the run's
 * one primary action; the body is two columns, stats and the scenario on the left, the advisor and
 * "Where to go" on the right; the run's settings and record surfaces follow below. The old three-column
 * cockpit (timeline / decision / advisor) left with the HFW parity slice: the timelines are their own
 * screen, and the cockpit's job is the turn's decision plus the scenario it belongs to.
 *
 * **One decision, stated once.** The advisor's rail prints the recommendation, its reason and its cost
 * in one block (`NextDecision.vue`), and the page head repeats only the ACTION as the page's single
 * primary, which is HFW annotation 1: a Trainer can act without scrolling. The recommendation itself is
 * never restated in a second sentence.
 *
 * **The finale lives in the scenario card.** `advisor.finale` is the advisor's proximity read and
 * `scenario.finale_state` is the run's own record; both are printed beside the scenario panels rather
 * than only below the fold, which is the Goal-Gradient failure the audit filed (HFW annotation 2).
 *
 * **DM-1: 72 turns, finale outside them.** Every position line prints `N of 72` from
 * `header.total_turns` (the calendar's own constant, never a literal). A stored turn past 72 is the
 * finale phase the client numbers on its own, so it renders as `Finale phase`, never as turn 73+.
 *
 * **The one query-string state** is `?edit_turn=<id>`, which names the turn the correction disclosure
 * opens on (plan §9.6 ruling 2); the selector navigates it. There is one form family, the manual
 * correction, and it posts to the route that already owns a turn write.
 *
 * **Loading and error are page-level** (ADR-0007): the only user-initiated async action here is the
 * correction, and Inertia keeps the current page on screen during a visit, so the state is announced
 * rather than drawn as a skeleton. `invalid` and `exception` are both cancelable, and returning
 * `false` takes the failure out of Inertia's default modal so this page's own `role="alert"` is the
 * single surface.
 *
 * The props contract is declared locally: `defineProps<Imported>()` cannot resolve an imported type,
 * because TypeScript 7 ships no `lib/typescript.js` for `@vue/compiler-sfc` to load (plan §11). Every
 * page declares its own contract for the same reason.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
/*
 * `StatSummary` is no longer imported: the parity pass cut the summary strip, because no cockpit HFW
 * draws a second reading of the header's facts above the canvas. Its own copy defect went with it —
 * on a completed run it printed "No recommendation. See the advisor." while the advisor was
 * correctly absent. The component file is left in place for the Careers list to adopt or for
 * deletion in a follow-up; nothing in this tree renders it.
 */
import CareerStatePanel from '../../components/career/CareerStatePanel.vue';
import ActionGrid from '../../components/career/ActionGrid.vue';
import AdvisorRail from '../../components/career/AdvisorRail.vue';
import Section from '../../components/ui/Section.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import RunRaceStrip from '../../components/career/RunRaceStrip.vue';
import ScenarioPanel from '../../components/scenario/ScenarioPanel.vue';
import UnityCupEditor from '../../components/career/UnityCupEditor.vue';
import BaseInput from '@/Components/Form/BaseInput.vue';
import BaseSelect from '@/Components/Form/BaseSelect.vue';
import BaseRadio from '@/Components/Form/BaseRadio.vue';
import { LinkButton } from '@/Components/Form';
import { Head, router, useForm } from '@inertiajs/vue3';
import { TURN_ENTRY_ENERGY_BANDS as energyBands, TURN_ENTRY_ENERGY_STATES as energyStates, TURN_ENTRY_STAT_FIELDS as statFields } from '../../domain/turnEntryFields';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface Header {
    scenario_label: string;
    scenario_declared: boolean;
    year_label: string | null;
    month_label: string | null;
    turn: number;
    /**
     * Slice 5 step 2.6: the two figures the known turn-count defect separates. `logged_turns` is how
     * many recorded turn rows the log holds, and `next_turn_to_play` is where the model says the next
     * decision sits. `turn` above is the position the header prints; when it disagrees with the log's
     * own count the page prints both, because the divergence is a labelled defect rather than a number
     * the view is allowed to pick.
     */
    logged_turns: number;
    next_turn_to_play: number | null;
    /**
     * DM-1: the career's own length, `CareerCalendar::TURNS_PER_CAREER`. Strictly 72; the finale block
     * sits outside it, so a stored turn past this figure is the finale phase rather than turn 73+.
     */
    total_turns: number;
    energy: number | null;
    energy_text: string | null;
    mood: string | null;
    fans: number | null;
    skill_points: number | null;
    widgets: string[];
    values: Record<string, number | string | null>;
}

export interface GradeObjective {
    index: number;
    name: string;
    required: number;
}

interface Correction {
    action: string;
    destroy_action: string;
    turn: number;
    selected_turn_id: number;
    turns: { id: number; turn: number }[];
    values: {
        speed: number;
        stamina: number;
        power: number;
        guts: number;
        wit: number;
        sp: number | null;
        energy: number | null;
        /**
         * Energy's own state and, when the state is `band`, the coarse word the client showed. They
         * arrive separately because a correction must be able to say "not recorded" and have the
         * previous band cleared, which a single number cannot express (R2-16).
         */
        energy_state: string | null;
        energy_band: string | null;
        fans: number | null;
        mood: string | null;
    };
}

/**
 * This run's own finale record, as `FinaleReader::forRun()` carries it: whether the block has arrived,
 * what was recorded against it, and the scenario's own label for it. Null when the catalogue holds no
 * finale row for the scenario.
 */
interface FinaleState {
    reached: boolean;
    outcome: string | null;
    placement: string | null;
    turn: number | null;
    label: string;
}

/**
 * Our Grand Concert's Performance reading. `enabled` is the matrix's `performance_input` flag read
 * server-side; the other three scenarios answer `false` with the same key set, so the card draws from
 * one flag and never from a scenario name (D-240, G-33). `observations` are the turn events this run
 * recorded, one per reading: no total is derived because no source publishes a starting value.
 */
interface Performance {
    enabled: boolean;
    types: string[];
    observations: { type: string; turn: number; delta: number }[];
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; umamusume_id: number; scenario: string | null; status: string; status_label: string; scenario_label: string; run_url: string; index_url: string; timeline_url: string; result_url: string; training_url: string; deck_url: string; status_labels: Record<string, string>; scenarios: Record<string, string>; update_url: string; destroy_url: string; export_csv_url: string; export_json_url: string; current_objective_index: number | null };
    header: Header;
    /**
     * Where the career stands, as `CareerPosition::toArray()` carries it. The header's Year, Month and
     * Turn are resolved from it server-side when `hasImportedPosition` is true, because the month and
     * year spellings already live in `CareerCalendar` and `CareerYear::label()`; spelling them again
     * here would be a second place for the calendar to be wrong (ADR-0015).
     */
    careerPosition: {
        year: number;
        month: number;
        phase: string;
        turn_index: number;
        scenario_countdown: number | null;
        field_states: Record<string, string> | null;
        field_values: Record<string, number> | null;
    } | null;
    hasImportedPosition: boolean;
    scenarioCountdown: number | null;
    gradeObjectives: GradeObjective[];
    /*
     * The run's own identity, as the Trainer entered it (A6.1 to A6.3). Each field is null until a
     * writer records it, so a block with nothing to say renders the absence rather than a zero. The
     * fan ladder is read server-side through `FanLadder`, so the class and the gap are one band's
     * answer; `growth_rate` is keyed by the stat matrix, the same keys `state.stats` carries.
     */
    identity: {
        trainee_rarity: number | null;
        potential_level: number | null;
        card_title: string | null;
        growth_rate: Record<string, number | null> | null;
        stat_order: string[];
        /**
         * The four stats whose ceiling a Trainer may enter, each with what the run holds and what the
         * scenario publishes (`ADR-0025`). Wit is absent: no column holds it, so no field may write one.
         */
        caps: Record<string, { entered: number | null; scenario: number }>;
        fans: number | null;
        fan_ladder: { class: string | null; nextThreshold: number | null; gap: number | null } | null;
    };
    /**
     * A-0.1: the cap arithmetic's recorded inputs (§1.2). The caps the run states, resolved the same
     * way the stat strip resolves them, beside the blue sparks the run's Legacy tree carries. The
     * source records that the two do not reconcile, so this is a read-out of inputs, not a derivation.
     */
    capComposition: {
        caps: Record<string, number>;
        sparks: { stat: string | null; stars: number | null; source: string }[];
        note: string;
    };
    /**
     * A-0.6: the derived team roster — the six deck cards' characters. A read-out, no stored roster;
     * `empty` names the absence when no deck card is equipped.
     */
    roster: {
        rows: { slot: number; name: string | null; friend: boolean; ownership: string | null }[];
        empty: string | null;
    };
    state: {
        stats: { key: string; label: string; current: number | null; target: number | null; cap: number }[];
        meta: { key: string; label: string; value: number | string | null }[];
    };
    /** A-0.11: the client's Mood Effect headline (great tier: +20% training, +4% running). */
    moodEffect: { training: number; running: number };
    actions: { key: string; label: string; href: string; recommended: boolean; note: string }[];
    // The decision's own shape is declared at `components/career/NextDecision.vue`, the one place that
    // reads it, and forwarded untouched the way the scenario section is (FD-2, plan §9a). `finale` stays
    // a sibling of the contract rather than a seventh key inside it.
    advisor: {
        decision: Record<string, unknown>;
        finale: { label: string; state: string; turns_away: number | null } | null;
    };
    // The strip's own shape is declared at `components/career/RunRaceStrip.vue`, the one place that
    // reads it. The page forwards the bundle untouched, the way it forwarded the calendar cells.
    raceStrip: Record<string, unknown>;
    // The scenario panel's shape is declared at `components/scenario/ScenarioPanel.vue` for the same
    // reason: the shell is its one reader, and E2 to E4 register renderers against it.
    scenario: Record<string, unknown>;
    /** Our Grand Concert's Performance reading; `enabled` is false for every other scenario. */
    performance: Performance;
    correction: Correction | null;
}>();

const { visiting, visitFailed } = useVisitState();

const empty = computed(() => props.header.turn === 0);

// The door an objective can be settled at, read off the grid's own entries rather than from a second
// route table. The grid already owns the href, and a due obligation with no door renders none.
const raceDoor = computed(() => props.actions.find((action) => action.key === 'race')?.href ?? null);

/**
 * DM-1: one phrase for the run's position, from the calendar's own denominator. A run with no logged
 * turn claims no number; a stored turn past 72 is the finale phase, which the client numbers on its
 * own and which is not a career turn.
 */
const turnPhrase = computed((): string => {
    if (props.header.turn <= 0) {
        return 'No turn recorded';
    }

    if (props.header.turn > props.header.total_turns) {
        return 'Finale phase';
    }

    return `Turn ${props.header.turn} of ${props.header.total_turns}`;
});

/**
 * Slice 5 step 2.6: the labelled-defect line. The header prints the run's position; the log holds a
 * number of recorded turns. For a played run they agree, and nothing is printed. For an imported run
 * whose position is ahead of its rows they disagree, and both figures print rather than one standing
 * for the other — the Dashboard already labels the same pair (`position_turn` beside `turn`), and the
 * cockpit must match that shape so a Trainer reading either screen is told the same thing.
 */
const turnDisagreement = computed((): string | null => {
    if (props.header.logged_turns === props.header.turn) {
        return null;
    }

    const logged = props.header.logged_turns;
    const plural = logged === 1 ? 'turn' : 'turns';

    return `The position reads turn ${props.header.turn}; the turn log holds ${logged} recorded ${plural}. `
        + 'The two disagree, so both are printed.';
});

/** This run's finale record, or null for a scenario the catalogue gives no finale row. */
const finaleState = computed((): FinaleState | null => (props.scenario as { finale_state?: FinaleState | null }).finale_state ?? null);

/**
 * The countdown the scenario card prints, when one exists. Two sources, one fact: the run's own finale
 * record says whether the block has arrived, and the advisor's proximity read is the only source that
 * dates it (the catalogue row carries no career turn for the finale block). A reached finale has no
 * countdown, and neither does one with no proximity inside the advisor's lookahead window.
 */
const finaleTurnsAway = computed((): number | null => {
    const advice = props.advisor.finale;
    const state = finaleState.value;

    if (advice === null || advice.turns_away === null) {
        return null;
    }

    if (state !== null && state.reached) {
        return null;
    }

    return advice.turns_away;
});

/**
 * The finale, in one line, for the scenario card and the page head. Three states, the same three the
 * Career Result prints: not yet reached, reached with nothing recorded, and recorded. `null` means the
 * catalogue holds no finale row at all, which is an absence the card prints rather than a blank.
 */
const finaleLine = computed((): string | null => {
    const state = finaleState.value;
    const advice = props.advisor.finale;

    if (state === null) {
        return advice === null ? null : `${advice.label}: ${advice.state}`;
    }

    if (state.reached) {
        return state.outcome === null
            ? `${state.label}: reached; outcome not recorded`
            : `${state.label}: ${[state.outcome, state.placement].filter((part): part is string => part !== null).join(', ')}`;
    }

    const away = finaleTurnsAway.value;

    return away === null
        ? `${state.label}: not yet reached`
        : `${state.label}: ${away} ${away === 1 ? 'turn' : 'turns'} away`;
});

/**
 * The closed run's own outcome, first: a finished career says what it achieved before anything else
 * (HFW annotation 5 on `cockpit-ura-finale.html`). A Retired run names the turn it stopped at, the
 * same spelling the careers list uses.
 */
const closedOutcome = computed((): string => {
    if (props.run.status !== 'Completed') {
        return props.header.turn > 0 ? `Retired at ${turnPhrase.value}` : 'Retired';
    }

    const state = finaleState.value;

    if (state === null || !state.reached || state.outcome === null) {
        return 'Finale outcome not recorded';
    }

    return `${state.label}: ${[state.outcome, state.placement].filter((part): part is string => part !== null).join(', ')}`;
});

/**
 * The page-head line. Position and scenario, with the closed run's outcome in front of them: the one
 * fact a finished career owes first. Identity stars and the card form stay on the identity card so
 * one figure is not printed three times on one screen.
 */
const lede = computed((): string => {
    const parts: string[] = [];

    if (props.run.status !== 'Active') {
        parts.push(closedOutcome.value);
    }

    if (props.header.year_label !== null) {
        parts.push(props.header.year_label);
    }

    if (props.header.month_label !== null) {
        parts.push(props.header.month_label);
    }

    if (props.header.turn > 0 && props.header.turn <= props.header.total_turns) {
        parts.push(turnPhrase.value);
    }

    parts.push(props.run.scenario_label);

    return parts.join(' · ');
});

interface RecommendedAction {
    action: string;
    action_url: string;
}

/** The advisor's recommendation, as its own words and its own door. */
const recommendation = computed((): RecommendedAction | null => {
    const value = (props.advisor.decision as { recommended?: RecommendedAction | null }).recommended;

    return value !== null && value !== undefined && typeof value.action === 'string' && typeof value.action_url === 'string'
        ? value
        : null;
});

/**
 * The page head's single primary action, which is the same action the advisor's rail states once
 * (HFW annotation 1). A fresh career has nothing to recommend and a retired one has no result to open,
 * so both render no primary at all rather than an invented one.
 */
const headAction = computed((): { label: string; href: string } | null => {
    if (isActive.value) {
        const value = recommendation.value;

        if (value === null) {
            return null;
        }

        return {
            label: value.action === 'Rest' ? 'Rest this turn' : `Train ${value.action} this turn`,
            href: value.action_url,
        };
    }

    return props.run.status === 'Completed'
        ? { label: 'Career result', href: props.run.result_url }
        : null;
});

/**
 * The scenario table's four team readings. They are computed rather than printed from the panel so
 * the card's surface matches `cockpit-unity-cup.html`, which draws them as table rows and nowhere
 * else. Each value is absent rather than zero when nothing recorded it.
 */
const teamLine = computed((): string => {
    const team = teamState.value;

    if (team === null) {
        return 'N/A, not recorded';
    }

    const parts = [team.team_name, team.team_motto].filter((part): part is string => part !== null && part !== '');

    return parts.length === 0 ? 'N/A, not recorded' : parts.join(' \u00b7 ');
});

const teamLeague = computed((): string => (teamState.value?.team_league_placement === null || teamState.value === null
    ? 'N/A'
    : String(teamState.value.team_league_placement)));

/**
 * The HFW prints `11 (inside 10-12)`: the combined Spirit and Extreme count, named against the band
 * the matrix publishes. Both halves are separate columns, so the total is their sum and never a
 * stored figure; a run with neither records the absence.
 */
const teamBursts = computed((): string => {
    const team = teamState.value;

    if (team === null || (team.spirit_bursts_count === null && team.extreme_bursts_count === null)) {
        return 'N/A';
    }

    const total = (team.spirit_bursts_count ?? 0) + (team.extreme_bursts_count ?? 0);

    return total >= 10 && total <= 12 ? `${total} (inside 10-12)` : String(total);
});

const teamRank = computed((): string => 'not recorded');

/** The Skills planner's URL, derived the way `CareerSectionNav` derives its steps. */
const skillsUrl = computed((): string => `${props.run.run_url.replace(/\/cockpit$/, '')}/skills`);

/**
 * "Where to go": the screens a career's turn leads into, from the doors this page already carries.
 * Every href is a prop or an action-grid entry, so no destination is spelled here and none can drift
 * from the route table. HFW parity for all four variants; Unity Cup's own keybox names it.
 */
const whereToGo = computed((): { label: string; href: string }[] => {
    const links: { label: string; href: string }[] = [{ label: 'Training decision', href: props.run.training_url }];
    const race = props.actions.find((action) => action.key === 'race');

    if (race !== undefined) {
        links.push({ label: 'Race decision', href: race.href });
    }

    links.push({ label: 'Skills planner', href: skillsUrl.value });
    links.push({ label: 'Run deck', href: props.run.deck_url });
    links.push({ label: 'Timeline', href: props.run.timeline_url });
    links.push({ label: 'Career result', href: props.run.result_url });

    return links;
});

/**
 * The completed card's Races tile. The URA HFW prints "4 of 4", a recorded-of-mandatory
 * ratio; the strip carries the recorded rows and no mandatory total, so the tile prints
 * the count it does have rather than a denominator it would be inventing.
 * HFW-OVERRIDE (text: races-denominator).
 */
const raceCount = computed((): number | null => {
    const run = (props.raceStrip as { run?: unknown[] }).run;

    return Array.isArray(run) ? run.length : null;
});

/** The five Performance types with this run's latest recorded reading for each, or null. */
const performanceRows = computed((): { type: string; latest: { turn: number; delta: number } | null }[] =>
    props.performance.types.map((type: string) => {
        const readings = props.performance.observations.filter((observation) => observation.type === type);
        const latest = readings.length === 0 ? undefined : readings[readings.length - 1];

        return { type, latest: latest === undefined ? null : { turn: latest.turn, delta: latest.delta } };
    }),
);

/**
 * The concert loop, in the order the sourced guide states it (`docs/scenarios/07-grand-concert.md`
 * §"The loop, in order", read 2026-10-05). The numbers a guide does not publish are absent from this
 * copy: it explains the shape of the loop, which is what a Trainer plans against, and claims no
 * per-turn amount or cost curve.
 */
const concertLoop = [
    { term: 'Lessons', detail: 'Open at turn 5, in the Lesson menu between the outing and race buttons.' },
    { term: 'Spend', detail: 'Performance buys Live Techniques and Songs.' },
    { term: 'Gauge', detail: 'Three Songs fill Live Performance Expectations, and a filled gauge guarantees Great Success.' },
    { term: 'Concerts', detail: 'Four Promo Concerts, then the Grand Concert, every six months from late December of Junior Year.' },
    { term: 'Payout', detail: '5 skill points per Technique Lesson and 25 per Song Lesson since the previous live; a live also raises the Performance cap by 50.' },
];

// A recorded reading is signed, so `+0` and `-0` never read as a missing value.
const signed = (value: number): string => (value >= 0 ? `+${value}` : String(value));

/**
 * A-0.1: the blue sparks the tree carries, one phrase each (`Power ★ (Parent A)`), or an explicit
 * absence when the run records no Legacy. The caps are printed as their own grid above it.
 */
const capSparkText = computed((): string => {
    if (props.capComposition.sparks.length === 0) {
        return "No blue spark is recorded in this run's Legacy.";
    }

    return props.capComposition.sparks
        .map((spark) => `${spark.stat ?? 'Unstated'} ${'★'.repeat(spark.stars ?? 0)} (${spark.source})`)
        .join(', ');
});

/*
 * The Unity Cup team editor (R2-14). The team system is the scenario's own, so the matrix's
 * `team_rank_ladder` panel flag decides whether the editor renders: a scenario name written into this
 * page would be the defect AGENTS.md §7 forbids. The payload's key set is uniform across scenarios, so
 * the flag and not the payload's presence is what gates the control.
 *
 * The shape is declared here rather than imported from the editor, because a `.vue` file's prop type
 * cannot be imported; the two are structurally identical by construction.
 */
interface TeamState {
    team_name: string | null;
    team_motto: string | null;
    team_league_placement: number | null;
    team_preseason_wins: number | null;
    unity_trainings_count: number | null;
    spirit_bursts_count: number | null;
    extreme_bursts_count: number | null;
}

const teamState = computed((): TeamState | null => (props.scenario as { team?: TeamState }).team ?? null);

const composesTeamPanel = computed(
    (): boolean =>
        (props.scenario as { panels?: Record<string, { on?: boolean }> }).panels?.team_rank_ladder?.on === true,
);

/*
 * The manual correction (design-2.0 §38). One disclosure holding every field of the latest turn, put
 * to the route and Form Request that already own a turn write; no second endpoint is created and no
 * field the request does not accept is offered. The five stats are required by that request, so they
 * are prefilled from the stored row and marked required here rather than silently defaulted.
 */
const correctionForm = useForm({
    turn: props.correction === null ? '' : String(props.correction.turn),
    speed: props.correction === null ? '' : String(props.correction.values.speed),
    stamina: props.correction === null ? '' : String(props.correction.values.stamina),
    power: props.correction === null ? '' : String(props.correction.values.power),
    guts: props.correction === null ? '' : String(props.correction.values.guts),
    wit: props.correction === null ? '' : String(props.correction.values.wit),
    sp: props.correction?.values.sp === null || props.correction === null ? '' : String(props.correction.values.sp),
    energy: props.correction?.values.energy === null || props.correction === null ? '' : String(props.correction.values.energy),
    energy_state: props.correction?.values.energy_state ?? 'unknown',
    energy_band: props.correction?.values.energy_band ?? '',
    fans: props.correction?.values.fans === null || props.correction === null ? '' : String(props.correction.values.fans),
    mood: props.correction?.values.mood ?? '',
});

/*
 * The band travels with the state that names it, the rule `TrainingDetail` already applies on the
 * guided form: a state of `exact` keeps no band, and any state but `band` clears it. Without this a
 * correction that switches the state to `not recorded` would leave the old band on the row and the
 * next reader would take a word for a reading nobody made (R2-16).
 */
watch(
    () => correctionForm.energy_state,
    (state: string) => {
        if (state !== 'exact') {
            correctionForm.energy = '';
        }

        if (state !== 'band') {
            correctionForm.energy_band = '';
        }
    },
);

const alert = ref<HTMLElement | null>(null);

// A refused write comes back with the field's message. The alert is focused rather than left for the
// Trainer to find, because the refusal is the whole answer to the press (WCAG 2.2 SC 3.3.1).
watch(
    () => correctionForm.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }

        await nextTick();
        alert.value?.focus();
    },
);

const turnSelector = ref<HTMLSelectElement | null>(null);

/*
 * The three run states. The Cockpit is a decision surface while a career is being played, and a summary
 * of one that has ended once it has not, so the top of the screen switches on `run.status` and names the
 * state in the DOM for the specs to read. `Retired` keeps the Completed shape and loses the Result door,
 * because a retired career has no finished result to open.
 */
const runState = computed((): string => props.run.status.toLowerCase());
const isActive = computed((): boolean => props.run.status === 'Active');


/*
 * F2, plan §9.6 ruling 2: arbitrary-turn correction. `?edit_turn=<id>` names the turn the form opens
 * on; the selector navigates that query string with `preserveState` so the disclosure stays open, and
 * the watch below re-syncs the form to the turn the server returned.
 */
function switchTurn(id: string): void {
    router.get(window.location.pathname, { edit_turn: id }, { preserveState: true, preserveScroll: true });
}

watch(
    () => props.correction,
    (correction) => {
        if (correction === null) {
            return;
        }

        correctionForm.turn = String(correction.turn);
        correctionForm.speed = String(correction.values.speed);
        correctionForm.stamina = String(correction.values.stamina);
        correctionForm.power = String(correction.values.power);
        correctionForm.guts = String(correction.values.guts);
        correctionForm.wit = String(correction.values.wit);
        correctionForm.sp = correction.values.sp === null ? '' : String(correction.values.sp);
        correctionForm.energy = correction.values.energy === null ? '' : String(correction.values.energy);
        correctionForm.energy_state = correction.values.energy_state ?? 'unknown';
        correctionForm.energy_band = correction.values.energy_band ?? '';
        correctionForm.fans = correction.values.fans === null ? '' : String(correction.values.fans);
        correctionForm.mood = correction.values.mood ?? '';
        correctionForm.clearErrors();
    },
);

const saveCorrection = (): void => {
    if (props.correction === null) {
        return;
    }

    correctionForm.put(props.correction.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => turnSelector.value?.focus());
        },
    });
};

// Delete one turn and its failure event (the controller's own transaction). A separate form because
// the two writes are separate routes; the disclosure gates it the way the run-delete door does.
const turnDeleteForm = useForm({});

const deleteTurn = (): void => {
    if (props.correction === null) {
        return;
    }

    turnDeleteForm.delete(props.correction.destroy_action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => turnSelector.value?.focus());
        },
    });
};

/*
 * The header edit forms (F2, plan §9.6 ruling 1). All three POST through `runs.update`, which shares
 * StoreTrainingRunRequest with create and so writes every field it is handed (the request fills the
 * columns it does not receive). Each form therefore carries the run's current scenario, status and
 * objective-index as hidden fields so a partial edit — status only, scenario only, period only —
 * cannot blank the sibling fields the form did not touch.
 *
 * The status form re-posts umamusume_id because the request treats it as required on store; a run
 * that already exists never changes its trainee here, so carrying the stored id is the honest value
 * rather than inventing a default.
 */
const statusForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    scenario: props.run.scenario ?? '',
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
    status: props.run.status,
});

const scenarioForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    status: props.run.status,
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
    scenario: props.run.scenario ?? '',
});

const periodForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    status: props.run.status,
    scenario: props.run.scenario ?? '',
    current_objective_index: props.run.current_objective_index === null ? '' : String(props.run.current_objective_index),
});

const saveStatus = (): void => {
    statusForm.put(props.run.update_url, { preserveScroll: true });
};

const saveScenario = (): void => {
    scenarioForm.put(props.run.update_url, { preserveScroll: true });
};

const savePeriod = (): void => {
    periodForm.put(props.run.update_url, { preserveScroll: true });
};

/*
 * The identity write (R2-13). The three forms above each carry the whole key set the request demands,
 * because `umamusume_id` and `status` are required; this one does the same and adds the three facts the
 * header already printed but nothing could enter. `growth_rate` is keyed by the stat matrix, so the
 * request refuses a sixth stat rather than storing one (`StoreTrainingRunRequest`).
 */
/** The request key that holds one stat's entered ceiling. The stats themselves come from `identity.caps`. */
const capColumn = (stat: string): string => `cap_${stat.toLowerCase()}`;

const identityForm = useForm({
    umamusume_id: String(props.run.umamusume_id ?? ''),
    status: props.run.status,
    trainee_rarity: props.identity.trainee_rarity === null ? '' : String(props.identity.trainee_rarity),
    potential_level: props.identity.potential_level === null ? '' : String(props.identity.potential_level),
    // The four entered ceilings, prefilled with what the run holds. An empty field is a clear rather than
    // a no-op, which is why they ride this form's key set at all (`ADR-0025`). The stats come from the
    // server's map, so this page names none of them.
    ...Object.fromEntries(
        Object.entries(props.identity.caps).map(([stat, ceiling]) => [
            capColumn(stat),
            ceiling.entered === null ? '' : String(ceiling.entered),
        ]),
    ) as Record<string, string>,
    growth_rate: Object.fromEntries(
        props.identity.stat_order.map((stat) => {
            const value = props.identity.growth_rate?.[stat];

            return [stat, value === null || value === undefined ? '' : String(value)];
        }),
    ) as Record<string, string>,
});

const saveIdentity = (): void => {
    identityForm
        // An empty field is "not stated" and `0` is a real figure, so the two are kept apart here
        // rather than collapsed by a `Number('')` on the way out (D-220). A growth row with nothing
        // stated is `null` rather than five nulls: the request's own contract is that a run which
        // stated no growth stores no row.
        .transform((data) => {
            const growth = Object.fromEntries(
                Object.entries(data.growth_rate).map(([stat, value]) => [stat, value === '' ? null : Number(value)]),
            );

            // A ceiling field with nothing in it is a clear, which is the statement "use the scenario's
            // own bound" — never a zero the client never showed. The four keys are the request's column
            // names, so they post flat beside the identity fields.
            const ceilings = Object.fromEntries(
                Object.keys(props.identity.caps).map((stat) => {
                    const column = capColumn(stat);
                    const value = data[column];

                    return [column, value === '' || value === undefined ? null : Number(value)];
                }),
            );

            return {
                ...data,
                ...ceilings,
                trainee_rarity: data.trainee_rarity === '' ? null : Number(data.trainee_rarity),
                potential_level: data.potential_level === '' ? null : Number(data.potential_level),
                growth_rate: Object.values(growth).every((value) => value === null) ? null : growth,
            };
        })
        .put(props.run.update_url, { preserveScroll: true });
};

const deleteForm = useForm({});

const deleteRun = (): void => {
    if (props.run.destroy_url === '') {
        return;
    }

    deleteForm.delete(props.run.destroy_url);
};

// E4: the Cockpit renders a "Shop" jump only when the matrix composes one. The shape is the
// same across scenarios (the key is always present), so a `null` check carries the whole
// branch. Other scenarios see no Shop affordance.
const hasShop = computed((): boolean => {
    const shop = (props.scenario as { shop?: unknown }).shop;

    return shop !== null && shop !== undefined;
});

/*
 * Scroll to and focus the Shop heading (§28 "quick access from the Cockpit"). We do not rely
 * on `:focus-visible` scroll-margin because the heading carries `tabindex="-1"` and would
 * otherwise miss the visible scroll target.
 */
const jumpToShop = (): void => {
    const heading = document.getElementById('trackblazer-shop-heading');

    if (heading === null) {
        return;
    }

    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
    heading.focus({ preventScroll: true });
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="props.header.turn === 0 ? null : turnPhrase"
        :run-url="props.run.run_url"
        current="cockpit"
    >
        <Head :title="`Career: ${props.run.trainee}`" />

        <!-- The HFW page head: the small caps eyebrow, the trainee as the one `h1`, the position and
             outcome as the lede, and the run's single primary action. It renders in the shell's
             `role="banner"` region through `#head`, so the shell's own fallback title band is not
             drawn and the document keeps exactly one heading of each level. -->
        <template #head>
            <PageHeader eyebrow="Cockpit" :title="props.run.trainee" :description="lede">
                <template #actions>
                    <LinkButton :href="props.run.index_url" variant="secondary" size="sm">
                        All careers
                    </LinkButton>
                    <!-- The same action the advisor states once, never a second recommendation. A
                         fresh career recommends nothing and a retired one has no result, so both
                         render no filled action at all. -->
                    <LinkButton v-if="headAction !== null" :href="headAction.href" variant="primary" size="sm">
                        {{ headAction.label }}
                    </LinkButton>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>


        <div :data-run-state="runState" class="contents">

        <!-- The empty state names what is missing, why it matters and what to do (plan §10). It sits
             above the grid rather than replacing it: the grid's own entries are still the doors, and
             the advisor's refusal is a real state rather than a blank. -->
        <Section
            v-if="empty"
            id="career-empty-heading"
            title="No turn recorded yet"
            class="rounded-lg border border-dashed border-rule bg-panel p-4"
        >
            <p class="mt-1 text-sm text-ink">
                Energy, Mood, Fans, Skill Points and the five stats are what this screen reads, and
                none has been entered for this run. Until one turn is recorded the advisor has
                nothing to rank, so it declines rather than guessing.
            </p>
            <a :href="props.run.training_url" class="mt-2 inline-flex min-h-11 items-center font-medium text-ink-strong underline">
                Record the first turn through Training
            </a>
        </Section>

        <!-- A closed run states its outcome first (HFW annotation 5 on the URA page): what the career
             achieved is the fact that matters once nothing is being decided. The advisor rail below
             collapses to the record, and no recommendation is invented for a career that has ended. -->
        <Section
            v-if="!isActive"
            id="cockpit-summary-heading"
            title="This run is complete."
            class="rounded-lg border border-rule bg-panel p-4"
        >
            <p class="mt-1 text-sm text-ink" data-region="closed-outcome">
                Nothing is being decided, so nothing is ranked and nothing is refused.
            </p>
            <ul class="tiles mt-3 grid-cols-2 @4xl:grid-cols-4">
                <li class="tile">
                    <p class="k">Finale</p>
                    <p v-if="finaleLine !== null" class="v" data-field="finale-outcome">{{ finaleLine }}</p>
                    <p v-else class="v na" data-field="finale-outcome">
                        N/A. This run's scenario has no finale row in the catalogue.
                    </p>
                </li>
                <li class="tile">
                    <p class="k">Turn</p>
                    <!-- DOMAIN OVERRIDE (DM-1): a completed run is past the 72-turn grid, so the
                         tile prints the stored turn alone, the way the HFW prints "Turn 75", and
                         not the finale-phase phrase the grid's denominator would force. -->
                    <p v-if="props.run.status === 'Completed'" class="v font-mono tabular-nums">{{ props.header.turn }}</p>
                    <p v-else class="v font-mono tabular-nums">{{ turnPhrase }}</p>
                    <p
                        v-if="turnDisagreement !== null"
                        class="mt-1 text-xs text-ink-muted"
                        data-field="turn-discrepancy"
                    >{{ turnDisagreement }}</p>
                </li>
                <li class="tile">
                    <p class="k">Races</p>
                    <div class="v font-mono tabular-nums">
                        <template v-if="raceCount !== null">{{ raceCount }}</template>
                        <AbsenceValue v-else compact reason="No race entry is recorded for this run." />
                    </div>
                </li>
                <li class="tile">
                    <p class="k">Skill points</p>
                    <div class="v font-mono tabular-nums">
                        <template v-if="props.header.skill_points !== null">{{ props.header.skill_points.toLocaleString('en-US') }}</template>
                        <AbsenceValue v-else compact reason="No turn entry for this run records Skill Points." />
                    </div>
                </li>
            </ul>
        </Section>

        <!-- The HFW's two-column body. Left: the run's stats, its scenario reading and the scenario's
             own panels. Right: the advisor, then the doors a career leads into. The single-column
             stack below `@4xl` keeps the reading order the tree already carries. -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:items-start @4xl:grid-cols-[2fr_1fr]">
            <div class="flex min-w-0 flex-col gap-4">
                <CareerStatePanel
                    :stats="props.state.stats"
                    :meta="props.state.meta"
                    :growth-rate="props.identity.growth_rate"
                    :fan-ladder="props.identity.fan_ladder"
                    :mood-effect="props.moodEffect"
                    denominator-label="cap"
                    :title="isActive ? 'Stats and state' : 'Final stats'"
                />

                <!-- Our Grand Concert's reading: five types, shared out of the turn-entry screen, and
                     the loop a Trainer plans the run against. Both draw from `performance.enabled`,
                     which is the matrix's own flag, so no scenario name reaches this page (G-33). -->
                <template v-if="props.performance.enabled">
                    <section aria-labelledby="career-performance-heading" class="card">
                        <h2 id="career-performance-heading" class="text-lg font-semibold text-ink-strong">Performance</h2>
                        <p class="sub">
                            Five types, earned from training. No starting value, per-turn amount or Lesson
                            cost is published, so no total is shown.
                        </p>
                        <ul class="tiles grid-cols-2 @3xl:grid-cols-3">
                            <li v-for="row in performanceRows" :key="row.type" class="tile">
                                <p class="k">{{ row.type }}</p>
                                <p v-if="row.latest !== null" class="v font-mono tabular-nums">
                                    {{ signed(row.latest.delta) }}
                                    <span class="text-xs font-normal text-ink-muted">at turn {{ row.latest.turn }}</span>
                                </p>
                                <p v-else class="v na">Not recorded</p>
                            </li>
                        </ul>
                        <p class="note mt-2">
                            <span class="g" aria-hidden="true">○</span>
                            <span>
                                The Performance field lives on the turn-entry screen, not in the cockpit. This
                                panel is a read, and a type no turn has recorded prints Not recorded rather than a
                                zero.
                            </span>
                        </p>
                    </section>

                    <section aria-labelledby="career-concert-loop-heading" class="card">
                        <h2 id="career-concert-loop-heading" class="text-lg font-semibold text-ink-strong">The concert loop</h2>
                        <dl class="mt-2 grid grid-cols-1 gap-x-4 gap-y-1 text-sm @3xl:grid-cols-3">
                            <template v-for="row in concertLoop" :key="row.term">
                                <dt class="font-semibold text-ink-strong">{{ row.term }}</dt>
                                <dd class="text-ink @3xl:col-span-2">{{ row.detail }}</dd>
                            </template>
                        </dl>
                    </section>
                </template>

                <Section
                    id="career-scenario-heading"
                    title="Scenario"
                    class="rounded-lg border border-rule bg-panel p-4"
                >
                    <!-- The finale countdown, in the scenario card rather than below the fold (HFW
                         annotation 2). A reached finale prints its record; an unreached one prints the
                         advisor's own proximity when it has one, and says so when nothing dates it.
                         No control lives here: the Unity Cup region pins that. -->
                    <!-- The card's surface is the HFW's: two pills and a label/value table. Every row
                         is a reading this page already carries, and none is invented for a scenario
                         that does not have it, so a scenario without the team system draws the finale
                         row alone and the boards below carry the rest. -->
                    <div class="mb-2 flex flex-wrap gap-x-2 gap-y-1">
                        <span class="pill pill-accent">{{ props.run.scenario_label }}</span>
                        <span class="pill pill-outline">Guide held</span>
                    </div>

                    <table class="t">
                        <tbody>
                            <tr data-region="scenario-finale">
                                <td>Finale</td>
                                <td>
                                    <template v-if="finaleLine !== null">
                                        <b v-if="finaleTurnsAway !== null">
                                            {{ finaleTurnsAway }} {{ finaleTurnsAway === 1 ? 'turn' : 'turns' }} away
                                        </b>
                                        <span v-else>{{ finaleLine }}</span>
                                    </template>
                                    <span v-else class="text-ink-muted">
                                        N/A. This run's scenario has no finale row in the catalogue.
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="composesTeamPanel">
                                <td>Team</td>
                                <td>{{ teamLine }}</td>
                            </tr>
                            <tr v-if="composesTeamPanel">
                                <td>League</td>
                                <td class="font-mono tabular-nums">{{ teamLeague }}</td>
                            </tr>
                            <tr v-if="composesTeamPanel">
                                <td>Bursts</td>
                                <td class="font-mono tabular-nums">{{ teamBursts }}</td>
                            </tr>
                            <tr v-if="composesTeamPanel">
                                <td>Team rank</td>
                                <td><span class="pill pill-neutral">{{ teamRank }}</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- HFW-OVERRIDE (scenario-extension): no cockpit HFW draws the boards this
                         scenario composes, and SCREEN_SPEC.md SCR-CAR-011 keeps them on this screen.
                         They stay reachable and closed, so the visible page is the wireframe's while
                         the panels it does not draw are one press away. The shell switches on the
                         matrix's own flags and widget keys and knows no scenario name (G-33). -->
                    <details data-region="scenario-detail" class="mt-3">
                        <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                            Scenario detail
                        </summary>
                        <div class="mt-2">
                            <ScenarioPanel :scenario="props.scenario as never" />
                        </div>
                    </details>

                    <!-- HFW OVERRIDE (copy, not visual) — `cockpit-trackblazer.html` prints a `.mockbar`
                         stating that its support data is illustrative. A wireframe artifact is not
                         shippable, so the same warning ships as a `.note` instead of the bar. The date is
                         the one the scenario matrix carries as its own verification stamp, never a date
                         this page chose; the wording avoids long unbreakable tokens because a note that
                         forces a 320px scroll would be a defect of its own. -->
                    <p
                        v-if="props.run.scenario === 'trackblazer'"
                        class="note mt-3 text-xs text-ink-muted"
                        data-field="support-data-note"
                    >
                        <b>Illustrative support data.</b> The Trackblazer figures on this screen are what
                        the scenario matrix records, and that matrix was verified on 2026-09-27. Anything
                        the matrix does not carry prints as an absence rather than an estimate.
                    </p>

                    <!-- The same override for Our Grand Concert (`cockpit-grand-concert.html` draws its
                         own `.mockbar`), with the same rule: the warning ships as words, the bar does not
                         ship at all. -->
                    <p
                        v-if="props.run.scenario === 'grand_concert'"
                        class="note mt-3 text-xs text-ink-muted"
                        data-field="support-data-note"
                    >
                        <b>Illustrative support data.</b> The concert loop and its payouts are what the
                        scenario matrix records, and that matrix was verified on 2026-09-27. A performance
                        type the run has not recorded prints <b>Not recorded</b> rather than a zero.
                    </p>
                </Section>
            </div>

            <div class="flex min-w-0 flex-col gap-4">
                <!-- Ruling 1: the advisor region is absent on a completed run, not empty. Gating the
                     wrapper rather than the rail is what makes the DOM agree with the page. -->
                <div v-if="isActive" data-region="advisor">
                    <AdvisorRail
                        :decision="props.advisor.decision"
                        :race-url="raceDoor"
                        :timeline-url="props.run.timeline_url"
                        :current-turn="props.header.turn === 0 ? null : props.header.turn"
                    />
                </div>

                <!-- HFW parity: every variant's right column ends with its doors, and Unity Cup's own
                     keybox names the section. Every href is a prop or an action-grid entry, so no
                     destination is spelled here and none can drift from the route table. -->
                <section aria-labelledby="career-where-to-go-heading" class="card">
                    <h2 id="career-where-to-go-heading" class="text-lg font-semibold text-ink-strong">Where to go</h2>
                    <ul class="mt-2 grid grid-cols-1 gap-2 @3xl:grid-cols-2">
                        <li v-for="link in whereToGo" :key="link.href">
                            <LinkButton :href="link.href" variant="secondary" size="sm">{{ link.label }}</LinkButton>
                        </li>
                    </ul>
                </section>
            </div>
        </div>

        <!-- The action grid is the set of doors a turn leads into, not a second ranking. No cockpit
             HFW draws it: `cockpit-unity-cup.html` reaches those screens through the stepper and the
             "Where to go" card, and the page head already carries the decision's single primary. It
             moves inside the run's tools disclosure rather than off the screen, so every door stays
             reachable while the visible page is the wireframe's. Trackblazer's Pro Shop rides with it
             for the same reason (plan Â§9 E4, design-2.0 Â§28). -->

        <!--
          The run's own write surfaces, behind one disclosure. SCREEN_SPEC.md SCR-CAR-011 gives the
          Cockpit the run identity, the cap composition, the roster, the run's races, the turn
          correction and the action grid; no cockpit HFW draws any of them. Collapsed rather than
          deleted so the doors stay reachable, and closed so the visible page is the wireframe's.
          HFW-OVERRIDE (write-paths): the HFW has no write surfaces and no disclosure.
        -->
        <details class="mt-4 rounded-lg border border-rule bg-panel p-4" data-region="run-tools">
            <summary class="min-h-11 cursor-pointer text-lg font-semibold text-ink-strong">Career tools</summary>

            <ActionGrid v-if="isActive" class="mt-4" :actions="props.actions" />

            <!-- Trackblazer's Pro Shop is reachable from the Cockpit's action area (plan §9
                 E4, design-2.0 §28 "The Shop should be quickly accessible from the Career
                 Cockpit"). The button is rendered only when the matrix composes a shop, so
                 other scenarios see no Shop affordance. WCAG 2.4.11: focus moves to the
                 Shop heading after the visit, and the body has `scroll-margin-top` so the
                 sticky mobile nav does not cover the focused control. -->
            <button
                v-if="hasShop"
                type="button"
                class="mt-2 inline-flex min-h-11 items-center justify-center rounded-full border-2 border-rule px-4 font-semibold text-ink-strong"
                @click="jumpToShop"
            >
                Shop
            </button>

        <section
            class="mt-4 rounded-lg border border-rule bg-panel p-4"
            aria-labelledby="career-timeline-heading"
        >
            <h2 v-if="isActive" id="career-timeline-heading" class="text-lg font-semibold text-ink-strong">
                Races in this run
            </h2>

            <!-- Once the career has ended the race list is history, not a decision, so it collapses
                 below the fold. The heading id moves onto the `summary` so the section keeps the one
                 accessible name it has while open. -->
            <details v-else data-region="races-in-this-run" class="mt-1 rounded-lg border border-rule bg-raised p-4">
                <summary
                    id="career-timeline-heading"
                    class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong"
                >
                    Races in this run
                </summary>
                <RunRaceStrip class="mt-2" :strip="props.raceStrip as never" />
            </details>

            <RunRaceStrip v-if="isActive" :strip="props.raceStrip as never" />

            <p class="mt-3 text-xs text-ink-muted">
                This region carries the run's own races. Every logged turn, race and event, in order,
                is on the Career Timeline.
            </p>

            <a
                :href="props.run.timeline_url"
                class="mt-2 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
            >
                Career Timeline
            </a>

            <!-- The Career Result's only other door was the 2.0.0 record page's header, so it sits
                 beside the timeline door here rather than in the action grid: the grid is the
                 advisor's answer about the next turn, and this is a summary of the career. -->
            <a
                :href="props.run.result_url"
                class="mt-2 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
            >
                Career Result
            </a>

            <!-- The run-delete door (F2, plan §9.6 ruling 4). Two-step disclosure: the summary
                 names the destructive action, the form inside submits the DELETE only after the
                 Trainer opens it and confirms. `deleteForm` carries no fields — the route is run
                 scoped and the controller deletes the bound row — so the only data that matters
                 is the URL. -->
            <details data-region="danger-zone" class="mt-4">
                <summary
                    class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-risk"
                >
                    Delete this career
                </summary>
                <p class="mt-2 text-sm text-ink-muted">
                    Permanently removes this run and every record tied to it: turns, race entries,
                    turn events, skills, support-deck slots, and the linked Veteran library record
                    (if any was saved). There is no undo.
                </p>
                <form
                    @submit.prevent="deleteRun"
                    class="mt-2"
                >
                    <button
                        type="submit"
                        class="min-h-11 rounded-full border-2 border-risk px-4 font-semibold text-risk"
                        :disabled="deleteForm.processing"
                    >
                        {{ deleteForm.processing ? 'Deleting…' : 'Delete this career' }}
                    </button>
                </form>
            </details>
        </section>

        <!-- The run's settings, below the reading. The header edit forms (F2, plan §9.6 ruling 1).
             Status, scenario and grade-point period all write through `runs.update`, the route and
             validator the 2.0.0 record screen used. A failure comes back with `page.props.errors`,
             which the page-level alert names. Each form carries the sibling fields the request still
             writes, so editing one cannot blank another. -->
        <section aria-labelledby="cockpit-header-edit" class="mt-4">
            <h2 id="cockpit-header-edit" class="text-lg font-semibold text-ink-strong">Run identity</h2>

            <!-- The export and deck doors are reads, not writes, so they stay outside the disclosure
                 a Trainer must open to change a value (audit 2026-10-12 §3.3). -->
            <ul class="mt-2 flex flex-wrap items-end gap-4 text-sm">
                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Export</span>
                    <!-- The export door (F2, plan §9.6). Both are GET file responses, so they are
                         plain links: routing a download through a redirect would turn it into an
                         HTML navigation instead of a file. -->
                    <span class="flex flex-wrap gap-2">
                        <a
                            :href="props.run.export_csv_url"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-semibold text-ink-strong underline"
                        >
                            Download CSV
                        </a>
                        <a
                            :href="props.run.export_json_url"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-semibold text-ink-strong underline"
                        >
                            Download JSON
                        </a>
                    </span>
                </li>

                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Support deck</span>
                    <!-- R2-17: `/training-runs/{run}/deck` answers and writes this run's six slots, and
                         the Cockpit offered no door to it, so a Trainer mid-career could reach the
                         builder only by typing its URL. A GET, so it is a plain link, like the exports. -->
                    <a
                        :href="props.run.deck_url"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-semibold text-ink-strong underline"
                    >
                        Edit this run's deck
                    </a>
                </li>
            </ul>

            <details data-region="edit-identity" class="mt-2 rounded-lg border border-rule bg-panel p-4">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                    Edit identity
                </summary>
                <ul class="mt-3 flex flex-wrap items-end gap-4 text-sm">
                <li class="flex flex-col gap-1">
                    <label for="status" class="text-ink-muted">Status</label>
                    <form @submit.prevent="saveStatus">
                         <BaseSelect
                           id="status"
                           v-model="statusForm.status"
                           name="status"
                           :options="Object.keys(props.run.status_labels).map(value => ({ value: value, label: props.run.status_labels[value] }))"
                         />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="statusForm.processing"
                        >
                            {{ statusForm.processing ? 'Saving…' : 'Change status' }}
                        </button>
                        <p v-if="statusForm.recentlySuccessful" class="mt-1 text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <label for="scenario" class="text-ink-muted">Scenario</label>
                    <form @submit.prevent="saveScenario">
                         <BaseSelect
                           id="scenario"
                           v-model="scenarioForm.scenario"
                           name="scenario"
                           :options="[{ value: '', label: 'No scenario set' }, ...Object.keys(props.run.scenarios).map(key => ({ value: key, label: props.run.scenarios[key] }))]"
                         />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="scenarioForm.processing"
                        >
                            {{ scenarioForm.processing ? 'Saving…' : 'Change scenario' }}
                        </button>
                        <p v-if="scenarioForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>

                <li
                    v-if="props.gradeObjectives.length > 0"
                    class="flex flex-col gap-1"
                >
                    <label for="current_objective_index" class="text-ink-muted">Grade Point period</label>
                    <form @submit.prevent="savePeriod">
                         <BaseSelect
                           id="current_objective_index"
                           v-model="periodForm.current_objective_index"
                           name="current_objective_index"
                           :options="[{ value: '', label: 'Not reported' }, ...props.gradeObjectives.map(objective => ({ value: String(objective.index), label: objective.index + '. ' + objective.name }))]"
                         />
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="periodForm.processing"
                        >
                            {{ periodForm.processing ? 'Saving…' : 'Report period' }}
                        </button>
                        <p v-if="periodForm.recentlySuccessful" role="status" class="mt-1 text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>

                <li class="flex flex-col gap-1">
                    <span class="text-ink-muted">Trainee rarity and potential</span>
                    <!-- R2-13: the header printed `★3 · Potential 2` and the growth row, and nothing
                         could enter either. The same write as the three forms above, with the three
                         facts added; empty stays "not stated" and `0` stays a real growth figure. -->
                    <form @submit.prevent="saveIdentity" class="flex flex-col gap-2">
                        <span class="flex flex-wrap items-end gap-3">
                            <BaseSelect
                              id="trainee_rarity"
                              v-model="identityForm.trainee_rarity"
                              name="trainee_rarity"
                              label="Rarity"
                              :options="[{ value: '', label: 'Not stated' }, ...[1, 2, 3].map(rarity => ({ value: String(rarity), label: '★' + String(rarity) }))]"
                            />
                            <label for="potential_level" class="flex flex-col gap-1">
                                <span class="text-xs text-ink-muted">Potential Level</span>
                                     <BaseInput
                                         id="potential_level"
                                         v-model="identityForm.potential_level"
                                         type="number"
                                         name="potential_level"
                                         min="1"
                                         max="5"
                                       />
                            </label>
                        </span>
                        <fieldset class="flex flex-wrap items-end gap-3">
                            <legend class="text-xs text-ink-muted">Growth rate</legend>
                            <label
                                v-for="stat in props.identity.stat_order"
                                :key="stat"
                                :for="`growth_rate_${stat}`"
                                class="flex flex-col gap-1"
                            >
                                <span class="text-xs text-ink-muted">{{ stat }}</span>
                                <span class="flex items-center gap-1">
                                     <BaseInput
                                         :id="`growth_rate_${stat}`"
                                         v-model="identityForm.growth_rate[stat]"
                                         type="number"
                                         :name="`growth_rate[${stat}]`"
                                         min="0"
                                         max="30"
                                       />
                                    <span class="text-ink-muted">%</span>
                                </span>
                            </label>
                        </fieldset>
                        <fieldset class="flex flex-wrap items-end gap-3">
                            <legend class="text-xs text-ink-muted">Stat ceilings</legend>
                            <!-- The ceiling the client shows for this run, entered rather than computed
                                 (`ADR-0025`): nothing here derives it from sparks or card effects. The
                                 published figure stays printed beside the field, so an entered ceiling is
                                 never read as the scenario's own arithmetic. -->
                            <label
                                v-for="(ceiling, stat) in props.identity.caps"
                                :key="stat"
                                class="flex flex-col gap-1"
                            >
                                <span class="text-xs text-ink-muted">{{ stat }}</span>
                                <span class="flex items-center gap-1">
                                     <BaseInput
                                         :id="`ceiling_${stat}`"
                                         v-model="identityForm[capColumn(String(stat))]"
                                         type="number"
                                         :name="capColumn(String(stat))"
                                         :aria-label="`${stat} ceiling`"
                                         min="1"
                                       />
                                    <span
                                        class="text-xs text-ink-muted"
                                        title="The figure this scenario publishes in its own cap table. An entered ceiling replaces it for this run; leaving the field empty restores it."
                                    >Scenario {{ ceiling.scenario }}</span>
                                </span>
                            </label>
                        </fieldset>
                        <p v-if="identityForm.isDirty" class="text-xs text-ink-muted">Unsaved changes.</p>
                        <button
                            type="submit"
                            class="mt-1 inline-flex min-h-11 w-fit items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong disabled:opacity-60"
                            :disabled="identityForm.processing"
                        >
                            {{ identityForm.processing ? 'Saving…' : 'Save identity' }}
                        </button>
                        <p v-if="identityForm.recentlySuccessful" class="text-xs text-ink-muted">Saved.</p>
                    </form>
                </li>
                </ul>
            </details>
        </section>

        <!-- The team write path rides the same disclosure as the other edit panels. It is `open` while the
             career is being played, so the Active screen keeps the panel it has always shown, and closed
             once the career has ended, where it is a setting rather than a decision. -->
        <details
            v-if="composesTeamPanel && teamState !== null"
            data-region="team-state"
            :open="isActive"
            class="mt-3 rounded-lg border border-rule bg-panel p-4"
        >
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">
                Edit team state
            </summary>
            <UnityCupEditor
                :action="props.run.update_url"
                :umamusume-id="props.run.umamusume_id"
                :status="props.run.status"
                :team="teamState"
            />
        </details>

        <!-- A-0.1: the cap arithmetic's recorded inputs (§1.2). The caps the run states sit beside the
             blue sparks its Legacy tree carries, and the panel states plainly that the two do not
             reconcile (the source's own open question). -->
        <section aria-labelledby="cap-composition-heading" class="card mt-4 rounded-lg p-4">
            <h2 id="cap-composition-heading" class="text-lg font-semibold text-ink-strong">Cap composition</h2>
            <dl class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                <div v-for="(cap, stat) in props.capComposition.caps" :key="stat" class="flex items-baseline gap-1">
                    <dt class="text-ink-muted">{{ stat }}</dt>
                    <dd class="font-mono tabular-nums text-ink-strong">{{ cap.toLocaleString('en-US') }}</dd>
                </div>
            </dl>
            <p class="mt-2 text-sm text-ink">
                <span class="font-semibold text-ink-strong">Blue sparks:</span>
                {{ capSparkText }}
            </p>
            <p class="mt-1 text-xs text-ink-muted">{{ props.capComposition.note }}</p>
        </section>

        <!-- A-0.6: the derived team roster — the six deck cards' characters. No stored roster;
             the list is read off the run's own deck slots. -->
        <section aria-labelledby="team-roster-heading" class="card mt-4 rounded-lg p-4">
            <h2 id="team-roster-heading" class="text-lg font-semibold text-ink-strong">Team roster</h2>
            <p v-if="props.roster.empty !== null" class="mt-1 text-sm text-ink-muted">{{ props.roster.empty }}</p>
            <ol v-else class="mt-2 space-y-1 text-sm">
                <li v-for="row in props.roster.rows" :key="row.slot" class="flex items-baseline justify-between gap-3">
                    <span class="text-ink">
                        {{ row.name ?? 'Unnamed card' }}
                        <span v-if="row.friend" class="ml-1 text-xs text-ink-muted">(Friends)</span>
                    </span>
                    <span class="font-mono text-xs text-ink-muted">Slot {{ row.slot }}<template v-if="row.ownership !== null"> · {{ row.ownership }}</template></span>
                </li>
            </ol>
        </section>

        <section v-if="props.correction !== null" aria-labelledby="career-correction-heading" class="mt-4">
            <h3 id="career-correction-heading" class="sr-only">Correct a turn by hand</h3>

            <!-- The turn selector (F2, plan §9.6 ruling 2). It sits outside the disclosure so a
                 switch does not close the form; `?edit_turn=<id>` is the same query the run
                 screen and the Timeline's decision link already use. -->
<BaseSelect
                    id="correction-turn-select"
                    ref="turnSelector"
                    label="Correct turn"
                    :value="String(props.correction.selected_turn_id)"
                    @update:model-value="switchTurn($event)"
                    :options="props.correction.turns.map(turn => ({ value: String(turn.id), label: 'Turn ' + turn.turn }))"
                />

            <details data-region="correct-turn" class="mt-2 rounded-lg border border-rule bg-panel p-4">
                <summary class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong">
                    Correct turn {{ props.correction.turn }} by hand
                </summary>
                <p class="mt-2 text-sm text-ink-muted">
                    Every field of the turn the app is reading. It writes on submit and shows no
                    preview, so use it to fix a mistyped reading rather than to log a new turn.
                </p>

                <div
                    v-if="correctionForm.hasErrors"
                    ref="alert"
                    role="alert"
                    tabindex="-1"
                    class="mt-3 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
                >
                    <p class="font-semibold">The correction was not saved.</p>
                    <ul class="mt-1 list-disc pl-5">
                        <li v-for="(message, key) in correctionForm.errors" :key="key">{{ message }}</li>
                    </ul>
                </div>

                <form class="mt-3 grid grid-cols-2 gap-3 text-sm @4xl:grid-cols-3" @submit.prevent="saveCorrection">
                    <BaseInput
                      v-model="correctionForm.turn"
                      type="number"
                      id="turn"
                      name="turn"
                      label="Turn *"
                      required
                      :error="correctionForm.errors.turn"
                    />
                    <BaseInput
                      v-for="field in statFields"
                      :key="field.name"
                      v-model="correctionForm[field.name]"
                      type="number"
                      :id="field.name"
                      name="field.name"
                      :label="`${field.label} *`"
                      required
                      :error="correctionForm.errors[field.name]"
                    />
                    <BaseInput
                      v-model="correctionForm.sp"
                      type="number"
                      id="sp"
                      name="sp"
                      label="Skill Points"
                      :error="correctionForm.errors.sp"
                    />
                    <fieldset class="col-span-2 flex flex-col gap-1 @4xl:col-span-3">
                        <legend class="text-ink-muted">Energy</legend>
                        <div class="flex flex-wrap items-center gap-x-4">
                            <BaseRadio
                              v-for="state in energyStates"
                              :key="state.value"
                              v-model="correctionForm.energy_state"
                              :id="`energy_state_${state.value}`"
                              name="energy_state"
                              :label="state.label"
                              :value="state.value"
                              :error="correctionForm.errors.energy_state"
                            />
                        </div>
                        <BaseInput
                          v-if="correctionForm.energy_state === 'exact'"
                          v-model="correctionForm.energy"
                          type="number"
                          id="energy"
                          name="energy"
                          label="Energy figure"
                          :min="0"
                          :max="100"
                          :error="correctionForm.errors.energy"
                        />
                        <BaseSelect
                          v-else-if="correctionForm.energy_state === 'band'"
                          v-model="correctionForm.energy_band"
                          name="energy_band"
                          label="Energy band"
                          :options="energyBands"
                          :error="correctionForm.errors.energy_band"
                        />
                    </fieldset>
                    <BaseInput
                      v-model="correctionForm.fans"
                      type="number"
                      id="fans"
                      name="fans"
                      label="Fans"
                      :error="correctionForm.errors.fans"
                    />
                    <BaseSelect
                      v-model="correctionForm.mood"
                      name="mood"
                      label="Mood"
                      :options="[{ value: '', label: 'not recorded' }, { value: 'GREAT', label: 'GREAT' }, { value: 'GOOD', label: 'GOOD' }, { value: 'NORMAL', label: 'NORMAL' }, { value: 'BAD', label: 'BAD' }, { value: 'AWFUL', label: 'AWFUL' }]"
                      :error="correctionForm.errors.mood"
                    />
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-md bg-primary-action px-4 font-semibold text-on-primary-action disabled:opacity-60 @4xl:col-span-3"
                        :disabled="correctionForm.processing"
                    >
                        {{ correctionForm.processing ? 'Saving…' : 'Save correction' }}
                    </button>
                    <!-- The two header forms on this screen already report their own write; this
                         one said nothing, so a Trainer could not tell a saved correction from a
                         dead button (R3-04). `Correction saved.` names which of the three forms
                         is speaking. -->
                    <p v-if="correctionForm.recentlySuccessful" class="text-xs text-ink-muted @4xl:col-span-3">
                        Correction saved.
                    </p>
                </form>

                <!-- Delete one turn and its failure event (F2, plan §9.6 ruling 2). A second
                     disclosure inside the correction panel: the write is `runs.turns.destroy`,
                     the route the record screen already used. -->
                <details class="mt-3 border-t border-rule pt-3">
                    <summary class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-risk">
                        Delete turn {{ props.correction.turn }}
                    </summary>
                    <p class="mt-2 text-sm text-ink-muted">
                        Deletes turn {{ props.correction.turn }} and its recorded failure event.
                        Other turns are unaffected. There is no undo.
                    </p>
                    <form class="mt-2" @submit.prevent="deleteTurn">
                        <button
                            type="submit"
                            class="min-h-11 rounded-full border-2 border-risk px-4 font-semibold text-risk"
                            :disabled="turnDeleteForm.processing"
                        >
                            {{ turnDeleteForm.processing ? 'Deleting…' : `Delete turn ${props.correction.turn}` }}
                        </button>
                    </form>
                </details>
            </details>
        </section>
        </details>

        </div>
    </CareerLayout>
</template>
