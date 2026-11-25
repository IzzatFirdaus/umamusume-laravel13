# Product and Capability Roadmap

**Authoritative roadmap for product capability.** This file is the single execution authority for all product-facing capability tasks: scenario surfaces (S18-E1, S18-E2, S18-A-P01, S18-RS-*), capability gaps (4.1-4.6), scenario detail arc, legacy builder and support card residuals, and MASTER phase-3 slices S3.1-S3.5. Canonical slice IDs retained from original sources. Historical sources archived in `docs/roadmap/archive/`. Completed work is marked and preserved as historical evidence; do not re-open.

---

## 1. Scenario Surfaces

| Slice | Scenario | Status | Evidence | Source |
|-------|----------|--------|----------|--------|
| S18-E1 | **Trackblazer** — largest untested surface; no prior Unity Cup cross-reference | **OPEN** | Manual walk pending; no browser spec | REMAINING-WORK §4.2, V3-R1.2 |
| S18-E2 | **Our Grand Concert** → **URA Finale** (control) | **PARTIALLY VERIFIED** | 10 items: 1/3/5/6/7 verified, 2 terminal by ruling, 4 terminal not built, 8/9/10 terminal absence/ruling | RESIDUAL-VERIFICATION §Slice 3.8 |
| S18-A-P01 | **Our Grand Concert** `goal_source` resolution | **DONE** | `S-A-P01-OG-1` report landed | REMAINING-WORK §1 |
| S18-RS-SPAN | Span-hole fix | **DONE** | `S-RS-SPAN-1` report landed | REMAINING-WORK §1 |
| S18-RS-KI154 | KI-154 sweep and fix | **DONE** | `S-RS-KI154-1` report landed | REMAINING-WORK §1 |

**Note**: EXECUTION-RECORD S3.1 confirms Independent Training door **DONE**; S3.2 hint-level copy **DONE**; S3.3 Japanese search key **DONE**.

---

## 2. Capability Gaps (from REMAINING-WORK Phase 4)

| Slice | Gap | Status | Blocker | Acceptance | Source |
|-------|-----|--------|---------|------------|--------|
| 4.1 | Team Rank cannot be entered anywhere — column exists, readers exist, **no writer** | **DONE** | Resolved 2026-11-24: `team_rank` rule in `StoreTrainingRunRequest` (`Rule::in(TeamRankPayload::LETTERS)`, includes `S+`), select on `UnityCupEditor.vue`, `latestTeamRank()` reads column then falls back to turn event | `TeamEditTest` and `StoreTrainingRunRequestTest` pass; Cockpit strip + Dashboard tile read it | REMAINING-WORK §4.1 |
| 4.2 | Unity Cup prints "declares no structure" for its finale — `config/scenarios.php` `unity_cup` has no `finale` key | **TERMINAL — KI-168** | Owner ruling on file: no distinct captured client string; adding config would invent UI copy. No code change warranted | KI-168 terminal register entry; only Trackblazer declares `finale` in `config/scenarios.php` | REMAINING-WORK §4.2 |
| 4.3 | Team-race slots can never be created — `StoreRaceEntryRequest` validates against `team_race` slot, no code creates one | **BLOCKED — OWNER** | Re-verified 2026-11-24: a fresh isolated browser database seeds only `goal_race` slots (296 rows), zero `team_race`; no owner source or terminal ruling landed | Source found and seeded, or terminal ruling in `KNOWN-ISSUES.md`; no implementation before the ruling | REMAINING-WORK §4.3, `DECISIONS-AND-DEPENDENCIES.md` D-8 |
| 4.4 | Roster (18 named / 20/20) is read-out of deck — no column, no form field | **DONE** | Resolved 2026-11-24: `roster_members` table (`2026_11_24_120000`), `RosterMember` model with 20-member ceiling + one-ACE-per-column guard, `RosterDistance` enum, `RosterMemberController` (store/update/destroy), `StoreRosterMemberRequest`, `CockpitController::rosterSection()` reads the recorded roster; authorised by **ADR-0039** (owner delegation 2026-11-18) | `CockpitRosterTest`, `RosterSeederReRunTest`, `RosterMemberTest`, `RosterDoesNotTouchTeamRankTest`, `RosterMembersForeignKeyRepairTest` pass; deck stays `deck_slots` (ADR-0014), never merged | REMAINING-WORK §4.4, `DECISIONS-AND-DEPENDENCIES.md` D-9, `ADR-0039` |
| 4.5 | Preseason rounds collapse to one count — `team_preseason_wins` tinyint only | **BLOCKED — OWNER** | Re-verified 2026-11-24: the column exists in a fresh isolated database; no per-round table or ADR landed | ADR or ruling recorded; no implementation before the ruling | REMAINING-WORK §4.5, `DECISIONS-AND-DEPENDENCIES.md` D-10 |
| 4.6 | Per-ancestor residuals — editor ships rank/stars/sparks/add-remove but **no guest flag, no identity input** | **DONE** | Resolved 2026-11-24: `legacies.*.is_guest` required boolean plus `ancestors_labels`/`ancestors_meta` rules in `StoreLegacySelectionRequest`, rebuilt by `LegacySelectionPayload::fromArray()` | `AncestorLabelTest` + `LegacyLabPageTest` round-trip both flag values; PRD §6.3 permits recording | REMAINING-WORK §4.6 |

