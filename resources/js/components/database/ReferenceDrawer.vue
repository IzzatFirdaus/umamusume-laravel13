<script setup lang="ts">
/*
 * The detail drawer for a Database reference row (design-2.0 §32).
 *
 * **Non-modal, so there is no focus trap to get wrong.** §32 asks for a drawer rather than a modal
 * (§31 reserves modals for confirmation and destructive actions), and this one is inline content: it
 * renders where the row is, carries `role="dialog"` with a name but no `aria-modal`, and lets Tab
 * leave it. A modal drawer without a trap would be a defect; a non-modal one is a disclosure.
 *
 * **Focus.** The panel takes focus on open (`tabindex="-1"`), and Escape closes it. Returning focus to
 * the row that opened it is the page's job, not this component's: the page holds the trigger and
 * restores it after the drawer unmounts, which is the pattern the career screens already use.
 *
 * The close control is a real button at the 44px floor, so the drawer is closable by keyboard and by
 * pointer without relying on the Escape handler alone.
 */
import { nextTick, onMounted, ref } from 'vue';

defineProps<{ title: string }>();
const emit = defineEmits<{ close: [] }>();

const panel = ref<HTMLElement | null>(null);

onMounted(async () => {
    await nextTick();
    panel.value?.focus();
});
</script>

<template>
    <section
        ref="panel"
        role="dialog"
        aria-labelledby="reference-drawer-title"
        tabindex="-1"
        class="mt-3 rounded-md border border-rule bg-panel p-4"
        @keydown.esc="emit('close')"
    >
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
            <h3 id="reference-drawer-title" class="text-base font-semibold text-ink-strong">
                {{ title }}
            </h3>

            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-full border border-rule px-3 text-sm font-medium text-ink-strong"
                @click="emit('close')"
            >
                Close
            </button>
        </div>

        <div class="mt-3 space-y-2 text-sm text-ink">
            <slot />
        </div>
    </section>
</template>
