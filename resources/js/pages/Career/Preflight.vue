<script setup lang="ts">
/*
 * The career setup wizard's last step, `SCR-CAR-010` (SCREEN-008, PRD FR-A-5, `ADR-0020` §1 and §3).
 * Step 6 of six, and the only step that writes.
 *
 * **Nothing is re-asked.** Every fact on this page is read from the draft the five earlier steps filled and
 * arrives as one `contract` prop, so Back and Edit round trips are lossless by construction: a step opens
 * on the values it stored, not on what the client still held.
 *
 * **The page is a checklist, not a report.** One row per entered step, each carrying its own state glyph
 * (entered or missing), the step's name, a one-line summary of what the draft holds or `Not entered`, and
 * the one Edit door that returns to that step. The earlier revision printed the whole contract again as
 * four detail blocks and repeated every door in a second row under them; the summary is what a Trainer
 * reads at the commitment screen, and the detail lives one click away on the step that owns it.
 *
 * **`Start career` is the page's only primary, and it is honest about what it needs.** A career needs a
 * scenario and a trainee, which is exactly what `StartCareerRequest` refuses without, so the button is
 * natively `disabled` until both are entered, and the reason is printed beside it whether or not it is
 * pressed.
 *
 * *Dated correction 2026-10-22 (UX audit F-05): the paragraph above used to say the button carries
 * `aria-disabled` and a click guard "rather than the `disabled` attribute", so that the plan's "focus
 * the first missing row on an attempted click" stayed reachable. `DESIGN.md` §5 (b)'s 2026-10-17
 * amendment names that shape as the defect — an enabled control whose label promises what the product
 * cannot perform — and rules native `disabled` or a named absence. The focus-on-refusal path is
 * removed with it: a disabled control cannot be pressed, so there is no attempted click to answer, and
 * the reason and the missing rows are already on screen before the press. The button is truly disabled
 * while the write is in flight as well, as before.*
 *
 * **The primary action creates the run.** The write re-runs every step's own Form Request server-side
 * before it creates anything, so this page's rendering is never the trust boundary. While the write is in
 * flight the button says so and is truly disabled, because it is a user-initiated action and its own
 * progress is the only loading state this screen has (ADR-0007).
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

interface Trainee {
    id: number;
    name: string;
    name_ja: string | null;
}

interface Scenario {
    key: string;
    label: string;
    focus: string | null;
}

interface Target {
    purpose: string;
    purpose_label: string;
    distance: string;
    surface: string;
    style: string;
    targets: Record<string, number>;
}

interface Spark {
    kind: string;
    kind_label: string | null;
    target: string | null;
    stars: number | null;
}

interface Member {
    slot: string;
    label: string;
    name: string | null;
    rank: number | null;
    rank_letter: string | null;
    rank_label: string;
    is_guest: boolean;
    ancestors: { slot: string; name: string | null; rank: number | null; rank_letter: string | null; rank_label: string; sparks: Spark[]; sparks_label: string }[];
    sparks: Spark[];
    probability: { value: null; title: string };
}

interface Card {
    id: number;
    name: string;
    type: string;
    type_label: string;
    rarity_word: string;
    artworkURL: string | null;
    effects: { effect_id: number; name: string | null; display: string }[];
}

interface Slot {
    position: number;
    label: string;
    is_friend: boolean;
    ownership: 'OWNED' | 'RENTED' | null;
    card: Card | null;
}

interface Warning {
    key: string;
    title: string;
    detail: string;
    href: string;
}

const props = defineProps<{
    step: number;
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    contract: {
        complete: boolean;
        build: {
            trainee: Trainee | null;
            scenario: Scenario | null;
            target: Target | null;
            legacy: { affinity: string | null; members: Member[] } | null;
        };
        deck: {
            slots: Slot[];
            equipped: number;
            analysis: {
                covered: number;
                categories: Record<string, unknown>;
                uncategorised: unknown[];
                strengths: string[];
                weaknesses: string[];
            };
        } | null;
        target: {
            stats: { key: string; label: string; value: number | null }[];
            races: { distance: string | null; surface: string | null; style: string | null };
            skills: { names: string[] };
        };
        /**
         * The derivable gaps in the entered data. The controller still composes them and
         * `CareerPreflightTest` still pins each trigger; the checklist states the section-level half
         * (entered or not) and this page no longer renders the list.
         */
        warnings: Warning[];
        ruleset: { label: string; title: string };
    };
    edit: Record<'scenario' | 'trainee' | 'target' | 'legacy' | 'deck', string>;
    startAction: string;
    /** Independent Training (§1.1.7): the three policies the mode offers. */
    policy_options: { value: string; label: string }[];
}>();

