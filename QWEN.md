# Trainer Desk - QWEN.md

## Project Overview

**Trainer Desk** is a local-only, single-user Laravel 13 application for trainers of the Global English release of *Umamusume Pretty Derby*. It consolidates four legacy applications (three trackers plus a career-run planner) into one unified tool.

### Core Purpose
- Browse a catalog of Umamusume characters with cross-referenced JP and Global data
- Log training runs turn-by-turn with skill states, support-card decks, race entries, and shop purchases
- Compare planned vs actual skills and export run data
- Review queue for unmatched/fuzzy source records

### Key Characteristics
- **Local-only**: Runs on loopback, no auth surface, no multi-user support, no public deploy
- **SQLite only**: Single database file with WAL mode + busy_timeout
- **No telemetry**: Completely offline-capable after initial data fetch
- **Single trainer**: All data belongs to one user, no accounts

## Technology Stack

| Layer | Technology |
|-------|------------|
| Runtime | PHP >= 8.3 (developed on 8.5.8) with `pdo_sqlite` and `intl` |
| Framework | Laravel 13 (13.42.0) |
| Database | SQLite only (WAL + busy_timeout) |
| Frontend | Inertia.js + Vue 3 + TypeScript |
| Styling | Tailwind CSS v4 (CSS-first `@theme`, no config file) |
| Build | Vite 7 |
| Testing | Pest 4 (feature-first), Playwright (browser/E2E) |
| Static Analysis | Larastan level 6, Pint (Laravel preset) |

### Deliberately Absent
- Auth packages (Sanctum, Breeze)
- Livewire
- Excel export libraries
- Redis
- MySQL/PostgreSQL
- Deploy tooling

## Project Structure

```
app/
├── Actions/              # One-off operations (PromoteMatchedRecord, ImportHistoricalRun, etc.)
├── Console/Commands/     # UmaFetch, UmaReparse, UmaBackup, UmaImportSupportCards
├── Enums/                # TitleCase enums (ReleaseStatus, RunStatus, SkillAcquisition, etc.)
├── Http/
│   ├── Controllers/      # Web + Api/V1 (thin controllers)
│   ├── Requests/         # Form Requests for every write
│   └── Resources/        # API Resources (camelCase JSON)
├── Models/               # Eloquent models (Umamusume, TrainingRun, Skill, SupportCard, etc.)
├── Services/
│   ├── DataPipeline/     # Fetch engine (SourceFetcher, NameNormalizer, CrossReferenceMatcher)
│   ├── ScenarioCaps.php  # Single owner of per-stat ceiling arithmetic
│   └── SupportCardEffects.php
├── Domain/               # Career and Training domain value objects
config/
├── uma.php               # Fetch allowlist, politeness, thresholds
└── scenarios.php         # Scenario names (only place they enter layout path)
database/
├── migrations/           # 80 migration files
├── factories/            # Factory per model
├── seeders/              # Data seeders
└── seeders/data/         # Committed source bodies for offline seeding
resources/
├── js/
│   ├── pages/            # 62 Vue page components
│   ├── components/       # 119 shared Vue components
│   ├── layouts/          # App, Setup, Career layouts
│   ├── domain/           # Pure domain shapes
│   └── composables/      # Vue composables (useVisitState, etc.)
├── css/app.css           # Tailwind v4 CSS-first theme
└── views/                # Only app.blade.php (Inertia shell) + errors/
routes/
├── web.php               # Browser routes
└── api.php               # Read-only /api/v1 routes
tests/
├── Feature/              # 257 feature tests (13 unit)
├── Unit/                 # Unit tests (no database)
├── browser/              # 126 Playwright specs
└── Fixtures/             # Stored response bodies for Http::fake
docs/
├── adr/                  # Architecture Decision Records (0001-0030)
├── scenarios/            # Per-scenario playing guides
├── research-scratch/     # Governance masters, slice records
└── UMAMUSUME_REFERENCE.md # Source-cited mechanics corpus
tools/                    # lore.php (lore gate), gate.py, doc_census.py
```

## Building and Running

### Initial Setup

