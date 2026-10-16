# Scenario Field Metadata Close-Out Report

**Date:** 2026-10-16
**Scope:** Closing the three loose ends the wiring report (`scenario-field-metadata-wiring-report-2026-10-16.md`) left open: the SpiritBurstsPanel regression case, the browser harness's `public/build` staleness, and the Rule family display ruling. The wiring itself was not reopened.
**State:** working tree only. Nothing committed, staged, or pushed.

---

## 1. Precondition outcome

`git status --porcelain` on the nine target files: seven dirty, `KNOWN-ISSUES.md` clean, and `git log` unchanged since the wiring pass (`71bb5ed` at HEAD) — the concurrent Rest/Recreation session has not committed; its work is still live in the tree.

`tests/browser/career-cockpit.spec.ts` is dirty with a 22-line addition whose content is the Rest/Recreation slice's own new case ("routes Rest and Recreation to the Training screen holding their choice", mtime 03:03, inspected). Per the precondition rule that file was **not edited**; Task 1's case went into `tests/browser/career-cockpit-forms.spec.ts` instead — clean, already walks Unity Cup cockpits, and already carries the team-editor and bursts surfaces the case asserts against. The wiring pass's own dirty files (config, `ScenarioSelectController.php`, `ScenarioSelect.vue`, `ScenarioPanel.vue`, `CareerScenarioSelectTest.php`, `career-scenario-select.spec.ts`) match the wiring report's description of its own edits and were treated as extendable.

Mid-pass, the owner rewrote `AGENTS.md` (the `CONSTRAINTS.md` pointer moved to `docs/research-scratch/GOVERNANCE.md`); no task here depended on the changed text.

## 2. Task 1 — SpiritBurstsPanel regression case

**File:** `tests/browser/career-cockpit-forms.spec.ts`, new case `renders the Unity Cup scenario region without a runtime error, the bursts widget as a meter` (line 190).

**What it asserts, all on the rendered page:** the scenario strip renders (label + the `Scenario ruleset date` line); the `spirit_bursts` widget renders as the labelled `ResourceMeter` (`role="img"` named `Spirit Bursts: not recorded`); and no console or page error matching `TypeError` / `Cannot read properties of undefined` reaches the console. No source-string assertion exists.

**Proof that it catches the defect, both directions, on this tree:**

- *Green with the fix:* `PLAYWRIGHT_PORT=8143 … npm run test:browser -- tests/browser/career-cockpit-forms.spec.ts -g "without a runtime error"` → `1 passed (1.4m)` (`.scratch-uma/spirit-bursts-panel-regression/case-pass.log`).
- *Red with the defect reintroduced:* the `import SpiritBurstsPanel …` and `register('spirit_bursts', SpiritBurstsPanel);` lines were temporarily re-added to `ScenarioPanel.vue`, the bundle rebuilt, the same command re-run → `1 failed` (`.scratch-uma/spirit-bursts-panel-regression/case-fail.log`). The failing assertion is the meter's `toBeVisible` — with the bad registration the panel-shaped component crashes the widget subtree, so the meter never renders; the console assertion behind it is the second net. The temporary state was reverted (verified: zero added `spirit_bursts` registration lines in `git diff`) and the bundle rebuilt before any further gate ran.
- The harness-staleness lesson this case teaches is itself filed as KI-101 (§3): this case passes against a stale bundle, so it is a tripwire only when the bundle is fresh — which is exactly what the KI's proposed refusal check would guarantee.

## 3. Task 2 — Harness freshness defect

**Filed as `KNOWN-ISSUES.md` KI-101**: "The browser harness serves `public/build` without a freshness check, so a Vue change that crashes on first render can pass the browser gate" — FILED 2026-10-16, OPEN.

