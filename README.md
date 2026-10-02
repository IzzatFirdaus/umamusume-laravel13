# Trainer Desk

Product name confirmed 2026-09-27 (PRD OQ-1 closed). Visual system: `DESIGN.md`.

A local-only Laravel 13 tool for Trainers of the Global English version of *Umamusume Pretty Derby*. It consolidates four legacy apps (three trackers plus a career-run planner) into one machine: browse a catalog of Umamusume whose JP and Global data is cross-referenced by a fetch engine, log training runs turn by turn, compare planned vs actual skills, and export. SQLite only, single user, no accounts, no telemetry.

The characters are Umamusume, a humanoid race. Repository text and code never use equine vocabulary for them (lore gate, `CONSTRAINTS.md` C-4).

## Documentation map

| File | Role |
|---|---|
| `PRD.md` | Product truth: users, stories, functional requirements, non-goals, open questions |
| `PRODUCT.md` | Product summary for design and agent context (name, audience, commitments) |
| `DESIGN.md` | Visual system contract: theme, tokens, components, surface specs |
| `ARCHITECTURE.md` | Full system design: schema, fetch engine, API contract, security model |
| `ARCHITECTURE-ESSENTIALS.md` | Token-efficient digest of the above for agent context |
| `docs/research-scratch/GOVERNANCE.md` §"PRE-MORTEM.md" | Risk record; §4 covers the fourth (planner) repository |
| `CONSTRAINTS.md` | Quality bar with thresholds and commands; read before writing code |
| `AGENTS.md` | Agent roles, escalation paths, Laravel Boost guidelines |
| `CLAUDE.md` | Coding rules for assistants (gitignored by design, machine-local) |

## Requirements

- PHP >= 8.3 (`composer.json`; developed on 8.5) with `pdo_sqlite` and `intl`
- Composer, and Node.js with npm for the Vite/Tailwind build
- No database server: SQLite in `database/database.sqlite` (WAL mode)

## Setup

```bash
composer setup        # install deps, .env, key, migrate, npm install + build
php artisan migrate:fresh --seed   # illustrative catalog data (seeder headers explain scope)
composer dev          # serve + queue worker + pail + Vite HMR on loopback
```

Keep the app on loopback; it has no auth surface and must not be exposed.

## Web surface

| Route | Purpose |
|---|---|
| `/umamusume` | Catalog index: filter by release status, normalized search |
| `/umamusume/{slug}` | Detail with aliases and provenance (source URL + fetched date) |
| `/skills` | Skill search (Screen D): normalized search, derived-type and unique facets, paginated |
| `/training-runs` | Trainer run CRUD, per-turn stat logging (0..1200 bounds), skill states Suggested/Acquired/Skipped |
| `/training-runs/{run}/export/csv|.json` | Run download, no data lock-in (US-6) |
| `/review` | Match review queue: confirm, alias, or reject engine proposals |
| `/design-preview` | Component review surface for the design system (not a product flow) |

**`/skills` reads fetched data, not seed data, and says which it has.** `php artisan migrate:fresh --seed`
leaves it stating that the catalog holds no rows yet: the seeder's ten illustrative names carry no
availability from a source, and `Skill::availableOnGlobal()` (PRD FR-D-3, `ADR-0011` §2) is what decides
that a row may reach a Trainer. Run `php artisan uma:fetch gametora-skills` to fill it — 623 `[Global]`
rows of 1,910 stored, as of the `609afe88` snapshot pulled 2026-09-29.

If that reports **unchanged since last snapshot** while the screen is still empty, use
`php artisan uma:reparse gametora-skills` instead. The snapshot short-circuit keys on the document's hash
under today's date in a `storage/` directory shared by every database in the working tree, so the second
database asked the same day is told nothing changed and stays empty (KI-27). Reparse reads the same stored
snapshot with no network and is safe on a database that already holds the rows, because the writer upserts
on `export_id`.

## Fetch engine

