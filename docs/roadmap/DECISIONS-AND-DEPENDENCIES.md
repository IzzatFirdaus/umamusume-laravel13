# Decisions and Dependencies

**Authoritative roadmap for owner decisions and cross-workstream dependencies.** This file is the single execution authority for all owner-gated decisions: register D-1..D-13, ADR index (ADR-0020/0021/0025/0027/0029), canonical dependency graph, open questions OQ-2/OQ-3/OQ-5/OQ-6, peer-owned items, roadmap laws L1-L8, and MASTER phase-0 slices S0.3/S0.4/S0.6 (D-13 owner-gated), phase-2 slice S2.5 (ADR-0027 accepted 2026-11-18 under owner delegation; correction-writer open), and phase-4 slice S4.3 (ADR hygiene/signatures/index), and phase-5 slices S5.1-S5.6. Canonical IDs retained. Historical sources archived in `docs/roadmap/archive/`. S3.5 (capability register) is owned by PRODUCT-AND-CAPABILITY-ROADMAP.md; V3-RELEASE depends on S4.3 for tag hand-off but does not own it.

---

## 1. Owner Decision Register (D-1 through D-13)

From `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` §14, `IMPLEMENTATION-ROADMAP-2026-11-16.md`, `REMAINING-WORK-ROADMAP-2026-11-17.md` §5.

| ID | Decision | Status | Evidence / Resolution |
|----|----------|--------|----------------------|
| D-1 | MWG file banking (14 files) | **DONE** | Commit `178a9079`; typecheck 0, build 0 |
| D-2 | BO-011 dark risk token `#FF6F3B` + focus ring | **DONE** | Token in `app.css:531`; `DesignTokensTest` 17 passed |
| D-3 | Dependency advisories | **DONE** | Framework 13.42.0; `composer audit` 1 (unreachable `symfony/yaml`) |
| D-4 | Suite-truth fixes (P3.1-P3.7) | **DONE** | Landed by peer commits (`44px`, `f8e428d9`, `veterans-transfer-requests.spec.ts`) |
| D-5 | Batch commits | **DONE** | Committed at `1b987cec` (2026-11-22); owner approval recorded | EXECUTION-RECORD N0.1 |
| D-6 | BO-004 dark `To do` pill | **DONE** | `--color-risk: #FF6F3B`; ratios 4.566/5.187/7.190 ≥ 4.5:1 |
| D-7 | `QWEN.md` truth | **DONE** | Commit `3cbbd8b9`; census 716→691 ≤ 708; `DocCitationParityTest` 2 passed |
| D-8 | Trackblazer/Unity Cup structure | **ACCEPTED — backend implemented 2026-11-25** | Unity Cup scenario-level team-race slots approved per corroborated calendar (Round 1 after Junior Late December, Round 2 Classic Late June, Round 3 Classic Late December, Round 4 after Senior Late June, Unity Cup Finals after Senior Late December). Attributed dataset: `unity-cup-team-races.json` (five seeded team_race scenario slots). Dedicated `team_races` migration and model; `is_fixture` migration belongs to synthetic catalogue exclusion (BO-003) — distinct from D-8. Authoritative frontend payload at `CockpitController::teamSection()`. Focused verification: 5/5 seeded slots green; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Ruling recorded in task crosswalk; distinct from RESEARCH:D03 countdowns. No terminal absence recorded for supported citation.
| D-9 | Roster sub-model | **ACCEPTED (architectural)** | ADR-0039 (owner delegation 2026-11-18) authorises `roster_members`; migration `2026_11_24_120000`, `RosterMember`, `RosterMemberController`, `CockpitRosterTest` green; per-skill table (A-0.14/KI-126) stays open — architectural acceptance recorded, implementation completeness separate | `docs/adr/0039-unity-cup-roster-recorded-sub-model.md`; PRODUCT §4.4 |
| D-10 | Preseason rounds table | **ACCEPTED — backend implemented 2026-11-25** | Individually recorded round outcomes authorized as authoritative values rather than aggregate count as sole value. Dedicated `preseason_rounds` table permitted if necessary but not mandated. Existing recorded data preserved; unknown historical outcomes preserved. Design delegated to Backend with Fullstack integration acceptance. Ruling recorded in task crosswalk; `is_fixture` migration belongs to synthetic catalogue exclusion (BO-003) unless a specific additional dependency is established. Authoritative frontend payload at `TeamRaceController::index()`. Focused verification: individual round outcomes preserved in `preseason_rounds` table; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Ruling recorded in task crosswalk; distinct from BO-003 fixture exclusion.
| D-11 | Per-ancestor guest flag + identity input | **DONE** | `StoreLegacySelectionRequest.php:113,131-132` validates `is_guest` and `ancestors_labels`; `tests/Feature/AncestorLabelTest.php` and `LegacyLabPageTest.php` prove the round trip; PRD §6.3 permits recording | REMAINING-WORK §4.6, PRODUCT 4.6 |
| D-12 | ADR-0027 Turn/Decision Logging | **ACCEPTED (owner delegation 2026-11-18) / OPEN correction-writer item** | ADR-0027 status is **accepted** — `docs/adr/0027-turn-and-decision-logging.md` contract line and status line, and `docs/adr/README.md:102`, all record `accepted (owner delegation 2026-11-18: "proceed with every owner gated tasks")`; committed at `dba86362` (2026-11-19). The signature gate is therefore **obsolete** — see §8 dated reconciliation. What remains open is a *separate* undecided question the ADR itself records under `open:`: the four correction columns (`choice`, `decision_snapshot`, `corrected_from`, `superseded_by`) have no writer — the shipped path `runs.turns.update` updates the turn row in place, so `corrected_from`/`superseded_by` stay null and the Timeline's corrected marker reads `updated_at`. Who writes a correction row, and from which screen, is undecided (2026-10-22 framework check). | ADR-0027 contract + `docs/adr/README.md:102`; commit `dba86362` |
| D-13 | ADR signatures (general) | **OWNER-GATED** | Multiple ADRs need owner signatures; not attempted | MASTER §14, EXECUTION-RECORD S4.3 |

