<script setup lang="ts">
/*
 * SCREEN-001, the 2.0 landing screen. The page pattern is `Catalog/Index.vue`'s: `AppLayout`,
 * `<Head title>`, the `#title` slot, and Inertia `Link` for the routes `spa.ts` resolves. There is
 * no write here, so there is no `useForm`.
 *
 * Three panels the brief's mockup names are deliberately not data panels, and the page says so in
 * words rather than leaving them blank:
 *
 *  - **Recent builds** has no source. A build is the Career Plan a run holds (`build_target`, C1) and
 *    the wizard that enters one is D2–D7, so listing runs under the word "Builds" would label a run
 *    as something it is not.
 *  - **Legacy-goal gaps** is omitted outright. A gap is the run's targets set against inherited
 *    stats, which is the inheritance computation `ADR-0020` §3 bans; C1 and C3 give no honest
 *    comparison, and the omission is reported on the slice rather than rendered as a guess.
 *  - **A scenario resource value** renders `N/A` with its reason, because no column records one. The
 *    run page's resource strip renders it the same way.
 *
 * Loading and error are page-level because this page's only user-initiated async action is following
 * one of its links (ADR-0007): Inertia keeps the current page on screen during a visit, so the state
 * is announced rather than drawn as a skeleton. `invalid` and `exception` are both cancelable, and
 * returning `false` takes the failure out of Inertia's default modal so this page's own `role="alert"`
 * is the single surface. Both listeners are removed on unmount, so the suppression is scoped to
 * visits started from here.
 */
import AppLayout from '../layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onUnmounted, ref } from 'vue';

/*
 * The props contract, declared here rather than imported from `resources/js/types.ts`. It cannot be
 * imported: `defineProps<Imported>()` needs `@vue/compiler-sfc` to read the type's source file, which
 * it does through `ts.sys`, and TypeScript 7 ships no `lib/typescript.js` to load (the same TS 7 gap
 * plan §11 records for `vue-tsc`). The build fails outright with "No fs option provided to
 * compileScript in non-Node environment" if it is tried. Every other page declares its props locally
 * for the same reason, so this is the page pattern and not a second one.
 *
 * Every nullable field is a named absence the page renders as `N/A` with a `title`, never a default
 * (AGENTS.md §5): `position_label` is null for a run with no logged turn, `energy` is null when no
 * turn recorded it, and `resource.value` is null because no column holds a scenario resource yet.
 */
interface ActiveCareer {
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    /** The matrix label from `config/scenarios.php`, or "No scenario set". Never a storage key. */
    scenario_label: string;
    /** e.g. "Senior Year · Early July", on the client's 24-turn grid. */
    position_label: string | null;
    /** Logged turns, the same reading the run page's resource strip takes. */
    turn: number;
    energy: number | null;
    resource: { label: string; value: number | null; value_hint: string } | null;
    resume_url: string;
}

interface VeteranRow {
    id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    run_id: number;
    run_url: string;
}

const props = defineProps<{
    activeCareer: ActiveCareer | null;
    recentVeterans: { rows: VeteranRow[]; total: number; limit: number; library_url: string };
    quickActions: { label: string; to: string }[];
    counts: { trainees: number; skills: number; supportCards: number };
    dataStatus: { label: string; state: string; verified_at: string | null };
}>();

// The ruleset is a shared prop, not a second dashboard prop: `HandleInertiaRequests` already ships it
// to every page, and it is null, so the page prints `N/A` with the reason rather than a copy of the
// absence (design-2.0 §48).
const page = usePage();
const ruleset = computed(() => page.props.app?.ruleset ?? null);

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

