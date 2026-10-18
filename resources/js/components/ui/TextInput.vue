<script setup lang="ts">
/*
 * A text field that takes its wiring from `FormField` (plan 1.8).
 *
 * The three attributes that matter are `id`, `aria-describedby` and `aria-invalid`, and all three are
 * bound props rather than inherited ones: a control that reads them from context would break the
 * moment it is used outside a FormField, and would fail silently rather than loudly.
 *
 * `aria-invalid` is emitted only when the field is actually invalid, never as a constant `false`:
 * a control that always claims to be valid carries no information, and `aria-invalid="true"` is what
 * tells a screen reader the value it just heard was refused.
 *
 * The 44px height is `min-h-11`, the same floor as every other control in the shell (WCAG 2.5.8).
 * The focus ring comes from the `ring` token rather than a raw colour so a theme change moves it.
 */
withDefaults(
    defineProps<{
        id: string;
        modelValue: string | number | null;
        type?: 'text' | 'email' | 'search' | 'tel' | 'url';
        name?: string;
        describedBy?: string;
        invalid?: boolean;
        required?: boolean;
        disabled?: boolean;
        placeholder?: string;
        autocomplete?: string;
        /** Caps input to accepted values before submit rather than after a refusal. */
        inputmode?: 'text' | 'numeric' | 'decimal';
    }>(),
    {
        type: 'text',
        name: undefined,
        describedBy: undefined,
        invalid: false,
        required: false,
        disabled: false,
        placeholder: undefined,
        autocomplete: undefined,
        inputmode: undefined,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

function onInput(event: Event): void {
    emit('update:modelValue', (event.target as HTMLInputElement).value);
}
</script>

<template>
    <input
        :id="id"
        :type="type"
        :name="name"
        :value="modelValue ?? ''"
        :aria-describedby="describedBy"
        :aria-invalid="invalid ? 'true' : undefined"
        :aria-required="required ? 'true' : 'false'"
        :required="required"
        :disabled="disabled"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        :inputmode="inputmode"
        class="block min-h-11 w-full rounded-md border border-rule bg-raised px-3 text-sm text-ink placeholder:text-ink-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:cursor-not-allowed disabled:bg-sunken disabled:text-ink-muted"
        @input="onInput"
    />
</template>
