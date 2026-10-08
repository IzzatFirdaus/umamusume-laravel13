<script setup lang="ts">
/*
 * One training option, as the Trainer compares it (SCREEN-010, design-2.0 §24 and §35).
 *
 * **The card's headline slot is empty on purpose.** The brief that shaped this screen prints a
 * projected gain per stat and a failure percentage for each card; both are unsourced, and the advisor
 * spec puts them out of scope (`docs/research-scratch/PROCESS-PLANS.md` section `trainer-advisor.md`
 * §1 and §5, `ADR-0001` §3). They render `N/A` with the exclusion named in the `title`, never a
 * number and never a dash (`AGENTS.md` §5). `CareerTrainingDetailTest` pins the option payload to a
 * key set that has no home for a projected figure, so this file cannot be handed one.
 *
 * **Three facts in the collapsed card, the modifiers on expansion** (design-2.0 §35, plan §13 under
 * Cognitive Load): the cost, the supports entered, and the deficit against the Trainer's own target.
 * `Progressive disclosure` here is a real `aria-expanded` button over a region that stays in the
 * document with `hidden`, so the tab order is the same whether or not the card is expanded, and a
 * collapsed card keeps its modifiers out of the accessibility tree.
 *
 * **Risk is a sourced statement or nothing.** `RiskNotMeasured` is `ADR-0001` §3's own word for Wit,
 * which enters a turn at full Energy; no other card has a published risk rule to cite, so its risk
 * line is `N/A`. LOW / MEDIUM / HIGH would be a band no source states.
 *
 * **No scenario name reaches this file** (G-33): the scenario lines arrive as resolved label-and-value
 * pairs, and `recommended` is a boolean from the advisor rather than a verdict made here. The badge
 * beside a figure is `ProvenanceBadge.vue`'s, the only owner of the four glyphs.
 */
import ProvenanceBadge from '../ProvenanceBadge.vue';
import { computed, ref } from 'vue';

interface Cost {
    min: number;
    max: number;
    source: string;
    verified_at: string;
    confidence: string;
}

interface Effect {
    effect_id: number;
    name: string | null;
    display: string;
}

const props = defineProps<{
    option: {
        key: string;
        choice: string;
        recommended: boolean;
        current: number | null;
        target: number | null;
        cap: number;
        cap_bonus: number | null;
        deficit: number | null;
        band: string | null;
        reason: string | null;
        energy_after: { min: number; max: number } | null;
        cost: Cost;
        supports: { name: string; effects: Effect[] }[];
        scenario_effects: { label: string; value: string }[] | null;
    };
    scenarioLabel: string;
    deckRecorded: boolean;
    chosen: boolean;
    turn: number;
}>();

const emit = defineEmits<{ train: [] }>();

const open = ref(false);

const group = props.option.key.toLowerCase();
const detailsId = `training-details-${group}`;

// Full literal class strings, keyed rather than interpolated: Tailwind v4 scans sources for complete
// class names, so a built name compiles to nothing (the same rule `StatBand.vue` records).
const groupBorder: Record<string, string> = {
    speed: 'border-line-speed',
    stamina: 'border-line-stamina',
    power: 'border-line-power',
    guts: 'border-line-guts',
    wit: 'border-line-wit',
};

const fmt = (n: number): string => n.toLocaleString('en-US');

// The cost prints as a range because the constant is one: it scales with a training level this tool
// does not track, so a single point would be invented precision (`ADR-0001` §2). Both ends, worst first.
const costText = computed(() =>
    props.option.cost.min === props.option.cost.max
        ? `E−${props.option.cost.min}`
        : `E−${props.option.cost.max} … E−${props.option.cost.min}`,
);

const costTitle = computed(() => {
    const stale = props.option.cost.confidence === 'stale'
        ? ' The source behind this range is marked stale.'
        : '';

    return `Declared constant: ${props.option.cost.source}, read ${props.option.cost.verified_at}.${stale}`;
});

const energyAfterTitle = computed(() => {
    const after = props.option.energy_after;

    if (after === null) {
        return 'This run has recorded no Energy, so the declared cost has nothing to subtract from.';
    }

    return after.min === after.max
        ? `${fmt(after.min)} leaves the entered Energy minus the declared cost. Calculated, not a reading.`
        : `${fmt(after.min)} to ${fmt(after.max)}: the entered Energy minus each end of the cost range. Calculated, not a reading.`;
});

const gainsTitle =
    'No source publishes a per-training stat yield; it turns on support bonds, training level and '
    + 'facility, none of which this tool models. Excluded from the advisor by design: '
    + 'PROCESS-PLANS.md trainer-advisor.md §1, ADR-0001 §3.';

const failureTitle =
    'No source publishes a failure curve, table or single probability. ADR-0001 §3 keeps a numeric '
    + 'estimate opt-in, and PROCESS-PLANS.md trainer-advisor.md §5 defers it to an advisor v1.1 that '
    + 'has an owner-ruled model behind it.';

const bondTitle =
    'Bond is not stored for a Support Card: deck_slots holds a card and its slot position, nothing '
    + 'else. Support-bond modelling is out of the advisor’s v1 scope '
    + '(PROCESS-PLANS.md trainer-advisor.md §1).';

