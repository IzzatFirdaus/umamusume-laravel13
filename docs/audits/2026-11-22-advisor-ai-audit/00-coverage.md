# Advisor AI Slice Audit — 2026-11-22

Consolidated final audit of the Advisor AI slice across deliverables 16.1–16.11.

## Scope

| Deliverable | File | Status |
|-------------|------|--------|
| 16.1 Production readiness | `01-production-readiness.md` | Reviewed |
| 16.2 Frontend wiring | `02-frontend-wiring.md` | Reviewed |
| 16.3 Sticky/footer layout | `03-sticky-footer-layout.md` | Reviewed |
| 16.4 ADR-0007 loading/error | `04-adr-0007-loading-error.md` | Reviewed |
| 16.5 WCAG 2.2 | `05-wcag-22.md` | Reviewed |
| 16.6 Responsive viewports | `06-responsive-viewports.md` | Reviewed |
| 16.7 Design systems | `07-design-systems.md` | Reviewed |
| 16.8 API contract | `08-api-contract.md` | Reviewed |
| 16.9 Tests | `09-tests.md` | Reviewed |
| 16.10 Security | `10-security.md` | Reviewed |
| 16.11 Provenance | `11-provenance.md` | Reviewed |

## Key artifacts audited

- `resources/js/components/career/NextDecision.vue` (435 lines)
- `resources/js/components/career/AdvisorRail.vue` (59 lines)
- `resources/js/pages/Career/Cockpit.vue` (~1330 lines)
- `app/Http/Controllers/Career/CockpitController.php` (`advisorAnalysis` line 170, `advisorSection` line 920)
- `app/Services/Advisor/AdvisorAnalysis.php` (309 lines)
- `config/advisor_ai.php` (188 lines)
- `routes/web.php` line 158
- `tests/Feature/AdvisorAnalysisRouteTest.php` (84 lines, 3 tests)
- `tests/Feature/TrainerAdvisorTest.php` (175 lines)
- `tests/Feature/AdvisorAiPipelineTest.php` (834 lines)
- `tests/Feature/AdvisorProvenanceTest.php` (946 lines)

## Overall assessment

The Advisor AI slice is architecturally sound but incomplete on production readiness. No component-level loading/error/cancellation/retry UI exists in `NextDecision.vue`, and `AdvisorRail.vue` forwards props untouched with no fetch logic of its own. The backend `advisorAnalysis()` correctly returns a `503` with `error.code = 'advisor_analysis_unavailable'` on `\Throwable`, but no explicit 503 path test exists. `AdvisorAnalysis` is read-only with deterministic fallback.

See per-deliverable reports for details.

---

## Reconciliation (2026-11-22, same day)

A reconciliation pass corrected this audit against the working tree (`ad0162f8` + in-flight Hermes Advisor backend files). Read in this order:

| File | Content |
|------|---------|
| `12-reconciliation-log.md` | R1–R8 dispositions; corrections C-1..C-6; several original findings withdrawn or reclassified |
| `14-api-contract.md` | Verified frontend-consumable response contract (supersedes `advisor-api-contract.json` where they disagree) |
| `15-implementation-roadmap-f1-f6.md` | F1–F6 implementation plan; Option A (typed JSON client + composable) chosen |
| `16-findings-register.md` | Corrected findings register + test matrix |
| `17-dsh-hermes-handoff.md` | Ownership split; Hermes requests H-1..H-4 |
| `18-sitrep.md` | Consolidated handoff |

**Material corrections to the original findings:** the only confirmed defect-class gap is the absent frontend consumer of `runs.advisor-analysis`. "Sticky breaks `@container`" was withdrawn (the rail has no sticky positioning); the `NextDecision` loading/error finding was reclassified as a future requirement of the AI integration, not a defect of the deterministic component; `explanation_source` is `'logfare'`, not `'ai'`; the repository has no authorization layer by design, so the contract file's ownership claim is wrong. The original `01`–`11` files are preserved below as historical evidence; where they disagree with `12`–`18`, the reconciliation wins.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22

### Coverage status at current HEAD

Audited files unchanged since baseline:
- `resources/js/components/career/NextDecision.vue`
- `resources/js/components/career/AdvisorRail.vue`
- `resources/js/pages/Career/Cockpit.vue`

### Limitations

No local server available for live re-measurement.
