# Sitrep: Advisor AI — full-stack consolidation and same-turn revision closure

**Agent:** Universal Full-Stack AI Advisor Engineer · **Date:** 2026-11-23
**Branch:** `master` · **HEAD:** `3600741e3a67f06e542337de4ad7a517a7ffa188` (unchanged)
**Predecessors:** `docs/sitrep/sitrep-hermes-advisor-ai-integration-2026-11-23.md`,
`docs/sitrep/sitrep-advisor-ai-frontend-2026-11-23.md`
**Nothing staged, committed or pushed.** 179 paths were already dirty on entry (peer work); none was
overwritten.

---

## A. Executive assessment

**VERIFIED WITH LIMITATIONS.**

The full-stack Advisor works as one system: the deterministic engine still ranks, the AI layer ranks
only the candidates the engine validated, provenance is independent and truthful, the endpoint's
200/404/503 behaviour holds, and the panel renders every state without ever acting for the Trainer.

The one inherited functional limitation — **same-turn staleness** — is now **closed within the
supported refresh model**. The cockpit exposes the career's revision as a sibling prop computed by
the same function the analysis fingerprints with, the composable compares it, and a response for a
career state the page no longer shows is dropped. Two new browser cases and five new backend cases
pin it.

The limitation that remains is not a defect: **cross-tab freshness is not claimed**. The revision
arrives as an Inertia prop, so a career edited in another tab is seen only after a visit or reload.
That is the architecture, not a gap in this slice, and it is now stated in three places (composable
header, spec footer, contract JSON) rather than implied.

`ADR-0038` remains **proposed**. Owner acceptance is not mine to grant, and no code change depends on
it.

---

## B. Completed implementation

### Inherited (verified present, not re-written)

| Area | State on entry |
|---|---|
| `app/Services/Advisor/**` (14 classes) | Complete: snapshot, candidates, retriever, Clef provider, explainer, index, evidence |
| `AdvisorAnalysis` pipeline + revision-keyed cache | Complete, `CONTRACT_VERSION = '1'` |
| `GET /training-runs/{run}/advisor-analysis` (`runs.advisor-analysis`) | Complete: 404 off-switch, 503 stable code |
| `tests/Feature/AdvisorRevisionTest.php` | Complete: 8 cases pinning fingerprint inputs |
| Typed client `services/advisor/analysis.ts` | Complete |
| `useAdvisorAnalysis` composable | Complete: token, cancel, turn guard, nav abort |
| `AdvisorAnalysisPanel.vue`, `AdvisorRail.vue`, `NextDecision.vue` | Complete after the 44px disclosure fix |
| 15 Advisor browser cases | Green on entry |

### Implemented now

1. **`AdvisorSnapshot::revision()`** — a public one-line entry point over the existing private
   `fingerprint()`. No second algorithm; the page and the response cannot disagree because they call
   the same function.
2. **`advisor_revision` cockpit prop** — a sibling of `advisor`, computed unconditionally. Not gated
   on `advisor_ai.enabled`: the revision describes the career, the switch describes the provider, and
   gating it would give the page a second rule for when the number means anything.
3. **`Cockpit.vue`** — prop typed `string`, forwarded to the rail.
4. **`AdvisorRail.vue`** — `careerRevision` prop forwarded to the panel.
5. **`AdvisorAnalysisPanel.vue`** — `careerRevision` prop threaded into the composable.
6. **`useAdvisorAnalysis.ts`** — third argument (a `Ref<string|null>`), a `revisionAtRequest` capture,
   a rejection branch beside the turn check, and a reactive `watch(revisionRef, invalidate)`.
7. **Tests** — 3 new backend cases in `AdvisorAnalysisRouteTest`, 1 new browser case, plus a
   fixture-honesty repair described in §F.

**Nothing outside the Advisor slice was touched.** No dependency, no design token, no config value,
no migration, no canonical game data.

---

## C. Backend verification

| Check | Result |
|---|---|
| Route | `GET /training-runs/{run}/advisor-analysis`, name `runs.advisor-analysis`, `routes/web.php:158` |
| Contract version | `'1'` — unchanged; no field was added to or removed from the response |
| 200 | Full payload or valid deterministic fallback, always |
| 404 | `abort_unless((bool) config('advisor_ai.enabled'), 404)` — proves the layer is off, nothing about the career |
| 503 | `{"error":{"code":"advisor_analysis_unavailable","message":"…"}}`; exception text asserted absent |
| Read-only | `AdvisorAnalysis::analyze()` has no write path; `AdvisorAnalysisRouteTest` asserts the run and its turn rows are byte-identical after the call |
| Provider states | Covered: validated, abstained, invalid choice, timeout, unavailable, explanation failure, retrieval degradation, null recommendation, deadline exhaustion |
| Caching | Key = `advisor-ai:{version}:{runId}:{revision}`; a decision-relevant edit misses by construction, never by expiry |
| Revision inputs | run id, scenario key, next turn, status, build target, latest turn's id/turn/energy/band/mood/five stats |
| Revision stability | Note edits do **not** move it (deliberate, pinned); race entries do not (they reach the analysis via the calendar read, not the run row) |

