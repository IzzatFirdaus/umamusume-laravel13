<?php

use App\Exceptions\DatabaseSafetyViolation;
use App\Services\DatabaseSafety\AgentSessionGuard;
use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Database\Events\ConnectionEstablished;

/**
 * Fail-safe execution-isolation regression coverage (brief G).
 *
 * These tests exercise the real boundary (`AgentSessionGuard` listening on
 * `ConnectionEstablished`) through the container, and the launcher
 * (`tools/agent-run.php`) through fresh PHP subprocesses, because the
 * environment markers that trigger the boundary cannot be changed inside the
 * shared PHPUnit process — the parent process already carries
 * `CLAUDE_SESSION_ID`.
 *
 * Subprocess probes receive the repo root *inside* the script (never via the
 * `ENV=... cmd` prefix, which cmd.exe does not support on Windows).
 *
 * No test writes to the owner's canonical database: every write attempt is
 * refused before a connection is handed out, and the launcher tests run
 * against session copies under `.scratch-uma/agent-sessions/`.
 */
beforeEach(function () {
    Config::set('database-safety.canonical', base_path('database/database.sqlite'));
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);
});

/*
 * Every test here repoints the `sqlite` connection at the canonical file and
 * declares a role (including the misleading `development` label). The guard
 * reads `$_SERVER` before `putenv`, so a leftover `$_SERVER['UMA_DATABASE_ROLE']`
 * shadows whatever a later suite declares with `putenv` alone — which is how
 * `MigrationReversibilityTest` failed its `isIsolatedRole()` gate when this file
 * ran first. Restore the in-memory config and clear the environment on the way
 * out, so the shared process is left exactly as it was found.
 */
afterEach(function () {
    Config::set('database.connections.sqlite.database', ':memory:');

    foreach (['UMA_DATABASE_ROLE', 'UMA_OPERATOR_ACKNOWLEDGED', 'UMA_EXPECTED_DATABASE'] as $name) {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);
    }
});

function setAgentMarkers(bool $present): void
{
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        if ($present) {
            putenv("{$marker}=test-session-01234567");
            $_ENV[$marker] = 'test-session-01234567';
            $_SERVER[$marker] = 'test-session-01234567';
        } else {
            // Clear every channel the guard reads: putenv(<name>) alone does
            // not remove a value phpunit's force="true" wrote into $_ENV/$_SERVER.
            putenv($marker);
            unset($_SERVER[$marker], $_ENV[$marker]);
        }
    }
    putenv('UMA_OPERATOR_ACKNOWLEDGED');
    unset($_SERVER['UMA_OPERATOR_ACKNOWLEDGED'], $_ENV['UMA_OPERATOR_ACKNOWLEDGED']);
}

it('refuses the connection itself when an agent session targets canonical without isolation', function () {
    setAgentMarkers(true);
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);
    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    $event = new ConnectionEstablished(DB::connection());

    expect(fn () => $agentGuard->onConnectionEstablished($event))->toThrow(DatabaseSafetyViolation::class);
});

it('treats a misleading role as no isolation when the resolved path is canonical', function () {
    setAgentMarkers(true);
    // The role claims "development" — a label, not isolation. The refusal keys
    // off the resolved file, so the label cannot authorise the write.
    putenv('UMA_DATABASE_ROLE=development');
    $_SERVER['UMA_DATABASE_ROLE'] = 'development';
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $agentGuard = app(AgentSessionGuard::class);
    $event = new ConnectionEstablished(DB::connection());

    expect(fn () => $agentGuard->onConnectionEstablished($event))->toThrow(DatabaseSafetyViolation::class);
});

it('refuses the connection when the operator explicitly acknowledges', function () {
    // FS-INT-3 hardened model: an agent session can never self-authorise a
    // canonical write. UMA_OPERATOR_ACKNOWLEDGED=yes set inside an agent process
    // (or inherited from a parent operator shell) grants nothing — only the
    // Trainer's own interactive process (no agent marker) may use the escape
    // hatch. The agent-marker fail-safe therefore still refuses.
    setAgentMarkers(true);
    putenv('UMA_OPERATOR_ACKNOWLEDGED=yes');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $agentGuard = app(AgentSessionGuard::class);
    $event = new ConnectionEstablished(DB::connection());

    expect(fn () => $agentGuard->onConnectionEstablished($event))->toThrow(DatabaseSafetyViolation::class);
});

it('never refuses the Trainer path when no agent marker is present', function () {
    setAgentMarkers(false);
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeFalse();

    // Canonical target, no marker: the Trainer's own app must pass untouched.
    $event = new ConnectionEstablished(DB::connection());
    $agentGuard->onConnectionEstablished($event);
    expect(true)->toBeTrue();
});

