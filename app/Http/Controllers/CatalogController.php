<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Server-rendered catalog browsing (PRD US-1, FR-A). Read path only; writes
 * come from the engine or manual edits, never here.
 */
class CatalogController extends Controller
{
    /**
     * Paginated roster tree, filterable by `status` (ReleaseStatus), `search` and
     * `show_unconfirmed`. Search compares a normalized term against the normalized match
     * key, the aliases and the card titles — the last two folded to the term's shape at
     * comparison time, see `normalizedColumn()` (matching rule, CLAUDE.md). A Trainer
     * searching a card must land on the trainee who owns it, so the card clause selects
     * the *parent* row. Unknown status values are ignored rather than erroring, and
     * `status=all` is the way back to the unfiltered list. Renders catalog.index.
     *
     * The default status is Released (Global), not "everything": a catalog whose first
     * screen is half JP-only rows is not the Global English tool PRD §1 describes.
     */
    public function index(Request $request, NameNormalizer $normalizer): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $page = max(1, (int) $request->query('page', '1'));
        $pageSize = min(100, max(1, (int) $request->query('pageSize', '25')));

        /** @var ReleaseStatus|null $statusEnum */
        $statusEnum = $status !== null ? ReleaseStatus::tryFrom((string) $status) : null;
        $showAllStatus = $status === 'all';
        $showUnconfirmed = $request->boolean('show_unconfirmed');
        $searchKey = $search !== null && $search !== '' ? $normalizer->normalize((string) $search) : null;

        $query = Umamusume::query()
            ->with(['cards' => $this->cardScope($showUnconfirmed)])
            ->when(! $showAllStatus && $statusEnum === null, fn (Builder $q): Builder => $q->where('release_status', ReleaseStatus::GlobalReleased->value))
            ->when($statusEnum !== null, fn (Builder $q): Builder => $q->where('release_status', $statusEnum->value))
            ->when($searchKey !== null, function (Builder $q) use ($searchKey): void {
                $q->where(function (Builder $sub) use ($searchKey): void {
                    // Both sides of each comparison have to arrive at the same string:
                    // `match_key` is already normalized, so the title and the alias are
                    // folded at comparison time by `normalizedColumn()`. A card match
                    // still surfaces its trainee, because the trainee is the level this
                    // page is organised at.
                    $sub->where('match_key', 'like', "%{$searchKey}%")
                        ->orWhereHas('aliases', fn ($a) => $a->whereRaw($this->normalizedColumn('alias').' like ?', ["%{$searchKey}%"]))
                        ->orWhereHas('cards', fn ($c) => $c->whereRaw($this->normalizedColumn('title').' like ?', ["%{$searchKey}%"]));
                });
            });

        [$items, $total] = $this->cached($query, $status, $searchKey, $page, $pageSize, $showUnconfirmed);

        // `withQueryString()` because the paginator is built by hand here: without it the
        // pager links carry a bare `?page=N` and paging silently reverts status, search
        // and the unconfirmed opt-in.
        $umamusumes = (new LengthAwarePaginator($items, $total, $pageSize, $page, ['path' => route('catalog.index')]))
            ->withQueryString();

