# Trainer Desk

A local-only Laravel 13 tool for Trainers of the Global English version of *Umamusume Pretty Derby*. It consolidates four legacy apps (three trackers plus a career-run planner) into one machine: browse a catalog of Umamusume whose JP and Global data is cross-referenced by a fetch engine, log training runs turn by turn, compare planned vs actual skills, and export. SQLite only, single user, no accounts, no telemetry.

Product name confirmed 2026-09-27 (PRD OQ-1 closed). Visual system: `DESIGN.md`.

The characters are Umamusume, a humanoid race. Repository text and code never use equine vocabulary for them (lore gate, `CONSTRAINTS.md` C-4).

## Overview

- **Catalog**: Inertia + Vue 3 screens over data the fetch engine cross-references between JP and Global sources. Every engine-written fact carries provenance (source URL, fetched timestamp, raw snapshot on disk).
- **Training runs**: Trainer-owned CRUD. Per-turn stat logging, skill states `Suggested` / `Acquired` / `Skipped`, equipped support-card deck, race entries, and shop purchases. Export is CSV or JSON; historical runs re-import from this app's own CSV export.
- **Review queue**: fuzzy or unmatched source records go to `/review` for the Trainer to confirm, alias, or reject. The engine proposes, the Trainer disposes; the engine never overwrites a row flagged `is_manual`.

## Current Status

Phase 1 of `PRD.md`, substantially implemented and in active development (no release tags; local deliverable only). The catalog, fetch engine, review queue, training-run domain, support-card deck surface, import/export, and the read-only `/api/v1` are built and covered by the Pest suite. Feature status and non-goals are authoritative in `PRD.md` §6; open decisions are tracked there (§7) and in `docs/adr/` (notably `ADR-0016`, next-race readiness, is an open question).

## Technology Stack

| Area            | Technology                                                                                                                                                                      |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Runtime         | PHP >= 8.3 (`composer.json`; developed on 8.5.8) with `pdo_sqlite` and `intl`                                                                                                   |
| Framework       | Laravel 13 (`laravel/framework` 13.32.0)                                                                                                                                        |
| Database        | SQLite only, WAL mode + `busy_timeout` (`database/database.sqlite`, gitignored)                                                                                                 |
| Cache / Queue   | framework `database` stores (no Redis)                                                                                                                                          |
| Frontend        | Inertia + Vue 3 + TypeScript (`inertiajs/inertia-laravel`, `@inertiajs/vue3`), Tailwind CSS v4 (CSS-first `@theme` in `resources/css/app.css`, no `tailwind.config.js`), Vite 7 |
| Testing         | Pest 4 (feature-first), `Http::fake`, in-memory SQLite for tests; Playwright for browser/E2E and accessibility                                                                  |
| Static analysis | Larastan level 6 (`phpstan.neon`), Pint (laravel preset, `pint.json`)                                                                                                           |
| Type check      | `tsc --noEmit` (`npm run typecheck`)                                                                                                                                            |

Deliberately absent (PRD §6): auth packages, Livewire, Excel export, Redis, MySQL/PostgreSQL, deploy tooling. The web surface is Inertia + Vue 3 (`ADR-0020` §1), which superseded the pre-2.0 Blade-only surface and the older "no SPA" note this file used to carry; the 2.0 line is a client-rendered shell over the same loopback-only, single-Trainer backend. `compose.yaml` is stock Laravel Sail (MySQL/Redis) and is **not** the supported database path for this app.

## Requirements

- PHP >= 8.3 with `pdo_sqlite` and `intl` (`intl` is used by `App\Services\DataPipeline\NameNormalizer`)
- Composer and Node.js with npm (Vite build)
- No database server: SQLite is the only supported driver

## Installation

```bash
git clone <repo> && cd umamusume-laravel13

# 1. Configure the environment BEFORE running setup. The skeleton .env.example
#    defaults to the Sail MySQL connection; this app is SQLite only, and
#    `composer setup` will not overwrite an existing .env.
Copy-Item .env.example .env
#    then set in .env:
#      DB_CONNECTION=sqlite
#      DB_DATABASE=database/database.sqlite

# 2. Create the database file.
New-Item -ItemType File database/database.sqlite   # or: touch database/database.sqlite (Git Bash)

# 3. One-command setup: composer install, artisan key:generate, migrate,
#    npm install, npm run build.
composer setup

# 4. Populate the catalog offline from the committed source bodies.
php artisan migrate:fresh --seed
```text

