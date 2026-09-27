<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReleaseStatus;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Database\Eloquent\Builder;
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
     * Paginated list, filterable by `status` (ReleaseStatus) and `search`.
     * Search runs against the normalized match key and aliases, not raw display
     * strings (matching rule, CLAUDE.md). Unknown status values are ignored
     * rather than erroring. Renders catalog.index.
     */
    public function index(Request $request, NameNormalizer $normalizer): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $page = max(1, (int) $request->query('page', '1'));
        $pageSize = min(100, max(1, (int) $request->query('pageSize', '25')));

        /** @var ReleaseStatus|null $statusEnum */
        $statusEnum = $status !== null ? ReleaseStatus::tryFrom((string) $status) : null;
        $searchKey = $search !== null && $search !== '' ? $normalizer->normalize((string) $search) : null;

        $query = Umamusume::query()
            ->withCount('aliases')
            ->when($statusEnum !== null, fn ($q) => $q->where('release_status', $statusEnum->value))
            ->when($searchKey !== null, function ($q) use ($searchKey): void {
                $q->where(function ($sub) use ($searchKey): void {
                    $sub->where('match_key', 'like', "%{$searchKey}%")
                        ->orWhereHas('aliases', fn ($a) => $a->whereRaw('lower(alias) like ?', ["%{$searchKey}%"]));
                });
            });

        [$items, $total] = $this->cached($query, $status, $searchKey, $page, $pageSize);

        $umamusumes = new LengthAwarePaginator($items, $total, $pageSize, $page, ['path' => route('catalog.index')]);

        return view('catalog.index', [
            'umamusumes' => $umamusumes,
            'statuses' => ReleaseStatus::cases(),
            'currentStatus' => $statusEnum,
            'search' => $search,
        ]);
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
    private function cached($query, ?string $status, ?string $searchKey, int $page, int $pageSize): array
    {
        $version = (int) Cache::remember('catalog:version', 3600, fn () => 0);
        $ttl = (int) config('uma.cache.ttl', 900);
        $base = "catalog:list:v{$version}:".md5("{$status}|{$searchKey}");

        /** @var list<int> $ids */
        $ids = Cache::remember(
            "{$base}:p{$page}:{$pageSize}",
            $ttl,
            fn (): array => $query->orderBy('name')->forPage($page, $pageSize)->pluck('id')->all(),
        );

        $total = Cache::remember("{$base}:count", $ttl, fn () => $query->count());

        // Re-read the page by id. `aliases_count` is asked for again rather than
        // cached: it is a live count, and the cache's job here is the identity of
        // the page, not the numbers drawn on it.
        $items = Umamusume::query()
            ->withCount('aliases')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        return [$items, $total];
    }
}
