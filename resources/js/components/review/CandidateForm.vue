<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

// One verdict form per pending candidate. Each control's accessible name carries the
// candidate's own name (F14): on a queue that renders one group per row, a generic
// "Status" label would be technically named and useless. Asserted in the browser spec.
const props = defineProps<{
    candidate: {
        id: number;
        proposed_name: string;
        suggested_umamusume_id: number | null;
    };
    aliasLanguages: { value: string; label: string }[];
}>();

const form = useForm({
    status: 'Confirmed',
    umamusume_id: props.candidate.suggested_umamusume_id ?? '',
    alias_language: '',
});

const hasError = (): boolean =>
    Boolean(form.errors.status || form.errors.umamusume_id || form.errors.alias_language);

const errorId = `review-errors-${props.candidate.id}`;
const describedBy = (): string | undefined => (hasError() ? errorId : undefined);

function submit(): void {
    form.post(`/review/${props.candidate.id}`);
}
</script>

<template>
    <form class="mt-3 flex flex-wrap items-center gap-2" @submit.prevent="submit">
        <label class="sr-only" :for="`review-status-${candidate.id}`">Verdict for {{ candidate.proposed_name }}</label>
        <select
            :id="`review-status-${candidate.id}`"
            v-model="form.status"
            name="status"
            :aria-invalid="form.errors.status ? 'true' : undefined"
            :aria-describedby="describedBy()"
            class="rounded-md border border-rule bg-raised px-2 py-1 text-ink"
        >
            <option value="Confirmed">Confirm (merge into suggestion, or create)</option>
            <option value="Aliased">Add as alias of…</option>
            <option value="Rejected">Reject</option>
        </select>

        <label class="sr-only" :for="`review-umamusume-${candidate.id}`">Umamusume id (optional) for {{ candidate.proposed_name }}</label>
        <input
            :id="`review-umamusume-${candidate.id}`"
            v-model="form.umamusume_id"
            type="number"
            name="umamusume_id"
            placeholder="Umamusume id (optional)"
            :aria-invalid="form.errors.umamusume_id ? 'true' : undefined"
            :aria-describedby="describedBy()"
            class="w-44 rounded-md border border-rule bg-raised px-2 py-1 text-ink"
        >

        <label class="sr-only" :for="`review-alias-language-${candidate.id}`">Alias language for {{ candidate.proposed_name }}</label>
        <select
            :id="`review-alias-language-${candidate.id}`"
            v-model="form.alias_language"
            name="alias_language"
            :aria-invalid="form.errors.alias_language ? 'true' : undefined"
            :aria-describedby="describedBy()"
            class="rounded-md border border-rule bg-raised px-2 py-1 text-ink"
        >
            <option value="">Alias language</option>
            <option v-for="language in aliasLanguages" :key="language.value" :value="language.value">
                {{ language.label }}
            </option>
        </select>

        <!-- The visible word stays in the name (WCAG 2.5.3, Label in Name). -->
        <button
            type="submit"
            :aria-label="`Resolve ${candidate.proposed_name}`"
            class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome"
        >
            Resolve
        </button>

        <!-- Every field this form can fail on, stacked: the request can return umamusume_id
             and alias_language together, and fixing one at a time re-submits blind to the other. -->
        <ul v-if="hasError()" :id="errorId" role="alert" class="mt-1 w-full space-y-0.5 text-sm text-risk">
            <li v-if="form.errors.status">{{ form.errors.status }}</li>
            <li v-if="form.errors.umamusume_id">{{ form.errors.umamusume_id }}</li>
            <li v-if="form.errors.alias_language">{{ form.errors.alias_language }}</li>
        </ul>
    </form>
</template>
