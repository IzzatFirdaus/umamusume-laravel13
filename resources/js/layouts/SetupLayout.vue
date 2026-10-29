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
 * answered one level above the page rather than competing with the product destinations. It sits in
 * the head band's right toolbar rather than in the step body; the HFW puts it in `.canvas`
 * (`career-scenario.html:70-85`) and the owner moved it, so that position is recorded as an
 * `HFW-OVERRIDE (structure: wizard toolbar)` on the band below.
 *
 * A step whose slice has not landed is a named absence (`to: null`), the same rule `AppLayout`
 * `items[]` uses. All six steps are live: the ancestry step (D5-wizard) and the deck step (D6-wizard)
 * carry their values in the session draft rather than on a run, and Preflight (D7) composes that draft
 * and creates the run. The `to: null` branch stays because the next wizard slice that lands a step ahead
 * of its own screen needs it, and because a named absence is the honest answer rather than a dead link.
 */
import AppLayout from './AppLayout.vue';
import { onMounted, nextTick } from 'vue';
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

// The wizard honours the same width preference as the shell through `AppLayout`'s `main`, which
// already carries the mode's max-width and padding. This wrapper re-stated the same cap, which
// nested a second max-width inside the first and made the step body narrower than the head band by
// the shell's own gutter; it also re-declared `container-type: inline-size` so an inner step could lay
// itself out off its own resolved width. The class stays because `.canvas` is the HFW's body marker
// (`career-scenario.html:70`) and `data-layout-container` is what `containment.spec.ts` reads.
const { mode } = useLayout();

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

    // The wizard draws the heading in its own head band (inside `main`), from the step's `#title`
    // slot. `#title` is a slot NAME, not an element id: the first version queried `#head h1, #title
    // h1` and matched nothing, which the spec caught as a missing tabindex rather than a missing
    // heading. The heading is simply the page's one `h1`, which `page-header.spec.ts` already
    // guarantees on every pilot page.
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
          The head band, drawn INSIDE the main subtree rather than in the shell's banner.

          The HFW puts it there (`.main > .phead`), and `main` is the subtree the parity harness
          compares, so the band has to render here for the shell to match its wireframe. Owner ruling
          2026-10-27: the wizard is the one surface whose `h1` sits in `main`; every other page keeps
          `PageHeader` in the shell's `#head` and its heading in the banner. The shell layout is not
          edited.

          Every step supplies `#eyebrow`/`#title`/`#lede`/`#actions`; the band draws one skeleton around
          them, so a step cannot render a second, differently shaped head.

          HFW-OVERRIDE (structure: wizard toolbar) - owner ruling 2026-10-28: the six step chips and
          their legend leave the step body and join `Step N of 6`, Cancel and Continue in one
          right-aligned toolbar inside `hrow-actions`, above the rule. The wireframe draws the chips in
          `canvas > .stepper` (`career-scenario.html:70-85`) and puts only the position and the two
          buttons beside the title (`:61-65`). The band keeps the wireframe's
          `phead > eyebrow + hrow + hr` skeleton; `tests/browser/setup-layout.spec.ts` excludes the
          three stepper markers from the band comparison and asserts this placement instead of the
          body position.
        -->
        <div data-region="phead">
            <!-- `text-xs` rather than the wireframe's 11px (`shell.css:361`): DESIGN.md §2.2's
                 owner-signed Metadata tier is `text-xs text-ink-muted`, and the visual system outranks
                 a prototype sheet. HFW-OVERRIDE (computed: eyebrow size). -->
            <p
                v-if="$slots.eyebrow"
                data-region="eyebrow"
                class="text-xs font-extrabold uppercase tracking-[0.12em] text-ink-muted"
            >
                <slot name="eyebrow" />
            </p>
            <div data-region="hrow" class="wizard-hrow">
                <!-- The title and its lede are one block, the actions the other, so the band keeps the
                     wireframe's shape: `.hrow > [div > h1 + p.lede] + .hrow-actions` (`career-trainee.html:56-66`).
                     Putting the lede directly in the band as a sibling of the pair pushed the actions onto
                     a line of their own at every width; the band's own column behaviour lives in
                     `.wizard-hrow` in `app.css`, which is where the compact stacking decision is recorded. -->
                <div class="min-w-0">
                    <h1
                        tabindex="-1"
                        class="mt-[7px] min-w-0 text-[28px] font-extrabold leading-[1.12] tracking-[-0.01em] text-ink-strong focus:outline-none"
                    >
                        <slot name="title" />
                    </h1>
                    <!-- The wireframe's lede belongs to the title band, not to the step body
                         (`career-trainee.html:58-59`: `.phead > .hrow > div > h1 + p.lede`). A step that
                         supplies it gets it under its own heading. The 13px size and the 66ch measure are
                         the wireframe's (`shell.css:364`), and compact tightens the measure to 60ch
                         exactly as `shell.css:414` does - both are tokens in `app.css`, because no stock
                         utility carries either number. -->
                    <p
                        v-if="$slots.lede"
                        data-region="lede"
                        class="mt-1.25 text-lede text-ink-muted"
                        :class="mode === 'compact' ? 'max-w-lede-compact' : 'max-w-lede'"
                    >
                        <slot name="lede" />
                    </p>
                </div>

                <!--
                  The single right toolbar (owner ruling 2026-10-28). Reading order is the reading a
                  Trainer does: where am I (six chips), what do they mean (legend), where am I exactly
                  (position), what moves me (the step's own pair). The chips are status spans under
                  `role="list"`, the current one carries `aria-current="step"` (WCAG 1.4.1: colour never
                  carries it alone), and the four legend marks name the states so "amber means you
                  started this one" is written down rather than inferred from a colour. `.wizard-hrow`,
                  `.stepper` and `.step-legend` carry the placement and ported rules in `app.css`, so
                  this template states structure and never a colour or a margin.
                -->
                <div data-region="hrow-actions" class="flex min-w-0 flex-col gap-2">
                    <nav aria-label="Setup steps" class="flex flex-col gap-1.5">
                        <!--
                          One exit, one label, in the wireframe's position: the HFW's stepper row carries the six
                          steps and nothing else, and the only leave-wizard control it draws is the head band's
                          `Cancel` (`career-scenario.html:63`). The extra exit link that used to sit at the end of
                          this row printed a second name for the same destination on every step and appeared nowhere
                          in the wireframe, so it is gone rather than restyled.
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

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <span data-region="hrow-position" class="text-xs text-ink-muted">Step {{ props.step }} of {{ steps.length }}</span>
                        <slot name="actions" />
                    </div>
                </div>
            </div>
            <div class="mt-4 border-t border-rule-soft" aria-hidden="true" data-region="hr" />
        </div>

        <!-- The step body. The stepper is no longer in here: it moved to the toolbar above the rule.
             `md:sticky` was never here on this wrapper, and the nav that carried it is gone from the
             body entirely, so the sticky note that used to guard `main`'s `container-type` no longer
             applies to a stepper. `containment.spec.ts` still pins the container's behaviour. -->
        <div class="canvas @container" data-layout-container data-region="canvas">
            <slot />
        </div>
    </AppLayout>
</template>
