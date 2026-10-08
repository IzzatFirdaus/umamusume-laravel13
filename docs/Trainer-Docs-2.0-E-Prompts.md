The E-slice prompts follow, E1 through E6. Paste the SHARED CONTEXT (E-phase) block above each one. It replaces the D-phase block and keeps the same baseline skills.

==============================================================  
SHARED CONTEXT, E-PHASE (paste before every E prompt)
======================================================================================================================

You are working in the Trainer Desk 2.0 repo (Laravel 13, Inertia v3, Vue 3, TypeScript, Tailwind v4, Pest 4, Playwright). You are building ONE slice of Phase E (scenario panels) of the Trainer Desk 2.0 frontend. Phase E plugs into the Career Cockpit that D8 built. If D8 has not landed, stop and report.

READ FIRST, in this order:

1. AGENTS.md
2. docs/proposals/frontend-development-plan.md: Global Constraints, sections 3, 8 (the "Scenario panel contract (E1)" paragraph), 9 (Phase E), 10, 12, 13, plus your slice's row in section 4
3. docs/proposals/screen-spec-2.0.md: your SCREEN-nnn section AND the "Product direction corrections" table
4. docs/proposals/design-2.0.md: sections 4, 25 to 28, 29, 36, 45, 45a, 49 and the "Appendix, Frontend architecture"
5. config/scenarios.php (read the real key names; never guess them), DESIGN.md, SCREEN_SPEC.md

AUTHORITY ORDER: landed ADRs, then AGENTS.md, then the development plan, then the two design briefs. The briefs are design intent. The plan is the authorization. Skills are METHOD only. If a skill's default advice conflicts with a repo document (dark: variants, a new package, extra animation, a redesign, a recommendation score), the repo wins. Say so in your report.

THE CENTRAL RULE OF PHASE E: the UI must not know how a scenario works (G-33, D-240). Panels are selected by panels.* flags and widgets[] read from config/scenarios.php through props. No component, test fixture, prop or class name may branch on scenario.label, scenario name, or a scenario key literal. The component FILE names from the brief (UraPanel.vue, UnityCupPanel.vue, TrackblazerPanel.vue) are allowed, but they are registered against widget or flag keys, not scenario names. A fifth scenario must be one config entry and zero component edits.

SKILLS: load each by name at the phase shown. If one is not installed, say so in your report and apply its intent anyway.  
Baseline (every slice):

* using-agent-skills: governs how you find and invoke the rest.
* planning-and-task-breakdown + spec-driven-development: PLAN PHASE. Write the slice plan (files, props contract, tests, open questions) before code.
* incremental-implementation + test-driven-development: BUILD PHASE. Failing props test, controller, component, passing test, Playwright spec.
* laravel-best-practices + testing-best-practices: controllers, Form Requests, query shape, Pest structure.
* frontend-ui-engineering + tailwindcss-development: Vue components, layout, state, WCAG. Tokens only, never dark: and never zinc-*.
* antislop, antislop-ui, antislop-human, antislop-copywriting, antislop-layoutmobile: apply to every string and layout. antislop-code for comments.
* constraint-driven-development: the repo's gates (G-19, G-33, G-41, G-47, DesignTokensTest) are the contract. Check your diff against them.
* source-driven-development: every number or label you print traces to config/scenarios.php, a model, a migration, an ADR or a reference-doc line. Cite it in the slice plan.
* doubt-driven-development: REVIEW PHASE. Challenge every non-trivial decision (especially anything that looks like a recommendation or a prediction) and report the result.
* code-review-and-quality: final self-review. The diff must contain no unrelated file.
* browser-testing-with-devtools: VERIFY PHASE. Console errors, focus order, 320px reflow, 200% zoom and reduced motion in a real browser.  
  Optional if installed: freeze (restrict edits to the files your slice needs), design-review (visual QA).

HARD RULES

