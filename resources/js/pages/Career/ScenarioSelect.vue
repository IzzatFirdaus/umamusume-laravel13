<script setup lang="ts">
/*
 * SCREEN-002, Scenario Selection (`ADR-0020` §1; plan §8 D2). Step 1 of the setup wizard.
 *
 * Every value on a card arrives already derived by `ScenarioSelectController`: the page prints props
 * and holds no `config()` lookup and no scenario-name branch, which is what gate G-33 tests. Adding a
 * fifth scenario is one entry in `config/scenarios.php` and no edit here —
 * `CareerScenarioSelectTest` proves that by injecting a synthetic scenario and reading the card back.
 *
 * The selection is a card-button pattern rather than a hidden radio group: a native `<button>` with
 * `aria-pressed` is in the tab order, needs no arrow-key handler to be reachable, and states which
 * card holds the stored choice after the PUT round trip. One action per card keeps the primary action
 * where the decision is (`design-2.0` §13, Fitts's Law) without a sixth control that does the same
 * thing, and that one action is a toggle: the screen renders `Stored choice: N/A`, so "no scenario" is
 * a state a Trainer can be in, and a state you can enter has to be exitable. The button's own label
 * says which way the next click goes.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import { BaseButton, LinkButton } from '../../components/Form';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

interface Optimizes {
    kind: 'uniform' | 'peaked' | 'unknown';
    stats: string[];
    bonus: number | null;
}

interface ScenarioCard {
    key: string;
    label: string;
    documented: boolean | null;
    /** The matrix's baseline entry (`config/scenarios.baseline`); the card states it as a pill. */
    is_baseline: boolean;
    /** The scenario's turn-rail observation field, where it has one; stated as the "Performance input" tag. */
    performance_input: boolean;
    optimizes: Optimizes;
    /** The two composition tags the card face prints (`config/scenarios.php` `card_tags`). */
    card_tags: string[];
    systems: string[];
    resources: string[];
    loop: string[];
    live_on_global: string | null;
    /** The published caps, composed as the matrix states them ("1300 ×4 · Wit 1800"), or null. */
    caps_label: string | null;
    metadata: {
        summary: string | null;
        planning_focus: string | null;
        planning_focus_source: string | null;
        recommended_use_reason: string | null;
        planning_focus_reason: string | null;
        recommended_use_source: string | null;
        key_mechanics: string[];
    } | null;
    description: string | null;
    recommended_use: string | null;
    rule_family: string | null;
    ruleset_date: { value: string; title: string } | null;
}

