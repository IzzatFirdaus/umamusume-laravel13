<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Test case without database transactions for tests that make HTTP requests
 * and need to see the database state created in the test.
 */
abstract class NoTransactionTestCase extends PHPUnitTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = require_once __DIR__.'/../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();

        // Run migrations
        $this->artisan('migrate:fresh');
    }

    protected function tearDown(): void
    {
        // Drop all tables
        DB::statement('PRAGMA foreign_keys = OFF');
        foreach (DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'") as $table) {
            Schema::dropIfExists($table->name);
        }
        DB::statement('PRAGMA foreign_keys = ON');

        parent::tearDown();
    }

    protected function artisan(string $command, array $parameters = []): int
    {
        return Artisan::call($command, $parameters);
    }

    public function get(string $uri, array $headers = []): TestResponse
    {
        return $this->call('GET', $uri, [], [], [], $headers);
    }

    public function call(string $method, string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = []): TestResponse
    {
        $server = array_merge([
            'HTTP_HOST' => 'localhost',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ], $server);

        return $this->app->handle(
            $request = Request::create($uri, $method, $parameters, $cookies, $files, $server)
        );
    }

    protected function withoutVite(): void
    {
        config(['vite.manifest_path' => null]);
    }
}
