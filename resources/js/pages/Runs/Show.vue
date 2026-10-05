<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import ResourceStrip from '../../components/ResourceStrip.vue';
import MoodPill from '../../components/MoodPill.vue';
import StatBand from '../../components/StatBand.vue';
import GradePointMeter from '../../components/GradePointMeter.vue';
import RaceCalendar from '../../components/RaceCalendar.vue';
import DeckPanel from '../../components/DeckPanel.vue';
import RacePanel from '../../components/RacePanel.vue';
import ShopPanel from '../../components/ShopPanel.vue';
import TeamRankGauge from '../../components/TeamRankGauge.vue';
import SpiritBurstRoster from '../../components/SpiritBurstRoster.vue';
import TeamRacePanel from '../../components/TeamRacePanel.vue';
import EpithetChecklist from '../../components/EpithetChecklist.vue';
import RaceFatigueChip from '../../components/RaceFatigueChip.vue';
import GuidedStep from '../../components/GuidedStep.vue';
import AppButton from '../../components/AppButton.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Run {
    id: number;
    umamusume_id: number;
    umamusume_name: string;
    status: string;
    status_label: string;
    scenario: string | null;
    scenario_label: string | null;
    has_scenario: boolean;
    notes: string | null;
    imported_display: string | null;
    import_source: string | null;
    turn_count: number;
    export_csv_url: string;
    export_json_url: string;
    update_url: string;
    destroy_url: string;
}

interface Turn {
    id: number;
    turn: number;
    speed: number;
    stamina: number;
    power: number;
    guts: number;
    wit: number;
    sp: number | null;
    condition: string | null;
    energy: number | null;
    fans: number | null;
    mood: string | null;
    update_url: string;
    destroy_url: string;
    failure: { penalty_kind: string; source_name: string } | null;
}

interface Goal {
    title: string;
    state: string;
    year_label: string | null;
    turn: number | null;
}

interface SkillRow {
    id: number;
    name: string;
    sp_cost: number | null;
    is_unique: boolean;
    turn_acquired: number | null;
}

interface SkillGroup {
    key: string;
    label: string;
    absent: string;
    skills: SkillRow[];
}

const props = defineProps<{
    run: Run;
    turns: Turn[];
    goals: Goal[];
    skillGroups: SkillGroup[];
    skillCatalog: { id: number; name: string; sp_cost: number | null }[];
    acquisitionOptions: { value: string; label: string }[];
    moodOptions: { value: string; arrow: string }[];
    scenarios: Record<string, string>;
    statuses: Record<string, string>;
    caps: Record<string, number>;
    statOrder: string[];
    baseCap: number;
    hardCap: number;
    gradeBanding: { step: number; labels: string[] };
    maxObjectiveIndex: number;
    band: { scenario: string | null; capBonus: Record<string, number> | null; values: Record<string, number>; skillPoints: number | null } | null;
    strip: Record<string, unknown>;
    currentMood: string | null;
    gradeMeter: Record<string, unknown>;
    teamRank: Record<string, unknown>;
    spiritBursts: Record<string, unknown>;
    teamRace: Record<string, unknown>;
    epithets: Record<string, unknown>;
    fatigue: Record<string, unknown>;
    calendar: Record<string, unknown>;
    deck: Record<string, unknown>;
    racePanel: Record<string, unknown>;
    shop: Record<string, unknown>;
    rail: Record<string, unknown>;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as { status?: string | null } | undefined)?.status ?? null);
const errors = computed(() => (page.props.errors as Record<string, string | undefined> | undefined) ?? {});

const statWords = ['speed', 'stamina', 'power', 'guts', 'wit'];

/*
 * Three PUTs to one route, each carrying the fields it is not editing. `runs.update` shares
 * StoreTrainingRunRequest with create and writes every field it is handed, so a form that omitted one
 * would blank that column.
 */
