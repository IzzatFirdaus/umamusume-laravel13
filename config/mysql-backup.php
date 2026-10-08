<?php

declare(strict_types=1);

return [
    'host' => env('TRAINER_DESK_MYSQL_HOST', '127.0.0.1'),
    'port' => env('TRAINER_DESK_MYSQL_PORT', '3306'),
    'database' => env('TRAINER_DESK_MYSQL_DB', 'trainer_desk_backup'),
    'username' => env('TRAINER_DESK_MYSQL_USER', 'root'),
    'password' => env('TRAINER_DESK_MYSQL_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
