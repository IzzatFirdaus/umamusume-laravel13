# AGENTS.md

Operational contract for coding agents working in this repository. It answers: how to
orient, what may not be touched, what must be run, and what "done" means here. It is not
a product spec, an architecture document, or a tutorial. Those live in the files named in
§3 and are linked rather than restated.

Read `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" before writing code. It is a pointer stub now; the binding quality
bar (the C-1..C-9 table, the Floor list, the C-4 lore rules, and the hand-off verification
sequence) lives in `docs/research-scratch/GOVERNANCE.md`, section "CONSTRAINTS.md (root
quality bar, C-1 to C-9)". Do not weaken a threshold to make a change pass.

## 1. What this repository is

**Trainer Desk**, a local-only, single-Trainer Laravel 13 tool for the Global English
release of *Umamusume Pretty Derby*. It consolidates four legacy applications: three
trackers plus the `uma_musume_race_planner` career-run planner (rev 0.2, repo #4).

- **No auth surface, no multi-user support, no public deploy.** It runs on loopback and
  must not be exposed (`PRD.md` §6.10). No hosting, cloud, or deployment path exists.
- **SQLite only** (`database/database.sqlite`, WAL + `busy_timeout`). The framework
  `users` table and `User` model are unused defaults.
- **Web surface is Inertia + Vue 3** (`inertiajs/inertia-laravel`, `@inertiajs/vue3`, TypeScript,
  Tailwind CSS v4, Vite), with the career set and the scenario panels as Vue components under
  `resources/js/`. Blade survives only as the `resources/views/app.blade.php` shell and
  `resources/views/errors/`; there are no Blade view components. JSON is a read-only `/api/v1`
  surface (P2, three controllers).
  *Dated correction 2026-10-08 (documentation-sync pass): the "51 single-file components" figure is
  superseded — the tree then carried 58 single-file components under `resources/js/components/` and 54
  page files under `resources/js/pages/`, after the Phase D–E career, Veterans and Database sets
  landed. *Dated correction 2026-10-12: the tree now carries 63 components and 48 pages. Counts like
  these age the moment a slice lands, so this is a snapshot note, not a number to keep in step; what
  holds is the shape: the career set and the scenario panels are Vue, and
  `resources/views/components/` is empty since slice B1.*
- **Data arrives through a stage-isolated fetch engine** that cross-references JP and
  Global catalog sources, snapshots each body, and promotes engine-owned facts with
  provenance.
- **No CI.** There is no `.github/workflows/`, so every gate in §9 runs locally, by an
  agent or the human owner, and its output is the only evidence.

## 2. Instruction scope and precedence

Layers, highest first. A lower layer never overrides a higher one; where a lower layer
disagrees, the higher one wins and the lower one is stale.

| Layer                       | Where                                                                                               | Authority                                                                                    |
| --------------------------- | --------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| Human owner instruction     | the task itself                                                                                     | Highest, but it cannot relax the quality bar without a written owner ruling (escalation 4)   |
| Quality bar                 | `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" | Never weakened to pass a check                                                               |
| Gate registry               | `GOVERNANCE.md` §GATE-REGISTRY.md                                                                   | How each gate runs, its evidence, its allowed exceptions                                     |
| Binding decisions           | `docs/adr/0001`-`0025`, `SUPERSEDED-*`                                                              | Accepted ADRs bind; `Proposed` ones do not                                                   |
| Product truth               | `PRD.md`                                                                                            | Users, requirements, §6 non-goals                                                            |
| System design               | `ARCHITECTURE.md` over `ARCHITECTURE-ESSENTIALS.md`                                                 | The digest loses to the full text                                                            |
| Screen behavior             | `SCREEN_SPEC.md` §8 authority table                                                                 | Per-screen states and workflows                                                              |
| Visual system               | `DESIGN.md` (+ folded corpus `docs/research-scratch/DESIGN-CORPUS.md`)                              | Tokens, components, motion                                                                   |
| Path-scoped rules           | `.ai/rules/index.md` -> code-style.md, eloquent.md, architecture.md, testing-standards.md   | Conventions for the globs they cover                                                         |
| Coding rules                | `CLAUDE.md`                                                                                         | Assistant coding rules, lore gate detail                                                     |
| Slice plans and records     | `docs/research-scratch/PROCESS-PLANS.md`, SLICE-RECORDS.md, `PLANS-AND-BRIEFS.md`                 | Local to a slice; cannot override a gate                                                     |
| Tooling-injected guidance   | the `<laravel-boost-guidelines>
=== .ai/custom/domain rules ===

# Custom Domain Guidelines

This file records the high-level domain terminology, application boundaries, and authorization patterns specific to this repository. It is loaded automatically by agents working on any part of the codebase.

## Application Identity

- **Project name:** Laravel 13 skeleton application.
- **PHP version:** 8.3+ (targeting 8.4+ features where appropriate).
- **Frontend stack:** Vite + Tailwind CSS v4 + vanilla JavaScript (no Livewire, no Inertia, no Flux UI installed).
- **Testing stack:** Pest 4 with `pest-plugin-laravel`.

## Core Domain Boundaries

- `app/Models/` — Eloquent models. Currently contains the `User` model extending `Illuminate\Foundation\Auth\User`.
- `app/Http/` — Controllers, form requests, middleware, and API resources.
- `app/Actions/` — (intended) One-off business operations invoked by controllers or jobs.
- `app/Services/` — (intended) Shared domain services wrapping external integrations or complex logic.
- `app/Console/` — Artisan commands and scheduled tasks.
- `app/Providers/` — Service providers, including the route service provider and any package providers.
- `database/migrations/` — Schema definitions using anonymous migration classes (Laravel 13 default).

## Authorization Patterns

- Use **Laravel policies** at `app/Policies/` for object-level authorization.
- Gate-based checks are acceptable for global, non-model rules.
- Always resolve the `User` model via route model binding or explicit `Auth::user()`; never trust client-supplied identifiers without a policy check.

## API Conventions

- API endpoints return JSON via Eloquent **API Resources** at `app/Http/Resources/`.
- Use **API versioning** for any public-facing endpoints (e.g., `api/v1/`).
- Wrap collections with `ResourceCollection`; wrap single models with the resource class.

## Events & Side Effects

- Dispatch events from **Action classes** or **Service classes**, never from controllers or models directly.
- Use `ShouldQueue` for jobs that interact with external services.
- Listeners should be registered in the `EventServiceProvider` at `app/Providers/EventServiceProvider.php`.

## Data Integrity

- Mass assignment is governed by the `#[Fillable]` attribute on each model.
- Soft deletes are not used by default; if added, ensure the `SoftDeletes` trait and corresponding migration column are added together.
- Use database transactions (`DB::transaction`) when multiple writes must be atomic.

