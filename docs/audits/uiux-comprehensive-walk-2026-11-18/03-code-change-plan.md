# 03 — Code-change plan

## Implemented slice — navigation focus continuity (BO-001)

- Files: `resources/js/spa.ts`, `tests/browser/navigation-focus.spec.ts`.
- Objective: give persistent-shell page changes the same heading-focus signal the wizard already gives its steps, without stealing focus from in-page work.
- Acceptance: destination `h1` focused after a component-changing Inertia visit; same-page filter keeps focus in the form; validation responses keep their own alert focus; wizard and race-decision focus specs remain green.
- Verification: `npm run build` exit 0; `npm run typecheck` exit 0; `npm run lint:ui` exit 0; browser run at port 8402 `14 passed, 2 skipped, 0 failed`; mutation at 8403 `1 failed, 1 passed` with the listener removed; source restored and rebuilt.
- Risk addressed: a listener on every visit would have stolen focus from search inputs and error alerts; both are guarded.

## Four follow-up slices implemented after the static-candidate re-measurement

| Slice | Findings | Files | Verification |
|---|---|---|---|
| Keyboard-scrollable turn log | BO-002 | `resources/js/pages/Career/Timeline.vue` | 39/39 browser cases at port 8404 |
| Page-title tier | BO-003 | `resources/js/pages/Veterans/TransferRequests.vue` | computed 30px |
| One provenance word per review row | BO-004 | `resources/js/pages/Review/Index.vue`, `tests/browser/review.spec.ts` | follow-up + full review suite green |
| Visible absence reasons on Dashboard | BO-005 | `resources/js/pages/Dashboard.vue` | follow-up + full dashboard suite green |

All four are covered by `tests/browser/audit-followup-2026-11-18.spec.ts`; the existing review spec was updated from the duplicate-label expectation to the documented single-label one and re-run.

## Not implemented, with reasons

| Candidate | Why no code change |
|---|---|
| Enlarge the 16 px race-name links to 44 px | No measured WCAG failure (50 px spacing); enlarging would change a dense catalogue table for no demonstrated user benefit |
| Add captions to the four support-card detail tables | Preceding headings and passing axe scans give them context; adding redundant names would be cosmetic churn |
| Move focus on full document loads | Browsers already start at the top; the measured problem was SPA visits only |
| Any visual redesign | Vision instrument unavailable; no measured evidence to act on |

No owner decision is required to land the implemented slice. A future visual pass would need a working vision endpoint or a human reviewer before any craft change is justified.
