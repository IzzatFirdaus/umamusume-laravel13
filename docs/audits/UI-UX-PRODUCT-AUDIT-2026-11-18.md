# Audit report — Trainer Desk, full frontend surface

## 1. Identity

- **Audit id:** AUD-20261118-001
- **Date:** 2026-11-18
- **Mode:** M4 product audit with A11y overlay
- **Scope:** All page-rendering browser routes and their Vue pages, plus the panel-only scenario surfaces registered in `SCREEN_SPEC.md` §3. 59 page-rendering routes, 58 top-level page components, 57 registered screen IDs, 4 Cockpit panel sub-components. 36 write/redirect/download endpoints were inspected only insofar as they own a screen's form.
- **Out of scope:** Backend correctness, data sourcing accuracy, lore gate, performance budgets, framework error documents as rendered pages (no route), and any state for which neither a dated screenshot nor a source location exists.
- **Frameworks applied:** All ten files indexed by `docs/framework-ui-ux/design-framework-index-2026-10-21.md`, routed through §4 of `docs/framework-ui-ux/_audit-prompt.md`. External standards were re-checked against primary sources; see §4.
- **Rendered evidence:** **PARTIAL.** Live Chromium/CDP could not be established in this session (Chrome remote-debugging authorization timed out). Ten dated screenshots from 2026-11-15 and 2026-11-16 were viewed directly and are cited as `OBSERVED_RENDERED` artifacts. No new DOM measurement, contrast reading, target-size reading, or keyboard pass was performed.
- **Reference path assumption:** The requested `docs/frontend-ui-ux` directory does not exist in this tree. The audit used `docs/framework-ui-ux/` (which contains the operating audit prompt, the settled index and all ten framework files) plus `docs/research/ui-ux-application-context-brief.md` and the `docs/audits/` screenshot corpus. This assumption is recorded rather than silently made.

## 2. Executive summary

- **No severity-4 finding was confirmed.** The product is unusually disciplined about disclosure, provenance and error prevention: the Preflight step states what will be created, what the run will be measured against, what is not built, and requires an explicit acknowledgement before its one write. That satisfies `SHR-4`, `TOG-6`, `UX-3` and `NRM-3` on a single screen.
- **One severity-3 finding is carried, not new:** the Cockpit overflows its canvas by 88 px at 640 px, already registered as KI-160 (`KNOWN-ISSUES.md:4953`). It remains open and blocks a clean reflow claim.
- **Two severity-2 findings are new:** the four-band desktop navigation becomes a flat unbanded strip at 390 px (F-01), and reduced-motion handling is applied in 3 of 42 Vue files that use transitions (F-03).
- **The largest unmeasured risk is conformance, not heuristics:** no contrast ratio, target size, focus visibility or 320 px reflow reading was taken in this session. Every such claim is in the unmeasured register (§6), not reported as a pass.
- **The audit instrument itself has a defect:** the operating prompt cites derived rule IDs that do not exist in the framework files (§7). Future audits using it risk filing false findings.

## 3. Scope and coverage

### 3.1 Inventory reconciliation

| Measure | Count | Source |
|---|---|---|
| Browser routes | 95 total | `routes/web.php` (route entries; the UX brief §17 records 91 named routes at an earlier HEAD) |
| Page-rendering routes | 59 | `routes/web.php`, controller `Inertia::render` calls |
| Write / redirect / download endpoints | 36 | `routes/web.php` |
| Top-level page components | 58 | `resources/js/pages/**/*.vue` |
| Cockpit panel sub-components | 4 | `resources/js/pages/Career/Cockpit/Panels/` |
| Registered screen IDs in `SCREEN_SPEC.md` §3 | 57 | `SCREEN_SPEC.md:49-106` |
| Routes without a page | 0 | cross-reference |
| Pages without a route | 0 | cross-reference |
| Registered screens without code or an error view | 0 | cross-reference |

Seven route groups (nine individual routes) carry no §3 matrix ID (`runs.settings`, `runs.confirm-delete`, `runs.turns.edit`, `review.detail`, `veterans.transfers`, `database.trainees/supports/skills` folded into `SCR-SYS-005`, and `database.races.show` folded into `SCR-SYS-006`). Five wireframe-era aliases exist only in §4.25. This is a registry gap, not a missing screen.

