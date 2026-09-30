# Claude Code

Project instructions for Claude Code sessions in this repository.

Read `CONSTRAINTS.md` before writing code. Do not weaken it to make a change pass.

## Lore Rules (top priority, non-negotiable)

The characters of *Umamusume Pretty Derby* are Umamusume: a humanoid race of girls. They are never animals.

1. Never use "horse" or "horses" (or sire, dam, mare, foal, stable-as-noun, breeding-of-characters, 🏇) to refer to characters. Use "Umamusume" (capital U) at the start of a sentence or as the proper race name, "umamusume" (lowercase) mid-sentence. Singular and plural are identical: one umamusume, many umamusume.
2. This applies everywhere: documentation, code comments, variable/class/table/column names, enum cases, seed and fixture data, commit messages, and UI strings.
3. A violation is a hard failure. Before finalizing any artifact, run the banned-pattern grep and fix every hit (see Banned Patterns below).

## Banned Patterns / Anti-Patterns

Blocked on sight (QA / Lore Guardian grep these):

- Lore: `horse`, `horses`, `sire`, `dam`, `mare`, `foal`, `🏇`, and animal framing of characters. Check context: "dam" in "damaged" and "stable" as an adjective are fine; the Guardian decides.
- Naming: any equine term in an identifier, table, column, route, config key, or test name.
- Over-engineering (Pre-Mortem §1): no auth/sessions surface, no SPA framework, no breeding/pairing engine, no EAV attribute tables, no Excel dependency, no Redis, no event calendar in Phase 1. Every new class cites a PRD FR-x/US-x or it does not ship.
- Architecture: no business logic in controllers or models (use Actions/Services); no inline `$request->validate()` in controllers (use Form Requests); no `toArray()` hand-mapping in API responses (use API Resources); no raw `{!! !!}` on fetched/source data; no hard-coded fetch URLs (config allowlist only); no writing to `is_manual = true` rows from the engine.
- Data: no fact stored without provenance; no matching on display strings (match on normalized `match_key`); no sentinel dates for "unreleased" (use nullable date + `release_status`).
- Code comments (antislop-code): no comments that restate the code, no `// Step 1/2/3` narration, no banner separators, no decorative emoji, no vague TODOs. Comments explain WHY (constraint, workaround, non-obvious behavior) or are omitted. PHPDoc over inline; array-shape types in PHPDoc.
- Copy (antislop-copywriting): no em dash (—) in prose, no AI buzzwords (seamless, powerful, revolutionary, cutting-edge, effortless, ultimate), no fabricated stats/testimonials/claims, no generic CTAs.
- Suppressions/stubs (CONSTRAINTS.md floor): no new `@phpstan-ignore`, `@ts-ignore`, `eslint-disable`, `# noqa`, no `throw new \Exception('not implemented')`, no empty `catch {}`, no skipped/deleted tests without a reason in the commit message.

## Planner Domain Rules [rev 0.2 — repo #4]

The training-run planner (runs, turns, skills; PRD FR-C) inherits these rules from the `uma_musume_race_planner` consolidation:

