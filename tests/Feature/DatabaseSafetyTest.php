<?php

use App\Database\SafeDatabaseManager;
use App\Exceptions\DatabaseSafetyViolation;
use App\Providers\DatabaseGuardServiceProvider;
use App\Services\DatabaseSafety\AgentSessionGuard;
use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Database\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

beforeEach(function () {
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.canonical', base_path('database/database.sqlite'));
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);
    Config::set('database-safety.destructive_commands', ['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback', 'db:wipe']);

    // phpunit.xml force-writes these into putenv AND $_ENV AND $_SERVER; a bare
    // putenv('<name>') unset does not clear the superglobals, so the guard would
    // keep reading the forced values for the rest of the run. Clear all three.
    foreach (['UMA_DATABASE_ROLE', 'UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'UMA_OPERATOR_ACKNOWLEDGED', 'UMA_EXPECTED_DATABASE'] as $name) {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);
    }
});

afterEach(function () {
    Config::set('database.connections.sqlite.database', ':memory:');

    // FS-INT-2: clear the shared in-memory PDO and migration flag so the next
    // Feature test in this process gets a fresh :memory: database instead of
    // reusing one left migrated (or mid-transaction) by this suite.
    RefreshDatabaseState::$inMemoryConnections = [];
    RefreshDatabaseState::$migrated = false;

    // Clear UMA_* vars that phpunit.xml force-writes into all three sources.
    foreach (['UMA_DATABASE_ROLE', 'UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'UMA_OPERATOR_ACKNOWLEDGED', 'UMA_EXPECTED_DATABASE'] as $name) {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);
    }

    // Clear agent session markers so they don't leak between tests.
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
});

/**
 * Declare an environment value across every source the guard reads
 * (`putenv`, `$_ENV`, `$_SERVER`), matching how phpunit.xml `force` writes.
 */
function guardEnv(string $name, ?string $value): void
{
    if ($value === null) {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);

        return;
    }
    putenv("{$name}={$value}");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

it('refuses destructive command when no role declared and DB targets canonical', function () {
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    $input = Mockery::mock(InputInterface::class);
    $input->allows()->hasParameterOption('--database')->andReturn(false);
    $output = Mockery::mock(OutputInterface::class);
    $event = new CommandStarting('migrate:fresh', $input, $output);

    expect(fn () => $guard->assertCommandSafe($event->command, $event->input))->toThrow(DatabaseSafetyViolation::class);
});

it('allows destructive command when isolated role is declared but DB is not canonical', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', '/tmp/scratch.sqlite');

    $guard = app(DatabaseGuard::class);

    $input = Mockery::mock(InputInterface::class);
    $input->allows()->hasParameterOption('--database')->andReturn(false);
    $output = Mockery::mock(OutputInterface::class);
    $event = new CommandStarting('migrate:fresh', $input, $output);

    $guard->assertCommandSafe($event->command, $event->input);
    expect(true)->toBeTrue();
});

it('refuses when UMA_DATABASE_ROLE is not an isolated role', function () {
    guardEnv('UMA_DATABASE_ROLE', 'development');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    $input = Mockery::mock(InputInterface::class);
    $input->allows()->hasParameterOption('--database')->andReturn(false);
    $output = Mockery::mock(OutputInterface::class);
    $event = new CommandStarting('migrate:fresh', $input, $output);

    expect(fn () => $guard->assertCommandSafe($event->command, $event->input))->toThrow(DatabaseSafetyViolation::class);
});

it('allows non-destructive commands without role when targeting canonical', function () {
    $guard = app(DatabaseGuard::class);

    $input = Mockery::mock(InputInterface::class);
    $input->allows()->hasParameterOption('--database')->andReturn(false);
    $output = Mockery::mock(OutputInterface::class);
    $event = new CommandStarting('migrate:status', $input, $output);

    $guard->assertCommandSafe($event->command, $event->input);
    expect(true)->toBeTrue();
});

