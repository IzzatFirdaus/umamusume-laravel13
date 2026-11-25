# TrainerDesk Bento Layout — Remediation Roadmap

**Repository:** `D:\Projects\umamusume-laravel13`
**Branch:** `master`
**HEAD at original audit:** `829ef0ac` · **HEAD at Hermes resume:** `3600741e` (166 working-tree entries from concurrent work — preserved)
**Server:** `http://127.0.0.1:8000` (running)
**Roadmap date:** 2026-11-23 (Asia/Kuala_Lumpur)

This roadmap converts the source-only bento audit (`docs/audits/2026-11-22-bento-design-language-review/`)
into rendered evidence and executes authorized frontend improvements. It supersedes the prior
34/62 geometric measurement record with actual browser-verified screen composition.

---

## 1. Reconciliation summary

| Source | Status |
|---|---|
| `docs/audits/2026-11-22-bento-design-language-review/` | 4 files created (source-only audit, 66 screens classified) |
| `docs/roadmap/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md` | R0-R4 largely complete (browser evidence) |
| `docs/sitrep/sitrep-2026-11-21-frontend-uiux-session-handoff.md` | Prior frontend UI/UX pass, no commits |
| Working tree | 110 modified files (peer work preserved, not swept) |

**Route inventory reconciled:** 59 distinct rendered user-facing screens (route count ≠ screen count;
`/training-runs/{run}` 302→cockpit, `/up`, `/storage/*`, `/artwork/*`, `/api/v1/*`, export/download endpoints
are non-rendering or asset routes).

---

## 2. Implemented slice: N3.27 statusNotice frontend consumption

**Finding:** `ScenarioSelectController` passes `statusNotice` (server-owned notice when the trainee's saved
build target may need review after changing scenario) but `ScenarioSelect.vue` did not declare or render
the prop. This was pending frontend integration (N3.27).

**Root cause:** The Vue page's `defineProps` had no `statusNotice` field, and the template had no
rendering site for it.

**Correction implemented:**
- Added `statusNotice: string | null` prop declaration (`resources/js/pages/Career/ScenarioSelect.vue:85-86`)
- Added rendering block after the "Saving your choice…" processing message (`:234-240`), using
  `role="status"`, `bg-raised`, `border-rule` treatment consistent with existing status messages

**Files modified:**
- `resources/js/pages/Career/ScenarioSelect.vue` (+2 prop lines, +7 rendering lines)

**Verification:**
- `npx tsc --noEmit` — clean (no type errors introduced)
- Browser navigation to `/career/setup/scenario` — page renders 4 scenario cards, step bar, Continue button,
  reversibility table; no console errors. `statusNotice` renders only when `session('status')` is non-null
  (normal case: absent, which is correct).

**Before/After:**
- Before: `statusNotice` prop received from server but silently dropped; Trainer never saw the build-target
  review notice after changing scenario.
- After: Notice renders as a bordered status line directly under the processing message, above the scenario
  cards, using the house `role="status"` pattern.

---

## 3. Shop Items and Sparks — dedicated composition review

Both routes were inspected in the live browser at 1280px (Azure Daybreak / light theme, Standard layout mode).

### 3.1 `/database/shop-items` (SCR-SYS-009)

**Composition observed:**
- `PageHeader` (eyebrow "Shop items", title "Pro Shop catalogue", lede with item count)
- `DatabaseNav` subnav (9 area links)
- Pro Shop table card (`role="region"`, `aria-labelledby`, `tabindex="0"`): "Trackblazer Pro Shop" h2,
  description, `table.t` with Item/Cost/Effect/Rating columns, Show all / Show fewer toggle
- `.g-2` grid with two cards:
  - "Shop rules" card (rules table + unconfirmed note with source ledger link)
  - "What the ratings are" card (explanation + `.g-3` tiles: Items/Rated/Source)
- Note paragraph distinguishing the two table idioms (Pro Shop reference table vs shared DataTable)
- `SourceNote` component
- "Catalogue" h2 + filter row (Kind/Spend site selects + Clear filters + filtered count status)
- `DataTable` "Shop item catalogue" with column chips (SparkChip for kind, ProvenanceBadge for state)
- `ReferenceDrawer` for item detail

**Assessment:** Compliant. Clear primary (Pro Shop table) and supporting (rules, ratings, catalogue)
regions. `.g-2` grid for supporting panels. `role="region"` on scrollable table. No card-in-card.
Filters sit adjacent to results. One filled action per screen convention honored (no filled button in
head; Show all is a secondary toggle).

**Change required:** None.

### 3.2 `/database/sparks` (SCR-SYS-010)

