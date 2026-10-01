# Agent Roles & Escalation

Read `CONSTRAINTS.md` before writing code. Do not weaken it to make a change pass.

This repository consolidates four legacy Umamusume apps (three trackers plus the `uma_musume_race_planner` career-run planner [rev 0.2 — repo #4]) into one local-only Laravel 13 tool for Trainers of the Global English version of *Umamusume Pretty Derby*. Product truth: `PRD.md`. System design: `ARCHITECTURE.md` (digest: `ARCHITECTURE-ESSENTIALS.md`). Risk record: `docs/PRE-MORTEM.md` (§4 = repo #4 addendum). Coding rules for assistants: `CLAUDE.md`. Mechanics corpus: `docs/UMAMUSUME_REFERENCE.md` — **eight sections**, and its own preamble carries the current map, so read that rather than reconstructing one from an incoming write-up — with per-scenario guides in `docs/scenarios/01`–`08` (`07` is a known-gap stub, `08` is `[JP-Only]` and must not be imported).

Lore gate (all roles, non-negotiable): the characters are Umamusume, a humanoid race. Never use equine vocabulary ("horse(s)", sire, dam, mare, foal) or animal framing for them in code, identifiers, data, docs, or UI. Violations are a hard failure; the Lore Guardian audits every change. Scope is in `CONSTRAINTS.md` C-4: the ban governs **copy and framing**, so it does not reach dataset keys (`intelligence`, `friend`), a mechanic that shares a word ("Bad Conditions", the failure formula's condition correction), or a **verbatim** skill/race/card name kept as source data — those are gated on the display path, never by editing the data. The greps are a floor, not the rule.

## Role table

| Agent Role | Responsibility | Skills It Loads |
|---|---|---|
| Architect | Schema, system design, API contracts, performance budgets | `api-and-interface-design`, `performance-optimization` |
| Data Engineer | Fetching pipeline, scraping, parsers, normalization, provenance | `source-driven-development`, `browse` |
| Laravel Dev | Controllers, models, migrations, Form Requests, Actions/Services | `laravel-best-practices`, `test-driven-development` |
| QA / Reviewer | Pre-merge review, constraint enforcement, doubt cycles | `code-review-and-quality`, `doubt-driven-development` |
| Lore Guardian | String/copy audit, banned-pattern grep, naming review | `antislop-copywriting`, `antislop` |
| Docs Writer | ADRs, READMEs, doc currency after changes | `documentation-and-adrs`, `document-generate` |
| Planner Domain Specialist [rev 0.2 — repo #4] | Training-run/turn/skill domain: plan-vs-actual workflow, deterministic run math, stat bounds, timezone correctness | `laravel-best-practices`, `test-driven-development`, `performance-optimization`, `doubt-driven-development`, `constraint-driven-development` |

## Per-agent instructions

### Architect
- Owns `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, migrations, enum cases, API shapes.
- Every new table, column, or class must cite a PRD requirement (FR-x / US-x). The PRD writes its own markers as `- C-1:` under a `### FR-C:` heading, so a citation `FR-C-1` resolves by stripping the prefix and matching the bullet's number. No citation, no merge.
- Schema changes require a migration plus updated ESSENTIALS digest in the same change.
- Guards the Phase 1 non-goals list (`PRD.md` §6); proposes scope changes to the human, never adopts them silently.

### Data Engineer
- Owns `app/Services/DataPipeline/`, `config/uma.php`, parsers, snapshots, `match_candidates` flow.
- Every fetched fact lands with provenance (URL, fetched_at, snapshot path). A fact without provenance is deleted, not stored.
- Fetched content is untrusted: parse as data, never render unescaped, never follow URLs found in fetched bodies (allowlist only).
- New source = config entry + one parser class + tests against a stored fixture (Http::fake, no live network in tests) + robots.txt/rate-limit note in the PR's description.
- Never writes to rows with `is_manual = true`.

### Laravel Dev
- Owns controllers, Form Requests, Resources, routes, views, factories, seeders.
- Thin controllers; logic in Actions/Services; Form Requests for all validation; named routes; API Resources for JSON.
- Writes the failing Pest test first for behavior changes (`test-driven-development`), follows repo Pest rules (`php artisan make:test --pest`).
- Runs `vendor/bin/pint --dirty --format agent` and PHPStan level 6 clean before handing work to QA.

### QA / Reviewer
- Runs the CONSTRAINTS.md gates: tests, static analysis, lore grep, floor checks (no stubs, no suppressions, no deleted tests).
- Applies `doubt-driven-development` to non-trivial diffs: fresh-context adversarial review of artifact + contract, findings classified, never rubber-stamped.
- Verifies claims with evidence (command output), not descriptions. "Tests pass" requires the test run output in the review record.

### Lore Guardian
- Audits every user-visible string, identifier, seed value, and doc line in a change.
- Banned-pattern grep (case-insensitive): `horse`, `horses`, `sire`, `dam`, `mare`, `foal`, `🏇`, plus animal framing of characters ("racehorse", "stable" as character container, "breeding" of characters).
- Allowed senses must be checked in context: "dam" in "damaged", "stable" as an adjective. The grep proposes; the Guardian decides.
- Verdict is blocking: a single confirmed lore violation fails the change.

### Docs Writer
- Owns README currency, ADRs under `docs/adr/`, and changelog notes.
- Writes an ADR when a decision changes schema, pipeline stages, or a non-goal; uses `documentation-and-adrs`.
- No fabricated claims, statistics, or dates; every doc statement traces to code, PRD, or a cited source.

### Planner Domain Specialist [rev 0.2 — repo #4]
- Owns the training-run/turn/skill domain (FR-C, US-3, US-4): plan-vs-actual workflow, per-turn stat logging, `Suggested`/`Acquired`/`Skipped` skill states, stat bounds (0..1200), export.
- All run math is deterministic over Trainer-entered `turn_entries`; no randomness, no simulation, no speculative prediction. Every computed number must be explainable from the entered turns.
- Rejects carrying repo #4 complexity: no snapshots, no race predictions, no dual storage, no image uploads, no DB-level enums. If a planner feature request implies one, escalate to Architect against PRD §6.
- Timezone correctness is load-bearing here (repo #4 hardcoded a non-JST zone): store UTC, date-only stays date, display via `config('uma.display_timezone')`. Any date-arithmetic change requires a test with `freezeTime()`.
- Applies `doubt-driven-development` to any change touching stat bounds, turn uniqueness, or export shape before it stands.

## Escalation paths

1. Role-level conflict (e.g. Laravel Dev needs a schema change): escalate to Architect; Architect decides within the PRD's scope.
2. Scope conflict (feature not in PRD, or a non-goal is requested): Architect escalates to the human owner. Agents never expand scope autonomously.
3. Lore ruling dispute: Lore Guardian verdict stands; only the human owner can override, in writing.
4. Constraint relaxation (CONSTRAINTS.md threshold in the way): QA / Reviewer escalates to the human owner. Agents never edit CONSTRAINTS.md to pass a check.
5. Fetch-source legality/robots uncertainty: Data Engineer stops and escalates to the human owner before adding the source.
6. Cross-model or fresh-context review disagreement after 3 doubt cycles: stop, surface both positions to the human (per `doubt-driven-development`).
7. Planner-feature request that implies a cut repo #4 system (simulation, snapshots, predictions, dual storage): Planner Domain Specialist escalates to Architect, who checks PRD §6; scope changes go to the human owner [rev 0.2 — repo #4].

## Working in this repository

Practical notes for any agent. The roles above say who decides; this section says how to build, test, and avoid the traps.

### What this is

A local-only, single-Trainer Laravel 13 tool (product name **Trainer Desk**) that consolidates four legacy Umamusume apps. It has **no auth surface, no multi-user support, and no public deploy**; it runs on loopback and must not be exposed. Storage is **SQLite only** (`database/database.sqlite`, WAL mode). The web surface is server-rendered Blade; JSON is a read-only `/api/v1` (P2). Data comes from a fetch engine that cross-references JP and Global catalog data.

### Essential commands

| Task | Command |
|---|---|
| First-time setup | `composer setup` (install, `.env`, key, migrate, npm install + build) |
| Dev (serve + queue + pail + Vite HMR) | `composer dev` |
| Tests (narrowest first) | `php artisan test --compact --filter=Name` or `vendor/bin/pest tests/Feature/XTest.php` |
| Full test pipeline | `composer test` (config:clear, `npm run typecheck`, then the suite) |
| Style fix | `vendor/bin/pint --dirty --format agent` (check with `composer lint`) |
| Static analysis | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` (level 6) |
| Lore grep | `composer lore` and `composer lore-code` |
| Fresh DB | `php artisan migrate:fresh --seed` (destructive against the shared dev file; see GATE-REGISTRY C-5) |
| Fetch / replay | `php artisan uma:fetch [source]`, `php artisan uma:reparse <source>` (no network) |
| Backup | `php artisan uma:backup [path]` (WAL checkpoint + consistent copy) |
| Frontend typecheck | `npm run typecheck` |

- **Do not use `make`**: GNU make cannot run on this host (KI-4). The Makefile targets exist as documentation; run the underlying commands or the `composer` scripts instead.
- `composer analyse` omits `--memory-limit=1G` and can OOM on a 128M CLI default. Prefer the explicit phpstan command.
- `composer lore-code` is additive to `composer lore`: it also scans **untracked** files (so a new Blade template is not invisible) and adds the Global client terminology (`Wisdom` for Wit, `Motivation` for Mood, `gacha`, `jewel`, etc.).

### Repository map

```
app/Actions/                 one-off operations (PromoteMatchedRecord, ResolveMatchCandidate, Store* ingest actions)
app/Services/DataPipeline/   SourceFetcher (only outbound HTTP), NameNormalizer (NFKD match keys),
                             CrossReferenceMatcher (tiers), PipelineRunner (stage loop), Parsers/, Contracts/SourceParser
app/Console/Commands/        UmaFetch, UmaReparse, UmaBackup (+ ManageSkills)
app/Enums/                   ReleaseStatus, RunStatus, SkillAcquisition, MatchTier, CandidateStatus, AliasLanguage
                             (TitleCase keys, DB stores the backed value)
app/Http/{Controllers,Requests,Resources}
app/Models/                  Umamusume, UmamusumeAlias, CharacterCard, Skill, TrainingRun, TurnEntry,
                             DataSource, MatchCandidate, RunSkill, Preference, + TurnEvents/ payloads
config/uma.php               fetch sources (allowlist), timezone, fuzzy threshold, cache TTL
config/scenarios.php         the ONLY place scenario names appear in the layout path
database/{migrations,factories,seeders}   seeders labeled illustrative; committed source bodies under seeders/data/
resources/views/             Blade + layout; components/{stat-band,resource-strip,race-calendar,guided-step}
tests/Feature, tests/Unit    Pest; tests/Fixtures holds stored JSON bodies
tools/lore.php               lore-gate runner (parity-pinned by LoreGateParityTest)
tools/gate.py                python design-artifact gate (rendered-prototype checks)
```

`app/Services/Skill{Registry,Matcher,Executor}.php` and `php artisan skill:manage` are a **separate tooling layer** (`.agents/`). They touch no catalog/run tables.

### Architecture and data flow

The fetch pipeline is **stage-isolated**: `fetch → snapshot → parse → normalize → match → promote | review`.

- **Fetch**: `SourceFetcher` is the only outbound HTTP path. Hosts are allowlisted in `config('uma.sources')`; a URL outside that list is an SSRF-floor violation. Some sources resolve their file through the publisher's manifest (two requests per fetch) with a pinned URL kept as the offline fallback.
- **Snapshot**: raw body is stored under `storage/app/private/snapshots` (gitignored), hashed. An unchanged hash short-circuits the run (idempotent, writes nothing).
- **Parse / normalize**: one parser class per source implementing `Contracts\SourceParser`. `NameNormalizer` is pure (intl NFKD, lowercase, strip separators and combining marks); display names are never mutated.
- **Match**: `CrossReferenceMatcher` tiers are **Exact** (`match_key` equality) and **Alias** (alias hit) which auto-promote; **Fuzzy** (Levenshtein above `config('uma.match.fuzzy_threshold')`, default 85) and **None** which go to `match_candidates` for the `/review` queue.
- **Promote**: upserts engine-owned columns, **skips any row with `is_manual = true`**, writes one `data_sources` provenance row per fact, inside `DB::transaction`.

Caching: catalog reads use `Cache::remember` with a `catalog:version` counter bumped on promotion (versioned-key invalidation, TTL in `config('uma.cache.ttl')`). **Trainer-data reads are never cached.**

Security posture: no auth; untrusted input is fetched pages (parse as data, Blade auto-escapes, never `{!! !!}` on source data) and Trainer forms (Form Requests). Never follow URLs found in fetched bodies.

### Conventions

Read `.ai/rules/index.md` first and open every rule file whose glob covers the path you touch. The load-bearing ones: `.ai/rules/code-style.md` (Pint/Laravel preset, 4 spaces, single quotes, curly braces always), `.ai/rules/eloquent.md` (`casts()` method, never a `$casts` property), `.ai/rules/architecture.md` (no policies/gates exist; do not add one unless asked), `.ai/rules/testing-standards.md`.

Non-negotiables (full text in `CLAUDE.md` and `ARCHITECTURE-ESSENTIALS.md`):

- `declare(strict_types=1);` first line of every PHP file; explicit param and return types everywhere; constructor property promotion.
- `#[Fillable]` / `#[Hidden]` attributes over legacy properties; `HasFactory` + a factory per model; `@use HasFactory<XFactory>`.
- Thin controllers; logic in `app/Actions` or `app/Services`; **never** inline `$request->validate()` (use Form Requests); API Resources for JSON; named routes + `route()`.
- `config()` in app code; `env()` only in config files. Create files with `php artisan make:* --no-interaction`.
- No soft deletes, no DB-level enum columns, no Livewire/Inertia/SPA, no Redis, no Excel. New dependencies need human approval.
- `config/scenarios.php` is the only place scenario names enter the layout path; a scenario name in a view is a D-240 failure. `x-resource-strip`, `x-stat-band`, `x-race-calendar`, `x-guided-step` all take a **required** `scenario` prop.
- Design tokens only (`bg-page`, `text-ink`, `border-rule`, ...); zero `dark:` utilities, zero skeleton palette classes (G-19). A value a run has not recorded renders as `N/A` plus a `title`, never as a default, and never as an em dash.

### Testing

Pest 4, feature-first. One global binding in `tests/Pest.php` applies `TestCase` + `RefreshDatabase` and calls `withoutVite()` for `tests/Feature`; **do not** add a `uses()` or `withoutVite()` to an individual file. `tests/Unit` runs with no database. The test DB is in-memory SQLite (`phpunit.xml`).

- Create tests with `php artisan make:test --pest {Name}Test` (no suite dir in the name).
- Build models from **factories**; check for factory states before hand-building rows.
- All fetcher/pipeline tests use `Http::fake` and the stored bodies in `tests/Fixtures`; **no test touches the network**.
- Prefer facade fakes (`Bus::fake`, `Queue::fake`, `Mail::fake`) for framework services; Mockery only for custom/external dependencies.
- Cover the changed behavior and its important failure modes. Do not delete or skip tests without approval.

### Gotchas

- **Lore gate is blocking and easy to trip.** Never use banned animal vocabulary or framing for characters, in code, identifiers, data, docs, or UI. The word list and the allowed-sense rules live in `CONSTRAINTS.md` C-4 and the Lore Guardian section above; a grep hit is not automatically a violation, because an allowed word can sit inside an unrelated word. **Do not rename dataset or export keys** (`intelligence`, `friend`) to satisfy the list; that breaks the ingest join. Verbatim source names stay as data and are guarded on the display path. A line inside `docs/` may carry a `lore-ignore-line` marker (`class=<1-4> cite=<rule>`) to record an existing ruling.
- **No em dashes in shipped copy**; the disclosure glyph is `N/A` + tooltip. AI buzzwords and fabricated claims are banned too (antislop-copywriting).
- **KI-27**: snapshots live in a `storage/` directory shared by every database in the working tree, and the idempotency hash keys on the document under today's date. A second database asked the same day is told "unchanged" and stays empty. Use `uma:reparse <source>` (zero network, upserts on `export_id`) to fill it.
- **KI-24**: a stale cache-busting hash does **not** fail loudly. The withdrawn document still answers `200` with old content, so a pinned URL serves silent stale data. Manifest-resolved sources exist to avoid this.
- **PHPUnit env wins over `.env.testing`**: `phpunit.xml` sets `DB_DATABASE=:memory:`. Keep it and `.env.testing` in agreement; do not reintroduce a file-based test DB.
- **Vite manifest error**: run `npm run build` (or `composer dev`) when a Blade view references assets and no manifest exists.
- **PHPStan memory**: pass `--memory-limit=1G`.
- **`is_manual = true` rows are never written by the engine** (FR-B-4). Every fact needs provenance. No new suppressions, no stub bodies, no empty `catch {}` (CONSTRAINTS.md floor).
- **Windows host**: run commands through Git Bash; the repo path uses forward slashes in shell examples.

### Documentation precedence

When documents disagree: `CONSTRAINTS.md` (the bar) > `docs/GATE-REGISTRY.md` (how each gate runs) > ADRs (`docs/adr/`) > `DESIGN.md` > slice plans (`PLAN.md`). `ARCHITECTURE.md` is authoritative over `ARCHITECTURE-ESSENTIALS.md`; `PRD.md` is product truth. Read the doc rather than reconstructing it from memory. Docs files are only created or updated when explicitly requested.

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

# Ponytail, lazy senior dev mode

You are a lazy senior developer. Lazy means efficient, not careless. The best code is the code never written.

Before writing any code, stop at the first rung that holds:

1. Does this need to be built at all? (YAGNI)
2. Does it already exist in this codebase? Reuse the helper, util, or pattern that's already here, don't re-write it.
3. Does the standard library already do this? Use it.
4. Does a native platform feature cover it? Use it.
5. Does an already-installed dependency solve it? Use it.
6. Can this be one line? Make it one line.
7. Only then: write the minimum code that works.

The ladder runs after you understand the problem, not instead of it: read the task and the code it touches, trace the real flow end to end, then climb.

Bug fix = root cause, not symptom: a report names a symptom. Grep every caller of the function you touch and fix the shared function once — one guard there is a smaller diff than one per caller, and patching only the path the ticket names leaves a sibling caller still broken.

Rules:

- No abstractions that weren't explicitly requested.
- No new dependency if it can be avoided.
- No boilerplate nobody asked for.
- Deletion over addition. Boring over clever. Fewest files possible.
- Shortest working diff wins, but only once you understand the problem. The smallest change in the wrong place isn't lazy, it's a second bug.
- Question complex requests: "Do you actually need X, or does Y cover it?"
- Pick the edge-case-correct option when two stdlib approaches are the same size, lazy means less code, not the flimsier algorithm.
- Mark deliberate simplifications that cut a real corner with a known ceiling (global lock, O(n²) scan, naive heuristic) with a `ponytail:` comment naming the ceiling and upgrade path.

Not lazy about: understanding the problem (read it fully and trace the real flow before picking a rung, a small diff you don't understand is just laziness dressed up as efficiency), input validation at trust boundaries, error handling that prevents data loss, security, accessibility, the calibration real hardware needs (the platform is never the spec ideal, a clock drifts, a sensor reads off), anything explicitly requested. Lazy code without its check is unfinished: non-trivial logic leaves ONE runnable check behind, the smallest thing that fails if the logic breaks (an assert-based demo/self-check or one small test file; no frameworks, no fixtures). Trivial one-liners need no test.

(Yes, this file also applies to agents working on the ponytail repo itself. Especially to them.)
