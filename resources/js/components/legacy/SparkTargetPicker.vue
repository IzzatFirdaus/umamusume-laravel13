<script setup lang="ts">
/**
 * The Spark row's `Target` field, as a searchable combobox rather than a native `<select>`.
 *
 * **Why it is a sibling of `SearchableSkillPicker` and not a generalisation of it.** That component
 * takes `{ id, name, type }[]` and emits `{ id, name }`; a Spark's target is a bare string the payload
 * stores verbatim (`ADR-0010`, `StoreLegacySelectionRequest::assertTargetMatchesCategory`). Teaching one
 * component both shapes means a union prop and a union emit at every existing call site, which is more
 * than the thirty lines this component costs. Recorded as `S-A-P02.2`'s decision-6 resolution.
 *
 * **The category is the row's, not the picker's.** `targets` arrives already scoped by the enclosing
 * `Category` control, so this component filters nothing by category itself: two independent filters over
 * one vocabulary would be two things that can disagree (`IA-3`).
 *
 * **The current-value guard (`S-A-P02.1`'s surviving half).** When `modelValue` names a target the list
 * does not carry — a Spark recorded before the Global filter, or one a Veteran's imported run brought —
 * it renders as `Current: <name>` at the head of the list rather than as an empty field. A stored target
 * that the filter hides must still be visible, or re-saving the row would silently drop it (`TOG-3`:
 * status may be stale or absent, but never wrong).
 *
 * **Accessibility is the contract `SearchableSkillPicker` already meets** (`WCAG 4.1.2` name/role/value,
 * `2.5.3` label in name, `NRM-2` a no-match state that says so, 44px house targets). The ids are the
 * caller's, not `useId()`'s: `AncestorEditor.vue:5-9` records the `ancestor-spark-target-…` contract as
 * load-bearing for three specs, so this component accepts an `id` prop and never generates one.
 */
import { computed, nextTick, ref, watch } from 'vue';

/**
 * One option in the list.
 *
 * A bare string is the Stat and Aptitude vocabulary (`config('scenarios.stat_order')` and
 * `LegacySelectionPayload::SPARK_APTITUDES`), which are the client's own words and have no second
 * spelling. The object form is the Skill catalogue, which carries a Japanese name beside the English
 * one on 605 of its 623 rows.
 */
type TargetOption = string | { name: string; name_ja: string | null };

interface Props {
    /** The option list, already scoped by the row's Category. */
    targets: TargetOption[];
    /** The stored target, or `''` / `null` for a Spark that records none. */
    modelValue: string | null;
    /** The field's id. Load-bearing: three specs locate it (`AncestorEditor.vue:5-9`). */
    id: string;
    /** The visible label, which must also be part of the accessible name (`WCAG 2.5.3`). */
    label: string;
    /** The server's message for this field, when it refused one. */
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const uid = `${props.id}-list`;
const search = ref('');
const isOpen = ref(false);
const highlighted = ref(-1);
const containerRef = ref<HTMLElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);

/** The value the row holds, as the field prints it. Empty is "Not recorded", the tree's own vocabulary. */
const selectedLabel = computed<string>(() => (props.modelValue === null || props.modelValue === '' ? '' : props.modelValue));

/**
 * What the input actually prints.
 *
 * **F-03's fix.** The input used to be bound to `search` alone, so it displayed the filter text
 * while the payload stored `modelValue` — two variables that nothing forced to agree before
 * submit. Typing `Pow`, leaving the option unclicked and saving therefore showed a value and
 * stored `null`, and a null target passes `assertTargetMatchesCategory`, so no error surfaced.
 *
 * The field now prints the committed value except while the Trainer is actively filtering, and
 * falls back to the committed value when the filter is emptied: it never displays something the
 * row has not stored.
 */
const displayValue = computed<string>(() => {
    if (!isOpen.value || search.value === '') {
        return selectedLabel.value;
    }

    return search.value;
});

/**
 * The list in one shape, whatever the Category's vocabulary is.
 *
 * The stored value is always the English `name`, because that is the string the payload keeps
 * (`ADR-0010`); the Japanese name is the second way a Trainer finds the same option, never a second
 * value to store.
 */
const options_ = computed<{ value: string; name: string; name_ja: string | null }[]>(() =>
    props.targets.map((target) =>
        typeof target === 'string'
            ? { value: target, name: target, name_ja: null }
            : { value: target.name, name: target.name, name_ja: target.name_ja },
    ),
);

