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
    <!-- Three columns on one grid, so the three controls share a baseline. The previous row was
         `flex items-end`: the id field's "(optional)" hint renders BELOW its input, so aligning the
         columns' bottoms lifted that one control 20px above its neighbours and the row read as three
         different baselines. The hint now sits in its own caption row under the grid, which is what
         makes the controls line up without pinning heights on controls that may need more. -->
    <form
        class="mt-3 grid grid-cols-1 items-start gap-x-3 gap-y-1 sm:grid-cols-[11rem_11rem_11rem_auto]"
        data-testid="review-form-row"
        :aria-busy="form.processing ? 'true' : null"
        @submit.prevent="submit"
    >
        <div>
            <BaseSelect
                :id="statusId"
                name="status"
                :model-value="form.status"
                label="Verdict"
                :aria-label="`Verdict for ${candidate.proposed_name}`"
                :options="verdictOptions"
                :error="form.errors.status"
                :disabled="form.processing"
                muted-when-empty
                @update:model-value="(v) => (form.status = v === null ? '' : String(v))"
            />
        </div>

        <!-- `pt-1` is the one alignment offset in the row, and it is measured rather than guessed:
             BaseSelect is a `flex flex-col gap-1` (label, 4px gap, select with its own `mt-1`),
             while BaseInput's label wraps the input directly, so the input's top lands 4px above
             the selects'. The padding restores it. It is padding and not a height because the
             controls' own floor is the tree's 44px and neither control should be resized to fit
             this row. -->
        <div class="pt-1">
            <BaseInput
                :id="umamusumeId"
                name="umamusume_id"
                :model-value="form.umamusume_id"
                type="number"
                label="Umamusume id"
                :aria-label="`Umamusume id for ${candidate.proposed_name}`"
                title="The catalog row's id to merge into. Leave empty to use the suggestion."
                :error="form.errors.umamusume_id"
                :disabled="form.processing"
                @update:model-value="onIdInput"
            />
        </div>

        <div>
            <BaseSelect
                :id="aliasId"
                name="alias_language"
                :model-value="form.alias_language"
                label="Alias language"
                :aria-label="`Alias language for ${candidate.proposed_name}`"
                :options="aliasOptions"
                :error="form.errors.alias_language"
                :disabled="form.processing"
                muted-when-empty
                @update:model-value="(v) => (form.alias_language = v === null ? '' : String(v))"
            />
        </div>

        <!-- The action rides the control row, so it sits on the same line as the controls it
             commits. The optional hint is a caption, not a label of its own. -->
        <div>
            <!-- The visible word stays in the name (WCAG 2.5.3, Label in Name). The verdict gate is
                 the shape `Veterans/Compare.vue` ships: the control is disabled until a verdict is
                 picked, so a Trainer meets the requirement rather than a dead button. The reason is
                 stated once for the whole queue (the page's own line) instead of on every row:
                 twenty-five copies of one sentence is noise, and the disabled state plus that line
                 is the same instruction. The request's own required rule still backs it up
                 server-side. The disabled styling is the house idiom `Pagination.vue` uses, so a
                 disabled Resolve reads as unavailable rather than merely quiet. -->
            <button
                type="submit"
                :aria-label="`Resolve ${candidate.proposed_name}`"
                :disabled="form.processing || form.status === ''"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-full px-3 font-semibold transition-colors duration-150 ease-out disabled:opacity-50 disabled:cursor-not-allowed"
                :class="accented
                    ? 'bg-primary-action text-on-primary-action'
                    : 'border border-rule bg-raised text-ink-strong hover:bg-panel'"
            >
                Resolve
            </button>
        </div>

        <span class="hidden text-xs text-ink-muted sm:block" aria-hidden="true">&nbsp;</span>
        <span class="hidden text-xs text-ink-muted sm:block" aria-hidden="true">(optional)</span>
        <span class="hidden text-xs text-ink-muted sm:block" aria-hidden="true">&nbsp;</span>
        <span class="hidden sm:block" aria-hidden="true"></span>
    </form>
</template>
