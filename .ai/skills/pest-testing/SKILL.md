---
name: pest-testing
description: "Trigger when the user asks to write, run, or refactor tests. Covers Pest 4 syntax, feature and unit test structure, database testing with RefreshDatabase, factory usage, and facade/Mockery mocking strategies."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: testing
---

# Pest Testing

Write and run tests in this project's Pest 4 setup.

## Trigger Criteria

User requests: "write a test", "add a test", "test this", "run the tests", "refactor tests", "cover this with tests".

## Setup

- **Runner:** Pest 4 (`vendor/bin/pest` or `php artisan test`).
- **Directories:** `tests/Unit/` for unit tests, `tests/Feature/` for feature tests.
- **Create tests:** `php artisan make:test --pest {Name}Test`. Do **not** include the suite directory in the name.

## Feature Test Skeleton

<code-snippet name="feature-test" lang="php">
<?php

declare(strict_types=1);

use App\Models\Scenario;

it('lists the scenario catalog', function (): void {
    Scenario::factory()->count(3)->create();

    $this->get(route('database.scenarios'))->assertOk();
});
</code-snippet>

## Database Testing

- Feature tests get `TestCase` and `RefreshDatabase` from the global binding in `tests/Pest.php`; never add `uses()` or `RefreshDatabase` to an individual file.
- Create models via **factories**: `Scenario::factory()->count(3)->create()`.
- Use `$this->faker` or `fake()` for data generation.
- Assert state with `assertDatabaseCount()`, `assertDatabaseMissing()`.

## Mocking

- Prefer **facade fakes**: `Bus::fake()`, `Mail::fake()`, `Notification::fake()`, `Queue::fake()`.
- Use **Mockery** for external clients and custom service interfaces:

<code-snippet name="mockery" lang="php">
$mock = Mockery::mock(SomeService::class);
$mock->shouldReceive('process')->once()->withArgs([1])->andReturnTrue();
$this->app->instance(SomeService::class, $mock);
</code-snippet>

## Assertions

- Response status: `$response->assertOk()`, `$response->assertNotFound()`.
- JSON structure: assert the decoded shape with `assertJsonPath()` or `assertJsonStructure()`.
- Database state: `$this->assertDatabaseCount('scenarios', 3)`.

## Running

- Narrow scope: `php artisan test --filter="lists the scenario catalog"`.
- Checkpoint suite: `php artisan test --compact`, used at the Level 3 points in `AGENTS.md` §9, not after every edit.
