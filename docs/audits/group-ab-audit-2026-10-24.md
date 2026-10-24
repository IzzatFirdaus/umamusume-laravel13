# Group A + Group B audit — missing functions, fourth-pass closeout

**Audit date.** 2026-10-24.
**Scope.** The 19 items the four UX walks documented as missing and that no reconciliation round has
claimed: 11 Group A items (walk-1/2/3 "source data with no UI home", keyed by walk-3's `U-01`…`U-12`
register) and 8 Group B items (the round-4 register's owner-call rows `R4-06`, `R4-07`, `R4-08`,
`R4-10`, `R4-12`, `R4-14`, `R4-16`, `R4-18`). Everything already landed is excluded and recorded, not
re-litigated.

**HEAD.** `3cdc466c` (`fix(career/trainee): preserve the filter through the select redirect (R4-23)`),
the R4 Phase 1 tip. The round-4 register was written at `f6018f87`; the diff `f6018f87..3cdc466c`
touches 24 files and none of them is a Group B owner (no `TeamPanelBuilder.php`, `UnityCupPanel.vue`,
`RaceEntryStatus.php`, `TraineeGoal.php`, `StoreTurnEntryRequest.php`, or any migration). The register's
Group B citations therefore still hold; each is re-read here at HEAD, not restated from the register.

**Method.** Read-only. For every item: read the code at HEAD at the file the walk cites, determine the
current state against the walk's observation, and classify into `openable`, `needs-schema`,
`needs-shape-decision`, `ruled-out`, or `landed`. No file under `app/`, `resources/`, `routes/`,
`tests/`, `database/` or `config/` was touched. The only files this pass creates are the audit doc and
the two plan docs.

## Cross-walk mapping

| Item (ID) | Walk-3 register | Walk-1 screen | Round-4 row | Source section |
|---|---|---|---|---|
| Cap composition — A-0.1 | `U-01` | Screen 4 (`:172-180`), Screen 8 | — | §1.2 lines 39-56 |
| Run-stored aptitudes — A-0.2 | D8 (verified) | Screen 3 | — | §1.1/§1.2 aptitude letters |
| Owned-skill inventory — A-0.3 | `U-07` | Screen 10 (`:514-516`) | — | §1.3 lines 63-67 |
| Reached effect values — A-0.4 | `U-08` | Screen 6 (`:309-312`) | — | §1.4 lines 97-104 |
| Card client levels — A-0.5 | `U-08` | Screen 6 (`:307-308`) | — | §1.4 "Client level" column |
| Roster editor — A-0.6 | `U-09`, P8 | Screen 8 | — | §1.9, §6 item 7 |
| +30 ranking bonus — A-0.7 | `U-09` | Screen 8 (`:398-399`) | — | §1.8, §7 item 5 |
| Purchasable pool — A-0.8 | `U-07` | Screen 10 (`:517-519`) | — | §2.2, §2.3 |
| Burst band position — A-0.9 | `U-09` | Screen 8 (`:400-415`) | — | §2.4, §8.2 |
| Top Star threshold — A-0.10 | — | Screen 8 (`:392-393`) | — | §1.1 fans |
| Mood Effect percentages — A-0.11 | — | Screen 8 | — | §1.7, §7 item 3 |
| Scenario mandatory races — B-0.1 | — | Screen 12 | `R4-06` | §1.6 |
| Cleared-goal recording — B-0.2 | `U-11` | Screen 13 | `R4-07` | §1.6 goals |
| Pending-turn + date — B-0.3 | `F-07` | Screen 9 | `R4-08` | §1.7 turn |
| Scheduled-race state — B-0.4 | `E-09` | Screen 12 | `R4-10` | §1.6 race card |
| Team stat grades — B-0.5 | `U-09` | Screen 8 | `R4-12` | §1.8 grades |
| Current Spirit gauge — B-0.6 | — | Screen 8 | `R4-14` | §1.8 |
| Skill levels — B-0.7 | `U-06` | Screen 10 | `R4-16` | §1.3, §2.1 |
| Borrowed-card owner — B-0.8 | `U-08` | Screen 6 | `R4-18` | §1.4 friend card |

## Per-item findings

### A-0.1 — Cap composition read-out

- **Walk-4 ID / first doc.** Walk-3 `U-01`; walk-1 Screen 4 "Source data with no UI home" records the
  caps "exceed the form's maximum and cannot be entered at all" and that "§1.2's whole cap-arithmetic
  discussion has no representation".
