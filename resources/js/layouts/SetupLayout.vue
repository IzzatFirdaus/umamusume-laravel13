<script setup lang="ts">
/*
 * The career setup wizard's shell (SCREEN-002 to SCREEN-008; `ADR-0020` §1, plan §2 convention 3:
 * `SetupLayout` is added "only when its first screen lands", which is this slice).
 *
 * It is `AppLayout`'s landmark structure and nothing more: one skip link, one `banner`, one labelled
 * `nav` for the steps, one `main`, and the same 44px targets. It deliberately does not re-declare the
 * product navigation, because a wizard that keeps the whole sidebar gives the Trainer nine ways to
 * leave a six-step flow and no way to tell which step they are on.
 *
 * A step whose slice has not landed is a named absence (`to: null`), the same rule `AppLayout`
 * `items[]` uses for Veterans. Steps 1 to 5 are live: the ancestry step (D5-wizard) and the deck step
 * (D6-wizard) carry their values in the session draft rather than on a run, which is what lets them exist
 * before Preflight creates one, unlike the run-scoped Legacy Lab and deck builder. Only Preflight (D7) is
 * still an absence.
 */
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ step: number }>();

const page = usePage();
const flashStatus = computed(() => page.props.flash?.status ?? null);

const steps = [
    { label: 'Scenario', to: '/career/setup/scenario' },
    { label: 'Trainee', to: '/career/setup/trainee' },
    { label: 'Your target', to: '/career/setup/target' },
    { label: 'Legacy', to: '/career/setup/legacy' },
    { label: 'Support Cards', to: '/career/setup/deck' },
    { label: 'Preflight', to: null },
];

const stepLink =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const stepCurrent = 'bg-raised text-ink-strong font-bold';
const stepAbsent = 'flex min-h-11 cursor-not-allowed items-center rounded-md px-3 text-sm font-medium text-ink-muted';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-page text-ink">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong"
        >
            Skip to content
        </a>

        <header
            role="banner"
            class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-b border-rule bg-panel px-4 py-3 sm:px-6"
        >
            <h1 class="text-base font-semibold text-ink-strong">
                <slot name="title" />
            </h1>
            <div class="flex items-center gap-3">
                <span class="text-xs text-ink-muted" aria-hidden="true">
                    Step {{ props.step }} of {{ steps.length }}
                </span>
                <Link href="/" class="inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised">
                    Leave setup
                </Link>
            </div>
        </header>

        <!-- The step list is a labelled nav, and the current step carries `aria-current="step"`, so
             the position is announced rather than carried by the accent alone (WCAG 1.4.1). -->
        <nav aria-label="Setup steps" class="border-b border-rule bg-panel px-2 py-2">
            <p class="sr-only">Step {{ props.step }} of {{ steps.length }}</p>
            <ol class="flex flex-wrap gap-1 text-sm">
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

        <main id="main" class="flex-1 px-4 py-6 sm:px-6">
            <p
                v-if="flashStatus"
                class="mb-4 rounded border border-green-line bg-green-tint px-3 py-2 text-sm text-ink"
            >
                {{ flashStatus }}
            </p>
            <slot />
        </main>
    </div>
</template>
