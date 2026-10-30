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
parent references on a run, nothing more."* <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 --> The
second sentence was written to cap the **engine** — the legacy app computed a descendant from two parent
references, and that system was cut — and it puts the ceiling on the stored record instead.

The design corpus then produced a screen. `CONSTRAINTS.md` D-268 measures what that screen shows against
what `training_runs` can hold:

> per Legacy: the chosen Umamusume, its rank, whether it is a Guest, its own two ancestors, and a Spark list with per-Spark kind, target and star rank, plus an affinity value for the pairing. The `training_runs` record holds two character ids. Phase 4's provenance requirement is therefore **not met by the existing columns**, and closing it is a schema proposal on the `ADR-0003` pattern, most likely a typed json payload keyed to the run rather than a wide table of nullable columns. <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

So the repository holds two statements that cannot both be followed: D-268 says the record is short of what
the screen produces and names the fix, and §6.3 says the record stops at two references.

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
### Amendment 2026-10-30 — a manual parent's name joins the shape

**Decision.** legacies.*.name is added to the optional per-legacy keys, stored only when a payload actually carries one, on the ncestors_meta precedent. A slot may hold either a library pick (legacy_id) or a typed name, never both: the library row wins, and the wizard does not offer the name input while a row is picked.

**Why.** UMAMUSUME_REFERENCE.md §1.5.4 records the case directly: a parent may be a friend's finished Umamusume, rented from the friend list. A rented parent has no Veteran row, so legacy_id cannot represent her, and without this key the whole case was unrecordable. is_guest already marked exactly this state and is unchanged: it is written through the payload, and no wizard control was added for it.

**Boundaries kept.** The key is optional, not required: a payload written before this amendment keeps validating, and an absent name keeps the byte-identical old shape. ame is a string of at most 255 characters and nothing is resolved from it - no library lookup, no derived figure. The existing refusals (the same library row twice, a parent who is the trainee herself) apply to ids only and are not extended to typed names, which is stated on the page rather than hidden.

**Readers branched.** Every surface that prints a parent name resolves the stored library id first and falls back to the payload name: the wizard card builder, the Preflight count and its legacy line, the inheritance event, and the Legacy Lab graph. LegacySelectionSchemaTest carries three cases: a payload with and without the key both validate while an unknown key is still refused, a rented-parent payload round-trips, and a payload without a name reads back in the pre-change shape.

