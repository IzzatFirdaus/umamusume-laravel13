# Claude Code instructions (Trainer Desk)

Read and follow `AGENTS.md` before doing repository work. It is the repository-wide
operational contract: precedence, roles and escalation, the lore gate, the Floor, coding
conventions, the pipeline rules, the validation matrix, the command reference, and the
definition of done. This file does not repeat it. It records only what is specific to
Claude Code in this repository.

If this file and `AGENTS.md` disagree, `AGENTS.md` wins. If either contradicts the binding
bar in `docs/research-scratch/GOVERNANCE.md`, section "CONSTRAINTS.md (root quality bar,
C-1 to C-9)", the bar wins and the conflict gets reported, not silently resolved.
(`CONSTRAINTS.md` was folded into that master in `1e84b0c`; the root file no longer exists.)

One correction to the previous version of this file: `CLAUDE.md` is **tracked** in git and
is yours to edit. `.claude/` **is** gitignored (`.gitignore:48`), so the hooks, skills, and
agent files under it are local configuration, not repository truth.

## 1. Instruction precedence, for Claude specifically

1. The task you were given.
2. `AGENTS.md`, then `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" for the bar.
3. Accepted ADRs (`docs/adr/`); a `Proposed` ADR is not binding.
4. `CLAUDE.md` (this file) for Claude-specific practice.
5. `.ai/rules/index.md` -> the rule files whose globs cover the paths in scope.
6. Laravel Boost's injected framework guidance.

The root quality bar (C-1..C-9) is a floor no task can relax; per `AGENTS.md` §2
("Instruction scope and precedence") it is "never weakened to pass a check."

Boost's injected block asks for Laravel policies, `Auth::user()`, Laravel Cloud deployment,
and an active `User` model. None of that exists here: there is no authorization layer by
design (`PRD.md` §5 "Non-Functional Requirements" NFR-1, and `ARCHITECTURE.md` §8 "Security
Model"), and deployment is a non-goal (`PRD.md` §6 "Non-Goals (eliminated legacy features)",
item 10). Do not add a policy, a gate, a `User` flow, or a deploy path because generic
guidance suggests it.

This repository's `AGENTS.md` is not read natively by Claude Code, because a root `CLAUDE.md` satisfies the project-instructions check, so the line-3 instruction to read `AGENTS.md` is the mechanism by which the contract is reached.

The discretionary read is deliberate: the `@AGENTS.md` import was priced at 14,043 tokens per session for `AGENTS.md` at 679 lines and declined, and the trigger to revisit is an actual miss, which is a session acting without the contract recorded in `KNOWN-ISSUES.md`.

## 2. Required reading before a substantial change

| Change                                         | Read first                                                                                                                                                                                                                |
| ---------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Anything                                       | `AGENTS.md`, then the `.ai/rules` files covering the paths in scope                                                                                                                                                           |
| UI, layout, copy, tokens                       | `DESIGN.md` (visual system) and `SCREEN_SPEC.md` §4 "Screen Specifications" and §8 "Source of Truth" (screen behavior)                                                                                                    |
| Pipeline, parsers, sources, provenance         | `ARCHITECTURE.md` §5 "Data-Fetching Engine (JP ↔ Global cross-reference)" and §6 "Integration Pattern (scraping → normalization → storage → cache)", `config/uma.php`, the parser's sibling in `app/Services/DataPipeline/Parsers/` |
| Schema                                         | `ARCHITECTURE.md` §3 "Database Schema & Eloquent Models", `ARCHITECTURE-ESSENTIALS.md` (the digest travels with the migration), the PRD requirement the column serves, and any ADR in the area                      |
| Planner domain (runs, turns, skills, export)   | `PRD.md` §4 "Functional Requirements" FR-C "Training-run domain (Trainer's own data)", `docs/adr/0015-per-scenario-stat-ceiling.md` (stat ceilings; amended by ADR-0025), `app/Services/ScenarioCaps.php`              |
| Lore, terminology, displayed vocabulary        | `AGENTS.md` §5 "Non-negotiables", `lang/en/uma.php`, `tools/lore.php`                                                                                                                                                     |
| Mechanics or roster facts                      | `docs/UMAMUSUME_REFERENCE.md` (eight sections; re-derive its table rather than quoting prose counts)                                                                                                                        |
| Onboarding, setup, commands                    | `README.md`                                                                                                                                                                                                               |

`PRODUCT.md` is a generated summary for design and agent context. Where it and `PRD.md`
disagree, `PRD.md` wins; do not edit `PRODUCT.md` by hand (the plugin rewrites it).

## 3. Tooling already wired for you here

**Laravel Boost MCP** (`php artisan boost:mcp`, configured in `.mcp.json`):

- `application-info` once per session, so package versions in context are real rather than
  remembered. Read versions from this tool, not from memory or documentation.
- `database-schema` before writing a migration or a model; `database-query` instead of
  `tinker` for read-only SQL.
- `search-docs` before relying on framework behavior that depends on the installed version.
  Pass `packages` when you know which ones matter.
- `browser-logs`, `last-error`, and `read-log-entries` after a UI change, before claiming a
  page works.
- `get-absolute-url` before handing a URL to the user.

**Browser tooling**: the `playwright-cli` skill in `.claude/skills/` drives a real browser for
rendered checks, and the repo's own Playwright suite is `@playwright/test` with
`playwright.config.ts` and specs under `tests/browser/` (`npm run test:browser`,
session-private per `AGENTS.md` §9 "Testing and validation"). There is **no** Playwright MCP
server: `.mcp.json` registers only `filesystem`, `git`, `docs`, `laravel-boost`, and
`gitkraken`.

**Hooks**: `.claude/settings.local.json` registers an impeccable design check after every
`Edit`/`Write` and a deeper pass at `Stop`. The check is active on this host: the script
resolves to a user-level installation outside the repository, so it runs here but not for
anyone who clones. Both are local, gitignored config. Do not edit that file as part of a
feature change.

**Skills** in `.claude/skills/`: `laravel-best-practices`, `pest-testing`,
`testing-best-practices`, `running-tests`, `tailwindcss-development`, `creating-models`,
`infer-conventions`, `playwright-cli`, `impeccable`. `deploying-to-cloud` is installed and
irrelevant: deployment is cut (`PRD.md` §6 "Non-Goals (eliminated legacy features)", item 10).

The `impeccable` design skill is present at `.claude/skills/impeccable` (host-local,
gitignored), `.cursor/skills/impeccable`, `.grok/skills/impeccable`,
`.github/skills/impeccable`, and user level `~/.agents/skills/impeccable`. The previous
version of this file named `.agents/` and `~/.claude/`, neither of which holds it.

**Skill automation** (`.agentrules`, `app/Services/Skill{Registry,Matcher,Executor}.php`,
`php artisan skill:manage`) is a separate declarative layer documented in
`docs/SKILL_AUTOMATION.md`. It reads `.agents/skills.json` (gitignored) and touches no
catalog or run table. Do not wire it into domain code.

## 4. Claude work pattern

1. Read `AGENTS.md`, then the `.ai/rules` files whose globs cover the files in scope
   (`AGENTS.md` §7 "Coding conventions").
2. Read the owning document for the class of change (§2 "Required reading before a substantial
   change"). Do not work from a summary of a document you have not opened.
3. Inspect the real flow end to end before editing. For a bug, see `AGENTS.md` §19 "Working
   style".
4. Plan the smallest coherent change. Name the files it will touch.
5. Edit only those files, following sibling files for structure, naming, and idiom.
6. Run the narrow validation that covers the change (§6 "Testing and validation").
7. Run the broader gates when the change is cross-cutting (`AGENTS.md` §9 "Testing and
   validation").
8. Read `git diff` before reporting. Unrelated hunks mean something went wrong.
9. Update the document that owns the information you changed (§13 "Documentation
   maintenance").
10. Report using the format in §15 "Completion reporting", including what was not run.

Trivial copy or single-token work does not need steps 2 to 5 in full. It does still need the
lore gate and a diff review.

## 5. Search before creating

Reuse is the default in this repository; a second implementation of something that already
ships is a defect.

- Committed components in `resources/js/components/` before writing markup;
  `resources/views/components/` does not exist, and a Blade view component added there is going
  backwards.
- `lang/en/uma.php` before writing a displayed string or an enum label.
- `config/scenarios.php` before naming a scenario anywhere near the layout path.
- `ScenarioCaps` before doing per-stat ceiling arithmetic.
- The Form Request that already owns a validation boundary (`app/Http/Requests/`).
- `app/Actions` and `app/Services` before adding a class.
- `tests/Fixtures/` bodies and `Http::fake` before writing a pipeline test.
- `docs/research-scratch/` masters before writing any new documentation file.

A new table, column, or class cites a PRD requirement (`FR-x` / `US-x`). No citation, no
merge (`AGENTS.md` §11 "Change-safety" and §4 "Roles and escalation", Architect role).

## 6. Testing and validation

Commands, gates, and which checks a change class needs are in `AGENTS.md` §9 "Testing and
validation" and §10 "Commands". The Claude-specific parts:

- `vendor/bin/pint` and `vendor/bin/phpstan analyse` operate on the **whole working tree**,
  not your diff. `AGENTS.md` §9 "Testing and validation" forbids `--dirty` in this tree
  (KI-75): run `pint --test --format agent <explicit paths>`. In a tree carrying other
  sessions' in-flight edits, check `git status` first, scope both tools to your own paths,
  and say in the report what you scoped out and why.
- Behavior changes ship with a test (`AGENTS.md` §15 "Definition of done"). Copy-only and
  layout-only changes do not need a new one. Never delete or skip a test without owner
  approval.
- Never report a gate as passing unless you ran it and are quoting the output. "Not run" is a
  legitimate line in the report.

## 7. UI and browser workflow

1. `DESIGN.md` for the visual system, `SCREEN_SPEC.md` §4 "Screen Specifications" for the
   screen's states and §8 "Source of Truth" for what owns which answer. Both are required
   before a visible change.
2. Design tokens, component reuse, and the no-`dark:`/no-skeleton rules are `AGENTS.md` §13
   "UI and UX rules" and §7 "Coding conventions"; they bind every visible change.
3. Desktop-first at 1280px+ (`DESIGN.md` §2.3 "Spacing and layout"). Below 768px the race
   calendar and the turn log are wide tables that deliberately scroll horizontally inside a
   focusable region rather than reflow; that is the contract, and the 768px minimum is a
   proposal rather than a ratified one (`DESIGN.md` §2.3, the 2026-09-29 R85 status line).
   Do not "fix" the scroll to be responsive without a decision.
4. Build or serve before looking at a page: `npm run dev` for iteration, `npm run build`
   when a Blade view references assets and the manifest is missing.
5. Verify the rendered result when it is cheap: Boost `browser-logs` plus a `playwright-cli`
   snapshot or screenshot at 1280 and 390 widths, or a spec under `tests/browser/` run
   through `npm run test:browser`. Check the console before claiming the page works.
6. Accessibility behavior here is test-covered by name: keyboard paths, target sizes, the
   review-form labels, and the focusable scroll regions. Keep them.

The `impeccable` design skill is the repo's route for design critique and polish work; load
it for that class of task rather than improvising a review.

## 8. Source of truth

The fuller table is `SCREEN_SPEC.md` §8 "Source of Truth"; these are the rows a Claude
session reaches for most often.

| Question                                    | Authority                                                                                                            |
| ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| Which screens exist, their URLs and names   | `routes/web.php`, `routes/api.php`                                                                                   |
| What a screen shows and how it got there    | controller methods, then the view                                                                                    |
| Validation and refusals                     | `app/Http/Requests/*`, then `ScenarioCaps` for ceilings                                                              |
| Authorization                               | there is none by design (`ARCHITECTURE.md` §8 "Security Model", `PRD.md` §5 "Non-Functional Requirements" NFR-1)      |
| Schema                                      | `ARCHITECTURE.md` §3 "Database Schema & Eloquent Models" plus the migrations; `ARCHITECTURE-ESSENTIALS.md` is the digest |
| Pipeline stage behavior                     | `AGENTS.md` §8 "Architecture rules an agent must know before editing", `app/Services/DataPipeline/`                   |
| Displayed vocabulary                        | `lang/en/uma.php`, enum labels keyed by case name                                                                    |
| Tokens, contrast, motion                    | `DESIGN.md` and `docs/research-scratch/DESIGN-CORPUS.md`                                                             |
| Automated behavior expectations             | `tests/Feature/*`                                                                                                    |
| Known defects and rulings                   | `KNOWN-ISSUES.md` (live register)                                                                                    |
| Agent operating rules                       | `AGENTS.md`                                                                                                          |
| When sources disagree                       | `AGENTS.md` §2 "Instruction scope and precedence"; record the conflict in `SCREEN_SPEC.md` §7 "Screen-System Gaps & Inconsistencies" or an ADR erratum |

## 9. Protected and sensitive areas

- **Authorization**: there is none, and the rule that guards it is the Boost-inoculation
  paragraph in §1 "Instruction precedence, for Claude specifically". `AGENTS.md` §12
  "Security and privacy" carries the same rule.
- **Secrets**: `.claude/` and the other agent-tool configs are gitignored and some hold live
  API keys on this host. Never copy a key into a source file, a doc, a fixture, a log line,
  or your report (`AGENTS.md` §12 "Security and privacy" lists the tracked-file rule and
  `.env.example`).
- **Lore gate**: advisory. The obligation is that it ran, that the report quotes the hit
  count and its delta from the last recorded figure, and that every hit carries a ruling. `AGENTS.md` §9
  "Testing and validation" carries the mechanism and the last recorded figure. Do not type the banned
  character vocabulary into any artifact (`AGENTS.md` §5 "Non-negotiables" owns the rules and
  points at the bar's C-4 section, where the banned vocabulary is defined, and §15
  "Definition of done" requires the gate to have run).
- **The dev SQLite file** holds real Trainer data. It is untracked, not disposable
  (`AGENTS.md` §10 "Commands", Destructive; §9 "Testing and validation").

## 10. Generated and tool-owned files

`AGENTS.md` §11 "Change-safety" owns the protected list, the regeneration notes, and the
lockfile rule. The generated artifact worth naming here is `PRODUCT.md` (impeccable
plugin output): regenerate, never hand-edit.

## 11. Large changes

- Understand the affected architecture first, then split the work so each step is
  independently verifiable. One concern per commit series (`AGENTS.md` §14 "Git and change
  management").
- Run the gates at each meaningful milestone rather than once at the end, so a break is
  cheap to localize.
- Slice plans and records live in `docs/research-scratch/PROCESS-PLANS.md` and
  `docs/research-scratch/SLICE-RECORDS.md`; new slices do not go in the repository root (`AGENTS.md` §3
  "Documentation map").
- Do not silently expand scope. A feature that needs a `PRD.md` §6 "Non-Goals (eliminated
  legacy features)" item lifted, or a new dependency, is an escalation (`AGENTS.md` §4
  "Roles and escalation" and §5 "Non-negotiables"), not a decision to make in passing.
- Review the aggregate diff, not only the last file you edited.

## 12. Ambiguity and unknowns

Do not invent a requirement. Read the owning document, inspect the code and its tests, and
prefer the smallest behavior-preserving change the evidence supports (`AGENTS.md` §16
"Handling ambiguity" carries the same order). When a decision would change product
behavior, security, data integrity, or a public contract and cannot be safely inferred,
stop and surface it instead of guessing. Mark genuinely undecided things as "Undecided,
owner input needed" rather than naming them. The product name is settled: **Trainer Desk**.

## 13. Documentation maintenance

Which document owns what is `AGENTS.md` §3 "Documentation map"; the dated-erratum rule, the
four-part defect form, and the derived ADR index are `AGENTS.md` §11 "Change-safety". This
file takes content only for Claude-specific practice.

**Personal preferences**: Do not add a `CLAUDE.local.md` to this repository; personal,
unshared preferences belong in `~/.claude/CLAUDE.md` (user-level, not tracked).

**Durable learnings** go into `.ai/rules/` files, the `docs/research-scratch/` masters, and
ADRs, not into Claude's own memory layer (`~/.claude/projects/<project>/memory/`), which is
on by default and is disabled by setting `"autoMemoryEnabled": false` in
`.claude/settings.local.json`. Because `.gitignore` ignores `/.claude` wholesale, no Claude
setting in this repository is shared with another clone: every setting file is per-host, so
this paragraph is the only repo-wide mechanism for the memory layer it describes.

## 14. Git and diff hygiene

- `git status` before you start and `git diff` before you report. This tree frequently holds
  uncommitted work from other sessions: do not revert it, do not reformat it, do not commit
  it. Scope your edits and your tools to your own files.
- There is no CI, so the diff is the review. `CHANGELOG.md` and `VERSION.md` are tracked
  files: `CHANGELOG.md` is updated by the Docs Writer on significant landings
  (`AGENTS.md` §3 "Documentation map", §14 "Git and change management").

## 15. Completion reporting

Report in this shape, and say plainly what you did not verify:

```text
Implemented:
- <file: what changed and why>

Validated:
- <command> -> <result or the output that proves it>
- not run: <gate> (reason)

Documentation:
- <doc updated, or "none, behaviour unchanged">

Known issues / failures:
- <anything failing, skipped, or newly discovered>

Intentionally deferred:
- <what you did not touch and why>
```

Keep it short. The point is that a reader can tell what actually happened and what is still
unproven.

## 16. Change log

This file was rewritten as Claude-specific guidance on top of `AGENTS.md`, then revised to
point at the owning document instead of restating it. Three false claims it once carried
are corrected and guarded here: that `CLAUDE.md` is gitignored (it is tracked); that the
`impeccable` skill lives at repo-level `.agents/skills/impeccable` (it does not; it is at
`~/.agents/skills/impeccable` and `.claude/skills/impeccable`); and that the repository has
a deployment path (it does not; deployment is a non-goal, `PRD.md` §6.10). The date-by-date
history is `git log` on this file.

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
