<script setup lang="ts">
/*
 * The pager under a table (plan 1.5). Three facts, always, in this order, because each answers a
 * different question a reader has after scanning a table:
 *
 *   - "Showing 1–25 of 67" — where am I in the whole set? Without it a reader cannot tell whether the
 *     table they are looking at is the whole answer or a slice of it.
 *   - "Page 1 of 3" — how much further is there? The current page is `aria-current="page"`, which is
 *     how a screen reader says "here" rather than leaving the reader to compare colours.
 *   - Previous/Next — the only two moves a pager needs. Numbered links are a second way to express
 *     "Page X of Y" and a second thing to keep in step with the request.
 *
 * The range and the total come from the page, not from the row count: a paginated table only holds
 * one page of rows, so counting them here would report 25 of 25 for every page but the last.
 */
withDefaults(
    defineProps<{
        /** 1-based page number. */
        page: number;
        /** Total number of pages, at least 1. */
        pages: number;
        /** 1-based index of the first row on this page. */
        from: number;
        /** 1-based index of the last row on this page. */
        to: number;
        /** Total rows across every page. */
        total: number;
        /** The i18n-ish noun for what is being counted, e.g. "races". Defaults to "rows". */
        noun?: string;
    }>(),
    { noun: 'rows' },
);

const emit = defineEmits<{
    /** The page the reader asked for. The owning page issues the request. */
    (event: 'navigate', page: number): void;
}>();

const hasPrevious = (page: number): boolean => page > 1;
const hasNext = (pages: number, page: number): boolean => page < pages;
</script>

<template>
    <nav aria-label="Pagination" class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
        <!-- One polite status rather than three separate spans: a page change should be announced once,
             and `aria-live` on each fragment would read the reader three numbers in three sentences. -->
        <p class="text-ink-muted" role="status">
            Showing {{ from }} to {{ to }} of {{ total }} {{ noun }}
            <span class="ml-2">Page {{ page }} of {{ pages }}</span>
        </p>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:text-ink-strong transition-colors duration-150 ease-out disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!hasPrevious(page)"
                @click="emit('navigate', page - 1)"
            >
                Previous
            </button>

            <!-- The current page as a real link with `aria-current`, not a disabled button: the reader
                 is here, and "here" is a state rather than an unavailable action. -->
            <span
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md bg-sunken px-3 font-semibold text-ink-strong"
                aria-current="page"
            >
                {{ page }}
            </span>

            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:text-ink-strong transition-colors duration-150 ease-out disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!hasNext(pages, page)"
                @click="emit('navigate', page + 1)"
            >
                Next
            </button>
        </div>
    </nav>
</template>
