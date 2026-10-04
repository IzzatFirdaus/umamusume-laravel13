<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import CandidateForm from '../../components/review/CandidateForm.vue';
import { Head } from '@inertiajs/vue3';

interface Candidate {
    id: number;
    proposed_name: string;
    proposed_name_ja: string | null;
    match_tier: string;
    match_tier_label: string;
    source_key: string;
    created_at: string;
    suggestion: string | null;
    suggested_umamusume_id: number | null;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

defineProps<{
    candidates: Paginator<Candidate>;
    aliasLanguages: { value: string; label: string }[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Review queue" />
        <template #title>Review queue</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Match review queue</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Fuzzy and unmatched fetch results land here. The engine never merges them without your
            decision.
        </p>

        <p
            v-if="candidates.data.length === 0"
            class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
        >
            Nothing pending. Run `php artisan uma:fetch` to populate.
        </p>

        <template v-else>
            <ul class="mt-6 space-y-4">
                <li
                    v-for="candidate in candidates.data"
                    :key="candidate.id"
                    class="rounded-md border border-rule bg-raised p-4 text-sm"
                >
                    <div class="flex items-baseline justify-between">
                        <span class="font-medium text-ink-strong">{{ candidate.proposed_name }}</span>
                        <span class="text-xs text-ink-muted">
                            {{ candidate.match_tier_label }} · {{ candidate.source_key }} ·
                            {{ candidate.created_at }}
                        </span>
                    </div>
                    <p v-if="candidate.proposed_name_ja" class="text-ink-muted">
                        {{ candidate.proposed_name_ja }}
                    </p>
                    <p v-if="candidate.suggestion" class="text-ink-muted">
                        Suggestion: {{ candidate.suggestion }}
                    </p>

                    <CandidateForm :candidate="candidate" :alias-languages="aliasLanguages" />
                </li>
            </ul>

            <nav
                v-if="candidates.links.length > 3"
                aria-label="Pagination"
                class="mt-4 flex flex-wrap gap-2 text-sm"
            >
                <template v-for="(link, index) in candidates.links" :key="index">
                    <a
                        v-if="link.url"
                        :href="link.url"
                        :aria-current="link.active ? 'page' : undefined"
                        class="rounded-md border border-rule px-3 py-1 text-ink hover:bg-raised"
                        :class="link.active ? 'bg-raised font-bold text-ink-strong' : ''"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 text-ink-muted" v-html="link.label" />
                </template>
            </nav>
        </template>
    </AppLayout>
</template>
