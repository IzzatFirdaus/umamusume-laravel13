<script setup lang="ts">
import CapsuleHeader from './CapsuleHeader.vue';

// Self-gating panel: `enabled` is the controller's resolved composesPanel('team_rank_ladder'),
// never a scenario slug (G-33). The ladder arrives pre-flattened from config so the view holds
// no config lookup (D-240), and `current.level` is run->facilityLevel(rank) computed server-side.
defineProps<{
    enabled: boolean;
    current: { rank: string; level: number | null } | null;
    ladder: { rank: string; level: number }[];
}>();
</script>

<template>
    <div v-if="enabled" class="rounded-md border border-rule bg-panel p-3">
        <CapsuleHeader title="Team Rank" class="mb-3" />

        <p v-if="current === null" class="text-sm text-ink">
            <span class="font-bold text-ink">Rank not recorded</span>: the league letter is
            read off the client, and this run has none entered yet.
        </p>
        <p v-else class="flex flex-wrap items-baseline gap-x-3 text-sm">
            <span class="text-base font-bold text-ink-strong">{{ current.rank }}</span>
            <span class="text-ink">
                <template v-if="current.level === null">above the top rung: grants a second hint, no higher facility</template>
                <template v-else>
                    facility level {{ current.level }}
                    <span class="text-xs text-ink-muted">derived from the rank letter</span>
                </template>
            </span>
        </p>

        <ol class="mt-3 flex flex-wrap gap-1.5" aria-label="League ladder">
            <li
                v-for="rung in ladder"
                :key="rung.rank"
                class="rounded px-2 py-1 font-mono text-xs font-bold tabular-nums"
                :class="current?.rank === rung.rank ? 'bg-pick text-on-pick' : 'bg-sunken text-ink-muted'"
                :aria-current="current?.rank === rung.rank ? 'step' : undefined"
            >
                {{ rung.rank }}
                <span class="font-sans font-normal">{{ current?.rank === rung.rank ? 'current' : 'lv ' + rung.level }}</span>
            </li>
        </ol>

        <p class="mt-2 text-xs text-ink-muted">
            The ladder carries S+ above S: it grants a second hint rather than a higher facility,
            so it has no level to show.
        </p>
    </div>
</template>
