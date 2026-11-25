# Frontend Rendered Verification Roadmap — 2026-11-22

**Status:** Live roadmap for the rendered frontend verification line.
**Predecessor:** `docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` (owns slices N0.1-N6.x).
**Session report:** `docs/sitrep/sitrep-frontend-rendered-verification-2026-11-22.md`
**Base commit:** `829ef0acf8f7b36079ca2b3374c9fdac7ef76087` (`master`, clean index)

This roadmap exists because the 2026-11-22 bento audit
(`docs/audits/2026-11-22-bento-design-language-review/`) was **source-only**: it recorded
"No live browser session was available", classified every theme/viewport cell `Source-only`,
and could not distinguish a screen that renders from a screen that renders *well*. Its
screen classifications are inventory, not visual proof. This line converts that inventory
into rendered evidence and closes the defects the rendering exposes.

---

## Governing rule for this line

A screen is not verified because it renders, has one `h1`, or fills its container. It is
verified when a browser measured it and the measurement was compared against what the
screen's primary task needs. Conversely, a conventional or deliberately narrow composition
is not a defect: **Compact mode on a wide monitor is a purpose, not wasted space.**

Authority is unchanged: `AGENTS.md` §2 precedence, `DESIGN.md` for the visual system,
`SCREEN_SPEC.md` for screen behaviour, accepted ADRs binding.

---

## Phase R0 — Harness (DONE 2026-11-22)

| ID | Item | State |
|---|---|---|
| R0.1 | Repair the DSH Windows sandbox gap that blocked every write, `php -S`, and child-process spawn. One ACL repair on `resources/js`, scoped to the repo root; verified on five directories. | Done |
| R0.2 | Stand up a measurement harness against the live server (built manifest, `php artisan serve` on :8000), reporting `h1` count, canvas width, card inventory, grid tracks, table overflow containment, document overflow, and sub-24px targets. | Done |

**Do not** re-run the ACL repair. It is verified; its rollback scripts are retained under
`.scratch-uma/lead/acl-repair/`.

---

## Phase R1 — Route reconciliation (DONE 2026-11-22)

| ID | Item | Evidence | State |
|---|---|---|---|
| R1.1 | Map the registered GET registry to **distinct rendered screens**, not to route count. | 59 user-facing GET routes rendered; 0 HTTP failures; exactly one `h1` on every one; 0 document-overflow routes at 1280. | Done |
| R1.2 | Classify the non-rendering routes instead of counting them as coverage gaps. | `/training-runs/{run}` 302 -> cockpit; `/up` health; `/storage/*` and `/artwork/*` assets; `/api/v1/*` JSON; `/training-runs/{run}/export/{format}` and `/preferences/export` downloads. | Done |

Route count was never a screen count. The old 34/62 record and the 66-entry inventory are
both superseded by R1.1's measured list.

---

## Phase R2 — Rendered defect closure

Each slice below was **opened by a measurement**, not by a source reading. A slice is done
when the after-measurement is recorded beside the before-measurement.

### R2.1 — Compare picker blank state (DONE)

| Field | Value |
|---|---|
| Route | `/legacy/compare` |
| Defect | Four compare checkboxes rendered **ticked** while `columns` was empty; clicking a "ticked" box unticked it, so the control's visible state contradicted its value. |
| Root cause | `BaseCheckbox.modelValue: boolean \| string \| number` compiles to `{type:[Boolean,String,Number]}`. The Vue runtime's boolean cast rewrites an empty-string prop to `true` (`runtime-core`: `opt[1] && value === ''`), and `isChecked()` reads a boolean as the answer while ignoring `value`. |
| Fix | Union reordered to `string \| number \| boolean`. |
| Evidence | Before `39=true,37=true,35=true,34=true`; after all `false`; ticking two + Compare renders a 26-row table. Six other consumers swept unchanged. |

