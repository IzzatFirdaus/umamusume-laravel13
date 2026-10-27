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
import { computed, onMounted, nextTick } from 'vue';
import { useLayout } from '../composables/useLayout';

const props = defineProps<{
    step: number;
    visited?: number[];
    saved?: number[];
    /**
     * Accepted and unused. `SetupDraft::summary()` still composes it and the controllers still send
     * it, but no career setup wireframe draws a carried-choices strip (`career-scenario.html`,
     * `career-trainee.html`, `career-target.html`, `career-legacy.html`, `career-deck.html` and
     * `career-preflight.html` were each searched for one and none has it), so the shell stopped
     * rendering it. The prop stays declared so the six controllers keep one shape while the strip's
     * remaining home is decided; dropping it here first would have been a controller change made to
     * suit a layout.
     */
    choices?: { label: string; value: string | null; url: string | null }[];
}>();

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

/*
 * The six step names are the wireframe's own words, not this layout's (`career-scenario.html:73-78`):
 * "Build target" and "Deck", not "Your target" and "Support deck". The step bar is the HFW's status
 * readout, so its labels are read off the HFW rather than paraphrased here — a paraphrased label makes
 * the same step print two names on one screen, because the head band's `h1` uses the page's own title.
 */
const steps = [
    { label: 'Scenario' },
    { label: 'Trainee' },
    { label: 'Build target' },
    { label: 'Legacy' },
    { label: 'Deck' },
    { label: 'Preflight' },
];

const isCompleted = (index: number): boolean =>
    (props.visited?.includes(index + 1) === true || props.saved?.includes(index + 1) === true) && index + 1 !== props.step;

/*
 * The step list's three states, as the HFW's `.step` modifiers rather than as links.
 *
 * The wireframe's stepper is a status readout, not a second navigation: the six `.step` spans carry
 * `.is-done` / `.is-partial` / `.is-empty` and the current one carries `aria-current="step"`. The
 * previous version rendered each step as a `<Link>`, which made the bar a second way to move through
 * the wizard beside the page's own Back/Continue pair, and made "which step am I on" compete with six
 * clickable targets. The states resolve through the ported `.step` rules in `app.css`; nothing here
 * restates a colour.
 */
function stepClass(index: number): string[] {
    const number = index + 1;

    if (number === props.step) {
        return ['is-partial'];
    }

    return isCompleted(index) ? ['is-done'] : ['is-empty'];
}


/*
 * Focus and document title, on every step load.
 *
 * A wizard re-renders the same shell with new content, so nothing tells a keyboard or screen-reader
 * user that the step changed: the URL moved, the headings did not. Two things fix that without
 * touching the shell.
 *
 *  - The step's `h1` takes focus. It is NOT moved: the head band is the wizard's own (rendered inside
 *    `main`, below the shell bar), so this finds whichever `h1` the band drew and gives it
 *    `tabindex="-1"` so a heading can take programmatic focus, then focuses it. The attribute is added
 *    here rather than in the band's markup so this layout keeps owning the wizard's own focus policy.
 *  - `document.title` becomes the step's title. The step label is the honest subject: a Trainer with
 *    six setup tabs open needs to tell them apart from the tab strip.
 *
 * `nextTick` because the h1 is rendered by the parent's `#title` slot, which is not in the DOM at
 * this component's own mount.
 */