It records the symptom, the evidence (the `53e491a` merge's SpiritBurstsPanel registration crashing every Unity Cup Cockpit render, invisible until the wiring pass rebuilt the bundle), the proving commands, **the smallest fix — a staleness refusal in `tests/browser/global-setup.ts`**: compare `public/build/manifest.json`'s mtime against the newest `resources/js/**` mtime and abort with a one-line "run npm run build" message before any server boots (chosen over an unconditional pre-run rebuild or an opt-in flag, because a rebuild inside the harness would serialise concurrent sessions behind one Vite build — the KI-70 hazard — while the refusal costs only the session that needed it), who it impacts, and what closure does not cover. The harness was not touched; `playwright.config.ts` and `tests/browser/global-setup.ts` remain protected.

## 4. Task 3 — Rule family display ruling

**Option B applied.** `ScenarioSelectController` gains `ruleFamilyLabel(string $key): string` — the map `repetition → Repetition`, `team_rank → Team rank`, `repetition_plus_purchases → Repetition + purchases`, with a `ucfirst(str_replace('_', ' ', …))` fallback so a fifth scenario's own taxonomy value can never reach the card as a snake_case key. `cards()` now emits the label; the config keeps the taxonomy value as its single source; the Vue row is unchanged (it reads `card.rule_family`, which now carries the label).

`metadata.ruleset` was **kept** in config, untouched, per the brief — it remains unreachable from any screen and stays flagged as the wiring report's open question 3.

Tests updated to the labels: `CareerScenarioSelectTest.php`'s first case (four `rule_family` assertions) and `career-scenario-select.spec.ts`'s R2-05 case (Unity `Team rank`, Trackblazer `Repetition + purchases`). `ScenarioMetadataTest`'s template regex still matches (`card.rule_family`).

## 5. Files changed (this close-out pass only)

| File | Change |
| ---- | ------ |
| `app/Http/Controllers/Career/ScenarioSelectController.php` | `ruleFamilyLabel()` map added; `rule_family` payload now carries the humanized label |
| `tests/browser/career-cockpit-forms.spec.ts` | The regression case added (§2) |
| `tests/browser/career-scenario-select.spec.ts` | Two Rule family assertions repointed to the labels |
| `tests/Feature/CareerScenarioSelectTest.php` | Four `rule_family` assertions repointed to the labels |
| `KNOWN-ISSUES.md` | KI-101 appended |
| `docs/research-scratch-source/scenario-field-metadata-wiring-report-2026-10-16.md` | A dated erratum appended (the key-vs-label paragraphs are superseded) |
| `docs/research-scratch-source/scenario-field-metadata-closeout-report-2026-10-16.md` | This report |

`resources/js/pages/Career/ScenarioSelect.vue` needed no edit — the row already reads the payload key the controller now labels.

## 6. Test results (actual output)

- `npm run typecheck` → clean (`tsc --noEmit`, no output).
- `php artisan test --compact --filter="CareerScenarioSelectTest|ScenarioMetadataTest|ScenarioPanelTest"` → **37 passed (668 assertions)**, 244s.
- `vendor/bin/pint --test --format agent app/Http/Controllers/Career/ScenarioSelectController.php` → `passed`.
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` (full tree) → **3 errors, all pre-existing and unchanged**: `CockpitController::MONTHS is unused` (:120, was :110 before the peer's line drift) and two `nullsafe.neverNull` findings at the same `?->turnIndex ?? …` statement (:291, was :281). Same three codes the wiring pass recorded; not touched.
- Lore: `php tools/lore.php code` → **148 hit(s)**; `php tools/lore.php docs` → **449 hit(s), 75 exempt** — identical totals to the wiring pass's final run, so no new hits from the label strings, the case, or KI-101.
- Browser, session-private (`PLAYWRIGHT_PORT=8143`, scratch `browser-8143.sqlite`), one run over `career-scenario-select.spec.ts` + `career-cockpit.spec.ts` + `career-cockpit-forms.spec.ts` + `accessibility.spec.ts` → **42 passed / 2 failed (9.6m)**. The mandated trio is green, and the new regression case is green inside the same run. The two failures are classified below (§8).

## 7. Screenshots

Both captured at 1920×1080 on a session-private serve (port 8145, scratch DB seeded with the harness's own `migrate --seed` recipe, server reaped in the same command):

1. `.scratch-uma/spirit-bursts-panel-regression/cockpit-unity-cup-no-crash.png` — the Unity Cup Cockpit after a full wizard walk. The no-crash behaviour it depicts is proven by the regression case's assertions in the same bundle state (meter renders, zero TypeError/console errors); the capture script's own walk and waits completed clean.
2. `.scratch-uma/spirit-bursts-panel-regression/scenario-select-rule-family.png` — the Scenario Select with Unity Cup's facts disclosure open, showing the Rule family row. The rendered value is pinned by `CareerScenarioSelectTest` (`Team rank`) and by the browser spec's exact-text assertion in the same bundle state.

## 8. Documentation drift and classified failures (flagged, not fixed)

1. **Two pre-existing red browser cases, proven at HEAD, not this pass's.** In `career-cockpit-forms.spec.ts`, `records the Unity Cup team state through the team editor` (:156) and `closing the team editor without saving records nothing` (:222) fail on `strict mode violation: locator('details').filter({ has: getByText('Edit team state') }) resolved to 2 elements`. Both 'Edit team state' texts exist at HEAD — `Cockpit.vue:764` (its own wrapper disclosure, committed `ae8acaf`) and `UnityCupEditor.vue:80` (the component's own internal summary, committed `63d1cdd`) — so the page carries two identically-summaried disclosures since the cockpit-wt arc, and these two tests have been red since then for any session that ran them. The peer's uncommitted `Cockpit.vue` diff (hunks at :546/:1053) does not touch the region. The fix belongs to the cockpit-wt slice's owner — either the spec's locator is scoped to the wrapper or one of the two summaries is a duplicate the product does not want; neither is this pass's call. **This is the third landed-red defect class from that arc** (after the SpiritBurstsPanel registration and the reverted aria-label, both fixed on 2026-10-16).
2. **The wiring report's key-vs-label paragraphs** were superseded by this ruling and now carry a dated erratum rather than a silent edit.
3. `SCREEN_SPEC.md`, `docs/proposals/*`, `docs/UMAMUSUME_REFERENCE.md` and the ADRs were not touched; the SCR-002 text already says the family row "reads the entry's `facility_level_source`", which the label derivation does not contradict — no drift to flag there.
4. The owner's mid-pass `AGENTS.md` rewrite (pointer consolidation into `GOVERNANCE.md`) was observed and did not affect these tasks.

## 9. Explicit stop

All three tasks landed and every gate the brief names has run. No commit, no stage, no PR; the only writes outside the change set were the session-private `.scratch-uma/spirit-bursts-panel-regression/` artifacts and the gitignored `public/build` rebuilds the regression proof required.

PLAN COMPLETE — SpiritBurstsPanel has a regression case; the harness freshness defect is filed; the Rule family row renders a humanized label. Two screenshots attached.