```bash
# 1. Clone and configure environment
git clone https://github.com/IzzatFirdaus/umamusume-laravel13.git
cd umamusume-laravel13
cp .env.example .env

# Edit .env to set:
#   DB_CONNECTION=sqlite
#   DB_DATABASE=database/database.sqlite

# 2. Create database file
touch database/database.sqlite  # On Windows: New-Item -ItemType File database/database.sqlite

# 3. Run setup (composer install, key:generate, migrate, npm install, npm run build)
composer setup
```

### Development

```bash
# Start dev environment (serve + queue + logs + Vite HMR)
composer dev

# Or serve for browser testing (uses file session/cache drivers)
composer serve:browser
```

### Data Setup

```bash
# Seed catalog from committed offline data (no network required)
php artisan migrate:fresh --seed

# Fetch from live sources (requires network, robots.txt compliance)
php artisan uma:fetch [source]

# Replay parse pipeline from stored snapshots (zero network)
php artisan uma:reparse <source>

# Mirror catalog artwork (manual, ADR-0021)
php artisan uma:fetch-art [--kind=...] [--dry-run] [--refetch]

# Import support card datasets
php artisan uma:import:support-cards

# Backup database (WAL checkpoint + consistent copy)
php artisan uma:backup [path]
```

### Testing

```bash
# Full pipeline (config:clear, TS typecheck, full suite)
composer test

# Run tests only
php artisan test --compact

# Narrow test execution
php artisan test --compact --filter=TestName
vendor/bin/pest tests/Feature/SpecificTest.php

# Static analysis (requires 1G memory limit)
vendor/bin/phpstan analyse --no-progress --memory-limit=1G

# Code style check
vendor/bin/pint --test --format agent <paths>

# TypeScript type checking
npm run typecheck

# UI linting
npm run lint:ui

# Lore gate (report-only, always exits 0)
composer lore        # Docs mode
composer lore-code   # Code mode

# Browser/E2E tests
npm run test:browser

# Accessibility tests
npm run test:a11y

# Dependency audit
composer audit && npm audit --omit=dev
```

### Build Assets

```bash
npm run build      # Production build
npm run dev        # Development with HMR
```

## Development Conventions

### Code Style

- **PHP**: `declare(strict_types=1);` on line one, explicit parameter/return types everywhere
- **Constructor property promotion** required
- **`#[Fillable]` and `#[Hidden]` attributes** (not legacy `$fillable`/`$hidden` properties)
- **`HasFactory` trait** with `@use HasFactory<XFactory>` annotation
- **Casts as method**: `casts()` method, never `$casts` property
- **TitleCase enum cases** in `app/Enums/`
- **Thin controllers**: Logic in `app/Actions` or `app/Services`
- **Form Requests** for every write operation
- **API Resources** for all JSON responses
- **Named routes** with `route()` helper everywhere

### Frontend Conventions

- **Design tokens only**: `bg-page`, `text-ink`, `border-rule`, etc.
- **Zero `dark:` utilities** and zero skeleton palette classes
- **Scenario names** from `config/scenarios.php` only (hardcoded names are defects)
- **Empty states**: Every data view renders empty, loading, and error states
- **Unrecorded values**: Render as `N/A` with disclosure `title`, never defaults
- **Global client vocabulary only** (see `lang/en/uma.php`)

### Testing Practices

- **Pest 4, feature-first** testing approach
- **Global binding** in `tests/Pest.php` applies `TestCase` + `RefreshDatabase` + `withoutVite()`
- **Never add** `uses()` or `withoutVite()` to individual test files
- **Build models from factories** (check for factory states first)
- **Http::fake** with stored bodies in `tests/Fixtures/` (no test touches network)
- **Facade fakes** (`Bus::fake`, `Queue::fake`, `Mail::fake`) for framework services
- **Mockery** only for custom/external dependencies
- **In-memory SQLite** for tests (`phpunit.xml` forces `DB_DATABASE=:memory:`)

### Architecture Rules

