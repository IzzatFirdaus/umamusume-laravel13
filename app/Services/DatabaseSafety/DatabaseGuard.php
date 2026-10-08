<?php

declare(strict_types=1);

namespace App\Services\DatabaseSafety;

use App\Exceptions\DatabaseSafetyViolation;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Answers two questions and nothing else: which file will this process write,
 * and is that permitted here.
 *
 * It never rewrites a path, never migrates, never deletes. It reports and it
 * refuses. Everything downstream - the boot assertion, the artisan hook, the
 * `uma:db-guard` command, the browser harness - calls into here, so there is one
 * definition of "the canonical file" and one implementation of "is this that
 * file".
 *
 * The refusal set comes from the 2026-10-08 incident, where the canonical
 * development database was truncated to 0 bytes and could not be rebuilt: the
 * surviving write-ahead log held 96 of the 856 pages the final committed
 * database had. See `config/database-safety.php` for the record.
 *
 * Role, expected identity and the destructive override are read from the
 * environment rather than from config on purpose. `php artisan config:cache`
 * bakes resolved values into `bootstrap/cache/config.php`, and a baked `false`
 * override is exactly the way a guard gets silently disarmed while still
 * looking configured.
 */
class DatabaseGuard
{
    /**
     * The role this process declared, or null when it claims to be the app itself.
     */
    public function role(): ?string
    {
        $role = $this->environment('UMA_DATABASE_ROLE');

        return is_string($role) && $role !== '' ? strtolower($role) : null;
    }

    public function isIsolatedRole(): bool
    {
        $role = $this->role();

        return $role !== null && in_array($role, (array) config('database-safety.isolated_roles'), true);
    }

    /**
     * The file a Trainer's real data lives in, from the single declaration in
     * `config/database-safety.php`.
     */
    public function canonicalPath(): string
    {
        $path = (string) config('database-safety.canonical');

        return $path === '' ? base_path('database/database.sqlite') : $path;
    }

    public function connectionName(?string $connectionName = null): string
    {
        return $connectionName ?? (string) config('database.default');
    }

    public function isSqlite(?string $connectionName = null): bool
    {
        return (string) config("database.connections.{$this->connectionName($connectionName)}.driver") === 'sqlite';
    }

    public function configuredPath(?string $connectionName = null): string
    {
        return (string) config("database.connections.{$this->connectionName($connectionName)}.database");
    }

    /**
     * Whether the configured value names a physical file. `:memory:` and `file:`
     * URIs have no path to compare, so every path-based rule stands aside for
     * them - which is what keeps an in-memory test database trivially safe.
     */
    public function isFileBacked(?string $connectionName = null): bool
    {
        $path = $this->configuredPath($connectionName);

        return $path !== ':memory:'
            && ! str_starts_with($path, 'file:')
            && ! str_contains($path, 'mode=memory');
    }

    /**
     * The absolute file the connection will open, resolved the way
     * `SQLiteConnector::parseDatabasePath()` resolves it: existing paths against
     * the working directory first, then the application base; in-memory and
     * `file:` URIs pass through unchanged.
     *
     * A not-yet-created scratch file is normalised as far as the anchors go and
     * returned unresolved, because a workflow creating its own file is the
     * expected case rather than an error.
     */
    public function resolvedPath(?string $connectionName = null): string
    {
        if (! $this->isFileBacked($connectionName)) {
            return $this->configuredPath($connectionName);
        }

        return $this->normalize($this->configuredPath($connectionName));
    }

    /**
     * Whether this connection would open the canonical development database.
     */
    public function targetsCanonical(?string $connectionName = null): bool
    {
        if (! $this->isSqlite($connectionName) || ! $this->isFileBacked($connectionName)) {
            return false;
        }

        return $this->sameFile($this->resolvedPath($connectionName), $this->canonicalPath());
    }

    /**
     * File identity that survives neither a rename nor a junction, because both
     * sides are resolved through `realpath` before comparison and compared
     * case-insensitively on a case-insensitive filesystem.
     */
    public function sameFile(string $a, string $b): bool
    {
        return strcasecmp($this->normalize($a), $this->normalize($b)) === 0;
    }

