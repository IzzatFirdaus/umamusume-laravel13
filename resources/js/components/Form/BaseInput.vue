<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string | number | File | null;
    type?: 'text' | 'number' | 'email' | 'password' | 'search' | 'file';
    id: string;
    name: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    min?: string | number;
    max?: string | number;
    step?: string | number;
    autocomplete?: string;
    accept?: string;
    ariaDescribedby?: string;
    ariaLabel?: string;
    title?: string;
    error?: string;
    list?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number | File | null];
    blur: [event: FocusEvent];
    focus: [event: FocusEvent];
    change: [event: Event];
}>();

const inputId = props.id;
const describedBy = computed(() => {
    // When there's an error, return ONLY the error ID to match the old inline behavior
    // where aria-describedby was exactly the error ID (e.g., "error-targets_Speed").
    if (props.error) {
        return `error-${props.id.replace(/^field-/, '')}`;
    }
    const ids = [props.ariaDescribedby];
    if (props.hint) {
        ids.push(`hint-${props.id.replace(/^field-/, '')}`);
    }
    return ids.filter(Boolean).join(' ') || undefined;
});

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    if (props.type === 'file') {
        emit('update:modelValue', target.files?.[0] ?? null);
        emit('change', event);
    } else {
        let value: string | number = target.value;
        if (props.type === 'number' && value !== '') {
            value = Number(value);
        }
        emit('update:modelValue', value);
    }
}

function onChange(event: Event): void {
    if (props.type === 'file') {
        emit('change', event);
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
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-ink">
        {{ label }}
<input
                :id="inputId"
                :type="type ?? 'text'"
                :name="name"
                :value="type === 'file' ? undefined : modelValue"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :readonly="readonly"
                :min="min"
                :max="max"
                :step="step"
                :autocomplete="autocomplete"
                :accept="accept"
                :list="list"
                :aria-label="ariaLabel"
                :title="title"
                :aria-describedby="describedBy"
                :aria-invalid="error !== undefined"
                @input="onInput"
                @change="onChange"
                @blur="onBlur"
                @focus="onFocus"
                class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:bg-sunken disabled:text-ink-muted disabled:cursor-not-allowed"
            />
    </label>
    <input
        v-else
        :id="inputId"
        :type="type ?? 'text'"
        :name="name"
        :value="type === 'file' ? undefined : modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="autocomplete"
        :accept="accept"
        :list="list"
        :aria-label="ariaLabel"
        :title="title"
        :aria-describedby="describedBy"
        :aria-invalid="error !== undefined"
        @input="onInput"
        @change="onChange"
        @blur="onBlur"
        @focus="onFocus"
        class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:bg-sunken disabled:text-ink-muted disabled:cursor-not-allowed"
    />
    <p v-if="hint" :id="`hint-${id.replace(/^field-/, '')}`" class="mt-1 text-sm text-ink-muted">{{ hint }}</p>
    <p v-if="error" :id="`error-${id.replace(/^field-/, '')}`" class="mt-1 text-sm text-risk" role="alert">{{ error }}</p>
</template>