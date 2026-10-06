<script setup lang="ts">
/*
 * The career setup wizard's shell (SCREEN-002 to SCREEN-008; `ADR-0020` §1, plan §2 convention 3:
 * `SetupLayout` is added "only when its first screen lands", which is this slice).
 *
 * It is `AppLayout` plus the step bar, which is the same decision `CareerLayout.vue` makes for the
 * career screens: the product navigation stays reachable, because a six-step flow that hides the way out
 * is a flow a Trainer can get stuck inside. The original docblock argued the opposite, on the grounds
 * that a wizard with the full sidebar "gives the Trainer nine ways to leave a six-step flow and no way to
 * tell which step they are on". Both halves of that have since been answered: `SetupDraft` writes every
 * step to the session as it is saved, so leaving mid-flow loses nothing, and the step count and
 * `aria-current="step"` are what tell a Trainer which step they are on, not the absence of a sidebar.
 *
 * The step bar stays the wizard's own sub-navigation, so "which step am I on and what is next" is
 * answered one level above the page rather than competing with the product destinations.
 *
 * A step whose slice has not landed is a named absence (`to: null`), the same rule `AppLayout`
 * `items[]` uses. All six steps are live: the ancestry step (D5-wizard) and the deck step (D6-wizard)
 * carry their values in the session draft rather than on a run, and Preflight (D7) composes that draft
 * and creates the run. The `to: null` branch stays because the next wizard slice that lands a step ahead
 * of its own screen needs it, and because a named absence is the honest answer rather than a dead link.
 */
import AppLayout from './AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{ step: number }>();

const steps = [
    { label: 'Scenario', to: '/career/setup/scenario' },
    { label: 'Trainee', to: '/career/setup/trainee' },
    { label: 'Your target', to: '/career/setup/target' },
    { label: 'Legacy', to: '/career/setup/legacy' },
    { label: 'Support Cards', to: '/career/setup/deck' },
    { label: 'Preflight', to: '/career/setup/preflight' },
];

const stepLink =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const stepCurrent = 'bg-raised text-ink-strong font-bold';
const stepAbsent =
    'flex min-h-11 cursor-not-allowed items-center rounded-md px-3 text-sm font-medium text-ink-muted';
const leaveLink =
    'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
</script>

<template>
    <AppLayout>
        <template #title><slot name="title" /></template>

        <!-- The step list is a labelled nav, and the current step carries `aria-current="step"`, so
             the position is announced rather than carried by the accent alone (WCAG 1.4.1). The visible
             count and the `sr-only` line are the same fact in two media: the number is a position, and
             a screen reader should hear it before the list rather than after the sixth item. -->
        <nav aria-label="Setup steps" class="mb-4 border-b border-rule pb-2">
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                <p class="text-xs text-ink-muted">
                    Step {{ props.step }} of {{ steps.length }}
                </p>
                <Link href="/" :class="leaveLink">Leave setup</Link>
            </div>

            <p class="sr-only">Step {{ props.step }} of {{ steps.length }}</p>

            <ol class="mt-1 flex flex-wrap gap-1 text-sm">
                <li v-for="(item, index) in steps" :key="item.label">
                    <Link
                        v-if="item.to"
                        :href="item.to"
                        :class="[stepLink, index + 1 === props.step ? stepCurrent : '']"
                        :aria-current="index + 1 === props.step ? 'step' : undefined"
                    >
                        <span class="mr-2 font-mono text-xs" aria-hidden="true">{{ index + 1 }}</span>{{ item.label }}
                    </Link>
                    <span v-else :class="stepAbsent" title="This step arrives with its own slice.">
                        <span class="mr-2 font-mono text-xs" aria-hidden="true">{{ index + 1 }}</span>{{ item.label }}
                        <span class="ml-2 text-xs">not built</span>
                    </span>
                </li>
            </ol>
        </nav>

        <slot />
    </AppLayout>
</template>
