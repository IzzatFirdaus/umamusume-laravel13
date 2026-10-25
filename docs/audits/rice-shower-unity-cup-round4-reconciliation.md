# Round 4 Reconciliation: Rice Shower / Unity Cup UX Walk — Fourth Pass

Reconciles `docs/audits/rice-shower-unity-cup-ux-walk-4.md` (the fourth browser walk, audit ID
`RS-UC-UXWALK-2026-10-24-04`) against the three earlier registers —
`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` (walk 1, `D1`–`D9`),
`docs/audits/rice-shower-unity-cup-round2-reconciliation.md` (walk 2, `R2-01`–`R2-21`) and
`docs/audits/rice-shower-unity-cup-round3-reconciliation.md` (walk 3, `R3-01`–`R3-32`) — and against
the code.

The audit names its own tree as `f6018f879c7191bfe74be0d119ba1750b0b12a61`
(`docs(contributing): record the fact-check adjacency lesson`, `2026-10-24`). `HEAD` is the same
commit, so every read below is one commit: the audit's, and the current one. Where a prior register
cites an older commit for a claim, the row reads that commit too and says whether the code agrees.

Nothing in this document is a fix. It classifies, names root causes, and proposes slices; no file
under `app/`, `resources/`, `routes/`, `tests/`, `database/`, `config/` or `docs/proposals/` was
touched to produce it, and the fourth audit is left exactly as it was written (it is still untracked
in the tree; see the Baseline test report).

## Method

Each observation from the fourth audit is classified as one of:

- **Confirmed fixed** — a later commit already addresses it; the code read proves it.
- **Confirmed defect** — the code read reproduces the failure.
- **Known limitation** — intentional and consistent with a recorded decision (`ADR`, `PRD` §6, a
  register entry).
- **Unrepresented source data** — a real gap in what the tool can hold, not a rendering bug.
- **Source conflict / open** — the evidence does not settle it; recorded open.
- **Does not reproduce** — the observation contradicts the source at the audit's own commit.

**A claim is the code read.** Every "Current status" below is a read of a named file at a named
commit, not a restatement of the audit. Where the audit's observation and the source disagree, the
source wins and the row is marked **Does not reproduce** with the command that shows it — the audit's
own rule (`AGENTS.md` §2, "when code and a written rule disagree, the code wins"), applied to an
audit rather than a rule.

## Verification note (read before the table)

The fourth walk reproduces far better than the third did. Round 3 found **five** observations that
contradicted the source at its own commit; this pass found **two**, both of them experiential
impressions rather than state claims, and both narrow: the rest of the audit is evidence-traced with
quoted before/after text.

| # | Audit says | Source at `f6018f87` says | Command |
| - | ---------- | ------------------------- | ------- |
| V-1 | E-02: "`Save target` gives no confirmation"; the header/breadcrumb "still reads `Target: not chosen` after a save" | The write returns a flash, and the shell renders it for every page of the wizard. The `Target: not chosen` header **does not exist in the tree at all**. | `sed -n '111p' app/Http/Controllers/Career/BuildTargetController.php` → `->with('status', 'Build target saved.')`; `rtk grep -rn "Target: " resources/js app` → one hit, `RacePlannerController.php:431`, a race-detail sentence. The only `--: not chosen` shape is `ChoicesSoFar.vue:44`. |
| V-2 | F-02: after re-saving only `Purpose`, "the previously saved two skill priorities were gone" | The priority list is unconditionally required on a save (`skill_priorities` => `['present', 'array']`) and pre-filled from the stored target (`props.target?.skill_priorities`); the write replaces the stored target whole, so a save that omitted the list is refused, not silently emptied. What the audit describes needs the browser to have posted an empty list over its own stored one. | `sed -n '81p' app/Http/Requests/StoreBuildTargetRequest.php` → `'skill_priorities' => ['present', 'array'],`; `sed -n '126p' resources/js/pages/Career/BuildTarget.vue` → pre-fills from `props.target?.skill_priorities`. |

**What the two share.** Both are experiential impressions of a screen that does give feedback and does
carry the list. Neither is a state claim, and neither can be settled past the code without a browser
re-read. Consequence: E-02 closes as **Does not reproduce** with a correction to what the screen
actually prints; F-02 is **Source conflict / open** — the loss is possible only through a posted empty
list, which is a user action the audit does not otherwise mention, and a one-run browser re-read
settles it either way.

**Two claims that look like contradictions and are not.** R2-13 and R3-12/R3-26 say rarity/potential
and the Unity Cup team editor exist; walk 4 says potential and the team rank have no field. Both
readings are correct and the audit is more granular than the earlier rows were: rarity **is** editable
(`Cockpit.vue:693-697`), potential **is** editable (`Cockpit.vue:699-705`, column
`potential_level`), and what walk 4 recorded was the header's absence when nothing had been entered
yet plus an identity editor it had not opened (`Edit identity` is a collapsed `<details>`). Those are
handled in R4-11 and R4-20, not as "does not reproduce".

**One claim that is a true regression risk and is not one now.** The Careers row's `Turn ~11 / 24`
(R4-07) is a hardcoded literal in an older veteran of the 2.0 cutover, in a file no round has claimed
before. It is not a regression from a R2/R3 fix; it is a defect that has never been raised.

## Cross-register mapping (mandatory, before the table)

