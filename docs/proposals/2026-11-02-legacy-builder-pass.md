# Pass report — Legacy builder: commit layout fix, triage regression, complete M2 screen audit

Date: 2026-11-02
Session: resume Legacy builder side-by-side + stalled audit

## Pre-audit triage (§0)

### 0.1 — Layout change committed
- **Status:** Committed
- **SHA:** `a1bfac85` fix(legacy): parents side by side in standard and wide layouts (SCR-CAR-006)
- **Files:** `resources/js/pages/Legacy/Builder.vue` (+16/-11; also included load-bearing `ancestors` array seeding for `AncestorEditor`)
- **Verification:** Verified via Playwright (prior session) — Standard and Wide → 2 grid tracks; Compact → 1 track stacked.

### 0.2 — Scratch verification spec removed
- **Status:** Deleted
- **File:** `tests/browser/_verify_legacy_grid.spec.ts` (untracked, removed)

### 0.3 — Regression gate triage
- **`legacy.spec.ts`:** 9/9 passed (session-private, port 8150)
- **`accessibility.spec.ts`:** 4 passed, 2 failed
  - `the visit progress indicator carries valid progressbar semantics mid-visit (KI-63)` — pre-existing/known issue pattern
  - `no list-role container holds non-list children on the dashboard or the two wizard steps` — pre-existing, different components (dashboard/wizard), not related to Builder.vue
- **Classification:** PRE-EXISTING/UNRELATED — no regressions introduced by the layout change. Legacy Lab specs all green.

## Audit
- **Report:** `docs/audits/AUD-20261102-029-legacy-builder-2026-11-02.md` (M2 screen audit)
- **Findings count by severity:** Severity 0: 6 findings (all observations, no defects). No severity ≥1 findings.
- **Coverage:** Builder states inspected via code + shipped regression specs; layout behavior confirmed correct.

## Findings resolution
| ID | Rule | Outcome | Notes |
| --- | --- | --- | --- |
| F-01 | GT-1, UI-6 | NOT-A-DEFECT | Group `<li>` + `ul.g-2` correctly groups parents; connector on group. |
| F-02 | UI-6, GT-5 | NOT-A-DEFECT | Compact override stacks parents correctly. |
| F-03 | UX-1, WCAG 2.5.8 | NOT-A-DEFECT | Parent selects meet 44px house rule (verified by spec). |
| F-04 | NRM-1, H1 | NOT-A-DEFECT | Error/refusal flow exercised by spec. |
| F-05 | SHR-8, GP-1 | NOT-A-DEFECT | `ancestors` array seeded load-bearing for editor. |
| F-06 | IA-5 | NOT-A-DEFECT | Empty states and absence copy present. |

## Verification
- `npm run typecheck` — clean
- `npm run lint:ui` — pre-existing violations in other files (Preferences/Edit.vue, Veterans/Index.vue etc., not in diff scope)
- `npm run build` — succeeded

## Deviations from dispatch
- Audit measurement spec (`_audit_legacy_builder.spec.ts`) was interrupted mid-run; full rendered JSON outputs not captured. Report uses PARTIAL rendered evidence with source inspection + shipped regression specs as primary evidence (acceptable per audit guidelines when browser measurement incomplete).
- Accessibility suite shows 2 pre-existing failures unrelated to layout change (classified as pre-existing per dispatch guidance).

## Commits
- `a1bfac85` fix(legacy): parents side by side in standard and wide layouts (SCR-CAR-006)
- `5827a7be` docs(audits): M2 screen audit of /legacy/{id} (AUD-20261102-029)
