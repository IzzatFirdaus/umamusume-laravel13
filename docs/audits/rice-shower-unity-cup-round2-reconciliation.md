# Round 2 Reconciliation: Rice Shower / Unity Cup UX Walk — Second Pass

Reconciles `docs/audits/rice-shower-unity-cup-ux-walk-2.md` (the second browser walk) against
`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` (the remediation record) and the current
codebase at `aedfc30`.

## Method

Each observation from the second audit is classified as one of:

- **Previously fixed and confirmed** — the second pass verifies the earlier fix.
- **Previously fixed but inconsistent** — one surface still presents obsolete behavior or copy.
- **Regression or possible regression** — later behavior conflicts with earlier observations or current contracts.
- **New confirmed defect** — the second audit establishes a new failure or misleading result.
- **Known limitation** — behavior is intentional and consistent with existing product decisions.
- **Source conflict / needs investigation** — evidence does not yet support a definitive correction.
- **Deferred feature** — valuable functionality outside the current remediation slice.

## Reconciliation Table

| Finding ID | Evidence (Audit 2) | Prior Status | Current Status | Root Cause | Proposed Slice | Regression Test |
| --- | --- | --- | --- | --- | --- | --- |
| **R2-01: Existing-career position rendered as Turn 1** | Dashboard, Cockpit, Timeline all show Junior Year Early January Turn 1; source is Senior Early October, 5 turns left (§1.1 line 21) | Fixed (Phases 4–5, `0025f37`, `d0bd138`, `a31000c`, `b890cd5`, `fc66978`, `8c7628d`, `3b6f3cc`) | **Confirmed fixed for snapshot mode, but not for new careers** | The cockpit reads `careerPosition` when `hasImportedPosition()` is true (`CockpitController.php:277`). New careers still derive from turn count (`:263-270`). The second audit's run was created via the wizard (new career), so it correctly starts at Turn 1. The mismatch is that the *source* describes an existing career but the UI created a new one. | Clarify in docs: the walkthrough should use the snapshot import path, not the wizard. No code change needed. **Verdict, 2026-10-10:** held. A wizard-created run has logged nothing, so Turn 1 and the year grid's first slot are what the tool knows, `TrainingRun::hasImportedPosition()` (app/Models/TrainingRun.php:707) gates the imported reading, so a run that has logged nothing claims no calendar place rather than borrowing one; the Dashboard's own empty position is `N/A` under the title "not standing anywhere on the calendar yet", which `dashboard.spec.ts:136` pins. What the walk actually hit is the R2-04 door, landed in `65d7068`: the snapshot flow was unreachable from the state the Trainer was in. | `SnapshotCockpitTest` already covers imported position; verify new-career flow still starts at Turn 1. |
| **R2-02: Legacy selections disappear during wizard navigation** | Screen 5: ancestry entered, saved with no flash; Screen 7: Preflight reports ANCESTRY N/A | Fixed (Phase 6, `31c9f5d`, `5876f64`, `f39ff03`) | **Not a code defect — session configuration issue** | Feature tests prove the persistence logic works correctly (`CareerLegacyDeckStepsTest` passes, including new R2-02 regression test verifying flash message and Preflight readback). The audit observation is consistent with a session persistence failure in the browser environment, not a code bug. Likely causes: (a) `SESSION_DRIVER=database` in `.env` but no `sessions` table migrated, or (b) browser session cookies not persisted between requests, or (c) CSRF token mismatch preventing form submission. The controller's redirect-with-flash pattern is correct; if the session cannot store data, neither the flash nor the legacy payload survives. Correction 2026-10-10 (this session, measured on this tree): hypothesis (a) is disproved here. `Schema::hasTable('sessions')` answers yes with 3 rows on `database/database.sqlite`, and `.env:30` reads `SESSION_DRIVER=database`, so the sessions table is migrated on the database the walkthrough would have used. (b) and (c) were not tested and nothing reproducible was demonstrated, so the cause of the observed browser failure stays unidentified; the row stands as "the code path is not at fault", which is all the evidence supports. | No code change needed. Document troubleshooting: verify `php artisan migrate:status` includes sessions table when using database session driver, or switch to `SESSION_DRIVER=file` for local development. Correction 2026-10-10: that check is already answered on this tree (the table is there), so the clause stands only as guidance for a fresh worktree. Nothing to implement for this finding. | New regression test added: `CareerLegacyDeckStepsTest::'it shows a confirmation flash and persists legacy data through navigation to Preflight (R2-02)'` verifies flash message, session persistence, and Preflight readback in one request cycle. |
| **R2-03: Preflight permits contract with unresolved warnings** | Screen 7: Warnings list target and ancestry gaps, but Start Career remains enabled | Known limitation (resolution record does not address blocking semantics) | **Confirmed** | Preflight is read-only and delegates creation to the wizard's write path. The wizard allows partial saves by design (D4 fixed the target validation, but did not make warnings block creation). This is a product decision, not a defect. | Document as intentional: warnings inform but do not block. If owner wants blocking, that's a new feature request. | `CareerPreflightTest` verifies warning display; add case for creation with warnings. |
| **R2-04: Snapshot state conflated with subsequent turn state** | Screen 10: Turn form defaults to Turn 1; Screen 11: Cockpit shows Junior Year Early January after recording stats | Fixed (Phases 4–5, same as R2-01) | **Same as R2-01** | The wizard creates a new career, not a snapshot import. The turn form's default is correct for a new career. The snapshot import path (if it exists) would set the initial position differently. | Verify if snapshot import path exists and is reachable from the Dashboard. If not, this is a missing feature, not a bug. **Verdict, 2026-10-10, landed `65d7068`:** the path exists (`career.snapshot*`, `SnapshotController`, `Career/Snapshot/Entry|Setup|Review`) and the Dashboard linked it, but the sentence holding that link sat inside the `<section v-else>` empty state, so the door vanished the moment the tool held a career for the very Trainer the flow is for. The paragraph now renders outside both career branches with its words, link and destination untouched; `dashboard.spec.ts` asserts the door in each state and pins its href. No position value was invented, which is the R2-01 half of the same finding. | `SnapshotTrainingDecisionTest` covers snapshot-mode turn recording. |
| **R2-05: Scenario metadata has duplicate Ruleset rows** | Screen 2: Unity Cup card prints `Ruleset · N/A` in fact block and `Ruleset · Team` in description block | Not addressed in resolution record | **Fixed** in `9777531`, browser-verified; the residual (the Cockpit's own strip) landed in `c7a9c87`, and the four further surfaces R2-19 names are closed in `24f7e48` | Confirmed, and the cause is a label collision rather than a duplicated fact. The two rows print two different values from two different sources and neither is hardcoded: the grid row is the tool-wide ruleset *version*, `props.ruleset.value`, which `ScenarioSelectController::ruleset()` returns as null by ruling because no source defines a Global version (`design-2.0` §48); the block row is the scenario's own rule family, `card.metadata.ruleset` from `config/scenarios.php` (`'Team'` for Unity Cup alone, added by `1098546`). `config/scenarios.php:225` already says the distinction in its own words, "ruleset names the scenario's rule family, not a version", but `1098546` reused the grid's heading for the new row, so one card headed two different facts `Ruleset` and read as self-contradicting. | Rename the headings so each fact owns one: grid `Ruleset version`, block `Rule family`. Values, sources and the `N/A`-with-`title` rule are untouched, and no `Team` is typed into the template. Done in `resources/js/pages/Career/ScenarioSelect.vue`; `SCREEN_SPEC.md` SCR-CAR-002 updated for the two headings, the absent-field state row and the test list. | `ScenarioMetadataTest::'it prints each fact on a scenario card under one label, and never one label twice'` pins the card's full `<dt>` heading set read off the template (RED before the fix with `'Ruleset'` twice, GREEN after, and it cannot pass on a pattern that matched nothing). `tests/browser/career-scenario-select.spec.ts` adds a rendered case: Unity Cup shows `Ruleset version · N/A` and `Rule family · Team`, and no element anywhere is headed bare `Ruleset`. |
| **R2-06: Support-deck ownership copy contradicts persisted behavior** | Screen 6: Copy says flag has no persistence column; Screen 19: Flag survives reload | Fixed (D3, `fdebcc9`, added `deck_slots.ownership`) | **Fixed** in `dd179ba`, browser-verified | Confirmed, and wider than one screen. `fdebcc9` (2026-10-09) gave `deck_slots` its nullable `ownership` column and wired both writers, but the sentence written at D6-wizard survived in three shipped places: `Career/DeckSelect.vue` (step 5) and `Career/Preflight.vue` (step 6) each printed "The deck table has no column for it yet", and `components/support/SupportSlot.vue`'s own docblock still called the value client-side. Four comments in the write path said the same (`StoreDeckRequest` twice, `TrainingRunController::syncDeck()`, `SetupDraft::deck()`, `DeckSelectController`, `StoreDraftDeckRequest` twice). `Support/Builder.vue` had been corrected in the same pass as the migration, and that is why Screen 19 read true while Screen 6 read false. | Copy and comments only, no persistence change: each screen now states the flag's real route, draft then `PreflightController::store()` then the slot row, and each keeps the three-state distinction (`Owned`, `Rented`, unrecorded renders `N/A`). | Two things, because one was lying. `SupportDeckBuilderTest` gains (a) a sweep of the four shipped deck surfaces: the retired wording in any shape fails it, and (b) the write-and-read-back case for `runs.deck.sync` with a posted flag, which no test had run before, so the stale comments were never contradicted. `tests/browser/support-deck.spec.ts` asserted the run screen's **pre-D3** sentence and has been failing since `fdebcc9` (pre-existing, not caused here); it now asserts the sentence the screen prints. `career-legacy-deck-steps.spec.ts` likewise asserted "The deck table has no column for it". |
| **R2-07: Retired run-record instructions remain visible** | Screen 9: `/training-runs/9` redirects to Cockpit, but three screens still reference "run record screen" | Partially fixed (2A.5, `d251f7f`, nav-rail relabelled) | **Fixed** in `380350e` (+ `a1379bd`), browser-verified | Confirmed, and the audit's "three screens" was the visible tip. `F2` deleted `Runs/Show.vue` and made `GET /training-runs/{run}` a redirect (`TrainingRunController::redirect()`), and `d251f7f` relabelled the nav rail, but seventeen shipped sentences across nine sources still named the retired screen in three phrasings: "the run record screen" (Cockpit strip absences, Career Result skills/races/status/retired lines, Timeline empty state, Save Veteran blocked state), "the record screens" (Save Veteran's `best_use` title), and "the run screen" (Race planner × 4, Skills planner absence, `RaceFacts` skill-point fact, Training Detail's first-turn link, Event and Inheritance no-turn panels, RaceCard's skip note). Two links also addressed the doorway itself while labelled otherwise: the deck builder's career-bar `run_url` and the Veteran's `runUrl`. A grep for the literal phrase found 9 files; a grep for the *concept* ("run screen", "career record") found the rest, which is why the audit's own wording was not enough. | Retarget each instruction to the surface that owns the action today, verified per destination: the action grid (`Cockpit.vue:588`), Training for the turn fields (`TrainingDetail.vue:121` posts `sp`), the Race planner for the calendar (`SCR-CAR-023`), the Skills planner for skill states (`skills_sync_url` form), the Cockpit for status and scenario (`Cockpit.vue:354,376`) and for recorded outcomes (`Cockpit.vue:510`). Where no 2.0 owner exists, Rest and Recreation, the copy now says so instead of sending the Trainer to a closed door. No route, redirect or payload was touched. | `NoStaleRunRecordLinksTest` already existed at `d251f7f` with two browser-side cases; it was kept verbatim and gained the sweep its design could not make: comment-stripped matchers over `app`, `resources/js`, `resources/views` and `lang`, plus a `runs.show` whitelist narrowed to the doorway's own file with its two retained redirects counted (2). Red first listed nine files; green after. `career-race-strip.spec.ts` had been failing since `3cdaa2f` (2026-10-09, the pass that repointed the strip's links) because it still addressed `Go to the run record screen` while the component printed `Go to the Cockpit` (pre-existing, not caused here); both its cases now pass, and `VeteranLibraryScreenTest`'s `runUrl` pin moved to `runs.cockpit` with the link. `CareerRacePlannerTest` gained a positive case so the retarget cannot pass by losing its destination. |
| **R2-08: Target save confirmation is misleading** | Screen 4: Empty target saves with "Build target saved." but page says "No target is recorded yet" | Fixed (D4, `be02297`) | **Confirmed fixed, but UX ambiguity remains** | The validation now allows empty targets, and the success message is technically correct (the save succeeded). However, the distinction between "save succeeded" and "target configured" is subtle. | Consider changing the flash to "Target cleared." when all fields are empty, or adding a visual indicator of the empty state. | `CareerBuildTargetTest` covers empty save; add UX assertion if changed. |
| **R2-09: Stat denominators disagree across screens** | Screen 11: Cockpit shows `Speed 607 / N/A`; Screen 15: Event decision shows `Speed 607/1300` | Not addressed in resolution record | **New confirmed defect** | The Cockpit uses the build target as denominator (which is null if no target entered). The Event decision screen uses the scenario cap. These should be consistent or clearly labelled. | Inspect `EventDecisionController` and `CockpitController` stat rendering. Use shared helper for denominator logic. | Cross-screen test verifying same stat displays same denominator. |
| **R2-10: Skills planner reports meaningful result over empty set** | Screen 18: "skills still to learn cost 0 SP" with no skills marked | Not addressed in resolution record | **Fixed** in `d95da07`, browser-verified | The arithmetic was never the defect: `SkillSpendCoverage::sum([], 173)` returns `total_cost 0, remaining 173`, which is a correct sum over an empty set and the service still refuses a sum with any price missing. The controller then passed that zero through as a figure, so the branch that prints the numbers won and the branch that names the absence never ran: the page read "The skills still to learn cost 0 SP, and the run holds 173 SP, leaving 173" for a Trainer who had prioritized nothing. The screen's own Required group already distinguished "no build target" from "target names no priorities", so the coverage panel was the one place the two collapsed. | `coverage` gained a `state` key (`no_priorities` / `covered` / `unpriced` / `cost`) derived from the priority list rather than from the sum: nothing prioritized prints no figure at all; every priority learned prints the real zero with the reason beside it; a missing price still refuses; `warn` is only ever true in `cost`. `SkillSpendCoverage` is untouched, the priority schema and `runs.build-target.update` are untouched, and ADR-0001's removal of the hint ladder is untouched. | `CareerSkillsPlannerTest`: two new cases pin `no_priorities` (total null, remaining null, no text, absence names itself) apart from `covered` (total 0, remaining 173, reason states the learned outcome), so the states cannot pass by being conflated again; the suite 21 passed (306 assertions) and `SkillSpendCoverageTest` 5 passed (56) with the service's rules unchanged. `career-skills-planner.spec.ts` gained the rendered case (5 passed, 1.1m): the screen shows the absence and no `still to learn cost 0 SP` string is in the DOM. |
| **R2-11: Hint discounts discrepancy** | Screen 18: "Hint discounts are not modelled." vs first audit showing hint ladder | Resolution record states ADR-0001 removed hint ladder | **Known limitation (intentional)** | ADR-0001 withdrew the hint discount ladder from the UI. The second audit correctly reports its absence. The first audit's observation is outdated. | No change needed. Document that hint discounts are stored but not displayed per ADR-0001. | None needed. |
| **R2-12: Race planner overwhelms with empty details** | Screen 20: Large surface with repeated N/A fields for placeholder races | Not addressed in resolution record | **Fixed** in `473bb19`, browser-verified | Confirmed as presentation, and the underlying data gap is real and stays. Ten fact rows print per race; five of them refuse for every race in the tool (running style is the trainee's, reward / Skill Points / scenario reward have no column, the win figure is held on `ADR-0016`), and on the calendar's placeholder rows the remaining five refuse too: `Junior Make Debut` carries the tier word `Debut` and nothing else, `URA Finals Qualifier` and `Semifinal` likewise, while `Aichi Hai` resolves 2,000 m / Medium / Turf. So a debut card was ten labels and ten refusals, and the audit measured 17,409 characters of it. | `RaceFacts::describesRace()` owns the test (distance, band, surface, fan gate or fan payout, with the tier deliberately excluded because `Debut` is a name and not a course), the row carries `detail_recorded`, and `RaceCard.vue` folds the grid to one line when the caller asks. The fold is opt-in through `fold-undescribed`, which only `RacePlanList.vue` passes: the planner prints a whole career and asked for density, the Race decision screen prints one or two races and its spec holds that each refusal is on the page. The drawer keeps every fact either way. No distance, surface, reward or gate was invented, no route or write touched, `ADR-0016` untouched. | `CareerRacePlannerTest` pins the fold's input on both kinds of row (`detail_recorded` true for a described slot, false for a debut-shaped one), 26 passed (403 assertions). `career-race-planner.spec.ts` adds the rendered case: the debut card shows `Details not recorded for this race.` with its obligation and year lines kept, another card still shows its `Distance band`, and the debut's drawer still lists every refused field. The first version of this folded on both screens and broke `career-race-decision.spec.ts:92` (the refusal disclosure assertion), which is the regression the opt-in exists to prevent. |
| **R2-13: Run identity incomplete** | Screen 8: Rarity and Potential Level named as missing but not editable | Fixed (2B A6.1, `b896d0b`) | **Fixed**, 2026-10-11, in `8e51b9f`: the read path existed, the write path did not | The migration added `trainee_rarity` and `potential_level` columns. The Cockpit's identity form should now include these fields. Verify the form renders them. | Check `Cockpit.vue` identity edit form for rarity/potential inputs. Add if missing. | `TraineeRarityPotentialTest` covers persistence; verify UI. |
| **R2-14: Unity Cup scenario state incomplete** | Screen 8: Team identity, rankings, burst counts have no input | Fixed (2B A6.6, A6.7, `709a233`, `89f3b11`) | **Fixed**, 2026-10-11, in `d8fb234`: the read path existed, the write path did not | Migrations added team identity and Unity Cup counter columns. The Cockpit's scenario panel should render these. Verify the panel displays the stored values. | Check `CockpitController::scenarioSection()` and `Cockpit.vue` team panel for rendered values. | `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest` cover persistence and rendering. |
| **R2-15: Active-career and Dashboard context** | Screen 1: Dashboard shows active career from first walk while allowing new draft | Known behavior (multi-run support) | **Known limitation (intentional)** | The Dashboard displays the most recent active career but also allows starting a new draft. This is by design to support multiple concurrent runs. | No change needed. Consider clarifying the Dashboard copy to explain multi-run support. | None needed. |
| **R2-16: Energy band not editable on the Cockpit correction form** | Cockpit correction writes `energy`; the guided training screen records a band beside it | Not addressed in the first audit | **Fixed**, 2026-10-11: the Cockpit form and read-back in `c3583f0`, the guided create path in `d21fcc1`, browser-verified | `turn_entries` carries the state and the band, and the guided screen writes them. The Cockpit's correction form posts the numeric reading only, so a band that is wrong stays wrong after a correction. Which behaviour is right (edit the band there, or show it read-only) is the owner's call and is recorded as such in the round-2 brief; no decision was inferred here. | Deferred: `Cockpit.vue` and `CockpitController.php` hold a concurrent session's uncommitted advisor refactor, so this file pair is not openable this session. | None yet. The chosen behaviour needs a round-trip test either way: write through the guided form, correct through the Cockpit, read back. |
| **R2-17: Run-scoped deck builder has no inbound link** | `/training-runs/{run}/deck` is reachable only by URL | Not addressed in the first audit | **Fixed**, 2026-10-11, in `5d26b49`, browser-verified | `runs.deck` answers, writes and reads back (Slice 2's `SupportDeckBuilderTest`), and the Training screen's door now points at it (`e2889ff`), but the Cockpit itself offers no door, so a Trainer mid-career cannot reach the deck builder by clicking. | Deferred with R2-16: the door belongs in `Cockpit.vue`, in the peer's dirty set. `TrainingDetail.vue`'s door landed first because that file was clean. | A feature test asserting the Cockpit response carries an `href` for `runs.deck`. |
| **R2-18: Training screen's deck door opened a new wizard** | Found by the R2-07 sweep of `runs.training` | Not in the second audit's list | **Fixed** in `e2889ff` | The "no Support Cards are recorded" line on the Training decision screen linked the literal `/career/setup/deck`. That route is step 5 of a fresh setup draft, so a Trainer with a career open was sent out of it into an unrelated draft and the career's own deck stayed empty; the label beside it, "Deck step", named the wizard rather than the destination. `runs.deck` was already the screen that writes this run's six slots. | The server now supplies `run.deck_url` beside the two doors already in that prop set, the anchor binds it, and the label reads "Record this run's deck". The wizard's own internal links (`SetupLayout.vue`, `LegacySelect.vue`, `DeckSelect.vue`) are unchanged, because there the next step is the next step. | `CareerTrainingDetailTest` pins `run.deck_url` at the same run's `runs.deck` and greps the component for the retired literal (present exactly once before, absent now). The screen's suite 10 passed (266 assertions) and the five suites that write through `runs.training` 46 passed (601 assertions). Not browser-verified: that spec's fixture equips a card, so the empty-deck branch never renders there. |

| **R2-19: The absent Global ruleset version is headed four different ways on six surfaces** | Found by the R2-05 residual sweep, 2026-10-10 | Not in the second audit's list | **Fixed** in `24f7e48`, browser-verified (was filed as open at the sweep, before slice 13 took it) | The same null value — `app.ruleset`, null by ruling because no source names a Global ruleset version (`design-2.0` §48) — reaches a Trainer under four headings: `Ruleset version` on the wizard card (`ScenarioSelect.vue:193`, `9777531`) and the Cockpit strip (`ScenarioPanel.vue:162`, `c7a9c87`), `Ruleset:` on the Dashboard's data-status panel (`Dashboard.vue:316`) and the Career Result's footer (`Result.vue:366`), `Ruleset snapshot:` on Preflight (`Preflight.vue:392`), and `Global Ruleset` on Preferences' game-version tab (`Preferences/Edit.vue:445`). Every one of them prints the refusal title that says *version*, so four of the six headings name the fact less exactly than the text beside them. R2-05 was one card carrying two headings for two facts; this is six surfaces carrying four headings for one fact. | Pick the card's wording, `Ruleset version`, and apply it to the four stragglers in one pass, leaving the wizard's `Rule family` row alone because that is a different fact. Values, sources and the `N/A`-with-`title` rule did not move. **Landed in `24f7e48`**, with Settings keeping its qualifier as `Global Ruleset Version`. | Two assertions pinned the old wording and moved with it: `dashboard.spec.ts` now takes the anchored version heading plus the retired `Ruleset:` as its red/green pair, and `preferences.spec.ts` matches with `exact`, because the retired label is a prefix of the new one and a substring match would pass on either wording. `CareerPreflightTest.php:132` pins Preflight's *value*, not its heading, and `CareerResultTest.php:240-241` pins the prop, so neither blocked the rename. `RulesetVersionVocabularyTest` now owns the rule: six named surfaces with the heading each must print, then a retired-wording sweep of all 105 `.vue` files under `resources/js`, the count case being the guard that keeps the sweep from reading as a pass when it scanned nothing. 2 passed (428 assertions); the two browser specs together 18 passed (7.7m) on `:8261`. |
| **R2-20: The Cockpit's page-wide 44px sweep fails on a collapsed disclosure's button** | Found while browser-verifying slice 11, 2026-10-10 | Not in the second audit's list | **Fixed** in `dc8ad7b` (KI-92 closed; was filed open at the sweep) | `scenario-panel.spec.ts:168-174` sizes every `main a, main button` by index. The run-delete door's submit button lives inside a closed `<details>` (`Cockpit.vue:661-684`), so it is in the DOM with no box, reads 0, and the case fails at `control 17` — the 18th control, one past the 17 the failure's own accessibility listing puts inside `main` before that door. The sweep was written by `6a74f85` (2026-10-08); the door arrived with `91494a1` (2026-10-09). The markup is compliant and the instrument is not: `ura-panel.spec.ts:206-223` already skips controls that `isVisible()` rejects and guards the skip so it cannot pass on nothing. | Landed as its own slice once the brief made it openable: the rule moved to `tests/utils/tap-targets.ts` next to `buildAxe` as `expectTapTargets(targets, minimumMeasured)`, and the four Cockpit sweeps call it instead of each carrying the loop — `scenario-panel.spec.ts`, `grand-concert-panel.spec.ts`, `trackblazer-panel.spec.ts`, `unity-cup-panel.spec.ts` — plus `ura-panel.spec.ts`, which had hand-rolled the same skip and keeps its `> 8` guard as `minimumMeasured = 9`. The second remedy KI-92 listed (open every disclosure before measuring) was not taken: the page-wide sweeps skip, and the opened-disclosure coverage lives in one new case in `career-cockpit-forms.spec.ts`, which opens the run-delete door and asserts the floor on what it reveals. Three latent sweeps keep a one-line signpost instead of a conversion. | Six affected specs together 53 passed (25.9m) on `:8262` with its own scratch database, including every case this row predicted would fail (`scenario-panel.spec.ts:162`, `grand-concert-panel.spec.ts:107`, `trackblazer-panel.spec.ts:226`, `unity-cup-panel.spec.ts:189`), the converted `ura-panel.spec.ts:200`, the header sweep at `career-cockpit-forms.spec.ts:237` and the new case at `:217`. No floor removed or lowered. Ad-hoc `tsc --noEmit` over the helper and all nine specs exit 0 — see R2-21 for why that is not `npm run typecheck`. |

| **R2-21: No npm script type-checks `tests/`, so a browser-spec commit reports a typecheck gate that cannot see it** | Found while landing R2-20's fix, 2026-10-10 | Not in the second audit's list | **Fixed** in `b558a97` (KI-93 closed; was filed open at slice 14) | `tsconfig.json:15` includes `resources/js/**/*.ts`, and `npm run typecheck` is `tsc --noEmit` against that config. The 48 specs under `tests/browser/` and the four shared modules under `tests/utils/` sit outside it and are only transpiled at run time by Playwright, so a type error there surfaces as a failing spec or not at all. Every commit in this register that touched a browser spec reported a green typecheck gate that never read the file it changed. | Owner-authorised as Option A: `tsconfig.json`'s `include` gained `tests/browser/**/*.ts` and `tests/utils/**/*.ts`, and `types` gained `"node"`, which required `@types/node` as a dev dependency (absent from the tree and the lockfile, so not available transitively). Widening surfaced **25 errors across 8 files**, all fixed in the same commit: 16 node-type (15 TS2591 + 1 TS2503 `NodeJS`), 5 `window.__setModel`/`window.__setBadgeState` now declared once on `Window` by `tests/browser/fixtures/fixture-globals.d.ts`, 2 `.vue` imports covered by `tests/browser/fixtures/shims-vue.d.ts`, and 2 `PerformanceEntry.transferSize`/`decodedBodySize` in `screen-speed.spec.ts` narrowed by an `entry is PerformanceResourceTiming` predicate. | `npm run typecheck` **exit 0, zero errors** under the widened scope, against 25 before; the `"node"` types did not disturb `resources/js`, which was the risk of widening one config instead of adding a second. `composer lore` 313 and `lore-code` 118, both exit 0 — the latter at its baseline after the new shim's prose changed from the word `mount` (on the banned family list) to `render`, rather than ruling a hit a plainer word avoids. Pint and PHPStan not applicable (no PHP changed). No browser run: two declaration files and one type predicate, no rendered change. `npm audit --omit=dev` is red at 1 high in `source-map-js`, pre-existing and filed as KI-94. |

## Baseline Test Report

- **Migrations:** All 58 migrations Ran, no Pending (`php artisan migrate:status` clean).
- **Focused tests:** Not yet run. Will execute per-slice tests during implementation.
- **Browser tests:** Not yet run. Harness requires session-private port and scratch database.
- **Static analysis:** `vendor/bin/phpstan` and `npm run typecheck` not yet executed.

## Proposed Slice Order

### Phase 1: Snapshot Integrity (Priority 0)
1. **R2-02:** Investigate legacy save disappearance (verify if regression or user error in audit)
2. **R2-03:** Clarify Preflight warning/blocking semantics (document or implement blocking)
3. **R2-01/R2-04:** Clarify snapshot vs new-career flow (update docs or add snapshot import path)

### Phase 2: Cross-Screen Semantic Consistency (Priority 1)
4. **R2-05:** Fix duplicate Ruleset rows in scenario cards (landed 2026-10-10, see Slice log)
5. **R2-06:** Update obsolete deck ownership copy (landed 2026-10-10, `dd179ba`)
6. **R2-07:** Remove remaining stale "run record screen" references (landed 2026-10-10, `380350e`)
7. **R2-09:** Reconcile stat denominator logic across screens

### Phase 3: Decision Quality (Priority 1)
8. **R2-10:** Fix skills planner empty-set calculation (landed 2026-10-10, `d95da07`)
9. **R2-12:** Compact race planner presentation for placeholder races (landed 2026-10-10, `473bb19`)

### Phase 4: Remaining Context (Priority 2)
10. **R2-13:** Verify rarity/potential UI fields exist
11. **R2-14:** Verify Unity Cup state rendering in Cockpit
12. **R2-15:** Document multi-run Dashboard behavior (optional)

## Concurrent Changes Constraint

The working tree has one modified file: `docs/audits/rice-shower-unity-cup-ux-walk.md` (uncommitted). This is the first audit document, which should not be edited. The second audit (`rice-shower-unity-cup-ux-walk-2.md`) is untracked and will be committed separately.

No other sessions' work is present in the tree, so edits to controllers and Vue components are safe.

## Next Action

Begin with **R2-02** (legacy save investigation) to determine if this is a regression or an audit methodology issue. This is the highest-risk finding because it affects data persistence.

## Slice log, 2026-10-10

Erratum on the two state claims above, both written before this slice and both now stale. Line 44 said `phpstan` and `npm run typecheck` had not been executed; they have been, on this slice, and are recorded below. Line 70 said the working tree held one modified file and "no other sessions' work"; the tree now also carries `tests/Feature/CareerLegacyDeckStepsTest.php` modified (the prior session's R2-02 regression test, uncommitted) plus this slice's four files, so a commit here is made with an explicit pathspec and nothing else.

### R2-02, carried forward, not reopened

The persistence path was already read end to end and the regression test exists and passes. The only new fact this session added is the disproof of hypothesis (a) recorded in the table row: the `sessions` table exists on the dev database with 3 rows and `.env` reads `SESSION_DRIVER=database`. No reproducible failure was demonstrated by any test, so R2-02 stays "code path not at fault, cause unidentified". The prior session's test remains uncommitted, which is a landing question for the owner rather than a defect.

### R2-05, closed

| Item | Evidence |
| --- | --- |
| Root cause | Two different facts printed under one heading in `ScenarioSelect.vue`: the grid's tool-wide ruleset *version* (`ScenarioSelectController::ruleset()`, null by ruling, `design-2.0` §48) and, since `1098546`, the Unity Cup card's rule *family* (`metadata.ruleset` from `config/scenarios.php`, `'Team'`). `config/scenarios.php:225` already distinguishes them in prose; the template reused the heading anyway. |
| Files changed | `resources/js/pages/Career/ScenarioSelect.vue` (two `<dt>` headings, one `title` wording), `tests/Feature/ScenarioMetadataTest.php` (new heading-set case), `tests/browser/career-scenario-select.spec.ts` (updated one selector, new rendered case), `SCREEN_SPEC.md` SCR-CAR-002 (Layout/Content paragraph, absent-field row, test list). |
| RED first | `vendor/bin/pest --filter=ScenarioMetadataTest` before the fix: `Failed asserting that two arrays are identical`, index 5 `Ruleset` expected `Ruleset version`, index 8 `Ruleset` expected `Rule family`, 1 failed / 1 passed. The pattern matched all ten headings, so the guard is not vacuous. |
| Focused green | `vendor/bin/pest --filter=ScenarioMetadataTest` after the fix: 2 passed (20 assertions). |
| Related regression | `vendor/bin/pest tests/Feature/CareerScenarioSelectTest.php tests/Feature/CareerPreflightTest.php`: 22 passed (336 assertions), which covers the cards' prop derivation and the Preflight contract that reads the same config key. `CareerScenarioSelectTest` alone: 10 passed (161 assertions), the figure `SCREEN_SPEC.md` cites. |
| Browser | `PLAYWRIGHT_PORT=8146 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-8146.sqlite npx playwright test career-scenario-select.spec.ts` after `npm run build`: 7 passed (25.6s). The new case asserts the rendered Unity Cup card shows `Ruleset version · N/A` beside `Rule family · Team`, and that no element on the page is headed bare `Ruleset`. Card inspected in Chromium, so the fix is browser-verified, not only source-verified. |
| Static analysis | `npm run typecheck` exit 0. `vendor/bin/pint --test --format agent tests/Feature/ScenarioMetadataTest.php` passed (explicit path only, never `--dirty`, KI-75). `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`: `[OK] No errors`. `composer lore` exit 0 (303 doc hits, its standing count; the two `SCREEN_SPEC.md` hits are lines 20 and 1000, neither edited here) and `composer lore-code` exit 0 (118 hits; no hit falls on any file this slice changed, so no new ruling is owed). |
| Commit | `9777531`, four paths by explicit pathspec, verified after the fact as `git diff 9777531 -- <those paths>` returning nothing and the commit touching nothing else. The audit documents and the prior session's `CareerLegacyDeckStepsTest.php` change were deliberately left uncommitted: they are not this slice, and `docs/audits/rice-shower-unity-cup-ux-walk.md` (the first audit, modified in the tree by another hand) must not land as part of a code fix. |
| Not covered | `ScenarioPanel.vue:160` still heads the same tool-wide version row plain `Ruleset` on the Cockpit's scenario strip. There it is the only ruleset fact on the surface, so nothing contradicts it and the second audit reported no defect; left alone rather than widened into a second screen. If a future slice puts a rule family beside it, the same collision returns and the heading should follow this one. |

### Next action

R2-06 (obsolete support-deck ownership copy) is the next slice in the queue and the next cheapest: verify the `deck_slots.ownership` write/read path, then correct the copy in `DeckSelect.vue`.

## Slice 2 log, 2026-10-10

### Pre-flight, re-recorded

HEAD was `9777531` with exactly the four peer artifacts uncommitted (`docs/audits/rice-shower-unity-cup-ux-walk.md`, `tests/Feature/CareerLegacyDeckStepsTest.php`, the two untracked audit documents). Browser harness available and used: `npx playwright test` with `PLAYWRIGHT_PORT` and `PLAYWRIGHT_SCRATCH_DB` pairs, `npm run build` first whenever a `.vue` file changed. New in the tree during this slice and not mine: `docs/research/ui-ux-application-context-brief.md` (untracked).

### R2-06, closed

| Item | Evidence |
| --- | --- |
| Root cause | Correction did not propagate. `fdebcc9` (2026-10-09, D3, `ADR-0023`) added the nullable `deck_slots.ownership` column, wired both writers, and rewrote the run screen's sentence in the same commit. The wizard's step 5, the Preflight contract, and five comments in the write path kept the pre-D3 wording. `git log -S"stores no field" -- resources/js/pages/Support/Builder.vue` shows the pair: added by `05e9584` (2026-10-06), removed by `fdebcc9`. |
| Persistence, verified not assumed | Column: `database/migrations/2026_10_09_120000_add_ownership_to_deck_slots_table.php`. Writers: `PreflightController::store()` line 140 and `TrainingRunController::syncDeck()` line 987. Reader: `TrainingRunController` line 269. No persistence code was touched by this slice. |
| Files changed | `resources/js/pages/Career/DeckSelect.vue`, `resources/js/pages/Career/Preflight.vue` (shipped sentences plus their HTML comments), `resources/js/components/support/SupportSlot.vue` (docblock), `app/Http/Requests/StoreDeckRequest.php` (two comments), `app/Http/Requests/Career/StoreDraftDeckRequest.php` (two docblocks), `app/Http/Controllers/Career/DeckSelectController.php`, `app/Http/Controllers/TrainingRunController.php`, `app/Services/Career/SetupDraft.php` (one docblock each), `tests/Feature/SupportDeckBuilderTest.php` (two new cases plus its own stale header citation), `tests/browser/support-deck.spec.ts`, `tests/browser/career-legacy-deck-steps.spec.ts` (the two assertions that quoted retired copy), `SCREEN_SPEC.md` SCR-CAR-007 (Persistence, Implementation References, Gap 1) and SCR-CAR-009/010 status rows. |
| New finding: a red browser test that was not in the baseline | `tests/browser/support-deck.spec.ts:196` asserted the run screen's pre-D3 sentence and therefore failed from `fdebcc9` onward, while the PHP suite stayed green because it asserts props, not copy. Classified pre-existing (introduced 2026-10-09 by the D3 copy change, not by this slice) and corrected here. |
| New finding: a phantom test citation | `SCREEN_SPEC.md:1735` and `SupportDeckBuilderTest`'s header both cite `RunDeckTest`. No such file exists in `tests/` (glob plus `git log` both empty), so the run-scoped deck write had no owner: nothing posted a flag through `runs.deck.sync` in any test. Both citations are corrected and the write is now pinned by `SupportDeckBuilderTest::'it writes the flag this screen posts onto the slot row and reads it back'`. |
| RED first | The sweep case failed on the first surface, `Expecting '<script setup lang="ts">…' not to match '/no column for it|has no owne… only/'`, 1 failed. It then failed on the missing affirmative sentence, so both halves bite. |
| Focused green | `vendor/bin/pest tests/Feature/SupportDeckBuilderTest.php tests/Feature/CareerPreflightTest.php` exit 0, 38 passed (560 assertions). |
| Related regression | `vendor/bin/pest tests/Feature/CareerLegacyDeckStepsTest.php` exit 0, 14 passed (269 assertions), unchanged by the copy edit and still green with the peer's uncommitted R2-02 case in it. |
| Browser | `npm run build` (21.67s), then `PLAYWRIGHT_PORT=8147 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-8147.sqlite npx playwright test career-legacy-deck-steps.spec.ts support-deck.spec.ts -g "six slots, the seven spelled types|keeps the ownership choice"`: 2 passed (44.5s). Both screens' sentences read in Chromium; the run screen's toggle interaction (aria-pressed per slot) still passes. |
| Gates | `npm run typecheck` no diagnostics. `pint --test --format agent` on the six changed PHP paths: `{"tool":"pint","result":"passed"}`. `phpstan analyse --no-progress --memory-limit=1G`: `[OK] No errors`. `composer lore` exit 0 at 303 doc hits and `composer lore-code` exit 0 at 118 code hits, both identical to the Slice 1 counts, so this slice added no hit. Note: an earlier lore-code run reported 166 because this session's own `.scratch-*.log` outputs were untracked in the repo root and the gate scans untracked files; the logs were moved to `/tmp` and the gate re-run. |
| Commit | `dd179ba`, pathspec-limited to the twelve files above. The four peer artifacts stayed uncommitted. |
| Not covered | The wizard's step 5 still refuses a save that equips a card without a flag (`StoreDraftDeckRequest` `required`), while the run screen accepts an absent one and keeps the stored value. That asymmetry is `ADR-0023`'s and is now described accurately on both screens; nothing about it was changed. |

### Next action

R2-07 (stale run-record references) is next: sweep every user-facing "run record screen" / "run screen" instruction, trace each to the surface that owns the action today, and add the repository-grep guard the resolution record names `NoStaleRunRecordLinksTest`.

## Slice 3 log, 2026-10-10

### R2-07, closed

| Item | Evidence |
| --- | --- |
| Root cause | A retired surface left its name behind in prose. `F2` deleted `Runs/Show.vue`, made `GET /training-runs/{run}` a redirect, and moved every write to a 2.0 owner; `d251f7f` relabelled the nav rail. Nothing swept the sentences. Seventeen of them survived in nine sources, and two links still addressed the doorway itself. |
| Files changed (19) | `app/Http/Controllers/Career/{CockpitController,ResultController,TimelineController,SaveVeteranController,SkillsPlannerController,RacePlannerController}.php`, `app/Http/Controllers/{TrainingRunController,VeteranController}.php`, `app/Services/RaceFacts.php`, `resources/js/pages/Career/{TrainingDetail,EventDecision,InheritanceEvent}.vue`, `resources/js/pages/Veterans/Show.vue`, `resources/js/components/career/RaceCard.vue`, `tests/Feature/{NoStaleRunRecordLinksTest,CareerRacePlannerTest,VeteranLibraryScreenTest}.php`, `tests/browser/career-race-strip.spec.ts`, `SCREEN_SPEC.md`. |
| Guard | `NoStaleRunRecordLinksTest` existed at `d251f7f` with two cases; both kept verbatim, two added. Its `run screen` and `career record` patterns were widened only after a concept search (not a phrase search) found the same misdirection under a second name. The `runs.show` whitelist counts the doorway's own two redirects, so the guard narrows links without touching the ruling. |
| RED first | The widened sweep named nine files; the `runs.show` link sweep named two. After the edits, 4 of 4 cases pass, and `CareerRacePlannerTest`'s new case pins a destination so a sentence cannot pass by losing one. |
| Tests | Guard 4 passed (5 assertions). `CareerRacePlannerTest` 21 passed (370 assertions) with the new case. Touched-screen batch 121 passed (1968 assertions), then 127 passed (2108 assertions) after the sweep widened. `CareerLegacyDeckStepsTest` 14 passed (269 assertions), so the prior session's held test stays green against these strings. Final combined run across R2-05, R2-06 and R2-07 suites: 61 passed (988 assertions). |
| Browser | `npm run build` exit 0, then `PLAYWRIGHT_PORT=8151 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-8151.sqlite npx playwright test career-race-strip.spec.ts career-cockpit.spec.ts -g "three run-scoped regions|says so when there is no turn being decided|walks the action grid|sizes the cockpit controls"`: 4 passed (2.2m). `career-race-strip.spec.ts` in full: 6 passed (1.5m). Both former stale link assertions read `Go to the Cockpit` in Chromium. |
| Gates | `npm run typecheck` exit 0. PHPStan level 6 `[OK] No errors`. Pint `{"tool":"pint","result":"passed"}` on the slice's PHP paths. `composer lore` 303 doc hits / `composer lore-code` 118 code hits, both exit 0, both unchanged from the standing counts. |
| Pre-existing failures observed, classified, not caused | (1) `career-cockpit.spec.ts:263` (advisor ranked-deficit line) fails in the tree: the concurrent `NextDecision` refactor holds `CockpitController.php`, `Cockpit.vue`, `types.ts`, `AdvisorRail.vue`, `CareerCockpitTest.php` and two untracked new files, all uncommitted, and the advisor region renders only its intro there. Not this slice; not fixed here. (2) Pint reports `method_chaining_indentation` in `tests/Feature/FinaleReportingTest.php` and `FinaleVocabularyTest.php`, files this slice never touched; a whole-directory sweep surfaces them, an explicit-path run does not, which is KI-75's exact trap. Reported, not reformatted. |
| New finding filed | `KI-91` (`e546d5b`): the Skills planner has no link into it anywhere in `app` or `resources`; `git grep -n "runs\\.skills\\.planner" -- app resources routes` returns the route declaration alone and no `skills_planner_url` prop exists. The screen works (19 + 4 cases), so this is an entry point lost in the F2 cutover, and where the door belongs is an `ADR-0020` §1 decision. R2-07's copy is accurate and points at it; the reachability half stays open. |
| Concurrency incident, recorded in full | The commit landed wrong and was corrected in the open. `380350e` was built with a pathspec commit, and a pathspec commit takes **working-tree** content: the spliced `CockpitController.php` I had staged never reached the file (the swap was rewritten by a local hook), so `380350e` captured the concurrent session's uncommitted `NextDecision` refactor and referenced a class that is untracked. A clean checkout of it fatals on every Cockpit route. Detected by diffing the committed blob against HEAD rather than trusting the working tree (`git show HEAD:... | grep -c NextDecision` → 1). `a1379bd` restored the file to HEAD's controller plus only R2-07's four strings, verified by sha256 against the spliced blob (`3514570…`), and `git grep -l "NextDecision" HEAD` now returns nothing. The other session's file was then put back byte-for-byte (`83badb71…`, matched against the pre-swap snapshot), and their work remains uncommitted and intact. **Rule for the next slice in this worktree: after any pathspec commit, diff the new commit against the previous HEAD for every file that was dirty before you started; a clean `git status` afterwards proves nothing about what went in.** |

### Next action

R2-09 (stat denominator semantics) is next. Read `CockpitController`'s stat strip and `EventDecisionController`'s band rows before choosing semantics: a build target and a scenario cap are different facts, `ADR-0015` makes `ScenarioCaps` the single ceiling owner, and `2026_10_10_170000_drop_stat_ceilings_from_training_runs` already removed the orphaned column. Also decide, inside this slice or the next, whether `ScenarioPanel.vue:160`'s plain `Ruleset` heading follows Slice 1's split.

## Slice 4 to 6 log, 2026-10-10

Pre-flight HEAD was `e546d5b` with the dirty set the round-2 brief listed. That set grew during the session: `config/scenarios.php`, `SCREEN_SPEC.md` and seven `docs/research-scratch/` masters are now also held by the concurrent session, so `SCREEN_SPEC.md` took **no** edit in slices 4, 5 and 6; each slice's spec row is named below as held rather than left stale silently.

| Slice | Finding | Commit | What it changed | Evidence |
| --- | --- | --- | --- | --- |
| 4 | R2-10 | `d95da07` | `coverage.state` (`no_priorities` / `covered` / `unpriced` / `cost`) on the Skills planner; the empty priority list no longer prints a figure, an all-learned list prints its real zero with the reason, `warn` is confined to `cost`. `SkillSpendCoverage`, the priority schema, `runs.build-target.update` and `ADR-0001`'s withdrawn ladder untouched. | `CareerSkillsPlannerTest` 21 passed (306 assertions) with two new cases; `SkillSpendCoverageTest` 5 passed (56); `career-skills-planner.spec.ts` 5 passed (1.1m) on `:8152`, its new case asserting the absence renders and `still to learn cost 0 SP` does not exist in the DOM; typecheck 0, Pint passed, PHPStan `[OK] No errors`, lore 303 / lore-code 118 at exit 0. |
| 5 | R2-18 (new row) | `e2889ff` | The Training decision screen's empty-deck door opened `/career/setup/deck`, a fresh wizard step 5, instead of the run's own builder. `run.deck_url` now arrives from `TrainingDecisionController` and the label reads "Record this run's deck". The wizard's own internal links are unchanged. | `CareerTrainingDetailTest` 10 passed (266 assertions) pinning the prop at `runs.deck` for the same run and the retired literal absent from the component (it was present exactly once); five suites writing through `runs.training` 46 passed (601 assertions). Not browser-verified: that spec's fixture equips a card, so the empty-deck branch never renders there. |
| 6 | R2-12 | `473bb19` | `RaceFacts::describesRace()` (distance, band, surface, fan gate, payout; a tier is excluded because `Debut` names an occasion rather than a course), `detail_recorded` on both rows that print `RaceCard`, and an opt-in `fold-undescribed` that only `RacePlanList.vue` passes. Placeholder cards now read one line where the planner prints a career of them; the drawer keeps every fact, and no race detail was invented. | `CareerRacePlannerTest` 26 passed (403 assertions) pinning both sides of the fold's input; both race specs together 11 passed (5.2m) on `:8156`. **A caught regression worth the record:** the first version folded on both screens and broke `career-race-decision.spec.ts:92`, which holds that each refused field is on the page and reachable; the opt-in prop is what fixes it, and the planner-only scope is now the tested boundary. Typecheck 0, Pint passed, PHPStan `[OK] No errors`, lore counts unchanged. |

`SCREEN_SPEC.md` rows left held, one per slice, because the file is the concurrent session's working property: SCR-CAR-016's coverage sentence (now four states, not a computed total), SCR-CAR-012's door list (the deck door is run-scoped now), and SCR-CAR-023's per-race row list (a placeholder row folds to one line, its drawer keeps the ten). Each is a one-line edit against copy the spec already carries; whoever lands the advisor refactor can take them with it, or the next session can once the file is clean.

### Blocked, and the reason is not this register

Slice 11 (the R2-05 residual on `ScenarioPanel.vue`) was opened and is **held uncommitted**: the heading now reads `Ruleset version:`, the comment says why, and two assertions were written (`ScenarioMetadataTest`'s vocabulary case, `scenario-panel.spec.ts`'s rendered case). None of it is verified, because the feature suite stopped being runnable mid-session: the concurrent session's **untracked** migration `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php` fatals during `RefreshDatabase`'s migrate. Observed twice inside ten minutes, with the error changing between runs, which is what a half-saved file looks like: first `Method Illuminate\Database\Schema\SQLiteBuilder::dropIndex does not exist`, then at that file's line 74 `Call to undefined method Illuminate\Database\Migrations\Migration@anonymous::quote()`.

The proof that it is not this session's: `CareerRacePlannerTest` passed 26 of 26 with 403 assertions at `473bb19` and minutes later reported **20 failed (0 assertions)**, every case dying at `Migrator.php:517` before an assertion ran. `ScenarioPanelTest` and `ScenarioMetadataTest` failed the same way, 26 failed / 0 assertions. That is every feature test in the tree, for every session, and it is why slices 7 to 10 (all of them already blocked on the advisor refactor) and slice 11 cannot be finished here.

Nothing in the migration, in `config/scenarios.php`, in `SCREEN_SPEC.md` or in any file of the concurrent session was edited, staged, moved or reverted by this session. Held, uncommitted, from slice 11: `resources/js/components/scenario/ScenarioPanel.vue`, `tests/Feature/ScenarioMetadataTest.php`, `tests/browser/scenario-panel.spec.ts`.

**Superseded later the same day, and the paragraph above stays as it was written.** The pre-flight gate
`vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` now passes, 3 passed / 22 assertions, so the
untracked turn-entries migration no longer fatals `RefreshDatabase` and slice 11 was verified and landed
in `c7a9c87`. The file is still on disk and still `Pending` on the dev database (`php artisan migrate:status`
→ `[14] Ran`, then `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table .... Pending`), which
is its author's to run; nothing here migrated the shared file. What still blocks slices 7 to 10 is not the
suite any more, it is the concurrent set itself, and it grew while slice 11 was being verified:
`app/Http/Controllers/Career/TimelineController.php`, `app/Http/Controllers/TrainingRunController.php`,
`app/Http/Requests/StoreTurnEntryRequest.php` and `app/Models/TurnEntry.php` are now dirty too, with
`app/Enums/TurnChoice.php` and `resources/js/components/career/RecordedActionCard.vue` untracked beside them.
That set is FD-4's turn-choice and correction work, and it lands **on top of** R2-16's target: the energy band
this slice would add to the Cockpit correction form is a field of the same correction path those files write.
Opening `StoreTurnEntryRequest` or `TurnEntry` now would be a second writer on one in-flight contract, so R2-16
is blocked by two sessions' work rather than one, and the brief's own instruction applies verbatim: wait for
the commit, or pick a different slice.

**Checked the claim above against HEAD, and it is half wrong.** The band fields are already committed, so R2-16
needs **no** write-path change: `StoreTurnEntryRequest.php:128-132` at HEAD validates `energy_state` as the enum
and `energy_band` as required-if-band, `TurnEntry` carries both in `#[Fillable]` and casts the state, and the
guided screen already renders the control (`TrainingDetail.vue:425-455`, clean at this writing). What is missing
is only the Cockpit's own correction form, which posts the numeric reading and nothing else, so the slice is
`Cockpit.vue` plus its controller's read-back and those tests, four files, all four in the peer's dirty set.
The peer's current work on the same path is the second reason to stay out: the columns they are adding now
(`choice`, `decision_snapshot`, `corrected_from`, `superseded_by`) reshape what a correction *is*, and a band
control bolted onto a form that is about to record a choice would be rebuilt by them within the hour. R2-16
therefore stays blocked, but it is a smaller slice than this register said, and its remaining risk is the form's
shape, not the schema.

### Next action

Restore the suite before any further slice: the turn-entries migration needs its index drop and its quoting done the way the three migrations beside it already do it (`grep -l dropIndex database/migrations` names them), and that is its author's edit, not a sweep to be done from outside. Then, in order: verify and land slice 11; take slices 7 to 10 once `Cockpit.vue`, `CockpitController.php`, `CockpitObjectivesTest.php` and `NextDecision*` are committed and the tree is quiet; and settle R2-16's product question (edit the band on the Cockpit, or show it read-only) before code, since both answers are defensible and only one of them is a feature.


### Not covered by R2-07, deliberately

Four docblock sentences in `CockpitController.php` still describe the retired
screen as a present surface ("The full calendar stays on the run record screen", "built for the run record
screen where the whole calendar is the question"). They are internal comments, the guard's own rule treats
them as the allowed historical class, and that file is the concurrent `NextDecision` slice's working
property: touching it again to edit prose would splice across someone else's in-flight refactor for no
user-visible gain. Same for the `CareerSurfaceCutoverTest` and `career-surface-cutover.spec.ts` headers,
which still state that the record screen "is still the only place a Trainer can reach" four writes; `F2`
withdrew that and the comments did not follow. Recorded here as the next author's cleanup, listed by file
so it can be done in one pass with their landing.

## Slice 11 and 12 log, 2026-10-10 (second sitting)

Pre-flight, run as the brief ordered and as the only command before any slice opened:
`vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` -> **3 passed (22 assertions), exit 0**. FD-4 is off the
migration path for `RefreshDatabase`, so slices opened. HEAD at pre-flight was `473bb19`; the dirty set was the
one the brief listed, and it grew (the FD-4 files named above).

| Slice | Finding | Commit | What it changed | Evidence |
| --- | --- | --- | --- | --- |
| 11 | R2-05 residual | `c7a9c87` | The Cockpit scenario strip headed its one ruleset fact `Ruleset:`. Now `Ruleset version:`, the word the wizard card took in `9777531`, with the comment saying why the strip carries the version and not the rule family. No value, no absence, no `N/A`-with-`title` rule moved. | `ScenarioMetadataTest` 3 passed (22 assertions) including the new heading comparison; `scenario-panel.spec.ts` 7 passed / 1 failed on `:8255` with its own scratch database, the failure being KI-92's sweep and not this change. RED proved without touching the tree: `git show HEAD:resources/js/components/scenario/ScenarioPanel.vue` line 160 reads `Ruleset:`, which is what both the feature matcher (`/^\s*Ruleset:$/m`) and the rendered case (`toHaveCount(0)`) were written against. Typecheck 0, Pint passed on the changed test file, `composer lore` exit 0 and `composer lore-code` exit 0 at the standing 118 hits, with **zero** hits in any changed file on both gates. |
| 12 | R2-01 / R2-04 | `65d7068` | The Dashboard's snapshot door existed, but only inside `<section v-else>`, the no-career state. The sentence moved outside both career branches; its words, its `href="/career/snapshot"` and its destination are untouched. R2-01 needed no code: the wizard's Turn 1 is what a run with no logged turn knows. | `dashboard.spec.ts` **6 passed (2.8m)** on `:8257`, now asserting the door in the empty state *and* the active-career state, the second also pinning the href so the case cannot pass on a link that goes elsewhere. Typecheck 0, `npm run build` 0, `lore-code` 118 at exit 0 with zero hits in either file. Not inspected by screenshot: the evidence is Playwright's rendered role and href assertions against the built bundle, which is what "browser-verified" claims here and nothing more. |

Two things the brief did not ask for and this session did not do: it did not touch `StoreTurnEntryRequest`,
`TurnEntry`, the FD-4 migration or the `runs.show` redirect, and it did not run the full feature suite or
`php artisan test --compact`. Both slices are copy and layout, which the change-class table places at the
affected-feature-test row, and the tree carries two sessions' in-flight work that a broad run would attribute
to this one. **`php artisan migrate:status` was run and is not clean**: `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table`
is `Pending` on `database/database.sqlite`, the concurrent author's file, not run here because migrating the shared
dev database is theirs to decide.

**PHPStan is red for a reason outside this work.** `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
-> exit 1, one error: `app/Services/Advisor/NextDecision.php:65`, `missingType.iterableValue` on
`snapshotFor()`'s array return type. That file is untracked (`?? app/Services/Advisor/NextDecision.php`), it is
FD-2's, and `phpstan.neon` analyses `app` only, so neither of this session's two PHP-side edits (both under
`tests/`) was even in scope. Not edited, not silenced, not counted as passing.

**Re-read later in the same sitting, PHPStan is exit 0, and the reason is the peer's.** The offending file left
the tree: `app/Services/Advisor/NextDecision.php` is neither tracked (`git ls-files app/Services/Advisor/` answers
`Advice`, `AdvisorOption`, `TrainerAdvisor` only) nor listed as untracked, so FD-2's untracked class was withdrawn
mid-session and its `missingType.iterableValue` went with it. `Cockpit.vue`, `CockpitController.php`, `types.ts`,
`AdvisorRail.vue`, `CareerCockpitTest.php` are all still dirty, so the withdrawal did not unblock slices 7 to 10.
Both readings are true of the moment each was taken, which is why they are recorded as two rather than the later
number replacing the earlier one.

**New findings, filed and not fixed here.** R2-19: one absent fact, four headings across six surfaces, with the
two test assertions that pin the old wording named in the row. R2-20 / **KI-92**: three Cockpit 44px sweeps
measure DOM presence rather than rendered targets, red since `91494a1` put a `<details>`-held confirm button on
the page `6a74f85`'s sweep reads, with the repo's own remedy already sitting in `ura-panel.spec.ts:206-223`.
`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` and `SCREEN_SPEC.md` took no edit from these two
slices, and neither did `rice-shower-unity-cup-ux-walk.md`, which was already dirty in the tree before this
sitting opened; SCR-001's door row and the strip's heading row are left as the next author's one-line changes,
stated here instead of silently stale.

### Slice 13 log, same sitting: R2-19 closed in `24f7e48`

Slice 11 renamed one surface and stopped, which is the wrong shape for a vocabulary fix: a correction that
leaves four siblings printing the retired word is a half-correction, and the retired literal survives its own
fix. So the sweep continued and found four more headings for the same absent value.

Seven files, one line each on the four components, and three test edits:

| File | Change |
| --- | --- |
| `resources/js/pages/Dashboard.vue` | `Ruleset:` -> `Ruleset version:` on the data-status panel's row |
| `resources/js/pages/Career/Result.vue` | `Ruleset:` -> `Ruleset version:` on the result footer's row |
| `resources/js/pages/Career/Preflight.vue` | `Ruleset snapshot:` -> `Ruleset version:` on the contract read-back |
| `resources/js/pages/Preferences/Edit.vue` | `Global Ruleset` -> `Global Ruleset Version`, keeping the tab's own qualifier |
| `tests/browser/dashboard.spec.ts` | the anchored new heading, plus the retired `^\s*Ruleset:` as `toHaveCount(0)` |
| `tests/browser/preferences.spec.ts` | `exact` on the new label, with the retired label asserted absent: the old wording is a prefix of the new one, so a substring match would pass on either |
| `tests/Feature/RulesetVersionVocabularyTest.php` | new. Six named surfaces with the heading each must print, then the retired-wording sweep over every `.vue` under `resources/js` |

Gates for this slice: `RulesetVersionVocabularyTest` 2 passed (428 assertions, 105 components swept);
`dashboard.spec.ts` + `preferences.spec.ts` 18 passed (7.7m) on `:8261` with its own scratch database;
typecheck 0; `npm run build` 0 (3m 55s, against 1m 29s earlier in the same session, which is the host and not
the change); Pint passed on the new file; `composer lore` 313 and `composer lore-code` 118, both exit 0 and both
at their standing counts.

**RED is asserted from pre-edit file contents, not from a red run.** The four old strings were read this session
before each edit (`Dashboard.vue:316`, `Result.vue:366`, `Preflight.vue:392`, `Preferences/Edit.vue:445`), and the
first run of the new guard did fail — on its own count guard, `Failed asserting that 0 is greater than 50`,
because `File::files()` is not recursive and the sweep had read nothing. That is the instrument proving it can
see, which is the property that matters most in a guard of this shape; it is not proof the four headings were
wrong, and this register does not claim it is. Reverting a file to prove red was not attempted: the session's
task guard holds the working tree, and the last time a revert was reached for, it was blocked as undoing
validated work.

**Two harness runs failed before this one passed, and the cause is boot, not the app.** `:8258` and `:8259` each
ended in `Error: Timed out waiting 60000ms from config.webServer` with a three-line log: no global-setup output
at all, which places the failure before the scratch database was built. `php -l config/scenarios.php` clean,
`php artisan about` exit 0, and a manual `php artisan serve --port=8260` answered `/up` in 0.15-0.5ms while
Playwright's own server never bound. Classified as infrastructure under host load, reproduced as green on the
third port rather than explained away; `migrate:status` was unaffected.

### Slice 14 log, same sitting: R2-20 closed in `dc8ad7b`

Pre-flight for this slice: HEAD `24f7e48`; `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` 3 passed
(22 assertions), exit 0; the FD-2 set still dirty (and `app/Services/Advisor/NextDecision.php`, which had left the
tree during slice 13's PHPStan read, was back as untracked), so slices 7 to 10 stayed closed and R2-20 was the only
openable slice.

Reconnaissance, since the brief asked for the full set rather than the two specs it named. `git grep`-equivalent
enumeration of the page-wide sweeps found the same unfiltered `page.locator('main a, main button')` loop in four
specs, not two, plus a fifth (`ura-panel.spec.ts`) that had already hand-rolled the skip and a guard of its own.
Which pages can even hold a disclosure was answered from the markup, not from the specs: `<details>` appears in
`Cockpit.vue` (three), `TrainingDetail.vue` (two), `CareerTimeline.vue`, `LegacySelect.vue`, `AbsenceValue.vue`
and `AppLayout.vue` (the mobile bar). `AbsenceValue.vue`'s disclosure holds text only, and the layout's sits
outside `main`, so the affected pages are the four Cockpits and the guided training screen — and the latter's
sweep already opens its disclosure before measuring (`career-training-detail.spec.ts:326`), which is the repo's
precedent for the remedy this slice did *not* take page-wide.

| Gate | Result |
| --- | --- |
| Browser, six specs | 53 passed (25.9m), exit 0, on `:8262` with its own scratch database. Previously failing cases now green: `scenario-panel.spec.ts:162`, `grand-concert-panel.spec.ts:107`, `trackblazer-panel.spec.ts:226`, `unity-cup-panel.spec.ts:189`. Converted and unregressed: `ura-panel.spec.ts:200`. New positive case: `career-cockpit-forms.spec.ts:217`. |
| Ad-hoc `tsc --noEmit` over the ten changed files | exit 0. Required because `npm run typecheck` cannot see them (R2-21). |
| `npm run typecheck` | exit 0, and **not evidence for this slice**: `tsconfig.json:15` includes `resources/js` only. Reported so the gate list is complete, not to claim it verified anything here. |
| Pint, PHPStan | Not applicable: no PHP file changed, and `phpstan.neon` analyses `app` only. Not run rather than run-and-quoted. |
| `composer lore` / `lore-code` | exit 0 / exit 0, 313 and 118 hits, both at their standing counts, zero hits in any changed file. |
| Focused Pest | `ScenarioMetadataTest` 3 passed (22 assertions), exit 0 — run at pre-flight for the gate the brief sets, not because this slice touches feature code. |

No `npm run build` was needed: the change is under `tests/`, and no Vue file moved. Nothing under `app/`,
`resources/`, `config/` or any FD-2 path was read or written except the two greps that established which pages
carry a `<details>`.

### Slice 15 log, same sitting: R2-21 closed in `b558a97`

Pre-flight for this sitting: HEAD `13e2621`; `ScenarioMetadataTest` 3 passed (22 assertions), exit 0; the FD-2 set
still fully dirty, so Phase 1 (slices 7 to 10) stayed closed and only Phase 2 or Phase 3 were candidates. Phase 3
is conditional on a disclosure landing on a signposted page, so nothing was openable unprompted; Phase 2 was put
to the owner as an A/B choice with the blast radius measured, and the owner chose Option A (widen the root
config).

**One correction to the number the choice was made on.** The first measurement compiled `tests/browser/*.spec.ts`
and `tests/utils/*.ts` and reported 21 errors. Option A's include is `tests/browser/**/*.ts`, which also covers
`tests/browser/fixtures/*.ts` — two modules that import `.vue` files directly. The true cost was **25 errors across
8 files**. The choice was unaffected (both options compile the same scope), but the work list was two items
longer, and the number is recorded here as measured rather than as first reported.

| Gate | Result |
| --- | --- |
| `npm run typecheck` (the widened gate) | **exit 0, zero errors.** Against 25 before. This is the gate the slice exists to make honest, and it now reads `resources/js`, `tests/browser/**` and `tests/utils/**` in one pass. |
| `@types/node` install | exit 0. Owner-authorised; `package.json` and `package-lock.json` moved with it, per AGENTS.md §11's "lockfile updates ship with the dependency change". |
| `npm audit --omit=dev` | exit 1, 1 high in `source-map-js`. **Pre-existing**: HEAD's lockfile carries the same six mentions (`git show HEAD:package-lock.json | grep -c source-map-js` → 6), pulled by Tailwind, Vite's postcss and Vue's compilers. Filed as KI-94; not fixed, because `npm audit fix` moves a transitive dependency and that is a decision the `@types/node` approval did not cover. |
| `composer lore` / `lore-code` | exit 0 / exit 0, 313 and 118. The additive gate's first reading of this change was **119**: the new shim's docblock said the fixtures "mount" components, and `mount` is on the banned family list. Reworded to `render`, which is also the more accurate verb here, so the count returned to its baseline instead of carrying a hit that needed a ruling. |
| Pint, PHPStan | Not applicable: no PHP changed. |
| Browser | Not run, and not needed: two declaration files and one type predicate whose runtime result is identical to the arrow it replaced. Recorded as not run rather than implied. |
| Focused Pest | Not applicable: no PHP changed. |

Nothing outside the slice's six files was touched. `tsconfig.json`, `package.json`, `package-lock.json`, the two new
declaration files and `screen-speed.spec.ts` are the whole diff, and the commit's name-only list was checked against
that.

### Next action, in order

1. Slices 7 to 10 wait on the peer's commit of `Cockpit.vue`, `CockpitController.php`, `types.ts`, `AdvisorRail.vue`
   and their four test files. R2-16 is smaller than this register said (band fields committed; only the form and
   its read-back are missing) and R2-17 is one anchor in the same file.
2. R2-19 was openable by anyone and is now closed as slice 13, `24f7e48`. What it leaves is one
   `SCREEN_SPEC.md:1287` row that still calls the Settings row `Global Ruleset`; the file is a concurrent
   session's working property, so the row waits with the two named in item 4.
3. KI-92 is closed (`dc8ad7b`, slice 14): the rule lives in `tests/utils/tap-targets.ts` and the sweeps that
   shared the hole call it. The decision the entry asked for was made in favour of skipping unplaced controls,
   with the opened-disclosure coverage kept as one positive case rather than in every sweep; the three latent
   sweeps carry a signpost comment and nothing else.
4. R2-21 / KI-93 is closed (`b558a97`, slice 15): `npm run typecheck` now reads `tests/browser/**` and
   `tests/utils/**` too, so browser-spec commits no longer need the ad-hoc `tsc` that slice 14 used. The two open
   items it leaves are recorded in KI-93's "what closure does not cover" (the loose `.vue` shim, and TypeScript
   outside the three included roots) and in **KI-94**, the pre-existing `source-map-js` high that
   `npm audit --omit=dev` reports and that would need an approved `npm audit fix` to clear.
5. Whoever lands FD-2 and FD-4 should run `migrate:status` on the dev file and take the three held
   `SCREEN_SPEC.md` rows named above; each is one line and each is already written down here.

## Pre-flight, 2026-10-10 (third sitting): no slice openable

Run as the brief orders, before any slice opened: `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` ->
**3 passed (22 assertions), exit 0**, so the suite is runnable and the gate passes. HEAD is `302ef30`, unchanged
since slice 15 landed. FD-2 is still fully dirty (`CockpitController.php`, `Cockpit.vue`, `types.ts`,
`AdvisorRail.vue`, `CareerCockpitTest.php`, `ScenarioAdvisorFinaleTest.php`, `FinaleVocabularyTest.php`,
`career-cockpit.spec.ts`, plus the untracked `NextDecision.{php,vue}`, `ObjectiveRow.vue`, `RecordedActionCard.vue`,
`CockpitObjectivesTest.php`, `NextDecisionTest.php`) and FD-4 grew beside it (`TrainingRunController.php`,
`StoreTurnEntryRequest.php`, `TurnEntry.php`, `TimelineController.php`, `app/Enums/TurnChoice.php`, the untracked
turn-entries migration, `TurnChoiceTest.php`). Slices 7 to 10 need `Cockpit.vue` and `CockpitController.php` (FD-2)
and `TrainingRunController.php` and `StoreTurnEntryRequest.php` (FD-4), so all four stay closed.

**Phase 2's trigger was re-tested rather than assumed, and it is unmet.** The condition is a `<details>` landing on
`/database/*`, `/career/setup/target` or `/career/setup/trainee`. A sweep of every `.vue` under `resources/js` for
`<details>` returns six files — `AppLayout.vue`, `Cockpit.vue`, `TrainingDetail.vue`, `LegacySelect.vue`,
`CareerTimeline.vue`, `AbsenceValue.vue` — and none of the three routes' page components
(`Database/{Events,Index,Races,Scenarios,ShopItems,Skills,Sparks,Supports,Trainees}.vue`, `Career/TraineeSelect.vue`,
`Career/TraineeProfile.vue`, `Career/BuildTarget.vue`) is among them. Two shared components could still carry one
in: `AppLayout.vue`'s mobile-bar `<details>` sits at line 334, after `</main>` closes at line 300, so the
`main a, main button` sweeps cannot reach it; and `AbsenceValue.vue`, which those pages may render, has a
`<summary>` as its only interactive element, which is neither an `<a>` nor a `<button>`. A shared disclosure
component therefore does not by itself trip the latent sweeps: the trigger is specifically a control *inside* a
collapsed `<details>` on one of the three pages, and no such control exists today.

**FD-4's request diff is one line, so R2-16's premise survives it.**
`git diff app/Http/Requests/StoreTurnEntryRequest.php` moves `choice` from `['nullable','string','max:60']` to
`Rule::enum(TurnChoice::class)`; the `energy_state` / `energy_band` rules at :128-132 are untouched. The band slice
is therefore still "the Cockpit's form and its read-back", not a schema change — but the correction *mechanism*
those files are rebuilding (append a correction row, `corrected_from` / `superseded_by`) is the very form R2-16
would add a control to, which is a reason to wait rather than a reason to re-plan.

**Next action.** Unchanged from the ordered list above: wait for FD-2 and FD-4 to commit, then slices 7, 8, 9, 10
in that order. Nothing was opened, nothing was edited outside this register, and no gate beyond the pre-flight ran.

## Pre-flight and the Slice G verdict, 2026-10-10 (fourth sitting)

Pre-flight, in the order the manual sets. Step 0.1: FD-2 is still dirty (`CockpitController.php`, `Cockpit.vue`,
`types.ts`, `AdvisorRail.vue` modified; `NextDecision.{php,vue}` and `NextDecisionTest.php` untracked), so Phase 1
(slices A-E) stays closed, and the gate's own list — "only slices F and H are openable" — leaves F blocked by its
stated dependency (FD-2 committed *and* slice E landed) and H's trigger unmet. Step 0.2:
`vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` -> 3 passed (22 assertions), exit 0. HEAD `302ef30`,
unchanged since slice 15.

**Slice G was opened because Phase 2 marks it "openable any time", and it resolves to no work.** Verified site by
site rather than assumed:

- `Runs/Import.vue:106` (group A') is **already fixed**: the fallback reads `Trainee id ${id} does not resolve`
  with a `title` naming the broken reference (`:112-113`), which is the sentence the manual asks for.
- Four of the seven group C paths **do not exist**: `components/DeckPanel.vue`, `components/GradePointMeter.vue`
  and `pages/Runs/Show.vue` are absent from the tree (the last deleted by `F2`), and `scenario/TeamRacePanel.vue`
  is really `components/TeamRacePanel.vue`.
- `pages/SupportCards/Show.vue:149` already carries its reason as a `title` (`:147`) beside the sentence.
- `pages/Career/TraineeSelect.vue:119` is `errorLabel()`'s default, a non-display value the proposal doc's own
  "not in scope" row tells us to leave.
- The two survivors — `career/RecommendationCard.vue:71` (`props.action ?? 'No action to rank'`) and
  `components/TeamRacePanel.vue:42` (`entry.title ?? 'a team race with no calendar row'`) — print the reason as
  visible prose, which `DESIGN.md:571-574` says **needs no component** ("A caller that already prints the reason as
  visible prose beside the marker keeps doing that and needs no component"), and
  `docs/proposals/provenance-and-absence-contracts.md:389-396` already ruled that class conformant as O1. The
  manual's instruction to substitute `N/A` + `title` therefore contradicts `DESIGN.md` and would move a reachable
  sentence into a hover-only attribute, which is the information loss the proposal doc warned against. Not
  converted: the wording call stays the owner's.

A bounded sweep of every `?? '...'` fallback under `resources/js/pages` and `resources/js/components` found no
un-named absence. The bare-`N/A` sites in `ScenarioSelect.vue` (`:185`, `:190`, `:194`, `:203`, `:209`) and
`Preflight.vue` (`statLabel` `:363`, `raceLabel` `:371`/`:375`/`:379`) each carry a `title`, and the prose
fallbacks name their absence. Nothing to convert.

**Next action.** Unchanged: FD-2 and FD-4 must commit before any Cockpit slice opens. Slice G is closed as a
verified no-op (not as work done), and should not be re-issued against the same path list.

## FD-2 + FD-4 landed together as `b9e53cb`

**FD-2 + FD-4 landed together as `b9e53cb`.** Round 2 Slices A, B, C, D and E are openable; F opens once E
lands, because F's fixture waits submit through Cockpit buttons that E creates. The combined commit was
necessary because `NextDecision.php:7,163` references `TurnChoice` and `TrainingRunController.php:36,822`
references `NextDecision` — the dependency is circular, so **no two-commit split is checkout-coherent**: FD-2
alone fatals on a missing enum and FD-4 alone fatals on a missing service.

This is not a Round 2 slice. It is a peer commit, landed by a committer session on the owner's explicit
instruction, that removes Round 2's blocker. Recorded here so the next session knows why `Cockpit.vue` and
`CockpitController.php` are no longer dirty.

**Two file sets the dispatch brief omitted, both required for coherence** (the owner authorised both
expansions before the commit): `config/scenarios.php`, because `CockpitController.php:523` reads
`config('scenarios.objective_priority')` and `CockpitObjectivesTest:283/:286` pins it; and
`PerformanceReadPathTest.php` plus `PerformanceTurnObservationTest.php`, which add
`'choice' => 'training-Speed'` because FD-4 makes `choice` required on the staged path. Left out on purpose:
`CareerLegacyDeckStepsTest.php` (Round 2's own held R2-02 test, still uncommitted) and `crush.json` (agent
config, never committed).

**Verification that landed it.** `CareerCockpitTest`, `ScenarioAdvisorFinaleTest`, `FinaleVocabularyTest`,
`NextDecisionTest`, `CockpitObjectivesTest` 72 passed (952 assertions); `TurnChoiceTest` 15 passed (118);
`CareerLegacyDeckStepsTest` + the two `Performance*` files 36 passed (366). `npm run typecheck` exit 0,
PHPStan `[OK] No errors`, `composer lore` 313 / `composer lore-code` 118 both exit 0 at standing counts,
`git diff --check` exit 0. The brief's Step 4 was **skipped as false**: the two tests it named as failing are
already at the eight-key contract and green, so editing them would have broken working tests.

**One gate red, not fixed by the committer.** `pint --test` on the committed PHP paths returns
`{"tool":"pint","result":"fail","files":[{"path":"tests/Feature/FinaleVocabularyTest.php","fixers":
["method_chaining_indentation","statement_indentation"]}]}`. Whitespace only, pre-existing (the Slice 3 log
already recorded it), and reformatting a peer's file was outside the committer's authorisation. **Owed by the
FD author**: run `vendor/bin/pint tests/Feature/FinaleVocabularyTest.php`.

**Clean-checkout probe.** A detached worktree at `b9e53cb` applied all migrations including
`2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` (exit 0) and `php artisan about` booted
(exit 0). The Pest run in that worktree first failed with `Call to undefined method Container::basePath()`,
which was this session's own probe wiring (a junctioned `vendor` plus copied `bootstrap/cache`, against
`optimize-autoloader: true`) and not the commit; the result is being re-established on a real install.

## Slice A log, 2026-10-11: R2-16 closed in `c3583f0` + `d21fcc1`

Pre-flight ran in the manual's order. Step 0.1: HEAD `b9e53cb`. Step 0.2: the only source file dirty was
`tests/Feature/CareerLegacyDeckStepsTest.php` (Round 2's held R2-02 test); every other entry was documentation.
Step 0.3: `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` -> 3 passed (22 assertions), exit 0.
Step 0.4, reading the FD diffs rather than the message: `StoreTurnEntryRequest` moves only `choice`
(`Rule::enum(TurnChoice::class)`), and `energy_state`/`energy_band` are untouched at `:129-134`; `TurnEntry`
keeps both in `#[Fillable]`; `updateTurn()` at `:930` writes `$request->validated()` whole; `turnAttributes()`
at `:651` is the **create** path's allow-list and carried neither key.

**The Cockpit half was already built when the slice opened.** `CockpitController::correctionSection()` sends
`correction.values.energy_state`/`.energy_band` (`:1471-1472`, citing R2-16), and `Cockpit.vue` already held the
refs, the clearing watch, the re-sync and the whole Energy fieldset in the correction form. All of it was
uncommitted working-tree content that the peer landed as `c3583f0` while this slice was being verified, so the
manual's Step 2 ("add the control to `Cockpit.vue`") needed no edit here.

**What was actually missing: `turnAttributes()` dropped the band on create.** The guided form
(`Career/TrainingDetail.vue:421-456`) posts `energy_state`/`energy_band` to `runs.turns.store` ->
`storeTurn()` -> `turnAttributes($validated)`, whose allow-list omitted both, so a band a Trainer picked on the
guided screen never reached the row while the same band saved correctly through a correction. `EnergyStateTest`
could not see it: it builds its rows with the factory, which bypasses the write path entirely.

| Gate | Result |
| --- | --- |
| RED first | `TurnEntryUpdateTest`'s new case -> `Failed asserting that null is identical to an object of class "App\Enums\EnergyState"`, 1 failed / 4 passed. |
| Focused green | `vendor/bin/pest tests/Feature/TurnEntryUpdateTest.php` 5 passed (26 assertions), exit 0. |
| Related regression | `CareerTrainingDetailTest` + `SnapshotTrainingDecisionTest` 12 passed (297 assertions), exit 0. |
| Browser | `PLAYWRIGHT_PORT=8164 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-8164.sqlite npx playwright test career-cockpit-forms.spec.ts` 12 passed (15.7m), exit 0. `:8163` boot-timed-out first (`Timed out waiting 60000ms from config.webServer`, no setup output), the boot-under-load signature this register already carries. |
| `npm run typecheck` | exit 0. Not evidence for this slice: no TypeScript or Vue file changed in it. |
| Pint | `{"tool":"pint","result":"passed"}` on `TrainingRunController.php` and the test file, explicit paths only (KI-75). |
| PHPStan | `[OK] No errors` on the changed controller. |
| `composer lore` / `lore-code` | exit 0 / exit 0, 313 and 118 hits, both at standing counts, no hit in either changed file. |

**Two collisions with the concurrent session, both benign and both recorded.** (1) A pathspec commit takes
working-tree content, so the peer's `c3583f0` captured this slice's two edits to `TurnEntryUpdateTest.php` (the
retitle of case 1 and the new create-path case) along with their own Cockpit half. (2) That commit therefore
carried a test for the create path whose fix was still unstaged here, so **`c3583f0` was red against its own
tree**; the peer marked the case `->skip(...)` with that reason, and after `d21fcc1` landed the fix they
un-skipped it in `1a997a5`. Both files this slice needed were clean when it started, so nothing was spliced.

**Deviation from the manual, stated as it asks.** Slice A's file list names `Cockpit.vue` and an `updateTurn()`
verify, and its four test cases are update-path. The create-path allow-list was outside that list and was added
because the manual's own first case ("Records a band via the guided form; reads it back") cannot pass without
it, and `TrainingRunController.php` was clean.

## Pre-flight, 2026-10-11 (fifth sitting): the peer's R2-09 is still in flight

A correction arrived asserting the peer's R2-09 was committed and HEAD had moved past `1a997a5`. Measured:
**it had not.** HEAD is `1a997a5` exactly, and the peer's three files are still dirty —
`resources/js/components/career/CareerStatePanel.vue`, `resources/js/pages/Career/Cockpit.vue` and
`resources/js/pages/Career/EventDecision.vue` — with `tests/Feature/StatDenominatorConsistencyTest.php`
untracked beside them. The manual's Step 0.4 therefore resolves to "matches present": the peer is still
editing, so slices B, C, D and E are closed, and F with them. Nothing was opened.

Steps 0.1-0.3 passed: HEAD `1a997a5` with the R2-16 landing verified as three small commits (`c3583f0` the
Cockpit half, `d21fcc1` the create path, `1a997a5` the un-skip), and
`vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` 3 passed (22 assertions), exit 0.

**How far the in-flight R2-09 has got, read only and not touched.** `CareerStatePanel.vue` already declares
`denominatorLabel: 'target' | 'cap'` as a required prop (`:59`), derives the second number from it (`:110`),
and renders the honest null as `no {{ denominatorLabel }} set` (`:133`) with the label beside the figure
(`:136`); no `1300` is hardcoded in it. **Neither caller passes the prop yet** —
`grep -rn denominatorLabel resources/js/` matches `CareerStatePanel.vue` alone — so the peer's tree is
typecheck-red at this instant and the slice is mid-edit rather than finished. Recorded only so the next
session does not re-trust the "committed" premise; no peer file was edited, staged, moved or reverted.

## Slice C, R2-13 half, 2026-10-11: closed in `8e51b9f`

Pre-flight at HEAD `44a7b13`, in the manual's order. Steps 0.1-0.3 passed: HEAD is `44a7b13` (R2-09 landed as
four files on top of `1a997a5`) and `ScenarioMetadataTest` 3 passed (22 assertions), exit 0. Step 0.4: the
source dirty set is **empty** — the peer committed R2-09, and Round 2's held `CareerLegacyDeckStepsTest.php` is
no longer dirty — so slices B, C, D and E were openable. Slice B was verified against the expected shape rather
than reopened: `CareerStatePanel.vue` declares `denominatorLabel: 'target' | 'cap'` as a required prop (`:59`),
the Cockpit passes `target` (`:489`), EventDecision labels its own strip `cap`, a null target renders
`no target set` (`:133`), no `1300` is hardcoded, and `StatDenominatorConsistencyTest` is the cross-screen case.
Marked landed.

**R2-13's premise was half true.** The *read* half was already built: `CockpitController` sends
`trainee_rarity`, `potential_level`, `growth_rate` and `stat_order`, and `CareerHeader.vue:83` prints
`★3 · Potential 2`. The *write* half was missing: the header section's three forms wrote only status, scenario
and the grade-point period, so the header showed what an import or a direct model write had stored and nothing
on the page could enter a rarity. `StoreTrainingRunRequest` already validated all ten Slice C fields and
`TrainingRunController::update()` persists `validated()` whole, so no PHP changed.

| Gate | Result |
| --- | --- |
| Focused Pest | `CockpitRunIdentityTest` **6 passed (77 assertions)**, exit 0, the three new wire cases included. |
| Related regression | `SnapshotCockpitTest` + `CockpitObjectivesTest` **16 passed (254 assertions)**, exit 0. |
| `npm run typecheck` | exit 0, before and after the spec edit. |
| `npm run build` | exit 0 (1m 13s); the Cockpit chunk rebuilt. |
| Pint | `{"tool":"pint","result":"passed"}` on the changed test file, explicit path. |
| PHPStan | Not applicable: no PHP file changed. |
| `composer lore` / `lore-code` | exit 0 / exit 0, 313 and 118, standing counts, no hit in a changed file. |
| Browser | `career-cockpit-forms.spec.ts` full file: **11 passed, 2 failed (9.1m)** on `:8166`. Both diagnosed and re-run green. The new case's read-back asserted on `section[aria-label="Career"]` (the career bar) while the rarity span lives in `CareerHeader`'s `section[aria-labelledby="career-header-heading"]` — a test locator bug, fixed, re-run **1 passed (20.0s)** on `:8168`. `saves a correction and returns to the cockpit with the new value` timed out at 180s under host load, then **passed on re-run in 33.3s** on `:8167` — flaky, not a regression. All thirteen cases now have green evidence. |

**Deviation, stated.** The manual bundles R2-13 and R2-14 into one commit. They are two separate findings and
only R2-13 is landed here: R2-14 needs a sibling `UnityCupEditor.vue` because `UnityCupPanel.vue` is a 314-line
registry-mounted pure renderer taking `scenario`/`label`/`kind`/`name` and owning no write, so editing it in
place would thread edit state through the panel registry. Splitting the commit half-lands neither finding.

**Two slices resolved to no work, measured.** Slice E (KI-88 half 2) is already done: the three header forms
render `<button type="submit">` (`Change status`, `Change scenario`, `Report period`, each `min-h-11`) at
`Cockpit.vue:392/415/446`, and all three corresponding browser cases pass. Slice F (KI-88 half 1) is already
done: `grep -rn "training-runs/\\d+" tests/browser/` matches **0 files**. Neither premise holds.

## Register closure pass, 2026-10-11

Four rows resolved while the work above landed. Measured, with the commits that did it:

| Item | Status | Evidence |
| --- | --- | --- |
| KI-88 half 2 (the Cockpit's three header edits render no submit control) | **Closed by `71488cb`** (`fix(career): give the cockpit's three header edits a submit control (KI-88)`) | The three forms at `Cockpit.vue:451/473/498` each render `<button type="submit">` (`Change status`, `Change scenario`, `Report period`, all `min-h-11`), and the three matching `career-cockpit-forms.spec.ts` cases pass. |
| KI-88 half 1 (the fixtures wait on the retired run-record URL) | **Closed by `5e1cdf2`** (`test(browser): re-anchor the create-redirect waits to the cockpit (KI-88)`) | The waits now read `page.waitForURL(/\/cockpit$/, ...)`, and `grep -rn "training-runs/\\d+" tests/browser/` matches **0 files**. |
| R2-13 spec locator | **No follow-up owed**: the correction was made *before* `8e51b9f` was committed, not after | `git show 8e51b9f:tests/browser/career-cockpit-forms.spec.ts` carries `section[aria-labelledby="career-header-heading"]` (1 match) and **zero** `aria-label="Career"` occurrences. The case's own comment still said "career bar"; the R2-14 commit corrects that one line. |
| Slice H (latent 44px sweeps) | **Trigger unmet as of 2026-10-11** | `grep -l "<details" resources/js/pages/Database/*.vue resources/js/pages/Career/BuildTarget.vue resources/js/pages/Career/TraineeSelect.vue` matches nothing, so the three signposted specs stay as they are. |

**Not touched, and why.** KI-88's own entries live in `KNOWN-ISSUES.md`, which is dirty on the concurrent
session's behalf (`git status --short` shows ` M KNOWN-ISSUES.md`), so the closures are recorded here rather
than by editing that file.

## Slice C' (R2-14) log, 2026-10-11: closed in `d8fb234`

**R2-14's premise was half true, the same way R2-13's was.** The read half was built: `CockpitController`'s
§49 section carries `team_name`, `team_motto`, `team_league_placement`, `team_preseason_wins` and the three
counters under `scenario.team` (`:1386-1392`), and `UnityCupPanel.vue` prints them. The write half was
missing: nothing on the Cockpit posted any of the seven. `StoreTrainingRunRequest` already validated all
seven and `TrainingRunController::update()` persists `validated()` whole, so no PHP changed.

**A sibling editor, not an extension.** `UnityCupPanel.vue` is a 314-line registry-mounted pure renderer whose
props come from the §49 bundle; it owns no write surface, and threading edit state through the panel registry
for one caller would break the "a panel is a read-only view" contract. `UnityCupEditor.vue` is run-scoped and
rendered by the Cockpit alone, gated on `scenario.panels.team_rank_ladder.on` — the matrix's flag, because a
scenario name in the page is the defect `AGENTS.md` §7 forbids.

| Gate | Result |
| --- | --- |
| Focused Pest | `CockpitTeamPanelTest` + `CockpitUnityCupCountersTest` **10 passed (150 assertions)**, exit 0, the four new write cases included. |
| Related regression | `SnapshotCockpitTest` + `CockpitObjectivesTest` **16 passed (254 assertions)**, exit 0. |
| `npm run typecheck` | exit 0. |
| `npm run build` | exit 0. |
| Pint / PHPStan | Not applicable: no PHP file changed. |
| `composer lore` / `lore-code` | exit 0 / exit 0, 313 and 118, standing counts. |
| Browser | `career-cockpit-forms.spec.ts` **14 passed (8.8m)**, exit 0 on `:8176`, the new team-editor case among them. |

**A real bug caught by the browser half, worth the record.** The first cut of the editor's `transform` called
`.trim()` on the numeric fields. `v-model` on an `<input type="number">` hands back a **number** once one is
typed, so the transform threw `TypeError: o.trim is not a function` at submit and **the request never left**:
no PUT, no flash, and no error alert (the Cockpit renders no page-level alert for `props.errors`, so a refused
write is silent). Four browser runs plus a `waitForResponse` and a component `console.log` probe located it;
the fix is an identity blank test (`value === ''`) rather than a `.trim()`. The feature tests could not have
caught it: they POST the wire directly and never run the transform.

## Slice D (R2-17) log, 2026-10-11: closed in `5d26b49`

`/training-runs/{run}/deck` answers and writes this run's six slots, and the Cockpit linked it nowhere. The
Training screen's door landed in `e2889ff` because that file was clean; the Cockpit is where a Trainer
mid-career actually stands, so the screen stayed URL-only for them. `deck_url` joins the run's other 2.0 URLs
in `CockpitController` (`:213`, beside `training_url`), the page's `run` prop gains it, and the header section
carries one plain anchor beside the exports: a deck read is a GET, so it is a link and not a form.

| Gate | Result |
| --- | --- |
| Focused Pest | `CockpitRunIdentityTest` **7 passed (86 assertions)**, exit 0, the new prop case included. |
| Pint / PHPStan | `{"tool":"pint","result":"passed"}` on both changed PHP paths; PHPStan `[OK] No errors` on the controller. |
| `npm run typecheck` / `npm run build` | exit 0 / exit 0. |
| `composer lore` / `lore-code` | exit 0 / exit 0, 313 and 118, standing counts. |
| Browser | `career-cockpit-forms.spec.ts` **15 passed (8.2m)**, exit 0 on `:8177`. The new case asserts the anchor is visible and its href ends `/training-runs/<id>/deck`; the header 44px sweep and the axe scan still pass with the new link in the section. |

With this the Round 2 slices are all landed: R2-13 (`8e51b9f`), R2-14 (`d8fb234`), R2-17 (`5d26b49`), R2-16
(`c3583f0` + `d21fcc1` + `1a997a5`), R2-09 (`44a7b13`).

## Phase 3 gate sweep, 2026-10-11

Run against `5d26b49`, the tip after Slice D. The manual's Steps 3.1 and 3.2 are UI walkthroughs and were
**not** run as walks; every behaviour they name is covered by a spec that did run, and the mapping is listed
so the gap is explicit rather than implied.

| Gate | Command | Result |
| --- | --- | --- |
| TypeScript | `npm run typecheck` | exit 0 |
| Build | `npm run build` | exit 0 |
| Pint | `pint --test --format agent` on the six PHP files this session changed | `{"tool":"pint","result":"passed"}` |
| PHPStan | `phpstan analyse --no-progress --memory-limit=1G` on `TrainingRunController` and `CockpitController` | `[OK] No errors` |
| Lore | `composer lore` / `composer lore-code` | 313 / 118, both exit 0, both at standing counts |
| Focused Pest | the four slice test files | **22 passed (262 assertions)**, exit 0 |
| Browser | `career-cockpit-forms.spec.ts` | **15 passed (8.2m)**, exit 0 on `:8177` |
| `migrate:status` | on `database/database.sqlite` | exit 0, **not clean**: `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` is `Pending` — FD-4's migration, on the shared dev file, and running it is the owner's call rather than this session's. |

**Step 3.1 (the Rice Shower anchor) and Step 3.2 (a new career), mapped rather than walked.** Each behaviour
the anchor names has a green spec: position on the Cockpit/Timeline/race surfaces (R2-01/R2-04's own specs,
landed before this session); the stat strip's denominator label (`StatDenominatorConsistencyTest`, R2-09);
rarity, potential and growth editable (the identity editor case, R2-13); team identity and the Unity Cup
counters editable (the team editor case, R2-14); the deck door (the deck-link case, R2-17); the energy band
editable and read back (`TurnEntryUpdateTest`, R2-16); and all three header forms submitting (three cases in
the same spec, KI-88 half 2). What a walk would add that no spec does is the *single* run carrying all of them
at once. Not run.

**Two browser files not run, and why.** `career-cockpit.spec.ts` is dirty on the concurrent session's behalf
(`git status --short` shows ` M tests/browser/career-cockpit.spec.ts`), so running it would exercise a peer's
in-flight edits and attribute their result to this work. `career-training-detail.spec.ts` was skipped for the
same reason: its subject is untouched by these slices, and each slice's own spec covers the path it changed.

## Round 2 close-out, 2026-10-11 (verify-only pass)

Pre-flight at `5d26b49`: HEAD as expected, the only dirty source file the peer's held
`CareerLegacyDeckStepsTest.php`, `ScenarioMetadataTest` 3 passed (22 assertions), exit 0. The forms-spec
result already recorded at this commit (15 passed on `:8177`) was reused rather than re-run, because no
source file changed after it.

**Phases 1 and 2 were produced as covering-spec runs, not a manual walk** — the owner chose that shape. Each
run used its own scratch database.

| Run | Result |
| --- | --- |
| `career-cockpit.spec.ts` (Phase 3, file clean) | **9 passed (7.8m)**, exit 0 on `:8181` |
| `career-timeline` + `support-deck` + `career-race-strip` + `career-race-decision` + `career-event-decision` + `career-skills-planner` + `career-inheritance-event` + `career-result`, one shared boot | **48 passed, 1 failed (19.5m)** on `:8182` |

**Checklist mapping.** Step 1.2's five Cockpit checks and Steps 1.4/1.5 are proven by the 15 passing
`career-cockpit-forms.spec.ts` cases (identity editor ★3 / Potential 2 / growth, team editor Blue Bloom,
Dreaming Big, 8, 4, 55, 6, 5, the three header submits, the honest `no target set` null, the deck link).
Step 1.3's band read-back is `TurnEntryUpdateTest`'s five cases plus `career-cockpit.spec.ts`'s "records
Energy through the cockpit correction and then marks one action". Steps 1.6/1.7 and Phase 2.2 are the eight
covering specs above. **Not proven by any run: the canonical literal values** — Rice Shower at
607/577/549/736/397 with the named six-card deck and Vodka/Maruzensky as parents. Every one of those rows
exists in the seeded catalog (verified by query: 1 trainee, 1 `[Rosy Dreams]` card, all six support cards by
character and title, Vodka, Maruzensky), but no spec enters them together, so cross-screen contradiction at
those exact figures stays unverified.

**The walk found one real thing, and it was the spec, not the screen** (`7efed5c`). `support-deck.spec.ts:76`
asserted `Owned` pressed on a fresh deck, while `SupportSlot.vue:166-172` deliberately renders an unflagged
slot as *neither* button pressed plus an `N/A` marker naming the absence — the three-state rule `dd179ba`
landed. Deterministic, not a load flake: 31 retries over 15s returned the same value. The assertion now pins
all three facts of the unrecorded state and the component is untouched; the case passes in 24.6s.

**Phase 4 closed** — `c68f480`, Pint on `tests/Feature/FinaleVocabularyTest.php`. Whitespace-only, proven by
`git diff -w` against the pre-fix file returning nothing; the suite is unchanged at 5 passed (54 assertions).

**Phase 5 blocked by the environment, not by the owner.** The owner authorised `php artisan migrate` on the
shared dev file; the session's safety layer refused the call, reading the shared-database write as lacking
confirmation. Not retried, not worked around. **`2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table`
is therefore still `Pending` on `database/database.sqlite`**, so any route reading `choice`,
`decision_snapshot`, `corrected_from` or `superseded_by` from the dev file keeps failing until the owner runs
`php artisan migrate` directly. Two `php artisan serve` processes are bound — `127.0.0.1:8000` (PID 25560)
and `127.0.0.1:8142` (PID 26284), the KI-73 leaked-server shape — and were left running because either may
belong to another session.

**Phase 6 — trigger unmet as of 2026-10-11.**
`git grep -n "<details>" -- resources/js/pages/Database/ resources/js/pages/Career/BuildTarget.vue
resources/js/pages/Career/TraineeSelect.vue` matches nothing, so the three signposted sweeps stay converted
in place and Slice H stays shut.

---

## Phase 1 canonical walk, first sitting (2026-10-11, from `c4deafa`)

**Pre-flight.** HEAD `c4deafa` (a peer's G3 URA finale surfaces, landed on top of this session's `7efed5c`).
The dirty source set at Step 0.1 was not the expected single file: `tests/Feature/UnityCupSurfacesTest.php`
appeared untracked, and its own header names it as a peer's in-flight **G4** slice (Unity Cup decision
surfaces, owner rulings of 2026-10-11). No `app/`, `resources/`, `config/` or `routes/` file was dirty, so
the walk was not blocked by peer source work; the G4 file was left untouched. `npm run typecheck` and
`npm run build` exit 0; `ScenarioMetadataTest` 3 passed / 22 assertions.

**Step 0.3 corrected.** The manual's two tinker commands were half right: `umamusume` does carry a `slug`
column, so `Umamusume::where('slug','rice-shower')` resolves (id 30, one row), but `SupportCard` has no
`title` column. The card titles the manual lists live in `title_en` and the six names it lists are
`char_name`: Tokai Teio `[Dream Big!]`, Nishino Flower `[Even the Littlest Bud]`, Biko Pegasus
`[Double Carrot Punch!]`, Matikanetannhauser `[Just Keep Going]`, Haru Urara `[Urara's Day Off!]`, Super
Creek `[Piece of Mind]`. All six exist on the dev file. `[Rosy Dreams]` is not an `umamusume` column at
all: it is a `character_cards.title`, and the dev file holds exactly one (`dev_character_cards=106`).

**Two databases, each short of one half of the walk.** The harness scratch DB is built by
`tests/browser/global-setup.ts` with `migrate --seed`, so it *has* the `turn_entries.choice` column but
*has no* Veterans (the seeder order in `DatabaseSeeder` never calls `TrackerVeteransSeeder`, and
`career-legacy-deck-steps.spec.ts` asserts that emptiness as the fresh-install state). The dev file *has*
the 7 Veterans (Vodka x2, Maruzensky x2, Daiwa Scarlet, Haru Urara, Mayano Top Gun) but is one migration
behind: `Schema::hasColumn('turn_entries','choice')` is **no**, `migrate:status` shows
`2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` Pending. Step 1.1's parent picks need
the first; Step 1.3's turn write needs the second. No database on this tree carries both until Phase 4 runs.

**Steps executed on the dev file through the browser (session `:8000`, the peer-bound server the
safety layer directed this walk onto).** Scenario pick stored (`Stored choice: Unity Cup`), trainee pick
stored (`Stored choice: Rice Shower`, profile link `/career/setup/trainee/30` confirming the same row the
catalogue query returned), target step visited and deliberately left empty, ancestry step driven: Parent A
`Vodka [Wild Top Gear] · 7`, Parent B `Maruzensky [Formula R] · 2`, Parent B `Own rank (letters)` = `UG`,
`Rented for the run` checked on Parent B.

**W1, the defect the walk found (`8a7b2e1`).** Pressing `Save Legacy` appeared to do nothing. The XHR probe
captured the answer: `props.errors["legacies.1.rank_letter"] = "The selected legacies.1.rank_letter is
invalid."` Two causes, at two layers. `LegacySelectionPayload::RANK_LETTERS` stopped at `S+` and does not
include `UG`, the letter this career's own source prints for Legacy 2 and both of her grandparents
(`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:153-155`); and `LegacySelect.vue` rendered errors for only
three keys (the parent pick, the grandparent names, the affinity), so a refusal on any other field printed
nothing and the *entire* Legacy write was discarded for one letter. Fixing the list is the root fix because
that one constant is the single owner of the Form Request rule (`StoreLegacySelectionRequest.php:103`), the
payload's own assertion (`:272`) and the `known-rank-letters` datalist
(`LegacySelectController.php:119`), so screen vocabulary and write vocabulary cannot drift apart again. The
refusal now renders beside the field that caused it and names itself through `aria-describedby`, the way the
grandparent errors already do. Only `UG` was added: `SS`, `U` and `UU` are named as absent in the audit
record but no source in this tree attests that the client prints them, and inventing letters would breach
`AGENTS.md` §5. `AncestorRankTest` pins the boundary (red first, with exactly the message the walk read);
`career-legacy-deck-steps.spec.ts` pins the rendered refusal (red first: element(s) not found).

**W2, a pre-existing red found in the same file.** The ancestry case at
`career-legacy-deck-steps.spec.ts:106` calls `.fill('Speed')` on `getByLabel('Applies to')`, which since
`6fa0d01` (2026-10-09, "typed spark targets with category-conditional validation") is a `<select>`, and the
value it names belongs to the separate `Target` select. Playwright refuses: `Element is not an <input>`.
The file has therefore been red for two days with nobody re-running it. Classified pre-existing, not caused
by W1 (W1's diff touches only the rank-letter block and the `errorFor` signature).

**W2 status: attempted, not landed, reverted.** Replacing `.fill('Speed')` with `selectOption` on both the
category and the target moved the failure onward rather than clearing it: `getByLabel('Stars')` then resolved
to three elements, and scoping from `getByLabel('Applies to').locator('xpath=ancestor::div[1]')` still left
two. So the case needs a row-level locator decided against the live DOM, which this sitting no longer has.
The three edits were reverted (`git diff` on the spec file is empty against `8a7b2e1`, and W1's added case is
committed and untouched), because a half-repair would leave the file red at a different line while looking
attended to. Open, with the exact locator problem recorded here; a `KI-nn` entry is owed in
`KNOWN-ISSUES.md` for the two-day window, and was not written there because that file carries a peer's
uncommitted edits, which a pathspec commit would capture (`c3583f0` precedent).

**Dated correction to W2, 2026-10-11 (sixth sitting, landed as `29f5fc1`, closes KI-96).** The diagnosis above
was half wrong, and the correction is a real one rather than a nit. **Both** commits broke the case, eighteen
minutes apart on 2026-10-09, and `git merge-base --is-ancestor 5876f64 6fa0d01` proves the order: `5876f64` at
17:54 ("ancestor letter rank alongside star count") made the node print `Rank not recorded, 4★`, so
`getByText('Rank 4')` went stale at `:130`; then `6fa0d01` at 18:12 made `Applies to` a `<select>`, and because
that break happens *earlier in the same test*, it masked the first one for two days. W2 therefore cost 9
insertions and 3 deletions across two locators, not the one line this register and the Phase plan both assumed,
and a repair that fixed only `:117` would have left the file red at `:134` looking attended to, which is the
exact failure mode the sitting above was written up for avoiding.

**General finding, reusable for every browser spec in this repo: "needs a live browser" was not true.**
Playwright writes `test-results/<test-title-slug>/error-context.md` on every failure, and that file carries the
**accessibility tree of the live DOM at the moment of failure** plus the call log and the numbered source lines.
One command settles a locator-uniqueness question with no server, no seeded run and no browser session:
`grep -oE '(combobox|spinbutton|textbox|checkbox|button) "[^"]*"' test-results/*/error-context.md | sort | uniq -c`.
Run against the sitting above's artifact it returned `Applies to` 1, `Target` 1, `Stars` 1 and
`Own rank (stars)` 2, which both counted the three elements and explained *why*: `getByLabel` matches a
case-insensitive **substring**, so `Stars` is also inside each parent's `Own rank (stars)`, so the remedy is
`{ exact: true }` rather than a row-level xpath. The same artifact names the real rendered string when an
assertion, not a locator, is what went stale. Caveat for the next reader: `test-results/` is gitignored
(`.gitignore:76`) and the file is overwritten by the next failure of the same test title, so capture what you
need into the register or the commit. Full write-up: `KI-96`.

**Walk status: not clean, and not recorded as clean.** Steps 1.1 (deck sub-row, Preflight, Start Career),
1.2, 1.3, 1.4 and 1.5 were **not executed.** Mid-walk the `browser-use` bridge wedged: every call
(`evaluate_script`, `take_snapshot`, `handle_dialog`, `take_screenshot`) returns
`Browser operation timed out after 15000ms` on every URL including `/`, while the tab stays listed and
`curl` to the same server answers `200`. The Playwright MCP browser hydrates no Inertia page in this
environment (`#app > *` never becomes visible), and its `browser_run_code_unsafe` path is blocked by the
session's safety layer. So the remaining evidence is not available this sitting and is recorded as missing
rather than inferred from the wire-level tests that already exist for those surfaces.

**What the walk did establish.** It found a real, user-visible defect on its second step, which no
feature-level or covering-spec run in Round 2 had caught: the canonical data this whole remediation is built
on could not be entered through the wizard, silently. That is the interaction-level result the manual asked
for, and it is why the walk is worth repeating once the bridge and Phase 4 are clear.

## W2 landed, 2026-10-11 (sixth sitting, Phase A)

**See the dated correction under the W2 entry itself, above.** It carries the two-commit attribution
(`5876f64` 17:54, then `6fa0d01` 18:12, the later break masking the earlier one), the 9 insertions / 3
deletions fact against the plan's "one line", and the reusable `error-context.md` finding. This section records
only what the landing added, so no number is stated twice in this register.

**The repair.** `tests/browser/career-legacy-deck-steps.spec.ts`, 9 insertions and 3 deletions, test-only. The
Spark is now entered as the screen offers it, category `Stat` then target `Speed`, and the rank read-back
asserts the string the node prints, `Rank not recorded, 4★`, which proves the star count round-tripped and the
unentered letter renders as a named absence in the same breath. No application file changed: `AncestorRankTest`
pins `rank_label` to that wording and `assertTargetMatchesCategory()` accepts `Stat` with `Speed`, so both
writes the case drives were already correct.

**Evidence.** `PLAYWRIGHT_PORT=8243 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/phaseA3.sqlite npx playwright test
tests/browser/career-legacy-deck-steps.spec.ts` -> 10 passed (50.7 s). `npm run typecheck` -> exit 0. The two
specs that share the Spark vocabulary were run to measure rather than infer: `legacy.spec.ts` plus
`career-inheritance-event.spec.ts` -> 16 passed (7.8 m) at port 8244, so the free-text `fill()` those screens
still drive is correct and not a third stale locator. `composer lore` -> exit 0, with zero hits on either the
new KI-96 entry or the changed spec lines (one pre-existing hit at `KNOWN-ISSUES.md:1242` predates this change).

**KI-96 filed and closed by this slice**, in the four-part form, with the divergence between the two Legacy
editors recorded as what closure does not cover.

**Landing constraint, again, and how this one was met.** `KNOWN-ISSUES.md` still carries the peer's uncommitted
KI-95 block, so a pathspec commit of that file would have landed their filing under this commit's message. The
sitting used a private index instead: the committed blob is `git show HEAD:KNOWN-ISSUES.md` plus this entry
alone (2,480 lines, `^### KI-95` count 0, `^### KI-96` count 1), the working file keeps both blocks, and the
worktree therefore stays dirty with KI-95 as the peer left it. `tests/Feature/CareerLegacyDeckStepsTest.php`
and every other peer-held file are untouched and uncommitted (Phase E's ruling is still owed).

**One follow-up a later sitting must know.** A private-index landing moves `master` and leaves the shared index
on the previous commit, so `git status` showed both landed paths as `MM`, meaning a bare `git commit` by any
session would have committed the pre-repair content and reverted KI-96 and the spec fix. The sitting checked
first that the index entries equalled `HEAD~1` exactly (spec `14811ee`, `KNOWN-ISSUES.md` 2,430 lines, so no
peer work was at risk), then aligned the index for its own two paths with `git reset -q -- KNOWN-ISSUES.md
tests/browser/career-legacy-deck-steps.spec.ts`. That is index-only and touches no file: `git diff --cached HEAD`
is empty afterwards, the spec reads clean, and `KNOWN-ISSUES.md` stays dirty by exactly the peer's 34 lines.
Anyone landing the same way should do the same step in the same sitting.

**Phase A is done. The gates this sitting did not run, stated plainly:** no full PHP suite, no `phpstan`
(nothing under `app/` changed), no `pint` (no PHP file changed), no `migrate:status` and no Phase B migrate
(owner action, refused by the safety layer as expected), and Phases C and D remain unexecuted because the
`browser-use` bridge wedge recorded above was not re-tested this sitting. The Playwright harness worked, which
is the narrower fact; the MCP bridge is a different instrument and its status is unchanged.

## Phase D fresh-career walk, 2026-10-11 (sixth sitting, owner-approved scratch copy)

**Recorded as Phase D, not the anchor walk.** The owner's ruling names the distinction: the canonical Rice
Shower anchor is run 9 on `database/database.sqlite`, and a scratch copy cannot reach it. This walk creates and
drives a **new** career. Phase B (`migrate` on the dev file) and Phase E (the held R2-02 test) are untouched and
still the owner's.

**Instrument, and the recorded blocker re-asserted itself halfway.** `browser-use` answered
`navigate_page` and `take_snapshot` on the peer's `:8000` server, which is what cleared Phase D's gate earlier
this sitting. Against the walk it worked for two calls and then hung: `take_snapshot` and `evaluate_script`
returned `Browser operation timed out after 15000ms` on the Inertia pages while `list_pages` still reported the
tab titled "Choose a scenario" and `list_network_requests` showed all five build assets at **200**. So the app
was healthy and the bridge was not: the wedge is intermittent and instrument-specific, not a fixed property of
the environment, and it is not evidence about the application either way. The walk therefore ran on a
deterministic driver instead: Playwright's own `chromium` launched from a script in the gitignored
`.scratch-uma/`, one browser context held across all six steps because `SetupDraft` is session-scoped, and the
cookie carried between runs by `context.storageState()`.

**Harness.** `.scratch-uma/phaseD.sqlite`, built by the harness's own recipe (`writeFileSync` an empty file,
then `DB_DATABASE=… UMA_DATABASE_ROLE=browser UMA_EXPECTED_DATABASE=… php artisan migrate --seed
--no-interaction`, exit 0). `uma:db-guard` -> `targets canonical: no`, `Database identity is safe`. It starts at
**0 runs** and, unlike the dev file, it **does** carry `turn_entries.choice`. Served on `:8251` with the same
three environment variables; `reuseExistingServer` is not in play because no shared server was touched.

**Walk result, step by step, with the wire evidence.**

| Step | What was entered | What the screen returned |
| --- | --- | --- |
| 1 Scenario | `Select Scenario` on the Unity Cup card | `PUT /career/setup/scenario` -> **303**, page re-renders step 1 printing `Stored choice: Unity Cup` |
| 2 Trainee | `/career/setup/trainee?search=Rice`, then `Select Trainee` | server-side filter returns **exactly one** row, `Rice ShowerライスシャワーReleased (Global)`; after the pick, `Stored choice: Rice Shower` |
| 3 Target | visited and **deliberately left unrecorded**, as the Phase 1 manual does | renders `Your target`, `Summary`, `Add`, `Save target` |
| 4 Legacy | typed ancestor `Grass Wonder`, own rank stars `4`, one Spark (`Stat` -> `Speed`, 2 stars), affinity `◎` | `Legacy recorded`, and the Spark reads back as the chip **`Blue Spark, Speed, 2 stars`** |
| 5 Deck | picker index 1 equipped to Slot 1 as owned | `Deck saved`, slot 1 carries `Aoi Kiryuin [Tracen Academy]`, the `Owned` toggle reports `aria-pressed="true"` |
| 6 Preflight | `Start Career` | lands `/training-runs/1/cockpit` |
| Cockpit | read of the new run | header `Rice Shower · Unity Cup · Active`, `TURN 0`, `No turn recorded yet`, and the absences all named with a reason: rarity, year, month, team rank and spirit bursts each render `N/A` with a `title`, stats read `N/A / no target set` |

**Console errors across the whole walk: zero.** No step 500-ed, no hydration failure, and the absence rule held
end to end on a career that has recorded nothing but a scenario, a trainee, an ancestry and one card.

**A defect I nearly filed, and the probe that killed it.** The Cockpit's scenario `<select>` lists `No scenario
set` first, and the run is Unity Cup, which reads as a header-and-control contradiction. It is not one: the live
DOM reports `value: "unity_cup"` and `selectedText: "Unity Cup"`, so the control does preselect the run's own
scenario and `No scenario set` is only the empty-value option. Filed as nothing; the innerText listing that
misled me shows options in order and cannot show which one is selected.

**One real observation for the sweep family.** `/career/setup/preflight` carries **zero**
`button[type="submit"]`: its only action, `Start Career`, is a click-handler button. The same query on the
Cockpit returns five submit controls (`Change status`, `Change scenario`, `Save identity`, `Save team state`,
`Delete this career`). This is recorded rather than filed because nothing in the accessibility contract requires
a submit control, and `career-legacy-deck-steps.spec.ts:259` is the repo's own example of a sweep that keys on
`button[type="submit"]` and would therefore measure nothing on that page: it would pass on an empty target set.
KI-88 is the family this belongs to.

**What this walk did not measure.** Three things, named rather than glossed. The rank read-back is asserted by
the landed browser spec, not by this walk: my probe regex required a one-word letter rank and returned null
against `Rank not recorded, 4★`. The deck's `Scenario Link:` badge wording **with a scenario already chosen**
was not read; the repo spec pins only the no-scenario state (`Scenario Link: N/A`). And the target step was
visited empty by design, so `Save target` and its write path were not exercised here.

**Consequence for Phase C, and it does not unlock it.** This recipe reaches further than the dev file (it has
`turn_entries.choice`), so a turn write would work here. But Phase C's steps 1.1 parent picks need the seven
Veterans that exist only on the dev file, and its anchor is run 9, which also lives only there. No database on
this tree carries both until Phase B runs, so **C is still gated on the owner's `migrate`** and this pass does
not substitute for it.

**Cleaned up.** `phaseD.sqlite*` and the saved session state were removed and the `:8251` listener reaped by PID
(a leaked serve takes every other session's browser run down, KI-73). The four `.mjs` drivers stay in the
gitignored `.scratch-uma/` as the recipe. Nothing was committed from Phase D: it changed no source file, so no
`pint`, `phpstan` or `typecheck` was owed, and the lore gate has nothing new to scan beyond this register, which
is untracked.

## General findings, 2026-10-11 (owner-ruled: register lines, not slices)

**1. `/career/setup/preflight` carries zero `button[type="submit"]`, so a submit-keyed sweep passes vacuously
there.** Measured on the Phase D walk: the page's only action, `Start Career`, is a click-handler button, while
the same query on the Cockpit returns five submit controls (`Change status`, `Change scenario`, `Save identity`,
`Save team state`, `Delete this career`). This is KI-88's family (that entry opened on a missing submit control
in the status form) and KI-92's class (a sweep whose target list does not match what the screen actually offers).
It is a *prospective* hazard rather than a live one, and the set is now measured rather than assumed:
`git grep -l 'type="submit"' 29f5fc1 -- tests/browser` returns exactly two files,
`career-legacy-deck-steps.spec.ts` and `career-timeline.spec.ts`, and neither drives Preflight — the deck sweep
at `career-legacy-deck-steps.spec.ts:251-270` asserts exactly two submits on **step 5**, which is correct for
that page, and the timeline sweep is a run screen. Preflight does have its own spec
(`tests/browser/career-preflight.spec.ts`, eight references at this commit), it simply does not key on submit
controls. So no test is lying today; if a submit sweep is ever pointed at step 6, it must assert that it measured
something, the guard `ura-panel.spec.ts` already carries. (An earlier draft of this line named
`career-training-detail.spec.ts`; that came from reading a line of the count listing as if it belonged to the
file listing, and the re-run above is what settled it.)

**2. Phase C's instrument should be named as the scripted Playwright driver, primary.** Phase D established the
`browser-use` MCP bridge wedges *intermittently*, not structurally: `navigate_page`, `list_pages` and
`list_network_requests` kept answering while `take_snapshot` and `evaluate_script` timed out at 15 s on every
Inertia page, with all five build assets returning 200 and the tab title reading "Choose a scenario". The
plan's earlier "bridge wedged, walk abandoned" note therefore overstates a permanent condition and understates a
recoverable one, and the correct move is to script the walk rather than to record Phase C as blocked.

**3. `innerText` cannot show which option is selected; read `value` and `selectedText` instead.** The Cockpit's
scenario `<select>` lists `No scenario set` first in the page text while the live DOM reports
`value: "unity_cup"` and `selectedText: "Unity Cup"`, and a header that says `Rice Shower · Unity Cup · Active`.
The control is correct; the instrument was not. Same trap family as the `{ exact: true }` fix in KI-96 (label
matching is a case-insensitive substring, which is also why `getByRole('button', { name: 'Filter' })` collided
with `Clear filters` during this walk). A defect nearly got filed on this reading, which is why the probe and not
the inference is the record.

**4. Two Windows-shell traps that cost this sitting time, one line each.** `git archive` plus a **junctioned
`vendor/` does not isolate a scratch app root**: `vendor/composer/autoload_psr4.php:117` maps `App\` to
`$baseDir . '/app'`, and `$baseDir` resolves through the junction to the primary tree, so the extracted app
autoloads the peer's uncommitted `app/` — the exact drift c2 exists to avoid. A real `composer install` in the
extracted root is the only correct route, and the junction must be deleted with `(Get-Item <link>).Delete()`
rather than a recursive remove, because a recursive delete follows the link into the primary `vendor/`. Second:
this session's command hook rewrote `.scratch-uma/phaseC-vacuum.php` to `.scratch-uma/vacuum.php` and mangled
`cmd //c mklink //J` paths, so a file-path argument is run better from inside its own directory and a junction is
made with PowerShell `New-Item -ItemType Junction`.

## Phase E landed, 2026-10-11 (owner ruling e1)

**R2-02 regression test landed as `0d2d85b`** — proves legacy persistence survives wizard navigation to
Preflight. R2-02 remains a code-path-not-at-fault finding; this test guards against future regressions. The case
was held uncommitted since the R2-02 slice, and the owner's ruling names the reason it still earns its place:
the flash, the `SetupDraft` write and Preflight's read of the stored ancestry are pinned as one path, and nothing
else in the suite crossed all three.

**Numbers, re-measured before staging (not inherited from the hold):** the file reads **14 passed (269
assertions)** and the case alone **1 passed (16 assertions)**, exactly the figures recorded when it was
deferred. Diff is one case, +31/-0.

**Provenance recorded in the commit body.** The working tree carried another session's uncommitted Trackblazer
slice while the suite ran (`CockpitController.php` +467/-2, `config/scenarios.php` +120/-2, `TrackblazerPanel.vue`
+119, plus three new panels and `tests/Feature/TrackblazerPlanningTest.php`), so the green is evidence against
that tree as well as against `29f5fc1`. The staged file itself holds no peer content: verified by hashing the
working file (`fa1e04f`), the index entry, and `git rev-parse HEAD:tests/Feature/CareerLegacyDeckStepsTest.php`
after the commit, all three equal, with `git diff --cached --name-only HEAD` showing exactly one path before the
commit and none after. The peer's files were left dirty and untouched.

## Phase B status, 2026-10-11: ruled yes, still owner-blocked

The owner ruled **B = authorize the migration**, with the reason recorded: the migration adds the four columns
FD-4 needs (`choice`, `decision_snapshot`, `corrected_from`, `superseded_by`), the dev file stays stuck for any
future session that wants to record a turn choice, and the peer work sits in the working tree rather than in the
dev DB, so the migrate does not race another session's edits.

**It has still not run, and not because of a refusal.** The safety layer accepts the migration only on the
verbatim sentence typed into the session by the owner — "Confirmed: run `php artisan migrate` against
`database/database.sqlite`." The ruling above is a yes in substance but not that sentence, and the manual says
plainly that this is not inferable from prior conversation. No migrate form was attempted this sitting, so the
standing rule against working around the refusal is intact.

**Measured state at the end of this sitting:** `php artisan migrate:status` on the dev file still reads
`2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table ..... Pending`, and the VACUUM snapshot taken
for Phase C confirms it independently: `has_choice=0` on the copy. Phase C therefore runs c2 because it must
migrate its own copy regardless; B is now hygiene for future sessions rather than a gate on this one.

## Phase C via extracted snapshot (c2), 2026-10-11 — executed. It is not the anchor walk, and says so

**Recorded as "C via extracted snapshot".** Owner ruling: c2, with the provenance caveat standing — this is a
substitute for the anchor walk against the dev file, not that walk, and the reason is peer drift, not preference:
`CockpitController.php` carries 467 uncommitted lines and `config/scenarios.php` 120, so any finding against the
drifted working tree would be evidence about an in-flight Trackblazer slice.

**Isolation, proven rather than assumed.** `git archive 29f5fc1` into `.scratch-uma/scratch-c/`, then a real
`composer install` there (the junctioned-`vendor` shortcut is recorded above as unusable). Proof it worked:
`(new ReflectionClass(App\Http\Controllers\Career\CockpitController::class))->getFileName()` resolves to
`.scratch-uma\scratch-c\app\...`, and that file is **1921 lines** against the primary tree's **2386**. The dev
file was VACUUMed to `phaseC.sqlite`, which carried **7 Veterans, 9 runs, 67 trainees, 559 support cards,
`has_choice=0`**, and migrating the copy put it at `migrations=60, maxBatch=15` with `choice=1`. The shared dev
file is unchanged: same query after the whole pass still reads 59 migrations, maxBatch 14, `choice=0`. Served on
`:8252` with `uma:db-guard` reporting `targets canonical: no`.

**Browser-layer caveat, stated because it is not eliminated.** `public/build` was copied from the primary tree,
so the Vue bundle is that tree's last build, not a build of `29f5fc1`'s `resources/js`. It carries no chunk for
the peer's three new panels (`grep -c TrackblazerRoutePlanner|TrackblazerFatiguePanel` on the manifest -> 0), and
this walk touches only Unity Cup and career surfaces, so the drift is immaterial here rather than absent.

**Walk, against run 10.**

| Step | Result |
| --- | --- |
| Scenario | `Stored choice: Unity Cup` |
| Trainee | `Stored choice: Rice Shower` via `?search=Rice` |
| Target | visited, left unrecorded, as the manual directs |
| Legacy | Parent A `Vodka [Wild Top Gear] · 7`, Parent B `Maruzensky [Formula R] · 2`, A `Rank A+`, B **`Rank UG`**, B `Rented from a friend`, affinity `◎`, `Legacy recorded` |
| Deck | six slots filled: `Tokai Teio`, `Nishino Flower`, `Biko Pegasus`, `Matikanetannhauser`, `Haru Urara` Owned; slot 6 · Friends `Super Creek [Piece of Mind]` **Rented** |
| Preflight | `Start Career` -> `/training-runs/10/cockpit` |
| C.7 turn | Speed 607, Stamina 577, Power 549, Guts 736, Wit 397, SP 173, Fans 209,245, mood `GREAT`, band Low, outcome Success -> `TURN 1`, stat strip reads `Speed 607 / no target set` |
| C.7 correction | band `low` saved and re-read as `low`, then `mid` saved and re-read as `mid` |
| C.8 identity | rarity `3`, potential `2`, growth Stamina `10`, Guts `20`; the strip renders `Stamina +10%` and `Guts +20%` inline |
| C.8 team | all seven fields persisted and re-read after reload; `select * from training_runs where id=10` confirms `Blue Bloom`, `Dreaming Big`, 8, 4, 55, 6, 5 |
| C.9 deck | run-scoped builder shows the six cards, `aria-pressed` toggles read Owned x5 + Rented |
| C.9 timeline | shows Turn 1 |
| C.9 result | `/training-runs/10/result` answers 200 and prints **`This career is still running, so there is no result`** — the Active refusal holds |
| Console | **zero errors across every step** |

**Finding 1, the one that matters for the plan: the wizard cannot produce the anchor's position.** C.6 expected
`Senior Early October, 5 turns remaining`. Measured on the created run: `Junior Year · Early January · TURN 1`.
The setup wizard records no year, month or turn count, and the Cockpit prints `YEAR N/A` and `MONTH N/A` with
their reasons until turns are recorded, so the canonical position is reachable only through the snapshot-import
path (`/career/snapshot`, PRD FR-G), not through the six steps this walk drove. **Phase C's anchor is therefore
still not walked**, and no number of wizard runs will walk it: the step that carries the anchor data is a
different door. This is the substantive result the manual asked C to produce.

**Finding 2: three of the six card titles in the manual do not match what the picker displays.** Display prints
`Tokai Teio [Teio-Oo-Oo!!!]`, `Nishino Flower [Little Cupcakes, Big Emotions]` and `Haru Urara [Tracen Academy]`
where the manual lists `[Dream Big!]`, `[Even the Littlest Bud]` and `[Urara's Day Off!]`; the other three match
exactly. `DeckSelectController::picker()` searches `char_name` **or** `title_en`, so a walk keyed on the manual's
bracket titles matches three cards and misses three. Not a defect; it is the `title_en` vs display split the
register already recorded around `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`, now with per-card evidence, and it
is a trap for any future scripted walk.

**Finding 3, an open question and deliberately not filed.** `spirit_bursts_count` is stored as 6 and the editor
re-reads 6, while the header rail prints `SPIRIT BURSTS N/A not yet recorded` — yet `unity_trainings_count` is
stored as 55 and the same rail prints `Unity Trainings 55`. Two columns, one row, two display outcomes. This
session probed twice and stopped short of calling it a defect because the rail may own a different concept from
the editor counter (exactly the shape that killed the scenario-select false alarm above). Named next step: read
the rail's props in `resources/js/components/career/` (the only `Spirit Bursts` label lives in
`UnityCupEditor.vue`, the editor) and establish whether the header figure is the counter or a race-derived value,
then file or withdraw.

**Two driver bugs, recorded so nobody reads them as app faults.** (1) A walk script crashed before
`storageState()`, so its draft was lost and the next run started empty and created no run at all; the surviving
scripts now save state in a `finally`. (2) `selectOption({ index: 1 })` on `energy_band` chose **Mid**, not Low,
because option 0 is Low; the Low-then-Mid sequence above is from the corrected script selecting by value. A third,
a doubled base URL, only cost one re-run.

**Also verified-not-a-defect, second time this session: a collapsed disclosure hides a real 44px control.** The
band select and all seven team inputs exist in the DOM but report `element is not visible` until their
`<details>` is opened — KI-92's mechanism on the Cockpit rather than on a sweep. Any future walk script must
open the disclosure before touching the control, and a locator that "resolves" is not a locator that acts.

**Cleanup.** `:8252` reaped by PID, `phaseC.sqlite`, the session state and the extracted root removed. The
`.mjs` drivers stay in gitignored `.scratch-uma/`. Phase C produced **no source change**, so no `pint`,
`phpstan`, `typecheck` or lore gate was owed, and nothing was committed for it.

## Phase C.1 and C.2 resolved, 2026-10-11 — one confirmed defect filed, one false finding withdrawn

### C.1 Spirit Bursts: CONFIRMED DEFECT, filed as KI-97 (`689267f`), not fixed here

The open question from the walk was resolved by reading the display path, and it is a defect.
`ResourceStrip.vue:43` maps the `spirit_bursts` widget to the value key `bursts`, and
`CockpitController.php:316` sets `'bursts' => null` unconditionally in the header's `values` array, so a Unity Cup
run prints `SPIRIT BURSTS N/A / not yet recorded` no matter what the Trainer entered. The three arguments that
make it a defect rather than a ruled absence are in the KI entry; the shortest is that the rail's own caption
(`sub = 'normal plus Extreme'`) asks for the combined figure, and `combined_bursts` is already computed and
pinned at 11 by `CockpitUnityCupCountersTest:47`.

**The design ambiguity the walk flagged does not exist.** I had left "6 or 11" as a question for a ruling. The
shipped copy answers it: the caption says normal plus Extreme, so the rail wants 11. No owner decision is needed
to pick the value, which is why the fix is one line rather than a discussion.

**Scope held to the one line.** `pragma_table_info('training_runs')` shows no `grade` and no `coin` column, so the
sibling `'grade_points' => null` and `'shop_coins' => null` honestly satisfy the comment's release condition and
were left alone. Only `burst`-named columns exist (`spirit_bursts_count`, `extreme_bursts_count`).

**Why no fix shipped.** Standing rule 2. `CockpitController.php` carries a peer's **467 added / 2 deleted**
uncommitted lines in this worktree, and that working copy still reads `'bursts' => null` at `:317`, so the
in-flight Trackblazer slice is not fixing it either. The paired test was not landed on its own, because a
component change and the assertion that pins its behaviour are one commit or nothing. The entry names the exact
line and the exact assertion for whoever owns the controller next.

### C.2 card brackets: NOT an app defect. The walk's own selection rule was wrong, and this register row is corrected

**The claim in the walk's table above is withdrawn and restated.** It recorded the six deck slots as if they were
the manual's six cards. Three of them were the wrong variant of the right character:

| Intended (`title_en`) | Actually placed | Why |
| --- | --- | --- |
| `Tokai Teio [Dream Big!]` (speed) | `Tokai Teio [Teio-Oo-Oo!!!]` (intelligence) | picker orders `type, char_name, title_en`; `intelligence` sorts before `speed` |
| `Nishino Flower [Even the Littlest Bud]` (speed) | `Nishino Flower [Little Cupcakes, Big Emotions]` (intelligence) | same |
| `Haru Urara [Urara's Day Off!]` (SR guts) | `Haru Urara [Tracen Academy]` (R guts) | same type and name, then `T` before `U` in `title_en` |
| `Biko Pegasus [Double Carrot Punch!]` | matches | - |
| `Matikanetannhauser [Just Keep Going]` | matches | - |
| `Super Creek [Piece of Mind]` | matches | - |

**Source and catalogue both checked out, so none of the manual's three branches applies.** A read-only query of
`support_cards` on the dev file returns all six `title_en` values exactly as the source document spells them,
brackets and the apostrophe in `Urara's Day Off!` included (`Haru Urara | title_en='[Urara\'s Day Off!]' |
rarity=3 | type='guts'`). The picker is not broken: `DeckSelectController::picker()` searches `char_name` **or**
`title_en`, so a search for `Dream Big!` finds the intended card. What failed was my driver's rule, "first option
whose label contains the character's name", against a catalogue where one character holds up to six cards. The
correct rule for any future anchor walk is to match the bracket title, or to search by it.

**Consequence for the walk's other results: none, and it is stated rather than assumed.** The deck content was
wrong; the deck *mechanism* checks still hold (six slots written, per-slot ownership flags, slot 6 · Friends
carrying `Rented`, Preflight carrying the flags onto `deck_slots`). C.7 and C.8 never read the deck, so the turn
write, both band corrections and all seven team fields remain as recorded. What the walk did **not** prove is
that the canonical Unity Cup deck can be entered, because the card that carries the scenario link
(`Haru Urara [Urara's Day Off!]`, the deck's only `yes` in the source's link column) was never placed.

**One number in a landed commit body is wrong and is corrected here rather than by rewriting.** `689267f`'s
message says the peer's controller carries "+471 lines"; the measured figure, before and after the commit, is
**467 added / 2 deleted**. The KI-97 entry and this register both state 467. Following the standing ruling on
attribution errors in landed commits, the record is amended by note and history is left alone.

## Phase C.2 anchor deck re-run, 2026-10-11 — CLEAN. The canonical deck is enterable, and the row above is closed

**What changed since the withdrawn row:** the selection rule moved from character name to the catalogue's own
bracket title. Rebuilt the c2 scaffold the same way and re-proved it before trusting anything: the extracted
root's `CockpitController` resolves inside `.scratch-uma/scratch-c/` at **1921 lines** (the primary's dirty copy is
2386), `php artisan migrate` ran against `phaseC2.sqlite` only (`MIGRATE_EXIT=0`, the pending migration DONE),
`uma:db-guard` -> `targets canonical: no`, and the primary `vendor/` came out of the whole pass at 51 entries,
untouched. The snapshot carried 7 Veterans and 9 runs as before.

**The six placements, read back off the page after Save deck.**

| Slot | Card as the app prints it | Ownership | Scenario link |
| --- | --- | --- | --- |
| 1 | `Tokai Teio [Dream Big!] · SSR Speed` | Owned | Not a Scenario Link |
| 2 | `Nishino Flower [Even the Littlest Bud] · SSR Speed` | Owned | Not a Scenario Link |
| 3 | `Biko Pegasus [Double Carrot Punch!] · SSR Speed` | Owned | Not a Scenario Link |
| 4 | `Matikanetannhauser [Just Keep Going] · SSR Guts` | Owned | Not a Scenario Link |
| 5 | `Haru Urara [Urara's Day Off!] · SSR Guts` | Owned | **Scenario Link** |
| 6 · Friends | `Super Creek [Piece of Mind] · SSR Stamina` | **Rented** | Not a Scenario Link |

Preflight -> `Start Career` created run 10 on the snapshot, and the run-scoped deck builder at
`/training-runs/10/deck` re-read all six with identical identities and identical flags. **Zero console errors.**

**Three of the four checklist facts verified; the fourth does not describe the product.**

1. `3 Speed / 1 Stamina / 2 Guts` — **withdrawn as an expectation, not failed.** No such aggregate is printed
   anywhere. `DeckAnalysis.vue:41` types its input as `categories: Record<string, Category>` and the shipped block
   renders **effect** categories ("Training power" with each card's stated anchor beside it), and its own docblock
   says the brief's bar chart became "the number beside its label". `git grep "Speed.*Stamina.*Guts"` at `29f5fc1`
   returns only `TrainerAdvisor.php` (stat ordering), `SupportTypeMark.vue` (the per-card mark) and
   `Veterans/Compare.vue` - no deck aggregate. The per-row types do sum to 3/1/2, so the manual's arithmetic is
   right about the deck and wrong about there being a line that prints it.
2. Slot 5 is the deck's only scenario-linked card — **verified two ways.** On the wizard step, 6 occurrences of
   `Scenario Link` of which 5 are `Not a Scenario Link`; on the run builder, exactly one row flagged `LINK`. This
   matches `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:96` (`Haru Urara` as the only `yes`).
3. Ownership `Owned` x5 plus slot 6 `Rented` — **verified** via `aria-pressed` per slot.
4. The run-scoped builder re-reads the six identities — **verified**.

**Consequence: the open item from the C walk is closed.** The canonical Unity Cup deck can be entered through the
six wizard steps and survives into `deck_slots` with its per-slot ownership flag and its one scenario link. The
three wrong-variant rows in the withdrawn entry above stay as they were written; this section is the correction,
not an erasure.

**This pass's own two failures, recorded because they cost time and one of them nearly produced a false number.**
(1) `composer install --no-audit` is not a valid flag on this composer build; composer exited on a usage error and
installed nothing, and because the invocation ended in `| tail -20` the reported exit code was **0** - the
pipeline-status trap recorded before, hit again in the same session. Re-run without the flag and capturing
`$?` directly showed the truth. (2) After trimming the deck table from four columns to three I left a four-field
destructure, so `title` received the slot number and the driver threw `title.replace is not a function`. Both are
driver faults; neither touched the application. (3) A related discovery worth keeping: `Start Career` consumes the
setup draft, so the wizard's Deck analysis cannot be re-read after creation - the aggregate check needed a second
pass that stops before Preflight, which is where fact 1's answer came from.

**Cleanup, per C.2.7.** `:8253` reaped by PID; `phaseC2.sqlite*` and its session state deleted; the extracted
tree **kept** in gitignored `.scratch-uma/scratch-c/` with its own `vendor/`, so a repeat does not pay another
`composer install`. Nothing was committed: C.2 produced no source change, so no `pint`, `phpstan` or `typecheck`
was owed, and the register remains untracked pending the owner's disposition ruling.

## KI-97 fixed and closed, 2026-10-11 (`5244f4c`) — the rail now prints 11

**The gate opened on its own.** The peer landed `820a61e feat(cockpit): draw the Trackblazer planning, calendar
and fatigue surfaces (G5)`, which cleared `CockpitController.php`, so Step KI-97.1's condition was met and the fix
was this session's to land. **A correction to something I said in the previous report:** I described the tree as
"fully clean" after that commit. It was not. My status command had filtered `.md` files out, and behind that
filter sat a session's G6 work - `~14,900` dirty lines across `docs/research-scratch/`, plus `config/scenarios.php`
and a second Trackblazer pass. The controller itself was clean at the moment I checked it, which is what the gate
required, but the broader claim was wrong and the fix's landing had to account for it.

**What shipped.** `'bursts' => $team['combined_bursts'] ?? null` at the header values array, reading the panel's
own figure so one number keeps one owner rather than being summed twice. The trailing `?? null` is load-bearing:
`CockpitUnityCupCountersTest` already pins that a run stating one tally and not the other has no combined total,
and the rail must keep the named absence there instead of inventing a half count.

**Evidence chain.** RED: the new feature case failed on its own assertion, `1 failed / 5 passed`, at
`->where('header.values.bursts', 11)`. GREEN: `6 passed (103 assertions)`, and the neighbour `CareerCockpitTest`
held at `36 passed (427 assertions)`. Rendered proof, because the defect was a rendered thing: the team-editor
case in `career-cockpit-forms.spec.ts` already writes 6 and 5, so the new assertion checks the box that owns the
label *and* the value - `1 passed`, session-private port and scratch database. Then the check was proved to be a
check rather than a ceremony: restoring `'bursts' => null` made `toContainText('11')` fail while
`toHaveCount(1)` still passed, which is what shows the locator is right and the pass was not vacuous. The fix was
put back before committing.

**Gates with honest statuses.** `pint --test` on both explicit paths -> passed. `phpstan analyse
--no-progress --memory-limit=1G` -> `[OK] No errors`. `composer lore` -> exit 0, `composer lore-code` -> exit 0,
no hit attributable to these lines. **`php artisan test --compact` -> NOT RUN**: the safety layer refused the
command twice in a row, so the full suite is unexecuted and this commit rests on the two feature files plus the
browser case. That is a gap in the hand-off, not a passed gate, and it is recorded as such.

**Landing mechanics.** The controller and `KNOWN-ISSUES.md` both carry another session's uncommitted work at the
time of the commit (their ~106-line shop/ledger hunk in the controller, their KI-95 block in the register), so
both blobs were **rebuilt from `HEAD` with only this change replayed** and pushed through a private index with a
three-argument CAS `update-ref`. Verified four ways: the commit touches exactly 4 paths;
`git show HEAD:...CockpitController.php` carries the fixed line at `:320` while the commit's controller delta is
`+5/-1`; `git show HEAD:KNOWN-ISSUES.md | grep -c '^### KI-95'` is **0**; and the worktree afterwards still shows
the peer's `107/1` in the controller and `33` lines in the register, untouched and uncommitted. The shared index
was realigned for all four paths, and `git diff --cached HEAD` is empty.

**Program state after this.** Landed: A (`29f5fc1`), E (`0d2d85b`), KI-97 filed (`689267f`) and closed
(`5244f4c`). Executed and recorded: D, C via c2, C.2 re-run clean. Still owner-only: B (the verbatim migrate
sentence), C.3 (gated on B), the F.1-F.6 rulings, and the register's disposition. KI-97 was the last
agent-actionable code item on the manual, so nothing further opens without one of those four inputs.

## Phase G gate sweep, 2026-10-11 — run against `5244f4c`, every status below is a command's own exit code

**Scope, and the one measurement that had to be corrected to get it.** The first enumeration used a range,
`git diff --name-only 29f5fc1~1..5244f4c`, which returns 14 paths - and seven of them are not this program's:
`app/Models/TrainingRun.php`, `config/scenarios.php`, the three Trackblazer panels, `TrackblazerPlanningTest`
and `trackblazer-panel.spec.ts` all belong to the peer's G5/G6 work that landed between my commits. The honest
scope is the per-commit listing, which is five source paths:

| Path | Commit |
| --- | --- |
| `tests/browser/career-legacy-deck-steps.spec.ts` | `29f5fc1` |
| `tests/Feature/CareerLegacyDeckStepsTest.php` | `0d2d85b` |
| `app/Http/Controllers/Career/CockpitController.php` | `5244f4c` |
| `tests/Feature/CockpitUnityCupCountersTest.php` | `5244f4c` |
| `tests/browser/career-cockpit-forms.spec.ts` | `5244f4c` |

(`KNOWN-ISSUES.md` is the sixth path and carries documentation only.)

**The sweep, with the status each command returned.**

| Gate | Result |
| --- | --- |
| `npm run typecheck` | exit 0 |
| `pint --test --format agent` on the three PHP paths | `{"tool":"pint","result":"passed"}`, exit 0 |
| `phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors`, exit 0 |
| `composer lore` | exit 0 (314 hits, 78 exempt) |
| `composer lore-code` | exit 0 (128 hits) |
| `vendor/bin/pest` on the two touched feature files | 20 passed (372 assertions), exit 0 |
| `npx playwright test` on the two touched specs | **25 passed (8.9 m)**, exit 0 |
| `php artisan migrate:status` | exit 0; `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` **Pending** |
| `npm run build` | **not run, deliberately.** The peer's Vue was mid-write again (`TrackblazerPanel.vue` plus a new `TrackblazerResourceLedger.vue`), and the build writes `public/build/manifest.json` that every session's browser reads. Recorded as deferred, not as passed |
| `php artisan test --compact` | **not run.** Refused twice during KI-97; a third attempt is thrashing against the guardrail, so this is a hand-off gap and the change rests on the per-file runs above |

**Both lore hit-counts rose since the KI-97 landing** (313 -> 314 docs, 119 -> 128 code). Neither is attributable
to these paths: the increase tracks the peer's G6 documentation and the new Trackblazer component. The gate
itself passes on both runs, which is the status that matters; the counts are named here so a later reader does
not read the rise as this program's.

**Where the program stands.** Landed and verified: A, E, KI-97 (filed and closed), with D, C via c2 and C.2
executed and recorded. Owner-only and unchanged: Phase B (the verbatim sentence; the dev file is still Pending,
so C.3 stays gated), the F.1-F.6 rulings, and this register's disposition. The full suite is the one gate this
program could not run, and it is named as such rather than implied away.

**HEAD advanced mid-sweep, and the fix survived it.** The peer landed `3ae564b feat(cockpit): draw the
Trackblazer shop ledger (G6)` while the browser specs were running, so HEAD is no longer the `5244f4c` the sweep
was run against. Two checks, because a peer commit touching the same file is exactly where a fix disappears
quietly: four of the five swept paths are **byte-identical** between `5244f4c` and HEAD (`git rev-parse` per
path), and the fifth, `CockpitController.php`, is the one G6 touched - `+107/-1` of shop/ledger code. The fix is
still there: `git show HEAD:...CockpitController.php | grep -n "'bursts' =>"` reads `320: 'bursts' =>
$team['combined_bursts'] ?? null`, the KI-97 comment is present, and the header `values` array is otherwise whole
(`team_rank` 316, `bursts` 320, `grade_points` 321, `shop_coins` 322). So the sweep's green is a statement about
these five paths at `5244f4c`, and the only path whose HEAD content differs since is the controller, where the
difference is the peer's work layered on top of this program's line rather than instead of it. What that means
for a future reader: the controller at HEAD carries 107 lines no gate in this sweep ever exercised, and their
own suite (`TrackblazerCockpitTest`, `TrackblazerPanelTest`) is where that coverage lives.

**Re-verified at `3ae564b`, so the green is not a claim about a superseded SHA.** After G6 landed the affected
tests were run again at the new HEAD rather than inherited from the `5244f4c` sweep:
`vendor/bin/pest tests/Feature/CareerLegacyDeckStepsTest.php tests/Feature/CockpitUnityCupCountersTest.php` ->
**20 passed (372 assertions)**, exit 0; the rendered KI-97 assertion (`npx playwright test
tests/browser/career-cockpit-forms.spec.ts -g "records the Unity Cup team state"`) -> **1 passed**, exit 0, on a
session-private port and scratch database; and `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` -> 3
passed (22 assertions), exit 0. So KI-97's closure holds with the peer's shop code in the same file, not only at
the SHA it landed on. Pre-flight at this HEAD also settles the close-out tree check: `git status --short` filtered
for source shows **nothing tracked and dirty** - the peer's G5 and G6 have both landed, and the only untracked
entry is `crush.json`, an agent-tool config this program never touched. The fix reads
`'bursts' => $team['combined_bursts'] ?? null` at `CockpitController.php:320`, with `combined_bursts` computed at
`:2253`, which is what shows the peer's commit layered their work on top of the fix rather than over it.

## Phase B executed, 2026-10-11 — the dev file is migrated, and C.3 is unblocked

**Owner authorization:** the verbatim sentence, typed by the owner in session. This is the first time it was
given; the earlier refusals were correct and are recorded above.

**What ran.** `php artisan migrate --no-interaction` against `database/database.sqlite` ->
`2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` DONE in 41.42 ms, exit 0. `migrate:status`
now reads it as `[15] Ran`, and no migration is Pending. The four columns the plan names are all present:
`Schema::getColumns('turn_entries')` intersected with `['choice','decision_snapshot','corrected_from','superseded_by']`
-> **present: all four, missing: none**, and `Schema::hasColumn('turn_entries', 'choice')` -> `yes`.

**Shared-state care.** A peer's `php artisan serve` is listening on `:8000`; it was noted and left running, per the
rule about not killing another session's server. SQLite's `busy_timeout` arbitrated the write. The dev file's
fingerprint after the run is 2,691,072 bytes, mtime 2026-10-11 18:49.

**Why this mattered beyond C.3.** The dev file had sat one migration behind for two days while the harness scratch
databases ran ahead of it, which is the class KI-60 recorded: a green suite says nothing about the shared file.
The file can now record a turn choice, so a turn write against it will not fail on a missing column.

## Phase F ruled-no, 2026-10-11 — blanket decline, no slice opened

The owner ruled **F-none**: no current need for any of the six conditional slices. One dated line each, no code:

- **F.1 card levels and limit breaks** - Ruled-no, no current need.
- **F.2 Competitive Build purpose (`BuildPurpose` case)** - Ruled-no, no current need.
- **F.3 risk tolerance (`BuildTargetPayload::KEYS`)** - Ruled-no, no current need.
- **F.4 per-skill priority marks (`skill_priorities` shape)** - Ruled-no, no current need.
- **F.5 remaining settings items** - Ruled-no, no current need.
- **F.6 Grand Concert slices 21-22** - Ruled-no, no current need.

Each gate is recorded here so a later session knows the absence is a ruling rather than an oversight: F.1 needed a
`PRD.md` §6.9 decision on a `user_support_cards` table or a per-slot column, F.2 to F.4 needed shape rulings on
the advisor contract, F.5 individual item rulings, and F.6 evidence at level 1 or 2. None was given, so none was
opened, and no source file was touched for any of them.

## Phase C.3 anchor walk on the dev file, 2026-10-11 — CLEAN, and it is the walk Phase C could not run

**This is the first walk against `database/database.sqlite` itself**, which is what Phase B unlocked: the file now
has `turn_entries.choice`, so a turn write completes there. Served on `:8260` from the working tree (the peer's
`:8000` server was left running). A fresh draft was started, not the prior sitting's.

**Dev file state before the walk (C.3.1):** 0 migrations Pending, **7 Veterans**, run 9 **Active**, 9 runs total.

**Run 10 was created on the dev file.** The six canonical cards went in by bracket title, the C.2 lesson applied
from the start:

| Slot | Card | Ownership | Scenario link |
| --- | --- | --- | --- |
| 1 | `Tokai Teio [Dream Big!] · SSR Speed` | Owned | Not a Scenario Link |
| 2 | `Nishino Flower [Even the Littlest Bud] · SSR Speed` | Owned | Not a Scenario Link |
| 3 | `Biko Pegasus [Double Carrot Punch!] · SSR Speed` | Owned | Not a Scenario Link |
| 4 | `Matikanetannhauser [Just Keep Going] · SSR Guts` | Owned | Not a Scenario Link |
| 5 | `Haru Urara [Urara's Day Off!] · SSR Guts` | Owned | **Scenario Link** |
| 6 · Friends | `Super Creek [Piece of Mind] · SSR Stamina` | **Rented** | Not a Scenario Link |

Ancestry: `Vodka [Wild Top Gear] · 7` and `Maruzensky [Formula R] · 2`, Parent B rank letter `UG` (accepted, and
echoed back as `Rank UG`), Parent B rented, affinity `◎`. `Start Career` -> `/training-runs/10/cockpit`.

**C.3.4 initial state, with one number corrected against the plan.** Identity editor present (rarity select,
potential level, **5 growth-rate inputs**), **team editor present with all 7 fields**, deck link present, and **5
`button[type="submit"]` controls** on the page (the plan says "three header forms each render a submit control";
five is what the page carries, the three among them). Stat strip reads `Speed N/A / no target set`. Position reads
**`Junior Year`, `TURN 0`** - the plan expected `TURN 1`, and 0 is correct: the counter is the number of recorded
turns, and none was recorded at that point. It reads `TURN 1` immediately after the first turn (below).

**C.3.5 the canonical turn.** Speed 607, Stamina 577, Power 549, Guts 736, Wit 397, SP 173, Fans 209245, mood
GREAT, band Low, outcome Success -> cockpit reads `TURN 1`, `Speed 607`, `FANS 209,245`, `MOOD GREAT`. The
correction form then read the band back as **`low`**, and after changing it to Mid and reloading it read **`mid`**.

**C.3.6 every field edit read back.** Rarity `3`, potential `2`, growth Stamina `10` / Guts `20`, and all seven
team fields: `Blue Bloom`, `Dreaming Big`, `8`, `4`, `55`, `6`, `5`.

**The KI-97 check the plan asked for, and it passes on the dev file.** With 6 and 5 stored, the header rail
renders **`SPIRIT BURSTS 11 normal plus Extreme`** - one box, the combined figure, and **not** `N/A`. That is the
rendered proof of the `5244f4c` fix on a real run rather than on a test fixture, and it is the specific
regression the plan told this walk to watch for.

**C.3.7 cross-screen.** The run-scoped deck builder re-reads all six identities with 5 Owned + 1 Rented. The
timeline shows Turn 1. The Result screen refuses: `still running, so there is no result to read yet.`, served as
`Career/Result` with the run left Active. **Zero console errors across the whole walk.**

**Outcome: clean, no defect, no slice.** Run 10 on the dev file is now `Active` with 1 turn entry, which is a
shared-state side effect the plan intended and the owner authorized by clearing Phase B.

**What this closes.** Phase C was the one phase that could not run against the canonical database, and its
missing steps were named rather than inferred. They are now executed: 1.1 deck, 1.2, 1.3 turn write, 1.4 and 1.5
all ran on the dev file in one continuous session. The anchor's *position* (Senior Early October) remains
unreachable by the wizard - that is a property of the setup path, not a gap in this walk, and the snapshot import
remains the only door to it.

## Program close-out, 2026-10-11

**The line above the gate sweep that reads "Owner-only and unchanged: Phase B ... the F.1-F.6 rulings, and this
register's disposition" is superseded by this block.** All three decisions landed in one session and were
executed:

- **B executed.** `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table` applied to
  `database/database.sqlite` as batch 15, exit 0; the four columns `choice`, `decision_snapshot`,
  `corrected_from`, `superseded_by` all verified present; `migrate:status` clean. A peer's `:8000` server was
  left running throughout.
- **F ruled-no.** Six dated lines, one per slice, no code opened.
- **G executed as g1.** The register landed whole as **`b180df6`**, one file, 1454 insertions, committed blob
  `40b48c9`, by pathspec so the peer's unstaged work stayed unstaged. A second small commit carries this
  close-out block, because leaving the sweep section's "still owner-only" line standing would have been a stale
  claim rather than history.
- **C.3 walked clean** on the dev file, and the KI-97 rail printed `11` there.

**Commits this program landed, in order:** `29f5fc1` (W2 spec repair), `0d2d85b` (R2-02 regression test),
`689267f` (KI-97 filed), `5244f4c` (KI-97 fixed and closed), `b180df6` (register), plus the close-out commit.
Every one is an ancestor of HEAD, and `git diff --cached HEAD` is empty.

**Gates at HEAD, each status a command's own exit code:** `npm run typecheck` 0; `pint --test` on the changed
paths passed; `phpstan analyse --memory-limit=1G` `[OK] No errors`; `composer lore` 0; `composer lore-code` 0;
`pest` on the touched feature files 20 passed (372 assertions); `playwright` on the touched specs 25 passed;
`php artisan migrate:status` no Pending. **Not run: `php artisan test --compact`**, refused twice by the
session's safety layer - the single unexecuted gate in this program, named here rather than implied away.
`npm run build` was deferred for the same reason it always was: peer Vue was mid-write, and the build writes the
manifest every session's browser reads.

**Tree at close:** no file this program authored is dirty. What remains uncommitted belongs to another session -
the retired `RecommendationCard.vue` (a working-tree deletion, nothing imports it; the only reference left is a
comment in `TrainingDetail.vue`, the file that session is editing), `TrainingDetail.vue` itself, and an untracked
`walkthrough-scratch.spec.ts`.

**The program is complete.** Every Round 2 finding has a disposition, every Trainer Desk 2.0 slice A1-E6 is
landed, the four round-2 phases that could produce code produced it, and the one phase that could not (C against
the canonical file) ran once Phase B cleared its gate.