`SCREEN_SPEC.md` has no literal §8 heading; the screen matrix is §3 (`:45-106`) and per-screen state tables live in §4 and §4.25. The audit used those sections as the screen-behavior authority.

### 3.2 Per-surface coverage matrix

`R` = rendered frame viewed in this audit (dated artifact below). `S` = source/state-table evidence only. `E` = empty state present in a viewed frame. `F` = finding raised. `U` = unmeasured claims remain.

| Screen ID | Route / surface | Evidence | Note |
|---|---|---|---|
| SCR-CAR-001 | `home` Dashboard | R, E | Populated and empty desktop frames; mobile frame. F-01, U |
| SCR-CAR-002 | `career.scenario` | R, S | Wizard step 1; desktop + mobile frames |
| SCR-CAR-003 | `career.trainee` | R, S | Desktop + mobile frames |
| SCR-CAR-004 | `career.trainee.profile` | R, S | Trainee search/selected frames in v3-ux set |
| SCR-CAR-005 | `career.target` | R, S | Required-marker frame in followup set |
| SCR-CAR-006 | `legacy.index/builder/compare` | R, S | Three routes, one screen ID |
| SCR-CAR-007 | `runs.deck` | R, S | Desktop + mobile frames |
| SCR-CAR-008 | `career.legacy` | R, S | Desktop + mobile frames |
| SCR-CAR-009 | `career.deck` | R, S | Desktop + mobile frames |
| SCR-CAR-010 | `career.preflight` | R, E | Desktop + mobile frames; strongest disclosure on the product. U |
| SCR-CAR-011 | `runs.cockpit` | R, S | Desktop light/dark, mobile. F-02 (carried), U |
| SCR-CAR-012 | `runs.training` | R, S | Desktop + mobile frames |
| SCR-CAR-013 | `runs.races.decision` | R, S | Desktop + mobile frames |
| SCR-CAR-014 | `runs.events.decision` | R, S | Desktop + mobile frames |
| SCR-CAR-015 | `runs.inheritance` | R, S | Desktop + mobile frames |
| SCR-CAR-016 | `runs.skills.planner` | R, S | Desktop + mobile frames |
| SCR-CAR-017 | `runs.timeline` | R, S | Desktop + mobile frames |
| SCR-CAR-018 | `runs.result` | R, S | Desktop light/dark, mobile |
| SCR-CAR-019…022, 024 | scenario panels (mounted) | R, S | Viewed as regions of Cockpit and Scenario detail |
| SCR-CAR-023 | `runs.races.planner` | R, S | Desktop + mobile frames; U (table legibility at 320 px) |
| SCR-CAR-025…027 | snapshot entry/setup/review | R, S | Three routes |
| SCR-CAR-028 | `runs.team` | R, S | Desktop + mobile frames |
| SCR-CAR-029 | `runs.scenario-detail` | R, S | Desktop light/dark, mobile |
| SCR-RUN-001 | `runs.index` | R, E | Populated + empty frames |
| SCR-RUN-002 | `runs.create` | R, S | Desktop + mobile frames |
| SCR-RUN-003 | `runs.show` | S | Retired; redirects to Cockpit. Not a screen |
| SCR-RUN-004/005 | `runs.import`, import preview | R, S | Form + preview frames |
| — | `runs.settings`, `runs.confirm-delete`, `runs.turns.edit` | R/S | Unregistered IDs; settings and delete frames viewed, turn-edit source only. U |
| SCR-CAT-001/002 | `catalog.index/show` | R, S | Index + detail frames |
| SCR-SKL-001/002 | `skills.index/show` | R, S | Index + detail frames |
| SCR-SUP-001/002 | `support-cards.index/show` | R, S | Index + detail frames |
| SCR-REV-001 | `review.index` | R, E | Populated + empty frames; detail unregistered |
| SCR-VET-001/002/004 | `veterans.index/show/compare` | R, E | Populated + empty + compare frames |
| SCR-VET-003 | `runs.veteran` | R, S | Save Veteran frames |
| — | `veterans.transfers` | S | No dated frame in the reviewed sets. U |
| SCR-SYS-002 | `preferences.edit` | R, S | Desktop + mobile frames |
| SCR-SYS-005…011 | Database hub + 7 reference pages | R, S | All eight have desktop + mobile frames |
| SCR-SYS-012 | `help` | R, S | Desktop + mobile frames |
| SCR-SYS-001/003/004 | 404 / 419 / 500 | S | Framework error views, no browser route; no rendered frame reviewed. U |

