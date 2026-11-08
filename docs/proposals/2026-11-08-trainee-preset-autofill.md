# Proposal — Trainee preset auto-fill at career start

**Status:** proposal, not slice. Awaiting owner ruling.
**Date opened:** 2026-11-08
**Source:** user's session, 2026-11-08; screenshot `/umamusume/vodka` profile page (base stats `96 / 61 / 105 / 75 / 88`, growth row `10 / 0 / 20 / 0 / 0`); user's words quoted in §1.
**Register reference:** none (not a Round 7 row).
**Audit reference:** none (`AUD-20261108-R7-A`, `-B`, `-C` did not name this).
**Ownership:** proposal recommends; §7 lists the decisions the owner makes.

---

## §1 Problem

The tool holds every trainee's preset facts on `character_cards`. The trainee profile page renders them. **The career-start flow carries three of the five into the run and drops two.**

The user's words, verbatim:

> *"is possible auto apply goals, stats and skills each trainee umamusume (innate and unique) chosen career, but trainers (users) option edit values and skills. but each umamusume already preset."*

The screenshot anchors it: Vodka `[Wild Top Gear]` prints base stats `96 / 61 / 105 / 75 / 88` and growth `10 / 0 / 20 / 0 / 0` on her profile page. A career started on that card lands on a Cockpit whose stat rail reads `N/A` until the Trainer types those five numbers into a turn entry.

**The brief this proposal answers assumed more was missing than actually is.** Measurement (§2) shows growth rate and the card's skill lists already seed. The genuine gaps are narrower and one of them is not a preset problem at all — it is a two-doors problem.

---

## §2 Evidence

Pre-flight baseline: `e7e0c5b22e93758f76e0716d9bdbb32ac4e8ab4e`, dirty paths 7 (all untracked audit/import files, none in scope).
`RENDERED_EVIDENCE = TRUE` — `playwright.chromium.launch()` returned `launched 153.0.8010.12`; rendered figures are live DOM reads against `php artisan serve --port=8177`.

### The card row (Vodka `[Wild Top Gear]`, `card_id = 100801`, `character_cards.id = 11`)

```sql
select base_stats, growth_rates, skills_unique, skills_innate
from character_cards where card_id = 100801;
```

```
base_stats   = {"Speed":96,"Stamina":61,"Power":105,"Guts":75,"Wit":88}
growth_rates = {"Speed":10,"Stamina":0,"Power":20,"Guts":0,"Wit":0}
skills_unique = [10081, 100081]
skills_innate = [200382, 200702, 200602]
```

Resolved: uniques → `Xceleration` (`is_unique=1`), `Cut and Drive!` (`is_unique=1`); innate → `Straightaway Recovery`, `Updrafters`, `Slick Surge`.
The profile page's third unique, `Into High Gear!` (`export_id=110081`), belongs to the *other* Vodka form `[Fiery Aqua Vitae]` (`card_id=100802`), where it is that card's sole unique. The screenshot showed one form's block; the tree holds both.

**Rendered, against the live app** (`/career/setup/trainee/8`):

```
base-stats  Speed 96 · Stamina 61 · Power 105 · Guts 75 · Wit 88   (has_base_na: false)
growth      Speed 10% · Stamina 0% · Power 20% · Guts 0% · Wit 0%
h1          Vodka ウオッカ
```

DB and renderer agree exactly.

### What the tree holds

