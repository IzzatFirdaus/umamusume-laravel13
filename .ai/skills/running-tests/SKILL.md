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

- **Checkpoint suite:** `php artisan test --compact` (Level 3 only, per `AGENTS.md` §9)
- **Single file:** `vendor/bin/pest tests/Feature/SomeFeatureTest.php`
- **Filter by name:** `php artisan test --filter=Name`
- **Direct Pest:** `vendor/bin/pest`

## Strategy

- Run the narrowest set that covers the change. Pass a file path or `--filter=Name`.
- Rerun a test after each change to it.
- Escalate through the Level 1, 2 and 3 runs in `AGENTS.md` §9. The agent runs the required gate and reports the observed output; it does not ask the owner to run a routine verification step.

## Debugging

- Use `--filter` to isolate a failing test.
- Use `dump()` or `dumpNow()` for output during a test run (Pest supports both).
- Feature tests inherit `TestCase` and `RefreshDatabase` from the global binding in `tests/Pest.php`; do not add either to the individual file.
- Check `storage/logs/laravel.log` for uncaught exceptions during tests.

## Configuration

- Test environment is configured in `phpunit.xml` and `.env.testing`.
- The test database is in-memory SQLite; no external services required.
- `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array` by default.
