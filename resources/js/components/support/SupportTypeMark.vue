<script setup lang="ts">
/*
 * The seven support types as the client prints them, each with a glyph.
 *
 * The word is the label; the glyph is beside it and never replaces it. That ordering is the whole
 * point: `design-2.0` §17 forbids communicating a value through colour alone, and a type chip whose
 * only signal is a coloured boot or cap is unreadable to a screen reader and to a Trainer who cannot
 * separate the seven fills. So the mark is `aria-hidden`, the word carries the meaning, and the two are
 * one focus-free unit a reader can take in as a whole.
 *
 * The glyphs are the shapes `UMAMUSUME_REFERENCE.md` §1.4.7 reads off the client's own legend row: a
 * boot for Speed, a flame for Guts, a heart for Stamina, a flexed arm for Power, a mortarboard for Wit,
 * one figure for Pal and a pair for Group. They are drawn here rather than imported because the
 * repository ships no icon set, and `design-2.0` §44 is about icon *rules*, not an inventory.
 *
 * One collision the source itself records: the heart is Stamina's type chip here and the client's HUD
 * uses a heart for Energy elsewhere, which is why one glyph carries one meaning here and never
 * doubles. All seven are drawn in `currentColor`, so they take the ink of whatever text sits beside
 * them and add no token of their own.
 */
defineProps<{
    /** The export's key, one of `SupportCard::TYPES`. */
    type: string;
    /** The client's word for it: Speed, Stamina, Power, Guts, Wit, Pal, Group. */
    label: string;
}>();
</script>

<template>
    <span class="inline-flex items-center gap-1 text-xs text-ink-strong">
        <svg
            v-if="type === 'speed'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M5 2v6h6" />
            <path d="M5 8c0 3 1.5 5 4.5 5S14 11 14 8" />
            <path d="M2 13h12" />
        </svg>
        <svg
            v-else-if="type === 'guts'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M8 14c2.5 0 4-1.8 4-4 0-2.5-2-3.4-2-6-1 1.2-2 1.8-3 2.5C5.6 7.5 4 8.6 4 10.5c0 2.2 1.7 3.5 4 3.5Z" />
        </svg>
        <svg
            v-else-if="type === 'stamina'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M8 13.5S2.5 10 2.5 6.4A3 3 0 0 1 8 4.6a3 3 0 0 1 5.5 1.8C13.5 10 8 13.5 8 13.5Z" />
        </svg>
        <svg
            v-else-if="type === 'power'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M2 6h4v3H2z" />
            <path d="M6 7h3l2-2 2 1-1 3h2" />
            <path d="M6 9v3h3" />
        </svg>
        <svg
            v-else-if="type === 'intelligence'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M8 2 14 5.5 8 9 2 5.5 8 2Z" />
            <path d="M4.5 7v3.5c0 1 1.6 2 3.5 2s3.5-1 3.5-2V7" />
        </svg>
        <svg
            v-else-if="type === 'friend'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <circle cx="8" cy="4.5" r="2" />
            <path d="M4.5 13v-2a3.5 3.5 0 0 1 7 0v2" />
        </svg>
        <svg
            v-else-if="type === 'group'"
            viewBox="0 0 16 16"
            class="h-3.5 w-3.5 shrink-0"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <circle cx="5.5" cy="5" r="1.8" />
            <circle cx="11.5" cy="5" r="1.8" />
            <path d="M2.5 13v-1.8a3 3 0 0 1 6 0V13" />
            <path d="M9 13v-1.8a3 3 0 0 1 4.5-2.6V13" />
        </svg>
        <span>{{ label }}</span>
    </span>
</template>
