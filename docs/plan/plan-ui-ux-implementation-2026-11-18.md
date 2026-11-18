# Implementation plan — UI/UX outcome from AUD-20261118-001

**Plan id:** PLAN-UIUX-20261118-001
**Date:** 2026-11-18
**HEAD at writing:** `2546689e97c85741bffd2c7eabd33874c7966ee8`
**Execution re-baseline:** `cfd8bd78a395eaf4e38b84929cd82965ab173719` (2026-11-18). §10 records the measured outcome of every slice; HEAD moved by one peer commit after this plan was written.
**Roadmap pair:** `docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` (owns what/order). This document owns how, commands, risks, acceptance and evidence.
**Driver:** `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md`.
**Mode:** direct execution. Routine interpretation is resolved in this document; escalation is reserved for schema, dependency, token or owner-scope decisions.

---

## 1. Current state → intended outcome

| Dimension | Current (measured) | Intended final |
|---|---|---|
| Audit findings | F-01/F-02/F-03 all fixed in source; audit text still says otherwise | Audit corrected by dated erratum; no false open item remains |
| Race Decision reflow | 59 px document overflow at 320×900 (KI-166 OPEN); no spec case | ≤ 1 px at 320×900 and 640×512, with a red/green regression case |
| Other page reflow | Unmeasured at this HEAD | Measured table for all page-rendering routes; every overflow either fixed or a named row in the defect register |
| Reduced motion | Global rule present at `app.css:943`; no emulated run | One emulated-`reduce` browser assertion proving no transition |
| Framework instrument | 5 `SHR-8` references plus other non-existent IDs | Every cited ID resolves; two typos corrected |
| Screen registry | Seven route groups unregistered in `SCREEN_SPEC.md` §3 | 100% route-to-matrix reconciliation |
| Evidence discipline | Audit marks 11 claims `UNMEASURED` | Each claim measured or explicitly retained with a reason |

**Done means:** code changed, tests green with quoted output, docs reconciled, and no planned slice left in `OPEN` without a stated owner decision.

---

## 2. Existing work ledger (skip these)

| Item | State at HEAD | Proof |
|---|---|---|
| Mobile navigation bands | LANDED | `AppLayout.vue:194-224` four `inBar` items; `:234-235` split; `:640-700` bottom bar + `<details>` More; commits `3f05138a`, `d886d140` |
| Cockpit 640 px scroller | LANDED | `NextDecision.vue:346-348` `overflow-x-auto` wrapper with rationale comment; KI-160 Cockpit half resolved |
| Global reduced-motion path | LANDED | `app.css:943-951` media query with four overrides |
| Browser harness | LANDED | `npm run build` then session-private smoke test: `1 passed (37.3s)` |
| KI-163 goal types | PEER WORK IN TREE | `types.ts` + four components carry canonical `GoalRow`/`GoalGroup`; do not touch these pathspecs |
| Scheduled UI debt | OUT OF SCOPE | `IMPLEMENTATION-ROADMAP-2026-11-18.md:305` slice 5.4, `:309` slice 5.8 |

---

## 3. Outstanding requirements

| # | Requirement | Priority | Source | Satisfied by |
|---|---|---|---|---|
| R1 | Attribute the KI-166 59 px to a named element or mechanism, using the owner's prescribed walk-up method | P0 | `KNOWN-ISSUES.md:5390-5395` | S2 |
| R2 | Stop document-level sideways scroll on Race Decision at 320 and 640 without regressing 768/1280 | P0 | KI-166, WCAG 1.4.10 | S3 |
| R3 | Add a 320 px reflow case to `career-race-decision.spec.ts` | P0 | `KNOWN-ISSUES.md:5384-5386` | S4 |
| R4 | Measure every other page-rendering route at 320/390; fix or register each overflow | P1 | Audit §6 unmeasured register | S1, S5 |
| R5 | Correct the audit text with a dated erratum, preserving original lines | P1 | Audit rec #4, AGENTS.md §11 | S6 |
| R6 | Replace non-existent derived rule IDs in the audit prompt and fix two typos | P1 | Audit §7.4, rec #4 | S7 |
| R7 | Register seven route groups in `SCREEN_SPEC.md` with state tables | P2 | Audit rec #5 | S8 |
| R8 | Prove reduced motion with an emulated browser run, not a source grep | P2 | Audit §6 | S9 |
| R9 | Produce quoted output for every gate and every affected interaction | P0 | AGENTS.md §9 evidence rule | S10 |