const riskTitle =
    'No sourced rule ranks this option’s risk. The only Energy line any source states is the 50 '
    + 'advisory one, and that describes the turn, not the discipline (ADR-0001 §3).';

const witRiskTitle =
    'Wit enters the turn at full Energy, so a band computed from Energy would assert an exemption the '
    + 'sources leave unsettled. RiskNotMeasured is ADR-0001 §3’s own word for it.';

const deficitTitle = computed(() => {
    if (props.option.target === null) {
        return 'No build target was entered for this stat, so there is no deficit to state.';
    }

    if (props.option.current === null) {
        return 'This run has recorded no turn yet, so the stat has no entered value to read a deficit from.';
    }

    return `${fmt(props.option.target)} entered as the target, minus ${fmt(props.option.current)} entered on the latest turn: the advisor’s own arithmetic.`;
});

const supportsTitle = computed(() =>
    props.deckRecorded
        ? `Counted from the deck this run records: the cards whose type reads ${props.option.key}.`
        : 'No Support Cards are recorded for this run, so nothing can be counted for this training.',
);

const scenarioTitle = computed(() => {
    if (props.option.scenario_effects === null) {
        return 'This run declares no scenario, so no scenario effect is claimed.';
    }

    if (props.option.scenario_effects.length === 0) {
        return `The ${props.scenarioLabel} entry declares nothing that bears on this training beyond its cap bonus.`;
    }

    return `Read from the ${props.scenarioLabel} entry of config/scenarios.php.`;
});

const capTitle = computed(() =>
    props.option.cap_bonus === null
        ? 'The run declares no scenario, so no scenario bonus is claimed on this ceiling.'
        : `Base ceiling plus the ${props.scenarioLabel} bonus of ${props.option.cap_bonus}, from config/scenarios.php. The turn validator uses the same figure (ADR-0015).`,
);

const showDeficit = computed(() => props.option.deficit !== null && props.option.target !== null);

const deficitText = computed(() => (props.option.deficit === null ? 'N/A' : fmt(props.option.deficit)));
</script>

