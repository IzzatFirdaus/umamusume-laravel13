<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveMatchCandidate;
use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Enums\CardRarity;
use App\Http\Requests\BulkRejectRequest;
use App\Http\Requests\ResolveMatchCandidateRequest;
use App\Models\CharacterCard;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $filters = [
            'entity' => $request->string('entity')->toString(),
            'match_tier' => $request->string('match_tier')->toString(),
        ];
        $candidates = self::pendingCandidates()
            ->when($filters['entity'] !== '', function (Builder $q) use ($filters): void {
                // ADR-0037: the source key IS the entity discriminator, and config('uma.sources')
                // is the one place a key's entity kind is declared. Filtering on the JSON payload
                // misses every historical row the runner wrote before the field existed, so the
                // filter selects the source keys that classify as the asked-for entity and matches
                // source_key against them.
                $keys = self::sourceKeysForEntity($filters['entity']);

                if ($keys === []) {
                    // A kind no source declares cannot have candidates: return none rather than
                    // every row (an empty IN list would be a WHERE that matches nothing anyway,
                    // but the explicit 0=1 keeps the intent readable).
                    $q->whereRaw('0 = 1');

                    return;
                }

                $q->whereIn('source_key', $keys);
            })
            ->when($filters['match_tier'] !== '', function (Builder $q) use ($filters): void {
                $q->where('match_tier', $filters['match_tier']);
            })
            ->with('suggestedUmamusume')
            ->paginate(self::PAGE_SIZE)
            ->withQueryString();

        $page = $candidates->currentPage();
        if ($page > $candidates->lastPage()) {
            return redirect()->route('review.index', self::pageParameters($candidates->lastPage(), $filters));
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
                // The entity this row belongs to, classified by its source key (ADR-0037): the
                // config's own per-key entity declaration, not a payload field historical rows
                // predate. A race candidate can only be rejected: the trainee promotion path would
                // write a race into the trainee table. The page uses this to withhold the verdict
                // rather than offer a button that refuses.
                'entity' => self::entityOf($candidate),
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
            'filters' => $filters,
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
                // The entity this row belongs to, classified by its source key (ADR-0037): the
                // config's own per-key entity declaration, not a payload field historical rows
                // predate. A race candidate can only be rejected: the trainee promotion path would
                // write a race into the trainee table. The page uses this to withhold the verdict
                // rather than offer a button that refuses.
                'entity' => self::entityOf($candidate),
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
     * Dispose of a batch of candidates with one Reject verdict.
     *
     * Reject-only by contract: the backlog this endpoint exists to clear is race candidates, which
     * `ResolveMatchCandidate` refuses to promote. Each row goes through that same action inside one
     * transaction, so a failure on any row rolls the whole batch back and no half-disposed queue can
     * be observed. Per-row provenance is the action's existing stamp — nothing here writes directly.
     */
    public function bulk(BulkRejectRequest $request, ResolveMatchCandidate $action): RedirectResponse
    {
        $ids = $request->candidateIds();

        DB::transaction(function () use ($ids, $action): void {
            foreach (MatchCandidate::query()->whereIn('id', $ids)->lockForUpdate()->get() as $candidate) {
                $action->handle($candidate, ['status' => CandidateStatus::Rejected->value]);
            }
        });

        return redirect()->route('review.index')
            ->with('status', sprintf('%d candidates rejected.', count($ids)));
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
     * The entity kind a candidate belongs to, from its source key (ADR-0037): the key is the
     * discriminator the accepted decision names, and `config('uma.sources')` is the one place a
     * key's kind is declared. The payload's own `entity` field is not read: historical rows were
     * written before the runner recorded it, and reading a field some rows lack would classify by
     * whichever rows are newest rather than by what the row actually is.
     *
     * A key the config does not declare (none today) classifies as `unknown`, which the frontend
     * treats as no-verdict rather than defaulting the row onto the trainee promotion path — the
     * old `?? 'umamusume'` fallback was the defect: it offered a race row to the one action that
     * would misfile it.
     */
    private static function entityOf(MatchCandidate $candidate): string
    {
        $entity = config('uma.sources.'.$candidate->source_key.'.entity');

        return is_string($entity) && $entity !== '' ? $entity : 'unknown';
    }

    /**
     * The source keys whose config entity declaration matches the asked-for kind — the set a
     * filtered queue may draw from.
     *
     * @return list<string>
     */
    private static function sourceKeysForEntity(string $entity): array
    {
        $keys = [];

        foreach ((array) config('uma.sources', []) as $key => $source) {
            if (is_array($source) && ($source['entity'] ?? null) === $entity) {
                $keys[] = (string) $key;
            }
        }

        return $keys;
    }

    /**
     * @param  array{entity?: string, match_tier?: string}  $filters
     * @return array{page?: int, entity?: string, match_tier?: string}
     */
    private static function pageParameters(int $page, array $filters = []): array
    {
        $parameters = $page === 1 ? [] : ['page' => $page];

        return array_filter([...$parameters, ...$filters], static fn ($value): bool => $value !== '');
    }
}
