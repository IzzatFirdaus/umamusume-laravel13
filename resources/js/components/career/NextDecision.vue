<script setup lang="ts">
/*
 * FD-2, the shared Next Decision component (plan §9a, screen-spec-2.0 §12 "Next Decision Component").
 *
 * **The five-element order is the contract, not a layout choice**: current priority, the recommended
 * action and its benefit, the best alternative and what it costs, the provenance-backed reasons, then
 * the conditions that would change the answer (design-2.0 §2 "State → Options → Recommendation →
 * Explanation → Decision"). Every branch below prints one of those five and nothing else.
 *
 * **How the order reaches the screen is the HFW's, and the order of the elements is not the order of
 * the blocks.** The wireframe's Advisor card reads: the warn block, the priority label, the
 * recommendation with its three bullets, then the "Obligations" table (`cockpit-unity-cup.html`;
 * `cockpit-trackblazer.html` and `cockpit-grand-concert.html` carry the same shape with their own
 * tables). So:
 *
 * - Priority prints as the HFW's small-caps label above the recommendation, not as a section of its
 *   own. It is not printed at all when a due obligation is opening the card, because the warn block
 *   states that same fact — one fact, one statement.
 * - The obligations table follows the recommendation block. Its label is the wireframe's word,
 *   "Obligations"; the section keeps its accessible name through `aria-labelledby`.
 *
 * **A region is not a card is not a block.** The composition gate reads the region's `h2` list; it
 * cannot see the order of the blocks inside a card, so the card's own order is asserted separately
 * (`tests/browser/career-cockpit.spec.ts`).
 *
 * **It receives the whole decision as one object and reads no scenario from anywhere.** There is no
 * scenario prop, no label comparison and no key literal in this file, so a fifth scenario reaches it
 * through the same payload with no edit here (gate G-33, D-240). The server owns the ranking
 * (`app/Services/Advisor/NextDecision.php`); this component formats what it is handed and acts for
 * nobody (design-2.0 §2 "the application never removes player agency").
 *
 * **Two refusals, two copies.** "No suitable action found" and "Not enough information" are different
 * statements about different things (design-2.0 §2), and the payload's `refusal.missing` is what tells
 * them apart: a non-empty list means an input is absent, an empty one means the ranking ran and found
 * nothing short. That is printed rather than inferred from the sentence.
 *
 * **Nothing here is a score, a band or a percentage.** The payload has no field for one and the
 * component does not derive one (ADR-0001 §3, ADR-0020 §2).
 *
 * Props are read through `computed`, never destructured into consts: Inertia patches the page in place,
 * so an instance survives a visit with new props underneath it and a value taken once at setup would
 * print the previous turn's answer. The Career Cockpit hit exactly that with its advisor band.
 */
import { computed } from 'vue';

import AlertRow from '../scenario/AlertRow.vue';
import AbsenceValue from '../AbsenceValue.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import RecordedActionCard from './RecordedActionCard.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface Objective {
    label: string;
    state: 'upcoming' | 'current' | 'completed' | 'not_reached' | 'unknown';
    due_turn: number | null;
    due_label: string | null;
    consequence: string | null;
    source: string;
    badge: Badge;
    title: string;
    /** `shared` rows come from `scenario_key IS NULL` and belong to every scenario's calendar. */
    scope: 'shared' | 'scenario';
}

interface RecordedRow {
    text: string;
    badge: 'confirmed' | 'calculated';
    title: string;
}

