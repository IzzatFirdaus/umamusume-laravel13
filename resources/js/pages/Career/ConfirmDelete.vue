<script setup lang="ts">
/**
 * The delete-run confirmation door (`C6b.3`, KI-144).
 *
 * The Cockpit's `Career tools` disclosure hosted a `Delete this career` disclosure as the interim
 * home for writes the retired record screen owned. The disclosure cannot retire until each write
 * has a door of its own, and deletion is the one write that must never happen by accident — so its
 * door is a page that states what will be lost and refuses until the Trainer says so.
 *
 * The DELETE itself stays on `runs.destroy`, which refuses a request with no confirmation flag.
 */
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import CareerLayout from '../../layouts/CareerLayout.vue';
import LinkButton from '../../components/Form/LinkButton.vue';
import PageHeader from '../../components/ui/PageHeader.vue';

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        status: string;
        status_label: string;
        scenario_label: string;
        run_url: string;
    };
    counts: { turns: number; races: number };
    destroy_url: string;
    index_url: string;
}>();

const acknowledged = ref(false);
const form = useForm({ confirmed: true });

/** What this page is for, in one line, under the one `h1`. */
const lede = computed(() => `${props.counts.turns} turns and ${props.counts.races} races recorded on this career.`);

const submit = (): void => {
    if (!acknowledged.value) {
        return;
    }

    form.delete(props.destroy_url);
};
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :trainee-ja="props.run.trainee_ja"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :status="props.run.status"
        :position="null"
        :run-url="props.run.run_url"
        current="cockpit"
    >
        <Head :title="`Delete this career: ${props.run.trainee}`" />

        <template #head>
            <PageHeader eyebrow="Delete this career" :title="props.run.trainee" :description="lede">
                <template #actions>
                    <LinkButton :href="props.run.run_url + '/cockpit'" variant="secondary" size="sm">
                        Cancel
                    </LinkButton>
                </template>
            </PageHeader>
        </template>

        <section aria-labelledby="confirm-delete-heading" class="card mt-4">
            <h2 id="confirm-delete-heading" class="text-lg font-semibold text-ink-strong">
                This cannot be undone
            </h2>
            <p class="sub">
                Deleting this career removes the run and everything recorded against it.
            </p>

            <ul class="mt-3 list-disc pl-5 text-sm">
                <li><b>{{ props.counts.turns }}</b> recorded {{ props.counts.turns === 1 ? 'turn' : 'turns' }}</li>
                <li><b>{{ props.counts.races }}</b> {{ props.counts.races === 1 ? 'race' : 'races' }}</li>
                <li>Its build target, its skill plan and its inheritance selection</li>
            </ul>

            <form class="mt-4 flex flex-col gap-3" @submit.prevent="submit">
                <label class="flex items-start gap-2 text-sm">
                    <input
                        v-model="acknowledged"
                        type="checkbox"
                        id="confirmed"
                        class="mt-0.5"
                        required
                    />
                    <span>I understand this cannot be undone.</span>
                </label>

                <p v-if="form.errors.confirmed" role="alert" class="text-sm text-risk">
                    {{ form.errors.confirmed }}
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="min-h-11 rounded-full border-2 border-risk px-4 font-semibold text-risk disabled:opacity-60"
                        :disabled="!acknowledged || form.processing"
                    >
                        {{ form.processing ? 'Deleting…' : 'Delete this career' }}
                    </button>
                    <LinkButton :href="props.index_url" variant="secondary" size="sm">
                        Back to all careers
                    </LinkButton>
                </div>
            </form>
        </section>
    </CareerLayout>
</template>
