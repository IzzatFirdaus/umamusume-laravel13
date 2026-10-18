<?php

declare(strict_types=1);

/*
 * The agent contract, as a guard (plan 1.12).
 *
 * `AGENTS.md` tells a contributor never to put a repository identifier into shipped copy, never to
 * show a bare `N/A`, and never to cite a docs path at a Trainer. Every one of those rules was, until
 * this file, enforced by reading. Reading is not a gate: the scan below found nineteen shipped
 * identifiers across eleven files while the rule had been in force for weeks.
 *
 * WHAT COUNTS AS SHIPPED TEXT
 *
 * Only text that reaches the screen. A docblock, a `//` comment and a `*` continuation line are for
 * maintainers and are skipped: they are where an identifier is *supposed* to live, because that is
 * how a future reader learns why a screen says what it says. Stripping them is what keeps the guard
 * from failing on its own documentation.
 *
 * PHP side: the scan reads every file under `app/` that is not a comment, and fails on an identifier
 * inside a string literal. Vue side: the scan reads the template block only (not `<script setup>`,
 * where identifiers are fine) and fails on an identifier in text content.
 *
 * N/A
 *
 * A visible `N/A` is allowed only where it carries a `title`, because the reason is the half that
 * tells a Trainer whether to go and enter a reading. `sr-only` text is allowed: it is the accessible
 * form of an absence and is never the only thing on screen. A `<details>` whose summary is only `N/A`
 * is never allowed, because there is no sentence for a reader to open.
 */

/**
 * Strips PHP comments conservatively, leaving string literals intact.
 *
 * A regex that removes `/* ... *\/` wholesale would also eat a docblock's own closing inside a string,
 * which no file here does; but a docblock opener inside a string (`'/**'`) would confuse it. Instead
 * this walks line by line and tracks block state, which is what the file actually needs.
 *
 * Trailing comments are handled too: `return true; // local-only tool (ARCHITECTURE §8)` is a comment,
 * and the first version only skipped lines that BEGAN with `//`, so every inline trailing comment
 * counted as shipped copy. Stripping happens outside string literals only, so a `//` inside a URL or
 * a message string is left alone.
 *
 * @return array<int, array{line: int, text: string}>
 */
function shippedPhpLines(string $source): array
{
    $out = [];
    $inBlock = false;

    foreach (preg_split('/\R/', $source) ?: [] as $index => $line) {
        $trimmed = ltrim($line);

        if (! $inBlock && str_starts_with($trimmed, '/*')) {
            $inBlock = ! str_contains($trimmed, '*/');

            continue;
        }

        if ($inBlock) {
            if (str_contains($trimmed, '*/')) {
                $inBlock = false;
            }

            continue;
        }

        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*')) {
            continue;
        }

        $out[] = ['line' => $index + 1, 'text' => stripTrailingPhpComment($line)];
    }

    return $out;
}

/**
 * Removes a trailing `//` comment, ignoring a `//` that sits inside a quoted string.
 *
 * A URL such as `https://example.test` must survive, so the scan tracks whether it is inside single
 * or double quotes before it treats `//` as the start of a comment.
 */
function stripTrailingPhpComment(string $line): string
{
    $inSingle = false;
    $inDouble = false;
    $length = strlen($line);

    for ($i = 0; $i < $length; $i++) {
        $char = $line[$i];

        if ($char === '\\') {
            $i++;

            continue;
        }

        if ($char === "'" && ! $inDouble) {
            $inSingle = ! $inSingle;

            continue;
        }

        if ($char === '"' && ! $inSingle) {
            $inDouble = ! $inDouble;

            continue;
        }

        if (! $inSingle && ! $inDouble && $char === '/' && ($line[$i + 1] ?? '') === '/') {
            return substr($line, 0, $i);
        }
    }

    return $line;
}