| # | Measurement | Result |
| --- | --- | --- |
| M-1 | `select count(*) from character_cards` | **263** |
| M-2 | `whereNotNull('base_stats')` / `whereNotNull('growth_rates')` / `whereNotNull('skills_unique')` | **263 / 263 / 263** — the preset is complete, not partial |
| M-3 | `character_cards` columns | `base_stats`, `growth_rates`, `skills_innate`, `skills_unique`, `skills_awakening`, `skills_event`, `skills_evo` all first-class json columns (`2026_11_02_141639_add_base_stats_and_growth_rates_to_character_cards.php`, `2026_09_30_073029_add_skill_lists_to_character_cards_table.php`) |
| M-4 | `training_runs` columns | has `growth_rate`, **has no base-stat or starting-stat column**; the five stat values live only on `turn_entries` (`speed, stamina, power, guts, wit`, all non-null) |
| M-5 | `turn_entries` for run 3 | **1** row, at turn 67, `speed 607 / stamina 577 / power 549 / guts 736 / wit 397` |
| M-6 | `select count(*) from run_skills` | **0** across the whole dev database (one run exists, created without a card) |
| M-7 | `StartCareerRequest::runAttributes()` `:165-177` | **copies `growth_rate` from the card** — `$attributes['growth_rate'] = $card->growth_rates;` |
| M-8 | `PreflightController::store()` `:147-186` | creates the run from those attributes, writes deck slots, **calls nothing that seeds skills** |
| M-9 | `TrainingRunController::prePopulateSkills()` `:261-283` | seeds `skills_innate ∪ skills_unique` as `SkillAcquisition::Suggested`, inside `DB::transaction` with the create (`:230-237`) — **the legacy run-create door, not the wizard door** |
| M-10 | `PreflightController` class docblock `:51` | *"No start skills are seeded (the same shape `runs.import` has, and for the same reason)"* — the absence is deliberate and documented |
| M-11 | `CockpitController::stateSection()` `:683-700` | `'current' => $latest?->statValue($stat)` — reads the **latest turn entry only**; a run with no entries has no stat to print |
| M-12 | `select count(*) from trainee_goals` | **1677** rows, **133** of 136 trainees, all from one source: `gametora.com/data/umamusume/ura-objectives.*.json` |
| M-13 | `trainee_goals` columns | **no `scenario` column.** Columns: `umamusume_id, race_catalog_slot_id, goal_type, required_placement, fan_threshold, career_year, turn, source_order, …`. The table is **URA-only by construction**, not per-scenario |
| M-14 | Vodka's goals | **13** rows — `Participate` ×2, `Placement` ×8, `FinalRound` ×3; turns 12…78, years 1-4 |
| M-15 | `TeamPanelBuilder::traineeGoalRows()` `:214-231` | queries `TraineeGoal::where('umamusume_id', $run->umamusume_id)` — **no scenario filter** |
| M-16 | `CockpitController::scenarioSectionContext()` `:335-336` | gates the panel on `config('scenarios.scenarios.'.$run->scenarioKey().'.panels.career_goals')` |
| M-17 | `config('scenarios.scenarios.*.panels.career_goals')` | `ura_finale` **true**, `unity_cup` **true**, `our_grand_concert` **true**, `trackblazer` **absent** |
| M-18 | Config key for a goal-shape distinction | **none exists.** `facility_level_source` distinguishes facility rules (`repetition` / `team_rank` / `repetition_plus_purchases`), not goals. No config key says "this scenario carries character goals" |
| M-19 | `CreateSnapshotRun::handle()` `:72-83` | creates the run with `umamusume_id, scenario, status, mode, notes, career_position, career_position_source` — **no `character_card_id`, no `growth_rate`, no skill seeding** |

### Live cockpit, run 3 (`unity_cup`, Rice Shower, `character_card_id = null`)

```
Speed 607 / 1,300 floor   Stamina 577 / 1,300 floor   Power 549 / 1,300 floor
Guts 736 / 1,300 floor    Wit 397 / 1,800 floor
```

607 is a *recorded mid-career reading* at turn 67, not a start line. It is printed because `turn_entries` has a row. A freshly started career with no entries has `current: null` for all five (M-11).

---

## §3 What current code does

**The wizard door** (`PreflightController::store()`, `:147`):

```php
$run = DB::transaction(function () use ($request): TrainingRun {
    $run = TrainingRun::create($request->runAttributes());   // growth_rate rides in here (M-7)
    // deck slots written
    return $run;
});
// no skill seeding
```

**The legacy door** (`TrainingRunController::store()`, `:230-237`):