Seeding reads `database/seeders/data/` (the committed copies of each source document) and fills the reference tables with no network: the roster, cards, 1,910 skills (623 `[Global]`, as of the `609afe88` snapshot), 559 support cards, scenario slots. Trainer-facing surfaces only show rows passing `Skill::availableOnGlobal()` (release status `GlobalReleased` **and** `name_is_client`), per `ADR-0011`.

## Configuration

- `.env` per above. No secrets are required; Phase 1 has none (`ARCHITECTURE.md` §8).
- `UMA_DISPLAY_TIMEZONE` (optional, default `UTC`): render zone for datetimes; storage is always UTC.
- `UMA_FETCH_USER_AGENT` (optional): descriptive UA for the fetch engine.
- `config/uma.php`: fetch source allowlist, politeness (`delay_ms`, `timeout_s`), fuzzy-match threshold, cache TTL.
- `config/scenarios.php`: the only place scenario names enter the layout path; a scenario name in a view is a gate failure.

## Running Locally

```bash
composer dev          # artisan serve + queue listener + pail logs + Vite HMR, concurrently
```text

Keep the app on loopback; it has no auth surface and must not be exposed (`ARCHITECTURE.md` §8).

Windows note: do not use `make`. GNU make cannot run on this host (KI-4); the `Makefile` targets are documentation. Use the `composer` scripts and direct `php artisan` / `vendor/bin` commands below.

### Serving for a browser pass

`.env` sets `SESSION_DRIVER=database` and `CACHE_STORE=database` (`.env:28`, `.env:38`), so every page
load writes session and cache rows into `database/database.sqlite`. That file is shared: a browser pass
against the default server mutates state other sessions hold open and moves the domain fingerprint that
exists to detect exactly that. Serve with both stores on files instead:

```bash
composer serve:browser                                # or, in a POSIX shell:
SESSION_DRIVER=file CACHE_STORE=file php artisan serve
```text

Use `file`, not `array`: the array driver does not carry the CSRF token between the GET that renders a
form and the POST that submits it, so every form in the pass comes back a 419. `phpunit.xml` forces
`array` for the test suite, which is the right choice there and the wrong one for a browser. The
shared database stays untouched, so the before-and-after fingerprint comparison means something.

## Fetch Engine

Eight sources are declared in `config/uma.php`: `gametora-characters`, `gametora-character-cards`, `gametora-race-catalog`, `gametora-skills`, `gametora-character-profiles`, `gametora-support-cards`, `gametora-support-effects`, `gametora-artwork`. Seven have a parser and a document to parse. The eighth is an **asset host** (`ADR-0021`): it declares directory shapes rather than a document, has no parser and no seed file, `uma:fetch` steps over it, and `uma:fetch-art` is its only reader. It lives in this array so the SSRF allowlist covers the host every request goes to, rather than leaving a second place a URL could hide.

Four of the seven resolve the document URL through the publisher's manifest first (two requests per fetch), keeping the pinned URL as an offline fallback; KI-24 measured that a stale pinned hash answers `200` with the superseded document, so manifest resolution is the freshness defense.

Pipeline stages are isolated: `fetch -> snapshot -> parse -> normalize -> match -> promote | review`. `SourceFetcher` is the only outbound HTTP path and only ever visits hosts in the config allowlist (SSRF floor). Unchanged snapshot hashes short-circuit a run; promotion upserts engine-owned columns inside a transaction and skips `is_manual` rows.

```bash
php artisan uma:fetch [source]            # omit source to fetch all declared sources
php artisan uma:reparse <source>          # replay parse->match->promote from stored snapshots, zero network
php artisan uma:fetch-art [--kind=…]      # mirror id-addressable artwork into storage/app/private/artwork (ADR-0021); manual only
php artisan uma:import:support-cards      # import the two support datasets from committed bodies, zero network
php artisan uma:backup [path]             # WAL checkpoint + consistent single-file copy (NFR-5)
```text

Adding a source requires owner approval plus a robots/rate-limit review and one parser class (`AGENTS.md`, Data Engineer). The robots/live-availability check on the GameTora host remains formally outstanding (PRD OQ-2).

## Web Surface

Verified against `php artisan route:list`:

