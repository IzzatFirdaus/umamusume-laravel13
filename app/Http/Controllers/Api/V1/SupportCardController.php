<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupportCardResource;
use App\Models\SupportCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only JSON endpoints for the support-card catalogue (ADR-0014).
 *
 * The deck a Trainer equips is written through the web UI and surfaces on the run detail response via
 * `TrainingRunResource`; nothing here accepts a write, which is what keeps the api envelope test's
 * premise that the 422 branch is unreachable true.
 */
class SupportCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pageSize = min(100, max(1, (int) $request->query('pageSize', '25')));

        $cards = SupportCard::query()->orderBy('support_id')->paginate($pageSize);

        return response()->json([
            'data' => SupportCardResource::collection($cards->items())->resolve(),
            'pagination' => [
                'page' => $cards->currentPage(),
                'pageSize' => $cards->perPage(),
                'totalItems' => $cards->total(),
                'totalPages' => $cards->lastPage(),
            ],
        ]);
    }

    public function show(SupportCard $supportCard): JsonResponse
    {
        return response()->json(['data' => new SupportCardResource($supportCard)]);
    }
}
