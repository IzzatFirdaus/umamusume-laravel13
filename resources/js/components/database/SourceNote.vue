<script setup lang="ts">
/*
 * The §48 metadata row: what a reference view was transcribed from, and when.
 *
 * Level 4 of design-2.0 §4's hierarchy (muted, smallest text). Every reference view carries one, so a
 * reader can tell a table from the guide apart from a table the tool fetched. The anchor is the doc's
 * own freshness date, printed as a date rather than an "updated" stamp.
 *
 * The "how to re-check" pointer is a link, not a paragraph: the guide holds the full procedure, and a
 * reference view that restated it would be copying the doc into the UI.
 */
defineProps<{
    section: string;
    anchor: string;
    doc: string;
    recheck?: { text: string; url: string };
}>();
</script>

<template>
    <p class="mt-4 max-w-3xl text-xs text-ink-muted">
        Transcribed from <code class="font-mono">{{ doc }}</code> {{ section }}, anchor
        <time :datetime="anchor">{{ anchor }}</time>.
        <template v-if="recheck">
            {{ recheck.text }}
            <a
                :href="recheck.url"
                class="inline-flex min-h-11 items-center font-medium text-ink-strong underline"
                rel="noreferrer noopener"
                target="_blank"
            >
                Open the source page
            </a>
        </template>
    </p>
</template>
