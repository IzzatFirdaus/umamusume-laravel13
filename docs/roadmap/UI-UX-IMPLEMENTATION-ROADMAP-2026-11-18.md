# UI/UX implementation roadmap — Trainer Desk (2026-11-18)

**Status:** current-state roadmap. First re-baselined at HEAD `2546689e97c85741bffd2c7eabd33874c7966ee8` on 2026-11-18; execution re-baselined at HEAD `cfd8bd78a395eaf4e38b84929cd82965ab173719` the same day. `cfd8bd78` landed one peer commit (slices 0.2–4.3 of the *other* `IMPLEMENTATION-ROADMAP-2026-11-18.md`) and explicitly left this pair, the CSS fix and the browser specs uncommitted in the shared worktree. §10 records the measured slice state at that HEAD.
**Driving audit:** `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md` (AUD-20261118-001).
**Pair document:** `docs/plan/plan-ui-ux-implementation-2026-11-18.md` owns commands, risks and acceptance detail.
**Authority order:** `AGENTS.md` §2 / §2a; `GOVERNANCE.md` §CONSTRAINTS C-1…C-9; `SCREEN_SPEC.md`; `DESIGN.md`; this roadmap.
**No new package, no new dependency, no schema change, no new root document.** Every slice stays inside the existing Laravel 13 / Inertia / Vue 3 / Tailwind v4 stack.

---

## 1. What this roadmap is for

Turn AUD-20261118-001 into a **verified, working frontend outcome**, not another report. The audit's three findings were written against a mix of dated screenshots and an earlier defect register. At this HEAD, all three are **already fixed in source**; they are excluded from execution (§3). The one genuine open reflow defect on the audited surface is **KI-166** (Race Decision, 59 px overflow at 320 px), which the audit carried only as an unmeasured risk. This roadmap therefore starts by re-deriving truth at HEAD, closes KI-166 with a regression test, sweeps the remaining screens for the same class of defect, reconciles the audit's documentation recommendations, and ends with measured gates.

**Final outcome:** every affected page holds the WCAG 1.4.10 reflow width with no document-level sideways scroll; the audit's stale claims are corrected in place; the framework instrument can be used without inventing rule IDs; and the whole result is evidenced by quoted command output.

---

## 2. Current state (measured this session)

| Check | Result | Evidence |
|---|---|---|
| Audit location | `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md` (moved from `docs/research-scratch/`) | `find docs -iname '*UI-UX-PRODUCT-AUDIT*'` |
| F-01 mobile navigation | **Already fixed.** `AppLayout.vue:234-235` splits four `inBar` destinations plus a `<details>` More disclosure; fixed since `d886d140` (2026-11-11) and `3f05138a` (2026-11-03). The flat ten-item strip exists only in the 2026-11-15 screenshots. | `AppLayout.vue:194-224, 640-700`; `git log -- AppLayout.vue` |
| F-02 Cockpit 640 px overflow | **Already fixed.** `NextDecision.vue:346-348` wraps the Obligations table in `overflow-x-auto`; KI-160's Cockpit half is resolved, and the Races half was split into KI-166. | `NextDecision.vue:346`; `KNOWN-ISSUES.md:4953`, `:5328` |
| F-03 reduced motion | **Already fixed.** Global `@media (prefers-reduced-motion: reduce)` with `animation-duration`, `iteration-count`, `transition-duration` and `scroll-behavior` overrides at `app.css:943-951`. The audit's `resources/js`-only grep missed CSS. | `app.css:939-951`; `git log -- app.css` |
| Genuine open reflow defect | **KI-166 OPEN.** `/training-runs/{id}/races` overflows 59 px at 320×900. Probe refuted the original `table.t` candidate; owner ruling says the empty `culprits` list was a probe defect. No reflow case exists in `career-race-decision.spec.ts`. | `KNOWN-ISSUES.md:5328-5395` |
| Browser harness | **Works** after `npm run build`. Session-private smoke test passed: `1 passed (37.3s)` on `accessibility.spec.ts` landing case, port 8318. | `.scratch-uma/uiux-2026-11-18/` |
| Audit-instrument rule IDs | **Still broken.** `SHR-8` appears 5 times in `_audit-prompt.md`; `UX-11…29`, `UI-8…13`, `TOG-9…16`, `GP-6…10`, `IA-7…8`, `NRM-7…8` also cited. Actual maxima: UX-10, UI-7, SHR-7, TOG-8, GP-5, IA-6, NRM-6. | `docs/framework-ui-ux/_audit-prompt.md:117,165,182,443,470` |
| SCREEN_SPEC registry gap | **Still open.** No rows for `runs.settings`, `runs.confirm-delete`, `runs.turns.edit`, `review.detail`, `veterans.transfers`, `database.trainees/supports/skills`, `database.races.show`. | `grep` on `SCREEN_SPEC.md` returns 0 |
| Existing roadmaps/plans | None reference AUD-20261118-001 or its findings. Older claims about KI-160 conflict with the current register; do not inherit them. | full-text grep across `docs/roadmap`, `docs/plan` |