**Additional decisions from REMAINING-WORK §5**:
- D-8 (Trackblazer/Unity Cup): from `S18-08` adjacent decision
- D-9 (Roster): from `S18-09` adjacent decision
- U-01/U-04/U-06 → 4.4/4.5/4.6 from Rice Shower Round 8 reconciliation

**Dated reconciliation, 2026-11-24 (numbering divergence).** Two registers carry `D-n` IDs and they do not line up. This register (from `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` §14) numbers D-8 = Trackblazer/Unity Cup team-race slots, D-9 = Roster sub-model, D-10 = Preseason rounds, D-12 = ADR-0027. The archived `remaining-work-roadmap-2026-11-17.md` §5 numbers D-8 = `match_candidates` 304 race rows, D-9 = `data_sources` provenance, D-10 = fan-ladder tiers, D-12 = roster+preseason. Two downstream claims cite the *archived* numbering and must not be read as closing this register's D-8/D-9:
- ADR-0037's "D-8 measurement" (`source_key` discriminates 304/304 rows `gametora-races`, no schema change) resolves **archived D-8** (match_candidates entity discriminator), **not** this register's D-8 (team-race slots). This register's D-8 is **ACCEPTED** — owner acceptance recorded 2026-11-25, five team_race slots seeded via `unity-cup-team-races.json`, `ScenarioSlotSeeder::seedUnityCup()` reconstructed; executable verification blocked by missing Composer dependencies; integration not yet reflected at localhost:8000 per owner confirmation. Distinct from archived D-8.
- Commit `dba86362` ("closes D-9 by measurement, every seeded row carries inline `source_url`") resolves **archived D-9** (data_sources provenance backfill), **not** this register's D-9 (roster sub-model). This register's D-9 is now **ACCEPTED (architectural)** by ADR-0039 (2026-11-24, owner delegation 2026-11-18), which authorises `roster_members`; per-skill table (A-0.14/KI-126) stays open — architectural acceptance recorded, implementation completeness separate; the archived D-9 remains the data_sources provenance question.
No D ID is renumbered; this note only records which register each downstream claim belongs to.

---

## 2. ADR Index (Architecture Decision Records)

| ADR | Title | Status | Related Decision |
|-----|-------|--------|------------------|
| ADR-0020 | Sourced Character Artwork | **ACCEPTED** | D-1 (MWG) |
| ADR-0021 | Sourced Character Artwork (mirror) | **ACCEPTED** | D-1 |
| ADR-0025 | Build Target Payload (two-key split, caps as run columns) | **ACCEPTED** | R4 (KI-132) |
| ADR-0027 | Turn and Decision Logging | **ACCEPTED** (owner delegation 2026-11-18) | D-12 (accepted; separate open correction-writer item) |
| ADR-0029 | Per-Run Measurement Columns | **ACCEPTED** | KI-116, KI-119 |
| ADR-0038 | AI Advisor as an optional read-only layer | **ACCEPTED — owner acceptance recorded 2026-11-25** | `enabled => false` preserved; deterministic engine remains the decision-safety floor; no external provider activation; Cockpit contract preserved; regression suites verified with Http::fake; 104 passing tests and 466 assertions; 83 passing tests and 482 assertions (post-formatting); PHPStan and targeted Pint passing |
| ADR-0039 | Unity Cup team roster as recorded sub-model | **ACCEPTED — owner delegation 2026-11-18** | Architectural acceptance recorded; `roster_members` migration and model built; `CockpitController::rosterSection()` reads recorded roster; per-skill table (A-0.14/KI-126) stays open — acceptance is architectural, implementation completeness separate |

