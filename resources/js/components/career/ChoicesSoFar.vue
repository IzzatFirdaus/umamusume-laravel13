<script setup lang="ts">
/*
 * The three choices the wizard carries forward (plan 3.2).
 *
 * One compact bar above the step content, so a Trainer five steps in can see what they already
 * decided without walking back. The per-step "Stored choice: ..." lines said the same thing on three
 * screens and nothing on the other three; one strip says it on all six.
 *
 * **"not chosen" is not an absence, and does not use the absence vocabulary.** `DESIGN.md` §5's rule —
 * an `N/A` carrying its reason, reachable by keyboard and screen reader — governs a *record* that does
 * not carry a value. A draft choice that has not been made yet is a normal state of a wizard, not a
 * gap in the record: nothing failed, no source is silent, and there is no reason to give beyond "you
 * have not reached that step". So the strip renders muted text, which is the shape §5 uses for a
 * value that is simply not there yet, and does not dress the state as a disclosure.
 *
 * **A chosen value links to its step; a missing one does not.** The link is the way to change the
 * decision, and a link on "not chosen" would be a control that goes somewhere without saying what it
 * changes. The step bar above already offers every step.
 */
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    choices: { label: string; value: string | null; url: string | null }[];
}>();
</script>

<template>
    <p
        class="mb-4 flex flex-wrap items-baseline gap-x-2 gap-y-1 border-b border-rule pb-2 text-sm text-ink-muted"
        data-field="choices-so-far"
    >
        <template v-for="(choice, index) in props.choices" :key="choice.label">
            <span v-if="index > 0" aria-hidden="true" class="text-ink-muted">·</span>
            <span>
                {{ choice.label }}:
                <template v-if="choice.value !== null">
                    <Link
                        v-if="choice.url !== null"
                        :href="choice.url"
                        class="inline-flex min-h-11 items-center font-medium text-ink-strong underline"
                    >{{ choice.value }}</Link>
                    <strong v-else class="text-ink-strong">{{ choice.value }}</strong>
                </template>
                <span v-else class="text-ink-muted">not chosen</span>
            </span>
        </template>
    </p>
</template>
