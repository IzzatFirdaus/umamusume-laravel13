<script setup lang="ts">
/*
 * The Cockpit's condensed status strip (plan slice 4.2). It is a SECOND READING of facts the page already
 * holds, never a second owner of them: every value here comes from `props.header`, which the identity card
 * and the state panel read from the same payload.
 *
 * **The strip does not carry the primary action.** Ruling 2 (2026-10-20) keeps the one
 * `bg-primary-action` CTA in the Advisor rail (`NextDecision.vue`), where it sits beside the reasoning
 * that justifies it. The strip shows an OUTLINE affordance that scrolls to and focuses that rail, so the
 * recommendation is reachable from the top of a long page without the page acquiring a second primary.
 * Measured consequence: the primary count is per-state, exactly one in a recommendation-bearing state and
 * exactly zero on a fresh career, and both are asserted rather than assumed.
 *
 * **Sticky choice.** It was written to pin at `top-0`, and that is REMOVED rather than left in place:
 * measured, `position: sticky` on this element computes and then does nothing, because its ancestor
 * `main` carries `container-type: inline-size` and no ancestor is a scroll container — the sticky
 * containing block moves with the page, so the strip drifts by exactly the scroll distance. Removing the
 * class would not change that, which is what made the stickiness assertion worthless. The same ancestry
 * wraps the "On this page" nav, whose docblock has always claimed `md:sticky`. Stickiness is
 * BLOCKED-ON-CONTAINMENT and recorded in the ledger. The class is absent rather than present-and-inert so
 * the source does not claim a behaviour the page does not have.
 *
 * The negative `-mx-6`/`px-6` pair the wayfinder nav uses is also deliberately absent: on this element it
 * widened the box past its container and produced a horizontal scroll at 320px, which three layout cases
 * caught.
 *
 * An unrecorded value renders as an absence with its reason, through `AbsenceValue`, rather than as a
 * bare marker: the same contract the state panel uses, and the shape `UiHygieneTest` accepts.
 */
import AbsenceValue from '../AbsenceValue.vue';

const props = defineProps<{
    /** The turn the run stands on. Always a number; turn 0 is a run with no logged turn. */
    turn: number;
    /** Energy as the header prints it (a figure, a band word, or null). */
    energyText: string | null;
    mood: string | null;
    fans: number | null;
    skillPoints: number | null;
    /**
     * The action the advisor recommends, as its own words ("Train Speed"), or null when the rail has
     * nothing to recommend. Read from the rail; this component never composes the sentence.
     */
    recommended: string | null;
}>();

const group = (n: number): string => n.toLocaleString('en-US');

// The reason wording is the one the identity card already owns, so a Trainer reads the same sentence
// wherever an absence appears.
const absent = (what: string): string => `${what} has not been recorded for this run.`;

/*
 * Scroll to and focus the rail. The rail is `data-region="advisor"`; its own recommendation CTA is the
 * first anchor inside it, so focusing that lands a keyboard Trainer on the action rather than on the
 * region's wrapper. Falls back to the region itself when no CTA exists, which is the no-recommendation
 * state where this button is not rendered at all.
 */
function revealAdvisor(): void {
    const rail = document.querySelector<HTMLElement>('[data-region="advisor"]');

    if (rail === null) {
        return;
    }

    rail.scrollIntoView({ block: 'start', behavior: 'smooth' });
    (rail.querySelector<HTMLElement>('a[href], button') ?? rail).focus();
}
</script>

<template>
    <section
        aria-label="Run summary"
        class="mb-3 border-b border-rule bg-page py-2"
    >
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm" data-region="summary-values">
            <span class="font-semibold text-ink-strong">Turn {{ props.turn }}</span>

            <span class="text-ink">
                Energy:
                <span v-if="props.energyText !== null" class="font-mono tabular-nums text-ink-strong">{{ props.energyText }}</span>
                <AbsenceValue v-else :reason="absent('Energy')" compact />
            </span>

            <span class="text-ink">
                Mood:
                <span v-if="props.mood !== null" class="text-ink-strong">{{ props.mood }}</span>
                <AbsenceValue v-else :reason="absent('Mood')" compact />
            </span>

            <span class="text-ink">
                Fans:
                <span v-if="props.fans !== null" class="font-mono tabular-nums text-ink-strong">{{ group(props.fans) }}</span>
                <AbsenceValue v-else :reason="absent('A Fan count')" compact />
            </span>

            <span class="text-ink">
                Skill Points:
                <span v-if="props.skillPoints !== null" class="font-mono tabular-nums text-ink-strong">{{ group(props.skillPoints) }}</span>
                <AbsenceValue v-else :reason="absent('A Skill Point count')" compact />
            </span>

            <!--
                The recommendation, as an OUTLINE affordance rather than a filled button. It is a control,
                not a claim: it moves the Trainer to the rail's own CTA.
            -->
            <button
                v-if="props.recommended !== null"
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border-2 border-rule px-3 text-sm font-semibold text-ink-strong"
                @click="revealAdvisor"
            >
                Recommended: {{ props.recommended }}
            </button>
            <!--
                No dash: `RenderedCopyHygieneTest` bans the em and en dash in shipped copy, and the
                dispatch's own draft copy used one. A full stop reads the same.
            -->
            <span v-else class="text-ink-muted">No recommendation. See the advisor.</span>
        </div>
    </section>
</template>
