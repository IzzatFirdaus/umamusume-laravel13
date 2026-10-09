<script setup lang="ts">
import CapsuleHeader from './CapsuleHeader.vue';

// `enabled` is the resolved composesPanel('team_race') (G-33). The Blade's `$slots ??`
// fallback was dead twice over (undefined variable, and the value never rendered), so the
// controller supplies `entries` directly from raceEntries filtered on the team_race slot.
// title/tier nulls carry the "not recorded" copy below, so the server passes raw values.
// 06:109, transcribed into config as circles_guidance: three circles is a safety margin,
// not a win condition, and a loss lowers the league rank.
defineProps<{
    enabled: boolean;
    guidance: number;
    entries: {
        title: string | null;
        tier: string | null;
        circles: number | null;
        placement: number | null;
    }[];
}>();
</script>

<template>
    <div v-if="enabled" class="rounded-md border border-rule bg-panel p-3">
        <CapsuleHeader title="Team Race" class="mb-3" />

        <p class="text-sm text-ink">
            <span class="font-bold text-ink-strong">Aim for at least {{ guidance }} circles</span>
            <span class="text-ink-muted">as a margin, not a win condition. A loss lowers the league
                rank, and after the 2026-07-01 rework a loss can be retried with an Alarm Clock.</span>
        </p>

        <p v-if="entries.length === 0" class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="status">
            No team races recorded. Circles are entered on the race form as the number the
            client showed before you committed.
        </p>
        <ul v-else class="mt-3 flex flex-col gap-1.5" aria-label="Team races">
            <li
                v-for="(entry, index) in entries"
                :key="index"
                class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
            >
                <span class="font-semibold text-ink-strong">{{ entry.title ?? 'a team race with no calendar row' }}</span>
                <span class="flex flex-wrap gap-x-3 font-mono text-xs tabular-nums text-ink-muted">
                    <span v-if="entry.tier === null" title="The race catalogue records no tier for this race.">N/A</span>
                    <span v-else>{{ entry.tier }}</span>
                    <span>{{ entry.circles === null ? 'circles not read' : entry.circles + ' circles' }}</span>
                    <span v-if="entry.circles !== null">{{ entry.circles >= guidance ? 'at or above the margin' : 'below the margin' }}</span>
                    <span>{{ entry.placement === null ? 'no placement' : 'placed ' + entry.placement }}</span>
                </span>
            </li>
        </ul>
    </div>
</template>