const group = (n: number): string => n.toLocaleString('en-US');
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <template #title>Dashboard</template>

        <div class="space-y-6">
            <p v-if="visiting" role="status" class="text-sm text-ink-muted">Loading…</p>

            <p
                v-if="visitFailed"
                role="alert"
                class="rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
            >
                That page could not be loaded. Try again, or open it from the navigation.
            </p>

            <!-- ACTIVE CAREER. Level 1 of the visual hierarchy (design-2.0 §4): the one thing a
                 returning Trainer came for, and the only accented action on the page. -->
            <section
                v-if="props.activeCareer"
                aria-labelledby="active-career-heading"
                class="rounded-md border border-rule bg-panel p-5"
            >
                <h2 id="active-career-heading" class="text-xs font-bold tracking-wide text-ink-muted">
                    ACTIVE CAREER
                </h2>

                <p class="mt-2 text-lg font-semibold text-ink-strong">
                    {{ props.activeCareer.trainee }}
                    <span
                        v-if="props.activeCareer.trainee_ja"
                        lang="ja"
                        class="ml-2 text-sm font-normal text-ink-muted"
                    >{{ props.activeCareer.trainee_ja }}</span>
                </p>

                <p class="mt-1 text-sm text-ink-muted">
                    <span>{{ props.activeCareer.scenario_label }}</span>
                    <span aria-hidden="true"> · </span>
                    <span
                        v-if="props.activeCareer.position_label"
                    >{{ props.activeCareer.position_label }}</span>
                    <!-- A run with nothing logged has no position to claim, so it says so rather
                         than pointing at Early January (D-220). -->
                    <span
                        v-else
                        title="No turn has been logged, so this career is not standing anywhere on the calendar yet."
                    >N/A</span>
                </p>

                <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-ink-muted">Turn</dt>
                        <dd class="font-mono text-xl tabular-nums text-ink-strong">
                            {{ group(props.activeCareer.turn) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Energy</dt>
                        <dd
                            v-if="props.activeCareer.energy !== null"
                            class="font-mono text-xl tabular-nums text-ink-strong"
                        >
                            {{ group(props.activeCareer.energy) }}
                        </dd>
                        <dd
                            v-else
                            class="font-mono text-xl tabular-nums text-ink-muted"
                            title="No logged turn has recorded Energy for this career."
                        >
                            N/A
                        </dd>
                    </div>
                    <div v-if="props.activeCareer.resource">
                        <dt class="text-xs text-ink-muted">{{ props.activeCareer.resource.label }}</dt>
                        <dd
                            v-if="props.activeCareer.resource.value !== null"
                            class="font-mono text-xl tabular-nums text-ink-strong"
                        >
                            {{ group(props.activeCareer.resource.value) }}
                        </dd>
                        <dd
                            v-else
                            class="font-mono text-xl tabular-nums text-ink-muted"
                            :title="props.activeCareer.resource.value_hint"
                        >
                            N/A
                        </dd>
                    </div>
                </dl>

                <Link
                    :href="props.activeCareer.resume_url"
                    class="enamel mt-4 inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
                >
                    Resume Career
                </Link>
            </section>

            <!-- The empty state design-2.0 §29 requires: what is missing, why it matters, what to do. -->
            <section
                v-else
                aria-labelledby="active-career-empty-heading"
                class="rounded-md border border-dashed border-rule bg-raised p-6"
            >
                <h2 id="active-career-empty-heading" class="text-xs font-bold tracking-wide text-ink-muted">
                    ACTIVE CAREER
                </h2>
                <p class="mt-2 text-sm text-ink">No active career. Start a new training run.</p>
                <p class="mt-2 text-sm text-ink-muted">
                    A career keeps your turns, Energy and race record in one place, and this screen
                    resumes it from wherever you left off. Creating one takes a trainee and a scenario,
                    and nothing here changes until you log your first turn.
                </p>
                <Link
                    href="/training-runs/create"
                    class="enamel mt-4 inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome"
                >
                    Start a new training run
                </Link>
            </section>

            <!-- Quick actions. Every destination is a live route; a destination whose slice had not
                 landed would be a named absence here rather than a dead link. -->
            <nav aria-label="Quick actions">
                <ul class="flex flex-wrap gap-3">
                    <li v-for="action in props.quickActions" :key="action.to">
                        <Link
                            :href="action.to"
                            class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:text-ink-strong"
                        >
                            {{ action.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- RECENT VETERANS -->
            <section aria-labelledby="recent-veterans-heading" class="rounded-md border border-rule bg-panel p-5">
                <h2 id="recent-veterans-heading" class="text-xs font-bold tracking-wide text-ink-muted">
                    RECENT VETERANS
                </h2>

                <template v-if="props.recentVeterans.total === 0">
                    <p class="mt-2 text-sm text-ink">No Veterans recorded yet.</p>
                    <p class="mt-2 text-sm text-ink-muted">
                        A Veteran is a completed run you have filed, and the library is where a finished
                        career becomes something the next one can inherit from. Finish a run, set its
                        status to Completed, and save it from the Legacy Lab.
                    </p>
                    <Link
                        :href="props.recentVeterans.library_url"
                        class="mt-4 inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-semibold text-ink hover:text-ink-strong"
                    >
                        Open the Legacy Lab
                    </Link>
                </template>

                <template v-else>
                    <ul class="mt-2 divide-y divide-rule">
                        <li
                            v-for="veteran in props.recentVeterans.rows"
                            :key="veteran.id"
                            class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-2"
                        >
                            <Link :href="veteran.run_url" class="text-sm font-semibold text-ink-strong hover:underline">
                                {{ veteran.trainee }}
                                <span
                                    v-if="veteran.trainee_ja"
                                    lang="ja"
                                    class="ml-2 text-sm font-normal text-ink-muted"
                                >{{ veteran.trainee_ja }}</span>
                            </Link>
                            <span class="text-xs text-ink-muted">{{ veteran.scenario_label }}</span>
                        </li>
                    </ul>

                    <p class="mt-3 text-xs text-ink-muted">
                        Showing the {{ props.recentVeterans.rows.length }} most recent of
                        {{ group(props.recentVeterans.total) }}.
                        <Link :href="props.recentVeterans.library_url" class="underline hover:text-ink-strong">
                            Open the Legacy Lab
                        </Link>
                    </p>
                </template>
            </section>

            <!-- RECENT BUILDS: a named absence, not a dead panel. There is no build record to list
                 until the Career Plan wizard (D2–D7) lands. -->
            <section aria-labelledby="recent-builds-heading" class="rounded-md border border-dashed border-rule bg-raised p-6">
                <h2 id="recent-builds-heading" class="text-xs font-bold tracking-wide text-ink-muted">
                    RECENT BUILDS
                </h2>
                <p class="mt-2 text-sm text-ink">No builds recorded yet.</p>
                <p class="mt-2 text-sm text-ink-muted">
                    A build is the Career Plan you set before a run: its purpose, distance, surface,
                    style and stat targets. Nothing records one yet, so a run listed here would be a
                    run wearing the wrong name. The plan wizard arrives with Trainer Desk 2.0.
                </p>
            </section>

            <!-- DATA STATUS. Level 4 metadata (design-2.0 §4, §48): what data this tool holds, which
                 ruleset it holds it under, and when it was last checked against its sources. The
                 glyph is decorative; the word "Current" carries the state, so nothing here is shown
                 through colour alone. -->
            <section aria-labelledby="data-status-heading" class="rounded-md border border-rule bg-panel p-5">
                <h2 id="data-status-heading" class="text-xs font-bold tracking-wide text-ink-muted">
                    DATA STATUS
                </h2>

                <p class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm">
                    <span class="font-semibold text-ink-strong">{{ props.dataStatus.label }}</span>
                    <span aria-hidden="true" class="text-green-deep">●</span>
                    <span class="text-ink">{{ props.dataStatus.state }}</span>
                    <span v-if="props.dataStatus.verified_at" class="text-xs text-ink-muted">
                        Verified {{ props.dataStatus.verified_at }}
                    </span>
                    <span
                        v-else
                        class="text-xs text-ink-muted"
                        title="No verification date is recorded for the scenario matrix."
                    >Verified N/A</span>
                </p>

                <p class="mt-2 text-xs text-ink-muted">
                    Ruleset:
                    <span
                        v-if="ruleset"
                        class="font-mono text-ink"
                    >{{ ruleset }}</span>
                    <!-- app.ruleset is null: no source defines a Global ruleset version, so the
                         screen names the absence instead of inventing one (design-2.0 §48). -->
                    <span
                        v-else
                        class="font-mono text-ink"
                        title="No source defines a Global ruleset version, so this tool states none."
                    >N/A</span>
                </p>

                <dl class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-ink-muted">Trainees</dt>
                        <dd class="font-mono text-2xl tabular-nums text-ink-strong">
                            {{ group(props.counts.trainees) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Skills</dt>
                        <dd class="font-mono text-2xl tabular-nums text-ink-strong">
                            {{ group(props.counts.skills) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Support cards</dt>
                        <dd class="font-mono text-2xl tabular-nums text-ink-strong">
                            {{ group(props.counts.supportCards) }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    </AppLayout>
</template>