interface Decision {
    priority: string;
    /**
     * `team_value` is G4's dual readout: two figures, or null when the scenario composes no team ladder
     * or the advised action raises no stat. An empty row carries no badge, because a badge qualifies a
     * displayed value (owner ruling 2a retires `unknown` beside an absence on this axis).
     */
    recommended: {
        action: string;
        action_url: string;
        benefit: string[];
        team_value: {
            label: string;
            value: string | null;
            badge: 'confirmed' | 'calculated' | null;
            title: string;
        }[] | null;
    } | null;
    alternatives: { action: string; action_url: string; tradeoff: string }[];
    why: { text: string; badge: Badge; title: string }[];
    switch_if: string[];
    refusal: { reason: string; missing: string[] } | null;
    objectives: Objective[];
    /**
     * FD-4's eighth key: the action last taken, the snapshot that went with it and the result as entered.
     * Null for a run that has recorded no action, and for every row written before the action noun existed
     * -- those rows have no forecast to quote, and inventing one would print advice no screen ever showed.
     */
    recorded_action: {
        id: number;
        decision: Record<string, unknown>;
        action: string;
        choice: string;
        expected: RecordedRow[];
        actual: RecordedRow[] | null;
        reconciliation: { match: boolean; note: string } | null;
        /**
         * The source quote stored on the row when the action was taken.
         *
         * Declared as the shape `NextDecision::recordedForecast()` actually builds (a `label`, a `kind`
         * of `reference` or `absence`, and the branch/statement/reason that kind carries) rather than
         * `Record<string, unknown>`, because this value is handed straight to `RecordedActionCard`,
         * whose `forecast` prop is that discriminated shape. The two disagreed, so the forecast section
         * of the recorded-action card could not be type-checked at all.
         */
        recorded_forecast: {
            label: string;
            kind: 'reference' | 'absence';
            branches?: { name: string; value: string; note: string | null }[];
            statement?: string;
            server?: string;
            source?: string;
            disagreement?: string;
            reason?: string;
        };
        recorded_at: string | null;
        corrected_from: number | null;
    } | null;
}

const props = withDefaults(
    defineProps<{
        decision: Decision;
        /**
         * The screen that can settle a due obligation, handed down by the page from its own action grid. It is
         * a door, not a fact about the run: FD-3's row shape carries no url, so the page supplies the one the
         * grid already computes rather than the contract growing a ninth key.
         */
        raceUrl: string | null;
        /** The same rule for FD-4's card: the full action history is the Timeline's, and the page owns that door. */
        timelineUrl?: string | null;
        /**
         * The position the page is being read at. Used only to state how long an overdue obligation has
         * been due ("55 turns ago" in the wireframe); null prints no arithmetic.
         */
        currentTurn?: number | null;
        /** The run's scenario name, resolved by the server, for the scenario-scoped group's heading. */
        scenarioLabel?: string | null;
    }>(),
    { timelineUrl: null, currentTurn: null, scenarioLabel: null },
);

const refusal = computed(() => props.decision.refusal);
const recommended = computed(() => props.decision.recommended);
/** Empty rather than null, so the template asks one question of a list it always has. */
const teamValues = computed(() => props.decision.recommended?.team_value ?? []);
const objectives = computed(() => props.decision.objectives);
const recordedAction = computed(() => props.decision.recorded_action);

// The one obligation the rail must not let the Trainer miss. Same rule the server used to name it.
const due = computed(() => objectives.value.find((row) => row.state === 'current' || row.state === 'not_reached') ?? null);

// An empty list is a real state, not a missing one: a scenario whose matrix turns the calendar off
// composes no obligations, and saying so beats an empty box.
const hasObjectives = computed(() => objectives.value.length > 0);

/**
 * The obligations split by who owns them. `scope` is composed on the server from
 * `race_catalog_slots.scenario_key` and travels with each row; a row this component cannot classify is
 * treated as the run's own, because naming it under the scenario is the lesser error than implying the
 * whole career inherits it.
 */
const sharedObjectives = computed(() => objectives.value.filter((row) => row.scope === 'shared'));
const scenarioObjectives = computed(() => objectives.value.filter((row) => row.scope !== 'shared'));

/** How long the one due obligation has been due, or null when the page has no position to measure from. */
const turnsAgo = computed<number | null>(() => {
    if (due.value === null || due.value.due_turn === null || props.currentTurn === null) {
        return null;
    }

    const delta = props.currentTurn - due.value.due_turn;

    return delta > 0 ? delta : null;
});

