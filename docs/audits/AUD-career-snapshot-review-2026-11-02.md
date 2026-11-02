# Audit report — M3 flow audit with M2 per-screen sweep: Career Snapshot Review

## 1. Identity

- Audit id: AUD-20261102-009
- Date: 2026-11-02
- Mode: M3 flow audit (multi-screen task open to close) with M2 per-screen sweep
- Scope: SCR-CAR-027 Career Snapshot review — route GET /career/snapshot/review (career.snapshot.review), controller App\Http\Controllers\Career\SnapshotController review method, page resources/js/pages/Career/Snapshot/Review.vue, the commit targets POST /career/snapshot (career.snapshot.store) via StoreSnapshotRequest, SCREEN_SPEC.md section SCR-CAR-027, tests/Feature/SnapshotReviewTest.php, tests/browser/career-snapshot.spec.ts.
- Out of scope: the Entry and Setup snapshot screens (separate audits this pass), CreateSnapshotRun domain action, performance, AAA criteria, any fix.
- Frameworks applied: docs/framework-ui-ux/design-framework-index-2026-10-21.md first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per section 4 routing. Read from the files this run, not from memory.
- Rendered evidence: FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are OBSERVED_SOURCE or UNMEASURED.

## 2. Executive summary

- 1 finding, severity 1 minor, noted as the systemic undo gap. None severity 3 or 4.
- F-01 — Record snapshot commits with no reversal path and no post-commit confirmation beyond the redirect flash Review.vue:144-151; SnapshotController::store commits directly app/Http/Controllers/Career/SnapshotController.php:166-173. TOG-6 when undo is absent the confirmation the absence forces must be present and named; NRM-6 evaluation gulf. The read-back before commit is good design no redundant entry WCAG 3.3.7, but once confirmed the write is final. This is the product-wide no-undo gap, not a Review-specific defect.
- The screen is otherwise clean: three-way state Known/Unknown/Not provided with distinct words and distinct treatments border colour text Review.vue:65-75; has_draft false shows an honest empty state Review.vue:107-110 and disables confirm; the refusal alert is focused via alertRef plus watch and nextTick Review.vue:53-59; confirm re-posts the payload it was handed so what was read back is what is committed Review.vue:61-63; position_label derived from the shared owner so read-back and cockpit cannot spell it two ways SnapshotController.php:155-164; Back to edit the snapshot with draft re-hydration.
- Cross-screen note: stepper markup differs across Entry Setup Review logged in section 7 not re-filed per screen.
- Largest unmeasured risk: contrast, target size under the 44 px house rule, reflow at 320 px, focus visible or obscured none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Snapshot Review career.snapshot.review | yes no draft | yes fields | yes refusal | yes recording | src | src | named | named | named | named | src | career-snapshot.spec.ts |

Framework coverage matrix:

| Framework | Rules applied | N/A or not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 3.3.1, 3.3.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only never conformance; 2.5.7 N/A no drag; 3.3.7 satisfied read back not re-ask; AAA not reached |
| Heuristics | H1, H2, H4, H5, H6, H8, H9 | H3 exit Back present, H7 accelerators, H10 help surface |
| Shneiderman | SHR-4, SHR-5 | SHR-6 systemic undo gap F-01 named here product-wide |
| Tognazzini | TOG-6 | TOG-1 50 ms UNMEASURED; TOG-2, TOG-3, TOG-4, TOG-5, TOG-7, TOG-8 |
| Information architecture | IA-1 | IA-2, IA-3, IA-4, IA-5, IA-6 |
| Gestalt | GT-2 | GT-3, GT-4, GT-5, GT-6 |
| Norman | NRM-3, NRM-5, NRM-6 | NRM-1, NRM-2, NRM-4 |
| Laws of UX | UX-3, UX-9 | UX-1, UX-2, UX-4, UX-5, UX-6, UX-7, UX-8, UX-10 |
| Laws of UI | UI-2, UI-3 | UI-1, UI-4, UI-5, UI-6, UI-7 |
| Gerhardt-Powals | GP-2, GP-4 | GP-1, GP-3, GP-5 |

