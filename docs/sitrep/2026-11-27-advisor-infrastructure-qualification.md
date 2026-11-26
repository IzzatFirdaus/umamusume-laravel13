# SITREP — Advisor Infrastructure Qualification (2026-11-27)

Docs — Technical Documentation Engineer. Session version: Docs v3.1.
Publication assignment: #131. Supplemental evidence reconciled from Infrastructure msg#140 and
Coordinator msg#141. Source revision: `fd9f3e2e`.

Scope: documentation only. No application database access, no test execution, no operational process,
no provider enablement, no application code change. File and line references below were read in place at
`fd9f3e2e`.

## 1. What this report is, and is not

This records an **Infrastructure-reported** qualification of the Advisor infrastructure layer. The test
counts in §2 were executed by Infrastructure and are **not independently reproduced** by this session
or by Coordinator (msg#141 states the same limit). They are recorded as attributed evidence with their
execution identity, not as repository-verified facts.

Nothing here approves a new algorithm, schema, provider state or design. §5 and §6 are investigation
findings about current behaviour; a finding is not a requirement.

## 2. Reported test execution

Infrastructure msg#140 reports, at `fd9f3e2e`, PHP 8.5.8, `vendor/bin/pest <file> --no-coverage`, exit 0
for every file, no network, no provider enablement:

| Test file | Passed | Assertions | Duration |
|---|---|---|---|
| `AdvisorAiPipelineTest.php` | 33 | 105 | 14.52s |
| `AdvisorProvenanceTest.php` | 32 | 116 | 16.95s |
| `AdvisorRevisionTest.php` | 9 | 15 | 8.30s |
| `AdvisorAnalysisRouteTest.php` | 7 | 89 | 8.43s |
| **Total** | **81** | **325** | **48.17s** |

A temporary subsequent-request test (deleted after the run) reported 2 passed / 22 assertions, exit 0,
confirming a cache hit on a second identical HTTP request with no duplicate provider call.

Limitations that must travel with these numbers:

- **Attributed, not reproduced.** No independent run exists in this session's evidence.
- **No execution log or command transcript was attached.** The identity is the revision plus the
  command form, not a captured receipt.
- **`terminate()` is not exercised by these tests.** Both trace middlewares export from `terminate()`,
  which the HTTP kernel invokes and a direct `handle()` call does not. The 81-test result therefore says
  nothing about export latency or worker occupancy.
- **No provider was enabled**, so no export path made a real network call. Green here is green for the
  disabled and mocked paths.

## 3. Corrected telemetry lifecycle

The earlier matrix understated this. Both telemetry integrations defer export to middleware
`terminate()`, which runs **after** the response is sent, not inline during `handle()`:

| Component | Behaviour at `fd9f3e2e` |
|---|---|
| `LangfuseTraceMiddleware::terminate()` | `app/Http/Middleware/LangfuseTraceMiddleware.php:80` — deferred export |
| `LatitudeTraceMiddleware::terminate()` | `app/Http/Middleware/LatitudeTraceMiddleware.php:54` — deferred export |
| Langfuse export timeout | `app/Services/LangfuseTraceService.php:395` — `Http::timeout(5.0)` |
| Latitude export timeout | `app/Http/Middleware/LatitudeTraceMiddleware.php:115` — `Http::timeout(5.0)` |
| Langfuse `flush()` | `app/Services/LangfuseTraceService.php:158` — `public function flush(): void {}`, a no-op |

**Correct reading:** the caller receives a fast response, but the worker is **not** immediately free.
It remains occupied during `terminate()` for up to 5 s per provider — up to 10 s with both Langfuse and
Latitude enabled — while the blocking POST runs. Export exceptions are logged and swallowed, so the
response is unaffected and the failure is fail-open.

This is a correction of a prior description, not a defect filing. Deferred export is a deliberate
design; the point is that "response returned" and "worker available" are different moments.

## 4. Cache and revision freshness

As reported and source-consistent: the revision fingerprint covers run id, scenario, next turn, status,
build target and the latest turn readings. The public delegate is `AdvisorSnapshot::revision()`
(`app/Services/Advisor/AdvisorSnapshot.php:148`), which wraps the private `fingerprint()` (`:162–188`);
`CockpitController.php:321` publishes it as the sibling `advisor_revision` prop. The cache key is
`advisor-ai:1:{careerId}:{revision}` with TTL 900 s as an outer bound, not the freshness mechanism;
invalidation is by key construction on turn, status, target and edit.

Two documented non-coverage areas, preserved as limits rather than defects:

- **Note edits** do not move the revision. Intentional — the Advisor ranks nothing on notes.
- **Race entries** do not move the revision. Recorded as a documented limitation: same-turn race or
  objective changes do not invalidate a cached analysis.

## 5. Investigation finding — possible same-turn revision gap

The race-entry gap in §4 is the concrete instance of a broader question: whether a same-turn change that
the Advisor's own obligations read from the controller calendar — rather than from the revision hash —
should move the revision. Currently it does not, by construction and by the documented test
(`AdvisorRevisionTest:139–158`).

Status: **INVESTIGATION FINDING.** No algorithm change is proposed, approved or required. Whether this
gap matters depends on whether a same-turn race entry can change a recommendation the panel is already
displaying; that has not been measured. Backend and Fullstack are asked to confirm the intended
behaviour, not to change it.

## 6. Investigation finding — test-only alternative-action evaluator

The alternative-action evaluator is exercised in tests only. It is not wired into a runtime path.
Recorded as a finding about current scope, not as a missing feature and not as a defect. Do not promote
it to a runtime requirement without an owner ruling.

## 7. Disposable browser-eval provisioning

Infrastructure reports `.env.browser-eval` (DB `database/synthetic-browser.sqlite`, all providers
disabled, `APP_ENV=browser-eval`) and `tools/provision-browser-eval.ps1` (removes only that synthetic
file, then migrate + seed with `--env=browser-eval`). Coordinator msg#141 source-checked both: the
script removes only the synthetic file.

Status: **ready, not executed.** Distinct from the genuine `database/database.sqlite` (never touched) and
from `:memory:` test databases. Any UX evaluation on it uses synthetic fixtures only. Provisioning
remains owner-gated per the standing UX environment decision.

## 8. Frozen delivery candidates

Five candidates are reported frozen and uncommitted at `fd9f3e2e`: `artisan`, `phpunit.xml`,
`tests/bootstrap.php`, `tools/scheduler.ps1`, `.github/workflows/nativephp-windows.yml`. Their SHA-256
identities were reconciled by Coordinator msg#126 and are unchanged by this document. They are not part
of this Advisor qualification and are not committed here.

## 9. Preserved constraints

- **ADR-0001** — the Energy-guidance lift stands, accepted in part; §5 schema superseded by ADR-0003.
  Nothing in this report widens or narrows it.
- **ADR-0038** — the AI Advisor remains an optional read-only layer, disabled by default, no
  unapproved live inference, no provider enablement, no automated action. The 81-test result was
  obtained with providers disabled, which is consistent with that boundary and does not extend it.
- Telemetry remains optional and failure-isolated. Sending is not proof of ingestion or display.

## 10. Open items

| Item | Disposition |
|---|---|
| Advisor infrastructure qualification | Evidence recorded (attributed); independent reproduction outstanding |
| Same-turn revision gap | Investigation finding; intended behaviour unconfirmed |
| Alternative-action evaluator | Test-only; no runtime scope |
| Browser-eval provisioning | Ready, not executed; owner-gated |
| Five frozen candidates | Unchanged; integration and commit by owner/Fullstack |
| Live scheduler restart | Owner-gated, not executed |
