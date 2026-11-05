<script setup lang="ts">
/**
 * Searchable skill picker — a combobox with text filtering over the catalogue.
 * Emits a single `select` event carrying `{ id, name }`.
 * Consumers choose the field they need:
 * - P1 (build target): accumulates `.name` into `skill_priorities`
 * - P5 (planner "Add a skill"): posts `.id` to `runs.skills.sync`
 * - P5 (compare control): sets `?compare=` param from `.id`
 *
 * Accessibility: listbox semantics with name/role/value (WCAG 4.1.2),
 * visible label inside the accessible name (2.5.3), filtering on the same
 * normalized key as search so loose typing matches canonical names,
 * fully keyboard-operable (type, arrows, Enter, Escape — D-55 contract),
 * 44px targets and the standard focus ring.
 */
import { computed, onBeforeUnmount, onMounted, ref, useId, watch } from 'vue';
import BaseInput from './BaseInput.vue';

interface Option {
    id: number;
    name: string;
    type: string | null;
}

interface Props {
    /** The full list of skills to filter */
    options: Option[];
    /** Optional placeholder when empty */
    placeholder?: string;
    /** Optional label for the input */
    label?: string;
    /** Optional disabled state */
    disabled?: boolean;
    /** Optional error message (mirrors BaseSelect's aria-describedby pattern) */
    error?: string;
    /** Optional hint text */
    hint?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    select: [payload: { id: number; name: string }];
}>();

// The label, the listbox and its options all need ids, and two pickers can share a page (the build
// target and the planner's add form), so the ids come from `useId()` rather than a prop.
const uid = useId();
const inputId = `skill-picker-${uid}`;
const listId = `${inputId}-list`;
const hintId = `${inputId}-hint`;
const errorId = `${inputId}-error`;
const describedBy = computed(() =>
    [props.hint === undefined ? null : hintId]
        .filter((id): id is string => id !== null)
        .join(' ') || undefined,
);

const search = ref('');
const isOpen = ref(false);
const highlightedIndex = ref(-1);
const containerRef = ref<HTMLElement | null>(null);

/**
 * `BaseInput` is a component, so a template ref on it is the component instance, not an element:
 * the picker's outside-click and blur paths read the DOM node by its id instead of assuming
 * `contains()` or `blur()` exist on that instance.
 */
function blurInput(): void {
    const input = document.getElementById(inputId);

    if (input instanceof HTMLInputElement) {
        input.blur();
    }
}

const filteredOptions = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) {
        return props.options;
    }
    return props.options.filter(
        (opt) => opt.name.toLowerCase().includes(q) || (opt.type ?? '').toLowerCase().includes(q),
    );
});

function onModelValue(value: string | number | File | null): void {
    search.value = value === null ? '' : String(value);
    isOpen.value = true;
    highlightedIndex.value = -1;
}

function onInput(event: Event): void {
    onModelValue((event.target as HTMLInputElement).value);
}

function onFocus(): void {
    isOpen.value = true;
}

function onBlur(): void {
    // Defer so click on option registers first
    setTimeout(() => {
        isOpen.value = false;
    }, 150);
}

function onKeydown(event: KeyboardEvent): void {
    // Escape closes even when the filter matched nothing: an empty list is still an open list.
    if (event.key === 'Escape') {
        isOpen.value = false;
        blurInput();

        return;
    }

    const opts = filteredOptions.value;
    if (!opts.length) {
        return;
    }

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedIndex.value = Math.min(highlightedIndex.value + 1, opts.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedIndex.value >= 0 && highlightedIndex.value < opts.length) {
                selectOption(opts[highlightedIndex.value]);
            }
            break;
        case 'Tab':
            isOpen.value = false;
            break;
    }
}

function selectOption(opt: Option): void {
    emit('select', { id: opt.id, name: opt.name });
    search.value = '';
    isOpen.value = false;
    blurInput();
}

function onOptionClick(opt: Option): void {
    selectOption(opt);
}

watch(isOpen, (open) => {
    if (!open) {
        highlightedIndex.value = -1;
    }
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

function handleClickOutside(event: MouseEvent): void {
    if (containerRef.value !== null && !containerRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
}
</script>

<template>
    <label
        v-if="label"
        :for="inputId"
        class="flex flex-col gap-1"
    >
        <span class="text-xs font-extrabold uppercase tracking-[0.06em] text-ink-muted">
            {{ label }}
        </span>

        <div ref="containerRef" class="relative" :class="{ 'ring-2 ring-brand-chrome': isOpen }">
            <BaseInput
                :id="inputId"
                :name="inputId"
                :placeholder="placeholder ?? 'Search skills…'"
                :disabled="disabled"
                :modelValue="search"
                @update:modelValue="onModelValue"
                @focus="onFocus"
                @blur="onBlur"
                @keydown="onKeydown"
                :aria-autocomplete="'list'"
                :aria-controls="listId"
                :aria-expanded="isOpen && filteredOptions.length > 0"
                :aria-activedescendant="highlightedIndex >= 0 ? `${listId}-option-${highlightedIndex}` : undefined"
                :aria-describedby="describedBy"
                :error="error"
                class="min-h-11 pr-10"
            />

            <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center px-3 text-ink-muted hover:text-ink"
                aria-hidden="true"
                @click="isOpen = !isOpen"
            >
                <svg
                    v-if="!isOpen"
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
                <svg
                    v-else
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 15l7-7 7 7"
                    />
                </svg>
            </button>

            <ul
                v-if="isOpen && filteredOptions.length > 0"
                :id="listId"
                role="listbox"
                :aria-label="label ?? 'Skill suggestions'"
                class="absolute z-10 mt-1 w-full max-h-60 overflow-auto rounded-md border border-rule bg-raised shadow-lg ring-1 ring-rule"
            >
                <li
                    v-for="(opt, index) in filteredOptions"
                    :key="opt.id"
                    :id="`${listId}-option-${index}`"
                    role="option"
                    :aria-selected="index === highlightedIndex"
                    class="px-3 py-2 cursor-pointer text-sm select-none transition-colors"
                    :class="[
                        index === highlightedIndex
                            ? 'bg-brand-chrome text-on-brand-chrome'
                            : 'hover:bg-rule-soft',
                    ]"
                    @click="onOptionClick(opt)"
                    @mousemove="highlightedIndex = index"
                >
                    <span class="font-semibold text-ink-strong">{{ opt.name }}</span>
                    <span v-if="opt.type" class="ml-2 text-xs text-ink-muted">({{ opt.type }})</span>
                </li>
            </ul>

            <p
                v-else-if="isOpen"
                class="absolute z-10 mt-1 w-full rounded-md border border-rule bg-raised shadow-lg ring-1 ring-rule p-3 text-sm text-ink-muted"
            >
                No skills match "{{ search }}".
            </p>
        </div>

        <p v-if="hint" class="text-xs text-ink-muted" :id="hintId">{{ hint }}</p>
    </label>
</template>