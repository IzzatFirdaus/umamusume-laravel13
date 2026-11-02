# Audit report — M3 flow audit with M2 per-screen sweep: Career Snapshot Setup

## 1. Identity

- Audit id: AUD-20261102-008
- Date: 2026-11-02
- Mode: M3 flow audit (multi-screen task open to close) with M2 per-screen sweep
- Scope: SCR-CAR-026 Career Snapshot setup — route GET /career/snapshot/setup (career.snapshot.setup), controller App\Http\Controllers\Career\SnapshotController setup method, page resources/js/pages/Career/Snapshot/Setup.vue, Form Request App\Http\Requests\StoreSnapshotRequest, SCREEN_SPEC.md section SCR-CAR-026, tests/Feature/SnapshotEntryTest.php (setup behaviour), tests/browser/career-snapshot.spec.ts.
- Out of scope: the Entry and Review snapshot screens (separate audits this pass), CreateSnapshotRun domain action, performance, AAA criteria, any fix.
- Frameworks applied: docs/framework-ui-ux/design-framework-index-2026-10-21.md first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per section 4 routing. Read from the files this run, not from memory.
- Rendered evidence: FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are OBSERVED_SOURCE or UNMEASURED.

## 2. Executive summary

- 3 findings: F-01 severity 2 moderate, F-02 severity 1 minor, F-03 severity 0 cosmetic.
- F-01 — Setup submits the full payload including notes up to 5000 characters as a GET query string to the Review screen Setup.vue:116-119; StoreSnapshotRequest allows notes max 5000 StoreSnapshotRequest.php:60. A long note risks a 414 URI Too Long failure and exposes data in URL and history. GET is also semantically a read yet carries payload toward a commit. H5 Error Prevention.
- F-02 — The position error block displays only the first truthy of year/month/phase Setup.vue:215-217 using an or chain, so concurrent position failures hide all but one. H9 error identification precise, secondary WCAG 3.3.1.
- F-03 — The action prop route('career.snapshot.store') is declared Setup.vue:29 but never used in Setup; the submit posts to props.review via GET Setup.vue:116-119. The store URL lives only on Review. Dead prop, code clarity, severity 0.
- The screen is otherwise well built: three-way state Known/Unknown/Not provided with distinct words and treatments; unknown disables the input constraint NRM-3; required fields marked with asterisk and labelled 3.3.2; Back and Review snapshot as the single primary action; Legacy Lab and Support Cards link out rather than duplicate writes GP-8; draft re-hydrates form and unknown toggles on Back TOG-7 session resume.
- Cross-screen note: stepper markup differs across Entry Setup Review logged in section 7 not re-filed per screen.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible or obscured none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Snapshot Setup career.snapshot.setup | yes empty form | yes | yes validation | yes reading back | src | src | named | named | named | named | src | career-snapshot.spec.ts |

Framework coverage matrix:

| Framework | Rules applied | N/A or not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 2.4.6, 3.3.1, 3.3.2, 3.3.3, 4.1.2 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only never conformance; 2.5.7 N/A no drag; 3.3.7 N/A snapshot does not re-ask it commits what was given; AAA not reached |
| Heuristics | H1, H2, H4, H5, H6, H8, H9 | H3 exit Back present, H7 accelerators, H10 help surface |
| Shneiderman | SHR-5, SHR-7 | SHR-4 closure single screen no commit, SHR-6 systemic undo gap |
| Tognazzini | TOG-3, TOG-7 | TOG-1 50 ms UNMEASURED; TOG-2, TOG-4, TOG-5, TOG-6, TOG-8 |
| Information architecture | IA-1, IA-4 | IA-2, IA-3, IA-5, IA-6 |
| Gestalt | GT-1, GT-2 | GT-3, GT-4, GT-5, GT-6 |
| Norman | NRM-1, NRM-3 | NRM-2, NRM-4, NRM-5, NRM-6 |
| Laws of UX | UX-1, UX-3, UX-9 | UX-2, UX-4, UX-5, UX-6, UX-7, UX-8, UX-10 |
| Laws of UI | UI-2, UI-3, UI-6 | UI-1, UI-4, UI-5, UI-7 |
| Gerhardt-Powals | GP-2, GP-3, GP-8 | GP-1, GP-4, GP-5 |

