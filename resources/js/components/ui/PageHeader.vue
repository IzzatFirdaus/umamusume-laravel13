<script setup lang="ts">
/*
 * The page's own name (plan 1.3). One `h1`, drawn on the page background at the owner-signed
 * Critical tier (`text-3xl font-semibold text-ink-strong`, DESIGN.md §2.2's 2026-10-14 amendment).
 * The plan text asked for 28px; that size has no token and the signed ladder is 12/14/18/30, so the
 * signed 30px `text-3xl` ships and the 28px figure stays with the owner.
 *
 * It lives in the page rather than in the shell because the dispatch that introduced it measured the
 * bar: nineteen pages put an eyebrow above a 30px heading, and neither fits in a 48px row. The shell
 * therefore keeps a title band for the pages that have not adopted this component yet, and a page
 * that renders `PageHeader` stops filling the band so exactly one `h1` reaches the document.
 *
 * `title` is required and is the only required prop: a header with no name is a layout element, not a
 * page header, and the guard tests refuse a route without exactly one `h1` anyway.
 */
withDefaults(
    defineProps<{
        /** The page's name. Rendered once, as the `h1`. */
        title: string;
        /**
         * The Japanese name beside the client string, when the screen has both. Rendered inside the `h1`
         * as its own `lang="ja"` span so a screen reader switches voice (WCAG 3.1.2); omitted means the
         * header renders byte-identically to before.
         */
        titleJa?: string;
        /** Optional small caps line above the name, e.g. "Overview". */
        eyebrow?: string;
        /** Optional one-line sentence under the name, held to a readable measure. */
        description?: string;
        /**
         * The step position, e.g. "Step 1 of 6". The wizard supplies it: the HFW puts the position in
         * the head band's action row (`.hrow-actions > .hrow-position`) rather than in a save bar, and
         * the parity harness reads it there.
         */
        position?: string;
    }>(),
    { eyebrow: undefined, description: undefined, position: undefined, titleJa: undefined },
);
</script>

<template>
    <!--
      The HFW's head band shape: `.phead > .eyebrow, .hrow, .hr` (`.hrow > [h1 + .lede], .hrow-actions`).
      The markers are `data-region` because the app draws this band with Tailwind utilities rather than
      the corpus' classes, and the wizard parity harness compares rendered markers, not Tailwind strings
      (`tests/browser/fixtures/hfw-structure.ts`).

      The eyebrow is a sibling ABOVE `.hrow`, not a line inside the title block: the wireframe draws it
      that way and the harness compares marker order, so nesting it would report a false divergence. The
      visual result is unchanged (the eyebrow is a full-width 12px line either way and the h1 keeps its
      7px offset).
    -->
    <div data-region="phead">
        <p
            v-if="eyebrow"
            data-region="eyebrow"
            class="text-xs font-extrabold uppercase tracking-[0.12em] text-ink-muted"
        >
            {{ eyebrow }}
        </p>
        <div data-region="hrow" class="flex flex-wrap items-end justify-between gap-x-5 gap-y-3">
            <div class="min-w-0 max-w-prose">
                <!-- `tabindex="-1"` so a route change can move focus here rather than leaving it on the
                     link that was clicked; the heading is not in the tab order otherwise (WCAG 2.4.3).
                     `focus:outline-none` keeps a non-operable heading from drawing a control ring
                     (NRM-1); the layout's focus move still announces the new page to assistive tech. -->
                <h1 tabindex="-1" class="mt-[7px] min-w-0 text-[28px] font-extrabold leading-[1.12] tracking-[-0.01em] text-ink-strong focus:outline-none">
                    {{ title }}<span v-if="titleJa" lang="ja" class="ml-2 text-ink-muted">{{ titleJa }}</span>
                </h1>
                <p v-if="description" data-region="lede" class="mt-[5px] text-[13px] text-ink-muted">
                    {{ description }}
                </p>
            </div>
            <div
                v-if="$slots.actions || position"
                data-region="hrow-actions"
                class="flex flex-wrap items-center gap-2 pb-1 md:shrink-0"
            >
                <span v-if="position" data-region="hrow-position" class="text-xs text-ink-muted">{{ position }}</span>
                <slot name="actions" />
            </div>
        </div>
        <!-- The HFW's hairline closes the head band (`.phead .hr`): 16px of air, then one rule. -->
        <div class="mt-4 border-t border-rule-soft" aria-hidden="true" data-page-head-rule data-region="hr" />
    </div>
</template>
