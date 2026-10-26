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
        /** Optional small caps line above the name, e.g. "Overview". */
        eyebrow?: string;
        /** Optional one-line sentence under the name, held to a readable measure. */
        description?: string;
    }>(),
    { eyebrow: undefined, description: undefined },
);
</script>

<template>
    <div class="flex flex-wrap items-end justify-between gap-x-5 gap-y-3">
        <div class="min-w-0 max-w-prose">
            <p
                v-if="eyebrow"
                class="text-xs font-extrabold uppercase tracking-[0.12em] text-ink-muted"
            >
                {{ eyebrow }}
            </p>
            <!-- `tabindex="-1"` so a route change can move focus here rather than leaving it on the
                 link that was clicked; the heading is not in the tab order otherwise (WCAG 2.4.3).
                 No `focus:outline-none`: a focused element must never lose its indicator (WCAG 2.4.7),
                 and the app-wide `:focus-visible` ring in app.css already draws it. -->
            <h1 tabindex="-1" class="mt-[7px] truncate text-[28px] font-extrabold leading-[1.12] tracking-[-0.01em] text-ink-strong">
                {{ title }}
            </h1>
            <p v-if="description" class="mt-[5px] text-[13px] text-ink-muted">
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2 pb-1 md:shrink-0">
            <slot name="actions" />
        </div>
        <!-- The HFW's hairline closes the head band (`.phead .hr`): 16px of air, then one rule. -->
        <div class="mt-4 basis-full border-t border-rule-soft" aria-hidden="true" data-page-head-rule />
    </div>
</template>
