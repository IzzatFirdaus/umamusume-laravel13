<?php

declare(strict_types=1);

/*
 * The census must report a tracked doc that is absent from disk, not raise on it.
 *
 * `tools/doc_census.py` reads every path `git ls-files` lists. An unstaged deletion leaves the file
 * listed and off the filesystem, so the run died with `FileNotFoundError` inside `dead_links()`, and
 * `DocCitationParityTest`, which only shells out to the tool, failed for a reason that was not
 * citation rot. Counting what is missing is this tool's subject; choking on it is not.
 *
 * The expected set is derived from git and the filesystem rather than fixed to one path, so the
 * assertion survives the deletion being committed or restored: when nothing is missing the category
 * still prints and the per-file lines are simply absent.
 */
it('reports its counts instead of raising when a tracked doc is missing from disk', function (): void {
    $out = (string) shell_exec('python tools/doc_census.py 2>&1');

    // The closing summary is the proof the tool finished; the traceback is the failure mode.
    expect($out)->not->toContain('Traceback')
        ->and($out)->toContain('dead markdown links:')
        ->and($out)->toContain('MISSING_FILE');

    $tracked = explode("\n", (string) shell_exec('git ls-files -- *.md'));

    $missing = array_values(array_filter(
        $tracked,
        static fn (string $path): bool => trim($path) !== '' && ! file_exists(base_path(trim($path))),
    ));

    foreach ($missing as $path) {
        expect($out)->toContain($path);
    }
});
