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

## Phase 0.2 — round-1 claims re-verified (2026-10-27)

Base worktree: `D:\Projects\td-base` created from `86127192` **before any code change**, with
`node_modules` and `vendor` junctions (Windows' equivalent of the symlink the instruction names) and
`storage`/`.env` copied; `npm run build` in the base → `✓ built in 26.67s`.

| Spec | Base (`86127192`) | Working tree | Attribution |
|---|---|---|---|
| `career-training-detail.spec.ts` | 20 of 21 pass; `:149` fails | `:92` and `:149` fail | `:92` stops at `tests/utils/start-career.ts:97` (`Save target` not found) — peer WIP in `resources/js/pages/Career/BuildTarget.vue` (mtime 02:13:09). `:149` is a stale copy pin; see below. |
| `career-deck-select.spec.ts` | **14 passed / 0 failed** | not re-run (peer-dirty `DeckSelect.vue`) | round 1's claim holds |
| `runs-create-forms.spec.ts:48` | **1 passed** | **1 passed** | premise stale; nothing to attribute |

`career-training-detail.spec.ts:149` is a real pre-existing failure at `86127192`: it pinned
`E−28 … E−17` / `E−0`, and commit `85d24959` re-spelled the *derived* cost row to
`−28 … −17 Energy` / `0 Energy` while leaving the server's published rows on `E−21`/`E−0`. The spec is
rewritten against the shipped copy (same count, same worst-first order, no assertion removed) in this
session's slice-1 commit. Round 1's claim was wrong in exactly this one hunk.

## Phase 0.3 — `runs-create-forms.spec.ts:48` attribution (2026-10-27)

- At base `86127192` (`D:\Projects\td-base`, fresh build): **pass** (`1 passed (50.0s)` with `:92`).
- At the working tree `e4a6e2fb` (fresh build): **pass** (`1 passed (33.9s)`).
- `fail → pass` is not the shape here; it is `pass → pass`. Per the instruction's own rule ("if it was
  stale, note it and move on"), the 48th line is not a defect on this tree. Nothing to bisect.
- The `../td-base` worktree is removed at the end of this session (recorded in Phase 7.7).

## Phase 0.4 — `lint:ui` (2026-10-27) — Path B

- Base `86127192`: `npm run lint:ui` finishes with **no `[debt-increase]` line** → the committed tree is
  at the 219 baseline.
- Working tree: `resources/js:0 [debt-increase] arbitrary-value count 220 exceeds baseline 219`, the
  220th hit being `resources/js/pages/Review/Index.vue:300`
  (`[minmax(0,1fr)_22rem]`). That file is peer-dirty (`git status` ` M`, mtime 02:37:50), so the
  conditional bump is **not** lawful: the baseline records a clean tree, and this tree is not one.
  Path B taken; the baseline is left at 219.

`lint:ui — blocked by peer, resources/js/pages/Review/Index.vue:300, last peer edit 2026-10-27 02:37:50`.

## Phase 0.5 — TraineeProfile growth row

`blocked — TraineeProfile.vue uncommitted, last peer edit 2026-10-27 01:44:01`.
Evidence: `git status --short resources/js/pages/Career/TraineeProfile.vue` → ` M resources/js/pages/Career/TraineeProfile.vue`. No code change made; no spec assertion added, because a
spec written against a file the session does not own would pin a peer's in-flight shape.

## Phase 0.6 — Veterans close-out

`blocked — Veterans/Index.vue uncommitted, last peer edit 2026-10-27 01:57:52` and
`blocked — Veterans/Show.vue uncommitted, last peer edit 2026-10-27 02:01:16`.
Evidence: `git status --short` → ` M resources/js/pages/Veterans/Index.vue`, ` M resources/js/pages/Veterans/Show.vue`.
The two committed Veterans slices (`b59ab9f2`, `367a7e6c`) already landed the eligibility arithmetic and
the five final stats, so what remains is the index factor-grade columns and the detail reorder — both
inside the peer-dirty files and therefore not this session's to edit.

