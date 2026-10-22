# ADR-0027: Turn and decision logging

<!-- ADR-CONTRACT
  decision: proposed — the owner's ruling is recorded; the agent cannot accept ADRs
  code: built — migration 2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table (ran, batch 5); turn_entries.choice, decision_snapshot, corrected_from, superseded_by; partial unique index on (training_run_id, turn) WHERE corrected_from IS NULL; lookup index on (training_run_id, turn); tests/Feature/TurnChoiceTest.php; tests/Feature/NextDecisionTest.php; tests/Feature/CareerCockpitTest.php; tests/Feature/CockpitObjectivesTest.php
  superseded-by: —
  amended-by: —
  open: the decision's four columns have no writer — the shipped correction path (runs.turns.update) updates the turn row in place, so corrected_from/superseded_by stay null and the Timeline's corrected marker is read from updated_at; who writes a correction row, and from which screen, is undecided (2026-10-22 framework check)
-->

Status: **Proposed** — drafted 2026-10-18 for the owner's review. The rulings D-1a through D-1d are recorded verbatim from source code and tests; the agent cannot accept ADRs.

## Context

The turn entry model originally had a plain unique constraint on `(training_run_id, turn)` which prevented recording corrections (two rows for the same turn). Phase G established four rulings (D-1a–D-1d) that changed the schema to support append-only correction history with bidirectional cross-links and a point-in-time decision snapshot.

The migration `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` implemented these rulings by adding four columns and swapping the unique index for a partial unique index constrained to original (non-correction) rows only.

## Decision

**D-1a** — `choice` is the action the Trainer took, written once at action time, never updated. `decision_snapshot` is the advisor's full `NextDecision` payload as it stood at the moment of the action, written once by the server and never updated. A recomputed value is not a snapshot.

Source: `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php:56-60` (columns); `tests/Feature/TurnChoiceTest.php:167` (test assertion: "written once by the server and never updated").

**D-1b** — The plain unique index on `(training_run_id, turn)` is replaced with a **partial unique index** that constrains only rows where `corrected_from IS NULL` (original rows). This allows exactly one original row per turn, but permits correction rows (which have `corrected_from` set) to share the same turn number. The predicate is `corrected_from IS NULL` (not `superseded_by IS NULL`) because a correction cannot be superseded-before-inserted: its own id does not exist until the insert succeeds, so the original it replaces is still `superseded_by IS NULL` at that moment. An index over `superseded_by` would reject every append.

Source: `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php:13-26` (migration comment explaining the index swap rationale); `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php:93-94` (partial index creation SQL).

**D-1c** — The cross-link pair `corrected_from` (FK to the row being corrected) and `superseded_by` (FK to the row that replaces it) are both stored so neither row has to scan the other to explain itself in the Timeline. Both directions are stored as nullable foreign keys with `ON DELETE SET NULL`.

Source: `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php:62-70` (column definitions and foreign keys); `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php:38-42` (migration comment explaining bidirectional storage).

**D-1d** — A `down()` migration refuses rather than deleting history. If any turn holds more than one row, the plain unique index cannot be recreated, and `down()` raises before dropping anything. This is deliberate: the alternative is a `down()` that silently discards correction rows to make itself succeed, which is the one thing a rollback must not do to an append-only history.

Source: `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php:101-122` (down() method with guard check and exception).

## Consequences

### 1. Columns and indexes created

| Column | Type | Purpose | Source |
|--------|------|---------|--------|
| `choice` | `string(32)?` | The action noun the Trainer took (`TurnChoice` value) | D-1a; migration line 56 |
| `decision_snapshot` | `json?` | Full `NextDecision` payload at action time | D-1a; migration line 60 |
| `corrected_from` | `unsignedBigInteger?` | FK to the original row this corrects | D-1c; migration line 64 |
| `superseded_by` | `unsignedBigInteger?` | FK to the correction that replaces this | D-1c; migration line 65 |

| Index | Definition | Purpose | Source |
|-------|------------|---------|--------|
| `turn_entries_run_turn_original_unique` | `UNIQUE (training_run_id, turn) WHERE corrected_from IS NULL` | One original row per turn | D-1b; migration line 94 |
| `turn_entries_run_turn_lookup` | `INDEX (training_run_id, turn)` | Ordinary lookup for every row | D-1b; migration line 98 |

### 2. Code paths that read these columns

- `App\Models\TurnEntry` — fillable attributes and casts for the four new columns
- `App\Services\Advisor\NextDecision::recordedAction()` — reads `choice` and `decision_snapshot` from the current row (`superseded_by IS NULL`) and the link back to the corrected row
- `App\Http\Controllers\TrainingRunController` — renders the Timeline with correction cross-links
- `App\Models\TrainingRun` — relationship methods for turn entries including corrections

### 3. Test files that pin the behavior

- `tests/Feature/TurnChoiceTest.php` — D-1a assertion at line 167; D-1b index swap test at line 23; D-1c cross-link tests; D-1d down() refusal test
- `tests/Feature/NextDecisionTest.php` — D-1a snapshot usage at line 103
- `tests/Feature/CareerCockpitTest.php` — D-1a reference at line 130
- `tests/Feature/CockpitObjectivesTest.php` — D-1a reference at line 112

## Links

- Migration: `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table`
- Also `ADR-0010` (Legacy selection payload, related schema pattern)
- PRD FR-C-2: `choice`/`decision_snapshot`/`corrected_from`/`superseded_by` (Phase G ruling D-1a–D-1d)
- `docs/research-scratch/SLICE-RECORDS.md` G2-FD4 (drafted source for this ADR)
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md` (the re-check table); rules H3, SHR-6, TOG-6,
  NRM-6

## Ledger

- 2026-10-22 · **framework check against `docs/framework-ui-ux/` — the schema is built and the write
  path is not.** The re-check table asks a change that moves what a user can undo against **H3** (user
  control and freedom), **SHR-6** (a committed turn is reversible from the screen that committed it),
  **TOG-6** (if undo is absent, the confirmation its absence forces must be present and named) and
  **NRM-6** (can the user tell what happened). **What the check found is that this decision records the
  shape of a history nothing writes.** The shipped correction path is `runs.turns.update` →
  `TrainingRunController::updateTurn()`, whose whole write is `$turn->update($request->validated())`:
  the row is amended in place, so `corrected_from` and `superseded_by` stay null on every corrected
  turn, and the append-only history this ADR decided for — the one that would make a correction
  *reversible* rather than destructive — never comes into being. The display side reads the older
  mechanism instead: `Career/TimelineController` marks a turn corrected from `$turn->updated_at !== null`
  and prints `Correction turn-N`, while `NextDecision::recordedAction()` filters on
  `superseded_by IS NULL` and reads `corrected_from` from the same rows. **What that leaves the
  Trainer is a correction that is not a reversal**: the Cockpit's manual correction can change a
  committed turn's values, and the values it replaced are gone, which is the half SHR-6 names and the
  half TOG-6's confirmation is supposed to cover. **Nothing here is repaired by this entry.** D-1a to
  D-1d stand as written, the migration and its partial unique index are as described, and the decision
  to preserve history is not withdrawn. What is owed is an owner ruling on the two questions the
  decision set leaves open: whether `updateTurn` writes an append-only correction row (and what the
  in-place update becomes), and what the user is shown when a committed turn is changed.
