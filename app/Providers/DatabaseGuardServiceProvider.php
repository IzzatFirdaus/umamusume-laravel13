<?php

declare(strict_types=1);

namespace App\Providers;

use App\Database\SafeDatabaseManager;
use App\Services\DatabaseSafety\AgentSessionGuard;
use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Events\ConnectionEstablished;
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
        $this->app->singleton(AgentSessionGuard::class);

        // SafeDatabaseManager never leaves a refused connection in the cache.
        // Bound as the container's `db` service so no framework (vendor) edit
        // is required for the cache-reuse fix.
        $this->app->singleton('db', function ($app) {
            return new SafeDatabaseManager(
                $app,
                $app->make(ConnectionFactory::class)
            );
        });
    }

    public function boot(): void
    {
        $guard = $this->app->make(DatabaseGuard::class);
        $this->app->make(AgentSessionGuard::class);

        Event::listen(CommandStarting::class, function (CommandStarting $event) use ($guard): void {
            $guard->assertCommandSafe($event->command, $event->input);
        });

        // Fail-safe boundary for agent-looking processes: a session marker in the
        // environment with a canonical target is refused before the first
        // statement. Without this registration the guard exists only as a class
        // the tests call directly; a real agent process would not be intercepted.
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            $this->app->make(AgentSessionGuard::class)->onConnectionEstablished($event);
        });
    }
}
