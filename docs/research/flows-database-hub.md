# Flows — Database Hub

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Http/Controllers/DatabaseController.php`, `routes/web.php` (the `/database` block), `resources/js/pages/Database/*.vue`.

A read-only reference hub: nine tiles leading to nine areas plus the data-source ledger. Nothing here writes.

---

## 1. Hub (`/database`, `database.index`)

### 1.1 One grouped query

The hub answers all four live areas in a single `UNION ALL` over four tables, each with two figures:

```sql
select 'trainees' as area, count(*) as total,
       sum(case when release_status = 'GlobalReleased' then 1 else 0 end) as scoped from umamusume
union all select 'supports', count(*), sum(case when release_status = 'Global' then 1 else 0 end) from support_cards
union all select 'skills', count(*), sum(case when release_status = 'GlobalReleased' and name_is_client = 1 then 1 else 0 end) from skills
union all select 'races', count(*), count(*) from race_catalog_slots
```

The **scoped** figure is what the tile prints, because that is what the door leads with; the raw count rides beside it only where the two differ. The three scoped arms repeat each door's own default predicate — `CatalogController::indexProps`, `SupportCardController::databaseIndex`, `Skill::scopeAvailableOnGlobal` — so the hub and the door always agree. Races has no scope, so both figures are the same and the tile prints one.

This replaced a defect (F-03) where the Skills tile said 1,910 while the Skills door said 623, because the door scopes to Global-released rows carrying a client name and the hub named no scope at all.

### 1.2 Tiles

| Tile | Door | Source label | Count | Tag |
| --- | --- | --- | --- | --- |
| Trainees | `database.trainees` | Fetched | scoped / recorded | — |
| Support Cards | `database.supports` | Fetched | scoped / recorded | — |
| Skills | `database.skills` | Fetched | scoped / recorded | — |
| Races | `database.races` | Fetched | recorded (no scope) | — |
| Events | `database.events` | Transcribed | null | Reference |
| Scenarios | `database.scenarios` | Config | `count(config('scenarios.scenarios'))` | — |
| Shop Items | `database.shop-items` | Transcribed | null | Trackblazer |
| Sparks | `database.sparks` | Transcribed | null | Inheritance |
| Data sources | `database.sources` | Config | null | Ledger |

A transcription has no table to count, so it prints the word (Reference / Trackblazer / Inheritance / Ledger) instead of a number no table can corroborate. `Data sources` was the ninth area and had no door at all until UX audit F-14; the hub is the only index of Database screens, so leaving it out made the page URL-only.

`matrix` carries `{state, verified_at, source}` from `config('scenarios.stale')`, `verified_at`, `source`.

---

## 2. Areas

### 2.1 Trainees (`/database/trainees`)

`CatalogController::databaseIndex` renders `Database/Trainees` with the same props the catalogue index builds. The pager and filter form write back to `route('database.trainees')`, not `/umamusume`.

### 2.2 Supports (`/database/supports`)

`SupportCardController::databaseIndex` renders `Database/Supports`, same query and row mapper at `SUPPORT_CARDS_PER_PAGE = 25`.

### 2.3 Skills (`/database/skills`)

`SkillController::databaseIndex` renders `Database/Skills` with the scope select intact.

All three ported areas stay under `/database/` so the hub has one URL space, but the controller that owns the query renders the page. `/umamusume`, `/skills` and `/support-cards` are unchanged.

### 2.4 Races (`/database/races`, `/database/races/{id}`)

`DatabaseController::races` reads `race_catalog_slots` — the shared `[Global]` career calendar, 410 rows on the audited database.

Filters (`database-races.html:72-77`): `q` (matches `title`), `grade` (`tier`), `surface`, `distance` (`distance_band`), plus `pageSize` (`PageSize::clamp`, default 25). Every facet reads a column the table already stores; nothing is derived.

Column set is what the source states and no more: `tier` already carries the client's own word (`G1`, `OP`, `Maiden`, `Debut`) and `grade_code` is its numeric twin, one-to-one across all 410 rows, so printing both would state one fact twice. `scenario_key`, track and race ids and fetch provenance are held off the table for the same reason.

**No cache**, deliberately: `catalog:version` is bumped by promotion, and this table is written by a fetch rather than by promotion, so a cached page would keep answering after a re-fetch. 410 rows is a query cheap enough to run.

`raceDetail(int $id)` renders `Database/RaceDetail` for one slot.

### 2.5 Scenarios (`/database/scenarios`)

`DatabaseController::scenarios` renders `Database/Scenarios` from `config('scenarios.scenarios')` — caps, panels, `live_on_global` dates, rulesets. Config is the source of truth here; no table holds a scenario.

### 2.6 Events (`/database/events`)

`DatabaseController::events` renders `Database/Events` — transcribed reference rows for recurring live-event types and their cadence. No table.

### 2.7 Shop items (`/database/shop-items`)

`DatabaseController::shopItems` renders `Database/ShopItems` — costs, effects and one publisher's worth ratings for the Trackblazer Pro Shop. Transcribed into config; no table.

### 2.8 Sparks (`/database/sparks`)

`DatabaseController::sparks` renders `Database/Sparks` — the blue, pink, green and white factor families and their grades. Transcribed; no table.

### 2.9 Data sources (`/database/sources`)

`DatabaseController::sources` renders `Database/Sources` — every dataset, when it was read, and what it fed. The reference views link here instead of printing a source path and a section number a Trainer cannot open (slice 2.2). This is also the Help screen's answer to "a date looks old".

---

## 3. Read-only contract

No `/database/*` route mutates anything. The Database hub is the reference shelf, not an editor:

- Reference tables expose no form.
- The three ported catalogue areas expose the catalogue's filter controls, which write to the query string only.
- Data enters through `uma:fetch`, `uma:reparse`, `uma:import:support-cards` and the committed seeders — never through this hub.

---

## 4. Verification surface

| Claim | Pinned by |
| --- | --- |
| Hub counts and scoped figures | `DatabaseIndexCountsTest`, `DatabaseTest` |
| Sources door present | `DatabaseHubSourcesLinkTest` |
| Portable areas share one screen | `CatalogRosterTreeTest`, `SupportCardPageTest`, `SkillSearchScreenTest` (each covers both URLs) |
| Race table columns and filters | `DatabaseTest`, `RaceCatalogFetchTest`, `TierLabelJoinTest` |
| Route coverage | `RouteCoverageTest` |
| Browser | `tests/browser/database*.spec.ts`, `race-detail.spec.ts` |