Sources live in `config/uma.php`. **Three are declared today** — `gametora-characters`, `gametora-race-catalog`, and `gametora-skills` (the last resolved through the publisher's manifest with the pinned URL kept as the documented fallback; `docs/adr/0011-skills-reference-import.md`). The line this file carried before named one source, and that was simply stale. Adding any source requires owner approval plus a robots/rate-limit review and one parser class; the robots/live check on `gametora-characters` is still outstanding (PRD OQ-2):

```php
'sources' => [
    'gametora-characters' => [
        'url' => 'https://gametora.com/data/umamusume/character-cards.<hash>.json',
        'parser' => GametoraCharacterParser::class,
        // plus politeness keys: delay_ms, timeout_s, timezone
    ],
],
```

```bash
php artisan uma:fetch [source]    # fetch -> snapshot -> parse -> match -> promote|review
php artisan uma:reparse <source>  # replay from stored snapshots, zero network
php artisan uma:backup [path]     # WAL checkpoint + consistent file copy (NFR-5)
```

## Local API (read-only, P2)

```bash
GET /api/v1/umamusume?status=GlobalReleased&search=special%20week&page=1&pageSize=25
GET /api/v1/umamusume/{slug}
GET /api/v1/training-runs[/{id}]
```

List shape: `{ "data": [...], "pagination": { "page", "pageSize", "totalItems", "totalPages" } }`.
Every non-2xx: `{ "error": { "code", "message" } }` (rendered centrally in `bootstrap/app.php`).

## Module map

```
app/Actions/          PromoteMatchedRecord (upsert + provenance, is_manual safe),
                      ResolveMatchCandidate (review verdicts)
app/Console/Commands/ UmaFetch, UmaReparse, UmaBackup (+ ManageSkills, see below)
app/Enums/            ReleaseStatus, RunStatus, SkillAcquisition, MatchTier,
                      CandidateStatus, AliasLanguage (TitleCase backed, DB stores values)
app/Http/Requests/    StoreTrainingRunRequest, StoreTurnEntryRequest (stat bounds),
                      StoreRunSkillRequest, ResolveMatchCandidateRequest
app/Http/Resources/   UmamusumeResource, TrainingRunResource, TurnEntryResource (camelCase)
app/Jobs/             FetchSourceJob (unique per source on the database queue)
app/Models/           Umamusume, UmamusumeAlias, Skill, TrainingRun, TurnEntry,
                      DataSource, MatchCandidate, RunSkill (pivot)
app/Services/DataPipeline/
                      SourceFetcher (only outbound HTTP path), NameNormalizer (NFKD match keys),
                      CrossReferenceMatcher (Exact/Alias/Fuzzy/None tiers),
                      PipelineRunner (shared stage loop), Contracts/SourceParser
database/             migrations per ARCHITECTURE §3; factories for all models;
                      seeders labeled illustrative
resources/views/      Blade + layout component, Tailwind v4 (@theme in resources/css/app.css)
tests/                Pest: Feature (catalog, runs, API, pipeline, matcher) + Unit (normalizer)
```

## Skill automation subsystem (pre-existing, unrelated to the Uma domain)

`app/Services/Skill{Registry,Matcher,Executor}.php` and `php artisan skill:manage`
drive the declarative skills in `.agents/skills.json` (see `.agents/README.md`).
They touch none of the catalog/run tables; treat them as a separate tooling
layer with its own docs.

## Quality gates

| Gate | Command |
|---|---|
| Tests | `composer test` or `vendor/bin/pest --compact` |
| Style | `composer lint` (check) / `vendor/bin/pint --dirty --format agent` (fix) |
| Static analysis | `composer analyse` (PHPStan level 6; needs `--memory-limit=1G` on a 128M CLI default) |
| Lore grep | `make lore` / `composer lore`, and `make lore-code` / `composer lore-code` (hits need a context ruling, CONSTRAINTS.md) |
| Migrations | `php artisan migrate:fresh --seed` |

The full bar, including the no-suppression floor, is `CONSTRAINTS.md`.
