# Trainer Desk — Remaining Implementation Plan
## Phase-and-slice execution instructions for `umamusume-laravel13`

**Purpose:** Implement the remaining product work in small, testable code/UI slices, from the current shipped baseline to a coherent Trainer Desk 2.0 experience.

**Repository:** `D:\Projects\umamusume-laravel13`  
**Application:** Laravel 13, PHP 8.5, SQLite, Inertia.js, Vue 3 + TypeScript, Tailwind v4, Pest 4, Playwright.

---

## Execution contract

This plan is an implementation checklist, not a documentation project. **Every slice must change runtime behavior, application code, tests, or visible UI.** Do not count README edits, audit reports, summaries, changelogs, or screenshots as completion.

### Mandatory working rules

1. **Inspect the live tree before each slice.** The source brief describes the tree as observed on 2026-10-10 and records an in-flight, uncommitted deck-ownership remediation. Some work may have landed since then.
2. **Exclude work that is already implemented.** Verify routes, actual form submissions, persistence, and tests—not merely the presence of a column or read-only display. If a slice is already complete, run its relevant tests and mark it skipped in the execution tracker; do not rewrite it.
3. **Protect concurrent work.** Check `git status` and diffs before editing. Do not revert, overwrite, stage, or commit another session's changes. Avoid overlapping edits to shared files such as `Cockpit.vue`, `TrainingRunController.php`, and scenario panels.
4. **Respect architectural owners.**
   - `config/scenarios.php` is the single source for scenario names and scenario composition/labels.
   - `ScenarioCaps` is the single owner of stat-ceiling arithmetic (`ADR-0015`).
   - `DeckSlot::POSITIONS` and `DeckSlot::OWNERSHIP` own deck positions and ownership values.
   - `SetupDraft` owns pre-run wizard state; the wizard creates the run only at Preflight.
   - The Legacy Lab records ancestry; it must not calculate inheritance, affinity payout, star-roll odds, or recommended combinations.
   - Keep the four Global scenarios: `ura_finale`, `unity_cup`, `trackblazer`, and `our_grand_concert`. Do not import the JP-only scenario guide.
5. **No unsupported mechanics.** Where game rules are not confirmed by code or sufficiently strong source evidence, build a clear manual-recording UI or leave the value explicitly unknown. Do not invent formulas, recommendations, or scenario rules.
6. **Test safely.** Feature tests use the test database, not the development SQLite file. Browser tests need a session-private port and scratch DB under the repository's KI-73/KI-80 constraints. Do not run `pint --dirty` in this shared worktree (KI-75).
7. **Each slice is complete only when** its behavior is implemented, validation/persistence is covered, the relevant focused tests pass, and at least one appropriate browser spec covers important user-facing interaction where practical.

### Standard slice workflow

For each slice:
1. Confirm current behavior and authoritative requirements in `PRD.md`, `ARCHITECTURE.md`, `SCREEN_SPEC.md`, accepted ADRs, routes, controllers, models, and existing tests.
2. Identify the smallest backend/UI/test change that completes the behavior.
3. Implement the change with existing project patterns and design tokens.
4. Add or update Pest feature/unit tests and Playwright coverage for the visible flow.
5. Run focused tests, then relevant adjacent regression tests and static checks that are safe in the shared tree.
6. Verify loading, empty, validation-error, success, and narrow-screen states where relevant.
7. Inspect the final diff and ensure unrelated work remains untouched.

---

# Phase 1 — Close high-impact cross-screen defects

## Slice 1.1 — Make stat denominators understandable and consistent (R2-09)

**Outcome:** Trainers can tell whether a displayed stat is compared with a personal target or a scenario ceiling.

**Inspect first**
- `app/Services/ScenarioCaps.php`
- `app/Domain/Training/StatCeilings.php`
- `resources/js/components/career/CareerStatePanel.vue`
- Event-decision page/component and its controller props
- `tests/Feature/**` for Cockpit and event-decision state

