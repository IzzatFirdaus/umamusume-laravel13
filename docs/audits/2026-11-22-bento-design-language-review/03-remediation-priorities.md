# 03 — Remediation priorities

Implementation-oriented backlog for the bento design-language audit.
Grouped into implementation batches without obscuring per-screen requirements.

Read-only audit: no application code was modified. All priorities below are recommendations for the frontend implementation agent.

## Priority legend

| Priority | Meaning |
|---|---|
| P0 | Severe layout or accessibility failure: essential content hidden, unusable controls, critical overflow, obstructed navigation |
| P1 | Major design-language inconsistency: broken hierarchy, seriously fragmented layout, significant misuse of panels |
| P2 | Moderate inconsistency: uneven spacing, inappropriate panel proportions, inconsistent grouping, weak section hierarchy |
| P3 | Minor refinement: small alignment, typography, or presentation adjustments |

## Batch 1 — Unblock (P0): screens that cannot render or load

| ID | Screen | Route | Finding ID | Priority | Source rule | Exact proposed change | Expected frontend files | Shared-component deps | Owner approval / backend dep | Verification |
|---|---|---|---|---|---|---|---|---|---|---|
| SCR-VET-004 | Veteran comparison | `/veterans/compare` | BENO-VET-001 | P0 | SCREEN_SPEC §4.20; matrix must load | Fix array serialization: `Compare.vue:85` sends `router.get` key `'veterans[]'` over an array; the request refuses it and `back()` lands on `/veterans`. Change to `veterans[0]=N` form so Inertia emits compatible query params. | `resources/js/pages/Veterans/Compare.vue` | None | Backend-dependent (array serialization) — no layout change | Matrix loads at `/veterans/compare`; sticky first column + header row; two-column render asserted in browser |

Note: SCR-VET-004's layout (sticky matrix, all property rows, `Best for` absent) is correct; the defect is data-submission, not layout. No bento reclassification needed.

## Batch 2 — P1: Major design-language inconsistency (layout structure)

No P1 layout defects were found across the 66 screens. The only P1-class item is structural debt with an owner gate:

| ID | Screen | Route | Finding ID | Priority | Source rule | Exact proposed change | Expected frontend files | Shared-component deps | Owner approval / backend dep | Verification |
|---|---|---|---|---|---|---|---|---|---|---|
| SCR-CAR-011 | Career Cockpit | `/training-runs/{run}/cockpit` | BENO-COCKPIT-001 | P1 | SCREEN_SPEC §8.25 HFW override register (Cockpit row); KI-144 | Retain `Career tools` disclosure as interim home for writes (run identity form, turn correction, delete-turn, delete-run). Do NOT delete until dedicated write doors (`runs.update`, `runs.turns.update`, `runs.turns.destroy`) exist — deleting first makes writes unreachable (data-loss bug). Retire as Slice 6b once doors land. | `resources/js/pages/Career/Cockpit.vue` | None | Owner-gated: do not remove without write doors | Disclosure retained until write doors exist; no data-loss regression |

Rationale: The Cockpit's bento hierarchy (header strip + status strip + advisor rail + scenario region) is compliant. The `Career tools` disclosure is a documented HFW override, not a visual defect.

## Batch 3 — P2: Moderate inconsistency (spacing, proportions, grouping)

| ID | Screen | Route | Finding ID | Priority | Source rule | Exact proposed change | Expected frontend files | Shared-component deps | Owner approval / backend dep | Verification |
|---|---|---|---|---|---|---|---|---|---|---|
| SCR-CAR-001 | Dashboard | `/` | BENO-DASH-001 | P2 | DESIGN.md §2.3 (`.card` padding, Section primitive); SCREEN_SPEC §4.21 | Replace the bare `class="card"` empty-state wrapper with `Section` (or align its padding to `.card` tokens: `14px 16px`, `radius-card`, `rule-soft` edge). Move "7 veterans · 3 shown" footer copy below the grid as a metadata caption instead of inside the grid. | `resources/js/pages/Dashboard.vue` | `Section` primitive (reuse existing) | None | Empty wrapper uses Section or `.card`-aligned padding; caption below grid; five-panel bento retained; one filled action unchanged |
| SCR-RUN-005 | Import preview & confirm | `/training-runs/import/preview` | BENO-IMPORT-001 | P2 | DESIGN.md §2.3 (table.t grammar) | Confirm the preview table uses `table.t` (not hand-rolled Tailwind) so row hairlines, 7px/9px cell padding, and 10px uppercase kicker match the shared grammar. | `resources/js/pages/Runs/Import.vue` | `table.t` CSS primitive | None | Preview table renders via `table.t` class; row hairlines and cell padding match other tables |
| SCR-SYS-006 | Race database | `/database/races` | BENO-RACES-001 | P2 | DESIGN.md §2.3 (focusable scroll region at 320px) | Confirm the wide table scrolls inside its own focusable region (`overflow-x-auto`, `role="region"`, `tabindex="0"`, `aria-label`) at 320px rather than paging the document. | `resources/js/pages/Database/Races.vue` | `table.t`, focusable region pattern | None | Table scrolls within region at 320px; region is focusable and labelled |

