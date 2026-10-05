<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface ShopItemOption {
    // The value the request validates against; the label is the catalogue row's own text.
    name: string;
    // "Name · 55 coins" is composed and number-formatted by the controller.
    label: string;
}

interface PurchaseRow {
    item: string;
    effect: string;
    // The number-formatted cost; the component only appends the word "coins".
    cost: string;
}

const props = defineProps<{
    // Composition flag (`panels.shop`), never a scenario slug (G-33/G-34): the Blade
    // returned before its markup on a run whose scenario has no shop.
    panelsShop: boolean;
    purchaseUrl: string;
    updateUrl: string;
    // `config(...shop.rotation_turns)` and `shop.max_copies_per_item`, read server-side.
    rotationTurns: number;
    maxCopies: number;
    // null renders "countdown not recorded"; otherwise the server has composed the
    // plural: "resets in 3 turns". Entered, never computed (D-232).
    resetsInLabel: string | null;
    catalogue: ShopItemOption[];
    purchases: PurchaseRow[];
    // `shopSpendTotal()` and `max(turn) ?: 1`, number-formatted / resolved server-side.
    spendTotal: string;
    turnDefault: number;
    // The `runs.update` carry-throughs: the shared StoreTrainingRunRequest writes every
    // field it is handed, so the rotation form sends the values it is not editing.
    umamusumeId: number;
    statusValue: string;
    scenario: string;
    currentObjectiveIndex: number | null;
    shopResetsIn: number | null;
}>();

// The Blade prefilled nothing from `old()` here and had no empty option, so the form opens
// on the first catalogue row and the last logged turn, exactly as it rendered.
const purchaseForm = useForm({
    turn: String(props.turnDefault),
    item: props.catalogue[0]?.name ?? '',
    cost: '',
    effect: '',
});

// A POST payload is still form data, so the hidden inputs of the Blade become these keys.
const rotationForm = useForm({
    umamusume_id: String(props.umamusumeId),
    status: props.statusValue,
    scenario: props.scenario,
    current_objective_index: props.currentObjectiveIndex === null ? '' : String(props.currentObjectiveIndex),
    shop_resets_in: props.shopResetsIn === null ? '' : String(props.shopResetsIn),
});

const rotationOptions = computed(() => Array.from({ length: props.rotationTurns + 1 }, (_, i) => String(i)));
</script>