---

## 3. Scenario Detail Arc (from REMAINING-WORK §1, V3 R2)

| Slice | Item | Status | Evidence |
|-------|------|--------|----------|
| S-RS-REMODEL-B1 | Remodel plan with W-ruling Addendum | **DONE** | `2026-11-10-scenario-detail-remodel.md` absorbed |
| S-RS-REMODEL-B2 | Phase 1/2 dispatches | **DONE** | Reports landed |
| S-RS-REMODEL-C1 | Phase 1 dispatch | **DONE** | Report landed |
| S-RS-REMODEL-C2 | Phase 2 report | **DONE** | Report landed |
| S-RS-VISUAL-1 | HFW visual overhaul | **DONE** | `S-RS-VISUAL-1-report` landed |
| S-RS-CLEANUP | Cleanup and ruling-prep | **DONE** | Reports landed |
| KI-154 | Scenario widget sweep | **DONE** | `ki-154-scenario-widget-sweep` landed |

---

## 4. Legacy Builder & Support Card Residuals (from IMPLEMENTATION-2026-11-17 S5, S18)

| Slice | Item | Status | Evidence |
|-------|------|--------|----------|
| S5.1 | `/api/v1/skills` catalog and runs only | **TERMINAL** | Recorded in `ARCHITECTURE.md:327`; PRD FR-E E-1 names "catalog and runs" only | PROJECT-ROADMAP §1 (R6) |
| S5.2 | NFR-19 diagnostics | **TERMINAL** | Recorded in `ARCHITECTURE.md:554` | PROJECT-ROADMAP §1 (R7) |
| S18-01..S18-07 | Surface fixes | **DONE** | Landed in worktree; `reconciliation-rounds/scenario-flow-surface-s18-handoff.md` §3 | REMAINING-WORK §1 |
| S18-10 | Owned-skills hand-off | **DONE** | `Review.vue` carries `data-owned-skills-handoff`; `SnapshotOwnedSkillsTest.php` exists (worktree) | REMAINING-WORK §1 |

---

## 5. Completed Product Work (excluded with evidence — do not re-open)

