<script setup lang="ts">
/*
 * The Skills Planner (plan §8 D13, design-2.0 only). What the run's skills look like against the
 * build target: the four states, the Skill Point coverage of the skills still to learn, and each
 * skill's own recorded conditions set against the target.
 *
 * **Nothing on this screen recommends, ranks or scores a skill.** The one ordering is the Trainer's
 * own priority list, and the race-fit cells state agreement, never desirability (PRD OQ-5 stays
 * open, `ADR-0020` §2). The one write reorders that list, and it posts to the run-scoped build
 * target write whose Form Request already owns `skill_priorities`.
 *
 * The page pattern is `Career/RaceDecision.vue`'s: `CareerLayout`, page-level loading and error
 * roles, and every figure composed server-side. The hint-discount ladder (UMAMUSUME_REFERENCE.md
 * §1.1.4) renders from a server prop, but the base prices above it stay base: a discount applies
 * only when a hint level sits on the skill, and no column records one, so no row is re-priced.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import SkillPlanRow from '../../components/career/SkillPlanRow.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import AcquisitionStatusEditor from '../../components/career/AcquisitionStatusEditor.vue';
import { BaseButton, BaseSelect } from '../../components/Form';
import SearchableSkillPicker from '../../components/Form/SearchableSkillPicker.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface FitCell {
    key: string;
    label: string;
    state: 'matches' | 'mismatch' | 'unrecorded';
    title: string | null;
}

interface PlanRow {
    id: number | null;
    name: string;
    name_ja: string | null;
    is_unique: boolean;
    sp_cost: number | null;
    recorded: { status: string; turn: number | null; current_level: number | null; hint_level: number | null } | null;
    conditions: string | null;
    fit: FitCell[];
    note: string | null;
    /**
     * Whether this run's selected form publishes the skill. Sent on every row by
     * `SkillsPlannerController::row()` (`:643`) and forwarded to `SkillPlanRow`, which renders the hint
     * control only for a pool row. It was missing here while the child required it, so the row's own
     * pool flag was invisible to this page.
     */
    in_pool: boolean;
}

interface Group {
    key: 'required' | 'available' | 'learned' | 'inherited';
    label: string;
    glyph: string;
    rows: PlanRow[];
    absent: string;
}

interface RecommendedSection {
    rows: { id: number; name: string; sp_cost: number; hint_adjusted_cost: number }[];
    total: number;
    count: number;
    sp: number | null;
    shortfall: number | null;
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string; skills_sync_url: string };
    target: null | {
        purpose: string;
        purpose_label: string;
        distance: string;
        surface: string;
        style: string;
        targets: Record<string, number>;
        save_url: string;
    };
    coverage: {
        sp: number | null;
        sp_title: string | null;
        state: 'no_priorities' | 'covered' | 'cost' | 'unpriced';
        total: number | null;
        total_title: string | null;
        remaining: number | null;
        absent: string | null;
        warn: boolean;
        text: string;
    };
/**
     * A-0.8: the priced skills the run has not acquired, sorted by cost, with the sum and the
     * shortfall against the run's recorded SP (M-BM-'2.2/M-BM-'2.3).
     */
    purchasable: {
        rows: { id: number; name: string; sp_cost: number; hint_adjusted_cost: number; rarity: number | null; is_gold: boolean; affordable?: boolean }[];
        total: number;
        count: number;
        sp: number | null;
        shortfall: number | null;
    };
    /** P4: budget-aware recommendation — covers priorities first, then cheapest remaining skills. */
    recommended: RecommendedSection;
    groups: Group[];
    acquisition_options: { value: string; label: string }[];
    /** UMAMUSUME_REFERENCE.md §1.1.4: the hint discount ladder, caption and percentage per row. */
    hint_discount: { key: number | string; label: string; discount: number }[];
    /** Slice 10: the Global catalogue skills this run does not hold yet, `{id, name}` each. */
    picker_skills: { id: number; name: string }[];
}>();

const { visiting, visitFailed } = useVisitState();

/*
 * The HFW's planner surface: a budget bar, filters, one skills table, the hint ladder and the
 * savebar. Planning is local until "Learn planned skills" posts the checked rows through the
 * existing skills-sync route (status Learned); the build target, coverage, purchasable list and
 * recorded groups stay in one disclosure so no write surface is lost.
 */
const planned = ref<number[]>([]);
const search = ref('');
const page = ref(1);
const PAGE_SIZE = 10;

const allRows = computed<PlanRow[]>(() => props.groups.flatMap((group) => group.rows));

const filteredRows = computed<PlanRow[]>(() => {
    const needle = search.value.trim().toLowerCase();

    return allRows.value.filter((row) => needle === '' || row.name.toLowerCase().includes(needle));
});

const pageCount = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / PAGE_SIZE)));

const visibleRows = computed(() => filteredRows.value.slice((page.value - 1) * PAGE_SIZE, page.value * PAGE_SIZE));

const plannedRows = computed<PlanRow[]>(() => allRows.value.filter((row) => row.id !== null && planned.value.includes(row.id)));

/**
 * What one row costs the plan: its displayed (hint-adjusted) cost, the same figure the table's
 * Cost column prints. Summing the base cost instead made the budget bar disagree with the row it
 * was summing, and made a hinted plan look more expensive than the Learn screen charges.
 */
