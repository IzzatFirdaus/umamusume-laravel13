# Pre-Mortem Report: Legacy Consolidation & Modernization

Date: 2026-09-27. Produced before Artifacts 1-6, per the phase gate. Revision 0.1 covered repos 1-3; §4 is the revision 0.2 addendum for repo #4.

Sources examined: `D:/Projects/uma-companion`, `D:/Projects/uma-tracker`, `D:/Projects/umamusume-tracker-app`. Addendum source (read-only scan): `D:/umamusume_dev/uma_musume_race_planner`.

## 1. Over-Engineering Audit

Threshold applied (constraint-driven-development): every class must map to a PRD user story, or it does not ship.

| Legacy component | Source repo | Verdict | Reason |
|---|---|---|---|
| Breeze/Sanctum auth stack | uma-tracker, umamusume-tracker-app (dormant) | Cut | Local-only, single-Trainer tool. Auth is attack surface with zero utility. |
| Vue 3 + Pinia SPA frontend | uma-companion | Cut | Blade + Tailwind v4 already in this skeleton. A SPA pipeline for one local user is maintenance for nobody. |
| Breeding/lineage engine (pairing, eligibility, validation) | uma-companion | Cut, reduced | Maps to no user story in the other apps. The one useful fact (which two Umamusume provided inheritance) becomes two nullable FKs on `training_runs`. Its sire/dam vocabulary is also a lore violation. |
| EAV `attributes` table | umamusume-tracker-app | Cut | Five stats are fixed. Five integer columns beat a name/value table on every axis. |
| Excel export (`maatwebsite/excel`) | uma-tracker, umamusume-tracker-app | Cut | Heavy dependency for a local tool. CSV/JSON cover the need with zero dependencies. |
| Event/banner calendar + goal progress | uma-companion | Defer (Phase 1 Non-Goal) | Useful, but entirely dependent on volatile scraped schedule data. Shipping it first would make the most visible feature the most fragile. |
| Redis/queue server, multiple cache stores | implied by scale patterns | Cut | SQLite database cache + the queue worker from `composer run dev` is the ceiling this tool needs. |
| Idempotency keys, cursor pagination, OpenAPI generation | api-and-interface-design defaults | Trim | Single local consumer. Versioned `/api/v1`, one error shape, offset pagination. Nothing beyond. |
| Dual schemas (legacy EAV vs normalized target) | umamusume-tracker-app | Cut | One normalized schema from day one. No legacy DB migration promised. |

What survives, and why: catalog browsing (all three apps had it), training-run logging with per-turn stats (all three independently built it), skill acquisition tracking (two of three), the JP to Global cross-reference engine (stated core logic, absent everywhere), CSV/JSON export (cheap, Trainers already use it).

## 2. Failure Point Analysis

### Data-scraping fragility

Failure: source sites change markup, add rate limits, or block IPs; a parser written against today's DOM silently returns wrong data next month.

Mitigation: every fetch stores the raw snapshot on disk (`storage/app/private/snapshots`) before parsing, so a broken parser is fixed and re-run without re-fetching. Parsers are isolated per source behind one interface. Every stored fact carries provenance (`data_sources`: URL, fetched_at, snapshot path, confidence). Fetch failures never mutate existing rows: the pipeline stages, then promotes only on a confident match. Rows a human corrected (`is_manual = true`) are never overwritten.

Residual risk accepted: web-search cross-referencing is heuristic. The engine proposes, the Trainer disposes. Unmatched and low-confidence candidates land in a review list, not the catalog.

### JP to Global data discrepancies

Failure: romanization differences, localized renames, release lag (live on JP, absent on Global), skill translations that diverge from community naming.

Mitigation: matching runs on a normalized `match_key` (NFKD, case-folded, punctuation and middle-dot stripped) plus an explicit `umamusume_aliases` table, never on display strings. Match results are tiered (Exact / Alias / Fuzzy / None); only Exact and Alias auto-promote, Fuzzy goes to human review. `release_status` is a first-class enum (`GlobalReleased`, `GlobalAnnounced`, `JapanOnly`), so "JP only" is data, not an error.