- **Pipeline stages isolated**: fetch → snapshot → parse → normalize → match → promote | review
- **SourceFetcher** is the only outbound HTTP path (SSRF allowlist in `config('uma.sources')`)
- **Snapshot hashing**: Unchanged hash short-circuits pipeline run
- **NameNormalizer** is pure (NFKD, lowercase, separators stripped)
- **Match tiers**: Exact/Alias auto-promote; Fuzzy/None go to review queue
- **Promote**: Upserts engine-owned columns, skips `is_manual = true` rows, writes provenance
- **ScenarioCaps**: Single owner of per-stat ceiling arithmetic
- **No business logic in controllers**
- **No inline `$request->validate()`**

### Lore Gate (Mandatory)

The characters are Umamusume (humanoid race). Banned vocabulary and framing enforced by `tools/lore.php`:
- Run `composer lore` and `composer lore-code` before shipping copy
- Every hit needs a context ruling
- Dataset/export keys are exempt; display paths are gated
- Never rename dataset keys to satisfy the list

### Documentation Hierarchy

When documents disagree:
1. Quality bar (`docs/research-scratch/GOVERNANCE.md` §CONSTRAINTS.md)
2. Gate registry
3. ADRs (accepted decisions bind)
4. `DESIGN.md`
5. Slice plans

`ARCHITECTURE.md` beats its digest. `PRD.md` is product truth.

### Git Conventions

- **Conventional commits**: `type(scope): summary` (lowercase, imperative)
- Examples: `feat(runs):`, `fix(components):`, `test(readme):`, `refactor(filters):`
- **One slice per commit series**
- **Never commit**: `vendor/`, `node_modules/`, `storage/` snapshots, `.env`, `public/build/`
- **Path-specific commits**: `git commit <path>` to guard the index
- **No deleting/skipping tests** without owner approval

## Key Commands Reference

| Task | Command |
|------|---------|
| First-time setup | `composer setup` |
| Dev environment | `composer dev` |
| Full test suite | `composer test` |
| Run all tests | `php artisan test --compact` |
| Style check | `composer lint` |
| Style fix | `vendor/bin/pint --test --format agent <paths>` |
| Static analysis | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` |
| TypeScript check | `npm run typecheck` |
| Fresh DB + seed | `php artisan migrate:fresh --seed` |
| Fetch all sources | `php artisan uma:fetch` |
| Backup database | `php artisan uma:backup` |
| Mirror artwork | `php artisan uma:fetch-art` |
| Route list | `php artisan route:list` |
| Lore gate (docs) | `composer lore` |
| Lore gate (code) | `composer lore-code` |
| Dependency audit | `composer audit && npm audit --omit=dev` |

## Important Files

| File | Purpose |
|------|---------|
| `PRD.md` | Product truth: users, stories, requirements, non-goals |
| `ARCHITECTURE.md` | Full system design: schema, pipeline, API, security |
| `ARCHITECTURE-ESSENTIALS.md` | Token-efficient digest (ARCHITECTURE.md wins on conflict) |
| `DESIGN.md` | Visual system: tokens, components, motion |
| `SCREEN_SPEC.md` | Screen inventory, states, workflows |
| `AGENTS.md` | Operational contract for coding agents |
| `CLAUDE.md` | Coding rules for assistants |
| `docs/research-scratch/GOVERNANCE.md` | Quality bar (C-1 to C-9), gate registry |
| `KNOWN-ISSUES.md` | Live defect register (KI-nn) |
| `CHANGELOG.md` | Notable changes (Keep a Changelog format) |
| `VERSION.md` | Current version, pre-release status |
| `docs/adr/README.md` | ADR index (0001-0030) |
| `docs/UMAMUSUME_REFERENCE.md` | Source-cited mechanics corpus |

## Security Model

- **No auth surface**: Loopback only, no accounts
- **Fetched pages are untrusted**: Parsed as data, never rendered unescaped
- **No `{!! !!}`** on source data (Blade auto-escape)
- **URLs in fetched bodies never followed** (config allowlist only)
- **Trainer input** goes through Form Requests
- **Phase 1 has no secrets**: Any future key goes in `.env` only
- **SSRF protection**: All outbound HTTP through `SourceFetcher` with allowlist

## Known Limitations

- **CI exists and is Windows-native**: `.github/workflows/` holds three workflows
  (`nativephp-windows.yml`, `nativephp-windows-acceptance.yml`, `release.yml`), added 2026-11-15 and
  reachable on `origin` (`github.com/IzzatFirdaus/umamusume-laravel13`). The earlier "No CI" line was
  wrong. Local gates are still the inner loop; CI covers the packaged Windows build and release.
- **GNU make unavailable** on Windows (KI-4): Use `composer` scripts instead. Confirmed 2026-11-16 —
  `make --version` exits 127. The `Makefile` is present but unusable on this host.
- **CI does not run the quality gates**: the three workflows build and acceptance-test the
  packaged Windows app (`nativephp-windows.yml`, `nativephp-windows-acceptance.yml`, `release.yml`).
  None of them runs `pest`, `pint`, `phpstan`, `npm run typecheck` or `npm run lint:ui`. Those gates
  are local-only; a green CI run says the exe packaged, not that the suite passed.
- **SQLite only**: No MySQL/PostgreSQL support
- **No image uploads**: Trainer-supplied files are cut (PRD §6.13)
- **Artwork mirror is manual**: `uma:fetch-art` must be run explicitly
- **Empty mirror is invisible by design**: Missing artwork renders nothing (`DESIGN.md` §4.7 rules
  absence a normal state, not an error state, and R-31 rejects decorative placeholders). This is a
  ratified design decision, not a defect; changing it needs an owner amendment.

### Measured gate status (2026-11-16, HEAD 69488c1f)

| Gate | Result |
|---|---|
| `vendor/bin/pest --filter <Name>` | green (targeted runs; one process at a time — overlapping runs hang) |
| `npm run build` | exit 0 |
| `npm run lint:ui` | exit 0, 516 report-only findings |
| `vendor/bin/pint --test --format agent <paths>` | green on touched paths |
| `npm run typecheck` | **exit 1, pre-existing**: `tests/browser/nativephp-packaged.spec.ts(37,9)` and `(38,9)` TS18047 |
| `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **exit 1, pre-existing**: `app/Http/Middleware/EnforceNativePHPSecret.php:13` |

