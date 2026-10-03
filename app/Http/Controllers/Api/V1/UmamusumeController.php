<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReleaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UmamusumeResource;
use App\Models\Umamusume;
use App\Services\DataPipeline\NameNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only JSON catalog endpoints (PRD US-9, FR-E). List responses use the
 * documented { data, pagination } envelope; the { error: { code, message } }
 * shape is applied centrally in bootstrap/app.php for every non-2xx here.
 */
class UmamusumeController extends Controller
{
    public function index(Request $request, NameNormalizer $normalizer): JsonResponse
    {
        $pageSize = min(100, max(1, (int) $request->query('pageSize', '25')));

        $statusValue = ReleaseStatus::tryFrom((string) $request->query('status'))?->value;
        $search = $request->query('search');
        $searchKey = is_string($search) && $search !== '' ? $normalizer->normalize($search) : null;

        $umamusumes = Umamusume::with('aliases')
            ->when($statusValue !== null, fn ($q) => $q->where('release_status', $statusValue))
            ->when($searchKey !== null, fn ($q) => $q->where('match_key', 'like', "%{$searchKey}%"))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($pageSize);

        return response()->json([
            'data' => UmamusumeResource::collection($umamusumes->items())->resolve(),
            'pagination' => [
                'page' => $umamusumes->currentPage(),
                'pageSize' => $umamusumes->perPage(),
                'totalItems' => $umamusumes->total(),
                'totalPages' => $umamusumes->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $umamusume = Umamusume::where('slug', $slug)
            ->with(['aliases', 'dataSources' => fn ($q) => $q->latest('fetched_at')])
            ->first();

        if ($umamusume === null) {
            return response()->json([
                'error' => ['code' => 'NOT_FOUND', 'message' => "No umamusume with slug '{$slug}'."],
            ], 404);
        }

        return response()->json(['data' => new UmamusumeResource($umamusume)]);
    }
}
