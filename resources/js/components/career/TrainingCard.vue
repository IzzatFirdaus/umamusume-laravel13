<script setup lang="ts">
/*
 * One training option, as the Trainer compares it (SCREEN-010, design-2.0 §24 and §35).
 *
 * **The card's headline slot is empty on purpose.** The brief that shaped this screen prints a
 * projected gain per stat and a failure percentage for each card; both are unsourced, and the advisor
 * spec puts them out of scope (`docs/research-scratch/PROCESS-PLANS.md` section `trainer-advisor.md`
 * §1 and §5, `ADR-0001` §3). They render through `AbsenceValue.vue`: `N/A` plus the exclusion named in
 * the disclosure, never a number and never a dash (`AGENTS.md` §5). A `title` was the first channel here
 * and it reached a mouse and nothing else, which is why the pattern moved. `CareerTrainingDetailTest`
 * pins the option payload to a key set that has no home for a projected figure, so this file cannot be
 * handed one.
 *
 * **Five elements in the collapsed card, the modifiers on expansion** (design-2.0 §35):
 * the stat figure, the server tag, the Confirmed badge, and the two action buttons. Citations,
 * supports count, target deficit, and risk defer to the Inspect details panel (progressive-
 * disclosure depth = 2: toggle → panel). `Progressive disclosure` here is a real `aria-expanded`
 * button over a region that stays in the document with `hidden`, so the tab order is the same
 * whether or not the card is expanded, and a collapsed card keeps its modifiers out of the
 * accessibility tree.
 *
 * **Risk is a sourced statement or nothing.** `RiskNotMeasured` is `ADR-0001` §3's own word for Wit,
 * which enters a turn at full Energy; no other card has a published risk rule to cite, so its risk
 * line is `N/A`. LOW / MEDIUM / HIGH would be a band no source states.
 *
 * **No scenario name reaches this file** (G-33): the scenario lines arrive as resolved label-and-value
 * pairs, and `recommended` is a boolean from the advisor rather than a verdict made here. The badge
 * beside a figure is `ProvenanceBadge.vue`'s, the only owner of the four glyphs.
 */
import AbsenceValue from '../AbsenceValue.vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import { buttonClasses } from '../Form/buttonStyles';
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

/** One published per-level row, as the scenario matrix's `training_tables` states it. */
interface PublishedRow {
    level: number;
    text: string;
    server: string;
    source: string;
    notes: string[];
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
        published: {
            gains: PublishedRow | null;
            gains_absence: string | null;
            cost: PublishedRow | null;
            risk_reason: string | null;
        };
    };
    scenarioLabel: string;
    deckRecorded: boolean;
    chosen: boolean;
    turn: number;
    /**
     * Whether the run has a previous turn. The card uses it to decide whether an option with no
     * advisor reason gets the `Rank` row: in a run that has logged nothing, the screen's own
     * empty-state paragraph explains the absence once, and repeating it per card is the noise the
     * 2026-10-20 fragment ruling removed (logged-state only, ruling 3f.1).
     */
    hasPreviousTurn: boolean;
}>();

const emit = defineEmits<{ train: [] }>();

/*
 * Why this option carries no rank. The wording is the removed fragment's own clause, verbatim from
 * `958746f^` minus the pointer prefix it began with, so nothing here is newly written copy (ruling
 * 3f.3). The pointer ("the advisor's line above the cards") is what the ruling deleted, because that
 * line names the recommended card and never explained a different card's absence.
 */
const unrankedReason = computed(
    () => `${props.option.key} has no target deficit to name and no Energy reading to spend.`,
);

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
// When the scenario matrix carries a published per-level row for this discipline, that row's single
// sourced figure takes the value slot and the declared constant drops to the provenance line — the
// constant still computes `energy_after`, so it stays visible either way (`ADR-0001` §2), and the
// governed `session_cost` key itself is untouched by this rendering.
const publishedCost = computed(() => props.option.published.cost);

const costText = computed(() =>
    props.option.cost.min === props.option.cost.max
        ? (props.option.cost.min === 0 ? '0 Energy' : `−${props.option.cost.min} Energy`)
        : `−${props.option.cost.min} … −${props.option.cost.max} Energy`,
);

/**
 * Wit is the one discipline the client refunds: `config/advisor.php` carries `wit_cost` as 0, sourced.
 * The tag states that in words rather than leaving the trailer to be inferred from a "0 Energy" figure
 * (DM-7), and the sign is the mechanic's, not a decoration.
 */
const isWit = computed(() => props.option.choice.toLowerCase().endsWith('-wit'));

const costTitle = computed(() => {
    const cost = props.option.published.cost;

    if (cost !== null) {
        return `Published per-level table: ${cost.source}. The level is the most recent one this run recorded for this training (Lv${cost.level}); the declared constant below is what Energy after computes from.`
            + cost.notes.map((note) => ` ${note}`).join('');
    }

    const stale = props.option.cost.confidence === 'stale'
        ? ' The source behind this range is marked stale.'
        : '';

    return `Declared constant: ${props.option.cost.source}, read ${props.option.cost.verified_at}.${stale}`;
});

