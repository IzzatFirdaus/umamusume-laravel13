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
import BaseCheckbox from '../Form/BaseCheckbox.vue';
import ArtworkSlot from '../ArtworkSlot.vue';
import SupportTypeMark from './SupportTypeMark.vue';
import { nextTick, ref, computed } from 'vue';

interface Option {
    id: number;
    name: string;
    name_ja: string | null;
    artworkURL: string | null;
    rarity_word: string;
    type: string;
    type_label: string;
    // Slice 4 extensions
    ownership_group: 'OWNED' | 'RENTED' | 'NOT_RECORDED';
    in_deck_position: number | null;
    type_match: boolean;
    slot_type: string | null;
}

const props = defineProps<{
    /** The slot's own name, e.g. "Slot 3 · Power". */
    slotLabel: string;
    /** The catalog rows the server returned for the current filter. */
    options: Option[];
    /** The seven support types, for the legend and the type filter. */
    types: { key: string; label: string }[];
    /**
     * The current filter values, echoed back by the server after a filter round trip.
     *
     * Nullable because the server's own echo is: `DeckSelectController` sends the parsed filter or null
     * when the URL names none (`DeckSelect.vue` declares `type: string | null`). The refs below hold
     * `''` for "no filter", so the null is normalised once on read rather than rejected here.
     */
    type: string | null;
    query: string | null;
}>();

const emit = defineEmits<{
    (event: 'equip', cardId: number): void;
    (event: 'filter', filters: { type: string; query: string }): void;
}>();

const dialog = ref<HTMLDialogElement | null>(null);
const opener = ref<HTMLElement | null>(null);
const type = ref('');
const query = ref('');
const ownedOnly = ref(false);

/** Group order for rendering: Owned → Rented → Not recorded */
const GROUP_ORDER = ['OWNED', 'RENTED', 'NOT_RECORDED'] as const;

/** Options grouped by ownership group, in the required order. */
const groupedOptions = computed(() => {
    const groups = {
        OWNED: [] as Option[],
        RENTED: [] as Option[],
        NOT_RECORDED: [] as Option[],
    };

    for (const option of props.options) {
        const group = option.ownership_group;
        if (groups[group]) {
            groups[group].push(option);
        }
    }

    const result: { group: 'OWNED' | 'RENTED' | 'NOT_RECORDED'; options: Option[] }[] = [];
    for (const group of GROUP_ORDER) {
        if (groups[group].length > 0) {
            result.push({ group, options: groups[group] });
        }
    }

    return result;
});

/** Filtered options respecting the "Owned only" checkbox. */
const filteredOptions = computed(() => {
    if (!ownedOnly.value) {
        return groupedOptions.value;
    }

    return groupedOptions.value
        .filter(g => g.group === 'OWNED')
        .map(g => ({ ...g, options: g.options }));
});

/** Group labels with derivation clauses (C16). */
const groupLabels: Record<'OWNED' | 'RENTED' | 'NOT_RECORDED', { label: string; clause: string }> = {
    OWNED: {
        label: 'Owned',
        clause: 'marked owned in a saved run\'s deck',
    },
    RENTED: {
        label: 'Rented',
        clause: 'marked rented in a saved run\'s deck',
    },
    NOT_RECORDED: {
        label: 'Not recorded',
        clause: 'no ownership recorded in any saved run',
    },
};

/** Derivation clause for the "Owned only" checkbox. */
const ownedOnlyClause = 'show only cards marked owned in a saved run\'s deck';

function open(event: Event, filters: { type: string | null; query: string | null }): void {
    openAt(event.currentTarget as HTMLElement, filters);
}

/**
 * Opening without a click event, for the mount path where the URL already names a slot and a filter:
 * the dialog reopens over the refined list rather than closing on the visit (R4-21). The opener is the
 * slot row itself so the focus return has somewhere to land.
 */
function openAt(element: HTMLElement | null, filters: { type: string | null; query: string | null }): void {
    opener.value = element;
    // A null filter and an empty one are the same state on this control, and the selects below are
    // bound to `''` for it: coercing here keeps one representation inside the dialog.
    type.value = filters.type ?? '';
    query.value = filters.query ?? '';
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
        class="m-auto w-full max-w-2xl rounded-lg border-rule bg-panel p-4 text-ink backdrop:bg-black/40"
        :aria-label="`Choose a card for ${slotLabel}`"
        @close="closed"
    >
        <form method="dialog" class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-ink-strong">Choose a card for {{ slotLabel }}</h2>
            <BaseButton type="submit" variant="secondary">Close</BaseButton>
        </form>

        <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted">
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

        <!-- "Owned only" checkbox (Slice 4) -->
        <div class="mt-2 flex items-center gap-2">
            <BaseCheckbox
                v-model="ownedOnly"
                id="deck-filter-owned-only"
                name="owned_only"
                :label="`Owned only (${ownedOnlyClause})`"
            />
        </div>

        <template v-if="filteredOptions.length > 0">
            <template v-for="groupData in filteredOptions" :key="groupData.group">
                <!-- Group label with Calculated mark + derivation clause (C16) -->
                <div class="mt-4 mb-2 flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        {{ groupLabels[groupData.group].label }}
                    </span>
                    <span class="pill pill-neutral text-xs">Calculated</span>
                    <span class="text-xs text-ink-muted">({{ groupLabels[groupData.group].clause }})</span>
                </div>

                <ul role="list" tabindex="0" aria-label="Support cards, scrollable" class="max-h-[50vh] space-y-2 overflow-y-auto focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring">
                    <li
                        v-for="option in groupData.options"
                        :key="option.id"
                        class="flex items-center gap-3 rounded-md border-rule p-2"
                    >
                        <ArtworkSlot :url="option.artworkURL" alt="" size="size-12" reserve />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink-strong">{{ option.name }}</p>
                            <p v-if="option.name_ja" lang="ja" class="truncate text-xs text-ink-muted">{{ option.name_ja }}</p>
                        </div>
                        <SupportTypeMark :type="option.type" :label="option.type_label" />

                        <!-- Type-match marker (Slice 4, C18). The glyph and its word are the card's own
                             `type`/`type_label`, not `slot_type`: `type_match` is true only when the two
                             are equal, so the card's type is the same value and is the non-nullable one.
                             The previous `slot_type` binding was also the reason this mark vanished when
                             the slot named no type, and it passed a `:size` the component never declared. -->
                        <span
                            v-if="option.type_match"
                            class="flex items-center gap-1 text-xs font-medium text-ink bg-panel px-2 py-0.5 rounded"
                        >
                            <SupportTypeMark :type="option.type" :label="option.type_label" />
                            <span>fits this slot</span>
                        </span>

                        <!-- In-deck badge + disabled Equip (Slice 4, D6, C19) -->
                        <template v-if="option.in_deck_position !== null">
                            <span class="pill pill-caution text-xs">In Slot {{ option.in_deck_position }}</span>
                            <BaseButton
                                variant="secondary"
                                :disabled="true"
                                class="opacity-50 cursor-not-allowed"
                                @click.prevent.stop="equip(option.id)"
                            >Equip</BaseButton>
                        </template>
                        <template v-else>
                            <BaseButton variant="secondary" @click="equip(option.id)">Equip</BaseButton>
                        </template>
                    </li>
                </ul>
            </template>
        </template>
        <p v-else class="note mt-3">No support card matches that filter. Clear the search or uncheck Owned only.</p>
    </dialog>
</template>