* Panels are static modules with their own currencies and objectives, mounted inside the Cockpit's scenario region as "Career Cockpit + Scenario Panel" (design-2.0 section 25). The Cockpit layout itself must not change per scenario.
* Copy the repo's page and component patterns; do not invent a second one. Any new page gets a controller method returning Inertia::render with explicit arrays (never Eloquent models), a route, and a Pest assertInertia test. No uses() or withoutVite() in test files.
* Writes keep their existing Form Requests and routes (runs.purchases.store, runs.races.store, runs.turns.*). Vue only posts and handles useForm errors.
* Unrecorded or unsourced values render as "N/A" with a title attribute explaining why. Never a default, a dash, or an invented number. ProvenanceBadge.vue (built in D4) labels every computed figure Confirmed / Calculated / Estimated / Unknown. Use it, never redefine the glyphs.
* HELD computation stays held: no race win probability, readiness band or risk percentage (ADR-0016), no per-training yields or failure %, no inheritance computation, no shop-item recommendation (rotation not modelled), no Grand Concert mechanics, no numeric confidence or score, no "recommended timing" or "projected benefit".
* Global terminology: Support Cards, Scouts, Veteran Umamusume, Pal, Wit, Sparks. Print "Trackblazer", never "Twinkle Star Climax" (UNVERIFIED, conflict row 31). Write "2026-07-01 Global rework".
* WCAG 2.2 AA: keyboard path, visible focus, min-h-11 (44px) targets, labels, aria-describedby on errors, prefers-reduced-motion respected, no drag-only interaction, state never by colour alone (glyph plus text), sticky UI must not cover focus. Meters are numbers AND bars, with the bar labelled.
* Every panel has loading, empty and error states, and each says what is missing, why it matters and what to do.
* Do not delete a Blade x-* component until its consumer count is zero (grep). If B1 already deleted a reference component (x-team-rank-gauge, x-team-race-panel, x-spirit-burst-roster, x-grade-point-meter, x-shop-panel, x-epithet-checklist), recover it from git history (git log --diff-filter=D, git show) and port its logic and props; do not rewrite from memory.
* No new npm or composer package. No vue-tsc. Do not file an ADR; draft text in your report if one seems needed.
* Update SCREEN_SPEC.md section 2 with the screen's row and its empty / loading / error state table.

PROCESS: plan phase, then TDD build, then Playwright spec in tests/browser/<screen>.spec.ts (rendered copy, 44px sweep, keyboard path, axe A + AA if the dependency exists), then review and verify phases.

HAND-OFF SEQUENCE, report real output and never claim an unrun gate passed: targeted tests, php artisan test --compact, vendor/bin/pint --dirty --format agent, vendor/bin/phpstan analyse --no-progress --memory-limit=1G, npm run typecheck, npm run build, npm run test:browser, composer lore + composer lore-code with a ruling per hit.

FINAL REPORT: files changed, props contract, every deviation from the plan and why, open questions for the owner, gate output, and a "Skills used" list (one line each, plus skipped skills and why).

==============================================================  
PROMPT E1: Scenario panel shell bound to config/scenarios.php (SCREEN-014 / 015 / 016)
=======================================================================================================================================================

TASK: Create components/scenario/ScenarioPanel.vue (the shell) and the panel registry that maps config widget and flag keys to renderer components. Mount it in the Cockpit's scenario region. Depends on D8. E2, E3, E4 and E6 plug into this slice, so its contract must be right the first time.

SKILLS (add to baseline):

* api-and-interface-design: this slice defines the contract that four later slices consume. Fix the types in resources/js/types.ts once and keep them stable.
* doubt-driven-development: challenge the registry. What renders when a widget key has no renderer? What if panels.* is all false? What if the scenario is unknown to the frontend? Each must fail visibly and safely.
* constraint-driven-development: G-33 is the headline gate here; make it a test, not a promise.
* code-simplification: one shell, one registry, zero per-scenario conditionals.

READ: plan section 8 "Scenario panel contract (E1)" and section 9 "E1 — the shell"; design-2.0 section 25 (Cockpit + Scenario Panel), section 49 appendix shape; screen-spec-2.0 SCREEN-014 to 016 headers and the corrections table rows for "§1–2" and "§26–31"; config/scenarios.php (all four entries, including our_grand_concert with documented => false); the Cockpit (D8) scenario region.

BUILD:

