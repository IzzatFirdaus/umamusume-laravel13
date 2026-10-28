<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
// FQ-10: `CandidateForm` renders on `Review/Detail.vue`; the queue links to it instead.
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
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
    neverFetched: boolean;
    /** Candidates whose verdict landed today (`status` left `Pending`, counted on `updated_at`). */
    resolvedToday: number;
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

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-3 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-3 text-right align-top text-sm text-ink';

function badgeFor(tier: string, sourceKey: string): { state: 'estimated' | 'unknown'; title: string } {
    return tier === 'Fuzzy'
        ? { state: 'estimated', title: `Fuzzy match from ${sourceKey}` }
        : { state: 'unknown', title: `No match from ${sourceKey}` };
}
</script>

<template>
    <AppLayout>
        <Head title="Confirm or reject matches" />
        <template #eyebrow>Review queue</template>
        <template #title>Confirm or reject matches</template>

        <p class="mt-1 max-w-3xl text-sm text-ink-muted">
            When the importer is unsure which catalog record a line refers to, it asks. Nothing is
            written until you answer. Fuzzy and unmatched fetch results land here, and the engine never
            merges them without your decision.
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
                Open a row to set its verdict and resolve it.
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
                        <span v-if="props.resolvedToday > 0"> · {{ props.resolvedToday }} today</span>
                    </p>
                </div>
                <!-- One Pending table, in the wireframe's columns (`review.html:69-101`). Both
                     halves of the queue are rows: a candidate with a proposal is answered by comparing
                     it, and a candidate without one is a question with no answer to compare. -->
                <div class="mt-3 overflow-x-auto rounded-md border border-rule bg-raised">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th :class="th">Candidate</th>
                                <th :class="th">Question</th>
                                <th :class="th">Best match</th>
                                <th :class="thNum">Confidence</th>
                                <th :class="thNum"><span class="sr-only">Review</span></th>
                            </tr>
                        </thead>
                        <tbody id="review-results">
                            <tr v-for="candidate in props.candidates.data" :key="candidate.id">
                                <td :class="td">
                                    <b class="text-ink-strong">{{ candidate.proposed_name }}</b>
                                    <span v-if="candidate.proposed_name_ja" lang="ja" class="block text-xs text-ink-muted">
                                        {{ candidate.proposed_name_ja }}
                                    </span>
                                    <span class="mt-0.5 block text-xs text-ink-muted">
                                        from {{ candidate.source_key }}
                                        <time :datetime="candidate.created_at"> · {{ candidate.created_at }}</time>
                                    </span>
                                </td>
                                <td :class="td">
                                    <template v-if="candidate.suggested_umamusume_id !== null">Which trainee is this?</template>
                                    <template v-else>No catalog record carries this name.</template>
                                </td>
                                <td :class="td">
                                    <template v-if="candidate.suggested_umamusume_id !== null">
                                        {{ candidate.suggestion }}
                                        <SuggestionHint
                                            v-if="candidate.suggestion"
                                            :suggestion="candidate.suggestion"
                                            :umamusume-id="candidate.suggested_umamusume_id"
                                            :source-key="candidate.source_key"
                                        />
                                    </template>
                                    <span v-else class="pill pill-caution">No match</span>
                                </td>
                                <td :class="[tdNum, 'text-right']">
                                    <!-- `ProvenanceBadge` is the single owner of the state word
                                         (DESIGN.md §5(b), 2026-10-17 amendment). -->
                                    <ProvenanceBadge
                                        :state="badgeFor(candidate.match_tier, candidate.source_key).state"
                                        :title="badgeFor(candidate.match_tier, candidate.source_key).title"
                                    />
                                </td>
                                <td :class="[td, 'text-right']">
                                    <Link
                                        :href="`/review/${candidate.id}`"
                                        :aria-label="`Review ${candidate.proposed_name}`"
                                        data-testid="review-detail-link"
                                        class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:text-ink-strong transition-colors duration-150 ease-out"
                                    >Review</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- The wireframe's two reference cards, side by side (`review.html:103-135`). They
                 are open on the page: the table above is shorter than the cards it replaced, so the
                 2,700px ceiling `review.spec.ts` measures still holds with them visible. -->
            <div class="mt-4 grid gap-4 g-side">
                <section class="card" aria-labelledby="review-signals-heading">
                    <h2 id="review-signals-heading">How a match is decided</h2>

                    <table class="t mt-3 w-full text-sm">
                        <caption class="sr-only">The signals a match is decided on and what each is worth</caption>
                        <thead>
                            <tr class="text-left text-xs text-ink-muted">
                                <th scope="col" class="pb-1 font-semibold">Signal</th>
                                <th scope="col" class="pb-1 font-semibold">Weight</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            <tr>
                                <td class="py-2">Exact name match</td>
                                <td class="py-2 text-ink-muted">Decisive, offered as the default</td>
                            </tr>
                            <tr>
                                <td class="py-2">Japanese name match</td>
                                <td class="py-2 text-ink-muted">Decisive when the romanised form differs</td>
                            </tr>
                            <tr>
                                <td class="py-2">Known alias</td>
                                <td class="py-2 text-ink-muted">Strong, shown as a separate candidate</td>
                            </tr>
                            <tr>
                                <td class="py-2">Fuzzy spelling</td>
                                <td class="py-2 text-ink-muted">Suggested only, never pre-selected</td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="note mt-3 text-xs text-ink-muted">
                        A fuzzy match is <b>never</b> applied automatically. The importer may offer it;
                        only a Trainer may accept it.
                    </p>
                </section>

                <section class="card" aria-labelledby="review-why-heading">
                    <h2 id="review-why-heading">Why this exists</h2>

                    <p class="mt-2 text-sm text-ink-muted">
                        A wrong match silently poisons every downstream screen: a run attributed to
                        the wrong trainee gets the wrong aptitudes, growth row and unique skill. The
                        queue exists so a guess becomes a question.
                    </p>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="tile">
                            <div class="k">Pending</div>
                            <div class="v font-mono text-sm">{{ props.candidates.total }}</div>
                        </div>
                        <div class="tile">
                            <div class="k">Resolved today</div>
                            <div class="v font-mono text-sm">{{ props.resolvedToday }}</div>
                        </div>
                    </div>
                </section>
            </div>

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