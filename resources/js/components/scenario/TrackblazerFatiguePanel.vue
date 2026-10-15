<script setup lang="ts">
/*
 * Race fatigue (G5, plan §10): the chain the Trainer reported, the odds row the publisher labels
 * with it, the two caveats that table leaves open, and the fixed recovery events.
 *
 * **The chain is entered, never derived.** `TrainingRun::consecutiveRaceCount()` returns null on
 * purpose: a turn with no race logged says "the Trainer has not named this turn a race", which is not
 * the same statement as "this turn rested", so a count read off absent rows would be guessing from
 * absence (D-270). What prints here is the `RaceFatiguePayload{consecutive_races}` the Trainer put on
 * a turn, or the sentence saying none is entered.
 *
 * **Both caveats are visible text.** A caveat a Trainer has to hover for is a caveat they never read,
 * and these two are the difference between a table and a usable one: the row is labelled "3 races"
 * without saying whether the race about to run is inside it, and the Late December limit names no
 * year. The source is one publisher (uma.guide,
 * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md:231-236`); the same master's other
 * Trackblazer section prints no odds at all, so one source is not two.
 *
 * **No figures live in this file.** The table, the caveats and the recovery list come from
 * `App\Http\Controllers\Career\CockpitController::fatigueSection()` reading the scenario's config
 * entry.
 *
 * The wide form is a real table with a caption and scoped headers, inside its own horizontal scroll
 * container so the page never scrolls sideways at 320px; the narrow form is the row alone.
 */
import { computed } from 'vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface FatigueSection {
    chain: number | null;
    chain_label: string | null;
    chain_badge: Badge | null;
    chain_title: string;
    row: Record<string, string> | null;
    row_title: string;
    table: Record<string, Record<string, string>>;
    caveats: string[];
    recovery: { when: string; effect: string }[];
    recovery_badge: Badge;
    recovery_title: string;
}

const props = defineProps<{
    fatigue: FatigueSection;
}>();

const headingId = 'trackblazer-race-fatigue';

const measures = computed((): string[] => Object.keys(props.fatigue.table['1 race'] ?? {}));
const chains = computed((): string[] => Object.keys(props.fatigue.table));
</script>

<template>
    <section :aria-labelledby="headingId" class="flex flex-col gap-3 rounded-md border border-rule bg-panel p-4">
        <h4 :id="headingId" class="text-lg font-semibold text-ink-strong">
            Race fatigue
        </h4>

        <p class="flex flex-wrap items-baseline gap-x-2 text-sm">
            <span class="text-ink">Chain so far:</span>
            <ProvenanceBadge v-if="props.fatigue.chain_badge !== null" :state="props.fatigue.chain_badge" :title="props.fatigue.chain_title" />
            <span v-if="props.fatigue.chain !== null" class="font-mono tabular-nums text-ink-strong">
                {{ props.fatigue.chain }}
                <span class="font-sans text-xs text-ink-muted">({{ props.fatigue.chain_label }})</span>
            </span>
            <span v-else class="text-ink-muted" :title="props.fatigue.chain_title">N/A</span>
        </p>

        <p v-if="props.fatigue.chain === null" class="text-xs text-ink-muted">
            {{ props.fatigue.chain_title }}
        </p>

        <!--
            The reference material folds; the caveats below never do. A native `<details>` is the
            platform disclosure (no component, no focus trap to maintain) and it is legal in this arm
            because the Trackblazer region already carries a form: unlike the Unity Cup scenario
            region, nothing pins it to zero focusable controls. The summary is a real tap target at
            the 44px contract.
        -->
        <details :open="props.fatigue.row !== null" class="flex flex-col gap-2">
            <summary class="min-h-11 cursor-pointer select-none rounded-md border border-rule bg-raised px-3 py-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                <template v-if="props.fatigue.chain_label !== null">
                    Odds at {{ props.fatigue.chain_label }}
                </template>
                <template v-else>
                    Every row the publisher prints
                </template>
            </summary>

            <dl
                v-if="props.fatigue.row !== null && props.fatigue.chain_label !== null"
                class="grid grid-cols-1 gap-x-3 gap-y-1 rounded-md border border-rule bg-raised p-3 sm:grid-cols-[minmax(0,12rem)_minmax(0,1fr)]"
            >
                <!-- Each `dt` is followed by its `dd` and the pairs are the `dl`'s own children: a heading
                     `dt` with no value beside it fails axe's `definition-list` rule, which is why the label
                     lives on the summary instead. -->
                <template v-for="(value, measure) in props.fatigue.row" :key="measure">
                    <dt class="text-xs text-ink-muted">{{ measure }}</dt>
                    <dd class="font-mono text-sm tabular-nums text-ink" :title="props.fatigue.row_title">{{ value }}</dd>
                </template>
            </dl>

            <div
                v-else
                class="overflow-x-auto rounded-md border border-rule bg-raised p-3"
                role="region"
                tabindex="0"
                aria-label="Fatigue measures by chain"
            >
                <table class="w-full min-w-[26rem] border-collapse text-xs">
                    <caption class="mb-2 text-left text-xs text-ink-muted">
                        No chain is entered for this run yet, so every row the publisher prints is shown.
                        <span :title="props.fatigue.row_title">{{ props.fatigue.row_title }}</span>
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" class="border-b border-rule py-1 pr-2 text-left font-semibold text-ink-strong">
                                Measure
                            </th>
                            <th
                                v-for="chain in chains"
                                :key="chain"
                                scope="col"
                                class="border-b border-rule px-2 py-1 text-left font-semibold text-ink-strong"
                            >
                                {{ chain }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="measure in measures" :key="measure">
                            <th scope="row" class="border-b border-rule py-1 pr-2 text-left font-normal text-ink">
                                {{ measure }}
                            </th>
                            <td
                                v-for="chain in chains"
                                :key="`${measure}-${chain}`"
                                class="border-b border-rule px-2 py-1 font-mono tabular-nums text-ink"
                            >
                                {{ props.fatigue.table[chain]?.[measure] ?? 'N/A' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-1">
                <p class="flex flex-wrap items-baseline gap-x-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                    Guaranteed recovery events
                    <ProvenanceBadge :state="props.fatigue.recovery_badge" :title="props.fatigue.recovery_title" />
                </p>
                <ul class="flex flex-col gap-1" aria-label="Guaranteed recovery events">
                    <li
                        v-for="event in props.fatigue.recovery"
                        :key="event.when"
                        class="flex flex-wrap items-baseline justify-between gap-x-3 text-xs"
                    >
                        <span class="text-ink">{{ event.when }}</span>
                        <span class="font-mono text-ink-muted">{{ event.effect }}</span>
                    </li>
                </ul>
            </div>
        </details>

        <ul class="flex flex-col gap-1" aria-label="What this table does not settle">
            <li
                v-for="caveat in props.fatigue.caveats"
                :key="caveat"
                class="rounded-md border border-rule bg-sunken px-3 py-2 text-xs text-ink"
            >
                {{ caveat }}
            </li>
        </ul>
    </section>
</template>
