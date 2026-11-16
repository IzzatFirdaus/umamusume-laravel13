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
    /**
     * Text for a button that has no default slot content. It renders as the visible label AND becomes
     * the accessible name, because a `label` attribute alone is not a name: axe's `button-name` rule
     * failed the disabled Compare control, which rendered as `<button label="Compare (0)">` with no
     * text at all. Call sites that pass a slot are unchanged — the slot still wins where both exist.
     */
    label?: string;
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
        <slot>{{ label }}</slot>
    </button>
</template>