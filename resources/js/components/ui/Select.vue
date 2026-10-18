<script setup lang="ts">
/*
 * The FormField family's `<select>` (plan 1.8).
 *
 * **It extends `BaseSelect.vue` rather than reimplementing it.** Twenty-five files use BaseSelect, and
 * its `:aria-required="required"` binding on both template branches is the fix `c2de34b` made for the
 * 0.6 slice. Reimplementing would mean re-deriving that behaviour and re-earning
 * `base-select.spec.ts`; inheriting it means the regression guard keeps testing the code that ships.
 *
 * What this adds over BaseSelect is only the family's prop names and defaults, so a page can move to
 * the family without changing the control underneath. When the last page migrates, BaseSelect can be
 * inlined here and the spec repointed, with no behaviour to re-prove.
 */
import BaseSelect from '../Form/BaseSelect.vue';

interface UiSelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

withDefaults(
    defineProps<{
        id: string;
        name: string;
        modelValue: string | number | null;
        options: UiSelectOption[];
        /** Visible label. Omitted only where `FormField` already prints it. */
        label?: string;
        placeholder?: string;
        hint?: string;
        error?: string;
        describedBy?: string;
        required?: boolean;
        disabled?: boolean;
        multiple?: boolean;
    }>(),
    {
        label: undefined,
        placeholder: undefined,
        hint: undefined,
        error: undefined,
        describedBy: undefined,
        required: false,
        disabled: false,
        multiple: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();
</script>

<template>
    <BaseSelect
        :id="id"
        :name="name"
        :model-value="modelValue"
        :options="options"
        :label="label"
        :placeholder="placeholder"
        :hint="hint"
        :error="error"
        :aria-describedby="describedBy"
        :required="required"
        :disabled="disabled"
        :multiple="multiple"
        @update:model-value="(value) => emit('update:modelValue', value)"
    />
</template>
