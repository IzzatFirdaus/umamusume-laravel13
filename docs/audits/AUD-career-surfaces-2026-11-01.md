# Audit report — M4 product audit: career (training-run) surfaces reachable from the Cockpit

## 1. Identity

- Audit id: AUD-20261101-002
- Date: 2026-11-01
- Mode: **M4 product audit** (A11y overlay applied as a separate track, Phase 5)
- Scope: the 13 career surfaces named in the task — `Cockpit` (SCR-CAR-011), `TrainingDetail` (SCR-CAR-012), `RaceDecision` (SCR-CAR-013), `EventDecision` (SCR-CAR-014), `InheritanceEvent` (SCR-CAR-015), `SkillsPlanner` (SCR-CAR-016), `Deck`/Support Builder (SCR-CAR-007, `Support/Builder.vue`), `Timeline` (SCR-CAR-017), `Result` (SCR-CAR-018), `RunSettings` (`runs.settings`, unregistered), `TeamEdit` (`runs.team`, unregistered), `ScenarioDetail` (`runs.scenario-detail`, unregistered), `ConfirmDelete` (`runs.confirm-delete`, unregistered). Per screen: page + controller read, SCREEN_SPEC §SCR-CAR-xxx where registered, browser spec where it exists.
- Out of scope: backend behaviour beyond each controller's own queries, the wizard (SCR-CAR-002..010), scenario panels beyond the shell, performance, content strategy, AAA criteria, the `eva01` third theme, any fix.
- Frameworks applied: `design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Gerhardt-Powals, IA, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **F-01 — RunSettings has no error surface.** Four forms (status, scenario, period, identity) post to `runs.update`; refused writes land in `form.errors` that the page never renders, and there is no `role="alert"` for a failed visit. A refused identity edit (e.g. a ceiling above the scenario's) reads as "nothing happened". Severity 2.
- **F-09 — five doors mislabel the page in the breadcrumb.** `RunSettings`, `TeamEdit`, `ScenarioDetail`, `ConfirmDelete` and `Result` pass `current="cockpit"` (or unset it), so `CareerLayout`'s crumb prints "Cockpit" on a page that is not the Cockpit. Severity 2.
- **F-04 — SkillsPlanner is reachable by URL only.** No 2.0 surface links to `runs.skills.planner`; the Cockpit's action grid does not grow an entry for it (SCREEN_SPEC §SCR-CAR-016, R2-07). Severity 2.
- **F-06 — the type scale is not a scale on the decision screens.** `TrainingDetail`, `EventDecision`, `SkillsPlanner` and `Timeline` carry 6–14 distinct arbitrary sizes (`text-[10px]` … `text-[15px]`) against `UI-1`'s three. Severity 2.
- Largest unmeasured risk: reflow and target size on the four new doors and on `ConfirmDelete`, which have no rendered pass at all; their browser specs (where they exist) were not executed in this sandbox.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Cockpit (011) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-cockpit.spec.ts |
| TrainingDetail (012) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-training-detail.spec.ts |
| RaceDecision (013) | yes (4) | yes | yes | yes | src | src | named | named | named | named | src | career-race-decision.spec.ts |
| EventDecision (014) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-event-decision.spec.ts |
| InheritanceEvent (015) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-inheritance-event.spec.ts |
| SkillsPlanner (016) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-skills-planner.spec.ts |
| Deck / Support Builder (007) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-deck-select.spec.ts |
| Timeline (017) | yes | yes | yes | yes | src | src | named | named | named | named | src | career-timeline.spec.ts |
| Result (018) | yes (2) | yes | yes | yes | src | src | named | named | named | named | src | career-result.spec.ts |
| RunSettings (unreg.) | n/a (run required) | yes | none | yes | src | src | named | named | named | named | src | run-settings.spec.ts |
| TeamEdit (unreg.) | n/a | yes | none | none | src | src | named | named | named | named | src | team-edit.spec.ts |
| ScenarioDetail (unreg.) | n/a | yes | none | none | src | src | named | named | named | named | src | none |
| ConfirmDelete (unreg.) | n/a | yes | yes (refused) | yes | src | src | named | named | named | named | src | run-delete.spec.ts |

Cockpit was previously audited as AUD-20261101-001; this pass carries no Cockpit-specific finding beyond the cross-screen items below. Framework coverage (applied and produced findings): `UI-1`, `H1`, `H4`/`SHR-1`, `IA-1`, `WCAG 3.3.1`/`3.3.3`. Applied, measured clean from source (no finding): `H5`/`NRM-3`/`SHR-5` (constraint vs validation, Form Requests in place — confirmed for every write except none outstanding), `SHR-6`/`TOG-6`/`H3` (reversibility — deferred; see gap register), `SHR-7`/`SHR-8` (value on the decision screen — present on TrainingDetail, RaceDecision, SkillsPlanner), `GP-1`/`GP-3` (grouped and labelled sums), `GT-2`/`UI-4` (absent values carry a word and a title), `IA-2` (banded nav via CareerSectionNav on every career screen), `IA-5` (empty states show an example or a path out on most screens), `WCAG 2.1.1`/`2.4.7`/`4.1.2`/`4.1.3` (links, buttons, labels, status regions present), `WCAG 1.4.1` (stale state carries word + border), `WCAG 3.1.2` (Japanese names are not printed on these screens; `lang="ja"` is set where they are, e.g. Dashboard/Veterans). `N/A` by the framework's own list: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing), `SHR-4` (no multi-step flow closure on these screens), `IA-3`/`IA-6` (structure between screens out of scope), AAA criteria. Not reached: `IA-4` (density exemption named — Database only), `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 3.2.6`/`3.3.7` (not exercised).

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller source read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Provenance caveats carried (audit prompt §3.1): Gerhardt-Powals primary text paywalled, items 5/6 an unconfirmed split (nothing here rests on that boundary); IA prose unread, only names/summaries are the article's own (IA-1 used only as its derived sentence); Norman's book not fetched, quotes from the encyclopedia entry; Tognazzini sixteen are the original edition, 2014 count unverified; Laws of UX holds 29 (Zeigarnik from its own page); Laws of UI holds 13 and publishes Closure and Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.
- SCREEN_SPEC drift noted: `SCR-CAR-015`'s "Validation & Error Handling" subsection claims the Inheritance Event validates inline with `request()->validate()`, but the controller injects `StoreInheritanceEventRequest` (a Form Request at `app/Http/Requests/StoreInheritanceEventRequest.php`). The doc is stale; the code follows the Floor. No finding filed (the code is correct).

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `H9` (secondary: `WCAG 3.3.1`, `3.3.3`) | Heuristics / WCAG | RunSettings, all states, all widths | Four forms (status, scenario, period, identity) post to `runs.update`; refused writes land in `form.errors` that the template never renders, and there is no `role="alert"` for a failed visit. A refused identity edit (e.g. a ceiling above the scenario's) reads as "nothing happened". | OBSERVED_SOURCE | `resources/js/pages/Career/RunSettings.vue:175-315` (no error block for any form) | 2 | High | Render `form.errors` beside each form (or a page-level `role="alert"`), matching EventDecision/RaceDecision's idiom. Acceptance: a refused write prints its reason on the same screen. |
| F-02 | `H1` (secondary: `TOG-1`) | Heuristics / Tognazzini | TeamEdit, all states, all widths | The page declares no loading or error state of its own (`role="status"`/`role="alert"` absent), and the Unity Cup write is delegated to `UnityCupEditor`, which has no error display and no processing announcement beyond a button label. | OBSERVED_SOURCE | `resources/js/pages/Career/TeamEdit.vue` (no `role="status"`/`role="alert"`); `resources/js/components/career/UnityCupEditor.vue:213-215` (only `form.processing` on the button) | 1 | Medium | Add page-level `role="status"`/`role="alert"` to TeamEdit, or move feedback into UnityCupEditor. Acceptance: a refused team write prints a reason and focus moves to it. |
| F-03 | `H1` (secondary: `TOG-1`) | Heuristics / Tognazzini | ScenarioDetail, all states, all widths | No page-level loading or error state (`role="status"`/`role="alert"` absent), unlike every peer career screen; a failed Inertia visit on this page has no announced surface. | OBSERVED_SOURCE | `resources/js/pages/Career/ScenarioDetail.vue` (no `role="status"`/`role="alert"`) | 1 | Medium | Add page-level `role="status"` "Loading…" and `role="alert"` for a failed visit, matching the peer screens. |
| F-04 | `IA-1` (secondary: `IA-2`) | Information architecture | SkillsPlanner, all states, all widths | Reachable by URL only: no 2.0 surface (Cockpit action grid, career bar, Dashboard) links to `runs.skills.planner`. SCREEN_SPEC §SCR-CAR-016 records it as R2-07. | OBSERVED_SOURCE | grep: only `SkillsPlanner.vue` self-references `runs.skills`; `Cockpit.vue` has no skills link | 2 | High | Add a link from the Cockpit's "Where to go" (or the career bar) to `runs.skills.planner`. Acceptance: a Trainer can reach the planner without typing a URL. |
| F-05 | `H4` (secondary: `SHR-1`) | Heuristics / Shneiderman | ConfirmDelete, all states, all widths | The confirmation list names turns and races plus "build target, skill plan, inheritance selection", but omits turn events, skills, deck slots and the linked Veteran that the Cockpit's delete door names as cascading children. | OBSERVED_SOURCE | `resources/js/pages/Career/ConfirmDelete.vue:80-84` vs Cockpit delete door copy | 2 | Medium | Align the two lists so the confirmation states exactly what `runs.destroy` removes. Acceptance: both surfaces name the same set. |
| F-06 | `UI-1` (secondary: `UI-5`) | Laws of UI | TrainingDetail, EventDecision, SkillsPlanner, Timeline; populated, all widths | 6–14 distinct arbitrary type sizes per screen (`text-[10px]`, `text-[10.5px]`, `text-[11.5px]`, `text-[12.5px]`, `text-[13px]`, `text-[13.5px]`, `text-[15px]` …) against `UI-1`'s three (page, section, body). Same shape as the Dashboard audit's F-01. | OBSERVED_SOURCE | `resources/js/pages/Career/TrainingDetail.vue`, `EventDecision.vue`, `SkillsPlanner.vue`, `Timeline.vue` (arbitrary `text-[…]` utilities) | 2 | Medium | Fold the intermediate steps into the token layer's three steps, then re-measure. Acceptance: ≤3 distinct computed sizes under `main` on each screen. |
| F-07 | `SHR-1` (secondary: `H4`) | Shneiderman | Deck / Support Builder, all states, all widths | The only career surface mounted under `AppLayout` rather than `CareerLayout`: its breadcrumb, who-pill and width container are hand-rolled, so one screen carries a second shell. | OBSERVED_SOURCE | `resources/js/pages/Support/Builder.vue:280` (`<AppLayout>`); peers use `CareerLayout` | 1 | Medium | Either move the deck builder onto `CareerLayout` or document the exception as a named one. Acceptance: one shell owns the career breadcrumb. |
| F-08 | `H1` (secondary: `TOG-1`) | Heuristics / Tognazzini | Deck / Support Builder, populated, all widths | `Save deck` gives no success confirmation beyond the button reverting from "Saving…"; there is no "Saved." line or flash, so a Trainer is not told the deck was stored. | OBSERVED_SOURCE | `resources/js/pages/Support/Builder.vue:274-276,336-346` (no success line) | 1 | Medium | Add a `role="status"` "Saved." line (or rely on a flash) after `form.post` resolves. Acceptance: a successful save names the fact. |
| F-09 | `H1` (secondary: `IA-1`) | Heuristics / Information architecture | RunSettings, TeamEdit, ScenarioDetail, ConfirmDelete, Result; all states, all widths | Five doors report "Cockpit" as the screen in `CareerLayout`'s breadcrumb because they pass `current="cockpit"` (or unset it): RunSettings, TeamEdit, ScenarioDetail, ConfirmDelete set `current="cockpit"`; Result sets none, which defaults to "Cockpit". | OBSERVED_SOURCE | `RunSettings.vue:151`, `TeamEdit.vue:64`, `ScenarioDetail.vue:50`, `ConfirmDelete.vue:58`, `Result.vue:192` (no `current`) | 2 | High | Pass the matching `current` value (or add the missing keys to `CareerSectionNav`'s step list) so the crumb names the page. Acceptance: the breadcrumb's third segment equals the page's `h1`. |
| F-10 | `WCAG 1.4.13` (secondary: `H9`) | WCAG 2.2 | InheritanceEvent, populated, all widths | Milestone-timeline glyph meanings and the star-roll odds column headers are carried only in `title` attributes, which are hover-only and unreachable on touch. | OBSERVED_SOURCE | `resources/js/pages/Career/InheritanceEvent.vue` (milestone timeline; `title`-only legend) | 2 | Medium | Render the glyph legend as visible text and put the odds headers in the table header row. Acceptance: no meaning lives only in a `title`. |
| F-11 | `H1` (secondary: `TOG-3`) | Heuristics / Tognazzini | RunSettings, populated, all widths | The page's `lede` claims "Everything here writes through ${run.run_url}" (the run base URL), while the back link and every form actually post to `run.update_url` (`runs.update`). A minor wording/destination mismatch on a write screen. | OBSERVED_SOURCE | `resources/js/pages/Career/RunSettings.vue:54` | 1 | High | Reword the lede to name `runs.update` (or drop the URL). Acceptance: the lede names the route the forms post to. |

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Dark- and light-theme body text meets 4.5:1 on every surface | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, risk edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling on the four new doors and on ConfirmDelete | No browser; their specs (where they exist) were not executed | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on every control | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and the 1.4.12 text-spacing override lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Screen-reader announcement of loading/error status and the milestone timeline | No AT attached | NVDA/VoiceOver pass over populated and empty states |
| Whether `title`-only disclosures are reachable at all on touch (F-10) | No touch device tested | Touch-only pass over InheritanceEvent |
| Whether the type-scale fix (F-06) leaves the badge/label sizes readable | No rendered measurement | Computed-style size comparison before/after |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Every target-size observation above is filed under the house rule (`UX-1`/`TOG-5`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The career surfaces are deliberately dense (HFW clone); `H8` is about signal against noise, and every element carries a fact. No finding.
- **Closure vs honesty.** Empty/absent tiles look like closed shapes but carry the word; `GT-2`/`UI-4` hold. No finding.
- **Feedback timing.** 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; the only acknowledgement surface on most of these screens is the `role="status"` line, whose latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on these screens. No finding.
- **Title-only disclosure (systemic).** Absence reasons, milestone glyphs and several `N/A` reasons are carried only in `title` attributes across most of the career set (and the wider app). Filed as a gap rather than 11 per-screen findings: there is no second disclosure channel (tooltip component, inline expansion) in the system. F-10 names the most affected screen. A component-level fix would close it everywhere.
- **No undo / no accelerators / no help surface (systemic).** Registered gaps `H3`, `H7`, `H10`: none of these 13 screens offers undo, accelerators or a help surface. None becomes a per-screen finding here because the absence is product-wide, not screen-specific (per audit prompt §6.3).
- **No separate corrections log (Timeline).** `CareerTimeline` infers a correction from `updated_at > created_at`; the model has no edit log or prior-values column. Recorded as the gap it is; upgrade path named in SCREEN_SPEC §SCR-CAR-017.
- **Scenario Race Planner / Race Decision readiness held.** `readiness` is `N/A` on `ADR-0016`; the owner owes a ruling on a descriptive (non-numeric) band. Recorded, not filed as a defect.
- **SCREEN_SPEC drift (Inheritance Event).** §SCR-CAR-015's "Validation & Error Handling" subsection claims inline `request()->validate()`; the controller uses `StoreInheritanceEventRequest`. The code follows the Floor; the doc is stale. Recorded here.
- **Unregistered screens.** `RunSettings`, `TeamEdit`, `ScenarioDetail` and `ConfirmDelete` have no `SCR-CAR-xxx` entry; their contract lives in route comments and page header comments. Coverage, not a finding.
- **ScenarioDetail has no dedicated browser spec.** Its panels are covered only by the cockpit's scenario-panel spec. Coverage, not a finding.

Gaps confirmed in the register rather than filed per screen: `H3` (no undo after a committed turn), `H7` (no accelerators), `H10` (no help surface), title-only disclosure channel, no separate corrections log, no undo of a deleted run (ConfirmDelete is irreversible by design — the checkbox is the `TOG-6` confirmation, which is present and named).

## 8. Recommendations

1. **F-01** (S): render `form.errors` (or a page-level `role="alert"`) on RunSettings. Check: a refused write prints its reason on the same screen.
2. **F-09** (S): pass the matching `current` value on the five mislabelled doors. Check: breadcrumb's third segment equals the page's `h1`.
3. **F-04** (S): add a link to `runs.skills.planner` from the Cockpit. Check: reachable without typing a URL.
4. **F-06** (M): take the type scale to the token layer on the four decision screens. Check: ≤3 distinct computed sizes under `main`.
5. **F-05** (S): align the ConfirmDelete cascade list with the Cockpit's. Check: both surfaces name the same set.
6. **F-10** (M): move milestone-glyph meaning out of `title`. Check: no meaning lives only in a `title`.
7. **F-02/F-03** (S): add page-level loading/error states to TeamEdit and ScenarioDetail. Check: `role="status"`/`role="alert"` present.
8. **F-07** (M): settle the deck builder's shell (move onto `CareerLayout` or record the exception). Check: one shell owns the career breadcrumb.
9. **F-08** (S): add a "Saved." confirmation to the deck builder. Check: a successful save names the fact.
10. **F-11** (S): reword the RunSettings lede. Check: the lede names the route the forms post to.
11. Register the four unregistered screens in SCREEN_SPEC (docs task, not a code fix).

## 9. Change-audit re-check

Not owed (mode is M4). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surfaces, states, viewports and themes stated.
- Framework routing recorded, narrowest rule per question, secondaries listed.
- §3.1 provenance caveats carried.
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status.
- No severity 3/4 finding rests on inference (highest severity here is 2).
- Severity and confidence are separate axes.
- No defect filed twice.
- Known conflicts logged as settlements.
- Gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed.
- No conformance claimed beyond criteria tested; no rendered observation claimed.
- Report does not modify app code.