**Implement**
1. Preserve `ScenarioCaps` as the only owner of cap arithmetic.
2. Choose a coherent presentation contract:
   - Cockpit target progress remains `current / target`.
   - Event decision may retain `current / cap` where the ceiling is relevant, but the denominator must be explicitly labelled (for example, “Stat ceiling”), and target progress must not be presented as ceiling progress.
   - If a single denominator policy is chosen by the product owner, apply it consistently instead; do not silently conflate target and cap.
3. Add accessible labels/help text and consistent number formatting.
4. Keep `N/A` for genuinely absent targets; do not render a false zero.
5. Ensure controllers provide both values only where the screen needs them and that the UI labels the one it is displaying.

**Expected code changes**
- The shared stat display component(s)
- Event-decision Vue page/component
- Relevant controller/prop mapping only if required
- Focused feature tests and a cross-screen browser spec

**Acceptance checks**
- A test proves target and ceiling are not interchanged.
- Missing targets render as unavailable, not zero.
- Cockpit and Event decision make the denominator's meaning clear.
- Existing ceiling arithmetic tests remain green.

## Slice 1.2 — Correct the Skills Planner empty-priority state (R2-10)

**Outcome:** An empty priority set is not reported as a successful “0 SP” coverage calculation.

**Inspect first**
- `app/Services/SkillSpendCoverage.php`
- `SkillsPlannerController`
- `resources/js/pages/Career/SkillsPlanner.vue` (confirm exact current path)
- Build-target/priority write path and tests

**Implement**
1. Distinguish three states in the service/controller contract:
   - no priorities configured;
   - priorities configured and covered;
   - priorities configured but not covered.
2. Return an explicit state or equivalent typed representation instead of inferring “no priorities” from `total_cost === 0`.
3. Render a useful empty state that explains how to add priorities and links to the actual target/priority editing surface.
4. Keep the calculated remaining-SP result only for a non-empty priority set.
5. Avoid implying that every skill has been covered when there are no selected priorities.

**Expected code changes**
- `SkillSpendCoverage`
- `SkillsPlannerController`
- `SkillsPlanner.vue`
- Pest tests for all three states
- Browser spec for empty-state copy and the inbound action

**Acceptance checks**
- Empty priorities never show a misleading “0 SP” coverage result.
- A genuinely covered non-empty set still shows success.
- An uncovered set still shows the correct remaining amount.
- The empty-state action leads to a usable edit surface.

## Slice 1.3 — Add the missing inbound path to the run-scoped Deck Builder

**Outcome:** A Trainer can reach and return from the deck builder for the current run without entering the setup wizard.

**Inspect first**
- `resources/js/pages/Career/TrainingDetail.vue` around the existing deck link
- `resources/js/components/career/CareerLayout.vue`
- `resources/js/pages/Support/Builder.vue`
- `routes/web.php` and the named `runs.deck` / `runs.deck.sync` routes
- Current R2-06 deck-ownership remediation and its tests

**Implement**
1. Repoint the run-scoped link to `/training-runs/{run}/deck` using the named route/route helper already used by the project.
2. Add an obvious inbound link from the Cockpit or another natural run-scoped entry point if none exists.
3. Preserve the distinction between the setup wizard's draft deck and the saved run deck.
4. Keep the existing ownership semantics and corrected ownership copy. Do not redo R2-06 if already present.
5. Add a return path to the Cockpit or originating run screen.

**Expected code changes**
- `TrainingDetail.vue`
- Cockpit/career navigation component if needed
- Playwright navigation test and focused feature coverage

**Acceptance checks**
- The link always opens the deck for the current run.
- It never drops the user into `/career/setup/deck` from a saved run.
- Empty deck slots do not show an ownership flag.
- Owned/rented wording matches the persisted `deck_slots.ownership` value.

## Slice 1.4 — Finish the energy-band write/display path

**Outcome:** The stored energy state is editable and visible wherever the product expects a Trainer to correct a turn.

**Inspect first**
- Migration adding `energy_state` to `turn_entries`
- `StoreTurnEntryRequest` and turn store/update actions
- Guided Training entry page
- Cockpit turn-correction form
- Turn model/cast/enums and related tests

