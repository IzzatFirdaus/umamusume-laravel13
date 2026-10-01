# ADR-0003: Consolidated Phase 1 schema expansion for Energy, Fans, events and races

Status: **accepted by owner 2026-09-27.** Absorbs the pending items in `ADR-0001` and `ADR-0002`.
Deciders: product owner (approval and US-10 promotion), design consultant (column definitions and consequences)
Supersedes: `ADR-0001` §5 (schema), `ADR-0002` recommendation
Related: `PRD.md` US-3, US-4, US-10, FR-C; `CLAUDE.md` Planner Rules 4, 5, 6

## Owner decisions recorded

1. **US-10 promoted from P2 to P1.** Reason given: a companion tool that cannot manage the race calendar and fan gating does not serve the micromanagement loop the game is built on.
2. **Energy, TurnEvent and Race/Fan tracking bundled into one amendment**, with column names defined here rather than deferred.
3. **Summer Camp corrected by the owner.** The earlier "choose 2 disciplines" instruction was a misremembering; §1.1.2 stands. Screen D is a generic multi-step scenario event, not a hardcoded camp picker (CONSTRAINTS D-135).
4. **The maiden gate is a distinct lock state** from the fan gate, visually and mechanically. *Clarified 2026-09-27:* "mechanically distinct" means the two are different **kinds** of predicate, not two values of one. The maiden gate is a boolean over race history (`scenario_races.maiden_gated` below), and the fan gate is a numeric comparison against `fans_needed`. Collapsing them into a single `introductory_race_cleared` flag would delete the threshold, so the shape below is already the simplification that survives contact with the data; the visual half of the claim rests separately on D-152, D-173, G-16b and G-28, which are client observations and do not depend on this argument.
5. **`scenario_races` is generalised to `scenario_slots` with a `kind` discriminator** (owner ruling, 2026-09-27, after the scenario work). Reason: **Trackblazer has no mandatory race goals at all.** A table named for races cannot hold a Grade Point deadline, and the tool must not break on the third scenario. `kind` takes `GoalRace`, `TeamRace`, `GradeDeadline` or `ScriptedEvent`, so one table and one timeline view serve all four scenarios. The section below keeps its original name and columns as the `GoalRace` case; the rename is a schema task, not a redesign, and it is **unfunded work** logged here so it is not lost.
   - **Read this reason narrowly, because the loose wording was challenged on 2026-09-27.** Trackblazer has no mandatory *race* goals — nothing designates "win the Satsuki Sho" — but it has three **mandatory Grade Point thresholds**, and those are objectives with the same run-ending force as a missed goal race: End of Junior Year 60 GP, End of Classic Year 300 GP, End of Senior Year 300 GP, with points consumed at each deadline and **no carry-over** (`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Grade Points (Replacing Career Goals)"). Because racing is the only GP source, the deadline and the race schedule are one system, which is exactly why `kind = GradeDeadline` exists. Two requirements follow for whoever writes the `scenario_slots` definition:
     - **Amended 2026-09-28.** An earlier revision of this bullet required the `GradeDeadline` row to carry a **threshold** and a **deadline turn**. That clause contradicts Ruling R1 item 1, which makes `config/scenarios.php` the authoritative source for grade objectives, and it is withdrawn: the slot row carries **calendar position and gates**, the figures stay in config, and no `threshold` or `track` column is to be added. The requirement that survives is the one the schema cannot skip — a deadline must be able to be **missed**, which is a state on the Trainer's side of the relationship, not a column on the reference row.
     - The threshold is **not one number per scenario**. There are four objectives (Debut race by Late June of Junior Year, then 60 / 300 / 300 Grade Points at the ends of Junior, Classic and Senior), and the three point thresholds split into **three tracks, not two** — `standard` 60 / 300 / 300, `dirt_leaning` 30 / 200 / 300 for a high-dirt or low-turf character such as Haru Urara, and `limited_turf_range` 60 / 200 / 300 for a turf character whose range outside short distances is weak, such as Curren Chan (`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` sections "Grade Points (Replacing Career Goals)" and "Basic Information", and `config/scenarios.php` `grade_objectives`, which carries all three). An earlier revision of this bullet named two tracks and omitted the plain-turf 60 / 300 / 300 case; a UI that rendered `standard` for a dirt-leaning trainee would show an impossible target. Surplus points do not carry between periods, so each objective is judged against zero.
     - **Missing a threshold ends the career** (owner-supplied from in-game observation, 2026-09-27). It is a hard fail: the run terminates to the career-end screen and the player either accepts retirement or spends an **Alarm Clock** to retry. The item corroborates the category — `items.json` id 95, `[Global]` "Alarm Clock" / 目覚まし時計, client text "Lets you try again on a Career goal race", already recorded against a missed mandatory placing and a lost Team Race. ❌ Still unverified: **where** a retry resumes; "start of that semester" is recollection, not a sourced value, and the schema must not encode a resume point. What the schema *does* need is that a missed deadline is terminal rather than a deduction, so the `GradeDeadline` row's states are pending / met / **missed-terminal**, not met / unmet.
6. **The stat bound moves to 0..2000** (owner ruling, 2026-09-27). `ADR-0002` option B, on the ground that `scenarios.json` carries `hard_caps = 2000` as a real field and the current 0..1200 bound has been rejecting live Global runs since the 2026-07-01 rework. The UI must keep the 1,200 halved-gains line and the scenario ceiling as two visible markers rather than silently raising one number; see `ADR-0002` amendment 2 and `DESIGN.md` §6.5.
   - **Amended 2026-09-27: 2000 is a value, not the rule.** The export carries `hard_caps = 2500` for scenarios 13 and 14 (`Beyond Dreams` and `らっしゃい！トレセン軒！`), so a flat `0..2000` constant is wrong in principle even though it is right for every scenario live on `[Global]` — both of those rows have `start_en = null`, i.e. they are `[JP]`-only, and this tool's audience is Global. The bound must therefore be **read from the scenario's own stored `scenarios.hard_cap`**, with 2000 as the value that happens to apply to all four Global scenarios today. This costs nothing to do correctly because the column already exists and the parser already populates it; it is only the *constant* that would be a mistake. Note also that `hard_caps` has a sixth element (9999, or 99999 on the newest `[JP]` rows) whose meaning is unverified — `ADR-0002` forbids labelling it as any stat on the strength of position, and it must not be read as a stat ceiling either.

## Design principle behind every column below

The schema already stores **absolute per-turn values**, not deltas: `turn_entries.speed` is the Speed the Trainee holds at that turn, which is why an injury drop is representable as 302 then 294 with no extra machinery. Energy and Fans follow the same rule.

**Store the end-of-turn total, not the delta.** Three reasons: it matches the existing convention, so there is one mental model for every numeric column; a delta series cannot answer "what was the Energy at the start of turn 14" without a summation that depends on every prior row being present and correct; and a missing or corrected middle turn silently corrupts every later derived total, whereas an absolute value is wrong only for itself. Deltas remain available as a presentation-layer subtraction, which is where they belong.

## Schema

### `turn_entries` additions

| Column | Type | Null | Rationale |
|---|---|---|---|
| `energy` | `unsignedSmallInteger` | yes | End-of-turn total, 0..100. Nullable because historical runs were logged without it and must not be backfilled with guesses |
| `fans` | `unsignedInteger` | yes | End-of-turn running total. Drives eligibility display |
| `mood` | `string`, cast to `MoodTier` enum | yes | Replaces the free-text `condition` as the structured mood home |

`condition` stays as free text for notes. `mood` is the queryable enum. The overlap is deliberate and temporary: retiring `condition` in the same change would break the existing run views for no functional gain.

**The `MoodTier` case names are unblocked as of 2026-09-27.** This paragraph recorded them as blocked, and the wording is kept so the reversal is auditable: `UMAMUSUME_REFERENCE.md` §1.1.6 records the Global client strings for the five states as `❌ UNVERIFIED`; the corpus evidences only `GREAT` and `GOOD`. The enum is defined with neutral cases (`Peak`, `Good`, `Normal`, `Poor`, `Worst`) and the display labels stay unset until captured from the client. CONSTRAINTS D-20 and D-138 apply. **What changed:** the owner supplied a capture of the client's own Mood Effect panel, which prints all five tiers and their arrows, and the cases are `Great` / `Good` / `Normal` / `Bad` / `Awful`. `Peak`, `Poor` and `Worst` are Game8's glosses of 絶好調 / 不調 / 絶不調 and are not client copy, so a migration written against the list above ships enum labels the game never displays. D-20 now records the resolution, D-203 is withdrawn, and `docs/design-research/DESIGN.md` §6.17 carries the strings with their effects. The labels are no longer unset; the three lower pills' *colours* are still derived rather than measured.

### `turn_events` (new)

One row per event the Trainer resolved, whatever fired it.

| Column | Purpose |
|---|---|
| `training_run_id` FK cascade, `turn` | locates the event, same key shape as `turn_entries` |
| `event_type` enum-backed | `Character`, `SupportCard`, `Group`, `Scenario` |
| `source_name` | event title as displayed |
| `choice_index`, `choice_label` | which option was taken, and its text at the time |
| `deltas` json | stat, SP, Energy, Mood and Fan changes **as observed** |
| `support_card_name`, `bond_delta` | nullable, support-card events only |
| `origin_note` | nullable free text, so an event fitting no category is recorded honestly |

`deltas` stores observed outcomes only. `turn_entries` remains the source of truth for absolute values; a mismatch between the two is a defect with a named owner, not a second opinion.

### `scenario_races` (new, reference data)

The fixed calendar. §1.6.1 confirms the scenario dictates the target-race calendar, and `ura-races.json` in the data export carries `fans_needed` and `fans_gain` per row, so this is real, citable data rather than a guess.

| Column | Purpose |
|---|---|
| `scenario_key`, `slot_label` | e.g. `ura-finale` / `Classic Year Late May` |
| `race_name`, `tier` | display name and tier label |
| `fans_needed` | the gate value, shown on locked cells |
| `mandatory` | bool, the Goal flag |
| `maiden_gated` | bool, the conditional Debut/Maiden lock |
| provenance fields | `url`, `fetched_at`, `snapshot_path`, `source_timezone`, per FR-A-4 |

**Provenance is not optional here.** Every cap and `fans_needed` value traces to sources flagged `⚠️ STALE` (Game8 2025-11-21, Kamigame 2024-02-19), and Grand Masters and L'Arc are marked "single-source, not corroborated". The Data Engineer rule is that a fact without provenance is deleted rather than stored, so a seeded calendar row without a source is a rule violation on arrival.

### `race_entries` (new)

What the Trainer actually did with each calendar slot.

| Column | Purpose |
|---|---|
| `training_run_id` FK cascade, `scenario_race_id` FK | the slot |
| `status` enum-backed | `NotOffered`, `Skipped`, `Entered`, `Completed` |
| `placement` | nullable, recorded result |
| `fans_gain` | nullable, observed |

`Skipped` being a stored status is the point: the owner required that skipping be a first-class action, and a first-class action needs a first-class value. A nullable-absent row cannot distinguish "not offered" from "declined", which is exactly the distinction the calendar UI renders.

## What this does not license

- **No race outcome prediction.** `PRD.md` §6.11 remains in force on that point; `ADR-0001` lifted the non-goal for Energy guidance only. Eligibility is arithmetic over stored values and is permitted. Projected placing, win probability and ranked race recommendations are not.
- **No failure percentage.** No source publishes a curve. Risk renders as Safe / Caution / Danger bands.
- **No tier inference from grade codes.** Only 100 = G1 and 400 = OP are client-confirmed. `scenario_races.tier` stores the label as recorded, never a derived value.
- **No support-card or breakthrough cap maths.** §1.3.4 makes a run's true ceiling scenario base plus breakthrough plus deck effects, and neither input is in Phase 1's schema. The cap display discloses what it is not accounting for (D-162).

## Consequences for the validation bound

`ADR-0002` established that the 0..1200 stat bound makes P0 US-3 unable to record a real run. With US-10 now P1 and scenario caps a first-class UI concern, that bound is no longer defensible as-is. Two things must land together with this schema:

1. The per-stat bound becomes **scenario-derived**, with `scenario_races`-style reference data supplying the caps, rather than a flat constant in `StoreTurnEntryRequest`.
2. Until that lands, a rejected value must produce an error naming **the tool's** limitation, never implying a game ceiling (D-31, G-15c).

This is a `StoreTurnEntryRequest` change, and Planner Rule 6 says bounds live in that one place. It stays true; only the source of the number moves.

## Work required before implementation

- `PRD.md`: promote US-10 to P1, add FR-C-6 for Energy and Fans, FR-C-7 for event and race logging, and amend §6.11 to the narrower race-outcome prohibition.
- `CLAUDE.md`: Planner Rule 4 needs the ADR-0001 §2 replacement wording (entered turns plus a declared, versioned constant set). Rule 6 needs the bound-source change above.
- `AGENTS.md`: the Planner Domain Specialist line asserting "no race predictions" needs the same narrowing, and the new columns need their FR citations recorded there.
- Re-verify the stale cap and `fans_needed` sources before seeding.

## Open items this leaves with the owner

| Item | Blocker |
|---|---|
| ~~`MoodTier` display labels~~ | **Resolved 2026-09-27**, not a blocker: the client's Mood Effect panel prints all five strings (`GREAT`/`GOOD`/`NORMAL`/`BAD`/`AWFUL`), see §`turn_entries` above and `DESIGN.md` §6.17 |
| Races per turn | "2 or 3" has no source in this repo |
| Senior-year Arima Kinen as a hard requirement | No source in this repo |
| Scenario-specific chrome | The entire 1,160-frame corpus is Unity Cup; there is no visual evidence for the other three scenarios' UI |

---

## Amendment R1 — 2026-09-27: Timeline source-of-truth ruling

**Ruling R1** (owner, following the scenario-slot migration and the PRD US-10 promotion):

1. **config = composition descriptors.** `config/scenarios.php` is the authoritative source for panel flags (`panels.*`), step lists (`steps`), grade objectives, and the shop catalogue. These are *composition descriptors* — they describe which UI pieces exist for a scenario. They are not a data store.

2. **scenario_slots = forward timeline store.** The `scenario_slots` table (created 2026-09-27 15:34, migration `2026_09_27_153416`) is the single canonical store for every timeline slot across all scenarios. Its `kind` discriminator (`goal_race`, `team_race`, `grade_deadline`, `scripted_event`) absorbs what `scenario_races` and the Trackblazer/Unity Cup logic previously split. Every slot carries provenance (`source_url`, `snapshot_path`, `fetched_at`, `source_timezone`) per FR-A-4 and the Data Engineer rule.

3. **scenario_races deprecated and frozen.** The `scenario_races` table and its new `is_manual` column (migration `2026_09_27_132304`) are frozen. No new rows, no queries against it in new code. The column is marked unused in this ruling. Existing data stays for rollback; a future migration may drop it once `scenario_slots` is fully wired.

4. **RaceEntry rewire deferred to Slice 2, test-first.** `RaceEntry` currently relates to both `scenario_race_id` (fillable, factory) and `scenario_slot_id` (FK, nullable). The rewire — making `scenario_slot_id` the sole reference and dropping `scenario_race_id` — is deferred to the next slice. It will land with:
   - a migration dropping `scenario_race_id` and its factory
   - a `RaceEntry` model change removing the `scenarioRace` relation
   - a feature test asserting `RaceEntry` is created via `scenario_slot_id` only

5. **Provenance exemption for owner-transcribed config values.** The shop catalogue and grade objectives in `config/scenarios.php` are transcribed from the Trackblazer sections of `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` with code-comment citations (e.g., lines 197–217). These do **not** carry the `source_url` / `snapshot_path` columns the schema requires for engine-written facts. This is an explicit, time-limited exemption: the values are accepted as "owner-transcribed" pending a fetch-source decision (PRD OQ-2). They are not engine-written, so they do not violate FR-B-4. A future fetch pipeline that ingests scenario data must supersede them with provenance-carrying rows.

## Formal record, 2026-09-27

`scenario_slots` is **approved by the owner** and recorded here so the file matches the decision. The
column definitions themselves are not in this document and the implementing agent did not invent them;
the definition still needs to be written down before that column is migrated.

Companion work recorded elsewhere: `ADR-0004` covers the ten aptitude letters and the `scenarios`
reference table, and closes `PRD.md` OQ-4 for those two domains only.

---

## Amendment R2 — 2026-09-28: what Slice 2's S1 actually found, and what it did not do

R1.4 listed three things the `RaceEntry` rewire would land with: a migration dropping
`scenario_race_id` and its factory, a model change removing the `scenarioRace` relation,
and a test asserting a race entry is created via `scenario_slot_id` **only**. Slice 2
executed none of those three as written, and the reason is measured rather than
preferential. This amendment records that so the ADR and the tree agree.

**`scenario_slot_id` stays nullable, deliberately.** Four independent findings:

1. **No source columns.** `scenario_slots` requires `month` (1-12, NOT NULL), `half`
   (enum Early/Late, NOT NULL) and `kind`, under a unique composite on
   (`scenario_key`, `month`, `half`, `kind`). `scenario_races` has none of the three —
   `Schema::getColumnListing()` returns `slot_label`, a free-text field whose factory
   value is `'Classic Year Late May'`. Reading a month, a half and a discriminator out of
   that string is inventing client data, which D-20 forbids.
2. **Nothing to move.** `scenario_races` holds 0 rows in the development database and 0
   rows after a clean `migrate` + `db:seed`: `DatabaseSeeder` calls `UmamusumeSeeder` and
   `SkillSeeder` only. A backfill is vacuous by construction.
3. **SQLite cannot do it in place.** Measured on a scratch copy of the real database,
   SQLite 3.49.1: `ALTER TABLE … ADD COLUMN … NOT NULL` without a default fails with
   "Cannot add a NOT NULL column with default value NULL", and `DROP COLUMN
   scenario_slot_id` fails with "unknown column … in foreign key definition". The
   two-step plan has no in-place form on this engine.
4. **NOT NULL would break a scenario.** `race_calendar` is true for `ura_finale` and
   `unity_cup` only; Trackblazer has no fixed race list and derives Grade Points from the
   race log the Trainer keeps. A mandatory slot reference makes a Trainer-picked race
   impossible to store, which is exactly what D-221 exists to prevent.

**What S1 did land** (`9134206`): `scenario_slot_id` was absent from `RaceEntry`'s
`#[Fillable]`, so `RaceEntry::create([...])` dropped it in silence — the INSERT ran
without the column, the `scenarioSlot()` relation could never resolve, and the rewire was
a schema with no way in. That is fixed, and the nullability contract is pinned by tests
rather than by this prose.

**R1.3 was being violated by the factory, and is not any more** (`9451659`): `RaceEntryFactory` defaulted `scenario_race_id` to `ScenarioRace::factory()`,
so every factory-built race entry wrote a row into the frozen table — including Trackblazer
entries that have no calendar race. The default is now `null`. `Adr0003SchemaTest` still
covers the retired column by naming it explicitly, including its `nullOnDelete` behaviour,
so the freeze holds without deleting the history that proves it.

**R1.4's third bullet is corrected as written.** "created via `scenario_slot_id` only"
describes the common case and forbids the necessary one. What is true, and now tested in
`tests/Feature/Schema/RaceEntrySlotLinkTest.php`, is: a slot-linked entry needs no
`scenario_races` row, and a free-form entry needs no slot. Both are storable; neither is
invented.

**Dropped from scope, with reasons recorded.** Removing `scenario_race_id` and the
`scenarioRace` relation is still the destination, but it is blocked behind (a) a populated
`scenario_slots` having no populated rows outside a factory — the empty
`ScenarioSlotSeeder` stub was deleted in Slice 3's T5, and slot population is recorded
below as fetch-engine work —
and (b) a decision about what a Trackblazer race points at once `gradeEarned()` can be
attributed to an objective (KI-10). A migration that drops a column two tests still
exercise, on a table that has never held a row outside a factory, would be schema theatre.

---

## Amendment R3 — 2026-09-28: `scenario_slots` is populated by the fetch engine, not a seeder

Slice 3's T5 (R14) asked whether `ScenarioSlotSeeder` should be filled with URA Finale
goal-race slots, conditional on every row carrying a server qualifier and a source date
(D-227). The condition fails, so the stub is deleted and the rule is recorded here.

**Why it cannot be filled today.** `docs/scenarios/01-ura-finale.md` contains no goal-race
list. The only race row in the whole guide is `Junior Make Debut (race) | After 11 turns |
Mandatory debut race` (line 87). There is no Oka Sho, no fan threshold, no month-and-half
placement for any URA target — so a seeder would have to author the `month`, `half`,
`tier` and `fans_needed` values that `scenario_slots` requires, which is inventing client
data (D-20) and attaching no provenance to it (D-227). The race names that appear in this
repository's tests and browser fixtures are factory sample data, and none of them is
sourced as a URA goal race with a gate figure.

**Where the rows come from instead.** The ADR-0004 pattern, which this table already
follows for caps: reference data arrives through the fetch engine with `source_url`,
`snapshot_path`, `fetched_at` and `source_timezone` populated, and `is_manual` reserved for
Trainer-entered rows. `scenario_slots` carries all five columns already, so the schema is
not the blocker — the source is. Slot population is therefore Data Engineer work against a
declared fetch source, and the escalation in `AGENTS.md` applies: an uncertain source is
stopped and surfaced, not seeded around.

**What this does not unblock.** R2's decision to keep `race_entries.scenario_slot_id`
nullable stands on its own grounds: Trackblazer's Trainer-chosen races have no slot in any
future, sourced version of this table either.
