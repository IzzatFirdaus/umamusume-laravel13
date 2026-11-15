# Trainer Desk — Remaining Implementation Instructions

**Scope:** Every phase, slice, and step that has not yet produced a tangible change on the current tree (`master` at `3e22cbe`, worktree `cherry-pet`). Completed work is listed in §0 as a reference only and is excluded from the instruction set.

**Order of execution:** Phases run in the order listed. Within a phase, slices must land green before the next begins. A slice is the unit of commit; a step is the unit of file change.

---

## 0. Completed (do not rebuild)

| Phase | Slices | Status |
| --- | --- | --- |
| A | A1–A4c (catalog detail, skills, support cards, runs list/create/detail/import) | Landed green |
| B | B1 (Blade shell retirement, dead view deletion) | Landed green |
| C | C1 (`BuildTarget`), C2 (`TrainerAdvisor`), C3 (Veteran library) | Landed green |
| D | D1–D18 (Dashboard enrichment, Scenario Selection, Trainee Selection/Profile, Build Target, Legacy Lab, Support Deck Builder, Preflight, Career Cockpit, Training Detail, Race Decision, Event Decision, Inheritance Event, Skills Planner, Career Timeline, Career Result, Save Veteran + Library, Database hub, Settings) | Landed green |
| E | E1–E6 (Scenario Panel shell, URA panel, Unity Cup panel, Trackblazer panel, Race Planner, Grand Concert baseline strip) | Landed green |
| F1 | Selection half (run list routes point to `runs.cockpit`) | Landed green |
| NativePHP config | `config/nativephp.php`, `NativeAppServiceProvider.php`, `package.json` scripts, `bootstrap/app.php` middleware removal | Landed green (unverified: request-secret gate) |
| A0 (partial) | D18 added `role="status"` to `AppLayout.vue` flash | Landed green |
| KI-71 | Grand Concert final scenario key corrected to `our_grand_concert` | Fixed at `480b711` |
| KI-69 | Browser harness owns scratch database via `global-setup.ts` | Fixed in `playwright.config.ts` |

---

## Phase A0 — Accessibility Remediation (remaining items)

Target: WCAG 2.2 level AA on every live screen. The 0.1.0 and 2.0 halves must not disagree.

### A0a — Shell and tokens (remaining)

| Step | File | Change |
| --- | --- | --- |
| A0a-1 | `resources/css/app.css` | Add `@media (prefers-reduced-motion: reduce)` block: set `* { transition-duration: 0.00001s !important; animation-duration: 0.00001s !important; scroll-behavior: auto !important; }`. The MOTION dial stays 1; this is the OS-preference escape hatch. |
| A0a-2 | `resources/css/app.css` | Add `scroll-padding-bottom` equal to the mobile nav height on the scroll container so focused controls are not covered (WCAG 2.4.11). Measure the nav height; use that value. |
| A0a-3 | `resources/js/layouts/AppLayout.vue` | Add `scroll-margin-top` to every focusable element inside `main` so the skip link target clears the sticky header. Assert in the browser spec. |
| A0a-4 | `tests/browser/accessibility.spec.ts` | Add a case that navigates to `/preferences`, tabs to the last control on the page, and asserts it is not covered by the mobile nav (2.4.11). |

### A0b — Ported Vue pages (remaining)

| Step | File | Change |
| --- | --- | --- |
| A0b-1 | `resources/js/pages/Dashboard.vue` | Add `lang="ja"` to every rendered Japanese name. Add a loading state (skeleton or spinner) for the Inertia visit; add an error state for a failed visit. |
| A0b-2 | `resources/js/pages/Catalog/Index.vue` | Add `lang="ja"` to every rendered Japanese name. |
| A0b-3 | `resources/js/pages/Review/Index.vue` | Add `lang="ja"` to every rendered Japanese name. |
| A0b-4 | `resources/js/pages/Preferences/Edit.vue` | Add `lang="ja"` to every rendered Japanese name. Add loading and error states. |
| A0b-5 | `tests/browser/accessibility.spec.ts` | Extend the axe scan to cover all four pages above. Assert 0 A + AA violations. |

### A0c — Live Blade screens (remaining)

The Blade screens listed in §14 of the frontend plan have mostly been deleted by B1. The remaining surfaces that still need a 44px sweep and `aria-describedby` wiring:

