<script setup lang="ts">
/*
 * The Shop Items reference view (SCREEN-023, `SCR-SYS-009`). The consumables and currencies from
 * `docs/UMAMUSUME_REFERENCE.md` §1.6.10, plus the Trackblazer Pro Shop read straight from
 * `config/scenarios.php`.
 *
 * **The scenario block is reused, not retyped.** The Pro Shop's nineteen rows live in the scenario
 * matrix's own `shop_items` key, so this page renders that block as a second section rather than
 * copying it. The reference config carries only the wider catalogue the matrix does not.
 *
 * **Two kinds of row, stated in words.** A consumable is spent; a currency is exchanged. The kind is a
 * text pill, never a colour, so the distinction survives without styling (§5, §42).
 *
 * **No recommendation.** §19 and §20 do not apply to a reference view, and the shop rotation is not
 * modelled, so no row is marked best value and no row is selected.
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

interface ShopRow {
    name: string;
    effect: string;
    spend_site: string;
    kind: 'consumable' | 'currency';
    state: Badge;
    source_title: string;
    detail: string;
}

interface ScenarioItem {
    name: string;
    cost: number;
    effect: string;
}

const props = defineProps<{
    rows: ShopRow[];
    scenarioShop: { label: string; rotation_turns: number; items: ScenarioItem[] };
    source: { section: string; anchor: string };
    totalCount: number;
    // The controller sends it (DatabaseController's shopItems), and the SourceNote under the table
    // renders it: without the declaration the re-check pointer this area's siblings all carry was
    // silently dropped, and no gate looked at the prop path.
    recheck: { text: string; url: string };
}>();

const selected = ref<ShopRow | null>(null);
const trigger = ref<HTMLElement | null>(null);

/*
 * The catalogue is a table, not a list of cards: a reader compares a spend site against another
 * spend site, and a table says which column a fact sits in. The kind and the spend site stay chips
 * because they are the two facts the page is organised by, and the cost is right-aligned in
 * tabular figures because a column of prices is compared down the page.
 */
const shopColumns: DataTableColumn[] = [
    { key: 'name', label: 'Item' },
    { key: 'kind', label: 'Kind' },
    { key: 'spend_site', label: 'Spend site' },
    { key: 'effect', label: 'Effect' },
    { key: 'state', label: 'Source' },
];

const shopRows = computed(() =>
    props.rows.map((row) => ({
        name: row.name,
        kind: row.kind,
        spend_site: row.spend_site,
        effect: row.effect,
        state: row.state,
        source_title: row.source_title,
        detail: row.detail,
    })),
);

const proShopColumns: DataTableColumn[] = [
    { key: 'name', label: 'Item' },
    { key: 'effect', label: 'Effect' },
    { key: 'cost', label: 'Cost (coins)', align: 'right', mono: true },
];

const open = (row: ShopRow, event: MouseEvent): void => {
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
    <AppLayout>
        <Head title="Shop Items" />
        <template #title>Shop Items</template>

        <DatabaseNav />

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            Consumables and currencies, with the client's own effect string and where each is actually
            spent. Spendable items are not support effects: the spend site column is what tells a Career
            item from a Team Trials one.
        </p>

        <SourceNote
            :section="props.source.section"
            :anchor="props.source.anchor"
            :recheck="props.recheck"
        />

        <p class="mt-6 text-sm text-ink-muted">{{ props.totalCount }} catalogue rows</p>

        <section
            v-if="props.rows.length === 0"
            aria-labelledby="shop-empty"
            class="mt-4 rounded-md border border-dashed border-rule bg-panel p-6"
        >
            <h3 id="shop-empty" class="text-lg font-semibold text-ink-strong">
                Item catalogue not loaded in this build
            </h3>
            <p class="mt-2 text-sm text-ink-muted">
                No catalogue rows are transcribed, so there is nothing to compare a spend against and no
                way to tell a Career item from a Team Trials one.
            </p>
            <p class="mt-2 text-sm text-ink">
                Client strings are in the reference guide's shop-item table; the
                {{ props.scenarioShop.label }} Pro Shop below is read from the scenario config and renders
                either way.
            </p>
        </section>

        <DataTable
            v-else
            class="mt-2"
            caption="Shop item catalogue"
            :columns="shopColumns"
            :rows="shopRows"
        >
            <!-- The name cell is the drawer's trigger, so the row still opens its own detail, and
                 the kind and the spend site stay chips because they are the two facts a reader
                 compares an item on. -->
            <template #cell-name="{ row }">
                <button
                    type="button"
                    class="flex min-h-11 items-center text-left text-sm font-medium text-ink-strong hover:underline"
                    :aria-expanded="selected?.name === row.name"
                    @click="open(row, $event)"
                >
                    {{ row.name }}
                </button>
            </template>
            <template #cell-kind="{ row }">
                <SparkChip
                    :label="row.kind === 'currency' ? 'Currency' : 'Consumable'"
                    :variant="row.kind === 'currency' ? 'currency' : 'consumable'"
                    size="sm"
                />
            </template>
            <template #cell-spend_site="{ row }">
                <SparkChip :label="row.spend_site" size="sm" />
            </template>
            <template #cell-state="{ row }">
                <ProvenanceBadge :state="row.state" :title="row.source_title" />
            </template>
        </DataTable>

        <ReferenceDrawer
            v-if="selected"
            class="mt-3"
            :title="selected.name"
            @close="close"
        >
            <p>{{ selected.detail }}</p>
            <p class="text-xs text-ink-muted">{{ selected.source_title }}</p>
        </ReferenceDrawer>

        <!--
            The Pro Shop block: the scenario matrix's own `shop_items`, reused verbatim. Its cost column
            is the client's price, and the rotation length is the matrix's configured fact.
        -->
        <section aria-labelledby="scenario-shop" class="mt-10">
            <h3 id="scenario-shop" class="text-lg font-semibold text-ink-strong">
                {{ props.scenarioShop.label }} Pro Shop
            </h3>

            <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                The item class this scenario sells, read from the scenario's own config. The rotation runs
                {{ props.scenarioShop.rotation_turns }} turns; which items are on offer in a given
                rotation is not modelled, so this is the catalogue and not a live lineup.
            </p>

            <p class="mt-4 text-sm text-ink-muted">
                {{ props.scenarioShop.items.length }} catalogue items
            </p>

            <DataTable
                class="mt-2"
                caption="Pro Shop catalogue items"
                :columns="proShopColumns"
                :rows="props.scenarioShop.items"
            />
        </section>
    </AppLayout>
</template>
