<script setup lang="ts">
import { computed } from 'vue';

/*
 * C4, the inherited-spark tally: one table across the six nodes, so a Spark carried by two
 * grandparents in different branches is visible at a glance.
 *
 * It is a tally and nothing else: every row is a Spark instance a node's own record holds, sorted
 * by family then star count descending, and the note states the boundary in the tool's own voice -
 * the tally does not predict what the inheritance will yield, and no probability, odds or
 * percentage is printed anywhere in the component. The graph prop is the same payload the six-node
 * read-back prints, so this table and the graph cannot disagree about what a node carries.
 */
const props = defineProps<{
    graph: {
        trainee: { name: string | null };
        parents: {
            label: string;
            name: string | null;
            sparks: { kind_label: string; target: string | null; stars: number | null }[];
            ancestors: {
                slot: string;
                name: string | null;
                sparks: { kind_label: string; target: string | null; stars: number | null }[];
            }[];
        }[];
    };
}>();

/** One row per Spark instance: the Spark's own word, its family, its stars, and the node carrying it. */
const rows = computed(() => {
    const carried: { spark: string; family: string; stars: number | null; carriedBy: string }[] = [];

    for (const parent of props.graph.parents) {
        const parentName = parent.name ?? `${parent.label}, not recorded`;

        for (const spark of parent.sparks) {
            carried.push({
                spark: spark.target ?? 'Not recorded',
                family: spark.kind_label,
                stars: spark.stars,
                carriedBy: parentName,
            });
        }

        for (const ancestor of parent.ancestors) {
            const ancestorName = ancestor.name ?? `${ancestor.slot}, not recorded`;

            for (const spark of ancestor.sparks) {
                carried.push({
                    spark: spark.target ?? 'Not recorded',
                    family: spark.kind_label,
                    stars: spark.stars,
                    carriedBy: ancestorName,
                });
            }
        }
    }

    // Family order, then star count descending, so the family a Trainer is comparing reads top-down;
    // a null star count is unrecorded, which sorts last inside its family rather than as a zero.
    return carried.sort((a, b) =>
        a.family === b.family
            ? (b.stars ?? -1) - (a.stars ?? -1)
            : a.family.localeCompare(b.family),
    );
});
</script>

<template>
    <section aria-labelledby="spark-summary-heading">
        <h3 id="spark-summary-heading" class="text-base font-semibold text-ink-strong">
            Sparks carried by this pair
        </h3>
        <p class="mt-1 text-xs text-ink-muted">
            This is a tally of sparks carried by the pair. It does not predict what the
            inheritance will yield.
        </p>

        <p v-if="rows.length === 0" class="mt-3 text-sm text-ink-muted">
            No sparks are recorded on any of the six nodes yet, so there is nothing to tally.
        </p>

        <div v-else class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="Sparks carried by this pair, scrollable">
            <table class="t">
                <thead>
                    <tr>
                        <th scope="col">Spark</th>
                        <th scope="col">Family</th>
                        <th scope="col">Stars</th>
                        <th scope="col">Carried by</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in rows" :key="`${row.carriedBy}-${row.spark}-${index}`">
                        <td>{{ row.spark }}</td>
                        <td>{{ row.family }}</td>
                        <td>
                            <span v-if="row.stars !== null">★{{ row.stars }}</span>
                            <span v-else title="No star count was recorded for this spark.">N/A</span>
                        </td>
                        <td>{{ row.carriedBy }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
