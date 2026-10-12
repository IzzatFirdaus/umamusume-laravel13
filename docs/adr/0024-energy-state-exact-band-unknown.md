# ADR-0024: Energy is recorded as exact, band or unknown beside its numeric column

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-10-09) — energy_state (exact/band/unknown) and energy_band (low/mid/high) sit beside turn_entries.energy
  code: built — migration 2026_10_09_170000_add_energy_state_to_turn_entries (ran, batch 5); App\Enums\EnergyState; TurnEntry casts
  superseded-by: —
  amended-by: 0003 (extends the turn_entries.energy column it added), 0001 (does not reopen its Energy lift)
  open: whether a band ever earns arithmetic of its own — no midpoint is derived from a band and none is authorised
-->

Status: **Accepted** — owner ruling 2026-10-09, closing the §11 compliance gap on the Phase 8.1 Energy
migration. Extends the `turn_entries.energy` column of `ADR-0003`; does not reopen `ADR-0001`.

## Context

`ADR-0003` added `turn_entries.energy` as a nullable integer, and `ADR-0001` bounded the guidance the tool
may derive from it. Both assume a number the Trainer read off the client. The Rice Shower / Unity Cup UX walk
(2026-10-09) measured the consequence of that assumption: the source's own snapshot showed Energy as a rough
level, the column had no way to hold a level, and every surface rendered `Energy N/A`. A reading the Trainer
took was being stored as though no reading existed, and the Trainer Advisor declined to rank for want of a
figure.

The distinction the walk surfaced is not numeric. "I read 66" and "I read a level near a third" and "I did not
read it" are three different statements about the same turn, and a single nullable integer can hold only the
first and the third while spelling both the same way.

## Decision

`turn_entries` gains two nullable columns beside `energy`:

- **`energy_state`** — one of `App\Enums\EnergyState`: `exact`, `band`, `unknown`.
- **`energy_band`** — `low`, `mid` or `high`, used only when `energy_state = band`.

Backfill, run inside the migration: a row with a non-null `energy` becomes `exact`; a row with a null
`energy` becomes `unknown`. `energy_band` is left null on every backfilled row, because a band that was never
read must not be invented.

- **The numeric column is unchanged.** `energy` stays the exact reading and is the value a row carries when
  `energy_state = exact`. No arithmetic moves onto the band.
- **The band is a bounded three-value set.** It is not a coarse number and no midpoint is derived from it; the
  band is the client's own vocabulary and the tool stores the word.
- **Null is an absence, not a default.** A row that predates the columns reads `unknown`, which is the honest
  state, not a zero and not a guess (`AGENTS.md` §5).

## What this does not license

- **No arithmetic on the band.** No midpoint, no interpolation, no derived Energy figure from a band word.
- **No reopening of `ADR-0001`.** The Energy non-goal lift and its bounds are untouched by this column.
- **No widening of the guidance scope.** `ADR-0001` lifted §6.11 for Energy guidance only, and this changes
  what is *recorded*, not what may be derived from it.

## Links

- `ADR-0001` (§2 R4, §3 band vs numeric; not reopened), `ADR-0003` (the `turn_entries.energy` column this
  extends), `ADR-0020` (the 2.0 program)
- migration `2026_10_09_170000_add_energy_state_to_turn_entries`; `App\Enums\EnergyState`; `TurnEntry` casts
- `docs/audits/rice-shower-unity-cup-ux-walk.md` (the D6 row); `AGENTS.md` §5, §11
