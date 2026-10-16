<?php

declare(strict_types=1);

/*
 * The Dashboard's layout gates (DL.14 → DL.3 flip).
 *
 * `wide_bento_enabled` defaults to true now that the DESIGN.md:301-321 shell-width ruling
 * has landed. The env var `DASHBOARD_WIDE_BENTO_ENABLED` can still override it to false
 * for regression testing, but the shipped default is enabled.
 */
return [
    'wide_bento_enabled' => env('DASHBOARD_WIDE_BENTO_ENABLED', true),
];