| Route                                                 | Purpose                                                                                                                                                                                                                  |                                      |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------ |
| `/` (`home`)                                         | the Dashboard, the Inertia landing screen (`SCR-CAR-001`)                                                                                                                                                                |                                      |
| `/umamusume`, `/umamusume/{slug}`                     | catalog index (release-status filter, normalized search) and detail with aliases, cards, and provenance                                                                                                                  |                                      |
| `/skills`, `/skills/{skill}`                          | skill search (Screen D) and detail; Global-filtered, paginated                                                                                                                                                           |                                      |
| `/support-cards`, `/support-cards/{card}`             | support-card catalog (reference data only, no collection state)                                                                                                                                                          |                                      |
| `/training-runs` (+ `/create`, PUT, DELETE) | Trainer run CRUD. `GET /training-runs/{run}` is a compatibility redirect to the Career Cockpit: `SCR-RUN-003` (the 0.1.0 run-detail page) is retired (F2), so it is not a normal-navigation destination. |                                      |
| `/training-runs/{run}/turns[/{turn}]`                 | per-turn stat logging; stats validate against the run's scenario ceiling (`base_cap` + `cap_bonus`, clamped to `hard_cap`, via `App\Services\ScenarioCaps`; `ADR-0015`). A run with no scenario keeps the 1200 base cap. |                                      |
| `/training-runs/{run}/skills`                         | skill states Suggested / Acquired / Skipped                                                                                                                                                                              |                                      |
| `/training-runs/{run}/deck`                           | the six support cards the run was equipped with                                                                                                                                                                          |                                      |
| `/training-runs/{run}/races`, `.../purchases`         | race entries and shop purchases per turn                                                                                                                                                                                 |                                      |
| `/training-runs/import` (+ preview, store)            | historical-run import from this app's own CSV export (`ADR-0017`)                                                                                                                                                        |                                      |
| `/training-runs/{run}/export/{format}`                | run download (`csv`/`json`), no data lock-in (US-6)                                                                                                                                                                      |                                      |
| `/review`, `POST /review/{candidate}`                 | match review queue                                                                                                                                                                                                       |                                      |
| `/career/setup/{scenario,trainee,target,legacy,deck,preflight}` | the six-step career setup wizard (`SCR-CAR-002`–`SCR-CAR-010`), session draft until Preflight creates the run                                                                                                 |                                      |
| `/training-runs/{run}/cockpit`                        | the run-scoped Career Cockpit (`SCR-CAR-011`)                                                                                                                                                                            |                                      |
| `/training-runs/{run}/{training,races,events,inheritance,skills,timeline,result,races/planner}` | the career detail screens (`SCR-CAR-012`–`SCR-CAR-023`): training, race decision and planner, event decision, inheritance, skills planner, timeline, result |                                      |
| `/training-runs/{run}/veteran`                        | Save Veteran (write half of the library, `SCR-VET-003`)                                                                                                                                                                  |                                      |
| `/veterans` (+ `/compare`, `/{veteran}`)              | Veteran library, comparison and detail (`SCR-VET-001`/`002`/`004`)                                                                                                                                                        |                                      |
| `/database` (+ `/trainees/supports/skills/races/scenarios`) | the Database hub (`SCR-SYS-005`–`007`), read-only reference surfaces                                                                                                              |                                      |
| `/legacy` (+ `/compare`, `/{run}`)                    | the run-scoped Legacy Lab (`SCR-CAR-006`), record-only                                                                                                                                                                    |                                      |
| `/up`                                                 | framework health route                                                                                                                                                                                                   |                                      |

The former `/design-preview` route has been deleted; component review now happens on the real screens and their tests.

## Local API (read-only)

```bash
GET /api/v1/umamusume?status=GlobalReleased&search=special%20week&page=1&pageSize=25
GET /api/v1/umamusume/{slug}
GET /api/v1/training-runs[/{id}]
GET /api/v1/support-cards[/{supportCard}]
```text

List shape: `{ "data": [...], "pagination": { "page", "pageSize", "totalItems", "totalPages" } }`. Every non-2xx: `{ "error": { "code", "message" } }`, rendered centrally in `bootstrap/app.php`. Contract: `ARCHITECTURE.md` §4.

## Testing & Quality Gates