### 3.3 States, viewports and themes actually covered

- **States:** populated and empty viewed in rendered frames for Dashboard, runs index and review; populated only for all other screens. `SCREEN_SPEC.md` §4 state tables were read for every registered screen. A rendered **loading** frame and a rendered **error** frame were not available in the reviewed corpus for any screen; those states are source-evidenced only.
- **Themes:** both light and dark viewed for Cockpit, Dashboard, Result and Scenario detail. One theme (light) viewed elsewhere. The dark-theme stat and scenario frames are dated 2026-11-16.
- **Viewports:** 1440×900 and 390×844 viewed for the full route sweep (2026-11-15); 1280×800, 375×812 and 640 px evidence exists in the followup set and KI-160 record. 320 px and 768 px were **not** viewed in this audit.
- **Keyboard-only pass:** not run on any surface.
- **Framework coverage:** §4 routing was applied to every screen. `GP-1…GP-5` were used for cognitive load, `IA-1…IA-6` for cross-screen structure, `WCAG-1…WCAG-8` only as citation discipline; no WCAG conformance verdict is claimed.

## 4. Evidence status and limits

### 4.1 Rendered artifacts viewed in this audit

All are existing repository artifacts; none was captured in this session.

| Artifact | Date | Screens |
|---|---|---|
| `docs/audits/browser-observation-2026-11-15/screenshots/dashboard-1440x900.png` | 2026-11-15 | Dashboard populated, desktop |
| `.../dashboard-390x844.png` | 2026-11-15 | Dashboard populated, mobile |
| `.../career-setup-preflight-1440x900.png` | 2026-11-15 | Preflight desktop |
| `.../career-setup-preflight-390x844.png` | 2026-11-15 | Preflight mobile |
| `.../training-runs-14-cockpit-1440x900.png` | 2026-11-15 | Cockpit desktop |
| `.../training-runs-14-races-planner-1440x900.png` | 2026-11-15 | Race planner desktop |
| `.../training-runs-14-races-planner-390x844.png` | 2026-11-15 | Race planner mobile |
| `.../help-390x844.png` | 2026-11-15 | Help mobile |
| `docs/audits/browser-observation-2026-11-15-followup/screenshots/statink-cockpit-1440x900-dark.png` | 2026-11-16 | Cockpit dark |
| `docs/audits/trainer-desk-v3-ux-retest/screenshots/retest-empty-dash.png` | 2026-11-12 | Dashboard empty |

The most recent dated set is `browser-observation-2026-11-15-followup` (files through 2026-11-16). The working tree has uncommitted changes in four frontend files (`UnityCupEditor.vue`, `ScenarioCardGrid.vue`, `UnityCupPanel.vue`, `GoalGroupCard.vue`, plus two Career pages); screenshots may predate those working-tree edits. Findings on those specific components would need a fresh frame and are not raised here.

### 4.2 Evidence status of claims in this report

- `OBSERVED_RENDERED`: F-01, and the positive observations in §2 and §3.
- `OBSERVED_SOURCE`: F-02 (KI record, measured previously), F-03 (source grep plus S11), the registry gap in §3.1, and all semantics claims (status roles, `aria-current`, focusable scroll regions).
- `UNMEASURED`: everything in §6.

### 4.3 External standards baseline (web research, primary sources)

Every URL below was fetched during this audit. The W3C Recommendation status and the new-criteria list were re-verified directly by the audit lead; the other readings come from the same primary-source pass.