| Step | File | Change |
| --- | --- | --- |
| A0c-1 | `resources/views/errors/404.blade.php` | Add a focus target and a skip-to-main path. Ensure heading structure is correct. |
| A0c-2 | `resources/views/errors/419.blade.php` | Same as A0c-1. |
| A0c-3 | `resources/views/errors/500.blade.php` | Same as A0c-1. |
| A0c-4 | `tests/Feature/ErrorPageViewsTest.php` | Add assertions for the focus target and skip link on each error page. |

### A0e — DESIGN.md

| Step | File | Change |
| --- | --- | --- |
| A0e-1 | `DESIGN.md` | In §8, add a line declaring the WCAG 2.2 AA conformance target. Note the `prefers-reduced-motion` addition. |

**A0 acceptance:** axe A + AA clean on every ported page; a keyboard-only pass; 200% zoom; 320px reflow; reduced-motion check. Each screen's browser spec asserts the criteria it names.

---

## Phase F — Record Screen Migration

**Context:** The 0.1.0 run-detail page (`Runs/Show.vue`, route `runs.show`) still owns nine write controls that have no 2.0 owner. The F1 ruling (2026-10-08) names four of them; the plan's §9.6 measured nine. This phase moves each write to its 2.0 host, then flips the redirect.

### F2 — Write owners on 2.0 surfaces

Each step below modifies or creates the named files. No new route, Form Request, or migration is introduced; every route and validator already exists.

#### F2.1 — Run status change → Cockpit header

| Step | File | Change |
| --- | --- | --- |
| F2.1-1 | `app/Http/Controllers/Career/CockpitController.php` | In `headerSection()`, replace the `status_label` string with a `status` key holding `['current' => string, 'options' => [['value','label']]]`. The options are `RunStatus::cases()` mapped to `->value` and `->label()`. |
| F2.1-2 | `app/Http/Controllers/Career/CockpitController.php` | In `headerSection()`, add a `statusUpdateUrl` key: `route('runs.update', $run)`. |
| F2.1-3 | `resources/js/pages/Career/Cockpit.vue` | In the header, replace the `status_label` text with a `<select>` bound to `form.status`, posting via `useForm` to `header.statusUpdateUrl`. On success, show a flash. On error, focus the select and show the error. |
| F2.1-4 | `resources/js/pages/Career/Cockpit.vue` | Add `role="alert"` to the error summary and focus it when `form.hasErrors`. |
| F2.1-5 | `tests/Feature/CareerCockpitTest.php` | Add a props assertion that `header.status` and `header.statusUpdateUrl` are present. Add a PUT case that posts a status change and asserts the run's status changed. |
| F2.1-6 | `tests/browser/career-cockpit.spec.ts` | Add a case: tab to the status select, change it, and assert the header prints the new status after the visit. |

#### F2.2 — Scenario assignment and grade-point period → Cockpit header

| Step | File | Change |
| --- | --- | --- |
| F2.2-1 | `app/Http/Controllers/Career/CockpitController.php` | In `headerSection()`, add `scenario` key: `['current' => string|null, 'options' => [['value','label']]]` from `config('scenarios.php')` keys and labels. |
| F2.2-2 | `app/Http/Controllers/Career/CockpitController.php` | In `headerSection()`, add `gradePeriod` key: `['current' => int|null, 'options' => [...], 'url' => route('runs.update', $run)]` when the run's scenario defines `grade_point_by_grade`. |
| F2.2-3 | `resources/js/pages/Career/Cockpit.vue` | Add a scenario `<select>` and a grade-period `<select>` beside the status select. Both post to `runs.update` via `useForm`. |
| F2.2-4 | `tests/Feature/CareerCockpitTest.php` | Props assertions for `scenario` and `gradePeriod`. PUT cases for each. |
| F2.2-5 | `tests/browser/career-cockpit.spec.ts` | Cases for scenario and grade-period changes. |

#### F2.3 — Turn correction (arbitrary row) → Cockpit correction block

