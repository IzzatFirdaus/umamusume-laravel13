<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DataPipeline\ArtworkMirror;
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
    public function show(ArtworkMirror $mirror, string $kind, int $id): Response
    {
        $allowed = ['card_portrait', 'support_thumb'];

        if (! in_array($kind, $allowed, true) || $id <= 0) {
            throw new NotFoundHttpException;
        }

        if (! $mirror->exists($kind, $id)) {
            throw new NotFoundHttpException;
        }

        $bytes = $mirror->disk()->get(
            $mirror->storedPath($mirror->relativePath($kind, $id))
        );

        return response($bytes, 200, ['Content-Type' => 'image/png']);
    }
}
