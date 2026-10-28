<!--
 * The HFW's explanatory note (`.note`, shell.css:624-636): a dashed frame, one decorative bullet,
 * and a sentence the screen owes its reader. Used where the screen must say why a figure is absent
 * or why two of its own numbers disagree, rather than hiding that in a tooltip.
 *
 * The bullet is `aria-hidden`: "circle" is not a word a screen reader should read before the
 * sentence. It carries `ink-muted` rather than the faint tier, per the wireframe's own contrast note
 * (`ink-faint` must never carry a label).
 -->
<template>
    <p class="note flex items-start gap-2.5 rounded-sm border border-dashed border-rule bg-raised px-3 py-2.5 text-xs leading-[1.45] text-ink-muted">
        <span class="flex-none font-extrabold text-ink-muted" aria-hidden="true">○</span>
        <span><slot /></span>
    </p>
</template>