/** One row of the review checklist: a step, its state, its summary, and its one door back. */
interface ChecklistRow {
    key: 'scenario' | 'trainee' | 'target' | 'legacy' | 'deck';
    label: string;
    entered: boolean;
    summary: string;
    /** The trainee's Japanese name, printed beside her English one where the record carries it. */
    ja?: string | null;
    edit: string;
}

// Independent Training (§1.1.7): the idle mode's inputs ride the form directly. The policy is the one
// required choice; rotation and priority skills default server-side to the client's own defaults.
const form = useForm({
    independent: false,
    policy: 'balanced',
});
const alert = ref<HTMLElement | null>(null);

// A refused write comes back with the section's message. The alert is focused rather than left for a
// Trainer to find, because the refusal is the whole answer to the press (WCAG 2.2, SC 3.3.1).
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

const legacySummary = computed((): string => {
    const legacy = props.contract.build.legacy;

    if (legacy === null) {
        return 'Not entered';
    }

    const named = legacy.members.filter((member) => member.name !== null).length;
    const affinity = legacy.affinity === null ? 'No Affinity grade read' : `Affinity ${legacy.affinity}`;

    return `${affinity} · ${named} of two parents recorded`;
});

const targetSummary = computed((): string => {
    const target = props.contract.build.target;

    if (target === null) {
        return 'Not entered';
    }

    return `${target.purpose_label} · ${target.surface} ${target.distance} · ${target.style}`;
});

const rows = computed<ChecklistRow[]>(() => [
    {
        key: 'scenario',
        label: 'Scenario',
        entered: props.contract.build.scenario !== null,
        summary: props.contract.build.scenario?.label ?? 'Not entered',
        edit: props.edit.scenario,
    },
    {
        key: 'trainee',
        label: 'Trainee',
        entered: props.contract.build.trainee !== null,
        summary: props.contract.build.trainee?.name ?? 'Not entered',
        ja: props.contract.build.trainee?.name_ja ?? null,
        edit: props.edit.trainee,
    },
    {
        key: 'target',
        label: 'Your target',
        entered: props.contract.build.target !== null,
        summary: targetSummary.value,
        edit: props.edit.target,
    },
    {
        key: 'legacy',
        label: 'Legacy',
        entered: props.contract.build.legacy !== null,
        summary: legacySummary.value,
        edit: props.edit.legacy,
    },
    {
        key: 'deck',
        label: 'Support deck',
        entered: props.contract.deck !== null,
        summary: props.contract.deck === null
            ? 'Not entered'
            : `${props.contract.deck.equipped} of six positions carry a card`,
        edit: props.edit.deck,
    },
]);

/** The steps the draft has nothing for, in the checklist's own order. */
const missing = computed<ChecklistRow[]>(() => rows.value.filter((row) => !row.entered));

/**
 * The heading's count. `role="status"` on it (in the template) is the announcement: the number is the
 * page's answer to "can I start", and it changes only when the draft does.
 */
const attention = computed((): string => {
    const count = missing.value.length;

    if (count === 0) {
        return `All ${rows.value.length} entered steps are recorded.`;
    }

    return count === 1 ? '1 step needs attention' : `${count} steps need attention`;
});

/** What `StartCareerRequest` refuses without, and nothing else: a scenario and a trainee. */
const canStart = computed((): boolean => props.contract.build.scenario !== null && props.contract.build.trainee !== null);

const startBlocker = computed((): string | null => {
    const absent: string[] = [];

    if (props.contract.build.scenario === null) {
        absent.push('A scenario');
    }

    if (props.contract.build.trainee === null) {
        absent.push('a trainee');
    }

    if (absent.length === 0) {
        return null;
    }

    const list = absent.join(' and ');

    return absent.length === 1
        ? `${list} is not entered, and a career cannot start without one.`
        : `${list} are not entered, and a career cannot start without both.`;
});

const start = (): void => {
    if (form.processing) {
        return;
    }

    form.put(props.startAction);
};

/*
 * The "This run" tiles (`career-preflight.html:90-106`): eight facts, each either a value the draft holds or
 * a stated absence. A tile is never blank and never a zero standing in for "unknown" — the sum below returns
 * null rather than 0 when there is no target, because a target total of 0 is a claim the draft has not made.
 */
const targetTotal = computed((): number | null => {
    const targets = props.contract.build.target?.targets ?? null;

    if (targets === null) {
        return null;
    }

    return Object.values(targets).reduce((sum, value) => sum + value, 0);
});

