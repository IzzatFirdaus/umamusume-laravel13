<script setup lang="ts">
/*
 * The HFW's status pill (`.pill` in the wireframe's shell.css), ported onto house tokens.
 *
 * Three fills, three meanings, and no fourth: `battery` is battery A (lime), `accent` is the
 * recommendation violet, `neutral` is a quiet count. A pill is a word about state; it is never the
 * screen's one filled action, which is why no variant here is the action blue.
 *
 * Colour is never the only carrier (WCAG 1.4.1): the caller's text says what the fill means, so a
 * Trainer reading in greyscale still reads the sentence.
 */
defineProps<{
    tone?: 'battery' | 'accent' | 'neutral';
}>();
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-pill px-2.5 py-[3px] text-[11.5px] font-extrabold leading-[1.45]"
        :class="{
            'bg-chrome text-on-chrome': (tone ?? 'battery') === 'battery',
            'bg-brand-accent-soft text-brand-accent-ink': tone === 'accent',
            'bg-sunken text-ink': tone === 'neutral',
        }"
    >
        <slot />
    </span>
</template>