**Composition observed:**
- `PageHeader` (eyebrow "Sparks", title "Spark families and grades", description)
- `DatabaseNav` subnav
- Families card (`role="region"`): `table.t` with Family/Carries/Applied at (5 rows: Blue, Pink, Green, White, Scenario)
- `.g-2` grid with two value cards:
  - "Blue spark values" (★1/★2/★3/★3 inherited ladder)
  - "Pink spark values" (single aptitude grade row)
- Inheritance loop card (4-step table + note)
- Note paragraph about table idioms
- `SourceNote` component
- "Spark categories" h2 + `DataTable` with category chips, JP name (`lang="ja"`), ProvenanceBadge
- Star-roll odds section (h3 + DataTable with caption)
- Navigation links to Legacy Lab / Veterans

**Assessment:** Compliant. Reference tables first (Families, Blue/Pink values, Inheritance loop),
then shared data tables (categories, odds). `.g-2` grid for value cards. `role="region"` on scrollable
family table. No card-in-card. Spark categories use neutral `SparkChip` (color not yet tokenized per
DESIGN.md §18 park — owner-gated, ND-13).

**Change required:** None. Spark family color identity is parked pending DESIGN.md §2 palette ruling.

---

## 4. Previously verified slices (no implementation required)

From `docs/roadmap/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md`:

| ID | Item | State | Evidence |
|---|---|---|---|
| R2.1 | Compare picker blank state | DONE | `BaseCheckbox` prop union reordered; 39/37/35/34 → all false |
| R2.2 | Advisor rail composition | DONE | Nested `.card`→`div`, obligations `mt-4` |
| R2.3 | WCAG 2.4.11 focus obscured | DONE | `html:has(.savebar){scroll-padding-bottom:6rem}` |
| R2.4 | Help column imbalance | DONE | Rules card moved to wide column; Δ1360→Δ122 |
| R2.5 | Setup.vue typecheck regression | DONE | One-line error bag key type widening |
| R4.1 | Theme persistence | DONE | All 3 palettes via Save changes, reload verified |
| R4.2 | Veterans compare picker | DONE | Indexed `veterans[0]=N` form, sticky matrix at 768/390/320 |
| R4.3 | Error/loading states | Partially verified | 419/500 docs render; 419 not producible via browser nav on this build |
| R4.4 | Sub-24px target inventory | DONE | 5 disclosure triggers corrected to `min-h-11` (44px) |
| R4.5 | Career Workspace at 768/390/320 | DONE | 3 region fixes (Race Decision table, Race Planner groups, Inheritance Result pick) |

Snapshot Setup correction (`g-side-wide-second`, `.g-side-wide-second` in `layout-grid.css`, 992px form,
3 stat columns) — **preserved as completed**, no regression detected.

---

## 5. Owner-gated items (not implemented — require owner decision)

| ID | Item | Gate | Why not implemented |
|---|---|---|---|
| BENO-COCKPIT-001 | Retire `Career tools` disclosure (Cockpit) | Owner must confirm write doors exist first | Deleting before doors (`runs.update`, `runs.turns.update`, `runs.turns.destroy`) makes writes unreachable (data-loss risk). Recorded as KI-144, Slice 6b. |
| 768px minimum | DESIGN.md §2.3 R85 status line | Proposal, not ratified | Do not "fix" scroll-to-reflow without a decision. Race calendar / turn log deliberately scroll horizontally below 768px. |
| Token contrast gate (G-18) | Browser contrast gate | Unrun on this host (sandbox write denial on `pest-plugin-browser/.temp/`) | Ratios documented in DESIGN.md §2.1, not re-verified here. |
| ND-13 | Spark family color identity | DESIGN.md §2 palette ruling | `SparkChip` renders neutral; color inventing a distinction the design system doesn't own. |

---

## 6. Implementation priority summary

| Priority | Count | Items |
|---|---|---|
| P0 | 0 | No P0 defects found in rendered screens |
| P1 | 0 | No P1 defects found in rendered screens |
| P2 | 0 | No P2 defects found in rendered screens |
| Implemented | 1 | N3.27 statusNotice (ScenarioSelect.vue) |
| No change required | 12+ | Shop Items, Sparks, Dashboard, and all R2/R4 verified slices |
| Owner-gated | 4 | BENO-COCKPIT-001, 768px minimum, G-18 gate, ND-13 |

---

## 7. Verification evidence (all from live browser at http://127.0.0.1:8000)