**Implement**
1. Confirm the canonical set of energy-band values from existing domain code and requirements; do not invent additional values.
2. Add the energy-band input to guided turn entry and the Cockpit correction form if both paths are intended to edit the same record.
3. Ensure validation accepts the same values in both paths.
4. Persist the field without erasing it when an unrelated correction omits it, unless the user explicitly clears it.
5. Render the saved band in Cockpit/Timeline/Result only where their screen contracts call for it.
6. Make numeric energy and categorical energy band visually distinct; they represent different facts.

**Expected code changes**
- Relevant Form Request(s), action/controller, Vue form(s), turn display component(s)
- Feature tests for store, update, and omitted-key preservation
- Browser test covering edit → save → reload

**Acceptance checks**
- The band survives save and reload.
- An unrelated correction does not erase an existing band.
- Invalid values are rejected and surfaced accessibly.
- Numeric energy and energy band are not conflated.

## Slice 1.5 — Add a real UI write path for run identity fields (R2-13)

**Outcome:** Rarity, potential level, and growth rates can be recorded/edited through the Cockpit, not only seeded or imported.

**Inspect first**
- `StoreTrainingRunRequest`
- `TrainingRunController::update()`
- `resources/js/pages/Career/Cockpit.vue`
- `resources/js/components/career/CareerHeader.vue`
- Existing model casts, enums/config, and `CockpitRunIdentityTest`

**Implement**
1. Add a compact identity editor to the Cockpit, matching the current edit/review interaction pattern.
2. Provide inputs for trainee rarity, potential level, and the five stat growth-rate values using existing domain values and validation.
3. Show current saved values clearly and allow a deliberate “unset” state only if the existing nullable contract permits it.
4. Submit through the existing `runs.update` path and validated request; do not add a duplicate endpoint.
5. Preserve the read-only career header as a summary of saved values.
6. Ensure edits do not reset status, scenario, grade-point period, target, or other fields absent from the submitted payload.

**Expected code changes**
- `Cockpit.vue`
- Possibly a focused `CareerIdentityForm.vue` component
- Existing request/controller only if current validation/prop mapping is insufficient
- Feature and browser tests

**Acceptance checks**
- Every displayed editable field can be changed, saved, and reloaded.
- Existing fields are preserved on partial updates.
- Invalid enum/range values fail validation.
- The header reflects the persisted values after success.

## Slice 1.6 — Add a real UI write path for Unity Cup state (R2-14)

**Outcome:** Unity Cup team identity and counters can be recorded from the scenario panel.

**Inspect first**
- `resources/js/components/scenario/UnityCupPanel.vue`
- `config/scenarios.php`
- `TrainingRun` fields and casts
- `StoreTrainingRunRequest` and `runs.update`
- `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest`, scenario metadata tests

**Implement**
1. Add an explicit edit mode or inline form to the Unity Cup panel.
2. Expose only existing validated fields: team name, motto, league placement, preseason wins, Unity Trainings count, Spirit Bursts count, Extreme Bursts count, facility level, and spirit-gauge state where supported by the current schema.
3. Derive scenario labels from the server/config; do not hardcode scenario names in page-level layout.
4. Hide or disable Unity Cup-only controls for other scenarios.
5. Persist through the existing validated `runs.update` path unless repository architecture proves a dedicated action is required.
6. Validate ranges using actual request/domain constraints; do not infer gameplay limits from the screenshot corpus alone.
7. Keep the panel readable in view mode and prevent accidental edits while switching scenarios.

**Expected code changes**
- `UnityCupPanel.vue`
- Cockpit prop/form mapping
- Request/controller only where necessary
- Feature tests for persistence, validation, and scenario isolation
- Browser test for edit/save/reload

**Acceptance checks**
- Each exposed field round-trips through persistence.
- Non-Unity-Cup runs do not show Unity Cup inputs.
- Existing team/counter read-only rendering remains correct.
- Scenario names and labels still originate from config/server props.

---

# Phase 2 — Improve run planning and multi-run clarity

## Slice 2.1 — Reduce Race Planner noise without hiding required races (R2-12)

**Outcome:** Data-poor race entries no longer dominate the page, while all required decisions remain reachable.

