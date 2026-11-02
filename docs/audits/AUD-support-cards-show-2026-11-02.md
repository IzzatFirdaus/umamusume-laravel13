# Audit report — M2 screen audit: Support-card detail

## 1. Identity

- Audit id: AUD-20261102-015
- Date: 2026-11-02
- Mode: **M2 screen audit** (single surface, open-to-close walkthrough plus rule sweep)
- Scope: `GET /support-cards/{card}` (`support-cards.show`, implicit binding), controller `App\Http\Controllers\SupportCardController::show`, page `resources/js/pages/SupportCards/Show.vue`, `SCREEN_SPEC.md` §SCR-SUP-002. States: populated (effects, stages, skills, used-by, provenance), no effect anchors, no skills stored, card not used in any run, no trainee resolves, no artwork; both themes; widths 320/390/768/1280px; keyboard-only.
- Out of scope: `/support-cards` index (separate audit, AUD-20261102-014), writes (read-only screen), AAA, any code fix, content strategy beyond the reference cross-check.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **No source-grounded findings were filed on this screen.** The surface is internally consistent: every effect row states the levels the source gives a value for and the last is the cap, never an interpolation (Show.vue:211-217); `statedAt()` prepends "1: Not recorded" when the source states nothing at level 1 (Show.vue:145-151); the limit-break diamonds render empty with a "breaks not recorded" readout rather than a fabricated progress (Show.vue:323-329); a card used by no run gets a labelled row carrying the neutral "Not used" pill rather than a bare sentence (Show.vue:343-351); provenance is a row plus a disclosure (no file path or snapshot location reaches the screen, Show.vue:407-417); the "Add to a deck" door opens `/career/setup/deck`, which renders its six empty slots when no draft exists rather than redirecting (Show.vue:163-178 comment).
- Largest unmeasured risk: contrast, the 44px house-rule target size, reflow at 320px, 200% resize, focus visible/obscured — none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| support-cards.show | n/a (404 on unknown id) | yes (effects, stages, hinted/event skills, used-by, provenance) | yes (404 via implicit binding) | n/a (no async load) | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.4.2, 2.4.6, 2.5.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only; 2.5.7 N/A (no drag); 2.1.1/2.4.7 UNMEASURED (no keyboard pass); AAA not reached |
| Heuristics | H1, H2, H4, H6, H8 | H3 N/A (no write); H5 N/A; H9 N/A; H10 N/A |
| Shneiderman | SHR-1, SHR-7 | SHR-2 N/A; SHR-4/5/6 N/A (read-only) |
| Tognazzini | TOG-3 | TOG-1 UNMEASURED; TOG-2/4/5/6/7/8 N/A |
| Information architecture | IA-1, IA-4 | IA-2/IA-3 structure between screens; IA-5 N/A (no empty-card state — 404 instead); IA-6/8 N/A |
| Gestalt | GT-1, GT-4 | GT-2/3/5/6 not reached |
| Norman | NRM-1, NRM-2, NRM-4, NRM-6 (both gulfs) | NRM-3, NRM-5 not reached |
| Laws of UX | UX-1 (44 px house rule), UX-4, UX-5, UX-9 | UX-2/3/6/7/8/10 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5/6/7 not reached |
| Gerhardt-Powals | GP-1, GP-3 | GP-2/4/5 not reached |

Framework coverage (applied and produced findings): none. Applied, measured clean from source: `H1` (provenance states last read + source + manual flag); `H2` (Global client vocabulary; the type word is the client's); `H4`/`SHR-1` (one `.pill`/`.btn`/`LinkButton` vocabulary, doors via `route()`); `NRM-6` (execution: link/deck-door present; evaluation: stage table + absence state directly perceived); `IA-1` (crumb + "Back to cards" door); `GT-2`/`UI-4` (absent anchors state "Not recorded" / "breaks not recorded", never a dash). `N/A`: `2.5.7`, `2.1.4`, `3.3.8`, `1.2`/`1.4.2`/`2.3`. Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12`, `WCAG 2.4.11`.

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` — 251 support cards carry a Global release date against 559 on JP; support effects ids 1 (Friendship Bonus), 14 (Initial Friendship Gauge), 27 (Failure Protection), 31 (Wit Friendship Recovery) match the deciding-effects vocabulary; the stage table prints stated anchors only (the `1: Not recorded` pattern is the normal path — 2,756 of 5,114 effect rows have no level-1 anchor, per `SCREEN_SPEC.md` §4207). Content track clean; no content discrepancy filed.
- Provenance caveats carried (§3.1): Gerhardt-Powals paywalled; IA prose unread; Norman book not fetched; Tognazzini sixteen are the original edition; Laws of UX 29; Laws of UI 13 publishes Closure/Continuity; no Law of Experience (Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity ? `uilaws-2026-10-21.md`; Common Fate ? `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob ? `laws-of-ux-2026-10-21.md`; 44 px house rule exceeding 2.5.8, never conformance.

## 5. Findings

No source-grounded findings were filed on this screen; every applicable rule was measured clean from source or is registered as unmeasured (§6). The IA-5 exemplar gap and title-only disclosure channel are systemic gaps logged in §7 rather than filed per screen.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Body text meets 4.5:1 in both themes on every card and table cell | No browser | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, pills, chips, focus ring) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the two-column layout | No browser | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on the deck door, holder links, run links, artwork frame | Boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on every control | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule and explicitly not as conformance.
- **Density vs minimalism.** The detail is dense but every element carries a fact (stage anchors, cap, held-by, where-used). `H8` holds. No finding.
- **Closure vs honesty.** The effects table lists stated anchors only and the last is the cap; nothing interpolates between anchors. `GT-2`/`UI-4` hold. No finding.
- **IA-5 exemplar gap.** The "Where this card is used" empty state is a labelled row carrying the neutral "Not used" pill (matching the wireframe's named-row empty state) rather than an example of a populated row. Pre-registered exemplar gap in the IA framework's gap register; logged here rather than re-filed per screen.
- **Title-only disclosure channel.** Not present on this screen — its absences are inline words (`breaks not recorded`, "level not recorded for this run").
- **Consistency vs exceptions.** The artwork frame stays in its own row under the sub-nav rather than inside the head row (an HFW-OVERRIDE documented at Show.vue:18-22) because `PageHeader` exposes one slot (`actions`) and no title-leading slot. The absence behaviour holds (a file the mirror lacks renders nothing). Named exception, not drift. No finding.

Conflicts: none found this pass.

Gaps confirmed in the register rather than filed per screen: `IA-5` exemplar gap (systemic), WCAG 1.4.3/1.4.10/1.4.11 and all target-size/resize/focus claims UNMEASURED.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. (No screen-specific recommendations.) Close the unmeasured register (§6) with a rendered pass in both themes at all four widths when Chromium is available.

## 9. Change-audit re-check

Not owed (M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (contrast, target size, reflow, resize, keyboard).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- Provenance caveats carried.
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- `UMAMUSUME_REFERENCE.md` content cross-check recorded (251 Global vs 559 JP support cards; support-effect ids match the vocabulary; stated-anchor pattern matches SCREEN_SPEC §4207; content track clean).
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run — zero findings filed; no severity 3/4 finding rests on inference.
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (1 item).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