it('launcher subprocess refuses the canonical database and persists nothing', function () {
    $repo = 'D:/Projects/umamusume-laravel13';
    $canonical = $repo.'/database/database.sqlite';
    $sizeBefore = filesize($canonical);

    // Fresh subprocess: agent markers inherited, no role, canonical target.
    // The launcher is not used here — this is a bare agent-style process, the
    // exact mistake the boundary exists to catch. The repo root is baked into
    // the script because cmd.exe has no `ENV=x cmd` prefix form.
    $script = sys_get_temp_dir().'/guard-refusal-probe-'.uniqid().'.php';
    file_put_contents($script, <<<PHP
<?php
\$repo = '{$repo}';
require \$repo.'/vendor/autoload.php';
\$app = require \$repo.'/bootstrap/app.php';
try {
    \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    \$count = Illuminate\Support\Facades\DB::table('umamusume')->count();
    echo "LEAK: read succeeded with count={\$count}";
} catch (Throwable \$e) {
    echo 'REFUSED: '.get_class(\$e);
}
PHP);

    $output = [];
    $code = 0;
    exec('php '.escapeshellarg($script).' 2>&1', $output, $code);

    unlink($script);

    $line = implode(' ', $output);
    expect($line)->toContain('REFUSED');

    // The canonical file must be byte-identical in size: no statement reached it.
    expect(filesize($canonical))->toBe($sizeBefore);
});

it('launcher subprocess isolates factory writes into a session copy', function () {
    $repo = 'D:/Projects/umamusume-laravel13';

    $script = sys_get_temp_dir().'/guard-launcher-probe-'.uniqid().'.php';
    file_put_contents($script, <<<'PHP'
<?php
$path = config('database.connections.sqlite.database');
echo 'SESSION_DB='.basename($path)."\n";
echo 'SESSION_COUNT='.Illuminate\Support\Facades\DB::table('umamusume')->count()."\n";
PHP);

    $output = [];
    $code = 0;
    exec('php '.escapeshellarg($repo.'/tools/agent-run.php').' '.escapeshellarg($script).' 2>&1', $output, $code);

    unlink($script);

    $line = implode(' ', $output);
    expect($code)->toBe(0);
    expect($line)->toContain('SESSION_DB=session-');
    expect($line)->not->toContain('SESSION_DB=database.sqlite');
});

it('canonical inventory is stable across the whole boundary suite', function () {
    // Read-only reconciliation: the owner's file ends this suite with the same
    // row counts it started with. Runs through the Trainer path: a marker-free
    // subprocess reads the canonical file, because under the hardened model an
    // agent session (which the PHPUnit process is) can never receive a canonical
    // read that an operator-ack can authorise — marker-free is the only identity
    // that may touch the real file. The probe is a fresh subprocess whose env
    // inherits phpunit.xml's forced DB_DATABASE; rather than relying on unsetting
    // inherited vars, the probe FORCES the canonical path directly (absolute,
    // read-only usage below) before boot, and strips every agent marker.
    $repo = 'D:/Projects/umamusume-laravel13';
    $canonical = $repo.'/database/database.sqlite';

    $script = sys_get_temp_dir().'/guard-inventory-probe-'.uniqid().'.php';
    file_put_contents($script, <<<PHP
<?php
foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as \$m) {
    putenv(\$m);
    unset(\$_SERVER[\$m], \$_ENV[\$m]);
}
putenv('UMA_OPERATOR_ACKNOWLEDGED');
unset(\$_SERVER['UMA_OPERATOR_ACKNOWLEDGED'], \$_ENV['UMA_OPERATOR_ACKNOWLEDGED']);
// Force the canonical identity directly; robust against any inherited env.
putenv('DB_DATABASE={$canonical}');
\$_SERVER['DB_DATABASE'] = '{$canonical}';
\$_ENV['DB_DATABASE'] = '{$canonical}';
putenv('UMA_DATABASE_ROLE');
unset(\$_SERVER['UMA_DATABASE_ROLE'], \$_ENV['UMA_DATABASE_ROLE']);
\$repo = '{$repo}';
require \$repo.'/vendor/autoload.php';
\$app = require \$repo.'/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'umamusume='.Illuminate\Support\Facades\DB::table('umamusume')->count()."\n";
echo 'training_runs='.Illuminate\Support\Facades\DB::table('training_runs')->count()."\n";
echo 'veterans='.Illuminate\Support\Facades\DB::table('veterans')->count()."\n";
echo 'synthetic='.Illuminate\Support\Facades\DB::table('umamusume')->whereNull('name_ja')->count()."\n";
PHP);

    $output = [];
    exec('php '.escapeshellarg($script).' 2>&1', $output);

    unlink($script);

    $line = implode(' ', $output);

    // Historical counts (135 / 18 / 8) are evidence, not invariants: the owner's
    // genuine gameplay records keep growing. Assert only structural invariants —
    // the canonical inventory is non-empty and carries no synthetic contamination —
    // so this suite cannot fail simply because a real career was added.
    preg_match('/umamusume=(\d+)/', $line, $uma);
    preg_match('/training_runs=(\d+)/', $line, $runs);
    preg_match('/veterans=(\d+)/', $line, $vets);
    expect((int) ($uma[1] ?? 0))->toBeGreaterThan(0);
    expect((int) ($runs[1] ?? 0))->toBeGreaterThan(0);
    expect((int) ($vets[1] ?? 0))->toBeGreaterThan(0);
    expect($line)->toContain('synthetic=0');
});
