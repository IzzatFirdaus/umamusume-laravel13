<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveMatchCandidate;
use App\Enums\CandidateStatus;
use App\Http\Requests\ResolveMatchCandidateRequest;
use App\Models\MatchCandidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
    public function index(): View
    {
        return view('review.index', [
            'candidates' => MatchCandidate::with('suggestedUmamusume')
                ->where('status', CandidateStatus::Pending->value)
                ->latest('created_by_fetch_at')
                ->paginate(25),
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
