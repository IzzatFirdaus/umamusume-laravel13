# Screen Specification

Product: **Trainer Desk** (local-only Laravel 13 tool for Trainers of the Global English version of *Umamusume Pretty Derby*). This document is the authoritative screen-level specification: every user-facing screen, its states, actions, validation, access rules, dependencies, and known gaps.

## 1. Purpose & Scope

Scope of this spec:

- All server-rendered Blade screens reachable from `routes/web.php`.
- The shared layout shell (nav, flash, theme, skip link) and the 404 error screen.
- Cross-screen workflows (run lifecycle, guided turn logging, historical import, review resolution).
- Gaps and contradictions found while tracing routes → controllers → views → Form Requests → tests.

Out of scope (summarized only where a screen touches them):

- The fetch engine and its artisan commands (`uma:fetch`, `uma:reparse`, `uma:backup`) — CLI surfaces, not screens; several screens reference them in empty-state copy.
- The read-only JSON API (`/api/v1`) — a machine surface with no visual layer (`DESIGN.md` §4.6 calls it "no visual surface"); recorded in §4.15.
- The `.agents/` skill-automation tooling layer — touches no catalog or run tables (`README.md`).

Repository terminology preserved throughout: **Trainer** (the single user), **trainee** (an Umamusume entry), **run**, **turn**, **deck**, **scenario**, `[Global]` (the Global English release server), **provenance** (source URL + fetch record). The characters are Umamusume, a humanoid race; equine vocabulary is a lore-gate failure (`CONSTRAINTS.md` C-4) and appears nowhere in this document's screen copy.

Evidence labels used below:

- **Implemented** — route + controller + view traced in this tree.
- **Partially Implemented** — screen ships, but a required-behavior element is missing or unresolved (named in its Gaps section).
- **Referenced but Missing** — a route, field, story, or component exists in docs/backend without a reachable UI (or vice versa).
- **Planned / Unknown** — PRD priority tier not yet built, or behavior not established by any repository source.

Access model (applies to every screen, so it is stated once): this tool has **no authentication surface, by design** (PRD NFR-1, PRD §6.1, `ARCHITECTURE.md` §8). One Trainer, one machine, loopback only. Every Form Request's `authorize()` returns `true`. "Unauthorized access" therefore does not exist as a screen state; the only refusals are resource-resolution failures (unknown id/slug → 404) and nested-ownership checks (a turn belonging to another run → 404 via `abort_unless`, `TrainingRunController::updateTurn/destroyTurn`). Environment restriction: the app **must not be exposed** beyond loopback (`README.md`).

## 2. Screen Inventory

Areas used (the repository's own grouping, not forced onto generic categories):

- **Runs** (Trainer-owned data, the primary workflow) — `SCR-RUN-*`
- **Catalog** (engine-written reference data, read-only) — `SCR-CAT-*`
- **Skills** (reference data; the PRD's "Screen D") — `SCR-SKL-*`
- **Support cards** (reference data) — `SCR-SUP-*`
- **Review** (Trainer decisions over engine proposals) — `SCR-REV-*`
- **Career** (the Trainer Desk 2.0 Inertia screens; `ADR-0020` §1) — `SCR-CAR-*`
- **System** — `SCR-SYS-*`

Navigation shell: `resources/js/layouts/AppLayout.vue`, the Inertia SPA shell (`ADR-0020` §1). Ten primary nav destinations in DOM order — Dashboard, New Career, Careers, Legacy Lab, Veterans, Support Cards, Skills, Review, Database, Settings — plus a focus-revealed "Skip to content" link targeting `main#main`, and zero named absences (`to: null`). _Corrected 2026-10-07; the paragraph previously read "Nine … Veterans is a named absence (`to: null`) until the D16 slice lands, so the count is eight live plus one, which is Miller's Law (plan §13)." D16 landed and Veterans became a real screen, "Careers" was added when the run list gained its only inbound link, and the Database hub repointed an existing label rather than adding one. The count is therefore ten live destinations against plan §13's "at or under eight", which is a constraint breach rather than a cosmetic note. Only four carry `inBar` on mobile (Dashboard, Careers, Legacy Lab, Support Cards) and the other six sit behind the More panel, so the ten-row list is the desktop sidebar. Whether that merges is an owner ruling rather than this slice's call._ `/` renders `SCR-CAR-001` (route name `home`), so the Dashboard is the landing screen. The Blade shell this paragraph used to describe (`resources/views/components/layout.blade.php`) was deleted in slice B1; the three error documents render themselves (`AppServiceProvider` composes them in place of the shell).

## 3. Screen Matrix

| ID            | Screen                                           | Area            | Actor     | Access                       | Route                                                                                                                                         | Status                                                                                                                                                                                             |
| ------------- | ------------------------------------------------ | --------------- | --------- | ---------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| SCR-RUN-001   | Training runs (list)                             | Runs            | Trainer   | none required (local-only)   | `GET /training-runs` (`runs.index`)                                                                                                           | Implemented                                                                                                                                                                                        |
| SCR-RUN-002   | New training run                                 | Runs            | Trainer   | none required                | `GET /training-runs/create` (`runs.create`)                                                                                                   | Implemented                                                                                                                                                                                        |
| SCR-RUN-003   | Run detail & turn log                            | Runs            | Trainer   | none required                | `GET /training-runs/{run}` (`runs.show`)                                                                                                      | Implemented (open findings, see gaps)                                                                                                                                                              |
| SCR-RUN-004   | Import a historical run (form)                   | Runs            | Trainer   | none required                | `GET /training-runs/import` (`runs.import`)                                                                                                   | Implemented                                                                                                                                                                                        |
| SCR-RUN-005   | Import preview & confirm                         | Runs            | Trainer   | none required                | `POST /training-runs/import/preview` (`runs.import.preview`)                                                                                  | Implemented                                                                                                                                                                                        |
| SCR-CAT-001   | Umamusume catalog                                | Catalog         | Trainer   | none required                | `GET /umamusume` (`catalog.index`)                                                                                                            | Implemented                                                                                                                                                                                        |
| SCR-CAT-002   | Umamusume profile detail                         | Catalog         | Trainer   | none required                | `GET /umamusume/{slug}` (`catalog.show`)                                                                                                      | Implemented                                                                                                                                                                                        |
| SCR-SKL-001   | Skill search (Screen D)                          | Skills          | Trainer   | none required                | `GET /skills` (`skills.index`)                                                                                                                | Implemented                                                                                                                                                                                        |
| SCR-SKL-002   | Skill detail                                     | Skills          | Trainer   | none required                | `GET /skills/{skill}` (`skills.show`)                                                                                                         | Implemented                                                                                                                                                                                        |
| SCR-SUP-001   | Support-card catalog                             | Support cards   | Trainer   | none required                | `GET /support-cards` (`support-cards.index`)                                                                                                  | Implemented                                                                                                                                                                                        |
| SCR-SUP-002   | Support-card detail                              | Support cards   | Trainer   | none required                | `GET /support-cards/{card}` (`support-cards.show`)                                                                                            | Implemented                                                                                                                                                                                        |
| SCR-REV-001   | Match review queue                               | Review          | Trainer   | none required                | `GET /review` (`review.index`)                                                                                                                | Implemented                                                                                                                                                                                        |
| SCR-VET-001   | Veteran library                                  | Veterans        | Trainer   | none required                | `GET /veterans` (`veterans.index`)                                                                                                            | Implemented 2026-10-06 (D16 read half); favorite, archive, delete and the Spark-graded sorts are named absences. _Corrected 2026-10-07 (D16 write half): this row also said nothing in the build files a Veteran yet. That was true on 2026-10-06 and is now false; `SCR-VET-003` files one_ |
| SCR-VET-002   | Veteran detail                                   | Veterans        | Trainer   | none required                | `GET /veterans/{veteran}` (`veterans.show`)                                                                                                   | Implemented 2026-10-06 (D16 read half); the six-node graph is not re-rendered here, it is one link away in the Legacy Lab                                                                          |
| SCR-VET-003   | Save Veteran                                     | Veterans        | Trainer   | none required                | `GET /training-runs/{run}/veteran` (`runs.veteran`), written by `POST` (`runs.veteran.store`)                                                  | **Built, not landed** 2026-10-07 (D16 write half): props and request gates green, browser spec written and unrun, `npm run build` blocked by another session's `Preferences/Edit.vue`. First caller of `RecordVeteran`. Factor analysis, a legacy value and a best use are held (`ADR-0020` §3) and print `N/A` with the ruling. **Corrected same day: the build cleared, the gate ran on a scratch database, all five cases green.** It found and fixed one axe-only defect: the Japanese name was printed in `text-ink-faint`, a documented non-text token at 2.99:1 |
| SCR-VET-004   | Veteran comparison                               | Veterans        | Trainer   | none required                | `GET /veterans/compare` (`veterans.compare`)                                                                                                  | **Built, not landed** 2026-10-07 (D16): same blocker and same gate state. Up to four careers, recorded values only, and distinct from `legacy.compare`, which compares configurations and needs a read-back. **Corrected same day: the gate ran and is RED.** `Compare.vue:85` sends `router.get` the key `'veterans[]'` over an array, so the request refuses it and `back()` lands on `/veterans` instead of a two-column comparison. Defect named; ruled and fixed at `Compare.vue:86`, and the re-run is 8 passed / 1 failed (exit 1) with the one failure this screen's own URL assertion, which matches `veterans[]=N` while Inertia emits `veterans[0]=N`. Two-column render still unasserted in a browser, so gate 8/9 and not landed |
| SCR-CAR-001   | Dashboard (2.0 landing screen)                   | Career          | Trainer   | none required                | `GET /` (`home`)                                                                                                                              | Implemented 2026-10-06 (D1); panels for builds and legacy-goal gaps are named absences                                                                                                             |
| SCR-CAR-002   | Scenario Selection (wizard step 1)               | Career          | Trainer   | none required                | `GET /career/setup/scenario` (`career.scenario`), written by `PUT` (`career.scenario.store`)                                                  | Implemented 2026-10-06 (D2); all six wizard steps are live now that Preflight landed (`SCR-CAR-010`, D7), so the step nav holds no named absence                                                   |
| SCR-CAR-003   | Trainee Selection (wizard step 2)                | Career          | Trainer   | none required                | `GET /career/setup/trainee` (`career.trainee`), written by `PUT` (`career.trainee.store`)                                                     | Implemented 2026-10-06 (D3); the growth-rate and scenario-suitability filters are omitted, no column holds either                                                                                  |
| SCR-CAR-004   | Trainee Profile (wizard step 2, read screen)     | Career          | Trainer   | none required                | `GET /career/setup/trainee/{umamusume}` (`career.trainee.profile`)                                                                            | Implemented 2026-10-06 (D3); career goals, growth rates, hint skills and evolution skills are omitted by ruling; version, stat distribution, inheritance and support types are named absences      |
| SCR-CAR-005   | Build Target (wizard step 3)                     | Career          | Trainer   | none required                | `GET /career/setup/target` (`career.target`), written by `PUT` (`career.target.store`)                                                        | Implemented 2026-10-06 (D4); the brief's fifth purpose (Competitive Build), risk tolerance and per-skill marks are not recorded, each stated on the page                                           |
| SCR-CAR-006   | Legacy Lab (browse, run builder and compare)     | Career          | Trainer   | none required                | `GET /legacy` (`legacy.index`), `GET`/`PUT /legacy/{run}` (`legacy.builder`, `legacy.update`), `GET /legacy/compare` (`legacy.compare`)       | Implemented 2026-10-05 (D5 run-scoped, `05e9584`); record only, and registered on the pass that filed `SCR-CAR-008`                                                                                |
| SCR-CAR-007   | Support Deck Builder (run-scoped)                | Career          | Trainer   | none required                | `GET /training-runs/{run}/deck` (`runs.deck`), written by `POST` (`runs.deck.sync`)                                                           | Implemented 2026-10-05 (D6 run-scoped, `05e9584`); the ownership flag is client-side only, disclosed as such                                                                                       |
| SCR-CAR-008   | Legacy Select (wizard step 4)                    | Career          | Trainer   | none required                | `GET /career/setup/legacy` (`career.legacy`), written by `PUT` (`career.legacy.store`)                                                        | Implemented 2026-10-06 (D5-wizard); the six-node graph as nested list items, record-only, and the two library picks carried in the draft's `legacy_parents` key                                    |
| SCR-CAR-009   | Support Deck Select (wizard step 5)              | Career          | Trainer   | none required                | `GET /career/setup/deck` (`career.deck`), written by `PUT` (`career.deck.store`)                                                              | Implemented 2026-10-06 (D6-wizard); six slots with OWNED/RENTED in the draft, seven types, the D6 analysis beside them                                                                             |
| SCR-CAR-010   | Career contract, "Preflight" (wizard step 6)     | Career          | Trainer   | none required                | `GET /career/setup/preflight` (`career.preflight`), written by `PUT` (`career.preflight.store`)                                               | Implemented 2026-10-06 (D7); the wizard's one write, composing the five entered steps and creating the run                                                                                         |
| SCR-CAR-011   | Career Cockpit (the run-scoped primary screen)   | Career          | Trainer   | none required                | `GET /training-runs/{run}/cockpit` (`runs.cockpit`), the one write posting to `PUT /training-runs/{run}/turns/{turn}` (`runs.turns.update`)   | Implemented 2026-10-06 (D8); read-only, the action grid's seven entries each link to the screen that owns the action today, and the milestone timeline and the scenario panel are named absences. *Dated note 2026-10-07 (E1): the second absence is closed. The scenario region mounts `ScenarioPanel.vue` (`SCR-CAR-019`); the milestone timeline stays an absence in this region, where the left column renders the run race strip.* |
| SCR-CAR-012   | Training Decision detail                         | Career          | Trainer   | none required                | `GET /training-runs/{run}/training` (`runs.training`), the one write posting to `POST /training-runs/{run}/turns` (`runs.turns.store`) | Implemented 2026-10-07 (D9); five option cards, the per-training yield and the failure rate held unsourced and rendered `N/A` with the exclusion in the `title` |
| SCR-CAR-013   | Race Decision                                    | Career          | Trainer   | none required                | `GET /training-runs/{run}/races` (`runs.races.decision`), the one write posting to `POST /training-runs/{run}/races` (`runs.races.store`) | Implemented 2026-10-07 (D10); the races at the turn being decided, the win figure held on `ADR-0016` and the readiness band pending the owner's ruling, both rendered `N/A`, and four of the brief's ten fields named absent with their reason |
| SCR-CAR-014   | Event Decision                                  | Career          | Trainer   | none required                | `GET /training-runs/{run}/events` (`runs.events.decision`), the one write posting to `POST /training-runs/{run}/events` (`runs.events.store`) | Implemented 2026-10-07 (D11); record-only: known outcomes are derived from this run's own recorded choices, the advisor refuses with its reason, and a choice without a recorded outcome carries the incomplete warning; no score or probability is computed |
| SCR-CAR-015   | Inheritance Event                                | Career          | Trainer   | none required                | `GET /training-runs/{run}/inheritance` (`runs.inheritance`), written by `POST` (`runs.inheritance.store`) | Implemented 2026-10-07 (D12); record-only: predicted section shows sourced Spark probabilities and star-roll odds, observed section records the Trainer's entered inspiration outcomes via TurnEvent, milestone timeline with three fixed events |
| SCR-CAR-016   | Skills Planner                                   | Career          | Trainer   | none required                | `GET /training-runs/{run}/skills` (`runs.skills.planner`), the one write posting to `PUT /training-runs/{run}/build-target` (`runs.build-target.update`) | Implemented 2026-10-07 (D13); the four skill states, the SP coverage warning, the sourced hint ladder priced per skill, and the race-fit cells that compare only what a source maps |
| SCR-CAR-017   | Career Timeline                                  | Career          | Trainer   | none required                | `GET /training-runs/{run}/timeline` (`runs.timeline`)                                                                                         | Implemented 2026-10-07 (D14); read-only, corrections flagged by timestamp comparison because the model has no prior-values column, no separate corrections log, and the `◉`/`○` glyphs are reserved for Phase E deadlines |
| SCR-CAR-018   | Career Result                                    | Career          | Trainer   | none required                | `GET /training-runs/{run}/result` (`runs.result`)                                                                                             | Implemented 2026-10-07 (D15); "build quality" omitted as a ruling (a target-completion percentage, a skill-coverage figure and an inheritance-quality figure are all held computation), Save Veteran is a door to `SCR-VET-003`, and the deficit is `TrainerAdvisor::deficits()` rather than a second copy |
| SCR-CAR-019   | Scenario panel shell                             | Career          | Trainer   | none required                | mounted inside `SCR-CAR-011` (the Cockpit's scenario region); no route of its own                                                                                                       | Implemented 2026-10-07 (E1), browser gate green at 8 passed: branch-free registry per gate G-33, no renderer registered yet, every widget falls to `ResourceMeter` or `WidgetFallback`, and the PARTIALLY DOCUMENTED badge does not render today because all four Global scenarios are documented or silent. *Dated note 2026-10-07 (E2): "no renderer registered yet" is superseded. `career_goals` now resolves to `UraPanel.vue` (`SCR-CAR-020`); widgets still fall to `ResourceMeter`, and the flags E3 and E4 own still fall to `WidgetFallback`.* *Dated note 2026-10-07 (E6): the badge's partial arm renders. The badge reads `partially_documented`, per the owner's ruling at plan §4.1 item 6, and the strip that arm belongs to is `SCR-CAR-024`.* |
| SCR-CAR-020   | URA panel (SCREEN-014)                           | Career          | Trainer   | none required                | mounted by `SCR-CAR-019` inside the Cockpit's scenario region, reached by the `career_goals` flag; no route of its own                                                                        | Built 2026-10-07 (slice E2), browser gate green at 10 passed. Three modules, two of them named absences. Career goals are unrecorded (no `trainee_goals` table) and Happy Meek has no stored level, so both render `N/A` with the reason; the mandatory race set is the one module with real data, read through `RaceCatalogSlot::isAtOrBeforeTurn()` |
| SCR-CAR-021   | Unity Cup panel — the Team Cockpit (SCREEN-015)  | Career          | Trainer   | none required                | mounted by `SCR-CAR-019` inside the Cockpit's scenario region, reached by the `team_race` and `team_rank_ladder` flags; no route of its own                                                  | Built 2026-10-07 (E3); read-only — no UI write records a team rank, a burst state or a Spirit reading, so every recorded figure the run lacks renders `N/A` or a named absence, and the burst-band table renders with the Extreme-counting caveat of run report §8.2 |
| SCR-CAR-022   | Trackblazer panel (SCREEN-016)                   | Career          | Trainer   | none required                | mounted by `SCR-CAR-019` inside the Cockpit's scenario region, reached by the `grade_objectives`, `shop` and `epithet_routes` flags; no route of its own                                  | Built 2026-10-07 (slice E4); read-only mirror of `TurnEvents\ShopPurchasePayload` for purchases, of `TrainingRun::gradePeriods()` for grade rows, and of `TrainingRun::epithetProgress()` for the checklist. Catalogue is config order; no `recommended` flag, no "best value" sort, no default selection, and the shop rotation is named absent in copy. The Cockpit's action area gains a "Shop" button (Trackblazer only) that scrolls and focuses the Shop heading. The official finale name "Twinkle Star Climax" is §7 conflict row 31 (UNVERIFIED) and renders as a named absence, never as a string. |
| SCR-CAR-023   | Scenario Race Planner (SCREEN-017)               | Career          | Trainer   | none required                | `GET /training-runs/{run}/races/planner` (`runs.races.planner`), the one write posting to `POST /training-runs/{run}/races` (`runs.races.store`) | Implemented 2026-10-07 (slice E5); the whole calendar in four groups (mandatory / upcoming / optional / rival, the last a named absence because no column marks a rival race), a reward comparison of up to four selected races with the ten aligned rows, and the target alignment in plain words with no score and no percentage. Win probability and expected risk render `N/A` with the `ADR-0016` blocker in the `title`; the brief's recommendation block is not built, and the deadline region states the next obligation and its turn rather than promising none will be missed |
| SCR-CAR-024   | Our Grand Concert baseline strip (SCR-017)        | Career          | Trainer   | none required                | mounted by `SCR-CAR-019` inside the Cockpit's scenario region, drawn where every panel flag is off; no route of its own                                  | Built 2026-10-07 (slice E6); the strip the panel region draws when a scenario composes no panel, which is the fourth `[Global]` scenario's whole surface. The badge reads `partially_documented` (owner ruling 2026-10-07), the five published caps render as base + bonus + cap through `ScenarioCaps`, and the scenario's own one-line reason its panels are off comes from config. No song, lesson or token string renders: the mechanics are sourced, the client's screen text is not measured |
| SCR-SYS-001   | Page not found (404)                             | System          | Trainer   | none required                | error rendering for any 404                                                                                                                   | Implemented                                                                                                                                                                                        |
| SCR-SYS-002   | Preferences                                      | System          | Trainer   | none required                | `GET /preferences` (`preferences.edit`), written by `PUT /preferences` (`preferences.update`); Export by `GET /preferences/export` (`preferences.export`), Backup by `POST /preferences/backup` (`preferences.backup`)                                                 | Implemented 2026-10-04; extended 2026-10-07 (D18) with category shell and Game version panel (§7-5); extended 2026-10-08 (D18b) with the four stored preferences in one `settings` row, the Export stream and the server-side Backup, and the absence reasons named per class (Units is a sourcing absence, not a schema one)                                                                                                                                                                     |
| SCR-SYS-003   | Session expired (419)                            | System          | Trainer   | none required                | error rendering for any 419                                                                                                                   | Implemented 2026-10-04 (§7-8)                                                                                                                                                                      |
| SCR-SYS-004   | Server error (500)                               | System          | Trainer   | none required                | error rendering for any uncaught failure                                                                                                      | Implemented 2026-10-04 (§7-8); standalone document, no database read                                                                                                                               |
| —             | Design preview                                   | System          | —         | —                            | `GET /design-preview`                                                                                                                         | Deleted; README's stale row removed 2026-10-04 (§7-1)                                                                                                                                              |
| SCR-SYS-005   | Database hub                                     | System          | Trainer   | none required                | `GET /database` (`database.index`)                                                                                                           | Implemented 2026-10-07 (D17); links to Trainees, Support Cards, Skills, Races, Scenarios; three render the shared catalog component at their own `/database/*` address (no redirect, none ruled), two are new: Races from `RaceCatalogSlot` (`SCR-SYS-006`) and Scenarios from `config/scenarios.php` (`SCR-SYS-007`). Extended 2026-10-08 to eight area links: Events, Shop Items and Sparks. The "Not in this build" section that named those three as absences is deleted, not softened. |
| SCR-SYS-006   | Race database                                    | System          | Trainer   | none required                | `GET /database/races` (`database.races`)                                                                                                     | Implemented 2026-10-07 (D17); paginated `race_catalog_slots`, the offline state names the fetch command, unrecorded cells render `N/A` with their reason, and the wide table scrolls inside its own focusable region at 320px. No win probability and no readiness band (`ADR-0016`)                   |
| SCR-SYS-007   | Scenario matrix                                  | System          | Trainer   | none required                | `GET /database/scenarios` (`database.scenarios`)                                                                                              | Implemented 2026-10-07 (D17); prints `config/scenarios.php` as resolved (caps base+bonus+sum, widgets, every panel on or off as a word, `live_on_global`, `partially_documented`), with the config's `verified_at` labelled as a verification date. No scenario name in the page (D-240, gate G-33)                   |
| SCR-SYS-008   | Events reference                                 | System          | Trainer   | none required                | `GET /database/events` (`database.events`)                                                                                                   | Implemented 2026-10-08; ten recurring event types read from `config/reference.php`, transcribed from `docs/UMAMUSUME_REFERENCE.md` §4.4. Read-only, no write path, no filter. Server tags print as bracketed text, each row carries a `ProvenanceBadge`, and the §48 metadata row names the doc section, the anchor date and how to re-check it. Not an ingest: the config is a dated snapshot the tool never refreshes. |
| SCR-SYS-009   | Shop Items reference                             | System          | Trainer   | none required                | `GET /database/shop-items` (`database.shopItems`)                                                                                             | Implemented 2026-10-08; ten catalogue rows from §1.6.10 (eight consumables plus the two currency items that section describes in prose), with kind and spend-site as words. The Trackblazer Pro Shop block below them is the same `config/scenarios.php` data the scenario matrix prints, reused rather than retyped, so the two destinations cannot disagree. No purchase form and no prices this tool did not record. |
| SCR-SYS-010   | Sparks reference                                 | System          | Trainer   | none required                | `GET /database/sparks` (`database.sparks`)                                                                                                    | Implemented 2026-10-08; six Spark categories from §1.5.2 with their record counts, the JP name in a `lang="ja"` span, and the three-band star-roll odds table from §1.5.3/§1.5.4 inside its own focusable scroll region. Odds are printed as the source words them ("about 50%"), never as a computed probability. No `factor` in any displayed string (`lore-code`). |

Action endpoints (no screens of their own, owned by the screens that host their forms): `runs.store`, `runs.update`, `runs.destroy`, `runs.export/{format}`, `runs.turns.store`, `runs.turns.update`, `runs.turns.destroy`, `runs.skills.sync`, `runs.deck.sync`, `runs.races.store`, `runs.purchases.store`, `runs.import.store`, `review.resolve`, `preferences.update`. As of 2026-10-04 every one of these has a form posting to it; the two that did not, `runs.turns.update` and `runs.turns.destroy`, gained the turn-row editor (§7-2).

## 4. Screen Specifications

---

### SCR-RUN-001 — Training runs (list)

#### Purpose

The landing screen. Lists every run the Trainer has logged, newest first, and offers the two ways a run enters the system: create one now, or import one from a finished career's CSV.

#### User / Actor

Trainer (the only actor in the product).

#### Access & Authorization

No auth. Reads `TrainingRun::with('umamusume')->latest()->paginate(25)`.

#### Entry Points

Primary nav "Careers"; `/` redirect; 404 page button; redirect target after deleting a run. The nav entry is new with the 2.0 link audit, which also corrected this row: it named the retired Blade shell's "Training runs" label while the SPA nav held no entry at all, so the list's only inbound path was the post-delete redirect.

#### Route / Location

`GET /training-runs`, name `runs.index`.

#### Layout / Structure

Page title → action row (text link "Import a historical run", primary pill "New run") → run list (or empty state) → pagination links.

#### Data Displayed

Per row: trainee name, scenario label after a `·` separator (only when `hasScenario()`; the raw slug is never printed — config label is read via `config('scenarios.scenarios.{key}.label')`), run status label, creation date (`toDateString`).

#### Inputs

None.

#### User Actions

| Action     | Trigger     | Result        | Navigation      | Feedback   |
| ---------- | ----------- | ------------- | --------------- | ---------- |
| Open run   | row link    | Career Cockpit | `runs.cockpit` | —          |
| New run    | pill        | create form   | `runs.create`   | —          |
| Import     | text link   | import form   | `runs.import`   | —          |

*Dated correction 2026-10-07 (F1, career surface cutover): the Open run row pointed at `runs.show` (SCR-RUN-003, the 0.1.0 run-detail page) and now carries `runs.cockpit` (`SCR-CAR-011`), because selecting a career from the list must land on the 2.0 Cockpit directly. `runs.show` remains registered as a compatibility doorway pending the owner's ruling on the redirect itself, whose blast radius — 38 feature files and 9 browser specs' afterEach cleanups still depending on SCR-RUN-003 rendering — is recorded in this slice's hand-off.*

#### Screen States

Initial with rows; **empty** (deliberate copy: "No runs yet … there is nothing to list until you start one" — an empty list is framed as a real state, not a failure); paginated (>25 rows).

#### Validation & Error Handling

None (no inputs). Deleted-run direct URLs resolve through route-model binding to 404 → SCR-SYS-001.

#### Security & Privacy

Trainer-owned data only; nothing sensitive. Runs are never cached (Trainer-data reads are never cached; `ARCHITECTURE.md` §6).

#### Accessibility

Nav links carry `min-h-11` (44px) targets. Empty state is plain prose (screen-reader accessible). Standard link semantics.

#### Responsive Behavior

Title/action row uses `flex-wrap`; row content `flex-wrap` so the date wraps under the name at narrow widths. No breakpoint-specific restructure.

#### Localization / Content

Hardcoded English in the component. Status label from `RunStatus::label()` (`lang/en/uma.php`), server-formatted onto the row. Dates are date-only (no timezone conversion). Scenario labels come only from `config/scenarios.php` (D-240: a scenario name must not be hardcoded in a view), and the row carries the label rather than the storage key.

#### Navigation & Workflow Relationships

`runs.create`, `runs.import`, `runs.show` forward; it is the success destination of run deletion.

#### Dependencies

`TrainingRun`, `Umamusume` models; `config/scenarios.php` for labels.

#### Implementation References

Page: `resources/js/pages/Runs/Index.vue`; Controller: `TrainingRunController::index`; Route: `routes/web.php:29`; Tests: `RunsIndexTest.php` (props), `tests/browser/runs.spec.ts` (empty state and the action controls).

#### Current Status

Implemented.

#### Open Questions / Gaps

None specific. (Filter/search over runs is not built and is not required by any story.)

---

### SCR-RUN-002 — New training run

#### Purpose

Start a run: pick a trainee (and optionally the costume card the career was started on), name the scenario, set initial status, and optionally record inheritance parents, the Grade Point period, shop countdown, and notes.

#### User / Actor

Trainer.

#### Access & Authorization

No auth. The trainee option list is restricted to `release_status = GlobalReleased`; costume cards to `unconfirmed = false` and ordered by debut-first (`cardScope` equivalence with the catalog, stated in the controller). A cardless trainee is still selectable and commits with `character_card_id` empty.

#### Entry Points

`runs.index` "New run" pill; `catalog.show` "Her runs → New run" (does **not** preselect the trainee — the page's own helper copy says so, `Catalog/Show.vue`); back button from import.

#### Route / Location

`GET /training-runs/create`, name `runs.create`; submits to `POST /training-runs` (`runs.store`).

#### Layout / Structure

Page title → single form card: Trainee combobox, Scenario select, Status select, inheritance A/B selects, Grade Point period number, Shop resets number, Notes textarea, submit.

#### Data Displayed

The trainee roster as an Inertia prop (English names, plus per-trainee Japanese names and card titles / release dates / debut flags). It is also the source the inheritance-parent selects read, so the page holds one trainee list rather than two that can drift.

> **Retired 2026-10-05 (owner ruling, `ADR-0020` §1).** This field used to ship two pickers: a native
> `select` for a browser with scripting off, and a combobox a module switched on by disabling the
> first and enabling a hidden `umamusume_id` / `character_card_id` pair. A client-rendered page has no
> no-script path — with scripting off there is no page at all — so the fallback, the JSON script island
> that fed the module, and the handover between the two went with the Blade view. The combobox is the
> control.

#### Inputs

| Field                                 | Type                                                           | Req        | Default                      | Validation (source: `StoreTrainingRunRequest`)                                                                                                   |
| ------------------------------------- | -------------------------------------------------------------- | ---------- | ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `umamusume_id`                        | combobox (`role=combobox` input over a `role=listbox` popup)   | required   | —                            | integer, exists `umamusume.id`                                                                                                                   |
| `character_card_id`                   | combobox commit (a cardless trainee writes nothing)            | optional   | —                            | integer, exists `character_cards.id` **scoped to the submitted trainee** (join in the rule)                                                      |
| `scenario`                            | select                                                         | optional   | "Not set (baseline strip)"   | in `config('scenarios.php')` keys; `''` normalizes to null                                                                                       |
| `status`                              | select                                                         | required   | `Active`                     | enum `RunStatus`                                                                                                                                 |
| `inheritance_parent_a_id` / `_b_id`   | select                                                         | optional   | none                         | exists `umamusume.id`                                                                                                                            |
| `current_objective_index`             | number 1..`RaceEntry::MAX_OBJECTIVE_INDEX`                     | optional   | empty                        | in range; cross-field closure: refused when the scenario's `panels.grade_objectives` is not true ("This scenario has no Grade Point periods…")   |
| `shop_resets_in`                      | number min 0                                                   | optional   | empty                        | nullable int ≥ 0 (scenario upper bound enforced at model layer)                                                                                  |
| `notes`                               | textarea                                                       | optional   | empty                        | max:5000                                                                                                                                         |

Required marker convention: `*` on fields with no answer of their own, `(optional)` in captions that do (audit I-2).

#### User Actions

| Action                   | Trigger                                     | Result                                                                                                                       | Feedback                                     |
| ------------------------ | ------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------- |
| Create run               | submit                                      | run row + card's Global-released innate/unique skills seeded as `Suggested` ("Starting" label), in one transaction (KI-33)   | redirect `runs.show`, flash "Run created."   |
| (Combobox pick)          | typing, arrows, Enter or a click on a row   | filters the trainee/card list and commits the pair the form posts                                                            | live `aria-live="polite"` status line        |
| (Enter with no cursor)   | Enter while nothing is highlighted          | the form submits; it is not a silent re-pick of row zero                                                                     | native submission                            |

#### Screen States

Initial (popup closed, live region silent: a count stated before anyone opened the list reports a list nobody asked for); validation-error re-render with the flashed input rehydrating every field, the combobox re-painting its label from the `umamusume_id` / `character_card_id` pair rather than from a stored string; in-flight (`aria-busy`) while the write is posted.

#### Validation & Error Handling

Server-authoritative (`StoreTrainingRunRequest`), errors rendered per field from the Inertia `errors` prop. A committed label the Trainer edits away clears the posted pair, so the next submit fails `required` rather than writing a run that disagrees with its own input (the server cannot see that disagreement: trainee and card agree with each other). Duplicate/mismatch cases: card not owned by trainee → field error. Empty scenario → null, not error. No client-side blocking beyond native `required` on the status select (kept no-looser-than-server, audit I-1/I-2 rule).

#### Security & Privacy

CSRF through the framework XSRF cookie header Inertia sends with every write. All user-entered content is Trainer's own; card titles are source data and reach the page through Vue interpolation, which escapes them.

#### Accessibility

Combobox implements `role=combobox` with `aria-expanded`, `aria-controls`, `aria-autocomplete="list"` and `aria-activedescendant`, over a `role=listbox` that carries its own accessible name, with `aria-required` on the input (the value that posts is the committed pair, not the text typed). The caption's `for` points at the input and the caption text is a prefix of the accessible name, so a speech-input user can say the words on screen (WCAG 2.5.3). Per-trainee headers and the band seam are `role=presentation` and `aria-hidden`, which puts each trainee's name in her options' own accessible names rather than in a group that owns nothing. Every control meets the 44px floor (`h-11` / `min-h-11`, KI-37). Tests: `tests/Feature/TraineeSelectorTest.php` (the payload and the POST round trip) and `tests/browser/runs.spec.ts` (the ARIA surface, the keyboard path, the cap, the commit, the stale-pair rule and the rehydration), `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` C-1…C-4 closed same-day 2026-10-03.

#### Responsive Behavior

Form capped `max-w-lg`. No dedicated breakpoint behavior.

#### Localization / Content

Hardcoded English. `·` middle dot as separator (em/en dash banned in shipped copy, R-02). Japanese names shown only in the combobox payload (list rows are English names).

#### Navigation & Workflow Relationships

Success → SCR-RUN-003. Sibling entry SCR-RUN-004 (import) shares its Form Request contract via inheritance (`ImportHistoricalRunRequest extends StoreTrainingRunRequest`).

#### Dependencies

Models `Umamusume`, `CharacterCard`, `TrainingRun`, `Skill` (pre-populate through `Skill::availableOnGlobal()`); `config/scenarios.php`; component `resources/js/components/TraineeCombobox.vue`.

#### Implementation References

Page: `resources/js/pages/Runs/Create.vue` (+ `components/TraineeCombobox.vue`); Controller: `TrainingRunController::create/store`; Request: `app/Http/Requests/StoreTrainingRunRequest.php`; Tests: `TraineeSelectorTest.php`, `RunCreateSurfaceTest.php`, `TrainingRunTest.php` (create + pre-populate cases), `tests/browser/runs.spec.ts`.

#### Current Status

Implemented.

#### Open Questions / Gaps

- `legacy_selection` (ADR-0010, `TrainingRun` fillable + `LegacySelectionPayload`) is stored and modeled but **no form field collects it on this screen** — Referenced but Missing (§7-3).
- Audit C-5 ("the request validates four fields the create form never sends") is resolved for inheritance/objective/shop fields; the legacy-selection half remains.

---

### SCR-RUN-003 — Run detail & turn log

#### Purpose

The work surface for one run: read the run's current state (resources, stats, mood), log turns through a guided two-stage rail (with a raw escape hatch), maintain skills (plan-vs-actual), record the equipped support deck, record races against the career calendar or by hand, record Trackblazer shop purchases, report the Grade Point period and shop rotation, export, and delete.

#### User / Actor

Trainer.

#### Access & Authorization

No auth. `{run}` route-model bound; unknown id → 404. Nested writes re-check ownership (`abort_unless($turn->training_run_id === $run->id, 404)`).

#### Entry Points

`runs.index` rows; flash redirects from its own actions; `catalog.show` "Her runs"; import commit redirect; export links.

#### Route / Location

`GET /training-runs/{run}` name `runs.show`. Query-string screen state: `?entry_mode=calendar|manual` (race panel branch), `?year=N` (career-year tab), `?deck_slot=N` (open deck slot), `?skill_row=N` (open skill row). Writes: `runs.update` (PUT), `runs.destroy` (DELETE), `runs.export/{format}` (GET), `runs.turns.store` (POST), `runs.skills.sync` (POST), `runs.deck.sync` (POST), `runs.races.store` (POST), `runs.purchases.store` (POST).

#### Layout / Structure

1. Status banner (`role="status"`, only after a save redirect back).
2. Header: trainee name + status label + scenario label (or "No scenario set"); export CSV/JSON links; import provenance line when `imported_at` is set.
3. Notes line when set.
4. Pinned **Resources** strip (`lg:sticky`, pinned at ~176px after fix `f5a91b2`) + Mood pill beside it ("not recorded" when absent).
5. **Stats** band (scrolls; renders only when ≥1 turn logged — five zeros would be a claim; D-220).
6. Scenario-composed panels (only when the run names a scenario): race calendar (gated `panels.race_calendar`), grade-point meter, shop panel, race panel, team-rank gauge, spirit-burst roster, team-race panel, epithet checklist, race-fatigue chip. Each component self-gates on the composition matrix (`config/scenarios.php`), so a scenario that does not open a mechanic renders nothing (D-221, G-34).
7. **Turn log** section: "Change scenario" form → "Report period" form (when the scenario composes grade objectives) → Support deck (`x-deck-panel`) → Turns heading + empty note or nine-column table (Turn, Speed, Stamina, Power, Guts, Wit, SP, Condition, Mood; failure chips with penalty kind) → guided rail (`x-guided-step`) with the turn-entry grid → error envelope list → "Correct a turn by hand" `<details>` escape hatch → Skills section → delete-run `<details>`.

#### Data Displayed

- Resource strip widgets per scenario (`turn`, `energy`, `fans`, plus `team_rank`, `spirit_bursts` on Unity Cup); an unrecorded value renders `N/A` + `title`, never a default or dash.
- Stat band: latest turn's five stats against scenario caps (`ScenarioCaps::forRun`, same call the validator makes, KI-47 fixed), 1200 halving marker as its own visible term (ADR-0002 UI condition), skill points.
- Mood pill: five client tiers GREAT…AWFUL with directional arrow (color is not the only channel, D-12/D-259).
- Skill lists grouped Starting(`Suggested`)/Acquired/Skipped with ✦ Unique mark + word, SP cost, turn acquired.
- Deck rows: card display name, slot label (position 6 always reads `Friends`), rarity/type words, derived `Scenario Link` badge, at-cap effects.
- Race rows: title, status, "Trainer-entered" mark, grade/placement/turn/circles/period as applicable.
- Shop: spend total ("Spent: N coins"), rotation countdown, catalogue rows (Trackblazer only).
- Provenance for imported runs: `Imported {date} from {import_source}`.

#### Inputs

| Group            | Fields                                                                                                                                                                                   | Validation source                            |                                      |                        |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------- | ------------------------------------ | ---------------------- |
| Change scenario  | `scenario` (hidden carry: `umamusume_id`, `status`)                                                                                                                                      | `StoreTrainingRunRequest`                    |                                      |                        |
| Report period    | `current_objective_index` select incl. "Not reported"                                                                                                                                    | same (cross-field scenario rule)             |                                      |                        |
| Guided turn rail | `choice` (radio), then `turn`, `speed`, `stamina`, `power`, `guts`, `wit`, `sp`, `energy` (post-turn total), `fans`, `mood`, `outcome`, `penalty_kind`, plus `stage`/`previewed` markers | `StoreTurnEntryRequest`                      |                                      |                        |
| Raw escape hatch | `turn`, five stats, `sp`, `condition`                                                                                                                                                    | same request, no `stage` (hatch stays valid) |                                      |                        |
| Skill rows       | `skills[N][skill_id                                                                                                                                                                      | status                                       | turn_acquired]` (N rows + one spare) | `StoreRunSkillRequest` |
| Deck             | `deck[1..6][support_card_id]`                                                                                                                                                            | `StoreDeckRequest`                           |                                      |                        |
| Race             | `entry_mode`, calendar ids or manual `title/month/half/tier`, `status`, `placement`, `fans_gain`, `circles`, `objective_index`, `turn_entry_id`                                          | `StoreRaceEntryRequest`                      |                                      |                        |
| Shop purchase    | `turn`, `item`, `cost`, `effect`                                                                                                                                                         | `StoreShopPurchaseRequest`                   |                                      |                        |
| Delete run       | confirm disclosure then submit                                                                                                                                                           | —                                            |                                      |                        |

Stat bounds: each of the five stats `0..scenario ceiling` (`ADR-0015`: base cap + scenario bonus; run with no scenario stays at 1200; the browser `max` on inputs mirrors the same caps so client and server never disagree); SP non-negative and uncapped; energy `0..100`; mood enum; fans ≥0. Turn unique per run (ignoring the edited row).

#### User Actions

| Action                     | Trigger                       | Preconditions                                                                                                                                | Result                                                                                                                                                                                                                 | Feedback                                              |
| -------------------------- | ----------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| Preview this turn          | rail submit `stage=preview`   | nothing writes (D-51)                                                                                                                        | PRG redirect back to `runs.show` with flashed input; rail re-renders in outcome step with delta bubbles (entered minus previous stored turn; zeros omitted; first turn has nothing to subtract and the rail says so)   | —                                                     |
| Confirm turn               | rail submit `stage=confirm`   | `previewed=1` marker must have been rendered first (server-enforced UX guard, not security)                                                  | turn row written; a `Failure` outcome also writes a `turn_events` Failure row (penalty kind + recorded down-deltas + origin note "Logged from the client, not modelled")                                               | status "Turn N logged."; rail resets to choice step   |
| Save correction            | raw form submit               | writes on submit, no preview                                                                                                                 | turn created                                                                                                                                                                                                           | status message                                        |
| Save skill status          | skills form                   | upsert semantics (`syncWithoutDetaching`): unlisted skills keep state; only `availableOnGlobal` skills writable                              | rows updated                                                                                                                                                                                                           | "Skill status saved."                                 |
| Save deck                  | deck form                     | all six slots posted (blanks are deliberate clears); delete-then-insert in one transaction; duplicate card refused                           | deck replaced                                                                                                                                                                                                          | "Deck saved."                                         |
| Record race                | race panel                    | calendar branch names a slot on **this run's** scenario/year; manual branch creates a `free_race` slot too; one entry may not set both ids   | `race_entries` row (+slot)                                                                                                                                                                                             | "Race recorded."                                      |
| Record purchase            | shop panel                    | item in the scenario catalogue at the stated price; holding-cap counted from stored rows                                                     | `turn_events` Scenario row                                                                                                                                                                                             | "Purchase recorded." + field errors                   |
| Report period / rotation   | respective forms              | shared request carried through                                                                                                               | run updated                                                                                                                                                                                                            | status banner                                         |
| Export CSV / JSON          | header links                  | format ∈ {csv, json} else 404                                                                                                                | attachment download `run-{id}.{format}`                                                                                                                                                                                | —                                                     |
| Change scenario            | scenario form                 | matrix keys                                                                                                                                  | run updated                                                                                                                                                                                                            | "Run updated."                                        |
| Delete run                 | disclosure → submit           | permanent, cascade to turns/skills                                                                                                           | run gone                                                                                                                                                                                                               | redirect list + "Run deleted."                        |

Rail keyboard path: digits 1–9 choose an activity, arrows rove (native radio group), Enter previews, Escape returns focus to the choices — advertised on-screen, never folklore; module `resources/js/guided-flow.ts`; everything degrades with scripting off (buttons are plain submits).

#### Screen States

No turns yet (band absent, rail still offers the door, empty-list line "No turns logged yet. Add the first one below."); loaded; choice step → outcome/preview step (server state via `previewed`, not client state); first-turn preview (empty deltas, committable); validation error (input rehydrated into the rail and/or hatch, error envelope list under the rail, `role`-consistent single treatment); deck empty = named absence with instruction; skills empty catalogue = KI-51 first-run message naming `uma:fetch gametora-skills` / `uma:reparse …`; race slots empty ("Races are fetched data, and this career year has none of them." / `No races recorded for this run.`); Energy `null` = "Energy not yet recorded" (no five empty cells); Energy band Safe(>50)/Caution(≥30)/Danger(<30, stated as the tool's own ruling — client publishes no threshold below 50); low-Energy Hint advisory under 50 with its arithmetic shown; unauthorized state does not exist (no auth).

#### Validation & Error Handling

As tabled above; failure path for every write is redirect-back with `old()` rehydration (the rail and the escape hatch rehydrate independently, never each other's fields — `showData` is gated on the rail's `stage` marker to keep that true). Business refusals surface as field errors, not exceptions (audit B1 moved the circles refusal into the Form Request so a Trainer-correctable mistake is recoverable). Model-layer guards stay behind request rules for non-UI writes.

#### Security & Privacy

All writes CSRF-protected. Deck/skill catalogues include only `[Global]` rows on the pickers, while a card/skill already on the run is re-offered so logging an older deck never dead-ends (server comments state this). Turn-acquired, placement, circles etc. are Trainer statements; no third-party data displayed. No rate limits (local tool).

#### Accessibility

Skip link; pinned strip below 25% viewport (measured 176px, O-2 closed); each panel its own `section` with `aria-label` (Run state, Resources, Stats, Skills, Race calendar, Turn log — O-4 closed); turn table inside a focusable scroll region (`role=region tabindex=0 aria-label="Turn log"`, KI-25 convention shared with the import table); status region `role=status`, error lists and race/shop refusals `role=alert`; failure chips carry the word "Failed" (D-12); Unique mark ✦ plus word; `h-11`/44px controls and Screen-D focus ring on the skill form (KI-37); per-row `<label for>` on skill controls (KI-36); `step="1"` steppers (DESIGN.md §6.14); previous-turn placeholders, never pre-filled values (an input arriving with a number asserts a fact, D-220). Tests: `KeyboardPathTest`, `RunViewFrameTest`, `RunViewTargetSizeTest`, `RunViewNoScriptTest`, `RunSaveConfirmationTest`, `GuidedTurn*` family.

#### Responsive Behavior

`lg:` sticky only at ≥1024px; below `lg` everything stacks one column. Turn-entry grid `grid-cols-2 md:grid-cols-4`. Nine-column turn table scrolls horizontally rather than reflowing. Race panel options measured against 390px viewport (`min-w-0` allowances). Shell `max-w-5xl`. A systematic responsive pass is recorded as **not** done (`docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` "What this audit could not verify").

#### Localization / Content

Group label `Suggested` renders as **Starting** (O-11/R-6 copy ruling in `lang/en/uma.php`; DB value unchanged). Rest/Recreation activity names are client-captured strings (I-3); no invented facility labels (D-20). Energy/Fans labels state "after this turn" (O-3 disambiguation direction; whether total-vs-delta is *the* model stays open — §7). Dates via `M j, Y`; datetimes through `config('uma.display_timezone')`; date-only stays date-only. No em dash in copy (`N/A` + title is the disclosure glyph).

#### Navigation & Workflow Relationships

Hub of WF-001/002/005/006/007. Links out to `skills.index` ("Search the skill catalog to narrow that list"), `support-cards.show` (deck rows), `catalog.show` (skill-holder links are from SCR-SKL-002, not here).

#### Dependencies

`TrainingRun`, `TurnEntry`, `TurnEvent`, `RunSkill`, `DeckSlot`, `RaceEntry`, `ScenarioSlot`, `RaceCatalogSlot`, `SupportCard`, `Skill` models; `ScenarioCaps`; `SupportCardEffects`; `ShopPurchasePayload`; `config/scenarios.php` (composition matrix — single source of what this screen renders); `ImportHistoricalRun` for provenance lines.

#### Implementation References

View: `resources/views/runs/show.blade.php` (681 lines); components `guided-step`, `stat-band`, `resource-strip`, `mood-pill`, `race-calendar`, `race-panel`, `deck-panel`, `shop-panel`, `grade-point-meter`, `team-rank-gauge`, `spirit-burst-roster`, `team-race-panel`, `epithet-checklist`, `race-fatigue-chip`; Controller: `TrainingRunController` (show/storeTurn/syncSkills/syncDeck/storeRace/storePurchase/export/destroy + `showData`); Requests: `StoreTurnEntryRequest`, `StoreRunSkillRequest`, `StoreDeckRequest`, `StoreRaceEntryRequest`, `StoreShopPurchaseRequest`; JS: `resources/js/guided-flow.ts`; Tests: `GuidedFirstTurnTest`, `GuidedTurnStagesTest`, `GuidedTurnOnRunViewTest`, `GuidedTurnValidationTest`, `RunDeckTest`, `RunSkillPickerTest`, `RunSkillRowLabelsTest`, `RunSaveConfirmationTest`, `RunWriteAtomicityTest`, `GoalPanelsOnRunDetailTest`, `GradePointMeterTest`, `FreeRaceWriterTest`, `HistoricalRunImportTest`.

#### Current Status

Implemented. `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` carries a live **Fail verdict (2026-10-03)** for this page after the owner pass; Part 7 subsequently closed the blockers (O-2, O-4, R-5, R-6, R-8, item 14 both halves). Open at filing time: O-11 skills-panel design, O-8 deck tiles + per-card state, O-12 goals surface, O-3 total-vs-delta wording, O-5/R-7 unseeded race slots, R-2/R-3 schema asks, `Infirmary`/`Races` turn choices, contested Rest figure.

#### Open Questions / Gaps

- `runs.turns.update` / `runs.turns.destroy` exist (controller + tests) but **no UI control posts to them** — turn rows have no Edit/Delete action (§7-2).
- Team rank / spirit bursts / team-race rounds display explanatory panels with **no capture control** (R-2, O-6/O-7): Unity Cup numbers read `N/A` with no way to fill them.
- Goals surface (client header "Place 1st in Arima Kinen, 5 turns") does not exist (O-12).
- Per-card deck state (level, limit break, bond, hint) is cut by design (ADR-0014 "identity, not collection") but the audit records the trainer-facing cost; a schema proposal needs an Architect/PRD gate before it can change.
- Race calendar correctness is data-blocked: 406 of 410 `race_catalog_slots` rows carry no `scenario_key` (O-5 correction note), so year/scenario filtering cannot be honest until seeded.

_Dated notes 2026-10-08 (documentation-sync pass, on the Phases A–E completion brief; each line above is
preserved as written and re-read against the tree)._
- The `runs.turns.update` / `runs.turns.destroy` gap (§7-2) is **resolved 2026-10-04**: every turn row
  carries "Edit turn N" and the opened row holds the PUT and DELETE forms, pinned by `TurnRowActionsTest`.
  The line above is the record of the gap before that resolution and is left standing.
- The capture gap (R-2, O-6/O-7) **changed half and half**: E3 landed the read-only Unity Cup panel
  (`SCR-CAR-021`), which is the display half this line's explanatory panels became; the no-capture half is
  unchanged — no write path records a team rank, a burst state or a Spirit reading, the strip still prints
  `N/A` with no entry path, and the capture proposal of `RACE-AND-SLICE-RESEARCH.md` §unity-cup-capture.md
  stays the owner's (§7-6).
- The goals-surface line (O-12) **is closed in its run-page half and open in its per-trainee half**: the
  run page's goals panel landed at Part 8 A.4 (`RunGoalsPanelTest`, the ported `goalRows` prop), while the
  client's per-trainee goal header this line quotes remains absent because no `trainee_goals` table exists
  (`KI-34`), and the UIX audit's dated re-read records the same split.
- The per-card deck state line is unchanged: `ADR-0014` still cuts it and the gate is still the PRD §6.9 /
  US-12 call (Part 9).
- The race-calendar line is unchanged and now has a sharper blocker: the pinned GameTora document answers
  **404** (`KI-67`), so no refresh can add the scenario keys that scoping needs.
- The `Fail` verdict this section's Status line quotes was revised by the audit's own Part 7 and measured a
  page A4b replaced; the audit's dated re-read records the per-item verdict and this section's §7-12 note
  carries the summary._

---

### SCR-RUN-004 — Import a historical run (form)

#### Purpose

Bring a finished career in from the CSV this app itself exports, either chosen as a file or pasted. Nothing is written at this step; the next step shows every row before commit.

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Trainee list = `GlobalReleased` only (no card gate: a paper sheet records the trainee, not her form — `character_card_id` is not collected here by design).

#### Entry Points

`runs.index` "Import a historical run" link; `runs.import` "Start over" self-link from the preview state.

#### Route / Location

`GET /training-runs/import` name `runs.import`; submits to `POST /training-runs/import/preview` (`runs.import.preview`), `multipart/form-data`.

#### Layout / Structure

Title → explainer paragraph ("the file has to be correct before the run exists") → form: Trainee select, Scenario select, Status select (default **Completed**, deliberately different from create's Active), Notes, CSV file input, paste textarea with the exact expected header line as placeholder/helper, submit.

#### Data Displayed

Header line from `ImportHistoricalRunRequest::HEADERS` (single source: `turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans` — the same columns `export()` writes).

#### Inputs

| Field            | Type          | Req        | Validation (`ImportHistoricalRunRequest extends StoreTrainingRunRequest`)                                                                                                                                           |
| ---------------- | ------------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `umamusume_id`   | select        | required   | parent rules                                                                                                                                                                                                        |
| `scenario`       | select        | optional   | parent rules                                                                                                                                                                                                        |
| `status`         | select        | required   | parent rules                                                                                                                                                                                                        |
| `notes`          | textarea      | optional   | max:5000                                                                                                                                                                                                            |
| `file`           | file `.csv`   | one-of     | `nullable, file, mimes:csv,txt, max:1024`; when both present the upload wins (request copies it into `csv` before validation)                                                                                       |
| `csv`            | textarea      | one-of     | `required, string` **server-side only** — no native `required` here, because the browser constraint would close the upload path (audit I-1 blocker, message: "Paste the run's CSV or choose the file to import.")   |

Parsing/normalization facts visible to the Trainer: UTF-8 BOM tolerated, CRLF tolerated, blank lines skipped, mood words upper-cased on read, short rows reject the whole file rather than guessing shifted columns.

#### User Actions

| Action           | Trigger                   | Result                                |
| ---------------- | ------------------------- | ------------------------------------- |
| Preview import   | submit                    | parse + validate rows → SCR-RUN-005   |
| (back to form)   | "Start over" on preview   | fresh form                            |

#### Screen States

Initial; validation error (textarea preserved via `old('csv')`; per-row errors point at the row that held the offending cell); empty catalogue trainee list simply offers fewer options.

#### Validation & Error Handling

Rows validated as `turns.*` nested rules before preview: turn required/integer/≥1/**distinct** ("Two rows claim the same turn number"), stats required vs scenario caps (an import is measured by the same `ScenarioCaps` the turn form uses), sp/energy/mood/fans/condition as on turns. 1..200 rows max (career length + headroom; a longer sheet is called out as a transcription error rather than truncated). Unusable first line → `turns.required` message quoting the exact header.

#### Security & Privacy

CSRF; upload contents read once from disk into a request field; the browser filename is kept as provenance (`import_source`), the server path never shown.

#### Accessibility

Labels on every control; the preview table (on SCR-RUN-005) is the scrollable region, form itself simple.

#### Responsive Behavior

`max-w-2xl` form card; stacked field list.

#### Localization / Content

Copy names the limitation plainly: import carries **turns only**; skills/deck/races stay empty and are added per-turn on the run page.

#### Navigation & Workflow Relationships

Forward: SCR-RUN-005. Failure loop: the same page re-rendered with the Inertia `errors` prop.

#### Dependencies

`Umamusume`; `config/scenarios.php`; `TrainingRunController::importForm/importChoices`.

#### Implementation References

Page: `resources/js/pages/Runs/Import.vue` (form branch); Controller: `TrainingRunController::importForm`; Request: `ImportHistoricalRunRequest`; ADR: `docs/adr/0017-historical-run-import.md`; Tests: `HistoricalRunImportTest`, `tests/browser/run-import.spec.ts` (the KI-46 composite sentence, which the MessageBag test could not reach).

#### Current Status

Implemented.

#### Open Questions / Gaps

- Skills/deck/races cannot be imported (file format has no columns; stated on screen). A richer interchange shape is unrequested (PRD §6.7 forbids legacy import tooling generally).

---

### SCR-RUN-005 — Import preview & confirm

#### Purpose

Show every turn row that will be written, plus the run's identity line, and require an explicit second POST to commit. The preview is *not* a trust boundary: the commit re-runs the identical validation, which is why raw CSV text travels in a hidden textarea rather than a signed token.

#### User / Actor

Trainer.

#### Access & Authorization

As SCR-RUN-004.

#### Entry Points

Only the previous form's submit (no GET route — this state is reached by `POST runs.import.preview` rendering the same view with a `preview` payload).

#### Route / Location

Rendered by `POST /training-runs/import/preview`; commits via `POST /training-runs/import` (`runs.import.store`).

#### Layout / Structure

"Confirm the import" heading → identity line (trainee · scenario-or-"No scenario (baseline strip)" · status · turn count) → note region stating turns-only semantics → scrollable `role=region tabindex=0 aria-label="Rows to import"` table (11 columns matching HEADERS) → buttons: "Import N turns" (primary) / "Start over" (link).

#### Data Displayed

Every parsed row verbatim as raw strings straight from the parse (which is also what makes a rejected cell point at its row).

#### User Actions

| Action           | Result                                                                                                                                                            | Feedback                                           |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------- |
| Import N turns   | `ImportHistoricalRun` writes run + all turns in one transaction; records `imported_at` + `import_source` (filename, or `pasted CSV ({8-char hash})` for pastes)   | redirect `runs.show`, status "Imported N turns."   |
| Start over       | new form GET                                                                                                                                                      | —                                                  |

#### Screen States

Preview loaded; re-validation failure on commit (edits to the flashed textarea are rejected on the second POST and the form re-renders with errors — no partial write ever reaches the action).

#### Validation & Error Handling

Same request class both steps; a half-failed file leaves no partial history (transaction).

#### Accessibility

Table has an `sr-only` caption "Every turn row that will be written"; scroll region keyboard-focusable.

#### Localization / Responsive / Security

As SCR-RUN-004; table scrolls, never reflows (nine-plus columns will not fit a phone).

#### Navigation & Workflow Relationships

Success → SCR-RUN-003 with import provenance line visible there.

#### Dependencies

`ImportHistoricalRun` action; `TrainingRun` fillable `imported_at`/`import_source`.

#### Implementation References

Page: `resources/js/pages/Runs/Import.vue` (preview branch, gated on the `preview` prop); Controller: `TrainingRunController::importPreview/importStore`; Action: `app/Actions/ImportHistoricalRun.php`; Tests: `HistoricalRunImportTest` (two-POST semantics pinned), `tests/browser/run-import.spec.ts`.

#### Current Status

Implemented. (Documented as a separate ID because it is a distinct workflow state with its own commit action; it shares the physical view file.)

#### Open Questions / Gaps

None observed beyond SCR-RUN-004's.

---

### SCR-CAT-001 — Umamusume catalog (roster tree)

#### Purpose

Browse the trainees whose JP and Global data the engine cross-referenced, each with her confirmed costume forms nested. Read-only (PRD US-1, FR-A-3).

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Default view is **GlobalReleased only** — "a catalog whose first screen is half JP-only rows is not the Global English tool PRD §1 describes." Unconfirmed forms (single-source) are hidden unless `?show_unconfirmed=1`.

#### Entry Points

Nav "Catalog"; 404 button; return links from detail.

#### Route / Location

`GET /umamusume` name `catalog.index`. Query contract: `status` (enum value or `all`; any other value is refused and the request lands on this canonical URL with the field named, `ADR-0018`), `search` (normalized), `show_unconfirmed` (boolean), `page` (>=1), `pageSize` (honoured, clamped 1..100, default 25).

#### Layout / Structure

Title → GET filter form (Search, Release status select, Show unconfirmed checkbox, Filter submit) → roster tree (each trainee row: name + optional Japanese name, max-rarity chip, form count or "no forms recorded", status label; nested form rows: verbatim client title, rarity chip, debut mark, "Not confirmed by two sources" flag, Global release date) → pagination preserving query string.

#### Data Displayed

Trainee name, `name_ja`, `release_status` label; cards' title/rarity/debut/global date/unconfirmed. Search matches normalized `match_key` **plus aliases plus card titles** (folded at comparison time by raw SQL `normalizedColumn()`; a card match surfaces its trainee — the page is organized at trainee level). Known search ceiling stated in code: diacritics and width-variant folding do not work portably in SQLite (`[Nuit Étoilée de Scarlet]` will not answer `nuit etoilee`); upgrade path recorded as a schema decision, not made here.

#### Inputs

Search (free text; `%`/`_`/`\` escaped so a wildcard query cannot dump the catalog — KI-26's shape fixed here), status select, unconfirmed opt-in (GET form ⇒ a reload, not live typing).

#### User Actions

Filter (submit), open detail (row link), paginate (links carry `withQueryString()`), toggle unconfirmed.

#### Screen States

Loaded; **empty-search** ("No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`."); empty-catalogue (same message pre-fetch).

#### Validation & Error Handling

Unknown `status` values are refused by `CatalogSearchRequest` and redirected to the canonical catalog with the field named beside the picker; `status=` (empty) is the no-filter default rather than an error. One contract for all three filter surfaces, decided in `ADR-0018`, which supersedes this screen's earlier documented deviation.

#### Security & Privacy

Read-only; Blade escaping; LIKE metacharacter escaping; cached payloads are ids+count only, never model objects (`serializable_classes => false` guard, KI-2).

#### Accessibility

No collapse widget was added by measured decision (68 trainees = three pages, each already expanded; a disclosure would add a keyboard stop and hide nothing — G-11 reasoning recorded in the view); `h-11` filter controls; semantic `<ul>` tree with `h2/h3` per level.

#### Responsive Behavior

Rows `flex-wrap`; card meta wraps under the title at narrow widths.

#### Localization / Content

Status labels via enum→`lang/en/uma.php`. Card titles are **verbatim source data including brackets**; the lore gate sits on the display path, and this page prints them as published. Dates date-only.

#### Navigation & Workflow Relationships

→ SCR-CAT-002. No control here writes.

#### Dependencies

`Umamusume`, `CharacterCard` (with `cardScope()` order: debut first, Global date, source card id); `NameNormalizer`; `Cache::remember` behind `catalog:version` (bumped on promotion; Trainer data never cached).

#### Implementation References

Page: `resources/js/pages/Catalog/Index.vue`; Controller: `CatalogController::index` (+`cardScope`, `normalizedColumn`, `cached`); Tests: `CatalogTest`, `CatalogRosterTreeTest`, `CatalogCacheRenderTest`, `tests/browser/catalog.spec.ts`.

#### Current Status

Implemented.

#### Open Questions / Gaps

- `pageSize` up to 100 exists but no UI exposes it ("the lever if that ever changes", view comment). Unresolved requirement, minor.

---

### SCR-CAT-002 — Umamusume profile detail

#### Purpose

Everything this tool knows about one trainee: identity and profile facts, aptitude, costume forms (tabbable per form), her skill lists, her runs, aliases, and provenance — with every absence named in words (PRD US-1 acceptance, FR-A-4/A-7).

#### User / Actor

Trainer.

#### Access & Authorization

No auth; unknown slug → 404. Unconfirmed forms obey the same `cardScope` disclosure as the list; `?form=` may only select a visible form (a stale shared link falls back to the first form instead of 404-ing or revealing a hidden one).

#### Entry Points

Catalog rows; skill-holder links (SCR-SKL-002); support-card "Belongs to" (SCR-SUP-002); direct/shared URL.

#### Route / Location

`GET /umamusume/{slug}` name `catalog.show`. Query: `?form={local card id}`, `?show_unconfirmed=1`.

#### Layout / Structure (binding order per WS-2)

Header (name, "Back to catalog") → **Basic information** (Japanese name, voice actor JP + EN line, Release date Global-or-JP-only, birthday, height, three sizes) → status dl (Release status, JP debut, Global debut, "Edited by Trainer" `is_manual` statement) → JapanOnly notice when applicable → **Aptitude** grid (ten letters, once, not per form) → **Costume forms** (a link strip when >1 form via `FormTabs.vue`; inline panel when exactly one; each panel `FormDetail.vue`: verbatim title, rarity/debut/unconfirmed marks, its own `source_url`/`snapshot_path`/`fetched_at` line) → hidden-forms count + "Show unconfirmed forms" link → **Skills** (four groups: unique/innate/awakening/event, resolved through `Skill.export_id`, `SkillRow.vue` links only for rows Screen D serves) → **Goal races** (named absence — KI-34 reservation, no table exists) → **Her runs** (last 10 with status, scenario label, turn count) + "New run" (helper states it does not preselect) → **Aliases** → **Provenance** (trainee-level `data_sources` last 10, with the sentence separating it from the per-form/per-card inline provenances).

#### Data Displayed

Profile nullability is *measured and normal*: `va_en` absent on 10 of 135, `three_sizes` on 10, `birth_year` on 17 of 163 rows — every absence renders as a named sentence ("Not published by the source", "No English dub listed", "· year not published"), never a blank, zero, or guessed date. Base stats and stat bonuses are deliberately absent (ADR-0012 Decision 1: columns authorized, not built). `skills_evo` is stored but **not listed**: most of its ids name skills `[Global]` has not shipped and the detail route refuses them, so listing would emit 829 dead links.

#### Inputs

None (display only). Form tabs are plain `<Link>`s carrying `?form={local card id}` (and `?show_unconfirmed=1` while the lever is on), so the choice is addressable and survives a reload; the active tab carries `aria-current="page"`.

#### User Actions

Switch costume form (tab + commit), show unconfirmed, open a skill (only when Global-released and client-named), start a run (no preselect), read provenance links.

#### Screen States

Loaded; profile-not-yet-fetched (Japanese name still prints; the sentence names `uma:fetch gametora-character-profiles`); all-forms-hidden variant of the empty-forms state ("Every costume form recorded for this trainee is hidden as unconfirmed."); no runs; no aliases; no fetched sources ("This record was seeded or entered by hand.").

#### Validation & Error Handling

Unknown slug → 404. Out-of-scope `?form=` → silent fallback to first visible form (stated design, not an error).

#### Security & Privacy

Provenance link text comes from `config('uma.sources')` / stored columns, never from a fetched body; snapshot path is named, not linked (disk path). `is_manual` disclosed so the Trainer knows the engine will not overwrite her.

#### Accessibility

`aria-labelledby` sections; dl/dt/dd semantics; the costume-form strip is a `<nav aria-label="Costume forms">` of links, the active one carrying `aria-current="page"` (native keyboard targets, `min-h-11`); Japanese script rendered as data with no re-casing.

#### Responsive Behavior

Profile grid `grid-cols-2 md:grid-cols-3`; status dl `md:grid-cols-4`.

#### Localization / Content

`japaneseName()` prefers the profile row and falls back to the trainee column — one resolution path, stated. Dates date-only; fetched timestamps shown in `config('uma.display_timezone')` with zone suffix.

#### Navigation & Workflow Relationships

Back to SCR-CAT-001; forward to SCR-SKL-002, SCR-RUN-002 (generic create, no preselect — recorded on the button itself so the affordance does not lie).

#### Dependencies

`Umamusume`, `UmamusumeProfile`, `CharacterCard`, `Skill`, `TrainingRun`, `DataSource`; `Catalog/Show.vue` with `FormTabs.vue`, `FormDetail.vue`, `AptitudeGrid.vue`, `SkillRow.vue`, `RarityChip.vue`.

#### Implementation References

Page: `resources/js/pages/Catalog/Show.vue` (+ `components/catalog/{FormTabs,FormDetail}.vue`, `components/{AptitudeGrid,SkillRow,RarityChip}.vue`); Controller: `CatalogController::show`; Tests: `CatalogDetailPageTest` (asserts the props), `CatalogSkillListsTest`, `tests/browser/catalog-detail.spec.ts`, `GametoraCharacterProfileParserTest`.

#### Current Status

Implemented.

#### Open Questions / Gaps

- Goal races: named absence; `trainee_goals` reserved (KI-34) — needs a fetch source decision (Data Engineer/owner).
- Aptitude search/width folding ceiling (§7-10 cross-reference to catalog index's SQL limitation).

---

### SCR-SKL-001 — Skill search (Screen D)

#### Purpose

Find a skill among the `[Global]`-released catalogue by name fragment, derived type, and unique flag (PRD FR-D-2/D-3; the run UI's picker is too long to scan, and this screen is the honest narrowing tool).

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Read path only; **every query starts from `Skill::availableOnGlobal()`** — a JP-only or third-party-named row cannot reach this screen (ADR-0011 §2, PRD FR-D-3). No cache (the screen's class docblock explains why `catalog:version` would be a lie for this table; reads measured 0.3–0.8 ms).

#### Entry Points

Nav "Skills"; run page "Search the skill catalog" link; skills/show return link.

#### Route / Location

`GET /skills` name `skills.index`. Query: `search` (≤120), `type` ∈ parser categories ∪ `Unspecified`, `unique` boolean, `page`, `pageSize` (1..100 clamped in controller).

#### Layout / Structure

Title → GET filter form (Search, Type select incl. "Unspecified", Unique-only checkbox, Filter) → invitation line (only when no query is in flight — D-65: two states are not one screen) → facet error → results count line "X of Y skills available on [Global]" → result rows (name link → detail, Japanese name only when it differs, ✦ Unique pill + word, type word, SP cost or `N/A` + explanatory title) → pagination → footer explaining that the type word is this tool's derivation, not client copy, and that the "Unspecified" facet finds the rows the sign rule withheld.

#### Data Displayed

The D-30-permitted Skill surface only: name, name_ja, sp_cost, type, is_unique. Not rendered, each with a stated reason: icon (column unimported, G-SK-19), description (both English description fields fail the terminology table, G-SK-16/20), rarity/class code (ADR-0011 §3).

#### Inputs

Search matched against `match_key` via normalized LIKE with an explicit `ESCAPE '\'` and pre-escaping of `%_` (KI-26 fixed on this surface); case/spacing-insensitive by construction. Unknown `type` value → refused, redirected back, field named (a dropped facet would "answer a question nobody asked with the whole catalog").

#### User Actions

Filter; open skill detail; paginate.

#### Screen States

Pre-fetch empty table (distinct copy naming both `uma:fetch gametora-skills` and the `uma:reparse` KI-27 trap); no-results naming the ask (`"…"` + type + unique) and stating a missing skill is a data gap, not a query mistake — deliberately **no** link to the review queue (skills bypass `match_candidates` by design, so a link would promise what cannot happen); loaded.

#### Validation & Error Handling

`SkillSearchRequest`; `page/pageSize` coerced rather than refused.

#### Security & Privacy / Accessibility / Responsive

CSRF n/a (GET); `h-11` inputs measured against DESIGN.md §6.14; checkbox at the 24px WCAG 2.2 AA floor (the tool's first checkbox); rows are a `<ul>` with wrap-baselines; no breakpoint structure beyond wrap.

#### Localization / Content

Latin-script "Japanese" names (`#LookatCurren` etc.) dedupe against the English column so 18/623 rows don't print the string twice — a display rule, data stays verbatim. Type words are this tool's derived classification, never client copy (D-20).

#### Navigation & Workflow Relationships

→ SCR-SKL-002; reached from SCR-RUN-003's skills section.

#### Dependencies

`Skill`; `NameNormalizer`; `GametoraSkillsParser::CATEGORIES`; `StoreSkills` (write path, referenced by copy).

#### Implementation References

Page: `resources/js/pages/Skills/Index.vue`; Controller: `SkillController::index` (+`query`, `describeAsk`, `availableCount`); Request: `SkillSearchRequest`; ADR: `docs/adr/0011-skills-reference-import.md`; Tests: `SkillSearchScreenTest` (asserts the props), `tests/browser/skills.spec.ts`, `SkillsFetchTest` (family).

#### Current Status

Implemented.

#### Open Questions / Gaps

- Running-style eligibility display is an open product question (PRD OQ-5) — nothing here is authorized; the screen must not become a recommendation surface (OQ-5 forbids `best_for` outright).

---

### SCR-SKL-002 — Skill detail

#### Purpose

One skill's full record: fields, mechanics as the source states them, the trainees whose forms hold it, and the absences the data genuinely has.

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Route param is the **local** `skills.id`; lookup starts from `availableOnGlobal()`, so an id the scope rejects is the same 404 as an unknown one. The export id never reaches the screen.

#### Entry Points

Screen-D rows; run-page/skill cross links from SCR-CAT-002 `SkillRow.vue`; support-card lists.

#### Route / Location

`GET /skills/{skill}` name `skills.show`.

#### Layout / Structure

Header (name + deduped Japanese line, "Back to skill search") → dl (Type, SP cost, Unique) → **Mechanics** (per activation group: condition/precondition as monospace engine expressions, base time ms, effect chips `effect {type}: ±{value}`; footer states these are the source's own data, and that description/awakening thresholds are not recorded) **or** its absence paragraph naming the three missing things → **Held by trainees** in four groups (unique/innate/awakening/event links to catalog detail, unconfirmed forms excluded) → **Provenance** (source link + read date, or "seeded or entered by hand").

#### Data Displayed / Inputs / Actions

Display only; link actions. No editing surface on this screen: the engine writes skills; the Trainer's skill opinions live on runs (C-3).

#### Screen States

Loaded; no mechanics recorded (naming absence); no holders ("No trainees recorded with this skill."); unknown/rejected id → 404.

#### Validation, Security, Accessibility, Responsive, Localization

As SCR-SKL-001 (same view idiom; holder links use slug routes; monospace data blocks are text, selectable).

#### Navigation & Workflow Relationships

→ SCR-CAT-002 holders; ← SCR-SKL-001, SCR-CAT-002, SCR-SUP-002.

#### Dependencies

`Skill`, `CharacterCard` (JSON `whereJsonContains` over the four lists, `json_valid` guarded, missing-column tolerated with `QueryException` narrowing).

#### Implementation References

Page: `resources/js/pages/Skills/Show.vue`; Controller: `SkillController::show` (+`holderRows`, `holders`); Tests: `SkillDetailTest` (asserts the props), `CatalogSkillListsTest` (mirror read), `tests/browser/skills.spec.ts`, `GametoraSkillsParserTest`.

#### Current Status

Implemented.

#### Open Questions / Gaps

- Rendering mechanics expressions is **beyond D-30's current Skill entry**; the widening ask travels in `docs/research-scratch/PLANS-AND-BRIEFS.md` (view comment) — documentation pending an owner ruling.

---

### SCR-SUP-001 — Support-card catalog

#### Purpose

Browse all 559 published support cards with rarity/type/availability facets and a sort. Deliberately **not** pre-filtered to `[Global]` — availability is a facet the Trainer picks, because the stored generated `release_status` column exists to state it per row, and pre-filtering would copy that decision silently onto the read side.

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Read-only; the engine writes `support_cards`, deck writes live on runs.

#### Entry Points

Nav "Support cards"; deck rows (detail); return links.

#### Route / Location

`GET /support-cards` name `support-cards.index`. Query: `rarity` ∈ {1,2,3}, `type` ∈ `SupportCard::TYPES`, `status` ∈ `SupportCard::AVAILABILITIES`, `sort` ∈ {`rarity`,`released`} (default name order); page fixed 25 (constant, "a size nobody can change is a size nobody has to reason about").

#### Layout / Structure

Title → GET filter form (four selects + Filter) → per-facet error lines → count line → card rows (assembled display name — never a stored composed name, FR-A-8(ii) — rarity chip + R/SR/SSR word, type word Wit/Pal etc., availability word, at-cap effect chips; unknown effect ids print `[Unverified] effect {id}` rather than an invented word) → pagination → footer stating the cap-anchor basis and the export-key↔client-word mapping.

#### Data Displayed / Inputs / Actions

Facet filtering and ordering with a stable tiebreaker (`char_name,title_en`) so pagination cannot reshuffle rows between pages (stated, load-bearing).

#### Screen States

Pre-fetch empty (names `gametora-support-cards` fetch + reparse trap); no-results naming the ask (no review-queue link, same reason as Screen D); loaded.

#### Validation & Error Handling

`SupportCardSearchRequest`; unknown facet values refused and redirected back **naming the route** (`$redirectRoute`, so a failed filter stays on the screen that caused it); int-vs-string key coercion pitfall handled at both ends (controller map keys + view cast).

#### Accessibility / Responsive / Localization

Same filter-surface idiom as Screen D (`h-11`, focus ring, wrap rows); client rarity/type words are display mappings over stored keys; `release_status` printed in the client's own terms.

#### Navigation & Workflow Relationships

→ SCR-SUP-002; feeds SCR-RUN-003's deck picker (`availableOnGlobal` = non-null `release_global`).

#### Dependencies

`SupportCard`, `SupportCardEffects::dictionary()` (read once per page, N+1 avoided by design), `CardRarity`.

#### Implementation References

View: `resources/js/pages/SupportCards/Index.vue` (+ `components/RarityChip.vue`); Controller: `SupportCardController::index`; Request: `SupportCardSearchRequest`; ADR: `docs/adr/0014-support-card-entities.md`; Tests: `SupportCardPageTest` (asserts the props), `tests/browser/support-cards.spec.ts`, `ApiV1SupportCardTest` (data shape), `GametoraSupportCardParserTest`.

#### Current Status

Implemented.

#### Open Questions / Gaps

- D-30 carries **no SupportCard entry at all**; the widening ask is filed in PLANS-AND-BRIEFS (view header). Card tier labels held pending a current Global source (ADR-0014).

---

### SCR-SUP-002 — Support-card detail

#### Purpose

One card's facts: identity, both server dates, availability, the trainee its source `char_id` resolves to (when this catalog tracks her), effects at cap, and the two skill lists — with unresolvable ids counted, not dropped, because silent drop would be "a lie by silence".

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Local `support_cards.id` route key (the source `support_id` is not a route key — a URL that renumbered with the publisher would break).

#### Entry Points

Catalog rows; deck rows.

#### Route / Location

`GET /support-cards/{card}` name `support-cards.show` (implicit binding).

#### Layout / Structure

Header (display name, optional `name_ja`/`title_ja` line, Back) → dl (Rarity chip+word, Type word, Availability, Released in Japan, Released on [Global] — date-only never timezone-shifted, Belongs to → catalog link or the named two-way miss: 9000-block staff and untracked trainees; measured 322/559 resolve) → **Effects** (at-cap chips + basis note) → **Hinted skills** and **Event skills** (each: not-stored vs empty-stated distinction; linked rows via `SkillRow.vue`; unlinked counted in words) → **Provenance** (source, read date, and the `is_manual` "Corrected by hand, so the fetch engine leaves it alone" line — FR-B-4's stop sign placed where a Trainer can see it).

#### Data Displayed / States

Unknown dictionary rows marked by id, never labelled. No tier label (held).

#### Validation, Security, Accessibility, Responsive, Localization

Same idiom as SCR-SUP-001; no inputs.

#### Navigation & Workflow Relationships

→ SCR-CAT-002, SCR-SKL-002; ← SCR-SUP-001, SCR-RUN-003 deck.

#### Dependencies

`SupportCard`, `SupportCardEffects::atCap`, `Skill` via `availableOnGlobal`, `Umamusume` via `external_ref` string join (deliberately not a foreign key — ADR-0014 correction 1).

#### Implementation References

View: `resources/js/pages/SupportCards/Show.vue` (+ `components/SkillRow.vue`, `components/RarityChip.vue`); Controller: `SupportCardController::show` (+`skillList`, `trainee`); Tests: `SupportCardPageTest` (asserts the props), `tests/browser/support-cards.spec.ts`, `RunDeckTest` (deck read-side), parser test above.

#### Current Status

Implemented.

#### Open Questions / Gaps

Same widening ask as SCR-SUP-001; group-card scenario-link miss is a known-and-accepted gap (FR-A-9).

---

### SCR-REV-001 — Match review queue

#### Purpose

The human half of the cross-reference engine: Fuzzy and None fetch results land here as pending candidates; the Trainer confirms (merge or create), aliases, or rejects. **The engine never merges without this decision** (PRD US-5, FR-B-3).

#### User / Actor

Trainer.

#### Access & Authorization

No auth. Lists `CandidateStatus::Pending` only; resolved rows leave the view.

#### Entry Points

Nav "Review"; Screen-D/support empty states deliberately do *not* link here (those tables bypass the queue by design — a link would be a control that cannot do what it says).

#### Route / Location

`GET /review` name `review.index`; verdict `POST /review/{candidate}` name `review.resolve`.

#### Layout / Structure

Title → explainer ("Fuzzy and unmatched fetch results land here…") → per-candidate card (proposed name + optional Japanese name, tier label · source key · fetch date, engine's suggested trainee) → one form per card: verdict select (Confirm/Aliased/Rejected with in-option explanation text), numeric `umamusume_id` override pre-filled with the suggestion, alias-language select, Resolve button → stacked error list → pagination (25).

#### Inputs

| Field              | Rules (`ResolveMatchCandidateRequest`)                                                 |
| ------------------ | -------------------------------------------------------------------------------------- |
| `status`           | required, enum `CandidateStatus`                                                       |
| `umamusume_id`     | nullable int, exists `umamusume.id` (overrides the fuzzy suggestion as merge target)   |
| `alias_language`   | required when `status=Aliased`, enum `AliasLanguage`                                   |

#### User Actions

| Verdict    | Effect (`ResolveMatchCandidate`, one transaction)                                                                                                 |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| Confirm    | promotes the stored payload — into the suggested row when targeted, as a new row otherwise (`PromoteMatchedRecord`; `is_manual` rows untouched)   |
| Aliased    | records the proposed name (and Japanese name when present) as alias(es) of the target, so future fetches match automatically                      |
| Rejected   | closes the candidate only; nothing written to catalog                                                                                             |

All three redirect back to the queue with "Candidate resolved."

#### Screen States

Empty ("Nothing pending. Run `php artisan uma:fetch` to populate."); pending rows; validation error (all three field errors render **stacked** per card — the request can return `umamusume_id` and `alias_language` together, and one-at-a-time fixing sends a Trainer re-submitting blind).

#### Validation & Error Handling

As above; unknown candidate id → 404.

#### Security & Privacy

CSRF; verdicts write catalog rows with provenance (the promote action carries the source key), so audit trail = `data_sources`, not a queue history table.

#### Accessibility

Audit F14 closed here: every control has an `sr-only` label **naming the candidate** ("Verdict for {proposed_name}" — a generic name on a queue of identical forms is ambiguous), `aria-invalid` + `aria-describedby` on failed fields, error list `role="alert"`, Resolve button `aria-label="Resolve {name}"` (visible label contains the accessible name, WCAG 2.5.3). Test: `ReviewFormAccessibilityTest`.

#### Responsive / Localization

Card content wraps; enum labels from `lang/en/uma.php` (tier, alias language); dates date-only.

#### Navigation & Workflow Relationships

Consumes engine output; improves SCR-CAT-001/002 data. No onward links from a resolved candidate (it leaves the view).

#### Dependencies

`MatchCandidate`, `ResolveMatchCandidate`, `PromoteMatchedRecord`, `UmamusumeAlias::firstOrCreate` uniqueness `(alias, language)` (FR-A-2).

#### Implementation References

View: `resources/views/review/index.blade.php`; Controller: `ReviewController`; Request: `ResolveMatchCandidateRequest`; Tests: `ReviewFormAccessibilityTest`, `CrossReferenceMatcherTest`, `FetchPipelineTest` (routing to queue).

#### Current Status

Implemented.

#### Open Questions / Gaps

None found. (Bulk actions are not built and no story asks.)

---

### SCR-SYS-005/006/007/008/009/010 — Database hub, race database, scenario matrix, reference views

`GET /database` (`database.index`), `GET /database/races` (`database.races`), `GET /database/scenarios`
(`database.scenarios`), and the three reference views `GET /database/events` (`database.events`),
`GET /database/shop-items` (`database.shop-items`), `GET /database/sparks` (`database.sparks`);
`App\Http\Controllers\DatabaseController` for the hub and the five areas it owns, and
`CatalogController::databaseIndex()`, `SupportCardController::databaseIndex()` and
`SkillController::databaseIndex()` for the three reused ones. SCREEN-023, plan §8 D17 (the first five
areas) and the reference-view slice (the last three). Read-only: nothing in this area writes, and no
route takes a run.

**Reuse, not a fork.** The three catalog areas each render the shared list component
(`components/catalog/TraineeCatalog.vue`, `support/SupportCardCatalog.vue`, `skills/SkillCatalog.vue`) under
their own `/database/*` address. The query lives in one private `indexProps()` per controller and both the
original route and the database route call it, so `/umamusume`, `/support-cards` and `/skills` are unchanged
in behaviour and the pagination path is the only argument that differs. A redirect was the smaller diff and
is what an earlier draft of this section claimed; `routes/web.php:249-253` records that no owner ruling
authorises a redirect, so the pages render in place and `database.spec.ts` asserts the address stays.

| Screen | State | Behavior |
| --- | --- | --- |
| Hub | Rendered | Eight area links, `prefetch: 'hover'`, each `min-h-11`. The "Not in this build" section that once named Events, Shop Items and Sparks as absences is **deleted**, not softened: all three are destinations now, and a row saying otherwise would be false. |
| Hub | Loading / error | None of its own: the hub is one server render with no user-initiated async action (`ADR-0007`), and a failed visit is the shell's. |
| Trainees, Supports, Skills | Rendered | The shared component's own states, unchanged from the original screens (search, filters, `Loading results…` `role="status"`, the catalog empty state, pagination clamped 1..100 by `PageSize::clamp`). |
| Races (`SCR-SYS-006`) | Empty, unseeded | "The race database is offline", why it matters (no turn number, distance, surface or fan gate to plan against), and the two commands that fill it: `php artisan uma:fetch gametora-race-catalog`, or `uma:reparse` when a fetch reports the document unchanged. No placeholder row. |
| Races | Populated | `{{ total }} of {{ totalCount }} slots`, one row per race per turn, ordered year then turn then sort order. |
| Races | Unrecorded cell | `N/A` with the reason on the element ("The source records no distance for this slot", and for a race with no gate, "No entry gate: open to a trainee at this point of the career"). Never a zero, never a dash. Measured on this tree's first page: 2 of 25 rows carry no distance, surface or fan gate, 23 of 25 carry no entry gate. |
| Races | Narrow viewport | The table scrolls inside `div[role="region"] tabindex="0" aria-label="Race slots, scrollable"`, never the document (`DESIGN.md` §6, the same shape as `Runs/Show.vue`'s turn log). Density is permitted here and nowhere else (plan §13). |
| Races | Held | No win probability, no readiness band, no percentage, no `distance_band` derived from a metre count (`ADR-0016`; the corpus disagrees at 1400 m). |
| Scenarios (`SCR-SYS-007`) | Rendered | Every entry of `config/scenarios.php`, as resolved: the published cap as base + bonus + sum (never collapsed, the config's own instruction), `widgets[]` and all six `panels.*` keys printed on or off as words (a reference matrix states the off panels rather than hiding them), `live_on_global`, and `PARTIALLY DOCUMENTED` where the config sets `partially_documented`. |
| Scenarios | Empty | Not reachable: the config is the source and it has entries. Rendering nothing would mean the config lost every scenario, which is a different failure than an absent dataset. |
| Scenarios | Provenance line | `Verified {{ verified_at }} against the GameTora scenarios.json field stats`, reading the config's `verified_at` (`2026-09-27`) and its header note that this is a verification date, not an update date (`config/scenarios.php:41-42`). |
| Events (`SCR-SYS-008`) | Rendered | The ten recurring event types from `docs/UMAMUSUME_REFERENCE.md` §4.4, read from `config/reference.php`. Each row carries the type name, the server tags as bracketed text (`[JP]`, `[Global]`), the cadence and the mechanics summary, plus a `ProvenanceBadge`. A row click opens the shared drawer with the detail and the source `title`. The §48 metadata row prints the doc section, the anchor date and the "how to re-check" pointer. |
| Events | Empty | "No recurring event types are sourced", with what is missing, why it matters (no cadence to plan around) and what to do (re-read the guide's §4.4 table). The table is not empty today; the state is the shape a config with no rows renders. |
| Events | Held | No live or upcoming instance is rendered: §4.1 to §4.3 are a dated snapshot and rot, so only the recurring-type cadence table ships. |
| Shop Items (`SCR-SYS-009`) | Rendered | Ten catalogue rows from §1.6.10 (eight consumables plus the two currency items that section describes in prose), each with the client's effect string, a spend-site pill and a kind pill (Consumable / Currency, as words), plus a `ProvenanceBadge` and a drawer carrying the detail. Below them, the Trackblazer Pro Shop block is read from `config/scenarios.php` `trackblazer.shop_items` (19 items) with the matrix's own rotation length, reused rather than retyped. |
| Shop Items | Empty | "Item catalogue not loaded in this build", naming the guide's §1.6.10 as where the client strings are and noting the Pro Shop block renders either way. |
| Shop Items | Held | No recommendation, no best-value sort and no default selection: the shop rotation is not modelled, so no row is promoted. |
| Sparks (`SCR-SYS-010`) | Rendered | The six Spark categories from §1.5.2, each with the category word, the `[Global]` name, the `[JP]` name carrying `lang="ja"`, the effect summary, the record count and a `ProvenanceBadge`. The star-roll-odds table from §1.5.3 renders on the page in its own labelled, focusable scroll region, with the "rolled, never chosen" note beneath it. |
| Sparks | Empty | "No Spark categories are sourced", with the three §29 clauses. |
| Sparks | Record counts | Five of the six counts are cells in the local Spark export (5, 10, 452, 37, 34); the sixth, 268, is the guide's own reading of part of that export's 336-record bucket. The export is gitignored and no runtime path reads it, so the counts are cited, never re-derived, and no count is invented. Beneath the list the view names the difference the sum implies: the guide counts **68 further records** in the same export that no page in its pass explains, so the 806 the six rows sum to is the described total while the export holds 874. The `SourceNote` row carries the same reading date. |
| Reference views | Provenance | Every row uses `ProvenanceBadge.vue`, the single owner of the four glyphs. The vocabulary is Confirmed / Calculated / Estimated / Unknown (`ADR-0020` §2), not the `screen-spec-2.0` §31 or `design-2.0` §45 variants. The badge's accessible name is the state word; its glyph is `aria-hidden`. |
| Reference views | Drawer | The shared `components/database/ReferenceDrawer.vue`: non-modal `role="dialog"` with a name, focus on open, Escape or the Close button to dismiss, and focus returned to the row that opened it. §32's pattern, not §31's modal. |
| Reference views | Narrow viewport | Rows are stacked cards, not a wide table, so no reference view scrolls the document sideways at 320 px. The one real table (the star-roll odds) scrolls inside its own labelled, focusable region. |

**Gaps.** (1) No scenario name appears in any of the six pages, and the matrix branches only on resolved
config values, so a fifth scenario is one config entry and no component edit (D-240, gate G-33). (2) The
scenario `notes` string is rendered verbatim from config; a scenario whose note is long wraps, and no
truncation is applied. (3) Skill icons have no column (`skills.iconid`), so the Skills area renders the
text-only row and reserves no frame (`DESIGN.md` §4.7, `ADR-0021` Decision 5); the "omit the slot cell at
narrow viewports" half of design-2.0 §45a is not implemented anywhere in `ArtworkSlot.vue` and is
inherited by this area rather than introduced by it. (4) The Race database's offline state is unreachable on
a seeded database, so the browser spec asserts whichever of the two states the tree holds (KI-69). (5) The
area has no density preference control: `design-2.0` §34 asks for one eventually and `Preference::KEYS`
holds two keys authorised by `PRD.md` US-11, so widening it is an owner decision, not a slice's.

`tests/Feature/DatabaseTest.php` (7 cases) pins the props: the hub's five areas and three deferred labels, the
three reused areas' component and pager shape, the Races offline payload on an empty table, the populated
table's `N/A` fields, the scenario matrix's caps/widgets/panels with the `partially_documented` flag on one
entry only, and the verification date. `tests/browser/database.spec.ts` (7 cases) asserts the rendered hub,
that each reused area keeps its own address, both race states with every `N/A` carrying its reason, the
matrix's four labels plus exactly one `PARTIALLY DOCUMENTED`, and the 320px pair (document does not scroll,
the race region does and is focusable).

### SCR-SYS-001 — Page not found (404)

#### Purpose

Keep a missing address inside the product: same shell, same theme, one action back to work. Reached by unknown slugs/ids, deleted rows, and mistyped links.

#### Actor / Access

Any; no auth concept applies.

#### Route / Location

`resources/views/errors/404.blade.php` renders for every `NotFoundHttpException`/model-not-found on web routes (API requests get the JSON envelope instead, §4.15).

#### Layout / Content

Title "Page not found" → note block "Nothing to show here" naming the three likeliest causes → buttons: Training runs (primary), Catalog, Review.

#### States

Single state. Three custom error views exist: 404 (this screen), 419 and 500 (`resources/views/errors/`). Other statuses fall through to framework defaults. §7-8 closed 2026-10-04.

#### Implementation References

View above; `bootstrap/app.php` exception rendering (JSON vs HTML split); `docs/adr/0007-c7-loading-state-scope-for-server-rendered-views.md` (loading/error state scope for server-rendered views).

#### Status

Implemented.

#### Gaps

None open. A stale-session POST used to surface the framework page; it now renders SCR-SYS-003 (§7-8, closed 2026-10-04).

---

### SCR-SYS-002 — Preferences

#### Purpose

Let a Trainer set the two UI preferences PRD US-11 authorizes, and read the Data Verification badge (SCREEN-024). Before 2026-10-04 the `preferences` table and the server-side theme render both existed with no way to write them, so the dark theme was reachable only by inserting a row by hand (audit O-1, §7-5). D18 (2026-10-07) organizes the surface into four Settings categories per `screen-spec-2.0.md` SCREEN-024:

- **General** — Theme (control, column exists); Language (a disabled single-option control, "English (Global)", because the corpus is Global-labelled); Units (`N/A`, a *sourcing* absence: race distance is the only unit this tool prints and it prints metres because the client does); Default scenario (control, stored, pre-selects the new-career screen until a career is chosen).
- **Recommendation** — Numeric failure estimate (control, column exists); Recommendation aggressiveness (control, stored, and its `title` says the advisor reads it in a follow-up slice); Stat-target defaults (five controls against the engine ceiling, pre-filling Build Target where no target is stored); Risk tolerance (`N/A`, naming D4's open question on whether it enters `BuildTargetPayload`). Race-risk thresholds is omitted entirely (held on `ADR-0016`).
- **Data** — Import (link to `runs.import`); Export (link, a streamed JSON download of `training_runs`, `turn_entries`, `veterans` and the settings blob); Backup (a server-side `VACUUM INTO` snapshot under `storage/app/backups/`, path returned in the flash, never streamed); Restore / Reset (named absences — destructive, owner-scoped per `AGENTS.md` §5).
- **Game version** — Global Ruleset (N/A, `app.ruleset` is null) + Verified date from `config('scenarios.verified_at')`.

_Dated note 2026-10-08 (D18b): the three "no column" absences in General and Recommendation became controls. The structured preferences live in one row of the `preferences` table keyed `settings`, a JSON blob whose key set is `Preference::SETTINGS_KEYS` — not a column, because a nullable column on a key-value table would be read by nothing, and the row the blob would live in cannot exist without also filling the NOT NULL `value` it duplicates. The plan's §8.6 D18b records the reasoning._

#### Actor / Access

Any; no auth concept applies.

#### Route / Location

`GET /preferences` name `preferences.edit` (`PreferenceController::edit`), written by `PUT /preferences` name `preferences.update`. Reached by the "Settings" nav link in `resources/js/layouts/AppLayout.vue` (SPA). One endpoint for all writable preferences, because writing preferences is one action on the key-value store, not one per key. The Data panel's two actions have their own routes: `GET /preferences/export` (`preferences.export`) streams the JSON download, and `POST /preferences/backup` (`preferences.backup`) writes the server-side snapshot.

#### Layout / Content

`<Head title="Settings">` and the `#title` slot; h2 "Preferences"; an intro line stating storage is server-side. One `<form>` wraps a labelled tablist (General, Recommendation, Data, Game version) whose arrow-key and click path switches `role="tabpanel"` visibility. The General panel holds Theme, Language, Default scenario and the Units absence; the Recommendation panel holds Recommendation aggressiveness, the five stat-target-default inputs, and the Risk-tolerance absence; the Data panel holds the Import and Export links, the Backup action in its own form (it is a second action, not a field of the preferences form), and the Restore/Reset absences; the Game-version panel holds the ruleset row and the verified date. Form errors and the Save button sit below the panels, always reachable. The Save button is the screen's only Level 2 element; Backup is a secondary action and carries its own sentence saying it writes server-side and downloads nothing.

#### States

| State               | Behaviour                                                                                                                                                                                                          |
| ------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Default (General)   | Theme select shows the stored value or the follow-the-OS option; the failure-estimate checkbox reflects `off`/`on`. Both controls are in the tab panel that is visible first, so the first browser assertion finds them. |
| Tab switched        | `v-if` swaps the panel; the old panel's controls leave the DOM and the new panel renders. ArrowLeft/ArrowRight/Home/End move between four tabs; each tab is a `<button role="tab">` with `aria-selected` and `tabindex`. |
| Named absence       | Renders `N/A` with a `title` naming which of the **four classes** it answers to. The vocabulary is closed and the names do not move: **schema absence** (no key exists to store it, including a key whose existence is another screen's open question); **governance hold** (the action is destructive and `AGENTS.md` §5 scopes it to the owner); **trust-model absence** (no source at all publishes the value, so there is nothing to print); **sourcing absence** (the client itself prints the value, and no source publishes an alternative form of it, so a preference over it has nothing to convert to). Never a default and never a dash. The rule applies to every unrecorded value on the surface (`AGENTS.md` §13). |
| Constrained control | A fifth *shape*, not a fifth class: the control is present and enabled-looking but cannot change, because there is exactly one legal value. Language is the case: a disabled single-option select whose `title` names the constraint. A disabled control that hid the one available value would be a lie, and offering a second value would be a false choice. The write path still accepts the key, so what the screen states is what the store holds. |
| Form error          | The refused submission keeps the user on the page; Inertia sets `form.errors`, the relevant message renders with an `id` referenced by `aria-describedby` on its control, and focus moves to the first invalid control, whose category is revealed first. |
| Export              | A streamed JSON download (`StreamedResponse`), rows written through a cursor rather than buffered whole, with `Content-Disposition: attachment`. The four top-level keys are the export's contract and `PreferenceControlsTest` pins them. |
| Backup              | A server-side snapshot under `storage/app/backups/`, the path returned in the same `role="status"` flash the save uses. Nothing is downloaded. A failure is unhandled on purpose: there is no second feedback channel to invent, and the app's own 500 document (`SCR-SYS-004`) is the error state the rest of this tool uses. |
| Loading (submit)    | `:aria-busy` on the form and a "Saving…" button label; the tablist stays in the tab order and is not trapped.                                                                                                          |

First paint stays correct because the theme is composed server-side (D-104); the control never writes to browser storage (§6 non-goal 12).

#### Validation & Error Handling

`UpdatePreferenceRequest`: `theme` present-and-nullable, in `light`/`dark` (the empty string is the stated follow-the-OS answer, and `ConvertEmptyStringsToNull` turns the form's empty choice into null before the rules are read); `failure_estimate` present, in `off`/`on`; `settings` present-and-nullable, carrying the structured blob whose own keys are `Preference::SETTINGS_KEYS` — `default_scenario` must be a key the matrix composes, `recommendation_aggressiveness` is one of `conservative`/`balanced`/`aggressive`, `stat_target_defaults` carries exactly the matrix's five stats between 0 and `ScenarioCaps::hardCap()`, and `language` is `en`. Any other input key, top-level or nested, is refused outright, because `validated()` would drop it silently and the screen would keep believing the preference had been stored. Follow-the-OS deletes the row rather than storing the word `system`, and a blob whose keys are all cleared deletes its row for the same reason: absence is the default, not a null to interpret.

#### Implementation References

`PreferenceController` (props: `theme`, `failureEstimate`, `verifiedAt`, `importUrl`, `settings`, `scenarioOptions`, `aggressivenessOptions`, `statOrder`, `hardCap`, `exportUrl`, `backupUrl`; actions: `export`, `backup`), `UpdatePreferenceRequest`, `Preference::{KEYS, SETTINGS_KEYS, settings, putSettings}`, `App\Actions\BackupDatabase` (the snapshot both `uma:backup` and this screen's Backup action share), `App\Services\ScenarioCaps::hardCap()`, `AppServiceProvider`'s layout composer, `resources/js/pages/Preferences/Edit.vue` (tablist shell), `resources/js/layouts/AppLayout.vue` (flash `role="status"` + `aria-live="polite"`), and the two read-side pre-fills (`Career\ScenarioSelectController::storedDefault()`, `Career\BuildTargetController::statDefaults()`); tests `tests/Feature/PreferenceControlsTest.php` (props, the blob's round trip and refusals, the export's shape, the backup's integrity) and `tests/browser/preferences.spec.ts` (rendered copy, keyboard path, tablist, the absence titles, 44px, 320px, reduced motion, axe).

#### Status

Implemented 2026-10-04 (§7-5). Extended 2026-10-07 (D18): four-category tablist shell, Game-version panel reading `config('scenarios.verified_at')` + the shared `app.ruleset` (null), named absences for every preference without a column. The failure-estimate *display* half stays blocked on a sourced model, which the screen states rather than hides. Extended 2026-10-08 (D18b, the owner's authorization of that slice's open question 1): Language, Default scenario, Recommendation aggressiveness and Stat-target defaults became controls stored in one `settings` row; Export and Backup became routes; Units stays `N/A` for a *sourcing* reason rather than a schema one, and Restore, Reset and Risk tolerance stay absences with their reasons named.

#### Gaps

1. The six named-absence preferences (Language, Units, Default scenario, Aggressiveness, Stat-target defaults, and Race-risk thresholds) have no column. D18 records the single open question: add a nullable `settings` JSON column to `preferences` validated by `Preference::KEYS` at its top level (matching the `build_target` json-column precedent), or approve individual columns? `DesignTokensTest:168` asserts `preferences` has exactly `['key','value','created_at','updated_at']` and is a one-line update in the same commit if approved. Not a bug; a product-scope decision.
2. `race_risk_thresholds` is not rendered at all: `ADR-0016` blocks any race-outcome prediction, and the screen does not invent a placeholder for it.
3. Reset has no route: it is a destructive action that `AGENTS.md` §5 puts behind owner approval. The Data panel states the absence; no confirmation dialog ships without a backend.
4. Export is run-scoped (`/training-runs/{run}/export/{format}`) with no global route; the Data panel renders the absence.
5. Backup and Restore are CLI-only (`uma:backup`); the Data panel renders the absence.

---

### SCR-CAR-001 — Dashboard (Trainer Desk 2.0 landing screen)

#### Purpose

The front door of the 2.0 SPA (`ADR-0020` §1): resume the career in progress, reach the three destinations a returning Trainer wants first, see the newest Veterans, and read the state of the tool's own data. `screen-spec-2.0.md` SCREEN-001; `design-2.0.md` §4 (hierarchy), §29 (empty states), §48 (data versioning).

#### Actor / Access

Any; no auth concept applies (PRD NFR-1).

#### Route / Location

`GET /` name `home` (`DashboardController::index`). Reached by the first nav destination and by any bare loopback URL. The Blade landing page this route used to redirect to (`/training-runs`) is still reachable at its own route; the redirect is gone.

#### Layout / Content

`<Head title="Dashboard">` and the `#title` slot, then five panels in order:

1. **Active career** — trainee name (with the Japanese name in `lang="ja"`), scenario label, career position (`Senior Year · Early July`, on the client's 24-turn grid), logged turn count, Energy, and the scenario's one widget beyond the three every scenario composes (Team Rank, Grade Points, and so on), read from `config/scenarios.php` `widgets[]` by subtraction against the baseline entry. Primary action "Resume Career" links to the run.
2. **Quick actions** — New Career, Legacy Lab, Support Cards. Every destination is a live route; a destination whose slice had not landed would render as a named absence, not a dead link. New Career resolves to the wizard's step 1 (`career.scenario`), which is the click path that makes SCR-CAR-002 reachable at all.
3. **Recent Veterans** — the three newest rows from C3's `ListVeterans`, each linking to its run, plus a link to the full library.
4. **Recent builds** — a named absence. A build is the Career Plan a run holds (`build_target`, C1) and the wizard that enters one is D2–D7, so there is no build record to list.
5. **Data status** — the `GLOBAL DATA ● Current` badge with the verification date from `config('scenarios.verified_at')`, the ruleset row, and the three catalogue counts.

#### States

| State                       | Behaviour                                                                                                                                                                                                                                                                                                                                                     |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (no active career)    | The active-career panel states what is missing, why it matters and what to do: "No active career. Start a new training run.", one sentence on what a career holds, and a "Start a new training run" action that opens the wizard's step 1. The rest of the page is unchanged, so the screen is usable on first load (Paradox of the Active User, plan §13).   |
| Empty (no Veterans)         | The Veterans panel states the absence and the path out of it (finish a run, set it to Completed, save it from the Legacy Lab) with a link to the library. `total` is `0`, never a count printed as though it were a fact about the Trainer.                                                                                                                   |
| Empty (no turn logged)      | Career position and Energy render `N/A` with a `title` naming the absence, never `0` and never Early January (D-220).                                                                                                                                                                                                                                         |
| Empty (scenario resource)   | The resource renders `N/A` with its reason on the element: no column records a scenario resource yet.                                                                                                                                                                                                                                                         |
| Loading                     | Page-level `role="status"` "Loading…" while an Inertia visit started from this screen is in flight. This page has no other user-initiated async action, so no skeleton is drawn (`design-2.0` §30, ADR-0007).                                                                                                                                                 |
| Error                       | Page-level `role="alert"` for a visit that could not complete. The `invalid` and `exception` listeners return `false`, which takes the failure out of Inertia's default modal so this screen's own alert is the single surface; both listeners are removed on unmount. A failed *read* of this page itself renders `SCR-SYS-004`.                             |

#### Validation & Error Handling

No inputs and no writes, so no Form Request and no validation. The ruleset row prints `N/A` with a `title` because `app.ruleset` is null: no source defines a Global ruleset version (`design-2.0` §48).

#### Implementation References

`app/Http/Controllers/DashboardController.php`; `resources/js/pages/Dashboard.vue`; `config/scenarios.php` `verified_at`, `widget_labels`, `baseline`; `App\Models\TrainingRun::stripValues()` / `careerYearForTurn()`; `App\Actions\ListVeterans`; shared props in `app/Http/Middleware/HandleInertiaRequests.php`. Tests: `tests/Feature/DashboardTest.php` (props), `tests/browser/dashboard.spec.ts` (rendered copy, 44px sweep, keyboard path).

#### Status

Implemented 2026-10-06 (slice D1). The empty/loading/error states above ship with the screen.

#### Gaps

Three, each stated on the screen or in the slice report rather than hidden:

1. **Recent builds** is a named absence until the Career Plan wizard lands (D2–D7). Not a bug.
2. **Legacy-goal gaps** is omitted entirely: a gap is the run's targets set against inherited stats, which is the inheritance computation `ADR-0020` §3 bans. C1 and C3 hold no honest comparison.
3. **A scenario resource value** is always `N/A` because no column records one; the run page's resource strip renders it the same way. Wiring Team Rank and Grade Points to their own readers (`latestTeamRank()`, `gradeEarned()`) is a follow-up that needs the D4 provenance badge, because `gradeEarned()` is calculated rather than entered or stored.

---

### SCR-CAR-002 — Scenario Selection (setup wizard step 1)

#### Purpose

Choose the career scenario that decides which resources the tool tracks, which systems it can show and which stat ceilings apply (`docs/proposals/screen-spec-2.0.md` SCREEN-002; `design-2.0` §13, §25, §48).

#### Actor / Access

Any; no auth concept applies (PRD NFR-1).

#### Route / Location

`GET /career/setup/scenario` name `career.scenario` and `PUT /career/setup/scenario` name `career.scenario.store` (`App\Http\Controllers\Career\ScenarioSelectController`). Reached from the Dashboard's New Career quick action and from the primary nav's New Career entry, both of which were repointed here by the 2.0 link audit, and by URL; the wizard shell is `resources/js/layouts/SetupLayout.vue`, which is this screen's and no other's until its sibling steps land.

#### Layout / Content

`SetupLayout` step 1 of 6, then one card per scenario the matrix composes, in the matrix's own order. Each card states the scenario's name, what it optimizes, its systems, its tracked resources, its turn loop, its Global availability date, its ruleset version, a description slot and a recommended-use slot. One `Select Scenario` action per card (`aria-pressed`), and a stored-choice line above the grid.

**Every field is derived from `config/scenarios.php` by `ScenarioSelectController::cards()`, and the page holds no `config()` lookup and no scenario name.** Optimization focus is read off `cap_bonus` (highest-bonus stat, or a stated uniform tie), systems are the `panels` entries that are `true` labelled through `panel_labels`, tracked resources are the `widgets` beyond the baseline entry's own list labelled through `widget_labels`, and the documentation badge comes from `documented`. `CareerScenarioSelectTest` proves the claim by injecting a fifth scenario into the matrix at runtime and reading its card back, so a new scenario is one config entry and zero component edits (D-240, gate G-33).

#### States

| State                          | Behaviour                                                                                                                                                                                                                                                                                                                                                     |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (no draft choice)        | The stored-choice line renders `N/A` with a `title` naming the absence, and every card's action is `aria-pressed="false"`. Nothing is pre-selected, so the screen never implies a choice nobody made.                                                                                                                                                         |
| Empty (no scenario composed)   | The grid is replaced by a statement that the matrix holds no scenario, so the screen cannot show an empty grid and call it a choice. Reachable only by deleting every entry from `config/scenarios.php`.                                                                                                                                                      |
| Absent field per card          | Ruleset `N/A` (`app.ruleset` is null by ruling), Description `N/A` and Recommended use `N/A`, each with a `title` giving the reason. No scenario composes systems or resources beyond the baseline: those read "No scenario system" and "None beyond the generic strip" rather than as blank rows.                                                            |
| Loading                        | `role="status"` "Saving your choice…" while the PUT is in flight, and the clicked button reads "Saving…" while its own visit runs. A custom state is required here because the action is user-initiated (ADR-0007); there is no cold-load skeleton (Inertia renders after the props arrive).                                                                  |
| Error                          | `role="alert"` carrying the Form Request's message, referenced from the actions by `aria-describedby`, so the refusal is announced with the control that caused it. The draft is left untouched: an unknown key never reaches the session.                                                                                                                    |
| After a successful write       | The PUT redirects back to this step and the choice is read from the session, so the pressed state reflects stored data rather than client memory. Focus is restored to the same action in the visit's `onSuccess` — not on mount, because a component instance survives a client-side visit and stealing focus on cold load would be a defect (WCAG 2.4.3).   |
| Unbuilt steps                  | All six wizard steps are live now that Preflight landed (`SCR-CAR-010`, D7), so the step nav renders no named absence. `SetupLayout.vue` keeps its `to: null` branch for a step that lands ahead of its own screen. No dead link. The page carries no "nothing to continue to" note: step 2 exists, and the nav is the way forward.                           |

#### Validation & Error Handling

`App\Http\Requests\Career\StoreScenarioRequest`: `scenario` required, string, and `Rule::in` the keys of `config('scenarios.scenarios')`. A key outside the matrix is refused rather than stored, because `SetupDraft::read()` validates the stored key on the way out and a refused write is honest while a stored-then-dropped one is not. A stale key left by a later config change reads as no scenario.

#### Persistence

The choice goes to `session('career.setup')` through `App\Services\Career\SetupDraft`, **not** to a `training_runs` row. `SetupDraft`'s class docblock carries the reasoning: `RunStatus` has no draft case, and an `Active` run created here would surface as a phantom career on SCR-CAR-001 and as a phantom builder target in SCR-CAR-006. The run is created once, at Preflight (D7). Accepted costs, stated rather than hidden: two tabs share one draft (last write wins), and an expired session loses it.

#### Implementation References

`ScenarioSelectController`, `StoreScenarioRequest`, `SetupDraft`, `SetupLayout.vue`, `pages/Career/ScenarioSelect.vue`, `config/scenarios.php` (`widget_labels`, `panel_labels`, `baseline`, `verified_at`). Tests: `tests/Feature/CareerScenarioSelectTest.php` (10 cases) and `tests/browser/career-scenario-select.spec.ts` (6 cases: card count, baseline-only derivation, badge state, keyboard selection with focus restore and cross-navigation survival, 44px sweep, 320px reflow with a clean console).

#### Status

Implemented 2026-10-06 (slice D2). Steps 2 to 6 of the wizard are D3, D4, the D5 and D6 wizard steps, and D7.

#### Gaps

1. **Description and recommended use are named absences.** Neither the corpus nor the matrix holds a one-line player-facing description or a recommended use for any scenario, so the card prints `N/A` with a reason. Whether `config/scenarios.php` should gain a sourced `description` per scenario is an owner question.
2. **The documentation badge is driven by a flag that currently reads true.** `our_grand_concert.documented` became `true` when commit `ab53861` recovered the 2026-10-05 primary read, so no card renders PARTIALLY DOCUMENTED today. The badge mechanism is pinned against a falsified matrix in the props test, and its positive rendering needs no component change if the owner rules the scenario only partially documented. The scenario's *baseline* state (no systems, no resources) is visible either way.
3. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

---

### SCR-CAR-003 — Trainee Selection (setup wizard step 2)

#### Purpose

Choose the trainee a career is built on, from the fetched catalog, before the run exists (`docs/proposals/screen-spec-2.0.md` SCREEN-003; `design-2.0` §13, §17).

#### Actor / Access

Any; no auth concept applies (PRD NFR-1).

#### Route / Location

`GET /career/setup/trainee` name `career.trainee` and `PUT /career/setup/trainee` name `career.trainee.store` (`App\Http\Controllers\Career\TraineeSelectController`). Reached from `SetupLayout`'s step nav (step 2) and by URL. Each row's "View Profile" action leads to SCR-CAR-004 at `GET /career/setup/trainee/{umamusume}` (`career.trainee.profile`), which binds the local primary key rather than the slug, because the id this step stores is the one a later step writes to `training_runs.umamusume_id`.

#### Layout / Content

`SetupLayout` step 2 of 6, then a scenario line read through `SetupDraft::scenarioLabel()` (`N/A` with a `title` when no scenario has been chosen), a stored-choice line, the filter form, and one card per trainee in the catalog's own order (debut form first, then Global release date, then card id, with solo-sourced forms hidden).

The filter form offers four kinds of filter and one sort, every one a real column: name/slug substring search; a Surface facet over `aptitude_turf`/`aptitude_dirt`; a Distance facet over the four distance columns; a Running style facet over the four style columns (facet letters are the parser's `S A B C D E F G` domain, `GametoraCharacterParser.php:143`); a unique-skill select over the `character_cards.skills_unique` exports that survive `Skill::scopeAvailableOnGlobal()`; a sort allowlist over the same columns with a direction control; Filter and Clear filters. The facet vocabulary, the letter domain, the sort keys and the unique-skill options all arrive as props from `TraineeSearchRequest` and the controller, so the page holds no column name, no letter list and no scenario name.

Each card prints name and `name_ja` (`lang="ja"`), release status, the `size-10` `ArtworkSlot` row geometry (`reserve`, decorative `alt=""`, because the row already prints the name), the ten `AptitudeBadge` letters (letter plus band word, never colour alone), per-form `RarityChip` with the client's rarity word and a debut marker, a primary "Select Trainee" action (`aria-pressed`) and a "View Profile" link. No growth-rate figure and no scenario-suitability rank appear, because no column holds either (see Gaps).

#### States

| State                                               | Behaviour                                                                                                                                                                                                                                                                                                                                     |
| --------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (no draft choice)                             | The stored-choice line renders `N/A` with a `title` naming the absence, and every action is `aria-pressed="false"`. Nothing is pre-selected, so the screen never implies a choice nobody made.                                                                                                                                                |
| Empty (no row matches the filters)                  | The grid is replaced by one sentence stating that no trainee matches, with the path out of it: the roster is filled by seed data or `php artisan uma:fetch`, and Clear filters shows every row the catalog holds.                                                                                                                             |
| Empty (no aptitude published)                       | The row's grid is replaced by "Aptitude: N/A" with a `title`: the parser writes all ten columns or none, so one absent letter means the whole set is absent (D-220).                                                                                                                                                                          |
| Absent filter (growth rate, scenario suitability)   | The form does not offer them at all: no column holds either figure, and a facet for a column that does not exist would be a filter that can never narrow. Recorded in Gaps.                                                                                                                                                                   |
| Loading                                             | `role="status"` "Loading the roster…" while a filter visit is in flight, and the pressed action reads "Saving…" while its own PUT runs. A custom state is required here because the action is user-initiated (ADR-0007); there is no cold-load skeleton (Inertia renders after the props arrive).                                             |
| Error (refused filter)                              | The Form Request redirects back to the step and the message is listed per field with `role="alert"`, so the refusal is announced where it was typed. The roster stays unfiltered: a refused value never narrows the list as though it had been applied.                                                                                       |
| Error (refused write)                               | `StoreTraineeRequest` messages re-render this step; the draft is left untouched, so an unknown id never reaches the session.                                                                                                                                                                                                                  |
| After a successful write                            | The PUT redirects back to this step and the pressed state is read from the session, so it reflects stored data rather than client memory. Focus is restored to the same action in the visit's `onSuccess` (not on mount: a component instance survives a client-side visit, and stealing focus on cold load would be a defect, WCAG 2.4.3).   |
| Unbuilt steps                                       | All six wizard steps are live now that Preflight landed (`SCR-CAR-010`, D7), so the step nav renders no named absence. The Legacy wizard step landed, so step 4 is a live link.                                                                                                                                                               |

#### Validation & Error Handling

`App\Http\Requests\Career\TraineeSearchRequest`: `search` nullable string, max 255; `surface`, `distance` and `style` nullable and in the seven letters the parser writes; `skill` nullable integer existing in `skills.export_id`; `direction` in `asc`/`desc`. An empty facet value reads as "no filter chosen". An unknown `sortBy` degrades to the default (`name`) rather than refusing, the way `PageSize::clamp` degrades: the key usually arrives from a pasted URL, and the allowlist is the only path into `orderBy()`, so an off-list key never becomes SQL. An aptitude sort ranks S to G through a CASE expression rather than the alphabet, so a G trainee is not presented as the best choice in the list. `App\Http\Requests\Career\StoreTraineeRequest`: `umamusume_id` required, integer, existing in `umamusume.id`.

#### Persistence

The choice goes to `session('career.setup')` through `App\Services\Career\SetupDraft`, the same draft step 1 writes, **not** to a `training_runs` row: an `Active` run made here would surface as a phantom career on SCR-CAR-001 and as a phantom builder target in SCR-CAR-006, and the run is created once, at Preflight (D7). SCR-CAR-002's section carries the full reasoning; the accepted costs are the same (two tabs share one draft, an expired session loses it).

#### Implementation References

`TraineeSelectController`, `TraineeSearchRequest`, `StoreTraineeRequest`, `SetupDraft`, `SetupLayout.vue`, `pages/Career/TraineeSelect.vue`, `AptitudeBadge.vue`, `RarityChip.vue`, `ArtworkSlot.vue`; `App\Services\PageSize`. Tests: `tests/Feature/CareerTraineeSelectTest.php` (props, filters, sort, draft) and `tests/browser/career-trainee-select.spec.ts` (rendered copy, filters and the empty match, keyboard selection with focus restore, the 44px sweep, 320px reflow, clean console).

#### Status

Implemented 2026-10-06 (slice D3). Step 4 and beyond are the D5-wizard, D6-wizard and D7 slices.

#### Gaps

1. **Growth rate and scenario suitability are omitted, with citations.** Neither has a column: `docs/research-scratch/GOVERNANCE.md:600` defers a growth-rate column ("No column added now") and `docs/research-scratch/DESIGN-CORPUS.md:727-728` keeps the figure off a rendered surface; a suitability facet would read `config/scenarios.php`'s `scenario_links` cast list as a ranking, which is a listing, not a judgement the tool holds a source for.
2. **Rarity is a form's fact, not a trainee's.** `character_cards.rarity` is the only rarity in the schema, so the card prints it per costume form and offers no trainee-level aggregate, which would state a property the data does not carry.
3. **The unique-skill option list is built from the card rows in PHP.** `skills_unique` is a JSON column and a portable SQL distinct over it does not exist; this is one bounded read per page load on the current catalog. The upgrade path is a pivot if the catalog grows an order of magnitude.
4. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

---

### SCR-CAR-004 — Trainee Profile (setup wizard step 2, read screen)

#### Purpose

State what the catalog holds about one trainee before the choice is committed: basic facts, her own aptitude letters, and the skills of each costume form (`docs/proposals/screen-spec-2.0.md` SCREEN-004; `design-2.0` §13, §17).

#### Actor / Access

Any; no auth concept applies (PRD NFR-1).

#### Route / Location

`GET /career/setup/trainee/{umamusume}` name `career.trainee.profile` (`App\Http\Controllers\Career\TraineeProfileController`), reached from SCR-CAR-003's "View Profile" action. The parameter binds the local primary key; an unknown id is the binding's 404, which is what a stale bookmarked draft deserves. The Select action posts to the shared `career.trainee.store` PUT.

#### Layout / Content

`SetupLayout` step 2 of 6, a back link to the roster and the scenario line, then four sections:

1. **Basic** — `size-16` artwork, name with `name_ja` (`lang="ja"`), availability, voice actor, birthday, height, Version and whether the row is Trainer-edited. The profile document is read through `hasFullBirthday()`/`hasThreeSizes()` guards, so a partial birthday or measurement is never printed as a whole one. There is no weight column, so no weight row exists.
2. **Aptitude** — the ten letters once for the trainee, through `AptitudeGrid.vue`; never repeated per costume form (ADR-0008), and stated as aptitude rather than as an "ideal" judgement.
3. **Costume forms** — one block per confirmed form: `size-10` artwork, form title, `RarityChip` with the client's rarity word, debut marker and Global release date, then the form's four skill lists (unique, starting, awakening, event). Skill names resolve through `Skill::scopeAvailableOnGlobal()`, so every printed name has a link that resolves on the Global client; `sp_cost` prints `N/A` with a `title` where the source publishes none; an id that resolves to no Global skill is counted in words rather than dropped. `skills_evo` is deliberately not listed (SCR-CAT-002's ruling).
4. **Build analysis** — recommended stat distribution, useful inheritance and useful support types as named absences, each `N/A` with its reason on the element, plus the distance/style reading labelled as aptitude. Career goals is not rendered at all: `trainee_goals` has never been migrated (the filing is reserved as KI-34), and `race_catalog_slots`' own comment says its obligation rows are scenario-scoped and "deliberately not the per-character Goal".

#### States

| State                                                                 | Behaviour                                                                                                                                                                                                                                        |
| --------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Empty (no draft choice)                                               | The Select action is `aria-pressed="false"` and no "Chosen" word appears, so the screen never implies a choice nobody made.                                                                                                                      |
| Empty (no profile document)                                           | Voice actor, birthday and height render `N/A` with a `title`; the rest of the screen is unchanged, so a trainee fetched without a profile document is still readable.                                                                            |
| Empty (no confirmed costume form)                                     | The forms list is replaced by one sentence: every form for this trainee is confirmed by a single source, so no skill list can be stated until a second source confirms one.                                                                      |
| Empty (no aptitude published)                                         | "Aptitude: N/A" with a `title`, the same all-or-nothing read as the roster.                                                                                                                                                                      |
| Empty (a skill list publishes nothing this catalog can resolve)       | `N/A` with a `title` when the source lists none; where the source lists ids this catalog cannot resolve, the count is stated in words instead of a short list presented as the whole (6 of the catalog's 237 awakening ids are in that state).   |
| Absent (version, stat distribution, inheritance, support types)       | Each renders `N/A` with its reason on the element: an unrecorded value is never a dash and never a default.                                                                                                                                      |
| Omitted (career goals, growth rates, hint skills, evolution skills)   | No heading and no row: each has no column or no per-trainee store, and a heading over nothing would advertise a fact the tool is known not to hold.                                                                                              |
| Loading                                                               | The Select action reads "Saving…" while its PUT is in flight (ADR-0007). The page itself is a read with no other async action, so no skeleton is drawn.                                                                                          |
| Error                                                                 | A refused write re-renders SCR-CAR-003 with the message: `StoreTraineeRequest` redirects to the roster step, which is where the choice is made. A failed read of this page renders SCR-SYS-004.                                                  |
| After a successful write                                              | The PUT redirects to SCR-CAR-003, where the stored choice rather than this screen's client state states what is selected.                                                                                                                        |

#### Validation & Error Handling

The read takes no untrusted input beyond the route key; the only write is the shared `StoreTraineeRequest` (see SCR-CAR-003). An unknown route id is a 404 through the model binding before any query runs.

#### Persistence

The same `session('career.setup')` draft as SCR-CAR-003, through the same PUT. Nothing on this screen writes a database row.

#### Implementation References

`TraineeProfileController`, `pages/Career/TraineeProfile.vue`, `AptitudeGrid.vue`, `SkillRow.vue`, `RarityChip.vue`, `ArtworkSlot.vue`; `App\Models\Skill::scopeAvailableOnGlobal()`. Tests: `tests/Feature/CareerTraineeSelectTest.php` (props, resolution counts, absences) and `tests/browser/career-trainee-select.spec.ts` (rendered sections, N/A titles, the omitted headings).

#### Status

Implemented 2026-10-06 (slice D3).

#### Gaps

1. **Career goals is omitted, not stubbed.** No `trainee_goals` migration, model or factory exists; the filing is reserved as KI-34 and needs a fetch-source decision, so the section the SCREEN-004 brief asks for has nothing to render today.
2. **Growth rates, hint skills and evolution skills are omitted.** Growth rates have no column and are kept off rendered surfaces by `DESIGN-CORPUS.md:727-728`; hint skills live only on `support_cards`; `skills_evo` is stored but SCR-CAT-002's ruling refuses to list evolved pairs.
3. **Six of the catalog's 237 awakening ids name no Global skill row** and are counted rather than resolved; closing that needs a source comparison, not a UI change.
4. **The three build-analysis absences are by ruling**, not by omission: there is no recommendation column, `support_cards` carries no `umamusume_id`, and inheritance is run-scoped. The rows stay in place so a future source has a surface waiting.
5. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

---

### SCR-CAR-005 — Build Target (setup wizard step 3)

#### Purpose

Record what the Trainer intends to build for this career, as one object entered before the run exists (`docs/proposals/screen-spec-2.0.md` SCREEN-005; `design-2.0` §49 for the provenance states; `ADR-0020` §1, §2).

#### Actor / Access

Any; no auth concept applies (PRD NFR-1).

#### Route / Location

`GET /career/setup/target` name `career.target` and `PUT /career/setup/target` name `career.target.store` (`App\Http\Controllers\Career\BuildTargetController`). Reached from `SetupLayout`'s step nav (step 3) and by URL. The write is validated by `App\Http\Requests\Career\StoreDraftBuildTargetRequest`, which extends the run-scoped `StoreBuildTargetRequest` so both entry points share one rule set and one `payload()` builder; the ceiling resolves against the route's run when there is one and against `SetupDraft::planningRun()` when there is not.

#### Layout / Content

`SetupLayout` step 3 of 6, heading "Your target", then the scenario line (label through `SetupDraft::scenarioLabel()`, `N/A` with a reason when none is chosen), the absence line when no target is stored, and one form with three groups plus a summary:

1. **What the career is for** — purpose, distance, surface and style. Purpose options are `BuildPurpose`'s four cases labelled through `uma.build_purpose`; the other three vocabularies are `BuildTargetPayload::DISTANCE_BANDS`, `::SURFACES` and `::STYLES`, sent from the controller so the form and the payload's own reader cannot drift. One line discloses that the design also names a fifth purpose (Competitive Build) this tool does not record.
2. **Stat targets** — five numeric inputs, one per `config('scenarios.stat_order')` entry, each with its cap printed beside it (`ScenarioCaps::forRun`, the same call the turn validator makes, `ADR-0015`) and a labelled bar showing the entered value against that cap. The bar never renders without its number beside it, and it is not a readiness verdict: whether a target is reachable in the turns left is held computation in this repository.
3. **Skill priorities** — free-text entry plus an ordered list with "Move up", "Move down" and "Remove" buttons, each at least 44px; no drag path is required (WCAG 2.5.7). One line states that a risk tolerance and per-skill marks (Required, High, Optional, Ignore) are not recorded.
4. **Summary** — one sentence assembled by the page from the entered values alone (`ProvenanceBadge` state `calculated`), with an empty field named in the sentence rather than omitted.

`ProvenanceBadge.vue` is the single owner of the four provenance states from `design-2.0` §49: ✓ Confirmed, ∑ Calculated, ~ Estimated, ? Unknown, each a distinct glyph and a distinct word held in one map, and no other source repeats either. The accessible name is the state word plus the caller's `title` when one was given; the glyph is `aria-hidden="true"`; and the badge sits beside the figure it labels, never in a page-level legend (Law of Proximity).

#### States

| State                                           | Behaviour                                                                                                                                                                                                                                                                                           |
| ----------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (no stored target)                        | The page states that no target is recorded and every field starts empty; no stat input is pre-filled with a zero. The summary names each absence.                                                                                                                                                   |
| Empty (no scenario chosen)                      | The caps fall back to the base cap with no bonus (`ScenarioCaps::forRun(null)`'s own answer) and the scenario line renders `N/A` with a `title` saying so. Nothing lends a bonus nobody chose.                                                                                                      |
| Absent (design items the payload cannot hold)   | The fifth purpose, risk tolerance and per-skill marks are each stated in one line: `BuildTargetPayload::KEYS` is exactly six keys and `assertKeys()` refuses an unknown one, so offering them would be a stored-shape change needing owner approval.                                                |
| Loading                                         | The submit button reads "Saving…" and a `role="status"` line says "Saving your target…" while the PUT is in flight (ADR-0007: the action is user-initiated). There is no cold-load skeleton (Inertia renders after the props arrive).                                                               |
| Error                                           | Each message renders with `role="alert"` under its own control and is referenced by `aria-describedby`; on a failed submit focus moves to the first invalid control in DOM order. The refusal names the bound it enforced ("Speed must be between 0 and 1200.", D-56) and the draft is untouched.   |
| After a successful write                        | The PUT redirects back to this step with "Build target saved." and the stored state is read from the draft, so the form reflects stored data rather than client memory.                                                                                                                             |
| Unbuilt steps                                   | All six wizard steps are live (SCR-CAR-010 landed 2026-10-06), so the step nav renders no named absence; step 3 carries `aria-current="step"`.                                                                                                                                                      |

#### Validation & Error Handling

`StoreDraftBuildTargetRequest` (extends `StoreBuildTargetRequest`): `purpose` required and one of `BuildPurpose`'s four cases; `distance`, `surface` and `style` required and in `BuildTargetPayload`'s constants; `targets` required and key-restricted to the stat matrix, all five stats required by name, `min:0`, each checked in `withValidator` against `ScenarioCaps::forRun` for the draft's planned scenario with the bound named. A hand-made POST cannot store a sixth stat, an unknown purpose or a value outside the vocabularies. The form is `novalidate` by design: the `max` attribute mirrors the cap as a browser convenience only, and the server's message is the one the page renders. The server is the authority on the clamp (`ADR-0015`).

#### Persistence

The target goes to `session('career.setup')` under the `build_target` key through `App\Services\Career\SetupDraft`, **not** to a `training_runs` row: the run is created once, at Preflight (D7), and SCR-CAR-002's section carries the reasoning. `SetupDraft::write()` merges into the raw session bag, so a later step-1 or step-2 write cannot drop the target. The run-scoped write (`runs.build-target.update` → `training_runs.build_target`) is unchanged and shares the same rule set.

#### Implementation References

`BuildTargetController`, `StoreDraftBuildTargetRequest`, `StoreBuildTargetRequest`, `SetupDraft` (`planningRun()`, `buildTarget()`, `write()`), `ScenarioCaps::forRun`, `BuildTargetPayload`, `BuildPurpose`, `uma.build_purpose`, `pages/Career/BuildTarget.vue`, `components/ProvenanceBadge.vue`, `SetupLayout.vue`. Tests: `tests/Feature/CareerBuildTargetTest.php` (props, draft round trip, the ceiling clamp with its bound named, refusal of off-vocabulary values, the untouched run-scoped write, the glyph sweep) and `tests/browser/career-build-target.spec.ts` (rendered copy, the save round trip with the calculated summary, the refused stat with focus, keyboard reorder, the 44px sweep, 320px reflow with a clean console).

#### Status

Implemented 2026-10-06 (slice D4).

#### Gaps

1. **The design's fifth purpose is not recorded.** The brief names six; `BuildPurpose` holds four, and its docblock already maps one design name (General Training) onto a stored case. "Competitive Build" has no case, and adding one would widen the advisor's contract, so the page discloses the gap in one line instead. Whether the case should exist is an owner question.
2. **Risk tolerance and per-skill marks (Required, High, Optional, Ignore) are not recorded.** Both would need a new `build_target` key, and `BuildTargetPayload::assertKeys()` refuses an unknown one, so this is a stored-shape change needing owner approval. `skill_priorities` stays a flat ordered list of names.
3. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

---

### SCR-CAR-006 — Legacy Lab (browse, run builder and compare)

#### Purpose

Record-only browse and compare of the Trainer's saved Legacies, and the six-node ancestry editor for one run (`ADR-0010`, `ADR-0020` §3, PRD FR-G). The number `SCR-CAR-006` is claimed by the builder here, not by the wizard: the `SetupDraft` prose and `TraineeSelectController` already read this run's builder as "the phantom builder target in SCR-CAR-006", and that is the surface this row registers.

#### Actor / Access

Any; no auth concept applies (PRD NFR-1).

#### Route / Location

`GET /legacy` (`legacy.index`), `GET /legacy/{run}` (`legacy.builder`), `PUT /legacy/{run}` (`legacy.update`), `GET /legacy/compare` (`legacy.compare`) (`App\Http\Controllers\LegacyController`). `{run}` is constrained to a number and `/legacy/compare` is declared before it, so `compare` can never read as a run id (routes `web.php:127-139`).

#### Layout / Content

Three surfaces, one record-only notice (`LegacyController::RECORD_ONLY_NOTICE`):

1. **Browse (`pages/Legacy/Index.vue`)** — the Veteran library through `ListVeterans`: a `totalCount` line, a `tag` facet that narrows, and the `filters`, `scenarios` and `trainees` props whose three search facets no query answers and so render as unstyled (plan §7 C3). An empty library states what is missing, why it matters and what to do, in the §29 voice.
2. **Builder (`pages/Legacy/Builder.vue`)** — six nodes as nested list items with `border-l` connectors; parents assigned through a labelled `<select>`, never a drag (WCAG 2.2 SC 2.5.7); ancestors and Sparks entered as names; each Spark a `SparkChip` spelling kind and star count so no fact is carried by colour alone (WCAG 1.4.1). The edit form seeds from the stored payload, including the `affinity` grade. The chance a Spark rolls is `N/A` with its reason in a `title`.
3. **Compare (`pages/Legacy/Compare.vue`)** — one table, a run per column and each stored property as a row header (16 rows: the eleven stored properties plus the five Spark kinds), scrollable inside a focusable labelled region at narrow widths. Nothing is scored, ranked or totalled (`ADR-0020` §3), and the caption says so in words.

#### States

| State                  | Behaviour                                                                                                                                                                             |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty library          | The browse screen states the empty library in words and points at completing a run to fill it; the builder's parent picks have no rows and say so on step 4's twin (`SCR-CAR-008`).   |
| Unbuilt builder        | Only the Active status opens the builder (`ADR-0010` D-260 fixity); a `Completed`/`Retired` run's write 404s.                                                                         |
| Nothing to compare     | `GET /legacy/compare` with no `runs[]` states that nothing is selected rather than rendering an empty table.                                                                          |
| Refused write          | `StoreLegacySelectionRequest` re-renders the builder with the message; nothing is stored.                                                                                             |
| Stored figure absent   | `N/A` with a `title` distinguishing "you did not record her rank" from "she has no rank", and the star-roll chance as a permanent named absence.                                      |

#### Validation & Error Handling

`LegacySearchRequest` for the browse query, `LegacyCompareRequest` for `runs[]`, and `StoreLegacySelectionRequest` for the write (the two parent identities must differ and neither may be the run's own trainee). Duplicates, unknown ids and off-dictionary Spark kinds and grades are refused rather than stored.

#### Persistence

`training_runs.legacy_selection` json plus the two `inheritance_parent_*` ids, written in one statement (`ADR-0010`). Ancestors, Sparks and grades live in the json; no extra columns are added. The wizard's step 4 (`SCR-CAR-008`) writes the same shape to the session draft until Preflight creates the run.

#### Implementation References

`LegacyController`, `LegacySearchRequest`, `LegacyCompareRequest`, `StoreLegacySelectionRequest`, `ListVeterans`, `AncestryGraph`, `LegacySelectionPayload`, `pages/Legacy/{Index,Builder,Compare}.vue`, `components/legacy/{AncestryNode,SparkChip}.vue`. Tests: `LegacyLabPageTest`, `LegacySelectionSchemaTest`, `tests/browser/legacy.spec.ts`.

#### Status

Implemented 2026-10-05 (slice D5, run-scoped, commit `05e9584`); row and section registered 2026-10-06 when `SCR-CAR-008`/`009`/`010` were filed.

#### Gaps

1. **No inheritance computation.** No outcome, expected stat, Affinity payout or Spark roll chance is derived (`ADR-0020` §3, PRD §6.4), and the `probability` every node carries is null by construction.
2. **The star-roll table is a sourced absence.** The odds would come from a corpus pair flagged stale (`REFERENCE` §1.5.3), so the value renders `N/A` with the reason on the element.
3. **Compare is read-only.** No score, ranking or "best" marker exists (`ADR-0020` §3), which the caption states rather than implying.

---

### SCR-CAR-007 — Support Deck Builder (run-scoped)

#### Purpose

Choose the six support cards an existing run starts with (`ADR-0014`, PRD FR-A-4). The wizard's step 5 (`SCR-CAR-009`) is the draft twin of this surface, entered before the run exists.

#### Actor / Access

Any; no auth concept applies (PRD NFR-1). A run must exist; the surface posts to `runs.deck.sync`.

#### Route / Location

`GET /training-runs/{run}/deck` (`runs.deck`) and `POST /training-runs/{run}/deck` (`runs.deck.sync`) (`App\Http\Controllers\TrainingRunController`), under `ADR-0014`.

#### Layout / Content

Six position slots, position six always labelled "Friends" whatever card it holds (`ADR-0014` correction 1); one picker at a time opened by `?deck_slot=`, narrowed by `?type=` and `?q=` server-side (a 559-row catalogue is not handed to the browser as option nodes). `DeckAnalysis` prints the counts `DeckAnalysis::build()` makes from `SupportCardEffects::atCap()` anchors, and nothing more. The ownership flag has no `deck_slots` column, so the toggle is client-side only, resets to Owned on reload, and the page says so under the deck.

#### States

| State             | Behaviour                                                                                                                               |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| Empty deck        | Every slot prints "Not equipped" with the next action; an empty deck is legal and rendered as a named absence.                          |
| One picker open   | `?deck_slot=` names the slot being edited; closed slots post hidden inputs, and only one picker is open at a time.                      |
| Refused write     | A duplicate card or an id outside the catalogue re-renders with the message ("Break the duplicate instead."); nothing is written.       |
| Ownership         | Client-side `OWNED`/`RENTED` toggles; a reload returns the slot to Owned, disclosed because the table has no column to hold the flag.   |

#### Validation & Error Handling

`DeckPickerSearchRequest` for the facet query and `StoreDeckRequest` for the write: exactly six positions, cards that exist, no duplicate, and the off-dictionary flag refused. The write deletes and re-inserts all six rows in one transaction, so a slot is a position.

#### Persistence

Six `deck_slots` rows per run, replaced whole on save. The Scenario Link badge is derived on read, never stored (`ADR-0014` correction 3). Preflight (`SCR-CAR-010`) writes the same six positions from the wizard's draft key.

#### Implementation References

`TrainingRunController::{deck,syncDeck}`, `DeckPickerSearchRequest`, `StoreDeckRequest`, `DeckAnalysis`, `SupportCardEffects`, `DeckSlot`, `SupportCard`, `pages/Support/Builder.vue`, `components/support/{SupportSlot,SupportTypeMark,DeckAnalysis}.vue`. Tests: `SupportDeckBuilderTest`, `RunDeckTest`, `tests/browser/support-deck.spec.ts`.

#### Status

Implemented 2026-10-05 (slice D6, run-scoped, commit `05e9584`); row and section registered 2026-10-06 when `SCR-CAR-008`/`009`/`010` were filed.

#### Gaps

1. **No ownership column.** `deck_slots` has no flag and adding one is the owner's call (`ADR-0014`), so the run-scoped surface's toggle is client-side only and resets on reload; only the wizard keeps the flag, in the draft, until Preflight.
2. **No score, ranking or replacement suggestion.** `DeckAnalysis` restates the counts and nothing more (`ADR-0020` §3, PRD FR-G-4).
3. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

---

### SCR-CAR-010 — Career contract, "Preflight" (setup wizard step 6)

`GET /career/setup/preflight` name `career.preflight`, and the wizard's one write: `PUT /career/setup/preflight` name `career.preflight.store`, both in `App\Http\Controllers\Career\PreflightController`. The brief's title for the screen is "Career Contract"; the heading and the step label read "Career contract" and "Preflight". Reached from `SetupLayout`'s step nav (step 6) and by URL.

The page composes the five entered steps into **one `contract` prop**: Build (trainee, scenario, your target, the six-node ancestry with its Affinity), Support deck (the six positions with the D6 analysis beside them) and Target (the five stats, the race profile, the skill priorities, and the ruleset snapshot). Nothing is re-asked (WCAG 3.3.7): every fact is read from `session('career.setup')` through `SetupDraft`, and each Edit link returns to the step that owns it.

| State                           | Behavior                                                                                                                                                                                                                                                                                                              |
| ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (a section not entered)   | That section renders `N/A` with a `title` saying which step enters it, and one warning per missing section links to it. Nothing else is invented and the missing section is never a zero.                                                                                                                             |
| Warnings present                | Each warning is one line: a glyph (`aria-hidden`), the title in words, the reason citing the entered values, and a link to the step. `text-goal-line`, not the error red: a warning is not a fault and never blocks.                                                                                                  |
| No warnings                     | One line says so in the same words the check uses ("nothing in the entered data reads as a gap this tool can see"), so silence is a claim rather than an empty box.                                                                                                                                                   |
| Deck analysis                   | `components/support/DeckAnalysis.vue` renders the same counts the deck step renders, from `DeckAnalysis::build()`; no score, no ranking, no replacement suggestion (`ADR-0020` §3).                                                                                                                                   |
| Loading                         | The submit button reads "Starting…" and disables while the PUT is in flight (ADR-0007: `Start Career` is user-initiated). There is no cold-load skeleton.                                                                                                                                                             |
| Error (a refused write)         | A `role="alert"` block with `tabindex="-1"` names the section and focuses itself; the message is the failing step's own message plus the instruction to reopen that step. Nothing was created.                                                                                                                        |
| After a successful write        | One transaction creates the run, its six `deck_slots` rows, the `legacy_selection` json with the two `inheritance_parent_*` ids and the `build_target` json; the draft is then reset and the response redirects to `runs.cockpit` for the new run (`SCR-CAR-011`), which is where a career is read from every turn.   |

#### Warnings, and the ones deliberately not shipped

Derivable from entered data, and each is a test in `tests/Feature/CareerPreflightTest.php`: a missing section (one per key, in step order); a deck with fewer than six cards (`deck_incomplete`); an ancestry slot with no parent (`legacy_slot_empty`); a target distance, surface or style whose trainee aptitude is D or lower (`aptitude_below_target`, the threshold being design-2.0 §17's own bands: S/A Strong, B/C Neutral, D/E Weak, F/G Very weak); a skill priority that none of this setup's sources names — the six deck cards' hint and event skills, the trainee's own cards' skills, and the Spark targets recorded on step 4 (`skill_priority_unsourced`, and the message says which sources were read).

**Not shipped, with the reason.** "Weak stamina plan" and "poor support synergy" would need per-training yields and synergy weights this repository does not hold (`PRD` §6.4, `ADR-0020` §3). "Missing scenario requirement" would need per-scenario requirement data; `config/scenarios.php` carries labels, widgets, panels and flags, not requirements. "Low factor probability" is held with the star-roll table (`REFERENCE` §1.5.3). A deck category with nothing in it is **not** a warning: the deck's own analysis prints that as a weakness line, and repeating it as a warning would state the same count twice.

#### Validation & Error Handling

`StartCareerRequest` composes the run's facts out of the draft in `prepareForValidation()` — the posted body is never trusted, because the session bag is the only writable copy — and `withValidator()` re-runs each step's own Form Request against its slice (`StoreDraftDeckRequest`, `StoreDraftLegacyRequest`, `StoreDraftBuildTargetRequest`, each hydrated with `createFrom()`), so a scenario removed from config, a deleted support card, a duplicate card, a parent who is the trainee herself or a Veteran whose row is gone is refused by the rule set that owns it. The payload readers (`LegacySelectionPayload::fromArray()`, `BuildTargetPayload::fromArray()`) are the second gate for an unknown key, which a rule set cannot see. A refusal reports the section, not the field: the draft is stale and the next action is the step that owns it. Only this write blocks; the warnings above never do.

#### Implementation References

`PreflightController`, `StartCareerRequest`, `SetupDraft` (all six keys), `AncestryGraph::build()`/`parentNames()`, `LegacySelectionPayload`, `BuildTargetPayload`, `BuildPurpose`, `DeckAnalysis::build()`/`inputFor()`, `SupportCardEffects`, `DeckSlot::positionLabel()`, `Veteran::traineeId()`, `pages/Career/Preflight.vue`, `components/support/DeckAnalysis.vue`, `layouts/SetupLayout.vue`. Tests: `tests/Feature/CareerPreflightTest.php` (the full contract, each warning trigger, the created run with its deck, ancestry, target and parent ids, the three refusals, the spent draft) and `tests/browser/career-preflight.spec.ts` (the carried values, the warning copy, an Edit round trip, `Start Career` landing on the run, and focus on the refusal).

#### Status

Implemented 2026-10-06 (slice D7).

#### Gaps

1. **`Start Career` is its own route, not a post to `runs.store`.** The plan's brief said "posting to the existing run-creation route"; `StoreTrainingRunRequest` validates the run row alone, so that endpoint would silently drop the deck, the ancestry and the target, and it runs no step's rules. The write therefore lives on `career.preflight.store` and writes through the same models (`TrainingRun::create`, `deckSlots()->create`) with every step's Form Request re-run first. Reported as a deviation.
2. **No costume card, so no starting skills.** The wizard collects no `character_card_id` and the draft has no key for one, so `TrainingRunController::prePopulateSkills()` has nothing to seed from; `runs.import` has the same shape for the same reason.
3. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

---

### SCR-CAR-011 — Career Cockpit

`GET /training-runs/{run}/cockpit` name `runs.cockpit`, `App\Http\Controllers\Career\CockpitController`. SCREEN-009, plan §8 D8. The screen a Trainer reads on every turn. It descends from the run's own URL, as the brief says it does, and it is **read-only except one write it does not own**: the manual correction posts to the existing `runs.turns.update` and its `StoreTurnEntryRequest` (design-2.0 §38). No new route for a turn, no new column, no migration.

Reached three ways: the run record screen's own "Career Cockpit" link (`runs.show` carries `cockpit_url`), the redirect after `Start Career` (`SCR-CAR-010`), and the Dashboard's active-career "Resume Career" action (`SCR-CAR-001`) — a career that has just started, or is being resumed, opens the screen it is read from every turn. The record screen keeps every route it had and stays one link away in the career bar.

#### Layout

Desktop is the brief's three columns — timeline 20%, current decision 50%, advisor 30% — with the scenario region full width beneath them (§12, design-2.0 §23). Tablet is two columns with the timeline as a horizontal strip; mobile is one column in the brief's order: header, current state, recommendation, action, scenario, timeline (§33, design-2.0 §40). The order is CSS only: no element is hidden at a breakpoint, so nothing leaves the accessibility tree and no primary decision needs a horizontal scroll.

The recommendation stays above the actions on mobile (design-2.0 §40), and the cockpit is the only screen whose action grid carries a `RECOMMENDED` marker — exactly one, and none when the advisor declines (Von Restorff, plan §13).

#### Components

`layouts/CareerLayout.vue` (the career bar over `AppLayout`), `components/career/CareerHeader.vue`, `components/career/CareerStatePanel.vue`, `components/career/ActionGrid.vue`, `components/career/RecommendationCard.vue`, `components/career/AdvisorRail.vue`. Reused, not rewritten: `ResourceStrip` (the scenario's own widgets), `EnergyGauge`, `MoodPill`, `RaceCalendar`.

_Dated note 2026-10-07 (D14a): the last of those four is no longer reused here. `components/career/RunRaceStrip.vue`
takes the left column, and `RaceCalendar.vue` is unchanged and stays the run record screen's component
(`SCR-RUN-001`). Gap 2 below carries the reason and the original sentence._

#### The advisor contract, and what it deliberately cannot carry

`advisor` is exactly `{ action, band, reasons, alternative, risk }` (plan §8, "Recommendation contract (D8)"). There is **no score and no numeric confidence**: both are unsourced (`ADR-0001` §3) and C2's `Advice` has no field for either. `band` is `AtOrAboveAdvisory` / `BelowAdvisory` / null, printed as a word plus a glyph so the state is never colour-only. `risk` is null in this slice: a delay or a reachability verdict is held computation (`ADR-0020` §3).

| State                                      | Behavior                                                                                                                                                                                                                                                                       |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Empty (no turn logged)                     | A `role`-less panel headed "No turn recorded yet" says what is missing (Energy, Mood, Fans, Skill Points and the five stats), why it matters (the advisor has nothing to rank), and what to do (a link to the run record screen). The grid still renders; nothing is a zero.   |
| Energy absent                              | C2 declines to rank, and the page carries the refusal across: `band` and `action` are null and the one reason line is C2's own sentence. The card prints "Recommendation unavailable because Energy has not been entered." No band, no ranking, no marker.                     |
| Energy present, no target                  | `band` is set, `action` is null, and the reason line says no build target is set. No marker.                                                                                                                                                                                   |
| Energy present, target met                 | `band` is set, `action` is null, and the reason line says every stat is at or above its target. No marker.                                                                                                                                                                     |
| Energy present, deficit                    | `action` is the largest-deficit stat (or `Rest` below the advisory line), `reasons` carries C2's own reason plus the Energy the action leaves behind, and exactly one grid entry carries `RECOMMENDED`.                                                                        |
| Missing value in the header or the panel   | `N/A` with a `title` naming which half of the fact is missing. Never a default, never a dash (`AGENTS.md` §5, D-220).                                                                                                                                                          |
| Loading                                    | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight, because the only user-initiated async actions are the calendar's year tabs and the correction. No cold-load skeleton.                                                                            |
| Error (a visit failed)                     | `role="alert"`: "That screen did not load. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface.                                                                                           |
| Error (a refused correction)               | A `role="alert"` block with `tabindex="-1"` inside the disclosure names the fields and focuses itself; the message is `StoreTurnEntryRequest`'s own. Nothing was written.                                                                                                      |
| After a successful correction              | The write returns to the page the form was posted from (`redirect()->back()`), so a correction made here stays here; the flash banner names the turn.                                                                                                                          |

#### The race strip (D14a, 2026-10-07)

The left column is `components/career/RunRaceStrip.vue`, fed by one `raceStrip` bundle
(`CockpitController::raceStripSection()`). It answers "where am I in the run" in three bounded regions,
each a `<section>` with its own accessible name: **Recorded in this run** (this run's `race_entries`,
oldest logged turn first, unlinked rows last), **This turn** (the turn `nextTurnToPlay()` names, how many
catalogue rows fall on it, and what the run has already entered against them), and **Still to come**
(the mandatory catalogue rows strictly ahead of that turn which the run has not recorded). One race
appears in exactly one region.

Three decisions the data forced, each of which a future reader should not re-litigate:

- **This Turn counts instead of listing.** `race_catalog_slots` holds eleven rows at Senior turn 19
  (measured on `database/database.sqlite` at 410 rows, 2026-10-07: year 1 begins at turn 12, years 2 and 3
  run 1..24, the finale block carries six rows with no turn). A single "the race at this turn" would have
  to drop ten or pick one, and picking one is `SCR-CAR-013`'s job. The prop is therefore
  `{turn, year_label, offered, recorded[]}` and the option list stays on the screen that owns it.
- **No goal races.** `trainee_goals` has never been migrated (KI-34), `race_catalog_slots` has no
  trainee-bearing column, and `scenario_slots` holds `goal_race` rows for `ura_finale` alone (296 rows;
  `GROUP BY scenario_key, kind` on the dev database returns one row, measured 2026-10-07). A per-row
  `goal` flag would print a claim with no source, so the rows carry no `goal` key and the region states
  the absence once as `N/A` with its reason. `is_mandatory` is a career obligation and is labelled
  "Mandatory", never "Goal" (`79ffad5`).
- **No state column on the Ahead rows.** A recorded obligation belongs to Run and not to the list of
  things still to come, so an Ahead row is by construction unrecorded and a state column would repeat
  "Not recorded yet" on every line. `SCR-CAR-013` keeps the obligation list with states, the `is_next`
  marker and the fan-gap arithmetic.

Provenance: every figure is a stored row (the run's own entries, or the catalogue's title, tier and
mandatory flag), so no `ProvenanceBadge` glyph appears in the region. The one derived value is the count of
catalogue rows at a turn, which is a read of the set rather than an inference from it. Nothing here
predicts: no readiness band, no win figure, no percentage, no distance band derived from metres and no
condition applicability (`ADR-0016`, `ADR-0020` §3). The strip introduces no motion of its own, so reduced
motion stays the global block's, and `RunRaceStripTest` greps the component for that absence.

| State | Behavior |
| --- | --- |
| Scenario composes no `race_calendar` | One sentence says the matrix closes the calendar, and the three regions are absent rather than drawn empty. No catalogue rows are printed for such a run. |
| No race recorded | The Recorded region names the absence, why it matters, and links to the run record screen. |
| No turn being decided | This Turn and Still to come both say the run has no turn to measure against, rather than listing every obligation as future. |
| Turn carries no race | "No race on this scenario's calendar falls at turn N", with the door to Race Decision. Never a race borrowed from another turn. |
| Race entered but not tied to a turn | The row prints `Turn N/A` with the KI-17 reason on the element, not `0` and not a dash. |
| No mandatory race ahead | The region says so and points at Race Decision for the full obligation list. |
| Goal races | `N/A` with the KI-34 reason, once per region. No row carries a `goal` field. |
| Race already entered | Appears under Recorded only. `career-race-strip.spec.ts` asserts the debut is absent from Still to come once entered. |

`raceStrip` replaces the `calendar` prop bundle entirely rather than sitting beside it, so no unused
calendar payload crosses the wire and the Cockpit keeps no `?year=` query state. `RunRaceStripTest`
(10 cases) asserts the shapes, the region separation, the two absences, the closed-matrix case and that no
string reaching the page carries a percent sign; `tests/browser/career-race-strip.spec.ts` (6 cases)
asserts the rendered copy, the gone-catalogue, the keyboard path and focus ring, the 44px targets, the
320px and 640x512 (200-percent width) reflow with no sideways scroll, the reduced-motion probe and an axe
scan. The four §12.5 manual checks map onto it as: keyboard-only (case 4), reflow (case 5 at 320 and at
the 200-percent width), reduced motion (case 5's probe, the one that can fail), and axe in place of the
colour-only read the hand-rolled checks used to stand in for (`@axe-core/playwright` is installed, so
§4.1 item 8's "no axe dependency" no longer holds).

_Dated note 2026-10-07, after D14 landed: D14 shipped as its own page (`Career/Timeline.vue`,
`SCR-CAR-017`, `TimelineController`, with `components/career/CareerTimeline.vue` mounted only by that
page). It did not take the Cockpit's left column, so this strip is not temporary in the way the D14a brief
assumed. The column's ownership is still D14's to change, but the replacement it anticipated has already
happened elsewhere and the strip is what the Cockpit shows until someone rules otherwise._

#### Validation & Error Handling

Every value is a stored fact, a config value, or a named absence. The one write reuses `StoreTurnEntryRequest` unchanged — no inline `$request->validate()`, no second endpoint — and `updateTurn()` scopes the turn to the routed run (`abort_unless`). The controller reads `$run->turnEntries` once (two queries for the page) and derives the latest turn from the loaded collection, so the turn log costs no N+1.

#### Gaps

1. **The action grid links to the screens that own each action today, not to D9–D12.** The brief says "otherwise they are named absences". Seven unfocusable items would fail this slice's own keyboard-path acceptance and leave the Cockpit with no way to act, so each entry links to the surface that owns the action — the run record screen (whose guided rail records a turn and whose panels record events and scenario actions), the Legacy builder for Inheritance, and the Race Decision screen (`SCR-CAR-013`) for Race — and the entry's own 2.0 screen is named in its note. No entry is a dead link. *Dated note 2026-10-07: D9 landed, so the Training entry now links to `runs.training` (`SCR-CAR-012`).* *Dated note 2026-10-07 (D10): the Race entry now links to `runs.races.decision` (`SCR-CAR-013`).* *Dated note 2026-10-07 (D12): the Inheritance entry now links to `runs.inheritance` (`SCR-CAR-015`), so four remain on the screens that own them today.* *Dated note 2026-10-07 (D11): the Event entry now links to `runs.events.decision` (`SCR-CAR-014`), so three remain on the screens that own them today, and its note now names the Event decision screen rather than an arrival.*
2. **The left column carries the race calendar, not the milestone timeline.** `career/CareerTimeline.vue` is D14. The column renders the scenario's race calendar (a real, reused component) and names the milestone absence in one line. _Dated correction 2026-10-07 (D14a): the first sentence stands and the second is superseded. The column now renders `RunRaceStrip.vue`, a run-scoped view of the same catalogue, because the full grid at a 20-share column is a wall of races the run has no business deciding (the interim-slice brief's problem statement, confirmed against the seed data: eleven rows at Senior turn 19). D14 still owns this column's future and may absorb the strip's contract, narrow it, or replace it with the timeline. The milestone absence is still named in one line._
3. **The scenario region is a named absence.** The scenario panels are Phase E (E1). The region says so rather than inventing a panel. _Dated correction 2026-10-07 (E1): the first sentence is superseded and the second stands in its own terms. The region now mounts `components/scenario/ScenarioPanel.vue` (`SCR-CAR-019`), which draws the scenario, its documentation state and its widget and panel keys from the `scenario` section the controller emits. It invents no panel: what this build cannot draw renders a named fallback sentence, and what is off draws nothing._
4. **`risk` is always null.** No source states a delay or a reachability verdict, and both are held computation.
5. **The stat bar measures the target, not the ceiling.** `StatBand` was deliberately not reused here: its bar is the scenario ceiling with the 1,200 halved-gains marker, which is the run record screen's question. Giving it a second, target-shaped meaning would change a landed component's contract for one caller.
6. **`runs.turns.update` now returns to the page the form was posted from.** It redirected to a fixed `runs.show`, which sent a Trainer who corrected a reading on the Cockpit off the screen they were reading. `TurnRowActionsTest` seeds the run screen as the referer, so its assertion is unchanged in substance.
7. **Axe coverage is in `tests/browser/career-cockpit.spec.ts`**, not `accessibility.spec.ts`: the cockpit needs a run, and the run is built by that file's own wizard walk. It uses the shared `tests/utils/accessibility.ts` builder and the same `wcag2a`/`wcag2aa`/`wcag21aa` tag set.

#### Status

Implemented 2026-10-06 (slice D8).

#### Implementation References

`CockpitController`, `TrainerAdvisor` (C2), `Advice`, `AdvisorOption`, `ScenarioCaps::forRun()`, `TrainingRun::{buildTarget,careerYearForTurn,calendarCells,nextTurnToPlay,composesPanel,scenarioKey,hasScenario}`, `RaceCatalogSlot::YEARS`, `StoreTurnEntryRequest` (the reused write), `pages/Career/Cockpit.vue`, `layouts/CareerLayout.vue`, `components/career/*`, `components/{ResourceStrip,EnergyGauge,MoodPill,RaceCalendar}.vue`. Tests: `tests/Feature/CareerCockpitTest.php` (8 cases: the empty run, the contract with Energy present and absent, the single marker across the three ranking rules, the config-driven widget list, current-over-target with the cap, the correction's route and values, a completed run) and `tests/browser/career-cockpit.spec.ts` (7 cases: the refusal copy, three breakpoints with no horizontal scroll, the keyboard path and focus ring, reduced motion, the 44px sweep, the correction then the marker, and an axe A+AA scan).

_Dated addition 2026-10-07 (D14a): the left column's references are `CockpitController::{raceStripSection,stripRow}`,
`RaceCatalogSlot::{scopeForScenario,scopeOnTurn,isAtOrBeforeTurn,YEARS,yearLabel,turnNumber,tier}`,
`RaceEntry::{tierKey,placementOrdinal}`, `RaceEntryStatus::label()` (via `HasLabel`, with the four words in
`lang/en/uma.php`'s `race_entry_status` map), `TrainingRun::{nextTurnToPlay,composesPanel,scenarioKey,hasScenario}`
and `components/career/RunRaceStrip.vue`. `calendarCells()` and `careerYearForTab()` are no longer called from
this screen (both stay on `SCR-RUN-001`), and `RaceCalendar.vue` is no longer mounted here. Tests:
`tests/Feature/RunRaceStripTest.php` (10 cases) and `tests/browser/career-race-strip.spec.ts` (6 cases)._

---

### SCR-CAR-012 — Training Decision detail

`GET /training-runs/{run}/training` name `runs.training`, `App\Http\Controllers\Career\TrainingDecisionController`. SCREEN-010, plan §8 D9. The screen a Trainer compares the five training actions on before choosing one. It is **read-only except one write it does not own**: `Train` holds the choice and the record form posts `stage=preview` to the existing `runs.turns.store` and its `StoreTurnEntryRequest`, the same route the run record screen's guided rail uses (plan §2, convention 6). No new route for a turn, no new column, no migration, no Form Request.

Reached from the Cockpit's action grid (`SCR-CAR-011`, the `training` entry) and by URL; the career bar keeps the run record screen and the Dashboard one link away, and the page carries its own link back to the Cockpit.

#### Layout

One column of five cards in `config/scenarios.php`'s `stat_order` — Speed, Stamina, Power, Guts, Wit — in a responsive grid (one column at 320px, two from `md`, three from `xl`), then the advisor line, the record form and the deck reading. No element is hidden at a breakpoint. Rest and Recreation stay on the run record screen: they are turn choices, not training options, and the brief's five cards are the five (`SCREEN-010` §Training card).

Each card is a `design-2.0` §24 decision card with §35's progressive disclosure: three sourced facts and the advisor's reason line collapsed, the modifiers in an `aria-expanded` region that stays in the document with `hidden`, so the tab order is the same whether or not the card is open. Exactly one card may carry the `RECOMMENDED` marker, and none does when the advisor recommends `Rest` or declines.

#### Components

`components/career/TrainingCard.vue` (new), `pages/Career/TrainingDetail.vue` (new), `layouts/CareerLayout.vue` (D8), `components/ProvenanceBadge.vue` (D4). Reused, not rewritten: `SupportCard::typeWord()`, `SupportCard::displayName()`, `SupportCardEffects::atCap()`, `ScenarioCaps::forRun()`, `TrainerAdvisor::{advise,constants}()`, `TrainingRun::{buildTarget,nextTurnNumber,scenarioKey,hasScenario}`.

#### The held computation this screen renders as absence

A per-training stat yield and a failure probability have no source: `docs/research-scratch/PROCESS-PLANS.md` section `trainer-advisor.md` §1 and §5 put both out of the advisor's v1 scope and `ADR-0001` §3 records that no source publishes a curve, a table or a single probability. `docs/proposals/screen-spec-2.0.md`'s own PRODUCT DIRECTION CORRECTIONS table rules the same for this screen ("Per-training yields are **unsourced**. Render `N/A`"). Both render `N/A` with the exclusion named in the `title`, never a number, never a dash, never a default (`AGENTS.md` §5).

The guarantee is structural, not visual: `CareerTrainingDetailTest` asserts every option payload's exact key set, so a field that could carry a projected figure fails the props test before it reaches a screen. `tests/browser/career-training-detail.spec.ts` asserts the rendered copy carries no `+N Stat` and no `Failure: N%`.

What the cards do state, and where each figure comes from:

| Figure                                   | Source                                                                                                                                   | Trust label                             |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------- |
| Energy cost, `E−28 … E−17`               | `config/advisor.php` `session_cost`, a range and kept a range (`ADR-0001` §2)                                                               | `Confirmed`, with source and read date  |
| Wit cost, `E−0`, and `RiskNotMeasured`   | `config/advisor.php` `wit_cost`; `ADR-0001` §3's correction on the band                                                                    | `Confirmed`                             |
| Energy after this turn                   | the entered Energy minus each end of the cost range, returned by C2                                                                        | `Calculated`                            |
| Target deficit                           | `BuildTargetPayload` target minus the latest `TurnEntry`, returned by C2 (never recomputed in the controller)                              | `Calculated`                            |
| Supports entered for this training       | `deck_slots` joined to `support_cards.type`, matched on `SupportCard::typeWord()`, so `intelligence` lands on Wit and the export key never renders | `Confirmed`                             |
| Support effects                          | `SupportCardEffects::atCap()` — the stated anchor at the card's highest listed level, nothing interpolated (`D-256`)                       | `Confirmed`                             |
| Scenario cap bonus and the two energy keys | `config/scenarios.php` for the run's own scenario key; no scenario is a named absence, not the baseline's facts                            | `Confirmed`                             |
| Current value, target, cap               | entered `TurnEntry`, entered target, `ScenarioCaps::forRun()` — the same call the turn validator makes (`ADR-0015`, KI-47)                 | `Confirmed`                             |
| Expected gains, failure probability, bond gains | no source, and no column: `deck_slots` holds a card and a position, nothing else                                                       | `N/A`, `Unknown`                        |
| Risk words (LOW / MEDIUM / HIGH)         | no sourced rule ranks a discipline's risk; the only Energy line is the advisory 50, which describes the turn, not the option                 | `N/A`, except `RiskNotMeasured` on Wit  |

#### States

| State                                        | Behavior                                                                                                                                                                                                                                                |
| -------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (no turn logged)                       | A panel headed "No turn recorded yet" names what is missing (the five stats, Energy, Mood, Fans, Skill Points), why it matters (the advisor has nothing to read a deficit from and nothing to rank), and what to do (a link to the run record screen). The five cards still render, with `N/A` rather than zeros.       |
| No build target                              | The deficit is `N/A` with "No build target was entered for this stat" in the `title`, and the advisor's own absence sentence is printed in the Advisor region. No marker.                                                                               |
| Energy absent                                | C2 declines; `band`, `deficit` and `energy_after` are null on every card and the refusal sentence is the advisor line. Wit still prints its sourced zero cost, because its cost does not read Energy.                                                   |
| Energy present                               | The Advisor region names the turn's band in the words `SCR-CAR-011` uses ("At or above the advisory line" / "Below the advisory line"), because a Trainer who arrives here by URL is making the same decision against the same sourced 50 line (SC 3.2.4). It is a word, never a colour.                            |
| Advisor recommends `Rest`                    | No card carries the marker — `Rest` is not one of the five — and the Advisor region says so in words with a link to the run record screen.                                                                                                             |
| No deck recorded                             | "No Support Cards are recorded for this run, so no card above can count a support" with a link to the deck step. A deck with no card of this training's type counts zero, which is a real entered answer.                                                |
| No scenario declared                         | `cap_bonus` and the scenario effect lines are `N/A` with "This run declares no scenario, so no scenario effect is claimed."                                                                                                                            |
| Loading                                      | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight. The only user-initiated async action is the preview POST; no cold-load skeleton.                                                                                          |
| Error (a visit failed)                       | `role="alert"`: "That turn did not go through. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface.                                                                 |
| Error (a refused preview)                    | A `role="alert"` block with `tabindex="-1"` lists `StoreTurnEntryRequest`'s own messages, focuses itself, and each message is linked to its input by `aria-describedby`. Nothing was written.                                                           |
| After a successful preview                   | The existing `storeTurn()` redirect: `runs.show` with the input held, where the preview renders and the confirm is gated by `previewed`. This screen never writes a turn row itself.                                                                    |

#### Validation & Error Handling

Trainer input goes through `StoreTurnEntryRequest`, unchanged and not duplicated: `turn`, the five stat totals (each bounded by `ScenarioCaps::forRun()`), `sp`, `energy` 0..100, `mood` the five client tiers, `fans`, `stage`, `choice`, `outcome` and `penalty_kind` when the outcome is a failure. The five totals are required and start empty; the last logged readings travel as `placeholder` only, because an input arriving pre-filled would assert the stat did not change (D-220) — the same rule the run screen's rail states. The `max` attribute mirrors the cap as a convenience; the write is refused server-side when a number is above it. No inline `$request->validate()`, no business logic in the controller beyond assembling props: every figure comes from C2, `ScenarioCaps`, `SupportCardEffects` or the row the Trainer entered.

#### Gaps

1. **`Train` does not fire the write on its own.** The brief says "Train posts through the existing guided-turn write", and it does — but the request requires five absolute stat totals and a declared outcome, which only the client can supply. A press that posted immediately would either fabricate them or return a refusal on every press, so `Train` holds the choice, moves focus to the record form, and the form's `Preview this turn` button posts. The write, the route and the Form Request are the existing ones.
2. **The preview and the confirm live on the run record screen.** `storeTurn()` redirects a preview to `runs.show`, whose rail renders the deltas and gates the confirm behind `previewed`. Rendering a second preview here would duplicate a landed, tested screen; a Trainer who starts a turn here finishes it there. Whether `storeTurn()` should honour an `intended` target so the loop closes on one screen is an owner question. *Ruled 2026-10-07: no. `StoreTurnEntryRequest` requires five absolute totals plus an outcome, and a single-screen flow has no source for either, so "hold the choice, focus the record form" is the shape until a Trainer-entered preview column exists. Do not build a second preview renderer.*
3. **`facility_level_source` is not rendered.** The scenario matrix states how a facility level is measured, but no column stores a facility level and the config word has no client-vocabulary map, so a card cannot say which level a training is at. The `SCENARIO_EFFECTS` map in the controller names the two keys that do bear on this decision.
4. **Bond is absent at the data layer, not only on this screen.** `turn_events.bond_delta` holds a past turn's observed delta; nothing holds a bond for a deck slot, so "bond gains" has no entered state to show. Reporting one turn's delta on a card about the next turn would be the wrong object.
5. **The `N/A` reasons are `title` tooltips**, following the landed pattern (`CareerStatePanel.vue`, `SCR-CAR-011`). They are dismissable and persistent (SC 1.4.13) but a touch-only reader cannot hover them; making the exclusion body copy instead is a `DESIGN.md` §33 decision this slice did not widen to.
6. **The wizard walk is duplicated across browser specs.** `tests/browser/career-training-detail.spec.ts` carries its own copy of the five-step fixture because no browser spec in this tree shares one and `tests/utils/` holds only the axe builder. A third copy is the point to extract it.
7. **The record form re-types the rail's field list.** `GuidedStep.vue` is a scenario rail (step progress, shop and Team Race panels), not a field set, so it is not reusable here; its slot in `pages/Runs/Show.vue` is inline markup. The field names, bounds and the `no logged turn` placeholder fallback are matched to it so the two surfaces of one write cannot read differently. Extracting a shared turn-fields component is the fix, and it touches a landed page and its browser spec, so it is not this slice's change. *Ruled 2026-10-07: yes, but as its own slice after D11 — extract once and align the placeholder fallback across all three copies; do not fold a refactor into a feature diff.*
8. **`RiskNotMeasured` prints as the engine's token.** `ADR-0001` §3's prose is "Risk not measured" and displayed vocabulary normally lives in `lang/en/uma.php`; the plan's D8/D9 rows and `TrainerAdvisor::RISK_NOT_MEASURED` use the token, and the two words are the same claim. Renaming it is a copy ruling for the owner, not a silent change by this slice. *Ruled 2026-10-07: the prose form wins. One-line polish when `TrainingCard.vue` is next touched, not its own slice; the displayed phrase belongs in `lang/en/uma.php` rather than a bare enum token.*
9. **No field can carry an observed failure percentage.** `frontend-development-plan.md` §3's per-training row permits a figure the Trainer reads off the client and enters, "record-as-observed with Confirmed provenance"; this screen's payload has no such field, because the same slice's brief holds the failure number. The recording path stays the run screen's turn form, which stores what is typed and prints the delta against the stored row. *Confirmed 2026-10-07: the payload stays as-is; the §3 row is permissive ("may"), no column carries the field, and adding one is a schema change needing an ADR.*
10. **`routes/web.php` labels two peer-built screens `SCR-CAR-013`.** The Race Decision block and the Inheritance Event block both cite `SCR-CAR-013` in their route comments; `SCREEN_SPEC.md` §2 is the authority and holds `SCR-CAR-013` for Race Decision alone, with D9 at `SCR-CAR-012` and Inheritance Event unregistered in that table as of this date. The typo sits inside peer sessions' uncommitted hunks, so it is flagged here rather than edited (shared-worktree rule, owner instruction 2026-10-07: fix only if the comment can be changed without touching their hunks).

#### Status

Implemented 2026-10-07 (slice D9).

#### Implementation References

`Career\TrainingDecisionController`, `Career\CockpitController` (the `training` entry now links here), `TrainerAdvisor` (C2), `Advice`, `AdvisorOption`, `ScenarioCaps::forRun()`, `SupportCardEffects::{dictionary,atCap}`, `SupportCard::{typeWord,displayName}`, `BuildTargetPayload`, `StoreTurnEntryRequest` and `TrainingRunController::storeTurn()` (the reused write), `MoodTier`, `config/advisor.php`, `config/scenarios.php`, `pages/Career/TrainingDetail.vue`, `components/career/TrainingCard.vue`, `layouts/CareerLayout.vue`, `components/ProvenanceBadge.vue`. Tests: `tests/Feature/CareerTrainingDetailTest.php` (10 cases: the five cards in matrix order with the config cost, the exact option key set and the absent yield fields, the deficit and `RiskNotMeasured` and the energy-after range, one marker then none for `Rest` then none for a decline, the deck match by client word, the no-deck state, the scenario keys and the no-scenario absence, the preview round trip through `runs.turns.store` into the rail, the refusal naming the empty fields, and every run status) and `tests/browser/career-training-detail.spec.ts` (6 cases: rendered copy with no projected number, the `N/A` titles, the disclosure by keyboard with a stable focus order, the cockpit entry point and the record round trip, 320px reflow with reduced motion, and the 44px sweep with an axe A+AA scan).

---

### SCR-CAR-013 — Race Decision

`GET /training-runs/{run}/races` name `runs.races.decision`, `App\Http\Controllers\Career\RaceDecisionController`. SCREEN-011, plan §8 D10. Whether and where to race, at the turn being decided. It descends from the run's own URL the way the Cockpit does, and it is **read-only except one write it does not own**: the drawer's `Enter Race` posts to the existing `runs.races.store` and its `StoreRaceEntryRequest`, so no second race-write route exists.

Reached from the Cockpit's action grid, whose Race entry opens it (`SCR-CAR-011`), and by URL.

#### Layout / Structure

`CareerLayout` → the turn being decided → the mandatory-race region (Level 1) → the readiness absence → the race list, one `components/career/RaceCard.vue` per race, or the empty-state sentence. Each card carries the race's headline (title, tier, mandatory/special marker, year and turn), the catalogue's fan gate and maiden rule, whatever the run has already recorded for the slot, and the brief's ten-field fact list. A `Details and actions` button opens a drawer holding the same facts, the readiness row, and the two actions.

#### The brief's ten fields, and the four that have no column

| Field | Source |
|---|---|
| Race name | `race_catalog_slots.title` |
| Grade | `race_catalog_slots.tier`, set on all 410 seeded rows |
| Distance | `race_catalog_slots.distance`, printed by `distanceLabel()` |
| Distance band | `race_catalog_slots.distance_band` (402 of 410), printed with the 1400 m conflict note: the export and Game8 disagree at that line, so no band is derived from a metre count |
| Surface | `race_catalog_slots.surface` |
| Fan gate | `race_catalog_slots.fans_needed`, rendered on the card rather than in the fact list, because it is a quantity to work toward |
| Running style | **`N/A`.** A running style is the trainee's aptitude, not a property of the race, and no column holds a per-race one |
| Fan gain | **`N/A`.** The row carries a payout *curve id*; no payout table for those curves is stored |
| Reward | **`N/A`.** No column holds a race reward |
| Skill Points | **`N/A`.** No column holds a skill-point payout; the Trainer records what the client paid, on the run screen |
| Scenario reward | **`N/A`.** No column holds one, and the scenario panels are Phase E |
| Estimated win probability | **`N/A`.** Held on `ADR-0016` |

#### The held figure

`readiness` is `{label: 'N/A', title: …}` and the `title` names the blocker: race prediction is blocked on the requirement data `ADR-0016` measures, that decision is an open question rather than a permission, and `PRD.md` §6.11 stands unamended. The brief's risk indicator and its `< 10` / `10-30` / `> 30` thresholds are percentages and are **not built**: the rendered surface carries no percent sign at all, and `CareerRaceDecisionTest` asserts that of both Vue files and of every prop that could print one.

The screen-spec's correction row asks for Excellent/Good/Borderline/Poor readiness bands instead of a percentage. A band is a category about preparation, and a percentage is a prediction of outcome, so the two are not the same claim; whether this tool may print a descriptive band (no number, no threshold, no outcome claim) is a ruling the owner owes, not something `ADR-0016` has decided. The row renders `N/A` pending that ruling, and the plan's D10 row and the correction row disagree, which is recorded here rather than resolved silently. **What changed 2026-10-07:** this paragraph first said a band "is a prediction of the same shape and is equally held". The owner ruled that reasoning wrong, and the screen is unchanged by the correction: it prints `N/A` either way.

#### Mandatory races

Read from `race_catalog_slots.is_mandatory` (7 of 410 rows: the Junior Make Debut, the qualifier, the semifinal and the four scenario finals), in calendar order, each with what the run has recorded for it. Read from the catalogue rather than from the run's entries, because an obligation not yet reached has no entry and is exactly what the region exists to show.

**A mandatory race is a career obligation, not a Goal.** `79ffad5` withdrew `is_mandatory` as the Goal pennant's input, because the client's red banner means this character's objective and the Goal sets differ per trainee (`docs/scenarios/09-global-race-calendar.md`). The region therefore says "Mandatory", never "Goal", and the Level 1 treatment is a glyph plus the word, never colour alone.

#### States

| State | Behavior |
|---|---|
| Empty (no scenario) | The sentence names the missing scenario, why it matters (no calendar, so nothing to decide between) and what to do (choose one on the run screen). |
| Empty (no turn logged) | The sentence says no turn has been logged, so there is no turn to decide about, and points at the run screen for the first turn. |
| Empty (every turn played) | A different sentence: the career is finished, so there is no next race, and the run record screen has the history. |
| Empty (no race at this turn) | A fourth sentence, scoped to the turn, pointing at the run screen's calendar for which turns carry a race. Never merged with the others, because the four mean different things. |
| Loading | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight. The drawer's `Enter Race` carries its own "Entering…" label, because that write is user-initiated. |
| Error (a visit failed) | `role="alert"`: "That screen did not load. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface. |
| Error (a refused write) | A `role="alert"` inside the drawer printing the first message `StoreRaceEntryRequest` returned. Nothing was written. |
| After a successful write | `storeRace()` returns to the page the form was posted from, so entering a race from here stays here; the flash banner names it. |

#### Gaps

1. **`Skip for now` records nothing.** The brief says "Skip returns without recording", so it is a link back to the run screen. `ADR-0003` stores a declined race as a row with status `Skipped` and the run screen's race panel offers that status, so the fact stays recordable; what this screen does not do is write it. Reported as a deviation from `ADR-0003`'s grain, for the owner. **The affordance is deferral, not refusal, and the label says so:** "Skip for now" leaves the turn undecided, so it owes no row. Were it ever relabelled "Decline this race" it would be a refusal, and `ADR-0003` would owe a `Skipped` row for it. The run panel's `Skipped` status is that refusal path and is not contradicted by this screen, which never offers it.
2. **The drawer is a native modal `<dialog>`, not a side drawer.** `design-2.0` §32 lists race details as drawer content. `showModal()` is the one place the platform already provides a focus trap and Escape-to-close, which is what this slice is accepted on, so the mechanism is a dialog styled as a right-hand panel. What the component adds is the focus return to the button that opened it.
3. **Only the turn link is offered as an optional field.** A finish, a fan gain and a placement are outcomes rather than decisions and are recorded on the run screen's race panel, which is where the run's history is kept.
4. **`is_special_race` renders as a marker but is not a deadline.** Eight rows carry it; the region lists only `is_mandatory`, and a special race is marked on its own card.
5. **Axe coverage is in `tests/browser/career-race-decision.spec.ts`** through the shared `tests/utils/accessibility.ts` builder, which scopes every scan to `#app` (KI-63).

#### Implementation References

`RaceDecisionController`, `RaceCatalogSlot` (`is_mandatory`, `hasFanGate`, `hasMaidenGate`, `distanceLabel`, `yearLabel`, `turnNumber`, `scopeForScenario`, `scopeOnTurn`), `RaceEntry` (`placementOrdinal`, `MAX_CIRCLES`), `RaceEntryStatus`, `TrainingRun::{nextTurnToPlay,hasScenario,scenarioKey}`, `StoreRaceEntryRequest` (the reused write), `pages/Career/RaceDecision.vue`, `components/career/RaceCard.vue`, `layouts/CareerLayout.vue`. Tests: `tests/Feature/CareerRaceDecisionTest.php` (the races at the turn, the ten facts present and absent, the readiness refusal, the mandatory list with its marker and cleared state, a slot already recorded, the four empty states, and the no-percentage assertion) and `tests/browser/career-race-decision.spec.ts`.

#### Status

Implemented 2026-10-07 (slice D10).

---

### SCR-CAR-014 — Event Decision

`GET /training-runs/{run}/events` name `runs.events.decision`, and `POST /training-runs/{run}/events` name `runs.events.store`, `App\Http\Controllers\Career\EventDecisionController`. SCREEN-012, plan §8 D11. Record-only: an event choice and its observed outcome go to the existing TurnEvent mechanism (`ADR-0003`), the same write grain the run screen's panels use, with one new Form Request (`StoreTurnEventRequest`) rather than an inline validation. No new column, no migration, no event catalog table.

Reached from the Cockpit's action grid, whose Event entry opens it (`SCR-CAR-011`), and by URL.

#### Layout / Structure

`CareerLayout` → the advisor refusal → the current career state → the known outcomes → the recorded history (one `components/career/EventCard.vue` per row) → the record form, or the named absence when the run has no turns. The state section prints the five stats against their targets and the `ScenarioCaps::forRun()` ceiling plus Energy, Mood, Fans and Skill Points, in the same shape the Cockpit prints them.

#### Known outcomes are derived, not looked up

There is no event catalog in the schema, so "known outcomes" are grouped from **this run's own recorded TurnEvents** by (event type, event name), first occurrence oldest first, each choice carrying its recorded outcome text or the incomplete flag. A choice whose row carries neither an `origin_note` nor non-empty `deltas` renders the glyph-and-word warning "⚠ Event outcome incomplete — choose manually" with the reason in its `title`. Failure and Inheritance rows are excluded from both the history and the groups; those screens own them.

The source picker offers the four enum cases a choice event can carry — Character, Support Card, Group, Scenario. The brief's "Random" source has no `TurnEventType` case, so it is an **absence, not an invented enum**: the write rejects it (`StoreTurnEventRequest` inlines `Rule::in` over the four), and this is recorded here rather than resolved by widening the enum.

#### The advisor refuses

`TrainerAdvisor` ranks training actions only and holds no event advice, so `advisor` ships `{ recommendation: null, band: null, reasons: [the refusal line], alternative: null, risk: null }`. The page prints "No recommendation available" with a `title` naming the blocker and the reason line beneath. Nothing is recommended, no option is preselected, and the Trainer's choice is the default path rather than an override. The known-choice radio group appears only when the event name and source being recorded match a recorded group; it starts unchecked and fills the choice fields when one is selected with the keyboard or pointer.

#### States

| State | Behavior |
|---|---|
| Empty (no recorded choices) | The `empty` sentence: no event choices recorded yet, use the record form below, each entry becomes a known outcome next time. History renders the sentence, not a blank list. |
| Empty (no turns logged) | The record form is replaced by the named absence: no turns logged yet, record a turn first. No disabled or empty form is offered. |
| Loading | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight; the record form's button carries its own "Recording…" label. |
| Error (a visit failed) | `role="alert"`: "That screen did not load. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface. |
| Error (a refused write) | A `role="alert"` with `tabindex="-1"` inside the form lists `StoreTurnEventRequest`'s messages and focuses itself. Nothing was written. |
| After a successful write | Redirects to the same screen with the flash banner "Event choice recorded."; the per-choice fields clear, the source and event name stay put so the new choice reappears in the picker, and nothing is left selected. |
| Unrecorded state figures | `N/A` with a `title` naming the absence (no turn yet, no target recorded). Never a default, never a dash. |

#### Gaps

1. **No score, confidence, total or probability reaches this screen.** A choice's worth is a claim about outcome; the screen records what was chosen and what it gave, and `CareerEventDecisionTest` asserts the absence structurally in every prop.
2. **The new write route and Form Request are a deviation from plan §8 D11's reuse shape.** D11 asked for reuse of the existing write grain; TurnEvent is the grain, but no existing endpoint accepts a choice event outside inheritance's payload, so one route plus one Form Request was the smallest correct write. The Floor's ban on inline `$request->validate()` made a controller-level validation impossible, and D12's inline validation is noted as the contrast in the hand-off.
3. **Known outcomes are per-run, not global.** A second run that has seen the same event shows nothing until it records it. A cross-run corpus is a data-engineering question (provenance, matching) and belongs to a later slice, not to this screen's read path.
4. **Axe coverage is in `tests/browser/career-event-decision.spec.ts`** through the shared `tests/utils/accessibility.ts` builder (KI-63).

#### Implementation References

`EventDecisionController`, `StoreTurnEventRequest`, `TurnEventType` (the four choice cases), `TurnEvent`, `ScenarioCaps::forRun()`, `TrainingRun::{hasScenario,scenarioKey,buildTarget}`, `CockpitController` (the grid's Event entry), `pages/Career/EventDecision.vue`, `components/career/EventCard.vue`, `layouts/CareerLayout.vue`. Tests: `tests/Feature/CareerEventDecisionTest.php` (9 cases: the props contract, the grouping with per-choice flags, the incomplete flag, the Failure/Inheritance exclusion, the store, the unknown source rejection, the required fields, the held-computation absence, and the no-turn state) and `tests/browser/career-event-decision.spec.ts` (6 cases: the refusal with N/A titles, the keyboard record then the radio picker, the incomplete warning, the 44px sweep, an axe A+AA scan, and the no-turn absence).

#### Status

Implemented 2026-10-07 (slice D11).

---

### SCR-CAR-015 — Inheritance Event

`GET /training-runs/{run}/inheritance` name `runs.inheritance`, and `POST /training-runs/{run}/inheritance` name `runs.inheritance.store`, `App\Http\Controllers\Career\InheritanceEventController`. SCREEN-013, plan §8 D12. Record-only inheritance tracking: the predicted section shows sourced probabilities and star-roll odds, the observed section records what the Trainer entered via the existing TurnEvent mechanism. No inheritance computation is performed (ADR-0020 §3).

Reached from the Cockpit's action grid, whose Inheritance entry opens it (`SCR-CAR-015`), and by URL.

#### Layout / Structure

`CareerLayout` → `<Head title>` → `#title` slot → milestone timeline (three fixed inheritance events: Career Start at turn 1, Classic Early April at turn 31, Senior Early April at turn 55; glyphs: ● completed, ◉ current, ○ upcoming, × missed) → **Predicted** region (Estimated badge): parent and grandparent Sparks from `legacy_selection`, spark counts by kind, sourced star-roll odds table (UMAMUSUME_REFERENCE.md §1.5.3), "expected inheritance" rendered as `N/A` with `title` citing ADR-0020 §3 → **Observed** region (Confirmed badge): recorded Inheritance-type TurnEvents listed by turn with source, choice, deltas and origin note, write form to record a new observed event.

The page follows the established career screen pattern: `CareerLayout`, `<Head title>`, `#title` slot, `router.get` with `preserveState`/`preserveScroll` for async visits, and the observed form posts to `runs.inheritance.store` via `useForm` with `@submit.prevent`.

#### Data Displayed

- Milestone timeline: three fixed events derived from the run's turn count (turn 1, 31, 55 per REFERENCE §1.5.1), each with a status glyph and tooltip.
- Predicted section: parent/grandparent Sparks from `legacy_selection` payload, grouped by kind (Blue/Pink/Green/White/Scenario) with counts, the sourced star-roll odds table (stat range → ★/★★/★★★ probabilities as Estimated).
- "Expected inheritance" field: always `N/A` with `title` "Not computed. Inheritance outcome computation is banned by ADR-0020 §3."
- Observed section: list of TurnEvent records with `event_type = Inheritance`, each showing turn, source name, choice label, deltas, origin note. Provenance badge: "Confirmed — entered by the Trainer" or "No inheritance events recorded yet."
- Write form: turn select (populated from logged turns), source name select (Inspiration Event, Classic April, Senior April, Golden Event, Other), choice label (optional), deltas (optional), origin note (optional). Submits to `runs.inheritance.store`.

#### Inputs

| Field          | Type      | Req        | Validation source        |
| -------------- | --------- | ---------- | ------------------------ |
| `turn`         | select    | required   | integer, min:1, max:72   |
| `source_name`  | select    | required   | string, max:100          |
| `choice_label` | text      | optional   | string, max:200          |
| `deltas`       | hidden    | optional   | array                    |
| `origin_note`  | textarea  | optional   | string, max:1000         |

#### User Actions

| Action                          | Trigger | Preconditions                     | Result                                        | Feedback                          |
| ------------------------------- | ------- | --------------------------------- | --------------------------------------------- | --------------------------------- |
| Record inheritance event        | submit  | valid turn, source_name           | TurnEvent created with `event_type=Inheritance` | flash "Inheritance event recorded." |

#### Screen States

| State                          | Behavior                                                                                                                                                                                                          |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| No legacy selection            | Empty state sentence: "No Legacy configuration recorded for this run. Enter the parents and their Sparks on the Legacy Select screen (wizard step 4) or the Legacy Lab, then return here to track inheritance events." |
| No observed events             | Observed region shows "No inheritance events recorded yet." with Confirmed provenance badge.                                                                                                                    |
| Loading                        | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight. The write form's submit carries its own "Recording…" label.                                                                    |
| Error (a visit failed)         | `role="alert"`: "That screen did not load. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface.                            |
| Error (a refused write)        | `role="alert"` block with `tabindex="-1"` printing `StoreTurnEntryRequest`'s own messages. Nothing was written.                                                                                                 |
| After a successful write       | Redirect back to `runs.inheritance` with flash status.                                                                                                                                                          |

#### Validation & Error Handling

Server-authoritative inline validation via `request()->validate([...])` in the controller's `store()` method. The `turn` must exist for the run (1..72). The `source_name` is required. No Form Request class — the write reuses the existing TurnEvent model and the controller validates directly. Errors render per field in the form, with the `role="alert"` block focusing itself.

#### Security & Privacy

CSRF through the framework XSRF cookie header Inertia sends with every write. All user-entered content is Trainer's own statements; no third-party data displayed. No rate limits (local tool).

#### Accessibility

Form controls meet 44px target (`min-h-11` / `h-11`). The milestone timeline glyphs have `aria-hidden` and their meaning is conveyed in tooltips (`title`). Provenance badges use the single `ProvenanceBadge.vue` component with the four standard glyphs, ensuring colour is not the only channel. Keyboard path: tab through milestone glyphs (tooltips on focus), tab through Predicted region headings, tab through Observed list, tab through form fields, submit with Enter. `prefers-reduced-motion` respected.

#### Responsive Behavior

Milestone timeline wraps at narrow widths. Predicted and Observed regions stack vertically at mobile widths. The form fields stack single-column.

#### Localization / Content

Hardcoded English. Source vocabulary from `UMAMUSUME_REFERENCE.md` §1.5.3. No em dashes in shipped copy. Unrecorded values render as `N/A` with `title`.

#### Navigation & Workflow Relationships

Cockpit action grid entry "Inheritance" → `runs.inheritance`. Run detail screen "Inheritance" link → `runs.inheritance`. Preflight step 4 (Legacy) → after recording parents, Trainer can visit this screen to track events.

#### Dependencies

`TrainingRun`, `TurnEvent`, `TurnEventType::Inheritance`, `LegacySelectionPayload`, `ScenarioCaps` (for milestone turn calculations), `config/scenarios.php` (for career year labels), `ProvenanceBadge.vue`, `SparkChip.vue`, `CareerLayout.vue`.

#### Implementation References

`InheritanceEventController`, `TurnEvent` model, `LegacySelectionPayload::sparkKindLabels()`, `UMAMUSUME_REFERENCE.md` §1.5.1 and §1.5.3, `pages/Career/InheritanceEvent.vue`, `layouts/CareerLayout.vue`, `components/ProvenanceBadge.vue`, `components/legacy/SparkChip.vue`. Tests: `tests/Feature/CareerInheritanceEventTest.php` (12 cases: predicted section with/without legacy, observed section empty/with events, write form submission, milestone glyphs at various turn counts, empty state, N/A with title on expected inheritance, star-roll table rendered as Estimated, provenance badges, validation errors, 44px sweep, axe A+AA scan) and `tests/browser/career-inheritance-event.spec.ts` (rendered copy, the predicted/observed separation, the milestone glyphs, the write form keyboard path, 320px reflow, axe scan).

#### Status

Implemented 2026-10-07 (slice D12).

---

### 4.15 Non-screen surfaces (for completeness)

- **Read-only JSON API (P2, US-9, FR-E)** — `routes/api.php`: `GET /api/v1/umamusume[/{slug}]`, `/training-runs[/{id}]`, `/support-cards[/{id}]`. No visual surface; list envelope `{data, pagination{page,pageSize,totalItems,totalPages}}`; every non-2xx renders `{error:{code,message}}` centrally (`bootstrap/app.php`: VALIDATION_ERROR/422, NOT_FOUND/404, HTTP_ERROR, INTERNAL/500). Controllers `app/Http/Controllers/Api/V1/*`, Resources `app/Http/Resources/*` (camelCase). Tests: `ApiV1Test`, `ApiV1ValidationEnvelopeTest`, `ApiV1RunIndexPaginationTest`, `ApiV1SupportCardTest`.
- **`/up`** — framework health ping (`bootstrap/app.php` `health:`).
- **CLI surfaces** — `uma:fetch`, `uma:reparse`, `uma:backup`, `skill:manage`; not screens; referenced by four screens' empty-state copy verbatim.

### 4.16 SCR-VET-001 — Veteran library

**Purpose.** Browse the Trainer's filed careers and open one (PRD FR-G-2, `SCREEN-021`, plan §8 D16's read half). Record-only, like every FR-G surface: the library stores and searches, and computes nothing (`ADR-0020` §3, FR-G-4).

#### Route / Location

`GET /veterans`, name `veterans.index` (`App\Http\Controllers\VeteranController::index`). Filtered by `VeteranSearchRequest`, which is `LegacySearchRequest` plus one key: `$redirectRoute` so a refused filter returns to the library rather than to the Legacy Lab, and `order`, which the browse surface does not offer.

#### Entry Points

Global navigation "Veterans" (desktop sidebar, and inside the mobile bar's More disclosure), the Dashboard's Recent Veterans panel via each row's `veteran_url`, and by URL. The nav entry used to be the product navigation's one `to: null` named absence; D16 made it a link.

#### Layout / Structure

AppLayout shell → record-only notice → filter row (Trainee, Scenario, Tag, Filter) → count line → row list or empty state → pagination → order pair → "NOT IN THIS BUILD" absence list. One row per Veteran: trainee (with `lang="ja"` name when it differs), scenario and status at the right, the Trainer's tags as chips, notes, then Open in Legacy Lab and Compare.

#### States

| State                             | Behavior                                                                                                                                                                                                                                                                                                                                                                                                      |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Empty (library holds nothing)     | Two sentences, not an empty box: "The library holds no Veterans yet." plus the reason (`filingNotice`) and the door (`file_veteran_url`). Corrected 2026-10-07: this cell said the reason was that no screen files a career yet, which was true of the tree until D16's write half landed.                                                                                                                                                                              |
| Empty (filters matched nothing)   | A different sentence, scoped to the filters, and it names the all-of tag rule. Never merged with the first state, because the two mean different things.                                                                                                                                                                                                                                                      |
| Filtered                          | The three facets `ListVeterans` answers and no others: `trainee`, `scenario`, one `tag`. The blank option means no filter, handled by the parent request's `prepareForValidation`.                                                                                                                                                                                                                            |
| Order                             | `Newest first` / `Oldest first`, a button pair rather than a fourth select, with `aria-current="true"` on the live one. It orders by the row's own id, and the `title` on Oldest first says so: no column holds the date a career finished, so this is not a completion-date sort.                                                                                                                            |
| Loading                           | `role="status"` "Loading results…" while a filter visit is in flight (ADR-0007: user-initiated). No cold-load skeleton.                                                                                                                                                                                                                                                                                       |
| Error                             | Each refusal renders `role="alert"` under its own control (Trainee, Scenario, Tag, Order). An unknown scenario is refused, not ignored (`ADR-0018` precedent).                                                                                                                                                                                                                                                |
| Absences                          | Four labelled lines with reasons: favorite/archive/delete (no column holds any), the Spark/aptitude/skill-coverage/race-history/usefulness sorts (no sourced table prices a Spark or scores a build), the Factors view (the same unsourced set), comparison and "find parents for this build" (comparison lives in the Legacy Lab; scored parent choice is what `ADR-0020` §3 keeps out of record screens).   |

#### Persistence

Read-only, as this screen always was. The write that fills the table lives on `SCR-VET-003` (`runs.veteran`), which landed 2026-10-07 and is the first caller of `RecordVeteran`; this section previously recorded that no such route existed and that Save Veteran was gated behind an unbuilt D15.

#### Implementation References

`VeteranController`, `VeteranSearchRequest`, `ListVeterans` (the C3 query, unchanged apart from the `order` argument, whose default preserves the old behaviour for the two surfaces that do not pass it), `App\Services\Legacy\VeteranRow` (the row shape, shared with `LegacyController::index`), `PageSize`, `pages/Veterans/Index.vue`. Tests: `tests/Feature/VeteranLibraryScreenTest.php` (empty state, the four absences, the row shape asserted equal to the Legacy Lab's for the same Veteran, the three facets, both refusals, both orders, builder-link presence keyed off `legacySelection()`) and `tests/browser/veterans.spec.ts` (rendered copy, the order visit, the live nav).

#### Status

Implemented 2026-10-06 (D16 read half).

#### Gaps

1. **An empty library is still the fresh-install state.** A row appears only when a Trainer files a finished career through `SCR-VET-003`; the empty state is stated on the page with the door to that, rather than left for a Trainer to infer. It used to be permanent, because nothing could create a row.
2. **`SCREEN-021`'s three views are one view here.** The Veterans list is built; Factors is the Spark inventory, which is unsourced; Ancestry is not re-rendered here (see SCR-VET-002).
3. **Axe coverage is provided by `tests/browser/accessibility.spec.ts`.** `@axe-core/playwright` is installed and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; the screen's own Playwright spec retains the hand-rolled checks for target size, keyboard path, focus order, reflow and console errors.

### 4.17 SCR-VET-002 — Veteran detail

**Purpose.** One filed career and the two facts the Trainer recorded on it, in the shape the library prints.

#### Route / Location

`GET /veterans/{veteran}`, name `veterans.show` (`VeteranController::show`). Reads through `ShowVeteran`, which since this slice also eager-loads the two inheritance parents: the screen names each parent, and a read that left them out would ask for one query per parent.

#### Layout / Structure

AppLayout shell → record-only notice → THE RECORD (`dl` of trainee, scenario, status, turns, Energy and Fans at the last logged turn, skills learned, races entered, tags, notes) → BUILT FROM (Parent A, Parent B) → action doors (Open the career record; Legacy Lab when a read-back exists, otherwise Back to the library with the reason on the `title`) → the same four labelled absences.

#### States

| State             | Behavior                                                                                                                                                                                            |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Values absent     | Energy, Fans, tags and notes render `N/A` with a `title` naming which absence it is. A career that logged no turn shows zero turns and `N/A` figures, never `0` for Energy (D-220, AGENTS.md §5).   |
| Parents absent    | `N/A, not recorded`, per parent. A run with no Parent A is a real state, not a query gap.                                                                                                           |
| Builder door      | Present exactly when `legacySelection()` returns a payload. Null is the named absence "never opened that screen", and a door onto it would be a dead end with a label on it.                        |
| Loading / Error   | None: the screen is a read with no form on it.                                                                                                                                                      |

#### Implementation References

`VeteranController::show`, `ShowVeteran`, `VeteranRow::from`, `TrainingRun::stripValues`, `pages/Veterans/Show.vue`. Tests: the two detail cases in `tests/Feature/VeteranLibraryScreenTest.php` (figures read back from a logged turn; unrecorded figures render absent).

#### Status

Implemented 2026-10-06 (D16 read half).

#### Gaps

1. **The six-node graph is not rendered here.** `components/legacy/AncestryNode.vue` is an editable node (pick control, options, field errors), so a read-only copy would be a second mapping of the same shape beside `Legacy/Builder.vue`'s. The detail screen names the two parents and links to the one surface that renders the graph. Marked in the controller as a deliberate simplification with its promotion path.
2. **No final stat block.** The five stats live on the run's turns and the run page composes them; this screen links to the run rather than restating a composition it does not own.

### SCR-CAR-016 — Skills Planner

`GET /training-runs/{run}/skills` name `runs.skills.planner`, `App\Http\Controllers\Career\SkillsPlannerController`. Plan §8 D13; the plan ids it `SCR-013` because it is design-2.0-only, so nothing in `screen-spec-2.0.md` describes it. Whether and what to learn, against the build target. Reached from the run record screen's header (`runs.skills_planner_url`), and by URL. The Cockpit's action grid does not grow an entry for it: that grid is the turn's decisions, and skills are build-level.

Read-only except one write it does not own: the reorder posts to the existing `runs.build-target.update` and its `StoreBuildTargetRequest`, because `skill_priorities` lives in `build_target` and that write owns the field. `runs.skills.sync` validates acquisition statuses only and the `run_skills` pivot carries no order column, so the plan's wording that the reorder saves through it cannot hold; the deviation is recorded in the D13 hand-off.

#### The four states, and their precedence

Highest first; a skill carries exactly one state.

| State | Glyph | Read from |
|---|---|---|
| Learned | ● | `run_skills.pivot.status = Acquired`. The outcome beats the plan: a priority the Trainer then learned shows here and costs nothing in the coverage sum. |
| Required | ! | `build_target.skill_priorities`, in the Trainer's own order, minus the learned. A `Suggested` or `Skipped` pivot row beside it renders as a recorded fact, not a second state. A name the catalogue has no row for is kept as entered, every figure it cannot state rendered as the absence it is. |
| Available | + | `run_skills.pivot.status = Suggested`, not named in the priorities. |
| Inherited | ◇ | Nothing: no column holds the skills a Legacy configuration passes down (`legacy_selection` carries parents and Sparks, never skills), so the group is the named absence. |

A `Skipped` row no priority names does not appear; the run screen's skill list owns the full acquisition record, and the planner says so. The glyphs are not the provenance set (ProvenanceBadge owns ✓, ∑, ~ and ?), never colour-only, and `aria-hidden` beside a visible word.

#### Skill Point coverage

The sum of the base prices of the skills still to learn, against the latest `turn_entries.sp`, badge `Calculated`. Both figures can be unstated: no turn logged means no recorded total, and a priority skill whose catalogue row carries no price means the sum would be a guess — a missing price is refused, never read as zero. The warning (glyph plus text) fires when the sum exceeds the recorded total, and the strip states that the total prices every skill at its base because no column holds hint levels (G-SK-3).

#### The hint ladder

`Hint Lvl 1` 10% off through `Hint Lvl Max` 40%, the client's own captions, read off the `[Global]` Learn screen on 2026-10-03 (`UMAMUSUME_REFERENCE.md` §1.1.4, `SKILLS-MECHANICS.md` §2.4, run report §2.1), badge `Confirmed` from `ProvenanceBadge`. Each skill's costs are `base × (1 − discount)` floored to the integer, in config (`uma.skills.hint_discount`), never inline. The percentages here are a sourced display, not a prediction; no win figure exists on this screen.

#### Race-fit assessment

Seven cells per skill, in the order distance, surface, style, course, weather, ground, phase, each exactly "Matches", "Does not match" or "Not recorded", against the build target, with the raw conditions printed as the source states them. A cell evaluates only where a source publishes the number map: `distance_type` 1-4 and `ground_type` 1-2, both decoded in `UMAMUSUME_REFERENCE.md` §1.2, so every evaluated cell cites that section in its `title`. Style is the cell where a recommendation would sneak in (PRD OQ-5): the condition records a number and no source maps the numbers to the client's labels, so the cell refuses to compare. The other dimensions are unconstrained because the build target records none of them. With no target set, nothing compares against anything.

#### States

| State | Behavior |
|---|---|
| Empty (no build target) | The target section and the Required group each name the absence, why the list is empty, and that this screen reorders priorities rather than adding them. The Available and Learned groups render normally. |
| Empty (a group with no rows) | The group's own absence sentence: nothing marked, nothing learned, or the Inherited named absence. |
| Empty (no turn logged) | The coverage strip renders `N/A` with the reason in the `title`, and no warning can fire. |
| Loading | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight. |
| Error (a visit failed) | `role="alert"`: "That screen did not load. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface. |
| Error (a refused save) | A `role="alert"` beside the save button printing the first message `StoreBuildTargetRequest` returned. Nothing was written. |
| After a successful save | `updateBuildTarget()` returns to the page the form was posted from, so saving from here stays here; the flash banner names it. |

#### Gaps

1. **No screen edits a saved run's build target.** Priorities are enterable at the wizard's target step; the run-scoped write has no editor yet, so this planner can reorder but not add. The no-target state says so in as many words.
2. **A priority name that matches no catalogue row is inert.** It renders with its reason and rides along in every save; renaming it needs an editor this screen does not have (gap 1).
3. **Inherited has no source.** Listed as a state because the brief's state model names it; the group is the named absence until a schema decision (G-SK-3's owner question) gives the run somewhere to read them from.
4. **Style fit is unclaimed, on purpose.** OQ-5's precondition (resolve the style number-to-label map from client strings first) is unmet; when it is met, the cell's state and `title` move together.
5. **Axe coverage** is in `tests/browser/career-skills-planner.spec.ts` through the shared `tests/utils/accessibility.ts` builder, which scopes every scan to `#app` (KI-63).

#### Implementation References

`SkillsPlannerController`, `config('uma.skills')` (the ladder, the two fit maps, the prerequisite pair), `BuildTargetPayload` (`skill_priorities`, the vocabulary constants), `Skill::scopeAvailableOnGlobal`, `TurnEntry` (`sp`), `StoreBuildTargetRequest` (the reused write), `pages/Career/SkillsPlanner.vue`, `components/career/SkillPlanRow.vue`, `ProvenanceBadge`. Tests: `tests/Feature/CareerSkillsPlannerTest.php` (11 cases) and `tests/browser/career-skills-planner.spec.ts`.

#### Status

Implemented 2026-10-07 (slice D13).

### SCR-CAR-017 — Career Timeline

`GET /training-runs/{run}/timeline` name `runs.timeline`, `App\Http\Controllers\Career\TimelineController`. SCREEN-018, plan §8 D14. A read screen that flattens the run's `turn_entries`, race log entries tied to a turn, and event log entries into one ordered rail the page renders oldest-first. Reached from the Cockpit's left column and by URL. **No new write route**; every value on the page comes from data the existing routes already own.

#### Layout / Structure

`<CareerLayout>` → heading + reproduction-rule paragraph → type-filter chips (`Turn`, `Race`, `Event`, `Inheritance`, `Failure` — only the kinds the data layer returned; the chip list is the controller's own composition) → the rail (`<ol>` of `<details>` rows) → a single back link to the Cockpit. No element is hidden at a breakpoint.

The rail's row order: every `TurnEntry` first, then the `RaceEntry` rows whose `turn_entry_id` points at that turn, then the `TurnEvent` rows whose `turn` is the same number. Within a turn, the events are in storage order. A run with no logged turns prints the empty state and no rail.

#### The five audit fields and what each reads

| Audit field | Source | What it shows | When it is `N/A` |
|---|---|---|---|
| BEFORE | previous logged turn's stored row | the five stats, sp, energy, fans the Trainee had at the end of the prior turn | turn 1 has no prior turn; non-turn rows drop the field |
| ACTION | one of: turn description (`Turn recorded`), race (`Race: <title> (<tier>)`), event (`<source>: <choice>`) | the action the Trainer's row captures | never |
| EXPECTED | the row's stored values for a turn row; null for non-turn rows | ADR-0003 stores absolute end-of-turn, so EXPECTED === ACTUAL | non-turn rows drop the field |
| ACTUAL | the row's stored values for a turn row; `result` for non-turn rows | the same values, the recorded `outcome_note` for events, or `'Outcome incomplete — choose manually'` when an event row carries neither | non-turn rows show the `result` text, not the stat block |
| RESULT | `'Recorded as entered'` on a turn row that has not been edited, `"<gained> instead of <expected>"` on a Failure row with a non-null `origin_note`, `'Outcome incomplete — choose manually'` on an event row with no recorded outcome, the race status plus placement on a race row | the stored fact, with prose that names what's unstored | never; the cell always renders |

The four stat values in the BEFORE / EXPECTED / ACTUAL blocks render as `N/A` with a `title` when `turn_entries.energy` or `turn_entries.fans` is null, which is the same rule ADR-0003 gives for those columns.

#### Glyphs and states

| Glyph | Meaning | Where it appears |
|---|---|---|
| `●` | completed (a stored row at this turn) | turn, race, event kinds |
| `◉` | current (the turn being decided) | reserved for the next-turn indicator on the Cockpit; not produced by this page today |
| `○` | upcoming (an unrecorded future obligation) | reserved for scenario deadlines (Phase E); not produced today |
| `×` | missed or failed | `TurnEventType::Failure` rows |

The four glyphs are the only places the rail carries `font-mono` text in `text-base`; the kind chip beside them is the textual state, so the rail never depends on colour or shape alone.

#### Type filters

A button group with one button per kind the controller found. Pressing a chip toggles its `aria-pressed` and adds or removes the kind from the client's active-filter list; the rail hides rows whose `kind_label` is not in the list. With no chips pressed, every row is visible. The handler is local-only: no extra request, no payload shrinks.

#### Corrections

`runs.turns.update` mutates `updated_at` but the model has no edit log column, so a corrected row carries `corrected: true` plus a deterministic `correction_id` (`turn-N`) the page renders as an `Updated` chip with the `title` "Correction turn-N: row was updated." The link on the row points at `runs.show?edit_turn={id}`, which is the run screen's open-edit affordance (see SCR-RUN-003). **No separate corrections log.** A future slice may add one; the controller updates `corrected` to read the new log rather than the timestamp comparison. The flag is the honest limit; the page does not silently rewrite a past decision.

#### Each row's `decision_url`

A row that points at one of the existing decision screens links to it: race rows → `runs.races.decision`, event rows → `runs.events.decision`, inheritance rows → `runs.inheritance`, failure rows → `runs.show` (the failure has no write path of its own; the run screen is where it was filed). Turn rows link to `runs.show?edit_turn={id}` for the on-screen edit affordance.

#### Validation & Error Handling

There is no write route, so there is no Form Request. The two collections the controller maps over are `turnEntries` and `turnEvents` plus `raceEntries` (with its slots eager-loaded), so the page costs three queries against the run. The per-row BEFORE reads the previous row's stored values in the same map, so the audit never re-queries the run.

#### Empty / Loading / Error

| State | Behavior |
|---|---|
| No turns logged | One paragraph names the absence and points at the run record screen. The rail is not rendered. |
| Loading | `role="status"` "Loading…" while an Inertia visit is in flight (ADR-0007 user-initiated). |
| Error  | `role="alert"` single surface, the page's own not the framework's modal. |

#### Gaps

1. **A separate corrections log is not built.** Today's indicator is the timestamp comparison (`updated_at > created_at`); the model has no prior-values column, so the page cannot show what the row used to read. Filed as the limit, with the upgrade path named in the section.
2. **`◉` and `○` glyphs are reserved, not produced.** "Current" is the Cockpit's job (it points at `nextTurnToPlay()`); "upcoming" is the scenario deadlines (Phase E). This screen renders neither because neither has a row today.
3. **A confirmed "missed terminal" state from `ADR-0003 R1` amendment 1 is not produced.** A `GradeDeadline` row in `scenario_slots` would carry it; the data layer is empty (ADR-0003 R3), and Phase E is what fills it.
4. **Axe scope is `#app`.** This screen reads its keyboard path, the disclosure's `aria-expanded`, the filter's `aria-pressed`, the 320px reflow and the 44px sweep through the page's own browser spec, and the `axe` scan runs against `#app` (CareerLayout's mount) the same way every other career spec scopes it, per `tests/utils/accessibility.ts`.

#### Status

Implemented 2026-10-07 (slice D14).

#### Implementation References

`App\Http\Controllers\Career\TimelineController` (one controller method, no model changes, no migration), `routes/web.php` (one `GET` route registered after `runs.events.store`), `TrainingRun::nextTurnToPlay` (read by the Cockpit, not by this page), `RaceEntry::raceCatalogSlot` / `scenarioSlot` / `turnEntry`, `TurnEntry::mood`, `TurnEventType`, `ADR-0003` §"Store the end-of-turn total, not the delta". Tests: `tests/Feature/CareerTimelineTest.php` (4 cases — the empty state, the BEFORE/AFTER mapping, the `corrected` flag, and the rows-under-turns interleaving) and `tests/browser/career-timeline.spec.ts` (5 cases — empty state, glyph-plus-text + keyboard disclosure, filter chip toggle, 44px sweep + 320px reflow, axe A+AA).

_Dated addition 2026-10-07 (D15): the "Reached from the Cockpit's left column" claim above was **false when D14 landed** — no file under `resources/js` or `app/` linked to `runs.timeline`, so the route was reachable by URL only. D15 made it true by replacing the left column's stale "arrive with the Career Timeline screen" sentence with the link, and the props test now pins `run.timeline_url` on `CockpitController::runSection()`. The Career Result screen is a second door (`runs.result`'s "View Career Timeline")._

### SCR-CAR-018 — Career Result

`GET /training-runs/{run}/result` name `runs.result`, `App\Http\Controllers\Career\ResultController`. SCREEN-019, plan §8 D15. What a finished career adds up to. Reached from the run record screen's header (`result_url`) and by URL. **Read-only: the screen records nothing and has no write route of its own.** It links to Save Veteran (`SCR-VET-003`), which is where the one write a finished career gets is entered.

_Dated addition 2026-10-08 (F1): the sentence above was the only door when it was written, and the record screen is the 0.1.0 page being retired, so a 2.0 surface had to take one. The Cockpit's left column now carries `run.result_url` beside the Career Timeline door (`CockpitController::runSection()`), pinned by `CareerCockpitTest` and by the browser case "the Cockpit carries the door to the Career Result" in `career-surface-cutover.spec.ts`. The door does not gate on run status, for the reason the record screen's door already gave: this screen answers an unfinished career itself (the `Active` and `Retired` rows below), so a Trainer who opens it too early is told what is missing rather than finding no door._

#### Layout / Structure

`<CareerLayout>` → heading + one-paragraph note that the tool computes no grade → **Final build** (five stat tiles with target and deficit, the entered target, skills learned, the trainee's aptitudes) → **Race history** (five counts and a table of completed races) → **Scenario result** (the Grade Point ladder where the scenario composes one, the reward absence) → **What you can do next** (the two doors and the Save Veteran link, or its refusal on a career that has not finished) → the ruleset line. No element is hidden at a breakpoint.

#### The three states

| State | Behavior |
|---|---|
| `Completed` | The three sections render. This is the only state that is "finished". |
| `Active` | One empty state: the career is still running, so there is no result to read. The Cockpit door is offered, because an active career still has a next turn to decide. |
| `Retired` | A **different** empty state: the career was abandoned before completion. **No Cockpit door is rendered anywhere on the page**, including the footer back-link, which points at the run record instead; the Timeline and run record are offered. Reusing the Active copy or door is the defect this separation exists to prevent, and `career-result.spec.ts` asserts `a[href$="/cockpit"]` has count 0. |

#### The four race counts, and why not two

`race_entries.status` and `race_entries.placement` are independent facts, so the counts are `completed`, `wins` (`placement` 1), `below_first` (`placement` above 1), `placement_unrecorded` (completed with no finish entered) and `g1_wins` (`placement` 1 with `tierKey()` `G1`, a stored label, never inferred from a grade code). A two-count shape would fold an unrecorded finish into "losses" and assert a placing nobody entered. `Skipped` and `NotOffered` rows are not races the career ran and no count reads them. The counts' key set is pinned by the props test so no win-rate percentage can arrive without a test naming it.

#### The deficit, and the null target

Per-stat deficit is the **only** arithmetic on the screen, and it is `TrainerAdvisor::deficits()`'s own number rather than a second copy (`ADR-0015`): the advisor ranks against it, and the result reports it, so the two cannot disagree. A run with no build target reports `deficit: null` with a `title` — never `current − 0`, which would make every stat look short by its own value. The props test asserts both `target` and `deficit` are null in that case.

#### Omitted as a ruling: "Build quality"

`screen-spec-2.0`'s SCREEN-019 correction row asks for "build quality" — a target-completion figure, a skill-coverage figure and an inheritance-quality figure. **All three are omitted by ruling, not by oversight.** A completion percentage is an aggregate over the same targets the per-stat deficits already state one by one, and the slice's own doubt pass rejected it; skill coverage and inheritance quality are `ADR-0020` §3's held computation and have no column to read even if permitted. No quality, completion, legacy-value or best-use string reaches the page, and the browser spec greps the main region for two of those words.

#### Save Veteran: the door, not the absence

_This section read "a named absence" until 2026-10-07, and the paragraph below is kept as the record of why it was one._ The brief reads "Primary action 'Save Veteran' to D16". When D15 landed, D16 was the Veteran library slice and the plan's §4 table had this slice depending on it, so no route existed and a link would have been a dead link (`SCR-CAR-011`'s action grid refused the same for the same reason). D16's write half landed on 2026-10-07 as `SCR-VET-003`, so the section now renders a link on a Completed career and one sentence on any other, and the props test pins the key set to `[available, reason, url]` — a score or a recommendation still cannot arrive without a test naming it.

#### Empty / Loading / Error

| State | Behavior |
|---|---|
| Empty | The two states in the table above, each with what is missing, why, and the door that applies. |
| Loading | `role="status"` "Loading…" while an Inertia visit is in flight (ADR-0007, user-initiated). |
| Error | `role="alert"` single surface, this page's own rather than the framework modal. |

#### Gaps

1. **The "already saved" branch is reachable only after a save.** `SCR-VET-003` files the row and prefills from it, so the branch's copy is true of a second visit; nothing else in the build creates a Veteran, and a fresh install still shows the unsaved sentence. (This gap used to read "Nothing can create a Veteran", which was the state of the tree before 2026-10-07.)
2. **`aptitudes` is the trainee's, not the career's.** No column records an aptitude changing during a run, so the section prints what the trainee came with and says so rather than implying the career earned it.
3. **Scenario rewards stay `N/A`.** No column on any table this tool reads holds a race reward, a Skill Point payout or a fan gain, so the section names the absence once (the same ruling `SCR-CAR-013` made).
4. **The fourth private `currentStat()` copy.** `CockpitController`, `TrainingDecisionController`, `EventDecisionController` and now `ResultController` each hold the same matrix-name lookup. D9's ponytail comment named the upgrade path — one accessor on `TurnEntry` — and it is now overdue; the refactor edits three landed controllers and their tests, so it is its own slice.
5. **Axe scope is `#app`**, per `tests/utils/accessibility.ts`, the same scope every career spec uses.

#### Status

Implemented 2026-10-07 (slice D15).

#### Implementation References

`App\Http\Controllers\Career\ResultController` (one action, no model change, no migration), `routes/web.php` (one `GET` route after `runs.timeline`), `TrainerAdvisor::deficits()` (widened from private to public for this second reader, `ADR-0015`), `TrainingRun::{buildTarget, composesGradeObjectives, gradePeriods, gradeEarned, gradeUnpricedCount, gradeUnassignedCount, veteran}`, `BuildTargetPayload`, `RunStatus`, `SkillAcquisition`, `AptitudeBadge.vue`, `ProvenanceBadge.vue`, `TrainingRunController`'s `result_url`. Tests: `tests/Feature/CareerResultTest.php` (8 cases — the completed sections, the two empty states, the no-target deficit, the advisory-matching deficit, the four race counts, the Save Veteran key set, the ruleset absence and the learned skills) and `tests/browser/career-result.spec.ts` (5 cases — the Active state, the Retired state with no Cockpit door, the completed sections with the named absences, the 44px sweep and 320px reflow, axe A+AA).

### SCR-CAR-019 — Scenario panel shell

`components/scenario/ScenarioPanel.vue` mounted inside the Cockpit's scenario region (`SCR-CAR-011`), over the `scenario` section `CockpitController` emits. SCREEN-014 / 015 / 016, plan §9 E1. **No route of its own and no write of its own**: the shell is a region of the Cockpit, and E2 to E4 plug renderers into it rather than adding screens.

#### The contract E2 to E4 consume

`{ label, declared, documented, version, version_title, widgets, widget_labels, widget_values, widgets_absence, panels, objectives, actions, alerts, recommendations, recommendations_absence, finale, finale_absence }` — the brief's §49 shape with the run's own facts folded in. `version` is `null` with a `title` because `app.ruleset` is null and no source defines a Global ruleset version.

**There is no scenario identity field.** No `key`, `id`, `scenario_key` or `config_key` crosses the wire; `label` is display text and nothing compares it. `ScenarioPanelTest` pins the section's key list, so an identity field cannot arrive without a test naming it, and the branch-free property of the shell itself is verified by reading it rather than by a grep.

**The registry** (`components/scenario/registry.ts`) is a `Map` with `register(key, component)` / `resolve(key)`. The shell owns the fallback chain: for a widget key `resolve(key) ?? ResourceMeter ?? WidgetFallback`, where the meter's turn is skipped unless the matrix labels the key; for an ON panel flag `resolve(flag) ?? WidgetFallback`. E1 registers nothing; E3 and E4 register their own renderers, and a fifth scenario is one `config/scenarios.php` entry and zero component edits (gate G-33, `D-240`).

#### The four states

| State | Behavior |
|---|---|
| Empty — no panel is on | The sentence differs by why: a run that names no scenario is told none is set and that the baseline caps apply; a scenario that composes no panel of its own (Our Grand Concert) is told that, and the baseline strip still renders. A fifth scenario that turns one flag on leaves this state without a code change. |
| Loading — Inertia visit in flight | `role="status"` "Loading…" on the page (`ADR-0007`: user-initiated). The shell adds no loading state of its own: it is server-rendered with the page and never fetches. |
| Error — a visit failed | `role="alert"` on the page, one surface. The shell has no request to fail, so it carries no error state of its own and one is not invented. |
| Panel fallback | **A real state, not an error.** A widget or flag the matrix declares but this build cannot draw renders a labelled notice naming the key or the panel. `WidgetFallback` is the shell's last resort, so an unrecognised key is a sentence rather than a blank or a thrown render. |

#### AlertRow's tones, and the dead one E2 removed

`AlertRow` takes `tone: 'note' | 'critical'`. **E1 shipped a `warning` tone mapped to `text-warning`,
and `--color-warning` has never existed in `resources/css/app.css`** — Tailwind emitted no rule for it,
so the branch silently inherited its parent colour. Both callers (the fallback notice and the shell's
empty state) pass `note`, so nothing rendered wrong, which is exactly why an axe run cannot find it: the
class was decoration that did nothing. E2 deleted the dead branch rather than leaving it beside a new
one, and added `critical` on `text-ink-strong` with weight, because `design-2.0` §4's Level 1 asks for
emphasis and a word, not a colour, and `--color-risk` is reserved for an error state while `--color-goal`
is the client's Goal pennant (`SCR-CAR-020` states the alert's rule in full).

#### Resource meters, and the bar

`ResourceMeter` renders a value and a bar for the same reading. No value prints `N/A` with a reason rather than `0` (D-220, `AGENTS.md` §5), which is every meter's state today because no column holds a scenario resource. No sourced maximum means no fill: a proportional width needs a denominator, and inventing one would draw a share of nothing. The bar is labelled (`role="img"` with an accessible name stating the reading), and the number beside it is the primary readout, so no state depends on the bar.

#### The PARTIALLY DOCUMENTED badge, and why it does not render today

`ScenarioStatusBadge` renders the partial state on an explicit `documented => false` and the documented state otherwise, because a scenario that says nothing about its documentation has not admitted a gap. **All four Global scenarios are documented or silent, so this build only ever draws the documented state.** The brief's correction table marks Our Grand Concert "PARTIALLY DOCUMENTED", and `documented` flipped to `true` on 2026-10-05 when the mechanics read landed, so the key records provenance rather than the badge's meaning. A dedicated `partially_documented` switch is drafted in a sibling slice's working tree and is not committed; E1 will not read a key that exists only in a working tree, and the hand-off sits at plan §4.1 item 6. Which key drives the badge is the **owner's ruling alone**, not this slice's and not the sibling slice's; when it lands, E1's one controller line changes in a follow-up commit rather than inside this slice.

*Dated correction 2026-10-07 (slice E6, the follow-up the paragraph above anticipated; the original sentence is kept because it is the record of what E1 shipped).* The owner ruled on 2026-10-07 and **`partially_documented` now drives the badge**: `CockpitController::scenarioSection()` reads it, `documented` stays a provenance marker that no screen reads, and Our Grand Concert renders "Partially documented" while the other three render "Documented". The partial arm is therefore reachable, and the strip it belongs to is `SCR-CAR-024`. The ruling and the hand-off are recorded at plan §4.1 item 6; the sibling's key and its reader are still in the working tree rather than in `HEAD`, which `SCR-CAR-024`'s gaps state as a dependency.

#### Gaps

1. **E1 ships no renderer.** Every widget resolves to the meter and every ON flag to the fallback, which is why Unity Cup and Trackblazer show fallback lines today. E3 (team rank, spirit bursts, team races) and E4 (grade points, shop, epithets) register theirs; E2's URA panel and E6's Grand Concert strip follow.
2. **`WidgetFallback` is unreachable by config today.** Every widget key the four scenarios declare is labelled in `widget_labels`, so the fallback's widget arm needs a config entry that names an unlabelled key — which `ScenarioPanelTest` adds through `config()->set` to prove the payload carries it.
3. **`objectives`, `actions`, `alerts` and `finale` are carried empty.** They are the §49 sections each panel fills for itself; E1 carries them rather than inventing a row, and `recommendations` carries a named absence because no source states scenario advice.
4. **design-2.0 §30 prefers skeletons; this repo forbids skeleton classes.** `DesignTokensTest` and the plan's §10 checklist both require no skeleton palette class, so the repo wins and the shell has no skeleton state. Recorded here rather than silently diverging from the brief.

#### Status

Landed 2026-10-07 (slice E1), with its browser gate green. `tests/browser/scenario-panel.spec.ts` is **8 passed (3.5m)** against a scratch-database server on port 8161, and `npm run build` exits 0; the earlier blocker (a concurrent slice's uncommitted `resources/js/pages/Preferences/Edit.vue`) cleared before the run. The props test, typecheck, Pint, PHPStan and the lore gate are green on E1's own files; see plan §4's E1 row for the full inventory. **The gate run found one real defect and it was D14's, not E1's:** the `Career Timeline` door on the Cockpit was an inline link measuring 32px, so E1's page-wide sweep failed it; it now uses the standalone `inline-flex min-h-11` treatment its sibling link already had, and the re-probe reports no control under 44px. The gate closed inside the session, so the recorded criterion owes no `KI-nn` entry (owed past 24 hours would earn one). E2 registers its renderer into this shell next.

#### Implementation References

`CockpitController::scenarioSection()` (assembled from `config/scenarios.php`'s `widget_labels`, `panel_labels` and the scenario entry, plus `TrainingRun::scenarioWidgets()`), `resources/js/components/scenario/{ScenarioPanel,ResourceMeter,ScenarioStatusBadge,AlertRow,WidgetFallback}.vue`, `components/scenario/registry.ts`, `types.ts`'s `ScenarioPanelSection`, `Cockpit.vue`'s `career-scenario-heading` region. Tests: `tests/Feature/ScenarioPanelTest.php` (5 cases — the §49 shape with the pinned key list, the same payload shape across all four scenarios, the `documented` default, the fifth scenario from config alone, and the unlabelled widget key) and `tests/browser/scenario-panel.spec.ts` (8 cases — the registry's `register`/`resolve` seam, the meter's reading and labelled bar, the ON-flag fallback naming its panel, the OFF flags drawing nothing, the baseline strip's scenario name and documentation and ruleset absence, the scenario that composes no panel, the 44px sweep with 320px reflow, and axe A+AA).

### SCR-CAR-020 — URA panel (SCREEN-014)

`components/scenario/UraPanel.vue`, the first renderer E1's registry resolves. It is reached by the
`career_goals` flag in `config/scenarios.php`, so it draws for any scenario whose matrix turns that
flag on and for none that leaves it off, and it never reads `scenario.label` (gate G-33, `D-240`).
Plan §9 E2. No route and no write of its own.

**Two of the three modules are absences, and that is the screen's honest state.** Measured on the dev
database copy on 2026-10-07, not read off the brief: no table holds a trainee's per-character goals
(`TrainingRun.php:599-616` stopped drawing the Goal pennant for exactly this reason, and the banner
"returns when `trainee_goals` exists"), nothing stores a Happy Meek level, and no source publishes the
reward table or the finale's contribution. The owner ruled all three absent on 2026-10-07. What the
repository does hold is the mandatory race set, so that is the one module with figures in it.

| Module | Source | Renders |
|---|---|---|
| Career goals | no table | `N/A` plus the three-part sentence: what is missing, what it costs, and that a `trainee_goals` schema proposal with a PRD citation unblocks it (`design-2.0` §29) |
| Mandatory races | `race_catalog_slots` where `is_mandatory`, scoped by `forScenario()` | one row each: state glyph **and word**, the career year, the recorded turn, and a door to `runs.races.decision` unless it is already recorded |
| URA progression and finale preparation | the same recorded rows | carried by the mandatory-race rows rather than by a second strip: the three Finals rounds *are* those rows, and drawing them twice would be the same data wearing two shapes |
| Happy Meek | nothing stored, nothing sourced | four rows, each `N/A` with its own reason. No level means nothing to badge as Confirmed; the last two fields are unsourced, not merely unrecorded, and say so differently |
| Duel availability | no field exists | `N/A` with the reason; the brief allows the field only "if a stored or configured field exists", and none does |

#### States

| State | Behavior |
|---|---|
| Empty — no mandatory row | The group says the calendar records none, rather than printing a heading over a blank. |
| Empty — a scenario that leaves the flag off | Nothing at all. The shell never mounts the renderer, and `ura-panel.spec.ts` asserts a Unity Cup cockpit draws no URA heading. |
| Loading | The page's Inertia visit state (`ADR-0007`); the panel adds none, being server-rendered with the Cockpit. |
| Error | The page's one `role="alert"` surface; the panel makes no request to fail. |
| Unrecorded reading | `N/A` with the reason in a `title` **and** printed as visible text, because a `title` alone is not reliably announced. |
| Alert — a mandatory race due and unrecorded | Level 1 Critical `AlertRow`: `▲` plus the words `Critical: <race> is due and not recorded`, with the turn detail in a `title`. |

#### The alert, and why it has no window

`design-2.0` §4's **Level 1 — Critical** names "mandatory race" as its first example and prescribes
emphasis, a clear icon and prominent placement, never a colour. So `AlertRow`'s `critical` tone is
`text-ink-strong` with weight, and the level travels as the word. `--color-risk` stays an error state
(validation failures) and `--color-goal` stays the client's Goal pennant, which this repository refuses
to emit until a per-character goal table exists: a mandatory race whose turn has passed is neither a
fault nor a pennant.

The rule is the derivable one the owner ruled on 2026-10-07: the turn the calendar records has arrived
or passed, and the run records nothing for it. **No window length exists in any source, so none is
invented**, and the copy says "due and not recorded" rather than "inside the deadline window". The
finale block carries no turn, so it can be current and can never be missed, and it raises no alert.

The position is never re-derived in the panel or the controller: `RaceCatalogSlot::isAtOrBeforeTurn()`
is the same owner the race strip already calls (`ADR-0015`), so the two regions cannot disagree about
whether a turn has arrived.

#### Gaps

1. **The `Recorded` state has no browser case.** Reaching it needs a race written through the race
   screen, which is `SCR-CAR-013`'s surface; `UraPanelTest` proves the arm, and the spec covers the
   three states a turn write can reach (upcoming, due now, missed).
2. **No write path exists for a Happy Meek level.** The brief forbids a migration, so the module is
   read-only and every row says the reason. An entry path is a schema proposal, not a UI decision.
3. **`race_calendar` is still ON for URA and has no renderer**, so the shell prints its fallback
   sentence beside this panel. The Cockpit's own left column already shows the run's races
   (`RunRaceStrip.vue`), so whether that flag should stay on for URA is an owner question, not a
   defect E2 can settle.

#### Status

Built 2026-10-07 (slice E2). See plan §4's E2 row for the gate inventory.

#### Implementation References

`CockpitController::careerGoalSections()` and `happyMeekRows()`, `config/scenarios.php`'s
`panel_labels.career_goals` and `ura_finale.panels.career_goals`, `RaceCatalogSlot::isAtOrBeforeTurn()`
/ `forScenario()` / `yearLabel()` / `turnNumber()` (all reused, none re-derived), `TrainingRun::nextTurnToPlay()`,
`components/scenario/{UraPanel,AlertRow}.vue`, `registry.ts` reached by `ScenarioPanel.vue`'s
`register('career_goals', UraPanel)`. Tests: `tests/Feature/UraPanelTest.php` (7 cases — the flag
composed on for URA and off for the other three, the recorded mandatory rows with their states and
calendar positions, the completed arm, the Critical alert at its turn and the missed arm, the career
goals absence, the four Happy Meek readings, and no leak into a cockpit whose flag is off) and
`tests/browser/ura-panel.spec.ts` (10 cases — the three module headings and the race rows as text, the
goals absence visible, the four `N/A` cells, the alert copy, the missed state, the finale's `N/A`
deadline, the flag-off leak guard, the keyboard door, the 44px sweep with 320px reflow, and axe A+AA).

### 4.18 Global navigation (the shell, not a screen)

`resources/js/layouts/AppLayout.vue` owns the product navigation; `CareerLayout.vue` and, since 2026-10-06, `SetupLayout.vue` mount it rather than re-declaring a shell, so the skip link, the banner, the one `main` landmark and the mobile bar exist exactly once on career and wizard screens alike.

- **Ten live destinations, zero named absences.** `New Career` points at the wizard's step 1 and `Careers` at the run list, both repointed by the link audit that found those two screens had no clickable inbound path; `Veterans` became a link when the library landed. The `to: null` branch stays as the mechanism (it prints `not built` in both renders, because a `title` alone is not reliably announced) and renders nothing today.
- **Mobile is design-2.0 §41's shape**: four slots (`Home`, `Career`, `Legacy`, `Deck`) plus a `More` disclosure holding the rest. The ten-row sidebar used to render whole inside an `overflow-x-auto` strip, which is the form §41 forbids ("do not attempt to shrink the desktop sidebar onto mobile") and the reason nothing at 320px looked scrollable. `More` is a native `<details>`, so the expanded state, the announcement and the toggle come from the platform; it closes on navigation and on Escape, which returns focus to the summary.
- `design-2.0.md` §28, cited by an older comment here as the source of this list, is Trackblazer Visual Language. §41 is the brief's only Navigation section, and the desktop sidebar's destinations are the shipped list rather than a cited one.
- **Collapsible rail, added 2026-10-06.** A `Collapse navigation` button in the sidebar header, `aria-expanded` plus `aria-controls="primary-nav"`, a 44px target, keyboard-operable. Collapsed is `w-64` to `w-14` and shows a mark from `components/NavGlyph.vue` in place of each destination's word. **The marks appear at one width only: the full rail is text, with no mark beside the word** (owner instruction, 2026-10-06). Collapsing is still safe because the word never leaves the DOM: it moves to `sr-only`, the link gains a `title` carrying it, and the active destination keeps `aria-current="page"` and adds a left rule on top of its fill, because `design-2.0` §42 asks for text alternatives to icons and forbids colour-only state. The width transition is `motion-reduce:transition-none` (§43, MOTION dial 1). Desktop only: below `md` the §41 bar keeps its four text slots.
- **The marks** are `design-2.0` §44's conceptual mapping, filtered through the `DESIGN.md` §6 motif gate: home, flag, list, network, trophy, cards, spark, triangle, book, gear, chevron, drawn as stroked paths (never emoji as the primary icon system, §44). Two of §44's suggestions change under that gate: its "DNA" for Legacy becomes the **node graph**, the shape the six-node ancestry already prints, because §6 separately refuses a family-tree treatment; and its **Trophy** for Veterans is the one mark §6's allowed list does not name, which the owner chose in the 2026-10-06 task instruction rather than an agent assuming it. `DESIGN.md` §6 is where that ruling belongs permanently, and writing it is the Lore Guardian's, not this file's. `list` (Careers) and `spark` (Skills) are marks for two destinations §44 does not name a row for.
- **Persistence is `localStorage`** (`trainer-desk.nav-rail`, `wide|compact`, default `wide`), deliberately not a third `Preference` key: `Preference::KEYS` is exactly `theme` and `failure_estimate` because PRD US-11 authorises those two, so a stored rail width is a product-scope widening plus a Settings change. The cost is stated rather than hidden: the rail follows this browser, not this Trainer, and it is absent from SCR-SYS-002.
  - _Erratum 2026-10-08: the sentence above held at the time it was written and no longer does. `Preference::KEYS` is now `theme`, `failure_estimate` and `settings` (D18b, the owner's ruling on the plan's §8.6 open question 1). The conclusion it carried is untouched: the rail width is still `localStorage`, because the `settings` blob's own key set (`Preference::SETTINGS_KEYS`) is the PRD US-11 list and a rail width is still not in it._
- Tests: the nav census in `tests/Feature/RunViewTargetSizeTest.php` (counts, pinned targets, the §41 slots, the forbidden utility class, the 44px floor on all six render classes), `tests/Feature/KeyboardPathTest.php` (the wizard inherits the shell without duplicating it; the rail collapses without losing a name, with the emoji and preference checks scoped to markup so they cannot fail on the prose that documents the rule) and `tests/browser/veterans.spec.ts` (collapse by keyboard, every destination still named and titled, the left rule, the narrower box, and the choice surviving a reload).

### 4.19 SCR-VET-003 — Save Veteran

**Purpose.** File a finished career into the Veteran library with the Trainer's own tags and note (PRD FR-G-1, `SCREEN-020`, plan §8's D16 write half). This is the screen that puts a row in `veterans`, and the first caller of `RecordVeteran`; before it landed the library could only ever be empty.

#### Route / Location

`GET /training-runs/{run}/veteran` (name `runs.veteran`) and the write `POST` to the same URI (name `runs.veteran.store`), the pair `runs.inheritance` uses. Run-scoped and `whereNumber`, because a Veteran is a pointer to one career and never a standalone record (`ADR-0010`). Reached from `SCR-CAR-018`'s "What you can do next" and from the library's empty state; entered with nothing typed, it is the wizard's last step in spirit and none in the draft.

#### Layout / Structure

CareerLayout shell → record-only notice → **What this career recorded** (trainee with `lang="ja"`, turns, skills learned, races run, the five stats at the last logged turn, and the Sparks each parent carries, read-only through `SparkChip`) → **Not computed** (factor analysis, legacy value, best use) → the form (**Tags**: five server-labelled suggestion groups as `aria-pressed` chips, then a free-text tag of the Trainer's own, then the chosen customs as removable chips; **Note**: a textarea) → save → NOT IN THIS BUILD.

#### States

| State | Behavior |
| --- | --- |
| Run not Completed | The form is absent. "Nothing to file yet" names the status the run actually holds and links to the run list and the career record, because a save button that can only refuse is a worse answer than a sentence. |
| Already filed | The same screen prefills from the existing row and the button reads `Update Veteran`; one run is one Veteran, so a second save rewrites rather than duplicating. |
| No logged turn | Every stat is `N/A` with the reason, never `0` (AGENTS.md §5). Turns, skills and races are real zeros because the counts are counted, not inferred. |
| No parents recorded | "This career records no parents, so it carries no Sparks", beside the Legacy Lab where a parent is chosen. |
| Held figures | Factor analysis, a legacy value and a best use print `N/A` with the `ADR-0020` §3 ruling as the `title`. No recommendation sentence appears in any element or attribute. |
| Tag refused | Field errors for `tags`, `tags.N` and the run-level refusal render with `role="alert"` in a list under the group; the note's error is `aria-describedby` from the textarea. |
| Loading | `Loading…` with `role="status"` while a visit is in flight. |
| Error | `This screen did not load. Nothing was saved; try again.` with `role="alert"`, on a failed or invalid response. |
| Saved | Redirect to the new library row with the flash `Veteran saved: {trainee}.`, so the confirmation is the record itself, not a toast over an empty form. |

#### Implementation References

`Career\SaveVeteranController::{show,store}`, `StoreVeteranRequest` (which owns trimming, case-insensitive de-duplication, the length and control-character bounds, and the Completed refusal as a field error), `RecordVeteran`, `AncestryGraph::{build,SPARK_KIND_LABELS}`, `TrainingRun::stripValues`, `config/uma.php`'s `veteran.suggested_tags`, `components/legacy/SparkChip.vue`, `pages/Career/SaveVeteran.vue`. Tests: `tests/Feature/SaveVeteranTest.php` (16 cases) and `tests/browser/career-save-veteran.spec.ts` (5 cases).

#### Status

**Built, not landed: browser gate owed** as of 2026-10-07 (D16 write half). `tests/Feature/SaveVeteranTest.php` is green (16 cases, 110 assertions) inside the full suite, and `tests/browser/career-save-veteran.spec.ts` is written but has never run, because `npm run build` fails on another session's uncommitted `resources/js/pages/Preferences/Edit.vue:81` and a page absent from the manifest cannot be opened. The completing command, against a scratch database per §4.1 item 7: `npm run build && PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test career-save-veteran`.

**Dated correction, same day.** The blocker cleared when another session fixed its own file, the build returned
exit 0 and the gate ran on a scratch database at port 8147. All five `career-save-veteran` cases pass. It found
one defect the props tests could not see: `<span lang="ja">` was printing the trainee's Japanese name in
`text-ink-faint`, which `resources/css/app.css:49-53` documents as a non-text token and axe measured at 2.99:1
against the 4.5:1 AA floor; it is now `text-ink-muted`, the value every landed page uses for the same span. This
screen's own gate is therefore green; the slice is still open because `SCR-VET-004`'s is not.

#### Gaps

1. **Favorite, archive and delete are not built.** `veterans` holds no column for any of the three; `SCREEN-021` asks for them and adding one is a stored-shape decision for the schema owner, not a UI slice. The screen says so rather than shipping a control that does nothing, so there is no destructive flow here and no confirmation dialog to test.
2. **A Veteran cannot be named apart from her trainee.** `SCREEN-020` lists "veteran name"; the table stores none, and `ADR-0010` keeps the run's foreign keys as the identity, so the name prints read-only through the run. An independent name is a column.
3. **The suggestions are one list for every scenario.** `config/uma.php` publishes the eighteen words `screen-spec-2.0` §24 names; nothing derives a suggested set from the career, because that is a recommendation with the same `ADR-0020` §3 bar as a legacy value.

### 4.20 SCR-VET-004 — Veteran comparison

**Purpose.** Up to four filed careers aligned property by property, recorded values only (PRD FR-G-2, `SCREEN-022`, `design-2.0` §46).

#### Route / Location

`GET /veterans/compare` (name `veterans.compare`), declared **before** `/veterans/{veteran}` with the binding `whereNumber` — the same pair of decisions `/legacy/compare` records, without which the literal word `compare` reads as a model id. The selection is `?veterans[]=`, so the view is a URL: shareable, reload-safe, no client state to rehydrate.

#### Layout / Structure

AppLayout shell → record-only notice → picker (a `fieldset` of filed careers, checkboxes at 44px, the Compare button inert while nothing is picked) → the table: one `<th scope="row">` per property, one column per career, inside `role="region" tabindex="0"` so the horizontal scroll is keyboard-reachable → NOT IN THIS BUILD.

Properties, in reading order: Scenario, Status, Turns logged, Energy, Fans, the five stats, Skills learned, Races run, the ten aptitude axes, Tags, Note, Legacy read-back.

#### States

| State | Behavior |
| --- | --- |
| Nothing selected | "Nothing selected" says what to do and links to the library, rather than rendering a table of one column of headers. |
| A career with no Legacy read-back | A column like any other. This is the difference from `legacy.compare`, which refuses such a run: here it is usually the career the Trainer came to look at. |
| Fifth career asked for | Refused by `VeteranCompareRequest`, and the picker disables the unchosen boxes at the cap so the limit is a message rather than a failed request. |
| Unrecorded figure | `N/A` with the reason (`title`): no logged turn, no source-published letter, no note recorded. "No tags recorded" is a phrase, not an empty cell. |
| Held rows | Inheritance usefulness, a compatibility calculation and a factor comparison are named once, as absences, never as a per-career score. |
| Refused id | A `veterans[]` value that is not in the library returns as a field error on the picker, not an absent column, so a stale link admits itself. |

#### Implementation References

`VeteranController::compare`, `VeteranCompareRequest::{veteranIds,veteransInOrder}` (which eager-loads once and re-keys to the posted order, because a comparison of the wrong two things reads as a correct answer), `VeteranRow::from`, `Umamusume::aptitudeAxes`, `pages/Veterans/Compare.vue`. Tests: `tests/Feature/VeteranCompareTest.php` (7 cases) and `tests/browser/veteran-compare.spec.ts` (4 cases).

#### Status

**Built, not landed: browser gate owed** as of 2026-10-07 (D16). `tests/Feature/VeteranCompareTest.php` is green (7 cases) inside the full suite; `tests/browser/veteran-compare.spec.ts` has never run, for the same `Preferences/Edit.vue` blocker as `SCR-VET-003`. Completing command: `npm run build && PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test veteran-compare`.

**Dated correction, same day: gate ran, RED, and the screen was not closed.** On a scratch database at port 8147,
one of the four cases passes on its own terms and the round trip fails. Clicking `Compare` in the picker
navigates to `/veterans`, not to a two-column comparison: `Compare.vue:83-86` calls `router.get` with the key
`'veterans[]'` mapped onto an array, so the parameter does not arrive as `veterans`, `VeteranCompareRequest`
refuses it, and `redirect()->back()` follows the session's previous URL, which is the library. The route, the
URL literal and the `selected` prop are correct; the param key is the defect. Two further cases in that pass were
lost when the scratch server stopped mid-run, and a third measures the picker's `label` row (44px, `min-h-11`)
rather than the native 24px checkbox box, a measurement choice flagged for the owner.

**Dated correction 2, same day.** The owner ruled the key, `{ 'veterans[]': … }` became `{ veterans: … }` at
`Compare.vue:86`, and the re-run (port 8150, scratch `d22.sqlite`, `npm run build` exit 0, server alive at the
end) is **8 passed, 1 failed, exit 1**. The browser now lands on the comparison with both ids — `waitForURL`
reported `/veterans/compare?veterans%5B0%5D=3&veterans%5B1%5D=2` — so the navigation defect above is closed. The
one remaining failure is this screen's own spec: it matches the library href's `veterans[]=N` form, while Inertia
serialises the same array as indexed `veterans[0]=N`, which PHP reads identically. Until that assertion is
loosened, no case asserts the two-column table's contents in a browser, so this row stays **built, gate 8/9**,
not landed. `SCR-VET-003` is landed green on its five cases. Full log:
`docs/research-scratch/SLICE-RECORDS.md` §`## D16-SAVE-VETERAN-2026-10-07.md`.

#### Gaps

1. **No four-at-once browser case.** The cap is refused at the request and asserted in `VeteranCompareTest`; a DOM version would file five careers through the UI to re-check the same rule, so the disabled checkbox is the browser-side evidence.
2. **The skills and races are counted, not listed.** A column per skill or race would make the table as wide as the library is deep; the counts link onward to the career record, which owns the lists.
3. **No ordering of the columns by anything.** `FR-G-4` forbids ranking, so the columns keep the order the URL named.

### SCR-CAR-021 — Unity Cup panel (SCREEN-015)

**Purpose.** Manage scenario-specific team progression (`SCREEN-015`, `design-2.0` §27, plan §9.1). The §27 first-class Team Panel: team rank with its ladder, the per-stat team grades, the member rows, Spirit with the burst machine, the burst-count bands, Special Training, and the team races. Registered against the two Unity-Cup panel flags; the component's file name is the brief's own and nothing else about it names a scenario (G-33).

#### Route / Location

No route of its own: `ScenarioPanel.vue` (the Cockpit's scenario region, `SCR-CAR-011`) renders it once per ON flag — the `team_race` registration draws the ported race panel, the `team_rank_ladder` registration draws the Team Panel. The `race_calendar` flag draws a note pointing at the left column, because `RunRaceStrip` already renders that calendar and the shell's fallback would have printed a false "cannot draw" beside it.

#### Layout / Structure

Desktop: team cards (rank gauge, ranking bonus, stat grades, member roster) beside the burst regions (Spirit meter, readiness and held-advice sentences, band table, Special Training); mobile stacks the two columns, no horizontal scroll. The three ported references keep their props and copy: `TeamRankGauge`, `SpiritBurstRoster`, `TeamRacePanel` — the same components the run record screen mounts, so the two screens cannot disagree about one fact.

#### Data and provenance

Recorded on the run: the rank letter (`TurnEvents\TeamRankPayload`, the facility level derived through the config mapping), the per-teammate burst states (`TurnEvents\SpiritBurstPayload`), and team race entries with circles and placement (`scenario_slots` rows of kind `team_race`). Configured: the ladder, the circle margin, the burst-count bands (`config/scenarios.php` `spirit_burst_bands`, transcribed from `docs/scenarios/02-unity-cup.md:213-218`), the payout timing, and the two Special Training facts (`team_training_energy_penalty`, `wit_burst_energy_bonus`). The +30 Team Ranking Bonus renders **Estimated** beside Rank S only — the rank the recorded frame shows it at; other ranks pay a figure this corpus does not state. The band table carries one `Estimated` badge and one warning line naming all three publisher conflicts the run report records (§7.4: the Skill Point figures and the payout month; §8.2: whether Extreme Bursts count), and never places this run in a band — the count the bands read is not recorded, and D-223 keeps bursts as states, not counters.

**Not built, on the record.** Burst readiness is not computed: the bands are thresholds, but no Spirit reading has a writer, so there is nothing to set against them — named absence. "Recommended timing" and "projected benefit" are held advice (`ADR-0020` §3) — named absences with the ruling in the `title`; the trigger rule itself is stated (a burst fires on the next Special Training with a teammate whose gauge is full), and nothing beyond it is computed. Per-stat team grades, member stats and roles, current Spirit, Unity Trainings and burst counts, team name, motto and league standing have no writer: each is `N/A` or a named absence. Recording any of them waits on the capture proposal of §7-6, which stays the owner's; the panel is read-only.

#### States

| State | Behavior |
| --- | --- |
| No rank recorded | The gauge's "Rank not recorded" sentence, the ladder rendered as reference, and no +30 bonus claim at all — the bonus binds to a rank, and none exists. |
| Rank recorded | The letter, its derived facility level with the cause named (D-222), and the current rung marked `aria-current="step"`. `widget_values.team_rank` and `header.values.team_rank` carry the same letter, so the header strip, the widget meter and the gauge cannot disagree. |
| Rank S recorded | The one rank the client frame shows a Team Ranking Bonus beside: "All Attributes +30" renders with the `Estimated` badge. Any other rank prints no bonus figure, because the corpus states none. |
| Roster empty | `SpiritBurstRoster`'s own empty state, naming the six states; a recorded roster renders each teammate as a row with the state's glyph AND word (never colour alone). |
| Spirit unrecorded | `N/A` with the reason as the `title`, on a meter whose labelled track renders empty — no sourced maximum, no invented fill. |
| Burst readiness | A named absence: not derivable from anything this build holds. |
| Timing / benefit | Named absences with the `ADR-0020` §3 ruling as the `title`. |
| No team races | The ported panel's margin line ("Aim for at least 3 circles — a margin, not a win condition") and its empty state; recorded entries render with the margin word per entry. |
| Run in a band | Never shown: the band table is reference, and the absence of the run's own position is stated beneath it. |
| Loading / error | None of its own: server-rendered with the page, which carries the `role="status"` and `role="alert"` surfaces (`ADR-0007`). |
| Flag off | The registration does not draw; no renderer output exists for a flag the scenario does not compose. |

#### Implementation References

`CockpitController::teamSection()` and the `team` key of `scenarioSection()` (the uniform shape the pinned key list in `ScenarioPanelTest` names), `resources/js/components/scenario/{UnityCupPanel,RaceCalendarNote}.vue`, the registration block on `ScenarioPanel.vue`, `components/{TeamRankGauge,SpiritBurstRoster,TeamRacePanel}.vue` (unmodified), `ProvenanceBadge` (Estimated), `config/scenarios.php` `unity_cup.spirit_burst_bands` and `burst_payout_timing`, `types.ts`'s `ScenarioTeamSection`. Tests: `tests/Feature/UnityCupPanelTest.php` (6 cases — full team, partial teams, none, the non-team scenarios, a fifth scenario) and `tests/browser/unity-cup-panel.spec.ts`.

#### Status

Built 2026-10-07 (slice E3). `UnityCupPanelTest` 7 cases; targeted 20 passed (583 assertions) across `UnityCupPanelTest`, `ScenarioPanelTest` and `CareerCockpitTest`; `unity-cup-panel.spec.ts` 7 passed and `scenario-panel.spec.ts` 8 passed (15 together, 4.2m) against a `VACUUM INTO` scratch copy on port 8166; `npm run typecheck` and `npm run build` exit 0; Pint run scoped to this slice's files (a concurrent session's half-written test file blocked the whole-suite load mid-hand-off); PHPStan clean on this slice's files; lore-code 115 tree hits with none in this slice's files. The doubt review's three accepted findings are in the artifact (Rank-S-only +30, the three-conflict band caveat, the readiness sentence), and two of this slice's own spec defects (an unsatisfiable exact-text ladder assertion; a keyboard case that asserted nothing) were fixed before this run. The data states a browser cannot reach (populated roster, recorded rank) are covered at props level because no write path exists.

**Dated note, same day (follow-up review).** One red browser gate was found on this slice's account and cleared by it: `career-cockpit.spec.ts:107` asserted no RECOMMENDED marker page-wide, and `getByText` matches case-insensitively as a substring, so the assertion matched this panel's absence sentence "Recommended timing and projected benefit are not built" rather than the grid's marker. A probe run proved it (`recommendedKeys` empty, the advisor's refusal printed, the only matching node the panel's prose). The assertion is now scoped to the Actions region, the same way the ranked case in that file already scopes itself, and the file re-runs 7 passed; E4 met the identical collision in its own rotation-absence copy and scoped it the same way, which is the durable note for later slices: **panel prose naming a held field will collide with any page-wide word assertion on this screen.**

#### Gaps

1. **No write path for team rank or burst states.** The structured payloads ride `turn_events` but no Form Request or screen records them (the "own Requests" line in `StoreTurnEventRequest`'s docblock is aspirational). Until the §7-6 capture proposal is ruled on, the panel is a read-only mirror of what the run screen's payloads hold, and the browser suite can only exercise absence states.
2. **The browser cannot see a populated roster.** A consequence of gap 1, not a test gap: the roster's glyph-and-text rows, a recorded rank and a recorded team race are asserted in `UnityCupPanelTest` at props level. If the owner rules a capture path, the browser cases follow it.
3. **The +30 bonus is one number with an inference attached.** The corpus saw it beside Rank S only, and its binding to the rank rather than the league standing is inferred from screen position; it renders `Estimated` and only for that rank, because a figure for the other rungs would be invented. A source that states the rule would move it to Confirmed and widen the gate.
4. **One registry Map serves both chains.** `registry.ts` keys widget renderers and panel renderers in the same Map, so a matrix that ever names a widget exactly like a registered panel flag (`team_race`, `team_rank_ladder`, `race_calendar`, `career_goals`) would resolve the panel component against the narrow widget props and throw inside the render. No scenario declares that today, and the fix belongs to the shell owner (two Maps, or a prefixed key), not to a panel slice.

### SCR-CAR-022 — Trackblazer panel (SCREEN-016)

**Purpose.** Manage Trackblazer-specific resources (`SCREEN-016`, `design-2.0` §28, plan §9 E4): Grade Points, Shop Coins, the Pro Shop catalogue and its purchase write, rival races from `RaceCatalogSlot`, the points-league finale (`finale.kind === 'points_league'`, `finale.races === 3`), and the epithet routes checklist. The component file name is the brief's own (`TrackblazerPanel.vue`) and the brief's other two retired reads (`x-grade-point-meter`, `x-shop-panel`, `x-epithet-checklist`) ported here for behaviour parity; nothing about the component names a scenario or label (gate G-33, D-240).

#### Route / Location

No route of its own: `ScenarioPanel.vue` (the Cockpit's scenario region, `SCR-CAR-011`) renders `TrackblazerPanel.vue` once per ON flag — `grade_objectives`, `shop` and `epithet_routes`. The Cockpit's action area (`SCR-CAR-011`) gains a single "Shop" button when `scenario.shop !== null` (Trackblazer only), which scrolls the Shop region into view and focuses its heading (WCAG 2.4.11).

#### Layout / Structure

Three regions, each mounted by name:

- **Grade Point** (`grade_objectives` mount): a working-toward line, a numeric-and-bar meter (the bar labelled), the surplus / remaining text, the ladder as an ordered `<ol>` with a row per period carrying `name`, `required`, and that period's own `earned` (no running total — D-232, no two deadlines share), and the rival-race list from `RaceCatalogSlot::forScenario('trackblazer')` as facts only.
- **Pro Shop** (`shop` mount): the rotation countdown with `countdown not recorded` for an absent `shop_resets_in`, the catalogue list in config order, the recorded purchases list, the purchase form posting to `runs.purchases.store`, the spend-total line, and "Shop Coins: not yet recorded" with the no-earning-side-writer reason in the copy.
- **Epithet routes** (`epithet_routes` mount): the checklist as `<li>` rows, each carrying the route, the epithet, the state pill (glyph plus word; never colour alone, plan §13 Selective Attention), the reward, the outstanding named races, and a final line citing the points-league finale and the named absence for "Twinkle Star Climax" (§7 conflict row 31).

#### Data and provenance

Recorded on the run: turn_events carrying `ShopPurchasePayload` (item, cost, effect) for purchases, `current_objective_index` (nullable 1-based) and `race_entries.grade_points_earned` for Grade Points, and `race_entries.scenario_slot_id` for the seen-titles the epithet checklist compares. Configured: `trackblazer.shop_items` (transcribed verbatim from `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` "Full Shop Item List", GameTora), `trackblazer.epithet_routes` ("Epithets (Race Route Bonuses)", uma.guide, post 2026-07-01 rework), `trackblazer.grade_objectives.standard` plus `grade_objective_labels`, `trackblazer.shop` (`rotation_turns`, `max_copies_per_item`, `locked_until_debut`), and `trackblazer.finale` (`kind`, `races`). The grade-point ladder shows only the standard track because dirt-leaning and limited-turf-range tracks have no aptitude input that places a trainee on them (KI-15).

**Not built, on the record.** "Recommended Purchase" is named absent: the rotation is not modelled (plan §3 knowledge groundings), and a "best value" sort, a default selection, or any row-level highlight would state a state of a lineup this tool cannot see (D-256). Shop Coins balance is named absent: no earning-side writer exists (`shop_coins_by_placement` is config-only and the per-rotation yield is not stored). The official finale name is a named absence with `title` citation citing §7 conflict row 31. Per-stat team grades, member stats and unity-training/burst counts are team concepts Trackblazer does not compose. The "Twinkle Star Climax" string is never printed anywhere on this slice.

#### States

| State | Behavior |
| --- | --- |
| Period not reported | "No period reported" sentence; the bar stays empty; the surplus line states the meter has no target to measure. `unassignedCount` races print a separate sentence ("results have no period entered against it, so none of them counts toward any total below"). |
| Period recorded, no races logged | The bar is empty; "not yet recorded" copy with the season caveat ("no races are logged for this run"). |
| Period recorded, races logged but unpriceable | The bar is empty; "not yet totalled" copy with the unpriced count, never a running total. |
| Period recorded, total met | The bar fills to the requirement; the line carries `earned / required · remaining to go` or `· over the objective` once surpassed; the surplus line names "Surplus does not carry over", not bankable. |
| Rotation unrecorded | "countdown not recorded" sentence; the form still accepts a purchase. |
| No purchases | The empty-state copy "No purchases recorded for this run"; catalogue and form still render. |
| Off-catalogue item post | Field-bound error on `item`, `aria-describedby="purchase-item-error"`; the page comes back with `role="alert"` reading the per-field message. |
| Wrong price post | Field-bound error on `cost`. |
| Held-copies cap exceeded | Field-bound error on `item` ("You already hold N of M copies…"). |
| Epithet `unverifiable` | Distinct rendering path, never folded into `open` (D-220, D-256): the state pill shows `?` plus the word "Unverifiable", the sentence is "<em>aggregate condition</em>, which this surface cannot read". |
| Cockpit Shop jump | Trackblazer-only: a button under `ActionGrid` scrolls and focuses `#trackblazer-shop-heading` (WCAG 2.4.11). Non-Trackblazer scenarios render no Shop affordance. |
| Loading / error | None of the panel's own: server-rendered with the page; `role="status"` and `role="alert"` live at the page level (`ADR-0007`). |
| Flag off | The registration does not draw; no renderer output exists for a flag the matrix does not turn on. |

#### Implementation References

`CockpitController::gradeSection()`, `shopSection()`, `epithetSection()`, `rivalSection()` and `finaleOfficialTitleAbsence()` build the payload that `scenarioSection()` returns; the keys are part of the ScenarioPanelTest pin (plan §9 E4 part 2). `resources/js/components/scenario/TrackblazerPanel.vue` branches on the flag key in `props.name`, never on a scenario name. The Cockpit's "Shop" jump lives on `resources/js/pages/Career/Cockpit.vue` (a `jumpToShop` helper, `hasShop` computed). Types in `resources/js/types.ts`: `ScenarioPanelSection` carries five Trackblazer-specific nullable keys, three new interfaces (`TrackblazerGradeSection`, `TrackblazerShopSection`, `TrackblazerEpithetSection`). Tests: `tests/Feature/TrackblazerPanelTest.php` (10 cases — Trackblazer exposure, catalogue order, no-`recommended` audit, recorded purchase read-back, off-catalogue refusal, wrong-price refusal, held-copies cap refusal, non-trackblazer uniform shape, grade ladder with no invented totals, points-league finale + Twinkle Star Climax absence citation) and `tests/browser/trackblazer-panel.spec.ts` (keyboard path, purchase round-trip, validation error tied to input by `aria-describedby`, epithet glyph+text, 44px sweep, 320px reflow, axe A+AA scoped to `#app`).

#### Status

Landed 2026-10-07 (slice E4). Slice plan at `docs/research-scratch/PLANS-AND-BRIEFS.md` §"E4 — Trackblazer scenario panel (SCREEN-016), 2026-10-07". Hand-off: targeted **`TrackblazerPanelTest` 10 passed (127 assertions)**, **`trackblazer-panel.spec.ts` 10 passed (6.0m)** against the shared `:8127` server, sibling re-runs green (`scenario-panel.spec.ts` 8 passed, `ura-panel.spec.ts` 10 passed, `unity-cup-panel.spec.ts` 7 passed), full Pest suite **1541 passed / 2 skipped (26600 assertions)** before the layout cap, Pint clean, `vendor/bin/phpstan analyse app/Http/Controllers/Career/CockpitController.php app/Http/Requests/StoreShopPurchaseRequest.php app/Models/TrainingRun.php --no-progress --memory-limit=1G` `[OK] No errors`, `npm run typecheck` exit 0, `npm run build` exit 0 with `Cockpit-D-WiUHWv.js` 53.65 kB, `composer lore` and `composer lore-code` clean on E4 files.

**Two decisions the panel makes that the test surface holds.**
- The rival list caps at eight rows (Miller's Law, plan §13). The Junior-Year seed's 50+ entries pushed the page over WCAG 1.4.10 reflow at 320px; the Race Database screen shows the full set, and the panel's "First 8 of N catalogued races. The full set is on the Race Database screen" line names the cap.
- The grade bar renders only when a period is reported (design-2.0 §49 absent-is-text-only). The browser case asserts the no-period sentence, not the bar; this is the same trade-off E3 made for the Spirit meter.

**Two test-side findings the browser pass produced and tightened in place, not cut.**
- A tampered item value reaches the boundary through a `<datalist>`-backed text input rather than a `<select>`. The Form Request is the authority; the option list was a convenience.
- "No `Recommended` text" scopes to "Recommended Purchase" card / heading / accessible image name, because the panel's own rotation-absence copy carries the word as a negation.

#### Gaps

1. **The browser cannot see a populated roster of purchases or grade rows.** A consequence of the panel's write path going through `runs.purchases.store` and the grade write through `race_entries.grade_points_earned`. The browser suite exercises the empty + keyboard paths; populated states are asserted at props level because no in-browser seeding harness exists.
2. **No catalogue rotation flag exists.** The component contract reserves space for `sale` and `limited` per-row flags, but the matrix does not carry them and no rotation writer exists. Adding a rotation would unblock the flags without a component edit (gate G-33).
3. **Shop Coins balance is permanently `not yet recorded`.** The earning side of the shop economy is not stored today (`shop_coins_by_placement` is config-only). A `TurnEvents\ShopCoinAwardPayload` model would let the panel render a balance, but D-232's "unspent coins die with the run" still applies.

### SCR-CAR-023 — Scenario Race Planner

`GET /training-runs/{run}/races/planner` name `runs.races.planner`, `App\Http\Controllers\Career\RacePlannerController`. SCREEN-017, plan §9 E5. The whole calendar, grouped, with the races the Trainer picks compared side by side. It descends from the run's own URL the way the Cockpit does, and it is **read-only except one write it does not own**: each card's `Enter Race` posts to the existing `runs.races.store` and its `StoreRaceEntryRequest`, so no second race-write route exists.

Reached from the Race Decision screen's "Plan the whole calendar" door (`SCR-CAR-013`), and by URL. A screen reachable only by URL is a defect (D14's finding on `runs.timeline`), which is why the door exists.

#### Layout / Structure

`CareerLayout` → the position being planned from → the held figures → the deadlines region → the four groups, each one `components/career/RacePlanList.vue` rendering a heading, its rule, and either `components/career/RaceCard.vue` per race (D10's card, reused) or its named empty state → the reward comparison, a real `<table>` with one `<th scope="row">` per property and one column per selected race (design-2.0 §46) → the target alignment, the same columns with plain words.

#### The four groups, and the rule each is built by

One query over `race_catalog_slots` (`scopeForScenario`, ordered by year, turn, sort_order) with the run's own `raceEntries` eager-loaded, partitioned by two citable facts: `is_mandatory`, and `RaceCatalogSlot::isAtOrBeforeTurn()`, which is the single owner of the position comparison (`ADR-0015`), so this screen and the run race strip cannot disagree about whether a turn has arrived.

| Group | Rule | Empty state |
|---|---|---|
| Mandatory races | `is_mandatory = true` (7 of 410 rows) | "No mandatory race is on this scenario's calendar." |
| Upcoming races | not mandatory, and the turn has not arrived | named, or the position sentence when no turn is logged |
| Optional races | not mandatory, the turn has arrived, and the run recorded no entry for it | named, or the position sentence when no turn is logged |
| Rival races | **none.** No column marks a rival race | the absence names why, with the corpus citation |

A reached race the run has already entered is left out of every group: the run screen's race panel owns the history, and the same row in two groups reads as two races.

#### The reward comparison and the alignment

Ten rows, in one order for every race: Grade, Distance, Distance band, Surface, Fan gate, Fan gain, Reward, Skill Points, Grade Points, Shop Coins. Five of them and Grade Points have a source behind them; Fan gain, Reward, Skill Points and Shop Coins are `N/A` with a `title` naming the missing column, and Grade Points is filled by `TrainingRun::gradePointsForTier()`, which returns null unless the run's own scenario entry defines a `grade_point_by_grade` table — so the cell is driven by config presence and never by a scenario name (gate G-33).

The alignment compares the race with the run's recorded `BuildTargetPayload` on distance band, surface and running style, in plain words: `Matches`, `Does not match` or `Not recorded`, never a score and never a percentage, with the `ProvenanceBadge` `calculated`. A running style is the trainee's aptitude and no column holds a per-race one, so that row is always `Not recorded`. A run with no target records nothing and the section says where the target is entered.

#### The held figures, and the promise not made

`Win probability: N/A` and `Expected risk: N/A`, each with a `title` naming the `ADR-0016` blocker. The brief's recommendation block is not built and no LOW / MEDIUM / HIGH wording appears. The deadline region states the next uncleared mandatory race and its turn, and raises the Level 1 `AlertRow` (E1's component, `tone="critical"`, glyph plus word) when `isAtOrBeforeTurn()` says that obligation's turn has arrived. It never claims "no critical training deadline will be missed": that is a promise about the whole career, which this tool cannot make.

#### States

| State | Behavior |
|---|---|
| Empty (no scenario) | One sentence naming the missing scenario, why it matters (no calendar to plan against) and what to do (choose one on the run screen). The four groups do not render at all. |
| Empty (no turn logged) | The mandatory group still renders, because an obligation does not need a position; the two position groups carry the sentence saying no turn has been logged, so neither claims a race is "ahead". |
| Empty (a group, with a calendar) | Each group states its own absence rather than drawing a blank region, and the rival group always states its own because no column marks a rival race. |
| Truncated | A position group lists at most eight rows, and the cap is named with the full count and a pointer at the Race Database screen. A list that stopped silently would read as a complete calendar that happens to hold eight races. |
| Nothing selected | The comparison says what to do rather than drawing an empty table; a comparison needs two columns to be a comparison. |
| Loading | Page-level (ADR-0007): `role="status"` "Loading…" while a visit is in flight. Each card's `Enter Race` carries its own "Entering…" label, because that write is user-initiated. |
| Error (a visit failed) | `role="alert"`: "That screen did not load. Nothing was saved; try again." Inertia's `invalid` and `exception` are both cancelable and return `false`, so this is the single surface. |
| Error (a refused write) | The `role="alert"` inside the card's drawer printing the first message `StoreRaceEntryRequest` returned. Nothing was written. |
| After a successful write | `storeRace()` returns to the page the form was posted from, so entering a race from here stays here; the flash banner names it. |

#### Gaps

1. **Rival races have no source.** The corpus says rival appearance is random and bounded by the trainee's aptitude array (`docs/scenarios/03-trackblazer.md`; `docs/research-scratch/DESIGN-CORPUS.md` records that a rival at a distance she is D-rated in is impossible), and no column marks one, so the group renders a named absence rather than listing races the client might never mark. A rival marker column would unblock it without a component edit.
2. **"Optional" is the brief's word, and the plan defines it.** The group is "not mandatory, the turn has arrived, nothing recorded", because that is the only reading that does not overlap the other two. Whether that is the intended set is an owner question, recorded in the slice plan.
3. **A position group caps at eight rows.** The full calendar is ~1.6 MB of props and `php -S` is one process, so the payload is paid on every request; eight is the ceiling E4's rival list already uses, and the truncation is named. A career-year filter or a paginator is the upgrade path.
4. **`Skip` records nothing.** As on `SCR-CAR-013`: the card's link returns to the run screen, and the run screen's race panel offers the `Skipped` status. `ADR-0003`'s grain and this screen's deferral are recorded in `SCR-CAR-013`'s gaps, not re-decided here.
5. **Axe coverage is in `tests/browser/career-race-planner.spec.ts`** through the shared `tests/utils/accessibility.ts` builder, which scopes every scan to `#app` (KI-63).

#### Implementation References

`RacePlannerController`, `RaceFacts` (the ten-field list, shared with `RaceDecisionController` so the two screens cannot drift), `RaceCatalogSlot` (`is_mandatory`, `isAtOrBeforeTurn`, `hasFanGate`, `hasMaidenGate`, `distanceLabel`, `yearLabel`, `turnNumber`, `scopeForScenario`), `RaceEntry` (`placementOrdinal`), `RaceEntryStatus`, `TrainingRun::{nextTurnToPlay,hasScenario,scenarioKey,buildTarget,gradePointsForTier}`, `BuildTargetPayload`, `StoreRaceEntryRequest` (the reused write), `pages/Career/RacePlanner.vue`, `components/career/RacePlanList.vue`, `components/career/RaceCard.vue`, `components/scenario/AlertRow.vue`, `components/ProvenanceBadge.vue`, `layouts/CareerLayout.vue`. Tests: `tests/Feature/CareerRacePlannerTest.php` (the four groups and their rules, a recorded entry moving a race out of the optional group, the ten comparison cells present and absent, Grade Points from the config table, the three alignment words, no target, the held pair naming `ADR-0016`, the next obligation and the Critical alert, the two calendar absences, the group cap, and the no-percentage assertion) and `tests/browser/career-race-planner.spec.ts`.

#### Status

Implemented 2026-10-07 (slice E5).

### SCR-CAR-024 — Our Grand Concert baseline strip (SCR-017)

**Purpose.** Give the fourth `[Global]` scenario a surface that states only what a source holds. Its
every panel flag is off, so the panel region has nothing to mount and draws its baseline strip instead:
the scenario's name and documentation state, the five published stat caps, the scenario's own one-line
reason no panel is drawn, and what the advisor does and does not do here (plan §9 E6, `D-241`, gate
G-41). The brief's name for this surface is `SCR-017`, which is **not** `SCREEN-017` (the Scenario Race
Planner, `SCR-CAR-023`); `design-2.0` §29's "Grand Concert (basic tracker only, advanced advisor
limited)" is what the strip implements, and the brief's fuller panel is deliberately not built.

#### Route / Location

No route of its own. `ScenarioPanel.vue` (`SCR-CAR-019`, mounted in the Cockpit's scenario region,
`SCR-CAR-011`) draws it in the branch where no panel flag is on. There is no flag to register against,
because "every panel off" is the absence of a flag rather than a flag's value, and the strip is
therefore config-driven: a fifth scenario that leaves every flag off reaches it for one config entry and
no component edit (gate G-33, `D-240`).

#### Layout / Structure

One region, in this order:

- the scenario's label and `ScenarioStatusBadge`;
- the ruleset `N/A` line and its `title` (E1's own, unchanged);
- the **published stat caps** as a table of five rows, one per stat in `config('scenarios.stat_order')`,
  each carrying `base`, `bonus` and `cap` as three separate cells. The header carries a `ProvenanceBadge`
  reading **Confirmed** with a `title` naming `config/scenarios.php`, and the matrix's verification date
  beside it. Drawn only for a scenario the run declared;
- the scenario's **own reason** its panels are off, as an `AlertRow` with a `ProvenanceBadge` reading
  **Unknown**, whose `title` names the file the reason is recorded in. The generic "no panels of its own"
  sentence renders only where the entry declares no line of its own, so the two never print twice;
- the **advisor-scope** line, `recommendations_absence`, saying no scenario advice is offered and the
  advisor ranks the turn being decided.

#### Data and provenance

Configured: `our_grand_concert.cap_bonus` (Speed 400, Stamina 100, Power 100, Guts 300, Wit 100) over
`base_cap` 1200, which is the 1600/1300/1300/1500/1300 the plan §3 row records as corroborated
2026-10-05 by two independent sources; `verified_at` (2026-09-27), labelled a verification date and not
a fetch date; and the entry's own `panel_absence` and `panel_absence_title` display strings. `cap` is
read through `ScenarioCaps::forRun()`, the single owner of the base-plus-bonus arithmetic (`ADR-0015`),
so the strip and the header's stat bars cannot disagree about a ceiling. Recorded on the run: nothing
the strip adds — the scenario's own widgets are empty for this scenario (its `widgets` are the baseline
three, subtracted), and the run's tracked values are the header's figures above.

#### States

| State | Behavior |
| --- | --- |
| Rendered | Every panel flag off, the run declared: the label, the partial badge, the caps table, the scenario's own reason and the advisor-scope line. |
| No panel of its own | The generic sentence renders only when the entry declares no `panel_absence`; the specific reason replaces it rather than joining it. |
| Run declared no scenario | The caps table and the absence line do not render. The strip keeps E1's wording for an undeclared run, because lending it the baseline entry's caps would state a composition nobody chose. |
| Caps unrecorded | Not reachable: `config/scenarios.php` is the source and it carries a `cap_bonus` for every entry. A missing row is a config failure, not an empty state. |
| Loading / error | None of its own: server-rendered with the page, and the region has no user-initiated async action (`ADR-0007`). |
| Held | No song, lesson, Performance Token or other unmeasured mechanic appears anywhere in the payload or the markup. The mechanics are sourced in `docs/scenarios/07-grand-concert.md`; the client's own screen text is not measured, and the strip says that rather than rendering the guide. |

#### Implementation References

`CockpitController::scenarioSection()` (the badge line reads `partially_documented`, per the owner's
2026-10-07 ruling at plan §4.1 item 6) and its `capsSection()` helper; `ScenarioCaps::forRun()`;
`config/scenarios.php` `our_grand_concert.{panel_absence,panel_absence_title}`;
`resources/js/components/scenario/ScenarioPanel.vue` (the every-panel-off branch);
`resources/js/types.ts` (`ScenarioPanelSection.caps`, `.panel_absence`, `.panel_absence_title`). Tests:
`tests/Feature/GrandConcertPanelTest.php` (the every-flag-off G-41 property, the five cap rows against
`ScenarioCaps`, the source citation, the unmeasured-mechanic floor, the absence declared on one scenario
only, and the fifth-scenario strip) and `tests/browser/grand-concert-panel.spec.ts` (the badge text, the
caps table, the two provenance badges, the honesty sentence and its `title`, the 44px sweep, the 320px
reflow and the axe A + AA scan scoped to `#app`).

#### Status

Built 2026-10-07 (slice E6). Slice plan at `docs/research-scratch/PLANS-AND-BRIEFS.md` §"E6 — Our Grand
Concert baseline strip (SCR-017), 2026-10-07". Three of the dispatch brief's premises did not hold on
this tree and are corrected in that plan: `documented` reads `true` rather than `false`;
`docs/scenarios/07-grand-concert.md` is a sourced guide rather than a stub, so the brief's sentence
about the corpus was not printable; and the badge key was the owner's ruling, not a slice's.

#### Gaps

1. **The badge's key is uncommitted with D17.** `partially_documented` and the Scenarios database screen
   that reads it are in the same working tree and are not in `HEAD`. If D17 is reverted the badge falls
   back to `Documented` and this screen's browser case goes red. Owner call: land D17 with or before E6.
2. **The caps render twice on one screen.** The header's stat bars and this strip both print the same
   ceilings. That is deliberate (G-41's own wording is "baseline strip plus known caps"), and dropping
   either is a later decision rather than an oversight.
3. **The strip is the extension point, and it is one config key.** When the client's own strings are
   captured, the scenario gains surfaces by a config edit plus new widget renderers, never by editing the
   strip: a widget key reaches `ResourceMeter` or a registered renderer through the existing chain, and a
   flag reaches the registry. `panel_absence` is what should be dropped at that point.

## 5. Workflow Specifications

### WF-001 — Run lifecycle

Actor: Trainer. Start: SCR-RUN-001. Completion: run listed/deleted.

```mermaid
flowchart LR
    L[SCR-RUN-001 list] --> C[SCR-RUN-002 create]
    C -->|valid| D[SCR-RUN-003 detail]
    C -->|invalid| C
    L --> I[SCR-RUN-004/005 import] -->|commit| D
    D -->|export csv/json| X[download]
    D -->|delete confirm| L
```text

Failure paths: create/validation back to create with `old()`; import rows fail validation on either POST → form re-renders, nothing written. Authorization boundary: none (local tool); run ownership is enforced only for nested turn routes (404).

### WF-002 — Guided turn logging (the core loop)

Start/Completion: SCR-RUN-003. Two stages on one endpoint; the split exists so a commit is always a second, deliberate action (D-51), enforced server-side by the `previewed` marker that only a preview response renders.

```mermaid
flowchart LR
    A[choice step: activity radio] --> B[enter totals]
    B -->|stage=preview POST → PRG GET| P[preview bubbles = entered − stored previous]
    P -->|stage=confirm POST| W[(turn row + failure event if Failure)]
    P -->|invalid| B
    B -->|first turn: empty preview, still committable| W
    A -.raw escape hatch.-> H[details form writes on submit] --> W
```text

Alternative path: hatch (D-53 reachable, not default) skips preview. Failure path: any rejection rehydrates the rail (and only the rail, keyed on its `stage` marker) — the two entry paths never fill each other. Notes: preview is arithmetic on two Trainer rows, never a projection; no odds shown (Planner Rule 5).

### WF-003 — Historical import

SCR-RUN-001 → 004 (file or paste) → 005 (all rows shown, turns-only limitation stated) → commit (transaction) → SCR-RUN-003 with provenance line. Failure: either POST can reject; edited textarea fails on the commit POST, not before the action. Start: SCR-RUN-004; Completion: SCR-RUN-003.

### WF-004 — Engine proposes, Trainer disposes

`uma:fetch` → Fuzzy/None candidates → SCR-REV-001 → Confirm/Alias/Reject → catalog (SCR-CAT-001/002) improves; aliases make future fetches auto-match. Never touches `is_manual` rows. No screen-to-screen loop: candidates leave the queue on resolution.

### WF-005 — Deck equip and re-read

SCR-SUP-001/002 (browse) → SCR-RUN-003 deck panel: six position slots, one open picker at a time (query `?deck_slot=`), closed slots post hidden inputs; save = delete-then-insert all six in one transaction (a slot is a *position*); duplicate card refused ("Break the duplicate instead."); empty deck is legal and renders as a named absence; position 6 always labeled `Friends`.

### WF-006 — Race record (scenario-aware)

SCR-RUN-003 race section: `?entry_mode=calendar|manual` GET branch switch (server state, survives failed submits via `old()`), calendar path picks a `race_catalog_slots` row for the visible `?year=` (or a previously hand-entered `free_race`), manual path creates both the slot and the entry; shared fields: status, placement, fans gain, circles (team-race slots only — refusal is a field error, not a 500), objective period, named turn.

### WF-007 — Shop purchase (Trackblazer)

Shop panel → purchase form (turn, item from the scenario catalogue, cost as read, effect as read) → price/held-copy refusals as field errors → one `turn_events` Scenario row; rotation countdown reported through the shared run-update.

## 6. Screen-State / Lifecycle Notes

| Screen            | State axis        | Values                                                   | Transition owner                                                                                             |
| ----------------- | ----------------- | -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| SCR-RUN-003       | rail stage        | `training/facility` choice → `outcome`                   | server (`previewed` in flashed input), never client state                                                    |
| SCR-RUN-003       | race branch       | `calendar` / `manual`                                    | GET query + `old()` precedence                                                                               |
| SCR-RUN-003       | career year       | `?year=` clamped tabs                                    | `careerYearForTab()` (address state so tabs are linkable/back-safe)                                          |
| SCR-RUN-003       | open picker row   | deck slot / skill row                                    | query param → errored row → first                                                                            |
| SCR-RUN-004/005   | import step       | form → preview → commit                                  | same view, two POST routes                                                                                   |
| SCR-RUN-*         | run status        | Active / Completed / Retired                             | select on the run page's own PUT form (§7-4, resolved 2026-10-04); create and import still set it at entry   |
| SCR-REV-001       | candidate         | Pending → Confirmed/Aliased/Rejected                     | verdict action                                                                                               |
| Skills/refs       | availability      | stored-at-write, applied-at-read (`availableOnGlobal`)   | engine columns, no UI toggle                                                                                 |
| Catalog           | disclosure        | confirmed / +unconfirmed (`?show_unconfirmed`)           | GET opt-in; the tab links carry it through (`FormTabs.vue` keeps the lever on every tab GET)                 |

## 7. Screen-System Gaps & Inconsistencies

1. **Documentation conflict (README vs routes).** `README.md` "Web surface" still listed `/design-preview`; the route is gone from `routes/web.php`, and `GuidedTurnOnRunViewTest` header states the surface "has since been deleted." README was stale. *Documentation Gap.*
   **Resolved 2026-10-04.** The row is gone and the deletion is recorded in prose instead. `EmptyStateCommandNamesTest` now checks every path in the Web surface table against the registered route list, so a deleted route cannot survive in that table again by the same accident. (A sibling session's README restructure also removed the row while this was in flight; the guard is the durable part.)
2. **Routes without UI entry.** `runs.turns.update` (PUT) and `runs.turns.destroy` (DELETE) are implemented, owned-checked, and covered by `TrainingRunTest` edits/removals — but **no form on any screen posts to them**; the turn table has no row Edit/Delete action. Behavior exists in the backend and is unreachable in the product. *Navigation/Implementation Gap; Unresolved Requirement.*
   **Resolved 2026-10-04.** Each turn row now carries an "Edit turn N" link that opens the row (a query parameter, `?edit_turn=`, because this page ships no script), and the opened row holds the PUT form and the DELETE form. Reuses `StoreTurnEntryRequest`, so an edit meets the same caps and per-run turn uniqueness as a create; it sends no `stage`/`choice`/`outcome`, because `updateTurn` writes readings only and a turn's outcome lives in `turn_events`, which that path does not touch. Pinned by `TurnRowActionsTest`, including the cross-run 404 and the fact that the row delete form only exists on the open row. Two things the resolution exposes and did not fix: `updateTurn` leaves a stored failure event untouched, and `destroyTurn` deletes the turn row but not its `turn_events` sibling, so a later turn with the same number can inherit an orphan chip.
3. **Backend field with no screen.** `TrainingRun.legacy_selection` (ADR-0010, model fillable + `LegacySelectionPayload`) has no input on create, update, or any form, and no display. *Referenced but Missing.*
   **Deferred with reason 2026-10-04; intentional, backend-only.** Verified against the tree: the column, the `#[Fillable]` entry, the `array` cast and `legacySelection()` all exist; `grep -rn legacy_selection` over `resources/views/`, `app/Actions/` and `app/Http/Controllers/` returns nothing, so the field has **no writer at all** and not merely no form. That is the state ADR-0010 authorized and it says so itself: ":63 Nothing enforces D-260's fixity yet... there is no writer to violate it," and ":67 The column is storage for a screen that does not exist. It is justified by D-268's finding... and it stays justified only while that screen is being built." `AGENTS.md` requires every new control to cite a requirement, and PRD §6.3 caps inheritance at two parent references with the engine banned, so the screen waits for a story rather than the column being deleted to match the absence of one. ADR-0010's own removal condition is the thing to watch: if Legacy Select is dropped from the design, the same ADR says the column should be removed rather than left as an empty bag.
4. **Status lifecycle not closable in UI.** *Resolved 2026-10-04.* A run used to be able to enter as Active/Completed/Retired and never leave it: both update forms carried `status` hidden purely to satisfy the shared request's `required` rule, so retiring a finished run meant editing the database by hand. The run page now has its own PUT form with a select over `\App\Enums\RunStatus::cases()`, so all three transitions and the way back are made on screen. The server rule is unchanged and predates this (`StoreTrainingRunRequest:52`, `Rule::enum(RunStatus::class)`), so a hand-built value outside the enum is still a rejected submission rather than a stored word; that is why the hidden carry-throughs stay, since the request writes every field it is handed. FR-C-4's "creatable, editable, and deletable through the web UI" now holds for status as it does for turns (§7-2). *Was: UX Gap; Unresolved Requirement.*
5. **Preferences ship without a control (US-11).** `preferences` table + server-side theme render exist; no theme toggle or failure-estimate toggle anywhere in `resources/views/` (audit O-1 confirms "the dark theme ships with no way to select it"). `failure_estimate` key exists only in a schema test. *Referenced but Missing; Partially Implemented story.*
   **Resolved 2026-10-04, with one half still blocked.** `GET /preferences` + `PUT /preferences` (SCR-SYS-002) write the two authorized keys, validated by `UpdatePreferenceRequest`, which refuses an unknown *key* as well as an unknown value because `validated()` would otherwise drop it silently. Follow-the-OS is stored as the absence of a row, which is what the theme composer already resolves. The control lives on its own screen behind a sixth nav link, not in the nav: a form in `components/layout` precedes every page's own form in document order and broke six page-wide control-count tests. `failure_estimate` persists and reads back and **changes no calculation**: ADR-0001 §3 records that no source publishes a failure curve and requires any number to print its formula beside it, so there is nothing honest to render. The screen says so rather than offering a silent switch. That display half stays blocked on a sourced model, not on this app.
6. **Scenario panels without capture (Unity Cup).** Team-rank gauge, spirit-burst roster, team-race panel render explanations; the only capture is `circles`/`placement` on the race form; the resource strip prints `N/A` with no entry path (audit R-2, O-6/O-7). *Implementation Gap blocked on a schema proposal with PRD citation (owner backlog item 2).*
   **Blocked item discharged, still Deferred 2026-10-04.** The schema proposal the gap was waiting on now exists: `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md`, section `## unity-cup-capture.md`. It separates the four figures into three per-turn readings needing columns on `turn_entries` (team rank, league position, burst counts, on the `energy`/`fans` precedent) and the team-race rounds needing **rows** in `scenario_slots` rather than any schema at all, since `circles` and `placement` already exist and `circles` is already gated to `kind = team_race`. It cites US-10 for the slot kind, ADR-0003 R3 for provenance, PRD §6.11 and §6.3 for what stays unbuilt, and reads the value vocabulary out of `config/scenarios.php:119-146` instead of inventing it. It writes no migration and closes nothing: the six open questions there, led by "is there a PRD story for this at all", are the owner's, and ADR-0010's precedent says a column that outlives its screen should be removed rather than kept.
   **Dated note 2026-10-08 (documentation-sync pass):** E3 landed the read-only Unity Cup panel (`SCR-CAR-021`) on 2026-10-07, which changed the gap's display half and not its capture half. The panel's own gap list records "no write path for team rank or burst states"; the resource strip still prints `N/A` with no entry path; and the capture proposal this item names is still the owner's, with none of its six open questions answered by a Phase E slice — E3's slice plan (plan §9.1) records which of them E3 answered, which is none of the schema ones, and the register records the reading in the RACE-AND-SLICE-RESEARCH.md note this pass appends beside §unity-cup-capture.md.
7. **Race calendar data unscoped.** `race_catalog_slots` seeded 410 rows with `scenario_key` on 4; calendar/picker cannot honestly scope years/scenarios (audit O-5 corrected, R-7; `TrainingRunController::raceSlotsFor` comment says empty-by-design until fetch lands, KI-11). *Implementation Gap, data-first.*
   **Deferred with reason 2026-10-04. No code change, as the gap is data.** Scope is blocked on scenario-tagged catalogue rows, not on a screen: the table holds 410 races and four carry a `scenario_key`, so `forScenario()` honestly returns nothing for the other 406 and the picker is right to look thin. Two corrections to this line while I am in it. First, the citation is stale: `KI-11` is **closed** in the committed register (`git show HEAD:KNOWN-ISSUES.md`, the Slice 13 status line: "18 resolved/closed (KI-1–9, KI-11–14...)"), while `TrainingRunController.php:208` still says "Empty by design until the fetch engine lands (KI-11)". The comment's premise is also superseded, since the fetch did land and filled 410 rows. Which text is corrected, and whether the comment moves to the open fetch work or to a new KI, is the owner's call and no code in this dispatch touches it. Second, a measured blocker on top of the scoping: `migrate --seed` on a fresh in-memory database reports `gametora-race-catalog: FAILED (table race_catalog_slots has no column named export_slot_id) — skipped` and **exits 0** (audit F-2), because `GametoraRaceCatalogParser.php:163` emits `export_slot_id`, no migration defines it, and `SourceDocumentSeeder.php:82-90` swallows the failure. Until that column is settled, no source refresh can add the scenario scoping this gap is waiting for.
8. **Error-state coverage asymmetry.** Custom 404 view only; no 500/419 views; framework default pages are off-theme (G-20 first-paint rule holds only for shipped screens). *Documentation/UX Gap, minor.*
   **Resolved 2026-10-04.** `resources/views/errors/419.blade.php` and `errors/500.blade.php` join the 404. 419 reuses `x-layout`, since the database is reachable for a token mismatch. 500 is a standalone document on purpose: the layout's theme composer reads the stored theme from `preferences`, and a page that queries is the wrong page to show when the database is what failed, so it renders the documented theme order's remaining term (the OS fallback, inline in the head). `ErrorPageViewsTest` proves the zero-query claim by counting queries while rendering 500 and then rendering the layout through 419 in the same window. No test read these views before; `grep errors.404 tests/` was empty.
9. **Facet-contract inconsistency across the three filter surfaces.** Catalog *ignores* unknown `status`; Skills/Support cards *refuse and redirect* (with documented reasoning on both sides). Also `pageSize` is exposed/clamped on catalog+skills but a fixed constant on support cards. Defensible per-screen, but the tool has two contracts for one idiom. *Consistency Gap; decision owed.*
   **Decision recorded 2026-10-04: `ADR-0018`.** One contract: a facet value outside the accepted set is refused and lands on that screen's canonical route, and `pageSize` is honoured and clamped to 1..100 (default 25) on all three, through `App\Services\PageSize`. Each surface keeps its own vocabulary because the vocabularies genuinely differ. The ADR records both previous arguments rather than overruling them, including why "redirect to the current URL minus the bad key" was rejected (the landing page must be one the picker can produce). Two follow-ups are named there rather than done: `SkillController.php:47` still carries its own copy of the clamp because a sibling session has that file uncommitted, and the three `/api/v1` copies stay put because §7-9 is about the filter surfaces. `ADR-0018` also corrects a claim inside the refusing side: skills redirected to `previous()`, which is referer-dependent, so §7-9's "Skills/Support cards refuse and redirect" described two different redirect targets.
10. **Search folding ceiling (catalog).** Alias/card-title folding cannot handle diacritics or width variants on SQLite (documented with the upgrade path = normalized columns; a schema change). Skills search avoids it by matching stored `match_key`. *Unresolved Requirement.*
   **Unchanged 2026-10-04, as instructed.** The documented upgrade path stays: it needs a schema change, which this dispatch bars, and the ceiling is disclosed in the code rather than hidden.
11. **Stale copy risk in commands naming.** Four screens print artisan command strings verbatim in empty states (`uma:fetch gametora-skills`, `gametora-support-cards`, `gametora-character-cards`, `gametora-character-profiles`), and README still says "Three [sources] are declared today" while `config/uma.php` declares the full set. The UI copy matches config; the README does not. *Documentation Gap.*
   **Resolved 2026-10-04.** Verified first: all four command strings the views print name a key that exists in `config/uma.sources`, and all four sources are declared. The README's count claim is gone rather than corrected to a new number, because correcting it re-creates the same line for the next source to break; the sibling session's README restructure, still uncommitted in this worktree, went further and lists all seven. The durable part is `EmptyStateCommandNamesTest`, which sweeps every Blade view for `uma:fetch <source>` and checks each token against the declared set, checks the README's source names the same way, and checks the Web surface table's paths against the registered routes. It has floors on all three sweeps, so it cannot pass by finding nothing.
12. **Run page carries an open adversarial review.** `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` verdict "Fail on the run page" (2026-10-03) with a still-open list (O-3 total-vs-delta semantics, O-8 deck tiles/reset, O-11 skills panel design, O-12 goals, Infirmary/Races choices, contested Rest figure). The screen is implemented; the review is not closed. This spec records both, per Step 3, rather than declaring either done. *Mixed; tracked in the audit's own backlog.*
   **Unchanged 2026-10-04, and not closed by this work.** Three of the four code items touched the run page (turn rows, status select, nav count), and none of them is an audit item; the verdict and its open list stand as the audit wrote them. Per the dispatch's hard rule no verdict in that file was edited and no open item was marked closed.
   **Dated re-read 2026-10-08 (documentation-sync pass):** the audit section now carries a per-item tree verdict appended at its own "Still open" ledger. On this tree, O-2, O-4, R-5 and R-8 are closed by the audit's own Part 7 commits and the A4b port; O-3's wording and O-12's run-page goals panel are closed with the modelling and per-trainee-goal halves recorded as open (`KI-34`); O-8's never-closing bar is gone from `DeckPanel.vue` while the per-card state half stays gated (`ADR-0014`); O-11, O-5/R-7, R-2/R-3 and the Infirmary/Races choices remain open as recorded. The `Fail` verdict itself was revised by Part 7 and measured a page A4b replaced; neither this item nor the audit marks it closed — the verdict is a 2026-10-03 statement about the Blade page that no longer exists, and the audit's own re-read says so in place.
13. **Orphaned library components.** `run-header`, `deck-editor`, `energy-gauge` (used only inside `run-header`), `app-button`, `advisory-row`, `capsule-header`, `support-card-rail`, `grade-badge` are referenced only by `FrontendComponentLibraryTest` (and compiled view cache) since `/design-preview` was deleted. They are exercised but mounted on no screen. *Consistency Gap (dead surface kept alive by tests); removal or re-mounting is a decision, not this document's.*
   **Status: partly Resolved 2026-10-04 (app-button mounted, two components deleted); five Deferred with reason, listed below.** Checked against the tree after items B to D, and none of the eight had gained a mount site: every `<x-…>` tag on every page is enumerated in the commit report, and the new turn editor, status form and preferences screen are inline markup. `app-button` is now mounted on three submit controls, and its `secondary` variant renders the exact class list two of them hand-carried. `advisory-row` and `support-card-rail` are deleted with their dataset entries and their two dedicated cases: zero call sites, and neither is named by DESIGN.md's three motif/energy/grade bullets.
   Kept and still owed to the owner: `run-header` and `energy-gauge` (coupled, one cannot go without the other's tests breaking; `energy-gauge` is also the only consumer of the `bg-lattice` utility, and the token-pruning gate that would notice self-skips with no browser package installed), `capsule-header` and `grade-badge` (both artifacts of open design rulings, in a file a sibling session is editing now), and `deck-editor` (the nearest existing thing to O-8's six card tiles, and O-8 is mid-flight at `b21f356`). Worth correcting in this line: `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md` names **none** of the eight components, so a caution about O-8 depending on them is a near-neighbour risk, not a documented dependency.
   **Further resolved 2026-10-04, and the two open design rulings it was waiting on are now closed in code.** The reason this item held `capsule-header` and `grade-badge` was that both were waiting on a ruling about how they render, not about whether they render: DESIGN.md §2.1 and §2.3 had already decided the treatment (grade fill keyed on the base letter with the modifier stripped, nine tint families; capsule chrome fill with the lattice bleed). The surfaces were simply still carrying their own copies. `capsule-header` is now mounted on all eight panels that hand-copied its div, and `grade-badge` now owns the grade fill in `stat-band`, which had its own nine-letter map and its own badge span. Nothing about either ruling changed; what changed is that the ruled implementation is the only one left. `FrontendComponentLibraryTest` asserts both visuals are defined in exactly one view, so a second copy fails the suite. Remaining on this item: `run-header`, `energy-gauge` (coupled, and the unreachable one is the one whose hue treatment §2.2 rules not material) and `deck-editor` (still gated on the per-card state decision PRD §6.9 and US-12 exclude). Reachability across the 24 remaining components: 22 from a route, those two with no call site at all.
   **Closed by deletion 2026-10-05 (slice B1, `0ea8d43`), dated note appended by the documentation-sync pass 2026-10-08.** The three components this item's last line kept "remaining" — `run-header`, `energy-gauge` and `deck-editor` — are gone from the tree: B1 deleted the zero-consumer Blade set (`app-button`, `capsule-header`, `deck-editor`, `energy-gauge`, `grade-badge`, `grade-point-meter`, `guided-step`, `race-calendar`, `resource-strip`, `run-header`) and `resources/views/components/` itself, and the Vue ports replaced them on the run page and the deck surfaces (`frontend-development-plan.md` §4's B1 row records the deletion). The `FrontendComponentLibraryTest` guard the item names was repointed at the Vue equivalents in the same pass. `grep -rn "<x-" resources/views` is zero on this tree, so the dead-surface question this item opened is closed by deletion, not by adoption — the outcome the plan's B1 section chose and the item's own "removal or re-mounting is a decision" allowed.
14. **Terminology drift caught and ruled.** `Wisdom/Motivation` (JP-wiki English) vs `Wit/Mood` (client words) is gated by `tools/lore.php` and the lang file's terms map; UI shows only client words. Recorded so a future reader does not "fix" the lang map's JP-side entries. *Consistency, resolved.*
   **Unchanged 2026-10-04; mechanism re-verified and it holds as written.** The ruling stands and was followed: the new turn-row editor, status select and preferences screen print Wit, Mood and the five client mood strings, and no JP-wiki gloss entered any of them. Re-checking what enforces it found both halves of this sentence true. `tools/lore.php:64` carries `wisdom|motivation` (with `strength|endurance|luck|agility|charisma`, and `gacha|jewel|factor`) in the pattern scanned over the app paths, and `lang/en/uma.php:70-90` is the terms map that states the Global label, `'card_wit' => 'Wit'`, with `:78-79` recording that 賢さ keys as `intelligence` in the export while the player-facing word is Wit. That is the JP-side entry the dispatch forbids editing, and it was not touched. `AGENTS.md`'s "Practical notes" line describes the same list from the other side ("the Global client terminology (`Wisdom` for Wit, `Motivation` for Mood...)"), which reads as though the wiki glosses are the client words; §7-14 and this dispatch both say the opposite, and the rendered copy plus the lang file agree with §7-14. The wording in `AGENTS.md` and in this item's first sentence is what needs the owner's ruling; neither file was edited here, and nothing in `tools/lore.php` or the lang map was touched.
15. **Import cannot carry skills/deck/races.** Stated on-screen; no story requests the wider format. *Unresolved Requirement, acceptable.*
   **Unchanged 2026-10-04, as instructed.** `ADR-0018`'s sibling concern, the turn edit path, does not touch the import: `runs.import.*` keeps its own request and its own column list, and `HistoricalRunImportTest` passes unchanged.
16. **No screen places artwork.** `ADR-0021` (2026-10-05) authorizes a local mirror of id-addressable third-party game art, and `DESIGN.md` §4.7 fixes how a slot behaves; no screen in this spec lists an image slot, and `grep -rn "<img" resources/views` returns zero on this tree, so nothing in the product renders a picture. *Unresolved Requirement, acceptable; the open decision is PRD OQ-6.* **Superseded in part 2026-10-05**; the sentence above is preserved as written and is false for four screens, as recorded below.
   **Resolved on the ported surfaces 2026-10-05; two candidates still do not carry a slot.**
`uma:fetch-art` is the mechanism and it was already recorded; this entry records the read half,
`artwork.show` plus `ArtworkMirror::url()` and `ArtworkSlot.vue`. **Placed, matching
`design-2.0` §45a:** SCR-CAT-001's trainee card header (`size-12`) and its costume-form rows
(`size-10`), SCR-CAT-002 section 1 Identity (`size-16`, decorative, no anchor), SCR-SUP-001's card
rows (`size-12`, clickable to the card's own page) and SCR-SUP-002's header (`size-16`, no anchor).
A miss renders the text-only row those screens shipped before, so **no state table above changes**
and the four-state collapse recorded below still holds.

**The slots are Vue, and the Blade components this entry previously named are gone.** `x-character-
portrait` and `x-support-thumb` were built for the three Blade screens the artwork work first
targeted and deleted the same day, because the A1 to A3 ports retired `catalog/show.blade.php` and
both support-card Blade views and those components had no other call site
(`frontend-development-plan.md` §5.1 step 8 deletes a shared component at zero call sites).
`ArtworkSlot.vue` replaced them across all four screens. **`grep -rn "<img" resources/views`
returning zero is therefore correct again**, not the gap this item's first sentence describes: the
`<img>` elements now come from `.vue` files. That sentence is preserved above as written and is
false for four screens in a second way as well.

**Not placed, and each for a stated reason rather than by omission.** The pre-run Legacy Select widget
cannot host a frame: SCR-RUN-CREATE's trainee picker is a native `<select>` whose `<option>` content
model is text, plus a client-rendered combobox listbox, so there is no row to put an image in; wiring
the combobox instead is a TypeScript slice with its own browser-spec cost, and it is deferred rather
than refused. Skill rows remain unreachable for the original reason: `skills` stores no `iconid`
column, so the 125 distinct ids live only in the committed dataset and mirroring them would need a
migration of its own (`ADR-0021`'s Verification records the finding).

**One open item rides with the placement, and one gap is closed.** Open: the catalog index resolves a
trainee's header portrait from her top-rarity form while the detail page resolves it from the active
or first form, so one multi-form trainee can show two different portraits across the two screens; that
is a placement decision for the owner, not something either screen should assume. Closed: the Blade
components' single `decorative` flag, which blanked the image `alt` and the anchor `aria-label`
together and so could not express a decorative image inside a named link, nor a no-action slot with no
anchor at all. `ArtworkSlot.vue` takes `alt` and `href`/`linkLabel` as separate props, which is what
lets the three no-action slots carry `alt=""` without leaving a nameless focus target that failed
WCAG 2.2 AA 4.1.2. `DESIGN.md` §4.7's fifth bullet now records the resolution rather than the gap.
Neither the open item nor the closure changes a state table. **PRD OQ-6 stays open**: the placement
question has an answered subset, not an answer, and the run-create and skill-row remainder is what is
left.
   **Unchanged 2026-10-05, and the build did not change it.** `uma:fetch-art` is the mechanism, not the placement: it fills `storage/app/private/artwork/` and writes `artwork/manifest.json`, and no screen reads either yet. If the owner answers OQ-6 yes, the candidate slots are SCR-CAT-001's trainee card header and its costume-form rows, SCR-CAT-002 section 1 (Identity), SCR-SUP-001's card rows and SCR-SUP-002's header, plus the pre-run Legacy Select widget. A skill row's icon is **not** reachable the same way: `skills` stores no `iconid` column, so the 125 distinct ids live only in the committed dataset and mirroring them would need a migration of its own (`ADR-0021`'s Verification records the finding). Four states a slot can be in, and three of them render identically: mirrored-and-present, never mirrored, gone upstream, and unreadable on disk, with everything after the first falling back to the text-only row that ships today, because the mirror is partial by nature and a broken frame would advertise a defect the tool does not have (`ADR-0021` Decision 5). No state table above changes until a slot exists, and no per-screen empty state is added on the strength of a mirror nobody has filled.
   **Corrected 2026-10-05: the build did change it, and the mirror is now filled. Gap §7-16 status: resolved in part.** Four of the five candidate slots named above are live, on the ported Vue screens rather than the Blade views this entry assumed: SCR-CAT-001's trainee card header and its costume-form rows, SCR-CAT-002 section 1 (Identity), SCR-SUP-001's card rows and SCR-SUP-002's header. `resources/js/components/ArtworkSlot.vue` is the one owner of the slot contract. The first live `uma:fetch-art` pass resolved all 665 ids into 45 MB with zero unresolved, so the four states above are no longer hypothetical: mirrored-and-present renders a frame, and the other three collapse to one render. That render is now specified rather than assumed — `ArtworkSlot` paints nothing, and a row reserves a transparent cell so its label holds one x whether or not the file exists (`DESIGN.md` §4.7). Still open under OQ-6: the pre-run Legacy Select widget, which cannot host a frame because its picker is a native `<select>` whose `<option>` content model is text, and skill icons, which still need the `skills.iconid` migration this entry already named. The per-screen state tables above still do not change, because an absent frame is not a new state of the screen — it is the same text row, held in place.

## 8. Source of Truth

| Question                                                      | Authority                                                                                                                                                                                                       | Corroboration                                                                                                                             |
| ------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| Which screens exist, their URLs and names                     | `routes/web.php` (+ `routes/api.php`)                                                                                                                                                                           | README table (stale on `/design-preview`, §7-1)                                                                                           |
| What a screen shows and how it got there                      | Controller methods                                                                                                                                                                                              | View files; docblock citations to PRD/ADR/KI                                                                                              |
| Validation and business refusal                               | `app/Http/Requests/*` (single owner per boundary)                                                                                                                                                               | Model saving guards behind them (deck/circles/shop); `ScenarioCaps` as the one ceiling arithmetic owner shared by form, band, and preview |
| Authorization                                                 | There is none by design: `ARCHITECTURE.md` §8, NFR-1; every `authorize(): true` is the evidence                                                                                                                 | Nested ownership 404s in `TrainingRunController`                                                                                          |
| Which panels a scenario shows                                 | `config/scenarios.php` composition matrix (D-240: the ONLY place scenario names enter the layout path)                                                                                                          | Panel components self-gate; `GoalPanelsOnRunDetailTest` absence checks                                                                    |
| Displayed label vocabulary                                    | `lang/en/uma.php` (enum labels keyed by case name) + client-captured strings per audit I-3/Part 5                                                                                                               | `EnumLabelTest`; lore gate `composer lore`/`tools/lore.php`                                                                               |
| Visual system, tokens, contrast, motion                       | `DESIGN.md` (+ corpus `docs/research-scratch/DESIGN-CORPUS.md`)                                                                                                                                                 | `DesignTokensTest`, `FlashBannerTokensTest`, gate G-18/G-19/G-20                                                                          |
| State coverage duty (empty/loading/error)                     | `ARCHITECTURE.md` §7 + ADR-0007 (server-rendered scope)                                                                                                                                                         | Per-screen empty states as documented above                                                                                               |
| Artwork: whether a file may be shown, and how absence behaves | `ADR-0021` (Decisions 3, 5, 6) + `DESIGN.md` §4.7                                                                                                                                                               | `PRD.md` OQ-6 for placement on a screen, open; `PRD.md` §6.13 for uploads, still cut; §7-16 above                                         |
| Automated behavioral expectations                             | `tests/Feature/*` Pest suite (HTTP via `Http::fake` fixtures; no network)                                                                                                                                       | Test names cited per screen                                                                                                               |
| Known defects and rulings                                     | `KNOWN-ISSUES.md` (live register; history in `docs/research-scratch/AUDIT-AND-VERIFICATION.md`)                                                                                                                 | Inline KI references above                                                                                                                |
| Screen-level UX findings & decisions owed                     | `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## UIX-AUDIT-TRAINING-RUNS.md`                                                                                                                      | Part 7 landed/closed ledger                                                                                                               |
| When sources disagree                                         | `CONSTRAINTS.md` > GATE-REGISTRY > ADRs > DESIGN > slice plans (`AGENTS.md` precedence; `ARCHITECTURE.md` over its digest; `PRD.md` is product truth) — conflicts are recorded here (§7), not silently resolved |                                                                                                                                           |

## 9. Change Log

| Date         | Change                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  | Reason                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| 2026-10-03   | Initial screen specification                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | Repository baseline                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| 2026-10-04   | Section 7 carries a status line for every gap (1 to 15). Resolved: 1, 2, 4, 5 (display half still open), 8, 11, 13 (partly). Deferred with reason: 3, 6, 7, 10, 15. Decision recorded: 9 (`ADR-0018`). Unchanged and not closed: 12, 14. New screen SCR-SYS-002 (Preferences); nav shell now six links; `runs.turns.update`/`runs.turns.destroy` no longer UI-less; SCR-SYS-001's "only one custom error view" state corrected.                                                                                                         | Screen-system gaps dispatch, items A to G. No verdict in `docs/UIX-AUDIT-TRAINING-RUNS.md` was edited and no open audit item was closed.                                                                                                                                                                                                                                                                                                                                                                                       |
| 2026-10-04   | Trainer Desk 2.0 design target filed for reference: `docs/proposals/screen-spec-2.0.md` (screen set) and `docs/proposals/design-2.0.md` (visual system). These are the Inertia/Vue rewrite target (`ADR-0020` §1), **reference-only** — this document still describes the shipped Blade app. The target's deferred-computation sections (race win-probability, per-training stat-yield and numeric failure, inheritance computation, shop recommendation) carry a governance banner naming the holding ADR and are not authorization.   | Owner-supplied design brief, filed as the 2.0 target. No shipped screen changed.                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| 2026-10-05   | Gap §7-16 added, and one §8 authority row for artwork. No screen section, state table or workflow changed.                                                                                                                                                                                                                                                                                                                                                                                                                              | `ADR-0021` authorized sourced artwork on 2026-10-05 while the placement question stays open (PRD OQ-6). The screen system records an authorization it has not yet implemented rather than letting the ADR be the only place a future build looks.                                                                                                                                                                                                                                                                              |
| 2026-10-06   | New area `SCR-CAR-*` and screen `SCR-CAR-001` (Dashboard), with the §4 section and its empty / loading / error state table. §2's nav-shell paragraph corrected: it described `resources/views/components/layout.blade.php`, deleted in slice B1, and claimed `/` redirects to `/training-runs`, which the Dashboard row makes false. No other screen section, state table or workflow changed.                                                                                                                                          | Slice D1 enriched the Dashboard (`docs/proposals/frontend-development-plan.md` §8). Plan §11 risk 5 requires Phase D screens to get their `SCR-*` entries with their first slice, and §10 requires the screen's state table to agree with the view.                                                                                                                                                                                                                                                                            |
| 2026-10-05   | §7-16 marked **superseded in part**, with a dated correction appended under it rather than the original sentence rewritten. No screen section, state table, workflow or §8 authority row changed. The correction names the four placed screens with their recorded geometry, states the pre-run Legacy Select as unplaceable with its reason, keeps the skill-row deferral, and carries forward two open items (the index/detail portrait-source drift, and the components' single `decorative` flag).                                  | The read half of `ADR-0021` landed, so §7-16's own claim that `grep -rn "<img" resources/views` returns zero no longer holds. `AGENTS.md` §16 says a stale written rule is reported rather than edited to match the code; the sentence is preserved verbatim and marked false-for-four-screens, with the correction dated beside it, which is the same shape §7-4 and §7-14 already use for their own closures. PRD OQ-6 stays **open**: a subset of placements is answered, not the question.                                 |
| 2026-10-06   | New screens `SCR-CAR-003` (Trainee Selection) and `SCR-CAR-004` (Trainee Profile) with their §3 rows, §4 sections and empty/loading/error state tables, inserted before §4.15. `SCR-CAR-002`'s status cell corrected from "steps 2 to 6" to "steps 3 to 6", because step 2 landed with D3. No other screen section, state table, workflow or §8 authority row changed.                                                                                                                                                                  | Slice D3 built the wizard's second step (`docs/proposals/frontend-development-plan.md` §8). Plan §11 risk 5 requires Phase D screens to get their `SCR-*` entries with their first slice, and §10 requires the screen's state table to agree with the view. The two screens omit the brief's growth-rate, scenario-suitability, career-goal, hint and evolution items because no column or table holds them, which the Gaps sections record with citations.                                                                    |
| 2026-10-06   | New screen `SCR-CAR-005` (Build Target, wizard step 3) with its §3 row, §4 section and empty/loading/error state table, inserted before §4.15. The step-nav statements that step 3 was a named absence are corrected in place: `SCR-CAR-002`'s status cell to "steps 4 to 6", its §4 "Unbuilt steps" row, and `SCR-CAR-003`'s §4 "Unbuilt steps" row and Status line. No other screen section, state table, workflow or §8 authority row changed.                                                                                       | Slice D4 built the wizard's third step (`docs/proposals/frontend-development-plan.md` §8), including the shared `ProvenanceBadge` whose four states come from `design-2.0` §49. Plan §11 risk 5 requires Phase D screens to get their `SCR-*` entries with their first slice, and §10 requires the screen's state table to agree with the view. The brief's fifth purpose, risk tolerance and per-skill marks are omitted because the stored payload's key set is closed, which the Gaps section records as owner questions.   |
| 2026-10-06   | Numbering settled for the career screens past the first three steps, and the two run-scoped builder screens that were claimed in prose and code but had no row are registered. `SCR-CAR-006` (Legacy Lab) and `SCR-CAR-007` (Support Deck Builder) gain their §3 rows and §4 sections, inserted before §4.15; `SCR-CAR-008` (wizard step 4), `SCR-CAR-009` (wizard step 5) and `SCR-CAR-010` (Preflight) were filed by a sibling pass on the same day. No other screen section, state table or workflow changed.                        | The collision the plan's §4.1 item 1 named: `TraineeSelectController` and the `SetupDraft` prose already read the run's builder as "SCR-CAR-006", and `Support/Builder.vue` and `support-deck.spec.ts` already read the deck builder as "SCR-CAR-007", but the registry had no rows for either, while steps 4 and 5 were about to take numbers. The settlement keeps the committed claims: the two builders own 006/007, the wizard's ancestry and deck take 008/009, and Preflight takes 010.                                 |
| 2026-10-07   | New screen `SCR-CAR-015` (Inheritance Event, SCREEN-013) with its §3 row and §4 section (states, data, inputs, actions, validation, accessibility, responsive, navigation, dependencies, implementation references), inserted before §4.15. The Cockpit action grid's Inheritance entry (gap §4-1656 item 1) updated to link to `runs.inheritance` (`SCR-CAR-015`) instead of `legacy.builder`. The `TurnEventType` enum gained the `Inheritance` case. `LegacySelectionPayload` gained `sparkKindLabels()`. No other screen section, state table, workflow or §8 authority row changed. | Slice D12 built the Inheritance Event record-only screen (`docs/proposals/frontend-development-plan.md` §8). The predicted section shows sourced Spark probabilities and star-roll odds; the observed section records Trainer-entered inspiration outcomes via TurnEvent. The "expected inheritance" renders `N/A` with a `title` citing ADR-0020 §3. The three inheritance milestones (turns 1, 31, 55) come from UMAMUSUME_REFERENCE.md §1.5.1. |
| 2026-10-07   | New screen `SCR-CAR-023` (Scenario Race Planner, SCREEN-017) with its §3 row and §4 section (the four group rules, the comparison and alignment tables, the held figures, the states, the gaps, the implementation references), inserted before §5. `SCR-CAR-013`'s §4 section gained the "Plan the whole calendar" door, and its props gained `planner_url`. The ten-field race fact list moved from `RaceDecisionController` to `App\Services\RaceFacts`, so the two screens that print it share one owner. No other screen section, state table, workflow or §8 authority row changed. | Slice E5 built the planner (`docs/proposals/frontend-development-plan.md` §9.5). Plan §11 risk 5 requires Phase E screens to get their `SCR-*` entries with their first slice, and §10 requires the screen's state table to agree with the view. The brief's win probability, expected risk, recommendation block and "no deadline will be missed" sentence are held or unsourced, which the section records with citations; the rival group has no source at all, and says so. |
| 2026-10-07   | New screen `SCR-CAR-024` (Our Grand Concert baseline strip, the brief's `SCR-017`) with its §3 row and §4 section (purpose, location, layout, provenance, states, gaps, implementation references), inserted before §5. `SCR-CAR-019`'s status cell gained the dated note that the badge's partial arm is now reachable and which key drives it. No other screen section, state table, workflow or §8 authority row changed. | Slice E6 built the strip the panel region draws when a scenario composes no panel (`docs/proposals/frontend-development-plan.md` §9 E6). Plan §11 risk 5 requires Phase E screens to get their `SCR-*` entries with their first slice, and §10 requires the screen's state table to agree with the view. The dispatch brief's premises were stale in three places, recorded in the slice plan: `documented` reads `true`, `docs/scenarios/07-grand-concert.md` is a sourced guide rather than a stub, and the badge key was the owner's ruling of 2026-10-07 (plan §4.1 item 6), which this slice applies. The row is `SCR-CAR-024` because `SCR-CAR-023` is E5's Scenario Race Planner. |
| 2026-10-08   | Documentation-sync pass: §7 gap 13 gained its B1 deletion close-out, §7 gap 6 gained the E3 dated note, §7 gap 12 gained the audit re-read summary, and `SCR-RUN-003`'s Gaps list gained dated notes re-reading each item against the tree. No §3 row, §4 section state table or workflow changed; the screen set was already complete with `SCR-CAR-024` (E6) filed 2026-10-07, so this pass records statuses, not screens. | Owner asked for the documentation set to be brought into agreement with the completion of Phases A–E. The gap statuses this pass touched are the ones the slice landings changed; each original sentence is preserved and a dated note is appended beside it, per this file's established pattern. This pass did not run `npm run test:browser`; the browser-gate claims it quotes are the slices' own. |
