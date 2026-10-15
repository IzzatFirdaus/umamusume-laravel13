<script setup lang="ts">
/*
 * URA Finale Career Milestones panel.
 *
 * Registered against the `career_milestones` flag. Draws the milestone
 * trackers with sub-cells and collapsible "Record this race" buttons.
 */

import AlertRow from '../../../../components/scenario/AlertRow.vue';
import ProvenanceBadge from '../../../../components/ProvenanceBadge.vue';
import SubCell from '../../../../components/SubCell.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface Milestone {
    label: string;
    state: string;
    title: string;
    figures: string;
    figures_badge: Badge;
    figures_title: string;
    due_turn: number | null;
    due_label: string;
    due_title: string;
    consequence: string;
    condition: string | null;
    decision_url: string;
}

const props = defineProps<{
    scenario: {
        milestones: Milestone[];
        alerts: { level: string; text: string; detail: string | null }[];
    };
    label: string;
    kind: 'widget' | 'panel';
    name: string;
}>();

const stateGlyphs: Record<string, string> = {
    completed: '●',
    current: '◉',
    upcoming: '○',
    missed: '×',
};

const stateWords: Record<string, string> = {
    completed: 'Recorded',
    current: 'Due now',
    upcoming: 'Upcoming',
    missed: 'Missed',
};

const glyph = (state: string): string => stateGlyphs[state] ?? '○';
const word = (state: string): string => stateWords[state] ?? state;
</script>

<template>
    <div class="flex flex-col gap-4">
        <AlertRow
            v-for="alert in props.scenario.alerts"
            :key="alert.text"
            glyph="▲"
            :text="`${alert.level}: ${alert.text}`"
            :detail="alert.detail"
            tone="critical"
        />

        <ul class="flex flex-col gap-3">
            <li
                v-for="milestone in props.scenario.milestones"
                :key="milestone.label"
                class="flex flex-col gap-2 rounded-md border border-rule bg-panel p-3"
            >
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <span class="font-semibold text-ink-strong">{{ milestone.label }}</span>
                    <span
                        v-if="milestone.state !== null"
                        class="inline-flex items-center gap-1 text-xs font-medium text-ink-strong"
                    >
                        <span aria-hidden="true" class="font-mono">{{ glyph(milestone.state) }}</span>
                        {{ word(milestone.state) }}
                    </span>
                </div>

                <SubCell
                    label="Due"
                    :value="milestone.due_turn === null ? 'N/A' : `Turn ${milestone.due_turn}`"
                    value-class="font-mono text-base tabular-nums text-ink-strong"
                    :title="milestone.due_title ?? (milestone.due_turn === null ? 'No due turn is recorded for this milestone.' : undefined)"
                />

                <SubCell
                    label="Figures"
                    :value="milestone.figures === '' ? 'N/A' : milestone.figures"
                    value-class="font-mono text-base tabular-nums text-ink-strong"
                    :title="milestone.figures_title ?? (milestone.figures === '' ? 'No figures were recorded for this milestone.' : undefined)"
                >
                    <ProvenanceBadge
                        v-if="milestone.figures_badge !== null"
                        :state="milestone.figures_badge"
                        :title="milestone.figures_title ?? undefined"
                    />
                </SubCell>

                <p v-if="milestone.condition !== null" class="text-xs text-ink-muted">
                    {{ milestone.condition }}
                </p>

                <p class="text-xs text-ink-muted">
                    {{ milestone.consequence }}
                </p>

                <a
                    :href="milestone.decision_url"
                    class="mt-2 inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                >
                    Record this race
                </a>
            </li>
        </ul>
    </div>
</template>