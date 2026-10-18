<script setup lang="ts">
/*
 * A single checkbox with a visible label (plan 1.8).
 *
 * The label is a real `<label for>` rather than a wrapping one, so the control and its name are
 * associated by id the same way every other field in the family is. The hit target is padded to the
 * 44px floor (WCAG 2.5.8) on the label, not on the 20px box: a checkbox whose tappable area is the
 * box alone is a control Trainers miss on a phone.
 */
withDefaults(
    defineProps<{
        id: string;
        /** The visible name, printed beside the box. */
        label: string;
        modelValue: boolean;
        name?: string;
        describedBy?: string;
        invalid?: boolean;
        disabled?: boolean;
    }>(),
    { name: undefined, describedBy: undefined, invalid: false, disabled: false },
);

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

function onChange(event: Event): void {
    emit('update:modelValue', (event.target as HTMLInputElement).checked);
}
</script>

<template>
    <label
        :for="id"
        class="inline-flex min-h-11 cursor-pointer items-center gap-2 text-sm text-ink"
    >
        <input
            :id="id"
            type="checkbox"
            :name="name"
            :checked="modelValue"
            :aria-describedby="describedBy"
            :aria-invalid="invalid ? 'true' : undefined"
            :disabled="disabled"
            class="size-5 shrink-0 rounded border border-rule text-brand-chrome focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:cursor-not-allowed"
            @change="onChange"
        />
        <span>{{ label }}</span>
    </label>
</template>
