# Decision Packet — N7.1: Standard Career Door (Phase 7 Slice)

## Decision Identifier and Title
**Identifier**: N7.1
**Title**: Standard career door — New Career wizard creates a turn-1 run only at Preflight
**Status**: Owner-gated; accepted 2026-11-25 per task crosswalk; selectively absorbed into MASTER roadmap
**Owner**: Principal Documentation, Research and Requirements Governance Expert
**Date**: 2026-11-25 (task crosswalk); rechecked 2026-11-25
**Related**: `docs/plan/trainerdesk-complete-uiux-implementation-plan-2026-11-20.md`; `TASK-OWNERSHIP-CROSSWALK-2026-11-23.md`; `PLAN-REGISTRY-2026-11-23.md`; `flows-career-creation.md` §§1–4

## Authority
**Accepted 2026-11-25**: Phase 7 slices selectively absorbed into MASTER roadmap; only already-authorized slices retained; undocumented slices documented as separate track per owner ruling; N7.1 is an already-authorized slice

**Previous Ruling**: 
- From `TASK-OWNERSHIP-CROSSWALK-2026-11-23.md:52`: `Nov20-N:N7.1–N7.22` | Same (Phase 7) | — | Owner-gated | **Accepted — Phase 7 slices selectively absorbed into MASTER roadmap 2026-11-25**
- From `TASK-OWNERSHIP-CROSSWALK-2026-11-23.md:200`: **ND-*: Phase 7** | `trainerdesk-complete-uiux-2026-11-20` | Nov20-N:N7.1–N7.22 | **Accepted — absorbed 2026-11-25** | Phase 7 slices selectively absorbed into MASTER roadmap; only already-authorized slices retained; undocumented slices documented as separate track per owner ruling

## Original Requirement
From `flows-career-creation.md` §§1–4 and the implementation plan:
- New Career wizard creates a turn-1 run only at Preflight
- Snapshot creates a run at an imported position
- CSV import writes an entire supplied history
- The retiring `runs.create` door is transitional and must not be silently equated with the wizard

**Three career doors** (non-negotiable domain distinction):
1. New Career wizard creates a turn-1 run only at Preflight
2. Snapshot creates a run at an imported position
3. CSV import writes an entire supplied history
- The retiring `runs.create` door is transitional and must not be silently equated with the wizard

## Current Implementation Status
- **New Career wizard**: Creates turn-1 run at Preflight only; does not create turn-1 at other screens
- **Snapshot**: Creates run at imported position
- **CSV import**: Writes entire supplied history
- **`runs.create` door**: Transitional; must not be silently equated with the wizard

## Owner Question
**Exact question**: Since N7.1 is an already-authorized slice that was absorbed into the MASTER roadmap 2026-11-25, does the owner need to take any action, or is the slice fully resolved?

**Option A**: No action needed — N7.1 is fully resolved as absorbed into MASTER roadmap 2026-11-25; only already-authorized slices retained; undocumented slices documented as separate track
- **Evidence required**: Task crosswalk 2026-11-23; plan registry 2026-11-23; task ownership crosswalk 2026-11-23
- **Development blocked**: None — slice is resolved
- **Release relevance**: Maintains current state; no new functionality

**Option B**: Re-review and re-absorb — re-examine N7.1 against current HEAD and re-absorb into MASTER if still valid
- **Evidence required**: Current HEAD measurement; re-absorption ruling
- **Development blocked**: Unnecessary if slice is already accepted
- **Release relevance**: Would re-evaluate resolved slice

**Option C**: Document as separate track — record N7.1 as documented separate track per owner ruling
- **Evidence required**: Separate track documentation; owner ruling
- **Development blocked**: None — recording is a documentation action
- **Release relevance**: Maintains current state; no new functionality

## Recommended Ruling
**No action needed — N7.1 is fully resolved as absorbed into MASTER roadmap 2026-11-25** (Option A). The slice was accepted per the task crosswalk and plan registry; no further owner action required.

**Supporting evidence**:
- `TASK-OWNERSHIP-CROSSWALK-2026-11-23.md:52` — N7.1–N7.22 owner-gated, accepted 2026-11-25
- `TASK-OWNERSHIP-CROSSWALK-2026-11-23.md:200` — ND-*: Phase 7 accepted 2026-11-25
- `PLAN-REGISTRY-2026-11-23.md:36` — N0.1–N6.18, N7.1–N7.22 | ✅ | Designated primary; Phase 7 not yet absorbed into five canonical roadmaps (owner ruling required) — but N7.1 is an already-authorized slice
- `TASK-OWNERSHIP-CROSSWALK-2026-11-23.md:138` — ND-*: Phase 7 absorbed 2026-11-25; only already-authorized slices retained

**Development blocked**: None — slice is resolved

**Unaffected work**: All existing career door functionality; N7.1 absorbed; no implementation change needed

**Release relevance**: Maintains current state; no new functionality

**Responsible Expert**: None (slice already absorbed; no expert needed beyond what's recorded)

**Uncertainty**: Whether future MASTER roadmap updates would change the absorption; not applicable to current ruling

## Category
Category B — Resolved/delegated requirements (N7.1 absorbed into MASTER 2026-11-25; no owner action needed)