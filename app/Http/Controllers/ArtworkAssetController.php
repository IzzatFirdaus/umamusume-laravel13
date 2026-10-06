<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DataPipeline\ArtworkMirror;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Streams an artwork file the mirror has already fetched. Id-addressed, not
 * path-addressed: the controller validates `kind` against the asset host's
 * declared paths and `id` against the integer cast, so a request like
 * `/artwork/../../etc/passwd` is refused before it reaches Storage.
 */
final class ArtworkAssetController extends Controller
{
    public function show(Request $request, ArtworkMirror $mirror, string $kind, int $id): Response
    {
        $allowed = ['card_portrait', 'support_thumb'];

        if (! in_array($kind, $allowed, true) || $id <= 0) {
            throw new NotFoundHttpException;
        }

        if (! $mirror->exists($kind, $id)) {
            throw new NotFoundHttpException;
        }

        $stored = $mirror->storedPath($mirror->relativePath($kind, $id));
        $disk = $mirror->disk();
        $mtime = $disk->lastModified($stored);

        // The mirrored bytes for one id change only when a Trainer re-runs `uma:fetch-art`, so the file's
        // own mtime and size are the validator. The manifest is not read here: it holds every entry, and
        // decoding all of them to answer about one file is the cost this route exists to avoid, and
        // hashing the bytes on every request trades a download for a read of the same bytes.
        $response = response($disk->get($stored), 200, [
            'Content-Type' => 'image/png',
            'ETag' => sprintf('W/"%x-%x"', $mtime, $disk->size($stored)),
            'Last-Modified' => gmdate('D, d M Y H:i:s T', $mtime),
            // The header this route was missing. With none, a screen change that re-creates an `<img>`
            // has nothing reusable and asks again; the owner's report of frames reloading on every
            // navigation is this line's absence, and `AGENTS.md` §8 already caches the catalog reads
            // beside these files while this one read the disk uncached on every paint.
            //
            // ponytail: the window is five minutes because the URL carries no version of the content, so
            // this is the whole of the staleness ceiling after a manual re-mirror. The upgrade path is a
            // content-hash segment in the route (`ADR-0021` owns that shape), which would make the
            // response immutable and let the window widen; shortening it instead would put the
            // re-download back. This still reads each file into memory; `response()->file()` streams, and
            // would answer the existing byte assertion from the file rather than from the body.
            'Cache-Control' => 'public, max-age=300',
        ]);

        // Revalidation after the window: the framework turns a matching validator into a 304 with no
        // body, which is the cheap answer when a Trainer re-opens a screen the next day.
        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }
}
