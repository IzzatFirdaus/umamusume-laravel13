# Audit report — M3 flow audit with M2 per-screen sweep: Race Decision

## 1. Identity

- Audit id: AUD-20261102-002
- Date: 2026-11-02
- Mode: **M3 flow audit** (multi-screen task open to close) with M2 per-screen sweep
- Scope: `SCR-CAR-013` Race Decision — route `GET /training-runs/{run}/races` (`runs.races.decision`), controller `App\Http\Controllers\Career\RaceDecisionController`, page `resources/js/pages/Career/RaceDecision.vue`, plus `SCREEN_SPEC.md` §SCR-CAR-013, `tests/Feature/CareerRaceDecisionTest.php`, `tests/browser/career-race-decision.spec.ts`.
- Out of scope: backend behaviour beyond the controller's own queries and `StoreRaceEntryRequest`, the Cockpit and Race Planner screens (separate audits this pass), performance, content strategy beyond source-data verification, AAA criteria, any fix.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn ENOENT`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.
## 2. Executive summary

- **F-01 — Calendar-entry refusal alert is not focused, unlike the manual-entry alert.** `enterForm.hasErrors` renders an alert (line 385-387) but no watcher moves focus to it; the manual form's alert is focused via `alertRef` + watcher (lines 106-118). A refused calendar entry is silently presented unless the Trainer happens to be looking at it. Severity 2, High.
- **F-02 — Dashes (`-`) are rendered for missing values instead of `N/A` with a `title`.** `race.fans_needed ?? '-'` (line 288), `selected.payout ? selected.payout.sp : '-'` (line 334) and `'+' + selected.payout.stat : '-'` (line 335) all print a bare dash, violating the repository rule that an unrecorded value renders as `N/A` with a disclosure title, never a dash. Severity 2, High.
- **F-03 — `selectedId` defaults to the first race even when it is blocked, and the detail area never states "cannot enter".** A blocked race can be the default selection; its detail panel shows facts but no clear ineligibility message beyond the disabled button's `title`. Severity 1, High.
- **F-04 — "scales" is unexplained copy in the 2nd/3rd-place payout cells.** The payout table prints `scales` for fans at 2nd and 3rd with no note explaining what scales means; only 1st place is sourced. Severity 1, High.
- **F-05 — `aptitudePill` prints `N/A` without a `title` explaining the absence.** When neither aptitude letter exists the pill shows `N/A` with no reason; the full label lives only on the parent cell's `title`. Severity 1, High.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible/obscured, dialog focus trap on the native `<dialog>` — none measurable in this sandbox.
## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Race Decision (SCR-CAR-013) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.1.1, 2.4.7, 3.3.1, 3.3.2, 3.3.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only; 2.5.7 N/A (no drag); 3.2.6/3.3.7 N/A; AAA not reached |
| Heuristics | H1, H2, H3, H4, H5, H6, H8, H9 | H7 accelerators (systemic gap) |
| Shneiderman | SHR-1, SHR-4, SHR-5, SHR-6, SHR-7 | SHR-2 |
| Tognazzini | TOG-1 (intent), TOG-3, TOG-4, TOG-6 | TOG-1 (50 ms) UNMEASURED; TOG-2, TOG-5, TOG-7, TOG-8 |
| Information architecture | IA-1, IA-4, IA-5 | IA-2, IA-3, IA-6 |
| Gestalt | GT-1, GT-2, GT-4 | GT-3, GT-5, GT-6 |
| Norman | NRM-1, NRM-2, NRM-4 | NRM-3, NRM-5, NRM-6 |
| Laws of UX | UX-1, UX-3, UX-4, UX-5, UX-9, UX-10 | UX-2, UX-6, UX-7, UX-8 |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5, UI-6, UI-7 |
| Gerhardt-Powals | GP-1, GP-2, GP-3 | GP-4, GP-5 |

