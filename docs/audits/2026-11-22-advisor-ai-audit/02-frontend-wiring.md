# 16.2 Frontend Wiring

## Audited files
- `resources/js/components/career/NextDecision.vue`
- `resources/js/components/career/AdvisorRail.vue`
- `resources/js/pages/Career/Cockpit.vue`

## Findings

### Gap: AdvisorRail does no fetching
`AdvisorRail.vue` (59 lines) wraps `NextDecision`, has `tabindex="0"`, `lg:max-h-[calc(100svh-1rem)]`, `lg:overflow-y-auto`, and forwards `decision`, `raceUrl`, `timelineUrl`, `currentTurn`, `scenarioLabel` untouched. It owns no fetch, no polling, no refetch.

### Gap: Cockpit gates AdvisorRail on `isActive`
`Cockpit.vue` (~1330 lines) gates `<div v-if="isActive" data-region="advisor">` around lines 1206–1214. Advisor prop shape `{decision, finale}` passed through `advisorSection()`.

### Gap: Page-level loading only for user-initiated async actions per ADR-0007
Cockpit has page-level loading (role="status") and error (role="alert") lines 909–910. Custom loading only user-initiated async actions.

## Acceptance
- Wiring is minimal but correct direction; missing client-side refetch/retry is the blocker.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
