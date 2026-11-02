# Audit report — M3 flow audit: career setup wizard (steps 1–6)

## 1. Identity

- Audit id: AUD-20261102-005
- Date: 2026-11-02
- Mode: **M3 flow audit** (multi-screen task open to close) with M2 per-screen sweep
- Scope: eight surfaces — 1) `GET /career/setup/scenario` (SCREEN-002, step 1) `resources/js/pages/Career/ScenarioSelect.vue` / `App\Http\Controllers\Career\ScenarioSelectController`; 2) `GET /career/setup/trainee` (SCREEN-003, step 2) `TraineeSelect.vue` / `TraineeSelectController`; 3) `GET /career/setup/trainee/{id}` (SCREEN-004, trainee profile sub-screen) `TraineeProfile.vue` / `TraineeProfileController`; 4) `GET /career/setup/target` (SCREEN-005, step 3) `BuildTarget.vue` / `BuildTargetController`; 5) `GET /career/setup/legacy` (SCR-CAR-008, step 4) `LegacySelect.vue` / `LegacySelectController`; 6) `GET /career/setup/deck` (SCR-CAR-009, step 5) `DeckSelect.vue` / `DeckSelectController`; 7) `GET /career/setup/preflight` (SCR-CAR-010, step 6) `Preflight.vue` / `PreflightController`; 8) intra-page sub-surfaces: `CardPicker` native `<dialog>` on DeckSelect; `AncestorEditor` two-parent blocks on LegacySelect. Per screen: page + controller read, `SCREEN_SPEC.md` where registered, browser spec where it exists.
- Out of scope: run creation backend beyond the `PreflightController`/`SetupDraft` write path, content strategy (scenario cap rows verified against the reference — clean), performance, AAA criteria, any fix.
- Frameworks applied: `design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **F-01 — "Create the run" is irreversible with no named confirmation.** Preflight's commit writes trainee, scenario, target, parents and deck and creates an Active run; `RunStatus` has no draft case and a run can only be retired. The button is gated only by `canStart` (scenario + trainee entered), and no confirmation is named. Severity 2, High.
- **F-02 — SetupDraft's one-draft-per-session sharing and expiry are undisclosed on every screen.** Two tabs share the single draft (last write wins) and session expiry loses it; no screen states either fact. Severity 2, High.
- **F-03 — ScenarioSelect's disabled Continue names no reason.** `props.selected === null` disables it with no `aria-describedby`/hint, unlike TraineeSelect's paired control ("Choose a trainee to continue." / `aria-describedby="trainee-continue-hint"`). Severity 2, High.
- **F-04 — The scenario empty state shows no example.** IA-5 asks an empty state to show what would fill it; the empty block names the absence and remedy only. Severity 1, High.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, 200% resize, focus visible/obscured, dialog focus trap and tab-restore after PUT — none measurable in this sandbox; the wizard has no rendered pass at all.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| ScenarioSelect (SCREEN-002, step 1) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |
| TraineeSelect (SCREEN-003, step 2) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |
| TraineeProfile (SCREEN-004) | yes | yes | n/a | n/a | src | src | named | named | named | named | src | none |
| BuildTarget (SCREEN-005, step 3) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |
| LegacySelect (SCR-CAR-008, step 4) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |
| DeckSelect (SCR-CAR-009, step 5) | yes | yes | yes | yes | src | src | named | named | named | named | src | none |
| Preflight (SCR-CAR-010, step 6) | n/a (all five gates required) | yes | yes | yes | src | src | named | named | named | named | src | none |
| CardPicker `<dialog>` on DeckSelect | yes | yes | yes | yes | src | src | named | named | named | named | src | none |
| AncestorEditor two-parent blocks on LegacySelect | yes | yes | yes | yes | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.1.1, 2.4.7, 3.3.1, 3.3.2, 3.3.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11/1.4.12 all UNMEASURED (no browser); 2.5.8 cited only as the house rule's floor, never as conformance; 2.5.7 N/A (no drag); 3.2.6/3.3.7 N/A; AAA not reached |
| Heuristics | H1, H2, H3, H4, H5, H6, H8, H9 | H7 no accelerators — systemic gap, §7 |
| Shneiderman | SHR-1, SHR-4 (N/A: no committed-turn closure on these screens), SHR-5 (N/A), SHR-6 (secondary on F-01), SHR-7 | SHR-2 (no expert path to serve); SHR-8 does not exist in the file |
| Tognazzini | TOG-6 (primary F-01), TOG-7 (secondary F-02), TOG-3 | TOG-1 (50 ms acknowledgement) UNMEASURED; TOG-2, TOG-4, TOG-5, TOG-7, TOG-8 N/A on read-mostly screens |
| Information architecture | IA-1, IA-5 | IA-2/IA-3/IA-6 structure between screens out of scope |
| Gestalt | GT-1, GT-2, GT-4, GT-6 | GT-3, GT-5 not reached |
| Norman | NRM-1, NRM-2 (primary F-03), NRM-4 (secondary F-06) | NRM-3, NRM-5, NRM-6 not reached |
| Laws of UX | UX-1 (44 px house rule, explicitly not conformance), UX-3, UX-4, UX-5, UX-9, UX-10 | UX-2, UX-6, UX-7, UX-8 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5, UI-6, UI-7 not reached |
| Gerhardt-Powals | GP-1, GP-2, GP-3 | GP-4, GP-5 not reached |

Framework coverage (applied and produced findings): `TOG-6`/`SHR-6`/`H3` (F-01), `H1`/`TOG-7` (F-02), `NRM-2` (F-03), `IA-5` (F-04), `H4`/`SHR-1` (F-05/F-06), `NRM-4` (secondary F-06), `H4` (F-07). Applied, measured clean from source (no finding): `H5`/`NRM-3`/`SHR-5` (reversal — no draft case in `RunStatus`, so irreversibility is real, covered by F-01); `SHR-7`/`SHR-8`-adjacent (stat defaults stored — `D18b` note); `GT-2`/`UI-4` (absent values carry a word and a title); `IA-1` (forward doors exist on every step); `WCAG 2.1.1`/`2.4.7`/`4.1.2`/`4.1.3` (links, buttons, labels, status regions present); `WCAG 1.4.1` (stale state carries word + border). `N/A` by the framework's own list: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing), `SHR-4` (no multi-step flow closure — the wizard is linear open-to-close), `IA-3`/`IA-6` (structure between screens out of scope), AAA criteria. Not reached: M4/M2 full rendered passes, `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 3.2.6`/`3.3.7` (not exercised).

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller source read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` anchor 2026-09-27 — all four scenario cap rows match line for line (§2.1), `base_cap` 1200 / `hard_cap` 2000 match, `live_on_global` dates match, capability verified against reference anchor 2026-09-27. Content track clean; no content discrepancy filed.
- Provenance caveats carried (§3.1 of the audit prompt): Gerhardt-Powals primary text paywalled, items 5/6 an unconfirmed split (nothing here rests on that boundary); IA prose unread, only names/summaries are the article's own (IA-5 used only as its derived sentence); Norman's book not fetched, quotes from the encyclopedia entry; Tognazzini sixteen are the original edition, 2014 count unverified; Laws of UX holds 29 (Zeigarnik from its own page); Laws of UI holds 13 and publishes Closure and Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `TOG-6` (secondary: `SHR-6`, `H3`) | Tognazzini / Shneiderman / Heuristics | /career/setup/preflight, commit action, populated | "Create the run" writes trainee, scenario, target, parents and deck and creates an Active run. There is no reversal (`RunStatus` has no draft case; a run can only be retired) and no confirmation is named. The button is only gated by `canStart` (scenario + trainee entered). | OBSERVED_SOURCE | `resources/js/pages/Career/Preflight.vue:454-461, 768-770`; `app/Services/Career/SetupDraft.php:22-24, 30-32` | 2 | High | Add a named confirmation stating finality (or that a created run can be retired), consistent with step 1's pre-commit reversibility table. Effort S. Acceptance: pressing "Create the run" shows a confirmation naming irreversibility/retire path, or finality is stated before commit. |
| F-02 | `H1` (secondary: `TOG-7`) | Heuristics / Tognazzini | All setup screens, session draft, populated | `SetupDraft` holds one draft per session: two tabs share it (last write wins) and session expiry loses it. No screen discloses either fact. | OBSERVED_SOURCE | `app/Services/Career/SetupDraft.php:26-28, 30-32`; no screen renders this disclosure | 2 | High | Disclose session scope on the wizard (single-session draft; another tab replaces it; expired session restarts the wizard). Effort S/M. Acceptance: the wizard states single-session draft and tab-sharing behavior before data loss can occur. |
| F-03 | `NRM-2` | Norman | /career/setup/scenario, populated, disabled primary | The disabled "Continue to trainee" button (`props.selected === null`) has no reason text — no `aria-describedby`, no beside-it hint. The equivalent control on the trainee screen does print the reason ("Choose a trainee to continue." with `aria-describedby="trainee-continue-hint"`). | OBSERVED_SOURCE | `resources/js/pages/Career/ScenarioSelect.vue:161-168` vs `TraineeSelect.vue:487-498` | 2 | High | Add the same hint + `aria-describedby` pairing to the scenario step's disabled Continue, or enable the button and surface the missing choice as an inline error on commit. Effort S. Acceptance: the disabled Continue names why it is disabled. |
| F-04 | `IA-5` | Information architecture | /career/setup/scenario, empty state | The empty branch ("No scenario is composed…") names the absence and remedy but shows no example of what would fill it — IA-5 asks an empty state to show an example of the contents. | OBSERVED_SOURCE | `resources/js/pages/Career/ScenarioSelect.vue:185-191` | 1 | High | Name a concrete example scenario (e.g. "URA Finale is the baseline and the one a first run is usually built on") in the empty block. Effort S. Acceptance: the empty state names at least one composable scenario. |
| F-05 | `H4` (secondary: `SHR-1`) | Heuristics / Shneiderman | LegacySelect forward door vs DeckSelect forward door, populated | LegacySelect's "Continue to deck" is a plain `<a href="/career/setup/deck">` that navigates without writing, so edits on step 4 are lost unless "Save Legacy" was clicked first; DeckSelect's "Continue to preflight" writes (`saveAndContinue`) then navigates. The exception is not named anywhere. | OBSERVED_SOURCE | `resources/js/pages/Career/LegacySelect.vue:329-340` vs `DeckSelect.vue:255-262, 389-396` | 1 | Medium | Make the legacy forward door save before navigating (like deck), or name the exception in screen copy ("changes on this step are not saved until you click Save Legacy"). Effort M. Acceptance: both forward doors behave identically, or the exception is stated on screen. |
| F-06 | `H4` (secondary: `NRM-4`) | Heuristics / Norman | LegacySelect and BuildTarget, all states | LegacySelect hard-codes hrefs (`/career/setup/target`, `/career/setup/deck`) and BuildTarget hard-codes `/preferences`. Every other page receives URLs server-side as props (`action`, `backUrl`, `scenarioUrl`, `profileUrl`). Violates "named routes and `route()` everywhere" (AGENTS.md §7). | OBSERVED_SOURCE | `resources/js/pages/Career/LegacySelect.vue:329-340`; `resources/js/pages/Career/BuildTarget.vue:622` | 1 | Medium | Replace literal hrefs with server-supplied props resolved through `route()`. Effort S. Acceptance: no literal setup URL remains in any setup page template. |
| F-07 | `H4` | Heuristics | /career/setup/trainee/{id}, populated | TraineeProfile hard-codes stat axes `['Speed','Stamina','Power','Guts','Wit']` (`statAxes`) while BuildTarget iterates `props.statOrder` from the server. If stat order changes, position means different things on different screens. | OBSERVED_SOURCE | `resources/js/pages/Career/TraineeProfile.vue:152` vs `BuildTarget.vue:75, 160-168` | 1 | Medium | Derive `statAxes` from `props.statOrder` on the profile page, or document as named exception. Effort S. Acceptance: both pages iterate the same server-provided stat order. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Dark- and light-theme body text meets 4.5:1 on every setup screen | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, risk edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the wizard screens | No browser; viewport never rendered | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on every control | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and the 1.4.12 text-spacing override lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every setup control | No keyboard run executed | Keyboard pass over each screen |
| Dialog focus trap on `CardPicker`'s native `<dialog>` | No rendered frame | Rendered pass over the dialog at open and close |
| Tab-restore after PUT (whole-deck replace on DeckSelect) | No interaction measured | Keyboard pass after a PUT replace |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Every target-size observation above is filed under the house rule (`UX-1`/`TOG-5`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The setup screens are deliberately dense; `H8` is about signal against noise, and every element carries a fact. No finding.
- **Closure vs honesty.** Absent values carry a word and a title (`N/A` with a reason, never a dash); `GT-2`/`UI-4` hold. No finding.
- **Feedback timing.** 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; the only acknowledgement surfaces are inline hints and status lines, whose latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on these screens. No finding.
- **Title-only disclosure (systemic).** Absence reasons and several `N/A` reasons are carried only in `title` attributes across most of the app; recorded as a gap rather than per-screen findings. F-03 names the most affected screen.
- **Code wins over stale rule.** Where code and a written rule disagree, the code wins (AGENTS.md §2); no such case required a ruling this pass.

Conflicts: none found this pass; no code-vs-rule disagreement required settlement.

Gaps confirmed in the register rather than filed per screen: `H3` (no undo on a committed turn — F-01 covers the screen-specific harm of the commit with no reversal), `H7` (no accelerators), `H10` (no help surface), IA-5 exemplar gap (F-04 covers the scenario screen; other empty states not re-filed), title-only disclosure channel (systemic), WCAG 1.4.3/1.4.11/etc all UNMEASURED.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. **F-01** (S): add a named confirmation stating finality (or that a created run can be retired) on "Create the run", consistent with step 1's pre-commit reversibility table. Check: pressing "Create the run" shows a confirmation naming irreversibility/retire path, or finality is stated before commit.
2. **F-02** (S/M): disclose single-session draft scope on the wizard — another tab replaces it (last write wins); expired session restarts the wizard. Check: the wizard states this before data loss can occur.
3. **F-03** (S): add the hint + `aria-describedby` pairing to the scenario step's disabled Continue, or enable the button and surface the missing choice as an inline error on commit. Check: the disabled Continue names why it is disabled.
4. **F-04** (S): name a concrete example scenario in the empty block (e.g. "URA Finale is the baseline and the one a first run is usually built on"). Check: the empty state names at least one composable scenario.
5. **F-06** (S): replace literal hrefs with server-supplied props resolved through `route()`. Check: no literal setup URL remains in any setup page template.
6. **F-07** (S): derive `statAxes` from `props.statOrder` on `TraineeProfile`, or document as named exception. Check: both pages iterate the same server-provided stat order.
7. **F-05** (M): make the legacy forward door save before navigating (like deck), or name the exception in screen copy ("changes on this step are not saved until you click Save Legacy"). Check: both forward doors behave identically, or the exception is stated on screen.

## 9. Change-audit re-check

Not owed (M3). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surfaces, states, viewports and themes stated, including what was not covered (per-page contrast, target size, reflow, keyboard, dialog focus).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- §3.1 provenance caveats carried (Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience).
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- `UMAMUSUME_REFERENCE.md` content cross-check recorded (anchor 2026-09-27; cap rows, `base_cap`/`hard_cap`, `live_on_global` dates all match; content track clean, no discrepancy filed).
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run.
- No severity 3/4 finding rests on inference (highest severity here is 2).
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (7 items).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
