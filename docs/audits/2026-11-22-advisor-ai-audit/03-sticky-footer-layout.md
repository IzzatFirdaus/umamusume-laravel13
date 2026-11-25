# 16.3 Sticky/Footer Layout

## Audited files
- `resources/js/components/career/AdvisorRail.vue`

## Findings

### Gap: Sticky positioning breaks main @container
`AdvisorRail.vue` uses `lg:max-h-[calc(100svh-1rem)]` and `lg:overflow-y-auto`. Sticky/footer layout constraints not fully verified against main `@container`.

### Gap: No footer integration verified
No evidence AdvisorRail integrates with page footer or respects sticky container boundaries across viewports.

## Acceptance
- Layout classes present but sticky/@container interaction unverified; needs viewport testing.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