**Working-tree rule:** the tree carries uncommitted peer work in `types.ts`, four scenario/goal components, two browser specs and several root docs. Every slice names explicit pathspecs; no `git add -A`, no `pint --dirty`, no unqualified browser run.

---

## 3. Explicitly excluded — do not re-implement

| Audit item | Why excluded | Reopen only if |
|---|---|---|
| F-01 mobile banding | Current source already renders four destinations + More; the finding used a stale rendered frame | A fresh 390 px frame shows the flat strip again |
| F-02 Cockpit 640 px overflow | `NextDecision.vue` scroller landed; KI-160 Cockpit half resolved | `career-race-strip.spec.ts:203` reflow case fails at 640 px |
| F-03 reduced motion | Global CSS rule exists at `app.css:943` | An emulated-`reduce` browser run shows a transition |
| Slice 5.4 UI debt / Slice 5.8 MWG remediations | Already scheduled in `IMPLEMENTATION-ROADMAP-2026-11-18.md:305,309`; duplicating would widen scope | Owner re-baselines that roadmap |
| KI-163 goal-shape unification | Peer work already in the dirty tree (`types.ts` + four components) | Typecheck fails on those pathspecs |

---

## 4. Phases and slices

### Phase 0 — Re-derive truth at HEAD

| # | Slice | Verdict | Deliverable | Acceptance |
|---|---|---|---|---|
| 0.1 | Audit finding reconciliation | **DONE** (this session) | §2 table above with source locations and commit dates | Each of F-01/F-02/F-03 has a current-source verdict, not a screenshot-only verdict |
| 0.2 | Full-surface reflow and axe sweep | OPEN | A session-private probe spec + measured table for the page-rendering routes at 320×900 and 390×844; axe result on the same set | Every route has a `scrollWidth - clientWidth` reading; each overflow has a named element before any fix is attempted |
| 0.3 | Baseline gates | OPEN | Quoted output of `npm run typecheck`, `npm run lint:ui`, `npm run build` | All three green on the unmodified tree; pre-existing failures recorded, not fixed in passing |

**Dependencies:** 0.2 depends on `npm run build` (harness freshness check at `global-setup.ts:203`). Nothing downstream starts before 0.2 produces named offenders.

### Phase 1 — Close the confirmed reflow defect

| # | Slice | Verdict | Deliverable | Acceptance |
|---|---|---|---|---|
| 1.1 | KI-166 mechanism probe | OPEN | Diagnostic that walks **up** from `document.documentElement`, comparing each box's `scrollWidth` to `clientWidth`, and prints the deepest unclipped overflow with `tag`, `className`, nearest `[data-region]`, `right`, `width`, `clientWidth`, `scrollWidth`, computed `overflow-x` | The 59 px is attributed to a named element or a named scroll interaction; the probe does not repeat the refuted `right > clientWidth` filter |
| 1.2 | Smallest layout fix | OPEN | Minimal change in `RaceDecision.vue` and/or `app.css`; no new component, no design-token change | 320×900 and 640×512 document overflow ≤ 1 px on `/training-runs/{id}/races`; 768 and 1280 unchanged; no horizontal scroll on the table's own card beyond its declared scroller |
| 1.3 | Regression coverage | OPEN | A 320 px reflow case in `tests/browser/career-race-decision.spec.ts` using the existing `openDecision` fixture | New case fails if the fix is reverted; passes with it; `career-race-planner.spec.ts:119` and `career-race-strip.spec.ts:203` remain green |

