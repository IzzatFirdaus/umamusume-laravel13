# Audit report — M2 screen audit: Umamusume catalog detail

## 1. Identity

- Audit id: AUD-20261102-011
- Date: 2026-11-02
- Mode: **M2 screen audit** (single surface, open-to-close walkthrough plus rule sweep)
- Scope: `GET /umamusume/{slug}` (`catalog.show`), controller `App\Http\Controllers\CatalogController::show`, page `resources/js/pages/Catalog/Show.vue`, `SCREEN_SPEC.md` §SCR-CAT-002. States: populated (default form + costume forms), `?show_unconfirmed=1`, Japan-only notice, no profile fetched, no aptitudes, no skills, no runs; both themes; widths 320/390/768/1280px; keyboard-only.
- Out of scope: the list screen (separate audit, AUD-20261102-010), writes beyond the idempotent "carry" draft POST, AAA, any code fix, content strategy beyond the reference cross-check.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **F-01 — The `? Unique` pill's derivation is carried only in a `title` attribute.** The Unique pill prints `? Unique` (Show.vue:256-260) with a `title="Marked from the source's skill class code, not from a card's own skill list."` that explains whose reading the mark is. That distinction — source-derived class code versus a card's own skill list — is available to sighted hover users only; keyboard and screen-reader users get the pill's visible name without the derivation, so the meaning of the mark is not fully exposed. Severity 1, Medium. (Same screen-specific instance of the title-only disclosure channel as AUD-20261102-012 F-01 and AUD-20261102-013 F-01.)
- Strengths: base stats and growth state "Not recorded" with an explanatory note rather than a fabricated `+0%` (Show.vue:218-222, 225-233); the costume strip is omitted for single-form trainees (no affordance to nowhere, Show.vue:347-347 comment); two absences named apart on the forms table (no Global card vs hidden as unconfirmed, with a "Show unconfirmed forms" link, Show.vue:369-376, 427-433); unique skills with no client link print the name with a note ("A name with no link is a skill the Global client has not received", Show.vue:458-466); provenance prints both the trainee's own fetch history and each card's `source_url`, never one in place of the other (controller:279-282); the "Start career" button posts the idempotent carry route (a `<button>`, not a link, Show.vue:579-585); the trainee/Japanese-name dedupe rule prints the pair only when they differ (Show.vue:187-190); the JP-only notice names the source as JP-sourced (Show.vue:199-201).
- Largest unmeasured risk: contrast, the 44px house-rule target size, reflow at 320px, focus visible/obscured — none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| catalog.show | n/a (404 on unknown slug; no empty trainee state) | yes (default form, costumes, skills, runs, goals, provenance) | yes (404 via `abort_if`) | n/a (no async load; cached reads only on the list) | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.4.2, 2.4.6, 2.5.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED; 2.5.8 house rule only; 2.5.7 N/A (no drag); 2.1.1/2.4.7 UNMEASURED (no keyboard pass); AAA not reached |
| Heuristics | H1, H2, H4, H6, H8 | H3 N/A (no destructive write); H5 N/A (no error-prone action); H9 N/A; H10 N/A |
| Shneiderman | SHR-1, SHR-7 | SHR-2 N/A; SHR-4/5/6 N/A (read-only + idempotent draft write) |
| Tognazzini | TOG-3 | TOG-1 UNMEASURED; TOG-2/4/5/6/7/8 N/A |
| Information architecture | IA-1, IA-4 | IA-2/IA-3 structure between screens (the index is the other axis); IA-5 N/A (no empty trainee state); IA-6/8 N/A |
| Gestalt | GT-1, GT-4 | GT-2/3/5/6 not reached |
| Norman | NRM-1, NRM-2, NRM-4, NRM-6 (both gulfs) | NRM-3, NRM-5 not reached |
| Laws of UX | UX-1 (44 px house rule), UX-4, UX-5, UX-9 | UX-2/3/6/7/8/10 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5/6/7 not reached |
| Gerhardt-Powals | GP-1, GP-3 | GP-2/4/5 not reached |

