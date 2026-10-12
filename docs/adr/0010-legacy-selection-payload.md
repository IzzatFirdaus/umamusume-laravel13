# ADR-0010: Record the Legacy Select read-back as one typed payload on the run

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-29) — §6 non-goal 3's "nothing more" clause caps computation, not recording; training_runs gains one legacy_selection json payload
  code: built — migration 2026_09_29_012615_add_legacy_selection_to_training_runs_table; App\Models\Legacy\LegacySelectionPayload; tests/Feature/LegacySelectionSchemaTest.php
  superseded-by: —
  amended-by: 0020 (builds on this §6.3 recording allowance for the Veteran library)
  open: no UI and no D-260 fixity guard exist yet — the column is storage for a screen that does not exist, and it is justified only while that screen is being built
-->

Status: **accepted** (owner ruling 2026-09-29, directed for Slice 15). Recorded here because it narrows a
documented non-goal. Shape also owner-ruled in the same session: one json payload, and the existing foreign
keys stay.

## Context

`PRD.md` §6 non-goal 3 reads: *"**No breeding/pairing engine** … Inheritance is recorded as two optional
parent references on a run, nothing more."* The second sentence was written to cap the **engine** — the
legacy app computed a descendant from two parent references, and that system was cut — and it puts the
ceiling on the stored record instead.

The design corpus then produced a screen. `CONSTRAINTS.md` D-268 measures what that screen shows against
what `training_runs` can hold: per Legacy, the chosen Umamusume, its rank, whether it is a Guest, its own
two ancestors, and a Spark list with per-Spark kind, target and star rank, plus an affinity value for the
pairing. The record holds two character ids, so Phase 4's provenance requirement is **not met by the
existing columns**.

Two facts were checked before this was written down, because both argue against the column: **no user story
asks for it** (`AGENTS.md` requires a new column to cite a PRD requirement, and the only citation available
is FR-C-1's "optional two inheritance parents", which is the thing §6.3 caps rather than the thing being
added); and **the screen's visual grounding is generated, not captured**, so the payload fields trace to
D-268's enumeration and to `UMAMUSUME_REFERENCE.md` §1.5, not to a client export.

## Decision

Narrow §6 non-goal 3's **"nothing more"** clause so that it caps **computation**, not **recording**.
`training_runs` gains one nullable `legacy_selection` json column, read through
`App\Models\Legacy\LegacySelectionPayload`, holding exactly what D-268 enumerates. The two
`inheritance_parent_*_id` foreign keys are unchanged.

Explicitly unchanged by this decision:

- **No engine over the payload.** Nothing reads it to produce an outcome. No predicted stat, no aptitude
  projection, no offspring model.
- **No inheritance mathematics.** Whether a Spark fires, what an affinity grade contributes in stat points,
  and how stars weight a payout are game-side, and D-270 keeps unpriced figures out of the tool.
- **The vocabulary ban.** §6.3's parenthetical stands: the animal framing that made the legacy system a lore
  violation stays out of this tool, and the screen is described as Legacies, Sparks and an affinity grade.
- **Race outcomes, snapshots, simulation, dual storage** (PRD §6.11, §6.12, `ADR-0001`'s carve-out limits).

Explicitly **not** in this decision:

- **No UI.** D-260 governs where a control may appear when one is built — a pre-run step, never reachable
  from a turn, its result fixed for the life of the run. This ADR authorises storage only.
- **No provenance rows.** The payload carries no `source_url` / `fetched_at`. It records what a Trainer read
  off their own client, which is entered data in the `race_entries.circles` sense, not a fetched fact.

## Consequences

- **The PRD's non-goals list now reads against the code.** §6.3 keeps its headline ban, and its second
  sentence gains a dated pointer here. It is **not** deleted: the engine stays banned, and a reader of §6
  must be able to see that the recording case was separated from it rather than that the line quietly moved.
- **One column, and the shape is the contract.** D-268's rejected alternative — a wide table of nullable
  columns — is what the incoming brief for this task actually named. The owner ruled for the payload
  instead, and the two parent foreign keys already on the table made the named pair a duplicate.
  `LegacySelectionSchemaTest` asserts both halves: the payload column exists, and `legacy_parent_*` does
  **not**. `ancestors` is a list of names, not ids: a Trainer's grandparents frequently are not in the
  local catalogue, and an id column that is usually null would be a second, emptier version of the same fact.
- **Deliberate gaps, recorded so they are not read as oversights.** `rank` is unbounded, because no source
  states a ceiling. `affinity` is one grade for the pair, because that is what D-268 lists; §1.5.4 grades
  *each* link including the four grandparent links, and those are not modelled. Nothing enforces D-260's
  fixity yet, because there is no writer to violate it — the guard belongs with the screen.

## What has to be true before this earns its keep

The column is storage for a screen that does not exist. It is justified by D-268's finding that Phase 4's
provenance requirement is otherwise unmeetable, and it stays justified only while that screen is being
built. If Legacy Select is dropped from the design, the column should be removed rather than left as an
empty bag.

Open question for the owner: `ADR-0004` and `PRD` OQ-4 gate the reference data this screen would want
beside a Legacy's aptitudes. This payload stores what the Trainer read, so it does not depend on OQ-4 — but
a Legacy Select that offers aptitude *context* does.

## Links

- `PRD.md` §6 non-goal 3, §6.11, §6.12, FR-C-1, OQ-4; `CONSTRAINTS.md` D-260, D-268, D-270
- `ADR-0003` (the Phase 1 schema this adds one column to), `ADR-0001` (the precedent for a partial non-goal
  carve-out that says what stays banned), `ADR-0004` (reference data), `ADR-0020` (the Veteran library,
  which builds on this allowance)
- `App\Models\Legacy\LegacySelectionPayload`; `tests/Feature/LegacySelectionSchemaTest.php`
- `docs/UMAMUSUME_REFERENCE.md` §1.5, §1.5.1, §1.5.2, §1.5.3, §1.5.4
