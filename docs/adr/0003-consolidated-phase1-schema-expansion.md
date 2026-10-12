# ADR-0003: Consolidated Phase 1 schema expansion for Energy, Fans, events and races

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-27) — Energy, TurnEvent, race/slot and race-entry tables land as one amendment
  code: built — migrations 2026_09_26_162818 (turn_entries), 2026_09_27_153416 (scenario_slots), tests/Feature/Adr0003SchemaTest.php
  superseded-by: —
  amended-by: 0009 (R3 narrowed: URA Finale slot seeding became Option A), 0015 (decision 6's bound, read per-stat), 0024 (adds energy_state/energy_band beside turn_entries.energy)
  open: RaceEntry's retired scenario_race_id column is still to be dropped, blocked on a populated scenario_slots and the Trackblazer attribution question (KI-10)
-->

Status: **accepted by owner 2026-09-27.** Absorbs the pending items in `ADR-0001` and `ADR-0002`.

## Decision

**Store the end-of-turn total, not the delta.** The schema already stores absolute per-turn values, so
`turn_entries.speed` is the Speed the Trainee holds at that turn; Energy and Fans follow the same rule. A
delta series cannot answer "what was the Energy at the start of turn 14" without a summation that depends
on every prior row being present and correct, and a missing or corrected middle turn silently corrupts
every later derived total. Deltas remain a presentation-layer subtraction.

The tables this ruling landed are `turn_entries` (`energy`, `fans`, `mood`), `turn_events`, `scenario_slots`
(generalised from `scenario_races` with a `kind` discriminator) and `race_entries`. The column definitions
are owned by the migrations; they are not restated here. `App\Enums\MoodTier` owns the mood vocabulary and
`App\Enums\TurnEventType` the event kinds.

Owner decisions recorded: US-10 promoted from P2 to P1; Energy, TurnEvent and Race/Fan tracking bundled
into one amendment; Summer Camp corrected by the owner; the maiden gate is a **distinct kind** of
predicate from the fan gate (a boolean over race history versus a numeric threshold); `scenario_races`
generalised to `scenario_slots` because Trackblazer has no mandatory race goals but does carry three
mandatory Grade Point thresholds.

**The stat bound moves to 0..2000** (decision 6), read from the run's own scenario rather than as a flat
constant, with 2000 as the value that applies to all four Global scenarios today. `ADR-0015` implemented
this by reading the per-stat cap.

## What this does not license

- **No race outcome prediction.** `PRD.md` §6.11 remains in force on that point; `ADR-0001` lifted the
  non-goal for Energy guidance only. Eligibility is arithmetic over stored values and is permitted;
  projected placing, win probability and ranked race recommendations are not.
- **No failure percentage.** No source publishes a curve. Risk renders as Safe / Caution / Danger bands.
- **No tier inference from grade codes.** Only 100 = G1 and 400 = OP are client-confirmed; the label is
  stored as recorded, never derived.
- **No support-card or breakthrough cap maths.** Neither input is in Phase 1's schema, and the cap display
  discloses what it is not accounting for (D-162).
- **`config/scenarios.php` is not a data store.** It owns composition descriptors (panels, steps, grade
  objectives, shop catalogue), not timeline rows.

## Amendment R1 — 2026-09-27: timeline source-of-truth

1. **config = composition descriptors** (panels, steps, grade objectives, shop catalogue), not a data store.
2. **`scenario_slots` = forward timeline store** (migration `2026_09_27_153416`), the single canonical store
   for every timeline slot across all scenarios, its `kind` discriminator absorbing what `scenario_races`
   and the per-scenario logic previously split. Every slot carries provenance per FR-A-4.
3. **`scenario_races` deprecated and frozen.** No new rows, no new-code queries; existing data stays for
   rollback.
4. **`RaceEntry` rewire deferred, test-first.** The rewire lands with a migration dropping
   `scenario_race_id`, the model change removing the relation, and a feature test.
5. **Provenance exemption for owner-transcribed config values.** The shop catalogue and grade objectives
   in `config/scenarios.php` are transcribed from
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` with code-comment citations. This is an
   explicit, time-limited exemption pending a fetch-source decision (PRD OQ-2): they are not
   engine-written, so they do not violate FR-B-4, and a future fetch pipeline must supersede them with
   provenance-carrying rows.

## Amendment R2 — 2026-09-28: what Slice 2's S1 found

**`scenario_slot_id` stays nullable, deliberately**, on four findings: `scenario_slots` requires `month`,
`half` and `kind` that `scenario_races` has no column for, so reading them out of a free-text `slot_label`
would be inventing client data (D-20); `scenario_races` holds zero rows in the development database and
after a clean seed, so a backfill is vacuous; SQLite cannot do it in place (a NOT NULL column without a
default fails, and dropping a column in an FK definition fails); and NOT NULL would break Trackblazer,
whose free races have no slot.

`scenario_slot_id` had been absent from `RaceEntry`'s `#[Fillable]`, so the relation could never resolve —
fixed, and the nullability contract is pinned by tests rather than by prose.
`RaceEntryFactory` defaulted `scenario_race_id` to a factory row, writing into the frozen table; the
default is now `null`.

R1.4's third bullet ("created via `scenario_slot_id` only") is corrected: what is true, and tested in
`tests/Feature/Schema/RaceEntrySlotLinkTest.php`, is that a slot-linked entry needs no `scenario_races` row
and a free-form entry needs no slot. Dropping the retired column stays the destination, blocked behind a
populated `scenario_slots` and the Trackblazer attribution question (KI-10).

## Amendment R3 — 2026-09-28: `scenario_slots` is populated by the fetch engine, not a seeder

Slice 3's T5 (R14) asked whether `ScenarioSlotSeeder` should be filled with URA Finale goal-race slots,
conditional on every row carrying a server qualifier and a source date (D-227). The condition failed, so
the stub was deleted.

**Why it could not be filled.** `docs/scenarios/01-ura-finale.md` contains no goal-race list — no Oka Sho,
no fan threshold, no month-and-half placement for any URA target — so a seeder would have had to author the
`month`, `half`, `tier` and `fans_needed` values, which is inventing client data (D-20) with no provenance
attached (D-227). The race names in this repository's tests are factory sample data, and none is sourced as
a URA goal race with a gate figure.

**Where the rows come from instead.** The `ADR-0004` pattern: reference data arrives through the fetch
engine with `source_url`, `snapshot_path`, `fetched_at` and `source_timezone` populated, and `is_manual`
reserved for Trainer-entered rows. The schema carries all five columns already, so the schema was never
the blocker — the source is. Slot population is therefore Data Engineer work against a declared fetch
source, and an uncertain source is stopped and surfaced, not seeded around.

R2's decision to keep `race_entries.scenario_slot_id` nullable stands on its own grounds: Trackblazer's
Trainer-chosen races have no slot in any future, sourced version of this table either.

## Links

- `ADR-0004` (aptitudes and the `scenarios` table), `ADR-0001` (its §5 schema lives here), `ADR-0002`
  (bound), `ADR-0009` (slot seeding, which narrowed R3), `ADR-0015` (decision 6)
- `PRD.md` US-3, US-4, US-10, FR-C, OQ-4, §6.11; `docs/UMAMUSUME_REFERENCE.md` §1.3.4
- `tests/Feature/Adr0003SchemaTest.php`, `tests/Feature/Schema/RaceEntrySlotLinkTest.php`
- `config/scenarios.php` (`grade_objectives` carries all three Grade Point tracks)

## Ledger

- 2026-09-28 · **R3 narrowed by `ADR-0009`** — Slice 11 built `ScenarioSlotSeeder` reading committed JSON
  for **URA Finale only** (Option A, R55). The 2026-09-28 reason ("no goal-race list exists") held for the
  other three scenarios and for the fetch-engine path, both of which still await an owner decision. The
  R3 floor that survives is D-20 / D-227 provenance, not the refusal of a seeder.
- 2026-09-28 · **`GradeDeadline` clause withdrawn** — an earlier revision required the row to carry a
  **threshold** and a **deadline turn**. That contradicted Ruling R1 item 1, which makes
  `config/scenarios.php` the authoritative source for grade objectives. What survives is that a deadline
  must be able to be **missed**, which is a state on the Trainer's side, not a column.
- 2026-09-27 · **mood-tier labels unblocked** — the paragraph recording the five `MoodTier` cases as
  unverified was reversed the same day when the owner supplied the client's Mood Effect panel. The cases
  are the client strings in `App\Enums\MoodTier`; the three lower pills' colours remain derived, not
  measured.
- 2026-09-27 · **Grade Point tracks corrected** — an earlier revision named two tracks and omitted the
  plain-turf case. There are three (`standard`, `dirt_leaning`, `limited_turf_range`), and a UI rendering
  `standard` for a dirt-leaning trainee would show an impossible target. `config/scenarios.php`
  `grade_objectives` carries all three.
- 2026-09-27 · **maiden gate clarified** — "mechanically distinct" means the two gates are different
  **kinds** of predicate, not two values of one: a boolean over race history versus a numeric comparison.
  Collapsing them would delete the fan threshold.
- 2026-09-27 · **formal record** — `scenario_slots` is approved by the owner and recorded so the file
  matched the decision; the column definitions live in the migration, and the implementing agent did not
  invent them.
