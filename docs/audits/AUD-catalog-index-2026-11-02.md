# Audit report — M2 screen audit: Umamusume catalog index

## 1. Identity

- Audit id: AUD-20261102-010
- Date: 2026-11-02
- Mode: **M2 screen audit** (single surface, open-to-close walkthrough plus rule sweep)
- Scope: `GET /umamusume` (`catalog.index`), controller `App\Http\Controllers\CatalogController`, page `resources/js/pages/Catalog/Index.vue`, mounted body `resources/js/components/catalog/TraineeCatalog.vue` (shared with `/database/trainees`, SCREEN-023), `SCREEN_SPEC.md` §SCR-CAT-001. States: empty catalogue, empty result (no match), loading, populated; both themes; widths 320/390/768/1280px; keyboard-only.
- Out of scope: the write path (none — read-only screen), `/umamusume/{slug}` detail (separate audit, AUD-20261102-011), the Database hub rendering (`/database/trainees`, same body, separate surface), AAA criteria, any code fix, content strategy beyond the reference cross-check.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **F-01 — Search cannot match diacritic names or full-width katakana width variants.** `normalizedColumn()` folds spaces, hyphens and middle dots only (CatalogController:252-261). SQLite `lower()` is ASCII-only, so diacritics survive and `[Nuit Étoilée de Scarlet]` will not answer `nuit etoilee`; NFKD rewrites full-width katakana on the term side while SQL leaves the stored column untouched, so a katakana alias or title folds on one side and not the other and cannot match either. Documented in-source as a known ceiling with an upgrade path (store normalized keys beside `title`/`alias`). Findability gap on the primary browse surface. Severity 2, High.
- Strengths: two absences named apart (empty catalogue vs no match, TraineeCatalog:293-309); release status printed as a word, never a machine token, and Japan-only is muted text rather than a badge ("not on Global is an absence, not a status this tool awards", TraineeCatalog:334-342); the growth row states its absence and prints no fabricated `+0%` (TraineeCatalog:373-375); the pager keeps the query string via `withQueryString()` (CatalogController:148-149); the default status is Released (Global) only, matching the Global English tool (CatalogController:53-55, 120).
- Largest unmeasured risk: contrast, the 44px house-rule target size, reflow at 320px, 200% resize, focus visible/obscured — none measurable in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| catalog.index shell + TraineeCatalog body | yes (no rows / no match) | yes | n/a (read-only; a refused facet lands on the canonical URL with the field named) | yes | src | src | named | named | named | named | src | none |

Framework coverage matrix:

| Framework | Rules applied | N/A / not reached |
|---|---|---|
| WCAG 2.2 AA | 1.3.1, 1.4.1, 2.4.2, 2.4.6, 2.5.3, 4.1.2, 4.1.3 | 1.4.3/1.4.10/1.4.11 UNMEASURED (no browser); 2.5.8 cited only as the house rule's floor, never as conformance; 2.5.7 N/A (no drag); 2.1.1/2.4.7 UNMEASURED (no keyboard pass run); AAA not reached |
| Heuristics | H1, H2, H4, H5, H6 (F-01), H8 | H3 N/A (no write); H7 N/A; H9 N/A (no error surface); H10 N/A |
| Shneiderman | SHR-1, SHR-7 | SHR-2 N/A; SHR-4/5/6 N/A (read-only) |
| Tognazzini | TOG-3 | TOG-1 (50 ms acknowledgement) UNMEASURED; TOG-2/4/5/6/7/8 N/A on a read-only screen |
| Information architecture | IA-1, IA-2, IA-3, IA-4 | IA-5 exemplar gap (gap register, §7); IA-6/8 N/A |
| Gestalt | GT-1, GT-4 | GT-2, GT-3, GT-5, GT-6 not reached |
| Norman | NRM-1, NRM-2, NRM-4, NRM-6 | NRM-3, NRM-5 not reached |
| Laws of UX | UX-1 (44 px house rule), UX-4, UX-5, UX-9, UX-10 | UX-2, UX-3, UX-6, UX-7, UX-8 not reached |
| Laws of UI | UI-1, UI-2, UI-3, UI-4 | UI-5, UI-6, UI-7 not reached |
| Gerhardt-Powals | GP-1, GP-3 | GP-2, GP-4, GP-5 not reached |

