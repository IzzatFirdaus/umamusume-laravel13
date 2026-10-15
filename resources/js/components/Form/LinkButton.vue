<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { buttonClasses, type ButtonSize, type ButtonVariant } from './buttonStyles';

interface Props {
    href: string;
    variant?: ButtonVariant;
    size?: ButtonSize;
}

const props = defineProps<Props>();

const classes = computed(() => buttonClasses(props.variant, props.size));
// The effective variant, echoed as a data attribute: the browser gate counts filled primaries by
// `[data-variant="primary"]`, and an omitted prop resolves to `primary` inside `buttonClasses()`.
const variant = computed(() => props.variant ?? 'primary');
</script>

<template>
    <Link :href="props.href" :class="classes" :data-variant="variant">
        <slot />
    </Link>
</template>
