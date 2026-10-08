<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DatabaseSafety\DatabaseGuard;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Prints the database identity this process actually resolved, and exits
 * non-zero when the identity is one the guard would refuse.
 *
 * The reason this exists as a command rather than a check inside the harness
 * that starts the browser server: the harness has to verify the database the
 * application itself will open, and the only process that can report that is
 * the application, run with the same environment the server was given. A check
 * written in the harness would be reasoning about the path a different process
 * is about to open, which is exactly the inference that failed on 2026-10-08.
 *
 * Exit codes: 0 identity is safe, 1 it resolves to the canonical development
 * database while an isolated role is declared.
 */
class UmaDbGuard extends Command
{
    protected $signature = 'uma:db-guard';

    protected $description = 'Report the database this process resolves, and refuse an unsafe identity (NFR-5)';

    public function __construct(private readonly DatabaseGuard $guard)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $role = $this->guard->role();
        $expected = $_SERVER['UMA_EXPECTED_DATABASE'] ?? $_ENV['UMA_EXPECTED_DATABASE'] ?? getenv('UMA_EXPECTED_DATABASE');
        $canonical = $this->guard->canonicalPath();
        $configured = $this->guard->configuredPath();
        $resolved = $this->guard->resolvedPath();
        $canonicalTarget = $this->guard->targetsCanonical();

        $this->line('role          : '.($role ?? '(none - the application itself)'));
        $this->line("canonical     : {$canonical}");
        $this->line("DB_DATABASE   : {$configured}");
        $this->line('resolved      : '.($this->guard->isFileBacked() ? $resolved : $resolved.' (not a file)'));

        if (is_string($expected) && $expected !== '') {
            $this->line("expected      : {$expected}");
        }

        $this->line('targets canonical: '.($canonicalTarget ? 'yes' : 'no'));

        if ($canonicalTarget && $this->guard->isIsolatedRole()) {
            $this->error("An isolated role ({$role}) resolves to the canonical development database.");
            $this->line('Pass an absolute scratch path to DB_DATABASE and retry.');

            return self::FAILURE;
        }

        try {
            $this->guard->assertIsolatedContext('uma:db-guard');
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Database identity is safe.');

        return self::SUCCESS;
    }
}
