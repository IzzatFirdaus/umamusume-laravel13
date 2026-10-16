<?php

declare(strict_types=1);

/*
 * `uma:backup` is the safety net `AGENTS.md` §10/§11 name before a destructive migration, so the
 * cases below pin the contract `App\Actions\BackupDatabase` documents: a consistent single-file
 * snapshot, a refusal to overwrite an existing destination, and a named error rather than a bare
 * failure when there is no database file to read. Every path is under the process temp directory,
 * never `database/database.sqlite`.
 */

beforeEach(function (): void {
    $this->scratchDir = sys_get_temp_dir().'/uma-backup-test-'.uniqid();
    mkdir($this->scratchDir, 0777, true);
});

afterEach(function (): void {
    if (isset($this->scratchDir) && is_dir($this->scratchDir)) {
        foreach (glob($this->scratchDir.'/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->scratchDir);
    }
});

it('writes a snapshot whose copy opens, passes integrity_check and carries the source rows', function (): void {
    $source = $this->scratchDir.'/source.sqlite';
    $writer = new PDO('sqlite:'.$source);
    $writer->exec('create table probe (a integer not null)');
    $writer->exec('insert into probe (a) values (7)');

    config(['database.connections.sqlite.database' => $source]);

    $destination = $this->scratchDir.'/copy.sqlite';

    $this->artisan('uma:backup', ['destination' => $destination])->assertExitCode(0);

    expect(is_file($destination))->toBeTrue();

    $copy = new PDO('sqlite:'.$destination);
    expect($copy->query('pragma integrity_check')->fetchColumn())->toBe('ok');
    expect((int) $copy->query('select count(*) from probe')->fetchColumn())->toBe(1);
});

it('refuses a second snapshot onto the same destination and leaves the first one intact', function (): void {
    $source = $this->scratchDir.'/source.sqlite';
    (new PDO('sqlite:'.$source))->exec('create table probe (a integer not null)');

    config(['database.connections.sqlite.database' => $source]);

    $destination = $this->scratchDir.'/copy.sqlite';

    $this->artisan('uma:backup', ['destination' => $destination])->assertExitCode(0);

    $sizeBefore = filesize($destination);

    $this->artisan('uma:backup', ['destination' => $destination])
        ->expectsOutputToContain('A file already exists at '.$destination)
        ->assertExitCode(1);

    expect(filesize($destination))->toBe($sizeBefore);
});

it('fails with the named error when there is no database file to back up', function (): void {
    config(['database.connections.sqlite.database' => $this->scratchDir.'/missing.sqlite']);

    $this->artisan('uma:backup', ['destination' => $this->scratchDir.'/out.sqlite'])
        ->expectsOutputToContain('No SQLite database file to back up')
        ->assertExitCode(1);

    expect(is_file($this->scratchDir.'/out.sqlite'))->toBeFalse();
});
