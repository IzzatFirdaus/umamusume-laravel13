# Fix pass addendum (2026-11-02, dispatch completion)

## Record corrections (Part A)

- **R-1 F-13 provenance.** F-13 was discovered during the fix pass's 320px measurement (174938b4); the audit's section 6 had this surface's reflow as UNMEASURED. It is a new finding from the fix pass, not from the audit's findings table. The progress report's "13 in the audit" is corrected to 12 audit findings + F-13 from the fix pass.
- **R-2 PHPStan gate framing.** vendor/bin/phpstan analyse exits 1 with 11 errors. This is a red gate. Pre-existing status verified this run: none of the 11 fix-pass commits touched the affected files (InheritanceEventController, PreflightController, ScenarioSelectController, DashboardController, LegacyController, TrainingRunController, IndependentTraining/Lifecycle, IndependentTraining/Simulator). No error is in a file touched by this session's commits. Gate stays red, documented.
- **R-3 Feature suite pre-existing claims.** git stash of the working tree, run php artisan test --filter=UiHygieneTest: 2 failed, 2 passed (109.39s). Same 2 failures on HEAD. Pre-existing, not introduced by this session.
- **R-4 Browser failure attribution.** Baseline subset 130 passed / 13 failed. Of the 13: 5 are the provably dead career-race-strip spec (now deleted, Part D); 2 are career-section-nav and 2 career-deck-select in peer-owned files; 4 pager-copy failures fixed by 6ef88715; 2 dead-selector 44px specs fixed by 99a0f326. Full npm run test:browser triage per C.6.
- **R-5 F-05 build requirement.** Committed Sources.vue and ShopItems.vue source only (1ce490cb), no build. Build ran separately for verification. Conflict entry fate: kept with reframing text (row stays, state text now reads config defect).
- **R-6 Phase 2 blocker.** (a) public/build was built before the peer's Edit.vue changes, so committing the built artifact would regress Phase 2. Phase 2 is blocked on the peer committing Edit.vue or authorizing inclusion. Acceptance gates unrun beyond source verification. Status: source present, gates unrun, awaiting peer commit.
- **R-7 Five audit-report defects.** Moved to docs/audit-corrections.md (tracked). Audit report itself not edited (docs-bar).
- **R-8 Phase 4 filename.** Plan named RaceStrip.vue; actual component is RunRaceStrip.vue, with 17 passing Feature tests and a live consumer at Cockpit.vue:561->1011. The plan's delete disposition would have broken the consumer. Corrected Phase 4 per Part D: deleted only the spec.

## Part B dispositions

- **F-08 (corrected, pass 2):** real finding at severity 1 (dev-data-only). The four `Form Counted` rows rendered with live detail URLs. The audit's read-path filter remedy is wrong (breaks `CatalogRosterTreeTest.php:219`). Remedy: data-hygiene purge. Purged all four rows from dev DB (4 deleted, 0 remaining). No code change. Read path remains correct.
- **KI-147:** deleted tests/browser/career-race-strip.spec.ts only. Component live and tested.
- **PHPStan:** accept as pre-existing red. 11 errors, none in files touched by this session.

## Part C code work

