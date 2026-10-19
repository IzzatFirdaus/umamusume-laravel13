<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string;
    id: string;
    name: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    rows?: number;
    cols?: number;
    /**
     * Declared rather than left to attribute fallthrough: with a `label` the root element is the
     * `<label>`, so an undeclared `maxlength` lands on the wrapper and the textarea stops enforcing it.
     */
    maxlength?: number | string;
    ariaDescribedby?: string;
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    blur: [event: FocusEvent];
    focus: [event: FocusEvent];
}>();

const textareaId = props.id;
const describedBy = computed(() => {
    const ids = [props.ariaDescribedby];
    if (props.error) {
        ids.push(`${props.id}-error`);
    }
    return ids.filter(Boolean).join(' ') || undefined;
});

function onInput(event: Event): void {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
}

function onBlur(event: FocusEvent): void {
    emit('blur', event);
}

function onFocus(event: FocusEvent): void {
    emit('focus', event);
}
</script>

<template>
    <label v-if="label" :for="textareaId" class="block text-sm font-medium text-ink">
        {{ label }}
        <textarea
            :id="textareaId"
            :name="name"
            :value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :rows="rows ?? 4"
            :cols="cols"
            :maxlength="maxlength"
            :aria-describedby="describedBy"
            :aria-invalid="error !== undefined"
            @input="onInput"
            @blur="onBlur"
            @focus="onFocus"
            class="mt-1 block w-full min-h-[88px] rounded-md border border-rule bg-raised px-2 text-ink resize-y focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:bg-sunken disabled:text-ink-muted disabled:cursor-not-allowed"
        />
    </label>
    <textarea
        v-else
        :id="textareaId"
        :name="name"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :rows="rows ?? 4"
        :cols="cols"
        :maxlength="maxlength"
        :aria-describedby="describedBy"
        :aria-invalid="error !== undefined"
        @input="onInput"
        @blur="onBlur"
        @focus="onFocus"
        class="mt-1 block w-full min-h-[88px] rounded-md border border-rule bg-raised px-2 text-ink resize-y focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring transition-colors duration-150 ease-out disabled:bg-sunken disabled:text-ink-muted disabled:cursor-not-allowed"
    />
    <p v-if="hint" class="mt-1 text-sm text-ink-muted">{{ hint }}</p>
    <p v-if="error" :id="`${textareaId}-error`" class="mt-1 text-sm text-risk" role="alert">{{ error }}</p>
</template>