<script setup lang="ts">
/*
 * The one surface primitive (DESIGN.md §2.3 surfaces, §5 empty states). A Card is `raised` on the
 * `page` field: a white sheet with a 1px rule and the ratified medium radius, and no shadow. The
 * overlay shadow is reserved for things that float over the page (`--shadow-overlay`), so a static
 * card that casts one claims a layer it does not occupy.
 *
 * Padding is a prop with two allowed values rather than a free class list: `md` (16px) is the
 * default and the dense form, `lg` (20px) is for a card that holds a form or a single sentence worth
 * of emphasis. Handing the caller arbitrary padding invites the drift the primitive exists to stop.
 *
 * `as` lets a card be a `section` or a `li` without a wrapper element, so the primitive never forces
 * an extra `<div>` into a list or a landmark structure.
 */
withDefaults(
    defineProps<{
        /** Padding step. `md` is 16px, `lg` is 20px. */
        padding?: 'md' | 'lg';
        /** The rendered element. Defaults to a plain `div`. */
        as?: string;
    }>(),
    { padding: 'md', as: 'div' },
);
</script>

<template>
    <component
        :is="as"
        :class="[
            'card',
            padding === 'lg' ? 'p-5' : 'p-4',
        ]"
    >
        <slot />
    </component>
</template>
