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
 * roles, and every figure composed server-side. No percentage appears here: the hint-discount
 * ladder is not modelled, because no column holds a hint level, and the screen states that absence
 * rather than printing a derived percentage.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import SkillPlanRow from '../../components/career/SkillPlanRow.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import AcquisitionStatusEditor from '../../components/career/AcquisitionStatusEditor.vue';
import { BaseButton, BaseSelect } from '../../components/Form';
import { Head, useForm } from '@inertiajs/vue3';
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
    groups: Group[];
    acquisition_options: { value: string; label: string }[];
    /** Slice 10: the Global catalogue skills this run does not hold yet, `{id, name}` each. */
    picker_skills: { id: number; name: string }[];
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
const pickedSkill = ref<number | string>('');

const pickerForm = useForm<{ skills: { skill_id: number | string; status: string; turn_acquired: number | null }[] }>({
    skills: [],
});

// The enum's default, read the way the editor reads it: the first acquisition option, which is
// `Suggested`. `StoreRunSkillRequest` requires a status on every row, so the client sends it.
const pickerStatus = computed(() => props.acquisition_options[0]?.value ?? 'Suggested');

// A refusal is keyed by the row that failed (`skills.0.skill_id`), so the flat map is scanned for the
// first key under `skills` rather than read as `errors.skills`. Silent failure would be worse than the
// refusal: the picker offers only ids the server just sent, so a rejection means a stale render.
const pickerError = computed(() => {
    const key = Object.keys(pickerForm.errors).find((name) => name.startsWith('skills'));

    return key === undefined ? null : pickerForm.errors[key];
});

function addPickedSkill(): void {
    if (pickedSkill.value === '') {
        return;
    }

    pickerForm.skills = [{ skill_id: pickedSkill.value, status: pickerStatus.value, turn_acquired: null }];

    pickerForm.post(props.run.skills_sync_url, {
        preserveScroll: true,
        onSuccess: () => {
            pickedSkill.value = '';
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
    >
        <Head :title="`Skills planner: ${props.run.trainee}`" />
        <template #title>Skills planner</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <!-- The target the fit cells compare against, or the named absence. -->
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

        <!-- The hint discounts are not modelled: no column holds a hint level, so nothing the run
             records can price one. The screen states that absence rather than printing a derived
             percentage (ADR-0001 §3's rule for a percentage no stored value backs). -->
        <section aria-labelledby="skills-hints-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
            <h2 id="skills-hints-heading" class="text-lg font-semibold text-ink-strong">Hint discounts</h2>
            <p class="mt-1 text-sm text-ink">Hint discounts are not modelled.</p>
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
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-5 text-sm font-bold text-on-chrome disabled:opacity-60"
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
             `runs.skills.sync`, the same Form Request the 0.1.0 record screen used. -->
        <!-- Slice 10: the add affordance. The editor below edits rows that already exist; this is
             where a run gains one it has never held, so the write path and the vocabulary stay the
             endpoint's own. -->
        <section aria-labelledby="add-skill-heading" class="mt-6 rounded-md border border-rule bg-panel p-4">
            <h2 id="add-skill-heading" class="text-lg font-semibold text-ink-strong">Add a skill</h2>
            <p v-if="props.picker_skills.length === 0" class="mt-1 text-sm text-ink-muted">
                Nothing left to add: the catalogue offers no Global skill this run does not already hold.
            </p>
            <form v-else class="mt-3 flex max-w-3xl flex-wrap items-end gap-3 text-sm" @submit.prevent="addPickedSkill">
                <BaseSelect
                    v-model="pickedSkill"
                    id="add-skill-picker"
                    name="skill"
                    label="Skill to add"
                    :options="[{ value: '', label: 'Choose a skill' }, ...props.picker_skills.map(s => ({ value: s.id, label: s.name }))]"
                />
                <BaseButton type="submit" variant="secondary" :disabled="pickerForm.processing">
                    Add skill
                </BaseButton>
            </form>
            <p v-if="pickerForm.processing" role="status" class="mt-2 text-sm text-ink-muted">Adding the skill…</p>
            <p v-if="pickerError" role="alert" class="mt-2 text-sm text-risk">{{ pickerError }}</p>
        </section>

        <AcquisitionStatusEditor
            v-if="props.run.skills_sync_url !== ''"
            class="mt-6"
            :action="props.run.skills_sync_url"
            :groups="props.groups"
            :options="props.acquisition_options"
        />
    </CareerLayout>
</template>
