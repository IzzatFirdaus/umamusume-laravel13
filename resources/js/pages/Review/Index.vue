<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
// FQ-10: `CandidateForm` renders on `Review/Detail.vue`; the queue links to it instead.
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
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
    review_url: string;
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
    queueUrl: string;
    databaseUrl: string;
    nextReviewUrl: string | null;
    /** Candidates whose verdict landed today (`status` left `Pending`, counted on `updated_at`). */
    resolvedToday: number;
    /** Total candidates resolved (all-time, non-Pending). */
    resolvedCount: number;
}>();

function goToPage(page: number): void {
    router.get(props.queueUrl, { page }, { preserveState: true, preserveScroll: true });
}

/*
 * Progress is measured against the whole queue, not the page.
 *
 * `candidates.total` is every Pending row the paginator counts, so "resolved" is the complement of
 * what is still pending: the queue this screen lists IS the unresolved set, which is why the count
 * runs downward as verdicts land rather than upward. Reported as resolved-of-total so the bar fills
 * as the Trainer works, which is the reading a progress bar invites.
 */
const resolvedCount = computed<number>(() => props.resolvedCount);
const totalCount = computed<number>(() => props.resolvedCount + props.candidates.total);
const progressPercent = computed<number>(() => (totalCount.value === 0 ? 0 : Math.round((resolvedCount.value / totalCount.value) * 100)));

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-3 align-top text-sm text-ink';

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
            Compare the imported name with the catalog and record a verdict for each case.
            If the importer cannot fuzzy-match any trainee, the row has no suggestion.
        </p>
        <dl class="mt-4 grid grid-cols-3 gap-2 rounded-md border border-rule-soft bg-panel p-3">
            <div>
                <dt class="text-xs text-ink-muted">Pending</dt>
                <dd class="font-mono text-lg font-semibold tabular-nums text-ink-strong">{{ candidates.total }}</dd>
            </div>
            <div>
                <dt class="text-xs text-ink-muted">Resolved today</dt>
                <dd class="font-mono text-lg font-semibold tabular-nums text-ink-strong">{{ resolvedToday }}</dd>
            </div>
            <div>
                <dt class="text-xs text-ink-muted">Rows shown</dt>
                <dd class="font-mono text-lg font-semibold tabular-nums text-ink-strong">{{ candidates.data.length }}</dd>
            </div>
        </dl>

        <Link
            v-if="nextReviewUrl"
            :href="nextReviewUrl"
            class="mt-3 inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-3 text-sm font-semibold text-ink-strong hover:bg-sunken"
        >
            Review next pending case
        </Link>

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
            promoted are on the <Link :href="databaseUrl" class="inline-flex min-h-11 items-center underline">Database screens</Link>.
        </p>

        <template v-else>
            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1">
                <div
                    class="h-2 min-w-16 flex-1 overflow-hidden rounded-full bg-sunken"
                    role="progressbar"
                    aria-label="All-time review progress"
                    :aria-valuenow="resolvedCount"
                    :aria-valuemin="0"
                    :aria-valuemax="totalCount"
                >
                    <div
                        class="h-full rounded-full bg-green-deep transition-[width] duration-150 ease-out motion-reduce:transition-none"
                        :style="{ width: `${progressPercent}%` }"
                    />
                </div>
                <p class="text-xs text-ink-muted">{{ resolvedCount }} of {{ totalCount }} resolved, all time</p>
            </div>

            <h2 class="mt-5 text-lg font-semibold text-ink-strong">Pending cases</h2>
            <div class="mt-3 min-w-0 overflow-x-auto scroll-p-2 rounded-md border border-rule bg-raised" role="region" tabindex="0" aria-label="Pending match candidates, scrollable">
                <table class="w-full min-w-2xl border-collapse md:min-w-0">
                    <caption class="sr-only">Pending candidates and their match evidence</caption>
                    <thead>
                        <tr>
                            <th scope="col" :class="th">Imported record</th>
                            <th scope="col" :class="th">Suggested match</th>
                            <th scope="col" :class="th">Evidence</th>
                            <th scope="col" :class="thNum"><span class="sr-only">Review</span></th>
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
                            <td :class="td">{{ candidate.suggestion ?? 'No suggestion' }}</td>
                            <td :class="td">
                                <span class="mr-1">{{ candidate.match_tier_label }}</span>
                                <ProvenanceBadge
                                    :state="badgeFor(candidate.match_tier, candidate.source_key).state"
                                    :title="badgeFor(candidate.match_tier, candidate.source_key).title"
                                />
                            </td>
                            <td :class="[td, 'text-right']">
                                <Link
                                    :href="candidate.review_url"
                                    :aria-label="`Review ${candidate.proposed_name}`"
                                    data-testid="review-detail-link"
                                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:text-ink-strong transition-colors duration-150 ease-out"
                                >Review</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- The wireframe's two reference cards, side by side (`review.html:103-135`). They
                 are open on the page: the table above is shorter than the cards it replaced, so the
                 2,700px ceiling `review.spec.ts` measures still holds with them visible. -->
            <div class="mt-4 grid gap-4 g-side">
                <section class="card" aria-labelledby="review-signals-heading">
                    <h2 id="review-signals-heading">How a match is decided</h2>

                    <div class="mt-3 overflow-x-auto" role="region" tabindex="0" aria-label="How a match is decided, scrollable">
                        <table class="t w-full text-sm">
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
                                <td class="py-2 text-ink-muted">Eligible for automatic matching</td>
                            </tr>
                            <tr>
                                <td class="py-2">Japanese name match</td>
                                <td class="py-2 text-ink-muted">Decisive when the romanised form differs</td>
                            </tr>
                            <tr>
                                <td class="py-2">Known alias</td>
                                <td class="py-2 text-ink-muted">Eligible for automatic matching</td>
                            </tr>
                            <tr>
                                <td class="py-2">Fuzzy spelling</td>
                                <td class="py-2 text-ink-muted">Suggested for manual review</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>

                    <p class="note mt-3 text-xs text-ink-muted">
                        A fuzzy match is <b>never</b> applied automatically. The importer may offer it;
                        only a Trainer may accept it.
                    </p>
                </section>

                <section class="card" aria-labelledby="review-why-heading">
                    <h2 id="review-why-heading">Why this exists</h2>

                    <p class="mt-2 text-sm text-ink-muted">
                        Careers and Veterans use the matched catalog record for the trainee's aptitudes,
                        growth rates and unique skill. Check the specific form before you confirm.
                    </p>

                </section>
            </div>

            <!-- The pager travels with the queue. It used to carry a bare `sticky bottom-0`, which is
                 a no-op here: `main` sets `container-type: inline-size` for the responsive grid
                 (AppLayout.vue:616) and containment ends `position: sticky` for every descendant. The
                 declaration promised a pin the browser never painted, so it is gone rather than left
                 as a false expectation. Filed as KI-150.
                 (AUD-20261102-021 F-04) -->
            <!-- Full-bleed across the shell band, without negative margins. The band sets
                 `px-4 md:px-[26px]`, so `-mx-4 md:-mx-8` pulled 32px out at `md` where only 26px of
                 padding existed — 6px of overflow per side, which is what the v3 audit measured as
                 `/review` scrolling to 1446px in a 1440px viewport (UX-07). The divider and the panel
                 background still run the band's full inner width; they stop at the padding the shell
                 actually guarantees. -->
            <div
                class="z-10 mt-6 border-t border-rule bg-panel py-3"
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
