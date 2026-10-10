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

/*
 * R2-07, the same retirement on the server side and in the inline copy.
 *
 * `GET /training-runs/{run}` answers with a redirect to the Cockpit (`TrainingRunController::redirect()`,
 * F2, plan §9.6; `Runs/Show.vue` and its `show()` method were deleted in the same pass), so a sentence
 * naming that screen as the place to log a turn, mark a skill, or change a status is an instruction with
 * no destination behind it. The nav rail was relabelled at `d251f7f` and the inline copy was not, which
 * is what the second audit caught on the Cockpit, the Career Result and the Timeline (R2-07).
 *
 * This is a grep gate run against the sources rather than the rendered pages, for the reason
 * `DesignTokensTest` gives for the same choice: what a Vue page prints is not in the server response, so
 * a rendered sweep would have to boot every screen in the app. Comments are stripped before matching
 * because they are where the retirement is documented *by naming it*, which is the allowed use; the ban
 * is on the words that reach a Trainer.
 */

/**
 * Every source that can put a sentence in front of a Trainer.
 *
 * RecursiveDirectoryIterator, not `glob('**')`: PHP's glob does not expand `**`, so a glob-based sweep
 * reads one directory and passes without having looked at the rest.
 *
 * @return array<string, string>
 */
function spokenSources(): array
{
    $sources = [];

    foreach (['app', 'resources/js', 'resources/views', 'lang'] as $root) {
        $dir = base_path($root);

        if (! is_dir($dir)) {
            continue;
        }

        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            if (preg_match('/(\.blade\.php|\.php|\.vue|\.ts)$/', $file->getFilename()) !== 1) {
                continue;
            }

            $sources[str_replace([DIRECTORY_SEPARATOR, base_path().DIRECTORY_SEPARATOR], ['/', ''], $file->getPathname())] =
                (string) file_get_contents($file->getPathname());
        }
    }

    return $sources;
}

/**
 * The comments out of a source, leaving what can reach an element or a response.
 *
 * `ponytail:` line comments are found by `//` not preceded by `:`, the same shape `DesignTokensTest`
 * uses; it covers a URL inside a string and would also cut a trailing double slash inside one. A real
 * tokenizer is the upgrade path, worth it only if a sentence ever hides behind that shape.
 */
function spokenWords(string $source): string
{
    $patterns = [
        '/\{\{--.*?--\}\}/s',
        '/<!--.*?-->/s',
        '/\/\*.*?\*\//s',
        '/(?<!:)\/\/[^\n]*/',
        '/^[ \t]*\*[^\n]*$/m',
    ];

    return (string) preg_replace($patterns, '', $source);
}

it('never names the retired run record screen in words a Trainer reads', function (): void {
    $hits = [];

    // The retired screen's own names, and nothing looser. "The run recorded it" and "a career records
    // the scenario" are ordinary English about the run's data, and a guard that flags them trains the
    // next reader to delete the guard. "The run record", "the run screen" and "the career record" are on
    // the list because those are how the same screen was addressed without the word "screen", and each
    // one was still sending a Trainer to it in shipped copy.
    $banned = '/run record screen|\brecord screens?\b|\bcareer record\b|the run record\b|\brun screen\b/i';

    foreach (spokenSources() as $path => $source) {
        if (preg_match($banned, spokenWords($source), $match) === 1) {
            $hits[] = $path.': '.$match[0];
        }
    }

    // The list is the finding: it fails by name and line, so the next stale sentence is caught at the
    // screen that carries it rather than in an audit pass.
    expect($hits)->toBe([]);
});

it('links every run door at the screen that answers it', function (): void {
    // `runs.show` is the compatibility doorway, and the ruling holds it for the two redirects a write
    // answers with (`syncDeck` and the status write). A *link* aimed at it is a different thing: it
    // sends a Trainer through a redirect to a page their label already names, which is how the retired
    // screen's name survived in copy. The whitelist is the doorway's own file and nothing else.
    $offenders = [];

    foreach (spokenSources() as $path => $source) {
        $count = preg_match_all("/route\(\s*'runs\.show'/", spokenWords($source));

        if ($count > 0 && ! str_ends_with($path, 'TrainingRunController.php')) {
            $offenders[] = basename($path)." ({$count})";
        }
    }

    expect($offenders)->toBe([]);

    // And the doorway itself still answers: the deck write and the status write redirect through it, so
    // the guard above narrows links and leaves the ruling's two redirects standing.
    expect(preg_match_all("/route\(\s*'runs\.show'/", spokenWords(
        (string) file_get_contents(base_path('app/Http/Controllers/TrainingRunController.php'))
    )))->toBe(2);
});
