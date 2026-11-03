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
import { computed, onMounted, ref, watch } from 'vue';
import { BaseInput } from './BaseInput';
import { BaseButton } from './BaseButton';

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

const search = ref('');
const isOpen = ref(false);
const highlightedIndex = ref(-1);
const inputRef = ref<HTMLInputElement | null>(null);
const listRef = ref<HTMLUListElement | null>(null);

const filteredOptions = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) {
        return props.options;
    }
    return props.options.filter(
        (opt) => opt.name.toLowerCase().includes(q) || (opt.type ?? '').toLowerCase().includes(q),
    );
});

function onInput(event: Event): void {
    search.value = (event.target as HTMLInputElement).value;
    isOpen.value = true;
    highlightedIndex.value = -1;
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
        case 'Escape':
            isOpen.value = false;
            inputRef.value?.blur();
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
    inputRef.value?.blur();
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

function handleClickOutside(event: MouseEvent): void {
    if (
        inputRef.value &&
        !inputRef.value.contains(event.target as Node) &&
        listRef.value &&
        !listRef.value.contains(event.target as Node)
    ) {
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

        <div class="relative" :class="{ 'ring-2 ring-brand-chrome': isOpen }">
            <BaseInput
                ref="inputRef"
                :id="inputId"
                :name="inputId"
                :placeholder="placeholder ?? 'Search skills…'"
                :disabled="disabled"
                :value="search"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
                @keydown="onKeydown"
                :aria-autocomplete="'list'"
                :aria-controls="listId"
                :aria-expanded="isOpen && filteredOptions.length > 0"
                :aria-activedescendant="highlightedIndex >= 0 ? `${listId}-option-${highlightedIndex}` : undefined"
                :aria-describedby="describedBy"
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
                ref="listRef"
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
        <p v-if="error" class="text-xs text-risk" :id="errorId" role="alert">{{ error }}</p>
    </label>
</template>