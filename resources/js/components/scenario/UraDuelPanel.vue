<script setup lang="ts">
/*
 * The Happy Meek duel panel (G3, `docs/URA-Finale-plan.md` §8), display-only.
 *
 * **Display-only is a ruling, not a gap.** `App\Enums\TurnChoice` records that the URA finale's duels
 * are display-only in this build (D-5), no column stores a duel, and this slice adds none. The
 * "This run" block is therefore empty on purpose and each row says why, so an empty panel never reads
 * as a duel that did not happen.
 *
 * **No win chance is numbered anywhere on this surface.** The client prints an icon beside each option
 * and no source publishes a percentage, so the rules row carries the icon's meaning and an `N/A`
 * marker, and no percent sign appears in the component. The same holds for the enhanced spark's rate
 * and the per-stat gain of a win: `docs/URA-Finale-plan.md` §11 names both as unconfirmed.
 *
 * **The surface is a native `<dialog>` opened with `showModal()`**, the treatment `RaceCard.vue`
 * settled on: the platform gives the focus trap, the inert background and Escape-to-close, and this
 * component adds only the focus return to the button that opened it.
 */
import { ref } from 'vue';
import AbsenceValue from '../AbsenceValue.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';

type Badge = 'confirmed' | 'calculated' | 'estimated' | 'unknown';

interface FactRow {
    label: string;
    value: string | null;
    badge: Badge;
    title: string;
}

interface HintRow {
    stat: string;
    name: string;
    sp_cost: string | null;
    badge: Badge;
    title: string;
}

const props = defineProps<{
    duel: {
        rules: FactRow[];
        hints: HintRow[];
        run: FactRow[];
    };
}>();

const dialog = ref<HTMLDialogElement | null>(null);
const opener = ref<HTMLButtonElement | null>(null);

function open(): void {
    dialog.value?.showModal();
}

// Fires for Escape and for every other close, so one handler covers the focus return.
function closed(): void {
    opener.value?.focus();
}

const headingId = 'ura-duel-panel';
</script>

<template>
    <div class="flex flex-col gap-2">
        <button
            ref="opener"
            type="button"
            class="inline-flex min-h-11 items-center self-start rounded-md border border-rule bg-raised px-3 text-xs font-medium text-ink-strong"
            @click="open"
        >
            Happy Meek duel panel
        </button>

        <dialog
            ref="dialog"
            class="ml-auto h-full max-h-none w-full max-w-md rounded-none border-l border-rule bg-page p-0 text-ink backdrop:bg-scrim"
            :aria-labelledby="headingId"
            @close="closed"
        >
            <div class="flex h-full flex-col gap-4 overflow-y-auto p-4">
                <div class="flex items-baseline justify-between gap-3">
                    <h3 :id="headingId" class="text-base font-semibold text-ink-strong">
                        Happy Meek duel
                    </h3>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-xs font-medium text-ink-strong"
                        @click="dialog?.close()"
                    >
                        Close
                    </button>
                </div>

                <p class="text-xs text-ink-muted">
                    Display-only in this build: the screen states the published rules and prints nothing
                    it has not been given, so it never decides a duel for the Trainer.
                </p>

                <section class="flex flex-col gap-2" aria-labelledby="ura-duel-rules">
                    <h4 id="ura-duel-rules" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        Published rules
                    </h4>

                    <dl class="flex flex-col gap-2">
                        <div
                            v-for="row in props.duel.rules"
                            :key="row.label"
                            class="flex flex-wrap items-baseline gap-x-2 gap-y-1 border-b border-rule pb-2 last:border-b-0"
                        >
                            <dt class="text-xs text-ink-muted">{{ row.label }}</dt>
                            <ProvenanceBadge :state="row.badge" :title="row.title" />
                            <dd v-if="row.value !== null" class="text-sm text-ink">{{ row.value }}</dd>
                            <dd v-else class="text-sm">
                                <AbsenceValue :reason="row.title" compact />
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="flex flex-col gap-2" aria-labelledby="ura-duel-hints">
                    <h4 id="ura-duel-hints" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        Racing Spirit hints
                    </h4>

                    <p class="text-xs text-ink-muted">
                        A hint is for the stat the duel was fought on, so the five rows below are the
                        names a win can point at. The sixth Racing Spirit skill belongs to mood rather
                        than to a stat a Trainer dueled on, and is not listed here.
                    </p>

                    <ul class="flex flex-col gap-2">
                        <li
                            v-for="row in props.duel.hints"
                            :key="row.stat"
                            class="flex flex-wrap items-baseline gap-x-2 gap-y-1"
                        >
                            <span class="text-xs font-medium text-ink-strong">{{ row.stat }}</span>
                            <span class="text-sm text-ink">{{ row.name }}</span>
                            <ProvenanceBadge :state="row.badge" :title="row.title" />
                            <span v-if="row.sp_cost !== null" class="font-mono text-xs text-ink">
                                {{ row.sp_cost }} SP
                            </span>
                            <AbsenceValue v-else :reason="row.title" compact />
                        </li>
                    </ul>
                </section>

                <section class="flex flex-col gap-2" aria-labelledby="ura-duel-run">
                    <h4 id="ura-duel-run" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        This run
                    </h4>

                    <dl class="flex flex-col gap-2">
                        <div
                            v-for="row in props.duel.run"
                            :key="row.label"
                            class="flex flex-col gap-1 border-b border-rule pb-2 last:border-b-0"
                        >
                            <div class="flex flex-wrap items-baseline gap-x-2">
                                <dt class="text-xs text-ink-muted">{{ row.label }}</dt>
                                <ProvenanceBadge :state="row.badge" :title="row.title" />
                            </div>
                            <AbsenceValue :reason="row.title" />
                        </div>
                    </dl>
                </section>
            </div>
        </dialog>
    </div>
</template>
