<script setup lang="ts">
/*
 * The Unity Cup team workspace, on a page of its own (`v3.0.0`).
 *
 * The cockpit used to render this as one permanently expanded seven-field form inside an `Edit team
 * state` disclosure, so the answer to "what is this team" was a column of inputs. That disclosure
 * retired; this page is the door that replaced it, and it is the **bento workspace** the release
 * delivered: a read-first overview, the attribute grade strip, the five-category formation board,
 * the recorded roster with its contextual editor, and the team form behind a disclosure rather than
 * in front of everything.
 *
 * **The read comes first, the write is one disclosure.** `TeamOverview`, `TeamGradeStrip` and
 * `TeamFormation` read; `TeamRoster` owns the roster's own CRUD; `UnityCupEditor` remains the single
 * writer for the seven scalar fields, D-10's rounds and the stat grades, mounted inside the
 * `Team` disclosure at the bottom. Nothing here re-implements a write those components own.
 *
 * **Only the payload the page is given is rendered.** `roster`, `observed_member_count` and
 * `team_race_countdown` arrive from `CockpitController` and `TeamPanelBuilder`; a key the server
 * has not shipped renders the component's own named absence rather than a fabricated figure. The
 * workspace never invents a backend field.
 *
 * It sits outside the stepper on purpose: `CareerSectionNav` has nine steps and none of them is a
 * team, so `current="team"` keeps the run's own tab lit while the page is open.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import TeamOverview from '../../components/career/TeamOverview.vue';
import TeamGradeStrip from '../../components/career/TeamGradeStrip.vue';
import TeamFormation from '../../components/career/TeamFormation.vue';
import TeamRoster from '../../components/career/TeamRoster.vue';
import UnityCupEditor from '../../components/career/UnityCupEditor.vue';
import { LinkButton } from '../../components/Form';
import { Head } from '@inertiajs/vue3';
import { useVisitState } from '../../composables/useVisitState';
import type { RosterPayload } from '../../components/career/RosterTableRows';

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
        /** The run's current league letter (ADR-0034); null is "not stated". */
        rank: string | null;
        facility_level: number | null;
        team_name: string | null;
        team_motto: string | null;
        team_league_placement: number | null;
        league_bonus: number | null;
        team_preseason_wins: number | null;
        unity_trainings_count: number | null;
        spirit_bursts_count: number | null;
        extreme_bursts_count: number | null;
        combined_bursts: number | null;
        burst_band: string | null;
        /** KI-133's labelled manual proxy; null until the Trainer enters one. */
        current_spirit_proxy: number | null;
        /** `D03`'s transcribed countdown; absent until the builder carries it. */
        team_race_countdown?: number | null;
        /** `UC3.3`: identities the client itself showed training beside the trainee. */
        observed_member_count: number | null;
        /** `D-10`'s per-round outcomes, one entry per stored row. */
        preseason_rounds?: { round: number; outcome: string | null }[];
        /** The scenario's published cadence, printed as the reason no turn is computed. */
        tournament?: { cadence_months: number | null; month_anchors: string[] } | null;
        /** The five per-stat grades the editor's selects bind to, in the config's stat order. */
        stat_grades: { key: string; label: string; grade: string | null }[];
        /**
         * The scenario's five race-category rows (`teamCoverageSection()`), or null when the matrix
         * does not compose `team_race`. The formation board reads its columns from here rather than
         * restating the client's category list.
         */
        coverage?: { rows: { category: string; asks: string }[] } | null;
    };
    /**
     * The recorded roster, `roster` + write URLs, or null when the page has not wired it in. A null
     * roster renders `TeamRoster`'s own "not wired" state rather than an empty table, so a page that
     * has not been given the payload cannot look like a team with no members.
     */
    roster?: RosterPayload | null;
    backUrl: string;
    scenarioDetailUrl: string;
}>();

const { visiting, visitFailed } = useVisitState();

const lede = `Recorded team state for ${props.run.scenario_label}. `
    + 'Every figure is what this run recorded; a blank stays blank rather than reading as a zero.';

/** The roster, or the reason there is none: a null payload and an empty roster are different facts. */
const rosterPayload = (): RosterPayload | null => props.roster ?? null;
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
        current="team"
    >
        <Head :title="`Team workspace: ${props.run.trainee}`" />

        <template #head>
            <PageHeader eyebrow="Team" title="Team workspace" :description="lede">
                <template #actions>
                    <LinkButton :href="props.backUrl" variant="secondary" size="sm">Back to Cockpit</LinkButton>
                    <LinkButton :href="props.scenarioDetailUrl" variant="secondary" size="sm">Scenario detail</LinkButton>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <!-- The bento: a two-column grid that reflows to one column on a narrow measure. The overview
             and the grade strip read the team's own state; the formation board reads the roster. -->
        <div class="grid grid-cols-1 gap-3.5 @3xl:grid-cols-2">
            <TeamOverview
                :team="props.team"
                :action="props.run.update_url"
                :umamusume-id="props.run.umamusume_id"
                :status="props.run.status"
            />

            <TeamGradeStrip
                :grades="props.team.stat_grades"
                :facility-level="props.team.facility_level"
                :rank="props.team.rank"
            />
        </div>

        <TeamFormation
            class="mt-3.5"
            :coverage="props.team.coverage ?? null"
            :members="rosterPayload()?.members ?? []"
        />

        <TeamRoster
            class="mt-3.5"
            :roster="rosterPayload()"
            :observed_member_count="props.team.observed_member_count"
        />

        <!-- The write, behind a disclosure rather than in front of the read. `UnityCupEditor` owns
             every field it posts, including D-10's four rounds and the stat grades. -->
        <details class="card mt-3.5">
            <summary class="flex min-h-11 cursor-pointer items-center text-base font-semibold text-ink-strong">
                Team state form
            </summary>
            <p class="sub">
                What this run recorded about its Unity Cup team. The form writes through the run's own
                update route, so an edit here is the same edit the Cockpit made. A blank field posts
                nothing rather than a zero.
            </p>
            <UnityCupEditor
                class="mt-3"
                :action="props.run.update_url"
                :umamusume-id="props.run.umamusume_id"
                :status="props.run.status"
                :team="props.team"
            />
        </details>
    </CareerLayout>
</template>