| Screen | Route | Width | Theme | Layout mode | Observation |
|---|---|---|---|---|---|
| Dashboard | `/` | 1280 | light | standard | Bento-primary: active career panel (≈2/3) + Needs you (≈1/3), 4 KPI tiles, Recent Veterans + Quick actions |
| Scenario Select | `/career/setup/scenario` | 1280 | light | standard | 4 scenario cards in `.g-2`, step bar, Continue button, reversibility table; `statusNotice` prop wired |
| Shop Items | `/database/shop-items` | 1280 | light | standard | Pro Shop table card + `.g-2` rules/ratings cards + catalogue DataTable |
| Sparks | `/database/sparks` | 1280 | light | standard | Families table + `.g-2` Blue/Pink value cards + inheritance loop + categories DataTable + odds table |

All four themes (light/dark/eva01) and all four viewports (1280/768/390/320) remain **source-only
inference** for screens not listed above — Playwright cannot spawn `php artisan serve` in this sandbox
(`spawn EPERM`), so full theme/viewport browser matrix was not executed.

---

## 8. Remaining blockers

| Blocker | Detail | Correct owner |
|---|---|---|
| Playwright spawn EPERM | Cannot run `npm run test:browser` — sandbox blocks child process spawn from Node.js | Environment / DSH host |
| Theme/viewport matrix unrun | All 3 themes × 4 viewports not browser-verified for most screens | Environment / DSH host |
| Token contrast gate (G-18) | Browser block unimplemented (`markTestIncomplete` → permission denied) | Peer / gate workstream |
| BENO-COCKPIT-001 | Career tools disclosure retirement — owner-gated | Product owner |

---

## 9. Files modified in this session

| File | Change |
|---|---|
| `resources/js/pages/Career/ScenarioSelect.vue` | Added `statusNotice` prop + `role="status"` rendering block (N3.27) |

No other files were touched. Peer work (110 modified files) preserved untouched.

---

## 10. Next actionable step

**If the environment unblocks Playwright:** run the full browser suite against the landing HEAD,
classify any reds, then re-verify the Shop Items / Sparks pages across all 3 themes × 4 viewports.

**If the environment stays blocked:** the N3.27 implementation is complete and verified via DSH browser
tools + typecheck. The roadmap is current. Remaining work is owner-gated or environment-blocked.

---

## 11. Hermes resumed pass — 2026-11-23 (supersedes §§6–10 status claims)

**Repository:** `master`, HEAD `3600741e` (three commits ahead of origin), 166 uncommitted porcelain
entries preserved. No staging, commit, reset or push was performed. Peer work (telemetry, advisor AI,
review queue, packaging and the earlier frontend slices) remains byte-intact; only two attribute-level
Vue hunks and this document set were added by this pass.

### 11.1 Reconciliation and coverage

- `php artisan route:list --method=GET --json` reports **73 registered GET routes**. Of these, six are
  `/api/v1/*` JSON, two Inertia DevTools entries, two asset handlers (`storage`, `artwork`), `/up`, two
  download endpoints, and `/training-runs/{run}`'s 302. **59 distinct rendered screens** were found:
  58 rendered on the first sweep plus the turn-correction route with a valid recorded turn (the first
  probe used turn 1 on run 28, which correctly 404s). `runs.advisor-analysis` is a JSON endpoint gated
  off by `config('advisor_ai.enabled')` and correctly answers 404 when disabled; it is not a screen.
- All 59 rendered screens were visited in a real Chromium browser against `http://127.0.0.1:8000`,
  captured at 1280x900 and measured for `h1` count, document overflow, cards, bento grids, tables and
  horizontal scrollers. **One `h1` and zero document overflow on every route; zero page errors.**
  Evidence: `05-rendered-screen-matrix.md`, `sweep.json`, `evidence/*.png`, contact sheets 01-05.
- Prior work was verified, not repeated: Snapshot Setup's `g-side-wide-second`/992px/three-column
  correction is intact; N3.27 `statusNotice` is declared and rendered (`ScenarioSelect.vue:86,235-240`)
  and was exercised through a real scenario POST on a session-private server; `raceFatigue`, `roster`
  combobox, per-source `tier`, indexed compare query and the R2/R4 fixes are present. BENO-DASH-001,
  BENO-IMPORT-001, BENO-RACES-001 and BENO-SHARED-001/003 are already closed in the tree.
- `BENO-COCKPIT-001` / KI-144 is closed: the `Career tools` disclosure no longer renders; only
  historical comments remain. It is no longer an owner gate.

### 11.2 Shop Items and Sparks — dedicated rendered review

