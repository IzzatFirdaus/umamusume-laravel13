# ARCHITECTURE-ESSENTIALS

Token-efficient digest of ARCHITECTURE.md for agent context injection. If this file and ARCHITECTURE.md disagree, ARCHITECTURE.md wins.

## Stack (pinned, installed 2026-09-27)
- PHP 8.5.8, laravel/framework 13.32.0, SQLite only (WAL + busy_timeout), `database` cache + queue stores
- Pest 4.7.8 (feature-first), Larastan 3.12.1 level 6, Pint 1.32.1 (`pint.json`)
- Tailwind v4 CSS-first (`@theme` in resources/css/app.css; NO tailwind.config.js), Vite 7, Blade, vanilla JS
- Forbidden deps (cut in Pre-Mortem): sanctum, breeze, maatwebsite/excel, SPA frameworks, Redis

## Product shape
- Local-only single-Trainer tool: NO auth, NO multi-user, NO public deploy, NO telemetry
- Domains: catalog (Umamusume + skills + provenance), trainer data (runs/turns/skills), fetch engine (JP↔Global cross-reference)
- Web = Blade; JSON = read-only /api/v1 (P2); export = CSV/JSON only

## Lore (hard rule, details in CLAUDE.md)
- Characters are Umamusume (humanoid race; singular=plural). Never "horse(s)", never animal framing, never sire/dam/mare/foal, no 🏇
- Table `umamusume` (invariant plural); model `Umamusume`

