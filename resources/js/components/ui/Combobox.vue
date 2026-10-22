<script setup lang="ts">
/*
 * A combobox: a text input that filters a listbox and commits one option (plan 1.9).
 *
 * It follows the ARIA 1.2 combobox pattern with a listbox popup, which is the pattern the platform
 * documents for "type to narrow, then choose":
 *
 *   - the input is `role="combobox"` with `aria-autocomplete="list"`, `aria-expanded` and
 *     `aria-controls` pointing at the listbox;
 *   - the active option is named by `aria-activedescendant` on the INPUT, not by moving DOM focus into
 *     the list. Moving focus would take the reader out of the field they are typing in, and the next
 *     keystroke would go to the list instead of the input;
 *   - the listbox is `role="listbox"` with `role="option"` children.
 *
 * The result count is announced through a polite live region, because filtering is a change the reader
 * cannot see: "3 options" tells a screen-reader user that typing narrowed the list rather than
 * emptied it.
 *
 * Keyboard: Down/Up rove, Enter commits, Escape closes without committing, Tab leaves the field open
 * state alone. Escape is handled before the global handlers so it does not also close something else
 * on the page.
 */
import { computed, nextTick, ref, watch } from 'vue';
import type { ComboboxOption } from './combobox-types';

const props = withDefaults(
    defineProps<{
        options: ComboboxOption[];
        modelValue: string | number | null;
        /** The field's accessible name. */
        label: string;
        id?: string;
        placeholder?: string;
        /** Passed down from FormField so the hint and error read with the control. */
        describedBy?: string;
    }>(),
    { id: undefined, placeholder: undefined, describedBy: undefined },
);

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();

const inputId = computed(() => props.id ?? 'combobox');
const listId = computed(() => `${inputId.value}-listbox`);
const statusId = computed(() => `${inputId.value}-status`);

const query = ref('');
const open = ref(false);
const activeIndex = ref(-1);
const inputEl = ref<HTMLInputElement | null>(null);

/** The chosen option's label, which is what the input shows when the popup is closed. */
const selectedLabel = computed(() => {
    const chosen = props.options.find((option) => option.value === props.modelValue);

    return chosen?.label ?? '';
});

const filtered = computed<ComboboxOption[]>(() => {
    const needle = query.value.trim().toLowerCase();
    if (needle === '') {
        return props.options;
    }

    return props.options.filter(
        (option) =>
            option.label.toLowerCase().includes(needle) ||
            (option.sublabel ?? '').toLowerCase().includes(needle),
    );
});

/** The option the input points at with `aria-activedescendant`, or undefined when none is active. */
const activeOption = computed<ComboboxOption | null>(() => filtered.value[activeIndex.value] ?? null);

const activeDescendant = computed<string | undefined>(() =>
    activeOption.value ? optionId(activeOption.value) : undefined,
);

const optionId = (option: ComboboxOption): string => `${inputId.value}-opt-${option.value}`;

// What the reader hears when the list changes under them.
const resultCount = computed(() => {
    const count = filtered.value.length;
    if (!open.value) {
        return '';
    }

    return count === 1 ? '1 option' : `${count} options`;
});

/**
 * Opening shows the whole list and moves the active option to the current value when there is one, so
 * a reader who reopens the field starts where they left off rather than at the top.
 */
function openList(): void {
    if (open.value) {
        return;
    }

    open.value = true;
    query.value = '';
    const at = filtered.value.findIndex((option) => option.value === props.modelValue);
    activeIndex.value = at;
}

function closeList(): void {
    open.value = false;
    activeIndex.value = -1;
    query.value = '';
}

function onInput(event: Event): void {
    query.value = (event.target as HTMLInputElement).value;
    open.value = true;
    // Typing resets the roving position to the first match: the reader narrowed the list and expects
    // Down to start from the top of what is now shown.
    activeIndex.value = filtered.value.length > 0 ? 0 : -1;
}

function commit(option: ComboboxOption): void {
    emit('update:modelValue', option.value);
    closeList();
    nextTick(() => inputEl.value?.blur());
}

function move(delta: number): void {
    if (!open.value) {
        openList();
        return;
    }

    if (filtered.value.length === 0) {
        return;
    }

    const next = activeIndex.value + delta;
    // Clamp rather than wrap: wrapping past the end of a long filtered list drops the reader back at
    // the top without telling them, which reads as the list having reset.
    activeIndex.value = Math.max(0, Math.min(filtered.value.length - 1, next));
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        move(1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        move(-1);
    } else if (event.key === 'Enter') {
        if (open.value && activeOption.value) {
            event.preventDefault();
            commit(activeOption.value);
        }
    } else if (event.key === 'Escape') {
        if (open.value) {
            // Consumed so the page's own Escape handlers do not also fire.
            event.stopPropagation();
            event.preventDefault();
            closeList();
        }
    }
}

// The displayed value follows the model when the model changes from outside (a form reset, a
// server round trip). `query` is only live while the popup is open.
watch(
    () => props.modelValue,
    () => {
        if (!open.value) {
            query.value = '';
        }
    },
);

function onBlur(): void {
    // A blur with the popup open is the reader moving away without choosing; close rather than leave
    // an orphaned listbox floating under a field they have left.
    window.setTimeout(() => closeList(), 120);
}
</script>

<template>
    <div class="relative">
        <input
            :id="inputId"
            ref="inputEl"
            type="text"
            role="combobox"
            :aria-label="label"
            aria-autocomplete="list"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-controls="listId"
            :aria-activedescendant="activeDescendant"
            :aria-describedby="describedBy"
            :value="open ? query : selectedLabel"
            :placeholder="placeholder"
            autocomplete="off"
            class="block min-h-11 w-full rounded-md border border-rule bg-raised px-3 text-sm text-ink placeholder:text-ink-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
            @input="onInput"
            @focus="openList"
            @blur="onBlur"
            @keydown="onKeydown"
        />

        <ul
            v-show="open"
            :id="listId"
            role="listbox"
            :aria-label="label"
            class="card absolute z-20 mt-1 max-h-72 w-full overflow-auto shadow-overlay"
        >
            <li
                v-for="(option, index) in filtered"
                :id="optionId(option)"
                :key="option.value"
                role="option"
                :aria-selected="option.value === modelValue ? 'true' : 'false'"
                :class="[
                    'cursor-pointer px-3 py-2 text-sm',
                    index === activeIndex ? 'bg-sunken text-ink-strong' : 'text-ink',
                    option.value === modelValue ? 'font-semibold' : '',
                ]"
                @mousedown.prevent="commit(option)"
            >
                <span :lang="option.lang">{{ option.label }}</span>
                <span v-if="option.sublabel" :lang="option.lang" class="block text-xs text-ink-muted">
                    {{ option.sublabel }}
                </span>
            </li>
            <li v-if="filtered.length === 0" class="px-3 py-2 text-sm text-ink-muted">
                No matches.
            </li>
        </ul>

        <!-- Polite, and separate from the listbox: the count is a status, not an option. -->
        <p :id="statusId" role="status" aria-live="polite" class="sr-only">{{ resultCount }}</p>
    </div>
</template>
