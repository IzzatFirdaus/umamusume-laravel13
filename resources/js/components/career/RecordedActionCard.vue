<script setup lang="ts">
/*
 * FD-4, the recorded-action card: the action the Trainer took, what this rail said before it, and what
 * the turn actually gave (`docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §9a FD-4, cited on this screen as
 * `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` §12, "SCREEN-009 — Career Cockpit").
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
 * **"Expected (from sources)" is a citation, not a second forecast.** The 2026-10-16 ruling authorises a
 * section that quotes what the reference publishes for the recorded action — all of a rest's branches,
 * none of an outing's magnitude, nothing at all for a discipline — with the server tag and the source
 * beside it. It never composes a number, and it sits after the rail's own lines rather than instead of
 * them, which is why the card now carries four sections and the rail's contract is untouched.
 *
 * Props are declared locally, and stay that way: handing `defineProps<>` a whole imported contract is
 * what this build refuses, because the compiler cannot expand imported members without an `fs` option
 * (gate TS7; `ProvenanceBadge.vue` carries the measured boundary). A type declared in this file, like
 * `Row` below, is not in that case at all.
 */
import AbsenceValue from '../AbsenceValue.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';

interface Row {
    text: string;
    badge: 'confirmed' | 'calculated';
    title: string;
}

interface Forecast {
    label: string;
    kind: 'reference' | 'absence';
    branches?: { name: string; value: string; note: string | null }[];
    statement?: string;
    server?: string;
    source?: string;
    disagreement?: string;
    reason?: string;
}

const props = withDefaults(
    defineProps<{
        action: string;
        recordedAt: string | null;
        expected: Row[];
        actual: Row[] | null;
        reconciliation: { match: boolean; note: string } | null;
        forecast?: Forecast | null;
        correctedFrom: number | null;
        timelineUrl: string | null;
    }>(),
    {
        recordedAt: null,
        forecast: null,
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
        class="p-4"
        :class="props.actual === null ? 'rounded-md border-2 border-rule bg-sunken' : 'pickcard'"
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
            Expected (from sources)
        </h4>
        <div v-if="props.forecast?.kind === 'reference'" class="mt-1">
            <p class="text-sm font-semibold text-ink-strong">{{ props.forecast.label }}</p>
            <dl v-if="props.forecast.branches !== undefined" class="mt-1 space-y-1">
                <div
                    v-for="(branch, index) in props.forecast.branches"
                    :key="`branch-${index}`"
                    class="flex flex-wrap items-baseline gap-x-2"
                >
                    <dt class="shrink-0 text-sm text-ink">{{ branch.name }}</dt>
                    <dd class="min-w-0 flex-1 break-words text-sm text-ink">
                        {{ branch.value }}<span v-if="branch.note !== null" class="text-ink-muted"> ({{ branch.note }})</span>
                    </dd>
                </div>
            </dl>
            <p v-else-if="props.forecast.statement !== undefined" class="mt-1 text-sm text-ink">
                {{ props.forecast.statement }}
            </p>
            <p v-if="props.forecast.server !== undefined" class="mt-1 text-xs text-ink-muted">
                {{ props.forecast.server }} · {{ props.forecast.source }}
            </p>
            <p v-if="props.forecast.disagreement !== undefined" class="mt-1 text-xs text-ink-muted">
                {{ props.forecast.disagreement }}
            </p>
        </div>
        <AbsenceValue
            v-else-if="props.forecast?.kind === 'absence'"
            :reason="props.forecast.reason ?? ''"
        />

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