Framework coverage (applied and produced findings): `H6` (F-01). Applied, measured clean from source (no finding): `H1`/`NRM-5` (loading `role="status"`); `H2` (Global client vocabulary, release status as a word); `H4`/`SHR-1` (one `.btn`/`.pill` vocabulary, doors via props); `H5`/`NRM-2` (refused facet names the field on the canonical URL); `IA-1` (crumb + way back on every load); `IA-4` (index previews name + three aptitude letters; detail holds the full set); `GT-2`/`UI-4` (absent values carry a word, never a dash); `WCAG 1.4.1` (state carries word + border/pill). `N/A`: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing). Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 2.4.11` (focus not obscured).

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no rendered frame was obtained.
- Structural and copy claims are `OBSERVED_SOURCE` against the page, controller and `TraineeCatalog.vue` read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Content cross-check: `docs/UMAMUSUME_REFERENCE.md` — 105 trainees carry a Global release date (latest 2026-09-24) against 268 on JP (reference §1.x); the index defaults to `release_status = GlobalReleased` (CatalogController:120) and refuses unknown status values, matching the Global English tool scoping. Content track clean; no content discrepancy filed.
- Provenance caveats carried (audit protocol §3.1): Gerhardt-Powals primary text paywalled (items 5/6 split unconfirmed — nothing here rests on it); IA prose unread, names/summaries only; Norman's book not fetched, quotes from the encyclopedia entry; Tognazzini sixteen are the original edition; Laws of UX holds 29 (Zeigarnik from its own page); Laws of UI holds 13 and publishes Closure/Continuity; there is no "Law of Experience" — it is Mental Model, UX-17.
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `H6` | Heuristics | /umamusume, populated, search | Search cannot match diacritic names or full-width katakana width variants. `normalizedColumn()` folds spaces, hyphens and middle dots only (CatalogController:252-261); SQLite `lower()` is ASCII-only so diacritics survive (`[Nuit Étoilée de Scarlet]` will not answer `nuit etoilee`), and NFKD rewrites full-width katakana on the term side while SQL leaves the stored column untouched, so a katakana alias or title folds on one side and not the other and cannot match either. Documented in-source as a known ceiling with an upgrade path (store normalized keys beside `title` and `alias`; that is a schema change, not this method's to make). | OBSERVED_SOURCE | `app/Http/Controllers/CatalogController.php:252-261`; the search clause at :125-141 | 2 | High | Store normalized companion keys for `title` and `alias` (a schema change requiring migration + ADR + digest), or document the searchable vocabulary on screen. Acceptance: a diacritic or width-variant query reaches its trainee. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Body text meets 4.5:1 in both themes on the card grid and filter row | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, pills, risk edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the card grid | No browser; viewport never rendered | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on the card tile, Filter/Clear buttons and facet selects | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and the 1.4.12 text-spacing override lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Focus visible and focus-not-obscured on Filter, Clear and each facet select | No keyboard run executed | Keyboard pass over the page |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Every target-size observation is filed under the house rule (`UX-1`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The index is a browse grid; every card carries a fact (name, three aptitude letters, form rarity, release status word). `H8` is about signal against noise and holds. No finding.
- **Closure vs honesty.** Absent values carry a word ("Aptitude not published for this trainee", "Growth not stored"); nothing claims completeness it does not print. `GT-2`/`UI-4` hold. No finding.
- **IA-5 exemplar gap.** Empty states name the absence and offer a remedy (Clear the filters / run the fetch) but show no example of what would fill them. This is the pre-registered exemplar gap in the IA framework's gap register (`information-architecture-principles-2026-10-21.md` §Gap register); it is logged here rather than re-filed per screen.
- **Title-only disclosure channel.** Not present on this screen; where `N/A` appears elsewhere it carries a visible word plus an `sr-only` twin (e.g. `SupportCards/Index.vue:336`), a pattern this screen does not need because its absences are inline words.
- **Code wins over stale rule.** No code-vs-rule disagreement required a ruling this pass.

Conflicts: none found this pass.

Gaps confirmed in the register rather than filed per screen: `IA-5` exemplar gap (systemic), `WCAG 1.4.3`/`1.4.10`/`1.4.11` and all target-size/resize/focus claims UNMEASURED.

## 8. Recommendations

Ordered by severity, then confidence, then effort:

1. **F-01** (S): store normalized companion keys for `title` and `alias` so diacritic and width-variant queries match, or document the searchable vocabulary on screen. Acceptance: `nuit etoilee` reaches `[Nuit Étoilée de Scarlet]`. Effort: a schema change (migration + ADR + digest line in `ARCHITECTURE-ESSENTIALS.md`).

## 9. Change-audit re-check

Not owed (M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surface, states, viewports and themes stated, including what was not covered (per-page contrast, target size, reflow, resize, keyboard, dialog focus).
- Framework routing recorded; narrowest rule per question; secondaries listed; all ten frameworks named in the coverage matrix.
- Provenance caveats carried (Gerhardt-Powals, IA prose, Norman, Tognazzini 2014 count, Laws of UX count, Laws of UI count, no Law of Experience).
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- `UMAMUSUME_REFERENCE.md` content cross-check recorded (105 Global trainees vs 268 JP; index defaults to Global Released; content track clean, no discrepancy filed).
- Every finding has a rule ID, named surface and state, specific observation, evidence status and `file:line` location verified this run.
- No severity 3/4 finding rests on inference (highest severity here is 2).
- Severity and confidence kept on separate axes.
- No defect filed twice; each row carries at most one primary rule.
- Recommendations ordered by severity then confidence then effort (1 item).
- Known conflicts logged as settlements; gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed (sections 1–10 in order; title line exact).
- No conformance claimed beyond criteria tested; no rendered observation claimed; 44 px never described as 2.5.8 conformance.
- Report does not modify app code.
