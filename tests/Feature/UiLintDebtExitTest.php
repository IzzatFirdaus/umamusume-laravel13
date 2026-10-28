<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/*
 * The lint gate's one job is to be seen by CI (plan §5.4, 2026-10-28 observation): `npm run lint:ui`
 * used to print `[debt-increase]` and exit 0, so a regression in the arbitrary-value count could not
 * fail anything.
 *
 * The check is a source walker whose root is `process.cwd()`, so the fixture is a throwaway tree run
 * from its own directory: one `.vue` template carrying arbitrary values, and a baseline one lower.
 * That is the smallest input that reproduces a count regression without touching the real baseline.
 */
it('exits non-zero when the arbitrary-value count grows past the baseline', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uma-lint-fixture-'.uniqid();

    File::ensureDirectoryExists($root.'/resources/css');
    File::ensureDirectoryExists($root.'/resources/js/pages');
    File::ensureDirectoryExists($root.'/tools');

    File::put($root.'/resources/css/app.css', ':root {
    color: #000;
}
');
    File::put($root.'/resources/js/pages/Fixture.vue', <<<'VUE'
<template>
    <p class="text-[11px] leading-[1.2]">Two arbitrary values.</p>
</template>
VUE);
    // One real finding against a baseline of zero: the count grew.
    File::put($root.'/tools/ui-lint-baseline.json', json_encode(['count' => 0, 'allowlist' => []], JSON_PRETTY_PRINT));

    $result = Process::path($root)->run(['node', base_path('tools/ui-framework-check.mjs')]);

    expect($result->exitCode())->toBe(1)
        ->and($result->errorOutput())->toContain('[debt-increase]');

    File::deleteDirectory($root);
});

it('keeps reporting debt at zero exit while the count holds', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uma-lint-fixture-'.uniqid();

    File::ensureDirectoryExists($root.'/resources/css');
    File::ensureDirectoryExists($root.'/resources/js/pages');
    File::ensureDirectoryExists($root.'/tools');

    File::put($root.'/resources/css/app.css', ':root {
    color: #000;
}
');
    File::put($root.'/resources/js/pages/Fixture.vue', <<<'VUE'
<template>
    <p class="text-[11px]">One arbitrary value, at the baseline.</p>
</template>
VUE);
    File::put($root.'/tools/ui-lint-baseline.json', json_encode(['count' => 1, 'allowlist' => []], JSON_PRETTY_PRINT));

    $result = Process::path($root)->run(['node', base_path('tools/ui-framework-check.mjs')]);

    // The finding still prints; only the regression is fatal.
    expect($result->errorOutput())->toContain('[arbitrary-value]')
        ->and($result->exitCode())->toBe(0);

    File::deleteDirectory($root);
});