1. No speculative simulation. No race simulators, randomness, prediction engines, or "estimated outcome" math. Repo #4's manual race-prediction grades and race-day snapshots are cut (PRD §6.11); do not reintroduce them.
2. Do not copy legacy complexity. No dual storage modes (SQLite is the only store), no localStorage-authored runs, no image uploads, no DB-level enum columns, no soft deletes, no Livewire.
3. Explicit timezone handling. Store UTC; parse JP-source datetimes as `Asia/Tokyo` and record `source_timezone`; date-only values stay `date`, never datetimes; display via `config('uma.display_timezone')`. Never hardcode a locale timezone in `config/app.php` (repo #4 did; it shifted JP date logic).
4. Deterministic calculations. Every derived number (deltas, totals, per-turn progression) is a pure function of Trainer-entered `turn_entries`. Same input, same output, no hidden state.
5. Explainable outputs. Any computed value shown in the UI must be traceable to the turns that produced it; no unexplained "recommended" numbers.
6. Stat bounds live in one place: `StoreTurnEntryRequest` (0..1200 per stat, turn >= 1). Do not scatter magic bounds through services or views.
7. Umamusume terminology everywhere in the planner domain too: characters are Umamusume, never equine vocabulary. Banned terms are a hard failure (Lore Rules above apply to run notes placeholders, seed data, export headers, and UI labels).

## Laravel 13 Coding Standards

Full detail in `laravel-best-practices` and the Boost guidelines below. Non-negotiables:

- PHP 8.5, `declare(strict_types=1);` at the top of every PHP file; explicit param and return types on every method.
- Eloquent models: `#[Fillable]`/`#[Hidden]` attributes (not legacy properties); `casts()` as a method; enums cast by class name; `HasFactory` + a factory per model; `loadMissing`/`with` against N+1.
- Thin controllers; logic in `app/Actions/` (one-off, `handle`/`__invoke`) or `app/Services/` (shared/external); dispatch events only from Actions/Services.
- Form Requests in `app/Http/Requests/` for all input validation; API Resources in `app/Http/Resources/` for all JSON; API under `/api/v1`.
- Named routes + `route()` helper; anonymous migration classes; `DB::transaction` for atomic multi-writes.
- `config()` in app code, `env()` only in config files. Read config with `php artisan config:show`.
- Create files with `php artisan make:* --no-interaction`.
- SQLite only (WAL + busy_timeout); no MySQL/PG-specific DDL.
- After any PHP edit: `vendor/bin/pint --dirty --format agent`. Keep PHPStan level 6 clean.
- Verify installed versions before relying on a package API (`composer show <pkg>`); never assume a version.

## Architectural Patterns

Follow `ARCHITECTURE-ESSENTIALS.md` (the token-efficient digest) for the schema, fetch pipeline, API contract, caching, and conventions. `ARCHITECTURE.md` is the authoritative full text and wins on any disagreement. Fetch pipeline is stage-isolated (fetch → snapshot → parse → normalize → match → promote|review); matching tiers are Exact/Alias (auto-promote) vs Fuzzy/None (review queue); all fetched content is untrusted and snapshotted before parsing.

## Testing Expectation

Pest 4, feature-first (`test-driven-development`, `testing-best-practices`):

- Write the failing test first for any behavior or logic change; then implement to green.
- Create tests with `php artisan make:test --pest {name}` (no suite dir in the name).
- All fetcher/pipeline tests use `Http::fake` and stored fixtures. No test touches the network.
- Use factories for models; check factory states before hand-building data.
- Cover the changed behavior and its important failure modes (unicode normalization, is_manual protection, match tiers, validation rejection). Do not add tests beyond them.
- Run the narrowest set that covers the change (`php artisan test --compact --filter=...`); ask the human to run the full suite after.
- Do not delete or skip tests without approval.

## Workflow Notes

- Load the matching skill before acting (`using-agent-skills`); the domain skills in `**/skills/**` are mandatory for their domains.
- Documentation files only when explicitly requested; no verification scripts when tests cover it.
- Do not change dependencies or create new base directories without approval.
- Product name is **Trainer Desk** (owner decision 2026-09-27, PRD OQ-1 closed). Do not invent names for other undecided things (sources, palette proposals); mark them `Undecided — owner input needed`.
- Visual work follows `DESIGN.md` (root): light base palette with dark opt-in via preference resolution (ADR-0006), tactical-athletic system, lore-sensitive iconography rules (no equestrian/animal motifs; stopwatch, chart, grid, track-line motifs only). Disclosure glyph is `N/A` + tooltip, never an em dash, in shipped copy.

- Read `AGENTS.md` first: it carries the shared Laravel Boost guidelines this repository follows. Follow the conventions and rules it records.
- The `impeccable` design skill covers UI design, redesign, critique, audit, and polish work in this project. It is installed at the project level (`.agents/skills/impeccable`, `.cursor/skills/impeccable`, `.grok/skills/impeccable`) and at the user level (`~/.claude/skills/impeccable`). Load and follow its instructions for any UI work.
- `CLAUDE.md` and `.claude/` are gitignored by design: agent guideline and skill files are machine-local in this repository.

===

<laravel-boost-guidelines>
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
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::select(['id', 'name', 'email'])->get(),
        ]);
    }
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

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

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

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

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

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

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

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

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

</laravel-boost-guidelines>
