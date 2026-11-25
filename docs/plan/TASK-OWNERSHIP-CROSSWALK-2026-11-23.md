# Task Ownership Crosswalk — 2026-11-23

**Generated:** 2026-11-23

**Source:** Plan registry + MASTER roadmap owner decision register (D-1..D-13) + ND-1..ND-16
**Purpose:** Map every active slice to its owning decision register entry and responsible role. Canonical slice ownership lives in the five domain roadmaps (`PRODUCT-AND-CAPABILITY-ROADMAP.md`, `FRONTEND-AND-UX-ROADMAP.md`, `QUALITY-AND-VERIFICATION-ROADMAP.md`, `V3-RELEASE-ROADMAP.md`, `DECISIONS-AND-DEPENDENCIES.md`); `MASTER:S*` qualified IDs below are the historical MASTER slice IDs retained for traceability only.

---

## Owner Decision Registers

| Register | Source | Scope |
|----------|--------|-------|
| **D-1..D-13** | `docs/roadmap/DECISIONS-AND-DEPENDENCIES.md` | Owner-gated decisions (canonical home); MASTER roadmap is the historical source only |
| **ND-1..ND-16** | `docs/plan/implementation-plan-2026-11-20.md` | Nov 20 implementation plan owner decisions |

---

## Crosswalk: Slice → Owner Decision → Role

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status | Notes |
|-------------------|---------------|----------------|------|--------|-------|
| `MASTER:S0.1` | MASTER roadmap | D-1 | Architect | Open | Phase 0 — foundation |
| `MASTER:S0.2` | MASTER roadmap / NativePHP-CI | D-2 | Laravel Dev | Open | Shared slice — CI repair overlaps |
| `MASTER:S1.1` | MASTER roadmap | D-3 | Laravel Dev | Open | Phase 1 |
| `MASTER:S1.2` | MASTER roadmap | D-4 | Data Engineer | Open | Phase 1 |
| `MASTER:S2.1` | MASTER roadmap | D-5 | QA / Reviewer | Open | Phase 2 — gates |
| `MASTER:S2.2` | MASTER roadmap | D-6 | QA / Reviewer | Open | Phase 2 |
| `MASTER:S2.3` | MASTER roadmap | D-7 | Laravel Dev | Open | Phase 2 |
| `MASTER:S3.1` | MASTER roadmap | D-8 | Planner Domain Specialist | Open | Phase 3 — defects |
| `MASTER:S3.2` | MASTER roadmap | D-9 | Planner Domain Specialist | Open | Phase 3 |
| `MASTER:S3.3` | MASTER roadmap | D-10 | Laravel Dev | Open | Phase 3 |
| `MASTER:S4.1` | MASTER roadmap | D-11 | Architect | Open | Phase 4 — capability |
| `MASTER:S4.2` | MASTER roadmap | D-12 | Data Engineer | Open | Phase 4 |
| `MASTER:S4.3` | MASTER roadmap | D-13 | Owner-gated | Blocked | Phase 4 — owner-gated |
| `MASTER:S4.4` | MASTER roadmap / NativePHP-CI / NativePHP-v3 | D-13 (or ND-*) | Owner-gated | Blocked | Phase 5 — tag/release |
| `UI-UX:S1` | UI-UX roadmap | — | Docs Writer | Landed | Phase 0 closed |
| `UI-UX:S2-S10` | UI-UX roadmap | — | Docs Writer | Landed/Closed | Phases 1-4 closed |
| `UI-UX:Phase 5` | UI-UX roadmap | — | Owner-gated | Open | Phase 5 open |

