# Audit report — M3 flow audit with M2 per-screen sweep: Save Veteran

## 1. Identity

- Audit id: AUD-20261102-004
- Date: 2026-11-02
- Mode: M3 flow audit (multi-screen task open to close) with M2 per-screen sweep
- Scope: SCR-VET-003 Save Veteran — route GET /training-runs/{run}/veteran (runs.veteran), written by POST (runs.veteran.store), controller App\Http\Controllers\Career\SaveVeteranController, page resources/js/pages/Career/SaveVeteran.vue, Form Request App\Http\Requests\StoreVeteranRequest, SCREEN_SPEC.md section SCR-VET-003, tests/Feature/SaveVeteranTest.php, tests/browser/career-save-veteran.spec.ts.
- Out of scope: the Veteran library list/detail (veterans.index/veterans.show), the Legacy Lab, RecordVeteran domain behaviour beyond the controller call, performance, AAA criteria, any fix.
- Frameworks applied: docs/framework-ui-ux/design-framework-index-2026-10-21.md first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per section 4 routing. Read from the files this run, not from memory.
- Rendered evidence: FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are OBSERVED_SOURCE or UNMEASURED.

## 2. Executive summary

- 2 findings, both severity 1 minor, none severity 3 or 4.
- F-01 — role="alert" used for a non-error blocked precondition message (SaveVeteran.vue:195): an assertive live region overstates a refusal as an alarm.
- F-02 — Saving overwrites an existing Veteran's tags and notes with no confirmation and no reversal path from this screen (SaveVeteran.vue:412, SaveVeteranController.php:147-154): TOG-6 undo absent confirmation named obligation is not met, and the evaluation gulf what will be replaced is not named.
- The screen is otherwise well built: page-level loading and error states, inline save acknowledgement, tag chips with aria-pressed plus a visible mark not colour alone, N/A with title for held figures, read-back prefill from an existing row no redundant entry, named absences, and a stated outcome on commit flash on the detail page SHR-4 closure.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible or obscured none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Save Veteran (SCR-VET-003) | yes blocked | yes | yes | yes | src | src | named | named | named | named | src | career-save-veteran.spec.ts |

Framework coverage matrix:

| Framework | Rules applied | N/A or not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 3.3.1, 3.3.2, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only never conformance; 2.5.7 N/A no drag; 3.3.7 satisfied by prefill; AAA not reached |
| Heuristics | H1, H2, H4, H5, H6, H8, H9 | H3 undo systemic gap, H7 accelerators, H10 help surface |
| Shneiderman | SHR-4, SHR-5, SHR-7 | SHR-6 systemic undo gap, SHR-2 |
| Tognazzini | TOG-4, TOG-6 | TOG-1 50 ms UNMEASURED; TOG-2, TOG-3, TOG-5, TOG-7, TOG-8 |
| Information architecture | IA-1, IA-5 | IA-2, IA-3, IA-4, IA-6 |
| Gestalt | GT-2, GT-4 | GT-3, GT-5, GT-6 |
| Norman | NRM-2, NRM-6 | NRM-1, NRM-3, NRM-4, NRM-5 |
| Laws of UX | UX-1, UX-4, UX-9, UX-10 | UX-2, UX-3, UX-5, UX-6, UX-7, UX-8 |
| Laws of UI | UI-2, UI-3, UI-4 | UI-1, UI-5, UI-6, UI-7 |
| Gerhardt-Powals | GP-1, GP-2, GP-3 | GP-4, GP-5 |

Framework coverage applied and produced findings: NRM-6/H9 (F-02), 1.3.1/H9 (F-01). Applied measured clean from source no finding: H1 loading/error/acknowledgement regions present; H2 Global client vocabulary Wit Spark; H4 one token set min-h-11 buttons; H5 refused write leaves entered state intact prefill on return; H6 held figures and absences read back; H8 every element carries a fact; SHR-4 flow closes on a stated outcome flash; SHR-7 stat values on the screen that tags them; GP-1/GP-3 shortfall not computed held stated as held; GT-2/UI-4 absent values carry word plus title never dash; IA-1 doors are props/actions; IA-5 empty blocked state names absence remedy action; WCAG 3.3.1 refusal names the cause; WCAG 4.1.3 role status/alert present. N/A: 2.5.7 no drag, 2.1.4 no accelerators, 3.3.8 no auth, 1.2/1.4.2/2.3 no media audio flashing. Not reached: WCAG 1.4.4/1.4.10/1.4.12 no resize pass, WCAG 3.2.6/3.3.7 re-check.

