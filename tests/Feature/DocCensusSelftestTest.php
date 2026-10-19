<?php

declare(strict_types=1);
use Symfony\Component\Process\Process;

/*
 * The census's own resolver check has to run, or it is a script nobody executes.
 *
 * `tools/doc_census_selftest.py` asserts the citation resolver in both directions: a live
 * citation must not read dead, and a deleted target must. It was red at HEAD and nothing
 * invoked it, so the red it carried was invisible. `DocCitationParityTest` only shells out
 * to the census and gates the total, and it cannot see a resolver that over-resolves and so
 * lowers that total for the wrong reason.
 *
 * The suite is what people run on this host, and GNU make does not run here (KI-4), so a
 * Makefile recipe would reproduce the same invisibility. This test is the wiring.
 *
 * It asserts the exit status and the script's own success line, and nothing about the
 * corpus. A dead-reference count is a property of the tree rather than of the resolver, so
 * pinning one here would make the test a hostage to whatever the corpus happens to hold.
 * The resolver's own fixtures live in the script, where they can be replaced deliberately
 * rather than spent by an unrelated edit.
 */
it('runs the census self-test and passes', function (): void {
    $process = new Process(
        ['python', base_path('tools/doc_census_selftest.py')],
        base_path(),
    );
    $process->run();

    // The self-test reports its failures as an assertion traceback and a non-zero exit,
    // so the exit status is the verdict and the output is only there to say why.
    expect($process->getExitCode())->toBe(
        0,
        $process->getOutput()."\n".$process->getErrorOutput(),
    );

    // Silence must not read as a pass. The script says when it passed, and it says so on
    // stdout, where the census prints its own counts.
    expect($process->getOutput())->toContain('resolver check passed');
});