The fingerprint's deliberate blind spot — a recorded race entry — is unchanged and documented rather
than silently widened. Widening it would invalidate cached analysis for a change that does not alter
the ranked candidates.

---

## D. Frontend verification

| Check | Result |
|---|---|
| Transport | `spa.ts` → `bootstrap.ts` → `window.axios`; sole Vite entry; user-initiated only |
| States | idle, loading, ready, disabled (404), unavailable (503/timeout/network), cancelled |
| Provider attribution | Four distinct sentences; Clef-vs-engine difference stated explicitly when they differ |
| Evidence | Text citations only, never links; `[Global]` scope not doubled |
| Cancellation | Cancel returns to idle and permits a new request |
| Stale protection | Request token (monotonic) + turn + **revision** + abort-on-navigate + unmount |
| Accessibility | `<summary class="flex min-h-11 …">` measured 44px at 1280/768/390/320; native disclosure, keyboard-operable, focus ring ≥2px, axe clean |
| Themes / widths | `light #c3d9f2`, `dark #050a1b`, `eva01 #1e1435`; no horizontal overflow at any width |
| Deterministic rail | Untouched: `NextDecision.vue` not modified; one primary door; AI block renders below it |
| Automatic actions | Zero: no POST/PUT from the panel, no re-aimed door, no AI-generated link |

---

## E. Same-turn revision outcome

**Backend fingerprint inputs** — `AdvisorSnapshot::fingerprint()`: SHA256 over run id, scenario key,
`nextTurnNumber()`, status, `build_target`, and the latest turn's id, turn, energy, energy state/band,
mood, and five stats; truncated to 16 hex chars.

**Backend exposure** — `AdvisorSnapshot::revision($run, $latest)` is called by the cockpit's `show()`
and crosses the wire as `advisor_revision`. `advisor` stays exactly `{decision, finale}`: the
protected contract is asserted twice in `AdvisorAnalysisRouteTest` (`->missing('advisor.analysis')`
and `->has('advisor', …->count(2))`).

**Frontend consumption** — `Cockpit.vue` → `AdvisorRail` → `AdvisorAnalysisPanel` → `useAdvisorAnalysis`
as a `computed<string|null>`. Never reconstructed client-side.

**Reactive invalidation** — two watches: the inherited turn watch, and a new revision watch. The
revision watch is the one the turn watch cannot replace, because a corrected reading or a target
edit moves the revision and leaves the turn number alone.

**Regression tests** — backend: revision moves on a same-turn reading edit and on a build-target
edit; it does not move on a notes edit; the cockpit prop equals the response's `career_revision`;
the prop exists whether the switch is on or off. Browser: a response with the correct turn and a
foreign revision is dropped, the ask button returns, no badge renders, and the deterministic rail
above is untouched.

**Cross-tab limitation** — not implemented and **not claimed**. There is no broadcast channel and
this slice adds none. Stated in the composable header, the spec footer and `advisor-api-contract.json`.

---

## F. Test evidence

| Command | Exit | Result |
|---|---|---|
| `php artisan test tests/Feature/AdvisorRevisionTest.php` | 0 | 7 passed, 1 skipped (no race catalog slot seeded), 14 assertions |
| `php artisan test tests/Feature/AdvisorAnalysisRouteTest.php` | 0 | **7 passed**, 89 assertions (4 inherited + 3 new) |
| `php artisan test tests/Feature/AdvisorAiPipelineTest.php tests/Feature/AdvisorProvenanceTest.php tests/Feature/TrainerAdvisorTest.php` | 0 | **77 passed**, 270 assertions |
| `php artisan test tests/Feature/CareerCockpitTest.php` | 0 | 56 passed, 647 assertions (protected cockpit contract) |
| `php artisan test tests/Feature/DesignTokensTest.php tests/Feature/TokenPairHygieneTest.php` | 0 | 19 passed, 502 assertions |
| `npm run typecheck` | 0 | clean |
| `npm run lint:ui` | 0 | exit 0; pre-existing arbitrary-value debt in `Dashboard.vue` / `Veterans/Index.vue` / `Preferences/Edit.vue` unchanged; **zero Advisor violations** |
| `npm run build` | 0 | 33.64s, `Cockpit-DAfbMpwE.js` 53.24 kB |
| `npx playwright test tests/browser/career-cockpit-advisor-ai.spec.ts` | 0 | **16 passed** (2.9m), 1 worker, scratch DB `browser-scratch-8704-…`, `targets canonical: no` |
| `./vendor/bin/pint --test <3 files>` | 0 | PASS |
| `./vendor/bin/phpstan analyse <2 files>` | 0 | No errors |

