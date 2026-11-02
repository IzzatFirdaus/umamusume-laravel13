# Audit report — M3 flow audit with M2 per-screen sweep: Career Snapshot Entry

## 1. Identity

- Audit id: AUD-20261102-007
- Date: 2026-11-02
- Mode: M3 flow audit (multi-screen task open to close) with M2 per-screen sweep
- Scope: SCR-CAR-025 Career Snapshot entry — route GET /career/snapshot (career.snapshot), controller App\Http\Controllers\Career\SnapshotController entry method, page resources/js/pages/Career/Snapshot/Entry.vue, SCREEN_SPEC.md section SCR-CAR-025, tests/Feature/SnapshotEntryTest.php, tests/browser/career-snapshot.spec.ts.
- Out of scope: the Setup and Review snapshot screens (separate audits this pass), CreateSnapshotRun, the run-scoped cockpit surfaces, performance, AAA criteria, any fix.
- Frameworks applied: docs/framework-ui-ux/design-framework-index-2026-10-21.md first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per section 4 routing. Read from the files this run, not from memory.
- Rendered evidence: FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are OBSERVED_SOURCE or UNMEASURED.

## 2. Executive summary

- 1 finding, severity 1 minor. None severity 3 or 4.
- F-01 — The stepper links to Setup and Review before any snapshot exists (Entry.vue:19-23); Review renders only an empty state No snapshot has been entered yet Review.vue:107-110, so a Trainer can reach a data-less review step. The empty state recovers and names the remedy, but the stepper sets an expectation of data that is not met. H2 Match Between System and Real World, secondary GT-2 honest partial indicator.
- The screen is otherwise clean: one action one paragraph one way back; plain client vocabulary; the position you enter becomes the run's own position and absent figures are recorded as absent never guessed at Entry.vue:34-38; page titled via Head; the stepper marks Entry aria-current="step" and gives a way back to the dashboard; no write occurs here so no write-state feedback is required.
- Cross-screen note: the three snapshot screens render three different stepper markups (Entry.vue:19-23, Setup.vue:152-156, Review.vue:92-105). Not re-filed per screen; logged in section 7.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible or obscured none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Snapshot Entry career.snapshot | yes no data | N/A no write | N/A no fetch | N/A static page | src | src | named | named | named | named | src | career-snapshot.spec.ts |

Framework coverage matrix:

| Framework | Rules applied | N/A or not reached |
|---|---|---|
| WCAG 2.2 AA | 2.4.2, 2.4.6, 3.3.2 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only never conformance; 2.1.1 N/A no interactive beyond links; 3.3.1/3.3.3 N/A no input; 4.1.2 links only; AAA not reached |
| Heuristics | H1, H2, H3, H4, H6, H8 | H5 N/A no errors possible, H7 accelerators, H9 N/A no errors, H10 help surface |
| Shneiderman | SHR-7 | SHR-4 closure single screen no commit, SHR-6 systemic undo gap |
| Tognazzini | TOG-7 | TOG-1 50 ms UNMEASURED; TOG-2, TOG-3, TOG-4, TOG-5, TOG-6, TOG-8 |
| Information architecture | IA-1, IA-5 | IA-2, IA-3, IA-4, IA-6 |
| Gestalt | GT-2 | GT-3, GT-4, GT-5, GT-6 |
| Norman | NRM-1, NRM-6 | NRM-2, NRM-3, NRM-4, NRM-5 |
| Laws of UX | UX-3, UX-9, UX-10 | UX-1, UX-2, UX-4, UX-5, UX-6, UX-7, UX-8 |
| Laws of UI | UI-2, UI-6 | UI-1, UI-3, UI-4, UI-5, UI-7 |
| Gerhardt-Powals | GP-2, GP-8 | GP-1, GP-3, GP-4, GP-5 |

