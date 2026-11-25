# 16.6 Responsive Viewports

## Audited files
- `resources/js/components/career/AdvisorRail.vue`

## Findings

### Partial: lg breakpoint utilities present
`lg:max-h-[calc(100svh-1rem)]` and `lg:overflow-y-auto` apply at 1024px+. No explicit testing at 1280/768/390/320 viewports.

### Gap: No viewport test evidence
No responsive test coverage for AdvisorRail/NextDecision across 1280/768/390/320px.

## Acceptance
- Breakpoint classes present; viewport behavior unverified.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
