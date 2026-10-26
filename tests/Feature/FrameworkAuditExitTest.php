<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;

/*
 * The framework audit must never pass vacuously (plan step 7.3).
 *
 * The port in `tools/ui-framework-audit.mjs` already departs from the source on this point: an absent
 * Playwright import exits **2**, and a Chromium that cannot launch exits **2** with `UNVERIFIED`, rather
 * than exiting 0 as the upstream `framework-audit.mjs` did at its `:31-34`. The rule the instruction
 * carries is "a CI run cannot pass when the driver is missing", so this test pins the behaviour rather
 * than the number:
 *
 *   - the exit code is non-zero,
 *   - the output says `UNVERIFIED`, and
 *   - the reason is printed rather than swallowed.
 *
 * The launch is forced to fail by pointing `PLAYWRIGHT_BROWSERS_PATH` at an empty directory, which is
 * cheaper and more honest than uninstalling a package: it is the same code path a missing browser takes.
 */
it('reports UNVERIFIED and exits non-zero when its browser driver cannot launch', function (): void {
    $empty = sys_get_temp_dir().DIRECTORY_SEPARATOR.'td-empty-browsers-'.getmypid();
    @mkdir($empty);

    try {
        $result = Process::path(base_path())
            ->env(['PLAYWRIGHT_BROWSERS_PATH' => $empty])
            ->timeout(180)
            ->run('node tools/ui-framework-audit.mjs');

        $output = $result->output().$result->errorOutput();

        expect($result->exitCode(), 'the audit exited 0 without measuring anything')->not->toBe(0)
            ->and($output)->toContain('UNVERIFIED')
            ->and($output)->toContain('could not launch');
    } finally {
        @rmdir($empty);
    }
});
