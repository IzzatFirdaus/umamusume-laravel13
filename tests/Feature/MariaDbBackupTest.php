<?php

declare(strict_types=1);

use App\Actions\MigrateToMariaDbAction;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\AssertionFailedError;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.driver', 'sqlite');
    Config::set('database.connections.sqlite.database', database_path('database.sqlite'));
});

it('ensures SQLite remains the default connection', function (): void {
    expect(config('database.default'))->toBe('sqlite');
});

it('does not change DB_CONNECTION on execution', function (): void {
    $initialDefault = config('database.default');

    $action = new MigrateToMariaDbAction;

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('handle');
    $method->setAccessible(true);

    try {
        $action->handle();
    } catch (Exception $e) {
    }

    expect(config('database.default'))->toBe($initialDefault);
});

it('requires mysql_backup database configuration', function (): void {
    Config::set('database.connections.mysql_backup', null);

    $action = new MigrateToMariaDbAction;

    try {
        $action->handle();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('Backup database connection');

        return;
    }

    throw new AssertionFailedError('Expected RuntimeException was not thrown');
});

it('validates target database name', function (): void {
    Config::set('database.connections.mysql_backup', [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'wrong_database_name',
        'username' => 'root',
        'password' => '',
    ]);

    $action = new MigrateToMariaDbAction;

    try {
        $action->handle();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain("must be 'trainer_desk_backup'");

        return;
    }

    throw new AssertionFailedError('Expected RuntimeException was not thrown');
});

it('validates target driver is mysql or mariadb', function (): void {
    Config::set('database.connections.mysql_backup', [
        'driver' => 'pgsql',
        'host' => '127.0.0.1',
        'port' => '5432',
        'database' => 'trainer_desk_backup',
        'username' => 'root',
        'password' => '',
    ]);

    $action = new MigrateToMariaDbAction;

    try {
        $action->handle();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('Target driver must be mysql or mariadb');

        return;
    }

    throw new AssertionFailedError('Expected RuntimeException was not thrown');
});

it('configures mysql_backup from mysql-backup.php', function (): void {
    $backupConfig = config('mysql-backup');

    expect($backupConfig)->not->toBeNull();
    expect($backupConfig['database'])->toBe('trainer_desk_backup');
    expect($backupConfig['host'])->toBe('127.0.0.1');
});
