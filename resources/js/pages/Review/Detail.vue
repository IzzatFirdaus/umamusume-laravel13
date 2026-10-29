<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import CandidateForm from '../../components/review/CandidateForm.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import StatusPill from '../../components/StatusPill.vue';
import SuggestionHint from '../../components/review/SuggestionHint.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    bestMatch: BestMatch | null;
    otherForms: OtherForm[];
    hasAlias: boolean;
    aliasLanguages: { value: string; label: string }[];
}>();

/*
 * The form owns the `useForm`, so the other-candidate row's "Use this" action calls into it rather
 * than carrying a second copy of the submit contract (`CandidateForm`'s exposed `chooseRecord`).
 * Typed narrowly to the one method the page uses.
 */
const candidateForm = ref<{ chooseRecord: (id: number) => void } | null>(null);


interface BestMatch {
    id: number;
    slug: string;
    name: string;
    name_ja: string | null;
    costume: string | null;
    aptitudes: { key: string; label: string; letter: string | null }[];
    /**
     * The rarity mark the HFW draws on this card (`review-detail.html:90`). Derived server-side from
     * the trainee's own character cards (`ReviewController@show()`, the same expression
     * `CatalogController` uses). Null when the trainee has no card row — the card then states the
     * absence in words rather than printing a zero-star mark.
     */
    max_rarity: { label: string; stars: number } | null;
}

interface OtherForm {
    id: number;
    name: string;
    costume: string | null;
    slug: string;
}

/** The question the page asks, so the head and the document title read the same. */
const heading = computed<string>(() => `Is this the right trainee?`);

/**
 * What the page costs if it is answered wrong, stated once, next to the decision.
 *
 * A matched name propagates: the catalog row it resolves to is what the career, the veteran record
 * and every screen downstream name the trainee by, so a wrong answer is not a wrong word on this
 * page - it is a wrong trainee on four.
 */
