<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { BaseInput, BaseSelect } from '../Form';

// One verdict form per pending candidate. Each control's accessible name carries the
// candidate's own name (F14): on a queue that renders one group per row, a generic
// "Status" label would be technically named and useless. Asserted in the browser spec.
// The visible label sits above the control; the aria-label keeps the candidate-named
// form of the name, and WCAG 2.5.3 holds because the visible text is its prefix.
// The queue's one accented primary (plan 6A.4): only the topmost unresolved card renders .
// The shared controls are multi-root, so the class that sizes them rides a wrapper div.
const props = defineProps<{
    candidate: {
        id: number;
        proposed_name: string;
        suggested_umamusume_id: number | null;
    };
    aliasLanguages: { value: string; label: string }[];
    accented?: boolean;
}>();

const form = useForm({
    // No preselected verdict. Confirm was the default and it is the one option that writes a catalog
    // row, so a stray click or Enter committed a merge the queue (Pending only) no longer lists and
    // nothing could undo; an unset field makes every verdict a deliberate pick, and the request's own
    // required rule refuses the unset value.
    status: '',
    umamusume_id: props.candidate.suggested_umamusume_id ?? '',
    alias_language: '',
});

const statusId = `review-status-${props.candidate.id}`;
const umamusumeId = `review-umamusume-${props.candidate.id}`;
const aliasId = `review-alias-language-${props.candidate.id}`;

const verdictOptions = [
    { value: '', label: 'Not set' },
    { value: 'Confirmed', label: 'Confirm (merge into suggestion, or create)' },
    { value: 'Aliased', label: 'Add as alias of…' },
    { value: 'Rejected', label: 'Reject' },
];

const aliasOptions = [{ value: '', label: 'Not set' }, ...props.aliasLanguages];

function onIdInput(value: string | number | File | null): void {
    form.umamusume_id = typeof value === 'string' || typeof value === 'number' ? value : '';
}

function submit(): void {
    form.post(`/review/${props.candidate.id}`);
}
</script>

<template>
    <form
        class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2"
        :aria-busy="form.processing ? 'true' : null"
        @submit.prevent="submit"
    >
        <div class="w-44">
            <BaseSelect
                :id="statusId"
                name="status"
                :model-value="form.status"
                label="Verdict"
                :aria-label="`Verdict for ${candidate.proposed_name}`"
                :options="verdictOptions"
                :error="form.errors.status"
                :disabled="form.processing"
                @update:model-value="(v) => (form.status = v === null ? '' : String(v))"
            />
        </div>

        <div class="w-44">
            <BaseInput
                :id="umamusumeId"
                name="umamusume_id"
                :model-value="form.umamusume_id"
                type="number"
                label="Umamusume id"
                :aria-label="`Umamusume id for ${candidate.proposed_name}`"
                hint="(optional)"
                title="The catalog row's id to merge into. Leave empty to use the suggestion."
                :error="form.errors.umamusume_id"
                :disabled="form.processing"
                @update:model-value="onIdInput"
            />
        </div>

        <div class="w-44">
            <BaseSelect
                :id="aliasId"
                name="alias_language"
                :model-value="form.alias_language"
                label="Alias language"
                :aria-label="`Alias language for ${candidate.proposed_name}`"
                :options="aliasOptions"
                :error="form.errors.alias_language"
                :disabled="form.processing"
                @update:model-value="(v) => (form.alias_language = v === null ? '' : String(v))"
            />
        </div>

        <!-- The visible word stays in the name (WCAG 2.5.3, Label in Name). -->
        <button
            type="submit"
            :aria-label="`Resolve ${candidate.proposed_name}`"
            :disabled="form.processing"
            class="inline-flex min-h-11 items-center rounded-full px-3 font-semibold transition-colors duration-150 ease-out disabled:opacity-60 disabled:cursor-wait"
            :class="accented
                ? 'bg-primary-action text-on-primary-action'
                : 'border border-rule bg-raised text-ink-strong hover:bg-panel'"
        >
            Resolve
        </button>
    </form>
</template>