| # | Source | Date / version | Verified fact used in this audit | URL |
|---|---|---|---|---|
| S1 | W3C | **Recommendation 12 December 2024** | WCAG 2.2 is the current REC; 2.1 is not deprecated by it | https://www.w3.org/TR/WCAG22/ |
| S2 | W3C WAI | current page | Nine success criteria are new in 2.2: 2.4.11 AA, 2.4.12 AAA, 2.4.13 AAA, 2.5.7 AA, 2.5.8 AA, 3.2.6 A, 3.3.7 A, 3.3.8 AA, 3.3.9 AAA | https://www.w3.org/WAI/standards-guidelines/wcag/new-in-22/ |
| S3 | W3C | REC 2024-12-12 | 4.1.1 Parsing is **obsolete and removed** from 2.2; do not cite it | https://www.w3.org/TR/WCAG22/ |
| S4 | W3C WAI | current page | 2.5.8 AA minimum target is **24 × 24 CSS px**; 2.5.5 AAA is 44 × 44 | https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html |
| S5 | W3C WAI | REC 2024-12-12 | 1.4.3 AA text contrast 4.5:1 (3:1 large); 1.4.11 AA non-text 3:1 | https://www.w3.org/TR/WCAG22/ |
| S6 | W3C WAI APG | living guidance, no REC status on primary page | "No ARIA is better than Bad ARIA"; a role is a promise, not keyboard behavior | https://www.w3.org/WAI/ARIA/apg/practices/read-me-first/ |
| S7 | W3C WAI APG | living guidance | Dialog pattern: name, focus moved in, Tab contained, Escape closes, focus returned | https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/ |
| S8 | Nielsen Norman Group | published 1994-04-24, reviewed 2024-01-30 | H1, H5 and H9 wording used by the repo's heuristics file | https://www.nngroup.com/articles/ten-usability-heuristics/ |
| S9 | MDN | last modified 2026-10-16 | `:focus-visible` changes the appearance, not when focus is evident | https://developer.mozilla.org/en-US/docs/Web/CSS/:focus-visible |
| S10 | Google web.dev | updated 2022-10-31 | Default focus order must match visual order; avoid positive `tabindex` | https://web.dev/learn/accessibility/focus/ |
| S11 | Google web.dev | updated 2019-03-11 | `prefers-reduced-motion: reduce` means remove non-essential movement | https://web.dev/articles/prefers-reduced-motion |
| S12 | WebDX CG / web.dev | current | Baseline 2024 is a yearly cohort of newly interoperable features, not a conformance level | https://web.dev/baseline |
| S13 | Design Tokens CG | **Draft Community Group Report 08 September 2026**, Format Module 2025.10 | Explicitly **not** a W3C Standard and not on the W3C Standards Track | https://www.designtokens.org/TR/2025.10/format/ |
| S14 | Apple HIG | current | 44 × 44 pt is iOS's **default** target, not its stated minimum (28 pt) | https://developer.apple.com/design/human-interface-guidelines/accessibility |
| S15 | Material Design | v2 page | 48 × 48 dp is a platform recommendation for touch targets | https://m2.material.io/design/usability/accessibility.html |
| S16 | Nielsen Norman Group | 2022-07-31 | Fitts's law: movement time depends on target size and distance | https://www.nngroup.com/articles/fitts-law/ |

**Settled reading of the 24/44 conflict:** 24 × 24 CSS px is the normative WCAG 2.2 AA threshold (S4); 44 × 44 is AAA (S4) or a platform-house heuristic (S14, S15, S16). The repo's 44 px rule exceeds AA and must never be described as AA conformance. This matches the settlement in `design-framework-index-2026-10-21.md`.

### 4.4 Provenance caveats carried from the framework files

- Gerhardt-Powals: primary paywalled; secondary enumeration lists nine items, conventionally ten; the 5/6 split is unconfirmed.
- Dan Brown's IA: only the eight principle names and one-line summaries were read; the article prose was not fetched.
- Norman: the book was not fetched; quotations come from the encyclopedia entry.
- Tognazzini: the sixteen are from the original edition; the 2014 revision truncated and its final count is unverified.
- Laws of UX: 29 entries including Zeigarnik; 28 in the index listing. Closure and Continuity belong to Laws of UI, Common Fate to Gestalt, and there is no "Law of Experience".
- WCAG file: the change log says "five new in 2.2" while the body marks six; the sixth, 3.3.8, is also marked not applicable because the product has no authentication. Reconciled to five applicable new criteria, consistent with S2.

