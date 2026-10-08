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
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import SparkChip from '../../components/legacy/SparkChip.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

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
    ancestors: { slot: string; name: string | null }[];
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
}>();

const visiting = ref(false);
const visitFailed = ref(false);

const stopLoading = router.on('start', () => {
    visiting.value = true;
    visitFailed.value = false;
});
const stopLoaded = router.on('finish', () => {
    visiting.value = false;
});
const stopFailed = router.on('exception', () => {
    visiting.value = false;
    visitFailed.value = true;
    return false;
});
const stopInvalid = router.on('invalid', () => {
    visiting.value = false;
    visitFailed.value = true;
    return false;
});

onUnmounted(() => {
    stopLoading();
    stopLoaded();
    stopFailed();
    stopInvalid();
});

const form = useForm({
    turn: '',
    source_name: '',
    choice_label: '',
    deltas: null as Record<string, unknown> | null,
    origin_note: '',
});

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

const sortedPredictedSparks = computed(() =>
    [...props.predicted.predicted_sparks].sort(
        (a, b) => sparkKindOrder.indexOf(a.kind as typeof sparkKindOrder[number]) -
            sparkKindOrder.indexOf(b.kind as typeof sparkKindOrder[number]),
    ),
);

const statusGlyphClass = (status: Milestone['status']): string => {
    const base = 'inline-flex items-center justify-center w-5 h-5 text-xs font-mono font-bold rounded-full';
    const classes: Record<Milestone['status'], string> = {
        completed: `${base} bg-green text-on-chrome`,
        current: `${base} bg-chrome text-on-chrome animate-pulse`,
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
    >
        <Head :title="`Inheritance: ${props.run.trainee}`" />
        <template #title>Inheritance</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <p class="max-w-3xl text-sm text-ink-muted">
            Track the three inheritance moments in this career. The Predicted section shows the Sparks
            carried by the chosen parents and grandparents. The Observed section records what actually
            fired in the game. No inheritance outcome is computed - this tool does not derive a result
            (ADR-0020 §3).
        </p>

        <!-- Empty state when no legacy selection exists -->
        <section
            v-if="props.empty"
            aria-labelledby="inheritance-empty-heading"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-4"
        >
            <h2 id="inheritance-empty-heading" class="text-base font-semibold text-ink-strong">
                No Legacy configuration yet
            </h2>
            <p class="mt-2 text-sm text-ink">{{ props.empty }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">
                    Back to Cockpit
                </a>
                <a href="/career/setup/legacy" class="inline-flex min-h-11 items-center rounded-md bg-chrome px-3 font-semibold text-on-chrome">
                    Enter Legacy Now
                </a>
            </div>
        </section>

        <!-- Main content when legacy configuration exists -->
        <div v-else>
            <!-- Milestone Timeline -->
            <section
                aria-labelledby="inheritance-milestones-heading"
                class="mt-4 rounded-md border border-rule bg-panel p-4"
            >
                <h2 id="inheritance-milestones-heading" class="text-base font-semibold text-ink-strong">
                    Inheritance Milestones
                </h2>
                <p class="mt-1 text-sm text-ink-muted">
                    Three fixed moments (REFERENCE §1.5.1): career start, Classic Early April, Senior Early April.
                    Glyphs: ● completed · ◉ current · ○ upcoming · × missed
                </p>

                <ol class="mt-3 space-y-2" role="list" aria-label="Inheritance milestones">
                    <li
                        v-for="milestone in props.milestones"
                        :key="milestone.key"
                        class="flex items-center gap-3 rounded-md border border-rule bg-raised p-3"
                    >
                        <span
                            :class="statusGlyphClass(milestone.status)"
                            :title="milestone.title"
                            :aria-label="`${milestone.label}, ${milestone.status}`"
                        >
                            {{ milestone.glyph }}
                        </span>
                        <span class="text-sm font-medium text-ink-strong">{{ milestone.label }}</span>
                        <span class="text-xs text-ink-muted">{{ milestone.status }}</span>
                    </li>
                </ol>
            </section>

            <!-- Predicted Section -->
            <section
                aria-labelledby="inheritance-predicted-heading"
                class="mt-4 rounded-md border border-rule bg-panel p-4"
            >
            <header class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="inheritance-predicted-heading" class="text-base font-semibold text-ink-strong">
                    Predicted Sparks
                </h2>
                <ProvenanceBadge
                    state="estimated"
                    title="Sourced probabilities from the chosen parents and grandparents. No outcome is computed."
                />
            </header>

            <p class="mt-2 text-sm text-ink-muted">
                Parent and grandparent Sparks from the Legacy configuration. Counts are what the Trainer
                entered; star ratings are roll probabilities, not guarantees.
            </p>

            <!-- Aggregated predicted sparks by kind -->
            <div v-if="sortedPredictedSparks.length > 0" class="mt-3 space-y-2">
                <div
                    v-for="spark in sortedPredictedSparks"
                    :key="spark.kind"
                    class="flex flex-wrap items-center gap-2 rounded-md border border-rule bg-raised p-2"
                >
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        {{ spark.kind_label }}
                    </span>
                    <span class="text-sm font-medium text-ink-strong">{{ spark.total_count }} Spark{{ spark.total_count > 1 ? 's' : '' }}</span>
                    <span class="flex-1"></span>
                    <span class="text-xs text-ink-muted">Total across both parents and grandparents</span>
                </div>
            </div>
            <p v-else class="mt-3 text-sm text-ink-muted" title="No Legacy configuration recorded for this run.">
                No Sparks recorded in the Legacy configuration.
            </p>

            <!-- Expected Inheritance (NOT computed) -->
            <div class="mt-4 rounded-md border border-rule bg-sunken p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Expected Inheritance</p>
                <p class="mt-1 flex items-center gap-2">
                    <span
                        :title="props.predicted.expected_inheritance.title"
                        class="font-mono text-ink"
                    >
                        {{ props.predicted.expected_inheritance.label }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-pick px-1.5 py-0.5 text-[0.6rem] font-semibold text-on-pick"
                        title="Sourced probabilities only. No computed total."
                    >
                        Not computed
                    </span>
                </p>
                <p class="mt-1 text-xs text-ink-muted">
                    Inheritance outcome computation is banned (ADR-0020 §3). This tool records what the
                    Trainer enters; it does not derive an outcome.
                </p>
            </div>

            <!-- Sourced Star-Roll Odds Table (REFERENCE §1.5.3) -->
            <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                    Sourced Star-Roll Probabilities (Estimated)
                </p>
                <p class="mt-1 text-xs text-ink-muted">
                    From UMAMUSUME_REFERENCE.md §1.5.3 (Game8 guide 2026-08-24). Probabilities, not guarantees.
                    Shown as Estimated; never summed into a single number.
                </p>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-sm text-ink" role="table">
                        <thead>
                            <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                <th class="p-2">Final Stat Value</th>
                                <th class="p-2 text-center">1 Star</th>
                                <th class="p-2 text-center">2 Stars</th>
                                <th class="p-2 text-center">3 Stars</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in props.predicted.star_roll_table"
                                :key="row.stat_range"
                                class="border-b border-rule"
                            >
                                <td class="p-2 font-medium">{{ row.stat_range }}</td>
                                <td class="p-2 text-center tabular-nums">{{ row.one_star }}</td>
                                <td class="p-2 text-center tabular-nums">{{ row.two_star }}</td>
                                <td class="p-2 text-center tabular-nums">{{ row.three_star }}</td>
                            </tr>
                        </tbody>
</table>
            </div>
        </div>
        </section>

        <!-- Observed Section -->
        <section
            aria-labelledby="inheritance-observed-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <header class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="inheritance-observed-heading" class="text-base font-semibold text-ink-strong">
                    Observed Outcomes
                </h2>
                <ProvenanceBadge
                    state="confirmed"
                    title="Entered by the Trainer. Always Confirmed because the Trainer recorded it."
                />
            </header>

            <p class="mt-2 text-sm text-ink-muted">
                What the Trainer actually saw in the game: which Sparks activated at each inheritance
                moment. Each entry is a Confirmed record.
            </p>

            <!-- Recorded events list -->
            <div v-if="props.observed.events.length > 0" class="mt-3 space-y-2">
                <div
                    v-for="event in props.observed.events"
                    :key="event.id"
                    class="rounded-md border border-rule bg-raised p-3"
                >
                    <div class="flex flex-wrap items-start gap-2">
                        <span class="inline-flex min-w-[7rem] shrink-0 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            Turn {{ event.turn }}
                        </span>
                        <span class="inline-flex min-w-[10rem] shrink-0 text-sm font-medium text-ink-strong">
                            {{ event.source_name }}
                        </span>
                        <span v-if="event.choice_label" class="text-sm text-ink">
                            {{ event.choice_label }}
                        </span>
                        <span v-else class="text-sm text-ink-muted">No detail recorded</span>
                        <span class="flex-1"></span>
                        <ProvenanceBadge state="confirmed" title="Entered by the Trainer." />
                    </div>
                    <p v-if="event.origin_note" class="mt-1 text-xs text-ink-muted">{{ event.origin_note }}</p>
                    <p v-if="event.deltas" class="mt-1 text-xs font-mono text-ink-muted">
                        Deltas: {{ JSON.stringify(event.deltas) }}
                    </p>
                </div>
            </div>
            <p v-else class="mt-3 text-sm text-ink-muted">
                {{ props.observed.provenance }}
            </p>

            <!-- Record new observed event form -->
            <form
                v-if="props.write.turns.length > 0"
                class="mt-4 rounded-md border border-rule bg-raised p-3"
                @submit.prevent="submitEvent"
            >
                <h3 class="text-sm font-semibold text-ink-strong">Record an inheritance event</h3>
                <p class="mt-1 text-xs text-ink-muted">
                    Enter what you saw in the client. This creates a Confirmed TurnEvent of type Inheritance.
                </p>

                <div
                    v-if="form.hasErrors"
                    ref="alert"
                    role="alert"
                    tabindex="-1"
                    class="mt-2 rounded-md border border-risk bg-raised p-2 text-sm text-risk"
                >
                    <p class="font-semibold">The event was not recorded.</p>
                    <ul class="mt-1 list-disc pl-5">
                        <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                    </ul>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs text-ink-muted">Turn *</span>
                        <select
                            v-model="form.turn"
                            name="turn"
                            required
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                        >
                            <option value="">Select turn</option>
                            <option
                                v-for="turn in props.write.turns"
                                :key="turn.id"
                                :value="turn.turn"
                            >
                                Turn {{ turn.turn }}
                            </option>
                        </select>
                        <p v-if="form.errors.turn" class="text-xs text-risk">{{ form.errors.turn }}</p>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-xs text-ink-muted">Source *</span>
                        <select
                            v-model="form.source_name"
                            name="source_name"
                            required
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                        >
                            <option value="">Select source</option>
                            <option
                                v-for="source in props.write.sources"
                                :key="source"
                                :value="source"
                            >
                                {{ source }}
                            </option>
                        </select>
                        <p v-if="form.errors.source_name" class="text-xs text-risk">{{ form.errors.source_name }}</p>
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-xs text-ink-muted">Detail</span>
                        <input
                            v-model="form.choice_label"
                            type="text"
                            name="choice_label"
                            placeholder="e.g., Blue Speed ★★★, Pink Mile ★★"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink"
                        />
                    </label>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs text-ink-muted">Notes</span>
                        <textarea
                            v-model="form.origin_note"
                            name="origin_note"
                            rows="2"
                            placeholder="Any additional context…"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 text-sm text-ink resize-none"
                        />
                    </label>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button
                        id="inheritance-submit"
                        type="submit"
                        class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome disabled:opacity-60 disabled:cursor-wait"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Recording…' : 'Record Event' }}
                    </button>
                    <p v-if="form.processing" role="status" class="text-sm text-ink-muted">
                        Recording the inheritance event…
                    </p>
                </div>
            </form>
            <p v-else class="mt-3 text-sm text-ink-muted">
                No turns logged yet. Record at least one turn on the run screen before adding an
                inheritance event.
            </p>
        </section>

        <div class="mt-4 max-w-sm">
            <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">
                Back to Cockpit
            </a>
        </div>
    </div>
    </CareerLayout>
</template>