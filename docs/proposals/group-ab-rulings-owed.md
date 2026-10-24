# Group A + Group B — rulings owed

**Date.** 2026-10-24. One section per blocked item, two options each, with a recommendation. The owner
rules; nothing in Phase 2 or Phase 3 opens until these are answered. Audit: `docs/audits/group-ab-audit-2026-10-24.md`.

---

### A-0.2 — Does the run store its own aptitude letters, or does the catalog reading suffice?

**Source.** Walk-1 finding D8 documents the aptitude question and was verified **not a tool defect** —
the catalog letters are correct; the frame's were wrong. Walk-3 U-02 and the source §1.1/§1.2 record the
run's aptitude letters. The app holds aptitudes only on the `umamusume` catalog table
(`database/migrations/2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php`); `TrainingRun`
carries no aptitude columns (`app/Models/TrainingRun.php:105`).
**Current state.** Aptitudes are catalog-only and correct; a run's post-inheritance aptitude drift is not
representable.
**Option A — add per-run aptitude columns.** Consequence: a run can record its own letters, but it is a
migration and needs an ADR plus a digest line.
**Option B — keep the catalog reading.** Consequence: the gap stays closed as "verified, not a defect"
(D8), and no schema lands.
**Recommendation.** B — D8 already settled the reading, and per-run aptitude drift is low-value for a
single-Trainer tool.
**Lands in.** nothing (a "will not add" note), or a new ADR if A.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

### A-0.4 — Does the run store the effect values each card has actually reached?

**Source.** Walk-1 Screen 6 (`:309-312`) and walk-3 U-08 document "the per-card 'Client rows visible'
values … the effects this run has actually reached" (§1.4 lines 97-104). The deck builder uses catalog
maxima (`ADR-0014:8`).
**Current state.** Unrecorded; `ADR-0014:8` states it open — "the builder's analysis continues to use
catalog anchors".
**Option A — store per-run reached effects.** Consequence: needs a card-level column first (A-0.5) plus
an effect lookup; a migration and ADR.
**Option B — keep catalog maxima.** Consequence: the deck analysis stays an anchor read, which it
discloses; no schema.
**Recommendation.** Defer to A-0.5 — reached effects are derivable from a stored level, so this rides the
card-level ruling.
**Lands in.** nothing now; a new ADR if A-0.5 lands and A.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

### A-0.5 — Is the run's per-run card level a fact to store, or is it collection state already declined?

**Source.** Walk-1 Screen 6 (`:307-308`) and walk-3 U-08 document "card levels and limit breaks … no
field on any deck surface" (§1.4 "Client level" column).
**Current state.** `ADR-0014:24` declines the **collection** (`user_support_cards` — level, breaks, perk)
as §6.9-cut; `ADR-0014:8` leaves "card levels and limit breaks are still unrecorded" open. `deck_slots`
carries no level column.
**Option A — add `deck_slots.card_level` (per-run).** Consequence: a run records the level its equipped
card sits at, distinct from collection ownership; a migration and ADR.
**Option B — treat card level as collection state, already declined.** Consequence: nothing lands, and
the absence is recorded as a ruling (ADR-0014), not an oversight.
**Recommendation.** A — the source distinguishes "what the card can be" from "what this run has", and the
per-run level is a run fact, not the Trainer's collection.
**Lands in.** a new ADR plus `deck_slots` migration and the deck surfaces.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

### A-0.6 — Does the tool need a roster editor, or is the derived read-out enough?

**Source.** Walk-3 U-09 and probe P8 (`:949`) and walk-4 S08 document "no roster surface exists anywhere
in the flow" (§1.9, §6 item 7).
**Current state.** `TrainingRun::spiritBurstRoster()` (`app/Models/TrainingRun.php:1255`) derives a
read-only roster from turn-event `SpiritBurstPayload` rows; `TeamPanelBuilder::teamSection()`
(`app/Services/Career/TeamPanelBuilder.php:442-452`) ships it. No stored roster, no write path.
**Option A — add roster storage (table or stored payload).** Consequence: a Trainer can record the
18-20 named members; a migration and ADR.
**Option B — keep the derived read-out.** Consequence: the roster stays a display of recorded burst
states, not an editable list; no schema.
**Recommendation.** B — the roster is a display fact, not a decision input, and no walk showed it driving
a choice.
**Lands in.** nothing, or a new ADR if A.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

