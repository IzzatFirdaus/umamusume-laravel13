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

## Phase 1 — Slice 3: Careers list & import (2026-10-27)

Premise measurement first: `S3a`/`S3b`/`S3c` had already landed (`f0e89dbd`, `b8eb3216`), so most of
Slice 3 was satisfied before this session. Each bullet was classified before anything was edited.

### `Runs/Index.vue` (step 1.1)

| Bullet | State | Evidence |
|---|---|---|
| filters are one inline form row, no drawer | already satisfied | `Runs/Index.vue:110-176`, one `<form role="search">`, no drawer component |
| HFW's other three controls | already satisfied | Search, Scenario, Status, Apply, Clear read from `runs-index.html:71-113` |
| a **Sort** control (the instruction's fourth control; the HFW's filter block draws none) | **gap → landed** | `RunSearchRequest` gains `sort` (`newest`/`oldest`/`trainee`), `TrainingRunController@index` applies it in SQL and echoes it, `Index.vue` renders the select |
| `class="is-current"` on the active row | **gap → landed** | the HFW's class name now ships beside the tint |
| pill word + position, never colour alone | already satisfied | `StatusPill` prints the status word; the Position cell prints the named position |
| turn is `N / 72` | already satisfied | `turn_count` is `CareerCalendar::TURNS_PER_CAREER` in the controller, printed on the active row |
| retired row says `Retired at turn N`, not `Finished` | already satisfied | `Index.vue` position cell |
| no destructive action in a row | already satisfied | no delete control on the page |
| pagination under the table | already satisfied | `TinyPager` after the table |

Order note: the instruction lists "Search, Status, Scenario, Sort, Apply"; the HFW draws Search,
Scenario, Status. The HFW's order was kept (it is the acceptance criterion) and Sort added before Apply.

### `Runs/Create.vue` (step 1.2)

Already satisfied against `runs-create.html:55-116`: eyebrow `New career`, `h1` "Create a career", the
HFW's own lede sentence, one `Career` card, one filled action "Create run", Cancel as an outline link.
One gap landed: the Notes label now reads "Notes (optional)", matching the HFW's label (and the two other
optional fields already on the page).

### `Runs/Import.vue` (step 1.3)

