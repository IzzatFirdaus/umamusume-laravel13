# Sitrep: Frontend Rendered Verification — 2026-11-22

**Session:** `829ef0acf8f7b36079ca2b3374c9fdac7ef76087` (HEAD, `master`)
**Workspace:** `D:\Projects\umamusume-laravel13` (Trainer Desk, Laravel 13 / Inertia / Vue 3 / Tailwind v4 / Vite)
**Agent:** lead (main frontend implementation agent)
**Date:** 2026-11-22
**Roadmap:** `docs/roadmap/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md`
**Predecessor sitrep:** `docs/sitrep/sitrep-frontend-completion-2026-11-21.md`

---

## Objective

Resume the authorized frontend modernization from the actual repository state: convert the
source-only bento audit into **rendered** evidence across all safely reachable user-facing
screens, fix what the rendering proves is defective, and verify it. Preserve the completed
Snapshot Setup correction. Do not produce another audit-only report.

---

## Environment blocker, found and closed first

Nothing could be measured or edited at session start. The cause was **one** defect, not many:

| Symptom | Detail |
|---|---|
| Writes denied | `resources/js`, `public/build`, `storage/logs`, `storage/framework/views`, `bootstrap/cache` all refused file writes; Laravel could not append `laravel.log`, so even `php artisan` commands died on the logger. |
| `php -S` dead | `Fatal Error Cannot create mutex (error 5)`. |
| Child processes dead | Node `spawnSync` -> `spawn EPERM`; Playwright could not start; headless Chromium died on `mojo ... platform_channel.cc:112 Check failed: Access is denied`. |

Diagnosis and repair used the repository's sandbox-permission procedure: one scoped run on
`resources/js` with `-AllowRoot` set to the repo root. Two grants were written (on
`resources` and `resources/js`) and both verified. Backups and rollback scripts remain under
`.scratch-uma/lead/acl-repair/`.

Re-probe after repair, all quoting actual output:

```text
WRITE OK   resources/js
WRITE OK   public/build
WRITE OK   storage/logs
WRITE OK   storage/framework/views
WRITE OK   bootstrap/cache
```

```text
spawn status 0 out 11
```

```text
INFO  Server running on [http://127.0.0.1:8161].
2026-11-22 18:47:38 /up ................................................ ~ 3.58ms
```

An earlier `javascript:` navigation error seen in the transcript was a discarded measurement
hack of this session, not an application fault.

---

## Route reconciliation

**59 user-facing GET routes rendered** at 1280x900 against the live server (built manifest),
**0 failures**, exactly one `h1` on every route, **0 document-overflow routes**.

Registered routes that are correctly *not* standalone screens:

| Route | Disposition |
|---|---|
| `/training-runs/{run}` | 302 -> `runs.cockpit` (retired 2026-10-09, F2) |
| `/up` | Framework health endpoint |
| `/storage/{path}`, `/artwork/{kind}/{id}` | Asset handlers |
| `/api/v1/*` | JSON |
| `/training-runs/{run}/export/{format}`, `/preferences/export` | Download responses |

The previous 34/62 coverage record and the 66-entry screen inventory are both superseded by
this measured list.

---

## Defects found and corrected

Every one below was opened by a browser measurement. Each has a before and an after.

### 1. `/legacy/compare` — picker showed four selected runs and selected none

Four compare checkboxes rendered **ticked** on a page whose `columns` prop was empty; clicking
a "ticked" box *unticked* it, so the control's appearance contradicted its value.

Root cause was in the shared primitive, not the page:
`BaseCheckbox.modelValue: boolean | string | number` compiles to
`{type:[Boolean,String,Number]}`; the Vue runtime's boolean cast rewrites an empty-string prop
to `true` (`runtime-core`: `opt[1] && value === ''`), and `isChecked()` then treats the
boolean as the answer and ignores `value`.

