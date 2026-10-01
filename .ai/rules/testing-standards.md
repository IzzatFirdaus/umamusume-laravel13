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

- Feature tests get their test case and database from one global binding in `tests/Pest.php`. Do not add a trait or a `uses()` call to an individual test file:

<code-snippet name="pest-global-binding" lang="php">
uses(TestCase::class, RefreshDatabase::class)
    ->in('Feature')
    ->beforeEach(function (): void {
        $this->withoutVite();
    });
</code-snippet>

- The binding is scoped to `Feature`; `tests/Unit` runs without a database.
- `withoutVite()` is applied globally to feature tests — do not repeat it per file.
- Authenticate a feature test with the `actingAsAdmin()` helper from `tests/Pest.php`.
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