<script setup lang="ts">
/*
 * The flash banner (plan 6B.6).
 *
 * Always mounted, and it carries only its text: a live region announces what changes inside it, so a
 * region created in the same tick as its message may never be announced (WCAG 4.1.3). Empty, it is
 * `sr-only` and says nothing; the classes that paint the banner arrive with the message.
 *
 * The dismiss button is a sibling of the region, never inside it: `role="status"` is read as its own
 * text, and a control inside the region would join the announcement.
 *
 * The timer lives here rather than on the page that writes, because the banner dismisses itself after
 * `FLASH_DISMISS_MS` and a page shell that forgot to arm it would leave a stale sentence on screen.
 *
 * `token` is the navigation identity (the page's props object). It is watched instead of the text
 * because two saves in a row flash the same sentence, and a watch on the text alone would miss the
 * second one after a dismiss.
 */
import { ref, watch } from 'vue';

const props = defineProps<{ status: string | null; token?: unknown }>();

/** Five seconds, the run the inline banner used before it was extracted. */
const FLASH_DISMISS_MS = 5000;

const visible = ref<string | null>(props.status);
let timer: ReturnType<typeof setTimeout> | undefined;

function dismiss(): void {
    visible.value = null;

    if (timer !== undefined) {
        clearTimeout(timer);
    }
}

function schedule(): void {
    if (timer !== undefined) {
        clearTimeout(timer);
    }

    if (visible.value === null) {
        return;
    }

    timer = setTimeout(() => {
        visible.value = null;
    }, FLASH_DISMISS_MS);
}

if (visible.value !== null) {
    schedule();
}

watch(
    () => props.token,
    () => {
        visible.value = props.status;
        schedule();
    },
);

defineExpose({ dismiss });
</script>

<template>
    <div
        class="flex items-center justify-between gap-2"
        :class="visible ? 'mb-4 rounded-md border border-rule bg-raised px-3 py-2' : ''"
    >
        <!-- The accessible name is explicit because `role="status"` takes no name from
             content (the mechanism probe in tests/browser/flash-name-mechanism.spec.ts): without
             the label, the banner cannot be addressed by name at all. -->
        <p
            role="status"
            :aria-label="visible ?? undefined"
            :class="visible ? 'text-sm text-ink' : 'sr-only'"
        >
            {{ visible ?? '' }}
        </p>
        <button
            v-if="visible"
            type="button"
            aria-label="Dismiss"
            class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center text-ink-muted hover:text-ink-strong transition-colors duration-150 ease-out"
            @click="dismiss"
        >
            ✕
        </button>
    </div>
</template>
