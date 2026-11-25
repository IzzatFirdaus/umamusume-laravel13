# Trainer Desk - Consolidated Implementation Plan (2026-11-20)
<!-- PLAN-CONTRACT
plan: implementation-plan-2026-11-20
status: in-progress
slices: N0.1-N6.18,N7.1-N7.22
superseded-by: —
amended-by: —
open: ND-1..ND-16 owner register; 63 slices; crosswalk
-->

> **For agentic workers:** execute task-by-task against the live tree. Steps use checkbox syntax where a slice carries them. Every slice closes on code plus quoted test/gate output, never on a progress report. Re-measure every premise before editing (AGENTS.md §2a, §9): this plan was written against a moving shared tree.

**Status:** executable plan. Authoring pass only: no app/, database/, resources/, tools/ or config/ file was modified to produce it. Owner named docs/plans as the destination, which authorizes this file under the file-discipline rule.

**Goal:** take Trainer Desk from the current partially-landed 3.0.0 worktree to the tagged v3.0.0 release defined by the master roadmap, then through the post-tag work toward the 1.0.0 gate, folding the verified remainders of the 2026-11-20 career-workspace, new-career-wizard, snapshot-and-veteran, and database-and-help UI/UX reviews into dependency order without re-running closed work or reversing recorded rulings.

**Architecture:** Laravel 13, PHP 8.5, SQLite (WAL), Inertia + Vue 3 + TS, Tailwind v4. Career position, ceilings, advisor and scenario composition stay with their existing server-side owners (CareerPosition, CareerCalendar, ScenarioCaps, StatCeilings, TrainerAdvisor, config/scenarios.php). The plan adds no simulation, no prediction, no new storage, no new dependency.

**Spec sources:** docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md (ordering authority), docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md (a11y workstream), docs/plan/implementation-plan-2026-11-19.md (untracked peer plan, partially executed), docs/plan/trainerdesk-four-screen-ux-review-2026-11-19.md (untracked peer rollout record), the 2026-11-20 career-workspace (11 screens), new-career-wizard (7 routes), snapshot-and-veteran (3 screens) and database-and-help (9 screens) reviews supplied by the owner (all screenshot-based, claim-tested against the tree), PRD.md, SCREEN_SPEC.md, accepted ADRs.

## Baseline (measured this pass; labels per row)

| Fact | Value | Provenance |
| --- | --- | --- |
| HEAD at authoring | cd7cb8cb | measured: git rev-parse --short HEAD |
| HEAD at slice reconciliation | 25d039c0 | measured by reconciler; three commits landed after it: 41bc48e8 (race calendar sort), 0f8710a3 (ScenarioCaps printed figures), cd7cb8cb (gate failability) |
| Divergence | 0 behind / 4 ahead at 0f8710a3; push still owed | measured: git rev-list --left-right --count origin/master...HEAD |
| Working tree | 98 porcelain rows at cd7cb8cb (63 M / 42 ?? at 0f8710a3 before the gate batch committed) | measured: git status --porcelain |
| Tags | 0.1.0, 2.0.1, 2.0.2, 2.1.0, 2.1.1; no v3* | measured: git tag -l |
| Citation census | worktree ratchet 528 <= 708 | measured: python tools/doc_census.py (this pass) |
| Dev DB | 304 Pending gametora-races candidates; data_sources 133 rows behind 1 source_key; 402 of 410 race slots carry track_name | static-read SELECTs by reconciler at 25d039c0; re-derive before acting |
| plan19 execution | 44 slice ids: 17 DONE-IN-WORKTREE / 10 PARTIAL / 15 OPEN / 1 OWNER-GATED / 1 MOOT | static-read reconciliation at 25d039c0 (tally quoted, not re-run) |
| Career review claims | 64 claims tested: 12 refuted outright, the rest EXISTS-already / PARTIAL / MISSING per screen | static-read at 8fd7e18e worktree; pivotal lines re-verified by this pass |
| Wizard review claims | 64 claims tested (5 shell + 59 route): 24 EXISTS-already, 26 PARTIAL, 14 MISSING; 8 premise errors named separately | static-read at 37320f35; percent rounding, debut-form pill and dead error path re-verified by this pass |
| Snapshot/veteran review claims | 41 claims tested: 6 EXISTS-already, 18 PARTIAL, 15 MISSING, 2 WRONG; 8 ruling conflicts (G1-G8) | static-read at 37320f35; snapshot surfaces clean at HEAD, veteran detail clean; AbsenceValue importers re-measured 40 |
| PHP floor | composer.json:12 reads ^8.4.1; zero langfuse/latitude hits | measured: search_files this pass |
| HEAD at database pass | 37320f35 (unchanged through the pass) | measured: git rev-parse HEAD |
| Divergence at database pass | 0 behind / 13 ahead; push still owed | measured: git rev-list --left-right --count origin/master...HEAD |
| Working tree at database pass | 112 porcelain rows (68 M / 6 D / 38 ??); the 6 D are the peer docs/plans-to-docs/plan rename | measured: git status --porcelain |
| Tree shape at database pass | 62 tracked pages, 119 tracked components, 122 tracked browser specs | measured: git ls-files + grep -c |
| Database review claims | 74 claims tested: 33 EXISTS / 25 PARTIAL / 12 MISSING / 4 NEEDS-DECISION; 9 premises refuted | static-read at 37320f35 by three investigators; pivotal lines re-verified by this pass |
| Sources payload | 13 Fetched + 4 Transcribed = 17 rows; 4 open conflicts hardcoded in the page; matrix verified 2026-11-02 | measured: config/uma.php sources keys, config/reference.php anchors, resources/js/pages/Database/Sources.vue:72-77 |

Directory note (measured at 37320f35): during authoring a peer session began renaming docs/plans/ into docs/plan/ (uncommitted; tracked files show D at the old path, copies untracked at the new one, and the move tool rewrote path references inside this file). This file was carried by that move and lives at docs/plan/implementation-plan-2026-11-20.md; it is not duplicated at the old path. plan19 was committed at e01be9b5 under docs/plans/ before the move, so its HEAD citation resolves there; the worktree copy sits under docs/plan/. The four-screen review and the sitrep remain untracked. Commits landed during authoring after the cd7cb8cb baseline: 4a257e66 (review verdict refusal, plan19 S1.11), e01be9b5 (plan19 committed), 37320f35 (PHP floor, plan19 S3.1).

The tree moves while it is read (master roadmap §14 trap). Every slice below carries a re-verify step; a verdict stamped at 25d039c0 is a snapshot, not a property.

## 1. Authority and disposition

Ordering authority stays with the master roadmap. This plan is its execution layer for the remaining work, plus one new workstream (Phase 3, the career-workspace review). It does not edit any prior document. Supersession is recorded here, never performed by rewriting sources.

| Document | State at cd7cb8cb | Disposition |
| --- | --- | --- |
| docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md | tracked, dirty (peer) | LIVE ordering authority. Worktree adds S0.6, D-13, §16 item 9. Several rows stale vs execution (see §5, N4.7). Not edited here. |
| docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md | tracked, dirty (peer) | LIVE a11y/reflow workstream. Phases 0-4 closed; Phase 5 (5.1-5.5) open, worktree-only addition. Absorbed as N5.1-N5.5. |
| docs/roadmap/EXECUTION-RECORD-2026-11-18.md | tracked, clean | Evidence appendix for the 816e923e execution session. Its DONE verdicts are inherited evidence, quoted not re-run. Header metadata stale (873 lines / 22 slices vs worktree 901 / 33). |
| docs/roadmap/RESIDUAL-VERIFICATION-2026-11-18.md | tracked, clean | Evidence appendix under old numbering; header map to master ids. §2.5 premise self-refuted; do not carry forward. |
| docs/roadmap/INDEX.md | tracked, clean | Register; stale metadata row for master (873/22/5). Correction owed via N4.7, not here. |
| docs/plan/implementation-plan-2026-11-19.md (HEAD copy: docs/plans/, committed e01be9b5) | tracked at HEAD; worktree copy untracked under the peer rename | LIVE, partially executed (17 done / 10 partial at 25d039c0). Its header "Nothing implemented" is stale. Remainders absorbed as N0.3-N0.5, N1.3, N2.1-N2.11, N4.1-N4.5, N4.8-N4.10. Its S-ids collide with master S-ids on different subjects; the crosswalk (§8) is the only join. Not edited. |
| docs/plan/trainerdesk-four-screen-ux-review-2026-11-19.md (moved from docs/plans/ by the peer rename) | untracked (peer) | Rollout record: Review/Skills/Veterans/Races plus palette/wallpaper wiring, all verified DONE-IN-WORKTREE by the reconciler. Excluded from remaining work (§3). Not edited. |
| docs/sitrep/sitrep-2026-11-19-visual-identity-session-handoff.md | untracked (peer) | Visual-identity handoff; palettes/plates committed at 8fd7e18e. D-13 answered-in-tree; master gate row stale. |
| docs/plan/implementation-roadmap-v3.0.0.md, docs/plan/remediation-plan-2026-11-15.md | tracked, clean | HISTORICAL. Recovery premise falsified (v3roadmap:1). Stay unedited per standing ruling. |
| docs/roadmap/v3.0.0-release-plan-2026-11-17.md and the ten earlier roadmaps | tracked, clean | HISTORICAL per master:4-6 and docs/roadmap/INDEX.md:14. No execution authority. "Never touch tests/browser/career-preflight.spec.ts" was superseded by the Independent Training restoration (exec S3.1 inverted two cut-pinning specs with recorded reason). |
| 2026-11-20 career-workspace review (owner attachment) | not in repo | Design proposal, screenshot-based. Claim-tested against code this pass; only verified gaps become slices (Phase 3). Its P0 layout reversals for Training/Events/Inheritance conflict with recorded HFW overrides and are owner-gated (ND-1). |
| 2026-11-20 new-career-wizard review (owner attachment) | not in repo | Design proposal, 7 routes, screenshot-based. Claim-tested at 37320f35; clean defects become N3.19-N3.27, ruling reversals are owner-gated (ND-9, N3.28), doc drift N3.29. Most P0/P1 recommendations conflict with recorded rulings (R7-36/R7-68, Slice 10.1, Phase D, HFW parity, ADR-0014 correction 1). |
| 2026-11-20 snapshot-and-veteran review (owner attachment) | not in repo | Design proposal, 3 screens, screenshot-based. Claim-tested at 37320f35; clean gaps become N3.30-N3.38, doc truth N3.39, ruling reversals owner-gated (ND-10, N3.40). The snapshot workflow itself is built (0bb3efd0); the review is a refinement ask, not greenfield. Three-state unknown contract and Review->Setup persistence already ship. |
| 2026-11-20 database & help review (owner attachment) | not in repo | Design proposal, 9 screens, screenshot-based. Claim-tested at 37320f35 (74 claims); verified gaps become Phase 6 (N6.1-N6.17), ruling reversals owner-gated (ND-11..ND-16, N6.18). Its P0 (Source Ledger) is a design priority, not a tag blocker. |
| docs/plan/legacy-spark-target-entry-2026-11-20.md | untracked (peer) | EXECUTED 2026-11-20: Race category + spark-only Skill target on the two Legacy entry surfaces; gates quoted in its execution log. Excluded from remaining work (§3.6); not the /database/sparks reference page. |

## 2. Intended final outcome

**v3.0.0 (master §16, worktree):** the line is tagged on a commit reachable from origin/master; full Pest and full browser suites have a quiet-box baseline at that commit with every red classified; no OPEN defect changes a shipped screen or the /api/v1 and schema surfaces; every capability the enabled configurations promise has a reachable write path and a read-back; PRD.md §6 non-goals stay cut. Whole-roadmap acceptance, nine items (master:877-886):

1. divergence 0 0
2. bash tools/gates.sh exits 0, every step quoted including the PHP suite
3. stable Playwright case count; full run green or every red filed with class and owning slice
4. DocCitationParityTest passes with DOC_CITATION_BASELINE still 708
5. adr-check.py 0 findings; every ADR contract matches Status
6. no OPEN KI names a shipped screen, /api/v1 or schema
7. VERSION.md + CHANGELOG.md describe that commit; v3.0.0 tags it
8. Independent Training run creatable, advanceable, readable through the UI
9. D-13 light/dark applied-and-green or formally parked with a dated note, never silently reversed

Anything less is a stated partial with the unrun gate named (master:888).

**1.0.0 gate (VERSION.md:58-63, master S5.9):** four open PRD questions closed, /api/v1 shapes declared in ARCHITECTURE.md, no OPEN defect touching API/schema. None satisfied at this HEAD. Post-tag Phase 5 closes them.

**Career-workspace UX outcome (review §6, narrowed to verified gaps):** each career screen separates the current decision from supporting evidence and history; required recording fields are reachable without scrolling past repeated explanations; unrecorded stays distinct from zero; navigation keeps link semantics and all nine destinations reachable; changes hold at 320/390/768/1280 across Azure Daybreak, Midnight Circuit and EVA-01. The review's layout reversals that contradict recorded HFW rulings ship only after owner decision ND-1.

**Database & Help UX outcome (db-review §8, narrowed to verified gaps):** a Trainer can trace any source-backed figure to its ledger record and back, distinguish a sourced fact from one guide's opinion and from absence, compare races/scenarios/items without re-reading prose, and resolve a specific Help question without reading the whole page. The ledger stays read-only, conflicts stay unresolved, cadence summaries stay transcriptions, and all nine areas plus Help hold at 320/1280 across Azure Daybreak, Midnight Circuit and EVA-01. Reversals of recorded HFW rulings ship only after ND-11..ND-16.

## 3. Completed work - excluded, with evidence

Do not re-run, re-build or re-verify these as work items. Verification at hand-off may re-run gates over them; that is regression, not implementation.

### 3.1 Master roadmap slices recorded DONE (inherited from EXECUTION-RECORD, quoted)

| Master id | Subject | Evidence (exec lines) |
| --- | --- | --- |
| S0.4 | Empty-diff status noise | exec:44 DONE |
| S1.1 | Quiet-box Pest baseline at 816e923e | exec:55-59, 309, 326-344 (2478 passed / 4 classified). Re-run owed at the NEW HEAD as N1.1, not as a rebuild of this result |
| S2.1 | page-header tier assertions | exec:98-102 DONE, 38-case batch green |
| S2.2 | KI-167 focus round trip | exec:104-142 DONE, root cause :disabled on radio, register CLOSED |
| S2.3 | KI-114 deck-picker specs | exec:144-159 DONE, premise refuted (residual:190-212), 32 passed |
| S2.4 | KI-127 card wrapper | exec:175-178 ALREADY FIXED at 1119e437 |
| S2.6 | Register closeout KI-116/119 + auditor vendored | exec:184-201 DONE (but parser drift re-found by plan19 S0.6; see N0.4/N0.5) |
| S3.1 | Independent Training door | exec:205-243 DONE, 33 passed / browser 15 passed; ADR-0032 accepted |
| S3.2 | Hint-level copy | exec:245-251 DONE; also committed 9fe072e5 |
| S3.3 | Japanese search key | exec:253-260 DONE, 30 passed |
| S4.1 | VERSION re-baseline at 816e923e | exec:271-280 DONE at that HEAD; stale again now, re-opened as N4.1 |
| S4.2 | CHANGELOG 3.0.0 range | exec:282-286 DONE |
| S4.3 | ADR hygiene agent half | exec:288-292 DONE, 0 findings; eight signatures landed via dba86362 (D-1 answered) |

Also landed by commit since the baseline (measured: git log 816e923e..HEAD): snapshot pipeline 0bb3efd0, Team Rank storage 721ad636 (ADR-0034), per-ancestor labels 00a1287b, veteran transfer dates c31ddc16, spark tally absence b6196caa, keyboard scrollers 36a761ba, whole-card scenario select fcf55782, navigation focus 137c4eba, IME fixture 62dc6a6f, hint copy 9fe072e5, eight ADR acceptances dba86362, probe retirements 12e97098, themes/plates 8fd7e18e, gate failability cd7cb8cb.

### 3.2 plan19 slices DONE-IN-WORKTREE (static-read reconciliation at 25d039c0)

S0.1 census/untracked exit codes (committed cd7cb8cb), S0.3 npm-audit UNAVAILABLE state (cd7cb8cb), S0.4 G-62 backdrop step (cd7cb8cb), S0.5 preflight delegates to gates.sh (cd7cb8cb), S1.3 maiden-lock predicate (TrainingRun.php hasImportedPosition() guard), S1.6 abandon confirmation door (Dashboard.vue confirm + flash; copy ruling parked ND-5), S1.7 independent policy requiredIf + Lifecycle::start in transaction, S1.10 cap arithmetic through ScenarioCaps (DatabaseController, DeckSelectController; extended by 0f8710a3), S1.11 review verdict integrity (Pending dropped, targetless Aliased refused, reopen upsert test-pinned; committed 4a257e66), S1.13 goals panel decisionTurn(), S1.14 turn renumber refused + non-column keys handled, S1.16 three Form Requests wired (AbandonIndependentTrainingRequest.php, ConfirmRunDeletionRequest.php, SnapshotReviewRequest.php; all tracked by 37320f35, SnapshotReviewRequest committed 4a257e66), S1.17 HintDiscount ladderKey 5/'5' to max (price(130,5)=78 pinned), S1.18 syncFailureEvent across three doors, S1.19 syncSkills transaction, S2.3 telemetry fully reverted (zero hits in composer.json/lock, bootstrap/app.php, routes/api.php; verified again this pass), S3.1 PHP floor ^8.4.1 + README rows (committed 37320f35). S4.5 MOOT (dissolved by S2.3 revert).

### 3.3 UI-UX roadmap Phases 0-4 (uiux §10/§11, inherited)

Reflow KI-166 fixed (.card:has(> table.t) position:relative), Race Planner 40px same rule, /legacy 41px, scenario card hit area 12/12, preflight ack hint, audit erratum + framework rule IDs + SCREEN_SPEC registry 66 rows, two browser passes green with reds classified. Do not re-implement (uiux:37-46 explicit exclusion).

### 3.4 Four-screen rollout (reconciler-verified in worktree)

Review decision workspace (queue continuation, scopes, explicit verdict, no bulk approve), Skills cost-first catalogue (base SP, Technical details disclosure, type_withheld), Veterans compact library + compare cap 2-6, Races dense sortable table (committed 41bc48e8), palette/wallpaper wiring (app.css --backdrop-image image-set, committed 8fd7e18e), four-screen-ux.spec.ts theme matrix (untracked). Their verification numbers are self-reported; the full-suite re-run at the new HEAD (N1.1/N1.2) is what re-proves them.

### 3.5 Career review claims already satisfied (do not build)

Native radio-in-label discipline selection (resources/js/pages/Career/TrainingDetail.vue:768-804); off-calendar race disclosure (resources/js/pages/Career/RaceDecision.vue:518-560); no silent zero probability (resources/js/pages/Career/RaceDecision.vue:476-490); separate build-target vs acquisition writes (routes/web.php:238,247); SP budget card with commit (resources/js/pages/Career/SkillsPlanner.vue:522-537); event empty state + Skip/Record/Undo distinction + transcription wording (resources/js/pages/Career/EventDecision.vue:251-253,407-442); one-selection-per-run inheritance contract (TrainingRun.php legacy_selection); aligned parent Spark tables that stack (resources/js/pages/Career/InheritanceEvent.vue:448-467); slot preserved across deck filters (resources/js/pages/Career/DeckSelect.vue:247,335-341); DeckSummary overview-first, analysis already removed from the deck step; per-form save boundaries and saved-state in RunSettings; blank posts null in settings and team (never 0%); Wit ceiling absence ruled server-side (app/Services/ScenarioCaps.php:32-41, ADR-0025); scenario grouping + seven specialized cards; nine-section nav with link semantics. Full verdict table: reconciler report, 64 claims.

