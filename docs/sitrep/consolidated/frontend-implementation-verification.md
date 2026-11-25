# Consolidated SITREP — Frontend Implementation & Verification (Workstream Group E)

**Generated:** consolidation of 5 source SITREPs  
**Scope:** Frontend slices, UI/UX handoff, completion, rendered verification, integration sign-off  
**Source files:** 5 reports spanning 2026-11-20 through 2026-11-23  
**Disposition:** Standalone workstream report; not a master SITREP; original sources archived in `docs/sitrep/archive/`

---

## 1. Source inventory

| # | Source SITREP | Date | SHA-256 | Role in consolidation |
|---|---|---|---|---|
| E1 | `sitrep-frontend-slices-2026-11-20.md` | 2026-11-20 | `bce77f5427b743391289708c692c91b69357e27d54ca9573e3724a4efcbaf97c` | Frontend slices execution |
| E2 | `sitrep-2026-11-21-frontend-uiux-session-handoff.md` | 2026-11-21 | `9ba35c17ebddf8930b4dc438317781bbd98df7ddbb5afb729d36314d6c4d73bc` | UI/UX session handoff |
| E3 | `sitrep-frontend-completion-2026-11-21.md` | 2026-11-21 | `911ef0585a93abde2430333a7b4e501b62e9372342971b7450eba8bef92eb003` | Frontend completion |
| E4 | `sitrep-frontend-rendered-verification-2026-11-22.md` | 2026-11-22 | `2d777de77693ebadf2eb7e344ffea9f0f57f3d72f97bd9a2b0b1aff4e80f5243` | Rendered verification, R4 pass, R5 pass |
| E5 | `sitrep-frontend-integration-signoff-2026-11-23.md` | 2026-11-23 | `5593a1a09102c5e65c480449ffe1e20c20e7f839c585c0b79a0f6b78ca3cd0ba` | Integration verification and sign-off |

---

## 2. Consolidated findings

### 2.1 Environment blocker and repair (E4)

**Blocker:** Sandbox write/spawn denial — `resources/js`, `public/build`, `storage/logs`, `storage/framework/views`, `bootstrap/cache` all refused writes; Laravel could not append `laravel.log`; `php -S` died on `Fatal Error Cannot create mutex (error 5)`; Node `spawnSync` → `spawn EPERM`; Playwright/Chromium died on `Access is denied`.

**Repair:** One scoped run on `resources/js` with `-AllowRoot` set to repo root. Two grants written (on `resources` and `resources/js`), both verified. Backups and rollback scripts under `.scratch-uma/lead/acl-repair/`.

**Post-repair probes:** all WRITE OK; spawn status 0 out 11; server running on `[http://127.0.0.1:8161]`.

### 2.2 Route reconciliation (E4)

**59 user-facing GET routes rendered** at 1280x900 against live server (built manifest), **0 failures**, exactly one `h1` on every route, **0 document-overflow routes**.

Registered routes correctly *not* standalone screens:
- `/training-runs/{run}` → 302 to `runs.cockpit` (retired 2026-10-09, F2)
- `/up` — framework health endpoint
- `/storage/{path}`, `/artwork/{kind}/{id}` — asset handlers
- `/api/v1/*` — JSON
- `/training-runs/{run}/export/{format}`, `/preferences/export` — download responses

Previous 34/62 coverage record and 66-entry screen inventory superseded by measured list.

### 2.3 Defects found and corrected (E4)

| # | Route/Component | Root cause | Fix | Before → After |
|---|---|---|---|---|
| 1 | `/legacy/compare` | `BaseCheckbox.modelValue` union `boolean \| string \| number` compiles to `{type:[Boolean,String,Number]}`; Vue runtime boolean cast rewrites empty-string prop to `true` | Reorder union to `string \| number \| boolean`; docstring explains order | `boxes: 39=true,37=true,35=true,34=true` → `boxes: 39=false,37=false,35=false,34=false` |
| 2 | `/training-runs/{run}/cockpit` | `NextDecision.vue` rendered nested `.card` inside `AdvisorRail.vue`'s `.card`; OBLIGATIONS label 0px under paragraph | Inner `div.card` → `div`; obligations `section` gained `mt-4` | `visibleNestedCards = 1, gapToPrevPx = 0` → `0, 16` |
| 3 | Skills/Deck/Legacy | `.savebar` `position:sticky; bottom:0`, 94px tall occludes focused controls | `html:has(.savebar) { scroll-padding-bottom: 6rem; }` in `app.css` | `/training-runs/36/skills` 6→0; `/training-runs/36/deck` 5→0; `/legacy/39` 15→1 |
| 4 | `/help` | Left column ended 1053px, right ran 2413px; six of eight answer cards in 315px rail | Move "Rules that are easier to look up" card to wide column (781px tall at 315px → 428px at 663px) | `left 1053 / right 2413` (delta 1360) → `left 1495 / right 1617` (delta 122) |
| 5 | `Snapshot/Setup.vue` | Pre-existing `TS7053` failing typecheck gate | One-line widening of error bag's key type (no behaviour change; pre-existing, another workstream's file) | Typecheck gate cleared |