```php
$run = DB::transaction(function () use ($validated): TrainingRun {
    $run = TrainingRun::create($validated);
    $this->prePopulateSkills($run);      // seeds innate ∪ unique as Suggested
    return $run;
});
```

So: **a career started from the dashboard run form gets its starting skills; a career started from the wizard does not.** The wizard's docblock records the absence as intentional; `prePopulateSkills`'s docblock records the seeding as intentional. Both are defensible in isolation and they contradict each other.

**Base stats** are not carried by either door, because there is nowhere to carry them to: `training_runs` has no base-stat column (M-4), and `turn_entries` is a per-turn log with non-null stat columns — a row there is a *reading*, and `CockpitController` reads the latest one (M-11). Writing a synthetic turn-0 entry would fabricate a recorded turn.

**Goals** are read scenario-agnostically. `TeamPanelBuilder` filters by `umamusume_id` alone (M-15); the panel's *visibility* is gated per scenario (M-16) but its *rows* are not. For Vodka in Unity Cup the cockpit would compose her 13 URA objectives — including three `FinalRound` rows filtered out at `:217`, leaving ten URA race demands — under a panel config that says `career_goals: true` for Unity Cup (M-17). Her URA ladder is not Unity Cup's obligation set.

---

## §4 Proposal recommends

Four changes, ranked by evidence strength. **Only the first two are what the user asked for; the third is a defect the measurement found; the fourth is the part that needs an owner ruling.**

### 4.1 Seed the starting skills on the wizard door (clear, small)

Call `prePopulateSkills` on the wizard's create, or lift it into a shared action both doors call. The card's `skills_innate ∪ skills_unique` becomes `Suggested` rows on the new run.

Rules cited: `SHR-1` (the same thing behaves the same everywhere — two create doors seeding differently is exactly that failure), `GP-1` (the product holds the list; making the Trainer re-enter it is arithmetic left in their head).

### 4.2 Show the card's base stats on the decision surfaces, without inventing a stored column

The base five are a **starting line, not a recorded turn**. Recommendation: render them beside the Cockpit's stat rail as a labelled reference (`Starting: 96 / 61 / 105 / 75 / 88`), read from `run->characterCard->base_stats`, and **do not** write them into `turn_entries` or add a `training_runs` column.