Framework coverage (applied and produced findings): none at the normative level produced a finding beyond F-01. Applied, measured clean from source: `H1` (provenance states last read + source key); `H2` (release status word, JP-only notice); `H4`/`SHR-1` (one `.pill`/`.btn` vocabulary, doors via `route()`); `NRM-6` (execution: link/open/button present; evaluation: provenance + absence state directly perceived); `IA-1` (crumb + "Back to catalog" door); `GT-2`/`UI-4` (absent values carry a word + title, never a dash). `N/A`: `2.5.7`, `2.1.4`, `3.3.8`, `1.2`/`1.4.2`/`2.3`. Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12`, `WCAG 2.4.11`.

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` — 105 Global trainees vs 268 JP; the show page respects `?show_unconfirmed` on both list and detail (`cardScope`, controller:793-804), and hides JP-only forms exactly as the list does. Content track clean; no content discrepancy filed.
- Provenance caveats carried (§3.1 of the audit prompt): Gerhardt-Powals paywalled; IA prose unread; Norman book not fetched; Tognazzini sixteen are the original edition; Laws of UX 29; Laws of UI 13 publishes Closure/Continuity; no Law of Experience (Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity ? `uilaws-2026-10-21.md`; Common Fate ? `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob ? `laws-of-ux-2026-10-21.md`; 44 px house rule exceeding 2.5.8, never conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | (title-only disclosure channel; no single SC) | WCAG 1.3.1 / 4.1.2 adjacency | /umamusume/{slug}, populated, Unique skill pill | The `? Unique` pill's derivation — "Marked from the source's skill class code, not from a card's own skill list." — is carried only in a `title` attribute (Show.vue:257-260). The visible name `? Unique` is exposed, but the distinction that matters (source-derived class code vs a card's own skill list) is available to sighted hover users only; keyboard and screen-reader users cannot reach it (`title` appears on hover, is not focusable, and is not reliably announced). | OBSERVED_SOURCE | `resources/js/pages/Catalog/Show.vue:256-260` | 1 | Medium | Render the derivation as visible text beside the pill, or pair the pill with an `aria-describedby`/sr-only twin stating the derivation (mirror the `N/A` + `sr-only` twin pattern used at `SupportCards/Index.vue:336`). Acceptance: the class-code-versus-card-list distinction is available without hovering. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Body text meets 4.5:1 in both themes on every card and table cell | No browser | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, pills, focus ring) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the two-column layout | No browser | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on the Start-career button, form links, Open buttons and costume strip | Boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize + text-spacing pass |
| Focus visible and focus-not-obscured on every control, including the sticky carry button | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule and explicitly not as conformance.
- **Density vs minimalism.** The detail is deliberately dense (profile, aptitudes, costumes, skills, goals, runs, provenance); every element carries a fact. `H8` holds. No finding.
- **Closure vs honesty.** The base-stats/growth table states absence ("Not recorded") and never implies a stored figure; the costume strip is honest about a single form. `GT-2`/`UI-4` hold. No finding.
- **Title-only disclosure channel.** F-01 names the one affected spot on this screen; several other `N/A`/absence reasons elsewhere in the app ride `title` only and are logged as a systemic gap rather than re-filed per screen.
- **Consistency vs exceptions.** No exception on this screen needs naming (the stat table's "Not recorded" is the rule, not an exception).
- **Feedback timing.** The carry POST is idempotent (rewrites the same two draft keys) and needs no pending guard, per the component comment (Show.vue:576-578); its acknowledgement latency is unmeasured (§6).

Conflicts: none found this pass.

Gaps confirmed in the register rather than filed per screen: title-only disclosure channel (systemic; F-01 is the screen-specific instance), WCAG 1.4.3/1.4.10/1.4.11 and all target-size/resize/focus claims UNMEASURED.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. **F-01** (M): expose the `? Unique` derivation without relying on `title` (visible text or an `aria-describedby`/sr-only twin). Acceptance: the class-code-versus-card-list distinction is available without hovering. Effort: S.

## 9. Change-audit re-check

Not owed (M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (contrast, target size, reflow, resize, keyboard).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- Provenance caveats carried.
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- `UMAMUSUME_REFERENCE.md` content cross-check recorded (105 Global vs 268 JP trainees; `show_unconfirmed` honoured on both list and detail; content track clean).
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run.
- No severity 3/4 finding rests on inference (highest severity here is 1).
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (1 item).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