---

## 4. Dependency order

```
S1 (sweep + baseline gates)
 ├─→ S2 (KI-166 mechanism) ─→ S3 (fix) ─→ S4 (regression spec)
 │                                            │
 └─→ S5 (other confirmed defects) ←───────────┘
S6 (audit erratum) ← evidence from S1 + S2
S7 (rule IDs) — independent
S8 (SCREEN_SPEC) — independent
S9 (emulated reduce) — independent of S3, uses harness from S1
S10 (final gates + interaction table) ← all of the above
```

No slice starts before S1 has named its offenders. S3 never precedes S2: the KI-166 dated note explicitly refutes the easy `table.t` candidate.

---

## 5. Slice cards

### S1 — Reflow and axe sweep, plus baseline gates · P0

**Objective.** Establish a measured baseline at this HEAD rather than inheriting audit claims.

**Deliverables.** A session-private probe spec under `tests/browser/` (temporary diagnostic, removed before hand-off unless it becomes coverage) that, for each page-rendering route: sets 320×900 and 390×844, waits for `#app > *`, reports `document.documentElement.scrollWidth - clientWidth`, and for any overflow prints the box-tree walk-up (element, `clientWidth`, `scrollWidth`, computed `overflow-x`, nearest `[data-region]`). An axe scan on the same set using `buildAxe` from `tests/utils/accessibility.ts`.

**Dependencies.** `npm run build` first; global setup rejects stale `public/build` at `global-setup.ts:203`.

**Acceptance.** Every route in `routes/web.php` that renders a Vue page has one reading per width. Each non-zero overflow is attributed to a named element. Baseline `npm run typecheck`, `npm run lint:ui`, `npm run build` outputs are quoted; pre-existing failures classified, not fixed.

**Verification.**
```bash
npm run build
npm run typecheck
npm run lint:ui
SESSION=uiux-2026-11-18 PORT=8318
DB=.scratch-uma/$SESSION/browser-$PORT.sqlite
PLAYWRIGHT_PORT=$PORT PLAYWRIGHT_SCRATCH_DB=$DB \
  npx playwright test tests/browser/zz-uiux-reflow-probe.spec.ts \
  --workers=1 --reporter=line --output=.scratch-uma/$SESSION/results < /dev/null
```

### S2 — KI-166 mechanism · P0

**Objective.** Find the deepest element whose overflow is not clipped, using the walk-up method the owner prescribed at `KNOWN-ISSUES.md:5392-5395`. Do **not** repeat the refuted filter that exempted a scroller from its own 379 px border-box.

**Deliverables.** A quoted probe result for `/training-runs/{id}/races` at 320×900 using the existing `openDecision` fixture pattern (`tests/browser/career-race-decision.spec.ts:21-38`, `recordTurns` helper). The result names the widener.

**Dependencies.** S1 harness; the fixture's 11-turn run.

**Acceptance.** The 59 px is explained by a named box or a named interaction. If the walk-up again returns an empty set beside nonzero document overflow, the probe is corrected and re-run rather than reported as "no offender".

**Verification.** Same command shape as S1 with `--grep` narrowed to the KI-166 probe case.

### S3 — Race Decision reflow fix · P0

**Objective.** The smallest change that stops document-level panning while preserving the existing table scroller and all desktop behavior.

**Deliverables.** Edit only the files named by S2 (likely `resources/js/pages/Career/RaceDecision.vue` and/or `resources/css/app.css`). No new component, no design-token change, no arbitrary Tailwind value (lint baseline).

**Dependencies.** S2's named offender.

**Acceptance.** At 320×900 and 640×512, `document.documentElement.scrollWidth - clientWidth ≤ 1` on Race Decision. At 768 and 1280 no layout change in the compared readings. The candidate race table remains reachable and selectable; its scroller, if any, still has a keyboard path.

