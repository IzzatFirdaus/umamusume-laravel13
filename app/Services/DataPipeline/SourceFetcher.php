<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * The only outbound HTTP path in the app (SSRF posture, ARCHITECTURE §8):
 * targets come from config('uma.sources'), every body is snapshotted raw
 * before anyone parses it, and polite-delay/retry settings are per source.
 */
final class SourceFetcher
{
    /**
     * Fetch one declared source and store the raw snapshot before any parsing.
     * An unchanged content hash short-circuits the pipeline (idempotent re-runs).
     *
     * @param  array{url: string, delay_ms?: int, timeout_s?: int}  $sourceConfig
     * @return array{body: string, snapshot_path: string, hash: string, unchanged: bool}|null null when the request ultimately failed
     */
    public function fetch(string $sourceKey, array $sourceConfig): ?array
    {
        $delayMs = (int) ($sourceConfig['delay_ms'] ?? 1000);

        usleep($delayMs * 1000);

        try {
            $response = Http::withHeaders([
                'User-Agent' => (string) config('uma.fetch.user_agent'),
            ])
                ->timeout((int) ($sourceConfig['timeout_s'] ?? 15))
                ->maxRedirects(2)
                ->retry((int) config('uma.fetch.retry_times', 2), 500, throw: false)
                ->get((string) $sourceConfig['url']);
        } catch (RequestException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $body = $response->body();
        $hash = hash('sha256', $body);
        $snapshotPath = "snapshots/{$sourceKey}/".now()->toDateString()."/{$hash}.html";

        $unchanged = Storage::disk('local')->exists($snapshotPath);

        if (! $unchanged) {
            Storage::disk('local')->put($snapshotPath, $body);
        }

        return [
            'body' => $body,
            'snapshot_path' => $snapshotPath,
            'hash' => $hash,
            'unchanged' => $unchanged,
        ];
    }
}
