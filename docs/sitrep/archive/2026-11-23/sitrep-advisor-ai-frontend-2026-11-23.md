# Sitrep: Advisor AI frontend — disclosure remediation and integration verification

**Agent:** Advisor AI Frontend Agent · **Date:** 2026-11-23 · **Branch:** `master` · **HEAD:** `3600741e3a67f06e542337de4ad7a517a7ffa188`
**Predecessor:** `docs/sitrep/sitrep-frontend-integration-signoff-2026-11-23.md` §8.1 (handed-off exception)
**Nothing staged, committed or pushed. HEAD did not move.**

---

## A. Executive result

The handed-off accessibility exception is **closed on measured rendered evidence**. The `Sources (n)` summary went from a 20px text line to a 44px interactive target at 1280 / 768 / 390 / 320px, with native disclosure semantics, keyboard reachability and a visible focus ring intact. The complete focused Advisor browser suite is green (15 passed), the build contains the correction, and static/contrast gates pass.

One pre-existing contract limitation is **documented, not fixed** (§ "Same-turn revision limitation" below); it does not affect the disclosure or turn-change behavior that was verified.

## B. Defect disposition

| Field | Value |
|---|---|
| Original problem | `Sources (n)` summary rendered at **279×20** @1280 and **322×20** @390 (R5 audit); project convention is a 44px interactive target. This is a TrainerDesk convention, **not** a universal WCAG 2.2 requirement. |
| Root cause | `<summary class="cursor-pointer text-sm font-semibold text-ink-strong">` had no `flex`/`min-h-11`, so the UA sized the native list-item summary to one 20px text line. |
| Reproduction (current bundle, before fix) | **1280: 279×20 · 768: 444×20 · 390: 322×20 · 320: 252×20** — real `getBoundingClientRect()` in Chromium, `.scratch-uma/advisor-agent-2026-11-23/before4.log`. |
| Exact change | One line in `resources/js/components/career/AdvisorAnalysisPanel.vue`: `<summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">`. The `flex` form uses the project's existing `summary.flex::before` marker restoration (`app.css:1204`), as other disclosures do. |
| After measurement | **1280: 279×44 · 768: 444×44 · 390: 322×44 · 320: 252×44** — same widths, no new overflow; `full3.log`. |
| Keyboard | Tab from the rail's primary door reaches the summary at every width; `role=status` of focus is not faked — computed outline is `solid` and ≥2px while focused. Enter opens, Space closes; citation text visible only when open. |
| Semantics preserved | Native `<details>/<summary>`; accessible name is still `Sources (1)`; one focus stop in the AI block; no nested controls; citation text remains text (no invented links); token classes unchanged. |

## C. Files changed

| File | Change |
|---|---|
| `resources/js/components/career/AdvisorAnalysisPanel.vue` | One-line `<summary>` class fix (the whole application diff). |
| `tests/browser/career-cockpit-advisor-ai.spec.ts` | Added (1) rendered 44px + keyboard + Enter/Space + focus + expand/collapse + surrounding-control matrix at 1280/768/390/320, and (2) three-theme × four-width geometry/overflow matrix. Corrected eight pre-existing brittle assertions to the current DOM without weakening behavior: cockpit URL suffix, four `Turn 1` exact matches, two straight-apostrophe copy matches, native `<summary>` lookup instead of a Chromium role mapping that exposes a closed summary as a `group`, generic single-primary-door check with an explicit "no AI candidate as door" assertion, AI-block scoping for the no-links check, and a bounded Tab walk to the ask button. |

No other file was edited. In particular `spa.ts`, `AdvisorRail.vue`, `Cockpit.vue`, `NextDecision.vue`, all backend files, shared CSS, config and dependencies were left untouched.

## D. Integration verification (current bundle)

- **Transport:** `spa.ts` still imports `./bootstrap`; `bootstrap.ts:9` installs `window.axios`; its only runtime consumer is `services/advisor/analysis.ts:207` (grep confirmed one runtime consumer). Sole Vite entry remains `spa.ts`. Route present at `routes/web.php:158`, name `runs.advisor-analysis`. A user click issues `GET /training-runs/{run}/advisor-analysis` and the panel processes the response.
- **200 / ready:** explanation, turn badge, attribution line, evidence citations, `preference: []` tolerance, Clef attribution with `explanation_source: logfare`. Diagnostic `0.7` / `70%` strings are asserted absent — preference is never shown as win probability.
- **404:** quiet terminal off-state copy, no `role="alert"`, no retry.
- **503:** inline `role="alert"` with the server's exact message and a working `Try again` (two calls, second returns 200).
- **Cancellation and races:** Cancel returns to idle and permits a new request; a late 200 released after a newer request is dropped by the monotonic request token; a response stamped turn 0 never overwrites the turn-1 view.
- **Deterministic Advisor:** block order (recommendation → AI block → standing explanation), one primary door, zero AI-generated links and zero non-GET writes; the protected cockpit `advisor` prop remains exactly `{decision, finale}`. `NextDecision.vue` was not modified.

