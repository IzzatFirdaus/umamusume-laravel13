<script setup lang="ts">
/*
 * What a team race is made of, as far as the sources carry it (G4).
 *
 * **The block stops where the corpus stops.** The cadence and its two month anchors are published, the
 * five-category shape is published, and clearing the set is published as a rise on every stat sized by
 * performance — the size itself is published nowhere, so it is quoted as the guides state it and no
 * figure is attached. Which opponent is stronger is not published at all, and that is the line the
 * brief asks for rather than a preview: no chance, no favoured side, and no standing a win would bring.
 *
 * **The next round has no turn.** `SCENARIO-PUBLISHER-REFERENCES.md:1186` records the per-round turn
 * count as unverified, so the round is anchored to the months the guides name and the turn reads `N/A`
 * with that reason. A derived turn from a 24-turn grid would be the tool inventing a calendar date.
 *
 * **The opponent names the matrix carries are not printed.** Measured 2026-10-11, two of the three
 * appear in no document this repository holds, and the third is named differently where it does appear.
 * Putting them on screen would launder an untraced string into a client fact, so the block states the
 * shape and the absence and leaves the list in config.
 *
 * No focusable control, per the owner's ruling of 2026-10-11 that keeps the panel region free of them,
 * which is why an absent figure here is `N/A` with its sentence printed rather than an `AbsenceValue`
 * disclosure: a `<summary>` is a tab stop, and this region's keyboard case counts them.
 */
import ProvenanceBadge from '../ProvenanceBadge.vue';

type Badge = 'confirmed' | 'calculated' | 'estimated' | 'unknown';

const props = defineProps<{
    tournament: {
        cadence_months: number | null;
        month_anchors: string[];
        category_count: number;
        clear_all_reward: string | null;
        badge: Badge;
        source_title: string;
        projection: string | null;
        projection_title: string;
        next: string | null;
        next_title: string;
        risks: string[];
    };
}>();

const headingId = 'unity-cup-tournament-readiness';

/** The anchors as the guides phrase them: "Late June and Late December". */
function anchorLine(): string {
    return props.tournament.month_anchors.join(' and ');
}
</script>

<template>
    <section
        class="rounded-md border border-rule bg-panel p-3"
        :aria-labelledby="headingId"
    >
        <h4 :id="headingId" class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
            Team race shape
        </h4>

        <p class="mt-2 flex flex-wrap items-baseline gap-x-2 text-sm text-ink">
            <ProvenanceBadge :state="props.tournament.badge" :title="props.tournament.source_title" />
            <span>
                Arrives every {{ props.tournament.cadence_months }} months, at {{ anchorLine() }}.
                {{ props.tournament.category_count }} races, one per category.
            </span>
        </p>

        <p v-if="props.tournament.clear_all_reward !== null" class="mt-1 text-sm text-ink">
            Clearing the set pays {{ props.tournament.clear_all_reward }}. No source publishes the size,
            so none is shown.
        </p>

        <!-- `dt`/`dd` siblings directly under the `dl`. The first version wrapped each pair in a `div` and
             left the `dd` outside it, which made the `dl`'s child list a `div` followed by a bare `dd`:
             axe's `definition-list` rule (wcag2a, SC 1.3.1) reads that as an improperly-ordered list and
             failed the committed scan. The badge, the marker and the reason all ride inside the `dd`. -->
        <!-- Both answers below are absences, so neither row carries a provenance badge (owner ruling
             2a): the `N/A` marker and the reason sentence are the whole statement. -->
        <dl class="mt-2 grid grid-cols-[minmax(0,10rem)_minmax(0,1fr)] items-baseline gap-x-3 gap-y-2">
            <dt class="text-xs text-ink-muted">Opponent projection</dt>
            <dd class="flex flex-wrap items-baseline gap-x-2">
                <span class="font-mono text-sm text-ink-muted">N/A</span>
                <span class="w-full text-xs text-ink-muted">{{ props.tournament.projection_title }}</span>
            </dd>

            <dt class="text-xs text-ink-muted">Next team race</dt>
            <dd class="flex flex-wrap items-baseline gap-x-2">
                <span class="font-mono text-sm text-ink-muted">N/A</span>
                <span class="w-full text-xs text-ink-muted">{{ props.tournament.next_title }}</span>
            </dd>
        </dl>

        <ul v-if="props.tournament.risks.length > 0" class="mt-2 flex flex-col gap-1">
            <li
                v-for="risk in props.tournament.risks"
                :key="risk"
                class="text-xs text-ink-muted"
            >
                {{ risk }}
            </li>
        </ul>
    </section>
</template>