- **Source.** §1.2 lines 39-56 — the six blue sparks plus any support-card Max-Stat layer that compose
  the run's per-stat caps; §1.2's open question (sparks account for Guts +8 and nothing else).
- **Code state at HEAD.** The caps are stored and shipped: `CockpitController::identitySection()`
  (`app/Http/Controllers/Career/CockpitController.php:394-417`) returns `caps` as
  `{entered, scenario}` per stat. The sparks are stored in `training_runs.legacy_selection` (JSON,
  migration `2026_09_29_012615_add_legacy_selection_to_training_runs.php:35`), shaped by
  `LegacySelectionPayload` (`app/Models/Legacy/LegacySelectionPayload.php:45,63`), and read by
  `InheritanceEventController::predictedSection()` (`app/Http/Controllers/Career/InheritanceEventController.php:61,74,123-149`).
  **No surface shows the two side by side.** The cockpit renders the caps (`identity.caps`), and the
  Inheritance screen renders the sparks, but no panel places the run's caps beside the six blue sparks.
- **Classification.** `openable`. A read-only display: the caps plus the sparks from the run's own
  `legacy_selection`, with the panel stating the two do not reconcile (§1.2's own open question). No
  schema, no shape ruling.

### A-0.2 — Run-stored aptitudes

- **First doc.** Walk-1 finding D8 (`rice-shower-unity-cup-ux-walk-resolutions.md:44,156`) — the
  aptitude letters were verified **not a tool defect**: the catalog reading is correct and the frame's
  letters were wrong.
- **Source.** §1.1/§1.2 record the run's aptitude letters (Turf A, Dirt G, Sprint E, Mile C, Medium A,
  Long A, Front runner B, …).
- **Code state at HEAD.** Aptitudes are catalog-only: migration
  `2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php` adds aptitude grades to the `umamusume`
  table; the trainee picker renders them (`TraineeSelect.vue`, walk-1 Screen 3). `TrainingRun` has no
  aptitude columns (`app/Models/TrainingRun.php:105` `Fillable` carries none), so a run's aptitudes are
  read from the catalog, never stored per run. An aptitude drift carried by an inheritance Spark is not
  representable on the run.
- **Classification.** `needs-schema`. A per-run aptitude reading would need columns on `training_runs`
  (or a run-scoped aptitude table), which §11 sends through an ADR. D8 already closed the
  catalog-reading question, so the residual is storage, not correctness.

### A-0.3 — Per-run owned-skill inventory

- **First doc.** Walk-3 `U-07` (the §1.3 owned list); walk-1 Screen 10 (`:514-516`) "§1.3's owned list
  has no entry point on this screen".
- **Code state at HEAD.** Landed. `SkillsPlannerController::show()`
  (`app/Http/Controllers/Career/SkillsPlannerController.php:33-58`) ships the run's skills through
  `groupRows()` (`:209-262`) into `Learned` / `Available` / `Required` groups read from the
  `training_run_skill` pivot, and `pickerSkills()` (`:539-549`) supplies the catalogue to add a skill;
  the acquisition-status write (`runs.skills.sync`) lives on the planner. Landing commit
  `fbd37bdd` (`feat(skills): add a catalogue picker to the skills planner`).
- **Classification.** `landed` — `fbd37bdd`.

### A-0.4 — Reached per-card effect values

- **First doc.** Walk-1 Screen 6 (`:309-312`) — "the per-card 'Client rows visible' values … the effects
  this run has actually reached"; walk-3 `U-08`.
- **Source.** §1.4 lines 97-104 — the effects a card has unlocked at the run's own level, versus the
  catalog maximum.
- **Code state at HEAD.** Unrecorded and stated as such: `ADR-0014` line 8 (`docs/adr/0014-support-card-entities.md:8`)
  — "the run's reached effect values are still unrecorded — the builder's analysis continues to use
  catalog anchors". The deck analysis renders catalog maxima, never a per-run reached set.
- **Classification.** `needs-schema`. A per-run reached-effect reading needs a card-level column first
  (A-0.5) and an effect lookup; a column is a migration.

### A-0.5 — Card client levels

- **First doc.** Walk-1 Screen 6 (`:307-308`) — "Card levels and limit breaks … no field on any deck
  surface"; walk-3 `U-08`; walk-resolutions line 66 defers it.
