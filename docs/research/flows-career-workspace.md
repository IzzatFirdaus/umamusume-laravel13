# Flows — Career Workspace (run-scoped)

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `routes/web.php`, `app/Http/Controllers/TrainingRunController.php`, `app/Http/Controllers/Career/*.php`, `app/Http/Requests/*.php`, `app/Models/TrainingRun.php`, `app/Services/**`.

Every screen here requires `{run}` and renders inside `CareerLayout.vue`. The nine-section stepper (`CareerSectionNav.vue`) addresses: cockpit, training, races, skills, events, inheritance, deck, timeline, result.

---

## 1. Cockpit

`GET /training-runs/{run}/cockpit` (`runs.cockpit`), `CockpitController::show` (`:235-330`).

### 1.1 Load strategy

`$needsEvents` decides whether `turnEvents` is hydrated at all: only when the scenario composes `team_rank_ladder`, or the run accepts Performance observations, or the scenario's widget list carries `spirit_bursts`. A URA or Grand Concert cockpit must not hydrate every event row to discard it. Base eager load: `umamusume`, `characterCard`, `turnEntries`, `raceEntries.{raceCatalogSlot,scenarioSlot,turnEntry}`, `deckSlots.supportCard`.

`$position = $run->careerPosition()` is built **once** because three props read it and a run with no stored position derives one with a `max(turn)` query.

`$team = $this->team->teamSection($run)` is also built once: the header's rank and the scenario section's team panel read the same pass. `burst_countdown` is added onto it for Unity Cup.

### 1.2 Props