| Slice | What | Evidence |
|-------|------|----------|
| P1.1 | 14 MWG files banked | Commit `178a9079`; typecheck 0, build 0, cockpit-forms 14/14 |
| P1.2 | BO-011 dark risk token + focus ring | Token `#FF6F3B` in `app.css:531`; `DesignTokensTest` 17 passed |
| P2 | Dependency advisories | Framework 13.42.0; `composer audit` 1 (unreachable `symfony/yaml`); `npm audit` 0 |
| P3.1-P3.7 | Suite-truth fixes | Landed by peer commits (`44px` button, `f8e428d9`, `veterans-transfer-requests.spec.ts`) |
| P5.3 | `QWEN.md` landed true | Commit `3cbbd8b9`; census 716→691 ≤ baseline 708; `DocCitationParityTest` 2 passed |
| P5.5 | BO-004 dark `To do` pill | `--color-risk: #FF6F3B`; ratios 4.566/5.187/7.190 all ≥ 4.5:1 |
| R1 (KI-101) | Refresh navigation count | `navigation-determinism.spec.ts:75-95` asserts `toEqual(['reload'])` |
| R2 (KI-128) | `Delete career` placement | `Cockpit.vue:1230-1237` sits outside race `<section>` |
| R4 (KI-132) | Build-target payload contract | Partial payloads refused at `StoreBuildTargetRequest.php:69-71`; two-key split at `BuildTargetController::store():172-178` (ADR-0025) |
| KI-104 (code half) | ADR-0027 nested-disclosure | `UnityCupEditor.vue` `<form>` is component root; 2 remaining `<details>` hits are comment text |
| KI-116 | Reached card effects | `SupportCardEffects::forLevel()` at `SupportCardEffects.php:209`; consumed at `DeckAnalysis.php:109` |
| KI-119 | Current spirit proxy | Column migration `2026_11_10_010500`; Fillable/cast on `TrainingRun`; prop on `UnityCupPanel.vue:87` |
| KI-127 | Panel card wrapper | Current path `resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue` has `<div class="card p-3">`; isolated browser fixture 2/2 passed on 2026-11-24. The panel is a dead registration, so the composed-surface pin stays deferred (`KNOWN-ISSUES.md:78`) |
| KI-159/KI-141 | Absences | Closed in register with per-row evidence; `SupportSlot.vue` keeps `Level` input + `AbsenceValue` disclosures |
| KI-160 | Cockpit overflow 88px at 640px | Fixed in `NextDecision.vue:346` wrapper; specs at `career-race-strip.spec.ts:216`, `career-cockpit.spec.ts:318` |
| Race-entry RP-period boundary (2026-11-24, working tree — not at HEAD `3600741e`) | `objective_index` on a scenario composing no Result Point periods reached the model saving guard and answered HTTP 500 where the form shows a field error | Scenario gate added to `StoreRaceEntryRequest::rules()` (`objective_index` closure, lines 74-91) mirroring the `StoreTrainingRunRequest` live-period rule; the same working-tree change narrows `status` from `Rule::enum(RaceEntryStatus::class)` to `Rule::in(array_map(..., RaceEntryStatus::writable()))` so `Scheduled` import provenance cannot be hand-posted as an outcome. Pinned by `tests/Feature/RaceEntryObjectiveIndexBoundaryTest.php` (untracked, 2 cases: refuses a Result Point period on a scenario that composes none — `assertSessionHasErrors('objective_index')`; accepts the same period on a scenario that composes them), 2 passed |

---

## 6. Owner-Gated Items (require owner action before code)

| Slice | Item | Required Owner Action | Source |
|-------|------|----------------------|--------|
| S3.4 | Maiden-lock fallback | Owner approval | EXECUTION-RECORD S3.4 |
| S3.5 | Capability register | Owner approval | EXECUTION-RECORD S3.5 |
| D-5 | Owner approval | **DONE** at `1b987cec` (2026-11-22) | EXECUTION-RECORD N0.1 |
| _Note:_ S0.1 (UI/UX accessibility batch), S0.3 (ADR drafts + document batch), S0.5 (reconcile master with origin/master) are owned by FRONTEND-AND-UX-ROADMAP.md, DECISIONS-AND-DEPENDENCIES.md and V3-RELEASE-ROADMAP.md respectively — not re-listed here to keep exactly one owner per MASTER slice. |
| D-13 | ADR signatures | Owner-gated | DECISIONS-AND-DEPENDENCIES.md D-13; EXECUTION-RECORD S4.3 |


---

## 6a. Residual product slices (verified 2026-11-24, dependency order)

These are the only product-capability items still open in this authority. Rows 4.1, 4.2, 4.6 and the
scenario-detail arc are closed or terminal and are retained in §§2-5 as history, not as execution work.
An owner-gated row is a decision request, not approval to implement.

| Order / ID | Category | Objective and affected components | Prerequisite | Acceptance and verification |
|---|---|---|---|---|
| 1 · D-8 / 4.3 | Owner-gated | Decide whether `team_race` slots get a citable source or a terminal absence. Affects `database/seeders/ScenarioSlotSeeder.php`, `app/Http/Requests/StoreRaceEntryRequest.php`, Unity Cup read-side disclosures. | Written owner ruling. | Seeded `team_race` row plus a round-trip race-entry test, or a terminal `KNOWN-ISSUES.md` row; re-run the same read-only slot census used on 2026-11-24. |
| 2 · D-9 / 4.4 | **DONE 2026-11-24** — persisted roster sub-model authorised and built | Affects the `TrainingRun` read model, `app/Services/Career/TeamPanelBuilder.php` and the team editor. | ADR (ADR-0039, owner delegation 2026-11-18) or terminal ruling. | ADR plus migration and save/reload test — **landed**: `roster_members` (`2026_11_24_120000`), `RosterMember`, `RosterMemberController`, `CockpitRosterTest`/`RosterSeederReRunTest`/`RosterMemberTest`/`RosterDoesNotTouchTeamRankTest`, `RosterMembersForeignKeyRepairTest` green. |
| 3 · D-10 / 4.5 | Owner-gated | Decide whether per-round preseason rows replace the single `team_preseason_wins` count. | ADR or ruling that the count is product truth. | Approved schema with round-trip test, or terminal note; no code before the ruling. |
| 4 · D-13 | Owner-gated | General ADR signatures and index hygiene. | Owner action. | `docs/adr/README.md` and each status line agree; no agent signature. |

