<script setup lang="ts">
/*
 * One skill row on the Skills Planner (plan §8 D13). Shared by the Required, Available and Learned
 * groups: the state word lives on the group's heading and its glyph, so a row never has to repeat
 * either. What a row prints is the skill's own facts, the ladder priced off the sourced percentages,
 * the raw conditions the source states, and the fit cells - matches, does not match, not recorded -
 * which compare nothing the repository cannot source (PRD OQ-5 keeps style unclaimed).
 *
 * The move buttons are the only edit; when `movable` is false they do not render, and the reorder
 * itself lives in the page that owns the list order.
 */
import { computed, nextTick, ref } from 'vue';

interface CostEntry {
    level: string;
    percent: number;
    cost: number | null;
}

interface FitCell {
    key: string;
    label: string;
    state: 'matches' | 'mismatch' | 'unrecorded';
    title: string | null;
}

const props = defineProps<{
    row: {
        id: number | null;
        name: string;
        name_ja: string | null;
        is_unique: boolean;
        sp_cost: number | null;
        costs: CostEntry[];
        recorded: { status: string; turn: number | null } | null;
        conditions: string | null;
        fit: FitCell[];
        note: string | null;
    };
    position: number;
    count: number;
    movable: boolean;
}>();

const emit = defineEmits<{ move: [delta: number] }>();

const FIT_WORDS: Record<FitCell['state'], string> = {
    matches: 'Matches',
    mismatch: 'Does not match',
    unrecorded: 'Not recorded',
};

const atTop = computed(() => props.position === 1);
const atBottom = computed(() => props.position === props.count);

const upButton = ref<HTMLButtonElement | null>(null);
const downButton = ref<HTMLButtonElement | null>(null);

async function move(delta: number): Promise<void> {
    emit('move', delta);
    await nextTick();

    // The pressed button may have become the end of the list the move ran into, and a disabled
    // control cannot hold focus, so the keyboard path follows to its counterpart instead of
    // stranding on the page.
    const pressed = delta === -1 ? upButton.value : downButton.value;

    if (pressed !== null && !pressed.disabled) {
        pressed.focus();

        return;
    }

    (delta === -1 ? downButton.value : upButton.value)?.focus();
}
</script>

<template>
    <li class="rounded-md border border-rule bg-raised p-3">
        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <h4 class="text-sm font-semibold text-ink-strong">
                {{ props.row.name }}
                <span v-if="props.row.name_ja !== null" lang="ja" class="ml-1 font-normal text-ink-muted">{{ props.row.name_ja }}</span>
                <span
                    v-if="props.row.is_unique"
                    class="ml-1 rounded-full bg-sunken px-2 py-0.5 text-xs font-normal text-ink-strong"
                    title="Marked from the source's skill class code, not from a card's own skill list."
                >✦ Unique</span>
            </h4>
            <p class="font-mono text-xs tabular-nums text-ink-muted">
                <span v-if="props.row.sp_cost === null" title="The source publishes no SP cost for this skill.">N/A SP</span>
                <template v-else>{{ props.row.sp_cost }} SP base</template>
            </p>
        </div>

        <p v-if="props.row.recorded !== null" class="mt-1 text-xs text-ink-muted">
            Recorded: {{ props.row.recorded.status }}
            <template v-if="props.row.recorded.turn !== null"> on turn {{ props.row.recorded.turn }}</template>
        </p>

        <!-- The ladder, priced: the captions and percentages are the client's own (REFERENCE §1.1.4),
             the arithmetic is base x (1 - discount) floored, and an unpriced skill costs N/A at
             every level rather than 0. -->
        <p v-if="props.row.sp_cost !== null" class="mt-1 flex flex-wrap gap-x-3 font-mono text-xs tabular-nums text-ink">
            <span v-for="cost in props.row.costs" :key="cost.level">{{ cost.level }}: {{ cost.cost }} SP</span>
        </p>
        <p v-else class="mt-1 text-xs text-ink-muted" title="No base price is published, so no discounted figure can exist at any hint level.">
            Costs at each hint level: N/A.
        </p>

        <p v-if="props.row.conditions !== null" class="mt-1 text-xs text-ink-muted">
            Conditions as the source states them: <code class="text-ink">{{ props.row.conditions }}</code>
        </p>

        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs sm:grid-cols-4">
            <div v-for="cell in props.row.fit" :key="cell.key" class="flex flex-col">
                <dt class="font-semibold uppercase tracking-wide text-ink-muted">{{ cell.label }}</dt>
                <dd class="text-ink-strong" :title="cell.title ?? undefined">{{ FIT_WORDS[cell.state] }}</dd>
            </div>
        </dl>

        <p v-if="props.row.note !== null" class="mt-2 rounded-md border border-rule bg-panel p-2 text-xs text-ink">
            {{ props.row.note }}
        </p>

        <div v-if="props.movable" class="mt-2 flex gap-2">
            <button
                ref="upButton"
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-semibold text-ink disabled:opacity-40"
                :disabled="atTop"
                :aria-label="`Move ${props.row.name} up, position ${props.position} of ${props.count}`"
                @click="move(-1)"
            >
                Move up
            </button>
            <button
                ref="downButton"
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-semibold text-ink disabled:opacity-40"
                :disabled="atBottom"
                :aria-label="`Move ${props.row.name} down, position ${props.position} of ${props.count}`"
                @click="move(1)"
            >
                Move down
            </button>
        </div>
    </li>
</template>
