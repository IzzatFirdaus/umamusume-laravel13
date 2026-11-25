**Agent:** Hermes (backend) · **Scope:** TRAINERDESK AI ADVISOR integration — ADR boundary record, contract metadata correction, 503 regression, focused verification · **Tree state at work:** branch `master`, working copy carries ~160 pre-existing entries from other sessions (drift, not mine); my footprint is exactly the five files in §5.

**Session:** `b4373e33` · **Date:** 2026-11-23 (Asia/Kuala_Lumpur) · **Deliverables:** `docs/adr/0038-ai-advisor-optional-read-only-layer.md` (new, Proposed), corrected `advisor-api-contract.json`, new 503 case in `tests/Feature/AdvisorAnalysisRouteTest.php`, ADR index regenerated, `config/advisor_ai.php` header note updated.

## 1. Executive conclusion

The Advisor AI boundary now has its governing record (ADR-0038, Proposed — owner acceptance is the
outstanding gate), the machine-readable contract file agrees with the producer on every field it
checked, and the one missing regression (503 path) is pinned by a passing test. The response
**shape is unchanged** — `contract_version` stays `'1'` — so the DSH frontend work F1–F6 needs no
contract adjustment beyond the metadata corrections, which are documentation, not a protocol
change. Nothing was staged, committed, or pushed.

| Workstream | Item | Result (evidence) |
|---|---|---|
| A | H-1 ADR for optional Advisor AI boundary | **Done, Proposed.** ADR-0038 drafted, all ten boundary points, `adr-check.py` exit 0, 0 findings (Decision trimmed to 495 words against the 500 cap). |
| A | H-2 contract metadata correction | **Done.** 12 targeted fixes in `advisor-api-contract.json`, JSON re-parsed valid after each batch. No response-shape change. |
| A | H-3 503 regression test | **Done.** New case in `AdvisorAnalysisRouteTest.php`; suite 4 passed (38 assertions), exit 0. |
| B | Freeze/verify `runs.advisor-analysis` contract | **Verified frozen.** All 17 response fields checked against `AdvisorAnalysis.php:147–196` by exact-byte inspection plus one live captured 200 payload. |
| C | Lifecycle 200/404/503, career revisions, request deadlines | **Verified.** 404 = `abort_unless(config('advisor_ai.enabled'))` (default page, not JSON); 503 = stable code body on any Throwable; revision = 16-hex SHA256, cache-key guarantee only; 12 s total budget. |
| D | Full-stack contract verification with doubles | **Done** at the backend boundary — every provider state covered by existing `Http::fake` suites; no live paid inference anywhere this session. |
| E | DSH integration handoff | **Delivered** in §7. |
| F | Focused verification only | **Done** — four named test files + scoped Pint + scoped PHPStan + ADR gate; no full Pest, no browser suite. |

## 2. H-1 — ADR-0038 (Proposed)

`docs/adr/0038-ai-advisor-optional-read-only-layer.md` records the boundary the
`config/advisor_ai.php` header had listed as owed:

1. Optional, off by default (`advisor_ai.enabled` defaults false; off = no cloud call, no local
   model call, no retrieval).
2. Deterministic engine is the decision-safety floor (`TrainerAdvisor` unmodified; every provider
   state lands back on the engine's recommendation).
3. Clef restricted to the engine's ranked candidates (called only when more than one action is
   ranked; out-of-set choices and malformed probability maps discarded).
4. Read-only execution (no save/update/dispatch; failed analysis leaves the career byte-identical
   — regression-tested).
5. Cache boundary is the career revision (`advisor-ai:{version}:{career_id}:{revision}`;
   fingerprint over run id, scenario key, next turn, status, build target, latest readings;
   note edits do not change it).
6. Provider degradation is the designed path (no key / 401 / 429 / 5xx / timeout / budget /
   invalid choice / abstention all fall back, stated in `decision` + `explanation_source`;
   12 s request budget).
7. Evidence from the local corpus with provenance (path/heading/line/servers; retrieval failure
   degrades to empty list, never a fabricated citation).
8. Canonical data vs supporting documents separated (no entry added to `config('uma.sources')`).
9. No runtime dependency on agent tooling.
10. Cockpit contract preserved (`advisor` prop stays exactly `decision` + `finale`; AI layer
    lives only on its own JSON route; 404-when-off is intentional).

Status **Proposed**: a Proposed ADR does not bind (AGENTS.md §2); owner acceptance is the
approval action. The index row was added by regenerating with the repository's own tool
(`tools/adr-index.py`); the README diff is exactly the index splice (one hunk: the 0031 row
re-derived by the tool — its "Still open" column now reads "— (the turn-12 fallback inference is
accepted by the owner delegation of 2026-11-18; see the ledger)" — plus the new 0038 row; nothing
outside the markers changed).

