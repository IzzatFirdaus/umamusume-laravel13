<script setup lang="ts">
import AppLayout from '../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    version: string | null;
    buildSha: string | null;
    feedUrl: string | null;
}>();
</script>

<template>
    <AppLayout>
        <Head title="About" />
        <template #title>About</template>

        <div class="max-w-2xl space-y-4">
            <h2 class="text-2xl font-semibold text-ink-strong">Trainer Desk</h2>

            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-ink">Version</dt>
                    <dd class="font-mono text-ink-strong">{{ version ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink">Build</dt>
                    <dd class="font-mono text-ink-muted">{{ buildSha ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink">Release feed</dt>
                    <dd class="text-ink-muted">
                        <a v-if="feedUrl" :href="feedUrl" class="text-pick-line hover:underline" target="_blank" rel="noopener">
                            {{ feedUrl }}
                        </a>
                        <span v-else title="No release feed configured">N/A</span>
                    </dd>
                </div>
            </dl>
        </div>
    </AppLayout>
</template>