| Step | File | Change |
| --- | --- | --- |
| F2.3-1 | `app/Http/Controllers/Career/CockpitController.php` | In `correctionSection()`, replace `$latest` with a `turns` key holding all recorded turns as an array `[['id','turn','speed','stamina','power','guts','wit','sp','condition','energy']]`. Keep the existing `latest` key for the default row. |
| F2.3-2 | `app/Http/Controllers/Career/CockpitController.php` | Add a `turnUrl` key per row: `route('runs.turns.update', [$run, $turn])`. |
| F2.3-3 | `resources/js/pages/Career/Cockpit.vue` | In the correction block, add a `<select>` that lists recorded turns by number. When the Trainer picks a turn other than the latest, re-render the form fields with that turn's values. Post to the row's `turnUrl`. |
| F2.3-4 | `tests/Feature/CareerCockpitTest.php` | Props assertion that `correction.turns` is an array. PUT case that corrects a non-latest turn and asserts the row changed. |
| F2.3-5 | `tests/browser/career-cockpit.spec.ts` | Case: pick a non-latest turn from the select, edit a stat, submit, and assert the row's value changed. |

#### F2.4 — Turn deletion → Cockpit correction block

| Step | File | Change |
| --- | --- | --- |
| F2.4-1 | `app/Http/Controllers/Career/CockpitController.php` | In `correctionSection()`, add a `deleteUrl` per row: `route('runs.turns.destroy', [$run, $turn])`. |
| F2.4-2 | `app/Http/Controllers/Career/CockpitController.php` | Add a `confirmCopy` key: a two-step disclosure sentence naming the cascade. |
| F2.4-3 | `resources/js/pages/Career/Cockpit.vue` | Add a delete button beside the correction form. It shows a disclosure first (the copy from `confirmCopy`), then a confirm button. On confirm, `router.delete(deleteUrl)`. On success, flash and re-render. |
| F2.4-4 | `tests/Feature/CareerCockpitTest.php` | DELETE case: assert the turn is deleted, the run's turn count decreased, and the failure event is gone. |
| F2.4-5 | `tests/browser/career-cockpit.spec.ts` | Case: click delete, assert the disclosure shows, click confirm, assert the row is gone. |

#### F2.5 — Free-race entry → Race Decision manual arm

| Step | File | Change |
| --- | --- | --- |
| F2.5-1 | `app/Http/Controllers/Career/RaceDecisionController.php` | In `show()`, add a `manualEntry` key: `['url' => route('runs.races.store', $run), 'available' => bool, 'reason' => string|null]`. Available when the turn has no mandatory race already entered. |
| F2.5-2 | `resources/js/pages/Career/RaceDecision.vue` | Add a "Manual race entry" disclosure beside the calendar races. When `manualEntry.available` is true, show a form: turn, name, and a submit button posting to `manualEntry.url` with `entry_mode=manual`. When false, show the reason. |
| F2.5-3 | `tests/Feature/CareerRaceDecisionTest.php` | Props assertions for `manualEntry`. POST case: create a manual race entry and assert the row and the `scenario_slots` row both exist. |
| F2.5-4 | `tests/browser/career-race-decision.spec.ts` | Case: open the manual entry, fill the form, submit, assert the race appears in the calendar. |

#### F2.6 — Skill acquisition status → Skills Planner

| Step | File | Change |
| --- | --- | --- |
| F2.6-1 | `app/Http/Controllers/Career/SkillsPlannerController.php` | In `show()`, add an `acquisition` key: `['url' => route('runs.skills.sync', $run), 'skills' => [['id','name','status','turn']]]`. The skills are the run's `run_skills` rows, grouped as the planner already reads them. |
| F2.6-2 | `resources/js/pages/Career/SkillsPlanner.vue` | Add a status `<select>` per skill row: `Suggested`, `Acquired`, `Skipped`. When the Trainer changes a status, collect all changed rows and post the full array to `acquisition.url` via `useForm`. |
| F2.6-3 | `tests/Feature/CareerSkillsPlannerTest.php` | Props assertions for `acquisition`. POST case: change two skills' statuses and assert both rows changed. |
| F2.6-4 | `tests/browser/career-skills-planner.spec.ts` | Case: change a skill's status from `Suggested` to `Acquired`, assert the row prints the new status after the visit. |

#### F2.7 — Export CSV / JSON → Cockpit header