<template>
    <article
        :class="[
            'flex min-w-0 flex-col rounded-md border bg-panel p-4',
            props.option.recommended
                ? 'border-2 border-pick-line'
                : `border ${groupBorder[group] ?? 'border-rule'}`,
        ]"
    >
        <h3 class="flex flex-wrap items-baseline gap-x-2 text-base font-bold text-ink-strong">
            {{ props.option.key }}
            <span v-if="props.option.recommended" class="text-xs font-bold">
                <span aria-hidden="true">★</span> RECOMMENDED
            </span>
        </h3>

        <p v-if="props.option.reason !== null" class="mt-1 text-sm text-ink">{{ props.option.reason }}</p>
        <p v-else class="mt-1 text-sm text-ink-muted">
            Not ranked. <span :title="`The advisor states no reason for this option on this turn: ${props.option.key} has no target deficit to name and no Energy reading to spend.`">Why is in the advisor’s line above the cards.</span>
        </p>

        <dl class="mt-3 space-y-2 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Expected gains</dt>
                <dd class="mt-0.5">
                    <span :title="gainsTitle" class="font-mono tabular-nums text-ink-strong">N/A</span>
                    <ProvenanceBadge state="unknown" class="ml-1 align-middle" />
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Energy cost</dt>
                <dd class="mt-0.5">
                    <span :title="costTitle" class="font-mono tabular-nums text-ink-strong">{{ costText }}</span>
                    <ProvenanceBadge state="confirmed" class="ml-1 align-middle" />
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Supports entered for this training</dt>
                <dd class="mt-0.5">
                    <span :title="supportsTitle" class="font-mono tabular-nums text-ink-strong">{{ props.option.supports.length }}</span>
                    <ProvenanceBadge state="confirmed" class="ml-1 align-middle" />
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Target deficit</dt>
                <dd class="mt-0.5">
                    <span :title="deficitTitle" class="font-mono tabular-nums text-ink-strong">
                        {{ showDeficit ? deficitText : 'N/A' }}
                    </span>
                    <ProvenanceBadge v-if="showDeficit" state="calculated" class="ml-1 align-middle" />
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Risk</dt>
                <dd class="mt-0.5">
                    <span
                        v-if="props.option.band === 'RiskNotMeasured'"
                        :title="witRiskTitle"
                        class="font-mono text-ink-strong"
                    >RiskNotMeasured</span>
                    <span v-else :title="riskTitle" class="font-mono text-ink-strong">N/A</span>
                </dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
                :aria-pressed="props.chosen"
                @click="emit('train')"
            >
                Train
            </button>

            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 text-sm font-bold text-ink-strong hover:border-green-line"
                :aria-expanded="open"
                :aria-controls="detailsId"
                @click="open = !open"
            >
                <span aria-hidden="true">{{ open ? '▾' : '▸' }}</span>
                Inspect details
            </button>
        </div>

        <p v-if="props.chosen" role="status" class="mt-2 text-xs text-ink-muted">
            {{ props.option.key }} is the choice held for turn {{ props.turn }}. Enter what the client
            showed and preview it below.
        </p>

        <div :id="detailsId" :hidden="!open" class="mt-3 border-t border-rule pt-3">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Energy after this turn</dt>
                    <dd class="mt-0.5">
                        <span :title="energyAfterTitle" class="font-mono tabular-nums text-ink-strong">
                            <template v-if="props.option.energy_after === null">N/A</template>
                            <template v-else-if="props.option.energy_after.min === props.option.energy_after.max">
                                {{ fmt(props.option.energy_after.min) }}
                            </template>
                            <template v-else>
                                {{ fmt(props.option.energy_after.min) }} to {{ fmt(props.option.energy_after.max) }}
                            </template>
                        </span>
                        <ProvenanceBadge
                            v-if="props.option.energy_after !== null"
                            state="calculated"
                            class="ml-1 align-middle"
                        />
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Failure probability</dt>
                    <dd class="mt-0.5">
                        <span :title="failureTitle" class="font-mono text-ink-strong">N/A</span>
                        <ProvenanceBadge state="unknown" class="ml-1 align-middle" />
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Support effects</dt>
                    <dd v-if="props.option.supports.length > 0" class="mt-1 space-y-2">
                        <div
                            v-for="(card, cardIndex) in props.option.supports"
                            :key="`${card.name}-${cardIndex}`"
                            class="rounded-md border border-rule bg-raised px-3 py-2"
                        >
                            <span class="block font-semibold text-ink-strong">{{ card.name }}</span>
                            <ul v-if="card.effects.length > 0" class="mt-1 space-y-0.5">
                                <li
                                    v-for="effect in card.effects"
                                    :key="effect.effect_id"
                                    class="flex flex-wrap items-baseline justify-between gap-2 text-xs"
                                >
                                    <!-- A dictionary row the export carries no label for prints its own
                                         id rather than a word this tool invented (D-20). -->
                                    <span class="text-ink">{{ effect.name ?? `Effect ${effect.effect_id}` }}</span>
                                    <span
                                        title="The anchor the source states at the card's highest listed level; nothing between levels is interpolated (D-256)."
                                        class="font-mono tabular-nums text-ink-strong"
                                    >{{ effect.display }}</span>
                                </li>
                            </ul>
                            <p v-else class="mt-1 text-xs text-ink-muted">
                                The source states no anchor for this card, so no value is claimed.
                            </p>
                        </div>
                    </dd>
                    <dd v-else class="mt-0.5">
                        <span :title="supportsTitle" class="font-mono text-ink-strong">N/A</span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Bond gains</dt>
                    <dd class="mt-0.5">
                        <span :title="bondTitle" class="font-mono text-ink-strong">N/A</span>
                        <ProvenanceBadge state="unknown" class="ml-1 align-middle" />
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Scenario effects</dt>
                    <dd v-if="props.option.scenario_effects !== null && props.option.scenario_effects.length > 0" class="mt-1">
                        <ul class="space-y-1">
                            <li
                                v-for="effect in props.option.scenario_effects"
                                :key="effect.label"
                                class="flex flex-wrap items-baseline justify-between gap-2"
                            >
                                <span :title="scenarioTitle" class="text-ink">{{ effect.label }}</span>
                                <span :title="scenarioTitle" class="font-mono tabular-nums text-ink-strong">{{ effect.value }}</span>
                            </li>
                        </ul>
                    </dd>
                    <dd v-else class="mt-0.5">
                        <span :title="scenarioTitle" class="font-mono text-ink-strong">N/A</span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Target impact</dt>
                    <dd class="mt-1 space-y-1">
                        <p class="flex flex-wrap items-baseline justify-between gap-2">
                            <span class="text-ink">Entered value</span>
                            <span
                                :title="props.option.current === null ? 'No turn has recorded this stat yet.' : 'Entered from the client on the latest logged turn.'"
                                class="font-mono tabular-nums text-ink-strong"
                            >{{ props.option.current === null ? 'N/A' : fmt(props.option.current) }}</span>
                        </p>
                        <p class="flex flex-wrap items-baseline justify-between gap-2">
                            <span class="text-ink">Your target</span>
                            <span
                                :title="props.option.target === null ? 'No build target was entered for this stat.' : 'Entered on the build target screen.'"
                                class="font-mono tabular-nums text-ink-strong"
                            >{{ props.option.target === null ? 'N/A' : fmt(props.option.target) }}</span>
                        </p>
                        <p class="flex flex-wrap items-baseline justify-between gap-2">
                            <span class="text-ink">Scenario cap</span>
                            <span :title="capTitle" class="font-mono tabular-nums text-ink-strong">{{ fmt(props.option.cap) }}</span>
                        </p>
                        <p class="text-xs text-ink-muted">
                            How much of the deficit one session closes is not sourced, so no progress
                            figure is drawn. The deficit is the distance to target; the turn that closes it
                            is the one you record.
                        </p>
                    </dd>
                </div>
            </dl>
        </div>
    </article>
</template>