## Batch 4 — P3: Minor refinements (alignment, typography, presentation)

No P3 layout defects found beyond the P2 items above. All screens assessed as compliant at P3.

## Cross-screen shared-component recommendations

| Finding ID | Issue | Affected screens | Cause | Shared correction | Exceptions to preserve | Verification |
|---|---|---|---|---|---|---|
| BENO-SHARED-001 | Empty-state wrapper inconsistency | Dashboard (bare `.card`) | Dashboard predates full `Section` adoption | Align empty wrapper to `.card` tokens or use `Section` | None | Dashboard empty state renders with consistent padding |
| BENO-SHARED-002 | `title`-only absence channel (migration debt) | Dashboard (Energy, Team Rank tiles), `AncestryNode.vue` | Pre-2026-10-17 idiom; `AbsenceValue` exists but not yet migrated | Migrate to `AbsenceValue` disclosure or visible prose beside marker | None (migration debt, not second channel) | `AbsenceValue` used on all three; no `title`-only absences remain |
| BENO-SHARED-003 | `subnav` overflow wrapper | Database pages with subnav | `layout-grid.css` `.subnav` carries HFW `overflow-x:auto; flex-wrap:nowrap` unconditionally | Restore media wrapper: wrap in `@media (max-width: 820px)` so the strip can wrap at desktop (noted in `layout-grid.css` :54-69, pending move to app.css) | None | Subnav wraps at desktop, scrolls only below 820px |
| BENO-SHARED-004 | `g-side`/`g-4` bento primitives | Dashboard, Database hub, Cockpit | Container-query breakpoints at canvas 585px/845px (not viewport) | Keep as the single bento grid source; do not duplicate one-off grid classes | None | All bento screens use `.g-side`/`.g-4`/`.g-2`/`.g-3` only |

## Owner-gated recommendations

| ID | Screen | Gate | Required approval | Specification changes |
|---|---|---|---|---|
| BENO-COCKPIT-001 | Career Cockpit | KI-144 / Slice 6b | Owner must confirm write doors (`runs.update`, `runs.turns.update`, `runs.turns.destroy`) exist before retiring `Career tools` disclosure | SCREEN_SPEC §8.25 HFW override register updated when disclosure retired |
| — | All | 768px minimum (DESIGN.md §2.3 R85) | Owner decision: ratify with measurement or withdraw the number | DESIGN.md §2.3 bullet updated |
| — | All | Token contrast ratios (DESIGN.md §2.1) | Owner: browser contrast gate unrun on this host (sandbox write denial) | No spec change; gate run when environment permits |

## Backend-dependent items

| ID | Screen | Dependency |
|---|---|---|
| BENO-VET-001 | Veteran comparison | Array serialization fix (`veterans[]=` → `veterans[0]=`) in `Compare.vue`; backend must accept the resulting query format |
| — | Veteran comparison (SCR-VET-004) | Gate RED blocks browser verification; fix required before layout can be visually confirmed |

## Implementation order (recommended)

1. **Batch 1 (P0)** — Fix `Compare.vue` array serialization so Veteran comparison loads. Unblock everything downstream.
2. **Batch 2 (P1)** — Retain `Career tools` disclosure; schedule Slice 6b retirement once write doors exist. No code change now.
3. **Batch 3 (P2)** — Dashboard empty-wrapper alignment + caption move; confirm `table.t` on Import preview; confirm focusable scroll region on Race database.
4. **Batch 4 (P3)** — None required.
5. **Cross-screen shared fixes** — `subnav` media wrapper; `title`-only absence migration; audit all consumers of `.g-side`/`.g-4` for single-primitive use.

## Verification requirements (all batches)

- No live browser session was available during this audit; all theme/viewport combinations are source-only. Before marking any batch complete, run the browser specs at 1280/768/390/320 across Azure Daybreak / Midnight Circuit / EVA-01 themes.
- `tests/browser/dashboard.spec.ts`, `tests/browser/career-cockpit.spec.ts`, `tests/browser/scenario-detail-order.spec.ts`, `tests/browser/veteran-compare.spec.ts`, `tests/browser/database-parity.spec.ts` are the key specs for bento screens.
- `DesignTokensTest` (102 token pin) and `DesignDocEnforcementTest` must remain green.
- `pint --test --format agent` on changed files only (never `--dirty`).
- No `dark:` utilities, no arbitrary Tailwind values, no card-in-card structures.

## Decision status summary

| Finding | Status |
|---|---|
| BENO-VET-001 (Veteran comparison array) | Backend-dependent |
| BENO-COCKPIT-001 (Career tools disclosure) | Owner-gated |
| BENO-DASH-001 (Dashboard empty wrapper) | Authorized |
| BENO-IMPORT-001 (Import preview table.t) | Authorized |
| BENO-RACES-001 (Race DB focusable region) | Authorized |
| BENO-SHARED-001/002/003/004 (shared) | Authorized |
| All other screens | No change required |

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
