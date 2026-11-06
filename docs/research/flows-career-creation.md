# Flows — Career Creation

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `routes/web.php`, `app/Http/Controllers/Career/*.php`, `app/Http/Requests/Career/*.php`, `app/Services/Career/SetupDraft.php`, `app/Actions/CreateSnapshotRun.php`, `app/Actions/ImportHistoricalRun.php`.

Three doors create a career. They are not equivalent: the wizard creates a new career at turn 1, the snapshot records an in-progress career at the position the Trainer reads off the client, and the CSV import writes a history from a file. A fourth, transitional door (`runs.create`) still exists.

---

## 1. New Career wizard

### 1.1 Shape

Six steps, one route pair each, all writing to the session draft `SetupDraft` (`SESSION_KEY = 'career.setup'`). No run exists until Preflight.

| # | Step | GET (name) | PUT (name) | Controller | Draft keys written |
| --- | --- | --- | --- | --- | --- |
| 1 | Scenario | `career.scenario` | `career.scenario.store` | `ScenarioSelectController` | `scenario` |
| 2 | Trainee | `career.trainee` / `career.trainee.profile` | `career.trainee.store` | `TraineeSelectController` / `TraineeProfileController` | `umamusume_id`, `character_card_id` |
| 3 | Build target | `career.target` | `career.target.store` | `BuildTargetController` | `build_target`, `caps` |
| 4 | Legacy | `career.legacy` | `career.legacy.store` | `LegacySelectController` | `legacy_selection`, `legacy_parents` |
| 5 | Deck | `career.deck` | `career.deck.store` | `DeckSelectController` | `deck` |
| 6 | Preflight | `career.preflight` | `career.preflight.store` | `PreflightController` | creates the run, then `SetupDraft::reset()` |

### 1.2 Why the draft exists

`RunStatus` has no `draft` case, and an `Active` run created at step 1 would surface as a phantom career on the Dashboard and as a phantom builder target in the Legacy Lab. `SetupDraft` is therefore the only owner of pre-run state, and the run is created once, at Preflight.

`SetupDraft` public surface: `read()`, `write()`, `traineeRecorded()`, `scenarioRecorded()`, `buildTarget()`, `characterCardId()`, `legacySelection()`, `legacyParents()`, `deck()`, `visited()`, `summary()`, `saved()`, `markVisited()`, `reset()`, `planningRun()`, `caps()`, `scenarioLabel()`.

### 1.3 Step 1 — Scenario

- `show()` renders the scenario cards from `config('scenarios.scenarios')`.
- `store()` writes `SetupDraft::write(['scenario' => $scenario])` and **redirects back to itself**, deliberately: the card then reflects stored state rather than whatever the client still holds, which is what makes "the selection survives navigation" a claim about the server.
- A null write is a deliberate clear, stored as null (not an empty string) so `SetupDraft::saved()` stops counting step 1 as saved. Flash: `Scenario cleared.` or `Scenario set.`
- `capsLabel()` composes the published ceilings through `ScenarioCaps::caps()` and words them as, for example, `1300 ×4 · Wit 1800` — the outlier is the scenario's mechanical identity, so it is printed rather than collapsed to one number.

### 1.4 Step 2 — Trainee

- Roster (`career.trainee`) plus a per-trainee profile read (`career.trainee.profile`, binding `{umamusume}`).
- `store()` writes `umamusume_id` and `character_card_id`. A present-and-null id is the deliberate clear; the keys are still written because `SetupDraft::traineeRecorded()` reads their **presence**, and a cast would turn a clear into trainee 0.
- Re-picking a trainee without naming a form clears the previous card, so a form from the old trainee cannot linger on the draft.
- `next=target` advances to step 3 and is used by the profile screen's own primary; every other caller (roster card click, catalog carry) gets the read-back redirect to the roster.
- `carry()` is the catalog's "New run" door: `POST /career/setup/trainee/{umamusume}/carry`. The model binding is the whole validation; the write is the one the step's own form makes, so the two doors cannot disagree about a carried choice.

### 1.5 Step 3 — Build target

