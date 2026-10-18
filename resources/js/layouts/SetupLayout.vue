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
import { computed, onMounted, nextTick } from 'vue';
import { useLayout } from '../composables/useLayout';

const props = defineProps<{ step: number; visited?: number[]; saved?: number[] }>();

// The wizard honours the same width preference as the shell, so its wide steps (the deck's
// master/detail, the scenario matrix) get the wide screen the Trainer asked for. `container-type:
// inline-size` is what lets an inner step lay itself out off the wrapper's resolved width instead
// of the viewport, so the sidebar collapse never re-breaks a step. The `AppLayout` `main` carries
// the same measure via `useLayout`; this wrapper re-states it so the step grid inside is the thing
// that fills, and so SetupLayout applies the shared map rather than its own inline one.
const { mode, layoutWidthClass } = useLayout();
const wrapperClass = computed(() => {
    return `${layoutWidthClass.value} mx-auto w-full`;
});

const steps = [
    { label: 'Scenario', to: '/career/setup/scenario' },
    { label: 'Trainee', to: '/career/setup/trainee' },
    { label: 'Your target', to: '/career/setup/target' },
    { label: 'Legacy', to: '/career/setup/legacy' },
    { label: 'Support deck', to: '/career/setup/deck' },
    { label: 'Preflight', to: '/career/setup/preflight' },
];

const stepLink =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const stepCurrent = 'bg-raised text-ink-strong font-bold';
const stepCompleted = 'text-ink-strong';
const stepNotStarted = 'text-ink-muted';

// A named absence reads as a step and is never a link, so this restates `stepLink`'s geometry
// without its hover and without `text-ink`: a muted, non-focusable row that keeps the 44px line
// height so the bar does not jump when a step is unbuilt. Not built on top of `stepLink` with a
// second text colour, because two `text-*` utilities in one class list resolve by stylesheet
// order rather than by the order they are written.
const stepAbsent =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink-muted';

const leaveLink =
    'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';

const isCompleted = (index: number): boolean =>
    (props.visited?.includes(index + 1) === true || props.saved?.includes(index + 1) === true) && index + 1 !== props.step;
const isNotStarted = (index: number): boolean =>
    !isCompleted(index) && index + 1 !== props.step;

/*
 * Focus and document title, on every step load.
 *
 * A wizard re-renders the same shell with new content, so nothing tells a keyboard or screen-reader
 * user that the step changed: the URL moved, the headings did not. Two things fix that without
 * touching the shell.
 *
 *  - The step's `h1` takes focus. It is NOT moved: since 1.3 the heading sits in the shell's title
 *    band, and the shell decides whether that band is `#head` or `#title`. This finds whichever `h1`
 *    the page rendered, gives it `tabindex="-1"` so a heading can take programmatic focus, and focuses
 *    it. The attribute is added here rather than in `AppLayout` so the shell keeps owning position and
 *    this layout owns the wizard's own focus policy.
 *  - `document.title` becomes the step's title. The step label is the honest subject: a Trainer with
 *    six setup tabs open needs to tell them apart from the tab strip.
 *
 * `nextTick` because the h1 is rendered by the parent's `#title` slot, which is not in the DOM at
 * this component's own mount.
 */
onMounted(async () => {
    await nextTick();

    // The shell renders the heading in its banner, but `#head` and `#title` are slot NAMES, not
    // element ids: the first version queried `#head h1, #title h1` and matched nothing, which the
    // spec caught as a missing tabindex rather than a missing heading. The heading is simply the
    // page's one `h1`, which is the property `page-header.spec.ts` already guarantees on every
    // pilot page.
    const heading = document.querySelector<HTMLElement>('main h1, h1');

    if (heading === null) {
        return;
    }

    heading.setAttribute('tabindex', '-1');
    heading.focus({ preventScroll: true });

    const label = steps[props.step - 1]?.label ?? heading.textContent?.trim() ?? '';

    // The step pages set their own `<Head title>` ("Choose a scenario"), which is more specific than
    // this layout's step label ("Scenario"). A first version overwrote it on mount and the two
    // raced, so a run would sometimes show the page's name and sometimes the shell's. The page wins:
    // this only fills the title when a step ships without one, and the tab never goes unnamed.
    if (document.title.trim() === '' && label !== '') {
        document.title = `${label} · Setup · Trainer Desk`;
    }
});
</script>

<template>
    <AppLayout>
        <template v-if="$slots.eyebrow" #eyebrow><slot name="eyebrow" /></template>
        <template #title><slot name="title" /></template>

        <!-- The step list is a labelled nav, and the current step carries `aria-current="step"`, so
             the position is announced rather than carried by the accent alone (WCAG 1.4.1). The visible
             count and the `sr-only` line are the same fact in two media: the number is a position, and
             a screen reader should hear it before the list rather than after the sixth item. -->
        <nav aria-label="Setup steps" class="mb-4 border-b border-rule pb-2 md:sticky md:top-0 md:z-10 md:-mx-6 md:bg-page md:px-6">
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
                        :class="[stepLink, index + 1 === props.step ? stepCurrent : isCompleted(index) ? stepCompleted : isNotStarted(index) ? stepNotStarted : '']"
                        :aria-current="index + 1 === props.step ? 'step' : undefined"
                    >
                        <span class="mr-2 font-mono text-xs" aria-hidden="true">{{ index + 1 }}</span>{{ item.label }}
                        <span v-if="index + 1 !== props.step" class="ml-2 text-xs">
                            {{ isCompleted(index) ? 'done' : 'not started' }}
                        </span>
                    </Link>
                    <span v-else :class="stepAbsent" title="This step arrives with its own slice.">
                        <span class="mr-2 font-mono text-xs" aria-hidden="true">{{ index + 1 }}</span>{{ item.label }}
                        <span class="ml-2 text-xs">not built</span>
                    </span>
                </li>
            </ol>
        </nav>

        <div :class="wrapperClass" class="@container" data-layout-container>
            <slot />

            <!--
              The action bar, when a step wants the shell to own it. The steps that landed before
              this slice render `StickyFooter` themselves (it has to be the root element of whatever
              holds it, so wrapping one here would break its own sticky range); a step from 3.2 onward
              can fill `#actions` instead and the shell places it at the foot of the step content.
              Empty for every current step, so nothing renders and nothing moves.
            -->
            <slot name="actions" />
        </div>
    </AppLayout>
</template>
