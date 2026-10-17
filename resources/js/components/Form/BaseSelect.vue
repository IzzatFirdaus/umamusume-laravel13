<script setup lang="ts">
import { computed } from 'vue';

interface Option {
    value: string | number;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue: string | number | null;
    id: string;
    name: string;
    label?: string;
    options: Option[];
    placeholder?: string;
    hint?: string;
    title?: string;
    ariaLabel?: string;
    required?: boolean;
    disabled?: boolean;
    multiple?: boolean;
    ariaDescribedby?: string;
    labelId?: string;
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
    blur: [event: FocusEvent];
    focus: [event: FocusEvent];
}>();

const selectId = props.id;
const labelId = props.labelId ?? `${selectId}-label`;
const describedBy = computed(() => {
    // When there's an error, return ONLY the error ID to match the old inline behavior
    // where aria-describedby was exactly the error ID (e.g., "error-targets_Speed").
    if (props.error) {
        return `${props.id}-error`;
    }
    return props.ariaDescribedby;
});

function onChange(event: Event): void {
    const target = event.target as HTMLSelectElement;
    if (props.multiple) {
        const values = Array.from(target.selectedOptions).map((opt) => opt.value);
        emit('update:modelValue', values as string | number | null);
    } else {
        emit('update:modelValue', target.value || null);
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
    <div v-if="label" class="flex flex-col gap-1">
        <label :for="selectId" :id="`${selectId}-label`" class="text-sm font-medium text-ink">
            <span data-testid="base-select-label">{{ label }}</span>
        </label>
        <select
            :id="selectId"
            :name="name"
            :title="title"
            :aria-label="ariaLabel"
            :aria-labelledby="ariaLabel ? undefined : `${selectId}-label`"
            :value="modelValue"
            :required="required"
            :aria-required="required"
            :disabled="disabled"
            :multiple="multiple"
            :aria-describedby="describedBy"
            :aria-invalid="error !== undefined"
            @change="onChange"
            @blur="onBlur"
            @focus="onFocus"
            class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:bg-sunken disabled:text-ink-muted disabled:cursor-not-allowed"
        >
            <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
                :disabled="option.disabled"
            >{{ option.label }}</option>
        </select>
    </div>
    <select
        v-else
        :id="selectId"
        :name="name"
        :title="title"
        :aria-label="ariaLabel"
        :value="modelValue"
        :required="required"
        :aria-required="required"
        :disabled="disabled"
        :multiple="multiple"
        :aria-describedby="describedBy"
        :aria-invalid="error !== undefined"
        @change="onChange"
        @blur="onBlur"
        @focus="onFocus"
        class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:bg-sunken disabled:text-ink-muted disabled:cursor-not-allowed"
    >
        <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
            :disabled="option.disabled"
        >{{ option.label }}</option>
    </select>
    <p v-if="hint" class="mt-1 text-sm text-ink-muted">{{ hint }}</p>
    <p v-if="error" :id="`${selectId}-error`" class="mt-1 text-sm text-risk" role="alert">{{ error }}</p>
</template>