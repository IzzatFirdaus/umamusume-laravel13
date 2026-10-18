# ADR-0027: Turn and decision logging

<!-- ADR-CONTRACT
  decision: proposed — the owner's ruling is recorded; the agent cannot accept ADRs
  code: built — migration 2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table (ran, batch 5); turn_entries.choice, decision_snapshot, corrected_from, superseded_by; partial unique index on (training_run_id, turn) WHERE corrected_from IS NULL; lookup index on (training_run_id, turn); tests/Feature/TurnChoiceTest.php; tests/Feature/NextDecisionTest.php; tests/Feature/CareerCockpitTest.php; tests/Feature/CockpitObjectivesTest.php
  superseded-by: —
  amended-by: —
  open: —
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
- PRD FR-C-2: `choice`/`decision_snapshot`/`corrected_from`/`superseded_by` (Phase G ruling D-1a–D-1d)
- ADR-0010 (Legacy selection payload, related schema pattern)
- `docs/research-scratch/SLICE-RECORDS.md` G2-FD4 (drafted source for this ADR)