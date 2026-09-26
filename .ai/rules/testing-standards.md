# Testing Standards

This project uses **Pest 4** as its test runner. All tests live under `tests/Unit/` and `tests/Feature/`.

## Framework & Discovery

- **Test runner:** Pest 4 (`vendor/bin/pest` or `php artisan test`).
- **Test files:** Create with `php artisan make:test --pest {Name}Test`. Do **not** include the suite directory in the name — use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- **Layout:** Feature tests exercise HTTP endpoints and request flows. Unit tests exercise pure logic in isolation.

## Running Tests

- Run the narrowest set that covers the change: `php artisan test --filter=testName` or `vendor/bin/pest tests/Feature/SomeFeatureTest.php`.
- Rerun tests after each change to them.
- After feature tests pass, ask the user to run the full suite with `php artisan test --compact`.

## Database & Transactions

- Use the `RefreshDatabase` trait in feature tests to seed and migrate a fresh testing database:

<code-snippet name="refresh-database" lang="php">
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\TestCases\TestCase;

class SomeFeatureTest extends TestCase
{
    use RefreshDatabase;
}
</code-snippet>

- The test environment uses an in-memory SQLite database (see `phpunit.xml`).
- Always create models via **factories** rather than direct `new` instantiation.
- Use `$this->faker` or `fake()` for test data generation.

## Mocking Strategy

- Prefer **facade fakes** over Mockery for framework services: `Bus::fake()`, `Mail::fake()`, `Notification::fake()`, `Queue::fake()`.
- Use Mockery for external third-party HTTP clients and custom service interfaces:

<code-snippet name="mockery-example" lang="php">
$mock = Mockery::mock(SomeService::class);
$mock->shouldReceive('process')->once()->withArgs([1])->andReturnTrue();
$this->app->instance(SomeService::class, $mock);
</code-snippet>

- "No Mockery" next to facade fakes is not a choice against Mockery — they double different things. Facade fakes isolate framework services; Mockery isolates custom dependencies.

## Assertions

- Assert response status codes, JSON structure, and database state.
- Use `$this->assertDatabaseCount()` and `$this->assertDatabaseMissing()` for state assertions.
- For JSON responses, assert on the decoded structure rather than exact string matching.

## Coverage Rules

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Do not delete tests or test files without approval — they are part of the application.