const statePill = (state: string): { word: string; cls: string } => {
    switch (state) {
        case 'current':
            return { word: 'current', cls: 'pill-caution' };
        case 'not_reached':
            return { word: 'not reached', cls: 'pill-caution' };
        case 'completed':
            return { word: 'met', cls: 'pill-positive' };
        case 'upcoming':
            return { word: 'ahead', cls: 'pill-neutral' };
        default:
            return { word: 'unknown', cls: 'pill-neutral' };
    }
};

// An empty `missing` is the other refusal: every input is recorded and nothing was short of its target.
const incompleteData = computed(
    () => refusal.value !== null && refusal.value.missing.length > 0,
);
</script>

<template>
    <div class="card rounded-xl p-4">
        <!-- The one obligation the calendar puts at or before this turn, marked the way the HFW
             marks its current row: a caution pill carrying the word, beside the sentence that names
             the obligation. The row itself already prints `Due` as a word, so the state never rides
             on the pill's colour alone (WCAG 1.4.1, HFW `is-current` row).

             It opens the card, where `cockpit-unity-cup.html` puts it: the fact comes before the
             thing to do about it. -->
        <div v-if="due !== null" class="warn flex flex-wrap items-start gap-2">
            <span class="pill pill-caution shrink-0">{{ statePill(due.state).word }}</span>
            <AlertRow
                class="min-w-0 flex-1"
                glyph="▲"
                tone="critical"
                :text="due.label + ' is due and not recorded.' + (turnsAgo === null ? '' : ` It fell due on turn ${due.due_turn}, ${turnsAgo} turns ago.`)"
                detail="The calendar puts it at or before the turn being decided and no race is recorded against it."
            />
        </div>

        <!-- Priority, as the HFW's card carries it: a small-caps label with the sentence under it,
             the context the recommendation is read against (`cockpit-trackblazer.html`,
             `cockpit-grand-concert.html`). It is NOT a section of its own, and it is not printed
             when the warn block above already states the fact — `cockpit-unity-cup.html` draws no
             priority line at all, and the sentence would be that same fact twice. -->
        <div v-if="due === null" class="mt-3">
            <p class="text-[10px] font-extrabold uppercase tracking-[0.08em] text-ink-muted">Priority</p>
            <p class="mt-1 text-sm font-semibold text-ink-strong" aria-live="polite">
                {{ props.decision.priority }}
            </p>
        </div>

        <template v-if="refusal !== null">
            <p class="mt-3 text-sm text-ink-strong">
                {{ incompleteData ? 'Not enough information to recommend an action.' : 'No suitable action found.' }}
            </p>
            <p class="mt-1 text-sm text-ink">{{ refusal.reason }}</p>
            <p v-if="incompleteData" class="mt-2 text-sm text-ink">
                Missing: {{ refusal.missing.join(', ') }}. Record it on this run and the ranking re-runs
                against what you entered.
            </p>
            <p v-else class="mt-2 text-sm text-ink">
                Nothing is missing. Every stat reaches the target you entered, so there is no deficit to
                rank and no action is put first.
            </p>
        </template>

        <template v-else>
            <h3 class="mt-3 text-lg font-semibold text-ink-strong">
                Recommended action
            </h3>
            <p class="mt-1 text-xl font-bold text-ink-strong">{{ recommended?.action }}</p>
            <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-ink">
                <li v-for="(line, index) in recommended?.benefit ?? []" :key="index">{{ line }}</li>
            </ul>

            <!-- G4's dual readout. Two labelled figures, each traced, and neither a promise: the trainee
                 side is room under a published cap and the team side is the rank this run reported. A
                 row with no reading prints its reason rather than a zero. -->
            <div v-if="teamValues.length > 0" class="mt-2">
                <h4 class="text-lg font-semibold text-ink-strong">
                    What this session pays
                </h4>
                <!-- `dt`/`dd` pairs as direct children of the `dl`, laid out by a grid. The reason lives in
                     the `dd` with the figure: `term` and `definition` are not name-from-content roles, so
                     Chromium gives a `dt` no accessible name and a reader gets nothing from the term alone. -->
                <dl class="mt-1 grid grid-cols-[minmax(0,9rem)_minmax(0,1fr)] items-baseline gap-x-3 gap-y-1">
                    <template v-for="row in teamValues" :key="row.label">
                        <dt class="text-xs text-ink-muted">{{ row.label }}</dt>
                        <dd class="flex flex-wrap items-baseline gap-x-2">
                            <ProvenanceBadge v-if="row.badge !== null" :state="row.badge" :title="row.title" />
                            <span v-if="row.value !== null" class="text-sm text-ink">{{ row.value }}</span>
                            <AbsenceValue v-else :reason="row.title" compact />
                        </dd>
                    </template>
                </dl>
            </div>

            <a
                :href="recommended?.action_url"
                class="mt-2 inline-flex min-h-11 items-center rounded-md bg-primary-action px-5 text-sm font-bold text-on-primary-action"
            >
                Go to {{ recommended?.action }}
            </a>

            <template v-if="props.decision.alternatives.length > 0">
                <h3 class="mt-4 text-lg font-semibold text-ink-strong">
                    Alternatives
                </h3>
                <ul class="mt-1 space-y-2">
                    <li v-for="option in props.decision.alternatives" :key="option.action" class="text-sm">
                        <a :href="option.action_url" class="inline-flex min-h-11 items-center font-semibold text-ink-strong underline">
                            {{ option.action }}
                        </a>
                        <p class="text-ink">{{ option.tradeoff }}</p>
                    </li>
                </ul>
            </template>
        </template>

        <!-- The HFW's obligations table, after the recommendation block (`cockpit-unity-cup.html`):
             the recommendation, its reason and its cost live in one block, and the table is what
             qualifies them. It is labelled "Obligations", which is the word the wireframe uses
             (`cockpit-unity-cup.html:144`); the section keeps its accessible name through
             `aria-labelledby`. -->
        <section v-if="hasObjectives" aria-labelledby="career-objectives-heading">
            <p id="career-objectives-heading" class="text-[10px] font-extrabold uppercase tracking-[0.08em] text-ink-muted">
                Obligations
            </p>

            <!-- The wireframe's obligations table (Obligation / State / Due): a table, not a row list,
                 so the three aligned columns carry the comparison. The state is a pill AND a word, and
                 an undated row prints "no date" rather than a dash. The provenance badge and the door
                 stay inside the obligation cell. -->
            <table class="t mt-2 w-full border-collapse text-sm">
                <thead>
                    <tr><th class="text-left">Obligation</th><th class="text-left">State</th><th class="text-right">Due</th></tr>
                </thead>
                <!-- Two bodies, one per scope, each named. `sharedObjectives` are the calendar rows every
                     scenario inherits (`scenario_key IS NULL`); `scenarioObjectives` are this scenario's
                     own. One unqualified table let a Unity Cup Trainer read the game's career-wide finals
                     rail as though it were Unity Cup's, which is what the v3 audit reported as UX-04. -->
                <tbody v-if="sharedObjectives.length > 0" data-scope="shared">
                    <tr class="border-b border-rule-soft">
                        <th colspan="3" scope="colgroup" class="pt-2 pb-1 text-left text-[10px] font-extrabold uppercase tracking-[0.08em] text-ink-muted">
                            Career finals, every scenario
                        </th>
                    </tr>
                    <tr v-for="row in sharedObjectives" :key="'shared-' + row.state + '-' + row.label" :class="row.state === 'current' ? 'is-current bg-green-tint' : ''" class="border-b border-rule-soft align-top">
                        <td class="py-2 pr-3">
                            <a v-if="due === row && props.raceUrl !== null" :href="props.raceUrl" class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline">{{ row.label }}</a>
                            <b v-else class="text-ink-strong">{{ row.label }}</b>
                            <ProvenanceBadge :state="row.badge" :title="row.title" />
                            <span v-if="row.consequence !== null" class="mt-0.5 block text-xs text-ink-muted">{{ row.consequence }}</span>
                        </td>
                        <td class="py-2 pr-3"><span class="pill" :class="statePill(row.state).cls">{{ statePill(row.state).word }}</span></td>
                        <td class="py-2 text-right font-mono" :title="row.due_label === null ? 'The calendar does not date this obligation.' : (row.due_turn === null ? 'The calendar does not place this obligation on the turn grid; the figure is the finale distance the Scenario panel reports.' : undefined)">{{ row.due_label ?? 'no date' }}</td>
                    </tr>
                </tbody>
                <tbody v-if="scenarioObjectives.length > 0" data-scope="scenario">
                    <tr class="border-b border-rule-soft">
                        <th colspan="3" scope="colgroup" class="pt-2 pb-1 text-left text-[10px] font-extrabold uppercase tracking-[0.08em] text-ink-muted">
                            {{ props.scenarioLabel }} only
                        </th>
                    </tr>
                    <tr v-for="row in scenarioObjectives" :key="'scenario-' + row.state + '-' + row.label" :class="row.state === 'current' ? 'is-current bg-green-tint' : ''" class="border-b border-rule-soft align-top">
                        <td class="py-2 pr-3">
                            <a v-if="due === row && props.raceUrl !== null" :href="props.raceUrl" class="inline-flex min-h-11 items-center font-semibold text-ink-strong hover:underline">{{ row.label }}</a>
                            <b v-else class="text-ink-strong">{{ row.label }}</b>
                            <ProvenanceBadge :state="row.badge" :title="row.title" />
                            <span v-if="row.consequence !== null" class="mt-0.5 block text-xs text-ink-muted">{{ row.consequence }}</span>
                        </td>
                        <td class="py-2 pr-3"><span class="pill" :class="statePill(row.state).cls">{{ statePill(row.state).word }}</span></td>
                        <td class="py-2 text-right font-mono" :title="row.due_label === null ? 'The calendar does not date this obligation.' : (row.due_turn === null ? 'The calendar does not place this obligation on the turn grid; the figure is the finale distance the Scenario panel reports.' : undefined)">{{ row.due_label ?? 'no date' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <h3 class="mt-4 text-lg font-semibold text-ink-strong">
            Why this answer
        </h3>
        <dl class="mt-1 space-y-2">
            <div v-for="(row, index) in props.decision.why" :key="index" class="flex flex-wrap items-baseline gap-x-2">
                <dt class="shrink-0">
                    <ProvenanceBadge :state="row.badge" :title="row.title" />
                </dt>
                <dd class="min-w-0 flex-1 break-words text-sm text-ink">{{ row.text }}</dd>
            </div>
        </dl>

        <template v-if="props.decision.switch_if.length > 0">
            <h3 class="mt-4 text-lg font-semibold text-ink-strong">
                What would change this
            </h3>
            <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-ink">
                <li v-for="(line, index) in props.decision.switch_if" :key="index">{{ line }}</li>
            </ul>
        </template>

        <div v-if="recordedAction !== null" class="mt-4 border-t-2 border-rule pt-4">
            <RecordedActionCard
                :action="recordedAction.action"
                :recorded-at="recordedAction.recorded_at"
                :expected="recordedAction.expected"
                :actual="recordedAction.actual"
                :reconciliation="recordedAction.reconciliation"
                :forecast="recordedAction.recorded_forecast"
                :corrected-from="recordedAction.corrected_from"
                :timeline-url="props.timelineUrl"
            />
        </div>
    </div>
</template>
