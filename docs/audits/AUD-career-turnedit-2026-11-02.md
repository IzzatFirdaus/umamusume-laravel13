# Audit report — M3 flow audit with M2 per-screen sweep: Turn Edit

## 1. Identity

- Audit id: AUD-20261102-006
- Date: 2026-11-02
- Mode: M3 flow audit (multi-screen task open to close) with M2 per-screen sweep
- Scope: Turn Edit door — route GET /training-runs/{run}/turns/{turn}/edit (runs.turns.edit), controller App\Http\Controllers\TrainingRunController editTurn method, page resources/js/pages/Career/TurnEdit.vue, the write targets PUT /training-runs/{run}/turns/{turn} (runs.turns.update) via StoreTurnEntryRequest, SCREEN_SPEC.md section SCR-CAR-011 cockpit correction door and turn rows, tests/Feature/TurnEditTest.php, tests/browser/turn-edit.spec.ts.
- Out of scope: the Cockpit screen itself, StoreTurnEntryRequest validation detail beyond what the page shows, performance, AAA criteria, any fix.
- Frameworks applied: docs/framework-ui-ux/design-framework-index-2026-10-21.md first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per section 4 routing. Read from the files this run, not from memory.
- Rendered evidence: FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are OBSERVED_SOURCE or UNMEASURED.

## 2. Executive summary

- 2 findings: F-01 severity 2 moderate, F-02 severity 1 minor. None severity 3 or 4.
- F-01 — No page-level loading or error state for the Inertia visit (TurnEdit.vue script lines 1-112). Unlike SaveVeteran, which uses useVisitState with role="status" loading and role="alert" error regions, TurnEdit renders nothing while loading and nothing on visit failure; a failed load looks like a blank page. H1 Visibility of System Status, secondary NRM-5.
- F-02 — The refusal alert block carries tabindex="-1" (TurnEdit.vue:149) suggesting intent to move focus, but no focus-handling code exists, so a keyboard user whose correction is refused is not directed to the alert. Review.vue handles this correctly with alertRef plus watch and nextTick Review.vue:53-59, so TurnEdit is inconsistent. Norman NRM-1 signifier, secondary H4 consistency.
- The screen is otherwise well built: the energy state watcher clears stale energy/band when the state changes so a correction cannot leave a reading nobody made R2-16 TurnEdit.vue:81-92; empty means not recorded and zero stays a real reading TurnEdit.vue:99-111; min="0" number constraints on stats; mood has an explicit "Not recorded" empty option; the save acknowledges inline Saving… and Saved. and focuses the refusal alert role="alert" tabindex="-1" on validation failure.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible or obscured none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Turn Edit runs.turns.edit | yes blank load | yes | yes refusal | no loading state | src | src | named | named | named | named | src | turn-edit.spec.ts |

Framework coverage matrix:

| Framework | Rules applied | N/A or not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 2.4.7, 3.3.1, 3.3.2, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only never conformance; 2.5.7 N/A no drag; 3.3.7 N/A no redundant entry on this door; AAA not reached |
| Heuristics | H1, H2, H4, H5, H8, H9 | H3 exit links present, H6 recognition values beside labels, H7 accelerators |
| Shneiderman | SHR-5, SHR-7 | SHR-6 systemic undo gap, SHR-4 closure single screen |
| Tognazzini | TOG-4, TOG-6 | TOG-1 50 ms UNMEASURED; TOG-2 trap double click not present; TOG-5, TOG-7, TOG-8 |
| Information architecture | IA-1 | IA-2, IA-3, IA-4, IA-5, IA-6 |
| Gestalt | GT-2 | GT-3, GT-4, GT-5, GT-6 |
| Norman | NRM-1, NRM-3, NRM-5 | NRM-2 blocked state not on this screen, NRM-4, NRM-6 |
| Laws of UX | UX-1, UX-3, UX-9 | UX-2, UX-4, UX-5, UX-6, UX-7, UX-8, UX-10 |
| Laws of UI | UI-2, UI-3 | UI-1, UI-4, UI-5, UI-6, UI-7 |
| Gerhardt-Powals | GP-1, GP-2 | GP-3, GP-4, GP-5 |

Framework coverage applied and produced findings: H1/NRM-5 (F-01), NRM-1/H4 (F-02). Applied measured clean from source no finding: H2 Global client vocabulary Mood Energy; H5 constraint vs validation energy state watcher clears stale bands; H8 every element carries a fact; SHR-5 refused write leaves entered state intact transform only sends on submit; GP-1 GP-2 shortfall and state computed by server; UI-3 state not colour alone mood uses words. N/A: 2.5.7 no drag, 2.1.4 no accelerators, 3.3.8 no auth. Not reached: WCAG resize/reflow passes, WCAG 3.2.6/3.3.7.

