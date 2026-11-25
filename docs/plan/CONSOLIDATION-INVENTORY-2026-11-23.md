# Plan Consolidation Inventory — 2026-11-23

**Generated:** 2026-11-23  
**Scope:** All Markdown files in `docs/plan/` functioning as implementation/execution/remediation/release plans, roadmaps, task handoffs, or amendments  
**Source of truth:** Git HEAD `816e923e` (measured 2026-11-23)  
**Method:** Every file read in full; classification by content, not filename; PLAN-CONTRACT presence verified by grep

**Supersession note (2026-11-23, Round 30).** The 2026-11-23 consolidation replaced the single-master execution model with **five canonical roadmaps** (`PRODUCT-AND-CAPABILITY-ROADMAP.md`, `FRONTEND-AND-UX-ROADMAP.md`, `QUALITY-AND-VERIFICATION-ROADMAP.md`, `V3-RELEASE-ROADMAP.md`, `DECISIONS-AND-DEPENDENCIES.md`). `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` is preserved as the historical legacy baseline and no longer holds ordering authority; `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` is archived (its authority passed to `FRONTEND-AND-UX-ROADMAP.md`). Rows below are the 2026-11-23 snapshot and keep their original text except where the Disposition column has been corrected for this supersession.

---

## Inventory Table

