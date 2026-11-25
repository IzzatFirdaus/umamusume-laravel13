<script setup lang="ts">
export interface Portfolio {
    strengths: { label: string; value: number }[];
    current_stats: { label: string; value: number }[];
    snapshot_turn: number | null;
    fit: { surfaces: string[]; distances: string[]; styles: string[]; skill_distances: string[] };
    progression_inferred: boolean;
    turns: number;
    races: number;
    events: number;
    deck_slots: number;
    legacy_recorded: boolean;
    deck_url: string;
    inheritance_url: string;
    timeline_url: string;
    source: string | null;
}

defineProps<{ portfolio: Portfolio; active?: boolean }>();

const list = (values: string[]): string => values.length > 0 ? values.join(' · ') : 'N/A';
</script>

<template>
    <section aria-labelledby="veteran-portfolio-heading" class="card mt-4">
        <h2 id="veteran-portfolio-heading" class="text-lg font-semibold text-ink-strong">{{ active ? 'Career portfolio' : 'Veteran portfolio' }}</h2>
        <template v-if="active">
            <p class="mt-1 text-sm text-ink-muted">
                {{ portfolio.snapshot_turn === null ? 'This career is in progress. Log a turn to add its first stats.' : `Current build at the last recorded turn, Turn ${portfolio.snapshot_turn}.` }}
            </p>
            <dl v-if="portfolio.current_stats.length > 0" aria-label="Current stats" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                <div v-for="stat in portfolio.current_stats" :key="stat.label" class="rounded-md border border-rule bg-raised p-2">
                    <dt class="text-xs text-ink-muted">{{ stat.label }}</dt>
                    <dd class="font-mono tabular-nums text-ink-strong">{{ stat.value }}</dd>
                </div>
            </dl>
        </template>

        <div class="mt-3 grid gap-4 md:grid-cols-3">
            <div>
                <h3 class="font-semibold text-ink-strong">Build strengths</h3>
                <p v-if="portfolio.strengths.length === 0" class="mt-1 text-sm text-ink-muted">{{ active ? 'Current stats are unavailable.' : 'Final stats are unavailable.' }}</p>
                <dl v-else class="mt-1 text-sm">
                    <div v-for="stat in portfolio.strengths" :key="stat.label" class="flex justify-between gap-2">
                        <dt>{{ stat.label }}</dt>
                        <dd class="font-mono tabular-nums">{{ stat.value }}</dd>
                    </div>
                </dl>
                <p v-if="portfolio.strengths.length > 0" class="mt-2 text-sm text-ink-muted">
                    For a similar build, consider support focused on {{ portfolio.strengths.map(stat => stat.label).join(' and ') }}. This suggestion uses the highest values in the latest snapshot.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-ink-strong">Estimated build fit</h3>
                <dl class="mt-1 text-sm">
                    <div><dt class="inline font-medium">Surface</dt> <dd class="inline" title="From catalogue aptitudes graded A or S">{{ list(portfolio.fit.surfaces) }}</dd></div>
                    <div><dt class="inline font-medium">Distance</dt> <dd class="inline" title="From catalogue aptitudes graded A or S">{{ list(portfolio.fit.distances) }}</dd></div>
                    <div><dt class="inline font-medium">Style</dt> <dd class="inline" title="From catalogue aptitudes graded A or S">{{ list(portfolio.fit.styles) }}</dd></div>
                </dl>
                <p v-if="portfolio.fit.skill_distances.length > 0" class="mt-2 text-sm">Learned distance-specific skills support {{ list(portfolio.fit.skill_distances) }}.</p>
                <p class="mt-2 text-sm text-ink-muted">Based on catalogue aptitudes and learned skills. Actual race settings require a race record.</p>
            </div>

            <div>
                <h3 class="font-semibold text-ink-strong">Career evidence</h3>
                <p class="mt-1 text-sm">{{ portfolio.turns }} turn snapshots · {{ portfolio.races }} completed race records · {{ portfolio.events }} recorded events</p>
                <p v-if="portfolio.progression_inferred" class="mt-2 text-sm">
                    <span class="font-semibold">Inferred progression</span>
                    The recorded Final placing suggests the Qualifier and Semifinal were cleared under the rules in the URA Finale scenario guide.
                </p>
                <ul class="mt-2 flex flex-wrap gap-x-3 text-sm">
                    <li><a :href="portfolio.deck_url" class="inline-flex min-h-11 items-center underline">{{ portfolio.deck_slots > 0 ? `${portfolio.deck_slots} saved deck slots` : 'Deck unrecorded' }}</a></li>
                    <li><a :href="portfolio.inheritance_url" class="inline-flex min-h-11 items-center underline">{{ portfolio.legacy_recorded ? 'Saved Legacy details' : 'Legacy unrecorded' }}</a></li>
                    <li><a :href="portfolio.timeline_url" class="inline-flex min-h-11 items-center underline">Career Timeline</a></li>
                </ul>
                <p v-if="portfolio.source" class="mt-2 break-words text-xs text-ink-muted">Imported from {{ portfolio.source }}</p>
            </div>
        </div>
    </section>
</template>