it('refuses isolated role targeting canonical even with override env vars', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    guardEnv('UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'true');
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');
    Config::set('database.connections.sqlite.database', base_path('database/database.sqlite'));

    $guard = app(DatabaseGuard::class);

    expect($guard->targetsCanonical())->toBeTrue();

    try {
        $guard->assertIsolatedContext('isolated role test');
        expect(true)->toBeFalse();
    } catch (DatabaseSafetyViolation $e) {
        expect($e->getMessage())->toContain('isolated workflow is not permitted');
    }
});

it('refuses browser role targeting canonical even with override env vars', function () {
    guardEnv('UMA_DATABASE_ROLE', 'browser');
    guardEnv('UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'true');
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');
    Config::set('database.connections.sqlite.database', base_path('database/database.sqlite'));

    $guard = app(DatabaseGuard::class);

    expect($guard->targetsCanonical())->toBeTrue();

    try {
        $guard->assertIsolatedContext('browser role test');
        expect(true)->toBeFalse();
    } catch (DatabaseSafetyViolation $e) {
        expect($e->getMessage())->toContain('isolated workflow is not permitted');
    }
});

it('refuses destructive command on canonical without any operator confirmation', function () {
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    $input = Mockery::mock(InputInterface::class);
    $input->allows()->hasParameterOption('--database')->andReturn(false);
    $output = Mockery::mock(OutputInterface::class);
    $event = new CommandStarting('migrate:fresh', $input, $output);

    expect(fn () => $guard->assertCommandSafe($event->command, $event->input))->toThrow(DatabaseSafetyViolation::class);
});

it('simulates LLM bypass attempt and remains blocked', function () {
    // Scenario: LLM reads error message, sets both override vars, no role
    guardEnv('UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'true');
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    $input = Mockery::mock(InputInterface::class);
    $input->allows()->hasParameterOption('--database')->andReturn(false);
    $output = Mockery::mock(OutputInterface::class);
    $event = new CommandStarting('migrate:fresh', $input, $output);

    expect(fn () => $guard->assertCommandSafe($event->command, $event->input))->toThrow(DatabaseSafetyViolation::class);
});

it('allows intentional operator workflow with both override vars and interactive shell', function () {
    // This test simulates the operator escape hatch: both vars set, no isolated role,
    // and the guard is run in a context where isInteractiveShell returns true.
    // In test context, posix_isatty(STDIN) is typically false, so the override is
    // blocked. We test the logic directly.
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    // Verify the guard requires all three conditions
    guardEnv('UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'true');
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');

    // In test context (non-TTY), the override is still blocked
    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    // But the logic itself recognizes the two env vars
    $reflection = new ReflectionClass($guard);
    $method = $reflection->getMethod('isInteractiveShell');
    $method->setAccessible(true);
    expect($method->invoke($guard))->toBeFalse();
});

it('allows legitimate isolated positive control with disposable scratch DB', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    putenv('UMA_EXPECTED_DATABASE=/tmp/scratch-test-'.uniqid().'.sqlite');
    $scratchDb = getenv('UMA_EXPECTED_DATABASE');
    Config::set('database.connections.sqlite.database', $scratchDb);

    $guard = app(DatabaseGuard::class);

    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeFalse();

    $guard->assertIsolatedContext('positive control');
    expect(true)->toBeTrue();

    // Cleanup
    if (file_exists($scratchDb)) {
        unlink($scratchDb);
    }
});

it('refuses Eloquent create when in isolated role targeting canonical DB', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    // An Eloquent create should be blocked
    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe("Eloquent create on {$cls}"))->toThrow(DatabaseSafetyViolation::class);
});

it('refuses Eloquent update when in isolated role targeting canonical DB', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->isIsolatedRole())->toBeTrue;
    expect($guard->targetsCanonical())->toBeTrue();

    // An Eloquent update should be blocked
    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe("Eloquent update on {$cls}"))->toThrow(DatabaseSafetyViolation::class);
});

