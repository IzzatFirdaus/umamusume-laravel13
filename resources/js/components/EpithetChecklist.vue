<script setup lang="ts">
import CapsuleHeader from './CapsuleHeader.vue';

// `enabled` is the resolved composesPanel('epithet_routes') (G-33). `seenRaceTitles` is
// run->completedRaceTitles() and `rows` is run->epithetProgress(); state, missing races and
// the aggregate `note` are all derived server-side against the config route table.
// Three states, three words: `unverifiable` must not fold into `open` (D-220, D-256).
type EpithetState = 'earned' | 'open' | 'unverifiable';

defineProps<{
    enabled: boolean;
    seenRaceTitles: string[];
    rows: {
        route: string;
        epithet: string;
        reward: string;
        state: EpithetState;
        missing: string[];
        note: string | null;
    }[];
}>();

const treatments: Record<EpithetState, string> = {
    earned: 'bg-green-tint text-ink border border-green-line',
    open: 'bg-sunken text-ink',
    unverifiable: 'bg-raised text-ink-muted border border-rule',
};
</script>

<template>
    <div v-if="enabled" class="rounded-md border border-rule bg-panel p-3">
        <CapsuleHeader title="Epithet routes" class="mb-3" />

        <p class="text-xs text-ink-muted">
            Derived
            <template v-if="seenRaceTitles.length === 0">
                from no entered races yet, so every named race below reads as outstanding
            </template>
            <template v-else>
                from {{ seenRaceTitles.length }} entered race {{ seenRaceTitles.length === 1 ? 'name' : 'names' }}:
                {{ seenRaceTitles.join(', ') }}
            </template>
            . Requirements are transcribed from the Trackblazer guide; nothing here is inferred
            about a race the tool cannot see.
        </p>

        <ul class="mt-3 flex flex-col gap-1.5" aria-label="Epithet routes">
            <li
                v-for="row in rows"
                :key="row.epithet"
                class="rounded-md border px-3 py-2 text-sm"
                :class="row.state === 'earned' ? 'border-green-line bg-green-tint' : 'border-rule bg-raised'"
            >
                <span class="flex flex-wrap items-baseline justify-between gap-x-3">
                    <span class="font-semibold text-ink-strong">{{ row.epithet }}</span>
                    <span class="shrink-0 rounded px-2 py-0.5 font-mono text-xs font-bold" :class="treatments[row.state]">
                        {{ row.state }}
                    </span>
                </span>
                <span class="mt-0.5 block text-xs text-ink-muted">
                    {{ row.route }} · {{ row.reward }}
                    <template v-if="row.note !== null">· needs {{ row.note }}, which this surface cannot read</template>
                    <template v-else-if="row.state !== 'earned' && row.missing.length > 0">· outstanding: {{ row.missing.join(', ') }}</template>
                </span>
            </li>
        </ul>
    </div>
</template>