// The same provenance, printed. `ADR-0001` §2 requires the constants behind a derived number to be
// visible wherever the number appears; a `title` is reachable by mouse only, so on a keyboard or
// a touch screen the source, its read date and its stale flag were simply absent. When a published
// row renders, its own source and level join the line, and the matrix's server tag states which
// server the figures are from — for Unity Cup the rework's SP divergence rides the notes, which is
// the wiring's Caveat 3 made visible where the scenario's costs render.
const costDetail = computed(() => {
    const declared = `Declared constant: ${props.option.cost.source}, read ${props.option.cost.verified_at}`
        + (props.option.cost.confidence === 'stale' ? ', source marked stale' : '');

    const cost = props.option.published.cost;

    if (cost === null) {
        return declared;
    }

    return `Published table: ${cost.source} · Lv${cost.level}, the level this run last recorded, server ${cost.server}. ${declared}`
        + cost.notes.map((note) => ` ${note}`).join('');
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

/** The figure for the present case; the absent case renders through `AbsenceValue` and has no text. */
const energyAfterText = computed(() => {
    const after = props.option.energy_after;

    if (after === null) {
        return '';
    }

    return after.min === after.max ? fmt(after.min) : `${fmt(after.min)} to ${fmt(after.max)}`;
});

// The published gains row: the scenario matrix's per-level base figures for this discipline, at
// the facility level this run last recorded. The [JP] tag beside the value is the wiring's
// Caveat 2: a JP figure presented to a Global run without the tag is the silent promotion the
// reference guide's deviation log records a prior occurrence of, so the tag never strips.
const publishedGains = computed(() => props.option.published.gains);

const gainsReason = computed(() => {
    const gains = props.option.published.gains;

    if (gains === null) {
        return props.option.published.gains_absence
            ?? 'No published per-level row applies to this training.';
    }

    return `Published per-level base gains: ${gains.source}. The level is the most recent one this run recorded for this training (Lv${gains.level}); actual gains also scale with mood, bonds and growth rate, none of which this tool models.`
        + gains.notes.map((note) => ` ${note}`).join('');
});

const gainsDetail = computed(() => {
    const gains = props.option.published.gains;

    if (gains === null) {
        return null;
    }

    return `Published table: ${gains.source} · Lv${gains.level}, the level this run last recorded.`;
});

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

// The risk row's reason: the scenario matrix's own measured negative — the 2026-10-15 artifact's
// reason verbatim — where the run declares a scenario, and the advisor-era statement where it
// does not. Wit never reaches this row: its card prints the engine's own RiskNotMeasured word.
const riskReason = computed(() => props.option.published.risk_reason ?? riskTitle);

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

    // Both figures exist and the deficit still does not. The only way the advisor leaves an option out
    // with a target and a reading present (`TrainerAdvisor::reason()`, and `deficit` arrives as
    // `?->deficitClosed`) is a run with no recorded Energy, and `energy_after` is null exactly when that
    // reading is. Without this arm the sentence below — which states a figure this row does not have —
    // would be printed as the reason for the absence (`DESIGN.md` §5, worded by kind).
    if (props.option.deficit === null) {
        return 'This run has recorded no Energy, so the advisor states no deficit for this option.';
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
        :data-discipline="group"
        :class="[
            'flex min-w-0 flex-col rounded-lg border bg-panel p-4',
            props.option.recommended
                ? 'border-2 border-pick-line'
                : `border ${groupBorder[group] ?? 'border-rule'}`,
        ]"
    >
        <h3 class="flex flex-wrap items-baseline gap-x-2 text-lg font-bold text-ink-strong">
            {{ props.option.key }}
            <span v-if="props.option.recommended" class="text-xs font-bold">
                <span aria-hidden="true">★</span> RECOMMENDED
            </span>
        </h3>

        <p v-if="props.option.reason !== null" class="mt-1 text-sm text-ink">{{ props.option.reason }}</p>

        <dl class="mt-3 space-y-2 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Expected gains</dt>
                <dd class="mt-0.5">
                    <template v-if="publishedGains !== null">
                        <span :title="gainsReason" class="font-mono tabular-nums text-ink-strong">{{ publishedGains.text }}</span>
                        <span class="text-xs text-ink-muted"> [{{ publishedGains.server }}]</span>
                    </template>
                    <AbsenceValue v-else class="font-mono tabular-nums" :reason="gainsReason" compact />
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Energy cost</dt>
                <dd class="mt-0.5">
                    <template v-if="publishedCost !== null">
                        <span
                            :title="costTitle"
                            class="font-mono tabular-nums"
                            :class="props.option.cost.min === 0 ? 'text-ink-strong' : 'text-[var(--color-down)]'"
                        >{{ publishedCost.text }} Energy</span>
                        <span class="text-xs text-ink-muted"> [{{ publishedCost.server }}]</span>
                    </template>
                    <template v-else>
                        <span
                            :title="costTitle"
                            class="font-mono tabular-nums"
                            :class="props.option.cost.min === 0 ? 'text-ink-strong' : 'text-[var(--color-down)]'"
                        >{{ costText }}</span>
                    </template>
                    <!-- DM-7: Wit training costs 0 Energy, so the card states the refund in words beside
                         the figure rather than leaving a bare "0" to be read as a missing value. It sits
                         outside both branches: the corpus publishes a cost for Wit too, so a tag nested in
                         the derived branch would vanish on the one card that needs it. -->
                    <span v-if="isWit" class="tag auto ml-1">no Energy</span>
                    <!-- `ADR-0001` §2 requires the constants behind a derived number to be visible wherever
                         the number appears, and a `title` is reachable by mouse only. The owner ruled the inline form
                         (2026-10-09); `ScenarioPanel.vue:205-208` and `Dashboard.vue:300-301` already print a
                         visible read date, so this is the shipped idiom extended, not a new one. It stays inside
                         the `dd`: as a sibling of `dt`/`dd` it broke the definition list's content model and axe
                         failed the page on `definition-list` (WCAG 1.3.1). -->
                </dd>
            </div>

            <!-- The rank, in the same row rhythm as the two rows above: a `<dt>` name and a `<dd>`
                 carrying the absence through `AbsenceValue`, which is the tree's one owner of "no value,
                 and why" (ruling 3f.4 - reused, not extended). Logged runs only (ruling 3f.1). -->
            <div v-if="props.option.reason === null && props.hasPreviousTurn">
                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Rank</dt>
                <dd class="mt-0.5">
                    <AbsenceValue :reason="unrankedReason" compact />
                </dd>
            </div>

            <!-- Supports, Target deficit, and Risk defer to the Inspect details panel: the default
                 card carries the stat figure, its server tag and the Confirmed badge, nothing more
                 (design-2.0 §35, progressive-disclosure depth = 2). -->
        </dl>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <!-- A pre-selection, not a commit: the choice is recorded by the form's submit on
                 `TrainingDetail.vue`, which is the screen's one filled commit action. The ruling
                 (2026-10-20, option A) put this on the canonical `secondary`, so the card cannot read
                 as the thing that writes the turn. -->
            <button
                type="button"
                :class="buttonClasses('secondary')"
                :aria-pressed="props.chosen"
                @click="emit('train')"
            >
                Train
            </button>

            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 text-sm font-bold text-ink-strong hover:border-green-line transition-colors duration-150 ease-out"
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
            showed and record it below.
        </p>

        <div :id="detailsId" :hidden="!open" class="mt-3 border-t border-rule pt-3">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Energy after this turn</dt>
                    <dd class="mt-0.5">
                        <AbsenceValue
                            v-if="props.option.energy_after === null"
                            class="font-mono tabular-nums"
                            :reason="energyAfterTitle"
                            compact
                        />
                        <span v-else :title="energyAfterTitle" class="font-mono tabular-nums text-ink-strong">
                            {{ energyAfterText }}
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
                        <AbsenceValue class="font-mono tabular-nums" :reason="failureTitle" compact />
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Support effects</dt>
                    <dd v-if="props.option.supports.length > 0" class="mt-1 space-y-2">
                        <div
                            v-for="(card, cardIndex) in props.option.supports"
                            :key="`${card.name}-${cardIndex}`"
                            class="card px-3 py-2"
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
                                        title="The anchor the source states at the card's highest listed level; nothing between levels is interpolated."
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
                        <AbsenceValue class="font-mono" :reason="supportsTitle" compact />
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Bond gains</dt>
                    <dd class="mt-0.5">
                        <AbsenceValue class="font-mono" :reason="bondTitle" compact />
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
                        <AbsenceValue class="font-mono" :reason="scenarioTitle" compact />
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

                 <!-- Sources, Supports count, Target deficit, and Risk: deferred from the default card
                      to preserve the five-element ceiling (design-2.0 §35). Every value still travels;
                      only its default placement moves. -->
                 <div v-if="publishedGains !== null || publishedCost !== null">
                     <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Sources</dt>
                     <dd class="mt-0.5 space-y-0.5 text-xs text-ink-muted">
                         <p v-if="gainsDetail !== null">{{ gainsDetail }}</p>
                         <p v-if="costDetail !== null">{{ costDetail }}</p>
                     </dd>
                 </div>

                 <div>
                     <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Supports count</dt>
                     <dd class="mt-0.5">
                         <span :title="supportsTitle" class="font-mono tabular-nums text-ink-strong">{{ props.option.supports.length }}</span>
                     </dd>
                 </div>

                 <div>
                     <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Target deficit</dt>
                     <dd class="mt-0.5">
                         <template v-if="showDeficit">
                             <span :title="deficitTitle" class="font-mono tabular-nums text-ink-strong">{{ deficitText }}</span>
                         </template>
                         <AbsenceValue v-else class="font-mono tabular-nums" :reason="deficitTitle" compact />
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
                         <AbsenceValue v-else class="font-mono" :reason="riskReason" compact />
                     </dd>
                 </div>
             </dl>
        </div>
    </article>
</template>
