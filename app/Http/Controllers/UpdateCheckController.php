<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class UpdateCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $feedUrl = config('app.update_feed_url');

        if (empty($feedUrl)) {
            return response()->json(['available' => false, 'version' => null, 'url' => null]);
        }

        $localVersion = config('app.version');

        try {
            $response = Http::timeout(5)->get($feedUrl);
            if (! $response->successful()) {
                return response()->json(['available' => false, 'version' => null, 'url' => null]);
            }

            $remote = $response->json();
            $remoteVersion = $remote['version'] ?? null;

            if ($remoteVersion !== null && version_compare($remoteVersion, $localVersion, '>')) {
                return response()->json([
                    'available' => true,
                    'version' => $remoteVersion,
                    'url' => $remote['url'] ?? null,
                ]);
            }
        } catch (\Throwable) {
            // Feed unreachable; banner stays inert.
        }

        return response()->json(['available' => false, 'version' => null, 'url' => null]);
    }
}
