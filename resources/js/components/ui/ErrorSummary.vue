<script setup lang="ts">
/*
 * Error summary (GOV.UK "Recover from validation errors" shape, plan 5.1).
 *
 * A failed submit needs two halves: the per-field message, which FormField already owns through
 * `aria-describedby`, and a summary at the top of the form that takes focus and names every
 * invalid field. This component is the second half.
 *
 * It renders nothing when there is nothing to say, so an ordinary successful page carries no
 * landmark. When errors appear it moves focus to itself, and every entry is a link that moves
 * focus to the control it names - each caller maps its form error key to the control's own id,
 * so no caller has to invent one.
 *
 * The wording of each entry mirrors the field's own message: the summary repeats it rather
 * than paraphrasing, because a reader who heard the field-level refusal once and then jumped
 * to the summary should not meet a different sentence.
 */
import { computed, nextTick, ref, useId, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        /**
         * The visible heading. Defaults to a count-aware sentence, which is the wording the
         * GOV.UK pattern prescribes: one problem, several problems.
         */
        heading?: string;
        /**
         * One entry per invalid field. `id` must match the control's own id when the refusal belongs
         * to one control. A stale-draft refusal can name a whole section with no single field, and
         * that entry passes `null` and prints without a link rather than pointing at nothing.
         */
        entries: { id: string | null; label: string; message: string }[];
        /**
         * Move focus to the summary when it appears. Off on forms that already own a
         * first-field focus convention, so the summary does not steal focus from the control.
         */
        autoFocus?: boolean;
    }>(),
    { heading: undefined, autoFocus: true },
);

const element = ref<HTMLDivElement | null>(null);
const generated = useId().replace(/[^a-zA-Z0-9]/g, '');
const headingId = `error-summary-heading-${generated}`;

const visibleHeading = computed(() => {
    if (props.heading !== undefined) {
        return props.heading;
    }

    return props.entries.length === 1
        ? 'One field needs attention'
        : `${props.entries.length} fields need attention`;
});

// Focus the summary when it appears, so a keyboard user lands on the problem rather than
// hunting for it. Watch the length rather than the array reference: a form that clears one
// error and gains another keeps the same count and should not steal focus back.
watch(
    () => props.entries.length,
    (current, previous) => {
        if (props.autoFocus && current > 0 && current !== previous) {
            nextTick(() => element.value?.focus());
        }
    },
    { immediate: true },
);

/**
 * Move focus to the control an entry names. The `href` alone only scrolls; the click is what puts
 * the caret or selection in the field, which is the recovery the pattern asks for.
 */
const focusField = (id: string): void => {
    const field = document.getElementById(id);

    if (!(field instanceof HTMLElement)) {
        return;
    }

    field.scrollIntoView({ block: 'center' });
    field.focus();
};
</script>

<template>
    <div
        v-if="entries.length > 0"
        ref="element"
        tabindex="-1"
        role="alert"
        :aria-labelledby="headingId"
        class="max-w-3xl rounded-md border border-risk bg-raised p-4"
        data-region="error-summary"
    >
        <h2 :id="headingId" class="text-lg font-semibold text-ink-strong">
            {{ visibleHeading }}
        </h2>
        <ul class="mt-2 space-y-1 text-sm">
            <li v-for="entry in entries" :key="entry.id ?? entry.message">
                <template v-if="entry.id !== null">
                    <a
                        :href="`#${entry.id}`"
                        class="font-semibold text-ink underline underline-offset-2 hover:decoration-2"
                        @click.prevent="focusField(entry.id)"
                    >
                        {{ entry.label }}: {{ entry.message }}
                    </a>
                </template>
                <span v-else class="text-ink">{{ entry.label }}: {{ entry.message }}</span>
            </li>
        </ul>
    </div>
</template>