const scenarioForm = useForm({ umamusume_id: props.run.umamusume_id, status: props.run.status, scenario: props.run.scenario ?? '' });
const statusForm = useForm({ umamusume_id: props.run.umamusume_id, scenario: props.run.scenario ?? '', status: props.run.status });
const periodForm = useForm({
    umamusume_id: props.run.umamusume_id,
    status: props.run.status,
    scenario: props.run.scenario ?? '',
    current_objective_index: String(props.gradeMeter.current ?? ''),
});

const editingTurn = ref<number | null>(null);
const editForm = useForm({
    turn: '', speed: '', stamina: '', power: '', guts: '', wit: '',
    sp: '', condition: '', energy: '', fans: '', mood: '',
});

function openTurn(turn: Turn): void {
    editingTurn.value = turn.id;
    editForm.defaults({
        turn: String(turn.turn),
        speed: String(turn.speed),
        stamina: String(turn.stamina),
        power: String(turn.power),
        guts: String(turn.guts),
        wit: String(turn.wit),
        sp: turn.sp === null ? '' : String(turn.sp),
        condition: turn.condition ?? '',
        energy: turn.energy === null ? '' : String(turn.energy),
        fans: turn.fans === null ? '' : String(turn.fans),
        mood: turn.mood ?? '',
    });
    editForm.reset();
}

function saveTurn(turn: Turn): void {
    editForm.put(turn.update_url, { preserveScroll: true });
}

// The catalogue is one list, not ten: each row used to carry every skill, measured at 6,270 option
// nodes. One row is open — the one the query names, else the one an error landed on, else the first —
// and the closed rows post a hidden value behind a link that opens them.
const openSkillRow = ref<number>(
    Number(new URLSearchParams(window.location.search).get('skill_row') ?? 0) || 0,
);
const skillsForm = useForm({
    skills: (props.skillGroups.flatMap((group) => group.skills).length + 1) > 0
        ? props.skillGroups.flatMap((group) => group.skills)
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((skill) => ({
                skill_id: String(skill.id),
                status: 'Acquired',
                turn_acquired: skill.turn_acquired === null ? '' : String(skill.turn_acquired),
            }))
            .concat([{ skill_id: '', status: 'Acquired', turn_acquired: '' }])
        : [{ skill_id: '', status: 'Acquired', turn_acquired: '' }],
});

const hatchForm = useForm({
    turn: String(props.run.turn_count + 1),
    speed: '', stamina: '', power: '', guts: '', wit: '', sp: '', condition: '',
});

const skillName = (rowIndex: number): string =>
    props.skillCatalog.find((skill) => String(skill.id) === skillsForm.skills[rowIndex]?.skill_id)?.name ?? 'Not chosen';

const railStep = computed(() => props.rail as unknown as {
    def: { label: string; steps: string[]; panels: Record<string, boolean> } & Record<string, unknown>;
    declared: boolean;
    current: string;
    previewed: boolean;
    choices: unknown[];
    selected: string | null;
    values: Record<string, unknown>;
    preview: { direction: string; text: string }[];
    energy: number | null;
    mood: string | null;
    turn: number;
    has_previous: boolean;
    previous: Record<string, number | null> | null;
    action: string;
});
</script>

