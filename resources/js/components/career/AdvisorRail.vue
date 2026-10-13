<script setup lang="ts">
/*
 * The advisor rail (screen-spec-2.0 §12, design-2.0 §23): the right column, and the only place on the
 * cockpit that makes a claim about what to do next.
 *
 * From FD-2 it is the rail, not the recommendation: `NextDecision.vue` renders the five-element answer
 * and this component supplies the heading, the region name, and the standing explanation that the
 * advisor ranks while the Trainer decides. The decision object is forwarded untouched.
 *
 * `Record<string, unknown>` with an `as never` on the child is the repo's own workaround, not slop:
 * TypeScript 7 ships no `lib/typescript.js`, so `@vue/compiler-sfc` cannot resolve an imported type
 * inside `defineProps<>` (see `resources/js/types.ts` and `Cockpit.vue`'s scenario forward).
 */
import NextDecision from './NextDecision.vue';

const props = withDefaults(
    defineProps<{
        decision: Record<string, unknown>;
        /** Forwarded to the decision's objectives; see `NextDecision.vue`'s `raceUrl`. */
        raceUrl?: string | null;
        /** Forwarded to FD-4's recorded-action card: the full action history is the Timeline's. */
        timelineUrl?: string | null;
    }>(),
    { raceUrl: null, timelineUrl: null },
);
</script>

<template>
    <!-- This section is the rail's own named region, and from `lg` it is also the scroll container
         the sticky column needs: a rail taller than the viewport scrolls inside itself instead of
         clipping, and the focusable-container treatment (DESIGN.md §2.3) keeps it keyboard-reachable
         rather than trackpad-only. The tab stop exists at every width, the way the race calendar's
         region already does. -->
    <section
        aria-labelledby="career-advisor-heading"
        class="rounded-md border border-rule bg-panel p-4 lg:max-h-[calc(100svh-1rem)] lg:overflow-y-auto"
        tabindex="0"
    >
        <h2 id="career-advisor-heading" class="text-lg font-semibold text-ink-strong">Advisor</h2>

        <NextDecision
            class="mt-3"
            :decision="props.decision as never"
            :race-url="props.raceUrl"
            :timeline-url="props.timelineUrl"
        />

        <p class="mt-3 text-xs text-ink-muted">
            The advisor ranks the next turn against the target you entered. It records nothing and acts
            for nobody: every action is yours to take.
        </p>
    </section>
</template>