### 2.4 Screens inspected, deliberately left alone (E4)

| Screen | Finding |
|---|---|
| `/` Dashboard | 8 cards, `g-side` 663/315; three layout modes via stored preference (canvas 1364/1152/768px); compact single-column stack is mode's purpose |
| `/training-runs/{run}/training` | At 320px discipline table in named focusable region (`role=region`, `tabindex=0`, client 252/scroll 334), zero document overflow |
| `/training-runs/{run}/scenario` | Two rows hold single `col-span-4` card on 12-column grid — grid arithmetic, not defect |
| `/training-runs/{run}/timeline` | Stat strip, record table, phase grouping — coherent |
| `/database/scenarios` | Four per-scenario panels, consistent table grammar |
| `/preferences` | Columns balance exactly, 1385/1385 |
| `/career/snapshot/setup` | Prior correction intact (`g-side-wide-second` gives 312/656, 992px form, three stat columns); not reimplemented |

### 2.5 Themes (E4/E5)

Applied via `html[data-theme]` (same attribute `app.blade.php` prints). Re-measured on Dashboard, Cockpit, Snapshot Setup, Compare. Computed colours differ per theme as expected. EVA-01 keeps violet primary **plus** secondary accents (green card-header rule, amber caution pills, green progress fills) — not monochrome wash.

**Theme colours (verbatim from E5 `themes.txt`):**

| Theme | Page | Ink | Primary | Green | Risk | Card |
|---|---|---|---|---|---|---|
| light | `#c3d9f2` | `#12304f` | `#2455c9` | `#5d8f03` | `#b22323` | rgb(228,241,253) |
| dark | `#050a1b` | `#eaf2ff` | `#164bff` | `#83b503` | `#ff6f3b` | rgb(18,34,79) |
| eva01 | `#1e1435` | `#f0e9dc` | `#715591` | `#72c068` | `#fc8d93` | rgb(52,40,91) |

### 2.6 R4 pass — 2026-11-23 (E4)

All five R4 items executed against isolated scratch DB (`.scratch-uma/lead/r4/r4.sqlite`) on `:8300`; shared `database/database.sqlite` and `:8000` dev server never used.

| Item | Result |
|---|---|
| R4.1 Theme persistence | **Verified complete.** Three palettes via real Preferences Save + reload; `html[data-theme]` followed each; control stayed checked; card surface moved with palette; layout radio + `failure_estimate` did not move. Restored to Follow-the-system. |
| R4.2 Veteran comparison | **Verified complete.** Seeded 8 banked Veterans; picker showed 8 unchecked; ticking 7 then 8 produced `?veterans%5B0%5D=7&veterans%5B1%5D=8`; matrix showed 2 careers side by side, 4 columns, sticky property column, named focusable region, pager 0 overflow at 768/390/320. |
| R4.4 Target inventory | **Verified complete.** 14 routes classified; every 13–20px box inside measured 48–100px label; `AbsenceValue`'s 24px `N/A` exactly SC 2.5.8 floor; Help inline links qualify via spacing (161–252px clear). **Five real defects corrected.** |
| R4.5 Career Workspace narrow | **Verified complete.** Ten routes at 1280/768/390/320. **Three real defects corrected** (unnamed unfocusable horizontal scrollers); everything else measured coherent, left alone with reasons. |
| R4.3 Loading states | **Verified.** Held visit renders shell `<progress aria-label="Page load progress">`; catalog sets `aria-busy="true"` with `Loading results…` resolving to filtered result. |

**R4.3 limitation:** 419 cannot be produced by browser navigation — Laravel 13's `PreventRequestForgery::hasValidOrigin()` returns true for `Sec-Fetch-Site: same-origin` before `tokensMatch()`. Measured: stale `_token` POST → 302; no-session POST → 419. 419/500 documents rendered from real Blade views as documents — same markup, only status code differs.