/** The two parent names the legacy step recorded, or the word the wireframe uses for an empty slot. */
const parentsSummary = computed((): string => {
    const legacy = props.contract.build.legacy;

    if (legacy === null) {
        return 'Not entered';
    }

    const named = legacy.members.map((member) => member.name).filter((name): name is string => name !== null);

    if (named.length === 0) {
        return 'empty';
    }

    // The wireframe prints "Vodka · empty" for one recorded parent, so an unfilled slot is stated in place
    // rather than silently dropped from the list.
    return [...named, ...Array.from({ length: legacy.members.length - named.length }, () => 'empty')].join(' · ');
});

/**
 * The deck's own state word. A deck with empty slots is a legitimate choice — the run commits either way —
 * so it is a warning rather than a refusal, and it is stated once here rather than on every later screen
 * (`career-preflight.html:140-143`).
 */
const deckState = computed((): 'Pass' | 'Warning' | 'Missing' => {
    const deck = props.contract.deck;

    if (deck === null) {
        return 'Missing';
    }

    return deck.equipped === deck.slots.length ? 'Pass' : 'Warning';
});

/** The one word each check row carries, read off the row's own state rather than restated per branch. */
function checkState(row: ChecklistRow): 'Pass' | 'Warning' | 'Missing' {
    if (!row.entered) {
        return 'Missing';
    }

    return row.key === 'deck' ? deckState.value : 'Pass';
}

/** The wireframe's career length: three career years on the client's own 24-turn grid. */
const CAREER_TURNS = 72;
</script>