function rowCost(row: PlanRow): number {
    if (row.sp_cost === null) {
        return 0;
    }

    return row.recorded?.hint_level !== null && row.recorded?.hint_level !== undefined
        ? getHintAdjustedCost(row.sp_cost, row.recorded.hint_level)
        : row.sp_cost;
}

const plannedSpend = computed(() => plannedRows.value.reduce((sum, row) => sum + rowCost(row), 0));

const remaining = computed(() => (props.coverage.sp === null ? null : props.coverage.sp - plannedSpend.value));

const budgetTitle = computed(() => (props.coverage.sp === null ? 'Spend Skill Points' : `Spend ${props.coverage.sp} Skill Points`));

const canCommit = computed(() => plannedRows.value.length > 0 && remaining.value !== null && remaining.value >= 0);

/** Slice 3.1: Gold skill warning — warn when White skills are selected but a Gold skill is within 50 SP of budget. */
const goldSkillWarning = computed((): number | null => {
    if (props.coverage.sp === null) {
        return null;
    }

    // Check if any White skill is selected (planned)
    const hasWhiteSelected = plannedRows.value.some((row) => {
        if (row.id === null) {
            return false;
        }
        // Find the skill in purchasable rows to check is_gold
        const purchasableSkill = props.purchasable.rows.find((s) => s.id === row.id);
        return purchasableSkill !== undefined && purchasableSkill.is_gold === false;
    });

    if (!hasWhiteSelected) {
        return null;
    }

    // Find the cheapest Gold skill
    const cheapestGold = props.purchasable.rows
        .filter((s) => s.is_gold === true)
        .sort((a, b) => (a.hint_adjusted_cost ?? a.sp_cost) - (b.hint_adjusted_cost ?? b.sp_cost))[0];

    if (!cheapestGold) {
        return null;
    }

    const goldCost = cheapestGold.hint_adjusted_cost ?? cheapestGold.sp_cost;
    const diff = goldCost - props.coverage.sp;

    // Warn if the Gold skill is within 50 SP of current budget
    return diff > 0 && diff <= 50 ? diff : null;
});

function togglePlan(id: number | null): void {
    if (id === null) {
        return;
    }

    planned.value = planned.value.includes(id) ? planned.value.filter((chosen) => chosen !== id) : [...planned.value, id];
}

function commitPlan(): void {
    if (!canCommit.value) {
        return;
    }

    pickerForm.skills = plannedRows.value.map((row) => ({
        skill_id: row.id as number,
        // "Learned" is the screen's word; the enum's case for a spent, acquired skill is
        // `Acquired`, and the Form Request validates against the enum (`SkillAcquisition`).
        status: 'Acquired',
        turn_acquired: null,
        hint_level: row.recorded?.hint_level ?? null,
    }));

pickerForm.post(props.run.skills_sync_url, {
        preserveScroll: true,
        onSuccess: () => {
            planned.value = [];
        },
    });
}

/** P2: hint discount percentage for a given hint level (1-5). */
function getHintDiscount(level: number): number {
    const discounts: Record<number, number> = { 1: 10, 2: 20, 3: 30, 4: 35, 5: 40 };
    return discounts[level] ?? 0;
}

/** P2: hint-adjusted cost (floor of base * (1 - discount/100)). */
function getHintAdjustedCost(base: number, level: number): number {
    const discount = getHintDiscount(level);
    return Math.floor(base * (100 - discount) / 100);
}

// The reorder is a local edit to the Trainer's list until it is saved, so it lives outside the
// props; a props change (the save's own response among them) re-syncs it to what the server
// resolved. The group array is fixed in shape, so the required group is found by key.
const requiredGroup = computed(() => props.groups.find((group) => group.key === 'required') ?? null);

const ordered = ref<PlanRow[]>([]);

watch(
    () => props.groups,
    () => {
        ordered.value = [...(requiredGroup.value?.rows ?? [])];
    },
    { immediate: true },
);

const announcement = ref('');

/**
 * ADR-0029 D4: save the level an acquired skill reached. The row's status and turn travel with it
 * because the sync rewrites the pivot row and the level together.
 */
function saveLevel(row: PlanRow, value: string): void {
    if (row.id === null || row.recorded === null) {
        return;
    }

    router.post(props.run.skills_sync_url, {
        skills: [{
            skill_id: row.id,
            status: row.recorded.status,
            turn_acquired: row.recorded.turn,
            current_level: value === '' ? null : Number(value),
        }],
    }, { preserveScroll: true });
}

/**
 * P2: save the hint level for a skill. The row's status and turn travel with it
 * because the sync rewrites the pivot row and the hint level together.
 */
function saveHint(row: PlanRow, value: number | null): void {
    if (row.id === null || row.recorded === null) {
        return;
    }

    router.post(props.run.skills_sync_url, {
        skills: [{
            skill_id: row.id,
            status: row.recorded.status,
            turn_acquired: row.recorded.turn,
            current_level: row.recorded.current_level,
            hint_level: value,
        }],
    }, { preserveScroll: true });
}

