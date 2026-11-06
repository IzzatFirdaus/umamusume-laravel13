# Flows — Navigation and App Entry

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `routes/web.php`, `resources/js/layouts/*.vue`, `app/Http/Controllers/DashboardController.php`, `app/Http/Middleware/HandleInertiaRequests.php`. Line numbers are accurate at this commit only.

---

## 1. Request entry

| Step | What happens | Evidence |
| --- | --- | --- |
| 1 | Browser hits `/` | `routes/web.php` — `Route::get('/', [DashboardController::class, 'index'])->name('home')` |
| 2 | `DashboardController::index()` builds props | `DashboardController.php:54-111` |
| 3 | Inertia renders `Dashboard.vue` | `Inertia::render('Dashboard', [...])` |
| 4 | Shared props merge in from the Inertia middleware | `HandleInertiaRequests` |

**Shared Inertia props** (`HandleInertiaRequests`): `app` (`name`, `version`, `ruleset` — always `null` by decision, `theme` from `Preference::resolvedTheme()`), `flash.status`, `preferences.settings`, `dashboard.wide_bento_enabled`.

There is no auth middleware, no login route, no session user. The app binds to loopback only (`AGENTS.md` §1, `PRD.md` §6.10).

---

## 2. The Dashboard (`/`, `home`)

### 2.1 Props

| Prop | Shape | Source |
| --- | --- | --- |
| `activeCareer` | `null` or one run summary | `activeCareer()` — newest `Active` run by `id` (`:127-192`) |
| `activeCareerCount` | int | count of `Active` runs (`:62`) |
| `finishedRunCount` | int | count of `Completed` runs (`:65`) |
| `globalScenarioCount` | int | scenarios in config carrying `live_on_global` (`:66-69`) |
| `careersUrl` | string | `route('runs.index')` |
| `recentVeterans` | `{rows, total, limit, library_url}` | `recentVeterans()`, `RECENT_VETERANS = 3` (`:52`, `:311-326`) |
| `needsYou` | `{dataStatus, veteranCount, reviewPending}` | `:81-85` |
| `quickActions` | list of `{label, to, icon}` | `quickActions()` (`:364-386`) |
| `counts` | `careersRunning`, `trainees`, `catalogueTrainees`, `forms`, `skills`, `supportCards` | `:100-108` |
| `dataStatus` | `{label, state, stale, verified_at, source}` | `dataStatus()` (`:388-410`) |

### 2.2 `activeCareer` internals

Each row carries: `run_id`, `trainee`, `trainee_ja`, `scenario_label`, `position_label`, `cockpit_turn`, `cockpit_label`, `turn`, `position_turn`, `total_turns`, `energy`, `resource`, `team_rank`, `artworkURL`, `indep`, `resume_url`.

Two position readings deliberately coexist:

- `turn` — the count of logged turns (`TrainingRun::stripValues()`).
- `position_turn` / `cockpit_turn` — the Cockpit's standing (`TrainingRun::decisionTurn()`: imported position for a snapshot run, else the turn after the last logged one).

The page prints both **only when they disagree**; that divergence is a recorded known defect (HFW `index.html:102-107`), not something the screen smooths over.

`total_turns` = `TrainingRun::TURNS_PER_YEAR` multiplied by `RaceCatalogSlot::YEAR_SENIOR` = 24 x 3 = 72. Read from the model, never typed.

### 2.3 Independent Training overlay

`independentStatus()` (`:197-217`) returns `null` for a normal career, else `{in_progress, completes_at, policy_label, abandon_url}`. While the 50-minute window is open the card swaps **Resume** for a timer; abandoning posts to `runs.indep.abandon` (see `flows-career-workspace.md`).

### 2.4 Data-status tile

`state` is composed from `config('scenarios.stale')`, and the risk border reads the boolean `stale`, never the display word (`AGENTS.md` §5 forbids matching on display strings). `source` composes `config('scenarios.source')` plus a stale marker when stale. `verified_at` is printed beside it.

### 2.5 Quick actions

Four destinations the sidebar does **not** carry: `Import a historical career` leads to `runs.import`, `Review queue` to `review.index`, `Open settings` to `preferences.edit`, `Database hub` to `database.index`. `icon` is a key in `NavGlyph`'s mark list — the same mark the rail uses, so one icon set serves both.

### 2.6 Two import doors on one screen

The empty Active-Career state carries `Import an existing career` (an inline `Link` to `/career/snapshot`) while the quick-actions list carries `Import a historical career` (to `/training-runs/import`). The difference is not stated on the screen — round-6 §11.2 records this as `Confirmed defect (copy/IA)`, rule `IA-1` primary.

---

## 3. Shells

Three Inertia layouts, all children of `AppLayout.vue`:

| Layout | Owner | Props |
| --- | --- | --- |
| `AppLayout.vue` | global shell (rail + mobile bar + topbar + skip link + `VisitProgressBar`) | — |
| `CareerLayout.vue` | run-scoped screens | `trainee`, `scenarioLabel`, `statusLabel`, `runUrl`, optional `traineeJa`, `status`, `position`, `current` |
| `SetupLayout.vue` | wizard | `step`, `choices`, plus step metadata |