| # | Document | Type | Original Date | Baseline SHA | Authority (A–E) | Current Role | Slices (roadmap IDs) | Dependencies | Overlap / Conflict | Unresolved Items | Disposition |
|---|----------|------|---------------|--------------|-----------------|--------------|----------------------|--------------|---------------------|------------------|-------------|
| 1 | `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` (in `docs/roadmap/`) | **Authoritative Roadmap** | 2026-11-18 | `816e923e` | **A** | Single execution roadmap; slices S0.1–S4.4 across 5 phases; owner decision register D-1..D-13 | S0.1–S4.4 | None (top of hierarchy) | — | None — 901 lines, measured at HEAD | **HISTORICAL / LEGACY BASELINE — superseded 2026-11-23 by the five canonical roadmaps (see `docs/roadmap/INDEX.md` §30); no execution/ordering authority** |
| 2 | `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` (in `docs/roadmap/`) | **Authoritative Roadmap (archived)** | 2026-11-18 | `816e923e` | **A** | Distinct accessibility workstream; phases 0-4 closed, phase 5 open | UI/UX phases 0-5 | MASTER roadmap (separate workstream) | Distinct from MASTER; no slice ID collision | Phase 5 open | **ARCHIVED — superseded by FRONTEND-AND-UX-ROADMAP.md** |
| 3 | `INDEX.md` (in `docs/roadmap/`) | **Registry / Index** | 2026-11-18 | `816e923e` | **A** | Registry of all masters, 29 consolidation rounds, cross-directory workflow rules | — | Both roadmaps above | — | — | **ACTIVE — registry** |
| 4 | `PLAN-TEMPLATE.md` | **Template / Contract Spec** | — | `816e923e` | **A** | Contract format specification for all executable plans (PLAN-CONTRACT schema) | — | Referenced by all plans | — | Only 4 plans currently have PLAN-CONTRACT | **ACTIVE — governing contract** |
| 5 | `implementation-plan-2026-11-20.md` | **Execution Plan (Active)** | 2026-11-20 | `816e923e` | **B** | Consolidated plan N0.1–N6.18; **peer-modified at HEAD** (2 status lines added: N3.2, N6.2 backend landed) | N0.1–N6.18 (44 slices) | MASTER roadmap S0.1–S4.4 via §8 crosswalk | Superseded by #6 (trainerdesk-complete-uiux) per its claim; §8 maps Nov 19→Nov 20 | 2 peer edits uncommitted; PLAN-CONTRACT missing | **ACTIVE but superseded — preserve peer edits** |
| 6 | `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | **Execution Plan (Superseding)** | 2026-11-20 | `816e923e` | **B** | "Comprehensive edition" — retains N0–N6 + adds Phase 7 (N7.1–N7.22), 11-flow matrix; claims to supersede #5 | N0.1–N7.22 | MASTER roadmap + UI-UX roadmap | Supersedes #5; overlaps UI-UX roadmap phase 5 | PLAN-CONTRACT missing; Phase 7 not in MASTER | **ACTIVE — designated primary** |
| 7 | `implementation-plan-2026-11-19.md` | **Execution Plan (Historical)** | 2026-11-19 | `fec658f8` | **C/D** | Outstanding work at HEAD `fec658f8`; 44 slices S0.1–S4.5; baseline for Nov 20 crosswalk | S0.1–S4.5 | MASTER roadmap | Crosswalk in #5 §8 maps 49 slices to Nov 20 N-IDs | PLAN-CONTRACT missing | **HISTORICAL — preserve as evidence** |
| 8 | `plan-tree-truth-2026-11-17.md` | **Execution Plan (4-part family, Plan 1/4)** | 2026-11-17 | `be8893a2` | **C/D** | Land tree (phase 0) + repair doc truth (phase 1); 380 lines | Phases 0-1 | → plan-truth-gates (Plan 2) | Sequence: 1→2→3→4; absorbed into later plans? | PLAN-CONTRACT missing | **HISTORICAL — determine absorption** |
| 9 | `plan-truth-gates-2026-11-17.md` | **Execution Plan (4-part family, Plan 2/4)** | 2026-11-17 | `be8893a2` | **C/D** | Test-suite and tooling truth (phase 2); 330 lines | Phase 2 | ← plan-tree-truth; → plan-defects | Sequence: 1→2→3→4 | PLAN-CONTRACT missing | **HISTORICAL — determine absorption** |
| 10 | `plan-defects-2026-11-17.md` | **Execution Plan (4-part family, Plan 3/4)** | 2026-11-17 | `be8893a2` | **C/D** | Defects with named code site (phase 3) — KI-166, 165, 163, 164, 104/ADR-0027; 517 lines | Phase 3 | ← plan-truth-gates; → plan-capability-release | Sequence: 1→2→3→4; KI-165 resolved by peer | PLAN-CONTRACT missing | **HISTORICAL — partial completion** |
| 11 | `plan-capability-release-2026-11-17.md` | **Execution Plan (4-part family, Plan 4/4)** | 2026-11-17 | `be8893a2` | **C/D** | Capability gaps and release bar (phases 4-5) — Team Rank, Unity Cup, owner-gated 4.3/4.4/4.5; 394 lines | Phases 4-5 | ← plan-defects | Sequence: 1→2→3→4; owner-gated items remain | PLAN-CONTRACT missing | **HISTORICAL — owner-gated items open** |
| 12 | `plan-ui-ux-implementation-2026-11-18.md` | **Execution Plan (Executed)** | 2026-11-18 | `cfd8bd78` | **D** | UI/UX outcome from AUD-20261118-001; 10 slices S1–S10 executed at `cfd8bd78` | S1–S10 | UI-UX roadmap | Executed; evidence in sitreps | PLAN-CONTRACT missing | **HISTORICAL — executed, preserve** |
| 13 | `nativephp-windows-ci-repair-2026-11-21.md` | **Execution Plan (Specialized)** | 2026-11-21 | `816e923e` | **C** | **HAS PLAN-CONTRACT** — CI repair for Windows gates, installer production, packaged acceptance | S0.2, S4.4 (per contract) | MASTER roadmap S0.2, S4.4 | Specialized NativePHP workstream | PLAN-CONTRACT present: `plan=nativephp-windows-ci-repair-2026-11-21, status=drafted, slices=S0.2,S4.4` | **ACTIVE — specialized workstream** |
| 14 | `legacy-spark-target-entry-2026-11-20.md` | **Execution Plan (Specialized)** | 2026-11-20 | `816e923e` | **C** | **HAS PLAN-CONTRACT** — Race/skill spark entry on Legacy surfaces; **EXECUTED 2026-11-20** | (none — executed) | — | Completed; contract shows `status=draft→executable` | PLAN-CONTRACT present: `slices=[], status=draft→executable` | **HISTORICAL — executed, preserve** |
| 15 | `nativephp-v3.0.0-implementation-plan-2026-11-20.md` | **Execution Plan (Specialized)** | 2026-11-20 | `816e923e` | **C** | NativePHP desktop packaging for v3.0.0 Windows x64; planning only | N0-N5 (internal) | MASTER roadmap S4.4? | Overlaps #13 (CI repair); #13 has contract, this doesn't | PLAN-CONTRACT missing | **ACTIVE — specialized, needs contract** |
| 16 | `frontend-colour-update-2026-11-14.md` | **Execution Plan (Specialized)** | 2026-11-14 | `d8238c87` | **C** | Ink family re-theme + dead `bg-warning` repair; 3 slices, owner-ruled | C1, C2 (internal) | DESIGN.md, DesignTokensTest guards | Distinct from UI-UX roadmap; audit-driven | Owner rulings required on C1/C2; PLAN-CONTRACT missing | **ACTIVE — specialized, needs contract** |
| 17 | `frontend-stat-colours-and-dark-mode-integration.md` | **Execution Plan (Specialized)** | 2026-11-14? | — | **C** | Stat colours + dark mode integration; references colour-update plan | (internal) | #16 colour-update | Related to #16; semantic family scope | PLAN-CONTRACT missing | **ACTIVE — specialized, needs contract** |
| 18 | `hermes-execution-plan-2026-11-17.md` | **Execution Plan (Session-specific)** | 2026-11-17 | `be8893a2` | **C/D** | Pass 2 after tree moved; supersedes roadmap ordering/counts; measured at `be8893a2` | S-0.1–S-4.4 (internal) | MASTER roadmap? | Supersedes `remaining-work-roadmap-2026-11-17.md` ordering | PLAN-CONTRACT missing; peer worktree complications | **HISTORICAL — session-specific** |
| 19 | `implementation-plan-2026-11-17-s18.md` | **Execution Plan (Scenario-flow surface)** | 2026-11-17 | `be8893a2` | **C** | S18 scenario-flow surface; slices A1-A4, B1-B2, Z verification | A1-A4, B1-B2, Z | `implementation-roadmap-2026-11-17-s18.md` (roadmap) | Paired with sibling roadmap; distinct from main roadmap | PLAN-CONTRACT missing | **HISTORICAL — session-specific** |
| 20 | `implementation-plan-2026-11-17.md` | **Execution Plan (Paired with roadmap)** | 2026-11-17 | `248e1cbb` | **C/D** | Paired with `implementation-roadmap-2026-11-17.md`; slices S1.1-S7 | S1.1-S7 | `implementation-roadmap-2026-11-17.md` | Predecessor to S18 plan (#19) | PLAN-CONTRACT missing | **HISTORICAL — superseded by S18** |
| 21 | `implementation-plan-hermes-2026-11-16.md` | **Execution Plan (Hermes session)** | 2026-11-16 | `d417a9a5` | **C/D** | Hermes session plan; tasks R0-R10; pairs with `implementation-roadmap-hermes-2026-11-16.md` | R0-R10 | `implementation-roadmap-hermes-2026-11-16.md` | Predecessor to Nov 17 plans | PLAN-CONTRACT missing | **HISTORICAL — session-specific** |
| 22 | `implementation-roadmap-v3.0.0.md` | **Roadmap (Superseded)** | 2026-11-16? | — | **D** | v3.0.0 roadmap; superseded by MASTER roadmap? | (various) | — | Likely superseded by MASTER (Nov 18) | PLAN-CONTRACT missing | **HISTORICAL — determine status** |
| 23 | `optimal-approach-2026-11-17-s18-release.md` | **Execution Plan / Approach** | 2026-11-17 | `be8893a2` | **C** | **Supersedes** `v3.0.0-release-plan-2026-11-17.md`; 5 slices to v3.0.0 tag | 1-5 (internal) | MASTER roadmap? | Supersedes v3.0.0-release-plan; some items executed (§12) | PLAN-CONTRACT missing | **ACTIVE — approach doc, some executed** |
| 24 | `project-plan-2026-11-16.md` | **Execution Plan (Paired)** | 2026-11-16 | `3cbbd8b9` | **C/D** | Pairs with `project-roadmap-2026-11-16.md`; phases A-E | A.1-A.4, B.1-B.4, C, D, E | `project-roadmap-2026-11-16.md` | Early plan; likely absorbed into later plans | PLAN-CONTRACT missing | **HISTORICAL — determine absorption** |
| 25 | `remediation-plan-2026-11-15.md` | **Remediation Plan (Proposal)** | 2026-11-15 | `2ae2de54` | **C/D** | **Status: proposal** — transfer 500s, schema drift, form 500s, state wipes; 4 slices + ADR obligation | 1a, 1b, 2, 3a, 3b, 4 | — | Proposal only; no code changes; KI entry + ADR-0031 needed | PLAN-CONTRACT missing | **HISTORICAL — proposal, needs execution** |
| 26 | `trainerdesk-four-screen-ux-review-2026-11-19.md` | **UX Review / Findings** | 2026-11-19 | `8fd7e18e` | **E** | Review of Review/Skills/Veterans/Races screens; implementation update 2026-11-20 | (review slices) | UI-UX roadmap | Executed per update; evidence in §Implementation update | PLAN-CONTRACT missing (type E — findings) | **HISTORICAL — executed review** |
| 27 | `v3.0.0-execution-plan-2026-11-16.md` | **Execution Plan (Checklist)** | 2026-11-16 | `d417a9a5` | **C/D** | Ordered TODO paired with `v3.0.0-implementation-roadmap-2026-11-16.md`; phases R1-R4 | R1.1-R4.3 | `v3.0.0-implementation-roadmap-2026-11-16.md` | Early checklist; superseded by optimal-approach (#23) | PLAN-CONTRACT missing | **HISTORICAL — superseded** |
| 28 | `finish-plan-2026-11-17.md` | **Execution Plan (Finish work)** | 2026-11-17 | — | **C/D** | Finish plan for Nov 17 work; 12.7K lines | (unknown — need read) | — | Need to classify | PLAN-CONTRACT missing | **PENDING — read required** |
| 29 | `career-legacy-manual-input-findings.md` | **Findings / Evidence** | 2026-11-17? | — | **E** | Findings from career legacy manual input; 3K lines | (findings) | — | Type E — evidence, not executable | PLAN-CONTRACT missing (type E) | **HISTORICAL — findings** |
| 30 | `implementation-plan-2026-11-16.md` | **Execution Plan** | 2026-11-16 | — | **C/D** | 27K lines; need read | (unknown) | — | Need to classify | PLAN-CONTRACT missing | **PENDING — read required** |

---

## Classification Summary

| Authority Tier | Count | Documents |
|----------------|-------|-----------|
| **A — Authoritative Roadmaps/Governance** | 4 | MASTER roadmap, UI-UX roadmap, INDEX.md, PLAN-TEMPLATE.md |
| **B — Active Execution Plans (current)** | 2 | implementation-plan-2026-11-20.md, trainerdesk-complete-uiux-2026-11-20.md |
| **C — Specialized/Session Execution Plans** | 11 | nativephp-windows-ci-repair, legacy-spark, nativephp-v3, frontend-colour, frontend-stat, hermes, S18, Nov17, Hermes, optimal-approach, remediation |
| **D — Historical Execution Plans (superseded/absorbed)** | 10 | plan-tree-truth, plan-truth-gates, plan-defects, plan-capability-release, plan-ui-ux, Nov17 paired, Nov16 paired, project-plan, v3.0.0-execution, implementation-roadmap-v3 |
| **E — Reviews/Findings/Evidence (not executable)** | 3 | four-screen-ux-review, career-legacy-findings, (template) |

**Total accounted:** 30 documents (26 in `docs/plan/` + 4 in `docs/roadmap/`)

---

## PLAN-CONTRACT Status

| Document | Has PLAN-CONTRACT? | Contract Fields |
|----------|-------------------|-----------------|
| `PLAN-TEMPLATE.md` | ✅ (example) | Template shows format |
| `nativephp-windows-ci-repair-2026-11-21.md` | ✅ | `plan=nativephp-windows-ci-repair-2026-11-21, status=drafted, slices=S0.2,S4.4, superseded-by=—, amended-by=—, open=—` |
| `legacy-spark-target-entry-2026-11-20.md` | ✅ | `plan=legacy-spark-target-entry-2026-11-20, status=draft→executable, slices=[], superseded-by=—, amended-by=—, open=—` |
| `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | ❌ | **MISSING** — active plan |
| `implementation-plan-2026-11-20.md` | ❌ | **MISSING** — active but superseded |
| `nativephp-v3.0.0-implementation-plan-2026-11-20.md` | ❌ | **MISSING** — specialized active |
| `frontend-colour-update-2026-11-14.md` | ❌ | **MISSING** — specialized active |
| `frontend-stat-colours-and-dark-mode-integration.md` | ❌ | **MISSING** — specialized active |
| All other 22 plans | ❌ | **MISSING** — historical or superseded |

