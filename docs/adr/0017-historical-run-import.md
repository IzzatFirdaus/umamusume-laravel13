# ADR-0017: A historical run imports as the CSV this app exports, through a web form

Status: **Accepted (Slice 4, built 2026-10-01).** Realises the import half of `PRD.md` US-12's neighbouring
concern without amending any non-goal. Depends on `ADR-0015` for the ceilings it validates against and
`ADR-0014` for the deck it deliberately does **not** fill. The pre-flight that produced its scope is
`docs/design-research/slice-4-preflight-2026-10-01.md`; this file records what was decided after that.

## Decision

A Trainer's finished career arrives as **one file, one run, two POSTs**:

1. `POST /training-runs/import/preview` validates and shows every row.
2. `POST /training-runs/import` re-validates the same bytes and writes.

The accepted header is the eleven columns `TrainingRunController::export()` emits —
`turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans` — and nothing else. The format was not
designed for the import; it was **inherited from the export**, which makes the round trip the test of the
feature rather than a claim about it.

### The write surface is web-only, and that was checked rather than assumed

`tests/Feature/ApiV1ValidationEnvelopeTest.php:69` asserts every `api/` route is a read, which keeps the
`VALIDATION_ERROR` envelope branch unreachable. Adding `POST /api/v1/training-runs/import` would break it,
so the question was whether that test is load-bearing or incidental. It is incidental. The reason is the
PRD: **FR-E-1 scopes the API to reads** ("Versioned `/api/v1` **read** endpoints for catalog and runs") and
US-9 says "I **query** a local JSON API". No write requirement exists anywhere in §4, and `AGENTS.md`
requires every new class to cite one. So there is no API endpoint, and the test's premise stands for a
reason that outlives this slice. Every Trainer-owned write already lives in `routes/web.php`.

### Turns only, and the three things left out are named on screen

Skills, deck slots and race entries have columns in this schema and are **not** in the file format. The
preview page says so in words (`resources/views/runs/import.blade.php`, the `role="note"` line).

Skills were the tempting omission to paper over: a name→id lookup with best-match would fill the panel.
It was refused because an unresolved name is exactly what `PRD.md` US-5's review queue exists to
adjudicate, and importing by best guess would attach a `skill_id` the source never stated — a provenance
violation, not a shortcut.

### `imported_at` and `import_source` are a new pair, deliberately not a borrowed one

Both are nullable with no default, so typed runs — the overwhelming majority — say nothing.

| Rejected alternative | Why |
|---|---|
| Reuse `created_at` | An import writes the row today. `created_at` is when the record entered the database; `imported_at` is what the Trainer asserts about the run's own past. Collapsing them makes every imported run claim it was created at import time — true of the row, false of the history. |
| Reuse `is_manual` | Marks a row the **fetch engine** must never overwrite (FR-B-4). An imported run is neither a hand-correction nor a fetch. |
| Reuse `source_url`/`fetched_at` | That pair carries a *fetched reference row's* origin. Nothing here was fetched. |

The names were grepped before being chosen (`git grep import_at\|import_source` → no matches).

### Every stat is measured by the run's own scenario

`ImportHistoricalRunRequest` builds its per-column bounds from `ScenarioCaps::forRun()` — the single owner
established by `ADR-0015` — against a transient run carrying only the submitted scenario. An import is
therefore refused for the same number the turn form and the stat band would refuse it for, and a run
naming no scenario is measured at the base cap with no bonus. Nothing in the import re-derives a ceiling.

### Two normalisations the file forces, and one limit it accepts

- **UTF-8 BOM and CRLF.** Excel writes both. Stripping the BOM is load-bearing: an unstripped one makes the
  first header cell read as `"\xEF\xBB\xBFturn"`, which rejects a file this application itself produced.
- **Mood is upper-cased, not defaulted.** `MoodTier`'s backing values are the `[Global]` client strings
  verbatim and those are uppercase, while a paper sheet writes `good`. An unrecognised word is left as-is
  so the enum rule rejects it **with its own message** rather than becoming null silently. This is a
  deliberate divergence from the pre-flight, which proposed mapping an unknown word to null and keeping
  the row: a blank mood is a false statement about the run, and the Trainer can see which row said it.
- **A comma inside `condition` makes the file unimportable.** `export()` joins with bare commas and does
  not quote, so such a value arrives as a twelfth cell. The row-length check rejects the file rather than
  reading `fans` into `energy` by position. This is the export's limitation inherited, not a new one;
  quoting on write is the fix and it belongs to `export()`, not to the parser.

`str_getcsv()` is called with its `$escape` argument explicit. PHP 8.5 deprecates relying on the default,
and an empty escape is the correct reading of a writer that emits no backslash quoting — passing it keeps
the parser symmetrical with the emitter instead of merely silencing a notice.

## Consequences

- `training_runs` gains two nullable columns. No existing row changes; no backfill is possible, since an
  older run's provenance is genuinely unknown.
- A failed import leaves nothing behind: the run and its turns land in one `DB::transaction` (NFR-4).
- The preview is **not** a trust boundary. Both POSTs run the same Form Request, so editing the flashed CSV
  into something invalid fails on the second request instead of reaching the action. That is why the raw
  text travels in a field rather than as a signed token standing in for it.
- No costume card is collected. `character_card_id` is nullable and a paper sheet records the trainee, not
  which of her forms was equipped; asking would offer a field the source cannot fill and record a guess as
  the Trainer's own observation.
- The `422` envelope branch stays unreachable, now with a line in the test naming the decision.
- **The import reaches an existing defect at volume.** A run arriving from a paper sheet often names no
  scenario, so `scenario = NULL` is this path's common case rather than an edge — and a no-scenario run
  renders its stat bands at 1,400 while its own form enforces 1,200. Measured, root-caused and filed as
  **KI-47**; it is `ADR-0015`'s surface and the Architect's call, so it is not corrected here. The import
  validates against `forRun()`, i.e. the number the turn form enforces, so it is on the strict side of the
  disagreement and cannot write a row its own page would later reject.

## Verification

`tests/Feature/HistoricalRunImportTest.php`, 11 tests / 82 assertions. The headline test asserts the
re-export of an imported run is **byte-identical** to the CSV that was fed in. Others pin the empty-scenario
normalisation by rendering the run page (the Slice 2 `''` regression this path is most exposed to), the
per-stat ceiling asymmetry, duplicate turn numbers, a foreign header, the uploaded-file provenance, and
that the preview step writes no rows.
