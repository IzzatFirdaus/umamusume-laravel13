<script setup lang="ts">
/*
 * The Inheritance Event, `SCR-CAR-015` (SCREEN-013, plan §8 D12). Record-only inheritance tracking.
 *
 * **Layout.** Follows the career screen pattern: `CareerLayout`, `<Head title>`, `#title` slot.
 * Two main regions - Predicted and Observed - visually separate per antislop-ui and the
 * screen-spec correction row. Each region's header carries its provenance state via
 * `ProvenanceBadge` (Estimated / Confirmed respectively), so the word and glyph live only
 * in that component. A milestone timeline with glyphs sits above.
 *
 * **The page pattern is `Catalog/Index.vue`'s**: the layout, `<Head title>`, the `#title` slot,
 * and `router.get` with `preserveState`/`preserveScroll` for any async visits. The observed form
 * posts to `runs.inheritance.store` via `useForm` with `@submit.prevent`.
 *
 * **Loading and error are page-level** (ADR-0007): the only user-initiated async action is the
 * observed event submission, and Inertia keeps the current page on screen during a visit.
 * `invalid` and `exception` are both cancelable, and returning `false` takes the failure out of
 * Inertia's default modal so this page's own `role="alert"` is the single surface.
 *
 * **Trust vocabulary on every figure.** Predicted carries `Estimated`; Observed carries `Confirmed`
 * because the Trainer entered it. The "expected inheritance" is `N/A` with a `title` citing
 * ADR-0020 §3. The star-roll table is sourced (REFERENCE §1.5.3) and marked `Estimated`.
 *
 * **Props contract declared locally** because TypeScript 7 ships no `lib/typescript.js` for
 * `@vue/compiler-sfc` to load (plan §11).
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import SparkChip from '../../components/legacy/SparkChip.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useVisitState } from '../../composables/useVisitState';
import { BaseInput, BaseSelect, BaseTextarea } from '../../components/Form';

interface RunSection {
    id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    run_url: string;
    cockpit_url: string;
}

interface LegacyParent {
    slot: string;
    label: string;
    name: string | null;
    rank: number | null;
    is_guest: boolean;
    /** A list of names, not slot records: the position is the slot (`ADR-0010` Consequences §2). */
    ancestors: (string | null)[];
    sparks: { kind: string; kind_label: string; target: string | null; stars: number | null }[];
    spark_counts: { kind: string; kind_label: string; count: number }[];
}

interface LegacySection {
    affinity: string | null;
    parents: LegacyParent[];
}

interface PredictedSpark {
    kind: string;
    kind_label: string;
    total_count: number;
}

interface PredictedSection {
    predicted_sparks: PredictedSpark[];
    expected_inheritance: { label: string; title: string };
    star_roll_table: { stat_range: string; one_star: string; two_star: string; three_star: string }[];
}

interface ObservedEvent {
    id: number;
    turn: number;
    source_name: string;
    choice_label: string | null;
    deltas: Record<string, unknown> | null;
    origin_note: string | null;
}

interface ObservedSection {
    events: ObservedEvent[];
    provenance: string;
}

interface Milestone {
    key: string;
    label: string;
    glyph: string;
    status: 'completed' | 'current' | 'upcoming' | 'missed';
    title: string;
}

interface WriteSection {
    action: string;
    turns: { id: number; turn: number }[];
    sources: string[];
}

const props = defineProps<{
    run: RunSection;
    legacy: LegacySection | null;
    predicted: PredictedSection;
    observed: ObservedSection;
    milestones: Milestone[];
    write: WriteSection;
    empty: string | null;
    caps: { stat: string; value: number | null; source: string }[];
}>();

const { visiting, visitFailed } = useVisitState();

/*
 * The HFW's ancestry picture and before/after table. The run stores one legacy selection
 * (ADR-0010: one json column), so the spark tables list what each parent carries and the screen
 * says that per-parent radios would change what that column holds; this is the documented
 * deviation on the page.
 */
const parentA = computed<LegacyParent | null>(() => props.legacy?.parents[0] ?? null);
const parentB = computed<LegacyParent | null>(() => props.legacy?.parents[1] ?? null);

const grandparent = (parent: LegacyParent | null, index: number): string | null =>
    parent?.ancestors[index] ?? null;

const sparkCarries = (spark: LegacyParent['sparks'][number]): string => {
    if (spark.kind === 'blue') {
        return spark.target === null ? 'Moves a stat' : `Moves ${spark.target}`;
    }

    if (spark.kind === 'pink') {
        return spark.target === null ? 'Lifts an aptitude grade' : `Lifts ${spark.target} aptitude`;
    }

    if (spark.kind === 'green' || spark.kind === 'white') {
        return 'Skill hint';
    }

    return 'Scenario spark';
};

