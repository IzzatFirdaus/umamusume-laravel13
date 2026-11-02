# Audit report — M2 screen audit: Support-card catalog index

## 1. Identity

- Audit id: AUD-20261102-014
- Date: 2026-11-02
- Mode: **M2 screen audit** (single surface, open-to-close walkthrough plus rule sweep)
- Scope: `GET /support-cards` (`support-cards.index`), controller `App\Http\Controllers\SupportCardController`, page `resources/js/pages/SupportCards/Index.vue`, `SCREEN_SPEC.md` §SCR-SUP-001. States: empty catalogue, empty result (no match), loading, populated; both themes; widths 320/390/768/1280px; keyboard-only.
- Out of scope: `/support-cards/{card}` detail (separate audit, AUD-20261102-015), the Database hub rendering (`/database/supports`, same body, separate surface), writes (read-only screen), AAA, any code fix, content strategy beyond the reference cross-check.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **No source-grounded findings were filed on this screen.** The surface is internally consistent: absences are named with reasons (never dashes), the empty state names both the empty catalogue and the no-match case with a remedy, the `N/A` friendship-bonus cell carries a visible word plus an `sr-only` twin (SupportCards/Index.vue:336), the Pal card's missing friendship bonus is the client word "None" with its reason (Index.vue:328-331), the scope line names one server ("251 cards on [Global]", Index.vue:356), and the "Effects that decide a deck slot" plus "uncovered effects" cards state facts no single row can (Index.vue:406-425).
- Notable transparency: cap-setting effects are documented as carried by zero cards ("Cap-setting effects exist in the vocabulary but are carried by zero of the {{ totalCount }} cards.", Index.vue:419-420), so a documented mechanism with zero coverage is named rather than silently omitted (IA-4 disclosure).
- Largest unmeasured risk: contrast, the 44px house-rule target size, reflow at 320px (the wide table sits in `overflow-x-auto` inside a labelled, focusable region, Index.vue:278), 200% resize, focus visible/obscured — none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| support-cards.index | yes (no rows / no match) | yes | n/a (read-only; a refused facet lands on the canonical URL with the field named) | yes | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.4.2, 2.4.6, 2.5.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only; 2.5.7 N/A (no drag); 2.1.1/2.4.7 UNMEASURED (no keyboard pass); AAA not reached |
| Heuristics | H1, H2, H4, H6, H8 | H3 N/A (no write); H5 N/A; H9 N/A; H10 N/A |
| Shneiderman | SHR-1, SHR-7 | SHR-2 N/A; SHR-4/5/6 N/A (read-only) |
| Tognazzini | TOG-3 | TOG-1 UNMEASURED; TOG-2/4/5/6/7/8 N/A |
| Information architecture | IA-1, IA-2, IA-3, IA-4 | IA-5 exemplar gap (gap register, §7); IA-6/8 N/A |
| Gestalt | GT-1, GT-4 | GT-2/3/5/6 not reached |
| Norman | NRM-1, NRM-2, NRM-4, NRM-6 | NRM-3, NRM-5 not reached |
| Laws of UX | UX-1 (44 px house rule), UX-4, UX-5, UX-9, UX-10 | UX-2/3/6/7/8 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5/6/7 not reached |
| Gerhardt-Powals | GP-1, GP-3 | GP-2/4/5 not reached |

Framework coverage (applied and produced findings): none. Applied, measured clean from source: `H1` (loading `role="status"`); `H2` (Global client vocabulary; the type word is the client's, the source's key for Wit is `intelligence` and for Pal is `friend`, Index.vue:430-433); `H4`/`SHR-1` (one `.btn`/`.pill`/`.tag` vocabulary, doors via `route()`); `IA-1` (crumb + way back); `IA-3` (browse axes: q, rarity, type, status, server, sort); `IA-4` (index previews type/rarity/friendship/key effects; detail holds the stage table); `GT-2`/`UI-4` (absent friendship bonus carries "N/A" + `title` + `sr-only`, never a dash); `UX-10` (nothing fills an empty layout — the empty state is purposeful, Index.vue:256-272). `N/A`: `2.5.7`, `2.1.4`, `3.3.8`, `1.2`/`1.4.2`/`2.3`. Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12`, `WCAG 2.4.11`.

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` — 251 support cards carry a Global release date against 559 on JP (reference §1.x); the index does not pre-filter to Global (`release_status` is a stored generated column, availability is a facet, SupportCardController:28-30) and counts 251 Global SSR (`globalCount`, controller:167). Support effects ids 1 (Friendship Bonus), 14 (Initial Friendship Gauge), 27 (Failure Protection), 31 (Wit Friendship Recovery) match the deciding-effects vocabulary (controller:234-240). Content track clean; no content discrepancy filed.
- Provenance caveats carried (§3.1): Gerhardt-Powals paywalled; IA prose unread; Norman book not fetched; Tognazzini sixteen are the original edition; Laws of UX 29; Laws of UI 13 publishes Closure/Continuity; no Law of Experience (Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity ? `uilaws-2026-10-21.md`; Common Fate ? `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob ? `laws-of-ux-2026-10-21.md`; 44 px house rule exceeding 2.5.8, never conformance.

## 5. Findings

No source-grounded findings were filed on this screen; every applicable rule was measured clean from source or is registered as unmeasured (§6). The IA-5 exemplar gap and title-only disclosure channel are systemic gaps logged in §7 rather than filed per screen.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Body text meets 4.5:1 in both themes on the table and filter row | No browser | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, pills, chips, risk edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the wide table inside `overflow-x-auto` | No browser; the card carries `relative` to contain AbsenceValue's sr-only (Index.vue:278) | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on the card row link, Open buttons, facet selects and Filter/Clear | Boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on Filter/Clear and each facet select | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule and explicitly not as conformance.
- **Density vs minimalism.** The table is deliberately dense for comparison; every column carries a fact. `H8` holds. No finding.
- **Closure vs honesty.** Absent friendship bonus states "N/A" with its reason (word + `title` + `sr-only`); absent effects state "The source states no effect anchor for this card." `GT-2`/`UI-4` hold. No finding.
- **IA-5 exemplar gap.** Empty states name the absence and offer a remedy (Clear filters / widen a facet) but show no example of what would fill them. Pre-registered exemplar gap in the IA framework's gap register; logged here rather than re-filed per screen.
- **Title-only disclosure channel.** Not present on this screen — its `N/A` already carries a visible word plus an `sr-only` twin (Index.vue:336), the pattern other screens should mirror.
- **Consistency vs exceptions.** Type-before-Rarity filter order matches the wireframe (Index.vue:198-200 comment); the scope line names one server. Declared and consistent. No finding.

Conflicts: none found this pass.

Gaps confirmed in the register rather than filed per screen: `IA-5` exemplar gap (systemic), WCAG 1.4.3/1.4.10/1.4.11 and all target-size/resize/focus claims UNMEASURED.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. (No screen-specific recommendations.) The title-only disclosure channel is systemic and is tracked on the screens where it occurs (AUD-20261102-011 F-01, AUD-20261102-012 F-01, AUD-20261102-013 F-01). Close the unmeasured register (§6) with a rendered pass in both themes at all four widths when Chromium is available.

## 9. Change-audit re-check

Not owed (M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (contrast, target size, reflow, resize, keyboard).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- Provenance caveats carried.
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- `UMAMUSUME_REFERENCE.md` content cross-check recorded (251 Global vs 559 JP support cards; availability is a facet not a pre-filter; support-effect ids match the vocabulary; content track clean).
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run — zero findings filed; no severity 3/4 finding rests on inference.
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (1 item).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
