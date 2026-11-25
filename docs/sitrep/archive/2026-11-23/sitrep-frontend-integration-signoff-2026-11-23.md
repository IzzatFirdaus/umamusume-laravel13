# Sitrep: Frontend integration verification and sign-off — 2026-11-23

**Agent:** lead (main frontend implementation agent) · **Branch:** `master` · **HEAD:** `3600741e`
· **Date:** 2026-11-23 (Asia/Kuala_Lumpur) · **Predecessor:** `sitrep-frontend-rendered-verification-2026-11-22.md`

**Scope:** the R5 pass — verify the *combined* frontend build after concurrent peer changes, coordinate
the peer-owned Advisor accessibility item, execute the G-18 token-contrast gate for the first time,
review the cumulative R4/R5 diff, run focused rendered regression, and reach a sign-off disposition.

**Nothing was staged, committed, or pushed.** HEAD did not move during this pass; two peer commits
(`6dc1b41d`, `3600741e`) had landed during the preceding R4 pass.

---

## 1. Executive conclusion

The combined frontend builds, typechecks and lints clean **against the current tree**, and the one
defect this pass existed to find was found and fixed: the AI Advisor panel could never issue a
request because `window.axios` was never installed. Two cockpit browser cases failed for an
unrelated, peer-caused reason (a heading-name collision) and were corrected in test scope.

G-18 is now **executed**, not "unrun": 49 contract pairs across all three themes, 18 passed /
501 assertions, zero skips.

One known accessibility defect remains and is **handed off, not fixed**: the peer-owned
`AdvisorAnalysisPanel.vue` `Sources (n)` disclosure measures 279x20 / 322x20 against the project's
44px convention. The file is another workstream's untracked component and was still being modified
during this pass.

**Disposition: READY WITH DOCUMENTED EXCEPTIONS** — unqualified for the ten files this line touched;
contingent for the Advisor panel until its owner applies the handed-off fix.

| Workstream | Result |
|---|---|
| P1 Combined build | **Done.** `BUILD=0`, manifest `11:48:15` newer than every `resources/js` file (newest `11:36:01`). Includes the peer Advisor changes. |
| P1 TypeScript | **Done.** `TSC=0`. |
| P1 UI lint | **Done.** `LINT=0`; `arbitrary-value` 208 vs baseline 209; `card-in-card` 0; `dark-utility` 0; `debt-increase` 0. |
| P2 Advisor disclosure | **Handed off.** Still 20px after the owner's latest edit; reproducible measurement supplied. |
| P3 G-18 | **Done.** 4 passed / 439 assertions; full `DesignTokensTest` 18 passed / 501 assertions. |
| P4 Diff review | **Done.** R4 hunks 1 line each in the shared files; peer work isolated and preserved. |
| P5 Responsive regression | **Done.** 32/32 cells clean (8 routes x 4 widths). |
| R4.3 error flow | **Retained as partially verified**, cause unchanged. |

---

## 2. Repository state at report time (observed, not assumed)

| Fact | Value | How observed |
|---|---|---|
| Branch | `master` | `git rev-parse --abbrev-ref HEAD` |
| HEAD | `3600741e3a67f06e542337de4ad7a517a7ffa188` | `git rev-parse HEAD` |
| Staged files | **0** | `git diff --cached --name-only` |
| Modified tracked files | **117** (overwhelmingly peer work) | `git status --porcelain` |
| Untracked entries | **50** (peer work) | `git status --porcelain` |
| Build manifest | `public/build/manifest.json` at `11:48:15` when this pass verified it; **rebuilt by a peer to `12:27:06`** afterwards | `Get-Item` |
| Newer source than the current build | **none** at report time (newest is `resources/js/pages/Review/Index.vue` at `12:24:09`) | mtime sweep vs manifest |

This pass's footprint is exactly the ten files in §5. Everything else in the worktree belongs to
concurrent workstreams and was left untouched.

---

## 3. The integration defect this pass existed to find

