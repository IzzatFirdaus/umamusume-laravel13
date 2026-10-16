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
import AbsenceValue from '../../components/AbsenceValue.vue';
import SubCell from '../../components/SubCell.vue';
import { Head, useForm } from '@inertiajs/vue3';
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
    optimizes: Optimizes;
    systems: string[];
    resources: string[];
    loop: string[];
    live_on_global: string | null;
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
    selected: string | null;
    ruleset: { value: string | null; title: string };
    /** `route('career.scenario.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
}>();

const choosing = ref<string | null>(null);

const form = useForm<{ scenario: string | null }>({ scenario: null });

function choose(scenario: ScenarioCard): void {
    const deselect = props.selected === scenario.key;
    choosing.value = scenario.key;
    form.scenario = deselect ? null : scenario.key;

    // The write URL is supplied by the server (`career.scenario.store`) so the page never states
    // it as a literal. Focus is restored in `onSuccess`, after the new props land, because the
    // component instance survives a client-side visit: an `onMounted` hook would never re-run,
    // and on a cold page load it would steal focus from a Trainer who has not acted yet
    // (WCAG 2.4.3 focus order, 3.2.6 consistent help). `nextTick` waits for Vue to repaint with the
    // stored selection.
    form.put(props.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => {
                document.getElementById(`scenario-${scenario.key}`)?.focus();
            });
        },
        onFinish: () => {
            choosing.value = null;
        },
    });
}

const selectedLabel = computed(() =>
    props.scenarios.find((card) => card.key === props.selected)?.label ?? null,
);

const optimizesText = (card: ScenarioCard): string | null => {
    if (card.optimizes.kind === 'unknown' || card.optimizes.stats.length === 0) {
        return null;
    }

    const stats = card.optimizes.stats.join(', ');

    // A tie is said as a tie. Naming one "primary" among five equal bonuses would be a preference
    // this matrix does not express.
    return card.optimizes.kind === 'uniform'
        ? `No single stat: +${card.optimizes.bonus} on all five`
        : `${stats} (+${card.optimizes.bonus})`;
};

// The Rule family row's provenance, as its `title`. The word is this repository's own taxonomy for
// how the scenario advances its facility level, read from the entry's `facility_level_source`; the
// 2026-10-15 artifact's report resolves the field against that config fact and states that no
// publisher uses the term, so the citation is the repository's, not a publisher's.
const ruleFamilyTitle = 'How this scenario advances its training facility level, read from the entry\'s `facility_level_source` in config/scenarios.php; the repository\'s own taxonomy, which no publisher uses.';
</script>

<template>
    <SetupLayout :step="1" :visited="props.visited" :saved="props.saved">
        <Head title="Choose a scenario" />
        <template #eyebrow>Setup</template>
        <template #title>Choose a scenario</template>

        <!-- Back breadcrumb -->
        <nav class="mt-4 mb-2" aria-label="Breadcrumb">
            <Link href="/career/setup/scenario" class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-ink hover:underline transition-colors duration-150 ease-out" aria-label="Back to scenario step">
                ← Back to scenario
            </Link>
        </nav>

        <!-- Header: eyebrow + large title + subtitle -->
        <header class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-accent-ink">Step 1 of 6</p>
            <p class="mt-1 text-2xl font-semibold text-ink-strong">Choose a scenario</p>
            <p class="mt-1 text-sm text-ink-muted">The scenario decides which resources, systems and stat ceilings apply.</p>
        </header>

        <p class="max-w-3xl text-sm text-ink-muted">
            The scenario decides which resources this tool tracks, which systems it can show, and which
            stat ceilings apply. It is the first choice of six, and the wizard carries it forward instead
            of asking again.
        </p>

        <p class="mt-3 text-sm text-ink" data-field="stored-choice">
            Stored choice:
            <strong v-if="selectedLabel" class="text-ink-strong">{{ selectedLabel }}</strong>
            <AbsenceValue
                v-else
                compact
                reason="No scenario has been chosen in this setup draft yet."
            />
        </p>

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
            class="mt-8 rounded-lg border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            No scenario is composed. `config/scenarios.php` holds none, so there is nothing to choose
            and no career can be started until one is added.
        </p>

        <ul v-else class="mt-6 grid list-none grid-cols-1 gap-4 @4xl:grid-cols-2 @7xl:grid-cols-4">
            <li
                v-for="card in props.scenarios"
                :key="card.key"
                data-scenario-card
                class="rounded-lg border border-rule bg-panel p-5"
                :class="props.selected === card.key ? 'border-2 border-chrome' : ''"
            >
                <h2 class="flex flex-wrap items-baseline gap-x-3 text-lg font-semibold text-ink-strong">
                    {{ card.label }}
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
                </h2>

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

                <details class="mt-3 rounded-md border border-rule bg-raised p-3">
                    <summary class="flex min-h-11 cursor-pointer items-center text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        Facts and mechanics
                    </summary>

                    <div class="mt-3 space-y-2 text-sm" role="list" aria-label="Facts and mechanics">
                        <SubCell label="Optimizes" :value="optimizesText(card) ?? 'N/A'" :title="optimizesText(card) ? undefined : 'This scenario publishes no stat cap bonus.'" />
                        <SubCell label="Systems" :value="card.systems.length > 0 ? card.systems.join(', ') : 'N/A'" :title="card.systems.length > 0 ? undefined : 'Every panel in the matrix is off for this scenario.'" />
                        <SubCell label="Tracked resources" :value="card.resources.length > 0 ? card.resources.join(', ') : 'None beyond the generic strip'" :title="card.resources.length > 0 ? undefined : 'It composes only the widgets every scenario carries.'" />
                        <SubCell label="Turn loop" :value="card.loop.length > 0 ? card.loop.join(' → ') : 'N/A'" :title="card.loop.length > 0 ? undefined : 'No turn loop steps are recorded for this scenario.'" />
                        <SubCell label="Live on Global" :value="card.live_on_global ?? 'N/A'" :title="card.live_on_global ? undefined : 'No Global availability date is recorded for this scenario.'" />
                        <SubCell label="Scenario ruleset date" :value="card.ruleset_date?.value ?? 'N/A'" :title="card.ruleset_date?.title ?? 'No source in this repository dates this scenario\'s ruleset.'" />
                        <div class="flex flex-wrap justify-between gap-x-4">
                            <dt class="text-ink-muted">Ruleset version</dt>
                            <dd v-if="props.ruleset.value === null" class="text-ink-muted">
                                <AbsenceValue compact :reason="props.ruleset.title" />
                            </dd>
                            <dd v-else class="font-mono text-ink" :title="props.ruleset.title">
                                {{ props.ruleset.value }}
                            </dd>
                        </div>
                        <SubCell data-field="description" label="Description" :value="card.description ?? 'N/A'" :title="card.description ? undefined : `No sourced description line exists for the ${card.label} guide in this repository.`" />
                        <SubCell data-field="recommended_use" label="Recommended use" :value="card.recommended_use ?? 'N/A'" :title="card.recommended_use ? (card.metadata?.recommended_use_source ?? undefined) : (card.metadata?.recommended_use_reason ?? 'The scenario guides state no recommended use, and choosing one would be a judgement this tool holds no source for.')" />
                    </div>

                    <!-- Not "Ruleset version": the fact grid above carries three ruleset-worded facts,
                         each on its own row — the tool-wide ruleset *version*, which no source names;
                         the scenario's own ruleset *date*, its launch or rework; and this row, the
                         scenario's rule *family*, read from the matrix's `facility_level_source`. One
                         label for two facts read as a card contradicting itself, which is exactly what
                         the second audit reported (R2-05). -->
                    <div v-if="card.metadata !== null" class="mt-3 rounded-md border border-rule bg-panel p-3 text-sm">
                        <dl class="space-y-1">
                            <div data-field="rule-family" class="flex flex-wrap justify-between gap-x-4">
                                <dt class="text-ink-muted">Rule family</dt>
                                <dd class="text-ink" :title="card.rule_family !== null ? ruleFamilyTitle : undefined">{{ card.rule_family ?? 'Not recorded' }}</dd>
                            </div>
                            <div class="flex flex-wrap justify-between gap-x-4">
                                <dt class="text-ink-muted">Planning focus</dt>
                                <dd
                                    v-if="card.metadata.planning_focus !== null"
                                    class="text-ink"
                                    :title="card.metadata.planning_focus_source ?? undefined"
                                >{{ card.metadata.planning_focus }}</dd>
                                <dd v-else>
                                    <AbsenceValue compact :reason="card.metadata.planning_focus_reason ?? 'No planning focus is recorded for this scenario in this repository.'" />
                                </dd>
                            </div>
                        </dl>
                        <details v-if="card.metadata.key_mechanics.length > 0" class="mt-2">
                            <summary class="flex min-h-11 cursor-pointer items-center text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                Key mechanics ({{ card.metadata.key_mechanics.length }})
                            </summary>
                            <ul class="mt-1 list-disc pl-5 text-sm text-ink">
                                <li v-for="(line, index) in card.metadata.key_mechanics" :key="index">{{ line }}</li>
                            </ul>
                        </details>
                    </div>
                </details>

                <p class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        :id="`scenario-${card.key}`"
                        type="button"
                        :class="[
                            'inline-flex min-h-11 items-center rounded-md px-4 text-sm font-bold disabled:cursor-wait disabled:bg-disabled',
                            props.selected === card.key
                                ? 'bg-chrome text-on-chrome'
                                : 'border border-rule bg-raised text-ink hover:bg-sunken transition-colors duration-150 ease-out'
                        ]"
                        :aria-pressed="props.selected === card.key ? 'true' : 'false'"
                        :aria-describedby="form.errors.scenario ? 'scenario-error' : undefined"
                        :disabled="form.processing"
                        @click="choose(card)"
                    >
                        {{ props.selected === card.key ? 'Selected · clear' : 'Select Scenario' }}
                    </button>
                </p>
            </li>
        </ul>
    </SetupLayout>
</template>