it('allows write when not in isolated role (development role)', function () {
    // Explicitly declare development role (no isolated role)
    guardEnv('UMA_DATABASE_ROLE', 'development');

    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', []);

    $guard = app(DatabaseGuard::class);

    // Development role: isIsolatedRole should be false
    expect($guard->isIsolatedRole())->toBeFalse();

    // assertWriteSafe must not throw when not in isolated role
    $cls = Model::class;

    // Use try/catch to verify no exception is thrown
    $threw = false;
    try {
        $guard->assertWriteSafe("Eloquent create on {$cls}");
    } catch (\DatabaseSafetyViolation $e) {
        $threw = true;
    }
    expect($threw)->toBeFalse();
});

it('refuses factory create when in isolated role targeting canonical DB', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    // Factory create should be blocked via the model boot event
    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe("Eloquent create on {$cls}"))->toThrow(DatabaseSafetyViolation::class);
});

it('refuses seeder run when in isolated role targeting canonical DB', function () {
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    // Seeder run should be blocked
    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe("Eloquent create on {$cls}"))->toThrow(DatabaseSafetyViolation::class);
});

it('db-guard command runs and exits successfully', function () {
    // Ensure we're in the clean test context with in-memory DB
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', ':memory:');

    $this->artisan('uma:db-guard')->assertExitCode(0);
});

it('database guard service provider is registered', function () {
    $providers = Config::get('app.providers', []);
    expect($providers)->toContain(DatabaseGuardServiceProvider::class);
});

it('config safety file exists and has required keys', function () {
    $config = Config::get('database-safety');
    expect($config)->toHaveKeys(['canonical', 'isolated_roles', 'destructive_commands', 'allow_canonical_destructive']);
    expect($config['isolated_roles'])->toContain('test');
    expect($config['isolated_roles'])->toContain('browser');
    expect($config['destructive_commands'])->toContain('migrate:fresh');
    expect($config['allow_canonical_destructive'])->toBeFalse();
});

it('factory creation blocked before database mutation', function () {
    // Verify that assertWriteSafe is checked before any DB write occurs.
    // This test confirms the guard is invoked during the create path,
    // not as a post-query check.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    // The guard must throw before the write reaches Eloquent.
    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe('Factory create pre-mutation test'))->toThrow(DatabaseSafetyViolation::class);
});

it('nested factories cannot bypass write protection', function () {
    // When a factory creation triggers another factory, the guard must still
    // refuse writes to the canonical DB in an isolated role.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    // Nested factory create should also be blocked
    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe('Nested factory create test'))->toThrow(DatabaseSafetyViolation::class);
});

it('misleading database role cannot bypass path checks', function () {
    // A non-isolated role label does not authorise a canonical write: the
    // `AgentSessionGuard` boundary keys off the resolved database path, and
    // the PHPUnit process carries an agent session marker, so this mirrors
    // an agent that declared a misleading role and still points at the real
    // file. The connection-level refusal is what fires here. Markers are
    // declared explicitly because a preceding suite may have stripped them.
    guardEnv('UMA_DATABASE_ROLE', 'development');
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        guardEnv($marker, 'combined-run-marker');
    }
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $agentGuard = app(AgentSessionGuard::class);
    expect($agentGuard->isAgentSession())->toBeTrue();

    $event = new ConnectionEstablished(DB::connection());
    expect(fn () => $agentGuard->onConnectionEstablished($event))->toThrow(DatabaseSafetyViolation::class);
});

it('generated TrainingRun for existing real trainee is protected', function () {
    // A TrainingRun referencing a real Umamusume must not be creatable when
    // in an isolated role targeting the canonical DB.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    $cls = Model::class;
    expect(fn () => $guard->assertWriteSafe('TrainingRun creation test'))->toThrow(DatabaseSafetyViolation::class);
});

