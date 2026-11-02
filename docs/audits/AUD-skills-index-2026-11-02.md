# Audit report — M2 screen audit: Skill search index

## 1. Identity

- Audit id: AUD-20261102-012
- Date: 2026-11-02
- Mode: **M2 screen audit** (single surface, open-to-close walkthrough plus rule sweep)
- Scope: `GET /skills` (`skills.index`), controller `App\Http\Controllers\SkillController`, page `resources/js/pages/Skills/Index.vue`, mounted body `resources/js/components/skills/SkillCatalog.vue` (shared with `/database/skills`, SCREEN-023), `SCREEN_SPEC.md` §SCR-SKL-001. States: empty catalogue, empty result (no match), loading, populated; both themes; widths 320/390/768/1280px; keyboard-only.
- Out of scope: `/skills/{skill}` detail (separate audit, AUD-20261102-013), the Database hub rendering (`/database/skills`, same body, separate surface), writes (read-only screen), AAA, any code fix, content strategy beyond the reference cross-check.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **F-01 — The `? Unique` pill's derivation is carried only in a `title` attribute.** The Unique pill prints `? Unique` (SkillCatalog.vue:264-268) with a `title="Marked from the source's skill class code, not from a card's own skill list."` that explains whose reading the mark is. That distinction is available to sighted hover users only; keyboard and screen-reader users get the pill's visible name without the derivation, so the meaning of the mark is not fully exposed. Severity 1, Medium. (Same screen-specific instance of the title-only disclosure channel as AUD-20261102-011 F-01 and AUD-20261102-013 F-01.)
- Strengths: two absences named apart (empty catalogue vs no match, SkillCatalog.vue:206-226); the no-match headline is `role="status"` (a live region, because it is the result of an action) while the empty catalogue is static and does not announce on load (SkillCatalog.vue:204-205 comment); the search placeholder says "Part of a skill name" and the footer states that no readable effect text is stored and distance cannot be filtered separately (SkillCatalog.vue:325-334); the type word is this tool's reading of effect codes and refuses rows it cannot classify, so some rows show no word and the Type filter's "Any"/unspecified choice finds them (SkillCatalog.vue:326-329); the scrollable table region is `role="region"` with `aria-label` and `tabindex="0"`, keeping horizontal scroll inside a labelled region at 320px (SkillCatalog.vue:230-235); the detail link carries the list's query and `from` so the way back resumes the search (SkillCatalog.vue:77-89).
- Largest unmeasured risk: contrast, the 44px house-rule target size, reflow at 320px, focus visible/obscured — none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| skills.index shell + SkillCatalog body | yes (no rows / no match) | yes | n/a (read-only; a refused facet lands on the canonical URL with the field named) | yes | src | src | named | named | named | named | src | none |

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
| Laws of UX | UX-1 (44 px house rule), UX-4, UX-5, UX-9 | UX-2/3/6/7/8/10 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5/6/7 not reached |
| Gerhardt-Powals | GP-1, GP-3 | GP-2/4/5 not reached |

Framework coverage (applied and produced findings): none beyond F-01. Applied, measured clean from source: `H1` (`role="status"` loading + no-match live region); `H2` (Global client vocabulary; "the condition column is the source's own engine data, not a client paraphrase", Skills/Index.vue:52); `H4`/`SHR-1` (one `.btn`/`.pill`/`.tag` vocabulary, doors via `route()`); `IA-1` (crumb + way back); `IA-3` (browse axes: search, type, unique); `IA-4` (index previews name/type/condition/base cost; detail holds the full condition table); `GT-2`/`UI-4` (absent values carry `N/A` + reason, never a dash). `N/A`: `2.5.7`, `2.1.4`, `3.3.8`, `1.2`/`1.4.2`/`2.3`. Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12`, `WCAG 2.4.11`.

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page, controller and `SkillCatalog.vue` read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` — 623 skills released on Global of 1,910 total (reference §1.x); the index scopes to `availableOnGlobal()` (SkillController:332) and matches on `match_key`, never a display string (controller:333-336). Content track clean; no content discrepancy filed.
- Provenance caveats carried (§3.1): Gerhardt-Powals paywalled; IA prose unread; Norman book not fetched; Tognazzini sixteen are the original edition; Laws of UX 29; Laws of UI 13 publishes Closure/Continuity; no Law of Experience (Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity ? `uilaws-2026-10-21.md`; Common Fate ? `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob ? `laws-of-ux-2026-10-21.md`; 44 px house rule exceeding 2.5.8, never conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | (title-only disclosure channel; no single SC) | WCAG 1.3.1 / 4.1.2 adjacency | /skills, populated, Unique skill pill | The `? Unique` pill's derivation — "Marked from the source's skill class code, not from a card's own skill list." — is carried only in a `title` attribute (SkillCatalog.vue:267). The visible name `? Unique` is exposed, but the distinction that matters (source-derived class code vs a card's own skill list) is available to sighted hover users only; keyboard and screen-reader users cannot reach it (`title` appears on hover, is not focusable, and is not reliably announced). | OBSERVED_SOURCE | `resources/js/components/skills/SkillCatalog.vue:264-268` | 1 | Medium | Render the derivation as visible text beside the pill, or pair the pill with an `aria-describedby`/sr-only twin stating the derivation (mirror the `N/A` + `sr-only` twin pattern used at `SupportCards/Index.vue:336`). Acceptance: the class-code-versus-card-list distinction is available without hovering. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Body text meets 4.5:1 in both themes on the table and filter row | No browser | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, pills, focus ring) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the labelled scrollable table region | No browser | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on the skill name links, Open buttons and facet selects | Boxes not measured | Rendered target-size sweep |
| 200% text resize and 1.4.12 text-spacing lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on Filter/Clear and each facet select | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations are filed under the house rule and explicitly not as conformance.
- **Density vs minimalism.** The table is the reference density for this surface; every column carries a fact (name, type, condition, base cost). `H8` holds. No finding.
- **Closure vs honesty.** Absent condition/type/cost carry `AbsenceValue` disclosures ("The source states no activation condition for this skill."); nothing claims a value it does not print. `GT-2`/`UI-4` hold. No finding.
- **IA-5 exemplar gap.** Empty states name the absence and offer a remedy (fetch command `uma:fetch gametora-skills`, or Clear) but show no example of what would fill them. Pre-registered exemplar gap in the IA framework's gap register; logged here rather than re-filed per screen.
- **Title-only disclosure channel.** F-01 names the affected spot on this screen; other `N/A`/absence reasons elsewhere in the app ride `title` only and are logged as a systemic gap.
- **Consistency vs exceptions.** The two HFW overrides (no Distance facet, name-only search) are named in the component header (SkillCatalog.vue:12-19) and in the footer, so they are declared exceptions, not drift. No finding.

Conflicts: none found this pass.

Gaps confirmed in the register rather than filed per screen: `IA-5` exemplar gap (systemic); title-only disclosure channel (systemic; F-01 is this screen's instance); WCAG 1.4.3/1.4.10/1.4.11 and all target-size/resize/focus claims UNMEASURED.

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
- `UMAMUSUME_REFERENCE.md` content cross-check recorded (623 Global skills of 1,910; index scopes to `availableOnGlobal()` and matches on `match_key`; content track clean).
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