## Schema (snake_case; enums TitleCase backed)
- umamusume: slug uniq, name (EN), name_ja, match_key (NFKD-normalized, indexed), release_status (GlobalReleased|GlobalAnnounced|JapanOnly), jp_debut_date?, global_debut_date?, is_manual (engine never overwrites)
- umamusume_aliases: umamusume_id FK, alias, language (Japanese|English|Romanized), uniq(alias, language)
- skills: name, name_ja?, match_key?, sp_cost?, type?, is_unique
- data_sources: umamusume_id FK, url, source_key, fetched_at, snapshot_path?, confidence?, source_timezone? (IANA)
- match_candidates (review queue): source_key, proposed_name/_ja/_match_key, suggested_umamusume_id?, match_tier (Fuzzy|None), status (Pending|Confirmed|Aliased|Rejected), payload json
- training_runs: umamusume_id FK, scenario?, status (Active|Completed|Retired), inheritance_parent_a_id?, inheritance_parent_b_id?, notes?
- turn_entries: training_run_id FK cascade, turn, speed/stamina/power/guts/wit, sp?, condition?, uniq(run, turn); stats validated 0..1200, turn >= 1 [rev 0.2 — repo #4]
- run_skills pivot: status (Suggested|Acquired|Skipped), turn_acquired? — Suggested = planned pre-run [rev 0.2 — repo #4]
- users/cache/jobs = framework defaults, users unused
- Planner (repo #4) maps onto training_runs + turn_entries + run_skills; its snapshots/race-predictions/dual-storage/image-uploads are cut, no new tables; growth-rate/aptitude data deferred to PRD OQ-4 [rev 0.2 — repo #4]

## Fetch engine (app/Services/DataPipeline)
- Stages: fetch → snapshot (storage/app/private/snapshots, raw body, hashed) → parse (per-source class, Contracts\SourceParser) → normalize → match → promote | review
- NameNormalizer (pure): intl NFKD, mb_strtolower, strip ・/-/spaces/combining marks, collapse whitespace; display names never mutated
- CrossReferenceMatcher tiers: Exact (match_key =) | Alias (alias hit) → auto-promote; Fuzzy (levenshtein ≥ config threshold, default 85%) | None → match_candidates only
- Promotion: upsert engine-owned columns, skip is_manual, one data_sources row per fact, DB::transaction per batch
- Idempotent: unchanged snapshot hash short-circuits; re-run writes nothing
- Concurrency: Cache::lock("uma-fetch:{source}") + ShouldBeUnique FetchSourceJob (uniqueId=source_key); web refresh dispatches and returns (stale-while-revalidate)
- Timezone: JP datetimes parsed Asia/Tokyo → stored UTC, source_timezone recorded; date-only stays date
- Commands: uma:fetch {source}, uma:reparse {source} (from snapshots, zero network), uma:backup (WAL checkpoint + file copy)
- HTTP: allowlisted hosts from config('uma.sources') ONLY (SSRF), limited redirects, per-source delay_ms/timeout_s, retry backoff max 2, descriptive UA

## API contract (/api/v1, P2)
- GET umamusume?status=&search=&page=&pageSize= ; GET umamusume/{slug} ; GET training-runs ; GET training-runs/{id}
- Shape: { data: ..., pagination: { page, pageSize, totalItems, totalPages } } on lists
- Error (all non-2xx): { error: { code, message } }; 400/404/422/500; 500 generic, details to log
- camelCase params+fields, plural nouns, no verbs, offset pagination, additive changes only, API Resources + PHPDoc array-shapes

## Caching
- Catalog reads: Cache::remember, TTL config default 15 min, keys include catalog:version counter bumped on promotion (versioned-keys invalidation)
- Trainer-data reads: never cached

## Security posture
- No auth surface; loopback serve only. Untrusted input = fetched pages (data-only parsing, Blade auto-escape, no {!! !!} for source data) + Trainer forms (Form Requests)
- No secrets in Phase 1; future keys go in .env only
- composer audit / npm audit before tagging a build

## Conventions (from AGENTS.md domain rules; non-negotiable)
- declare(strict_types=1); explicit param+return types; #[Fillable]/#[Hidden] attributes; casts() as method; HasFactory + factory per model
- Thin controllers; logic in Actions (app/Actions) / Services; Form Requests for all validation; events only from Actions/Services
- Named routes + route() helper; API Resources for JSON; anonymous migration classes
- Config via config(); env() only in config files; php artisan make:* with --no-interaction
- loadMissing/with against N+1; DB::transaction for atomic multi-writes; no soft deletes
- Pint --dirty after PHP edits; PHPStan level 6 clean; Pest tests for behavior changes (read testing-best-practices skill first); Http::fake in all fetcher tests, no network in tests
- Comments: PHPDoc over inline; no restating-the-obvious comments (antislop-code)

## Directory delta
- app/{Actions, Console/Commands, Enums, Services/DataPipeline(+Contracts)}
- app/Http/{Controllers(+Api/V1), Requests, Resources}
- app/Models/{Umamusume, UmamusumeAlias, Skill, TrainingRun, TurnEntry, DataSource, MatchCandidate, RunSkill (pivot)}
- config/uma.php; database/{factories, migrations, seeders}; storage/app/private/snapshots (gitignored)
- tests/Feature/{Catalog, TrainingRun, ApiV1, FetchPipeline, CrossReferenceMatcher}; tests/Unit/NameNormalizer (pure)

## Phase-1 non-goals (do not build without new PRD scope)
- Auth/multi-user; SPA; breeding engine (inheritance = 2 nullable parent FKs only); EAV; Excel; event calendar; legacy DB import; MySQL/PG; support cards; deploy paths
- [rev 0.2 — repo #4] race simulation/predictions/snapshots; dual storage modes or browser-side authoritative data; trainee image uploads; DB-level enum columns

## Key doc citations
- HTTP client: https://laravel.com/docs/13.x/http-client ; Locks: https://laravel.com/docs/13.x/cache#atomic-locks
- Resources: https://laravel.com/docs/13.x/eloquent-resources ; SQLite: https://laravel.com/docs/13.x/database#configuration
- Queues/unique jobs: https://laravel.com/docs/13.x/queues ; NFKD: https://www.php.net/manual/en/normalizer.normalize.php
