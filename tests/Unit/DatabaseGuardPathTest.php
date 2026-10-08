<?php

declare(strict_types=1);

use App\Services\DatabaseSafety\DatabaseGuard;

/*
 * `DatabaseGuard::isAbsolute()` has no other coverage, which is why a Windows regression shipped in it:
 * the character class was written `[\\/]`, which compiles to a class holding only `/`, so every drive
 * path with backslashes (`D:\...`) read as relative. The browser harness passes its scratch database as
 * an absolute Windows path, so `assertAbsolutePath()` refused it at application boot and the whole
 * Playwright suite stopped before its first assertion.
 *
 * Pure predicate, no config and no database: this is the cheapest place to pin both separators.
 */

it('reads an absolute path as absolute', function (string $path): void {
    expect((new DatabaseGuard)->isAbsolute($path))->toBeTrue();
})->with([
    'a Windows drive path with backslashes' => 'D:\Projects\umamusume-laravel13\database\browser-scratch.sqlite',
    'a Windows drive path with forward slashes' => 'D:/Projects/umamusume-laravel13/database/browser-scratch.sqlite',
    'a POSIX path' => '/var/db/scratch.sqlite',
]);

it('reads a relative path as relative', function (string $path): void {
    expect((new DatabaseGuard)->isAbsolute($path))->toBeFalse();
})->with([
    'a bare file name' => 'database.sqlite',
    'a relative directory' => 'database/scratch.sqlite',
    'a relative Windows path' => 'database\scratch.sqlite',
]);
