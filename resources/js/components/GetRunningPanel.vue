<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/*
 * C6, the first-run checklist: a fresh install's honest state is no reference data, no career, no
 * recorded turn, and the Dashboard's counts answer none of the three. This panel lists the three
 * steps with their live completion so Persona A's first session has a route.
 *
 * It renders only while a step is outstanding; the Dashboard owns that gate, so a Trainer who has
 * recorded a turn never sees it again. Each fact is a stored existence read supplied by the
 * controller (`firstRun`), never a count the component derives. Step 3 links into the active
 * career's training screen through the prop the Dashboard already holds, because no fixed href
 * reaches "the run you just started" on a database this panel cannot read.
 */
const props = defineProps<{
    firstRun: {
        fetch_done: boolean;
        career_exists: boolean;
        turn_recorded: boolean;
        /** The active career's training screen, so step 3 links to the run that exists. */
        training_url: string | null;
    };
}>();

const steps = computed(() => [
    {
        key: 'fetch',
        done: props.firstRun.fetch_done,
        label: 'Fetch reference data',
        detail: 'Load the trainee, skill and support-card catalogues.',
        href: '/review',
    },
    {
        key: 'career',
        done: props.firstRun.career_exists,
        label: 'Start or import a career',
        detail: 'Pick a scenario and a trainee, or import a run you have already started.',
        href: '/career/setup/scenario',
    },
    {
        key: 'turn',
        done: props.firstRun.turn_recorded,
        label: 'Record your first turn',
        detail: 'Log one turn on the training screen of your active career.',
        href: props.firstRun.training_url,
    },
]);
</script>

<template>
    <section aria-labelledby="get-running-heading" class="card" data-region="get-running">
        <h2 id="get-running-heading" class="text-base">Get running</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Three steps take a new install from empty to its first recorded turn. Each shows its
            live state.
        </p>

        <ol class="mt-3 space-y-2">
            <li
                v-for="(step, index) in steps"
                :key="step.key"
                class="flex min-h-11 items-center gap-3"
            >
                <span
                    class="flex size-6 shrink-0 items-center justify-center rounded-full border border-rule text-xs font-extrabold"
                    :class="step.done ? 'text-ink-muted' : 'text-ink-strong'"
                    :data-step-state="step.done ? 'done' : 'todo'"
                >
                    {{ index + 1 }}
                </span>

                <span class="flex-1">
                    <Link
                        v-if="step.href !== null"
                        :href="step.href"
                        class="text-sm font-semibold text-ink-strong underline underline-offset-2 hover:decoration-2"
                    >{{ step.label }}</Link>
                    <span v-else class="text-sm font-semibold text-ink-strong">{{ step.label }}</span>

                    <span class="block text-xs text-ink-muted">{{ step.detail }}</span>
                </span>

                <span
                    class="text-xs font-semibold"
                    :class="step.done ? 'text-ink-muted' : 'text-risk'"
                >
                    {{ step.done ? 'Done' : 'To do' }}
                </span>
            </li>
        </ol>
    </section>
</template>