function move(index: number, delta: number): void {
    const to = index + delta;

    if (to < 0 || to >= ordered.value.length) {
        return;
    }

    const [row] = ordered.value.splice(index, 1);
    ordered.value.splice(to, 0, row);
    announcement.value = `${row.name} moved to position ${to + 1} of ${ordered.value.length}.`;
}

const form = useForm({
    purpose: '',
    distance: '',
    surface: '',
    style: '',
    targets: {} as Record<string, number>,
    skill_priorities: [] as string[],
});

// A refused save answers in its own alert, and focus moves there rather than staying on the press
// target (WCAG 2.2 SC 3.3.1, the idiom TrainingDetail's guided form owns).
const alertRef = ref<HTMLElement | null>(null);

watch(
    () => form.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }

        await nextTick();
        alertRef.value?.focus();
    },
);

function save(): void {
    if (props.target === null) {
        return;
    }

    // ponytail: the other fields ride along from this page's props, so a save from a stale render
    // overwrites a concurrent edit of the target; single-Trainer loopback makes that a ceiling
    // worth naming rather than a check worth building (upgrade path: an updated_at echo in the
    // Form Request).
    form.purpose = props.target.purpose;
    form.distance = props.target.distance;
    form.surface = props.target.surface;
    form.style = props.target.style;
    form.targets = props.target.targets;
    form.skill_priorities = ordered.value.map((row) => row.name);
    form.put(props.target.save_url, { preserveScroll: true });
}

/*
 * Slice 10, the add affordance. The editor below changes the status of a row that already exists in
 * one of the four groups, so a run with no build target and no rows had no way to gain its first
 * skill. This posts the same payload shape the editor's rows use, to the same run-scoped endpoint,
 * so there is one write path and one vocabulary.
 */
const pickedSkillId = ref<number | null>(null);

const pickerForm = useForm<{ skills: { skill_id: number | string; status: string; turn_acquired: number | null; hint_level: number | null }[] }>({
    skills: [],
});

// The enum's default, read the way the editor reads it: the first acquisition option, which is
// `Suggested`. `StoreRunSkillRequest` requires a status on every row, so the client sends it.
const pickerStatus = computed(() => props.acquisition_options[0]?.value ?? 'Suggested');

// A refusal is keyed by the row that failed (`skills.0.skill_id`), so the flat map is scanned for the
// first key under `skills` rather than read as `errors.skills`. Silent failure would be worse than the
// refusal: the picker offers only ids the server just sent, so a rejection means a stale render.
// The bag is indexed as a record because Inertia types it from the form's own shape, whose only key is
// `skills`: the server keys row failures `skills.<n>.<field>`, which those types cannot express.
const pickerError = computed(() => {
    const errors = pickerForm.errors as Record<string, string | undefined>;
    const key = Object.keys(errors).find((name) => name.startsWith('skills'));

    return key === undefined ? null : errors[key] ?? null;
});

function onSkillPicked(payload: { id: number; name: string }): void {
    pickedSkillId.value = payload.id;
}

function addPickedSkill(): void {
    if (pickedSkillId.value === null) {
        return;
    }

    pickerForm.skills = [{ skill_id: pickedSkillId.value, status: pickerStatus.value, turn_acquired: null, hint_level: null }];

    pickerForm.post(props.run.skills_sync_url, {
        preserveScroll: true,
        onSuccess: () => {
            pickedSkillId.value = null;
        },
    });
}

const addingRecommendation = ref(false);

function addRecommendation(): void {
    if (addingRecommendation.value) {
        return;
    }

    const recommendedIds = props.recommended.rows.map((r) => r.id);
    const newIds = recommendedIds.filter((id) => !planned.value.includes(id));

    if (newIds.length === 0) {
        return;
    }

    // A plan entry is a `run_skills` row: the write goes through the one sync endpoint, which
    // upserts, so a double submit cannot create a second entry per skill (the guard here is the
    // second line, not the first). The row then renders in Available with its hint selector, and
    // the local `planned` marks it for "Learn planned skills".
    const hintFor = (id: number): number | null => {
        const row = allRows.value.find((candidate) => candidate.id === id);

        return row?.recorded?.hint_level ?? null;
    };

    addingRecommendation.value = true;
    planned.value = [...planned.value, ...newIds];

    router.post(props.run.skills_sync_url, {
        skills: newIds.map((id) => ({
            skill_id: id,
            status: pickerStatus.value,
            turn_acquired: null,
            hint_level: hintFor(id),
        })),
    }, {
        preserveScroll: true,
        onFinish: () => {
            addingRecommendation.value = false;
        },
    });
}