**Inspect first**
- Race planner controller and `RacePlanner.vue`
- `RacePlannerTest`
- Current race catalog completeness and `scenario_races`/`race_catalog_slots`
- Existing product requirements for obligations, races ahead, reached-unrecorded races, and rivals

**Implement**
1. Keep required/obligatory races prominent.
2. Group placeholder races with missing catalog details into a compact disclosure or secondary section rather than repeating identical `N/A` metadata across the main page.
3. Keep each placeholder accessible and identifiable; do not silently delete, merge, or reorder meaningful race entries.
4. Preserve the existing category groupings and race-record write path.
5. Make the collapsed count and expanded content understandable to screen readers.
6. Avoid inventing race names, distances, prizes, or conditions to fill catalog gaps.

**Expected code changes**
- `RacePlanner.vue`
- Controller/view-model only if grouping metadata is needed
- Focused feature tests and Playwright expand/collapse/navigation coverage

**Acceptance checks**
- Required races remain visible and actionable.
- Placeholder detail does not overwhelm the primary decision view.
- Expanding reveals the same underlying races and actions.
- No race record is dropped or altered by the presentation change.

## Slice 2.2 — Clarify Dashboard context when multiple careers are active

**Outcome:** The Dashboard communicates that it highlights the newest active career while the Careers list remains the complete source of truth.

**Inspect first**
- `DashboardController`
- Dashboard Vue page
- `runs.index` page and existing dashboard tests

**Implement**
1. Keep the newest active career as the primary card if that is the accepted product behavior.
2. Add a concise, visible indication when more than one active career exists, with a count and link to Careers.
3. If the controller currently queries only one run, add a count-only query without loading a large set of full run models.
4. Ensure recent Veterans and quick actions remain visually distinct from active-run context.
5. Do not change the product into a single-active-run system or add automatic run switching.

**Expected code changes**
- `DashboardController`
- Dashboard Vue page
- Feature test for zero, one, and multiple active careers
- Browser assertion for the multi-run notice

**Acceptance checks**
- Multiple active careers are not implied to be impossible.
- The featured run remains clearly identified.
- The count and Careers link are correct.
- The single-active-run layout remains uncluttered.

## Slice 2.3 — Make turn corrections safer and more explicit

**Outcome:** Editing an earlier turn is predictable and cannot be confused with recording the next turn.

**Inspect first**
- Cockpit correction disclosure
- Guided Training entry page
- `StoreTurnEntryRequest`, turn update/store controller methods
- Existing arbitrary-turn correction tests and `turn_entries` uniqueness rules

**Implement**
1. Label the correction action with the selected turn/position and distinguish it from “Record next turn.”
2. Show a concise before-save summary when the user is editing an existing turn.
3. Preserve the existing arbitrary-turn correction capability.
4. Use validation errors adjacent to fields and preserve entered values after a failed save.
5. Prevent accidental duplicate submissions using the established loading/disabled pattern.
6. Do not change career-position derivation or snapshot/import semantics.

**Expected code changes**
- Cockpit correction component/form
- Guided Training page if needed for consistent labels
- Feature tests for correction targeting and duplicate handling
- Browser test for editing an earlier turn and confirming the saved position

**Acceptance checks**
- The user can tell whether they are recording a new turn or correcting an existing one.
- A correction updates the intended turn only.
- Validation failure preserves the form state.
- Snapshot/imported positions continue to display correctly.

---

# Phase 3 — Complete scenario-specific recording experiences

These slices complete the user-facing recording paths for the four Global scenarios. They must use existing stored state and verified rules; they are not permission to build an unverified game simulator.

## Slice 3.1 — Complete Trackblazer state editing

**Outcome:** The Trackblazer panel exposes the already-modelled grade-point and shop-coin state in a usable recording workflow.

**Inspect first**
- `config/scenarios.php`
- Trackblazer panel component and controller props
- Existing `TrainingRun` fields, validation, and `scenario_slots`
- Relevant scenario tests and `docs/research-scratch/screenshot-registry/findings.md` if present in the live tree