Framework coverage (applied and produced findings): `SHR-1`/`H1`/`WCAG 3.3.1` (F-01), house N/A rule/`WCAG 3.3.2` (F-02), `NRM-2` (F-03), `H2`/`WCAG 3.3.2` (F-04), `GT-2`/`UI-4` (F-05). Applied, measured clean from source (no finding): `H1`/`NRM-5` (page-level loading/status/alert; in-flight "Entering…" label); `H2` (Global client vocabulary; `Surface not recorded`, `Band not recorded` rather than invented terms); `H3`/`TOG-6` (drawer writes to existing `runs.races.store`; result recording is a disclosure, not a modal stack); `H4`/`SHR-1` (one `.btn` vocabulary, one `min-h-11` target, doors via props); `H5`/`NRM-2` (blocked rows show `blocked_reason` on the disabled button; maiden gate reason named); `H6`/`NRM-4` (fixed column order; grade/surface/distance/aptitude in same position on every row); `H8` (every element carries a fact; the "Only the 1st-place figure is sourced" note prevents extrapolation); `H9` (error messages plain and constructive; readiness prints `N/A` with the ADR-0016 blocker named); `SHR-4` (the result disclosure closes on a recorded placement); `SHR-7` (readiness and payout figures on the decision screen); `GP-1`/`GP-3` (no arithmetic left to the Trainer — payout figures are sourced, not computed); `GT-2`/`UI-4` (absent values carry `N/A` where the rule is followed); `IA-1` (forward door `planner_url`, back door to cockpit); `IA-5` (empty states name absence + remedy + action). `N/A`: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing). Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 3.2.6`/`3.3.7` (not exercised), `WCAG 2.4.11` (focus not obscured — not measured).
## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn ENOENT`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller source read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Provenance caveats carried (audit protocol §3.1): Gerhardt-Powals primary text paywalled; IA prose unread (names/summaries only); Norman's book not fetched (quotes from encyclopedia entry); Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.
## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `SHR-1` (secondary: `H1`, `WCAG 3.3.1`) | Shneiderman / Heuristics / WCAG 2.2 | Race Decision, populated, error state | `enterForm.hasErrors` renders a page-level alert (line 385-387) but no watcher moves focus to it. The manual form's alert IS focused via `alertRef` + watcher (lines 106-118). The two refusal surfaces behave inconsistently; a calendar-entry refusal is not announced to a screen-reader user. | OBSERVED_SOURCE | `resources/js/pages/Career/RaceDecision.vue:106-118` (manual, focused) vs `:385-387` (calendar, not focused) | 2 | High | Add the same `hasErrors` watcher + `alertRef.focus()` pattern to `enterForm`. Acceptance: a refused calendar entry moves focus to its alert. |
| F-02 | house N/A rule (secondary: `WCAG 3.3.2`) | AGENTS.md / WCAG 2.2 | Race Decision, populated, table cells | `race.fans_needed ?? '-'` (line 288), `selected.payout ? selected.payout.sp : '-'` (line 334) and `'+' + selected.payout.stat : '-'` (line 335) print a bare dash for missing values. The repository rule (`AGENTS.md` §5) requires `N/A` with a `title`, never a dash. | OBSERVED_SOURCE | `resources/js/pages/Career/RaceDecision.vue:288, 334-337` | 2 | High | Replace every `?? '-'` / `? : '-'` with `AbsenceValue` or `N/A` + `title`. Acceptance: no bare dash remains in any data cell. |
| F-03 | `NRM-2` | Norman | Race Decision, populated, blocked row | `selectedId` defaults to the first non-blocked race, else `props.races[0]` (line 147). When every race is blocked the default selection is a blocked race; the detail panel (lines 311-367) shows its facts but no clear "cannot enter" message beyond the disabled button's `title` attribute. | OBSERVED_SOURCE | `resources/js/pages/Career/RaceDecision.vue:147, 311-367` | 1 | High | Name the ineligibility in the detail panel text (not only `title`) when the selected race is blocked. Acceptance: a blocked selected race states why it cannot be entered in body copy. |
| F-04 | `H2` (secondary: `WCAG 3.3.2`) | Heuristics / WCAG 2.2 | Race Decision, populated, payout table | The 2nd and 3rd-place payout cells print `scales` for fans (line 337) with no note explaining what "scales" means; only 1st place is sourced, and the note below (lines 343-348) explains 1st place only. | OBSERVED_SOURCE | `resources/js/pages/Career/RaceDecision.vue:337, 343-348` | 1 | High | Replace `scales` with "Not published" (or add a note stating 2nd/3rd payouts are not published by the client). Acceptance: no unexplained token in any data cell. |
| F-05 | `GT-2` (secondary: `UI-4`) | Gestalt / Laws of UI | Race Decision, populated, aptitude pill | `aptitudePill` returns `race.aptitude.distance ?? race.aptitude.surface ?? 'N/A'` (line 205-209). When neither letter exists the pill shows `N/A` with no `title`; the full surface/distance label lives only on the parent cell's `title` (line 284-287). | OBSERVED_SOURCE | `resources/js/pages/Career/RaceDecision.vue:205-209, 284-287` | 1 | High | Give the pill its own `:title` naming the absence reason, mirroring the cell's `title`. Acceptance: every `N/A` carries a `title` explaining why. |

