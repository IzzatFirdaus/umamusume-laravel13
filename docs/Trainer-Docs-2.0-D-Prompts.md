I read SKILL.md. It lists 115 global and 39 project-local skills, and I mapped the useful ones to each slice. The shared context now carries a baseline skill set, a skill precedence rule, and a skills-used report line. Every prompt gets its own add-on skills, each with the reason it applies.

Two choices to know about:

* I only used skills that fit the repo's constraints. I left out `pick-ui-library` (no new packages), the `design-taste` / `high-end-visual-design` family (they push redesigns), and the animation skills (the MOTION dial is 1).
* The shared context now says that if a skill's default advice conflicts with AGENTS.md, an ADR or the plan, the repo wins. `tailwindcss-development` suggests `dark:` variants, for example, and the repo forbids them.

==============================================================  
SHARED CONTEXT (paste before every prompt)
===========================================================================================================

You are working in the Trainer Desk 2.0 repo (Laravel 13, Inertia v3, Vue 3, TypeScript, Tailwind v4, Pest 4, Playwright). You are building ONE slice of Phase D of the Trainer Desk 2.0 frontend.

READ FIRST, in this order:

1. AGENTS.md
2. docs/proposals/frontend-development-plan.md: Global Constraints, sections 2, 3, 8, 12 and 13, plus your slice's row
3. docs/proposals/screen-spec-2.0.md: your SCREEN-nnn section AND the "Product direction corrections" table
4. docs/proposals/design-2.0.md: the sections named in your prompt
5. DESIGN.md and SCREEN_SPEC.md

AUTHORITY ORDER: landed ADRs, then AGENTS.md, then the development plan, then the two design briefs. The briefs are design intent. The plan is the authorization. Skills (below) are METHOD only. If a skill's default advice conflicts with any repo document (for example a skill suggesting dark: variants, a new package, an extra animation, a redesign, a placeholder image, or a recommendation score), the repo wins. Say so in your report and move on.

SKILLS: how to use them  
Load each skill by name at the phase shown. If a skill is not installed, say so in your report and apply its intent anyway.

Baseline skills (every slice):

* using-agent-skills: governs how you find and invoke the rest.
* planning-and-task-breakdown + spec-driven-development: PLAN PHASE. Turn your slice into an ordered task list and a short written spec (files, props contract, tests, open questions) before any code.
* incremental-implementation + test-driven-development: BUILD PHASE. Thin vertical steps: failing props test, controller, page, passing test, Playwright spec.
* laravel-best-practices + testing-best-practices: controllers, Form Requests, query shape, Pest structure, naming, isolation.
* frontend-ui-engineering + tailwindcss-development: Vue components, layout, state, WCAG. Tokens only, never dark: and never zinc-*.
* antislop, antislop-ui, antislop-human, antislop-copywriting, antislop-layoutmobile: apply to every string and layout you write (no filler copy, honest empty states, keyboard and focus, tap targets, no horizontal overflow). antislop-code for comments (keep only the ones that carry information).
* constraint-driven-development: the repo's gates (G-19, G-33, G-47, DesignTokensTest) and CONSTRAINTS.md are the contract. Check your diff against them.
* doubt-driven-development: REVIEW PHASE. For every non-trivial decision you made (persistence mechanism, thresholds, anything you inferred), get an adversarial fresh-context challenge before it stands, and report the result.
* code-review-and-quality: final self-review of the diff before hand-off. The diff must contain no unrelated file.
* browser-testing-with-devtools: VERIFY PHASE. Check console errors, focus order, 320px reflow, 200% zoom and reduced-motion in a real browser alongside the Playwright spec.

Optional if installed: freeze (restrict edits to resources/js, app/Http, routes, tests, config as needed for your slice), careful (before any destructive command), design-review (visual QA pass on flagship screens).

HARD RULES

* Copy the page pattern of resources/js/pages/Catalog/Index.vue: AppLayout, <Head title>, #title slot, useForm with @submit.prevent, router.get with preserveState and preserveScroll. Do not invent a second pattern.
* Each page gets a controller method returning Inertia::render('Page', $props), a route, and a Pest test using assertInertia. Map models to explicit arrays. Never pass Eloquent models. No uses() or withoutVite() in test files.
* Writes keep their existing Form Requests and routes. The Vue form only posts.
* Tokens only: no zinc-* utilities, no dark: outside the theme override block.
* Never branch on a scenario name. Read config/scenarios.php through props (panels.* flags, widgets[]).
* Unrecorded or unsourced values render as "N/A" with a title attribute explaining why. Never a default, never a dash, never an invented number.
* Trust vocabulary: every computed figure carries Confirmed / Calculated / Estimated / Unknown. ProvenanceBadge.vue is built in D4. Before D4, show only entered or stored values and named absences.
* HELD computation stays held: no race win probability or readiness band, no inheritance computation or "expected inheritance", no per-training stat yields or failure %, no shop recommendation, no Grand Concert mechanics, no numeric confidence or score.
* Global terminology only: Support Cards, Scouts, Veteran Umamusume, Pal, Wit, Sparks. Print "Trackblazer", not "Twinkle Star Climax". Write "2026-07-01 Global rework".
* WCAG 2.2 AA: keyboard path, visible focus, h-11 / min-h-11 (44px) targets, labels, aria-describedby on errors, lang="ja" on Japanese names, prefers-reduced-motion respected, no drag-only interaction (always give a button alternative), sticky UI must not cover focus. Never show state through colour alone.
* Every page has loading, empty and error states. An empty state says what is missing, why it matters, and what to do.
* Wizard slices (D2 to D7) carry entered values forward and never re-ask for them (WCAG 3.3.7).
* Navigation: AppLayout already has nine destinations, two of them named absences (to: null). Miller's Law says at most eight. Replace a named absence instead of adding an entry. If a slice would exceed eight, stop and report.
* Wizard persistence: build_target lives on training_runs and runs.deck.sync is run-scoped, so the D2 to D7 wizard needs a draft/planning run or an equivalent. Decide the smallest mechanism using existing TrainingRun statuses. Do not invent an enum value, column or migration without owner approval. Report the decision.
* No new npm or composer package. No vue-tsc. Do not file an ADR; draft the text in your report if one seems needed.
* Add or extend the SCR-CAR-* (or matching area) row in SCREEN_SPEC.md section 2 with the empty / loading / error state table.

