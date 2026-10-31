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
import PageHeader from '../../components/ui/PageHeader.vue';
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

interface ShopRule {
    rule: string;
    detail: string;
}

const props = defineProps<{
    rows: ShopRow[];
    scenarioShop: { label: string; rotation_turns: number; items: ScenarioItem[] };
    source: { section: string; anchor: string };
    totalCount: number;
    /** One publisher's "Worth" ratings, keyed by the item name it matched exactly. */
    ratings: Record<string, string>;
    ratingsSource: string;
    // The controller sends it (DatabaseController's shopItems), and the SourceNote under the table
    // renders it: without the declaration the re-check pointer this area's siblings all carry was
    // silently dropped, and no gate looked at the prop path.
    recheck: { text: string; url: string };
}>();

/*
 * The wireframe's shop rules (`database-shop-items.html:86-100`). The rotation figure is the scenario
 * config's own; the rest are the guide's published constraints, and the last row says which claim the
 * repository records as unconfirmed instead of printing a duration for it.
 */
const shopRules = computed<ShopRule[]>(() => [
    { rule: 'Unlock', detail: 'After the Debut race' },
    { rule: 'Refresh', detail: `Every ${props.scenarioShop.rotation_turns} turns` },
    { rule: 'Copies per item', detail: '5' },
    { rule: 'Sales', detail: '10-20%' },
    { rule: 'Stacking', detail: 'Multi-turn items cannot be used while active' },
    { rule: 'Overwrite', detail: 'A lower-grade item used before a higher one is wasted' },
]);

const ratedCount = computed<number>(() =>
    props.scenarioShop.items.filter((item) => props.ratings[item.name] !== undefined).length,
);

/** How many of the catalogue's rows the table shows before "Show all" is pressed. */
const shown = ref(10);
const visibleItems = computed<ScenarioItem[]>(() => props.scenarioShop.items.slice(0, shown.value));

/** The ported pill class for one publisher rating, by what the rating says. */
function ratingClass(rating: string): string {
    if (rating.startsWith('Must Buy')) {
        return 'pill pill-positive';
    }

    if (rating.startsWith('Skip') || rating.startsWith('Generally Skip')) {
        return 'pill pill-neutral';
    }

    return 'pill pill-caution';
}

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
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Shop Items' }]">
        <Head title="Shop Items" />
        <template #head>
            <PageHeader
                eyebrow="Shop items"
                title="Pro Shop catalogue"
                :description="`${props.scenarioShop.items.length} items, costs and effects transcribed from one publisher's list. Ratings are that guide's opinion, labelled as such on every row.`"
            />
        </template>

        <DatabaseNav />

        <!--
          The wireframe's bare catalogue card (`database-shop-items.html:69-84`): no heading, a `table.t`
          with the item, its cost, its effect and one publisher's rating, and the client's pager under
          it. The rating's source is in the column heading, which is the wireframe's own key.
          HFW-OVERRIDE (data: rows): the mock's ten rows are one publisher's priorities; this page
          prints the Pro Shop's config rows plus the §1.6.10 catalogue below.
        -->
        <div class="card mt-4">
            <table
                class="t"
                :aria-label="`${props.scenarioShop.label} Pro Shop items, with one publisher's rating`"
            >
                <thead>
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col" class="n">Cost</th>
                        <th scope="col">Effect</th>
                        <th scope="col">Rating (one guide)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in visibleItems" :key="item.name">
                        <th scope="row">{{ item.name }}</th>
                        <td class="n font-mono tabular-nums">{{ item.cost }}</td>
                        <td>{{ item.effect }}</td>
                        <td>
                            <span
                                v-if="props.ratings[item.name]"
                                :class="ratingClass(props.ratings[item.name])"
                                :title="props.ratingsSource"
                            >{{ props.ratings[item.name] }}</span>
                            <span v-else>N/A<span class="sr-only">: this publisher names no rating for the item.</span></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-3 flex flex-wrap items-center justify-end gap-1.5 border-t border-rule pt-3">
                <p class="text-xs text-ink-muted">
                    {{ props.scenarioShop.items.length }} items · showing
                    {{ Math.min(shown, props.scenarioShop.items.length) }}
                </p>
                <button
                    v-if="shown < props.scenarioShop.items.length"
                    type="button"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-md border border-rule px-3 text-sm font-extrabold text-ink hover:bg-raised transition-colors duration-150 ease-out"
                    @click="shown = props.scenarioShop.items.length"
                >Show all</button>
            </div>
        </div>

        <div class="grid gap-3.5 g-2 mt-4">
            <div class="card" role="region" aria-labelledby="shop-rules">
                <h2 id="shop-rules">Shop rules</h2>
                <table class="t">
                    <tbody>
                        <tr v-for="rule in shopRules" :key="rule.rule">
                            <th scope="row" class="w-40">{{ rule.rule }}</th>
                            <td>{{ rule.detail }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        "Race-triggered items last 3 turns" is recorded as <b>unconfirmed</b>. No source in the
                        repository states a duration for that class, so the catalogue does not print one.
                        No item on either client guarantees training success.
                    </span>
                </p>
            </div>

            <div class="card" role="region" aria-labelledby="shop-ratings">
                <h2 id="shop-ratings">What the ratings are</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    The "Rating" column is one publisher's priorities, transcribed. It is not this tool's
                    recommendation: <b>no row is recommended</b>, and a rating appears only where the
                    publisher's item matched the catalogue's cost and effect exactly.
                </p>
                <div class="grid g-3 mt-3 gap-3.5">
                    <div class="tile">
                        <div class="k">Items</div>
                        <div class="v font-mono text-sm">{{ props.scenarioShop.items.length }}</div>
                    </div>
                    <div class="tile">
                        <div class="k">Rated</div>
                        <div class="v font-mono text-sm">{{ ratedCount }}</div>
                    </div>
                    <div class="tile">
                        <div class="k">Source</div>
                        <div class="v text-sm">1 guide</div>
                    </div>
                </div>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        Coins scale with placement, 100 for first and less below. Grade Points and Shop Coins
                        are independent counters: a Pre-OP win pays the same coins as a G1 win but a fifth of
                        the Grade Points.
                    </span>
                </p>
            </div>
        </div>

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

        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Catalogue</h2>
        <p class="mt-1 text-sm text-ink-muted">{{ props.totalCount }} catalogue rows</p>

        <!--
          HFW-OVERRIDE (copy: table idiom note): the page draws two table idioms, and this one sentence
          says which is which. It sits above the first `DataTable` wrapper; that component carries its
          own card and has no slot for a note inside it, so the sentence is the block above rather than
          a Card inside a Card (`lint:ui` refuses the nesting).
        -->
        <p class="note mt-4">
            <span class="g" aria-hidden="true">○</span>
            <span>
                The Pro Shop table above is this page's reference table; the catalogue below is the
                shared data table this tool uses for its own lists, with a focusable scroll region.
                Both print the recorded facts and neither is a recommendation.
            </span>
        </p>

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

    </AppLayout>
</template>