- **Source.** §1.4 "Client level" column (35/35, 30/30, 30/30, 50/50, 30/30, 35/35).
- **Code state at HEAD.** `ADR-0014` declines the **collection** half: `user_support_cards` ("level,
  breaks, perk level") is "no" (`docs/adr/0014-support-card-entities.md:24`), because collection
  tracking is the feature `PRD.md` §6.9 cut. The **per-run** half ("what this run's equipped card is
  at") is recorded open: ADR-0014 line 8 lists "card levels and limit breaks are still unrecorded" among
  the open items. `deck_slots` carries only `support_card_id`, `slot_position`, `ownership`
  (migrations `2026_09_30_142618`, `2026_10_09_120000`); no level column.
- **Classification.** `needs-schema`. A per-run `deck_slots.card_level` is a migration and needs an ADR;
  the collection reading is already `ruled-out` by ADR-0014 and is not re-opened here.

### A-0.6 — Roster editor

- **First doc.** Walk-3 `U-09` and probe P8 (`:949`) — "No roster surface exists anywhere in the flow";
  walk-4 S08 — "the tool has no roster editor at all".
- **Source.** §1.9 — 18 named roster members against a 20/20 counter; §6 item 7.
- **Code state at HEAD.** The roster is **read-only and derived**: `TrainingRun::spiritBurstRoster()`
  (`app/Models/TrainingRun.php:1255`) collapses turn-event `SpiritBurstPayload` rows, and
  `TeamPanelBuilder::teamSection()` (`app/Services/Career/TeamPanelBuilder.php:442-452`) ships that
  read-out. There is no write path and no stored roster — a roster editor would need a table or a
  stored payload the tool does not hold.
- **Classification.** `needs-schema`. Recording a roster (member names + states) is a new table or a
  stored payload column.

### A-0.7 — +30 team ranking bonus

- **First doc.** Walk-1 Screen 8 (`:398-399`) — "Team stat grades … and the +30 ranking bonus … there is
  no field for the bonus"; walk-3 `U-09`.
- **Source.** §1.8, §7 item 5/11 — the Global team-rank bonus "Team Ranking Bonus / All Attributes +30".
- **Code state at HEAD.** Landed as a display. `UnityCupPanel.vue:205-208`
  (`resources/js/components/scenario/UnityCupPanel.vue`) renders "Team Ranking Bonus: All Attributes +30"
  with an `Estimated` provenance badge when `team.rank === 'S'`. Landing commit `6a74f857`
  (`feat(scenarios): land the panel shell and its four panels (E1-E4, E6)`).
- **Classification.** `landed` — `6a74f857`. (The later source finding that the bonus binds to league
  placement rather than rank — §8.3 — is a separate, already-recorded publisher conflict, not this gap.)

### A-0.8 — Purchasable skill pool with prices

- **First doc.** Walk-3 `U-07` ("§2.2's purchasable pool with the 173 SP question"); walk-1 Screen 10
  (`:517-519`) "the recommended spend … no advice surface".
- **Source.** §2.2 (purchasable at or under 173 SP) and §2.3 (recommended spend).
- **Code state at HEAD.** Not present. `SkillsPlannerController::pickerSkills()`
  (`app/Http/Controllers/Career/SkillsPlannerController.php:539-549`) ships only `{id, name}` per
  addable skill, explicitly "no surface reads a cost … for a skill that is not on the run yet"
  (`:533-535`). The `coverageSection()` (`:123-194`) sums only the **Required** (priority-listed) rows;
  there is no panel listing the priced, not-yet-acquired pool sorted by cost.
- **Classification.** `openable`. A read-only `purchasable` section: every priced skill minus the ones
  already acquired, sorted by `sp_cost`, with the sum and the shortfall against the run's SP. No schema.

### A-0.9 — Burst economy band position

- **First doc.** Walk-1 Screen 8 (`:400-415`) — "the band table consequently cannot place the run in its
  10-12 band"; walk-3 probe P6 (`:947`).
- **Source.** §2.4/§8.2 — the 10-12 gold-band conclusion and the Extreme-counting dispute.
- **Code state at HEAD.** Landed. `TeamPanelBuilder::teamSection()` computes `combined_bursts`
  (`app/Services/Career/TeamPanelBuilder.php:493-496`) and `burst_band` via `burstBandFor()`
  (`:536,659-681`), and `UnityCupPanel.vue` renders the run's position — the band table marks the row
  "(this run)" (`:286-295`) and a line prints "This run: N bursts combined, inside the X band"
  (`:314-316`), with the §8.2 caveat named (`:300-306`). Landing commit `5eba0d01`
  (`feat(cockpit): render the run's identity, team, and Unity Cup state`), refactored in `5b5bcb8e`.
- **Classification.** `landed` — `5eba0d01`.

### A-0.10 — Top Star 240,000 threshold

- **First doc.** Walk-1 Screen 8 (`:392-393`) — "the Top Star threshold (240,000) and the 30,755-fan
  gap have no field"; walk-4 S11 reads `209,245 · Star · 30,755 to next`.
- **Source.** §1.1 fans.
- **Code state at HEAD.** The number is computed and shipped but **not rendered**. `FanLadder::fromValue()`
  (`app/Services/FanLadder.php:52-66`) returns `{class, nextThreshold, gap}`, and
  `CockpitController::identitySection()` ships it as `fan_ladder`
  (`app/Http/Controllers/Career/CockpitController.php:416`). But `CareerStatePanel.vue`'s `fanSuffix`
  (`resources/js/components/career/CareerStatePanel.vue:77-85`) renders only `${class} · ${gap} to next`
  and never `nextThreshold`. The reader still has to know 240,000 from outside.
- **Classification.** `openable`. Render `nextThreshold` beside the gap; a one-line view change, no
  schema, no ruling.

### A-0.11 — Mood Effect percentages

- **First doc.** Walk-4 §7 item 3 (source doc) — "The Mood Effect panel re-confirms §1.1.6 at ±20%
  training and ±4% running".
- **Source.** §1.7 Mood; §7 item 3; `docs/UMAMUSUME_REFERENCE.md` §1.1.6.
- **Code state at HEAD.** The mood renders as a tier pill only: `CareerStatePanel.vue:162` mounts
  `MoodPill` with the tier, and `MoodPill.vue` (`resources/js/components/MoodPill.vue`) prints the tier
  and a directional arrow, no percentages. The ±20% training / ±4% running figure is a **single sourced
  reading** held in the reference corpus (§1.1.6), not in any `config/` table or code constant; no
  per-tier percentage table is stored anywhere.
- **Classification.** `needs-shape-decision`. The figure is not derivable from stored data: rendering it
  means either transcribing a committed reference figure into a config/`config('uma')` entry (a sourced
  single figure) or, if per-tier percentages are wanted, building a lookup table the tool does not have.
  Both shapes are defensible; neither is a one-line read-only change.

### B-0.1 — Scenario-scoped mandatory races (R4-06)

- **Source.** §1.6; the round-4 register's shape question — filter the seeded set vs. seed per-scenario.
- **Code state at HEAD.** `CockpitController::obligationSlots()`
  (`app/Http/Controllers/Career/CockpitController.php:667-675`) and
  `TeamPanelBuilder::careerGoalSections()` (`app/Services/Career/TeamPanelBuilder.php:86-92`) both filter
  `RaceCatalogSlot` by `forScenario($run->scenarioKey())->where('is_mandatory', true)`. The scenario
  scoping exists; what is open is whether the seeded mandatory rows are correct for a non-URA scenario.
  A Unity Cup run still lists the seeded URA rows if they are the only `is_mandatory` rows.
- **Classification.** `needs-shape-decision` — filter the seeded set (no schema) vs. a new seed migration
  (schema, needs ADR).

### B-0.2 — Cleared-goal recording (R4-07)

- **Source.** §1.6 goals; round-4 register R4-07, which recategorised `KI-34`.
- **Code state at HEAD.** `TraineeGoal.php:20-27` (`app/Models/TraineeGoal.php`) states the design:
  "Nothing here says whether a run met it. That is derived from the `race_entries`". `TeamPanelBuilder`
  derives progress: `traineeGoalRows()` groups finished entries (`:235-239`) and `objectiveMet()`
  (`:321-359`) reads completed `race_entries` placement. The gap is an un-recordable completed race, not
  a missing goal-state column.
- **Classification.** `needs-shape-decision` — add completed-race recording (write path, no new column)
  vs. add a goal-state column (schema).

### B-0.3 — Pending-turn state + date input (R4-08)

- **Source.** §1.7 turn; round-4 register R4-08.
- **Code state at HEAD.** `StoreTurnEntryRequest` (`app/Http/Requests/StoreTurnEntryRequest.php:74-86`)
  requires the five stats and `turn`; `choice` and `outcome` are required only when `stage` is set
  (`:142-143`), so the plain form accepts a no-outcome row. There is **no date input** anywhere in the
  rules, and `TurnChoice` (`app/Enums/TurnChoice.php`) has no "pending" case — the guided rail forces
  `Success`/`Failure`.
- **Classification.** `needs-shape-decision` — a "pending" turn state (new enum value, shape) vs. a
  plain-form date field on the snapshot door (`StoreSnapshotRequest`).

### B-0.4 — Scheduled-race state (R4-10)

- **Source.** §1.6 race card; round-4 register R4-10.
- **Code state at HEAD.** `RaceEntryStatus` (`app/Enums/RaceEntryStatus.php:12-19`) is
  `NotOffered`, `Skipped`, `Entered`, `Completed` — no undecided/scheduled state — and the only write
  path stores `Entered` (`RaceCard.vue`, per the register's `:112-121`).
- **Classification.** `needs-shape-decision` — a stored `Undecided` enum value (vocabulary change, owner
  approval) vs. a derived UI line.

### B-0.5 — Team stat grades (R4-12)

- **Source.** §1.8 grades A/A/A/S/A; round-4 register R4-12.
- **Code state at HEAD.** `TeamPanelBuilder::teamSection()` hard-codes `grade => null` for every stat
  (`app/Services/Career/TeamPanelBuilder.php:501-506`), and `UnityCupPanel.vue:210-225` prints the
  five-row `N/A` with the reason. No column.
- **Classification.** `needs-schema` — a `team_stat_grades` column (or table) is a migration, ADR first.

### B-0.6 — Current Spirit gauge (R4-14)

- **Source.** §1.8; round-4 register R4-14.
- **Code state at HEAD.** `UnityCupPanel.vue:231-241` mounts `ResourceMeter` with `value: null, max: null`
  and the absence sentence "No column records a Spirit value". No column, no payload reader.
- **Classification.** `needs-schema` — a `current_spirit` column is a migration, ADR first.

### B-0.7 — Skill levels (R4-16)

- **Source.** §1.3/§2.1 hint levels; round-4 register R4-16.
- **Code state at HEAD.** `SkillsPlannerController` carries acquisition state only
  (`app/Http/Controllers/Career/SkillsPlannerController.php:216-228`); no level column exists and no
  field renders one. `SkillAcquisition` (`app/Enums/SkillAcquisition.php`) has no level member.
- **Classification.** `needs-schema` — a `skill_levels` table or per-run level column is a migration.

### B-0.8 — Borrowed-card owner identity (R4-18)

- **Source.** §1.4 friend card; round-4 register R4-18.
- **Code state at HEAD.** `deck_slots.ownership` (migration
  `2026_10_09_120000_add_ownership_to_deck_slots_table.php:24`) records `OWNED` / `RENTED` / nothing —
  the borrowed **flag**, never the owner account. No account/identity column exists.
- **Classification.** `needs-shape-decision` — add an owner-identity column (schema) vs. "will not add"
  for a loopback, single-Trainer tool (the register's stated likelihood).

## Classification table

| Item | Group | State | Evidence / commit |
|---|---|---|---|
| A-0.1 | A | openable | `CockpitController.php:394-417` (caps) vs. `InheritanceEventController.php:74` (sparks); no shared surface |
| A-0.2 | A | needs-schema | `2026_09_27_090000…umamusume_table.php` (catalog-only); `TrainingRun.php:105` no aptitude columns |
| A-0.3 | A | landed | `fbd37bdd` |
| A-0.4 | A | needs-schema | `ADR-0014:8` — reached values unrecorded |
| A-0.5 | A | needs-schema | `ADR-0014:24` declines collection; `:8` leaves per-run level open; no `deck_slots.card_level` |
| A-0.6 | A | needs-schema | `TrainingRun.php:1255` read-only roster; no stored roster |
| A-0.7 | A | landed | `6a74f857` (`UnityCupPanel.vue:205-208`) |
| A-0.8 | A | openable | `SkillsPlannerController.php:539-549` ships `{id,name}` only; no purchasable section |
| A-0.9 | A | landed | `5eba0d01` (`TeamPanelBuilder.php:493-496,536`; `UnityCupPanel.vue:286-295,314-316`) |
| A-0.10 | A | openable | `FanLadder.php:52-66` ships `nextThreshold`; `CareerStatePanel.vue:77-85` omits it |
| A-0.11 | A | needs-shape-decision | `MoodPill.vue` tier-only; ±20%/±4% in `UMAMUSUME_REFERENCE.md` §1.1.6, not in config/code |
| B-0.1 | B | needs-shape-decision | `CockpitController.php:667-675` scoped but seeded-set question open (filter vs seed) |
| B-0.2 | B | needs-shape-decision | `TraineeGoal.php:20-27` derived from `race_entries` (write path vs column) |
| B-0.3 | B | needs-shape-decision | `StoreTurnEntryRequest.php:142-143`; no date, no pending case (enum vs form field) |
| B-0.4 | B | needs-shape-decision | `RaceEntryStatus.php:12-19` no undecided state (stored vs derived) |
| B-0.5 | B | needs-schema | `TeamPanelBuilder.php:501-506` `grade => null` |
| B-0.6 | B | needs-schema | `UnityCupPanel.vue:231-241` `value: null` |
| B-0.7 | B | needs-schema | `SkillsPlannerController.php:216-228` acquisition only, no level |
| B-0.8 | B | needs-shape-decision | `2026_10_09_120000…ownership…php:24` flag only, no account (column vs will-not-add) |

**Counts.** openable 3 · needs-schema 7 · needs-shape-decision 6 · ruled-out 0 · landed 3.

## Not verifiable / needs browser re-read

- **A-0.11.** Whether the ±20%/±4% figure is one sourced reading or a per-tier table is a source
  question, not a code read; the audit cannot settle the shape from the tree alone. Recorded
  `needs-shape-decision` rather than guessed openable.
- **A-0.2.** D8 already verified the catalog aptitude reading is correct; whether a per-run aptitude
  storage is demanded at all is a product question, not a code gap. Recorded `needs-schema` on the
  assumption the run's post-inheritance aptitudes are wanted.
- **A-0.5.** The per-run card level vs. the collection reading needs the owner's reading of `ADR-0014`;
  the audit records the split rather than choosing a side.

## Baseline report

- **Read at.** `3cdc466c` (`HEAD`), the R4 Phase 1 tip; no second commit compared for Group A.
- **Tree state.** `git status --short` → three untracked paths, none tracked-dirty:
  `docs/audits/rice-shower-unity-cup-round4-reconciliation.md`,
  `docs/audits/rice-shower-unity-cup-ux-walk-4.md`,
  `tests/Feature/ZzProbeOrderTest.php`. No peer work entangled.
- **Gates run this phase.** `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed (44
  assertions); `npm run typecheck` → exit 0. No source file touched, no commit made.
## Addendum, 2026-10-24 — cross-check against every walk's unrepresented-source-data register

Re-read at `04c16a22` (`HEAD`; the tree moved past the `3cdc466c` this audit was written against when
`04c16a22` `test(ZzProbeOrder): add placeholder for future test implementation` landed). A cross-check of
the walk-1/2/3/4 "unrepresented source data" registers against the `A-0.1..A-0.11` / `B-0.1..B-0.8` rows
above surfaced four candidates no existing row covers. Each was verified at HEAD. Append-only: no earlier
row, table, or prose was changed.

### Candidate verdicts

| Candidate | Verdict | Settling evidence |
|---|---|---|
| 1 — training-tile banner + three card avatars | genuinely missing | `TurnEntry.php:61` `Fillable` carries facilities, preview, failure, choice — no banner and no card-avatar list; `TrainingDetail.vue` renders no banner or avatar |
| 2 — green-factor unique-skill spark | covered (does not reproduce) | all six green factor names exact-match `skills`; `LegacySelectController.php:127-131` ships them via `sparkTargets.Skill` = every skill name |
| 3 — 20/20 members counter | genuinely missing | `Cockpit.vue:862-869` (the A-0.6 roster) renders names/states only; `TeamPanelBuilder.php:442-507` derives a burst roster with no counter |
| 4 — guest skill lists on the roster | genuinely missing | `Skill.php:39,58` marks `is_unique` with no character link; the A-0.6 roster carries name/friend/ownership only |

Three genuinely-missing items below. None is `openable`: each needs a column or a stored roster, so none
opens a Phase 3 slice.

### A-0.12 — Training-tile banner and the three card avatars

- **First doc.** Walk-1 §1.7 (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:249-250`) records, on the observed
  training tile, "Speed facility **Lv4** with the banner 'Floor Cleaning', three card avatars on the tile".
- **Source.** §1.7 line 249-250.
- **Code state at HEAD.** The turn write stores the facility levels (`facility_speed` … `facility_wit`), the
  `preview_gains`, and `failure_rate` (`app/Models/TurnEntry.php:61` `Fillable`). There is no column for the
  facility banner name and no column for the three support cards whose avatars the tile showed;
  `TrainingDetail.vue` renders neither. `StoreTurnEntryRequest` accepts neither field.
- **Classification.** `needs-schema`. Two turn-level columns: a `facility_banner` string and a
  `training_card_ids` list (or a join table), each a migration requiring an ADR, digest line, and PRD
  citation before code.

### A-0.13 — 20/20 members counter

- **First doc.** The `A-0.6` source text names "18 named roster members against a 20/20 counter" (§1.9);
  walk-1 §1.9 and walk-3 P8 list the 18-member roster.
- **Source.** §1.9.
- **Code state at HEAD.** The landed `A-0.6` (`d6c30475`) ships a roster derived from the six deck slots
  (`app/Http/Controllers/Career/CockpitController.php` `rosterSection()`; `Cockpit.vue:862-869`) — names,
  friend flag, ownership, slot. It renders no "N of 20" counter. `TeamPanelBuilder.php:442-507` derives a
  second roster from `spiritBurstRoster()` with no counter either. Neither surface counts against the
  source's 20-member team capacity.
- **Classification.** `needs-schema`. The 20/20 counter counts a stored roster the tool does not hold (the
  derived deck roster is 6, not 18); it rides the same stored-roster blocker that `A-0.6` was ruled to
  derive around. Blocked until a roster is stored (ADR).

### A-0.14 — Guest skill lists on the roster

- **First doc.** Walk-1 §1.9 records "the 18-member roster and the guests' skill lists".
- **Source.** §1.9.
- **Code state at HEAD.** The `A-0.6` derived roster carries each member's name and state only — no skill
  list. `Skill.php` has no character link (`is_unique` is a bare boolean, `Skill.php:39,58`), so a member's
  skills cannot be resolved from the catalogue the way a support card's `hint_skills`/`event_skills` can.
- **Classification.** `needs-schema`. Either a character-to-skill link or per-roster-member skill storage;
  a migration and ADR before code.

### Addendum classification table

| Item | State | Evidence |
|---|---|---|
| A-0.12 | needs-schema | `TurnEntry.php:61` — no banner/avatar columns |
| A-0.13 | needs-schema | `Cockpit.vue:862-869` + `TeamPanelBuilder.php:442-507` — roster without a counter |
| A-0.14 | needs-schema | `Skill.php:39,58` — no character link; roster without skill lists |

### Candidate 2, expanded — the green-factor sparks reproduce, so no item is filed

Walk-4 S05 (`rice-shower-unity-cup-ux-walk-4.md:246`) claims "the spark form's `Kind = Green` has `Applies
to` set to Stat/Aptitude/Skill and a target, but no field for a unique-skill name paired with a star grade".
That does not reproduce at HEAD. The six green factors the walk names (§8.5 lines 1191-1197) — Cut and
Drive!, Angling and Scheming, Resplendent Red Ace, Red Shift/LP1211-M, Shooting for Victory! — each
exact-match a `skills` row (probed against the dev database; `Red Shift/LP1211-M` is stored with its slash
and card-code suffix verbatim), and `LegacySelectController.php:127-131` ships every skill name as
`sparkTargets.Skill`. Selecting `Applies to = Skill` turns the target field into a `<select>` of those names
(`LegacySelect.vue:427-429`), and the write boundary accepts a Skill target as a verbatim string
(`StoreLegacySelectionRequest.php:178-180`). So a Green / Skill / unique-name / ★1..3 spark is writable
through the UI.

The mechanism the owner flagged is real — `6fa0d01` replaced the free-text `Applies to` with typed selects —
but the functional gap is closed: every documented green factor is selectable, so the free-text → typed
change did not remove a value the source records. No `A-0.x` row is added for it.