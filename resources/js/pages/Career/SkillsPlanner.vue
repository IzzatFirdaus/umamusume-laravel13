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
 * roles, and every figure composed server-side. Percentages appear here and are the client's own
 * hint-discount captions, which is a sourced display, not a prediction of anything.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import SkillPlanRow from '../../components/career/SkillPlanRow.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface CostEntry {
    level: string;
    percent: number;
    cost: number | null;
}

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
    costs: CostEntry[];
    recorded: { status: string; turn: number | null } | null;
    conditions: string | null;
    fit: FitCell[];
    note: string | null;
}

interface Group {
    key: 'required' | 'available' | 'learned' | 'inherited';
    label: string;
    glyph: string;
    rows: PlanRow[];
    absent: string;
}

const props = defineProps<{
    run: { id: number; trainee: string; trainee_ja: string | null; scenario_label: string; status_label: string; run_url: string };
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
        total: number | null;
        total_title: string | null;
        warn: boolean;
        text: string;
    };
    groups: Group[];
    ladder: { level: string; percent: number }[];
}>();

const { visiting, visitFailed } = useVisitState();

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
    >
        <Head :title="`Skills planner: ${props.run.trainee}`" />
        <template #title>Skills planner</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <!-- The target the fit cells compare against, or the named absence. -->
        <section aria-labelledby="skills-target-heading" class="rounded-md border border-rule bg-panel p-4">
            <h2 id="skills-target-heading" class="text-base font-semibold text-ink-strong">Your target</h2>
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
            <h2 id="skills-coverage-heading" class="flex items-center gap-2 text-base font-semibold text-ink-strong">
                Skill Point coverage
                <ProvenanceBadge state="calculated" title="The sum of the base prices of the skills still to learn, against the last recorded Skill Point total." />
            </h2>
            <p class="mt-1 text-sm text-ink">
                The skills still to learn cost
                <span class="font-mono tabular-nums" :title="props.coverage.total_title ?? undefined">{{ props.coverage.total ?? 'N/A' }}</span>
                SP, and the run holds
                <span class="font-mono tabular-nums" :title="props.coverage.sp_title ?? undefined">{{ props.coverage.sp ?? 'N/A' }}</span>
                SP.
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
                less when a hint level is on the skill, and no column holds hint levels, so the
                sum is the honest one.
            </p>
            <p v-else-if="props.coverage.text !== ''" class="mt-2 text-sm text-ink">
                {{ props.coverage.text }}
            </p>
        </section>

        <!-- The ladder the per-row costs are priced from. The percentages are the client's own
             captions (REFERENCE §1.1.4), which is why this screen can print them at all. -->
        <section aria-labelledby="skills-ladder-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="skills-ladder-heading" class="text-base font-semibold text-ink-strong">The hint ladder</h2>
            <p class="mt-1 flex flex-wrap items-center gap-x-3 text-sm text-ink">
                <span v-for="level in props.ladder" :key="level.level" class="font-mono tabular-nums">
                    {{ level.level }} {{ level.percent }}% off
                </span>
                <ProvenanceBadge
                    state="confirmed"
                    title="Read off the [Global] Learn screen on 2026-10-03: UMAMUSUME_REFERENCE.md §1.1.4, SKILLS-MECHANICS.md §2.4, run report §2.1."
                />
            </p>
        </section>

        <section
            v-for="group in props.groups"
            :key="group.key"
            :aria-labelledby="`skills-${group.key}-heading`"
            class="mt-4"
        >
            <h2 :id="`skills-${group.key}-heading`" class="text-base font-semibold text-ink-strong">
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
            class="mt-4 flex items-center gap-3"
            :aria-busy="form.processing"
            @submit.prevent="save"
        >
            <button
                type="submit"
                :aria-describedby="form.hasErrors ? 'skills-order-error' : undefined"
                :disabled="form.processing"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-5 text-sm font-bold text-on-chrome disabled:opacity-60"
            >
                {{ form.processing ? 'Saving…' : 'Save order' }}
            </button>
            <span class="text-xs text-ink-muted">The order above saves as the run's skill priorities.</span>
            <p v-if="form.hasErrors" id="skills-order-error" role="alert" class="text-sm text-risk">
                {{ Object.values(form.errors)[0] ?? 'The order could not be saved.' }}
            </p>
        </form>
        <p v-else-if="props.target === null" class="mt-2 text-sm text-ink-muted" role="status">
            Nothing to save: a run with no build target has no priority list to reorder.
        </p>
    </CareerLayout>
</template>