Snapshot/veteran claims already satisfied (do not build): three persistent field states known/unknown/not_provided with zero a valid Known (SnapshotFieldState.php, app/Http/Requests/StoreSnapshotRequest.php:58-71, app/Actions/CreateSnapshotRun.php:189-206); TurnEntry written only when all five core stats Known (app/Actions/CreateSnapshotRun.php:41,113-123); Review->Setup rehydration of values AND unknown toggles (app/Http/Controllers/Career/SnapshotController.php:113-117, resources/js/pages/Career/Snapshot/Setup.vue:57-99, tests/browser/career-snapshot.spec.ts:234); review-before-create with explicit irreversible-commit acknowledgment (resources/js/pages/Career/Snapshot/Review.vue, SH3); descendant rows with trainee/scenario/status linking the actual child run (resources/js/pages/Veterans/Show.vue:305-311, app/Http/Controllers/VeteranController.php:191-204); Not-in-build already a closed-by-default disclosure (resources/js/pages/Veterans/Show.vue:350-359, 9c50f139, tests/browser/veterans.spec.ts:368); Factors refusal binding (ADR-0020 §3, app/Http/Controllers/VeteranController.php:344-356); stat grid already container-query responsive 2/3/5 cols (resources/js/pages/Career/Snapshot/Setup.vue:283); entry back fallback is the review's sanctioned option (resources/js/pages/Career/Snapshot/Entry.vue:67-72); notes label already specific (resources/js/pages/Career/Snapshot/Setup.vue:337-348); the disabled-door hint is an audit-ruled explanation, not stray validation (resources/js/pages/Career/Snapshot/Entry.vue:33-43, AUD-20261102-007 F-01).

Wizard claims already satisfied (do not build): whole-card scenario selection with arrow-key group + selected words (resources/js/pages/Career/ScenarioSelect.vue, fcf55782); descriptive Continue labels and one filled action per step (tests/browser/wizard-actions.spec.ts); sequential shell with accessible state words, no task list (resources/js/layouts/SetupLayout.vue:86-95,265-292); trainee filters map stored aptitude columns, result count + zero-match empty state + aria selection state (resources/js/pages/Career/TraineeSelect.vue:632-636,723-735); profile aptitude categories with letters-vs-stats note (resources/js/pages/Career/TraineeProfile.vue:168-194); profile return/continue preserves form (R3-06); optional skill priorities + disabled saved-defaults with reason (resources/js/pages/Career/BuildTarget.vue:637-641,720-731); Legacy three pathways + optional slots + five Spark kinds + record-only projected start + affinity wording (resources/js/pages/Career/LegacySelect.vue:377-385,680-691,738-752,834-844); deck modal slot-named picker, validity-first diagnostics, closed-by-default summaries, no deck score, responsive 2/1-col grid (resources/js/components/support/CardPicker.vue:179-187, resources/js/pages/Career/DeckSelect.vue:493-525,582-648, resources/js/components/support/DeckSummary.vue:153-168); preflight technical-vs-consequence separation, sole primary Create with acknowledgment + double-mint guard, Independent Training own disclosure with server requiredIf, flash+cockpit completion state (resources/js/pages/Career/Preflight.vue:657-686,1061-1146, app/Http/Controllers/Career/PreflightController.php:233-242).

### 3.6 Database & Help review claims already satisfied (do not build)

Hub nav distinct home pill + contained 320px scroll (resources/js/components/database/DatabaseNav.vue:43-48, pinned tests/browser/database.spec.ts:681-721); banner already prints the verification date and "nothing fetched today" (resources/js/pages/Database/Index.vue:50-62); whole-card tile links with aria-label (resources/js/pages/Database/Index.vue:70-91); aptitude order fixed server-side (app/Http/Controllers/CatalogController.php:555-585); search placeholder "Trainee or card name" with alias/JP coverage (resources/js/components/catalog/TraineeCatalog.vue:221, app/Http/Controllers/CatalogController.php:125-141); race sort + aria-sort + Calendar default (41bc48e8, tests/Feature/DatabaseRacesSortTest.php 6 cases); race name/position hierarchy, right-aligned tabular figures, labelled scrollable region, collapsed reference, win-prob refusal (resources/js/pages/Database/Races.vue:180-183,:310-315,:368,:400-490); scenario matrices-first order, release-vs-rework dates, partially-documented badge, ScenarioCaps parity (resources/js/pages/Database/Scenarios.vue:157-269, 0f8710a3, tests/Feature/ScenarioCapParityTest.php 6 cases); events limitation under the heading, single-sentence mechanics + drawer, provenance popover, category separation (resources/js/pages/Database/Events.vue:74,:134-141,:156,:183-195); shop column order, rating attribution, rules placement, SourceNote pattern (resources/js/pages/Database/ShopItems.vue:189-192,:204,:225-258,:290-294); sparks families-first, two-parent column, ★3 cap note, pink/blue separation, 4-step loop (resources/js/pages/Database/Sparks.vue:191-290); ledger kind-vs-tier separation, tier reference, read-only GET routes (resources/js/pages/Database/Sources.vue:108-145,:185,:206); Help topbar-only placement, trust vocabulary matching ProvenanceMark, accurate Undo wording, version from VERSION.md (resources/js/pages/Help.vue:66-70,:117-126,:208, resources/js/layouts/AppLayout.vue:581-589). Full verdict tables: three investigator reports, 74 claims.

Legacy spark target entry EXECUTED (exclude): docs/plan/legacy-spark-target-entry-2026-11-20.md records the Race category + spark-only Skill target landing on both Legacy entry surfaces with quoted gates (SparkTargetValidationTest 14 passed, Legacy filter 83 passed, typecheck/phpstan/pint clean, build verified in the emitted chunks). Its residue is the peer config/uma.php legacy_spark_targets block. It is entry-surface work, not the /database/sparks reference page; N6.13's missing Legacy/Veterans doors are unrelated.

## 4. Global constraints (bind every slice)

- Quality bar C-1..C-9 (docs/research-scratch/GOVERNANCE.md:1507-1515): all Pest pass + test per behavior change; PHPStan level 6 zero errors (--memory-limit=1G); Pint clean on explicit paths (never --dirty, KI-75); lore gate ran with a ruling per hit; fresh migrate+seed on scratch DB with working down(); catalog index <200ms; empty/loading/error states per ADR-0007; no new package without approval + audits clean; typecheck strict clean.
- The Floor: no new suppressions, no stub bodies, no deleted/skipped test without owner approval + reason, no engine write to is_manual=true, no fact without provenance, no matching on display strings, no fetch URL outside config('uma.sources'), no business logic in controllers, no inline $request->validate().
- Scope fence (PRD.md §6): no auth, no hosting, no breeding/inheritance computation, no EAV, no Excel, no event calendar, no legacy migration tooling, no MySQL/Postgres, no support-card collection/inventory, no race simulation/prediction, no dual storage, no image uploads. A redesigned summary is never a license to compute a cut value.
- Domain owners: CareerPosition/CareerCalendar (turns), ScenarioCaps/StatCeilings (ceilings), TrainerAdvisor (recommendation), config/scenarios.php (scenario names/panels, G-33: zero scenario literals in pages), HintDiscount (SP ladder), SupportCardEffects (level interpolation). Frontend never re-derives their arithmetic.
- HFW authority: SCREEN_SPEC.md:4190 and :4230 registers plus in-code HFW OVERRIDE comments (resources/js/pages/Career/TrainingDetail.vue:949, resources/js/pages/Career/EventDecision.vue:315, resources/js/pages/Career/InheritanceEvent.vue:527, resources/js/pages/Career/RaceDecision.vue:518, resources/js/pages/Career/RacePlanner.vue:379, resources/js/pages/Support/Builder.vue:422, resources/js/pages/Career/Cockpit.vue:1100). Reversing one is an owner decision (ND-1) with a SCREEN_SPEC §7 entry and inverted spec assertions, never a quiet layout edit. Database pages carry their own in-force overrides (resources/js/pages/Database/Sources.vue:151-165, resources/js/pages/Database/Events.vue:84-108, resources/js/pages/Database/ShopItems.vue:165-175, resources/js/pages/Database/Sparks.vue:108-151, resources/js/pages/Help.vue:17-41); the same reversal rule applies (ND-11..ND-16).
- Absence contract: unrecorded renders N/A with a disclosure channel (AbsenceValue.vue is the house primitive, 40 importers), never a default or zero. ProvenanceBadge states confirmed/calculated/estimated/unknown; §7-18 trust-label definitions remain an owner question.
- Copy: no em dashes in shipped copy; Global client vocabulary only (lang/en/uma.php); no invented client strings, figures or dates.
- Shared tree: name every path in commits; stage only your hunk; never sweep peer work; one Pest process at a time; browser suite session-private (PLAYWRIGHT_PORT + PLAYWRIGHT_SCRATCH_DB under .scratch-uma/<session>/); writing commands never backgrounded while peers work; scratch artifacts inside the repo only.
- Citation discipline in this file: backticked paths resolve at HEAD; untracked peer files and to-be-authored files are written unbackticked so the census does not count them as dead citations.

## 5. Owner decision register

Master §14 decisions, current state (derived from exec/sitrep/commits; labels inherited unless marked measured):

| Id | Decision | State at this pass | Blocks |
| --- | --- | --- | --- |
| D-1 | Sign eight ADRs | ANSWERED: dba86362 accepts eight, drafts ADR-0037 (measured commit) | nothing live |
| D-2 | Authorize Independent Training or retire | ANSWERED in effect: ADR-0032 accepted, door shipped; PRD line still owed (N4.4) | N4.4 copy choice |
| D-3 | Merge/rebase divergence + push | merge done fec658f8; PUSH still owed (measured 0 behind / 4+ ahead) | N0.2 |
| D-4 | Authorize npm run build in shared tree for browser baseline | unrecorded; builds happened via peers | N1.2 |
| D-5 | page-header tier assertion approval | executed but no approval artifact quoted; commit message must carry it | N0.1 |
| D-6 | Six probe retirements | ANSWERED: 12e97098 retired six under owner delegation (measured commit) | nothing |
| D-7 | Team-race slots: source package or terminal absence | OPEN | N5.7 |
| D-8 | Roster/preseason rounds: defer or schedule with ADR | OPEN | N5.7 |
| D-9 | data_sources backfill vs terminal gap | OPEN (133 rows / 1 source_key measured) | N5.6, N2.11 full half |
| D-10 | FQ-01..FQ-09 primitive-layer rulings | OPEN | N5.9 |
| D-11 | OQ-2 / OQ-3 / OQ-6 rulings | OPEN | N5.8, N5.11 |
| D-12 | Cut v3.0.0 after gates | OPEN | N4.10 |
| D-13 | Palette ruling | ANSWERED-IN-TREE: palettes/plates committed 8fd7e18e, DESIGN records authorization (sitrep); master gate row stale, erratum owed N4.7. Acceptance item 9 still needs the green gate quote at tag time | N4.7, N4.10 |

New decisions this plan surfaces (recommendation first; owner answers in writing):

| Id | Question | Options | Recommendation | Blocks |
| --- | --- | --- | --- | --- |
| ND-1 | Reverse the three HFW write-field placements the review flags (Training client totals below comparison, Events identification below options, Inheritance moment fields below savebar)? | (a) keep HFW placements, ship only non-conflicting improvements; (b) reverse all three with SCREEN_SPEC §7 entries + spec inversions; (c) reverse selectively | (a) for 3.0.0: the placements are recorded rulings with in-code authority comments, and the review is screenshot-based. Re-open post-tag with a live walk. | N3.16 |
| ND-2 | Race-candidate Confirm semantics under ADR-0037 (entity=source_key, no column): what does Confirm do for a race row? | (a) keep Confirm withheld (422), Reject-only until a race-promotion action exists; (b) authorize StoreRaceCatalogSlots promotion path | (a) for 3.0.0; (b) is a Phase-5 capability with its own slice | N2.1, N2.9 |
| ND-3 | KI-104 duplicate block: plan19 orders renumber to KI-169; register's 2026-11-10 correction (KNOWN-ISSUES.md:229,:243) rules no renumbering | (a) keep both blocks, merge subjects by dated note; (b) renumber | (a): AGENTS.md §11 never renumber; the register's own ruling wins over plan19 | N0.5 |
| ND-4 | Transferred career re-bank door: permanent disable or override copy? | (a) disabled door + reason, no override; (b) override with confirmation | (a): record-only library, no story asks for re-bank | N2.10 |
| ND-5 | Abandon-Independent-Training copy: native confirm() shipped without written ruling | (a) ratify confirm() + flash; (b) move to runs.confirm-delete-style page | (a) for 3.0.0 (behavior landed, test-pinned); (b) post-tag polish | N2.10 edge |
| ND-6 | Cockpit mandatory-race obligations: distinguish missed vs unrecorded-historical-gap? | (a) keep not_reached vocabulary, add disclosure that absence is not proof of failure; (b) derive missed from recorded position | (a): no stored fact separates them today; (b) is a derivation the schema does not support | N3.5 |
| ND-7 | Deck slot 5: review claims scenario-linked card; code type-matches intelligence only (app/Http/Controllers/Career/DeckSelectController.php:85,321-328) | (a) keep type-match, correct the review's claim; (b) scenario-link slot 5 via config | (a) unless a source states the slot rule; no fabricated client rule | N3.18 |
| ND-8 | Orphan components (9 files, 0 importers) and dead props: delete or keep? | (a) delete with owner approval per file; (b) keep as reference | (a) for the 9 orphans once a grep re-confirms zero importers; dead props deleted with their slices; wizard orphans FormDetail.vue/FormTabs.vue added by N3.29 | N3.17 |
| ND-9 | Reverse the wizard rulings the review flags (catalogue-card density, form-gated Continue, single-page pager, form+rarity lede, BuildTarget shape/summary/warn, Legacy summary+Edit/conditional manual/hidden grandparents/graph/picker filtering, 3x2 grid, borrowed-only slot 6, Change-link-per-section, checkbox beside Create)? | (a) keep rulings for 3.0.0, ship clean defects N3.19-N3.27 only; (b) reverse selectively after a live walk; (c) full wizard redesign | (a): each reversal contradicts a recorded ruling (R7-36/R7-68, Slice 10.1 at SCREEN_SPEC.md:1927, Phase D, HFW parity, spec pins) and the review is screenshot-based. ADR-0020 §3 record-only and ADR-0014 slot contract are architecture fences, not presentation rulings - they do not move even under (b) without an ADR erratum | N3.28 |
| ND-10 | Reverse the snapshot/veteran rulings the review flags (G1 bottom primary vs one-accent, G2 identity-panel merge, G3 disabled-door hint removal, G4 final-stat tiles, G5 portrait in PageHeader, G6 sticky summary/bar, G7 Energy Band persistence, G8 form widening/two-column)? | (a) keep rulings for 3.0.0, ship clean slices N3.30-N3.38; (b) reverse selectively with HFW-OVERRIDE records + spec/test travel; (c) full redesign | (a). G1 is a wireframe-vs-DESIGN.md conflict: DESIGN.md owns the visual system (AGENTS.md §2 precedence), so one-accent stands unless the owner rules otherwise. G7 is a persistence change, not presentation. G8 is a soft gate: widening may proceed with a recorded HFW-OVERRIDE entry (IA-4), never quietly | N3.40 |
| ND-11 | Hub grouping by question + consolidate the two Source Ledger doors (db-review §3.1(4)(5))? | (a) keep the flat nine-tile grid and both doors; (b) group/restructure with SCREEN_SPEC §7 entries | (a): HFW database.html:151,153,155 draws exactly this launcher ("Nine areas, one grid, no nesting"), the pick-border already differentiates the ledger tile, and specs pin 9 listitems | N6.18 |
| ND-12 | Demote the catalog card release badge below the name (db-review §3.2(1))? | (a) keep the filled pill; (b) demote with an HFW override record | (a): HFW catalog.html:225 annotation 6 rules "Release status is a word. 'Global' is a pill with text"; portrait+name hierarchy already ships | N6.18 |
| ND-13 | Color identities for spark families and event server badges (db-review §3.5(5), §3.7(2))? | (a) keep neutral chips pending the parked DESIGN.md §2 palette ruling; (b) rule the palette now | (a): SparkChip.vue:10-19 records the park; bg-sunken is the theme's own gated token, not hardcoded black | N6.18 |
| ND-14 | Per-row evidence tier in the ledger (db-review §3.8(3) Evidence column)? | (a) keep the page-level tier card; (b) assign per-row tiers (data model + ruling) | (a): recorded override resources/js/pages/Database/Sources.vue:151-154; no per-row tier exists in any payload; inventing tiers is a fabricated classification | N6.18, N6.5 tier half |
| ND-15 | Help rules quick-reference links (db-review §3.9(9))? | (a) keep text rows per the recorded inert-link rationale; (b) link only targets that now exist (/database/sparks etc.) with the rationale updated | (b) recommended but owner call: it reverses a recorded decision (resources/js/pages/Help.vue:188-197); some targets now exist, the rationale's premise is partly stale | N6.18 |
| ND-16 | Scenario column identities + column-max cap highlight + optional chart (db-review §3.4(2)(3))? | (a) keep outlier bolding and plain headers; (b) design ruling + config key for identities, override for max-highlight, chart as new scope | (a): shipped bold is the recorded wireframe reading ("Bold marks the outlier, not the maximum", resources/js/pages/Database/Scenarios.vue:100-113,190-192); G-33 bans scenario-name branching without a config key | N6.18 |

## 6. Execution protocol (every slice)

1. Re-verify the premise: git rev-parse HEAD, git status --porcelain -- <named paths>, read the defect site. If a peer landed it, record DONE with the commit sha and move on.
2. Failing test first (Pest feature or browser case per change class, AGENTS.md §9 matrix). Run it, quote the failure.
3. Smallest change that passes. No drive-by refactors. LF endings only (CRLF fails pint line_ending).
4. Focused run green, then related regression (Level 2). Full suite only at phase checkpoints (Level 3), one Pest process at a time, quiet-box pre-flight (KI-74: time one trivial SQLite read first).
5. Hand-off sequence: targeted tests -> php artisan test --compact (checkpoint) -> vendor/bin/pint --test --format agent <explicit paths> -> vendor/bin/phpstan analyse --no-progress --memory-limit=1G -> npm run typecheck -> npm run lint:ui -> composer lore + composer lore-code (ruling per hit) -> composer audit + npm audit --omit=dev when deps changed.
6. Browser work: PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser; lock dir .scratch-uma/<session>/playwright.lock; serve with composer serve:browser; rebuild assets first when specs read public/build.
7. Commits only on explicit owner instruction, explicit pathspecs, conventional subjects; D-5 approval quoted where required. Never stage peer hunks.
8. Docs move with behavior: SCREEN_SPEC state tables for screen changes, digest+ADR for schema, KNOWN-ISSUES four-part form for new defects, dated errata (never silent rewrites) for stale dated claims.
9. A slice is DONE only when its acceptance command output is quoted in the hand-off. Unrun gates are reported as not run. Documentation is never the deliverable of a behavior slice.

## 7. Phases and slices

Priority: P0 = blocks the tag; P1 = important before tag; P2 = post-tag or refinement. Dependencies use N-ids. "Source" names the owning prior document and id.

### Phase 0 - Land the tree and make gates trustworthy (blocks everything)

#### N0.1 - Commit the remaining worktree batches
- Priority P0 | Deps: none | Source: master S0.1/S0.3, plan19 pass completion | OWNER-GATED
- Objective: land the verified plan19 + four-screen work as reviewed commit series so every later baseline runs on a committed tree.
- Files: the 63 modified + code untracked paths (Form Requests, migration, seed JSON, tests, controllers, Vue pages, tools already partly committed at cd7cb8cb). Root draft images (bg-*.png etc.) stay untracked per standing instruction; agent.json, ki_entries.json are peer scratch, not swept.
- Actions: owner authorizes batch boundaries (data-integrity batch, review/skills/veterans/races batch, doc batch). Per batch: pint --test on explicit paths, focused tests, git add <pathspecs>, commit with D-5 approval quoted for the page-header batch. LF normalization check before each commit (11 files were CRLF-fixed in the prior session; re-check).
- Deliverable: commit series on master; git diff --cached empty between commits.
- Acceptance: each commit only its own paths; focused suites green per batch; no peer hunk lands (git show --stat per commit reviewed).
- Verify: git log --oneline; git status --porcelain (remaining rows named and attributed).
- Risk: shared-tree hunk interleaving (AGENTS.md §14); a peer commit may land the same paths first - re-check immediately before staging.

