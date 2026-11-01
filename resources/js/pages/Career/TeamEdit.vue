<script setup lang="ts">
/*
 * The Unity Cup team write, on a page of its own (`v3.0.0`).
 *
 * The cockpit used to render this form inside an `Edit team state` disclosure at the bottom of the
 * page. That disclosure retires with this release, and this page is the door that replaced it: the
 * write is reachable before the block is deleted, not after, which is the difference between this and
 * `b7922db0`.
 *
 * **The form is not re-implemented here.** `UnityCupEditor` already owns the seven fields, the
 * null-not-zero rule (D-220: a blank input posts `null`, never `0`, because a zero burst count is a
 * real reading while an unstated one is not) and the `runs.update` payload. This page supplies the
 * shell and the two props the editor cannot read for itself, `action` and `status`.
 *
 * It sits outside the stepper on purpose: `CareerSectionNav` has nine steps and none of them is a
 * team, so `current="cockpit"` keeps the run's own tab lit while the page is open.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import UnityCupEditor from '../../components/career/UnityCupEditor.vue';
import { LinkButton } from '../../components/Form';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario: string | null;
        scenario_label: string;
        umamusume_id: number;
        run_url: string;
        update_url: string;
    };
    /** The team section the cockpit's panel reads, built by `TeamPanelBuilder::teamSection()`. */
    team: {
        team_name: string | null;
        team_motto: string | null;
        team_league_placement: number | null;
        team_preseason_wins: number | null;
        unity_trainings_count: number | null;
        spirit_bursts_count: number | null;
        extreme_bursts_count: number | null;
        stat_grades: { key: string; label: string; grade: string | null }[];
    };
    backUrl: string;
}>();

const lede = `Recorded team state for ${props.run.scenario_label}. `
    + 'Every field stays blank until you state it, and a blank field posts nothing rather than a zero.';
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
        current="cockpit"
    >
        <Head :title="`Team setup: ${props.run.trainee}`" />

        <template #head>
            <PageHeader eyebrow="Team" title="Edit team state" :description="lede">
                <template #actions>
                    <LinkButton :href="props.backUrl" variant="secondary" size="sm">Back to Cockpit</LinkButton>
                </template>
            </PageHeader>
        </template>

        <section aria-labelledby="team-edit-heading" class="card mt-4">
            <h2 id="team-edit-heading" class="text-lg font-semibold text-ink-strong">Team</h2>
            <p class="sub">
                What this run recorded about its Unity Cup team. The form writes through the run's own
                update route, so an edit here is the same edit the Cockpit made.
            </p>

            <UnityCupEditor
                class="mt-3"
                :action="props.run.update_url"
                :umamusume-id="props.run.umamusume_id"
                :status="props.run.status"
                :team="props.team"
            />
        </section>
    </CareerLayout>
</template>
