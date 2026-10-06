<script setup lang="ts">
/*
 * The action grid (SCREEN-009 §12, design-2.0 §24). Seven entries, at most one marked `RECOMMENDED`.
 *
 * The marker is a property of the advisor's answer and arrives as a boolean, so nothing here decides
 * which action is best — and the count is Von Restorff's one accent, not an accent per card (plan §13).
 * It is a glyph plus the word, so the recommendation is never carried by colour alone.
 *
 * Every entry is a link, and every link is live. The brief says an unlanded action is "a named
 * absence"; seven unfocusable items would fail this slice's own keyboard-path acceptance and leave
 * the Cockpit with no way to act, so each entry links to the screen that records the action today —
 * the run record screen, whose guided rail offers Training, Rest and Recreation as its own turn
 * choices and whose panels record races, events and scenario actions, or the Legacy builder for the
 * ancestry — and the entry's note names the 2.0 screen that will replace it. There is therefore no
 * absent arm to render, which is why the template has none.
 */
interface Action {
    key: string;
    label: string;
    href: string;
    recommended: boolean;
    note: string;
}

const props = defineProps<{ actions: Action[] }>();

const entryClass =
    'flex min-h-11 w-full flex-col justify-center rounded-md border border-rule bg-raised px-3 py-2 text-left hover:bg-panel';
</script>

<template>
    <section aria-labelledby="career-actions-heading" class="rounded-md border border-rule bg-panel p-4">
        <h2 id="career-actions-heading" class="text-base font-semibold text-ink-strong">Actions</h2>

        <ul class="mt-3 space-y-2">
            <!-- The recommended entry carries the fixed `#action-recommended` id, so the recommendation
                 card can link to it without knowing which action it recommended; `tabindex="-1"` lets
                 that fragment land focus on the entry rather than only scrolling to it. -->
            <li
                v-for="action in props.actions"
                :id="action.recommended ? 'action-recommended' : undefined"
                :key="action.key"
                tabindex="-1"
            >
                <a
                    :href="action.href"
                    :class="[entryClass, action.recommended ? 'border-2 border-pick-line' : '']"
                >
                    <span class="flex flex-wrap items-baseline gap-x-2">
                        <span class="text-sm font-semibold text-ink-strong">{{ action.label }}</span>
                        <span v-if="action.recommended" class="text-xs font-bold text-ink-strong">
                            <span aria-hidden="true">★</span> RECOMMENDED
                        </span>
                    </span>
                    <span class="mt-0.5 text-xs text-ink-muted">{{ action.note }}</span>
                </a>
            </li>
        </ul>
    </section>
</template>
