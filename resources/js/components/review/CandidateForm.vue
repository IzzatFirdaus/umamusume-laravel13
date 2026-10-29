<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { BaseInput, BaseSelect } from '../Form';

// One verdict form per pending candidate. Each control's accessible name carries the
// candidate's own name (F14): on a queue that renders one group per row, a generic
// "Status" label would be technically named and useless. Asserted in the browser spec.
// The visible label sits above the control; the aria-label keeps the candidate-named
// form of the name, and WCAG 2.5.3 holds because the visible text is its prefix.
// The queue's one accented primary (plan 6A.4): only the topmost unresolved card renders .
// The shared controls are multi-root, so the class that sizes them rides a wrapper div.
const props = withDefaults(
    defineProps<{
        candidate: {
            id: number;
            proposed_name: string;
            suggested_umamusume_id: number | null;
        };
        aliasLanguages: { value: string; label: string }[];
        accented?: boolean;
        /**
         * Whether this row opens with its form showing.
         *
         * The queue's two halves want different answers. A row the engine proposed a match for is the
         * primary decision surface, so it opens expanded; a row it could not match needs research before
         * anything can be typed, so it opens collapsed to one line. Twenty-five expanded forms was the
         * page's cost: measured at 4,949px with every form showing.
         */
        defaultExpanded?: boolean;
    }>(),
    { accented: false, defaultExpanded: true },
);

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

/*
 * Per-row disclosure.
 *
 * A collapsed row keeps everything a Trainer needs to decide whether to open it: the name, the
 * Japanese name and the record state are rendered by the page above this component, so the toggle
 * only needs to say whether the form below is showing. `useForm` is created either way, so opening
 * a row does not reset a half-entered verdict.
 */
const expanded = ref(props.defaultExpanded);
const formId = `review-form-${props.candidate.id}`;

function onIdInput(value: string | number | File | null): void {
    form.umamusume_id = typeof value === 'string' || typeof value === 'number' ? value : '';
}

function submit(): void {
    form.post(`/review/${props.candidate.id}`);
}

/*
 * The savebar's status word (`review-detail.html:120`, `.dirty`).
 *
 * It reads "Not yet answered" until a verdict exists, then the verdict's own word, because the
 * status line's job is to say what state the decision is in rather than to repeat the question.
 * Derived from `form.status` rather than from a separate ref, so it cannot drift from the value
 * that is actually submitted.
 */
const statusWord = computed((): string => {
    const picked = verdictOptions.find((option) => option.value === form.status);

    return form.status === '' || picked === undefined ? 'Not yet answered' : picked.label;
});

/*
 * The two named actions. Each sets the verdict and submits in one press: the HFW's savebar is a
 * decision surface, so a Trainer should not have to select a value and then press a third control
 * to commit it. Reject is a first-class verdict here for the reason the wireframe's keybox gives —
 * a queue that only offers "confirm" is a rubber stamp.
 */
function decide(status: 'Confirmed' | 'Rejected'): void {
    form.status = status;
    submit();
}

/*
 * "Use this" — the other-candidate row's action (`review-detail.html:109`).
 *
 * The row names a different form of the same trainee, and the wireframe makes that a decision rather
 * than a label: pressing it adopts that record and commits in one press. The form lives here because
 * this component owns the `useForm`, so the row calls in rather than duplicating the submit contract.
 */
function chooseRecord(id: number): void {
    form.umamusume_id = id;
    decide('Confirmed');
}

defineExpose({ chooseRecord });
</script>

<template>
    <!-- The disclosure control, rendered only on a row that can be collapsed. It names the row it
         opens (so twenty of them are distinguishable by a screen reader) and carries
         `aria-expanded`, which is what makes the state audible rather than only visible. It is
         inline with the row's own text rather than below it: a 44px block laid under every header
         made a "one-line" row 108px tall, which is most of what the collapse was meant to save. -->
    <button
        v-if="!defaultExpanded"
        type="button"
        :aria-expanded="expanded"
        :aria-controls="formId"
        :aria-label="`${expanded ? 'Hide' : 'Show'} the verdict form for ${candidate.proposed_name}`"
        class="-ml-2 inline-flex min-h-11 items-center gap-1 rounded-md px-2 align-middle text-xs font-medium text-ink-muted hover:text-ink-strong transition-colors duration-150 ease-out"
        @click="expanded = !expanded"
    >
        <span aria-hidden="true">{{ expanded ? '▾' : '▸' }}</span>
        {{ expanded ? 'Hide verdict form' : 'Set a verdict' }}
    </button>

    <!-- Three columns on one grid, so the three controls share a baseline. The previous row was
         `flex items-end`: the id field's "(optional)" hint renders BELOW its input, so aligning the
         columns' bottoms lifted that one control 20px above its neighbours and the row read as three
         different baselines. The hint now sits in its own caption row under the grid, which is what
         makes the controls line up without pinning heights on controls that may need more. -->
    <form
        v-show="expanded"
        :id="formId"
        :class="variant === 'savebar'
            ? 'mt-3 flex flex-col gap-3'
            : 'mt-3 grid grid-cols-1 items-start gap-x-3 gap-y-1 sm:grid-cols-[11rem_11rem_11rem_auto]'"
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
        <div v-if="variant === 'row'">
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

        <!--
            The detail page's savebar (`review-detail.html:119-122`).

            Three properties the wireframe asks for, and where each comes from:
            * the status line is `role="status"` and reads "Not yet answered" until a verdict is
              picked, then the chosen word — the HFW's own `.dirty` slot;
            * Reject is a REAL action, not the absence of one. The HFW's keybox is explicit: "A queue
              that only offers 'confirm' is not a review; it is a rubber stamp";
            * Confirm is never pre-selected — `form.status` starts `''` above, so neither button is
              armed on load and a stray Enter commits nothing.

            Both buttons set the verdict and then call the same `submit()` the queue row uses, so the
            request contract is unchanged. The advanced controls stay above rather than inside the
            bar: `Aliased` still needs the alias-language select, and hiding it would make that
            verdict unreachable on this screen.
        -->
        <div v-else class="savebar">
            <span class="dirty" role="status" data-region="review-verdict-status">
                {{ statusWord }}
            </span>
            <button
                type="button"
                :aria-label="`Reject this match for ${candidate.proposed_name}`"
                :disabled="form.processing"
                class="inline-flex min-h-11 items-center justify-center rounded-full border border-rule bg-raised px-4 font-semibold text-ink-strong transition-colors duration-150 ease-out hover:bg-panel disabled:opacity-50 disabled:cursor-not-allowed"
                @click="decide('Rejected')"
            >
                Reject this match
            </button>
            <button
                type="button"
                :aria-label="`Confirm this match for ${candidate.proposed_name}`"
                :disabled="form.processing"
                class="inline-flex min-h-11 items-center justify-center rounded-full bg-primary-action px-4 font-semibold text-on-primary-action transition-colors duration-150 ease-out hover:bg-primary-action/90 disabled:opacity-50 disabled:cursor-not-allowed"
                @click="decide('Confirmed')"
            >
                Confirm this match
            </button>
        </div>

        <span class="hidden text-xs text-ink-muted sm:block" aria-hidden="true">&nbsp;</span>
        <span class="hidden text-xs text-ink-muted sm:block" aria-hidden="true">(optional)</span>
        <span class="hidden text-xs text-ink-muted sm:block" aria-hidden="true">&nbsp;</span>
        <span class="hidden sm:block" aria-hidden="true"></span>
    </form>
</template>