## 7. Reconciliation Notes (conflicts resolved)

| Conflict | Resolution | Evidence |
|----------|------------|----------|
| **KI-114 (S2.3)**: `archive/IMPLEMENTATION-ROADMAP-2026-11-18.md` §2.5 marks OPEN with premise "specs drive retired controls" | **REFUTED** — premise invalid. Selectors resolve to live controls (`SupportSlot.vue:221`, `Builder.vue:443/455/494`). Only 3 failures on `career-legacy-deck-steps.spec.ts` Legacy step unclassified. | EXECUTION-RECORD S2.3: "DONE with premise **refuted**"; RESIDUAL-VERIFICATION §Slice 2.5: "premise refuted — selectors resolve to live sites" |
| **Scenario scope**: Multiple roadmaps name overlapping scenario slices (S18, R1-R8, S-A-P01, S-RS-*) | **UNIFIED** under Scenario Surfaces (§1) and Capability Gaps (§2) with canonical IDs retained. Historical slice numbers preserved in Source column. | Cross-checked REMAINING-WORK §1, PROJECT-ROADMAP §2, IMPLEMENTATION-2026-11-17 §S6, IMPLEMENTATION-S18 |
| **v3.0.0 tag blocker**: `v3.0.0-implementation-roadmap-2026-11-16.md` claims "single red spec blocks tag" | **REFUTED** by `v3.0.0-release-plan-2026-11-17.md` — quiet-box gate, full browser suite, owner cuts tag; journey spec green at HEAD `3600741e` | V3-RELEASE-PLAN P1-P6; EXECUTION-RECORD S4.4 (owner-gated, not attempted) |

---

## 8. Source-to-Destination Register

| Source File | Source Section | Destination | Status |
|-------------|----------------|-------------|--------|
| `remaining-work-roadmap-2026-11-17.md` | Phase 4 (4.1-4.6) | §2 Capability Gaps | Consolidated |
| `remaining-work-roadmap-2026-11-17.md` | §1 Completed, §2 Consolidation, §3 Dependency graph | §3, §4, §5 | Consolidated |
| `v3.0.0-implementation-roadmap-2026-11-16.md` | Features A-G, Phases R1-R4 | §1, §3, §5 | Consolidated |
| `project-roadmap-2026-11-16.md` | Phase E (E.1-E.2), §1 Completed | §1, §5 | Consolidated |
| `implementation-roadmap-2026-11-17.md` | S6 (scenario walks), S7 (owner decisions) | §1, §6 | Consolidated |
| `implementation-roadmap-2026-11-17-s18.md` | S18-01..S18-10 | §1, §4 | Consolidated |
| `implementation-roadmap-2026-11-16.md` | P5.1, P5.2, P5.3, P5.5 | §5 | Consolidated |
| `v3.0.0-release-plan-2026-11-17.md` | P1-P6 prioritized slices | §5, §7 | Consolidated |
| `execution-record-2026-11-18.md` | S0-S4 execution record | §5, §6, §7 | Consolidated |
| `residual-verification-2026-11-18.md` | §Slice 3.8 (Trackblazer/URA Finale) | §1 | Consolidated |
| `master-implementation-roadmap-2026-11-18.md` | S3.1-S3.5, S4.1-S4.4 | §3, §6 | Consolidated |

---

## MASTER Slice Ownership Map (Product Capability)

| MASTER Slice | Slice Name | Canonical Owner | Status |
|---|---|---|---|
| S3.1 | Restore Independent Training door | PRODUCT | DONE |
| S3.2 | Correct shipped hint-level copy | PRODUCT | DONE |
| S3.3 | Confirm Japanese search key populated | PRODUCT | DONE |
| S3.4 | Maiden-lock fallback for imported runs | PRODUCT | BLOCKED — owner |
| S3.5 | Owner-gated capability register | PRODUCT | BLOCKED — owner |

*All MASTER phase-3 slices S3.1–S3.5 owned by PRODUCT-AND-CAPABILITY-ROADMAP.md*

---

*Generated: 2026-11-23. All canonical IDs (S2.3, R4.3, KI-114, D-13, etc.) retained from original sources. Historical sources archived in `docs/roadmap/archive/`.*