PROCESS: plan phase (skills above), then build phase with TDD, then Playwright spec in tests/browser/<screen>.spec.ts (rendered copy, 44px sweep, keyboard path, axe A + AA if the dependency already exists), then review and verify phases.

HAND-OFF SEQUENCE, report real output and never claim an unrun gate passed: targeted tests, php artisan test --compact, vendor/bin/pint --dirty --format agent, vendor/bin/phpstan analyse --no-progress --memory-limit=1G, npm run typecheck, npm run build, npm run test:browser, composer lore + composer lore-code with a ruling per hit.

FINAL REPORT must include: files changed, props contract, every deviation from the plan and why, open questions for the owner, gate output, and a "Skills used" list with one line each (what it changed in your work) plus any skill you skipped and why.

==============================================================  
PROMPT D1: Dashboard enrichment (SCREEN-001)
=============================================================================================================

TASK: Enrich resources/js/pages/Dashboard.vue and its controller props. Depends on C1 (BuildTarget) and C3 (veterans).

SKILLS (add to baseline):

* performance-optimization: the dashboard aggregates several sources. Eager-load, avoid N+1, cap recent lists, and use Cache::remember only where the catalog pattern already does.
* api-and-interface-design: define the dashboard props contract (activeCareer, recentVeterans, dataStatus) once, typed in resources/js/types.ts.
* antislop-copywriting: the empty states and the DataStatus line must be specific, not generic ("Welcome back" filler is fine only if the spec has it).

READ: screen-spec-2.0 SCREEN-001 plus the data-status note under "Product direction corrections"; design-2.0 sections 4, 29, 39, 48; plan section 8 row D1; plan section 13 rows "Paradox of the Active User" and "Von Restorff".

BUILD:

* ActiveCareerCard: the active run's trainee name, scenario label, year and half, turn, Energy, and the scenario's primary resource (read from config widgets[], not by scenario name). Primary action "Resume Career" links to the run. Energy reads "N/A" with a title when no turn has recorded it.
* Quick actions: New Career, Legacy Lab, Support Cards. A destination whose slice has not landed is a named absence (to: null), not a dead link.
* Recent Veterans from the C3 list action. Recent Builds only if a real source exists; otherwise a named absence.
* Legacy-goal gaps only if C1 and C3 give a real comparison. Otherwise omit the panel and say why in the report.
* DataStatus: a subtle "GLOBAL DATA ● Current" badge with the verified date from config/scenarios.php. Glyph plus text. The ruleset line reads "N/A" with a title because app.ruleset is null.
* Empty state when no active career: "No active career. Start a new training run." plus what / why / what-to-do per design-2.0 section 29.
* No onboarding wall: the page is usable on first load.

ACCEPTANCE: every panel is real data or a named absence with no invented counts. Props test covers active, no-active and no-veterans cases. Playwright covers the empty state, Resume link, DataStatus text and 44px sweep. lang="ja" on any Japanese name.

==============================================================  
PROMPT D2: Scenario Selection + SetupLayout (SCREEN-002)
=========================================================================================================================

TASK: Create layouts/SetupLayout.vue and pages/Career/ScenarioSelect.vue. This is the first screen of the setup wizard.

SKILLS (add to baseline):

* spec-driven-development: write the wizard contract first (steps, carried state, what each step reads and writes). D3 to D7 will build on it.
* doubt-driven-development: the wizard-persistence decision is the riskiest call in this slice. Challenge it adversarially (what breaks on refresh, back button, two tabs, abandoned drafts) before it stands.
* constraint-driven-development: G-33 (no scenario-name branching) is the central gate here.
* antislop-ui + antislop-layoutmobile: four cards must be distinct by content, not decoration; no overflow at 320px.
* design-review (optional, global): one visual QA pass on the card grid.

READ: screen-spec-2.0 SCREEN-002 and its correction row (four scenarios, no star ratings); design-2.0 sections 13, 25, 48; plan sections 8 (D2) and 9 (E6); config/scenarios.php, especially the our_grand_concert entry with documented => false.

BUILD:

* SetupLayout: minimal wizard shell with a step indicator ("Step n of 6"), skip link, a labelled nav, and the same landmarks as AppLayout. Do not add CareerLayout yet.
* ScenarioSelect: four cards generated by looping the scenarios the controller resolves from config/scenarios.php. Each shows name, short description, what it optimizes (primary / secondary / training complexity as text, NO star ratings or difficulty), primary mechanic, availability (live_on_global date), and ruleset version rendered as "N/A" with a title.
* Grand Concert card: a "PARTIALLY DOCUMENTED" badge with glyph and text. Its description says only baseline tracking is available. Do not write mechanics the config does not hold.
* Primary action per card: "Select Scenario". Use a radio-group or card-button pattern with a full keyboard path and aria-checked / aria-pressed. Carry the selection to D3 via the persistence decision.
* Adding a fifth scenario must be one config entry and zero component edits. Prove it with a test that iterates the config.

ACCEPTANCE: grep the diff for scenario-name branching (none allowed). Four cards render. The Grand Concert baseline state is visible. Selection survives navigation to the next step and back.

==============================================================  
PROMPT D3: Trainee Selection + Trainee Profile (SCREEN-003 / SCREEN-004)
=========================================================================================================================================

TASK: Create pages/Career/TraineeSelect.vue and pages/Career/TraineeProfile.vue. Depends on D2.

SKILLS (add to baseline):