**ADR-0027 Detail**: Zero hits for `decision_log`, `turn_log`, `turn_and_decision` in `app`, `config`, `database/migrations` (measured 2026-11-18; the name search finds nothing because the four columns live on `turn_entries`, not on a log table). ADR is **ACCEPTED** (owner delegation 2026-11-18 — `docs/adr/README.md:102`, the ADR contract/status lines, commit `dba86362`). Code half (KI-104 nested disclosure) **DONE** in `UnityCupEditor.vue`. What is *not* built is the correction writer: see D-12's open item above.

---

## 3. Dependency Graph (Canonical)

From `PROJECT-ROADMAP-2026-11-16.md` §3, `REMAINING-WORK-2026-11-17.md` §3.

```
┌─────────────────────────────────────────────────────────────────┐
│ 0.1 Land worktree product content (BLOCKING)                   │
│    ├─ 0.2 Lockfile: npm ci --dry-run exits 0          [DONE]  │
│    ├─ 0.3 Untracked build inputs: all 4 tracked        [DONE]  │
│    ├─ 0.4 Composer script: pre-native-build restored   [DONE]  │
│    └─ 0.5/0.6 Tree hygiene: win-unpacked ignored       [DONE]  │
└──────────────────────┬──────────────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        ▼              ▼              ▼
┌───────────────┐ ┌────────────┐ ┌───────────────┐
│ Phase 1:      │ │ Phase 2:   │ │ Phase 3:      │
│ Register      │ │ Browser    │ │ Defects       │
│ Truth         │ │ Truth      │ │ (code site)   │
└───────┬───────┘ └─────┬──────┘ └───────┬───────┘
        │               │                │
        └───────────────┼────────────────┘
                        ▼
               ┌──────────────────┐
               │ Phase 5: Release │
               │ Bar (7 cond.)    │
               └────────┬─────────┘
                        │
                        ▼
               ┌──────────────────┐
               │ Phase D: Version │
               │ Truth + Tag      │
               └────────┬─────────┘
                        │
                        ▼
               ┌──────────────────┐
               │ Phase E: Scenario│
               │ Walks (indep.)   │
               └──────────────────┘
```

**Ordering constraints**:
- Nothing in Phase 3/4 may start before **0.1** (worktree is only place S18/peer fixes exist)
- Phase 1.1 → 1.2 → 1.3 → Condition 7
- Phase 2.1 → 2.2 → 2.3 → 2.6 → Condition 2
- Phase 2.4 → 2.5 (independent; makes every later gate one command)
- Phase 5.1 → 5.2 → 5.3 → 5.4 (owner cuts tag)

---

## 4. Open Questions (from PRD §7, V3-R3)

| ID | Question | Resolution | Evidence |
|----|----------|------------|----------|
| OQ-3 | Fetch scheduler default | **RESOLVED: manual-only** | `bootstrap/app.php:50-53` registers only `independent-training:complete` everyMinute; `uma:fetch` appears in no schedule registration | V3-R3.1 |
| OQ-5 | Skill style eligibility | **DEFERRED** | Do not ship | PRD §7 |
| OQ-6 | Artwork remainder | **RESOLVED** | (a) Pre-run pick screen uses native `<select>` — confirmed; (b) `skills.iconid` migration — **out of scope** | V3-R3.2 |

---

## 5. Peer-Owned Items (Hands-Off, Logged Only)

| Item | Owner | Status | Notes |
|------|-------|--------|-------|
| `Dashboard.vue:563` BO-008 hunk | Peer | Uncommitted | Skip per law L4; log only |
| `full-career-journey.spec.ts` tag blocker | Peer | **PREMISE REFUTED** | No `details[data-region="team-state"]` locator and no `/settings` nav in the file; `FRONTEND-AND-UX-ROADMAP.md` R5 records it green at `3600741e`. Not a tag blocker; do not touch |
| `AdvisorAnalysisPanel.vue` disclosure (279×20 → 279×44) | Peer | **RESOLVED 2026-11-24** | Corrected to 44px target; `BENO-COCKPIT-001` closed; `career-cockpit-advisor-ai.spec.ts` green across 1280/768/390/320px, three themes |
| Working-tree files with mtime < 5 min of peer session | Peer | Various | Skip, log; do not claim |

