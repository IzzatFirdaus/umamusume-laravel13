<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    theme: 'light' | 'dark' | null;
    failureEstimate: 'on' | 'off';
}>();

// US-11: the third theme value, follow-the-OS, is the absence of a row, so it is sent as an
// empty string and stored by dropping the row (the request turns it into null).
const form = useForm({
    theme: props.theme ?? '',
    failure_estimate: props.failureEstimate,
});

function onEstimateToggle(event: Event): void {
    form.failure_estimate = (event.target as HTMLInputElement).checked ? 'on' : 'off';
}

function submit(): void {
    form.put('/preferences');
}
</script>

<template>
    <AppLayout>
        <Head title="Preferences" />
        <template #title>Preferences</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Preferences</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Two keys, stored in this app's database and read back on the next request. Nothing here
            is kept in the browser, because PRD §6 non-goal 12 cuts a second source of truth.
        </p>

        <form
            class="mt-6 grid max-w-3xl grid-cols-1 gap-4 rounded-md border border-rule bg-raised p-4 text-sm sm:grid-cols-2"
            @submit.prevent="submit"
        >
            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">Theme</span>
                <select
                    v-model="form.theme"
                    name="theme"
                    class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                >
                    <!-- The third authorized value is the absence of a row, so "follow the system"
                         writes nothing rather than storing the word `system`. -->
                    <option value="">Follow the system</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                </select>
            </label>

            <label class="inline-flex min-h-11 items-center gap-2 self-end">
                <input
                    type="checkbox"
                    name="failure_estimate"
                    class="h-5 w-5 accent-chrome"
                    :checked="form.failure_estimate === 'on'"
                    @change="onEstimateToggle"
                >
                <span class="font-medium text-ink">Numeric failure estimate</span>
            </label>

            <p v-if="form.errors.theme" class="w-full text-risk">{{ form.errors.theme }}</p>
            <p v-if="form.errors.failure_estimate" class="w-full text-risk">
                {{ form.errors.failure_estimate }}
            </p>
            <p v-if="form.errors.preferences" class="w-full text-risk">{{ form.errors.preferences }}</p>

            <p class="text-xs text-ink-muted sm:col-span-2">
                Turning the estimate on changes no screen yet. `ADR-0001` §3 records that no source
                publishes a failure curve and requires any number this tool prints to show its
                formula beside it, so there is nothing honest to render until a model exists. The
                choice is stored, and the band the app can source from Energy (Safe, Caution, Danger)
                shows either way.
            </p>

            <button
                type="submit"
                class="inline-flex min-h-11 items-center justify-self-start rounded-md border border-rule bg-raised px-4 text-sm font-bold text-ink-strong hover:bg-panel"
            >
                Save preferences
            </button>
        </form>
    </AppLayout>
</template>
