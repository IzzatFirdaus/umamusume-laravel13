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
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import ReferenceDrawer from '../../components/database/ReferenceDrawer.vue';
import SourceNote from '../../components/database/SourceNote.vue';
import { Head } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

interface ShopRow {
    name: string;
    effect: string;
    spend_site: string;
    kind: 'consumable' | 'currency';
    state: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
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
    source: { section: string; anchor: string; doc: string };
    totalCount: number;
}>();

const selected = ref<ShopRow | null>(null);
const trigger = ref<HTMLElement | null>(null);

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

        <h2 class="text-2xl font-semibold text-ink-strong">Shop items</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            Consumables and currencies, with the client's own effect string and where each is actually
            spent. Spendable items are not support effects: the spend site column is what tells a Career
            item from a Team Trials one.
        </p>

        <SourceNote
            :section="props.source.section"
            :anchor="props.source.anchor"
            :doc="props.source.doc"
            :recheck="props.recheck"
        />

        <p class="mt-6 text-sm text-ink-muted">{{ props.totalCount }} catalogue rows</p>

        <section
            v-if="props.rows.length === 0"
            aria-labelledby="shop-empty"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6"
        >
            <h3 id="shop-empty" class="text-base font-semibold text-ink-strong">
                Item catalogue not loaded in this build
            </h3>
            <p class="mt-2 text-sm text-ink-muted">
                No catalogue rows are transcribed, so there is nothing to compare a spend against and no
                way to tell a Career item from a Team Trials one.
            </p>
            <p class="mt-2 text-sm text-ink">
                Client strings are in the reference guide §1.6.10; the
                {{ props.scenarioShop.label }} Pro Shop below is read from the scenario config and renders
                either way.
            </p>
        </section>

        <ul v-else class="mt-2 flex flex-col gap-3">
            <li
                v-for="row in props.rows"
                :key="row.name"
                class="rounded-md border border-rule bg-raised"
            >
                <button
                    type="button"
                    class="flex min-h-11 w-full flex-wrap items-baseline justify-between gap-x-4 gap-y-2 px-4 py-3 text-left hover:bg-panel"
                    :aria-expanded="selected?.name === row.name"
                    @click="open(row, $event)"
                >
                    <span class="text-sm font-medium text-ink-strong">{{ row.name }}</span>

                    <span class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong">
                            {{ row.kind === 'currency' ? 'Currency' : 'Consumable' }}
                        </span>
                        <span class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong">
                            {{ row.spend_site }}
                        </span>
                        <ProvenanceBadge :state="row.state" :title="row.source_title" />
                    </span>
                </button>

                <dl
                    class="grid grid-cols-1 gap-x-4 gap-y-1 border-t border-rule px-4 py-3 text-sm sm:grid-cols-[9rem_1fr]"
                >
                    <dt class="text-ink-muted">Effect</dt>
                    <dd class="text-ink">{{ row.effect }}</dd>
                    <dt class="text-ink-muted">Spend site</dt>
                    <dd class="text-ink">{{ row.spend_site }}</dd>
                </dl>

                <div class="px-4 pb-3">
                    <ReferenceDrawer
                        v-if="selected?.name === row.name"
                        :title="row.name"
                        @close="close"
                    >
                        <p>{{ row.detail }}</p>
                        <p class="text-xs text-ink-muted">{{ row.source_title }}</p>
                    </ReferenceDrawer>
                </div>
            </li>
        </ul>

        <!--
            The Pro Shop block: the scenario matrix's own `shop_items`, reused verbatim. Its cost column
            is the client's price, and the rotation length is the matrix's configured fact.
        -->
        <section aria-labelledby="scenario-shop" class="mt-10">
            <h3 id="scenario-shop" class="text-lg font-semibold text-ink-strong">
                {{ props.scenarioShop.label }} Pro Shop
            </h3>

            <p class="mt-2 max-w-3xl text-sm text-ink-muted">
                The item class this scenario sells, read from
                <code class="font-mono text-xs">config/scenarios.php</code>. The rotation runs
                {{ props.scenarioShop.rotation_turns }} turns; which items are on offer in a given
                rotation is not modelled, so this is the catalogue and not a live lineup.
            </p>

            <p class="mt-4 text-sm text-ink-muted">
                {{ props.scenarioShop.items.length }} catalogue items
            </p>

            <ul class="mt-2 flex flex-col gap-2">
                <li
                    v-for="item in props.scenarioShop.items"
                    :key="item.name"
                    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 rounded-md border border-rule bg-raised px-4 py-2 text-sm"
                >
                    <span class="font-medium text-ink-strong">{{ item.name }}</span>
                    <span class="text-ink-muted">{{ item.effect }}</span>
                    <span class="font-mono tabular-nums text-ink-strong">{{ item.cost }} coins</span>
                </li>
            </ul>
        </section>
    </AppLayout>
</template>
