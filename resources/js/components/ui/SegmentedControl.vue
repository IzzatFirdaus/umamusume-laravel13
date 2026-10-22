<script setup lang="ts">
/*
 * A row of mutually exclusive choices as one control (plan 1.8).
 *
 * It is real `<input type="radio">` elements inside a `<fieldset>` with a `<legend>`, not buttons with
 * `aria-pressed`. A radio group is a group: a screen reader announces "3 of 5" as the reader arrows
 * through, which a row of toggle buttons never says. Arrow keys move and select within the group for
 * free, and the browser handles the focus model.
 *
 * **Selection is a fill AND a check glyph** (WCAG 1.4.1). The fill alone is not enough: in a row of
 * three muted segments the selected one is the same shape as its neighbours and differs only by
 * luminance, which a reader with low contrast sensitivity cannot see. The check is the second signal.
 *
 * The radios are visually hidden but still rendered and focusable, and the visible segment is their
 * `<label>`. Hiding the input with `display: none` would remove it from the tab order and break the
 * group's keyboard behaviour; `sr-only` keeps it there. The focus ring is drawn on the label through
 * `:focus-visible` on the peer input, so the reader sees where they are.
 */
import { useId } from 'vue';

withDefaults(
    defineProps<{
        /** The group's name, rendered as the legend. */
        legend: string;
        options: { value: string; label: string }[];
        modelValue: string | null;
        name?: string;
        /** Renders the legend for screen readers only, when the page already prints it above. */
        hideLegend?: boolean;
    }>(),
    { name: undefined, hideLegend: false },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const groupName = `segmented-${useId()}`;
</script>

<template>
    <fieldset class="min-w-0">
        <legend :class="hideLegend ? 'sr-only' : 'mb-1 text-sm font-medium text-ink'">
            {{ legend }}
        </legend>
        <div class="inline-flex flex-wrap gap-1 rounded-[var(--radius-md)] border border-[var(--color-rule)] bg-[var(--color-sunken)] p-1">
            <template v-for="option in options" :key="option.value">
                <input
                    :id="`${groupName}-${option.value}`"
                    type="radio"
                    class="peer sr-only"
                    :name="name ?? groupName"
                    :value="option.value"
                    :checked="modelValue === option.value"
                    @change="emit('update:modelValue', option.value)"
                />
                <label
                    :for="`${groupName}-${option.value}`"
                    class="inline-flex min-h-11 cursor-pointer items-center gap-1.5 rounded px-3 text-sm text-ink transition-colors duration-150 ease-out hover:text-ink-strong peer-checked:bg-raised peer-checked:font-semibold peer-checked:text-ink-strong peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ring"
                >
                    <!-- The check is the second signal beside the fill. It is drawn only on the
                         selected segment, and is decorative because the radio state already says so. -->
                    <span v-if="modelValue === option.value" aria-hidden="true" class="font-mono text-xs">✓</span>
                    {{ option.label }}
                </label>
            </template>
        </div>
    </fieldset>
</template>