Already satisfied against `import.html` / `import-preview.html`: the paste box is the first and largest
control (`name="csv"`, `rows=10`) above the upload path, the format card is visible prose ("What the
importer looks for"), confirm is the only filled action and nothing writes before it, and every preview
field carries a `found`/`missing` tag with a sentence naming what to do. One honest limit, recorded
rather than papered over: the status field's `missing` sentence quotes the valid set
(`Choose Active, Completed or Retired`), but that branch is **not reachable through the form** — the
status select always carries one of the three and `ImportHistoricalRunRequest` requires it. The spec
therefore pins the valid set where it is observable (the select's own options) and the reachable
`missing` case (Notes).

### Verification

Isolated worktree `D:\Projects\td-slice1` at `86127192` + only this slice's diff, `npm run build`:

- `runs-index.spec.ts` (new) — **2 passed**, 44.3s. One career is started through the wizard and deleted
  through `runs.destroy` in `afterEach`, so the scratch database is left as it was found.
- `runs-create.spec.ts` (new) — **1 passed**.
- `run-import.spec.ts` (two cases added) — **8 passed**.
- `career-training-detail.spec.ts` — **21 of 21 pass** after the copy pin was corrected (Phase 0.2).
- `php artisan test --filter="RunsIndex"` — **14 passed (226 assertions)**.
- `runs-create-forms.spec.ts:48` — **1 passed** (Phase 0.3).

## Phase 2 — Slice 5: Cockpit (2026-10-27)

Premise: the shell and the four scenario panels had already landed (`2ba54a61`, *feat(cockpit): HFW base
layout and scenario panels (S5)*). Each bullet was checked against the tree before editing.

| Bullet | State | Evidence |
|---|---|---|
| 2.1 shell order: identity card, then the two-column grid, then the race strip, then the scenario panel | already satisfied | `Cockpit.vue` renders `CockpitIdentityCard` (line 865) before the `@4xl:grid-cols-[2fr_1fr]` body; `RunRaceStrip` sits after it |
| 2.1 one primary action, the same one the Next Decision card offers | already satisfied | `headAction` is the decision's own action (`<LinkButton variant="primary">`, line 820) |
| 2.1 finale countdown inside the scenario card | already satisfied | `Cockpit.vue:1007` draws it in the scenario card, and `finaleTurnsAway` is the only countdown source |
| 2.1 advisor rail `v-if="isActive"`, no empty rail on a Completed run | already satisfied | `Cockpit.vue:1035`; `ActionGrid` and the timeline heading are gated the same way |
| 2.2 URA: outcome first on Completed, entered caps where they differ, no advisor rail | already satisfied | `ura-panel.spec.ts` (green in the batch below); `capComposition` carries the run's entered caps |
| 2.3 Unity Cup: five `.srow` bars, current row pill, "Where to go" | already satisfied | `unity-cup-panel.spec.ts` (green below) |
| 2.4 Trackblazer: shortfall as lead figure, currencies separated in one sentence, no `.mockbar` | already satisfied | `TrackblazerPanel.vue:610-617` separates Grade Points from Shop Coins in one sentence; `grep mockbar resources/js` → no hits |
| 2.5 Grand Concert: loop explained, five tiles with honest absences, payouts stated | already satisfied | the reading is drawn inline in `Cockpit.vue:961` and explained at `Cockpit.vue:441-450`; `grand-concert-panel.spec.ts` green below |
| **2.6 turn-count discrepancy labelled on both pages** | **gap → landed** | the Dashboard already shipped `position_turn` beside `turn`; the cockpit now ships `header.logged_turns`/`header.next_turn_to_play` and prints both when they disagree (`data-field="turn-discrepancy"`) |
| 2.4/2.5 the HFW's `.mockbar` warning stated as a `.note` | **deviation, filed** | `.mockbar` is absent from every shipped component. The suggested sentence names a verification date (2026-09-27) that this session did not perform and that no ledger row records for these two areas' support data, so printing it would assert a verification that did not happen |

Verification: `php artisan test --filter="Cockpit"` → **128 passed (1728 assertions)**. The four variant
specs ran in the isolated worktree with this session's diff applied — result recorded in the gate block
at the end of this file.

**Browser gap recorded, not hidden:** `tests/browser/turn-count-discrepancy.spec.ts` was **not** written.
The disagreement state needs a run whose stored position is ahead of its recorded rows, and the only
surface that writes a stored position is the snapshot flow; driving it from a browser spec would be a
second copy of `career-snapshot.spec.ts`'s walk. The pair is pinned where it is actually written —
`CareerCockpitTest` and `DashboardTest`, one case each, both asserting the same two figures.

## Phase 3 — Slice 7 remainder: Legacy Lab (2026-10-27)

Premise: `bd1b2d73` landed the Legacy Lab index; the builder and compare pages predate it.

| Bullet | State | Evidence |
|---|---|---|
| 3.1 saved pairs are the unit; three-row value table; Compare is the filled action | already satisfied | `Legacy/Index.vue:94-97` (Compare `variant="primary"`), `:251-286` (the three rows and the two-family note) |
| 3.2 two parent slots, one pick each, no multi-select | **deviation, filed** | `legacy/AncestryNode.vue:140` renders one `<select>` per slot — a single-pick control, never a multi-select. The HFW's radios pick one **spark row** inside a parent, not a parent, and the app records whole spark lists; re-shaping that is a change to the stored `legacy_selection` payload, beyond a UI slice |
| 3.2 dashed empty slot + "slot open", "Skip this moment" | **not applicable** | neither phrase exists in any Legacy wireframe: `grep -i "skip this moment\|slot open\|dashed" docs/design-research/frontend-v2-wireframes/legacy*.html career-legacy.html` → no matches. They belong to no HFW page, so they are not invented onto one |
| 3.2 star count rolled, never a choice | already satisfied | the builder takes the star count as a number the Trainer reads off the game; nothing offers it as a choice |
| **3.3 differences in a column** | **gap → landed** | new "Where they differ" table: one verdict per property, `same` / `differs by N` / `differs (N values)` |
| **3.3 "Nothing decisive" as a permitted verdict** | **gap → landed** | the all-identical line now reads "Nothing decisive — …" |
| 3.3 matching rows still print | already satisfied | the detail table is unchanged and prints every property |
| bonus: absence reasons were `title`-only (rule 1) | **gap → fixed** | the two rank rows, the Spark-count split and the Spark chance row now print their reasons |

## Phase 4 — Slice 8: Library (2026-10-27)

Premise: `fac82088` (*feat(library): HFW catalog, support-card and skill screens (S8)*) landed the whole
slice. Measured bullet by bullet:

- **Catalog index/detail** — cards rather than tables, three aptitude letters per card, debut form and
  rarity on the card, growth row with `+0%` for real zeros, release status as a word pill, and the
  nine-letter aptitude table on the detail. Present in `Catalog/{Index,Show}.vue`; `catalog.spec.ts`
  covers them.
- **Support cards** — effect chips as words, Pal "None", type counts, the zero-coverage family as a line,
  one row per card, stage table with absent levels never printed as `0`, four-diamond break state,
  duplicate rule, provenance row with no file paths. Present; `support-cards.spec.ts` covers them.
- **Skills** — engine condition in mono beside the readable name, base SP cost only (the ladder is the
  planner's, `4b80f533`), search over name and effect, type as a filter; detail prints engine tokens
  first and the "No client string." empty state, with `sourceText` never rendered as copy. Present;
  `skills.spec.ts` covers them.
- `php artisan test --filter="Catalog|SupportCard|Skill"` was **not** re-run as a gate in this session;
  the class was green at the last recorded run and nothing in this session touched those files. Recorded
  as "not run", which is a legitimate line in a report and not a claim of green.

## Phase 5 — Slice 9: Database hub (2026-10-27)

Premise: `42ac8b74` landed the hub and the area subnav.

| Bullet | State | Evidence |
|---|---|---|
| 5.1 `areas[]` carries `count: int` | already satisfied, with named absences | four areas carry a live count, `Scenarios` carries the config count, and Events / Shop Items / Sparks / Data sources carry `null` plus a documented reason (no table holds them) |
| 5.1 one grouped query for the counts, no N+1 | **partly satisfied, filed** | the hub issues four `count()` statements (Umamusume, SupportCard, Skill, RaceCatalogSlot) plus a config read. That is four round trips, not nine, and not the single `UNION ALL` the instruction sketches. A `UNION ALL` across four unrelated tables returns one row per table only with a synthetic key, which the payload then has to re-split; the four-call shape is what the controller's docblock already commits to. Recorded rather than re-shaped in a release commit |
| 5.2 nine tiles, live counts, sub-nav strip, verification date | already satisfied | `Database/Index.vue`, `components/database/DatabaseNav.vue` |
| **5.2 Sources tile carries the pick border** | **gap → landed** | `isLedger()` adds `border-brand-chrome` to that one tile |
| 5.3 six sub-pages: sub-nav, honesty column, scoping, retry rules beside events, caveats on the row, breadcrumb, `N/A` with a reason | already satisfied | `database.spec.ts` covers the hub, the areas, the caveats and the breadcrumbs; `380bf50a` fixed the N/A notes |

## Phase 6 — Slice 11 remainder: Preferences + Errors (2026-10-27)

- **6.1 Preferences** — already satisfied by `985a44cb`, `8c182d3f` and `f3d3936a`: one `.savebar`, one
  verb ("Save changes"), disabled while clean, three stated widths with their numbers, theme as a choice
  of options with the "Follow the system stores nothing" note, language separated from game vocabulary.
  The HFW's moved-up save action is the one deviation, and `8c182d3f` recorded it as KI-134 rather than
  pretending the page matches. `preferences.spec.ts` covers all five acceptance lines.
- **6.2 Error pages** — already satisfied by `fe2fa850`: `resources/views/errors/{404,419,500}.blade.php`
  answer the data-loss question first, offer three exits with one filled action, and carry no
  `http-equiv="refresh"`. They are Blade documents, not Inertia pages: the app has no `resolveErrorPage`
  and no `resources/js/pages/Errors/`, so the instruction's "whichever component the app uses" resolves
  to these three views.

## Phase 7 — sweep, verification, release (2026-10-27)

- **7.3 framework audit** — already non-vacuous in this repository's port. `tools/ui-framework-audit.mjs`
  exits **2** with `UNVERIFIED` both when Playwright is absent (`:38-45`) and when Chromium cannot launch
  (`:65-72`); the upstream `framework-audit.mjs` the instruction names is the HFW's reference copy under
  `docs/design-research/`, not a shipped tool. A fixture test now pins the behaviour
  (`tests/Feature/FrameworkAuditExitTest.php`), asserting a non-zero exit, `UNVERIFIED` and the printed
  reason — the rule the instruction carries is "CI cannot pass when the driver is missing", and the exit
  code it suggests (1) is a number, not the rule.
- **7.1/7.2 full sweep and DM verification** — **not run as a repo-wide sweep in this session.** The two
  rule classes it checks were touched where this session edited: the `title`-only reasons found in
  `Legacy/Compare.vue` were fixed, and the DM-3 override is already present and now matches the amended
  row. A repo-wide table with a row per rule was not produced, so no claim of a clean sweep is made.
- **7.4 full gates** — **partial**. Run in this session: `npm run typecheck` (exit 0),
  `npm run build` (exit 0, base and slice worktrees), the targeted Pest classes named per phase, and the
  targeted browser specs listed below. **Not run:** the full `npm run test:browser` suite,
  `pint --test`, `phpstan analyse`, `php tools/lore.php`, `node tools/ui-framework-audit.mjs` against a
  live server. The 46-baseline comparison is therefore **not** produced, and the release is labelled
  accordingly rather than claiming a clean full-suite run.

## Gate log — 2026-10-27

| Command | Where | Result |
|---|---|---|
| `npm run build` | `D:\Projects\td-base` (`86127192`) | exit 0, `✓ built in 26.67s` |
| `npm run build` | `D:\Projects\td-slice1` (`86127192` + this session's diff) | exit 0, `✓ built in 23.49s` / `24.96s` |
| `npm run typecheck` | working tree | exit 0 (`tsc --noEmit`) |
| `php artisan test --filter="RunsIndex"` | working tree | **14 passed (226 assertions)** |
| `php artisan test --filter="Cockpit"` | working tree | **128 passed (1728 assertions)** |
| `php artisan test --filter="hands over both turn readings"` | working tree | **2 passed (24 assertions)** — cockpit + dashboard |
| `php artisan test --filter="FrameworkAuditExit"` | working tree | **1 passed** (non-zero exit, `UNVERIFIED`, reason printed) |
| `npx playwright test career-training-detail` | `td-slice1` | **21 passed** after the copy pins were corrected |
| `npx playwright test career-deck-select` | `td-base` | **14 passed** |
| `npx playwright test runs-create-forms:48` | `td-base` and working tree | **1 passed** each |
| `npx playwright test runs-index.spec.ts` | `td-slice1` | **2 passed** (44.3s) |
| `npx playwright test runs-create.spec.ts` | `td-slice1` | **1 passed** |
| `npx playwright test run-import.spec.ts` | `td-slice1` | **8 passed** |
| `npx playwright test legacy.spec.ts database.spec.ts:30 ura-panel unity-cup-panel trackblazer-panel grand-concert-panel` | `td-slice1` | **58 tests: 56 passed, 2 failed** — both were this session's own new assertions (the hub's class-token read matched `hover:border-brand-chrome`, and two `legacy.spec.ts` table locators went ambiguous once the difference table existed). Corrected and re-run green: hub **1 passed**, builder **1 passed**, compare alignment **1 passed**, difference column **3 passed together** (`-g "aligns up to four columns|prints the difference in its own column|confirms a selection"`, 42.0s). Two further pre-existing pins in `legacy.spec.ts` (`Blue Sparks` cell, `Spark chance` cell) needed rewriting because those cells now print their reasons as visible text; both re-run green |
| `npm run lint:ui` | working tree | **red by peer**: `count 220 exceeds baseline 219`, `Review/Index.vue:300`. Base worktree is green, so the finding is foreign WIP |
| `npm run lint:ui` | `td-base` | green (no `[debt-increase]` line) |

**Not run, and named rather than implied:** full `npm run test:browser`, `vendor/bin/pint --test`,
`vendor/bin/phpstan analyse`, `php tools/lore.php`, `node tools/ui-framework-audit.mjs` against a live
server, the §7.1 rule sweep, the §7.2 DM table, and the 46-baseline comparison.

## Session report — 2026-10-27

**Status.** Phase 0: 0.1, 0.2, 0.3, 0.7, 0.8 landed; 0.4 Path B, 0.5 and 0.6 blocked by foreign
uncommitted files, each filed with its path and mtime. Phase 1: landed (`30e9bcc7`). Phase 2: 2.1-2.5
measured as already satisfied and confirmed by their specs; 2.6 landed. Phase 3: 3.1 and 3.3 landed
(3.3's two gaps), 3.2 filed as a payload-shape change plus two non-existent HFW phrases. Phase 4:
measured, already satisfied, not re-gated. Phase 5: 5.2 landed, 5.1's count prop already satisfied, the
single-query shape filed. Phase 6: both halves already satisfied by earlier commits. Phase 7: 7.3
verified and pinned, 7.5 partly done, 7.6 the release commit, 7.4 partial and stated as such.

**`DOMAIN OVERRIDE` in the tree (this session's own).** None added. The one this session relied on is
`Legacy/Index.vue`'s `DOMAIN OVERRIDE (DM-3)` on the Green/White row, which now matches the amended
DM-3 text.

**Tests written.** `tests/browser/runs-index.spec.ts` (new, 2 cases), `tests/browser/runs-create.spec.ts`
(new, 1 case), `tests/Feature/FrameworkAuditExitTest.php` (new, 1 case), and one new case each in
`tests/Feature/RunsIndexTest.php`, `tests/Feature/CareerCockpitTest.php`, `tests/browser/run-import.spec.ts`,
`tests/browser/legacy.spec.ts`, `tests/browser/database.spec.ts`.

**Tests rewritten.** `tests/browser/career-training-detail.spec.ts` (three copy pins, same intent, no
assertion removed), `tests/browser/legacy.spec.ts` (two table locators scoped to the side-by-side
region, and the Spark-count cell's pin now reads the count plus the split).

**Changed files.** Shell/page: `Runs/{Index,Create}.vue`, `Legacy/Compare.vue`, `Database/Index.vue`,
`Career/Cockpit.vue`. Controller/request: `TrainingRunController.php`, `RunSearchRequest.php`,
`Career/CockpitController.php`. Tests: the seven files above. Docs: `docs/proposals/TASKS.md`,
`docs/proposals/frontend-development-update-plan.md`, `CHANGELOG.md`, `VERSION.md`, `AGENTS.md`.

**Commits.** `86127192`, `ae4b6230`, `70631a60`, `30e9bcc7`, plus the phase 2/3/5 commit and the release
commit (hashes in `git log`).

**Prior-session claims re-verified.** Round 1's Deck claim (14/14 at base) and Training claim (21/21
after the copy pin correction) hold. The `runs-create-forms:48` premise was stale (pass at base and at
HEAD). Round 2's Timeline/Result/SaveVeteran/RaceDecision/SkillsPlanner/Inheritance commits were not
re-run in this session; their suites are in the full browser run that this session did not execute.

**The three foreign files.** `VeteranController.php` (` M`, 01:44) and `Help.vue` (untouched by this
session) — not edited. `Review/Index.vue` (` M`, 02:37:50) — not edited, and it still carries the 220th
`lint:ui` finding, so the baseline stays at 219.

**`85d24959`.** Acknowledged as round-1's home. It was read for blame evidence only (the energy-cost
copy change), never reset, rebased, split or amended.

**Worktrees.** `../td-base` and `../td-slice1` are removed at the end of the session; if either survives,
the reason will be appended here.

---

# Round 4 — post-handoff session (2026-10-27 10:05 +08)

Session: `hf-gates-and-slices-2026-10-27`. Round 3's report above stands as the baseline; nothing in it is
re-verified as a matter of course, only where §1.4 of the continuation brief asks.

## Tag resolution (2026-10-27 10:05)

Resolution **A — delete and re-cut**. The tag was cut on a commit whose release gates had not run, which
is the situation the plan's Phase 6 sweep exists to prevent; it was local, so deleting it costs nothing
and re-cutting it after §7.4 is the honest order.

The four commands and their output, run before deleting:

```text
$ git tag -l '3.0.*'
3.0.0
$ git log -1 --oneline 3.0.0
c2c72c13 docs: release 3.0.0 — changelog, version, agents snapshot corrections, the evidence ledger
$ git ls-remote --tags origin '3.0.*'
(no output — no 3.0.* tag exists on the remote)
$ git rev-parse --short HEAD
c2c72c13
$ git tag -d 3.0.0
Deleted tag '3.0.0' (was 7d7f783b)
$ git tag -l '3.0.*'
(no output)
```

`7d7f783b` is the annotated tag object, whose target was `c2c72c13`. The tag will be re-cut in §7.6 on the
commit that passes the §7.4 gates. `CHANGELOG.md` and `VERSION.md` keep their 3.0.0 entries: they are the
release notes, and only the tag moves.

## Tree state and peer surfaces (2026-10-27 10:05)

- `git rev-parse --short HEAD` → `c2c72c13`; branch `master`.
- `85d24959` still resolves (`WIP: prior slice state before ST-4`) and was not touched.
- `git status --short`: 22 modified, 3 untracked — all peer work, none of it this session's. New since
  round 3's snapshot: `resources/css/app.css`, `tests/utils/start-career.ts` (03:50:22) and the untracked
  `tests/browser/setup-layout.spec.ts`.
- The four named peer surfaces are **still dirty with round 3's exact mtimes**, so all four slices stay
  blocked and none of the files was opened for edit:

```text
2026-10-27 01:44:01  resources/js/pages/Career/TraineeProfile.vue
2026-10-27 01:57:52  resources/js/pages/Veterans/Index.vue
2026-10-27 02:01:16  resources/js/pages/Veterans/Show.vue
2026-10-27 02:37:50  resources/js/pages/Review/Index.vue
```

`blocked — TraineeProfile.vue uncommitted, mtime 2026-10-27 01:44:01`
`blocked — Veterans/Index.vue uncommitted, mtime 2026-10-27 01:57:52`
`blocked — Veterans/Show.vue uncommitted, mtime 2026-10-27 02:01:16`
`blocked — Review/Index.vue uncommitted, mtime 2026-10-27 02:37:50`

Peers stopped writing six hours before this snapshot (latest write 03:50) and did not commit, so the
situation is "quiet but uncommitted": the files are not in flux, but they are not this session's to edit
and the KI-70 rule is about ownership, not about recent activity.

## §1.4 corrections re-run (2026-10-27)

Run in `../td-rel4`, a clean worktree at `1ca9caa3` (`HEAD` at the time) with `node_modules`/`vendor`
junctions and a fresh `npm run build` (`✓ built in 33.93s`), because the working tree's wizard files are
peer-dirty and would measure the peer's WIP rather than the committed tree:

| Command | Result |
|---|---|
| `npx playwright test career-training-detail.spec.ts career-deck-select.spec.ts` | **35 passed (5.1m)** — 21 training-detail + 14 deck-select, matching round 3's claim |
| `php artisan test --filter="FrameworkAuditExit"` | **1 passed (3 assertions)** |

Nothing regressed, so no attribution work was needed.

## §1.5 — the five deferred items (2026-10-27)

**A — Legacy builder one-spark-per-parent radios: BLOCKED, with the shape named.**
`training_runs.legacy_selection` is **one column holding one selection** (`ADR-0010`), read through
`TrainingRun::legacySelection() → LegacySelectionPayload::fromArray()`, whose shape is
`{legacies: list<{rank, rank_letter?, is_guest, ancestors: list<string|null>, ancestors_sparks?, sparks:
list<{kind, target, stars}>, …}>, affinity: string|null}`. There is no per-run catalogue of candidate
sparks to choose from, and no table of saved pairs: the builder writes the sparks the Trainer types. A
radio that picks *one* spark per parent therefore is not a UI slice — it changes what the column stores
and what the update request accepts (`StoreLegacySelectionRequest`), and it would delete the ancestor
spark lists the Inheritance screen already reads (`InheritanceEventController:121-136`).
`blocked — payload shape: one selection per run; radios need a behaviour change, ADR-0010/ADR-0015 territory`.

**B — "Skip this moment" / "slot open": the phrases DO exist, on a page round 3 did not grep.
CLOSED by landing them.**
`grep -i "skip this|slot open" docs/design-research/frontend-v2-wireframes/*.html` → **7 hits, all in
`inheritance.html`** (`:87`, `:88`, `:99`, `:100` slot-open nodes; `:189` the Skip action; `:200`/`:202`
the keybox rules). Round 3grepped only `legacy*.html`/`career-legacy.html`, so its "no such phrase" note
was scoped too narrowly — the correction is filed here rather than re-editing the plan. The slice landed
on `Career/InheritanceEvent.vue`: a `data-slot-state` node list where an empty grandparent slot is drawn
with a dashed border and reads **slot open**, the optionality stated in words, and a **Skip this moment**
outcome link back to the Cockpit.

**C — the "illustrative" note: CLOSED, and the date IS sourceable.**
`config/scenarios.php:41` carries `'verified_at' => '2026-09-27'` — the scenario matrix's own verification
stamp. The note therefore ships with the date **and its source**: "the scenario matrix is verified
2026-09-27 in `config/scenarios.php`", on the Cockpit's scenario card for `trackblazer` and (its own
sentence) `grand_concert`. It does not claim the *support data* was verified on that date, only that the
matrix behind it was.

**D — Veterans remainder + `TraineeProfile` growth row: still BLOCKED.**
Both `Veterans/*.vue` files and `TraineeProfile.vue` carry round 3's exact mtimes (`01:57:52`, `02:01:16`,
`01:44:01`) and are still ` M`. Not opened for edit.
`blocked — Veterans/Index.vue uncommitted, mtime 2026-10-27 01:57:52`
`blocked — Veterans/Show.vue uncommitted, mtime 2026-10-27 02:01:16`
`blocked — Career/TraineeProfile.vue uncommitted, mtime 2026-10-27 01:44:01`

**E — lint baseline: still BLOCKED, and the gate does not fail.**
`npm run lint:ui` on the working tree: `resources/js:0 [debt-increase] arbitrary-value count 220 exceeds
baseline 219`, the 220th hit `resources/js/pages/Review/Index.vue:300`. That file is still peer-dirty
(mtime 02:37:50), so the baseline stays at 219. Observation worth filing rather than fixing under 7.3:
the gate printed a `debt-increase` finding and still exited **0**, so CI (if it existed) could not see it.
`blocked — Review/Index.vue:300, peer uncommitted, mtime 2026-10-27 02:37:50`.

## Phase A — the cockpit shell and its four variants (2026-10-27)

Premise, measured rather than assumed: S5.1–S5.5 had already landed with `2ba54a61`, and round 3 added
S5.6. What was missing was **the shell's own spec** and one HFW copy note; the four variants were already
green. So Phase A landed as verification plus two additions, not a rewrite.

| Bullet | State | Evidence |
|---|---|---|
| S5.1 identity card first, one primary, countdown in the scenario card, rail gated on `isActive` | already satisfied | `Cockpit.vue`: `CockpitIdentityCard` at `:865`, head action at `:820`, `[data-region="scenario-finale"]` inside the Scenario section at `:1011`, `AdvisorRail v-if="isActive"` at `:1035` |
| S5.1 the shell spec the brief asks for | **gap → landed** | new `tests/browser/cockpit-shell.spec.ts`: identity above both body regions, one filled action, the 390px reflow, the countdown inside the scenario card and **not** in the advisor, and a Completed run with no advisor rail and its outcome printed first |
| S5.2 URA finale: outcome first, entered caps, no rail | already satisfied | `ura-panel.spec.ts` green in the batch below |
| S5.3 Unity Cup: five bars, pill-caution, "Where to go" | already satisfied | `unity-cup-panel.spec.ts` green below |
| S5.4 Trackblazer: shortfall lead figure, currencies in one sentence, no `.mockbar` | already satisfied | `TrackblazerPanel.vue:610-617`; `grep mockbar resources/js` → 0 hits |
| S5.4/S5.5 the `.mockbar` warning as a `.note` | **gap → landed** | two notes on the Cockpit's scenario card (`data-field="support-data-note"`), one for `trackblazer`, one for `grand_concert`, each naming the matrix's `verified_at` as the source of the date (see §1.5 C) |
| S5.5 Grand Concert: loop explained, five tiles with absences, payouts | already satisfied | drawn inline at `Cockpit.vue:961` and explained at `:441-450`; `grand-concert-panel.spec.ts` green below |

**Two of this session's own assertions were wrong on first run and were corrected, not weakened:** the
two-column "shared top edge" check (the scenario card sits *below* the body grid, so the pair being
compared was never in one row — the assertion now pins the order the shell actually documents), and the
320px reflow, which the new note broke because `config/scenarios.php` in a `<code>` does not wrap
(`break-words` added — a real regression this session introduced and fixed).

## Phase B — S7.1 Legacy Index (2026-10-27)

| Bullet | State | Evidence |
|---|---|---|
| saved pairs are the unit | **gap → landed** | `LegacyController@index` gains an additive, read-only `pairs` projection (`savedPairs()`): runs carrying a `legacy_selection`, each projected as one pair with the two parent names read off `inheritance_parent_a_id`/`_b`. No table, no write, no score |
| three-row value table | already satisfied | the reference aside already printed blue grades, pink range and the Green/White hint row (`Legacy/Index.vue:334-354`, with its two `DOMAIN OVERRIDE` comments); each pair row now also carries the three values **per pair**, which is the brief's "values from the payload" |
| Compare is the only filled action | already satisfied | `LinkButton variant="primary"` in the page head |
| the HFW's `<h2>` headers | **gap → landed** | "Saved legacies" is new and first; the library list keeps its own heading, and the aside already carried "What a legacy is worth" and "Parents available". A first attempt added a *duplicate* worth table above the grid — caught by the new spec's strict-mode failure and removed rather than left as a second copy |
| S7.2 radios | blocked, see §1.5 A | payload shape |
| tests | **landed** | new `tests/browser/legacy-index.spec.ts`: saved pair as a row keyed by its run, the three values, the three regions and their order, one primary |

## Phase C — S8 Library (2026-10-27)

Already satisfied end to end by `fac82088`; measured bullet by bullet and **not** re-written:

- Catalog: cards, three aptitude letters per card (`catalog.spec.ts:96-102`), debut form and rarity on the
  card, a growth row that states the catalogue holds no growth figure, release status as a word pill;
  detail carries the full nine letters under their own headings and the `FormTabs` link strip with
  `aria-current="page"` (`catalog-detail.spec.ts:173-191`).
- Support cards: effect chips as words, Pal `None` with its reason, type counts, one row per card; the
  detail's stage table states that a level between anchors is an absence and not a zero, the break widget
  is four diamonds with a level readout, and provenance is a row plus a link to the ledger with no file
  path.
- Skills: engine condition beside the name, base SP cost only, search over name and effect text, type as
  a filter; detail prints engine tokens first and the "No client string." empty state, with `sourceText`
  never rendered as copy.
- No test was added: the brief's four assertions already exist in `catalog.spec.ts` and
  `catalog-detail.spec.ts`, and re-writing them would be churn. `php artisan test --filter="Catalog|SupportCard|Skill"`
  was not re-run this session; `Legacy|Database` (119 passed) and the new Feature classes were.

## Phase D — S9 Database (2026-10-27)

| Bullet | State | Evidence |
|---|---|---|
| S9.1 `areas[].count` | already satisfied (round 3 measured it) | four live counts, one config count, four documented `null`s |
| S9.1 **one grouped query** | **gap → landed** | the four live counts now come from one `UNION ALL` (`DatabaseController@index`), and `tests/Feature/DatabaseIndexCountsTest.php` proves both the numbers and that exactly **one** statement mentions the four tables (`toHaveCount(1)` + `toContain('union all')`) |
| S9.2 nine tiles, strip, verification date, Sources pick border | already satisfied (`496d02c6` + `42ac8b74`) | `database.spec.ts` |
| S9.3 sub-nav strip on **every** area page | **gap → landed** | `Skills.vue`, `Supports.vue` and `Trainees.vue` were the three without `DatabaseNav`; all nine now carry it |
| S9.3 breadcrumb `Database / <Area>` | **gap → landed** | all nine sub-pages now pass `:breadcrumb` to `AppLayout`; only the hub did before |
| S9.3 "Effects documented?" honesty column | **filed, not built** | the Races/Events/ShopItems payloads carry no per-row "effects documented" flag, and a column printing the same word on every row would be a claim the data cannot corroborate. Each row already states its own absence as `N/A` with a visible reason (`380bf50a`), the same honesty at the grain the data has |
| tests | **landed** | new `tests/browser/database-subpages.spec.ts`: one case per area (one `h1`, the strip, the `Database` crumb link and the area's own crumb text) plus the hub's nine tiles, strip and verification date |

## Phase E — S6 Preferences + error pages (2026-10-27)

- **Preferences**: already satisfied, and closer to the HFW than round 3 recorded. The save action is in
  the **page head's action row** (`PageHeader #actions`), not a bottom bar, so the brief's option (a) is
  the shipped shape; the state line reads `All changes saved` / `Unsaved changes`, the control is
  disabled while clean (KI-134 resolved), the three widths state their numbers, and the theme card states
  that "Follow the system" stores nothing so the OS decides. `preferences.spec.ts` covers all five lines.
  No change made.
- **Error pages**: already satisfied in shape (Blade documents with one filled action, plain-language
  headlines, no meta refresh), with two gaps closed here: the **419 had two exits and now has three**
  (Careers added beside Dashboard) and the **500 had two and now has three** (Database added). New
  `tests/Feature/ErrorPageCopyTest.php` pins all three documents (data-loss answer, exactly one
  `bg-primary-action`, three exits, no `http-equiv`), and new `tests/browser/errors.spec.ts` pins the one
  error a visitor actually reaches: a 404 with its data-loss line, one filled action, three exits and no
  auto-redirect.

## 12.1 sweep — 2026-10-27

Greps over `resources/js/**` (`.vue`, `.ts`). "Hits" is the raw count; the note says whether a hit is a
finding. Nothing was fixed in this sweep because nothing was found: the one rule class that had findings
this release (title-only reasons) was closed by `496d02c6`, and the UI hygiene guard still enforces it.

| # | Rule | Grep | Result | Files touched |
|---|---|---|---|---|
| 1 | `title=`-only reasons | `title=` | 263 hits; **0 findings**. The attribute is used for labels, icon hints and system-name captions, and the absence-reason shape is enforced by `tests/Feature/UiHygieneTest.php` (it reads the 25 lines after each `N/A` for a `_title`/`_reason`/`absence` twin) | — |
| 2 | `animate-pulse` / `.skel` | `animate-pulse\|\.skel` | **0 hits** | — |
| 3 | growth zeros `+0%` | `Not recorded` | 109 hits — the honest-absence wording is the shipped default | — |
| 4 | confirmed → `null` | `confirmed.*null` | 7 hits, all payload/badge conventions | — |
| 5 | `ArtworkSlot` missing → nothing | `ArtworkSlot` | 28 hits, all the component and its consumers | — |
| 6 | `HFW OVERRIDE` for visuals | `HFW OVERRIDE` | 1 hit — the new Cockpit support-data note, which is a **copy** override and says so in its own comment | `Cockpit.vue:1033` |
| 7 | `dark:` | `dark:` | 2 hits, **both inside comments that prohibit it** (`SparkChip.vue:18`, `AptitudeBadge.vue:46`) → **0 utilities** | — |
| 8 | one primary per page | `.bg-primary-action` count | Asserted per page by this session's three new specs (cockpit shell, legacy index, error pages); no repo-wide count was taken | — |
| 9 | `Form/buttonStyles.ts` imports | `buttonStyles` | 4 hits, the shared module and its importers | — |
| 10 | `.savebar` disabled-when-clean | `savebar` | 1 hit (`Preferences/Edit.vue`), and `preferences.spec.ts` pins disabled-while-clean | — |
| 11 | provenance row + link, no paths | `database.sources\|database/sources` | 11 hits, all links to the ledger | — |
| 12 | client vocabulary verbatim | spot-check | `Released (Global)`, `None`, `Not recorded`, `No client string.` read as the client's own words | — |
| 13 | lore gate | `php tools/lore.php` | **11 hits / 7 files**: all code identifiers, the gate's own patterns, and two comments (`AncestryNode.vue:41` "pairing", `UnityCupPanel.vue:194` "pairing"); **none is displayed vocabulary**. Delta from the last recorded figure: **unknown** — no baseline figure was in this session's context, so none is claimed | — |
| 14 | one `h1` per page | `<h1` | 2 hits, both shared chrome (`ui/PageHeader.vue:42` and `AppLayout.vue:484`); a page renders one or the other, never both | — |
| 15 | blank is not zero | `N/A` + reason tests | Enforced by `UiHygieneTest` and by each panel spec's absence cases | — |
| 16 | state never on colour alone | status pills | Every status carries a word (`StatusPill`, `.pill-caution`), and the Careers row prints position beside status | — |
| 17 | `.tag.auto` / `.tag.you` | `tag auto\|tag.auto` | 3 hits, the provenance tag family | — |

## 12.2 DM sweep — 2026-10-27

`DOMAIN OVERRIDE` appears **10 times**, numbered where the wireframe conflicts with the domain:
DM-1 ×2, DM-3 ×3, DM-8 ×2, plus three copy-only overrides that name no DM (Legacy's worth table and
Import's two cell/edit overrides).

| DM | Acceptance | Files expected | Present | Line |
|---|---|---|---|---|
| DM-1 | 72 turns + finale outside | cockpit, timeline, result, dashboard | yes, 2 overrides | `CareerTimeline.vue:9`, `Career/Timeline.vue:184` |
| DM-2 | five stats distinct | cockpit stat summary | implemented, no override needed | `components/career/StatSummary.vue` (five distinct rows) |
| DM-3 | four/five spark families | inheritance, legacy, veterans | yes, 3 overrides | `Career/InheritanceEvent.vue:318` (five families), `Career/LegacySelect.vue:611`, `Legacy/Index.vue:354` |
| DM-4 | SP ladder, base cost + note | skills planner | implemented, ladder renders below base prices | `Career/SkillsPlanner.vue:13-15`, `:301-303` |
| DM-5 | mood percentages + non-monotone ramp | mood pill, cockpit | implemented (Mood Effect headline) | `Career/Cockpit.vue` (`Mood Effect` block) |
| DM-6 | four running strategies | trainee/aptitude surfaces | **not found in the surfaces the row names** — no `Pace Chaser`/`Front Runner`/`End Closer`/`Late Surger` string in `Career/*.vue`. Filed rather than invented: the strategy vocabulary lives in the wizard's build target (`distance`/`surface`/`style` selects), which is `Career/BuildTarget.vue` — a peer-dirty file this session could not open | — |
| DM-7 | energy bend, rest distribution | training decision | implemented ("Rest is rolled… bends around 50") | `Career/TrainingDetail.vue:?` rest paragraph |
| DM-8 | six-slot deck, guest, Pal "None" | deck select, support deck | yes, 2 overrides | `Career/DeckSelect.vue:370`, `components/support/SupportSlot.vue:171` |

DM-6's row is the one gap in this table, and it is a *measurement* gap rather than a missing mechanic:
the strategy fields are the wizard's (`select[name="style"]`), which `career-build-target.spec.ts` covers,
but that file is peer-dirty so this session did not read or run it.



## ST-4 close-out — deviations filed 2026-10-27

Two HFW-vs-app mismatches of one class: an HFW label promises a navigation the controller does not
perform. Both are behaviour-change questions, not UI calls, so they are **filed, not resolved**. Neither
redirect was changed in the close-out commit.

### Deviation 1 — BuildTarget "Continue to legacy" saves-and-stays

- **Where:** `resources/js/pages/Career/BuildTarget.vue` primary button;
  `app/Http/Controllers/Career/BuildTargetController.php` redirect target.
- **HFW:** `career-target.html` — `<a class="btn btn-primary" href="career-legacy.html">Continue to legacy</a>`.
  A link that navigates.
- **App:** the button POSTs; the controller saves and redirects to `career.target` (the same route).
  `CareerBuildTargetTest` pins `assertRedirect(route('career.target'))`. The label says "Continue to
  legacy"; the redirect says "stay".
- **Two resolutions, both valid, one needs an Architect:**
  - **(a) Label honesty.** Revert the label to "Save target" — honest, matches the pinned redirect,
    loses the HFW's language. No test change.
  - **(b) Behaviour parity.** Change the redirect to `route('career.legacy')` — matches the HFW, advances
    the wizard. Rewrites one assertion in `CareerBuildTargetTest`; a behaviour change under the plan's
    §11 test contract.
- **Chosen: (a).** The label is reverted to "Save target" in this commit; this entry records that the
  HFW's "Continue to legacy" language was not adopted because the controller's redirect contract does
  not support it. The main session may reverse (a) → (b) as its own decision.

### Deviation 2 — Legacy primary is "Save Legacy", not "Continue to deck"

- **Where:** `resources/js/pages/Career/LegacySelect.vue` primary button.
- **HFW:** `career-legacy.html` — primary is "Continue to deck" (a link to `career-deck.html`).
- **App:** primary is "Save Legacy"; "Choose a veteran" is a select, not a filled button. This was the
  prior ST-4 session's documented deviation, kept.
- **Two resolutions, both valid:**
  - **(a) Keep "Save Legacy."** The label honestly reflects what happens (POST and stay). The HFW's
    "Continue to deck" would require the same redirect-parity work as Deviation 1.
  - **(b) Match the HFW.** Change the redirect to `route('career.deck')` and relabel — same
    behaviour-change class as Deviation 1.
- **Chosen: (a).** "Save Legacy" is kept, consistent with Deviation 1's resolution. Filed here so a
  future pass can pair the two.

### ∑ glyph sweep — resolved, not filed

`tests/Feature/CareerBuildTargetTest.php`'s "keeps the three trust glyphs in ProvenanceMark alone"
greps `resources/js/**` for `∑` and found `resources/js/pages/Help.vue:21`, where the character is the
**subject** of the marks glossary rather than an accidental duplicate. The sweep now carries a
file-scoped exclusion (`$helpGlossary`) with an in-place rationale comment. The exclusion does not
weaken the sweep for any other file. `Help.vue` was not edited (foreign, Slice 11.2).

## ST-4 close-out — hand-back 2026-10-27

| Field | Value |
|---|---|
| Commit | `0201d8e7` — "ST-4: career setup wizard HFW parity (close-out)" |
| Branch / base | `master`; parent `1ca9caa3`, which sits on `c2c72c13` (both still ancestors) |
| Files staged (16) | `resources/js/layouts/SetupLayout.vue`, `resources/js/pages/Career/{ScenarioSelect,TraineeSelect,BuildTarget,LegacySelect,DeckSelect,Preflight}.vue`, `resources/css/app.css`, `tests/browser/career-{build-target,trainee-select,preflight,legacy-deck-steps}.spec.ts`, `tests/browser/setup-layout.spec.ts` (new), `tests/utils/start-career.ts`, `tests/Feature/CareerBuildTargetTest.php`, `app/Http/Controllers/Career/BuildTargetController.php`, `docs/proposals/TASKS.md` |
| Files NOT staged | Peer work, all left as found: `app/Http/Controllers/ReviewController.php`, `resources/js/pages/Review/{Index,Detail}.vue`, `resources/js/pages/Veterans/{Index,Show}.vue`, `resources/js/pages/Career/TraineeProfile.vue`, `tests/browser/dashboard.spec.ts`, `tests/browser/career-scenario-select.spec.ts` (re-dirtied by a peer after this session), `tests/Feature/ReviewDetailPayloadTest.php` (untracked). `resources/js/pages/Help.vue` was never opened. Untracked `tests/browser/annotation-sweep.spec.ts` is ST-4 work but was not in the close-out staging list, so it is left for a follow-up. |
| Playwright wizard suite | **82 passed / 0 failed** (7 career specs + `setup-layout.spec.ts` + `annotation-sweep.spec.ts`) |
| Re-run after the label revert | `career-build-target` + `career-preflight` → **12 passed / 0 failed** |
| Pest `CareerBuildTarget` | **13 passed / 0 failed** (the sweep test is green) |
| Pest wizard remainder | **71 passed / 0 failed** (`CareerLegacyDeckSteps`, `CareerScenarioSelect`, `CareerTraineeSelect`, `CareerPreflight`) |
| ∑ glyph sweep | Resolved via a file-scoped allowlist with in-place rationale; green. `Help.vue` untouched. |
| Deviation 1 (BuildTarget) | **(a) chosen and applied** — label reverted to "Save target" in this commit; `CareerBuildTargetTest`'s pinned `assertRedirect(route('career.target'))` untouched |
| Deviation 2 (Legacy) | **(a) chosen and kept** — "Save Legacy" stays the primary |
| Dark theme | Not explicitly measured. Zero `dark:` utilities confirmed by grep (the repo-wide table above records 2 hits, both inside comments that prohibit the utility); the flip rides `html[data-theme='dark']`. |
| Screenshot blocker | `mcode-tools` connector fails `UND_ERR_HEADERS_TIMEOUT`; mitigated by DOM measurement (`scrollWidth`, `getBoundingClientRect`, structure) via Playwright evaluate. |
| Gates | `npm run typecheck` clean; `npm run build` ✓; `pint --test` passed; `phpstan analyse` no errors on touched paths; `lore code` 221 hits (advisory, none added); `lint:ui` adds no arbitrary-value debt (the `220>219` is `Review/Index.vue:300`, peer-owned) |
| Not run | Full `test:browser` suite, full Pest suite, live framework-audit |
| Next | **Main session owns.** The ST-4 agent does not continue into Phase 6.1 or any other slice. |

Two additions beyond the close-out brief's staging list, both deliberate and flagged here:

1. **`app/Http/Controllers/Career/BuildTargetController.php`** — staged because it is the supplier of the
   `finaleTargets` prop that `BuildTarget.vue` declares as required (the ST-4 §4 prop delta, and the
   locus §4 Deviation 1 names). Committing the page without its controller would have left a required prop
   unsupplied. The redirect inside it was **not** changed.
2. **`docs/proposals/TASKS.md`** — the brief makes the deviation entries and this hand-back deliverables.

The commit's subject acquired a UTF-8 BOM from `Out-File -Encoding utf8` on first write; it was amended
once, immediately, with a BOM-free writer to strip it. The amended hash is the one above; the original
`0fa022ef` never left this branch.

### Attribution incident — this hand-back is committed under `ad876208`, not `0201d8e7`

Recorded rather than repaired. While this close-out was running, a peer committed `ad876208`
(`feat(legacy,inheritance,database,errors): … (S7.1, S9.1, S9.3, S11.3)`) onto the same branch. A
subsequent `git commit --amend --no-edit` — intended to fold this hand-back into `0201d8e7` — amended
the peer's HEAD instead, because the peer's commit had landed in the interim. Result: **the hand-back
section you are reading lives in `ad876208`**, whose message is the peer's and says nothing about ST-4.

What is and is not affected:

- `0201d8e7` itself is intact and unmodified: same 16 files, same stat, same subject, and it is still
  the commit that carries every ST-4 code and spec change. Its `--stat` shows no peer file.
- The peer's code changes are untouched — only a docs file was appended to their commit.
- Nothing was reset, rebased, or force-pushed; `85d24959`, `c2c72c13`, `1ca9caa3` all still resolve
  as ancestors.

Not repaired because repairing it means rewriting a commit this sub-task does not own (`git reset` +
recommit of `ad876208`), which the close-out brief forbids. The main session owns whichever of these it
prefers: (a) leave it, accepting that an ST-4 docs section rides a peer commit; or (b) rewrite
`ad876208` to drop the `TASKS.md` hunk and re-land it under an ST-4 subject. Option (a) is what stands.

---

# Round 4 — gates, sweeps and the re-cut (2026-10-27 12:00 +08)

Session: `hf-gates-and-slices-2026-10-27`. The sections above are round 3's; the §1.5/Gate/Report
sections below are this session's, appended rather than interleaved so no peer write is overwritten.

**Attribution incident observed and accepted.** My first slice commit was amended by the ST-4 session
into `ad876208` (their note above says so), so the commit carrying this session's S7.1/S9.1/S9.3/S11.3
work is `ad876208`, not the hash it was created under. Its tree is intact; only a docs file was appended.
Not repaired: rewriting it means rewriting a commit this session does not own.

## Phase 7.4 gates — 2026-10-27

Worktree `D:\Projects\td-release` at **`06642b9d`** (branch `master`), `node_modules`/`vendor` junctions,
`storage`/`.env` copied, built there.

| Gate | Result |
|---|---|
| `npm run typecheck` | **exit 0** |
| `npm run lint:ui` | **exit 0, no `[debt-increase]` line** — the clean tree is inside the 219 baseline; the 220th finding the working tree reports is the peer's `Review/Index.vue:300` |
| `npm run build` | **exit 0** |
| `vendor/bin/pint --test --format agent` (session PHP paths) | **passed**; it fixed `unary_operator_spaces`/`no_unused_imports` in `DatabaseController.php`, and the fixed file is mirrored into the gate tree so the tagged content is the gated content |
| `vendor/bin/phpstan analyse` (session `app` paths) | **No errors**, level 6. Tests are outside phpstan's scope by config (`paths: app`), proven by analysing an accepted control file and getting 61 `method.notFound` errors the gate never asks for |
| `php tools/lore.php` | **11 hits / 7 files**, none in shipped vocabulary; delta unknown (no baseline figure in context) |
| `node tools/ui-framework-audit.mjs` | the `UNVERIFIED` path is pinned by `FrameworkAuditExitTest` (exit 2, reason printed); against a live server it is **not run** |
| `php artisan test` (full, session tree) | **1994 passed, 15 failed, every failure attributed below — 0 this session's** |
| `npx playwright test` (full, gate worktree) | **NOT COMPLETED — blocked.** 153 of 603 tests executed in ~50 minutes (0 green summary lines, 3 distinct failure shapes), then the single worker wedged on `career-scenario-select.spec.ts:297` for 15+ minutes with no progress and the run was stopped. §7.4 therefore does **not** pass, and per §7.6 the `3.0.0` tag is **not** re-cut this session |

**Pest in a worktree is unusable on this host, recorded rather than worked around.** `php artisan test`
inside `../td-release` produced 1589 instant failures with `A facade root has not been set`
(`LessonEvidenceBoundaryTest.php:46`), and the same subset in a second throwaway worktree failed 71
tests with **0 assertions**. The Pest gate therefore ran in the session tree, and the citation census —
pure git — ran in a throwaway worktree for attribution.

**The 15 Pest failures, attributed:**

| Test | Cause | Evidence |
|---|---|---|
| `DocCitationParityTest` (754 dead vs 708 baseline) | **pre-existing** | `python tools/doc_census.py` in a throwaway worktree at `1ca9caa3` (before any of this session's work) also reads **754**. The dead targets are repo-wide rot — `CONSTRAINTS.md` (147), `PLAN.md` (48), `RAW-FINDINGS.md` (25) |
| `DesignTokensTest` ×2 (95 tokens vs 94), `DesignDocEnforcementTest` | peer WIP `resources/css/app.css` (` M`) | the count reads the working-tree stylesheet |
| `KeyboardPathTest` ×2, `RulesetVersionVocabularyTest`, `SupportDeckBuilderTest` | peer commit **`0201d8e7` (ST-4)** | the failures quote `AppLayout.vue` and the career pages' new opening (`const { visiting, visitFailed } = useVisitState();`); those files are committed, so the regression is committed peer work |
| `NoStaleRunRecordLinksTest`, `EmptyStateCommandNamesTest` | peer commit `0201d8e7` | run doors and the README Web-surface table moved with ST-4 |
| `FrontendAuditFixesTest` | peer WIP `SupportCardController`/`SupportCardEffects` | the failure names `SupportCardController.php`, a file this session never opened |
| `RenderedCopyHygieneTest` (em dash) | peer-dirty pages (BuildTarget, DeckSelect, LegacySelect, TraineeProfile, Review/Detail, Review/Index, Veterans/Show) + committed `CareerTimeline`/`SupportSlot` | this session's own dashes are fixed: `Nothing decisive:`, `Trainee A-Z`, `1 to 3 hint levels`, `★1 to ★2`; the earlier run listed `Legacy/Compare.vue`, `Legacy/Index.vue` and `Runs/Index.vue`, and none appears now |

**Two regressions this session introduced and fixed before the tag:** the nine Database pages were
re-encoded as mojibake by a PowerShell string edit (caught by `RenderedCopyHygieneTest`'s
double-encoded-UTF-8 case; repaired by restoring from `ad876208~1`'s parent and re-applying the edits
through Node, which decodes and re-encodes UTF-8), and the new support-data note broke the Trackblazer
320px reflow (`trackblazer-panel.spec.ts:430`), fixed by removing the unbreakable path token from the copy.

## Final report — 2026-10-27

**1. Per-slice status.** S3 (round 3) done; S5.1–S5.6 done (S5.1–S5.5 verified as already satisfied,
S5.6 landed in round 3, the shell spec and the support-data note landed here); S7.1 done; S7.2 **blocked** on
`legacy_selection`'s payload shape; S7.3 done (round 3); S8 verified, no change needed; S9.1/S9.3 done;
S9.2 done (round 3 + the strip/breadcrumb here); S11.1 verified; S11.3 done (three exits each).
Veterans and TraineeProfile remain blocked on peer-dirty files.

**2. `DOMAIN OVERRIDE` inventory.** 10 sites: DM-1 ×2 (`CareerTimeline.vue:9`, `Career/Timeline.vue:184`),
DM-3 ×3 (`Career/InheritanceEvent.vue:318`, `Career/LegacySelect.vue:611`, `Legacy/Index.vue:354`),
DM-8 ×2 (`Career/DeckSelect.vue:370`, `components/support/SupportSlot.vue:171`), plus three copy-only
overrides that name no DM (`Legacy/Index.vue:334`, `Runs/Import.vue:335`, `:519`). This session added one
`HFW OVERRIDE (copy, not visual)` on the Cockpit's scenario card.

**3. Tests written.** `cockpit-shell.spec.ts` (4), `legacy-index.spec.ts` (1), `database-subpages.spec.ts`
(10), `errors.spec.ts` (1), `DatabaseIndexCountsTest.php` (2), `ErrorPageCopyTest.php` (4).
**Rewritten:** `cockpit-shell.spec.ts` twice (the column-geometry assertion and a turn write for the
one-primary check) — both were my own wrong assumptions, corrected rather than weakened.
**Retired:** none.

**4. Changed files.** Shell/pages: `Career/InheritanceEvent.vue`, `Career/Cockpit.vue`, `Legacy/Index.vue`,
`Legacy/Compare.vue`, `Runs/Index.vue`, the nine `Database/*.vue`. Controller: `LegacyController`,
`DatabaseController`. Views: `errors/419`, `errors/500`. Tests: the six above. Docs: `TASKS.md`,
`frontend-development-update-plan.md`, `CHANGELOG.md`.

**5. Commands and results.** Every command and its output is in the gate table above and in the phase
sections; logs were written per run and quoted here rather than attached.

**6. Commits.** `1ca9caa3` (tag resolution + peer remeasure), `ad876208` (slice work, amended by the ST-4
session), `06642b9d` (encoding repair, dash removal, phpstan typing), plus the docs commit at the end.
Tag: `3.0.0` deleted and re-cut per §1.2 — see the tag section above and the release note below.

**7. The 46-baseline re-check.** **Not produced, and the full-suite gate is blocked.** The suite ran 153
of 603 tests before the single worker wedged at `career-scenario-select.spec.ts:297` (a peer-owned ST-4
spec) and was stopped; three failure shapes were seen before the wedge (`toHaveCount` resolved to 1,
`toHaveAttribute` on `<span class="text-xs">N/A</span>`, and an `Event outcome incomplete - choose
manually` pill), none attributable without a completed run. Round 3's batch already showed `ura-panel`,
`unity-cup-panel`, `trackblazer-panel` and `grand-concert-panel` green, which closes 8+3+2 of the 46;
those stay closed. **Consequence: `3.0.0` was deleted and is NOT re-cut — a tag on a tree whose
full-suite gate has not run is the defect this session was created to prevent.** The re-cut is the next
session's job, on a commit whose suite finishes.

**8. Visual-verification blocker.** Screenshots remain unavailable; every acceptance criterion above is a
DOM measurement (`getBoundingClientRect`, `getComputedStyle`, class tokens, text assertions), named per
slice in the phase tables.

**9. Safe defaults chosen.** Resolution A for the tag (delete, re-cut); Path B for the lint baseline
(no bump while the peer file is dirty); "three exits" implemented as one filled + two outline on all
three error documents; the honesty column filed rather than fabricated; DM-6 filed rather than guessed.

**10. Prior-session claims re-verified.** Round 3's `career-training-detail` 21 and
`career-deck-select` 14 both reproduce at `1ca9caa3` (35 passed, 5.1m); `FrameworkAuditExit` 1 passed.
`runs-create-forms:48` stays green per round 3 and was not re-attributed.

**11. Plan amendments.** One block appended to `frontend-development-update-plan.md` recording DM-3's
five keys, S7.2's payload block, the corrected page for "Skip this moment"/"slot open", the framework
audit's exit code needing no erratum, and the two filed deviations.

**12. Tag resolution.** Recorded at the top of this round-4 section with all four command outputs;
`3.0.0` was deleted (`was 7d7f783b`, target `c2c72c13`). **It is not re-cut:** §7.4's full-suite gate did
not complete (blocked, see §7), and §7.6 requires the gates. The repository is therefore left with **no
`3.0.0` tag**, which is the honest state — a tag on unverified content was the defect this session
existed to remove. Re-cutting it is a one-command job on the first commit whose full suite finishes:
`git tag -a 3.0.0 -m "HFW parity, v3.0.0"`.

**13. Deferred items.** A blocked (payload shape), B closed (landed on the Inheritance screen), C closed
(date sourced from `config/scenarios.php`), D blocked (peer-dirty, mtimes recorded), E blocked (peer-dirty;
the clean tree is green, so no bump is owed).

**14. Foreign files at hand-back.** `Career/TraineeProfile.vue` (01:44:01), `Veterans/Index.vue` (01:57:52),
`Veterans/Show.vue` (02:01:16), `Review/Index.vue` (02:37:50) — all still ` M`, plus the peer work that
arrived during the session (`SkillsPlanner*`, `SupportCardEffects`, `config/uma.php`, `docs/adr/0011`,
`docs/adr/0029`, `Review/Detail.vue`, two test files). None was edited by this session.

**15. `85d24959`** remains round 1's home; it was read for blame only and never reset, rebased, split or
amended.

**16. Worktrees.** `../td-release` is removed at the end of the session; `../td-base`, `../td-slice1`,
`../td-rel4`, `../td-census` and `../td-attrib` were removed as they were finished with.

---

# Main agent — round 5, Phases 5–7 (2026-10-27 14:50 +08)

Session: `main-agent-phase5-2026-10-27`. The round-5 brief splits Phases 0–4 (ST-4 agent) from
Phases 5–7 (main agent). **At this session's start no ST-4 follow-up hand-back exists:** this file has
no `## ST-4 follow-up — hand-back` section, and `D:\Projects\td-wedge` is at `d55927cd` with only an
untracked probe file. The ST-4 follow-up is visibly in flight there: `.wedge-plain.txt` (mtime
2026-10-27 13:46:07) records `career-scenario-select.spec.ts:297` and `:57` failing in 2.1m, and
`tests/browser/zz-committed-scenario.spec.ts` (13:57:56) is an untracked probe in the shared tree.
Phases 6.2–6.6 (release docs) and Phase 7 (tag) depend on that hand-back and stay blocked; this
section records what Phase 5 and the independent half of Phase 6.5 could measure.

## Phase 5 — peer-blocked re-measurement (2026-10-27 14:50 +08)

Tree at measurement: HEAD `d55927cd`, branch `master`, `git tag -l '3.0.*'` empty, 41 working-tree
entries.

| Item | `git status --short` line | mtime | Outcome |
|---|---|---|---|
| 5.1 `Career/TraineeProfile.vue` | ` M resources/js/pages/Career/TraineeProfile.vue` | 2026-10-27 01:44:01 | `blocked — resources/js/pages/Career/TraineeProfile.vue uncommitted, mtime 2026-10-27 01:44:01, peer-owned` |
| 5.2 `Veterans/Index.vue` | ` M resources/js/pages/Veterans/Index.vue` | 2026-10-27 01:57:52 | `blocked — resources/js/pages/Veterans/Index.vue uncommitted, mtime 2026-10-27 01:57:52, peer-owned` |
| 5.3 `Veterans/Show.vue` | ` M resources/js/pages/Veterans/Show.vue` | 2026-10-27 02:01:16 | `blocked — resources/js/pages/Veterans/Show.vue uncommitted, mtime 2026-10-27 02:01:16, peer-owned` |
| 5.4 `Review/Index.vue` | ` M resources/js/pages/Review/Index.vue` | 2026-10-27 02:37:50 | `blocked — resources/js/pages/Review/Index.vue:300, peer uncommitted, mtime 2026-10-27 02:37:50` |

The four mtimes are identical to round 4's hand-back: no peer has written these files since. None was
edited. The two surfaces that were peer-dirty in round 4 and are now clean are
`Career/ScenarioSelect.vue` (03:05:37) and `resources/css/app.css` (04:41:58); clearing them is the
ST-4 agent's Phase 0.3/3 work, not this session's.

### 5.4 lint — 221, not 220: a second peer finding arrived

`npm run lint:ui` on the working tree prints
`resources/js:0  [debt-increase] arbitrary-value count 221 exceeds baseline 219` and **exits 0**
(log: `.scratch-uma/main-2026-10-27/lint-ui.log`). The clean worktree at HEAD
(`D:\Projects\td-wedge`, `node_modules` linked) prints **219** and exits 0
(`lint-ui-clean.log`). Diffing the two finding sets leaves exactly two additions, both peer-owned:

| file:line | value | file mtime |
|---|---|---|
| `resources/js/pages/Review/Index.vue:300` | `[minmax(0,1fr)_22rem]` | 2026-10-27 02:37:50 |
| `resources/js/pages/Career/SkillsPlanner.vue:374` | `[1fr_auto]` | 2026-10-27 10:20:10 |

Round 4 recorded 220 and named only the first. `SkillsPlanner.vue:374` is the 221st and arrived after
that measurement, so the rise is a peer write, not a re-count. `tools/ui-lint-baseline.json` stays at
`count: 219`. **This is a filing, not a fix: the gate cannot see the regression**, because the
`[debt-increase]` line prints but the process exits 0. Changing the exit code is outside this phase.

### 5.5 Legacy builder radios — re-confirmed blocked

Re-read at `d55927cd` (HEAD line numbers; the working tree's `TrainingRun.php` is peer-dirty but its
diff does not touch this read path):

- `app/Models/TrainingRun.php:247` — `legacySelection(): ?LegacySelectionPayload` reads the single
  `$this->legacy_selection` column (`:249`) and hydrates one payload (`:253`).
- `app/Models/Legacy/LegacySelectionPayload.php:43` — `KEYS = ['legacies', 'affinity']`: one
  selection per run.
- `app/Http/Controllers/LegacyController.php:125-126` — the saved-pair projection states the same
  fact: a pair "is stored as one `training_runs.legacy_selection` column (`ADR-0010`), so 'a saved
  pair' is a run that carries a selection, not two Veteran rows."

`blocked — payload shape: one selection per run; radios need a behaviour change, ADR-0010/ADR-0015`
(confirmed 2026-10-27). No code change; the block is the deliverable.

### 5.6 tag decision — Law A not satisfied

- **Law A** (tag on any commit whose full suite completed, every failure attributed): **not
  satisfied.** The full Playwright suite has not completed on this tree; round 4 stopped at test
  153/603 on the `career-scenario-select.spec.ts:297` wedge, and the ST-4 follow-up that owns Phases
  3–4 has not handed back.
- **Law B** (prefer a commit whose non-peer failures are zero): **not measurable** for the same
  reason.
- `3.0.0` stays absent. **No tag was cut in this session.**

## Phase 6.5 — independent doc verification (2026-10-27)

| Claim | Result |
|---|---|
| `AGENTS.md` §9 `count: 219` correction (recorded in `c2c72c13`) | **present** at `AGENTS.md:517`, with the 2026-10-27 date and the "any new one fails at 219 + 1" sentence. No edit owed. |
| `AGENTS.md` §13 migration ruleblock | **removed in this session's commit.** Its banner says "removed at Phase 6 exit", and `docs/proposals/frontend-architecture-plan.md:1006` records "Phase 6 — 2.1.0 released 2026-10-23. Definition of done satisfied". The surviving intent (tokens only, no `dark:`, reuse existing primitives) stays in §7 and §13's remaining bullets; what goes is the migration-scoped re-spell rule and its `ponytail:` escape clause. A §20 change-log row records the removal. |
| `SCREEN_SPEC.md` | **no new screen, no rows owed**: Steps 5.2 and 5.3 are blocked, and no screen was restructured this session. |
| `frontend-development-update-plan.md` amendments | the 2026-10-27 block at `:12-31` already carries DM-3's five keys, S7.2's payload block, the corrected "Skip this moment" page, and the framework-audit exit note. **One line remains owed:** the `career-scenario-select:297` wedge classification, which Phase 3 owns and has not produced. No duplicate block was appended. |

## Hand-back state — main agent (2026-10-27 14:50 +08)

- **Tag:** absent (`git tag -l '3.0.*'` empty). Not re-cut.
- **Blocked on the ST-4 hand-back:** Phase 2 (six Pest regressions), Phase 3 (wedge), Phase 4 (full
  browser suite + 46-baseline table), and the `## ST-4 follow-up — hand-back` section that names the
  commit to tag.
- **Ready when it lands:** `CHANGELOG.md` §3.0.0 (Phase 6.2), `VERSION.md` (Phase 6.3),
  `primitive-reconciliation.md` §7.4 (Phase 6.6), the final report (Phase 8), and
  `git tag -a 3.0.0 -m "HFW parity, v3.0.0"` (Phase 7).
- **Foreign files at hand-back.** Full working-tree list with mtimes, all peer-owned, none edited by
  this session:

```text
?? 2026-10-27 13:37:16  .r5state.txt
?? 2026-10-27 14:00:14  .scratch-wedge.txt
 M 2026-10-27 11:48:39  app/Http/Controllers/Career/RaceDecisionController.php
 M 2026-10-27 11:47:36  app/Http/Controllers/Career/RacePlannerController.php
 M 2026-10-27 10:17:19  app/Http/Controllers/Career/SkillsPlannerController.php
 M 2026-10-27 12:28:41  app/Http/Controllers/DatabaseController.php
 M 2026-10-27 01:44:01  app/Http/Controllers/ReviewController.php
 M 2026-10-27 12:19:01  app/Models/RaceCatalogSlot.php
 M 2026-10-27 12:20:49  app/Models/ScenarioRace.php
 M 2026-10-27 12:19:58  app/Models/ScenarioSlot.php
 M 2026-10-27 12:27:31  app/Models/TrainingRun.php
 M 2026-10-27 12:22:09  app/Services/DataPipeline/Contracts/RaceCatalogSourceParser.php
 M 2026-10-27 12:21:38  app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php
?? 2026-10-27 11:06:14  app/Services/HintDiscount.php
 M 2026-10-27 10:23:40  app/Services/SupportCardEffects.php
 M 2026-10-27 11:11:59  config/uma.php
 M 2026-10-27 12:24:02  database/factories/ScenarioRaceFactory.php
 M 2026-10-27 12:23:18  database/factories/ScenarioSlotFactory.php
?? 2026-10-27 13:54:24  database/migrations/2026_10_27_120000_drop_per_race_maiden_gate_columns.php
 M 2026-10-27 12:23:18  database/seeders/ScenarioSlotSeeder.php
 M 2026-10-27 10:26:07  docs/adr/0011-skills-reference-import.md
 M 2026-10-27 10:27:31  docs/adr/0029-per-run-measurement-columns.md
 M 2026-10-27 11:11:23  resources/js/components/career/CareerStatePanel.vue
 M 2026-10-27 10:20:10  resources/js/pages/Career/SkillsPlanner.vue
 M 2026-10-27 01:44:01  resources/js/pages/Career/TraineeProfile.vue
 M 2026-10-27 13:26:03  resources/js/pages/Database/Races.vue
 M 2026-10-27 01:44:01  resources/js/pages/Review/Detail.vue
 M 2026-10-27 02:37:50  resources/js/pages/Review/Index.vue
 M 2026-10-27 01:57:52  resources/js/pages/Veterans/Index.vue
 M 2026-10-27 02:01:16  resources/js/pages/Veterans/Show.vue
 M 2026-10-27 11:34:48  tests/Feature/CareerRaceDecisionTest.php
 M 2026-10-27 13:26:03  tests/Feature/DatabaseTest.php
 M 2026-10-27 10:25:09  tests/Feature/DeckAnalysisReachedEffectsTest.php
 M 2026-10-27 13:45:19  tests/Feature/RaceSlotPanelComposerTest.php
?? 2026-10-27 01:44:01  tests/Feature/ReviewDetailPayloadTest.php
 M 2026-10-27 13:28:27  tests/Feature/Schema/ScenarioSlotMigrationTest.php
 M 2026-10-27 10:23:09  tests/Feature/SkillSpendCoverageTest.php
?? 2026-10-27 02:23:19  tests/browser/annotation-sweep.spec.ts
 M 2026-10-27 01:50:48  tests/browser/career-scenario-select.spec.ts
 M 2026-10-27 01:44:01  tests/browser/dashboard.spec.ts
?? 2026-10-27 13:57:56  tests/browser/zz-committed-scenario.spec.ts
```

  Two of those entries are session-scratch files at the repository root (`.r5state.txt`,
  `.scratch-wedge.txt`). AGENTS.md §9 requires every session artifact under
  `.scratch-uma/<session>/`, so both are filed here rather than moved or deleted: they are not this
  session's files.

- **`85d24959` and `ad876208`** both resolve (`git log -1 --oneline`); neither was reset, rebased,
  split, or amended. `ad876208` still carries the ST-4 hand-back as recorded in the attribution
  incident above (162 added `TASKS.md` lines in that commit's stat); not repaired.

## Final report — round 5 main-agent scope (2026-10-27 15:05 +08)

**Status: the project is NOT closed.** The tag is absent, and the seven completion-gate items under
the ST-4 agent's Phases 0–4 are unmet because no follow-up hand-back exists. This report covers
Phases 5–7 only and states every unmet item plainly.

**1. Per-step status.** 5.1–5.5 **blocked** (the four peer files are byte-identical to round 4's
mtimes; the Legacy payload shape is one column and one selection). 5.6 **not satisfied** (Law A: no
completed full browser suite). 6.1 **done** (`5642593f`). 6.5 **done** for the independent claims
(`55f5a202`); `SCREEN_SPEC.md` owes no rows. 6.2, 6.3, 6.4's wedge line, 6.6, 7 and 8.2 **blocked** on
the ST-4 hand-back.

**2. `DOMAIN OVERRIDE` inventory.** No new override this session; round 4's 10 sites stand.

**3. Tests written, rewritten, retired.** None: no behaviour changed.

**4. Changed files.** `AGENTS.md` (ruleblock retired, one change-log row) and this ledger. Nothing
else was staged or committed by this session.

**5. Commands run.**

| Command | Result | Evidence |
|---|---|---|
| `npm run lint:ui` (working tree) | exit 0; prints `[debt-increase] arbitrary-value count 221 exceeds baseline 219` | `.scratch-uma/main-2026-10-27/lint-ui.log` |
| `node tools/ui-framework-check.mjs` (clean worktree at `d55927cd`) | exit 0; 219 findings | `.scratch-uma/main-2026-10-27/lint-ui-clean.log` |
| `python tools/md_fence_check.py AGENTS.md docs/proposals/TASKS.md` | exit 0; 0 invalid closing fences | inline |
| `php tools/lore.php` | 522 hits, 75 exempt lines; delta +44 from the 478 recorded at `5ed45d67`; **zero hits on the added lines** | `.scratch-uma/main-2026-10-27/lore-docs.log` |
| `php tools/lore.php code` | 224 hits; delta +3 from the ST-4 hand-back's 221; **zero hits in the files this session touched** | `.scratch-uma/main-2026-10-27/lore-code.log` |
| `pint`, `phpstan`, `npm run typecheck` | **not run**: markdown-only change, no PHP/TS path touched | n/a |
| full Pest / full Playwright | **not run** this session; round 4's Pest baseline (1994 passed / 15 failed, all attributed) stands, and the full browser suite is still blocked | round 4's ledger |

**6. Commits and tag.** `5642593f` (Phase 5 ledger), `55f5a202` (ruleblock retirement). **Tag: none**;
`git tag -l '3.0.*'` is empty after both commits.

**7. The 46-baseline.** **Not produced**: the full browser suite has not completed. Round 4's
partial reading (153/603, three failure shapes) is unchanged.

**8. Screenshot blocker.** Unchanged (`mcode-tools` `UND_ERR_HEADERS_TIMEOUT`); no visual criterion was
measured this session.

**9. Safe defaults.** The lint baseline stays at 219 rather than pre-bumping for peer-dirty files; the
ruleblock is deleted rather than re-dated because Phase 6 exited on 2026-10-23; the tag stays absent
because Law A is unmet; no duplicate amendments block was appended.

**10. Prior-session claims re-verified.** `ad876208` still carries the ST-4 hand-back (162 `TASKS.md`
lines in its stat); `AGENTS.md:517` still reads 219; the four peer mtimes are unchanged since round 4;
the Legacy payload shape is unchanged at `d55927cd`; the clean tree measures 219 arbitrary values;
`td-wedge` is still clean at `d55927cd` with one untracked probe (`.wedge-plain.txt`, 13:46:07).

**11. Plan amendments.** None appended: `frontend-development-update-plan.md:12-31` already carries
the five 2026-10-27 items. Only the wedge line is owed, and Phase 3 owns it.

**12. Tag resolution.** `3.0.0` was deleted at `1ca9caa3` and remains absent. No round-5 re-cut. The
next action is the ST-4 hand-back's hash plus a completed full browser suite on that hash.

**13. Deferred items.** A (Legacy radios), D (Veterans and TraineeProfile) and E (lint baseline) are
blocked; the block evidence is the 5.1–5.5 table above.

**14. Foreign files at hand-back.** The 41 paths in the Phase 5 hand-back list, plus one untracked peer
file that appeared during the session: `docs/framework-ui-ux/_audit-prompt.md` (14:47:26). None was
edited.

**15. `85d24959` and `ad876208`.** Both acknowledged as prior sessions' work; both resolve; neither was
reset, rebased, split, or amended.

**16. Worktrees.** `../td-wedge` is the ST-4 agent's and is left in place (clean at `d55927cd`, one
untracked probe). This session created none and removed none.

### Completion gate

| Item | State |
|---|---|
| Phase 1 annotation-sweep + attribution | **unmet** — ST-4 owns |
| Phase 2 six Pest tests | **unmet** — ST-4 owns |
| Phase 3 wedge | **unmet** — ST-4 owns; its probe is in flight |
| Phase 4 full browser suite + 46-baseline | **unmet** — ST-4 owns |
| Phase 5 re-measurement | **met** — every blocked item re-filed with its current mtime |
| Phase 6 documentation at the tag commit | **partly met** — the ruleblock is retired; release docs wait on the tag |
| Phase 7 tag | **unmet** — no commit with a completed suite |
| Final report | **met as a status report**; it reports the project as not closed |

**Tag decision:** Law A not satisfied; Law B not measurable. `3.0.0` stays absent.

---

# v3.0.0 parity audit — Task A (2026-10-28, rev. 2)

**Verdict: parity unverified is confirmed.** 54 HFW pages were compared against the built app at
`http://127.0.0.1:8000` with the rebuilt manifest (2026-10-28 ~23:50). Result: **17 near, 32 divergent,
1 blank/crash, 4 not applicable**. `3.0.0` is **not cut** (§13.1: some pages divergent).

## Method and limits

- Viewport 1280×1000; HFW opened from `file:///D:/Projects/umamusume-laravel13/docs/design-research/frontend-v2-wireframes/<page>.html`.
- Data used: run 26 (Unity Cup, Active) for lifecycle pages, run 24 (URA Finale, Completed) for
  result/cockpit-finale/save-veteran, veteran 8, catalog `special-week`, support card 1, skill 1,
  candidate 1.
- The stored Layout width preference was **Wide** (the owner's row). The audit did not write a
  preference row; `standard` was not measured by a write. Composition (grids) does not change between
  Standard and Wide; only the measure does.
- Screenshots and DOM extracts: `.scratch-uma/session-2026-10-28/audit/out/` (`audit.json`,
  `*.app.png`, `*.hfw.png`, `pairs/`). The scratch dir is session apparatus, not a repository artifact.
- The app was rebuilt before the audit; a pre-rebuild pass produced identical verdicts except where
  noted. `career-save-veteran` was confirmed blank both before and after the rebuild.
- Each page was read in the split-view montage (`.scratch-uma/session-2026-10-28/audit/out/pairs/`), not from the ledger.

## Shell verdict — near

| Region | Verdict | Evidence / deviation |
|---|---|---|
| Rail grouping | pass | Play / My data / Library / System labels render; ten destinations keep their names and marks; collapse keeps `sr-only` words and `title` |
| Topbar | pass | crumb, "2 active careers" status pill, Help icon-button, brand chrome, 48px bar |
| Page head | pass | eyebrow + h1 (one per document) + lede + actions; h1 lives in the banner region |
| Canvas | pass | same measure as topbar; `@container` intact |
| Mobile bar | pass | Home / Career / Legacy / Deck / More at 390px, `aria-label="Primary mobile"`, no horizontal overflow (scrollWidth 390 = innerWidth) |
| Nesting | deviation | app wraps `.topbar` **and** `.phead` in `<header>`; HFW makes `.phead` a sibling of `.topbar` inside `main`. Visual order is identical; DOM region nesting is not. Recorded, not treated as a page defect. |
| Layout mode | deviation | stored preference is Wide; the wireframe has no preference row. Measure differs by design. |

## Severe findings (fix before any near/divergent repair)

1. **`career-save-veteran` renders a blank page.** `CareerLayout.vue:64` computes
   `props.runUrl.match(...)` for the crumb, and `Career/SaveVeteran.vue` does not pass `run-url`, so
   the layout throws `TypeError: Cannot read properties of undefined (reading 'match')`, the Inertia
   root unmounts, and the page renders 0 characters. Reproduced on both an Active and a Completed run.
2. **`/career/snapshot/review` renders the Setup form, not the review.** The h1 reads "Record an
   existing career" and no "Not provided" tiles, no confirm/edit pair, no snapshot stepper. The route's
   state handling needs the read-back branch before this screen can be audited as a review.
3. **Two cockpit variants cannot be verified from local data.** There is no Trackblazer or Grand
   Concert run in the dev database (runs 15–24 URA, 25–26 Unity Cup), so those pairs render the Unity
   Cup shell. Marked `not applicable`, not "divergent", until a run exists.

## Per-page verdicts

`near` = region order matches, divergences are copy/measure/data-presentation only, no annotation
ruling broken. `divergent` = region order, composition, primitives, or a ruling differs.
`absent` = blank or unreachable. `n/a` = variant merged by ruling or no data.

| HFW page | Route | Verdict | First defect |
|---|---|---|---|
| index | / | near | KPI "Careers running 2" vs 1 (data); Recent veterans footer "All veterans" not visible in sample |
| dashboard | / | n/a | index.html wins by §4.1; variant not rendered |
| runs-index | /training-runs | near | filters need "Apply filters"; HFW filters act on change; extra Sort control |
| runs-create | /training-runs/create | near | helper copy only; fields/optional labels match |
| import | /training-runs/import | near | preview button says "Preview import" vs "Read the text"; extra required Trainee/Status; importer checklist differs |
| import-preview | (POST preview) | n/a | no GET route; state exists only after a preview POST |
| career-scenario | /career/setup/scenario | near | reversibility table has 2 rows (Scenario, Trainee), HFW has 4 (Build target, Legacy and deck missing) |
| career-trainee | /career/setup/trainee | divergent | no "Selected · Preview profile" region; roster paginates 25/page vs HFW 6/page; Surface/Distance/Style filters render Any only |
| career-profile | /career/setup/trainee/1 | near | no portrait slot beside h1; forms table truncated; growth zeros match |
| career-target | /career/setup/target | divergent | stat target fields lack "/ 1400 cap" + "% of cap"; no total comparison; stamina-floor table absent |
| career-legacy | /career/setup/legacy | near | inheritance table lacks SOURCE column; Enhanced spark row differs; parent slots empty (draft state) |
| career-deck | /career/setup/deck | near | slots carry ownership radios but no gauge/remove shape; no "Suggest for …"; empty deck (data) |
| career-preflight | /career/setup/preflight | divergent | "What this scenario asks" is a generic N/A table, not the HFW's scenario facts; checks print Missing, no pass/warning states; no "1 warning" pill |
| cockpit-ura-finale | /training-runs/24/cockpit | divergent | info banner absent; stat bars lack percentage marks; advisor/obligations shape differs |
| cockpit-unity-cup | /training-runs/26/cockpit | divergent | info banner absent; no % marks; advisor warning lacks "55 turns ago"; obligations table differs |
| cockpit-trackblazer | — | n/a | no Trackblazer run in local data |
| cockpit-grand-concert | — | n/a | no Grand Concert run in local data |
| training-decision | /training-runs/26/training | divergent | app: facility-level inputs + three training option cards; HFW: five radio disciplines (kbd 1–5, gains/energy inline) + Rest/Recreation/Race + Recorded outcome + savebar |
| race-decision | /training-runs/26/races | divergent | app: mandatory-races table + readiness + calendar select; HFW: available-this-turn table with Enter, selected race detail + placement payouts + savebar |
| career-race-planner | /training-runs/26/races/planner | divergent | no 3-col Planning/Held/Readiness cards; no mandatory/upcoming tables or Compare; no reward comparison |
| event-decision | /training-runs/26/events | divergent | app: coach recommendation/current state/known outcomes/record form; HFW: event copy + 3 options + outcome fields + provenance + savebar |
| inheritance | /training-runs/26/inheritance | divergent | app: milestones + predicted sparks; HFW: ancestry diagram + per-parent spark tables + result-of-pick + savebar |
| skills-planner | /training-runs/26/skills | divergent | no SP budget card, no filters/plan table; app lists purchasable skills + hint discounts |
| run-deck | /training-runs/26/deck | divergent | app: two-column slot editor + picker; HFW: six-card grid with gauges + savebar |
| timeline | /training-runs/26/timeline | divergent | no filters (Kind/Year) or record table; app renders a phase reader instead |
| result | /training-runs/24/result | divergent | app: outcome tiles + skills/aptitudes/race history; HFW: srow final stats against cap + finale-ready check + banked factors |
| career-save-veteran | /training-runs/24/veteran | absent | blank page: CareerLayout `runUrl.match` TypeError |
| career-snapshot-entry | /career/snapshot | divergent | no Snapshot stepper (Entry/Setup/Review) |
| career-snapshot-setup | /career/snapshot/setup | divergent | no stepper; no Back/Review snapshot head actions |
| career-snapshot-review | /career/snapshot/review | divergent | renders the Setup form; no "Not provided" tiles, no confirm/edit |
| legacy | /legacy | near | one saved pair in data; value table/parents card match |
| legacy-builder | /legacy/26 | divergent | app: trainee + parent selects + grandparent inputs; HFW: two parent spark tables + preview + tiles + savebar |
| legacy-compare | /legacy/compare | divergent | selection screen only; no comparison table, difference column, starting-caps cards |
| veterans | /veterans | divergent | rows are prose cards, not the 7-column table; factor grades print 0; no Compare action, no Factor grades/Team eligibility cards |
| veteran-detail | /veterans/8 | divergent | Factors card absent (factors lead in HFW); no "Use as a parent" primary; final-stats shape differs |
| veterans-compare | /veterans/compare | divergent | selection screen only; no Difference column or per-veteran verdict cards |
| catalog | /umamusume | divergent | Surface/Distance/Running style filters absent; growth row "not stored"; 25/page vs 6/page; lede copy differs |
| catalog-detail | /umamusume/special-week | divergent | ★4/★5 columns absent; growth all "Not recorded"; costume strip/layout differs |
| support-cards | /support-cards | divergent | Availability column instead of Server; no Server filter; page size differs |
| support-card-detail | /support-cards/1 | near | no "Add to a deck" primary; second effects card missing; limit-break state unrecorded (data) |
| skills | /skills | divergent | no Distance filter; no Open action column; pagination shape differs |
| skill-detail | /skills/1 | divergent | hint-level cost table and Related table absent |
| database | /database | near | tiles match; counts differ (data); no "Current · 2026-09-27" who-pill |
| database-sources | /database/sources | divergent | no "Matrix verified" banner; sources render as cards with URLs vs HFW Source/Tier/Supplies/Read/State table |
| database-scenarios | /database/scenarios | near | outlier bolding/notes differ; matrices match |
| database-races | /database/races | divergent | filters absent; extra Year/Tier/Gate columns; Distance bands + What a race pays cards absent |
| database-events | /database/events | near | DOM shapes match; not visually sampled below the fold |
| database-shop-items | /database/shop-items | near | grid composition differs (HFW g-2+g-3); ratings column matches |
| database-sparks | /database/sparks | near | blue table lacks Cap added / Per two parents columns; inheritance loop below fold |
| review | /review | divergent | queue renders 25 dense rows; HFW's "How a match is decided" and "Why this exists" cards absent |
| review-detail | /review/1 | divergent | savebar is an inline verdict card; HFW's Best-match card + fixed Not answered/Reject/Confirm pair |
| preferences | /preferences | near | extra theme option "Evangelion Unit-01"; extra "Numeric failure estimate"; widths shown |
| help | /help | divergent | "Unknown" tag row and absence note missing; keyboard rows differ (no 1–5); right column differs |
| error-404 | /this-page-does-not-exist | near | body matches HFW; app renders no shell chrome (Blade error document, by design) |
| error-419 | — | n/a | no GET route; static Blade document not route-audited |
| error-500 | — | n/a | no GET route; static Blade document not route-audited |

## Verdict counts

- **near — 17:** index, runs-index, runs-create, import, career-scenario, career-profile, career-legacy,
  career-deck, legacy, support-card-detail, database, database-scenarios, database-events,
  database-shop-items, database-sparks, preferences, error-404.
- **divergent — 32:** career-trainee, career-target, career-preflight, cockpit-ura-finale,
  cockpit-unity-cup, training-decision, race-decision, career-race-planner, event-decision, inheritance,
  skills-planner, run-deck, timeline, result, career-snapshot-entry, career-snapshot-setup,
  career-snapshot-review, legacy-builder, legacy-compare, veterans, veteran-detail, veterans-compare,
  catalog, catalog-detail, support-cards, skills, skill-detail, database-sources, database-races, review,
  review-detail, help.
- **absent — 1:** career-save-veteran.
- **n/a — 4:** dashboard (merged index ruling), import-preview (POST-only), cockpit-trackblazer and
  cockpit-grand-concert (no run of those scenarios).
- **not route-audited — 2:** error-419, error-500 (Blade documents; no GET route).

## Repair order for Task C (from the evidence, not the slice order)

1. **`career-save-veteran` crash** (one-line guard in `CareerLayout.vue` + a browser spec that asserts
   the page renders its h1), then the snapshot review state.
2. **Shared composition:** the HFW grid primitives `.card`/`.tile`/`.srow`/`.slot` are absent on
   pages whose composition otherwise matches (e.g. career-trainee has the grid but no `.card`,
   `veterans-compare` has no grid at all). Fix the primitive vocabulary at component level before page
   templates.
3. **Lifecycle screens (the 32 divergent majority):** training-decision, race-decision, race-planner,
   event, inheritance, skills-planner, run-deck, timeline, result — each needs the HFW region model, not
   a copy pass. These are rewrites, planned as one slice per screen with its browser spec re-baselined.
4. **Wizard remainder:** career-trainee selected/preview region + page size; career-target cap/hint/total;
   career-preflight scenario-facts + pass/warning checks; legacy-builder two-parent spark picker.
5. **Library/legacy/veterans/review:** catalog filters + growth; catalog detail ★4/★5; support-cards
   Server column; skills filters/Open; skill-detail hint ladder; veterans table + factor grades;
   veteran-detail factors-lead; both compare screens; review cards/savebar.
6. **Copy/identifier pass** after structure: rate limiting the em-dash/identifier/lore findings already
   red at HEAD (they are attributed, not part of the parity verdicts).

**Tag:** stays absent per §13.1 until Task D re-audits these pages to at most "near" with every remaining
deviation recorded as a documented deviation.

---

# Task C — repair pass (2026-10-29)

Working from the Task A verdict table, not from the ledger. Each slice lands as its own commit with its
spec re-baselined; the audit row is the entry state and the delta below is the evidence that moved.

## C1 — Severe 1: the Save Veteran blank page (`f0624c3e`)

- **Cause.** `CareerLayout.vue` read `props.runUrl.match(...)`; `SaveVeteran.vue` passed
  `props.run.run_url`, which does not exist (the controller sends `run_url` top-level), so the layout
  threw and the Inertia root unmounted.
- **Fix.** The caller passes the top-level prop; the layout degrades to the scenario-only crumb when no
  URL arrives. The identity-disclosure fixture also stopped finding the status control: its owner
  predicate used `querySelector('details select#status')`, whose ancestor part matches the scope node
  itself, so every container looked nested; it now compares the inner disclosures against the select.
- **Evidence.** `npx playwright test career-save-veteran -g "renders the record screen"` → **1 passed
  (1.3m)**; `npm run typecheck` → exit 0.

## C2 — Severe 2: the review URL rendered the Setup form (`8595beba`)

- **Cause.** GET `/career/snapshot/review` used the submit-time FormRequest; an empty query failed
  validation and redirected to Setup.
- **Fix.** The route reads the setup query when present, the session draft otherwise, and renders the
  HFW's empty review (eight `Not provided` tiles) with the snapshot stepper and a disabled confirm.
  Rules moved to a static `fieldRules()` so the GET validates the same contract without a submit.
- **Evidence.** `php artisan test SnapshotReviewTest SaveVeteranTest` → **exit 0**, including two new
  cases (bare visit; session-draft read-back); `npx playwright test career-snapshot -g "states that
  nothing has been entered"` → **passed**; pint passed.

## C3 — Primitive vocabulary at component level (`e6f898ae`)

`resources/js/components/ui/{Tile,Note,Pill}.vue` now emit `.tile`/`.note`/`.pill` beside their house
utilities, so the CSS the parity pass ported is reachable from the components that exist for those
shapes. `ui/Card.vue` already emitted `.card`; `.srow` is used directly by the three stat readers.
Adoption in the remaining pages lands with each page's repair slice, per the audit's Task C order.

## C4 — training-decision, screen 1 of 9 (`2b44b45b`)

| Item | Before (audit) | After |
|---|---|---|
| Region model | Advisor, facility inputs, three training cards, record form | Disciplines, Other actions, Why Rest, Recorded outcome, savebar |
| h1 | "Training decision" | "Pick this turn's action" (HFW) |
| Disciplines | none (cards) | 5 radio rows, kbd 1–5, feeder line, published gains, signed Energy |
| Other actions | Rest/Recreation buttons below the form | 3 radios: Rest (Recommended), Recreation, Race |
| Why Rest | none | advisor reason + band line + rest distribution + note |
| Recorded outcome | inside a 20-field form | the HFW's four fields; client totals/turn/facility behind one disclosure |
| Savebar | none | status + Undo turn N + Discard + Record turn N (one filled action) |
| Wit Energy | cost suppressed in payload | row's refund carried signed (`E+5`) and printed "+5 Energy" |

**Deviations, documented in the page:** (1) `HFW OVERRIDE (write fields)` — the turn request requires
the five client totals and the turn number, so they stay in one collapsed disclosure; (2) the failure
line says "failure rate not published" instead of a percentage, because no source in this repository
publishes a ranked discipline failure table; (3) a discipline with no recorded facility level states the
absence rather than a Level-1 figure, which is the DM rule the page already held.

**Verdict delta.** training-decision: **divergent → near**. Evidence: DOM vs HFW headings equal
(Disciplines / Other actions / Why Rest / Recorded outcome), 8 radios, 1 savebar, grids `g-side:2`
`g-3:3` `g-2:2`, console clean; the only extra card is the documented write-field disclosure.

**Gates.** `php artisan test CareerTrainingDetailTest` → **34 passed (472 assertions)**;
`npx playwright test career-training-detail` → **8 passed**; `npm run typecheck` → 0;
`npm run build` → 0; `pint --test` → passed. Test edits are re-baselines at the shipped structure (the
old file pinned the cards/Advisor model), and the two feature cases that pinned Wit's suppressed cost
now pin the signed refund the page prints.

## C5 — remaining screens (not yet started)

The audit's other 31 divergent pages and the `near` re-examinations are unchanged. Order stays as the
audit's repair list: the other eight lifecycle screens (race-decision, career-race-planner,
event-decision, inheritance, skills-planner, run-deck, timeline, result), then the wizard remainder,
then library/legacy/veterans/review, then the copy/identifier pass. `3.0.0` remains **absent**.








