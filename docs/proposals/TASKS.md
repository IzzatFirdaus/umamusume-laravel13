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