| Step | File | Change |
| --- | --- | --- |
| F2.7-1 | `app/Http/Controllers/Career/CockpitController.php` | In `headerSection()`, add `exportCsvUrl` and `exportJsonUrl` keys: `route('runs.export.csv', $run)` and `route('runs.export.json', $run)`. |
| F2.7-2 | `resources/js/pages/Career/Cockpit.vue` | In the header, add two links: "Export CSV" and "Export JSON". Each is a plain `<a>` with `download` attribute. |
| F2.7-3 | `tests/Feature/CareerCockpitTest.php` | Props assertions that both URLs are present. |
| F2.7-4 | `tests/browser/career-cockpit.spec.ts` | Case: assert both links are visible and their `href` values match the expected routes. |

#### F2.8 — Run deletion → Cockpit disclosure

| Step | File | Change |
| --- | --- | --- |
| F2.8-1 | `app/Http/Controllers/Career/CockpitController.php` | In `headerSection()`, add `deleteUrl`: `route('runs.destroy', $run)`. Add `deleteCascadeCopy`: a two-step disclosure sentence naming the Veteran cascade. |
| F2.8-2 | `resources/js/pages/Career/Cockpit.vue` | Add a "Delete career" disclosure at the bottom of the header. It shows the cascade copy first, then a confirm button. On confirm, `router.delete(deleteUrl)`. On success, flash and redirect to `/training-runs`. |
| F2.8-3 | `tests/Feature/CareerCockpitTest.php` | DELETE case: assert the run is deleted, the associated Veteran is deleted, and the redirect goes to `/training-runs`. |
| F2.8-4 | `tests/browser/career-cockpit.spec.ts` | Case: open the delete disclosure, assert the cascade copy, click confirm, assert the redirect. |

#### F2.9 — Browser cleanup sweep

| Step | File | Change |
| --- | --- | --- |
| F2.9-1 | `tests/utils/delete-run.ts` | Verify that every browser spec that creates a run calls this helper in `afterEach`. Grep `tests/browser/` for `create` calls that do not have a matching `delete` call. Fix any gaps. |

### F2.10 — The flip

**Prerequisite:** Every F2.1–F2.8 step is green. The 2.0 surfaces own all nine writes.

| Step | File | Change |
| --- | --- | --- |
| F2.10-1 | `app/Http/Controllers/TrainingRunController.php` | In `show()`, replace `Inertia::render('Runs/Show', $this->showData($run))` with `return redirect(route('runs.cockpit', $run));`. |
| F2.10-2 | `tests/Feature/CareerSurfaceCutoverTest.php` | Update the pin: `GET /training-runs/{run}` now asserts a redirect to `runs.cockpit`. |
| F2.10-3 | `tests/Feature/RunsShowTest.php` (and the 37 other files that assert `component('Runs/Show')`) | Per file: either retire the assertion with the screen, or move it to the 2.0 owner that now carries that behaviour. The full list is in plan §9.6. Deleting a test needs owner approval; the disposition list goes in the hand-off. |
| F2.10-4 | `app/Http/Controllers/DashboardController.php` | In `veteranRow()['run_url']`, change the target from `runs.show` to `runs.cockpit`. |
| F2.10-5 | `app/Http/Controllers/Career/CockpitController.php` | In `runSection()['run_url']`, change the target from `runs.show` to `runs.cockpit`. |
| F2.10-6 | `app/Http/Controllers/Career/CockpitController.php` | In `strip.links.run_url`, change the target. |
| F2.10-7 | `app/Http/Controllers/TrainingRunController.php` | In `store`, `update`, `destroyTurn`, `syncSkills`, `syncDeck`, `storeRace`, `storePurchase` — change the redirect target from `runs.show` to `runs.cockpit`. |
| F2.10-8 | `tests/browser/career-cockpit.spec.ts` | Re-run the full spec. Assert that no link on the Cockpit points to `runs.show`. |
| F2.10-9 | `SCREEN_SPEC.md` | Update `SCR-RUN-003` status to "Redirected to `SCR-CAR-011`". Update `SCR-CAR-011` to reflect the nine write owners. |
| F2.10-10 | `README.md` | Update the route table: `runs.show` now redirects. |

**F2 acceptance:** The full suite is green. `npm run build` exits 0. `npm run typecheck` exits 0. `npm run test:browser` is green. PHPStan L6 is clean. Pint is clean. Lore is clean.

---

## Phase G — NativePHP Packaging (Windows x64)