| Walk-4 ID | R2/R3 correspondence | Status at HEAD | Note |
|---|---|---|---|
| F-01 | R2-08 (target save semantics, `be02297`), R2-09 (stat denominators), R3-07 | still open, different mechanism | The stored target reaching the run was never the subject of R2-08 (that row is about an empty save's flash) or R3-07 (that row is about denominator labelling). This is the draft→run carrier. |
| F-02 | — | new | Not previously raised; see V-2 (source conflict still open). |
| F-03 | R2-03 | same class, still open as a recorded known limitation | R2-03 was closed as *intentional* ("warnings inform but do not block"). The code agrees; walk 4 re-raises it. Reclassified here as **Known limitation**, with the copy defect beside it (R4-04). |
| F-04 | — | new | No prior row about a run id ever being displayed. |
| F-05 | `KI-34` (reserved number, no entry) and R3-28's chain | same class, still open | Goal/obligation scoping by scenario is the root; R3-28 covers the *cleared goal* half, this is the *wrong-scenario obligation* half. |
| F-06 | R3-28 → `KI-34` (dated correction 2026-10-13 in round 3) | still open, **corrected classification** | Round 3/`KI-34` said cleared goals have "no recorded source". The code says the opposite: progress is deliberately derived from `race_entries` (`TraineeGoal.php:20-27`), so the gap is an un-recordable race entry, not a missing goal-state column. |
| F-07 | R2-04 / R2-01 (wizard vs snapshot), R3-11, R3-19 | same root, sharper evidence | R2-04/R3-11 said a mid-career snapshot cannot be created by the wizard. Walk 4 shows the snapshot door exists (`career.snapshot*`) and was used with a turn number; the residual is the missing date input and the forced outcome. |
| F-08 | R2-01, R3-11 | related, **different mechanism** | R2-01/R3-11 are about a *new-career* run legitimately reading Turn 1. Walk 4's `Turn 20` is a per-screen renumbering of a logged turn 67 on the decision screen. Not the same defect. |
| F-09 | — | new | The Careers row is not a surface any prior round examined. |
| F-10 | — | new | No prior row about the trainee picker dropping its filter. |
| F-11 | — | new | Round 3's `CareerSectionNav` work (slice 4.1, `2026-10-22` note in that component) built a run-scoped Legacy href; walk 4 read `/legacy` from a page that is **not** under `CareerLayout`. |
| E-01 | — | new | |
| E-02 | R3-04 (sibling "no success feedback" finding), R2-08 | **Does not reproduce** | R3-04 was about the Cockpit correction form; the target step is a different surface and it flashes. |
| E-03 | R3-10 | same class, still open as a workflow cost | Unchanged: per-row editor, no bulk entry. Round 3's row stands. |
| E-04 | R3-03 (deck picker query lag) | adjacent, **different mechanism** | R3-03 was the `deck_slot` URL lagging; this is the `q` filter navigating and closing the dialog, plus epithet matching. `DeckSelect.vue:162-181` shows the navigation is deliberate. |
| E-05 | R2-06 (`fdebcc9`, `dd179ba`) | adjacent, **different mechanism** | R2-06 fixed ownership *persistence* and its copy. This is the default-`N/A` ergonomics of the picker. |
| E-06 | — | new | |
| E-07 | R3-01 (energy display half) | adjacent only | R3-01 is about a band reading; this is an empty-new-run density judgement. |
| E-08 | — | new | |
| E-09 | — | new; overlaps R4-10's mechanism | `RaceEntryStatus` has no undecided state, and the only write path stores `Entered` (`RaceCard.vue:112-121`). |
| E-10 | — | new | |
| E-11 | — | new; same root as F-04 | |
| E-12 | New per walk 4's own note | new | See R4-28 (small copy pass). |
| E-13 | R2-12 (`473bb19`, fold on the planner only) | related, still open by design | The decision screen deliberately does not fold (`RaceCard.vue:134-136`); the audit read the decision screen. |
| U-01 | R2-13 → "fixed" `8e51b9f`; R3-12 → "does not reproduce" | **the R2/R3 claim holds; walk 4 read the header** | Rarity and potential both render as inputs (`Cockpit.vue:693-705`). The header shows `N/A` because nothing was entered. Residual is a UX gap, not a missing field. |
| U-02 | R3-26 → "does not reproduce" (`d8fb234`, `UnityCupEditor.vue`) | the R3 claim holds for *team identity*; this is a **different fact** | `TeamPanelBuilder.php:501-506` hard-codes null for every `stat_grades` row and the panel prints its own absence sentence. No column exists. |
| U-03 | R2-14 → "fixed" `d8fb234`; R3-26 | **partly contradicts, and R3 was already narrow** | Team identity/counters exist (`UnityCupEditor.vue`); **Team Rank has another owner**: `TurnEvent` `TeamRankPayload` (`TrainingRun.php:1206-1217`). The rank *is* recordable, through a turn event, not through this panel. |
| U-04 | R3-26 | still unrepresented | `UnityCupPanel.vue:232-246` prints the absence; no column. |
| U-05 | R3-27 (`R2-12` payout landed `473bb19`) | still open | Going/field/recommended flag have no column; the scheduled-race gap is R4-10's root. |
| U-06 | R3-24 | still open | No skill level anywhere. |
| U-07 | R2-11 / R3-15 / R3-29, and the **dated correction 2026-10-13** in round 3 | still a **Known limitation**, owner the citation correction fixes | ADR is `ADR-0011` / `ADR-0014` (and `ADR-0005` R37 for the hint pools), **not `ADR-0001`**. |
| U-08 | R2-06 (`ownership`/`Rented`) | adjacent, different fact | `Rented` records external; the owner's identity is not a column, and R2-06 never claimed it was. |

## Reconciliation table

| R4 ID | Walk-4 ID | Type | Short description | Prior status | Current status | Root cause | Proposed slice | Regression test |
|---|---|---|---|---|---|---|---|---|
| **R4-01** | F-04 | Functional | Run id 26 appears only in the URL; no screen names it | new | **Confirmed defect** | `TrainingRunController.php:61-76` never ships `id`, the row renders `run.name` only (`Runs/Index.vue:87-92`); `PreflightController.php:158-159` flashes "Career started for Rice Shower". Also true of the cockpit header. | **S1** — ship `id` in the run list + cockpit header, print it beside the title; test | `RunsIndexTest`: assert the row payload carries a visible `id`; `CareerPreflightTest`: assert the flash names the run |
| **R4-02** | E-11 | Experiential | Two identical Rice Shower / Unity Cup / Active rows cannot be told apart | new; same root as F-04 | **Confirmed defect** | Same as R4-01; plus the row's only differentiator is `created_date` (`Runs/Index.vue:103-107`) | **S1** (same slice; one row fixes both) | Same as R4-01; browser: two runs of the same trainee are distinguishable by an accessible name |
| **R4-03** | F-01 | Functional | A saved build target never attaches to the created run | R2-08, R2-09, R3-07 adjacent | **Confirmed defect** | Draft key mismatch: the step writes `build_target` (`BuildTargetController.php:106-109`), and the start request merges **`'build_target' => SetupDraft::buildTarget()`** — which **exists** (`StartCareerRequest.php:61`) — so the carrier is present; the loss is the all-or-nothing payload. The step stores `payload()`, which is `null` whenever `purpose`/`distance`/`surface`/`style`/`targets` are unset (`StoreBuildTargetRequest.php:160-190`). Walk 4 saved ceilings + priorities with those unset, so `build_target` stored null while `caps` stored. Preflight then reads `SetupDraft::buildTarget()` → null → `Missing` (`PreflightController.php:169-182`, `:76-91`). | **S2** — a ceilings-only save is a real save: either store ceilings (+priorities) as a target with the unset fields named, or split the payload so `build_target` can carry ceilings/priorities without the all-or-nothing purpose. Owner call on shape. Test | `CareerBuildTargetTest`: save with ceilings + one priority and no purpose, then assert `SetupDraft::buildTarget()` is not null (RED now: `toBeNull()`); `CareerPreflightTest`: same draft renders `contract.complete` without `missing_section.target` |
| **R4-04** | F-03 | Functional | A step Preflight names `Missing` does not stop `Start career` | R2-03 → **Known limitation** | **Confirmed defect, but only the copy** | `PreflightController.php:87-91` computes `complete`; `StartCareerRequest.php:76-81` requires only `umamusume_id` and `scenario` and its own docblock states "the wizard's warnings never block". Blocking is the owner's recorded call (R2-03); the defect is that `Preflight.vue:319` heads the block "The five entered steps" and R4-03 lets a *saved* step read `Missing`. | **S2** (rides R4-03) — reconcile the heading/wording with the count; do **not** make warnings block without an owner ruling | `CareerPreflightTest`: the missing-target case prints a count that agrees with the rows it renders |
| **R4-05** | E-06 | Experiential | `The five entered steps` sits over a six-step wizard | new (copy of R2-03/R4-04 area) | **Confirmed defect (copy)** | `Preflight.vue:251,319` print `five`; the wizard has six steps and step 6 is this screen | **S2** | Assert the heading's count equals the rendered row count |
| **R4-06** | F-05 | Functional | A Unity Cup career lists URA Finals race names as mandatory obligations | R3-28 / `KI-34` adjacent | **Confirmed defect** | Every mandatory obligation the Cockpit and the decision screen render comes from `race_catalog_slots`/`scenario_slots` scoped by scenario key and `is_mandatory` (`CockpitController.php:568-584`, `RaceDecisionController.php:40-44`); the screen sentence says "mandatory", and for a Unity Cup run the mandatory set can only be the seeded URA rows. The scenario-scoping exists (`composesPanel('race_calendar')`) but does not remove the seeded rows. | **S8** (needs the seed/scope question answered first: which rows carry `is_mandatory` for a non-URA scenario). No schema change if the answer is "filter the seeded set". | `CareerPreflightTest` sibling: a Unity Cup run's `deadlines` contains no `URA Finals` row |
| **R4-07** | F-06 | Functional | Cleared goals cannot be shown at turn 67; `Goals Achieved 0 of 8` is derived from nothing | R3-28 → `KI-34`, **recategorised** | **Confirmed defect, but the fix is a write path, not a column** | `TeamPanelBuilder.php:235-239` groups finished entries by slot and `objectiveMet()` (`:321-360`) reads them; no race entries exist for this run, so nothing is met. `TraineeGoal.php:20-27` states the design: "Nothing here says whether a run met it. That is derived from the `race_entries`". So the loss is that three races the client ran are not recordable through the wizard path (R4-03's siblings), not that goal state has no home. | **S8** (same write-path work as R4-15/R4-16) — a completed-race record with a placement; no new column | `TeamPanelBuilder`-level feature test: record three completed entries, assert `Goals Achieved 3 of 8` and the cleared rows carry `state` |
| **R4-08** | F-07 | Functional | The turn form has no date input and requires an outcome, so a mid-career snapshot forces a fabricated value | R2-04, R3-11, R3-19 | **Source conflict / open** — the form is behaving as designed; the **snapshot door** is the answer and it is turn-numbered only | `StoreTurnEntryRequest.php:71-86` requires the five stats and `turn`; `:141-143` makes `choice` and `outcome` required only when `stage` is set (the guided rail). The plain form on the Training screen posts `stage` null (`TrainingDetail.vue` form fields), so the escape hatch **does accept** a row with no outcome. What the audit described is the guided path's required outcome. The date is not an input on either path; `career_position` is set by the snapshot door (`SnapshotController`). | **S6** — one copy line on the guided form: state that a snapshot that has not decided a turn uses the plain form, or give the snapshot door a date field. Owner call on which. | `SnapshotTrainingDecisionTest` sibling: a snapshot run recorded from the plain form carries no `choice`/`outcome` |
| **R4-09** | F-08 | Functional | The race screen reads `Senior Year · Late October · Turn 20` while the cockpit reads `Turn 67` | R2-01/R3-11 related, **different mechanism** | **Confirmed defect** | `RaceDecisionController.php:127` prints `($next['turn'] % 2 === 1 ? 'Early' : 'Late') . MONT…` — a **within-year** turn (1..24) labelled as if it were the career turn; the cockpit prints the absolute index (`CockpitController.php:334-350`). One owner computes the right number twice, two surfaces print it on two clocks. | **S3** — one formatter, one clock: the race header prints the absolute turn beside the within-year position, or names the clock it uses | `CareerRaceDecisionTest`: with turn 67 logged, `nextTurn.turn` (or the new `turn_index`) reads 68, not 20 |
| **R4-10** | E-09 | Experiential | A race being decided offers only `Enter Race`; no "scheduled, not yet run" state | new (mechanism shared with U-05) | **Confirmed defect (missing state)** | `RaceEntryStatus` has `NotOffered`, `Skipped`, `Entered`, `Completed` (`app/Enums/RaceEntryStatus.php`) — no undecided state — and the only write path stores `Entered` (`RaceCard.vue:112-121`). | **S9** — needs a decision: is "scheduled" a status value (a stored-vocabulary change, owner approval) or a derived line? State both and stop. | — (blocked on the decision) |
| **R4-11** | U-01 | Unrepresented | Potential Lvl 2 has no field; the header reads `N/A` | R2-13 "fixed", R3-12 "does not reproduce" | **Does not reproduce as stated** — the field exists; the *header* is empty because nothing was entered | `Cockpit.vue:699-705` renders `potential_level` and posts it (`:442`); the column exists (migration `2026_10_1*_add_trainee_rarity*`, `potential_level` `nullable`). The header absence is the correct `N/A` for an unentered value. | **S4** — optional: have the identity editor default-open when the value is null, or link the header absence to the editor | `TraineeRarityPotentialTest` already covers persistence; add a header copy assertion only if S4 lands |
| **R4-12** | U-02 | Unrepresented | Team stat grades A/A/A/S/A have no input | R3-26 "does not reproduce" (different fact) | **Unrepresented source data**, and the screen says so | `TeamPanelBuilder.php:501-506` hard-codes `grade` to null for every stat; `UnityCupPanel.vue:221,243-246` prints the absence and its reason | **S10** — owner call: a `team_stat_grade` column is a schema change (§11: ADR + digest + PRD citation). Nothing else. | — |
| **R4-13** | U-03 | Unrepresented | Team Rank S has no input on `Edit team state` | R2-14 "fixed", R3-26 | **Partly contradicted — and the rank has another owner, which R3 did not name** | The rank **is** recordable: `TrainingRun::latestTeamRank()` reads `TeamRankPayload` from `TurnEvent` rows (`TrainingRun.php:1206-1217`), and the cockpit strip reads it (`CockpitController.php:372`). `Edit team state` (`UnityCupEditor.vue`) carries name/motto/league/preseason/trainings/bursts only because those are columns. So a Trainer records rank **through the turn's event**, not through a team-state field. | **S11** — no new column; a copy line in `UnityCupEditor.vue` naming where the rank is recorded. Owner call whether a shortcut belongs there. | — |
| **R4-14** | U-04 | Unrepresented | Current Spirit gauge has no input | R3-26 | **Unrepresented source data** | `UnityCupPanel.vue:232-246` prints the absence; no column, no payload | **S10** (owner call, schema) | — |
| **R4-15** | U-05 | Unrepresented | The turn's race day (Daishoten, Soft going, `Recommended`) has no representation | R3-27 (payout landed) | **Unrepresented source data**, and partly R4-10 | No going/field/recommended column; the scheduled-race gap is R4-10's enum | **S9** (rides the state decision) + S10 for the going column | — |
| **R4-16** | U-06 | Unrepresented | Skill levels (unique Lvl 3, hint levels) have no field | R3-24 | **Unrepresented source data** | `SkillsPlanner.vue` carries acquisition state only; no level column | **S10** (schema; owner) | — |
| **R4-17** | U-07 | Unrepresented | Hint discounts are not modelled | R2-11 / R3-15 / R3-29 + round 3's 2026-10-13 correction | **Known limitation** | `SkillsPlanner.vue:290-291` states it; the ruling is `ADR-0011` (level only, no percentage), with `ADR-0014` listing accumulation out of scope and `ADR-0005` R37 cutting the hint pools. **Not `ADR-0001`.** | — | — |
| **R4-18** | U-08 | Unrepresented | The borrowed card's owner account is not representable beyond `Rented` | R2-06 adjacent | **Unrepresented source data** | `deck_slots.ownership` (R2-06) stores owned/rented; no account field | **S10** (owner call; local-only tool, so likely "will not add") | — |
| **R4-19** | E-03 | Experiential | Spark entry is per-row form work; six ancestors with 30+ sparks is impractical | R3-10 | **Known limitation / workflow cost** (unchanged) | `LegacySelect.vue` per-row editors; a bulk paste is a design project | **S12** (owner call) | — |
| **R4-20** | E-05 | Experiential | Ownership defaults to `N/A` and is a separate decision from card choice | R2-06 adjacent | **Confirmed defect (ergonomics)** | `DeckSelect.vue:207-212` sets `ownership` from the slot's existing flag, defaulting `'OWNED'` on equip — so the audit's "six slots can be saved unclassified" is only reachable by clearing the flag after equipping (`:221-225`). The observable friction is that a slot picked but not yet equipped reads `N/A`. | **S7** — one line: name the default in the picker copy, or pre-select `Owned` visually on the slot | Browser case: equip with no ownership chosen, assert the slot renders `Owned` and the payload carries it |
| **R4-21** | E-04 | Experiential | The deck picker's `Filter` navigates and closes the dialog; the printed epithet matches nothing | R3-03 adjacent, different mechanism | **Confirmed defect (two halves)** | (a) `DeckSelect.vue:162-181` deliberately navigates per filter (`preserveState: retarget` only on retarget), closing the dialog on a plain filter; (b) the search is `char_name LIKE` OR `title_en LIKE` (`DeckSelectController.php:152-154`) — the row prints `Name [Epithet]`, the epithet is in `title_en` and the row composes the two, so searching the printed string can miss. | **S7** — (a) filter the picker in place, or keep the dialog open across the visit; (b) match the composed printed label | `CareerLegacyDeckStepsTest` / `career-deck-select.spec.ts`: searching `Tokai Teio [Dream Big!]` returns the row |
| **R4-22** | E-01 | Experiential | No forward control at the bottom of the trainee picker | new | **Confirmed defect (ergonomics)** | `TraineeSelect.vue` ends at pagination (`:120-170` area); advancement is the wizard rail (`SetupLayout.vue:54,77-80`) | **S7** (one step, one pass) | Browser: the trainee step exposes a forward control at the end of the table |
| **R4-23** | F-10 | Functional | Selecting a trainee clears search/sort and reloads all 67 rows | new | **Confirmed defect** | `TraineeSelectController.php:173` redirects to `route('career.trainee')` with no query (`:148-173`); the filter is a GET query (`:116`, `:284-290`) | **S7** | `CareerTraineeSelectTest`: select via a filtered URL, assert the redirect preserves `search`/`sortBy`/`direction` |
| **R4-24** | E-12 | Experiential | Help text names `Competitive Build`, which the select does not offer | new per walk 4's own note | **Confirmed defect (copy)** | `BuildTarget.vue:448` prints it; `BuildPurpose` has four cases (`BuildTargetController.php:144-149`); `lang/en/uma.php:65` documents the gap | **S2** (copy) | Assert no shipped string names an option the select cannot offer |
| **R4-25** | E-13 | Experiential | Every race detail is a ten-row N/A block repeated across seven races | R2-12 (fold on the planner **only**) | **Known limitation / by design** | `RaceCard.vue:134-136` states the decision screen deliberately does not fold; the fold is opt-in for the planner (`fold-undescribed`) | — | — |
| **R4-26** | E-08 | Experiential | Training cards cannot show expected gains until facility levels are recorded, and those inputs live below the cards | new | **Confirmed defect (ordering)** | `TrainingDetail.vue` renders the cards above the turn form which owns `facility_*` (`StoreTurnEntryRequest.php:97-101`) | **S7** (same screen family) | Browser: the level inputs are reachable from the cards |
| **R4-27** | E-07 | Experiential | First cockpit is a wall of N/A with the only CTA at the bottom | R3-01 adjacent | **Known limitation (density judgement)** — no code defect | The absence is correct for a Turn 0 run; the CTA is `Record the first turn through Training` | — | — |
| **R4-28** | E-10 | Experiential | Skill costs print in `CC` while the run tracks SP | new | **Does not reproduce** | `CC` is not in the repo: `rtk grep -rn "CC" resources/js/pages/Career resources/js/components/skills app/Services` → no hit; the single `CC` token is a seeder comment (`TrackerVeteransSeeder.php:158`). Every skill cost renders in SP (`SkillPlanRow.vue:84-85`, `SkillRow.vue:24-25`), and an unpriced skill reads `N/A SP` with its reason. | None; re-verify on a build-current browser if it recurs | — |
| **R4-29** | F-11 | Functional | The career nav's `Legacy` on the run deck points at `/legacy`, losing the run | new | **Confirmed defect** | `CareerSectionNav.vue:77` builds `/legacy/{runId}` — correct — and it is the only nav under `CareerLayout`. The run deck (`TrainingRunController::deck()` → `Support/Builder.vue:225-245`) renders **no career nav**; its only `Legacy` link is the global `Legacy Lab → /legacy` (`AppLayout.vue:133`). So the audit read the global nav and reported it as the career nav. | **S5** — render the career nav on the deck builder (or add a run-scoped Legacy link there); no schema | `career-deck-select.spec.ts`: the deck page's `Legacy` link href ends `/legacy/<id>` |
| **R4-30** | F-09 | Functional | Careers row reads `Turn ~11 / 24` for a run at turn 67 | new | **Confirmed defect (hardcoded literal)** | `Runs/Index.vue:111-118` prints `<p …>Turn ~11 / 24</p>` and `style="width: 45%"` — both literals, in the component, for every Active run. `TrainingRunController::index()` ships no turn for the row (`:61-76`). | **S3** (one formatter) — ship the run's real position, or remove the placeholder; the array must not say 11 for a run at 67 | `RunsIndexTest`: an Active run with turn 67 logged does not render `Turn ~11 / 24` |
| **R4-31** | F-02 | Functional | Re-saving the target for the purpose deleted the saved priorities | new; V-2 open | **Source conflict / open** | `StoreBuildTargetRequest.php:81` requires `skill_priorities` present; `BuildTarget.vue:126` pre-fills them; the write replaces the target whole (`BuildTargetController.php:106-109`). The audit's loss needs a posted empty list. | **S2** (a browser re-read first; if it reproduces, the same patch semantics) | `CareerBuildTargetTest`: save, then save again with the prefilled list; priorities survive |
| **R4-32** | E-02 | Experiential | `Save target` gives no confirmation | R2-08, R3-04 adjacent | **Does not reproduce** (V-1) | `BuildTargetController.php:111` flashes `Build target saved.`; `AppLayout.vue:31-32,387` renders every wizard page's flash. The quoted `Target: not chosen` string is in no file. | None; the residual (header/breadcrumb wording) is R4-04's copy pass if the owner wants it | — |

## Baseline test report

- **Not run:** any test, gate, build, or migration. This is a read-only classification. No PHP or JS
  command was executed against the application, and no artisan command was issued.
- **Read at:** `f6018f87` (the audit's commit, which is also `HEAD`); no second commit to compare.
- **Tree state at HEAD:** `git status --short` → `?? docs/audits/rice-shower-unity-cup-ux-walk-4.md`.
  One dirty path, untracked, **the audit itself** (not this pass; it is the immutable source). No
  tracked modification, so no peer work is entangled with any read above.
- **The audit's own console-clean claim:** walk 4 records "0 errors, 1 informational message across
  the whole walk" and a clean tree at start (§7.1, Method statement). That is the best available
  evidence the walk ran on a clean build; it is not proof the bundle was current (no `npm run build`
  is recorded), so the two **Does not reproduce** rows above are marked for browser re-read rather
  than closed for good.
- **Dev-database caveat:** the walk ran against the shared dev database (7 Veterans, 12 runs, run 25
  pre-existing). R4-01/R4-02's indistinguishability is a product property (no id is ever displayed)
  that dev state merely exposed; R4-29 is dev-agnostic; R4-07's zero-progress reading is a product
  consequence of an empty race log, not stale data.

## Proposed slice order

### Phase 1 — defects that reproduce, small and self-contained

Ordered cheapest + highest-signal + highest-confidence first.

1. **S1 — R4-01/R4-02: the run is named.** One payload field (`id`) on the list and the cockpit
   header, one line of markup, one test. Highest confidence, zero ambiguity (no run id is displayed
   anywhere, `TrainingRunController.php:61-76`).
2. **S3 — R4-09/R4-30: one clock, two surfaces.** `Runs/Index.vue:118` is a hardcoded literal and
   `RaceDecisionController.php:127` prints a within-year number as a career turn. Two reads of the
   same derivation, one formatter. Confirm first whether they share it: they do not — one is a
   literal, the other a mislabelled modulo; the slice fixes both but does not claim one root.
3. **S7 — R4-20/R4-21/R4-22/R4-23/R4-26: wizard and picker ergonomics.** Small, independent
   interactions (ownership default copy, filter-in-place, forward control, filter preservation,
   level inputs above the cards). Grouped because each is one line and they share no risk.
4. **S2 — R4-03/R4-04/R4-05/R4-24/R4-31: the target step.** Data loss is the reason it is Phase 1;
   it follows the cheap ones because its fix shape (ceilings-only payload vs split payload) is an
   owner call, and R4-31 needs a browser re-read before anyone patches it.
5. **S5 — R4-29: the deck page's nav.** Render the career nav on the deck builder; the href builder
   already exists and is correct.

### Phase 2 — re-verify before opening any work

6. **R4-11 (U-01)**, **R4-13 (U-03)**, **R4-12/U-02**, **R4-14/U-04** — each prior register claims
   either "fixed", "does not reproduce", or "still absent". The reads above settle them: U-01 exists
   (header is quiet, field is not), U-02 is genuinely absent and self-disclosed, U-03 is recordable
   through a different owner (turn events), U-04 is absent. Close as **Does not reproduce** /
   **Unrepresented** / **Partly contradicted** as written, with the correction above; only R4-13's
   copy line is work (S11).
7. **R4-08 (F-07)** and **R4-10 (E-09)** — re-read on a current build with the guided vs plain form
   both exercised. If F-07's plain form does accept a staged-less row (as the code reads), the audit's
   "blocking" is a walk-path artefact and the finding narrows to the date input. If E-09 needs a
   status value, that is an owner call (stored vocabulary, §11).

### Phase 3 — owner calls and larger gaps

8. **R4-06 (F-05)** and **R4-07 (F-06)** — scenario-scoped obligations (a seed/scope decision) and the
   completed-race record. Both are write-path/seed work, not new goal columns.
9. **R4-12, R4-14, R4-16, R4-18** — each is a schema change (team stat grades, current Spirit, skill
   levels, borrowed-card owner). §11 sends each through an ADR, a digest line and a PRD citation;
   nothing opens until the owner rules. R4-18 is plausibly a "will not add" for a loopback tool.
10. **R4-15 (U-05)** — the going/field/recommended columns; follows R4-10's state decision.
11. **R4-19 (E-03)** — bulk spark entry is a design project, not a fix.
12. **R4-17 (U-07)** — no change; the ruling is `ADR-0011` / `ADR-0014` (and `ADR-0005` R37), **not
    `ADR-0001`**, per round 3's dated correction of 2026-10-13.

## Concurrent changes constraint

The tree is quiet: one untracked file, the audit. That is the exception in this repository's recent
history, and it will not stay true once Phase 1 opens. Any slice that lands must use an explicit
pathspec and verify the committed blob against `HEAD` afterwards — the round-2 register's own rule,
written after `380350e` captured a peer's uncommitted controller:

> after any pathspec commit, diff the new commit against the previous HEAD for every file that was dirty
> before you started; a clean `git status` afterwards proves nothing about what went in.

Two slices touch files that other work has touched recently and that a peer may reopen:
`resources/js/pages/Career/Cockpit.vue` (S1, S4) and `resources/js/pages/Runs/Index.vue` (S1, S3).
Neither is dirty now; both are named here so a later reader does not read a clean start as an
invitation to `pint --dirty` (KI-75).

## Per-finding detail (Phase 1 and Phase 2 candidates only)

### R4-01 / R4-02 — the run is never named

**What reproduces.** `TrainingRunController::index()` builds each row from `id`, `name`,
`scenario_label`, `status_label`, `created_date` and `url` (`app/Http/Controllers/TrainingRunController.php:61-76`),
and `Runs/Index.vue:81-92` renders `run.name` as the only identity. The cockpit header prints the
trainee, scenario and status (`CareerSectionNav.vue:156-171`) and no id. The creation flash reads
`Career started for {name}` (`app/Http/Controllers/Career/PreflightController.php:158-159`). So run
26 first appeared in the URL, exactly as walk 4 records.

**Root cause.** No surface carries `training_runs.id`. The redirect target is the only place it lives.

**Fix shape.** Add `id` to the row payload and to the cockpit's run section, and print it beside the
title in both.

**Regression test.** `RunsIndexTest` asserts the Inertia row carries `id` and that two runs of the
same trainee render different accessible labels; `CareerPreflightTest` asserts the flash names the
run.

**RED first.** Expected failure: `RunsIndexTest` fails at the missing `id` key with
`Expected a value but got null` (inertia `where('runs.data.0.id', …)`); the browser case fails its
`aria-label` uniqueness assertion with two identical names.

### R4-03 — a saved target never becomes the run's target

**What reproduces.** The step writes two draft keys, `build_target` and `caps`
(`app/Http/Controllers/Career/BuildTargetController.php:106-109`). `StartCareerRequest` merges
`'build_target' => SetupDraft::buildTarget()` and `...SetupDraft::caps()` and writes them onto the row
(`app/Http/Requests/Career/StartCareerRequest.php:61-67`, `:138-142`) — so the carrier exists — but
the step stored `payload()`, and `payload()` is `null` whenever the purpose/band/style/target block is
unset (`app/Http/Requests/StoreBuildTargetRequest.php:160-190`, called at `BuildTargetController.php:107`).
Walk 4's first save entered only ceilings and skill priorities, so `build_target` was written null
and only `caps` survived. Preflight reads `SetupDraft::buildTarget()` and the reader returns null
(`app/Http/Controllers/Career/PreflightController.php:169-182`), so the step is `Missing`
(`:76-91`) and `complete` is false (`:87-91`). The run then has no target: `CockpitController.php:435`
reads `$run->buildTarget()` and the skills planner's absence branch runs (`SkillsPlannerController.php:37,160,336`).

**Root cause.** The all-or-nothing payload rule collapses a partial, legitimate save (ceilings are
explicitly not part of the all-or-nothing block, `StoreBuildTargetRequest.php:93-96`) into "no target",
and the run only ever receives `build_target`. A Trainer who records caps and priorities is told the
step is missing and the run is created without a target, which R4-04 then does not stop.

**Fix shape.** Decide whether a ceilings-plus-priorities save is a target (store it with the unset
fields named) or whether `build_target` splits from `caps` (then the step is genuinely incomplete and
Preflight must say so before any save). Either way the step's `done` mark and Preflight's `Missing`
must agree.

**Regression test.** `CareerBuildTargetTest`: `put(route('career.target.store'))` with ceilings and one
priority and no purpose, then `expect(SetupDraft::buildTarget())->not->toBeNull()`. `CareerPreflightTest`:
the same draft renders `contract.complete` without the `missing_section.target` warning.

**RED first.** Expected failure: `CareerBuildTargetTest` fails at `toBeNull()`
(`Failed asserting that null is not null`); `CareerPreflightTest` fails at `contract.complete` with
`false` where `true` is expected.

### R4-04 / R4-05 — the Preflight count and the non-blocking warning

**What reproduces.** `PreflightController.php:87-91` computes `complete` from five sections and
`:76-91` supplies `build.target = null` for the missing case. `StartCareerRequest.php:76-81` requires
only `umamusume_id` and `scenario`, and its docblock (`:77-80`) states that the payload sections "stay
optional on purpose, because the wizard's warnings never block" — which is R2-03's recorded ruling.
Independently, `Preflight.vue:251` prints `All five entered steps are recorded.` and `:319` heads the
block `The five entered steps`, over a six-step wizard whose sixth step is the page.

**Root cause.** The rendered heading counts a hardcoded five; the start request's optional-field rule
is the owner's decision, and the block is the copy that must agree with it.

**Fix shape.** One copy pass: the heading prints the number of rows it renders. Do **not** make the
warning block without an owner ruling.

**Regression test.** `CareerPreflightTest`: the missing-target case asserts the rendered heading's
count equals the number of rendered step rows.

**RED first.** Expected failure: the heading assertion reads `five` against a rendered row count of
five but a wizard of six — the case fails on the constant it pinning.

### R4-09 / R4-30 — one clock, two surfaces

**What reproduces.** `RaceDecisionController.php:118-127` builds `nextTurn` from
`TrainingRun::decisionTurn()`, which returns `{year, turn}` with `turn` as
`(($position->turnIndex - 1) % TURNS_PER_YEAR) + 1` (`app/Models/TrainingRun.php:669-681`) — a
within-year number 1..24 — and `:127` prints it as `turn`. `CockpitController.php:334-350` prints
`$position->turnIndex`, the absolute number. So a run at absolute turn 67 prints `Turn 20` on the
race screen and `Turn 67` on the cockpit. Separately, `Runs/Index.vue:111-118` prints the literal
`Turn ~11 / 24` and `style="width: 45%"` for every Active row, with no position anywhere in the
payload (`TrainingRunController.php:61-76`).

**Root cause.** Two, not one: the race header prints a within-year number under the absolute turn's
label, and the list prints a placeholder as if it were data. Same class of harm (a Trainer cannot
trust a turn figure), different mechanisms; one slice fixes both, and neither is the other's cause.

**Fix shape.** Race header: print the absolute turn, or name the clock (`Turn 20 of the year · 68th
turn`). List row: ship the run's real position or remove the placeholder; never a literal 11.

**Regression test.** `CareerRaceDecisionTest`: with turn 67 logged, the rendered `nextTurn` reads 68
(or carries an explicit `turn_index`); `RunsIndexTest`: an Active run at turn 67 does not render
`Turn ~11 / 24`.

**RED first.** Expected failure: `CareerRaceDecisionTest` reads `20` where `68` is expected;
`RunsIndexTest` finds the literal `Turn ~11 / 24` in the response body.

### R4-11 (Phase 2 re-read) — potential level

**What reproduces.** The field: `Cockpit.vue:686-705` renders `Trainee rarity and potential`, with a
`Rarity` select and a `potential_level` input, posted at `:441-442` and read at `:395-396`; the column
is `potential_level`, nullable, in the `trainee_rarity`/`potential_level` migration. What walk 4
recorded is the header reading `N/A` because nothing had been entered — the correct reading for an
unrecorded value — on a run whose identity editor was never opened.

**Root cause.** R2-13 and R3-12 are right. Walk 4 read the header, not the form.

**Fix shape.** None required. Optional UX only (default-open the editor when both values are null).

**Regression test.** Already exists: `TraineeRarityPotentialTest`.

**RED first.** Not applicable; this row closes as **Does not reproduce**.

### R4-13 (Phase 2 re-read) — Team Rank

**What reproduces.** `Edit team state` (`resources/js/components/career/UnityCupEditor.vue`) carries
name, motto, league placement, preseason wins, trainings and bursts — the six columns the
`2026_10_10_150000_add_team_identity_to_training_runs.php` migration added. The rank is **not** one of
its fields. But the rank **is** stored and read: `TrainingRun::latestTeamRank()` walks turn events for
a `TeamRankPayload` (`app/Models/TrainingRun.php:1206-1217`), and the cockpit's header strip reads it
into `values.team_rank` (`app/Http/Controllers/Career/CockpitController.php:363-372`).

**Root cause.** The rank has a different owner from the rest of the team state (a per-turn event, not
a run column), which neither R2-14 nor R3-26 named. Walk 4's "no rank input" is true of that editor
and false of the tool.

**Fix shape.** A copy line in the team editor naming where a rank is recorded, or a shortcut into the
turn-event form. Not a column.

**Regression test.** `CockpitTeamPanelTest` sibling: the strip renders the rank from a recorded
`TeamRankPayload`.

**RED first.** Expected failure: the strip reads `N/A` where `S` was recorded — which would prove the
editor gap mattered; it does not reproduce today.

## Next action

Open **S1 (R4-01/R4-02)** first: it is the only confirmed defect here whose fix is one payload field
and one line of markup, it needs no owner ruling, and every later walk has hit it because the run
under test is unnameable in the UI.

## Definition-of-done checklist

- [x] Every walk-4 finding appears in exactly one R4-NN row (`R4-01`–`R4-32`; 32 rows, no collapse —
      F-04/E-11 and R4-01/R4-02 share a root but stay separate rows because one is the payload and
      one is the rendered distinction).
- [x] Every `Current status` is a code read at a named commit (`f6018f87` = `HEAD`), not a
      restatement of the audit.
- [x] The cross-register mapping table is complete for every walk-4 finding with an R2/R3 overlap
      (13 rows), including the two prior rows the new evidence narrows (R2-13, R2-14) and the two it
      corrects (R3-26's team-grade reading, R3-28's `KI-34` framing).
- [x] Every `Does not reproduce` has a command proving it (V-1, V-2, R4-28).
- [x] Root causes are named for every confirmed defect.
- [x] The audit and rounds 1–3 are left unedited; this register is additive.
- [x] Baseline test report explicitly says nothing was run, and records the tree state.
- [x] Phase 1 candidates each have a detail paragraph and a proposed test.
- [x] No findings collapsed whose mechanisms differ (R4-08 vs R4-10; R4-09 vs R4-30; R4-21's two
      halves share a screen but are recorded as two causes in one row).
- [x] Walk-4's F/E/U classification preserved in the `Type` column.
- [ ] A current-build browser re-read of R4-31 and R4-08 — **not done**; it needs a browser run this
      read-only pass did not perform.
- [ ] No gate run — stated, not implied away.

## Dated correction, 2026-10-24 — R4-06 and R4-08 register rows

R4-06 (F-05) and R4-08 (F-07) still read as live findings in the register above. Their
dispositions were recorded at KI-122 and KI-123; the register rows predate those
recordings. Per the append-only rule, this note closes the gap without editing the rows.

**R4-06 / F-05.** Ruled out. The three URA Finals rounds are shared across scenarios; only
the Final forks (Unity Cup's is Aoharu). The current mandatory list is correct, and the
proposed owner filter would have deleted legitimate shared obligations. Evidence:
`docs/scenarios/09-global-race-calendar.md:105,632-633`. See KI-122.

**R4-08 / F-07 (date half only).** Does not reproduce. `Snapshot/Setup.vue:165-186` already
renders Year/Month/Phase, and `CreateSnapshotRun:58-79` writes `career_position`. The date
input existed at the audit's own commit. The date half closed further at `499e0105`, which
extracted the position label to one owner (`app/Services/Career/CareerPositionLabel.php`)
and made the snapshot review name the position the Trainer entered. The pending-turn-state
half named in KI-123 remains a separate question. See KI-123.

## Dated correction, 2026-10-25 — R4-29 (S5) does not reproduce

R4-29 (F-11) reads: "the career nav's `Legacy` on the run deck points at `/legacy`, losing the run",
with the proposed slice "render the career nav on the deck builder". The first half was a walk-path
artefact and the second half is already true.

**What the code says.** `resources/js/pages/Support/Builder.vue:273` mounts `CareerSectionNav`, and
`resources/js/components/career/CareerSectionNav.vue` builds the Legacy href as `/legacy/${runId}`
from `runUrl`. The deck's payload carries `run.id` and `run.run_url`
(`TrainingRunController::deck()`). Two `Legacy` links therefore render on that page: the career
nav's, which is run-scoped, and `AppLayout`'s global `Legacy Lab`, which is not. Walk 4 read the
global one and reported it as the career nav.

**Evidence, at the previous commit.** `tests/browser/support-deck.spec.ts`, case
`carries the career nav with a run-scoped Legacy link`, was run against `f2114c88` before any
application change: the `href` and `aria-current` assertions **passed**, and the only failing
assertion in that case was one this pass added for R4-01's header line, which the S1-residual commit
supplies. A RED that appears only on a different slice's assertion is not evidence for this row.

**Disposition.** R4-29 closes as **Does not reproduce**. No source change was made for S5; the
browser case lands as a characterization pin (`7722165d`) so the next walk cannot conflate the two
navs again. S5 is withdrawn from the slice list rather than left pending.

**Root cause of the misread, kept so the next pass does not repeat it.** The audit read a page that
renders two links with the same visible word and no distinguishing accessible name. That is a real
copy weakness even though the entry it was attributed to is correct; if a later pass wants to spend a
slice on it, the fix is a distinguishable name on the global link, not a nav on the deck.
