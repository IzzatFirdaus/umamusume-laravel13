# Plan Registry — 2026-11-23

**Generated:** 2026-11-23  
**Source:** `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md` (inventory of all 26 plan files + 4 roadmap files)  
**Governing authority hierarchy:** A > B > C > D > E per AGENTS.md §3a and PLAN-TEMPLATE.md

---

## Authority Hierarchy

| Tier | Name | Role | Count |
|------|------|------|-------|
| **A** | Authoritative Roadmaps & Governance | Distributed ordering authority; five canonical roadmaps (product / frontend-UX / quality / release / decisions) each own their domain's slices; INDEX.md registry; PLAN-TEMPLATE contract spec | 4 (5 roadmaps + index + template, counted by role) |
| **B** | Active Execution Plans | Current executable plans; N0–N6 + Phase 7 consolidated | 2 |
| **C** | Specialized / Session Execution Plans | Distinct task workstreams; NativePHP, colour, stat-colours, hermes, S18, remediation, etc. | 11 |
| **D** | Historical Execution Plans | Superseded or absorbed; preserve as evidence, never edit | 11 |
| **E** | Reviews / Findings / Evidence | Non-executable; UX reviews, findings records | 2 |

---

## Tier A — Authoritative Roadmaps & Governance

| Document | Role | Baseline SHA | PLAN-CONTRACT | Notes |
|----------|------|--------------|---------------|-------|
| `docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Historical / legacy baseline — no execution authority; superseded by five domain-specific authorities | `816e923e` | N/A (roadmap) | Preserved as historical consolidation evidence |
| `docs/roadmap/archive/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Distinct accessibility workstream; phases 0-4 closed, phase 5 open; archived | `816e923e` | N/A (roadmap) | Superseded by FRONTEND-AND-UX-ROADMAP.md |
| `docs/roadmap/INDEX.md` | Registry of five domain-specific authorities, consolidation rounds, cross-directory workflow rules | `816e923e` | N/A (index) | Cross-directory workflow authority |
| `docs/plan/PLAN-TEMPLATE.md` | Contract format specification; PLAN-CONTRACT schema | `816e923e` | N/A (template) | Governs all plan contract blocks |

---

## Tier B — Active Execution Plans

| Document | Status | Slices | PLAN-CONTRACT | Superseded By | Amended By | Notes |
|----------|--------|--------|---------------|---------------|------------|-------|
| `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | **in-progress** | N0.1–N6.18, N7.1–N7.22 | ✅ | — | — | Designated primary; Phase 7 not yet absorbed into the five canonical roadmaps (owner ruling required) |
| `implementation-plan-2026-11-20.md` | **superseded** | N0.1–N6.18 | ✅ | `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | — | Peer edits (2 status lines) uncommitted; preserve |

---

## Tier C — Specialized / Session Execution Plans

| Document | Status | Slices | PLAN-CONTRACT | Superseded By | Amended By | Notes |
|----------|--------|--------|---------------|---------------|------------|-------|
| `nativephp-windows-ci-repair-2026-11-21.md` | **drafted** | S0.2,S4.4 | ✅ | — | — | CI repair for Windows gates, installer, acceptance |
| `legacy-spark-target-entry-2026-11-20.md` | **landed** | [] | ✅ | — | — | EXECUTED 2026-11-20; preserve as evidence |
| `nativephp-v3.0.0-implementation-plan-2026-11-20.md` | **drafted** | N0-N5 | ✅ | — | — | Planning only; Windows packaging not runnable |
| `frontend-colour-update-2026-11-14.md` | **drafted** | Slice 1-Slice 3 | ✅ | — | — | Owner rulings pending on C1/C2 |
| `frontend-stat-colours-and-dark-mode-integration.md` | **drafted** | Slice A-Slice C | ✅ | — | — | Semantic family deferred |
| `hermes-execution-plan-2026-11-17.md` | **in-progress** | S-0.1–S-4.4 | ✅ | — | — | Peer worktree complications; S-0.5 must land together |
| `implementation-plan-2026-11-17-s18.md` | **landed** | A1-A4,B1-B2,Z | ✅ | — | — | Deferred items need owner decisions |
| `implementation-plan-2026-11-17.md` | **superseded** | S1.1-S7,S2-S7 | ✅ | — | — | s18 covers A/B/Z scenario-flow surfaces, not this plan's S1-S7 scope; supersession link removed |
| `implementation-plan-hermes-2026-11-16.md` | **superseded** | R0-R10 | ✅ | `hermes-execution-plan-2026-11-17.md` | — | Session predecessor |
| `optimal-approach-2026-11-17-s18-release.md` | **in-progress** | Slice 1-Slice 5 | ✅ | — | — | Slices 3-4 must be in tag or deferred; Slice 5 (CI gates) independent |
| `remediation-plan-2026-11-15.md` | **drafted** | Slice 1a-Slice 4 | ✅ | — | — | Awaiting owner approval; ADR-0031 obligation |