* code-simplification: reuse, do not copy. Extract shared bodies from Catalog/Index.vue and Catalog/Show.vue into components if both screens need them. Behaviour of the catalog pages must not change.
* performance-optimization: the roster query is the heaviest in the wizard. Keep the catalog's cardScope() and PageSize::clamp, avoid N+1 on aptitudes and cards.
* antislop-layoutmobile: card grids and the filter bar at 320px.
* source-driven-development: ground each filter and profile section in a real column or relation. Cite the model or migration in your slice plan.

READ: screen-spec-2.0 SCREEN-003 and SCREEN-004; design-2.0 sections 17, 42, 45a, 46; existing pages/Catalog/Index.vue and Show.vue, components/AptitudeGrid.vue, FormTabs.vue, ArtworkSlot.vue, SkillRow.vue, TraineeCombobox.vue; CatalogController (cardScope() and the filter queries).

BUILD:

* TraineeSelect: reuse the catalog queries and Paginator pattern. Searchable, sortable roster. Filters only where real columns or relations exist (Surface, Distance, Running style, Aptitude, Unique skill, Character). If Growth rate or Scenario suitability have no data, omit them and list the omission in the report.
* Trainee card: name (lang="ja" for the Japanese name), aptitude badges as letter plus text (S/A strong, B/C neutral, D/E weak, F/G very weak), "View Profile" and the primary "Select Trainee". ArtworkSlot follows 45a geometry; when absent it renders the text-only row with no placeholder.
* TraineeProfile sections: Basic (name, rarity, version, growth rates only if stored), Aptitudes, Skills (unique, starting, awakening, event, only lists the data holds), Career goals (only if stored), Build analysis. Build analysis shows sourced facts only. "Recommended stat distribution" and "useful inheritance" are unsourced, so render "N/A" with a title.
* Selecting stores the trainee in wizard state and goes to D4. "Back" keeps the chosen scenario.

ACCEPTANCE: components reused, not duplicated. Aptitude never colour-only. Props test covers filter behaviour. Playwright covers keyboard selection and the 44px sweep. Existing catalog specs still pass.

==============================================================  
PROMPT D4: Build Target + ProvenanceBadge (SCREEN-005)
=======================================================================================================================

TASK: Build components/ProvenanceBadge.vue and pages/Career/BuildTarget.vue. Depends on C1.

SKILLS (add to baseline):

* api-and-interface-design: ProvenanceBadge is a shared contract used by every later slice. Fix its prop type (state union, optional source), its accessible-name rule and its placement rule once, and export the type.
* security-and-hardening: server-side validation is the authority. Stat targets clamp through ScenarioCaps::forRun in the Form Request. The client clamp is a convenience. Check mass-assignment and that skill_priorities cannot carry unexpected keys.
* doubt-driven-development: challenge the purposes conflict and the missing risk-tolerance field before deciding how to report them.
* source-driven-development: ground payload shape in BuildTargetPayload, StoreBuildTargetRequest and ScenarioCaps.
* antislop-human: error messages tied to inputs, focus moves to the first error.

READ: screen-spec-2.0 SCREEN-005 and its Career Plan correction; design-2.0 sections 15, 20, 36, 49; plan C1 (section 7) and the D4 row; app/Models/Advisor/BuildTargetPayload, StoreBuildTargetRequest, ScenarioCaps::forRun.

BUILD:

* ProvenanceBadge.vue: the ONLY place the four glyphs live. States Confirmed / Calculated / Estimated / Unknown. Each has a distinct glyph plus visible text, 3:1 non-text contrast, an accessible name, and a title. It sits adjacent to the figure it labels.
* BuildTarget page headed "Your target" (never "The correct target"). Fields come from BuildTargetPayload only: purpose, distance, surface, style, five stat targets, ordered skill priorities.
* Purpose options come from the C1 enum (StoryClear, ChampionsMeeting, ParentFarming, SkillFarming). The screen spec lists six. Do NOT add values. Report the difference to the owner.
* Risk tolerance and per-skill Required / High / Optional / Ignore marks are in the screen spec but not in the C1 payload. Do not build them without owner approval; list them as open questions.
* Stat targets: numeric input plus a bar showing distance to target, each labelled. Show the cap beside the field and server errors tied to the input with aria-describedby.
* Skill priorities: ordered list with Move up / Move down buttons (no drag-only).
* Human-readable summary generated only from entered values, badge Calculated. If a field is empty the summary names the absence.
* Absent target: the page names the absence and does not default values.

ACCEPTANCE: props test round-trips the payload and the cap clamp. Playwright covers keyboard reorder, cap error text, and the badge's text plus accessible name. Grep proves no glyph is defined outside ProvenanceBadge.

==============================================================  
PROMPT D5: Legacy Lab, record-only (SCREEN-006)
================================================================================================================

TASK: Create pages/Legacy/Index.vue, Legacy/Builder.vue and Legacy/Compare.vue. Depends on C3. Record-only: pick, browse, compare.

SKILLS (add to baseline):

* doubt-driven-development: the line between "record" and "compute" is where this slice fails. Challenge every displayed number and label: is it entered, stored, or derived? Derived means it does not ship.
* source-driven-development: UMAMUSUME_REFERENCE.md section 1.5 (sparks, star-roll odds, six-node tree) and ADR-0010 / ADR-0020 section 3. Cite lines in your slice plan.
* code-simplification: one SparkChip component for every spark (Law of Similarity), one ancestry-node component.
* frontend-ui-engineering: the six-node graph needs a real accessible structure (a labelled list or tree with connector lines as decoration), not an SVG-only picture.
* antislop-ui: no decorative anime flourish; connectors and chips carry meaning.

READ: screen-spec-2.0 SCREEN-006 and its correction row; design-2.0 sections 18, 22, 29, 46 and the knowledge-grounding rows on Sparks and Affinity; plan D5 and section 3 (the Sparks row); ADR-0020 section 3; ADR-0010; LegacySelectionPayload.

