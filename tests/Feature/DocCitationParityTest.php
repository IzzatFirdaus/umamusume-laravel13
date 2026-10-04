<?php

declare(strict_types=1);

/*
 * The documentation-citation ratchet, mirroring LoreGateParityTest's shape.
 *
 * `tools/doc_census.py` counts backticked `.md` references across tracked markdown that do not resolve —
 * tried as written, under `docs/`, and beside the citing file. This gate pins the total so citation rot
 * fails a test instead of accumulating silently, which is what it did for a week: the census existed and
 * computed the number, and nothing invoked it.
 *
 * Three properties of the measurement, so nobody "fixes" this test against them:
 *
 * 1. The count is deliberately the SIGNAL, not a report. `tools/doc_census.py:112` prints only the first
 *    twenty dead links (`dead[:20]`) plus a per-target summary; a gate that printed five hundred lines
 *    would be scrolled past and then deleted. Run `composer docs` for the full output.
 * 2. The number OVERCOUNTS by design, and this test does not pretend otherwise. Most GONE entries are
 *    provenance lines inside masters naming the sources they absorbed — dated records that must not be
 *    edited, so they are counted here and tolerated. The ratchet's job is that the count never RISES; the
 *    worklist for lowering it is the census's own per-target summary, read with the triage rule in
 *    `docs/PLAN-DOC-SYNC-2026-10-02.md` Task 5 (repoint a line a reader would follow; errata for a record).
 * 3. The baseline is a measured value, not an aspiration, and it moves one way. It was recorded on
 *    2026-10-02 immediately after the doc-sync pass's own edits landed, so it includes the rot this
 *    repository's dated records carry. Lower it when a citation is repointed. Never raise it to make a
 *    failing gate pass: that is `CONSTRAINTS.md`'s "no task may weaken a threshold" applied to a number.
 *
 * Deliberately NOT mirrored into the Makefile. GNU make cannot run on this host (KI-4); the Makefile's
 * targets are documentation, and `LoreGateParityTest`'s make/composer parity assertion is left unextended
 * for `docs` so nobody "fixes" the asymmetry by adding a target that cannot execute here.
 */

const DOC_CITATION_BASELINE = 708;

it('keeps dead markdown citations from rising above the recorded baseline', function (): void {
    $out = (string) shell_exec('python tools/doc_census.py 2>&1');

    // The census must actually have measured something: an empty or errored run would otherwise read as
    // zero dead links and pass the gate by not having looked.
    expect($out)->toContain('tracked markdown');
    expect($out)->toMatch('/dead markdown links: \d+/');

    preg_match('/dead markdown links: (\d+)/', $out, $m);

    expect((int) $m[1])->toBeLessThanOrEqual(DOC_CITATION_BASELINE);
});

it('pins the baseline at the value the 2026-10-02 pass measured, so a silent edit cannot move it', function (): void {
    // A ratchet only works if the number it enforces is itself tracked. If someone changes
    // DOC_CITATION_BASELINE without re-measuring and recording why, this line fails.
    expect(DOC_CITATION_BASELINE)->toBe(708);
});
