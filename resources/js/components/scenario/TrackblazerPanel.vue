<script setup lang="ts">
/*
 * SCREEN-016's panel: the Trackblazer scenario (plan §9 E4, `SCR-CAR-022`).
 *
 * Registered against the three Trackblazer flags (`grade_objectives`, `shop`, `epithet_routes`)
 * and it branches on `props.name`, the flag key the shell mounted it for. It never reads
 * `scenario.label`; every string comes from the rows the controller composed or from the
 * matrix (gate G-33, D-240). The path to E4 was TDD: TrackblazerPanelTest pins the catalogue
 * in `shop_items` order with no `recommended` field, and rejects a wrong-cost purchase at the
 * boundary so the form does not invent a price.
 *
 * **Shop rotation is not modelled (plan §3 knowledge grounding).** The catalogue renders in
 * config order. No "Recommended Purchase" card, no `recommended` flag, no "best value" sort or
 * highlight, no default-selected item. The state copy says in text that the rotation is not
 * modelled, so the catalogue is the only list this build prints.
 *
 * **Purchase write goes through the existing boundary.** `runs.purchases.store` and its
 * `StoreShopPurchaseRequest` are the authority; the Vue form only posts `{turn, item, cost, effect}`
 * and reads `useForm`'s per-field errors. Inputs are labelled by `for`/`id`; error text is
 * rendered outside the `<label>` so its accessible name stays clean, and `aria-describedby`
 * ties each error to its input.
 *
 * **The "Twinkle Star Climax" finale name is a named absence** (§7 conflict row 31 UNVERIFIED,
 * per the reference guide). The export label "Trackblazer" stands; the absence is carried as
 * `scenario.finale_official_title_absence`, and the section reads it as a `title` citation so
 * a screen reader hears what is missing.
 *
 * **Three epithet states.** `earned`, `open`, `unverifiable`. The third is the load-bearing
 * one: a route this surface cannot see is not a route the Trainer has not earned (D-220, D-256).
 */

import AlertRow from './AlertRow.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface GradeObjective {
    index: number;
    name: string;
    required: number;
}

interface GradePeriod {
    index: number;
    name: string;
    required: number;
    earned: number | null;
    unpriced: number;
}

interface GradeSection {
    objectives: GradeObjective[];
    current: number | null;
    earned: number | null;
    unpriced_count: number;
    unassigned_count: number;
    periods: GradePeriod[];
    ladder_source: 'standard';
    rotation_turns: number | null;
    rotation_resets_in: number | null;
}

interface ShopCatalogueRow {
    name: string;
    cost: number;
    effect: string;
}

interface ShopPurchaseRow {
    item: string;
    cost: number;
    effect: string;
    turn: number;
}

interface ShopSection {
    catalogue: ShopCatalogueRow[];
    purchases: ShopPurchaseRow[];
    rotation_turns: number | null;
    rotation_resets_in: number | null;
    max_copies: number;
    spend_total: number;
    fill: string;
    select_action: string;
}

interface EpithetRow {
    route: string;
    epithet: string;
    reward: string;
    state: 'earned' | 'open' | 'unverifiable';
    missing: string[];
    note: string | null;
}

interface EpithetSection {
    rows: EpithetRow[];
    seen_titles: string[];
}

interface RivalRow {
    title: string;
    year_label: string | null;
    turn: number | null;
    tier: string | null;
}

interface RivalSection {
    rows: RivalRow[];
    absence: string | null;
}

const props = defineProps<{
    scenario: {
        grade: GradeSection | null;
        shop: ShopSection | null;
        epithet: EpithetSection | null;
        rival: RivalSection | null;
        finale_official_title_absence: string | null;
        finale_structure: unknown;
    };
    label: string;
    kind: 'widget' | 'panel';
    name: 'grade_objectives' | 'shop' | 'epithet_routes';
}>();

/**
 * Heading ids derived from the matrix's flag keys, never from a scenario name. The Shop id is
 * the jump target the Cockpit's "Shop" button scrolls to and focuses (WCAG 2.4.11).
 */
const gradeHeadingId = 'trackblazer-grade-heading';
const shopHeadingId = 'trackblazer-shop-heading';
const epithetHeadingId = 'trackblazer-epithet-heading';