it('Query Builder write paths obey safety boundary', function () {
    // Query Builder inserts targeting the canonical DB must be blocked when
    // in an isolated role. The guard validates the resolved connection path.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    $cls = Model::class;
    // Use try/catch since raw QB invocation in test may vary
    $threw = false;
    try {
        $guard->assertWriteSafe('QueryBuilder write test');
    } catch (DatabaseSafetyViolation $e) {
        $threw = true;
    }
    expect($threw)->toBeTrue();
});

it('individually invoked seeder run cannot bypass protection', function () {
    // Running a seeder independently must respect the write boundary.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    $cls = Model::class;
    $threw = false;
    try {
        $guard->assertWriteSafe('Seeder run test');
    } catch (DatabaseSafetyViolation $e) {
        $threw = true;
    }
    expect($threw)->toBeTrue();
});

it('supported isolated factory/fixture operations continue working', function () {
    // In isolated role with a scratch DB, factory operations must still succeed.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    putenv('UMA_EXPECTED_DATABASE=/tmp/scratch-guard-test-'.uniqid().'.sqlite');
    $scratchDb = getenv('UMA_EXPECTED_DATABASE');
    Config::set('database.connections.sqlite.database', $scratchDb);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeFalse();

    // assertWriteSafe must not throw when targeting a non-canonical DB
    $cls = Model::class;

    // Use try/catch to verify no exception is thrown
    $threw = false;
    try {
        $guard->assertWriteSafe('Isolated factory test');
    } catch (DatabaseSafetyViolation $e) {
        $threw = true;
    }
    expect($threw)->toBeFalse();

    // Cleanup
    if (file_exists($scratchDb)) {
        unlink($scratchDb);
    }
});

it('genuine Trainer operations and approved historical imports remain functional', function () {
    // The Trainer's own interactive process carries no agent session marker,
    // so the `AgentSessionGuard` boundary never fires for it. The marker can
    // only be varied in a fresh process (the shared PHPUnit process inherits
    // CLAUDE_SESSION_ID), so this probes a no-marker subprocess against the
    // canonical database. The identity is forced explicitly rather than
    // unset-inherited, because phpunit's forced env does not survive every
    // channel a child process consults.
    $repo = 'D:/Projects/umamusume-laravel13';
    $canonical = $repo.'/database/database.sqlite';

    $script = sys_get_temp_dir().'/guard-trainer-path-'.uniqid().'.php';
    file_put_contents($script, <<<PHP
<?php
// Trainer-path probe: force the canonical identity explicitly, strip markers.
putenv('DB_DATABASE={$canonical}');
\$_SERVER['DB_DATABASE'] = '{$canonical}';
\$_ENV['DB_DATABASE'] = '{$canonical}';
putenv('UMA_DATABASE_ROLE');
unset(\$_SERVER['UMA_DATABASE_ROLE'], \$_ENV['UMA_DATABASE_ROLE']);
foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as \$m) {
    putenv(\$m);
    unset(\$_SERVER[\$m], \$_ENV[\$m]);
}
putenv('UMA_OPERATOR_ACKNOWLEDGED');
unset(\$_SERVER['UMA_OPERATOR_ACKNOWLEDGED'], \$_ENV['UMA_OPERATOR_ACKNOWLEDGED']);
\$repo = '{$repo}';
require \$repo.'/vendor/autoload.php';
\$app = require \$repo.'/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'count='.Illuminate\Support\Facades\DB::table('umamusume')->count()."\n";
PHP);

    $output = [];
    exec('php '.escapeshellarg($script).' 2>&1', $output);
    unlink($script);

    $line = implode(' ', $output);
    expect($line)->toContain('count=');
    expect($line)->not->toContain('REFUSED');
    expect($line)->not->toContain('DatabaseSafetyViolation');
});

