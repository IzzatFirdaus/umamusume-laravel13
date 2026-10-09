# ADR-0024: Energy is recorded as exact, band or unknown beside its numeric column

Status: **Accepted** — owner ruling 2026-10-09, closing the §11 compliance gap on the Phase 8.1
Energy migration. Extends the `turn_entries.energy` column of `ADR-0003`; does not reopen `ADR-0001`.

Date: 2026-10-09
Deciders: product owner (ruling), implementing agent (draft)
Relates to: `ADR-0001` (§2 R4, §3 band vs numeric), `ADR-0003` (the `turn_entries.energy` column this
extends), the Rice Shower / Unity Cup UX walk's D6 row

## Context

`ADR-0003` added `turn_entries.energy` as a nullable integer, and `ADR-0001` bounded the guidance the
tool may derive from it. Both assume a number the Trainer read off the client. The Rice Shower / Unity
Cup UX walk (2026-10-09) measured the consequence of that assumption: the source's own snapshot showed
Energy as a rough level ("near a third"), the column had no way to hold a level, and every surface
rendered `Energy N/A` — the walk's `Energy N/A everywhere` finding. A reading the Trainer took was being
stored as though no reading existed, and the Trainer Advisor declined to rank for want of a figure.

The distinction the walk surfaced is not numeric. "I read 66" and "I read a level near a third" and "I
did not read it" are three different statements about the same turn, and a single nullable integer can
hold only the first and the third while spelling both the same way.

## Decision

`turn_entries` gains two nullable columns beside `energy`:

- **`energy_state`** — one of `App\Enums\EnergyState`: `exact`, `band`, `unknown`.
- **`energy_band`** — `low`, `mid` or `high`, used only when `energy_state = band`.

Backfill, run inside the migration: a row with a non-null `energy` becomes `exact`; a row with a null
`energy` becomes `unknown`. `energy_band` is left null on every backfilled row, because a band that
was never read must not be invented.

- **The numeric column is unchanged.** `energy` stays the exact reading and is the value a row carries
  when `energy_state = exact`. No arithmetic moves onto the band.
- **The band is a bounded three-value set.** It is not a coarse number and no midpoint is derived from
  it; the band is the client's own vocabulary and the tool stores the word.
- **Null is an absence, not a default.** A row that predates the columns reads `unknown`, which is the
  honest state, not a zero and not a guess (`AGENTS.md` §5).
- **The Advisor reads the state.** A band or an unknown reading is named in the advice's source line
  rather than declining, so the tool answers from what was recorded instead of refusing for want of a
  figure.

## Explicitly unchanged

- **`energy` remains numeric.** This is an extension of `ADR-0003`'s column, not a replacement, and the
  column's type and bounds are untouched.
- **`ADR-0001`'s Energy guidance scope stands.** The band is a bounded three-value set, not the invented
  numeric model `ADR-0001` §3 refuses; the advisory line at 50 is still the only sourced line, and no
  second threshold is derived from a band.
- **No prediction.** The state says what was read, never what a turn will yield (`ADR-0020` §3).

## Consequences

### 1. The `Energy N/A everywhere` finding is closed

A band reading is stored as a band and rendered as one (`Energy: Low band`); an unknown reading is
stored and rendered as the named absence (`Energy: Not recorded`). The three states are distinct on the
screen and in the store, which is the distinction the walk asked for.

### 2. The Advisor answers from a band instead of declining

A low band is treated as below the advisory line and Rest is advised; a mid or high band is at or above
it, and the largest deficit is advised. The recommendation's reason names the band it came from, so the
Trainer sees the state the advice rests on rather than a figure the tool does not hold.

### 3. What this does not close

The band is three coarse values and no finer reading is recovered from it; a client that shows a fourth
level needs a fourth value and a superseding decision. The exact numeric column is unaffected, and a
surface that only ever writes a number continues to work with `energy_state = exact`.
