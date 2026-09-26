---
name: running-tests
description: "Trigger when the user asks to run tests, check test status, or debug failing tests. Covers Pest 4 invocation, filter selection, and test isolation."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: testing
---

# Running Tests

Run and debug tests in this project's Pest 4 setup.

## Trigger Criteria

User requests: "run the tests", "run this test", "debug failing test", "check test status", "run the test suite".

## Commands

- **Full suite:** `php artisan test --compact`
- **Single file:** `vendor/bin/pest tests/Feature/SomeFeatureTest.php`
- **Filter by test name:** `php artisan test --filter=test_something`
- **Direct Pest:** `vendor/bin/pest`

## Strategy

- Run the narrowest set that covers the change. Pass a file path or `--filter=testName`.
- Rerun a test after each change to it.
- After feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

## Debugging

- Use `--filter` to isolate a failing test.
- Use `dump()` or `dumpNow()` for output during a test run (Pest supports both).
- For database state issues, ensure `RefreshDatabase` is present in the feature test.
- Check `storage/logs/laravel.log` for uncaught exceptions during tests.

## Configuration

- Test environment is configured in `phpunit.xml` and `.env.testing`.
- The test database is in-memory SQLite; no external services required.
- `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array` by default.