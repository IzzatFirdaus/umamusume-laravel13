<script setup lang="ts">
/*
 * The fan-gate timeline (G3, `docs/URA-Finale-plan.md` §13).
 *
 * **No number lives in this file.** The two figures per gate come off the scenario's own config entry
 * and the state comes off the run's latest fan reading, both composed in
 * `App\Http\Controllers\Career\CockpitController::milestoneSection()`. A scenario whose entry declares
 * no gates gets an empty list, and the section draws nothing rather than an empty heading.
 *
 * **The mandatory races are not re-listed here.** `UraPanel` already draws them as `Mandatory races`
 * from the same screen, and the finale block is the strip's own line. This list is the gates.
 *
 * **A state between the two published figures is `N/A`, not a guess.** No column records whether this
 * trainee races on turf or dirt, so a gate whose reading clears one figure and not the other has no
 * state this tool can claim. Naming a track would be the invention; the two bounds that need no naming
 * are what the server decides on.
 */
import ProvenanceBadge from '../ProvenanceBadge.vue';

type Badge = 'confirmed' | 'calculated' | 'estimated' | 'unknown';

interface MilestoneRow {
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
    milestones: MilestoneRow[];
}>();

// The four marks `UraPanel` prints for its own rows, kept in step by the browser spec that names each
// word. A state outside this set has no glyph, which is how `unknown` reaches the screen.
const glyphs: Record<string, string> = {
    completed: '●',
    current: '◉',
    upcoming: '○',
    missed: '×',
};

const words: Record<string, string> = {
    completed: 'Recorded',
    current: 'Due now',
    upcoming: 'Upcoming',
    missed: 'Missed',
};

const headingId = 'career-milestones';
const marked = (state: string): boolean => glyphs[state] !== undefined;
</script>

<template>
    <section
        v-if="props.milestones.length > 0"
        class="flex flex-col gap-2"
        :aria-labelledby="headingId"
    >
        <h3
            :id="headingId"
            class="text-lg font-semibold text-ink-strong"
        >
            Career milestones
        </h3>

        <ol class="flex flex-col gap-3">
            <li
                v-for="row in props.milestones"
                :key="row.label"
                class="flex flex-col gap-1 border-b border-rule pb-3 last:border-b-0"
            >
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    <template v-if="marked(row.state)">
                        <span aria-hidden="true" class="font-mono text-ink">{{ glyphs[row.state] }}</span>
                        <span class="text-xs text-ink-muted" :title="row.title">{{ words[row.state] }}</span>
                    </template>
                    <span v-else class="text-xs text-ink-muted" :title="row.title">N/A</span>

                    <span class="text-sm font-medium text-ink-strong">{{ row.label }}</span>

                    <!-- Both figures travel as one published pair: the gate is the same event on either
                         track, and which one binds is the reading no column holds. -->
                    <ProvenanceBadge :state="row.figures_badge" :title="row.figures_title" />
                    <span class="font-mono text-sm text-ink">{{ row.figures }}</span>
                </div>

                <!-- The state with no mark is the case a Trainer most needs the sentence for, so it reads
                     on the page and not only in a `title`. -->
                <p v-if="!marked(row.state)" class="text-xs text-ink-muted">
                    {{ row.title }}
                </p>

                <p class="text-xs text-ink-muted" :title="row.due_title">
                    {{ row.due_label }} · turn N/A
                </p>

                <p class="text-xs text-ink">
                    When met: {{ row.consequence }}<template v-if="row.condition !== null">, and it also needs
                    {{ row.condition }}</template>.
                </p>

                <!-- App copy, no client source: the client prints no string for this door, so the tool's
                     own words label it, as `Record this race` does on the rows above. -->
                <a
                    :href="row.decision_url"
                    class="inline-flex min-h-11 items-center text-xs font-medium text-ink-strong underline"
                >
                    Race options for this gate
                </a>
            </li>
        </ol>
    </section>
</template>