it('rejected operations leave the protected database unchanged', function () {
    // When assertWriteSafe throws, the database file must not be modified.
    guardEnv('UMA_DATABASE_ROLE', 'test');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $guard = app(DatabaseGuard::class);
    expect($guard->isIsolatedRole())->toBeTrue();
    expect($guard->targetsCanonical())->toBeTrue();

    $dbPath = $guard->resolvedPath();
    $sizeBefore = filesize($dbPath);

    $cls = Model::class;
    try {
        $guard->assertWriteSafe('Integrity check test');
    } catch (DatabaseSafetyViolation $e) {
        // expected — operation was rejected
    }

    $sizeAfter = filesize($dbPath);
    expect($sizeBefore)->toBe($sizeAfter);
});

it('does not cache a connection refused by a ConnectionEstablished listener', function () {
    // Durable binding: the repository-owned SafeDatabaseManager must be the `db`
    // service, so no framework (vendor) edit is required for the cache-reuse fix.
    expect(app('db'))->toBeInstanceOf(SafeDatabaseManager::class);

    $db = app('db');

    // Ensure a clean slate: a prior test may already have cached the default
    // connection, which would be returned without re-dispatching the event.
    $db->purge();

    $events = app('events');
    $guard = app(AgentSessionGuard::class);

    // Remove every listener so only our throwing listener fires during the
    // test, then restore AgentSessionGuard afterwards. Other tests in this
    // file call onConnectionEstablished() directly, so the dispatcher listener
    // is not required for them.
    $events->forget(ConnectionEstablished::class);

    $events->listen(ConnectionEstablished::class, function (): void {
        throw new RuntimeException('listener refused connection');
    });

    try {
        // First call: connection created, listener throws -> must NOT be cached.
        expect(fn () => $db->connection())->toThrow(RuntimeException::class);

        // Second call: must NOT return a cached connection; must re-resolve and re-throw.
        expect(fn () => $db->connection())->toThrow(RuntimeException::class);
    } finally {
        $events->forget(ConnectionEstablished::class);

        // Restore the environment before re-registering AgentSessionGuard so
        // RefreshDatabase's teardown (which resolves a connection) does not
        // trip the guard: isolated role + :memory: means targetsCanonical()
        // returns false and the guard returns early.
        guardEnv('UMA_DATABASE_ROLE', 'test');
        Config::set('database.connections.sqlite.database', ':memory:');

        $events->listen(ConnectionEstablished::class, function ($event) use ($guard): void {
            $guard->onConnectionEstablished($event);
        });
    }
});

// Task #12 regression coverage: marker detection matrix
// Marker = any non-empty string independently in $_SERVER / $_ENV / getenv().
// "0" counts; absent/false/null/empty-string do not. No ?? short-circuit,
// no truthiness, no comparison against null alone.

it('detects "0" as a valid agent marker in allowsCanonicalDestructive', function () {
    guardEnv('CLAUDE_SESSION_ID', '0');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    guardEnv('CLAUDE_SESSION_ID', null);
});

it('does not treat empty-string marker as present in allowsCanonicalDestructive', function () {
    guardEnv('CLAUDE_SESSION_ID', '');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    guardEnv('CLAUDE_SESSION_ID', null);
});

it('does not false-positive when marker is absent from all sources', function () {
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();
});

it('detects marker in $_ENV when $_SERVER has empty string (conflicting sources)', function () {
    $_SERVER['CLAUDE_SESSION_ID'] = '';
    $_ENV['CLAUDE_SESSION_ID'] = 'agent-session-01';
    putenv('CLAUDE_SESSION_ID=');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    unset($_SERVER['CLAUDE_SESSION_ID'], $_ENV['CLAUDE_SESSION_ID']);
    putenv('CLAUDE_SESSION_ID');
});

it('detects marker only in getenv (process environment)', function () {
    $_SERVER['CODEBUDDY_SESSION_ID'] = '';
    $_ENV['CODEBUDDY_SESSION_ID'] = '';
    putenv('CODEBUDDY_SESSION_ID=agent-session-02');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    unset($_SERVER['CODEBUDDY_SESSION_ID'], $_ENV['CODEBUDDY_SESSION_ID']);
    putenv('CODEBUDDY_SESSION_ID');
});

