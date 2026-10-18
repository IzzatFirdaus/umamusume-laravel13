<script setup lang="ts">
/*
 * The shop ledger (G6). What this run holds, the cap each holding sits against, the one publisher's
 * worth ratings, and its Summer reserve.
 *
 * **What it does not print, on purpose.** The Pro Shop arm above already renders the catalogue, the
 * recorded purchases, the spend total, the purchase form and the coin balance's absence ("Shop Coins:
 * not yet recorded"). A second surface for any of those is the one-fact-two-surfaces rule, so this
 * component carries only what the arm lacks. There is deliberately no balance row and no second form.
 *
 * **Nothing here is this tool's advice.** Every rating and the reserve line come from
 * `config/scenarios.php`'s `shop_worth` and `shop_reserve`, both transcribed from uma.guide's own
 * pages and both carrying their source in a `title`. They travel as `estimated`, which is the state
 * for guidance rather than a rule. The arm's sentence "no row is recommended" stays true: the tool
 * recommends nothing and reports what one publisher said.
 *
 * **No projected cost.** Nothing in this build records a purchase the Trainer has not made yet, so a
 * cost projection would be a figure with no source. It is reported as an owed writer, and the row
 * simply does not exist.
 *
 * No number lives in this file: held counts, the cap, the ratings and the reserve are all composed in
 * `CockpitController::ledgerSection()`. The rows are a `dt`/`dd` grid, bare as the `dl`'s own
 * children, because axe's `definition-list` rule reads that child list and Chromium computes no
 * accessible name for the term role.
 */
import ProvenanceBadge from '../ProvenanceBadge.vue';
import type { ProvenanceState } from '../../types';

type Badge = Badge | null;

interface LedgerItem {
    name: string;
    held: number;
    cap: number;
    worth: string | null;
    worth_badge: Badge;
    worth_title: string;
}

interface LedgerSection {
    items: LedgerItem[];
    items_absence: string | null;
    cap: number;
    inflow: string | null;
    inflow_badge: Badge;
    inflow_title: string;
    reserve: {
        label: string;
        value: string;
        badge: Badge;
        title: string;
    } | null;
    rated_count: number;
    rated_title: string;
}

const props = defineProps<{
    ledger: LedgerSection;
}>();

const headingId = 'trackblazer-shop-ledger';

// The one string this file owns: it describes the count's provenance, not a value.
const heldTitle = 'Counted from this run\'s recorded purchases, against the cap the catalogue states.';
</script>

<template>
    <section :aria-labelledby="headingId" class="flex flex-col gap-3 border-t border-rule pt-3">
        <h4 :id="headingId" class="text-lg font-semibold text-ink-strong">
            Shop ledger
        </h4>

        <p v-if="props.ledger.items_absence !== null" class="text-sm text-ink-muted" role="status">
            {{ props.ledger.items_absence }}
        </p>

        <ul v-else class="flex flex-col gap-1.5" aria-label="Items held">
            <li
                v-for="item in props.ledger.items"
                :key="item.name"
                class="rounded-md border border-rule bg-raised px-3 py-2"
            >
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <span class="text-sm font-semibold text-ink-strong">{{ item.name }}</span>
                    <span class="font-mono text-xs tabular-nums text-ink">
                        {{ item.held }} of {{ item.cap }}
                    </span>
                </div>

                <!-- The publisher's rating is a separate line from the count above, so a guide's
                     opinion is never read as a fact about this run's inventory. -->
                <p v-if="item.worth !== null" class="mt-1 flex flex-wrap items-baseline gap-x-2 text-xs">
                    <ProvenanceBadge v-if="item.worth_badge !== null" :state="item.worth_badge" :title="item.worth_title" />
                    <span class="text-ink">{{ item.worth }}</span>
                </p>
                <p v-else class="mt-1 text-xs text-ink-muted" :title="item.worth_title">
                    No publisher rating for this item.
                </p>
            </li>
        </ul>

        <dl class="grid grid-cols-1 gap-x-3 gap-y-2 rounded-md border border-rule bg-raised p-3 @3xl:grid-cols-[minmax(0,10rem)_minmax(0,1fr)]">
            <dt class="text-xs text-ink-muted">Inflow from entered races</dt>
            <dd class="flex flex-col gap-1">
                <span class="flex flex-wrap items-baseline gap-x-2">
                    <ProvenanceBadge v-if="props.ledger.inflow_badge !== null" :state="props.ledger.inflow_badge" :title="props.ledger.inflow_title" />
                    <span v-if="props.ledger.inflow !== null" class="font-mono text-sm tabular-nums text-ink">{{ props.ledger.inflow }}</span>
                    <span v-else class="text-sm text-ink-muted">N/A</span>
                </span>
                <span v-if="props.ledger.inflow === null" class="text-xs text-ink-muted">{{ props.ledger.inflow_title }}</span>
            </dd>

            <template v-if="props.ledger.reserve !== null">
                <dt class="text-xs text-ink-muted">Summer reserve</dt>
                <dd class="flex flex-col gap-1">
                    <span class="flex flex-wrap items-baseline gap-x-2">
                        <ProvenanceBadge :state="props.ledger.reserve.badge" :title="props.ledger.reserve.title" />
                        <span class="text-sm text-ink">{{ props.ledger.reserve.label }}</span>
                    </span>
                    <span class="text-xs text-ink-muted">{{ props.ledger.reserve.title }}</span>
                </dd>
            </template>

            <dt class="text-xs text-ink-muted">Catalogue items rated</dt>
            <dd class="flex flex-col gap-1">
                <span class="font-mono text-sm tabular-nums text-ink">{{ props.ledger.rated_count }}</span>
                <span class="text-xs text-ink-muted">{{ props.ledger.rated_title }}</span>
            </dd>
        </dl>
    </section>
</template>
