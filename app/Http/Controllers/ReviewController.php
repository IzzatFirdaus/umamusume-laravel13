<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveMatchCandidate;
use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Http\Requests\ResolveMatchCandidateRequest;
use App\Models\MatchCandidate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Human review of the Fuzzy/None match queue (PRD US-5). The engine proposes,
 * the Trainer disposes here; resolution is delegated to the
 * ResolveMatchCandidate action so the verdict and its catalog writes stay in
 * one transaction.
 */
class ReviewController extends Controller
{
    /**
     * Lists Pending candidates only; resolved rows leave this view.
     */
    public function index(): Response
    {
        $candidates = MatchCandidate::with('suggestedUmamusume')
            ->where('status', CandidateStatus::Pending->value)
            // Rows the engine could suggest a match for come first: a suggestion is the queue's
            // cheapest decision, so the ones a Trainer can act on in one click are not buried
            // behind the ones that need research. Newest-fetch order still breaks the ties inside
            // each half, so this narrows the queue without reordering either part of it.
            ->orderByRaw('suggested_umamusume_id IS NULL')
            ->latest('created_by_fetch_at')
            ->paginate(25)
            ->through(fn (MatchCandidate $candidate): array => [
                'id' => $candidate->id,
                'proposed_name' => $candidate->proposed_name,
                'proposed_name_ja' => $candidate->proposed_name_ja,
                'match_tier' => $candidate->match_tier->value,
                'match_tier_label' => $candidate->match_tier->label(),
                'source_key' => $candidate->source_key,
                'created_at' => $candidate->created_by_fetch_at->toDateString(),
                'suggestion' => $candidate->suggestedUmamusume?->name,
                'suggested_umamusume_id' => $candidate->suggested_umamusume_id,
            ]);

        return Inertia::render('Review/Index', [
            'candidates' => $candidates,
            // The queue being empty means two different things, and only the table knows which: no row
            // at all is a database nobody has fetched, while rows with a verdict on them is a queue
            // someone has worked through.
            'neverFetched' => MatchCandidate::query()->count() === 0,
            /*
             * How much of that work landed today (HFW review.html "Resolved today" tile). `status`
             * leaves `Pending` on exactly one path — the verdict action — so a row's `updated_at` IS
             * the moment it was decided, and counting the decided rows dated today needs no new column.
             *
             * `aliasLanguages` is NOT passed here: the verdict form moved to `Review/Detail` in FQ-10,
             * and a queue that no longer renders a select has no business shipping its options.
             */
            'resolvedToday' => MatchCandidate::query()
                ->where('status', '!=', CandidateStatus::Pending->value)
                ->whereDate('updated_at', today())
                ->count(),
        ]);
    }

    /**
     * One candidate on its own page (FQ-10, plan 4b.2).
     *
     * The queue used to render a verdict form inside every row; the form moves here so the queue is a
     * list a Trainer scans and this is the screen they decide on. The props are the row's own shape plus
     * the alias languages `CandidateForm` offers, so the form receives exactly what the inline form did.
     */
    public function show(MatchCandidate $candidate): Response
    {
        $candidate->loadMissing('suggestedUmamusume');

        return Inertia::render('Review/Detail', [
            'candidate' => [
                'id' => $candidate->id,
                'proposed_name' => $candidate->proposed_name,
                'proposed_name_ja' => $candidate->proposed_name_ja,
                'match_tier' => $candidate->match_tier->value,
                'match_tier_label' => $candidate->match_tier->label(),
                'source_key' => $candidate->source_key,
                'created_at' => $candidate->created_by_fetch_at->toDateString(),
                'suggestion' => $candidate->suggestedUmamusume?->name,
                'suggested_umamusume_id' => $candidate->suggested_umamusume_id,
            ],
            'aliasLanguages' => collect(AliasLanguage::cases())
                ->map(fn (AliasLanguage $language): array => [
                    'value' => $language->value,
                    'label' => $language->label(),
                ])
                ->all(),
        ]);
    }

    /**
     * Applies the Trainer's verdict (see the ResolveMatchCandidate action for
     * what each status does) and returns to the queue.
     */
    public function resolve(ResolveMatchCandidateRequest $request, MatchCandidate $candidate, ResolveMatchCandidate $action): RedirectResponse
    {
        $action->handle($candidate, $request->verdict());

        return redirect()->route('review.index')->with('status', 'Candidate resolved.');
    }
}
