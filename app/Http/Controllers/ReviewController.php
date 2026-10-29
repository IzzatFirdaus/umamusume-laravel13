<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveMatchCandidate;
use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Http\Requests\ResolveMatchCandidateRequest;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
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

        $suggested = $candidate->suggestedUmamusume;

        return Inertia::render('Review/Detail', [
            'candidate' => [
                'id' => $candidate->id,
                'proposed_name' => $candidate->proposed_name,
                'proposed_name_ja' => $candidate->proposed_name_ja,
                'match_tier' => $candidate->match_tier->value,
                'match_tier_label' => $candidate->match_tier->label(),
                'source_key' => $candidate->source_key,
                'created_at' => $candidate->created_by_fetch_at->toDateString(),
                'suggestion' => $suggested?->name,
                'suggested_umamusume_id' => $candidate->suggested_umamusume_id,
            ],
            /*
             * The record the engine proposed, in the shape the "Best match" card prints. The stored
             * suggestion is used rather than a fresh `CrossReferenceMatcher` call: it is what the
             * fetch wrote down, so the screen shows the decision that is actually pending instead of
             * a second opinion that could disagree with the row under review.
             */
            'bestMatch' => $suggested === null ? null : [
                'id' => $suggested->id,
                'slug' => $suggested->slug,
                'name' => $suggested->name,
                'name_ja' => $suggested->name_ja,
                'costume' => self::costumeOf($suggested->name),
                'aptitudes' => $suggested->aptitudeAxes(),
            ],
            /*
             * The other forms of the same trainee. A costume is not a column: it is a bracketed prefix
             * inside `name` (`[Rosy Dreams] Rice Shower`), so two forms share a name and differ in
             * growth. They are listed apart rather than collapsed, because collapsing them is what
             * would make a costume tag meaningless on the row above.
             */
            'otherForms' => $suggested === null
                ? []
                : self::otherFormsOf($suggested),
            'hasAlias' => $candidate->proposed_name !== ''
                && UmamusumeAlias::query()
                    ->whereRaw('lower(alias) = lower(?)', [$candidate->proposed_name])
                    ->exists(),
            'aliasLanguages' => collect(AliasLanguage::cases())
                ->map(fn (AliasLanguage $language): array => [
                    'value' => $language->value,
                    'label' => $language->label(),
                ])
                ->all(),
        ]);
    }

    /**
     * The bracketed costume prefix of a catalog name, or null when the row carries none.
     */
    private static function costumeOf(string $name): ?string
    {
        return preg_match('/^\[([^\]]+)\]\s*/', $name, $matches) === 1 ? $matches[1] : null;
    }

    /**
     * The other catalog forms of the same trainee: same name with the costume prefix removed.
     *
     * @return list<array{id: int, name: string, costume: string|null, slug: string}>
     */
    private static function otherFormsOf(Umamusume $suggested): array
    {
        $base = preg_replace('/^\[[^\]]+\]\s*/', '', $suggested->name) ?? $suggested->name;

        return Umamusume::query()
            ->whereKeyNot($suggested->id)
            ->where(function ($query) use ($base): void {
                $query->where('name', 'like', '%'.$base.'%')
                    ->orWhere('name', $base);
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (Umamusume $form): array => [
                'id' => $form->id,
                'name' => $form->name,
                'costume' => self::costumeOf($form->name),
                'slug' => $form->slug,
            ])
            ->all();
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