**Law L4 (AGENTS.md §9)**: Peer files staged intermittently; `git add -A` is commit-sweep hazard. Private index for every commit.

---

## 6. Roadmap Laws (from PROJECT-ROADMAP §3, REMAINING-WORK §5.1)

1. **No slice closed on assertion** — closes on command output quoted in hand-off
2. **Recorded owner ruling is terminal** — reversing requires new written ruling; later contradiction does not silently supersede
3. **Baseline never raised to pass** — `DocCitationParityTest.php:26` forbids; fix is making docs true
4. **`career-preflight.spec.ts` is record of Phase E2 cut** — not stale; contradicting specs are stale
5. **Never `pint --dirty`** — dirty set not your slice on shared worktree
6. **One Pest process at a time** — overlapping runs hang rather than error
7. **Private index for every commit** — peer files staged intermittently
8. **Stale evidence is not evidence** — `HEAD` moves mid-session; re-derive SHA before attributing counts

---

## 7. Source-to-Destination Register

| Source File | Source Section | Destination | Status |
|-------------|----------------|-------------|--------|
| `master-implementation-roadmap-2026-11-18.md` | §14 (D-1..D-13), §12, §16 | §1, §3, §6 | Consolidated |
| `implementation-roadmap-2026-11-16.md` | Owner register D-1..D-13, OQ-2/3/5/6, R1-R8 | §1, §4 | Consolidated |
| `project-roadmap-2026-11-16.md` | §3 Dependency graph, §3 Roadmap laws | §3, §6 | Consolidated |
| `remaining-work-roadmap-2026-11-17.md` | §5 Owner decisions, §3 Dependency graph | §1, §3, §4 | Consolidated |
| `v3.0.0-implementation-roadmap-2026-11-16.md` | Open questions OQ-3/OQ-6, Definition of Done | §4, §6 | Consolidated |
| `execution-record-2026-11-18.md` | S3.5, S4.3 (D-13), N0.1 (D-5) | §1 | Consolidated |
| `v3.0.0-release-plan-2026-11-17.md` | Decisions D1-D6, prioritized slices | §1, §3 | Consolidated |

---

## 8. Reconciliation Notes

| Conflict | Resolution | Evidence |
|----------|------------|----------|
| **D-5 (batch commits)**: Multiple roadmaps reference | **UNIFIED** — committed at `1b987cec` (2026-11-22); owner approval recorded | EXECUTION-RECORD N0.1 |
| **ADR-0027**: Multiple sources (KI-104, D-12, D-13) | **UNIFIED** — single entry: ADR ACCEPTED (owner delegation 2026-11-18, commit `dba86362`); code half DONE; the correction-writer question is a separate open item, not a signature gate | ADR-0027 contract/status, `docs/adr/README.md:102`, commit `dba86362` |
| **OQ-3/OQ-6**: V3 roadmap claims open, REMAINING-WORK claims resolved | **RESOLVED** — V3-R3 evidence shows resolved in code | V3-R3.1, V3-R3.2 |
| **Peer-owned dirty tree**: Multiple roadmaps reference | **LOGGED ONLY** — hands-off per Law L4 | AGENTS.md §9, PROJECT-ROADMAP §0 hazard |

---

## MASTER Slice Ownership Map (Decisions/Dependencies)

| MASTER Slice | Slice Name | Canonical Owner | Status |
|---|---|---|---|
| S0.3 | Commit ADR drafts + document batch | DECISIONS | OPEN — owner-gated |
| S0.4 | Clear empty-diff status noise | DECISIONS | DONE |
| S0.6 | Light/dark re-tune (D-13 owner-gated) | DECISIONS | BLOCKED — owner |
| S2.5 | KI-104 browser half + ADR-0027 | DECISIONS | ADR-0027 accepted (owner delegation 2026-11-18); correction-writer open — not signature-gated |
| S5.1 | Provenance backfill | DECISIONS | Archived D-9 half resolved by measurement 2026-11-18; canonical D-9 (roster sub-model) now **ACCEPTED (architectural)** by ADR-0039 (2026-11-24); per-skill table (A-0.14/KI-126) stays open |
| S5.2 | match_candidates entity discriminator | DECISIONS | Archived D-8 half resolved by ADR-0037 (accepted); canonical D-8 (team-race slots) now ACCEPTED — backend implemented 2026-11-25, 5 seeded slots |
| S5.3 | Close OQ-2 and OQ-3 | DECISIONS | OPEN |
| S5.4 | ADR-0028 primitive layer | DECISIONS | OPEN |
| S5.5 | ADR code gaps | DECISIONS | OPEN |
| S5.6 | Artwork coverage (OQ-6) | DECISIONS | RESOLVED |

