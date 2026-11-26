# TrainerDesk — Project Status

**Status date:** 2026-11-26 (MYT, UTC+8)  
**Application:** Trainer Desk / TrainerDesk  
**Repository:** `D:\Projects\umamusume-laravel13` · [GitHub](https://github.com/IzzatFirdaus/umamusume-laravel13)  
**Current version:** v3.0.0 pre-release  
**Current phase:** Integration, residual implementation, safety remediation and release qualification  
**Release disposition:** NOT QUALIFIED  
**Operating model:** Five permanent WorkBuddy Experts; four specialist worktrees; binding V2.1 authority; V2.2 hybrid execution prompts  
**Latest reported master checkpoint:** `3b9415c6` (VERIFIED by Docs 2026-11-26: actual `master` HEAD = `3b9415c6`; `origin/docs` fast-forwarded to match, effective diff empty)  
**Earlier supplied published master pin:** `6e17dca086dae6eafbf8ed1fafb3a4d956b19e6f` (2026-11-26)  
**Status provenance:** Original 23–26 November reports, V2.1 handoff and source/CI roadmap, reconciled with the supplied *TrainerDesk — Hybrid Expert Implementation Prompts V2.2*. The latter is a secondary account of newer Expert reports and assignments, not raw Git, test or MCP evidence. No fresh local or remote repository, MCP mailbox, database or runtime inspection was performed for this revision. Retain earlier pinned evidence as historical; establish actual current state at A1.

## 0. Read this first

This is the maintained project-state entry point for new sessions. Read it before using older chats or plans. It records current dispositions, decisions, dependencies and the next executable work; it does not replace canonical specifications, accepted ADRs, detailed incident records or the original cumulative requirement inventory. Where historical reports conflict with this reconciliation, do not revive their obsolete status or instructions. If new direct evidence conflicts with this file, reconcile the evidence and update the status explicitly; do not silently override approved scope or safety rules.

Authority: owner decisions and applicable permissions; `AGENTS.md`, `EXPERT-OPERATING-MODEL.md`, accepted ADRs, PRD/ARCHITECTURE/CONSTRAINTS/SCREEN_SPEC/DESIGN, issue register, five canonical domain roadmaps, active plans. SITREPs and audits are evidence, not independent authorization. The MASTER roadmap is historical ordering context, not a competing execution queue. Read actual local versions and section headings; do not invent references (e.g. old `SCREEN_SPEC.md §8` or fabricated DB-safety `AGENTS.md §13.5`).

### Exact resume action

**A1 → A2/B1 → C0; C1 and read-only Docs merge incident investigation can proceed in parallel after A1.** Establish current SHA/tree, all worktrees and existing assignments against the reported `3b9415c6` checkpoint. Backend message #42 is not accepted for integration: coordinate withdrawal/reopening of the frozen candidate and require independently generated synthetic-only fixtures plus discriminating authorization evidence. Fullstack and Docs investigate the reported published merge regression `23581aa2` and its reported restorations; validate the effective integration tree, not just the incoming branch. Do not rerun unsafe suites unchanged. Continue C0 Advisor wiring only where still missing and C1 Windows verifier dependencies where unresolved. Frontend resumes Task #8 from the reported `130e998e` synchronization; source/UI work can proceed independently, mutating browser tests require B1/B2 safety. Coordinator reconciles all actual assignments and acknowledgments. No blanket freeze.

**Next-session acceptance:** actual local HEAD/tree and ownership evidence, acknowledged P0 assignment or confirmed existing work, disposable-only test targets and meaningful authorization matrix, and an attributable integration plan. Do not create new Experts, duplicate existing assignments or replace implementation with another roadmap. The owner has not granted new execution, database, tag or publication permissions by requesting this file.

## 1. Product goal and locked boundaries

Deliver a locally operable, release-qualified v3.0.0 for a single Trainer playing the Global English release of Umamusume: Pretty Derby. Preserve Laravel 13, PHP, Inertia, Vue 3, TypeScript, Tailwind CSS v4, SQLite and Vite; local loopback, no authentication/multi-user/cloud deployment, no alternative authoritative database or browser-side persistence. Core capabilities are career creation/import, recording and correction, scenario workspaces, skills/races/decks/Legacy, Unity Cup team records, Veterans, catalogue, provenance and reviewed data ingestion, deterministic Advisor and optional read-only AI, accessible themes and read-only `/api/v1`.

- Record observed facts, explicit zero, unknown/null, derived values and sourced reference facts distinctly. No invented gameplay history, fabricated catalogue/roster entries, guessed mechanics, simulations, race-win probabilities or automated game playing. Global and JP eligibility remain distinct. Follow repository lore rules (C-4); preserve official names and source evidence.
- Deterministic Advisor authoritative; ADR-0038 accepted 25 Nov, optional AI disabled by default, read-only, no unapproved live inference, provider enablement, billing or automated action. Telemetry optional and failure-isolated; sending is not proof of ingestion/display.
- ADR-0039 approved recorded roster (up to 20, one ACE per distance, origin/card association, stats/caps, ten aptitudes); no individual member skill table or deck-generated roster. Team Rank manual; no auto derivation. D-8 and D-10 accepted; see §6.
- ADR-0027 append-only turn corrections; preserve original rows, effective readers and both distinct `turn_entries` indices. Do not restore retired Cockpit correction form/payload; dedicated Turn Edit is the replacement.
- Preserve N7.1 acceptance; N7.2–22 remain individually owner-gated. A0.14/KI126 declined/closed-wontfix; D06 collection/limit-break architecture deferred; D07 broad Scenario Detail redesign declined. ND-1–16 are individual decisions, not blanket implementation permission. Nine UI/UX revision areas are a separate register.
- Three themes: Azure Daybreak, Midnight Circuit, EVA-01 (purple-first, approved outcome-based visual balance rather than rigid green ratio). WCAG 2.2 AA; 44px project target distinct from 24px normative minimum with exceptions. Approved print, forced-colors and error-shell/backdrop policies; preserve responsive, keyboard, focus, contrast and reduced-motion requirements.
- Manual artwork mirror, honest missing-art placeholders; no automated remote image retrieval or new upload feature without approval. NativePHP local Windows installer approved; public distribution, updater and Cloudflare deployment deferred. No tag or release/cutover without owner approval of exact candidate and sequence.
- Later 1.0.0 API maturity gate is distinct from v3: close four recorded PRD questions, document `/api/v1` response shapes in ARCHITECTURE and eliminate unresolved API/schema defects. No automatic scope expansion.

## 2. Permanent Experts and execution governance

| Expert / role | Location / branch | Authority and active responsibility |
|---|---|---|
| Coordinator — Senior Project Manager | Main checkout, read-only; no fifth worktree | Cumulative tracker, evidence acceptance, dependencies, actual directed MCP coordination; no implementation, DB access, builds or Git mutations |
| Fullstack — Senior Developer | `.worktrees/fullstack` / `fullstack` | Technical integration, own cross-layer work, controlled Backend/Frontend delivery commits and master synchronization; incident, baseline, quality and CI |
| Backend — Backend Architect | `.worktrees/backend` / `backend` | Laravel, database safety, persistence, API/contracts, focused regressions; freeze exact verified delivery for Fullstack, no independent branch mutation beyond existing authority |
| Frontend — Frontend Developer | `.worktrees/frontend` / `frontend` | Vue/TS, UX, accessibility, browser isolation and rendered acceptance; controlled delivery, no independent branch rewrite |
| Docs — Technical Documentation Engineer | `.worktrees/docs` / `docs` | Documentation, research, canonical status, source evidence; autonomous scoped commits and normal push to verified Docs branch; master integration by Fullstack; no application DB queries/runtime/code |

Researcher is closed. Preserve all five WorkBuddy 5.11.7 permanent V2.1 sessions; use role-based ownership across the entire documentation corpus rather than agents per directory or subagents. Hybrid Expert Implementation Prompts V2.2 combine Autonomous AI Agent Prompt Collection V2 with Autonomous AI Agent Prompts V3. V2.2 is a prompt revision for the existing sessions, not a new operating model. V2.1 project-specific role and safety authority remains binding, including Fullstack-controlled Backend/Frontend delivery, Docs normal scoped publication and read-only Coordinator. V3 §11 safe checkpointing (isolated temporary index where appropriate) never extends permissions. Use English for user-facing reports and inter-agent coordination. One underlying requirement, one implementation owner. Coordinator is not an integration writer.

- Check root, branch, status/index, exact intended diff, peer ownership and remote before Git operations. Preserve checkpoints, stashes, archives and unique work. No hard resets, force pushes, history rewrites, blanket clean/staging, worktree removal, destructive recovery or shared Git metadata edits without specific authority. Freeze only the delivering specialist when genuinely needed; immutable Docs commits do not require a global freeze.
- Docs branch publication is approved; previous master publication occurred but separate approval is not present in supplied transcript. Do not infer future blanket master-push/tag authority. Additive correction, not rollback of published history.
- Temporary artefacts only in `.scratch-uma/` or explicitly sanctioned existing worktrees/private locations. Respect `_AGENTS_DO_NOT_CREATE_FOLDERS_OR_FILES_IN_THIS_DIRECTORY.txt`; no extra root scratch files. This root-level status file is explicitly requested by owner. Preserve existing scratch/recovery evidence; cleanup requires scoped approval.
- Validate dependency/asset realpaths, shared vendor/public/build junctions, Vite manifest, private process/port/PID, DB role and target identity. `public/hot` / Vite dev versus built asset issue historically reported; current state unverified. Do not use operational port 8000 for mutating browser verification. Do not override freshness or lower quality thresholds.

## 3. Evidence and repository checkpoint

Evidence labels: **PUB** = prior supplied report's pinned published source/remote/CI inspection; **REP** = attributed Expert report, not independently executed here; **DOC** = supplied document contents; **UNKNOWN** = no current proof; **NEXT** = proposed work, not done. A later snapshot supersedes only facts actually rechecked. Separate implemented, verified, integrated, published, accepted, release-qualified.

| Item | Last evidenced position | Current disposition |
|---|---|---|
| Master | `3b9415c6` (V2.2 prompt bundle, REP via DOC); earlier `6e17dca086dae6eafbf8ed1fafb3a4d956b19e6f` (roadmap, PUB) | Newer reported checkpoint; exact full SHA, ancestry, publication and live tip UNKNOWN |
| Docs merge regression | `67e7d947` (Docs merge), `23581aa2` (reported published master regression); restoration `57faa9de`, `b9a714f2` (REP via DOC) | Restoration reported; root cause, full effective diff, restoration completeness and current ancestry OPEN |
| Docs contributions | `67e7d947`, `3f78662f`, `7b34a048` (REP via DOC) | Preserve legitimate documentation; verify exact trees and publication |
| Frontend synchronization | `130e998e` (REP via DOC), preserve `186eb10b` | Recheck local branch/ancestry and actual source; producer evidence 34/271, rendered acceptance OPEN |
| Backend frozen delivery | MCP #42 (REP via DOC) | Rejected pending synthetic-only fixture correction and independent authorization evidence |
| Task #12 + Check #6 master merge | `0f58aefb57ebfcfbdcc7f1b558e460a6a732683e`; Backend `82786761` | Integrated/published; final safety acceptance conditional |
| Portable MCP V2 | `efdbafdef900a6c47f4a5213f795d9885b8b764b` | Integrated/published |
| Docs | `4039b1ac1f9a8069dd500d52a482e037eba9fa4f`; ancestors `ccd3ada7`, `318d76fd`, `1f6f0257` | Published; at older cutoff 5 ahead/14 behind master, base `f820ada9`; current divergence UNKNOWN |
| Earlier Backend final diff | `efdbafde...0f58aefb` | 10 paths, +407/-41 (not 9); actual newer diff requires inspection |
| Local worktrees | Earlier Master/Fullstack `0f58aefb`, Backend `82786761`, FE `eabdf141`, Docs `4039b1ac`; newer reported FE `130e998e` and Backend frozen #42 | Historical REP only; current local HEAD/tree, dirty files, branches, stashes, remote refs and active writers UNKNOWN |
| Original programme | 250 organizational assignments: FE 62, BE 84, Docs 46, FS 58 | Original 730-line inventory unavailable; not a feature denominator; no aggregate completion count/percentage |
| Destructive reset | Earlier discarded Backend Task #12 changes; recovery/reconstruction reported | A2 open until equivalence and unique recovery evidence established |

Earlier historical baselines: `3600741e` (23–24 Nov consolidation), `11207c42` (25 Nov test failure report), `320421e7` (pre-V2.1 integration). They are not today's local HEAD. Preserve historical evidence; do not blindly cherry-pick or restore whole files from old trees.

Read-only A1 preflight, run in verified registered worktrees using actual local access:

```powershell
Set-Location 'D:\Projects\umamusume-laravel13'
git rev-parse --show-toplevel
git worktree list --porcelain
git status --porcelain=v2 --branch
git rev-parse HEAD
git rev-parse 'HEAD^{tree}'
git log -1 --format='%H %s'
git diff --stat
git diff --cached --stat
git ls-files --others --exclude-standard
git ls-remote --heads origin master docs backend frontend fullstack
git ls-remote --tags origin refs/tags/v3.0.0
```

Inspect real intended diffs and compare refs only after confirming they exist. Commands are proposed; no local execution represented here. No secrets in logs. No canonical DB probing during baseline inventory.

## 4. P0 database safety and incident (do not bypass)

### 4.1 Task #12 and hazardous test targets

Task #12 and Check #6 were integrated at `0f58aefb`; FS-INT-3/L1 corrections exist, but authorization and safe test execution acceptance remain open. Prior direct inspection at that SHA: `AgentSessionGuard.php` 51–69, `DatabaseGuard.php` 142–166, `tools/agent-run.php` 47–62 already independently test SERVER/ENV/getenv for nonempty strings. **`"0"` is a valid agent-session marker**; absent/false/null/empty are not. No redundant implementation fix. Empty earlier sources must not hide later valid ones. Correct stale comment/error suggesting an ACK can allow marked agents. An agent cannot self-authorize via inherited ACK/override; role/target checks must be independently verified. File ownership is not the authorization matrix.

| Pinned source at `0f58aefb` | Hazard; recheck current code before correction |
|---|---|
| `tests/Feature/DatabaseSafetyTest.php` 17–29, 125–159 | `base_path('database/database.sqlite')` may resolve to genuine main DB |
| Same 478–519 | Hardcoded genuine path/main root, strips markers/role/ACK, queries `umamusume` count |
| `tests/Feature/AgentSessionIsolationTest.php` 25–28, 66–122 | Canonical path from base_path, synthetic events not sufficient identity proof |
| Same 125–160 | Hardcoded main-root subprocess, target not forced, generic REFUSED and filesize checks insufficient |
| Same 162–183; `tools/agent-run.php` 95–105 | Main launcher may snapshot canonical via VACUUM INTO; count is not claimed actual factory write |
| Same 185–242 | Marker-stripped forced genuine DB inventory reads |
| `DatabaseSafetyTest.php` 161–239, 591–720 | False-only tests cannot discriminate marker denial from independent gates; non-TTY “positive” actually false |

**Do not rerun these suites unchanged, even read-only.** No genuine data snapshot as test fixture. Use unique synthetic simulated canonical plus scratch A/B and a private application context. Assert normalized actual target identity before bootstrap/opening, reject real path/aliases/symlink/URI, prevent inherited env/config cache/PDO fallback; verify launcher uses only disposable synthetic data. Prior 89/451 and 15/45 green reports cannot establish disposable-only execution. No new destructive mutation is proved solely by test source.

**V2.2 delivery disposition:** message #42 is rejected for integration pending independently generated synthetic-only fixtures. The supplied prompts do not establish its exact file diff, tested SHA, executed commands, original tool receipt or database access history. Retrieve the actual delivery and coordinate reopening before modifying it. Explicitly prohibit VACUUM INTO, SQLite backup, file copying or any other fixture creation from genuine Trainer data. Supersede #42 with a reviewed frozen candidate and meaningful positive/negative controls; do not change the already-correct marker predicate simply to match a contradictory report. Reconcile explicit `"0"` test names and assertions against the actual tested revision.

### 4.2 Mandatory discriminating matrix

Test both recognized marker names across independent SERVER, ENV, getenv and conflicting sources; `"0"`, other nonempty strings, empty, false, null/absent. Check actual entrypoints (`agent-run.php`, composer agent, agent:tinker and applicable guards) as well as methods. All targets below are simulated disposable fixtures.

| Case | Expected result |
|---|---|
| Marked agent, simulated canonical, absent/inherited ACK or override | Connection refusal; operator/destructive denial independently |
| Marked agent, simulated canonical, misleading role | Same refusal |
| Markerless isolated role, simulated canonical, even valid ACK/override | Refuse isolated context and destructive/mutation path |
| Correct role, expected/resolved scratch A | Permit authorized write/read and prove it occurred on A |
| Expected A, resolved B, cached PDO/purge/reconnect/runtime config switch | Refuse wrong identity before connection handed out |
| Markerless non-isolated trainer, simulated canonical ordinary operation | Non-destructive positive control |
| Markerless operator, missing/wrong signals or noninteractive | Destructive authorization denied |
| Markerless operator, both valid signals and supported interactive condition | Positive decision on simulation only; report unavailable if host cannot exercise |

Record role, marker-source class, expected/resolved identity, exact decision, exception/reason, exit, before/after disposable fingerprints and actual write/read. Check unique launcher scratch identity, not just filename. Backend repair → frozen delivery → Fullstack independent source review and safe execution → controlled integration; Coordinator tracks conditional acceptance. No real `UMA_OPERATOR_ACKNOWLEDGED=yes` workaround.

### 4.3 Eight-check ledger

| Check | Disposition | Next owner/action |
|---|---|---|
| #1 Connection lifecycle | Prior PASS; affected checks revalidate | BE/FS safe corrected suites |
| #2 Actual multi-session isolation | PARTIAL | FS: actual distinct Expert environments/children, native IDE evidence |
| #3 Browser identity | Final acceptance outstanding | FE: wrong server/port/role/DB/token/PID, occupied port, replacement worker/retry; private positive case |
| #4 Maintenance authorization | Integrated, conditional | BE/FS: full matrix above |
| #5 Expected identity | Prior PASS, revalidate affected paths | BE/FS: expected A vs B/cache/role |
| #6 Fixture classification | Integrated; reported 8/29, coverage limited | BE: verify independent catalogue page vs API, fixture eligibility, provenance |
| #7 Docs and classification | PARTIAL | Docs/Coordinator: dated reconciliation and failure disposition |
| #8 Persistent data preservation | Historical structural PASS only; incident OPEN | FS/owner: bounded approved offline preservation; no repeat live probe |

Check #6: default factory fixture true, `catalogue()` false; Global vs JP eligibility, manual flag independent, ordinary collection exclusions, resource `isFixture`; intentional fixture flag is allowed. Two differently named tests exercised same API index in older source; separate page behavior and external real-record provenance not proved. Never revive unsupported mandatory 28-name fixture pool or delete synthetic rows arbitrarily.

### 4.4 Canonical automation incident — OPEN

Researcher automation `dd93502b-59d7-496d-a982-8019f6bb86d4` (test-participant-a) reportedly ran once and was paused. Intended disposable MCP experiment; reports/logs mention app users, RFC-invalid email, TEST MESSAGE, guard refusal and possible insert/delete/count. Email and time reports conflict; actual write mechanism, exact changes and residual effects UNKNOWN. Laravel-mediated access guarded; arbitrary raw PDO/sqlite3 and filesystem writes are outside that guard, not proof raw PDO caused the incident. No OS-level isolation established. Empty table, same size/count or integrity pass cannot prove no prior changes.

Keep offending automation paused; Researcher closed. Preserve existing config/run logs, backup manifests/hashes and evidence. No repeat live probes, resume/recreate, speculative restore, operational migration/seeding/deletion or ACL changes. Fullstack owns investigation; BE handles proven application containment defects; Coordinator records limits, Docs documents. Any consistent post-incident snapshot and offline comparison to pre-incident backup requires bounded owner approval, distinct target, SQLite-consistent backup procedure, offline integrity/FK/hash verification and legitimate gameplay difference analysis. Do not casually copy a live DB without coordinating WAL/SHM. Other automations reportedly unchanged (eight active), not proof of isolation; no unrelated disablement.

Historical SQLite audit: CLI 3.54.1, PHP SQLite 3.53.2, WAL, integrity/FK checks passed and storage normal by report; two `turn_entries` indices serve different query purposes (`turn_entries_run_turn_lookup`, `turn_entries_run_turn_original_unique`), preserve both. Unexpected `t` table with two apparent 2026-11-10 test rows: origin/dependencies unresolved. Effective busy timeout, FK enforcement, WAL concurrency, test contamination paths and original/corrected invariant coverage require bounded safe audit. No destructive cleanup. Historical backup `database.sqlite.backup-20261125-215656` reported integrity OK/FK0; path/durability not independently established. Operational data and migration counts UNKNOWN.

### 4.5 Published Docs merge regression — OPEN (separate incident)

The V2.2 prompts report that Docs merge `67e7d947` introduced unexpected application/tooling changes associated with published master regression `23581aa2`. Restoration is reported at `57faa9de`, `b9a714f2` and subsequent commits; the actual trees, parent diffs, changed paths and causal mechanism are not supplied in the prompt bundle. Do not infer that branch divergence alone caused the regression, or that restoration closes the incident. Preserve the legitimate documentation from `67e7d947`, `3f78662f` and `7b34a048`.

Fullstack and Docs: inspect both parents, merge tree, conflict resolution and effective diff against the intended integration base; establish whether index manipulation, merge resolution or another mechanism caused stale application content/deletions. Verify restoration semantically against accepted contracts and current source. Any proposed Docs merge must demonstrate authorized documentation-only effective changes, preserve unrelated source/tooling, verify exact staged diff and resulting commit tree before publication. No indiscriminate unstaging of application merge results, force-push, history rewrite, competing Docs application repair or unreviewed mixed-scope merge. Coordinator records restoration and root-cause acceptance separately. Fullstack owns application/integration remediation; Docs owns documentation evidence and scoped publication. No new master-push permission.

## 5. P0 Advisor regression and Windows CI

### C0 — Advisor vertical slice (Fullstack, after A1; tests after B1/B2)

Latest source roadmap at `6e17dca` found `AdvisorAnalysis.php`, typed client, composable, panel and tests present, but missing `runs.advisor-analysis` GET route, `CockpitController::advisorAnalysis()`, sibling `advisor_revision` prop, `AdvisorRail.vue` panel mount and public `AdvisorSnapshot::revision()` delegate to the fingerprint algorithm. Earlier November 23 passing sign-off and Accepted ADR-0038 do not establish current wiring. Recheck current branch before editing, restore only missing contract-aligned hunks (not old whole files). Preserve deterministic `NextDecision`, `{decision, finale}` contract, current cockpit/Unity Cup code, off-by-default and read-only behavior. Thread revision Cockpit → rail → panel → composable using same backend fingerprint. Verify route registration, disabled 404, enabled 200 incl fallback, stable 503 `advisor_analysis_unavailable`, stale same-turn response rejection, no writes, panel accessibility/mount/requests, typecheck/build. Existing `AdvisorAnalysisRouteTest`, `AdvisorRevisionTest`, `career-cockpit-advisor-ai.spec.ts` on isolated target. Run #48 existence/current state and real provider activation unverified; no unauthorized live calls.

### C1 — Windows fast-gate bootstrap (Fullstack, parallel after A1)

At `6e17dca`, [Windows Build #45839322224](https://github.com/IzzatFirdaus/umamusume-laravel13/actions/runs/45839322224) and [Windows Acceptance #45839795925](https://github.com/IzzatFirdaus/umamusume-laravel13/actions/runs/45839795925) failed fast gate because `tools/verify-backdrop.py` imports unavailable NumPy; Pillow is also a verifier dependency. Installer/executable jobs were skipped, not failed or passed. [Codespaces #45839320246](https://github.com/IzzatFirdaus/umamusume-laravel13/actions/runs/45839320246) reported successful. Inspect current workflow, install/reuse reproducible compatible test-only NumPy/Pillow in both Windows workflows, preserve verifier and thresholds; execute actual gates and reruns before evaluating packaging. No broad runtime dependencies or disabled gate.

## 6. Cumulative functional acceptance ledger

Preserve existing functionality; a source presence or historical pass is not final acceptance. IDs retain their source namespace; D-8/D-9/D-10 in different historical plans are not interchangeable. This is a priority ledger, not a substitute for the original individual 250-item inventory.

| Requirement / domain | Current reconciled disposition | Remaining work / owner |
|---|---|---|
| Core career setup, import, Snapshot, Independent Training | Implemented; historical verification | Preserve standard start at Preflight, distinct imports/snapshots, validation, recorded Energy and actual career position; FS/FE regression |
| N7.1 | Accepted/absorbed | Do not redo; N7.2–22 require individual rulings |
| D01 / ADR-0039 roster | Implemented; D01 9/137 reported accepted | FE/BE/FS current populated CRUD, cap/origin/support identity and ten aptitude fields, explicit null/omission/clearing; do not add another roster model |
| D-8 calendar | Accepted and implemented | Five sourced scenario slots, provenance, entry null/recorded, idempotence; no parallel team-race table |
| D-10 preseason | Accepted and implemented | Rounds 1–4 WIN/LOSS/null, partial vs explicit clear, legacy aggregate independent, round-trip |
| D02 / ADR-0034 Team Rank | Implemented, manual | Preserve manual rank independent of roster/rounds/league |
| D03 and Task #8 | Backend contracts and FE consumption substantially delivered; final acceptance OPEN | FE six-contract runtime verification (below) |
| Career Reference / KI172 / TD03 / D-1 | Producers/panel implemented by reports | Verify Timeline/Veteran actual payload, links `veteran_url` and `run_url` separately, deletion/absence/scheduled semantics |
| ADR-0027 corrections | Writer recovered, five acceptance items outstanding | BE/FS repeated corrections, effective readers, decision/snapshot fidelity incl omission/failure, stale revision/rollback and migration behavior; read actual ADR |
| C05 scenario lock, C09 trainee filters, KI-173 Scheduled semantics | Implemented and historically tested | Preserve scenario immutability after first recorded turn; filters across navigation; Scheduled not a recorded entry or completed objective |
| C06/C08 keyboard feedback and scenario availability | Historical mixed evidence | Reproduce current issue before modifying approved behavior |
| N2.11 / Source Ledger, TD08/D-2/N6.6 | Source union and UI implemented, historical/resolved conflict coverage to verify | BE/FE five states open/resolved/mixed/none/unknown, affected_sources vs open count, provenance timestamps/channel/filters/links; no invented counts |
| Race planner / absences | Existing planner, RaceFacts and disclosures | Verify tier/surface/distance/distance_band/fans_needed, no fabricated race facts or running style on race |
| BO-013 Cap wording; Result Points terminology | Owner rulings accepted | Reconcile current rendering; keep internal grade_points compatibility, no terminology-only migration, provisional cap not confirmed maximum |
| BO-003 / EX-02 fixtures | Check #6 integrated | Coverage limitations §4.3; preserve genuine catalogue eligibility, manual flag, history |
| N6.8 catalogue navigation; N5.1/N5.2/N5.3/N5.4/N5.5; N3.39/N6.17; F-1/F-2 | Implemented/reported focused verification | Verify integrated snapshot: query return path, error summaries, IME, deferred Cockpit, accessibility matrix, absence values, Veteran/Snapshot parity. Do not rebuild |
| R0–R5, Phase 5 and nine UI areas | Core implementation delivered; final runtime acceptance not established | FE actual three themes and 1280/768/390/320, keyboard, focus, target, contrast, overflow, hierarchy, spacing, motion, statistical identity; keep HFW decisions and 44px house rule |
| Advisor ADR-0038 | Accepted; integration regression at latest supplied SHA | C0 §5; C4 optional operational acceptance only if owner permits |
| Telemetry / scheduler / launcher | Substantially implemented with later runtime evidence | FS classify remaining liveness, termination latency, generation tracing/association, actual scheduler/startup reliability; no provider enablement |
| NativePHP | Local installer approved; qualification outstanding | C1/D/E; actual packaged executable evidence and owner-controlled release |

### 6.1 Unity Cup six-contract and roster acceptance

Task #8 resumes from the reported Frontend synchronization to `130e998e`, retaining `186eb10b` and later legitimate changes. Producer-side 34 passed / 271 assertions is reported; exact tested revision, command and logs must be recovered before attributing it. The newer V2.2 prompts identify the rendered `team_race_calendar` consumer as missing; recheck current Vue source and implement only if still absent. Verify actual server payloads and rendered UI for (1) team-race calendar, (2) manually recorded Team Race countdown, (3) independent computed Finals countdown, (4) observed member count, (5) Career Reference, (6) standard schedules. Existing `advisor.finale.turns_away` and `reference.schedule` may satisfy the Finals and schedule contracts if actual equivalence is demonstrated; do not create duplicate consumers or backend models. Test null, zero, recorded and unknown states, explicit clearing, invalid input, reload, navigation and scenario switches. Do not alias goal/manual countdown/finals or fabricate fallback values. Check #3 first: actual expected role/token/path digest/canonical flag/PID match before mutations, wrong server/port/DB/role, occupied port, replacement worker/retry and positive private server. Last FE private DB absent / port 8003 inactive by report; verify current state, do not use :8000.

D-8 calendar anchors: Junior Late Dec, Classic Late Jun, Classic Late Dec, Senior Late Jun, Senior Late Dec (finals). Use `ScenarioSlotSeeder::seedUnityCup()`, sourced `team_race` slots with source_key/order/label/provenance. No invented turn numbers, venue, outcome or standard race semantics. Roster state: separate actual member identity/origin, exact support variant, observed aptitudes and count, recorded row count, stats/caps, formation. Verify existing rendered controls (earlier source roadmap found aptitude form state but could not establish actual inputs); use accepted association validation only, not unsupported same-deck membership restriction. Up to 20, one ACE per distance; no per-member skill table. Backend payload already includes member origin, card identity and ten aptitudes, Team Edit roster prop, observed count, countdown and preseason rounds. Do not rewrite these wholesale.

### 6.2 Historical Rice Shower reference (source, not current DB proof)

`[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (68 captures, 67 distinct) is the source for the historical Unity Cup snapshot. Preserve source/provenance; do not conflate catalogue defaults with run observations. Historical Blue Bloom / Dreaming Big, Team Rank S, league 8th; Senior Early Oct, imported turn 67, five turns remaining to goal and finals; mood GREAT, low Energy band with unknown numeric value. Dashboard should reflect turn 67 and low band, including `Imported snapshot at turn 67.`; earlier Training Runs page was correct while Dashboard regressed, then build reportedly corrected it. Recheck without mutating owner run.

| Observation | Value |
|---|---|
| Speed / Stamina / Power / Guts / Wit | 607 / 577 / 549 / 736 / 397 |
| Recorded caps | 1325 / 1322 / 1368 / 1308 / 1800 |
| Skill Points / fans | 173 / 209,245 |
| Team grades | A / A / A / S / A |
| Unity trainings / Spirit / Extreme bursts | 55 / 6 / 5 |
| Observed vs recorded team members | 20 observed; 17 named + trainee = 18 recorded; 2 unknown |
| ACE | Sprint Seeking the Pearl; Mile Nishino Flower; Medium Tokai Teio; Long Rice Shower; Dirt Haru Urara |
| Preseason | Four wins observed; finals next |

Original run #48 existence/current state not established; reported reconstruction #54 (18 roster, 6 deck, 5 races, 11 skills, 2 turn events) not proven equivalent, richer original notes/counts may be missing. No renumbering, invented members or recovery on live DB. Compare approved offline copies to source if owner authorizes. Source report carries unresolved stat-cap layer, burst/gold thresholds/source disagreements and catalogue vs observed aptitude discrepancy; do not convert these into deterministic application calculations without resolution.

### 6.3 Source-gated and deferred work

- `UMAMUSUME_REFERENCE.md` and Rice Shower report distinguish Global/JP, client observations, publisher claims, stale/disputed information and inferred mechanics. Preserve the source conflict log; do not promote source-gated proposals into approved features.
- Trackblazer: use Result Points in UI, existing internals unchanged. Manual variants reported standard 60/300/300, dirt 30/200/300, limited turf 60/200/300; exact thresholds/precedence require source evidence; no invented objective derivation. S18-E1 walkthrough outstanding.
- Happy Meek: exact Global duel category rewards/effects unresolved; +4 cap evidence does not authorize unsupported automated outcomes.
- Grand Concert S9–18: lesson/song/live bonus/promotional lives evidence gates; retain established scenario vs finale labels and recorded behavior, do not invent unsupported mechanics. S18-E2 Grand Concert→URA control classification remains to verify.
- L–P source requirements missing/unresolved; A–K packets reported. ND-4 transfer re-bank third state not established; preserve ADR33 offered/completed/withdrawn, nullable veteran_id, recorded reward (not automatic payout). ADR16/ND2, ADR37, N5.6 source-dependent provenance/eligibility; no invented probabilities. A0.14/KI126 declined, not reopened by ADR-0039.
- Original approved audit rulings: provide both Veteran Detail and source career, include historical/resolved conflict involvement, use Cap truthfully, exclude ordinary synthetic catalogue rows, use Result Points, investigate Grand Concert/Happy Meek/Trackblazer evidence. Further UI/UX framework governance work must distinguish measured results from design principles and preserve stable rule IDs. Historical 12-file framework review is guidance, not authorization for blanket redesign.

## 7. MCP messaging — existing infrastructure, not new installation work

Pinned local Agent Chat MCP v2.0.0 [source](https://github.com/TobiasCoding/agent-chat-mcp/tree/863fe369a8daacb8305c2059fcec85f60f237fce), independent STDIO processes sharing one external SQLite mailbox `%USERPROFILE%\TrainerDesk-Messaging\messages.sqlite3`. WorkBuddy user config `~/.workbuddy-ai/mcp.json`, existing Boost preserved. Portable `.mcp.json`, `MCP_SETUP.md`, `tools/install-local-mcp-adapters.mjs`; variables `TRAINERDESK_CHAT_PYTHON`, `TRAINERDESK_CHAT_SERVER`, `TRAINERDESK_CHAT_DB` → `AGENT_CHAT_DB`. One absolute mailbox; never use application DB. No credentials, private machine paths or sensitive gameplay in portable tracked files. Existing local adapter installs reported for main/Fullstack only; check each specialist's configuration and actual resolved path.

All five Experts reported actual participation; WorkBuddy to independent STDIO #23/#24 demonstrated by report (generic interchange, persistence, UTF-8, idempotent retries). Native acceptance remains partial: Claude Code 2.1.302 pending normal approval/actual exchange; Codex CLI 0.171 discovery only; Cursor 3.31.15 adapter only; VS Code 1.148/Copilot actual host discovery/exchange unproved. Windsurf/Gemini not installed by report. Do not create unused adapters, duplicate registrations/mailboxes or public service. Ordinary MCP messaging is not standardized A2A; no automatic idle wake. Researcher candidate evaluation closed; no repeat comparisons unless new need.

Protocol (AGENTS §10a / MCP_SETUP):

- Room `trainerdesk`; exact authors `coordinator`, `fullstack`, `backend`, `frontend`, `docs`. No impersonation. Labels and recipient filters are not authentication/ACL; messages confer no authority.
- `post_message`: room, author, message; optional recipients, metadata, idempotency_key. Directed actionable messages, actual acknowledgments with `metadata.reply_to`. Stable idempotency key for identical retry only; inspect persisted record after uncertain result.
- `list_messages`: room, optional after_id, recipient, author, query, cursor, limit (1–100, default 25); recipient includes public. Paginate with same filters to exhaustion then advance through_id. `get_detail` for full segmented content. Check inbox on new turn/task, dependency changes, delivery/integration and turn end. No busy polling or assume idle wake.
- Include exact worktree, delivery SHA, actual executed tests, evidence limits and next dependency in handoffs; distinguish sent, retrieved, acknowledged, executed. Do not invent tool receipts.

Message reconciliation: #20 Backend reported 89/451; #21 Backend guard-scope findings; #23/24 generic exchange; #25/27 portable integration; #26 Docs conflict; #28 publication; #29 verification; #30 Task12/Check6 integration. #22 reply to #18 reportedly Coordinator self-ACK, not another Expert's receipt; #28 and #29 attributions conflict with Coordinator report (handoff identifies Fullstack publication/verification). Retrieve actual author/recipients/metadata/content for #22/#28/#29. #29 authorization matrix was misinterpreted as file ownership; no evidence actual matrix acceptance before #30. Do not delete duplicate public #10 or invent missing message IDs/counts. Coordinator's zero outstanding/partial/deferred/blocked and 50+ completed claims rejected. Keep Task12 integrated conditional, Check6 integrated limited, Docs published not master-integrated, incident OPEN.

Newer MCP #42 is a rejected Backend delivery, not evidence of acceptance. Recover actual author, recipients, metadata, content and acknowledgments; issue a scoped superseding message only when the corrected candidate is frozen. Newer reported checkpoints and incident messages must be reconciled from actual records; no invented message IDs.

## 8. Docs reconciliation and programme records

Use existing domain authorities under `docs/roadmap/`: `PRODUCT-AND-CAPABILITY-ROADMAP.md`, `FRONTEND-AND-UX-ROADMAP.md`, `QUALITY-AND-VERIFICATION-ROADMAP.md`, `V3-RELEASE-ROADMAP.md`, `DECISIONS-AND-DEPENDENCIES.md`. Plan registry, ownership crosswalk and 2026-11-23 consolidation inventory/report under `docs/plan/` preserve relationships. Historical MASTER and RESIDUAL are not new queues; preserve archive, evidence, stable IDs and actual source dispositions. Older 26-plan validator and 33/33 MASTER slice ownership census are historical, not proof of current entire programme closure. Distinguish dependency, amendment, incorporation and supersession; `amended-by` points forward. Do not redo prior documentation consolidation.

Docs/FS: compare current master and docs semantically (at earlier base `f820ada9`, eight Docs-side paths: `AGENTS.md`, `CHANGELOG.md`, `EXPERT-OPERATING-MODEL.md`, `MCP_SETUP.md`, `README.md`, `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md`, `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md`, `docs/sitrep/SITREP-V21-CONTINUATION-2026-11-26.md`). Preserve unique content, do not duplicate or revive stale tracker. Verify current changes before merging. Update Check6 integrated evidence/limits, P0 incident/test safety, authorization wording and MCP protocol. Place full supplied handoff at `docs/sitrep/SESSION_HANDOFF-V2.1-2026-11-26.md` if not already present, append short pointer to existing `docs/SESSION_HANDOFF.md`; no overwrite of history. Current placement not verified. Docs normal scoped commit/push; FS integration. Validate actual Markdown including untracked docs (git ls-files selection can miss them), citations, links, ADR/issue register and PLAN-CONTRACT.

The newer published Docs merge incident (§4.5) changes the priority of branch reconciliation: prove effective documentation-only changes against the intended integration baseline before further Docs integration. Preserve the three newer reported documentation commits and legitimate prior work. Validate the commit tree, not just Markdown or the incoming diff. No blanket freeze; ordinary independent Docs work remains authorized.

## 9. Dependency-ordered remaining roadmap (A1–F3)

The latest supplied 22-slice roadmap is the planning crosswalk, not a new canonical queue. This table incorporates earlier safety/incident findings that the newer roadmap did not explicitly resolve. Reconcile each slice with current local sources, existing ownership and actual test execution; assign only remaining work.

| Slice | Owner | Objective / exit condition | Dependencies |
|---|---|---|---|
| A1 | FS + Coordinator | Read-only baseline, SHA/tree/ownership/worktrees/remote/tag and pending deliveries; preservation | None |
| A2 | FS + BE (recovery); FS + Docs (merge incident) | Destructive-reset recovery equivalence plus separate published Docs merge regression: both parents/effective tree, restoration verification, root cause, safe integration procedure; preserve unique evidence; test only after B1 preflight/repair | A1; B1 for tests (source review first) |
| B1 | BE + FS | Replace rejected #42 delivery with independently generated synthetic-only fixtures; actual discriminating authorization matrix and agent/launcher isolation; no genuine-data snapshots or live probes | A1/A2 source review |
| B2 | FE + FS | Private browser identity negative and positive probes, no wrong-server mutations | B1, fresh isolated bundle |
| B3 | BE | Check6 fixture eligibility/provenance, independently test page/API; preserve existing work | A1/B1 |
| B4 | Docs + Coordinator + FS | Eight-check evidence closeout; incident and safety limits truthful; no premature acceptance | A2, B1–B3, D1, incident evidence/owner gates |
| C0 | FS | Restore approved Advisor route/controller/panel/revision if absent, focused end-to-end | A1; B1/B2 for respective tests |
| C1 | FS | Reproducible Windows NumPy/Pillow verifier prerequisites, actual CI reruns | A1 (parallel) |
| C2 | FE | Phase 5 UI/UX and three-theme rendered acceptance; fix reproduced defects only | A1/B2, fresh build; C0 as affected |
| C3 | BE + FE + FS | Unity Cup persistence, extended roster, five calendar anchors, manual/derived fields and Task8 contracts, scratch migration/seed | B1–B3 |
| C4 | FS/owner | Conditional actual Advisor behavior and run existence; off state or approved read-only provider flow; no live canonical testing by agents | C0/B1, explicit operational authorization |
| C5 | FE/BE | Trackblazer S18-E1 / Grand Concert S18-E2 and approved scenario walkthroughs; no invented mechanics | B2/C2/C3/C0 affected paths |
| D1 | FS + specialists | Preserve newer suite artifact (reported 2,883 pass/50 fail/39 skip); establish source stability and exit; investigate 12 `canChangeScenario()` and 10 missing-route failures, other clusters and KI-169 identity; classify by reproduction: app/harness/env/already-fixed | A1/B1–B3 |
| D2 | FS | Full Pest on settled candidate, safe DB and no concurrent writers; explained skips/failures, current evidence | D1/C3/C5 |
| D3 | FE + FS | Full Playwright same SHA, private server/DB, current build, artwork fixture classification | B2/C2–C5/D1 |
| D4 | FS | Clean clone under approved scratch, composer setup, scratch migration/seed, artwork-present and empty-mirror placeholder | B1/C1, lockfile |
| D5 | FS + Docs | Whole-release quality gates, source/issue/version/changelog truth, no raised baseline | B4/C1/D2–D4 |
| E1 | FS + owner | Actual NativePHP build and packaged-exe acceptance on disposable profile; resolve tag/package ordering | C1/D4/D5, owner decision |
| E2 | Owner + FS | Explicit release/tag/push authorization for exact SHA and accepted gate matrix | B4/D2–D5, owner-approved E1 timing |
| F1 | Owner/FS/Docs | Later 1.0 API/PRD/schema maturity gate | E2 or separate scheduling |
| F2 | Owner + relevant Expert | Individual gated decisions incl ND/N7, source uncertainties; preserve declined scope; ADR-0027 already implemented, acceptance remains mandatory not new writer approval | Source evidence and ruling |
| F3 | Owner | Optional future work only with new scope; no automatic provider, cross-tab sync, redesign, infrastructure or member-skill expansion | New approval |

Cross-cutting obligations: ADR-0027 acceptance, source ledger and race absence verification, incident preservation, native MCP acceptance, Docs semantic integration, source-gated research, operational runtime/telemetry/scheduler and actual test/build isolation. They are not extinguished by roadmap phase closure. No circular prerequisite: A2 source review precedes B1; executable A2 closure follows corrected B1 harness. Broad D2/D3 never run before safe test identity is proven; independent read-only or isolated work can continue.

## 10. Verification and release qualification

| Evidence (historical unless stated) | Disposition |
|---|---|
| 89 passed / 451 assertions (Task12 + Check6; DatabaseSafetyTest, AgentSessionIsolationTest, Check6FixtureClassificationTest, CatalogRosterTreeTest); 15/45 post-merge | Reported green; unsafe test-target limitations; not disposable-only proof |
| Check6 8/29; earlier integration 87/581 | Reported targeted overlapping suites; never sum as unique counts |
| Earlier FE 15 browser harness cases | Pre-final safety changes; repeat relevant corrected identity checks |
| Newer FE producer-side 34 passed / 271 assertions (REP via V2.2 prompts) | Exact tested revision, command, and artifact unverified; not rendered Task #8 acceptance |
| Newer comprehensive suite: 2,883 passed / 50 failed / 39 skipped (REP via V2.2 prompts) | Provisional triage only. Preserve raw artifact; recover actual command, exit status, revision/tree and concurrent source changes. Investigate reported 12 `canChangeScenario()` and 10 missing-route failures, remaining clusters and KI-169 identity. No release qualification or blanket attribution |
| 25 Nov at `11207c42` (65 unstaged changes) | 1,973 failed / 938 passed, 2,911 tests; attribution unresolved; not current failures |
| 24 Nov Mission 7 | 2,758 passed / 5 failed / 40 skipped of 2,803; later five reportedly corrected; not current proof |
| Mission 9 | 2,760 passed / 28 failed / 40 skipped; concurrent changes invalidated attribution |
| N6.17 historical matrix | 60/60 reported; broader 72 passed, 3 themes/2 widths; not current whole-release evidence |
| PHPStan/Pint/TS/build/lint | Historical green and later red reports; reg.exe/tool interruption and incomplete output prevent blanket current green |
| Telemetry | Later report 41 targeted/145, both-provider termination 10.09s, four heartbeats and correlated traces; request-handler speed does not prove next-request availability |
| Current full PHP/browser, clean install, packaged executable | UNKNOWN / outstanding |

Final candidate: exact same settled SHA/tree for full Pest and Playwright, role/path/PID/preflight evidence, no concurrent source/build writers, no unsafe suites. Run current `tools/gates.sh` and canonical seven-condition matrix; full relevant Pint, PHPStan/Larastan level 6, Vue/TS typecheck, UI lint, Vite build, DesignTokens, contrast/backdrop, lore rulings, register/ADR/citation ratchets, Composer and npm production audits. Keep existing baselines; do not suppress/raise thresholds to pass. Earlier 708 citation baseline and UI lint 209 are historical policy evidence; inspect actual current enforced values. Symfony YAML prior update/audit is historical, recheck candidate. Missing prerequisites/environment limitations are blockers, not app failures. Any red/skip must be named, justified and accepted only under applicable owner release rule; no blanket waiver. Report current logs and exit codes, not predicted tests.

NativePHP: actual Windows workflow gate and installer/executable jobs, `composer install --no-dev`, `NATIVEPHP_EXE_PATH` resolved, package artifact, hidden acceptance on disposable profile/DB; current packaging behavior unknown because jobs skipped at latest inspected CI. Reconcile conflicting package-before/after-tag ordering with owner; do not cut tag to satisfy test. Verify current remote tag refs, VERSION.md and CHANGELOG.md for exact candidate. Local port 8000, runtime process, PHP/Node installs, scheduler, telemetry provider state and live database not verified; no operational cutover inferred. No release recommendation until data-safety and required release gates are green or explicitly accepted within authority.

## 11. Source index and update contract

Primary supplied status sources (read exact local/repository versions if available):

0. *TrainerDesk — Hybrid Expert Implementation Prompts V2.2*, supplied as `Pasted text(7).txt` (338 lines). Newer reported checkpoint `3b9415c6`, rejection #42, Docs merge regression/restoration, suite triage counts and Frontend continuation. Secondary source only; retrieve actual Expert transcripts, Git trees, MCP records and test artifacts before promoting these claims. V2.2 prompt instructions do not grant additional authority.

1. `SESSION_HANDOFF-V2.1-2026-11-26.md`, especially §§3–8, 9, 11–14: earlier pinned `0f58aefb`/`4039b1ac`, actual safety source findings, Expert roles, incident, MCP, next dispatch. Preserve separately.
2. `TRAINERDESK_REMAINING_WORK_ROADMAP_2026-11-26.md` §§2–6: later pinned `6e17dca`, confirmed Advisor wiring regression and CI dependency blocker, 6 phases/22 slices. This file combines rather than discards earlier P0 evidence.
3. Coordinator supplied cumulative SITREP (26 Nov 22:12 MYT), messages #1–30: attributed reports only; reconciliation in §§3–4, 7 above. Zero-open/fully-accepted claims not accepted.
4. Supplied November 23–24 handoffs and 26 November alternative roadmaps (pasted reports): planning consolidation, historical work, role realignment, approved rulings, regression and release history. Historical four-agent proposal superseded by five V2.1 permanent Experts. Do not use conflicting older open/closed counts as latest truth.
5. `[Rosy_Dreams]Rice_Shower_Unity-Cup.md` and `UMAMUSUME_REFERENCE.md` (source-backed game records, not live application state).
6. Repository `AGENTS.md`, `EXPERT-OPERATING-MODEL.md`, `MCP_SETUP.md`, `docs/INDEX.md`, `docs/SESSION_HANDOFF.md`, `PRD.md`, `ARCHITECTURE.md`, `DESIGN.md`, `CONSTRAINTS.md`, `SCREEN_SPEC.md`, `KNOWN-ISSUES.md`, ADR register/bodies, canonical roadmaps and existing active plan. `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md`, DOCS-RECONCILIATION companion, destructive-reset incident and Fullstack integration handoff are detailed evidence. Check paths locally rather than guess.

Pinned references: [latest supplied master](https://github.com/IzzatFirdaus/umamusume-laravel13/commit/6e17dca086dae6eafbf8ed1fafb3a4d956b19e6f), [Task12 merge](https://github.com/IzzatFirdaus/umamusume-laravel13/commit/0f58aefb57ebfcfbdcc7f1b558e460a6a732683e), [Docs publication](https://github.com/IzzatFirdaus/umamusume-laravel13/commit/4039b1ac1f9a8069dd500d52a482e037eba9fa4f), [older integration comparison](https://github.com/IzzatFirdaus/umamusume-laravel13/compare/efdbafde...0f58aefb). These are historical pins, not a fresh network lookup by this status-file author.

### Maintenance at each material checkpoint

Coordinator reconciles current state and actual MCP evidence; Docs maintains this file and canonical detailed trackers with Fullstack's integration coordination. Retain historical records in existing SITREPs, not repeated full narrative here. For each changed item record canonical ID/namespace, owner, exact SHA/tree and paths, status (implemented/verified/integrated/published/accepted as separate dimensions), executed command/exit/results, before/after behavior, database identity where relevant, residual limitations and required approvals. If blocked, identify the concrete blocker and next action. Never close a requirement on source existence, message narration, or a historical pass alone. Do not repeat a completed investigation without new contradictory evidence. Preserve individually completed, partially accepted, outstanding, blocked, deferred, declined and superseded items; no guessed aggregate counts. Keep this entry point concise enough to resume the next session and link the detailed evidence instead of introducing competing plans.

### Next checkpoint record (not yet executed)

- Current local master / remote / specialist HEADs and tree identities: UNKNOWN; A1.
- Exact outstanding delivery and actual acknowledgments: UNKNOWN; Coordinator/FS inspect MCP, including rejected #42, no duplicate assignment.
- Published Docs merge regression `23581aa2`: reported restoration `57faa9de` / `b9a714f2`, root cause and restoration completeness OPEN; FS/Docs inspect parents and effective integration diff.
- Frontend Task #8: verify `130e998e`, preserve `186eb10b`; missing rendered calendar reported, 34/271 producer result not rendered acceptance.
- Newer comprehensive suite 2,883/50/39: provisional triage, preserve artifact and establish exact attribution before further broad tests.
- P0 safe-test correction and authorization matrix: OPEN until current evidence proves completion.
- Advisor C0 and Windows C1: verify whether newer local/remote changes already resolved them; otherwise implement within approved scope.
- Canonical incident and recovery evidence: OPEN; owner-gated preservation/forensics, no repeat live probe.
- Release: NOT QUALIFIED; no current tag/packaging acceptance or release authorization established.

## Docs reconciliation checkpoint (2026-11-26, Docs)

**Verified Git state:** `master` = `3b9415c6` (restored, clean). `origin/docs` fast-forwarded `7b34a048 → 3b9415c6` (non-destructive, explicit refspec; effective diff vs master EMPTY; non-documentation diff NONE). Local `.worktrees/docs` HEAD still `7b34a048` (5 behind, 0 ahead) with uncommitted stale pre-Task#12 reverts (12 tracked + 3 untracked) — preserved to `.scratch-uma/docs-recovery-20261126T2347/` (patch + untracked copies + MANIFEST); local sync pending Fullstack/owner (no reset --hard performed).

**Docs merge incident (separate from restoration):** `67e7d947` merged docs-side `4039b1ac` with master `0f58aefb` (base `f820ada9` predates Task#12 code); resolution took docs side wholesale, discarding master code. `23581aa2` then merged stale docs `7b34a048` into master `6e17dca0`, propagating the deletion. Restoration: `57faa9de` + `b9a714f2` (code diff vs `6e17dca0` empty; Task#12+Check#6 89/451 GREEN). Remote reconciliation: Docs FF `origin/docs` to `3b9415c6`. Remaining obligation: local worktree sync (owner/Fullstack).

**Acceptance state preserved:** Check #6 = PASS (committed+frozen+integrated). Task #12 = CONDITIONAL (FS-INT-3 closed, L1 impl-correct; acceptance BLOCKED on synthetic-fixture resubmit). Backend msg#42 = REJECTED (Fullstack msg#44, Coordinator msg#43) — probe VACUUMs genuine canonical; requires independently generated synthetic-only fixture + discriminating matrix. Check #8 = open canonical-database incident (owner-gated offline preservation, P0 procedure). Check #7 = PARTIAL (50 fail/2883 pass triage logged, KI-169). Preserved: Advisor C0 (restored), Windows CI C1 (fixed), Frontend Task #8, remaining authorised roadmap slices; declined A0.14/KI126 retained.

**MCP:** Docs delivery reported to `trainerdesk` room msg#46 (reply_to #40). No new messages after #46 as of this checkpoint.
