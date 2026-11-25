# 16.4 ADR-0007 Loading/Error

## Audited files
- `resources/js/pages/Career/Cockpit.vue` lines 909–910
- `resources/js/components/career/NextDecision.vue`

## Findings

### Compliant: Page-level loading role="status" and error role="alert"
Cockpit.vue lines 909–910 have page-level loading (role="status") and error (role="alert") per ADR-0007.

### Gap: No custom loading state for user-initiated async actions
ADR-0007: custom loading state only for user-initiated async actions. `NextDecision.vue` has none — no loading, error, cancellation, retry UI at component level.

### Gap: No component-level error role="alert"
No component-level error announcement inside AdvisorRail/NextDecision.

## Acceptance
- Page-level ADR-0007 compliant; component-level missing is a gap.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