| Gate                                                | Command                                                                                                                                        |
| --------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| Full pipeline (config:clear, TS typecheck, suite)   | `composer test`                                                                                                                                |
| Tests                                               | `php artisan test --compact` (narrowest first: `--filter=Name`, or a file path via `vendor/bin/pest`)                                          |
| Style check / fix                                   | `composer lint` / `vendor/bin/pint --dirty --format agent`                                                                                     |
| Static analysis                                     | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` (level 6; `composer analyse` omits the flag and can OOM a 128M CLI default)       |
| TS type check                                       | `npm run typecheck`                                                                                                                            |
| Accessibility                                       | `npm run test:a11y` (Playwright + `@axe-core/playwright`, WCAG 2.1 AA ruleset)                                                                 |
| Lore grep                                           | `composer lore` and `composer lore-code` (extended, includes untracked files and Global client terminology); each hit needs a context ruling   |
| Dependency audit                                    | `composer audit` and `npm audit --omit=dev` (the `Makefile` `audit` target wraps both; make itself is unavailable, KI-4)                       |

Tests use Pest 4, feature-first, with a global `TestCase` + `RefreshDatabase` binding in `tests/Pest.php`. All fetcher/pipeline tests use `Http::fake` against stored bodies in `tests/Fixtures`; no test touches the network. The test database is in-memory SQLite (`phpunit.xml` forces it). The full quality bar, including the no-suppression floor, is `CONSTRAINTS.md`.

## Common Commands

| Task                              | Command                                                                                             |
| --------------------------------- | --------------------------------------------------------------------------------------------------- |
| First-time setup                  | `composer setup` (then fix `.env` DB to sqlite, see Installation)                                   |
| Dev environment                   | `composer dev`                                                                                      |
| Build assets                      | `npm run build`                                                                                     |
| Accessibility tests               | `npm run test:a11y`                                                                                 |
| Fresh DB + offline catalog data   | `php artisan migrate:fresh --seed`                                                                  |
| Fetch / replay / backup           | `php artisan uma:fetch`, `uma:reparse <source>`, `uma:backup`                                       |
| Mirror catalog artwork            | `php artisan uma:fetch-art` (options `--kind`, `--dry-run`, `--refetch`); manual only, `ADR-0021`   |
| Route list                        | `php artisan route:list`                                                                            |
| Backup before experimenting       | `php artisan uma:backup` (writes to `storage/app/backups/`)                                         |

## Database & Data Setup

> **Warning:** `php artisan migrate:fresh --seed` drops all tables and recreates them against `database/database.sqlite`, removing local Trainer data. It is the documented rebuild path for this single-Trainer dev database (`docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md", C-5), not a production operation; run `php artisan uma:backup` first if the runs matter.

- Storage is SQLite only, WAL + `busy_timeout`, so the web request and queue worker coexist (NFR-4).
- Seeders: `UmamusumeSeeder` (two illustrative trainees), `UmamusumeRosterSeeder` (committed roster), `SkillSeeder` (nine names the skills import later adopts), `SourceDocumentSeeder` (every committed body through `PipelineRunner`), `ScenarioSlotSeeder` (URA finale goal races). Order in `DatabaseSeeder` is load-bearing.
- Snapshots land in `storage/app/private/snapshots` (gitignored, hashed). Trainer data (runs, turns, manual edits) is never deleted or overwritten by the engine.

## Main Workflows

1. Fetch or seed reference data, then browse `/umamusume`, `/skills`, `/support-cards`; each engine row shows its source URL and fetch date.
2. Work the `/review` queue for Fuzzy/None match candidates.
3. Create a run (`/training-runs/create`), pick scenario and costume card, record the Legacy Select payload, and set planned skills as `Suggested`.
4. Log turns one by one (five stats, SP, condition, Energy, Mood, Fans) with races and purchases per turn; the guided-step UI drives the form.
5. Mark skills `Acquired` or `Skipped` as they happen; keep the six-card deck current.
6. Export the run as CSV or JSON, or re-import a past run through `/training-runs/import` with a preview step.

Screen-level behavior is specified in `SCREEN_SPEC.md`.

## Project Structure