| Field | Value |
|---|---|
| **Symptom** | The AI Advisor panel's request never left the browser. The panel rendered its request button, but pressing it settled immediately on the `unavailable` line: "The analysis could not be produced. The recommendation on the cockpit is unaffected." |
| **Measured evidence** | `typeof window.axios === 'undefined'` and `axiosGet === 'undefined'` on `/training-runs/1/cockpit`; zero XHR entries captured by a `page.on('request')` listener after the button press. |
| **Root cause** | `resources/js/bootstrap.ts:9` assigns `window.axios = axios`, but **nothing imported the module**. Its only importer was `resources/js/app.ts`, which was deleted with the Blade shell in commit `0ea8d43c` (`git show 0ea8d43c` shows `-import './bootstrap';` on the deleted file). `spa.ts` is the sole Vite entry (`vite.config.js:11`) and never imported it. The module was dead code while `services/advisor/analysis.ts` documented it as "the transport". |
| **Why every gate missed it** | `tsc` compiles the module; Vite builds it; the panel's `unavailable` branch is a legitimate, deliberately-designed state. Only a browser driven to `ready` exposes the gap. |
| **Fix** | One import in `resources/js/spa.ts`: `import './bootstrap';`, with the removal history stated in a comment above it. |
| **After evidence** | `GET http://127.0.0.1:8310/training-runs/1/advisor-analysis` fires; the panel reaches `ready` and renders "AI advisor / Turn 48 / The engine's own ranking, no AI consulted / … / Sources (2)". |

**Blast radius checked before editing:** `grep` over `resources/js` finds exactly one consumer of
`window.axios` (`services/advisor/analysis.ts`). Nothing else in the shell depends on it.

---

## 4. Second defect: cockpit heading collision (peer-caused, fixed in test scope)

Two cases in `tests/browser/career-cockpit.spec.ts` failed with:

```text
strict mode violation: getByRole('heading', { name: 'Advisor' }) resolved to 2 elements:
  1) <h2 id="career-advisor-heading" ...>Advisor</h2>
  2) <h3 ...>AI advisor</h3>
```

| Field | Value |
|---|---|
| Cause | The peer-owned `AdvisorAnalysisPanel.vue` adds `<h3>AI advisor</h3>` inside the rail that already carries `<h2 ...>Advisor</h2>` (`AdvisorRail.vue:50`). Playwright's `name` option is a **substring** match, so the bare query became ambiguous. |
| Proof it predates this pass | The `AI advisor` h3 rendered at **07:41** (captured in `.scratch-uma/lead/r5/diag.txt`), before this pass's `spa.ts` fix at **07:58**. |
| Correction | `exact: true` on the three `Advisor` matchers (lines 179, 388, 399). No application change; the app markup is correct and was left alone. |
| After evidence | Both previously failing cases re-run: **2 passed (42.6s)**. |

---

## 5. Files changed by this pass

Diffstat is `git diff HEAD --numstat`, additions/removals.

| File | +/- | Change | Ownership |
|---|---|---|---|
| `resources/js/spa.ts` | +7 / −0 | `import './bootstrap';` plus the removal-history comment | mine |
| `tests/browser/career-cockpit.spec.ts` | +3 / −3 | three `Advisor` heading matchers made `exact: true` (file was already peer-modified; only these three lines are mine) | mine, peer file |
| `tests/browser/career-race-decision.spec.ts` | +40 / −0 | R4's permanent scroll-region regression case (preserved) | mine (R4) |
| `resources/js/pages/Career/RaceDecision.vue` | +76 / −8 | R4 region role/name/tab stop + rationale comment | mine (R4) |
| `resources/js/pages/Career/RacePlanner.vue` | +9 / −1 | R4 conditional region on group cards | mine (R4) |
| `resources/js/pages/Career/InheritanceEvent.vue` | +34 / −2 | R4 region + comment | mine (R4) |
| `resources/js/pages/Career/EventDecision.vue` | +1 / −1 | R4 `min-h-11` on the retry disclosure | mine (R4) |
| `resources/js/pages/Career/Cockpit.vue` | +87 / −16 | **one line mine** (R4 `min-h-11` on Notes); the rest is peer BENO/N3.2 work | mixed |
| `resources/js/pages/Dashboard.vue` | +44 / −7 | **one line mine** (R4 `min-h-11` on the card note); the rest is peer BENO-DASH-001 | mixed |
| `resources/js/pages/Legacy/Builder.vue` | +2 / −2 | R4 `min-h-11` on two disclosures | mine (R4) |

