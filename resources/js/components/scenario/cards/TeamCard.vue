<script setup lang="ts">
/*
 * One card: the team's own identity (A6.6), as the Trainer entered it.
 *
 * Extracted from `UnityCupPanel.vue` for `S-RS-VISUAL-1` so the page can place each card in the
 * HFW's ruled order: the order interleaves cards from three components, which is impossible while
 * one component draws ten of them contiguously (`docs/design-research/scenario-detail-hfw-2026-11-09/`).
 * The markup, copy and data gates are carried over unchanged; the span moved to the composer, and
 * the capsule became the plain `h2` W.1 = C rules for this page (`DESIGN.md` §2.3 dated exception).
 */
defineProps<{
    team: {
        team_name: string | null;
        team_motto: string | null;
        team_league_placement: number | null;
        team_preseason_wins: number | null;
    };
}>();
</script>

<template>
    <div class="card p-3">
        <h2 class="text-lg font-semibold text-ink-strong">Team</h2>
        <dl class="mt-2 flex flex-col gap-1 text-sm">
            <div v-if="team.team_name !== null" class="flex items-baseline justify-between gap-3">
                <dt class="text-ink-muted">Name</dt>
                <dd class="text-ink-strong">{{ team.team_name }}</dd>
            </div>
            <div v-if="team.team_motto !== null" class="flex items-baseline justify-between gap-3">
                <dt class="text-ink-muted">Motto</dt>
                <dd class="text-ink">{{ team.team_motto }}</dd>
            </div>
            <div v-if="team.team_league_placement !== null" class="flex items-baseline justify-between gap-3">
                <dt class="text-ink-muted">League placement</dt>
                <dd class="font-mono tabular-nums text-ink-strong">{{ team.team_league_placement }}</dd>
            </div>
            <div v-if="team.team_preseason_wins !== null" class="flex items-baseline justify-between gap-3">
                <dt class="text-ink-muted">Preseason rounds won</dt>
                <dd class="font-mono tabular-nums text-ink-strong">{{ team.team_preseason_wins }}</dd>
            </div>
        </dl>
    </div>
</template>