### A-0.11 — Is the Mood Effect one sourced figure to render, or a per-tier lookup table?

**Source.** Walk-4 §7 item 3 documents the Mood Effect panel at "±20% training and ±4% running"
(§1.7; `docs/UMAMUSUME_REFERENCE.md` §1.1.6). The mood renders as a tier pill only
(`resources/js/components/MoodPill.vue`).
**Current state.** The figure is a single sourced reading in the reference corpus, not in any `config/`
table or code constant; no per-tier percentage table exists.
**Option A — transcribe the one sourced figure (±20% training / ±4% running) into `config('uma')` and
render it beside the mood.** Consequence: the cockpit shows the two sourced numbers; a small, sourced,
read-only addition, no schema.
**Option B — build a per-tier percentage lookup.** Consequence: a fuller surface, but the per-tier figures
are not sourced anywhere this repo reads, so it would be invented or held.
**Recommendation.** A — the source confirms one figure, not a ladder.
**Lands in.** `config/uma.php`, `CockpitController`, `CareerStatePanel.vue` (or a mood cell).
**Blocks.** nothing — filed as KNOWN-ISSUES until ruled.

---

### B-0.1 — Scenario-scoped mandatory races: filter the seeded set, or seed per scenario? (R4-06)

**Source.** Walk-4 F-05 documents a Unity Cup career listing URA Finals names as mandatory (§1.6).
**Current state.** `CockpitController::obligationSlots()` (`app/Http/Controllers/Career/CockpitController.php:667-675`)
and `TeamPanelBuilder::careerGoalSections()` (`app/Services/Career/TeamPanelBuilder.php:86-92`) filter
`forScenario()->where('is_mandatory', true)`; the scenario scoping exists but does not remove seeded
rows the matrix should not show.
**Option A — filter the seeded mandatory set by scenario.** Consequence: a Unity Cup run's obligation list
carries no URA Finals row; no schema change.
**Option B — a new seed migration adding per-scenario mandatory rows.** Consequence: correct per-scenario
obligations, but a seed/schema change and an ADR first.
**Recommendation.** A — the scoping column already exists; filtering is the no-schema fix.
**Lands in.** `CockpitController.php:568-584`, `RaceDecisionController.php:40-44`.
**Blocks.** B-3.1.

---

### B-0.2 — Cleared goals: add completed-race recording, or a goal-state column? (R4-07)

**Source.** Walk-4 F-06 documents "Goals Achieved 0 of 8" derived from nothing (§1.6). The round-4
register recategorised `KI-34`.
**Current state.** `TraineeGoal.php:20-27` (`app/Models/TraineeGoal.php`) states progress is derived from
`race_entries`; `TeamPanelBuilder::objectiveMet()` (`app/Services/Career/TeamPanelBuilder.php:321-359`)
reads completed entries. The gap is an un-recordable completed race, not a missing goal column.
**Option A — add completed-race recording (write path).** Consequence: a completed entry with a placement
feeds the existing derivation; no new column.
**Option B — add a goal-state column.** Consequence: a stored goal state, but it creates a second owner
for a fact `race_entries` already answers, and a migration.
**Recommendation.** A — matches the model's own design (`TraineeGoal.php:20-27`).
**Lands in.** a race-write path (the race decision screen or `StoreRaceEntryRequest`).
**Blocks.** B-3.2.

---

### B-0.3 — Pending turn + date: a "pending" state, or a plain-form date field? (R4-08)

**Source.** Walk-4 F-07 documents a mid-career snapshot forcing a fabricated outcome; the round-4
register names the two shapes.
**Current state.** `StoreTurnEntryRequest` (`app/Http/Requests/StoreTurnEntryRequest.php:74-86`) requires
stats and `turn`; `choice`/`outcome` are required only when `stage` is set (`:142-143`), so the plain
form already accepts a no-outcome row. There is no date input, and `TurnChoice`
(`app/Enums/TurnChoice.php`) has no pending case.
**Option A — add a "pending"/undecided turn state (new `TurnChoice` value or enum).** Consequence: the
guided rail can record an undecided turn; a shape change needing the ruling.
**Option B — add a date field to the snapshot door (`StoreSnapshotRequest`).** Consequence: a snapshot
names year/month directly instead of deriving it from a turn number; no outcome invented.
**Recommendation.** B — the date field answers the snapshot's real need with no vocabulary change.
**Lands in.** `StoreSnapshotRequest`, the snapshot surface.
**Blocks.** B-3.3.