/**
 * The `<template>` block of a Vue SFC, with HTML comments removed.
 *
 * `<script setup>` is excluded on purpose: identifiers in code are not shipped copy. A template-only
 * read is also what makes the guard precise enough to be useful, because a JSON path or a docs
 * reference in a transform function is not a Trainer-facing string.
 *
 * Returns the template's content AND the line number the block starts on, so a reported line is the
 * line in the FILE. The first version indexed the stripped text and reported line 61 for a file whose
 * template starts at line 3, which sent every finding to the wrong place.
 *
 * @return array{text: string, offset: int}|null
 */
function shippedVueTemplate(string $source): ?array
{
    if (preg_match('/<template>(.*)<\/template>/s', $source, $m, PREG_OFFSET_CAPTURE) !== 1) {
        return null;
    }

    $raw = $m[1][0];
    $offset = substr_count(substr($source, 0, (int) $m[1][1]), "\n");

    // Comments are blanked with newlines preserved rather than removed, so a line number after a
    // multi-line comment still points at the same file line.
    $blanked = preg_replace_callback(
        '/<!--.*?-->/s',
        static fn (array $hit): string => preg_replace('/[^\n]/', ' ', $hit[0]) ?? '',
        $raw,
    ) ?? $raw;

    return ['text' => $blanked, 'offset' => $offset];
}

/**
 * Identifier patterns that must never reach a Trainer.
 *
 * `§\d` is included because a section reference is as opaque as an ADR number: the reader does not
 * hold the document. It is matched only in the Vue template, since PHP docblocks carry them legally.
 *
 * @return array<string, string>
 */
function shippedIdentifierPatterns(): array
{
    return [
        'an ADR citation' => '/\bADR-\d+/',
        'a decision identifier' => '/\bD-\d{2,3}\b/',
        'a goal identifier' => '/\bG-\d+\b/',
        'a known-issue identifier' => '/\bKI-\d+/',
        'an open-question identifier' => '/\bOQ-\d+\b/',
        'a docs path' => '#\bdocs/#',
        // `.php` only. `.json` was in the first version and matched `base_path('.agents/skills.json')`,
        // which is a real data file the product reads rather than a source reference a Trainer should
        // never see. A `.php` path has no such legitimate reading.
        'a source-file path' => '/\b[\w\/\\\\]+\.php\b/',
        'a section reference' => '/§\d/',
    ];
}

it('never ships a repository identifier to a Trainer', function (): void {
    $patterns = shippedIdentifierPatterns();
    $violations = [];

    /*
     * Rulings where a `.php` path is the correct reading rather than a citation.
     *
     * `DatabaseGuard::assertNoConfigurationCache()` throws to an OPERATOR running a harness, not to a
     * Trainer: the message's whole value is naming the file whose existence is the fault. Line 233 is
     * the `file_exists()` probe itself. Rewriting either would degrade a diagnostic to save a rule
     * that was written about copy. Keyed on file and line so an edit that moves the string falls back
     * into the scan.
     */
    $machinePathRulings = [
        'app/Services/DatabaseSafety/DatabaseGuard.php:233',
        'app/Services/DatabaseSafety/DatabaseGuard.php:238',
    ];

    foreach (glob(base_path('app/{,*/,*/*/,*/*/*/}*.php'), GLOB_BRACE) ?: [] as $file) {
        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file);

        foreach (shippedPhpLines((string) file_get_contents($file)) as $entry) {
            $lineKey = $relative.':'.$entry['line'];

            foreach ($patterns as $what => $pattern) {
                if (preg_match($pattern, $entry['text']) === 1) {
                    if ($what === 'a source-file path' && in_array($lineKey, $machinePathRulings, true)) {
                        continue;
                    }

                    $violations[] = sprintf('%s:%d carries %s', $relative, $entry['line'], $what);
                }
            }
        }
    }

    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/js'), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.vue')) {
            continue;
        }

        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
        $template = shippedVueTemplate((string) file_get_contents($file->getPathname()));

        if ($template === null) {
            continue;
        }

        foreach (preg_split('/\R/', $template['text']) ?: [] as $index => $line) {
            foreach ($patterns as $what => $pattern) {
                if (preg_match($pattern, $line) === 1) {
                    $violations[] = sprintf(
                        '%s:%d carries %s',
                        $relative,
                        $index + 1 + $template['offset'],
                        $what,
                    );
                }
            }
        }
    }

    expect($violations)->toBe([], sprintf(
        "Repository identifiers reached shipped copy:\n  %s",
        implode("\n  ", $violations),
    ));
});