**Implement**
1. Make grade-point period, current grade points, and shop coins editable wherever the current schema and request contract support them.
2. Present grade-point objectives and epithet routes using existing catalog/config data only.
3. Make the Pro Shop entry point accessible from the Trackblazer panel if the current screen contract supports it; show available catalog items and known costs/effects without fabricating inventory.
4. Preserve a clear distinction between current recorded values and goals/targets.
5. If an item purchase/consumption model does not exist, do not fake a transaction. Implement a manual record/edit action only where the domain already supports it; otherwise keep the catalog informational.
6. Hide Trackblazer-specific controls on unrelated scenarios.

**Expected code changes**
- Trackblazer panel component
- Cockpit/controller prop mapping
- Existing request/model paths as needed
- Trackblazer feature and browser tests

**Acceptance checks**
- Grade points and shop coins save and reload correctly.
- Current values are not confused with targets.
- Catalog information comes from existing sources.
- No unsupported purchase simulation or invented epithet rule is introduced.

## Slice 3.2 — Complete Our Grand Concert state editing with evidence-safe behavior

**Outcome:** Our Grand Concert has a practical state-recording UI for existing supported fields, without pretending undocumented concert mechanics are implemented.

**Inspect first**
- Our Grand Concert panel and `config/scenarios.php`
- Existing schema/request support for turn, energy, fans, goals, and finale-title state
- KI-82 owner decision/status in the live issue register
- Screenshot-registry findings and current in-game captures, if available

**Implement**
1. Expose the scenario state that is already represented in the schema and approved by the current screen contract.
2. Use config/server-supplied labels for the scenario panel and its widgets.
3. Keep career goals readable and recordable where the model supports them.
4. Resolve the finale-title behavior only from an explicit product-owner ruling or authoritative requirement. Do not invent a new title transition.
5. Where Performance Points, concert deadlines, lessons, support bonds, or risk indicators lack a verified model/rule contract, do not display fabricated calculations. Add only fields and status recording that are explicitly supported.
6. Make unknown/unrecorded values visibly distinct from zero.
7. Hide scenario-specific inputs on the other three scenarios.

**Expected code changes**
- Our Grand Concert panel
- Relevant controller/props and validated update path
- Tests for persisted state, unknown-vs-zero rendering, and scenario isolation
- Browser test for the panel's main record/edit journey

**Acceptance checks**
- Existing supported values can be recorded and revisited.
- Missing data does not appear as zero.
- No unsupported concert mechanics or computed recommendations are shown.
- Finale-title behavior matches the owner's explicit ruling.

## Slice 3.3 — Make scenario panel composition and edit affordances consistent

**Outcome:** All four scenarios use a consistent shell while retaining their distinct scenario-specific state.

**Inspect first**
- `components/scenario/ScenarioPanel.vue`
- Ura Finale, Unity Cup, Trackblazer, and Our Grand Concert panel components
- `config/scenarios.php`
- Existing scenario metadata and browser tests

**Implement**
1. Keep one shared panel shell for title, summary, status, actions, and responsive behavior.
2. Keep scenario-specific details in the registered panel renderers rather than adding scenario-name conditionals throughout Cockpit.vue.
3. Use the labels passed from config/server as required props; do not add hardcoded scenario-name defaults.
4. Normalize edit/view mode, save feedback, validation errors, and empty-state presentation across panels.
5. Ensure panels remain useful at mobile widths and do not overflow long names or counters.
6. Preserve URA Finale as the baseline composition and Unity Cup as the only scenario with the `Team` rule family.

**Expected code changes**
- Shared `ScenarioPanel.vue`
- Individual scenario panel components only where required
- Scenario config/prop mapping only if a real runtime gap exists
- Component/feature/browser tests

**Acceptance checks**
- Each scenario uses the shared shell and its own renderer.
- Server-configured labels remain the only source of scenario names in the layout path.
- No panel leaks fields from another scenario.
- All four panels pass narrow-screen and empty-state checks.

---

# Phase 4 — Finish the career lifecycle and supporting surfaces

## Slice 4.1 — Make Result → Save Veteran a clear, recoverable flow

