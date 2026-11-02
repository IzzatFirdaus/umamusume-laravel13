# Audit report — M3 flow audit with M2 per-screen sweep: Career Cockpit

## 1. Identity

- Audit id: AUD-20261102-001
- Date: 2026-11-02
- Mode: **M3 flow audit** (multi-screen task open to close) with M2 per-screen sweep
- Scope: `SCR-CAR-011` Career Cockpit — route `GET /training-runs/{run}/cockpit` (`runs.cockpit`), controller `App\Http\Controllers\Career\CockpitController`, page `resources/js/pages/Career/Cockpit.vue`, plus `SCREEN_SPEC.md` §SCR-CAR-011, `tests/Feature/CareerCockpitTest.php`, `tests/browser/career-cockpit.spec.ts`.
- Out of scope: backend behaviour beyond the controller's own queries and Form Requests, the Race Decision and Race Planner screens (separate audits this pass), performance, content strategy beyond source-data verification, AAA criteria, any fix.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn ENOENT`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.
## 2. Executive summary

- **F-01 — "Skills planner" door is derived by string replacement on `run_url`, not a server `route()` prop.** `skillsUrl` computes `${props.run.run_url.replace(/\/cockpit$/, '')}/skills`; it happens to resolve to `runs.skills.planner` today but would break silently if the cockpit URL pattern changes. Severity 1, High.
- **F-02 — `teamLeague` computed carries a convoluted, redundant null-check.** `teamState.value?.team_league_placement === null || teamState.value === null` — the optional chaining is masked by the second operand, so the expression is correct but opaque and regression-prone. Severity 0, High.
- **F-03 — Header edit forms and the correction/delete forms are defined in script but never rendered.** `statusForm`, `scenarioForm`, `periodForm`, `identityForm`, `correctionForm`, `saveCorrection`, `deleteTurn`, `deleteRun` (Cockpit.vue:640-882) are left behind by the v3.0.0 surgery that moved these writes to dedicated pages (`RunSettings`, `TeamEdit`); nothing in the template binds them. Severity 1, High.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible/obscured — none measurable in this sandbox.
## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Cockpit (SCR-CAR-011) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.1.1, 2.4.7, 3.3.1, 3.3.2, 3.3.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only, never conformance; 2.5.7 N/A (no drag); 3.2.6/3.3.7 N/A; AAA not reached |
| Heuristics | H1, H2, H3, H4, H5, H6, H8, H9 | H7 accelerators (systemic gap) |
| Shneiderman | SHR-1, SHR-4, SHR-5, SHR-6, SHR-7 | SHR-2 (no expert path) |
| Tognazzini | TOG-3, TOG-4, TOG-6 | TOG-1 (50 ms) UNMEASURED; TOG-2, TOG-5, TOG-7, TOG-8 |
| Information architecture | IA-1, IA-4, IA-5 | IA-2, IA-3, IA-6 |
| Gestalt | GT-1, GT-2, GT-4 | GT-3, GT-5, GT-6 |
| Norman | NRM-1, NRM-2, NRM-4 | NRM-3, NRM-5, NRM-6 |
| Laws of UX | UX-1, UX-3, UX-4, UX-5, UX-9, UX-10 | UX-2, UX-6, UX-7, UX-8 |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5, UI-6, UI-7 |
| Gerhardt-Powals | GP-1, GP-2, GP-3 | GP-4, GP-5 |