**Dependencies:** 1.2 depends on 1.1; 1.3 depends on 1.2. No fix is written from a guess about the mechanism.

### Phase 2 — Anything else the sweep finds

| # | Slice | Verdict | Deliverable | Acceptance |
|---|---|---|---|---|
| 2.1 | Fix each additional confirmed defect | OPEN (conditional) | One smallest fix per named offender from 0.2 | Same ≤ 1 px reflow rule; no unmeasured claim promoted to a finding |
| 2.2 | Extend coverage per fixed surface | OPEN (conditional) | One browser case per fixed surface | Each case is red against the pre-fix tree or explicitly marked as coverage-only |

**Dependencies:** 2.x depends on 0.2 and runs after Phase 1. If 0.2 finds nothing beyond KI-166, both rows close as `NO FINDING` with the measured table, not silently dropped.

### Phase 3 — Reconcile the audit's documentation recommendations

| # | Slice | Verdict | Deliverable | Acceptance |
|---|---|---|---|---|
| 3.1 | Audit erratum | OPEN | Dated erratum appended to `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md` preserving the original sentences and recording the three resolved findings with source evidence | Original findings remain readable; erratum states HEAD and date; no silent rewrite |
| 3.2 | Framework rule IDs | OPEN | `_audit-prompt.md` §4/§10/§11 corrected to the derived IDs that exist; the two framework typos fixed | Every cited ID resolves in its framework file; `grep` for `SHR-8` returns 0 |
| 3.3 | SCREEN_SPEC registry | OPEN | §3 rows and §4 state tables for the seven unregistered route groups | Route-to-matrix cross-reference reaches 100%; no page without a row |

**Dependencies:** 3.1 depends on 0.1; 3.2 and 3.3 are independent of Phase 1 but must not be used to justify a frontend change.

### Phase 4 — Verify and hand off

| # | Slice | Verdict | Deliverable | Acceptance |
|---|---|---|---|---|
| 4.1 | Frontend gates | OPEN | Quoted output of typecheck, lint:ui, build, and targeted browser specs | All green or every failure classified pre-existing/unrelated with evidence |
| 4.2 | Interaction verification | OPEN | Per-page result table for every affected page and interaction: reflow, keyboard reach, status announcement, form write | Each row cites a command and output; unrun rows named as unrun |
| 4.3 | Register + hand-off | OPEN | KI-166 state line corrected with measured output; this roadmap's verdicts updated; final report | No open slice remains without an owner or an explicit owner decision |

**Dependencies:** 4.x depends on Phases 1–3.

---

## 5. Slice execution order

```
0.1 (done) → 0.2 → 0.3
                 │
                 └──→ 1.1 → 1.2 → 1.3
                              │
                              └──→ 2.1 → 2.2 (conditional)
                                             │
3.1 ← 0.1        3.2        3.3             │
       └──────────────┴─────────────────────┘
                              ↓
                         4.1 → 4.2 → 4.3
```

---

## 6. Verification commands (house style)

Every browser invocation is session-private and named; no unqualified run.

```bash
# Session identity
SESSION=uiux-2026-11-18
PORT=8318
DB=.scratch-uma/$SESSION/browser-$PORT.sqlite

# Fresh assets (required by global-setup.ts:203)
npm run build

# Frontend gates
npm run typecheck
npm run lint:ui
python tools/md_fence_check.py <changed markdown paths>

# Reflow + axe sweep and targeted specs
PLAYWRIGHT_PORT=$PORT PLAYWRIGHT_SCRATCH_DB=$DB \
  npx playwright test <spec paths> --workers=1 --reporter=line \
  --output=.scratch-uma/$SESSION/results < /dev/null

# Never: unqualified `npx playwright test`, `pint --dirty`,
# background writing commands while peers work, or writing outside this repository.
```

