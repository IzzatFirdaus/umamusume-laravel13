<?php

use Illuminate\Contracts\Console\Kernel;

echo "=== Path Resolution Analysis ===\n\n";

// Key test: what does database_path('database.sqlite') return?
// And does realpath() ever get a chance to resolve against CWD?

// The config uses: env('DB_DATABASE', database_path('database.sqlite'))
// database_path('database.sqlite') returns: base_path() . '/database/database.sqlite'
// base_path() is ALWAYS set to dirname(__DIR__) of bootstrap/app.php = the repo root
// So the config value is ALWAYS absolute, regardless of CWD

echo "In Laravel 10+:\n";
echo "  database_path('database.sqlite') = base_path('database/database.sqlite')\n";
echo "  base_path() is hardcoded via dirname(__DIR__) in bootstrap/app.php\n";
echo "  This always returns an ABSOLUTE path to the repo's database directory\n\n";

// BUT the config value is passed to SQLiteConnector::parseDatabasePath()
// which does:
//  1. realpath($path)  -- if path is absolute, this resolves to itself if it exists
//  2. fallback: realpath(base_path($path))

// For an absolute path like D:/Projects/.../database/database.sqlite:
// realpath() returns the canonicalized absolute path if file exists
// or FALSE if file does NOT exist

// For a relative path like 'database/database.sqlite':
// realpath() resolves against CWD
// If file exists at CWD/database/database.sqlite, it returns that path (CWD-relative!)
// Otherwise falls back to base_path()

echo "SQLiteConnector::parseDatabasePath() resolution:\n";
echo "1. realpath($path) -- checks relative to CWD\n";
echo "2. realpath(base_path($path)) -- checks relative to app base path\n\n";

echo "SCENARIO A: DB_DATABASE not set in .env (legacy app)\n";
echo "  Config value: D:/Projects/uma_musume_race_planner/database/database.sqlite (ABSOLUTE from database_path())\n";
echo "  This is ALWAYS the legacy app's own database, regardless of CWD\n";
echo "  Result: SAFE - cannot redirect via CWD\n\n";

echo "SCENARIO B: DB_DATABASE set to RELATIVE 'database/database.sqlite'\n";
echo "  Config value: database/database.sqlite (RELATIVE)\n";
echo "  realpath() resolves against CWD\n";
echo "  IF CWD is Trainer Desk, and database/database.sqlite EXISTS there\n";
echo "  -> Resolves to Trainer Desk's database! RISK!\n";
echo "  IF file does NOT exist, falls back to base_path() -> legacy repo's DB\n\n";

echo "SCENARIO C: DB_DATABASE set to ABSOLUTE path\n";
echo "  An agent could explicitly set DB_DATABASE=D:/Projects/umamusume-laravel13/database/database.sqlite\n";
echo "  This would target Trainer Desk's DB\n";
echo "  BUT this requires an explicit, deliberate action\n\n";

// Verify scenario A
echo "=== Verifying Scenario A ===\n";
chdir('D:/Projects/umamusume-laravel13');
require 'D:/Projects/uma_musume_race_planner/vendor/autoload.php';
$app = require_once 'D:/Projects/uma_musume_race_planner/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$dbConfig = config('database.connections.sqlite.database');
echo 'Config DB_DATABASE value: '.$dbConfig."\n";
echo 'Starts with drive letter? '.(preg_match('#^[A-Za-z]:[\\\\/]#', $dbConfig) ? 'YES (absolute)' : 'NO (relative)')."\n";
echo 'realpath: '.(realpath($dbConfig) ?: 'FALSE')."\n";

$trainerDeskDb = 'D:/Projects/umamusume-laravel13/database/database.sqlite';
echo 'Matches Trainer Desk canonical DB? '.(realpath($dbConfig) === realpath($trainerDeskDb) ? 'YES' : 'NO - safe')."\n";