```text
app/Actions/              PromoteMatchedRecord, ResolveMatchCandidate, ImportHistoricalRun,
                          Store* ingest actions (skills, cards, profiles, support, race slots)
app/Console/Commands/     UmaFetch, UmaReparse, UmaBackup, UmaImportSupportCards (+ ManageSkills, see below)
app/Enums/                ReleaseStatus, RunStatus, SkillAcquisition, MatchTier, CandidateStatus,
                          AliasLanguage, CardRarity, MoodTier, RaceEntryStatus, SpiritBurstState, TurnEventType
app/Http/Controllers/     Web controllers + Api/V1 (Umamusume, TrainingRun, SupportCard)
app/Http/Requests/        Form Requests for every write (stat bounds, deck, import, search)
app/Http/Resources/       API Resources (camelCase): Umamusume, TrainingRun, TurnEntry, SupportCard
app/Models/               Umamusume, UmamusumeAlias, UmamusumeProfile, CharacterCard, Skill,
                          TrainingRun, TurnEntry, TurnEvent, RunSkill, RaceEntry, RaceCatalogSlot,
                          Scenario, ScenarioRace, ScenarioSlot, SupportCard, SupportEffect, DeckSlot,
                          DataSource, MatchCandidate, Preference
app/Services/DataPipeline/ SourceFetcher (only outbound HTTP), NameNormalizer (NFKD),
                          CrossReferenceMatcher (Exact/Alias/Fuzzy/None), PipelineRunner, Parsers/, Contracts/
app/Services/             ScenarioCaps (per-stat ceilings), SupportCardEffects (+ the Skill* automation trio below)
config/uma.php            fetch allowlist, politeness, thresholds; config/scenarios.php scenario layout data
database/                 migrations, factories for every model, seeders, seeders/data (committed source bodies)
resources/views/          Blade: `app.blade.php` (the Inertia shell) and `errors/{404,419,500}.blade.php`; `resources/views/components/` is empty since slice B1
resources/js/             Inertia + Vue 3 + TypeScript: `spa.ts`, `bootstrap.ts`, `types.ts`, `pages/**/*.vue` (54), `components/**/*.vue` (58), `layouts/`
routes/                   web.php, api.php (named routes throughout)
tests/                    Pest: Feature (100+ files) + Unit; tests/Fixtures stored bodies
docs/                     adr/, scenarios/, UMAMUSUME_REFERENCE.md, research-scratch/ (governance masters)
tools/                    lore.php (lore gate), gate.py (design-artifact gate), doc_census.py, roster-crosscheck.php
```text

## Documentation Map

| Document                                | Purpose                                                                                                                               |
| --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| `PRD.md`                                | Product truth: users, stories, functional/non-functional requirements, non-goals, open questions                                      |
| `PRODUCT.md`                            | Product summary for design and agent context                                                                                          |
| `ARCHITECTURE.md`                       | Full system design: schema, fetch engine, API contract, security model                                                                |
| `ARCHITECTURE-ESSENTIALS.md`            | Token-efficient digest of the above (ARCHITECTURE.md wins on conflict)                                                                |
| `DESIGN.md`                             | Visual system contract: theme, tokens, components                                                                                     |
| `SCREEN_SPEC.md`                        | Screen and workflow specifications                                                                                                    |
| `CONSTRAINTS.md`                        | Pointer; the binding quality bar (C-1 to C-9) lives in `docs/research-scratch/GOVERNANCE.md`                                          |
| `docs/research-scratch/GOVERNANCE.md`   | Gate registry, pre-mortem risk record, consolidated design/governance masters                                                         |
| `AGENTS.md`                             | Agent roles, escalation paths, practical build notes                                                                                  |
| `CLAUDE.md`                             | Coding rules for assistants                                                                                                           |
| `KNOWN-ISSUES.md`                       | Pointer; defect register KI-01..KI-58 under `docs/research-scratch/AUDIT-AND-VERIFICATION.md`. New entries append to the root file.   |
| `docs/adr/README.md`                    | ADR index (`ADR-0001` to `ADR-0021`) with the errata convention                                                                       |
| `docs/UMAMUSUME_REFERENCE.md`           | Source-cited mechanics reference; live-ops claims are dated snapshots                                                                 |
| `docs/scenarios/`                       | Per-scenario playing guides                                                                                                           |
| `.ai/rules/index.md`                    | Path-scoped repo rules (style, Eloquent, testing)                                                                                     |
| `PLAN.md`                               | Pointer; the frontend slice plan lives in `docs/research-scratch/PROCESS-PLANS.md`                                                    |

When documents disagree: `CONSTRAINTS.md` bar > gate registry > ADRs > `DESIGN.md` > slice plans; `ARCHITECTURE.md` over its digest; `PRD.md` is product truth.

## Skill Automation Subsystem (separate tooling layer)

`app/Services/Skill{Registry,Matcher,Executor}.php` and `php artisan skill:manage` drive the declarative skills in `.agents/skills.json` (machine-local registry, not tracked). They touch none of the catalog/run tables and are documented in `docs/SKILL_AUTOMATION.md`. Treat them as separate tooling from the Uma domain.