<template>
    <AppLayout>
        <Head :title="`Run: ${run.umamusume_name}`" />
        <template #title>{{ run.umamusume_name }}</template>

        <p
            v-if="flash"
            role="status"
            class="mb-4 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-strong"
        >
            {{ flash }}
        </p>

        <div class="flex flex-wrap items-baseline justify-between gap-3">
            <h1 class="text-2xl font-semibold text-ink-strong">
                {{ run.umamusume_name }}
                <span class="ml-2 text-sm font-normal text-ink-muted">
                    {{ run.status_label }} ·
                    {{ run.scenario_label ?? 'No scenario set' }}
                </span>
            </h1>
            <div class="flex gap-3 text-sm">
                <a :href="run.export_csv_url" class="inline-flex min-h-11 items-center hover:underline">Export CSV</a>
                <a :href="run.export_json_url" class="inline-flex min-h-11 items-center hover:underline">Export JSON</a>
            </div>
        </div>

        <p v-if="run.imported_display" class="mt-1 text-xs text-ink-muted">
            Imported {{ run.imported_display }} from {{ run.import_source || 'a CSV' }}
        </p>

        <p v-if="run.notes" class="mt-2 text-sm text-ink-muted">{{ run.notes }}</p>

        <!-- The pinned region is the Resources strip alone (O-2). `lg:contents` removes this wrapper's
             box at the breakpoint so the sticky strip's containing block is the page, which is what lets
             it stay pinned over the log; below `lg` it is an ordinary block and nothing is sticky. -->
        <section aria-label="Run state" class="bg-page lg:contents">
            <section aria-label="Resources" class="bg-page lg:sticky lg:top-0 lg:z-10 lg:py-2">
                <h2 class="text-lg font-semibold text-ink-strong">Resources</h2>
                <ResourceStrip
                    class="mt-3"
                    :widgets="strip.widgets as string[]"
                    :scenario-label="strip.scenarioLabel as string"
                    :has-grade-objectives="strip.hasGradeObjectives as boolean"
                    :declared="strip.declared as boolean"
                    :values="strip.values as Record<string, number | string | null>"
                />

                <!-- Mood is the trainee's state, not a turn's detail. A turn that stored no tier says
                     "not recorded" rather than defaulting to NORMAL (D-220). -->
                <div v-if="band !== null" class="mt-2 flex flex-wrap items-baseline gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Mood</span>
                    <MoodPill :tier="currentMood" unrecorded="not recorded" />
                </div>
            </section>

            <!-- No logged turn means no band: five zeroes would be a claim about a trainee nobody
                 entered (D-220). -->
            <section v-if="band !== null" aria-label="Stats">
                <h2 class="mt-6 text-lg font-semibold text-ink-strong">Stats</h2>
                <StatBand
                    class="mt-3"
                    :caps="caps"
                    :values="band.values"
                    :skill-points="band.skillPoints"
                    :cap-bonus="band.capBonus"
                    :base-cap="baseCap"
                    :hard-cap="hardCap"
                    :grade-banding="gradeBanding"
                />
            </section>
        </section>

        <!-- O-12: the goal line the client prints, on every scenario and on a run with no scenario at
             all. Races the catalogue has not offered yet stay out rather than showing an empty state. -->
        <section v-if="goals.length > 0" aria-label="Goals" class="mt-2">
            <h2 class="text-lg font-semibold text-ink-strong">Goals</h2>
            <ol class="mt-3 flex flex-col gap-1.5" aria-label="Race goals">
                <li
                    v-for="goal in goals"
                    :key="`${goal.title}-${goal.turn}`"
                    class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-panel px-3 py-2 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ goal.title }}</span>
                        <span v-if="goal.year_label !== null || goal.turn !== null" class="ml-1.5 text-xs text-ink-muted">
                            <template v-if="goal.year_label !== null">{{ goal.year_label }}</template>
                            <template v-if="goal.year_label !== null && goal.turn !== null"> · </template>
                            <template v-if="goal.turn !== null">Turn {{ goal.turn }}</template>
                        </span>
                    </span>
                    <span
                        class="shrink-0 text-xs font-bold"
                        :class="goal.state === 'Cleared' ? 'text-green-deep' : (goal.state === 'Failed' ? 'text-risk' : 'text-ink')"
                    >
                        {{ goal.state }}
                    </span>
                </li>
            </ol>
        </section>

        <!-- Each panel self-gates on the composition matrix, so a scenario that does not open a mechanic
             renders nothing rather than an empty frame (D-221, D-241, G-34). Mounting them all is what
             keeps the set scenario-composed without a single scenario-name branch in this template. -->
        <template v-if="run.has_scenario">
            <section v-if="(calendar as { show: boolean }).show" aria-label="Race calendar" class="mt-2">
                <RaceCalendar
                    class="mt-3"
                    :cells="(calendar as { cells: unknown[] }).cells as never"
                    :year="(calendar as { year: number }).year"
                    :year-tabs="(calendar as { yearTabs: unknown[] }).yearTabs as never"
                    :year-word="(calendar as { yearWord: string | null }).yearWord"
                    :next-turn="(calendar as { nextTurn: number | null }).nextTurn"
                />
            </section>

            <GradePointMeter class="mt-3" v-bind="gradeMeter as never" />
            <ShopPanel class="mt-3" v-bind="shop as never" />
            <RacePanel class="mt-3" v-bind="racePanel as never" />
            <TeamRankGauge class="mt-3" v-bind="teamRank as never" />
            <SpiritBurstRoster class="mt-3" v-bind="spiritBursts as never" />
            <TeamRacePanel class="mt-3" v-bind="teamRace as never" />
            <EpithetChecklist class="mt-3" v-bind="epithets as never" />
            <RaceFatigueChip class="mt-3" v-bind="fatigue as never" />
        </template>

        <section aria-label="Turn log" class="mt-2">
            <form class="mt-4 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-4 text-sm" @submit.prevent="scenarioForm.put(run.update_url, { preserveScroll: true })">
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Scenario</span>
                    <select v-model="scenarioForm.scenario" name="scenario" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                        <option value="">Not set (baseline strip)</option>
                        <option v-for="(label, key) in scenarios" :key="key" :value="key">{{ label }}</option>
                    </select>
                </label>
                <button type="submit" class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong">
                    Change scenario
                </button>
                <p v-if="errors.scenario" class="w-full text-risk">{{ errors.scenario }}</p>
            </form>

            <!-- §7-4: a run could be created in any of three states and never moved afterwards, so
                 retiring a finished run meant editing the database by hand. -->
            <form class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-4 text-sm" @submit.prevent="statusForm.put(run.update_url, { preserveScroll: true })">
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Status</span>
                    <select v-model="statusForm.status" name="status" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                        <option v-for="(label, value) in statuses" :key="value" :value="value">{{ value }}</option>
                    </select>
                </label>
                <AppButton type="submit" variant="secondary">Change status</AppButton>
                <p v-if="errors.status" class="w-full text-risk">{{ errors.status }}</p>
            </form>

            <!-- Its own form because its own verb: this one says which deadline the Trainer is working
                 to. The period is entered, never inferred (D-270), and "not reported" is a real option
                 rather than a placeholder. -->
            <form
                v-if="run.has_scenario && (gradeMeter.objectives as unknown[]).length > 0"
                class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-4 text-sm"
                @submit.prevent="periodForm.put(run.update_url, { preserveScroll: true })"
            >
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-ink">Grade Point period</span>
                    <select v-model="periodForm.current_objective_index" name="current_objective_index" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                        <option value="">Not reported</option>
                        <option
                            v-for="objective in (gradeMeter.objectives as { index: number; name: string }[])"
                            :key="objective.index"
                            :value="String(objective.index)"
                        >
                            {{ objective.index }}. {{ objective.name }}
                        </option>
                    </select>
                </label>
                <button type="submit" class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-bold text-ink-strong">
                    Report period
                </button>
                <p v-if="errors.current_objective_index" class="w-full text-risk">{{ errors.current_objective_index }}</p>
            </form>

            <!-- The deck sits outside the hasScenario cluster: every scenario has six slots, so a run
                 that names no scenario still had a deck. -->
            <h2 class="mt-8 text-lg font-semibold text-ink-strong">Support deck</h2>
            <DeckPanel class="mt-3" v-bind="deck as never" />

            <h2 class="mt-8 text-lg font-semibold text-ink-strong">Turns</h2>

            <p v-if="turns.length === 0" class="mt-2 text-sm text-ink-muted">
                No turns logged yet. Add the first one below.
            </p>
            <div v-else class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="Turn log">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-rule text-left text-ink-muted">
                            <th class="py-1 pr-3">Turn</th>
                            <th class="pr-3">Speed</th>
                            <th class="pr-3">Stamina</th>
                            <th class="pr-3">Power</th>
                            <th class="pr-3">Guts</th>
                            <th class="pr-3">Wit</th>
                            <th class="pr-3">SP</th>
                            <th class="pr-3">Condition</th>
                            <th class="pr-3">Mood</th>
                            <th class="pr-3">Turn actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="turn in turns" :key="turn.id">
                            <tr class="border-b border-rule">
                                <td class="py-1 pr-3">
                                    {{ turn.turn }}
                                    <template v-if="turn.failure">
                                        <span class="ml-1.5 rounded border border-risk px-1 text-xs font-bold text-risk">Failed</span>
                                        <span class="block text-xs text-ink-muted">
                                            Penalty kind: {{ turn.failure.penalty_kind }} · {{ turn.failure.source_name }}
                                        </span>
                                    </template>
                                </td>
                                <td class="pr-3">{{ turn.speed }}</td>
                                <td class="pr-3">{{ turn.stamina }}</td>
                                <td class="pr-3">{{ turn.power }}</td>
                                <td class="pr-3">{{ turn.guts }}</td>
                                <td class="pr-3">{{ turn.wit }}</td>
                                <td class="pr-3">{{ turn.sp ?? '' }}</td>
                                <td class="pr-3">{{ turn.condition }}</td>
                                <td class="pr-3"><MoodPill :tier="turn.mood" unrecorded="not recorded" /></td>
                                <td class="pr-3 align-top">
                                    <button
                                        v-if="editingTurn === turn.id"
                                        type="button"
                                        class="inline-flex min-h-11 items-center underline"
                                        @click="editingTurn = null"
                                    >
                                        Close turn {{ turn.turn }}
                                    </button>
                                    <!-- §7-2: the route existed, the control did not. The two destructive
                                         submissions below are reachable only on the row just asked for. -->
                                    <button
                                        v-else
                                        type="button"
                                        class="inline-flex min-h-11 items-center underline"
                                        @click="openTurn(turn)"
                                    >
                                        Edit turn {{ turn.turn }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="editingTurn === turn.id">
                                <td colspan="10" class="px-1 py-2">
                                    <form
                                        class="grid grid-cols-2 gap-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4"
                                        @submit.prevent="saveTurn(turn)"
                                    >
                                        <label class="flex flex-col gap-1">
                                            <span>Turn *</span>
                                            <input v-model="editForm.turn" type="number" name="turn" min="1" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                        </label>
                                        <label v-for="stat in statWords" :key="stat" class="flex flex-col gap-1">
                                            <span>{{ stat.charAt(0).toUpperCase() + stat.slice(1) }} *</span>
                                            <input
                                                v-model="editForm[stat]"
                                                type="number"
                                                :name="stat"
                                                min="0"
                                                :max="caps[stat.charAt(0).toUpperCase() + stat.slice(1)]"
                                                required
                                                class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                                            >
                                        </label>
                                        <label class="flex flex-col gap-1">
                                            <span>SP</span>
                                            <input v-model="editForm.sp" type="number" name="sp" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                        </label>
                                        <label class="flex flex-col gap-1">
                                            <span>Condition</span>
                                            <input v-model="editForm.condition" type="text" name="condition" maxlength="255" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                        </label>
                                        <label class="flex flex-col gap-1">
                                            <span>Energy</span>
                                            <input v-model="editForm.energy" type="number" name="energy" min="0" max="100" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                        </label>
                                        <label class="flex flex-col gap-1">
                                            <span>Fans</span>
                                            <input v-model="editForm.fans" type="number" name="fans" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                        </label>
                                        <label class="flex flex-col gap-1">
                                            <span>Mood</span>
                                            <select v-model="editForm.mood" name="mood" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                                                <option value="">not recorded</option>
                                                <option v-for="tier in moodOptions" :key="tier.value" :value="tier.value">{{ tier.value }}</option>
                                            </select>
                                        </label>
                                        <AppButton type="submit" variant="primary" class="self-end">Save turn</AppButton>
                                    </form>
                                    <div class="mt-2 flex max-w-3xl flex-wrap items-center gap-3 text-sm">
                                        <span class="text-ink-muted">Removes turn {{ turn.turn }} and its readings. The row above it stays.</span>
                                        <button type="button" class="min-h-11 rounded-full border-2 border-risk px-3 font-semibold text-risk" @click="editForm.delete(turn.destroy_url, { preserveScroll: true })">
                                            Delete turn {{ turn.turn }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- The rail is the door: the numbers stay on this screen because they are what the tool
                 records, and the client's projected gains are not. The previous turn's values are
                 placeholders, never pre-filled: an input arriving with a number already asserts the
                 stat did not change (D-220). -->
            <GuidedStep
                class="mt-6"
                :def="railStep.def"
                :declared="railStep.declared"
                :current="railStep.current"
                :selected="railStep.selected"
                :choices="railStep.choices as never"
                :preview="railStep.preview"
                :previewed="railStep.previewed"
                :energy="railStep.energy"
                :action="railStep.action"
            >
                <div class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Turn</span>
                        <input
                            :value="railStep.values.turn ?? railStep.turn"
                            type="number" name="turn" min="1" required
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                    </label>
                    <label v-for="stat in statWords" :key="stat" class="flex flex-col gap-1">
                        <span class="font-medium text-ink">{{ stat.charAt(0).toUpperCase() + stat.slice(1) }}</span>
                        <input
                            :value="railStep.values[stat] ?? ''"
                            type="number" :name="stat" min="0"
                            :max="caps[stat.charAt(0).toUpperCase() + stat.slice(1)]" required
                            :placeholder="railStep.previous?.[stat] ?? 'no logged turn'"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Skill Points</span>
                        <input :value="railStep.values.sp ?? ''" type="number" name="sp" min="0" :placeholder="railStep.previous?.sp ?? 'no logged turn'" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <!-- O-3: the field takes the post-turn reading the client shows, not a delta. The
                         server subtracts the previous stored total to print the change, so naming the
                         total on the label is what removes the ambiguity. -->
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Energy (after this turn)</span>
                        <input :value="railStep.values.energy ?? ''" type="number" name="energy" min="0" max="100" :placeholder="railStep.previous?.energy ?? 'no logged turn'" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Fans (after this turn)</span>
                        <input :value="railStep.values.fans ?? ''" type="number" name="fans" min="0" :placeholder="railStep.previous?.fans ?? 'no logged turn'" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Mood</span>
                        <select
                            name="mood"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                        >
                            <option value="">not yet recorded</option>
                            <option
                                v-for="tier in moodOptions"
                                :key="tier.value"
                                :value="tier.value"
                                :selected="railStep.mood === tier.value"
                            >
                                {{ tier.value }} {{ tier.arrow }}
                            </option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Outcome</span>
                        <select name="outcome" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option v-for="outcome in ['Success', 'Failure']" :key="outcome" :value="outcome" :selected="railStep.values.outcome === outcome">{{ outcome }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-ink">Penalty kind</span>
                        <!-- Only required when the outcome is a failure, which the server checks. Not
                             hidden behind a script: nothing here runs one, and a field always visible
                             cannot be missed. -->
                        <select name="penalty_kind" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                            <option value="">none, or not a failure</option>
                            <option value="energy" :selected="railStep.values.penalty_kind === 'energy'">Energy</option>
                            <option value="mood" :selected="railStep.values.penalty_kind === 'mood'">Mood</option>
                            <option value="stat" :selected="railStep.values.penalty_kind === 'stat'">A stat</option>
                        </select>
                    </label>
                </div>

                <p v-if="railStep.preview.length === 0 && !railStep.has_previous" class="mt-3 text-xs text-ink-muted">
                    This is the run's first turn, so there is no turn to compare against and the preview
                    will have nothing to subtract. The numbers above are what gets stored.
                </p>
            </GuidedStep>

            <!-- One envelope for both kinds of error: the `previewed` request-stage marker renders as a
                 list item like any per-field error, because one treatment is easier to read than two. -->
            <ul v-if="Object.keys(errors).length > 0" class="mt-2 max-w-3xl list-disc pl-6 text-sm text-risk">
                <li v-for="(message, key) in errors" :key="key">{{ message }}</li>
            </ul>

            <!-- D-53: the raw entry field exists, is reachable, and is not the default. It writes on
                 submit and shows no preview, and the summary says so. -->
            <details class="mt-6 max-w-3xl">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-muted hover:text-ink">
                    Correct a turn by hand
                </summary>
                <p class="mt-2 text-sm text-ink-muted">
                    Every field at once, for fixing a mistyped turn or importing values. It writes on
                    submit and shows no preview, so use the card above for a turn you are logging as it
                    happens.
                </p>
                <form
                    class="mt-3 grid grid-cols-2 gap-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4"
                    @submit.prevent="hatchForm.post(railStep.action, { preserveScroll: true })"
                >
                    <label class="flex flex-col gap-1"><span>Turn *</span><input v-model="hatchForm.turn" type="number" name="turn" min="1" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"></label>
                    <label v-for="stat in statWords" :key="stat" class="flex flex-col gap-1">
                        <span>{{ stat.charAt(0).toUpperCase() + stat.slice(1) }} *</span>
                        <input v-model="hatchForm[stat]" type="number" :name="stat" min="0" :max="caps[stat.charAt(0).toUpperCase() + stat.slice(1)]" required class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                    </label>
                    <label class="flex flex-col gap-1"><span>SP</span><input v-model="hatchForm.sp" type="number" name="sp" min="0" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"></label>
                    <label class="flex flex-col gap-1"><span>Condition</span><input v-model="hatchForm.condition" type="text" name="condition" maxlength="255" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"></label>
                    <button type="submit" class="enamel inline-flex min-h-11 items-center self-end rounded-full bg-chrome px-3 font-semibold text-on-chrome">Save correction</button>
                </form>
            </details>

            <section aria-label="Skills">
                <h2 class="mt-10 text-lg font-semibold text-ink-strong">Skills</h2>

                <template v-for="group in skillGroups" :key="group.key">
                    <h3 class="mt-3 text-sm font-semibold text-ink-muted">{{ group.label }}</h3>
                    <p v-if="group.skills.length === 0" class="text-sm text-ink-muted">{{ group.absent }}</p>
                    <ul v-else class="text-sm">
                        <li v-for="skill in group.skills" :key="skill.id">
                            {{ skill.name }}
                            <!-- The sparkle is the client's own mark for a unique skill and means nothing
                                 else here; the word rides beside it because D-12 bars a state that lives
                                 only in a glyph. -->
                            <span v-if="skill.is_unique" class="text-ink-muted">✦ Unique</span>
                            <span v-if="skill.sp_cost !== null" class="text-ink-muted">· {{ skill.sp_cost }} SP</span>
                            <span v-if="skill.turn_acquired" class="text-ink-muted">· turn {{ skill.turn_acquired }}</span>
                        </li>
                    </ul>
                </template>

                <p class="mt-2 max-w-3xl text-xs text-ink-muted">
                    SP is the cost the source states for that skill. Hint-level discounts follow the ladder recorded in
                    docs/research-scratch/SKILLS-MECHANICS.md §2.4: 10, 20, 30, 35 and 40 percent at Lv1 through Lv Max.
                    <a href="/skills" class="inline-flex min-h-11 items-center underline">Search the skill catalog</a>
                    to narrow that list before choosing here.
                </p>

                <form class="mt-4 max-w-3xl space-y-3 rounded-md border border-rule bg-raised p-4 text-sm" @submit.prevent="skillsForm.post(`/training-runs/${run.id}/skills`, { preserveScroll: true })">
                    <p v-if="skillCatalog.length === 0" class="text-xs text-ink-muted">
                        No skills are available to choose yet. Run `php artisan uma:fetch gametora-skills` to fill
                        the catalogue, or `php artisan uma:reparse gametora-skills` if a fetch says the document is
                        unchanged.
                    </p>

                    <!-- KI-36: each control gets its own `<label for>`. The wrapper label used to name the
                         group, which left the status select and the turn input with no accessible name
                         at all, and a placeholder is not a name: it disappears exactly when the field
                         has a value, which is every row on a pre-populated run. -->
                    <div v-for="(row, rowIndex) in skillsForm.skills" :key="rowIndex" class="flex flex-wrap items-end gap-3">
                        <div class="flex flex-col gap-1">
                            <template v-if="rowIndex === openSkillRow">
                                <label :for="`skill-${rowIndex}-id`" class="text-ink-muted">Skill</label>
                                <select :id="`skill-${rowIndex}-id`" v-model="row.skill_id" :name="`skills[${rowIndex}][skill_id]`" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                                    <option value="">Choose a skill</option>
                                    <option v-for="skill in skillCatalog" :key="skill.id" :value="String(skill.id)">
                                        {{ skill.name }}<template v-if="skill.sp_cost !== null"> · {{ skill.sp_cost }} SP </template>
                                    </option>
                                </select>
                            </template>
                            <template v-else>
                                <span class="text-ink-muted">Skill</span>
                                <p class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                    <span class="text-ink">{{ skillName(rowIndex) }}</span>
                                    <button type="button" class="inline-flex min-h-11 items-center underline" @click="openSkillRow = rowIndex">
                                        Change row {{ rowIndex }}
                                    </button>
                                </p>
                                <input type="hidden" :name="`skills[${rowIndex}][skill_id]`" :value="row.skill_id">
                            </template>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label :for="`skill-${rowIndex}-status`" class="text-ink-muted">Acquisition status</label>
                            <select :id="`skill-${rowIndex}-status`" v-model="row.status" :name="`skills[${rowIndex}][status]`" class="h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                                <option v-for="option in acquisitionOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label :for="`skill-${rowIndex}-turn`" class="text-ink-muted">Turn acquired</label>
                            <input :id="`skill-${rowIndex}-turn`" v-model="row.turn_acquired" type="number" :name="`skills[${rowIndex}][turn_acquired]`" min="1" step="1" placeholder="Turn" class="h-11 w-20 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                        </div>
                    </div>

                    <p v-if="Object.keys(skillsForm.errors).length > 0" class="text-risk">
                        <span v-for="(message, key) in skillsForm.errors" :key="key" class="block">{{ message }}</span>
                    </p>

                    <button type="submit" class="enamel h-11 rounded-full bg-chrome px-3 font-semibold text-on-chrome">Save skill status</button>
                </form>
            </section>
        </section>

        <!-- Confirmation is a disclosure, not a script (ADR-0007). The summary is the prompt; the form
             inside submits the DELETE only after the Trainer opens it. It sits outside the turn-log
             section so the scrolling region keeps its one disclosure. -->
        <details class="mt-10">
            <summary class="flex min-h-11 cursor-pointer items-center rounded-full border-2 border-risk px-3 text-sm font-semibold text-risk">
                Delete run
            </summary>
            <p class="mt-2 text-sm text-ink-muted">
                This removes the run and every turn logged for it. The action is permanent.
            </p>
            <form class="mt-2" @submit.prevent="editForm.delete(run.destroy_url, { preserveScroll: true })">
                <button type="submit" class="min-h-11 rounded-full border-2 border-risk px-3 font-semibold text-risk">Delete this run</button>
            </form>
        </details>
    </AppLayout>
</template>
