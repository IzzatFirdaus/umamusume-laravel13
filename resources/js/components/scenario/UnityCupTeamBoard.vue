<script setup lang="ts">
/*
 * The team race's published shape, one row per category (G4).
 *
 * **The table states an ask and a blank, not a coverage verdict.** A team race is five races, one per
 * category, and three are fielded in each — `docs/scenarios/02-unity-cup.md:149`, repeated at
 * `SCENARIO-PUBLISHER-REFERENCES.md:1028`. Dirt is one of the five and is a surface rather than a
 * distance band, which is why these rows are not the four distance bands the build-target form uses:
 * keying the board off that list would drop a fifth of the race it describes. No column records who
 * this run puts in a category, so every row reads `N/A` with the reason named once, and no row is
 * called covered or short. Counting the trainee's own race entries here would answer a different
 * question and be presented as this one.
 *
 * **Native table rows, no flex on the row.** `display: flex` on a `<tr>` costs it the row role in the
 * accessibility tree, and this region is axe-scanned in `unity-cup-panel.spec.ts`. The columns are one
 * short word and one marker, so 320px is met without restructuring the table.
 *
 * **No focusable control**, per the owner's ruling of 2026-10-11 that keeps this region free of them.
 * That is why an empty cell uses the `N/A`-plus-visible-sentence pattern `UraPanel` already uses rather
 * than `AbsenceValue`: that component is a native disclosure, and its summary is a tab stop, which is
 * precisely what this region's committed keyboard case refuses. The reason still reads on the page, which
 * is the half of its purpose that matters here; the disclosure is the half this surface cannot spend.
 * `unity-cup-panel.spec.ts` counts the disclosures in the region to keep the claim honest.
 */
import CapsuleHeader from '../CapsuleHeader.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import type { ProvenanceState } from '../../types';

type Badge = Badge;

interface CoverageRow {
    category: string;
    asks: string;
    fielded: string | null;
}

const props = defineProps<{
    coverage: {
        rows: CoverageRow[];
        badge: Badge;
        source_title: string;
        absence_title: string;
    };
}>();

const headingId = 'unity-cup-team-coverage';
</script>

<template>
    <section
        v-if="props.coverage.rows.length > 0"
        class="card p-3"
        :aria-labelledby="headingId"
    >
        <CapsuleHeader title="Team race coverage" class="mb-3" />
        <h4 :id="headingId" class="sr-only">
            Team race coverage
        </h4>

        <table class="mt-1 w-full text-sm">
            <caption class="mt-1 text-left text-xs text-ink-muted">
                One race per category, {{ props.coverage.rows[0].asks }} in each. The reading this run has
                for a category is who it fields there, and no column stores it.
            </caption>
            <thead>
                <tr class="border-b border-rule text-left">
                    <th scope="col" class="py-1 text-xs font-medium text-ink-muted">Category</th>
                    <th scope="col" class="py-1 text-right text-xs font-medium text-ink-muted">
                        This run fields
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="row in props.coverage.rows"
                    :key="row.category"
                    class="border-b border-rule last:border-b-0"
                >
                    <th scope="row" class="py-1 text-left align-top text-xs font-medium text-ink-strong">
                        {{ row.category }}
                    </th>
                    <td class="py-1 text-right align-top font-mono text-sm text-ink-muted">N/A</td>
                </tr>
            </tbody>
        </table>

        <!-- One sentence for five identical absences: an unrecorded reading is `N/A` with its reason
             printed on the page, never a zero and never a hover-only note (D-220). -->
        <p class="mt-1 text-xs text-ink-muted">{{ props.coverage.absence_title }}</p>

        <p class="mt-2 flex flex-wrap items-baseline gap-x-2 text-xs text-ink-muted">
            <ProvenanceBadge :state="props.coverage.badge" :title="props.coverage.source_title" />
            <span>{{ props.coverage.source_title }}</span>
        </p>
    </section>
</template>