BUILD:

* A persistent banner on every Legacy screen: "Record only. This screen stores and compares what you enter. It does not compute inheritance." Glyph plus text.
* Index: browse recorded veterans (C3 list action). Filters: spark type, distance, surface, style, scenario, rating. Offer only what C3 supports.
* Builder: a six-node ancestry graph (Trainee on top; Parent A and Parent B; four grandparents A1, A2, B1, B2) joined by visible connector lines. Assigning a veteran to a node is a button flow ("Assign to Parent A") with a full keyboard path, no drag. Each node shows recorded Blue / Pink / Green / White sparks via the shared SparkChip (colour AND label AND star count).
* Spark probabilities: show one only if the repo already holds a sourced star-roll table (reference section 1.5), labelled Estimated. Otherwise "N/A" with a title. Never sum, aggregate, or show "expected inheritance", payout totals or a recommended combination. Affinity is not computed: render "N/A" with a title.
* Compare: up to four veterans side by side, identical properties aligned in rows (design-2.0 section 46), recorded values only.
* Primary action "Confirm Inheritance", saving via the existing LegacySelectionPayload path.

ACCEPTANCE: grep shows no inheritance, affinity or optimization computation. Props test covers filters and the six-node payload. Playwright covers keyboard assignment, the banner and compare alignment.

==============================================================  
PROMPT D6: Support Deck Builder (SCREEN-007)
=============================================================================================================

TASK: Create pages/Support/Builder.vue over the existing runs.deck.sync write, plus components/support/{SupportSlot,DeckAnalysis}.vue as needed.

SKILLS (add to baseline):

* source-driven-development: every analysis number must trace to SupportCardEffects anchors (UMAMUSUME_REFERENCE.md section 1.4, SUPPORT-CARDS.md). Cite them.
* code-simplification + deprecation-and-migration: DeckPanel.vue and the run page's deck payload already exist. Extract and reuse. Do not delete anything without a zero-consumer grep, and keep runs.deck.sync behaviour (including the 2026-10-05 closed-slot change).
* performance-optimization: the card picker lists many cards. Keep the paginated query shape of SupportCardController.
* doubt-driven-development: challenge each deck-analysis category: is it derivable, or is it a score in disguise?
* frontend-ui-engineering: slot replacement is a stateful keyboard interaction. Define focus return after a replacement.

READ: screen-spec-2.0 SCREEN-007 and its correction row; design-2.0 sections 12, 17, 33, 42, 45a; plan D6; components/DeckPanel.vue, the run deck payload in TrainingRunController, SupportCardEffects, pages/SupportCards/Index.vue and Show.vue.

BUILD:

* Six slots, each with an ownership flag OWNED / RENTED as a labelled toggle with text. Do not assume "five owned plus one borrowed".
* Seven support types as one typed list: Speed, Stamina, Power, Guts, Wit, Pal, Group. Type shown as text plus icon.
* Scenario Link: DERIVED from scenario plus character at render time, never stored on the card. If the repo cannot derive it, "N/A" with a title.
* Card picker with filters only where data exists (type, rarity, level, limit break, skill, training bonus, race bonus, scenario compatibility). Keyboard slot replacement, no drag.
* Deck analysis by category (training power, early run, race bonus, safety, events, skills). Compute a category only from stated SupportCardEffects anchor values, show the actual numbers with a Calculated badge. No composite score, and no bar unless the number is printed beside it. Strengths and weaknesses are plain text derived from those numbers. "Recommended replacement" is NOT built: render a named absence.
* Primary action "Confirm Deck", posting through the existing runs.deck.sync and its Form Request.
* Image slots follow 45a. Absent artwork shows no placeholder.

ACCEPTANCE: props test covers six slots, the ownership flag, seven types. Playwright covers slot replacement by keyboard, the 44px sweep, and that analysis shows numbers with labels.

==============================================================  
PROMPT D7: Run Preflight / Career Contract (SCREEN-008)
========================================================================================================================

TASK: Create pages/Career/Preflight.vue. Depends on D4, D5, D6.

SKILLS (add to baseline):

* spec-driven-development: write the warning rules as a table in the slice plan (trigger, data read, reason line) before coding. Each rule must be testable.
* doubt-driven-development: challenge every threshold. The aptitude cut-off must come from design-2.0 section 17's tiers, not from you.
* security-and-hardening: the preview is not a trust boundary. "Start Career" must re-run the Form Requests server-side and refuse stale or tampered wizard state.
* api-and-interface-design: the contract prop that composes D4 to D6 should be one typed object, so Edit / Back round-trips are lossless.

READ: screen-spec-2.0 SCREEN-008 and its "Career Contract" correction; design-2.0 sections 13, 14, 21, 36; plan D7; design-2.0 section 17 for aptitude tiers.

BUILD:

* Sections Build (trainee, scenario, target, six legacy members), Support deck (six cards and the D6 analysis), Target (stats, skill priorities, race profile). Everything is read from carried wizard state. Nothing is re-asked.
* Warnings: only those derivable from entered data, each with a one-line reason citing the values. Derivable examples: a target distance, surface or style whose trainee aptitude is D or lower (state the threshold in your plan); a skill priority with no source on the trainee, deck or legacy; a missing wizard section. Do NOT ship "weak stamina plan", "poor support synergy" or "missing scenario requirement" unless derivable from sourced data. Report what you skipped.
* Each warning: glyph plus text. Warnings never block; the Trainer decides. Only hard server validation errors block.
* Actions: Back, Edit Legacy, Edit Deck, Edit Target (each returns to its step with values intact), and the primary "Start Career", posting to the existing run-creation route and redirecting to the run.
* Ruleset snapshot field: "N/A" with a title (app.ruleset is null).

ACCEPTANCE: props test covers the full contract, each warning trigger, and that Start Career creates a run. Playwright covers Edit then return with values intact, the warning text, and focus moving to the first error after a failed submit.