Framework coverage (applied and produced findings): `NRM-1`/`H1` (F-01, F-03), code-clarity (F-02). Applied, measured clean from source (no finding): `H1`/`NRM-5` (page-level loading/status/alert regions present); `H2` (Global client vocabulary; `card_title` verbatim with brackets); `H3`/`TOG-6` (delete goes to `confirm_delete_url` first — confirmation before destruction); `H4`/`SHR-1` (one token set, one focus ring, doors via props); `H5`/`NRM-2` (blocked states name reasons; disabled delete has confirmation page); `H6`/`NRM-4` (position means same thing — turn denominator `N of 72` everywhere); `H8` (every element carries a fact; no ornament); `H9` (error messages plain, refusal copy names cause); `SHR-4` (flow closes on stated outcome); `SHR-7` (decision values on the decision screen); `GP-1`/`GP-3` (shortfall computed, e.g. `speed_to_win` `short_by`); `GT-2`/`UI-4` (absent values carry word + title, never dash); `IA-1` (every door is a prop or action entry); `IA-5` (empty state names absence + remedy + action); `WCAG 3.3.1` (refusal focuses the alert); `WCAG 4.1.3` (`role="status"`/`role="alert"` present). `N/A`: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing), `SHR-4` closure is the closed-run summary, not a multi-step flow. Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 3.2.6`/`3.3.7` (not exercised).
## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn ENOENT`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller source read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Provenance caveats carried (audit protocol §3.1): Gerhardt-Powals primary text paywalled; IA prose unread (names/summaries only); Norman's book not fetched (quotes from encyclopedia entry); Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.
- Prior audit cross-reference: AUD-20261101-001's F-01 (team rank pill reads `header.values.team_rank`) and F-02 (info banner product language) are both resolved in the current tree (`Cockpit.vue:524`, `Cockpit.vue:1042`) and are not re-filed.
## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `NRM-1` (secondary: `H4`) | Norman / Heuristics | Cockpit, populated, "Where to go" doors | `skillsUrl` (line 532) builds the skills URL by string replacement on `run_url` (`.replace(/\/cockpit$/, '')/skills`) instead of a server-supplied `route('runs.skills.planner', $run)` prop. It resolves correctly today but is a silent-breakage risk if the cockpit route pattern changes. | OBSERVED_SOURCE | `resources/js/pages/Career/Cockpit.vue:532` | 1 | High | Pass the skills URL as a server prop resolved through `route()`. Acceptance: no URL string manipulation remains in the cockpit template. |
| F-02 | code clarity | (none — hygiene) | Cockpit, all states | `teamLeague` (line 501-503) reads `teamState.value?.team_league_placement === null || teamState.value === null`. The optional chaining is redundant because the second operand already catches null; the expression works but is opaque and invites a future regression if the second condition is dropped. | OBSERVED_SOURCE | `resources/js/pages/Career/Cockpit.vue:501-503` | 0 | High | Simplify to `teamState.value === null || teamState.value.team_league_placement === null ? 'N/A' : String(teamState.value.team_league_placement)`. Acceptance: one null check, no optional chaining. |
| F-03 | `NRM-1` (secondary: `H1`) | Norman / Heuristics | Cockpit, all states | Write methods are defined in script but bound to no template control: `statusForm`/`saveStatus`, `scenarioForm`/`saveScenario`, `periodForm`/`savePeriod`, `identityForm`/`saveIdentity`, `correctionForm`/`saveCorrection`, `turnDeleteForm`/`deleteTurn`, `deleteForm`/`deleteRun` (lines 640-882). The v3.0.0 surgery moved these writes to dedicated pages; the cockpit's script still carries them as dead code. | OBSERVED_SOURCE | `resources/js/pages/Career/Cockpit.vue:640-882` | 1 | High | Remove the unrendered form definitions and their save/delete handlers, or bind them to template controls if they are still intended on this screen. Acceptance: no write method exists without a template binding. |

No finding is filed twice for one defect; each row carries at most one primary rule.
## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every cockpit panel | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, risk edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; viewport never rendered | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on every control | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every cockpit control | No keyboard run executed | Keyboard pass over the page |
| Advisor rail scroll container (`lg:max-h-[calc(100svh-1rem)]`) keeps focus reachable | No rendered frame | Rendered pass over the rail at open and scroll |
## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule (`UX-1`/`TOG-5`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The cockpit is deliberately dense; `H8` is about signal against noise, and every element carries a fact. No finding.
- **Closure vs honesty.** Absent values carry a word and a title (`N/A` with a reason, never a dash); `GT-2`/`UI-4` hold. No finding.
- **Feedback timing.** 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; acknowledgement surfaces are inline hints and status lines whose latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on this screen. No finding.
- **Title-only disclosure (systemic).** Absence reasons and several `N/A` reasons are carried only in `title` attributes across most of the app; recorded as a gap rather than per-screen findings.
- **Prior findings resolved.** AUD-20261101-001 F-01 (team rank) and F-02 (banner copy) are fixed in the current tree; not re-filed.
- **Code wins over stale rule.** Where code and a written rule disagree, the code wins (AGENTS.md §2); no such case required a ruling this pass.

Conflicts: none found this pass; no code-vs-rule disagreement required settlement.

Gaps confirmed in the register rather than filed per screen: `H3` (no undo on a committed turn — systemic), `H7` (no accelerators — systemic), `H10` (no help surface — systemic), title-only disclosure channel (systemic), WCAG 1.4.3/1.4.11/etc all UNMEASURED.
## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. **F-03** (S): remove the unrendered form definitions and save/delete handlers, or bind them to template controls. Acceptance: no write method exists without a template binding.
2. **F-01** (S): pass the skills URL as a server prop resolved through `route()`. Acceptance: no URL string manipulation remains in the cockpit template.
3. **F-02** (S): simplify the `teamLeague` null-check. Acceptance: one null check, no optional chaining.

## 9. Change-audit re-check

Not owed (M3/M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (per-page contrast, target size, reflow, keyboard, dialog focus).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- §3.1 provenance caveats carried (Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience).
- Evidence capability recorded as FALSE; `spawn ENOENT` named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run.
- No severity 3/4 finding rests on inference (highest severity here is 1).
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (3 items).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
