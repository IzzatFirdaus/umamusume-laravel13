<script setup lang="ts">
/*
 * The scenario's own board suite, on a page of its own (`v3.0.0`).
 *
 * The cockpit used to render this inside a `Scenario detail` disclosure at the foot of the Scenario
 * card. That disclosure retires, and the door of the same name in "Where to go" leads here instead, so
 * the boards stay reachable: the Unity Cup, Trackblazer, URA and Grand Concert panels, the team-race
 * facility panel, and `CareerGoalsPanel`, the ADR-0026 trainee-goals surface whose only host this suite
 * is. Deleting the suite with the disclosure would have made all of them unreachable.
 *
 * **The panel is rendered bare, not inside a `.card`.** `ScenarioPanel` draws its own cards, and a card
 * inside a card is what `npm run lint:ui` refuses; the disclosure this replaces rendered it bare for the
 * same reason.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import ScenarioPanel from '../../components/scenario/ScenarioPanel.vue';
import { LinkButton } from '../../components/Form';
import { Head } from '@inertiajs/vue3';
import { useVisitState } from '../../composables/useVisitState';

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario: string | null;
        scenario_label: string;
        run_url: string;
    };
    /** The scenario section `ScenarioSectionBuilder::scenarioSection()` composes for this run. */
    scenario: Record<string, unknown>;
    backUrl: string;
}>();

const { visiting, visitFailed } = useVisitState();

const lede = `The boards ${props.run.scenario_label} composes for this run. `
    + 'Every panel reads what the run recorded and names what it has not.';
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="null"
        :run-url="props.run.run_url"
        current="scenario"
    >
        <Head :title="`Scenario detail: ${props.run.trainee}`" />

        <template #head>
            <PageHeader eyebrow="Scenario" title="Scenario detail" :description="lede">
                <template #actions>
                    <LinkButton :href="props.backUrl" variant="secondary" size="sm">Back to Cockpit</LinkButton>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <!-- The region name is load-bearing, not decoration: the panel specs anchor on `region
             "Scenario"`, which the cockpit's Scenario card used to carry. Keeping the name here means
             every one of those cases follows the panels to this page unchanged. -->
        <section aria-label="Scenario" class="mt-4">
            <!-- `as never` is lifted verbatim from the disclosure this page replaces (`Cockpit.vue` at
                 `7be35b69`): `ScenarioPanel`'s `defineProps` cannot take an imported type (plan §11,
                 TS7), which the panel's own header records at `ScenarioPanel.vue:34-35`. Same cast,
                 same reason, no new debt; widening the panel's prop type is the follow-up. -->
            <ScenarioPanel :scenario="props.scenario as never" />
        </section>
    </CareerLayout>
</template>
