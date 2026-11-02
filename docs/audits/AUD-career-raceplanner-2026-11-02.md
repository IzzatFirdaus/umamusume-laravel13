# Audit report — M3 flow audit with M2 per-screen sweep: Scenario Race Planner

## 1. Identity

- Audit id: AUD-20261102-003
- Date: 2026-11-02
- Mode: **M3 flow audit** (multi-screen task open to close) with M2 per-screen sweep
- Scope: `SCR-CAR-023` Scenario Race Planner — route `GET /training-runs/{run}/races/planner` (`runs.races.planner`), controller `App\Http\Controllers\Career\RacePlannerController`, page `resources/js/pages/Career/RacePlanner.vue`, plus `SCREEN_SPEC.md` §SCR-CAR-023, `tests/Feature/CareerRacePlannerTest.php`, `tests/browser/career-race-planner.spec.ts`.
- Out of scope: backend behaviour beyond the controller's own queries, the Cockpit and Race Decision screens (separate audits this pass), performance, content strategy beyond source-data verification, AAA criteria, any fix.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn ENOENT`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.
## 2. Executive summary

- **F-01 — The `optional` and `rival` race groups are built by the controller but never rendered.** The template (lines 235-303) renders only `mandatory` and `upcoming`; `optional` and `rival` (controller:202-266) are computed and transmitted as props but never displayed. Dead state — data exists but no UI surfaces it. Severity 2, High.
- **F-02 — The selection cap of 4 (`LIMIT`) is not communicated to the user.** `toggle()` silently caps selection with `.slice(0, LIMIT)` (line 93-97); a 5th click is ignored with no feedback, and the "Nothing to compare yet" prompt (line 311-319) names only the minimum ("at least two"), never the maximum. Severity 1, High.
- **F-03 — `orderedGroups` computed is unused.** Defined at line 115 but never referenced in the template, which iterates `props.groups.mandatory`/`props.groups.upcoming` directly. Dead code, no user impact. Severity 0, High.
- **F-04 — "Critical:" prefix on the deadline alert is not consistent with the Race Decision screen.** `deadline()` prefixes the alert text with "Critical:" (controller:599); Race Decision's deadline rows use state words without that prefix. Severity 1, Medium.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible/obscured — none measurable in this sandbox.
## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Race Planner (SCR-CAR-023) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.1.1, 2.4.7, 3.3.1, 3.3.2, 3.3.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only; 2.5.7 N/A (no drag); 3.2.6/3.3.7 N/A; AAA not reached |
| Heuristics | H1, H2, H4, H5, H6, H8 | H3 (no reversal on selection), H7 (systemic gap), H9 (no error surface on this read screen) |
| Shneiderman | SHR-1, SHR-4, SHR-5, SHR-7 | SHR-2, SHR-6 |
| Tognazzini | TOG-1 (intent), TOG-3, TOG-4, TOG-6 | TOG-1 (50 ms) UNMEASURED; TOG-2, TOG-5, TOG-7, TOG-8 |
| Information architecture | IA-1, IA-4, IA-5 | IA-2, IA-3, IA-6 |
| Gestalt | GT-1, GT-2, GT-4 | GT-3, GT-5, GT-6 |
| Norman | NRM-1, NRM-2, NRM-4 | NRM-3, NRM-5, NRM-6 |
| Laws of UX | UX-1, UX-3, UX-4, UX-5, UX-10 | UX-2, UX-6, UX-7, UX-8, UX-9 |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5, UI-6, UI-7 |
| Gerhardt-Powals | GP-1, GP-2, GP-3 | GP-4, GP-5 |