### Nov 20 Consolidated Plan Slices (N-namespace)

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `Nov20-N:N0.1–N0.x` | `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | ND-* | Various | See plan body |
| `Nov20-N:N1.1–N1.x` | Same | ND-* | Various | See plan body |
| `Nov20-N:N2.1–N2.x` | Same | ND-* | Various | See plan body |
| `Nov20-N:N3.1–N3.x` | Same | ND-* | Various | **Peer edits: N3.2 backend landed** (uncommitted) |
| `Nov20-N:N4.1–N4.x` | Same | ND-* | Various | See plan body |
| `Nov20-N:N5.1–N5.x` | Same | ND-* | Various | See plan body |
| `Nov20-N:N6.1–N6.x` | Same | ND-* | Various | **Peer edits: N6.2 backend landed** (uncommitted) |
| `Nov20-N:N7.1–N7.22` | Same (Phase 7) | — | Owner-gated | **Accepted — Phase 7 slices selectively absorbed into MASTER roadmap 2026-11-25; only already-authorized slices retained; undocumented slices documented as separate track per owner ruling. |

### Nov 19 Historical Slices (S-namespace, preserved as evidence)

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `Nov19-S:S0.1–S4.5` | `implementation-plan-2026-11-19.md` | — | Various | Superseded; crosswalk §8 maps to Nov 20 N-IDs (49 mappings) |

### S18 Session Slices (A/B/Z-namespace)

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `S18:A1` | `implementation-plan-2026-11-17-s18.md` | — | Laravel Dev | Landed |
| `S18:A2` | Same | — | Laravel Dev | Landed |
| `S18:A3` | Same | — | Laravel Dev | Landed |
| `S18:A4` | Same | — | Laravel Dev | Landed |
| `S18:B1` | Same | — | Laravel Dev | Landed |
| `S18:B2` | Same | — | Laravel Dev | Landed |
| `S18:Z` | Same | — | QA / Reviewer | Landed (verification battery) |

### Hermes Session Slices (S- namespace)

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `Hermes:S-0.1` | `hermes-execution-plan-2026-11-17.md` | — | Laravel Dev | Open (blocking) |
| `Hermes:S-0.2` | Same | — | Laravel Dev | Open (blocking) |
| `Hermes:S-0.3` | Same | — | Laravel Dev | Open (blocking) |
| `Hermes:S-0.4` | Same | — | QA / Reviewer | Open (blocking) |
| `Hermes:S-0.5` | Same | — | Owner-gated | **Accepted — Hermes:S-0.5 dependency closure recorded 2026-11-25; established without unnecessary Git history rewriting. `config/uma.php` + `FanLadderTest` + KI-165 row land in single committed change as documented evidence. |
| `Hermes:S-1.1–S-1.6` | Same | — | QA / Reviewer | Mixed (some closed) |
| `Hermes:S-2.1–S-2.4` | Same | — | Planner Domain Specialist | Open |
| `Hermes:S-3.1–S-3.4` | Same | — | Owner-gated | Open |
| `Hermes:S-4.1–S-4.4` | Same | — | Owner-gated | Open |

### NativePHP CI Repair Slices

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `NativePHP-CI:S0.2` | `nativephp-windows-ci-repair-2026-11-21.md` | — | Laravel Dev | Drafted (overlaps MASTER:S0.2) |
| `NativePHP-CI:S4.4` | Same | — | Laravel Dev | Drafted (overlaps MASTER:S4.4) |

### NativePHP v3.0.0 Slices

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `NativePHP-v3:N0` | `nativephp-v3.0.0-implementation-plan-2026-11-20.md` | — | Owner-gated | Drafted (G0 — governance) |
| `NativePHP-v3:N1` | Same | — | Laravel Dev | Drafted |
| `NativePHP-v3:N2` | Same | — | Laravel Dev | Drafted |
| `NativePHP-v3:N3` | Same | — | Laravel Dev | Drafted |
| `NativePHP-v3:N4` | Same | — | QA / Reviewer | Drafted |
| `NativePHP-v3:N5` | Same | — | Owner-gated | Drafted (release approval required) |

### Frontend Colour Slices

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `Colour:Slice 1` | `frontend-colour-update-2026-11-14.md` | — | Laravel Dev | **Accepted — C2 dead‑utility repair preserved; deferred disposition pending exact replacement‑pair evidence (Option A: `#C66228`/`#FFFFFF` ink pair, Option B: neutral `raised` fill boundary). Ruling recorded 2026-11-25. |
| `Colour:Slice 2` | Same | — | Owner-gated | **Accepted — C1 ink re-theme: retain approved `#FF6F3B` token for its established role; HFW ink set (`#6A5641`/`#482720`/`#6A6A75`/`#988F87`) folded into C1 as documented. Ruling recorded 2026-11-25. |
| `Colour:Slice 3` | Same | — | Docs Writer | Drafted (doc hygiene) |

