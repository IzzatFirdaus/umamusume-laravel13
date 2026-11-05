<?php

declare(strict_types=1);

/*
 * `.gitattributes` declares `* text=auto eol=lf`, and source-reading tests depend on it.
 *
 * This guard exists because the rule was broken and nothing said so. A pass rewrote twenty-one
 * `.vue`/`.ts`/`.json` files with CRLF while editing them; `git` reported only a "CRLF will be
 * replaced by LF" warning, which reads as cosmetic. It was not. `RulesetVersionVocabularyTest`
 * matches `/^\s*Ruleset version:$/m` against a `file_get_contents()` result, and PCRE's `$` (without
 * `/D`) does not match before a `\r`, so the same bytes passed on LF and failed on CRLF: two tests
 * went red against a change that had not touched their subject.
 *
 * The check is deliberately narrow. It reads the working tree, not the index, and it names the files
 * it checks so that a new tree (a fresh clone, a CI checkout) cannot pass by checking nothing: a
 * sweep that silently reads zero files is the failure mode this repository has hit before.
 */
it('keeps every source file on the LF endings .gitattributes declares', function (): void {
    $roots = ['resources/js', 'resources/views', 'tests', 'app', 'config', 'database', 'routes'];
    $suffixes = ['php', 'vue', 'ts', 'json', 'css'];
    $offenders = [];
    $checked = 0;

    foreach ($roots as $root) {
        $path = base_path($root);

        if (! is_dir($path)) {
            continue;
        }

        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (! in_array($file->getExtension(), $suffixes, true)) {
                continue;
            }

            $checked++;

            $body = (string) file_get_contents($file->getPathname());

            if (str_contains($body, "\r\n")) {
                $offenders[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
    }

    // The floor, not a budget: if the sweep finds nothing to read it is broken, not the tree.
    expect($checked)->toBeGreaterThan(500, 'the line-ending sweep read no files, so it proves nothing');
    expect($offenders)->toBe([], 'these files carry CRLF though .gitattributes declares eol=lf: '.implode(', ', $offenders));
});