**Defects corrected in R4 pass:**

| File | Change | Measurement |
|---|---|---|
| `RaceDecision.vue` | Race table card gained `role="region"` + `tabindex="0"` | Was scroller 284/506 no tab stop; now focusable, ringed 2px, ArrowRight 0→40 |
| `RacePlanner.vue` | Each group card gained conditional `role`/`tabindex` | Three scrollers 284/413–420 no tab stop; now all focusable, keyboard-scrollable |
| `InheritanceEvent.vue` | Result-pick card gained `role="region"` + `tabindex="0"` | 284/309 no tab stop; now focusable, ArrowRight 0→25 |
| `EventDecision.vue` | Retry disclosure `min-h-11` | 279×16 → 279×44 |
| `Cockpit.vue` | Notes disclosure `min-h-11` | 661×20 → 661×44 |
| `Dashboard.vue` | Card-note disclosure `min-h-11` | 279×16 → 279×44 |
| `Legacy/Builder.vue` | Both disclosures `min-h-11` | 966×~20 / 956×~20 → 44 each |
| `career-race-decision.spec.ts` | New case: region named, focusable, ringed, keyboard-scrollable, document never pans | Passes in real harness |

**R4 gates:** build `BUILD=0` (twice); typecheck `TSC=0`; lint `LINT=0`, `arbitrary-value` 208 (baseline 209); browser specs 43 passed / 2 skipped + new scroll-region case 1 passed.

### 2.7 R5 pass — 2026-11-23 (E4/E5)

Resumed against newer tree than R4 (HEAD `3600741e`, two peer commits landed during R4). Isolated scratch DB (`.scratch-uma/lead/r5/r5.sqlite`) on `:8310`.

**Integration defect found and fixed:**
- **Symptom:** AI Advisor panel request never left browser; `typeof window.axios === 'undefined'`; zero XHR fired; panel settled on `unavailable` line.
- **Root cause:** `resources/js/bootstrap.ts` installs `window.axios`, but nothing imported it. Only importer was `resources/js/app.ts`, deleted with Blade shell in `0ea8d43c`. `spa.ts` (sole Vite entry) never imported it → module dead code while `services/advisor/analysis.ts` documented it as transport.
- **Fix:** One line in `resources/js/spa.ts`: `import './bootstrap';` with removal history in comment.
- **Evidence:** Before — no XHR, `axiosType: undefined`, panel showed unavailable line. After — `GET /training-runs/1/advisor-analysis` fires; panel reaches `ready` ("Turn 48", attribution, explanation, `Sources (2)`).

**Cockpit spec collision (peer-caused, corrected in test scope):**
- `AdvisorAnalysisPanel.vue` (peer, untracked) adds `<h3>AI advisor</h3>` beside rail's `<h2>Advisor</h2>`. Playwright `name` option is substring match → `getByRole('heading', { name: 'Advisor' })` resolved to 2 elements, failed strict mode in `career-cockpit.spec.ts` (lines 179, 388, 399). Proven pre-existing (h3 rendered 07:41, before this pass's 07:58 `spa.ts` fix). Matcher made `exact: true`; both failing cases now pass.

**R5 gates:**
| Gate | Command | Result |
|---|---|---|
| TypeScript | `npm run typecheck` | `TSC=0` |
| UI lint | `npm run lint:ui` | `LINT=0`; `arbitrary-value` 208 vs baseline 209; `card-in-card` 0; `dark-utility` 0; `debt-increase` 0 |
| Build | `npm run build` | `BUILD=0`, manifest `11:48:15` newer than every `resources/js` file (newest `11:36:01`) |
| G-18 contrast | `php artisan test --filter='browser contrast and token resolution'` | 4 passed / 439 assertions |
| DesignTokensTest | `php artisan test tests/Feature/DesignTokensTest.php` | 18 passed / 501 assertions / 0 skips |
| Focused specs | `career-race-decision` + `preferences` + `career-cockpit` | 60 passed / 2 skipped in 64; 2 failures (heading collision) → 2 passed on re-run |

**Evidence:** 32-cell responsive sweep (8 routes × 1280/768/390/320): one `h1` each, 0 document overflow, 0 visible card-in-card, 0 unnamed/unfocusable scrollers. Disclosures: all non-inline triggers 44px and toggling; `AbsenceValue` inline `N/A` deliberately 24px (SC 2.5.8 floor). Scroll regions: 5 regions across 3 screens, each named, focusable, 2px-ringed, ArrowRight-scrolled. Themes: 3 distinct palettes; EVA-01 keeps green `#72c068` plus risk tone.