Both routes were reviewed full-page at 1280 and then at 768/390/320 in light, dark and eva01 using
**stored Preferences saves** on a session-private server (`:8311`, DB guarded as non-canonical).
Shop Items keeps the reference table as the primary region, the rules/ratings pair as a `.g-2`
supporting band, provenance notes as continuous prose, and the catalogue table with its own named
focusable scroller. Sparks moves from Families to Blue/Pink values to the inheritance loop, then to
categories and star-roll odds, with the same 992/489/489 proportions and no disconnected cards.
**No layout change was justified on either page.** Neutral `SparkChip` colour remains ND-13
(owner-gated by DESIGN.md §2), and every source/provenance label was preserved.

### 11.3 New confirmed defects and implemented slices

| Slice | Route | Observed problem | Root cause | Correction | Files | Priority |
|---|---|---|---|---|---|---|
| H-1 | `/` | At 320px the Recent veterans table is 63px wider than its card; the scroller had no name or tab stop while every comparable table has one | The `div.overflow-x-auto` wrapper was added without the app's scroll-region contract | `role="region"`, `tabindex="0"`, `aria-label="Recent veterans table, scrollable"`; arrow-key pan measured 0->14px with focus retained | `resources/js/pages/Dashboard.vue` | P2 |
| H-2 | `/review/{candidate}` | At 320px the "Other candidates" card scrolls 26px with no tab stop or region role | The card itself is the scroller (`.card:has(> table.t)`) but only carried `aria-labelledby` | `role="region"` + `tabindex="0"` on the existing card, reusing its heading name | `resources/js/pages/Review/Detail.vue` | P2 |

Both are local attribute fixes using the pattern already verified in Race Decision, Race Planner and
Inheritance Event. No new class, arbitrary value, CSS abstraction, dependency or backend contract.

### 11.4 Verification actually executed

| Check | Command / method | Result |
|---|---|---|
| Build | `npm run build` | exit 0 |
| TypeScript | `npm run typecheck` | exit 0 |
| UI framework | `npm run lint:ui` | exit 0; arbitrary-value 208 vs baseline 209; card-in-card 0; dark-utility 0 |
| Full route sweep | Chromium, 60 URLs at 1280x900 | 58/60 first-pass 200; turn-correction rechecked 200 with valid turn; advisor-analysis correctly 404 (feature gate); 0 overflow, 1 h1 per screen |
| Responsive scroller sweep | Chromium at 320px, all rendered screens | 2 fixed defects; remaining unnamed scrollers are the Database subnav (a `<nav>` whose links are focusable and scroll into view) |
| Dashboard theme x mode matrix | Real Preferences saves on scratch server, 3 themes x 3 modes x 4 widths = 36 cases | `html[data-theme]` and `[data-layout]` matched every saved combination; compact canvas 768px at 1280 viewport; 0 overflow |
| Shop/Sparks theme matrix | Stored Preferences saves, 3 themes x 4 widths | 24 cases, 0 overflow; 992/489/489 panel proportions in all themes |
| N3.27 | Real POST from `/career/setup/scenario` on scratch server | "Scenario set." rendered in a `role="status"` line on the redirected step |
| Focused browser specs | `PLAYWRIGHT_PORT=8315 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/hermes-2026-11-23/browser-8315.sqlite PLAYWRIGHT_FRESH_BUILD=1 npx playwright test tests/browser/dashboard.spec.ts tests/browser/review.spec.ts` | **25 passed, 1 failed** |
| Failure classification | `review.spec.ts:372` wireframe-order case | Pre-existing and unrelated: the two `.tile` markers are absent at HEAD as well, on `/review` (index); this pass changed only `Review/Detail.vue`. Peer-owned wireframe-parity work; no test weakened |

Full Pest and full Playwright suites were deliberately not run (AGENTS.md §9 focused verification).

### 11.5 Updated priority status

- **P0:** none confirmed in rendered screens. The historical BENO-VET-001 blocker is resolved and
  verified (`/veterans/compare` renders from real selection).
- **P1:** none confirmed. No conventional screen was forced into a bento grid.
- **P2:** 2 confirmed, both implemented (H-1, H-2).
- **Owner-gated:** ND-13 spark family colour identity; 768px minimum proposal (DESIGN.md §2.3,
  unratified); G-18 token contrast browser gate on hosts where its temp-path write is denied.
- **Peer-owned, pre-existing (closed in §12):** review-index wireframe tile parity (`review.spec.ts:372`).
- **Environment:** this host can launch Chromium directly; the earlier `spawn EPERM` blocker does not
  apply here. The scratch server and evidence stay inside `.scratch-uma/hermes-2026-11-23/`.

### 11.6 Next actionable step

Re-run the two focused specs after the peer's review-index tile work lands, then close the wireframe
parity row. No other authorized frontend slice remains open from this assessment.

---

