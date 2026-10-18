<script setup lang="ts">
/*
 * A multi-line field (plan 1.8). Same wiring contract as `TextInput`: the ids come in as props from
 * `FormField`, and `aria-invalid` appears only when the value was actually refused.
 *
 * `rows` defaults to 4 rather than the browser's 2: every textarea this product has is a note or a
 * reason, and two rows make a note look like it wants one line.
 */
withDefaults(
    defineProps<{
        id: string;
        modelValue: string | null;
        name?: string;
        describedBy?: string;
        invalid?: boolean;
        required?: boolean;
        disabled?: boolean;
        placeholder?: string;
        rows?: number;
    }>(),
    {
        name: undefined,
        describedBy: undefined,
        invalid: false,
        required: false,
        disabled: false,
        placeholder: undefined,
        rows: 4,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

function onInput(event: Event): void {
    emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
}
</script>

<template>
    <textarea
        :id="id"
        :name="name"
        :value="modelValue ?? ''"
        :rows="rows"
        :aria-describedby="describedBy"
        :aria-invalid="invalid ? 'true' : undefined"
        :aria-required="required ? 'true' : 'false'"
        :required="required"
        :disabled="disabled"
        :placeholder="placeholder"
        class="block w-full rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink placeholder:text-ink-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:cursor-not-allowed disabled:bg-sunken disabled:text-ink-muted"
        @input="onInput"
    />
</template>
