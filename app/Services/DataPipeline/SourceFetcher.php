<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * The only outbound HTTP path in the app (SSRF posture, ARCHITECTURE §8):
 * targets come from config('uma.sources'), every body is snapshotted raw
 * before anyone parses it, and polite-delay/retry settings are per source.
 *
 * A source may declare `manifest` instead of trusting its pinned `url`. That exists because
 * `KI-24` measured the opposite of what `config/uma.php` claimed: a stale cache-busting hash does not
 * fail, it answers `200` and serves the superseded document forever. Resolution is one small request to
 * the publisher's manifest, and the pinned `url` stays as the documented fallback so a manifest outage
 * degrades to a stale-but-working fetch instead of a source with no address (NFR-2).
 */
final class SourceFetcher
{
    /**
     * The shape a manifest hash is allowed to have, and the whole reason the check exists: the hash is
     * the one value in the resolved URL that did not come from this file, so it must be incapable of
     * carrying a path, a host or a scheme. Eight lowercase hex characters, or the fetch uses the pin.
     */
    private const HASH_PATTERN = '/^[a-f0-9]{8}$/';

    /**
     * Fetch one declared source and store the raw snapshot before any parsing.
     * An unchanged content hash short-circuits the pipeline (idempotent re-runs).
     *
     * @param  array{url?: string, manifest?: array{url: string, base: string, key: string}, delay_ms?: int, timeout_s?: int}  $sourceConfig
     * @return array{body: string, snapshot_path: string, hash: string, unchanged: bool, url: string, manifest_fallback: bool}|null null when the request ultimately failed
     */
    public function fetch(string $sourceKey, array $sourceConfig): ?array
    {
        $resolved = $this->resolveUrl($sourceConfig);

        if ($resolved === null) {
            return null;
        }

        [$url, $usedFallback] = $resolved;

        $response = $this->send($url, $sourceConfig);

        if ($response === null) {
            return null;
        }

        $body = $response;
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
            // Reported so the caller can write *this* into provenance rather than the pin: which
            // document a fact came from is the thing a later reader needs to check.
            'url' => $url,
            'manifest_fallback' => $usedFallback,
        ];
    }

    /**
     * Where the document actually lives.
     *
     * @param  array{url?: string, manifest?: array{url: string, base: string, key: string}, delay_ms?: int, timeout_s?: int}  $sourceConfig
     * @return array{0: string, 1: bool}|null null only when the source has no address at all
     */
    private function resolveUrl(array $sourceConfig): ?array
    {
        // Declared `url?: string` above, so an isset check alone answers both "is it there" and
        // "is it a string" — a second is_string here would be a check that cannot fail.
        $pinned = isset($sourceConfig['url']) ? $sourceConfig['url'] : null;

        $manifest = $sourceConfig['manifest'] ?? null;

        if (! is_array($manifest)) {
            return $pinned === null ? null : [$pinned, false];
        }

        $resolved = $this->fromManifest($manifest, $sourceConfig);

        if ($resolved !== null) {
            return [$resolved, false];
        }

        return $pinned === null ? null : [$pinned, true];
    }

    /**
     * @param  array{url: mixed, base: mixed, key: mixed}  $manifest
     * @param  array{delay_ms?: int, timeout_s?: int}  $sourceConfig
     * @return string|null the resolved document URL, or null on any part the manifest did not supply
     */
    private function fromManifest(array $manifest, array $sourceConfig = []): ?string
    {
        $key = $manifest['key'] ?? null;
        $base = $manifest['base'] ?? null;
        $url = $manifest['url'] ?? null;

        if (! is_string($key) || $key === '' || ! is_string($base) || ! is_string($url)) {
            return null;
        }

        // The source's own timeout and delay apply to the manifest read too, so one
        // `timeout_s` in `config/uma.php` means one thing across both requests.
        $body = $this->send($url, $sourceConfig);

        if ($body === null) {
            return null;
        }

        $decoded = json_decode($body, true, 512);
        $hash = is_array($decoded) ? ($decoded[$key] ?? null) : null;

        if (! is_string($hash) || preg_match(self::HASH_PATTERN, $hash) !== 1) {
            return null;
        }

        return $base.$key.'.'.$hash.'.json';
    }

    /**
     * One polite, allowlisted GET. Returns the body, or null on any failure — including a non-2xx,
     * which the previous shape of this method handled inline and both callers now share.
     *
     * @param  array{delay_ms?: int, timeout_s?: int}  $sourceConfig
     */
    private function send(string $url, array $sourceConfig = []): ?string
    {
        // Waited per request, not per fetch: a manifest-resolved source makes two, and the delay is
        // what the politeness note in `config/uma.php` promised the publisher.
        usleep((int) ($sourceConfig['delay_ms'] ?? 1000) * 1000);

        try {
            $response = $this->request($sourceConfig)->get($url);
        } catch (RequestException) {
            return null;
        }

        return $response->failed() ? null : $response->body();
    }

    /**
     * @param  array{timeout_s?: int}  $sourceConfig
     */
    private function request(array $sourceConfig): PendingRequest
    {
        return Http::withHeaders([
            'User-Agent' => (string) config('uma.fetch.user_agent'),
        ])
            ->timeout((int) ($sourceConfig['timeout_s'] ?? 15))
            ->maxRedirects(2)
            ->retry((int) config('uma.fetch.retry_times', 2), 500, throw: false);
    }
}