**Outcome:** A finished run can be reviewed and filed as a Veteran with unambiguous state and duplicate-safe behavior.

**Inspect first**
- Result page/controller
- Save Veteran page/controller and `RecordVeteran`
- Veteran uniqueness/idempotency behavior
- Existing result and Veteran tests

**Implement**
1. Make the Result page's primary action reflect whether the run has already been saved as a Veteran.
2. If already filed, link to the existing Veteran rather than inviting a duplicate save.
3. Preserve the finished run as a run record; saving a Veteran must not destroy or transform it.
4. Show validation or domain conflicts inline and retain context after failure.
5. Ensure abandoned runs cannot be filed as finished Veterans unless existing product rules explicitly allow it.
6. Keep the final stat/build summary consistent with the Cockpit's saved state.

**Expected code changes**
- Result Vue page
- Save Veteran Vue page/component
- Controller/action only if idempotent state is not already exposed
- Feature and browser tests

**Acceptance checks**
- First save creates or records the Veteran according to existing rules.
- Repeat visits do not produce duplicate Veterans.
- The user can navigate from Result to Veteran detail.
- Run and Veteran records remain independently accessible.

## Slice 4.2 — Improve Veterans and Legacy Lab comparison usability without changing domain semantics

**Outcome:** Trainers can compare recorded career outcomes and ancestry without implying that the Legacy Lab calculates inheritance.

**Inspect first**
- Veterans index/detail/compare pages
- Legacy index/builder/compare pages
- `Veteran`, `LegacySelectionPayload`, and comparison controllers
- Existing comparison tests and `SCREEN_SPEC.md`

**Implement**
1. Make compared values align by label and unit, with unavailable values clearly marked.
2. Provide an empty state and a clear selection path when fewer than two records are available to compare.
3. Keep Veterans as finished-career records and Legacy Lab as recorded ancestry.
4. Never add calculated affinity, inheritance payout, star-roll odds, or recommended parent combinations.
5. Preserve links from a Legacy entry to the underlying run/Veteran where the relationship exists.
6. Ensure comparison tables remain usable on narrow screens (responsive rows/cards or controlled horizontal overflow).

**Expected code changes**
- Veteran comparison page/component
- Legacy comparison page/component
- Controller props only if comparison rows lack stable labels/units
- Feature and browser tests

**Acceptance checks**
- Same metrics align across compared records.
- Missing data is not presented as zero.
- Empty and one-record states have a useful next action.
- UI copy does not imply computed inheritance advice.

## Slice 4.3 — Finish catalog-to-career entry points and retire confusing transitional paths

**Outcome:** A Trainer can start a career from a catalog detail page through the primary wizard, while any retained legacy create route is clearly transitional.

**Inspect first**
- Catalog detail page and its start-career link
- `runs.create`/`runs.store` route usage
- Scenario/trainee wizard draft flow
- Existing navigation tests

**Implement**
1. Make the primary catalog action begin the six-step wizard with the selected trainee carried forward using the established draft contract.
2. Ensure navigating backward/forward in the wizard does not lose the selected trainee.
3. Keep the legacy one-combobox form only if current product requirements still require it; otherwise remove its inbound links before removing the route.
4. Do not create a run before Preflight on the wizard path.
5. Do not mix wizard draft state with saved run deck state.

**Expected code changes**
- Catalog detail Vue page
- Trainee selection/setup controller or draft handling only if necessary
- Feature/browser tests for selected-trainee handoff and no early run creation

**Acceptance checks**
- Catalog action opens the wizard with the chosen trainee selected.
- Run count does not increase before Preflight.
- Wizard draft survives normal step navigation.
- Legacy route remains reachable only through intentional, tested paths if retained.

---

# Phase 5 — Accessibility, responsive behavior, and interaction polish

## Slice 5.1 — Fix the invalid Inertia progress-bar accessibility contract (KI-63)

**Outcome:** Navigation progress is exposed with valid ARIA semantics and does not create an axe WCAG 2.2 A 4.1.2 violation.

**Inspect first**
- App shell and Inertia NProgress integration
- Current `role="bar"` rendering/configuration
- Existing accessibility/browser checks