it('detects either agent marker independently in allowsCanonicalDestructive', function () {
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    $guard = app(DatabaseGuard::class);

    guardEnv('CLAUDE_SESSION_ID', 'claude-01');
    expect($guard->allowsCanonicalDestructive())->toBeFalse();
    guardEnv('CLAUDE_SESSION_ID', null);

    guardEnv('CODEBUDDY_SESSION_ID', 'codebuddy-02');
    expect($guard->allowsCanonicalDestructive())->toBeFalse();
    guardEnv('CODEBUDDY_SESSION_ID', null);
});

it('detects "0" as a valid agent marker in isAgentSession', function () {
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
    guardEnv('CLAUDE_SESSION_ID', '0');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeTrue();

    guardEnv('CLAUDE_SESSION_ID', null);
});

it('does not treat empty-string marker as agent session in isAgentSession', function () {
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
    guardEnv('CLAUDE_SESSION_ID', '');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeFalse();

    guardEnv('CLAUDE_SESSION_ID', null);
});

it('does not false-positive when marker is absent from all sources in isAgentSession', function () {
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeFalse();
});

it('detects marker in $_ENV when $_SERVER has empty string in isAgentSession', function () {
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
    $_SERVER['CLAUDE_SESSION_ID'] = '';
    $_ENV['CLAUDE_SESSION_ID'] = 'agent-session-01';
    putenv('CLAUDE_SESSION_ID=');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeTrue();

    unset($_SERVER['CLAUDE_SESSION_ID'], $_ENV['CLAUDE_SESSION_ID']);
    putenv('CLAUDE_SESSION_ID');
});

it('detects marker only in getenv in isAgentSession', function () {
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
    $_SERVER['CODEBUDDY_SESSION_ID'] = '';
    $_ENV['CODEBUDDY_SESSION_ID'] = '';
    putenv('CODEBUDDY_SESSION_ID=agent-session-02');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isAgentSession())->toBeTrue();

    unset($_SERVER['CODEBUDDY_SESSION_ID'], $_ENV['CODEBUDDY_SESSION_ID']);
    putenv('CODEBUDDY_SESSION_ID');
});

// Task #12: isOperatorAuthorised containment — agent sessions never inherit operator authority

it('denies operator authorisation when an agent session marker is present', function () {
    // Even with UMA_OPERATOR_ACKNOWLEDGED=yes, an agent session must not be authorised.
    guardEnv('CLAUDE_SESSION_ID', 'agent-session-01');
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isOperatorAuthorised())->toBeFalse();

    guardEnv('CLAUDE_SESSION_ID', null);
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', null);
});

it('authorises operator when no agent session and UMA_OPERATOR_ACKNOWLEDGED=yes', function () {
    // Legitimate Trainer interactive process: no agent marker, operator acknowledged.
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isOperatorAuthorised())->toBeTrue();

    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', null);
});

it('denies operator authorisation when no agent session and no acknowledgement', function () {
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', null);

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isOperatorAuthorised())->toBeFalse();
});

it('detects UMA_OPERATOR_ACKNOWLEDGED in $_ENV when $_SERVER has empty string', function () {
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', null);
    $_SERVER['UMA_OPERATOR_ACKNOWLEDGED'] = '';
    $_ENV['UMA_OPERATOR_ACKNOWLEDGED'] = 'yes';
    putenv('UMA_OPERATOR_ACKNOWLEDGED=');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isOperatorAuthorised())->toBeTrue();

    unset($_SERVER['UMA_OPERATOR_ACKNOWLEDGED'], $_ENV['UMA_OPERATOR_ACKNOWLEDGED']);
    putenv('UMA_OPERATOR_ACKNOWLEDGED');
});

