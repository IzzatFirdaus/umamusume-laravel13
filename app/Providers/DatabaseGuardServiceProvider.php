<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Makes the database safety guard live in both directions an isolated workflow
 * reaches the database.
 *
 * `boot()` covers every process that boots the application - a request through
 * `php artisan serve`, a `php artisan` invocation, a test - because each of
 * those must have declared an isolated role and named a database before it
 * touched the file.
 *
 * `Artisan::before` covers the commands that destroy tables, and it is the
 * check that would have stopped the 2026-10-08 incident at the exact moment it
 * began: `migrate:fresh --seed` resolving to the canonical development file.
 * It is registered separately from `boot()` because those commands are also
 * where the damage is done, and the caller of one is usually a fresh shell that
 * declared no role at all - `boot()` would have let it through.
 *
 * Thrown from a `before` handler, the refusal surfaces as a non-zero artisan
 * exit with the message on stderr and the command body never runs. That is the
 * whole point: no partial `migrate:fresh`, which is what left the 2026-10-08
 * file as a 0-byte main database beside a 1,849,912-byte write-ahead log.
 */
class DatabaseGuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseGuard::class);
    }

    public function boot(): void
    {
        $guard = $this->app->make(DatabaseGuard::class);

        $guard->assertIsolatedContext('Application boot');

        Event::listen(CommandStarting::class, function (CommandStarting $event) use ($guard): void {
            $guard->assertCommandSafe($event->command, $event->input);
        });
    }
}