Framework coverage applied and produced findings: H2/GT-2 (F-01). Applied measured clean from source no finding: H1 page is static no async state needed; H3 doors back to dashboard and forward to Setup; H4 one token set min-h-11 links/buttons; H6 position and absence explained on the page; H8 every element carries a fact; IA-1 Entry has way back and doors; IA-5 empty state absent here but not applicable no data view; Norman NRM-1 links are signifiers; GP-2 position stated plainly; GP-8 only needed info shown Legacy and support deck link out rather than duplicate. N/A: 2.5.7 no drag, 2.1.4 no accelerators, 3.3.8 no auth, 3.3.1/3.3.3 no input, 1.2/1.4.2/2.3 no media. Not reached: WCAG resize/reflow passes, WCAG 3.2.6/3.3.7.

## 4. Evidence status and limits

- RENDERED_EVIDENCE = FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no rendered frame was obtained.
- Structural and copy claims are OBSERVED_SOURCE against the page source read this run; visual, contrast, target-size and reflow claims are UNMEASURED section 6.
- Provenance caveats carried audit protocol section 3.1: Gerhardt-Powals primary text paywalled; IA prose unread names summaries only; Norman's book not fetched quotes from encyclopedia entry; Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no Law of Experience it is Mental Model UX-17.
- Attribution rule applied: Closure/Continuity cited to uilaws-2026-10-21.md; Common Fate cited to gestalt-principles-2026-10-21.md; Fitts/Hick/Jakob cited to laws-of-ux-2026-10-21.md; 44 px described as a house rule exceeding 2.5.8 never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface or state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | H2 secondary GT-2 | Heuristics Gestalt | Snapshot Entry stepper forward doors | The stepper links to Setup and Review before any snapshot exists Entry.vue:19-23; Review renders only an empty state No snapshot has been entered yet Review.vue:107-110, so a Trainer can reach a data-less review step. The empty state recovers and names the remedy fill the form first, but the stepper sets an expectation of data that is not met. | OBSERVED_SOURCE | resources/js/pages/Career/Snapshot/Entry.vue:19-23; resources/js/pages/Career/Snapshot/Review.vue:107-110 | 1 | Medium | Gate the Review door or label the stepper steps so a data-less step does not look complete; keep the empty-state recovery. Acceptance: a Trainer cannot reach a review that holds nothing without an explicit no-data label. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every panel | No browser; intended ratios in app.css only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast borders focus rings meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; viewport never rendered | Rendered reflow pass at 320 px populated both themes |
| Target sizes of the 44 px house rule hold on every control min-h-11 links buttons | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize plus text-spacing pass at 320 px |
| Focus visible on the stepper links and actions | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log not findings

Settlements applied rather than filed as defects:

- 44 px versus 2.5.8. Target-size observations are filed under the house rule UX-1/TOG-5 and explicitly not as conformance, per wcag-2.2-accessibility-2026-10-21.md.
- Density versus minimalism. The screen is deliberately thin one section; H8 holds. No finding.
- Closure versus honesty. The stepper does not claim completion; GT-2 holds except F-01. No additional finding.
- Feedback timing. No async action here; N/A.
- Consistency versus exceptions. Cross-screen stepper markup differs across Entry Setup Review logged here not re-filed per screen. No finding.
- Title-only disclosure systemic. N/A no title attributes of note here.
- Systemic gaps confirmed in the register rather than filed per screen: H3/TOG-6 SHR-6 no undo systemic; H7 no accelerators; H10 no help surface; WCAG 1.4.3/1.4.11 etc all UNMEASURED.

Conflicts: none found this pass; no code-versus-rule disagreement required settlement.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. F-01 S: gate or label the Review step so a data-less review is not presented as complete. Acceptance: a Trainer cannot reach a review that holds nothing without an explicit no-data label.

## 9. Change-audit re-check

Not owed M3/M2. No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered per-page contrast, target size, reflow, keyboard, dialog focus.
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- section 3.1 provenance caveats carried Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience.
- Evidence capability recorded as FALSE; spawn ENOENT named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status and file:line location verified this run.
- No severity 3 or 4 finding rests on inference highest severity here is 1.
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort 1 item.
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed sections 1-10 in order; title line exact.
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