Framework coverage (applied and produced findings): `IA-4` (F-01), `H1`/`SHR-3` (F-02), `SHR-1`/`H4` (F-04). Applied, measured clean from source (no finding): `H1`/`NRM-5` (page-level loading/status/alert; the deadline alert states the obligation and turn); `H2` (Global client vocabulary; `Surface not recorded`, `Band not recorded`, `Not recorded` rather than invented terms); `H4`/`SHR-1` (one `.btn` vocabulary, one `min-h-11` target, doors via props); `H5`/`NRM-2` (blocked rows show `blocked_reason` beside the Compare button; maiden gate reason named); `H6`/`NRM-4` (fixed column order; same columns in comparison and alignment tables); `H8` (every element carries a fact; held figures print `N/A` with the ADR-0016 blocker named); `SHR-4` (the planner states the next obligation and its turn rather than promising the whole career); `SHR-7` (comparison and alignment figures on the planner screen); `GP-1`/`GP-3` (no arithmetic left to the Trainer — all figures are sourced or absent); `GT-2`/`UI-4` (absent values carry `N/A` + `title`, never dash); `IA-1` (forward door "This turn's race", back door to cockpit); `IA-5` (empty/absence states name absence + remedy). `N/A`: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing), `H9` (no error surface — read-only screen). Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 3.2.6`/`3.3.7` (not exercised), `WCAG 2.4.11` (focus not obscured — not measured).
## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn ENOENT`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller source read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Provenance caveats carried (audit protocol §3.1): Gerhardt-Powals primary text paywalled; IA prose unread (names/summaries only); Norman's book not fetched (quotes from encyclopedia entry); Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.
## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `IA-4` | Information architecture | Race Planner, populated, group regions | The controller builds four groups — `mandatory`, `upcoming`, `optional`, `rival` (RacePlannerController:202-266) — but the template renders only `mandatory` and `upcoming` (RacePlanner.vue:235-303). `optional` races (turn arrived, nothing recorded) and `rival` races (no column marks them) are transmitted as props with `total`/`empty`/`truncated` but never displayed. Dead state. | OBSERVED_SOURCE | `resources/js/pages/Career/RacePlanner.vue:235-303` vs `app/Http/Controllers/Career/RacePlannerController.vue:202-266` | 2 | High | Render the `optional` and `rival` group sections, or remove them from the controller payload. Acceptance: every group in `props.groups` has a corresponding template section. |
| F-02 | `H1` (secondary: `SHR-3`) | Heuristics / Tognazzini | Race Planner, populated, selection | `toggle()` caps selection at `LIMIT = 4` via `.slice(0, LIMIT)` (line 93-97); a 5th click is silently ignored. The empty-state prompt names only the minimum ("Choose at least two races", line 314) and never states the maximum. A user cannot tell why a 5th race won't join the comparison. | OBSERVED_SOURCE | `resources/js/pages/Career/RacePlanner.vue:93-97, 311-319, 322-325` | 1 | High | State the cap in the selection prompt (e.g. "Pick two to four races") and disable the Compare control or show a hint when four are selected. Acceptance: the maximum selection is communicated before a silent cap is hit. |
| F-03 | code clarity | (none — hygiene) | Race Planner, all states | `orderedGroups` (line 115) is computed from `props.groups` but never referenced; the template iterates `props.groups.mandatory`/`props.groups.upcoming` directly. Dead computed, no user impact. | OBSERVED_SOURCE | `resources/js/pages/Career/RacePlanner.vue:115` | 0 | High | Remove `orderedGroups` or use it in the template. Acceptance: no computed property without a consumer. |
| F-04 | `SHR-1` (secondary: `H4`) | Shneiderman / Heuristics | Race Planner, populated, deadline alert | `deadline()` prefixes the alert text with "Critical:" (RacePlannerController:599). Race Decision's deadline rows use state words (`Cleared`, `Entered`, `Not recorded yet`) without a "Critical" prefix. The same class of obligation-level alert is worded differently on the two screens. | OBSERVED_SOURCE | `app/Http/Controllers/Career/RacePlannerController.vue:599` vs `app/Http/Controllers/Career/RaceDecisionController.vue:311-360` | 1 | Medium | Align the deadline alert wording between the two screens (drop "Critical:" or add it consistently). Acceptance: obligation-level alerts use the same wording on Race Decision and Race Planner. |

No finding is filed twice for one defect; each row carries at most one primary rule.
## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every group table and comparison cell | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, `bg-green-tint` selection highlight) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the comparison table (`min-w-[40rem]`) | No browser; the table overflows horizontally | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on `btn-sm` `min-h-11` Compare buttons | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing on the alignment table | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on Compare buttons and Clear selection | No keyboard run executed | Keyboard pass over the page |
## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule (`UX-1`/`TOG-5`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The planner calendar is deliberately dense; `H8` is about signal against noise, and every column carries a fact. No finding.
- **Closure vs honesty.** Absent values carry `N/A` + `title`; the truncation notice names `First N of M` rather than implying completeness. `GT-2`/`UI-4` hold. No finding.
- **Feedback timing.** 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; the toggle's visual highlight (`bg-green-tint`) is the acknowledgement surface, whose latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on this screen. No finding.
- **Title-only disclosure (systemic).** `blocked_reason` and several `N/A` reasons are carried in `title` attributes; F-02 relates to selection-state communication, not title-only disclosure.
- **Held figures `N/A` are documented absences, not defects.** Win probability and expected risk print `N/A` with the `ADR-0016` blocker named (controller:517-529, 538-544); this matches the Race Decision. No finding.
- **No undo after commit (systemic).** `H3` gap registered; the planner is read-only (writes happen via RaceCard's drawer on the decision screen). Not re-filed per screen.
- **`rival` group is an honest absence.** The group states "No column marks a rival race" rather than listing races the client might never mark. No finding.

Conflicts: none found this pass; no code-vs-rule disagreement required settlement.

Gaps confirmed in the register rather than filed per screen: `H3` (no undo on a committed turn — systemic), `H7` (no accelerators — systemic), `H10` (no help surface — systemic), title-only disclosure channel (systemic), WCAG 1.4.3/1.4.11/etc all UNMEASURED.
## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. **F-01** (S): render the `optional` and `rival` group sections, or remove them from the controller payload. Acceptance: every group in `props.groups` has a corresponding template section.
2. **F-02** (S): state the maximum selection in the prompt and hint at the cap. Acceptance: "Pick two to four races" (or equivalent) appears before a silent cap is hit.
3. **F-03** (S): remove `orderedGroups` or use it in the template. Acceptance: no computed property without a consumer.
4. **F-04** (M): align deadline alert wording between Race Decision and Race Planner. Acceptance: obligation-level alerts use the same wording on both screens.

## 9. Change-audit re-check

Not owed (M3/M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (per-page contrast, target size, reflow, keyboard).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- §3.1 provenance caveats carried (Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience).
- Evidence capability recorded as FALSE; `spawn ENOENT` named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run.
- No severity 3/4 finding rests on inference (highest severity here is 2).
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (4 items).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
