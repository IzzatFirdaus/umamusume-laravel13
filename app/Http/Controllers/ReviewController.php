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
        $action->handle($candidate, $request->validated());

        return redirect()->route('review.index')->with('status', 'Candidate resolved.');
    }
}