/**
 * The guard, as a list entry. Rendered only when the stored value is not in `targets`, which is the
 * case a filter change or an imported run produces.
 */
const storedButFiltered = computed<string | null>(() => {
    const value = selectedLabel.value;

    return value !== '' && !options_.value.some((option) => option.value === value) ? value : null;
});

/**
 * What the list offers for the current search text, matching on either name.
 *
 * The Japanese name is searched because it is printed: `SkillController.php:515` states the rule this
 * answers — *"The rule is search matches what the user can see"* — and the two existing pickers in
 * this tree already follow it, the support-card search on `name_ja` (`SupportCardController.php:643`)
 * and the skill catalogue on `match_key_ja` (`SkillController.php:579`). A Trainer who types
 * `食いしん坊` and sees that name on the row must not get nothing back.
 *
 * The comparison folds case so `Gourmand` and `gourmand` are one search, the way the catalogue's own
 * normaliser does.
 */
const matches = computed<{ value: string; name: string; name_ja: string | null }[]>(() => {
    const q = search.value.trim().toLowerCase();

    if (q === '') {
        return options_.value;
    }

    return options_.value.filter(
        (option) =>
            option.name.toLowerCase().includes(q) ||
            (option.name_ja ?? '').toLowerCase().includes(q),
    );
});

/** The list as rendered: the guard row first when it applies, then the matches. */
const options = computed<{ value: string; name: string; name_ja: string | null; isGuard: boolean }[]>(() => {
    const guard = storedButFiltered.value === null
        ? []
        : [{ value: storedButFiltered.value, name: storedButFiltered.value, name_ja: null, isGuard: true }];

    return [
        ...guard,
        ...matches.value.map((option) => ({ ...option, isGuard: false })),
    ];
});

/**
 * Resolve typed filter text to its option, or discard it.
 *
 * **The second half of F-03's fix.** `displayValue` stops the field from *printing* a value the row
 * has not stored; this stops the row from *losing* a value the Trainer typed. A prefix that names
 * exactly one option (`Pow` -> `Power`) is committed on the way out; anything ambiguous or unmatched
 * is dropped rather than saved as `null`.
 *
 * Committing a unique prefix is what a Trainer means by typing it. Refusing was the alternative, but
 * it would turn every abbreviation into an error where the intent is unambiguous.
 */
function commitTypedText(): void {
    const typed = search.value.trim();

    if (typed === '') {
        return;
    }

    const needle = typed.toLowerCase();
    const exact = options_.value.find((option) => option.value.toLowerCase() === needle);
    const prefix = options_.value.filter((option) => option.name.toLowerCase().startsWith(needle));
    const chosen = exact ?? (prefix.length === 1 ? prefix[0] : null);

    if (chosen !== null) {
        emit('update:modelValue', chosen.value);
    }
}

/**
 * Commit only when the typed text is a complete option name.
 *
 * Called on every keystroke, so the narrow rule matters: a unique *prefix* is deliberately not
 * resolved here, because committing `Sp` while a Trainer is typing `Speed` would rewrite the row
 * mid-word. `commitTypedText` (on close) is where a prefix is allowed to resolve.
 */
function commitExactText(): void {
    const typed = search.value.trim();

    if (typed === '') {
        return;
    }

    const exact = options_.value.find((option) => option.value.toLowerCase() === typed.toLowerCase());

    if (exact !== undefined) {
        emit('update:modelValue', exact.value);
    }
}

function open(): void {
    isOpen.value = true;
}

function close(): void {
    isOpen.value = false;
    highlighted.value = -1;
    // F-03: resolve or discard the filter text before the field reverts to `selectedLabel`. Without
    // this, text that was typed but never clicked vanished on save, which is the other half of the
    // silent-null defect: `select()` clears `search`, so it is empty on this path by construction.
    commitTypedText();
    search.value = '';
}

function select(value: string): void {
    emit('update:modelValue', value);
    search.value = '';
    close();
    nextTick(() => {
        inputRef.value?.blur();
    });
}

function onInput(event: Event): void {
    search.value = (event.target as HTMLInputElement).value;
    isOpen.value = true;
    highlighted.value = -1;
    // F-03: a *complete* name typed and not clicked still counts as the answer, so it is committed
    // as it is typed. Only an exact match commits here: resolving a unique prefix on every keystroke
    // would silently rewrite the value while the Trainer is still typing, which is the same class of
    // surprise this fix exists to remove. Prefixes resolve on close instead (`commitTypedText`).
    commitExactText();
}