`AppLayout` carries: `VisitProgressBar` (teleported to `<body>`; the replacement for KI-63's invalid ARIA bar), a `Skip to content` link, the rail, the mobile bar, and the topbar with the `?` Help door (`href="/help"` at `:550`).

---

## 4. Primary navigation — `AppLayout.vue`

### 4.1 Ten destinations, grouped in four

The rail is grouped (HFW `shell.js` `NAV`), which is how ten items meet the "at or under eight" guideline without deleting a destination:

| Group | Item | `to` | Mobile slot | `inBar` | `prefetches` |
| --- | --- | --- | --- | --- | --- |
| Play | Dashboard | `/` | Home | yes | yes |
| Play | New Career | `/career/setup/scenario` | — | no | **no** |
| My data | Careers | `/training-runs` | Career | yes | yes |
| My data | Legacy Lab | `/legacy` | Legacy | yes | yes |
| My data | Veterans | `/veterans` | — | no | yes |
| My data | Review | `/review` | — | no | yes |
| Library | Support Cards | `/support-cards` | Deck | yes | yes |
| Library | Skills | `/skills` | — | no | yes |
| System | Database | `/database` | — | no | yes |
| System | Settings | `/preferences` | — | no | yes |

### 4.2 Prefetch rule

Nine of ten prefetch on hover (props plus lazy chunk). **New Career does not**, and that is the whole exception: Inertia caches a prefetched response for about 30 seconds, and the wizard's steps render session-draft state, so a reused prefetch would show the scenario or trainee chosen *before* the save that just changed it. A read-only catalog, library or settings screen cannot go stale the way a draft can.

### 4.3 Active state

`isCurrent(to)` (`:47-51`) matches on prefix after stripping the query string:

```ts
const [path] = page.url.split('?');

return path === to || path.startsWith(`${to}/`);
```

So `/legacy/12` and `/legacy/compare` both mark Legacy Lab current, and applying a filter does not drop `aria-current="page"`.

### 4.4 Mobile bar

Five slots: four `inBar` destinations plus a native `<details>` "More" disclosure carrying the other six (`:303-364`). Slot class is `flex-1 min-w-0` (not `shrink-0`) so five slots divide the viewport instead of overflowing at 320 px. `moreIsCurrent` is a `computed` so the disclosure marks itself when the current screen lives behind it.

"More" closes and returns focus to its `<summary>` on selection (`onMoreToggle`, focus restore in `nextTick`).

### 4.5 Collapsible rail

Collapsed means marks only; expanded means words only. The choice persists in `localStorage` under `trainer-desk.nav-rail` (`'compact'` or `'wide'`, `:258-275`) — deliberately **not** a `Preference` row, because `Preference::KEYS` is exactly `theme`, `failure_estimate`, `settings`. Group headings stay in the DOM when collapsed, so the mark keeps its accessible name.

### 4.6 Named-absence branch

`to: null` renders "not built" rather than a dead link. No destination uses it now that the Veteran library landed; the branch stays because `SetupLayout.vue` uses the same mechanism and a destination that lands ahead of its screen needs it.

---

## 5. Career shell — `CareerLayout.vue`

- Wraps `AppLayout` (not a second shell): global destinations stay reachable from every career screen.
- Carries the career bar: the trainee, scenario and screen crumb, where the screen segment is the live section key capitalised (`cockpit` renders `Cockpit`).
- Forwards `#title` into `AppLayout`'s heading so the document keeps exactly one `h1`.
- Carries `CareerSectionNav.vue`, the nine-section stepper: `cockpit`, `training`, `races`, `skills`, `events`, `inheritance`, `deck`, `timeline`, `result`. Section hrefs are built from the run URL base (`runUrl.replace(/\/cockpit$/, '')`).
- **Reachability rule** (`CareerSectionNav.vue:72-95`): with a known terminal status (`Completed` or `Retired`), `result` becomes live and `training` does not (`The career has ended, so no turns are left to decide`); with an absent or blank status every section stays reachable, which is the safe default because a wrongly disabled tab hides a live screen. Unreachable steps are named in prose and listed, not left as a `title` on a dead control.
- `current` reads the live section key, so the crumb and the stepper cannot drift.

The `run_url` prop is always `route('runs.cockpit', $run)`. No browser file addresses `runs.show` literally — pinned by `tests/Feature/NoStaleRunRecordLinksTest.php`.

---

## 6. Setup shell — `SetupLayout.vue`

Step rail, six entries (`:60-66`): `Scenario`, `Trainee`, `Build target`, `Legacy`, `Deck`, `Preflight`. Renders `Step N of 6`, a `role="list"` stepper with `data-setup-steps`, and one leave-wizard control in the head band. All six steps are live — the ancestry step and the deck step both landed.

---

## 7. Retired run record

`GET /training-runs/{run}` (`runs.show`) leads to `TrainingRunController::redirect()`. `{run}` still binds, so a missing id 404s rather than silently landing somewhere.

Only the **GET** is redirected. The write routes registered under `training-runs/{run}` keep their own destinations.

---

## 8. Ambient UI

| Element | Behaviour |
| --- | --- |
| `VisitProgressBar` | Inertia navigation progress; teleported to `<body>` so every shell carries it and no page imports it |
| Skip link | `href="#main"`, `sr-only` until focused |
| Flash | `role="status"` on success lines; a refused write focuses a `role="alert"` (WCAG 2.2 SC 3.3.1) |
| Help door | Topbar `?` leads to `/help`; deliberately absent from the rail |
| Theme | `app.theme` shared prop from `Preference::resolvedTheme()`; the theme change lands on next reload (the flash says so) |

---

## 9. Verification surface

| Claim | Pinned by |
| --- | --- |
| No browser file addresses `runs.show` | `NoStaleRunRecordLinksTest` |
| One `h1` per route | `tests/browser/ui-hygiene.spec.ts` |
| Identifiers and bare `N/A` rules | `UiHygieneTest` |
| Dashboard props and figures | `DashboardTest`, `IndependentTrainingDashboardTest` |
| Career section reachability | `CareerSectionNavTest` |
| Route inventory completeness | `RouteCoverageTest` |