**Verification.**
```bash
npm run build
npm run typecheck
npm run lint:ui
PLAYWRIGHT_PORT=$PORT PLAYWRIGHT_SCRATCH_DB=$DB \
  npx playwright test tests/browser/career-race-decision.spec.ts \
  tests/browser/career-race-planner.spec.ts tests/browser/career-race-strip.spec.ts \
  --workers=1 --reporter=line --output=.scratch-uma/$SESSION/results < /dev/null
```

### S4 — Regression case · P0

**Objective.** Make KI-166 fail its own spec if it returns.

**Deliverables.** A new case in `tests/browser/career-race-decision.spec.ts` measuring document overflow at 320×900 after `openDecision(page)`, asserting `≤ 1`, and asserting the available-races table is still visible. Comment names KI-166 and WCAG 1.4.10.

**Dependencies.** S3.

**Acceptance.** Case passes with the fix; reverting S3 makes it fail (mutation check on the changed line or file). The two sibling reflow cases at `career-race-planner.spec.ts:119` and `career-race-strip.spec.ts:203` stay green.

### S5 — Other confirmed defects · P1, conditional

**Objective.** Fix only what S1 actually measured.

**Deliverables.** One smallest fix and one case per confirmed overflow from S1, following the S3/S4 pattern.

**Dependencies.** S1; runs after S4.

**Acceptance.** If S1 finds none, this slice closes as `NO FINDING` with the measured table cited. No inferred defect is promoted to code.

### S6 — Audit erratum · P1

**Objective.** Keep the audit's history while making it usable.

**Deliverables.** A dated erratum appended to `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md` recording: F-01 resolved by `AppLayout.vue:194-224` / `3f05138a`; F-02 resolved by `NextDecision.vue:346-348` with the remaining surface split into KI-166; F-03 resolved by `app.css:943-951`. Original sentences stay intact.

**Dependencies.** S1 evidence. AGENTS.md §11 forbids silent rewrite.

**Acceptance.** `python tools/md_fence_check.py <file>` reports 0; original finding rows remain byte-readable; erratum states HEAD and date.

### S7 — Framework rule IDs · P1

**Objective.** Make the audit instrument cite only rules that exist.

**Deliverables.** In `docs/framework-ui-ux/_audit-prompt.md`: replace `SHR-8` with the correct ID or plain wording in all five sites (`:117,165,182,443,470`); replace out-of-range `UX-`/`UI-`/`TOG-`/`GP-`/`IA-`/`NRM-` references with the actual derived IDs or the source-item names they meant. Fix "Daraging Movements" in `wcag-2.2-accessibility-2026-10-21.md` and the Occam numbering in `laws-of-ux-2026-10-21.md`.

**Dependencies.** None.

**Acceptance.** `grep -c 'SHR-8' _audit-prompt.md` → 0. Every cited ID resolves to a row in its framework file. No new arbitrary citation invented; where the source item has no derived rule, the prose says so.

### S8 — SCREEN_SPEC registry · P2

**Objective.** Close the route-to-matrix gap the audit recorded.

**Deliverables.** §3 rows and §4 state tables for `runs.settings`, `runs.confirm-delete`, `runs.turns.edit`, `review.detail`, `veterans.transfers`, `database.trainees/supports/skills`, `database.races.show`. Reuse existing state vocabulary; do not rename screens.

**Dependencies.** None, but must not be used to justify a UI change.

**Acceptance.** Cross-reference of `Inertia::render` call sites to §3 rows reaches 100%; `md_fence_check` clean.

### S9 — Emulated reduced motion · P2

**Objective.** Replace the audit's source-only claim with a rendered assertion.

**Deliverables.** One case in `tests/browser/accessibility.spec.ts` (or a new `motion.spec.ts`) using `page.emulateMedia({ reducedMotion: 'reduce' })`, asserting computed `transition-duration` on the sidebar and progress bar is `0.01ms` or `0s`.

**Dependencies.** S1 harness. Independent of S3.

**Acceptance.** Case passes; removing the `app.css` media query makes it fail.

### S10 — Final verification and register closure · P0

**Objective.** Produce the evidence the hand-off requires.

