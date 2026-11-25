<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Throwable;

/**
 * DatabaseManager that never leaves a refused connection in the cache.
 *
 * `Illuminate\Database\DatabaseManager::connection()` caches the connection in
 * `$this->connections[$name]` *before* firing `ConnectionEstablished`. When an
 * `ConnectionEstablished` listener (for example `AgentSessionGuard`) refuses the
 * connection by throwing, the connection is already cached, so the next
 * `connection()` call in the same process returns it without re-dispatching the
 * event — silently bypassing the database-safety guard.
 *
 * This subclass overrides `connection()` to purge a connection from the cache
 * whenever its establishment (including the `ConnectionEstablished` event)
 * throws, so a refused connection is never reused by a later call in the same
 * process. It is bound as the container's `db` service by
 * `DatabaseGuardServiceProvider` and requires no framework (vendor) edits.
 */
class SafeDatabaseManager extends DatabaseManager
{
    /**
     * Get a database connection instance, purging any connection whose
     * establishment was refused by a `ConnectionEstablished` listener.
     *
     * @param  \UnitEnum|string|null  $name
     * @return Connection
     */
    public function connection($name = null)
    {
        try {
            return parent::connection($name);
        } catch (Throwable $e) {
            $name = $name ?? $this->getDefaultConnection();

            // A refused connection must not linger in `$this->connections`, or a
            // later `connection()` call would reuse it without re-dispatching
            // `ConnectionEstablished` and silently bypassing the guard.
            $this->purge($name);

            throw $e;
        }
    }
}