- `show()` reads the draft through `SetupDraft::buildTarget()` and clamps against `SetupDraft::planningRun()`, a synthetic planning run — that is what lets ceilings be enforced before a run row exists.
- `store()` writes two keys: `build_target` (`BuildTargetPayload`, six keys) and `caps` (the entered ceilings). A ceiling is a run column, not a payload key (`BuildTargetPayload::assertKeys()` refuses a seventh), so it travels under its own draft key and Preflight writes it onto the created run (`ADR-0025`).
- Redirects forward to `career.legacy` with `Build target saved.`

### 1.6 Steps 4 and 5 — Legacy and Deck

Both reuse the run-scoped rule sets by inheritance rather than restating them:

| Draft request | Extends |
| --- | --- |
| `StoreDraftLegacyRequest` | `StoreLegacySelectionRequest` |
| `StoreDraftDeckRequest` | `StoreDeckRequest` |
| `StoreDraftBuildTargetRequest` | `StoreBuildTargetRequest` |

That is what makes a draft value and a saved one obey one dictionary.

- Legacy writes `legacy_selection` and `legacy_parents`; `next=deck` advances, otherwise it returns to itself.
- Deck writes the whole six-slot set (`SetupDraft::write(['deck' => ...])`) — a replace, not a merge, because a merge would leave a card the Trainer just cleared sitting in the draft while nothing on screen still claimed it. It returns to the step and to `deck_slot=N` when that is one of `DeckSlot::POSITIONS`; an out-of-range slot (a pasted URL) falls back to the step default rather than refusing the page.
- `DeckSelectController::ownershipGroups()` derives an Ownership group per card from **saved runs only** (the session draft is deliberately excluded): `OWNED` if any saved slot records it, else `RENTED` if any does, else `NOT_RECORDED`.

**Known gap (R6-05, blocking):** `StoreLegacySelectionRequest` accepts a manual parent `name`, and every reader supports it, but the rebuilt record omits `name`, so it is gone before Preflight exists. Cold-load manual mode also tests `legacy_id === null` while stored payloads omit `legacy_id`, so the picker cannot re-enter manual mode even once the name is fixed.

### 1.7 Step 6 — Preflight, the wizard's one write

`PreflightController::show()` composes the contract from the draft:

- `build` (scenario, trainee, deck), `target` (`stats` rows, `races`, `skills.names`), `warnings`, `ruleset`.
- `targetPayload()` treats a stored payload the reader refuses as *no target* rather than a fatal: the reader's refusal means the tool's vocabulary changed under a saved draft, and the page's answer is the same missing-section warning a never-entered target gets.
- `scenarioSection()` reads the scenario's own rules from config (`metadata.planning_focus`, `facility_level_source`, `fan_gates`, `finale_targets.stamina_by_distance`) rather than recomputing them, so the screen and the config cannot disagree.
- `ruleset` is a named absence: `N/A` with the title `No source defines a Global ruleset version, so this snapshot names the absence rather than a version.`
- `edit` carries the five step URLs back, and `startAction` is `career.preflight.store`.