## 3. H-2 — contract metadata corrections (`advisor-api-contract.json`)

Every fix below was verified against the producer bytes and/or a live captured payload before
landing. The file stays untracked slice work; the response shape is untouched.

| Field / block | Was (wrong) | Now (verified) |
|---|---|---|
| `authorization` | "Session-based (requires authenticated user owning the run)" | None by design (PRD.md NFR-1, ARCHITECTURE.md §8); no ownership policy exists or is implied |
| `security.auth` / `security.authorization` | "Web session (Laravel auth)" / "standard Laravel ownership" | Same no-auth correction |
| 200 `scenario` | "scenario key, e.g. 'unity_cup'" | **Display label** (live payload: `"Unity Cup"`); the key is not a response field |
| 200 `decision.model` | "e.g. 'clef', 'unknown'" | Config default `'clef'`; `'unknown'` when a provider response omits its model; null when deterministic |
| 200 `explanation_source` | `'deterministic' | 'ai'` | `'deterministic' | 'logfare'` |
| `clef_validated.explanation_source` | `"ai"` | `logfare` only when the explanation came from the provider; a validated decision can carry the deterministic explanation |
| `clef_abstained.explanation_source` | `"deterministic"` (absolute) | deterministic OR logfare — the explanation step is independent of decision status |
| `deterministic.when` | included `advisor_ai.enabled === false` | Removed — that state 404s before a payload exists; single-candidate skip stated |
| `error_404.body` | "Not Found" (implied JSON) | Laravel default 404 page (Inertia `httpException` view), not JSON; branch on status code |
| `stale_result_behavior` | "Impossible by design" | Cache-key guarantee only; a late in-flight response for an older turn is a **frontend** stale-protection duty |
| `gaps[0]` | "No explicit authorization policy test found …" | Rewritten: no authorization layer exists by design, so nothing is missing |
| `gaps[4]` / `gaps[5]` | "No explicit test for 503" / "fingerprint not documented" | Closed: 503 case named and passing; fingerprint pinned to `AdvisorSnapshot::fingerprint()` + ADR-0038 |
| `dsh_handoff` | carried "Security via session auth", "8-field decision object" | Rewritten with verified semantics (see §7) |

## 4. H-3 — 503 regression test

`tests/Feature/AdvisorAnalysisRouteTest.php` — "answers 503 with a stable code when the analysis
throws, without leaking the failure":

- Enables the layer, fakes HTTP, creates a real run + turn-1 readings.
- Forces the failure at the only point the controller's dependency can fail: a **partial Mockery
  mock of the container-built final instance** (`Mockery::mock(app(AdvisorAnalysis::class))`,
  `analyze` → `RuntimeException('advisor pipeline boom: token abc-123')`) — `AdvisorAnalysis`
  is final, so a class-level mock is not an option.
- Asserts: status 503, JSON content type, exact body `{error:{code:'advisor_analysis_unavailable',
  message:'The analysis could not be produced. The recommendation on the cockpit is unaffected.'}}`,
  **no** exception text and **no** token substring anywhere in the body, and the analysed career
  plus its turns value-identical before/after.

Result: `4 passed (38 assertions)`, exit 0, no PHP warnings (an unused global import that
triggered a warning was removed).

## 5. Files touched (mine only)

| File | State | Change |
|---|---|---|
| `tests/Feature/AdvisorAnalysisRouteTest.php` | untracked (slice-owned) | +503 case, +1 import |
| `advisor-api-contract.json` | untracked (slice-owned) | metadata corrections, §3 |
| `config/advisor_ai.php` | untracked (slice-owned) | header note only: "ADR owed" paragraph replaced by ADR-0038 citation; `php -l` clean; zero code lines changed |
| `docs/adr/0038-ai-advisor-optional-read-only-layer.md` | **new** | the ADR |
| `docs/adr/README.md` | tracked | index splice only (verified: 1 hunk, table rows only) |

No `app/` code changed. Scratch verification test created and deleted. No DSH-owned paths
(`resources/js/**`, `tests/browser/**`) touched. Nothing staged/committed/pushed (no
authorization given).

## 6. Verification (commands → quoted output)

