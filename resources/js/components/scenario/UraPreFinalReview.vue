<script setup lang="ts">
/*
 * The check a career makes against its own finale (G3), shown only inside the window.
 *
 * **The window is not arithmetic this file owns.** The section is null outside it, so a three-turn-old
 * career draws no heading at all rather than an empty block; the proximity comes from the advisor's
 * own `finale_context`, which is the reason the rail and this panel cannot name different turns.
 *
 * **Every target is a published figure, hedged as the source hedges it.** The guide states the Speed
 * ask as "around 800, or an A rank", so the qualifier travels with the number instead of being
 * rounded into a requirement nobody published. The four stamina floors print as a table because the
 * one that applies is the finale distance, and nothing in this tool computes which distance that is.
 *
 * **A reading the run has not logged is `N/A` with its reason**, never a zero: a zero would claim the
 * trainee stands at 0 Speed, which is a statement about a career this tool has no evidence for.
 */
import AbsenceValue from '../AbsenceValue.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';

type Badge = 'confirmed' | 'calculated' | 'estimated' | 'unknown';

/**
 * A badge qualifies a displayed value (owner ruling 2a): the rows this panel draws an `N/A` on carry
 * `null`, and no chip accompanies the absence. `unknown` survives only on the Database reference
 * views' citation-strength axis, which is not this surface.
 */
interface StatRow {
    label: string;
    value: string | null;
    cap: string | null;
    badge: Badge | null;
    title: string;
}

const props = defineProps<{
    preFinal: {
        window: {
            state: string;
            label: string;
            turns_away: number | null;
            turns_ahead: number;
        };
        recorded: StatRow[];
        speed_target: {
            phrasing: string;
            value: string | null;
            short_by: string | null;
            badge: Badge | null;
            title: string;
        };
        stamina_floors: {
            rows: { distance: string; floor: string }[];
            badge: Badge;
            title: string;
        };
        distance: { value: string | null; title: string };
    };
}>();

const states: Record<string, string> = {
    next: 'arrives on the turn being decided',
    upcoming: 'ahead of the turn being decided',
    passed: 'behind this run',
};

/** The window sentence, from the advisor's own state word. */
function windowLine(): string {
    const w = props.preFinal.window;
    const said = states[w.state] ?? w.state;

    return w.turns_away === null
        ? `${w.label}: ${said}.`
        : `${w.label}: ${said} in ${w.turns_away} ${w.turns_away === 1 ? 'turn' : 'turns'}.`;
}

const headingId = 'ura-pre-final-review';
</script>

<template>
    <section class="flex flex-col gap-3" :aria-labelledby="headingId">
        <h3 :id="headingId" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
            Pre-final review
        </h3>

        <p class="text-sm text-ink" :title="props.preFinal.window.title">
            {{ windowLine() }}
            <ProvenanceBadge state="calculated" :title="props.preFinal.window.title" />
        </p>

        <div class="flex flex-col gap-2">
            <h4 id="ura-pre-final-recorded" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                Recorded against the published cap
            </h4>

            <dl class="flex flex-col gap-2">
                <div
                    v-for="row in props.preFinal.recorded"
                    :key="row.label"
                    class="flex flex-wrap items-baseline gap-x-2 gap-y-1 border-b border-rule pb-2 last:border-b-0"
                >
                    <dt class="text-xs text-ink-muted">{{ row.label }}</dt>
                    <ProvenanceBadge v-if="row.badge !== null" :state="row.badge" :title="row.title" />
                    <dd v-if="row.value !== null" class="font-mono text-sm text-ink">{{ row.value }}</dd>
                    <dd v-else>
                        <AbsenceValue :reason="row.title" compact />
                    </dd>
                    <span v-if="row.cap !== null" class="text-xs text-ink-muted">of {{ row.cap }}</span>
                </div>
            </dl>
        </div>

        <div class="flex flex-col gap-2">
            <h4 id="ura-pre-final-targets" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                Published targets
            </h4>

            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                <span class="text-xs text-ink-muted">To win the finale</span>
                <ProvenanceBadge
                    v-if="props.preFinal.speed_target.badge !== null"
                    :state="props.preFinal.speed_target.badge"
                    :title="props.preFinal.speed_target.title"
                />
                <span class="text-sm text-ink">{{ props.preFinal.speed_target.phrasing }}</span>
                <span v-if="props.preFinal.speed_target.value !== null" class="font-mono text-xs text-ink">
                    recorded {{ props.preFinal.speed_target.value }}
                </span>
                <span
                    v-if="props.preFinal.speed_target.short_by !== null"
                    class="text-xs text-ink-muted"
                    :title="props.preFinal.speed_target.title"
                >
                    {{ props.preFinal.speed_target.short_by }} short of the approximation
                </span>
                <AbsenceValue
                    v-if="props.preFinal.speed_target.value === null"
                    :reason="props.preFinal.speed_target.title"
                    compact
                />
            </div>

            <ul class="flex flex-col gap-1">
                <li
                    v-for="floor in props.preFinal.stamina_floors.rows"
                    :key="floor.distance"
                    class="flex flex-wrap items-baseline gap-x-2"
                >
                    <span class="text-xs text-ink-muted">{{ floor.distance }}</span>
                    <span class="font-mono text-sm text-ink">{{ floor.floor }}</span>
                    <span class="text-xs text-ink-muted">stamina</span>
                </li>
            </ul>

            <ProvenanceBadge :state="props.preFinal.stamina_floors.badge" :title="props.preFinal.stamina_floors.title" />

            <!-- The distance is a pure absence: nothing computes it, so the row carries the `N/A`
                 marker and its reason and no badge (owner ruling 2a). -->
            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                <span class="text-xs text-ink-muted">This run's finale distance</span>
                <AbsenceValue :reason="props.preFinal.distance.title" compact />
            </div>
        </div>
    </section>
</template>
