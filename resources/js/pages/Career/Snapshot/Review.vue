<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

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
    action: string;
    back: string;
}>();

const form = useForm(props.payload);

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

        <p class="max-w-2xl text-sm text-ink-muted">
            This is exactly what will be written. Read it back: a field marked Unknown is one the client
            did not show, and a field marked Not provided is one you left alone. They are different
            statements, and the run keeps them apart.
        </p>

        <section aria-labelledby="snapshot-review-heading" class="mt-6 max-w-3xl rounded-md border border-rule bg-panel p-4">
            <h2 id="snapshot-review-heading" class="text-base font-semibold text-ink-strong">Current state</h2>
            <ul class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
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

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button
                type="button"
                :disabled="form.processing"
                class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:opacity-60"
                @click="submit"
            >
                {{ form.processing ? 'Recording…' : 'Record snapshot' }}
            </button>
            <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Recording the snapshot…</p>
            <a :href="props.back" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised">Edit the snapshot</a>
        </div>
    </AppLayout>
</template>
