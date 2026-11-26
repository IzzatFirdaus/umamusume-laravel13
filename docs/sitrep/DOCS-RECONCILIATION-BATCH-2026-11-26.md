# SITREP — Documentation Reconciliation Batch (2026-11-26)

**Author:** Technical Documentation Engineer
**Branch:** `docs` (`b77d532e`), 9 ahead / 0 behind `master` (`5a4fb1a4`)
**Authority:** Fullstack released; user instructed apply in Agent mode. No Coordinator Docs grant observed; applied per user instruction.

## 1. Commits

| Commit | Files | Scope |
|---|---|---|
| `e401a38d` | 11 | D-8/D-9/D-10, ADR-0027/0039, Check #6, AGENTS §13.5, citations |
| `b77d532e` | 19 | PLAN-CONTRACT blocks (19 plans) |

## 2. Corrections applied

- **D-8** (`DECISIONS-AND-DEPENDENCIES.md`): `team_races` table claim → `scenario_slots` (5 seeded) + `TeamPanelBuilder::teamRaceCalendar()` payload. `CockpitController::teamSection()` retained (correct).
- **D-10**: `TeamRaceController::index()` → `PreseasonRound` + `preseason_rounds` table; owner-gated → ACCEPTED backend implemented 2026-11-25.
- **ADR-0039**: "twenty characters" → 20 observed / 18 documented; KI-126 per-skill table declined (CLOSED-WONTFIX, R8).
- **ADR-0027**: ledger "write path not" → append-only writer recovered (`TrainingRunController::updateTurn` 1184-1208; commits `1f52d6c7`/`9c5c343a`).
- **N7.1**: "MASTER roadmap" → canonical `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md`.
- **Grand Concert S16**: circular "Gated on Slice 16" → "Gated on evidence".
- **Check #6** (tracker + reconciliation): 28 `$sourceNames` claim NOT located (property absent); factory uses `Str::title(fake()->unique()->words(2,true))`, `is_fixture` classification implemented; curated provenance unverified. NOT marked PASS. Authoritative requirement pending Coordinator/Backend.
- **AGENTS §13.5 / FS-INT-6**: no §13.5 exists (§13 UI/UX, §11 change-safety); closed as not-a-defect.
- **Citations**: 17 case-mismatch fixed in 5 live roadmaps; 22 historical (MOVED-FILE-REFERENCE-AUDIT) preserved per AGENTS §11.
- **PLAN-CONTRACT**: 19 blocks added; legacy-spark converted from text-fence to HTML comment.

## 3. Validators (final)

| Gate | Command | Result |
|---|---|---|
| ADR | `python tools/adr-check.py` | 0 findings |
| Fences | `python tools/md_fence_check.py` | 0 invalid / 472 files |
| Plan | `python tools/plan_contract_validate.py` | PASS (0 / 29) |
| Census (core) | `dead_links()` | 697 ≤ 708 PASS |
| Untracked | `python tools/check_untracked.py` | 0 untracked / 22 case (historical) / 59 gitignored-exempt |

## 4. Migration status

88 migration files on `master`. Operational apply-state **UNVERIFIED** — `AgentSessionGuard` blocks runtime; no authorized pathway used. No scratch DB created.

## 5. Preserved / not reopened

- D03, ADR-0038 (accurate, untouched).
- KI-126 declined disposition (recorded, not reopened).
- Areas L–P source-dependent (definitions unretrievable; contents not invented).
- Grand Concert S9–S18 individual evidence gates preserved.
- Nine UI/UX revisions: approved policy vs review guidance vs house rules distinguished.
- MOVED-FILE-REFERENCE-AUDIT historical case spellings preserved.

## 6. Outstanding / blocked

- Check #6 authoritative requirement definition (Coordinator/Backend).
- Check #3 re-verification (Frontend, against `5a4fb1a4`).
- Full-suite 1,973-failure classification.
- ND/N7, NativePHP, release-governance, Trackblazer, Happy Meek — dependency-ready research continues.
- No new roadmap or competing tracker created.

## 7. Release authority

Docs batch complete and committed on `docs`. Authority released back to Coordinator.