==============================================================  
PROMPT D8: Career Cockpit + CareerLayout (SCREEN-009)
======================================================================================================================

TASK: Create layouts/CareerLayout.vue, pages/Career/Cockpit.vue, and components/career/{CareerHeader,CareerStatePanel,ActionGrid,RecommendationCard,AdvisorRail}.vue. Depends on C2 and D7. This is the flagship screen.

SKILLS (add to baseline):

* impeccable: use its critique and polish passes on the finished cockpit, within the repo's tokens and MOTION dial 1. Its suggestions never override DESIGN.md.
* design-review (optional, global): visual hierarchy QA against design-2.0 section 4 (four levels). The recommendation card is the only accented element.
* code-simplification: reuse the 19 components in the Runs/Show.vue import graph (StatBand, EnergyGauge, MoodPill, ResourceStrip, GuidedStep, RaceCalendar). Wire them, do not rewrite them.
* performance-optimization: the cockpit is read on every turn. Keep the payload lean and avoid N+1 on turns and goals.
* antislop-layoutmobile + antislop-human: three breakpoints, no horizontal scroll for primary decisions, sticky-nav scroll-padding so focus is never obscured (2.4.11).
* doubt-driven-development: challenge that no field in the advisor props smuggles a score or numeric confidence.
* browser-testing-with-devtools: check 1280, 768 and 320 widths, 200% zoom and reduced motion in a real browser.

READ: screen-spec-2.0 SCREEN-009, sections 13, 29, 33, plus the "state machine" correction; design-2.0 sections 2, 4, 19, 23, 24, 36, 38, 40; plan D8 including the "Recommendation contract (D8)" paragraph; app/Services/Advisor/TrainerAdvisor and config/advisor.php; the existing pages/Runs/Show.vue and its 19 components.

BUILD:

* Do not delete Runs/Show.vue or its components. Decide the route in your slice plan (the spec says it descends from GET /training-runs/{run}) and keep all existing write routes.
* Layout: desktop three columns (Timeline about 20%, Current decision about 50%, Advisor about 30%); tablet two columns with the timeline as a horizontal strip; mobile single column in the order Header, Current State, Recommendation, Action, Scenario, Timeline.
* CareerHeader: scenario, year, month, turn, Energy, Mood, Fans, Skill Points, scenario resources from config widgets[].
* CareerStatePanel: five stats plus four meta values. Each stat shows "current / target" AND a bar. Missing values read "N/A".
* ActionGrid: Training, Race, Rest, Recreation, Scenario action, Event, Inheritance. Exactly one carries a "RECOMMENDED" marker. Entries link to D9 to D12 if landed, otherwise they are named absences.
* RecommendationCard / AdvisorRail props exactly: { action, band: 'AtOrAboveAdvisory'|'BelowAdvisory', reasons: string[], alternative: string|null, risk: string|null }. Band printed as text plus glyph, with reason lines. NO score and NO numeric confidence. It never auto-executes; the Trainer's action is always their own button. When Energy is absent show no band and no ranking and print "Recommendation unavailable because Energy has not been entered."
* Manual correction goes through the existing runs.turns.update. Do not build undo unless a route already exists.

ACCEPTANCE: props test for the advisor contract with Energy absent and present. Grep confirms no scenario-name branching. Playwright covers the three breakpoints, reflow with no horizontal scroll, the keyboard path through the grid, and the reduced-motion check.

==============================================================  
PROMPT D9: Training Decision detail (SCREEN-010)
=================================================================================================================

TASK: Create components/career/TrainingCard.vue and pages/Career/TrainingDetail.vue. Depends on D8.

SKILLS (add to baseline):

* source-driven-development: every term shown on a training card must trace to PROCESS-PLANS.md "trainer-advisor.md" sections 1, 3 and 5, config/advisor.php, or Trainer-entered data. Cite them.
* antislop-copywriting: write the N/A titles precisely ("No published source; excluded by the advisor spec, ADR-0001 section 3"), not generic "unavailable".
* doubt-driven-development: challenge every number on the card. If you cannot name its source, it becomes N/A.
* frontend-ui-engineering: progressive disclosure with aria-expanded and a stable focus order.

READ: screen-spec-2.0 SCREEN-010 and its correction row (yields are unsourced, render N/A); design-2.0 sections 12, 24, 35, 36; plan D9; PROCESS-PLANS.md section "trainer-advisor.md" sections 1, 3, 5; config/advisor.php.

BUILD:

* Option cards for Speed, Stamina, Power, Guts, Wit. Default summary shows only what is sourced or entered: Energy cost (config session_cost 17 to 28 rendered "E−28 … E−17"; Wit shows wit_cost 0 as "RiskNotMeasured"), supports entered for that training if recorded, bond gains if entered, and target deficit (target minus current, badge Calculated).
* DO NOT print "+62 Speed / +25 Power", "Failure: 2%" or any invented yield. Render "N/A" with a title citing the exclusion.
* Expandable details (disclosure button, aria-expanded): expected gains N/A, energy cost, failure probability N/A, support effects (stated anchor values only), bond gains, scenario effects, target impact.
* Actions: "Train" posts through the existing guided-turn write (runs.turns.store); "Inspect details" toggles the disclosure. The recommended option, if any, carries the single RECOMMENDED marker.
* Risk words (LOW / MEDIUM / HIGH RISK) only where a sourced rule supports them; otherwise "N/A".

ACCEPTANCE: grep proves no hard-coded yield or failure number. Props test covers option payloads. Playwright covers the disclosure by keyboard and the N/A titles.

==============================================================  
PROMPT D10: Race Decision (SCREEN-011)
=======================================================================================================

TASK: Create components/career/RaceCard.vue and pages/Career/RaceDecision.vue. Depends on D8.

SKILLS (add to baseline):

