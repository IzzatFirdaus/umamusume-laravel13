<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string | number | null;
    id: string;
    name: string;
    label?: string;
    value: string | number;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    ariaDisabled?: boolean;
    ariaDescribedby?: string;
    error?: string;
    title?: string;
    hintId?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
    blur: [event: FocusEvent];
    focus: [event: FocusEvent];
}>();

const radioId = props.id;
const describedBy = computed(() => {
    const ids = [props.ariaDescribedby];
    if (props.error) {
        ids.push(`${props.id}-error`);
    }
    return ids.filter(Boolean).join(' ') || undefined;
});

const isChecked = computed(() => props.modelValue === props.value);

function onChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    if (target.checked) {
        emit('update:modelValue', props.value);
    }
}

function onBlur(event: FocusEvent): void {
    emit('blur', event);
}

function onFocus(event: FocusEvent): void {
    emit('focus', event);
}
</script>

<template>
    <label v-if="label" :for="radioId" class="inline-flex min-h-11 items-center gap-2 cursor-pointer">
        <input
            :id="radioId"
            type="radio"
            :name="name"
            :value="value"
            :checked="isChecked"
            :required="required"
            :disabled="disabled"
            :aria-disabled="ariaDisabled"
            :aria-describedby="describedBy"
            :aria-invalid="error !== undefined"
            :title="title"
            @change="onChange"
            @blur="onBlur"
            @focus="onFocus"
            class="size-5 rounded-full border-rule bg-raised text-chrome focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:opacity-60 disabled:cursor-not-allowed"
        />
        <span class="font-medium text-ink">{{ label }}</span>
    </label>
    <label v-else class="inline-flex min-h-11 items-center gap-2 cursor-pointer">
        <input
            :id="radioId"
            type="radio"
            :name="name"
            :value="value"
            :checked="isChecked"
            :required="required"
            :disabled="disabled"
            :aria-disabled="ariaDisabled"
            :aria-describedby="describedBy"
            :aria-invalid="error !== undefined"
            :title="title"
            @change="onChange"
            @blur="onBlur"
            @focus="onFocus"
            class="size-5 rounded-full border-rule bg-raised text-chrome focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:opacity-60 disabled:cursor-not-allowed"
        />
        <slot />
    </label>
    <p v-if="hint" :id="hintId" class="mt-1 text-sm text-ink-muted">{{ hint }}</p>
    <p v-if="error" :id="`${radioId}-error`" class="mt-1 text-sm text-risk" role="alert">{{ error }}</p>
</template>