*Dated erratum 2026-11-17 (HEAD `be8893a2`): the two "exit 1, pre-existing" rows above are superseded
by measurement, not edited. Both gates now pass on this tree: `npm run typecheck` exit 0 and
`vendor/bin/phpstan analyse --no-progress --memory-limit=1G` "[OK] No errors" (run 2026-11-17). The
falsified rows are kept verbatim as the erratum's subject; treat the exit-0 measurement as current.*

## Quality Bar (C-1 to C-9)

The binding quality bar lives in `docs/research-scratch/GOVERNANCE.md` §CONSTRAINTS.md:
- **C-1**: No new suppressions (`@phpstan-ignore`, `eslint-disable`, `@ts-ignore`, `# noqa`)
- **C-2**: No stub bodies (`not implemented`, empty `catch {}`, TODO placeholders)
- **C-3**: No deleted/skipped tests without owner approval
- **C-4**: Lore gate enforcement (banned vocabulary)
- **C-5**: No engine write to `is_manual = true` rows
- **C-6**: No fact stored without provenance
- **C-7**: No matching on display strings (use normalized `match_key`)
- **C-8**: No fetch URL outside `config('uma.sources')` allowlist
- **C-9**: No business logic in controllers

## Hand-off Verification Sequence

Before reporting a change complete:
1. Targeted tests green
2. `php artisan test --compact`
3. `vendor/bin/pint --test --format agent <paths>` (never `--dirty`, KI-75)
4. `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
5. `npm run typecheck`
6. `npm run lint:ui`
7. `composer lore` and `composer lore-code` with ruling per hit
8. `composer audit` and `npm audit --omit=dev` (when dependencies changed)
9. `php artisan migrate:status` clean on dev database (when schema changed)

## Additional Resources

- **MCP Setup**: See `MCP_SETUP.md` for client configuration
- **Scenario Guides**: `docs/scenarios/01`, `02`, `03`, `07`, `08`, `09`
- **Skill Automation**: `app/Services/Skill{Registry,Matcher,Executor}.php` (separate tooling layer)
- **Path-scoped rules**: `.ai/rules/index.md` (code-style, eloquent, architecture, testing)
