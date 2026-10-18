<script setup lang="ts">
/*
 * One sentence naming everything a region does not have (plan 1.11).
 *
 * A detail view with six unrecorded figures used to say so six times: six markers, six reasons, six
 * disclosures. That is six announcements of the same fact, and it buries the two figures that ARE
 * recorded. This component says it once: "Not recorded: reward, running style, skill points."
 *
 * **It is a sentence, not a marker.** The absence is stated in words a sighted and a screen-reader
 * reader both get from one line. Nothing here is a `title`, a tooltip or an icon.
 *
 * **Correspondence is the point:** it names what is absent, so a reader who wanted a specific figure
 * can tell whether that figure is one of the missing ones rather than re-scanning the region.
 *
 * **An empty list renders nothing.** A region with every value present has no absence to describe, and
 * a component that printed "Not recorded:" with no list would invent one.
 *
 * Table cells are the exception and keep `DataTable`'s dash with its `sr-only` "Not recorded": a cell
 * speaks for a single value in a column whose header already names the row, so a group sentence cannot
 * attach to it.
 */
import { computed } from 'vue';

const props = defineProps<{
    /** The names of the figures this region does not hold, in the region's own words. */
    labels: string[];
}>();

/**
 * A comma list: "reward, running style, skill points".
 *
 * Not "reward, running style and skill points". The plan's own copy is the comma form, and the line is
 * a label followed by a list rather than a spoken clause — the leading "Not recorded:" already carries
 * the sentence. Keeping the list punctuation uniform also means the string is the same however many
 * names it holds, which is what makes it one shape for every region.
 */
const joined = computed<string>(() => {
    const labels = props.labels.filter((label) => label.trim() !== '');

    return labels.join(', ');
});
</script>

<template>
    <p v-if="joined !== ''" class="text-sm text-ink-muted">
        Not recorded: {{ joined }}.
    </p>
</template>