/** The period the run is on, or the absence state. */
const currentPeriod = computed<{ name: string; required: number } | null>(() => {
    if (props.scenario.grade === null || props.scenario.grade.current === null) {
        return null;
    }

    const idx = props.scenario.grade.current - 1;
    return props.scenario.grade.objectives[idx] ?? null;
});

const earned = computed<number | null>(() => props.scenario.grade?.earned ?? null);

const barPercent = computed((): number => {
    const period = currentPeriod.value;

    if (period === null || period.required === 0) {
        return 0;
    }

    const value = earned.value ?? 0;

    return Math.min(100, Math.round((value / period.required) * 100));
});

const barLabel = computed((): string => {
    const period = currentPeriod.value;
    const value = earned.value;

    if (period === null) {
        return 'Grade Points not yet recorded';
    }

    if (value === null) {
        return props.scenario.grade?.unpriced_count && props.scenario.grade.unpriced_count > 0
            ? 'Grade Points not totalled'
            : 'Grade Points not yet recorded';
    }

    return `${value} of ${period.required} toward ${period.name}`;
});

const remaining = computed((): number => {
    const period = currentPeriod.value;
    const value = earned.value;

    if (period === null || value === null) {
        return 0;
    }

    return Math.max(0, period.required - value);
});

const over = computed((): number => {
    const period = currentPeriod.value;
    const value = earned.value;

    if (period === null || value === null) {
        return 0;
    }

    return Math.max(0, value - period.required);
});

const purchaseForm = useForm({
    turn: 1,
    item: '',
    cost: 0,
    effect: '',
});

/* Track the Shop heading so the Cockpit's "Shop" jump button can scroll and focus it
 * (WCAG 2.4.11). `tabindex="-1"` lets it receive programmatic focus without entering the
 * keyboard order. */
const shopHeading = ref<HTMLElement | null>(null);

/** The catalogue is config's order, never resorted. Sort is not a recommendation under E4. */
const catalogue = computed<ShopCatalogueRow[]>(() => props.scenario.shop?.catalogue ?? []);

/** Recorded spend; null would render "N/A" with a `title` because there is no earning side. */
const spendTotal = computed((): number => props.scenario.shop?.spend_total ?? 0);

/** Shop Coins: no earning-side writer today, so the absence copy is what the panel says. */
const shopCoinsCopy = computed((): string => 'Shop Coins: not yet recorded');

const epithetGlyphs: Record<EpithetRow['state'], string> = {
    earned: '●',
    open: '○',
    unverifiable: '?',
};

const epithetWords: Record<EpithetRow['state'], string> = {
    earned: 'Earned',
    open: 'Open',
    unverifiable: 'Unverifiable',
};

const epithetToneClasses: Record<EpithetRow['state'], string> = {
    earned: 'border-green-line bg-green-tint text-ink',
    open: 'border-rule bg-raised text-ink',
    unverifiable: 'border-rule bg-sunken text-ink-muted',
};
</script>