Fix: union reordered to `string | number | boolean` so the compiler emits
`[String,Number,Boolean]` and `shouldCastTrue` stays `false`. The order's meaning is now
documented in the prop's own docstring, because the next editor would otherwise "tidy" it back.

Evidence — before:

```text
boxes: 39=true, 37=true, 35=true, 34=true
emptyMessage: true
```

after:

```text
boxes: 39=false, 37=false, 35=false, 34=false
emptyMessage: true
```

and ticking two then pressing Compare renders the table:

```text
url: ?runs%5B0%5D=39&runs%5B1%5D=37
headers: ["Property","Rice Shower  Run #39","Admire Vega  Run #37","Difference"]
```

Regression sweep of the other six consumers (Preferences, Snapshot Setup, Legacy Builder,
Veterans index, Trainees catalogue, Deck wizard): all render as before.

### 2. `/training-runs/{run}/cockpit` — Advisor drew a card inside a card

`AdvisorRail.vue` renders the region's `.card`; `NextDecision.vue` rendered **another**
`.card` inside it, so a 3px left rail sat inside a 3px left rail and the rail read as two
stacked panels rather than one answer. Separately, the "OBLIGATIONS" label sat 0px under the
paragraph above it and read as that sentence's tail.

Fix: inner `div.card` -> `div`; the obligations `section` gained `mt-4`.

Evidence:

```text
before: visibleNestedCards = 1, gapToPrevPx = 0
after:  visibleNestedCards = 0, gapToPrevPx = 16
```

(The five further `.card` matches inside the rail are `provenance-tooltip` nodes measured at
width 0 — a pre-existing popover mechanism, not layout.)

### 3. Skills / Deck / Legacy — WCAG 2.2 AA 2.4.11 (Focus Not Obscured)

`.savebar` is `position:sticky; bottom:0` and 94px tall. Tabbing each screen put focused
controls underneath it. Scrolling did reach them, so nothing was unreachable — 2.4.11 is about
visibility, not reachability.

Fix in `resources/css/app.css`, following the repo's two existing precedents (the mobile bar's
4rem and the setup step nav's 6.5rem), scoped with `:has()` so no unrelated screen pays:

```css
html:has(.savebar) { scroll-padding-bottom: 6rem; }
```