---

## Tier D — Historical Execution Plans (Preserve, Never Edit)

| Document | Status | Slices | PLAN-CONTRACT | Superseded By | Notes |
|----------|--------|--------|---------------|---------------|-------|
| `plan-tree-truth-2026-11-17.md` | **superseded** | Phase 0-1 (family Plan 1/4) | ✅ | — | Sequential phase dependency (Plan 1 of 4); Plan 2 depends on Plan 1, not a scope replacement |
| `plan-truth-gates-2026-11-17.md` | **superseded** | Phase 2 (family Plan 2/4) | ✅ | — | Sequential phase dependency (Plan 2 of 4); Plan 3 depends on Plan 1 Phase 0, not a scope replacement |
| `plan-defects-2026-11-17.md` | **superseded** | Phase 3 (family Plan 3/4) — KI-166,165,163,164,104/ADR-0027 | ✅ | — | Sequential phase dependency (Plan 3 of 4); Plan 4 depends on Plans 1-2, not a scope replacement |
| `plan-capability-release-2026-11-17.md` | **superseded** | Phases 4-5 (family Plan 4/4) — Team Rank, Unity Cup | ✅ | — | Not replaced by impl-2026-11-20; Team Rank storage (721ad636) and per-ancestor labels (00a1287b) landed via separate commits; owner-gated items 4.3/4.4/4.5 remain open |
| `plan-ui-ux-implementation-2026-11-18.md` | **landed** | S1-S10 | ✅ | `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | Executed at `cfd8bd78` |
| `implementation-plan-2026-11-19.md` | **superseded** | S0.1-S4.5 | ✅ | `implementation-plan-2026-11-20.md` | Crosswalk §8 maps 49 slices to Nov 20 N-IDs |
| `implementation-roadmap-v3.0.0.md` | **superseded** | (v3.0.0 roadmap slices) | ✅ | five canonical roadmaps (product / frontend-UX / quality / release / decisions), 2026-11-23 consolidation; MASTER retained as historical baseline | Recovery premise falsified at G0 |
| `v3.0.0-execution-plan-2026-11-16.md` | **superseded** | R1.1-R4.3 | ✅ | `optimal-approach-2026-11-17-s18-release.md` | Early checklist |
| `finish-plan-2026-11-17.md` | **dropped** | P7.3 | ✅ | — | Package HOLD; v3.0.0 tag not ready; dropped hold decision, not replaced by optimal-approach (S18 follow-through, Slice 1-5) |
| `implementation-plan-2026-11-16.md` | **superseded** | P0.1-P7.3 | ✅ | `project-plan-2026-11-16.md` | P0-P5 slices closed |
| `project-plan-2026-11-16.md` | **superseded** | A.1-A.4,B.1-B.4,C,D,E | ✅ | `implementation-plan-2026-11-20.md` | Early plan; absorbed into Nov 20 plans |

---

## Tier E — Reviews / Findings / Evidence (Non-executable)

| Document | Status | Slices | PLAN-CONTRACT | Notes |
|----------|--------|--------|---------------|-------|
| `trainerdesk-four-screen-ux-review-2026-11-19.md` | **landed** | — | ✅ | Review executed; implementation update 2026-11-20 |
| `career-legacy-manual-input-findings.md` | **landed** | — | ✅ | Evidence record |

---

## Supersession Chain (Reciprocal Links Verified)

```
implementation-plan-hermes-2026-11-16
  └─ superseded-by → hermes-execution-plan-2026-11-17

