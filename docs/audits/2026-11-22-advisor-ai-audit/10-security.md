# 16.10 Security

## Audited files
- `app/Http/Controllers/Career/CockpitController.php` (`advisorAnalysis` line 170)
- `app/Services/Advisor/AdvisorAnalysis.php` (309 lines)
- `config/advisor_ai.php`

## Findings

### Compliant: Read-only service
`AdvisorAnalysis` read-only: no write/save/dispatch. No side effects.

### Compliant: Error handling
try/catch returns `503` on `\Throwable` without leaking internals beyond `error.code`/`message`.

### Gap: No SSRF/allowlist check specific to advisor
`AdvisorAnalysis` does not perform outbound HTTP itself; provider calls faked in tests. No explicit allowlist assertion in slice.

### Gap: Enabled flag default false
`ADVISOR_AI_ENABLED` default `false` — safe-by-default.

## Acceptance
- Security posture sound; no new secrets or exposure introduced by slice.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Status:** Reviewed; no source changes affecting this document's claims since baseline.
**Limitations:** No local server available for live re-measurement; all claims rest on source-static inspection.