No finding is filed twice for one defect; each row carries at most one primary rule.
## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every race row and detail panel | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, Mandatory pill edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the race table | No browser; the table is `border-collapse` inside `g-side` | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on `btn-sm` `min-h-11` controls | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing on the payout table | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on the Enter/Record buttons and the `<dialog>` | No keyboard run executed | Keyboard pass over the page and dialog |
| Native `<dialog>` focus trap and Escape-to-close on the manual-entry drawer | No rendered frame | Rendered pass over the dialog at open and close |
## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule (`UX-1`/`TOG-5`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The race table is deliberately dense; `H8` is about signal against noise, and every column carries a fact. No finding.
- **Closure vs honesty.** Absent values carry `N/A` where the rule is followed; `GT-2`/`UI-4` hold on the conforming cells. F-02 files the non-conforming cells.
- **Feedback timing.** 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; the in-flight "Entering…"/"Recording…" labels are the acknowledgement surfaces, whose latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on this screen. No finding.
- **Title-only disclosure (systemic).** `blocked_reason` and several `N/A` reasons are carried in `title` attributes; F-03 and F-05 name the most affected surfaces.
- **Readiness `N/A` is a documented absence, not a defect.** `readiness()` returns `N/A` with the `ADR-0016` blocker named (controller:367-373); this matches the Cockpit and Planner. No finding.
- **No undo after commit (systemic).** `H3` gap registered; the Race Decision has no reversal for a committed entry. Not re-filed per screen.

Conflicts: none found this pass; no code-vs-rule disagreement required settlement.

Gaps confirmed in the register rather than filed per screen: `H3` (no undo on a committed turn — systemic), `H7` (no accelerators — systemic), `H10` (no help surface — systemic), title-only disclosure channel (systemic), WCAG 1.4.3/1.4.11/etc all UNMEASURED.
## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. **F-01** (S): add the `hasErrors` watcher + `alertRef.focus()` to `enterForm`. Acceptance: a refused calendar entry moves focus to its alert.
2. **F-02** (S): replace every bare `'-'` with `N/A` + `title` (or `AbsenceValue`). Acceptance: no bare dash remains in any data cell.
3. **F-03** (S): name the ineligibility in the detail panel body copy when the selected race is blocked. Acceptance: a blocked selected race states why in body copy, not only `title`.
4. **F-04** (S): replace `scales` with "Not published" or add an explanatory note. Acceptance: no unexplained token in any data cell.
5. **F-05** (S): give the aptitude pill its own `:title` naming the absence. Acceptance: every `N/A` carries a `title`.

## 9. Change-audit re-check

Not owed (M3/M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (per-page contrast, target size, reflow, keyboard, dialog focus).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- §3.1 provenance caveats carried (Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience).
- Evidence capability recorded as FALSE; `spawn ENOENT` named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run.
- No severity 3/4 finding rests on inference (highest severity here is 2).
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (5 items).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