v3.0.0-execution-plan-2026-11-16
  └─ superseded-by → optimal-approach-2026-11-17-s18-release

implementation-plan-2026-11-16
  └─ superseded-by → project-plan-2026-11-16
       └─ superseded-by → implementation-plan-2026-11-20
            └─ superseded-by → trainerdesk-complete-uiux-implementation-plan-2026-11-20

implementation-plan-2026-11-19
  └─ superseded-by → implementation-plan-2026-11-20
       └─ superseded-by → trainerdesk-complete-uiux-implementation-plan-2026-11-20

implementation-roadmap-v3.0.0
  └─ superseded-by → five canonical roadmaps (2026-11-23 consolidation); MASTER retained as historical baseline

plan-ui-ux-implementation-2026-11-18
  └─ superseded-by → trainerdesk-complete-uiux-implementation-plan-2026-11-20

# Removed (sequential phase dependencies, not scope replacements):
# plan-tree-truth-2026-11-17 → plan-truth-gates-2026-11-17 → plan-defects-2026-11-17 → plan-capability-release-2026-11-17
# implementation-plan-2026-11-17 → implementation-plan-2026-11-17-s18
# finish-plan-2026-11-17 → optimal-approach-2026-11-17-s18-release
```

---

## Qualified Slice ID Convention

Slice IDs are **not globally unique**; they are scoped to their plan:

| Plan Namespace | Example Slice IDs |
|----------------|-------------------|
| `MASTER` (historical) | S0.1, S0.2, S1.1, S2.3, S3.1, S4.4 |
| `UI-UX` (historical, archived roadmap) | S1, S2, ..., S10 (phases 0-5) |
| `PRODUCT` / `FRONTEND` / `QUALITY` / `V3-RELEASE` / `DECISIONS` (current canonical) | owned per `docs/roadmap/INDEX.md` §30.1 |
| `Nov20-N` | N0.1–N6.18, N7.1–N7.22 |
| `Nov19-S` | S0.1–S4.5 |
| `Hermes` | S-0.1–S-4.4 |
| `S18` | A1-A4, B1-B2, Z |
| `NativePHP-CI` | S0.2, S4.4 |
| `NativePHP-v3` | N0–N5 |

Cross-plan references must use qualified form: `MASTER:S0.2`, `Nov20-N:N3.2`, `NativePHP-CI:S0.2`.

---

## Verification Status

- `python tools/md_fence_check.py docs/plan/` → **0 invalid fences in 24 tracked files** ✅ (directory invocation covers tracked files only per `git ls-files -- *.md`; the 7 untracked plan docs — 4 deliverables + 3 new plans — checked separately as explicit file args, also 0 invalid; 24+7 = 31 .md on disk)
- `python tools/md_fence_check.py docs/roadmap/` → **0 invalid fences in 14 files present on disk** ✅ (11 roadmap files are staged-moved to `docs/roadmap/archive/` by a peer — tracked-but-deleted at old paths, so not counted; `docs/roadmap/archive/` checked separately → 0 invalid in 11 files; 9 untracked roadmap docs checked as explicit file args → 0 invalid)
- `python tools/plan_contract_validate.py docs/plan/` → **26 plans checked, 0 errors, PASS** ✅
- `python tools/ui-link-check.mjs docs/plan/` → **clean** ✅
- PLAN-CONTRACT blocks → **all 26 plan files have valid HTML comment blocks with all 6 required fields** ✅ (4 deliverables carry no contract; 31 .md = 26 plans + 4 deliverables + PLAN-TEMPLATE.md)
- Reciprocal supersession links → **all chains verified** ✅
- Qualified slice IDs → **convention documented above** ✅

---

## Deliverable C — Task Ownership Crosswalk (next)

See `docs/plan/TASK-OWNERSHIP-CROSSWALK-2026-11-23.md` for slice → owner decision register mapping.