### 2.8 Files changed by frontend line (E4/E5)

| File | +/- | Change | Ownership |
|---|---|---|---|
| `resources/js/spa.ts` | +7 / −0 | `import './bootstrap';` + removal-history comment | Frontend line (R5) |
| `tests/browser/career-cockpit.spec.ts` | +3 / −3 | Three `Advisor` matchers → `exact: true` | Frontend line, peer file |
| `tests/browser/career-race-decision.spec.ts` | +40 / −0 | R4 scroll-region regression case | Frontend line (R4) |
| `resources/js/pages/Career/RaceDecision.vue` | +76 / −8 | R4 region role/name/tab stop + rationale | Frontend line (R4) |
| `resources/js/pages/Career/RacePlanner.vue` | +9 / −1 | R4 conditional region on group cards | Frontend line (R4) |
| `resources/js/pages/Career/InheritanceEvent.vue` | +34 / −2 | R4 region + comment | Frontend line (R4) |
| `resources/js/pages/Career/EventDecision.vue` | +1 / −1 | R4 `min-h-11` on retry disclosure | Frontend line (R4) |
| `resources/js/pages/Career/Cockpit.vue` | +87 / −16 | One line mine (R4 `min-h-11` on Notes); rest peer BENO/N3.2 | Mixed |
| `resources/js/pages/Dashboard.vue` | +44 / −7 | One line mine (R4 `min-h-11` on card note); rest peer BENO-DASH-001 | Mixed |
| `resources/js/pages/Legacy/Builder.vue` | +2 / −2 | R4 `min-h-11` on two disclosures | Frontend line (R4) |
| `resources/js/components/Form/BaseCheckbox.vue` | +12 / −1 | Prop union order + docstring | Frontend line (E4) |
| `resources/js/components/career/NextDecision.vue` | +11 / −3 | Nested card removed; obligations spacing | Frontend line (E4) |
| `resources/js/pages/Help.vue` | +93 / −12 | Rules card moved to wide column | Frontend line (E4) |
| `resources/css/app.css` | +17 / −0 | `html:has(.savebar)` scroll-padding | Frontend line (E4) |
| `resources/js/pages/Career/Snapshot/Setup.vue` | +6 / −3 | Typecheck widening only (rest of 493-line diff is peer's) | Frontend line, pre-existing |

**Nothing committed, staged, or pushed.** Working tree held 166 entries at E5 report time, overwhelmingly peer work.

### 2.9 Outstanding exceptions (E5)

| Item | Detail |
|---|---|
| Advisor disclosure (`AdvisorAnalysisPanel.vue`) | Peer-owned, untracked. `<summary>` measures **279×20** @1280, **322×20** @390 vs 44px convention. Expected markup: `<summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">`. Not fixed here — file is another workstream's, still being modified at 11:36 during pass. **Handed off to Advisor AI workstream.** |
| `BENO-COCKPIT-001` — retire `Career tools` disclosure | Owner must confirm write doors exist first. |
| 768px minimum (`DESIGN.md` §2.3) | Proposal, not ratified. |
| `docs/roadmap/INDEX.md` registration | Live-roadmap convention needs owner decision. Roadmap remains **unregistered**. |
| R4.3 error flow | Retained as partially verified (419/500 cannot be produced by browser navigation). |

### 2.10 Build-staleness caveat (E5)

E5 verified `11:48:15` bundle containing every file it changed. Afterwards peer review workstream saved `resources/js/pages/Review/Index.vue` at `12:24:09` and rebuilt bundle at `12:27:06`. **Every measurement in section 6 belongs to the `11:48:15` bundle, not the current `12:27:06` one.** Review screen neither built nor measured by this pass.

---

## 3. Cross-stream notes relevant to consolidation

- The Advisor disclosure 20px exception is **handed off, not fixed** — closed by Group F's frontend agent (see `sitrep-advisor-ai-frontend-2026-11-23.md`) via one-line `min-h-11` fix, measured to 44px at all widths.
- The `window.axios` transport fix (one import in `spa.ts`) enabled the Advisor panel to reach `ready` — cross-workstream dependency with Group F.
- No application source, frontend component, backend route, migration, config, dependency, or test was modified outside the documented file changes above.
- Nothing was committed, staged, or pushed by this workstream.