## 5. Findings

Severity uses Nielsen 0–4; confidence is a separate axis. One defect, one finding.

| ID | Rule | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
|---|---|---|---|---|---|---|---|---|---|
| F-01 | `IA-2` (related `SHR-1`) | Information architecture | Global navigation, populated, 390 px light theme | Desktop navigation is banded into PLAY / MY DATA / LIBRARY / SYSTEM. At 390 px the same destinations appear as one flat horizontal strip of nine items with no band labels. The four-band grouping is lost at the narrow width, and the exception is not named in any document. | `OBSERVED_RENDERED` | `docs/audits/browser-observation-2026-11-15/screenshots/dashboard-1440x900.png` and `.../dashboard-390x844.png` | 2 | Medium | Preserve the band labels in the mobile strip, or group the destinations in a labelled disclosure. If flat-at-mobile is intentional, name it beside the existing dark-theme and Database density exceptions in `SCREEN_SPEC.md` §7. |
| F-02 | `KI-160` / `WCAG 1.4.10` | WCAG 2.2 AA (carried defect) | Cockpit, populated, 640 px | The Cockpit canvas measures `scrollWidth 728` against `clientWidth 640`, an 88 px horizontal overflow attributed to `NextDecision.vue`'s Obligations table and its grid column. Already registered and explicitly not fixed by the race-strip change. | `OBSERVED_SOURCE` | `KNOWN-ISSUES.md:4953-4965`; diagnostic `.scratch-uma/complete-1/diag5.log` cited there | 3 | High | Keep open under KI-160. Fix the grid column and table wrapper so the left column holds at 320 px and 200% width, then re-run `tests/browser/career-race-strip.spec.ts` reflow case. This audit adds no new diagnosis. |
| F-03 | `GT-5` (related S11) | Gestalt / external guidance | Application-wide motion, all states | `transition-`/`animate-` utilities appear in 42 Vue files, but `motion-reduce:transition-none` appears in only three (`AppLayout.vue:367,398`, `Review/Index.vue:136`). There is no global reduced-motion rule in the source scan. A user with `prefers-reduced-motion: reduce` therefore still receives most author transitions. | `OBSERVED_SOURCE` | `resources/js` grep, 2026-11-18; S11 | 2 | Medium | Add one global `@media (prefers-reduced-motion: reduce)` rule that removes non-essential transitions, then remove the three local duplicates. Acceptance: a browser spec with reduced motion emulated asserts no transition on the sidebar and progress bar. |

**Positive observations, not findings:** the empty Dashboard gives an exemplar and two doors (`retest-empty-dash.png`, `IA-5`); Preflight states closure, constraints and the single write (`SHR-4`, `TOG-6`, `NRM-3`); data provenance is printed beside the figure on Dashboard (`UX-4`); status messages use `role="status"`/`role="alert"` in 25 located sites; navigation and stepper items carry `aria-current`; wide tables use focusable named scroll regions (`DataTable.vue:13,84`).

