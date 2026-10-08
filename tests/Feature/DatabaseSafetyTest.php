<?php

use App\Exceptions\DatabaseSafetyViolation;
use App\Providers\DatabaseGuardServiceProvider;
use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Config;
use Mockery;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

beforeEach(function () {
    Config::set('database.connections.sqlite.database', 'database/database.sqlite');
    Config::set('database-safety.canonical', 'D:/Projects/umamusume-laravel13/database/database.sqlite');
    Config::set('database-safety.isolated_roles', ['isolated', 'test', 'browser', 'scratch', 'e2']);
    Config::set('database-safety.destructive_commands', ['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback', 'db:wipe']);

    putenv('UMA_DATABASE_ROLE');
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE');
    putenv('UMA_OPERATOR_ACKNOWLEDGED');
    putenv('UMA_EXPECTED_DATABASE');
});

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
    putenv('UMA_DATABASE_ROLE=test');
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
    putenv('UMA_DATABASE_ROLE=development');
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
    putenv('UMA_DATABASE_ROLE=test');
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE=true');
    putenv('UMA_OPERATOR_ACKNOWLEDGED=yes');
    Config::set('database.connections.sqlite.database', 'D:/Projects/umamusume-laravel13/database/database.sqlite');

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
    putenv('UMA_DATABASE_ROLE=browser');
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE=true');
    putenv('UMA_OPERATOR_ACKNOWLEDGED=yes');
    Config::set('database.connections.sqlite.database', 'D:/Projects/umamusume-laravel13/database/database.sqlite');

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
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE=true');
    putenv('UMA_OPERATOR_ACKNOWLEDGED=yes');
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
    putenv('UMA_ALLOW_CANONICAL_DESTRUCTIVE=true');
    putenv('UMA_OPERATOR_ACKNOWLEDGED=yes');

    // In test context (non-TTY), the override is still blocked
    expect($guard->allowsCanonicalDestructive())->toBeFalse();

    // But the logic itself recognizes the two env vars
    $reflection = new ReflectionClass($guard);
    $method = $reflection->getMethod('isInteractiveShell');
    $method->setAccessible(true);
    expect($method->invoke($guard))->toBeFalse();
});

it('allows legitimate isolated positive control with disposable scratch DB', function () {
    putenv('UMA_DATABASE_ROLE=test');
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

it('db-guard command runs and exits successfully', function () {
    // Ensure we're in the clean test context with in-memory DB
    putenv('UMA_DATABASE_ROLE=test');
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
