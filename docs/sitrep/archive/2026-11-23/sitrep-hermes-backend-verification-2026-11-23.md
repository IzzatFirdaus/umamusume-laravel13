# SITREP — Hermes backend verification, 2026-11-23 (second session)

**Agent:** Hermes — backend support. **Consumer:** DSH (Advisor frontend sign-off). **Branch:** `master`, HEAD `3600741e`. **Runtime:** PHP 8.5.1, Laravel 13.42.0, Windows, Git Bash sandbox (reg.exe blocked by policy — no impact on these checks).

---

## A. Executive conclusion

**Ready with limitations.** The backend contract is stable at version `'1'`, every focused Advisor suite passes in this environment (including the five corpus-index tests the previous session classified as environmentally blocked — they pass now), and no backend runtime code changed in this session. The two limitations are unchanged and frontend-side by design: (1) 404 bodies are indistinguishable between "layer disabled" and "run not found" — confirmed deliberately, do not branch on the body; (2) the revision fingerprint does not cover calendar obligations, so a recorded race inside the same turn is invisible to it — stale-response rejection for that case stays DSH's job.

## B. Completed backend work (this session)

| Work | Status |
|---|---|
| Reconciliation vs previous Hermes sitrep (H-1/H-2/H-3) | All present and preserved; nothing re-created |
| Full re-verification of contract, provider paths, fallback, cache | Done, all passing |
| Five corpus-index tests re-run | **Pass now** (environmental blocker no longer reproduces; direct PDO write check to `storage/app/private/advisor-index/` returns WRITE OK) |
| New: `tests/Feature/AdvisorRevisionTest.php` (Workstream C) | Created; 7 passed, 1 skipped (no seeded catalog slot — race-entry case skipped, limitation documented below) |
| Pint fix to `AdvisorProvenanceTest.php` (pre-existing style, untracked slice file) | 1 style issue fixed; re-passing |
| 404 body probing (JSON vs HTML accept) | Done via isolated request; findings in §D |
| Write-path scan (`save/update/create/delete/increment`) over `app/Services/Advisor/**` + controller | Zero matches; read-only confirmed by scan plus existing tests |

**No `app/**` file changed this session. Diff vs previous session: one new test file, one style-only fix to an untracked test file.**

## C. API contract

Unchanged. `contract_version: '1'` (`AdvisorAnalysis::CONTRACT_VERSION`, cached under the same key). All 18 fields verified against `AdvisorAnalysis.php:147-196` and live test responses; `advisor-api-contract.json` matches the code, including the corrected `scenario` = display label and `explanation_source ∈ {'deterministic','logfare'}`. Deterministic `decision.preference` serializes as JSON `[]` (PHP list); `decision.model` is `null` when deterministic and `'unknown'` when a provider omits it. No DSH adjustment required.

## D. HTTP and provider states

- **200** — full payload; deterministic / Clef-validated / abstained / fallback / explanation-degraded / null-recommendation / degraded-retrieval all covered by passing tests (`AdvisorAiPipelineTest` 33 + `AdvisorProvenanceTest` 32 + route 4).
- **404, layer disabled** — `abort_unless(config('advisor_ai.enabled'))`, controller line 172. Body depends on Accept header (probed this session):
  - JSON request (axios: `Accept: application/json` / `X-Requested-With`): `{"error":{"code":"NOT_FOUND","message":"Resource not found."}}` via `bootstrap/app.php` render closure — **not** the Laravel HTML page the contract JSON claims.
  - HTML request: Laravel default 404 page (`text/html`).
- **404, nonexistent run** — identical status and identical JSON envelope (binding `ModelNotFoundException` hits the same render branch). **Bodies are indistinguishable by design; DSH branches on status only.** No feature-discovery endpoint added (per assignment).
- **503** — exact body `{"error":{"code":"advisor_analysis_unavailable","message":"The analysis could not be produced. The recommendation on the cockpit is unaffected."}}`; `application/json`; no exception text/token leakage; career untouched (pinned by existing 503 regression test — preserved, not duplicated).
- **Read-only** — verified three ways: grep-level write-path scan (zero), `never writes to the career during an analysis` test (passing), 503-test before/after row equality (passing).

## E. Career revision assessment (new evidence, `AdvisorRevisionTest.php`)

Fingerprint inputs (`AdvisorSnapshot::fingerprint()`): run id, scenario key, next turn number, run status, build target, and the latest turn's `id/turn/energy/energy_state/energy_band/mood/speed/stamina/power/guts/wit/sp/fans`. SHA-256, truncated 16 hex.

