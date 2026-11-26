<?php

/**
 * Fail-safe launcher for agent-oriented PHP execution (brief C: safe commands
 * for every agent).
 *
 * Guards `php tools/agent-run.php` sessions — Tinker experiments, factory
 * sweeps, fixture generators, diagnostic scripts — by refusing to run anything
 * while the process targets the canonical development database without
 * isolation. The refusal happens before the payload runs, so nothing can
 * mutate the file first.
 *
 * Usage (payload is a PHP file path or `--tinker`):
 *
 *   php tools/agent-run.php path/to/script.php            # run with forced isolation
 *   php tools/agent-run.php --tinker                       # isolated tinker shell
 *
 * Isolation established here:
 *   UMA_DATABASE_ROLE   = test          (activates every DatabaseGuard rule)
 *   DB_DATABASE         = .scratch-uma/agent-sessions/<session-id>.sqlite
 *                         (absolute, session-private, never the canonical file)
 *   UMA_EXPECTED_DATABASE = same path   (positive identity check at boot)
 *
 * The scratch database is copied from the canonical file when it does not
 * exist yet, so the session sees the Trainer's current data read-only by
 * default — mutations land in the copy, not the real file.
 *
 * The canonical file is never opened for writing by this launcher: the copy is
 * made with SQLite's `VACUUM INTO`, which takes a consistent snapshot of a
 * live WAL database without holding a write lock.
 */

use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$guard = $app->make(DatabaseGuard::class);

$repo = dirname(__DIR__);

// Session identity: the agent session marker when present, else the PID, so
// concurrent agent sessions never share a scratch file (acceptance test G.7).
$sessionId = ($_SERVER['CLAUDE_SESSION_ID'] ?? $_SERVER['CODEBUDDY_SESSION_ID']
    ?? $_ENV['CLAUDE_SESSION_ID'] ?? $_ENV['CODEBUDDY_SESSION_ID'] ?? null);
$sessionKey = $sessionId !== null ? substr((string) $sessionId, 0, 8) : 'pid-'.getmypid();

$scratchDir = $repo.'/.scratch-uma/agent-sessions';
if (! is_dir($scratchDir)) {
    mkdir($scratchDir, 0777, true);
}

$scratch = $scratchDir.'/session-'.$sessionKey.'.sqlite';
// Strip any operator-authorisation variables the agent process may have
// inherited from its parent shell. An agent child carrying UMA_OPERATOR_ACKNOWLEDGED=yes
// and UMA_ALLOW_CANONICAL_DESTRUCTIVE=true must not gain canonical write or
// destructive access merely by inheritance (FS-INT-3 Check #4). Only the Trainer's
// own interactive process (no agent marker) may set these.
foreach (['UMA_OPERATOR_ACKNOWLEDGED', 'UMA_ALLOW_CANONICAL_DESTRUCTIVE'] as $var) {
    unset($_SERVER[$var]);
    unset($_ENV[$var]);
    putenv($var);
}

putenv('UMA_DATABASE_ROLE=test');
putenv('DB_DATABASE='.$scratch);
putenv('UMA_EXPECTED_DATABASE='.$scratch);
$_SERVER['UMA_DATABASE_ROLE'] = 'test';
$_SERVER['DB_DATABASE'] = $scratch;
$_SERVER['UMA_EXPECTED_DATABASE'] = $scratch;
$_ENV['UMA_DATABASE_ROLE'] = 'test';
$_ENV['DB_DATABASE'] = $scratch;
$_ENV['UMA_EXPECTED_DATABASE'] = $scratch;

// Config was already resolved at bootstrap, so re-bind the connection config to
// the scratch path before anything opens it.
config(['database.connections.sqlite.database' => $scratch]);

if (! file_exists($scratch)) {
    // Seed the session copy from the canonical file via a read-only VACUUM INTO.
    $canonical = $guard->canonicalPath();
    if (file_exists($canonical)) {
        $source = new PDO('sqlite:'.$canonical, null, null, [PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY]);
        $source->exec("VACUUM INTO '".str_replace("'", "''", $scratch)."'");
        echo "[agent-run] session copy seeded from canonical snapshot\n";
    } else {
        touch($scratch);
        echo "[agent-run] no canonical file; session starts empty\n";
    }
}

echo "[agent-run] session {$sessionKey} isolated at {$scratch}\n";

// Prove the identity before handing control to the payload.
$check = $app->make(Kernel::class)->call('uma:db-guard');
if ($check !== 0) {
    fwrite(STDERR, "[agent-run] pre-flight failed; refusing to run payload\n");
    exit(1);
}

// Dispatch the payload as an artisan command so it boots with the isolated env.
$kernel = $app->make(Kernel::class);

$args = array_slice($argv, 1);
if (($args[0] ?? '') === '--tinker') {
    $status = $kernel->call('tinker');
} elseif (($args[0] ?? '') === '--migrate-fresh') {
    $status = $kernel->call('migrate:fresh');
} elseif (($args[0] ?? '') !== '' && is_file($args[0])) {
    // Run a PHP script inside the isolated environment.
    $status = 0;
    try {
        require $args[0];
    } catch (Throwable $e) {
        fwrite(STDERR, '[agent-run] payload failed: '.$e->getMessage()."\n");
        $status = 1;
    }
} else {
    fwrite(STDERR, "usage: php tools/agent-run.php <script.php | --tinker | --migrate-fresh>\n");
    exit(2);
}

exit($status ?? 0);
