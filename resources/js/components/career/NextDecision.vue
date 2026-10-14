<script setup lang="ts">
/*
 * FD-2, the shared Next Decision component (plan §9a, screen-spec-2.0 §12 "Next Decision Component").
 *
 * **The five-element order is the contract, not a layout choice**: current priority, the recommended
 * action and its benefit, the best alternative and what it costs, the provenance-backed reasons, then
 * the conditions that would change the answer (design-2.0 §2 "State → Options → Recommendation →
 * Explanation → Decision"). Every branch below prints one of those five and nothing else.
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
import ObjectiveRow from './ObjectiveRow.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import RecordedActionCard from './RecordedActionCard.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface Objective {
    label: string;
    state: 'upcoming' | 'current' | 'completed' | 'unknown';
    due_turn: number | null;
    due_label: string | null;
    consequence: string | null;
    source: string;
    badge: Badge;
    title: string;
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
    }>(),
    { timelineUrl: null },
);

const refusal = computed(() => props.decision.refusal);
const recommended = computed(() => props.decision.recommended);
/** Empty rather than null, so the template asks one question of a list it always has. */
const teamValues = computed(() => props.decision.recommended?.team_value ?? []);
const objectives = computed(() => props.decision.objectives);
const recordedAction = computed(() => props.decision.recorded_action);

// The one obligation the rail must not let the Trainer miss. Same rule the server used to name it.
const due = computed(() => objectives.value.find((row) => row.state === 'current') ?? null);

// An empty list is a real state, not a missing one: a scenario whose matrix turns the calendar off
// composes no obligations, and saying so beats an empty box.
const hasObjectives = computed(() => objectives.value.length > 0);

// An empty `missing` is the other refusal: every input is recorded and nothing was short of its target.
const incompleteData = computed(
    () => refusal.value !== null && refusal.value.missing.length > 0,
);
</script>

<template>
    <div class="rounded-xl border border-rule bg-raised p-4">
        <section v-if="hasObjectives" aria-labelledby="career-objectives-heading">
            <h3 id="career-objectives-heading" class="text-lg font-semibold text-ink-strong">
                Objectives
            </h3>

            <ol class="mt-1 space-y-2">
                <ObjectiveRow
                    v-for="row in objectives"
                    :key="`${row.state}-${row.label}`"
                    :label="row.label"
                    :state="row.state"
                    :due_turn="row.due_turn"
                    :due_label="row.due_label"
                    :consequence="row.consequence"
                    :source="row.source"
                    :badge="row.badge"
                    :title="row.title"
                    :url="due === row ? props.raceUrl : null"
                />
            </ol>

            <AlertRow
                v-if="due !== null"
                class="mt-2"
                glyph="▲"
                tone="critical"
                :text="`${due.label} is due and not recorded.`"
                detail="The calendar puts it at or before the turn being decided and no race is recorded against it."
            />
        </section>

        <h3
            class="text-lg font-semibold text-ink-strong"
            :class="hasObjectives ? 'mt-4' : ''"
        >
            Current priority
        </h3>
        <p class="mt-1 text-sm font-semibold text-ink-strong" aria-live="polite">
            {{ props.decision.priority }}
        </p>

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
                class="mt-2 inline-flex min-h-11 items-center rounded-full bg-chrome px-5 text-sm font-bold text-on-chrome"
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
                :corrected-from="recordedAction.corrected_from"
                :timeline-url="props.timelineUrl"
            />
        </div>
    </div>
</template>
