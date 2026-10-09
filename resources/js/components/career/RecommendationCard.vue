<script setup lang="ts">
/*
 * The recommendation card (SCREEN-009 §29/§30, design-2.0 §19). The screen's one accented element
 * (Selective Attention, plan §13).
 *
 * **The props are the plan's "Recommendation contract (D8)" plus one status line**: action, band,
 * reasons, alternative, risk, and `finaleContext`. There is no `score` and no numeric confidence, because `ADR-0001` §3 leaves both
 * unsourced and C2's `Advice` has no field for them; a sixth prop here would be the second place to be
 * wrong. `risk` is null in this slice — a delay or a reachability verdict is held computation.
 *
 * **It never acts for the Trainer.** The only control is the Trainer's own button, and it is an anchor
 * to the one entry the grid marks `RECOMMENDED` — one fixed id, so this card does not need to know
 * which action it recommended and the contract stays the contract. The card says what the advisor
 * thinks; where the Trainer records it is a separate, deliberate press (design-2.0 §2 "the
 * application never removes player agency").
 *
 * **A band is a word and a glyph, never a colour.** `●` and `▲` are distinct marks beside distinct
 * sentences, so the state survives a greyscale render (WCAG 1.4.1). Neither glyph is one of the four
 * provenance glyphs, which `ProvenanceBadge.vue` owns alone.
 *
 * **Energy absent is the refusal, printed.** The band is null exactly when the run has recorded no
 * Energy — that is C2's own first rule — so the card prints the sentence rather than a band read off
 * nothing.
 *
 * **The heading says "Recommendation", not "Best action".** The engine's verdict is the largest deficit
 * against the Trainer's own target (`TrainerAdvisor.php` rule 4), which is not a claim that the action is
 * best; `screen-spec-2.0.md:1321` prints the superlative as design intent and that file is reference
 * only, so its wording is not the shipped copy (`ADR-0020`, `PRD.md` FR-F-3).
 */
import { computed } from 'vue';

const props = defineProps<{
    action: string | null;
    band: 'AtOrAboveAdvisory' | 'BelowAdvisory' | null;
    reasons: string[];
    alternative: string | null;
    risk: string | null;
    /**
     * Where the run stands against its scenario's finale, computed server-side by `FinaleReader`. It is
     * context in the Trainer's reading order, never a sixth recommendation: the card still names one
     * action, and no rule in the engine covers concert preparation (`ADR-0020` §3). Words only, no
     * glyph and no colour, so it cannot be read as a second verdict beside the band mark.
     */
    finaleContext: { label: string; state: string; turns_away: number | null } | null;
}>();

const BANDS: Record<'AtOrAboveAdvisory' | 'BelowAdvisory', { glyph: string; word: string }> = {
    AtOrAboveAdvisory: { glyph: '●', word: 'At or above the advisory line' },
    BelowAdvisory: { glyph: '▲', word: 'Below the advisory line' },
};

/*
 * `computed`, not a plain `const`. Inertia patches the page in place, so this component keeps its
 * instance across a visit and its props change underneath it: a value derived once at setup would
 * stay frozen at the first render's band. That is exactly what happened here — a correction that
 * recorded Energy left the card printing the refusal while the reason lines beside it updated.
 */
const band = computed(() => (props.band === null ? null : BANDS[props.band]));
</script>

<template>
    <div class="rounded-md border-2 border-pick-line bg-raised p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Recommendation</h3>

        <p v-if="band === null" class="mt-2 text-sm text-ink-strong">
            Recommendation unavailable because Energy has not been entered.
        </p>

        <template v-else>
            <p class="mt-2 text-xl font-bold text-ink-strong">
                {{ props.action ?? 'No action to rank' }}
            </p>
            <p class="mt-1 text-sm text-ink">
                <span aria-hidden="true">{{ band.glyph }}</span> {{ band.word }}
            </p>
        </template>

        <template v-if="props.reasons.length > 0">
            <h3 class="mt-3 text-xs font-semibold uppercase tracking-wide text-ink-muted">Why</h3>
            <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-ink">
                <li v-for="(reason, index) in props.reasons" :key="index">{{ reason }}</li>
            </ul>
        </template>


        <p v-if="props.finaleContext !== null" class="mt-3 text-sm text-ink">
            <template v-if="props.finaleContext.state === 'next'">
                The {{ props.finaleContext.label }} is the next turn.
            </template>
            <template v-else-if="props.finaleContext.state === 'passed'">
                The {{ props.finaleContext.label }} has passed; no outcome is recorded.
            </template>
            <template v-else>
                The {{ props.finaleContext.label }} is {{ props.finaleContext.turns_away }} turns away.
            </template>
        </p>

        <p v-if="props.alternative !== null" class="mt-3 text-sm text-ink">
            Alternative: <span class="font-semibold text-ink-strong">{{ props.alternative }}</span>
        </p>

        <p v-if="props.risk !== null" class="mt-2 text-sm text-ink">
            Risk: <span class="text-ink-strong">{{ props.risk }}</span>
        </p>

        <a
            v-if="props.action !== null"
            href="#action-recommended"
            class="enamel mt-3 inline-flex min-h-11 items-center rounded-full bg-chrome px-5 text-sm font-bold text-on-chrome"
        >
            Go to the recommended action
        </a>
    </div>
</template>