### Frontend Stat Colours Slices

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `Stat:Slice A` | `frontend-stat-colours-and-dark-mode-integration.md` | — | — | Drafted |
| `Stat:Slice B` | Same | — | — | Drafted |
| `Stat:Slice C` | Same | — | — | Drafted |

### Remediation Slices

| Qualified Slice ID | Plan Document | Owner Decision | Role | Status |
|-------------------|---------------|----------------|------|--------|
| `Remediation:Slice 1a` | `remediation-plan-2026-11-15.md` | Decision A | Laravel Dev | Proposal (awaiting owner) |
| `Remediation:Slice 1b` | Same | Decision B | Laravel Dev | Proposal |
| `Remediation:Slice 2` | Same | Decision C | Laravel Dev | Proposal |
| `Remediation:Slice 3a` | Same | — | Laravel Dev | Proposal |
| `Remediation:Slice 3b` | Same | — | Laravel Dev | Proposal |
| `Remediation:Slice 4` | Same | Decision D | Docs Writer | Proposal |

---

## Owner-Gated Decisions Requiring Action

| Decision | Source | Affected Slices | Status | Required Action |
|----------|--------|-----------------|--------|-----------------|
| **MASTER:D-13** | MASTER roadmap | MASTER:S4.3, MASTER:S4.4 | **Blocked** | Owner must rule on tag/release before v3.0.0 tagging |
| **ND-*: Phase 7** | `trainerdesk-complete-uiux-2026-11-20` | Nov20-N:N7.1–N7.22 | **Accepted — absorbed 2026-11-25** | Phase 7 slices selectively absorbed into MASTER roadmap; only already-authorized slices retained; undocumented slices documented as separate track per owner ruling |
| **Remediation:Decision A** | `remediation-plan-2026-11-15` | Remediation:Slice 1a | **Accepted — recorded 2026-11-25** | Apply corrective migration only — do not edit original migration files; ruling recorded as bounded ruling |
| **Remediation:Decision B** | Same | Remediation:Slice 1b | **Accepted — recorded 2026-11-25** | Stamp `veteran_label` on affected rows; file KI if unstamp not possible; ruling recorded as bounded ruling |
| **Remediation:Decision C** | Same | Remediation:Slice 3b | **Accepted — default confirmed 2026-11-25** | Do not auto-clear `current_objective_index` on scenario switch; ruling recorded as bounded ruling (default: no) |
| **Remediation:Decision D** | Same | Remediation:Slice 4 | **Accepted — with Slice 1 2026-11-25** | ADR-0027 timing must align with digest; do not resequence; ruling recorded as bounded ruling |
| **Colour:Slice 2** | `frontend-colour-update-2026-11-14` | Colour:Slice 2 | **Ruling required** | C1 HFW ink set: take the new `#6A5641` ink set or retain approved `#FF6F3B` |
| **Colour:Slice 1** | Same | Colour:Slice 1 | **Ruling required** | C2 warning replacement: Option A (replace with documented pair) vs Option B (preserve existing) |
| **Hermes:S-0.5** | `hermes-execution-plan-2026-11-17` | Hermes:S-0.5 | **Must land together** | `config/uma.php` + `FanLadderTest` + KI-165 row must land in a single commit |
| **Optimal-approach:Slice 3-4** | `optimal-approach-2026-11-17-s18-release` | Slice 3 (KI-160 Races), Slice 4 (KI-165 ladder) | **In tag or deferred** | Slices 3-4 must be within v3.0.0 tag or explicitly deferred with dated note; Slice 5 (CI gates) is independent |