const consequence =
    'The row you pick becomes the trainee every downstream screen names: the career, its veteran record and the Legacy Lab all read it from here. A wrong answer is not a wrong word on this page.';

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
        <Head :title="heading" />
        <template #eyebrow>Review</template>
        <template #title>{{ heading }}</template>

        <div class="w-full">
            <!-- Source and candidate side by side in one row: the question this screen asks is a
                 comparison, so the two halves of it are read together rather than stacked. -->
            <div class="grid gap-5 min-[1081px]:grid-cols-2">
                <section class="card" aria-labelledby="review-source-heading">
                    <h2 id="review-source-heading" class="text-lg font-semibold text-ink-strong">Source</h2>
                    <p class="mt-1 text-xs text-ink-muted">What the text said, verbatim.</p>

                    <div
                        class="mt-3 rounded-md border border-rule-soft bg-raised px-3 py-2.5 text-sm"
                        data-testid="review-source-text"
                    >
                        <!-- The received name stays a heading: it is the subject of the page, and the
                             browser spec addresses it by role and name. -->
                        <h3 class="text-base font-semibold text-ink-strong">
                            {{ props.candidate.proposed_name }}
                        </h3>
                        <span v-if="props.candidate.proposed_name_ja" lang="ja" class="text-ink-muted">
                            {{ props.candidate.proposed_name_ja }}
                        </span>
                        <p class="mt-1 font-mono text-xs text-ink-muted">
                            {{ props.candidate.source_key }} · {{ props.candidate.created_at }}
                        </p>
                    </div>

                    <p class="note mt-3 text-xs text-ink-muted">
                        A bracketed costume tag is part of the match: it narrows the record to one form.
                        Stripping it would leave a trainee with several costumes indistinguishable.
                    </p>
                </section>

                <!-- The wireframe gives the proposed record the page's one pick border
                     (`review-detail.html`); the border is the decision's target, not a selected state. -->
                <section class="card" :class="props.bestMatch ? 'is-picked' : ''" aria-labelledby="review-best-heading">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 id="review-best-heading" class="text-lg font-semibold text-ink-strong">
                            {{ props.bestMatch ? 'Best match' : 'No match' }}
                        </h2>
                        <StatusPill
                            v-if="props.candidate.match_tier === 'Fuzzy'"
                            :label="pillFor(props.candidate.match_tier).label"
                            :variant="pillFor(props.candidate.match_tier).variant"
                            size="sm"
                        />
                        <ProvenanceBadge
                            v-else
                            :state="badgeFor(props.candidate.match_tier, props.candidate.source_key).state"
                            :title="badgeFor(props.candidate.match_tier, props.candidate.source_key).title"
                        />
                    </div>

                    <template v-if="props.bestMatch">
                        <p class="mt-2 text-base font-semibold text-ink-strong">
                            {{ props.bestMatch.name }}
                            <span v-if="props.bestMatch.name_ja" lang="ja" class="text-sm font-medium text-ink-muted">
                                {{ props.bestMatch.name_ja }}
                            </span>
                        </p>

                        <!-- The rarity mark the HFW draws beside the name (`review-detail.html:90`).
                             Present when the trainee has a character card; when none exists the mark
                             states the absence in words instead of printing a zero-star reading. -->
                        <p class="mt-2 flex flex-wrap gap-1.5">
                            <span v-if="props.bestMatch.max_rarity" class="pill pill-neutral">
                                {{ props.bestMatch.max_rarity.label }}
                            </span>
                            <span v-else class="pill pill-neutral" title="No character card is stored for this record">
                                Rarity not recorded
                            </span>
                        </p>

                        <ul v-if="props.bestMatch.aptitudes.length > 0" class="mt-2 flex list-none flex-wrap gap-1.5">
                            <li
                                v-for="axis in props.bestMatch.aptitudes"
                                :key="axis.key"
                                class="pill pill-outline"
                            >
                                {{ axis.label }} {{ axis.letter ?? 'N/A' }}
                            </li>
                        </ul>

                        <table class="t mt-3 w-full text-sm">
                            <caption class="sr-only">The proposed catalog record</caption>
                            <tbody class="divide-y divide-rule">
                                <tr>
                                    <th scope="row" class="py-1.5 text-left font-normal text-ink-muted">Costume</th>
                                    <td class="py-1.5">{{ props.bestMatch.costume ?? 'None' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="py-1.5 text-left font-normal text-ink-muted">Slug</th>
                                    <td class="py-1.5 font-mono text-xs">{{ props.bestMatch.slug }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="py-1.5 text-left font-normal text-ink-muted">Matched on</th>
                                    <td class="py-1.5">{{ props.candidate.match_tier_label }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>

                    <p v-else class="mt-2 text-sm text-ink-muted">
                        No catalog row is close enough to propose. Nothing is written until you answer
                        this page, so the record is unchanged.
                    </p>
                </section>
            </div>

            <!-- The other forms of the same trainee. A costume is a bracketed prefix inside the name,
                 not a column, so two forms share a name and differ in growth. Listing them apart
                 rather than collapsing them is what makes the costume tag above mean anything. -->
            <section class="card mt-5" aria-labelledby="review-others-heading">
                <h2 id="review-others-heading" class="text-lg font-semibold text-ink-strong">
                    Other candidates
                </h2>

                <table class="t mt-3 w-full text-sm">
                    <caption class="sr-only">The records this name could refer to, and why each is listed</caption>
                    <thead>
                        <tr class="text-left text-xs text-ink-muted">
                            <th scope="col" class="pb-1 font-semibold">Record</th>
                            <th scope="col" class="pb-1 font-semibold">Kind</th>
                            <th scope="col" class="pb-1 font-semibold">Why it is listed</th>
                            <th scope="col" class="pb-1 text-right font-semibold"><span class="sr-only">State</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule">
                        <tr>
                            <td class="py-2">
                                {{ props.candidate.proposed_name }}
                                <span v-if="props.bestMatch?.costume" class="text-ink-muted">
                                    · [{{ props.bestMatch.costume }}]
                                </span>
                            </td>
                            <td class="py-2">{{ props.candidate.match_tier_label }}</td>
                            <td class="py-2 text-ink-muted">
                                {{ badgeFor(props.candidate.match_tier, props.candidate.source_key).title }}
                            </td>
                            <td class="py-2 text-right">
                                <span class="pill pill-positive">Proposed</span>
                            </td>
                        </tr>
                        <tr v-for="form in props.otherForms" :key="form.id">
                            <td class="py-2">{{ form.name }}</td>
                            <td class="py-2">Same trainee</td>
                            <td class="py-2 text-ink-muted">
                                A different form{{ form.costume ? ` ([${form.costume}])` : '' }}; growth row
                                and rarity differ.
                            </td>
                            <td class="py-2 text-right">
                                <!-- "Use this" is a real action, not a state label: the HFW makes
                                     the other form an answer (`review-detail.html:109`). It adopts
                                     that record and commits in one press, through the form that owns
                                     the submit contract (`CandidateForm`'s exposed `chooseRecord`). -->
                                <button
                                    type="button"
                                    :aria-label="`Use ${form.name} as the match`"
                                    class="inline-flex min-h-11 items-center justify-center rounded-full border border-rule bg-raised px-3 text-sm font-semibold text-ink-strong transition-colors duration-150 ease-out hover:bg-panel"
                                    @click="candidateForm?.chooseRecord(form.id)"
                                >
                                    Use this
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">-</td>
                            <td class="py-2">Alias</td>
                            <td class="py-2 text-ink-muted">
                                {{ props.hasAlias
                                    ? 'A registered alias matches this name exactly.'
                                    : 'No alias is registered for this name.' }}
                            </td>
                            <td class="py-2 text-right">
                                <span class="pill pill-neutral">{{ props.hasAlias ? 'Registered' : 'none' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="note mt-3 text-xs text-ink-muted">{{ consequence }}</p>
            </section>

            <!-- The verdict form is the screen's decision surface and appears once. The route exists
                 so the queue does not render a verdict form per row, and the browser spec pins the
                 count at one. -->
            <section
                aria-labelledby="review-detail-heading"
                data-testid="review-detail"
                class="card mt-5"
            >
                <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                    <h2 id="review-detail-heading" class="text-lg font-semibold text-ink-strong">
                        Your verdict
                    </h2>
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
                    ref="candidateForm"
                    :candidate="props.candidate"
                    :alias-languages="props.aliasLanguages"
                    :accented="true"
                    :default-expanded="true"
                    variant="savebar"
                />
            </section>

            <div class="mt-4">
                <Link
                    href="/review"
                    class="text-sm text-ink-muted hover:text-ink-strong transition-colors duration-150 ease-out"
                >
                    ← Back to the queue
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
