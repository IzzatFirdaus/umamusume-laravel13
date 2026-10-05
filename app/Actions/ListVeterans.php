<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Veteran;
use App\Services\PageSize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

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
     * @param  array{trainee?: int|null, scenario?: string|null, tags?: list<string>}  $filters
     * @return LengthAwarePaginator<int, Veteran>
     */
    public function handle(array $filters = [], mixed $pageSize = null): LengthAwarePaginator
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
        // narrow rather than widen as more are chosen.
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }

        return $query->latest('id')->paginate(PageSize::clamp($pageSize));
    }
}