**Implement**
1. Correct or wrap the progress indicator so its semantics use a valid role and accessible label.
2. Avoid announcing every navigation detail excessively; use an appropriate status/progress announcement pattern.
3. Respect reduced-motion preferences if the current implementation animates the indicator.
4. Add a regression assertion that checks the rendered semantics, not only a source string.

**Expected code changes**
- Progress component/integration and its configuration
- Browser/accessibility test

**Acceptance checks**
- No invalid ARIA role is emitted.
- Progress remains visible during navigation.
- Keyboard and screen-reader users receive meaningful status without duplicate noisy announcements.

## Slice 5.2 — Standardize loading, validation, empty, and success states on touched flows

**Outcome:** The newly completed flows behave consistently rather than each inventing its own feedback.

**Implement**
1. Reuse the existing design system and form conventions for busy/disabled submit states.
2. Keep server validation errors adjacent to the relevant input and announce form-level failures accessibly.
3. Preserve user input after failed validation.
4. Provide explicit empty states for the Skills Planner, race details, comparison screens, and scenario panels.
5. Use success messaging that says whether a value was “saved,” “configured,” or “recorded” accurately; do not claim a target is achieved merely because it was saved.
6. Do not add gratuitous animation or new visual primitives when existing components cover the interaction.

**Expected code changes**
- Only the Vue forms/components touched by earlier phases
- Focused feature/browser tests for each changed interaction

**Acceptance checks**
- Submit buttons cannot trigger duplicate writes while pending.
- Validation messages are discoverable and tied to fields.
- Success copy reflects the actual operation.
- Empty/loading/error/success states remain visually and semantically distinct.

## Slice 5.3 — Verify mobile and compact layouts for the main career loop

**Outcome:** The primary end-to-end journey works at narrow viewport sizes.

**Implement**
1. Test Dashboard, New Career wizard, Cockpit, Training entry, Race Planner, Skills Planner, scenario panels, Result, Veteran detail, and comparison screens at mobile width.
2. Fix horizontal overflow, clipped actions, inaccessible disclosures, and form controls too narrow to use.
3. Preserve the existing mobile bottom navigation and More disclosure.
4. Do not change the navigation count or information architecture without an explicit product decision.
5. Add Playwright viewport assertions for the most failure-prone screens.

**Expected code changes**
- Responsive classes/layouts in affected Vue pages/components
- Browser tests at representative desktop and mobile viewport sizes

**Acceptance checks**
- No primary action is clipped or unreachable.
- Tables have a usable narrow-screen strategy.
- The six-step wizard remains navigable.
- Cockpit and scenario panels remain readable without horizontal page overflow.

---

# Phase 6 — End-to-end integration and regression closure

## Slice 6.1 — Exercise the complete new-career journey

**Outcome:** A Trainer can create a run, edit its identity and scenario state, record a turn, plan a race/skill, finish it, and save a Veteran.

**Implement**
1. Add or complete a Playwright journey using a session-private port and scratch database.
2. Cover Scenario → Trainee → Target → Legacy → Deck → Preflight.
3. Assert no run exists before Preflight and exactly one intended run exists afterward.
4. Continue into Cockpit; edit identity/scenario fields; record a turn; open the run-scoped deck; visit Race and Skills planners.
5. Finish the run through the supported status/result flow and save a Veteran.
6. Assert the Veteran appears in the library and can be opened.
7. Keep test fixtures deterministic; do not rely on the development SQLite database or live remote fetches.

**Expected code changes**
- Playwright specs and reusable browser fixtures/helpers
- Only production-code fixes revealed by actual failures

**Acceptance checks**
- The entire journey passes on a clean scratch database.
- Assertions cover persisted values after navigation/reload.
- The test does not depend on pre-existing local data.
- Existing wizard, deck ownership, and lifecycle tests still pass.

## Slice 6.2 — Add scenario-isolation and update-preservation regression coverage

**Outcome:** Scenario-specific controls cannot leak data across scenarios, and partial updates preserve unrelated fields.