// ADR-0001 §2 wants the constants behind a number visible wherever the number appears, so the two
// provenance strings these figures carry print as a line rather than only on hover. An absence's
// reason stays in the `title`: an unlogged Skill Point total is a gap, not a sourced figure.
const coverageConstants = computed(() =>
    [
        props.coverage.total !== null ? props.coverage.total_title : null,
        props.coverage.sp !== null ? props.coverage.sp_title : null,
    ]
        .filter((t): t is string => t !== null)
        .join(' · '),
);
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
        current="skills"
    >
        <Head :title="'Skills planner: ' + props.run.trainee" />
        <template #head>
            <PageHeader
                eyebrow="Skills planner"
                :title="budgetTitle"
                description="A hint lowers a skill's cost; the ladder is 10% / 20% / 30% / 35% / 40% off. A hint you have not earned still buys the skill at full price."
            >
                <template #actions>
                    <a :href="props.run.run_url" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">Back to cockpit</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <section class="card" aria-labelledby="skills-budget-heading">
            <h2 id="skills-budget-heading" class="sr-only">Skill Point budget</h2>
            <div class="flex flex-wrap items-center gap-4">
                <div class="min-w-[180px] flex-1">
                    <div class="text-xs font-extrabold uppercase tracking-[0.07em] text-ink-muted">Skill Points</div>
                    <div class="font-mono text-kpi font-extrabold leading-tight text-ink-strong">{{ props.coverage.sp ?? 'N/A' }}</div>
                </div>
                <div class="min-w-[180px] flex-1">
                    <div class="text-xs font-extrabold uppercase tracking-[0.07em] text-ink-muted">Planned spend</div>
                    <div class="font-mono text-kpi font-extrabold leading-tight text-ink-strong">{{ plannedSpend }}</div>
                </div>
                <div class="min-w-[180px] flex-1">
                    <div class="text-xs font-extrabold uppercase tracking-[0.07em] text-ink-muted">Remaining</div>
                    <div class="font-mono text-kpi font-extrabold leading-tight" :class="remaining === null || remaining < 0 ? 'text-risk' : 'text-green-deep'">{{ remaining ?? 'N/A' }}</div>
                </div>
                <button type="button" class="btn btn-primary min-h-11" :disabled="!canCommit || pickerForm.processing" @click="commitPlan">
                    {{ pickerForm.processing ? 'Learning…' : 'Learn planned skills' }}
                </button>
            </div>
            <Note class="mt-2.5">
                The budget bar prints the run's recorded SP beside the plan's own sum, so the constraint is visible
                while browsing rather than discovered at commit.
            </Note>
        </section>

        <div class="mt-3.5 flex flex-wrap items-end gap-2.5">
            <label class="flex min-w-[220px] flex-1 flex-col gap-1">
                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Search</span>
                <input v-model="search" type="search" name="skill_search" placeholder="Skill name" class="min-h-11 rounded-md border border-rule bg-raised px-3 text-sm" @input="page = 1">
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Type</span>
                <BaseSelect disabled :options="[{ value: '', label: 'Any' }]" model-value="" title="No column carries a skill type, so this filter cannot act." />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">Hints</span>
                <BaseSelect disabled :options="[{ value: '', label: 'Any' }]" model-value="" title="Hint levels are not recorded per skill, so this filter cannot act." />
            </label>
            <button type="button" class="btn btn-quiet min-h-11" @click="search = ''; page = 1">Clear</button>
        </div>

        <section class="card mt-3" aria-labelledby="skills-table-heading">
            <h2 id="skills-table-heading" class="sr-only">Skills</h2>
            <p v-if="filteredRows.length === 0" class="text-sm text-ink">{{ search === '' ? 'No skill is recorded on this run yet.' : 'No skill matches that search.' }}</p>
            <template v-else>
                <div class="overflow-x-auto">
                    <table class="t w-full min-w-[44rem] border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="w-8"></th><th class="text-left">Skill</th><th class="text-left">Family</th>
                                <th class="text-right">Base</th><th class="text-right">Discount</th><th class="text-right">Cost</th><th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in visibleRows" :key="row.id ?? row.name" :class="row.id !== null && planned.includes(row.id) ? 'is-current bg-green-tint' : ''" class="border-b border-rule-soft">
                                <td class="py-2 pr-2">
                                    <input type="checkbox" :aria-label="'Plan ' + row.name" :checked="row.id !== null && planned.includes(row.id)" :disabled="row.id === null" @change="togglePlan(row.id)">
                                </td>
                                <td class="py-2 pr-3">
                                    <b>{{ row.name }}</b>
                                    <span class="block text-xs text-ink-muted">{{ row.conditions ?? 'conditions not recorded' }}</span>
                                </td>
                                <td class="py-2 pr-3">{{ row.is_unique ? 'Unique' : 'Standard' }}</td>
                                <td class="py-2 pr-3 text-right font-mono">{{ row.sp_cost ?? 'N/A' }}</td>
                                <td class="py-2 pr-3 text-right font-mono">
                                    <template v-if="row.recorded !== null && row.recorded.hint_level !== null">
                                        {{ getHintDiscount(row.recorded.hint_level) }}%
                                    </template>
                                    <template v-else>
                                        <span class="text-ink-muted" title="No hint level recorded; full base cost applies.">N/A</span>
                                    </template>
                                </td>
                                <td class="py-2 pr-3 text-right font-mono">
                                    <template v-if="row.sp_cost !== null && row.recorded !== null && row.recorded.hint_level !== null">
                                        <b>{{ getHintAdjustedCost(row.sp_cost, row.recorded.hint_level) }}</b>
                                    </template>
                                    <template v-else>
                                        <b>{{ row.sp_cost ?? 'N/A' }}</b>
                                    </template>
                                </td>
                                <td class="py-2 text-right">
                                    <span v-if="row.recorded !== null" class="pill pill-neutral">{{ row.recorded.status }}</span>
                                    <span v-else-if="row.id !== null && planned.includes(row.id) && remaining !== null && remaining < 0" class="pill pill-caution">Too expensive</span>
                                    <span v-else-if="row.id !== null && planned.includes(row.id)" class="pill pill-caution">Planned</span>
                                    <span v-else-if="row.id === null" class="pill pill-neutral">Catalogue only</span>
                                    <span v-else class="text-xs text-ink-muted">Not planned</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="text-ink-muted">{{ filteredRows.length }} {{ filteredRows.length === 1 ? 'skill' : 'skills' }} · page {{ page }} of {{ pageCount }}</span>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-sm min-h-11" :disabled="page <= 1" @click="page = Math.max(1, page - 1)">Previous</button>
                        <button type="button" class="btn btn-sm min-h-11" :disabled="page >= pageCount" @click="page = Math.min(pageCount, page + 1)">Next</button>
                    </div>
                </div>
            </template>
        </section>

        <section class="card mt-3.5" aria-labelledby="skills-ladder-heading">
            <h2 id="skills-ladder-heading">Why the hint ladder is not linear</h2>
            <table class="t mt-1 w-full border-collapse text-sm">
                <thead><tr><th class="text-left">Hint level</th><th class="text-right">Discount</th><th class="text-left">Client caption</th></tr></thead>
                <tbody>
                    <tr v-for="step in props.hint_discount" :key="step.key" class="border-b border-rule-soft">
                        <td class="py-2 pr-3">{{ step.label }}</td>
                        <td class="py-2 pr-3 text-right font-mono">{{ step.discount }}%</td>
                        <td class="py-2 font-mono">{{ step.label }}</td>
                    </tr>
                </tbody>
            </table>
            <Note class="mt-2.5">
                The ladder steps +10, +10, +10, +5, +5, and it is not linear past Lv3. The measured sample reconciles
                exactly: 130 → 104 / 91 / 84, and 160 → 144 / 128 / 112 / 96. The one rounding case is
                130 × 0.65 = 84.5, floored to 84. "Lv Max" is a caption, not a fifth number.
            </Note>
        </section>

        <!-- A STATUS STRIP, NOT A SECOND COMMIT.
             `skills-planner.html` draws no savebar at all: its one primary is "Learn planned skills",
             in the budget card where the SP figure the decision needs sits (SHR-7). This bar used to
             carry a second `.btn-primary` bound to the same `commitPlan`, so the page offered the same
             write twice and spent the accent twice (UX-6, UX-7).

             The primary is gone; the bar keeps its class, its `role="status"` count and `Clear plan`.
             HFW-OVERRIDE (savebar: demoted to a status strip). The class name stays because
             `career-skills-planner.spec.ts:84` pins `.savebar` count 1, so the container is what the
             spec binds; the promise is carried by the words in it, not by the container. -->
        <div class="savebar">
            <span class="dirty" role="status">
                {{ planned.length === 0 ? 'No skill planned' : planned.length + (planned.length === 1 ? ' skill planned' : ' skills planned') }}
            </span>
            <button type="button" class="btn min-h-11" @click="planned = []">Clear plan</button>
        </div>
        <p v-if="pickerError" role="alert" class="mt-2 text-sm text-risk">{{ pickerError }}</p>

        <details class="card mt-3.5">
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Build target, recorded skills and the add form</summary>
            <div class="mt-2">
                <section aria-labelledby="skills-target-heading" class="rounded-md border border-rule bg-panel p-4">
                            <h2 id="skills-target-heading" class="text-lg font-semibold text-ink-strong">Your target</h2>
                            <p v-if="props.target !== null" class="mt-1 flex flex-wrap gap-x-3 text-sm text-ink">
                                <span>{{ props.target.purpose_label }}</span>
                                <span aria-hidden="true">·</span>
                                <span>{{ props.target.distance }}</span>
                                <span aria-hidden="true">·</span>
                                <span>{{ props.target.surface }}</span>
                                <span aria-hidden="true">·</span>
                                <span>{{ props.target.style }}</span>
                                <span
                                    v-for="(value, stat) in props.target.targets"
                                    :key="stat"
                                    class="font-mono tabular-nums text-ink-muted"
                                >{{ stat }} {{ value }}</span>
                            </p>
                            <p v-else class="mt-1 text-sm text-ink">
                                No build target is set on this run, so the fit cells below compare against nothing.
                                This screen reorders the priorities a target names; it does not add any.
                            </p>
                        </section>
                
                        <section aria-labelledby="skills-coverage-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                            <h2 id="skills-coverage-heading" class="flex items-center gap-2 text-lg font-semibold text-ink-strong">
                                Skill Point coverage
                                <ProvenanceBadge state="calculated" title="The sum of the base prices of the skills still to learn, against the last recorded Skill Point total." />
                            </h2>
                            <!-- The computed line, or the named absence. A bare `N/A` here read as a panel that
                                 could never resolve; the total now prints whenever the catalogue prices every
                                 skill, and names the ones it cannot price when it does not. The fourth case is the
                                 one R2-10 added: with no priorities there is no sum to report, so the screen says
                                 that instead of printing the zero an empty list happens to add up to. -->
                            <p v-if="props.coverage.state === 'no_priorities'" class="mt-1 text-sm text-ink">
                                {{ props.coverage.absent }}
                            </p>
                            <p v-else-if="props.coverage.total !== null" class="mt-1 text-sm text-ink">
                                The skills still to learn cost
                                <span class="font-mono tabular-nums" :title="props.coverage.total_title ?? undefined">{{ props.coverage.total }}</span>
                                SP, and the run holds
                                <span class="font-mono tabular-nums" :title="props.coverage.sp_title ?? undefined">{{ props.coverage.sp ?? 'N/A' }}</span>
                                SP<template v-if="props.coverage.remaining !== null">, leaving
                                    <span class="font-mono tabular-nums">{{ props.coverage.remaining }}</span></template>.
                            </p>
                            <p v-else class="mt-1 text-sm text-ink">{{ props.coverage.absent }}</p>
                
                            <!-- UMAMUSUME_REFERENCE.md §1.1.4: the hint discount ladder is a client fact, and it
                                 renders below in the Hint discounts section. A row here stays at its base cost
                                 because a discount applies only when a hint level sits on the skill, and no column
                                 records one, so nothing re-prices a row against the ladder. -->
                            <p class="mt-1 text-xs text-ink-muted">
                                Every figure above prices a skill at its base cost. The client charges less when a hint
                                level sits on the skill; the ladder it applies is the Hint discounts section below, and a
                                run records no hint level here, so no discount is applied to these rows.
                            </p>
                            <p v-if="coverageConstants !== ''" class="mt-1 text-xs text-ink-muted">
                                {{ coverageConstants }}
                            </p>
                            <p
                                v-if="props.coverage.warn"
                                role="status"
                                class="mt-2 rounded-md border-2 border-goal-line bg-raised p-3 text-sm font-semibold text-ink-strong"
                            >
                                <span aria-hidden="true" class="font-mono font-bold text-goal-line">!</span>
                                {{ props.coverage.text }} The total prices every skill at its base; the client pays
                                less when a hint level is on the skill, and this build does not record hint
                                levels, so the sum is the honest one.
                            </p>
                            <p v-else-if="props.coverage.text !== ''" class="mt-2 text-sm text-ink">
                                {{ props.coverage.text }}
                            </p>
                        </section>
                
                        <!-- A-0.8: the priced skills the run has not acquired, sorted by cost, with the sum and the
                             shortfall against the run's SP (§2.2's "purchasable at or under N SP", §2.3's recommended
                             spend). Read-only; the catalogue carries every base price. -->
                        <section aria-labelledby="skills-purchasable-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                            <h2 id="skills-purchasable-heading" class="flex items-center gap-2 text-lg font-semibold text-ink-strong">
                                Purchasable skills
                                <ProvenanceBadge state="calculated" title="Every priced skill the run has not acquired, sorted by base SP cost, against the last recorded Skill Point total." />
                            </h2>
                            <p class="mt-1 text-sm text-ink">
                                <span v-if="props.purchasable.count > props.purchasable.rows.length">Showing the cheapest </span>
                                <span class="font-mono tabular-nums">{{ props.purchasable.rows.length }}</span>
                                <span v-if="props.purchasable.count > props.purchasable.rows.length">
                                    of <span class="font-mono tabular-nums">{{ props.purchasable.count }}</span>
                                </span>
                                priced skills not yet acquired cost
                                <span class="font-mono tabular-nums">{{ props.purchasable.total }}</span> SP in total<template
                                    v-if="props.purchasable.sp !== null"
                                >, and the run holds <span class="font-mono tabular-nums">{{ props.purchasable.sp }}</span> SP<template
                                    v-if="props.purchasable.shortfall !== null && props.purchasable.shortfall > 0"
                                >, a shortfall of <span class="font-mono tabular-nums">{{ props.purchasable.shortfall }}</span> SP</template></template>.
                            </p>
                            <p class="mt-1 text-xs text-ink-muted">
                                This list reads the full catalogue, not this run's pool.
                            </p>
                            <!-- Gold skill warning: warn if any white skill is selected while a gold skill is within 50 SP.
                                 The surface is the caution pair from `app.css` -- `--color-status-caution` behind
                                 `--color-status-caution-ink`, a G-18 contract row at 4.5:1 in both themes -- rather
                                 than a raw amber palette shade: a hardcoded palette value cannot follow the theme,
                                 which forks by flipping custom properties only (D-101), and `DesignTokensTest`
                                 rejects palette classes outright. The shape follows the existing caution callout
                                 precedent (`Legacy/Compare.vue`, `Runs/Import.vue`): a rule border on the caution fill. -->
                            <div
                                v-if="goldSkillWarning"
                                class="mt-2 p-3 rounded-md border border-rule bg-status-caution text-status-caution-ink text-sm"
                                role="alert"
                            >
                                <strong>Gold skill within reach:</strong> You have selected White skills while a Gold skill costs only {{ goldSkillWarning }} SP more than your current budget. Consider saving SP for the Gold skill.
                            </div>
                            <!-- A capped scroll region is keyboard-reachable or it is not reachable (WCAG 2.1.1 and
                                 axe's `scrollable-region-focusable`): `tabindex` puts the list itself in the tab
                                 order and the name says what scrolling it reveals. -->
                            <ol
                                v-if="props.purchasable.rows.length > 0"
                                tabindex="0"
                                aria-label="Priced skills this run has not acquired"
                                class="mt-2 max-h-72 space-y-1 overflow-y-auto text-sm"
                            >
                                <li
                                    v-for="skill in props.purchasable.rows"
                                    :key="skill.id"
                                    class="flex items-baseline justify-between gap-3"
                                    :class="[
                                        skill.affordable === false ? 'opacity-50 pointer-events-none' : '',
                                        skill.is_gold ? 'bg-yellow-50 border-l-2 border-yellow-400 pl-2' : '',
                                    ]"
                                >
                                    <span class="text-ink">{{ skill.name }}</span>
                                    <span class="font-mono tabular-nums text-ink-strong">{{ skill.sp_cost }} SP</span>
                                    <span v-if="skill.is_gold" class="pill pill-yellow text-xs">Gold</span>
                                </li>
                            </ol>