onMounted(async () => {
    await nextTick();

    // The wizard draws the heading in its own head band (inside `main`), whether the step rendered
    // `PageHeader` or filled `#title`. `#head`/`#title` are slot NAMES, not element ids: the first
    // version queried `#head h1, #title h1` and matched nothing, which the spec caught as a missing
    // tabindex rather than a missing heading. The heading is simply the page's one `h1`, which
    // `page-header.spec.ts` already guarantees on every pilot page.
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
        <!--
          The head band, drawn INSIDE the canvas rather than in the shell's banner.

          The HFW puts it there (`.main > .phead`), and `main` is the subtree the parity harness compares,
          so the band has to render here for the shell to match its wireframe. Owner ruling 2026-10-27:
          the wizard is the one surface whose `h1` sits in `main`; every other page keeps `PageHeader`
          in the shell's `#head` and its heading in the banner. The shell layout is not edited.

          A step that renders `PageHeader` (1 and 6) supplies the band through `#head` and the component
          carries its own markers; the other five fill `#eyebrow`/`#title` and the band below draws the
          same marker skeleton around them. Either way the position is visible text in
          `.hrow-actions > .hrow-position`, which is where the wireframe puts it.
        -->
        <template v-if="$slots.head"><slot name="head" /></template>
        <div v-else data-region="phead">
            <p
                v-if="$slots.eyebrow"
                data-region="eyebrow"
                class="text-xs font-extrabold uppercase tracking-[0.12em] text-ink-muted"
            >
                <slot name="eyebrow" />
            </p>
            <div data-region="hrow" class="flex flex-wrap items-end justify-between gap-x-5 gap-y-3">
                <h1
                    tabindex="-1"
                    class="mt-[7px] min-w-0 truncate text-[28px] font-extrabold leading-[1.12] tracking-[-0.01em] text-ink-strong"
                >
                    <slot name="title" />
                </h1>
                <div data-region="hrow-actions" class="flex flex-wrap items-center gap-2 pb-1 md:shrink-0">
                    <span data-region="hrow-position" class="text-xs text-ink-muted">Step {{ props.step }} of {{ steps.length }}</span>
                    <slot name="actions" />
                </div>
            </div>
            <div class="mt-4 border-t border-rule-soft" aria-hidden="true" data-region="hr" />
        </div>

        <!-- The step list is the wireframe's stepper: a status readout, not a second navigation.
             `aria-current="step"` carries the position for assistive tech (WCAG 1.4.1), and the four
             legend marks name the states so "amber means you started this one" is written down rather
             than inferred from a colour. The `.step`/`.step-legend` rules are ported into `app.css`,
             so this template states structure and never a colour.

             `md:sticky` was here and is REMOVED: it never did anything. This nav sits inside the
             `AppLayout` `main`, which carries `container-type: inline-size` (load-bearing for the
             `@3xl:`/`@4xl:` step layouts - see the note on `wrapperClass` above), so the sticky
             containing block moves with the page instead of the scrollport and the nav scrolled away
             with everything else. `tests/browser/containment.spec.ts` pins the current behaviour. -->
        <div :class="wrapperClass" class="canvas @container" data-layout-container data-region="canvas">
            <nav aria-label="Setup steps" class="mb-4">
                <!--
                  One exit, one label, in the wireframe's position: the HFW's stepper row carries the six
                  steps and nothing else, and the only leave-wizard control it draws is the head band's
                  `Cancel` (`career-scenario.html:63`). The extra `Leave setup` link that used to sit at
                  the end of this row printed a second name for the same destination on every step and
                  appeared nowhere in the wireframe, so it is gone rather than restyled.
                -->
                <div class="stepper" role="list" data-setup-steps>
                    <span
                        v-for="(item, index) in steps"
                        :key="item.label"
                        role="listitem"
                        :class="['step', ...stepClass(index)]"
                        :aria-current="index + 1 === props.step ? 'step' : undefined"
                        :aria-label="item.label"
                    >
                        <span class="n" aria-hidden="true">{{ index + 1 }}</span>{{ item.label }}
                    </span>
                </div>

                <div class="step-legend" aria-hidden="true">
                    <span><i class="l-done" />Saved</span>
                    <span><i class="l-partial" />Started unfinished</span>
                    <span><i class="l-empty" />Not started</span>
                    <span><i class="l-current" />Current</span>
                </div>
            </nav>

            <slot />
        </div>
    </AppLayout>
</template>