    public function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\/]/', $path) === 1;
    }

    public function allowsCanonicalDestructive(): bool
    {
        $override = $this->environment('UMA_ALLOW_CANONICAL_DESTRUCTIVE');

        if (! is_string($override) || ! in_array(strtolower($override), ['1', 'true', 'yes', 'on'], true)) {
            return false;
        }

        $operatorAck = $this->environment('UMA_OPERATOR_ACKNOWLEDGED');

        return is_string($operatorAck)
            && strtolower($operatorAck) === 'yes'
            && $this->isInteractiveShell();
    }

    private function isInteractiveShell(): bool
    {
        return function_exists('posix_isatty')
            ? posix_isatty(STDIN)
            : false;
    }

    /**
     * Every rule an isolated role has to satisfy, in the order a failure is
     * most useful to read.
     */
    public function assertIsolatedContext(string $context): void
    {
        if (! $this->isIsolatedRole()) {
            return;
        }

        $this->assertNoConfigurationCache($context);
        $this->assertAbsolutePath($context);
        $this->assertNotCanonical($context);
        $this->assertMatchesExpected($context);
    }

    /**
     * Refuse the commands that destroy tables when they would open the canonical
     * file and no explicit override was set. Checked regardless of role, because
     * the caller of a destructive command is usually a fresh shell that declared
     * nothing at all.
     *
     * @param  InputInterface|array<string, mixed>  $input
     */
    public function assertCommandSafe(string $commandName, InputInterface|array $input): void
    {
        /** @var array<string, mixed> $inputArray */
        $inputArray = is_array($input) ? $input : [];
        if (! in_array($commandName, (array) config('database-safety.destructive_commands'), true)) {
            return;
        }

        $connectionName = null;

        if ($input instanceof InputInterface && $input->hasParameterOption('--database')) {
            $connectionName = $input->getParameterOption('--database');
        } elseif (isset($inputArray['--database']) && $inputArray['--database'] !== false) {
            $connectionName = $inputArray['--database'];
        }

        $connectionName = is_string($connectionName) && $connectionName !== '' ? $connectionName : null;

        if (! $this->isSqlite($connectionName) || ! $this->isFileBacked($connectionName)) {
            return;
        }

        if (! $this->targetsCanonical($connectionName)) {
            return;
        }

        if ($this->allowsCanonicalDestructive()) {
            return;
        }

        throw new DatabaseSafetyViolation(
            "{$commandName} would drop every table in the canonical development database "
            ."{$this->canonicalPath()}. Point it at a scratch database by setting DB_DATABASE to an "
            .'absolute path, or set UMA_ALLOW_CANONICAL_DESTRUCTIVE=true together with '
            .'UMA_OPERATOR_ACKNOWLEDGED=yes (interactive shell only) to accept the loss explicitly.'
        );
    }

    /**
     * A cached config freezes the database path that was resolved when it was
     * written, so a harness override passed as an environment variable would be
     * ignored and the process would silently open the canonical file.
     */
    public function assertNoConfigurationCache(string $context): void
    {
        if (! $this->isIsolatedRole() || ! file_exists(base_path('bootstrap/cache/config.php'))) {
            return;
        }

        throw new DatabaseSafetyViolation(
            "{$context}: bootstrap/cache/config.php exists, so the cached database path is winning over "
            ."the environment a harness passes in, and this process opens {$this->resolvedPath()} instead "
            .'of what it was started with. Run `php artisan config:clear` and retry.'
        );
    }

    /**
     * A relative path makes the caller's working directory the database selector,
     * which is the mechanism that turned a missed prefix into a deleted file.
     */
    public function assertAbsolutePath(string $context, ?string $connectionName = null): void
    {
        if (! $this->isIsolatedRole() || ! $this->isFileBacked($connectionName)) {
            return;
        }

        $path = $this->configuredPath($connectionName);

        if ($this->isAbsolute($path)) {
            return;
        }

        throw new DatabaseSafetyViolation(
            "{$context}: DB_DATABASE={$path} is not an absolute path, so which file this process writes "
            .'depends on the working directory the caller happened to run from. Set DB_DATABASE to '
            .'an absolute scratch path.'
        );
    }

    public function assertNotCanonical(string $context, ?string $connectionName = null): void
    {
        if (! $this->isIsolatedRole() || ! $this->targetsCanonical($connectionName)) {
            return;
        }

        throw new DatabaseSafetyViolation(
            "{$context}: UMA_DATABASE_ROLE={$this->role()} but DB_DATABASE resolves to the canonical "
            ."development database {$this->canonicalPath()}. An isolated workflow is not permitted to "
            .'write the real data; point DB_DATABASE at an absolute scratch path.'
        );
    }

    /**
     * A positive identity check: when a workflow was started with the database it
     * intends, it says so, and this process proves it opened that one.
     */
    public function assertMatchesExpected(string $context, ?string $connectionName = null): void
    {
        $expected = $this->environment('UMA_EXPECTED_DATABASE');

        if ($expected === false || ! $this->isFileBacked($connectionName)) {
            return;
        }

        $actual = $this->resolvedPath($connectionName);

        if ($this->sameFile($actual, $expected)) {
            return;
        }

        throw new DatabaseSafetyViolation(
            "{$context}: UMA_EXPECTED_DATABASE={$expected} but DB_DATABASE resolves to {$actual}. The "
            .'database this process actually opened does not match the identity it was started with, so '
            .'it refuses rather than guessing which of the two was meant.'
        );
    }

    /**
     * Resolve a database path to a comparable absolute form.
     */
    private function normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        if (($real = realpath($path)) !== false) {
            return str_replace('\\', '/', $real);
        }

        if ($this->isAbsolute($path)) {
            return $path;
        }

        $anchored = realpath(base_path($path));

        if ($anchored !== false) {
            return str_replace('\\', '/', $anchored);
        }

        return rtrim(str_replace('\\', '/', base_path()), '/').'/'.$path;
    }

    /**
     * Read an environment variable the way the guard needs it: from the process
     * environment, not from the resolved config, so caching cannot disarm a check.
     */
    private function environment(string $name): string|false
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        return is_string($value) ? $value : false;
    }
}
