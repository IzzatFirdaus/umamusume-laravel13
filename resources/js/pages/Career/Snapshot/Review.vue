<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';

/*
 * The snapshot read-back. This screen exists so a Trainer reads exactly what will be written before
 * it is written, and the thing it must not do is flatten three different statements into one blank.
 *
 * Every state field prints one of three sentences: Known names the number the Trainer read, Unknown
 * says the client shows nothing for it, and Not provided says the field was left alone. They are
 * rendered with distinct treatments as well as distinct words, so a reviewer scanning the list can
 * tell a deliberate "I don't know" from a forgotten field.
 *
 * The confirm button re-posts the payload it was handed rather than re-collecting it, so what was
 * read back is what is committed.
 */
interface ReviewField {
    key: string;
    label: string;
    state: string;
    state_label: string;
    value: number | null;
    display: string;
}

const props = defineProps<{
    fields: ReviewField[];
    payload: Record<string, unknown>;
    /** False when no setup form has been read back yet: every field is "Not provided". */
    has_draft: boolean;
    /** The position the snapshot names, in the client's words; null only if the form carried none. */
    position_label?: string | null;
    action: string;
    back: string;
    entry: string;
}>();

const steps = [
    { key: 'entry', label: 'Entry', href: props.entry },
    { key: 'setup', label: 'Setup', href: props.back },
    { key: 'review', label: 'Review', href: '#' },
];

const form = useForm(props.payload);

/*
 * A refused confirm comes back to this page with the error bag populated, and until this block the
 * page rendered none of it: "Record snapshot" pressed, nothing changed, and the read-back read as
 * broken. The block takes focus so the refusal is the next thing a keyboard Trainer meets (the same
 * shape the record screens own).
 */
const alertRef = ref<HTMLDivElement | null>(null);

watch(() => form.hasErrors, (hasErrors) => {
    if (hasErrors) {
        void nextTick(() => alertRef.value?.focus());
    }
});

const submit = (): void => {
    form.post(props.action);
};

const treatment = (state: string): string => {
    if (state === 'known') {
        return 'border-rule bg-raised text-ink';
    }

    if (state === 'unknown') {
        return 'border-rule bg-raised text-risk';
    }

    return 'border-dashed border-rule bg-raised text-ink-muted';
};
</script>

<template>
    <AppLayout>
        <Head title="Review snapshot" />
        <template #title>Review snapshot</template>

        <p class="max-w-3xl text-sm text-ink-muted">
            This is exactly what will be written. Read it back: a field marked Unknown is one the client
            did not show, and a field marked Not provided is one you left alone. They are different
            statements, and the run keeps them apart.
        </p>

        <!-- The HFW's snapshot stepper: Entry / Setup / Review, with the live step named as well as
             tinted. The bare review URL is the "nothing entered" state, so it is a legitimate stop
             rather than a blank form. -->
        <nav aria-label="Snapshot steps" class="mt-3 flex flex-wrap items-center gap-2 text-sm">
            <template v-for="step in steps" :key="step.key">
                <a
                    v-if="step.href !== '#'"
                    :href="step.href"
                    class="inline-flex min-h-11 items-center rounded-full border border-rule px-3 font-medium text-ink hover:bg-raised"
                >{{ step.label }}</a>
                <span
                    v-else
                    aria-current="step"
                    class="inline-flex min-h-11 items-center rounded-full border-2 border-pick-line bg-green-tint px-3 font-bold text-ink-strong"
                >{{ step.label }}</span>
            </template>
        </nav>

        <p v-if="!props.has_draft" role="status" class="mt-3 max-w-3xl rounded-md border border-dashed border-rule bg-raised px-3 py-2 text-sm text-ink-muted">
            No snapshot has been entered yet, so every field reads "Not provided". Fill the form first;
            this screen is the read-back, not the entry.
        </p>

        <p v-if="props.position_label" class="mt-2 max-w-3xl text-sm font-medium text-ink-strong">
            Position: {{ props.position_label }}
        </p>

        <section aria-labelledby="snapshot-review-heading" class="mt-6 max-w-3xl rounded-md border border-rule bg-panel p-4">
            <ul class="mt-3 grid gap-2 g-2">
                <li
                    v-for="field in props.fields"
                    :key="field.key"
                    class="flex min-h-11 items-center rounded-md border px-3 text-sm"
                    :class="treatment(field.state)"
                    :data-state="field.state"
                >
                    {{ field.display }}
                </li>
            </ul>
        </section>

        <div
            v-if="form.hasErrors"
            ref="alertRef"
            role="alert"
            tabindex="-1"
            class="mt-6 max-w-3xl rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk"
        >
            <p class="font-semibold">The snapshot was not recorded.</p>
            <ul class="mt-1 list-disc pl-4">
                <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
            </ul>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button
                type="button"
                :disabled="form.processing || !props.has_draft"
                class="inline-flex min-h-11 items-center rounded-md bg-primary-action px-4 text-sm font-bold text-on-primary-action disabled:opacity-60"
                @click="submit"
            >
                {{ form.processing ? 'Recording…' : 'Record snapshot' }}
            </button>
            <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Recording the snapshot…</p>
            <a :href="props.back" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out">Edit the snapshot</a>
        </div>
    </AppLayout>
</template>