* Adopt the brief's section 49 shape as-is: { name, version, status, resources, objectives, actions, alerts, recommendations, finale }. Rules from the plan: version renders as a named absence ("N/A" with a title, because app.ruleset is null), and any "recommendations" entry that implies prediction renders as a named absence where the corpus is silent.
* ScenarioPanel.vue takes the resolved scenario array from the Cockpit as a prop. It switches on panels.* flags and widgets[] only. It never reads scenario.label for logic (display of the label as text is fine).
* Registry: a typed map of widget or flag key to async-free renderer component. An unrecognised key renders a labelled fallback ("This scenario declares a widget this build cannot draw") rather than an error or a blank. A scenario with every panel off renders only the baseline strip (E6 owns its content; E1 just must not break).
* Shared building blocks the panels reuse: ScenarioStatusBadge (status text plus glyph, including PARTIALLY DOCUMENTED from documented => false), ResourceMeter (value AND bar, labelled, "N/A" when unrecorded), AlertRow (glyph plus text). Build them here so E2 to E4 do not each invent one.
* Placement: inside the Cockpit's scenario region at all breakpoints (desktop right of the decision area per D8, tablet and mobile in the D8 order). The Cockpit header and state panel must render identically across all four scenarios.
* Loading, empty and error states for the shell per design-2.0 sections 29 and 30 (no skeleton class; DesignTokensTest forbids it).

ACCEPTANCE:

