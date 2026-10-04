<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use App\Models\CharacterCard;
use App\Models\SupportCard;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * The local artwork mirror `ADR-0021` authorizes: id-addressable files from one declared asset
 * host, written to `storage/app/private/artwork/` (gitignored), with a `manifest.json` beside them.
 *
 * Three rules shape it, all of them the ADR's rather than this file's invention.
 * Nothing is stored in the database: a rendered path is derived from the id the catalog row
 * already carries, so a column here would be a second store for a fact that has one owner
 * (`PRD.md` §6.12). A file is never requested for a Trainer: ids come from catalog columns,
 * never from a request. And a miss is a normal state, not an error, because the mirror is
 * partial by nature (`ADR-0021` Decision 5, `DESIGN.md` §4.7).
 */
final class ArtworkMirror
{
    /** The one source this service reads, and the only asset entry in `config('uma.sources')`. */
    public const SOURCE_KEY = 'gametora-artwork';

    private const DISK = 'local';

    private const ROOT = 'artwork';

    private const MANIFEST = self::ROOT.'/manifest.json';

    public function __construct(private readonly SourceFetcher $fetcher) {}

    /**
     * Which artwork sets the config declares, in the order a default run walks them.
     *
     * @return list<string>
     */
    public function kinds(): array
    {
        $paths = config('uma.sources.'.self::SOURCE_KEY.'.paths');

        return is_array($paths) ? array_keys($paths) : [];
    }

    /**
     * The path under the asset host's own base, with the id substituted.
     *
     * `{id}` is cast to an int before it is interpolated, so a value that arrived as a string
     * carrying a path separator or a scheme cannot reach `SourceFetcher`.
     */
    public function relativePath(string $kind, int $id): string
    {
        $shape = config('uma.sources.'.self::SOURCE_KEY.".paths.{$kind}");

        if (! is_string($shape)) {
            throw new InvalidArgumentException("Unknown artwork kind '{$kind}'. Declared: ".implode(', ', $this->kinds()));
        }

        return str_replace('{id}', (string) $id, $shape);
    }

    /**
     * The ids this kind mirrors, straight out of the catalog. No request value reaches here.
     *
     * @return list<int>
     */
    public function sourceIds(string $kind): array
    {
        $ids = match ($kind) {
            'card_portrait' => CharacterCard::query()->orderBy('card_id')->pluck('card_id')->all(),
            'support_thumb' => SupportCard::query()->orderBy('support_id')->pluck('support_id')->all(),
            default => throw new InvalidArgumentException("No catalog column is declared for artwork kind '{$kind}'."),
        };

        return array_map(static fn (mixed $id): int => (int) $id, $ids);
    }

    /**
     * Where the file sits on the disk, relative to `storage/app/private`.
     */
    private function storedPath(string $relativePath): string
    {
        return self::ROOT.'/'.$relativePath;
    }

    /**
     * Fetch one kind into the mirror.
     *
     * @return array{kind: string, ids: int, fetched: int, already: int, unresolved: int, bytes: int}
     */
    public function mirror(string $kind, bool $skipExisting = true, bool $dryRun = false): array
    {
        $ids = $this->sourceIds($kind);
        $manifest = $this->manifest();
        $counts = ['kind' => $kind, 'ids' => count($ids), 'fetched' => 0, 'already' => 0, 'unresolved' => 0, 'bytes' => 0];

        foreach ($ids as $id) {
            $relative = $this->relativePath($kind, $id);
            $stored = $this->storedPath($relative);

            if ($skipExisting && Storage::disk(self::DISK)->exists($stored)) {
                $counts['already']++;

                continue;
            }

            if ($dryRun) {
                $counts['fetched']++;

                continue;
            }

            $response = $this->fetcher->fetchAsset(self::SOURCE_KEY, $relative);

            if ($response === null) {
                // A 404 and a transport failure are one bucket because the mirror's answer to both is
                // the same: no file, and the text-only row keeps rendering. Which one it was is in the
                // log line `SourceFetcher::send()` writes with the status code.
                $counts['unresolved']++;

                continue;
            }

            Storage::disk(self::DISK)->put($stored, $response['body']);

            $manifest[$relative] = [
                'url' => $response['url'],
                'sha256' => hash('sha256', $response['body']),
                'fetched_at' => now()->toIso8601String(),
            ];

            $counts['fetched']++;
            $counts['bytes'] += strlen($response['body']);
        }

        if (! $dryRun) {
            $this->writeManifest($manifest);
        }

        return $counts;
    }

    /**
     * The audit trail for the files: url, sha256 and fetched-at per path. It records where a byte
     * came from, which is what `ADR-0021` chose over a `data_sources` row per image — provenance
     * rows there are for engine-owned facts about the catalog, and a file is not a fact.
     *
     * @return array<string, array{url: string, sha256: string, fetched_at: string}>
     */
    public function manifest(): array
    {
        $raw = Storage::disk(self::DISK)->get(self::MANIFEST);

        if ($raw === null) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, array{url: string, sha256: string, fetched_at: string}>  $manifest
     */
    private function writeManifest(array $manifest): void
    {
        if ($manifest === []) {
            return;
        }

        ksort($manifest);

        Storage::disk(self::DISK)->put(
            self::MANIFEST,
            (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }
}
