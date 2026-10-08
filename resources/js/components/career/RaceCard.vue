<script setup lang="ts">
/*
 * One race on the Race Decision screen (SCREEN-011, `SCR-CAR-013`). A summary card, the brief's field
 * list, and a drawer holding the two actions the brief names.
 *
 * **Nothing here predicts the race.** There is no win figure, no percentage and no readiness band:
 * the brief's risk thresholds (`< 10`, `10-30`, `> 30`) are percentages, and race prediction is held
 * on `ADR-0016`, which is an open question rather than a permission. The readiness row prints `N/A`
 * with the reason, and the card carries no percent sign at all.
 *
 * **The drawer is a native `<dialog>` opened with `showModal()`.** That is the one place the platform
 * already gives a focus trap and Escape-to-close, so the keyboard behaviour the slice is accepted on
 * is the browser's rather than a hand-rolled one; what this component adds is the focus return to the
 * button that opened it. `design-2.0` §32 calls the surface a drawer and lists race details among the
 * things it is for; the mechanism is a modal dialog, chosen for the trap.
 *
 * **A mandatory race is an obligation, not a Goal.** `is_mandatory` is a career rule (the debut, the
 * qualifier, the semifinal, the scenario finals), and `79ffad5` withdrew it as the Goal pennant's
 * input because the client's red banner means this character's objective. The marker therefore says
 * "Mandatory", never "Goal".
 */
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    race: Race;
    readiness: { label: string; title: string };
    entry: { action: string; turns: { id: number; turn: number }[] };
    skipUrl: string;
}>();

const dialog = ref<HTMLDialogElement | null>(null);
const opener = ref<HTMLButtonElement | null>(null);

function open(): void {
    // `showModal()` gives the focus trap, the inert background and Escape-to-close; the browser
    // moves focus to the first focusable descendant itself.
    dialog.value?.showModal();
}

// Fires for Escape and for every other close, so one handler covers the focus return.
function closed(): void {
    opener.value?.focus();
}

const form = useForm({ turn_entry_id: '' });

// The decision the screen exists for: enter the race. The run's own panel records the outcome.
function enter(): void {
    form
        .transform(() => ({
            entry_mode: 'calendar',
            race_catalog_slot_id: String(props.race.id),
            status: 'Entered',
            turn_entry_id: form.turn_entry_id === '' ? null : form.turn_entry_id,
        }))
        .post(props.entry.action, { preserveScroll: true });
}

const group = (n: number): string => n.toLocaleString('en-US');
</script>

<template>
    <li class="rounded-md border border-rule bg-panel p-4">
        <!-- A slot rather than a prop, because the only caller that fills it is the planner's
             comparison picker (`RacePlanList.vue`), and a card with no selection control must render
             exactly as D10 left it. Unfilled, this contributes nothing. -->
        <slot name="select" />

        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <h3 class="text-base font-semibold text-ink-strong">{{ props.race.title }}</h3>
            <p class="flex flex-wrap items-baseline gap-x-2 text-xs text-ink-muted">
                <span v-if="props.race.is_mandatory" class="font-bold text-ink-strong">
                    <span aria-hidden="true">!</span> Mandatory
                </span>
                <span v-else-if="props.race.is_special" class="font-bold text-ink-strong">
                    <span aria-hidden="true">!</span> Special
                </span>
                <span v-if="props.race.tier !== null">{{ props.race.tier }}</span>
                <span>{{ props.race.year_label }}</span>
                <span v-if="props.race.turn !== null">Turn {{ props.race.turn }}</span>
            </p>
        </div>

        <!-- The catalogue's own gate, which is a quantity to work toward rather than a lock. -->
        <p v-if="props.race.fans_needed !== null" class="mt-2 text-sm text-ink">
            Fan gate: <span class="font-mono tabular-nums">{{ group(props.race.fans_needed) }}</span> fans.
        </p>
        <p v-if="props.race.maiden_gated" class="mt-1 text-sm text-ink">
            Maiden rule: this race opens only once the trainee has won one.
        </p>

        <!-- What the run has already done about this slot, when it has done anything. -->
        <p v-if="props.race.status !== null" class="mt-2 text-sm text-ink">
            Recorded: <span class="font-semibold text-ink-strong">{{ props.race.status }}</span>
            <template v-if="props.race.placement !== null"> · {{ props.race.placement }}</template>
            <template v-if="props.race.fans_gain !== null"> · {{ group(props.race.fans_gain) }} fans</template>
            <template v-if="props.race.grade_points !== null"> · {{ group(props.race.grade_points) }} Grade Points</template>
        </p>

        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-sm sm:grid-cols-3">
            <div v-for="fact in props.race.facts" :key="fact.key" class="flex flex-col">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ fact.label }}</dt>
                <dd
                    class="text-ink-strong"
                    :title="fact.title ?? undefined"
                >
                    {{ fact.value ?? 'N/A' }}
                </dd>
            </div>
        </dl>

        <button
            ref="opener"
            type="button"
            class="mt-3 inline-flex min-h-11 items-center rounded-md border-2 border-rule px-4 text-sm font-bold text-ink-strong"
            @click="open"
        >
            Details and actions for {{ props.race.title }}
        </button>

        <dialog
            ref="dialog"
            class="ml-auto h-full max-h-none w-full max-w-md rounded-none border-l border-rule bg-page p-0 text-ink backdrop:bg-scrim"
            @close="closed"
        >
            <div class="flex h-full flex-col gap-4 overflow-y-auto p-4">
                <div class="flex items-baseline justify-between gap-3">
                    <h3 class="text-base font-semibold text-ink-strong">{{ props.race.title }}</h3>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center rounded-md px-3 text-sm font-semibold text-ink underline"
                        @click="dialog?.close()"
                    >
                        Close
                    </button>
                </div>

                <dl class="flex flex-col gap-2 text-sm">
                    <div v-for="fact in props.race.facts" :key="fact.key">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ fact.label }}</dt>
                        <dd class="text-ink-strong" :title="fact.title ?? undefined">{{ fact.value ?? 'N/A' }}</dd>
                    </div>
                </dl>

                <!-- The one figure the brief asks for that this tool must not state. -->
                <p class="rounded-md border border-rule bg-raised p-3 text-sm">
                    Readiness:
                    <span class="font-semibold text-ink-strong" :title="props.readiness.title">{{ props.readiness.label }}</span>
                </p>

                <form class="flex flex-col gap-3" @submit.prevent="enter">
                    <label v-if="props.entry.turns.length > 0" class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-ink">Turn this race was run on (optional)</span>
                        <select v-model="form.turn_entry_id" name="turn_entry_id" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">not tied to a turn</option>
                            <option v-for="turn in props.entry.turns" :key="turn.id" :value="String(turn.id)">Turn {{ turn.turn }}</option>
                        </select>
                    </label>

                    <p v-if="form.hasErrors" role="alert" class="rounded-md border border-risk bg-raised p-2 text-sm text-risk">
                        {{ Object.values(form.errors)[0] }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-5 text-sm font-bold text-on-chrome disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Entering…' : 'Enter Race' }}
                        </button>
                        <a :href="props.skipUrl" class="inline-flex min-h-11 items-center rounded-md px-3 text-sm font-semibold text-ink underline">
                            Skip for now
                        </a>
                    </div>

                    <p class="text-xs text-ink-muted">
                        Skip records nothing and returns to the run screen. A finish, a fan gain and a
                        placement are outcomes rather than decisions, so they are recorded there.
                    </p>
                </form>
            </div>
        </dialog>
    </li>
</template>