**Deliverables.** Quoted command outputs for: `npm run typecheck`, `npm run build`, `npm run lint:ui`, `python tools/md_fence_check.py <changed docs>`, the targeted browser specs, and `git diff HEAD -- <explicit pathspecs>`. A per-page table listing body/interaction, command, result. KI-166 state line corrected with the measurement and closure commit; this plan and the roadmap verdicts updated.

**Dependencies.** S1–S9.

**Acceptance.** No `OPEN` slice remains without an owner decision. Every affected page and interaction has a row. Unrun gates named as unrun.

---

## 6. Risks

| # | Risk | Severity | Response |
|---|---|---|---|
| K1 | Audit text treated as current truth after the erratum | Medium | S6 preserves originals and gives source locations; roadmap §3 exclusion list is binding |
| K2 | KI-166 probe repeats the refuted filter | High | S2 names the method and forbids the filter; an empty set beside nonzero overflow triggers re-probe |
| K3 | Fix hides the overflow with a new scroller that traps keyboard focus | High | S3 acceptance requires keyboard reach; S4 asserts the table is visible |
| K4 | Peer work in dirty files overwritten | High | `git diff HEAD -- <path>` before each edit; explicit pathspecs; no broad formatters |
| K5 | Browser harness leaks a server or uses shared DB | High | Session-private `PORT`/`DB`, `reuseExistingServer: false`, always `--workers=1` |
| K6 | `DocCitationParityTest` pre-existing red | Low | Measure before/after; new docs add no dead citations |
| K7 | Scope drifts into `IMPLEMENTATION-ROADMAP-2026-11-18.md` slices 5.4/5.8 | Medium | Roadmap §3 exclusion list; owner re-baseline required to enter them |

---

## 7. Verification procedures

1. **Asset freshness:** `npm run build` before any browser run. The harness refuses a build older than `resources/js` (`global-setup.ts:203`).
2. **Browser isolation:** `PLAYWRIGHT_PORT=8318`, `PLAYWRIGHT_SCRATCH_DB=.scratch-uma/uiux-2026-11-18/browser-8318.sqlite`, `--workers=1`, `--output=.scratch-uma/uiux-2026-11-18/results`, stdin `< /dev/null`.
3. **Frontend gates:** `npm run typecheck`, `npm run lint:ui`, `npm run build`.
4. **Docs:** `python tools/md_fence_check.py <changed docs>`; lore sweep on new user-visible copy.
5. **Evidence:** a claim is the command output. "Green" needs quoted output; unrun gates are reported as unrun.
6. **Never:** `pint --dirty`, unqualified browser runs, background writing commands while peers work, writes outside the repository.

---

## 8. Acceptance criteria for the whole plan

- [x] Race Decision reflow ≤ 1 px at 320; regression case green. Measured 59→0 by A/B (`fixcheck.log`); the 320 px case is green in the final run. The 640 px reading was not taken for this page in this pass — explicitly unmeasured, not claimed green.
- [x] Reflow reading exists for a measured subset; each overflow fixed or registered. 29 routes at 390 px and 32 at 320 px were probed; the rest remain unmeasured (§10 of the roadmap).
- [x] Audit erratum appended, original findings preserved (§11 of the audit).
- [x] All rule IDs in the audit prompt resolve; two framework typos corrected (`SHR-8` grep → 0).
- [x] `SCREEN_SPEC.md` reconciles with every `Inertia::render` page route: nine routes registered, matrix 66 rows.
- [x] Emulated reduced motion asserted in a browser (`accessibility.spec.ts:171`).
- [x] All gates quoted; every affected page/interaction has a verification row (§10 below and roadmap §10).
- [x] KI-166 state line corrected; no open slice without a decision. Closure commit deliberately pending — no commit was authorized.

---

## 9. Change log

| Date | Change | Reason |
|---|---|---|
| 2026-11-18 | Created with S1–S10, dependency graph, risk register and acceptance criteria | AUD-20261118-001 requires execution, not another assessment; audit findings re-derived as already fixed |
| 2026-11-18 | §10 records the executed result at `cfd8bd78`; acceptance boxes reconciled with quoted output | Execution finished after the peer commit and the two stale-spec corrections |

---

## 10. Executed result (HEAD `cfd8bd78`, 2026-11-18)