**Carry-forward:** any future component whose prop union includes `Boolean` before `String`
inherits this cast. State the order's meaning in the prop's own docstring (done).

### R2.2 — Advisor rail composition (DONE)

| Field | Value |
|---|---|
| Route | `/training-runs/{run}/cockpit` |
| Defect | A `.card` inside `.card` — a 3px left rail nested in a 3px left rail — so the rail read as two stacked panels. The "OBLIGATIONS" label sat 0px under the paragraph above it and read as that sentence's tail. |
| Fix | `NextDecision.vue`: inner `div.card` -> `div`; the obligations `section` gained `mt-4`. |
| Evidence | Visible nested cards 1 -> 0; label gap 0px -> 16px. |

The Advisor *region* (`AdvisorRail.vue`) keeps its `tabindex="0"` and `aria-labelledby`;
a nested region was deliberately not added, since it would create a second tab stop to the
same content.

### R2.3 — WCAG 2.2 2.4.11 focus obscured by the sticky savebar (DONE)

| Field | Value |
|---|---|
| Routes | `/training-runs/{run}/skills`, `.../deck`, `/legacy/{run}` |
| Defect | `.savebar` is `position:sticky; bottom:0` and 94px tall. Tabbing each screen put 6 / 5 / 15 **focused** controls underneath it, including the bar's own "Clear plan". Scrolling did reach the content, so nothing was unreachable — but 2.4.11 is about visibility, not reachability. |
| Fix | `resources/css/app.css`: `html:has(.savebar){scroll-padding-bottom:6rem}`, mirroring the repo's existing mobile-bar rule (`@media (max-width:767px)`, 4rem) and the setup-step rule (6.5rem), each scoped with `:has()` so unrelated screens pay nothing. |
| Evidence | Genuine occlusions (bar's own controls and the off-canvas rail excluded): Skills 6 -> 0, Deck 5 -> 0, Legacy 15 -> 1. The residual 1 is "Data sources" in the hidden rail at x=0, outside the bar's x=262 band — not occluded. |

### R2.4 — Help column imbalance (DONE)

| Field | Value |
|---|---|
| Route | `/help` |
| Defect | Left column ended at 1053px while the right ran to 2413px: **1360px of dead space**, and a 133-word reference table squeezed into 315px so its rows wrapped every 3-4 words. Six of the eight answer cards were in the narrow rail. |
| Fix | `Help.vue`: "Rules that are easier to look up" moved from the narrow rail into the wide column. |
| Evidence | Columns 1053/2413 (Δ1360) -> 1495/1617 (Δ122). The moved table 781px -> 428px tall. Heading order and `regions[0] === 'help-loop'` unchanged (`help.spec.ts` pins both). |

### R2.5 — Setup.vue typecheck regression (DONE)

A pre-existing `TS7053` in `Snapshot/Setup.vue` (another workstream's file) failed the
typecheck gate. One-line widening of the error bag's key type; no behaviour change. Recorded
because the gate could not have gone green otherwise, **and because it was pre-existing** —
attributing it to this line would be false.

---

## Phase R3 — Screens verified appropriate, no change (DONE 2026-11-22)

These were inspected and left alone. Recording them matters: an absence of change is a
result, and silently re-auditing them later is waste.

| Screen | Why it holds |
|---|---|
| `/` Dashboard | 8 cards, `g-side` 663/315, no overflow. All three layout modes exercised **through the stored preference** end to end: wide 1364px, standard 1152px, compact 768px canvas. |
| `/training-runs/{run}/training` | 661/315 bento-hybrid. At 320 the discipline table sits in a named, focusable region (`role=region`, `tabindex=0`, "Discipline comparison, scrollable", 252/334) with zero document overflow — the two-dimensional-scroll contract holding as designed. |
| `/training-runs/{run}/scenario` | 16 panel cards on a 12-column bento grid. The two rows holding a single `col-span-4` card are grid arithmetic, not a defect — deliberately **not** "fixed" into symmetry. |
| `/training-runs/{run}/timeline` | Stat strip + record table + phase grouping; coherent. |
| `/database/scenarios` | Four per-scenario panels, consistent table grammar. |
| `/preferences` | Columns balance exactly (1385/1385); radios reflect the stored value. |
| `/career/snapshot/setup` | Prior pass's correction intact: `g-side-wide-second` gives 312/656, 992px form, three stat columns. |

---

## Phase R4 — Remaining work

Ordered by what unblocks the most evidence per unit of risk. Status column added by the R4 pass
(2026-11-23): every item below is now either closed with browser evidence or carries a precise
limitation. Measurements are on an isolated scratch database (`.scratch-uma/lead/r4/r4.sqlite`)
served on :8300; nothing was written to the shared `database/database.sqlite`.

| ID | Item | State | Evidence |
|---|---|---|---|
| R4.1 | Theme persistence through the real Save flow | **Done** | All three palettes selected via the Theme radios, saved with the screen's own `Save changes`, then reloaded: `light`/`dark`/`eva01` each reached `html[data-theme]` on the next document and the control stayed checked. Card surface moved with the palette (light `rgb(228,241,253)`, dark `rgb(18,34,79)`, eva01 `rgb(52,40,91)`); layout radio (`standard`) and `failure_estimate` (`off`) did not move. Restored to "Follow the system" (`data-theme` back to null). |
| R4.2 | `/veterans/compare` picker | **Done** | Scratch DB seeded with eight banked Veterans (`TrackerVeteransSeeder`). Library rendered 8 checkboxes, all initially unchecked; ticking rows 7 then 8 produced `?veterans%5B0%5D=7&veterans%5B1%5D=8` — the accepted indexed form, in tick order. Matrix rendered `2 careers side by side`, 4 columns (Property / two careers / Difference), sticky property column at `left:0` with an opaque background, and the named focusable region `Career comparison, scrollable`. At 768/390/320 the pager appears, two header cells stay visible and document overflow is 0. |
| R4.3 | Error and loading states in browser | **Partially verified, with a recorded cause** | 419/500 documents render coherently at 1280/768/390/320 (one `h1`, one filled exit, three exits all 44px, no `http-equiv`, 0 overflow; 419 keeps its stored theme, 500 correctly carries none). Loading states verified: a held visit shows the shell's `<progress>` indicator and clears it on arrival, and the catalog sets `aria-busy="true"` with a `Loading results…` status that resolves to the filtered result. **Limitation:** a 419 cannot be produced through a browser navigation on this build — Laravel 13's `PreventRequestForgery::hasValidOrigin()` accepts any `Sec-Fetch-Site: same-origin` POST before comparing tokens (measured: a deliberately stale `_token` answered 302; the same POST without a session answered 419). The two documents were therefore rendered from the real Blade views and opened as documents. A 500 needs a broken app. |
| R4.4 | Sub-24px target inventory | **Done** | Swept 14 routes at 1280 through `a, button, input, select, textarea, summary, [tabindex=0]` and classified each hit. Compliant through a larger target: every checkbox/radio whose visible box is 13-20px sits inside a `label` measured 625x64, 279x61, 120x48 or 252x100. Compliant at the criterion's floor: `AbsenceValue`'s compact `N/A` disclosure is 24x24 by design inside a table cell. Corrected: five disclosure triggers were 16-20px tall against the project's own 44px convention (`EventDecision` retry note, `Cockpit` Notes, `Dashboard` card note, `Legacy/Builder` notice and Spark summary) — all now `min-h-11` and measured 44px. |
| R4.5 | Career Workspace screens at 768/390/320 | **Done, with three defects corrected** | Ten routes measured at 1280/768/390/320. Every screen has exactly one `h1`, zero *document* overflow at all four widths, and grids that collapse cleanly (`g-side` 661/315 -> one track; `g-4` 239x4 -> one track). Three cards were horizontal scrollers with no tab stop — Race Decision's race table, the three Race Planner group tables, and Inheritance's Result pick card — corrected to `role="region"` + `aria-labelledby` + `tabindex="0"`; each measured focusable, visibly ringed (2px solid), and keyboard-scrollable (ArrowRight moved 0->40, 0->40, 0->25). Regression case added to `career-race-decision.spec.ts`. |

### R4.5 screens measured and left unchanged

| Screen | Why it holds at narrow widths |
|---|---|
| Race Decision | After the region fix: 284 client against 506 of table at 320, inside a named scroller; detail tiles stay 4-up at 1280 and stack below. |
| Event Decision | `g-side` 891/891 balanced at 1280; disclosures carry the 44px floor; no overflow at any width. |
| Inheritance Event | `g-2` 489/489 and `g-3` 312x3 at 1280; collapses to one track; no overflow. |
| Skills Planner | 48 cards; its table is already a named `region` at `min-w-[44rem]`, so it scrolls inside the region and never the document. |
| Timeline | `g-4` 239x4 -> one track; the turn log is already `Turn log, scrollable`; no overflow. |
| Career Result | `tiles g-4` + balanced `g-side`; the Completed races table is a named region; no overflow. |
| Cockpit, Training, Snapshot Setup | Unchanged and still coherent; Cockpit's `Obligations, scrollable` region overflows by 9px at 320 as designed. |

### R4.4 classification detail (exceptions with evidence)

| Hit | Measured | Disposition |
|---|---|---|
| Checkbox cells, Skills Planner | 20x20 box | Compliant target size: inside a page row; sibling boxes are 49px apart centre-to-centre, so the 24px circle test passes with no intersection. |
| Training radios | 13x13 box | Compliant by the larger target: each is wrapped in a `label` measured 625x64 / 202x94. |
| Preferences radios | 16x16 box | Compliant by the larger target: `label` measured 627x61 / 279x61. |
| Snapshot Setup "I don't know" boxes | 14x24 box | Compliant by the larger target: `label` measured 120x48. |
| `AbsenceValue` compact `N/A` | 24x24 | Compliant target size: exactly SC 2.5.8's floor, and it is the inline disclosure for one cell value. |
| Skip link | 1x1 | Not a defect: it is `sr-only` until focused, then `focus:not-sr-only` renders it. |
| Help inline links (Careers, Import a run, Check the source) | 46x19, 69x16, 94x16 | Compliant through spacing: nearest other target is 161-252px away, so the 24px circle never intersects. |
| Disclosure triggers (five sites) | 16-20px tall | **Defect, corrected** to `min-h-11`; measured 44px after. |

---

## Phase R5 — integration verification (2026-11-23)

Resumed from the R4 pass against a newer tree (HEAD `3600741e`, peer commits landed during R4).
Measurements ran on an isolated scratch database (`.scratch-uma/lead/r5/r5.sqlite`, migrated +
`UraFinaleRunsSeeder`) served on `:8310`; the shared `database/database.sqlite` was never used.

| Item | State | Evidence |
|---|---|---|
| Combined Vite build | **Done** | `npm run build` -> `BUILD=0`, manifest `07:59:46`, newer than every `resources/js` file (newest was `AdvisorAnalysisPanel.vue` at `06:43:53`). The bundle therefore includes the Advisor workstream's changes, not just R4's. |
| TypeScript | **Done** | `npm run typecheck` -> `TSC=0`. |
| UI framework lint | **Done** | `npm run lint:ui` -> `LINT=0`; `arbitrary-value` **208** (baseline 209, no increase), `card-in-card` 0, `dark-utility` 0, `debt-increase` 0. |
| Advisor `window.axios` transport | **FIXED** | `bootstrap.ts` installs `window.axios` but had **no importer** after `0ea8d43c` deleted `app.ts`; the Advisor panel threw before issuing any request and settled on its `unavailable` line. Measured `typeof window.axios === 'undefined'` and zero XHR; after the fix the XHR fires and the panel reaches `ready`. |
| G-18 token contrast | **Done** | `php artisan test --filter='browser contrast and token resolution'` -> 4 passed, **439 assertions**; full `DesignTokensTest` -> **18 passed, 501 assertions**, no skips. 49 contract pairs across `light`, `dark` and `eva01`. This is the gate's real run, not the zero-assertion false pass KI records warn about. |
| R4 cumulative diff | **Reviewed** | 9 files. R4 hunks are 1 line each in the 4 shared files (peer BENO/N3.2 work isolated and preserved); 3 region files; 1 spec; plus the R5 `spa.ts` import. |
| Responsive regression | **Done** | 8 routes x 4 widths = 32 cells: one `h1` each, 0 document overflow, 0 visible card-in-card, 0 unnamed/unfocusable scrollers. |
| Disclosure + region behaviour | **Done** | All non-inline summaries 44px and toggling; `AbsenceValue`'s inline `N/A` stays 24px (SC 2.5.8 floor). Regions named, focused, 2px ringed, ArrowRight-scrolled on all 3 corrected screens. |
| Themes | **Done** | `light` / `dark` / `eva01` chosen through the real Preferences Save: `#c3d9f2` / `#050a1b` / `#1e1435` page, three distinct palettes, EVA-01 keeps green `#72c068` and its own risk tone rather than collapsing to one hue. |
| R4.3 error flow | **Retained as partially verified** | 419/500 documents render correctly; a same-origin browser POST cannot produce 419 because Laravel 13's `PreventRequestForgery::hasValidOrigin()` accepts `Sec-Fetch-Site: same-origin` before comparing tokens. Unchanged, by design. |

### Advisor AI coordination — peer-owned, not modified

`resources/js/components/career/AdvisorAnalysisPanel.vue` is **untracked peer work** (Advisor AI
workstream) and still carries one disclosure below the project's 44px convention:

| Field | Value |
|---|---|
| Component | `resources/js/components/career/AdvisorAnalysisPanel.vue` |
| Control | `<summary>` inside the `Sources ({{ n }})` `<details>`, `mt-3` |
| Measured target | **279x20** at 1280, **322x20** at 390 (rendered, with the analysis endpoint stubbed so the panel reaches `ready`) |
| Requirement | The same 44px control convention the other 45+ disclosure triggers follow (`tests/utils/tap-targets.ts`; R4.4 corrected five identical cases) |
| Expected correction | `<summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">`, matching the corrected siblings |
| Keyboard / focus | Native `<summary>`; must keep its toggle, stay in tab order, and keep the base `outline-color` ring (`app.css:1091`) |
| Regression check | Add a 44px assertion beside the panel's existing tests; reuse the `min-h-11` measurement used in R4.4 |

This agent did not edit the file: it is another workstream's untracked component, and its owner is
the only party that can confirm the panel's intended density.

---

## Owner-gated and backend-dependent

| Item | Gate |
|---|---|
| `BENO-COCKPIT-001` — retire the `Career tools` disclosure | Owner must confirm the write doors (`runs.update`, `runs.turns.update`, `runs.turns.destroy`) exist first. Deleting before they do makes writes unreachable. **Not touched.** |
| 768px minimum (`DESIGN.md` §2.3, R85 status line) | Proposal, not ratified. Do not "fix" the scroll-to-reflow without a decision. |
| Token contrast gate (G-18) | **Closed by R5**: 18 passed / 501 assertions across the three themes (see Phase R5). |

---

## Acceptance for this line

1. Every reachable user-facing screen has rendered evidence, not a source reading.
2. Every recorded defect in R2 has a before/after measurement.
3. R3's no-change list stays defensible if re-measured.
4. R4 items are either closed or still listed here with a first step.
5. No completion claim without command output; no unattributed regression.