<template>
    <SetupLayout :step="props.step" :visited="props.visited" :saved="props.saved">
        <Head title="Before the first turn" />
        <template #head>
            <PageHeader
                eyebrow="New career"
                title="Before the first turn"
                description="One screen listing what will be created, what the run will be measured against, and the things worth knowing before you commit."
                position="Step 6 of 6"
            >
                <!--
                  The HFW's head-band pair (`career-preflight.html:61-65`). Both controls used to sit in a
                  row inside the "This run" card, at the foot of its training-policy fieldset, so the screen's
                  one write was below a card the Trainer had to reach by scrolling.

                  "Create the run" is this step's write and it stays a method call rather than a form submit:
                  `PreflightController@store` is a PUT composed from the draft, and the button's disabled
                  state reads `canStart`, which is the same gate the request re-checks.
                -->
                <template #actions>
                    <Link
                        :href="props.edit.deck"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:bg-panel"
                    >
                        Back to deck
                    </Link>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-primary-action bg-primary-action px-4 font-semibold text-on-primary-action hover:bg-primary-action/90 disabled:opacity-60"
                        :disabled="!canStart || form.processing"
                        @click="start"
                    >
                        {{ form.processing ? 'Starting…' : form.independent ? 'Start Independent Training' : 'Create the run' }}
                    </button>
                </template>
            </PageHeader>
        </template>

        <!-- The refusal, when the server finds a section that no longer validates. Nothing below blocks;
             this does, and it names the section so the next action is the step that owns it. -->
        <div
            v-if="form.hasErrors"
            ref="alert"
            role="alert"
            tabindex="-1"
            class="mt-4 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
        >
            <p class="font-semibold">The career was not created.</p>
            <ul class="mt-1 list-disc pl-5">
                <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
            </ul>
        </div>

        <!-- `career-preflight.html:87-189`: a two-column shell, the run and its checks on the left, what the
             scenario asks and what is missing on the right. `g-side` is 2.1fr / 1fr above 820px and one
             column below it (shell.css:404-406), so the breakpoints are media queries and not container
             queries: the columns must follow the window, not the shell's content box. -->
        <div class="grid grid-cols-1 gap-3.5 min-[821px]:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]">
            <div class="space-y-3.5">
                <section class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="run-heading">
                    <h2 id="run-heading" class="text-sm font-bold text-ink-strong">This run</h2>

                    <div class="mt-2.5 grid grid-cols-2 gap-2.5 min-[1081px]:grid-cols-4">
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Trainee</p>
                            <p class="text-sm text-ink-strong">
                                <template v-if="props.contract.build.trainee">{{ props.contract.build.trainee.name }}</template>
                                <AbsenceValue v-else compact reason="No trainee has been chosen in this draft." />
                            </p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Form</p>
                            <p class="text-sm text-ink-strong">
                                <template v-if="props.contract.build.trainee">as chosen above</template>
                                <AbsenceValue v-else compact reason="No trainee has been chosen in this draft, so no costume form is named." />
                            </p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Scenario</p>
                            <p class="text-sm text-ink-strong">
                                <template v-if="props.contract.build.scenario">{{ props.contract.build.scenario.label }}</template>
                                <AbsenceValue v-else compact reason="No scenario has been chosen in this draft." />
                            </p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Distance</p>
                            <p class="text-sm text-ink-strong">
                                <template v-if="props.contract.build.target">{{ props.contract.build.target.distance }}</template>
                                <AbsenceValue v-else compact reason="The target step has not been entered." />
                            </p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Parents</p>
                            <p class="text-sm text-ink-strong">{{ parentsSummary }}</p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Deck</p>
                            <p class="text-sm text-ink-strong">
                                <template v-if="props.contract.deck">
                                    {{ props.contract.deck.equipped }} of {{ props.contract.deck.slots.length }}
                                </template>
                                <AbsenceValue v-else compact reason="The deck step has not been entered." />
                            </p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Target total</p>
                            <p class="text-sm font-mono tabular-nums text-ink-strong">
                                <template v-if="targetTotal !== null">{{ targetTotal.toLocaleString('en-US') }}</template>
                                <AbsenceValue v-else compact reason="The target step has not been entered." />
                            </p>
                        </div>
                        <div class="rounded-md border border-rule bg-raised px-2.5 py-2">
                            <p class="text-xs text-ink-muted">Career length</p>
                            <p class="text-sm font-mono tabular-nums text-ink-strong">{{ CAREER_TURNS }} turns</p>
                        </div>
                    </div>

                    <!-- The wireframe states where the 72 comes from, because it is the one number on this
                         screen that is not read off the draft. The post-December finale block sits outside
                         the 24-turn grid and is deliberately not counted. -->
                    <p class="mt-2.5 text-xs text-ink-muted">
                        {{ CAREER_TURNS }} turns is three career years of the client's own 24-turn grid. The
                        post-December finale block sits outside that grid and is not counted.
                    </p>
                </section>

                <!-- The wireframe's Checks table (`career-preflight.html:108-144`), one row per step the
                     wizard has, so the state and the door back to the step that owns it are on the same line.
                     The ids survive from the list this replaced: they are the hooks the spec and the
                     screen's own deep links use. -->
                <section class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="checks-heading">
                    <h2 id="checks-heading" class="text-sm font-bold text-ink-strong">Checks</h2>
                    <p role="status" class="mt-1 text-sm text-ink">{{ attention }}</p>

                    <table class="mt-2.5 w-full text-left text-sm">
                        <caption class="sr-only">The five setup steps and whether each is recorded</caption>
                        <thead>
                            <tr class="border-b border-rule text-xs uppercase tracking-wide text-ink-muted">
                                <th scope="col" class="py-1.5 pr-2 font-semibold">Check</th>
                                <th scope="col" class="py-1.5 pr-2 font-semibold">State</th>
                                <th scope="col" class="py-1.5 font-semibold">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in rows"
                                :id="`preflight-step-${row.key}`"
                                :key="row.key"
                                tabindex="-1"
                                class="border-b border-rule last:border-0 align-top"
                            >
                                <th scope="row" class="py-2 pr-2 font-semibold text-ink-strong">{{ row.label }}</th>
                                <td class="py-2 pr-2">
                                    <!-- A pill states the word as text, never a colour alone (design-2.0 §17). -->
                                    <span
                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold"
                                        :class="checkState(row) === 'Pass'
                                            ? 'border-pick-line bg-raised text-ink'
                                            : checkState(row) === 'Warning'
                                                ? 'border-status-caution bg-raised text-ink'
                                                : 'border-risk bg-raised text-ink'"
                                    >{{ checkState(row) }}</span>
                                </td>
                                <td class="py-2 text-ink">
                                    <span>{{ row.summary }}</span>
                                    <span v-if="row.ja" lang="ja" class="ml-2 text-ink-muted">{{ row.ja }}</span>
                                    <!-- Exactly one fix link, on the row that is the one warning. A link on
                                         every row would be five ways to leave a screen whose point is the
                                         single thing left to do. -->
                                    <Link
                                        v-if="checkState(row) === 'Warning'"
                                        :href="row.edit"
                                        :aria-label="`Fix ${row.label}`"
                                        class="ml-2 inline-flex min-h-11 items-center text-sm font-medium text-ink underline"
                                    >
                                        Fix this
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>

            <div class="space-y-3.5">
                <!-- What the run will be measured against. Every row is a fact the draft or the ruleset
                     holds; a row this tool cannot answer is absent rather than invented. -->
                <section class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="asks-heading">
                    <h2 id="asks-heading" class="text-sm font-bold text-ink-strong">What this scenario asks</h2>
                    <table class="mt-2.5 w-full text-left text-sm">
                        <caption class="sr-only">The target this run will be measured against</caption>
                        <tbody>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Scenario</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.scenario">{{ props.contract.build.scenario.label }}</template>
                                    <AbsenceValue v-else compact reason="No scenario has been chosen in this draft." />
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Focus</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.scenario?.focus">{{ props.contract.build.scenario.focus }}</template>
                                    <AbsenceValue v-else compact reason="The scenario record carries no focus sentence." />
                                </td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Purpose</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.target">{{ props.contract.build.target.purpose_label }}</template>
                                    <AbsenceValue v-else compact reason="The target step has not been entered." />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="py-1.5 pr-2 font-normal text-ink-muted">Course</th>
                                <td class="py-1.5 text-ink">
                                    <template v-if="props.contract.build.target">
                                        {{ props.contract.build.target.surface }} {{ props.contract.build.target.distance }} ·
                                        {{ props.contract.build.target.style }}
                                    </template>
                                    <AbsenceValue v-else compact reason="The target step has not been entered." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Product gaps, each naming what is missing and what the screen does instead. None says
                     "not built": that is developer language in a user interface
                     (`career-preflight.html:160-174`). -->
                <section class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="gaps-heading">
                    <h2 id="gaps-heading" class="text-sm font-bold text-ink-strong">What is not built yet</h2>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-ink-muted">
                        <li>Per-discipline facility training counts are not recorded, so no facility level can be shown.</li>
                        <li>
                            No column records a Global ruleset version, so the version is stated as an absence
                            beside the ruleset it would qualify.
                        </li>
                        <li>
                            A deck position's owned-or-rented flag is stored per position, and a position holding
                            no card creates no deck row.
                        </li>
                    </ul>
                    <p class="mt-2.5 text-xs text-ink-muted">
                        Ruleset version:
                        <AbsenceValue compact :reason="props.contract.ruleset.title" />
                    </p>
                </section>

                <!-- The commitment itself. `Start career` is the page's only filled action, and it is honest
                     about what it needs: a scenario and a trainee, which is exactly what
                     `StartCareerRequest` refuses without. -->
                <section class="rounded-lg border border-rule bg-panel p-4" aria-labelledby="commit-heading">
                    <h2 id="commit-heading" class="text-sm font-bold text-ink-strong">Commit</h2>
                    <p class="mt-2 text-xs text-ink-muted">
                        Creating the run writes the trainee, scenario, target, parents and deck. Turn 1 is
                        recorded from the cockpit, not here, so nothing is marked played until a turn is
                        actually logged.
                    </p>
                    <p v-if="startBlocker !== null" class="mt-2 text-sm text-ink">{{ startBlocker }}</p>

                    <!-- Independent Training (§1.1.7): the entry tab. The idle mode runs its 50 minutes
                         with the app closed; choosing it swaps the policy selector in. Rotation and
                         priority skills stay at the client's defaults, so only the policy is asked. -->
                    <fieldset class="mt-3">
                        <legend class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Career mode</legend>
                        <div class="mt-1.5 flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center rounded-md border px-4 text-sm font-medium"
                                :class="form.independent ? 'border-rule bg-raised text-ink' : 'border-pick bg-pick text-on-pick'"
                                @click="form.independent = false"
                            >
                                Normal Career
                            </button>
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center rounded-md border px-4 text-sm font-medium"
                                :class="form.independent ? 'border-pick bg-pick text-on-pick' : 'border-rule bg-raised text-ink'"
                                @click="form.independent = true"
                            >
                                Independent Training
                            </button>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.independent" class="mt-3">
                        <legend class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Training policy</legend>
                        <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-2">
                            <label v-for="option in props.policy_options" :key="option.value" class="inline-flex items-center gap-1.5 text-sm text-ink">
                                <input v-model="form.policy" type="radio" name="policy" :value="option.value" class="accent-pick" />
                                {{ option.label }}
                            </label>
                        </div>
                        <p class="mt-1.5 text-xs text-ink-muted">
                            Balanced is recommended: the Stamina and Sprint policies skew stats and cost win rate.
                        </p>
                    </fieldset>

                    <!-- "Create the run" and "Back to deck" moved to the head band above (`#actions`), where
                         the HFW draws them. Only the policy note keeps its place here, beside the radios it
                         describes. -->
                </section>
            </div>
        </div>
    </SetupLayout>
</template>