**Context:** `config/nativephp.php`, `NativeAppServiceProvider.php`, `package.json` scripts, and `bootstrap/app.php` are landed. The build has been attempted five times; every attempt reached "Copying App to build directory" or later but the final artifact was never produced. The `npm ci` step (Electron dependency install) takes 14 minutes on a cold cache; the `composer install --no-dev` step in `PrunesVendorDirectory.php:15` has a hardcoded 300-second timeout that is insufficient for `nativephp/php-bin` downloads. The `vendor/nativephp/desktop/resources/electron/node_modules` directory now exists (cached from the last attempt), so subsequent builds skip that step.

### G1 — Complete the build

| Step | Action | Expected output |
| --- | --- | --- |
| G1-1 | Run `php artisan native:build win x64 --no-interaction` as a background process with no shell timeout. The command must run to completion. | The process exits 0. A `.exe` artifact appears in `nativephp/electron/dist/`. |
| G1-2 | If the build fails at `composer install --no-dev` with a timeout: the vendor file `vendor/nativephp/desktop/src/Builder/Concerns/PrunesVendorDirectory.php:15` hardcodes `timeout(300)`. Do not edit the vendor file. Instead, pre-seed the build directory's vendor cache: run `cd vendor/nativephp/desktop/resources/electron && npm ci` to complete the Electron install, then run `cd vendor/nativephp/desktop/resources/build && composer install --no-dev` manually (no timeout). After that, re-run `native:build`. The cached download makes the in-process call complete within 300s. | The `composer install` step completes within the timeout. The build proceeds to `electron-builder`. |
| G1-3 | If the build fails at `electron-builder`: inspect the error. Common causes: missing icon file, missing app ID, missing signing config. Fix the config or the icon path in `config/nativephp.php`. Re-run. | The `.exe` artifact is produced. |
| G1-4 | Record the artifact path, size, and SHA-256 in the hand-off. | A file hash and size are recorded. |

### G2 — Validate the artifact

| Step | Action | Expected output |
| --- | --- | --- |
| G2-1 | Verify the `.exe` is a valid NSIS installer: `file nativephp/electron/dist/*.exe` reports "PE32" and "NSIS". | The file type is correct. |
| G2-2 | Verify the artifact size is in the expected range (100–300 MB for a Windows NativePHP app). | The size is plausible. |
| G2-3 | Record the artifact in `docs/adr/` or `docs/research-scratch/SLICE-RECORDS.md` with the build command, the artifact path, and the SHA-256. | The record exists. |

### G3 — Inspect packaged environment and APP_KEY

| Step | Action | Expected output |
| --- | --- | --- |
| G3-1 | Locate the packaged `.env` file inside the build output. The path is `nativephp/electron/dist/win-unpacked/resources/build/app/.env` (or the equivalent in the `app.asar` archive). If the build uses `app.asar`, extract it: `npx asar extract nativephp/electron/dist/win-unpacked/resources/app.asar /tmp/asar-extracted`. | The `.env` file is readable. |
| G3-2 | Verify that `APP_KEY` is present and is the value from `.env` at build time. The key is base64-encoded. | `APP_KEY` matches the source `.env`. |
| G3-3 | Verify that all `cleanup_env_keys` from `config/nativephp.php` are absent from the packaged `.env`. | No development-only keys leak. |
| G3-4 | Record any discrepancies in the hand-off. | The record exists. |

### G4 — Verify the request-secret gate

| Step | Action | Expected output |
| --- | --- | --- |
| G4-1 | Check that `bootstrap/app.php` does not contain a manual `PreventRegularBrowserAccess` middleware prepend. The `NativeServiceProvider` should auto-register it when `nativephp-internal.running` is true. | The middleware is not manually prepended. |
| G4-2 | If the middleware is absent from the app's middleware stack in the packaged runtime: the gate is not enforced. This is a defect. File it in `KNOWN-ISSUES.md`. | A `KI-nn` entry exists if the gate is missing. |
| G4-3 | If the gate is present: verify it returns 403 for a request without the `X-NativePHP-Secret` header or `_php_native` cookie. This can be tested by running the packaged app and making an HTTP request to `http://127.0.0.1:{port}` without the secret. | The 403 response is recorded. |

### G5 — Verify user-data paths

