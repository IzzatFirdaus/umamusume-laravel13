<?php

declare(strict_types=1);

// Pail needs pcntl (https://github.com/laravel/pail), which no Windows PHP build
// ships, and `composer dev` runs concurrently with --kill-others: a logs pane that
// exits takes serve, queue, and Vite down with it. So where pcntl is missing the
// pane reports the fallback and then blocks; Ctrl+C still reaches every pane.
if (! function_exists('pcntl_fork')) {
    echo 'logs: pail needs the [pcntl] extension, unavailable on this host;'.PHP_EOL
        .'logs: watch storage/logs/laravel.log instead.'.PHP_EOL;

    while (true) {
        sleep(3600);
    }
}

passthru('php artisan pail --timeout=0', $exitCode);

exit($exitCode);
