# Audit corrections — AUD-database-2026-11-01 (pass 2)

Dated 2026-11-02. Records five defects found in the audit report during the fix pass, plus the
dispositions taken for F-08, KI-147 and PHPStan. The audit report itself (`docs/audits/AUD-database-2026-11-01.md`)
is not edited in this pass (owner docs-bar); these corrections are the tracked record until the bar lifts.

## Defects in the audit report

### 1. F-07 per-surface count varies
- **Location:** audit §2 ("eleven times per page") vs §5 F-07 ("filed once as systemic").
- **Defect:** the same finding is counted two different ways in two sections.
- **Correction needed:** pick one count and state it consistently; if systemic, drop the per-page number.

### 2. §5 severity order is non-monotonic
- **Location:** audit §5.
- **Defect:** sequence reads `F-01..F-10 = 2/High`, `F-11 = 1/High`, `F-13 = 2/High`, `F-12 = 1/High`.
- **Correction needed:** order the twelve severity-2 rows (F-01..F-10, F-13) by breadth, then F-11 and F-12.

### 3. §8 effort order (S-M before S)
- **Location:** audit §8 item 4 precedes item 5.
- **Defect:** effort S-M listed before S, against the stated severity → confidence → effort rule.
- **Correction needed:** reorder so S precedes S-M.

### 4. Three §8 recommendation premises disproved by the fix pass
- **Location:** audit §8.
- **Defects:**
  - F-03's "existing dual-count pattern on `/database/supports`" does not exist; the door grammar is
    `{{list.total}} of {{totalCount}}` (`SupportCardCatalog.vue:202`). Corrected in `ed7f01e3`.
  - F-08's filter remedy zeroes every factory-seeded roster test (`CatalogRosterTreeTest.php:219`); the
    finding is real (rows render) but the remedy is data-hygiene (purge), not the read-path filter.
    Disposition: fixed via purge (pass 2).
  - F-06's "second card" annotation: distance bands are read off `race_catalog_slots` and the export's
    course metres, so annotating them would misattribute fetched figures. Only the payout card annotated
    (`00dd6d1b`).

### 5. §7 "three Open conflicts" stale
- **Location:** audit §7 (line ~248 of the plan's record).
- **Defect:** says "the three Open conflicts entries"; the ledger prints four since `0174519c`.
- **Correction needed:** update to "four Open conflicts" (F-05 reframed as config defect in `1ce490cb`).

## Dispositions

### F-08 — real finding at severity 1 (dev-data-only); remedy is data-hygiene purge
- **Corrected disposition (pass 2):** the four `Form Counted 1..4` rows **do** render at
  `/database/trainees?search=Form%20Counted` with live detail URLs, and the screens do not distinguish
  them from real trainees. That is a real finding. The audit's proposed remedy (read-path
  `whereNotNull('external_ref')`) is wrong because it would break `CatalogRosterTreeTest.php:219`, which
  intentionally creates trainees without `external_ref`. A wrong remedy does not make the finding false.
  The finding is real; the remedy is data-hygiene (purge the rows), not a read-path filter.
- **Evidence:** `CatalogRosterTreeTest.php:180-230`; dev rows id 68-71 (external_ref null, no pipeline source)
  rendered at `/database/trainees?search=Form%20Counted`.
- **Action taken:** purged all four rows from the dev DB (4 deleted, 0 remaining). Documented in
  `KNOWN-ISSUES.md` as a data-hygiene entry so a future reader understands why the read-path filter
  cannot be used.
- **F-08 closes as:** fixed via data purge. No code change. Read path remains correct.

### KI-147 — delete only the spec
- **Decision:** reading (c). `RunRaceStrip.vue` is live, tested (17 Feature tests in `RunRaceStripTest`),
  and consumed by `Cockpit.vue:561→1011`. The browser spec `career-race-strip.spec.ts` exercised a mount
  removed at `3d8c5ee2`; it is dead (5 cases failed with no region). Deleted the spec; component stays.
- **Evidence:** `grep -r "career-race-strip"` returns only docs/KNOWN-ISSUES/SCREEN_SPEC references;
  `RunRaceStripTest` 17 tests pass.
- **KI-147 closes as:** spec deleted; no dispositions for the component remain.

### PHPStan — accept as pre-existing red
- **Decision:** the 11 errors are pre-existing. None of the 11 fix-pass commits touched the affected files
  (`InheritanceEventController`, `PreflightController`, `ScenarioSelectController`, `DashboardController`,
  `LegacyController`, `TrainingRunController`, `IndependentTraining/Lifecycle`, `IndependentTraining/Simulator`).
- **Evidence:** `git log 7e9df158..ba335bb4 --name-only` does not include any affected file.
- **Gate status:** red, documented. `vendor/bin/phpstan analyse` exits 1 with 11 errors, all pre-existing.

## F-13 provenance
F-13 was discovered during the fix pass's 320px measurement (support-cards document scroll 248px → 0
after `relative` on the scroller, `174938b4`). It is a new finding from the fix pass, not from the
audit's findings table (the audit's §6 had this surface's reflow as UNMEASURED). Corrected in the
progress report; the audit report's findings table is not extended.

## Phase 2 (Preferences) status
`Preferences/Edit.vue` is peer-owned and uncommitted. Source verified line by line; acceptance gates
unrun in this pass beyond source inspection. Status: source present, gates unrun, awaiting peer commit.
Not committed by this pass.

## Phase 4 status
KI-147 disposition (c): spec deleted. Component live and tested. No code change to the component.
`npm run typecheck` and `npm run test:browser` (targeted) confirm no new failure.