**Fixture-honesty repair (part of D).** Making the panel compare revisions would have made every
existing browser case fail, because `analysisFixture()` hardcoded `career_revision: 'fixture-revision'`,
which matches no real career. Fixed by teaching the fixture to carry the page's own value:
`openCockpitAtTurn1()` now reads `advisor_revision` off Inertia's `script[data-page="app"]` payload
into a module-level slot, and `analysisFixture()` stamps it. No assertion was weakened; the same-turn
case overrides the value deliberately to prove the rejection path.

**Not performed:** live-provider runs (no paid inference in the regression suites, by design); the
full application suite (run scoped, per §10 of the directive); cross-tab synchronisation (no such
mechanism exists to test).

**Environmental note:** the Playwright run rebuilds the scratch database through the seeder chain
(~40s) before the first case. Expected and observed on every prior Advisor run; not a defect.

---

## G. Files changed

| Path | Change |
|---|---|
| `app/Services/Advisor/AdvisorSnapshot.php` | +13: public `revision()` delegating to the existing private `fingerprint()` |
| `app/Http/Controllers/Career/CockpitController.php` | +`AdvisorSnapshot` import; +`advisor_revision` prop with the sibling/rationale comment |
| `resources/js/pages/Career/Cockpit.vue` | +`advisor_revision: string` prop + doc; forwarded to `AdvisorRail` |
| `resources/js/components/career/AdvisorRail.vue` | +`careerRevision` prop, defaulted `null`, forwarded to the panel |
| `resources/js/components/career/AdvisorAnalysisPanel.vue` | +`careerRevision` prop; third argument to the composable |
| `resources/js/composables/useAdvisorAnalysis.ts` | +`currentRevision` parameter, `revisionRef`, `revisionAtRequest`, rejection branch, `watch(revisionRef)`; header doc updated |
| `tests/Feature/AdvisorAnalysisRouteTest.php` | +3 cases: prop equals response revision, same-turn edit moves it, prop present with switch off |
| `tests/browser/career-cockpit-advisor-ai.spec.ts` | +`cockpitRevision()` helper, `renderedRevision` slot, fixture stamping, 1 new case, footer note on cross-tab scope |
| `advisor-api-contract.json` | `stale_result_behavior` and `dsh_handoff` updated to describe the prop and the in-flight comparison |
| `docs/sitrep/sitrep-advisor-fullstack-2026-11-23.md` | this report |

The `git diff --stat` for these paths totals more than these edits because peers have uncommitted
work in `CockpitController.php` (`advisorAnalysis()`, `team_url`) and `Cockpit.vue`. Those hunks
predate this session and were left intact.

---

## H. Repository state

- **Branch:** `master` · **HEAD:** `3600741e3a67f06e542337de4ad7a517a7ffa188` — **did not move**
- **Staged:** nothing
- **Commits / pushes / resets / cleans / stashes:** none
- **Working tree:** dirty on entry and on exit — **179 paths**, overwhelmingly peer work predating
  this session. Not claimed clean.
- **Databases:** the suite used its own per-invocation scratch file
  (`.scratch-uma/sqlite/browser-scratch-8704-1795414841100.sqlite`); `uma:db-guard` printed
  `targets canonical: no`. `database/database.sqlite` was never touched.

---

## I. Remaining work

**Confirmed functional defects:** none found in the Advisor slice.

**Environmental limitations:**

1. `AdvisorRevisionTest` skips its race-entry case when no `race_catalog_slots` row is seeded in the
   unit-test database. The skip is guarded and honest; seeding it is a fixture decision, not a code fix.
2. `npm run lint:ui` prints pre-existing arbitrary-value debt in three unrelated pages. Exit code is
   0; none of it is Advisor code.

**Owner-gated decisions:**

1. **ADR-0038 remains `proposed`.** Implementation is verified; acceptance is the owner's, and this
   session deliberately did not self-accept it.
2. **Live-provider verification** (a real Clef and Logfare call) is a separate session pass. The
   suites are fixture-driven on purpose.

**Optional enhancements (not implemented, not required):**

1. Cross-tab freshness would need a broadcast channel or polling. Out of the approved architecture
   for a loopback single-Trainer tool; the refresh model is correct for this product.
2. The `conflicts` response array is always empty, reserved for future source-conflict detection.

---

## J. Sign-off verdict

The Advisor full stack is **VERIFIED WITH LIMITATIONS** — verified end to end for the same-page,
refresh-based model the product actually uses, with the two named limitations above (one skipped
fixture case, one owner-gated ADR) and the cross-tab boundary stated rather than papered over.

Same-turn freshness is **implemented and browser-tested**, not deferred. The deterministic engine
remains the authority, the AI layer remains optional and read-only, provenance remains independent,
and no component compiles-and-renders in place of an executed test.

Not production-ready in the deployment sense, and not claimed to be: this is a loopback-only tool
with no auth surface by design, and the AI layer ships switched off.
