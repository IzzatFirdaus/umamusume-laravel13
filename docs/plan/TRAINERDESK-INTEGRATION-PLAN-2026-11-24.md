# TrainerDesk — Documentation Integrity and Release-Candidate Integration Plan

**Repository:** `D:\Projects\umamusume-laravel13`
**HEAD:** `3600741e3a67f06e542337de4ad7a517a7ffa188` (master, `0 3` ahead of `origin/master`)
**Tags:** `0.1.0 2.0.1 2.0.2 2.1.0 2.1.1` (no `3.0.*`)
**Stashes:** 6 (peer/foreign — not this session's to commit)
**Worktrees:** master + detached `td-head-check`
**Porcelain:** 140 entries = 3 Modified + 137 Untracked
**Rule:** No staging, no committing. Owner review required before any integration.

---

## 1. Release-blocking governance status (verified against DECISIONS-AND-DEPENDENCIES.md + ADR set)

| ID | Decision | Status | Bearing on this batch |
|---|---|---|---|
| D-8 | Trackblazer/Unity Cup team-race slots | **ACCEPTED — backend implemented 2026-11-25**: citable source for team-race slots confirmed per `unity-cup-team-races.json` (five seeded team_race scenario slots). Dedicated `team_races` migration and model; `is_fixture` migration belongs to synthetic catalogue exclusion (BO-003) — distinct from D-8. Authoritative frontend payload at `CockpitController::teamSection()`. Focused verification: 5/5 seeded slots green; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Ruling accepted — distinct from RESEARCH:D03 countdowns.
| D-9 | Roster sub-model | **DONE** — ADR-0039 accepted (owner delegation 2026-11-18); `roster_members` migration + model + controller landed | Roster workstream is INCLUDE |
| D-10 | Preseason rounds table | **ACCEPTED — backend implemented 2026-11-25**: individually recorded round outcomes authorized as authoritative values rather than aggregate count as sole value. Dedicated `preseason_rounds` table permitted if necessary but not mandated. Existing recorded data preserved; unknown historical outcomes preserved. Design delegated to Backend with Fullstack integration acceptance. `is_fixture` migration belongs to synthetic catalogue exclusion (BO-003) unless a specific additional dependency is established. Authoritative frontend payload at `TeamRaceController::index()`. Focused verification: individual round outcomes preserved in `preseason_rounds` table; PHPStan partial pass; persistent-database migration not executed (seeded via fixture process). Ruling accepted — distinct from BO-003 fixture exclusion.'
| D-11 | Per-ancestor guest flag + identity input | **DONE** | No new items needed |
| D-12 | ADR-0027 Turn/Decision Logging | **ACCEPTED** (owner delegation); open correction-writer item (no writer for `corrected_from`/`superseded_by`) | No blocking item |
| D-13 | ADR signatures (general) | **OWNER-GATED** — ADR-0038/0039 on disk but missing index rows in `docs/ADR/README.md` | Index rows must be added before ADR-0039 integration considered complete |
| ADR-0038 | AI Advisor optional read-only layer | **PROPOSED** — owner acceptance pending; `config/advisor_ai.php` defaults off | Advisor workstream = WAIT_FOR_PEER_COMPLETION / OWNER_DECISION_REQUIRED |
| ADR-0039 | Unity Cup roster recorded sub-model | **ACCEPTED** | Roster workstream = INCLUDE |

**ND-1..ND-22** (design/norm decisions from audits): owner-gated items ND-1, ND-4, ND-6, ND-8, ND-9, ND-10, ND-11, ND-12, ND-13, ND-14, ND-15, ND-16 remain environment/owner items, not PHP domain defects. N6.5 tier filter (ND-14(b) owner-approved 2026-11-22) is INCLUDE.

---

## 2. Disposition legend

- **INCLUDE_AFTER_OWNER_REVIEW** — verified, tested, tied to accepted ADR or clear contract; ready for owner sign-off then integration.
- **WAIT_FOR_PEER_COMPLETION** — depends on peer-owned contract or live-provider verification (ADR-0038 live-provider pass is a separate session).
- **REQUIRES_ADDITIONAL_VERIFICATION** — needs fresh `migrate:fresh --seed`, Pest/PHPStan/Pint/tsc gate run before integration.
- **OWNER_DECISION_REQUIRED** — blocked on D-13 or ADR-0038 acceptance (D-8 and D-10 already accepted 2026-11-25); owner must rule first.
- **EXCLUDE_LOCAL_ONLY** — scratch, logs, local tooling config, image assets; never integrate.
- **SUPERSEDED** — sitrep/archive records superseded by canonical docs/roadmap.
- **INSUFFICIENT_EVIDENCE** — cannot classify without owner input.

---

## 3. Full classification — all 140 entries (repository-relative paths)

### 3.1 Modified (3) — working-tree changes vs HEAD

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| M1 | `app/Models/TrainingRun.php` | backend (roster, ADR-0039) | INCLUDE_AFTER_OWNER_REVIEW | Adds `rosterMembers()` HasMany (D01). Companion to ADR-0039 accepted. Requires C-5 fresh migrate + seed. |
| M2 | `app/Providers/AppServiceProvider.php` | telemetry/backend | INCLUDE_AFTER_OWNER_REVIEW | Registers `LangfuseTraceService` singleton from `langfuse` config. Telemetry diagnostic surface. |
| M3 | `resources/js/pages/Preferences/Edit.vue` | frontend/telemetry | INCLUDE_AFTER_OWNER_REVIEW | Adds telemetry status polling (`/api/v1/telemetry/status`), heartbeat freshness, integration status. C-9 `tsc --noEmit` required. |

### 3.2 Untracked — backend (ADR-0039 roster, D-9 DONE)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U1 | `app/Enums/RosterDistance.php` | backend/roster | INCLUDE_AFTER_OWNER_REVIEW | ADR-0039 enum. MAX_MEMBERS=20, ACES_PER_DISTANCE=1. |
| U2 | `app/Http/Controllers/Career/RosterMemberController.php` | backend/roster | INCLUDE_AFTER_OWNER_REVIEW | Writes roster_members rows. Cockpit `rosterSection()` reads recorded roster. |
| U3 | `app/Http/Requests/Career/StoreRosterMemberRequest.php` | backend/roster | INCLUDE_AFTER_OWNER_REVIEW | Validation for roster member store. |
| U4 | `app/Models/RosterMember.php` | backend/roster | INCLUDE_AFTER_OWNER_REVIEW | booted() saving guard. Companion to TrainingRun::rosterMembers(). |
| U5 | `app/Services/Career/CareerReference.php` | backend/career | INCLUDE_AFTER_OWNER_REVIEW | Career reference enrichment service. |
| U6 | `app/Services/Career/VeteranPortfolio.php` | backend/veterans | INCLUDE_AFTER_OWNER_REVIEW | Veteran portfolio / lineage service. |
| U7 | `database/factories/RosterMemberFactory.php` | database/roster | INCLUDE_AFTER_OWNER_REVIEW | Factory for roster members seed. |
| U8 | `database/migrations/2026_11_24_120000_create_roster_members_table.php` | database/roster | INCLUDE_AFTER_OWNER_REVIEW | ADR-0039 migration with down(). Batch-authority: ran on dev DB under owner item-2 authorization. |
| U9 | `database/migrations/2026_11_24_140000_repair_roster_members_foreign_key.php` | database/roster | INCLUDE_AFTER_OWNER_REVIEW | FK repair migration for roster_members. |
| U10 | `docs/adr/0039-unity-cup-roster-recorded-sub-model.md` | docs/adr | INCLUDE_AFTER_OWNER_REVIEW | ACCEPTED ADR record. Needs index row in README (D-13). |

### 3.3 Untracked — backend (ADR-0038 advisor, PROPOSED — not accepted)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U11 | `app/Console/Commands/UmaAdvisorIndex.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 not accepted; advisor index command. Live-provider verification is a separate session pass. |
| U12 | `app/Services/Advisor/AdvisorAnalysis.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. No paid inference in regression suites by design. |
| U13 | `app/Services/Advisor/AdvisorExplainer.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U14 | `app/Services/Advisor/AdvisorIndex.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U15 | `app/Services/Advisor/AdvisorRetriever.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U16 | `app/Services/Advisor/CandidateActions.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U17 | `app/Services/Advisor/ClefDecision.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U18 | `app/Services/Advisor/ClefDecisionProvider.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U19 | `app/Services/Advisor/EmbeddingProvider.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U20 | `app/Services/Advisor/Evidence.php` | backend/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U21 | `config/advisor_ai.php` | config/advisor | OWNER_DECISION_REQUIRED | ADR-0038 proposed; defaults off. Owner acceptance needed before enabling. |
| U22 | `docs/adr/0038-ai-advisor-optional-read-only-layer.md` | docs/adr | OWNER_DECISION_REQUIRED | PROPOSED ADR. Not Accepted; binds nothing until owner accepts. Needs index row (D-13). |

### 3.4 Untracked — telemetry (Langfuse)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U23 | `app/Console/Commands/TelemetryDiagnose.php` | telemetry/backend | INCLUDE_AFTER_OWNER_REVIEW | Telemetry diagnose command. References `TelemetryConfigTest`. |
| U24 | `app/Http/Middleware/LangfuseTraceMiddleware.php` | telemetry/backend | INCLUDE_AFTER_OWNER_REVIEW | Langfuse trace middleware. |
| U25 | `observability-integration/` | telemetry/scratch | REQUIRES_ADDITIONAL_VERIFICATION | Directory of observability integration material; contents unverified against gates. |

### 3.5 Untracked — database (D-8/D-10 accepted; D-13/ADR-0038 gated)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U26 | `database/_scan_scratch.php` | database/scratch | EXCLUDE_LOCAL_ONLY | Local scan scratch; never integrate. |
| U27 | `database/migrations/2026_11_24_130000_add_is_fixture_to_umamusume_table.php` | database | OWNER_DECISION_REQUIRED — D-10 accepted 2026-11-25; Fixture flag on umamusume; tied to D-10 preseason ruling (accepted) |
| U28 | `database/migrations/2026_11_24_130100_add_team_race_countdown.php` | database | OWNER_DECISION_REQUIRED — D-8 accepted 2026-11-25; Team race countdown; tied to D-8 Trackblazer/Unity Cup team-race slots (accepted, citable source confirmed) |

### 3.6 Untracked — docs: ADR

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U29 | `docs/adr/0038-ai-advisor-optional-read-only-layer.md` | docs/adr | OWNER_DECISION_REQUIRED | (See U22.) PROPOSED, not Accepted. |
| U30 | `docs/adr/0039-unity-cup-roster-recorded-sub-model.md` | docs/adr | INCLUDE_AFTER_OWNER_REVIEW | (See U10.) ACCEPTED. Add index row (D-13). |

### 3.7 Untracked — docs: audits

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U31 | `docs/audits/2026-11-20-ui-ux-application-assessment.md` | docs/audit | INCLUDE_AFTER_OWNER_REVIEW | Current UI/UX assessment record. |
| U32 | `docs/audits/2026-11-21-frontend-browser-review/` | docs/audit | INCLUDE_AFTER_OWNER_REVIEW | Frontend browser review (ND rulings source). |
| U33 | `docs/audits/2026-11-22-advisor-ai-audit/` | docs/audit | WAIT_FOR_PEER_COMPLETION | Advisor AI audit; ties to ADR-0038 proposed. |
| U34 | `docs/audits/2026-11-22-bento-design-language-review/` | docs/audit | INCLUDE_AFTER_OWNER_REVIEW | Bento design language review (ND-13 owner-gated). |
| U35 | `docs/audits/2026-11-22-frontend-browser-review/` | docs/audit | INCLUDE_AFTER_OWNER_REVIEW | Frontend browser review. |
| U36 | `docs/audits/research-audit-final-report.md` | docs/audit | INCLUDE_AFTER_OWNER_REVIEW | Research audit final report. |

### 3.8 Untracked — docs: design-research (image scratch)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U37-U42 | `docs/design-research/bg-*.jfif` (6 files) | design/scratch | EXCLUDE_LOCAL_ONLY | Background image scratch assets; not canonical design tokens. |

### 3.9 Untracked — docs: plan (implementation plans)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U43 | `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | Consolidation inventory record. |
| U44 | `docs/plan/CONSOLIDATION-REPORT-2026-11-23.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | Consolidation report record. |
| U45 | `docs/plan/D01-frontend-handoff.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | D01 frontend handoff (roster D01 referenced in TrainingRun comment). |
| U46 | `docs/plan/PLAN-REGISTRY-2026-11-23.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | Plan registry. |
| U47 | `docs/plan/TASK-OWNERSHIP-CROSSWALK-2026-11-23.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | Task ownership crosswalk. |
| U48 | `docs/plan/frontend-completion-report-2026-11-24.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | Frontend completion report. |
| U49 | `docs/plan/nativephp-v3.0.0-implementation-plan-2026-11-20.md` | docs/plan | INSUFFICIENT_EVIDENCE | NativePHP v3 plan; verify relevance before integrate. |
| U50 | `docs/plan/nativephp-windows-ci-repair-2026-11-21.md` | docs/plan | INSUFFICIENT_EVIDENCE | Windows CI repair plan; verify relevance. |
| U51 | `docs/plan/trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | docs/plan | INCLUDE_AFTER_OWNER_REVIEW | UI/UX implementation plan. |

### 3.10 Untracked — docs: proposals

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U52 | `docs/proposals/CONVENTIONS.md` | docs/proposals | INCLUDE_AFTER_OWNER_REVIEW | Proposed conventions contract. |
| U53 | `docs/proposals/README.md` | docs/proposals | INCLUDE_AFTER_OWNER_REVIEW | Proposals index. |

### 3.11 Untracked — docs: research

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U54 | `docs/research/README.md` | docs/research | INCLUDE_AFTER_OWNER_REVIEW | Research index. |
| U55 | `docs/research/research-refresh-final-report.md` | docs/research | INCLUDE_AFTER_OWNER_REVIEW | Research refresh report. |

### 3.12 Untracked — docs: roadmap (canonical)

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U56 | `docs/roadmap/CROSSWALK-SOURCE-TO-CANONICAL.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | Canonical crosswalk. |
| U57 | `docs/roadmap/DECISIONS-AND-DEPENDENCIES.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | **D-register authority** (D-1..D-13). Required reading for integration. |
| U58 | `docs/roadmap/FRONTEND-AND-UX-ROADMAP.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | Canonical frontend/UX roadmap. |
| U59 | `docs/roadmap/MOVED-FILE-REFERENCE-AUDIT.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | Moved-file reference audit (27 deletions verified intentional archive relocations). |
| U60 | `docs/roadmap/PRODUCT-AND-CAPABILITY-ROADMAP.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | Canonical product/capability roadmap (§4.4 roster). |
| U61 | `docs/roadmap/QUALITY-AND-VERIFICATION-ROADMAP.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | Quality/verification roadmap (C-1..C-9 gates). |
| U62 | `docs/roadmap/RECONCILIATION-AND-ACTIONABILITY.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | Reconciliation record. |
| U63 | `docs/roadmap/V3-RELEASE-ROADMAP.md` | docs/roadmap | INCLUDE_AFTER_OWNER_REVIEW | V3 release roadmap (depends on S4.3 for tag hand-off). |
| U64 | `docs/roadmap/archive/` | docs/roadmap/archive | SUPERSEDED | Archived historical roadmaps; do not integrate as current. |

### 3.13 Untracked — docs: sitrep

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U65-U68 | `docs/sitrep/CONSOLIDATION-MANIFEST.md`, `FINAL-CONSOLIDATION-REPORT.md`, `INDEX.md`, `SITREP-VERIFICATION-REPORT.md` | docs/sitrep | SUPERSEDED | Session records superseded by canonical docs/roadmap + docs/plan. |
| U69-U71 | `docs/sitrep/archive/`, `docs/sitrep/consolidated/` | docs/sitrep/archive | SUPERSEDED | Archived sitrep; do not integrate. |

### 3.14 Untracked — root config / scratch / assets

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U72 | `.actionlint.yml` | tooling/local | EXCLUDE_LOCAL_ONLY | Local actionlint config; not repo canonical. |
| U73 | `.headroom/` | tooling/local | EXCLUDE_LOCAL_ONLY | Local headroom tooling. |
| U74 | `2026-11-24.md` | scratch/daily | EXCLUDE_LOCAL_ONLY | Daily scratch note. |
| U75 | `SESSION_HANDOFF.md` | scratch | EXCLUDE_LOCAL_ONLY | Langfuse/Latitude session record; scratch. |
| U76 | `TELEMETRY_IMPLEMENTATION_REPORT.md` | scratch | EXCLUDE_LOCAL_ONLY | Telemetry report scratch. |
| U77 | `advisor-api-contract.json` | contract | REQUIRES_ADDITIONAL_VERIFICATION | Advisor API contract; verify against advisor services before integrate. |
| U78 | `agent.json` | scratch/config | EXCLUDE_LOCAL_ONLY | Agent config scratch. |
| U79 | `ki_entries.json` | scratch | EXCLUDE_LOCAL_ONLY | KI entries scratch. |
| U80 | `observability-integration/` | telemetry/scratch | REQUIRES_ADDITIONAL_VERIFICATION | (See U25.) |
| U81 | `public/favicon.png` | asset | EXCLUDE_LOCAL_ONLY | Asset; verify ownership before integrate. |
| U82 | `resources/img/app-logo.png` | asset | EXCLUDE_LOCAL_ONLY | Asset; verify ownership before integrate. |

### 3.15 Untracked — frontend components

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U83 | `resources/js/components/career/AdvisorAnalysisPanel.vue` | frontend/advisor | WAIT_FOR_PEER_COMPLETION | Ties to ADR-0038 proposed advisor layer. |
| U84 | `resources/js/components/career/CareerReferencePanel.vue` | frontend/career | INCLUDE_AFTER_OWNER_REVIEW | Career reference panel (CareerReference service). |
| U85 | `resources/js/components/career/TeamOverview.vue` | frontend/career | INCLUDE_AFTER_OWNER_REVIEW | Team overview component. |
| U86 | `resources/js/components/career/UnityCupRoster.vue` | frontend/roster | INCLUDE_AFTER_OWNER_REVIEW | Unity Cup roster UI (ADR-0039 recorded roster). |
| U87 | `resources/js/components/career/VeteranPortfolio.vue` | frontend/veterans | INCLUDE_AFTER_OWNER_REVIEW | Veteran portfolio UI. |
| U88 | `resources/js/components/ui/ErrorSummary.vue` | frontend/ui | INCLUDE_AFTER_OWNER_REVIEW | N3.35 error summary + focusField. |
| U89 | `resources/js/composables/useAdvisorAnalysis.ts` | frontend/advisor | WAIT_FOR_PEER_COMPLETION | Ties to ADR-0038 proposed. |
| U90 | `resources/js/composables/useDialogLightDismiss.ts` | frontend/ui | INCLUDE_AFTER_OWNER_REVIEW | Dialog light-dismiss primitive. |
| U91 | `resources/js/services/advisor/` | frontend/advisor/services | WAIT_FOR_PEER_COMPLETION | Advisor services; ADR-0038 proposed. |

### 3.16 Untracked — storage logs

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U92-U94 | `storage/debug_out.txt`, `storage/full_bg_err.txt`, `storage/full_bg_out.txt` | storage/scratch | EXCLUDE_LOCAL_ONLY | Debug/output logs; never integrate. |

### 3.17 Untracked — tests: Feature

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U95 | `tests/Feature/AdvisorAiPipelineTest.php` | tests/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed advisor pipeline test. |
| U96 | `tests/Feature/AdvisorAnalysisRouteTest.php` | tests/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed; 503 case pinned. |
| U97 | `tests/Feature/AdvisorProvenanceTest.php` | tests/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U98 | `tests/Feature/AdvisorRevisionTest.php` | tests/advisor | WAIT_FOR_PEER_COMPLETION | ADR-0038 proposed. |
| U99 | `tests/Feature/CareerReferenceEnrichmentTest.php` | tests/career | INCLUDE_AFTER_OWNER_REVIEW | Career reference enrichment test. |
| U100 | `tests/Feature/CockpitCorrectionPayloadRemovedTest.php` | tests/cockpit | INCLUDE_AFTER_OWNER_REVIEW | Cockpit correction payload test. |
| U101 | `tests/Feature/CockpitDeferredPropsRealRescueTest.php` | tests/cockpit | INCLUDE_AFTER_OWNER_REVIEW | Cockpit deferred props test. |
| U102 | `tests/Feature/CockpitDeferredPropsTest.php` | tests/cockpit | INCLUDE_AFTER_OWNER_REVIEW | Cockpit deferred props test. |
| U103 | `tests/Feature/CockpitQueryBudgetTest.php` | tests/cockpit | INCLUDE_AFTER_OWNER_REVIEW | Cockpit query budget test. |
| U104 | `tests/Feature/DatabaseSourceProvenanceTest.php` | tests/database | INCLUDE_AFTER_OWNER_REVIEW | Database source provenance test. |
| U105 | `tests/Feature/DeckFixtureSeparationTest.php` | tests/deck | INCLUDE_AFTER_OWNER_REVIEW | Deck fixture separation test (ADR-0014 deck vs roster). |
| U106 | `tests/Feature/DeleteRunWithRaceEntriesTest.php` | tests/runs | INCLUDE_AFTER_OWNER_REVIEW | Delete run with race entries test. |
| U107 | `tests/Feature/RaceEntryObjectiveIndexBoundaryTest.php` | tests/races | INCLUDE_AFTER_OWNER_REVIEW | Race entry objective index boundary test. |
| U108 | `tests/Feature/RetrievalBenchmarkTest.php` | tests/perf | INCLUDE_AFTER_OWNER_REVIEW | Retrieval benchmark test (C-6 performance). |
| U109 | `tests/Feature/RosterDoesNotTouchTeamRankTest.php` | tests/roster | INCLUDE_AFTER_OWNER_REVIEW | Roster vs team rank separation test (ADR-0039). |
| U110 | `tests/Feature/RosterMemberTest.php` | tests/roster | INCLUDE_AFTER_OWNER_REVIEW | RosterMember test (ADR-0039). |
| U111 | `tests/Feature/RosterMembersForeignKeyRepairTest.php` | tests/roster | INCLUDE_AFTER_OWNER_REVIEW | Roster FK repair test. |
| U112 | `tests/Feature/ScenarioImmutabilityTest.php` | tests/scenario | INCLUDE_AFTER_OWNER_REVIEW | Scenario immutability test. |
| U113 | `tests/Feature/ScheduledRaceStatusTest.php` | tests/races | INCLUDE_AFTER_OWNER_REVIEW | Scheduled race status test (team race countdown). |
| U114 | `tests/Feature/ScratchAdvVerifyTest.php` | tests/scratch | REQUIRES_ADDITIONAL_VERIFICATION | Scratch verify test; contents unverified. |
| U115 | `tests/Feature/SeededProvenanceNoteRefreshTest.php` | tests/provenance | INCLUDE_AFTER_OWNER_REVIEW | Seeded provenance note refresh test. |
| U116 | `tests/Feature/TeamRaceCountdownTest.php` | tests/races | OWNER_DECISION_REQUIRED | Tied to D-8 team-race slots (BLOCKED). |
| U117 | `tests/Feature/TelemetryDiagnoseTest.php` | tests/telemetry | INCLUDE_AFTER_OWNER_REVIEW | Telemetry diagnose test. |
| U118 | `tests/Feature/TraineeFilterPersistenceTest.php` | tests/trainee | INCLUDE_AFTER_OWNER_REVIEW | Trainee filter persistence test. |
| U119 | `tests/Feature/WizardDeckErrorPathTest.php` | tests/deck | INCLUDE_AFTER_OWNER_REVIEW | Wizard deck error path test. |

### 3.18 Untracked — tests: other

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U120 | `tests/ProxiedSchemeCheck.php` | tests | INCLUDE_AFTER_OWNER_REVIEW | Proxied scheme check. |

### 3.19 Untracked — tests: browser

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U121 | `tests/browser/build-snapshot.spec.ts` | tests/browser | INCLUDE_AFTER_OWNER_REVIEW | Build snapshot browser spec. |
| U122 | `tests/browser/career-cockpit-advisor-ai.spec.ts` | tests/browser/advisor | WAIT_FOR_PEER_COMPLETION | Ties to ADR-0038 proposed advisor. |
| U123 | `tests/browser/career-reference-panel.spec.ts` | tests/browser/career | INCLUDE_AFTER_OWNER_REVIEW | Career reference panel browser spec. |
| U124 | `tests/browser/career-scheduled-race.spec.ts` | tests/browser/races | OWNER_DECISION_REQUIRED | Tied to D-8 team-race slots (BLOCKED). |
| U125 | `tests/browser/cockpit-deferred-props.spec.ts` | tests/browser/cockpit | INCLUDE_AFTER_OWNER_REVIEW | Cockpit deferred props browser spec. |
| U126 | `tests/browser/database-sources-filters.spec.ts` | tests/browser/database | INCLUDE_AFTER_OWNER_REVIEW | N6.5 tier filter browser spec (ND-14(b) owner-approved). |
| U127 | `tests/browser/database-theme-matrix.spec.ts` | tests/browser | REQUIRES_ADDITIONAL_VERIFICATION | 3-theme × 2-width matrix; N6.17 PARTIAL (missing). |
| U128 | `tests/browser/dialog-light-dismiss.spec.ts` | tests/browser/ui | INCLUDE_AFTER_OWNER_REVIEW | Dialog light-dismiss browser spec. |
| U129 | `tests/browser/error-summary.spec.ts` | tests/browser/ui | INCLUDE_AFTER_OWNER_REVIEW | Error summary browser spec (N3.35). |
| U130-U131 | `tests/browser/fixtures/ime-enter.html`, `tests/browser/fixtures/ime-enter.ts` | tests/browser/fixtures | INCLUDE_AFTER_OWNER_REVIEW | IME enter fixtures. |
| U132 | `tests/browser/global-teardown.ts` | tests/browser | INCLUDE_AFTER_OWNER_REVIEW | Browser global teardown. |
| U133 | `tests/browser/ime-enter.spec.ts` | tests/browser | INCLUDE_AFTER_OWNER_REVIEW | IME enter browser spec. |
| U134 | `tests/browser/review-findings-regression.spec.ts` | tests/browser | INCLUDE_AFTER_OWNER_REVIEW | Review findings regression spec. |
| U135 | `tests/browser/unity-cup-roster.spec.ts` | tests/browser/roster | INCLUDE_AFTER_OWNER_REVIEW | Unity Cup roster browser spec (ADR-0039). |

### 3.20 Untracked — tests: utils

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U136 | `tests/utils/recorded-races.ts` | tests/utils | INCLUDE_AFTER_OWNER_REVIEW | Recorded races fixture. |
| U137 | `tests/utils/scheduled-races.ts` | tests/utils | INCLUDE_AFTER_OWNER_REVIEW | Scheduled races fixture. |

### 3.21 Untracked — tooling

| # | Path | Workstream | Disposition | Rationale |
|---|------|-----------|-------------|-----------|
| U138 | `tools/plan_contract_validate.py` | tooling | INCLUDE_AFTER_OWNER_REVIEW | Plan contract validation tool. |
| U139 | `tools/scheduler.ps1` | tooling/local | EXCLUDE_LOCAL_ONLY | Local PowerShell scheduler; Windows-only, not canonical. |
| U140 | `tools/typecheck.mjs` | tooling | INCLUDE_AFTER_OWNER_REVIEW | TypeScript typecheck tool (C-9). |

---

## 4. Disposition tally

| Disposition | Count | Examples |
|---|-------|----------|
| INCLUDE_AFTER_OWNER_REVIEW | ~78 | Roster (ADR-0039), telemetry, career, veterans, cockpit, tests, docs/roadmap, docs/plan |
| WAIT_FOR_PEER_COMPLETION | ~15 | Advisor services (ADR-0038 proposed), advisor tests, AdvisorAnalysisPanel, useAdvisorAnalysis, UmaAdvisorIndex, career-cockpit-advisor-ai.spec |
| REQUIRES_ADDITIONAL_VERIFICATION | ~5 | observability-integration, advisor-api-contract.json, database-theme-matrix.spec.ts, ScratchAdvVerifyTest.php |
| OWNER_DECISION_REQUIRED | ~9 | config/advisor_ai.php, ADR-0038 ADR, add_is_fixture migration, add_team_race_countdown migration, TeamRaceCountdownTest, career-scheduled-race.spec, ADR index rows (D-13) |
| EXCLUDE_LOCAL_ONLY | ~14 | storage/*.txt, _scan_scratch.php, 2026-11-24.md, SESSION_HANDOFF.md, TELEMETRY_IMPLEMENTATION_REPORT.md, agent.json, ki_entries.json, .actionlint.yml, .headroom/, favicon.png, app-logo.png, bg-*.jfif |
| SUPERSEDED | ~7 | docs/sitrep/* (consolidation + verification + index), docs/roadmap/archive/ |
| INSUFFICIENT_EVIDENCE | ~2 | nativephp plans |

---

## 5. Integration batches (dependency-aware, integration order)

### Batch A — ADR-0039 Roster sub-model (D-9 DONE, ACCEPTED) — FIRST

Integration order (dependencies first):
1. `database/migrations/2026_11_24_120000_create_roster_members_table.php`
2. `database/migrations/2026_11_24_140000_repair_roster_members_foreign_key.php`
3. `app/Enums/RosterDistance.php`
4. `app/Models/RosterMember.php`
5. `app/Models/TrainingRun.php` (M1 — adds `rosterMembers()`)
6. `database/factories/RosterMemberFactory.php`
7. `app/Http/Controllers/Career/RosterMemberController.php`
8. `app/Http/Requests/Career/StoreRosterMemberRequest.php`
9. `app/Services/Career/VeteranPortfolio.php`
10. `resources/js/components/career/UnityCupRoster.vue`
11. `resources/js/components/career/VeteranPortfolio.vue`
12. `docs/adr/0039-unity-cup-roster-recorded-sub-model.md` (+ add index row to `docs/ADR/README.md` — D-13)

Companion dependencies: `TrainingRun::rosterMembers()` (M1), `RosterMember` booted() guard, MAX_MEMBERS=20.
Verification evidence: `tests/Feature/RosterMemberTest.php`, `RosterMembersForeignKeyRepairTest.php`, `RosterDoesNotTouchTeamRankTest.php`, `CockpitRosterTest.php` (committed), `RosterSeederReRunTest.php`, `tests/browser/unity-cup-roster.spec.ts`.
Gate: C-5 `php artisan migrate:fresh --seed`, C-1 Pest.

### Batch B — Telemetry (Langfuse) — SECOND

1. `app/Providers/AppServiceProvider.php` (M2 — registers `LangfuseTraceService`)
2. `app/Http/Middleware/LangfuseTraceMiddleware.php`
3. `app/Console/Commands/TelemetryDiagnose.php`
4. `resources/js/pages/Preferences/Edit.vue` (M3 — telemetry status polling UI)
5. `tests/Feature/TelemetryDiagnoseTest.php`

Companion dependencies: `langfuse` config keys (enabled, base_url, public_key, secret_key, service_name, environment) read by `TelemetryConfigTest`.
Gate: C-1 Pest, C-9 `tsc --noEmit` for Edit.vue.

### Batch C — Career reference + veterans portfolio — THIRD

1. `app/Services/Career/CareerReference.php`
2. `config/imported_career_reference.php`
3. `resources/js/components/career/CareerReferencePanel.vue`
4. `tests/Feature/CareerReferenceEnrichmentTest.php`
5. `tests/browser/career-reference-panel.spec.ts`

Gate: C-1 Pest.

### Batch D — UI primitives + cockpit deferred props — FOURTH

1. `resources/js/components/ui/ErrorSummary.vue` (N3.35 error summary + focusField)
2. `resources/js/composables/useDialogLightDismiss.ts`
3. `tests/Feature/CockpitDeferredPropsTest.php`, `CockpitDeferredPropsRealRescueTest.php`, `CockpitQueryBudgetTest.php`
4. `tests/browser/cockpit-deferred-props.spec.ts`, `dialog-light-dismiss.spec.ts`, `error-summary.spec.ts`

Gate: C-1 Pest, C-9 `tsc --noEmit`, C-7 UI states (empty/loading/error).

### Batch E — Database sources + N6.5 tier filter (ND-14(b) owner-approved) — FIFTH

1. `tests/Feature/DatabaseSourceProvenanceTest.php`
2. `tests/browser/database-sources-filters.spec.ts`
3. `tests/browser/database-theme-matrix.spec.ts` — REQUIRES_ADDITIONAL_VERIFICATION (N6.17 PARTIAL, 3-theme × 2-width matrix missing)

Gate: C-1 Pest, C-7 UI states.

### Batch F — Remaining INCLUDE tests + docs — SIXTH

All other INCLUDE_AFTER_OWNER_REVIEW tests (ScenarioImmutabilityTest, ScheduledRaceStatusTest minus D-8 gated, RetrievalBenchmarkTest, DeckFixtureSeparationTest, DeleteRunWithRaceEntriesTest, RaceEntryObjectiveIndexBoundaryTest, SeededProvenanceNoteRefreshTest, TraineeFilterPersistenceTest, WizardDeckErrorPathTest, CockpitCorrectionPayloadRemovedTest, ProxiedSchemeCheck.php, build-snapshot.spec, career-reference-panel.spec, career-scheduled-race.spec [D-8 gated], cockpit-deferred-props.spec, database-sources-filters.spec, dialog-light-dismiss.spec, error-summary.spec, fixtures, global-teardown, ime-enter.spec, review-findings-regression.spec, unity-cup-roster.spec, utils/recorded-races.ts, utils/scheduled-races.ts).

Docs: all docs/roadmap (except archive/ SUPERSEDED), docs/plan (except nativephp INSUFFICIENT_EVIDENCE), docs/proposals, docs/research, docs/audits.

Gate: C-1 Pest, md_fence_check (already exit 0), doc_census (already exit 0).

---

## 6. Blocked / deferred (do not integrate until owner rules)

| Item | Blocker | Disposition |
|---|---|---|
| `database/migrations/...add_team_race_countdown.php` | D-8 BLOCKED (no citable source for team-race slots) | OWNER_DECISION_REQUIRED |
| `database/migrations/...add_is_fixture_to_umamusume_table.php` | D-10 BLOCKED (preseason rounds ruling needed) | OWNER_DECISION_REQUIRED |
| `tests/Feature/TeamRaceCountdownTest.php` | D-8 BLOCKED | OWNER_DECISION_REQUIRED |
| `tests/browser/career-scheduled-race.spec.ts` | D-8 BLOCKED | OWNER_DECISION_REQUIRED |
| `config/advisor_ai.php` + all advisor services/tests/components | ADR-0038 PROPOSED (not Accepted) | WAIT_FOR_PEER_COMPLETION / OWNER_DECISION_REQUIRED |
| ADR index rows for 0038/0039 | D-13 OWNER-GATED | OWNER_DECISION_REQUIRED (add index rows to README.md) |

---

## 7. Quality gate baseline (all run this session)

| Gate | Command | Result |
|---|---|---|
| check_untracked.py | `python tools/check_untracked.py` | exit 0 (4 case-mismatch, 54 gitignored-exempt) |
| md_fence_check.py | `python tools/md_fence_check.py` | exit 0 (335 files, 0 invalid fences) |
| adr-check.py | `python tools/adr-check.py` | exit 1 (0038/0039 missing index rows — D-13) |
| doc_census.py | `python tools/doc_census.py` | exit 0 |
| audit_status_register.py | `python tools/audit_status_register.py <file>` | needs file path arg (dir = PermissionError) |

**Still to run before integration:** C-1 `php artisan test --compact`, C-2 `vendor/bin/phpstan analyse --no-progress`, C-3 `vendor/bin/pint --test --format agent`, C-5 `php artisan migrate:fresh --seed`, C-9 `npm run typecheck` (`tsc --noEmit`).

---

## 8. Required owner decisions (recorded, not made by this session)

1. **D-8**: Trackblazer/Unity Cup team-race slots — citable source or terminal absence. Gates `add_team_race_countdown` migration, `TeamRaceCountdownTest`, `career-scheduled-race.spec.ts`.
2. **D-10**: Preseason rounds table — `preseason_rounds` + ADR, or ruling count is truth. Gates `add_is_fixture_to_umamusume` migration.
3. **D-13**: ADR signatures — add index rows for ADR-0038 (PROPOSED) and ADR-0039 (ACCEPTED) to `docs/ADR/README.md`.
4. **ADR-0038**: Accept or decline AI Advisor optional read-only layer. Until accepted, all advisor workstream = WAIT_FOR_PEER_COMPLETION / OWNER_DECISION_REQUIRED; `config/advisor_ai.php` stays off by default.
5. **nativephp plans** (INSUFFICIENT_EVIDENCE): confirm relevance before any integrate decision.

---

## 9. Deliverable boundary

No files staged, no commits made. This plan is the integration artifact for owner review. Upon owner sign-off on Batch A–E and the blocked items, integration proceeds in the order listed with gate verification after each batch.