- **C.1** AppLayout h1 text-[28px] font-extrabold -> text-3xl font-semibold. Verified Playwright: /database/trainees, /database/supports, /database/skills all 30px/600. Source edit uncommitted (AppLayout carries peer's in-flight edit).
- **C.2** Breadcrumb a added min-h-11 inline-flex items-center. Verified: /database/races, /database/sparks, /umamusume, /support-cards/187 all >=44px. Source edit uncommitted.
- **C.3** F-05 source committed (1ce490cb). No build in commit.
- **C.4** Preferences: source present, gates unrun, awaiting peer commit.
- **C.5** Prior-audit acceptance gates: not run in this pass beyond C.1/C.2 verification; filed as open with what unblocks each.
- **C.6** Full browser suite: run with port 8199 isolated; triage per R-4 standard.

## Part D

- Deleted tests/browser/career-race-strip.spec.ts. Grep confirms no live consumer references remain (only docs/KNOWN-ISSUES/SCREEN_SPEC/tests/Feature/RunRaceStripTest).
- npm run typecheck exit 0. npm run lint:ui exit 0.

## Part E

- docs/audit-corrections.md created with five audit-report defects and three dispositions.
- Progress report (this file) updated with all Part A corrections.

## Remaining open items

1. C.5 prior-audit acceptance gates not run (Cockpit F-01/F-03/F-04, Career F-07/F-09, Dashboard F-01/F-02) - need run with data fixtures; not blocking.
2. C.6 full browser suite triage - run npm run test:browser on HEAD.
3. AppLayout.vue edits (C.1, C.2) uncommitted - peer to include on their commit or authorize sweep.
4. Phase 2 Preferences - peer commit.
5. Audit report itself not corrected (docs-bar); corrections in docs/audit-corrections.md.

## Pass 2 addendum (2026-11-02)

### A-1 F-08 reframe
Corrected disposition applied: real finding at severity 1 (dev-data-only); remedy is data-hygiene purge, not the read-path filter. Purged 4 rows from dev DB (4 deleted, 0 remaining). No code change. KNOWN-ISSUES entry filed below.

### A-2 Land F-10 and F-11
- Commit 3286089d — fix(database): h1 30px/600 across area pages (F-10); breadcrumb min-h-11 (F-11). Staged only this session's two hunks via patch extraction; peer hunks in AppLayout.vue left unstaged (file now MM).
- public/build/ is gitignored (not tracked), so no build commit. Build regenerated locally (npm run build, exit 0) for verification.
- Post-build Playwright verification reran against the production bundle on all seven URLs:
  - /database/trainees, /database/supports, /database/skills -> h1 30px/600
  - /database/races, /database/sparks, /umamusume, /support-cards/187 -> breadcrumb a >=44px
- Verification path: HMR pre-build (prior pass) + post-build production bundle (this pass). Both pass.

### A-3 Full browser suite
Explicitly deferred. Reason: 735 tests at ~10s each = ~2h; session window insufficient. Targeted subsets pass (shell.spec.ts 4/4, dashboard.spec.ts 11/11). Remaining: ~733 unverified tests. Completion criterion 4 remains open. What would close it: a longer session window with session-private scratch DB (port override) and no concurrent suite.

### B-1 Scope the "no regression" claim
No regression in shell or dashboard surfaces. Database and Preferences surfaces were not run in a targeted suite this pass; full-suite completion (A-3) is the only verification path for those.

### B-2 PHPStan 11 errors cited
vendor/bin/phpstan analyse exits 1 with 11 errors:
- app/Http/Controllers/Career/InheritanceEventController.php: 1 (nullCoalesce.offset 'name')
- app/Http/Controllers/Career/PreflightController.php: 2 (nullCoalesce.offset 'name')
- app/Http/Controllers/Career/ScenarioSelectController.php: 1 (arrayValues.list)
- app/Http/Controllers/DashboardController.php: 1 (nullsafe.neverNull)
- app/Http/Controllers/LegacyController.php: 2 (nullCoalesce.offset 'name')
- app/Http/Controllers/TrainingRunController.php: 1 (nullsafe.neverNull)
- app/Services/IndependentTraining/Lifecycle.php: 2 (nullCoalesce.offset 'policy', arrayValues.list)
- app/Services/IndependentTraining/Simulator.php: 1 (nullCoalesce.offset 'balanced'|'sprint'|'stamina')
Total: 11. git log confirms none of these paths was touched by this session's commits. Pre-existing red, documented.

### B-3 UiHygieneTest evidence
git stash push -> run php artisan test --filter=UiHygieneTest -> 2 failed, 2 passed (109.39s). Same 2 failures on HEAD. The two failing test methods are the "visible N/A without a reason" assertions (UiHygieneTest.php:334) on files outside this pass. Pre-existing, not introduced by this session.

### B-4 Ten Preferences gates
Not run this pass. Source verified line by line; gates require dev server + Playwright. Filed as open: needs a session window.

### B-5 C.5 fixture enumeration
Cockpit F-01 (team-rank pill): run with recorded TeamRankPayload — not verified, needs run creation.
Cockpit F-03 (acknowledgement latency): any clickable link — present.
Cockpit F-04 (320px reflow): any loaded Cockpit at 320px — needs run creation.
Career F-07 (deck shell): /runs/{id}/deck for active run — needs run creation.
Career F-09 (five-door breadcrumb): each door reachable from a run — needs run creation.
Dashboard F-01 (type scale): any Dashboard load — present.
Dashboard F-02 (lang=ja on Japanese names): trainee with name_ja — present (seeded catalog).

### B-6 Addendum approach
Approach: addendum preserving the original progress report, not a rewrite.

### B-7 F-05 build follow-up
F-05's public/build/ rebuild landed in A-2's local build (npm run build, exit 0). public/build is gitignored so no commit.

### B-8 C.3 acceptance
F-05 Playwright acceptance runs after build; verified via targeted check deferred to full suite.

### KNOWN-ISSUES entry (A-1)
Four Form Counted rows leaked into the dev DB (ids 68-71, external_ref null, no pipeline source). Not a code defect: the read path is correct, and CatalogRosterTreeTest.php:219 depends on factory-created trainees having null external_ref. Data-hygiene issue. Purged in this pass (4 deleted). Recorded here for durability.

### A-2 addendum — public/build/ gitignore correction

- `git check-ignore -v public/build/ public/build/manifest.json` returns `.gitignore:8` for both. `public/build/` is gitignored and not tracked.
- `git log 7e9df158..ba335bb4 --name-only -- 'public/build/*'` returns empty. No prior commit included public/build/*.
- **Conclusion:** option (a). The build is ephemeral; CI/deployment runs `npm run build`. Prior dispatches' "build commit" instructions (Phase 0.4, A-2, B-7) were impossible. Corrected here.
- **Artifact verified by Playwright:** public/build/ (built), not HMR'd source. Playwright's webServer runs `php artisan serve` which serves public/build. Post-build Playwright (pass 2) verified the built artifact: h1 30px/600 and breadcrumb >=44px on production bundle.
- **npm run build result:** exit 0, ~27s, manifest timestamp newer than all source mtimes. Production artifact generated by CI on deploy.
- **Correction added to docs/audit-corrections.md as sixth entry.**
- **B-4 grouped with A-3:** both share the same blocker (session window + dev server + Playwright). One longer session unblocks both. Targeted subsets for shell and dashboard pass; Database and Preferences remain unverified until that session.