| Prop | Contents |
| --- | --- |
| `run` | `id`, `trainee`, `trainee_ja`, `status`, `status_label`, `umamusume_id`, `scenario`, `scenario_label` (`No scenario set` when none — never the baseline's name), and every door URL: `run_url`, `skills_url`, `settings_url`, `confirm_delete_url`, `index_url`, `timeline_url`, `result_url`, `training_url`, `deck_url`, `update_url`, `destroy_url`, `export_csv_url`, `export_json_url`, `status_labels`, `scenarios`, `current_objective_index` |
| `header` | Trainee identity, status, position, team rank |
| `careerPosition` | `CareerPosition::toArray()` or null |
| `hasImportedPosition` | bool |
| `scenarioCountdown` | A turns-until-milestone figure a **snapshot** names on entry; null for every run that derives its own position. Read in the Cockpit header. `R6-34`'s "dead pipe" is **half right** — see below |
| `gradeObjectives` | Trackblazer objective ladder (`TrainingRun::gradeObjectives()`) |
| `identity` | Rarity, potential level, chosen form, fan ladder position |
| `capComposition` | Entered caps beside the Legacy tree's blue sparks, with the source's non-reconciliation named. **Its scope is entered caps, not the scenario floor** — R6-04's mislabel, see §1.6 |
| `roster` | Derived: the six deck cards' characters. A read-out, no column. **Its derivation is pinned by a test** (`CockpitRosterTest`, two cases) — replacing it with a member roster is a contract change, see §1.7 |
| `state` | Five stat rows `{key, label, current, target, cap}`, plus `energy` (an `energyReading()` result), `mood`, `fans`, `skill_points` |
| `moodEffect`, `moodEffectTable` | From `config('uma.mood_effect')`; the full five-tier table, both columns |
| `actions` | Seven action keys with notes |
| `advisor` | `TrainerAdvisor` output |
| `performance` | Our Grand Concert: five types plus what this run recorded; a non-composing scenario answers `enabled: false` with the same key set, so the page never branches on a scenario name |
| `raceStrip` | `RaceStripBuilder` |
| `team_url` | `runs.team` when the scenario composes `team_rank_ladder`, else null |
| `scenario_detail_url` | `runs.scenario-detail` |
| `scenario` | `ScenarioSectionBuilder` output: `widgets`, `objectives`, `alerts`, `milestones`, `duel`, `pre_final`, `grade`, `shop`, `epithet`, `rival` |
| `correction` | **Sent but unread.** The controller builds the turn-correction block (`correctionSection()`, `:1248-1281`) and `Cockpit.vue:236` declares the prop, but no template reads it: `C6b.6` moved the form to `runs.turns.edit`, reached from the Timeline row. A leftover of the `Career tools` retirement (`06a65aef`). |

### 1.3 Panel gating

Every panel section is composed only for a scenario whose config turns its flag on (`$run->composesPanel('...')`, gate G-33). `config/scenarios.php` is the only place a scenario name enters this path.

### 1.4 `scenarioCountdown`: not a dead pipe, but a pipe with no inlet — verified 2026-11-06

R6-34 called `scenarioCountdown` a dead pipe. Reading the whole path, that is half right, and the half that is wrong matters because it changes what the fix is.

**It is plumbed end to end and it is tested.** `CareerPosition` carries it (`CareerPosition.php:49`), validates it non-negative (`:63`), serialises it (`:147`) and accepts it back from a snapshot payload (`:116`); `CockpitController:277` passes it as a prop; `SnapshotScenarioCountdownTest` has four cases asserting 5, 0, null-from-a-named-position and null-from-a-derived-position. So the read path works and the wire is contracted.

**The inlet is the gap.** `CareerPosition::fromTurnIndex($turnIndex, $countdown)` is the only constructor path the app calls, and every caller passes one argument — `TimelineController:120`, `:211` and `TrainingRun::careerPosition():785` — so `scenarioCountdown` is always null outside a factory-built position in a test. `CreateSnapshotRun` builds its position from `career_year`, `career_month` and `career_phase` and does **not** pass a countdown (`CreateSnapshotRun.php:63-80`), and `StoreTrainingRunRequest` validates no `scenario_countdown` key at all: its only mention of `career_position` is the presence guard at `:188`. So the value can be held and printed, and nothing in the app can produce one.

**The renderer is also absent.** `Cockpit.vue:163` and `:168` declare it in the prop type and **no template reads it**; `TrainingDetail.vue:119` declares `scenario_countdown` inside the `careerPosition` type and reads only `year`, `phase`, `month` and `turn_index` (`:143-154`). Grepping `resources/js/` for any component that renders a countdown returns nothing.

**What the client's own widget is — and the semantic choice the fix has to make.** The career screen prints `5 turn(s) until the Unity Cup` beside the standard objective timer. That counter counts down to the **finals**; the publishers describe a countdown sitting below the objective timer that counts to the next **six-monthly team race**. These are different numbers and the app's `team_race.month_anchors` (`['Late June', 'Late December']`) already carries the data to compute the second. A fix has to name which one the field means before it can fill it: an inlet alone is insufficient, because the two readings disagree about what the number is.

### 1.5 R6-08: the two counters, and which one the client backs

`CockpitController:628-634` renders a **literal** note (`the six blue sparks account for the Guts extra and nothing else`) beside a **derived** list: `capSparkText` in `Cockpit.vue:609-617` over `capComposition.sparks`, which `CockpitController:594-627` collects from **parent and grandparent** sparks. The Inheritance tile's own counter (`InheritanceEvent.vue:413` over `:145-149`) is derived from a narrower scope: parents only. Two facets: a literal describing the source document's tree rather than this run's, and two counters that disagree because one includes grandparent sparks.


**The client settles which counter is right, and it is the wider one — recorded 2026-11-06.** The client's inheritance tree is **six ancestors** — two parents and four grandparents — exposed as one Legacy panel plus one Sparks panel per ancestor, and the recorded run reads six blue sparks across that whole tree (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.5). So `capComposition.sparks`'s parent-plus-grandparent scope matches the client and the Inheritance tile's parents-only counter is the outlier. The fix is therefore to **name the scope on both counters**, not to change the data: the literal beside the cap list describes the source document's own tree rather than this run's, and that sentence is the part that is wrong.

### 1.6 R6-04: the client prints two different ceilings, and the app's labels fold them together — confirmed 2026-11-06

The client keeps two numbers apart that the app's copy conflates, and the frames show both on one screen: the **scenario floor** is 1300 across Speed, Stamina, Power and Guts with Wit at 1800, and the **live per-stat cap** in force is a separate higher value — 1325 / 1322 / 1368 / 1308 / 1800 on the recorded run (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.2). The gap between them is the whole cap-composition question.

The app's naming folds them, with three distinct sites:

- `BuildTarget.vue:525` captions a sum as **`Sum of the five scenario caps`** while the figures it sums are the run's *entered* caps.
- `BuildTarget.vue:410` says a target is "compared against the run's scenario cap", where the comparison is against `ScenarioCaps::forRun()`, which prefers an **entered** ceiling where one exists (`ScenarioCaps.php:15` and `:120`).
- The Cockpit's bars are bounded by the entered ceiling while their caption asserts a scenario ceiling.

This is not a copy nit: `ScenarioCaps` deliberately implements a two-layer answer — entered ceiling where the Trainer stated one, scenario arithmetic otherwise, engine hard cap bounding both (`:120-124`) — and the labels collapse that three-way source into one word. The correction is to print which layer a number came from, which is the same discipline the Inheritance screen already applies when it states `entered` / `scenario` / `not recorded` per cell.

### 1.7 The roster gap, and why it is prior to two other defects — verified 2026-11-06

The client's Unity Cup team is up to **20 members**: the trainee, the deck's supports excluding Pal cards, the four story characters, and further characters who join after each six-monthly race. The Team Info panel reads `20 / 20` and the Team tab lays the roster out as five distance columns with an ACE slot plus two more in each (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.8, §1.9). The app has **no member entity at all**: `roster` is derived from the six `deck_slots` and its own docblock says what it is — `A-0.6: the derived team roster — the six deck cards' characters ... with no stored roster and no column` (`CockpitController.php:638-640`).

**The derivation is tested, which changes the shape of any fix.** `tests/Feature/CockpitRosterTest.php` carries two cases — `test_derives_the_roster_from_the_six_deck_cards` and `test_names_the_absence_when_no_deck_is_equipped` — written against the six-card contract. Replacing the derived roster with a member roster therefore does not just add a feature; it changes a pinned contract, and the second case's named absence is part of it.

**Two defects stand behind this one rather than beside it.** R6-48's team-rank form has nothing to reference because there are no members to derive a rank from (§5), and the finals programme cannot be recorded because the per-distance assignment is a property of the members, not of the run. The `team_race` block already carries the parts that are run-level — five categories including Dirt, the six-month cadence, the three-of-five win condition, the Alarm Clock retry — so what is missing is specifically the roster and each member's distance assignment.

**What the client would let the app hold, if a member entity existed.** Every member carries a full stat line, its own caps, its aptitudes and a skill list readable by holding the member's icon (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.9). The recorded run shows eleven guest R cards at 700-900 caps against the trainee's 1300/1800, so a member's numbers are a different scale from the run's and cannot share a column with them. That is why the gap is a data-model question, not a form.

---

## 2. Recording a turn

### 2.1 Training decision (guided)

`GET /training-runs/{run}/training` (`runs.training`), `TrainingDecisionController::show`.

Props: `run`, `target`, `energy`, `options` (five stat rows), `deck` (`{recorded, slots}`), `advisor`, `write`.

Each stat option carries `{key, choice, recommended, current, target, cap, cap_bonus, deficit, band, reason, energy_after, cost, supports, scenario_effects, published}`. Constants such as `team_training_energy_penalty`, `wit_burst_energy_penalty` and the training adjacency map (`Speed→Power`, `Stamina→Guts`, `Power→Stamina`, `Guts→Speed+Power`, `Wit→Speed`) come from the reference corpus.

`write` carries `{action, turn, moods, extra_choices, outcomes, previous}` — `previous` mirrors the last turn's field names, including the five facility levels, so the form opens on the state the client is still showing rather than on nothing.

**Note (R6-28):** the page carries four `<h2>`s (Disciplines, Other actions, Why this action, Recorded outcome) plus two expanded write regions, and 28 field widgets across them. Six asterisks match the six `required` attributes exactly.

### 2.2 The `StoreTurnEntryRequest` contract

| Field | Rule |
| --- | --- |
| `turn` | required, integer, **min 1 and no maximum**, unique per run (ignoring the edited turn) |
| `speed` `stamina` `power` `guts` `wit` | required, integer, `between:0,{ScenarioCaps::forRun()}` — the run's **own** ceiling, not a flat 1200 |
| `sp` | nullable, integer, min 0 — no upper bound, because no source states one |
| `condition` | nullable, string, max 255 |
| `facility_speed` … `facility_wit` | nullable, integer, 1..5 (the client's five-level ladder; zero would be a level the client does not have) |
| `failure_rate` | nullable, integer, 0..100 |
| `preview_gains` | nullable, `array:speed,stamina,power,guts,wit,sp` — a seventh key is refused, not dropped; values are unconstrained in sign because a preview can show a penalty |
| `energy` | nullable, integer, 0..100 (`ADR-0001`) |
| `energy_state` | nullable, `EnergyState` enum (`exact`, `band`, `unknown`) |
| `energy_band` | nullable, `low|mid|high`, **required exactly when** `energy_state === band` |
| `mood` | nullable, `MoodTier` enum (five client tiers, never free text) |
| `fans` | nullable, integer, min 0 |
| `stage` | nullable, `preview|confirm` |
| `choice` | required when staged, `TurnChoice` enum |
| `outcome` | required when staged, one of `TurnChoice::outcomes()` keys |
| `penalty_kind` | required when the chosen outcome is a failure; `energy|mood|stat` |
| `previewed` | required on `confirm`, `in:1` — a UX guard, not a security control (there is no auth surface) |
| `performance` | nullable, `array:type,delta`; `type` a `PerformanceType`, `delta` integer `not_in:0` |

**The turn bound is asymmetric across the three turn writers, and it is reachable — verified 2026-11-06.** `StoreTurnEntryRequest` (the raw turn write, `runs.turns.store` / `runs.turns.update`) bounds `turn` at **min 1 and no maximum**. The two turn-*linked* writers both cap at 72: `StoreTurnEventRequest.php:38` (`'turn' => ['required','integer','min:1','max:72']`) and `StoreInheritanceEventRequest.php:32` (same rule). So a turn row at 73 or later is accepted by the turn entry and its position is then refused by every reader that goes through the calendar:

- `CareerCalendar::turnIndexToPosition()` throws on a `turnIndex` above `TURNS_PER_CAREER` (`CareerPosition::__construct` throws `A career turn index is 1 to 72; [73] is not.`, pinned by `CareerCalendarTest`).
- `TrainingRun::careerPosition()` therefore returns null above the constant — the derivation declines rather than clamping (`TrainingRun.php:774-782`).
- The Timeline's `total_turns` is `CareerCalendar::TURNS_PER_CAREER` (`TimelineController.php:71`), so a recorded turn 73 has no column on the grid.

The client's own shape is the reason this matters: a career is **72 training turns plus a finale**, and Unity Cup runs a six-turn URA block *after* the Unity Cup Finals at Senior Late December, so the turns a Unity Cup run most needs to record are exactly the ones past 72 (`docs/UMAMUSUME_REFERENCE.md` §1.2.6 records the 72/75/78 convention split). The app has no per-scenario career extent: `config/scenarios.php` carries `training_tables`, `shop`, `team_race` and `finale` blocks but **no finale-length or career-extent field**, and no `career_extent` / `finale_turns` key exists anywhere in `config/` or `app/` (grepped 2026-11-06). The one related constant is `TrainerAdvisor::FINALE_TURNS_AHEAD = 5`, which is a look-ahead window for advice, not a career length. So the asymmetry is not a bug in one validator; it is the missing concept, surfacing at three readers.

`prepareForValidation()` drops empty `preview_gains` members and the whole key when nothing survives, so an untouched disclosure does not store six nulls claiming a reading that was never taken.

### 2.3 Writing a turn

`POST /training-runs/{run}/turns` (`runs.turns.store`), `TrainingRunController::storeTurn` (`:949-1060`).

- A `stage=preview` submit **writes nothing**: it redirects to the Cockpit with the input re-flashed (`previewed=1`). PRG, so refreshing cannot re-issue the POST (audit F-7). No 2.0 surface posts the stage marker any more; the branch stays for a hand-made request.
- Otherwise, one transaction:
  1. `NextDecision::snapshotFor($previousTurn, $run)` is taken **before** the create, so `decision_snapshot` records what the rail was showing when the card was pressed, not the state this turn produced.
  2. `turnEntries()->create($this->turnAttributes($validated) + decision_snapshot)`.
  3. Any existing `Failure` event at this turn number is deleted, whether or not this turn failed — a success at a reused number must shed an orphan.
  4. If the choice's outcome is a failure (`TurnChoice::outcomes()`), a `turn_events` row is written: `event_type = Failure`, `source_name`/`choice_label` from the rail's own label list, `deltas = {penalty_kind, recorded: [...]}`, and an `origin_note` stating the trainer logged it and no source publishes a failure chance.
  5. If `performance` is present, a `Scenario` event carries `PerformancePayload::make(type, delta)->toArray()`. No running total is kept; `turn_entries` stays the record of absolute values.
- Redirect to `runs.cockpit` with `Turn {n} logged.`

`destroyTurn` (`DELETE /training-runs/{run}/turns/{turn}`) deletes the `Failure` event at that number and the row in one transaction. It deliberately leaves `Scenario` events (shop purchases, Performance observations) alone: deleting a turn is not a ruling on a record the turn made.

### 2.4 Correcting a turn

**One door, one write.** The correction form is its own page:

- `GET /training-runs/{run}/turns/{turn}/edit` (`runs.turns.edit`), `editTurn`. `abort_unless($turn->training_run_id === $run->id, 404)`. A turn with no recorded state reads `energy_state = unknown` (the enum's own word for no reading), never a fabricated `exact`. The page carries `energy_states` and `moods` for its selects, and `timeline_url` for the way back.
- It is reached from a **Timeline** row's `edit_url`; the Cockpit's inline correction block was retired with the `Career tools` disclosure (`06a65aef`). `CockpitController` still builds a `correction` prop and `Cockpit.vue` still declares it, but no template reads it — a leftover, not a second door.
- The form `PUT`s to `/training-runs/{run}/turns/{turn}` (`runs.turns.update`) through the **same** `StoreTurnEntryRequest` the guided create uses. `updateTurn` calls `$turn->update($request->validated())`; absent keys are preserved by `validated()`.

`?edit_turn=<id>` still names any logged turn for the controller's `correctionSection()`, but since the block is gone it resolves for nothing a screen renders. An id from another run or a non-numeric value falls back to the latest rather than erroring — the selector is a convenience, not a trust boundary.

---

## 3. Races

| Screen | Route | Controller | Write |
| --- | --- | --- | --- |
| Race decision | `runs.races.decision` (`/{run}/races`) | `RaceDecisionController::show` | `runs.races.store` |
| Race planner | `runs.races.planner` (`/{run}/races/planner`) | `RacePlannerController::show` | `runs.races.store` |

**Race decision** props: `run`, `races` (the run's calendar with per-slot facts, obligations, maiden gates, entries), `deadlines`, `readiness`, `entry`, `planner_url`, `empty`, `entry_mode`, `manual_slots`, `manual_defaults`, `race_statuses`.

**Race planner** props: `run`, `position`, `groups`, `comparison` (nine rows: grade, distance, distance band, surface, fan gate, fan gain, reward, skill points, grade points, shop coins), `alignment` (three rows: distance, surface, style), `held`, `readiness`, `deadline`, `entry`, `empty`. Groups are `mandatory`, `upcoming`, `optional`, `rival`.

**The one write** is `POST /training-runs/{run}/races` (`runs.races.store`), `storeRace`, validated by `StoreRaceEntryRequest`:

- `entry_mode` required (`calendar|manual`).
- `status` required (`RaceEntryStatus`).
- `placement` 1..99, `fans_gain` min 0, `circles` in `0..MAX_CIRCLES`, `objective_index` in `1..MAX_OBJECTIVE_INDEX`.
- `turn_entry_id` validated by a closure — a turn from another run is refused.
- Normalisation: a manual entry nulls `tier`; a calendar entry keeps the slot's own.

There is no second race-write route.

---

## 4. Skills

`GET /training-runs/{run}/skills` (`runs.skills.planner`), `SkillsPlannerController::show`.

Props: `run`, `target`, `coverage`, `purchasable`, `recommended`, `hint_discount` (`HintDiscount::ladder()`), `groups`, `skills_sync_url`, `acquisition_options`, `picker_skills`.

- `coverage` is `SkillSpendCoverage` against the build target's priority list. An **empty** priority list is refused rather than priced (R2-10) — the planner will not answer a question the run never asked.
- `purchasable` rows carry `{id, name, sp_cost, hint_adjusted_cost, rarity, is_gold, affordable}`; `shortfall` is `max(0, total - sp)` and stays null when `sp` is unknown.
- `hint_adjusted_cost` equals `sp_cost` today: no hint level is recorded on an unacquired skill.

**Write:** `POST /training-runs/{run}/skills` (`runs.skills.sync`), `syncSkills`, `StoreRunSkillRequest` (`skills` present, array). Per entry:

1. `Skill::availableOnGlobal()->find(id)` — the same scope the rule applies, so the two cannot disagree about which rows are writable.
2. `setSkillStatus($skill, $status, $turnAcquired, $hintLevel)` (upsert through `syncWithoutDetaching`).
3. `ADR-0029` D4: an `Acquired` skill with a level gets a `skill_levels` row; anything else has its level row deleted.
4. Returns to the **planner**, not the Cockpit: a Trainer posting from there is mid-inventory, and landing elsewhere costs a round trip per skill.

**Copy defect (R6-15):** `SkillsPlanner.vue:590` prints `{{ 'Hint Lvl ' + step.label }}` in the `Client caption` column while `HintDiscount::ladder()` already returns `Hint Lvl 1` … `Hint Lvl Max` (`HintDiscount.php:25-31`), so the cell reads `Hint Lvl Hint Lvl 1`. The `Hint level` column on the same row prints `step.label` alone and is correct; `HintDiscount::priceLadder()` returns the short `Lv1…Lv Max` form the prefix was originally written for.

**Copy defect (R6-16):** the trainee step promises `The form you pick sets this run's rarity and potential.` The payload sends `umamusume_id` and `character_card_id` only; `SetupDraft` and `StartCareerRequest` hold neither value. Both fields are enterable later, off the wizard path, in Run settings.

---

## 5. Events

`GET /training-runs/{run}/events` (`runs.events.decision`), `EventDecisionController::show`; write `POST` (`runs.events.store`).

Props: `run`, `state`, `events` (the run's recorded choices), `known`, `advisor` (which refuses rather than ranking), `write`, `empty`.

`store()` writes a `TurnEvent`: `training_run_id`, `turn`, `event_type` (`TurnEventType`, `Character|Support Card|Group|Scenario`), `source_name`, `choice_index`, `choice_label`, `support_card_name`, `bond_delta`, `origin_note`.

`StoreTurnEventRequest` bounds: `turn` 1..72, `source_name` max 100, `choice_label` required max 200, `support_card_name` max 100, `bond_delta` integer, `origin_note` max 1000. No `deltas` key is validated — see R6-48 below.

**Known defect (R6-48):** `UnityCupEditor.vue` states `Team Rank is recorded through a turn's team-race event, not through this editor.` No trainer form provides one: `StoreTurnEventRequest` has no `deltas` rule and `EventDecisionController` creates events without it. `TeamRankPayload` is constructed only in tests. The model supports the payload and the reader works, so the gap is a missing form.

**R6-48's premise is partly wrong, and the correction is recorded rather than applied — verified 2026-11-06.** The client presents Team Rank as a **derived status**: the Team Info panel prints it beside the team's five per-stat grades, the training and burst counters and the member count, all of which the game computes from the team rather than the Trainer stating them (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.8). So "Team Rank is recorded through a turn's team-race event" is the wrong model of the fact. Two things are true at once here:

- **The inputs already exist as run-level columns.** `team_name`, `team_motto`, `team_league_placement`, `team_preseason_wins`, `unity_trainings_count`, `spirit_bursts_count` and `extreme_bursts_count` are all on `training_runs` and read by `TeamPanelBuilder` (`:537-548`). `team_league_placement` is validated `min:1,max:99` (`StoreTrainingRunRequest.php:87`). So the app already holds everything a rank derivation would read, except the members.
- **What is genuinely absent is the roster**, and §1.7's finding is why: with no member entity, the per-stat grades have nothing behind them and a derived rank has nothing to derive from. That makes the roster gap prior to the R6-48 gap, not parallel to it.

`TeamRankPayload` accepts `S+` among its letters (`TeamRankPayload::LETTERS` = `G,F,E,D,C,B,A,S,S+`) while `config/scenarios.php`'s `team_rank_ladder` tops out at `S` for facility level, which is correct rather than inconsistent: S+ is the **overall** rank the 2026-07-01 rework added and it grants a hint, not a higher facility (`docs/UMAMUSUME_REFERENCE.md` §2.2.2). A per-stat select must not offer S+; an overall-rank field may. That distinction is not recorded anywhere in the app's docs yet.

**The league-placement bonus is absent from the model, the config and every surface — verified 2026-11-06.** The client prints a `Team Ranking Bonus: All Attributes +30` tooltip beside the Team Rank emblem, and that figure is the **league-placement** bonus, not a reading of the rank letter: the published ladder pays 2nd-5th +50, 6th-10th +30, 11th-15th +20 and 16th-20th +10, and the recorded run's 8th place sits in the +30 band (`docs/UMAMUSUME_REFERENCE.md` §2.2.2; `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.8). The app's handling stops short of the number:

- It **stores the input**: `team_league_placement`, validated `min:1,max:99` (`StoreTrainingRunRequest.php:87`), surfaced in `teamSection` (`TeamPanelBuilder.php:538`).
- It **stores no ladder** — no `league_bonus`, `placement_bonus` or band table exists in `config/scenarios.php`, which carries `team_rank_ladder` for facility level and `spirit_burst_bands` for the burst payout, so the absence is a gap in a file that already keeps two neighbouring ladders.
- It **renders no bonus** anywhere: grepping `app/` and `resources/js/pages/Career/` for a league-bonus surface returns only the stored column and its prop.

So the app can record where a run placed and cannot state what that placement paid, which is the same shape the burst bands had before `spirit_burst_bands` existed.

---

## 6. Inheritance

`GET /training-runs/{run}/inheritance` (`runs.inheritance`), `POST` (`runs.inheritance.store`).

Milestones: `career_start` (turn 1), `classic_april` (turn 31), `senior_april` (turn 55).

Props: `run`, `legacy`, `predicted`, `observed`, `milestones`, `write`, `empty`, `caps`.

- `predicted` is sourced probabilities only — `InheritanceEventController::predictedSection()` includes `ancestors_sparks` (R3-02 fixed), so grandparent Sparks count toward the Predicted Sparks total.
- `observed` records what the Trainer saw, via the existing `TurnEvent` mechanism. `store()` writes `event_type = Inheritance` with `deltas`.
- Caps come through `ScenarioCaps`, and each cell states its source: `entered`, `scenario`, or `not recorded`.
- **No inheritance computation happens anywhere** (`ADR-0020` §3). The `Before / Change / After` table on the screen heads its columns `Result of this pick` and prints `N/A` with `· this tool computes no inheritance change` (R6-30: honest disclaimer, misleading scaffold).

---

## 7. Timeline

`GET /training-runs/{run}/timeline` (`runs.timeline`), `TimelineController::show`. Read-only; no write route.

Props: `run`, `origin`, `entries`, `filters`, `empty`, `total_turns` (`CareerCalendar::TURNS_PER_CAREER` = 72), `finale` (`FinaleReader::forRun()`).

Entry kinds: `turn`, `race`, and event-derived rows. Turn rows carry `{key, kind, kind_label, glyph, turn, action_label, before, actual, mood, facilities, failure_rate, preview_gains, result, corrected, correction_id, edit_url, decision_url}`.

**Two defects in one row (R6-12):**

1. The controller emits `before` and `actual` (`TimelineController.php:237-238`, and `:268-269` / `:297-298` for the other kinds). The page declares both `after` and `actual` (`Timeline.vue:32`, `:34`) but reads only `after` — `entry.after?.energy` and `entry.after?.sp` at `:117-118`. `entry.actual` is never read, so SP 173 prints `N/A`.
2. `stats()` returns raw ints including `'energy' => $turn->energy` and no band text, so the band has no representation here even after the key is fixed. `TurnEntry::energyReading()` already resolves `band` / `exact` / `none` and the Cockpit calls it (`CockpitController:701`); the Timeline does not.

---

## 8. Result

`GET /training-runs/{run}/result` (`runs.result`), `ResultController::show`. Read-only.

Props: `run`, `build`, `races`, `finale_state`, `headline` (last turn's totals), `finding`, `scenario`, `save_veteran`, `ruleset`, `independent`, `empty`.

- `build` = `{stats[], target, skills{learned, not_learned, empty}, aptitudes[]}`, each stat row `{key, label, current, target, deficit}`.
- `finding` composes a sourced sentence about the finale (for example a Stamina comparison against the reference's own distance rule, with `source` named beside it).
- `independent` is null for a normal career, else `{policy_label, result}` — the idle-mode completion banner with what the run did **not** do.
- `ruleset` is a named absence, like Preflight's.

`save_veteran` carries the door; see §10.

---

## 9. Deck builder (run-scoped)

`GET /training-runs/{run}/deck` (`runs.deck`), `TrainingRunController::deck`; write `POST` (`runs.deck.sync`).

Props: `run` (`id`, `name`, `status_label`, `scenario`, `has_scenario`, `scenario_label`, `run_url`), `slots`, `picker`, `types`, `rarities`, `availabilities`, `analysis` (`DeckAnalysis::build`), `action`.

- `slots` — the six position rows, each with the card it holds and the owned/rented flag the run records.
- `picker` — filtered by `DeckPickerSearchRequest` (`type`, `rarity`, `status`, `query`, `slot`).
- `analysis` — the deck's aggregate read; reached effect values are computed by `SupportCardEffects::atLevel()` from the card's anchors and tagged `basis: 'catalog'|'level'`, never recorded.

`syncDeck()` reads the existing `ownership` and `card_level` per position **before** deleting, then deletes and re-creates every slot:

- `ownership`: posted value, else the slot's existing value, else null. The flag belongs to the slot, not the card (`ADR-0023`).
- `card_level`: an explicit null clears it; an **omitted** key keeps the slot's existing value.
- Returns to `runs.show` (which lands on the Cockpit) with `Deck saved.`

**Door:** the Cockpit links here through `run.deck_url` (R2-17 fixed). No other page links to the run-scoped builder — `Support/Builder.vue`'s only self-reference is a same-page reload for filter/page/equip.

---

## 10. Save Veteran

`GET /training-runs/{run}/veteran` (`runs.veteran`), `POST` (`runs.veteran.store`).

Props: `run` (with `recordable`), `career` (`turns`, `energy`, `fans`), `stats`, `counts` (`skills`, `races`), `graph` (`AncestryGraph::build`), `spark_kinds`, `suggested_tags` (`config('uma.veteran.suggested_tags')`), `limits`, `saved`, `held`.

`store()` runs `StoreVeteranRequest` then `RecordVeteran`:

- Only a **Completed** run is recordable; anything else raises `InvalidArgumentException`. The screen's `recordable` flag is the honest pre-check.
- Saving is keyed on the run (`updateOrCreate(['training_run_id' => ...])`), so one run is one Veteran and a re-save rewrites tags and notes instead of growing a duplicate.
- Limits: `MAX_TAGS = 20`, `MAX_TAG_LENGTH = 40`, `MAX_NOTES_LENGTH = 2000`; control characters are refused (`not_regex`).
- `held.factor_analysis` and `held.legacy_value` are named absences: no table prices a Spark.

---

## 11. Run settings, team, scenario detail

Three v3.0.0 doors that replaced inline Cockpit disclosures. All are **reads**; every field writes through `runs.update`. The Cockpit's `identitySection` data is passed to `runs.settings`, and `UnityCupEditor.vue` is rendered by `TeamEdit.vue` — not by `Cockpit.vue`, which contains no `useForm` at this tree.

| Screen | Route | Controller | Gate |
| --- | --- | --- | --- |
| Run settings | `runs.settings` (`/{run}/settings`) | `CockpitController::settings` | none |
| Team edit | `runs.team` (`/{run}/team`) | `CockpitController::teamEdit` | `composesPanel('team_rank_ladder')`, else redirect to the Cockpit with `Team state is only available for a scenario that records a team.` |
| Scenario detail | `runs.scenario-detail` (`/{run}/scenario`) | `CockpitController::scenarioDetail` | none |

`RunSettings.vue` builds four `useForm` instances (status, scenario, period, identity) all `put`ting to `props.run.update_url` with `preserveScroll: true`. `TurnEdit.vue` does the same against its own `update_url`.

These live on `CockpitController` rather than separate controllers because `runSection()` and `scenarioSectionContext()` are private there; a separate controller would either duplicate them or force a visibility refactor outside the pass.

---

## 12. Delete a career

Two-step, plus a required flag:

1. `GET /training-runs/{run}/delete` (`runs.confirm-delete`), `confirmDelete()` — a **read** that shows what will be lost (`turnEntries` and `raceEntries` counts) via `loadCount`.
2. `ConfirmDelete.vue` requires the checkbox before enabling the button (`form = useForm({confirmed: true})` on submit).
3. `DELETE /training-runs/{run}` (`runs.destroy`), `destroy()` — refuses without `request->boolean('confirmed')`, returning `back(fallback: confirm-delete)` with `Confirm that this career should be deleted.` Otherwise deletes and redirects to `runs.index` with `Run deleted.`

---

## 13. Writes, in one table

| Route | Method | URI | Request | Effect |
| --- | --- | --- | --- | --- |
| `runs.update` | PUT | `/training-runs/{run}` | `StoreTrainingRunRequest` | status, scenario, period, identity, team state, build target, notes, objective index |
| `runs.destroy` | DELETE | `/training-runs/{run}` | — (needs `confirmed`) | deletes the run |
| `runs.turns.store` | POST | `/{run}/turns` | `StoreTurnEntryRequest` | one turn row (+ failure event, + Performance event) |
| `runs.turns.update` | PUT | `/{run}/turns/{turn}` | `StoreTurnEntryRequest` | corrects a turn |
| `runs.turns.destroy` | DELETE | `/{run}/turns/{turn}` | — | deletes a turn and its failure event |
| `runs.races.store` | POST | `/{run}/races` | `StoreRaceEntryRequest` | one race entry |
| `runs.events.store` | POST | `/{run}/events` | `StoreTurnEventRequest` | one event row |
| `runs.inheritance.store` | POST | `/{run}/inheritance` | `StoreInheritanceEventRequest` | one inheritance event |
| `runs.skills.sync` | POST | `/{run}/skills` | `StoreRunSkillRequest` | upserts skill statuses, levels |
| `runs.deck.sync` | POST | `/{run}/deck` | `StoreDeckRequest` | replaces six deck slots |
| `runs.build-target.update` | PUT | `/{run}/build-target` | `StoreBuildTargetRequest` | the run's target and entered caps |
| `runs.purchases.store` | POST | `/{run}/purchases` | `StoreShopPurchaseRequest` | a `Scenario` event with `ShopPurchasePayload` |
| `runs.indep.abandon` | POST | `/{run}/indep-abandon` | — | abandons the 50-minute window (idempotent; no-op for a normal career) |
| `runs.export` | GET | `/{run}/export/{format}` | — | `csv` or `json`, else 404 |

`runs.update` also owns the site of `StoreTrainingRunRequest`'s big rule set — including `team_stat_grades.*` (G through S; `S+` refused as overall rank only) and `shop_resets_in`. The scenario picker on Run settings and the copy `It cannot be changed after the first turn.` on the scenario step contradict each other: the server applies the change with no turn or post-start condition (R6-17).

---

## 14. Export

`GET /training-runs/{run}/export/{format}` — `abort_unless(in_array($format, ['csv','json']))`.

- `json` → `{data: TrainingRunResource}` with `umamusume`, `turnEntries`, `skills` loaded.
- `csv` → header row `turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans`, then one row per turn. Energy, mood and fans are appended **after** the original eight so a consumer keying on column order still reads the same leading fields (audit F-9).
- `Content-Disposition: attachment; filename="run-{id}.{format}"`.

---

## 15. Verification surface

| Area | Tests |
| --- | --- |
| Cockpit composition and identity | `CareerCockpitTest`, `CockpitRunIdentityTest`, `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest`, `CockpitRosterTest`, `CockpitCapCompositionTest`, `CockpitObjectivesTest`, `CockpitMoodEffectTest`, `CockpitAlertScopeTest`, `CockpitEnergyBandDisplayTest`, `CockpitFinaleStripTest`, `CockpitGoalFromCompletedRaceTest` |
| Turns | `TurnEntryUpdateTest`, `TurnChoiceTest`, `GuidedTurnStagesTest`, `TurnFailurePreviewTest`, `TurnEntryFieldSourceTest`, `TurnFacilityLevelsTest`, `EnergyReadingConsistencyTest`, `TurnEditTest`, `TurnLogScrollRegionTest` |
| Races | `CareerRaceDecisionTest`, `CareerRacePlannerTest`, `RaceEntryOrdinalTest`, `FreeRaceWriterTest`, `RacePayoutTest` |
| Skills | `CareerSkillsPlannerTest`, `CareerSkillsPlannerRecommendationTest`, `SkillSpendCoverageTest`, `RunSkillInventoryTest`, `SkillHintLevelTest` |
| Events / inheritance | `CareerEventDecisionTest`, `CareerInheritanceEventTest`, `TurnEventTypePayloadsTest`, `GrandparentSparksTest`, `GrandparentRanksTest` |
| Timeline / Result | `CareerTimelineTest`, `CareerResultTest`, `SnapshotTimelineTest`, `FinaleReportingTest`, `FinaleVocabularyTest`, `FinaleAbsenceTest` |
| Deck | `SupportDeckBuilderTest`, `CareerDeckPickerTest`, `CareerDeckSummaryTest`, `DeckSlotCardLevelTest`, `DeckAnalysisReachedEffectsTest` |
| Veteran save | `SaveVeteranTest` |
| Settings / team / scenario | `RunSettingsTest`, `RunTeamStateTest`, `TeamEditTest`, `ScenarioDetailTest`, `RunGrowthRateTest`, `TraineeRarityPotentialTest`, `EnteredStatCapsTest` |
| Delete / export | `RunDeleteTest`, `ConfirmDelete` behaviour in `RunDeleteTest`, `RunWriteAtomicityTest` |
| Performance (Grand Concert) | `PerformanceFoundationTest`, `PerformanceReadPathTest`, `PerformanceTurnInputTest`, `PerformanceTurnObservationTest`, `TrainingDetailPerformanceBoundaryTest` |
| Independent Training | `IndependentTrainingLifecycleTest`, `IndependentTrainingStoreTest`, `IndependentTrainingDashboardTest` |