* source-driven-development: race facts come from RaceCatalogSlot and the calendar data only. Cite the model and migration.
* doubt-driven-development: the briefs ask for readiness bands and risk thresholds, but the plan holds all race prediction on ADR-0016. Challenge any string, prop or enum that implies a prediction.
* frontend-ui-engineering: the race-detail drawer needs focus trap, Escape to close, and focus return to the opener.
* antislop-human: Level 1 "Critical" mandatory-race alerts are glyph plus text, never colour alone.

READ: screen-spec-2.0 SCREEN-011 and its correction row; design-2.0 sections 21, 32, 36; plan D10 and the ADR-0016 note in sections 3 and 6; RaceCatalogSlot, components/RaceCalendar.vue and RacePanel.vue, the existing runs.races.store route.

BUILD:

* RaceCard shows race name, grade, distance, surface, running style, fan gain, reward and skill-point gain, only fields present on RaceCatalogSlot or entered by the Trainer. Missing fields read "N/A" with a title. Do not hard-code distance bands; if distance_band is shown it carries a source/confidence note (the 1400m Sprint-versus-Mile conflict).
* Win probability and readiness bands (Excellent / Good / Borderline / Poor) are NOT computed. Render "Readiness: N/A" with a title naming the ADR-0016 data blocker. The percentage risk indicator is not built.
* Mandatory-race deadlines come from the calendar data, Level 1 "Critical", glyph plus text.
* Actions: "Enter Race" posts via runs.races.store; "Skip" returns without recording. Details open in a drawer or inline disclosure with focus management.

ACCEPTANCE: grep proves no win-percentage string. Props test covers present and absent race fields. Playwright covers the N/A title text, the drawer's keyboard behaviour and the 44px sweep.

==============================================================  
PROMPT D11: Event Decision (SCREEN-012)
========================================================================================================

TASK: Create components/career/EventCard.vue and pages/Career/EventDecision.vue. Depends on D8.

SKILLS (add to baseline):

* api-and-interface-design: the event props contract must mirror the TurnEvents*Payload classes exactly. Do not widen it for fields the spec wants but the payloads lack.
* security-and-hardening: event text and notes are untrusted. No v-html, validate through the existing Form Request, tie errors to inputs.
* doubt-driven-development: challenge the advisor panel. A recommendation without a derivable reason line must not ship.
* antislop-human: the "outcome incomplete" warning is glyph plus text; the override path is as prominent as the recommended path.

READ: screen-spec-2.0 SCREEN-012 and its event-model correction; design-2.0 sections 2, 36, 38; plan D11; TurnEvent, the TurnEvents*Payload classes, and the existing event recording in TrainingRunController.

BUILD:

* Event model in the UI: source (Support / Character / Scenario / Random), event name, choices, known outcomes per choice, current career state alongside. Build only the sources and outcome fields the payload classes hold.
* Choices are labelled radio options. Outcomes show recorded effects only. A choice with no recorded outcome prints "⚠ Event outcome incomplete. Choose manually."
* Advisor panel: show a recommended option only when the engine returns one with a derivable reason line. Otherwise "No recommendation available" and why. The Trainer can always override. Never preselect the recommended option.
* Primary action "Record Choice" posts through the existing TurnEvent write and its Form Request. Errors tie to inputs with aria-describedby.

ACCEPTANCE: props test covers complete and incomplete outcomes plus the override path. Playwright covers the incomplete warning, keyboard selection and the recorded result.

==============================================================  
PROMPT D12: Inheritance Event, record-only (SCREEN-013)
========================================================================================================================

TASK: Create pages/Career/InheritanceEvent.vue. Depends on D8.

SKILLS (add to baseline):

* source-driven-development: use the same SparkChip and the reference section 1.5 facts as D5. Take milestone turns only from config or recorded events, and cite where.
* doubt-driven-development: predicted versus observed is the contract of this screen (ADR-0020 section 3). Challenge any element that blurs them.
* code-simplification: reuse SparkChip and the timeline glyph component. Do not fork them.
* antislop-ui: two clearly separate regions with their own headings and badges, not one merged table.

READ: screen-spec-2.0 SCREEN-013 and its correction row (probability, not guarantees); design-2.0 sections 18, 22, 49; plan D12; ADR-0020 section 3; LegacySelectionPayload and the existing event recording.

BUILD:

* Parent and grandparent sparks from the run's legacy selection, via SparkChip.
* Observed outcomes: a form to record the inspiration result the Trainer saw in the game (which sparks activated, previous inheritance), posted through the existing event recording. Predicted and observed are visually apart, each with a heading and badge (Estimated vs Confirmed). Observed is always Confirmed because the Trainer entered it.
* Predicted / "expected inheritance": NOT computed. Show "N/A" with a title citing ADR-0020 section 3. A sourced star-roll table may be shown as Estimated, never summed.
* Milestone timeline with glyph plus text (● completed, ◉ current, ○ upcoming, × missed). Do not hard-code "Classic April".

ACCEPTANCE: props test proves observed values round-trip and that no predicted total exists in the payload. Playwright covers keyboard entry and the predicted-versus-observed separation.

==============================================================  
PROMPT D13: Skills Planner (SCR-013, design-2.0 only)
======================================================================================================================

TASK: Create pages/Career/SkillsPlanner.vue. Depends on D8. This screen exists only in design-2.0.

SKILLS (add to baseline):

* spec-driven-development: write the state model first (Required / Available / Learned / Inherited): where each state is read from and its precedence when a skill qualifies for two.
* source-driven-development: hint-level discount percentages must come from a sourced table in config or the reference doc. If none exists, the discounted cost is N/A. Cite what you found or the absence.
* doubt-driven-development: the race-fit assessment is where a recommendation could sneak in (OQ-5 stays open). Challenge any wording that ranks or advises.
* frontend-ui-engineering: ordered list with button-based reordering and a live-region announcement of the new position.

