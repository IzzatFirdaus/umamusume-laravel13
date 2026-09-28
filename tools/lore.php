<?php

declare(strict_types=1);

/*
 * The lore gates, runnable without GNU make.
 *
 * KI-4 exists because `make lore` and `make lore-code` are the recorded way to run
 * these greps, and `make` is absent on this project's Windows hosts — every slice has
 * so far had to say "make is not available, the recipe bodies were run by hand".
 *
 * A composer script holding the same command strings does not work: composer hands the
 * string to cmd.exe on Windows, and cmd does not treat single quotes as quoting, so
 * `-- ':!vendor'` reaches git still wearing its quotes. git then fails with
 * "':!vendor' is outside repository", `|| true` swallows it, and the gate prints
 * nothing and exits 0. A gate that reports clean while matching nothing is worse than
 * no gate, because it reads as a pass.
 *
 * So the greps run here, through Process with an argument array: no shell, no quoting
 * layer, identical behaviour on sh and cmd.
 *
 * Usage:  php tools/lore.php          repo-wide, tracked files   (mirrors `make lore`)
 *         php tools/lore.php code     app paths, untracked too   (mirrors `make lore-code`)
 *
 * Both stay report-only, like the Makefile's `|| true`: the Lore Guardian decides
 * whether a hit is a violation (CONSTRAINTS.md §3, AGENTS.md), so the script's job is
 * to surface, not to fail. The exit code is always 0; the hit count is printed so a
 * caller can assert on it.
 *
 * Line marker (R51). A hit line carrying a `<!-- lore-ignore-line ... -->` comment is
 * skipped and counted separately, which is what stops the gate's own ruling tables from
 * inflating the total every time a slice records a new itemization row. The comment
 * opener is part of the needle so prose about the mechanism is not mistaken for a
 * directive. The marker is line-scoped, not file- or block-scoped: one marked row
 * exempts that row and nothing else. It carries the allowed-sense class and the ruling
 * it answers to, and `LoreGateParityTest` holds both halves of that shape plus the rule
 * that markers live in `docs/` only. The `make lore` recipes filter the same literal, so
 * the two runners cannot disagree about what was skipped. `lore-code` deliberately does
 * not: a marker outside `docs/` is against the rule, and the hit it tried to hide still
 * prints.
 */

use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$mode = ($argv[1] ?? '') === 'code' ? 'code' : 'docs';

// Flags and patterns are the Makefile's, copied literally. Dropping `-E` while
// keeping `-w` turns the pattern into a basic regex, where `|` is a literal bar and
// the whole alternation matches nothing — a clean sweep that means nothing. If a
// pattern changes, both copies change and LoreGateParityTest fails naming the diff.
$excluded = [':!vendor', ':!node_modules', ':!docs/PRE-MORTEM.md'];
$appPaths = ['app/**', 'config/**', 'resources/**', 'routes/**', 'database/**', 'tests/**', 'lang/**'];

$runs = $mode === 'code'
    ? [
        ['-inwE', 'horse|horses|sire|sires|foal|foals|mare|mares|filly|jockey|saddle|bridle|hoof|hooves|mane|paddock|tack|reins|herd|mount', $appPaths, true],
        ['-inwE', 'dam|stable|wisdom|motivation|strength|endurance|luck|agility|charisma|gacha|jewel|factor|grass|sand|friend|planned|archived|account|login', $appPaths, true],
        ['-inE', 'condition gauge|pick-?up banner|share link', $appPaths, true],
    ]
    : [
        ['-inE', 'horse|sire|foal|🏇', $excluded, false],
        ['-inwE', 'dam|mare|stable', $excluded, false],
        ['-inwE', 'stallion|colt|filly|gelding|equine|pony|thoroughbred|breeding|pairing|bloodline|pedigree|lineage|hoof|mane|tail|withers', $excluded, false],
    ];

$total = 0;
$exempt = 0;

foreach ($runs as [$flags, $pattern, $paths, $untracked]) {
    $command = ['git', 'grep', $flags];

    if ($untracked) {
        $command[] = '--untracked';
    }

    $command[] = $pattern;
    $command[] = '--';

    foreach ($paths as $path) {
        $command[] = $path;
    }

    $process = new Process($command, dirname(__DIR__));
    $process->run();

    // git grep exits 1 when it matched nothing and 0 when it did; anything else is a
    // real failure and must not be mistaken for a clean sweep.
    if (! in_array($process->getExitCode(), [0, 1], true)) {
        fwrite(STDERR, "lore: git grep failed (exit {$process->getExitCode()}): {$process->getErrorOutput()}\n");
        exit(2);
    }

    $output = trim($process->getOutput());

    if ($output !== '') {
        foreach (explode("\n", $output) as $hit) {
            // A marked line is a ruling already on the page, not a new hit. Counted
            // apart so the exemption is visible in the summary rather than silent, and
            // only in docs mode: markers belong in `docs/` (R51), and a marker planted
            // outside must buy nothing, so `code` mode never filters. The comment opener
            // is part of the needle so prose and headings that merely name the directive
            // are not mistaken for one.
            if ($mode === 'docs' && str_contains($hit, '<!-- lore-ignore-line')) {
                $exempt++;

                continue;
            }

            $total++;
            echo $hit."\n";
        }
    }
}

echo "lore-{$mode}: {$total} hit(s)".($mode === 'docs' ? ", {$exempt} exempt line(s)" : '')."\n";

exit(0);
