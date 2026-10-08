# ADR-0022: The Veteran tag filter matches a model-folded twin, not the stored spelling

Status: **Proposed — uncommitted draft, not landed.** Prepared by the KI-57–KI-72 resolution pass on
2026-10-08 as the §11 decision record that accompanies the shipped schema change. The number, the status
line and the `docs/adr/README.md` regeneration are the owner's to land; a sibling draft may already claim
`0022` (this repository carries a history of number collisions — two KI-70 headings and two KI-71 — and
nothing in the tree checks for a duplicate). Nothing here binds until the owner records it as Accepted.

Date: 2026-10-08
Deciders: product owner (ruling, 2026-10-07, recorded in `KNOWN-ISSUES.md` KI-72), Laravel Dev (drafting)
Implements: `KNOWN-ISSUES.md` KI-72; `PRD.md` FR-G-2 (the library's tag filter)
Related: `database/migrations/2026_10_08_180000_add_tags_normalized_to_veterans_table.php`;
`app/Models/Veteran.php`; `app/Actions/ListVeterans.php`; `app/Actions/RecordVeteran.php`;
`ARCHITECTURE-ESSENTIALS.md` (the `veterans` line); `tests/Feature/VeteranLibraryTest.php`,
`tests/Feature/LegacyLabPageTest.php`

## Context

`veterans.tags` stores the Trainer's own spelling: `StoreVeteranRequest::prepareForValidation()`
de-duplicates case-insensitively and keeps the first spelling exactly as typed. The library's filter
answered that column with `whereJsonContains('tags', 'Speed')`, and SQLite compares JSON string elements
case-sensitively. A career filed with the tag `speed` was therefore unfindable through the `Speed`
suggestion chip — the row existed, the filter said it did not, and nothing errored. The suggestion chip is
the library's discovery path, so the miss landed on precisely the free-text half the Save Veteran screen
exists to offer (KI-72).

The owner's ruling named the mechanism: a normalized companion column, the stored tags keeping the
Trainer's spelling and the filter matching a lowercase twin. That is a schema change, so §11's migration
package applies: migration with a working `down()`, the digest updated, a PRD citation, and this decision
record.

## Decision

1. **`veterans.tags_normalized`** — a nullable JSON list holding the same tags folded to lowercase. It is
   a filter key, not displayed data: no screen prints it, and `tags` remains the only rendered spelling.
2. **The `Veteran` model's own `saving` guard folds the twin from `tags` on every save.** `RecordVeteran`
   keeps writing tags and notes, and the request keeps its verbatim storage; neither carries the twin.
3. **`ListVeterans` matches `mb_strtolower($tag)` against the twin**, one clause per named tag, keeping the
   all-of narrowing that `ADR-0018`'s facet contract already defines.
4. **The migration backfills existing rows** over `DB::table`, never the model, so a schema step does not
   depend on a model shape a later commit can move.

## Why the guard, not the write site — the reproduction that decided it

The write-site derivation was built first, exactly as the register's closure text sketches it, and it
failed one test away from the shipped surface: `LegacyLabPageTest`'s browse case sets tags with a direct
model update (`$veteran->update(['tags' => […]])`), which no write-site derivation sees. The twin stayed
empty, `GET /legacy?tag=Turf` returned `veterans.data` of size **0 where 2 were expected**, and the filter
went silent again — the same defect class KI-72 files, reproduced inside the fix. Folding the twin in the
model's saving guard closes it for every writer at once: the form through the action, a factory, a direct
model update, and `tinker`. This is the repository's existing answer for derived columns — `RaceEntry` and
`TrainingRun` carry saving guards for the same reason, the latter's comment naming it: *what keeps a writer
that is not a Form Request honest*.

## Consequences

- **The named limit of this design is bulk query-builder writes.** `Veteran::query()->update([...])`
  bypasses model events, so a bulk write of `tags` can still leave the twin stale. That path exists only in
  test fixtures today (`LegacyLabPageTest` clears tags that way before re-setting them per row); a future
  production bulk write must fold the twin itself or go through a model save.
- No index is added. `whereJsonContains` on a personal-scale table (the library "numbers in the tens", as
  `LegacyController`'s roster comment states) needs none, and adding one would be a claim about SQLite
  JSON indexing nobody has measured.
- The filter's displayed wording does not change, and the `veterans` fixture ruling (KI-69 class 3, plan
  §4.1 item 10) is untouched by this decision: the regression that ships is Pest-level, and a browser-level
  case still waits on that fixture.
- The migration ships with the digest line updated in the same change. The column stays **Pending** on
  `database/database.sqlite` until the owner applies it, and this ADR must land before or with that apply,
  because HEAD without the guard reads a column nothing writes.