READ: plan D13 and open question OQ-5; design-2.0 sections 15, 17, 33; components/SkillRow.vue, the run's skillCatalog and skillGroups props, the runs.skills.sync write, and BuildTarget skill_priorities.

BUILD:

* Skill states Required / Available / Learned / Inherited, each text plus glyph. Required comes from the Trainer's ordered skill priorities in the BuildTarget.
* SP coverage: sum SP cost of Required skills against current Skill Points and warn (glyph plus text) when SP cannot cover them. Badge Calculated.
* Hint-level discount: base cost × (1 − discount%), with percentages only from a sourced table. Otherwise "N/A" with a title.
* Race-fit assessment per skill: the skill's recorded conditions (distance, surface, style, course, weather, ground, phase) beside the build target as a plain "matches / does not match / not recorded". No score.
* No skill recommendation of any kind.
* Priority reorder via Move up / Move down buttons. Save through runs.skills.sync and its Form Request.

ACCEPTANCE: props test covers the SP warning and each state. Playwright covers keyboard reorder, the warning text and the 44px sweep.

==============================================================  
PROMPT D14: Career Timeline (SCREEN-018)
=========================================================================================================

TASK: Create components/career/CareerTimeline.vue and pages/Career/Timeline.vue. Depends on D8.

SKILLS (add to baseline):

* performance-optimization: a full career is 70+ turns with events. Eager-load TurnEvent per turn, paginate or window the list, and avoid N+1.
* api-and-interface-design: define one typed history-entry shape (turn, kind, before, after, corrected_from) that D8's strip, D15 and D16 can reuse.
* source-driven-development: ground the before/after mapping in ADR-0003 and turnRows(); cite them.
* antislop-human: the rail is a list with proper semantics (ol), each entry reachable by keyboard, glyph plus text for state.

READ: screen-spec-2.0 SCREEN-018 and its decision-audit correction; design-2.0 sections 22, 32, 37; plan D14; TurnEntry and TurnEvent, turnRows(), ADR-0003.

BUILD:

* One vertical rail with glyph plus text: ● completed, ◉ current, ○ upcoming, × missed or failed. Each entry: turn, action label, before state, after state. Entries expand inline (aria-expanded) or open a drawer with focus management.
* Decision audit: BEFORE, ACTION, EXPECTED, ACTUAL, RESULT, accepted / rejected only where recorded. Anything unstored prints "N/A" with a title. Do not show advisor confidence.
* Corrections appear as additional entries and are never silently rewritten. A past decision is read-only here.
* Filters by type (training, races, events, inheritance, scenario actions, purchases), only for types that exist in the data.
* Link to the matching decision screens where they exist.

ACCEPTANCE: props test proves the before/after mapping and that a correction appends. Playwright covers the keyboard path through the rail, the expansion and glyph-plus-text states.

==============================================================  
PROMPT D15: Career Result (SCREEN-019)
=======================================================================================================

TASK: Create pages/Career/Result.vue. Depends on D8.

SKILLS (add to baseline):

* antislop-copywriting: calm, specific summary copy (Peak-End Rule). No celebratory filler, no "Great job!".
* impeccable: one polish pass on hierarchy and spacing, within tokens.
* doubt-driven-development: challenge every aggregate. Per-stat deficits are fine; any single "quality", "completion %" or "legacy value" figure is not.
* design-review (optional, global): quick visual QA.

READ: screen-spec-2.0 SCREEN-019 and its correction (Veteran Creation framing); design-2.0 sections 4, 15, 17, 45; plan D15 and the "Peak-End Rule" row in section 13.

BUILD:

* Reads a finished run only. If not finished, show an empty state that says what is missing and links back to the cockpit.
* Sections: Final build (five stats with value AND the target they were set against; skills; aptitudes with letter badges), Race history (races, wins, losses, G1 wins, from recorded entries only), Scenario result (objectives and rewards from the scenario config and recorded data; panels driven by flags, not scenario names).
* NO derived build-quality score, no aggregate target-completion percentage, no legacy-value rating, no "best use recommendation". Per-stat deficit against target is allowed, badge Calculated.
* Ruleset line "N/A" with a title.
* Primary action "Save Veteran" to D16.

ACCEPTANCE: props test for finished and unfinished runs. Playwright covers the unfinished empty state, the headings and the 44px sweep.

==============================================================  
PROMPT D16: Save Veteran + Veteran Library + Comparison (SCREEN-020 / 021 / 022)
=================================================================================================================================================

TASK: Create pages/Career/SaveVeteran.vue, pages/Veterans/Index.vue, Veterans/Show.vue and Veterans/Compare.vue. Depends on C3 and D15.

SKILLS (add to baseline):

* security-and-hardening: notes and custom tags are free text. Never v-html. Validate length and shape in the Form Request. Delete is destructive: confirm, authorise as the repo does, and test the refusal path.
* careful (optional, global): active before any destructive command while testing delete.
* api-and-interface-design: define the veteran list/show props once and reuse in Index, Show, Compare and the Legacy Lab picker.
* doubt-driven-development: challenge "Factors", "Ancestry" and "Find Parents for This Build" for hidden computation. They are search and display only.
* code-simplification: reuse SparkChip, the ancestry graph from D5 and the comparison row component from D5.
* laravel-best-practices: list/filter/sort queries use indexed stored fields and explicit allow-lists.

READ: screen-spec-2.0 SCREEN-020 to 022 and their correction rows; design-2.0 sections 18, 29, 31, 46; plan D16 and C3 (section 7); ADR-0020 section 3; ADR-0010; the veterans table and its record / list / show actions.

BUILD:

