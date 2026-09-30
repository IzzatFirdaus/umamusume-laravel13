<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingRunResource;
use App\Models\TrainingRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only JSON run endpoints (PRD US-9, FR-E); the write side stays in the
 * web UI. Detail includes turns and per-run skill statuses.
 */
class TrainingRunController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pageSize = min(100, max(1, (int) $request->query('pageSize', '25')));

        $runs = TrainingRun::with('umamusume')->latest('id')->paginate($pageSize);

        return response()->json([
            'data' => TrainingRunResource::collection($runs->items())->resolve(),
            'pagination' => [
                'page' => $runs->currentPage(),
                'pageSize' => $runs->perPage(),
                'totalItems' => $runs->total(),
                'totalPages' => $runs->lastPage(),
            ],
        ]);
    }

    public function show(TrainingRun $run): JsonResponse
    {
        $run->load(['umamusume', 'turnEntries', 'skills', 'deckSlots.supportCard']);

        return response()->json(['data' => new TrainingRunResource($run)]);
    }
}