**Absence claims:** none is made in this report. The audit prompt's four-layer rule was applied to the two candidate absences (reduced-motion handling and unbanded mobile navigation); both were checked at screen, navigation, implementation and data layers as far as source allows.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
|---|---|---|
| Text contrast meets 1.4.3 AA in both themes on every screen | No browser; token values in `app.css` are intentions, not readings | Rendered contrast measurement per text token in light and dark |
| Non-text and focus-indicator contrast meets 1.4.11 | Same | Rendered 3:1 readings on borders, bars and focus rings |
| Interactive targets meet 2.5.8 AA 24 × 24 and the 44 px house rule | No DOM geometry captured | `getBoundingClientRect()` sweep at 320/390/768/1280 px |
| Focus is visible and not obscured (2.4.7, 2.4.11) | No keyboard-only pass | Keyboard traversal with screenshots at each stop |
| Race planner tables reflow at 320 px without loss (1.4.10) | Only 390 px viewed; KI-160 proves a reflow gap elsewhere | 320 px DOM scroll-width reading on `runs.races.planner` |
| Loading and error states render as specified (C-7, ADR-0007) | No rendered loading or error frame in the reviewed corpus | Trigger an async load and a forced error on one data view per family |
| Reduced motion actually suppresses all non-essential movement | Source scan only, no emulated-media run | Browser run with `prefers-reduced-motion: reduce` |
| Drag interactions have a single-pointer alternative (2.5.7) | Inventory did not enumerate drag handlers | Grep plus a pointer-only interaction pass |
| Help sits in the same relative order on every page (3.2.6) | Observed on the frames viewed, not across all 58 | Cross-route DOM order check |
| Wizard draft never asks twice for entered data (3.3.7) | Draft behavior observed in copy only | Form-by-form field trace across the six steps |
| 404 / 419 / 500 pages are usable front doors (IA-1) | No rendered frame; no browser route | Direct navigation to each error view with a return path check |

## 7. Conflict and gap log (not findings)

**Conflicts applied as settled:** 50 ms acknowledgement vs 400 ms response; consistency vs named exceptions; Database density exemption; 24 px AA vs 44 px house rule; learnability vs usability; progressive disclosure vs the Advisor as a reference surface; visual closure permitted, informational closure forbidden. None was re-filed as a defect.

**Gaps confirmed or updated:**

1. `H3` / `SHR-6` (no undo after a committed turn) remains a gap. On a specific screen it would become a finding; no new specific harm was observed in this audit.
2. `H7` (accelerators) remains a gap.
3. `H10` is **updated, not open**: `SCR-SYS-012` Help exists and was viewed on mobile. It contains a task-focused reference with sections on the turn loop, reference data, where numbers come from, and what is not built.
4. **Audit-instrument gap (new):** `docs/framework-ui-ux/_audit-prompt.md` §4 and §10 cite derived IDs that do not exist in the framework files: `SHR-8`, `UX-11`…`UX-29`, `UI-8`…`UI-13`, `TOG-9`…`TOG-16`, `GP-6`…`GP-10`, `IA-7`…`IA-8`, `NRM-7`…`NRM-8`. The actual derived tables are UX-1…10, UI-1…7, SHR-1…7, TOG-1…8, GP-1…5, IA-1…6, GT-1…6, NRM-1…6, H1…10, WCAG-1…8. Also `laws-of-ux-2026-10-21.md` misnumbers Occam as (21) instead of (19), and `wcag-2.2-accessibility-2026-10-21.md` contains "Daraging Movements". These are documentation defects in the audit instrument; they are logged here so no future audit files a finding against a non-existent rule.

## 8. Recommendations

Ordered by severity, then confidence, then effort.

| # | Change | Satisfies | Effort | Acceptance check |
|---|---|---|---|---|
| 1 | Fix the KI-160 grid/table overflow at 640 px | `WCAG 1.4.10`, KI-160 | M | `tests/browser/career-race-strip.spec.ts` reflow case green; `scrollWidth === clientWidth` at 640 px and 320 px |
| 2 | Add a global reduced-motion rule; drop the three local copies | `GT-5`, S11 | S | Emulated `reduce` run shows no transition on sidebar and progress bar |
| 3 | Preserve or formally exempt the mobile navigation bands | `IA-2`, `SHR-1` | S if exemption, M if layout | 390 px frame shows band labels or a named disclosure; `SCREEN_SPEC.md` §7 records the exception if kept flat |
| 4 | Correct the rule IDs in the audit prompt and the two framework typos | Audit-instrument integrity | S | Every ID in `_audit-prompt.md` §4/§10 resolves in the corresponding framework file |
| 5 | Register the seven unregistered page routes in `SCREEN_SPEC.md` §3 with state tables | Screen-behavior authority | M | §3 matrix row count reconciles with `Inertia::render` call sites |
| 6 | Capture one rendered loading and one rendered error frame per screen family, then close the §6 rows that can be measured | C-7, `WCAG-8` | M | Each formerly unmeasured claim carries a dated artifact or is explicitly retained as unmeasured |

## 9. Exit checklist

