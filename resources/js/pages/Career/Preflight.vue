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
import StickyFooter from '../../components/ui/StickyFooter.vue';
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

const form = useForm({});
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
        return 'All five entered steps are recorded.';
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
</script>

<template>
    <SetupLayout :step="props.step" :visited="props.visited" :saved="props.saved">
        <Head title="Review and start" />
        <template #head>
            <PageHeader
                eyebrow="Setup · Step 6 of 6"
                title="Review and start"
                description="What the five earlier steps recorded, and the one door back to each of them."
            />
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

        <section class="mx-auto w-full max-w-4xl" aria-labelledby="checklist-heading">
            <h2 id="checklist-heading" class="text-lg font-semibold text-ink-strong">
                The five entered steps
            </h2>
            <p role="status" class="mt-1 text-sm text-ink">{{ attention }}</p>

            <ol class="mt-3 max-w-3xl space-y-2">
                <li
                    v-for="row in rows"
                    :id="`preflight-step-${row.key}`"
                    :key="row.key"
                    tabindex="-1"
                    class="rounded-lg border border-rule bg-panel p-3"
                >
                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                        <p class="min-w-0 text-sm">
                            <!-- Glyph plus text, never the glyph alone: the state is a word for a screen
                                 reader and a shape for everyone else. -->
                            <span
                                aria-hidden="true"
                                class="mr-1 font-mono font-bold"
                                :class="row.entered ? 'text-goal-line' : 'text-risk'"
                            >{{ row.entered ? '✓' : '✗' }}</span>
                            <span class="sr-only">{{ row.entered ? 'Entered' : 'Missing' }}</span>
                            <span class="font-semibold text-ink-strong">{{ row.label }}</span>
                            <span class="text-ink"> · {{ row.summary }}</span>
                            <span v-if="row.ja" lang="ja" class="ml-2 text-ink-muted">{{ row.ja }}</span>
                        </p>
                        <Link
                            :href="row.edit"
                            :aria-label="`Edit ${row.label}`"
                            class="inline-flex min-h-11 shrink-0 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                        >
                            Edit
                        </Link>
                    </div>
                </li>
            </ol>
        </section>

        <section class="mx-auto w-full max-w-4xl mt-6" aria-labelledby="notes-heading">
            <h2 id="notes-heading" class="text-lg font-semibold text-ink-strong">Before you start</h2>

            <!-- The flag is in the checklist's own draft, and `StartCareerRequest::ownershipByPosition()`
                 carries it onto the slot row this screen creates (`ADR-0023`, D3). Said at the commitment
                 screen because a position left unclassified is the one entered fact that arrives as an
                 absence. -->
            <p class="mt-2 max-w-3xl rounded-md border border-dashed border-rule bg-panel p-3 text-xs text-ink-muted">
                The owned or rented flag is in this setup draft, and starting the career writes it onto
                each slot's deck row, one value per position. A position holding no card creates no deck
                row, and a card whose flag was never chosen is stored as no flag, never as Owned.
            </p>

            <p class="mt-2 text-sm text-ink-muted">
                Ruleset version:
                <AbsenceValue compact :reason="props.contract.ruleset.title" />
            </p>
        </section>

        <StickyFooter>
            <template #left>
                <p v-if="startBlocker !== null" class="text-sm text-ink">{{ startBlocker }}</p>
            </template>
            <template #right>
                <button
                    type="button"
                    class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-5 text-sm font-semibold text-on-primary-action disabled:opacity-60"
                    :disabled="!canStart || form.processing"
                    @click="start"
                >
                    {{ form.processing ? 'Starting…' : 'Start career' }}
                </button>
            </template>
        </StickyFooter>
    </SetupLayout>
</template>