---

### B-0.4 — Scheduled race: a stored "undecided" status, or a derived line? (R4-10)

**Source.** Walk-4 E-09 documents a race being decided offering only `Enter Race` (§1.6).
**Current state.** `RaceEntryStatus` (`app/Enums/RaceEntryStatus.php:12-19`) is
`NotOffered`/`Skipped`/`Entered`/`Completed`; the only write stores `Entered`.
**Option A — add an `Undecided`/`Scheduled` status value.** Consequence: a stored-vocabulary change with
owner approval, and a new state every reader must handle.
**Option B — derive a "scheduled, not yet run" line on the decision screen.** Consequence: the surface
states the fact without touching the enum or its readers.
**Recommendation.** B — derived, no vocabulary change, no new state to leak into stored data.
**Lands in.** `RaceDecision.vue` (a line), no enum change.
**Blocks.** B-3.4.

---

### B-0.5 — Team stat grades: add the column, or keep the named absence? (R4-12)

**Source.** Walk-4 U-02 documents team stat grades A/A/A/S/A having no input (§1.8).
**Current state.** `TeamPanelBuilder::teamSection()` hard-codes `grade => null` for every stat
(`app/Services/Career/TeamPanelBuilder.php:501-506`); `UnityCupPanel.vue:210-225` prints the absence.
**Option A — add a `team_stat_grades` column (or table).** Consequence: the run records the five letters;
a migration, ADR, and digest line.
**Option B — keep the named absence.** Consequence: the panel keeps stating the gap honestly; no schema.
**Recommendation.** A — the grades are source-cited and load-bearing (they explain the Guts Level 5 in
§2.6).
**Lands in.** a new ADR, a migration, `TeamPanelBuilder`, `UnityCupEditor.vue`.
**Blocks.** B-3.5.

---

### B-0.6 — Current Spirit gauge: add the column, or keep the named absence? (R4-14)

**Source.** Walk-4 U-04 documents the Current Spirit gauge having no input (§1.8).
**Current state.** `UnityCupPanel.vue:231-241` mounts `ResourceMeter` with `value: null, max: null` and
the absence sentence; no column or payload reader.
**Option A — add a `current_spirit` column.** Consequence: the gauge reads a recorded value; a migration,
ADR, and digest line.
**Option B — keep the named absence.** Consequence: the gauge stays empty with its reason; no schema.
**Recommendation.** A — it is the one reading that would let burst readiness be computed.
**Lands in.** a new ADR, a migration, `UnityCupPanel.vue`, a writer.
**Blocks.** B-3.6.

---

### B-0.7 — Skill levels: add the column/table, or keep acquisition-only? (R4-16)

**Source.** Walk-4 U-06 documents skill levels (unique Lvl 3, hint levels) having no field (§1.3, §2.1).
**Current state.** `SkillsPlannerController` carries acquisition state only
(`app/Http/Controllers/Career/SkillsPlannerController.php:216-228`); no level column.
**Option A — add a `skill_levels` table or per-run level column.** Consequence: the planner records a
level per acquired skill; a migration, ADR, and digest line.
**Option B — keep acquisition-only.** Consequence: levels stay unrecorded and the screen states it; no
schema.
**Recommendation.** A — the source's hint-discount ladder (§2.1) is keyed by level, so levels are the
missing input, not a nicety.
**Lands in.** a new ADR, a migration, `SkillsPlannerController`, `SkillsPlanner.vue`.
**Blocks.** B-3.7.

---

### B-0.8 — Borrowed-card owner: add the identity column, or "will not add"? (R4-18)

**Source.** Walk-4 U-08 documents the friend card's owner account being unrepresentable beyond `Rented`
(§1.4).
**Current state.** `deck_slots.ownership` (migration `2026_10_09_120000_add_ownership_to_deck_slots_table.php:24`)
records `OWNED`/`RENTED`/nothing; no account or identity column.
**Option A — add an owner-identity column.** Consequence: the borrowed card names whose account it came
from; a migration and ADR, and a field a loopback tool has no second account to fill.
**Option B — "will not add".** Consequence: the `Rented` flag stays the whole record; the absence is a
ruling, not an oversight.
**Recommendation.** B — a single-Trainer, loopback-only tool has no other account identity to record.
**Lands in.** nothing (a "will not add" note), or a new ADR if A.
**Blocks.** B-3.8.