---

## 7. Risks and dependencies

| Risk | Effect | Mitigation |
|---|---|---|
| Audit findings are stale | A slice fixes an already-fixed thing | 0.1 verdicts are source-based and recorded before execution |
| KI-166 cause is a scroll extent, not an escaping child (KI-166 dated note) | The obvious `overflow-x-auto` wrapper will not fix it | 1.1 walks up the box tree; no fix before a named offender |
| Peer edits in `types.ts` and four components | A frontend edit could overwrite uncommitted work | `git diff HEAD -- <path>` before each edit; explicit pathspecs only |
| Browser harness DB sharing | A leaked server or deleted peer DB | Session-private `PORT`/`DB`; `reuseExistingServer: false` is already enforced |
| DocCitationParityTest pre-existing red (KI-106) | A new doc may appear to add failures | Measure before and after; new docs carry no dead citations |
| Scope widening into scheduled UI debt | Duplicate work with `IMPLEMENTATION-ROADMAP-2026-11-18.md` | §3 exclusion list is binding unless the owner re-baselines |

---

## 8. Owner decisions owed

None for Phases 0–2. Phase 3 changes audit/framework documents only; Phase 4 closes a defect row. Escalate only if the 0.2 sweep finds a defect whose fix needs a schema change, a new dependency, or a design-token change (all outside this roadmap's authority).

---

## 9. Change log

| Date | Change | Reason |
|---|---|---|
| 2026-11-18 | Created; re-baselined at `2546689e`; three audit findings excluded as already fixed; KI-166 made the primary execution target | AUD-20261118-001 needs implementation, but its findings predate current source |
| 2026-11-18 | Execution re-baselined at `cfd8bd78`; §10 records measured verdicts for every slice | Peer commit landed one commit after the pair was written; all execution evidence needed a current HEAD |

---

## 10. Execution reconciliation at `cfd8bd78` (2026-11-18)

Every verdict below is an observation at HEAD `cfd8bd78a395eaf4e38b84929cd82965ab173719` with the
working-tree changes named in §2. No slice is left in `OPEN` without a decision.

| Slice | Verdict | Evidence |
|---|---|---|
| 0.1 finding reconciliation | DONE | Audit §11 dated erratum; F-01/F-02/F-03 all resolved in source before this pass |
| 0.2 full-surface sweep | DONE (partial coverage) | `.scratch-uma/uiux-2026-11-18/probe.log`: 29 routes at 390 px all `overflow: 0`; 32 routes at 320 px, only KI-166 `59` and planner `40` nonzero, Cockpit `0`. The remaining page-rendering routes were not probed and stay unmeasured. |
| 0.3 baseline gates | DONE | `npm run build` exit 0; `npm run typecheck` exit 0; `npm run lint:ui` exit 0 with 516 report-only baseline findings |
| 1.1 KI-166 mechanism | DONE | `probe.log` box dump: absolute `sr-only` spans with `right: 379` under a static ancestor chain; `culprits.log` names the exact spans |
| 1.2 smallest layout fix | DONE | `resources/css/app.css` `.card:has(> table.t)` gains `position: relative`; `fixcheck.log` A/B `59→0` and `40→0` |
| 1.3 regression coverage | DONE | `career-race-decision.spec.ts:302` KI-166 case green in the final 33-case run |
| 2.1 / 2.2 additional defects | DONE | Race Planner's same-class 40 px overflow fixed by the same rule; `career-race-planner.spec.ts:115` rewritten to an explicit delta and green |
| 3.1 audit erratum | DONE | Audit §11 preserves §5 verbatim and records the three resolutions with source locations |
| 3.2 framework rule IDs | DONE | `_audit-prompt.md` modified, `grep -c 'SHR-8'` → 0; two framework typos corrected |
| 3.3 SCREEN_SPEC registry | DONE | Nine routes registered (`SCR-CAR-030…032`, `SCR-REV-002`, `SCR-VET-005`, `SCR-SYS-013…016`) with §4 state rows; matrix 66 rows, `md_fence_check` 0 |
| 4.1 frontend gates | DONE | Same three gates above plus the final browser run: `31 passed, 2 skipped, 0 failed` (port 8334, session-private DB) |
| 4.2 interaction verification | DONE | The 33-case run covers axe scans, keyboard paths, the 44 px floor, reduced motion, focus restoration, form writes and reflow on the affected surfaces; §6's remaining unmeasured claims stay unmeasured |
| 4.3 register + hand-off | DONE | KI-166 state line corrected with measured values below; this section is the hand-off record |

Two pre-existing stale assertions in `tests/browser/accessibility.spec.ts` were corrected rather than
dropped: the unnamed `progressbar` locator now names "Page load progress" (the Review page's own
"Review progress" bar is legitimate), and the removed "Facts and mechanics" disclosure guard now
asserts `[data-scenario-card]` so the scan cannot pass on an empty step. Both were reproduced at
`8332` (2 failed) and verified green at `8333` (2 passed) before the full re-run.

**Not measured, explicitly retained:** the audit's §6 contrast, target-size, focus-visibility and
keyboard-pass claims are unchanged by this pass. No WCAG conformance verdict is claimed.
## 11. Second browser pass at `816e923e` (2026-11-18)

A driven-browser pass in Chromium against `127.0.0.1:8000`, after the first pass's slices were closed.
Detail and provenance labels are in `docs/audits/uiux-comprehensive-walk-2026-11-18/07-browser-verified-2026-11-18.md`.

| Slice | Verdict | Evidence |
|---|---|---|
| 4.1 full route sweep | DONE | 54 routes at 1440x900 and 48 at 390x844; 0 console errors, 0 failed requests, exactly one `h1` per page. Two 404s were probe error (no `/trainees` route, `/umamusume` takes a slug), and the 404 page renders correctly. |
| 4.2 overflow class closed | DONE | `/legacy` measured `41` px of document overflow at 390 px from an `sr-only` span escaping a `position: static` scroller. KI-166's rule required the table to be a direct child of `.card`, which this page is not. `:where(.card, [class*="overflow-x-auto"]):has(table)` closes the class; re-measured `0`, wrapper still scrolls. Post-fix sweep of 48 routes: 0 overflow. |
| 4.3 tile suffix separator | DONE | `Tile.vue` announced `1of 72`; `ml-1` spaced it visually only. Now `1 of 72`, and `dashboard.spec.ts:275` no longer pins the defect. |
| 4.4 scenario card hit area | DONE | Probe grid measured 1/12 points inside the card (4% of 561x216). The card now takes the click and skips interactive children; 12/12 measured, selection persisted across reload. |
| 4.5 preflight dead end | DONE | The acknowledgement that enables `Create the run` sat 952 px below the button with no explanation at the point of use. A `[data-ack-hint]` sentence now sits in the head band, gated on `canStart` so it never competes with `startBlocker`'s own sentence. |
| 4.6 regression coverage | DONE | `tests/browser/browser-walk-2026-11-18.spec.ts`, 3 cases, green. |
| 4.7 pre-existing red recorded | DONE | `career-scenario-select.spec.ts:147` fails identically at HEAD with this work reverted. Filed as KI-167; not fixed, root cause not established. |

**Cleared as false positives in the same pass:** bottom-nav clipping the CTA (nav 45 px, `main`
reserves 96 px, content ends above it), the empty cockpit being a dead end (it carries a "Record the
first turn through Training" link), a "future" verification date (2026-11-02 is 16 days past), and the
Dashboard's repeated absence wording (required by `Tile.vue` and `DESIGN.md` §5). Vision-model verdicts
in this pass were wrong four times and are treated as leads only.