| Mutation | Revision changes? | Test result |
|---|---|---|
| New turn logged | Yes | ✓ |
| Same-turn reading edit (energy 60→35, same turn number) | **Yes** | ✓ — the case a turn check alone misses |
| Build target change (same turn) | Yes | ✓ |
| Run status change | Yes | ✓ |
| Scenario change | Yes | ✓ |
| Notes edit | No (deliberate) | ✓ |
| **Race entry recorded against calendar** | **No — fingerprint limitation** | skipped (no seeded slot); by code reading: obligations enter via `CockpitController::objectiveSection()`, never the fingerprint |
| Cache behavior | Keyed `advisor-ai:{ver}:{career}:{revision}`; same-turn edit misses old entry by construction; old entry left untouched (no invalidation write to race with) | ✓ |

**The three guarantees, separated:** backend cache freshness (holds — key carries revision); backend response consistency (holds — payload carries the analyzed snapshot's revision); frontend presentation freshness (**DSH's alone** — a response in flight during a same-turn edit or a race recording still arrives for the older revision; compare `career_revision` (and `turn`) at arrival and discard). The frontend receives enough: `career_revision` is comparable opaquely; DSH must not reconstruct it.

## F. Regression results

| Command | Result | Exit |
|---|---|---|
| `php artisan test tests/Feature/AdvisorAnalysisRouteTest.php tests/Feature/AdvisorAiPipelineTest.php` | 37 passed (143 assertions) | 0 |
| `php artisan test tests/Feature/AdvisorProvenanceTest.php tests/Feature/TrainerAdvisorTest.php` | 44 passed (165 assertions) | 0 |
| `php artisan test tests/Feature/AdvisorRevisionTest.php` (new) | 7 passed, 1 skipped (14 assertions) | 0 |
| Combined focused suite (5 files) | **88 passed, 1 skipped (322 assertions)** | 0 |
| `vendor/bin/pint --test` (all 19 Advisor files incl. controller + new test) | PASS | 0 |
| `vendor/bin/phpstan analyse app/Services/Advisor app/Http/Controllers/Career/CockpitController.php` | No errors | 0 |
| `python tools/adr-check.py docs/adr` | 0 findings | 0 |

Environment: Laravel test env, SQLite, `Http::fake` throughout — no live provider calls. The previous session's five `SQLSTATE[HY000][14]` failures **do not reproduce**; classify them resolved-in-this-environment, not historically passing.

## G. Files changed (this session)

- `tests/Feature/AdvisorRevisionTest.php` — **new**. Pins fingerprint inputs, same-turn edit behavior, cache-key semantics, and the race-entry limitation. Backend test infrastructure, Hermes-owned.
- `tests/Feature/AdvisorProvenanceTest.php` — style-only Pint fix (untracked slice file; no assertion touched).

No `app/**`, no routes, no config, no frontend-owned file touched.

## H. Repository state

Branch `master`, HEAD `3600741e`. Working tree: ~168 modified/untracked entries from multiple sessions (large shared in-flight state; nothing staged or committed by me — no authorization given). Stashes present (`stash@{0}` Track B WIP, `stash@{1}` peer UC-1 work — not mine, untouched). `storage/app/private/advisor-index/` holds 181 scratch `*.sqlite` files (46 `test-*`, rest `benchmark-*`) — pre-existing leak, owner call on cleanup, untouched.

## I. DSH handoff (delta only — full contract in `advisor-api-contract.json`)

1. **404 body correction:** with axios, the disabled-layer 404 body IS JSON `{"error":{"code":"NOT_FOUND","message":"Resource not found."}}` — the contract JSON's "Laravel default 404 page, not JSON" note describes the HTML-Accept case only. Branch on status; the body cannot distinguish disabled vs missing run; never retry on 404.
2. **Revision guarantee:** covers turn + readings + target + status + scenario, **not** calendar obligations. A same-turn energy edit produces a new revision (backend cache is safe); a race recording in the same turn does not. Reject late responses by comparing `career_revision`/`turn` at arrival.
3. **Fixture id mismatch (browser spec, DSH-owned):** `career-cockpit-advisor-ai.spec.ts` fixtures use `recommended_action.id: 'training-Speed'` and alternatives/blocked ids like `'training-Power'`; the real backend emits `'speed'`, `'power'` (lowercase, underscore). Harmless for panel rendering (labels are displayed), but any future test asserting on ids must use the backend vocabulary.
4. **Safe fixtures:** contract JSON §response is verified field-for-field; reuse it. The `conflicts` key is always `[]`.

## J. Remaining work

- **Confirmed backend defects:** none found this session.
- **Environmental blockers:** none — previous index-file failures resolved in this environment. Scratch-file leak in `advisor-index/` remains an owner-decision cleanup (KI candidate, unchanged from prior sitrep).
- **Frontend-owned (DSH):** 44px disclosure fix, browser sign-off, stale-protection implementation (per §I.2), fixture id vocabulary if asserted.
- **Owner-gated:** ADR-0038 acceptance (still Proposed — config + tests enforce the boundary regardless); scratch-file cleanup authorization; live-provider verification pass.
- **Optional improvements:** none implemented; no speculative contract changes during sign-off.
