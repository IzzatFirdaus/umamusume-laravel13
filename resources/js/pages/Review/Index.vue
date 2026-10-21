<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import CandidateForm from '../../components/review/CandidateForm.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import StatusPill from '../../components/StatusPill.vue';
import StatusBadge from '../../components/ui/StatusBadge.vue';
import SuggestionHint from '../../components/review/SuggestionHint.vue';
import Pagination from '../../components/ui/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';

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
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    candidates: Paginator<Candidate>;
    aliasLanguages: { value: string; label: string }[];
    neverFetched: boolean;
}>();

/**
 * The canonical pager owns the range, so the page owns the request. The pager it replaces was a set
 * of Inertia links built from the server's own paginator, and those links carried the query string
 * with them, so this rebuilds the same query with the new page number rather than replacing it.
 */
function goToPage(page: number): void {
    const query = new URLSearchParams(window.location.search);
    query.set('page', String(page));

    router.get(`${window.location.pathname}?${query.toString()}`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function pillFor(tier: string): { label: string; variant: 'warning' | 'neutral' } {
    return tier === 'Fuzzy'
        ? { label: 'Fuzzy match', variant: 'warning' }
        : { label: 'No match', variant: 'neutral' };
}

function badgeFor(tier: string, sourceKey: string): { state: 'estimated' | 'unknown'; title: string } {
    return tier === 'Fuzzy'
        ? { state: 'estimated', title: `Fuzzy match from ${sourceKey}` }
        : { state: 'unknown', title: `No match from ${sourceKey}` };
}
</script>

<template>
    <AppLayout>
        <Head title="Review queue" />
        <template #eyebrow>Review</template>
        <template #title>Review queue</template>

        <h2 class="text-lg font-semibold text-ink-strong">Match review queue</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Fuzzy and unmatched fetch results land here. The engine never merges them without your
            decision.
        </p>

        <!-- Two different absences. A queue someone has worked through is empty for a reason, and a
             database nobody has ever fetched has never had anything to review; telling a Trainer to run
             the fetch when they cleared six candidates an hour ago is the wrong instruction (R-27). -->
         <p
             v-if="candidates.data.length === 0 && neverFetched"
             class="mt-8 rounded-md border border-dashed border-rule bg-panel p-6 text-sm text-ink-muted"
         >
            Nothing pending. This database has never been fetched, so there is nothing here yet. Run
            <code>php artisan uma:fetch</code> to populate.
        </p>
        <p
             v-else-if="candidates.data.length === 0"
             class="mt-8 rounded-md border border-dashed border-rule bg-panel p-6 text-sm text-ink-muted"
         >
            Nothing pending. Every match the engine could not decide has had your verdict. The rows it
            promoted are on the <Link href="/database">Database screens</Link>.
        </p>

        <template v-else>
            <p class="mt-4 text-sm text-ink-muted">
                {{ candidates.data.length }} of {{ candidates.total }} items to resolve.
            </p>
            <p class="mt-2 text-xs text-ink-muted">
                No suggestion means the source name does not fuzzy-match any trainee.
            </p>
            <!-- The verdict gate's reason, once. Every card's Resolve stays disabled until its own
                 verdict is set; a copy of this sentence on each of twenty-five rows said it
                 twenty-five times and buried the buttons it was about. -->
            <p class="mt-1 text-xs text-ink-muted">
                Set a verdict on any row to enable its resolve.
            </p>

            <ul class="mt-3 space-y-3">
                <li
                    v-for="(candidate, index) in candidates.data"
                    :key="candidate.id"
                    class="rounded-md border border-rule bg-panel p-4 text-sm"
                >
                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium text-ink-strong">{{ candidate.proposed_name }}</span>
                            <!-- Plan 1.6: the match verdict is an exception when it is not a match,
                                 and StatusBadge is the component that says so in one worded pill.
                                 "Fuzzy match" stays a StatusPill because it is a softer warning
                                 rather than an absence; "No match" is the absence, and it is
                                 StatusBadge's `unknown` case. -->
                            <StatusBadge
                                v-if="candidate.match_tier !== 'Fuzzy'"
                                state="unknown"
                                label="No match"
                            />
                            <StatusPill
                                v-else
                                :label="pillFor(candidate.match_tier).label"
                                :variant="pillFor(candidate.match_tier).variant"
                                size="sm"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <ProvenanceBadge
                                :state="badgeFor(candidate.match_tier, candidate.source_key).state"
                                :title="badgeFor(candidate.match_tier, candidate.source_key).title"
                            />
                            <time class="text-xs text-ink-muted" :datetime="candidate.created_at">{{ candidate.created_at }}</time>
                        </div>
                    </div>
                    <p v-if="candidate.proposed_name_ja" lang="ja" class="text-ink-muted">
                        {{ candidate.proposed_name_ja }}
                    </p>
                    <SuggestionHint
                        v-if="candidate.suggestion"
                        :suggestion="candidate.suggestion"
                        :umamusume-id="candidate.suggested_umamusume_id"
                        :source-key="candidate.source_key"
                    />

                    <CandidateForm
                        :candidate="candidate"
                        :alias-languages="aliasLanguages"
                        :accented="index === 0"
                    />
                </li>
            </ul>

            <Pagination
                :page="props.candidates.current_page"
                :pages="props.candidates.last_page"
                :from="props.candidates.from ?? 0"
                :to="props.candidates.to ?? 0"
                :total="props.candidates.total"
                noun="candidates"
                @navigate="goToPage"
            />
        </template>
    </AppLayout>
</template>