Evidence — genuinely occluded controls (the bar's own controls and the off-canvas rail excluded):

```text
/training-runs/36/skills  6 -> 0
/training-runs/36/deck    5 -> 0
/legacy/39               15 -> 1
```

The residual 1 on `/legacy/39` is the "Data sources" link in the hidden rail at x=0, while the
bar occupies x=262..1254 — not occluded. Excluding that rail from the count is correct, and
calling it a remaining defect would be false.

### 4. `/help` — 1360px of dead space and unreadable reference tables

The left column ended at 1053px while the right ran to 2413px. Six of the eight answer cards
lived in the 315px rail, including a 133-word table whose rows wrapped every three or four
words.

Fix: "Rules that are easier to look up" moved from the rail into the wide column. Chosen by
measurement — that card is 781px tall at 315px wide and 428px at 663px.

Evidence:

```text
before: left 1053 / right 2413  (delta 1360)
after:  left 1495 / right 1617  (delta 122)
```

`help.spec.ts` pins the `h2` set and `regions[0] === 'help-loop'`; both still hold.

### 5. `Snapshot/Setup.vue` — pre-existing `TS7053` failing the typecheck gate

One-line widening of the error bag's key type. No behaviour change. Recorded explicitly as
**pre-existing and another workstream's file** — this line did not introduce it and does not
claim it.

---

## Screens inspected and deliberately left alone

An absence of change is a result here, not an omission:

| Screen | Finding |
|---|---|
| `/` Dashboard | 8 cards, `g-side` 663/315. All three layout modes driven **through the stored preference**: canvas 1364px (wide) / 1152px (standard) / 768px (compact). Compact's single-column stack is the mode's purpose, not wasted space. |
| `/training-runs/{run}/training` | At 320px the discipline table sits in a named focusable region (`role=region`, `tabindex=0`, "Discipline comparison, scrollable", client 252 / scroll 334) with zero document overflow. |
| `/training-runs/{run}/scenario` | Two rows hold a single `col-span-4` card on a 12-column grid. That is grid arithmetic; forcing symmetry would have been the defect. |
| `/training-runs/{run}/timeline` | Stat strip, record table, phase grouping — coherent. |
| `/database/scenarios` | Four per-scenario panels, consistent table grammar. |
| `/preferences` | Columns balance exactly, 1385 / 1385. |
| `/career/snapshot/setup` | Prior correction intact: `g-side-wide-second` gives 312/656, 992px form, three stat columns. Not reimplemented. |

---

## Themes

Applied by setting `html[data-theme]` — the same attribute `app.blade.php` prints — and
re-measured on Dashboard, Cockpit, Snapshot Setup and Compare. Computed colours differ per
theme as expected. EVA-01 keeps its violet primary **plus** its secondary accents (the green
card-header rule, amber caution pills, green progress fills); it is not a monochrome wash.

---

## Quality gates (all commands run this session)

| Gate | Command | Result |
|---|---|---|
| Build | `npm run build` | `BUILD=0` |
| TypeScript | `npm run typecheck` | `TSC=0` |
| UI framework lint | `npm run lint:ui` | `LINT=0`; arbitrary-value **208** vs baseline 209; no `card-in-card`, no `debt-increase`, no `dark-utility` |
| Browser specs | `career-cockpit.spec.ts` + `legacy.spec.ts` | **45 passed** |
| Browser specs | `help.spec.ts` + `career-cockpit.spec.ts` | **44 passed** (13.9m) |

`lint:ui` total findings moved 525 -> 526 across the session. The countable gate
(`arbitrary-value`) went **down** to 208; the delta is a `literal-length` entry in
`app.css`, which is not the ratcheted counter. Verified by rule breakdown rather than assumed.

Full PHP suite was **not** run — this was a frontend-only pass and `AGENTS.md` §9 restricts
the suite to checkpoint/gate moments.

---

## Files changed by this agent

| File | +/− | Change |
|---|---|---|
| `resources/js/components/Form/BaseCheckbox.vue` | +12 / −1 | Prop union order + docstring |
| `resources/js/components/career/NextDecision.vue` | +11 / −3 | Nested card removed; obligations spacing |
| `resources/js/pages/Help.vue` | +93 / −12 | Rules card moved to the wide column |
| `resources/css/app.css` | +17 / −0 | `html:has(.savebar)` scroll-padding |
| `resources/js/pages/Career/Snapshot/Setup.vue` | +6 / −3 | Typecheck widening only (rest of its 493-line diff is a **peer's** work) |

> One self-inflicted slip, caught and reverted before reporting: writing `Help.vue` whole-file
> normalised an en-dash in a keyboard-table row to a hyphen. Restored, then confirmed the
> string in the working tree matches `git show HEAD:resources/js/pages/Help.vue`; the diff no
> longer carries that line.

**Not committed, not staged, not pushed.** Working tree holds **166** entries, the large
majority belonging to concurrent workstreams and preserved untouched.

---

## Evidence not inspected / not verified

| Item | Why |
|---|---|
| Theme persistence through the real Save flow | Themes were set on the attribute, not submitted through Preferences. Palette verified; write path not. |
| `/veterans/compare` picker | Renders zero checkboxes on an empty library; selection could not be exercised. |
| 419 / 500 and loading states | Not exercised in browser this pass. |
| Sub-24px targets | Collected by the sweep (checkbox cells 13-20px, inline table links 16-20px) but not triaged; most sit inside a `min-h-11` label or `table.t`'s 44px cell. |
| Career Workspace at 768/390/320 | Only Cockpit, Training, Snapshot Setup and Compare were checked narrow. Race Decision, Event Decision, Inheritance, Skills, Timeline and Result remain 1280-only. |
| G-18 token contrast gate | Recorded as unrun on this host; not attempted. |

---

## Blockers

| Blocker | Impact | Resolution |
|---|---|---|
| ~~DSH sandbox write/spawn denial~~ | ~~No edits, no server, no browser~~ | **Closed** by the ACL repair above |
| `BENO-COCKPIT-001` owner gate | Cannot retire the `Career tools` disclosure | Owner must confirm the write doors exist first; deleting first makes writes unreachable |
| 768px minimum (`DESIGN.md` §2.3) | Proposal, not ratified | Owner decision; do not "fix" scroll-to-reflow meanwhile |
| Concurrent workstreams on the tree | Cannot stage or commit without sweeping peer hunks | Explicit-path commits only, when authorized |

---

## Exact next actionable step

Open **R4.1** from the roadmap: drive `/preferences` Save at 1280 for each of `light`,
`dark` and `eva01`, and re-read `document.documentElement.dataset.theme` after each save. That
closes the one theme claim this session left as inference. Then R4.5 (Career Workspace narrow
sweep), because six dense workflows currently have 1280-only evidence.

---

## Handoff summary

- Sandbox gap closed; harness reproducible (built manifest + `php artisan serve` on a free port).
- 59 routes rendered and reconciled; non-rendering routes given precise dispositions.
- Four real defects fixed with before/after measurements; one pre-existing gate failure
  cleared and attributed honestly.
- Seven screens inspected and deliberately unchanged, with reasons.
- Gates: build, typecheck and lint green; 89 browser spec cases green across two runs.
- Everything unverified is listed above rather than implied.

**Sources:** live browser measurements at 1280/768/390/320, `git status`, `git diff`,
`npm run build`/`typecheck`/`lint:ui` output, Playwright spec output, HEAD `829ef0ac`.
Evidence artifacts: `.scratch-uma/lead/audit/` (`MATRIX.md`, `sweep.json`, `evidence.json`,
`focus-final.json`, `shots/`).

---

## R4 pass — 2026-11-23 (resumed session)

All five R4 items were executed. Measurements ran against an **isolated scratch database**
(`.scratch-uma/lead/r4/r4.sqlite`, migrated + seeded + `UraFinaleRunsSeeder` + `TrackerVeteransSeeder`)
served on `:8300`; the shared `database/database.sqlite` and the dev server on `:8000` were never used.

### Closed

| Item | Result |
|---|---|
| R4.1 Theme persistence | **Verified complete.** Three palettes driven through the real Preferences Save and reloaded; `html[data-theme]` followed each, the control stayed checked, the card surface moved with the palette, and the layout radio plus `failure_estimate` did not move. Restored to Follow-the-system. |
| R4.2 Veteran comparison | **Verified complete.** Seeded eight banked Veterans. Picker showed 8 unchecked boxes; ticking 7 then 8 produced `?veterans%5B0%5D=7&veterans%5B1%5D=8`; the matrix showed `2 careers side by side`, 4 columns, sticky property column, named focusable region, and a pager with 0 overflow at 768/390/320. |
| R4.4 Target inventory | **Verified complete.** 14 routes classified. Every 13-20px box is inside a measured 48-100px label; `AbsenceValue`'s 24px `N/A` is exactly SC 2.5.8's floor; Help's inline links qualify through spacing (161-252px clear). **Five real defects corrected.** |
| R4.5 Career Workspace narrow | **Verified complete.** Ten routes at 1280/768/390/320. **Three real defects corrected** (unnamed unfocusable horizontal scrollers); everything else measured coherent and left alone, with reasons recorded. |
| R4.3 Loading states | **Verified.** A held visit renders the shell `<progress aria-label="Page load progress">` and clears it; the catalog sets `aria-busy="true"` with a `Loading results…` status that resolves to the filtered result. |

### Defects corrected this pass

| File | Change | Measurement |
|---|---|---|
| `resources/js/pages/Career/RaceDecision.vue` | Race table's card gained `role="region"` + `tabindex="0"` | Was the scroller at 284/506 with no tab stop; now focusable, ringed 2px, ArrowRight 0->40 |
| `resources/js/pages/Career/RacePlanner.vue` | Each group card gained conditional `role`/`tabindex` (only when it renders a table) | Three scrollers 284/413-420 with no tab stop; now all three focusable and keyboard-scrollable |
| `resources/js/pages/Career/InheritanceEvent.vue` | Result-pick card gained `role="region"` + `tabindex="0"` | 284/309 with no tab stop; now focusable, ArrowRight 0->25 |
| `resources/js/pages/Career/EventDecision.vue` | Retry disclosure `min-h-11` | 279x16 -> 279x44 |
| `resources/js/pages/Career/Cockpit.vue` | Notes disclosure `min-h-11` | 661x20 -> 661x44 |
| `resources/js/pages/Dashboard.vue` | Card-note disclosure `min-h-11` | 279x16 -> 279x44 |
| `resources/js/pages/Legacy/Builder.vue` | Both disclosures `min-h-11` | 966x~20 / 956x~20 -> 44 each |
| `tests/browser/career-race-decision.spec.ts` | New case: region is named, focusable, ringed, keyboard-scrollable, document never pans | Passes in the real harness |

### R4.3 limitation, established by measurement rather than assumed

A 419 cannot be produced by a browser navigation on this build. Laravel 13's
`PreventRequestForgery::hasValidOrigin()` returns true for any request whose `Sec-Fetch-Site` is
`same-origin`, **before** `tokensMatch()` runs. Measured: a top-level POST carrying a deliberately
stale `_token` answered 302 (accepted); the same POST from a client with no session answered 419.
A 500 requires the app to be broken. The two documents were therefore rendered from the real Blade
views (`render-errors.php`, read-only) and opened as documents — the same markup the exception
handler emits, where only the status code differs.

### Gates (this pass, command output)

| Gate | Command | Result |
|---|---|---|
| Build | `npm run build` | `BUILD=0` (twice: after the region fixes, and after the disclosure fixes) |
| TypeScript | `npm run typecheck` | `TSC=0` |
| UI framework lint | `npm run lint:ui` | `LINT=0`; `arbitrary-value` **208** (baseline 209, last observed 208), `card-in-card` 0, `dark-utility` 0, `debt-increase` 0 |
| Browser specs | `preferences.spec.ts` + `career-race-decision.spec.ts` + `career-race-planner.spec.ts` + `career-inheritance-event.spec.ts` | **43 passed, 2 skipped** (6.0m) |
| Browser spec | new scroll-region case | **1 passed** |

Full Pest and full Playwright suites were **not** run (`AGENTS.md` §9 cost-aware escalation; this was a
frontend-only pass).

### Note on the shared worktree

`AdvisorRail.vue` and `AdvisorAnalysisPanel.vue` (both untracked peer work) were being edited at
02:45-02:49 while this pass ran; the bundle is therefore older than those two files. The new
regression case was run with `PLAYWRIGHT_FRESH_BUILD=1` on a session-private scratch DB, after
confirming every file this pass changed (02:32-02:35) was inside the 02:37 build. Nothing was
staged, committed, or pushed, and the peer's files were left untouched — including
`AdvisorAnalysisPanel.vue`, whose disclosure is 20px tall and would otherwise have been corrected.

---

## R5 pass — 2026-11-23 (integration verification)

Resumed against a newer tree than R4's (HEAD `3600741e`, two peer commits landed during R4).
All measurements on an isolated scratch DB (`.scratch-uma/lead/r5/r5.sqlite`) served on `:8310`;
`database/database.sqlite` and the `:8000` dev server were never used.

### The integration defect this pass existed to find

| Field | Value |
|---|---|
| Symptom | The AI Advisor panel's request never left the browser: `typeof window.axios === 'undefined'`, zero XHR fired, and the panel settled on its `unavailable` line ("The analysis could not be produced"). |
| Root cause | `resources/js/bootstrap.ts` installs `window.axios`, but nothing imported it. Its only importer was `resources/js/app.ts`, deleted with the Blade shell in `0ea8d43c`. `spa.ts` is the sole Vite entry and never imported it, so the module was dead code while `services/advisor/analysis.ts` documented it as the transport. |
| Fix | One line in `resources/js/spa.ts`: `import './bootstrap';`, with the history stated in the comment. |
| Evidence | Before: no XHR, `axiosType: undefined`, panel showed the unavailable line. After: `GET /training-runs/1/advisor-analysis` fires and the panel reaches `ready` ("Turn 48", attribution, explanation, `Sources (2)`). |

This was invisible to every earlier gate: TypeScript compiles the module, Vite builds it, and the panel's own error path is a legitimate state. Only a browser reaching `ready` exposed it.

### Cockpit spec collision (peer-caused, corrected in test scope)

`AdvisorAnalysisPanel.vue` (peer, untracked) adds an `<h3>AI advisor</h3>` beside the rail's existing
`<h2>Advisor</h2>`. Playwright's `name` option is a substring match, so `getByRole('heading', { name: 'Advisor' })`
resolved to **two** headings and failed strict mode in `career-cockpit.spec.ts` (lines 179, 388, 399).
Proven pre-existing: the `AI advisor` h3 rendered at 07:41, before this pass's 07:58 `spa.ts` fix.
The matcher was made `exact: true`; both previously failing cases now pass.

### Gates

| Gate | Command | Result |
|---|---|---|
| TypeScript | `npm run typecheck` | `TSC=0` |
| UI lint | `npm run lint:ui` | `LINT=0`; `arbitrary-value` **208** (baseline 209), `card-in-card` 0, `dark-utility` 0, `debt-increase` 0 |
| Build | `npm run build` | `BUILD=0`, manifest `11:48:15`, newer than every `resources/js` file (newest `11:36:01`) |
| G-18 contrast | `php artisan test --filter='browser contrast and token resolution'` | **4 passed, 439 assertions** |
| DesignTokensTest | `php artisan test tests/Feature/DesignTokensTest.php` | **18 passed, 501 assertions, 0 skips** |
| Focused specs | `career-race-decision` + `preferences` + `career-cockpit` | **60 passed, 2 skipped** in 64; the 2 failures were the heading collision, now **2 passed** on re-run |

### Evidence

- 32-cell responsive sweep (8 routes x 1280/768/390/320): one `h1` each, 0 document overflow, 0 visible card-in-card, 0 unnamed/unfocusable scrollers.
- Disclosures: all non-inline triggers 44px and toggling; `AbsenceValue`'s inline `N/A` deliberately 24px (SC 2.5.8 floor).
- Scroll regions: 5 regions across 3 screens, each named, focusable, 2px ringed, ArrowRight-scrolled.
- Themes: `#c3d9f2` / `#050a1b` / `#1e1435` page colours, three distinct palettes; EVA-01 keeps green `#72c068` plus its own risk tone.
- Files: `.scratch-uma/lead/r5/` (`regress.txt`, `disc.txt`, `themes.txt`, `adv2.txt`, `g18.log`, `dt.log`, `specs.log`, `cockpit2.log`, `shots/`).

### Still open

- **Advisor disclosure** (`AdvisorAnalysisPanel.vue`, peer, untracked): the `Sources (n)` `<summary>` measures **279x20 / 322x20** against the 44px convention. Handed off in the roadmap; not edited, because the file is another workstream's and was still being modified at 11:36 during this pass.
- **R4.3** stays partially verified for the reason R4 recorded (Laravel 13 accepts `Sec-Fetch-Site: same-origin` before comparing CSRF tokens).
