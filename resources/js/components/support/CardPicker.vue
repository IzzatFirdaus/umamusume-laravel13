<script setup lang="ts">
/*
 * The support-card picker, as a native modal dialog (plan 3.7).
 *
 * **Why a dialog rather than a permanent panel.** The deck page used to carry the whole catalog in a
 * column beside the six slots, so the page was two screens of content for one job: pick a card. A
 * modal is the shape the platform already gives focus trapping, an inert background and
 * Escape-to-close for free, and it states which slot it is filling by name in its own heading.
 *
 * **The pattern is copied from `RaceCard.vue`, measured rather than assumed.** `showModal()` on a
 * `ref`, one `@close` handler for every close path (Escape, the Cancel button, a programmatic close),
 * and that handler returns focus to the opener. The one adaptation: this picker has SIX openers, so
 * the opener element is stored when the dialog opens rather than bound once at mount.
 *
 * **No close-guard.** Nothing in this dialog is destructive and the search box is disposable, so a
 * Trainer who presses Escape loses a filter string, not an entry.
 */
import BaseButton from '../Form/BaseButton.vue';
import BaseInput from '../Form/BaseInput.vue';
import BaseSelect from '../Form/BaseSelect.vue';
import ArtworkSlot from '../ArtworkSlot.vue';
import SupportTypeMark from './SupportTypeMark.vue';
import { nextTick, ref } from 'vue';

interface Option {
    id: number;
    name: string;
    name_ja: string | null;
    artworkURL: string | null;
    rarity_word: string;
    type: string;
    type_label: string;
}

defineProps<{
    /** The slot's own name, e.g. "Slot 3 · Power". */
    slotLabel: string;
    /** The catalog rows the server returned for the current filter. */
    options: Option[];
    /** The seven support types, for the legend and the type filter. */
    types: { key: string; label: string }[];
    /** The current filter values, echoed back by the server after a filter round trip. */
    type: string;
    query: string;
}>();

const emit = defineEmits<{
    (event: 'equip', cardId: number): void;
    (event: 'filter', filters: { type: string; query: string }): void;
}>();

const dialog = ref<HTMLDialogElement | null>(null);
const opener = ref<HTMLElement | null>(null);
const type = ref('');
const query = ref('');

/**
 * Opens the dialog for a slot, remembering the control that opened it.
 *
 * The opener is captured here rather than passed as a prop because the six slots each have their own
 * button: one ref bound at mount could only ever point at one of them, and focus would return to the
 * wrong slot.
 */
function open(event: Event, filters: { type: string; query: string }): void {
    openAt(event.currentTarget as HTMLElement, filters);
}

/**
 * Opening without a click event, for the mount path where the URL already names a slot and a filter:
 * the dialog reopens over the refined list rather than closing on the visit (R4-21). The opener is the
 * slot row itself so the focus return has somewhere to land.
 */
function openAt(element: HTMLElement | null, filters: { type: string; query: string }): void {
    opener.value = element;
    type.value = filters.type;
    query.value = filters.query;
    dialog.value?.showModal();
}

function applyFilter(): void {
    // No close: the parent re-fetches the options with `preserveState`, so the dialog stays open over
    // the refined list instead of costing a click per search (R4-21).
    emit('filter', { type: type.value, query: query.value });
}

function equip(cardId: number): void {
    emit('equip', cardId);
    dialog.value?.close();
}

// Fires for Escape and for every other close, so one handler covers the focus return. `nextTick`
// because on the equip path the slot's own button is re-rendered as the dialog closes.
function closed(): void {
    nextTick(() => opener.value?.focus());
}

defineExpose({ open, openAt });
</script>

<template>
    <dialog
        ref="dialog"
        class="w-full max-w-2xl rounded-[var(--radius-lg)] border border-[var(--color-rule)] bg-[var(--color-panel)] p-4 text-[var(--color-ink)] backdrop:bg-black/40"
        :aria-label="`Choose a card for ${slotLabel}`"
        @close="closed"
    >
        <form method="dialog" class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-[var(--color-ink-strong)]">Choose a card for {{ slotLabel }}</h2>
            <BaseButton type="submit" variant="secondary">Close</BaseButton>
        </form>

        <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-[var(--color-ink-muted)]">
            <span class="text-xs font-semibold uppercase tracking-wide">The seven support types</span>
            <span v-for="kind in types" :key="kind.key" class="inline-flex items-center gap-1">
                <SupportTypeMark :type="kind.key" :label="kind.label" />
            </span>
        </p>

        <div class="mt-3 flex flex-wrap items-end gap-3 text-sm">
            <BaseSelect
                v-model="type"
                id="deck-filter-type"
                name="type"
                label="Type"
                :options="[{ value: '', label: 'All' }, ...types.map(k => ({ value: k.key, label: k.label }))]"
            />
            <BaseInput
                v-model="query"
                id="deck-filter-search"
                name="q"
                type="search"
                label="Name contains"
            />
            <BaseButton type="button" variant="secondary" @click="applyFilter">Filter</BaseButton>
        </div>

        <ul v-if="options.length > 0" role="list" class="mt-3 max-h-[50vh] space-y-2 overflow-y-auto">
            <li
                v-for="option in options"
                :key="option.id"
                class="flex items-center gap-3 rounded-[var(--radius-md)] border border-[var(--color-rule)] p-2"
            >
                <ArtworkSlot :url="option.artworkURL" alt="" size="size-10" reserve />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[var(--color-ink-strong)]">{{ option.name }}</p>
                    <p v-if="option.name_ja" lang="ja" class="truncate text-xs text-[var(--color-ink-muted)]">{{ option.name_ja }}</p>
                </div>
                <SupportTypeMark :type="option.type" :label="option.type_label" />
                <BaseButton variant="secondary" @click="equip(option.id)">Equip</BaseButton>
            </li>
        </ul>

        <p v-else class="note mt-3">
            No support card matches that filter.
        </p>
    </dialog>
</template>
