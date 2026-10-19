<script setup lang="ts">
/*
 * The metadata row under a reference view: which dataset it came from, and when that was read.
 *
 * Level 4 of the design system's hierarchy (muted, smallest text). Every reference view carries one,
 * so a reader can tell a table from the guide apart from a table the tool fetched.
 *
 * **It names a dataset, not a file.** The row used to end with a repository path and a section
 * number, neither of which a Trainer can open. Both moved to `/database/sources`, which lists every
 * dataset once with its date and its source page; this row links there and states the one fact that
 * differs per view, which is the date the dataset was read.
 *
 * The "how to re-check" pointer stays a link rather than a paragraph: the guide holds the full
 * procedure, and a reference view that restated it would be copying the guide into the UI.
 */
import { Link } from '@inertiajs/vue3';

defineProps<{
    section: string;
    anchor: string;
    recheck?: { text: string; url: string };
}>();
</script>

<template>
    <p class="mt-4 max-w-3xl text-xs text-ink-muted">
        {{ section }}. Read on <time :datetime="anchor">{{ anchor }}</time>.
        <Link
            :href="'/database/sources'"
            class="inline-flex min-h-11 items-center font-medium text-ink-strong underline"
        >Where this dataset comes from</Link>
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