<p v-else class="mt-1 text-sm text-ink">The catalogue prices no skill this run has not already acquired.</p>
                        </section>

                        <!-- P4: Budget-aware recommendation panel -->
                        <section aria-labelledby="skills-recommended-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                            <h2 id="skills-recommended-heading" class="flex items-center gap-2 text-lg font-semibold text-ink-strong">
                                Recommended <span class="pill pill-neutral" aria-describedby="recommended-source">Calculated</span>
                            </h2>
                            <p class="sub mt-1">
                                Covers required priorities first at their hint-adjusted costs, then fills remaining SP
                                with the cheapest remaining skills. The rule is this tool's heuristic, not a client
                                mechanic.
                            </p>
                            <div v-if="props.recommended.rows.length > 0" class="mt-2">
                                <p class="text-sm text-ink">
                                    <span v-if="props.recommended.count > props.recommended.rows.length">Showing the first </span>
                                    <span class="font-mono tabular-nums">{{ props.recommended.rows.length }}</span>
                                    <span v-if="props.recommended.count > props.recommended.rows.length">
                                        of <span class="font-mono tabular-nums">{{ props.recommended.count }}</span>
                                    </span>
                                    recommended skills cost
                                    <span class="font-mono tabular-nums">{{ props.recommended.total }}</span> SP
                                    <template v-if="props.recommended.sp !== null">
                                        , and the run holds <span class="font-mono tabular-nums">{{ props.recommended.sp }}</span> SP
                                        <template v-if="props.recommended.shortfall !== null && props.recommended.shortfall > 0">
                                            , a shortfall of <span class="font-mono tabular-nums">{{ props.recommended.shortfall }}</span> SP
                                        </template>
                                        <template v-else>
                                            , leaving <span class="font-mono tabular-nums">{{ props.recommended.sp - props.recommended.total }}</span> SP unspent
                                        </template>
                                    </template>.
                                </p>
                                <p class="mt-1 text-xs text-ink-muted" id="recommended-source">
                                    (a) The rule is this tool's own heuristic, not a client mechanic. (b) The fill reuses the cheapest-first ordering. (c) The pool is the run's form's skill list, the trainee profile's "Skills on this form" (unique + innate + awakening, plus acquired event skills). (d) When the budget outlasts the pool, the residual is printed.
                                </p>
                                <ol tabindex="0" aria-label="Recommended skills for this run" class="mt-2 max-h-72 space-y-1 overflow-y-auto text-sm">
                                    <li v-for="skill in props.recommended.rows" :key="skill.id" class="flex items-baseline justify-between gap-3">
                                        <span class="text-ink">{{ skill.name }}</span>
                                        <span class="font-mono tabular-nums text-ink-strong">{{ skill.hint_adjusted_cost }} SP</span>
                                    </li>
                                </ol>
                                <button
                                    type="button"
                                    class="btn btn-primary min-h-11 mt-2"
                                    :disabled="planned.some(p => props.recommended.rows.some(r => r.id === p)) || addingRecommendation"
                                    @click="addRecommendation"
                                >
                                    {{ addingRecommendation ? 'Adding…' : 'Add recommendation to plan' }}
                                </button>
                            </div>
                            <p v-else class="mt-1 text-sm text-ink-muted">
                                No recommendation can be generated: the run has no recorded SP, or all skills are already acquired.
                            </p>
                        </section>

                        <!-- UMAMUSUME_REFERENCE.md §1.1.4: the hint discount ladder, read off the client Learn screen. -->
                        <section aria-labelledby="skills-hints-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                            <h2 id="skills-hints-heading" class="text-lg font-semibold text-ink-strong">Hint discounts</h2>
                            <p class="mt-1 text-sm text-ink">
                                The client's Learn screen prints <code>Hint Lvl N</code> above <code>NN% OFF</code>. The
                                displayed cost is <code>base × (1 − discount)</code>, floored to the integer.
                            </p>
                            <ol class="mt-2 grid max-w-sm grid-cols-[1fr_auto] gap-x-6 gap-y-1 text-sm">
                                <li v-for="row in props.hint_discount" :key="String(row.key)" class="contents">
                                    <span class="text-ink">{{ row.label }}</span>
                                    <span class="font-mono tabular-nums text-ink-strong">{{ row.discount }}% off</span>
                                </li>
                            </ol>
                        </section>
                
                        <section
                            v-for="group in props.groups"
                            :key="group.key"
                            :aria-labelledby="`skills-${group.key}-heading`"
                            class="mt-4"
                        >
                            <h2 :id="`skills-${group.key}-heading`" class="text-lg font-semibold text-ink-strong">
                                <span aria-hidden="true" class="font-mono font-bold text-goal-line">{{ group.glyph }}</span>
                                {{ group.label }}
                            </h2>
                
                            <p v-if="group.rows.length === 0" class="mt-2 rounded-md border border-dashed border-rule bg-raised p-3 text-sm text-ink">
                                {{ group.absent }}
                            </p>
                
                            <ol v-else class="mt-2 flex flex-col gap-2">
                                <!-- The required group renders the reorderable local order; the other groups render
                                     the rows as resolved. -->
                                <SkillPlanRow
                                    v-for="(row, index) in (group.key === 'required' ? ordered : group.rows)"
                                    :key="row.id ?? `${group.key}-${row.name}`"
                                    :row="row"
                                    :position="group.key === 'required' ? index + 1 : 1"
                                    :count="group.key === 'required' ? ordered.length : 1"
                                    :movable="group.key === 'required'"
                                    @move="(delta: number) => move(index, delta)"
                                    @level="(value: string) => saveLevel(row, value)"
                                    @hint="(value: number | null) => saveHint(row, value)"
                                />
                            </ol>
                        </section>
                
                        <!-- The reorder's announcement: the region exists from render, so a move is announced the
                             moment the text lands in it. -->
                        <p role="status" aria-live="polite" class="mt-2 text-sm text-ink-muted">{{ announcement }}</p>
                
                        <!-- The one write: the reordered list, saved through the build target's own Form Request.
                             The other target fields ride along unchanged because the write replaces the object
                             whole. -->
                        <form
                            v-if="props.target !== null && ordered.length > 0"
                            class="mt-4 flex max-w-3xl items-center gap-3"
                            :aria-busy="form.processing"
                            @submit.prevent="save"
                        >
                            <button
                                type="submit"
                                :aria-describedby="form.hasErrors ? 'skills-order-error' : undefined"
                                :disabled="form.processing"
                                class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-5 text-sm font-bold text-on-primary-action disabled:opacity-60"
                            >
                                {{ form.processing ? 'Saving…' : 'Save order' }}
                            </button>
                            <span class="text-xs text-ink-muted">The order above saves as the run's skill priorities.</span>
                            <p
                                v-if="form.hasErrors"
                                id="skills-order-error"
                                ref="alertRef"
                                role="alert"
                                tabindex="-1"
                                class="rounded-md border border-risk bg-raised px-2 py-1 text-sm text-risk"
                            >
                                <span v-for="(message, key) in form.errors" :key="key" class="block">{{ message }}</span>
                            </p>
                        </form>
                        <p v-else-if="props.target === null" class="mt-2 text-sm text-ink-muted" role="status">
                            Nothing to save: a run with no build target has no priority list to reorder.
                        </p>
                