=== .ai/framework/core rules ===

<code-snippet name="strict-types" lang="php">
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

```text
public function index(): JsonResponse
{
    return response()->json([
        'data' => User::select(['id', 'name', 'email'])->get(),
    ]);
}
```

}
</code-snippet>

## PHP Baseline

- Every PHP file **must** begin with `<?php` followed by `declare(strict_types=1);`.
- Use **PHP 8.4+** features: readonly classes, `#[Override]`, intersection types where applicable, and constructor property promotion.
- All method parameters and return types must be explicitly typed: `function store(Request $request): JsonResponse`.
- Use **TitleCase** for enum cases: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer **PHPDoc** blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array-shape type definitions in PHPDoc blocks, e.g. `@return array{ id: int, name: string }`.

## Controllers & Actions

- Controllers should be thin; delegate business logic to **Action classes** in `app/Actions/` or **Service classes** in `app/Services/`.
- Action classes expose a `handle(...)` method (or `__invoke`) and are invoked from controllers or jobs.
- Use **Form Request classes** in `app/Http/Requests/` for all input validation. Never inline `validate()` in controllers.
- Return JSON responses via `response()->json()` for APIs; use `redirect()->back()` or named routes for web flows.

## Models & Eloquent

- Models live in `app/Models/`. Use the **attributes** (`#[Fillable]`, `#[Hidden]`) over legacy `$fillable`/`$hidden` properties.
- Use the `HasFactory` trait and a factory at `database/factories/{Model}Factory.php`.
- Define `casts()` as a method returning an array — this is the Laravel 13 standard.
- Use **lazy eager loading** (`loadMissing`) to avoid N+1 queries in endpoints.
- API responses must use **Eloquent API Resources** at `app/Http/Resources/` rather than manual `toArray()` mappings.

## Routing

- Use **named routes** and the `route()` helper for all URL generation.
- Group routes by middleware: `api` prefix + `api` middleware for API routes; `web` middleware for browser routes.
- Keep `routes/web.php` and `routes/api.php` thin — delegate to route model bindings and controller methods.

## Configuration & Environment

- Read configuration with `config('app.name')` or `php artisan config:show app.name`. Never hardcode env values.
- Use `env()` only inside configuration files; in application code use `config()` so values are cached.

## Frontend

- Use **Vite** with `@tailwindcss/vite` and **Tailwind CSS v4**. Assets are referenced via `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- Blade templates live in `resources/views/`. Use the `layout` Blade component for shared wrappers.
- For Tailwind-specific guidance, activate the `tailwindcss-development` skill.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