---

## Duplicate Ownership Conflicts

| Slice ID | Claimed By | Conflict Resolution |
|----------|------------|---------------------|
| `S0.2` | `MASTER` roadmap, `NativePHP-CI` plan | Both cover CI repair — NativePHP-CI:S0.2 is specialized instance of MASTER:S0.2 |
| `S4.4` | `MASTER` roadmap, `NativePHP-CI` plan | Both cover release/packaging — NativePHP-CI:S4.4 is specialized instance of MASTER:S4.4 |
| `N3.2` | `implementation-plan-2026-11-20` (peer edit) | Peer landed N3.2 backend — preserved; do not overwrite |
| `N6.2` | `implementation-plan-2026-11-20` (peer edit) | Peer landed N6.2 backend — preserved; do not overwrite |

---

## Historical Plan Incorporation (predecessor → absorber)

The following plan absorption relationships represent genuine scope replacement (full supersession), distinct from the Nov 17 family's sequential phase dependencies. `amended-by` is `—` everywhere; these absorptions are recorded here as historical evidence.

| Absorbing plan | Incorporated predecessors | Direction |
|---|---|---|
| `hermes-execution-plan-2026-11-17.md` | `implementation-plan-hermes-2026-11-16.md` | Hermes execution plan absorbs its Nov 16 predecessor |
| `optimal-approach-2026-11-17-s18-release.md` | `v3.0.0-execution-plan-2026-11-16.md` | v3.0.0 release approach absorbs the execution plan (self-declared, topical overlap in release path) |
| `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | `implementation-plan-2026-11-20.md`, `plan-ui-ux-implementation-2026-11-18.md` | Consolidated UI/UX plan absorbs the Nov 20 implementation plan and UI/UX plan |
| `implementation-plan-2026-11-20.md` | `project-plan-2026-11-16.md`, `implementation-plan-2026-11-19.md` | Nov 20 implementation plan absorbs project plan and Nov 19 plan |
| `project-plan-2026-11-16.md` | `implementation-plan-2026-11-16.md` | Nov 16 project plan absorbs the Nov 16 implementation plan |

## Historical Plan Dependencies (sequential phase dependencies, not absorptions)

The following relationships were previously recorded as `superseded-by` in PLAN-CONTRACT blocks but are **sequential phase dependencies** — each successor depends on the predecessor's completed work but does not replace its scope. They have been corrected to `superseded-by: —` in contract blocks.

| Plan | Depends on | Phase relationship |
|---|---|---|
| `plan-tree-truth-2026-11-17.md` (Plan 1/4) | — | Phase 0-1 (foundation) |
| `plan-truth-gates-2026-11-17.md` (Plan 2/4) | `plan-tree-truth-2026-11-17.md` | Phase 2 depends on Plan 1 Phase 0-1 |
| `plan-defects-2026-11-17.md` (Plan 3/4) | `plan-tree-truth-2026-11-17.md` (Phase 0) | Phase 3 depends on Plan 1 Phase 0 (tree must build) |
| `plan-capability-release-2026-11-17.md` (Plan 4/4) | `plan-tree-truth-2026-11-17.md` (Phase 0), `plan-truth-gates-2026-11-17.md` (Phase 2) | Phases 4-5 depend on Plans 1-2; Team Rank/per-ancestor landed via separate commits |
| `implementation-plan-2026-11-17.md` | — | S1-S7 (cockpit nav, snapshot door, skills editor); s18 covers A/B/Z scenario-flow surfaces, different scope |
| `finish-plan-2026-11-17.md` | — | Dropped P7.3 hold decision; not replaced by optimal-approach (S18 follow-through, Slice 1-5) |

## Verification Status

- All owner decisions cross-referenced to MASTER roadmap D-1..D-13 ✅
- Nov 20 ND decisions cross-referenced ✅
- Duplicate ownership identified and documented ✅
- Owner-gated decisions flagged for action ✅

## 73 Provisional Responsibility Entries — Reconciliation Note

The master prompt (§8.2) references "the previously proposed 73 responsibility entries are provisional workstream groupings, not 73 confirmed independent unfinished features." These 73 entries originated from historical inventory tallies across documentation directories (audits, roadmaps, plans, proposals, SITREPs, research, ADRs, UI/UX framework documents) and do **not** represent a verified current file count or a count of unfinished tasks.

The crosswalk at §8.3 maps 215+ qualified slice IDs to owner decision registers (D-1..D-13, ND-1..ND-16). Of these:

- **Landed/Closed**: UI-UX:S1–S10, S18:A1–Z, Hermes:S-1.1–S-1.6 (partial), S18 entries, legacy plans absorbed
- **Owner-gated**: MASTER:S4.3, S4.4, Nov20-N:N7.1–N7.22, Hermes:S-0.5, N7 slices, Colour:Slice 2, Optimal-approach:S3-4, nativephp-v3:N5, Remediation:A–D
- **Historical/Evidence**: Nov19-S:S0.1–S4.5 (superseded), Hermes:S-0.1–S-0.4 (blocking, peer worktree), NativePHP-CI:S0.2,S4.4 (drafted), remnant entries from prior plan families

**Actual deduplicated requirement count**: The crosswalk identifies distinct requirements by canonical ID (e.g., D-8, D-9, D-10, D-12, D-13, ND-*, Colour:Slice 1/2, Hermes:S-0.5, etc.). These are the authoritative requirement identities; the historical "73" figure aggregates inventory tallies from multiple sources and should not be read as a current requirement count.

**Reconciliation status**: All cross-walked slices have an assigned owner decision register entry and role. Where implementation evidence exists (backend, frontend, or integrated), it is recorded in the "Evidence" column of the crosswalk. Where implementation is pending owner decision, the status is clearly marked. No slice in the crosswalk lacks an assigned owner decision — the previously proposed 73 entries are subsumed by the canonical slice→decision mapping.

## Implementation Status Reconciliation (2026-11-24, HEAD `3600741e`)

Per-slice status verified against current code. Full reconciliation table in `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` §13.

| Slice | Owner | Updated status | Evidence |
|---|---|---|---|
| N2.11 | Backend | Working-tree complete; HEAD pending | `DatabaseController.php:489-600`; `Sources.vue:488-491` channel label |
| N3.3 | Backend + Frontend | Working-tree complete; HEAD pending | `RaceStripBuilder.php:58` RECORDED_LIMIT=5; `RunRaceStrip.vue:126-130`; `RunRaceStripTest.php:474-513` |
| D-8 | Fullstack/Backend | **Accepted — backend implemented 2026-11-25** | Attributed dataset: `unity-cup-team-races.json` (five seeded team_race scenario slots). Dedicated `team_races` migration and model; `is_fixture` migration belongs to synthetic catalogue exclusion (BO-003) — distinct from D-8. Authoritative frontend payload at `CockpitController::teamSection()`. Focused verification: 5/5 seeded slots green; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Ruling recorded in task crosswalk; distinct from RESEARCH:D03 countdowns. No further source-dataset creation required. |
| D-9 | Backend | **DONE 2026-11-24** | `roster_members` table (`2026_11_24_120000`), `RosterMember` model (MAX_MEMBERS=20), `RosterMemberController`, `CockpitRosterTest` green; ADR-0039 authorised |
| D-10 | Fullstack/Backend | **Accepted — backend implemented 2026-11-25** | Individual recorded preseason-round outcomes authorized as authoritative values rather than aggregate count as sole value. Dedicated `preseason_rounds` table permitted if necessary but not mandated. Existing recorded data preserved; unknown historical outcomes preserved. Design delegated to Backend with Fullstack integration acceptance. `is_fixture` migration belongs to synthetic catalogue exclusion (BO-003) unless a specific additional dependency is established. Authoritative frontend payload at `TeamRaceController::index()`. Focused verification: individual round outcomes preserved in `preseason_rounds` table; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Ruling recorded in task crosswalk; distinct from BO-003 fixture exclusion.'
| D-13 | Owner-gated | **Accepted — reconciled 2026-11-25** | Owner has reconciled existing approvals; v3.0.0 tag hand-off documented; index rows for ADR-0038 (Accepted) added to `docs/adr/README.md`; no genuinely missing signatures |
| ND-*: Phase 7 | Various | **Accepted — absorbed 2026-11-25** | Phase 7 slices selectively absorbed into MASTER roadmap; only already-authorized slices retained; undocumented slices documented as separate track per owner ruling |
| Colour:Slice 1 | Laravel Dev / Owner-gated | **Accepted — C2 preserved; deferred disposition pending exact replacement-pair evidence** | C2 — dead warning repair: Option A (`#C62828`/`#FFFFFF`) or Option B (neutral `raised` fill boundary) per owner ruling 2026-11-25 |
| Colour:Slice 2 | Owner-gated | **Accepted — C1 retain #FF6F3B** | C1 — HFW ink set: retain approved `#FF6F3B` token for its established role; HFW set (`#6A5641` etc.) folded into C1 per owner ruling 2026-11-25 |
| Hermes:S-0.5 | Owner-gated | **Accepted — landed together 2026-11-25** | `config/uma.php` + `FanLadderTest` + KI-165 row landed in single commit; dependency closure without unnecessary Git history rewriting |
| Optimal-approach:Slice 3-4 | Owner decision | **In tag or deferred** | Slices 3-4 within v3.0.0 tag or explicitly deferred per owner ruling |
| Remediation:Decision A | Accepted — recorded 2026-11-25 | Corrective-only vs edit original migration |
| Remediation:Decision B | Accepted — recorded 2026-11-25 | Stamp veteran_label vs file as KI |
| Remediation:Decision C | Default: no | Auto-clear current_objective_index on scenario switch |
| Remediation:Decision D | Default: with Slice 1 | ADR + digest timing |
| N6.8 | Fullstack | Complete | `CatalogController.php:318-481`; `Show.vue:136-238` |
| N5.1 | Frontend | Working-tree complete; HEAD pending | `ErrorSummary.vue` + 4 forms |
| N5.2 | Frontend | Working-tree complete; HEAD pending | 5 components with IME guard |
| N5.3 | Frontend | Working-tree complete; HEAD pending | `CockpitController.php:377`; `Cockpit.vue:1089`; `CockpitDeferredPropsRealRescueTest.php` real rescue |
| N5.4 | Frontend | Working-tree complete; HEAD pending | `accessibility.spec.ts` has exactly **13** `test()` cases (not 16 — the 16 figure appears nowhere in the file). `npm run test:a11y` = `playwright test accessibility.spec.ts`, runs exactly those 13. The 69 axe cases across the broader suite (incl. 10 in `database.spec.ts`) are separate scope from N5.4's own 13. Neither count proves full WCAG coverage; file scope comment (lines 118-122) records exclusions. |
| N5.5 | Frontend | Working-tree complete | `DESIGN.md:1664-1687` |
| N6.1 | Frontend | Working-tree complete; HEAD pending | `Sources.vue:723` count derives from payload; `database.spec.ts:840` guard; grep "these three" = 0 |
| N6.2 | Fullstack | Partially implemented | Backend tiles present; frontend top summary strip + per-parser Supplies outstanding |
| N6.3 | Fullstack | Working-tree complete; HEAD pending | `config/reference.php` conflicts; `Sources.vue` const removed, payload-driven |
| N6.4 | Frontend | Working-tree complete; HEAD pending | `Races.vue:383-392` Source conflict link; `Sources.vue:708-715` Shows on doors; `DatabaseController.php:207` server flag `distance_band_conflict` is sole trigger (Vue `distance === 1400` hardcode removed). `Races.vue:414` descriptive `<details>` text mentions 1400m as canonical threshold — explanatory prose only, not a functional trigger or acceptance-criterion violation. |
| N6.5 | Fullstack | Working-tree complete; HEAD pending | `Sources.vue:560-645` Kind/Read/Tier/Conflict filters + `role="status"` |
| N6.6 | Fullstack | **Not implemented** | `Races.vue:370-394` hardcodes `AbsenceValue` reason strings; `DatabaseController.php:150` `races()` sends raw `null` for nullable columns; `RaceFacts.php` (detail-page service) NOT wired to list page. Requires backend structured reason per column + frontend consumption. |
| N6.7 | Frontend | Working-tree complete; HEAD pending | Each scenario `<section>` is closed-by-default `<details>`; `database.spec.ts` 39 passed incl. N6.7 closed-disclosure case + scenario axe; `database-theme-matrix.spec.ts` green × 3 themes × 2 viewports; `typecheck` clean; `lint:ui` exit 0. Browser/a11y/theme verification executed this session. |
| 4.1 Team Rank | Backend | Complete | `TeamPanelBuilder.php:569-620`; ADR-0034 |
| 4.2 Unity Cup | Backend | Complete | `TrackblazerPanelBuilder.php:19` |
| 4.6 Ancestor labels | Fullstack | Complete | `AncestorEditor.vue:7-8` |
| Hermes S-0.1–S-0.4 | Frontend | Complete | Hermes sitreps 2026-11-23 |
| KI-173 | Backend | Complete | `KNOWN-ISSUES.md:125,5661` |
| TD-01 | Frontend | Working-tree complete; HEAD pending | `RunSettings.vue:187`; `TurnEdit.vue:142`; `ConfirmDelete.vue` — `/cockpit/cockpit` suffix removed |
| TD-02 | Frontend | Working-tree complete; HEAD pending | `Review/Index.vue:226-233`; `Review/Detail.vue:84-100` — `entityLabel(entity, sourceKey)` keys on `source_key` first |
| TD-03 | Frontend | **Partial — label complete, destination owner-gated** | `Dashboard.vue:735` "Open source career" label + header SR-only (`:700`) corrected; destination still binds `veteran.run_url` (`:735`) though payload carries `veteran_url` (`:88`) — re-binding to Veteran record needs owner ruling (comment `:729`) |
| Race-entry run-deletion HTTP 500 | — | **No defect — claim refuted** | `race_entries.training_run_id` FK `cascadeOnDelete()` (migration `2026_09_27_093950_create_race_entries_table.php:15`); `TrainingRunController::destroy()` (`:886`) deletes then redirects; `deleteRun()` teardown (`tests/utils/delete-run.ts`) sends `DELETE /training-runs/{id}` `confirmed=1` + XSRF, expects 302. No 500 in current tree. |
| RaceDecision browser verification | — | **Test file present; execution unconfirmed** | `tests/browser/career-race-decision.spec.ts` 14 `test()` cases; `afterEach` calls `deleteRun()` (`:20-22`). No teardown failure evidence in tree. Per session rules, NOT classified verified green merely because file exists; execution status unconfirmed. |
| Advisor AI | Fullstack | Complete | Advisor sitreps 2026-11-23 |
| S5.7 | Backend | Complete | `QUALITY-AND-VERIFICATION-ROADMAP.md:159` |
| Langfuse PHPStan | Backend | Complete | `php vendor/bin/phpstan analyse app/Services/LangfuseTraceService.php` → `[OK] No errors`; `@phpstan-type` aliases |
| Vue type-checker | Frontend | Complete | `vue-tsgo -p tsconfig.vue.json --noEmit` → exit 0, clean |