const blueSparks = computed(() =>
    [parentA.value, parentB.value]
        .flatMap((parent) => parent?.sparks ?? [])
        .filter((spark) => spark.kind === 'blue'),
);

const blueStars = computed(() => blueSparks.value.reduce((sum, spark) => sum + (spark.stars ?? 0), 0));

const undoableTurn = computed(() => Math.max(1, (props.write.turns.at(-1)?.turn ?? 0)));

const skip = (): void => {
    router.visit(props.run.cockpit_url);
};


const form = useForm({
    turn: '',
    source_name: '',
    choice_label: '',
    deltas: null as Record<string, unknown> | null,
    origin_note: '',
});

// The turn the entry is recorded on: one past the last logged turn.
const initialTurn = (props.write.turns.at(-1)?.turn ?? 0) + 1;
form.turn = String(initialTurn);

const alert = ref<HTMLElement | null>(null);

watch(
    () => form.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }
        await nextTick();
        alert.value?.focus();
    },
);

const submitEvent = (): void => {
    form.post(props.write.action, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => document.getElementById('inheritance-submit')?.focus());
        },
    });
};

const sparkKindOrder = ['blue', 'pink', 'green', 'white', 'scenario'] as const;

/**
 * The two grandparent slots a parent brings, always two (`inheritance.html:202`).
 *
 * The payload carries `ancestors` as a list of names with nulls for the empty positions, so this pads
 * a short list rather than trusting its length: a parent with no ancestors recorded must still draw
 * both slots and say `slot open`, because the rule provides them and the absence is a state worth
 * seeing, not a row worth omitting.
 */
const ancestorSlots = (parent: LegacyParent): (string | null)[] =>
    [0, 1].map((index) => parent.ancestors[index] ?? null);

const sortedPredictedSparks = computed(() =>
    [...props.predicted.predicted_sparks].sort(
        (a, b) => sparkKindOrder.indexOf(a.kind as typeof sparkKindOrder[number]) -
            sparkKindOrder.indexOf(b.kind as typeof sparkKindOrder[number]),
    ),
);

const sparkFamilyLabels: Record<string, string> = {
    blue: 'Blue',
    pink: 'Pink',
    green: 'Green',
    white: 'White',
    scenario: 'Scenario',
};

/**
 * Five families per parent, always five.
 *
 * `spark_counts` omits a family that holds no Spark (`InheritanceEventController::legacySection`),
 * so reading it alone would silently drop a family from the table. Building the row from
 * `sparkKindOrder` and looking the count up keeps the absent family visible as its own column.
 */
const parentFamilyRows = computed(() =>
    (props.legacy?.parents ?? []).map((parent) => ({
        label: parent.label,
        name: parent.name,
        isGuest: parent.is_guest,
        families: sparkKindOrder.map((kind) => ({
            kind,
            label: sparkFamilyLabels[kind] ?? kind,
            count: parent.spark_counts.find((row) => row.kind === kind)?.count ?? null,
        })),
    })),
);

