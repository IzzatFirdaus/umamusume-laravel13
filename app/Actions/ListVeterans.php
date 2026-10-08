<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Veteran;
use App\Services\PageSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The Veteran library query (PRD FR-G-2, ADR-0020 §3): read-only, and it searches only what the Trainer
 * recorded. There is no ranking, no score and no "best parent" — the library stores and searches, and it
 * never orders by a computed value (FR-G-4).
 *
 * The filters are the ones FR-G-2 names. `trainee` and `scenario` read the run a Veteran points at,
 * because that is where those facts are recorded; the distance, surface, style and stat-spark facets are
 * the Trainer's own tags (design-2.0 SCREEN-020 lists them as the suggested tag vocabulary), so they are
 * searched through `tags` rather than through columns this table does not have. The plan's §7 "rating"
 * filter is not built: no source records a rating, and inventing one is the computation the slice forbids.
 *
 * An absent filter is no filter, and an empty string or an empty tag list reads the same way, so a cleared
 * form field does not filter the library down to nothing. The page size is clamped by `PageSize`, the one
 * rule the filter surfaces share (ADR-0018).
 */
final class ListVeterans
{
    /**
     * The paginator is typed as the concrete `LengthAwarePaginator` rather than the
     * `Illuminate\Contracts\Pagination\LengthAwarePaginator` interface, because the screen that
     * renders this list calls `through()` to map each row to an explicit array. The interface does
     * not declare it, so the wider type would force that screen either to re-query or to map by hand
     * and lose the "never pass an Eloquent model to a page" rule the rest of the rewrite holds. The
     * concrete class is what `paginate()` returns anyway, so this narrows the promise to the truth
     * rather than widening it.
     *
     * @param  array{trainee?: int|null, scenario?: string|null, tags?: list<string>}  $filters
     * @param  'newest'|'oldest'  $order  the library's own recording order; see the note in the body
     * @return LengthAwarePaginator<int, Veteran>
     */
    public function handle(array $filters = [], mixed $pageSize = null, string $order = 'newest'): LengthAwarePaginator
    {
        $trainee = $filters['trainee'] ?? null;
        $scenario = $filters['scenario'] ?? null;
        $tags = $filters['tags'] ?? [];

        $query = Veteran::query()->with('trainingRun.umamusume');

        if ($trainee !== null) {
            $query->whereHas('trainingRun', fn (Builder $run): Builder => $run->where('umamusume_id', $trainee));
        }

        if (filled($scenario)) {
            $query->whereHas('trainingRun', fn (Builder $run): Builder => $run->where('scenario', $scenario));
        }

        // Every named tag must be present: a Veteran tagged Mile and Turf matches both, and the filters
        // narrow rather than widen as more are chosen. Matched case-insensitively against the folded
        // twin, because SQLite's `whereJsonContains` compares JSON strings exactly and the chips the
        // Trainer clicks carry a casing a hand-typed tag may not (KI-72).
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags_normalized', mb_strtolower($tag));
        }

        // The toggle orders by the row's own id, which is the order the library recorded them in. It is
        // not a completion date: no column holds the date a career actually finished, so labelling
        // `created_at` that would print a fact the store does not have. The default is today's behaviour,
        // so the two surfaces that call this without an order argument see the list they already show.
        return $order === 'oldest'
            ? $query->oldest('id')->paginate(PageSize::clamp($pageSize))
            : $query->latest('id')->paginate(PageSize::clamp($pageSize));
    }
}
