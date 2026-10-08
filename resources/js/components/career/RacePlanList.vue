<script setup lang="ts">
/*
 * One group of the Scenario Race Planner (`SCR-CAR-023`, SCREEN-017, plan §9 E5): its heading, the
 * sentence that states the rule it was built by, and either its races as the D10 `RaceCard` or the
 * named empty state that says what is missing and what to do.
 *
 * **One race component, not two.** The card is `components/career/RaceCard.vue`, the one D10 built:
 * the planner adds a selection control through the slot that component carries and changes nothing
 * else about it. A second race card would be the same facts drawn twice, and the two would drift.
 *
 * The group key names the rule, never a scenario: `mandatory`, `upcoming`, `optional` and `rival` are
 * the planner's own partition of the calendar, and the heading id is built from the key so two groups
 * cannot collide on one id.
 */
import RaceCard from './RaceCard.vue';

interface Fact {
    key: string;
    label: string;
    value: string | null;
    title: string | null;
}

interface Race {
    id: number;
    title: string;
    tier: string | null;
    year_label: string;
    turn: number | null;
    fans_needed: number | null;
    maiden_gated: boolean;
    is_mandatory: boolean;
    is_special: boolean;
    facts: Fact[];
    status: string | null;
    placement: string | null;
    fans_gain: number | null;
    grade_points: number | null;
}

const props = defineProps<{
    group: { key: string; label: string; definition: string; total: number; truncated: string | null; empty: string | null; races: Race[] };
    readiness: { label: string; title: string };
    entry: { action: string; turns: { id: number; turn: number }[] };
    skipUrl: string;
    selected: number[];
    limit: number;
}>();

const emit = defineEmits<{ toggle: [id: number] }>();

const headingId = `race-plan-${props.group.key}-heading`;

function isSelected(id: number): boolean {
    return props.selected.includes(id);
}

// The cap is the brief's "up to four". A fifth is refused by the control rather than by a message
// after the fact, and the refusal is visible because the control is disabled rather than inert.
function atLimit(id: number): boolean {
    return !isSelected(id) && props.selected.length >= props.limit;
}
</script>

<template>
    <section :aria-labelledby="headingId" class="mt-4">
        <h2 :id="headingId" class="text-base font-semibold text-ink-strong">{{ props.group.label }}</h2>
        <p class="mt-1 text-sm text-ink-muted">{{ props.group.definition }}</p>

        <p
            v-if="props.group.empty !== null"
            class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink"
        >
            {{ props.group.empty }}
        </p>
        <ul v-else class="mt-3 flex flex-col gap-3">
            <RaceCard
                v-for="race in props.group.races"
                :key="race.id"
                :race="race"
                :readiness="props.readiness"
                :entry="props.entry"
                :skip-url="props.skipUrl"
            >
                <template #select>
                    <!-- A pressed toggle rather than a checkbox: it is a 44px control with a glyph
                         and a word, so the state is never carried by colour, and the repo already
                         reads mode switches this way (`RacePanel.vue`). -->
                    <button
                        type="button"
                        class="mb-2 inline-flex min-h-11 items-center gap-2 rounded-md border px-3 text-sm font-semibold"
                        :class="
                            isSelected(race.id)
                                ? 'border-pick-line bg-pick/10 text-ink-strong'
                                : 'border-rule text-ink-muted'
                        "
                        :aria-pressed="isSelected(race.id) ? 'true' : 'false'"
                        :disabled="atLimit(race.id)"
                        @click="emit('toggle', race.id)"
                    >
                        <span aria-hidden="true" class="font-mono">{{ isSelected(race.id) ? '−' : '+' }}</span>
                        Compare {{ race.title }}
                    </button>
                </template>
            </RaceCard>
        </ul>

        <!-- The loss is named rather than silent: a list that stops at eight reads as a calendar that
             holds eight races. -->
        <p v-if="props.group.truncated !== null" class="mt-2 text-xs text-ink-muted">
            {{ props.group.truncated }}
        </p>
    </section>
</template>