* A Pest test renders the same run header for all four Global scenarios (G-33's own assertion).
* A test adds a fifth scenario entry via config()->set (with a new widget set drawn only from existing keys) and asserts a panel renders with zero component changes.
* A grep over the diff for scenario names, scenario.label comparisons and key literals in conditionals returns nothing.
* Playwright: shell landmarks, status badge text, fallback text for an unknown widget key, 320px reflow, 44px sweep.

==============================================================  
PROMPT E2: URA panel, goals and Happy Meek (SCREEN-014)
========================================================================================================================

TASK: Create components/scenario/UraPanel.vue, registered in the E1 registry against the widget or flag keys that config/scenarios.php assigns to URA's panels. Depends on E1.

SKILLS (add to baseline):

* source-driven-development: URA is the least complex scenario (design-2.0 section 26). Every field must trace to config or an existing run column. Cite the config keys in your slice plan.
* doubt-driven-development: the screen spec asks for "potential reward" and "final-race contribution" for Happy Meek. Challenge each: sourced, entered, or unsourced?
* antislop-ui: URA is deliberately quiet. No decorative permanent panels beyond what the spec lists.

READ: screen-spec-2.0 SCREEN-014 (section 18); design-2.0 section 26 (URA Visual Language: career goals, Happy Meek, finale progression, avoid unnecessary permanent panels), section 22 (timeline glyphs), section 36; plan section 9 "E2 URA"; the run's existing goal data (read TrainingRunController, the goals relation and the run page props); config/scenarios.php URA entry.

BUILD:

* Career goals: the character's goals as recorded on the run, each with status as glyph plus text (● completed, ◉ current, ○ upcoming, × missed) and the turn deadline when stored. Mandatory races within the goals link to the D10 race screen if it exists.
* URA progression and finale preparation: a short strip derived only from recorded goals and config milestones. Do not hard-code race names or turns.
* Happy Meek module: current level (Trainer-entered value, badge Confirmed), duel availability (only if a stored or configured field exists), potential reward and final-race contribution. Render each of the last two as "N/A" with a title unless the repo already holds a sourced value. Do not estimate.
* Alerts: a mandatory goal race inside the deadline window is a Level 1 "Critical" AlertRow (glyph plus text), derived from the calendar data only.
* Recording a Happy Meek level or goal outcome goes through an existing write. If none exists, the module is read-only and the slice plan says so for the owner. Do not add a migration.
* Reuse E1's ResourceMeter, AlertRow and status badge. No new permanent panels.

ACCEPTANCE: props test covers goals in each state, Happy Meek with and without a recorded level, and N/A fields. A grep proves no scenario-name branching. Playwright covers goal states as text, the Critical alert, the N/A titles, keyboard path and the 44px sweep.

==============================================================  
PROMPT E3: Unity Cup panel, Team Cockpit (SCREEN-015)
======================================================================================================================

TASK: Create components/scenario/UnityCupPanel.vue (the Team Cockpit), registered against the config keys for Unity Cup's panels. Port the three Blade references. Depends on E1.

SKILLS (add to baseline):

* code-simplification + deprecation-and-migration: this is a port. Recover x-team-rank-gauge, x-team-race-panel and x-spirit-burst-roster (from the tree, or from git history if B1 deleted them) and carry over their props and logic. Behaviour parity first, redesign second. Delete a Blade component only when its grep consumer count is zero.
* source-driven-development: Spirit, Spirit Burst, Extreme Spirit Burst, team rank and Special Training numbers come from config/scenarios.php or run data only. Cite the keys.
* doubt-driven-development: the spec's "burst readiness", "recommended timing" and "projected benefit" are advice. Challenge each: derivable from stated config values, or not?
* frontend-ui-engineering: the roster is a labelled list or table with row-level keyboard reach, not a div grid.

READ: screen-spec-2.0 SCREEN-015 (section 19) and its Components table row (reuses x-spirit-burst-roster, x-team-rank-gauge, x-team-race-panel); design-2.0 section 27 (Unity Cup Visual Language: team cards, member rows, Spirit meters, burst indicators; the Team Panel is a first-class UI element), sections 15 and 45; plan section 9 "E3"; config/scenarios.php Unity Cup entry; the existing Blade components and the run page's Unity-related props.

BUILD:

* Team Panel (first-class): team rank as the recorded letter grade (for example "A+"), per-stat team grades (Speed, Stamina, Power, Guts, Wit) as letter plus text, only for values recorded on the run. Missing values read "N/A".
* Team composition and member rows: members from the run's recorded team data (stats, role), as a labelled table or list.
* Team races: reuse the x-team-race-panel logic with race facts from RaceCatalogSlot or recorded data. No win probability, no risk percentage.
* Spirit and bursts: current Spirit as value AND bar (ResourceMeter), burst indicators for Spirit Burst and Extreme Spirit Burst as glyph plus text, with the thresholds taken from config. Burst readiness is shown only as "current Spirit versus the configured threshold" (Calculated). "Recommended timing" and "projected benefit" are NOT built: render a named absence with a title.
* Special Training: shown as recorded or configured facts only.
* Recording Spirit or team state goes through existing writes. If none exists, read-only; list it as an open question. No migration.
* Responsive: desktop shows team cards beside the Spirit meters; mobile stacks, with no horizontal scroll.

ACCEPTANCE: props test covers a full team, a partial team and no team data. Grep proves no scenario-name branching. Playwright covers the roster by keyboard, meter text AND bar, burst glyph plus text, the named absence for timing/benefit, 320px reflow and the 44px sweep.

==============================================================  
PROMPT E4: Trackblazer panel, grade points, shop, epithets (SCREEN-016)
========================================================================================================================================

TASK: Create components/scenario/TrackblazerPanel.vue registered against Trackblazer's config keys. Port the three Blade references. Depends on E1.

SKILLS (add to baseline):

* code-simplification + deprecation-and-migration: recover x-grade-point-meter, x-shop-panel and x-epithet-checklist (tree or git history) and port props and logic. Grep consumers before any deletion.
* doubt-driven-development: the screen spec shows a "Recommended Purchase" card with a reason line. The plan holds it. Challenge every shop element for an implied recommendation, including sort order, default selection and badge wording.
* security-and-hardening: purchases write through runs.purchases.store. Keep its Form Request as the authority, tie errors to inputs, and never trust client-side cost or coin totals.
* source-driven-development: shop catalogue rows come from config trackblazer.shop. Cite the keys and the verification date.
* antislop-copywriting: shop rows are scan-read. Write item, cost, effect and duration as exact labelled values.

READ: screen-spec-2.0 SCREEN-016 (section 20) and its Components row (reuses x-shop-panel, x-grade-point-meter, x-epithet-checklist; shop recommendation HELD); design-2.0 section 28 (Trackblazer Visual Language: Grade Points, Shop Coins, Race Calendar, Shop Inventory, Rival Alerts; the Shop must be quickly accessible from the Cockpit), section 45; plan section 3 (Shop row) and section 9 "E4"; config/scenarios.php Trackblazer entry and trackblazer.shop; the existing purchases write and its Form Request.

BUILD:

* Grade Points meter: current value, thresholds from config, value AND bar, remaining to next threshold (Calculated). Shop Coins: the recorded balance, "N/A" when unrecorded.
* Shop catalogue: a labelled table or list of item, cost, effect, duration from config. Catalogue order is the config order. NO "Recommended Purchase" card, NO recommended flag, NO "best value" sort or highlight, NO default-selected item. State once, in text, that the shop rotation is not modelled so this is the catalogue only.
* Recording a purchase: select item, then a plain "Record Purchase" button posting through runs.purchases.store. Show the server's validation errors tied to inputs. Show purchased items for the run as a list.
* Epithet checklist: items from config or recorded data, each as checked / unchecked with glyph plus text. Do not infer completion.
* Rival races and race schedule: from RaceCatalogSlot or recorded data only, as facts. Rival alerts are AlertRows (glyph plus text) triggered only by data on the calendar.
* Do not print "Twinkle Star Climax". The finale reads "Trackblazer finale" and any official title is a named absence with a title citing conflict row 31.
* Quick access: the Cockpit's action area gets a "Shop" entry that scrolls or navigates to this panel's shop region with focus moved to its heading (2.4.11 safe).

ACCEPTANCE: a grep over the diff finds none of: "recommended", "best value", "Twinkle Star Climax" outside the named-absence title. Props test covers the catalogue, a recorded purchase and a rejected purchase. Grep proves no scenario-name branching. Playwright covers the shop by keyboard, the purchase round-trip and error text, glyph plus text for epithets, the 44px sweep and 320px reflow.

==============================================================  
PROMPT E5: Scenario Race Planner (SCREEN-017)
==============================================================================================================

TASK: Create pages/Career/RacePlanner.vue (or the route the D8 slice plan fixed for Cockpit sub-pages) and components/career/RacePlanList.vue. Race facts only, no win probability. Depends on D8. It reuses RaceCard from D10 if D10 has landed.

SKILLS (add to baseline):

* source-driven-development: every race field traces to RaceCatalogSlot, its migration, or the calendar data. Cite them. The 1400m Sprint-versus-Mile conflict means distance_band, if shown, carries source and confidence, and is never hard-coded.
* doubt-driven-development: the spec asks for "expected risk", a "Win probability: 84%" recommendation block and "No critical training deadline will be missed". Challenge each line and keep only what is derivable.
* code-simplification: reuse D10's RaceCard and D5's comparison row pattern. No second race component.
* performance-optimization: the planner lists a full calendar. Keep one eager-loaded query and paginate or group by phase.
* frontend-ui-engineering: reward comparison is a real labelled table with keyboard-reachable rows.

READ: screen-spec-2.0 SCREEN-017 (section 21), its header banner about win-probability being HELD under ADR-0016 and ADR-0020 section 4, and the correction "race simulator explicitly deferred"; design-2.0 sections 21, 32, 46; plan section 3 (Race win probability row) and section 9 "E5"; RaceCatalogSlot, components/RaceCalendar.vue, RacePanel.vue, the existing runs.races.store route.

BUILD:

* Four groups: upcoming, mandatory, optional, rival races, taken from the calendar and RaceCatalogSlot. A group with no data shows a named empty state (what is missing, why, what to do). The rival group is shown only where the data exists.
* Reward comparison: selected races side by side (up to four) with aligned rows: grade, distance, surface, fan gain, reward, skill-point gain, Grade Points or Shop Coins where config defines them. Missing fields read "N/A" with a title.
* Target alignment: plain matches / does not match / not recorded text for race distance, surface and style against the BuildTarget. No score and no percentage. Badge Calculated.
* Expected risk and win probability: render "Win probability: N/A" and "Expected risk: N/A", each with a title naming the ADR-0016 data blocker. Do not build readiness bands or LOW / MEDIUM / HIGH wording. Do not render the spec's recommendation block.
* Deadline awareness: show mandatory races with their turn and a Level 1 "Critical" AlertRow when the calendar puts one inside the current window. Do not claim "no deadline will be missed"; state only the next mandatory race and its turn.
* Actions: "Enter Race" posts via runs.races.store; "Skip" returns without recording.

ACCEPTANCE: a grep proves no win-percentage string and no readiness-band enum. Props test covers each group present and absent, N/A fields and a four-race comparison. Playwright covers the keyboard path through the groups and comparison table, the N/A titles, the Critical alert text, 320px reflow and the 44px sweep.

==============================================================  
PROMPT E6: Grand Concert panel, baseline strip only (SCR-017, design-2.0 only)
===============================================================================================================================================

TASK: Create the baseline strip for the fourth scenario, registered in the E1 registry for the "every panel off" case. Depends on E1. NOTE: this is SCR-017 (Grand Concert panel) from design-2.0. It is NOT SCREEN-017 (the Scenario Race Planner), which is E5. Do not mix the two.

SKILLS (add to baseline):

* doubt-driven-development: the point of this slice is restraint. Challenge every element for an invented mechanic. If it is not in config/scenarios.php or the run data, it does not appear.
* source-driven-development: cite the scenario's config entry (documented => false, the caps, the verification date) and the sourcing rows in plan section 3 ("Grand Concert mechanics").
* antislop-copywriting: the honesty line must be plain and specific, not apologetic filler.
* constraint-driven-development: G-41 and G-33 are the gates; make G-41's assertion a test.

READ: plan section 3 (Grand Concert mechanics row), section 9 "E6" (D-241, G-41); screen-spec-2.0 corrections rows "§1–2" and "§26–31" and the SCREEN-014 to 017 summary row; design-2.0 sections 25, 29, 49; config/scenarios.php our_grand_concert entry; docs/scenarios/07 (the stub).

BUILD:

* The baseline strip shows only what the config and the run hold: scenario name, the "PARTIALLY DOCUMENTED" status badge (glyph plus text), the five stat caps from config (they were corroborated by two independent sources on 2026-10-05), the config verification date, and the run's own tracked values (the ordinary career state the Cockpit already shows).
* Every panel is off. Songs, lessons and Performance Tokens do not exist in the corpus, and the panel says so in one plain sentence with a title pointing at the stub. Do not list placeholder songs, lessons, tokens, objectives or recommendations. Do not add input fields for mechanics nobody has documented.
* Advanced advisor features are limited: the Cockpit's recommendation for this scenario uses only the generic engine, and this panel adds nothing to it. Print that scope in text so the Trainer is not left guessing.
* Because the caps are sourced but the mechanics are not, show caps with badge Confirmed (sourced) and the mechanics line with badge Unknown.
* Use E1's ScenarioStatusBadge, ResourceMeter and AlertRow. No Grand-Concert-specific component beyond the strip itself.
* When mechanics are verified later, the change should be a config edit plus new widget renderers. State this in your slice plan so the owner knows the extension point.

ACCEPTANCE: G-41's assertion as a test (every panel flag off yields the strip and nothing else). A test proves no songs, lessons or token strings appear anywhere in the rendered props or markup. Grep proves no scenario-name branching. Playwright covers the badge text, the caps list, the honesty sentence and its title, 320px reflow and the 44px sweep.

==============================================================  
NOTES
======================================================================

* Name collision: SCREEN-017 is the Scenario Race Planner (E5). SCR-017 is the Grand Concert panel (E6, design-2.0 only). The E6 prompt says so in its first line.
* G-33 and the component names: the brief names UraPanel, UnityCupPanel and TrackblazerPanel, which can look like branching on scenario. The prompts allow those file names but require registration against widget or flag keys from config. I didn't have config/scenarios.php, so every prompt says to read the real key names and never guess them.
* Held items the spec still shows: Spirit "recommended timing" and "projected benefit" (E3), Happy Meek "potential reward" and "final-race contribution" (E2), the shop "Recommended Purchase" card (E4), "Win probability", "Expected risk" and the recommendation block (E5). The prompts render each as a named "N/A" absence, or leave it out where the plan says so.
* Blade references: the plan says Phase E keeps the six x-* references, but B1 may delete the zero-consumer ones. E3 and E4 tell the agent to recover them from git history if so.
* Writes: Happy Meek level, Spirit and team state have no known existing write routes. E2 and E3 therefore build those parts read-only if none exists, and report it as an open question. A migration needs owner approval.
* Dependencies: E1 first, then E2, E3, E4 and E6 can run in parallel. E5 needs only D8, and reuses D10's RaceCard if that has landed.