**Action required:** Add PLAN-CONTRACT to all executable plans (tiers B and C) per PLAN-TEMPLATE.md rules.

---

## Supersession / Amendment Relationships (To Be Established)

| Superseded Document | Superseded By | Relationship | Reciprocal Link Required |
|---------------------|---------------|--------------|--------------------------|
| `implementation-plan-2026-11-20.md` | `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | Superseded (comprehensive edition) | ✅ Both ways |
| `implementation-plan-2026-11-19.md` | `implementation-plan-2026-11-20.md` | Superseded (next day) | ✅ Both ways |
| `plan-tree-truth-2026-11-17.md` → `plan-truth-gates-2026-11-17.md` → `plan-defects-2026-11-17.md` → `plan-capability-release-2026-11-17.md` | Later consolidated plans? | Sequence family (1→2→3→4) | Chain links |
| `implementation-plan-2026-11-17.md` | `implementation-plan-2026-11-17-s18.md` | Superseded (S18 follow-through) | ✅ Both ways |
| `implementation-plan-hermes-2026-11-16.md` | Nov 17 plans | Superseded (session predecessor) | ✅ Both ways |
| `v3.0.0-execution-plan-2026-11-16.md` | `optimal-approach-2026-11-17-s18-release.md` | Superseded (explicit in optimal-approach) | ✅ Both ways |
| `implementation-roadmap-v3.0.0.md` | `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Likely superseded | ✅ Both ways |
| `project-plan-2026-11-16.md` | Later plans? | Absorbed? | Determine |
| `remediation-plan-2026-11-15.md` | Executed slices? | Proposal → execution? | Determine |

