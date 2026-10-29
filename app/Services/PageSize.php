<?php

declare(strict_types=1);

namespace App\Services;

/**
 * The one page-size rule the filter surfaces share (SCREEN_SPEC.md §7-9, ADR-0018).
 *
 * `pageSize` is clamped rather than validated on every surface, web and API: a nonsense size is a
 * smaller or bigger page, not a refused request, because the value usually arrives from a URL a
 * Trainer pasted or a tab restored, and the filter they were working with is worth more than the
 * argument. `ApiV1ValidationEnvelopeTest:85` states the same rule for `/api/v1`.
 *
 * The numbers are the ones `CatalogController`, `SkillController` and the three API controllers
 * each restated as `min(100, max(1, ...))`. Extracting them changes no behaviour: absent is the
 * default, `?pageSize=` still lands on the floor, and 200 still lands on the ceiling.
 *
 * A surface whose default is not 25 passes its own (`SupportCardController::index()` shows the five
 * rows its wireframe draws). That is a parameter, not a second constant: the floor and ceiling are the
 * same rule on every surface.
 */
class PageSize
{
    public const MIN = 1;

    public const MAX = 100;

    public const DEFAULT = 25;

    public static function clamp(mixed $raw, int $default = self::DEFAULT): int
    {
        return min(self::MAX, max(self::MIN, (int) ($raw ?? $default)));
    }
}