const props = defineProps<{
    scenarios: ScenarioCard[];
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    choices: { label: string; value: string | null; url: string | null }[];
    selected: string | null;
    ruleset: { value: string | null; title: string };
    /** `config/scenarios.verified_at`: the date the caps quoted in the footnote were checked. */
    verified_at: string;
    /** `route('career.scenario.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
}>();

const choosing = ref<string | null>(null);

const form = useForm<{ scenario: string | null }>({ scenario: null });

function choose(scenario: ScenarioCard): void {
    choosing.value = scenario.key;
    form.scenario = scenario.key;
    // The write URL is supplied by the server (`career.scenario.store`) so the page never states
    // it as a literal. Focus is restored in `onSuccess`, after the new props land, because the
    // component instance survives a client-side visit: an `onMounted` hook would never re-run,
    // and on a cold page load it would steal focus from a Trainer who has not acted yet
    // (WCAG 2.4.3 focus order, 3.2.6 consistent help). `nextTick` waits for Vue to repaint with the
    // stored selection. The target is the radio now that the card IS the radio's label: the old
    // per-card button no longer exists, and a focus restore aimed at a removed id silently did nothing.
    form.put(props.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => {
                document.getElementById(`scenario-radio-${scenario.key}`)?.focus();
            });
        },
        onFinish: () => {
            choosing.value = null;
        },
    });
}

/**
 * Move the selection to the previous scenario card (arrow-left/arrow-up).
 * Wraps from the first card to the last.
 */
function prevCard(): void {
    const index = props.scenarios.findIndex((c) => c.key === props.selected);
    const next = index <= 0 ? props.scenarios.length - 1 : index - 1;
    choose(props.scenarios[next]);
}

/**
 * Move the selection to the next scenario card (arrow-right/arrow-down).
 * Wraps from the last card to the first.
 */
function nextCard(): void {
    const index = props.scenarios.findIndex((c) => c.key === props.selected);
    const next = index >= props.scenarios.length - 1 ? 0 : index + 1;
    choose(props.scenarios[next]);
}

/**
 * Commit the step and move on.
 *
 * A radio group holds the choice; this is the one action that advances. It is disabled until a card
 * is selected, because the next step needs a scenario to compose against and a Continue that lands on
 * a page with nothing to show is worse than a disabled button that says why.
 */
function continueStep(): void {
    if (props.selected === null) {
        return;
    }

    router.visit('/career/setup/trainee');
}

</script>

<template>
    <SetupLayout :step="1" :visited="props.visited" :saved="props.saved">
        <Head title="Which scenario is this run?" />
        <template #head>
            <PageHeader
                eyebrow="New career"
                title="Which scenario is this run?"
                description="The scenario decides which panels the cockpit composes, which caps apply, and which systems exist. It cannot be changed after the first turn."
                position="Step 1 of 6"
            >
                <template #actions>
                    <LinkButton href="/" variant="secondary">Cancel</LinkButton>
                    <!-- One filled action (HFW annotation: "Continue to trainee" is the only filled
                         button; Cancel is an outline). Disabled until a card holds, because the next
                         step needs a scenario to compose against. -->
                    <BaseButton
                        type="button"
                        variant="primary"
                        :disabled="props.selected === null || form.processing"
                        @click="continueStep"
                    >
                        Continue to trainee
                    </BaseButton>
                </template>
            </PageHeader>
        </template>

        <p
            v-if="form.errors.scenario"
            id="scenario-error"
            role="alert"
            class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
        >
            {{ form.errors.scenario }}
        </p>

        <p v-if="form.processing" role="status" class="mt-3 text-sm text-ink-muted">Saving your choice…</p>

        <!-- A page renders this branch when the matrix composes nothing. It is reachable only by
             deleting every scenario from `config/scenarios.php`, and it is stated rather than left
             blank so the screen never shows an empty grid and calls it a choice. -->
        <p
            v-if="props.scenarios.length === 0"
            class="mt-8 rounded-lg border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted"
        >
            No scenario is composed. The scenario settings hold none, so there is nothing to choose
            and no career can be started until one is added.
        </p>

        <!--
          A real radio group, not a row of toggle buttons. The choice is one of four and only one can
          hold, which is what a radio group means to a screen reader and to a keyboard: arrow keys move
          within it, and the group announces itself as a single question. The card is the radio's
          label, so the whole card is the hit target while the input stays the thing that holds state.

          Selection is carried by fill AND a check glyph, never fill alone (WCAG 1.4.1). Choosing a
          card does not navigate: the Trainer may change their mind, and the single Continue button in
          the footer is what commits the step.
        -->
        <fieldset v-else class="mt-6">
            <legend class="text-sm font-semibold text-ink-strong">Which scenario is this career in?</legend>

            <div class="mt-3 grid grid-cols-1 gap-4 min-[821px]:grid-cols-2">
                <div
                    v-for="card in props.scenarios"
                    :key="card.key"
                    data-scenario-card
                    data-region="card"
                    class="relative rounded-lg border border-rule bg-panel p-4"
                    :class="props.selected === card.key ? 'border-2 border-pick-line bg-green-tint' : ''"
                >
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <label
                        :for="`scenario-radio-${card.key}`"
                        class="flex min-h-11 cursor-pointer items-start gap-2"
                    >
                        <input
                            :id="`scenario-radio-${card.key}`"
                            type="radio"
                            name="scenario"
                            class="mt-1 h-4 w-4 shrink-0 accent-chrome"
                            :value="card.key"
                            :checked="props.selected === card.key"
                            :aria-checked="props.selected === card.key"
                            :aria-describedby="form.errors.scenario ? 'scenario-error' : undefined"
                            :disabled="form.processing"
                            @change="choose(card)"
                            @keydown.arrow.left="prevCard"
                            @keydown.arrow.right="nextCard"
                            @keydown.arrow.up="prevCard"
                            @keydown.arrow.down="nextCard"
                        >
                        <h2 class="text-lg font-semibold text-ink-strong">{{ card.label }}</h2>
                    </label>
                    <!-- The HFW's card face order: name, then the baseline pill, then the release date,
                         then the paragraph and the tag row. The pill and the tag sit OUTSIDE the `h2`:
                         a heading that swallowed them would carry an accessible name longer than the
                         scenario, and `card()` in the spec addresses each card by that exact name. -->
                    <span v-if="card.is_baseline" class="pill pill-neutral">Baseline</span>
                    <span
                        v-if="card.live_on_global"
                        data-region="tag"
                        class="rounded-full border border-rule px-2 py-0.5 text-xs text-ink-muted"
                    >Global · {{ card.live_on_global }}</span>
                    <!-- Glyph and word, so the documentation state is never carried by colour or by the
                         dot alone (WCAG 1.4.1). -->
                    <span
                        v-if="card.documented === false"
                        class="inline-flex items-center gap-1 rounded-full bg-sunken px-2 py-0.5 text-xs font-bold text-ink-strong"
                        title="The matrix records no mechanics guide for this scenario, so only baseline tracking is available."
                    >
                        <span aria-hidden="true">◐</span>
                        PARTIALLY DOCUMENTED
                    </span>
                </div>

                <!-- The selected card states its state in words as well as in the input, so a
                     sighted reader who cannot see the accent colour still knows which card holds. -->
                <p v-if="props.selected === card.key" class="mt-1 text-xs font-semibold text-ink-strong">
                    <span aria-hidden="true">✓</span> Selected
                </p>

                <!-- The option-card grammar (Hick's Law, ux-laws §3.2 row 4): the choice reads from a
                     name, a status word and one subtitle, and the eight-row fact grid with the matrix's
                     own metadata sits behind the card's one disclosure - the same pattern the key
                     mechanics collapse already uses. The subtitle is the matrix's summary where the
                     matrix has one, and the named absence where it does not; writing a sentence here
                     would be an invented fact (AGENTS.md §5). -->
                <p v-if="card.metadata?.summary" data-field="summary" class="mt-2 text-sm text-ink-muted">{{ card.metadata.summary }}</p>
                <AbsenceValue
                    v-else
                    reason="No sourced summary line exists for this scenario in this repository."
                />

                <!-- The composition tags and the published caps, on the card face (HFW annotation 4:
                     the caps print as the matrix states them, the outlier named rather than collapsed). -->
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span
                        v-for="tag in card.card_tags"
                        :key="`compose-${tag}`"
                        data-region="tag"
                        class="rounded-full border border-rule px-2 py-0.5 text-xs text-ink-muted"
                    >{{ tag }}</span>
                    <span
                        v-if="card.performance_input"
                        data-region="tag"
                        class="rounded-full border border-rule px-2 py-0.5 text-xs text-ink-muted"
                    >Performance input</span>
                    <span
                        v-if="card.caps_label"
                        data-region="tag"
                        class="rounded-full border border-rule px-2 py-0.5 text-xs text-ink"
                    >{{ card.caps_label }}</span>
                </div>

                </div>
            </div>
        </fieldset>

        <p class="note mt-2">
            The panel tags are the composition matrix, not a marketing list: a scenario that leaves a
            panel off cannot draw it, and a fifth scenario reaches the same shell for one config entry.
            The caps quoted are the published scenario caps from config/scenarios.php, verified
            {{ props.verified_at }}.
        </p>

        <!-- Reversibility stated before the commit (HFW annotation 3): the page says what a Trainer
             would otherwise learn by getting it wrong. -->
        <section
            data-region="card"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
            aria-labelledby="scenario-reversibility"
        >
            <h2 id="scenario-reversibility" class="text-lg font-semibold text-ink-strong">What changes after this step</h2>
            <table class="mt-2 w-full text-sm">
                <thead>
                    <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                        <th class="py-2 pr-3">Decision</th>
                        <th class="pr-3">Reversible?</th>
                        <th>Why</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Scenario</td>
                        <td class="pr-3"><span class="pill pill-caution">No</span></td>
                        <td class="text-ink-muted">Switching composes a different panel set, cap table and currency; the cockpit has no shape for a mid-run swap.</td>
                    </tr>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Trainee</td>
                        <td class="pr-3"><span class="pill pill-caution">No</span></td>
                        <td class="text-ink-muted">The run's identity, growth row and base stats are the trainee's.</td>
                    </tr>
                    <tr class="border-b border-rule align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Build target</td>
                        <td class="pr-3"><span class="pill pill-positive">Yes</span></td>
                        <td class="text-ink-muted">Targets are the Trainer's own goals, editable from the cockpit.</td>
                    </tr>
                    <tr class="align-top">
                        <td class="py-2 pr-3 font-semibold text-ink-strong">Legacy and deck</td>
                        <td class="pr-3"><span class="pill pill-positive">Yes</span></td>
                        <td class="text-ink-muted">Both are run-scoped selections with their own editors.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </SetupLayout>
</template>
