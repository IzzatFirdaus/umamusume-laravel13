# Slice 4 pre-flight — historical run import

Date: 2026-10-01. Read-only pass; no code written. Each answer is measured against the tree, not assumed
from the brief.

## A. `ApiV1ValidationEnvelopeTest` — resolved to **option 2: no API write endpoint**

The pinned assertion (`tests/Feature/ApiV1ValidationEnvelopeTest.php:69`) is not a comment, it is a filter
over the live route table:

```php
it('leaves the 422 branch unreachable, because the api has no write', function (): void {
    // …
    // Every route is a read. The moment one is not, VALIDATION_ERROR becomes reachable
    // through a FormRequest and this test is wrong - which is the point of asserting it.
    expect($routes)->not->toBeEmpty()
        ->and($routes->filter(fn ($route): bool => ! in_array($route->methods()[0], ['GET', 'HEAD', 'OPTIONS'], true)))
        ->toBeEmpty();
});
```

Two things settled the choice, and only one of them is this test:

- **`PRD.md` FR-E-1 scopes the API to reads**: "Versioned `/api/v1` **read** endpoints for catalog and runs".
  US-9 says "I **query** a local JSON API". There is no write requirement anywhere in §4.
- **`AGENTS.md`**: "Every new table, column, or class must cite a PRD requirement (FR-x / US-x). No citation,
  no merge." `POST /api/v1/training-runs/import` has no citation. Creating it would mean amending FR-E-1
  from "read" to something wider, which is an owner scope change, not a slice implementation detail.

So the test's premise stands and its comment gains one line saying the write surface was considered by
Slice 4 and declined on the PRD citation, not overlooked. Option 3 was checked and does not exist: nothing
about a file upload makes a route "out-of-band"; it is still an `api/` route and the filter still sees it.

**Consequence the import must honour:** it is a web form under `routes/web.php`, and every Trainer-owned write
in this app already lives there (`syncSkills`, `storeRace`, `storePurchase`, `storeTurn`, `syncDeck`).

## B. Field-by-field mapping — every sheet field has a home; one has no *format* column

Source of the inventory: `docs/deprecated/REVIEW-2026-09-30.md:308` — "Each sheet carries: stat values with
grade letters, skills with SP costs and acquired/skipped marks, mood tier, energy value, goal, and a
conditions row." Column lists read from the migrated schema.

| Sheet field | Lands in | Status |
|---|---|---|
| five stats | `turn_entries.speed/stamina/power/guts/wit` | holds; validated per scenario ceiling (Slice 1 / ADR-0015) |
| **stat grade letters** | nowhere, **by design** | Derived. `config('scenarios.php')` `grade_banding` is `'provisional' => true` with its step printed beside the badge, because no source defines a client stat grade. Importing a legacy letter would store this tool's own provisional scale as if it were the Trainer's observation. |
| SP | `turn_entries.sp` | holds; non-negative, uncapped (ADR-0015) |
| conditions row | `turn_entries.condition` | holds, nullable, 255 chars |
| mood tier | `turn_entries.mood` → `MoodTier` | holds, **with a casing note**: the enum's backing values are the client strings uppercase (`GREAT/GOOD/NORMAL/BAD/AWFUL`) and the sheets write them lowercase. Import maps case-insensitively and leaves an unknown word null rather than failing the row. |
| energy | `turn_entries.energy` | holds, 0–100 (ADR-0001) |
| fans | `turn_entries.fans` | holds, non-negative |
| skills + SP cost + acquired/skipped | `run_skills.status` + `turn_acquired`; SP cost is on `skills` | **has a home but is out of scope for the import** — see below |
| goal | `race_entries.scenario_slot_id` / `objective_index`, `training_runs.current_objective_index` | holds |
| which costume card | `training_runs.character_card_id` | holds |
| deck | `deck_slots` (Slice 2 / ADR-0014) | holds; not on the sheets |

**Skills: named out of scope, not dropped silently.** Resolution needs a name→id lookup, and unresolved names
are exactly what `PRD.md` US-5's review queue exists to adjudicate. Importing by best-match would assign a
`skill_id` the source never stated, which fails the provenance floor. So the import writes turns and the run
header, and the preview screen says in words that skills are not imported and stay editable per-turn on the
run page.

**The format is the export's own header**, because a round trip is the test the brief asks for:
`turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans` (`TrainingRunController::export()`). One run
per file; run identity comes from the form, since a turn CSV carries no trainee.

**Two candidate formats, and why CSV.** JSON is what `TrainingRunResource` already emits and would round-trip
skills and deck for free, so it is the lazier parser. It is rejected because the stated source is **seventeen
paper sheets** — a Trainer transcribing those types into a spreadsheet, not authors JSON. CSV serves the real
use; JSON stays the trivially-addible second shape.

## C. `imported_at` / `import_source` — no collision, so they are the first migration

`git grep -n "imported_at\|import_source" -- app database config` → **no matches**. The names are free.

Not to be confused with two existing pairs, which is the reason this check was worth running: `is_manual`
(a hand correction the engine must not overwrite, FR-B-4) and `source_url`/`fetched_at` (a fetched reference
row's provenance). An imported run is neither: it is Trainer-authored data that arrived by file rather than
by keystroke, so it needs its own pair on `training_runs`.

## Informational

- **No existing bulk-run-creation path to reuse.** `TrainingRunController::store()` creates one run from one
  validated request inside a transaction; `app/Actions/Store*` are all fetch-pipeline ingests of reference
  data, per the naming note in `ADR-0014` — `Store*` means "persist ingested catalogue rows", not "handle a
  Trainer form". The import action therefore follows `store()`'s transaction shape and is named
  `ImportHistoricalRun`, not `StoreRun`.
- **`scenario` normalisation is a live requirement, not a niceness.** Slice 2's browser pass found that a run
  whose `scenario` column is the empty string 500s the run page until `filled()` was introduced
  (`TrainingRun::hasScenario()`). An import path takes external strings, so it must map `''` to `null` before
  writing rather than trusting the FormRequest normaliser that the web form route runs.

## What Slice 4 therefore builds

ADR-0017 · one migration adding `imported_at`/`import_source` to `training_runs` · `ImportHistoricalRun`
action · a web import page with preview-before-commit · a one-line "imported" indicator on the run screen ·
tests that export a run and import it back · a browser pass on a scratch database. **No API endpoint.**