---

## Cross-Document Consistency Issues (To Resolve)

1. **Broken links** — Verify all relative links in plans resolve to existing files
2. **Stale supersession claims** — #6 claims to supersede #5 but #5 has peer edits; reciprocal links missing
3. **Duplicate slice ownership** — Multiple plans claim S0.2, S4.4 (MASTER, nativephp-ci-repair, nativephp-v3)
4. **Missing PLAN-CONTRACT** — 26 of 30 executable plans lack contract blocks
5. **Slice ID namespace collisions** — S0.1 in MASTER ≠ S0.1 in UI-UX ≠ S0.1 in hermes; need qualified IDs
6. **Index vs inventory drift** — `docs/roadmap/INDEX.md` lists 29 consolidation rounds; plan registry not yet built
7. **Peer edits uncommitted** — `implementation-plan-2026-11-20.md` has 2 status lines added by another session; must preserve

---

## Next Actions (Priority Order)

1. **Add PLAN-CONTRACT blocks** to all tier B and C plans (8 documents minimum)
2. **Establish reciprocal supersession links** for 9 confirmed relationships
3. **Qualify slice IDs** with plan namespace (e.g., `MASTER:S0.1`, `UI-UX:S1`, `HERMES:S-0.1`)
4. **Build plan registry** (Deliverable B) — single Markdown index with all plans, status, slices, links
5. **Create task ownership crosswalk** (Deliverable C) — slice → owner decision register mapping
6. **Run validation**: `python tools/md_fence_check.py docs/plan/`, PLAN-CONTRACT syntax, unique qualified IDs, reciprocal links, active slice ownership, relative links
7. **Produce consolidation report** (Deliverable D) — this inventory + decisions + diffs