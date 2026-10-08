<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\MigrateToMariaDbAction;
use Illuminate\Console\Command;

class MigrateToMariaDb extends Command
{
    protected $signature = 'db:backup:mysql {--verify : Verify backup integrity after migration}';

    protected $description = 'Migrate data from SQLite to MariaDB backup database';

    public function handle(): int
    {
        $this->info('Starting SQLite to MariaDB backup migration...');

        try {
            $this->info('Source: SQLite');
            $this->info('Target: MariaDB trainer_desk_backup');
            $this->newLine();

            $action = new MigrateToMariaDbAction;

            $results = $action->handle();

            $this->info('Migration completed successfully!');
            $this->newLine();
            $this->info('Results:');
            $this->newLine();

            $totalRows = 0;
            foreach ($results as $table => $result) {
                if ($result['status'] === 'success') {
                    $this->line("  <info>{$table}</info>: {$result['rows']} rows");
                    $totalRows += $result['rows'];
                } else {
                    $this->line("  <comment>{$table}</comment>: {$result['reason']}");
                }
            }

            $this->newLine();
            $this->info("Total rows migrated: {$totalRows}");

            if ($this->option('verify')) {
                $this->newLine();
                $this->info('Verifying backup integrity...');

                $verification = MigrateToMariaDbAction::verifyIntegrity();

                $allMatch = true;

                foreach ($verification as $table => $result) {
                    if ($result['match']) {
                        $this->line("  <info>{$table}</info>: source={$result['source']}, target={$result['target']} ✓");
                    } else {
                        $allMatch = false;
                        $this->line("  <error>{$table}</info>: source={$result['source']}, target={$result['target']} ✗");
                    }
                }

                if ($allMatch) {
                    $this->newLine();
                    $this->info('✓ All tables verified successfully!');

                    return self::SUCCESS;
                }

                $this->newLine();
                $this->error('✗ Backup verification failed - row counts do not match');

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\RuntimeException $e) {
            $this->error('Migration failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