#### N0.2 - Push to origin/master
- Priority P0 | Deps: N0.1 | Source: master S0.5 push half, D-3 | OWNER-GATED
- Objective: divergence 0 0.
- Actions: owner pushes; record git rev-list --left-right --count origin/master...HEAD = 0 0.
- Acceptance: master acceptance item 1.
- Verify: the rev-list command output; npm ci --dry-run on fresh clone if S0.5 acceptance is invoked.
- Risk: peers keep committing; push window must be agreed.

#### N0.3 - Ui-lint card-in-card exit case
- Priority P0 | Deps: N0.1 | Source: plan19 S0.2 open half
- Objective: prove the fatal card-in-card rule can fail the gate (exit-code half landed cd7cb8cb; test half missing).
- Files: tests/Feature/UiLintDebtExitTest.php (tracked, 2 cases at :17,:44); fixture nested-Card file to be authored under tests/Fixtures/.
- Actions: add third case feeding a nested-Card fixture to tools/ui-framework-check.mjs, asserting exit 1; keep existing two cases.
- Acceptance: php artisan test --filter=UiLintDebtExit green with 3 cases; node tools/ui-framework-check.mjs on the fixture exits 1.
- Verify: quoted outputs of both.
- Risk: fixture path resolution in the test; use base_path().

#### N0.4 - Register auditor gate step
- Priority P0 | Deps: N0.5 | Source: plan19 S0.6 open half
- Objective: the parser fix (optional list marker, landed cd7cb8cb) is enforced by gates.sh, not just available.
- Files: tools/gates.sh (tracked, dirty), tools/audit_status_register.py (tracked).
- Actions: add step python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140 after N0.5 reconciles the register; prove non-vacuity by injecting a contradictory row in a scratch copy and watching exit 1.
- Acceptance: bash tools/gates.sh --fast lists the step and exits 0 on the reconciled register; injection exits 1.
- Verify: quoted gate output + injection proof.
- Risk: index-lines window drifts as entries are filed; pin the window to the reconciled register.