## 4. Evidence status and limits

- RENDERED_EVIDENCE = FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no rendered frame was obtained.
- Structural and copy claims are OBSERVED_SOURCE against the page, controller and Form Request source read this run; visual, contrast, target-size and reflow claims are UNMEASURED section 6.
- Provenance caveats carried audit protocol section 3.1: Gerhardt-Powals primary text paywalled; IA prose unread names summaries only; Norman's book not fetched quotes from encyclopedia entry; Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no Law of Experience it is Mental Model UX-17.
- Attribution rule applied: Closure/Continuity cited to uilaws-2026-10-21.md; Common Fate cited to gestalt-principles-2026-10-21.md; Fitts/Hick/Jakob cited to laws-of-ux-2026-10-21.md; 44 px described as a house rule exceeding 2.5.8 never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface or state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | 1.3.1 secondary H9 | WCAG Heuristics | Save Veteran blocked state | The blocked precondition block prints its refusal in p role="alert" SaveVeteran.vue:195. role="alert" is an assertive live region reserved for errors; a not Completed yet refusal is not an error, so the role overstates severity and can alarm assistive tech users. | OBSERVED_SOURCE | resources/js/pages/Career/SaveVeteran.vue:193-195 | 1 | High | Replace role="alert" with a plain paragraph or role="status"/aria-describedby; reserve role="alert" for actual save failures. Acceptance: no non-error uses role="alert". |
| F-02 | TOG-6 secondary NRM-6 | Tognazzini Norman | Save Veteran populated re-save | Saving overwrites an existing Veteran's tags and notes saved not null Update Veteran SaveVeteran.vue:412 with no confirmation and no reversal path from this screen; the write posts and redirects SaveVeteranController.php:147-154. TOG-6 when undo is absent the confirmation the absence forces must be present and named; NRM-6 evaluation gulf what will be replaced is not stated. | OBSERVED_SOURCE | resources/js/pages/Career/SaveVeteran.vue:412; app/Http/Controllers/Career/SaveVeteranController.php:147-154 | 1 | Medium | Add a pre-save notice naming that re-saving replaces tags/notes, or a confirmation before overwrite; record the undo absence as the systemic gap H3/TOG-6 rather than a per-screen fix. Acceptance: overwriting is named before commit or a confirmation gates it. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every panel | No browser; intended ratios in app.css only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast borders focus rings risk edge meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; viewport never rendered | Rendered reflow pass at 320 px populated both themes |
| Target sizes of the 44 px house rule hold on every control min-h-11 buttons chips | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize plus text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every control | No keyboard run executed | Keyboard pass over the page |
| 50 ms click acknowledgement on tag chips and Save | No timing instrumentation | Rendered or tooling latency pass |

## 7. Conflict and gap log not findings

Settlements applied rather than filed as defects:

- 44 px versus 2.5.8. Target-size observations are filed under the house rule UX-1/TOG-5 and explicitly not as conformance, per wcag-2.2-accessibility-2026-10-21.md.
- Density versus minimalism. The screen is deliberately dense review plus tags plus note plus absences; H8 is signal against noise and every element carries a fact. No finding.
- Closure versus honesty. Absent values carry a word and a title N/A with a reason never a dash; GT-2/UI-4 hold. No finding.
- Feedback timing. 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; acknowledgement surfaces are inline hints whose latency is unmeasured section 6.
- Consistency versus exceptions. No exceptions on this screen. No finding.
- Title-only disclosure systemic. Several N/A absence reasons are carried only in title attributes; recorded as a gap rather than per-screen findings.
- Systemic gaps confirmed in the register rather than filed per screen: H3/TOG-6 no undo on a committed write F-02 names it here but the fix is product-wide; H7 no accelerators; H10 no help surface; WCAG 1.4.3/1.4.11 etc all UNMEASURED.

Conflicts: none found this pass; no code-versus-rule disagreement required settlement.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. F-01 S: replace role="alert" on the blocked block with a non-alert role. Acceptance: no non-error uses role="alert".
2. F-02 S: name the overwrite before commit or add a confirmation. Acceptance: re-saving a Veteran states it replaces tags/notes, or a confirmation gates the write.

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
- Recommendations ordered by severity then confidence then effort 2 items.
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed sections 1-10 in order; title line exact.
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