<template>
    <div v-if="panelsShop" class="rounded-md border border-rule bg-panel p-3">
        <!-- The capsule anatomy is DESIGN.md §2.3's, carried from x-capsule-header. -->
        <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pr-5 pl-20">
            <span class="text-base font-bold text-on-chrome">Shop</span>
        </div>

        <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 text-sm">
            <p class="text-ink">
                <span class="font-bold text-ink-strong">Rotation:</span>
                <span v-if="resetsInLabel === null" class="text-ink-muted">countdown not recorded</span>
                <span v-else class="font-mono tabular-nums">{{ resetsInLabel }}</span>
                <span class="text-xs text-ink-muted">of a {{ rotationTurns }}-turn rotation</span>
            </p>

            <p class="text-xs text-ink-muted">
                Buying at a higher rank overwrites the lower one, and unspent Shop Coins do
                not survive the run.
            </p>
        </div>

        <p v-if="purchases.length === 0" class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="status">
            No purchases recorded for this run.
        </p>
        <ul v-else class="mt-3 flex flex-col gap-1.5" aria-label="Recorded purchases">
            <li
                v-for="(purchase, index) in purchases"
                :key="`${index}-${purchase.item}`"
                class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
            >
                <span class="min-w-0 flex-1">
                    <span class="font-semibold text-ink-strong">{{ purchase.item }}</span>
                    <span class="mt-0.5 block text-xs text-ink-muted">{{ purchase.effect }}</span>
                </span>
                <span class="shrink-0 font-mono text-xs tabular-nums text-ink-muted">{{ purchase.cost }} coins</span>
            </li>
        </ul>

        <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 border-t border-rule pt-3 text-xs text-ink-muted">
            <span>Spent: <span class="font-mono tabular-nums text-ink">{{ spendTotal }}</span> coins</span>
            <span>Shop Coins: not yet recorded</span>
        </div>

        <form
            class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm"
            :aria-busy="purchaseForm.processing"
            @submit.prevent="purchaseForm.post(purchaseUrl)"
        >
            <!-- Explicit `for`/`id` labelling, and the error outside the label: text inside a
                 wrapped label joins the field's accessible name (D-12's reading rule). -->
            <div class="flex flex-col gap-1">
                <label for="purchase-turn" class="font-medium text-ink">Turn</label>
                <input
                    id="purchase-turn"
                    v-model="purchaseForm.turn"
                    type="number"
                    name="turn"
                    min="1"
                    required
                    class="w-20 rounded-md border bg-raised px-2 py-1 text-ink"
                    :class="purchaseForm.errors.turn ? 'border-risk' : 'border-rule'"
                    :aria-invalid="purchaseForm.errors.turn ? 'true' : 'false'"
                    :aria-describedby="purchaseForm.errors.turn ? 'purchase-turn-error' : undefined"
                >
                <p v-if="purchaseForm.errors.turn" id="purchase-turn-error" class="text-xs text-risk">
                    {{ purchaseForm.errors.turn }}
                </p>
            </div>

            <div class="flex flex-col gap-1">
                <label for="purchase-item" class="font-medium text-ink">Item</label>
                <select
                    id="purchase-item"
                    v-model="purchaseForm.item"
                    name="item"
                    required
                    class="rounded-md border bg-raised px-2 py-1 text-ink"
                    :class="purchaseForm.errors.item ? 'border-risk' : 'border-rule'"
                    :aria-invalid="purchaseForm.errors.item ? 'true' : 'false'"
                    :aria-describedby="purchaseForm.errors.item ? 'purchase-item-error' : undefined"
                >
                    <option v-for="row in catalogue" :key="row.name" :value="row.name">{{ row.label }}</option>
                </select>
                <p v-if="purchaseForm.errors.item" id="purchase-item-error" class="text-xs text-risk">
                    {{ purchaseForm.errors.item }}
                </p>
            </div>

            <div class="flex flex-col gap-1">
                <label for="purchase-cost" class="font-medium text-ink">Cost read</label>
                <input
                    id="purchase-cost"
                    v-model="purchaseForm.cost"
                    type="number"
                    name="cost"
                    min="0"
                    required
                    class="w-20 rounded-md border bg-raised px-2 py-1 text-ink"
                    :class="purchaseForm.errors.cost ? 'border-risk' : 'border-rule'"
                    :aria-invalid="purchaseForm.errors.cost ? 'true' : 'false'"
                    :aria-describedby="purchaseForm.errors.cost ? 'purchase-cost-error' : undefined"
                >
                <p v-if="purchaseForm.errors.cost" id="purchase-cost-error" class="text-xs text-risk">
                    {{ purchaseForm.errors.cost }}
                </p>
            </div>

            <div class="flex grow flex-col gap-1">
                <label for="purchase-effect" class="font-medium text-ink">Effect read</label>
                <input
                    id="purchase-effect"
                    v-model="purchaseForm.effect"
                    type="text"
                    name="effect"
                    maxlength="255"
                    required
                    class="rounded-md border bg-raised px-2 py-1 text-ink"
                    :class="purchaseForm.errors.effect ? 'border-risk' : 'border-rule'"
                    :aria-invalid="purchaseForm.errors.effect ? 'true' : 'false'"
                    :aria-describedby="purchaseForm.errors.effect ? 'purchase-effect-error' : undefined"
                >
                <p v-if="purchaseForm.errors.effect" id="purchase-effect-error" class="text-xs text-risk">
                    {{ purchaseForm.errors.effect }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="purchaseForm.processing"
                class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong"
            >
                Record purchase
            </button>

            <p class="w-full text-xs text-ink-muted">
                A higher-rank buy overwrites the lower one, and up to {{ maxCopies }} copies of an
                item can be held at a time. Cost and effect are entered as read, and checked against
                the catalogue this scenario sells.
            </p>

            <p v-if="purchaseForm.hasErrors" class="w-full text-sm text-risk" role="alert">
                The purchase was not recorded. See the field marked below.
            </p>
        </form>

        <form
            class="mt-3 flex flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm"
            :aria-busy="rotationForm.processing"
            @submit.prevent="rotationForm.put(updateUrl)"
        >
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Turns until rotation</span>
                <select v-model="rotationForm.shop_resets_in" name="shop_resets_in" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">Not recorded</option>
                    <option v-for="turns in rotationOptions" :key="turns" :value="turns">{{ turns }}</option>
                </select>
            </label>

            <button
                type="submit"
                :disabled="rotationForm.processing"
                class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong"
            >
                Report rotation
            </button>

            <p v-if="rotationForm.errors.shop_resets_in" class="w-full text-risk">
                {{ rotationForm.errors.shop_resets_in }}
            </p>
        </form>
    </div>
</template>
