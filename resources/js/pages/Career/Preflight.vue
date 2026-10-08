<script setup lang="ts">
/*
 * The career setup wizard's Preflight step, `SCR-CAR-010` (SCREEN-008, PRD FR-A-5, `ADR-0020` §1 and §3).
 * Step 6 of six, and the only step that writes.
 *
 * **Nothing is re-asked.** Every fact on this page is read from the draft the five earlier steps filled and
 * arrives as one `contract` prop, so Back and Edit round trips are lossless by construction: a step opens
 * on the values it stored, not on what the client still held.
 *
 * **The warnings never block.** Each one restates entered data (a section nobody filled, a deck with an
 * empty position, a target band sitting on a Weak aptitude, a skill priority no source in this setup names)
 * and each links to the step that can change it. The Trainer decides; only a server refusal stops the
 * write, and that is a section that no longer validates rather than a judgement about the plan.
 *
 * **The primary action creates the run.** `Start Career` posts to the wizard's one write, which re-runs
 * every step's own Form Request server-side before it creates anything, so this page's rendering is never
 * the trust boundary. While the write is in flight the button says so, because it is a user-initiated
 * action and its own progress is the only loading state this screen has (ADR-0007).
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import DeckAnalysis from '../../components/support/DeckAnalysis.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';

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
    is_guest: boolean;
    ancestors: { slot: string; name: string | null }[];
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
        warnings: Warning[];
        ruleset: { label: string; title: string };
    };
    edit: Record<'scenario' | 'trainee' | 'target' | 'legacy' | 'deck', string>;
    startAction: string;
}>();

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

const start = (): void => {
    form.put(props.startAction);
};

const memberName = (member: Member): string => member.name ?? 'N/A, not chosen';
const statLabel = (value: number | null): string => (value === null ? 'N/A' : String(value));
const raceLabel = (value: string | null): string => value ?? 'N/A';
</script>

<template>
    <SetupLayout :step="props.step">
        <Head title="Career contract" />
        <template #title>Career contract</template>

        <p class="mt-4 text-sm text-ink-muted">
            Everything the six steps recorded, in the order you entered it. Nothing here is asked again.
        </p>

        <!-- The refusal, when the server finds a section that no longer validates. Warnings below never
             block; this does, and it names the section so the next action is the step that owns it. -->
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

        <!-- Warnings: glyph plus text, never colour alone, and each one links to the step it came from. -->
        <section v-if="props.contract.warnings.length > 0" class="mt-6" aria-labelledby="warnings-heading">
            <h2 id="warnings-heading" class="text-base font-semibold text-ink-strong">
                Warnings
            </h2>
            <ul class="mt-2 space-y-2">
                <li
                    v-for="warning in props.contract.warnings"
                    :key="warning.key"
                    class="flex flex-wrap items-baseline gap-x-2 rounded-md border border-rule bg-panel p-3 text-sm"
                >
                    <span aria-hidden="true" class="font-mono font-bold text-goal-line">!</span>
                    <span class="font-semibold text-ink-strong">{{ warning.title }}.</span>
                    <span class="text-ink">{{ warning.detail }}</span>
                    <Link :href="warning.href" class="min-h-11 font-medium text-ink-strong underline">
                        Open that step
                    </Link>
                </li>
            </ul>
        </section>

        <p
            v-else
            class="mt-6 rounded-md border border-rule bg-panel p-3 text-sm text-ink"
        >
            No warnings. Every section is filled and nothing in the entered data reads as a gap this tool can
            see.
        </p>

        <section class="mt-6" aria-labelledby="build-heading">
            <h2 id="build-heading" class="text-base font-semibold text-ink-strong">Build</h2>

            <dl class="mt-2 grid gap-2 sm:grid-cols-2">
                <div class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Trainee</dt>
                    <dd class="mt-1 text-sm text-ink-strong">
                        <template v-if="props.contract.build.trainee">
                            {{ props.contract.build.trainee.name }}
                            <span
                                v-if="props.contract.build.trainee.name_ja"
                                lang="ja"
                                class="ml-2 text-ink-muted"
                            >{{ props.contract.build.trainee.name_ja }}</span>
                        </template>
                        <span v-else title="No trainee is recorded in this setup yet.">N/A</span>
                    </dd>
                </div>

                <div class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Scenario</dt>
                    <dd class="mt-1 text-sm text-ink-strong">
                        <template v-if="props.contract.build.scenario">
                            {{ props.contract.build.scenario.label }}
                            <span v-if="props.contract.build.scenario.focus" class="ml-2 text-ink-muted">
                                {{ props.contract.build.scenario.focus }}
                            </span>
                        </template>
                        <span v-else title="No scenario is recorded in this setup yet.">N/A</span>
                    </dd>
                </div>

                <div class="rounded-md border border-rule bg-panel p-3 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Your target</dt>
                    <dd class="mt-1 text-sm text-ink">
                        <template v-if="props.contract.build.target">
                            {{ props.contract.build.target.purpose_label }} ·
                            {{ props.contract.build.target.surface }}
                            {{ props.contract.build.target.distance }} ·
                            {{ props.contract.build.target.style }}
                        </template>
                        <span v-else title="No build target is recorded in this setup yet.">N/A</span>
                    </dd>
                </div>

                <div class="rounded-md border border-rule bg-panel p-3 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        Ancestry
                        <span v-if="props.contract.build.legacy" class="ml-2 normal-case tracking-normal">
                            Affinity
                            <span v-if="props.contract.build.legacy.affinity">
                                {{ props.contract.build.legacy.affinity }}
                            </span>
                            <span v-else title="No Affinity grade was read for the pair.">N/A</span>
                        </span>
                    </dt>
                    <dd class="mt-1 text-sm text-ink">
                        <ul v-if="props.contract.build.legacy" class="space-y-2">
                            <li v-for="member in props.contract.build.legacy.members" :key="member.slot">
                                <p class="font-medium text-ink-strong">
                                    {{ member.label }}:
                                    <span :class="member.name === null ? 'text-ink-muted' : ''">
                                        {{ memberName(member) }}
                                    </span>
                                    <span v-if="member.is_guest" class="ml-2 text-ink-muted">
                                        Rented from a friend
                                    </span>
                                </p>
                                <p class="text-xs text-ink-muted">
                                    <span v-for="(ancestor, index) in member.ancestors" :key="ancestor.slot">
                                        {{ index > 0 ? ' · ' : '' }}{{ ancestor.slot }}:
                                        <span :title="ancestor.name === null ? 'No name was recorded for this ancestor.' : undefined">
                                            {{ ancestor.name ?? 'N/A' }}
                                        </span>
                                    </span>
                                </p>
                                <p class="text-xs text-ink-muted">
                                    <template v-if="member.sparks.length > 0">
                                        <span v-for="spark in member.sparks" :key="`${spark.kind}-${spark.target}`" class="mr-2">
                                            {{ spark.kind_label ?? spark.kind }}
                                            <span v-if="spark.target">on {{ spark.target }}</span>
                                            <span v-if="spark.stars !== null">★{{ spark.stars }}</span>
                                        </span>
                                    </template>
                                    <span v-else title="No Sparks were recorded for this parent.">No Sparks recorded</span>
                                </p>
                            </li>
                        </ul>
                        <span v-else title="No two-parent ancestry is recorded in this setup yet.">N/A</span>
                    </dd>
                </div>
            </dl>
        </section>

        <section class="mt-6" aria-labelledby="deck-heading">
            <h2 id="deck-heading" class="text-base font-semibold text-ink-strong">Support deck</h2>

            <p v-if="props.contract.deck === null" class="mt-2 rounded-md border border-rule bg-panel p-3 text-sm text-ink">
                <span title="No six-slot deck is recorded in this setup yet.">N/A</span>. The deck step has not been saved.
            </p>

            <template v-else>
                <ol class="mt-2 grid gap-2 sm:grid-cols-2">
                    <li
                        v-for="slot in props.contract.deck.slots"
                        :key="slot.position"
                        class="rounded-md border border-rule bg-panel p-3 text-sm"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            {{ slot.label }}
                        </p>
                        <p class="mt-1 text-ink-strong">
                            <template v-if="slot.card">{{ slot.card.name }}</template>
                            <span v-else title="This position carries no card.">Not equipped</span>
                        </p>
                        <p v-if="slot.card" class="text-xs text-ink-muted">
                            {{ slot.card.rarity_word }} · {{ slot.card.type_label }}
                            <span v-if="slot.ownership === 'OWNED'">· Owned</span>
                            <span v-else-if="slot.ownership === 'RENTED'">· Rented</span>
                        </p>
                    </li>
                </ol>

                <p class="mt-2 text-sm text-ink-muted">
                    {{ props.contract.deck.equipped }} of six positions carry a card.
                </p>

                <!-- The flag is printed above, and `StartCareerRequest::deckByPosition()` writes the six
                     positions and nothing else: there is no `deck_slots` column for it (`ADR-0014`). Said
                     at the commitment screen because this page's own promise is "everything the six steps
                     recorded", and the flag is the one entered fact that will not be recorded. -->
                <p class="mt-2 rounded-md border border-dashed border-rule bg-raised p-3 text-xs text-ink-muted">
                    The owned or rented flag is in this setup draft. The deck table has no column for it yet,
                    so starting the career writes the six cards it can store and the flag stays in the draft
                    until one exists.
                </p>

                <DeckAnalysis class="mt-3" v-bind="props.contract.deck.analysis" />
            </template>
        </section>

        <section class="mt-6" aria-labelledby="target-heading">
            <h2 id="target-heading" class="text-base font-semibold text-ink-strong">Target</h2>

            <dl class="mt-2 grid gap-2 sm:grid-cols-3">
                <div v-for="stat in props.contract.target.stats" :key="stat.key" class="rounded-md border border-rule bg-panel p-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ stat.label }}</dt>
                    <dd
                        class="mt-1 font-mono text-sm text-ink-strong"
                        :title="stat.value === null ? 'No target was entered for this stat.' : undefined"
                    >
                        {{ statLabel(stat.value) }}
                    </dd>
                </div>
            </dl>

            <p class="mt-2 text-sm text-ink">
                Race profile:
                <span :title="props.contract.target.races.distance === null ? 'No distance band was entered.' : undefined">
                    {{ raceLabel(props.contract.target.races.distance) }}
                </span>
                <span class="text-ink-muted"> · </span>
                <span :title="props.contract.target.races.surface === null ? 'No surface was entered.' : undefined">
                    {{ raceLabel(props.contract.target.races.surface) }}
                </span>
                <span class="text-ink-muted"> · </span>
                <span :title="props.contract.target.races.style === null ? 'No running style was entered.' : undefined">
                    {{ raceLabel(props.contract.target.races.style) }}
                </span>
            </p>

            <p class="mt-1 text-sm text-ink">
                Skill priorities:
                <template v-if="props.contract.target.skills.names.length > 0">
                    {{ props.contract.target.skills.names.join(' · ') }}
                </template>
                <span v-else title="No skill priorities were entered on the target step.">None entered</span>
            </p>

            <p class="mt-3 text-sm text-ink-muted">
                Ruleset snapshot:
                <span class="text-ink" :title="props.contract.ruleset.title">{{ props.contract.ruleset.label }}</span>
            </p>
        </section>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <Link
                :href="props.edit.deck"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-medium text-ink hover:bg-raised"
            >
                Back
            </Link>
            <Link
                :href="props.edit.legacy"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-medium text-ink hover:bg-raised"
            >
                Edit Legacy
            </Link>
            <Link
                :href="props.edit.deck"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-medium text-ink hover:bg-raised"
            >
                Edit Deck
            </Link>
            <Link
                :href="props.edit.target"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-4 text-sm font-medium text-ink hover:bg-raised"
            >
                Edit Target
            </Link>

            <button
                type="button"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-5 text-sm font-semibold text-on-chrome disabled:opacity-60"
                :disabled="form.processing"
                @click="start"
            >
                {{ form.processing ? 'Starting…' : 'Start Career' }}
            </button>
        </div>
    </SetupLayout>
</template>