it('never renders a visible N/A without a title, and never as a disclosure summary', function (): void {
    $violations = [];
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/js'), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.vue')) {
            continue;
        }

        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());

        // A <details> whose summary is only N/A: there is no sentence for a reader to open, so the
        // disclosure is an affordance that leads to nothing.
        if (preg_match('/<summary[^>]*>\s*N\/A\s*<\/summary>/i', $source) === 1) {
            $violations[] = sprintf('%s has a <summary> that is only N/A', $relative);
        }

        // A visible N/A that IS the element's text, with no title on that element.
        //
        // The match is on an N/A that stands alone between tags (`>N/A<`, `{{ 'N/A' }}`), not on the
        // word inside a sentence. Prose that merely mentions the marker ("A cell with no source
        // behind it reads N/A, and its title says why") is copy ABOUT the rule and is not itself a
        // bare marker; the first version failed on eleven such sentences. `sr-only` N/A is allowed:
        // it is the accessible form of an absence, never the only thing on screen.
        $template = shippedVueTemplate($source);
        if ($template === null) {
            continue;
        }

        foreach (preg_split('/\R/', $template['text']) ?: [] as $index => $line) {
            if (preg_match('/>\s*N\/A\s*</', $line) !== 1 && preg_match("/\{\{\s*'N\/A'\s*\}\}/", $line) !== 1) {
                continue;
            }

            $fileLine = $index + 1 + $template['offset'];

            // The reason may sit on the tag that opened this text, so the title check looks at the
            // template lines around the hit rather than only at the text line itself.
            $context = implode("\n", array_slice(preg_split('/\R/', $template['text']) ?: [], max(0, $index - 2), 5));

            if (str_contains($context, 'title=') || str_contains($context, 'sr-only')) {
                continue;
            }

            $violations[] = sprintf('%s:%d shows N/A without a title', $relative, $fileLine);
        }
    }

    expect($violations)->toBe([], sprintf(
        "Visible N/A without a reason:\n  %s",
        implode("\n  ", $violations),
    ));
});

/*
 * Config display strings.
 *
 * `config/scenarios.php` composes strings a page prints, and the `app/**` scan above never reads it:
 * the guard covered controllers and services but not the file several of them read their copy from.
 * The gap was found on 2026-10-18 by reading `planning_focus_reason` (three `docs/` paths and a `§9`)
 * by hand while the guard reported those paths clean. Same pattern set, same shipped-lines rule.
 *
 * The allowlist below is the pre-existing debt this first scan found: 38 shipped identifiers in
 * `config/reference.php`, `config/scenarios.php` and `config/advisor.php`, measured on 2026-10-18.
 * They are listed rather than fixed because two of the three files are held and the third's copy is
 * a page's own work. Each entry is debt, not a precedent: a guard that fails on the tree as it stands
 * is a guard the next dispatch turns off (`tools/ui-lint.mjs` records the same ruling for its own
 * arbitrary-value list). Removing an entry is a welcome change; adding one is not.
 */
function configIdentifierDebt(): array
{
    return [
        // config/advisor.php
        'config/advisor.php:34', 'config/advisor.php:42', 'config/advisor.php:49', 'config/advisor.php:56',
        // config/reference.php
        'config/reference.php:43', 'config/reference.php:52', 'config/reference.php:56',
        'config/reference.php:150', 'config/reference.php:164', 'config/reference.php:182',
        'config/reference.php:191', 'config/reference.php:200', 'config/reference.php:209',
        'config/reference.php:218', 'config/reference.php:227', 'config/reference.php:236',
        'config/reference.php:245', 'config/reference.php:262', 'config/reference.php:277',
        'config/reference.php:286', 'config/reference.php:295', 'config/reference.php:304',
        'config/reference.php:313', 'config/reference.php:322',
        // config/scenarios.php (held)
        'config/scenarios.php:152', 'config/scenarios.php:166', 'config/scenarios.php:298',
        'config/scenarios.php:350', 'config/scenarios.php:364', 'config/scenarios.php:468',
        'config/scenarios.php:487', 'config/scenarios.php:500', 'config/scenarios.php:551',
        'config/scenarios.php:577', 'config/scenarios.php:765', 'config/scenarios.php:772',
        'config/scenarios.php:825', 'config/scenarios.php:889', 'config/scenarios.php:897',
        'config/scenarios.php:948', 'config/scenarios.php:976', 'config/scenarios.php:1072',
        'config/scenarios.php:1073', 'config/scenarios.php:1077', 'config/scenarios.php:1087',
        'config/scenarios.php:1090', 'config/scenarios.php:1138',
    ];
}

