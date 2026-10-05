<script setup lang="ts">
import CapsuleHeader from './CapsuleHeader.vue';
import { computed } from 'vue';

// No scenario name is passed (G-33): the controller's `gradeObjectives()` already returns
// [] when the scenario composes no Grade Point panel, so an empty list is the off state
// (D-221, D-241, gate G-34). rotationTurns is config shop.rotation_turns or null.
interface Objective {
    index?: number;
    name: string;
    required: number;
}

interface Period {
    index: number;
    earned: number | null;
    unpriced: number;
}

const props = defineProps<{
    objectives: Objective[];
    current: number | null;
    earned: number | null;
    unpricedCount: number;
    periods: Period[];
    unassignedCount: number;
    rotationTurns: number | null;
}>();

const fmt = (n: number): string => n.toLocaleString('en-US');

// The denominator is the current objective alone. Surplus never carries forward (D-232),
// so a running total would print a banking strategy the game does not permit.
const index = computed(() =>
    props.current === null
        ? null
        : Math.max(0, Math.min(props.objectives.length - 1, Math.trunc(props.current))),
);

const objective = computed(() => (index.value === null ? null : props.objectives[index.value] ?? null));
const required = computed(() => (objective.value === null ? 0 : Math.max(0, Math.trunc(objective.value.required ?? 0))));
const logged = computed(() => objective.value !== null && props.earned !== null);
const earnedVal = computed(() => (logged.value ? Math.max(0, Math.trunc(props.earned as number)) : null));
const over = computed(() => (logged.value ? Math.max(0, (earnedVal.value as number) - required.value) : 0));
const remaining = computed(() => Math.max(0, required.value - (earnedVal.value ?? 0)));
const percent = computed(() =>
    !logged.value || required.value === 0 ? 0 : Math.min(100, Math.round(((earnedVal.value as number) / required.value) * 100)),
);

// Ladder rows read their own sum by the objective's 1-based index, never a running total (D-232).
const sums = computed(() => new Map(props.periods.map((row) => [Math.trunc(row.index), row])));

const barLabel = computed(() => {
    const name = objective.value?.name ?? 'the current objective';
    const head = logged.value
        ? `${fmt(earnedVal.value as number)} of ${fmt(required.value)}`
        : props.unpricedCount > 0
          ? 'Grade Points not totalled'
          : 'Grade Points not yet recorded';
    return `${head} toward ${name}`;
});
</script>