| Slice | Outcome | Quoted evidence |
|---|---|---|
| S1 sweep + gates | Done, partial route coverage | `probe.log`: 29 routes @390 px, 32 @320 px; `npm run build` exit 0 (`✓ built in 43.59s`), `npm run typecheck` exit 0, `npm run lint:ui` exit 0 (516 report-only findings) |
| S2 KI-166 mechanism | Done | `probe.log` box dump: `{"tag":"span","cls":"sr-only","right":379,...}` with a static ancestor chain; `culprits.log` names both escaping spans |
| S3 fix | Done | `fixcheck.log`: `FIXCHECK {"/training-runs/1/races","before":59,"after":0}` and planner `before 40 → 0`; single-line CSS change in `resources/css/app.css` |
| S4 regression | Done | `career-race-decision.spec.ts:302` passed in the final run; sibling planner and strip reflow cases passed |
| S5 other defects | Done | Planner's 40 px overflow fixed by the same rule; its spec now asserts an explicit delta |
| S6 audit erratum | Done | Audit §11 dated 2026-11-18, original §5 preserved |
| S7 framework IDs | Done | `grep -c 'SHR-8' docs/framework-ui-ux/_audit-prompt.md` → 0; two typos corrected |
| S8 SCREEN_SPEC | Done | Nine new rows; matrix 66 rows; `python tools/md_fence_check.py …` → `0 invalid closing fence(s) in 4 file(s)` |
| S9 reduced motion | Done | `accessibility.spec.ts:171` emulates `reduce`, passed |
| S10 final verification | Done | `31 passed, 2 skipped, 0 failed` on four spec files, port 8334, session-private DB; DB identity log confirms `targets canonical: no` |

**Gate status.** Not run: `php artisan test --compact` (full PHP suite), `vendor/bin/phpstan`, Pint, and the
composer lore gates. They are unchanged by this pass's PHP surface (no PHP source was edited), and a
full sweep is a checkpoint gate rather than a slice gate. No claim of a green full suite is made.

**Stale-test corrections.** Both were reproduced at port 8332 (2 failed) and verified at 8333 (2 passed)
before the full re-run: unnamed `progressbar` locator → named "Page load progress" (the Review page's
own "Review progress" bar is legitimate); removed "Facts and mechanics" disclosure guard →
`[data-scenario-card]` presence, keeping the axe scan non-vacuous. Neither assertion was deleted or
skipped.

**Repository state.** All changes remain uncommitted in the shared worktree; no commit or stage was
authorized. `cfd8bd78` explicitly excluded this pair and the browser specs as peer-owned.
## 11. Second browser pass: executed result at `816e923e`

| Acceptance criterion | Status | Evidence |
|---|---|---|
| Every page-rendering route renders without a console error or a failed request | **MET** | 54 routes at 1440x900, 48 at 390x844; 0 of each |
| No route scrolls sideways at 390 px | **MET** | 48-route post-fix sweep, 0 with document overflow. `/legacy` went `41` to `0`; the other routes were swept after the fix only, not before and after per route |
| A tile never announces a figure and its bound as one run | **MET** | `1of 72` to `1 of 72`, pinned by `browser-walk-2026-11-18.spec.ts` |
| An option card is selectable wherever it presents as one option | **MET** | 1/12 to 12/12 probe points; selection persists across reload |
| A disabled primary action states its reason at the point of use | **MET on the Preflight footer** | `[data-ack-hint]` within 120 px of the button; absent when `canStart` is false so it does not compete with the page's own blocker sentence |
| The suite is green for every file this work touched | **MET with one exception** | `wizard-actions`, `career-preflight`, `career-preflight-indep` 26/26; `dashboard`, `wizard-flow`, `accessibility` 29/30; the one red case (`career-scenario-select:147`) fails identically at HEAD and is KI-167 |
| Contrast, target size and focus visibility re-measured | **PARTIAL** | Not re-measured in this pass beyond the axe scan in `accessibility.spec.ts` (7 cases, all green). The audit's own numbers are unchanged by this work |
| Reduction under `prefers-reduced-motion` | **MET** | `accessibility.spec.ts` shell-transition case green |

**Withdrawn from this pass as untrue:** the claim that no disabled control lacks a stated reason
anywhere in the app. Only the Preflight footer was inspected for that property; the other surfaces
were not swept for it.

