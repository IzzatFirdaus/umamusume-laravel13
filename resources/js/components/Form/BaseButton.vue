<script setup lang="ts">
import { computed } from 'vue';
import { buttonClasses, type ButtonSize, type ButtonVariant } from './buttonStyles';

interface Props {
    type?: 'button' | 'submit' | 'reset';
    variant?: ButtonVariant;
    size?: ButtonSize;
    disabled?: boolean;
    loading?: boolean;
    ariaDescribedby?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    click: [event: MouseEvent];
}>();

const classes = computed(() => buttonClasses(props.variant, props.size));
const variant = computed(() => props.variant ?? 'primary');

function onClick(event: MouseEvent): void {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
}
</script>

<template>
    <button
        :type="type ?? 'button'"
        :disabled="disabled || loading"
        :aria-describedby="ariaDescribedby"
        :aria-busy="loading"
        @click="onClick"
        :class="classes"
        :data-variant="variant"
    >
        <slot />
    </button>
</template>