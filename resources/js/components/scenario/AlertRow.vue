<script setup lang="ts">
/*
 * One glyph-plus-text notice. The shared shape for a line the scenario shell or a panel has to
 * state about itself: a fallback it cannot draw, an absence it carries, a warning it recorded.
 *
 * The glyph is `aria-hidden` and the text carries the meaning, so a reader who cannot see the
 * character loses nothing (never state through colour or shape alone). `tone` picks the token pair
 * and never the words.
 *
 * `critical` is design-2.0 §4's Level 1 and rides on **emphasis, not colour**: `--color-risk` is
 * reserved for an error state (a validation failure), and `--color-goal` is the client's Goal
 * pennant, which `TrainingRun` refuses to emit until a per-character goal table exists. A due
 * mandatory race is neither a fault nor a pennant, so it gets `text-ink-strong` plus weight, and
 * the level's own word travels in the text.
 */
withDefaults(
    defineProps<{
        /** The mark. `▲` for a critical line, `○` for a neutral note. */
        glyph: string;
        text: string;
        /** An extra reason for a `title`, when the sentence alone does not say why. */
        detail?: string | null;
        tone?: 'note' | 'critical';
    }>(),
    { detail: null, tone: 'note' },
);
</script>

<template>
    <p
        class="flex items-start gap-2 text-sm"
        :class="tone === 'critical' ? 'font-semibold text-ink-strong' : 'text-ink-muted'"
    >
        <span aria-hidden="true" class="font-mono">{{ glyph }}</span>
        <span>
            <span :title="detail ?? undefined">{{ text }}</span>
        </span>
    </p>
</template>