<template>
    <!--
        The shell renders one component per ON flag. This component's `name` prop carries the
        flag key, never a scenario name; the three render arms below are the only branches.
        `v-if` over `v-show` so the placeholder copy never enters the DOM for an unset arm.
    -->
    <div class="flex flex-col gap-4">
        <section
            v-if="props.name === 'grade_objectives'"
            :aria-labelledby="gradeHeadingId"
            class="flex flex-col gap-3 rounded-md border border-rule bg-panel p-4"
        >
            <h3 :id="gradeHeadingId" tabindex="-1" class="text-base font-semibold text-ink-strong">
                Grade Point
            </h3>

            <div v-if="props.scenario.grade === null" class="text-sm text-ink-muted" role="status">
                <p>No scenario is set for this run, so no grade-point meter is composed.</p>
            </div>
            <template v-else>
                <div class="rounded-md border border-rule bg-raised p-3">
                    <p
                        v-if="currentPeriod === null"
                        class="text-sm text-ink"
                    >
                        <span class="font-semibold text-ink-strong">no period reported</span>:
                        which deadline this run is working toward is something the Trainer says,
                        not something this tool infers from a date, so there is no target to
                        measure against yet.
                    </p>
                    <template v-else>
                        <p class="text-xs font-bold uppercase tracking-widest text-ink-muted">
                            Working toward
                        </p>
                        <p class="mt-1 text-base font-semibold text-ink-strong">
                            {{ currentPeriod.name }}
                        </p>

                        <div
                            class="mt-3 h-2.5 overflow-hidden rounded-full bg-sunken"
                            role="img"
                            :aria-label="barLabel"
                        >
                            <div
                                class="h-full rounded-full bg-green-deep"
                                :style="`width: ${barPercent}%`"
                            />
                        </div>

                        <p v-if="earned !== null" class="mt-2 font-mono text-sm tabular-nums text-ink-strong">
                            {{ earned }} / {{ currentPeriod.required }}
                            <span class="font-sans text-xs text-ink-muted">
                                <template v-if="over > 0">
                                    · {{ over }} over the objective
                                </template>
                                <template v-else-if="currentPeriod.required > 0">
                                    · {{ remaining }} to go
                                </template>
                                <template v-else>
                                    · no points required
                                </template>
                            </span>
                        </p>
                        <p
                            v-else-if="props.scenario.grade.unpriced_count > 0"
                            class="mt-2 text-sm text-ink-muted"
                        >
                            <span class="font-semibold text-ink">not yet totalled</span>:
                            {{ props.scenario.grade.unpriced_count }} logged
                            {{ props.scenario.grade.unpriced_count === 1 ? 'result has' : 'results have' }}
                            no published Grade Point value, so any total here would count less
                            than this run earned.
                        </p>
                        <p v-else class="mt-2 text-sm text-ink-muted">
                            <span class="font-semibold text-ink">not yet recorded</span>:
                            no races are logged for this run, so there is no progress to show yet.
                        </p>

                        <p
                            v-if="over > 0"
                            class="mt-2 rounded-md border border-rule bg-sunken px-3 py-2 text-xs text-ink"
                        >
                            {{ over }} past the objective, and not banked. The next objective
                            starts from zero whatever this one finishes at.
                        </p>
                    </template>
                </div>

                <p class="rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink">
                    <span class="font-semibold text-ink-strong">Surplus does not carry over.</span>
                    Each of these is judged on its own, so whatever is left over at one deadline
                    is not available at the next.
                </p>

                <ol class="flex flex-col gap-1.5" aria-label="Grade Point objectives">
                    <li
                        v-for="(objective, index) in props.scenario.grade.objectives"
                        :key="objective.index"
                        class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
                    >
                        <span class="font-medium text-ink">{{ objective.name }}</span>
                        <span class="font-mono text-xs tabular-nums text-ink-muted">
                            {{ objective.required }} pts
                            <template v-if="props.scenario.grade.periods[index]?.earned !== null">
                                ·
                                {{ props.scenario.grade.periods[index].earned }} earned
                            </template>
                            <template v-else-if="(props.scenario.grade.periods[index]?.unpriced ?? 0) > 0">
                                · not totalled
                            </template>
                            <template v-else>
                                · not recorded
                            </template>
                        </span>
                    </li>
                </ol>

                <!--
                    Rival races from `RaceCatalogSlot` are facts only (per `ADR-0016`), not a
                    planner. They live under the grade-points region because the two currencies
                    are joined: every earned Grade Point is a placement. Capped at eight rows
                    because the Junior-Year seed holds 50+ entries and Miller's Law (§13) keeps
                    the surface at or under eight; the Race Database screen shows the full set.
                -->
                <div class="mt-2 border-t border-rule pt-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        Rival races and schedule
                    </p>
                    <p
                        v-if="props.scenario.rival === null || props.scenario.rival.rows.length === 0"
                        class="mt-2 text-sm text-ink-muted"
                    >
                        {{ props.scenario.rival?.absence ?? 'No Trackblazer race is in the local calendar.' }}
                    </p>
                    <template v-else>
                        <ul class="mt-2 flex flex-col gap-1.5">
                            <li
                                v-for="race in props.scenario.rival.rows.slice(0, 8)"
                                :key="race.title"
                                class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 text-sm"
                            >
                                <span class="min-w-0 flex-1 truncate font-medium text-ink">{{ race.title }}</span>
                                <span class="shrink-0 font-mono text-xs text-ink-muted">
                                    {{ race.tier ?? 'grade N/A' }} · {{ race.year_label ?? 'year N/A' }} · turn {{ race.turn ?? 'N/A' }}
                                </span>
                            </li>
                        </ul>
                        <p v-if="props.scenario.rival.rows.length > 8" class="mt-2 text-xs text-ink-muted">
                            First 8 of {{ props.scenario.rival.rows.length }} catalogued races. The full set
                            is on the Race Database screen.
                        </p>
                    </template>
                </div>

                <p class="text-xs text-ink-muted">
                    Targets shown are the
                    <span class="font-semibold text-ink">standard</span>
                    track. Which trainee belongs on the dirt-leaning or limited-turf-range track
                    is not sourced, and
                    <span class="font-mono text-ink">KI-15</span>
                    carries the disagreement.
                </p>
            </template>
        </section>

        <section
            v-else-if="props.name === 'shop'"
            :aria-labelledby="shopHeadingId"
            class="flex flex-col gap-3 rounded-md border border-rule bg-panel p-4"
        >
            <h3
                :id="shopHeadingId"
                ref="shopHeading"
                tabindex="-1"
                class="text-base font-semibold text-ink-strong"
            >
                Pro Shop
            </h3>

            <div v-if="props.scenario.shop === null" class="text-sm text-ink-muted" role="status">
                <p>No scenario is set for this run, so no Pro Shop is composed.</p>
            </div>
            <template v-else>
                <div class="rounded-md border border-rule bg-raised p-3 text-sm">
                    <p class="text-ink">
                        <span class="font-semibold text-ink-strong">Rotation:</span>
                        <template v-if="props.scenario.shop.rotation_resets_in === null">
                            <span class="text-ink-muted">countdown not recorded</span>
                        </template>
                        <template v-else>
                            <span class="font-mono tabular-nums">
                                resets in {{ props.scenario.shop.rotation_resets_in }}
                                {{ props.scenario.shop.rotation_resets_in === 1 ? 'turn' : 'turns' }}
                            </span>
                        </template>
                        <span class="text-xs text-ink-muted">
                            of a {{ props.scenario.shop.rotation_turns }}-turn rotation
                        </span>
                    </p>
                    <p class="mt-1 text-xs text-ink-muted">
                        Buying at a higher rank overwrites the lower one, and unspent Shop Coins
                        do not survive the run.
                    </p>
                    <p class="mt-2 text-xs text-ink-muted">
                        The shop rotation is not modelled, so this list is the catalogue only;
                        no row is recommended, no row is selected, no row is highlighted.
                    </p>

                    <!--
                        The visible catalogue renders `config('scenarios.scenarios.trackblazer.shop_items')`
                        in declared order, with item, cost and effect. The form's text input below
                        accepts a name from this list (datalist autocompletes), and a tampered
                        name is refused by `StoreShopPurchaseRequest`. The list is read-only: it
                        prints what the catalogue holds, no badge, no sort.
                    -->
                    <ul class="mt-3 flex flex-col gap-1.5" aria-label="Pro Shop catalogue">
                        <li
                            v-for="row in catalogue"
                            :key="row.name"
                            class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
                        >
                            <span class="min-w-0 flex-1">
                                <span class="font-semibold text-ink-strong">{{ row.name }}</span>
                                <span class="mt-1 block text-xs text-ink-muted">{{ row.effect }}</span>
                            </span>
                            <span class="shrink-0 font-mono text-xs tabular-nums text-ink-muted">
                                {{ row.cost }} coins
                            </span>
                        </li>
                    </ul>
                </div>

                <p
                    v-if="props.scenario.shop.purchases.length === 0"
                    class="rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted"
                    role="status"
                >
                    No purchases recorded for this run.
                </p>
                <ul v-else class="flex flex-col gap-1.5" aria-label="Recorded purchases">
                    <li
                        v-for="(purchase, index) in props.scenario.shop.purchases"
                        :key="`${purchase.turn}-${purchase.item}-${index}`"
                        class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="font-semibold text-ink-strong">{{ purchase.item }}</span>
                            <span class="mt-1 block text-xs text-ink-muted">{{ purchase.effect }}</span>
                        </span>
                        <span class="font-mono text-xs tabular-nums text-ink-muted">
                            {{ purchase.cost }} coins · turn {{ purchase.turn }}
                        </span>
                    </li>
                </ul>

                <div class="flex flex-wrap gap-x-6 gap-y-1 border-t border-rule pt-3 text-xs text-ink-muted">
                    <span>Spent: <span class="font-mono tabular-nums text-ink">{{ spendTotal }}</span> coins</span>
                    <span>{{ shopCoinsCopy }}</span>
                </div>

                <!--
                    The Vue form is one form per panel region; it posts to the existing boundary
                    `runs.purchases.store` whose `StoreShopPurchaseRequest` validates against
                    the catalogue. Per-field errors are bound to inputs by `aria-describedby`
                    so a screen reader announces them with the field, not after.
                -->
                <form
                    method="POST"
                    :action="props.scenario.shop.fill"
                    class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm"
                    @submit.prevent="purchaseForm.post(props.scenario.shop.fill, { preserveScroll: true })"
                >
                    <div class="flex flex-col gap-1">
                        <label for="purchase-turn" class="font-medium text-ink">Turn</label>
                        <input
                            id="purchase-turn"
                            v-model="purchaseForm.turn"
                            type="number"
                            name="turn"
                            min="1"
                            required
                            :aria-invalid="purchaseForm.errors.turn !== undefined ? 'true' : 'false'"
                            :aria-describedby="purchaseForm.errors.turn !== undefined ? 'purchase-turn-error' : undefined"
                            class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink min-h-11"
                        >
                        <span
                            v-if="purchaseForm.errors.turn !== undefined"
                            id="purchase-turn-error"
                            class="text-xs text-risk"
                        >
                            {{ purchaseForm.errors.turn }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label for="purchase-item" class="font-medium text-ink">Item</label>
                        <!--
                            A text input with a `<datalist>` so a Trainer picks from the catalogue
                            by name, and a tampered value (an item the catalogue does not hold)
                            reaches the boundary for the same field error the Form Request would
                            raise on a normal submit. No default is selected; the empty value is
                            the form's initial state and a real item is required to post.
                        -->
                        <input
                            id="purchase-item"
                            v-model="purchaseForm.item"
                            type="text"
                            name="item"
                            list="purchase-item-list"
                            maxlength="120"
                            required
                            :aria-invalid="purchaseForm.errors.item !== undefined ? 'true' : 'false'"
                            :aria-describedby="purchaseForm.errors.item !== undefined ? 'purchase-item-error' : undefined"
                            class="min-w-32 rounded-md border border-rule bg-raised px-2 py-1 text-ink min-h-11"
                        >
                        <datalist id="purchase-item-list">
                            <option v-for="row in catalogue" :key="row.name" :value="row.name">
                                {{ row.cost }} coins
                            </option>
                        </datalist>
                        <span
                            v-if="purchaseForm.errors.item !== undefined"
                            id="purchase-item-error"
                            class="text-xs text-risk"
                        >
                            {{ purchaseForm.errors.item }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label for="purchase-cost" class="font-medium text-ink">Cost read</label>
                        <input
                            id="purchase-cost"
                            v-model.number="purchaseForm.cost"
                            type="number"
                            name="cost"
                            min="0"
                            required
                            :aria-invalid="purchaseForm.errors.cost !== undefined ? 'true' : 'false'"
                            :aria-describedby="purchaseForm.errors.cost !== undefined ? 'purchase-cost-error' : undefined"
                            class="w-20 rounded-md border border-rule bg-raised px-2 py-1 text-ink min-h-11"
                        >
                        <span
                            v-if="purchaseForm.errors.cost !== undefined"
                            id="purchase-cost-error"
                            class="text-xs text-risk"
                        >
                            {{ purchaseForm.errors.cost }}
                        </span>
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
                            :aria-invalid="purchaseForm.errors.effect !== undefined ? 'true' : 'false'"
                            :aria-describedby="purchaseForm.errors.effect !== undefined ? 'purchase-effect-error' : undefined"
                            class="rounded-md border border-rule bg-raised px-2 py-1 text-ink min-h-11"
                        >
                        <span
                            v-if="purchaseForm.errors.effect !== undefined"
                            id="purchase-effect-error"
                            class="text-xs text-risk"
                        >
                            {{ purchaseForm.errors.effect }}
                        </span>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 py-2 font-semibold text-ink-strong"
                    >
                        Record purchase
                    </button>

                    <p class="w-full text-xs text-ink-muted">
                        A higher-rank buy overwrites the lower one, and up to
                        {{ props.scenario.shop.max_copies }} copies of an item can be held at
                        a time. Cost and effect are entered as read, and checked against the
                        catalogue this scenario sells.
                    </p>

                    <p
                        v-if="purchaseForm.hasErrors"
                        class="w-full text-sm text-risk"
                        role="alert"
                    >
                        The purchase was not recorded. See the field marked below.
                    </p>
                </form>
            </template>
        </section>

        <section
            v-else-if="props.name === 'epithet_routes'"
            :aria-labelledby="epithetHeadingId"
            class="flex flex-col gap-3 rounded-md border border-rule bg-panel p-4"
        >
            <h3 :id="epithetHeadingId" tabindex="-1" class="text-base font-semibold text-ink-strong">
                Epithet routes
            </h3>

            <div v-if="props.scenario.epithet === null" class="text-sm text-ink-muted" role="status">
                <p>No scenario is set for this run, so no epithet checklist is composed.</p>
            </div>
            <template v-else>
                <p class="text-xs text-ink-muted">
                    Derived
                    <template v-if="props.scenario.epithet.seen_titles.length === 0">
                        from no entered races yet, so every named race below reads as outstanding.
                    </template>
                    <template v-else>
                        from {{ props.scenario.epithet.seen_titles.length }} entered race
                        {{ props.scenario.epithet.seen_titles.length === 1 ? 'name' : 'names' }}:
                        {{ props.scenario.epithet.seen_titles.join(', ') }}.
                    </template>
                    Requirements are transcribed from the Trackblazer guide; nothing here is
                    inferred about a race the tool cannot see.
                </p>

                <ul class="flex flex-col gap-1.5" aria-label="Epithet routes">
                    <li
                        v-for="(row, index) in props.scenario.epithet.rows"
                        :key="`${row.route}-${row.epithet}-${index}`"
                        :class="[
                            'flex flex-col gap-1 rounded-md border px-3 py-2 text-sm',
                            epithetToneClasses[row.state],
                        ]"
                    >
                        <span class="flex flex-wrap items-baseline justify-between gap-x-3">
                            <span class="font-semibold text-ink-strong">
                                <!-- Glyph plus word: state is never carried by colour alone
                                     (WCAG 1.4.1, plan §13 Selective Attention). -->
                                <span aria-hidden="true">{{ epithetGlyphs[row.state] }}</span>
                                <span>{{ row.epithet }}</span>
                            </span>
                            <span
                                :class="[
                                    'rounded px-2 py-0.5 font-mono text-xs font-bold uppercase tracking-wide',
                                    epithetToneClasses[row.state],
                                ]"
                            >
                                {{ epithetWords[row.state] }}
                            </span>
                        </span>
                        <span class="text-xs text-ink-muted">
                            {{ row.route }} · {{ row.reward }}
                            <template v-if="row.note !== null">
                                · needs {{ row.note }}, which this surface cannot read.
                            </template>
                            <template v-else-if="row.state !== 'earned' && row.missing.length > 0">
                                · outstanding: {{ row.missing.join(', ') }}
                            </template>
                        </span>
                    </li>
                </ul>
            </template>

            <p v-if="props.scenario.finale_official_title_absence !== null" class="text-xs text-ink-muted">
                <span class="font-semibold text-ink">Trackblazer finale</span>: a 3-race points
                league. The
                <span
                    class="font-semibold text-ink"
                    :title="props.scenario.finale_official_title_absence"
                >published client name</span>
                is unverified; the export label stands.
            </p>
        </section>
    </div>
</template>