* SaveVeteran: name, tags, notes (free text). Tags are SUGGESTIONS (Speed, Stamina, Power, Guts, Wit, Sprint, Mile, Medium, Long, Dirt, Turf, Front Runner, Pace Chaser, Late Surger, End Closer, Skill, Race, Scenario) the Trainer toggles, plus custom tags. Workflow: review recorded sparks, tag, save. Factor analysis and "legacy value" are computation and NOT built: "N/A" with a title citing ADR-0020 section 3. Write no recommendations such as "Excellent Medium parent".
* Index: three views, Veterans (all), Factors (searchable inventory of recorded factors), Ancestry (six-node graph of where a veteran came from, using SparkChip). Search, filter and sort only on stored fields. "Find Parents for This Build" navigates to the Legacy Lab with the build's filters applied (a search, not an optimizer).
* Favorite, archive, delete: build ONLY if C3 provides them; delete needs a confirmation dialog with focus management. Otherwise list them as not built.
* Show: the recorded veteran with ruleset line "N/A". Empty state "No Veterans Yet" (what / why / action).
* Compare: up to four, aligned property rows, recorded values only.

ACCEPTANCE: grep proves no inheritance computation. Props test covers tags, filters, compare limit. Playwright covers tag toggling by keyboard, the empty state and the delete confirmation if present.

==============================================================  
PROMPT D17: Database (SCREEN-023)
==================================================================================================

TASK: Create pages/Database/{Trainees,Supports,Skills,Races,Scenarios}.vue. Depends on A1 to A3 (landed).

SKILLS (add to baseline):

* code-simplification + deprecation-and-migration: reuse, don't fork. Extract shared list and detail bodies, keep /umamusume, /skills and /support-cards working, and prove with the existing browser specs that nothing regressed. Redirect only if an owner ruling exists.
* performance-optimization: reference tables are the largest lists in the app. Keep server-side pagination and the existing cache keys.
* antislop-layoutmobile: wide tables scroll inside their own container, never the page, at 320px.
* source-driven-development: the Scenarios page prints config/scenarios.php as-is with its own verification date; cite the header.

READ: screen-spec-2.0 SCREEN-023; design-2.0 sections 29, 32, 34; plan D17 and the "Deliberately traded" note in section 13; the existing pages/Catalog/_, Skills/_, SupportCards/* and their controllers; config/scenarios.php.

BUILD:

* Reuse the ported screens. Add a Database hub linking to the five areas. Trainees, Supports and Skills present existing data; do not rebuild their queries.
* Races: from RaceCatalogSlot. With 0 rows, name the offline state (what is missing, why, what to do). No placeholder races.
* Scenarios: render the resolved matrix from config/scenarios.php as-is (caps, panels, widgets, live date, documented flag). Grand Concert shows baseline plus "PARTIALLY DOCUMENTED". No scenario-name branching.
* Events, Shop Items and Sparks tabs are out of scope; list them as deferred.
* Density is allowed here and nowhere else. Tables still need labelled columns and keyboard-reachable rows.
* Image slots follow 45a.

ACCEPTANCE: Playwright covers the hub, the Races offline state, the Scenarios matrix and 320px reflow. Existing catalog, skills and support specs pass untouched.

==============================================================  
PROMPT D18: Settings (SCREEN-024)
==================================================================================================

TASK: Extend pages/Preferences/Edit.vue into Settings categories: General, Recommendation, Data, Game version.

SKILLS (add to baseline):

* laravel-best-practices: any migration needs a working down(), an ARCHITECTURE-ESSENTIALS.md digest update and a PRD citation (AGENTS.md section 11). Stop and ask before writing one.
* deprecation-and-migration: if a preference column is added, use expand-then-contract and keep the existing preferences working.
* documentation-and-adrs: update the SCREEN_SPEC.md row and the digest. Draft (do not file) any ADR text the owner might need.
* security-and-hardening + careful (optional, global): Reset is destructive. Confirmation dialog, server-side guard, and a test of the refusal path.
* doubt-driven-development: challenge each proposed preference. If nothing reads it, it should not exist.
* antislop-human: save feedback is a polite live region that never steals focus.

READ: screen-spec-2.0 SCREEN-024; design-2.0 sections 34, 47, 48; plan D18 and risk 4 in section 11; PreferencesController, its Form Request, the preferences columns and tests/browser/preferences.spec.ts.

BUILD:

* Category navigation inside the page: a labelled tablist or anchored fieldsets with a keyboard path and stable Help placement (WCAG 3.2.6). Keep existing preferences and the existing spec passing.
* General (theme, language, units, default scenario) and Recommendation (aggressiveness, risk tolerance, stat-target defaults): add a control ONLY if its column already exists or the owner approves the migration. In your slice plan list every proposed preference, mark which already have a column, and stop and ask before any migration. "Race risk thresholds" is NOT built because it depends on win probability.
* Data (import, export, backup, restore, reset): link only to import and export routes that already exist. Reset is destructive and needs a confirmation dialog with focus trap, Escape and focus return; build it only if a route exists, otherwise a named absence.
* Game version: "Global Ruleset, Version: N/A" with a title explaining that no ruleset string is sourced; plus the verified date from config/scenarios.php.
* Save feedback: polite live region ("✓ Preferences saved").

ACCEPTANCE: props test for each real preference, existing preferences tests unchanged. Playwright covers category navigation by keyboard, the saved message, the N/A ruleset row and the reset confirmation if it exists.

==============================================================  
NOTES
======================================================================

* Skills the prompts lean on hardest: doubt-driven-development for the slices where computation could creep in (D5, D7, D10, D12, D13, D16) and for the wizard-persistence decision (D2). source-driven-development grounds any number on screen in a model, config or reference line.
* Global-only skills (design-review, careful, freeze) are marked optional. SKILL.md shows them installed globally but not in `.agents/skills/`, so an agent in a clean sandbox may not have them. The prompts tell the agent to say so and carry on.
* I did not use `qa`, `ship`, `land-and-deploy` or `review`. `qa` fixes bugs and could touch unrelated files, and the others change git or deploy state. The hand-off sequence already covers verification, and the owner decides when to ship.
* The "Skills used" line in the final report is there so you can see which skills the agent applied. If one is never reported, it probably wasn't loaded.
