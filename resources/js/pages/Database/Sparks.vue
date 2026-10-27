<script setup lang="ts">
/*
 * The Sparks reference view (SCREEN-023, `SCR-SYS-010`). The Spark categories from
 * `docs/UMAMUSUME_REFERENCE.md` Â§1.5.2 and the star-roll odds from Â§1.5.3.
 *
 * **The category colour is Â§18's spark identity, paired with the word.** Blue, Pink, Green and White
 * are carried as an accent chip beside the category name, never as the only difference between two
 * rows: the category word and the global name both print.
 *
 * **The record counts are cited, and one of them is the guide's own reading.** The Spark export does
 * exist on a developed machine (`research-scratch/data/json/factors.json`) but it is gitignored and no
 * runtime path reads it, so the six counts are transcribed from the guide rather than re-derived. Five
 * of the six are cells in that export (5, 10, 452, 37, 34). The sixth, 268, is the guide reading part
 * of its 336-record bucket, which is why the 806 these rows sum to is the described total while the
 * export holds 874. The view names that difference instead of leaving the reader to find it.
 *
 * **The odds table renders on the page.** It is three rows and it answers a question a Trainer asks
 * directly, so it is not hidden in a drawer (Â§35's progressive disclosure is for the row detail, not
 * for a table this small).
 */
import AppLayout from '../../layouts/AppLayout.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import DataTable, { type DataTableColumn } from '../../components/ui/DataTable.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import SparkChip from '../../components/SparkChip.vue';
import ReferenceDrawer from '../../components/database/ReferenceDrawer.vue';
import SourceNote from '../../components/database/SourceNote.vue';
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import type { ProvenanceState } from '../../types';

type Badge = ProvenanceState;

interface Category {
    category: string;
    jp_name: string;
    global_name: string;
    effect: string;
    records: number;
    state: Badge;
    source_title: string;
}

interface OddsRow {
    band: string;
    one_star: string;
    two_stars: string;
    three_stars: string;
}

const props = defineProps<{
    categories: Category[];
    rollOdds: OddsRow[];
    rollOddsNote: string;
    recheck: { text: string; url: string };
    source: { section: string; anchor: string };
    totalCount: number;
}>();

// Â§18's four spark families have no committed token set in this build, so `SparkChip` renders every
// variant neutral and the category word carries the identity. Inventing a colour mapping would state
// a distinction the design system does not own; the word is the distinction, and it always prints.

const selected = ref<Category | null>(null);
const trigger = ref<HTMLElement | null>(null);

/*
 * The categories are a table, not a list of cards: a reader compares a record count against another
 * record count, and a table says which column a figure sits in. The category chip stays because the
 * family is the word the page is organised by, and the count is right-aligned in tabular figures
 * because a column of counts is compared down the page.
 */
const categoryColumns: DataTableColumn[] = [
    { key: 'category', label: 'Category' },
    { key: 'jp_name', label: 'JP name' },
    { key: 'effect', label: 'Effect' },
    { key: 'records', label: 'Records', align: 'right', mono: true },
    { key: 'state', label: 'Source' },
];

const categoryRows = computed(() =>
    props.categories.map((row) => ({
        category: row.category,
        global_name: row.global_name,
        jp_name: row.jp_name,
        effect: row.effect,
        records: `${row.records} records`,
        state: row.state,
        source_title: row.source_title,
    })),
);

/*
 * The star-roll odds are the same property across three bands, so they are one table with the
 * bands down the side. The figures are the source's own strings, so they are printed as they came.
 */
const oddsColumns: DataTableColumn[] = [
    { key: 'band', label: 'Final stat value' },
    { key: 'one_star', label: '1 star', align: 'right', mono: true },
    { key: 'two_stars', label: '2 stars', align: 'right', mono: true },
    { key: 'three_stars', label: '3 stars', align: 'right', mono: true },
];

