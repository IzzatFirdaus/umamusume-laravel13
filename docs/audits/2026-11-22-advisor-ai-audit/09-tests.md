# 16.9 Tests

## Audited files
- `tests/Feature/AdvisorAnalysisRouteTest.php` (84 lines, 3 tests)
- `tests/Feature/TrainerAdvisorTest.php` (175 lines)
- `tests/Feature/AdvisorAiPipelineTest.php` (834 lines)
- `tests/Feature/AdvisorProvenanceTest.php` (946 lines)

## Findings

### Compliant: 3 route tests present
`AdvisorAnalysisRouteTest`: 404 off default; JSON contract when on; leaves cockpit advisor prop unchanged (missing 'advisor.analysis').

### Gap: No explicit 503 error path test
No test covering `advisorAnalysis()` returning `503` with `error.code = 'advisor_analysis_unavailable'` on `\Throwable`.

### Gap: No cockpit advisor prop test for 'advisor.analysis'
Test notes cockpit advisor prop unchanged but missing 'advisor.analysis' key assertion.

### Compliant: Pipeline/provenance tests extensive
`AdvisorAiPipelineTest` (834 lines): Http::fake all provider calls; snapshot stability; deterministic on budget exhaustion.
`AdvisorProvenanceTest` (946 lines): decision provenance; explanation correctness.

### Compliant: Ranking rules tested
`TrainerAdvisorTest` (175 lines): ranking rules; source/date constants.

## Acceptance
- Test coverage strong on pipeline/provenance; missing explicit 503 error-path test is a gap.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
