# ADR-0010: Record the Legacy Select read-back as one typed payload on the run

Status: **accepted.** Directed by the owner for Slice 15 (the Schema Session), 2026-09-29, and
recorded here because it narrows a documented non-goal, which `AGENTS.md` requires an ADR to do.
Shape also owner-ruled in the same session: one json payload, and the existing foreign keys stay.

Date: 2026-09-29
Deciders: product owner (decision and shape), Laravel Dev + Architect (recording and consequences)
Amends: `PRD.md` §6 non-goal 3 (the "nothing more" clause only)
Related: `ADR-0003` (the consolidated Phase 1 schema this adds one column to), `ADR-0001` (the precedent for narrowing a non-goal in part and saying what stays banned), D-260, D-268, D-220, D-270, `CONSTRAINTS.md` §10q

## Context

`PRD.md` §6 non-goal 3 reads: *"**No breeding/pairing engine** (replaces uma-companion's sire × dam system, whose vocabulary was also a lore violation). Inheritance is recorded as two optional parent references on a run, nothing more."* <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

The second sentence is the one in the way. It was written to cap the *engine* — the legacy app computed a descendant from two parent references, and that system was cut — and it puts the ceiling on the stored record instead: two references, nothing more.

The design corpus then produced a screen. `CONSTRAINTS.md` D-260 places **Legacy Select** before the first turn (create run, choose scenario, choose Trainee, choose Legacies, then turn one), and D-268 measures what that screen shows against what `training_runs` can hold:

> per Legacy: the chosen Umamusume, its rank, whether it is a Guest, its own two ancestors, and a Spark list with per-Spark kind, target and star rank, plus an affinity value for the pairing. The `training_runs` record holds two character ids. Phase 4's provenance requirement is therefore **not met by the existing columns**, and closing it is a schema proposal on the `ADR-0003` pattern, most likely a typed json payload keyed to the run rather than a wide table of nullable columns. <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

So the repository holds two statements that cannot both be followed: D-268 says the record is short of what the screen produces and names the fix, and §6.3 says the record stops at two references.

Two further facts were checked before this was written down, because both argue against the column:

- **No user story asks for it.** `PRD.md` FR-A through FR-E and US-1 through US-11 contain no Legacy Select requirement. `AGENTS.md` tells the Architect that a new column must cite a PRD requirement, and the only citation available is FR-C-1's "optional two inheritance parents", which is the thing §6.3 caps rather than the thing being added. This ADR is the mechanism by which that gap is closed rather than worked around.
- **The screen's visual grounding is generated, not captured.** `DESIGN.md:1544` records that the three frames behind §6.27 and §6.28 were generated for those sections and then read back. `CONSTRAINTS.md:126` adds that neither "Legacy Select" nor "Inspiration" has been read off a captured client frame. The payload fields therefore trace to D-268's enumeration and to `UMAMUSUME_REFERENCE.md` §1.5, not to a client export.

## Decision

Narrow §6 non-goal 3's **"nothing more"** clause so that it caps **computation**, not **recording**.

`training_runs` gains one nullable `legacy_selection` json column, read through `App\Models\Legacy\LegacySelectionPayload`, holding exactly what D-268 enumerates. The two `inheritance_parent_*_id` foreign keys are unchanged.

Explicitly unchanged by this decision:

- **No engine over the payload.** Nothing reads this payload to produce an outcome. No predicted stat, no aptitude projection, no offspring model, no "this pair yields X".
- **No inheritance mathematics.** Whether a Spark fires, what an affinity grade contributes in stat points, and how stars weight a payout are game-side. The corpus prices some of them (`REFERENCE` §1.5.2, §1.5.3) and D-270 keeps unpriced figures out of the tool; none of it is computed here.
- **The vocabulary ban.** §6.3's parenthetical stands: the animal framing that made the legacy system a lore violation stays out of this tool, and the screen is described as Legacies, Sparks and an affinity grade.
- **Race outcomes, snapshots, simulation, dual storage** (PRD §6.11, §6.12, `ADR-0001`'s carve-out limits).

Explicitly **not** in this decision:

- **No UI.** D-260 governs where a control may appear when one is built — a pre-run step, never reachable from a turn, its result fixed for the life of the run. This ADR authorises storage only.
- **No provenance rows.** The payload carries no `source_url` / `fetched_at`. It records what a Trainer read off their own client, which is entered data in the `race_entries.circles` sense, not a fetched fact. A future capture that changes this is a new decision.

## Consequences

### 1. The PRD's non-goals list now reads against the code

`PRD.md` §6.3 keeps its headline ban, and its second sentence gains a dated pointer here. It is **not** deleted: the engine stays banned, and a reader of §6 must be able to see that the recording case was separated from it rather than that the line quietly moved.

### 2. One column, and the shape is the contract

D-268's rejected alternative — a wide table of nullable columns — is what the incoming brief for this task actually named (`legacy_parent_a_id`, `legacy_parent_b_id`, `inherited_sparks`). The owner ruled for the payload instead, and the two parent foreign keys already on the table made the named pair a duplicate. `LegacySelectionSchemaTest` asserts both halves: the payload column exists, and `legacy_parent_*` does **not**.

`ancestors` is a list of names, not ids. A Trainer's grandparents frequently are not in the local `umamusume` catalogue, and an id column that is usually null would be a second, emptier version of the same fact.

### 3. Deliberate gaps, recorded so they are not read as oversights

- **`rank` is unbounded.** `REFERENCE` §1.5.1 states that three stars guarantees a unique-skill Spark and states no ceiling on a character's own count. Bounding it would invent a number.
- **`affinity` is one grade for the pair**, because that is what D-268 lists. §1.5.4 grades *each* link in the diagram, including the four grandparent links, and those are not modelled. A later screen that shows per-link grades needs this column revisited, not merely read.
- **Nothing enforces D-260's fixity yet.** "Its result is fixed for the life of the run" is a data invariant, and there is no writer to violate it — the column has no form behind it. The guard belongs with the screen, and putting it here would be a rule no path can reach.

### 4. What has to be true before this earns its keep

The column is storage for a screen that does not exist. It is justified by D-268's finding that Phase 4's provenance requirement is otherwise unmeetable, and it stays justified only while that screen is being built. If Legacy Select is dropped from the design, `ADR-0003`'s consolidation argument does not rescue the column and it should be removed rather than left as an empty bag.

### 5. Open question for the owner

`ADR-0004` (aptitude and scenario cap reference data) and `PRD` OQ-4 gate the reference data this screen would want beside a Legacy's aptitudes. This payload stores what the Trainer read, so it does not depend on OQ-4 — but a Legacy Select that offers aptitude *context* does. Whether that context is in scope for the screen is not decided here.
