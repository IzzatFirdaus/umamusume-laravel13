<?php

declare(strict_types=1);

use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/*
 * Slice 1.5: migration reversibility, actually exercised.
 *
 * `AGENTS.md` §11 requires "Every migration has a working `down()`", and until this file nothing
 * checked it. A static assertion that the word `down` appears in each file would have been cheap and
 * nearly worthless: it passes for a `down()` whose body is empty or whose table name is wrong. So this
 * runs the real thing against a real database — roll the whole stack back, prove the tables are gone,
 * then migrate forward again and prove they are back.
 *
 * **Why this lives in Unit with its own TestCase binding.** `tests/Pest.php` binds `TestCase` and
 * `RefreshDatabase` to `Feature` only, and `tests/Unit` is otherwise database-free (see
 * `DatabaseGuardPathTest`'s "Pure predicate, no config and no database"). This test needs the container
 * to run Artisan but must NOT have `RefreshDatabase`, which owns the schema lifecycle it exists to
 * exercise, so it takes `TestCase` alone.
 *
 * **Why it is safe on a developer machine.** The connection is repointed at a file under
 * `storage/framework/testing/`, never `database/database.sqlite`, and the test proves that twice before
 * it writes: the resolved paths differ, and `DatabaseGuard` agrees the process is isolated. The
 * destructive action here is the one that would wipe a Trainer's career records if it ever pointed at
 * the wrong file, so it gets two gates rather than a comment.
 */
uses(TestCase::class)->in(__FILE__);

beforeEach(function (): void {
    // A per-test filename: Windows holds a handle for the life of the connection, and reusing one path
    // across two cases made the second `unlink` fail with "Resource temporarily unavailable".
    $path = storage_path('framework/testing/migration-reversibility-'.getmypid().'-'.uniqid().'.sqlite');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    touch($path);

    // The guard reads its role and its expectation from the environment, so the process declares
    // itself isolated before the connection moves anywhere.
    putenv('UMA_DATABASE_ROLE=isolated');
    putenv('UMA_EXPECTED_DATABASE='.$path);
    config([
        'database.connections.sqlite.database' => $path,
        'database-safety.canonical' => database_path('database.sqlite'),
        // `phpunit.xml` forces `UMA_DATABASE_ROLE=test`, so the guard takes its isolated path and may
        // emit a diagnostic. Resolve the logger at the Laravel facade level rather than letting the
        // channel fall through to a bare Monolog instance, whose API is `warning()`, not `warn()`.
        'logging.default' => 'single',
    ]);
    DB::purge('sqlite');

    $this->migrationPath = $path;
});

afterEach(function (): void {
    putenv('UMA_DATABASE_ROLE');
    putenv('UMA_EXPECTED_DATABASE');

    // Close the handle before unlinking, or Windows keeps the file locked.
    DB::disconnect('sqlite');
    DB::purge('sqlite');

    if (file_exists($this->migrationPath)) {
        unlink($this->migrationPath);
    }
});

it('rolls the whole schema back and migrates it forward again on an isolated database', function (): void {
    // Gate 1: the connection must not be the canonical file. Resolved, because a relative
    // `database/database.sqlite` and an absolute one are the same database.
    expect(realpath($this->migrationPath))->not->toBe(realpath(database_path('database.sqlite')));

    // Gate 2: the guard must agree this process is isolated.
    expect(app(DatabaseGuard::class)->isIsolatedRole())->toBeTrue();

    Artisan::call('migrate', ['--force' => true]);
    $files = count(glob(database_path('migrations/*.php')) ?: []);
    expect($files)->toBeGreaterThan(0);

    // Every migration ran: the ledger holds one row per file.
    expect(DB::table('migrations')->count())->toBe($files);

    // The tables a career actually lives in exist before the rollback, so what follows is a real
    // removal and not a database that was empty all along.
    foreach (['training_runs', 'turn_entries', 'veterans'] as $table) {
        expect(DB::getSchemaBuilder()->hasTable($table))->toBeTrue();
    }

    Artisan::call('migrate:rollback', ['--force' => true]);

    // An empty ledger is the definition of "the whole stack came apart" rather than one batch of it.
    expect(DB::table('migrations')->count())->toBe(0);

    foreach (['training_runs', 'turn_entries', 'veterans'] as $table) {
        expect(DB::getSchemaBuilder()->hasTable($table))->toBeFalse();
    }

    // And forward again from nothing: the half a `down()`-only check would never reach.
    Artisan::call('migrate', ['--force' => true]);
    expect(DB::table('migrations')->count())->toBe($files);

    foreach (['training_runs', 'turn_entries', 'veterans'] as $table) {
        expect(DB::getSchemaBuilder()->hasTable($table))->toBeTrue();
    }
});

it('rolls back and reapplies twice, so a stale ledger cannot hide a broken down()', function (): void {
    Artisan::call('migrate', ['--force' => true]);
    $files = count(glob(database_path('migrations/*.php')) ?: []);

    foreach ([1, 2] as $pass) {
        Artisan::call('migrate:rollback', ['--force' => true]);
        expect(DB::table('migrations')->count())->toBe(0);

        Artisan::call('migrate', ['--force' => true]);
        expect(DB::table('migrations')->count())->toBe($files);
    }
});