## 4. Evidence status and limits

- RENDERED_EVIDENCE = FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no rendered frame was obtained.
- Structural and copy claims are OBSERVED_SOURCE against the page and controller source read this run; visual, contrast, target-size and reflow claims are UNMEASURED section 6.
- Provenance caveats carried audit protocol section 3.1: Gerhardt-Powals primary text paywalled; IA prose unread names summaries only; Norman's book not fetched quotes from encyclopedia entry; Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no Law of Experience it is Mental Model UX-17.
- Attribution rule applied: Closure/Continuity cited to uilaws-2026-10-21.md; Common Fate cited to gestalt-principles-2026-10-21.md; Fitts/Hick/Jakob cited to laws-of-ux-2026-10-21.md; 44 px described as a house rule exceeding 2.5.8 never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface or state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | H1 secondary NRM-5 | Heuristics Norman | Turn Edit populated error loading | The page has no useVisitState loading or error region. While an Inertia visit is in flight the screen renders nothing, and a failed visit looks identical to a blank page. SaveVeteran.vue:182-185 shows the reference pattern this screen omits. | OBSERVED_SOURCE | resources/js/pages/Career/TurnEdit.vue script lines 1-112; template has no loading/error regions | 2 | High | Add useVisitState with role="status" Loading… and role="alert" error regions matching SaveVeteran.vue:182-185. Acceptance: a visit failure shows an alert, not a blank page. |
| F-02 | NRM-1 secondary H4 | Norman Heuristics | Turn Edit error refusal | The refusal alert block has tabindex="-1" TurnEdit.vue:149 but no code ever focuses it, so the signifier is a lie and a keyboard user is not directed to the refusal. Review.vue focuses its alert via alertRef plus watch/nextTick Review.vue:53-59, making TurnEdit inconsistent. | OBSERVED_SOURCE | resources/js/pages/Career/TurnEdit.vue:147-156; contrast resources/js/pages/Career/Snapshot/Review.vue:53-59 | 1 | Medium | Either remove tabindex="-1" or add focus handling so the refusal alert receives focus on validation failure. Acceptance: the refusal alert is focused when a correction is refused. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every panel | No browser; intended ratios in app.css only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast borders focus rings risk edge meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; viewport never rendered | Rendered reflow pass at 320 px populated both themes |
| Target sizes of the 44 px house rule hold on every control min-h-11 button inputs selects | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize plus text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every control | No keyboard run executed | Keyboard pass over the page |
| 50 ms click acknowledgement on Save correction | No timing instrumentation | Rendered or tooling latency pass |

## 7. Conflict and gap log not findings

Settlements applied rather than filed as defects:

- 44 px versus 2.5.8. Target-size observations are filed under the house rule UX-1/TOG-5 and explicitly not as conformance, per wcag-2.2-accessibility-2026-10-21.md.
- Density versus minimalism. The grid of stat inputs is dense but every element carries a fact; H8 holds. No finding.
- Closure versus honesty. Not reached; no partial indicator on this screen. No finding.
- Feedback timing. Inline Saving… and Saved. acknowledgement surfaces; latency unmeasured section 6.
- Consistency versus exceptions. No exceptions on this screen. No finding.
- Title-only disclosure systemic. N/A reasons carried only in title attributes recorded as a gap rather than per-screen findings.
- Systemic gaps confirmed in the register rather than filed per screen: H3/TOG-6 SHR-6 no undo on a committed turn correction; H7 no accelerators; H10 no help surface; WCAG 1.4.3/1.4.11 etc all UNMEASURED.

Conflicts: none found this pass; no code-versus-rule disagreement required settlement.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. F-01 S: add useVisitState loading and error regions to Turn Edit. Acceptance: a visit failure shows an alert, not a blank page.
2. F-02 S: focus the refusal alert on validation failure or remove tabindex="-1". Acceptance: the refusal alert is focused when a correction is refused.

## 9. Change-audit re-check

Not owed M3/M2. No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered per-page contrast, target size, reflow, keyboard, dialog focus.
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- section 3.1 provenance caveats carried Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience.
- Evidence capability recorded as FALSE; spawn ENOENT named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status and file:line location verified this run.
- No severity 3 or 4 finding rests on inference highest severity here is 2.
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort 2 items.
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed sections 1-10 in order; title line exact.
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