In the two mixed files, `git diff HEAD -U0` filtered to summary rows returns exactly **one** added and
**one** removed line each, confirming the peer hunks are untouched.

Documentation updated (untracked, this line's own): `docs/roadmap/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md`
(gained a Phase R5 section and had its G-18 row closed) and
`docs/sitrep/sitrep-frontend-rendered-verification-2026-11-22.md` (gained an R5 section).

---

## 6. Verification actually executed

All browser measurement ran against an **isolated scratch database**
(`.scratch-uma/lead/r5/r5.sqlite`, `migrate --seed` + `UraFinaleRunsSeeder`) served on `:8310`.
The shared `database/database.sqlite` and the `:8000` dev server were never used; `:8000` answered
`200` on `/up` before and after.

### Static gates

| Gate | Command | Result |
|---|---|---|
| TypeScript | `npm run typecheck` | `TSC=0` |
| UI framework lint | `npm run lint:ui` | `LINT=0`; `arbitrary-value` **208** (baseline 209), `card-in-card` 0, `dark-utility` 0, `debt-increase` 0, `literal-length` 222 |
| Build | `npm run build` | `BUILD=0` twice; final manifest `11:48:15` |

### Contrast (G-18)

| Command | Result |
|---|---|
| `php artisan test --filter='browser contrast and token resolution'` | **4 passed, 439 assertions**, 46.12s |
| `php artisan test tests/Feature/DesignTokensTest.php` | **18 passed, 501 assertions, 0 skips**, 21.46s |

The gate resolves 102 declared tokens off `documentElement` per theme and asserts 49 contract pairs
across `light`, `dark` and `eva01`. This is a real run, not the zero-assertion false pass this
repository's KI notes warn about — the assertion count is quoted above.

### Browser gates

| Command | Result |
|---|---|
| `playwright test career-race-decision.spec.ts preferences.spec.ts career-cockpit.spec.ts` | **60 passed, 2 skipped, 2 failed** (8.8m). Both failures = the §4 collision. |
| `playwright test career-cockpit.spec.ts -g "names every absent value\|lays out at 1280"` (after fix) | **2 passed** (42.6s) |

### Purpose-built measurements (in `.scratch-uma/lead/r5/`)

| Probe | File | Result |
|---|---|---|
| Responsive regression, 8 routes x 1280/768/390/320 | `regress.txt` | 32/32 cells: one `h1`, 0 document overflow, 0 **visible** card-in-card, 0 unnamed/unfocusable scrollers |
| Disclosures + scroll regions | `disc.txt` | all non-inline summaries 44px and toggling; inline `N/A` 24px; 5 regions across 3 screens named, focused, 2px-ringed, ArrowRight-scrolled |
| Three themes via real Preferences Save | `themes.txt` | `#c3d9f2` / `#050a1b` / `#1e1435` page; three distinct palettes; EVA-01 keeps green `#72c068` and its own risk tone |
| Advisor panel reached `ready` and disclosure measured | `adv2.txt`, `adv3.txt` | `Sources (2)` = **279x20** @1280, **322x20** @390 |
| Pre-fix evidence of the dead transport | `axios.txt`, `diag.txt`, `url.txt` | `axiosType: undefined`, zero XHR |
| Post-fix evidence | `url2.txt` | XHR fires; panel `ready` |

**Theme colours, verbatim from `themes.txt`:**

```text
light  page #c3d9f2  ink #12304f  primary #2455c9  green #5d8f03  risk #b22323  card rgb(228,241,253)
dark   page #050a1b  ink #eaf2ff  primary #164bff  green #83b503  risk #ff6f3b  card rgb(18,34,79)
eva01  page #1e1435  ink #f0e9dc  primary #715591  green #72c068  risk #fc8d93  card rgb(52,40,91)
```

Screenshots: `.scratch-uma/lead/r5/shots/advisor-1280.png`, `advisor-390.png`.

---

## 7. Corrections carried forward from R4 (verified intact this pass)

Re-measured against the current build, not taken from the R4 report:

| R4 item | Current measurement |
|---|---|
| Race Decision region | name `available-heading`, 284 client / 506 scroll, focused, 2px solid ring, ArrowRight scrolled |
| Race Planner regions | 3 groups (`mandatory/upcoming/optional`), 284/413-420, all focused and scrolled |
| Inheritance region | `result-heading`, 284/309, focused and scrolled |
| Disclosure targets | `EventDecision` 44, `Cockpit` 44, `Dashboard` 44, `Legacy/Builder` 44 and 44 |
| `AbsenceValue` inline `N/A` | deliberately 24x24 — WCAG 2.2 SC 2.5.8's floor, not a defect |

Also confirmed unchanged: R4.1 theme persistence (all three palettes applied and survived reload via
the real Save), R4.2 Veteran comparison (indexed query and 4-column matrix), R3 screens.

---

## 8. Outstanding work

### 8.1 Peer-owned defect — Advisor disclosure (highest-value open item)

| Field | Value |
|---|---|
| Component | `resources/js/components/career/AdvisorAnalysisPanel.vue` (**untracked**, Advisor AI workstream) |
| Control | the `<summary>` inside the `Sources ({{ n }})` `<details>`, currently line 254 |
| Current markup | `<summary class="cursor-pointer text-sm font-semibold text-ink-strong">` |
| Measured target | **279x20** @1280, **322x20** @390 |
| Requirement | the 44px control convention every other disclosure trigger in the tree follows; R4.4 corrected five identical cases |
| Expected markup | `<summary class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-ink-strong">` |
| Keyboard / focus | native `<summary>`; keep the toggle, keep the tab stop, keep the base `outline-color` ring from `app.css:1091` |
| Regression check | assert `>= 44` height beside the panel's tests; reuse the R4.4 measurement |
| Why not fixed here | the file is another workstream's untracked component and was modified at **11:36** during this pass |

**Do not** claim global disclosure-target closure while this remains.

### 8.2 Owner-gated, untouched by design

| Item | Gate |
|---|---|
| `BENO-COCKPIT-001` — retire the `Career tools` disclosure | Owner must confirm the write doors exist first. |
| 768px minimum (`DESIGN.md` §2.3) | Proposal, not ratified. |
| `docs/roadmap/INDEX.md` registration | The live-roadmap convention needs an owner decision. This roadmap remains **unregistered**. |

### 8.3 R4.3 — retained limitation

The 419/500 documents render correctly and were verified as documents. They **cannot** be produced by
an ordinary browser navigation on this build: Laravel 13's `PreventRequestForgery::hasValidOrigin()`
returns true for `Sec-Fetch-Site: same-origin` **before** `tokensMatch()` runs. Measured: a top-level
POST with a deliberately stale `_token` answered `302`; the same POST with no session answered `419`.
Do not disable CSRF or add an exception route to close this.

### 8.4 Build-staleness caveat for the next session

This pass verified the `11:48:15` bundle, which contained every file it had changed. Afterwards the
tree moved again: the peer review workstream saved `resources/js/pages/Review/Index.vue` at `12:24:09`
and rebuilt the bundle at `12:27:06`.

**Every measurement in section 6 belongs to the `11:48:15` bundle, not to the current `12:27:06` one.**
The review screen was neither built nor measured by this pass. Do not read any claim here as covering
the newer bundle; if a review-screen question matters, measure it against `12:27:06` directly.

`tests/browser/global-setup.ts` refuses to run against a stale bundle by default. Use
`PLAYWRIGHT_FRESH_BUILD=1` only after confirming your own files are inside the build, exactly as this
pass did, or simply rebuild.

---

## 9. Deviations and things not verified

- **Not run:** full Pest suite, full Playwright suite, PHPStan, Pint. Deliberate — `AGENTS.md` §9
  cost-aware escalation; this was a frontend-only pass.
- **Not verified:** the Advisor panel against a **live** provider. Every `ready` measurement used a
  stubbed endpoint via `page.route`; no paid inference was invoked, and provider behaviour is the
  backend workstream's contract (ADR-0038, still Proposed).
- **Not verified:** the `window.axios` fix on any screen other than the cockpit — there is no other
  consumer, per the grep in §3.
- **Two skipped specs** in the 64-case run were not investigated; they are pre-existing skips in
  `career-cockpit.spec.ts`, not something this pass introduced.
- **A self-inflicted detour, recorded for honesty:** an early attempt to append to
  `career-race-decision.spec.ts` through a tool layer mangled backtick escaping and dropped a quote.
  It was repaired by rewriting the block deterministically and re-verified (`escapedBacktick=false`,
  `testCount=14`, then the case ran green). No residue remains.

---

## 10. Exact next actionable step

**For the Advisor AI workstream**, in order:

1. Apply the one-line `min-h-11` change to the `Sources (n)` `<summary>` in
   `resources/js/components/career/AdvisorAnalysisPanel.vue`.
2. Add a height assertion beside the panel's existing tests.
3. Re-run the panel measurement — expected `44` at both 1280 and 390.
4. Report back so the §8.1 exception can be struck and the frontend sign-off becomes unqualified.

**For any frontend agent resuming this line**, in order:

1. `git rev-parse HEAD` + `git status --porcelain` — confirm the tree still matches §2.
2. `npm run build && npm run typecheck && npm run lint:ui` — expect `BUILD=0 / TSC=0 / LINT=0`,
   `arbitrary-value` 208.
3. `php artisan test tests/Feature/DesignTokensTest.php` — expect **18 passed / 501 assertions**, 0 skips.
4. Re-measure the Advisor disclosure; strike §8.1 only on a measured 44.
5. Do **not** re-open R4.1/R4.2/R4.5 or the `bootstrap` import; they are verified.

---

## 11. Evidence index

| Path | Contents |
|---|---|
| `.scratch-uma/lead/r5/regress.txt` | 32-cell responsive sweep |
| `.scratch-uma/lead/r5/disc.txt` | disclosure targets + region focus/keyboard |
| `.scratch-uma/lead/r5/themes.txt` | three-theme palettes and card fills |
| `.scratch-uma/lead/r5/adv2.txt`, `adv3.txt` | Advisor disclosure measurement (before/after peer edit — both 20px) |
| `.scratch-uma/lead/r5/axios.txt`, `diag.txt`, `url.txt`, `url2.txt` | dead-transport evidence and the post-fix XHR |
| `.scratch-uma/lead/r5/g18.log`, `dt.log` | G-18 and full `DesignTokensTest` output |
| `.scratch-uma/lead/r5/specs.log`, `cockpit2.log` | the 64-case run and the 2-case re-run |
| `.scratch-uma/lead/r5/f-tsc.log`, `f-lint.log`, `f-build.log` | final static gates |
| `.scratch-uma/lead/r5/shots/` | Advisor screenshots at 1280 and 390 |
| `.scratch-uma/lead/r4/` | the R4 pass's 70 artifacts, still present |

---

## 12. Handoff summary

- **Verified current state:** `master` @ `3600741e`; build/typecheck/lint green; G-18 executed
  (18 passed / 501 assertions); 32/32 responsive cells clean; all three themes intact; every R4
  correction re-measured and holding.
- **Fixed this pass:** the AI Advisor's dead `window.axios` transport (one import in `spa.ts`), and a
  peer-caused heading collision in `career-cockpit.spec.ts` (three `exact: true` matchers).
- **Verified, not fixed:** R4.4/R4.5 corrections; R4.1/R4.2 journeys.
- **Remaining work:** one peer-owned 20px disclosure (`AdvisorAnalysisPanel.vue`), with the exact
  patch stated in §8.1.
- **Blockers:** none for this line. The Advisor item is an ownership boundary, not a technical block.
- **Owner-gated:** `BENO-COCKPIT-001`, the 768px minimum, roadmap index registration.
- **Nothing staged, committed, or pushed.**
- **Next step:** the Advisor workstream applies the one-line `min-h-11` fix and re-measures to 44.
