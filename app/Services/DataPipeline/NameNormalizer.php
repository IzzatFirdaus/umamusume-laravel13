<?php

declare(strict_types=1);

namespace App\Services\DataPipeline;

use Normalizer;

/**
 * Pure normalization: display names are never mutated, only match keys (ARCHITECTURE §5).
 * NFKD then strip combining marks, so "Cafe"/"Café" and full/half-width katakana share a key.
 */
final class NameNormalizer
{
    public function normalize(string $name): string
    {
        $folded = Normalizer::normalize($name, Normalizer::FORM_KD);

        if ($folded === false) {
            $folded = $name;
        }

        $lower = mb_strtolower($folded);

        $stripped = preg_replace('/\p{Mn}+/u', '', $lower) ?? $lower;

        // NFKD maps the ideographic middle dot to its halfwidth form; strip both.
        return str_replace(['・', '･', '-', ' ', '　'], '', $stripped);
    }
}