`store()` runs `StartCareerRequest` (which re-runs each step's Form Request), then, inside one `DB::transaction`:

1. `TrainingRun::create($request->runAttributes())`.
2. For each `deckByPosition()` entry, `deckSlots()->create([support_card_id, slot_position, ownership])` — the flag the deck step recorded, carried onto the run (`ADR-0023` D3).
3. Outside the transaction, if `independentConfig() !== null`, `Lifecycle::start($run, $config)` opens the 50-minute idle window.
4. `SetupDraft::reset()`.
5. Redirect to `runs.cockpit` with `Career started for {trainee}, run #{id}.` — `CareerPreflightTest` pins both the destination and the words. Two careers of one trainee are told apart by the id (R4-01).

Position: the wizard writes no `career_position`. `mode` defaults to `new_career`; the first turn is 1.

**Known gap (R6-11, blocking):** Preflight creation writes three deck keys only (`support_card_id`, `slot_position`, `ownership`). `card_level` is stored by the run-scoped `runs.deck.sync` but **dropped by the wizard commit**, and `StartCareerRequest` neither validates nor forwards it.

---

## 2. Snapshot (an in-progress career)

### 2.1 Shape

| Method | URI | Name | Controller action |
| --- | --- | --- | --- |
| GET | `/career/snapshot` | `career.snapshot` | `entry` |
| GET | `/career/snapshot/setup` | `career.snapshot.setup` | `setup` |
| POST | `/career/snapshot/review` | `career.snapshot.review.submit` | `review` |
| GET | `/career/snapshot/review` | `career.snapshot.review` | `review` |
| POST | `/career/snapshot` | `career.snapshot.store` | `store` |

### 2.2 Why review is a POST

The form arrives as a POST so its notes never enter the URL. The GET stays for the bare visit: opening Review without a submitted form and without a parked draft is a real state, not an error — it renders every field as "Not provided". The draft parks in the session under `career.snapshot.draft`.

### 2.3 The three-way field state

`SnapshotFieldState` distinguishes three sentences, never three renderings of an absence:

| State | Sentence |
| --- | --- |
| `Known` | `Known · {label} {value}` |
| `Unknown` | `Unknown · {label}` — the Trainer looked; the client shows nothing |
| `NotProvided` | `Not provided · {label}` — left alone |

A reviewer can therefore tell "I don't know this" from "I didn't fill it in".

### 2.4 Validation and write

`StoreSnapshotRequest` requires `umamusume_id`, `scenario`, `career_year`, `career_month` (1..12), `career_phase`. Optional: the five stats, `energy` (0..100), `fans`, `skill_points`, `notes` (max 5000), plus `field_states`.

`store()` calls `(new CreateSnapshotRun)->handle($request->validated())`, then redirects to `runs.cockpit` with `Snapshot recorded.`

`CreateSnapshotRun` sets `career_position` and `career_position_source = 'imported'`; `TrainingRun::hasImportedPosition()` is `career_position !== null`. Both the Cockpit (`CockpitController`) and the Timeline read the imported position.

**Known gap (R6-34):** `CareerPosition::scenarioCountdown` is plumbed end to end — serialized, deserialized, read at `CockpitController:277` — but no rule accepts it and no snapshot input posts it, so the only snapshot writer always stores null. The one renderer (`CockpitIdentityCard.vue`) is imported nowhere. A dead pipe, not a missing feature.

---

## 3. CSV import

### 3.1 Shape

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET | `/training-runs/import` | `runs.import` | `importForm` |
| POST | `/training-runs/import` | `runs.import.store` | `importStore` |
| POST | `/training-runs/import/preview` | `runs.import.preview` | `importPreview` |

Registering the literal segment before `training-runs/{run}` matters: otherwise `import` binds as a run id and 404s on the model.

### 3.2 Contract

`ImportHistoricalRunRequest`:

- `HEADERS = ['turn','speed','stamina','power','guts','wit','sp','condition','energy','mood','fans']`.
- `MAX_ROWS = 200` (a career is about 72 turns plus headroom, not a round number chosen for taste).
- Rows are parsed into `turns` **before** the rules run, so each stat is checked against the scenario's own ceiling.
- `turn` is `distinct` — two rows claiming the same turn number is refused with `Two rows claim the same turn number. Turn numbers are unique within a run.`
- Error copy names the turn: the request substitutes the row's turn for `:where`, so a message reads "Turn 3 has no speed" rather than the raw `turns.0.speed` path.
- A mapping override is allowed (`mapping.*` in `HEADERS`), and the preview carries the mapping forward — without it a remapped file would be refused on the confirm step by a strict header check it never used on the first one.
- `sourceLabel()` keeps the browser's filename when there was one and otherwise folds a pasted body to a short content hash, so `import_source` always names something the Trainer can point at.

### 3.3 Write

`importStore()` re-validates rather than accepting a token from the preview — keeping the flow two POSTs and no session state. `ImportHistoricalRun::handle()` writes the run and every turn inside one transaction, setting `imported_at` and `import_source`. Redirects to `runs.show` (which lands on the Cockpit) with `Imported N turns.`

An import is a Trainer's own history arriving by file, which is why it is *not* named `Store*`: in this repo that prefix means "persist rows the fetch pipeline ingested from a declared source".

---

## 4. Legacy create form (transitional)

`GET /training-runs/create` (`runs.create`), `POST /training-runs` (`runs.store`). One-combobox form, kept and reachable from a catalog page carrying a chosen trainee. `store()` writes the run and pre-populates its `Starting` skills from the chosen card inside one transaction (a half-created run would show an empty Skills section), then redirects to the Cockpit with `Run created.`

`prePopulateSkills()` is creation-only:

- A run naming no card has nothing to seed.
- A card whose lists are null was published without them, so the method returns rather than `foreach`-ing null.
- Every id resolves through `Skill::scopeAvailableOnGlobal()` — the same read-path filter the picker uses — so a Japan-only skill is dropped rather than offered (KI-33).
- Never a backfill: deriving `Starting` into an in-progress run would overwrite memory with plan, which D-270 and Planner Rule 4 both forbid. `TrainingRunTest` pins that against a second creation from the same card.

`StoreTrainingRunRequest` also accepts the identity/team/scenario fields used by `runs.update` (see `flows-career-workspace.md`):

- `umamusume_id` required; `character_card_id` nullable and scoped.
- `scenario` nullable, `Rule::in(self::scenarios())`; an empty string is normalised to null (an imported or hand-typed run storing `''` would 500 the run page).
- `status` required (`Active`, `Completed`, `Retired`); `mode` defaults to `NewCareer`.
- `trainee_rarity` 1..3, `potential_level` 1..5, `growth_rate` per stat 0..30.
- `team_name`, `team_motto` (255), `team_league_placement` 1..99, `team_preseason_wins` 0..4.
- `team_stat_grades.*` in `G F E D C B A S` — `S+` is refused with `Per-stat grade must be G-S; S+ is overall team rank only.`
- `unity_trainings_count`, `spirit_bursts_count`, `extreme_bursts_count` (min 0), `current_objective_index`, `shop_resets_in`, `notes` (max 5000).

---

## 5. Independent Training at creation

`StartCareerRequest::independentConfig()` is the only caller that opens an idle window at creation time. The controls that posted it are gone from Preflight, so a normal wizard run creates no overlay; the branch remains for a request that carries the config.

Lifecycle (`app/Services/IndependentTraining/Lifecycle.php`):

- `DURATION_MINUTES = 50`; `started_at = now()`, `completes_at = now()->addMinutes(50)`.
- Inputs: `policy` (`IndependentTrainingPolicy`), `priority_skills` (max 10, `MAX_PRIORITY_SKILLS`), `rotation` (null means the scenario's target races only).
- One at a time — `canStart()` refuses a second window, by the reference's own rule.
- Completion is driven by the scheduled tick `independent-training:complete` (`CompleteIndependentTraining`), not a browser timer.

`Simulator` is explicitly an approximation: the client does not publish its per-turn selection algorithm. Two facts are pinned rather than approximated — growth rate does not affect base stats in this mode, and the hints returned are exactly the priority list (capped at ten).

---

## 6. Which door to use

| Situation | Door |
| --- | --- |
| Starting a career at turn 1 | Wizard (`career.scenario`) |
| Already mid-career in the client | Snapshot (`career.snapshot`) |
| History exists as a spreadsheet | CSV import (`runs.import`) |
| One-off / transitional | `runs.create` |

The wizard and the snapshot are **not** equivalent: the wizard creates a new career by construction, the snapshot carries an imported position. Rounds 2 and 3's R2-01/R2-04 confusion came from walking a source that described an existing career through the wizard door.

---

## 7. Verification surface

| Claim | Pinned by |
| --- | --- |
| Wizard steps, draft round-trips, optional target | `CareerScenarioSelectTest`, `CareerTraineeSelectTest`, `CareerBuildTargetTest`, `CareerLegacyDeckStepsTest`, `CareerPreflightTest`, `CareerStartGrowthRateTest` |
| Snapshot entry, review state words, position | `SnapshotEntryTest`, `SnapshotReviewTest`, `SnapshotCockpitTest`, `SnapshotIdempotencyTest`, `SnapshotScenarioCountdownTest` |
| CSV import contract and copy | `HistoricalRunImportTest`, `ImportErrorCopyTest` |
| Legacy create path and skill seeding | `RunCreateSurfaceTest`, `CatalogStartCareerTest` |
| Independent Training lifecycle | `IndependentTrainingLifecycleTest`, `IndependentTrainingStoreTest`, `IndependentTrainingSimulatorTest`, `IndependentTrainingSchemaTest` |
