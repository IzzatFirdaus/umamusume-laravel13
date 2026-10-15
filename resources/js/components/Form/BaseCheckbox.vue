<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: boolean | string | number;
    id: string;
    name: string;
    label?: string;
    value?: string | number;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    ariaDescribedby?: string;
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean | string | number];
    blur: [event: FocusEvent];
    focus: [event: FocusEvent];
}>();

const checkboxId = props.id;
const describedBy = computed(() => {
    const ids = [props.ariaDescribedby];
    if (props.error) {
        ids.push(`${props.id}-error`);
    }
    return ids.filter(Boolean).join(' ') || undefined;
});

const isChecked = computed(() => {
    if (typeof props.modelValue === 'boolean') {
        return props.modelValue;
    }
    return props.modelValue === props.value;
});

function onChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    if (props.value !== undefined) {
        emit('update:modelValue', target.checked ? props.value : false);
    } else {
        emit('update:modelValue', target.checked);
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
    <label v-if="label" :for="checkboxId" class="inline-flex min-h-11 items-center gap-2 cursor-pointer">
        <input
            :id="checkboxId"
            type="checkbox"
            :name="name"
            :value="value"
            :checked="isChecked"
            :required="required"
            :disabled="disabled"
            :aria-describedby="describedBy"
            :aria-invalid="error !== undefined"
            @change="onChange"
            @blur="onBlur"
            @focus="onFocus"
            class="size-6 rounded-md border-rule bg-raised text-chrome focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:opacity-60 disabled:cursor-not-allowed"
        />
        <span class="font-medium text-ink">{{ label }}</span>
    </label>
    <label v-else class="inline-flex min-h-11 items-center gap-2 cursor-pointer">
        <input
            :id="checkboxId"
            type="checkbox"
            :name="name"
            :value="value"
            :checked="isChecked"
            :required="required"
            :disabled="disabled"
            :aria-describedby="describedBy"
            :aria-invalid="error !== undefined"
            @change="onChange"
            @blur="onBlur"
            @focus="onFocus"
            class="size-6 rounded-md border-rule bg-raised text-chrome focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:opacity-60 disabled:cursor-not-allowed"
        />
        <slot />
    </label>
    <p v-if="hint" class="mt-1 text-sm text-ink-muted">{{ hint }}</p>
    <p v-if="error" :id="`${checkboxId}-error`" class="mt-1 text-sm text-risk" role="alert">{{ error }}</p>
</template>