### Same-turn revision limitation (unresolved by design, escalated)

The composable compares `payload.turn` with the turn captured at request time. It does **not** compare `career_revision`, because the cockpit exposes no current revision (verified: no `revision` in `Cockpit.vue` props or the controller's `advisorSection`, and the `advisor` prop must stay `{decision, finale}`). A decision-relevant reading or build-target edit that does not change the turn number therefore cannot invalidate a held AI answer through a revision signal. Turn comparison is **not** claimed to prove same-turn freshness. No fingerprint was invented in the frontend; existing turn invalidation is preserved. **Exact backend requirement:** expose the current `career_revision` through a non-`advisor` cockpit prop (or a dedicated lightweight endpoint) so `useAdvisorAnalysis` can discard a 200 whose `career_revision` differs from the page's current value without widening the protected prop contract. Owner: Hermes / backend.

## E. Browser evidence

- Route: `/training-runs/1/cockpit` on a per-invocation scratch SQLite database (`:8391`); `uma:db-guard` printed `targets canonical: no` on every run. Shared `database/database.sqlite` and `:8000` were never used.
- Responses: `page.route` fixtures from `analysisFixture()` — controlled 200 (empty/default and nonempty `evidence[]`), 404, 503, stale turn, gated delayed response. No live provider call.
- Themes: `light #c3d9f2`, `dark #050a1b`, `eva01 #1e1435` (measured `--color-page`); geometry and no-overflow asserted at each × 1280/768/390/320.
- Evidence files: `.scratch-uma/advisor-agent-2026-11-23/{before4,after3,full3}.log` and the sibling `*.sqlite` scratch files. No new screenshots were captured this session; the lead's pre-fix shots under `.scratch-uma/lead/r5/shots/` remain historical.
- Final result: **15 passed (3.1m)**, exit 0.

## F. Quality gates

| Command | Result |
|---|---|
| `npm run build` | exit 0; manifest `13:07:46`. Built `Cockpit-*.js` contains `es={class:"flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong"}` and `t("summary",es," Sources ("...)`. No source file is newer than the manifest. |
| `npm run typecheck` | exit 0, run twice (before and after the spec corrections). |
| `npm run lint:ui` | exit 0; `arbitrary-value` 208 (baseline 208), `card-in-card` 0, `dark-utility` 0, `debt-increase` 0. |
| `php artisan test tests/Feature/DesignTokensTest.php` | exit 0; **18 passed / 501 assertions**, 0 skips. |
| `PLAYWRIGHT_PORT=8391 PLAYWRIGHT_SCRATCH_DB=… npx playwright test tests/browser/career-cockpit-advisor-ai.spec.ts --reporter=line` | Before fix: 1 failed with measured 20px. After fix: **15 passed**, exit 0. |
| Not run | Full Pest suite, full Playwright suite, PHPStan, Pint (no PHP changed) — deliberate focused scope. |

## G. Concurrent work preservation

117 modified and 51 untracked entries were already present at reconciliation and were left alone. The pre-existing peer diff in `spa.ts` (axios import), `AdvisorRail.vue`, `Cockpit.vue` and all Hermes backend files is intact. No shared layout, theme token, config, migration or dependency was changed; no broad formatter was run over peer files. Nothing staged, committed, pushed, reset or merged.

## H. Repository status

Branch `master`, HEAD `3600741e3a67f06e542337de4ad7a517a7ffa188`, staged files 0, modified tracked files 117, untracked entries 51 plus this SITREP. No commits made.

## I. Sign-off verdict

**ADVISOR FRONTEND VERIFIED — ACCESSIBILITY EXCEPTION CLOSED**

The 44px disclosure target, keyboard behavior, controlled 200/404/503 handling, cancellation, stale-response protection, deterministic preservation and focused browser regression were all verified on a build containing the current source. The separate same-turn revision limitation above remains open on the backend contract and is not covered by this verdict.