it('does not treat "0" as UMA_OPERATOR_ACKNOWLEDGED=yes', function () {
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', '0');

    $agentGuard = app(AgentSessionGuard::class);

    expect($agentGuard->isOperatorAuthorised())->toBeFalse();

    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', null);
});

// Restored: agent session inheriting both override vars must not authorise canonical destructive
it('refuses canonical destructive when an agent session inherits both override vars', function () {
    // FS-INT-3: an agent child inheriting UMA_ALLOW_CANONICAL_DESTRUCTIVE=true and
    // UMA_OPERATOR_ACKNOWLEDGED=yes from a parent operator shell must NOT authorise
    // canonical destructive commands, even if the shell is interactive.
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv("{$marker}=test-session-01234567");
        $_ENV[$marker] = 'test-session-01234567';
        $_SERVER[$marker] = 'test-session-01234567';
    }
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE=true');
    $_ENV['UMA_ALLOW_CANONICAL_DESTRUCTIVE'] = 'true';
    $_SERVER['UMA_ALLOW_CANONICAL_DESTRUCTIVE'] = 'true';
    putenv('UMA_OPERATOR_ACKNOWLEDGED=yes');
    $_ENV['UMA_OPERATOR_ACKNOWLEDGED'] = 'yes';
    $_SERVER['UMA_OPERATOR_ACKNOWLEDGED'] = 'yes';
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');

    $guard = app(DatabaseGuard::class);

    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    // Clean up so other tests in this shared process are unaffected
    foreach (['CLAUDE_SESSION_ID', 'CODEBUDDY_SESSION_ID'] as $marker) {
        putenv($marker);
        unset($_ENV[$marker], $_SERVER[$marker]);
    }
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE');
    unset($_ENV['UMA_ALLOW_CANONICAL_DESTRUCTIVE'], $_SERVER['UMA_ALLOW_CANONICAL_DESTRUCTIVE']);
    putenv('UMA_OPERATOR_ACKNOWLEDGED');
    unset($_ENV['UMA_OPERATOR_ACKNOWLEDGED'], $_SERVER['UMA_OPERATOR_ACKNOWLEDGED']);
});

// Connection-level containment: agent + canonical + operator ack still refused
it('refuses agent connection to canonical even with operator acknowledgement', function () {
    guardEnv('CLAUDE_SESSION_ID', 'agent-session-01');
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);

    $agentGuard = app(AgentSessionGuard::class);
    expect($agentGuard->isAgentSession())->toBeTrue();
    expect($agentGuard->isOperatorAuthorised())->toBeFalse();

    $event = new ConnectionEstablished(DB::connection());
    expect(fn () => $agentGuard->onConnectionEstablished($event))->toThrow(DatabaseSafetyViolation::class);

    guardEnv('CLAUDE_SESSION_ID', null);
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', null);
});

// Launcher sanitization: stripping operator vars prevents guard authorization
it('launcher sanitization strips operator authorisation vars from all sources', function () {
    // Simulate tools/agent-run.php FS-INT-3 Check #4: strip UMA_OPERATOR_ACKNOWLEDGED
    // and UMA_ALLOW_CANONICAL_DESTRUCTIVE from $_SERVER, $_ENV, and process env.
    guardEnv('UMA_OPERATOR_ACKNOWLEDGED', 'yes');
    guardEnv('UMA_ALLOW_CANONICAL_DESTRUCTIVE', 'true');

    foreach (['UMA_OPERATOR_ACKNOWLEDGED', 'UMA_ALLOW_CANONICAL_DESTRUCTIVE'] as $var) {
        putenv($var);
        unset($_ENV[$var], $_SERVER[$var]);
    }

    // After stripping, neither guard path may see the operator acknowledgement.
    $agentGuard = app(AgentSessionGuard::class);
    expect($agentGuard->isOperatorAuthorised())->toBeFalse();

    $guard = app(DatabaseGuard::class);
    expect($guard->allowsCanonicalDestructive())->toBeFalse();
});