Residual risk accepted: fan-community skill translations have no single authority. Skill cross-referencing is Phase 2, starting from Trainer-editable aliases.

### Cache-invalidation races

Failure: `composer run dev` runs web server and queue worker concurrently; a manual refresh during a scheduled fetch double-writes rows or double-burns a rate-limited source.

Mitigation: one writer at a time via `Cache::lock` around each per-source fetch (atomic claim), fetch jobs unique-queued per source. Catalog reads use TTL cache with stale-while-revalidate semantics: a refresh never blocks a read. For a single-user local tool this is the whole concurrency model.

### Filesystem/SQLite persistence risks

Failure: SQLite under concurrent writers throws `database is locked`; a crashed write corrupts the only copy of the Trainer's logged runs.

Mitigation: WAL journal mode + `busy_timeout` on the sqlite connection; all multi-write operations inside `DB::transaction`. Backup is a documented file copy after a WAL checkpoint (`uma:backup`). SQLite is the only supported production driver; no DB server is assumed anywhere.

## 3. Edge Case Identification

1. Identical JP/EN names vs divergent localizations. Some names romanize identically (Special Week / スペシャルウィーク); others were renamed in localization. `match_key` handles the identical case, alias rows the divergent case, review queue when neither fires. Seed data includes one of each so scaffolding tests cover both paths.
2. JP-only characters. `release_status = JapanOnly`, Global fields nullable, UI labels JP-sourced data explicitly ("Not yet released on Global") so a Trainer never plans around unavailable content. Debut dates are nullable dates, never sentinel values.
3. Unicode edge cases. Full-width vs half-width katakana, middle dot (・) in multiword names, prolonged sound mark (ー), combining marks. NFKD folds width variants; middle dot and punctuation are stripped for `match_key`, preserved in display names. SQLite lacks MySQL-style utf8mb4 collation, so matching always goes through the normalized key, never `=` on display strings.
4. Timezone handling. JP schedules are announced in JST; the Trainer lives elsewhere. Store UTC; convert JP-source datetimes from `Asia/Tokyo` at fetch time with the source timezone recorded in provenance; display in `config('uma.display_timezone')`. Date-only values (debut dates) stay dates, never datetimes, so no shift can move a release day.
5. Lore leaks from legacy data. Legacy repos contain a horse emoji (🏇) in UI and one schema uses an equine table name for characters (uma-companion). Banned-pattern grep (`horse`, `🏇`, `sire`, `dam`, `mare`, `foal`) runs in review; see CLAUDE.md.

## 4. Addendum (revision 0.2): `uma_musume_race_planner` [rev 0.2 — repo #4]

Fourth repository scanned read-only on 2026-09-27. Sections 1-3 above stand unchanged as revision 0.1. Despite its name, the repo is a career-run planner/tracker (turn-by-turn stats, skills, SP, mood/condition, per-plan dashboards), not a race simulator. It contains no simulation, randomness, or prediction engine; its "predictions" are user-entered aptitude grades. It also carries CSV/JSON importers for a fifth legacy app (`uma-run-tracker`), so the consolidation target count is four repos plus one data-format reference.

### 4.1 Over-engineering found in repo #4 (verdicts)

