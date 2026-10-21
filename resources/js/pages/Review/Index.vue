<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import CandidateForm from '../../components/review/CandidateForm.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import StatusPill from '../../components/StatusPill.vue';
import StatusBadge from '../../components/ui/StatusBadge.vue';
import SuggestionHint from '../../components/review/SuggestionHint.vue';
import Pagination from '../../components/ui/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

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

/*
 * The queue is two questions, so it renders as two sections.
 *
 * The controller answers one paginated list and already orders a suggestion ahead of no suggestion
 * (`orderByRaw('suggested_umamusume_id IS NULL')`), so the split is a presentation fact and not a
 * second query: the array arrives in the order the two groups want, and filtering it partitions it
 * without re-sorting either half.
 *
 *   - **Needs a decision** is the half the Trainer can settle in one click: the engine proposed a
 *     catalog row, so each card is a comparison and a verdict.
 *   - **No fuzzy match** is the half that needs research: nothing was proposed, so the row is a
 *     name and a form.
 *
 * One list made the Trainer read twenty-five cards to find the two they could act on, and the
 * proposal's collapse target (5,162px to ~1,000px) is mostly this: the no-match half collapses to
 * one line each.
 */
const withSuggestion = computed<Candidate[]>(() => props.candidates.data.filter((candidate) => candidate.suggested_umamusume_id !== null));
const withoutSuggestion = computed<Candidate[]>(() => props.candidates.data.filter((candidate) => candidate.suggested_umamusume_id === null));

/*
 * Progress is measured against the whole queue, not the page.
 *
 * `candidates.total` is every Pending row the paginator counts, so "resolved" is the complement of
 * what is still pending: the queue this screen lists IS the unresolved set, which is why the count
 * runs downward as verdicts land rather than upward. Reported as resolved-of-total so the bar fills
 * as the Trainer works, which is the reading a progress bar invites.
 */
const resolvedCount = computed<number>(() => Math.max(0, props.candidates.total - props.candidates.data.length));
const progressPercent = computed<number>(() => (props.candidates.total === 0 ? 0 : Math.round((resolvedCount.value / props.candidates.total) * 100)));

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
            <p class="mt-2 text-xs text-ink-muted">
                No suggestion means the source name does not fuzzy-match any trainee.
            </p>
            <!-- The verdict gate's reason, once. Every card's Resolve stays disabled until its own
                 verdict is set; a copy of this sentence on each of twenty-five rows said it
                 twenty-five times and buried the buttons it was about. -->
            <p class="mt-1 text-xs text-ink-muted">
                Set a verdict on any row to enable its resolve.
            </p>

            <template v-if="candidates.data.length > 0">
                <!-- PROGRESS: a bar and a number. "N of M items to resolve" was a sentence a reader
                     had to parse; the bar answers "how far through am I" at a glance, and the
                     number beside it keeps the exact figure for anyone who needs it. The fill is
                     the chrome role, not the action role: `bg-primary-action` is this screen's one
                     accented commit button and the spec pins that count at one. -->
                <div class="mt-4 flex items-center gap-3">
                    <div
                        class="h-2 flex-1 overflow-hidden rounded-full bg-sunken"
                        role="progressbar"
                        aria-label="Review progress"
                        :aria-valuenow="resolvedCount"
                        aria-valuemin="0"
                        :aria-valuemax="candidates.total"
                    >
                        <div
                            class="h-full rounded-full bg-brand-chrome transition-[width] duration-150 ease-out motion-reduce:transition-none"
                            :style="{ width: `${progressPercent}%` }"
                        />
                    </div>
                    <p class="shrink-0 text-xs text-ink-muted">
                        {{ resolvedCount }} of {{ candidates.total }} resolved
                    </p>
                </div>
                <!-- NEEDS A DECISION: the engine proposed a row, so each card is a comparison and a
                     one-click verdict. -->
                <section
                    v-if="withSuggestion.length > 0"
                    aria-labelledby="review-with-suggestion"
                    class="mt-4"
                >
                    <h3 id="review-with-suggestion" class="text-base font-semibold text-ink-strong">
                        Needs a decision
                        <span class="font-normal text-ink-muted">({{ withSuggestion.length }})</span>
                    </h3>
                    <p class="mt-1 text-xs text-ink-muted">
                        The engine proposed a catalog row for each of these. Confirm, alias or reject it.
                    </p>
                    <ul class="mt-3 space-y-3">
                        <li
                            v-for="(candidate, index) in withSuggestion"
                            :key="candidate.id"
                            class="rounded-md border border-rule bg-panel p-4 text-sm"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-ink-strong">{{ candidate.proposed_name }}</span>
                                    <!-- "Fuzzy match" stays a StatusPill because it is a softer warning
                                         rather than an absence; "No match" is the absence and is
                                         StatusBadge's `unknown` case (plan 1.6). -->
                                    <StatusPill
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
                </section>

                <!-- NO FUZZY MATCH: nothing was proposed, so the row is a name and a form. -->
                <section
                    v-if="withoutSuggestion.length > 0"
                    aria-labelledby="review-without-suggestion"
                    class="mt-6"
                >
                    <h3 id="review-without-suggestion" class="text-base font-semibold text-ink-strong">
                        No fuzzy match
                        <span class="font-normal text-ink-muted">({{ withoutSuggestion.length }})</span>
                    </h3>
                    <p class="mt-1 text-xs text-ink-muted">
                        Nothing in the catalog fuzzy-matches these names. Enter the id to merge into, or reject them.
                    </p>
                    <ul class="mt-3 space-y-3">
                        <li
                            v-for="candidate in withoutSuggestion"
                            :key="candidate.id"
                            class="rounded-md border border-rule bg-panel p-4 text-sm"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-ink-strong">{{ candidate.proposed_name }}</span>
                                    <StatusBadge state="unknown" label="No match" />
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

                            <CandidateForm
                                :candidate="candidate"
                                :alias-languages="aliasLanguages"
                                :accented="false"
                            />
                        </li>
                    </ul>
                </section>
            </template>

            <!-- The pager is pinned to the bottom of the viewport while the queue scrolls under it:
                 on a twenty-five card page the next page was a scroll away from the card being
                 worked on. `main` carries `@container`, which makes `position: sticky` a no-op for a
                 descendant (containment.spec.ts pins that), so the pin uses the fixed-position
                 footer idiom `StickyFooter.vue` ships rather than a bare `sticky` utility. -->
            <div
                class="sticky bottom-0 z-10 -mx-4 mt-6 border-t border-rule bg-panel px-4 py-3 md:-mx-8 md:px-8"
                data-testid="review-pager"
            >
                <Pagination
                    :page="props.candidates.current_page"
                    :pages="props.candidates.last_page"
                    :from="props.candidates.from ?? 0"
                    :to="props.candidates.to ?? 0"
                    :total="props.candidates.total"
                    noun="candidates"
                    @navigate="goToPage"
                />
            </div>
        </template>
    </AppLayout>
</template>