<template>
    <div v-if="objectives.length > 0" class="rounded-md border border-rule bg-panel p-3">
        <!-- The capsule header's anatomy was inlined here while CapsuleHeader.vue did not exist;
             it is the one owner of the lattice bleed and the 44px height now (DESIGN.md §2.3, D-3). -->
        <CapsuleHeader title="Grade Point" class="mb-3" />

        <div class="rounded-md border border-rule bg-raised p-3">
            <template v-if="objective === null">
                <!-- No target, no bar, no zero: `0 / 300` would assert both that a period is
                     chosen and that the trainee stands on nothing, and neither is known (D-220). -->
                <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Grade Point period</p>
                <p class="mt-0.5 text-sm text-ink">
                    <span class="font-bold text-ink">no period reported</span>: which deadline this run
                    is working toward is something the Trainer says, not something this tool infers
                    from a date, so there is no target to measure against yet.
                </p>
                <p v-if="unassignedCount > 0" class="mt-1.5 text-xs text-ink-muted">
                    {{ fmt(unassignedCount) }} logged
                    {{ unassignedCount === 1 ? 'result has' : 'results have' }} no period
                    entered against it, so none of them counts toward any total below.
                </p>
            </template>
            <template v-else>
                <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">Working toward</p>
                <p class="mt-0.5 text-base font-bold text-ink-strong">{{ objective.name }}</p>

                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-sunken" role="img" :aria-label="barLabel">
                    <div class="h-full rounded-full bg-green-deep" :style="{ width: `${percent}%` }"></div>
                </div>

                <p v-if="logged" class="mt-1.5 font-mono text-sm tabular-nums text-ink-strong">
                    {{ fmt(earnedVal as number) }} / {{ fmt(required) }}
                    <span class="font-sans text-xs text-ink-muted">
                        <template v-if="over > 0">· {{ fmt(over) }} over the objective</template>
                        <template v-else-if="required > 0">· {{ fmt(remaining) }} to go</template>
                        <template v-else>· no points required</template>
                    </span>
                </p>
                <!-- R18's middle state: logged races that cannot be priced is not the same
                     sentence as nothing logged. The total stays withheld (KI-10). -->
                <p v-else-if="unpricedCount > 0" class="mt-1.5 text-sm text-ink-muted">
                    <span class="font-bold text-ink">not yet totalled</span>:
                    {{ unpricedCount }} logged
                    {{ unpricedCount === 1 ? 'result has' : 'results have' }}
                    no published Grade Point value, so any total here would count less than
                    this run earned.
                </p>
                <p v-else class="mt-1.5 text-sm text-ink-muted">
                    <span class="font-bold text-ink">not yet recorded</span>: no races are
                    logged for this run, so there is no progress to show yet.
                </p>

                <!-- The bar caps at the objective, so the overflow is stated in words rather than left as a topped-out bar. -->
                <p v-if="over > 0" class="mt-2 rounded-md border border-rule bg-sunken px-3 py-2 text-xs text-ink">
                    {{ fmt(over) }} past the objective, and not banked. The next objective
                    starts from zero whatever this one finishes at.
                </p>
            </template>
        </div>

        <p class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink">
            <span class="font-bold text-ink-strong">Surplus does not carry over.</span>
            <template v-if="!logged">
                Each of these is judged on its own, so whatever is left over at one deadline is
                not available at the next.
            </template>
            <template v-else-if="required > 0">
                {{ fmt(earnedVal as number) }} of {{ fmt(required) }} is met against this
                objective; anything beyond it is not banked toward the next one.
            </template>
            <template v-else> This objective asks for no points, so there is nothing to bank. </template>
        </p>

        <ol class="mt-3 flex flex-col gap-1.5" aria-label="Grade Point objectives">
            <li
                v-for="(item, i) in objectives"
                :key="i"
                class="flex items-baseline justify-between gap-3 rounded-md border px-3 py-2 text-sm"
                :class="i === index ? 'border-2 border-pick-line bg-raised' : 'border-rule bg-panel'"
                :aria-current="i === index ? 'step' : undefined"
            >
                <span class="min-w-0 flex-1">
                    <span class="font-semibold" :class="i === index ? 'text-ink-strong' : 'text-ink-muted'">
                        {{ item.name }}
                    </span>
                    <span v-if="index !== null && i < index" class="ml-1.5 text-xs font-bold text-green-deep">Complete</span>
                </span>
                <span class="shrink-0 font-mono text-xs tabular-nums text-ink-muted">
                    {{ fmt(Math.trunc(item.required ?? 0)) }} pts
                    <!-- Each deadline carries its own sum only; an absent period says "not
                         recorded" rather than 0 (D-232, D-220). -->
                    <template v-if="sums.get(item.index ?? i + 1)">
                        ·
                        <template v-if="sums.get(item.index ?? i + 1)?.earned !== null">
                            {{ fmt(sums.get(item.index ?? i + 1)!.earned as number) }} earned
                        </template>
                        <template v-else-if="(sums.get(item.index ?? i + 1)?.unpriced ?? 0) > 0">not totalled</template>
                        <template v-else>not recorded</template>
                    </template>
                </span>
            </li>
        </ol>

        <!-- D-232: the rotation decides when an offer is worth saving for; the balance is not,
             and this build holds no coin figure yet, so it is named as missing (D-220). -->
        <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 border-t border-rule pt-3 text-xs text-ink-muted">
            <span v-if="rotationTurns !== null">Rotation resets in {{ Math.trunc(rotationTurns) }} turns</span>
            <span>Shop Coins: not yet recorded</span>
        </div>

        <!-- R40: the ladder is the standard track; the other two tracks are unsourced and KI-15 carries the disagreement (D-256). -->
        <p class="mt-3 text-xs text-ink-muted">
            Targets shown are the <span class="font-bold text-ink">standard</span> track. Which
            trainee belongs on the dirt-leaning or limited-turf-range track is not sourced, and
            KI-15 carries the disagreement.
        </p>
    </div>
</template>
