<script setup lang="ts">
/*
 * The Career Result, `SCR-CAR-018` (SCREEN-019, plan §8 D15). What a finished career adds up to:
 * the final build against its targets, the race history, and the scenario's objectives.
 *
 * **Read-only, and it records nothing.** There is no form here and no write route. It does open the door to
 * Save Veteran (`SCREEN-020`, D16), which is where the one write a finished career gets is entered; this
 * screen reads the career and files nothing itself.
 *
 * **Sections render only for a Completed run.** `Active` and `Retired` are two different absences
 * with two different doors: an active career goes back to the Cockpit, where its next turn is
 * decided; a retired one came here by a status change and was never finished, so the screen says
 * so and offers the Timeline instead — inviting a turn decision would be a door onto a question
 * that no longer exists.
 *
 * Props contract is declared locally: TypeScript 7 ships no `lib/typescript.js` for
 * `@vue/compiler-sfc` to load (plan §11).
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import AptitudeBadge from '../../components/AptitudeBadge.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import { Head, router } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';

interface StatRow {
    key: string;
    label: string;
    current: number | null;
    target: number | null;
    deficit: number | null;
    deficit_title: string | null;
}

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario_label: string;
        run_url: string;
        cockpit_url: string;
        timeline_url: string;
    };
    build: {
        stats: StatRow[];
        target: { purpose_label: string; distance: string; surface: string; style: string } | null;
        skills: {
            learned: { id: number; name: string; name_ja: string | null; turn_acquired: number | null }[];
            not_learned: number;
            empty: string | null;
        };
        aptitudes: { key: string; label: string; letter: string | null }[];
    };
    races: {
        counts: Record<string, number>;
        rows: { id: number; title: string; tier: string | null; placement: string | null; turn: number | null }[];
        empty: string | null;
    };
    scenario: {
        composed: boolean;
        notice: string | null;
        periods: { index: number; name: string; required: number; earned: number | null; unpriced: number }[];
        earned: number | null;
        unpriced: number;
        unassigned: number;
        rewards: string | null;
    };
    save_veteran: { available: boolean; reason: string; url: string };
    ruleset: { value: null; title: string };
    empty: { reason: string; message: string } | null;
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

const statValue = (value: number | null): string => (value === null ? 'N/A' : String(value));

// The one derived figure the slice ships, and it is the advisor's own subtraction. Null is a real
// state here (no target entered, or no turn logged) and never a zero.
const deficitValue = (stat: StatRow): string => (stat.deficit === null ? 'N/A' : String(stat.deficit));

const countOf = (key: string): number => props.races.counts[key] ?? 0;
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Career result: ${props.run.trainee}`" />
        <template #title>Career result</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            That screen did not load. Nothing was saved; try again.
        </p>

        <!-- The two unfinished states. Different absences, different doors: only the Active career
             has a turn left to decide, so only it points at the Cockpit. -->
        <section
            v-if="props.empty !== null"
            aria-labelledby="result-empty-heading"
            class="rounded-md border border-dashed border-rule bg-raised p-4"
        >
            <h2 id="result-empty-heading" class="text-base font-semibold text-ink-strong">
                {{ props.empty.reason === 'retired' ? 'This career was retired' : 'This career is still running' }}
            </h2>
            <p class="mt-1 max-w-3xl text-sm text-ink">{{ props.empty.message }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a
                    v-if="props.empty.reason === 'active'"
                    :href="props.run.cockpit_url"
                    class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome"
                >Back to the Cockpit</a>
                <a
                    :href="props.run.timeline_url"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                >Career Timeline</a>
                <a
                    :href="props.run.run_url"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                >Run record</a>
            </div>
        </section>

        <template v-else>
            <p class="max-w-3xl text-sm text-ink-muted">
                {{ props.run.trainee }}'s finished career, as the run recorded it. Every figure below is a
                stored or entered value, and each deficit is measured against the target you set. The tool
                computes no grade for the build and no score for the career.
            </p>

            <!-- Final build -->
            <section aria-labelledby="result-build-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="result-build-heading" class="text-base font-semibold text-ink-strong">Final build</h2>

                <dl class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <div v-for="stat in props.build.stats" :key="stat.key" class="rounded-md border border-rule bg-raised p-3">
                        <dt class="text-xs text-ink-muted">{{ stat.label }}</dt>
                        <dd class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong">
                            <span :title="stat.current === null ? 'No turn was logged, so this stat has no recorded value.' : null">
                                {{ statValue(stat.current) }}
                            </span>
                        </dd>
                        <p class="mt-1 text-xs text-ink-muted">
                            Target
                            <span
                                class="font-mono tabular-nums"
                                :title="stat.target === null ? 'No build target was set for this run.' : null"
                            >{{ statValue(stat.target) }}</span>
                        </p>
                        <p class="mt-0.5 flex items-baseline gap-1.5 text-xs text-ink-muted">
                            <span>Deficit</span>
                            <span class="font-mono tabular-nums text-ink-strong" :title="stat.deficit_title ?? undefined">
                                {{ deficitValue(stat) }}
                            </span>
                            <ProvenanceBadge v-if="stat.deficit !== null" state="calculated" />
                        </p>
                    </div>
                </dl>

                <p v-if="props.build.target" class="mt-3 text-sm text-ink">
                    <span class="text-ink-muted">Target:</span>
                    {{ props.build.target.purpose_label }},
                    {{ props.build.target.distance }} {{ props.build.target.surface }},
                    {{ props.build.target.style }}.
                </p>
                <p v-else class="mt-3 text-sm text-ink-muted">
                    <span title="No build target was set for this run.">N/A</span>.
                    No build target was set for this run, so the stats above are read against no target.
                </p>

                <h3 class="mt-4 text-sm font-semibold text-ink-strong">Skills learned</h3>
                <p v-if="props.build.skills.empty !== null" class="mt-1 text-sm text-ink-muted">
                    {{ props.build.skills.empty }}
                </p>
                <ul v-else class="mt-2 flex flex-wrap gap-2">
                    <li
                        v-for="skill in props.build.skills.learned"
                        :key="skill.id"
                        class="inline-flex items-baseline gap-2 rounded-md border border-rule bg-raised px-2 py-1 text-sm"
                    >
                        <span class="text-ink-strong">{{ skill.name }}</span>
                        <span v-if="skill.turn_acquired !== null" class="font-mono text-xs tabular-nums text-ink-muted">
                            Turn {{ skill.turn_acquired }}
                        </span>
                    </li>
                </ul>
                <p class="mt-2 text-xs text-ink-muted">
                    Skills on the run's list that it did not learn: {{ props.build.skills.not_learned }}.
                </p>

                <h3 class="mt-4 text-sm font-semibold text-ink-strong">Aptitudes</h3>
                <p class="mt-1 text-xs text-ink-muted">
                    These belong to {{ props.run.trainee }} and did not change during the career; no column
                    records an aptitude being altered.
                </p>
                <p v-if="props.build.aptitudes.length === 0" class="mt-2 text-sm text-ink-muted">
                    <span title="No aptitude letters have been published for this trainee.">N/A</span>.
                    No aptitude letters are published for this trainee.
                </p>
                <ul v-else class="mt-2 flex flex-wrap gap-2">
                    <li v-for="axis in props.build.aptitudes" :key="axis.key">
                        <AptitudeBadge :label="axis.label" :letter="axis.letter" />
                    </li>
                </ul>
            </section>

            <!-- Race history -->
            <section aria-labelledby="result-races-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="result-races-heading" class="text-base font-semibold text-ink-strong">Race history</h2>

                <dl class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div class="rounded-md border border-rule bg-raised p-3">
                        <dt class="text-xs text-ink-muted">Races completed</dt>
                        <dd class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong">{{ countOf('completed') }}</dd>
                    </div>
                    <div class="rounded-md border border-rule bg-raised p-3">
                        <dt class="text-xs text-ink-muted">Wins</dt>
                        <dd class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong">{{ countOf('wins') }}</dd>
                    </div>
                    <div class="rounded-md border border-rule bg-raised p-3">
                        <dt class="text-xs text-ink-muted">Placings below first</dt>
                        <dd class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong">{{ countOf('below_first') }}</dd>
                    </div>
                    <div class="rounded-md border border-rule bg-raised p-3">
                        <dt class="text-xs text-ink-muted">Finishes not recorded</dt>
                        <dd
                            class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong"
                            :title="countOf('placement_unrecorded') > 0 ? 'These races are recorded as completed, but no placing was entered for them, so they are counted as neither a win nor a placing.' : undefined"
                        >{{ countOf('placement_unrecorded') }}</dd>
                    </div>
                    <div class="rounded-md border border-rule bg-raised p-3">
                        <dt class="text-xs text-ink-muted">G1 wins</dt>
                        <dd class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong">{{ countOf('g1_wins') }}</dd>
                    </div>
                </dl>

                <p v-if="props.races.empty !== null" class="mt-3 text-sm text-ink-muted">{{ props.races.empty }}</p>
                <div v-else class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="Completed races">
                    <table class="w-full border-collapse text-sm">
                        <caption class="sr-only">Every race this career is recorded as having completed</caption>
                        <thead>
                            <tr class="border-b border-rule text-left text-ink-muted">
                                <th scope="col" class="py-2 pr-3 font-medium">Race</th>
                                <th scope="col" class="py-2 pr-3 font-medium">Tier</th>
                                <th scope="col" class="py-2 pr-3 font-medium">Finish</th>
                                <th scope="col" class="py-2 font-medium">Turn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="race in props.races.rows" :key="race.id" class="border-b border-rule last:border-0">
                                <td class="py-2 pr-3 text-ink-strong">{{ race.title }}</td>
                                <td class="py-2 pr-3 text-ink-muted">{{ race.tier ?? 'N/A' }}</td>
                                <td class="py-2 pr-3" :title="race.placement === null ? 'No placing was entered for this race.' : undefined">
                                    <span v-if="race.placement !== null" class="text-ink">{{ race.placement }}</span>
                                    <span v-else class="text-ink-muted">N/A</span>
                                </td>
                                <td class="py-2 text-ink-muted">
                                    <span v-if="race.turn !== null" class="font-mono tabular-nums">{{ race.turn }}</span>
                                    <span v-else title="This race was not tied to a logged turn (KI-17).">N/A</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Scenario result -->
            <section aria-labelledby="result-scenario-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="result-scenario-heading" class="text-base font-semibold text-ink-strong">Scenario result</h2>

                <p v-if="props.scenario.notice !== null" class="mt-1 text-sm text-ink-muted">{{ props.scenario.notice }}</p>

                <template v-else>
                    <dl class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="period in props.scenario.periods" :key="period.index" class="rounded-md border border-rule bg-raised p-3">
                            <dt class="text-xs text-ink-muted">{{ period.name }}</dt>
                            <dd class="mt-0.5 font-mono text-lg tabular-nums text-ink-strong">
                                <span
                                    :title="period.earned === null ? 'No priced finish is recorded in this period, so it has no total.' : null"
                                >{{ period.earned === null ? 'N/A' : period.earned }}</span>
                                <span class="text-sm font-normal text-ink-muted"> / {{ period.required }} GP</span>
                            </dd>
                            <p v-if="period.unpriced > 0" class="mt-1 text-xs text-warning" title="This period holds a finish below first, and no source prices one, so the period's total is withheld rather than understated.">
                                {{ period.unpriced }} finish{{ period.unpriced === 1 ? '' : 'es' }} unpriced
                            </p>
                        </div>
                    </dl>

                    <p v-if="props.scenario.unassigned > 0" class="mt-3 text-xs text-warning" title="These races were completed before a period was chosen for them, so no period's total counts them.">
                        {{ props.scenario.unassigned }} completed race{{ props.scenario.unassigned === 1 ? '' : 's' }}
                        {{ props.scenario.unassigned === 1 ? 'is' : 'are' }} not assigned to a Grade Point period.
                    </p>
                </template>

                <p class="mt-3 text-sm text-ink-muted">
                    <span title="No column on any table this tool reads holds a race reward or a Skill Point payout.">N/A</span>.
                    {{ props.scenario.rewards }}
                </p>
            </section>

            <!-- The doors. Save Veteran belongs to D16 and is named rather than linked. -->
            <section aria-labelledby="result-actions-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="result-actions-heading" class="text-base font-semibold text-ink-strong">What you can do next</h2>

                <div class="mt-3 flex flex-wrap gap-2">
                    <a
                        :href="props.run.timeline_url"
                        class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome"
                    >View Career Timeline</a>
                    <a
                        :href="props.run.run_url"
                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised"
                    >Run record</a>
                </div>

                <!-- The door to `SCREEN-020`. It is a link only when the career actually finished: for an
                     Active or Retired run the reason is the useful half of the line, and a link there would
                     open a screen whose save button can only refuse. -->
                <div v-if="props.save_veteran.available" class="mt-3 flex flex-wrap items-center gap-3">
                    <a
                        :href="props.save_veteran.url"
                        class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-semibold text-on-chrome"
                    >Save Veteran</a>
                    <p class="text-sm text-ink-muted">{{ props.save_veteran.reason }}</p>
                </div>
                <p v-else class="mt-3 text-sm text-ink-muted">
                    <span title="A career is filed once it is Completed.">Not available yet</span>.
                    {{ props.save_veteran.reason }}
                </p>
            </section>

            <p class="mt-4 text-xs text-ink-muted">
                Ruleset:
                <span :title="props.ruleset.title">N/A</span>.
                {{ props.ruleset.title }}
            </p>
        </template>

        <div v-if="props.empty === null" class="mt-4 max-w-sm">
            <a :href="props.run.cockpit_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">
                Back to Cockpit
            </a>
        </div>
    </CareerLayout>
</template>