| Component | Verdict | Reason |
|---|---|---|
| Dual storage modes (browser localStorage "local runs" vs account/DB runs) + conversion service | Cut | A local-only tool has exactly one store: SQLite. Dual-store parity is pure liability, and browser-only data is one cleared cache away from loss. |
| Sanctum auth + login/register routes + hardcoded "Public User" id=1 | Cut | Same verdict as §1 row 1. The id=1 FK assumption is a migration trap, not a feature. |
| Livewire 3 component tree (~20 components) | Cut | Not installed here; Blade + vanilla JS is the stack (ARCHITECTURE §7). Carrying Livewire would add a dependency for zero new capability. |
| Race-day snapshots (immutable `career_snapshots` + SnapshotService) | Cut | Speculative archival of data SQLite already persists. CSV/JSON export (US-6) covers the real need: getting data out. |
| Manual race predictions table (`race_predictions`, venue/ground/aptitude grades) | Cut (Phase 1) | No race/calendar entity exists in the unified schema; predictions hang off free text. Revisit only if US-10 (P2) is ever scheduled. |
| Trainee image upload + ImageProcessingService | Cut | No user story; adds an upload attack surface to a tool that otherwise accepts only form fields and fetched text. |
| Excel/Markdown export paths | Cut | §1 row 5 stands. CSV/JSON only. Note: repo #4's README cites Laravel Excel but the dependency is absent from its composer.json; the claim was already dead. |
| DB-level enum columns (career_stage, class, status on `plans`) | Cut | Repo #4's own migration risk: enum DDL complicates SQLite/MySQL portability. Unified schema uses string columns + PHP backed enums (ARCHITECTURE §3). |
| Soft deletes on plans | Cut | Domain rule: no soft deletes; Trainer-data deletion is explicit and cascades. |
| JSON-typed columns on `umamusume` (growth_rates, aptitudes, base_stats) | Defer (INVESTIGATE) | Useful planning data, but engine-owned facts must arrive through the fetch pipeline with provenance, not hardcoded seeders. Phase 2 candidate once sources (OQ-2) are chosen. No column added now. |
| Turn tracker + per-turn stat logging (StatProgressService) | Keep, merged | Independently validates the `turn_entries` design. One concrete gain adopted: deterministic stat bounds (0..1200) become validation rules in `StoreTurnEntryRequest`. |
| Skill 3-state status (Acquired / Skipped / Suggested) | Keep, refactored | `SkillAcquisition` enum gains `Suggested` (planned-but-not-yet-taken), matching how planners actually work: plan vs actual comparison (US-4). |
| CSV/JSON legacy importers (MigrateLegacyCsv/Json, FormatDetector) | INVESTIGATE | Real, working reference code for the `uma-run-tracker` JSON shape. Not carried in (Non-Goal 7 stands for Phase 1); located and cited if the Trainer later asks for import. |

### 4.2 New failure points

**Date/time calculation errors.** Repo #4 hardcodes `Asia/Kuala_Lumpur` in `config/app.php` while its domain data is JP-sourced; any date-sensitive logic silently shifts by 1-2 hours versus JST and by a day near midnight boundaries. Mitigation (already designed, now load-bearing): store UTC, record `source_timezone` per fact, convert JP datetimes from `Asia/Tokyo` at parse time, keep date-only values as `date`, display via `config('uma.display_timezone')`. The app timezone stays UTC; no locale-specific hardcoding.

**Planner recalculation after data updates.** A run's turns and skills reference catalog rows; when a fetch updates or merges catalog data, existing runs must not silently change meaning. Mitigation: runs reference `skill_id`/`umamusume_id` FKs (display follows the catalog, history of entered numbers never mutates); candidate merges that would move FKs go through the human review queue (`match_candidates`), never auto-merge; `is_manual` rows are immutable to the engine. There are no derived/cached calculations over catalog data, so there is nothing to recalculate: all run math (deltas, totals) is computed from Trainer-entered `turn_entries` at render time, deterministically.

**Browser-only data loss.** Repo #4's localStorage mode means authoritative run data can exist only in a browser profile. Mitigation: single store (SQLite), and `uma:backup` (NFR-5) is the documented durability path.

### 4.3 New edge cases

1. Stat bounds. Repo #4 encodes MAX_STAT_VALUE = 1200, MIN 0, and 70-78 turn careers. Adopted as validation bounds (0..1200 per stat, turn >= 1) rather than free integers; bounds live in one Form Request, not scattered.
2. `Suggested` skills on a run. A planned skill has no `turn_acquired`; UI and export must distinguish "planned" from "acquired turn N" and "skipped".
3. Lore violations in repo #4 to never copy: "racehorses" in two character-list views, 🏇 in its README feature list, "horse" in a skill description seeder and legacy docs, "horse girl" in its BRD. All fail the banned-pattern grep; replacements are "Umamusume"/"umamusume" per CLAUDE.md Lore Rules.
4. String-slug primary keys on its `umamusume` table vs bigint FKs elsewhere. Unified schema keeps bigint PK + unique slug; if its seed data is ever imported, slugs map to `slug` column values, not PKs.
