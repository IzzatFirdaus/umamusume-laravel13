# TASKS — Trainer Desk HFW v3.0.0 remainder

Evidence ledger for the v3.0.0 remainder instruction. One section per numbered step.
Session: `hf-remainder-2026-10-27`.

## Phase 0.1 — tree state check (2026-10-27 02:47 +08)

- `git rev-parse --short HEAD` → `e4a6e2fb`
- `git branch --show-current` → `master`
- `git log --oneline -10` →

```text
e4a6e2fb test(race-decision): pin the comparison table and the refused row's visible reason
b59ab9f2 feat(veterans): do the team-eligibility arithmetic on the page and state the star ceiling (S7b)
380bf50a test(database): assert the N/A note the sweep introduced, not the removed titles
367a7e6c feat(veterans): read the payload's final five stats, absence named per stat (S7b)
d62db9cc fix(races): print the held-figure reasons and the absent-turn reason (12.1 sweep cont.)
2f4b32da fix(copy): print reasons as text, not as hover-only titles (12.1 sweep)
f0caa281 feat(inheritance): print all five spark families per parent, absence named (S6.2, DM-3)
4b80f533 feat(skills-planner): price the base cost and name why the ladder is absent (S6.2, DM-4)
e69984e2 feat(race-decision): compare the turn's races in one table, refusals on the row (S6.1b, Path B)
6e4e6896 test(s6.3): pin phase grouping, blank-is-not-zero, the finale outside the grid and one accented tile
```

- `git status --short` (verbatim, 19 modified + 7 untracked at the time of measurement):

```text
 M app/Http/Controllers/Career/BuildTargetController.php
 M app/Http/Controllers/ReviewController.php
 M resources/js/layouts/SetupLayout.vue
 M resources/js/pages/Career/BuildTarget.vue
 M resources/js/pages/Career/DeckSelect.vue
 M resources/js/pages/Career/LegacySelect.vue
 M resources/js/pages/Career/Preflight.vue
 M resources/js/pages/Career/ScenarioSelect.vue
 M resources/js/pages/Career/TraineeProfile.vue
 M resources/js/pages/Career/TraineeSelect.vue
 M resources/js/pages/Review/Detail.vue
 M resources/js/pages/Review/Index.vue
 M resources/js/pages/Veterans/Index.vue
 M resources/js/pages/Veterans/Show.vue
 M tests/browser/career-legacy-deck-steps.spec.ts
 M tests/browser/career-preflight.spec.ts
 M tests/browser/career-scenario-select.spec.ts
 M tests/browser/career-trainee-select.spec.ts
 M tests/browser/dashboard.spec.ts
?? .scratch-deck.diff
?? .scratch-lint.txt
?? .scratch-preflight.diff
?? .scratch-state.txt
?? .scratch-trainee.diff
?? tests/Feature/ReviewDetailPayloadTest.php
?? tests/browser/annotation-sweep.spec.ts
```

- Peer mtimes measured at 2026-10-27 02:47:39 +08 (KI-70 before-edit record):

```text
2026-10-27 01:44:01  app/Http/Controllers/Career/BuildTargetController.php
2026-10-27 01:44:01  app/Http/Controllers/ReviewController.php
2026-10-27 01:44:01  resources/js/layouts/SetupLayout.vue
2026-10-27 02:13:09  resources/js/pages/Career/BuildTarget.vue
2026-10-27 01:44:01  resources/js/pages/Career/DeckSelect.vue
2026-10-27 02:01:21  resources/js/pages/Career/LegacySelect.vue
2026-10-27 01:44:01  resources/js/pages/Career/Preflight.vue
2026-10-27 01:44:01  resources/js/pages/Career/ScenarioSelect.vue
2026-10-27 01:44:01  resources/js/pages/Career/TraineeProfile.vue
2026-10-27 01:44:01  resources/js/pages/Career/TraineeSelect.vue
2026-10-27 01:44:01  resources/js/pages/Review/Detail.vue
2026-10-27 02:37:50  resources/js/pages/Review/Index.vue
2026-10-27 01:57:52  resources/js/pages/Veterans/Index.vue
2026-10-27 02:01:16  resources/js/pages/Veterans/Show.vue
2026-10-27 02:02:20  tests/browser/career-legacy-deck-steps.spec.ts
2026-10-27 01:51:13  tests/browser/career-preflight.spec.ts
2026-10-27 01:50:48  tests/browser/career-scenario-select.spec.ts
2026-10-27 01:51:13  tests/browser/career-trainee-select.spec.ts
2026-10-27 01:44:01  tests/browser/dashboard.spec.ts
```

Consequence for this session's plan: `Review/Index.vue`, `Veterans/Index.vue`, `Veterans/Show.vue` and
`TraineeProfile.vue` are foreign-dirty, so Steps 0.4 (Path B), 0.5 and 0.6 are filed blocked rather than
edited. Peers were observed writing `Review/Index.vue` nine minutes before this header was taken.