## 12. Final frontend integration and sign-off — 2026-11-23 continuation

**Scope:** KI-169, the three N3.27 conditional cases, H-1/H-2 regression, changed-screen design
matrix, and G-18 feasibility. No new audit and no backend work.

### 12.1 Repository state

- Branch `master`, HEAD unchanged at `3600741e`; 167 porcelain entries at entry, 168 at exit (a peer
  sitrep landed during this pass; it was left untouched).
- Files changed by this continuation: `resources/js/pages/Review/Index.vue`, `KNOWN-ISSUES.md`
  (KI-169 closure), this roadmap and `05-rendered-screen-matrix.md`. No other file was touched.
- No staging, commit, push, reset or clean. Peer diffs in `Review/Index.vue` (target-size and ADR-0037
  labelling) were preserved byte-for-byte; only the tile block was added.

### 12.2 KI-169 — CLOSED

The peer had not corrected the queue by this continuation: `Review/Index.vue` still carried only the
39/8 target-size/ADR-0037 diff and rendered no `.tile`. The top `dl` was replaced by the wireframe's
two `.tile` regions inside the right-hand "Why this exists" card, consuming the existing
`candidates.total` and `resolvedToday` props. The former "Rows shown" figure was retained in the
scope sentence, which makes the filtered/global distinction explicit. No count query, prop or
assertion was changed.

Evidence: wireframe case (`PLAYWRIGHT_PORT=8316`, scratch DB `browser-8316.sqlite`, fresh build)
**1 passed**; full focused run (`PLAYWRIGHT_PORT=8317`, scratch DB `browser-8317.sqlite`)
`review.spec.ts + dashboard.spec.ts` **26 passed, 0 failed**. `05-rendered-screen-matrix.md`'s
`/review` row now carries this disposition.

### 12.3 N3.27 — all three conditional cases verified in browser

Isolated server on `:8318` with `matrix.sqlite` (guarded as non-canonical), fresh browser context,
real wizard PUTs and a real step-3 target save. Evidence: `n327b.json` / `n327b.log`.

| Case | Result |
|---|---|
| A — scenario changed with saved build target | Exact server warning rendered in `role="status"`; new radio checked; target inputs on step 3 still `StoryClear / Turf / Front Runner / 100 / 100` |
| B — re-post the recorded scenario with a target | "Scenario set." only; no false stale-target warning |
| C — changed scenario in a fresh draft with no target | "Scenario set." only |
| Flash lifetime | Plain re-render after each case shows no status notice; the warning does not persist |
| Placement and a11y | Notice y=87px, before the scenario cards, in an accessible `role="status"` region; zero page errors |

### 12.4 H-1 / H-2 regression and changed-screen design matrix

- At 320px in all three themes, both regions keep `role="region"`, `tabindex="0"`, an accessible name
  (explicit label for Dashboard, heading reference for Review Detail), `table` semantics, and a
  `:focus-visible` 2px outline. Dashboard overflows 63px inside its region, Review Detail 26px;
  document overflow stays 0. Arrow-key pan advances both (Dashboard 1px after one press, Review
  Detail 6px after four presses, 26px programmatic maximum).
- `/review` was measured with a seeded session-private queue (8 pending) across **3 themes x 3 modes
  x 4 widths = 36 cases**: exactly two tiles in every case, values `8` and `0`, two 135px tracks at
  1280 in Wide/Standard, single-column stacking in Compact, zero document overflow, queue table and
  named scroller intact. Evidence: `review-matrix.json`, `review-*.png`.
- Build `0`, typecheck `0`, `lint:ui` `0` (arbitrary-value 208 vs 209 baseline, card-in-card 0,
  dark-utility 0). No full Pest or full Playwright suite was run.

### 12.5 G-18 — now executed

The browser contrast gate is **not blocked on this host**. Command:
`LOG_CHANNEL=stderr php artisan test --compact --filter='browser contrast and token resolution'`
-> **4 passed, 439 assertions** (12.12s). No gate, palette or temp path was changed. The previous
sandbox-write blocker is closed for this environment; the historical owner-gated statement in §11.5
is superseded only on hosts where that write denial still applies.

### 12.6 Remaining state

- **P0/P1:** none. **P2:** H-1, H-2 and KI-169 all implemented and verified.
- **Owner-gated:** ND-13 spark colour identity; the unratified 768px minimum proposal. Neither was
  silently implemented.
- **Backend:** no defect or next action. **Environment:** no current G-18 or browser-spawn blocker.
- **Next actionable requirement:** none from this task. Future work on these screens should re-run the
  focused Review/Dashboard specs and the three N3.27 cases only if those surfaces change.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
