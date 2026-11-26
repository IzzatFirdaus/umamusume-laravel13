# Check #7 — Historical Failure Triage (2026-11-26)

## Run
- **Revision**: master `6e17dca0` (code identical to current `b9a714f2` after docs-merge code restore).
- **Command**: `env -u CODEBUDDY_MCP_CONFIG -u CODEBUDDY_SKILL_DESC_MAX_OVERRIDES php vendor/bin/pest --compact`
  (env trim required: Windows 32767 UTF-16 env-block limit crashed the Pest browser/Playwright subprocess —
  environmental, not a code failure).
- **Result**: **50 failed, 39 skipped, 2883 passed (41113 assertions)**, PEST_EXIT=2.

## Historical comparison
- 2026-11-25 comprehensive run @ `11207c42`: 1,973 failed / 938 passed with **65 unstaged modified files**.
- Current integrated master: **50 failed / 2883 passed**. The 65 unstaged files were the dominant
  historical cause; integrating Task#12 + Check#6 + frontend + docs removed the bulk. The earlier
  "1,973" is NOT a current failure count.

## Failure classification (50)
Exception types:
- 12 × `BadMethodCallException` (ScenarioImmutabilityTest cluster — scenario change guards)
- 10 × `RouteNotFoundException` (ScratchAdvVerify + scenario routes)
- 9 × `Failed asserting that false is true`
- 4 × `ErrorException` (DeckFixtureSeparation)
- 3 × string/array identity mismatches (UiHygiene / RenderedCopyHygiene — em/en-dash, repository id)
- 2 × `31 is identical to 29` (query-budget / filter count)
- 1 × null-vs-string (SeededProvenanceNoteRefresh)

Affected files (unique, ~25): ScenarioImmutabilityTest, SeededProvenanceNoteRefreshTest (7),
UiHygieneTest (2), RenderedCopyHygieneTest, DeckFixtureSeparationTest (2), CockpitQueryBudgetTest (2),
TraineeFilterPersistenceTest (3), TurnEditTest, TrainingRunTest, FilterContractTest, EnumLabelTest,
LoreGateParityTest, IndependentTrainingLifecycleTest, ScratchAdvVerifyTest, WizardDeckErrorPathTest.

## Disposition
- **Not release-qualified** until the 50 are individually triaged and fixed or classified.
- Clusters suggest a small number of root causes (scenario immutability API `BadMethodCallException`,
  missing/renamed routes, em-dash copy hygiene) rather than 50 independent defects.
- Next: per-cluster root-cause (start ScenarioImmutabilityTest `BadMethodCallException` — 12 failures,
  likely one missing method/route). Full failure list: `.scratch-uma/check7-failures.txt`.

Full historical game-domain audit not repeated. No canonical DB access (suite uses `:memory:`).