<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import CandidateForm from '../../components/review/CandidateForm.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import StatusPill from '../../components/StatusPill.vue';
import SuggestionHint from '../../components/review/SuggestionHint.vue';
import { Head, Link } from '@inertiajs/vue3';

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

const props = defineProps<{
    candidate: Candidate;
    aliasLanguages: { value: string; label: string }[];
}>();

/** The queue's own pill mapping, kept in step with it by a spec rather than by an import. */
function pillFor(tier: string): { label: string; variant: 'warning' | 'neutral' } {
    return tier === 'Fuzzy' ? { label: 'Fuzzy', variant: 'warning' } : { label: 'No match', variant: 'neutral' };
}

/** A suggestion the engine could not confirm is `estimated`; no suggestion at all is `unknown`. */
function badgeFor(tier: string, sourceKey: string): { state: 'estimated' | 'unknown'; title: string } {
    const estimated = tier === 'Fuzzy';

    return {
        state: estimated ? 'estimated' : 'unknown',
        title: estimated
            ? `Fuzzy match from ${sourceKey}; confirm or reject it below.`
            : `No catalog row fuzzy-matches this name (source ${sourceKey}).`,
    };
}
</script>

<template>
    <Head :title="`Review ${props.candidate.proposed_name}`" />

    <AppLayout>
        <div class="mx-auto w-full max-w-3xl">
            <nav aria-label="Breadcrumb" class="text-sm">
                <Link href="/review" class="text-ink-muted hover:text-ink-strong transition-colors duration-150 ease-out">
                    ← Review queue
                </Link>
            </nav>

            <!-- The detail region is the page, and it appears once: the route exists so the queue does
                 not render a verdict form per row, and the browser spec pins the count at one. -->
            <section
                aria-labelledby="review-detail-heading"
                data-testid="review-detail"
                class="mt-3 rounded-md border border-rule bg-panel px-4 py-3"
            >
                <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 id="review-detail-heading" class="text-base font-semibold text-ink-strong">
                            {{ props.candidate.proposed_name }}
                        </h1>
                        <span v-if="props.candidate.proposed_name_ja" lang="ja" class="text-ink-muted">
                            {{ props.candidate.proposed_name_ja }}
                        </span>
                        <StatusPill
                            v-if="props.candidate.match_tier === 'Fuzzy'"
                            :label="pillFor(props.candidate.match_tier).label"
                            :variant="pillFor(props.candidate.match_tier).variant"
                            size="sm"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <ProvenanceBadge
                            :state="badgeFor(props.candidate.match_tier, props.candidate.source_key).state"
                            :title="badgeFor(props.candidate.match_tier, props.candidate.source_key).title"
                        />
                        <time class="text-xs text-ink-muted" :datetime="props.candidate.created_at">
                            {{ props.candidate.created_at }}
                        </time>
                    </div>
                </div>

                <SuggestionHint
                    v-if="props.candidate.suggestion"
                    :suggestion="props.candidate.suggestion"
                    :umamusume-id="props.candidate.suggested_umamusume_id"
                    :source-key="props.candidate.source_key"
                />

                <CandidateForm
                    :candidate="props.candidate"
                    :alias-languages="props.aliasLanguages"
                    :accented="true"
                    :default-expanded="true"
                />
            </section>
        </div>
    </AppLayout>
</template>