function onKeydown(event: KeyboardEvent): void {
    // Escape closes even on an empty match set: an empty list is still an open list.
    if (event.key === 'Escape') {
        event.preventDefault();
        close();
        inputRef.value?.blur();

        return;
    }

    const list = options.value;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        open();
        highlighted.value = Math.min(highlighted.value + 1, list.length - 1);

        return;
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlighted.value = Math.max(highlighted.value - 1, 0);

        return;
    }

    if (event.key === 'Enter') {
        event.preventDefault();

        if (highlighted.value >= 0 && highlighted.value < list.length) {
            select(list[highlighted.value].value);
        }

        return;
    }

    if (event.key === 'Tab') {
        close();
    }
}

function clear(): void {
    emit('update:modelValue', '');
    search.value = '';
    close();
}

function onDocumentClick(event: MouseEvent): void {
    if (containerRef.value !== null && !containerRef.value.contains(event.target as Node)) {
        close();
    }
}

/**
 * Commit on blur.
 *
 * F-03 made the *display* honest, but a save can be pressed while the field still has focus, and a
 * document click on a non-focusable element never moves focus, so the outside-click path is not
 * enough. Blur is the event the platform always delivers when focus leaves.
 *
 * Synchronous, not deferred: an earlier version used `setTimeout(0)`, which ran AFTER the save had
 * already serialised the form, so the value committed too late to be posted. Deferring is also
 * unnecessary — pressing an option calls `select()`, which clears `search` first, so `commitTypedText`
 * sees empty text and cannot overwrite the option the Trainer actually pressed.
 */
function onBlur(): void {
    commitTypedText();
    close();
}

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('click', onDocumentClick);
    } else {
        document.removeEventListener('click', onDocumentClick);
    }
});
</script>

<template>
    <div ref="containerRef" class="flex min-w-48 flex-col gap-1">
        <label :for="props.id" class="text-xs font-medium text-ink">{{ props.label }}</label>

        <!-- The chosen value, printed beside the field so the row reads back what it stores without the
             Trainer opening the list. A blank states the tree's own "Not recorded" rather than a dash. -->
        <div class="relative">
            <input
                :id="props.id"
                ref="inputRef"
                type="text"
                role="combobox"
                autocomplete="off"
                :value="displayValue"
                :placeholder="selectedLabel === '' ? 'Not recorded' : selectedLabel"
                :aria-expanded="isOpen"
                :aria-controls="uid"
                :aria-activedescendant="highlighted >= 0 ? `${uid}-option-${highlighted}` : undefined"
                :aria-invalid="props.error !== undefined"
                :aria-describedby="props.error ? `${props.id}-error` : undefined"
                :aria-label="props.label"
                class="mt-1 block min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                @input="onInput"
                @focus="open"
                @keydown="onKeydown"
                @blur="onBlur"
            />

            <button
                v-if="selectedLabel !== ''"
                type="button"
                class="absolute inset-y-0 right-0 flex min-h-11 items-center px-3 text-xs text-ink-muted hover:text-ink"
                :aria-label="`Clear ${props.label}`"
                @click="clear"
            >
                Clear
            </button>
        </div>

        <p v-if="props.error" :id="`${props.id}-error`" class="text-xs text-risk" role="alert">{{ props.error }}</p>

        <ul
            v-if="isOpen"
            :id="uid"
            role="listbox"
            :aria-label="props.label"
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border border-rule bg-raised shadow-lg"
            style="max-width: 24rem"
        >
            <li
                v-for="(option, index) in options"
                :id="`${uid}-option-${index}`"
                :key="`${option.value}-${index}`"
                role="option"
                :aria-selected="index === highlighted"
                class="min-h-11 cursor-pointer px-3 py-2 text-sm select-none"
                :class="[
                    index === highlighted ? 'bg-brand-chrome text-on-brand-chrome' : 'hover:bg-rule-soft',
                    option.isGuard ? 'italic' : '',
                ]"
                @click="select(option.value)"
                @mousemove="highlighted = index"
            >
                {{ option.name }}
                <span v-if="option.name_ja" lang="ja" class="ml-2 text-xs">{{ option.name_ja }}</span>
            </li>

            <li v-if="options.length === 0" class="px-3 py-2 text-sm text-ink-muted">
                No targets match "{{ search }}".
            </li>
        </ul>
    </div>
</template>