Framework coverage applied and produced findings: H5 (F-01), H9/3.3.1 (F-02), code clarity (F-03). Applied measured clean from source no finding: H1 Reading the snapshot back… status during processing; H2 Global client vocabulary scenario trainee year month half; H4 one token set min-h-11; H5 constraint unknown disables input; H6 labels beside inputs; H8 every element carries a fact; H9 refusal messages plain; SHR-5 refused write leaves state intact draft re-hydrates; TOG-7 draft survives Back; GP-3 fieldStates computed summation; GP-8 Legacy and support deck link out; GT-1 cards bound one concept; GT-2 empty positions not provided honest. N/A: 2.5.7 no drag, 2.1.4 no accelerators, 3.3.8 no auth, 3.3.7 no redundant entry, 1.2/1.4.2/2.3 no media. Not reached: WCAG resize/reflow passes, WCAG 3.2.6/3.3.7.

## 4. Evidence status and limits

- RENDERED_EVIDENCE = FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no rendered frame was obtained.
- Structural and copy claims are OBSERVED_SOURCE against the page, controller and Form Request source read this run; visual, contrast, target-size and reflow claims are UNMEASURED section 6.
- Provenance caveats carried audit protocol section 3.1: Gerhardt-Powals primary text paywalled; IA prose unread names summaries only; Norman's book not fetched quotes from encyclopedia entry; Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no Law of Experience it is Mental Model UX-17.
- Attribution rule applied: Closure/Continuity cited to uilaws-2026-10-21.md; Common Fate cited to gestalt-principles-2026-10-21.md; Fitts/Hick/Jakob cited to laws-of-ux-2026-10-21.md; 44 px described as a house rule exceeding 2.5.8 never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface or state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | H5 | Heuristics | Snapshot Setup submit to review | Setup submits the full payload including notes up to 5000 characters as a GET query string to Review Setup.vue:116-119; StoreSnapshotRequest allows notes max 5000 StoreSnapshotRequest.php:60. A long note risks a 414 URI Too Long failure and exposes data in URL and history. GET is semantically a read yet carries payload toward a commit. | OBSERVED_SOURCE | resources/js/pages/Career/Snapshot/Setup.vue:116-119; app/Http/Requests/StoreSnapshotRequest.php:60 | 2 | High | POST the payload to Review or park the draft in the session and pass a reference; cap notes for the query if GET is kept. Acceptance: a long note cannot trigger a URI length failure. |
| F-02 | H9 secondary 3.3.1 | Heuristics WCAG | Snapshot Setup position errors | The position error block shows only the first truthy of year/month/phase using an or chain Setup.vue:215-217, so concurrent position failures hide all but one. | OBSERVED_SOURCE | resources/js/pages/Career/Snapshot/Setup.vue:215-217 | 1 | High | Render all position errors, for example loop over year/month/phase errors. Acceptance: concurrent position failures all display. |
| F-03 | code clarity | none hygiene | Snapshot Setup props | The action prop route('career.snapshot.store') is declared Setup.vue:29 but never used in Setup; the submit uses props.review via GET Setup.vue:116-119. The store URL lives only on Review. | OBSERVED_SOURCE | resources/js/pages/Career/Snapshot/Setup.vue:29, :116-119 | 0 | High | Remove the unused action prop from Setup, or use it for the commit. Acceptance: no declared prop without a template binding. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every panel | No browser; intended ratios in app.css only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast borders focus rings meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; viewport never rendered | Rendered reflow pass at 320 px populated both themes |
| Target sizes of the 44 px house rule hold on every control min-h-11 inputs selects checkboxes | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize plus text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every control | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log not findings

Settlements applied rather than filed as defects:

- 44 px versus 2.5.8. Target-size observations are filed under the house rule UX-1/TOG-5 and explicitly not as conformance, per wcag-2.2-accessibility-2026-10-21.md.
- Density versus minimalism. The state field grid is dense but every element carries a fact; H8 holds. No finding.
- Closure versus honesty. The three-way state is honest Not provided never guesses; GT-2 holds. No finding.
- Feedback timing. Reading the snapshot back… status during processing; latency unmeasured section 6.
- Consistency versus exceptions. Cross-screen stepper markup differs across Entry Setup Review logged here not re-filed per screen. No finding.
- Title-only disclosure systemic. N/A no title attributes of note here.
- Systemic gaps confirmed in the register rather than filed per screen: H3/TOG-6 SHR-6 no undo systemic; H7 no accelerators; H10 no help surface; WCAG 1.4.3/1.4.11 etc all UNMEASURED.

Conflicts: none found this pass; no code-versus-rule disagreement required settlement.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. F-01 S: stop sending the snapshot payload as a GET query string. Acceptance: a long note cannot trigger a URI length failure.
2. F-02 S: render all position errors not just the first. Acceptance: concurrent position failures all display.
3. F-03 S: remove the unused action prop from Setup. Acceptance: no declared prop without a template binding.

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
- Recommendations ordered by severity then confidence then effort 3 items.
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed sections 1-10 in order; title line exact.
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
