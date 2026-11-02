# Audit report — SCR-CAR-008, the career setup wizard's inheritance step

- Audit id: **AUD-20261030-002**
- `verified-against`: the tree at `8acd26c8` for the baseline sweep; the post-fix readings in §11 are `verified-against` the two commits this pass landed, named there.
- Companion: `scr-car-008-legacy-source-accuracy-2026-10-30.md` (the source-accuracy pass over the same screen), and `scr-car-009-deck-screen-audit-2026-10-30.md` (this report's skeleton, and the §7 settlements it cites rather than re-derives).

## 1. Identity

- Date: baseline measured 2026-10-30 (the run crossed midnight into 2026-10-31; the frames are named by the run, not the clock). Post-fix re-measurement 2026-10-31, in §11.
- Mode: **M2 screen audit**, with the A11y overlay of `_audit-prompt.md` §2 applied as a separate track.
- Scope: `GET /career/setup/legacy` (`resources/js/pages/Career/LegacySelect.vue`, `App\Http\Controllers\Career\LegacySelectController`, written by `career.legacy.store` through `StoreDraftLegacyRequest`, the subclass of `StoreLegacySelectionRequest`). Surfaces walked: the two parent cards and their pickers, the manual-name block, the per-parent Spark editors, the four grandparent editors, Projected start, Pair Affinity, the "What inheritance actually does" table, the "Parents must differ" section, the step bar and the head band's doors.
- States covered: **empty** (no Veteran rows, nothing chosen), **populated** (two parents typed by name, four Sparks per slot, one grandparent named), **error** (a write refused by the server), **loading** (the save in flight). All four at 1280, 768, 390 and 320 CSS px, in **light and dark**: 32 frame pairs, 64 measured frames, with a PNG per pair.
- Out of scope: the other five wizard steps and Preflight, the run-scoped Legacy Lab builder (`SCR-CAR-006`), the six-node graph (which this step no longer builds), performance, AAA criteria (2.4.13, 2.5.5, 1.4.6), axe (provided by `tests/browser/accessibility.spec.ts`), and the `eva01` theme.
- Frameworks applied: `design-framework-index-2026-10-21.md` first, then per its routing table — Heuristics, Shneiderman, Tognazzini, Gerhardt-Powals, IA, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA. Read from the files for this run, not from memory.
- Rendered evidence: **TRUE**.

## 2. Executive summary

- **A refused write reaches this screen and renders nowhere** (F-01). The save answers the mismatched Spark with a redirect carrying `legacies.0.sparks.0.target`; the measured frame after that write holds no `role="alert"`, no `.text-risk` element and no `*-error` node, while the typed name survives and the step stays put. The step binds three of the request's error keys and not this one.
- **The field that produced it reads as cleared while the form still holds the old value** (F-02). After a category flip the Target select computes `selectedIndex -1` and `value ""`, yet the posted body carries `"target": "Turf"` under `"category": "Stat"`. The editor's own `@change="spark.target = ''"` never runs: `BaseSelect` is a multi-root component that declares no `change` emit, so the listener is dropped. This is what makes F-01 reachable at all, and it is reported rather than fixed in this slice.
- **The projected-start table's second column is headed "Scenario cap" and prints 1200 for a draft with no scenario** (F-03). The number is the baseline cap, not a scenario's ceiling, and the step already holds `scenarioLabel` to tell the two apart.
- **Twenty-five dashes stand where the repo's rule says `N/A` with a disclosure channel** (F-04), and **neither table on the screen carries a `<caption>` or a `th[scope]`** (F-05) while the sibling deck step carries both.
- **The screen is clean where the deck step failed.** At 320 px the document reads `scrollWidth === 320`, `panX === 0` in every state and both themes; the Projected-start region scrolls inside itself (client 254, content 331) with no `contain-paint` and no help from a `<details>` ancestor. Contrast: zero failures against the floors across all 64 frames, 100 percent of the text elements checked. No target falls under 2.5.8's 24 px floor.
- **Largest unmeasured risk**: the keyboard path and the focus ring inside the two scroll regions. Neither was walked here; the deck audit measured that on its own screen and this one has the same region shape. §6 carries it.
- The repository's standing statement that Chromium cannot launch here (`spawn EPERM`) did **not** hold for these runs; see §4 and §7.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Two parent cards and pickers | yes | yes | yes | yes | yes | yes | yes | yes | yes | yes | not run — §6 |
| Manual-name block and rank fields | no rows to pick | yes | yes | yes | yes | yes | yes | yes | yes | yes | not run |
| Spark editors (Kind, Category, Target, Stars) | yes (collapsed) | yes (two rows per parent) | yes (refused pair) | yes | yes | yes | yes | yes | yes | yes | not run |
| Grandparent editors | yes | yes (one named) | yes | yes | yes | yes | yes | yes | yes | yes | not run |
| Projected start table and its region | yes | yes | yes | yes | yes | yes | yes | yes | yes | yes | region not tab-walked |
| Pair Affinity control | yes | yes | yes | yes | yes | yes | yes | yes | yes | yes | not run |
| "What inheritance actually does" | yes | yes | yes | yes | yes | yes | yes | yes | yes | yes | not run |
| "Parents must differ" | yes | yes | yes | yes | yes | yes | yes | yes | yes | yes | n/a (prose) |
| 200% text resize, text-spacing override, AT announcement | not run — §6 | | | | | | | | | | |

The seeded library was **empty** in every frame: `DatabaseSeeder` does not call the two run seeders, so `ListVeterans` returns nothing and the pick list shows the library disclosure. Populated state is reached through the "Not in my library - enter a name" sentinel, which is the rented-parent path the reference names (§1.5.4). A library with Veteran rows is therefore unmeasured — §6.

**Framework coverage.** Applied and produced findings: `H9` with `WCAG 3.3.1` (F-01), `H5` with `NRM-3`/`SHR-5` (F-02), `TOG-3` "Status may be stale or absent, but never wrong" (`tognazzini-first-principles-2026-10-21.md:253`) (F-03), `GT-2`/`UI-4` with the repo's own `AGENTS.md` §5 Copy rule (F-04), `WCAG 1.3.1` (F-05), `UX-1` house rule (F-06), `UI-1` (F-07).

Applied, measured clean, recorded as no finding: `WCAG 1.4.10` (reflow: `scrollWidth === 320` and `panX === 0` at 320 px in all four states and both themes; the two regions report `overflow-x: auto`, the Projected-start one scrolls at content 331 against client 254); `WCAG 1.4.3` and `1.4.6`-floor-check (zero sub-floor readings against 72 checked text elements per frame, both themes); `WCAG 2.5.8` (`under24` empty in all 64 frames); `H1`/`SHR-3`/`4.1.3` (one `role="status"` region, and the in-flight frame reads "Saving the Legacy…" while the button label reads "Saving…"); `2.4.7`-adjacent (one accent-hued control on the screen, the "Continue to deck" door, with the single `h1`); `3.3.2`/`4.1.2` (every one of the 38 measured inputs has a wired `label`/`for` pair, which `career-legacy-deck-steps.spec.ts:299` already pins, and the Stars fields carry `min=1 max=3`, matching the three-star ceiling at §1.5.3); `1.4.13`/`4.1.2` on `AbsenceValue` (the reason appearing twice in the frame's text is the component's documented "one statement in two media", `resources/js/components/AbsenceValue.vue:10-14`); `2.1.1` partial (the region carries `tabindex="0"`, measured as an attribute, not walked).

`N/A` by the framework's own list: `2.5.7` (no drag operation here), `3.3.8`/`2.1.4` (no authentication, no accelerators — `H7` stays the registered gap), `1.2`/`1.4.2`/`2.3` (no media, no audio, no flashing).

Not reached: M3/M4 phases (one screen), and `IA-2`/`IA-3`/`IA-6` (structure between screens is out of scope by the mode).

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = TRUE`. Chromium launched for five Playwright runs against a session-private server (`PLAYWRIGHT_PORT=8186`) and a session-private scratch database (`PLAYWRIGHT_SCRATCH_DB=.scratch-uma/legacy-step4/browser-8186.sqlite`), each behind `.scratch-uma/legacy-step4/playwright.lock`. Artifacts: `.scratch-uma/legacy-step4/audit/{empty,populated,error,loading}.json`, `error-visible.json`, `populated-inputs.json`, 32 PNGs, plus `probe-reachability.json` from the reachability instrument.
- Instrument: `audit-measure.spec.ts`'s `probe()` and `digest()` from the deck pass, reused verbatim, with four additions in this session's copy (`tests/browser/_tmp-legacy-measure.spec.ts`): `doc.panX`, each region's `contain`, the region list, and the three text blocks this screen's findings quote. Contrast is `getComputedStyle` colour against the first solid ancestor background, sRGB relative luminance, two decimals — a reading within about 0.05 of a floor is not filed. Theme is set by writing the `data-theme` attribute the stylesheet keys on, the same mechanism the server uses, not a visit to `/preferences`.
- Target sizes are `getBoundingClientRect` on the visible element.
- The one measurement in this report that the harness itself pollutes is the acknowledgement time: 72 ms wall clock from the click to the status text becoming visible, which includes Playwright's locator round trip. It is therefore reported as *not* a `TOG-1` verdict and lives in §6.
- The error frame's request/response evidence is `probe-reachability.json` (a PUT at 303 whose posted body is printed, followed by a non-Inertia GET) plus the Pest replay of that exact body (`tests/Feature/_tmpSparkTargetProbeTest.php`, since deleted): form-encoded, JSON, and JSON with `X-Inertia: true`, all three refused the pair and all three left `SetupDraft::legacySelection()` null. The refusal is real; only its rendering is missing.
- Provenance caveats carried, per `_audit-prompt.md` §3.1: Gerhardt-Powals' primary text is paywalled and items 5 and 6 are an unconfirmed split (`gerhardt-powals-principles-2026-10-21.md:19`); the IA file's prose is unread and only its names and derived lines are the article's own (`information-architecture-principles-2026-10-21.md:19`); Norman's book was not fetched (`norman-interaction-principles-2026-10-21.md:15`); Tognazzini's sixteen are the original edition's (`tognazzini-first-principles-2026-10-21.md:17`); Laws of UX holds 29 entries (`laws-of-ux-2026-10-21.md:14`) and Laws of UI 13 (`uilaws-2026-10-21.md:18`). Nothing in this report rests on the two uncertain splits.
- Attribution rule for this report: every finding is against the screen as shipped at `8acd26c8`. F-01 and F-03 are closed by this pass's own commits and §11 carries the post-fix readings; F-02, F-04, F-05, F-07 are reported and not fixed.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `H9` (secondary: `WCAG 3.3.1`, `SHR-5`) | Heuristics | Save, error state, all widths, both themes | The write is refused and the screen says nothing. After the refused click the measured frame holds `alerts: []`, `riskText: []`, `errorNodes: []`, still on `/career/setup/legacy`, with the typed name intact. The request emits `legacies.{i}.sparks.{j}.target` and the step binds `legacies.{i}.legacy_id`, `legacies.{i}.ancestors.{n}` and the whole-payload keys, but not the Spark fields. A Trainer is left with a Save that appears to do nothing | OBSERVED_RENDERED | `.scratch-uma/legacy-step4/audit/error-visible.json`; `probe-reachability.json` `exchanges`, `after`; `resources/js/pages/Career/LegacySelect.vue:290-291`, `:539-541`; `app/Http/Requests/StoreLegacySelectionRequest.php:213-217` | 2 | High | Bind the field's own message through the error prop `BaseSelect` already renders as `role="alert"`. Acceptance check: after the same attempt the sentence naming the category's legal values is on screen and the entered state survives. **Closed by C2** |
| F-02 | `H5` (secondary: `NRM-3`, `TOG-3`) | Heuristics | Spark editor Category/Target pair, error state, 1280 px | The Target control shows nothing chosen while the form still holds the previous category's value and posts it. Measured after flipping Aptitude to Stat: the select reports `selectedIndex: -1` and `value: ""`, and the PUT body carries `{"category":"Stat","target":"Turf"}`. The editor's `@change="spark.target = ''"` is the intended guard and never runs: `BaseSelect` has three root nodes and declares only `update:modelValue`, `blur` and `focus`, so a `change` listener is not inherited by any element | OBSERVED_RENDERED | `probe-reachability.json` `afterFlip`, `postedSparks`; `resources/js/components/Form/BaseSelect.vue:43-47`, `:86-143`; `resources/js/pages/Career/LegacySelect.vue:504` | 2 | High | Either make the reset real, or stop posting the transient category the way `Builder.vue` does (`category: inferCategory(spark.target)` at `resources/js/pages/Legacy/Builder.vue:336-339`, which derives it from the target at submit). Not this slice's edit: it changes what is stored. Owner's call, named here. |
| F-03 | `TOG-3` (secondary: `GT-2`, `H9`) | Tognazzini | Projected start table header, empty and populated, all widths | The column is headed "Scenario cap" and every row prints 1200 while the draft holds no scenario. 1200 is `config('scenarios.base_cap')`, the baseline the reference gives as "every stat before breakthroughs" (`UMAMUSUME_REFERENCE.md` §1.3.4); the scenario ceilings are URA 1400 across five, Unity Cup 1300/1300/1300/1300/1800, Trackblazer 1200/1900/1200/1200/1500 and Grand Concert 1600/1300/1300/1500/1300. The page can tell the two states apart already: `scenarioLabel` is a prop | OBSERVED_RENDERED | `populated.json` `projectedText` at `320-light`; `app/Http/Controllers/Career/LegacySelectController.php:146`, `:162`; `app/Services/ScenarioCaps.php:163-179`; `config/scenarios.php:112` | 2 | High | Print the header the numbers actually answer to, "Base cap" when no scenario is chosen. Acceptance check: an empty-draft frame reads "Base cap" and a scenario frame reads "Scenario cap". **Closed by C1** |
| F-04 | `AGENTS.md` §5 Copy (secondary: `GT-2`, `UI-4`) | House rule / Gestalt | Projected start table body, populated and empty, all widths | Unrecorded values render as dashes: the scenario-caps column falls back to `-`, both parent columns print `-` and the Projected column is a literal `-` in every row, so the populated frame shows 25 of them. `AGENTS.md` §5 is explicit ("A value a run has not recorded renders as `N/A` with a `title`, never a default and never a dash"), and the parent columns carry a `?` fallback for a missing star count (`★? Blue`), which is neither form. The screen already imports the component that states an absence with its reason | OBSERVED_RENDERED | `populated.json` `projectedText`; `resources/js/pages/Career/LegacySelect.vue:596`, `:600-602`, `:606-608`, `:610`; `AGENTS.md` §5 "Copy" | 2 | High | Route the unrecorded cells through `AbsenceValue`, which is what the deck step's `N/A` markers do, and name what the `?` is standing for. Not this slice's edit: it is a markup decision across four columns. |
| F-05 | `WCAG 1.3.1` (AA) | WCAG 2.2 | Both tables, every state, all widths | Two tables on the screen, zero `<caption>` elements and zero `th[scope]` attributes, in every state and at every width. The row header of the projected table is a plain `<td>`, so the association a Trainer needs to read "Wit, 1200, -, -, -" as a row of one stat is visual only. The sibling deck step measures six captions and 29 scoped column headers, so this is a gap against the house pattern as well as the criterion | OBSERVED_RENDERED | `populated.json` `semantics.tables`/`captions`/`thCol`/`thRow` at all eight frames; `resources/js/pages/Career/LegacySelect.vue:582-613`, `:718-758` | 2 | High | Give each table a caption naming what it prints and scope the header row. Acceptance check: the instrument reports `captions 2` and `thCol` above zero. |
| F-06 | `UX-1` house rule (explicitly **not** `WCAG 2.5.8`) | Laws of UX | The one compact `N/A` marker in the Enhanced-spark row, populated and empty, all widths | Measured 39.8×24 px: it meets 2.5.8's 24 px floor and the component documents why the dense-row form is not the house 44 px bar (`AbsenceValue.vue:15-19`), so **no conformance failure is claimed**. It is the only sub-44 target on the screen: `under44` holds exactly this one entry at all 64 frames and `under24` is empty everywhere | OBSERVED_RENDERED | `populated.json` `under44`, `under24` at all frames; `laws-of-ux-2026-10-21.md:164`; `wcag-2.2-accessibility-2026-10-21.md:107` | 1 | High | No action: the row that carries it is unsourced and is removed by C1, so the post-fix frame reads `under44` empty. The row stays in this table so the 44 px bar is not later mis-read as 2.5.8 |
| F-07 | `UI-1` (secondary: `SHR-1`) | Laws of UI | Whole screen, populated, all widths, both themes | Five distinct computed font sizes are on the screen — 28, 15, 14, 13 and 12 px — where the rule permits three (page, section, body). The 28 px step is the single `h1`, the 15 px steps the eight `h2` section headings at weight 800, and 13 px appears once. Milder than the deck step's nine, and the same token-scale question, not a page edit | OBSERVED_RENDERED | `populated.json` `typeSizes`, `typeScale` at `320-light` (`14/400` 42 elements, `12/500` 27, `14/800` 5); `uilaws-2026-10-21.md:94` | 1 | Medium | Take it to the token layer with the deck step's F-05, not here. Acceptance check: at most three distinct computed sizes under `main` |

No finding was filed twice for one defect, and each row carries at most one primary rule. The mechanism table's source errors are not repeated here: they belong to `scr-car-008-legacy-source-accuracy-2026-10-30.md`, and F-03 and F-06 are the two whose symptom a Trainer sees rather than a figure that is merely wrong.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| The keyboard path reaches both scroll regions and every Spark row, and a focused child's ring is not clipped | The instrument records `tabindex="0"` on each region but no tab walk was run here; the deck audit measured the same shape on its own screen and found the clip at 287.17 against 287 | A 320 px tab loop over the populated frame, measuring each focused child against its region's clip, exactly as `career-deck-summary.spec.ts` does for the deck step |
| A click is acknowledged inside `TOG-1`'s 50 ms | 72 ms was measured wall clock, and the harness's own locator round trip is inside that number, so it cannot answer a 50 ms bar | A `performance.now()` stamp taken inside the page at pointer-down and at the status node's first paint |
| A screen reader announces the refusal, the pending label and the recorded Sparks | No AT was attached; `role="status"`, `role="alert"` and the label wiring were read from the DOM only | An NVDA or VoiceOver pass over the populated and error frames |
| 200% text resize and the text-spacing override lose nothing | No resize pass was run | `WCAG 1.4.4`/`1.4.12` measurement at 320 px, populated, both themes |
| The populated-library picker (Veteran rows, costume labels, the 100-row page and `rosterTotal`) | `DatabaseSeeder` does not call the two run seeders, so the scratch database holds zero Veterans and the sentinel path was used instead | `TrackerVeteransSeeder` then `UraFinaleRunsSeeder` on the scratch database, then the same sweep with two library picks |
| A repeat deeper in the diagram zeroes Affinity, as §1.5.4 states | The step stores two Veterans of one trainee rather than policing it, and no measurement read the consequence | Would require the computation `ADR-0020` §3 forbids; the honest substitute is a stated absence, which is what C1's copy does |
| `eva01` behaviour | Out of scope by the mode | The same sweep in the third palette |
| Whether any element on the screen animates | The instrument this run did not emit the animation field the deck pass added | Re-run with `animated` restored to `probe()` |
| Whether F-05's missing `th[scope]` is what lets F-03's header drift | Both are on the same element; neither was tested against the other | A caption-plus-scope edit, then the same reflow and text readings |

## 7. Conflict and gap log (not findings)

Settlements already made on the sibling screen and **cited rather than re-derived** here: `scr-car-009-deck-screen-audit-2026-10-30.md:81-86` carries the 44 px rule as the house bar that exceeds WCAG 2.5.8 (`wcag-2.2-accessibility-2026-10-21.md:107`), the absence of `SHR-8`, `IA-7` and `IA-8` from their files (the Shneiderman table ends `SHR-7` at `:141`, the IA table `IA-6` at `:147`), the withdrawal of `WCAG 4.1.1`, `norman-interaction-principles-2026-10-21.md:59`'s "not offer the same card twice" as contradicted by shipped behaviour, and the typo at `wcag-2.2-accessibility-2026-10-21.md:165`. This report cites `SHR-7` and `IA-5` on those terms and files nothing under the withdrawn or non-existent IDs.

New to this screen:

- **The premise this pass set out to test was wrong, and the correction is the finding.** The plan read the editor's `@change="spark.target = ''"` as making the mismatched-Spark state unreachable, and a first instrument attempt timed out because a select cannot re-select an option it no longer offers. Measured against the posted body instead, the reset does not run at all, so the state is reachable and the refusal arrives unrouted (F-01, F-02). Recorded because an audit that had trusted the reading of the code would have filed the opposite conclusion.
- **A name in the accessibility tree that reads as duplicated text.** `mechanismText` shows the Enhanced row's `N/A` marker text twice; `AbsenceValue.vue:10-14` documents that as "one statement in two media", so it is the component working, and nothing is filed.
- **The five kind names against the four the wireframe draws.** The table gained a Scenario row this pass, so it now matches the five kinds the payload and the editor already offer (`AncestryGraph::SPARK_KIND_LABELS`, `LegacySelectionPayload::sparkKindLabels()`). The DOM-override comment above the table records the difference rather than hiding it, which is the settlement `DESIGN.md` asks for.
- **Density against minimalism.** Two side-by-side tables, four grandparent editors and two Spark lists per parent is the wizard's own disclosure shape, and every effect list sits behind a `<summary>`, so the preview rule holds and nothing is filed.
- **Honesty against closure.** The `Projected` column's dashes could read as "zero"; F-04 files them as a rule breach, not as a closure conflict, so this row records only that no claim of completeness is made in words.
- **The `spawn EPERM` caveat did not hold.** Chromium launched five times for this pass, and the three framework files carrying that line are stale, as the deck audit already recorded.

Gaps confirmed in the register rather than filed per screen: `H3` no undo after a committed turn (`usability-heuristics:231`), `H7` no accelerators (`:232`), `H10` no help surface (`:233`). On this screen none becomes a finding: nothing here commits a turn, the six editors need no accelerator, and the absence of help is the registered gap.

## 8. Recommendations

1. **F-01**, S. Bind the Spark-field refusals the request already emits. Satisfies `H9`, `WCAG 3.3.1`. Check: the refused pair prints its sentence and keeps the entered state. **Landed in C2.**
2. **F-03**, S. Print "Base cap" while the draft carries no scenario. Satisfies `TOG-3`. Check: the empty-draft frame's header text. **Landed in C1.**
3. **F-02**, S, and the owner decides which half. Make the category reset fire, or derive the posted category from the target the way `Builder.vue` already does. The second is one line in the transform and changes what is stored; the first needs `BaseSelect` to own a `change` emit, which every call site shares. Do neither inside a copy pass.
4. **F-05**, S. Caption both tables and scope their header rows. Satisfies `WCAG 1.3.1`. Check: `captions 2` and `thCol` above zero on this screen.
5. **F-04**, S. Route the unrecorded cells through `AbsenceValue` and say what `?` means. Satisfies `AGENTS.md` §5. Check: no `-` and no `?` remains in the projected table.
6. **F-07**, L, and not this screen's edit. Fold it in with the deck step's F-05 at the token layer.
7. **F-06** needs no change; the row it names is removed for being unsourced, and the entry exists to stop a later review mis-filing a dense `N/A` marker as a 2.5.8 failure.
8. The accuracy pass's own corrections ride in C1: the transposed Blue and Pink figures, the duplicated unique-skill row, the Green and White glosses, the added Scenario row, the removed Enhanced row, the two sentences that credited the picker with enforcement it does not perform, and `factor` to `Spark` in shipped copy.

## 9. Change-audit re-check

Not owed: the mode is M2, so `_audit-prompt.md` §11's ten questions are the M1 exit gate and are not scored here. The change this pass landed was nevertheless read against them while it was being built. C1 is copy: question 1 (a new control) and question 7 (a new boundary) do not apply, question 3 (a value rendered without its reason) applies to the corrected figures and is answered by the citation comments beside each, and question 5 (a claim the tool cannot support) is what the pass exists to clear, with the Enhanced row removed rather than hedged. C2 adds one error binding and no control: question 1 applies and the answer is a message inside the field's own `role="alert"`, wired by the `BaseSelect` error prop already in place. The two findings C1 and C2 close are F-01 and F-03; the other five stay open and named. The claim is a reading of one change, not a re-audit of a commit.

## 10. Exit checklist

Scope, mode, surfaces, states, viewports and themes stated; routing per question with the narrowest rule and the secondaries listed; §3.1 caveats carried; evidence capability recorded as TRUE and the stale `spawn EPERM` caveat named; every finding carries a rule ID, a named surface and state, a specific observation and an evidence status; nothing above severity 2 rests on inference (F-01, F-02 and F-03 are measurements, F-04 and F-05 are counts, F-06 and F-07 are measurements the report does not claim failures for); severity and confidence kept apart; no defect filed twice; the falsified premise recorded in §7 rather than quietly dropped; conflicts logged as settlements; gaps left in the register with what would settle each; the template followed; no conformance claimed beyond the criteria tested; and the one number the harness pollutes (the 72 ms acknowledgement) named instead of filed.

## 11. Post-fix readings, 2026-10-31

Dated readings are preserved, never rewritten, so §5's figures stand as the 2026-10-30 record of the
screen as shipped at `8acd26c8`. `verified-against`: `4401f4b6` (C1, the copy pass) and `063e3aad` (C2,
the refusal binding), landed on `7454f29e`. The same instrument re-ran all four states, four widths, two
themes (32 frame pairs, `.scratch-uma/legacy-step4/audit-post/`, `4 passed (44.2s)`) after one shared
`npm run build`, and `tests/browser/career-legacy-deck-steps.spec.ts` ran in the same session-private
harness: 15 passed, 1 failed, and the failure is the pre-existing step-5 case named in §7 of the C2
commit message, not a step-4 case.

| Measure | Baseline (2026-10-30) | Post-fix (2026-10-31) | Read |
| --- | --- | --- | --- |
| Document reflow at 320 px, all states, both themes | `scrollWidth 320`, `panX 0` | `scrollWidth 320`, `panX 0` | unchanged, still clean (`WCAG 1.4.10`) |
| `under44` targets | 1 in every frame (the Enhanced row's compact `N/A` disclosure, 39.8 × 24) | **0 in every frame** | **F-06 closed** by the row's removal |
| Focusable targets counted | 29 empty / 59 populated / 39 refused | 28 / 58 / 38 | one fewer in each state, and it is the same disclosure |
| `under24` (`WCAG 2.5.8`) | 0 | 0 | unchanged |
| Contrast failures | 0 in all 64 frames | 0 empty and populated, **1 in the refused frame at 320 dark** | see F-08 below |
| Projected-start region | client 254, content 331 | client 254, content 300 | the header text is shorter now; the region still scrolls inside itself |
| Projected-start header text | `StatScenario capFrom Parent AFrom Parent BProjectedSpeed1200…` | `StatBase capFrom Parent AFrom Parent BProjectedSpeed1200…` | **F-03 closed**: the column names the baseline while the draft holds no scenario |
| Refused-write frame | `alerts: []`, `riskText: []`, `errorNodes: []`, still on `/career/setup/legacy`, `nameValue: "Silence"` | `alerts` and `riskText` carry `A Stat Spark names one of Speed, Stamina, Power, Guts, Wit; [Turf] is not.`, `errorNodes` holds `legacy-spark-target-0-0-error`, same path, `nameValue: "Silence"` | **F-01 closed**. The message names the stale value, which is the only way a Trainer can see what F-02 hides |
| Click to status | 72 ms, "Saving the Legacy…" in `role="status"`, button "Saving…" | 83 ms, same two strings | both figures include Playwright's locator round trip, so neither answers `TOG-1`'s 50 ms; §6 stands |
| Computed type sizes | 28, 15, 14, 13, 12 | 28, 15, 14, 13, 12 | unchanged, **F-07 open** |
| `captions` / `thCol` | 0 / 0 | 0 / 0 | unchanged, **F-05 open** |

**F-08, new, from the post-fix frame.** `WCAG 1.4.3` (AA), severity 2, confidence High,
`OBSERVED_RENDERED`. Making the refusal visible made its contrast measurable: the message renders at
**4.15:1** in the dark theme (`rgb(255, 68, 0)` on `rgb(42, 42, 42)`) as 14 px weight 400 text, where the
floor is 4.5:1; the light theme passes. It is not this screen's style. The sentence comes from
`BaseSelect.vue:145` (`<p class="mt-1 text-sm text-risk" role="alert">`), so every server refusal that any
form prints through that component carries the same reading, and the reason it never appeared in a
baseline frame is that the baseline rendered no refusals here at all. The deck audit's F-02 measured the
same 4.15 on the same hue and fixed it by moving a non-danger sentence to ink; that remedy does not
apply here, because a refusal is the danger the hue is for. The fix belongs to `--color-risk` (or to the
error line's weight and size) at the token layer, and this pass leaves it there rather than restyling one
field. Acceptance check: every `text-risk` refusal on the step reads at or above 4.5 in both themes.

Findings this slice closes: **F-01** (C2), **F-03** and **F-06** (C1). Findings it leaves open and named:
**F-02** (the stale target, whose root cause is the dropped `change` listener on a shared component),
**F-04** (the dashes, now asserted at `career-legacy-deck-steps.spec.ts:302`), **F-05** (caption and scope),
**F-07** (the type scale) and **F-08**, which this pass's own fix uncovered.

