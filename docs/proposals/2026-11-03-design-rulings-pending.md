# Design rulings pending — Dashboard type-scale and 14 section measures

Date: 2026-11-03 (final closure pass)

## Dashboard-F-01 (KI-151) — type-scale drift

**Current state:** five live sizes on Dashboard: `text-xs`, `text-sm`, `text-base`, `text-lg`, `text-kpi`. The design system ratifies four tiers (Critical / Heading / Body / Metadata). The fifth tier (`text-kpi`) is a documented extension, but Dashboard uses five where four are ratified.

**Cross-cutting:** `--text-kpi` feeds `KpiCard`, `Timeline`, and `SkillsPlanner` — changing the token affects all three.

**Options:**
- (a) Schedule a cross-cutting type-scale pass (touches Dashboard + KpiCard + Timeline + SkillsPlanner)
- (b) Merge `text-kpi` into the Heading tier
- (c) Accept five-tier documented exception

**Recommendation:** (a) — but only when a session can absorb the cross-cutting touch. The fifth tier is legitimate for KPI display; the drift is that Dashboard uses all five where four would suffice.

**Status:** pending owner

---

## 14 section-measure rulings (CQ-07)

The following pages carry narrow section measures that need owner rulings. Each page has three options: keep narrow, widen, or add a named exception.

| # | Page | Current measure | Notes |
|---|---|---|---|
| 1 | `Database/Sources.vue` | narrow | Source ledger table; wide content |
| 2 | `Database/Races.vue` | narrow | Race catalog table |
| 3 | `Database/Scenarios.vue` | narrow | Scenario list |
| 4 | `Database/Events.vue` | narrow | Event list |
| 5 | `Database/ShopItems.vue` | narrow | Shop item list |
| 6 | `Database/Sparks.vue` | narrow | Spark list |
| 7 | `Catalog/Index.vue` | narrow | Trainee catalog (25/page) |
| 8 | `Catalog/Show.vue` | narrow | Trainee detail |
| 9 | `SupportCards/Index.vue` | narrow | Card list |
| 10 | `SupportCards/Show.vue` | narrow | Card detail |
| 11 | `Skills/Index.vue` | narrow | Skill search |
| 12 | `Skills/Show.vue` | narrow | Skill detail |
| 13 | `Veterans/Index.vue` | narrow | Veteran library |
| 14 | `Veterans/Show.vue` | narrow | Veteran detail |

**Options per page:** (a) keep narrow, (b) widen to standard, (c) add named exception in `DESIGN.md`

**Recommendation:** per-page ruling required — the owner should review each page at 1280px and decide. No blanket recommendation; the measures were set deliberately and changing them is a design decision.

**Status:** pending owner (14 rulings)