const open = (row: Category, event: MouseEvent): void => {
    trigger.value = event.currentTarget as HTMLElement;
    selected.value = row;
};

const close = async (): Promise<void> => {
    selected.value = null;
    await nextTick();
    trigger.value?.focus();
};
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Sparks' }]">
        <Head title="Sparks" />
        <template #title>Sparks</template>

        <DatabaseNav />

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            The Spark categories a finished Umamusume can carry, and the odds a Spark rolls at one,
            two or three stars. The inventory is the reference guide's own count; roll odds follow
            the final value of the stat.
        </p>

        <SourceNote
            :section="props.source.section"
            :anchor="props.source.anchor"
            :recheck="props.recheck"
        />

        <p class="mt-6 text-sm text-ink-muted">{{ props.totalCount }} Spark categories</p>

        <section
            v-if="props.categories.length === 0"
            aria-labelledby="sparks-empty"
            class="mt-4 rounded-md border border-dashed border-rule bg-panel p-6"
        >
            <h3 id="sparks-empty" class="text-lg font-semibold text-ink-strong">
                No Spark categories are sourced
            </h3>
            <p class="mt-2 text-sm text-ink-muted">
                The reference guide's table of Spark categories is what this view prints, and it is empty here.
                Without it there is no way to tell a stat spark from a competition one, or to size the
                odds a parent faces.
            </p>
            <p class="mt-2 text-sm text-ink">
                Re-read the guide's Spark categories against the local export.
            </p>
        </section>

        <DataTable
            v-else
            class="mt-2"
            caption="Spark categories"
            :columns="categoryColumns"
            :rows="categoryRows"
        >
            <!-- The category cell carries the chip and the drawer's trigger, so the row still opens
                 its own detail, and the Japanese name keeps its language so a screen reader reads
                 it as Japanese rather than as an English word. -->
            <template #cell-category="{ row }">
                <span class="flex flex-wrap items-center gap-2">
                    <SparkChip :label="row.category" size="sm" />
                    <button
                        type="button"
                        class="flex min-h-11 items-center text-left text-sm font-medium text-ink-strong hover:underline"
                        :aria-expanded="selected?.category === row.category"
                        @click="open(row, $event)"
                    >
                        {{ row.global_name }}
                    </button>
                </span>
            </template>
            <template #cell-jp_name="{ row }">
                <span lang="ja">{{ row.jp_name }}</span>
            </template>
            <template #cell-state="{ row }">
                <ProvenanceBadge :state="row.state" :title="row.source_title" />
            </template>
        </DataTable>

        <ReferenceDrawer
            v-if="selected"
            class="mt-3"
            :title="selected.global_name"
            @close="close"
        >
            <p>{{ selected.effect }}</p>
            <p class="text-ink-muted">
                {{ selected.records }} records, as the reference guide
                counts them.
            </p>
            <p class="text-xs text-ink-muted">{{ selected.source_title }}</p>
        </ReferenceDrawer>

        <p v-if="props.categories.length > 0" class="mt-3 max-w-3xl text-sm text-ink-muted">
            The guide counts 68 further records in the same export that no page in its pass explains;
            they are counted, not described, so they are absent here.
        </p>

        <!--
            The star-roll odds, Â§1.5.3. A real table with row headers, because the four columns are the
            same property across three bands and comparison is the point (design-2.0 Â§46).
        -->
        <section aria-labelledby="spark-odds" class="mt-10">
            <h3 id="spark-odds" class="text-lg font-semibold text-ink-strong">
                Star-roll odds by final stat value
            </h3>

            <p class="mt-2 max-w-3xl text-sm text-ink-muted">{{ props.rollOddsNote }}</p>

            <DataTable
                class="mt-3"
                caption="Star-roll odds by final stat value: one, two and three stars"
                :columns="oddsColumns"
                :rows="props.rollOdds"
            />
        </section>
    </AppLayout>
</template>