- [x] Scope, mode, surfaces, states, viewports and themes stated (§1, §3).
- [x] Framework routing recorded; narrowest framework chosen per question (§5, §7).
- [x] Pre-flight provenance caveats carried (§4.4).
- [x] Evidence capability recorded; CDP authorization failure named (§1).
- [x] Every finding has a rule ID, surface, state, observation and evidence status (§5).
- [x] No Low-confidence finding carries severity 3 or 4; F-02 is High, F-01/F-03 are severity 2.
- [x] Severity and confidence are separate columns.
- [x] No defect filed twice under two frameworks.
- [x] Known conflicts logged as settlements (§7).
- [x] Gaps in the gap register, not repeated per screen (§7).
- [x] Unmeasured register names what would settle each claim (§6).
- [x] No WCAG conformance claimed beyond criteria actually tested; no rendered claim made without a viewed artifact.
- [x] Mode is M4, so the M1 ten-question re-check does not apply.

---

**Research citations:** S1–S16 in §4.3. **Repository evidence:** `AGENTS.md`, `SCREEN_SPEC.md` §3/§4/§4.25, `KNOWN-ISSUES.md` KI-160, `docs/framework-ui-ux/` (ten files + index + operating prompt), `docs/research/ui-ux-application-context-brief.md` §8/§17, `routes/web.php`, `resources/js/pages/`, `resources/js/layouts/`, and the dated screenshot artifacts listed in §4.1.

---

## 11. Dated erratum — 2026-11-18, re-derived at HEAD `2546689e`

This section preserves §5 verbatim and records that its three findings no longer describe current source. The original sentences above are not rewritten. The re-derivation was performed for `docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` and `docs/plan/plan-ui-ux-implementation-2026-11-18.md`.

| Finding | Original claim | Measured at this HEAD | Status |
|---|---|---|---|
| F-01 | Mobile navigation is a flat nine-item strip at 390 px | `AppLayout.vue:194-224` defines four `inBar` destinations; `:234-235` splits `barItems` from `moreItems`; `:640-700` renders a fixed bottom bar plus a `<details>` More panel. Landed in `3f05138a` (2026-11-03) and `d886d140` (2026-11-11), before the 2026-11-15 screenshots. | **RESOLVED** — no code change owed |
| F-02 | Cockpit overflows 88 px at 640 px; KI-160 open | `NextDecision.vue:346-348` wraps the Obligations table in `overflow-x-auto` with a rationale comment. KI-160's Cockpit half is resolved; its Races half was split into KI-166 (`KNOWN-ISSUES.md:5328`). A 320 px browser sweep at this HEAD measures 0 on Cockpit and 59 px on Race Decision, 40 px on Race Planner. | **RESOLVED as filed; subclass reopened as KI-166** |
| F-03 | No global reduced-motion rule | `resources/css/app.css:943-951` carries `@media (prefers-reduced-motion: reduce)` with `animation-duration`, `animation-iteration-count`, `transition-duration` and `scroll-behavior` overrides. The audit's grep covered `resources/js` only and missed CSS. | **RESOLVED** — no code change owed |

**What the re-derivation changed in execution:** the three recommendations aimed at those findings were skipped as completed work. The measured sweep added a new frontend defect the audit had only listed as unmeasured: absolutely positioned `sr-only` spans inside table cells escaped their card's `overflow-x: auto` because every ancestor was `static`, widening the document by 59 px on Race Decision and 40 px on Race Planner at 320 px. The fix adds `position: relative` to the shared `.card:has(> table.t)` scroller in `resources/css/app.css`; a browser A/B measured `59→0` and `40→0` before the source edit. Regression cases now live in `tests/browser/career-race-decision.spec.ts` and `tests/browser/career-race-planner.spec.ts`.

**Unchanged:** §6's contrast, target-size, focus and keyboard claims remain unmeasured by this audit. §7's conflicts and gaps stand. The rule-ID defect in §7.4 was corrected in `docs/framework-ui-ux/_audit-prompt.md` (all `SHR-8` references now resolve to `SHR-7`, the derived ID for Shneiderman's golden rule 8) and the two framework typos were corrected in place.