        return view('catalog.index', [
            'umamusumes' => $umamusumes,
            'statuses' => ReleaseStatus::cases(),
            'currentStatus' => $statusEnum,
            'search' => $search,
            'showAllStatus' => $showAllStatus,
            'showUnconfirmed' => $showUnconfirmed,
            'allStatusesLabel' => 'All statuses',
        ]);
    }

    /**
     * Raw SQL that folds a stored string into the shape `NameNormalizer::normalize()`
     * leaves a search term in, so a `like` between the two is a comparison and not a
     * coincidence. Needed because `character_cards.title` and `umamusume_aliases.alias`
     * carry verbatim source data with no normalized column behind them, and normalize()
     * deletes the spaces that make most card epithets multi-word: `red strife` can only
     * reach `[Red Strife]` if the column loses its space too.
     *
     * `$column` is interpolated rather than bound because SQL takes identifiers and
     * literals where a placeholder cannot go. It is a string literal from this file at
     * both call sites, never request input; the *term* is what varies, and it stays a
     * bound parameter in the caller.
     *
     * Known ceiling, stated rather than assumed away: this folds spaces, hyphens and
     * middle dots, which every engine here can do. It cannot fold **diacritics**
     * portably — SQLite's `lower()` is ASCII-only, so it neither maps `É` to `E` nor
     * drops combining marks the way normalize() does. `[Nuit Étoilée de Scarlet]`
     * therefore still will not answer `nuit etoilee`. The upgrade path for that is a
     * normalized key stored beside `title` and `alias`, written by the pipeline the way
     * `match_key` already is: durable, indexable, and honest on both sides. It is a
     * schema change, so it is not this method's to make.
     */
    private function normalizedColumn(string $column): string
    {
        $expression = "lower({$column})";

        foreach (NameNormalizer::FOLDED_CHARACTERS as $character) {
            $expression = "replace({$expression}, '{$character}', '')";
        }

        return $expression;
    }

    /**
     * Detail page for one Umamusume by slug (aliases + last 10 provenance
     * rows). Renders catalog.show.
     *
     * Not cached. This read is one indexed lookup plus two bounded relation
     * loads on a local SQLite file, and the only thing a cache could hold for it
     * is the model graph itself — which `serializable_classes => false` will not
     * hand back. Leaving it out is the fix; see `cached()` for the same reason
     * stated with the evidence (KI-2).
     *
     * @throws NotFoundHttpException when the slug is unknown
     */
    public function show(string $slug): View
    {
        $umamusume = Umamusume::where('slug', $slug)
            ->with(['aliases', 'dataSources' => fn ($q) => $q->latest('fetched_at')->limit(10)])
            ->first();

        abort_if($umamusume === null, 404);

        return view('catalog.show', ['umamusume' => $umamusume]);
    }

    /**
     * Cache the list page + its count under a version-embedded key. Promotion
     * bumps catalog:version, so a whole page group invalidates at once instead
     * of per-row churn (versioned-keys strategy, ARCHITECTURE §6).
     *
     * What is cached is the page's list of ids and its total, never the models.
     * `config/cache.php` ships `serializable_classes => false`, which is the
     * framework refusing to unserialize objects out of a persistent store: a
     * cached Eloquent collection comes back as `__PHP_Incomplete_Class` and the
     * view dies reading `->slug`. CACHE_STORE=array in phpunit.xml kept the suite
     * green because the array store hands back the very objects it was given, so
     * only the real app showed it (KI-2).
     *
     * @param  Builder<Umamusume>  $query
     * @return array{0: Collection<int, Umamusume>, 1: int}
     */
    private function cached($query, ?string $status, ?string $searchKey, int $page, int $pageSize, bool $showUnconfirmed): array
    {
        $version = (int) Cache::remember('catalog:version', 3600, fn () => 0);
        $ttl = (int) config('uma.cache.ttl', 900);
        // Defensive key separation, not a live dependency: the flag changes which cards a
        // row shows, not which parent rows exist or how many there are, because
        // `cardScope()` constrains only the eager load. It stays in the key so a future
        // scope that filters parents by card state cannot reuse a page cached without it.
        $base = "catalog:list:v{$version}:".md5("{$status}|{$searchKey}|".($showUnconfirmed ? '1' : '0'));

        /** @var list<int> $ids */
        $ids = Cache::remember(
            "{$base}:p{$page}:{$pageSize}",
            $ttl,
            fn (): array => $query->orderBy('name')->forPage($page, $pageSize)->pluck('id')->all(),
        );

        $total = Cache::remember("{$base}:count", $ttl, fn () => $query->count());

        // Re-read the page by id rather than caching the models. The card tree is loaded
        // through the same `cardScope()` the list query used, so a cache hit cannot hand
        // back trainees whose forms went missing (KI-2's failure shape).
        $items = Umamusume::query()
            ->with(['cards' => $this->cardScope($showUnconfirmed)])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        return [$items, $total];
    }

    /**
     * Which cards a row of the tree shows, and in what order.
     *
     * One method called by both the list query and the cache re-read rather than a
     * closure threaded between them: `Relation::__call` forwards `when()` and
     * `orderBy()` to the underlying Builder, so a closure declared to take and
     * return a `HasMany` would be a type the runtime does not honour. Mutating the
     * relation in place and returning nothing keeps the declared shape true, and
     * the two paths still cannot drift because there is only one of them.
     *
     * Debut first, then the Global release date, then the source's own card id as
     * the tiebreak two same-dated forms need to keep the page repeatable.
     *
     * @return Closure(HasMany<CharacterCard, Umamusume>): void
     */
    private function cardScope(bool $showUnconfirmed): Closure
    {
        return static function (HasMany $cards) use ($showUnconfirmed): void {
            if (! $showUnconfirmed) {
                $cards->where('unconfirmed', false);
            }

            $cards->orderBy('is_debut_form', 'desc')
                ->orderBy('global_release_date')
                ->orderBy('card_id');
        };
    }
}