<!-- The acquired/skipped status write (F2, plan §9.6 ruling 5). The build target is a plan;
                             the acquisition status is a record of what the run actually has. The two are separate
                             writes on separate routes so one cannot overwrite the other. This form POSTs through
                             `runs.skills.sync`, the same Form Request the 2.0.0 record screen used. -->
                        <!-- Slice 10: the add affordance. The editor below edits rows that already exist; this is
                             where a run gains one it has never held, so the write path and the vocabulary stay the
                             endpoint's own. -->
                        <section aria-labelledby="add-skill-heading" class="mt-6 rounded-md border border-rule bg-panel p-4">
                            <h2 id="add-skill-heading" class="text-lg font-semibold text-ink-strong">Add a skill</h2>
                            <p v-if="props.picker_skills.length === 0" class="mt-1 text-sm text-ink-muted">
                                Nothing left to add: the catalogue offers no Global skill this run does not already hold.
                            </p>
                            <form v-else class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 text-sm" @submit.prevent="addPickedSkill">
                                <SearchableSkillPicker
                                    :options="props.picker_skills.map(s => ({ id: s.id, name: s.name, type: '' }))"
                                    label="Skill to add"
                                    placeholder="Search and add a skill…"
                                    hint="Pick a skill by name; type to filter. The selected skill is added to the run."
                                    @select="onSkillPicked"
                                />
                                <BaseButton type="submit" variant="secondary" size="md" :disabled="!pickedSkillId || form.processing">
                                    Add to run
                                </BaseButton>
                            </form>
                        </section>
                
                        <AcquisitionStatusEditor
                            v-if="props.run.skills_sync_url !== ''"
                            class="mt-6"
                            :action="props.run.skills_sync_url"
                            :groups="props.groups"
                            :options="props.acquisition_options"
                        />
            </div>
        </details>
    </CareerLayout>
</template>
