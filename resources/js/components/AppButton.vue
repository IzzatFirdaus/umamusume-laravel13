<script setup lang="ts">
/*
 * Full literal class strings, keyed rather than interpolated: Tailwind v4 scans sources for
 * complete class names, so "bg-{variant}" compiles to nothing. Every variant carries
 * `min-h-11` (44px), the control size contract KI-29 / KI-37 measured against.
 */
const props = withDefaults(
    defineProps<{
        variant?: 'primary' | 'secondary' | 'danger' | 'banner' | 'discipline';
        href?: string | null;
        type?: 'button' | 'submit' | 'reset';
        // The 1..5 key hint printed on the Discipline cap; ignored by every other variant.
        shortcut?: string | null;
    }>(),
    {
        variant: 'primary',
        href: null,
        type: 'button',
        shortcut: null,
    },
);

const variants: Record<string, string> = {
    primary: 'enamel min-h-11 rounded-full bg-chrome px-5 font-bold text-on-chrome',
    secondary: 'min-h-11 rounded-full border-2 border-rule px-4 font-bold text-ink-strong',
    danger: 'min-h-11 rounded-full bg-risk px-4 font-bold text-on-chrome',
    // Banner: rectangular with a 40px right wedge cap, so `pr-12` clears the cap.
    banner: 'enamel relative min-h-11 rounded-md bg-chrome pr-12 pl-4 font-bold text-on-chrome',
};
</script>

<template>
    <!-- Round Discipline button: 64px, a 4px green ring over a deeper 2px outer ring. -->
    <button
        v-if="variant === 'discipline'"
        :type="type"
        class="relative grid size-16 place-items-center rounded-full border-4 border-green bg-raised font-bold text-ink-strong ring-2 ring-green-deep"
    >
        <slot />
        <span
            v-if="shortcut !== null"
            class="absolute -right-1 -bottom-1 grid size-5 place-items-center rounded-full bg-chrome font-mono text-xs font-bold text-on-chrome"
            aria-hidden="true"
        >{{ shortcut }}</span>
    </button>
    <a v-else-if="href !== null" :href="href" :class="variants[variant]">
        <slot />
        <span
            v-if="variant === 'banner'"
            class="absolute inset-y-0 right-0 grid w-10 place-items-center rounded-r-md bg-green-deep text-on-chrome"
            aria-hidden="true"
        >›</span>
    </a>
    <button v-else :type="type" :class="variants[variant]">
        <slot />
        <span
            v-if="variant === 'banner'"
            class="absolute inset-y-0 right-0 grid w-10 place-items-center rounded-r-md bg-green-deep text-on-chrome"
            aria-hidden="true"
        >›</span>
    </button>
</template>
