<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\ParallelTesting;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Every `Storage::fake('local')` in a test resolves to one fixed root,
         * `storage/framework/testing/disks/local`, and `Storage::fake()` calls
         * `cleanDirectory()` on that root before each test. Two Pest processes run in the
         * same worktree therefore wipe each other's snapshots mid-run: a fetch whose body
         * is already on disk short-circuits as "unchanged", writes nothing and still exits
         * 0, so the test fails on a row that was never written — the same false-regression
         * shape KI-74 describes for a contended box. Giving the fake disk a per-process
         * token makes the root process-private, the isolation KI-73/KI-80 gave the browser
         * suite. `TEST_TOKEN` wins when Pest's own parallel runner set it, so parallel
         * runs keep their own worker isolation.
         */
        $token = $_SERVER['TEST_TOKEN'] ?? null;

        ParallelTesting::resolveTokenUsing(
            fn (): string => is_string($token) && $token !== '' ? $token : (string) getmypid()
        );
    }
}
