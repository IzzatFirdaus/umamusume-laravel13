# ADR-0017: A historical run imports as the CSV this app exports, through a web form

<!-- ADR-CONTRACT
  decision: accepted (Slice 4, built 2026-10-01) — one file, one run, two POSTs, against the eleven columns export() emits and nothing else
  code: built — migration 2026_09_30_174646 (imported_at, import_source); ImportHistoricalRunRequest; tests/Feature/HistoricalRunImportTest.php
  superseded-by: —
  amended-by: 0015 (the ceilings it validates against are ScenarioCaps::forRun's), 0014 (the deck it deliberately does not fill)
  open: a comma inside condition makes a file unimportable — the export joins with bare commas and does not quote; the fix belongs to export(), not to the parser
-->

Status: **Accepted** (Slice 4, built 2026-10-01). Realises the import half of `PRD.md` US-12's neighbouring
concern and touches no non-goal. Reads `ADR-0015`'s ceiling owner for the bounds it validates against, and
`ADR-0014` for the deck it deliberately does **not** fill.

## Decision

A Trainer's finished career arrives as **one file, one run, two POSTs**: a preview that validates and shows
every row, then a write that re-validates the same bytes and writes.

The accepted header is the eleven columns `TrainingRunController::export()` emits — `turn, speed, stamina,
power, guts, wit, sp, condition, energy, mood, fans` — and nothing else. The format was not designed for the
import; it was **inherited from the export**, which makes the round trip the test of the feature rather than a
claim about it.

### The write surface is web-only, and that was checked rather than assumed

`tests/Feature/ApiV1ValidationEnvelopeTest.php` asserts every `api/` route is a read, which keeps the
`VALIDATION_ERROR` envelope branch unreachable. The reason it stands is the PRD: **FR-E-1 scopes the API to
reads** and US-9 says "I query a local JSON API". No write requirement exists anywhere in §4, and `AGENTS.md`
requires every new class to cite one. So there is no API endpoint, and the test's premise stands for a reason
that outlives this slice.

### Turns only, and the three things left out are named on screen

Skills, deck slots and race entries have columns in this schema and are **not** in the file format. The
preview page says so in words.

Skills were the tempting omission to paper over: a name→id lookup with best-match would fill the panel. It
was refused because an unresolved name is exactly what `PRD.md` US-5's review queue exists to adjudicate, and
importing by best guess would attach a `skill_id` the source never stated — a provenance violation, not a
shortcut.

### `imported_at` and `import_source` are a new pair, deliberately not a borrowed one

Both are nullable with no default, so typed runs — the overwhelming majority — say nothing. Reusing
`created_at` would make every imported run claim it was created at import time: true of the row, false of the
history. Reusing `is_manual` would misuse the flag that marks a row the **fetch engine** must never overwrite
(FR-B-4), and an imported run is neither a hand-correction nor a fetch. Reusing `source_url`/`fetched_at`
would claim a fetched origin for something that was not fetched. The names were grepped before being chosen.

### Every stat is measured by the run's own scenario

`ImportHistoricalRunRequest` builds its per-column bounds from `ScenarioCaps::forRun()` — the single owner
established by `ADR-0015` — against a transient run carrying only the submitted scenario. An import is
therefore refused for the same number the turn form and the stat band would refuse it for, and a run naming
no scenario is measured at the base cap with no bonus. Nothing in the import re-derives a ceiling.

## The two normalisations the file forces, and the one limit it accepts

- **UTF-8 BOM and CRLF.** Excel writes both. Stripping the BOM is load-bearing: an unstripped one makes the
  first header cell read as a BOM-prefixed `turn`, which rejects a file this application itself produced.
- **Mood is upper-cased, not defaulted.** `MoodTier`'s backing values are the `[Global]` client strings
  verbatim and those are uppercase, while a paper sheet writes `good`. An unrecognised word is left as-is so
  the enum rule rejects it **with its own message** rather than becoming null silently. This is a deliberate
  divergence from the pre-flight, which proposed mapping an unknown word to null and keeping the row: a blank
  mood is a false statement about the run, and the Trainer can see which row said it.
- **A comma inside `condition` makes the file unimportable.** `export()` joins with bare commas and does not
  quote, so such a value arrives as a twelfth cell. The row-length check rejects the file rather than reading
  `fans` into `energy` by position. This is the export's limitation inherited, not a new one.

`str_getcsv()` is called with its `$escape` argument explicit. PHP 8.5 deprecates relying on the default, and
an empty escape is the correct reading of a writer that emits no backslash quoting — passing it keeps the
parser symmetrical with the emitter instead of merely silencing a notice.

## Consequences

- `training_runs` gains two nullable columns. No existing row changes; no backfill is possible, since an older
  run's provenance is genuinely unknown.
- A failed import leaves nothing behind: the run and its turns land in one `DB::transaction` (NFR-4).
- The preview is **not** a trust boundary. Both POSTs run the same Form Request, so editing the flashed CSV
  into something invalid fails on the second request instead of reaching the action.
- No costume card is collected. `character_card_id` is nullable and a paper sheet records the trainee, not
  which of her forms was equipped; asking would offer a field the source cannot fill and record a guess as
  the Trainer's own observation.
- **The import reaches an existing defect at volume.** A run arriving from a paper sheet often names no
  scenario, so `scenario = NULL` is this path's common case rather than an edge — and a no-scenario run
  rendered its stat bands at 1,400 while its own form enforced 1,200. Measured, root-caused and filed as
  **KI-47**, which is `ADR-0015`'s surface and the Architect's call, so it was not corrected here. **KI-47 is
  closed** (`04658f2`, 2026-10-01). The import validates against `forRun()`, i.e. the number the turn form
  enforces, so it was on the strict side of the disagreement and could not write a row its own page would
  later reject.

## Links

- `PRD.md` US-12, FR-E-1, US-5, US-9, NFR-4; `AGENTS.md` escalation 2
- `ADR-0015` (the ceiling owner), `ADR-0014` (the deck not filled), `ADR-0012` (the card not collected)
- `ImportHistoricalRunRequest`, `ImportHistoricalRun`; `TrainingRunController::export()`
- `tests/Feature/HistoricalRunImportTest.php`, `tests/Feature/ApiV1ValidationEnvelopeTest.php`
- KI-47 (closed by `04658f2`)

## Ledger

- 2026-10-01 · **KI-47 filed against this path and closed the same day.** The consequence above recorded
  that the no-scenario band/form ceiling disagreement "is `ADR-0015`'s surface and the Architect's call, so it
  is not corrected here". It was corrected here in the end: the human owner ruled the band is wrong and
  `04658f2` closed it on 2026-10-01. Kept because the sentence is the shape of a correct deferral that was
  then overtaken, and a reader needs to see that the import was on the strict side while it stood.
