<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

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
     * The same ceiling `maxRedirects(2)` set before redirects moved into `send()`: two hops, then the
     * fetch gives up rather than following a cycle.
     */
    private const MAX_REDIRECT_HOPS = 2;

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

        $body = $response['body'];
        $hash = hash('sha256', $body);
        // The name is the content's own identity, so the path holds nothing else. A date segment here
        // made the existence test below answer a different question than it looked like it was asking:
        // the same document fetched tomorrow missed it, was stored a second time, and was re-parsed.
        $snapshotPath = "snapshots/{$sourceKey}/{$hash}.html";

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
            // document a fact came from is the thing a later reader needs to check. After F-9 that is the
            // address that finally answered, redirect hops included, not the one we asked for.
            'url' => $response['url'],
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
        $document = $this->send($url, $sourceConfig);

        if ($document === null) {
            return null;
        }

        $decoded = json_decode($document['body'], true, 512);
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
     * Redirects are followed here rather than by the HTTP client, and every hop is re-checked against the
     * config allowlist before it is requested (F-9). Letting the client follow them silently would mean a
     * document on an allowlisted origin that answers `302` to a host of someone else's choosing gets
     * fetched, snapshotted and parsed under that source's key, with only the User-Agent as evidence anyone
     * left the allowlist.
     *
     * @param  array{delay_ms?: int, timeout_s?: int}  $sourceConfig
     * @return array{body: string, url: string}|null the body and the address that answered for it
     */
    private function send(string $url, array $sourceConfig = []): ?array
    {
        for ($hop = 0; ; $hop++) {
            // Waited per request, not per fetch: a manifest-resolved source makes two, and the delay is
            // what the politeness note in `config/uma.php` promised the publisher.
            usleep((int) ($sourceConfig['delay_ms'] ?? 1000) * 1000);

            try {
                $response = $this->request($sourceConfig)->get($url);
            } catch (HttpClientException $e) {
                // Widened from `RequestException` on purpose: on this framework version a connection failure is
                // a `ConnectionException`, which is a sibling, not a subtype, so the old catch let a DNS or
                // timeout fault escape `fetch()` and crash the whole `uma:fetch` run instead of failing one
                // source. `HttpClientException` is the parent of both, and null is the contract every caller
                // already handles. The log line is the half that was missing (N-3): `->retry(..., throw: false)`
                // means a spent timeout used to arrive here and leave without a word.
                Log::warning("Fetch of '{$url}' failed: ".$e->getMessage());

                return null;
            }

            if ($response->status() < 300 || $response->status() >= 400) {
                break;
            }

            $next = $this->allowlistedTarget($url, (string) $response->header('Location'));

            if ($next === null) {
                return null;
            }

            if ($hop >= self::MAX_REDIRECT_HOPS) {
                Log::warning("Fetch of '{$url}' redirected more than ".self::MAX_REDIRECT_HOPS.' times.');

                return null;
            }

            $url = $next;
        }

        if ($response->failed()) {
            Log::warning("Fetch of '{$url}' answered {$response->status()}.");

            return null;
        }

        return ['body' => $response->body(), 'url' => $url];
    }

    /**
     * Where a `Location` header points, or null when it points anywhere this tool has not declared.
     *
     * The header is the source's own text, so it is input, not instruction: a relative reference is
     * resolved against the URL just requested, a non-https scheme is refused, and the host must be one of
     * the hosts `config('uma.sources')` already names. Nothing here is fetched until that check passes.
     */
    private function allowlistedTarget(string $from, string $location): ?string
    {
        if (trim($location) === '') {
            Log::warning("Fetch of '{$from}' answered a redirect with no Location.");

            return null;
        }

        try {
            $target = (string) UriResolver::resolve(new Uri($from), new Uri($location));
        } catch (InvalidArgumentException) {
            Log::warning("Fetch of '{$from}' pointed at an unparseable Location: '{$location}'.");

            return null;
        }

        $parts = parse_url($target);
        $host = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';

        if (($parts['scheme'] ?? null) !== 'https' || $host === '' || ! in_array($host, self::allowedHosts(), true)) {
            Log::warning("Refused redirect from '{$from}' to off-allowlist target '{$target}'.");

            return null;
        }

        return $target;
    }

    /**
     * Every host the declared sources can name: the pin, and the manifest URL and base a manifest-resolved
     * source is built from. Derived from config rather than a second list, so adding a source cannot
     * silently fail to widen it.
     *
     * @return list<string>
     */
    private static function allowedHosts(): array
    {
        $hosts = [];

        foreach ((array) config('uma.sources') as $source) {
            $candidates = [
                $source['url'] ?? null,
                $source['manifest']['url'] ?? null,
                $source['manifest']['base'] ?? null,
            ];

            foreach ($candidates as $candidate) {
                if (! is_string($candidate)) {
                    continue;
                }

                $host = strtolower((string) parse_url($candidate, PHP_URL_HOST));

                if ($host !== '') {
                    $hosts[$host] = true;
                }
            }
        }

        return array_keys($hosts);
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
            // Redirects are `send()`'s business now (F-9). `maxRedirects(0)` is not the way to say it: the
            // client would raise TooManyRedirects on the first 3xx instead of handing it back.
            ->withOptions(['allow_redirects' => false])
            ->retry((int) config('uma.fetch.retry_times', 2), 500, throw: false);
    }
}