it('never ships a repository identifier from a config display string', function (): void {
    $patterns = shippedIdentifierPatterns();
    $debt = configIdentifierDebt();
    $violations = [];

    foreach (glob(base_path('config/*.php')) ?: [] as $file) {
        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file);

        foreach (shippedPhpLines((string) file_get_contents($file)) as $entry) {
            foreach ($patterns as $what => $pattern) {
                if (preg_match($pattern, $entry['text']) === 1) {
                    if (in_array(sprintf('%s:%d', $relative, $entry['line']), $debt, true)) {
                        continue;
                    }

                    $violations[] = sprintf('%s:%d carries %s', $relative, $entry['line'], $what);
                }
            }
        }
    }

    expect($violations)->toBe([], sprintf(
        "Repository identifiers reached a config display string:\n  %s",
        implode("\n  ", $violations),
    ));
});

/*
 * Defect-fingerprint locators.
 *
 * A test that finds an element by the identifier the copy is supposed to hide proves the defect
 * rather than the fix: it passes while the identifier ships, and fails the moment the copy is
 * corrected. Six such pins were found and repaired between 2.1a and 2.1d, each by hand, because no
 * guard looked for them. This one does.
 *
 * The marker check is deliberately narrow: a browser spec's `hasText`/`getByText` and a Pest
 * `assertSee`/`assertDontSee`/`->where` that carry an identifier INSIDE a quoted string. A comment
 * naming the identifier is fine and is skipped, because that is where the ruling lives.
 */
it('never locates a test by a repository identifier', function (): void {
    $identifier = '(?:ADR-\d+|\bD-\d{2,3}\b|\bG-\d+\b|KI-\d+|\bOQ-\d+\b|§\d)';

    $specs = base_path('tests/browser');
    $tests = base_path('tests/Feature');

    $locator = '~(?:hasText|getByText|assertSee|assertDontSee)\s*\(\s*[\'"][^\'"]*'.$identifier.'[^\'"]*[\'"]~u';
    $where = '~->where\s*\(\s*[\'"][^\'"]*[\'"]\s*,\s*[\'"][^\'"]*'.$identifier.'[^\'"]*[\'"]~u';

    $violations = [];

    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($specs, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.spec.ts')) {
            continue;
        }

        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
        $lines = preg_split('/\R/', (string) file_get_contents($file->getPathname())) ?: [];

        foreach ($lines as $index => $line) {
            $trimmed = ltrim($line);

            // A comment naming the identifier is where the ruling lives, not a locator.
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            if (preg_match($locator, $line) === 1) {
                $violations[] = sprintf('%s:%d locates an element by a repository identifier', $relative, $index + 1);
            }
        }
    }

    foreach (glob($tests.'/*.php') ?: [] as $file) {
        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file);

        foreach (shippedPhpLines((string) file_get_contents($file)) as $entry) {
            if (preg_match($locator, $entry['text']) === 1 || preg_match($where, $entry['text']) === 1) {
                $violations[] = sprintf('%s:%d pins a repository identifier in an assertion', $relative, $entry['line']);
            }
        }
    }

    expect($violations)->toBe([], sprintf(
        "Tests locate elements by identifiers the copy must hide:\n  %s",
        implode("\n  ", $violations),
    ));
});