Framework coverage applied and produced findings: TOG-6/NRM-6 (F-01). Applied measured clean from source no finding: H1 Recording… status during processing; H2 plain client vocabulary; H4 one token set min-h-11; H5 confirm disabled without draft constraint; H6 position_label and field displays; H8 every element carries a fact; H9 refusal plain names cause; SHR-4 flow closes on stated outcome flash; NRM-3 confirm disabled without draft; NRM-5 continuous recording status; UI-3 state not colour alone words plus treatment; GP-2 state clear; GP-4 state has word plus colour plus border treatment. N/A: 2.5.7 no drag, 2.1.4 no accelerators, 3.3.8 no auth, 1.2/1.4.2/2.3 no media. Not reached: WCAG resize/reflow passes, WCAG 3.2.6/3.3.7.

## 4. Evidence status and limits

- RENDERED_EVIDENCE = FALSE. Chromium cannot launch in this sandbox (spawn ENOENT); no rendered frame was obtained.
- Structural and copy claims are OBSERVED_SOURCE against the page, controller and Form Request source read this run; visual, contrast, target-size and reflow claims are UNMEASURED section 6.
- Provenance caveats carried audit protocol section 3.1: Gerhardt-Powals primary text paywalled; IA prose unread names summaries only; Norman's book not fetched quotes from encyclopedia entry; Tognazzini sixteen are the original edition; Laws of UX holds 29; Laws of UI holds 13 and publishes Closure/Continuity; no Law of Experience it is Mental Model UX-17.
- Attribution rule applied: Closure/Continuity cited to uilaws-2026-10-21.md; Common Fate cited to gestalt-principles-2026-10-21.md; Fitts/Hick/Jakob cited to laws-of-ux-2026-10-21.md; 44 px described as a house rule exceeding 2.5.8 never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface or state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | TOG-6 secondary NRM-6 | Tognazzini Norman | Snapshot Review commit | Record snapshot commits with no reversal path and no post-commit confirmation beyond the redirect flash Review.vue:144-151; SnapshotController::store commits directly app/Http/Controllers/Career/SnapshotController.php:166-173. TOG-6 when undo is absent the confirmation the absence forces must be present and named; NRM-6 evaluation gulf. The read-back before commit is good, but once confirmed the write is final. This is the product-wide no-undo gap named here rather than a Review-specific defect. | OBSERVED_SOURCE | resources/js/pages/Career/Snapshot/Review.vue:144-151; app/Http/Controllers/Career/SnapshotController.php:166-173 | 1 | Medium | Record the no-undo gap product-wide H3/TOG-6/SHR-6; if a per-screen fix is wanted, add a confirmation before commit or an undo after. Acceptance: the undo absence is tracked as a single product-wide gap, not duplicated per screen. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light/dark body text meets 4.5:1 on every panel | No browser; intended ratios in app.css only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast borders focus rings meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; viewport never rendered | Rendered reflow pass at 320 px populated both themes |
| Target sizes of the 44 px house rule hold on every control min-h-11 confirm button links | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize plus text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every control | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log not findings

Settlements applied rather than filed as defects:

- 44 px versus 2.5.8. Target-size observations are filed under the house rule UX-1/TOG-5 and explicitly not as conformance, per wcag-2.2-accessibility-2026-10-21.md.
- Density versus minimalism. The field read-back list is dense but every element carries a fact; H8 holds. No finding.
- Closure versus honesty. Not provided is honest never guesses; GT-2 holds. No finding.
- Feedback timing. Recording… status during processing; latency unmeasured section 6.
- Consistency versus exceptions. Cross-screen stepper markup differs across Entry Setup Review logged here not re-filed per screen. No finding.
- Title-only disclosure systemic. N/A no title attributes of note here.
- Systemic gaps confirmed in the register rather than filed per screen: H3/TOG-6 SHR-6 no undo on a committed write F-01 names it here but the fix is product-wide; H7 no accelerators; H10 no help surface; WCAG 1.4.3/1.4.11 etc all UNMEASURED.

Conflicts: none found this pass; no code-versus-rule disagreement required settlement.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. F-01 M: track the no-undo gap product-wide rather than duplicating it per screen. Acceptance: H3/TOG-6/SHR-6 recorded as one systemic gap with an owner decision.

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
