<script setup lang="ts">
/*
 * One skill row on the Skills Planner (plan §8 D13). Shared by the Required, Available and Learned
 * groups: the state word lives on the group's heading and its glyph, so a row never has to repeat
 * either. What a row prints is the skill's own facts, its stored base price, the raw conditions the
 * source states, and the fit cells - matches, does not match, not recorded - which compare nothing
 * the repository cannot source (PRD OQ-5 keeps style unclaimed).
 *
 * The move buttons are the only edit; when `movable` is false they do not render, and the reorder
 * itself lives in the page that owns the list order.
 */
import { computed, nextTick, ref } from 'vue';

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
        recorded: { status: string; turn: number | null; current_level: number | null } | null;
        conditions: string | null;
        fit: FitCell[];
        note: string | null;
    };
    position: number;
    count: number;
    movable: boolean;
}>();

const emit = defineEmits<{ move: [delta: number]; level: [value: string] }>();

function onLevel(event: Event): void {
    emit('level', (event.target as HTMLInputElement).value);
}

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
    <li class="card p-3">
        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <h4 class="text-lg font-semibold text-ink-strong">
                {{ props.row.name }}
                <span v-if="props.row.name_ja !== null" lang="ja" class="ml-1 font-normal text-ink-muted">{{ props.row.name_ja }}</span>
                <span
                    v-if="props.row.is_unique"
                    class="ml-1 rounded-full bg-sunken px-2 py-0.5 text-xs font-normal text-ink-strong"
                >✦ Unique <span class="font-normal text-ink-muted">· from the source's class code</span></span>
            </h4>
            <p class="font-mono text-xs tabular-nums text-ink-muted">
                <span v-if="props.row.sp_cost === null">N/A SP <span class="font-sans text-ink-muted">· the source publishes no cost</span></span>
                <template v-else>{{ props.row.sp_cost }} SP base</template>
            </p>
        </div>

        <p v-if="props.row.recorded !== null" class="mt-1 text-xs text-ink-muted">
            Recorded: {{ props.row.recorded.status }}
            <template v-if="props.row.recorded.turn !== null"> on turn {{ props.row.recorded.turn }}</template>
        </p>

        <!-- ADR-0029 D4: the level an acquired skill reached in this run, 1..6. Hint levels are a
             support-card fact and are not this row's. -->
        <label
            v-if="props.row.recorded !== null && props.row.recorded.status === 'Acquired'"
            class="mt-1 flex items-center gap-2 text-xs text-ink-muted"
        >
            <span>Level</span>
            <input
                type="number"
                min="1"
                max="6"
                class="min-h-11 w-20 rounded-[var(--radius-md)] border border-rule bg-raised px-2 text-sm text-ink"
                :value="props.row.recorded.current_level ?? ''"
                :aria-label="`Level for ${props.row.name}`"
                @change="onLevel"
            />
            <span>of 6</span>
        </label>

        <!-- The conditions are engine expressions a Trainer reads only when asking what gates a skill,
             so they sit behind a disclosure rather than in every row's scan line (plan 4.5). The
             `<code>` keeps the wrap classes that stop a long expression pushing the page sideways
             (A-94), and the summary keeps the native marker so the open/closed state stays visible. -->
        <details v-if="props.row.conditions !== null" class="mt-1 min-w-0 text-xs text-ink-muted">
            <summary class="min-h-11 cursor-pointer">Conditions</summary>
            <p class="mt-1 break-words">
                Conditions as the source states them: <code class="break-words whitespace-normal text-ink">{{ props.row.conditions }}</code>
            </p>
        </details>

        <dl class="mt-2 grid min-w-0 grid-cols-2 gap-x-4 gap-y-1 text-xs @3xl:grid-cols-4">
            <div v-for="cell in props.row.fit" :key="cell.key" class="flex min-w-0 flex-col">
                <dt class="font-semibold uppercase tracking-wide text-ink-muted">{{ cell.label }}</dt>
                <dd class="min-w-0 break-words text-ink-strong" :title="cell.title ?? undefined">{{ FIT_WORDS[cell.state] }}</dd>
            </div>
        </dl>

        <p v-if="props.row.note !== null" class="card mt-2 p-2 text-xs text-ink">
            {{ props.row.note }}
        </p>

        <div v-if="props.movable" class="mt-2 flex flex-wrap items-center gap-2">
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
            <span v-if="atTop || atBottom" class="text-xs text-ink-muted">{{ atTop ? 'First in the plan' : 'Last in the plan' }}</span>
        </div>
    </li>
</template>
