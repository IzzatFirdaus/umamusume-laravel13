# 16.8 API Contract

## Audited files
- `app/Http/Controllers/Career/CockpitController.php` (`advisorAnalysis` line 170, `advisorSection` line 920)
- `routes/web.php` line 158
- `config/advisor_ai.php`

## Findings

### Compliant: Route registered
`Route::get('/training-runs/{run}/advisor-analysis', [CockpitController::class, 'advisorAnalysis'])->name('runs.advisor-analysis')` at routes/web.php line 158.

### Compliant: 404 when disabled
`abort_unless config('advisor_ai.enabled')` → 404.

### Compliant: 503 error contract
`advisorAnalysis()` try/catch returns `503` `{error:{code:'advisor_analysis_unavailable', message}}` on `\Throwable`.

### Compliant: advisorSection returns {decision, finale}
Returns `['decision' => NextDecision::build(...), 'finale' => $advice->finale_context]` matching ADR-0020 §3 six-key frozen contract shape.

### Gap: Total timeout 12s, decision timeout 20s
`config/advisor_ai.php`: `total_timeout` 12s; decision timeout 20s; retries 1; cache TTL 900s.

## Acceptance
- API contract sound; 503 error path lacks explicit test.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