**Implement**
1. Add parameterized tests for all four Global scenarios.
2. Confirm Unity Cup fields are absent/inactive for non-Unity-Cup runs.
3. Confirm Trackblazer fields are absent/inactive for non-Trackblazer runs.
4. Confirm Our Grand Concert fields are absent/inactive for non-Our-Grand-Concert runs.
5. Confirm baseline URA controls remain available without scenario-specific fields.
6. Test partial `runs.update` requests preserve fields omitted from the payload.
7. Test unknown/null/zero values separately wherever the schema distinguishes them.

**Expected code changes**
- Pest feature tests
- Browser scenario-selection/edit tests
- Production fixes only if the tests expose real defects

**Acceptance checks**
- No scenario-specific write path is exposed in the wrong scenario.
- Omitted fields remain unchanged.
- Null, zero, and unknown have intentional behavior.
- Config-driven labels and scenario composition are preserved.

## Slice 6.3 — Run final quality gates safely

**Outcome:** All changed behavior has focused regression coverage and no unrelated work is disturbed.

**Implement**
1. Run focused Pest tests for each completed slice.
2. Run the related feature-test groups and static analysis allowed by the current repository rules.
3. Run Playwright using a session-private port and scratch DB.
4. Check formatting only on authorized changed files; do not run `pint --dirty` in the shared worktree.
5. Inspect `git diff --check`, test output, and `git status`.
6. Fix failures caused by the changes. Do not broaden scope to unrelated pre-existing failures without authorization.

**Expected tangible changes**
- Code/test fixes for regressions actually found
- No documentation-only “completion” changes

**Acceptance checks**
- All focused tests pass.
- Relevant browser journeys pass.
- No dev database is mutated by tests.
- No unrelated files are overwritten, staged, or committed.

---

# Deferred work that is intentionally not part of this implementation sequence

These are excluded because they require new evidence, product-owner decisions, or are already shipped. Do not silently fold them into a slice.

- **New screenshot capture for Trackblazer and Our Grand Concert:** the current screenshot corpus has no captures for these scenarios. Capture source evidence before implementing unverified detailed mechanics.
- **Concert mechanic simulation or recommendations:** do not implement calculations for Performance Points, concert deadlines, lessons, bonds, or risks until an authoritative rules contract and evidence exist.
- **Legacy Lab inheritance computation:** explicitly outside its record-only contract.
- **Authentication, multi-user support, public hosting/deployment, and remote storage:** project non-goals.
- **A broad visual redesign of already-shipped Dashboard, catalog, Database hub, Review, Preferences, and navigation:** do not repeat completed 2.0 work unless a concrete regression is demonstrated.
- **Rebuilding the retired run-record screen:** `/training-runs/{run}` redirects to Cockpit by design.
- **Rebuilding the six-step wizard, Snapshot flow, CSV import, Veteran library, or existing scenario-panel registration:** these are shipped surfaces. Extend them only where a slice above identifies a specific missing behavior.
- **Screenshot registry maintenance as a product feature:** the registry is research tooling, not a Trainer Desk runtime feature.
- **Documentation/report-only tasks:** they do not satisfy any implementation slice.

---

# Completion definition

The remaining implementation is complete when:

- Stat target/ceiling meaning is clear across screens.
- The Skills Planner has a correct empty-priority state.
- The run-scoped deck builder is discoverable and the wizard/run deck paths stay separate.
- Energy band, identity fields, and Unity Cup state have validated UI write paths where supported by the schema.
- Race Planner density is improved without dropping race records.
- Dashboard communicates multi-run context accurately.
- Trackblazer and Our Grand Concert expose supported, editable recorded state without invented mechanics.
- Result → Save Veteran and comparison flows are unambiguous and record-only boundaries are preserved.
- The primary career journey passes through a deterministic browser test.
- Scenario isolation, partial-update preservation, accessibility, and mobile behavior are covered by regression tests.
- All touched code is tested, the diff is scoped, and no unrelated work has been overwritten.

**Execution order:** Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5 → Phase 6. A later slice may be pulled forward only when it is a direct dependency of an earlier slice. If inspection shows a slice is already complete, verify it and skip it rather than reimplementing it.