*All MASTER phase-0 S0.3/S0.4/S0.6 (D-13), phase-2 S2.5, phase-4 S4.3, phase-5 S5.1–S5.6 owned by DECISIONS-AND-DEPENDENCIES.md*


---

## 9. Residual owner decisions (verified 2026-11-24, no implementation before ruling)

These are decision requests, not approved implementation. Each row belongs to this authority; the
owning roadmap's dependency order waits on it.

| Order / ID | Category | Objective and affected components | Prerequisite | Acceptance and verification |
|---|---|---|---|---|
| 1 · D-8 | ACCEPTED — backend implemented 2026-11-25 | Unity Cup scenario-level team-race slots approved per corroborated calendar (Round 1 after Junior Late December, Round 2 Classic Late June, Round 3 Classic Late December, Round 4 after Senior Late June, Unity Cup Finals after Senior Late December). Five seeded team_race scenario slots via `unity-cup-team-races.json`; `ScenarioSlotSeeder::seedUnityCup()` reconstructed but functional verification blocked by missing Composer dependencies. | Written owner ruling recorded 2026-11-25. | 5/5 seeded slots green; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Distinct from archived D-8 (match_candidates). |
| 2 · D-9 | ACCEPTED (architectural, via ADR-0039) | Persisted roster sub-model authorised by ADR-0039 (owner delegation 2026-11-18); affects `TrainingRun`, `TeamPanelBuilder` and team editor. Migration `2026_11_24_120000`, `RosterMember`, `RosterMemberController`, `CockpitRosterTest`/`RosterSeederReRunTest`/`RosterMemberTest`/`RosterDoesNotTouchTeamRankTest`, `RosterMembersForeignKeyRepairTest` green. Architectural acceptance recorded; per-skill table (A-0.14/KI-126) stays open — implementation completeness is a separate question from the ADR's acceptance. | ADR-0039 accepted; core roster landed. | ADR plus migration and save/reload test landed; per-skill table (A-0.14/KI-126) remains open. |
| 3 · D-10 | Owner-gated | Per-round preseason rows or single-count product truth. | ADR or ruling. | Accepted ADR/schema test or terminal note. |
| 4 · ADR-0038 | Accepted — owner acceptance recorded 2026-11-25 | Accept or decline the optional AI advisor layer. | `enabled => false` preserved; deterministic engine remains the decision-safety floor; no external provider activation; Cockpit contract preserved; regression suites verified with Http::fake; 104 passing tests and 466 assertions; 83 passing tests and 482 assertions (post-formatting); PHPStan and targeted Pint passing. | Status line changed by owner; `config/advisor_ai.php` switch remains the enforcement point. |
| 5 · Visual target | Accepted — owner ruling 2026-11-25 | Keep or revise the EVA-01 green coverage target recorded in `DESIGN.md:577-583`. | Outcome-based visual policy replaces the rigid 5–7% green pixel-coverage target; do not adopt the reported 0.41% or 0.46% measurements as the new target. Evaluate visual balance, semantic clarity, theme consistency, readability and accessibility. Purple remains the dominant EVA-01 theme identity; green, orange and other approved supporting colours should be meaningfully visible throughout appropriate interface elements. The theme must not be reduced to purple surfaces with a single green line. Delegate ordinary colour distribution, surface treatment and component-level design decisions to the Frontend Agent. This ruling does not authorize weakening accessibility requirements. |
| 6 · Backdrop scope | Accepted — owner ruling 2026-11-25 | Decide whether error shells inherit `app-backdrop` and whether print/forced-colors require explicit opt-out. | Normal application pages may use the established theme-aware backdrop. Career workspaces, catalogues and Database pages may retain appropriate themed backgrounds. Error shells may use restrained theme-aware backgrounds, provided error information and recovery actions remain clearly readable. Print output must explicitly suppress unnecessary decorative backgrounds. Forced-colours mode must explicitly preserve system colour behaviour and suppress incompatible decorative backgrounds. Reduced-motion preferences must be respected. Backdrops must not obscure text, controls, focus indicators or meaningful content. Delegate implementation details to Frontend. Do not require further owner approval for ordinary implementation within this policy. |

---

*Generated: 2026-11-23. All canonical IDs (D-1..D-13, KI-114, ADR-0027, OQ-3, etc.) retained from original sources. Historical sources in `docs/roadmap/archive/`.*