const statusGlyphClass = (status: Milestone['status']): string => {
    const base = 'inline-flex items-center justify-center w-5 h-5 text-xs font-mono font-bold rounded-full';
    const classes: Record<Milestone['status'], string> = {
        completed: `${base} bg-green text-on-chrome`,
        current: `${base} bg-status-positive text-status-positive-ink`,
        upcoming: `${base} border border-rule text-ink-muted bg-raised`,
        missed: `${base} bg-risk text-on-chrome`,
    };
    return classes[status];
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
        current="inheritance"
    >
        <Head :title="'Inheritance event: ' + props.run.trainee" />
        <template #head>
            <PageHeader
                eyebrow="Inheritance event"
                title="Choose what to inherit"
                description="The inheritance moment happens at career start, Classic and Senior. Pick the spark you want carried from each parent; the run's caps move by the result."
            >
                <template #actions>
                    <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong">Back to cockpit</a>
                </template>
            </PageHeader>
        </template>

        <p v-if="visiting" role="status" class="mb-3 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <form @submit.prevent="submitEvent">
            <section class="card" aria-labelledby="ancestry-heading">
                <h2 id="ancestry-heading">Inheritance</h2>
                <p class="sub">Two parents and two grandparents behind each, per the six-ancestor rule. A repeated ancestor contributes compatibility 0.</p>

                <div class="mt-2 flex flex-col gap-2" role="group" aria-label="Six ancestor slots">
                    <div class="grid gap-2 g-3">
                        <div class="rounded-md border border-dashed border-rule bg-raised px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Grandparent</span>
                            <span class="block text-sm text-ink">{{ grandparent(parentA, 0) ?? 'Not set' }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ grandparent(parentA, 0) === null ? 'slot open' : 'recorded ancestor' }}</span>
                        </div>
                        <div class="rounded-md border border-dashed border-rule bg-raised px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Grandparent</span>
                            <span class="block text-sm text-ink">{{ grandparent(parentA, 1) ?? 'Not set' }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ grandparent(parentA, 1) === null ? 'slot open' : 'recorded ancestor' }}</span>
                        </div>
                        <div class="rounded-md border border-rule bg-panel px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Parent A</span>
                            <span class="block text-sm font-semibold text-ink-strong">{{ parentA?.name ?? 'Not set' }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ parentA === null ? 'slot open' : props.run.scenario_label }}</span>
                        </div>
                        <div class="rounded-md border-2 border-pick-line bg-green-tint px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Trainee</span>
                            <span class="block text-sm font-semibold text-ink-strong">{{ props.run.trainee }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ props.run.scenario_label }}</span>
                        </div>
                    </div>

                    <div class="grid gap-2 g-3">
                        <div class="rounded-md border border-dashed border-rule bg-raised px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Grandparent</span>
                            <span class="block text-sm text-ink">{{ grandparent(parentB, 0) ?? 'Not set' }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ grandparent(parentB, 0) === null ? 'slot open' : 'recorded ancestor' }}</span>
                        </div>
                        <div class="rounded-md border border-dashed border-rule bg-raised px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Grandparent</span>
                            <span class="block text-sm text-ink">{{ grandparent(parentB, 1) ?? 'Not set' }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ grandparent(parentB, 1) === null ? 'slot open' : 'recorded ancestor' }}</span>
                        </div>
                        <div class="rounded-md border border-rule bg-panel px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Parent B</span>
                            <span class="block text-sm font-semibold text-ink-strong">{{ parentB?.name ?? 'Not set' }}</span>
                            <span class="block text-[11px] text-ink-muted">{{ parentB === null ? 'slot open' : props.run.scenario_label }}</span>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-3 py-2">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Compatibility</span>
                            <span class="block text-sm font-semibold text-ink-strong">{{ props.legacy?.affinity ?? 'Not recorded' }}</span>
                            <span class="block text-[11px] text-ink-muted">raises trigger chance and payout</span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-3.5 grid gap-3.5 g-2">
                <section v-for="parent in [parentA, parentB]" :key="parent?.slot ?? 'none'" class="card" :aria-labelledby="'parent-' + (parent?.slot ?? 'none') + '-heading'">
                    <h2 :id="'parent-' + (parent?.slot ?? 'none') + '-heading'">
                        {{ parent === null ? 'Parent' : (parent.slot.endsWith('a') ? 'Parent A · ' : 'Parent B · ') + (parent.name ?? 'Not set') }}
                    </h2>
                    <p class="sub">{{ parent === null ? 'No parent recorded for this slot' : (parent.is_guest ? 'Guest · ' : '') + '★'.repeat(parent.rank ?? 0) + ' · ' + props.run.scenario_label }}</p>

                    <p v-if="parent === null || parent.sparks.length === 0" class="text-sm text-ink">
                        {{ parent === null ? 'No parent is recorded in this slot.' : 'This parent carries no recorded spark.' }}
                    </p>
                    <table v-else class="t mt-1 w-full border-collapse text-sm">
                        <thead>
                            <tr><th class="text-left">Spark</th><th class="text-left">Grade</th><th class="text-left">What it carries</th><th class="text-right"></th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="spark in parent.sparks" :key="spark.kind + '-' + (spark.target ?? '') + '-' + (spark.stars ?? '')" class="border-b border-rule-soft">
                                <td class="py-2 pr-3"><b>{{ spark.kind_label }}{{ spark.target === null ? '' : ' · ' + spark.target }}</b></td>
                                <td class="py-2 pr-3">{{ spark.stars === null ? 'N/A' : '★' + spark.stars }}</td>
                                <td class="py-2 pr-3">{{ sparkCarries(spark) }}</td>
                                <td class="py-2 text-right text-xs text-ink-muted">recorded</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>

            <Note class="mt-2.5">
                <b>One selection per run is what this tool stores.</b> The moment grants one spark per parent, but the
                run carries a single legacy selection, so this screen lists the sparks each parent carries and the
                run's recorded selection rather than promising per-parent radios the column cannot hold.
            </Note>

            <section class="card mt-3.5" aria-labelledby="result-heading">
                <h2 id="result-heading">Result of this pick</h2>
                <table class="t mt-1 w-full border-collapse text-sm">
                    <thead><tr><th class="text-left">Stat</th><th class="text-right">Before</th><th class="text-right">Change</th><th class="text-right">After</th></tr></thead>
                    <tbody>
                        <tr v-for="row in props.caps" :key="row.stat" :class="row.source === 'entered' ? 'is-current bg-green-tint' : ''" class="border-b border-rule-soft">
                            <td class="py-2 pr-3"><b>{{ row.stat }}</b> cap <span class="text-xs text-ink-muted">({{ row.source }})</span></td>
                            <td class="py-2 pr-3 text-right font-mono">{{ row.value ?? 'N/A' }}</td>
                            <td class="py-2 pr-3 text-right font-mono text-ink-muted" title="This tool computes no inheritance change; the cap beside it is the stored figure.">N/A</td>
                            <td class="py-2 text-right font-mono">{{ row.value ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-2.5 grid gap-2.5 g-3">
                    <div class="tile"><div class="k">Blue sparks</div><div class="v font-mono text-sm">{{ blueSparks.length }} recorded · {{ blueStars }}★</div></div>
                    <div class="tile"><div class="k">Aptitude shifts</div><div class="v text-sm text-ink-muted">none recorded this pick</div></div>
                    <div class="tile"><div class="k">Skill hints</div><div class="v text-sm text-ink-muted">none recorded this pick</div></div>
                </div>
                <Note class="mt-2.5">
                    <b>The cap raise is the loop.</b> Two ★3 blue parents raise a cap by 16 each at the start of a
                    career, and this run's ceilings are already above its scenario's published cap where an entered
                    ceiling was recorded; inheritance adds to the entered value, never to the published one.
                </Note>
            </section>

            <div class="savebar">
                <span class="dirty" role="status">
                    {{ props.legacy === null ? 'No legacy selection recorded' : 'Legacy selection recorded' }}
                </span>
                <button type="button" class="btn min-h-11" @click="skip()">Skip this moment</button>
                <button type="submit" class="btn btn-primary min-h-11" :disabled="form.processing">
                    {{ form.processing ? 'Recording…' : 'Record inheritance' }}
                </button>
            </div>

            <!-- HFW OVERRIDE (write fields): recording this moment writes a TurnEvent; the wireframe's
                 savebar is a pick, but the schema holds the event, so the keying fields live here. -->
            <details class="card mt-3.5">
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Record what this moment granted (required to record)</summary>
                <div class="mt-2 grid max-w-3xl grid-cols-2 gap-3 text-sm @4xl:grid-cols-4">
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Turn *</span>
                        <BaseInput v-model="form.turn" type="number" name="turn" min="1" max="72" required :error="form.errors.turn" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Source *</span>
                        <BaseInput v-model="form.source_name" name="source_name" maxlength="100" required :error="form.errors.source_name" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Choice *</span>
                        <BaseInput v-model="form.choice_label" name="choice_label" maxlength="200" required :error="form.errors.choice_label" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Note</span>
                        <BaseInput v-model="form.origin_note" name="origin_note" maxlength="1000" placeholder="Optional" :error="form.errors.origin_note" />
                    </label>
                </div>
            </details>
        </form>

        <details class="card mt-3.5">
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Observed inheritance events ({{ props.observed.events.length }})</summary>
            <p v-if="props.observed.events.length === 0" class="mt-1 text-sm text-ink-muted">No inheritance event has been recorded on this run.</p>
            <ul v-else class="mt-2 flex flex-col gap-2 text-sm">
                <li v-for="event in props.observed.events" :key="event.id" class="rounded-md border border-rule-soft bg-raised px-3 py-2">
                    Turn {{ event.turn }} · {{ event.source_name }} · {{ event.choice_label ?? 'choice not recorded' }}
                </li>
            </ul>
        </details>

        <details class="card mt-3.5">
            <summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">Inheritance milestones</summary>
            <ul class="mt-2 flex flex-col gap-1.5 text-sm">
                <li v-for="milestone in props.milestones" :key="milestone.key" class="flex items-center gap-2">
                    <span aria-hidden="true">{{ milestone.glyph }}</span>
                    <span>{{ milestone.label }}: {{ milestone.status }}</span>
                </li>
            </ul>
        </details>
    </CareerLayout>
</template>
