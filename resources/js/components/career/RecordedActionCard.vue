<script setup lang="ts">
/*
 * FD-4, the recorded-action card: the action the Trainer took, what this rail said before it, and what
 * the turn actually gave (`docs/proposals/frontend-development-plan.md` §9a FD-4, cited on this screen as
 * `screen-spec-2.0.md` §12, "SCREEN-009 — Career Cockpit").
 *
 * **Three states, and the card prints which one it is in.** An action with no entered result is greyed and
 * asks for the result in a polite `role="status"` rather than showing an empty comparison; an action with a
 * result prints forecast and actual side by side with the reconciliation note under them; a run with no
 * recorded action renders no card at all, because the parent has already left the slot empty. Absence is
 * never a zero here and never a dash (`AGENTS.md` §5).
 *
 * **`expected` is a quote, not a projection.** Every line arrives from the decision snapshot stored on the
 * row when the action was taken, so a card read three turns later says what was advised then, not what the
 * advisor would say now. The reconciliation compares the action recorded to the action recommended; it
 * never compares gains, because the forecast names a deficit to close and no source in this corpus
 * publishes what a training turn pays (`ADR-0003:92-95`, `ADR-0001` §3, `PRD.md` §6.11).
 *
 * **The agreement state is a word, not a mark.** `CareerBuildTargetTest` keeps the two sweepable trust
 * glyphs inside `ProvenanceBadge.vue` alone, so a second component printing one would split a vocabulary
 * across two owners and read as a provenance statement where there is none.
 *
 * Props are declared locally: `@vue/compiler-sfc` cannot resolve an imported type inside `defineProps`
 * (gate TS7), so the shape is written here and the page forwards its own typed object into it.
 */
import ProvenanceBadge from '../ProvenanceBadge.vue';

interface Row {
    text: string;
    badge: 'confirmed' | 'calculated';
    title: string;
}

const props = withDefaults(
    defineProps<{
        action: string;
        recordedAt: string | null;
        expected: Row[];
        actual: Row[] | null;
        reconciliation: { match: boolean; note: string } | null;
        correctedFrom: number | null;
        timelineUrl: string | null;
    }>(),
    {
        recordedAt: null,
        correctedFrom: null,
        timelineUrl: null,
    },
);

const when = (iso: string | null): string => {
    if (iso === null) {
        return 'not dated';
    }

    const stamp = new Date(iso);

    return Number.isNaN(stamp.getTime()) ? 'not dated' : stamp.toLocaleDateString();
};
</script>

<template>
    <section
        class="rounded-md border-2 p-4"
        :class="props.actual === null ? 'border-rule bg-sunken' : 'border-pick-line bg-raised'"
        aria-labelledby="recorded-action-heading"
    >
        <h3
            id="recorded-action-heading"
            class="text-lg font-semibold text-ink-strong"
        >
            Recorded action
        </h3>

        <p class="mt-1 text-lg font-bold text-ink-strong">{{ props.action }}</p>
        <p class="text-xs text-ink-muted">
            Recorded {{ when(props.recordedAt) }}<span v-if="props.correctedFrom !== null">,
                correcting row {{ props.correctedFrom }}</span>
        </p>

        <h4 class="mt-3 text-lg font-semibold text-ink-strong">
            What the rail said
        </h4>
        <dl v-if="props.expected.length > 0" class="mt-1 space-y-1">
            <div
                v-for="(row, index) in props.expected"
                :key="`expected-${index}`"
                class="flex flex-wrap items-baseline gap-x-2"
            >
                <dt class="shrink-0">
                    <ProvenanceBadge :state="row.badge" :title="row.title" />
                </dt>
                <dd class="min-w-0 flex-1 break-words text-sm text-ink">{{ row.text }}</dd>
            </div>
        </dl>
        <p v-else class="mt-1 text-sm text-ink-muted">
            The rail gave no benefit line for this action when it was taken.
        </p>

        <h4 class="mt-3 text-lg font-semibold text-ink-strong">
            What the turn gave
        </h4>

        <p
            v-if="props.actual === null"
            role="status"
            class="mt-1 text-sm text-ink-muted"
        >
            No result entered yet. Record what the turn gave and the comparison appears here.
        </p>
        <dl v-else class="mt-1 space-y-1">
            <div
                v-for="(row, index) in props.actual"
                :key="`actual-${index}`"
                class="flex flex-wrap items-baseline gap-x-2"
            >
                <dt class="shrink-0">
                    <ProvenanceBadge :state="row.badge" :title="row.title" />
                </dt>
                <dd class="min-w-0 flex-1 break-words text-sm text-ink">{{ row.text }}</dd>
            </div>
        </dl>

        <p
            v-if="props.reconciliation !== null"
            class="mt-3 flex flex-wrap items-baseline gap-x-2 text-sm text-ink"
        >
            <span class="shrink-0 font-bold text-ink-strong">
                {{ props.reconciliation.match ? 'Agreed' : 'Different' }}
            </span>
            <span class="min-w-0 flex-1 break-words">{{ props.reconciliation.note }}</span>
        </p>

        <a
            v-if="props.timelineUrl !== null"
            :href="props.timelineUrl"
            class="mt-3 inline-flex min-h-11 items-center text-sm font-semibold text-ink-strong underline"
        >
            Every action this run took
        </a>
    </section>
</template>
