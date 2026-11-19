<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveMatchCandidate;
use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Enums\CardRarity;
use App\Http\Requests\ResolveMatchCandidateRequest;
use App\Models\CharacterCard;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    private const PAGE_SIZE = 25;

    /**
     * Lists Pending candidates only; resolved rows leave this view.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $candidates = self::pendingCandidates()
            ->with('suggestedUmamusume')
            ->paginate(self::PAGE_SIZE);

        $page = $candidates->currentPage();
        if ($page > $candidates->lastPage()) {
            return redirect()->route('review.index', self::pageParameters($candidates->lastPage()));
        }

        $continuation = $request->session()->get('reviewContinuation');
        $next = is_array($continuation)
            ? ($candidates->getCollection()->firstWhere('id', $continuation['candidate_id']) ?? $candidates->getCollection()->first())
            : null;

        $candidates
            ->through(fn (MatchCandidate $candidate): array => [
                'id' => $candidate->id,
                'proposed_name' => $candidate->proposed_name,
                'proposed_name_ja' => $candidate->proposed_name_ja,
                // The entity this row belongs to, from the payload the runner wrote (S1.1). A race
                // candidate can only be rejected: the trainee promotion path would write a race into
                // the trainee table. The page uses this to withhold the verdict rather than offer a
                // button that refuses.
                'entity' => (string) ($candidate->payload['entity'] ?? 'umamusume'),
                'match_tier' => $candidate->match_tier->value,
                'match_tier_label' => $candidate->match_tier->label(),
                'source_key' => $candidate->source_key,
                'created_at' => $candidate->created_by_fetch_at->toDateString(),
                'suggestion' => $candidate->suggestedUmamusume?->name,
                'suggested_umamusume_id' => $candidate->suggested_umamusume_id,
                'review_url' => route('review.detail', ['candidate' => $candidate->id, ...self::pageParameters($page)], absolute: false),
            ]);

        return Inertia::render('Review/Index', [
            'candidates' => $candidates,
            'queueUrl' => route('review.index', absolute: false),
            'databaseUrl' => route('database.index', absolute: false),
            'nextReviewUrl' => $next === null
                ? null
                : route('review.detail', ['candidate' => $next->id, ...self::pageParameters($page)], absolute: false),
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
            // Total resolved (all-time), so the progress bar reflects actual work done, not pending rows
            // on other pages (AUD-20261102-021 F-01).
            'resolvedCount' => MatchCandidate::query()
                ->where('status', '!=', CandidateStatus::Pending->value)
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
    public function show(Request $request, MatchCandidate $candidate): Response
    {
        // `cards` rides the same eager load as the trainee itself: the Best match card prints the
        // rarity mark the HFW draws (`review-detail.html:90`, `pill pill-neutral "★★★ SSR"`), and the
        // rarity lives on the character card, not on `umamusume`. Loading it here costs one relation
        // on one already-read row rather than a lazy query per render, and the derivation below is
        // `CatalogController`'s own expression (`:135-137`, `:159`) so the two screens cannot disagree
        // about what a trainee's rarity is.
        $candidate->loadMissing(['suggestedUmamusume.cards', 'suggestedUmamusume.aliases']);

        $suggested = $candidate->suggestedUmamusume;
        $pageParameters = self::pageParameters(max(1, min($request->integer('page', 1), 2147483647)));

        $maxRarity = $suggested === null || $suggested->cards->isEmpty()
            ? null
            : CardRarity::from((int) $suggested->cards->max(static fn (CharacterCard $card): int => $card->rarity->value));

        return Inertia::render('Review/Detail', [
            'queueUrl' => route('review.index', $pageParameters, absolute: false),
            'resolveUrl' => route('review.resolve', ['candidate' => $candidate->id, ...$pageParameters], absolute: false),
            'reopenUrl' => route('review.reopen', $candidate, absolute: false),
            'candidate' => [
                'id' => $candidate->id,
                'proposed_name' => $candidate->proposed_name,
                'proposed_name_ja' => $candidate->proposed_name_ja,
                // The entity this row belongs to, from the payload the runner wrote (S1.1). A race
                // candidate can only be rejected: the trainee promotion path would write a race into
                // the trainee table. The page uses this to withhold the verdict rather than offer a
                // button that refuses.
                'entity' => (string) ($candidate->payload['entity'] ?? 'umamusume'),
                'match_tier' => $candidate->match_tier->value,
                'match_tier_label' => $candidate->match_tier->label(),
                'source_key' => $candidate->source_key,
                'external_ref' => $candidate->external_ref,
                'created_at' => $candidate->created_by_fetch_at->toDateString(),
                'suggestion' => $suggested?->name,
                'suggested_umamusume_id' => $candidate->suggested_umamusume_id,
                'status' => $candidate->status->value,
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
                'catalog_url' => route('catalog.show', ['slug' => $suggested->slug], absolute: false),
                'aliases' => $suggested->aliases
                    ->sortBy('alias', SORT_NATURAL | SORT_FLAG_CASE)
                    ->map(fn (UmamusumeAlias $alias): array => [
                        'id' => $alias->id,
                        'name' => $alias->alias,
                        'language' => $alias->language->value,
                    ])
                    ->values()
                    ->all(),
                'costume' => self::costumeOf($suggested->name),
                'aptitudes' => $suggested->aptitudeAxes(),
                // Null when no card row exists: the card prints the absence in words rather than a
                // zero-star mark, because a trainee with no catalogue card is a real state here.
                'max_rarity' => $maxRarity === null ? null : ['label' => $maxRarity->label(), 'stars' => $maxRarity->stars()],
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
     * Returns a resolved candidate to Pending (reversal for ReviewDetail-F-01, SHR-6/H3).
     */
    public function reopen(MatchCandidate $candidate): RedirectResponse
    {
        $candidate->status = CandidateStatus::Pending;
        $candidate->save();

        return redirect()->route('review.index')->with('status', 'Candidate returned to Pending.');
    }

    public function resolve(ResolveMatchCandidateRequest $request, MatchCandidate $candidate, ResolveMatchCandidate $action): RedirectResponse
    {
        $page = $request->integer('page', 1);
        $pageIds = self::pendingCandidates()
            ->offset(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE + 1)
            ->pluck('id');
        $position = $pageIds->search($candidate->id, strict: true);
        $nextId = $position === false ? null : $pageIds->get($position + 1);

        $action->handle($candidate, $request->verdict());

        if ($page > 1) {
            $lastPage = max(1, (int) ceil(self::pendingCandidates()->count() / self::PAGE_SIZE));
            $page = min($page, $lastPage);
        }

        return redirect()->route('review.index', self::pageParameters($page))
            ->with('status', 'Candidate resolved.')
            ->with('reviewContinuation', ['candidate_id' => $nextId]);
    }

    /**
     * Suggested cases first, then newest fetch first, with a stable tie-break for one fetch batch.
     *
     * @return Builder<MatchCandidate>
     */
    private static function pendingCandidates(): Builder
    {
        return MatchCandidate::query()
            ->where('status', CandidateStatus::Pending->value)
            ->orderByRaw('suggested_umamusume_id IS NULL')
            ->latest('created_by_fetch_at')
            ->orderByDesc('id');
    }

    /**
     * @return array{page?: int}
     */
    private static function pageParameters(int $page): array
    {
        return $page === 1 ? [] : ['page' => $page];
    }
}