- `php artisan test tests/Feature/AdvisorAnalysisRouteTest.php` → **4 passed (38 assertions)**, exit 0.
- `php artisan test` (AdvisorAnalysisRouteTest + AdvisorAiPipelineTest + AdvisorProvenanceTest +
  TrainerAdvisorTest) → **5 failed, 76 passed (290 assertions)**.
  - **All 5 failures are one environmental condition, not a regression:** identical
    `PDOException SQLSTATE[HY000][14] unable to open database file` at
    `app/Services/Advisor/AdvisorIndex.php:55` — the five corpus-index tests that build a real
    SQLite file under `storage/app/private/advisor-index/`. Proven environmental: a spawned PHP
    child (phpunit's own process class) receives **EPERM writing that directory** in this sandbox
    while writing to scratch succeeds; the five failing tests are exactly the ones that open the
    index file, and none of my five files touch `AdvisorIndex`, its path, or `storage/`. The
    directory holds 176 leaked `test-*.sqlite` files from earlier sessions (2026-11-22), which is
    how these suites passed before.
  - The remaining 76 include every 200/404/provider/fallback/abstention/cache/budget case.
- `vendor/bin/pint --test --format agent tests/Feature/AdvisorAnalysisRouteTest.php config/advisor_ai.php`
  → `{"tool":"pint","result":"passed"}`.
- `python tools/adr-check.py docs/adr` → exit 0, **0 finding(s)** (after trimming the Decision
  section to 495 words against the gate's 500-word cap).
- PHPStan (project gate scope is `paths: [app]` per `phpstan.neon`; my diff adds no `app/`
  files): run over `app/` with a scratch `tmpDir` → 9 errors, **all in
  `app/Services/LangfuseTraceService.php`** — a peer's telemetry file (last commit
  `6dc1b41d feat(telemetry) …`, never touched by this session). Pre-existing against that peer's
  landing, not mine. (Pointing PHPStan at the test file instead surfaces only the
  missing-Pest-extension `TestCall::get()` noise the project gate never runs, since
  `tests/` is out of its scope.)
- `advisor-api-contract.json` re-parsed as valid JSON after the final edit.
- Live 200 payload captured via a temporary test (deleted afterwards): `scenario="Unity Cup"`,
  `turn=2`, `career_revision` 16-hex, `decision={provider:'deterministic', model:null,
  status:'fallback', fallback_used:true, preference:[], confidence:null}`,
  `explanation_source="deterministic"`, `conflicts=[]`,
  `retrieval={degraded:true, lexical_count:0, semantic_count:0}`.

**Not run:** full Pest suite, Playwright, `npm run typecheck`/`lint:ui` (no TS/Vue/Blade
changed), lore gate (no new user-visible copy), live provider calls (forbidden in regressions).

## 7. DSH handoff (F1–F6 consumption contract)

- **Route:** `GET /training-runs/{run}/advisor-analysis` (name `runs.advisor-analysis`),
  `web` middleware, **no auth by design**, query string ignored. Disabled → 404 (Laravel default
  page, not JSON): show a quiet "off" line, **never retry**, never an error toast. 404 and 503 are
  not interchangeable — branch on status.
- **200 shape** (contract_version '1', all fields verified): `contract_version`, `career_id`,
  `career_revision` (16-hex), `turn` (the next turn number), `scenario` (**display label**,
  e.g. `Unity Cup`), `recommended_action` (`id/label/kind/eligibility` or null),
  `deterministic_action` (`id/label` or null), `decision{provider:'deterministic'|'clef',
  model (nullable; 'unknown' possible), status:'fallback'|'abstained'|'validated',
  fallback_used, preference, confidence (nullable)}`, `explanation`,
  `explanation_source:'deterministic'|'logfare'` (independent of decision state),
  `evidence[{id,path,heading,line,servers,citation}]`, `retrieval{degraded,lexical_count,
  semantic_count}`, `unknowns`, `conflicts` (always `[]`), `obligation`, `alternatives`,
  `blocked`, `generated_at` (UTC `gmdate('c')`).
- **503 body (exact):** `{"error":{"code":"advisor_analysis_unavailable","message":"The analysis
  could not be produced. The recommendation on the cockpit is unaffected."}}` — dismissible
  inline error; the deterministic rail on the page is untouched.
- **`preference` values are diagnostic provider figures — never rendered as win odds.**
- **Stale protection is the frontend's:** the backend guarantees only the cache key. A response in
  flight when a turn is logged still answers for the older turn; compare `career_revision`
  (and `turn`) and discard late responses.
- **Cockpit prop stays exactly `{decision, finale}`** on `/cockpit`; consume this route
  separately. Canonical specs: `advisor-api-contract.json` and
  `docs/audits/2026-11-22-advisor-ai-audit/14-api-contract.md`; boundary record ADR-0038
  (proposed).

## 8. Open items

- **ADR-0038 owner acceptance** — the only remaining gate on the boundary record; nothing else
  waits on it (config + tests enforce the boundary regardless).
- **Live-provider verification** — separate session pass; no paid inference in regression suites
  by design.
- **Observed, not fixed (owner call needed):** the corpus-index tests build per-PID SQLite files
  under `storage/app/private/advisor-index/` and never unlink them (176 files present at
  09:00 local). Candidate KI entry; also the reason those five tests fail under any sandbox that
  denies writes to `storage/`.
- Pre-existing (not mine): 9 PHPStan errors in peer's `LangfuseTraceService.php`; ~160
  working-copy entries from other sessions.
