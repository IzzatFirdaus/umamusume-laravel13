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
 * card holds the stored choice after the POST round trip. One "Select Scenario" action per card keeps
 * the primary action where the decision is (`design-2.0` §13, Fitts's Law) without a sixth control
 * that does the same thing.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
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
    description: string | null;
    recommended_use: string | null;
}

const props = defineProps<{
    scenarios: ScenarioCard[];
    selected: string | null;
    ruleset: { value: string | null; title: string };
    nextStepReady: boolean;
}>();

const choosing = ref<string | null>(null);

const form = useForm<{ scenario: string }>({ scenario: '' });

function choose(scenario: ScenarioCard): void {
    choosing.value = scenario.key;
    form.scenario = scenario.key;

    // The URL is literal, as every other page in this app states it: there is no Ziggy dependency
    // here, so `router.route()` would throw at click time rather than at build time.
    //
    // Focus is restored in `onSuccess`, after the new props land, because the component instance
    // survives a client-side visit: an `onMounted` hook would never re-run, and on a cold page load
    // it would steal focus from a Trainer who has not acted yet (WCAG 2.4.3 focus order, 3.2.6
    // consistent help). `nextTick` waits for Vue to repaint with the stored selection.
    form.put('/career/setup/scenario', {
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
</script>

<template>
    <SetupLayout :step="1">
        <Head title="Choose a scenario" />
        <template #title>Choose a scenario</template>

        <p class="max-w-2xl text-sm text-ink-muted">
            The scenario decides which resources this tool tracks, which systems it can show, and which
            stat ceilings apply. It is the first choice of six, and the wizard carries it forward instead
            of asking again.
        </p>

        <p class="mt-3 text-sm text-ink">
            Stored choice:
            <strong v-if="selectedLabel" class="text-ink-strong">{{ selectedLabel }}</strong>
            <span
                v-else
                class="text-ink-muted"
                title="No scenario has been chosen in this setup draft yet."
            >N/A</span>
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
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            No scenario is composed. `config/scenarios.php` holds none, so there is nothing to choose
            and no career can be started until one is added.
        </p>

        <ul v-else class="mt-6 grid list-none grid-cols-1 gap-4 xl:grid-cols-2">
            <li
                v-for="card in props.scenarios"
                :key="card.key"
                class="rounded-md border border-rule bg-panel p-5"
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

                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Optimizes</dt>
                        <dd v-if="optimizesText(card)" class="text-ink">{{ optimizesText(card) }}</dd>
                        <dd v-else class="text-ink-muted" title="This scenario publishes no stat cap bonus.">N/A</dd>
                    </div>
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Systems</dt>
                        <dd v-if="card.systems.length > 0" class="text-ink">{{ card.systems.join(', ') }}</dd>
                        <dd v-else class="text-ink-muted" title="Every panel in the matrix is off for this scenario.">
                            No scenario system
                        </dd>
                    </div>
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Tracked resources</dt>
                        <dd v-if="card.resources.length > 0" class="text-ink">{{ card.resources.join(', ') }}</dd>
                        <dd v-else class="text-ink-muted" title="It composes only the widgets every scenario carries.">
                            None beyond the generic strip
                        </dd>
                    </div>
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Turn loop</dt>
                        <dd v-if="card.loop.length > 0" class="text-ink">{{ card.loop.join(' → ') }}</dd>
                        <dd v-else class="text-ink-muted" title="No turn loop steps are recorded for this scenario.">N/A</dd>
                    </div>
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Live on Global</dt>
                        <dd v-if="card.live_on_global" class="font-mono text-ink">{{ card.live_on_global }}</dd>
                        <dd v-else class="text-ink-muted" title="No Global availability date is recorded for this scenario.">N/A</dd>
                    </div>
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Ruleset</dt>
                        <dd class="font-mono text-ink" :title="props.ruleset.title">
                            {{ props.ruleset.value ?? 'N/A' }}
                        </dd>
                    </div>
                    <!-- The brief asks for a short description and a recommended use. Neither the
                         corpus nor `config/scenarios.php` holds either, so they are named absences:
                         a sentence written here would be an invented fact (AGENTS.md §5). -->
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Description</dt>
                        <dd class="text-ink-muted" title="No sourced one-line description exists for any scenario in this repository.">
                            {{ card.description ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="flex flex-wrap justify-between gap-x-4">
                        <dt class="text-ink-muted">Recommended use</dt>
                        <dd class="text-ink-muted" title="Recommending a use would be a judgement this tool holds no source for.">
                            {{ card.recommended_use ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>

                <p class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        :id="`scenario-${card.key}`"
                        type="button"
                        class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:cursor-wait disabled:bg-disabled"
                        :aria-pressed="props.selected === card.key ? 'true' : 'false'"
                        :aria-describedby="form.errors.scenario ? 'scenario-error' : undefined"
                        :disabled="form.processing"
                        @click="choose(card)"
                    >
                        {{ choosing === card.key ? 'Saving…' : 'Select Scenario' }}
                    </button>
                    <!-- The stored state is a word, and it sits beside the action rather than inside
                         the card's heading: inside the `<h2>` it would have become part of the
                         heading's accessible name, so "Trackblazer" would read as
                         "Trackblazer SELECTED" and the card would stop matching its own name. -->
                    <span
                        v-if="props.selected === card.key"
                        class="text-xs font-bold text-ink-muted"
                    >Selected</span>
                </p>
            </li>
        </ul>

        <!-- Step 2 is the Trainee Selection slice (D3). Until it lands there is nowhere to continue
             to, and the screen says so instead of offering a dead link or a second submit button. -->
        <p
            v-if="!props.nextStepReady"
            class="mt-6 max-w-2xl text-sm text-ink-muted"
            title="The Trainee Selection step is its own slice (D3)."
        >
            Your choice is stored in this setup draft. Trainee selection is the next step and is not
            built yet, so there is nothing to continue to from here.
        </p>
    </SetupLayout>
</template>