#### N0.5 - Register OPEN closeout and KI-104 duplicate ruling
- Priority P0 | Deps: N0.1 | Source: plan19 S0.7, master S2.6 drift | OWNER-AFFECTED (ND-3)
- Objective: one authoritative OPEN set; auditor reports CONTRADICTIONS: none.
- Files: KNOWN-ISSUES.md (tracked, clean at HEAD; index :8-119 vs bodies vs stale :139-224 block).
- Actions: reconcile per row using the index as current authority (register's own declaration); flip bodies that name ancestor commits (verify git merge-base --is-ancestor first); KI-104 dual subjects handled per ND-3 (keep both blocks, dated note, no renumber); remove or mark the stale 2026-11-10 reconciliation block.
- Acceptance: python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140 exits 0, NO INDEX ROW: none, CONTRADICTIONS: none; OPEN set matches master acceptance item 6 (no OPEN KI on shipped screens/API/schema).
- Verify: auditor output quoted; per-flip sha ancestry quoted.
- Risk: register has three status surfaces that drift; never average counts, name the method (index-row census vs body reading).

#### N0.6 - Citation ratchet proof on the committed tree
- Priority P0 | Deps: N0.1 | Source: master S1.3, plan19 S3.3 premise
- Objective: turn the worktree's 528 <= 708 reading into a committed-tree gate pass.
- Files: doc batch from N0.1; tests/Feature/DocCitationParityTest.php (tracked); tools/doc_census.py (tracked).
- Actions: after the doc batch commits, run python tools/doc_census.py (expect <= 708; worktree measured 528 this pass) and php artisan test --filter=DocCitationParity. Resolve or tombstone the most-cited dead targets the census names (docs/research-scratch/INDEX.md 41 hits, docs/audits/RECONCILIATION-REGISTER.md 12, docs/proposals/primitive-reconciliation.md 9, docs/OPEN-QUESTIONS.md 9, TASKS.md 9). Baseline stays 708 (C-8: never raise it to pass).
- Acceptance: both cases pass; census <= 708 on the committed tree; no baseline change.
- Verify: quoted census tail + test output.
- Risk: new untracked docs with backticked dead paths re-inflate the count; this plan's own citation discipline (§4) is the guard.

### Phase 1 - Suite truth at the landed HEAD

#### N1.1 - Quiet-box full Pest baseline
- Priority P0 | Deps: N0.1, N0.6 | Source: master S1.1 re-run at new HEAD
- Objective: one full-suite result at the landed HEAD with every red classified.
- Actions: KI-74 pre-flight (time one trivial SQLite read against the dev file); php artisan test --compact, output quoted; classify each red (regression / pre-existing / unrelated / infra / flaky) with focused reproduction. KI-162 (Dashboard/Races assertions, not currently reproducing) re-verified here, closed or re-filed. Inherited reds from the four-screen doc (race fan/reward seed inputs, career-delete redirect) expected to be resolved by S1.4/S1.16 work - confirm, do not assume.
- Acceptance: no unclassified red; result stamped with HEAD sha and wall clock.
- Verify: quoted suite tail + per-red classification table.
- Risk: contention voids the sweep (KI-74); subprocess-spawning tests fail under load and pass isolated = artifact, not regression.

#### N1.2 - Full browser baseline
- Priority P0 | Deps: N0.1, D-4 build authorization | Source: master S1.2 (never completed; two sweeps invalidated)
- Objective: full green-at-one-bundle browser baseline, 884 tests / 126 files population re-counted at the landed HEAD.
- Actions: npm run build (authorized, quiet box); session-private port + scratch DB + lock dir; full run; classify reds. Known pre-existing candidates to classify, not silently fix: tests/browser/career-cockpit.spec.ts:267/:636 (Races-in-this-run region gated on active run), unity-cup-panel.spec.ts helper stuck on wizard, career-cockpit correction-form-in-closed-disclosure cases (SCREEN_SPEC.md:4211-4228 records them as pre-existing with HEAD-revert proof). Each gets a ruling: assertion wrong or gate wrong.
- Acceptance: stable case count; every red filed with class + owning slice; wall clock recorded.
- Verify: quoted runner summary + classification table.
- Risk: leaked servers / peer DB deletion without session-private env (KI-73/KI-80); D-288/G-18 unwritable-path trap (zero assertions = nothing measured).

#### N1.3 - NFR-1 no-telemetry guard test
- Priority P1 | Deps: N0.1 | Source: plan19 S2.3 open edge
- Objective: pin the telemetry revert so a re-introduction fails a test, not a review.
- Files: tests/Feature/ApiV1 test file (tracked; name re-verified at dispatch).
- Actions: add case: Http::fake(); GET /api/v1/umamusume; Http::assertNothingSent(). Keep existing API cases.
- Acceptance: focused suite green; case fails if any outbound HTTP is wired into the API path.
- Verify: quoted focused run.
- Risk: none material; pure guard.

### Phase 2 - Data-integrity remainders (plan19 open halves)

#### N2.1 - Review queue entity labels
- Priority P1 | Deps: N0.1 | Source: plan19 S1.1 remainder; ADR-0037 (accepted)
- Objective: Index list names the entity kind the way Detail already does; Confirm stays withheld for non-trainee rows (422, never 500).
- Files: resources/js/pages/Review/Index.vue (tracked, dirty; prints source_key at :158, interface lacks entity), resources/js/types.ts (tracked), app/Http/Controllers/ReviewController.php (tracked; payload already carries entity per PipelineRunner diff).
- Actions: add entity to the Index payload type + a visible kind word per row (client vocabulary); no schema column (ADR-0037 rules entity=source_key). Race-Confirm promotion semantics stay parked under ND-2.
- Acceptance: php artisan test --compact --filter=Review green; Confirm on a race candidate answers 422 with named reason (ReviewEntityDispatchTest, untracked, 5 cases, lands with N0.1).
- Verify: quoted focused run.
- Risk: peer four-screen work owns this file; re-read before editing.

#### N2.2 - EnrichRaces README line
- Priority P2 | Deps: N0.1 | Source: plan19 S1.2 remainder
- Objective: document the command's is_manual skip + provenance behavior (code half landed: skippedManual counter, inline provenance stamping; deviation from data_sources rows documented - umamusume_id NOT NULL FK, ADR-0008).
- Files: README.md (tracked, dirty).
- Actions: one command-table row + one sentence on manual-row protection; no code change.
- Acceptance: grep enrich-races README.md >= 1 hit; doc matches shipped behavior.
- Verify: grep output.
- Risk: README is peer-dirty; stage only this hunk.

#### N2.3 - Offline seed consumer for race enrichment
- Priority P1 | Deps: N0.1 | Source: plan19 S1.4 remainder; functional item 7
- Objective: a fresh clone seeds race tracks, fan curves and rewards with no network. Seed bodies now exist (untracked: database/seeders/data/race-fans.json 26497B, race-rewards.json 40719B, racetracks_extended.json 5805B; SeedFileCompletenessTest.php untracked, 2 cases) but no offline consumer reads them: EnrichRaces reads storage snapshots then fetches; SourceDocumentSeeder skips the enrichment parser.
- Files: app/Console/Commands/EnrichRaces.php (tracked, dirty), database/seeders/SourceDocumentSeeder.php (tracked), config/uma.php (tracked, dirty; seed_file declarations :431,:445,:473).
- Actions: give the seeder (or a --from-seed path on the command) the committed bodies as input, same parser, provenance stamped; decide the three unreferenced files (gametora-characters.e9e9ee6d.json, race-tier-labels-2026-09-29.json, ura-races.json) - wire or tombstone with a dated note, owner call if wire.
- Acceptance: DB_DATABASE=.scratch-uma/<session>/acceptance.sqlite php artisan migrate:fresh --seed fills the three tables offline; php artisan test --filter=SeedFile green; functional item 7 demonstrable.
- Verify: quoted scratch-DB row counts + focused run.
- Risk: seeder changes touch shared seeding path; test on scratch only, never migrate:fresh the dev file.

#### N2.4 - Scheduler overlap guard
- Priority P1 | Deps: N0.1 | Source: plan19 S1.8 remainder
- Objective: independent-training:complete cannot overlap itself.
- Files: bootstrap/app.php (tracked, clean; everyMinute() at :53 lacks withoutOverlapping()).
- Actions: add ->withoutOverlapping() to the schedule entry; transaction/unique-index halves already landed (untracked migration 2026_11_19_120000_add_unique_index_to_independent_training_runs_table.php).
- Acceptance: php artisan test --filter=IndependentTraining green; schedule:list shows the guard.
- Verify: quoted focused run + schedule:list row.
- Risk: lock store is database (SESSION/CACHE database drivers); confirm cache lock table exists on scratch seed.

#### N2.5 - One hint-adjusted price in both planner panels
- Priority P1 | Deps: N0.1 | Source: plan19 S1.9 remainder
- Objective: purchasable and recommended sections quote the same hint-adjusted figure the server refuses by.
- Files: app/Http/Controllers/Career/SkillsPlannerController.php (tracked, clean; :136 hint_adjusted_cost=(int)sp_cost vs :267 HintDiscount::price).
- Actions: route :136 through HintDiscount::price with the recorded hint level (null hint = base price, absence preserved); budgetRefusal delta half already landed.
- Acceptance: php artisan test --filter=SkillsPlanner --filter=RunSkill green; functional item 13: Cost column, Remaining bar, recommendation ordering and server refusal quote one number for a Lv Max hint (price(130,5)=78).
- Verify: quoted focused runs.
- Risk: recommendation ordering tests may pin old prices; update expectations with the behavior change, never skip.

#### N2.6 - No-scenario obligation guard in CockpitController
- Priority P1 | Deps: N0.1 | Source: plan19 S1.12 remainder
- Objective: a run with no scenario shows no obligations anywhere on the cockpit (TeamPanelBuilder half landed; obligationSlots still unguarded).
- Files: app/Http/Controllers/Career/CockpitController.php (tracked, dirty; obligationSlots() :1072-1080 forScenario(scenarioKey()) unguarded, feeds raceStrip :307 and obligations loop :906).
- Actions: hasScenario() guard returning empty collection, mirroring app/Services/Career/TeamPanelBuilder.php:86-102.
- Acceptance: php artisan test --filter=Cockpit green incl the existing no-scenario case extended to the strip; functional item 12.
- Verify: quoted focused run.
- Risk: peer owns this file's dirty hunks; re-read, stage only this change.

#### N2.7 - Trackblazer scenario literals to config flags
- Priority P1 | Deps: N0.1 | Source: plan19 S1.15 remainder
- Objective: zero scenarioKey() === / !== literals in app/ (G-33 class).
- Files: app/Services/Career/TrackblazerPanelBuilder.php (tracked; :61,:577,:739,:758), app/Services/Career/TrackblazerVariant.php (tracked; :105), config/scenarios.php (tracked, dirty; capability-flag pattern already established by payout_family/happy_meek).
- Actions: add the needed capability flag(s) to the trackblazer entry; readers consume flags. Cockpit grand_concert typo half already landed (support_data_note prop).
- Acceptance: grep -rn "scenarioKey() ===" app/ and !== variants return empty (comment hits named); php artisan test --filter=Trackblazer --filter=ScenarioIsolation green.
- Verify: quoted grep + focused runs.
- Risk: flag naming must match config conventions; a fifth scenario stays zero-component-edit.

#### N2.8 - Review filters and bulk Reject
- Priority P1 | Deps: N0.1, N2.1 | Source: plan19 S2.2
- Objective: the 304-row backlog is disposable through the UI; no bulk Confirm ever.
- Files: routes/web.php (tracked; 4 review routes, no bulk), app/Http/Controllers/ReviewController.php (tracked, dirty), app/Http/Requests/ (new BulkRejectRequest to be authored), resources/js/pages/Review/Index.vue (tracked, dirty).
- Actions: entity + match_tier filter params on index; POST /review/bulk Reject with Form Request, transaction, per-row provenance; select-all UI with count feedback; filters echo in pagination.
- Acceptance: php artisan test --filter=Review green incl new bulk cases (reject-only, transaction rollback on failure, no promotion side effect); functional item 4.
- Verify: quoted focused run.
- Risk: bulk write is a new write boundary - Form Request mandatory; keep manual-only resolution compatible with the four-screen ruling (no auto-approve).

#### N2.9 - Dispose the 304 Pending race rows
- Priority P1 | Deps: N2.8, ND-2 | Source: plan19 S1.5 remainder | OWNER-AFFECTED
- Objective: review queue holds zero race rows (functional item 2).
- Actions: owner authorizes Reject-all for the gametora-races Pending set (they were filed by the pre-fix fetch path); execute via N2.8 bulk Reject on the dev DB, or a one-off action with the same guard; record before/after counts.
- Acceptance: SELECT count(*) FROM match_candidates WHERE status='Pending' AND source_key LIKE 'gametora-races%' = 0 (re-derive exact predicate); uma:fetch no longer files race candidates (FetchPipelineTest skip cases landed).
- Verify: quoted SELECT before/after + fetch test output.
- Risk: irreversible write to dev data - owner instruction required; backup first (uma:backup).

#### N2.10 - Transfer re-bank third state
- Priority P1 | Deps: N0.1, ND-4 | Source: plan19 S2.1
- Objective: a transferred career cannot be re-banked, and the screen says why (functional item 8).
- Files: app/Http/Controllers/Career/ResultController.php (tracked, clean; saveVeteranSection :439-453 available=finished only), resources/js/pages/Career/Result.vue (tracked), app/Models/TransferRequest.php (tracked; name re-verified at dispatch).
- Actions: third state from completed transfer request; disabled door with visible reason (AbsenceValue idiom); no override unless ND-4 rules otherwise.
- Acceptance: php artisan test --filter=CareerResult --filter=Transfer green incl new third-state case; browser case pins disabled door + reason.
- Verify: quoted focused runs.
- Risk: Result.vue spec pins Retired-state door absence (career-result.spec.ts cockpit-door count 0); do not disturb.

#### N2.11 - Sources screen union read (interim)
- Priority P2 | Deps: N0.1 | Source: plan19 S2.4; D-9 full backfill stays gated
- Objective: Sources screen stops saying "No fetch recorded" for 12 of 13 datasets by unioning the newest inline fetched_at per table.
- Files: app/Http/Controllers/DatabaseController.php (tracked, clean at 37320f35; sources() :470, the per-source max(fetched_at) group-by already landed as F-04; the union read adds the inline stamps). Coordinate with N6.1-N6.2, which own the page copy and layout.
- Actions: union read across inline provenance stamps (EnrichRaces-style) + data_sources; label each row's channel; full backfill remains N5.6 under D-9.
- Acceptance: php artisan test --filter=Database green; sources view shows a dated reading for every dataset that has one.
- Verify: quoted focused run.
- Risk: do not invent source keys or dates; absence stays absence where no stamp exists.

### Phase 3 - Career UX: workspace, wizard, snapshot, veteran detail (verified review gaps only)

Shared-primitive note: the review's §4C "standardize indicators" is largely built (AbsenceValue 40 importers, ProvenanceBadge 27, Note, Section, PageHeader 39, buttonStyles with primary/secondary/destructive hierarchy and 44px targets). No new primitive layer is authored here; slices adopt existing ones. The FQ primitive refactor stays post-tag (N5.9, D-10).

Wizard workstream (N3.19-N3.29): the 2026-11-20 new-career-wizard review was claim-tested at 37320f35 (64 claims: 24 EXISTS-already, 26 PARTIAL, 14 MISSING; 8 premise errors). Route map: /career/setup/scenario = resources/js/pages/Career/ScenarioSelect.vue; trainee = resources/js/pages/Career/TraineeSelect.vue; trainee/{id} = resources/js/pages/Career/TraineeProfile.vue; target = resources/js/pages/Career/BuildTarget.vue; legacy = resources/js/pages/Career/LegacySelect.vue; deck = resources/js/pages/Career/DeckSelect.vue (same page and controller as the run deck step, so N3.11/N3.20/N3.21 cover both surfaces); preflight = resources/js/pages/Career/Preflight.vue; shell = resources/js/layouts/SetupLayout.vue; draft = app/Services/Career/SetupDraft.php. The review's two P0 targets (Legacy, Deck) are mostly already shipped or ruled: whole-card scenario selection (fcf55782), modal slot-named picker, closed-by-default effect summaries, record-only projected start (ADR-0020 §3), three parent pathways, five Spark kinds, sole primary Create with acknowledgment gate. Its structural reversals (summary-plus-Edit parents, hidden grandparents, wizard ancestry graph, 3x2 grid, borrowed-only slot 6, form-gated Continue, Change-link-per-section, checkbox beside Create) contradict recorded rulings and spec pins; they are parked under ND-9, not queued as builds. Clean defects ship as N3.19-N3.27.

Snapshot/veteran workstream (N3.30-N3.40): the 2026-11-20 snapshot-and-veteran review was claim-tested at 37320f35 (41 claims: 6 EXISTS-already, 18 PARTIAL, 15 MISSING, 2 WRONG). Route map: /career/snapshot = resources/js/pages/Career/Snapshot/Entry.vue; /career/snapshot/setup = resources/js/pages/Career/Snapshot/Setup.vue; /career/snapshot/review = resources/js/pages/Career/Snapshot/Review.vue (controller app/Http/Controllers/Career/SnapshotController.php, store via app/Actions/CreateSnapshotRun.php); /veterans/{id} = resources/js/pages/Veterans/Show.vue (VeteranController::show). Data contract measured: three persistent field states known/unknown/not_provided (app/Enums/SnapshotFieldState.php, enum-validated in StoreSnapshotRequest, persisted on the CareerPosition payload); zero is a valid Known value; a TurnEntry is written only when all five core stats are Known; Energy is numeric-only on snapshot (Band exists on the turn contract, unreachable here); the session draft parks on successful review and Setup rehydrates values AND unknown toggles. Already shipped and excluded: review-before-create with acknowledgment gate, descendant rows linking the actual child run, Not-in-build collapsed disclosure (9c50f139), Factors refusal (ADR-0020 §3), container-query responsive stat grid. Eight recommendations collide with recorded rulings or test pins (ND-10): bottom primary vs DESIGN.md one-accent, six-section heading pin, audit-ruled disabled-door hint, stat-tile wireframe parity, PageHeader slot contract, shell sticky containment, Energy Band persistence, form widening. Snapshot surfaces are clean at HEAD; the peer four-screen rework owns Veterans/Index.vue (library), sharing only VeteranRow shape and AbsenceValue with the detail page.

Peer coordination: a peer session is adding a sixth Race Spark-target family end-to-end (dirty at 37320f35: LegacySelectController.php, StoreLegacySelectionRequest.php, LegacySelectionPayload.php, StartingCapProjection.php, resources/js/components/legacy/AncestorEditor.vue, resources/js/pages/Career/LegacySelect.vue, Legacy/Builder.vue, config/uma.php, SCREEN_SPEC note). Legacy-touching slices re-read these files first and never stage peer hunks. The peer TS union omits Race (resources/js/pages/Career/LegacySelect.vue:619, resources/js/components/legacy/AncestorEditor.vue:203) - reported to the owning session, not fixed here.

#### N3.1 - Navigation current-step defects
- Priority P1 | Deps: N0.1 | Source: review X1 extra findings (measured this pass)
- Objective: every career page lights its nav step; completed runs keep a reachable stepper.
- Files: resources/js/pages/Career/TeamEdit.vue:72 (current="team" matches no STEPS key; header comment :16 stale), resources/js/layouts/CareerLayout.vue (v-if isActiveRun hides stepper for completed/retired runs), resources/js/components/career/CareerSectionNav.vue.
- Actions: map Team to its contextual destination correctly (team is not a nine-step; pass the owning step or render contextual state explicitly); decide completed-run stepper visibility so Result's aria-current can light (review: Result step rarely shows). Spec: career-section-nav.spec.ts (7 tests) extended, not replaced.
- Acceptance: browser case per fix; existing nav cases stay green; link semantics preserved (no ARIA tabs).
- Verify: quoted session-private browser run.
- Risk: completed-run stepper change touches a spec-pinned absence (career-result.spec.ts pins cockpit-door count 0 for Retired); read the pins first.

#### N3.2 - Cockpit career status strip
- Priority P1 | Deps: N0.1 | Source: review C1 (PARTIAL)
- Objective: one strip answers position, last recorded turn, next entry, Energy, Mood, SP, next obligation.
- Files: resources/js/pages/Career/Cockpit.vue (next_turn_to_play prop shipped at :76, rendered nowhere), resources/js/components/career/CareerStatePanel.vue.
- Actions: render next_turn_to_play beside last-recorded turn ("Last recorded: Turn N / Next entry: Turn N+1" wording resolved server-side, no frontend arithmetic); fold existing Energy/Mood/SP readings into the strip; absent values keep AbsenceValue. No new prop derivation.
- Acceptance: feature test pins the strip's fields incl the null next-turn state; browser case at 390/1280; SCREEN_SPEC cockpit state table updated.
- Verify: quoted focused runs.
- Risk: head lede already unifies part of this; do not duplicate it - move, not copy.

#### N3.3 - Bounded cockpit race strip
- Priority P1 | Deps: N0.1 | Source: review C6 (MISSING); NFR-10 bounded payloads
- Objective: cockpit shows last-race summary + pending races + link, not the unbounded recorded-race list.
- Files: app/Services/Career/RaceStripBuilder.php:119 ($recorded = $run->raceEntries, no limit), resources/js/pages/Career/Cockpit.vue:1183-1217, resources/js/components/career/RunRaceStrip.vue.
- Actions: bound the recorded slice server-side (last N, N a named constant with rationale), keep this-turn + upcoming mandatory rows, add "Full record on Timeline" link; closed-run collapsed details behavior unchanged.
- Acceptance: feature test pins the bound (a run with N+5 recorded races renders N); existing race-strip states (SCREEN_SPEC :2147 table) all still render; query-count ceiling for cockpit not worsened (NFR-11).
- Verify: quoted focused runs + query-count test if present.
- Risk: career-cockpit.spec.ts asserts the Races-in-this-run region (already a classified pre-existing red, N1.2); coordinate the ruling.

#### N3.4 - Where-to-go grid dedupe
- Priority P2 | Deps: N3.2 | Source: review C4 (PARTIAL)
- Objective: stepper stays the backbone; contextual grid keeps only doors the stepper lacks.
- Files: resources/js/pages/Career/Cockpit.vue:1149-1173 (grid overlaps 6 of 9 destinations; 3 unique doors: Run settings, Scenario detail, Team setup).
- Actions: drop the 6 duplicated buttons, keep the 3 contextual doors + advisor-driven next action; no route changes.
- Acceptance: browser case: 9 destinations reachable via stepper, 3 contextual doors present, no duplicate hrefs in the grid; cockpit landmark names preserved (spec :636 class).
- Verify: quoted browser run.
- Risk: low; pure removal.

#### N3.5 - Obligation state vocabulary
- Priority P2 | Deps: ND-6 | Source: review C3 (PARTIAL)
- Objective: "not reached" on a Senior-year career reads as unrecorded history, not proof of failure.
- Files: resources/js/components/career/NextDecision.vue:189-202, app/Services/Career/TeamPanelBuilder.php:332.
- Actions: per ND-6(a): add a visible disclosure sentence where a mandatory obligation is not_reached past its window ("Not recorded. This tool cannot tell a missed race from an unrecorded one."); no new state derivation. If owner rules (b), scope a schema-backed slice instead.
- Acceptance: feature test pins the disclosure; five existing objective states unchanged.
- Verify: quoted focused run.
- Risk: deriving "missed" without stored facts violates the absence contract; do not.

#### N3.6 - Training absence dedupe and conditional preview
- Priority P1 | Deps: N0.1 | Source: review T1, T6 (MISSING)
- Objective: one shared explanation replaces five identical gainsAbsence sentences; preview readings appear when there are observations to enter.
- Files: resources/js/pages/Career/TrainingDetail.vue:798,807-810 (per-row ~180-char sentence), :1055-1092 (preview details always open), app/Http/Controllers/Career/TrainingDecisionController.php:516 (gainsAbsence payload).
- Actions: per-row label shrinks to "Facility level not recorded" (AbsenceValue compact); one Note near the discipline group carries the explanation; preview <details> opens only when the trainer has entered readings (or stays closed by default with a visible label); choose() focus target reviewed (currently the outcome heading). HFW placement of the totals block itself stays (ND-1).
- Acceptance: feature test pins one explanation + five compact labels; browser case: preview closed on empty form, open after entry; existing training states (SCREEN_SPEC :2245) unchanged; no computed gains introduced.
- Verify: quoted focused runs.
- Risk: career-training-detail.spec.ts (14 tests) pins current structure; read before editing.

#### N3.7 - Races selection clarity
- Priority P1 | Deps: N0.1 | Source: review R1, R5, R6, R4 side defect
- Objective: one obvious selection-to-action path; no stretched empty panel; aptitude axis visible; consecutive count honest.
- Files: resources/js/pages/Career/RaceDecision.vue (:305-306 grid lacks items-start, :377-382 row Enter posts immediately, :462-473 result disclosure, :502-514 savebar, :484 static "not recorded" literal, :239-270 aptitude pill), app/Http/Controllers/Career/RaceDecisionController.php (:383-389 consecutive-race payload).
- Actions: (a) add md:items-start (the fix resources/js/pages/Career/Cockpit.vue:939-941 already documents); (b) make row selection explicit: row click selects, one primary action acts on the selection - keep backend contracts (enter vs record result remain distinct posts); (c) visible inline axis marker beside the aptitude letter (title/sr-only text stays); (d) ship the recorded consecutive-race count as a prop where the run has one, keep "not recorded" + reason where it does not (the static literal is a side defect: it prints even for runs with recorded consecutive races). No probability computation (ADR-0016 hold stands).
- Acceptance: feature tests pin count prop both states; browser cases: selection visible, no stretch at 1280, axis marker visible; career-race-decision.spec.ts (13) + reflow case :302 stay green.
- Verify: quoted focused runs.
- Risk: three coexisting entry paths are spec-driven; invert assertions with recorded reason where behavior intentionally changes.

#### N3.8 - Skills learned/candidate separation and copy lag
- Priority P1 | Deps: N0.1 | Source: review S2 (PARTIAL), S1 copy lag
- Objective: learned skills read as a distinct state without opening a disclosure; pricing copy matches the server.
- Files: resources/js/pages/Career/SkillsPlanner.vue (:132 groups.flatMap merges four groups, :619 per-row pill, :698-699 closed group disclosure, :750-754 copy claims base-cost while controller prices hint-adjusted), app/Http/Controllers/Career/SkillsPlannerController.php:519-543 (groups).
- Actions: surface a compact learned/recorded section above the candidate table (groups already computed server-side); candidate table keeps status pills; fix :750-754 copy to state hint-adjusted pricing (coordinates with N2.5). Budget card + commit stay as shipped (HFW register :4200 pins the savebar status strip).
- Acceptance: feature test pins learned section content; browser case: learned visible without disclosure; career-skills-planner.spec.ts (18) green incl savebar count 1 pin.
- Verify: quoted focused runs.
- Risk: page-length goal must not delete the ladder/source sections - demote, keep reachable.

#### N3.9 - Events Alarm Clock dedupe
- Priority P2 | Deps: N0.1 | Source: review E6 (PARTIAL)
- Objective: the Alarm Clock explanation appears once, near the decision.
- Files: resources/js/pages/Career/EventDecision.vue:230,389,392-395 (three copies).
- Actions: keep one visible sentence at the relevant field; longer form in a labelled details; identification-field placement stays (HFW, ND-1).
- Acceptance: feature test pins single visible copy; career-event-decision.spec.ts (10) green.
- Verify: quoted focused run.
- Risk: low.

#### N3.10 - Inheritance legacy status and dead payload
- Priority P1 | Deps: N0.1 | Source: review I4 (MISSING), I5 dead computeds
- Objective: stored legacy selection visible as status near the top with a Legacy Lab door; dead predicted-sparks payload removed or rendered.
- Files: resources/js/pages/Career/InheritanceEvent.vue (:513-515 status only mid-page, :361 only back-to-cockpit anchor, :298-331 dead sortedPredictedSparks/parentFamilyRows), app/Http/Controllers/Career/InheritanceEventController.php:303-326 (star-roll payload shipped, never rendered).
- Actions: top status line ("Legacy selection recorded" / absence sentence) + Link to Legacy Lab (route exists on global rail, resources/js/components/career/CareerSectionNav.vue:51-53); decide dead payload: render the sourced star-roll odds in the existing predicted section (SCREEN_SPEC :2410 allows sourced probabilities) OR delete payload+computeds - recommend render, it is sourced data already computed; no inheritance computation added. Moment-fields placement stays (HFW, ND-1).
- Acceptance: feature test pins status both states + link; career-inheritance-event.spec.ts (9) green; Before/Change/After no-computation wording untouched.
- Verify: quoted focused runs.
- Risk: G-49 no-inheritance-prediction gate; star-roll odds are sourced reference, keep their citation.

#### N3.11 - Deck equip labels and action hierarchy
- Priority P1 | Deps: N0.1 | Source: review D2, D5 (PARTIAL); wizard review 3.6.4 (MISSING)
- Objective: every equip action names its destination; Replace and Remove are visually distinct.
- Files: resources/js/components/support/CardPicker.vue:278 (plain "Equip"), resources/js/components/support/SupportSlot.vue:259-277 (Replace/Remove identical styling), resources/js/pages/Career/DeckSelect.vue.
- Actions: button label "Equip to slot N" (slotLabel already in dialog heading); Remove demoted to secondary/destructive-ghost per buttonStyles, Replace stays primary-adjacent; fillingSlot dead prop (:72) removed. Remove gets a distinct destructive treatment plus a confirmation safeguard for a filled slot (clearSlot writes blank immediately, resources/js/pages/Career/DeckSelect.vue:362-367); controls group by purpose (config: level/ownership; selection: Replace; consequential: Remove). SupportSlot is shared: this slice covers both /career/setup/deck and the run deck step. Six-slot contract, modal catalogue, ownership distinction untouched.
- Acceptance: browser case: equip button names slot; tests/browser/career-deck-select.spec.ts (20) + support-deck.spec.ts green (13/13 live-control pins).
- Verify: quoted browser run.
- Risk: specs pin accessible names; update with the label change, quoted.

#### N3.12 - Timeline pager, fallback marker, phase bodies
- Priority P2 | Deps: N0.1 | Source: review L1, L2, L3
- Objective: no pager for one page; fallback turn associations marked; phase grouping carries content or goes.
- Files: resources/js/pages/Career/Timeline.vue:99,299-300 (inline pager unconditional), :312-316 (phase details bodies empty), app/Http/Controllers/Career/TimelineController.php:187-204 (fallback races with null turn_entry_id get $latestTurn, unmarked; events on unlogged turns dropped), resources/js/components/ui/Pagination.vue:51,72,93 (same unconditional pattern, 8 importers).
- Actions: gate pager on pages > 1 in BOTH Timeline inline pager and ui/Pagination.vue (fix the class, not the site); add a visible "associated turn" marker on fallback rows (server flag); include events on unlogged turns with their own absence marker OR document the exclusion on screen - recommend include with marker; phase details bodies filled with the grouped entries or the section removed (owner-lite: recommend fill, it is the review's temporal-grouping ask).
- Acceptance: feature tests pin fallback flag + unlogged-turn events; browser cases: single page shows no pager, multi page does; career-timeline.spec.ts (12) green; catalogue pagination specs unaffected (8 importers regression).
- Verify: quoted focused runs.
- Risk: Pagination change reaches 8 catalog surfaces - Level 2 regression mandatory.

#### N3.13 - Settings dirty indicators and Wit note
- Priority P2 | Deps: N0.1 | Source: review G3-sub, G4
- Objective: every form shows unsaved state; Wit ceiling absence explained on screen.
- Files: resources/js/pages/Career/RunSettings.vue (:219,:240,:261 saved-state exists, :344 isDirty only on identityForm; ceilings fieldset :311).
- Actions: isDirty indicator on statusForm/scenarioForm/periodForm (pattern already in identityForm); one sentence beside the ceilings fieldset: Wit's scenario bound is fixed (ADR-0025), no entered ceiling exists for it. Four useForm boundaries unchanged.
- Acceptance: run-settings.spec.ts extended (dirty indicator per form); RunSettingsTest green; blank-stays-null behavior untouched.
- Verify: quoted focused runs.
- Risk: low.

#### N3.14 - Team grouping, proxy help, dirty state
- Priority P2 | Deps: N0.1 | Source: review M1, M2, M5
- Objective: identity / performance / counters / grades read as groups; Spirit proxy explained at the input; unsaved state visible.
- Files: resources/js/components/career/UnityCupEditor.vue (:131 single flat grid, :204-208 proxy label comment-only rationale, :78 one useForm, :269 saved confirmation; no isDirty), resources/js/pages/Career/TeamEdit.vue.
- Actions: fieldsets for identity (name/motto), performance (placement/preseason/rank), counters (Unity Trainings/Spirit/Extreme Bursts), grades (existing fieldset kept); visible help text for the manual Spirit proxy (transcription, not a client gauge); isDirty + saved confirmation pattern from RunSettings. Blank-posts-null rule and single Save unchanged.
- Acceptance: team-edit.spec.ts extended; TeamEditTest + RunTeamStateTest green; stat-grade order Speed/Stamina/Power/Guts/Wit preserved.
- Verify: quoted focused runs.
- Risk: Team Rank storage is ADR-0034 territory; editor writes through the existing validator, no new path.

#### N3.15 - Scenario detail overview, qualifiers, team door
- Priority P1 | Deps: N0.1 | Source: review N1, N4, N5
- Objective: at-a-glance recorded progress first; Estimated beside each burst-band figure; Team Edit reachable from the team section.
- Files: resources/js/pages/Career/ScenarioDetail.vue (81 lines, shell), resources/js/components/scenario/ScenarioPanel.vue, resources/js/components/scenario/cards/BurstBandsCard.vue:42-44 (card-level qualifier), app/Http/Controllers/Career/CockpitController.php:223 (scenario action; ships no team_url).
- Actions: overview strip from already-shipped props (team rank, Unity Trainings, bursts, next obligation, goal progress) - no new computation; per-row Estimated badge where the band table is estimated (publisher-conflict note stays); team_url prop + contextual Team Edit link in the team section. HFW-ruled 12-card bento order stays (review N2 refuted); goals prominence improved within the ruled grid, not by reordering it.
- Acceptance: ScenarioDetailTest + ScenarioPanelTest + ScenarioIsolationTest green; browser case: overview present, qualifier per figure, team link resolves; unity-cup-panel.spec.ts helper defect classified under N1.2 first.
- Verify: quoted focused runs.
- Risk: scenario isolation (G-33) - overview reads flags, never scenario names; other scenarios' panels unaffected.

#### N3.16 - HFW write-field placement reversals
- Priority P2 | Deps: ND-1 ruling | Source: review T2, E1, I2 | OWNER-GATED
- Objective: only if owner reverses the recorded rulings: move Training client totals above comparison, Events identification above options, Inheritance moment fields above the savebar.
- Files: resources/js/pages/Career/TrainingDetail.vue:949-1053, resources/js/pages/Career/EventDecision.vue:315-337, resources/js/pages/Career/InheritanceEvent.vue:527-548; SCREEN_SPEC.md §7 new entries; the specs pinning current placement.
- Actions: per reversal: SCREEN_SPEC §7 dated entry naming the superseded override, spec assertions inverted with recorded reason (AGENTS.md §9 cut-reversal rule), layout move, focused + browser runs. Until ruled: NO edit.
- Acceptance: owner ruling quoted; three screens' state tables agree with views; all prior specs green or inverted with reason.
- Verify: quoted runs + ruling reference.
- Risk: reversing a signed wireframe ruling without the owner is a scope violation; this slice exists to make the gate explicit, not to pre-authorize the work.

#### N3.17 - Orphan and dead-code disposition
- Priority P2 | Deps: N0.1, ND-8 | Source: reconciler extra findings | OWNER-GATED (deletion)
- Objective: remove components no surface imports and props no template reads.
- Files (0 importers measured): resources/js/components/career/StatSummary.vue, TrainingCard.vue, EventCard.vue, ActionGrid.vue, CockpitIdentityCard.vue, ChoicesSoFar.vue; resources/js/components/ui/StickyFooter.vue, ui/StatusBadge.vue; resources/js/components/CapsuleHeader.vue. Dead props: EventDecision state/statValue/statTitle/metaTitle, RaceDecision modes/switchMode, SupportSlot fillingSlot (handled in N3.11), InheritanceEvent props.empty.
- Actions: re-grep importers at dispatch (peer work may adopt one); owner approves deletion list; git rm the approved files; dead props deleted with their owning slices. ResourceStrip stays if CockpitIdentityCard's deletion orphans it - decide in the same ruling.
- Acceptance: npm run typecheck + build green; no spec references a deleted file (grep first); deletion commit names owner approval.
- Verify: quoted grep (0 importers) + typecheck/build.
- Risk: a peer slice may be mid-adoption of an orphan; re-check immediately before deletion.

#### N3.18 - Deck slot-5 scenario-link question
- Priority P2 | Deps: ND-7 | Source: review D3 (PARTIAL/WRONG)
- Objective: settle whether slot 5 is scenario-linked or intelligence type-matched; correct whichever record is wrong.
- Files: app/Http/Controllers/Career/DeckSelectController.php:85,321-328 (5 => intelligence, 6 => friend), config/scenarios.php scenario_links.
- Actions: if ND-7(a): record the review claim as refuted in the Phase-3 hand-off, no code. If (b): config-driven slot-5 link + feature test + SCREEN_SPEC deck state update.
- Acceptance: ruling quoted; code and doc agree.
- Verify: focused deck tests if (b).
- Risk: inventing a slot rule the client does not state violates the lore/copy fence.

#### N3.19 - Build target percent precision
- Priority P1 | Deps: N0.1 | Source: wizard review 3.4.4 (MISSING, verified live this pass)
- Objective: an incomplete target must not print as 100% of the ceiling.
- Files: resources/js/pages/Career/BuildTarget.vue:241 (percent() = Math.min(100, Math.max(0, Math.round(value/ceiling*100)))), :493 (render).
- Actions: below equality print one decimal (99.9%) or a "Nearly at ceiling" word; exact 100 only when value === ceiling; stored numbers unchanged; read tests/browser/career-build-target.spec.ts percent assertions first and update with recorded reason where pinned.
- Acceptance: case pins 1398/1400 not 100%; equality case still 100%; no storage or validator change.
- Verify: quoted focused feature + browser run.
- Risk: spec-pinned rounded text; invert with reason, never skip.

#### N3.20 - Wizard deck per-slot error path
- Priority P1 | Deps: N0.1 | Source: wizard review 3.6.9 (dead code verified live)
- Objective: per-field refusals (duplicate card, level above rarity ceiling, ownership required) render near the slot, not only as the whole-deck banner.
- Files: resources/js/pages/Career/DeckSelect.vue:165 (fieldErrors computed, never rendered), :228 (errorLabel, never called); resources/js/components/support/SupportSlot.vue :error prop (run builder passes it; wizard step does not).
- Actions: pass :error per slot from fieldErrors, render errorLabel text beside the slot; validator stays authoritative.
- Acceptance: feature test pins per-slot refusal rendering for duplicate + level-ceiling cases; tests/browser/career-deck-select.spec.ts (20) green on both deck surfaces.
- Verify: quoted focused runs.
- Risk: shared SupportSlot; regress wizard and run deck together.

#### N3.21 - Deck summary current-tier highlight
- Priority P2 | Deps: N0.1 | Source: wizard review 3.6.7 (PARTIAL)
- Objective: the upgrade ladder marks the recorded level's tier as current; other tiers read as reference, not granted benefits.
- Files: resources/js/components/support/DeckSummary.vue:145-149 (impliedTierIndex defined, unused), :267-296 (summary row), :337-373 (ladder), :390-396 (tier filter defaults All tiers).
- Actions: consume impliedTierIndex to mark the current tier; optionally auto-set the filter to the current tier with the control visible; no new effect arithmetic (SupportCardEffects owns values).
- Acceptance: tests/browser/career-deck-summary.spec.ts (10) green + new case pins the marker at a recorded level; unrecorded level renders absence, no marker.
- Verify: quoted focused runs.
- Risk: shared with run deck summary; both surfaces regress.

#### N3.22 - Preflight optional-Legacy state
- Priority P1 | Deps: N0.1 | Source: wizard review 3.7.2 (PARTIAL, contract mismatch)
- Objective: the checks table matches the optional contract: zero parents reads Skipped, not Missing; Pass means the actual requirement is satisfied.
- Files: resources/js/pages/Career/Preflight.vue:353-359,601-618,791-794 (Legacy Pass requires legacyNamedCount>=2; only target gets Skipped); app/Http/Requests/Career/StartCareerRequest.php:95 (parents nullable).
- Actions: extend Skipped to Legacy at zero parents; keep Warning/Missing for genuinely invalid partial states; SCREEN_SPEC preflight state table moves with the change; read tests/browser/career-preflight.spec.ts (14) pins first.
- Acceptance: tests/Feature/CareerPreflightTest.php (64) green + new case: zero parents Skipped and creation allowed; both parents Pass.
- Verify: quoted focused runs.
- Risk: a pin may encode the defect; invert with recorded reason, align to the nullable contract.

#### N3.23 - Preflight limitations list
- Priority P2 | Deps: N0.1 | Source: wizard review 3.7.7 (MISSING)
- Objective: not-built disclosures group by consequence; the heading stops using developer language.
- Files: resources/js/pages/Career/Preflight.vue:1017-1037 (flat 3-item list; heading contradicts its own comment at :1017-1019).
- Actions: separate run-affecting limitations (facility counts shape early training advice) from project info; rename heading in client vocabulary; keep every disclosure.
- Acceptance: feature test pins grouping and all three items present; lore gate no new hit.
- Verify: quoted focused run.
- Risk: low; copy plus grouping.

#### N3.24 - Profile selected-form pill and scenario emphasis
- Priority P1 | Deps: N0.1 | Source: wizard review 3.3.2, 3.3.7 (verified live)
- Objective: the draft's chosen form is marked on the profile; the chosen scenario stands out from the other available ones.
- Files: resources/js/pages/Career/TraineeProfile.vue:436 (Selected pill keyed card.is_debut_form, not the draft form), :213 (primaryForm fallback), :451-470 (uniform scenario pills); app/Http/Controllers/Career/TraineeProfileController.php:170-176 (selectedCardId, scenarioLabel, scenarioPending already shipped; the last two undeclared in Vue).
- Actions: key the pill to the draft selectedCardId; declare scenarioLabel/scenarioPending props and emphasize the chosen scenario; form-grain framing only - a trainee-level rarity lede stays gated under ND-9 (ADR-0008, HFW copy override at resources/js/pages/Career/TraineeProfile.vue:18-21).
- Acceptance: tests/browser/career-profile.spec.ts (8) green + new case: a picked non-debut form is marked, debut unmarked when not chosen; scenario emphasis present.
- Verify: quoted focused runs.
- Risk: the debut pill may be spec-pinned; read pins first.

#### N3.25 - Trainee Selected headline form
- Priority P1 | Deps: N0.1 | Source: wizard review 3.2.2 (PARTIAL, defect half)
- Objective: the Selected summary never names an unchosen form.
- Files: resources/js/pages/Career/TraineeSelect.vue:416-420 (headlineForm falls back to forms[0]), :645-693 (Selected card with form picker).
- Actions: with no form picked, the headline reads a "Choose a form" state; the picker gets the strongest actionable emphasis inside the panel; Continue is NOT gated (two-destinations ruling stands, ND-9); single-form auto-select path preserved.
- Acceptance: tests/browser/career-trainee-select.spec.ts (20) green + new case pins the unchosen headline state.
- Verify: quoted focused runs.
- Risk: form nullable by design (StoreTraineeRequest); do not turn the hint into a requirement.

#### N3.26 - Profile growth row compaction
- Priority P2 | Deps: N0.1 | Source: wizard review 3.3.4 (MISSING)
- Objective: five growth values read as one compact row, not five stacked full-width boxes.
- Files: resources/js/pages/Career/TraineeProfile.vue:334-339 (.tile boxes); resources/css/app.css:1602-1613 (.tiles has no grid-template-columns), :73 (--grid-template-columns-tiles token defined, never consumed).
- Actions: consume the existing token (or the smallest grid rule) so tiles form a row at wide widths and stack narrow; values stay recorded form growth; N/A stays absent; no new arbitrary value (budget 209).
- Acceptance: browser case at 390/1280 pins row/stack; tests/browser/career-profile.spec.ts green; npm run lint:ui no new debt.
- Verify: quoted focused runs.
- Risk: profile growth wireframe parity unverified - check HFW notes at dispatch; if pinned, fold into ND-9 instead.

#### N3.27 - Draft upstream-change messaging
- Priority P2 | Deps: N0.1 | Source: wizard review 2.4 (PARTIAL)
- Objective: an upstream change that affects downstream choices names the affected sections instead of silently keeping stale values.
- Files: app/Services/Career/SetupDraft.php:100-110 (merge semantics preserved), :411-424 (planningRun re-clamps caps at render); app/Http/Controllers/Career/ScenarioSelectController.php:90-102; resources/js/layouts/SetupLayout.vue:34-43 (choices strip prop accepted, unrendered).
- Actions: after a scenario change, flag the stale target/caps sections and surface a notice naming the affected steps; no silent discard, no draft-contract change (D2 wizard contract).
- Acceptance: feature test pins the notice after a scenario switch; backward navigation still preserves answers (wizard suites green).
- Verify: quoted focused runs.
- Risk: messaging only; reset() semantics stay touched only at Preflight commit.

#### N3.28 - Wizard ruling package
- Priority P2 | Deps: ND-9 ruling | Source: wizard review structural recommendations | OWNER-GATED
- Objective: only if the owner reverses recorded rulings: catalogue-card simplification (3.2.1), form-gated Continue (3.2.3), single-page pager hiding (3.2.4), form+rarity lede (3.3.1), BuildTarget summary restore / Distance regroup / warn demotion (3.4.1/3.4.6/3.4.9/3.4.2), Legacy summary-plus-Edit / conditional manual / hidden grandparents / wizard graph / picker filtering (3.5.2-3.5.5/3.5.9), 3x2 grid (3.6.1/3.6.10), borrowed-only slot 6 (3.6.2), Change-link-per-section (3.7.1), checkbox beside Create (3.7.4).
- Files: the seven wizard pages, resources/js/layouts/SetupLayout.vue, resources/js/components/legacy/AncestorEditor.vue, resources/js/components/support/DeckSummary.vue; SCREEN_SPEC.md §7 entries; the specs pinning each ruling (tests/browser/career-trainee-select.spec.ts:114-120,147-150; tests/browser/career-build-target.spec.ts:292-336,396-405; tests/browser/wizard-actions.spec.ts head-band contract; tests/browser/career-preflight.spec.ts Commit-card 25-descendant/900-char ceiling).
- Actions: per reversal: dated SCREEN_SPEC entry naming the superseded ruling (R7-36/R7-68 open-by-default, Slice 10.1 owner ruling at SCREEN_SPEC.md:1927, Phase D "Two slots, not a graph", HFW overrides, ADR-0014 correction 1), spec inversions with recorded reason, focused + browser runs. ADR-0020 §3 record-only is an architecture fence and does not move with a presentation ruling. Until ruled: NO edit.
- Acceptance: ruling quoted; state tables agree with views; prior specs green or inverted with reason.
- Verify: quoted runs + ruling reference.
- Risk: the review's highest-priority items are exactly the ruled ones; executing them without the ruling is a scope violation.

#### N3.29 - Wizard doc-truth and stale artifacts
- Priority P2 | Deps: N0.1, wizard slice landings | Source: claim-test stale-artifact list
- Objective: wizard docs, comments and dead code match the tree.
- Files: SCREEN_SPEC.md:1679-1684 (SCR-CAR-005 distance/summary/bar drift); resources/js/pages/Career/TraineeProfile.vue N/A docblock (stale post-ADR-0030); resources/js/pages/Career/BuildTarget.vue:27-29,:547-549 (heading names a cut row); resources/js/pages/Career/ScenarioSelect.vue:323-328 (stale fact-grid comment); tests/Feature/CareerTraineeSelectTest.php:628 (name describes retired DataTable); orphan FormDetail.vue/FormTabs.vue (0 imports, add to ND-8 list); resources/js/pages/Career/LegacySelect.vue:717-731 (bare '-' cells vs N/A+title rule, spec-pinned table).
- Actions: dated errata for spec drift; stale comments/docblocks removed with their slices; test name corrected (no behavior change); orphans routed to ND-8; the bare '-' deviation flagged to the Lore Guardian/owner (copy rule vs spec pin - a ruling, not a quiet edit).
- Acceptance: doc diff matches tree; npm run typecheck green; Guardian ruling recorded for the '-' cells.
- Verify: quoted grep + typecheck.
- Risk: peer Race Spark work owns the dirty resources/js/pages/Career/LegacySelect.vue/AncestorEditor.vue hunks (TS union omits Race at resources/js/pages/Career/LegacySelect.vue:619, resources/js/components/legacy/AncestorEditor.vue:203); report to the owning session, never fix or stage their work.

#### N3.30 - Snapshot entry onboarding
- Priority P1 | Deps: N0.1 | Source: snapshot review E1/E2/E3/E4/E6/E7 (E5 excluded, ND-10 G3)
- Objective: a first-time Trainer understands what Snapshot records, what it will ask, and how it differs from New Career.
- Files: resources/js/pages/Career/Snapshot/Entry.vue:19-20 (title), :45 (max-w-3xl card, no mx-auto), :49-58 (purpose copy), :61-66 (button), :26-39 (stepper); resources/js/pages/Career/Snapshot/Setup.vue:181-185 and resources/js/pages/Career/Snapshot/Review.vue:39-43 (stepper labels); resources/js/pages/Dashboard.vue:495,777 (door labels share the wording).
- Actions: rename title to "Record an existing career" and button to "Start recording" (travel with both dashboard door labels); center the card (mx-auto; 768px width already inside the review's own 760-900px range); add an itemized readiness list (trainee, scenario, position, any visible stats; not every value must be known); relabel the stepper consistently across all three screens (Introduction / Record current state / Review and create), keeping Setup's readout non-clickable; optional "Which creation method should I use?" disclosure. The disabled-door hint stays (audit AUD-20261102-007 F-01, text-pinned).
- Acceptance: tests/Feature/SnapshotEntryTest.php (3) + tests/browser/career-snapshot.spec.ts (15) green with copy pins updated by reason; hint pin :86 unchanged; door labels consistent.
- Verify: quoted focused runs.
- Risk: text pins across three screens; update together, never partially.

#### N3.31 - Snapshot setup trainee combobox
- Priority P1 | Deps: N0.1 | Source: snapshot review S2 (MISSING)
- Objective: searchable, keyboard-operable trainee selection reusing the shared component, not a second list.
- Files: resources/js/pages/Career/Snapshot/Setup.vue:202-209 (BaseSelect dropdown); resources/js/components/TraineeCombobox.vue (exists, one importer: resources/js/pages/Runs/Create.vue); app/Http/Controllers/Career/SnapshotController.php:69 (full roster prop).
- Actions: swap in TraineeCombobox; stored identity stays umamusume_id (catalogue trainee); NO costume/form selector - the snapshot contract has none, and adding one would change stored identity.
- Acceptance: tests/Feature/SnapshotTraineeIdentityTest.php (7) green; combobox keyboard case; tests/browser/career-snapshot.spec.ts trainee cases updated.
- Verify: quoted focused runs.
- Risk: combobox payload shape vs roster prop; adapt at the boundary, not the component.

#### N3.32 - Snapshot setup position readout
- Priority P1 | Deps: N0.1 | Source: snapshot review S3 (PARTIAL)
- Objective: the defining Year/Month/Half selection shows a readable position summary on Setup, derived by the existing owners.
- Files: resources/js/pages/Career/Snapshot/Setup.vue:239-274 (selects, no readout); app/Http/Controllers/Career/SnapshotController.php:161-181 (position_label via CareerCalendar::turnIndexFor + CareerPositionLabel::forTurn, shipped to Review only); resources/js/pages/Career/Snapshot/Review.vue:128-130 (renders it).
- Actions: ship the same position_label to Setup and render it beneath the controls; no frontend turn formula (CareerPosition/CareerCalendar own derivation).
- Acceptance: feature test pins Setup readout equals Review label for the same selection; concurrent position errors stay (tests/browser/career-snapshot.spec.ts:162).
- Verify: quoted focused runs.
- Risk: null position (incomplete selection) renders absence, never a guessed turn.

#### N3.33 - Snapshot unknown-value ergonomics
- Priority P1 | Deps: N0.1 | Source: snapshot review S5/S7 (MISSING)
- Objective: compact per-field unknown controls plus a safe bulk action, without changing the three-state contract.
- Files: resources/js/pages/Career/Snapshot/Setup.vue:303-310,325-332 (8 BaseCheckbox rows), :101-107 (toggleUnknown clears the typed value on tick); app/Enums/SnapshotFieldState.php (known/unknown/not_provided).
- Actions: compact the unknown affordance per field (accessible names updated, tests/browser/career-snapshot.spec.ts:150-160 pins travel); add "Mark unfilled values as unknown" applying ONLY to unfilled fields, preserving every entered number, with a visible affected-fields summary. Erase-on-tick semantics stay as shipped (unknown carries no value); a non-destructive toggle is a semantics change parked under ND-10.
- Acceptance: feature tests pin bulk safety (entered values untouched, known zeros untouched) and state transitions; no zero-filling anywhere.
- Verify: quoted focused runs.
- Risk: the review warns against silent erasure; the shipped erasure is deliberate and visible - keep it deliberate, document it in the field help.

#### N3.34 - Snapshot Energy limitation copy
- Priority P2 | Deps: N0.1 | Source: snapshot review S8 copy arm (Band exposure gated ND-10 G7)
- Objective: the form states its numeric-only Energy contract instead of implying band support.
- Files: resources/js/pages/Career/Snapshot/Setup.vue (Energy field); app/Http/Requests/StoreSnapshotRequest.php:63 (nullable int 0-100); app/Actions/CreateSnapshotRun.php:131-135 (writes Exact or Unknown, never Band).
- Actions: one visible sentence: Energy is recorded as the exact number read from the client, or left unknown; a color band cannot be saved here. No contract change.
- Acceptance: feature test pins the sentence; snapshot energy validation unchanged.
- Verify: quoted focused run.
- Risk: low; copy only.

#### N3.35 - Snapshot validation summary and refusal persistence
- Priority P1 | Deps: N0.1 | Source: snapshot review S13/S14 residual (PARTIAL)
- Objective: errors summarize with links to fields, and a refused submission never loses entered values or unknown states.
- Files: resources/js/pages/Career/Snapshot/Setup.vue:142-148,196-346 (per-field errors exist, no summary); app/Http/Controllers/Career/SnapshotController.php:113-117 (draft parked only on SUCCESSFUL review; redirect remount rehydrates only the parked draft, so first-pass refusal loses entries).
- Actions: add a linked error summary (GOV.UK shape; same pattern N5.1 will standardize post-tag - no new primitive); fix refusal persistence: prefer Inertia 422 back-render preserving useForm state; if the redirect flow is contractual, park the draft on refusal too - verify the contract at dispatch and record the choice; reload-before-first-review autosave is a separate decision, not auto-built (review itself says inspect first).
- Acceptance: feature test: refused submit preserves values AND field_states; summary anchors resolve to fields; tests/Feature/SnapshotReviewTest.php (11) green; tests/browser/career-snapshot.spec.ts concurrent-errors pin :162 stays.
- Verify: quoted focused runs.
- Risk: persistence semantics are recorded; a parking change needs the contract check, never a silent autosave.

#### N3.36 - Snapshot recorded-state summary
- Priority P2 | Deps: N3.32 | Source: snapshot review S9 (MISSING; two-column variant gated ND-10 G8)
- Objective: a live summary of what has been recorded, driven by actual form state.
- Files: resources/js/pages/Career/Snapshot/Setup.vue (no summary region today; resources/js/pages/Career/Snapshot/Review.vue:132-144 is the only read-back).
- Actions: compact "Current snapshot" disclosure within the existing measure: trainee, scenario, position (N3.32 label), count of supplied numeric values, unknown/not-provided counts. No base-stat or scenario-default guessing. Full side-column summary ships only if ND-10 rules the widening.
- Acceptance: browser case pins summary reflects live form state incl unknown toggles; absence never renders as zero.
- Verify: quoted browser run.
- Risk: low; read-only projection of form state.

#### N3.37 - Veteran detail absence and action copy
- Priority P1 | Deps: N0.1 | Source: snapshot review V2/V5/V10/V11/V12
- Objective: house absence primitive, explicit lifecycle labels, and self-explanatory actions on the detail page.
- Files: resources/js/pages/Veterans/Show.vue:174-191 (inline N/A reasons), :221,230 (title-only N/A), :164-166 (Status row), :105-110 (Banked shell pill), :91-98 (transfer confirm dialog), :115-117 (Open the Cockpit), :323-345 (Provenance card); app/Http/Controllers/VeteranController.php:205,209 (runUrl, sourcesUrl).
- Actions: adopt AbsenceValue (house direction, 40 importers; keep visible reasons - stronger than title-only); label "Career status: Completed" vs "Veteran: Banked" (two sources already distinct, never merged); one-line transfer explanation beside the button (dialog stays; completion removes the Veteran - say so); label toward the wireframe's "Open the source run"; one sentence separating local record provenance from the external Source Ledger. No Factors, no ratings (ADR-0020 §3).
- Acceptance: tests/browser/veterans.spec.ts detail cases green (:291 notes, :368 collapse, :420 C3 trail); VeteranLibraryScreenTest detail cases green; SCREEN_SPEC §4.17 copy rows travel (N3.39).
- Verify: quoted focused runs.
- Risk: resources/js/pages/Veterans/Show.vue is clean at HEAD but the peer library rework shares VeteranRow; re-read before editing.

#### N3.38 - Veteran detail structure pass
- Priority P1 | Deps: N3.37 | Source: snapshot review V4/V6/V7/V8
- Objective: career summary vs metadata separated; notes own section; lineage visually grouped; ancestry empty state leads with the fact.
- Files: resources/js/pages/Veterans/Show.vue:131-233 (The Record mixes gameplay + metadata), :226-232 (Notes inside), :261-292 (Built from), :297-317 (Used as parent in), :270-291 (per-parent N/A empty state).
- Actions: split The Record into career summary (scenario, status, counts, end-state) and metadata (filed, source run, tags); separate Trainer notes section preserving full text; group Built from + Used as parent in under one Legacy and lineage heading with relationships kept distinct; ancestry empty state leads "No ancestry was recorded for this source run." (builder_url-null and parents-null stay different conditions). Structural deviation from the wireframe card shape: record an HFW-OVERRIDE entry per parity convention (IA-4), update SCREEN_SPEC state rows :2565-2570.
- Acceptance: tests/browser/veterans.spec.ts:291 long-notes pin green; new cases pin the split, grouping and empty-state lead; no inference added to lineage.
- Verify: quoted focused runs.
- Risk: wireframe parity is soft-gated here, not owner-decided; the override entry is the obligation.

#### N3.39 - Snapshot/veteran doc truth and flow coverage
- Priority P2 | Deps: N0.1, N3.30-N3.38 landings | Source: claim-test spec-drift list
- Objective: SCREEN_SPEC matches the shipped surfaces; the snapshot end-to-end flow gains its missing spec.
- Files: SCREEN_SPEC.md:2581 (§4.17 gap 2 "No final stat block" FALSE since 367a7e6c), :2559 (layout row describes single-column + four absences; shipped two-column + three), "skills learned/races entered" vs shipped "Skills recorded/Races recorded", "N/A with a title" vs inline visible reasons; SCR-CAR-025/026/027 rows :91-93 and §4.22-4.24 lack HFW dated notes; §4.25 snapshot flow-spec gap (flow coverage 3 of 5).
- Actions: dated errata for each stale row (originals preserved); register the snapshot screens' parity claims (code-comment wireframe citations) in the HFW apparatus; author tests/browser/snapshot-flow.spec.ts end-to-end (entry -> setup -> review -> create -> cockpit, session-private harness, AGENTS.md §9 flow-spec rule). Declined with reason: stepper implementation unification (SH1 - three deliberate implementations, no symptom) and origin-aware back (E8 - dashboard fallback is the review's own sanctioned option; no nav-state plumbing exists).
- Acceptance: doc diff matches tree; new flow spec green in a session-private run; plan's own stale figures corrected (AbsenceValue 40, Form Requests tracked).
- Verify: quoted flow-spec run + doc review.
- Risk: SCREEN_SPEC is peer-dirty (library row rework); stage only own hunks or hand the erratum list to the owning session.

#### N3.40 - Snapshot/veteran ruling package
- Priority P2 | Deps: ND-10 ruling | Source: snapshot review G1-G8 | OWNER-GATED
- Objective: only if the owner rules: bottom primary action bar (G1, wireframe vs DESIGN.md one-accent - DESIGN.md wins by AGENTS.md §2 precedence unless ruled otherwise), Scenario+Trainee identity merge (G2, six-section heading pin tests/browser/career-snapshot.spec.ts:104-116), disabled-door hint removal (G3, audit F-01 + text pin :86), final-stat tiles (G4, HFW srow parity landed 367a7e6c), portrait in PageHeader (G5, one-actions-slot contract, tests/browser/page-header.spec.ts:44-46, 29+ importers), sticky summary/bar (G6, @container makes sticky a no-op, containment.spec.ts pins the decision), Energy Band exposure (G7, StoreSnapshotRequest + CreateSnapshotRun persistence change), form widening/two-column (G8, soft gate: HFW-OVERRIDE record required).
- Files: the three Snapshot pages, resources/js/pages/Veterans/Show.vue, resources/js/components/ui/PageHeader.vue (G5 only), resources/css/app.css + AppLayout.vue (G6 only), StoreSnapshotRequest/CreateSnapshotRun (G7 only); SCREEN_SPEC §7 entries; the named spec pins.
- Actions: per reversal: dated ruling reference, HFW-OVERRIDE or ADR erratum as the conflict class demands, spec inversions with recorded reason, focused + browser runs. G7 additionally needs the schema/persistence review path (AGENTS.md §11). Until ruled: NO edit.
- Acceptance: ruling quoted; state tables agree; prior specs green or inverted with reason.
- Verify: quoted runs + ruling reference.
- Risk: G1/G4/G5 reverse recorded visual rulings; G6 reverses a shell architecture decision; G7 changes persistence - each is a different authority class, do not bundle them into one CSS diff.

### Phase 4 - Document truth and the tag

#### N4.1 - VERSION.md re-baseline
- Priority P0 | Deps: N0.1, N0.6, N1.1 | Source: plan19 S3.2
- Objective: every measured row re-derived at the landed HEAD.
- Files: VERSION.md (tracked, clean; :19 divergence, :27 suite, :29 census, :30 ADR rows stale).
- Actions: re-run each row's command beside it; stamp HEAD; census row reflects N0.6 result; ADR row 0 findings (adr-check measured 0 at exec S4.3, re-run); suite row quotes N1.1.
- Acceptance: no stale count; every row re-derivable.
- Verify: quoted commands.
- Risk: VERSION is Architect-owned at phase exits; coordinate.

#### N4.2 - Citation-red claim sites
- Priority P0 | Deps: N0.6 | Source: plan19 S3.3
- Objective: docs stop describing DocCitationParityTest as red once it passes.
- Files: VERSION.md:29-33,:86; docs/roadmap/EXECUTION-RECORD-2026-11-18.md:333,:351 (dated errata, original sentences preserved).
- Actions: mark criterion (d) satisfied with the census basis named; exec gets a dated erratum, not a rewrite.
- Acceptance: four sites consistent with the quoted gate output.
- Verify: doc diff + census output.
- Risk: exec is an append-only evidence record; erratum form only.

#### N4.3 - ARCHITECTURE API section truth
- Priority P1 | Deps: N0.1 | Source: plan19 S3.4
- Objective: §4 declares the six terminal /api/v1 endpoints, support-cards included.
- Files: ARCHITECTURE.md:313-317 (four endpoints), :329-332 (support-cards forbidden list), routes/api.php (six routes).
- Actions: add support-card lines; remove from forbidden list; keep terminal-six ruling (2026-11-16); digest paragraph travels (ARCHITECTURE-ESSENTIALS.md).
- Acceptance: php artisan route:list --path=api matches §4 six-for-six.
- Verify: quoted route list + diff.
- Risk: API shapes are also a 1.0-gate item (N5.15); this slice fixes the count, the shape declarations stay with N5.15.

#### N4.4 - Independent Training doc trail
- Priority P1 | Deps: N0.1, D-2 PRD choice | Source: plan19 S3.5
- Objective: CURRENT.md and PRD stop denying the shipped mode.
- Files: docs/adr/CURRENT.md:158 ("no Trainer-facing entry control" - stale), PRD.md (zero Independent Training mentions).
- Actions: dated erratum in CURRENT.md; PRD line or explicit non-goal amendment per owner choice (ADR-0032 accepted is the authority); adr-check stays 0.
- Acceptance: python tools/adr-check.py docs/adr = 0 findings; erratum dated.
- Verify: quoted adr-check.
- Risk: PRD amendment is owner territory; recommend the line, do not invent it.

#### N4.5 - AGENTS.md count and trap corrections
- Priority P1 | Deps: N0.1 | Source: plan19 S3.6/S3.7/S3.8 doc half
- Objective: instruction file matches the tree.
- Files: AGENTS.md:137 (pages 66 vs measured 62; components 119 correct; layouts 2 vs measured 3), :490 KI-76 bullet (config/inertia.php now published, SSR default false, timeout wired - verify at dispatch), :19/:419 "No CI" (three build-only workflows exist), CLAUDE.md:272/:276 same CI claim.
- Actions: correct counts with re-derivable find commands beside them; rewrite the KI-76 bullet to the published-config state; CI lines corrected to "build-only CI, no gate job" until N4.8 lands, then to the gate-job truth. Changelog row per AGENTS.md §20 convention.
- Acceptance: find counts match; config/inertia.php reading matches the bullet; grep workflows matches the CI sentence.
- Verify: quoted commands.
- Risk: AGENTS.md is a tracked instruction source; edits are in-scope for doc-truth but keep the diff minimal.

#### N4.6 - SCREEN_SPEC §7 reconciliations
- Priority P1 | Deps: N0.1, Phase 3 landings | Source: constraints report drift list
- Objective: the conflict register matches the shipped tree.
- Files: SCREEN_SPEC.md (tracked, dirty - peer owns current hunks; coordinate).
- Actions: dated entries for: §7-3 stale (Legacy Lab builder now writes Sparks per HFW register); §7-6 team_rank landed via ADR-0034/KI-133, capture half still open; §7-17 figure 74 title-only sites (AbsenceChannelTest ratchet) supersedes "17 sites"; KI-144 body CLOSED 2026-11-01 vs HFW register still citing Slice 6b - reconcile the register row; §8 numbering drift (AGENTS.md cites §8, state tables live in §4) - record as erratum, do not renumber silently; §7-18 trust labels stay OPEN pending owner definition; database-area drift from the Phase-6 claim test: SCR-SYS-011 "tile reads three" stale since 0174519c (SCREEN_SPEC.md:110), the §4.26 version sentence stale vs G6=SHIP (SCREEN_SPEC.md:3496), verified_at 2026-09-27 stale vs config 2026-11-02 (SCREEN_SPEC.md:1275), DatabaseTest/database.spec counts 7/7 stale vs measured 16/28 (SCREEN_SPEC.md:1300,:1303), SCR-SYS-008 "no filter" state moves when N6.12 lands, SCR-SYS-009 order wording drift. KI-152 index OPEN (:220) vs body CLOSED (:103) drift goes to N0.5.
- Acceptance: each named item has a dated disposition; no silent rewrite.
- Verify: doc diff review.
- Risk: peer-dirty file; stage only own hunks or hand the list to the owning session.

#### N4.7 - Roadmap and INDEX dated errata
- Priority P1 | Deps: Phases 0-3 evidence | Source: reconciliation G6-G16 | OWNER/Docs Writer
- Objective: the ordering documents stop presenting closed work as open.
- Files: docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md (peer-dirty), docs/roadmap/INDEX.md, docs/roadmap/EXECUTION-RECORD-2026-11-18.md header.
- Actions: dated errata (original rows preserved): S2.2/S2.3/S2.4/S2.6/S3.1 closed per exec; D-13 answered-in-tree at 8fd7e18e with the gate quote; S0.6 row reconciled with the landed palettes (different token values than the row's draft hexes - record the measured values); metadata 901 lines / 33 slices / six phases; INDEX master/residual rows re-measured; gates.sh tracked (master:40/:156 stale); auditor vendored (residual:623-630 stale). This plan does not edit these files; it hands the erratum list to the owner/Docs Writer.
- Acceptance: errata landed by the owning session; acceptance item 9 (D-13) quotable.
- Verify: git log for the erratum commit.
- Risk: two sessions editing one roadmap is the exact hazard the collaboration rules exist for; hand off, do not grab.

#### N4.8 - CI gate job and release acceptance predecessor
- Priority P1 | Deps: N0.3, N0.4 | Source: plan19 S4.1/S4.2
- Objective: CI runs a gate that can fail; the release installer is preceded by the packaged acceptance job.
- Files: .github/workflows/ (three build-only workflows), .github/workflows/release.yml:174 (needs: build only), new gate workflow to be authored.
- Actions: gate job runs bash tools/gates.sh --fast with the sockets extension and PHP floor matching composer.json; release.yml publish needs the acceptance job; AGENTS/CLAUDE CI lines move with it (N4.5).
- Acceptance: a pushed lint failure reddens the gate job (injection proof); workflow_dispatch shows the packaged spec running against this run's installer.
- Verify: quoted CI run logs.
- Risk: CI is new territory for this repo ("No CI" was the standing claim); plan19 authorizes it, keep the job minimal. Windows-vs-Linux runner differences for gates.sh steps - test the job on the runner the workflows already use.

#### N4.9 - Pre-tag audit readings
- Priority P0 | Deps: N0.1 | Source: plan19 S4.4
- Objective: composer audit + npm audit --omit=dev outputs quoted in the tag commit body, no reachable critical/high.
- Actions: run both at the tag candidate; quote; any reachable advisory blocks the tag or gets an owner ruling.
- Acceptance: both outputs recorded; gates.sh composer block prints REACHED (S3.9 cosmetic message tweak optional, resolution logic untouched).
- Verify: quoted outputs.
- Risk: npm audit metadata availability (UNAVAILABLE state landed cd7cb8cb is not a clean result).

#### N4.10 - Cut v3.0.0
- Priority P0 | Deps: N0.2, N0.5, N0.6, N1.1, N1.2, N4.1-N4.9, D-12 | Source: master S4.4, plan19 S4.3 | OWNER ONLY
- Objective: annotated tag v3.0.0 on an origin/master-reachable commit with the gate output recorded.
- Actions: owner verifies the nine acceptance items (§2); CHANGELOG final row; git tag -a v3.0.0 with gate readings in the body; push tag.
- Acceptance: git tag -l 'v3*' contains v3.0.0; git merge-base --is-ancestor v3.0.0 origin/master exits 0.
- Verify: quoted commands.
- Risk: any unrun gate makes this a stated partial, never a silent tag.

### Phase 5 - Post-tag, toward 1.0

#### N5.1 - Error summary (GOV.UK shape)
- Priority P2 | Deps: tag | Source: uiux 5.1 (open, not owner-gated)
- Objective: failed submit focuses an error summary; one entry per invalid field; anchors resolve.
- Files: shared form error component to be authored or extended from existing error display; career write forms adopt it.
- Acceptance: uiux:270-273 criteria; no new arbitrary Tailwind value (budget 209).
- Verify: browser cases + lint:ui.

#### N5.2 - IME-safe Enter in unguarded components
- Priority P2 | Deps: tag | Source: uiux 5.2 = master S5.8 partial (dedupe: one slice, not two)
- Objective: composing Enter never commits; four refusal cases red against current tree first.
- Files: the four components uiux:283-304 names (re-derive at dispatch; 62dc6a6f landed the fixture + picker wrap).
- Acceptance: uiux:295-297; isComposing guard per Baseline table.
- Verify: quoted browser cases.

#### N5.3 - Deferred props for data-heavy reads
- Priority P2 | Deps: tag | Source: uiux 5.3
- Objective: deferred key absent from first payload, present in partial reload; fallback then data, no layout shift; rescue path on failure.
- Files: cockpit/catalog read paths named by uiux:306-325; Inertia deferred props (framework feature, no dependency).
- Acceptance: uiux:317-319; NFR-10/11 budgets not worsened.
- Verify: feature + browser cases.

#### N5.4 - WCAG 2.2 AA assertions
- Priority P2 | Deps: tag | Source: uiux 5.4 = master S5.8 partial (dedupe with N5.2/N5.13)
- Objective: 2.4.11 focus-not-obscured, 2.5.8 target-size, 2.5.7 dragging alternatives pinned; each proven fail-on-violation by temporary revert.
- Acceptance: uiux:345-346; house 44px convention stays stricter than the 24px floor.
- Verify: quoted axe/browser cases.

#### N5.5 - Baseline decisions record
- Priority P2 | Deps: tag | Source: uiux 5.5
- Objective: every platform feature used has a row (status + date checked); candidate table already filled (Popover usable, dialog usable, container queries in use, isComposing usable-with-guard, View Transitions defer past 3.0.0).
- Acceptance: uiux:368-369; no feature outside the table without a row.
- Verify: doc review.

#### N5.6 - Provenance backfill
- Priority P2 | Deps: D-9 ruling | Source: master S5.1
- Objective: one data_sources row per engine-written fact, or a ruled terminal gap.
- Actions: backfill action + provenance test after ruling; never invent source keys (133 rows / 1 source_key measured).
- Verify: php artisan test --filter=Provenance.

#### N5.7 - Team-race slots and roster/preseason
- Priority P2 | Deps: D-7, D-8 rulings | Source: master S3.5 capability rows
- Objective: source package or terminal absence for team-race slots; roster/preseason deferred or scheduled with ADR.
- Note: master §14 D-number mis-wiring (G18): S3.5 table cites D-11/D-12 for subjects §14 assigns to D-7/D-8; the match_candidates discriminator has NO correct owner decision - ADR-0037 settled entity=source_key, race promotion semantics remain ND-2. Erratum owed via N4.7.

#### N5.8 - OQ-2 / OQ-3 close
- Priority P2 | Deps: D-11 | Source: master S5.3
- Objective: source legality/refresh policy + scheduler-vs-manual ruled; config('uma.sources') reflects it.
- Verify: php artisan config:show uma.sources.

#### N5.9 - Primitive layer FQ-01..FQ-09
- Priority P2 | Deps: D-10, ADR-0028 | Source: master S5.4
- Objective: @theme inline primitive layer; arbitrary-value count down from 209 (uiux 5.6 defers here).
- Verify: npm run lint:ui + typecheck.

#### N5.10 - ADR code gaps
- Priority P2 | Deps: tag | Source: master S5.5 (narrowed)
- Objective: ADR-0002 two markers (1200 halved-gains line + scenario ceiling, still unbuilt per 2026-10-22 check), ADR-0004 FK + scenario repoint, ADR-0010 legacy UI index erratum, ADR-0011 hint import remainder. ADR-0018 PageSize half ALREADY BUILT (app/Services/PageSize.php) - excluded.
- Verify: focused feature tests + adr-check.

#### N5.11 - Artwork OQ-6 remainder
- Priority P2 | Deps: D-11/OQ-6 | Source: master S5.6
- Objective: skill-icon column (skills.iconid migration) + pre-run picker frame from the allowlisted host; four of five surfaces already answered.
- Verify: php artisan uma:fetch-art --dry-run + mirror test; miss never blocks render (NFR-17).

#### N5.12 - Performance budgets
- Priority P2 | Deps: N1.1 | Source: master S5.7
- Objective: catalog index <200ms (NFR-3) and cockpit query ceilings (NFR-11) pinned by tests.
- Verify: benchmark output + performance test.

#### N5.13 - MWG remainder
- Priority P2 | Deps: tag | Source: master S5.8 minus N5.2/N5.4 overlaps
- Objective: <search>, popover tooltips, LCP images, dialog overscroll, BaseInput props, <fieldset>, closedby, content-visibility; MWG #5 excluded.
- Verify: test:a11y + axe cases.

#### N5.14 - Parked debt triggers
- Priority P2 | Deps: trigger events | Source: plan19 Phase 5 table
- Items: TrainingRun.php split (1718 lines measured; trigger = a slice changing three unrelated concerns or a cockpit query-count failure; split by job, never line count); ui-framework-check.mjs line-skew fix (preserve newlines in stripComments + eva01 selector in tokenSpans; do it in the commit that next trusts an app.css finding); data_sources backfill = N5.6.

#### N5.15 - 1.0 gate
- Priority P2 | Deps: N5.6-N5.13, four PRD questions | Source: master S5.9, VERSION.md:58-63
- Objective: four open PRD questions closed (OQ-2, OQ-3, OQ-5 remainder, OQ-6), /api/v1 shapes declared in ARCHITECTURE.md, no OPEN KI touching API/schema; VERSION gate table updated; full gates.sh.
- Verify: bash tools/gates.sh + VERSION.md gate rows.

### Phase 6 - Database & Help reference UX (2026-11-20 review, claim-tested)

74 claims tested (30 over Home/Catalogue/Races/Scenarios, 44 over Events/Shop/Sparks/Sources/Help): 33 EXISTS, 25 PARTIAL, 12 MISSING, 4 NEEDS-DECISION. Static-read at 37320f35 by three read-only investigators; pivotal lines re-verified live by this pass (Sources CONFLICTS + both stale notes, Help keyboard override vs the live TrainingDetail accelerators, Races conflict anchor, Scenarios on/off cells, ShopItems pager, DatabaseController::sources() at :470, AppLayout G6 ruling history, ADR-0030 contract, catalog footer and back-link). Only verified gaps become slices. The review's design priorities are not tag blockers: this phase is P2 post-tag; its P0 (Source Ledger trust work, N6.1-N6.5) is the pull-forward candidate if the owner wants it before v3.0.0. Master §16 acceptance is unchanged by this phase.

Refuted premises (do not build): the hub banner already prints the verification date and "nothing fetched today" (resources/js/pages/Database/Index.vue:50-62, the review misread it as "Global data · Current"); aptitude family order is fixed server-side (app/Http/Controllers/CatalogController.php:555-585); the search placeholder already states trainee-and-card coverage (resources/js/components/catalog/TraineeCatalog.vue:221); race sortable headers shipped at 41bc48e8; Events mechanics cells are single sentences of 73-126 chars with a drawer already (measured strlen); event categories are not merged (10 live-service rows, in-game categories separated in the reference panel); "Recorded" is not a shipped provenance state (confirmed renders deliberately unmarked, resources/js/components/ProvenanceMark.vue:45-58); scenario sparks are their own sixth category, not a White sub-kind (recorded override, resources/js/pages/Database/Sparks.vue:108-112); the hub nav is already a distinct home pill with contained 320px scroll (resources/js/components/database/DatabaseNav.vue:43-48, pinned tests/browser/database.spec.ts:681-721). The review's optional sticky Race column is excluded: position:sticky is a no-op under the shell's @container containment (resources/js/layouts/AppLayout.vue:639-650, containment.spec.ts pins).

New defects found by the claim test (not review claims): D1 Sources.vue:266 "these three are the three still open" vs 4 conflict rows; D2 Sources.vue:231 "newest recorded fetch across every dataset" contradicts the per-source F-04 fix (app/Http/Controllers/DatabaseController.php:495-497); D3 Help.vue:17-19 keyboard override claims 1-5 do nothing while resources/js/pages/Career/TrainingDetail.vue:464-497 wires and prints them; D4 Help.vue:9-10 + SCREEN_SPEC §4.26 "shell prints no version" stale vs owner ruling G6=SHIP (resources/js/layouts/AppLayout.vue:20-32); D5 SCREEN_SPEC.md:110 SCR-SYS-011 "tile reads three" stale since 0174519c; D6 TraineeCatalog.vue:399-403 footer "not stored in this catalogue" vs ADR-0030 (accepted, built, 263/263 rows carry base_stats/growth_rates); D7 resources/js/pages/Catalog/Show.vue:207 "Back to catalog" drops the query string; D8 SCREEN_SPEC.md:1275 verified_at 2026-09-27 vs config 2026-11-02 and :1300/:1303 test counts 7/7 vs measured 16/28. KI-152 index row OPEN (:220) vs body CLOSED (:103) is register drift for N0.5, not a Phase-6 edit. D1/D2/D3/D6/D7 become slice work below; D4/D5/D8 ride N4.6.

Shared-system note: no new primitive layer is authored. AbsenceValue (36 importers), ProvenanceBadge (27), PageHeader (39), SourceNote, DataTable (3 importers) and buttonStyles (44px base) already exist and are re-measured this pass. The two table idioms (DataTable vs per-page table.t) are a recorded deliberate choice (resources/js/pages/Database/Scenarios.vue:281-293 idiom note); no forced migration. The FQ primitive refactor stays N5.9 under D-10. Review §4.2 typography hierarchy ships no slice: the four text tiers are owner-signed (DESIGN.md §2.2) and the dense-table exception is deliberate (IA principles document); a table-type change needs a DESIGN.md ruling first. Review §4.4 cross-reference pairs beyond the ledger mostly ship already via SourceNote and the provenance popovers; the missing doors are N6.4 (ledger), N6.13 (Legacy/Veterans) and N6.16 (Help anchors).

#### N6.1 - Sources copy truth and conflict-count guard
- Priority P2 (review P0, pull-forward candidate) | Deps: N0.1 | Source: db-review §3.8(5)(7); defects D1/D2
- Objective: the ledger's notes stop contradicting its payload; conflict-count drift fails a test.
- Files: resources/js/pages/Database/Sources.vue (:231 fetched-date note, :266 "these three" note), tests/Feature/DatabaseTest.php, tests/browser/database.spec.ts, tests/browser/database-parity.spec.ts (:230 stale "twelve" comment).
- Actions: rewrite :231 to the per-source wording the controller already stamps; rewrite :266 so the count word derives from the conflict list (or names four under a guard); add a feature case pinning row-count against the note claim and a browser case for the note text; correct the parity comment to 17. SCR-SYS-011 erratum rides N4.6.
- Acceptance: php artisan test --compact --filter=Database green incl the new guard; focused browser case green; grep "these three" resources/js/pages/Database/Sources.vue = 0.
- Verify: quoted focused runs.
- Risk: N2.11 touches the same controller note; land N2.11 first or coordinate the hunk.

#### N6.2 - Sources summary placement and Supplies differentiation
- Priority P2 | Deps: N6.1, N2.11 coordinate | Source: db-review §3.8(1)(3)(4)
- Objective: the 17/4/verified summary sits at the top; the Supplies column differentiates sources instead of repeating one pipeline sentence on all 13 fetched rows.
- Files: resources/js/pages/Database/Sources.vue (tiles :278-287 move beside the :99-106 banner), app/Http/Controllers/DatabaseController.php sources() (:495-497 note), tests.
- Actions: move the two tiles and the verification date into a top summary strip (values already computed); replace the identical fetched note with a one-line table legend plus a per-source Supplies phrase transcribed from what each parser ingests (config/uma.php and app/Services/DataPipeline/Parsers/ are the evidence; no invented supplies); the Read header states what its date measures; transcribed rows keep their anchor wording. No per-row tier (ND-14).
- Acceptance: DatabaseTest + database.spec.ts + parity green with allow-list updates recorded; fetched Supplies wording truthful per parser.
- Verify: quoted focused runs.
- Risk: Supplies wording is a copy surface - Global vocabulary, lore ruling per hit.

#### N6.3 - Conflict records as data
- Priority P2 | Deps: N6.2 | Source: db-review §3.8(7)
- Objective: conflicts become server payload records with ids and relationships, not a Vue const.
- Files: config/reference.php (new conflicts section), app/Http/Controllers/DatabaseController.php sources(), resources/js/pages/Database/Sources.vue (:72-77 const removed), resources/js/types.ts, tests.
- Actions: fields id/question/claims/state/affected_routes/source_refs (docs/UMAMUSUME_REFERENCE.md §7 row numbers, log at :4287+); state stays open for all four - no resolution; affected_routes lists only always-reachable routes (run-scoped screens excluded with a recorded reason); the count tile derives from the payload.
- Acceptance: feature test pins the four records and their fields; browser case pins the rendered rows; no conflict text changed in substance.
- Verify: quoted focused runs.
- Risk: the §7 log holds 62 rows, most resolved - only the four open ones surface; do not import the whole log.

#### N6.4 - Bidirectional provenance links
- Priority P2 | Deps: N6.3 | Source: db-review §3.8(8), §3.3(4)
- Objective: the race 1,400 m conflict reaches its ledger record; ledger records reach their affected screens.
- Files: resources/js/pages/Database/Races.vue (:372-378 anchor keeps #race-reference and adds a ledger link), resources/js/pages/Database/Sources.vue conflict rows, resources/js/pages/Database/ShopItems.vue (:241-247 Artisan link unified), tests/browser/database.spec.ts (:759-762 click pin extended).
- Actions: ledger conflict rows link affected_routes from N6.3; the Races conflict disclosure gains a "View the ledger record" link per conflict id; the hardcoded distance === 1400 trigger moves to a server flag where the parse already derives the band (re-derive at dispatch; SCREEN_SPEC.md:1272 notes band derived at parse).
- Acceptance: browser round-trip race to ledger to race green; existing conflict-click pin stays green; keyboard path preserved.
- Verify: quoted session-private browser run.
- Risk: new anchor ids must not collide with the existing #race-reference disclosure behavior.

#### N6.5 - Sources filters
- Priority P2 | Deps: N6.3, N2.11 | Source: db-review §3.8(6)
- Objective: filter the ledger by kind, date status and conflict involvement.
- Files: app/Http/Controllers/DatabaseController.php sources(), resources/js/pages/Database/Sources.vue, tests.
- Actions: query-param filters following the races-sort read precedent (41bc48e8: clamp unknown tokens, preserve across pagination); kind Fetched/Transcribed, dated/no-fetch-recorded, in-open-conflict; result count announced (role="status"); empty result state per ADR-0007. No tier filter until ND-14 rules.
- Acceptance: feature cases per filter and combined; browser case; focused suite green.
- Verify: quoted focused runs.
- Risk: the date-status filter is only meaningful after the N2.11 union read; sequence it.

#### N6.6 - Races list absence precision
- Priority P2 | Deps: N0.1 | Source: db-review §3.3(5)
- Objective: list N/A reasons distinguish unpublished / not-applicable / not-recorded where the source records the distinction.
- Files: app/Http/Controllers/DatabaseController.php races(), `app/Services/RaceFacts.php:28-70`, resources/js/pages/Database/Races.vue (:359-381), tests.
- Actions: server-owned reason per nullable column (the detail page already carries richer reasons); where no distinction is recorded keep "Not recorded" - never invent a reason class; update the five N/A disclosure pins with the new reasons.
- Acceptance: DatabaseTest + database.spec.ts green; absence contract holds (N/A plus disclosure, never zero).
- Verify: quoted focused runs.
- Risk: the facts-service path and line range re-verify at dispatch.

#### N6.7 - Scenario matrix words and detail disclosure
- Priority P2 | Deps: N0.1 | Source: db-review §3.4(4)(5)
- Objective: the panel matrix reads Included/Not included; per-scenario detail collapses below the comparison spine.
- Files: resources/js/pages/Database/Scenarios.vue (:229 cells, :295-375 sections), tests/browser/database.spec.ts (:438-441 h3 pins, :449-456 on/off pins), tests/browser/database-parity.spec.ts.
- Actions: copy change with an HFW-OVERRIDE record (the mock cells are on/off; design-2.0 §42 word-not-glyph is satisfied either way); per-scenario sections become details elements with visible summary lines; caps and panels matrices stay primary and expanded; pin inversions recorded with reason (AGENTS.md §9); no content deleted.
- Acceptance: database.spec + parity + DatabaseTest green; all four scenarios' full mechanics reachable in one disclosure each.
- Verify: quoted focused runs.
- Risk: the parity spine stays untouched - the sections sit below it (SCREEN_SPEC.md:106 dated note).

#### N6.8 - Catalog truth and return path
- Priority P2 | Deps: N0.1 | Source: db-review §3.2(6)(7); defects D6/D7
- Objective: the footer states the ADR-0030 truth as a disclosure; Back to catalog keeps the Trainer's filters.
- Files: resources/js/components/catalog/TraineeCatalog.vue (:399-403 footer), resources/js/pages/Catalog/Show.vue (:192 breadcrumb, :207 back link), app/Http/Controllers/CatalogController.php, tests/browser/catalog.spec.ts (:107-110 fabricated-+0% refusal pin), tests/Feature/CatalogDetailPageTest.php.
- Actions: footer becomes a labelled disclosure: base stats and growth rates are stored per form (ADR-0030, accepted 2026-11-02, 263/263 rows measured), the index card omits the figures rather than print a fabricated +0% (refusal pin stays), the trainee page renders them; show() ships a return-path prop carrying the incoming query string; back link and breadcrumb consume it. Copy must not overclaim the Show render state while KI-152's index/body drift is unreconciled (N0.5).
- Acceptance: catalog.spec + CatalogDetailPageTest green incl the new return-path case; +0% refusal pin unchanged.
- Verify: quoted focused runs.
- Risk: Show.vue carries peer-dirty history; re-read before editing.

#### N6.9 - Catalog filter legibility
- Priority P2 | Deps: N6.8 | Source: db-review §3.2(4)(5)
- Objective: active constraints visible beside the count; the unconfirmed toggle defines itself.
- Files: resources/js/components/catalog/TraineeCatalog.vue (:279-286 toggle, :317 count line), tests/browser/catalog.spec.ts.
- Actions: active-constraint summary from the echoed select values beside "X of Y trainees"; the toggle gains a disclosure carrying the stored definition verbatim ("Not confirmed by two sources", the wording already on resources/js/pages/Catalog/Show.vue:421-422); no new filter semantics.
- Acceptance: catalog.spec green incl the definition-disclosure case; count wording unchanged.
- Verify: quoted focused runs.
- Risk: low; copy plus one disclosure.

#### N6.10 - Shop fact-vs-opinion hierarchy
- Priority P2 | Deps: N0.1 | Source: db-review §3.6(1)(3)(4)(6)
- Objective: effects dominate ratings; the Pro Shop section is named; the pager is predictable.
- Files: resources/js/pages/Database/ShopItems.vue (:180 aria-only heading, :89-99 pill classes, :199-205 cells, :211-222 pager), tests.
- Actions: visible h2 for the Pro Shop section; rating pills become plain text (attribution already ships: "Rating (one guide)" header, per-row title, "What the ratings are" card); Effect column emphasis within existing tokens; "Show all 19 Pro Shop items" label derives its count and a Show fewer control appears after expansion; the general catalogue stays untouched by the pager.
- Acceptance: DatabaseTest + database.spec + parity green; no pill class on rating cells; pager scope pinned.
- Verify: quoted focused runs.
- Risk: the parity allow-list may pin rating pills - update with recorded reason.

#### N6.11 - Shop catalogue filters
- Priority P2 | Deps: N6.10 | Source: db-review §3.6(5)
- Objective: kind / spend-site filters over the 10 transcribed rows.
- Files: resources/js/pages/Database/ShopItems.vue (:353-362 chips already render), tests.
- Actions: client-side selects over the payload (kind and spend_site already stored, app/Http/Controllers/DatabaseController.php:352-353); count feedback; clear control. ponytail: client-side at 10 rows; upgrade to server params if the catalogue grows.
- Acceptance: browser case per filter plus empty state; no route change.
- Verify: quoted session-private browser run.
- Risk: DataTable has no filter API - page-level controls, not a DataTable change (its 3 importers unaffected).

#### N6.12 - Events server filter and cadence labels
- Priority P2 | Deps: N0.1 | Source: db-review §3.5(2)(3)(7)
- Objective: browse by server; cadence comparable at a glance without manufacturing schedules.
- Files: config/reference.php (events rows :59-140), app/Http/Controllers/DatabaseController.php events() (:301), resources/js/pages/Database/Events.vue, tests, SCREEN_SPEC.md SCR-SYS-008 state row.
- Actions: server filter Global/JP/both (servers stored per row); add a cadence_summary field per row transcribed from the existing observation text (Monthly where the text says monthly, :79; Not established where single-observation :87 or too-short :71); exact text stays in the drawer; rows link the mechanics panels below; the spec "no filter" state moves with the change.
- Acceptance: DatabaseTest + database.spec green incl filter cases; every summary traceable to its row's observation text (citation comment per row).
- Verify: quoted focused runs.
- Risk: the taxonomy is transcription, not derivation - a summary the text does not support is a fabricated fact (Floor).

#### N6.13 - Sparks classification label, odds citation, doors
- Priority P2 | Deps: N0.1 | Source: db-review §3.7(7)(8)(9)
- Objective: the categories table reads as a classification; the odds carry their source; Legacy Lab and Veterans are reachable.
- Files: resources/js/pages/Database/Sparks.vue (:305+ categories, :395+ odds, imports :21-31), config/reference.php (odds note :345), tests.
- Actions: label the six-category table as the data classification of the five families (not a second family list); the odds section prints its source anchor and coverage (the controller docblock names it; verify the §1.5.3 anchor in docs/UMAMUSUME_REFERENCE.md at dispatch); add Link doors to /legacy and /veterans (routes exist on the global rail). Color identity stays parked (ND-13).
- Acceptance: DatabaseTest + database.spec + parity green; doors resolve; no computation added (G-49).
- Verify: quoted focused runs.
- Risk: star-roll odds stay sourced approximations - never computed.

#### N6.14 - Hub tile wording and hover
- Priority P2 | Deps: N0.1 | Source: db-review §3.1(1)(2)
- Objective: scoped counts state which number is which; tiles respond to hover.
- Files: resources/js/pages/Database/Index.vue (:79-87 tiles), app/Http/Controllers/DatabaseController.php index() (:67-97), tests/browser/database.spec.ts, tests/Feature/DatabaseIndexCountsTest.php.
- Actions: tile wording names the pair (Global forms vs catalogue entries, semantics from the existing scoped/recorded payload; transcribed tiles keep the tag word, no fabricated totals); hover state matching the catalog-card precedent (resources/js/components/catalog/TraineeCatalog.vue:326 hover:border-ink-faint), tokens only; verify the HFW database.html tile copy at dispatch and record an override if diverging. Grouping and dual-door consolidation stay ND-11.
- Acceptance: DatabaseIndexCountsTest + database.spec green; focus ring unchanged; no new arbitrary value.
- Verify: quoted focused runs + npm run lint:ui.
- Risk: the parity spec may pin tile text - update with recorded reason.

#### N6.15 - Help task index, limitations, troubleshooting, keyboard truth
- Priority P2 | Deps: N0.1 | Source: db-review §3.9(1)(2)(4)(5); defects D3/D4
- Objective: a Trainer resolves a specific question without reading the whole page; Help documents the controls that exist.
- Files: resources/js/pages/Help.vue (:17-19 stale override, :9-10 version docblock, :83-88 keys, :117-126 undo, :227-262 troubleshooting), tests/browser/help.spec.ts, tests/Feature/HelpScreenTest.php.
- Actions: question anchor nav to the seven existing anchors; the Undo note named under a Current limitations heading (wording already matches the four disabled doors - keep); troubleshooting rows restructured What you see / Why / What to do; the keyboard table adds the TrainingDetail-scoped 1-5 pick / Enter record / Esc back with the typing-guard scope stated (resources/js/pages/Career/TrainingDetail.vue:464-497, savebar contract :910-912); the stale override gets a dated correction keeping the original sentence; the docblock version sentence corrected to G6=SHIP (resources/js/layouts/AppLayout.vue:20-32). Rules-row links stay untouched pending ND-15; glossary expansion stays gated on §7-18. The help.spec.ts:54 stale comment ("Five career screens") is corrected with the slice - four doors measured.
- Acceptance: help.spec + HelpScreenTest green incl new anchor and keyboard cases; every documented control verified wired; link pins stay green.
- Verify: quoted focused runs.
- Risk: Help carries 10 in-force HFW overrides; additive sections only, no spine reversal.

#### N6.16 - Contextual Help deep-links
- Priority P2 | Deps: N6.15 | Source: db-review §3.9(6)
- Objective: first consumers of the existing help anchors from the screens that need them.
- Files: resources/js/components/catalog/TraineeCatalog.vue (unconfirmed toggle), resources/js/pages/Database/Races.vue (absence disclosure), resources/js/pages/Database/Sources.vue (tier card), tests/browser/help.spec.ts link pins.
- Actions: named deep links /help#help-tags and /help#help-source from the three surfaces (anchors exist at resources/js/pages/Help.vue:109-228, zero consumers today); no primitive-wide change - AbsenceValue and ProvenanceMark stay untouched (36/27 importers would be the blast radius).
- Acceptance: browser case per link resolves to its anchor; help.spec link pins extended.
- Verify: quoted session-private browser run.
- Risk: anchor ids must stay stable - N6.15 owns them.

#### N6.17 - Database/Help theme and reflow verification
- Priority P2 | Deps: N6.1-N6.16, N1.2 | Source: db-review §5 step 6, §4.5
- Objective: all nine areas plus Help hold across three themes at 320/1280 after the phase lands.
- Files: tests/browser/database-parity.spec.ts (light+dark today, :102-105), a database-ux theme matrix spec to be authored (four-screen-ux pattern, untracked peer precedent), tests/utils/accessibility.ts (read-only reference).
- Actions: extend the theme matrix to the 10 routes x 3 themes x 2 viewports; axe scans per route; the wcag22aa tag decision stays with N5.4 (buildAxe carries wcag2a/2aa/21aa today - do not add the tag unilaterally); classify every red.
- Acceptance: session-private run of the new spec quoted; no new arbitrary Tailwind value (budget 209); document no-overflow at 320.
- Verify: quoted runner summary + classification table.
- Risk: browser contention (KI-73/KI-80); one process at a time, lock dir.

#### N6.18 - Database ruling package
- Priority P2 | Deps: ND-11..ND-16 rulings | Source: db-review structural recommendations | OWNER-GATED
- Objective: make the six gated reversals explicit, not pre-authorized.
- Actions: until ruled, NO edit. Per reversal: a SCREEN_SPEC §7 dated entry naming the superseded override, pin inversions with recorded reason, focused + browser runs. ND-11 hub grouping/dual door; ND-12 release-badge demotion; ND-13 spark/server color identity; ND-14 per-row tier; ND-15 Help rules links; ND-16 scenario identity/max-highlight/chart.
- Acceptance: ruling quoted per item; code and doc agree; prior specs green or inverted with reason.
- Verify: quoted runs + ruling references.
- Risk: reversing a signed wireframe ruling without the owner is a scope violation; this slice exists to make the gate explicit.

## 8. Crosswalk (one join key per piece of work)

| This plan | Master roadmap | plan19 (2026-11-19) | uiux roadmap | Review claim |
| --- | --- | --- | --- | --- |
| N0.1 | S0.1, S0.3 | pass completion | - | - |
| N0.2 | S0.5 (push half) | - | - | - |
| N0.3 | S0.2 (test half) | S0.2 | - | - |
| N0.4 | S0.2 (register step) | S0.6 | - | - |
| N0.5 | S2.6 (drift) | S0.7 | - | - |
| N0.6 | S1.3 | S3.3 premise | - | - |
| N1.1 | S1.1 | - | - | - |
| N1.2 | S1.2 | - | 0.2/4.x inheritance | - |
| N1.3 | - | S2.3 edge | - | - |
| N2.1 | - | S1.1 remainder | - | - |
| N2.2 | - | S1.2 remainder | - | - |
| N2.3 | - | S1.4 remainder | - | - |
| N2.4 | - | S1.8 remainder | - | - |
| N2.5 | - | S1.9 remainder | - | - |
| N2.6 | - | S1.12 remainder | - | - |
| N2.7 | - | S1.15 remainder | - | - |
| N2.8 | - | S2.2 | - | - |
| N2.9 | - | S1.5 remainder | - | - |
| N2.10 | - | S2.1 | - | - |
| N2.11 | - | S2.4 | - | - |
| N3.1 | - | - | - | X1 |
| N3.2 | - | - | - | C1 |
| N3.3 | - | - | - | C6 |
| N3.4 | - | - | - | C4 |
| N3.5 | - | - | - | C3 |
| N3.6 | - | - | - | T1, T6 |
| N3.7 | - | - | - | R1, R5, R6, R4-side |
| N3.8 | - | - | - | S2, S1-copy |
| N3.9 | - | - | - | E6 |
| N3.10 | - | - | - | I4, I5 |
| N3.11 | - | - | - | D2, D5 |
| N3.12 | - | - | - | L1, L2, L3 |
| N3.13 | - | - | - | G3-sub, G4 |
| N3.14 | - | - | - | M1, M2, M5 |
| N3.15 | - | - | - | N1, N4, N5 |
| N3.16 | - | - | - | T2, E1, I2 (HFW-gated) |
| N3.17 | - | - | - | extra findings |
| N3.18 | - | - | - | D3 |
| N3.19 | - | - | - | wizard 3.4.4 |
| N3.20 | - | - | - | wizard 3.6.9 |
| N3.21 | - | - | - | wizard 3.6.7 |
| N3.22 | - | - | - | wizard 3.7.2 |
| N3.23 | - | - | - | wizard 3.7.7 |
| N3.24 | - | - | - | wizard 3.3.2, 3.3.7 |
| N3.25 | - | - | - | wizard 3.2.2 |
| N3.26 | - | - | - | wizard 3.3.4 |
| N3.27 | - | - | - | wizard 2.4 |
| N3.28 | - | - | - | wizard structural (ND-9) |
| N3.29 | - | - | - | wizard stale artifacts |
| N3.30 | - | - | - | snapshot E1-E4, E6, E7 |
| N3.31 | - | - | - | snapshot S2 |
| N3.32 | - | - | - | snapshot S3 |
| N3.33 | - | - | - | snapshot S5, S7 |
| N3.34 | - | - | - | snapshot S8 copy arm |
| N3.35 | - | - | - | snapshot S13, S14 residual |
| N3.36 | - | - | - | snapshot S9 |
| N3.37 | - | - | - | veteran V2, V5, V10-V12 |
| N3.38 | - | - | - | veteran V4, V6-V8 |
| N3.39 | - | - | - | snapshot/veteran spec drift, SH1/E8 declined |
| N3.40 | - | - | - | snapshot G1-G8 (ND-10) |
| N4.1 | S4.1 (re-open) | S3.2 | - | - |
| N4.2 | - | S3.3 | - | - |
| N4.3 | - | S3.4 | - | - |
| N4.4 | - | S3.5 | - | - |
| N4.5 | - | S3.6, S3.7, S3.8 doc half | - | - |
| N4.6 | - | - | 3.3 inheritance | - |
| N4.7 | §14 errata | - | - | - |
| N4.8 | - | S4.1, S4.2 | - | - |
| N4.9 | - | S4.4 | - | - |
| N4.10 | S4.4 | S4.3 | - | - |
| N5.1 | - | - | 5.1 | - |
| N5.2 | S5.8 (partial) | - | 5.2 | - |
| N5.3 | - | - | 5.3 | - |
| N5.4 | S5.8 (partial) | - | 5.4 | - |
| N5.5 | - | - | 5.5 | - |
| N5.6 | S5.1 | Phase-5 debt | - | - |
| N5.7 | S3.5 rows | - | - | - |
| N5.8 | S5.3 | - | - | - |
| N5.9 | S5.4 | - | 5.6 deferral | - |
| N5.10 | S5.5 (minus 0018) | - | - | - |
| N5.11 | S5.6 | - | - | - |
| N5.12 | S5.7 | - | - | - |
| N5.13 | S5.8 (remainder) | - | - | - |
| N5.14 | - | Phase-5 table | - | - |
| N5.15 | S5.9 | - | - | - |
| N6.1 | - | - | - | db §3.8(5)(7) + D1/D2 |
| N6.2 | - | - | - | db §3.8(1)(3)(4) |
| N6.3 | - | - | - | db §3.8(7) |
| N6.4 | - | - | - | db §3.8(8), §3.3(4) |
| N6.5 | - | - | - | db §3.8(6) |
| N6.6 | - | - | - | db §3.3(5) |
| N6.7 | - | - | - | db §3.4(4)(5) |
| N6.8 | - | - | - | db §3.2(6)(7) + D6/D7 |
| N6.9 | - | - | - | db §3.2(4)(5) |
| N6.10 | - | - | - | db §3.6(1)(3)(4)(6) |
| N6.11 | - | - | - | db §3.6(5) |
| N6.12 | - | - | - | db §3.5(2)(3)(7) |
| N6.13 | - | - | - | db §3.7(7)(8)(9) |
| N6.14 | - | - | - | db §3.1(1)(2) |
| N6.15 | - | - | - | db §3.9(1)(2)(4)(5) + D3/D4 |
| N6.16 | - | - | - | db §3.9(6) |
| N6.17 | - | - | - | db §5 step 6, §4.5 |
| N6.18 | - | - | - | db structural (ND-11..ND-16) |

Excluded as done (§3): master S0.4, S2.1-S2.4, S2.6, S3.1-S3.3, S4.2, S4.3 agent half; plan19 S0.1, S0.3-S0.5, S1.3, S1.6, S1.7, S1.10, S1.11, S1.13, S1.14, S1.16-S1.19, S2.3, S3.1, S4.5; uiux Phases 0-4; four-screen rollout; theme/plate landing.

## 9. Whole-plan verification checklist

At the tag candidate (owner runs or quotes):

```
# tree
git rev-list --left-right --count origin/master...HEAD   # 0 0
python tools/doc_census.py                               # <= 708, can fail
python tools/check_untracked.py                          # clean, can fail
python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140   # exit 0
python tools/adr-check.py docs/adr                       # 0 findings
python tools/verify-backdrop.py                          # 6/6 ok
node tools/ui-framework-check.mjs                        # exit 0, card-in-card fatal
# behaviour
php artisan test --compact                               # full suite quoted
npm run typecheck
vendor/bin/pint --test --format agent <changed paths>
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php tools/lore.php && php tools/lore.php code            # ruling per hit
# schema (scratch DB, never the dev file)
DB_DATABASE=.scratch-uma/<session>/acceptance.sqlite php artisan migrate:fresh --seed
php artisan migrate:status                               # clean on dev DB
# browser (session-private)
PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser
# release (owner)
bash tools/gates.sh
composer audit && npm audit --omit=dev
git tag -l 'v3*'
```

Functional acceptance (plan19 items, still binding; status at this pass):

1. uma:fetch runs to completion skipping enrichment keys - code landed, re-prove at N1.1
2. zero race rows in review queue - OPEN (N2.9)
3. Confirm on non-trainee = 422 never 500; reopen no duplicate - landed, test-pinned
4. bulk dispose Pending by entity filter - OPEN (N2.8)
5. winless native career past turn 12 keeps maiden lock - landed (S1.3)
6. enrich-races skips hand-edited rows and counts them - landed
7. fresh clone seeds tracks/fans/rewards offline - OPEN (N2.3)
8. transferred career cannot re-bank, screen says why - OPEN (N2.10)
9. independent=1 without policy errors, no run exists - landed (S1.7)
10. no overlapping IT sessions; close asks or keeps Active - landed except withoutOverlapping (N2.4)
11. SP budget holds across two posts - landed (budgetRefusal delta)
12. no-scenario run shows no obligations/goals - partial (N2.6)
13. Lv Max priced once everywhere - server landed; planner panels OPEN (N2.5)
14. correction clears stale Failure chip; renumber refused - landed (S1.14/S1.18)
15. gates.sh fails on nested Card / citation over baseline / bad plate - exit codes landed; card-in-card test case OPEN (N0.3)

Snapshot lifecycle acceptance: entry -> setup -> review -> create completes end-to-end (flow spec authored in N3.39; page coverage tests/browser/career-snapshot.spec.ts, 15 cases incl 3 axe scans); the three-state contract holds (known/unknown/not_provided, zero valid Known, no zero-filling); a refused submission preserves entries and unknown states (N3.35); Review->Setup rehydration unchanged; Veteran detail pins stay green (tests/browser/veterans.spec.ts detail cases, VeteranLibraryScreenTest).

Wizard creation acceptance: the six-step flow completes end-to-end (tests/browser/wizard-flow.spec.ts, full-career-journey.spec.ts); backward navigation preserves answers (SetupDraft merge semantics); creation writes one run, resets the draft, redirects to the cockpit with the named flash (tests/browser/career-preflight.spec.ts, CareerPreflightTest); the Independent Training path stays separate with policy requiredIf; percent, pill and per-slot error fixes (N3.19/N3.24/N3.20) pinned by new cases.

Career-workspace acceptance (review §6, narrowed): recorded vs next-turn figures agree with server owners (N3.2); required fields visible, errors near controls, rejected input preserved (existing contract, regression at N1.1/N1.2); nine destinations reachable (N3.1); unknown distinct from zero (absence contract, all Phase 3 slices); keyboard paths (N5.4 pins); no page-wide overflow at 320/390/768/1280 (uiux Phase 1 fix + N1.2 sweep); three themes keep semantics (four-screen theme matrix + N1.2); Pest/Playwright contracts unchanged or inverted with recorded reason.

Database & Help acceptance (db-review §8, narrowed): from any source-backed figure the Trainer reaches the responsible ledger record and back (N6.3/N6.4); guide ratings read as one guide's opinion, never the app's recommendation (N6.10); absence stays N/A with a reason, never zero (N6.6 and every Phase-6 slice); conflicts stay unresolved and the ledger read-only (N6.3 boundary); cadence summaries trace to their observation text (N6.12); Help answers a named question via anchors and documents only wired controls (N6.15/N6.16); nine areas + Help hold at 320/1280 across the three themes (N6.17); Pest/Playwright contracts unchanged or inverted with recorded reason.

## 10. Risks

| Risk | Likelihood | Impact | Mitigation |
| --- | --- | --- | --- |
| Peer commits land slices mid-execution | high (observed: 4 commits during authoring) | stale verdicts, double work | re-verify premise per slice (§6.1); DONE-with-sha exits |
| Shared-tree hunk sweep in commits | medium | peer work committed under wrong author | explicit pathspecs, git diff HEAD -- <path> before/after staging |
| HFW reversal without ruling | medium if review read as authorization | scope violation, spec churn | ND-1 gate; N3.16 unexecutable until ruled |
| Vacuous gate reported green | medium | false release confidence | injection proofs N0.3/N0.4; UNAVAILABLE != clean |
| Browser contention / leaked server | medium | voided sweep, deleted peer DB | session-private env + lock dir; one process at a time |
| Derivation creep (missed states, slot rules, probabilities) | medium | cut-system violation | absence contract; ND-6/ND-7; ADR-0016 hold |
| Register/census figure drift | high | wrong acceptance claims | method named beside every count; dual refs when dirty |
| CI job platform mismatch | medium | red gate on runner | test on existing runner image; minimal job |
| Database review read as HFW authorization | medium | scope violation, parity churn | ND-11..ND-16 gate; N6.18 unexecutable until ruled; parity pins updated only with recorded reason |
| Plan file co-edited by peer sessions | high (observed: wizard fold landed mid-pass) | lost edits, duplicate slices | re-stat before write, asserted patches, changelog row per pass; never rewrite peer sections |

## 11. Measurement log (this pass)

- git rev-parse/log/status/tag/rev-list: baseline table rows (measured).
- python tools/doc_census.py: "ratchet: 528 <= 708", invalid fences 0, most-cited dead targets listed (measured, worktree).
- search_files verified: resources/js/pages/Career/RaceDecision.vue:484 static literal; resources/js/pages/Career/TeamEdit.vue:72 current="team"; resources/js/pages/Career/Cockpit.vue:76 next_turn_to_play; composer.json:12 ^8.4.1 with zero telemetry hits; HFW OVERRIDE comments at the seven cited sites; SCREEN_SPEC.md:4190/:4230 registers (measured).
- git ls-files: tracked/untracked split for citation discipline (measured).
- Reconciler static-read at 25d039c0: 44-slice verdicts, dev-DB SELECTs (304/133/402), four-screen rollout checks (static-read, quoted not re-run).
- Screen-claim reconciliation at 8fd7e18e worktree: 64 claims, 12 refuted (static-read; pivotal lines re-verified above).
- Inherited: EXECUTION-RECORD verdicts, uiux §10/§11 closures, master §16 acceptance text (quoted with sources).
- Database pass at 37320f35: git rev-list 0/13, status 112 (68 M / 6 D / 38 ??), tag list unchanged, census ratchet 528 <= 708 re-run, tracked counts 62 pages / 119 components / 122 browser specs (measured).
- Three-investigator claim test: 74 claims (30 + 44), tally 33 EXISTS / 25 PARTIAL / 12 MISSING / 4 NEEDS-DECISION; Sources payload measured (13+4=17 rows, 4 conflicts, verified 2026-11-02); cadence/mechanics string lengths measured; importer counts re-measured (AbsenceValue 36, ProvenanceBadge 27, PageHeader 39, DataTable 3) (static-read + measured).
- Pivotal lines re-verified live by this pass: resources/js/pages/Database/Sources.vue:72-77/:231/:266/:278-287, resources/js/pages/Help.vue:17-19 vs resources/js/pages/Career/TrainingDetail.vue:464-497, resources/js/pages/Database/Races.vue:372-378, resources/js/pages/Database/Scenarios.vue:229, resources/js/pages/Database/ShopItems.vue:211-222, app/Http/Controllers/DatabaseController.php:470-497/:301, resources/js/layouts/AppLayout.vue:20-32, resources/js/components/catalog/TraineeCatalog.vue:399-403, resources/js/pages/Catalog/Show.vue:207, docs/adr/0030-add-base-stats-and-growth-rates-to-character-cards.md contract (accepted, built, 263/263), SCREEN_SPEC.md:110/:1275/:1300/:1303 and §4.26 region (measured/static-read).
- Not run this pass: Pest, Playwright, phpstan, pint, lore, builds, audits (documentation-only pass owes no full sweep; gates run at execution per §6).

## 12. Change log

| Date | Change | Reason |
| --- | --- | --- |
| 2026-11-20 | Database & Help workstream folded in: 74 claims tested at 37320f35 (33 EXISTS / 25 PARTIAL / 12 MISSING / 4 NEEDS-DECISION), Phase 6 N6.1-N6.18, ND-11..ND-16, N2.11 line-ref corrected (:437 to :470, controller now clean), N4.6 database errata added, KI-152 index/body drift flagged for N0.5; peer wizard fold (N3.19-N3.29, ND-9) and snapshot/veteran fold (N3.30-N3.40, ND-10) preserved untouched; database decisions numbered ND-11..ND-16 around the peer ND-10 | owner supplied the Database & Help review and named this file |
| 2026-11-20 | Snapshot/veteran workstream folded in: 41 claims tested at 37320f35, 11 slices N3.30-N3.40, ND-10 ruling package (G1-G8), snapshot/veteran exclusions and lifecycle acceptance added; plan corrections (AbsenceValue 40 importers, three Form Requests tracked, SnapshotReviewRequest committed 4a257e66) | owner supplied the Snapshot & Veteran review and named this file |
| 2026-11-20 | Wizard workstream folded in: 64 claims tested at 37320f35, 11 slices N3.19-N3.29, ND-9 ruling package, N3.11 extended (Remove safeguard + purpose grouping), wizard exclusions and acceptance added; peer Race Spark work flagged | owner supplied the New Career Wizard review and named this file |
| 2026-11-20 | Citation expansion (28 basename:line tokens given directories), peer rename docs/plans/ to docs/plan/ recorded, plan19 S1.11/S3.1 committed mid-authoring | mechanical verification pass |
| 2026-11-20 | Created: consolidated execution plan over master roadmap remainders, plan19 open halves, uiux Phase 5, and the claim-tested 2026-11-20 career review; 63 slices, crosswalk, owner register ND-1..ND-8 | owner ask: comprehensive dependency-ordered plan in docs/plans |