Rules cited: `SHR-7` (the value a decision needs is on the decision screen — the numbers a turn-1 training decision compares against belong there), `GP-1`, `H2` (client vocabulary: these are the client's own five base values, not a tool derivation).

This is deliberately *not* "auto-fill the stat input". Pre-filling turn 1's stat fields with the base line would make the first recorded reading indistinguishable from a typed one, which is the fabrication the snapshot flow explicitly refuses (`CreateSnapshotRun` `:96-104`: "filling the gap with a zero would print a number the Trainer never read"). A reference strip states the same five numbers without claiming they were read.

### 4.3 Gate the goal ladder on the scenario actually carrying character goals (defect)

`trainee_goals` is URA-only (M-12, M-13). `panels.career_goals` is `true` for three scenarios (M-17). Those two facts together mean Unity Cup and Our Grand Concert cockpits compose a URA ladder as if it were theirs.

Two ways to close it, and the choice is the owner's:
- **(a) Config.** Add a key per scenario naming the goal source (`'goal_source' => 'trainee_goals'` for URA, `'goal_source' => null` elsewhere), and gate the ladder read on it. This is `IA-6`'s shape (adding a scenario costs configuration, not layout) and it is what the config already does for facility rules via `facility_level_source` (M-18).
- **(b) Data.** Add a `scenario` column to `trainee_goals`, populate it from per-scenario sources as they are fetched. This is a schema change and needs its own ADR.

**Recommendation: (a) now for Unity Cup; (a), or (b) later, for Our Grand Concert pending §7 decision 7.** (a) states the truth the tool holds; (b) invents a column for data nobody has fetched.

**Rendered outcome of the gate.** A scenario whose goal source is null must not draw a ladder. The tree has one established behaviour for a panel flag that is off, and this follows it rather than inventing a second: `ScenarioPanel.vue` draws nothing at all for an off flag — *"an OFF flag still draws nothing at all, because the region names the composition, never the absence of a system the scenario does not use."* Trackblazer is the live precedent (`panels.career_goals` absent, M-17), and its cockpit already renders no career-goals panel. So the gate's outcome is **(ii) panel hidden entirely**, which is the pattern the tree already uses — not a new empty state.

That choice is worth stating because `IA-5` (an empty state shows an example of what would fill it) would argue for a named absence instead. It does not apply here: the panel is not empty for Unity Cup, it is **not that scenario's panel at all**. The team races Unity Cup does carry are a different panel (`team_race`), already composed for that scenario.

**Not `KI-122`, not `R7-18`, not `R5-12`.** A reader who knows Round 7 reaches for `R7-18` — *"URA Finals obligations on a Unity Cup run"* — and for `R5-12`'s first half, the same claim from Round 5. Both are **`Ruled out`**, and `KI-122` (CLOSED, does not reproduce) is the ruling that closed them: the three URA Finals rounds are shared across scenarios by design, only the Final forks per scenario, and filtering by scenario key would have deleted legitimate obligations from every run. That ruling is about **`race_catalog_slots`** — the mandatory-race rows, keyed by a real `scenario_key` column with `null` meaning shared.

§4.3 is a different table under a different column. `trainee_goals` has **no `scenario` column** at all (M-13) and holds **URA-only** data (M-12). The question here is not whether shared rounds should be filtered — they should not, and `KI-122` settles that — but whether a URA-only per-character ladder should render under a scenario it does not describe. **`KI-122` does not settle §4.3; it settles a different table under a different column.**

Rules cited: `NRM-3` (a rule the system enforces is expressed by the control, not a message after it — the panel should not render a ladder it cannot honour), `H5` (error prevention: a Unity Cup Trainer reading URA goals is being told a wrong fact), `IA-6`.

### 4.4 Goals seeding — what "seed the trainee's goals" would even mean

The brief asked whether a run should seed the trainee's goals. Measurement changes the question: **goals are not seeded, they are joined.** `TeamPanelBuilder` queries `trainee_goals` by `umamusume_id` at read time (M-15); there is no run-side goal row, no `current_objective_index` write at creation, and no per-run copy. The ladder is already "auto-applied" — for every run of that trainee, in every scenario.

So 4.4 is not a seeding task. It is 4.3: making the join scenario-correct. **Recommendation: do nothing beyond 4.3.**

### Scenario-by-scenario disposition (the brief's §5.1 decision 4)

| Scenario | `panels.career_goals` | `trainee_goals` holds | Recommendation |
| --- | --- | --- | --- |
| **URA Finale** | `true` | her ladder (13 rows for Vodka) | Keep as-is. This is the one scenario the data fits. |
| **Unity Cup — confirmed** | `true` | her URA ladder — wrong data | Gate off via 4.3(a) **confirmed**. Unity Cup has no URA ladder of its own; the panel currently renders Vodka's ten race demands under a scenario that is not URA. The panel's team-race content stays, under its own `team_race` flag. |
| **Trackblazer** | absent | her URA ladder | Already gated off; nothing to do. |
| **Our Grand Concert — pending §7 decision 7** | `true` | her URA ladder | **Held.** `config/scenarios.php:1118-1119` says *"the trainee's own objective ladder is a career fact, not a concert mechanic"* and that it is "the one panel this entry composes" — without saying whether "the trainee's own objective ladder" means the URA list or a concert-specific one. If the owner rules it means the URA ladder, OGC behaves as URA behaves and **no gate is added**. If the owner rules it means a concert-specific ladder, §4.3 gates OGC off exactly as Unity Cup, because the table holds no concert rows. |

---

## §5 Rejected alternatives

| Alternative | Why rejected |
| --- | --- |
| **A. Seed base stats into turn 1 as a synthetic `turn_entries` row** | Fabricates a reading. `turn_entries` requires all five stats non-null and the cockpit prints the latest row as *the run's state*; a seeded row would be indistinguishable from a typed one, and `CreateSnapshotRun` documents this exact refusal for the snapshot door. |
| **B. Add `training_runs.base_stats` json column** | A migration to store what `character_cards` already stores, keyed by a card the run already points at. `SHR-8`/`GP-1` say read it, do not duplicate it. Rejected on the ladder's first rung: it does not need to exist. |
| **C. Copy the card's skill ids into the draft at step 2, so Preflight sees them** | The draft is a wizard scratch bag; putting seeded skills in it makes "skills the Trainer chose" and "skills the card implies" the same field. `prePopulateSkills` already distinguishes them by status (`Suggested` vs `Acquired`), which is the correct split. |
| **D. Seed goals as run rows at creation** | There is no run-side goal table and the ladder is derived at read time (`TeamPanelBuilder` docblock: "Progress is **derived, never stored**"). Adding one would create a second owner for a fact the query already answers. |
| **E. Add `scenario` to `trainee_goals` now** | Schema change for data not yet fetched. Only URA publishes a per-character objective list in this tool (`ura-objectives.ead4bbfe.json`, M-12). 4.3(b) holds it open for when a second source lands. |

---

## §6 Fix would not change

- **Not the run's position.** Turn/year/month seeding is `R7-20` / `R7-32`'s problem, closed. This proposal does not touch `CareerPosition` or `CareerCalendar`.
- **Not the goal derivation logic.** `TeamPanelBuilder`'s `objectiveMet()`, `objectiveHasRecord()`, `TraineeGoal::obligationLine()` and `CockpitController::objectiveSection` are untouched. 4.3 gates *whether* the ladder composes, not *how* a row is judged.
- **Not the snapshot door** unless the owner explicitly widens decision 3. `CreateSnapshotRun` has no card id (M-19); making it seed skills would require the snapshot to ask for a form, which is `R7-99`-adjacent scope.
- **Not the trainee picker or the profile page.** `TraineeSelect.vue` and `TraineeProfile.vue` already render all five preset facts correctly (M-rendered); they stay.
- **Not the scenario matrix's other keys.** `facility_level_source`, `cap_bonus`, `panels.*` beyond `career_goals` all stay.
- **Not `growth_rate`.** It already seeds (M-7).
- **No scenario name** enters a component (`AGENTS.md` §7, gate G-33); 4.3 goes through config, which is the same shape the existing `career_goals` gate already uses.

---

## §7 Owner decisions required

| # | Question | Recommendation | Why |
| --- | --- | --- | --- |
| 1 | **Seed starting skills on the wizard door?** | **Yes** — call `prePopulateSkills` (or a shared action) from `PreflightController::store()` | Two doors create a run; one seeds, one does not. `prePopulateSkills` is already idempotent and already scoped to `availableOnGlobal()` skill rows, so a duplicate is impossible. `PreflightController:51`'s "no start skills are seeded" note becomes stale and must be corrected in the same change. |
| 2 | **What does "base stats" mean for a run?** | **A labelled reference beside the rail, never a stored column, never a seeded turn entry** | The card holds them; the run has nowhere honest to put them. A reference states the starting line without claiming a reading. Rejected alternatives A and B. **On the user's "trainers option edit values":** the base five are a **card fact, not state** the run owns, so there is nothing on the run to edit. What the Trainer edits is the **turn-1 reading** — the base line plus whatever the first turns produced — entered on the turn form, which the run records. The reference strip states what the card started at; it does not itself accept edits, because editing a card fact on a run screen would create a second owner for a value `character_cards` already holds. "Edit values" is satisfied by the existing turn form and `RunSettings.vue`, not by a new editable base-stat field. |
| 3 | **Does the snapshot door also seed?** | **No, not in this change** | `CreateSnapshotRun` names no card (M-19), so there is nothing to seed from. Widening it means asking the snapshot for a costume form, which is its own question. |
| 4 | **Gate the goal ladder per scenario via config?** | **Yes — add a per-scenario goal-source key; `trainee_goals` only for URA.** Unity Cup confirmed; Our Grand Concert held behind decision 7 | `trainee_goals` is URA-only by construction (M-12, M-13) and the panel flags three scenarios on (M-17). Unity Cup currently renders a URA ladder as its own. This is the measurement-found defect, and it is the strongest finding in this proposal. Our Grand Concert's own config note (`:1118-1119`) may already claim the URA ladder as its intent, so its gate waits on decision 7. |
| 5 | **Add `scenario` to `trainee_goals` now?** | **No** — hold until a second scenario's goal list is fetched | A schema change for data that does not exist. Needs an ADR when it does. |
| 6 | **Editability after seeding** | **Already supported; nothing to build** | Seeded skills land as `Suggested`; `SkillsPlannerController` writes `Acquired`/`Skipped`/`Suggested` per skill (`ADR-0029`). `RunSettings.vue:107-141` edits `growth_rate` and the four caps. The user's "trainers have option to edit" is satisfied by the existing status editor and settings page. |
| 7 | **Our Grand Concert's ladder** | **Owner confirms which ladder `config/scenarios.php:1118-1119` means** before 4.3 ships for OGC | The config note says *"the trainee's own objective ladder is a career fact, not a concert mechanic"* and that it is "the one panel this entry composes" — without saying whether that is the URA list or a concert-specific one. If the URA list, the panel is correct for OGC and 4.3 only closes Unity Cup. If a concert-specific ladder, the panel must gate off. This is a copy-vs-data question the tool cannot settle alone. **Unity Cup does not wait on this.** |

---

## §8 Risk and reversibility

- **Blast radius.** 4.1: one controller method call plus a stale docblock correction. 4.2: one controller payload key plus one component block, no schema. 4.3: one config key per scenario plus a gate in `CockpitController::scenarioSectionContext` and `TeamPanelBuilder::traineeGoalRows`. No migration in any of the three.
- **Reversibility.** 4.1 reverts by deleting one call and restoring the docblock sentence. 4.2 reverts by deleting a prop and its block — nothing reads it. 4.3 reverts by removing the key and the gate, which restores today's behaviour exactly (the ladder composes for any trainee whose goals exist).
- **Highest risk is 4.1, and it is a behaviour change the user did not literally ask for.** Seeding skills on the wizard door means every new career starts with 3-7 `Suggested` skill rows a Trainer did not enter. If a Trainer's workflow is to enter the sheet's real skill state first, the seeded rows are noise they must clear. Mitigation: they are `Suggested`, not `Acquired`, and the Skills Planner already groups by status. **If the owner rules against 4.1, 4.2 and 4.3 stand on their own.**
- **4.3 changes what an existing run's cockpit shows.** Runs already created under Unity Cup will lose their goal ladder. That is the intended correction, but it is a visible regression to anyone who was reading those rows. Worth stating in the change note.
- **Spec risk.** No browser spec pins the wizard's post-start skill count today (`CareerPreflightTest` asserts `contract.target.skills.names` is `[]` at `:168` — the *draft's* priority list, not the run's seeded rows). 4.1 needs a new assertion; nothing existing breaks.

---

## §9 Proposed slice shape (not commitment)

**Not a commitment.** Naming a shape, not a slice id, commit or phase.

Three separable pieces, in evidence strength order. They need not ship together.

- **Piece 1 (4.3, the defect).** `config/scenarios.php` gains one key per scenario; `CockpitController::scenarioSectionContext()` and `TeamPanelBuilder::traineeGoalRows()` read it. Files: `config/scenarios.php`, `app/Http/Controllers/Career/CockpitController.php`, `app/Services/Career/TeamPanelBuilder.php`. Test: a feature test creating a Unity Cup run on a trainee with `trainee_goals` rows and asserting the `career_goals` panel composes no ladder rows, plus the URA counterpart asserting it still does. RED: today the Unity Cup run gets Vodka's ten URA rows.
- **Piece 2 (4.1, the parity gap).** `PreflightController::store()` seeds skills; the `:51` docblock sentence is corrected. Files: `app/Http/Controllers/Career/PreflightController.php` (and `TrainingRunController` if `prePopulateSkills` is lifted into an action). Test: extend `CareerPreflightTest` asserting `run_skills` holds the card's `innate ∪ unique` as `Suggested` after a wizard start. RED: 0 rows today.
- **Piece 3 (4.2, the reference strip).** `CockpitController::identitySection()` or `stateSection()` gains `starting_stats`; the rail component renders it. Files: `app/Http/Controllers/Career/CockpitController.php`, the cockpit stat component under `resources/js/`. Test: a browser spec asserting the five numbers render beside the rail for a run with a card, and render nothing (not `N/A`) for a run without one — the distinction `DESIGN.md` §4.7 and `AGENTS.md` §13 make between `N/A` and an absent slot.
- **Pint / PHPStan / typecheck / lint:ui** per the hand-off sequence. `lint:ui` will flag any arbitrary Tailwind value in the new strip; reuse the rail's existing tile classes.

---

## §10 Proposal does not do

- No code. No file under `app/`, `resources/`, `database/`, `tests/`, `config/`, `routes/` is changed by this session.
- No slice opened. No RED, no GREEN, no pre-flight gate.
- No dispatch. No slice id, commit SHA or phase named.
- No re-opening of Round 7. This defect is not `R7-78` / `R7-79` (those record *owned/acquired* skills and per-skill level as unrepresented source data; `R7-78`'s note "No wizard skills step; nothing seeds the acquired set" describes the same absence but is a *source-data* row, not a preset question). It is not `R7-20` / `R7-32` (turn convention). It is not `R7-99` (snapshot read-back).
- No claim that the audits named this. `AUD-20261108-R7-A/B/C` did not.
- No third proposal. Two observations recorded but not proposed: (i) the 10 duplicate skill names (P-02 §2 M-2) affect goal and skill matching generally; (ii) three `[Global]` trainees have no `trainee_goals` rows (133 of 136, M-12). Neither is in scope here.
- **No claim that the user's screenshot is the whole card set.** It showed one form's block; Vodka has two forms in the tree. Recorded in §2 so no reader mistakes the screenshot for an inventory.

---

## §11 Change log

| Date | Change |
| --- | --- |
| 2026-11-08 | Opened. Pre-flight baseline `e7e0c5b22e93758f76e0716d9bdbb32ac4e8ab4e`, `git status --short` = 7 untracked (`docs/audits/RS-UC-2026-10/`, `RS-UC-2026-11-snapshot-door/`, `RS-UC-2026-12/`, `rice-shower-unity-cup-ux-walk-6.md`, `-walk-7-snapshot-door.md`, `-walk-7.md`, `docs/imports/ura-finale-runs.md.bak`). `RENDERED_EVIDENCE = TRUE` (`launched 153.0.8010.12`). Measurements M-1..M-19 taken against `database/database.sqlite` and a live `php artisan serve --port=8177`. **Three measurements contradicted the planning brief** and are recorded as such: growth rate already seeds (brief assumed it did not); the card's skill lists already seed on one door only (brief assumed neither door); `trainee_goals` has no `scenario` column and is URA-only by construction (brief assumed per-scenario rows exist). |
| 2026-11-08 | Refinement — §4.3 distinguishes `R7-18`/`R5-12` (`KI-122` settles shared `race_catalog_slots` obligations, not the `trainee_goals` ladder) |
| 2026-11-08 | Refinement — §7 decision 2 clarifies that the user's "edit values" means the turn-form reading, not an editable base-stat field |
| 2026-11-08 | Refinement — §4.3 scoped to Unity Cup confirmed; Our Grand Concert held pending §7 decision 7 |
| 2026-11-08 | Refinement — §4.3 states the gate's rendered outcome: the panel is hidden entirely, following `ScenarioPanel.vue`'s existing off-flag behaviour and Trackblazer's precedent |
| 2026-11-08 | Refinement — duplicate check against `docs/proposals/` run (title filter + body grep for `character_cards.*base.?stat`, `spark.?target`, `1,?910`, `JapanOnly`); no prior proposal covers this surface. |