| Step | Action | Expected output |
| --- | --- | --- |
| G5-1 | Check `vendor/nativephp/desktop/resources/electron/electron-plugin/src/server/php.ts` for the `app.getPath('userData')` call. This is the path where Electron stores the app's user data (storage, database, bootstrap). | The path is `app.getPath('userData')`. |
| G5-2 | Record the resolved path for Windows. It is typically `%APPDATA%/{app_id}` or `%LOCALAPPDATA%/{app_id}` depending on the Electron version. | The path is recorded. |
| G5-3 | Verify that the `NATIVEPHP_NSIS_DELETE_APP_DATA` flag in `config/nativephp.php` controls whether this directory is deleted on uninstall. The current value is `env('NATIVEPHP_NSIS_DELETE_APP_DATA', false)`, so the default is `false`. | The flag and its effect are recorded. |

### G6 — Test install, upgrade, and uninstall persistence

| Step | Action | Expected output |
| --- | --- | --- |
| G6-1 | Run the `.exe` installer. Accept the defaults. The app installs to `%LOCALAPPDATA%\Programs\{app_id}` (or the NSIS default). | The app launches. |
| G6-2 | Launch the app. Verify the window opens and the app responds to requests. | The window renders. |
| G6-3 | Create a test run in the app. Verify it persists across app restarts (the database is in the user-data directory). | The run survives a restart. |
| G6-4 | Re-run the `.exe` installer (upgrade path). Verify the existing data is preserved. | The run still exists after upgrade. |
| G6-5 | Uninstall via Add/Remove Programs. If `NATIVEPHP_NSIS_DELETE_APP_DATA` is `false`, the data directory should persist. If `true`, it should be deleted. | The persistence behaviour matches the flag. |
| G6-6 | Record all results in `docs/research-scratch/SLICE-RECORDS.md`. | The record exists. |

---

## Phase A0d — Error Pages (remaining)

| Step | File | Change |
| --- | --- | --- |
| A0d-1 | `resources/views/errors/404.blade.php` | Add a focus target (`tabindex="-1"` on the `<main>` element) and a skip-to-main link. Ensure the heading structure starts at `<h1>`. |
| A0d-2 | `resources/views/errors/419.blade.php` | Same as A0d-1. |
| A0d-3 | `resources/views/errors/500.blade.php` | Same as A0d-1. |
| A0d-4 | `tests/Feature/ErrorPageViewsTest.php` | Add assertions: each error page has exactly one `<h1>`, a focusable element, and a skip link. |

---

## Phase H — Open Known Issues (code changes only)

These are `KNOWN-ISSUES.md` entries that require code changes, not just documentation updates.

### KI-73 — Port 8127 wedged

| Step | Action | Expected output |
| --- | --- | --- |
| H-1 | Run `netstat -ano | findstr 8127` to identify the PID holding port 8127. Reap it: `taskkill /F /PID {pid}`. | Port 8127 is free. |
| H-2 | Re-run `npm run test:browser`. Verify the suite starts without a port conflict. | The suite starts cleanly. |

### KI-76 — SSR timeout

| Step | File | Change |
| --- | --- | --- |
| H-3 | `tests/browser/global-setup.ts` | Add `INERTIA_SSR_ENABLED=false` to the environment passed to the web server. This prevents the Inertia SSR endpoint from timing out when Vite's dev server is not running. |
| H-4 | `playwright.config.ts` | Add `INERTIA_SSR_ENABLED: 'false'` to the `webServerEnv` object. |

---

## Phase I — Final Verification

Run after every phase above is green.

| Step | Command | Expected output |
| --- | --- | --- |
| I-1 | `php artisan test --compact` | Full suite passes. |
| I-2 | `npm run typecheck` | Exit 0. |
| I-3 | `npm run build` | Exit 0. |
| I-4 | `npm run test:browser` | Full browser suite passes. |
| I-5 | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | Exit 0, no errors. |
| I-6 | `vendor/bin/pint --test` | Exit 0, no changes needed. |
| I-7 | `composer lore` | No hits, or all hits have rulings. |
| I-8 | `composer lore-code` | No hits, or all hits have rulings. |
| I-9 | `php artisan migrate:status` | All migrations are up to date. |
| I-10 | `git status` | Working tree is clean (or only the expected files are staged). |
