<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * The run-record surface is gone: `runs.show` is a redirect to the cockpit
 * (`routes/web.php:116`), so a label naming "the run record" now points at a screen that does not
 * exist. This pins the repointing.
 *
 * Two assertions, each able to fail for its own reason:
 *
 * 1. No browser file addresses `runs.show` at all. The links take a server-provided `run_url`, which
 *    already lands on the cockpit through the redirect; a literal route name would be a second,
 *    staler address for the same door.
 * 2. No browser file renders the label `Run record`. The markers are the rendered ones (`>Run record<`,
 *    `Run record</a>`, `Run record</Link>`), so a docblock that names the retired surface for history
 *    is not caught by a copy assertion that is not about history.
 *
 * **No file is exempt.** `resources/js/layouts/CareerLayout.vue` carried the nav rail's own `Run record`
 * door while a concurrent session held unstaged hunks in it; the door is now labelled `Cockpit` and the
 * exemption that skipped the file is gone, so both assertions cover the whole browser surface.
 *
 * @return list<string>
 */
function browserSurfaceFiles(): array
{
    $root = base_path('resources/js');

    $files = [];

    foreach (Finder::create()->files()->in($root)->name(['*.vue', '*.ts']) as $file) {
        $files[] = str_replace('\\', '/', $file->getRelativePathname());
    }

    sort($files);

    return $files;
}

it('addresses no retired route name anywhere in the browser surface', function (): void {
    $offenders = [];

    foreach (browserSurfaceFiles() as $path) {
        if (str_contains((string) file_get_contents(base_path('resources/js/'.$path)), 'runs.show')) {
            $offenders[] = $path;
        }
    }

    expect($offenders)->toBe([]);
});

it('renders no label naming the retired run-record surface', function (): void {
    $markers = ['>Run record<', 'Run record</a>', 'Run record</Link>'];
    $offenders = [];

    foreach (browserSurfaceFiles() as $path) {
        $source = (string) file_get_contents(base_path('resources/js/'.$path));

        foreach ($markers as $marker) {
            if (str_contains($source, $marker)) {
                $offenders[] = $path.' -> '.$marker;
            }
        }
    }

    expect($offenders)->toBe([]);
});
