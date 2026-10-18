<script setup lang="ts">
/*
 * The label, hint and error around one control (plan 1.8).
 *
 * A form control without a programmatic label is a control a screen reader announces as "edit text,
 * blank". This component owns the three relationships that make one:
 *
 *   - `for` -> the control's `id`, passed down through the `field` slot.
 *   - `aria-describedby` -> the hint and the error, so the reason a value was refused is read with
 *     the control rather than discovered later in a summary at the top of the page.
 *   - `role="alert"` on the error, so a refusal that arrives after a submit is announced without the
 *     reader having to move focus to find it.
 *
 * **Required is the default and is unmarked.** A form where every field is required does not need six
 * asterisks; it needs the exceptions named, so only optional fields carry "(optional)". An asterisk
 * on the default is a legend the reader has to learn and then ignore.
 *
 * The ids are derived from `useId()` rather than a prop, so two forms on one page cannot collide, and
 * the slot exposes them so the control inside can bind them without the caller threading strings.
 */
import { computed, useId } from 'vue';

const props = withDefaults(
    defineProps<{
        /** The field's visible name. Bound to the control through `for`. */
        label: string;
        /** Help text, read with the control through `aria-describedby`. */
        hint?: string;
        /** A refusal message. Rendered with `role="alert"`. */
        error?: string | null;
        /**
         * Whether a value is required. Default `true`. Only `false` appends "(optional)", because
         * required is the norm this form is written in.
         */
        required?: boolean;
        /** The control's `id`. Generated when omitted, and passed to the slot either way. */
        id?: string;
    }>(),
    { hint: undefined, error: null, required: true, id: undefined },
);

const generated = useId();
const fieldId = computed(() => props.id ?? `field-${generated}`);
const labelId = computed(() => `${fieldId.value}-label`);
const hintId = computed(() => `${fieldId.value}-hint`);
const errorId = computed(() => `${fieldId.value}-error`);

/** The ids a control must adopt. Two attributes, and the error joins them only when it exists. */
const describedBy = computed<string | undefined>(() => {
    const ids = [props.hint ? hintId.value : null, props.error ? errorId.value : null].filter(
        (value): value is string => value !== null,
    );

    return ids.length > 0 ? ids.join(' ') : undefined;
});

const suffix = computed(() => (props.required ? null : '(optional)'));
</script>

<template>
    <div class="flex flex-col gap-1">
        <label :for="fieldId" :id="labelId" class="text-sm font-medium text-ink">
            {{ label }}
            <!-- The word is the marker, not an asterisk: it needs no legend and reads in full. -->
            <span v-if="suffix" class="font-normal text-ink-muted">{{ suffix }}</span>
        </label>

        <slot
            :id="fieldId"
            :described-by="describedBy"
            :invalid="error !== null && error !== undefined"
            :required="required"
        />

        <p v-if="hint" :id="hintId" class="text-xs text-ink-muted">{{ hint }}</p>
        <p
            v-if="error"
            :id="errorId"
            role="alert"
            class="text-xs font-semibold text-risk"
        >
            {{ error }}
        </p>
    </div>
</template>