## Contribution

No dedicated `CONTRIBUTING.md` exists. Actual practice, from `AGENTS.md` and the gates above:

- Behavior changes start with a failing Pest test (`php artisan make:test --pest {Name}`); models come from factories.
- Form Requests for all validation, thin controllers, logic in Actions/Services, named routes, API Resources for JSON, `declare(strict_types=1)` and explicit types everywhere, `#[Fillable]`/`#[Hidden]` attributes, `casts()` as a method.
- Before hand-off: targeted tests green, `vendor/bin/pint --dirty --format agent`, PHPStan level 6 clean, `composer lore-code` clean (every grep hit needs a context ruling; the verdict is blocking).
- Schema or pipeline changes need a migration, a PRD citation (FR-x / US-x), and a decision recorded as an ADR with a dated erratum rather than a silent rewrite.
- Scope changes never happen silently: `PRD.md` §6 non-goals are owner territory (`AGENTS.md` escalation paths).

## Security

No `SECURITY.md` exists; the model is documented in `ARCHITECTURE.md` §8:

- No auth surface, no multi-user support, no public deploy. Serve on loopback only.
- Fetched pages are untrusted input: parsed as data, never rendered unescaped (Blade auto-escape, no `{!! !!}` on source data), URLs found in bodies never followed (config allowlist only).
- Trainer forms go through Form Requests.
- Phase 1 has no secrets; any future key goes through `.env` only.
- `composer audit` and `npm audit --omit=dev` before tagging a build.

## Deployment

There is none, by decision (PRD §6.10): no hosting, deployment, or cloud path. The deliverable is a local `composer dev`. Do not expose this app.

## Troubleshooting

- **Vite manifest error on a page** → the built assets are missing or stale. Cause: no `npm run build` after edits or clone. Fix: `npm run build` (or `composer dev` for HMR).
- **`migrate` fails to find the database** → `database/database.sqlite` does not exist yet. Fix: create an empty file at that path, then `php artisan migrate`.
- **A fetch says "unchanged since last snapshot" but the screen is empty** → KI-27: the snapshot short-circuit keys on the document hash under today's date in a `storage/` directory shared by every database in the working tree, so a second database asked the same day is told nothing changed. Fix: `php artisan uma:reparse <source>` (zero network, upserts on source identity, safe on a populated database).
- **A pinned source URL serves old data** → KI-24: withdrawn documents still answer `200`. Manifest-resolved sources exist to avoid this; `uma:fetch` warns when it falls back to the pinned URL.
- **`composer analyse` OOMs** → PHP CLI 128M default. Fix: `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`.
- **`make` fails on this host** → GNU make is unavailable (KI-4). Run the underlying `composer` scripts or artisan/vendor commands.

## Known Limitations

Evidence-backed current state, distinct from the PRD non-goals in §6:

- Next-race readiness is not a shipped judgement: fatigue data is recorded but the threshold is an open question (`ADR-0016`).
- Support cards are reference data and per-run deck only; card collection state (ownership, levels, limit breaks) is cut (`ADR-0014`). Card tier labels are held for want of a Global source.
- Sourced artwork is authorized and **built** (`ADR-0021`, 2026-10-05): `uma:fetch-art` mirrors id-addressable game art from an allowlisted host into a gitignored local mirror, and `ArtworkSlot.vue` renders it on the catalog and support-card surfaces; an absent file paints nothing (`DESIGN.md` §4.7). Which further screens get a slot is the open placement remainder (PRD OQ-6). There are still no trainee image uploads (PRD §6.13).
- The GameTora robots.txt / rate-limit verification is formally outstanding (PRD OQ-2); sources are bounded by config politeness settings instead.
- Fetch scheduling is manual; the scheduler default is open (PRD OQ-3).
- Aptitude letters and per-scenario caps are engine-owned facts (`ADR-0004`); growth rates and base stats are not implemented (PRD OQ-4 scope).
- The framework-default `users` table and `User` model exist but are unused.
- The live defect register is `KNOWN-ISSUES.md` -> `docs/research-scratch/AUDIT-AND-VERIFICATION.md` (KI-01..KI-58); `tools/gate.py`'s hex allowlist currently has an open defect (KI-58) affecting rendered-prototype checks.

## License

Declared MIT in `composer.json` (`"license": "MIT"`); no `LICENSE` file is present at the repository root.
